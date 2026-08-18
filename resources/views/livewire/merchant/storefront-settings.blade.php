<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

$store = null;
$template = '';
$sections = [];

state([
    'template' => '',
    'sections' => [],
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->store = currentStore();
    abort_unless($this->store, 404);
    $this->template = $this->store->landing_template ?? 'single-product';
    $theme = $this->store->theme;
    $this->sections = $theme?->homepage_sections ?? ['hero', 'social_proof', 'faq', 'cta'];
});

$save = function (): void {
    $store = currentStore();
    abort_unless($store, 404);

    $store->update(['landing_template' => $this->template]);

    $theme = $store->theme;
    if ($theme) {
        $theme->update(['homepage_sections' => $this->sections]);
    } else {
        $store->theme()->create(['homepage_sections' => $this->sections]);
    }

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.template_updated'));
};

?>

<div>
    @php
        $availableSections = [
            'hero'         => ['label' => 'Hero Section', 'description' => 'The main banner/hero area at the top'],
            'social_proof' => ['label' => 'Social Proof', 'description' => 'Trust badges (Secure Payment, Fast Delivery, Easy Returns)'],
            'faq'          => ['label' => 'FAQ Section', 'description' => 'Frequently Asked Questions accordion'],
            'cta'          => ['label' => 'Call to Action', 'description' => 'Final CTA section with order button'],
            'categories'   => ['label' => 'Categories', 'description' => 'Category filter pills (catalog/brand templates)'],
            'brands'       => ['label' => 'Brands Filter', 'description' => 'Brand filter pills (brand template)'],
            'description'  => ['label' => 'Product Description', 'description' => 'Full product description section (single-product template)'],
        ];
    @endphp

    <x-edz.page-header
        title="{{ __('merchant_panel.storefront_template') }}"
        description="{{ __('merchant_panel.storefront_template_desc') }}">
    </x-edz.page-header>

    @if ($store?->isPubliclyActive())
        <div class="mb-6 p-4 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <x-edz.icon name="external-link" class="w-5 h-5 text-accent-600 dark:text-accent-400" />
                    <div>
                        <p class="text-sm font-medium text-accent-700 dark:text-accent-300">{{ __('storefront.your_store_link') }}</p>
                        <p class="text-xs text-accent-600 dark:text-accent-400 font-mono">{{ $store->public_url }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        x-data="{ copied: false }"
                        x-on:click="
                            navigator.clipboard.writeText('{{ $store->public_url }}');
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        "
                        class="edz-btn edz-btn--secondary edz-btn--sm"
                    >
                        <x-edz.icon name="copy" class="w-4 h-4 me-1" />
                        <span x-text="copied ? '{{ __('buttons.copied') }}' : '{{ __('buttons.copy_link') }}'"></span>
                    </button>
                    <a href="{{ $store->public_url }}" target="_blank" rel="noopener noreferrer"
                       class="edz-btn edz-btn--primary edz-btn--sm">
                        <x-edz.icon name="external-link" class="w-4 h-4 me-1" />
                        {{ __('storefront.visit_store') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="save" x-data="edzDirty()">
        {{-- Template Selection --}}
        <div class="edz-card edz-card--padded space-y-6 mb-6">
            <h3 class="text-sm font-semibold text-ink">{{ __('merchant_panel.storefront_template') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach (['single-product' => __('merchant_panel.template_single'), 'catalog' => __('merchant_panel.template_catalog'), 'brand' => __('merchant_panel.template_brand')] as $key => $label)
                    <label class="edz-card edz-card--padded cursor-pointer border-2 transition-all duration-200 @if ($template === $key) border-accent-500 bg-accent-50 dark:bg-accent-900/10 @else border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 @endif">
                        <input type="radio" name="template" value="{{ $key }}" wire:model.live="template" class="sr-only" />
                        <div class="text-center py-4">
                            <p class="font-semibold text-ink">{{ $label }}</p>
                            @if ($store?->isPubliclyActive())
                                <a href="{{ $store->public_url }}" target="_blank" rel="noopener noreferrer"
                                   class="mt-2 inline-flex items-center text-xs text-accent-600 hover:text-accent-500 dark:text-accent-400 dark:hover:text-accent-300">
                                    <x-edz.icon name="eye" class="w-3 h-3 me-1" />
                                    {{ __('storefront.preview') }}
                                </a>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Homepage Sections --}}
        <div class="edz-card edz-card--padded space-y-6 mb-6">
            <div>
                <h3 class="text-sm font-semibold text-ink">{{ __('merchant_panel.homepage_sections') }}</h3>
                <p class="text-xs text-ink-400 mt-1">{{ __('merchant_panel.homepage_sections_desc') }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($availableSections as $key => $section)
                    <label class="flex items-start gap-3 p-3 rounded-lg border transition-all duration-200 cursor-pointer
                        {{ in_array($key, $sections) ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        <input type="checkbox" name="sections[]" value="{{ $key }}"
                               wire:model.live="sections"
                               class="mt-0.5 rounded border-gray-300 text-accent-600 focus:ring-accent-500" />
                        <div>
                            <p class="text-sm font-medium text-ink">{{ $section['label'] }}</p>
                            <p class="text-xs text-ink-400">{{ $section['description'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="edz-btn edz-btn--primary">
                {{ __('merchant_panel.save_template') }}
            </button>
        </div>
    </form>
</div>
