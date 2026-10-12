{{-- Delivery KPI row (PHASE 37-L split): total orders, delivered revenue,
     return rate and average delivered order value. Reads the shared $summary. --}}
@if ($canTopKpis)
<div class="edz-stagger grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 xl:grid-cols-4">
    {{-- Total Orders --}}
    <div class="edz-card edz-card--padded group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.total_orders') }}</p>
            @if ($summary['has_previous'] && $summary['total_orders_change'] != 0)
                <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                    {{ $summary['total_orders_change'] > 0 ? 'text-success-fg-strong bg-success-surface' : 'text-danger-fg-strong bg-danger-surface' }}">
                    {{ $summary['total_orders_change'] > 0 ? '▲' : '▼' }} {{ abs($summary['total_orders_change']) }}%
                </span>
            @endif
        </div>
        <p class="text-3xl font-bold tracking-tighter text-ink leading-none">{{ $summary['total_orders'] }}</p>
        <p class="mt-2 text-xs text-ink-muted">{{ $periodLabel }}</p>
    </div>

    {{-- Delivered Revenue --}}
    <div class="edz-card edz-card--padded group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.revenue') }}</p>
            <div class="w-8 h-8 rounded-lg bg-success-surface flex items-center justify-center text-success-600">
                <x-edz.icon name="trending-up" class="w-4 h-4" />
            </div>
        </div>
        <p class="text-3xl font-bold tracking-tighter text-ink leading-none">{{ number_format($summary['revenue'], 0) }}<span class="text-lg font-semibold text-ink-muted ms-1">{{ __('stores.currency_symbol') }}</span></p>
        <p class="mt-2 text-xs text-ink-muted">{{ __('dashboard.revenue_delivered_in_period') }}</p>
        <p class="mt-1 text-xs text-ink-muted">{{ $periodLabel }}</p>
    </div>

    {{-- Return Rate --}}
    <div class="edz-card edz-card--padded group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.return_rate') }}</p>
            <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                {{ $summary['return_rate'] <= config('analytics.targets.return_rate') ? 'text-success-fg-strong bg-success-surface' : 'text-danger-fg-strong bg-danger-surface' }}">
                {{ $summary['return_rate'] }}%
            </span>
        </div>
        <div class="w-full bg-surface-secondary rounded-full h-1.5 overflow-hidden">
            <div class="h-1.5 rounded-full transition-all duration-700 ease-out-expo
                {{ $summary['return_rate'] <= config('analytics.targets.return_rate') ? 'bg-success-500' : 'bg-danger-500' }}"
                 style="width: {{ $summary['return_rate'] }}%"></div>
        </div>
        <p class="mt-2 text-xs text-ink-muted">{{ __('dashboard.return_of_processed') }}</p>
    </div>

    {{-- Average Delivered Order Value --}}
    <div class="edz-card edz-card--padded group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.aov') }}</p>
        </div>
        <p class="text-3xl font-bold tracking-tighter text-ink leading-none">{{ number_format($summary['aov'], 0) }}<span class="text-lg font-semibold text-ink-muted ms-1">{{ __('stores.currency_symbol') }}</span></p>
        <p class="mt-2 text-xs text-ink-muted">{{ __('dashboard.revenue_delivered_in_period') }}</p>
        <p class="mt-1 text-xs text-ink-muted">{{ $periodLabel }}</p>
    </div>
</div>
@endif
