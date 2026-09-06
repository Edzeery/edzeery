<?php

use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Services\OrderTrackingService;
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

layout('components.layouts.store');

state([
    'filters' => [
        'provider' => null,
        'tracking_status' => null,
        'date_from' => null,
        'date_to' => null,
    ],
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

    // Tracking-status popup (P29.4)
    'statusHistoryFor' => null,
    'statusHistory' => [],
    'statusHistoryMeta' => null,
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

    if (! empty($f['tracking_status'])) {
        $query->whereHas('latestTracking', fn ($q) => $q->where('tracking_status', $f['tracking_status']));
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

$resetFilters = function (): void {
    $this->filters = [
        'provider' => null,
        'tracking_status' => null,
        'date_from' => null,
        'date_to' => null,
    ];
    $this->page = 1;
    $this->loadShipments();
};

$openDrawer = function (string $orderId): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

    $order = Order::where('store_id', currentStoreId())
        ->with(['customer', 'status', 'shippingProvider', 'deliveryRider', 'city', 'state'])
        ->find($orderId);

    if (! $order) {
        return;
    }

    $tracking = app(OrderTrackingService::class)->currentTracking($order);

    $this->drawerOrderId = $orderId;

    $this->drawerTracking = [
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
    ];

    if ($tracking) {
        $this->drawerStatusHistories = OrderTrackingHistory::where('store_id', currentStoreId())
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

$membership = fn () => \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())
    ->where('user_id', auth()->id())
    ->first();

$trackingTransition = function (string $action): void {
    $order = Order::where('store_id', currentStoreId())->find($this->drawerOrderId);

    if (! $order) {
        return;
    }

    $service = app(OrderService::class);
    $trackingService = app(OrderTrackingService::class);
    $by = $this->membership();

    if ($service->canTransition($order, $action)) {
        $service->transition($order, $action, null, $by);
    }

    match ($action) {
        'in_transit' => $trackingService->markInTransit($order, $by?->id),
        'out_for_delivery' => $trackingService->markOutForDelivery($order, $by?->id),
        default => null,
    };
};

$trackingAction = function (string $orderId, string $action): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

    $order = Order::where('store_id', currentStoreId())->find($orderId);

    if (! $order) {
        return;
    }

    $service = app(OrderService::class);
    $trackingService = app(OrderTrackingService::class);
    $by = $this->membership();

    try {
        match ($action) {
            'delivered'  => $service->deliver($order, $by),
            'returned'   => $service->transition($order, 'returned', null, $by),
            'in_transit', 'out_for_delivery' => $this->trackingTransition($action),
            'failed_attempt' => $trackingService->markFailedAttempt($order, $by?->id),
            'returning' => $trackingService->markReturning($order, $by?->id),
            'lost' => $trackingService->markLost($order, $by?->id),
            'damaged' => $trackingService->markDamaged($order, $by?->id),
            default => throw new \InvalidArgumentException("Unknown tracking action [{$action}]"),
        };

        $this->openDrawer($orderId);
        $this->loadShipments();
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('buttons.save')]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::warning("tracking action [{$action}] failed for order [{$order->number}]: " . $e->getMessage());
        $this->dispatch('swal:toast', ['icon' => 'error', 'title' => $e->getMessage()]);
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

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="edz-card edz-card--padded flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-accent-surface text-accent-fg-strong flex items-center justify-center">
                <x-edz.icon name="truck" class="w-5 h-5" />
            </div>
            <div>
                <div class="text-2xl font-bold text-ink tabular-nums">{{ $this->stats['active'] }}</div>
                <div class="text-xs text-ink-muted">{{ __('order_flow.tracking_stats_active') }}</div>
            </div>
        </div>
        <div class="edz-card edz-card--padded flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-success/10 text-success flex items-center justify-center">
                <x-edz.icon name="check-circle" class="w-5 h-5" />
            </div>
            <div>
                <div class="text-2xl font-bold text-ink tabular-nums">{{ $this->stats['delivered_today'] }}</div>
                <div class="text-xs text-ink-muted">{{ __('order_flow.tracking_stats_delivered_today') }}</div>
            </div>
        </div>
        <div class="edz-card edz-card--padded flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-warning/10 text-warning flex items-center justify-center">
                <x-edz.icon name="arrow-uturn-left" class="w-5 h-5" />
            </div>
            <div>
                <div class="text-2xl font-bold text-ink tabular-nums">{{ $this->stats['returned_today'] }}</div>
                <div class="text-xs text-ink-muted">{{ __('order_flow.tracking_stats_returned_today') }}</div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="edz-card edz-card--padded mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="w-full sm:w-48">
                <select wire:model.live="filters.provider" class="edz-input text-sm">
                    <option value="">— {{ __('order_flow.filter_provider') }} —</option>
                    @foreach ($this->allProviders as $pr)
                        <option value="{{ $pr['id'] }}">{{ $pr['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-48">
                <select wire:model.live="filters.tracking_status" class="edz-input text-sm">
                    <option value="">— {{ __('order_flow.filter_tracking_status') }} —</option>
                    @foreach (\App\Enums\Store\OrderTrackingStatus::cases() as $ts)
                        <option value="{{ $ts->value }}">{{ $ts->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative">
                <input type="text" wire:model.blur="filters.date_from" class="edz-input text-sm flatpickr-input"
                    placeholder="{{ __('order_flow.filter_date') }} —" autocomplete="off">
            </div>
            <div class="relative">
                <input type="text" wire:model.blur="filters.date_to" class="edz-input text-sm flatpickr-input"
                    placeholder="— {{ __('order_flow.filter_date') }}" autocomplete="off">
            </div>
            <button wire:click="resetFilters" class="edz-btn edz-btn--ghost edz-btn--sm">
                <x-edz.icon name="x-mark" class="w-4 h-4" />
                {{ __('buttons.reset') }}
            </button>
        </div>
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block edz-card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-start text-xs uppercase tracking-wide text-ink-muted border-b border-surface-border">
                    <th class="text-start px-4 py-3 font-medium">{{ __('merchant_panel.number') }}</th>
                    <th class="text-start px-4 py-3 font-medium">{{ __('merchant_panel.customer') }}</th>
                    <th class="text-start px-4 py-3 font-medium">{{ __('merchant_panel.city') }}</th>
                    <th class="text-start px-4 py-3 font-medium">{{ __('order_flow.tracking_provider') }}</th>
                    <th class="text-start px-4 py-3 font-medium">{{ __('order_flow.tracking_number_copy') }}</th>
                    <th class="text-start px-4 py-3 font-medium">{{ __('order_flow.tracking_status') }}</th>
                    <th class="text-start px-4 py-3 font-medium">{{ __('merchant_panel.total') }}</th>
                    <th class="text-end px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border">
                @forelse ($this->shipments as $s)
                    <tr class="hover:bg-surface-secondary/50 transition">
                        <td class="px-4 py-3 font-medium text-ink">#{{ $s['number'] }}</td>
                        <td class="px-4 py-3">
                            <div class="text-ink">{{ $s['customer'] }}</div>
                            <div class="text-xs text-ink-muted" dir="ltr">{{ $s['phone'] }}</div>
                        </td>
                        <td class="px-4 py-3 text-ink-muted text-xs">{{ $s['city'] }}</td>
                        <td class="px-4 py-3 text-ink-muted text-xs">{{ $s['provider'] }}</td>
                        <td class="px-4 py-3 text-ink-muted text-xs font-mono" dir="ltr">
                            @if (!empty($s['tracking_number']))
                                <button
                                    x-on:click="navigator.clipboard.writeText('{{ $s['tracking_number'] }}').then(() => EdzSwal.toast ? EdzSwal.toast('{{ __('order_flow.copy_done') }}') : null)"
                                    class="inline-flex items-center gap-1 hover:text-accent-600">
                                    {{ $s['tracking_number'] }}
                                    <x-edz.icon name="clipboard" class="w-3 h-3" />
                                </button>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($s['tracking_status'])
                                <button type="button" wire:click="openStatusHistory('{{ $s['id'] }}')"
                                    title="{{ __('order_flow.tracking_history') }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer hover:opacity-80 {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->color() }}">
                                    {!! \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->label() }}
                                </button>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-ink tabular-nums">{{ $s['total'] }}</td>
                        <td class="px-4 py-3 text-end">
                            <button wire:click="openDrawer('{{ $s['id'] }}')"
                                class="edz-btn edz-btn--ghost edz-btn--xs" title="{{ __('merchant.order_details') }}">
                                <x-edz.icon name="info-circle" class="w-4 h-4" />
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-ink-muted">
                            <x-edz.icon name="truck" class="w-8 h-8 mx-auto mb-2" />
                            {{ __('order_flow.no_tracking_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="md:hidden space-y-3">
        @forelse ($this->shipments as $s)
            <div class="edz-card p-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="font-medium text-ink">#{{ $s['number'] }}</div>
                    @if ($s['tracking_status'])
                        <button type="button" wire:click="openStatusHistory('{{ $s['id'] }}')"
                            title="{{ __('order_flow.tracking_history') }}"
                            class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer hover:opacity-80 {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->color() }}">
                            {!! \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                            {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->label() }}
                        </button>
                    @endif
                </div>
                <div class="mt-2 text-sm text-ink">{{ $s['customer'] }}
                    <span class="text-xs text-ink-muted" dir="ltr">• {{ $s['phone'] }}</span>
                </div>
                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-muted">
                    <span>{{ $s['city'] }}</span>
                    <span>•</span>
                    <span>{{ $s['provider'] }}</span>
                    @if (!empty($s['tracking_number']))
                        <button
                            x-on:click="navigator.clipboard.writeText('{{ $s['tracking_number'] }}').then(() => EdzSwal.toast ? EdzSwal.toast('{{ __('order_flow.copy_done') }}') : null)"
                            class="inline-flex items-center gap-1 font-mono text-accent-600">
                            {{ $s['tracking_number'] }}
                            <x-edz.icon name="clipboard" class="w-3 h-3" />
                        </button>
                    @endif
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <span class="font-semibold text-ink">{{ $s['total'] }}</span>
                    <button wire:click="openDrawer('{{ $s['id'] }}')"
                        class="edz-btn edz-btn--ghost edz-btn--xs">
                        {{ __('buttons.view') }}
                    </button>
                </div>
            </div>
        @empty
            <div class="edz-card edz-card--padded text-center text-ink-muted py-12">
                <x-edz.icon name="truck" class="w-8 h-8 mx-auto mb-2" />
                {{ __('order_flow.no_tracking_found') }}
            </div>
        @endforelse
    </div>

    @if ($this->page > 1 && count($this->shipments) === $this->perPage)
        <div class="mt-4 flex justify-center">
            <button wire:click="$set('page', {{ $this->page + 1 }}); $wire.loadShipments()"
                class="edz-btn edz-btn--ghost edz-btn--sm">
                {{ __('pagination.next') }}
            </button>
        </div>
    @endif

    {{-- Shipment Drawer — extracted partial (P29.4) --}}
    @include('livewire.merchant.tracking.partials.order-drawer')

    {{-- Tracking-status popup (P29.4) — extracted partial --}}
    @include('livewire.merchant.tracking.partials.tracking-history-popup')
</div>