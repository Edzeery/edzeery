<?php

namespace App\Domains\Cart\Support;

use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;

class OrderRules
{
    /**
     * Effective per-line order limits.
     * Priority: product override -> store default -> (1, unlimited).
     *
     * @return array{min: int, max: int|null}
     */
    public static function limits(Product $product, ?Store $store = null): array
    {
        $store ??= $product->store;

        $min = $product->min_order_qty
            ?? $store?->settings?->min_order_qty
            ?? 1;

        $max = $product->max_order_qty
            ?? $store?->settings?->max_order_qty
            ?? null;

        return [
            'min' => max(1, (int) $min),
            'max' => $max !== null ? max(1, (int) $max) : null,
        ];
    }

    /**
     * Whether this store tracks inventory at all.
     */
    public static function tracksInventory(?Store $store): bool
    {
        if (! $store) {
            return true;
        }

        return (bool) ($store->settings?->inventory_tracking ?? true);
    }

    /**
     * Whether orders beyond available stock are accepted.
     */
    public static function allowsBackorder(?Store $store): bool
    {
        if (! $store) {
            return false;
        }

        return (bool) ($store->settings?->allow_backorder ?? false);
    }

    /**
     * Hard quantity cap for a single cart line, or null when unlimited.
     *
     * The merchant's max order qty (product override -> store default)
     * ALWAYS applies. Available stock only caps the line when the store
     * tracks inventory and does not accept backorders.
     */
    public static function lineCap(ProductVariant $variant, ?Store $store = null, ?Product $product = null): ?int
    {
        $store ??= $variant->product?->store;
        $product ??= $variant->product;

        $caps = [];

        $limits = self::limits($product, $store);
        if ($limits['max'] !== null) {
            $caps[] = $limits['max'];
        }

        if (
            self::tracksInventory($store)
            && ! self::allowsBackorder($store)
            && $variant->stock !== null
        ) {
            $caps[] = (int) $variant->stock;
        }

        if ($caps === []) {
            return null;
        }

        return max(0, min($caps));
    }
}
