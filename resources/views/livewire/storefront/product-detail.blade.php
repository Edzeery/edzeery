<?php

use App\Domains\Cart\Services\CartService;
use App\Models\Products\Product;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'product' => null,
]);

mount(function (Product $product): void {
    $this->product = $product->load(['images', 'variants.optionValues.option', 'brand', 'categories', 'store']);
});

$addToCart = function (string $variantId = null) {
    $cartService = app(CartService::class);
    $storeId = currentStoreId();

    if ($variantId) {
        $cartService->addItem($storeId, $variantId, 1);
    } elseif ($this->product->variants->count() === 1) {
        $cartService->addItem($storeId, $this->product->variants->first()->id, 1);
    }

    $this->dispatch('swal', type: 'success', title: __('storefront.added_to_cart'));
    $this->dispatch('cart-updated');
};
?>

<div>
    @if($product)
    <div class="max-w-6xl mx-auto">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-8">
            <a href="/" class="hover:text-gray-700 dark:hover:text-gray-200 transition">{{ $product->store->name ?? __('storefront.back_to_store') }}</a>
            <span>/</span>
            @if($product->categories->first())
                <span class="hover:text-gray-700 dark:hover:text-gray-200 transition">{{ $product->categories->first()->name }}</span>
                <span>/</span>
            @endif
            <span class="text-gray-900 dark:text-white">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {{-- Images --}}
            <div x-data="productGallery()" class="space-y-4" dir="ltr">
                @if($product->images->count())
                    {{-- Main Image --}}
                    <div class="relative aspect-square rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 group cursor-zoom-in"
                         @click="openLightbox()" role="region" aria-label="{{ __('storefront.product_images') ?? 'Product images' }}">
                        @foreach($product->images as $i => $img)
                            <img
                                x-show="active === {{ $i }}"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-105"
                                x-transition:enter-end="opacity-100 scale-100"
                                src="{{ asset('storage/' . $img->path) }}"
                                alt="{{ $product->name }} — {{ $i + 1 }}"
                                class="w-full h-full object-cover absolute inset-0"
                                onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'"
                                draggable="false"
                            >
                        @endforeach

                        {{-- Counter --}}
                        <div class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm text-white text-xs font-medium px-2.5 py-1 rounded-full z-10">
                            <span x-text="active + 1"></span>/<span>{{ $product->images->count() }}</span>
                        </div>

                        {{-- Navigation Arrows --}}
                        @if($product->images->count() > 1)
                            <button type="button" x-on:click.stop="prev()"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm text-gray-700 dark:text-gray-200 shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-200 flex items-center justify-center hover:bg-white dark:hover:bg-gray-900 z-10"
                                aria-label="Previous image">
                                <ion-icon name="chevron-back" class="text-xl"></ion-icon>
                            </button>
                            <button type="button" x-on:click.stop="next()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm text-gray-700 dark:text-gray-200 shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-200 flex items-center justify-center hover:bg-white dark:hover:bg-gray-900 z-10"
                                aria-label="Next image">
                                <ion-icon name="chevron-forward" class="text-xl"></ion-icon>
                            </button>
                        @endif
                    </div>

                    {{-- Thumbnails --}}
                    @if($product->images->count() > 1)
                        <div class="flex gap-2.5 overflow-x-auto pb-1 scrollbar-hide" x-ref="thumbs">
                            @foreach($product->images as $i => $img)
                                <button
                                    type="button"
                                    x-on:click="goTo({{ $i }})"
                                    :class="active === {{ $i }}
                                        ? 'ring-2 ring-offset-2 store-border-primary ring-offset-white dark:ring-offset-gray-900 opacity-100'
                                        : 'ring-1 ring-gray-200 dark:ring-gray-700 opacity-60 hover:opacity-90'"
                                    class="shrink-0 w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden transition-all duration-200"
                                >
                                    <img src="{{ asset('storage/' . $img->path) }}" alt="" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'" draggable="false">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="aspect-square rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('img/icons/noimg.png') }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-8 opacity-60">
                    </div>
                @endif

                {{-- Lightbox --}}
                <div x-show="lightbox" x-transition.opacity class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-center justify-center" @click.self="lightbox = false" @keydown.escape.window="lightbox = false" @keydown.left.window="if(lightbox) prev()" @keydown.right.window="if(lightbox) next()" style="display:none">
                    <button type="button" @click="lightbox = false" class="absolute top-4 right-4 text-white/70 hover:text-white transition" aria-label="Close">
                        <ion-icon name="close" class="text-3xl"></ion-icon>
                    </button>
                    @if($product->images->count() > 1)
                        <button type="button" x-on:click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition" aria-label="Previous">
                            <ion-icon name="chevron-back" class="text-2xl"></ion-icon>
                        </button>
                        <button type="button" x-on:click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition" aria-label="Next">
                            <ion-icon name="chevron-forward" class="text-2xl"></ion-icon>
                        </button>
                    @endif
                    @foreach($product->images as $i => $img)
                        <img x-show="active === {{ $i }}" x-transition src="{{ asset('storage/' . $img->path) }}" alt="{{ $product->name }}" class="max-w-[90vw] max-h-[85vh] object-contain rounded-lg" onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'" draggable="false">
                    @endforeach
                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-black/50 backdrop-blur-sm text-white text-sm px-3 py-1.5 rounded-full">
                        <span x-text="active + 1"></span> / {{ $product->images->count() }}
                    </div>
                </div>
            </div>

            {{-- Info --}}
            <div class="flex flex-col">
                @if($product->brand)
                    <span class="text-sm font-semibold store-text-primary uppercase tracking-wider mb-2">{{ $product->brand->name }}</span>
                @endif

                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ $product->name }}</h1>

                <div class="mb-6">
                    <span class="text-3xl font-bold store-text-primary">{{ currency($product->price) }}</span>
                    @if($product->variants->count() === 1 && $product->variants->first()->compare_price)
                        <span class="text-lg text-gray-400 dark:text-gray-500 line-through ml-3">{{ currency($product->variants->first()->compare_price) }}</span>
                    @endif
                </div>

                @if($product->short_description)
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6">{{ $product->short_description }}</p>
                @endif

                {{-- Variants --}}
                @if($product->variants->count() > 1)
                    <div class="mb-6" x-data="{ selected: '{{ $product->variants->first()?->id }}' }">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('storefront.options') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->variants as $variant)
                                <button
                                    type="button"
                                    x-on:click="selected = '{{ $variant->id }}'"
                                    :class="selected === '{{ $variant->id }}' ? 'store-border-primary store-bg-primary-soft store-text-primary ring-1' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'"
                                    class="border-2 rounded-lg px-5 py-2.5 text-sm font-medium transition ring-transparent"
                                >
                                    {{ $variant->name }}
                                    <span class="ml-1 text-xs opacity-70">{{ currency($variant->price) }}</span>
                                </button>
                            @endforeach
                        </div>

                        <button
                            type="button"
                            x-on:click="$wire.addToCart(selected)"
                            class="mt-6 w-full sm:w-auto store-btn-primary text-white font-bold py-3.5 px-10 rounded-lg transition text-base flex items-center justify-center gap-2"
                        >
                            <ion-icon name="cart-outline" class="text-xl"></ion-icon>
                            {{ __('storefront.add_to_cart') }}
                        </button>
                    </div>
                @else
                    <button
                        type="button"
                        wire:click="addToCart(null)"
                        class="w-full sm:w-auto store-btn-primary text-white font-bold py-3.5 px-10 rounded-lg transition text-base flex items-center justify-center gap-2"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                    >
                        <ion-icon name="cart-outline" class="text-xl"></ion-icon>
                        <span wire:loading.remove>{{ __('storefront.add_to_cart') }}</span>
                        <span wire:loading>{{ __('storefront.placing') }}</span>
                    </button>
                @endif

                {{-- Trust signals --}}
                <div class="mt-10 pt-8 border-t border-gray-200 dark:border-gray-700 grid grid-cols-3 gap-4">
                    <div class="flex flex-col items-center text-center">
                        <ion-icon name="shield-checkmark-outline" class="text-2xl store-text-primary mb-1"></ion-icon>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('storefront.secure_payment') }}</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <ion-icon name="car-outline" class="text-2xl store-text-primary mb-1"></ion-icon>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('storefront.fast_delivery') }}</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <ion-icon name="refresh-outline" class="text-2xl store-text-primary mb-1"></ion-icon>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('storefront.easy_returns') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        @if($product->description)
        <div class="mt-16 pt-12 border-t border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('storefront.product_details') }}</h2>
            <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>
        @endif
    </div>
    @else
    <div class="text-center py-20">
        <ion-icon name="bag-outline" class="text-6xl text-gray-300 dark:text-gray-600 mb-4"></ion-icon>
        <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('storefront.product_not_found') }}</p>
        <a href="/" class="mt-4 inline-block store-btn-primary text-white font-semibold py-2.5 px-6 rounded-lg transition">
            {{ __('storefront.back_to_store') }}
        </a>
    </div>
    @endif
</div>
