<?php

namespace App\Domains\Order\Services;

use App\Enums\Store\OrderTrackingStatus;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use App\Models\Stores\Store;
use Illuminate\Support\Str;

class OrderTrackingService
{
    /**
     * Create a new tracking record for an order being shipped.
     * Idempotent: returns existing open tracking if one exists.
     */
    public function startShipment(Order $order, ?string $trackingNumber = null, int|string|null $actorMembershipId = null): OrderTracking
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
     * Unique, rider-scoped tracking number printed on the label as a scannable
     * Code128 barcode. Format: {STORE-3}-{HM|SD}-{6 digits}, e.g. EDZ-HM-402731.
     * The prefix is the first 3 ASCII letters of the store name (shorter names
     * repeat their last letter; a name with no ASCII letters falls back to the
     * slug's letters) and HM/SD keeps the home-vs-stopdesk delivery marker.
     * Uniqueness is guarded against every other tracking number of the store.
     */
    public function generateRiderTrackingNumber(Order $order): string
    {
        $prefix = $this->storePrefix($order->store);
        $type = $order->delivery_type === 'stopdesk' ? 'SD' : 'HM';

        do {
            $candidate = $prefix . '-' . $type . '-' . random_int(100000, 999999);
        } while (OrderTracking::where('store_id', $order->store_id)->where('tracking_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * 3-letter store prefix for rider tracking numbers. Code128-safe ASCII only:
     * name is uppercased, non-letters stripped and the first 3 letters kept; a
     * name shorter than 3 letters repeats its last letter ('Lo' → LOO); a name
     * with no ASCII letters (e.g. Arabic) falls back to the slug's letters, then
     * to 'STO'.
     */
    protected function storePrefix(Store $store): string
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($store->name));

        if ($letters === '') {
            $letters = preg_replace('/[^A-Z]/', '', strtoupper($store->slug));
        }

        if ($letters === '') {
            $letters = 'STO';
        }

        while (strlen($letters) < 3) {
            $letters .= substr($letters, -1);
        }

        return substr($letters, 0, 3);
    }

    /**
     * Ensure a rider hand-off has a tracking row + number so the rider tab is never
     * blank: creates a fresh open SHIPPED tracking when none exists, otherwise
     * backfills the number and, when the status is missing, a SHIPPED status on the
     * current open row. A rider hand-off is never carrier-probed, so any stale
     * shipping_provider_id is cleared. Idempotent.
     */
    public function ensureRiderTracking(Order $order, string $trackingNumber): OrderTracking
    {
        $open = $this->currentOpenTracking($order);

        if ($open) {
            $previous = $open->tracking_status;
            $changes = [];

            if (blank($open->tracking_number)) {
                $changes['tracking_number'] = $trackingNumber;
            }

            if (blank($open->tracking_status)) {
                $changes['tracking_status'] = OrderTrackingStatus::SHIPPED->value;
            }

            // Rider hand-offs are never carrier-probed: clear any stale carrier
            // provider so bulk sync (whereHas shippingProvider + open status)
            // never tries to refresh a local HM/SD rider number.
            if ($open->shipping_provider_id !== null) {
                $changes['shipping_provider_id'] = null;
            }

            if (! empty($changes)) {
                $open->update($changes);

                if (array_key_exists('tracking_status', $changes)) {
                    $this->recordHistory(
                        $open,
                        OrderTrackingStatus::SHIPPED->value,
                        null,
                        null,
                        ['previous_status' => $previous, 'rider_backfill' => true],
                        $order,
                    );
                }
            }

            return $open;
        }

        return $this->startShipment($order, $trackingNumber);
    }

    /**
     * Mark the order's currently open tracking record as delivered.
     */
    public function markDelivered(Order $order, int|string|null $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::DELIVERED->value, $actorMembershipId, $notes, [
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark the order's currently open tracking record as returned.
     */
    public function markReturned(Order $order, int|string|null $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::RETURNED->value, $actorMembershipId, $notes, [
            'returned_at' => now(),
        ]);
    }

    /**
     * Mark the order's currently open tracking record as being returned
     * (in transit back) without a final outcome yet.
     */
    public function markReturning(Order $order, int|string|null $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::RETURNING->value, $actorMembershipId, $notes, [
            'returned_at' => now(),
        ]);
    }

    /**
     * Mark the order's currently open tracking record as lost (terminal).
     */
    public function markLost(Order $order, int|string|null $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::LOST->value, $actorMembershipId, $notes);
    }

    /**
     * Mark the order's currently open tracking record as damaged (terminal).
     */
    public function markDamaged(Order $order, int|string|null $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::DAMAGED->value, $actorMembershipId, $notes);
    }

    /**
     * Record a failed delivery attempt (non-terminal).
     */
    public function markFailedAttempt(Order $order, int|string|null $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::FAILED_ATTEMPT->value, $actorMembershipId, $notes);
    }

    /**
     * Mark an order currently out for delivery / in transit.
     */
    public function markInTransit(Order $order, int|string|null $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::IN_TRANSIT->value, $actorMembershipId, $notes);
    }

    /**
     * Mark an order currently out for delivery.
     */
    public function markOutForDelivery(Order $order, int|string|null $actorMembershipId = null, ?string $notes = null): ?OrderTracking
    {
        return $this->applyStatus($order, OrderTrackingStatus::OUT_FOR_DELIVERY->value, $actorMembershipId, $notes);
    }

    protected function applyStatus(
        Order $order,
        string $newStatus,
        int|string|null $actorMembershipId,
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
        int|string|null $actorMembershipId,
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