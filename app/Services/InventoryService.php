<?php

namespace App\Services;

use App\Enums\Store\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Products\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public static function adjust(
        ProductVariant $variant,
        int $quantity,
        ?string $reason = null
    ): void {
        self::apply(
            $variant,
            $quantity,
            InventoryMovementType::ADJUSTMENT,
            $reason
        );
    }

    public static function apply(
        ProductVariant $variant,
        int $quantity,
        InventoryMovementType $type,
        $source = null,
        ?User $user = null
    ): void {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        DB::transaction(function () use (
            $variant,
            $quantity,
            $type,
            $source,
            $user
        ) {
            // 🔒 Lock row properly
            $variant = ProductVariant::query()
                ->whereKey($variant->id)
                ->lockForUpdate()
                ->first();

            $previousStock = $variant->stock;

            $delta = $type->isDecrease()
                ? -$quantity
                : $quantity;

            $newStock = $variant->stock + $delta;

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'stock' => 'Insufficient stock.',
                ]);
            }

            // ✅ Update snapshot stock
            $variant->update([
                'stock' => $newStock,
            ]);

            // 🧾 Ledger entry
            InventoryMovement::create([
                'store_id'           => $variant->product->store_id,
                'product_variant_id' => $variant->id,
                'quantity'           => $quantity,
                'balance_after'      => $newStock,
                'type'               => $type->value,
                'user_id'            => $user?->id ?? auth()->id(),
                'source_type' => $source ? (is_object($source) ? get_class($source) : (string)$source) : null,
                'source_id'   => is_object($source) && method_exists($source, 'getKey') ? $source->getKey() : null,

            ]);
            // 🔔 Low stock notification
            if (
                $variant->isLowStock()
                && $previousStock > $variant->low_stock_threshold
                && $variant->last_low_stock_notified_at === null
            ) {
                $variant->updateQuietly([
                    'last_low_stock_notified_at' => now(),
                ]);


                // User::query()
                //     ->each(
                //         fn(User $admin) =>
                //         $admin->notify(new LowStockNotification($variant))
                //     );
            }

            // 🔄 Reset notification if recovered
            if (
                $previousStock <= $variant->low_stock_threshold
                && $variant->stock > $variant->low_stock_threshold
            ) {
                $variant->updateQuietly([
                    'last_low_stock_notified_at' => null,
                ]);
            }
        });
    }
}
