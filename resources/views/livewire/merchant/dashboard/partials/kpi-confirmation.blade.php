{{-- Confirmation KPI row (PHASE 37-L split, de-duplicated in 37-M2): the
     confirmation rate from the one report, and the live pending queue — which
     now carries the awaiting-confirmation count as a badge in its own header,
     so the count is not repeated in a second card. --}}
@php
    $showPendingCard = $canConfirm;
@endphp
@if ($canTopKpis || $showPendingCard)
<div class="edz-stagger grid grid-cols-1 gap-4 mb-6 lg:grid-cols-3">
    @if ($canTopKpis)
    {{-- Confirmation Rate — the one home of the rate and its badge. --}}
    @php
        $confRate = (int) ($confirmationStats['rate'] ?? 0);
        $confTarget = (int) config('analytics.targets.confirmation_rate');
    @endphp
    <div @class(['lg:col-span-1', 'lg:col-span-3' => ! $showPendingCard])>
        <div class="edz-card edz-card--padded group h-full">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.confirmation_rate') }}</p>
                <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                    {{ $confRate >= $confTarget ? 'text-success-fg-strong bg-success-surface' : 'text-warning-fg-strong bg-warning-surface' }}">
                    {{ $confRate }}%
                </span>
            </div>
            <div class="w-full bg-surface-secondary rounded-full h-1.5 overflow-hidden">
                <div class="h-1.5 rounded-full transition-all duration-700 ease-out-expo
                    {{ $confRate >= $confTarget ? 'bg-success-600' : 'bg-warning-600' }}"
                     style="width: {{ $confRate }}%"></div>
            </div>
            <p class="mt-2 text-xs text-ink-muted">{{ __('dashboard.confirmation_rate_basis', [
                'confirmed' => (int) ($confirmationStats['confirmed'] ?? 0),
                'denominator' => (int) ($confirmationStats['denominator'] ?? 0),
            ]) }}</p>
        </div>
    </div>
    @endif
    @if ($showPendingCard)
    <div @class(['lg:col-span-2', 'lg:col-span-3' => ! $canTopKpis])>
        @include('livewire.merchant.dashboard.partials.pending-orders')
    </div>
    @endif
</div>
@endif
