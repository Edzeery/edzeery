<?php

namespace App\Domains\Orders\Support;

use App\Models\Orders\OrderItem;
use App\Models\Products\ProductVariant;
use Illuminate\Support\Collection;

/**
 * Pure grouping core: reduces order lines to per-product, per-variant buckets.
 * Overconsumed by OrderItemsFormatter — the merchant panel, printed label and
 * carrier text all flow through this single shape. Never issues queries itself;
 * callers must eager-load `items.product`, `items.variant.optionValues.option`.
 */
final class OrderItemsGrouper
{
    /**
     * Group lines by product, aggregating repeated variants (same variant id
     * merges with summed quantity; product-level rows with no variant at all
     * collapse into a single "__no_variant__" bucket).
     *
     * @param  Collection<int, OrderItem>  $items
     * @return Collection<int, array{
     *     product_id: string,
     *     product_name: string,
     *     variants: list<array{
     *         variant_id: string|null,
     *         option_label: string,
     *         sku: string|null,
     *         price: float,
     *         qty: int,
     *     }>,
     * }>
     */
    public function group(Collection $items): Collection
    {
        return $this->lines($items)
            ->groupBy('product_id')
            ->map(function (Collection $lines): array {
                $buckets = [];

                foreach ($lines as $line) {
                    $key = $line['variant_id'] ?? '__no_variant__';

                    if (! isset($buckets[$key])) {
                        $buckets[$key] = [
                            'variant_id' => $line['variant_id'],
                            'option_label' => $line['option_label'],
                            'sku' => $line['sku'],
                            'price' => $line['price'],
                            'qty' => 0,
                        ];
                    }

                    $buckets[$key]['qty'] += $line['qty'];
                }

                return [
                    'product_id' => (string) $lines->first()['product_id'],
                    'product_name' => (string) $lines->first()['product_name'],
                    'variants' => array_values($buckets),
                ];
            })
            ->values();
    }

    /**
     * Flat per-line details — the raw material of every presenter.
     *
     * @param  Collection<int, OrderItem>  $items
     * @return Collection<int, array{
     *     product_id: string,
     *     product_name: string,
     *     variant_id: string|null,
     *     option_label: string,
     *     sku: string|null,
     *     price: float,
     *     qty: int,
     * }>
     */
    private function lines(Collection $items): Collection
    {
        return $items->values()->map(function (OrderItem $item): array {
            $variant = $item->variant;

            return [
                'product_id' => (string) $item->product_id,
                'product_name' => trim((string) ($item->product?->name ?? $variant?->name ?? '—')),
                'variant_id' => $item->product_variant_id,
                'option_label' => $this->optionLabel($variant),
                'sku' => $variant?->sku,
                'price' => (float) $item->price,
                'qty' => max(1, (int) $item->quantity),
            ];
        });
    }

    /**
     * Values-only join of a variant's options ("Medium / Beige"), dropping the
     * option names the product-edit picker shows ("Size: Medium, Color: Beige")
     * because label/table/slip space is tighter. Empty when the product has no
     * configured variant options — callers must omit parens/slashes/bullets.
     */
    private function optionLabel(?ProductVariant $variant): string
    {
        if (! $variant) {
            return '';
        }

        return $variant->optionValues
            ->map(fn ($ov) => trim((string) $ov->value))
            ->filter()
            ->implode(' / ');
    }
}