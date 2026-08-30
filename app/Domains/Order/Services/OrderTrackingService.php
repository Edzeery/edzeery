<?php

namespace App\Domains\Order\Services;

use App\Enums\Store\OrderTrackingStatus;
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
        $open = $this->currentOpenTracking($order);

        if ($open) {
            return $open;
        }

        return $order->trackings()->create([
            'store_id' => $order->store_id,
            'shipping_provider_id' => $order->shipping_provider_id,
            'tracking_number' => $trackingNumber,
            'tracking_status' => OrderTrackingStatus::SHIPPED->value,
            'shipped_at' => now(),
            'webhook_token' => Str::random(40),
        ]);
    }

    /**
     * Mark the order's currently open tracking record as delivered.
     */
    public function markDelivered(Order $order): ?OrderTracking
    {
        $tracking = $this->currentOpenTracking($order);

        if (! $tracking) {
            return null;
        }

        $tracking->update([
            'tracking_status' => OrderTrackingStatus::DELIVERED->value,
            'delivered_at' => now(),
        ]);

        return $tracking;
    }

    /**
     * Mark the order's currently open tracking record as returned.
     */
    public function markReturned(Order $order): ?OrderTracking
    {
        $tracking = $this->currentOpenTracking($order);

        if (! $tracking) {
            return null;
        }

        $tracking->update([
            'tracking_status' => OrderTrackingStatus::RETURNED->value,
            'returned_at' => now(),
        ]);

        return $tracking;
    }

    /**
     * Mark the order's currently open tracking record as being returned
     * (in transit back) without a final outcome yet.
     */
    public function markReturning(Order $order): ?OrderTracking
    {
        $tracking = $this->currentOpenTracking($order);

        if (! $tracking) {
            return null;
        }

        $tracking->update([
            'tracking_status' => OrderTrackingStatus::RETURNING->value,
            'returned_at' => now(),
        ]);

        return $tracking;
    }

    /**
     * Mark the order's currently open tracking record as lost (terminal).
     */
    public function markLost(Order $order): ?OrderTracking
    {
        $tracking = $this->currentOpenTracking($order);

        if (! $tracking) {
            return null;
        }

        $tracking->update([
            'tracking_status' => OrderTrackingStatus::LOST->value,
        ]);

        return $tracking;
    }

    /**
     * Mark the order's currently open tracking record as damaged (terminal).
     */
    public function markDamaged(Order $order): ?OrderTracking
    {
        $tracking = $this->currentOpenTracking($order);

        if (! $tracking) {
            return null;
        }

        $tracking->update([
            'tracking_status' => OrderTrackingStatus::DAMAGED->value,
        ]);

        return $tracking;
    }

    /**
     * Record a failed delivery attempt (non-terminal).
     */
    public function markFailedAttempt(Order $order): ?OrderTracking
    {
        $tracking = $this->currentOpenTracking($order);

        if (! $tracking) {
            return null;
        }

        $tracking->update([
            'tracking_status' => OrderTrackingStatus::FAILED_ATTEMPT->value,
        ]);

        return $tracking;
    }

    /**
     * The most relevant tracking record: latest open, or latest overall.
     */
    public function currentTracking(Order $order): ?OrderTracking
    {
        return $this->currentOpenTracking($order)
            ?? $order->trackings()->latest('created_at')->first();
    }

    protected function currentOpenTracking(Order $order): ?OrderTracking
    {
        return $order->trackings()
            ->whereNull('delivered_at')
            ->whereNull('returned_at')
            ->latest('created_at')
            ->first();
    }
}
