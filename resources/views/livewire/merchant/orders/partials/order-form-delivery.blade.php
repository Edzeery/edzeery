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
