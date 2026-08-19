<?php

use App\Domains\Analytics\Services\StoreDashboardAnalyticsService;
use App\Domains\User\Services\SubscriptionGuardService;
use function Livewire\Volt\layout;
use function Livewire\Volt\with;

layout('components.layouts.store');

$analytics = app(StoreDashboardAnalyticsService::class);
$subscriptionGuard = app(SubscriptionGuardService::class);

with([
    'summary'               => $analytics->summary(),
    'salesByDay'            => $analytics->salesByDay(),
    'ordersByStatus'        => $analytics->ordersByStatus(),
    'ordersByState'         => $analytics->ordersByState(),
    'deliveryTypeBreakdown' => $analytics->deliveryTypeBreakdown(),
    'pendingOrders'         => $analytics->pendingConfirmationOrders(),
    'topProducts'           => $analytics->topSellingProducts(),
    'lowStockVariants'      => $analytics->lowStockVariants(),
    'subscription'          => $subscriptionGuard->getSubscription(),
    'hasActiveSubscription' => $subscriptionGuard->hasActiveSubscription(),
    'subscriptionStatus'    => $subscriptionGuard->statusLabel(),
    'daysRemaining'         => $subscriptionGuard->daysRemaining(),
]);
?>

<div x-data="{
    chartDays: {{ json_encode($salesByDay->pluck('date')->values()) }},
    chartRevenue: {{ json_encode($salesByDay->pluck('revenue')->values()->map(fn($v) => (float) $v)) }},
    chartOrders: {{ json_encode($salesByDay->pluck('total')->values()->map(fn($v) => (int) $v)) }},
    statusLabels: {{ json_encode($ordersByStatus->pluck('key')->values()) }},
    statusCounts: {{ json_encode($ordersByStatus->pluck('count')->values()->map(fn($v) => (int) $v)) }},
    statusColors: {{ json_encode($ordersByStatus->pluck('color')->values()) }},
    stateLabels: {{ json_encode($ordersByState->pluck('name')->values()) }},
    stateCounts: {{ json_encode($ordersByState->pluck('count')->values()->map(fn($v) => (int) $v)) }},
    stateRevenues: {{ json_encode($ordersByState->pluck('revenue')->values()->map(fn($v) => (float) $v)) }},
    deliveryLabels: {{ json_encode($deliveryTypeBreakdown->pluck('delivery_type')->values()) }},
    deliveryCounts: {{ json_encode($deliveryTypeBreakdown->pluck('count')->values()->map(fn($v) => (int) $v)) }},
    renderCharts() {
        this.$nextTick(() => {
            if (typeof window.renderDashboardCharts === 'function') {
                window.renderDashboardCharts(this);
            }
        });
    }
}" x-init="renderCharts()">

    {{-- KPI Cards — Apple-style large numbers with negative tracking --}}
    <div class="edz-stagger grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
        {{-- Total Orders --}}
        <div class="edz-card edz-card--padded group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.total_orders') }}</p>
                @if ($summary['total_orders_change'] != 0)
                    <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                        {{ $summary['total_orders_change'] > 0 ? 'text-success-700 bg-success-50 dark:text-success-300 dark:bg-success-900/30' : 'text-danger-700 bg-danger-50 dark:text-danger-300 dark:bg-danger-900/30' }}">
                        {{ $summary['total_orders_change'] > 0 ? '▲' : '▼' }} {{ abs($summary['total_orders_change']) }}%
                    </span>
                @endif
            </div>
            <p class="text-3xl font-bold tracking-tighter text-ink leading-none">{{ $summary['total_orders'] }}</p>
            <p class="mt-2 text-xs text-ink-muted">{{ __('dashboard.this_month') }}</p>
        </div>

        {{-- Revenue --}}
        <div class="edz-card edz-card--padded group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.revenue') }}</p>
                <div class="w-8 h-8 rounded-lg bg-success-50 dark:bg-success-900/20 flex items-center justify-center text-success-600">
                    <x-edz.icon name="trending-up" class="w-4 h-4" />
                </div>
            </div>
            <p class="text-3xl font-bold tracking-tighter text-ink leading-none">{{ number_format($summary['revenue'], 0) }}<span class="text-lg font-semibold text-ink-muted ms-1">{{ __('stores.currency_symbol') }}</span></p>
            <p class="mt-2 text-xs text-ink-muted">{{ __('dashboard.delivered_orders') }}</p>
        </div>

        {{-- Confirmation Rate --}}
        <div class="edz-card edz-card--padded group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.confirmation_rate') }}</p>
                <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                    {{ $summary['confirmation_rate'] >= 70 ? 'text-success-700 bg-success-50 dark:text-success-300 dark:bg-success-900/30' : 'text-warning-700 bg-warning-50 dark:text-warning-300 dark:bg-warning-900/30' }}">
                    {{ $summary['confirmation_rate'] }}%
                </span>
            </div>
            <div class="w-full bg-surface-secondary rounded-full h-1.5 overflow-hidden">
                <div class="h-1.5 rounded-full transition-all duration-700 ease-out-expo
                    {{ $summary['confirmation_rate'] >= 70 ? 'bg-success-500' : 'bg-warning-500' }}"
                     style="width: {{ $summary['confirmation_rate'] }}%"></div>
            </div>
            <p class="mt-2 text-xs text-ink-muted">{{ __('dashboard.confirmed_of_total') }}</p>
        </div>

        {{-- Return Rate --}}
        <div class="edz-card edz-card--padded group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.return_rate') }}</p>
                <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                    {{ $summary['return_rate'] <= 10 ? 'text-success-700 bg-success-50 dark:text-success-300 dark:bg-success-900/30' : 'text-danger-700 bg-danger-50 dark:text-danger-300 dark:bg-danger-900/30' }}">
                    {{ $summary['return_rate'] }}%
                </span>
            </div>
            <div class="w-full bg-surface-secondary rounded-full h-1.5 overflow-hidden">
                <div class="h-1.5 rounded-full transition-all duration-700 ease-out-expo
                    {{ $summary['return_rate'] <= 10 ? 'bg-success-500' : 'bg-danger-500' }}"
                     style="width: {{ $summary['return_rate'] }}%"></div>
            </div>
            <p class="mt-2 text-xs text-ink-muted">{{ __('dashboard.return_of_processed') }}</p>
        </div>
    </div>

    {{-- Secondary KPIs --}}
    <div class="edz-stagger grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <div class="edz-card edz-card--padded">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.aov') }}</p>
            <p class="mt-2 text-2xl font-bold tracking-tighter text-ink">{{ number_format($summary['aov'], 0) }} <span class="text-sm font-medium text-ink-muted">{{ __('stores.currency_symbol') }}</span></p>
        </div>
        <div class="edz-card edz-card--padded">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('titles.products') }}</p>
            <p class="mt-2 text-2xl font-bold tracking-tighter text-ink">{{ $summary['active_products'] }} <span class="text-sm font-medium text-ink-muted">/ {{ $summary['total_products'] }}</span></p>
        </div>
        <div class="edz-card edz-card--padded">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('titles.team') }}</p>
            <p class="mt-2 text-2xl font-bold tracking-tighter text-ink">{{ $summary['total_members'] }}</p>
        </div>
    </div>

    {{-- Store Link --}}
    <div class="edz-card edz-card--padded mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl {{ currentStore()?->isPubliclyActive() ? 'bg-success-50 dark:bg-success-900/20 text-success-600' : 'bg-warning-50 dark:bg-warning-900/20 text-warning-600' }} flex items-center justify-center">
                    <x-edz.icon name="external-link" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">{{ __('storefront.your_store_link') }}</p>
                    <p class="text-xs font-mono text-ink-muted">{{ currentStore()?->public_url ?? '-' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                    x-data="{ copied: false }"
                    x-on:click="navigator.clipboard.writeText('{{ currentStore()?->public_url }}'); copied = true; setTimeout(() => copied = false, 2000);"
                    class="edz-btn edz-btn--secondary edz-btn--sm">
                    <x-edz.icon name="copy" class="w-4 h-4 me-1" />
                    <span x-text="copied ? '{{ __('buttons.copied') }}' : '{{ __('buttons.copy_link') }}'"></span>
                </button>
                @if (currentStore()?->isPubliclyActive())
                    <a href="{{ currentStore()?->public_url }}" target="_blank" rel="noopener noreferrer"
                       class="edz-btn edz-btn--primary edz-btn--sm">
                        <x-edz.icon name="external-link" class="w-4 h-4 me-1" />
                        {{ __('storefront.visit_store') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="edz-stagger grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        {{-- Sales Trend --}}
        <div class="lg:col-span-2 edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">{{ __('dashboard.sales_trend') }}</h3>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Orders by Status --}}
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">{{ __('dashboard.orders_by_status') }}</h3>
            @if ($ordersByStatus->isNotEmpty())
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            @else
                <div class="h-64 flex items-center justify-center">
                    <p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Second Charts Row --}}
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

    {{-- Tables Row --}}
    <div class="edz-stagger grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        {{-- Pending Orders --}}
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold tracking-tight text-ink">{{ __('dashboard.pending_confirmation') }}</h3>
                @if ($pendingOrders->count() > 0)
                    <a href="{{ route('merchant.orders.index', currentStore()) }}" wire:navigate
                       class="text-xs font-medium text-accent-600 hover:text-accent-500 transition-colors">
                        {{ __('buttons.view_all') }}
                    </a>
                @endif
            </div>
            @if ($pendingOrders->isNotEmpty())
                <div class="divide-y divide-surface-border">
                    @foreach ($pendingOrders as $order)
                        <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-ink">#{{ $order->number }}</p>
                                <p class="text-xs text-ink-muted">{{ $order->customer_name ?? '-' }} · {{ $order->customer_phone ?? '-' }}</p>
                            </div>
                            <div class="text-end">
                                <p class="text-sm font-bold tracking-tight text-ink">{{ number_format($order->total_amount, 0) }} {{ __('stores.currency_symbol') }}</p>
                                <p class="text-xs text-ink-muted">{{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-edz.icon name="checkmark-circle-outline" class="w-10 h-10 mx-auto text-ink-muted mb-2" />
                    <p class="text-sm text-ink-muted">{{ __('dashboard.no_pending_orders') }}</p>
                </div>
            @endif
        </div>

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

    {{-- Stock Alerts --}}
    @if ($lowStockVariants->isNotEmpty())
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

    {{-- Chart.js rendering --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        window.renderDashboardCharts = function(data) {
            const fontColor = getComputedStyle(document.documentElement).getPropertyValue('--edz-color-text-soft') || '#6b7280';
            const gridColor = getComputedStyle(document.documentElement).getPropertyValue('--edz-color-border') || '#e5e7eb';

            Chart.defaults.color = fontColor;
            Chart.defaults.font.family = "'Inter', 'IBM Plex Sans Arabic', sans-serif";

            if (data.chartDays.length > 0) {
                new Chart(document.getElementById('salesChart'), {
                    type: 'line',
                    data: {
                        labels: data.chartDays.map(d => { const dt = new Date(d); return dt.toLocaleDateString('ar-DZ', { month: 'short', day: 'numeric' }); }),
                        datasets: [{
                            label: '{{ __("dashboard.revenue") }}',
                            data: data.chartRevenue,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#6366f1',
                            yAxisID: 'y',
                        }, {
                            label: '{{ __("dashboard.total_orders") }}',
                            data: data.chartOrders,
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.05)',
                            fill: false,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#22c55e',
                            yAxisID: 'y1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 16, usePointStyle: true } } },
                        scales: {
                            x: { grid: { color: gridColor, drawBorder: false }, border: { display: false } },
                            y: { position: 'left', grid: { color: gridColor, drawBorder: false }, border: { display: false }, title: { display: true, text: '{{ __("stores.currency_symbol") }}' } },
                            y1: { position: 'right', grid: { drawOnChartArea: false }, border: { display: false }, title: { display: true, text: '{{ __("dashboard.orders") }}' } },
                        }
                    }
                });
            }

            if (data.statusLabels.length > 0) {
                const colorMap = {
                    'pending': '#f59e0b', 'confirmed': '#3b82f6', 'preparing': '#8b5cf6',
                    'shipped': '#6366f1', 'in_transit': '#06b6d4', 'out_for_delivery': '#14b8a6',
                    'delivered': '#22c55e', 'completed': '#22c55e', 'cancelled': '#ef4444',
                    'canceled': '#ef4444', 'returned': '#f97316', 'refunded': '#ec4899',
                    'on_hold': '#64748b', 'paid': '#3b82f6', 'draft': '#9ca3af',
                };
                const colors = data.statusLabels.map((k, i) => data.statusColors[i] || colorMap[k] || '#6b7280');

                new Chart(document.getElementById('statusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.statusLabels,
                        datasets: [{ data: data.statusCounts, backgroundColor: colors, borderWidth: 0, hoverOffset: 4 }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, usePointStyle: true } } },
                        cutout: '68%',
                    }
                });
            }
        };
    </script>
</div>
