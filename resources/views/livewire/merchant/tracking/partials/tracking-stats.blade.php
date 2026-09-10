{{-- Tracking stats (Phase A) — extracted from the tracking index. --}}
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