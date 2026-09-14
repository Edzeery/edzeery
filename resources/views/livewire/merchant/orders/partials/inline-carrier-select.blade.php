@php
    // The inline carrier select merges active shipping companies and delivery
    // riders. Companies come first, then a hard divider (mirroring the modal
    // picker) so riders-only stores and mixed stores read the same group list.
    $carrierProviderOptions = collect($this->allProviders)
        ->map(fn ($p) => ['value' => (string) $p['id'], 'label' => $p['name'], 'hint' => null, 'kind' => 'provider'])
        ->values()
        ->all();

    $carrierRiderOptions = collect($this->riderOptions)
        ->map(fn ($r) => $r + ['kind' => 'rider'])
        ->values()
        ->all();

    $carrierSelectOptions = array_merge(
        $carrierProviderOptions,
        ($carrierRiderOptions !== [])
            ? [['value' => '__delimiter__', 'label' => __('merchant_panel.partner_rider'), 'hint' => null, 'kind' => 'delimiter', 'is_divider' => true]]
            : [],
        $carrierRiderOptions,
    );
@endphp

<div class="edz-inline-edit__edit" wire:key="{{ $wireKeyPrefix }}-{{ $orderId }}">
    {{-- A server-rendered value bakes the current model into the hidden input,
         so Alpine pre-selects it on init — the wire:model round-trip alone is a
         hydration race and can leave the trigger showing the placeholder. --}}
    <x-edz.select wire:model="editingValue" :options="$carrierSelectOptions" option-hint="hint"
        optionDivider="is_divider" size="sm" search
        value="{{ (string) ($this->editingValue ?? '') }}"
        placeholder="{{ __('merchant_panel.shipping_provider') }}" />
    <div class="edz-inline-edit__actions">
        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderProvider"
            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
            <span>{{ __('buttons.save') }}</span>
        </button>
        <button type="button" class="edz-inline-edit__cancel"
            @click="$wire.cancelOrderEdit()">{{ __('buttons.cancel') }}</button>
    </div>
    @if ($this->editingError)
        <p class="edz-inline-edit__error">{{ $this->editingError }}</p>
    @endif
</div>