<?php

use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Services\DeliveryRiderService;
use App\Domains\Shipping\Services\NoestTrackingSyncService;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\updated;

layout('components.layouts.store');

state([
    'filters' => [
        'provider' => null,
        'tracking_statuses' => [],
        'date_from' => null,
        'date_to' => null,
    ],
    'search' => '',
    'filteredTotal' => 0,
    'shipments' => [],
    'page' => 1,
    'perPage' => 20,
    'allProviders' => [],
    'stats' => ['active' => 0, 'delivered_today' => 0, 'returned_today' => 0],

    // Drawer
    'drawerOrderId' => null,
    'drawerTracking' => null,
    'drawerStatusHistories' => [],
    'drawerEvents' => [],
    'canViewDrawerEvents' => false,

    // Carrier note composer (P33.2)
    'noteDraft' => '',
    'sendingNote' => false,

    // Tracking-status popup (P29.4)
    'statusHistoryFor' => null,
    'statusHistory' => [],
    'statusHistoryMeta' => null,

    // Phase A — active tab: 'carrier' (shipping companies) | 'rider' (delivery rider)
    'trackingTab' => 'carrier',

    // Phase D — delivery-rider tab (store side) + drawer assignment
    'allRiders' => [],
    'riderRiders' => [],
    'selectedRiderId' => null,
    'riderShipments' => [],
    'riderShipmentTotal' => 0,
]);

$loadShipments = function (): void {
    $storeId = currentStoreId();
    $f = $this->filters;

    $query = Order::query()
        ->with([
            'customer',
            'status',
            'latestTracking.shippingProvider',
            'shippingProvider',
            'items.product',
            'city',
            'state',
        ]);

    if (! empty($f['provider'])) {
        $query->where('shipping_provider_id', $f['provider']);
    }

    if (! empty($this->search)) {
        $s = trim($this->search);
        $query->where(function ($q) use ($s) {
            $q->where('number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"))
                ->orWhereHas('customer', fn ($cq) => $cq->where('phone', 'like', "%{$s}%"))
                ->orWhere('phone_secondary', 'like', "%{$s}%")
                ->orWhereHas('latestTracking', fn ($tq) => $tq->where('tracking_number', 'like', "%{$s}%"))
                ->orWhereHas('shippingProvider', fn ($pq) => $pq->where('name', 'like', "%{$s}%"));
        });
    }

    if (! empty($f['tracking_statuses'])) {
        $query->whereHas('latestTracking', fn ($q) => $q->whereIn('tracking_status', $f['tracking_statuses']));
    }

    if (! empty($f['date_from'])) {
        $query->where('created_at', '>=', $f['date_from']);
    }

    if (! empty($f['date_to'])) {
        $query->where('created_at', '<=', $f['date_to'] . ' 23:59:59');
    }

    $trackingStatusIds = \App\Models\Status::system()->forType('order')
        ->whereIn('key', \App\Domains\Order\Support\OrderWorkflow::carrier())
        ->pluck('id')->all();

    if (! empty($trackingStatusIds)) {
        $query->whereIn('status_id', $trackingStatusIds);
    }

    $paginated = $query->orderByDesc('created_at')->paginate($this->perPage, ['*'], 'page', $this->page);

    $this->filteredTotal = $paginated->total();

    $this->shipments = collect($paginated->items())
        ->map(function (Order $order) {
            $tracking = $order->latestTracking;

            return [
                'id' => $order->id,
                'number' => $order->number,
                'customer' => $order->customer?->name ?? '—',
                'phone' => $order->customer?->phone ?? $order->phone ?? '—',
                'total' => currency($order->total_amount),
                'city' => $order->city?->name ?? ($order->state?->name ?? '—'),
                'provider' => $order->shippingProvider?->name ?? '—',
                'tracking_number' => $tracking?->tracking_number,
                'tracking_status' => $tracking?->tracking_status ?? null,
                'shipped_at' => $tracking?->shipped_at,
                'status_key' => $order->status?->key ?? null,
                'status_color' => $order->status?->color ?? 'gray',
            ];
        })
        ->all();

    $this->stats = [
        'active' => OrderTracking::where('store_id', $storeId)
            ->whereNull('delivered_at')
            ->whereNull('returned_at')
            ->count(),
        'delivered_today' => OrderTracking::where('store_id', $storeId)
            ->whereDate('delivered_at', today())
            ->count(),
        'returned_today' => OrderTracking::where('store_id', $storeId)
            ->whereDate('returned_at', today())
            ->count(),
    ];
};

$refresh = function (): void {
    $this->loadShipments();
};

$clearFilters = function (): void {
    $this->search = '';
    $this->filters = [
        'provider' => null,
        'tracking_statuses' => [],
        'date_from' => null,
        'date_to' => null,
    ];
    $this->page = 1;
    $this->loadShipments();
};

$setFilter = function (string $key, $value): void {
    $this->filters[$key] = $value;
    $this->page = 1;
    $this->loadShipments();
};

$toggleTrackingStatus = function (string $value): void {
    $current = $this->filters['tracking_statuses'] ?? [];
    $this->filters['tracking_statuses'] = in_array($value, $current, true)
        ? array_values(array_diff($current, [$value]))
        : array_merge($current, [$value]);
    $this->page = 1;
    $this->loadShipments();
};

updated([
    'search' => function (): void {
        $this->page = 1;
        $this->loadShipments();
    },
    'filters.date_from' => function (): void {
        $this->page = 1;
        $this->loadShipments();
    },
    'filters.date_to' => function (): void {
        $this->page = 1;
        $this->loadShipments();
    },
    'trackingTab' => function (): void {
        if ($this->trackingTab === 'rider') {
            $this->loadRiderOverview();
        }
    },
]);

$loadDrawerHistories = function (string $trackingId): void {
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
};

$openDrawer = function (string $orderId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

    $order = Order::where('store_id', currentStoreId())
        ->with(['customer', 'status', 'shippingProvider.carrier', 'deliveryRider', 'city', 'state'])
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
        'city' => $order->city?->name ?? '—',
        'address' => $order->address,
        'provider' => $order->shippingProvider?->name ?? ($order->deliveryRider?->name ?? '—'),
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
};

$closeDrawer = function (): void {
    $this->drawerOrderId = null;
    $this->drawerTracking = null;
    $this->drawerStatusHistories = [];
    $this->drawerEvents = [];
    $this->canViewDrawerEvents = false;
};

// ——— Tracking-status popup (P29.4) — opened from the tracking-status column/card. --—
$openStatusHistory = function (string $orderId): void {
    $order = Order::where('store_id', currentStoreId())
        ->with('latestTracking.shippingProvider')
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
    ];
};

$closeStatusHistory = function (): void {
    $this->statusHistoryFor = null;
    $this->statusHistory = [];
    $this->statusHistoryMeta = null;
};

// ——— Phase C — automatic carrier sync (no manual status transitions). ———
$syncTracking = function (string $trackingId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

    $tracking = OrderTracking::where('store_id', currentStoreId())
        ->with('shippingProvider')
        ->find($trackingId);

    if (! $tracking) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.tracking_sync_failed')]);
        return;
    }

    $result = app(NoestTrackingSyncService::class)->syncOne($tracking);

    if (($result['ok'] ?? false)) {
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.tracking_synced')]);
    } else {
        \Illuminate\Support\Facades\Log::warning("tracking sync failed for [{$tracking->tracking_number}]: " . ($result['error'] ?? 'unknown'));
        $this->dispatch('swal:toast', [
            'icon' => 'warning',
            'title' => $result['error'] === 'no_number'
                ? __('order_flow.tracking_no_number')
                : __('order_flow.tracking_sync_failed'),
        ]);
        return;
    }

    $this->loadShipments();

    if ($this->drawerOrderId === (string) $tracking->order_id) {
        $this->openDrawer((string) $tracking->order_id);
    }
};

// ——— Carrier note composer (P33.2) ———
$sendCarrierNote = function (string $trackingId): void {
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
                'store_id'                 => $tracking->store_id,
                'order_id'                 => $tracking->order_id,
                'order_tracking_id'        => $tracking->id,
                'status'                   => 'carrier_note',
                'changed_by_membership_id' => currentMembership()?->id,
                'notes'                    => (string) $this->noteDraft,
                'payload'                  => ['carrier_response' => $result],
                'created_at'               => now(),
            ]);

            $this->noteDraft = '';

            $this->loadDrawerHistories($tracking->id);

            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => ($result['message'] ?? __('order_flow.note_sent'))]);
        } else {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => ($result['message'] ?? __('order_flow.note_failed'))]);
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::warning("carrier note failed for tracking [{$tracking->tracking_number}]: " . $e->getMessage());
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
    } finally {
        $this->sendingNote = false;
    }
};

// ——— Phase D — delivery-rider tab (store side) + drawer assignment. ———
$loadRiderOverview = function (): void {
    $storeId = currentStoreId();

    $workflowStatusIds = \App\Models\Status::system()->forType('order')
        ->whereIn('key', \App\Domains\Order\Support\OrderWorkflow::carrier())
        ->pluck('id');

    $shipments = Order::query()
        ->where('store_id', $storeId)
        ->whereNotNull('delivery_rider_id')
        ->whereIn('status_id', $workflowStatusIds)
        ->with('latestTracking')
        ->get(['id', 'delivery_rider_id']);

    $grouped = $shipments->groupBy('delivery_rider_id')->map(fn ($rows) => [
        'total' => $rows->count(),
        'active' => $rows->filter(fn ($o) => ! $o->latestTracking || (! $o->latestTracking->delivered_at && ! $o->latestTracking->returned_at))->count(),
    ]);

    $this->riderRiders = app(DeliveryRiderService::class)->listForStore($storeId)
        ->map(function (DeliveryRider $rider) use ($grouped) {
            $counts = $grouped->get($rider->id, ['total' => 0, 'active' => 0]);

            return [
                'id' => $rider->id,
                'name' => $rider->name,
                'phone' => $rider->phone,
                'vehicle_label' => $rider->vehicle_label,
                'is_active' => (bool) $rider->is_active,
                'total' => $counts['total'],
                'active' => $counts['active'],
            ];
        })
        ->values()
        ->all();

    $this->allRiders = array_map(fn ($r) => [
        'id' => $r['id'],
        'name' => $r['name'],
        'phone' => $r['phone'],
        'vehicle_label' => $r['vehicle_label'],
        'is_active' => $r['is_active'],
    ], $this->riderRiders);

    $this->selectedRiderId = null;
    $this->riderShipments = [];
    $this->riderShipmentTotal = 0;
};

$toggleRider = function (string $riderId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

    if ($this->selectedRiderId === $riderId) {
        $this->selectedRiderId = null;
        $this->riderShipments = [];
        $this->riderShipmentTotal = 0;
        return;
    }

    $this->loadRiderShipments($riderId);
};

$loadRiderShipments = function (string $riderId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

    $storeId = currentStoreId();
    $rider = app(DeliveryRiderService::class)->findForStore($riderId, $storeId);

    if (! $rider) {
        return;
    }

    $workflowStatusIds = \App\Models\Status::system()->forType('order')
        ->whereIn('key', \App\Domains\Order\Support\OrderWorkflow::carrier())
        ->pluck('id');

    $orders = Order::query()
        ->with(['customer', 'status', 'latestTracking', 'deliveryRider', 'city', 'state'])
        ->where('store_id', $storeId)
        ->where('delivery_rider_id', $riderId)
        ->whereIn('status_id', $workflowStatusIds)
        ->orderByDesc('created_at')
        ->limit(200)
        ->get();

    $this->selectedRiderId = $riderId;
    $this->riderShipmentTotal = $orders->count();

    $this->riderShipments = $orders
        ->map(function (Order $order) {
            $tracking = $order->latestTracking;

            return [
                'id' => $order->id,
                'number' => $order->number,
                'customer' => $order->customer?->name ?? '—',
                'phone' => $order->customer?->phone ?? $order->phone ?? '—',
                'total' => currency($order->total_amount),
                'city' => $order->city?->name ?? ($order->state?->name ?? '—'),
                'provider' => $order->deliveryRider?->name ?? '—',
                'tracking_number' => $tracking?->tracking_number,
                'tracking_status' => $tracking?->tracking_status ?? null,
                'shipped_at' => $tracking?->shipped_at,
                'status_key' => $order->status?->key ?? null,
                'status_color' => $order->status?->color ?? 'gray',
            ];
        })
        ->all();
};

$assignRider = function (string $orderId, ?string $riderId = null): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_ASSIGN->value), 403);

    $order = Order::where('store_id', currentStoreId())->find($orderId);

    if (! $order) {
        return;
    }

    if ($riderId !== null && $order->shipping_provider_id) {
        $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.rider_has_provider')]);
        return;
    }

    if ($riderId !== null) {
        $rider = app(DeliveryRiderService::class)->findForStore($riderId, currentStoreId());

        if (! $rider) {
            return;
        }
    }

    $order->update(['delivery_rider_id' => $riderId]);

    $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.rider_saved')]);

    $this->loadRiderOverview();
    $this->loadShipments();

    if ($this->drawerOrderId === $orderId) {
        $this->openDrawer($orderId);
    }
};

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

    $storeId = currentStoreId();

    $this->allProviders = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', $storeId)
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name'])
        ->toArray();

    $this->loadRiderOverview();
    $this->loadShipments();
});
?>

<div>
    {{-- Page Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <x-edz.page-header title="{{ __('order_flow.tracking_page_title') }}"
            description="{{ __('order_flow.tracking_page_subtitle') }}">
        </x-edz.page-header>
        <div class="flex items-center gap-2">
            <button wire:click="refresh" class="edz-btn edz-btn--ghost edz-btn--sm" wire:loading.attr="disabled"
                wire:loading.class="opacity-50 pointer-events-none" wire:target="refresh">
                <x-edz.icon name="arrow-path" wire:loading.remove wire:target="refresh" class="w-4 h-4" />
                <x-edz.spinner wire:target="refresh" class="w-4 h-4" />
            </button>
        </div>
    </div>

    {{-- Phase A — carrier/rider tabs --}}
    @include('livewire.merchant.tracking.partials.tracking-tabs')

    @if ($this->trackingTab === 'carrier')
        @include('livewire.merchant.tracking.partials.tracking-stats')

        @include('livewire.merchant.tracking.partials.tracking-toolbar')

        @if (! empty($this->filteredTotal))
            <div class="mb-3 flex items-center gap-2 text-sm text-ink-muted">
                <span>{{ $this->filteredTotal }} {{ __('order_flow.tracking_count') }}</span>
            </div>
        @endif

        {{-- Desktop table + mobile cards + load-more --}}
        @include('livewire.merchant.tracking.partials.tracking-list')
    @else
        @include('livewire.merchant.tracking.partials.tracking-rider-tab')
    @endif

    {{-- Shipment Drawer — extracted partial (P29.4) --}}
    @include('livewire.merchant.tracking.partials.order-drawer')

    {{-- Tracking-status popup (P29.4) — extracted partial --}}
    @include('livewire.merchant.tracking.partials.tracking-history-popup')
</div>