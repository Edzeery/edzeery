{{-- Create/Edit order form SHIPPING PARTNER section body — the professional
     order: الشحن → طريقة الدفع → شركة/رجل التوصيل ⇄ نوعية التوصيل
     (home/office). The company picker and the delivery-type toggle share ONE
     responsive row (side by side on ≥sm, stacked on mobile). The office-level
     field does NOT live here: it renders inside the Address section (via
     order-destination-fields → order-office-field) once a stopdesk lane with a
     company is chosen. Needs a `delivery` Alpine binding on an ancestor scope —
     hoisted by order-form-modal to the wrapper that contains both the Address
     and the Shipping Partner sections. --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="edz-label">{{ __('merchant_panel.shipment') }}</label>
        <x-edz.select wire:model="form.shipment_type" :options="$this->formShipmentTypeOptions()" size="sm" />
        @error('form.shipment_type')
            <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>
    <div>
        <label class="edz-label">{{ __('merchant_panel.payment_method') }}</label>
        <x-edz.select wire:model="form.payment_method" :options="[['value' => 'cod', 'label' => __('merchant_panel.cod')]]" size="sm" />
    </div>
</div>

{{-- Delivery company ⇄ delivery type: one row on ≥sm (two cells), stacked on mobile. --}}
<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
    <div>
        <label class="edz-label">{{ __('merchant_panel.delivery_company') }}</label>
        @include('livewire.merchant.orders.partials.partner-picker', ['picker' => 'form'])
    </div>
    <div>
        @include('livewire.merchant.orders.partials.order-delivery-type-toggle')
    </div>
</div>

{{-- Refund request (طلب تعويض أموال) + Can open + Send from warehouse —
     offered only when the chosen carrier provider has BOTH the platform
     capability, its documented API support and the merchant opt-in. --}}
@php
    // The tracking page reuses this partial too, but only the orders page
    // exposes formPartnerCapabilities() — degrade gracefully there.
    $formCaps = method_exists($this, 'formPartnerCapabilities')
        ? $this->formPartnerCapabilities()
        : ['refund_request' => false, 'can_open' => false, 'send_from_carrier_warehouse' => false];
@endphp
@if (($formCaps['refund_request'] ?? false) || ($formCaps['can_open'] ?? false) || ($formCaps['send_from_carrier_warehouse'] ?? false))
    <div class="mt-4 flex flex-wrap items-center gap-6">
        @if ($formCaps['refund_request'])
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="form.refund_request" class="edz-checkbox" />
                <span class="text-sm text-ink">{{ __('merchant_panel.refund_request') }}</span>
                <x-edz.tooltip block label="{{ __('merchant_panel.refund_request_tooltip', ['definition' => __('merchant_panel.refund_request_definition')]) }}">
                    <x-edz.icon name="information-circle" class="w-4 h-4 text-ink-muted cursor-help" />
                </x-edz.tooltip>
            </label>
        @endif
        @if ($formCaps['can_open'])
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="form.can_open" class="edz-checkbox" />
                <span class="text-sm text-ink">{{ __('merchant_panel.can_open') }}</span>
                <x-edz.tooltip block label="{{ __('merchant_panel.can_open_tooltip', ['definition' => __('merchant_panel.can_open_definition')]) }}">
                    <x-edz.icon name="information-circle" class="w-4 h-4 text-ink-muted cursor-help" />
                </x-edz.tooltip>
            </label>
        @endif
        @if ($formCaps['send_from_carrier_warehouse'])
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="form.send_from_carrier_warehouse" class="edz-checkbox" />
                <span class="text-sm text-ink">{{ __('merchant_panel.send_from_carrier_warehouse') }}</span>
                <x-edz.tooltip block label="{{ __('merchant_panel.send_from_carrier_warehouse_tooltip', ['definition' => __('merchant_panel.send_from_carrier_warehouse_definition')]) }}">
                    <x-edz.icon name="information-circle" class="w-4 h-4 text-ink-muted cursor-help" />
                </x-edz.tooltip>
            </label>
        @endif
    </div>
@endif
