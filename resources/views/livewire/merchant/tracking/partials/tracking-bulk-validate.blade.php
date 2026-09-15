{{-- Bulk dispatch-validation (Phase 8): carrier-tab FAB + analysis modal.
     Gated by order.dispatch_validate; never in trash mode. The modal analyzes
     the current page's shipments, scans barcodes one-by-one, then hands the
     ready ones to the carrier through chunked /valid/orders. --}}
@if (
    $this->trackingTab === 'carrier'
    && ! $this->showTrash
    && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value)
)
    <button wire:click="openBulkValidateModal" type="button"
        title="{{ __('order_flow.bulk_validate_btn') }}"
        class="fixed bottom-6 end-6 z-40 inline-flex items-center gap-2 rounded-full edz-btn edz-btn--primary edz-btn--sm shadow-lg shadow-ink/20">
        <x-edz.icon name="shield-check" class="w-4 h-4" />
        <span class="hidden sm:inline">{{ __('order_flow.bulk_validate_btn') }}</span>
    </button>
@endif

<x-edz.modal :is-open="$this->showBulkValidateModal" @close="$wire.closeBulkValidateModal()" size="md"
    show-close-button wire:key="tracking-bulk-validate">
    <div class="p-5">
        <h3 class="text-lg font-semibold text-ink mb-1">{{ __('order_flow.validate_shipment_title') }}</h3>
        <p class="text-xs text-ink-muted mb-4">{{ __('order_flow.validate_shipment_hint') }}</p>

        <div class="rounded-xl border border-surface-border bg-surface-tertiary/30 p-3 mb-4">
            <x-edz.barcode-scan-input wireScanMethod="bulkValidateFromBarcode"
                :label="__('order_flow.validate_scan_label')"
                placeholder="{{ __('order_flow.validate_scan_placeholder') }}" />
        </div>

        @if ($this->bulkValidateReadyCount > 0)
            <p class="text-sm font-semibold text-ink mb-2">
                {{ __('order_flow.bulk_validate_ready_title') }} ({{ $this->bulkValidateReadyCount }})
            </p>
            <ul class="space-y-1 max-h-44 overflow-y-auto edz-scroll mb-4">
                @foreach (collect($this->bulkValidateAnalysis)->where('ready', true) as $entry)
                    <li class="flex items-center justify-between gap-2 rounded-md bg-surface px-2 py-1">
                        <span class="inline-flex items-center gap-1.5 text-success-600">
                            <x-edz.icon name="check-circle" class="w-4 h-4" />
                            <span class="text-xs font-medium">#{{ $entry['number'] }}</span>
                        </span>
                        <span class="truncate text-xs text-ink-muted max-w-[55%]">{{ $entry['tracking_number'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($this->bulkValidateSkipCount > 0)
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
            <button wire:click="closeBulkValidateModal" type="button" class="edz-btn edz-btn--ghost"
                wire:loading.attr="disabled" wire:target="confirmBulkValidate">
                {{ __('buttons.cancel') }}
            </button>
            <button wire:click="confirmBulkValidate" type="button"
                @disabled($this->bulkValidateReadyCount === 0 || $this->bulkValidateBusy)
                wire:loading.attr="disabled" wire:target="confirmBulkValidate"
                class="edz-btn edz-btn--primary {{ $this->bulkValidateReadyCount === 0 || $this->bulkValidateBusy ? 'opacity-50 cursor-not-allowed' : '' }}">
                <x-edz.spinner wire:target="confirmBulkValidate" class="w-4 h-4" />
                <span>{{ $this->bulkValidateReadyCount === 0
                    ? __('order_flow.bulk_validate_confirm_none')
                    : ($this->bulkValidateReadyCount === count($this->bulkValidateAnalysis)
                        ? __('order_flow.bulk_validate_confirm')
                        : __('order_flow.bulk_validate_confirm_some', ['count' => $this->bulkValidateReadyCount])) }}</span>
            </button>
        </div>
    </div>
</x-edz.modal>