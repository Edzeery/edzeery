<?php

use App\Models\Brand;
use App\Models\Products\Product;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'search' => '',
    'brand_id' => '',
    'sortBy' => 'newest',
    'sections' => [],
    'section_content' => [],
]);

mount(function (): void {
    $store = currentStore();
    $theme = $store->theme;
    $this->sections = $theme?->homepage_sections ?? ['hero', 'brands', 'social_proof'];
    $this->section_content = $theme?->section_content ?? [];
});

$addToCart = function (string $variantId) {
    app(\App\Domains\Cart\Services\CartService::class)->addItem(currentStoreId(), $variantId, 1);
    $this->dispatch('swal', type: 'success', title: __('storefront.added_to_cart'));
    $this->dispatch('cart-updated');
};
?>

<div>
    @php
        $store = currentStore();
        $brands = Brand::where('store_id', $store->id)
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $query = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->with(['images', 'brand', 'categories', 'variants']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($brand_id) {
            $query->where('brand_id', $brand_id);
        }

        match ($sortBy) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->orderByDesc('created_at'),
        };

        $products = $query->paginate(12);
    @endphp

    {{-- Hero / Brand Header --}}
    @if (in_array('hero', $sections))
        @php $hero = $section_content['hero'] ?? []; @endphp
        <section class="relative overflow-hidden text-white">
            <div class="absolute inset-0 store-gradient opacity-90"></div>
            @if ($store->cover)
                <img src="{{ asset('storage/' . $store->cover) }}" alt=""
                    class="absolute inset-0 w-full h-full object-cover opacity-30">
            @endif
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                @if ($store->logo)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}"
                        class="w-20 h-20 rounded-full mx-auto mb-6 object-cover border-4 border-white/20">
                @endif
                                    <h1 class="text-2xl sm:text-3xl lg:text-5xl font-bold mb-4">{{ $hero['title'] ?: $store->name }}</h1>
                @if ($hero['description'] ?: $store->description)
                    <p class="text-lg text-white/80 max-w-2xl mx-auto">{{ $hero['description'] ?: $store->description }}
                    </p>
                @endif

                {{-- Search --}}
                <div class="max-w-xl mx-auto">
                    <div class="relative w-full">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('storefront.search_products') }}"
                            class="w-full px-5 py-3
                                 {{ isRTL() ? 'pr-12 pl-5' : 'pl-12 pr-5' }}
                                 rounded-full bg-white/20 backdrop-blur-sm
                                 text-white placeholder-white/60
                                 border border-white/30
                                 focus:outline-none focus:ring-2 focus:ring-white/50">

                        <ion-icon name="search-outline"
                            class="absolute {{ $iconPosition }} top-1/2 -translate-y-1/2
                                text-white/70 text-xl pointer-events-none"></ion-icon>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Breadcrumb --}}
    <nav class="py-3 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('storefront.home', ['store' => $store->slug]) }}" class="hover:text-gray-700 dark:hover:text-gray-200 transition">{{ $store->name }}</a></li>
            <li class="text-gray-300 dark:text-gray-600">/</li>
            <li class="text-gray-900 dark:text-white font-medium">{{ __('storefront.collections') }}</li>
        </ol>
    </nav>

    {{-- Brand Filter --}}
    @if (in_array('brands', $sections) && $brands->count())
        @php $brandsContent = $section_content['brands'] ?? []; @endphp
        <section class="py-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                    {{ $brandsContent['title'] ?? __('storefront.collections') }}</h2>
                <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                    <button wire:click="$set('brand_id', '')"
                        class="shrink-0 px-5 py-2.5 rounded-lg text-sm font-medium transition border
                        {{ empty($brand_id) ? 'store-bg-primary text-white store-border-primary' : 'bg-transparent text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:store-border-primary' }}">
                        {{ __('storefront.all_collections') }}
                    </button>
                    @foreach ($brands as $brand)
                        <button wire:click="$set('brand_id', '{{ $brand->id }}')"
                            class="shrink-0 px-5 py-2.5 rounded-lg text-sm font-medium transition border
                            {{ $brand_id === $brand->id ? 'store-bg-primary text-white store-border-primary' : 'bg-transparent text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:store-border-primary' }}">
                            {{ $brand->name }}
                            <span class="ml-1 text-xs opacity-70">({{ $brand->products_count }})</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Sort + Count --}}
    <section class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">

                {{ $products->total() ?? 0 }} {{ __('storefront.products') }}
            </p>
            <select wire:model.live="sortBy"
                class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                <option value="newest">{{ __('storefront.newest') }}</option>
                <option value="price_asc">{{ __('storefront.price_low_high') }}</option>
                <option value="price_desc">{{ __('storefront.price_high_low') }}</option>
            </select>
        </div>
    </section>

    {{-- Product Grid --}}
    <section class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($products->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($products as $product)
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition group">
                            <a href="{{ route('storefront.product', ['store' => $store->slug, 'product' => $product->slug]) }}"
                                class="block">
                                <div class="aspect-square bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                    @if ($product->images->count())
                                        <img src="{{ asset('storage/' . $product->images->first()->path) }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                            onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'">
                                    @else
                                        <img src="{{ asset('img/icons/noimg.png') }}" alt="{{ $product->name }}"
                                            class="w-full h-full object-contain p-4 opacity-60">
                                    @endif
                                </div>
                            </a>

                            <div class="p-4">
                                <a
                                    href="{{ route('storefront.product', ['store' => $store->slug, 'product' => $product->slug]) }}">
                                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1 line-clamp-2">
                                        {{ $product->name }}</h3>
                                </a>
                                @if ($product->brand)
                                    <p class="text-xs store-text-primary font-medium mb-2">{{ $product->brand->name }}
                                    </p>
                                @endif
                                <div class="flex items-center justify-between mt-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg font-bold store-text-primary">{{ currency($product->min_price ?? $product->price) }}</span>
                                        @if (($product->compare_price ?? 0) > 0 && ($product->compare_price ?? 0) > ($product->min_price ?? $product->price))
                                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 line-through">{{ currency($product->compare_price) }}</span>
                                            <span class="text-xs font-bold text-red-500 bg-red-50 dark:bg-red-900/30 px-1.5 py-0.5 rounded-full">
                                                -{{ round((1 - ($product->min_price ?? $product->price) / $product->compare_price) * 100) }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if ($product->variants->count() === 1)
                                        <button wire:click="addToCart('{{ $product->variants->first()->id }}')"
                                            wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                            class="store-btn-primary text-white min-h-[44px] min-w-[44px] flex items-center justify-center rounded-lg transition text-sm"
                                            title="{{ __('storefront.add_to_cart') }}">
                                            <ion-icon name="cart-outline"></ion-icon>
                                        </button>
                                    @elseif ($product->variants->count() > 1)
                                        <a href="{{ route('storefront.product', ['store' => $store->slug, 'product' => $product->slug]) }}"
                                            class="store-btn-primary text-white min-h-[44px] min-w-[44px] flex items-center justify-center px-3 rounded-lg transition text-xs font-medium gap-1">
                                            <ion-icon name="options-outline"></ion-icon>
                                            {{ __('storefront.view_options') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-20">
                    <ion-icon name="bag-outline" class="text-6xl text-gray-300 dark:text-gray-600 mb-4"></ion-icon>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('storefront.no_products_found') }}</p>
                </div>
            @endif
        </div>
    </section>
</div>
