<?php

namespace App\Models\Orders;

use App\Models\Products\ProductVariant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasUlids;
    use SoftDeletes;
    protected $fillable = [
        'store_id',
        'order_id',
        'product_variant_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(\App\Models\Orders\Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Products\Product::class, 'product_id');
    }

    public function store()
    {
        return $this->belongsTo(\App\Models\Stores\Store::class);
    }

    protected static function booted(): void
    {
        static::deleted(function (OrderItem $item) {
            if ($item->isForceDeleting()) {
                return;
            }

            if (! $item->variant) {
                return;
            }

            // Only release if a RESERVE exists for this item (order was confirmed+)
            $hasReservation = \App\Models\InventoryMovement::query()
                ->where('source_type', \App\Models\Orders\Order::class)
                ->where('source_id', $item->order_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->where('type', \App\Enums\Store\InventoryMovementType::RESERVE->value)
                ->exists();

            if (! $hasReservation) {
                return;
            }

            // Idempotency: skip if already released
            $alreadyReleased = \App\Models\InventoryMovement::query()
                ->where('source_type', \App\Models\Orders\Order::class)
                ->where('source_id', $item->order_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->where('type', \App\Enums\Store\InventoryMovementType::RELEASE->value)
                ->exists();

            if ($alreadyReleased) {
                return;
            }

            \App\Services\InventoryService::apply(
                variant: $item->variant,
                quantity: $item->quantity,
                type: \App\Enums\Store\InventoryMovementType::RELEASE,
                source: $item->order,
                user: auth()->user()
            );
        });

        static::restored(function (OrderItem $item) {
            if (! $item->variant) {
                return;
            }

            // Idempotency: skip if already reserved
            $alreadyReserved = \App\Models\InventoryMovement::query()
                ->where('source_type', \App\Models\Orders\Order::class)
                ->where('source_id', $item->order_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->where('type', \App\Enums\Store\InventoryMovementType::RESERVE->value)
                ->exists();

            if ($alreadyReserved) {
                return;
            }

            \App\Services\InventoryService::apply(
                variant: $item->variant,
                quantity: $item->quantity,
                type: \App\Enums\Store\InventoryMovementType::RESERVE,
                source: $item->order,
                user: auth()->user()
            );
        });
    }
}
