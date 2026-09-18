<?php

use App\Enums\Store\StorePermissionEnum;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'overflowEnabled' => true,
    'overflowPercentage' => 10,
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $settings = currentStore()?->settings;

    $this->overflowEnabled = (bool) ($settings->distribution_overflow_enabled ?? true);
    $this->overflowPercentage = (int) ($settings->distribution_overflow_percentage ?? 10);
});

$save = function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $store = currentStore();
    abort_unless($store, 404);

    $this->validate([
        'overflowPercentage' => ['required', 'integer', 'min:0', 'max:100'],
    ]);

    $store->settings()->updateOrCreate([], [
        'distribution_overflow_enabled' => $this->overflowEnabled,
        'distribution_overflow_percentage' => $this->overflowPercentage,
    ]);

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
};
?>

<div>
    <x-edz.page-header title="{{ __('merchant_panel.order_distribution_settings') }}"
        description="{{ __('merchant_panel.order_distribution_settings_desc') }}">
    </x-edz.page-header>

    <div class="max-w-2xl">
        @include('livewire.merchant.order-distribution-settings.partials.overflow-settings')
    </div>
</div>