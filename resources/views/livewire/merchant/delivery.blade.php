<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Locations\City;
use App\Models\Locations\State;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    // Tabs
    'tab' => 'providers',

    // Providers tab
    'providers' => [],

    // Rates tab
    'rates' => [],

    // Stopdesk tab
    'stopdeskPoints' => [],

    // Shared data
    'states' => [],
    'cities' => [],

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

    // Rate modal
    'showRateModal' => false,
    'editingRateId' => null,
    'rateForm' => [
        'shipping_provider_id' => '',
        'state_id' => '',
        'label' => '',
        'office_cost' => '',
        'home_cost' => '',
        'free_above' => '',
        'is_active' => true,
    ],

    // Stopdesk modal
    'showStopdeskModal' => false,
    'editingStopdeskId' => null,
    'stopdeskForm' => [
        'shipping_provider_id' => '',
        'state_id' => '',
        'city_id' => '',
        'name' => '',
        'address' => '',
        'phone' => '',
        'is_active' => true,
    ],
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value) ||
        canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->states = State::orderBy('name')->get(['id', 'name'])->toArray();
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

    $this->rates = DeliveryRate::where('store_id', $storeId)
        ->with('provider', 'state')
        ->orderBy('state_id')
        ->get()
        ->toArray();

    $this->stopdeskPoints = StopdeskPoint::where('store_id', $storeId)
        ->with('provider', 'state', 'city')
        ->orderBy('name')
        ->get()
        ->toArray();
};

$carrierOption = function (Carrier $c): array {
    return [
        'id' => $c->id,
        'name' => $c->name,
        'code' => $c->code,
        'credential_fields' => $c->credentialFieldList(),
    ];
};

$setTab = function (string $tab): void {
    $this->tab = $tab;
};

// ——— Stopdesk cities watcher ———
$watchState = function (string $context, ?string $stateId = null): void {
    $context === 'stopdesk'
        ? $this->stopdeskForm['state_id'] = $stateId
        : $this->rateForm['state_id'] = $stateId;

    $this->cities = $stateId
        ? City::where('state_id', $stateId)->orderBy('name')->get(['id', 'name'])->toArray()
        : [];
    $this->stopdeskForm['city_id'] = '';
};

// ===========================
// Providers (connected carriers)
// ===========================

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

// ===========================
// Delivery rates (per provider -> per state: office/home)
// ===========================

$openRateModal = function (?string $rateId = null): void {
    if ($rateId) {
        $rate = DeliveryRate::where('store_id', currentStoreId())->findOrFail($rateId);
        $this->editingRateId = $rate->id;
        $this->rateForm = [
            'shipping_provider_id' => $rate->shipping_provider_id ?? '',
            'state_id' => $rate->state_id ?? '',
            'label' => $rate->label ?? '',
            'office_cost' => $rate->office_cost !== null ? (string) $rate->office_cost : '',
            'home_cost' => $rate->home_cost !== null ? (string) $rate->home_cost : '',
            'free_above' => $rate->free_above !== null ? (string) $rate->free_above : '',
            'is_active' => $rate->is_active,
        ];
    } else {
        $this->editingRateId = null;
        $this->rateForm = [
            'shipping_provider_id' => '',
            'state_id' => '',
            'label' => '',
            'office_cost' => '',
            'home_cost' => '',
            'free_above' => '',
            'is_active' => true,
        ];
    }
    $this->showRateModal = true;
};

$saveRate = function (): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    $this->validate([
        'rateForm.shipping_provider_id' => 'required',
        'rateForm.state_id' => 'required',
        'rateForm.office_cost' => 'nullable|numeric|min:0',
        'rateForm.home_cost' => 'nullable|numeric|min:0',
        'rateForm.free_above' => 'nullable|numeric|min:0',
    ]);

    if (($this->rateForm['office_cost'] ?? '') === '' && ($this->rateForm['home_cost'] ?? '') === '') {
        $this->addError('rateForm.office_cost', __('merchant_panel.need_office_or_home'));
        $this->addError('rateForm.home_cost', __('merchant_panel.need_office_or_home'));
        return;
    }

    $data = [
        'shipping_provider_id' => $this->rateForm['shipping_provider_id'],
        'state_id' => $this->rateForm['state_id'],
        'label' => $this->rateForm['label'] ?: null,
        'office_cost' => ($this->rateForm['office_cost'] ?? '') !== '' ? $this->rateForm['office_cost'] : null,
        'home_cost' => ($this->rateForm['home_cost'] ?? '') !== '' ? $this->rateForm['home_cost'] : null,
        'free_above' => ($this->rateForm['free_above'] ?? '') !== '' ? (int) $this->rateForm['free_above'] : null,
        'is_active' => $this->rateForm['is_active'],
    ];

    $storeId = currentStoreId();

    if ($this->editingRateId) {
        DeliveryRate::where('store_id', $storeId)->findOrFail($this->editingRateId)->update($data);
    } else {
        DeliveryRate::updateOrCreate([
            'store_id' => $storeId,
            'shipping_provider_id' => $data['shipping_provider_id'],
            'state_id' => $data['state_id'],
        ], $data);
    }

    $this->showRateModal = false;
    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.rate_saved'));
};

$deleteRate = function (string $id): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    DeliveryRate::where('store_id', currentStoreId())->findOrFail($id)->delete();
    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.rate_deleted'));
};

// ===========================
// Stopdesk CRUD
// ===========================

$openStopdeskModal = function (?string $stopdeskId = null): void {
    if ($stopdeskId) {
        $point = StopdeskPoint::where('store_id', currentStoreId())->findOrFail($stopdeskId);
        $this->editingStopdeskId = $point->id;
        $this->stopdeskForm = [
            'shipping_provider_id' => $point->shipping_provider_id ?? '',
            'state_id' => $point->state_id ?? '',
            'city_id' => $point->city_id ?? '',
            'name' => $point->name,
            'address' => $point->address ?? '',
            'phone' => $point->phone ?? '',
            'is_active' => $point->is_active,
        ];
        $this->cities = $point->state_id
            ? City::where('state_id', $point->state_id)->orderBy('name')->get(['id', 'name'])->toArray()
            : [];
    } else {
        $this->editingStopdeskId = null;
        $this->stopdeskForm = [
            'shipping_provider_id' => '',
            'state_id' => '',
            'city_id' => '',
            'name' => '',
            'address' => '',
            'phone' => '',
            'is_active' => true,
        ];
        $this->cities = [];
    }
    $this->showStopdeskModal = true;
};

$saveStopdesk = function (): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    $this->validate([
        'stopdeskForm.name' => 'required|string|max:255',
        'stopdeskForm.shipping_provider_id' => 'required',
        'stopdeskForm.state_id' => 'required',
        'stopdeskForm.phone' => 'nullable|string|max:20',
    ]);

    $data = [
        'shipping_provider_id' => $this->stopdeskForm['shipping_provider_id'],
        'state_id' => $this->stopdeskForm['state_id'],
        'city_id' => $this->stopdeskForm['city_id'] ?: null,
        'name' => $this->stopdeskForm['name'],
        'address' => $this->stopdeskForm['address'] ?: null,
        'phone' => $this->stopdeskForm['phone'] ?: null,
        'is_active' => $this->stopdeskForm['is_active'],
    ];

    $storeId = currentStoreId();

    if ($this->editingStopdeskId) {
        StopdeskPoint::where('store_id', $storeId)->findOrFail($this->editingStopdeskId)->update($data);
    } else {
        StopdeskPoint::create(array_merge($data, ['store_id' => $storeId]));
    }

    $this->showStopdeskModal = false;
    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.stopdesk_saved'));
};

$deleteStopdesk = function (string $id): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    StopdeskPoint::where('store_id', currentStoreId())->findOrFail($id)->delete();
    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.stopdesk_deleted'));
};
?>

<div>
    <x-edz.page-header title="{{ __('merchant_panel.delivery_settings') }}"
        description="{{ __('merchant_panel.delivery_settings_desc') }}">
    </x-edz.page-header>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 border-b border-surface-border overflow-x-auto">
        <button wire:click="setTab('providers')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px whitespace-nowrap {{ $tab === 'providers' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-ink-muted hover:text-ink' }}">
            <x-edz.icon name="truck" class="w-4 h-4" />
            {{ __('merchant_panel.tab_providers') }}
        </button>
        <button wire:click="setTab('rates')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px whitespace-nowrap {{ $tab === 'rates' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-ink-muted hover:text-ink' }}">
            <x-edz.icon name="banknotes" class="w-4 h-4" />
            {{ __('merchant_panel.tab_rates') }}
        </button>
        <button wire:click="setTab('stopdesk')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px whitespace-nowrap {{ $tab === 'stopdesk' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-ink-muted hover:text-ink' }}">
            <x-edz.icon name="map-pin" class="w-4 h-4" />
            {{ __('merchant_panel.tab_stopdesk') }}
        </button>
    </div>

    {{-- ============ PROVIDERS TAB ============ --}}
    @if ($tab === 'providers')
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
                                <div class="w-11 h-11 rounded-xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center">
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
                            <div class="flex items-center justify-end gap-1 mt-4 pt-3 border-t border-neutral-border dark:border-dark-border">
                                <button type="button" aria-label="{{ __('merchant_panel.edit_provider') }}"
                                        wire:click="openProviderModal('{{ $provider['id'] }}')"
                                        class="edz-btn edz-btn--ghost edz-btn--sm">
                                    <x-edz.icon name="edit" class="w-4 h-4" />
                                </button>
                                <button type="button" aria-label="{{ __('merchant_panel.confirm_delete_provider') }}"
                                        class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                        x-data
                                        x-on:click="EdzSwal.confirmDelete(() => { $wire.deleteProvider('{{ $provider['id'] }}') })">
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
    @endif

    {{-- ============ RATES TAB ============ --}}
    @if ($tab === 'rates')
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-ink-muted">{{ __('merchant_panel.tab_rates_desc') }}</p>
            @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                <button wire:click="openRateModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('merchant_panel.new_rate') }}
                </button>
            @endif
        </div>

        @if (!empty($rates))
            <div class="edz-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="edz-table">
                        <thead>
                            <tr>
                                <th>{{ __('merchant_panel.shipping_provider') }}</th>
                                <th>{{ __('merchant_panel.state') }}</th>
                                <th>{{ __('merchant_panel.office_cost') }}</th>
                                <th>{{ __('merchant_panel.home_cost') }}</th>
                                <th>{{ __('merchant_panel.free_above') }}</th>
                                <th>{{ __('merchant_panel.status') }}</th>
                                @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                                    <th class="text-end">{{ __('merchant_panel.actions') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rates as $rate)
                                <tr wire:key="delivery-rate-{{ $rate['id'] }}">
                                    <td class="font-medium text-ink">{{ $rate['provider']['name'] ?? '—' }}</td>
                                    <td>{{ $rate['state']['name'] ?? '—' }}</td>
                                    <td class="font-mono">{{ $rate['office_cost'] !== null ? number_format((float) $rate['office_cost'], 2) . ' DA' : '—' }}</td>
                                    <td class="font-mono">{{ $rate['home_cost'] !== null ? number_format((float) $rate['home_cost'], 2) . ' DA' : '—' }}</td>
                                    <td class="font-mono">{{ $rate['free_above'] ? number_format($rate['free_above']) : '—' }}</td>
                                    <td>
                                        <span class="{{ $rate['is_active'] ? 'edz-badge edz-badge--success' : 'edz-badge edz-badge--neutral' }}">
                                            {{ $rate['is_active'] ? __('merchant_panel.provider_active') : __('merchant_panel.provider_inactive') }}
                                        </span>
                                    </td>
                                    @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                                        <td class="text-end">
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" aria-label="{{ __('merchant_panel.edit_rate') }}"
                                                        wire:click="openRateModal('{{ $rate['id'] }}')"
                                                        class="edz-btn edz-btn--ghost edz-btn--sm">
                                                    <x-edz.icon name="edit" class="w-4 h-4" />
                                                </button>
                                                <button type="button" aria-label="{{ __('merchant_panel.confirm_delete_rate') }}"
                                                        class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                                        x-data
                                                        x-on:click="EdzSwal.confirmDelete(() => { $wire.deleteRate('{{ $rate['id'] }}') })">
                                                    <x-edz.icon name="trash" class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="edz-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                    <x-edz.icon name="banknotes" class="w-8 h-8 text-ink-muted opacity-40" />
                </div>
                <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_rates_yet') }}</p>
            </div>
        @endif
    @endif

    {{-- ============ STOPDESK TAB ============ --}}
    @if ($tab === 'stopdesk')
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-ink-muted">{{ __('merchant_panel.tab_stopdesk_desc') }}</p>
            @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                <button wire:click="openStopdeskModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('merchant_panel.new_stopdesk') }}
                </button>
            @endif
        </div>

        @if (!empty($stopdeskPoints))
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($stopdeskPoints as $point)
                    <div wire:key="stopdesk-{{ $point['id'] }}" class="edz-card edz-card--padded">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-info-50 dark:bg-info-900/20 flex items-center justify-center">
                                    <x-edz.icon name="map-pin" class="w-5 h-5 text-info-500" />
                                </div>
                                <div>
                                    <p class="font-semibold text-ink">{{ $point['name'] }}</p>
                                    <p class="text-xs text-ink-muted">{{ $point['provider']['name'] ?? '—' }}</p>
                                </div>
                            </div>
                            <span class="{{ $point['is_active'] ? 'edz-badge edz-badge--success' : 'edz-badge edz-badge--neutral' }}">
                                {{ $point['is_active'] ? __('merchant_panel.stopdesk_active') : __('merchant_panel.stopdesk_inactive') }}
                            </span>
                        </div>

                        <div class="space-y-1.5 text-sm text-ink-muted">
                            @if ($point['state'] || $point['city'])
                                <p class="flex items-center gap-1.5">
                                    <x-edz.icon name="map-pin" class="w-3.5 h-3.5" />
                                    {{ $point['state']['name'] ?? '' }}{{ $point['city'] ? ' — ' . $point['city']['name'] : '' }}
                                </p>
                            @endif
                            @if ($point['address'])
                                <p class="flex items-center gap-1.5">
                                    <x-edz.icon name="home" class="w-3.5 h-3.5" />
                                    {{ $point['address'] }}
                                </p>
                            @endif
                            @if ($point['phone'])
                                <p class="flex items-center gap-1.5" dir="ltr">
                                    <x-edz.icon name="phone" class="w-3.5 h-3.5" />
                                    <span class="text-start">{{ $point['phone'] }}</span>
                                </p>
                            @endif
                        </div>

                        @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                            <div class="flex items-center justify-end gap-1 mt-4 pt-3 border-t border-neutral-border dark:border-dark-border">
                                <button type="button" aria-label="{{ __('merchant_panel.edit_stopdesk') }}"
                                        wire:click="openStopdeskModal('{{ $point['id'] }}')"
                                        class="edz-btn edz-btn--ghost edz-btn--sm">
                                    <x-edz.icon name="edit" class="w-4 h-4" />
                                </button>
                                <button type="button" aria-label="{{ __('merchant_panel.confirm_delete_stopdesk') }}"
                                        class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                        x-data
                                        x-on:click="EdzSwal.confirmDelete(() => { $wire.deleteStopdesk('{{ $point['id'] }}') })">
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
                    <x-edz.icon name="map-pin" class="w-8 h-8 text-ink-muted opacity-40" />
                </div>
                <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_stopdesk_yet') }}</p>
            </div>
        @endif
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
                             class="border-t border-surface-border dark:border-ink-700 pt-4">
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
                        <div class="border-t border-surface-border dark:border-ink-700 pt-4">
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

    {{-- ============ RATE MODAL ============ --}}
    @if ($showRateModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="lg"
            wire:key="rate-modal-{{ $editingRateId ?? 'new' }}">
            <form wire:submit="saveRate">
                <div class="p-6 space-y-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">
                            {{ $editingRateId ? __('merchant_panel.edit_rate') : __('merchant_panel.new_rate') }}
                        </h3>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="set('showRateModal', false)">
                            <x-edz.icon name="x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.shipping_provider') }} *</label>
                            <x-edz.select wire:model="rateForm.shipping_provider_id"
                                :options="collect($providers)->map(fn ($p) => ['value' => $p['id'], 'label' => $p['name']])->all()"
                                placeholder="—" size="sm" search />
                            @error('rateForm.shipping_provider_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.state') }} *</label>
                            <x-edz.select wire:model="rateForm.state_id"
                                :options="collect($states)->map(fn ($s) => ['value' => $s['id'], 'label' => $s['name']])->all()"
                                placeholder="—" size="sm" search />
                            @error('rateForm.state_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.office_cost') }} (DA)</label>
                            <input type="number" step="0.01" min="0" wire:model="rateForm.office_cost" class="edz-input text-sm">
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.office_cost_hint') }}</p>
                            @error('rateForm.office_cost')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.home_cost') }} (DA)</label>
                            <input type="number" step="0.01" min="0" wire:model="rateForm.home_cost" class="edz-input text-sm">
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.home_cost_hint') }}</p>
                            @error('rateForm.home_cost')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.free_above') }}</label>
                            <input type="number" step="0.01" min="0" wire:model="rateForm.free_above" class="edz-input text-sm">
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.free_above_hint') }}</p>
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.rate_label') }}</label>
                            <input type="text" wire:model="rateForm.label" class="edz-input text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="rateForm.is_active" class="edz-checkbox" />
                                <span class="text-sm text-ink">{{ __('merchant_panel.provider_active') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex justify-end gap-2 pt-2 border-t border-surface-border">
                        <button type="button" class="edz-btn edz-btn--ghost"
                                wire:click="set('showRateModal', false)">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary" wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 pointer-events-none" wire:target="saveRate">
                            <x-edz.spinner wire:target="saveRate" />
                            <span wire:loading.remove wire:target="saveRate">{{ __('buttons.save') }}</span>
                            <span class="sr-only">{{ __('buttons.save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-edz.modal>
    @endif

    {{-- ============ STOPDESK MODAL ============ --}}
    @if ($showStopdeskModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="lg"
            wire:key="stopdesk-modal-{{ $editingStopdeskId ?? 'new' }}">
            <form wire:submit="saveStopdesk">
                <div class="p-6 space-y-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">
                            {{ $editingStopdeskId ? __('merchant_panel.edit_stopdesk') : __('merchant_panel.new_stopdesk') }}
                        </h3>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="set('showStopdeskModal', false)">
                            <x-edz.icon name="x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.stopdesk_name') }} *</label>
                            <input type="text" wire:model="stopdeskForm.name" class="edz-input text-sm" required>
                            @error('stopdeskForm.name')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.shipping_provider') }} *</label>
                            <x-edz.select wire:model="stopdeskForm.shipping_provider_id"
                                :options="collect($providers)->map(fn ($p) => ['value' => $p['id'], 'label' => $p['name']])->all()"
                                placeholder="—" size="sm" search />
                            @error('stopdeskForm.shipping_provider_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.state') }} *</label>
                            <x-edz.select wire:model="stopdeskForm.state_id"
                                wire:change="watchState('stopdesk', $event.target.value)"
                                :options="collect($states)->map(fn ($s) => ['value' => $s['id'], 'label' => $s['name']])->all()"
                                placeholder="—" size="sm" search />
                            @error('stopdeskForm.state_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.city') }}</label>
                            <select wire:model="stopdeskForm.city_id" class="edz-input text-sm">
                                <option value="">{{ __('merchant_panel.apply_state_wide') }}</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.stopdesk_address') }}</label>
                            <input type="text" wire:model="stopdeskForm.address" class="edz-input text-sm">
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.stopdesk_phone') }}</label>
                            <input type="tel" wire:model="stopdeskForm.phone" class="edz-input text-sm" dir="ltr">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="stopdeskForm.is_active" class="edz-checkbox" />
                                <span class="text-sm text-ink">{{ __('merchant_panel.stopdesk_active') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex justify-end gap-2 pt-2 border-t border-surface-border">
                        <button type="button" class="edz-btn edz-btn--ghost"
                                wire:click="set('showStopdeskModal', false)">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary" wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 pointer-events-none" wire:target="saveStopdesk">
                            <x-edz.spinner wire:target="saveStopdesk" />
                            <span wire:loading.remove wire:target="saveStopdesk">{{ __('buttons.save') }}</span>
                            <span class="sr-only">{{ __('buttons.save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-edz.modal>
    @endif
</div>