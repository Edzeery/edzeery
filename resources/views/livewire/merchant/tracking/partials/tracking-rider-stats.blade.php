{{-- Rider tab aggregate stats (Phase D-parity) — mirrors tracking-stats.blade.php. --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="edz-card edz-card--padded flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-accent-surface text-accent-fg-strong flex items-center justify-center">
            <x-edz.icon name="users" class="w-5 h-5" />
        </div>
        <div>
            <div class="text-2xl font-bold text-ink tabular-nums">{{ $this->riderStatsActiveCount }}</div>
            <div class="text-xs text-ink-muted">{{ __('order_flow.rider_stats_active') }}</div>
        </div>
    </div>
    <div class="edz-card edz-card--padded flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-success/10 text-success flex items-center justify-center">
            <x-edz.icon name="cube" class="w-5 h-5" />
        </div>
        <div>
            <div class="text-2xl font-bold text-ink tabular-nums">{{ $this->riderStatsActiveShipments }}</div>
            <div class="text-xs text-ink-muted">{{ __('order_flow.rider_stats_active_shipments') }}</div>
        </div>
    </div>
    <div class="edz-card edz-card--padded flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-warning/10 text-warning flex items-center justify-center">
            <x-edz.icon name="banknotes" class="w-5 h-5" />
        </div>
        <div>
            <div class="text-2xl font-bold text-ink tabular-nums">{{ currency($this->riderStatsCodDueToday) }}</div>
            <div class="text-xs text-ink-muted">{{ __('order_flow.rider_stats_cod_due_today') }}</div>
        </div>
    </div>
</div>