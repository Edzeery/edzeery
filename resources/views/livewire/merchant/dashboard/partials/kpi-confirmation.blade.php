{{-- Confirmation KPI row (PHASE 37-L split): the confirmation rate from the one
     report, the pending count from the scoped summary, and the pending queue.
     The confirmation rate is never re-computed here — it is the report's. --}}
@php
    $showPendingCard = $canConfirm;
@endphp
@if ($canTopKpis || $showPendingCard)
<div class="edz-stagger grid grid-cols-1 gap-4 mb-6 lg:grid-cols-3">
    @if ($canTopKpis)
    <div @class([
        'grid grid-cols-1 gap-4 sm:grid-cols-2',
        'lg:col-span-2' => $showPendingCard,
        'lg:col-span-3' => ! $showPendingCard,
    ])>
        {{-- Confirmation Rate --}}
        @php
            $confRate = (int) ($confirmationStats['rate'] ?? 0);
            $confTarget = (int) config('analytics.targets.confirmation_rate');
        @endphp
        <div class="edz-card edz-card--padded group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.confirmation_rate') }}</p>
                <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                    {{ $confRate >= $confTarget ? 'text-success-fg-strong bg-success-surface' : 'text-warning-fg-strong bg-warning-surface' }}">
                    {{ $confRate }}%
                </span>
            </div>
            <div class="w-full bg-surface-secondary rounded-full h-1.5 overflow-hidden">
                <div class="h-1.5 rounded-full transition-all duration-700 ease-out-expo
                    {{ $confRate >= $confTarget ? 'bg-success-500' : 'bg-warning-500' }}"
                     style="width: {{ $confRate }}%"></div>
            </div>
            <p class="mt-2 text-xs text-ink-muted">{{ __('dashboard.confirmation_rate_basis', [
                'confirmed' => (int) ($confirmationStats['confirmed'] ?? 0),
                'denominator' => (int) ($confirmationStats['denominator'] ?? 0),
            ]) }}</p>
        </div>
        {{-- Awaiting Confirmation --}}
        <div class="edz-card edz-card--padded group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.pending_confirmation_count') }}</p>
            </div>
            {{-- pending_count comes from the summary, which now walks the same
                 scoped read (product + date basis) as the report. --}}
            <p class="text-3xl font-bold tracking-tighter text-ink leading-none">{{ $summary['pending_count'] }}</p>
            <p class="mt-2 text-xs text-ink-muted">{{ $periodLabel }}</p>
        </div>
    </div>
    @endif
    @if ($showPendingCard)
    <div class="lg:col-span-1">
        @include('livewire.merchant.dashboard.partials.pending-orders')
    </div>
    @endif
</div>
@endif
