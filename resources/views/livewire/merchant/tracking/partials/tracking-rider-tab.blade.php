{{-- Rider tab (advanced grid) — rider aggregate stats + the same unified grid as the carrier tab,
    with the extra 'delivery_rider' column and its header filter. Data flows through
    $this->shipments / visibleColumns / riderRiders / riderStats*.

    When the rider tab's own trash is open, the stats/toolbar are replaced by the shared
    trash banner + a search-only trash grid (query scoped to orders with a delivery rider). --}}
<div>
    @if ($this->showTrash)
        @include('livewire.merchant.tracking.partials.tracking-trash-banner')
        @include('livewire.merchant.tracking.partials.tracking-toolbar')

        <div class="mb-3 flex items-center gap-2 text-sm text-ink-muted">
            <span>{{ $this->filteredTotal }} {{ __('order_flow.tracking_count') }}</span>
        </div>

        @include('livewire.merchant.tracking.partials.tracking-list')
    @else
        @include('livewire.merchant.tracking.partials.tracking-rider-stats')

        {{-- Summary line — filter-responsive riders/shipments (same placement as carrier's count line) --}}
        <div class="mb-3 flex items-center gap-2 text-sm text-ink-muted">
            <span>{{ trans('order_flow.rider_stats_summary', ['riders' => count($this->riderRiders), 'shipments' => $this->riderStatsActiveShipments]) }}</span>
        </div>

        @include('livewire.merchant.tracking.partials.tracking-toolbar')

        <div class="mb-3 flex items-center gap-2 text-sm text-ink-muted">
            <span>{{ $this->filteredTotal }} {{ __('order_flow.tracking_count') }}</span>
        </div>

        @include('livewire.merchant.tracking.partials.tracking-list')
    @endif
</div>