<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\updated;
use function Livewire\Volt\uses;

layout('components.layouts.store');

uses([
    \App\Livewire\Concerns\HasOrderProductPicker::class,
    \App\Livewire\Concerns\TrackingGridConcern::class,
    \App\Livewire\Concerns\TrackingColumnConcern::class,
    \App\Livewire\Concerns\TrackingDrawerConcern::class,
    \App\Livewire\Concerns\TrackingRiderFormConcern::class,
    \App\Livewire\Concerns\TrackingTrashConcern::class,
]);

state([
    'filters' => [
        'provider' => null,
        'tracking_statuses' => [],
        'date_from' => null,
        'date_to' => null,
        'amount_min' => null,
        'amount_max' => null,
        'city' => null,
        'rider' => null,
        'assigned_to' => null,
        'confirmed_by' => null,
    ],
    'search' => '',
    'filteredTotal' => 0,
    'shipments' => [],
    'page' => 1,
    'perPage' => 20,
    'allProviders' => [],
    'allCities' => [],
    'allMembers' => [],
    'allStates' => [],
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

    // Carrier-note popup (notes column) — same API as the drawer composer
    'shipmentNotesFor' => null,
    'shipmentNotes' => [],
    'shipmentNotesMeta' => null,

    // Phase A — active tab: 'carrier' (shipping companies) | 'rider' (delivery rider)
    'trackingTab' => 'carrier',

    // Trash bin — soft-deleted orders (same pattern as the orders page:
    // restore / restore-all / permanent delete scoped to ORDER_DELETE).
    'showTrash' => false,
    'trashCount' => 0,

    // Label printing — carrier label streamed through the auth proxy, or our
    // own printable sheet (riders / carriers without a label endpoint).
    'labelOpen' => false,
    'labelOrderId' => null,
    'labelData' => null,

    // Rider tab — configured riders (filter source) + filter-responsive counts/stats
    'allRiders' => [],
    'riderRiders' => [],
    'riderOptions' => [],
    'riderStatsActiveCount' => 0,
    'riderStatsActiveShipments' => 0,
    'riderStatsCodDueToday' => 0,

    // Grid column preferences — per active tab (view_key tracking_carrier / tracking_rider)
    'visibleColumns' => [],
    'draftColumns' => [],
    'showTableSettings' => false,
    'tableStyle' => 'default',
    'draftStyle' => 'default',

    // Order edit modal (ported from the orders page so the tracking grid can
    // fix an order while its shipment is in flow)
    'showCreateModal' => false,
    'showEditModal' => false,
    'showProductPickerModal' => false,
    'showVariantPickerModal' => false,
    'editingOrderId' => null,
    'form' => [
        'customer_name' => '',
        'customer_phone' => '',
        'phone_secondary' => '',
        'address' => '',
        'state_id' => '',
        'city_id' => '',
        'delivery_type' => 'home',
        'shipping_provider_id' => '',
        'delivery_rider_id' => '',
        'stopdesk_point_id' => '',
        'shipment_type' => 'delivery',
        'payment_method' => 'cod',
        'discount_type' => null,
        'discount_value' => null,
        'discount_reason' => '',
        'notes' => '',
        'weight_kg' => '',
        'items' => [],
    ],
    'formProductResults' => [],
    'formProductView' => 'list', // 'list' | 'variants'
    'formSelectedProduct' => null,
    'formSelectedItems' => [],
    'formPartnerType' => 'provider', // 'provider' | 'rider' segment in the order edit form
    'productChunkLoading' => false,
    'productHasMore' => true,
    'formOffices' => [],
    'loadingOffices' => false,
    'formOfficesVersion' => 0,
    'formHasOffices' => false,
    'formCities' => [],
    'formAvailableStates' => [],
    'formCoverageHint' => '',
    'formDuplicateWarnings' => [],
]);

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
    'filters.amount_min' => function (): void {
        $this->page = 1;
        $this->loadShipments();
    },
    'filters.amount_max' => function (): void {
        $this->page = 1;
        $this->loadShipments();
    },
    'filters.city' => function (): void {
        $this->page = 1;
        $this->loadShipments();
    },
    'filters.rider' => function (): void {
        $this->page = 1;
        $this->loadShipments();
    },
    'filters.assigned_to' => function (): void {
        $this->page = 1;
        $this->loadShipments();
    },
    'filters.confirmed_by' => function (): void {
        $this->page = 1;
        $this->loadShipments();
    },
    'trackingTab' => function (): void {
        if (! in_array($this->trackingTab, ['carrier', 'rider'], true)) {
            $this->trackingTab = 'carrier';
            return;
        }

        $this->loadTrackingTabPreferences();
        $this->saveColumnPreferences();

        // Each tab owns its own trash bin — leaving for the other tab exits trash mode.
        $this->showTrash = false;

        $this->page = 1;
        $this->loadShipments();
    },
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

    $storeId = currentStoreId();

    $this->allProviders = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', $storeId)
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name'])
        ->toArray();

    $this->allCities = \App\Models\Locations\City::whereIn(
        'id',
        Order::where('store_id', $storeId)->whereNotNull('city_id')->distinct()->pluck('city_id'),
    )->orderBy('name')->get()->toArray();

    $this->allMembers = \App\Models\Stores\Team\StoreMembership::where('store_id', $storeId)
        ->where('is_active', true)
        ->with('user')
        ->get()
        ->map(fn ($membership) => [
            'id' => (string) $membership->id,
            'name' => $membership->user?->name ?? '—',
        ])
        ->sortBy('name')
        ->values()
        ->toArray();

    $this->allStates = \App\Models\Locations\State::whereIn(
        'id',
        Order::where('store_id', $storeId)->whereNotNull('state_id')->distinct()->pluck('state_id'),
    )->orderBy('name')->get(['id', 'name'])->toArray();

    $this->loadRiderOptions();

    $this->loadTrackingTabPreferences();
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
        @if ($this->showTrash)
            {{-- Shared trash banner (restore-all / empty-trash, scoped to ORDER_DELETE). --}}
            @include('livewire.merchant.tracking.partials.tracking-trash-banner')
        @else
            @include('livewire.merchant.tracking.partials.tracking-stats')
        @endif

        @include('livewire.merchant.tracking.partials.tracking-toolbar')

        @if (! empty($this->filteredTotal))
            <div class="mb-3 flex items-center gap-2 text-sm text-ink-muted">
                <span>{{ $this->filteredTotal }} {{ __('order_flow.tracking_count') }}</span>
            </div>
        @endif

        {{-- Advanced grid -- desktop table + mobile cards + prev/next --}}
        @include('livewire.merchant.tracking.partials.tracking-list')
    @else
        @include('livewire.merchant.tracking.partials.tracking-rider-tab')
    @endif

    {{-- Header-column filter portal (advanced grid) --}}
    @include('livewire.merchant.tracking.partials.tracking-filter-portal')

    {{-- Table settings modal (columns / style) --}}
    @include('livewire.merchant.tracking.partials.tracking-table-settings-modal')

    {{-- Shipment Drawer — extracted partial (P29.4) --}}
    @include('livewire.merchant.tracking.partials.order-drawer')

    {{-- Tracking-status popup (P29.4) — extracted partial --}}
    @include('livewire.merchant.tracking.partials.tracking-history-popup')

    {{-- Carrier-notes popup (notes column) — history + composer --}}
    @include('livewire.merchant.tracking.partials.tracking-notes-popup')

    {{-- Label-print modal (our own sheet — carrier labels open in a new tab) --}}
    @include('livewire.merchant.tracking.partials.label-print-modal')

    {{-- Order edit modal + shared product picker (ported from the orders page) --}}
    @include('livewire.merchant.orders.partials.order-form-modal')
    @include('livewire.merchant.orders.partials.orders-product-picker')

    <script>
        if (! window.__edzTrackingOpenLabel) {
            window.__edzTrackingOpenLabel = true;
            document.addEventListener('open-label', function (e) {
                if (e.detail && e.detail.url) {
                    window.open(e.detail.url, '_blank', 'noopener');
                }
            });
        }
    </script>
</div>