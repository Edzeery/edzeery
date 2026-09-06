{{-- Per-row order event-log dropdown (P29.4). Exposed to the row actions column of the orders table and the mobile card. Expects: $orderId, $order (row array), $canViewEvents. On sm+ it anchors to the trigger (viewport-clamped); on phones it becomes a bottom sheet with a scrim. --}}
@if ($canViewEvents)
    <div class="relative shrink-0" x-data="orderEventsMenu($el)" @click.away="close()" data-order-id="{{ $orderId }}"
        data-can-view="{{ $canViewEvents ? '1' : '0' }}">
        <button @click="toggle()" x-ref="evTrigger" type="button" class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
            title="{{ __('order_flow.order_timeline') }}" :aria-expanded="open.toString()" aria-haspopup="dialog">
            <x-edz.icon name="clock" class="w-4 h-4 shrink-0" />
        </button>

        {{-- Mobile scrim — a dimmed backdrop that closes the sheet --}}
        <div x-show="open" x-cloak x-transition.opacity @click="close()"
            class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden"></div>

        {{-- Panel: anchored dropdown on sm+, bottom sheet on phones --}}
        <div x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-3"
            :style="menuStyle"
            class="fixed inset-x-0 bottom-0 sm:inset-x-auto sm:bottom-auto z-[210] w-full sm:w-80
                   rounded-t-2xl sm:rounded-xl border border-b-0 sm:border-b border-surface-border
                   bg-surface p-3 sm:p-2 pb-[calc(1rem+env(safe-area-inset-bottom))] sm:pb-2
                   shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)] sm:shadow-lg
                   max-h-[70vh] sm:max-h-[340px] overflow-y-auto edz-scroll">

            {{-- Grab handle (mobile only) --}}
            <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>

            <div class="flex items-center justify-between gap-2 px-1 mb-1.5">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                    <x-edz.icon name="clock" class="w-3.5 h-3.5 text-ink-muted" />
                    <span>{{ __('order_flow.order_timeline') }}</span>
                </p>
                <button @click="close()" type="button"
                    class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary sm:hidden"
                    title="{{ __('general.close') }}">
                    <x-edz.icon name="x-mark" class="w-4 h-4" />
                </button>
            </div>

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
        </div>
    </div>
@endif
