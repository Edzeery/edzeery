<?php

namespace App\Support;

use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;

class VariantPreviewBuilder
{
    /**
     * Build variant preview combinations from product options
     */
    public static function fromOptions(array $options): array
    {
        if (empty($options)) {
            return [];
        }

        // 1️⃣ Collect IDs
        $optionIds = collect($options)
            ->pluck('product_option_id')
            ->filter()
            ->unique()
            ->toArray();

        $valueIds = collect($options)
            ->pluck('values')
            ->flatten()
            ->filter()
            ->unique()
            ->toArray();

        if (empty($optionIds) || empty($valueIds)) {
            return [];
        }

        // 2️⃣ Fetch once
        $optionsCollection = ProductOption::whereIn('id', $optionIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $valuesCollection = ProductOptionValue::whereIn('id', $valueIds)
            ->get(['id', 'product_option_id', 'value'])
            ->groupBy('product_option_id');

        // 3️⃣ Build options map
        $optionsMap = collect($options)
            ->filter(fn ($o) => ! empty($o['values']))
            ->map(function ($option) use ($optionsCollection, $valuesCollection) {

                $optionModel = $optionsCollection->get($option['product_option_id']);

                if (! $optionModel) {
                    return null;
                }

                return [
                    'option_name' => $optionModel->name,
                    'values'      => $valuesCollection->get(
                        $option['product_option_id'],
                        collect()
                    ),
                ];
            })
            ->filter()
            ->values();

        if ($optionsMap->isEmpty()) {
            return [];
        }

        // 4️⃣ Cartesian Product
        $combinations = [[]];

        foreach ($optionsMap as $option) {
            $tmp = [];

            foreach ($combinations as $combination) {
                foreach ($option['values'] as $value) {
                    $tmp[] = array_merge($combination, [[
                        'option'   => $option['option_name'],
                        'value'    => $value->value,
                        'value_id' => $value->id,
                    ]]);
                }
            }

            $combinations = $tmp;
        }

        // 5️⃣ Final format
        return collect($combinations)->map(fn ($combo) => [
            'labels' => collect($combo)
                ->map(fn ($c) => "{$c['option']} : {$c['value']}")
                ->implode(' , '),

            'sku_parts' => collect($combo)
                ->pluck('value')
                ->map(fn ($v) => SkuGenerator::normalizePart($v))
                ->toArray(),

            'value_ids' => collect($combo)
                ->pluck('value_id')
                ->toArray(),

            'name' => collect($combo)
                ->map(fn ($c) => "{$c['option']} : {$c['value']}")
                ->implode(' / '),

            // Defaults (pure data)
            'sku' => null,
            'barcode' => null,
            'price' => null,
            'compare_price' => null,
            'cost_price' => null,
            'stock' => 1,
            'low_stock_threshold' => 5,
            'weight' => null,
            'length' => null,
            'width' => null,
            'height' => null,
            'is_active' => true,
        ])->toArray();
    }
}
