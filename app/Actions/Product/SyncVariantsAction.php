<?php

namespace App\Actions\Product;

use App\Models\Products\Product;
use App\Support\SkuGenerator;
use App\Services\BarcodeService;

class SyncVariantsAction
{
    public function handle(Product $product, array $data): void
    {
        if (empty($data['variants_preview'])) {
            throw new \DomainException(__('messages.variants_preview_required'));
        }

        foreach ($data['variants_preview'] as $index => $preview) {
            if (empty($preview['price']) || empty($preview['cost_price'])) {
                throw new \DomainException(__('messages.variant_price_cost_required'));
            }

            $variant = $product->variants()->create([
                'name' => $preview['name'] ?? $product->name,
                'sku' => ($data['auto_generate_sku'] ?? false)
                    ? SkuGenerator::variant($product->store->slug, $product->slug, $preview['sku_parts'] ?? [])
                    : ($preview['sku'] ?? throw new \DomainException(__('messages.variant_sku_required'))),
                'barcode' => ($data['auto_generate_barcode'] ?? false)
                    ? BarcodeService::variant(null)
                    : ($preview['barcode'] ?? null),
                'price' => $preview['price'],
                'cost_price' => $preview['cost_price'],
                'is_active' => $preview['is_active'] ?? true,
                'is_default' => $index === 0,
            ]);

            // Sync Option Values
            if (!empty($preview['value_ids'])) {
                $values = \App\Models\Products\ProductOptionValue::whereIn('id', $preview['value_ids'])->get()->keyBy('id');
                $pivotData = [];
                foreach ($preview['value_ids'] as $valueId) {
                    $pivotData[$valueId] = ['product_option_id' => $values[$valueId]->product_option_id];
                }
                $variant->optionValues()->sync($pivotData);
            }
        }
    }
}
