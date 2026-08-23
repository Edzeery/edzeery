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
    'favicon' => null,
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
    abort_unless(canStore(\App\Enums\Store\StorePermissionEnum::STORE_UPDATE->value), 403);

    $store = currentStore();
    abort_unless($store, 404);

    $this->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'logo' => 'nullable|image|max:2048',
        'cover' => 'nullable|image|max:4096',
        'favicon' => 'nullable|file|max:256|mimes:png,svg',
        'supported_languages' => 'nullable|array',
        'supported_languages.*' => 'in:ar,fr,en,es',
        'language' => 'required|in:ar,fr,en,es',
    ]);

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

    if ($this->favicon instanceof TemporaryUploadedFile) {
        $faviconPath = $this->favicon->store('stores', 'public');
        $store->seo()->updateOrCreate([], ['favicon' => $faviconPath]);
    }

    $settingsData = [
        'currency' => $this->currency,
        'language' => $this->language,
        'guest_checkout' => $this->guest_checkout,
        'inventory_tracking' => $this->inventory_tracking,
        'show_out_of_stock' => $this->show_out_of_stock,
        'allow_backorder' => $this->allow_backorder,
    ];

    if (Schema::hasColumn('store_settings', 'supported_languages')) {
        $supported = $this->supported_languages ?? [];
        // Ensure default language is in supported languages
        if (! in_array($this->language, $supported)) {
            $supported[] = $this->language;
        }
        $settingsData['supported_languages'] = array_unique($supported);
    }
    if (Schema::hasColumn('store_settings', 'phone')) {
        $settingsData['phone'] = $this->phone;
    }

    $store->settings()->updateOrCreate([], $settingsData);

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
};
?>

<div x-data="{ activeTab: 'branding' }">
    <x-edz.page-header title="{{ __('merchant_panel.store_settings') }}"
        description="{{ __('merchant_panel.store_settings_desc') }}">
    </x-edz.page-header>

    <form wire:submit="save" x-data="edzDirty()">

        <div class="flex flex-col lg:flex-row gap-6">

            {{-- Mobile: Horizontal Tabs --}}
            <div class="lg:hidden shrink-0">
                <div class="flex gap-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl overflow-x-auto">
                    @php
                        $tabs = [
                            'branding'   => ['icon' => 'color-palette', 'label' => __('merchant_panel.branding')],
                            'general'    => ['icon' => 'info-circle',   'label' => __('merchant_panel.general_info')],
                            'commerce'   => ['icon' => 'credit-card',   'label' => __('merchant_panel.commerce')],
                            'inventory'  => ['icon' => 'cube',          'label' => __('merchant_panel.inventory_group')],
                            'languages'  => ['icon' => 'language',      'label' => __('merchant_panel.languages')],
                        ];
                    @endphp
                    @foreach ($tabs as $tabKey => $tab)
                        <button type="button"
                            x-on:click="activeTab = '{{ $tabKey }}'"
                            class="flex items-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 flex-1 justify-center"
                            :class="activeTab === '{{ $tabKey }}'
                                ? 'bg-white dark:bg-gray-700 text-accent-600 dark:text-accent-400 shadow-sm'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'">
                            <x-edz.icon :name="$tab['icon']" class="w-4 h-4 shrink-0" />
                            <span>{{ $tab['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Desktop: Vertical Tabs --}}
            <div class="hidden lg:block w-64 shrink-0">
                <div class="edz-card p-2 sticky top-6">
                    <nav class="space-y-1">
                        @foreach ($tabs as $tabKey => $tab)
                            <button type="button"
                                x-on:click="activeTab = '{{ $tabKey }}'"
                                class="w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-start transition-all duration-200 group"
                                :class="activeTab === '{{ $tabKey }}'
                                    ? 'bg-accent-50 dark:bg-accent-900/15 text-accent-700 dark:text-accent-300'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-gray-900 dark:hover:text-gray-200'">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 transition-colors duration-200"
                                     :class="activeTab === '{{ $tabKey }}'
                                         ? 'bg-accent-100 dark:bg-accent-800/40'
                                         : 'bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 dark:group-hover:bg-gray-700'">
                                     <x-edz.icon :name="$tab['icon']" class="w-5 h-5" />
                                </div>
                                <span class="text-sm font-medium"
                                    :class="activeTab === '{{ $tabKey }}' ? 'text-accent-800 dark:text-accent-200' : ''">
                                    {{ $tab['label'] }}
                                </span>
                            </button>
                        @endforeach
                    </nav>
                </div>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0 space-y-6">

                {{-- Tab: Branding --}}
                <div x-show="activeTab === 'branding'" x-transition>
                    <div class="edz-card edz-card--padded">
                        <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                            <x-edz.icon name="color-palette" class="w-5 h-5 text-accent-500" />
                            {{ __('merchant_panel.branding') }}
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            {{-- Logo --}}
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.logo') }}</label>
                                <label class="flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border
                                              hover:border-accent-400 dark:hover:border-accent-500 cursor-pointer transition group bg-surface-secondary dark:bg-dark-secondary">
                                    @if ($logo)
                                        <div class="relative w-full h-full flex items-center justify-center p-2">
                                            <img src="{{ $logo->temporaryUrl() }}" class="max-h-full max-w-full object-contain rounded-lg">
                                            <button type="button" wire:click="$set('logo', null)"
                                                    class="absolute top-1 right-1 w-5 h-5 rounded-full bg-danger-500 text-white flex items-center justify-center text-xs hover:bg-danger-600 transition">
                                                <x-edz.icon name="x" class="w-3 h-3" />
                                            </button>
                                        </div>
                                    @elseif(currentStore()?->logo)
                                        <div class="relative w-full h-full flex items-center justify-center p-2">
                                            <img src="{{ asset('storage/' . currentStore()->logo) }}" class="max-h-full max-w-full object-contain rounded-lg">
                                        </div>
                                    @else
                                        <x-edz.icon name="image" class="text-2xl text-ink-soft group-hover:text-accent-500 transition mb-1" />
                                        <span class="text-xs text-ink-muted text-center px-2">Drag & drop logo</span>
                                        <span class="text-[10px] text-ink-soft mt-0.5">PNG, JPG up to 2MB</span>
                                    @endif
                                    <input type="file" wire:model="logo" accept="image/*" class="sr-only">
                                </label>
                                @error('logo')
                                    <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Cover --}}
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.cover') }}</label>
                                <label class="flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border
                                              hover:border-accent-400 dark:hover:border-accent-500 cursor-pointer transition group bg-surface-secondary dark:bg-dark-secondary">
                                    @if ($cover)
                                        <div class="relative w-full h-full flex items-center justify-center p-2">
                                            <img src="{{ $cover->temporaryUrl() }}" class="max-h-full max-w-full object-cover rounded-lg">
                                            <button type="button" wire:click="$set('cover', null)"
                                                    class="absolute top-1 right-1 w-5 h-5 rounded-full bg-danger-500 text-white flex items-center justify-center text-xs hover:bg-danger-600 transition">
                                                <x-edz.icon name="x" class="w-3 h-3" />
                                            </button>
                                        </div>
                                    @elseif(currentStore()?->cover)
                                        <div class="relative w-full h-full flex items-center justify-center p-2">
                                            <img src="{{ asset('storage/' . currentStore()->cover) }}" class="max-h-full max-w-full object-cover rounded-lg">
                                        </div>
                                    @else
                                        <x-edz.icon name="cloud-upload" class="text-2xl text-ink-soft group-hover:text-accent-500 transition mb-1" />
                                        <span class="text-xs text-ink-muted text-center px-2">Drag & drop cover</span>
                                        <span class="text-[10px] text-ink-soft mt-0.5">PNG, JPG up to 4MB</span>
                                    @endif
                                    <input type="file" wire:model="cover" accept="image/*" class="sr-only">
                                </label>
                                @error('cover')
                                    <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Favicon --}}
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1.5">{{ __('merchant_panel.favicon') }}</label>
                                <label class="flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border
                                              hover:border-accent-400 dark:hover:border-accent-500 cursor-pointer transition group bg-surface-secondary dark:bg-dark-secondary">
                                    @if ($favicon)
                                        <div class="relative w-full h-full flex items-center justify-center p-2">
                                            <img src="{{ $favicon->temporaryUrl() }}" class="max-h-full max-w-full object-contain rounded-lg">
                                            <button type="button" wire:click="$set('favicon', null)"
                                                    class="absolute top-1 right-1 w-5 h-5 rounded-full bg-danger-500 text-white flex items-center justify-center text-xs hover:bg-danger-600 transition">
                                                <x-edz.icon name="x" class="w-3 h-3" />
                                            </button>
                                        </div>
                                    @elseif(currentStore()?->seo?->favicon)
                                        <div class="relative w-full h-full flex items-center justify-center p-2">
                                            <img src="{{ asset('storage/' . currentStore()->seo->favicon) }}" class="max-h-full max-w-full object-contain rounded-lg">
                                        </div>
                                    @else
                                        <x-edz.icon name="globe" class="text-2xl text-ink-soft group-hover:text-accent-500 transition mb-1" />
                                        <span class="text-xs text-ink-muted text-center px-2">{{ __('merchant_panel.favicon') }}</span>
                                        <span class="text-[10px] text-ink-soft mt-0.5">PNG, SVG up to 256KB</span>
                                    @endif
                                    <input type="file" wire:model="favicon" accept="image/png,image/svg+xml" class="sr-only">
                                </label>
                                @error('favicon')
                                    <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab: General --}}
                <div x-show="activeTab === 'general'" x-transition>
                    <div class="edz-card edz-card--padded">
                        <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                            <x-edz.icon name="info-circle" class="w-5 h-5 text-accent-500" />
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
                </div>

                {{-- Tab: Commerce --}}
                <div x-show="activeTab === 'commerce'" x-transition>
                    <div class="edz-card edz-card--padded">
                        <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                            <x-edz.icon name="credit-card" class="w-5 h-5 text-accent-500" />
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
                                <label for="guest_checkout" class="edz-label mb-0">{{ __('merchant_panel.guest_checkout') }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab: Inventory --}}
                <div x-show="activeTab === 'inventory'" x-transition>
                    <div class="edz-card edz-card--padded">
                        <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                            <x-edz.icon name="cube" class="w-5 h-5 text-accent-500" />
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
                </div>

                {{-- Tab: Languages --}}
                <div x-show="activeTab === 'languages'" x-transition>
                    <div class="edz-card edz-card--padded">
                        <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                            <x-edz.icon name="language" class="w-5 h-5 text-accent-500" />
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
                                @error('language') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
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
                                @error('supported_languages') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                                <p class="text-xs text-ink-muted mt-2">{{ __('merchant_panel.supported_languages_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="flex justify-end">
                    <button type="submit" class="edz-btn edz-btn--primary">
                        <x-edz.icon name="save" class="w-4 h-4 me-1" />
                        {{ __('buttons.save') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
