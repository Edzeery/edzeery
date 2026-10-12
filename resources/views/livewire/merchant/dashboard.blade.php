<?php

use App\Domains\Analytics\Services\StoreDashboardAnalyticsService;
use App\Domains\Analytics\Support\DashboardFilterOptions;
use App\Domains\User\Services\SubscriptionGuardService;
use App\Enums\Store\StorePermissionEnum;
use App\Livewire\Concerns\DashboardFilterConcern;
use App\Livewire\Concerns\DashboardProductFilterConcern;

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\uses;
use function Livewire\Volt\with;

layout('components.layouts.store');
uses([
    DashboardFilterConcern::class,
    DashboardProductFilterConcern::class,
]);

state([
    'showProductFilterModal' => false,
    'filterProductResults' => [],
    'filterProductHasMore' => false,
    'filterProductChunkLoading' => false,
]);

$analytics = app(StoreDashboardAnalyticsService::class);
$subscriptionGuard = app(SubscriptionGuardService::class);

$canTopKpis = canStore(StorePermissionEnum::STATS_TOP_KPIS->value);
$canStatsDelivery = canStore(StorePermissionEnum::STATS_DELIVERY->value);
$canConfirm = canStore(StorePermissionEnum::ORDER_CONFIRM->value);
$canStatsConfirmation = canStore(StorePermissionEnum::STATS_CONFIRMATION->value);
$canInventory = canStore(StorePermissionEnum::INVENTORY_VIEW->value);
$canTopProducts = canStore(StorePermissionEnum::PRODUCT_VIEW->value);

with(function () use ($analytics, $subscriptionGuard, $canTopKpis, $canStatsDelivery, $canConfirm, $canStatsConfirmation, $canInventory, $canTopProducts) {
    $filter = $this->filter();

    // memberDimension selects the whole dashboard view, not just member
    // attribution: it decides which KPIs lead and which lists are loaded.
    $isDeliveryView = $filter->memberDimension === 'delivery';
    $canConfirmStats = $canConfirm || $canStatsConfirmation;
    // Charts follow the active view, so a confirmation-only user still sees the
    // trend and the status doughnut.
    $canViewStats = $isDeliveryView ? $canStatsDelivery : $canConfirmStats;

    $filterOptions = app(DashboardFilterOptions::class);
    $currentMembership = auth()->user()?->storeMemberships()->where('store_id', currentStoreId())->first();
    $carriers = $filterOptions->carriers(currentStoreId());
    // The confirmation team section names soft-deleted members only when its
    // without/with-trash toggle is on (D7); the delivery view never does.
    $members = $filterOptions->members(
        $currentMembership,
        $filter->memberDimension,
        ! $isDeliveryView && $this->teamTrash,
    );
    $carrierOptions = $filterOptions->carrierSelectOptions(currentStoreId());
    $memberOptions = $filterOptions->memberSelectOptions($currentMembership, $filter->memberDimension);

    $periodLabel = $filter->period === 'custom'
        ? __('dashboard.period_range', [
            'from' => $filter->localFrom()?->format('Y-m-d'),
            'to' => $filter->localTo()?->format('Y-m-d'),
        ])
        : __("dashboard.period_{$filter->period}");

    return [
        'filter' => $filter,
        'filterOptions' => [
            'carriers' => $carriers,
            'members' => $members,
            'carrierOptions' => $carrierOptions,
            'memberOptions' => $memberOptions,
        ],
        'periodLabel' => $periodLabel,
        // Boundaries are stored in UTC; compare against the local dates the user typed.
        'invalidRange' => $filter->period === 'custom' && (
            blank($this->dateFrom)
            || blank($this->dateTo)
            || $this->dateFrom !== $filter->localFrom()?->format('Y-m-d')
            || $this->dateTo !== $filter->localTo()?->format('Y-m-d')
        ),
        'isDeliveryView' => $isDeliveryView,
        'canViewStats' => $canViewStats,
        'summary' => $canTopKpis ? $analytics->summary($filter) : collect(),
        'teamPerformance' => $canViewStats && $isDeliveryView && ! $filter->memberLocked
            ? $analytics->teamPerformance($filter, $members) : [],
        'confirmationTeam' => $canViewStats && ! $isDeliveryView && ! $filter->memberLocked
            ? $analytics->confirmationTeam($filter, $members) : [],
        'teamTrash' => $this->teamTrash,
        'statusBreakdown' => $canViewStats ? $analytics->statusBreakdown($filter) : collect(),
        'trend' => $canViewStats ? $analytics->trendSeries($filter) : null,
        'confirmationTab' => $this->confirmationTab,
        'teamSearch' => $this->teamSearch,
        'confirmationStats' => $canViewStats && ! $isDeliveryView ? $analytics->confirmationStats($filter) : null,
        'productIds' => $this->productIds,
        'showProductFilterModal' => $this->showProductFilterModal,
        'filterProductResults' => $this->filterProductResults,
        'filterProductHasMore' => $this->filterProductHasMore,
        'filterProductChunkLoading' => $this->filterProductChunkLoading,
        'ordersByState' => $canStatsDelivery && $isDeliveryView ? $analytics->ordersByState($filter) : collect(),
        'deliveryTypeBreakdown' => $canStatsDelivery && $isDeliveryView ? $analytics->deliveryTypeBreakdown($filter) : collect(),
        'pendingOrders' => $canConfirm && ! $isDeliveryView ? $analytics->pendingConfirmationOrders($filter) : collect(),
        'topProducts' => $canTopProducts && $isDeliveryView ? $analytics->topSellingProducts($filter) : collect(),
        'lowStockVariants' => $canInventory ? $analytics->lowStockVariants() : collect(),
        'subscription' => $subscriptionGuard->getSubscription(),
        'hasActiveSubscription' => $subscriptionGuard->hasActiveSubscription(),
        'subscriptionStatus' => $subscriptionGuard->statusLabel(),
        'daysRemaining' => $subscriptionGuard->daysRemaining(),
        'canTopKpis' => $canTopKpis,
        'canStatsDelivery' => $canStatsDelivery,
        'canConfirm' => $canConfirm,
        'canStatsConfirmation' => $canStatsConfirmation,
        'canInventory' => $canInventory,
        'canTopProducts' => $canTopProducts,
    ];
});
?>
<div>
    @include('livewire.merchant.dashboard.partials.filter-bar')

    <div wire:loading.class="opacity-60 transition-opacity duration-200"
         wire:target="period,dateFrom,dateTo,carrierId,memberId,memberDimension,dateBasis,productIds,teamTrash,teamSearch,setConfirmationTab">

    {{-- Base stats mode (M4): one global control so the count/rate choice frames
         every confirmation statistic — the headline, the charts and the team.
         It sits inside the loading wrapper so switching it dims the content it
         is about to change. --}}
    @if ($canViewStats && ! $isDeliveryView)
        <div class="mb-6">
            <div class="flex w-full items-center gap-1 rounded-xl border border-surface-border bg-surface-secondary/70 p-1 sm:inline-flex sm:w-auto"
                 role="tablist">
                @foreach (['count', 'rate'] as $tab)
                    <button type="button"
                        id="conf-tab-{{ $tab }}"
                        wire:click="setConfirmationTab('{{ $tab }}')"
                        role="tab"
                        aria-controls="dashboard-stats-panel"
                        aria-selected="{{ $confirmationTab === $tab ? 'true' : 'false' }}"
                        @class([
                            'flex-1 rounded-lg px-4 py-2 text-center text-sm font-semibold transition-colors sm:flex-none',
                            'bg-surface text-ink shadow-sm' => $confirmationTab === $tab,
                            'text-ink-muted hover:text-ink' => $confirmationTab !== $tab,
                        ])>
                        {{ __("dashboard.confirmation_tab_{$tab}") }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div role="tabpanel"
         @if ($canViewStats && ! $isDeliveryView) id="dashboard-stats-panel" aria-labelledby="conf-tab-{{ $confirmationTab }}" @endif>

    {{-- KPI Cards — Apple-style large numbers with negative tracking.
         The confirmation view promotes the pending queue into this row so the
         operator reads the headline numbers and the to-do list in one glance. --}}
    @if ($isDeliveryView)
        @include('livewire.merchant.dashboard.partials.kpi-delivery')
    @else
        @include('livewire.merchant.dashboard.partials.kpi-confirmation')
    @endif
    {{-- Charts Row --}}
    @if ($canViewStats)
        @if ($isDeliveryView)
            @include('livewire.merchant.dashboard.partials.charts')
        @elseif ($confirmationTab === 'count')
            {{-- Count tab: the two-figure headline shares the row with the charts
                 on lg, one column under 768. The charts render narrow: their
                 internal three-column split collapses. --}}
            <div class="edz-stagger grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                @include('livewire.merchant.dashboard.partials.confirmation-stats')
                @php($narrowCharts = true)
                @include('livewire.merchant.dashboard.partials.charts')
                @php($narrowCharts = false)
            </div>
        @else
            {{-- Rate tab: the rate and its horizontal group row read full width,
                 and the charts with the doughnut drop underneath. --}}
            <div class="mb-6">
                @include('livewire.merchant.dashboard.partials.confirmation-stats')
            </div>
            @php($narrowCharts = false)
            @include('livewire.merchant.dashboard.partials.charts')
        @endif
    @endif
    {{-- Delivery Breakdown Row: delivery view only --}}
    @if ($canStatsDelivery && $isDeliveryView)
    <div class="edz-stagger grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        {{-- Geographic Distribution --}}
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">{{ __('dashboard.orders_by_state') }}</h3>
            @if ($ordersByState->isNotEmpty())
                <div class="space-y-2.5">
                    @php($maxState = $ordersByState->max('count'))
                    @foreach ($ordersByState as $state)
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-medium text-ink w-24 truncate" title="{{ $state->name }}">{{ $state->name }}</span>
                            <div class="flex-1 bg-surface-secondary rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full bg-accent-500 transition-all duration-700 ease-out-expo"
                                     style="width: {{ $maxState > 0 ? ($state->count / $maxState * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-ink w-8 text-right">{{ $state->count }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-32 flex items-center justify-center">
                    <p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p>
                </div>
            @endif
        </div>
        {{-- Delivery Type --}}
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">{{ __('dashboard.delivery_breakdown') }}</h3>
            @if ($deliveryTypeBreakdown->isNotEmpty())
                <div class="space-y-4 mt-6">
                    @foreach ($deliveryTypeBreakdown as $dt)
                        @php($total = $deliveryTypeBreakdown->sum('count'))
                        @php($pct = $total > 0 ? round(($dt->count / $total) * 100) : 0)
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-sm font-medium text-ink">{{ $dt->delivery_type === 'home' ? __('orders.delivery_home') : __('orders.delivery_stopdesk') }}</span>
                                <span class="text-sm font-bold tracking-tight text-ink">{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-surface-secondary rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-700 ease-out-expo {{ $dt->delivery_type === 'home' ? 'bg-accent-500' : 'bg-success-500' }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-ink-muted">{{ $dt->count }} {{ __('orders.orders') }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-32 flex items-center justify-center">
                    <p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p>
                </div>
            @endif
        </div>
    </div>
    @endif
    {{-- Team section: the delivery view keeps the 37-J table; the confirmation
         view (M5) gets the tab-following member section driven by the same scan
         as the headline cards. --}}
    @if ($canViewStats && ! $filter->memberLocked)
        @if ($isDeliveryView)
            @include('livewire.merchant.dashboard.partials.team-performance')
        @else
            @include('livewire.merchant.dashboard.partials.confirmation-team')
        @endif
    @endif
    {{-- Remaining list (delivery only): top products. The confirmation view's
         pending queue moved up into the KPI row. --}}
    @if ($canTopProducts && $isDeliveryView)
    <div class="edz-stagger grid grid-cols-1 gap-6 mb-6 lg:grid-cols-1">
        {{-- Top Selling Products --}}
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold tracking-tight text-ink">{{ __('dashboard.top_products') }}</h3>
                <a href="{{ route('merchant.products.index', currentStore()) }}" wire:navigate
                   class="text-xs font-medium text-accent-600 hover:text-accent-500 transition-colors">
                    {{ __('buttons.view_all') }}
                </a>
            </div>
            @if ($topProducts->isNotEmpty())
                <div class="divide-y divide-surface-border">
                    @foreach ($topProducts as $product)
                        <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-ink truncate">{{ $product->name }}</p>
                                <p class="text-xs text-ink-muted">{{ $product->total_qty }} {{ __('orders.units_sold') }}</p>
                            </div>
                            <span class="text-sm font-bold tracking-tight text-ink">{{ number_format($product->total_revenue, 0) }} {{ __('stores.currency_symbol') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-edz.icon name="bag-outline" class="w-10 h-10 mx-auto text-ink-muted mb-2" />
                    <p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p>
                </div>
            @endif
        </div>
    </div>
    @endif
    {{-- Stock Alerts --}}
    @if ($canInventory && $lowStockVariants->isNotEmpty())
        <div class="edz-card edz-card--padded mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold tracking-tight text-ink">{{ __('titles.stock_alerts') }}</h3>
                <a href="{{ route('merchant.stock-alerts.index', currentStore()) }}"
                   class="text-xs font-medium text-danger-600 hover:text-danger-500 transition-colors">
                    {{ __('buttons.view_all') }}
                </a>
            </div>
            <div class="divide-y divide-surface-border">
                @foreach ($lowStockVariants as $variant)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-ink truncate">{{ $variant->product->name }}</p>
                            <p class="text-xs text-ink-muted">
                                {{ $variant->optionValues->pluck('value')->implode(' / ') ?: $variant->name }}
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="edz-badge edz-badge--warning">{{ $variant->stock }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    </div>
    </div>
</div>
