<?php

use App\Domains\Shipping\Contracts\DeliveryRatesManager;
use App\Domains\Shipping\Models\DeliveryPriceList;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\DeliveryRateCity;
use App\Domains\Shipping\Models\DeliveryRateListCity;
use App\Domains\Shipping\Models\DeliveryRateListState;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Products\Product;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    // Providers (companies) side list
    'providers' => [],

    // All states (id, name) — loaded once
    'states' => [],

    // Selected provider / syncing flag
    'selectedProviderId' => null,
    'syncing' => false,

    // Rates keyed by state_id for the selected provider
    'ratesByState' => [],

    // Municipality popup (per state)
    'showStatePopup' => false,
    'popupStateId' => null,
    'popupStateName' => '',
    'popupCities' => [],
    'popupCitiesWithPrices' => [],
    'popupCenters' => [],
    'applyAllHomeCost' => '',
    'stateOfficeCost' => '',
    'stateDefaultCenterId' => '',

    // Active sub-tab: 'company' | 'lists'
    'tab' => 'company',

    // Price lists (store-wide, not carrier-bound)
    'lists' => [],
    'listsLoaded' => false,
    'selectedListId' => null,

    // List rates keyed by state_id for the selected list
    'listRatesByState' => [],

    // List modal (create/edit + product picker)
    'showListModal' => false,
    'editingListId' => null,
    'listName' => '',
    'listSelectedProductIds' => [],
    'listSelectedProducts' => [],
    'listProductSearch' => '',

    // List-state municipality popup (no default center: store-wide list)
    'showListStatePopup' => false,
    'listPopupStateId' => null,
    'listPopupStateName' => '',
    'listPopupCitiesWithPrices' => [],
    'listApplyAllHomeCost' => '',
    'listStateOfficeCost' => '',
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value) ||
        canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->states = State::active()
        ->orderBy('sort_order')->orderBy('name')
        ->get(['id', 'name'])
        ->toArray();

    $this->loadData();
    $this->loadLists();
});

$loadData = function (): void {
    $storeId = currentStoreId();

    $this->providers = ShippingProvider::where('store_id', $storeId)
        ->withCount('deliveryRates')
        ->orderBy('name')
        ->get()
        ->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'carrier' => $p->carrier?->name,
            'is_default' => $p->is_default,
            'is_active' => $p->is_active,
            'rates_count' => $p->delivery_rates_count,
        ])
        ->all();

    if ($this->selectedProviderId) {
        $this->loadRates($this->selectedProviderId);
    }
};

$loadRates = function (string $providerId): void {
    $storeId = currentStoreId();

    $this->ratesByState = DeliveryRate::where('store_id', $storeId)
        ->where('shipping_provider_id', $providerId)
        ->get()
        ->mapWithKeys(fn (DeliveryRate $r) => [
            $r->state_id => [
                'home_cost' => $r->home_cost !== null ? (string) $r->home_cost : '',
                'office_cost' => $r->office_cost !== null ? (string) $r->office_cost : '',
                'source' => $r->source ?? 'manual',
            ],
        ])
        ->all();
};

$selectProvider = function (string $providerId): void {
    $this->selectedProviderId = $providerId;
    $this->loadRates($providerId);
};

$updateStateCost = function (string $stateId, string $field, ?string $value): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    if (! $this->selectedProviderId) {
        return;
    }

    $provider = ShippingProvider::where('store_id', currentStoreId())
        ->findOrFail($this->selectedProviderId);

    $rate = DeliveryRate::firstOrNew([
        'store_id'             => currentStoreId(),
        'shipping_provider_id' => $provider->id,
        'state_id'             => $stateId,
    ]);

    $rate->{$field} = $value === '' ? null : $value;
    $rate->source = 'manual';
    $rate->save();

    $this->ratesByState[$stateId] = array_merge(
        $this->ratesByState[$stateId] ?? ['home_cost' => '', 'office_cost' => '', 'source' => 'manual'],
        [$field => $value]
    );
};

$syncProvider = function (DeliveryRatesManager $manager): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    if (! $this->selectedProviderId) {
        return;
    }

    $this->syncing = true;

    try {
        $provider = ShippingProvider::with('carrier')->where('store_id', currentStoreId())
            ->findOrFail($this->selectedProviderId);

        $states = $this->states;
        $result = $manager->syncProvider($provider, collect($states)->map(fn ($s) => State::find($s['id'])));

        $this->loadRates($this->selectedProviderId);
        $this->loadData();

        if ($result['count'] > 0) {
            $this->dispatch('swal', type: 'success', title: __('merchant_panel.sync_success', ['count' => $result['count']]));
        } else {
            $this->dispatch('swal', type: 'info', title: __('merchant_panel.sync_no_rates'));
        }
    } finally {
        $this->syncing = false;
    }
};

// ——— Municipality (state) popup ———

$openStatePopup = function (string $stateId): void {
    if (! $this->selectedProviderId) {
        return;
    }

    $state = State::find($stateId);
    if (! $state) {
        return;
    }

    $this->popupStateId = $stateId;
    $this->popupStateName = $state->name;
    $this->stateOfficeCost = $this->ratesByState[$stateId]['office_cost'] ?? '';

    $priceLookup = DeliveryRateCity::where('store_id', currentStoreId())
        ->where('shipping_provider_id', $this->selectedProviderId)
        ->where('state_id', $stateId)
        ->get()
        ->keyBy('city_id');

    $this->popupCitiesWithPrices = City::where('state_id', $stateId)
        ->active()
        ->orderBy('name')
        ->get(['id', 'name'])
        ->map(function ($city) use ($priceLookup) {
            $row = $priceLookup->get($city->id);

            return [
                'id' => $city->id,
                'name' => $city->name,
                'home_cost' => $row && $row->home_cost !== null ? (string) $row->home_cost : '',
            ];
        })
        ->all();

    $this->stateDefaultCenterId = DeliveryRate::where('store_id', currentStoreId())
        ->where('shipping_provider_id', $this->selectedProviderId)
        ->where('state_id', $stateId)
        ->value('default_center_id') ?? '';

    $this->applyAllHomeCost = '';

    // Candidate default centers: stopdesk points for this provider + state (or state-wide).
    $this->popupCenters = StopdeskPoint::where('store_id', currentStoreId())
        ->where('shipping_provider_id', $this->selectedProviderId)
        ->where('state_id', $stateId)
        ->orderBy('name')
        ->get(['id', 'name', 'city_id'])
        ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])
        ->all();

    $this->showStatePopup = true;
};

$saveMunicipalityCost = function (string $stateId, string $cityId, ?string $value): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    if (! $this->selectedProviderId || ! $stateId || ! $cityId) {
        return;
    }

    $storeId = currentStoreId();

    if ($value === '' || $value === null) {
        DeliveryRateCity::where('store_id', $storeId)
            ->where('shipping_provider_id', $this->selectedProviderId)
            ->where('state_id', $stateId)
            ->where('city_id', $cityId)
            ->delete();
    } else {
        DeliveryRateCity::updateOrCreate(
            [
                'store_id'             => $storeId,
                'shipping_provider_id' => $this->selectedProviderId,
                'state_id'             => $stateId,
                'city_id'              => $cityId,
            ],
            ['home_cost' => $value, 'is_active' => true]
        );
    }

    foreach ($this->popupCitiesWithPrices as $i => $city) {
        if ($city['id'] === $cityId) {
            $this->popupCitiesWithPrices[$i]['home_cost'] = $value;
            break;
        }
    }
};

$applyAllHomeCost = function (string $stateId): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    if (! $this->selectedProviderId || ! $stateId || $this->applyAllHomeCost === '') {
        return;
    }

    $storeId = currentStoreId();
    $value = $this->applyAllHomeCost;

    DB::transaction(function () use ($storeId, $stateId, $value) {
        $cities = City::where('state_id', $stateId)->active()->pluck('id');

        foreach ($cities as $cityId) {
            DeliveryRateCity::updateOrCreate(
                [
                    'store_id'             => $storeId,
                    'shipping_provider_id' => $this->selectedProviderId,
                    'state_id'             => $stateId,
                    'city_id'              => $cityId,
                ],
                ['home_cost' => $value, 'is_active' => true]
            );
        }
    });

    foreach ($this->popupCitiesWithPrices as $i => $city) {
        $this->popupCitiesWithPrices[$i]['home_cost'] = $value;
    }

    $this->applyAllHomeCost = '';
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.apply_all_done'));
};

$saveStateOffice = function (string $stateId, ?string $officeCost): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    if (! $this->selectedProviderId || ! $stateId) {
        return;
    }

    $rate = DeliveryRate::firstOrNew([
        'store_id'             => currentStoreId(),
        'shipping_provider_id' => $this->selectedProviderId,
        'state_id'             => $stateId,
    ]);
    $rate->office_cost = $officeCost === '' ? null : $officeCost;
    $rate->save();

    $this->ratesByState[$stateId]['office_cost'] = $officeCost;
};

$saveDefaultCenter = function (string $stateId, string $centerId): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    if (! $this->selectedProviderId || ! $stateId) {
        return;
    }

    $rate = DeliveryRate::firstOrNew([
        'store_id'             => currentStoreId(),
        'shipping_provider_id' => $this->selectedProviderId,
        'state_id'             => $stateId,
    ]);
    $rate->default_center_id = $centerId ?: null;
    $rate->save();

    $this->stateDefaultCenterId = $centerId;
};

$closeStatePopup = function (): void {
    $this->showStatePopup = false;
    $this->popupStateId = null;
    $this->popupCitiesWithPrices = [];
    $this->popupCenters = [];
};

// ——— Price lists tab (store-wide lists, not carrier-bound) ———

$loadLists = function (): void {
    $storeId = currentStoreId();

    $this->lists = DeliveryPriceList::withCount('products')
        ->where('store_id', $storeId)
        ->orderBy('name')
        ->get()
        ->map(fn (DeliveryPriceList $l) => [
            'id' => $l->id,
            'name' => $l->name,
            'is_active' => $l->is_active,
            'products_count' => $l->products_count,
        ])
        ->all();

    $this->listsLoaded = true;

    if ($this->selectedListId && ! collect($this->lists)->contains('id', $this->selectedListId)) {
        $this->selectedListId = null;
        $this->listRatesByState = [];
    }

    if (! $this->selectedListId && ! empty($this->lists)) {
        $this->selectedListId = $this->lists[0]['id'];
        $this->loadListRates($this->selectedListId);
    }
};

$setTab = function (string $tab): void {
    if (! in_array($tab, ['company', 'lists'], true)) {
        return;
    }
    $this->tab = $tab;
    if ($tab === 'lists' && ! $this->listsLoaded) {
        $this->loadLists();
    }
};

$selectList = function (string $listId): void {
    $this->selectedListId = $listId;
    $this->loadListRates($listId);
};

$loadListRates = function (string $listId): void {
    $this->listRatesByState = DeliveryRateListState::where('delivery_price_list_id', $listId)
        ->get()
        ->mapWithKeys(fn (DeliveryRateListState $r) => [
            $r->state_id => [
                'home_cost' => $r->home_cost !== null ? (string) $r->home_cost : '',
                'office_cost' => $r->office_cost !== null ? (string) $r->office_cost : '',
            ],
        ])
        ->all();
};

$listProductResults = computed(function () {
    $search = trim($this->listProductSearch);

    return Product::query()
        ->where('is_active', true)
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        })
        ->orderBy('name')
        ->limit(15)
        ->get(['id', 'name', 'sku'])
        ->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'image_url' => null,
        ])
        ->all();
});

$openListModal = function (?string $listId = null): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    $this->closeListModal();
    $this->editingListId = $listId;

    if ($listId) {
        $list = DeliveryPriceList::where('store_id', currentStoreId())->findOrFail($listId);

        $this->listName = $list->name;
        $this->listSelectedProductIds = $list->products()->pluck('products.id')->all();
        $this->listSelectedProducts = $list->products()
            ->get(['products.id', 'products.name'])
            ->pluck('name', 'id')
            ->all();
    }

    $this->showListModal = true;
};

$closeListModal = function (): void {
    $this->showListModal = false;
    $this->editingListId = null;
    $this->listName = '';
    $this->listSelectedProductIds = [];
    $this->listSelectedProducts = [];
    $this->listProductSearch = '';
    $this->resetErrorBag();
};

$toggleListProduct = function (string $productId): void {
    $product = Product::where('is_active', true)->find($productId);
    if (! $product) {
        return;
    }

    if (in_array($productId, $this->listSelectedProductIds, true)) {
        $this->listSelectedProductIds = array_values(array_diff($this->listSelectedProductIds, [$productId]));
        unset($this->listSelectedProducts[$productId]);
    } else {
        $this->listSelectedProductIds[] = $productId;
        $this->listSelectedProducts[$productId] = $product->name;
    }
};

$saveList = function (): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    $validated = $this->validate([
        'listName' => ['required', 'string', 'max:191'],
    ]);

    $isEdit = $this->editingListId !== null;

    if ($isEdit) {
        $list = DeliveryPriceList::where('store_id', currentStoreId())->findOrFail($this->editingListId);
        $list->update(['name' => $this->listName]);
    } else {
        $list = DeliveryPriceList::create([
            'store_id' => currentStoreId(),
            'name' => $this->listName,
            'is_active' => true,
        ]);
    }

    $list->products()->sync($this->listSelectedProductIds);

    $this->loadLists();
    $this->selectedListId = $list->id;
    $this->loadListRates($list->id);
    $this->closeListModal();

    $this->dispatch('swal', type: 'success', title: $isEdit
        ? __('merchant_panel.list_updated')
        : __('merchant_panel.list_created'));
};

$toggleListActive = function (string $listId): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    $list = DeliveryPriceList::where('store_id', currentStoreId())->findOrFail($listId);
    $list->update(['is_active' => ! $list->is_active]);

    if ($this->selectedListId === $listId) {
        $this->loadListRates($listId);
    }

    $this->loadLists();
};

$deleteList = function (string $listId): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);

    DeliveryPriceList::where('store_id', currentStoreId())->findOrFail($listId)->delete();

    $this->loadLists();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.list_deleted'));
};

$updateListStateCost = function (string $stateId, string $field, ?string $value): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    if (! $this->selectedListId) {
        return;
    }

    $rate = DeliveryRateListState::firstOrNew([
        'delivery_price_list_id' => $this->selectedListId,
        'state_id'               => $stateId,
    ]);

    $rate->{$field} = $value === '' ? null : $value;
    $rate->save();

    $this->listRatesByState[$stateId] = array_merge(
        $this->listRatesByState[$stateId] ?? ['home_cost' => '', 'office_cost' => ''],
        [$field => $value]
    );
};

// ——— List-state municipality popup ———

$openListStatePopup = function (string $stateId): void {
    if (! $this->selectedListId) {
        return;
    }

    $state = State::find($stateId);
    if (! $state) {
        return;
    }

    $this->listPopupStateId = $stateId;
    $this->listPopupStateName = $state->name;
    $this->listStateOfficeCost = $this->listRatesByState[$stateId]['office_cost'] ?? '';

    $priceLookup = DeliveryRateListCity::where('delivery_price_list_id', $this->selectedListId)
        ->where('state_id', $stateId)
        ->get()
        ->keyBy('city_id');

    $this->listPopupCitiesWithPrices = City::where('state_id', $stateId)
        ->active()
        ->orderBy('name')
        ->get(['id', 'name'])
        ->map(function ($city) use ($priceLookup) {
            $row = $priceLookup->get($city->id);

            return [
                'id' => $city->id,
                'name' => $city->name,
                'home_cost' => $row && $row->home_cost !== null ? (string) $row->home_cost : '',
            ];
        })
        ->all();

    $this->listApplyAllHomeCost = '';
    $this->showListStatePopup = true;
};

$saveListMunicipalityCost = function (string $stateId, string $cityId, ?string $value): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    if (! $this->selectedListId || ! $stateId || ! $cityId) {
        return;
    }

    if ($value === '' || $value === null) {
        DeliveryRateListCity::where('delivery_price_list_id', $this->selectedListId)
            ->where('state_id', $stateId)
            ->where('city_id', $cityId)
            ->delete();
    } else {
        DeliveryRateListCity::updateOrCreate(
            [
                'delivery_price_list_id' => $this->selectedListId,
                'state_id'               => $stateId,
                'city_id'                => $cityId,
            ],
            ['home_cost' => $value]
        );
    }

    foreach ($this->listPopupCitiesWithPrices as $i => $city) {
        if ($city['id'] === $cityId) {
            $this->listPopupCitiesWithPrices[$i]['home_cost'] = $value;
            break;
        }
    }
};

$applyAllListHomeCost = function (string $stateId): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    if (! $this->selectedListId || ! $stateId || $this->listApplyAllHomeCost === '') {
        return;
    }

    $value = $this->listApplyAllHomeCost;

    DB::transaction(function () use ($stateId, $value) {
        $cities = City::where('state_id', $stateId)->active()->pluck('id');

        foreach ($cities as $cityId) {
            DeliveryRateListCity::updateOrCreate(
                [
                    'delivery_price_list_id' => $this->selectedListId,
                    'state_id'               => $stateId,
                    'city_id'                => $cityId,
                ],
                ['home_cost' => $value]
            );
        }
    });

    foreach ($this->listPopupCitiesWithPrices as $i => $city) {
        $this->listPopupCitiesWithPrices[$i]['home_cost'] = $value;
    }

    $this->listApplyAllHomeCost = '';
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.apply_all_done'));
};

$saveListOffice = function (string $stateId, ?string $officeCost): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_PRICING_MANAGE->value), 403);
    if (! $this->selectedListId || ! $stateId) {
        return;
    }

    $rate = DeliveryRateListState::firstOrNew([
        'delivery_price_list_id' => $this->selectedListId,
        'state_id'               => $stateId,
    ]);
    $rate->office_cost = $officeCost === '' ? null : $officeCost;
    $rate->save();

    $this->listRatesByState[$stateId]['office_cost'] = $officeCost;
    $this->listStateOfficeCost = $officeCost;
};

$closeListStatePopup = function (): void {
    $this->showListStatePopup = false;
    $this->listPopupStateId = null;
    $this->listPopupCitiesWithPrices = [];
};
?>

<div>
    <x-edz.page-header title="{{ __('merchant_panel.announced_rates') }}"
        description="{{ __('merchant_panel.announced_rates_desc') }}">
    </x-edz.page-header>

    {{-- Sub-tabs: by delivery company / by price list --}}
    <div class="flex gap-1 p-1 bg-surface-secondary rounded-xl overflow-x-auto mb-5 max-w-md" role="tablist"
        aria-label="{{ __('merchant_panel.announced_rates') }}">
        <button type="button" role="tab"
            wire:click="setTab('company')"
            :aria-selected="$tab === 'company' ? 'true' : 'false'"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 flex-1 justify-center
            {{ $tab === 'company'
                ? 'bg-surface text-brand-fg shadow-sm'
                : 'text-ink-muted hover:text-ink-soft' }}">
            <x-edz.icon name="truck" class="w-4 h-4 shrink-0" />
            <span>{{ __('merchant_panel.announced_by_company') }}</span>
        </button>
        <button type="button" role="tab"
            wire:click="setTab('lists')"
            :aria-selected="$tab === 'lists' ? 'true' : 'false'"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 flex-1 justify-center
            {{ $tab === 'lists'
                ? 'bg-surface text-brand-fg shadow-sm'
                : 'text-ink-muted hover:text-ink-soft' }}">
            <x-edz.icon name="list-bullet" class="w-4 h-4 shrink-0" />
            <span>{{ __('merchant_panel.announced_by_list') }}</span>
        </button>
    </div>

    @if ($tab === 'company')
        @if (empty($providers))
        <div class="edz-card p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                <x-edz.icon name="banknotes" class="w-8 h-8 text-ink-muted opacity-40" />
            </div>
            <p class="text-ink-muted">{{ __('merchant_panel.no_rates_yet') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-5 items-start">

            {{-- Company side list --}}
            <aside class="edz-card edz-card--padded lg:sticky lg:top-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-3">
                    {{ __('merchant_panel.select_company') }}
                </p>
                <div class="flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible">
                    @foreach ($providers as $provider)
                        <button type="button"
                            wire:click="selectProvider('{{ $provider['id'] }}')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-start transition-colors whitespace-nowrap lg:whitespace-normal
                            {{ $selectedProviderId === $provider['id'] ? 'bg-brand-surface ring-1 ring-brand-ring' : 'hover:bg-surface-secondary' }}">
                            <x-edz.icon name="truck"
                                class="w-4 h-4 shrink-0 {{ $selectedProviderId === $provider['id'] ? 'text-brand-500' : 'text-ink-muted' }}" />
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium text-ink truncate">{{ $provider['name'] }}</span>
                                @if ($provider['carrier'])
                                    <span class="block text-xs text-ink-muted truncate">{{ $provider['carrier'] }}</span>
                                @endif
                            </span>
                            @if ($provider['rates_count'] > 0 && $selectedProviderId === $provider['id'])
                                <span class="edz-badge edz-badge--neutral shrink-0">{{ $provider['rates_count'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </aside>

            {{-- Main panel --}}
            <section>
                @if (! $selectedProviderId)
                    <div class="edz-card p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                            <x-edz.icon name="list-bullet" class="w-8 h-8 text-ink-muted opacity-40" />
                        </div>
                        <p class="text-ink-muted">{{ __('merchant_panel.select_company_hint') }}</p>
                    </div>
                @else
                    @php $currentProvider = collect($providers)->firstWhere('id', $selectedProviderId); @endphp

                    {{-- Header: company name + description + manual sync --}}
                    <div class="edz-card edz-card--padded mb-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-11 h-11 rounded-xl bg-brand-surface flex items-center justify-center shrink-0">
                                    <x-edz.icon name="truck" class="w-5 h-5 text-brand-500" />
                                </div>
                                <div>
                                    <h2 class="font-semibold text-ink">{{ $currentProvider['name'] ?? '' }}</h2>
                                    <p class="text-sm text-ink-muted">{{ __('merchant_panel.announced_provider_desc') }}</p>
                                </div>
                            </div>

                            <button type="button" wire:click="syncProvider"
                                class="edz-btn edz-btn--ghost edz-btn--sm" wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 pointer-events-none" wire:target="syncProvider">
                                <x-edz.spinner wire:target="syncProvider" />
                                <span wire:loading.remove wire:target="syncProvider">
                                    <x-edz.icon name="arrow-path" class="w-4 h-4" />
                                </span>
                                <span>{{ $syncing ? __('merchant_panel.syncing_rates') : __('merchant_panel.sync_rates') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Rates grid (designed as a table, no <table> element) --}}
                    <div class="edz-card overflow-hidden">
                        {{-- Header row --}}
                        <div class="grid grid-cols-12 gap-3 px-5 py-3 bg-surface-secondary text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            <div class="col-span-12 sm:col-span-6 lg:col-span-4">{{ __('merchant_panel.state') }}</div>
                            <div class="col-span-6 sm:col-span-3 lg:col-span-2">{{ __('merchant_panel.home_cost') }}</div>
                            <div class="col-span-6 sm:col-span-3 lg:col-span-2">{{ __('merchant_panel.office_cost') }}</div>
                            <div class="hidden lg:block lg:col-span-2 text-end">{{ __('merchant_panel.source') }}</div>
                            <div class="col-span-12 sm:col-span-12 lg:col-span-2 text-end">{{ __('merchant_panel.actions') }}</div>
                        </div>

                        {{-- Divider --}}
                        <div class="border-t border-surface-border"></div>

                        <div class="divide-y divide-surface-border">
                            @foreach ($states as $state)
                                @php
                                    $cell = array_merge(
                                        ['home_cost' => '', 'office_cost' => '', 'source' => 'manual'],
                                        $ratesByState[$state['id']] ?? []
                                    );
                                @endphp
                                <div wire:key="rate-row-{{ $selectedProviderId }}-{{ $state['id'] }}"
                                     class="grid grid-cols-12 gap-3 px-5 py-3.5 items-center hover:bg-surface-secondary/60 transition-colors">
                                    <div class="col-span-12 sm:col-span-6 lg:col-span-4 flex items-center gap-2 min-w-0">
                                        <span class="text-sm font-medium text-ink truncate">{{ $state['name'] }}</span>
                                    </div>

                                    {{-- Home (base) cost --}}
                                    <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                        <div class="relative max-w-[9rem]">
                                            <input type="number" step="0.01" min="0"
                                                value="{{ $cell['home_cost'] }}"
                                                wire:change="updateStateCost('{{ $state['id'] }}', 'home_cost', $event.target.value)"
                                                class="edz-input text-sm pr-8" placeholder="—">
                                            <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                        </div>
                                    </div>

                                    {{-- Office cost --}}
                                    <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                        <div class="relative max-w-[9rem]">
                                            <input type="number" step="0.01" min="0"
                                                value="{{ $cell['office_cost'] }}"
                                                wire:change="updateStateCost('{{ $state['id'] }}', 'office_cost', $event.target.value)"
                                                class="edz-input text-sm pr-8" placeholder="—">
                                            <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                        </div>
                                    </div>

                                    <div class="hidden lg:block lg:col-span-2">
                                        <span class="edz-badge {{ $cell['source'] === 'announced' ? 'edz-badge--info' : 'edz-badge--neutral' }}">
                                            {{ $cell['source'] === 'announced' ? __('merchant_panel.source_announced') : __('merchant_panel.source_manual') }}
                                        </span>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="col-span-12 sm:col-span-12 lg:col-span-2 lg:justify-self-end">
                                        <button type="button"
                                            wire:click="openStatePopup('{{ $state['id'] }}')"
                                            class="edz-btn edz-btn--ghost edz-btn--sm">
                                            <x-edz.icon name="pencil" class="w-4 h-4" />
                                            {{ __('merchant_panel.manage_municipalities') }}
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>
        @endif
    @else
        @if (empty($lists))
            <div class="edz-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                    <x-edz.icon name="list-bullet" class="w-8 h-8 text-ink-muted opacity-40" />
                </div>
                <p class="text-ink-muted mb-5">{{ __('merchant_panel.no_lists_yet') }}</p>
                <button type="button" wire:click="openListModal"
                    class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('merchant_panel.add_list') }}
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-5 items-start">

                {{-- Lists side column --}}
                <aside class="edz-card edz-card--padded lg:sticky lg:top-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            {{ __('merchant_panel.select_list') }}
                        </p>
                        <button type="button" wire:click="openListModal"
                            class="edz-btn edz-btn--primary edz-btn--sm shrink-0">
                            <x-edz.icon name="plus" class="w-4 h-4" />
                            <span class="hidden sm:inline">{{ __('merchant_panel.add_list') }}</span>
                        </button>
                    </div>

                    <div class="flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible">
                        @foreach ($lists as $list)
                            <button type="button"
                                wire:click="selectList('{{ $list['id'] }}')"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-start transition-colors whitespace-nowrap lg:whitespace-normal
                                {{ $selectedListId === $list['id'] ? 'bg-brand-surface ring-1 ring-brand-ring' : 'hover:bg-surface-secondary' }}">
                                <x-edz.icon name="list-bullet"
                                    class="w-4 h-4 shrink-0 {{ $selectedListId === $list['id'] ? 'text-brand-500' : 'text-ink-muted' }}" />
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-medium text-ink truncate">{{ $list['name'] }}</span>
                                    <span class="block text-xs text-ink-muted truncate">
                                        {{ __('merchant_panel.list_products_count', ['count' => $list['products_count']]) }}
                                    </span>
                                </span>
                                @if (! $list['is_active'])
                                    <span class="edz-badge edz-badge--warning shrink-0">{{ __('merchant_panel.list_inactive') }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </aside>

                {{-- Main panel: selected list rates --}}
                <section>
                    @if (! $selectedListId)
                        <div class="edz-card p-12 text-center">
                            <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                                <x-edz.icon name="list-bullet" class="w-8 h-8 text-ink-muted opacity-40" />
                            </div>
                            <p class="text-ink-muted">{{ __('merchant_panel.select_list_hint') }}</p>
                        </div>
                    @else
                        @php $currentList = collect($lists)->firstWhere('id', $selectedListId); @endphp

                        {{-- Header: list name + quick actions --}}
                        <div class="edz-card edz-card--padded mb-4">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-brand-surface flex items-center justify-center shrink-0">
                                        <x-edz.icon name="list-bullet" class="w-5 h-5 text-brand-500" />
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h2 class="font-semibold text-ink">{{ $currentList['name'] ?? '' }}</h2>
                                            <span class="edz-badge {{ $currentList['is_active'] ? 'edz-badge--success' : 'edz-badge--warning' }}">
                                                {{ $currentList['is_active'] ? __('merchant_panel.list_active') : __('merchant_panel.list_inactive') }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-ink-muted mt-0.5">
                                            {{ __('merchant_panel.list_products_count', ['count' => $currentList['products_count'] ?? 0]) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" wire:click="openListModal('{{ $selectedListId }}')"
                                        class="edz-btn edz-btn--ghost edz-btn--sm">
                                        <x-edz.icon name="pencil" class="w-4 h-4" />
                                        {{ __('merchant_panel.edit') }}
                                    </button>
                                    <button type="button" wire:click="toggleListActive('{{ $selectedListId }}')"
                                        class="edz-btn edz-btn--ghost edz-btn--sm">
                                        <x-edz.icon name="arrow-path" class="w-4 h-4" />
                                        {{ $currentList['is_active'] ? __('merchant_panel.list_disable') : __('merchant_panel.list_enable') }}
                                    </button>
                                    <button type="button" aria-label="{{ __('merchant_panel.confirm_delete_list') }}"
                                        class="edz-btn edz-btn--ghost edz-btn--sm"
                                        x-on:click="EdzSwal.confirmDelete(() => { $wire.deleteList('{{ $selectedListId }}') })">
                                        <x-edz.icon name="trash" class="w-4 h-4 text-danger-600" />
                                        {{ __('merchant_panel.delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- List rates grid (states: home/office + municipalities) --}}
                        <div class="edz-card overflow-hidden">
                            <div class="grid grid-cols-12 gap-3 px-5 py-3 bg-surface-secondary text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                <div class="col-span-12 sm:col-span-6 lg:col-span-4">{{ __('merchant_panel.state') }}</div>
                                <div class="col-span-6 sm:col-span-3 lg:col-span-2">{{ __('merchant_panel.home_cost') }}</div>
                                <div class="col-span-6 sm:col-span-3 lg:col-span-2">{{ __('merchant_panel.office_cost') }}</div>
                                <div class="col-span-12 sm:col-span-12 lg:col-span-4 text-end">{{ __('merchant_panel.actions') }}</div>
                            </div>

                            <div class="border-t border-surface-border"></div>

                            <div class="divide-y divide-surface-border">
                                @foreach ($states as $state)
                                    @php
                                        $cell = array_merge(
                                            ['home_cost' => '', 'office_cost' => ''],
                                            $listRatesByState[$state['id']] ?? []
                                        );
                                    @endphp
                                    <div wire:key="list-rate-row-{{ $selectedListId }}-{{ $state['id'] }}"
                                         class="grid grid-cols-12 gap-3 px-5 py-3.5 items-center hover:bg-surface-secondary/60 transition-colors">
                                        <div class="col-span-12 sm:col-span-6 lg:col-span-4 flex items-center gap-2 min-w-0">
                                            <span class="text-sm font-medium text-ink truncate">{{ $state['name'] }}</span>
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <div class="relative max-w-[9rem]">
                                                <input type="number" step="0.01" min="0"
                                                    value="{{ $cell['home_cost'] }}"
                                                    wire:change="updateListStateCost('{{ $state['id'] }}', 'home_cost', $event.target.value)"
                                                    class="edz-input text-sm pr-8" placeholder="—">
                                                <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                            </div>
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <div class="relative max-w-[9rem]">
                                                <input type="number" step="0.01" min="0"
                                                    value="{{ $cell['office_cost'] }}"
                                                    wire:change="updateListStateCost('{{ $state['id'] }}', 'office_cost', $event.target.value)"
                                                    class="edz-input text-sm pr-8" placeholder="—">
                                                <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                            </div>
                                        </div>

                                        <div class="col-span-12 sm:col-span-12 lg:col-span-4 lg:justify-self-end">
                                            <button type="button"
                                                wire:click="openListStatePopup('{{ $state['id'] }}')"
                                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                                <x-edz.icon name="pencil" class="w-4 h-4" />
                                                {{ __('merchant_panel.manage_municipalities') }}
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        @endif
    @endif

    {{-- ============ STATE / MUNICIPALITIES POPUP ============ --}}
    @if ($showStatePopup)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="xl"
            wire:key="state-popup-{{ $selectedProviderId }}-{{ $popupStateId }}">
            <div class="p-6">
                {{-- Header: state name + close --}}
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-bold text-ink">{{ $popupStateName }}</h3>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                            wire:click="closeStatePopup">
                        <x-edz.icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>
                <p class="text-sm text-ink-muted mb-5">{{ __('merchant_panel.state_popup_desc') }}</p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    {{-- Municipalities pane (RTL: first => right; LTR: first => left) --}}
                    <div class="edz-card edz-card--padded">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <p class="font-semibold text-ink">{{ __('merchant_panel.municipalities') }}</p>

                            {{-- Apply-to-all home price --}}
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <input type="number" step="0.01" min="0"
                                        wire:model="applyAllHomeCost"
                                        wire:keydown.enter="applyAllHomeCost('{{ $popupStateId }}')"
                                        class="edz-input text-sm pr-8 w-32" placeholder="0">
                                    <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                </div>
                                <button type="button"
                                    wire:click="applyAllHomeCost('{{ $popupStateId }}')"
                                    class="edz-btn edz-btn--primary edz-btn--sm"
                                    wire:loading.attr="disabled" wire:target="applyAllHomeCost">
                                    {{ __('merchant_panel.apply_all') }}
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-12 gap-3 px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            <div class="col-span-7">{{ __('merchant_panel.municipality') }}</div>
                            <div class="col-span-5 text-end">{{ __('merchant_panel.home_cost') }}</div>
                        </div>

                        <div class="divide-y divide-surface-border max-h-[26rem] overflow-y-auto edz-scroll">
                            @foreach ($popupCitiesWithPrices as $city)
                                <div wire:key="city-row-{{ $city['id'] }}" class="grid grid-cols-12 gap-3 px-2 py-2.5 items-center">
                                    <div class="col-span-7 text-sm text-ink truncate">{{ $city['name'] }}</div>
                                    <div class="col-span-5">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                value="{{ $city['home_cost'] }}"
                                                wire:change="saveMunicipalityCost('{{ $popupStateId }}', '{{ $city['id'] }}', $event.target.value)"
                                                class="edz-input text-sm pr-8 w-full" placeholder="—">
                                            <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Default center + office price pane --}}
                    <div class="edz-card edz-card--padded">
                        <p class="font-semibold text-ink mb-4">{{ __('merchant_panel.default_center') }}</p>

                        <div class="space-y-4">
                            <div>
                                <label class="edz-label">{{ __('merchant_panel.select_default_center') }}</label>
                                <x-edz.select wire:model="stateDefaultCenterId"
                                    wire:change="saveDefaultCenter('{{ $popupStateId }}', $event.target.value)"
                                    :options="collect($popupCenters)->map(fn ($c) => ['value' => $c['id'], 'label' => $c['name']])->all()"
                                    placeholder="{{ __('merchant_panel.no_center_selected') }}" size="sm" search />
                                @if (empty($popupCenters))
                                    <p class="text-xs text-ink-muted mt-2">{{ __('merchant_panel.no_centers_hint') }}</p>
                                @endif
                            </div>

                            <div>
                                <label class="edz-label">{{ __('merchant_panel.office_cost') }}</label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0"
                                        value="{{ $stateOfficeCost }}"
                                        wire:change="saveStateOffice('{{ $popupStateId }}', $event.target.value)"
                                        class="edz-input text-sm pr-8" placeholder="—">
                                    <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                </div>
                                <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.office_cost_hint') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-edz.modal>
    @endif

    {{-- ============ PRICE LIST MODAL (create / edit + products) ============ --}}
    @if ($showListModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="lg"
            wire:key="list-modal-{{ $editingListId ?? 'new' }}">
            <div class="p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-bold text-ink">
                        {{ $editingListId ? __('merchant_panel.edit_list') : __('merchant_panel.new_list') }}
                    </h3>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="closeListModal">
                        <x-edz.icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>
                <p class="text-sm text-ink-muted mb-5">{{ __('merchant_panel.list_modal_desc') }}</p>

                <form wire:submit="saveList" class="space-y-5">
                    <div>
                        <label for="list-name" class="edz-label">{{ __('merchant_panel.list_name') }}</label>
                        <input id="list-name" type="text" wire:model="listName"
                            class="edz-input w-full" placeholder="{{ __('merchant_panel.list_name_placeholder') }}">
                        @error('listName')
                            <p class="text-xs font-medium mt-1.5 text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="edz-label">{{ __('merchant_panel.select_products') }}</label>
                        <div class="relative">
                            <x-edz.product-multi-picker
                                :options="$this->listProductResults"
                                :selected="$listSelectedProductIds"
                                :selected-names="$listSelectedProducts"
                                toggle="toggleListProduct"
                                model="listProductSearch"
                                :placeholder="__('merchant_panel.search_products_to_add')"
                                :empty-message="__('merchant_panel.list_no_products_found')" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-surface-border">
                        <button type="button" wire:click="closeListModal" class="edz-btn edz-btn--ghost edz-btn--sm">
                            {{ __('merchant_panel.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm"
                            wire:loading.attr="disabled" wire:target="saveList">
                            <span wire:loading.remove wire:target="saveList">
                                <x-edz.icon name="check" class="w-4 h-4" />
                            </span>
                            <x-edz.spinner wire:target="saveList" />
                            {{ __('merchant_panel.save_list') }}
                        </button>
                    </div>
                </form>
            </div>
        </x-edz.modal>
    @endif

    {{-- ============ LIST-STATE / MUNICIPALITIES POPUP ============ --}}
    @if ($showListStatePopup)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="xl"
            wire:key="list-state-popup-{{ $selectedListId }}-{{ $listPopupStateId }}">
            <div class="p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-bold text-ink">{{ $listPopupStateName }}</h3>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="closeListStatePopup">
                        <x-edz.icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>
                <p class="text-sm text-ink-muted mb-5">{{ __('merchant_panel.state_popup_desc') }}</p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    {{-- Municipalities pane --}}
                    <div class="edz-card edz-card--padded">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <p class="font-semibold text-ink">{{ __('merchant_panel.municipalities') }}</p>

                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <input type="number" step="0.01" min="0"
                                        wire:model="listApplyAllHomeCost"
                                        wire:keydown.enter="applyAllListHomeCost('{{ $listPopupStateId }}')"
                                        class="edz-input text-sm pr-8 w-32" placeholder="0">
                                    <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                </div>
                                <button type="button"
                                    wire:click="applyAllListHomeCost('{{ $listPopupStateId }}')"
                                    class="edz-btn edz-btn--primary edz-btn--sm"
                                    wire:loading.attr="disabled" wire:target="applyAllListHomeCost">
                                    {{ __('merchant_panel.apply_all') }}
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-12 gap-3 px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            <div class="col-span-7">{{ __('merchant_panel.municipality') }}</div>
                            <div class="col-span-5 text-end">{{ __('merchant_panel.home_cost') }}</div>
                        </div>

                        <div class="divide-y divide-surface-border max-h-[26rem] overflow-y-auto edz-scroll">
                            @foreach ($listPopupCitiesWithPrices as $city)
                                <div wire:key="list-city-row-{{ $city['id'] }}"
                                    class="grid grid-cols-12 gap-3 px-2 py-2.5 items-center">
                                    <div class="col-span-7 text-sm text-ink truncate">{{ $city['name'] }}</div>
                                    <div class="col-span-5">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                value="{{ $city['home_cost'] }}"
                                                wire:change="saveListMunicipalityCost('{{ $listPopupStateId }}', '{{ $city['id'] }}', $event.target.value)"
                                                class="edz-input text-sm pr-8 w-full" placeholder="—">
                                            <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Office price pane --}}
                    <div class="edz-card edz-card--padded">
                        <p class="font-semibold text-ink mb-4">{{ __('merchant_panel.office_cost') }}</p>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.office_cost') }}</label>
                            <div class="relative">
                                <input type="number" step="0.01" min="0"
                                    value="{{ $listStateOfficeCost }}"
                                    wire:change="saveListOffice('{{ $listPopupStateId }}', $event.target.value)"
                                    class="edz-input text-sm pr-8" placeholder="—">
                                <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                            </div>
                            <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.office_cost_hint') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-edz.modal>
    @endif
</div>