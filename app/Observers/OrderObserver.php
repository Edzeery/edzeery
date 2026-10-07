<?php

namespace App\Observers;

use App\Domains\Order\Services\OrderAuditService;
use App\Domains\Order\Support\OrderStatusCapture;
use App\Domains\Status\Support\OrderStatusStage;
use App\Enums\Store\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Orders\Order;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Status;
use App\Models\Stores\Team\StoreMembership;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class OrderObserver
{
    /** الحقول المفتاحية التي تُسجَّل عند تعديلها في سجل الأحداث (دون ضجيج المجالات المتغيرة). */
    protected const TRACKED_FIELDS = [
        'customer_id',
        'phone',
        'phone_secondary',
        'address',
        'city_id',
        'stopdesk_point_id',
        'delivery_type',
        'shipping_provider_id',
        'delivery_rider_id',
        'total_amount',
        'notes',
    ];

    /**
     * Before creating order
     */
    public function creating(Order $order): void
    {
        if (! $order->status_id) {
            $status = Status::system()
                ->forType('order')
                ->where('key', 'pending')
                ->first();

            $order->status_id = $status?->id;
        }

        if (! $order->number) {
            $order->number = $this->generateOrderNumber($order);
        }
    }

    public function created(Order $order): void
    {
        app(OrderAuditService::class)->created($order);
    }

    /**
     * After updating order
     */
    public function updated(Order $order): void
    {
        $this->recordFieldChanges($order);

        if (! $order->wasChanged('status_id')) {
            return;
        }

        $this->handleStatusChange($order);
    }

    protected function recordFieldChanges(Order $order): void
    {
        if (empty($order->getChanges())) {
            return;
        }

        $changes = [];
        foreach (self::TRACKED_FIELDS as $field) {
            if (! array_key_exists($field, $order->getChanges())) {
                continue;
            }

            $changes[$field] = [
                'from' => $order->getOriginal($field),
                'to' => $order->getAttribute($field),
            ];
        }

        if (empty($changes)) {
            return;
        }

        $actor = $this->currentMembership();

        app(OrderAuditService::class)->fieldChanges($order, $changes, $actor);
    }

    protected function handleStatusChange(Order $order): void
    {
        // Force a fresh load: $order->status_id was just updated by
        // transitionToStatus(), but the cached relationship still
        // points to the previous status.
        $order->unsetRelation('status');
        $status = $order->status;

        if (! $status) {
            return;
        }

        $meta = Order::popTransitionMeta($order->id) ?? [];

        // PHASE 38-C — provenance: every history row keeps what it transitioned
        // FROM (NULL on creation) and WHO/WHAT triggered it (source bucket,
        // defaulting to the automated/system bucket for unlabelled internal
        // flows; every UI and bulk path passes an explicit source).
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status_id' => $status->id,
            'from_status' => $meta['from_key'] ?? null,
            'source' => $meta['source'] ?? 'system',
            'changed_by_membership_id' => $meta['changed_by_membership_id'] ?? null,
            'reason' => $meta['reason'] ?? null,
        ]);

        // PHASE 38-C — first-write-wins event stamps (C.1/C.2). Atomic CAS
        // (WHERE <column> IS NULL) inside the SAME transaction as the status
        // change, so a re-transition can never rewrite a stamp and two
        // concurrent confirmations produce exactly one winner. A missing actor
        // leaves the actor column NULL on purpose: "unattributed", never
        // silently credited.
        $actorId = $meta['changed_by_membership_id'] ?? null;

        if (OrderStatusStage::isEarningConfirmation($status->key)) {
            OrderStatusCapture::stampConfirmation($order, $actorId, now());
        }

        match ($status->key) {
            'delivered' => OrderStatusCapture::stampDelivered($order, now()),
            'returned' => OrderStatusCapture::stampReturned($order, now(), $meta['return_reason_key'] ?? null),
            default => null,
        };

        $actor = ! empty($meta['changed_by_membership_id'])
            ? StoreMembership::find($meta['changed_by_membership_id'])
            : $this->currentMembership();

        app(OrderAuditService::class)->statusChanged(
            $order,
            $meta['from_key'] ?? null,
            $status->key,
            $meta['reason'] ?? null,
            $actor,
        );

        $this->syncTracking($order, $status, $actor?->id);

        if (! $status->affects_inventory || empty($status->movement_type)) {
            return;
        }

        if (! \App\Domains\Cart\Support\OrderRules::tracksInventory($order->store)) {
            return;
        }

        $movementType = InventoryMovementType::tryFrom($status->movement_type);
        if (! $movementType || ! $movementType->affectsStock()) {
            return;
        }

        DB::transaction(function () use ($order, $movementType, $meta) {
            $actorUser = null;
            if (! empty($meta['changed_by_membership_id'])) {
                $membership = StoreMembership::find($meta['changed_by_membership_id']);
                $actorUser = $membership?->user;
            }

            foreach ($order->items as $item) {
                $variant = $item->variant;

                if (! $variant) {
                    continue;
                }

                // Idempotency: skip if this exact movement already exists
                $alreadyApplied = InventoryMovement::query()
                    ->where('source_type', Order::class)
                    ->where('source_id', $order->id)
                    ->where('product_variant_id', $variant->id)
                    ->where('type', $movementType->value)
                    ->exists();

                if ($alreadyApplied) {
                    continue;
                }

                InventoryService::apply(
                    variant: $variant,
                    quantity: $item->quantity,
                    type: $movementType,
                    source: $order,
                    user: $actorUser ?? auth()->user()
                );
            }
        });
    }

    protected function generateOrderNumber(Order $order): string
    {
        $store = \App\Models\Stores\Store::lockForUpdate()->find($order->store_id);
        $lastNumber = $store
            ? Order::withTrashed()->where('store_id', $order->store_id)->max(DB::raw('CAST(number AS UNSIGNED)'))
            : 0;

        $nextNumber = $lastNumber ? ((int) $lastNumber + 1) : 1;

        return str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }

    protected function syncTracking(Order $order, Status $status, ?string $actorMembershipId = null): void
    {
        $service = app(\App\Domains\Order\Services\OrderTrackingService::class);

        match ($status->key) {
            'shipped' => $service->startShipment($order, null, $actorMembershipId),
            'delivered' => $service->markDelivered($order, $actorMembershipId),
            'returned' => $service->markReturned($order, $actorMembershipId),
            default => null,
        };
    }

    protected function currentMembership(): ?StoreMembership
    {
        if (! function_exists('currentStoreId')) {
            return null;
        }

        $storeId = currentStoreId();

        if (! $storeId || ! auth()->check()) {
            return null;
        }

        return StoreMembership::where('store_id', $storeId)
            ->where('user_id', auth()->id())
            ->first();
    }
}
