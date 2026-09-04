<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StorePermissionEnum;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'providers' => [],

    // Carrier catalog (platform -> sub-companies)
    'platforms' => [],
    'standaloneCarriers' => [],

    // Provider modal
    'showProviderModal' => false,
    'editingProviderId' => null,
    'providerForm' => [
        'platform_id' => '',
        'carrier_id' => '',
        'name' => '',
        'credential_values' => [],
        'is_active' => true,
        'is_default' => false,
    ],
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value) ||
        canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->loadData();
});

$loadData = function (): void {
    $storeId = currentStoreId();

    $this->platforms = CarrierPlatform::active()
        ->with(['carriers' => fn ($q) => $q->active()->orderBy('sort_order')->orderBy('name')])
        ->orderBy('name')
        ->get()
        ->map(fn (CarrierPlatform $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'carriers' => $p->carriers->map(
                fn ($c) => $this->carrierOption($c)
            )->all(),
        ])
        ->all();

    $this->standaloneCarriers = Carrier::active()
        ->whereNull('platform_id')
        ->orderBy('name')
        ->get()
        ->map(fn ($c) => $this->carrierOption($c))
        ->all();

    $this->providers = ShippingProvider::where('store_id', $storeId)
        ->with('carrierPlatform', 'carrier')
        ->withCount('deliveryRates')
        ->orderBy('name')
        ->get()
        ->map(fn (ShippingProvider $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'platform' => $p->carrierPlatform?->name,
            'carrier' => $p->carrier?->name,
            'credentials_count' => count((array) ($p->credentials ?? [])),
            'rates_count' => $p->delivery_rates_count,
            'is_active' => $p->is_active,
            'is_default' => $p->is_default,
        ])
        ->all();
};

$carrierOption = function (Carrier $c): array {
    return [
        'id' => $c->id,
        'name' => $c->name,
        'code' => $c->code,
        'credential_fields' => $c->credentialFieldList(),
    ];
};

$providerPlatformOptions = function (): array {
    $opts = collect($this->platforms)
        ->map(fn ($p) => ['value' => $p['id'], 'label' => $p['name'], 'hint' => count($p['carriers'])])
        ->all();

    if (! empty($this->standaloneCarriers)) {
        $opts[] = [
            'value' => '__standalone__',
            'label' => __('merchant_panel.standalone_carriers'),
            'hint' => count($this->standaloneCarriers),
        ];
    }

    return $opts;
};

$providerCarrierOptions = function (): array {
    $platformId = $this->providerForm['platform_id'];

    if ($platformId === '__standalone__') {
        return $this->standaloneCarriers;
    }

    if (! $platformId) {
        return [];
    }

    return collect($this->platforms)->firstWhere('id', $platformId)['carriers'] ?? [];
};

$selectedCarrier = function (): ?array {
    $carrierId = $this->providerForm['carrier_id'];

    if (! $carrierId) {
        return null;
    }

    return collect($this->providerCarrierOptions())->firstWhere('id', $carrierId);
};

$selectProviderPlatform = function (string $platformId): void {
    $this->providerForm['platform_id'] = $platformId;
    $this->providerForm['carrier_id'] = '';
    $this->providerForm['name'] = '';
    $this->providerForm['credential_values'] = [];
};

$selectProviderCarrier = function (string $carrierId): void {
    $this->providerForm['carrier_id'] = $carrierId;
    $this->providerForm['credential_values'] = [];

    $carrier = collect($this->providerCarrierOptions())->firstWhere('id', $carrierId);

    if ($carrier) {
        $this->providerForm['name'] = $carrier['name'];

        foreach ($carrier['credential_fields'] as $field) {
            $this->providerForm['credential_values'][$field['key']] = '';
        }
    }
};

$openProviderModal = function (?string $providerId = null): void {
    if ($providerId) {
        $provider = ShippingProvider::where('store_id', currentStoreId())->findOrFail($providerId);

        $this->editingProviderId = $provider->id;
        $this->providerForm['platform_id'] = $provider->carrier_platform_id ?? ($provider->carrier_id ? '__standalone__' : '');
        $this->providerForm['carrier_id'] = $provider->carrier_id ?? '';
        $this->providerForm['name'] = $provider->name;
        $this->providerForm['is_active'] = $provider->is_active;
        $this->providerForm['is_default'] = $provider->is_default;

        $credentials = (array) ($provider->credentials ?? []);
        $this->providerForm['credential_values'] = collect($this->providerCarrierOptions())
            ->firstWhere('id', $provider->carrier_id)['credential_fields'] ?? [];
        $values = [];
        foreach ($this->providerForm['credential_values'] as $field) {
            $values[$field['key']] = $credentials[$field['key']] ?? '';
        }
        $this->providerForm['credential_values'] = $values;
    } else {
        $this->editingProviderId = null;
        $this->providerForm = [
            'platform_id' => '',
            'carrier_id' => '',
            'name' => '',
            'credential_values' => [],
            'is_active' => true,
            'is_default' => false,
        ];
    }

    $this->showProviderModal = true;
};

$saveProvider = function (): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    $carrier = $this->selectedCarrier();

    $rules = [
        'providerForm.name' => 'required|string|max:255',
        'providerForm.carrier_id' => 'required',
    ];

    if ($carrier) {
        foreach ($carrier['credential_fields'] as $field) {
            if (! empty($field['required'])) {
                $rules["providerForm.credential_values.{$field['key']}"] = 'required|string';
            }
        }
    }

    $this->validate($rules);

    $data = [
        'name' => $this->providerForm['name'],
        'carrier_platform_id' => $this->providerForm['platform_id'] === '__standalone__'
            ? null
            : ($this->providerForm['platform_id'] ?: null),
        'carrier_id' => $this->providerForm['carrier_id'],
        'credentials' => array_filter($this->providerForm['credential_values'] ?? [], fn ($v) => $v !== '' && $v !== null),
        'is_active' => $this->providerForm['is_active'],
        'is_default' => $this->providerForm['is_default'],
    ];

    $storeId = currentStoreId();

    DB::transaction(function () use ($storeId, $data) {
        if ($data['is_default']) {
            ShippingProvider::where('store_id', $storeId)->update(['is_default' => false]);
        }

        if ($this->editingProviderId) {
            ShippingProvider::where('store_id', $storeId)->findOrFail($this->editingProviderId)->update($data);
        } else {
            ShippingProvider::create(array_merge($data, ['store_id' => $storeId]));
        }
    });

    $this->showProviderModal = false;
    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.provider_saved'));
};

$toggleProviderActive = function (string $id): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    $provider = ShippingProvider::where('store_id', currentStoreId())->findOrFail($id);
    $provider->update(['is_active' => ! $provider->is_active]);
    $this->loadData();
};

$deleteProvider = function (string $id): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    $storeId = currentStoreId();

    $hasOrders = DB::table('orders')
        ->where('store_id', $storeId)
        ->where('shipping_provider_id', $id)
        ->exists();

    if ($hasOrders) {
        $this->dispatch('swal', type: 'error', title: __('merchant_panel.provider_has_orders'));
        return;
    }

    ShippingProvider::where('store_id', $storeId)->findOrFail($id)->delete();
    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.provider_deleted'));
};
?>

<div>
    <x-edz.page-header title="{{ __('merchant_panel.delivery_companies') }}"
        description="{{ __('merchant_panel.delivery_companies_desc') }}">
    </x-edz.page-header>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-ink-muted">{{ __('merchant_panel.tab_providers_desc') }}</p>
        @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
            <button wire:click="openProviderModal" class="edz-btn edz-btn--primary edz-btn--sm">
                <x-edz.icon name="plus" class="w-4 h-4" />
                {{ __('merchant_panel.new_provider') }}
            </button>
        @endif
    </div>

    @if (!empty($providers))
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($providers as $provider)
                <div wire:key="provider-{{ $provider['id'] }}" class="edz-card edz-card--padded">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-brand-surface flex items-center justify-center">
                                <x-edz.icon name="truck" class="w-5 h-5 text-brand-500" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-ink truncate">{{ $provider['name'] }}</p>
                                <p class="text-xs text-ink-muted truncate">
                                    @if ($provider['carrier'])
                                        {{ $provider['carrier'] }}@if ($provider['platform']) · {{ $provider['platform'] }}@endif
                                    @else
                                        {{ __('merchant_panel.legacy_provider') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            @if ($provider['is_default'])
                                <span class="edz-badge edz-badge--success">{{ __('merchant_panel.default_badge') }}</span>
                            @endif
                            <button type="button" wire:click="toggleProviderActive('{{ $provider['id'] }}')"
                                    class="cursor-pointer {{ $provider['is_active'] ? 'edz-badge edz-badge--success' : 'edz-badge edz-badge--neutral' }}">
                                {{ $provider['is_active'] ? __('merchant_panel.provider_active') : __('merchant_panel.provider_inactive') }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-ink-muted">{{ __('merchant_panel.credential_values') }}</span>
                            <span class="font-medium text-ink">
                                @if ($provider['credentials_count'] > 0)
                                    {{ $provider['credentials_count'] }} {{ __('merchant_panel.credentials_count') }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink-muted">{{ __('merchant_panel.prices_linked') }}</span>
                            <span class="font-medium text-ink">{{ $provider['rates_count'] }}</span>
                        </div>
                    </div>

                    @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                        <div class="flex items-center justify-end gap-1 mt-4 pt-3 border-t border-surface-border">
                            <button type="button" aria-label="{{ __('merchant_panel.edit_provider') }}"
                                    wire:click="openProviderModal('{{ $provider['id'] }}')"
                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                <x-edz.icon name="edit" class="w-4 h-4" />
                            </button>
                            <button type="button" aria-label="{{ __('merchant_panel.confirm_delete_provider') }}"
                                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                    x-data
                                    x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete()) await $wire.deleteProvider('{{ $provider['id'] }}') })()">
                                <x-edz.icon name="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="edz-card p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                <x-edz.icon name="truck" class="w-8 h-8 text-ink-muted opacity-40" />
            </div>
            <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_providers_yet') }}</p>
            @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                <button wire:click="openProviderModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('merchant_panel.new_provider') }}
                </button>
            @endif
        </div>
    @endif

    {{-- ============ PROVIDER MODAL ============ --}}
    @if ($showProviderModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="lg"
            wire:key="provider-modal-{{ $editingProviderId ?? 'new' }}-{{ $providerForm['carrier_id'] ?: 'no-carrier' }}">
            <form wire:submit="saveProvider">
                <div class="p-6 space-y-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">
                            {{ $editingProviderId ? __('merchant_panel.edit_provider') : __('merchant_panel.new_provider') }}
                        </h3>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="set('showProviderModal', false)">
                            <x-edz.icon name="x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- 2-level carrier selection --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.platform') }}</label>
                            <x-edz.select wire:model="providerForm.platform_id"
                                wire:change="selectProviderPlatform($event.target.value)"
                                :options="$this->providerPlatformOptions()"
                                placeholder="{{ __('merchant_panel.select_platform') }}" size="sm" search />
                            @error('providerForm.platform_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.carrier') }}</label>
                            <x-edz.select wire:key="carrier-options-{{ $providerForm['platform_id'] ?: 'none' }}"
                                wire:model="providerForm.carrier_id"
                                wire:change="selectProviderCarrier($event.target.value)"
                                :options="$this->providerCarrierOptions()" option-value="id" option-label="name"
                                placeholder="{{ $providerForm['platform_id'] ? __('merchant_panel.select_carrier') : __('merchant_panel.select_platform_first') }}"
                                size="sm" search />
                            @error('providerForm.carrier_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.provider_name') }} *</label>
                            <input type="text" wire:model="providerForm.name" class="edz-input text-sm" required>
                            @error('providerForm.name')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Dynamic credential fields --}}
                    @php $carrier = $this->selectedCarrier(); @endphp
                    @if ($carrier && !empty($carrier['credential_fields']))
                        <div wire:key="credential-fields-{{ $providerForm['carrier_id'] }}"
                             class="border-t border-surface-border pt-4">
                            <p class="text-sm font-medium text-ink mb-1">{{ __('merchant_panel.credential_values') }}</p>
                            <p class="text-xs text-ink-muted mb-3">{{ __('merchant_panel.provider_credentials_hint') }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach ($carrier['credential_fields'] as $field)
                                    <div>
                                        <label class="edz-label">
                                            {{ $field['label'] ?? $field['key'] }}
                                            @if (!empty($field['required'])) * @endif
                                        </label>
                                        <input type="{{ $field['type'] ?? 'text' }}"
                                            wire:model="providerForm.credential_values.{{ $field['key'] }}"
                                            class="edz-input text-sm" dir="ltr"
                                            @if (($field['type'] ?? 'text') !== 'password') autocomplete="off" @endif>
                                        @error("providerForm.credential_values.{$field['key']}")
                                            <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="border-t border-surface-border pt-4">
                            <p class="text-xs text-ink-muted">{{ __('merchant_panel.no_credentials_required') }}</p>
                        </div>
                    @endif

                    {{-- Options --}}
                    <div class="flex flex-wrap items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="providerForm.is_active" class="edz-checkbox" />
                            <span class="text-sm text-ink">{{ __('merchant_panel.provider_active') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="providerForm.is_default" class="edz-checkbox" />
                            <span class="text-sm text-ink">{{ __('merchant_panel.make_default') }}</span>
                        </label>
                    </div>

                    {{-- Footer --}}
                    <div class="flex justify-end gap-2 pt-2 border-t border-surface-border">
                        <button type="button" class="edz-btn edz-btn--ghost"
                                wire:click="set('showProviderModal', false)">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary" wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 pointer-events-none" wire:target="saveProvider">
                            <x-edz.spinner wire:target="saveProvider" />
                            <span wire:loading.remove wire:target="saveProvider">{{ __('buttons.save') }}</span>
                            <span class="sr-only">{{ __('buttons.save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-edz.modal>
    @endif
</div>