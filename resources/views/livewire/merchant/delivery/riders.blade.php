<?php

use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Services\DeliveryRiderService;
use App\Enums\Store\StorePermissionEnum;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'riders' => [],

    // Rider modal
    'showRiderModal' => false,
    'editingRiderId' => null,
    'riderForm' => [
        'name' => '',
        'phone' => '',
        'email' => '',
        'vehicle_type' => '',
        'notes' => '',
        'is_active' => true,
    ],
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_RIDERS_VIEW->value) ||
        canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->loadData();
});

$loadData = function (): void {
    $this->riders = app(DeliveryRiderService::class)
        ->listForStore(currentStoreId())
        ->map(function (DeliveryRider $rider) {
            return [
                'id' => $rider->id,
                'name' => $rider->name,
                'phone' => $rider->phone,
                'email' => $rider->email,
                'vehicle_type' => $rider->vehicle_type,
                'vehicle_label' => $rider->vehicle_label,
                'notes' => $rider->notes,
                'is_active' => (bool) $rider->is_active,
                'orders_count' => (int) ($rider->orders_count ?? 0),
            ];
        })
        ->all();
};

$openRiderModal = function (?string $riderId = null): void {
    abort_unless(canStore(
        $riderId
            ? StorePermissionEnum::DELIVERY_RIDERS_UPDATE->value
            : StorePermissionEnum::DELIVERY_RIDERS_CREATE->value
    ), 403);

    if ($riderId) {
        $rider = app(DeliveryRiderService::class)->findForStore($riderId, currentStoreId());
        if (! $rider) {
            return;
        }
        $this->editingRiderId = $rider->id;
        $this->riderForm = [
            'name' => $rider->name,
            'phone' => $rider->phone,
            'email' => $rider->email,
            'vehicle_type' => $rider->vehicle_type,
            'notes' => $rider->notes,
            'is_active' => (bool) $rider->is_active,
        ];
    } else {
        $this->editingRiderId = null;
        $this->riderForm = [
            'name' => '',
            'phone' => '',
            'email' => '',
            'vehicle_type' => DeliveryRider::VEHICLE_MOTORCYCLE,
            'notes' => '',
            'is_active' => true,
        ];
    }
    $this->showRiderModal = true;
};

$saveRider = function (): void {
    abort_unless(canStore(
        $this->editingRiderId
            ? StorePermissionEnum::DELIVERY_RIDERS_UPDATE->value
            : StorePermissionEnum::DELIVERY_RIDERS_CREATE->value
    ), 403);

    $this->validate([
        'riderForm.name' => 'required|string|max:255',
        'riderForm.phone' => 'required|string|max:20',
        'riderForm.email' => 'nullable|email|max:255',
        'riderForm.vehicle_type' => 'required|in:' . implode(',', array_keys(DeliveryRider::vehicleOptions())),
    ]);

    $data = [
        'name' => $this->riderForm['name'],
        'phone' => $this->riderForm['phone'],
        'email' => $this->riderForm['email'] ?: null,
        'vehicle_type' => $this->riderForm['vehicle_type'],
        'notes' => $this->riderForm['notes'] ?: null,
        'is_active' => $this->riderForm['is_active'],
    ];

    $service = app(DeliveryRiderService::class);
    $storeId = currentStoreId();

    if ($this->editingRiderId) {
        $service->update($this->editingRiderId, $storeId, $data);
    } else {
        $service->create($storeId, $data);
    }

    $this->showRiderModal = false;
    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.rider_saved'));
};

$deleteRider = function (string $id): void {
    abort_unless(canStore(StorePermissionEnum::DELIVERY_RIDERS_DELETE->value), 403);
    app(DeliveryRiderService::class)->delete($id, currentStoreId());
    $this->loadData();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.rider_deleted'));
};
?>

<div>
    <x-edz.page-header title="{{ __('merchant_panel.tab_riders') }}"
        description="{{ __('merchant_panel.tab_riders_desc') }}">
    </x-edz.page-header>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-ink-muted">{{ __('merchant_panel.rider_own_desc') }}</p>
        @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_RIDERS_CREATE->value))
            <button wire:click="openRiderModal" class="edz-btn edz-btn--primary edz-btn--sm">
                <x-edz.icon name="plus" class="w-4 h-4" />
                {{ __('merchant_panel.new_rider') }}
            </button>
        @endif
    </div>

    @if (!empty($riders))
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($riders as $rider)
                <div wire:key="rider-{{ $rider['id'] }}" class="edz-card edz-card--padded">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-xl bg-info-surface flex items-center justify-center shrink-0">
                                <x-edz.icon name="user" class="w-5 h-5 text-info-500" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-ink truncate">{{ $rider['name'] }}</p>
                                <p class="text-xs text-ink-muted flex items-center gap-1">
                                    <x-edz.icon name="truck" class="w-3.5 h-3.5" />
                                    {{ $rider['vehicle_label'] ?? $rider['vehicle_type'] }}
                                </p>
                            </div>
                        </div>
                        <x-edz.badge :tone=" $rider['is_active'] ? 'success' : 'neutral' ">
                            {{ $rider['is_active'] ? __('merchant_panel.rider_active') : __('merchant_panel.rider_inactive') }}
                        </x-edz.badge>
                    </div>

                    <div class="space-y-1.5 text-sm text-ink-muted">
                        @if ($rider['phone'])
                            <p class="flex items-center gap-1.5" dir="ltr">
                                <x-edz.icon name="phone" class="w-3.5 h-3.5" />
                                <span class="text-start">{{ $rider['phone'] }}</span>
                            </p>
                        @endif
                        @if ($rider['email'])
                            <p class="flex items-center gap-1.5 truncate">
                                <x-edz.icon name="mail" class="w-3.5 h-3.5" />
                                <span class="truncate text-start" dir="ltr">{{ $rider['email'] }}</span>
                            </p>
                        @endif
                        <p class="flex items-center gap-1.5">
                            <x-edz.icon name="list-bullet" class="w-3.5 h-3.5" />
                            {{ __('merchant_panel.rider_orders_count') }}: {{ $rider['orders_count'] }}
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-1 mt-4 pt-3 border-t border-surface-border">
                            @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_RIDERS_UPDATE->value))
                                <button type="button" aria-label="{{ __('merchant_panel.edit_rider') }}"
                                        wire:click="openRiderModal('{{ $rider['id'] }}')"
                                        class="edz-btn edz-btn--ghost edz-btn--sm">
                                    <x-edz.icon name="edit" class="w-4 h-4" />
                                </button>
                            @endif
                            @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_RIDERS_DELETE->value))
                                <button type="button" aria-label="{{ __('merchant_panel.confirm_delete_rider') }}"
                                        class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                        x-data
                                        x-on:click="EdzSwal.confirmDelete(() => { $wire.deleteRider('{{ $rider['id'] }}') })">
                                    <x-edz.icon name="trash" class="w-4 h-4" />
                                </button>
                            @endif
                        </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="edz-card p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                <x-edz.icon name="user" class="w-8 h-8 text-ink-muted opacity-40" />
            </div>
            <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_riders_yet') }}</p>
            @if (canStore(\App\Enums\Store\StorePermissionEnum::DELIVERY_RIDERS_CREATE->value))
                <button wire:click="openRiderModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('merchant_panel.new_rider') }}
                </button>
            @endif
        </div>
    @endif

    {{-- ============ RIDER MODAL ============ --}}
    @if ($showRiderModal)
        <x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="lg"
            wire:key="rider-modal-{{ $editingRiderId ?? 'new' }}">
            <form wire:submit="saveRider">
                <div class="p-6 space-y-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">
                            {{ $editingRiderId ? __('merchant_panel.edit_rider') : __('merchant_panel.new_rider') }}
                        </h3>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="set('showRiderModal', false)">
                            <x-edz.icon name="x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.rider_name') }} *</label>
                            <input type="text" wire:model="riderForm.name" class="edz-input text-sm" required>
                            @error('riderForm.name')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.rider_phone') }} *</label>
                            <input type="tel" wire:model="riderForm.phone" class="edz-input text-sm" dir="ltr" required>
                            @error('riderForm.phone')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.rider_email') }}</label>
                            <input type="email" wire:model="riderForm.email" class="edz-input text-sm" dir="ltr">
                            @error('riderForm.email')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="edz-label">{{ __('merchant_panel.rider_vehicle') }} *</label>
                            <select wire:model="riderForm.vehicle_type" class="edz-input text-sm" required>
                                @foreach (\App\Domains\Shipping\Models\DeliveryRider::vehicleOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('riderForm.vehicle_type')
                                <span class="text-danger-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="edz-label">{{ __('merchant_panel.rider_notes') }}</label>
                            <textarea wire:model="riderForm.notes" rows="2" class="edz-input text-sm"
                                placeholder="{{ __('merchant_panel.rider_notes_placeholder') }}"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="riderForm.is_active" class="edz-checkbox" />
                                <span class="text-sm text-ink">{{ __('merchant_panel.rider_active') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex justify-end gap-2 pt-2 border-t border-surface-border">
                        <button type="button" class="edz-btn edz-btn--ghost"
                                wire:click="set('showRiderModal', false)">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary" wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 pointer-events-none" wire:target="saveRider">
                            <x-edz.spinner wire:target="saveRider" />
                            <span wire:loading.remove wire:target="saveRider">{{ __('buttons.save') }}</span>
                            <span class="sr-only">{{ __('buttons.save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-edz.modal>
    @endif
</div>
