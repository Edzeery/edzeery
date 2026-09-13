{{-- Unified carrier-partner picker (company / rider), used by the create+edit
     form, the delivery quick-edit modal and the confirmation drawer.
     Single combined select: companies → delimiter → riders.
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

    // Single-company store with no riders: no selector at all — the sole
    // carrier is auto-selected (marker asserted by OrdersDefaultProviderTest).
    $singleCompanyMode = $soloCompany && ! $hasRiders;

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
    @if ($singleCompanyMode)
        {{-- A store with a single shipping company: no selector needed, the
             company is auto-selected (marker asserted by OrdersDefaultProviderTest). --}}
        <div data-edz-company-single class="edz-input text-sm bg-surface-secondary">
            {{ $providerOptions[0]['label'] }}
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
            @if (! $isConfirm) :disabled="$loadingOffices" @endif
            class="{{ $soloCompany ? 'edz-company-select' : '' }}" />
        @error($providerBinding)
            <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
        @enderror
    @endif
</div>