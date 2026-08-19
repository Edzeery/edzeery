<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\StoreSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\usesFileUploads;

usesFileUploads();

layout('components.layouts.store');

state([
    'name' => '',
    'description' => '',
    'phone' => '',
    'logo' => null,
    'cover' => null,
    'currency' => 'DZD',
    'language' => 'ar',
    'supported_languages' => [],
    'guest_checkout' => true,
    'inventory_tracking' => true,
    'show_out_of_stock' => false,
    'allow_backorder' => false,
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $store = currentStore();
    abort_unless($store, 404);

    $settings = $store->settings;
    $hasNewCols = Schema::hasColumn('store_settings', 'supported_languages');

    $this->name = $store->name;
    $this->description = $store->description ?? '';
    $this->currency = $settings->currency ?? 'DZD';
    $this->language = $settings->language ?? 'ar';
    $this->supported_languages = $hasNewCols ? $settings->supported_languages ?? [$settings->language ?? 'ar'] : [$settings->language ?? 'ar'];
    $this->phone = $hasNewCols ? $settings->phone ?? '' : '';
    $this->guest_checkout = $settings->guest_checkout ?? true;
    $this->inventory_tracking = $settings->inventory_tracking ?? true;
    $this->show_out_of_stock = $settings->show_out_of_stock ?? false;
    $this->allow_backorder = $settings->allow_backorder ?? false;
});

$save = function (): void {
    $store = currentStore();
    abort_unless($store, 404);

    $data = [
        'name' => $this->name,
        'description' => $this->description,
    ];

    if ($this->logo instanceof TemporaryUploadedFile) {
        $data['logo'] = $this->logo->store('stores', 'public');
    }

    if ($this->cover instanceof TemporaryUploadedFile) {
        $data['cover'] = $this->cover->store('stores', 'public');
    }

    $store->update($data);

    $settingsData = [
        'currency' => $this->currency,
        'language' => $this->language,
        'guest_checkout' => $this->guest_checkout,
        'inventory_tracking' => $this->inventory_tracking,
        'show_out_of_stock' => $this->show_out_of_stock,
        'allow_backorder' => $this->allow_backorder,
    ];

    if (Schema::hasColumn('store_settings', 'supported_languages')) {
        $settingsData['supported_languages'] = $this->supported_languages;
    }
    if (Schema::hasColumn('store_settings', 'phone')) {
        $settingsData['phone'] = $this->phone;
    }

    $store->settings()->updateOrCreate([], $settingsData);

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
};
?>

<div>
    <x-edz.page-header title="{{ __('merchant_panel.store_settings') }}"
        description="{{ __('merchant_panel.store_settings_desc') }}">
    </x-edz.page-header>

    <form wire:submit="save" x-data="edzDirty()">
        {{-- Branding --}}
        <div class="edz-card edz-card--padded mb-6">
            <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                <ion-icon name="color-palette-outline" class="text-lg text-accent-500"></ion-icon>
                {{ __('merchant_panel.branding') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Logo --}}
                <div>
                    <label class="edz-label">{{ __('stores.logo') }}</label>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-20 h-20 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border overflow-hidden flex items-center justify-center bg-neutral-secondary dark:bg-dark-secondary shrink-0">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover" />
                            @elseif(currentStore()?->logo)
                                <img src="{{ asset('storage/' . currentStore()->logo) }}"
                                    class="w-full h-full object-cover" />
                            @else
                                <ion-icon name="image-outline" class="text-2xl text-ink-muted"></ion-icon>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="logo" accept="image/*" class="edz-input text-sm" />
                            @error('logo')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Cover --}}
                <div>
                    <label class="edz-label">{{ __('stores.cover') }}</label>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-32 h-20 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border overflow-hidden flex items-center justify-center bg-neutral-secondary dark:bg-dark-secondary shrink-0">
                            @if ($cover)
                                <img src="{{ $cover->temporaryUrl() }}" class="w-full h-full object-cover" />
                            @elseif(currentStore()?->cover)
                                <img src="{{ asset('storage/' . currentStore()->cover) }}"
                                    class="w-full h-full object-cover" />
                            @else
                                <ion-icon name="image-outline" class="text-2xl text-ink-muted"></ion-icon>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="cover" accept="image/*" class="edz-input text-sm" />
                            @error('cover')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- General Info --}}
        <div class="edz-card edz-card--padded mb-6">
            <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                <ion-icon name="information-circle-outline" class="text-lg text-accent-500"></ion-icon>
                {{ __('merchant_panel.general_info') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="edz-label">{{ __('general.store_name') }}</label>
                    <input type="text" wire:model="name" class="edz-input" required />
                </div>

                <div>
                    <label class="edz-label">{{ __('merchant_panel.phone') }}</label>
                    <input type="tel" wire:model="phone" class="edz-input" placeholder="0XXX XX XX XX" />
                </div>

                <div class="md:col-span-2">
                    <label class="edz-label">{{ __('general.description') }}</label>
                    <textarea wire:model="description" rows="3" class="edz-input"></textarea>
                </div>
            </div>
        </div>

        {{-- Commerce --}}
        <div class="edz-card edz-card--padded mb-6">
            <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                <ion-icon name="card-outline" class="text-lg text-accent-500"></ion-icon>
                {{ __('merchant_panel.commerce') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="edz-label">{{ __('merchant_panel.currency') }}</label>
                    <select wire:model="currency" class="edz-input">
                        <option value="DZD">DZD — {{ __('merchant_panel.algerian_dinar') }}</option>
                        <option value="USD">USD — US Dollar</option>
                        <option value="EUR">EUR — Euro</option>
                        <option value="MAD">MAD — Moroccan Dirham</option>
                        <option value="TND">TND — Tunisian Dinar</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" id="guest_checkout" wire:model="guest_checkout" class="edz-checkbox" />
                    <label for="guest_checkout"
                        class="edz-label mb-0">{{ __('merchant_panel.guest_checkout') }}</label>
                </div>
            </div>
        </div>

        {{-- Inventory --}}
        <div class="edz-card edz-card--padded mb-6">
            <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                <ion-icon name="cube-outline" class="text-lg text-accent-500"></ion-icon>
                {{ __('merchant_panel.inventory_group') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <label
                    class="flex items-start gap-3 p-4 rounded-xl border transition-all cursor-pointer
                    {{ $inventory_tracking ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10' : 'border-neutral-border dark:border-dark-border' }}">
                    <input type="checkbox" wire:model="inventory_tracking"
                        class="mt-0.5 rounded border-neutral-border text-accent-600 focus:ring-accent-500" />
                    <div>
                        <p class="text-sm font-medium text-ink">{{ __('merchant_panel.inventory_tracking') }}</p>
                        <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.inventory_tracking_desc') }}</p>
                    </div>
                </label>

                <label
                    class="flex items-start gap-3 p-4 rounded-xl border transition-all cursor-pointer
                    {{ $allow_backorder ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10' : 'border-neutral-border dark:border-dark-border' }}">
                    <input type="checkbox" wire:model="allow_backorder"
                        class="mt-0.5 rounded border-neutral-border text-accent-600 focus:ring-accent-500" />
                    <div>
                        <p class="text-sm font-medium text-ink">{{ __('merchant_panel.allow_backorder') }}</p>
                        <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.allow_backorder_desc') }}</p>
                    </div>
                </label>

                <label
                    class="flex items-start gap-3 p-4 rounded-xl border transition-all cursor-pointer
                    {{ $show_out_of_stock ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10' : 'border-neutral-border dark:border-dark-border' }}">
                    <input type="checkbox" wire:model="show_out_of_stock"
                        class="mt-0.5 rounded border-neutral-border text-accent-600 focus:ring-accent-500" />
                    <div>
                        <p class="text-sm font-medium text-ink">{{ __('merchant_panel.show_out_of_stock') }}</p>
                        <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.show_out_of_stock_desc') }}</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Languages --}}
        <div class="edz-card edz-card--padded mb-6">
            <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                <ion-icon name="language-outline" class="text-lg text-accent-500"></ion-icon>
                {{ __('merchant_panel.languages') }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="edz-label">{{ __('merchant_panel.default_language') }}</label>
                    <select wire:model="language" class="edz-input">
                        <option value="ar">{{ __('merchant_panel.arabic') }}</option>
                        <option value="fr">{{ __('merchant_panel.french') }}</option>
                        <option value="en">{{ __('merchant_panel.english') }}</option>
                        <option value="es">{{ __('merchant_panel.spanish') }}</option>
                    </select>
                </div>

                <div>
                    <label class="edz-label">{{ __('merchant_panel.supported_languages') }}</label>
                    <div class="flex flex-wrap gap-2 mt-2" x-data>
                        @foreach (['ar' => __('merchant_panel.arabic'), 'fr' => __('merchant_panel.french'), 'en' => __('merchant_panel.english'), 'es' => __('merchant_panel.spanish')] as $code => $label)
                            <label
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm cursor-pointer transition"
                                :class="$wire.supported_languages.includes('{{ $code }}') ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10 text-accent-700 dark:text-accent-400' : 'border-neutral-border dark:border-dark-border text-ink-muted hover:border-neutral-border'">
                                <input type="checkbox" value="{{ $code }}"
                                    wire:model.live="supported_languages" class="sr-only" />
                                <span
                                    class="w-2 h-2 rounded-full"
                                    :class="$wire.supported_languages.includes('{{ $code }}') ? 'bg-accent-500' : 'bg-neutral-border dark:bg-dark-border'"></span>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-ink-muted mt-2">{{ __('merchant_panel.supported_languages_desc') }}</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="edz-btn edz-btn--primary">
                <ion-icon name="save-outline" class="w-4 h-4 me-1"></ion-icon>
                {{ __('buttons.save') }}
            </button>
        </div>
    </form>
</div>
