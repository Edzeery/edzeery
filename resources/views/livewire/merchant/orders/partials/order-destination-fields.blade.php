{{-- Wilaya → commune → office — shared by the create-edit order form
     (order-form-delivery) and the delivery quick-edit modal (order-delivery-cascade).
     Requires an Alpine `delivery` binding on an ancestor (form.delivery_type mirror).
     The office row only exists for an office lane delivered by a company — never
     for a rider leg or before a company is chosen. --}}
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

{{-- Office (office deliveries only, scoped to company + commune) --}}
<div x-show="delivery === 'stopdesk'" x-cloak class="mt-4">
@if (blank($this->form['delivery_rider_id'] ?? null) && filled($this->form['shipping_provider_id'] ?? null))
    <div class="flex items-center gap-2">
        <div class="flex-1">
            <label class="edz-label">{{ __('merchant_panel.office') }}</label>
            <x-edz.select wire:model="form.stopdesk_point_id"
                wire:change="onFormOfficePicked"
                :options="$this->formOffices" option-value="value"
                option-label="label" option-hint="hint" option-code="code"
                placeholder="{{ __('merchant_panel.select_office') }}" size="sm"
                search :disabled="$loadingOffices"
                lazy source="loadFormOfficesLazy"
                :scope="($this->form['shipping_provider_id'] ?? '') . '|' . ($this->form['state_id'] ?? '') . '|' . ($this->form['city_id'] ?? '') . '|' . $this->formOfficesVersion" />
        </div>
        <button type="button" wire:click="refreshFormOffices"
            wire:loading.attr="disabled"
            class="edz-btn edz-btn--ghost edz-btn--sm mt-5 shrink-0 disabled:opacity-50 disabled:pointer-events-none {{ $loadingOffices ? 'opacity-50 pointer-events-none' : '' }}"
            aria-label="{{ __('merchant_panel.refresh_offices') }}">
            <x-edz.icon name="arrow-path" class="w-4 h-4" />
        </button>
    </div>
    @if (empty($this->form['state_id']))
        <p class="text-xs text-ink-muted mt-1">{{ __('storefront.select_state_for_desks') }}</p>
    @elseif (empty($this->form['city_id']))
        <p class="text-xs text-ink-muted mt-1">{{ __('storefront.select_city_for_desks') }}</p>
    @elseif (! $this->formHasOffices)
        <p class="text-xs text-warning-500 mt-1">{{ __('merchant_panel.office_none_for_destination') }}</p>
    @else
        <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.office_hint') }}</p>
    @endif
    @error('form.stopdesk_point_id')
        <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
    @enderror
@endif
</div>