<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Locations\City;
use App\Models\Locations\State;
use Illuminate\Support\Facades\Log;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'providers' => [],
    'states' => [],
    'cities' => [],

    // Sidebar selection
    'selectedProviderId' => null,

    // Offices grouped by state for the selected provider,
    // keyed by state id or '__unassigned__' when a point has no wilaya yet.
    'pointsByState' => [],
    'stateRows' => [],

    // Carrier office sync
    'syncCandidates' => [],
    'selectedSyncProviderId' => '',
    'syncing' => false,

    // State offices popup
    'showOfficesPopup' => false,
    'popupStateId' => null,
    'popupStateName' => '',
    'popupOffices' => [],

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

    $this->states = State::active()
        ->orderedByCode()
        ->get(['id', 'name', 'state_code'])
        ->toArray();

    $this->loadData();
});

$providerHasIntegration = function (ShippingProvider $provider): bool {
    $code = $provider->carrier?->code;

    if (! $code) {
        return false;
    }

    $adapterClass = config(
        "delivery.carrier_integrations.{$code}",
        config('delivery.carrier_integrations.*'),
    );

    return $adapterClass && class_exists($adapterClass);
};

$loadData = function (): void {
    $storeId = currentStoreId();

    $this->providers = ShippingProvider::with('carrier')
        ->where('store_id', $storeId)
        ->withCount('stopdeskPoints')
        ->orderBy('name')
        ->get()
        ->map(fn (ShippingProvider $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'carrier' => $p->carrier?->name,
            'is_integrated' => $this->providerHasIntegration($p),
            'points_count' => $p->stopdesk_points_count,
        ])
        ->all();

    $this->syncCandidates = collect($this->providers)
        ->filter(fn ($p) => $p['is_integrated'])
        ->map(fn ($p) => ['id' => $p['id'], 'name' => $p['name']])
        ->values()
        ->all();

    if ($this->selectedSyncProviderId && ! collect($this->syncCandidates)->contains('id', $this->selectedSyncProviderId)) {
        $this->selectedSyncProviderId = '';
    }

    if ($this->selectedProviderId && ! collect($this->providers)->contains('id', $this->selectedProviderId)) {
        $this->selectedProviderId = null;
        $this->pointsByState = [];
        $this->stateRows = [];
    }

    if ($this->selectedProviderId) {
        $this->loadPoints($this->selectedProviderId);
    }
};

$loadPoints = function (string $providerId): void {
    $points = StopdeskPoint::where('store_id', currentStoreId())
        ->where('shipping_provider_id', $providerId)
        ->with('state', 'city')
        ->orderBy('name')
        ->get()
        ->map(fn (StopdeskPoint $point) => array_merge($point->toArray(), [
            'synced' => filled($point->external_code),
        ]));

    $this->pointsByState = $points
        ->groupBy(fn ($point) => $point['state_id'] ? (string) $point['state_id'] : '__unassigned__')
        ->map(fn ($group) => $group->values()->all())
        ->all();

    $this->stateRows = collect($this->pointsByState)
        ->map(function ($group, $stateKey) {
            $group = collect($group);

            return [
                'key' => $stateKey,
                'name' => $stateKey === '__unassigned__'
                    ? __('merchant_panel.stopdesk_unassigned')
                    : (collect($this->states)->firstWhere('id', $stateKey)['name'] ?? __('merchant_panel.stopdesk_unassigned')),
                'code' => $stateKey === '__unassigned__'
                    ? null
                    : (collect($this->states)->firstWhere('id', $stateKey)['state_code'] ?? null),
                'count' => $group->count(),
                'synced_count' => $group->where('synced')->count(),
            ];
        })
        ->values()
        ->sortBy('name')
        ->values()
        ->all();
};

$selectProvider = function (string $providerId): void {
    if (! collect($this->providers)->contains('id', $providerId)) {
        return;
    }

    $this->selectedProviderId = $providerId;

    if (collect($this->syncCandidates)->contains('id', $providerId)) {
        $this->selectedSyncProviderId = $providerId;
    }

    $this->loadPoints($providerId);
};

$syncStopdesk = function (): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    if (! $this->selectedSyncProviderId) {
        if ($this->selectedProviderId && collect($this->syncCandidates)->contains('id', $this->selectedProviderId)) {
            $this->selectedSyncProviderId = (string) $this->selectedProviderId;
        } else {
            return;
        }
    }

    $this->syncing = true;

    try {
        $provider = ShippingProvider::with('carrier')->where('store_id', currentStoreId())
            ->findOrFail($this->selectedSyncProviderId);

        if (! $provider->carrier || ! $this->providerHasIntegration($provider)) {
            $this->dispatch('swal', type: 'error', title: __('merchant_panel.sync_no_adapter'));
            return;
        }

        $result = app(\App\Domains\Shipping\Services\StopdeskOfficeSync::class)->sync($provider, null, null, true);

        $this->loadData();

        if ($result['synced'] && $result['total'] > 0) {
            $this->dispatch('swal', type: 'success', title: __('merchant_panel.stopdesk_sync_done', ['total' => $result['total']]));
        } else {
            $this->dispatch('swal', type: 'info', title: __('merchant_panel.stopdesk_sync_empty'));
        }
    } catch (\Throwable $e) {
        Log::warning('stopdesk sync failed: ' . $e->getMessage());
        $this->dispatch('swal', type: 'error', title: __('merchant_panel.stopdesk_sync_error'));
    } finally {
        $this->syncing = false;
    }
};

$watchState = function (string $stateId): void {
    $this->stopdeskForm['state_id'] = $stateId;
    $this->cities = $stateId
        ? City::where('state_id', $stateId)->orderBy('name')->get(['id', 'name'])->toArray()
        : [];
    $this->stopdeskForm['city_id'] = '';
};

$openOfficesPopup = function (string $stateKey): void {
    if (! $this->selectedProviderId) {
        return;
    }

    if ($stateKey === '__unassigned__') {
        $this->popupStateName = __('merchant_panel.stopdesk_unassigned');
    } else {
        $state = State::find($stateKey);
        if (! $state) {
            return;
        }
        $this->popupStateName = $state->name;
    }

    $this->popupStateId = $stateKey;
    $this->popupOffices = $this->pointsByState[$stateKey] ?? [];
    $this->showOfficesPopup = true;
};

$closeOfficesPopup = function (): void {
    $this->showOfficesPopup = false;
    $this->popupStateId = null;
    $this->popupStateName = '';
    $this->popupOffices = [];
};

$openStopdeskModal = function (?string $stopdeskId = null, ?string $defaultStateId = null): void {
    // The state-offices popup stays open: the office modal renders on top (it is
    // included later in the DOM with the same z-index) so the user keeps their
    // context and the popup list refreshes right after save/delete.
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
            'shipping_provider_id' => $this->selectedProviderId ?? '',
            'state_id' => $defaultStateId ?? '',
            'city_id' => '',
            'name' => '',
            'address' => '',
            'phone' => '',
            'is_active' => true,
        ];
        $this->cities = $defaultStateId
            ? City::where('state_id', $defaultStateId)->orderBy('name')->get(['id', 'name'])->toArray()
            : [];
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

    if ($this->showOfficesPopup && $this->popupStateId) {
        $this->popupOffices = $this->pointsByState[$this->popupStateId] ?? [];
    }

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.stopdesk_saved'));
};

$deleteStopdesk = function (string $id): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    StopdeskPoint::where('store_id', currentStoreId())->findOrFail($id)->delete();

    $this->loadData();

    if ($this->showOfficesPopup && $this->popupStateId) {
        $this->popupOffices = $this->pointsByState[$this->popupStateId] ?? [];
    }

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.stopdesk_deleted'));
};
?>

<div>
    <x-edz.page-header title="{{ __('merchant_panel.tab_stopdesk') }}"
        description="{{ __('merchant_panel.tab_stopdesk_desc') }}">
    </x-edz.page-header>

    @if (empty($providers))
        <div class="edz-card p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                <x-edz.icon name="map-pin" class="w-8 h-8 text-ink-muted opacity-40" />
            </div>
            <p class="text-ink-muted">{{ __('merchant_panel.no_providers_yet') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-5 items-start">

            {{-- Companies side list --}}
            @include('livewire.merchant.delivery.partials.stopdesk-provider-sidebar', [
                'providers' => $providers,
                'selectedProviderId' => $selectedProviderId,
            ])

            {{-- Main panel --}}
            <section>
                @if (! $selectedProviderId)
                    <div class="edz-card p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                            <x-edz.icon name="map-pin" class="w-8 h-8 text-ink-muted opacity-40" />
                        </div>
                        <p class="text-ink-muted">{{ __('merchant_panel.select_company_hint_offices') }}</p>
                    </div>
                @else
                    @php $currentProvider = collect($providers)->firstWhere('id', $selectedProviderId); @endphp

                    {{-- Company header: title + manual add + carrier sync --}}
                    <div class="edz-card edz-card--padded mb-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-11 h-11 rounded-xl bg-brand-surface flex items-center justify-center shrink-0">
                                    <x-edz.icon name="map-pin" class="w-5 h-5 text-brand-500" />
                                </div>
                                <div>
                                    <h2 class="font-semibold text-ink">{{ $currentProvider['name'] ?? '' }}</h2>
                                    <p class="text-sm text-ink-muted">{{ __('merchant_panel.stopdesk_provider_desc') }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                                    <button wire:click="openStopdeskModal"
                                        class="edz-btn edz-btn--primary edz-btn--sm">
                                        <x-edz.icon name="plus" class="w-4 h-4" />
                                        {{ __('merchant_panel.new_stopdesk') }}
                                    </button>
                                @endif
                                @if (($currentProvider['is_integrated'] ?? false))
                                    <button type="button" wire:click="syncStopdesk"
                                        class="edz-btn edz-btn--ghost edz-btn--sm"
                                        wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none"
                                        wire:target="syncStopdesk">
                                        <x-edz.spinner wire:target="syncStopdesk" />
                                        <span wire:loading.remove wire:target="syncStopdesk">
                                            <x-edz.icon name="arrow-path" class="w-4 h-4" />
                                        </span>
                                        <span>{{ $syncing ? __('merchant_panel.syncing_rates') : __('merchant_panel.stopdesk_sync_button') }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Offices grid grouped by state --}}
                    @include('livewire.merchant.delivery.partials.stopdesk-state-grid', [
                        'stateRows' => $stateRows,
                        'selectedProviderId' => $selectedProviderId,
                    ])
                @endif
            </section>
        </div>
    @endif

    {{-- ============ STATE OFFICES POPUP ============ --}}
    @if ($showOfficesPopup)
        @include('livewire.merchant.delivery.partials.stopdesk-offices-popup', [
            'popupStateName' => $popupStateName,
            'popupStateId' => $popupStateId,
            'popupOffices' => $popupOffices,
            'selectedProviderId' => $selectedProviderId,
        ])
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
                                wire:change="watchState($event.target.value)"
                                :options="collect($states)->map(fn ($s) => ['value' => $s['id'], 'label' => $s['name'], 'code' => $s['state_code'] ?? null])->all()"
                                option-code="code"
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