{{-- Shared carrier-partner picker (company / rider), used by the create+edit form,
     the delivery quick-edit modal and the confirmation drawer. Exclusive partner:
     picking a rider clears the company (+ office), picking a company clears the rider.
     $picker is 'form' (bindings on $this->form.* + formPartnerType) or 'confirm'. --}}
@php
    if (($picker ?? 'form') === 'confirm') {
        $typeBinding = 'confirmPartnerType';
        $providerBinding = 'confirmProviderId';
        $riderBinding = 'confirmRiderId';
        $switchAction = 'switchConfirmPartner';
        $hasProviderChange = false;
    } else {
        $typeBinding = 'formPartnerType';
        $providerBinding = 'form.shipping_provider_id';
        $riderBinding = 'form.delivery_rider_id';
        $switchAction = 'switchFormPartner';
        $hasProviderChange = true;
    }

    $activeType = (string) ($this->{$typeBinding} ?? 'provider');

    $providerOptions = collect($this->allProviders)
        ->map(fn ($p) => ['value' => (string) $p['id'], 'label' => $p['name'], 'hint' => null, 'kind' => 'provider'])
        ->values()
        ->all();

    $riderOptions = $this->riderOptions;
@endphp

<div class="space-y-3">
    <div class="inline-flex rounded-lg border border-surface-border overflow-hidden">
        <button type="button" wire:click="{{ $switchAction }}('provider')"
            class="px-4 py-2 text-sm font-medium transition-colors {{ $activeType === 'provider' ? 'bg-accent-600 text-white' : 'bg-surface text-ink' }}">
            <x-edz.icon name="truck" class="w-4 h-4 inline mr-1" />
            {{ __('merchant_panel.partner_company') }}
        </button>
        <button type="button" wire:click="{{ $switchAction }}('rider')"
            class="px-4 py-2 text-sm font-medium transition-colors {{ $activeType === 'rider' ? 'bg-accent-600 text-white' : 'bg-surface text-ink' }}">
            <x-edz.icon name="user" class="w-4 h-4 inline mr-1" />
            {{ __('merchant_panel.partner_rider') }}
        </button>
    </div>

    @if ($activeType === 'provider')
        <div>
            <label class="edz-label">{{ __('merchant_panel.shipping_company') }}</label>

            @if (count($providerOptions) === 1)
                {{-- A store with a single shipping company: no selector needed, the
                     company is auto-selected (marker asserted by OrdersDefaultProviderTest). --}}
                <div data-edz-company-single class="edz-input text-sm bg-surface-secondary">
                    {{ $providerOptions[0]['label'] }}
                </div>
            @else
                <x-edz.select wire:model="{{ $providerBinding }}"
                    @if ($hasProviderChange) wire:change="applyProviderScope($event.target.value)" @endif
                    :options="$providerOptions" option-value="value" option-label="label" option-hint="hint"
                    placeholder="{{ __('merchant_panel.select_company') }}" size="sm" search
                    @if ($picker === 'form') :disabled="$loadingOffices" @endif class="edz-company-select" />
                @error($providerBinding)
                    <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            @endif
        </div>
    @else
        <div>
            <label class="edz-label">{{ __('merchant_panel.partner_rider') }}</label>
            <x-edz.select wire:model="{{ $riderBinding }}"
                :options="$riderOptions" option-value="value" option-label="label" option-hint="hint"
                placeholder="{{ __('merchant_panel.select_rider') }}" size="sm" search />
            @error($riderBinding)
                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
    @endif
</div>