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

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
        {{-- Total Orders --}}
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-ink-400">{{ __('dashboard.total_orders') }}</p>
                @if ($summary['total_orders_change'] != 0)
                    <span class="text-xs font-medium {{ $summary['total_orders_change'] > 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ $summary['total_orders_change'] > 0 ? '+' : '' }}{{ $summary['total_orders_change'] }}%
                    </span>
                @endif
            </div>
            <p class="mt-3 text-2xl font-bold tracking-tight text-ink">{{ $summary['total_orders'] }}</p>
            <p class="mt-1 text-xs text-ink-400">{{ __('dashboard.this_month') }}</p>
        </div>

        {{-- Revenue --}}
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-ink-400">{{ __('dashboard.revenue') }}</p>
            </div>
            <p class="mt-3 text-2xl font-bold tracking-tight text-ink">{{ number_format($summary['revenue'], 0) }} {{ __('stores.currency_symbol') }}</p>
            <p class="mt-1 text-xs text-ink-400">{{ __('dashboard.delivered_orders') }}</p>
        </div>

        {{-- Confirmation Rate --}}
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-ink-400">{{ __('dashboard.confirmation_rate') }}</p>
                <span class="edz-badge {{ $summary['confirmation_rate'] >= 70 ? 'edz-badge--success' : 'edz-badge--warning' }}">
                    {{ $summary['confirmation_rate'] }}%
                </span>
            </div>
            <div class="mt-3 w-full bg-surface-100 dark:bg-ink-800 rounded-full h-2">
                <div class="h-2 rounded-full {{ $summary['confirmation_rate'] >= 70 ? 'bg-success-500' : 'bg-warning-500' }}"
                     style="width: {{ $summary['confirmation_rate'] }}%"></div>
            </div>
            <p class="mt-1 text-xs text-ink-400">{{ __('dashboard.confirmed_of_total') }}</p>
        </div>

        {{-- Return Rate --}}
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-ink-400">{{ __('dashboard.return_rate') }}</p>
                <span class="edz-badge {{ $summary['return_rate'] <= 10 ? 'edz-badge--success' : 'edz-badge--danger' }}">
                    {{ $summary['return_rate'] }}%
                </span>
            </div>
            <div class="mt-3 w-full bg-surface-100 dark:bg-ink-800 rounded-full h-2">
                <div class="h-2 rounded-full {{ $summary['return_rate'] <= 10 ? 'bg-success-500' : 'bg-danger-500' }}"
                     style="width: {{ $summary['return_rate'] }}%"></div>
            </div>
            <p class="mt-1 text-xs text-ink-400">{{ __('dashboard.return_of_processed') }}</p>
        </div>
    </div>

    {{-- Secondary KPIs --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <div class="edz-card edz-card--padded">
            <p class="text-sm font-medium text-ink-400">{{ __('dashboard.aov') }}</p>
            <p class="mt-2 text-xl font-bold text-ink">{{ number_format($summary['aov'], 0) }} {{ __('stores.currency_symbol') }}</p>
        </div>
        <div class="edz-card edz-card--padded">
            <p class="text-sm font-medium text-ink-400">{{ __('titles.products') }}</p>
            <p class="mt-2 text-xl font-bold text-ink">{{ $summary['active_products'] }} <span class="text-sm font-normal text-ink-400">/ {{ $summary['total_products'] }}</span></p>
        </div>
        <div class="edz-card edz-card--padded">
            <p class="text-sm font-medium text-ink-400">{{ __('titles.team') }}</p>
            <p class="mt-2 text-xl font-bold text-ink">{{ $summary['total_members'] }}</p>
        </div>
    </div>

    {{-- Store Link --}}
    <div class="edz-card edz-card--padded mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ currentStore()?->isPubliclyActive() ? 'bg-success-50 dark:bg-success-900/20 text-success-600' : 'bg-warning-50 dark:bg-warning-900/20 text-warning-600' }} flex items-center justify-center">
                    <x-edz.icon name="external-link" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">{{ __('storefront.your_store_link') }}</p>
                    <p class="text-xs font-mono text-ink-400">{{ currentStore()?->public_url ?? '-' }}</p>
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
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        {{-- Sales Trend --}}
        <div class="lg:col-span-2 edz-card edz-card--padded">
            <h3 class="text-sm font-semibold text-ink mb-4">{{ __('dashboard.sales_trend') }}</h3>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Orders by Status --}}
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold text-ink mb-4">{{ __('dashboard.orders_by_status') }}</h3>
            @if ($ordersByStatus->isNotEmpty())
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            @else
                <div class="h-64 flex items-center justify-center">
                    <p class="text-sm text-ink-400">{{ __('dashboard.no_data') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Second Charts Row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        {{-- Geographic Distribution --}}
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold text-ink mb-4">{{ __('dashboard.orders_by_state') }}</h3>
            @if ($ordersByState->isNotEmpty())
                <div class="space-y-3">
                    @php($maxState = $ordersByState->max('count'))
                    @foreach ($ordersByState as $state)
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-medium text-ink w-24 truncate" title="{{ $state->name }}">{{ $state->name }}</span>
                            <div class="flex-1 bg-surface-100 dark:bg-ink-800 rounded-full h-4 overflow-hidden">
                                <div class="h-4 rounded-full bg-accent-500 transition-all"
                                     style="width: {{ $maxState > 0 ? ($state->count / $maxState * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-xs font-medium text-ink w-8 text-right">{{ $state->count }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-32 flex items-center justify-center">
                    <p class="text-sm text-ink-400">{{ __('dashboard.no_data') }}</p>
                </div>
            @endif
        </div>

        {{-- Delivery Type --}}
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold text-ink mb-4">{{ __('dashboard.delivery_breakdown') }}</h3>
            @if ($deliveryTypeBreakdown->isNotEmpty())
                <div class="space-y-4 mt-6">
                    @foreach ($deliveryTypeBreakdown as $dt)
                        @php($total = $deliveryTypeBreakdown->sum('count'))
                        @php($pct = $total > 0 ? round(($dt->count / $total) * 100) : 0)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-ink">{{ $dt->delivery_type === 'home' ? __('orders.delivery_home') : __('orders.delivery_stopdesk') }}</span>
                                <span class="text-sm font-semibold text-ink">{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-surface-100 dark:bg-ink-800 rounded-full h-3">
                                <div class="h-3 rounded-full {{ $dt->delivery_type === 'home' ? 'bg-accent-500' : 'bg-success-500' }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-ink-400">{{ $dt->count }} {{ __('orders.orders') }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-32 flex items-center justify-center">
                    <p class="text-sm text-ink-400">{{ __('dashboard.no_data') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Tables Row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        {{-- Pending Orders --}}
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-ink">{{ __('dashboard.pending_confirmation') }}</h3>
                @if ($pendingOrders->count() > 0)
                    <a href="{{ route('merchant.orders.index', currentStore()) }}" wire:navigate
                       class="text-xs font-medium text-accent-600 hover:text-accent-500">
                        {{ __('buttons.view_all') }}
                    </a>
                @endif
            </div>
            @if ($pendingOrders->isNotEmpty())
                <div class="divide-y divide-surface-100 dark:divide-ink-800">
                    @foreach ($pendingOrders as $order)
                        <div class="flex items-center justify-between py-3 {{ $loop->last ? 'border-b-0' : '' }}">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-ink">#{{ $order->number }}</p>
                                <p class="text-xs text-ink-400">{{ $order->customer_name ?? '-' }} · {{ $order->customer_phone ?? '-' }}</p>
                            </div>
                            <div class="text-end">
                                <p class="text-sm font-semibold text-ink">{{ number_format($order->total_amount, 0) }} {{ __('stores.currency_symbol') }}</p>
                                <p class="text-xs text-ink-400">{{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <p class="text-sm text-ink-400">{{ __('dashboard.no_pending_orders') }}</p>
                </div>
            @endif
        </div>

        {{-- Top Selling Products --}}
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-ink">{{ __('dashboard.top_products') }}</h3>
                <a href="{{ route('merchant.products.index', currentStore()) }}" wire:navigate
                   class="text-xs font-medium text-accent-600 hover:text-accent-500">
                    {{ __('buttons.view_all') }}
                </a>
            </div>
            @if ($topProducts->isNotEmpty())
                <div class="divide-y divide-surface-100 dark:divide-ink-800">
                    @foreach ($topProducts as $product)
                        <div class="flex items-center justify-between py-3 {{ $loop->last ? 'border-b-0' : '' }}">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-ink truncate">{{ $product->name }}</p>
                                <p class="text-xs text-ink-400">{{ $product->total_qty }} {{ __('orders.units_sold') }}</p>
                            </div>
                            <span class="text-sm font-semibold text-ink">{{ number_format($product->total_revenue, 0) }} {{ __('stores.currency_symbol') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <p class="text-sm text-ink-400">{{ __('dashboard.no_data') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Stock Alerts --}}
    @if ($lowStockVariants->isNotEmpty())
        <div class="edz-card edz-card--padded mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-ink">{{ __('titles.stock_alerts') }}</h3>
                <a href="{{ route('merchant.stock-alerts.index', currentStore()) }}"
                   class="text-xs font-medium text-danger-600 hover:text-danger-500">
                    {{ __('buttons.view_all') }}
                </a>
            </div>
            <div class="divide-y divide-surface-100 dark:divide-ink-800">
                @foreach ($lowStockVariants as $variant)
                    <div class="flex items-center justify-between py-3 {{ $loop->last ? 'border-b-0' : '' }}">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-ink truncate">{{ $variant->product->name }}</p>
                            <p class="text-xs text-ink-400">
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
            const fontColor = getComputedStyle(document.documentElement).getPropertyValue('--edz-text-secondary') || '#6b7280';
            const gridColor = getComputedStyle(document.documentElement).getPropertyValue('--edz-border') || '#e5e7eb';

            Chart.defaults.color = fontColor;

            if (data.chartDays.length > 0) {
                new Chart(document.getElementById('salesChart'), {
                    type: 'line',
                    data: {
                        labels: data.chartDays.map(d => { const dt = new Date(d); return dt.toLocaleDateString('ar-DZ', { month: 'short', day: 'numeric' }); }),
                        datasets: [{
                            label: '{{ __("dashboard.revenue") }}',
                            data: data.chartRevenue,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y',
                        }, {
                            label: '{{ __("dashboard.total_orders") }}',
                            data: data.chartOrders,
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            fill: false,
                            tension: 0.4,
                            yAxisID: 'y1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } },
                        scales: {
                            x: { grid: { color: gridColor } },
                            y: { position: 'left', grid: { color: gridColor }, title: { display: true, text: '{{ __("stores.currency_symbol") }}' } },
                            y1: { position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: '{{ __("dashboard.orders") }}' } },
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
                        datasets: [{ data: data.statusCounts, backgroundColor: colors, borderWidth: 0 }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8 } } },
                        cutout: '65%',
                    }
                });
            }
        };
    </script>
</div>
