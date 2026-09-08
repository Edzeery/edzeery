<?php

namespace App\Livewire\Concerns;

use App\Domains\Cart\Support\OrderRules;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use Illuminate\Support\Facades\Storage;

/**
 * Shared product-picker family for the merchant Orders page.
 *
 * Extracted out of the (already large) orders/index.blade.php Volt view so the
 * create/edit order flows and the 31.10 items-edit product modal reuse ONE
 * picker implementation: a server-built pool of 100 products per request with
 * client-side filtering ([data-search]) and on-demand chunk continuation
 * (loadProductChunk) so users never hit a per-keystroke server query.
 *
 * The picker writes straight into $this->form['items'] (the shared draft), so
 * it is safe to drive from "create order", "edit order" AND the per-order
 * items edit modal alike. State properties live in the Volt view's state()
 * array; this trait only contributes methods.
 */
trait HasOrderProductPicker
{
    /** Build one group of product rows (pool chunk) for the picker list. */
    protected function pickerProductResults(int $limit, int $offset = 0): array
    {
        return Product::with(['primaryImage', 'variants:id,product_id,name,sku,price,stock'])
            ->select('id', 'name', 'price', 'type')
            ->where('store_id', currentStoreId())
            ->where('is_active', true)
            ->orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($product) {
                $variants = $product->variants;
                $prices = $variants->pluck('price')->filter();
                $imageUrl = $product->primaryImage?->path
                    ? Storage::disk('public')->url($product->primaryImage->path)
                    : asset('img/icons/noimg.png');
                $minPrice = $prices->min() ?? ($product->price ?? 0);
                $maxPrice = $prices->max() ?? ($product->price ?? 0);
                $firstVariant = $variants->count() === 1 ? $variants->first() : null;

                return [
                    'id' => $firstVariant?->id ?? null,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'image_url' => $imageUrl,
                    'variant_count' => $variants->count(),
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'price_range' => $minPrice != $maxPrice ? currency($minPrice).' — '.currency($maxPrice) : currency($minPrice),
                    'has_variants' => $product->hasVariants(),
                    'first_variant' => $firstVariant
                        ? [
                            'id' => $firstVariant->id,
                            'name' => $firstVariant->name,
                            'sku' => $firstVariant->sku,
                            'price' => $firstVariant->price,
                            'price_formatted' => currency($firstVariant->price),
                            'stock' => $firstVariant->stock,
                            'stock_status' => $firstVariant->stock <= 0 ? 'out' : ($firstVariant->stock <= 5 ? 'low' : 'ok'),
                            'stock_text' => $firstVariant->stock <= 0 ? __('merchant_panel.out_of_stock') : $firstVariant->stock.' '.__('merchant_panel.left'),
                        ]
                        : null,
                ];
            })
            ->values()
            ->toArray();
    }

    /** Load the first picker chunk (one request) and reset the chunk cursor. */
    public function loadProducts(): void
    {
        $this->formProductResults = $this->pickerProductResults(100);
        $this->productHasMore = count($this->formProductResults) === 100;
        $this->productChunkLoading = false;
    }

    /**
     * Append the next 100 products in one request when the current pool has
     * already been exhausted by the client-side search.
     */
    public function loadProductChunk(): void
    {
        if ($this->productChunkLoading || ! $this->productHasMore) {
            return;
        }

        $this->productChunkLoading = true;
        $offset = count($this->formProductResults);
        $chunk = $this->pickerProductResults(100, $offset);

        $this->formProductResults = array_values(array_merge($this->formProductResults, $chunk));
        $this->productHasMore = count($chunk) === 100;
        $this->productChunkLoading = false;
    }

    /** Open the variant list modal for a specific product. */
    public function selectProduct(string $productId): void
    {
        $product = Product::with(['primaryImage', 'variants.optionValues.option'])
            ->where('id', $productId)
            ->where('store_id', currentStoreId())
            ->first();

        if (! $product) {
            return;
        }

        $imageUrl = $product->primaryImage?->path ? Storage::disk('public')->url($product->primaryImage->path) : asset('img/icons/noimg.png');

        $this->formSelectedProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'image_url' => $imageUrl,
            'variants' => $product->variants
                ->map(function ($v) {
                    $optionLabels = $v->optionValues->map(fn ($ov) => ($ov->option?->name ?? '').': '.$ov->value)->implode(', ');

                    return [
                        'id' => $v->id,
                        'name' => $v->name,
                        'option_labels' => $optionLabels,
                        'sku' => $v->sku,
                        'price' => $v->price,
                        'stock' => $v->stock,
                        'is_active' => $v->is_active,
                    ];
                })
                ->toArray(),
        ];
        $this->formProductView = 'variants';
    }

    public function backToProducts(): void
    {
        $this->formProductView = 'list';
        $this->formSelectedProduct = null;
    }

    /**
     * Resolve the unit price that will be stored for a newly added line: the
     * variant price, else the product price, else 0 (the UI renders a
     * "please select a product" hint when the result stays empty).
     */
    protected function resolveItemPrice(ProductVariant $variant): float
    {
        return (float) ($variant->price ?? $variant->product?->price ?? 0);
    }

    /** Add one variant to the shared draft (or bump its quantity). */
    public function addFormItem(string $variantId): void
    {
        $variant = ProductVariant::with(['product', 'product.primaryImage'])
            ->where('store_id', currentStoreId())
            ->findOrFail($variantId);

        $store = $variant->product?->store;
        $cap = OrderRules::lineCap($variant, $store);
        $available = (int) $variant->stock;
        $tracks = OrderRules::tracksInventory($store);
        $backorder = OrderRules::allowsBackorder($store);

        if ($tracks && ! $backorder && $available <= 0) {
            $this->syncFormSelectedItems();
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.out_of_stock', ['variant' => $variant->name])]);

            return;
        }

        $found = false;
        foreach ($this->form['items'] as $idx => &$item) {
            if ($item['product_variant_id'] === $variantId) {
                if ($cap !== null && ($item['quantity'] ?? 0) >= $cap) {
                    $this->syncFormSelectedItems();
                    $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.max_qty_reached', ['cap' => $cap])]);

                    return;
                }
                $this->form['items'][$idx]['quantity']++;
                $this->form['items'][$idx]['preorder'] = $backorder && $available < $this->form['items'][$idx]['quantity'];
                $found = true;
                break;
            }
        }
        unset($item);

        if (! $found) {
            $this->form['items'][] = [
                'product_variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'name' => ($variant->product?->name ?? '').' — '.$variant->name,
                'sku' => $variant->sku ?? '',
                'price' => $this->resolveItemPrice($variant),
                'quantity' => 1,
                'stock' => $variant->stock ?? 0,
                'weight' => $variant->weight ?? 0,
                'cap' => $cap,
                'preorder' => $backorder && $available < 1,
                'image_url' => $variant->product?->primaryImage?->path ? Storage::disk('public')->url($variant->product->primaryImage->path) : asset('img/icons/noimg.png'),
            ];
        }

        $this->syncFormSelectedItems();
        $this->recalcFormWeight();
    }

    /** Quick-add by barcode / SKU from the picker's enter shortcut. */
    public function addFormItemByBarcode(string $code): void
    {
        if (strlen($code) < 2) {
            return;
        }

        $variant = ProductVariant::with(['product', 'product.primaryImage'])
            ->whereHas('product', fn ($q) => $q->where('store_id', currentStoreId()))
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)->orWhere('sku', $code);
            })
            ->first();

        if (! $variant) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.product_not_found')]);

            return;
        }

        $store = $variant->product?->store;
        $cap = OrderRules::lineCap($variant, $store);
        $available = (int) $variant->stock;
        $tracks = OrderRules::tracksInventory($store);
        $backorder = OrderRules::allowsBackorder($store);

        if ($tracks && ! $backorder && $available <= 0) {
            $this->syncFormSelectedItems();
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.out_of_stock', ['variant' => $variant->name])]);

            return;
        }

        foreach ($this->form['items'] as $idx => &$item) {
            if ($item['product_variant_id'] === $variant->id) {
                if ($cap !== null && ($item['quantity'] ?? 0) >= $cap) {
                    $this->syncFormSelectedItems();
                    $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.max_qty_reached', ['cap' => $cap])]);

                    return;
                }
                $this->form['items'][$idx]['quantity']++;
                $this->form['items'][$idx]['preorder'] = $backorder && $available < $this->form['items'][$idx]['quantity'];
                $this->syncFormSelectedItems();
                $this->recalcFormWeight();

                return;
            }
        }
        unset($item);

        $this->form['items'][] = [
            'product_variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'name' => ($variant->product?->name ?? '').' — '.$variant->name,
            'sku' => $variant->sku ?? '',
            'price' => $this->resolveItemPrice($variant),
            'quantity' => 1,
            'stock' => $variant->stock ?? 0,
            'weight' => $variant->weight ?? 0,
            'cap' => $cap,
            'preorder' => $backorder && $available < 1,
            'image_url' => $variant->product?->primaryImage?->path ? Storage::disk('public')->url($variant->product->primaryImage->path) : asset('img/icons/noimg.png'),
        ];
        $this->syncFormSelectedItems();
        $this->recalcFormWeight();
    }

    public function removeFormItem(int $index): void
    {
        unset($this->form['items'][$index]);
        $this->form['items'] = array_values($this->form['items']);
        $this->syncFormSelectedItems();
        $this->recalcFormWeight();
    }

    public function updateFormItemQty(int $index, int $qty): void
    {
        if (! isset($this->form['items'][$index])) {
            return;
        }

        $cap = $this->form['items'][$index]['cap'] ?? null;
        $this->form['items'][$index]['quantity'] = min(max(1, $qty), $cap ?? PHP_INT_MAX);

        $variant = ProductVariant::find($this->form['items'][$index]['product_variant_id']);
        if ($variant) {
            $available = (int) $variant->stock;
            $this->form['items'][$index]['preorder'] = OrderRules::allowsBackorder($variant->product?->store) && $available < $this->form['items'][$index]['quantity'];
        }

        $this->recalcFormWeight();
    }

    public function updateFormItemPrice(int $index, $price): void
    {
        if (isset($this->form['items'][$index])) {
            $this->form['items'][$index]['price'] = max(0, (float) $price);
        }
    }

    public function syncFormSelectedItems(): void
    {
        $this->formSelectedItems = collect($this->form['items'])->pluck('quantity', 'product_variant_id')->toArray();
        $this->dispatch('selected-items-updated', items: $this->formSelectedItems);
    }

    public function recalcFormWeight(): void
    {
        $weight = collect($this->form['items'] ?? [])->sum(
            fn ($i) => (float) ($i['weight'] ?? 0) * (int) ($i['quantity'] ?? 1)
        );
        $this->form['weight_kg'] = $weight > 0 ? round($weight, 3) : '';
    }
}