{{-- Delivery-type toggle (home / office) — shared by the create-edit order form
     (order-form-delivery) and the delivery quick-edit modal (order-delivery-cascade).
     Sits BESIDE the company picker (same responsive row) and spreads to the full
     cell width so both controls look aligned. Requires an Alpine `delivery`
     binding on an ancestor (form.delivery_type mirror). The office lane button
     is rejected server-side when a rider leg is active (changeDeliveryType
     guard): a rider always carries to the address. --}}

<label class="edz-label ">{{ __('merchant_panel.delivery') }}</label>
<div class="flex w-full rounded-lg border border-surface-border overflow-hidden">
    <button type="button" :class="delivery === 'home' ? 'bg-brand-500 text-white' : 'bg-surface text-ink'"
        @click="delivery = 'home'; $wire.changeDeliveryType('home')"
        class="flex-1 px-4 py-2 text-sm font-medium transition-colors">
        <x-edz.icon name="home" class="w-4 h-4 inline mr-1" />
        {{ __('merchant_panel.home_delivery_label') }}
    </button>
    <button type="button" :class="delivery === 'stopdesk' ? 'bg-brand-500 text-white' : 'bg-surface text-ink'"
        @click="delivery = 'stopdesk'; $wire.changeDeliveryType('stopdesk')"
        class="flex-1 px-4 py-2 text-sm font-medium transition-colors">
        <x-edz.icon name="building-storefront" class="w-4 h-4 inline mr-1" />
        {{ __('merchant_panel.stop_desk_label') }}
    </button>
</div>
