<?php

use App\Models\Products\Product;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

mount(function (): void {
    $store = currentStore();
    $theme = $store->theme;
    $this->sections = $theme?->homepage_sections ?? ['hero', 'social_proof', 'faq', 'cta'];

    $this->product = Product::where('store_id', $store->id)
        ->where('is_active', true)
        ->with(['images', 'variants.optionValues.option', 'brand'])
        ->first();

    if (! $this->product) {
        abort(404);
    }
});

$addToCart = function (string $variantId = null) {
    $cartService = app(\App\Domains\Cart\Services\CartService::class);
    $storeId = currentStoreId();

    if ($variantId) {
        $cartService->addItem($storeId, $variantId, 1);
    } elseif ($this->product->variants->count() === 1) {
        $cartService->addItem($storeId, $this->product->variants->first()->id, 1);
    }

    $this->dispatch('swal', type: 'success', title: __('storefront.added_to_cart'));
};

$selectedVariant = null;

state([
    'product' => null,
    'selectedVariant' => null,
    'sections' => [],
]);
?>

<div>
    @if($product)
    {{-- Hero Section --}}
    @if(in_array('hero', $sections))
    <section class="relative overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                {{-- Text --}}
                <div>
                    @if($product->brand)
                        <span class="inline-block text-sm font-semibold store-text-primary mb-3 uppercase tracking-wider">{{ $product->brand->name }}</span>
                    @endif
                    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight mb-6">
                        {{ $product->name }}
                    </h1>
                    @if($product->short_description ?? $product->description)
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed">
                            {{ $product->short_description ?? Str::limit($product->description, 200) }}
                        </p>
                    @endif

                    <div class="flex items-baseline gap-3 mb-8">
                        <span class="text-4xl font-bold store-text-primary">{{ currency($product->price) }}</span>
                    </div>

                    {{-- Variant selector --}}
                    @if($product->variants->count() > 1)
                        <div class="mb-6" x-data="{ selected: '{{ $product->variants->first()?->id }}' }">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('storefront.options') }}</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->variants as $variant)
                                    <button
                                        type="button"
                                        x-on:click="selected = '{{ $variant->id }}'"
                                        :class="selected === '{{ $variant->id }}' ? 'store-border-primary store-bg-primary/10 store-text-primary' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300'"
                                        class="border-2 rounded-lg px-4 py-2 text-sm font-medium transition cursor-pointer"
                                    >
                                        {{ $variant->name }}
                                    </button>
                                @endforeach
                            </div>

                            <button
                                type="button"
                                x-on:click="$wire.addToCart(selected)"
                                class="mt-4 w-full sm:w-auto store-btn-primary text-white font-bold py-3 px-8 rounded-lg transition text-lg"
                            >
                                <ion-icon name="cart-outline" class="mr-2"></ion-icon>
                                {{ __('storefront.add_to_cart') }}
                            </button>
                        </div>
                    @else
                        <button
                            type="button"
                            wire:click="addToCart('{{ $product->variants->first()?->id }}')"
                            class="store-btn-primary text-white font-bold py-3 px-8 rounded-lg transition text-lg"
                            wire:loading.attr="disabled"
                        >
                            <ion-icon name="cart-outline" class="mr-2"></ion-icon>
                            {{ __('storefront.add_to_cart') }}
                        </button>
                    @endif
                </div>

                {{-- Image --}}
                <div class="relative">
                    @if($product->images->count())
                        <img
                            src="{{ asset('storage/' . $product->images->first()->path) }}"
                            alt="{{ $product->name }}"
                            class="rounded-2xl shadow-2xl w-full object-cover aspect-square"
                        >
                    @else
                        <div class="rounded-2xl shadow-2xl bg-gray-200 dark:bg-gray-700 w-full aspect-square flex items-center justify-center">
                            <ion-icon name="image-outline" class="text-6xl text-gray-400"></ion-icon>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Description --}}
    @if($product->description && in_array('description', $sections))
    <section class="py-16 bg-white dark:bg-gray-800">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">{{ __('storefront.product_details') }}</h2>
            <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>
    </section>
    @endif

    {{-- Social Proof --}}
    @if(in_array('social_proof', $sections))
    <section class="py-16 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">{{ __('storefront.why_customers_love_us') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 store-bg-primary/10 rounded-full flex items-center justify-center mb-4">
                        <ion-icon name="shield-checkmark-outline" class="text-2xl store-text-primary"></ion-icon>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('storefront.secure_payment') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('storefront.pay_on_delivery') }}</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 store-bg-primary/10 rounded-full flex items-center justify-center mb-4">
                        <ion-icon name="car-outline" class="text-2xl store-text-primary"></ion-icon>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('storefront.fast_delivery') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('storefront.across_the_country') }}</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 store-bg-primary/10 rounded-full flex items-center justify-center mb-4">
                        <ion-icon name="refresh-outline" class="text-2xl store-text-primary"></ion-icon>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('storefront.easy_returns') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('storefront.hassle_free_policy') }}</p>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- FAQ --}}
    @if(in_array('faq', $sections))
    <section class="py-16 bg-white dark:bg-gray-800" x-data="{ openFaq: null }">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 text-center">{{ __('storefront.faq') }}</h2>
            <div class="space-y-4">
                @foreach([
                    ['q' => __('storefront.faq_delivery_q'), 'a' => __('storefront.faq_delivery_a')],
                    ['q' => __('storefront.faq_payment_q'), 'a' => __('storefront.faq_payment_a')],
                    ['q' => __('storefront.faq_return_q'), 'a' => __('storefront.faq_return_a')],
                ] as $faq)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                        <button
                            x-on:click="openFaq = openFaq === {{ $loop->index }} ? null : {{ $loop->index }}"
                            class="w-full px-6 py-4 text-left flex items-center justify-between"
                        >
                            <span class="font-medium text-gray-900 dark:text-white">{{ $faq['q'] }}</span>
                            <ion-icon :name="openFaq === {{ $loop->index }} ? 'chevron-up-outline' : 'chevron-down-outline'" class="text-gray-500"></ion-icon>
                        </button>
                        <div x-show="openFaq === {{ $loop->index }}" x-transition class="px-6 pb-4">
                            <p class="text-gray-600 dark:text-gray-300">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Final CTA --}}
    @if(in_array('cta', $sections))
    <section class="py-16 store-gradient">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">{{ __('storefront.ready_to_order') }}</h2>
            <p class="text-white/80 mb-8 text-lg">{{ __('storefront.get_yours_now') }}</p>
            <a
                href="#"
                x-on:click.prevent="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="inline-flex items-center gap-2 bg-white font-bold py-3 px-8 rounded-lg hover:bg-white/90 transition text-lg store-text-primary"
            >
                <ion-icon name="cart-outline"></ion-icon>
                {{ __('storefront.order_now') }}
            </a>
        </div>
    </section>
    @endif
    @else
    <div class="text-center py-20">
        <p class="text-gray-500 dark:text-gray-400">{{ __('storefront.no_product_available') }}</p>
    </div>
    @endif
</div>
