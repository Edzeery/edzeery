<?php

namespace App\Actions\Product;

use App\Models\Products\Product;

class BuildEditFormDataAction
{
    public function handle(Product $product): array
    {
        $product->load(['variants.optionValues.option', 'images']);

        $data = [
            'images' => $product->images->sortBy('sort_order')->pluck('path')->toArray(),
            'description' => $product->description,
            'short_description' => $product->short_description,
        ];

        if ($product->variants->isNotEmpty()) {
            $data['has_variants'] = true;
            // Build options & preview
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

            $data['options'] = collect($options)->map(fn($o) => [
                'product_option_id' => $o['product_option_id'],
                'type' => $o['type'],
                'values' => array_values(array_unique($o['values'])),
            ])->values()->toArray();

            $data['variants_preview'] = $product->variants->map(fn($variant) => [
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'price' => $variant->price,
                'cost_price' => $variant->cost_price,
                'stock' => $variant->stock,
                'is_active' => $variant->is_active,
            ])->toArray();
        }

        return array_merge($data, [
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
    }
}
