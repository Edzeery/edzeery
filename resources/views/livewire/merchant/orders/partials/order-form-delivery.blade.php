{{-- Create/Edit order form delivery section — the requested professional order:
     شركة/رجل التوصيل → الشحن (carrier-scoped; a rider leg is always "delivery")
     → طريقة الدفع → نوعية التوصيل → الولاية → البلدية → المكتب. Owns the
     `delivery` Alpine state (form.delivery_type mirror) like the delivery
     quick-edit cascade; the shared type-toggle and destination-fields partials
     keep both forms on one source of truth. --}}
<div x-data="{ delivery: $wire.form.delivery_type }"
    x-init="$watch('delivery', v => $wire.set('form.delivery_type', v))"
    x-effect="delivery = $wire.form.delivery_type">
    <label class="edz-label">{{ __('merchant_panel.shipping_partner') }}</label>
    @include('livewire.merchant.orders.partials.partner-picker', ['picker' => 'form'])

    {{-- Shipment → Payment (scoped shipment list; a rider leg is always "delivery") --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
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

    @include('livewire.merchant.orders.partials.order-delivery-type-toggle')

    @include('livewire.merchant.orders.partials.order-destination-fields')
</div>