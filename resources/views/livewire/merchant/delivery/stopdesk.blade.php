<?php

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Locations\City;
use App\Models\Locations\State;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'stopdeskPoints' => [],
    'states' => [],
    'cities' => [],

    // Stopdesk modal
    'showStopdeskModal' => false,
    'editingStopdeskId' => null,
    'providers' => [],
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

    $this->providers = ShippingProvider::where('store_id', $storeId)
        ->orderBy('name')
        ->get()
        ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])
        ->all();

    $this->stopdeskPoints = StopdeskPoint::where('store_id', $storeId)
        ->with('provider', 'state', 'city')
        ->orderBy('name')
        ->get()
        ->toArray();
};

$watchState = function (string $stateId): void {
    $this->stopdeskForm['state_id'] = $stateId;
    $this->cities = $stateId
        ? City::where('state_id', $stateId)->orderBy('name')->get(['id', 'name'])->toArray()
        : [];
    $this->stopdeskForm['city_id'] = '';
};

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
    <x-edz.page-header title="{{ __('merchant_panel.tab_stopdesk') }}"
        description="{{ __('merchant_panel.tab_stopdesk_desc') }}">
    </x-edz.page-header>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-ink-muted">{{ __('merchant_panel.pickup_points_desc') }}</p>
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
                            <div class="w-11 h-11 rounded-xl bg-info-surface flex items-center justify-center">
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
                        <div class="flex items-center justify-end gap-1 mt-4 pt-3 border-t border-surface-border">
                            <button type="button" aria-label="{{ __('merchant_panel.edit_stopdesk') }}"
                                    wire:click="openStopdeskModal('{{ $point['id'] }}')"
                                    class="edz-btn edz-btn--ghost edz-btn--sm">
                                <x-edz.icon name="edit" class="w-4 h-4" />
                            </button>
                            <button type="button" aria-label="{{ __('merchant_panel.confirm_delete_stopdesk') }}"
                                    class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                    x-data
                                    x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete()) await $wire.deleteStopdesk('{{ $point['id'] }}') })()">
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
            @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_PRICING_MANAGE->value))
                <button wire:click="openStopdeskModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('merchant_panel.new_stopdesk') }}
                </button>
            @endif
        </div>
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