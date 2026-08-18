<?php

use App\Enums\Store\LandingTemplateEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use Illuminate\Support\Facades\Validator;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.merchant');

state([
    'landing_template' => 'single_product',
]);

mount(function (): void {
    $store = currentStore();
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $this->landing_template = $store->landing_template?->value ?? LandingTemplateEnum::SINGLE_PRODUCT->value;
});

$save = function () {
    Validator::make(
        ['landing_template' => $this->landing_template],
        ['landing_template' => 'required|string|in:' . implode(',', array_column(LandingTemplateEnum::cases(), 'value'))]
    )->validate();

    $store = currentStore();
    abort_unless(canStore(StorePermissionEnum::STORE_UPDATE->value), 403);

    $store->update(['landing_template' => $this->landing_template]);

    session()->flash('success', __('Storefront template updated successfully'));
};
?>

<div>
    <x-edz.page-header
        title="{{ __('Storefront Template') }}"
        description="{{ __('Choose how your public store looks') }}">
    </x-edz.page-header>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach(\App\Enums\Store\LandingTemplateEnum::options() as $option)
                <label class="cursor-pointer">
                    <input type="radio" wire:model="landing_template" value="{{ $option['value'] }}" class="peer sr-only">
                    <div class="border-2 rounded-xl p-6 text-center peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/30 border-gray-200 dark:border-gray-600 transition">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ $option['label'] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $option['description'] }}</p>
                    </div>
                </label>
            @endforeach
        </div>

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg transition">
            {{ __('Save Template') }}
        </button>
    </form>
</div>
