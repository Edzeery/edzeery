{{-- Unified carrier-partner picker (company / rider), used by the create+edit
     form, the delivery quick-edit modal and the confirmation drawer.
     Single combined select: companies → delimiter → riders.
     The picker is ALWAYS visible whenever at least one partner exists — even a
     single-company store gets a real select (its sole option pre-selected), so
     the company selection never disappears from the add/edit popup.
     Encoded values route the chosen leg: `p:{id}` / `r:{id}`.
     $picker is 'form' (bindings on $this->form.* + formPartnerType) or 'confirm'. --}}
@php
    $isConfirm = ($picker ?? 'form') === 'confirm';

    $typeBinding = $isConfirm ? 'confirmPartnerType' : 'formPartnerType';
    $providerBinding = $isConfirm ? 'confirmProviderId' : 'form.shipping_provider_id';
    $riderBinding = $isConfirm ? 'confirmRiderId' : 'form.delivery_rider_id';
    $switchAction = $isConfirm ? 'switchConfirmPartner' : 'switchFormPartner';

    $activeValue = (string) ($this->{$typeBinding} ?? '');

    $providerOptions = collect($this->allProviders)
        ->map(fn ($p) => ['value' => 'p:' . $p['id'], 'label' => $p['name'], 'hint' => null, 'kind' => 'provider'])
        ->values()
        ->all();

    $riderOptions = collect($this->riderOptions)
        ->map(fn ($r) => ['value' => 'r:' . (string) ($r['value'] ?? $r['id'] ?? ''), 'label' => $r['label'] ?? $r['name'] ?? '', 'hint' => $r['hint'] ?? null, 'kind' => 'rider'])
        ->values()
        ->all();

    $hasProviders = $providerOptions !== [];
    $hasRiders    = $riderOptions !== [];
    $soloCompany  = count($providerOptions) === 1;

    // Livewire disables the picker while the office dropdown is loading. The
    // condition is hoisted OUT of the component tag on purpose: Blade fails to
    // compile an anonymous component whose attributes contain raw @if/@endif.
    $pickerDisabled = ! $isConfirm && (bool) ($this->loadingOffices ?? false);

    // Unified option list with optional delimiter when both groups exist.
    $unifiedOptions = array_merge(
        $providerOptions,
        ($hasProviders && $hasRiders)
            ? [['value' => '__delimiter__', 'label' => __('merchant_panel.partner_rider'), 'hint' => null, 'kind' => 'delimiter', 'is_divider' => true]]
            : [],
        $riderOptions,
    );
@endphp

<div class="space-y-3">
    @if (! $hasProviders && ! $hasRiders)
        {{-- A store with no active partners: an empty select is a dead, silent
             control. Show an inline message + call-to-action instead (Sub-phase C). --}}
        <div class="rounded-xl border border-dashed border-surface-border bg-surface-secondary px-4 py-3">
            <p class="text-sm text-ink-muted">{{ __('merchant_panel.partner_empty_state') }}</p>
            @if ($settingsStore = currentStore())
                <a href="{{ route('merchant.delivery', $settingsStore) }}" wire:navigate
                    class="mt-1.5 inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700">
                    {{ __('merchant_panel.partner_empty_cta') }}
                    <x-edz.icon name="arrow-right" class="w-4 h-4" />
                </a>
            @endif
        </div>
    @else
        {{-- Deferred wire:model (not .live): the pick must land as ONE atomic
             round-trip — value + switchXxxPartner handler together — so a racing
             second request can never revert the pick (see Sub-phase B issues). --}}
        <x-edz.select wire:model="{{ $typeBinding }}"
            wire:change="{{ $switchAction }}($event.target.value)"
            :options="$unifiedOptions" option-value="value" option-label="label" option-hint="hint"
            optionDivider="is_divider"
            placeholder="{{ __('merchant_panel.select_company') }}" size="sm" search
            :disabled="$pickerDisabled"
            class="{{ $soloCompany ? 'edz-company-select' : '' }}" />
        @error($providerBinding)
            <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
        @enderror
    @endif
</div>