<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\StoreSetting;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'name'            => '',
    'description'     => '',
    'currency'        => 'DZD',
    'language'        => 'ar',
    'guest_checkout'  => true,
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $store = currentStore();
    abort_unless($store, 404);

    $settings = $store->settings;

    $this->name = $store->name;
    $this->description = $store->description ?? '';
    $this->currency = $settings->currency ?? 'DZD';
    $this->language = $settings->language ?? 'ar';
    $this->guest_checkout = $settings->guest_checkout ?? true;
});

$save = function (): void {
    $store = currentStore();
    abort_unless($store, 404);

    $store->update([
        'name' => $this->name,
        'description' => $this->description,
    ]);

    $store->settings()->updateOrCreate([], [
        'currency' => $this->currency,
        'language' => $this->language,
        'guest_checkout' => $this->guest_checkout,
    ]);

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
};
?>

<div>
    <x-edz.page-header
        title="{{ __('merchant_panel.store_settings') }}"
        description="{{ __('merchant_panel.store_settings_desc') }}">
    </x-edz.page-header>

    <form wire:submit="save" x-data="edzDirty()">
        <div class="edz-card edz-card--padded space-y-6">
            <h3 class="text-lg font-semibold text-ink">{{ __('merchant_panel.general_info') }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="edz-label">{{ __('general.store_name') }}</label>
                    <input type="text" id="name" wire:model="name"
                           class="edz-input" required />
                </div>

                <div>
                    <label for="currency" class="edz-label">{{ __('merchant_panel.currency') }}</label>
                    <select id="currency" wire:model="currency" class="edz-input">
                        <option value="DZD">DZD — {{ __('merchant_panel.algerian_dinar') }}</option>
                        <option value="USD">USD — US Dollar</option>
                        <option value="EUR">EUR — Euro</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="edz-label">{{ __('general.description') }}</label>
                    <textarea id="description" wire:model="description" rows="3"
                              class="edz-input"></textarea>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700" />

            <h3 class="text-lg font-semibold text-ink">{{ __('merchant_panel.preferences') }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="language" class="edz-label">{{ __('merchant_panel.language') }}</label>
                    <select id="language" wire:model="language" class="edz-input">
                        <option value="ar">{{ __('merchant_panel.arabic') }}</option>
                        <option value="fr">{{ __('merchant_panel.french') }}</option>
                        <option value="en">{{ __('merchant_panel.english') }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" id="guest_checkout" wire:model="guest_checkout"
                           class="edz-checkbox" />
                    <label for="guest_checkout" class="edz-label mb-0">{{ __('merchant_panel.guest_checkout') }}</label>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="edz-btn edz-btn--primary">
                    {{ __('buttons.save') }}
                </button>
            </div>
        </div>
    </form>
</div>
