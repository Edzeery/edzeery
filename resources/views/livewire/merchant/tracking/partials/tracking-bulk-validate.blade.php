{{-- Dispatch-validation (Phase 8) — gated by order.dispatch_validate.
     Two popups:

      1. Scanner / camera modal (toolbar button, only when shipments need
         validation). Every scanned barcode validates IMMEDIATELY via the
         single-order flow and appends its own outcome to the in-modal list:
         order number + tracking + success / error reason. There is no
         separate "اعتماد" confirmation step.

      2. Bulk results popup — opened by the bulk bar's "اعتماد لدى الناقل"
         after a DIRECT carrier handover, listing each selected shipment with
         its tracking number and its success / failure reason.

     Both are @if-guarded and mount fresh, matching the project's modal
     pattern (orders bulk-send, tracking-history, label-print). --}}

@if ($this->showBulkValidateModal)
<x-edz.modal :is-open="true" @edz-modal-closed="$event.target === $event.currentTarget && $wire.closeBulkValidateModal()" size="md"
    show-close-button wire:key="tracking-bulk-validate">
    <div class="p-5">
        <h3 class="text-lg font-semibold text-ink mb-1">{{ __('order_flow.validate_shipment_title') }}</h3>
        <p class="text-xs text-ink-muted mb-4">{{ __('order_flow.validate_shipment_hint') }}</p>

        <div class="rounded-xl border border-surface-border bg-surface-tertiary/30 p-3 mb-4">
            <x-edz.barcode-scan-input wireScanMethod="bulkValidateFromBarcode"
                :label="__('order_flow.validate_scan_label')"
                placeholder="{{ __('order_flow.validate_scan_placeholder') }}" />
        </div>

        @if (count($this->bulkValidateResults) > 0)
            <p class="text-sm font-semibold text-ink mb-2">
                {{ __('order_flow.scan_results_title') }} ({{ count($this->bulkValidateResults) }})
            </p>
            <ul class="space-y-1 max-h-52 overflow-y-auto edz-scroll mb-4">
                @foreach ($this->bulkValidateResults as $entry)
                    <li class="flex items-start justify-between gap-2 rounded-md bg-surface px-2 py-1.5">
                        <span class="inline-flex items-center gap-1.5 {{ $entry['ok'] ? 'text-success-600' : 'text-danger-600' }}">
                            <x-edz.icon name="{{ $entry['ok'] ? 'check-circle' : 'x-circle' }}" class="w-4 h-4 shrink-0" />
                            <span class="text-xs font-medium">#{{ $entry['number'] }} — {{ $entry['tracking_number'] }}</span>
                        </span>
                        <span class="truncate text-xs text-ink-muted max-w-[45%]" title="{{ $entry['message'] }}">{{ $entry['message'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-6 flex justify-end">
            <button wire:click="closeBulkValidateModal" type="button" class="edz-btn edz-btn--ghost">
                {{ __('buttons.cancel') }}
            </button>
        </div>
    </div>
</x-edz.modal>
@endif

@if ($this->showBulkValidateResults)
<x-edz.modal :is-open="true" @edz-modal-closed="$event.target === $event.currentTarget && $wire.closeBulkValidateResults()" size="md"
    show-close-button wire:key="bulk-validate-results">
    <div class="p-5">
        <h3 class="text-lg font-semibold text-ink mb-1">{{ __('order_flow.bulk_validate_results_title') }}</h3>
        <p class="text-xs text-ink-muted mb-4">{{ __('order_flow.bulk_validate_results_hint') }}</p>

        <ul class="space-y-1 max-h-72 overflow-y-auto edz-scroll mb-4">
            @foreach ($this->bulkValidateResults as $entry)
                <li class="flex items-start justify-between gap-2 rounded-md bg-surface px-2 py-1.5">
                    <span class="inline-flex items-center gap-1.5 {{ $entry['ok'] ? 'text-success-600' : 'text-danger-600' }}">
                        <x-edz.icon name="{{ $entry['ok'] ? 'check-circle' : 'x-circle' }}" class="w-4 h-4 shrink-0" />
                        <span class="text-xs font-medium">#{{ $entry['number'] }} — {{ $entry['tracking_number'] }}</span>
                    </span>
                    <span class="truncate text-xs text-ink-muted max-w-[45%]" title="{{ $entry['message'] }}">{{ $entry['message'] }}</span>
                </li>
            @endforeach
        </ul>

        <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2">
            <button wire:click="closeBulkValidateResults" type="button" class="edz-btn edz-btn--ghost">
                {{ __('buttons.close') }}
            </button>
        </div>
    </div>
</x-edz.modal>
@endif