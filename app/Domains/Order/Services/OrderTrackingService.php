<?php

namespace App\Domains\Order\Services;

use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use Illuminate\Support\Str;

class OrderTrackingService
{
    /**
     * Create a new tracking record for an order being shipped.
     * Idempotent: returns existing open tracking if one exists.
     */
    public function startShipment(Order $order, ?string $trackingNumber = null): OrderTracking
    {
        $open = $order->trackings()
            ->whereNull('delivered_at')
            ->whereNull('returned_at')
            ->latest('created_at')
            ->first();

        if ($open) {
            return $open;
        }

        return $order->trackings()->create([
            'store_id'              => $order->store_id,
            'shipping_provider_id'  => $order->shipping_provider_id,
            'tracking_number'       => $trackingNumber,
            'shipped_at'            => now(),
            'webhook_token'         => Str::random(40),
        ]);
    }

    /**
     * Mark the order's currently open tracking record as delivered.
     */
    public function markDelivered(Order $order): ?OrderTracking
    {
        $tracking = $this->currentTracking($order);

        if (! $tracking) {
            return null;
        }

        $tracking->update(['delivered_at' => now()]);

        return $tracking;
    }

    /**
     * Mark the order's currently open tracking record as returned.
     */
    public function markReturned(Order $order): ?OrderTracking
    {
        $tracking = $this->currentTracking($order);

        if (! $tracking) {
            return null;
        }

        $tracking->update(['returned_at' => now()]);

        return $tracking;
    }

    /**
     * The most relevant tracking record: latest open, or latest overall.
     */
    public function currentTracking(Order $order): ?OrderTracking
    {
        return $order->trackings()
            ->whereNull('delivered_at')
            ->whereNull('returned_at')
            ->latest('created_at')
            ->first()
            ?? $order->trackings()->latest('created_at')->first();
    }
}
