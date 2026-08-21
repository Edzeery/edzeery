<?php

use App\Domains\Cart\Services\CartService;
use App\Models\Products\ProductVariant;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'items' => [],
    'subtotal' => 0,
    'count' => 0,
]);

mount(function (): void {
    $this->refreshCart();
});

$refreshCart = function () {
    $cart = app(CartService::class);
    $storeId = currentStoreId();

    $items = $cart->getItems($storeId)->toArray();
    $this->subtotal = $cart->getSubtotal($storeId);
    $this->count = $cart->getCount($storeId);

    if (!empty($items)) {
        $variantIds = array_column($items, 'variant_id');
        $variants = ProductVariant::with('product.images')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        foreach ($items as &$item) {
            $variant = $variants[$item['variant_id']] ?? null;
            $product = $variant?->product;
            $firstImage = $product?->images?->first()?->path;
            $item['image'] = $firstImage
                ? asset('storage/' . $firstImage)
                : asset('img/icons/noimg.png');
            $item['slug'] = $product?->slug ?? '';
        }
        unset($item);
    }

    $this->items = $items;
};

$removeItem = function (string $variantId) {
    app(CartService::class)->removeItem(currentStoreId(), $variantId);
    $this->refreshCart();
};

$clearCart = function () {
    app(CartService::class)->clear(currentStoreId());
    $this->refreshCart();
};

$updateQty = function (string $variantId, int $qty) {
    app(CartService::class)->updateQuantity(currentStoreId(), $variantId, $qty);
    $this->refreshCart();
};
?>

<div x-data="{
    open: false,
    init() {
        window.addEventListener('cart-updated', () => { this.$wire.refreshCart(); });
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.open) { this.open = false; }
        });
        this._pollInterval = setInterval(() => {
            if (document.visibilityState === 'visible') { this.$wire.refreshCart(); }
        }, 30000);
    },
    destroy() { clearInterval(this._pollInterval); },
    toggle() { this.open = !this.open; },
    listen() { window.dispatchEvent(new Event('cart-updated')); }
}" class="relative">

    {{-- Trigger Button --}}
    <button x-on:click="toggle()"
        class="relative p-2.5 rounded-lg text-gray-600 dark:text-gray-300
               hover:bg-gray-100 dark:hover:bg-gray-700
               transition-colors duration-150 min-h-[44px] min-w-[44px]
               flex items-center justify-center"
        aria-label="{{ __('storefront.cart') }}">
        <ion-icon name="cart-outline" class="text-[22px] leading-none"></ion-icon>
        @if ($count > 0)
            <span class="absolute -top-0.5 -end-0.5 store-bg-primary text-white
                         text-[10px] font-bold leading-none
                         min-w-[18px] h-[18px] px-1
                         flex items-center justify-center rounded-full
                         ring-2 ring-white dark:ring-gray-800">
                {{ $count > 99 ? '99+' : $count }}
            </span>
        @endif
    </button>

    {{-- Backdrop --}}
    <div x-show="open"
        x-on:click="open = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60]"
        style="display: none;">
    </div>

    {{-- Sidebar Panel --}}
    <div x-show="open"
        @if (app()->getLocale() === 'ar')
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-[70] w-full sm:w-[420px] bg-white dark:bg-gray-900
                   shadow-2xl shadow-black/20 flex flex-col"
        @else
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-[70] w-full sm:w-[420px] bg-white dark:bg-gray-900
                   shadow-2xl shadow-black/20 flex flex-col"
        @endif
        style="display: none;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full store-bg-primary flex items-center justify-center">
                    <ion-icon name="cart-outline" class="text-white text-lg"></ion-icon>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('storefront.cart') }}</h2>
                    @if ($count > 0)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $count }} {{ __('storefront.items') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-1">
                @if ($count > 0)
                    <button x-data
                            x-on:click.prevent="if (await EdzSwal.confirmAction(@js(__('storefront.clear_cart')), @js(__('storefront.clear_cart_confirm')))) $wire.clearCart()"
                            class="w-9 h-9 rounded-full flex items-center justify-center
                                   text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400
                                   hover:bg-red-50 dark:hover:bg-red-900/20
                                   transition-colors duration-150"
                            aria-label="{{ __('storefront.clear_cart') }}">
                        <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                    </button>
                @endif
                <button x-on:click="open = false"
                    class="w-9 h-9 rounded-full flex items-center justify-center
                           text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-200
                           hover:bg-gray-100 dark:hover:bg-gray-700
                           transition-colors duration-150"
                    aria-label="Close">
                    <ion-icon name="close-outline" class="text-xl"></ion-icon>
                </button>
            </div>
        </div>

        {{-- Content --}}
        @if ($count === 0)
            {{-- Empty State --}}
            <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 text-center">
                <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-5">
                    <ion-icon name="bag-outline" class="text-4xl text-gray-300 dark:text-gray-600"></ion-icon>
                </div>
                <p class="text-base font-medium text-gray-900 dark:text-white mb-1.5">
                    {{ __('storefront.your_cart_is_empty') }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-[240px]">
                    {{ __('storefront.review_cart') }}
                </p>
                <button x-on:click="open = false"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                           store-btn-primary text-white text-sm font-semibold
                           transition-all duration-150 hover:shadow-lg">
                    <ion-icon name="bag-handle-outline" class="text-base"></ion-icon>
                    {{ __('storefront.back_to_store') }}
                </button>
            </div>
        @else
            {{-- Items List --}}
            <div class="flex-1 overflow-y-auto overscroll-contain">
                <div class="px-5 py-3 divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($items as $item)
                        <div wire:key="cart-item-{{ $item['variant_id'] }}"
                             class="flex gap-3.5 py-4 first:pt-0 last:pb-0">

                            {{-- Product Image --}}
                                        <a href="{{ route('storefront.product', ['store' => currentStore()?->slug ?? '', 'product' => $item['slug']]) }}"
                               x-on:click="open = false"
                               class="shrink-0 w-[72px] h-[72px] rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:opacity-80 transition-opacity">
                                <img src="{{ $item['image'] }}"
                                     alt="{{ $item['product_name'] }}"
                                     class="w-full h-full object-cover"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'">
                            </a>

                            {{-- Product Details --}}
                            <div class="flex-1 min-w-0 flex flex-col">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                            <a href="{{ route('storefront.product', ['store' => currentStore()?->slug ?? '', 'product' => $item['slug']]) }}"
                                           x-on:click="open = false"
                                           class="block text-sm font-medium text-gray-900 dark:text-white leading-snug line-clamp-2 hover:text-[var(--store-primary)] transition-colors">
                                            {{ $item['product_name'] }}
                                        </a>
                                        @if ($item['variant_name'])
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $item['variant_name'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <button x-data
                                            x-on:click.prevent="if (await EdzSwal.confirmAction(@js(__('storefront.remove')), @js(__('messages.action_confirm_delete')))) $wire.removeItem('{{ $item['variant_id'] }}')"
                                            class="shrink-0 p-1 rounded-lg text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                            aria-label="{{ __('storefront.remove') }}">
                                        <ion-icon name="trash-outline" class="text-base"></ion-icon>
                                    </button>
                                </div>

                                {{-- Price + Quantity --}}
                                <div class="flex items-center justify-between mt-auto pt-2">
                                    <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                                        <button wire:click="updateQty('{{ $item['variant_id'] }}', {{ $item['quantity'] - 1 }})"
                                                wire:loading.attr="disabled"
                                                wire:loading.class="opacity-40"
                                                :disabled="{{ $item['quantity'] <= 1 ? 'true' : 'false' }}"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-50 dark:bg-gray-800
                                                       text-gray-600 dark:text-gray-400
                                                       hover:bg-gray-100 dark:hover:bg-gray-700
                                                       transition-colors disabled:opacity-30 disabled:cursor-not-allowed
                                                       text-sm font-medium select-none">
                                            &minus;
                                        </button>
                                        <span class="w-8 text-center text-sm font-semibold text-gray-900 dark:text-white tabular-nums select-none">
                                            {{ $item['quantity'] }}
                                        </span>
                                        <button wire:click="updateQty('{{ $item['variant_id'] }}', {{ $item['quantity'] + 1 }})"
                                                wire:loading.attr="disabled"
                                                wire:loading.class="opacity-40"
                                                :disabled="{{ $item['max_stock'] && $item['quantity'] >= $item['max_stock'] ? 'true' : 'false' }}"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-50 dark:bg-gray-800
                                                       text-gray-600 dark:text-gray-400
                                                       hover:bg-gray-100 dark:hover:bg-gray-700
                                                       transition-colors disabled:opacity-30 disabled:cursor-not-allowed
                                                       text-sm font-medium select-none">
                                            &plus;
                                        </button>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white tabular-nums">
                                        {{ currency($item['price'] * $item['quantity']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Footer --}}
            <div class="shrink-0 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-5 py-4">
                {{-- Subtotal --}}
                <div class="flex justify-between items-center mb-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('storefront.subtotal') }}</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white tabular-nums">
                        {{ currency($subtotal) }}
                    </span>
                </div>

                {{-- Checkout Button --}}
                <a href="{{ route('storefront.checkout', ['store' => currentStore()?->slug ?? '']) }}"
                   x-on:click="open = false"
                   class="block w-full text-center py-3 px-4 rounded-xl
                          store-btn-primary text-white font-bold text-sm
                          min-h-[48px] flex items-center justify-center gap-2
                          transition-all duration-150 hover:shadow-lg hover:brightness-110">
                    <ion-icon name="lock-closed-outline" class="text-base"></ion-icon>
                    {{ __('storefront.checkout') }}
                </a>

                <p class="text-center text-[11px] text-gray-400 dark:text-gray-500 mt-2.5">
                    <ion-icon name="shield-checkmark-outline" class="align-text-bottom text-xs"></ion-icon>
                    {{ __('storefront.secure_checkout') }}
                </p>
            </div>
        @endif
    </div>
</div>
