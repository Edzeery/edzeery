{{-- Shared delivery cascade for the delivery quick-edit modal. Owns the
     `delivery` Alpine state (form.delivery_type mirror); the create/edit order
     form uses order-form-delivery (the same pieces with shipment + payment in
     between). Company picker ⇄ delivery-type toggle share ONE responsive row
     (side by side on ≥sm, stacked on mobile). Rider legs are handled server-side:
     the picker switch forces the rider to home delivery and the type toggle
     rejects office lanes. --}}
<div x-data="{ delivery: $wire.form.delivery_type }"
    x-init="$watch('delivery', v => $wire.set('form.delivery_type', v))"
    x-effect="delivery = $wire.form.delivery_type">
    <label class="edz-label">{{ __('merchant_panel.shipping_partner') }}</label>

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
        <div>
            @include('livewire.merchant.orders.partials.partner-picker', ['picker' => 'form'])
        </div>
        <div>
            @include('livewire.merchant.orders.partials.order-delivery-type-toggle')
        </div>
    </div>

    @include('livewire.merchant.orders.partials.order-destination-fields')
</div>
