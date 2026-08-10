<?php

namespace App\Services;

use App\Domains\Plan\Services\FeatureUsageService;
use App\Models\Products\Product;
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
            throw new \DomainException('No active subscription.');
        }

        $featureService = app(\App\Domains\Plan\Services\FeatureUsageService::class);

        // ✅ تحقق قبل أي عملية
        if (!$featureService->canUse($subscription, 'products_limit')) {
            throw new \DomainException('You have reached your product limit.');
        }

        return DB::transaction(function () use ($data, $store, $featureService, $subscription) {

            $type = !empty($data['has_variants']) ? 'variable' : 'simple';

            $autoBarcode = $data['auto_generate_barcode'] ?? false;
            $barcode = $autoBarcode
                ? BarcodeService::product(null)
                : ($data['barcode'] ?? null);

            $autoSku = $data['auto_generate_sku'] ?? false;

            $baseSku = $autoSku
                ? SkuGenerator::product(currentStore()->slug, $data['slug'])
                : ($data['sku'] ?? null);

            if (!$baseSku) {
                throw ValidationException::withMessages([
                    'sku' => 'SKU is required. Enable auto-generate or enter a SKU manually.',
                ]);
            }

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
                        'auto_generate_barcode'
                    ]),
                )
            );

            $this->syncImages($product, $data['images'] ?? []);

            if (!empty($data['has_variants'])) {
                $this->syncVariants($product, $data);
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

            $autoSku = $data['auto_generate_sku'] ?? false;
            $autoBarcode = $data['auto_generate_barcode'] ?? false;

            $baseSku = $autoSku
                ? SkuGenerator::product(currentStore()->slug, $data['slug'])
                : ($data['sku'] ?? null);

            $barcode = $autoBarcode
                ? BarcodeService::product(null) // توليد تلقائي
                : ($data['barcode'] ?? null);



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
                    $product->variants()->delete();
                    $this->syncVariants($product, $data);
                }
            } else {
                // Simple Product
                $product->variants()->delete();
                $this->createSingleVariant($product, $data);
            }


            return $product;
        });
    }

    protected function syncVariants(Product $product, array $data): void
    {
        if (empty($data['variants_preview'])) {
            throw ValidationException::withMessages([
                'variants_preview' => __('variants_preview.'),
            ]);
        }

        foreach ($data['variants_preview'] as $index => $preview) {

            if (empty($preview['price']) || empty($preview['cost_price'])) {
                throw ValidationException::withMessages([
                    'variants_preview' => 'Each variant must have price and cost price.',
                ]);
            }

            $variant = $product->variants()->create([
                'name'       => $preview['name'] ?? $product->name,
                'sku' => ($data['auto_generate_sku'] ?? false)
                    ? SkuGenerator::variant(currentStore()->slug, $product->slug, $preview['sku_parts'] ?? [])
                    : ($preview['sku'] ?? throw ValidationException::withMessages(['sku' => 'SKU is required for variant.'])),
                'barcode' => ($data['auto_generate_barcode'] ?? false)
                    ? BarcodeService::variant(null)
                    : ($preview['barcode'] ?? null),
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
        }
    }

    protected function createSingleVariant(Product $product, array $data): void
    {
        $product->variants()->create([
            'name'       => $product->name,
            'sku'        => ($data['auto_generate_sku'] ?? false) ? SkuGenerator::product(currentStore()->slug, $product->slug) : $product->sku,
            'barcode'    => ($data['auto_generate_barcode'] ?? false) ? BarcodeService::product(null) : $product->barcode,
            'price'      => $data['price'] ?? 0,
            'cost_price' => $data['cost_price'],
            'compare_price' => $data['compare_price'] ?? 0,
            'stock'      => $data['stock'] ?? 0,
            'is_active'  => $data['is_active'] ?? true,
            'is_default' => true,
        ]);
    }

    protected function syncImages(Product $product, array $images): void
    {
        foreach (array_values($images) as $index => $path) {
            $product->images()->create([
                'path' => $path,
                'sort_order' => $index,
            ]);
        }
    }

    public function buildEditFormData(Product $product): array
    {
        $product->load(['variants.optionValues.option', 'images']);

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
