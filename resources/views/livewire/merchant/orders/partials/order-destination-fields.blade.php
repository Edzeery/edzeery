{{-- Wilaya → commune → office — shared by the create-edit order form
     (Address section of order-form-modal) and the delivery quick-edit modal
     (order-delivery-cascade). Requires an Alpine `delivery` binding on an
     ancestor (form.delivery_type mirror). The office row only exists for an
     office lane delivered by a company — never for a rider leg or before a
     company is chosen. `$withOffice` defaults to true; the create-edit form
     keeps it enabled so the office follows state/city in the Address section. --}}
<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="edz-label">{{ __('merchant_panel.state') }}</label>
        <x-edz.select wire:model="form.state_id" wire:change="changeFormState($event.target.value)"
            :options="$this->formAvailableStates !== [] ? $this->formAvailableStates : $this->allStates" option-value="id" option-label="name" option-code="state_code" placeholder="—"
            size="sm" search />
        @if ($this->formCoverageHint)
            <p class="text-xs text-warning-500 mt-1">{{ __("order_flow.{$this->formCoverageHint}") }}</p>
        @endif
        @error('form.state_id')
            <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>
    <div>
        <label class="edz-label">{{ __('merchant_panel.city') }}</label>
        <x-edz.select wire:model="form.city_id" wire:change="changeFormCity($event.target.value)"
            :options="$this->formCities" option-value="id"
            option-label="name" placeholder="—" size="sm" search
            lazy source="loadFormCitiesLazy"
            :scope="($this->form['delivery_type'] ?? 'home') . '|' . ($this->form['shipping_provider_id'] ?? '') . '|' . ($this->form['state_id'] ?? '')" />
        @error('form.city_id')
            <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>
</div>

@if ($withOffice ?? true)
    @include('livewire.merchant.orders.partials.order-office-field')
@endif