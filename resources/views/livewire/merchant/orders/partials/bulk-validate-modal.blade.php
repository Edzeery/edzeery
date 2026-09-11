{{-- Bulk validate-at-carrier modal (Phase 36): per-order eligibility before handover --}}
@if ($showBulkValidateModal)
    <div @edz-modal-closed.window="$wire.closeBulkValidateModal()">
    <x-edz.modal :is-open="true" size="md" show-close-button wire:key="bulk-validate-modal">
        <div class="p-5">
            <h3 class="text-lg font-semibold text-ink mb-1">{{ __('order_flow.validate_shipment_title') }}</h3>
            <p class="text-xs text-ink-muted mb-4">{{ __('order_flow.validate_shipment_hint') }}</p>

            @if ($this->bulkValidateSkipCount === 0)
                <p class="text-sm text-ink-muted">{{ __('order_flow.bulk_validate_ready_title') }}: {{ $this->bulkValidateReadyCount }}</p>
            @else
                <x-edz.alert type="warning">
                    <p class="font-semibold mb-1">{{ __('order_flow.bulk_validate_skipped_title', ['count' => $this->bulkValidateSkipCount]) }}</p>
                    <ul class="space-y-1 max-h-40 overflow-y-auto edz-scroll">
                        @foreach (collect($this->bulkValidateAnalysis)->where('ready', false) as $entry)
                            <li class="leading-relaxed break-words text-xs">
                                #{{ $entry['number'] }} — {{ implode('، ', $entry['reasons']) }}
                            </li>
                        @endforeach
                    </ul>
                </x-edz.alert>
            @endif

            <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2">
                <button wire:click="closeBulkValidateModal" type="button"
                    class="edz-btn edz-btn--ghost">
                    {{ __('buttons.cancel') }}
                </button>
                @if ($this->bulkValidateSkipCount === 0)
                    <button wire:click="confirmBulkValidate" type="button"
                        class="edz-btn edz-btn--primary"
                        wire:loading.attr="disabled" wire:target="confirmBulkValidate">
                        <x-edz.spinner wire:target="confirmBulkValidate" class="w-4 h-4" />
                        <span>{{ __('order_flow.bulk_validate_confirm') }}</span>
                    </button>
                @elseif ($this->bulkValidateReadyCount > 0)
                    <button wire:click="confirmBulkValidate" type="button"
                        class="edz-btn edz-btn--primary"
                        wire:loading.attr="disabled" wire:target="confirmBulkValidate">
                        <x-edz.spinner wire:target="confirmBulkValidate" class="w-4 h-4" />
                        <span>{{ __('order_flow.bulk_validate_confirm_some', ['count' => $this->bulkValidateReadyCount]) }}</span>
                    </button>
                @else
                    <button type="button" disabled
                        class="edz-btn edz-btn--primary opacity-50 cursor-not-allowed">
                        {{ __('order_flow.bulk_validate_confirm_none') }}
                    </button>
                @endif
            </div>
        </div>
    </x-edz.modal>
    </div>
@endif