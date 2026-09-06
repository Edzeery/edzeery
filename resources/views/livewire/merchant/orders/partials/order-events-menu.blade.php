{{-- Per-row order event-log dropdown (P29.4). Exposed to the row actions column of the orders table and the mobile card. Expects: $orderId, $order (row array), $canViewEvents. --}}
@if ($canViewEvents)
    <div class="relative" x-data="orderEventsMenu($el)" @click.away="close()" data-order-id="{{ $orderId }}"
        data-can-view="{{ $canViewEvents ? '1' : '0' }}">
        <button @click="toggle()" x-ref="evTrigger" type="button" class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
            title="{{ __('order_flow.order_timeline') }}">
            <x-edz.icon name="clock" class="w-4 h-4 shrink-0" />
        </button>
        <div x-show="open" x-cloak x-transition
            class="fixed z-[210] w-80 bg-surface border border-surface-border rounded-xl
             shadow-lg p-2 max-h-[340px] overflow-y-auto edz-scroll "
            :style="'top:' + top + 'px; left:' + left + 'px'">
            <p class="text-xs font-semibold text-ink-muted uppercase tracking-wide px-1 mb-1.5">
                {{ __('order_flow.order_timeline') }}
            </p>
            @if ($this->eventsPreviewOrderId === $orderId && !empty($this->eventsPreview))
                <ol class="divide-y divide-surface-border">
                    @foreach (array_slice($this->eventsPreview, 0, 4) as $ev)
                        <li class="flex items-start gap-2 px-1 py-2 text-xs">
                            <span
                                class="mt-1 w-1.5 h-1.5 rounded-full shrink-0 {{ $loop->first ? 'bg-accent-600' : 'bg-surface-border' }}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-ink leading-snug">{{ $ev['message'] ?? '—' }}</p>
                                <p class="text-xs text-ink-muted mt-0.5">
                                    {{ __('order_flow.event_type_' . ($ev['event_type'] ?? 'note')) }}
                                    • {{ \Carbon\Carbon::parse($ev['occurred_at'])->diffForHumans() }}
                                    @if (!empty($ev['actor']['user']['name']))
                                        • {{ $ev['actor']['user']['name'] }}
                                    @endif
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <button type="button" wire:click="openOrderEventsModal('{{ $orderId }}')" @click="close()"
                    class="w-full mt-1 inline-flex items-center justify-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-accent-700 hover:bg-surface-tertiary">
                    {{ __('order_flow.show_more') }}
                    <x-edz.icon name="chevron-down" class="w-3 h-3" />
                </button>
            @else
                <p class="px-1 py-2 text-xs text-ink-muted">{{ __('order_flow.order_timeline_loading') }}</p>
            @endif
        </div>
    </div>
@endif
