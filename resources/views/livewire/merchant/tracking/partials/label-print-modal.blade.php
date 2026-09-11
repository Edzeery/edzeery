{{-- Label-print modal (own sheet — fallback when the carrier has no label endpoint or its label fetch fails; carrier labels open in a new tab via the auth proxy instead). window.print() is scoped to #edz-label-sheet so only the sheet prints. --}}
@if ($this->labelOpen && $this->labelData)
    <x-edz.modal :is-open="$this->labelOpen" @close="$wire.closeLabel()" size="md"
        wire:key="label-print-modal-{{ $this->labelOrderId }}">
        <style>
            @media print {
                body {
                    visibility: hidden;
                }
                #edz-label-sheet,
                #edz-label-sheet * {
                    visibility: visible;
                }
                #edz-label-sheet {
                    position: absolute;
                    inset: 0;
                    margin: 0;
                    width: 100%;
                    max-width: 100%;
                    border: 0;
                    border-radius: 0;
                    box-shadow: none;
                    padding: 8mm;
                }
            }
        </style>

        <div class="relative">
            <div
                class="edz-label-no-print sticky top-0 z-10 flex items-center justify-between gap-2 border-b border-line-200 bg-white px-4 py-3">
                <p class="text-sm font-bold text-ink">{{ __('order_flow.label_store_title') }}</p>
                <div class="flex items-center gap-2">
                    <button wire:click="closeLabel" class="edz-btn edz-btn--ghost edz-btn--sm">
                        <x-edz.icon name="x-mark" class="w-4 h-4" />
                        {{ __('order_flow.label_close') }}
                    </button>
                    <button x-on:click="window.print()" class="edz-btn edz-btn--primary edz-btn--sm">
                        <x-edz.icon name="printer" class="w-4 h-4" />
                        {{ __('order_flow.label_print') }}
                    </button>
                </div>
            </div>

            <div id="edz-label-sheet"
                class="mx-auto my-4 w-full max-w-[360px] overflow-hidden rounded-lg border border-line-200 bg-white text-ink shadow-sm">
                <div class="border-b-2 border-ink bg-neutral-50 px-4 py-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-base font-extrabold">{{ $this->labelData['provider'] ?? $this->labelData['rider'] ?? '—' }}</p>
                        <p class="text-sm font-bold tabular-nums">#{{ $this->labelData['number'] }}</p>
                    </div>
                    @if (! empty($this->labelData['tracking_number']))
                        <p class="text-[11px] font-medium text-ink-muted tabular-nums" dir="ltr">
                            {{ $this->labelData['tracking_number'] }}
                        </p>
                    @endif
                </div>

                <div class="space-y-3 px-4 py-3 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold">{{ $this->labelData['customer'] }}</span>
                        <span class="tabular-nums" dir="ltr">{{ $this->labelData['phone'] }}</span>
                    </div>

                    <div class="break-words text-ink-muted leading-snug">
                        {{ $this->labelData['address'] }}
                        @if (! empty($this->labelData['stopdesk']))
                            <span class="mt-1 block font-semibold text-ink">
                                {{ __('orders.delivery_stopdesk') }}: {{ $this->labelData['stopdesk'] }}
                            </span>
                        @endif
                    </div>

                    @if (! empty($this->labelData['items']))
                        <p class="break-words border-t border-dashed border-line-200 pt-2 text-xs leading-snug text-ink-muted">
                            {{ $this->labelData['items'] }}
                        </p>
                    @endif

                    <div class="flex items-center justify-between border-t-2 border-dashed border-ink pt-2">
                        <span class="font-bold">{{ __('merchant_panel.total') }}</span>
                        <span class="text-base font-extrabold tabular-nums">{{ $this->labelData['total'] }}</span>
                    </div>
                </div>

                <div class="flex flex-col items-center border-t border-line-200 bg-neutral-50 px-4 py-3">
                    <x-edz.barcode :value="$this->labelData['barcode']" :height="64" :show-text="true" />
                    @if (! empty($this->labelData['tracking_number']))
                        <p class="mt-1 text-[11px] font-semibold text-ink">
                            {{ __('order_flow.label_pickup_code') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </x-edz.modal>
@endif