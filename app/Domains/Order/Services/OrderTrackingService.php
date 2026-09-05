<?php

namespace App\Domains\Order\Services;

use App\Enums\Store\OrderTrackingStatus;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use Illuminate\Support\Str;

class OrderTrackingService
{
    /**
     * Create a new tracking record for an order being shipped.
     * Idempotent: returns existing open tracking if one exists.
     */
    public function startShipment(Order $order, ?string $trackingNumber = null, ?int $actorMembershipId = null): OrderTracking
    {
        $open = $this->currentOpenTracking($order);

        if ($open) {
            return $open;
        }

        $tracking = $order->trackings()->create([
            'store_id' => $order->store_id,
            'shipping_provider_id' => $order->shipping_provider_id,
            'tracking_number' => $trackingNumber,
            'tracking_status' => OrderTrackingStatus::SHIPPED->value,
            'shipped_at' => now(),
            'webhook_token' => Str::random(40),
        ]);

        $this->recordHistory(
            $tracking,
            OrderTrackingStatus::SHIPPED->value,
            $actorMembershipId,
            null,
            ['shipment_started' => true],
            $order,
        );

        return $tracking;
    }

    /**
     * Mark the order's currently open tracking record as delivered.
     */
    public function markDelivered(Order $order, ?int $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::DELIVERED->value, $actorMembershipId, $notes, [
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark the order's currently open tracking record as returned.
     */
    public function markReturned(Order $order, ?int $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::RETURNED->value, $actorMembershipId, $notes, [
            'returned_at' => now(),
        ]);
    }

    /**
     * Mark the order's currently open tracking record as being returned
     * (in transit back) without a final outcome yet.
     */
    public function markReturning(Order $order, ?int $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::RETURNING->value, $actorMembershipId, $notes, [
            'returned_at' => now(),
        ]);
    }

    /**
     * Mark the order's currently open tracking record as lost (terminal).
     */
    public function markLost(Order $order, ?int $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::LOST->value, $actorMembershipId, $notes);
    }

    /**
     * Mark the order's currently open tracking record as damaged (terminal).
     */
    public function markDamaged(Order $order, ?int $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::DAMAGED->value, $actorMembershipId, $notes);
    }

    /**
     * Record a failed delivery attempt (non-terminal).
     */
    public function markFailedAttempt(Order $order, ?int $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::FAILED_ATTEMPT->value, $actorMembershipId, $notes);
    }

    /**
     * Mark an order currently out for delivery / in transit.
     */
    public function markInTransit(Order $order, ?int $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::IN_TRANSIT->value, $actorMembershipId, $notes);
    }

    /**
     * Mark an order currently out for delivery.
     */
    public function markOutForDelivery(Order $order, ?int $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::OUT_FOR_DELIVERY->value, $actorMembershipId, $notes);
    }

    protected function applyStatus(
        Order $order,
        string $newStatus,
        ?int $actorMembershipId,
        ?string $notes,
        array $extra = [],
    ): ?OrderTracking {
        $tracking = $this->currentOpenTracking($order);

        if (! $tracking) {
            return null;
        }

        $previous = $tracking->tracking_status;

        $tracking->update(array_merge([
            'tracking_status' => $newStatus,
        ], $extra));

        $this->recordHistory($tracking, $newStatus, $actorMembershipId, $notes, [
            'previous_status' => $previous,
            'terminal' => OrderTrackingStatus::tryFrom($newStatus)?->isTerminal() ?? false,
        ], $order);

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

    protected function recordHistory(
        OrderTracking $tracking,
        string $status,
        ?int $actorMembershipId,
        ?string $notes,
        ?array $payload,
        Order $order,
    ): OrderTrackingHistory {
        $history = OrderTrackingHistory::create([
            'store_id'                  => $tracking->store_id,
            'order_id'                  => $tracking->order_id,
            'order_tracking_id'         => $tracking->id,
            'status'                    => $status,
            'changed_by_membership_id'  => $actorMembershipId,
            'notes'                     => $notes,
            'payload'                   => $payload,
            'created_at'                => now(),
        ]);

        $actor = null;
        if ($actorMembershipId) {
            $actor = \App\Models\Stores\Team\StoreMembership::find($actorMembershipId);
        }

        app(OrderAuditService::class)->tracking(
            $order,
            $status,
            $tracking->tracking_number,
            $actor,
        );

        return $history;
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