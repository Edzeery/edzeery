<?php

namespace App\Domains\Cart\Services;

use App\Models\Products\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'edzeery_cart';

    public function getStoreCart(string $storeId): array
    {
        $carts = Session::get(self::SESSION_KEY, []);

        return $carts[$storeId] ?? ['items' => [], 'coupon_code' => null];
    }

    public function addItem(string $storeId, string $variantId, int $quantity = 1): array
    {
        $cart = $this->getStoreCart($storeId);
        $variant = ProductVariant::with('product')->findOrFail($variantId);

        if (! $variant->product || (string) $variant->product->store_id !== (string) $storeId) {
            abort(404);
        }

        $existingQty = $cart['items'][$variantId]['quantity'] ?? 0;
        $newQty = $existingQty + $quantity;

        if ($variant->stock !== null && $newQty > $variant->stock) {
            $newQty = $variant->stock;
        }

        if ($newQty <= 0) {
            return $cart;
        }

        $cart['items'][$variantId] = [
            'variant_id'   => $variantId,
            'product_name' => $variant->product->name,
            'variant_name' => $variant->name,
            'price'        => (float) ($variant->price ?? $variant->product->price ?? 0),
            'quantity'     => max(1, $newQty),
            'max_stock'    => $variant->stock,
        ];

        $this->persist($storeId, $cart);

        return $cart;
    }

    public function updateQuantity(string $storeId, string $variantId, int $quantity): array
    {
        $cart = $this->getStoreCart($storeId);

        if (! isset($cart['items'][$variantId])) {
            return $cart;
        }

        if ($quantity <= 0) {
            return $this->removeItem($storeId, $variantId);
        }

        $variant = ProductVariant::find($variantId);

        if (! $variant) {
            return $this->removeItem($storeId, $variantId);
        }

        $max = $variant->stock ?? $quantity;
        $cart['items'][$variantId]['quantity'] = min($quantity, $max);

        $this->persist($storeId, $cart);

        return $cart;
    }

    public function removeItem(string $storeId, string $variantId): array
    {
        $cart = $this->getStoreCart($storeId);

        if (! isset($cart['items'][$variantId])) {
            return $cart;
        }

        unset($cart['items'][$variantId]);
        $this->persist($storeId, $cart);

        return $cart;
    }

    public function clear(string $storeId): void
    {
        $carts = Session::get(self::SESSION_KEY, []);
        unset($carts[$storeId]);
        Session::put(self::SESSION_KEY, $carts);
    }

    public function getItems(string $storeId): Collection
    {
        $cart = $this->getStoreCart($storeId);

        return collect($cart['items']);
    }

    public function getCount(string $storeId): int
    {
        return $this->getItems($storeId)->sum('quantity');
    }

    public function getSubtotal(string $storeId): float
    {
        return $this->getItems($storeId)
            ->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    public function getTotal(string $storeId, float $shippingCost = 0): float
    {
        return $this->getSubtotal($storeId) + $shippingCost;
    }

    public function isEmpty(string $storeId): bool
    {
        return $this->getCount($storeId) === 0;
    }

    public function applyCoupon(string $storeId, ?string $couponCode): void
    {
        $cart = $this->getStoreCart($storeId);
        $cart['coupon_code'] = $couponCode;
        $this->persist($storeId, $cart);
    }

    public function toArray(string $storeId): array
    {
        $cart = $this->getStoreCart($storeId);
        $cart['count']    = $this->getCount($storeId);
        $cart['subtotal'] = $this->getSubtotal($storeId);

        return $cart;
    }

    private function persist(string $storeId, array $cart): void
    {
        $carts = Session::get(self::SESSION_KEY, []);
        $carts[$storeId] = $cart;
        Session::put(self::SESSION_KEY, $carts);
    }
}
