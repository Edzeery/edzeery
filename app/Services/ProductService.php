<?php

namespace App\Services;

use App\Domains\Plan\Services\FeatureUsageService;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Support\SkuGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function create(Store $store, array $data): Product
    {
        $subscription = auth()->user()->latestSubscription();

        if (!$subscription) {
            throw new \DomainException(__('messages.subscription_required'));
        }

        $featureService = app(\App\Domains\Plan\Services\FeatureUsageService::class);

        // ✅ تحقق قبل أي عملية
        if (!$featureService->canUse($subscription, 'products_limit')) {
            throw new \DomainException(__('messages.product_limit_reached'));
        }

        return DB::transaction(function () use ($data, $store, $featureService, $subscription) {

            $type = !empty($data['has_variants']) ? 'variable' : 'simple';

            $autoBarcode = $data['auto_generate_barcode'] ?? false;
            $barcode = $autoBarcode
                ? BarcodeService::product(null)
                : (($data['barcode'] ?? null) ?: null);

            $autoSku = $data['auto_generate_sku'] ?? false;
            $reservedSkus = [];

            $baseSku = $autoSku
                ? SkuGenerator::product($store->slug, $data['slug'])
                : (($data['sku'] ?? null) ?: null);

            if (!$baseSku) {
                throw ValidationException::withMessages([
                    'sku' => __('messages.sku_required'),
                ]);
            }

            // The default variant mirrors the product SKU and SKUs are unique
            // per store across ALL variants, so the base SKU itself must also
            // dodge existing variant SKUs - not just other product rows.
            $baseSku = $autoSku
                ? $this->resolveUniqueSku($store, $baseSku, null, $reservedSkus)
                : $this->ensureSkuFree($store, $baseSku, null, $reservedSkus, 'sku', 'sku_duplicate');

            $product = Product::create(
                array_merge(
                    [
                        'store_id' => $store->id,
                        'barcode' => $barcode,
                        'sku' => $baseSku,
                        'description' => $data['description'] ?? null,
                        'type' => $type,
                    ],
                    Arr::except($data, [
                        'options',
                        'variants_preview',
                        'images',
                        'has_variants',
                        'auto_generate_sku',
                        'auto_generate_barcode',
                        'sku',
                        'barcode',
                    ]),
                )
            );

            $this->syncImages($product, $data['images'] ?? []);

            if (!empty($data['has_variants'])) {
                $this->syncVariants($product, $data, $store, $reservedSkus);
            } else {
                $this->createSingleVariant($product, $data);
            }

            // ✅ استهلاك بعد نجاح كل شيء داخل transaction
            $featureService->consume($subscription, 'products_limit');

            return $product;
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {

            $store = $product->store;

            $autoSku = $data['auto_generate_sku'] ?? false;
            $autoBarcode = $data['auto_generate_barcode'] ?? false;
            $reservedSkus = [];

            $baseSku = $autoSku
                ? SkuGenerator::product($store->slug, $data['slug'])
                : (($data['sku'] ?? null) ?: null);

            $barcode = $autoBarcode
                ? BarcodeService::product(null) // توليد تلقائي
                : (($data['barcode'] ?? null) ?: null);

            if (!$baseSku) {
                throw ValidationException::withMessages([
                    'sku' => __('messages.sku_required'),
                ]);
            }

            $baseSku = $autoSku
                ? $this->resolveUniqueSku($store, $baseSku, $product->id, $reservedSkus)
                : $this->ensureSkuFree($store, $baseSku, $product->id, $reservedSkus, 'sku', 'sku_duplicate');

            $product->update(
                array_merge(
                    [
                        'barcode' => $barcode,
                        'sku' => $baseSku,
                        'type'     => !empty($data['has_variants']) ? 'variable' : 'simple',
                        'description' => $data['description'] ?? $product->description,

                    ],
                    Arr::except($data, [
                        'options',
                        'variants_preview',
                        'images',
                        'has_variants',
                        'auto_generate_sku',
                        'auto_generate_barcode',
                        'sku',
                        'barcode',
                    ])
                )
            );


            if (!empty($data['images'])) {
                $product->images()->delete();
                $this->syncImages($product, $data['images']);
            }

            if (!empty($data['has_variants'])) {

                if (!empty($data['options_changed'])) {
                    // الخيارات تغيرت → أعد إنشاء Variants
                    $this->deleteVariantImages($product);
                    $product->variants()->delete();
                    $this->syncVariants($product, $data, $store, $reservedSkus);
                }
            } else {
                // Simple Product
                $this->deleteVariantImages($product);
                $product->variants()->delete();
                $this->createSingleVariant($product, $data);
            }


            return $product;
        });
    }

    protected function syncVariants(Product $product, array $data, Store $store, array &$reservedSkus): void
    {
        if (empty($data['variants_preview'])) {
            throw ValidationException::withMessages([
                'variants_preview' => __('messages.variants_preview_required'),
            ]);
        }

        $autoSku = $data['auto_generate_sku'] ?? false;

        foreach ($data['variants_preview'] as $index => $preview) {

            if (empty($preview['price']) || empty($preview['cost_price'])) {
                throw ValidationException::withMessages([
                    'variants_preview' => __('messages.variant_price_cost_required'),
                ]);
            }

            if ($autoSku) {
                // Option values that normalize to nothing (e.g. non-Latin text)
                // collapse distinct variants onto the same auto SKU, and the
                // store's unique constraint is (store_id, sku): resolve each
                // candidate against the batch and every other variant in the
                // store so a plain INSERT can never trip it.
                $sku = $this->resolveUniqueSku(
                    $store,
                    SkuGenerator::variant($store->slug, $product->slug, $preview['sku_parts'] ?? []),
                    $product->id,
                    $reservedSkus
                );
            } else {
                $sku = $preview['sku'] ?? null;

                if (blank($sku)) {
                    throw ValidationException::withMessages([
                        'variants_preview' => __('messages.variant_sku_required'),
                    ]);
                }

                $this->ensureSkuFree($store, $sku, $product->id, $reservedSkus, 'variants_preview');
            }

            $variant = $product->variants()->create([
                'name'       => $preview['name'] ?? $product->name,
                'sku' => $sku,
                'barcode' => ($data['auto_generate_barcode'] ?? false)
                    ? BarcodeService::variant(null)
                    : (($preview['barcode'] ?? null) ?: null),
                'price'      => $preview['price'],
                'compare_price' => $preview['compare_price'] ?? null,
                'cost_price' => $preview['cost_price'],
                'stock'      => $preview['stock'] ?? 0,
                'low_stock_threshold' => $preview['low_stock_threshold'] ?? 5,
                'weight'     => $preview['weight'] ?? null,
                'length'     => $preview['length'] ?? null,
                'width'      => $preview['width'] ?? null,
                'height'     => $preview['height'] ?? null,
                'is_active'  => $preview['is_active'] ?? true,
                'is_default' => $index === 0,
            ]);

            $valueIds = $preview['value_ids'] ?? [];

            if (!empty($valueIds)) {

                $values = \App\Models\Products\ProductOptionValue::whereIn('id', $valueIds)
                    ->get()
                    ->keyBy('id');

                $pivotData = [];

                foreach ($valueIds as $valueId) {
                    $pivotData[$valueId] = [
                        'product_option_id' => $values[$valueId]->product_option_id,
                    ];
                }

                $variant->optionValues()->sync($pivotData);
            }

            if (!empty($preview['new_image'])) {
                $path = $preview['new_image']->store('products', 'public');

                $variant->images()->create([
                    'path' => $path,
                    'store_id' => $product->store_id,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }
        }
    }

    protected function createSingleVariant(Product $product, array $data): void
    {
        $product->variants()->create([
            'name'       => $product->name,
            // The base product SKU was already resolved and reserved in
            // create()/update(); the default variant mirrors it verbatim.
            'sku'        => $product->sku,
            'barcode'    => ($data['auto_generate_barcode'] ?? false) ? BarcodeService::product(null) : $product->barcode,
            'price'      => $data['price'] ?? 0,
            'cost_price' => $data['cost_price'],
            'compare_price' => $data['compare_price'] ?? 0,
            'stock'      => $data['stock'] ?? 0,
            'is_active'  => $data['is_active'] ?? true,
            'is_default' => true,
        ]);
    }

    /**
     * Whether a SKU is already claimed inside the current save batch or by any
     * other variant in the store. Auto-generating a SKU must never write a
     * value the (store_id, sku) unique constraint would reject.
     */
    protected function isSkuTaken(Store $store, string $sku, ?string $ignoreProductId, array $reservedSkus): bool
    {
        if (isset($reservedSkus[$sku])) {
            return true;
        }

        return ProductVariant::query()
            ->where('store_id', $store->id)
            ->when($ignoreProductId !== null, fn ($query) => $query->where('product_id', '!=', $ignoreProductId))
            ->where('sku', $sku)
            ->exists();
    }

    /**
     * Return the given base SKU uniquely: append -2, -3, ... until it is free.
     */
    protected function resolveUniqueSku(Store $store, string $base, ?string $ignoreProductId, array &$reservedSkus): string
    {
        $candidate = $base;
        $counter = 2;

        while ($this->isSkuTaken($store, $candidate, $ignoreProductId, $reservedSkus)) {
            $candidate = $base.'-'.$counter++;
        }

        $reservedSkus[$candidate] = true;

        return $candidate;
    }

    /**
     * Manual SKUs are user-owned: collisions are surfaced as validation errors
     * instead of being silently renamed.
     */
    protected function ensureSkuFree(Store $store, string $sku, ?string $ignoreProductId, array &$reservedSkus, string $field, string $messageKey = 'variant_sku_duplicate'): string
    {
        if ($this->isSkuTaken($store, $sku, $ignoreProductId, $reservedSkus)) {
            throw ValidationException::withMessages([
                $field => __("messages.{$messageKey}", ['sku' => $sku]),
            ]);
        }

        $reservedSkus[$sku] = true;

        return $sku;
    }

    protected function syncImages(Product $product, array $images): void
    {
        foreach (array_values($images) as $index => $path) {
            $product->images()->create([
                'path' => $path,
                'store_id' => $product->store_id,
                'sort_order' => $index,
            ]);
        }
    }

    protected function deleteVariantImages(Product $product): void
    {
        \App\Models\Products\ProductImage::where('imageable_type', \App\Models\Products\ProductVariant::class)
            ->whereIn('imageable_id', $product->variants()->pluck('id'))
            ->delete();
    }

    public function buildEditFormData(Product $product): array
    {
        $product->load(['variants.optionValues.option', 'variants.images', 'images']);

        $data = [
            'images' => $product->images->sortBy('sort_order')->pluck('path')->toArray(),
            'description' => $product->description,
            'short_description' => $product->short_description,
        ];


        if ($product->type === 'variable' && $product->variants->isNotEmpty()) {
            $data['has_variants'] = true;

            // بناء options لكل خيار مع دمج جميع القيم المخصصة عبر جميع المتغيرات
            $options = [];
            foreach ($product->variants as $variant) {
                foreach ($variant->optionValues as $value) {

                    $option = $value->option;

                    if (!isset($options[$option->id])) {
                        $options[$option->id] = [
                            'product_option_id' => $option->id,
                            'type' => $option->type->value,
                            'values' => [],
                        ];
                    }

                    $options[$option->id]['values'][] = $value->id;
                }
            }


            $data['options'] = collect($options)
                ->map(fn($o) => [
                    'product_option_id' => $o['product_option_id'],
                    'type' => $o['type'],
                    'values' => array_values(array_unique($o['values'])),
                ])
                ->values()
                ->toArray();

            // بناء preview للمتغيرات
            $data['variants_preview'] = $product->variants->map(fn($variant) => [
                'labels' => $variant->optionValues
                    ->map(fn($v) => "{$v->option->name} : {$v->value}")
                    ->implode(' , '),
                'value_ids' => $variant->optionValues->pluck('id')->toArray(),
                'sku' => $variant->sku,
                'sku_parts' => $variant->optionValues
                    ->pluck('value')
                    ->map(fn($v) => \App\Support\SkuGenerator::normalizePart($v))

                    ->toArray(),
                'barcode' => $variant->barcode,
                'price' => $variant->price,
                'compare_price' => $variant->compare_price,
                'cost_price' => $variant->cost_price,
                'stock' => $variant->stock,
                'low_stock_threshold' => $variant->low_stock_threshold,
                'weight' => $variant->weight,
                'length' => $variant->length,
                'width' => $variant->width,
                'height' => $variant->height,
                'is_active' => $variant->is_active,
                'image' => $variant->images->firstWhere('is_primary', true)?->path,
                'new_image' => null,
                'profit' => $variant->price - $variant->cost_price,
                'margin' => $variant->price > 0
                    ? round((($variant->price - $variant->cost_price) / $variant->price) * 100, 2)
                    : null,
            ])->toArray();
        }
        $data = array_merge($data, [
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'unit' => $product->unit,
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
            'is_active' => $product->is_active,
            'is_featured' => $product->is_featured,
        ]);

        return $data;
    }
}
