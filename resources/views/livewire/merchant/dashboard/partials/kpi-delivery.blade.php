{{-- Delivery KPI row (PHASE 37-N7, D6): the two rate cards that now lead the
     Delivery view — the delivery rate and the return rate. Both read the one
     $deliveryStats report (D10) and follow the active date basis (D3): gross on
     "created", net on "last status". --}}
@php
    $stats = $deliveryStats ?? [];
    $delivered = (int) ($stats['delivered'] ?? 0);
    $returned = (int) ($stats['returned'] ?? 0);
    $denominator = (int) ($stats['denominator'] ?? 0);
    $deliveryRate = (int) ($stats['delivery_rate'] ?? 0);
    $returnRate = (int) ($stats['return_rate'] ?? 0);
    $kind = (string) ($stats['kind'] ?? 'gross');
    $deliveryTarget = (int) config('analytics.targets.delivery_rate');
    $returnTarget = (int) config('analytics.targets.return_rate');
    $deliveryLabel = __($kind === 'net' ? 'dashboard.delivery_rate_net' : 'dashboard.delivery_rate_gross');
    $deliveryBasisKey = $kind === 'net' ? 'dashboard.delivery_rate_net_basis' : 'dashboard.delivery_rate_gross_basis';
    $returnBasisKey = $kind === 'net' ? 'dashboard.return_rate_net_basis' : 'dashboard.return_rate_gross_basis';
@endphp
<div class="edz-stagger grid grid-cols-1 gap-4 mb-6 lg:grid-cols-2">
    {{-- Delivery rate --}}
    <div class="edz-card edz-card--padded group h-full">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ $deliveryLabel }}</p>
            <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                {{ $deliveryRate >= $deliveryTarget ? 'text-success-fg-strong bg-success-surface' : 'text-warning-fg-strong bg-warning-surface' }}">
                {{ $deliveryRate }}%
            </span>
        </div>
        <div class="w-full bg-surface-secondary rounded-full h-1.5 overflow-hidden">
            <div class="h-1.5 rounded-full transition-all duration-700 ease-out-expo
                {{ $deliveryRate >= $deliveryTarget ? 'bg-success-600' : 'bg-warning-600' }}"
                 style="width: {{ $deliveryRate }}%"></div>
        </div>
        <p class="mt-2 text-xs text-ink-muted">{{ __($deliveryBasisKey, [
            'delivered' => $delivered,
            'denominator' => $denominator,
        ]) }}</p>
    </div>

    {{-- Return rate --}}
    <div class="edz-card edz-card--padded group h-full">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('dashboard.return_rate') }}</p>
            <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                {{ $returnRate <= $returnTarget ? 'text-success-fg-strong bg-success-surface' : 'text-danger-fg-strong bg-danger-surface' }}">
                {{ $returnRate }}%
            </span>
        </div>
        <div class="w-full bg-surface-secondary rounded-full h-1.5 overflow-hidden">
            <div class="h-1.5 rounded-full transition-all duration-700 ease-out-expo
                {{ $returnRate <= $returnTarget ? 'bg-success-600' : 'bg-danger-600' }}"
                 style="width: {{ $returnRate }}%"></div>
        </div>
        <p class="mt-2 text-xs text-ink-muted">{{ __(($kind === 'net' ? 'dashboard.return_rate_net_basis' : 'dashboard.return_rate_gross_basis'), [
            'returned' => $returned,
            'denominator' => $denominator,
        ]) }}</p>
    </div>
</div>
