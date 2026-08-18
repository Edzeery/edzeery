<?php

use App\Models\Products\Product;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'product' => null,
]);

mount(function (Product $product): void {
    $this->product = $product->load(['images', 'variants.optionValues.option', 'brand', 'categories']);
});
?>

<div>
    @if($product)
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-300">{{ $product->description }}</p>
    @else
        <p>{{ __('Product not found') }}</p>
    @endif
</div>
