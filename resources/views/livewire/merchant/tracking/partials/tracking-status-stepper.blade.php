{{-- Carrier-tracking stepper (Phase 7) — horizontal stage progression shown at the top of the
    status popup. Stages follow OrderWorkflow::carrier(); the reached stage is highlighted with
    the mystatuskit colour, done stages show a check, and the returned branch sits after delivered.
    Exceptional terminal states (lost/damaged/failed_attempt/returning) leave the rail neutral and
    render as a kit chip next to it. Reads $this->statusHistoryMeta. --}}
@php
    $trkStepKeys  = ['shipped', 'in_transit', 'out_for_delivery', 'delivered', 'returned'];
    $trkCurrent   = $this->statusHistoryMeta['tracking_status'] ?? null;
    $trkStepIndex = array_search($trkCurrent, $trkStepKeys, true);
    $trkStepIndex = $trkStepIndex === false ? null : $trkStepIndex;
    $trkHasBranch = $trkStepIndex === null; // exceptional state → neutral rail + chip
@endphp

<div class="mb-5 mt-1">
    <div class="relative flex items-center">
        @foreach ($trkStepKeys as $trkI => $trkKey)
            @php
                $trkStepKit = \Edzeery\MyStatusKit\Facades\Status::for('tracking', $trkKey);
                $trkDone    = $trkStepIndex !== null && $trkI < $trkStepIndex;
                $trkIsCur   = $trkStepIndex !== null && $trkI === $trkStepIndex;
            @endphp
            <div class="flex flex-col items-center shrink-0">
                <span
                    class="{{ $trkIsCur ? ($trkStepKit->color() ?? 'bg-accent-600 text-white') : ($trkDone ? 'bg-accent-600 text-white' : 'bg-surface-tertiary text-ink-muted border border-surface-border') }} relative z-10 flex items-center justify-center w-6 h-6 rounded-full">
                    @if ($trkDone)
                        <x-edz.icon name="check" class="w-3.5 h-3.5" />
                    @elseif ($trkIsCur)
                        <span class="w-2 h-2 rounded-full bg-white/90"></span>
                    @else
                        <span class="text-[10px] font-semibold leading-none">{{ $trkI + 1 }}</span>
                    @endif
                </span>
                <span class="mt-1.5 text-[10px] font-medium whitespace-nowrap {{ $trkIsCur ? 'text-ink font-semibold' : 'text-ink-muted' }}">{{ $trkStepKit->label() }}</span>
            </div>

            @if ($trkI < count($trkStepKeys) - 1)
                <span class="mx-1 sm:mx-2 h-0.5 flex-1 min-w-3 rounded-full {{ $trkStepIndex !== null && $trkI < $trkStepIndex ? 'bg-accent-600' : 'bg-surface-border' }}"></span>
            @endif
        @endforeach
    </div>

    @if ($trkHasBranch)
        @php
            $trkBranchKit = $trkCurrent
                ? \Edzeery\MyStatusKit\Facades\Status::for('tracking', $trkCurrent)
                : null;
        @endphp
        @if ($trkBranchKit)
            <div class="mt-3 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {{ $trkBranchKit->color() }}">
                {!! $trkBranchKit->icon(null, 'w-3.5 h-3.5') !!}
                {{ $trkBranchKit->label() }}
            </div>
        @endif
    @endif
</div>