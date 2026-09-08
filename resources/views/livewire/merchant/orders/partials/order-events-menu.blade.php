{{-- Per-row order event-log dropdown (P29.4). Exposed to the row actions column of the orders table and the mobile card. Expects: $orderId, $order (row array), $canViewEvents. On sm+ it anchors to the trigger (viewport-clamped); on phones it becomes a bottom sheet with a scrim. --}}
@if ($canViewEvents)
    <div class="relative shrink-0" x-data="orderEventsMenu($el)" @click.away="close()" data-order-id="{{ $orderId }}"
        data-can-view="{{ $canViewEvents ? '1' : '0' }}">
        <button @click="toggle()" x-ref="evTrigger" type="button"
            class="edz-btn edz-btn--ghost edz-btn--xs shrink-0 {{ ($touch ?? false) ? 'min-h-11 min-w-11' : '' }}"
            title="{{ __('order_flow.order_timeline') }}" :aria-expanded="open.toString()" aria-haspopup="dialog">
            <x-edz.icon name="clock" class="w-4 h-4 shrink-0" />
        </button>

        <x-edz.mobile-bottom-sheet :title="__('order_flow.order_timeline')" icon="clock" close-expr="close()"
            sm-width="sm:w-80" sm-max-height="sm:max-h-[340px]" sm-pad="sm:p-2 sm:pb-2" sm-z=""
            :keep-header-sm="true" :scrim-fade="true">
            @if ($this->eventsPreviewOrderId === $orderId && !empty($this->eventsPreview))
                <ol class="divide-y divide-surface-border">
                    @foreach (array_slice($this->eventsPreview, 0, 4) as $ev)
                        <li class="flex items-start gap-2 px-1 py-2.5 text-xs">
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
                    class="w-full mt-1.5 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-accent-700 bg-accent-50 hover:bg-accent-100 sm:py-1.5 sm:text-xs">
                    {{ __('order_flow.show_more') }}
                    <x-edz.icon name="chevron-down" class="w-3.5 h-3.5 sm:w-3 sm:h-3" />
                </button>
            @else
                <p class="px-1 py-3 text-xs text-ink-muted">{{ __('order_flow.order_timeline_loading') }}</p>
            @endif
        </x-edz.mobile-bottom-sheet>
    </div>
@endif
