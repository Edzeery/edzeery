{{-- Bulk Send to Carrier (P29.3) --}}
@if ($showBulkSendModal)
    <div @edz-modal-closed.window="$wire.closeBulkSendModal()">
    <x-edz.modal :is-open="true" size="md" show-close-button wire:key="bulk-send-modal">
        <div class="p-5">
            <h3 class="text-lg font-semibold text-ink mb-1">{{ __('order_flow.bulk_send_summary_title') }}</h3>
            <p class="text-xs text-ink-muted mb-4">{{ __('order_flow.bulk_send_summary_subtitle') }}</p>

            @if (empty($this->bulkSendSummary) && $this->bulkSendSkipCount === 0)
                <p class="text-sm text-ink-muted">{{ __('order_flow.bulk_send_no_groups') }}</p>
            @else
                @if (! empty($this->bulkSendSummary))
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-2">
                        {{ __('order_flow.bulk_send_ready_title') }}
                    </h4>
                    <ul class="space-y-2 mb-4">
                        @foreach ($this->bulkSendSummary as $g)
                            <li class="flex items-center justify-between gap-2 rounded-lg border border-surface-border bg-surface-secondary px-3 py-2">
                                <span class="text-sm font-medium text-ink">{{ $g['name'] }}</span>
                                <span class="text-xs font-semibold text-ink-muted">{{ __('order_flow.bulk_send_group_count', ['count' => $g['count']]) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($this->bulkSendSkipCount > 0)
                    <x-edz.alert type="warning">
                        <p class="font-semibold mb-1">{{ __('order_flow.bulk_send_skipped_title', ['count' => $this->bulkSendSkipCount]) }}</p>
                        <ul class="space-y-1 max-h-40 overflow-y-auto edz-scroll">
                            @foreach (collect($this->bulkSendAnalysis)->where('ready', false) as $entry)
                                <li class="leading-relaxed break-words">
                                    #{{ $entry['number'] }} — {{ implode('، ', $entry['reasons']) }}
                                </li>
                            @endforeach
                        </ul>
                    </x-edz.alert>
                @endif
            @endif

            <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2">
                <button wire:click="closeBulkSendModal" type="button"
                    class="edz-btn edz-btn--ghost">
                    {{ __('buttons.cancel') }}
                </button>
                @if ($this->bulkSendSkipCount === 0)
                    <button wire:click="confirmBulkSend" type="button"
                        class="edz-btn edz-btn--primary"
                        wire:loading.attr="disabled">
                        <span>{{ __('order_flow.bulk_send_confirm') }}</span>
                    </button>
                @elseif ($this->bulkSendReadyCount > 0)
                    <button wire:click="confirmBulkSend" type="button"
                        class="edz-btn edz-btn--primary"
                        wire:loading.attr="disabled">
                        <span>{{ __('order_flow.bulk_send_confirm_some', ['count' => $this->bulkSendReadyCount]) }}</span>
                    </button>
                @else
                    <button type="button" disabled
                        class="edz-btn edz-btn--primary opacity-50 cursor-not-allowed">
                        {{ __('order_flow.bulk_send_confirm_none') }}
                    </button>
                @endif
            </div>
        </div>
    </x-edz.modal>
    </div>
@endif