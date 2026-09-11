<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StorePermissionEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function Livewire\Volt\computed;
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

    // Connection test
    'testingConnection' => false,
    'connectionTestResult' => null,
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
            'code' => $p->code,
            'platform' => $p->carrierPlatform?->name,
            'carrier' => $p->carrier?->name,
            'carrier_id' => $p->carrier_id,
            'carrier_logo' => $p->carrier?->logo,
            'credentials_count' => count((array) ($p->credentials ?? [])),
            'rates_count' => $p->delivery_rates_count,
            'is_active' => $p->is_active,
            'is_default' => $p->is_default,
            'webhook_enabled' => (bool) $p->webhook_token,
            'webhook_token' => $p->webhook_token,
            'webhook_last_seen' => $p->webhook_last_seen_at?->toDateTimeString(),
        ])
        ->all();
};

$carrierOption = function (Carrier $c): array {
    return [
        'id' => $c->id,
        'name' => $c->name,
        'code' => $c->code,
        'logo' => $c->logo,
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

$hasIntegrationAdapter = computed(function (): bool {
    $carrier = $this->selectedCarrier();

    if (! $carrier || empty($carrier['code'])) {
        return false;
    }

    $adapterClass = config(
        "delivery.carrier_integrations.{$carrier['code']}",
        config('delivery.carrier_integrations.*'),
    );

    return $adapterClass && class_exists($adapterClass);
});

$selectProviderPlatform = function (string $platformId): void {
    $this->providerForm['platform_id'] = $platformId;
    $this->providerForm['carrier_id'] = '';
    $this->providerForm['name'] = '';
    $this->providerForm['credential_values'] = [];
    $this->connectionTestResult = null;

    // A company whose catalogue has a single branch needs no branch picker:
    // the only branch is picked implicitly. The standalone group keeps its
    // picker because every independent carrier is a distinct company choice.
    $carriers = $this->providerCarrierOptions();
    if ($platformId !== '__standalone__' && count($carriers) === 1) {
        $this->selectProviderCarrier((string) $carriers[0]['id']);
    }
};

$selectProviderCarrier = function (string $carrierId): void {
    $this->providerForm['carrier_id'] = $carrierId;
    $this->providerForm['credential_values'] = [];
    $this->connectionTestResult = null;

    $carrier = collect($this->providerCarrierOptions())->firstWhere('id', $carrierId);

    if ($carrier) {
        $this->providerForm['name'] = $carrier['name'];

        foreach ($carrier['credential_fields'] as $field) {
            $this->providerForm['credential_values'][$field['key']] = '';
        }
    }
};

$testProviderConnection = function (): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    $this->connectionTestResult = null;

    $carrier = $this->selectedCarrier();

    if (! $carrier || empty($carrier['code'])) {
        $this->connectionTestResult = ['ok' => false, 'message' => __('merchant_panel.connection_not_supported')];
        return;
    }

    $adapterClass = config(
        "delivery.carrier_integrations.{$carrier['code']}",
        config('delivery.carrier_integrations.*'),
    );

    if (! $adapterClass || ! class_exists($adapterClass)) {
        $this->connectionTestResult = ['ok' => false, 'message' => __('merchant_panel.connection_not_supported')];
        return;
    }

    $this->testingConnection = true;

    try {
        $adapter = app($adapterClass);

        // Throwaway provider built from the currently-typed, unsaved
        // credentials so the merchant can test before saving.
        $probe = new ShippingProvider(['credentials' => $this->providerForm['credential_values'] ?? []]);

        $this->connectionTestResult = $adapter->testConnection($probe);
    } catch (\Throwable $e) {
        $this->connectionTestResult = ['ok' => false, 'message' => $e->getMessage()];
    } finally {
        $this->testingConnection = false;
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

    $provider = null;

    DB::transaction(function () use ($storeId, $data, &$provider) {
        if ($data['is_default']) {
            ShippingProvider::where('store_id', $storeId)->update(['is_default' => false]);
        }

        if ($this->editingProviderId) {
            $provider = ShippingProvider::where('store_id', $storeId)->findOrFail($this->editingProviderId);
            $provider->update($data);
        } else {
            $provider = ShippingProvider::create(array_merge($data, ['store_id' => $storeId]));
        }
    });

    // Carrier-backed, active providers refresh their (cached) office list right
    // after save so newly supplied credentials take effect immediately.
    if ($provider && $provider->carrier_id && $data['is_active']) {
        \App\Domains\Shipping\Jobs\SyncStopdeskOfficesJob::dispatch($storeId, $provider->id);
    }

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

// ——— Delivery webhook — carrier push endpoint per provider. ———
$enableWebhook = function (string $id): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    $provider = ShippingProvider::where('store_id', currentStoreId())->findOrFail($id);

    abort_unless($provider->carrier_id, 403);

    if (! $provider->webhook_token) {
        $provider->update(['webhook_token' => (string) Str::uuid()]);
    }

    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('order_flow.webhook_ready'));
};

$regenerateWebhook = function (string $id): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    $provider = ShippingProvider::where('store_id', currentStoreId())->findOrFail($id);

    abort_unless($provider->carrier_id, 403);

    $provider->update([
        'webhook_token' => (string) Str::uuid(),
        'webhook_last_seen_at' => null,
    ]);

    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('order_flow.webhook_regenerated'));
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
                            <div class="w-11 h-11 rounded-xl bg-brand-surface flex items-center justify-center overflow-hidden">
                                @if ($provider['carrier_logo'])
                                    <img src="{{ asset('storage/' . $provider['carrier_logo']) }}" alt="{{ $provider['name'] }}"
                                        class="w-full h-full object-cover" onerror="this.style.display='none'">
                                @endif
                                <span @if ($provider['carrier_logo']) class="hidden" @endif>
                                    <x-edz.icon name="truck" class="w-5 h-5 text-brand-500" />
                                </span>
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

                    {{-- Delivery webhook — carrier push endpoint (carrier-backed providers only) --}}
                    @if ($provider['carrier_id'])
                        <div class="mt-4 pt-3 border-t border-surface-border space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-ink">{{ __('order_flow.webhook_endpoint') }}</span>
                                @if ($provider['webhook_enabled'])
                                    <x-edz.badge tone="success">{{ __('order_flow.webhook_ready') }}</x-edz.badge>
                                @else
                                    <x-edz.badge tone="neutral">{{ __('order_flow.webhook_not_configured') }}</x-edz.badge>
                                @endif
                            </div>
                            @if ($provider['webhook_enabled'])
                                @php
                                    $webhookCanonical = $provider['code']
                                        ? route('webhooks.delivery', ['provider' => $provider['code']])
                                        : null;
                                    $webhookFull = $provider['code']
                                        ? route('webhooks.delivery', ['provider' => $provider['code'], 'token' => $provider['webhook_token']])
                                        : route('webhooks.delivery', ['provider' => $provider['webhook_token']]);
                                @endphp
                                <div class="flex items-center gap-1" x-data="{ copied: false }">
                                    @if ($webhookCanonical)
                                        <code dir="ltr"
                                            class="flex-1 min-w-0 truncate text-[11px] text-ink-muted tabular-nums"
                                            data-webhook="{{ $webhookFull }}"
                                            title="{{ $webhookCanonical }}">{{ $webhookCanonical }}</code>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm shrink-0"
                                            :title="copied ? '{{ __('order_flow.webhook_copy') }} ✓' : '{{ __('order_flow.webhook_copy') }}'"
                                            x-on:click="navigator.clipboard.writeText($el.closest('.flex').querySelector('code').dataset.webhook); copied = true; setTimeout(() => copied = false, 1500)">
                                            <x-edz.icon name="clipboard" class="w-4 h-4" />
                                        </button>
                                        @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                                            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm shrink-0"
                                                title="{{ __('order_flow.webhook_token_regenerate') }}"
                                                wire:click="regenerateWebhook('{{ $provider['id'] }}')">
                                                <x-edz.icon name="arrow-path" class="w-4 h-4" />
                                            </button>
                                        @endif
                                    @else
                                        <code dir="ltr"
                                            class="flex-1 min-w-0 truncate text-[11px] text-ink-muted tabular-nums"
                                            data-webhook="{{ $webhookFull }}">{{ $webhookFull }}</code>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm shrink-0"
                                            :title="copied ? '{{ __('order_flow.webhook_copy') }} ✓' : '{{ __('order_flow.webhook_copy') }}'"
                                            x-on:click="navigator.clipboard.writeText($el.closest('.flex').querySelector('code').dataset.webhook); copied = true; setTimeout(() => copied = false, 1500)">
                                            <x-edz.icon name="clipboard" class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>
                                @if ($webhookCanonical)
                                    <div class="flex items-center gap-1" x-data="{ copied: false }">
                                        <span class="text-[11px] text-ink-muted shrink-0">{{ __('order_flow.webhook_token_label') }}:</span>
                                        <code dir="ltr"
                                            class="flex-1 min-w-0 truncate text-[11px] font-mono text-ink-muted tabular-nums"
                                            data-token="{{ $provider['webhook_token'] }}"
                                            title="{{ $provider['webhook_token'] }}">{{ $provider['webhook_token'] }}</code>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm shrink-0"
                                            :title="copied ? '{{ __('order_flow.webhook_token_copy') }} ✓' : '{{ __('order_flow.webhook_token_copy') }}'"
                                            x-on:click="navigator.clipboard.writeText($el.closest('.flex').querySelector('code').dataset.token); copied = true; setTimeout(() => copied = false, 1500)">
                                            <x-edz.icon name="shield-check" class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endif
                                <p class="text-[11px] leading-snug text-ink-muted">{{ __('order_flow.webhook_header_hint') }}</p>
                                <p class="text-[11px] text-ink-muted">
                                    {{ __('order_flow.webhook_last_seen') }}:
                                    <span class="tabular-nums">{{ $provider['webhook_last_seen'] ?? __('order_flow.webhook_never') }}</span>
                                </p>
                            @else
                                <p class="text-[11px] leading-snug text-ink-muted">{{ __('order_flow.webhook_hint') }}</p>
                                @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                                    <button type="button" wire:click="enableWebhook('{{ $provider['id'] }}')"
                                        class="edz-btn edz-btn--ghost edz-btn--sm">
                                        <x-edz.icon name="external-link" class="w-4 h-4" />
                                        {{ __('order_flow.webhook_enable') }}
                                    </button>
                                @endif
                            @endif
                        </div>
                    @endif

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
                            <label class="edz-label">{{ __('merchant_panel.delivery_company') }}</label>
                            <x-edz.select wire:model="providerForm.platform_id"
                                wire:change="selectProviderPlatform($event.target.value)"
                                :options="$this->providerPlatformOptions()"
                                placeholder="{{ __('merchant_panel.select_delivery_company') }}" size="sm" search />
                            @error('providerForm.platform_id')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        @php
                            $branchCarriers = $this->providerCarrierOptions();
                            $branchCarrierCount = count($branchCarriers);
                            $isStandaloneChoice = ($providerForm['platform_id'] ?? '') === '__standalone__';
                            $hasPlatformChoice = filled($providerForm['platform_id'] ?? '');
                            $isBranchedCompany = $hasPlatformChoice && ! $isStandaloneChoice;
                        @endphp
                        @if ($isStandaloneChoice)
                            <div>
                                <label class="edz-label">{{ __('merchant_panel.delivery_company') }}</label>
                                <x-edz.select wire:key="carrier-options-{{ $providerForm['platform_id'] ?: 'none' }}"
                                    wire:model="providerForm.carrier_id"
                                    wire:change="selectProviderCarrier($event.target.value)"
                                    :options="$branchCarriers" option-value="id" option-label="name"
                                    placeholder="{{ __('merchant_panel.select_delivery_company') }}" size="sm" search />
                                @error('providerForm.carrier_id')
                                    <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @elseif ($isBranchedCompany && $branchCarrierCount === 1)
                            <div>
                                <label class="edz-label">{{ __('merchant_panel.delivery_company_branch') }}</label>
                                <input type="text" value="{{ $branchCarriers[0]['name'] }}"
                                    class="edz-input text-sm bg-surface-secondary opacity-70" disabled>
                                <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.delivery_company_single_branch') }}</p>
                                <input type="hidden" wire:model="providerForm.carrier_id" />
                            </div>
                        @elseif ($isBranchedCompany && $branchCarrierCount === 0)
                            <div>
                                <label class="edz-label">{{ __('merchant_panel.delivery_company_branch') }}</label>
                                <div class="edz-input text-sm text-ink-muted bg-surface-secondary opacity-70">{{ __('merchant_panel.delivery_company_no_branches') }}</div>
                            </div>
                        @else
                            <div>
                                <label class="edz-label">{{ __('merchant_panel.delivery_company_branch') }}</label>
                                <x-edz.select wire:key="carrier-options-{{ $providerForm['platform_id'] ?: 'none' }}"
                                    wire:model="providerForm.carrier_id"
                                    wire:change="selectProviderCarrier($event.target.value)"
                                    :options="$branchCarriers" option-value="id" option-label="name"
                                    placeholder="{{ $isBranchedCompany ? __('merchant_panel.select_delivery_company_branch') : __('merchant_panel.select_delivery_company_first') }}"
                                    size="sm" search />
                                @error('providerForm.carrier_id')
                                    <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
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
                            @if ($this->hasIntegrationAdapter)
                                <div class="mt-3">
                                    <button type="button" wire:click="testProviderConnection"
                                        class="edz-btn edz-btn--ghost edz-btn--sm" wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50 pointer-events-none" wire:target="testProviderConnection">
                                        <x-edz.spinner wire:target="testProviderConnection" />
                                        <span wire:loading.remove wire:target="testProviderConnection">
                                            <x-edz.icon name="shield-check" class="w-4 h-4" />
                                        </span>
                                        <span>{{ $testingConnection ? __('merchant_panel.testing_connection') : __('merchant_panel.test_connection') }}</span>
                                    </button>
                                    @if ($connectionTestResult)
                                        <div class="mt-2">
                                            <x-edz.badge :tone="$connectionTestResult['ok'] ? 'success' : 'danger'">
                                                {{ $connectionTestResult['message'] }}
                                            </x-edz.badge>
                                        </div>
                                    @endif
                                </div>
                            @endif
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
