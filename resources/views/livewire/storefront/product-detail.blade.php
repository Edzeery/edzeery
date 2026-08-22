<?php

use App\Domains\Cart\Services\CartService;
use App\Models\Products\Product;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'product' => null,
    'quantity' => 1,
    'selectedVariantId' => null,
    'relatedProducts' => [],
]);

mount(function (Product $product): void {
    $this->product = $product->load(['images', 'variants.optionValues.option', 'brand', 'categories', 'store']);

    if ($this->product->variants->count() === 1) {
        $this->selectedVariantId = $this->product->variants->first()->id;
    }

    $categoryIds = $this->product->categories->pluck('id');
    $brandId = $this->product->brand_id;

    $this->relatedProducts = Product::query()
        ->with(['images', 'variants'])
        ->where('id', '!=', $this->product->id)
        ->where(function ($query) use ($categoryIds, $brandId) {
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });

            if ($brandId) {
                $query->orWhere('brand_id', $brandId);
            }
        })
        ->where('is_active', true)
        ->latest()
        ->limit(8)
        ->get();
});

$addToCart = function (): void {
    if (! $this->product) {
        return;
    }

    $variantId = $this->selectedVariantId ?? $this->product->variants->first()?->id;

    if (! $variantId) {
        return;
    }

    $variant = $this->product->variants->firstWhere('id', $variantId);

    if (! $variant || (int) $variant->stock <= 0) {
        $this->dispatch('swal', type: 'error', title: __('storefront.out_of_stock'));
        return;
    }

    $quantity = max(1, min((int) $this->quantity, (int) $variant->stock));

    app(CartService::class)->addItem(currentStoreId(), $variantId, $quantity);

    $this->dispatch('cart-updated');
    $this->dispatch('swal', type: 'success', title: __('storefront.added_to_cart'));
};

$addRelatedToCart = function (string $variantId): void {
    app(CartService::class)->addItem(currentStoreId(), $variantId, 1);

    $this->dispatch('cart-updated');
    $this->dispatch('swal', type: 'success', title: __('storefront.added_to_cart'));
};

$incrementQuantity = function (): void {
    $variant = $this->product?->variants->firstWhere('id', $this->selectedVariantId ?? $this->product->variants->first()?->id);

    if (! $variant || (int) $variant->stock <= 0) {
        $this->quantity = 1;
        return;
    }

    $this->quantity = min((int) $this->quantity + 1, (int) $variant->stock);
};

$decrementQuantity = function (): void {
    $this->quantity = max(1, (int) $this->quantity - 1);
};
?>

<div>
    @if($this->product)
        @php
            $__maxComparePrice = (float) $this->product->variants->max('compare_price');
            $__minVariantPrice = (float) ($this->product->variants->min('price') ?? $this->product->price);
            $__imageDiscountPercent = ($__maxComparePrice > 0 && $__minVariantPrice > 0 && $__maxComparePrice > $__minVariantPrice)
                ? (int) round((1 - $__minVariantPrice / $__maxComparePrice) * 100)
                : 0;

            $__activeVariant = $this->product->variants->firstWhere('id', $this->selectedVariantId)
                ?? $this->product->variants->first();
            $__initialOutOfStock = ! $__activeVariant || (int) $__activeVariant->stock <= 0;

            $__variantPayload = [];
            foreach ($this->product->variants as $__v) {
                $__price = (float) $__v->price;
                $__compare = filled($__v->compare_price) ? (float) $__v->compare_price : 0;

                $__variantPayload[(string) $__v->id] = [
                    'name' => $__v->name,
                    'price_formatted' => currency($__v->price),
                    'compare_price_formatted' => ($__compare > 0 && $__compare > $__price && $__price > 0) ? currency($__v->compare_price) : null,
                    'discount_percent' => ($__compare > 0 && $__compare > $__price && $__price > 0) ? (int) round((1 - $__price / $__compare) * 100) : 0,
                    'stock' => (int) $__v->stock,
                    'threshold' => (int) $__v->low_stock_threshold,
                    'out_of_stock' => (int) $__v->stock <= 0,
                    'option_values' => $__v->optionValues
                        ->map(fn ($ov) => $ov->option?->name . ': ' . $ov->value)
                        ->filter()
                        ->implode(', '),
                ];
            }
        @endphp

        <div class="max-w-6xl mx-auto">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-8">
                <a href="/" class="hover:text-gray-700 dark:hover:text-gray-200 transition">{{ $this->product->store->name ?? __('storefront.back_to_store') }}</a>
                <span>/</span>
                @if($this->product->categories->first())
                    <span class="hover:text-gray-700 dark:hover:text-gray-200 transition">{{ $this->product->categories->first()->name }}</span>
                    <span>/</span>
                @endif
                <span class="text-gray-900 dark:text-white">{{ $this->product->name }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                {{-- Images --}}
                <div x-data="productGallery()" class="space-y-4" dir="ltr">
                    @if($this->product->images->count())
                        {{-- Main Image --}}
                        <div class="relative aspect-square rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 group cursor-zoom-in"
                             @click="openLightbox()" role="region" aria-label="{{ __('storefront.product_images') ?? 'Product images' }}">
                            @foreach($this->product->images as $i => $img)
                                <img
                                    x-show="active === {{ $i }}"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 scale-105"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    src="{{ asset('storage/' . $img->path) }}"
                                    alt="{{ $this->product->name }} — {{ $i + 1 }}"
                                    class="w-full h-full object-cover absolute inset-0"
                                    onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'"
                                    draggable="false"
                                >
                            @endforeach

                            {{-- Featured Badge --}}
                            @if($this->product->is_featured)
                                <span class="absolute top-4 left-4 z-10 flex items-center gap-1.5 store-bg-primary text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                                    <ion-icon name="star" class="text-sm"></ion-icon>
                                    {{ __('storefront.featured') }}
                                </span>
                            @endif

                            {{-- Discount Badge --}}
                            @if($__imageDiscountPercent > 0)
                                <span class="absolute top-4 right-4 z-10 bg-red-500 text-white text-xs font-bold px-2.5 py-1.5 rounded-full shadow-lg">
                                    -{{ $__imageDiscountPercent }}%
                                </span>
                            @endif

                            {{-- Counter --}}
                            <div class="absolute bottom-4 right-4 bg-black/50 backdrop-blur-sm text-white text-xs font-medium px-2.5 py-1 rounded-full z-10">
                                <span x-text="active + 1"></span>/<span>{{ $this->product->images->count() }}</span>
                            </div>

                            {{-- Navigation Arrows --}}
                            @if($this->product->images->count() > 1)
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
                        @if($this->product->images->count() > 1)
                            <div class="flex gap-2.5 overflow-x-auto pb-1 scrollbar-hide" x-ref="thumbs">
                                @foreach($this->product->images as $i => $img)
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
                        <div class="aspect-square rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden relative">
                            @if($this->product->is_featured)
                                <span class="absolute top-4 left-4 z-10 flex items-center gap-1.5 store-bg-primary text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                                    <ion-icon name="star" class="text-sm"></ion-icon>
                                    {{ __('storefront.featured') }}
                                </span>
                            @endif
                            <img src="{{ asset('img/icons/noimg.png') }}" alt="{{ $this->product->name }}" class="w-full h-full object-contain p-8 opacity-60">
                        </div>
                    @endif

                    {{-- Lightbox --}}
                    <div x-show="lightbox" x-transition.opacity class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-center justify-center" @click.self="lightbox = false" @keydown.escape.window="lightbox = false" @keydown.left.window="if(lightbox) prev()" @keydown.right.window="if(lightbox) next()" style="display:none">
                        <button type="button" @click="lightbox = false" class="absolute top-4 right-4 text-white/70 hover:text-white transition" aria-label="Close">
                            <ion-icon name="close" class="text-3xl"></ion-icon>
                        </button>
                        @if($this->product->images->count() > 1)
                            <button type="button" x-on:click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition" aria-label="Previous">
                                <ion-icon name="chevron-back" class="text-2xl"></ion-icon>
                            </button>
                            <button type="button" x-on:click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition" aria-label="Next">
                                <ion-icon name="chevron-forward" class="text-2xl"></ion-icon>
                            </button>
                        @endif
                        @foreach($this->product->images as $i => $img)
                            <img x-show="active === {{ $i }}" x-transition src="{{ asset('storage/' . $img->path) }}" alt="{{ $this->product->name }}" class="max-w-[90vw] max-h-[85vh] object-contain rounded-lg" onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'" draggable="false">
                        @endforeach
                        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-black/50 backdrop-blur-sm text-white text-sm px-3 py-1.5 rounded-full">
                            <span x-text="active + 1"></span> / {{ $this->product->images->count() }}
                        </div>
                    </div>
                </div>

                {{-- Info --}}
                <div class="flex flex-col"
                     x-data="productInfo({!! json_encode($__variantPayload) !!}, @js(__('storefront.low_stock', ['count' => ':count'])), @js(__('storefront.in_stock')), @js(__('storefront.out_of_stock')), @js(__('storefront.out_of_stock_short')), @js(__('storefront.add_to_cart')))">
                    @if($this->product->brand)
                        <span class="text-sm font-semibold store-text-primary uppercase tracking-wider mb-2">{{ $this->product->brand->name }}</span>
                    @endif

                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ $this->product->name }}</h1>

                    {{-- Price --}}
                    <div class="mb-4">
                        <template x-if="activeVariant">
                            <div class="flex items-center flex-wrap gap-x-3 gap-y-1">
                                <span class="text-3xl font-bold store-text-primary" x-text="activeVariant.price_formatted"></span>
                                <template x-if="activeVariant.compare_price_formatted">
                                    <span class="text-lg text-gray-400 dark:text-gray-500 line-through" x-text="activeVariant.compare_price_formatted"></span>
                                </template>
                                <template x-if="activeVariant.discount_percent > 0">
                                    <span class="text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded-full" x-text="'-' + activeVariant.discount_percent + '%'"></span>
                                </template>
                            </div>
                        </template>
                        <template x-if="!activeVariant">
                            <div class="flex items-center flex-wrap gap-x-3 gap-y-1">
                                <span class="text-3xl font-bold store-text-primary">{{ currency($__minVariantPrice ?: $this->product->price) }}</span>
                                @if($__imageDiscountPercent > 0 && $__maxComparePrice > 0)
                                    <span class="text-lg text-gray-400 dark:text-gray-500 line-through">{{ currency($__maxComparePrice) }}</span>
                                    <span class="text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded-full">-{{ $__imageDiscountPercent }}%</span>
                                @endif
                            </div>
                        </template>
                    </div>

                    {{-- Stock Indicator --}}
                    <div class="flex items-center gap-2 mb-6">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="stockDotClass"></span>
                        <span class="text-sm font-medium" :class="stockTextClass" x-text="stockLabel"></span>
                    </div>

                    @if($this->product->short_description)
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6">{{ $this->product->short_description }}</p>
                    @endif

                    {{-- Variants --}}
                    @if($this->product->variants->count() > 1)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('storefront.options') }}</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($this->product->variants as $variant)
                                    @php
                                        $__outOfStock = (int) $variant->stock <= 0;
                                    @endphp
                                    <button
                                        type="button"
                                        x-on:click="select('{{ $variant->id }}')"
                                        @if($__outOfStock) disabled tabindex="-1" @endif
                                        :class="$wire.selectedVariantId === '{{ $variant->id }}'
                                            ? 'store-border-primary store-bg-primary-soft store-text-primary ring-1 border-transparent cursor-default'
                                            : '{{ $__outOfStock
                                                ? 'border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-600 line-through opacity-60 cursor-not-allowed'
                                                : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500' }}'"
                                        class="border-2 rounded-lg px-5 py-2.5 text-sm font-medium transition ring-transparent disabled:cursor-not-allowed"
                                    >
                                        {{ $variant->name }}
                                        <span class="ml-1 text-xs opacity-70">{{ currency($variant->price) }}</span>
                                        @if($__outOfStock)
                                            <span class="ml-1 text-xs font-semibold text-red-500 dark:text-red-400 no-underline">{{ __('storefront.out_of_stock_short') }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Quantity --}}
                    @if(! $__initialOutOfStock)
                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('storefront.quantity') }}</label>
                            <div class="inline-flex items-center rounded-lg border-2 border-gray-200 dark:border-gray-700 overflow-hidden">
                                <button
                                    type="button"
                                    wire:click="decrementQuantity()"
                                    class="w-10 h-10 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                    :disabled="$wire.quantity <= 1"
                                    aria-label="Decrease quantity"
                                >
                                    <ion-icon name="remove" class="text-lg"></ion-icon>
                                </button>
                                <input
                                    type="number"
                                    wire:model.blur="quantity"
                                    min="1"
                                    max="{{ ($__activeVariant && (int) $__activeVariant->stock > 0) ? (int) $__activeVariant->stock : 1 }}"
                                    class="w-14 h-10 text-center border-x-2 border-gray-200 dark:border-gray-700 bg-transparent text-gray-900 dark:text-white focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                    aria-label="{{ __('storefront.quantity') }}"
                                >
                                <button
                                    type="button"
                                    wire:click="incrementQuantity()"
                                    class="w-10 h-10 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                    :disabled="activeVariant && $wire.quantity >= activeVariant.stock"
                                    aria-label="Increase quantity"
                                >
                                    <ion-icon name="add" class="text-lg"></ion-icon>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Add to Cart --}}
                    <button
                        type="button"
                        wire:click="addToCart()"
                        :disabled="isOutOfStock"
                        @if($__initialOutOfStock) disabled @endif
                        class="w-full sm:w-auto store-btn-primary text-white font-bold py-3.5 px-10 rounded-lg transition text-base flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                        wire:target="addToCart"
                    >
                        <ion-icon name="cart-outline" class="text-xl"></ion-icon>
                        <span x-text="isOutOfStock ? outOfStockShortLabel : addToCartLabel" wire:loading.remove wire:target="addToCart">{{ __('storefront.add_to_cart') }}</span>
                        <span wire:loading class="hidden" wire:target="addToCart">{{ __('storefront.placing') }}</span>
                    </button>

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
            @if($this->product->description)
                <div class="mt-16 pt-12 border-t border-gray-200 dark:border-gray-700">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('storefront.product_details') }}</h2>
                    <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                        {!! nl2br(e($this->product->description)) !!}
                    </div>
                </div>
            @endif

            {{-- Related Products --}}
            @if($this->relatedProducts->count())
                <section class="mt-16 pt-12 border-t border-gray-200 dark:border-gray-700">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">{{ __('storefront.you_may_also_like') }}</h2>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                        @foreach($this->relatedProducts as $rp)
                            @php
                                $__rpImage = $rp->images->first()?->path;
                                $__rpMinPrice = (float) ($rp->variants->min('price') ?? $rp->price);
                                $__rpMaxCompare = (float) $rp->variants->max('compare_price');
                                $__rpDiscount = ($__rpMaxCompare > 0 && $__rpMinPrice > 0 && $__rpMaxCompare > $__rpMinPrice)
                                    ? (int) round((1 - $__rpMinPrice / $__rpMaxCompare) * 100)
                                    : 0;
                                $__rpHasStock = $rp->variants->contains(fn ($rv) => (int) $rv->stock > 0);
                                $__rpFirstVariant = $rp->variants->first();
                                $__rpSingleInStock = $rp->variants->count() === 1 && $__rpFirstVariant && (int) $__rpFirstVariant->stock > 0;
                                $__rpUrl = route('storefront.product', ['store' => $this->product->store->slug, 'product' => $rp->slug]);
                            @endphp

                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition group relative flex flex-col">
                                <a href="{{ $__rpUrl }}" class="block">
                                    <div class="relative aspect-square bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                        @if($__rpImage)
                                            <img src="{{ asset('storage/' . $__rpImage) }}"
                                                alt="{{ $rp->name }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                                onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'"
                                                draggable="false">
                                        @else
                                            <img src="{{ asset('img/icons/noimg.png') }}" alt="{{ $rp->name }}"
                                                class="w-full h-full object-contain p-4 opacity-60" draggable="false">
                                        @endif

                                        @if($__rpDiscount > 0)
                                            <span class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg">
                                                -{{ $__rpDiscount }}%
                                            </span>
                                        @endif

                                        @if($rp->is_featured)
                                            <span class="absolute top-3 right-3 flex items-center gap-1 store-bg-primary text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg">
                                                <ion-icon name="star" class="text-xs"></ion-icon>
                                                {{ __('storefront.featured') }}
                                            </span>
                                        @endif

                                        @if(! $__rpHasStock)
                                            <span class="absolute inset-0 bg-black/45 flex items-center justify-center">
                                                <span class="bg-red-500/95 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                                                    {{ __('storefront.out_of_stock_short') }}
                                                </span>
                                            </span>
                                        @endif
                                    </div>
                                </a>

                                <div class="p-4 flex flex-col flex-1">
                                    <a href="{{ $__rpUrl }}">
                                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1 line-clamp-2">{{ $rp->name }}</h3>
                                    </a>

                                    <div class="flex items-center flex-wrap gap-x-2 gap-y-1 mt-2">
                                        <span class="text-lg font-bold store-text-primary">{{ currency($__rpMinPrice) }}</span>
                                        @if($__rpDiscount > 0)
                                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 line-through">{{ currency($__rpMaxCompare) }}</span>
                                            <span class="text-xs font-bold text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-1.5 py-0.5 rounded-full">
                                                -{{ $__rpDiscount }}%
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-auto pt-3 flex justify-end">
                                        @if($__rpSingleInStock)
                                            <button type="button"
                                                wire:click="addRelatedToCart('{{ $__rpFirstVariant->id }}')"
                                                wire:loading.attr="disabled"
                                                wire:loading.class="opacity-50"
                                                wire:target="addRelatedToCart('{{ $__rpFirstVariant->id }}')"
                                                class="store-btn-primary text-white min-h-[44px] min-w-[44px] flex items-center justify-center rounded-lg transition text-sm disabled:cursor-not-allowed"
                                                title="{{ __('storefront.add_to_cart') }}">
                                                <ion-icon name="cart-outline"></ion-icon>
                                            </button>
                                        @elseif(! $__rpHasStock)
                                            <span class="bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 min-h-[44px] min-w-[44px] flex items-center justify-center rounded-lg text-sm cursor-not-allowed"
                                                title="{{ __('storefront.out_of_stock') }}">
                                                <ion-icon name="cart-outline"></ion-icon>
                                            </span>
                                        @else
                                            <a href="{{ $__rpUrl }}"
                                                class="store-btn-primary text-white min-h-[44px] flex items-center justify-center px-3 rounded-lg transition text-xs font-medium gap-1">
                                                <ion-icon name="options-outline"></ion-icon>
                                                {{ __('storefront.view_options') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
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
