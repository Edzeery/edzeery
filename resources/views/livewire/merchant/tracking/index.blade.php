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

    $this->drawerEvents = OrderEvent::where('store_id', currentStoreId())
        ->where('order_id', $order->id)
        ->with('actor.user')
        ->orderByDesc('occurred_at')
        ->limit(15)
        ->get()
        ->toArray();
};

$closeDrawer = function (): void {
    $this->drawerOrderId = null;
    $this->drawerTracking = null;
    $this->drawerStatusHistories = [];
    $this->drawerEvents = [];
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
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->color() }}">
                                    {!! \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->label() }}
                                </span>
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
                        <span
                            class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->color() }}">
                            {!! \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                            {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])->label() }}
                        </span>
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

    {{-- Shipment Drawer --}}
    @if ($this->drawerTracking)
        <div @edz-modal-closed.window="$wire.closeDrawer()">
            <x-edz.modal :isOpen="true" size="lg" wire:key="tracking-drawer">
                <div class="p-6">
                    {{-- Header --}}
                    <div class="flex items-start gap-3">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-accent-surface text-accent-fg-strong shrink-0">
                            <x-edz.icon name="truck" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <h3 class="text-base sm:text-lg font-bold text-ink">#{{ $this->drawerTracking['number'] }}</h3>
                                @if ($this->drawerTracking['tracking_status'])
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-0.5 rounded-full {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $this->drawerTracking['tracking_status'])->color() }}">
                                        {!! \Edzeery\MyStatusKit\Facades\Status::for('tracking', $this->drawerTracking['tracking_status'])->icon(null, 'w-3.5 h-3.5 shrink-0') !!}
                                        <span>{{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $this->drawerTracking['tracking_status'])->label() }}</span>
                                    </span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-sm font-medium text-ink">{{ $this->drawerTracking['customer'] }}</p>
                            <p class="text-xs text-ink-muted" dir="ltr">{{ $this->drawerTracking['phone'] }}</p>
                        </div>
                    </div>

                    {{-- Carrier card --}}
                    <section class="mt-5">
                        <h4
                            class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                            <x-edz.icon name="truck" class="w-4 h-4" />
                            {{ __('order_flow.carrier_card') }}
                        </h4>
                        <dl
                            class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                            <div class="flex items-start justify-between gap-3 px-3 py-2">
                                <dt class="text-ink-muted shrink-0">{{ __('order_flow.tracking_provider') }}</dt>
                                <dd class="text-ink text-end">{{ $this->drawerTracking['provider'] }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3 px-3 py-2">
                                <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.tracking_number') }}</dt>
                                <dd class="text-ink text-end font-mono">
                                    {{ $this->drawerTracking['tracking_number'] ?? '—' }}
                                    @if (!empty($this->drawerTracking['tracking_number']))
                                        <button
                                            x-on:click="navigator.clipboard.writeText('{{ $this->drawerTracking['tracking_number'] }}').then(() => EdzSwal.toast ? EdzSwal.toast('{{ __('order_flow.copy_done') }}') : null)"
                                            class="text-accent-600 hover:text-accent-700 ms-1 align-middle"
                                            title="{{ __('order_flow.tracking_number_copy') }}">
                                            <x-edz.icon name="clipboard" class="w-3.5 h-3.5 inline-block" />
                                        </button>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex items-start justify-between gap-3 px-3 py-2">
                                <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.shipped_at') }}</dt>
                                <dd class="text-ink text-end">
                                    {{ $this->drawerTracking['shipped_at'] ? \Carbon\Carbon::parse($this->drawerTracking['shipped_at'])->format('M d, Y H:i') : '—' }}
                                </dd>
                            </div>
                            <div class="flex items-start justify-between gap-3 px-3 py-2">
                                <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.delivered_at') }}</dt>
                                <dd class="text-ink text-end">
                                    {{ $this->drawerTracking['delivered_at'] ? \Carbon\Carbon::parse($this->drawerTracking['delivered_at'])->format('M d, Y H:i') : '—' }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    {{-- Shipment summary --}}
                    <section class="mt-5">
                        <h4
                            class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                            <x-edz.icon name="bag" class="w-4 h-4" />
                            {{ __('order_flow.shipment_summary') }}
                        </h4>
                        <dl
                            class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                            <div class="flex items-start justify-between gap-3 px-3 py-2">
                                <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.city') }}</dt>
                                <dd class="text-ink text-end">{{ $this->drawerTracking['city'] }}</dd>
                            </div>
                            @if (!empty($this->drawerTracking['address']))
                                <div class="flex items-start justify-between gap-3 px-3 py-2">
                                    <dt class="text-ink-muted shrink-0">{{ __('merchant_panel.address') }}</dt>
                                    <dd class="text-ink text-end min-w-0">
                                        {{ \Illuminate\Support\Str::limit($this->drawerTracking['address'], 60) }}</dd>
                                </div>
                            @endif
                            <div class="flex items-start justify-between gap-3 px-3 py-2.5 bg-surface font-bold text-ink">
                                <dt>{{ __('merchant_panel.total') }}</dt>
                                <dd class="tabular-nums">{{ $this->drawerTracking['total'] }}</dd>
                            </div>
                        </dl>
                    </section>

                    {{-- Quick actions --}}
                    @if (canStore(StorePermissionEnum::ORDER_MANAGE->value) && $this->drawerOrderId)
                        <section class="mt-5">
                            <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                                {{ __('merchant_panel.actions') }}
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'in_transit')"
                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'in_transit')->label() }}
                                </button>
                                <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'out_for_delivery')"
                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'out_for_delivery')->label() }}
                                </button>
                                <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'failed_attempt')"
                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'failed_attempt')->label() }}
                                </button>
                                <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'returning')"
                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'returning')->label() }}
                                </button>
                                <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'delivered')"
                                    class="edz-btn edz-btn--primary edz-btn--sm">
                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'delivered')->label() }}
                                </button>
                                <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'returned')"
                                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600">
                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'returned')->label() }}
                                </button>
                                <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'lost')"
                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'lost')->label() }}
                                </button>
                                <button wire:click="trackingAction('{{ $this->drawerOrderId }}', 'damaged')"
                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                    {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', 'damaged')->label() }}
                                </button>
                            </div>
                        </section>
                    @endif

                    {{-- Tracking history --}}
                    <section class="mt-5">
                        <h4
                            class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                            <x-edz.icon name="clock" class="w-4 h-4" />
                            {{ __('order_flow.tracking_history') }}
                        </h4>
                        @if (!empty($this->drawerStatusHistories))
                            <ol
                                class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30">
                                @foreach ($this->drawerStatusHistories as $i => $h)
                                    <li class="flex items-start gap-3 px-3 py-2.5 text-sm">
                                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $i === 0 ? 'bg-accent-600' : 'bg-surface-border' }}"></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-ink leading-snug">
                                                @php
                                                    $tsStatus = \App\Enums\Store\OrderTrackingStatus::tryFrom($h['status']);
                                                @endphp
                                                {{ $tsStatus?->label() ?? $h['status'] }}
                                                @if (!empty($h['notes']))
                                                    <span class="text-ink-muted">— {{ $h['notes'] }}</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-ink-muted mt-0.5">
                                                {{ \Carbon\Carbon::parse($h['created_at'])->diffForHumans() }}
                                                @if (!empty($h['by']))
                                                    • {{ $h['by'] }}
                                                @endif
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        @else
                            <div class="text-xs text-ink-muted">{{ __('order_flow.tracking_history_empty') }}</div>
                        @endif
                    </section>

                    {{-- Order events timeline --}}
                    @if (!empty($this->drawerEvents))
                        <section class="mt-5">
                            <h4
                                class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                                <x-edz.icon name="list-bullet" class="w-4 h-4" />
                                {{ __('order_flow.order_timeline') }}
                            </h4>
                            <ol
                                class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30">
                                @foreach ($this->drawerEvents as $i => $ev)
                                    <li class="flex items-start gap-3 px-3 py-2.5 text-sm">
                                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $i === 0 ? 'bg-accent-600' : 'bg-surface-border' }}"></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-ink leading-snug">{{ $ev['message'] ?? '—' }}</p>
                                            <p class="text-xs text-ink-muted mt-0.5 flex flex-wrap items-center gap-x-2">
                                                <span>{{ __('order_flow.event_type_' . ($ev['event_type'] ?? 'note')) }}</span>
                                                <span>•</span>
                                                <span>{{ \Carbon\Carbon::parse($ev['occurred_at'])->diffForHumans() }}</span>
                                                @if (!empty($ev['actor']['user']['name']))
                                                    <span>•</span>
                                                    <span>{{ $ev['actor']['user']['name'] }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endif
                </div>
            </x-edz.modal>
        </div>
    @endif
</div>