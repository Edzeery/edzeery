<?php

namespace App\Livewire\Concerns;

use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Services\NoestTrackingSyncService;
use App\Domains\Shipping\Services\StopdeskOfficeSync;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;

/**
 * Shipment drawer, tracking-status popup, automatic carrier sync, carrier-note
 * composer, label printing and cancel-shipment — extracted out of
 * tracking/index.blade.php.
 */
trait TrackingDrawerConcern
{
    public function loadDrawerHistories(string $trackingId): void
    {
        $this->drawerStatusHistories = OrderTrackingHistory::where('store_id', currentStoreId())
            ->where('order_tracking_id', $trackingId)
            ->with('changedBy.user')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($h) => [
                'status' => $h->status,
                'notes' => $h->notes,
                'created_at' => $h->created_at,
                'by' => $h->changedBy?->user?->name ?? null,
            ])
            ->all();
    }

    public function openDrawer(string $orderId): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

        $order = Order::where('store_id', currentStoreId())
            ->with([
                'customer',
                'status',
                'shippingProvider.carrier',
                'deliveryRider',
                'city',
                'state',
                'items.product',
                'items.variant',
                'items.variant.optionValues.option',
            ])
            ->find($orderId);

        if (! $order) {
            return;
        }

        $tracking = app(OrderTrackingService::class)->currentTracking($order);

        $this->drawerOrderId = $orderId;

        $this->drawerTracking = [
            'tracking_id' => $tracking?->id,
            'order_id' => $order->id,
            'number' => $order->number,
            'customer' => $order->customer?->name ?? '—',
            'phone' => $order->customer?->phone ?? '—',
            'total' => currency($order->total_amount),
            'item_groups' => app(\App\Domains\Orders\Support\OrderItemsFormatter::class)
                ->toTableGroups($order->items)
                ->toArray(),
            'city' => $order->city?->name ?? '—',
            'state' => $order->state?->name ?? '—',
            'address' => $order->address,
            'provider' => $order->shippingProvider?->name ?? ($order->deliveryRider?->name ?? '—'),
            'provider_logo' => $order->shippingProvider?->carrier?->logo,
            'tracking_number' => $tracking?->tracking_number,
            'tracking_status' => $tracking?->tracking_status,
            'shipped_at' => $tracking?->shipped_at,
            'delivered_at' => $tracking?->delivered_at,
            'returned_at' => $tracking?->returned_at,
            'last_synced_at' => $tracking?->last_synced_at,
            'rider_id' => $order->delivery_rider_id,
            'rider_name' => $order->deliveryRider?->name,
            'has_provider' => (bool) $order->shipping_provider_id,
            'carrier_supports_api_notes' => (bool) ($order->shippingProvider?->carrier?->capabilityList()['api_notes'] ?? false),
            'carrier_supports_validation' => (bool) (
                $order->shippingProvider?->carrier
                && ($adapterClass = config(
                    "delivery.carrier_integrations.{$order->shippingProvider->carrier->code}",
                    config('delivery.carrier_integrations.*'),
                ))
                && is_string($adapterClass)
                && class_exists($adapterClass)
                && method_exists($adapterClass, 'validateOrder')
            ),
            'carrier_validated_at' => $tracking?->carrier_validated_at,
            'carrier_validation_error' => $tracking?->carrier_validation_error,
            'confirmed_by' => $order->confirmedByHistory?->changedBy?->user?->name ?? null,
            'assigned_to' => $order->assignedMembership?->user?->name ?? null,
        ];

        if ($tracking) {
            $this->loadDrawerHistories($tracking->id);
        }

        // P29.1 — Event log visibility: same rule as the orders details drawer.
        $currentMembership = \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())
            ->where('user_id', auth()->id())
            ->first();

        $this->canViewDrawerEvents = $currentMembership
            ? \App\Support\StoreOrderPermissions::canViewOrderEventLog($order, $currentMembership)
            : false;

        $this->drawerEvents = $this->canViewDrawerEvents
            ? OrderEvent::where('store_id', currentStoreId())
                ->where('order_id', $order->id)
                ->with('actor.user')
                ->orderByDesc('occurred_at')
                ->limit(15)
                ->get()
                ->toArray()
            : [];
    }

    public function closeDrawer(): void
    {
        $this->drawerOrderId = null;
        $this->drawerTracking = null;
        $this->drawerStatusHistories = [];
        $this->drawerEvents = [];
        $this->canViewDrawerEvents = false;
    }

    // ——— Tracking-status popup (P29.4, stepper Phase 7) — opened from the tracking-status column/card. --—
    public function openStatusHistory(string $orderId): void
    {
        $order = Order::where('store_id', currentStoreId())
            ->with('latestTracking.shippingProvider.carrier')
            ->find($orderId);

        if (! $order?->latestTracking) {
            return;
        }

        $tracking = $order->latestTracking;

        $this->statusHistoryFor = $orderId;
        $this->statusHistory = OrderTrackingHistory::where('store_id', currentStoreId())
            ->where('order_tracking_id', $tracking->id)
            ->with('changedBy.user')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($h) => [
                'status' => $h->status,
                'notes' => $h->notes,
                'created_at' => $h->created_at,
                'by' => $h->changedBy?->user?->name ?? null,
            ])
            ->all();
        $this->statusHistoryMeta = [
            'number' => $order->number,
            'tracking_number' => $tracking->tracking_number,
            'tracking_id' => $tracking->id,
            'tracking_status' => $tracking->tracking_status,
            'carrier_supports_api_notes' => (bool) ($tracking->shippingProvider?->carrier?->capabilityList()['api_notes'] ?? false),
            'public_tracking_url' => $tracking->tracking_number
                ? $tracking->shippingProvider?->carrier?->publicTrackingUrl((string) $tracking->tracking_number)
                : null,
        ];
    }

    public function closeStatusHistory(): void
    {
        $this->statusHistoryFor = null;
        $this->statusHistory = [];
        $this->statusHistoryMeta = null;
    }

    // ——— Phase C — automatic carrier sync (no manual status transitions). ———
    private const UNKNOWN_SYNC_LIST_CAP = 25;

    private function unknownTrackingItemHtml(OrderTracking $tracking): string
    {
        $orderNumber = (string) ($tracking->order?->number ?? $tracking->order_id);

        return strtr(e(__('order_flow.tracking_unknown_item')), [
            ':order' => '<bdi>'.e($orderNumber).'</bdi>',
            ':tracking' => '<bdi>'.e((string) $tracking->tracking_number).'</bdi>',
        ]);
    }

    private function unknownTrackingListHtml(\Illuminate\Support\Collection $trackings): string
    {
        $limited = $trackings->take(self::UNKNOWN_SYNC_LIST_CAP);

        $rows = $limited
            ->map(fn (OrderTracking $t) => '<div style="padding:.15rem 0">'.$this->unknownTrackingItemHtml($t).'</div>')
            ->implode('');

        if ($trackings->count() > self::UNKNOWN_SYNC_LIST_CAP) {
            $rows .= '<div style="padding:.15rem 0">'.e(__('order_flow.tracking_unknown_more', ['count' => $trackings->count() - self::UNKNOWN_SYNC_LIST_CAP])).'</div>';
        }

        return '<div style="max-height:42vh;overflow:auto;line-height:2;text-align:start">'.$rows.'</div>';
    }

    public function syncTracking(string $trackingId): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

        $tracking = OrderTracking::where('store_id', currentStoreId())
            ->with(['shippingProvider', 'order'])
            ->find($trackingId);

        if (! $tracking) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.tracking_sync_failed')]);

            return;
        }

        $result = app(NoestTrackingSyncService::class)->syncOne($tracking);

        if (($result['ok'] ?? false)) {
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.tracking_synced')]);
        } else {
            \Illuminate\Support\Facades\Log::warning("tracking sync failed for [{$tracking->tracking_number}]: ".($result['error'] ?? 'unknown'));

            if (($result['error'] ?? null) === 'no_data') {
                $this->dispatch('swal:toast', [
                    'icon' => 'warning',
                    'title' => __('order_flow.tracking_unknown_carrier'),
                    'html' => $this->unknownTrackingListHtml(collect([$tracking])),
                ]);
            } else {
                $this->dispatch('swal:toast', [
                    'icon' => 'warning',
                    'title' => ($result['error'] ?? null) === 'no_number'
                        ? __('order_flow.tracking_no_number')
                        : __('order_flow.tracking_sync_failed'),
                ]);
            }

            return;
        }

        $this->loadShipments();

        if ($this->drawerOrderId === (string) $tracking->order_id) {
            $this->openDrawer((string) $tracking->order_id);
        }
    }

    // ——— Bulk sync — refresh every open, adapter-backed tracking row at once. ———
    public function syncAllTracking(): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

        if ($this->showTrash) {
            return;
        }

        $resolver = new StopdeskOfficeSync();

        $trackings = OrderTracking::query()
            ->where('store_id', currentStoreId())
            ->whereNotNull('tracking_number')
            ->whereHas('shippingProvider')
            ->whereIn('tracking_status', collect(OrderTrackingStatus::open())->map(fn ($s) => $s->value)->all())
            ->with(['shippingProvider', 'order'])
            ->get()
            ->filter(fn (OrderTracking $t) => $resolver->resolve($t->shippingProvider) !== null);

        if ($trackings->isEmpty()) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.tracking_sync_none')]);

            return;
        }

        $service = app(NoestTrackingSyncService::class);

        $done = 0;
        $failed = 0;
        $unknown = collect();

        foreach ($trackings->groupBy('shipping_provider_id') as $tracks) {
            $provider = $tracks->first()->shippingProvider;

            $adapter = $resolver->resolve($provider);

            if (! $adapter || ! method_exists($adapter, 'trackingsInfo')) {
                $failed += $tracks->count();

                continue;
            }

            foreach ($tracks->chunk(20) as $chunk) {
                try {
                    $data = $adapter->trackingsInfo($provider, $chunk->pluck('tracking_number')->all());
                } catch (\Throwable $e) {
                    report($e);
                    $failed += $chunk->count();

                    continue;
                }

                if (! is_array($data)) {
                    $failed += $chunk->count();

                    continue;
                }

                foreach ($chunk as $tracking) {
                    $entry = $data[(string) $tracking->tracking_number] ?? null;

                    if (! is_array($entry)) {
                        $failed++;
                        $unknown->push($tracking);

                        continue;
                    }

                    $service->apply($tracking, $entry);
                    $done++;
                }
            }
        }

        $unknownList = $unknown->isNotEmpty() ? $this->unknownTrackingListHtml($unknown) : '';

        if ($failed === 0) {
            $icon = 'success';
            $title = __('order_flow.tracking_sync_bulk_done', ['done' => $done]);
        } elseif ($done === 0 && $unknown->count() === $failed) {
            $icon = 'warning';
            $title = __('order_flow.tracking_unknown_carrier');
        } else {
            $icon = 'warning';
            $title = __('order_flow.tracking_sync_bulk_failed', ['done' => $done, 'failed' => $failed]);
        }

        $this->dispatch('swal:toast', [
            'icon' => $icon,
            'title' => $title,
            ...($unknownList !== '' ? ['html' => $unknownList] : []),
        ]);

        $this->loadShipments();
    }

    // ——— Carrier note composer (P33.2) ———
    public function sendCarrierNote(string $trackingId): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

        $this->validate([
            'noteDraft' => ['required', 'string', 'max:255'],
        ]);

        $tracking = OrderTracking::where('store_id', currentStoreId())
            ->with('shippingProvider.carrier')
            ->find($trackingId);

        if (! $tracking || ! $tracking->shippingProvider?->carrier) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.note_failed')]);

            return;
        }

        // Server-side capability guard — the action must be unreachable when the
        // carrier can't take notes, not just hidden in the UI.
        if (! $tracking->shippingProvider->carrier->supports_api_notes) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.carrier_note_not_supported')]);

            return;
        }

        if (! $tracking->tracking_number) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.note_failed')]);

            return;
        }

        $this->sendingNote = true;

        try {
            $carrier = $tracking->shippingProvider->carrier;

            $adapterClass = config(
                "delivery.carrier_integrations.{$carrier->code}",
                config('delivery.carrier_integrations.*'),
            );

            $result = $adapterClass && class_exists($adapterClass)
                ? app($adapterClass)->addNote($tracking->shippingProvider, $tracking->tracking_number, (string) $this->noteDraft)
                : ['ok' => false, 'message' => __('order_flow.note_failed')];

            if (($result['ok'] ?? false)) {
                OrderTrackingHistory::create([
                    'store_id' => $tracking->store_id,
                    'order_id' => $tracking->order_id,
                    'order_tracking_id' => $tracking->id,
                    'status' => 'carrier_note',
                    'changed_by_membership_id' => currentMembership()?->id,
                    'notes' => (string) $this->noteDraft,
                    'payload' => ['carrier_response' => $result],
                    'created_at' => now(),
                ]);

                $this->noteDraft = '';

                $this->loadDrawerHistories($tracking->id);

                // The composer now lives in the status popup — keep its timeline in sync.
                if (filled($this->statusHistoryFor) && (string) $this->statusHistoryFor === (string) $tracking->order_id) {
                    $this->statusHistory = $this->drawerStatusHistories;
                }

                $this->dispatch('swal:toast', ['icon' => 'success', 'title' => ($result['message'] ?? __('order_flow.note_sent'))]);
            } else {
                $this->dispatch('swal:toast', ['icon' => 'error', 'title' => ($result['message'] ?? __('order_flow.note_failed'))]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("carrier note failed for tracking [{$tracking->tracking_number}]: ".$e->getMessage());
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
        } finally {
            $this->sendingNote = false;
        }
    }

    // ——— Carrier-note popup (notes column) — history + composer, same API as the drawer. ———
    public function openShipmentNotes(string $orderId): void
    {
        $order = Order::where('store_id', currentStoreId())
            ->with(['latestTracking.shippingProvider.carrier'])
            ->find($orderId);

        if (! $order?->latestTracking || ! $order->latestTracking->tracking_number) {
            return;
        }

        $tracking = $order->latestTracking;

        $this->shipmentNotesFor = $orderId;
        $this->shipmentNotes = OrderTrackingHistory::where('store_id', currentStoreId())
            ->where('order_tracking_id', $tracking->id)
            ->where('status', 'carrier_note')
            ->with('changedBy.user')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($h) => [
                'notes' => $h->notes,
                'created_at' => $h->created_at,
                'by' => $h->changedBy?->user?->name ?? null,
            ])
            ->all();
        $this->shipmentNotesMeta = [
            'number' => $order->number,
            'tracking_number' => $tracking->tracking_number,
            'tracking_id' => $tracking->id,
            'carrier_supports_api_notes' => (bool) ($tracking->shippingProvider?->carrier?->capabilityList()['api_notes'] ?? false),
        ];
    }

    public function closeShipmentNotes(): void
    {
        $this->shipmentNotesFor = null;
        $this->shipmentNotes = [];
        $this->shipmentNotesMeta = null;
    }

    // Compatibility shim: the shared order-form-modal duplicate-warning rows navigate
    // to order details via openOrderDetails(); here it maps to the tracking drawer.
    public function openOrderDetails(string $orderId): void
    {
        $this->openDrawer($orderId);
    }

    // ——— Label printing — carrier label is streamed through the auth proxy so the
    // bearer token never reaches the browser; otherwise we render our own sheet. ———
    public function openLabel(string $orderId): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

        $order = Order::where('store_id', currentStoreId())
            ->with([
                'customer',
                'state',
                'city',
                'items.product',
                'items.variant',
                'items.variant.optionValues.option',
                'shippingProvider.carrier',
                'deliveryRider',
                'latestTracking',
                'stopdeskPoint',
            ])
            ->find($orderId);

        if (! $order) {
            return;
        }

        $tracking = $order->latestTracking;

        // Preferred: the carrier's own printable label. The merchant proxy
        // re-attaches the bearer token server-side, so the URL is never public.
        if ($tracking?->tracking_number && $order->shippingProvider) {
            $adapter = (new StopdeskOfficeSync)->resolve($order->shippingProvider);

            if ($adapter && method_exists($adapter, 'getLabel')) {
                $label = $adapter->getLabel($order->shippingProvider, (string) $tracking->tracking_number);

                if (! empty($label['ok'])) {
                    $url = route('merchant.tracking.label', [
                        'store' => currentMembershipStore()->slug,
                        'tracking' => $tracking->tracking_number,
                    ]);

                    $this->dispatch('open-label', ['url' => $url]);

                    return;
                }
            }
        }

        // Fallback: our own label sheet — always works for rider orders, carriers
        // without a label endpoint, or carrier auth failures.
        $this->labelOpen = true;
        $this->labelOrderId = (string) $order->id;
        $this->labelData = [
            'number' => $order->number,
            'tracking_number' => $tracking?->tracking_number,
            'customer' => $order->customer?->name ?? '—',
            'phone' => $order->customer?->phone ?? ($order->phone ?? '—'),
            'address' => trim(implode(' - ', array_filter([
                $order->state?->name,
                $order->city?->name,
                $order->address,
            ]))),
            'delivery_type' => $order->delivery_type,
            'provider' => $order->shippingProvider?->name,
            'rider' => $order->deliveryRider?->name,
            'stopdesk' => $order->stopdeskPoint?->name,
            'total' => currency($order->total_amount),
            'items' => app(\App\Domains\Orders\Support\OrderItemsFormatter::class)
                ->toDetailedLines($order->items),
            'barcode' => $tracking?->tracking_number ?? $order->number,
        ];
    }

    public function closeLabel(): void
    {
        $this->labelOpen = false;
        $this->labelOrderId = null;
        $this->labelData = null;
    }

    public function cancelShipment(string $orderId, ?string $reason = null): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

        $order = Order::where('store_id', currentStoreId())
            ->with(['shippingProvider.carrier', 'status'])
            ->find($orderId);

        if (! $order) {
            return;
        }

        $result = app(\App\Domains\Shipping\Services\OrderShippingGateway::class)->cancel($order, $reason, currentMembership());

        if (($result['ok'] ?? false)) {
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => ($result['notice'] ?? null) === 'carrier_unknown'
                    ? __('order_flow.shipment_cancelled_unknown_carrier')
                    : __('order_flow.shipment_cancelled'),
            ]);
        } else {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => ($result['error'] ?? __('order_flow.shipment_cancellation_failed'))]);

            return;
        }

        $this->page = 1;
        $this->loadShipments();

        if ($this->drawerOrderId !== null && (string) $this->drawerOrderId === (string) $order->id) {
            $this->closeDrawer();
        }
    }

    // ——— Dispatch validation (Phase 36) — barcode handover to the carrier
    // logistics. Delegates to OrderShippingGateway::validate which persists
    // carrier_validated_at / carrier_validation_error and audits the event. ———
    public function validateShipment(string $orderId): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value), 403);

        $order = Order::where('store_id', currentStoreId())
            ->with(['shippingProvider.carrier'])
            ->find($orderId);

        if (! $order) {
            return;
        }

        $result = app(\App\Domains\Shipping\Services\OrderShippingGateway::class)->validate($order, currentMembership());

        if (($result['ok'] ?? false)) {
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => ($result['message'] ?: __('order_flow.shipment_validated')),
            ]);
        } else {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => ($result['message'] ?? $result['error'] ?? __('order_flow.shipment_validation_failed')),
            ]);

            return;
        }

        $this->loadShipments();

        if ($this->drawerOrderId !== null && (string) $this->drawerOrderId === (string) $order->id) {
            $this->openDrawer((string) $order->id);
        }
    }

    /**
     * Barcode path of the same handover: the scanned tracking must equal the
     * open drawer's current tracking number before validating.
     */
    public function validateShipmentFromBarcode(string $trackingNumber): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value), 403);

        $orderId = $this->drawerOrderId;

        if (! $orderId) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.validation_tracking_required')]);

            return;
        }

        $order = Order::where('store_id', currentStoreId())
            ->with(['shippingProvider.carrier'])
            ->find($orderId);

        if (! $order) {
            return;
        }

        $tracking = app(OrderTrackingService::class)->currentTracking($order);

        if (! $tracking?->tracking_number || (string) $tracking->tracking_number !== trim($trackingNumber)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('order_flow.validate_barcode_mismatch')]);

            return;
        }

        $this->validateShipment((string) $order->id);
    }
}
