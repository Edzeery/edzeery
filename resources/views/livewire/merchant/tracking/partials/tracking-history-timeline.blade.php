{{-- Shared tracking-history timeline (OrderTrackingHistory rows, latest first) — owned by the tracking page. Expects: $histories = [ ['status','notes','created_at','by'], ... ]. --}}
@php
    $trkHistories = $histories ?? [];
    $trkIcon = $icon ?? 'clock';
@endphp

<section class="mt-5">
    <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
        <x-edz.icon :name="$trkIcon" class="w-4 h-4" />
        {{ __('order_flow.tracking_history') }}
    </h4>
    @if (!empty($trkHistories))
        <ol class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30">
            @foreach ($trkHistories as $trkI => $trkH)
                <li class="flex items-start gap-3 px-3 py-2.5 text-sm">
                    <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ ($trkH['status'] ?? null) === 'carrier_note' ? 'bg-accent-500' : ($trkI === 0 ? 'bg-accent-600' : 'bg-surface-border') }}"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-ink leading-snug">
                            @php
                                $trkStatus = \App\Enums\Store\OrderTrackingStatus::tryFrom($trkH['status'] ?? null);
                                $trkLabel = ($trkH['status'] ?? null) === 'carrier_note'
                                    ? __('order_flow.carrier_note_status')
                                    : ($trkStatus?->label() ?? ($trkH['status'] ?? '—'));
                            @endphp
                            {{ $trkLabel }}
                            @if (!empty($trkH['notes']))
                                <span class="text-ink-muted">— {{ $trkH['notes'] }}</span>
                            @endif
                        </p>
                        <p class="text-xs text-ink-muted mt-0.5">
                            {{ \Carbon\Carbon::parse($trkH['created_at'] ?? now())->diffForHumans() }}
                            @if (!empty($trkH['by']))
                                • {{ $trkH['by'] }}
                            @endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        <div class="text-xs text-ink-muted">{{ __('order_flow.tracking_history_empty') }}</div>
    @endif
</section>