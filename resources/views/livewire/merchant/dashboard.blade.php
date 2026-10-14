<?php

use App\Domains\Analytics\Services\StoreDashboardAnalyticsService;
use App\Domains\Analytics\Support\DashboardFilterFactory;
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

    // PHASE 37-N5: the dashboard is three views behind one control. The tab
    // decides which KPIs, charts and lists are built — and which filters the
    // factory keeps — so an inactive view never pays for its queries.
    $tabs = $this->permittedTabs();
    $activeTab = $this->activeTab();
    $isTopView = $activeTab === 'top';
    $isDeliveryView = $activeTab === 'delivery';
    $isConfirmationView = $activeTab === 'confirmation';
    $canViewStats = ! $isTopView;
    $canConfirmStats = $canConfirm || $canStatsConfirmation;

    $filterOptions = app(DashboardFilterOptions::class);
    $currentMembership = auth()->user()?->storeMemberships()->where('store_id', currentStoreId())->first();
    $carriers = $filterOptions->carriers(currentStoreId());

    // The confirmation team section names soft-deleted members only when its
    // without/with-trash toggle is on (D7); no other view ever does.
    $members = $filterOptions->members(
        $currentMembership,
        $filter->memberDimension,
        $isConfirmationView && $this->teamTrash,
    );

    // PHASE 37-N7 (D8): the Delivery team section names members through its own
    // trash toggle and cohort pick (all / with / without the confirmation team),
    // a refinement of the already-scanned rows. The member dropdown keeps the
    // full dimension list.
    $deliveryTeamMembers = $isDeliveryView
        ? $filterOptions->members($currentMembership, 'delivery', $this->teamTrash, $this->teamCohort)
        : $members;

    // The Top view is a two-list executive board: each list names its own
    // cohort's members, and a one-member store has no ranking to draw (D7).
    $topConfirmers = $isTopView ? $filterOptions->members($currentMembership, 'confirmation') : collect();
    $topDeliverers = $isTopView ? $filterOptions->members($currentMembership, 'delivery') : collect();

    $showTopConfirmers = $isTopView && $canConfirmStats && $topConfirmers->count() > 1;
    $showTopDeliverers = $isTopView && $canStatsDelivery && $topDeliverers->count() > 1;

    $carrierOptions = $filterOptions->carrierSelectOptions(currentStoreId());
    $memberOptions = $filterOptions->memberSelectOptions($currentMembership, $filter->memberDimension);

    // Every list is built from its own tab's aggregate query; the Top lists
    // reuse the same services with a top filter, so they group by the right
    // key (assignment vs newest tracking) while every refinement stays dropped.
    $factory = app(DashboardFilterFactory::class);
    $topFilter = fn (string $dimension) => $factory->make([
        'period' => $this->period,
        'dateFrom' => $this->dateFrom,
        'dateTo' => $this->dateTo,
        'memberDimension' => $dimension,
        'view' => 'top',
    ], $currentMembership);

    $showDeliveryList = ($isDeliveryView && $canStatsDelivery && ! $filter->memberLocked)
        || $showTopDeliverers;
    $showConfirmationList = ($isConfirmationView && $canConfirmStats && ! $filter->memberLocked)
        || $showTopConfirmers;

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
        'tabs' => $tabs,
        'activeTab' => $activeTab,
        'isTopView' => $isTopView,
        'isDeliveryView' => $isDeliveryView,
        'isConfirmationView' => $isConfirmationView,
        'canViewStats' => $canViewStats,
        'canConfirmStats' => $canConfirmStats,
        // The four executive cards follow the Top tab; the confirmation tab
        // still reads the pending count off the same summary.
        'summary' => $canTopKpis && ! $isDeliveryView ? $analytics->summary($filter) : collect(),
        'teamPerformance' => $showDeliveryList
            ? $analytics->teamPerformance($isDeliveryView ? $filter : $topFilter('delivery'), $isDeliveryView ? $deliveryTeamMembers : $topDeliverers)
            : [],
        'confirmationTeam' => $showConfirmationList
            ? $analytics->confirmationTeam($isConfirmationView ? $filter : $topFilter('confirmation'), $isConfirmationView ? $members : $topConfirmers)
            : [],
        'showTopConfirmers' => $showTopConfirmers,
        'showTopDeliverers' => $showTopDeliverers,
        'showDeliveryList' => $showDeliveryList,
        'showConfirmationList' => $showConfirmationList,
        'teamTrash' => $this->teamTrash,
        'teamCohort' => $this->teamCohort,
        'statusBreakdown' => $canViewStats ? $analytics->statusBreakdown($filter) : collect(),
        'trend' => $canViewStats ? $analytics->trendSeries($filter) : null,
        'confirmationTab' => $this->confirmationTab,
        'deliveryTab' => $this->deliveryTab,
        'teamSearch' => $this->teamSearch,
        'confirmationStats' => $isConfirmationView ? $analytics->confirmationStats($filter) : null,
        'deliveryStats' => $isDeliveryView ? $analytics->deliveryStats($filter) : null,
        'productIds' => $this->productIds,
        'showProductFilterModal' => $this->showProductFilterModal,
        'filterProductResults' => $this->filterProductResults,
        'filterProductHasMore' => $this->filterProductHasMore,
        'filterProductChunkLoading' => $this->filterProductChunkLoading,
        'ordersByState' => $canTopKpis && $isTopView ? $analytics->ordersByState($filter) : collect(),
        'deliveryTypeBreakdown' => $canTopKpis && $isTopView ? $analytics->deliveryTypeBreakdown($filter) : collect(),
        'pendingOrders' => $canConfirm && $isConfirmationView ? $analytics->pendingConfirmationOrders($filter) : collect(),
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
    <div wire:loading.class="opacity-60 transition-opacity duration-200"
         wire:target="period,dateFrom,dateTo,carrierId,memberId,memberDimension,dateBasis,productIds,teamTrash,teamCohort,teamSearch,setConfirmationTab,setDeliveryTab,setTab">

    {{-- View tabs (PHASE 37-N5): Confirmation, Delivery and Top lead the page,
         so the user picks the lens first and then narrows it with the filters. --}}
    @include('livewire.merchant.dashboard.partials.tabs')

    @include('livewire.merchant.dashboard.partials.filter-bar')

    <div id="dashboard-panel-{{ $activeTab }}"
         wire:key="dash-panel-{{ $activeTab }}"
         role="tabpanel"
         @if (count($tabs) >= 2) aria-labelledby="dashboard-tab-{{ $activeTab }}" @endif
         tabindex="0">

    @if ($isTopView)
        {{-- Executive board: the four headline cards plus the two member
             rankings. No charts, and no filter beyond the period. --}}
        @include('livewire.merchant.dashboard.partials.kpi-top')
        @include('livewire.merchant.dashboard.partials.top-breakdown')
        @include('livewire.merchant.dashboard.partials.top-members')
    @elseif ($isDeliveryView)
        {{-- Rate cards lead the delivery view (D6), then the counts, then the
             charts, the team and the top products. --}}
        @include('livewire.merchant.dashboard.partials.kpi-delivery')
        @if ($canViewStats)
            {{-- Body switch (PHASE 37-N8): the count/rate tabs above the
                 per-status breakdown, mirroring the confirmation view. --}}
            <div class="mb-6">
                <div class="flex w-full items-center gap-1 rounded-xl border border-surface-border bg-surface-secondary/70 p-1 sm:inline-flex sm:w-auto"
                     role="tablist"
                     aria-label="{{ __('dashboard.delivery_stats_title') }}">
                    @foreach (['count', 'rate'] as $tab)
                        <button type="button"
                            id="delivery-tab-{{ $tab }}"
                            wire:click="setDeliveryTab('{{ $tab }}')"
                            role="tab"
                            aria-controls="dashboard-panel-{{ $activeTab }}"
                            aria-selected="{{ $deliveryTab === $tab ? 'true' : 'false' }}"
                            @class([
                                'flex-1 rounded-lg px-4 py-2 text-center text-sm font-semibold transition-colors sm:flex-none',
                                'bg-surface text-ink shadow-sm' => $deliveryTab === $tab,
                                'text-ink-muted hover:text-ink' => $deliveryTab !== $tab,
                            ])>
                            {{ __("dashboard.delivery_tab_{$tab}") }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
        @include('livewire.merchant.dashboard.partials.delivery-stats')
        {{-- Charts Row --}}
        @if ($canViewStats)
            @include('livewire.merchant.dashboard.partials.charts')
        @endif
        {{-- Team section: the 37-J delivery table. --}}
        @if ($showDeliveryList)
            @include('livewire.merchant.dashboard.partials.team-performance')
        @endif
        {{-- Top products (delivery only). --}}
        @if ($canTopProducts && $isDeliveryView)
        <div class="edz-stagger grid grid-cols-1 gap-6 mb-6 lg:grid-cols-1">
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
    @else
        {{-- Confirmation view: the count/rate body switch, then the rate card
             and pending queue, then the body and its charts, then the team. --}}
        @if ($canViewStats)
            <div class="mb-6">
                <div class="flex w-full items-center gap-1 rounded-xl border border-surface-border bg-surface-secondary/70 p-1 sm:inline-flex sm:w-auto"
                     role="tablist"
                     aria-label="{{ __('dashboard.confirmation_stats_title') }}">
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
        @include('livewire.merchant.dashboard.partials.kpi-confirmation')
        @if ($canViewStats)
            @if ($confirmationTab === 'count')
                <div class="edz-stagger grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                    @include('livewire.merchant.dashboard.partials.confirmation-stats')
                    @php($narrowCharts = true)
                    @include('livewire.merchant.dashboard.partials.charts')
                    @php($narrowCharts = false)
                </div>
            @else
                <div class="mb-6">
                    @include('livewire.merchant.dashboard.partials.confirmation-stats')
                </div>
                @php($narrowCharts = false)
                @include('livewire.merchant.dashboard.partials.charts')
            @endif
        @endif
        @if ($showConfirmationList)
            @include('livewire.merchant.dashboard.partials.confirmation-team')
        @endif
    @endif

    </div>

    {{-- Stock Alerts: inventory health is view-independent. --}}
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
