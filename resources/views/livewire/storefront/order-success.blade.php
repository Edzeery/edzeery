<?php

use App\Models\Orders\Order;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;

layout('components.layouts.storefront');

mount(function (string $order): void {
    $this->orderNumber = $order;
});

$props = ['orderNumber' => ''];
?>

<div class="max-w-lg mx-auto text-center py-16">
    <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
        <ion-icon name="checkmark-outline" class="text-3xl text-green-600 dark:text-green-400"></ion-icon>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __('Order Placed Successfully') }}</h1>
    <p class="text-gray-600 dark:text-gray-300 mb-6">
        {{ __('Your order number is') }}: <span class="font-mono font-bold">{{ $orderNumber }}</span>
    </p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">{{ __('We will contact you soon to confirm your order.') }}</p>
    <a href="/" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition">
        <ion-icon name="arrow-back-outline"></ion-icon>
        {{ __('Back to Store') }}
    </a>
</div>
