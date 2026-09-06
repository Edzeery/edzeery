{{-- Shared order-events timeline (audit log), used by the order-details drawer (orders index) and the tracking drawer (tracking index). Guards are handled at the include site. --}}
@php
    $timelineEvents = $events ?? [];
    $timelineDays = collect($timelineEvents)
        ->groupBy(fn ($ev) => \Carbon\Carbon::parse($ev['occurred_at'] ?? now())->format('Y-m-d'));
    $timelineNewestEventId = $timelineEvents[0]['id'] ?? null;
    $timelineIcon = $icon ?? 'clock';
@endphp

<section class="mt-5">
    <h4
        class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
        <x-edz.icon :name="$timelineIcon" class="w-4 h-4" />
        {{ __('order_flow.order_timeline') }}
    </h4>
    <div
        class="rounded-xl border border-surface-border overflow-hidden bg-surface-tertiary/30">
        @foreach ($timelineDays as $dayKey => $dayEvents)
            @php
                $evDay = \Carbon\Carbon::parse($dayKey);
            @endphp
            <div class="px-3 pt-3">
                <p
                    class="text-[11px] font-semibold uppercase tracking-wide text-ink-muted">
                    @if ($evDay->isToday())
                        {{ __('order_flow.event_day_today') }}
                    @elseif ($evDay->isYesterday())
                        {{ __('order_flow.event_day_yesterday') }}
                    @else
                        {{ $evDay->translatedFormat('l, M j') }}
                    @endif
                </p>
            </div>
            <ol class="divide-y divide-surface-border">
                @foreach ($dayEvents as $ev)
                    <li class="flex items-start gap-3 px-3 py-2.5 text-sm">
                        <span
                            class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ ($ev['id'] ?? null) === $timelineNewestEventId ? 'bg-accent-600' : 'bg-surface-border' }}"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-ink leading-snug">{{ $ev['message'] ?? '—' }}</p>
                            <p
                                class="text-xs text-ink-muted mt-0.5 flex flex-wrap items-center gap-x-2">
                                <span>{{ __('order_flow.event_type_' . ($ev['event_type'] ?? 'note')) }}</span>
                                <span>•</span>
                                <span>{{ \Carbon\Carbon::parse($ev['occurred_at'])->format('H:i') }}</span>
                                @if (!empty($ev['actor']['user']['name']))
                                    <span>•</span>
                                    <span>{{ $ev['actor']['user']['name'] }}</span>
                                @endif
                                @if (!empty($ev['actor']['role']))
                                    <x-role-badge :role="$ev['actor']['role']" />
                                @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endforeach
    </div>
</section>