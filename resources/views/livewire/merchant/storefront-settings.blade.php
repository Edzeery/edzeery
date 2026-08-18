<?php

use App\Models\Stores\Store;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

$store = null;
$template = '';

state([
    'template' => '',
]);

mount(function (): void {
    $this->store = currentStore();
    abort_unless($this->store, 404);
    $this->template = $this->store->landing_template ?? 'single-product';
});

$save = function (): void {
    $store = currentStore();
    abort_unless($store, 404);

    $store->update(['landing_template' => $this->template]);

    session()->flash('success', __('merchant_panel.template_updated'));
};
?>

<div>
    <x-edz.page-header
        title="{{ __('merchant_panel.storefront_template') }}"
        description="{{ __('merchant_panel.storefront_template_desc') }}">
    </x-edz.page-header>

    @if (session('success'))
        <div class="mb-6 p-4 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg text-success-700 dark:text-success-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save">
        <div class="edz-card edz-card--padded space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach (['single-product' => __('merchant_panel.template_single'), 'catalog' => __('merchant_panel.template_catalog'), 'brand' => __('merchant_panel.template_brand')] as $key => $label)
                    <label class="edz-card edz-card--padded cursor-pointer border-2 transition-all duration-200 @if ($template === $key) border-accent-500 bg-accent-50 dark:bg-accent-900/10 @else border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 @endif">
                        <input type="radio" name="template" value="{{ $key }}" wire:model.live="template" class="sr-only" />
                        <div class="text-center py-4">
                            <p class="font-semibold text-ink">{{ $label }}</p>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="edz-btn edz-btn--primary">
                    {{ __('merchant_panel.save_template') }}
                </button>
            </div>
        </div>
    </form>
</div>
