<?php

namespace App\Domains\Order\Actions;

use App\Models\Products\ProductVariant;

class SuggestCompareDiscountAction
{
    public function execute(array $items, float $subtotal): array
    {
        $variantIds = collect($items)
            ->pluck('product_variant_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $compareMap = $variantIds
            ? ProductVariant::whereIn('id', $variantIds)->get(['id', 'compare_price'])->keyBy('id')
            : collect();

        $amount = round(collect($items)->sum(function ($i) use ($compareMap) {
            $target = $compareMap[$i['product_variant_id'] ?? null] ?? null;

            if (! $target || $target->compare_price === null) {
                return 0;
            }

            return max(0, (float) $target->compare_price - (float) ($i['price'] ?? 0)) * (int) ($i['quantity'] ?? 0);
        }), 2);

        $amount = min($amount, max(0, (float) $subtotal));

        return [
            'eligible' => $amount > 0,
            'amount' => $amount,
            'percent' => $subtotal > 0 ? round(($amount / $subtotal) * 100, 1) : null,
        ];
    }
}