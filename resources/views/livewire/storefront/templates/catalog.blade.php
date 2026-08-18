<?php

use App\Models\Category;
use App\Models\Products\Product;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'search'       => '',
    'category_id'  => '',
    'sortBy'       => 'newest',
    'sections'     => [],
]);

mount(function (): void {
    $store = currentStore();
    $theme = $store->theme;
    $this->sections = $theme?->homepage_sections ?? ['hero', 'categories', 'social_proof'];
});

$addToCart = function (string $variantId) {
    $cartService = app(\App\Domains\Cart\Services\CartService::class);
    $cartService->addItem(currentStoreId(), $variantId, 1);
    $this->dispatch('swal', type: 'success', title: __('storefront.added_to_cart'));
};
?>

<div>
    @php
        $store = currentStore();

        $categories = Category::where('store_id', $store->id)
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $query = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->with(['images', 'brand', 'categories']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $category_id));
        }

        match ($sortBy) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'oldest'     => $query->orderBy('created_at'),
            default      => $query->orderByDesc('created_at'),
        };

        $products = $query->paginate(12);
    @endphp

    {{-- Hero --}}
    @if(in_array('hero', $sections))
    <section class="store-gradient text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold mb-4">{{ $store->name }}</h1>
            <p class="text-lg text-white/80 mb-8">{{ $store->description ?? 'Browse our full catalog' }}</p>

            {{-- Search --}}
            <div class="max-w-xl mx-auto">
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search products...') }}"
                        class="w-full px-5 py-3 pl-12 rounded-full bg-white/20 backdrop-blur-sm text-white placeholder-white/60 border border-white/30 focus:outline-none focus:ring-2 focus:ring-white/50"
                    >
                    <ion-icon name="search-outline" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 text-xl"></ion-icon>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Categories --}}
    @if(in_array('categories', $sections))

    @if($categories->count())
    <section class="py-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                <button
                    wire:click="$set('category_id', '')"
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition
                        {{ empty($category_id) ? 'store-bg-primary text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                >
                    {{ __('All') }}
                </button>
                @foreach($categories as $cat)
                    <button
                        wire:click="$set('category_id', '{{ $cat->id }}')"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition
                            {{ $category_id === $cat->id ? 'store-bg-primary text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                    >
                        {{ $cat->name }} ({{ $cat->products_count }})
                    </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endif

    {{-- Sort --}}
    <section class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $products->total() }} {{ __('products') }}
            </p>
            <select wire:model.live="sortBy" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                <option value="newest">{{ __('Newest') }}</option>
                <option value="price_asc">{{ __('Price: Low to High') }}</option>
                <option value="price_desc">{{ __('Price: High to Low') }}</option>
            </select>
        </div>
    </section>

    {{-- Product Grid --}}
    <section class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($products->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($products as $product)
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition group">
                            <a href="{{ route('storefront.product', ['store' => $store->slug, 'product' => $product]) }}" class="block">
                                <div class="aspect-square bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                    @if($product->images->count())
                                        <img
                                            src="{{ asset('storage/' . $product->images->first()->path) }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <ion-icon name="image-outline" class="text-4xl text-gray-400"></ion-icon>
                                        </div>
                                    @endif
                                </div>
                            </a>

                            <div class="p-4">
                                <a href="{{ route('storefront.product', ['store' => $store->slug, 'product' => $product]) }}">
                                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1 line-clamp-2">{{ $product->name }}</h3>
                                </a>
                                @if($product->brand)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $product->brand->name }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-3">
                                    <span class="text-lg font-bold store-text-primary">{{ currency($product->min_price ?? $product->price) }}</span>
                                    @if($product->variants->count() === 1)
                                        <button
                                            wire:click="$wire.addToCart('{{ $product->variants->first()->id }}')"
                                            class="store-btn-primary text-white p-2 rounded-lg transition text-sm"
                                            title="{{ __('Add to cart') }}"
                                        >
                                            <ion-icon name="cart-outline"></ion-icon>
                                        </button>
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
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No products found') }}</p>
                </div>
            @endif
        </div>
    </section>
</div>
