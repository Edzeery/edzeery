{{-- Dispatch validation (Phase 36) — barcode handover to the carrier logistics.
     Own section: shown only for carrier-sent, not-yet-validated shipments when the
     acting member holds order.dispatch_validate and the carrier supports it. --}}
@if (
    canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value)
    && ! empty($this->drawerTracking['has_provider'])
    && ! empty($this->drawerTracking['tracking_number'])
    && ! empty($this->drawerTracking['carrier_supports_validation'])
    && empty($this->drawerTracking['carrier_validated_at'])
)
    <section class="mt-5">
        <h4
            class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
            <x-edz.icon name="checkmark-circle" class="w-4 h-4" />
            {{ __('order_flow.validate_shipment_title') }}
        </h4>

        @if (! empty($this->drawerTracking['carrier_validation_error']))
            <x-edz.alert type="danger" class="mb-3">
                {{ $this->drawerTracking['carrier_validation_error'] }}
            </x-edz.alert>
        @endif

        <div class="rounded-xl border border-surface-border bg-surface-tertiary/30 p-3">
            <x-edz.barcode-scan-input
                wireScanMethod="validateShipmentFromBarcode"
                :label="__('order_flow.validate_scan_label')"
                placeholder="{{ __('order_flow.validate_scan_placeholder') }}" />

            <button wire:click="validateShipment('{{ $this->drawerTracking['order_id'] }}')" type="button"
                wire:loading.attr="disabled" wire:target="validateShipment"
                class="mt-3 w-full sm:w-auto edz-btn edz-btn--primary edz-btn--sm justify-center">
                <x-edz.icon name="checkmark-circle" class="w-4 h-4" wire:loading.remove wire:target="validateShipment" />
                <x-edz.spinner wire:target="validateShipment" class="w-4 h-4" />
                <span>{{ __('order_flow.validate_shipment_btn') }}</span>
            </button>

            <p class="mt-2 text-xs text-ink-muted">{{ __('order_flow.validate_shipment_hint') }}</p>
        </div>
    </section>
@elseif (
    canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value)
    && ! empty($this->drawerTracking['has_provider'])
    && ! empty($this->drawerTracking['carrier_validated_at'])
)
    <section class="mt-5">
        <h4
            class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
            <x-edz.icon name="checkmark-circle" class="w-4 h-4" />
            {{ __('order_flow.validate_shipment_title') }}
        </h4>
        <div class="flex items-center justify-between gap-3 rounded-lg border border-surface-border bg-surface p-3">
            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-success-600">
                <x-edz.icon name="check-circle" class="w-4 h-4" />
                {{ __('order_flow.shipment_validated_badge') }}
            </span>
            <span class="text-xs text-ink-muted tabular-nums">
                {{ \Carbon\Carbon::parse($this->drawerTracking['carrier_validated_at'])->translatedFormat('M d, Y H:i') }}
            </span>
        </div>
    </section>
@endif