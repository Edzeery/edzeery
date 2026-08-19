<?php

use App\Domains\Cart\Services\CartService;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'items' => [],
    'subtotal' => 0,
    'count' => 0,
    'alignment' => 'right-0',
]);

mount(function (): void {
    $this->refreshCart();
});

$refreshCart = function () {
    $cart = app(CartService::class);
    $storeId = currentStoreId();

    $this->items = $cart->getItems($storeId)->toArray();
    $this->subtotal = $cart->getSubtotal($storeId);
    $this->count = $cart->getCount($storeId);
};

$removeItem = function (string $variantId) {
    app(CartService::class)->removeItem(currentStoreId(), $variantId);
    $this->refreshCart();
};

$updateQty = function (string $variantId, int $qty) {
    app(CartService::class)->updateQuantity(currentStoreId(), $variantId, $qty);
    $this->refreshCart();
};

?>

<div x-data="{ open: false }" class="relative" wire:poll.5s="refreshCart" x-on:cart-updated.window="$wire.refreshCart()">
    {{-- Trigger --}}
    <button x-on:click="open = !open"
        class="relative p-2.5 sm:p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white min-h-[44px] min-w-[44px] flex items-center justify-center">
        <ion-icon name="cart-outline" class="text-2xl"></ion-icon>
        @if ($count > 0)
            <span
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                {{ $count }}
            </span>
        @endif
    </button>

    {{-- Slide-over — bottom sheet on mobile, dropdown on desktop --}}
    <div x-show="open" x-on:click.away="open = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 sm:static sm:z-auto">
        {{-- Backdrop (mobile only) --}}
        <div x-show="open" class="fixed inset-0 bg-black/40 sm:hidden" x-on:click="open = false" x-transition.opacity></div>
        {{-- Panel --}}
        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:opacity-0"
            x-transition:enter-end="translate-y-0 sm:opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 sm:opacity-100"
            x-transition:leave-end="translate-y-full sm:opacity-0"
            class="fixed bottom-0 inset-x-0 sm:absolute sm:top-full sm:{{ $alignment }} sm:mt-2 sm:w-80 max-h-[85vh] bg-white dark:bg-gray-800 sm:rounded-xl rounded-t-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            {{-- Drag handle (mobile only) --}}
            <div class="sm:hidden flex justify-center pt-3 pb-1">
                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></div>
            </div>
            <div class="p-4 max-h-[80vh] sm:max-h-[400px] overflow-y-auto">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center justify-between">
                <span>{{ __('storefront.cart') }}</span>
                <span class="text-sm font-normal text-gray-500">{{ $count }} {{ __('storefront.items') }}</span>
            </h3>

            @if ($count === 0)
                <div class="py-8 text-center">
                    <ion-icon name="bag-outline" class="text-5xl text-gray-300 dark:text-gray-600 mb-3"></ion-icon>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('storefront.your_cart_is_empty') }}</p>
                    <button x-on:click="open = false"
                        class="mt-3 text-sm font-medium store-text-primary hover:underline">{{ __('storefront.back_to_store') }}</button>
                </div>
            @else
                <div class="space-y-3 max-h-60 overflow-y-auto">
                    @foreach ($items as $item)
                        <div class="flex items-center gap-3 text-sm">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white truncate">
                                    {{ $item['product_name'] }}</p>
                                @if ($item['variant_name'])
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['variant_name'] }}</p>
                                @endif
                                <div class="flex items-center gap-2 mt-1">
                                    <button
                                        wire:click="updateQty('{{ $item['variant_id'] }}', {{ $item['quantity'] - 1 }})"
                                        class="w-8 h-8 sm:w-6 sm:h-6 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">-</button>
                                    <span class="text-gray-700 dark:text-gray-200">{{ $item['quantity'] }}</span>
                                    <button
                                        wire:click="updateQty('{{ $item['variant_id'] }}', {{ $item['quantity'] + 1 }})"
                                        class="w-8 h-8 sm:w-6 sm:h-6 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">+</button>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ currency($item['price'] * $item['quantity']) }}</p>
                                <button x-data
                                    @click.prevent="if (await EdzSwal.confirmAction('{{ __('storefront.remove') }}', '{{ __('messages.action_confirm_delete') }}')) $wire.removeItem('{{ $item['variant_id'] }}')"
                                    class="text-xs text-red-500 hover:text-red-700 mt-1">{{ __('storefront.remove') }}</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 mt-3 pt-3">
                    <div class="flex justify-between font-semibold text-gray-900 dark:text-white">
                        <span>{{ __('storefront.subtotal') }}</span>
                        <span>{{ currency($subtotal) }}</span>
                    </div>
                    <a href="{{ route('storefront.checkout', ['store' => currentStore()->slug]) }}"
                        class="mt-3 block w-full text-center store-btn-primary text-white font-semibold py-2.5 px-4 rounded-lg transition">
                        {{ __('storefront.checkout') }}
                    </a>
                </div>
            @endif
        </div>
        </div>
    </div>
</div>
