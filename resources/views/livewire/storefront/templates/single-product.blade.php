<?php

use App\Models\Products\Product;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'product' => null,
    'selectedVariant' => null,
    'sections' => [],
    'section_content' => [],
]);

mount(function (): void {
    $store = currentStore();
    if (!$store) { return; }
    $theme = $store->theme;
    $this->sections = $theme?->homepage_sections ?? ['hero', 'social_proof', 'faq', 'cta'];
    $this->section_content = $theme?->section_content ?? [
        'hero' => ['title' => '', 'description' => '', 'button_text' => ''],
        'social_proof' => [
            'title' => __('storefront.why_customers_love_us'),
            'items' => [
                ['title' => __('storefront.secure_payment'), 'description' => __('storefront.pay_on_delivery'), 'icon' => 'shield-checkmark-outline'],
                ['title' => __('storefront.fast_delivery'), 'description' => __('storefront.across_the_country'), 'icon' => 'car-outline'],
                ['title' => __('storefront.easy_returns'), 'description' => __('storefront.hassle_free_policy'), 'icon' => 'refresh-outline'],
            ],
        ],
        'faq' => [
            'title' => __('storefront.faq'),
            'items' => [
                ['question' => __('storefront.faq_delivery_q'), 'answer' => __('storefront.faq_delivery_a')],
                ['question' => __('storefront.faq_payment_q'), 'answer' => __('storefront.faq_payment_a')],
                ['question' => __('storefront.faq_return_q'), 'answer' => __('storefront.faq_return_a')],
            ],
        ],
        'cta' => ['title' => __('storefront.ready_to_order'), 'description' => __('storefront.get_yours_now'), 'button_text' => __('storefront.order_now')],
        'description' => ['title' => __('storefront.product_details')],
    ];

    $this->product = Product::where('store_id', $store->id)
        ->where('is_active', true)
        ->with(['images', 'variants.optionValues.option', 'brand'])
        ->first();

    if (! $this->product) {
        abort(404);
    }

    $this->selectedVariant = $this->product->variants->first();
});

$selectVariant = function (string $variantId): void {
    $variant = $this->product->variants->firstWhere('id', $variantId);

    if (! $variant) {
        return;
    }

    $this->selectedVariant = $variant;
};

$addToCart = function (): void {
    $storeId = currentStoreId();
    if (!$storeId) { return; }
    $cartService = app(\App\Domains\Cart\Services\CartService::class);

    $variant = $this->selectedVariant;

    if (!$variant && $this->product->variants->count() === 1) {
        $variant = $this->product->variants->first();
    }

    if (!$variant) {
        $this->dispatch('swal', type: 'error', title: __('storefront.please_select_variant'));
        return;
    }

    if ($variant->isOutOfStock()) {
        $this->dispatch('swal', type: 'error', title: __('storefront.out_of_stock'));
        return;
    }

    $cartService->addItem($storeId, $variant->id, 1);

    $this->dispatch('cart-updated');
};
?>

<div>
    @if($this->product)
    {{-- Hero Section --}}
    @if(in_array('hero', $this->sections))
    <section class="relative overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                {{-- Text --}}
                <div>
                    @if($this->product->brand)
                        <span class="inline-block text-sm font-semibold store-text-primary mb-3 uppercase tracking-wider">{{ $this->product->brand->name }}</span>
                    @endif
                    <h1 class="text-2xl sm:text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight mb-6">
                        {{ $this->product->name }}
                    </h1>
                    @if($this->product->short_description ?? $this->product->description)
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed">
                            {{ $this->product->short_description ?? Str::limit($this->product->description, 200) }}
                        </p>
@endif

                    <div x-data="{
                            selectedVariantId: @js($this->product->variants->first()?->id),
                            variants: @js($this->product->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => (float) $v->price, 'compare_price' => (float) ($v->compare_price ?? 0), 'stock' => $v->stock, 'low_stock_threshold' => $v->low_stock_threshold]),
                            getVariant(id) { return this.variants.find(v => v.id === id); },
                            getVariantPrice(v) { return v ? (float) v.price : 0; },
                            getVariantCompare(v) { return v ? (float) (v.compare_price ?? 0) : 0; },
                            getVariantStock(v) { return v ? v.stock : 0; },
                            getVariantLowStockThreshold(v) { return v ? v.low_stock_threshold : 0; },
                            getStockStatus(v) {
                                if (!v) return 'in';
                                if (v.stock <= 0) return 'out';
                                if (v.stock > 0 && v.stock <= v.low_stock_threshold) return 'low';
                                return 'in';
                            },
                            getStockText(v) {
                                const status = this.getStockStatus(v);
                                if (status === 'low') return '@php echo e(__(\'storefront.low_stock\', [\'count\' => 1])); @endphp'.replace('1', v.stock);
                                if (status === 'out') return '@php echo e(__(\'storefront.out_of_stock\')); @endphp';
                                return '@php echo e(__(\'storefront.in_stock\')); @endphp';
                            },
                            getStockClass(status) {
                                return status === 'out' ? 'text-red-500 dark:text-red-400' :
                                       status === 'low' ? 'text-amber-600 dark:text-amber-400' :
                                       'text-emerald-600 dark:text-emerald-400';
                            }
                        }"
                        x-init="selectedVariantId = @js($this->product->variants->first()?->id)">

                    {{-- Price display (reactive via Alpine) --}}
                    <template x-if="getVariant(getVariant(selectedVariantId))">
                        <div class="flex items-center flex-wrap gap-x-3 gap-y-1 mb-2">
                            <span class="text-2xl sm:text-3xl lg:text-4xl font-bold store-text-primary" x-text="currency(getVariantPrice(getVariant(selectedVariantId)))"></span>
                            <template x-if="getVariantCompare(getVariant(selectedVariantId)) > 0 && getVariantCompare(getVariant(selectedVariantId)) > getVariantPrice(getVariant(selectedVariantId))">
                                <span class="text-lg text-gray-400 dark:text-gray-500 line-through" x-text="currency(getVariantCompare(getVariant(selectedVariantId)))"></span>
                                <span class="text-sm font-bold text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full" x-text="'-' + Math.round((1 - getVariantPrice(getVariant(selectedVariantId)) / getVariantCompare(getVariant(selectedVariantId))) * 100) + '%'"></span>
                            </template>
                        </div>

                        {{-- Stock status --}}
                        <p class="mb-6 text-sm font-medium" :class="getStockClass(getStockStatus(getVariant(selectedVariantId)))" x-text="getStockText(getVariant(selectedVariantId))"></p>
                    </template>

                    {{-- Variant selector --}}
                    @if($this->product->variants->count() > 1)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('storefront.options') }}</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($this->product->variants as $variant)
                                    <button
                                        type="button"
                                        @click="selectedVariantId = '{{ $variant->id }}'; $wire.selectVariant('{{ $variant->id }}')"
                                        :class="selectedVariantId === '{{ $variant->id }}'
                                            ? 'store-border-primary store-bg-primary-soft store-text-primary'
                                            : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300'"
                                        class="border-2 rounded-lg px-4 py-2 text-sm font-medium transition cursor-pointer"
                                    >
                                        {{ $variant->name }}
                                    </button>
                                @endforeach
                            </div>

                            <button
                                type="button"
                                wire:click="addToCart"
                                :disabled="getStockStatus(getVariant(selectedVariantId)) === 'out'"
                                class="mt-4 w-full sm:w-auto store-btn-primary text-white font-bold py-3 px-8 rounded-lg transition text-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled"
                            >
<x-edz.icon name="cart" class="mr-2 w-5 h-5"></x-edz.icon>
                                {{ __('storefront.add_to_cart') }}
                            </button>
                        </div>
                    @else
                        <button
                            type="button"
                            wire:click="addToCart"
                            :disabled="getStockStatus(getVariant(selectedVariantId)) === 'out'"
                            class="store-btn-primary text-white font-bold py-3 px-8 rounded-lg transition text-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                        >
                            <x-edz.icon name="cart" class="mr-2 w-5 h-5"></x-edz.icon>
                            {{ __('storefront.add_to_cart') }}
                        </button>
                    @endif
                </div>

                {{-- Image --}}
                <div class="relative">
                    @if($this->product->images->count())
                        <img
                            src="{{ asset('storage/' . $this->product->images->first()->path) }}"
                            alt="{{ $this->product->name }}"
                            class="rounded-2xl shadow-2xl w-full object-cover aspect-square"
                            onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'"
                        >
                    @else
                        <div class="rounded-2xl shadow-2xl bg-gray-200 dark:bg-gray-700 w-full aspect-square flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('img/icons/noimg.png') }}" alt="{{ $this->product->name }}" class="w-full h-full object-contain p-8 opacity-60">
                        </div>
                    @endif

                    {{-- Featured Badge --}}
                    @if($this->product->is_featured)
                        <span class="absolute top-4 {{ isRTL() ? 'end-4' : 'start-4' }} z-10 flex items-center gap-1.5 store-bg-primary text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                            <x-edz.icon name="star" class="text-sm w-4 h-4"></x-edz.icon>
                            {{ __('storefront.featured') }}
                        </span>
                    @endif

                    {{-- Discount Badge --}}
                    @php
                        $_badgeVariant = $this->selectedVariant ?? $this->product->variants->first();
                        $_heroMaxCompare = (float) ($_badgeVariant?->compare_price ?? 0);
                        $_heroMinPrice = (float) ($_badgeVariant?->price ?? $this->product->price);
                        $_heroDiscount = ($_heroMaxCompare > 0 && $_heroMinPrice > 0 && $_heroMaxCompare > $_heroMinPrice)
                            ? (int) round((1 - $_heroMinPrice / $_heroMaxCompare) * 100)
                            : 0;
                    @endphp
                    @if($_heroDiscount > 0)
                        <span class="absolute top-4 {{ isRTL() ? 'start-4' : 'end-4' }} z-10 bg-red-500 text-white text-xs font-bold px-2.5 py-1.5 rounded-full shadow-lg">
                            -{{ $_heroDiscount }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Description --}}
    @if($this->product->description && in_array('description', $this->sections))
    <section class="py-16 bg-white dark:bg-gray-800">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">{{ $this->section_content['description']['title'] ?? __('storefront.product_details') }}</h2>
            <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                {!! nl2br(e($this->product->description)) !!}
            </div>
        </div>
    </section>
    @endif

    {{-- Social Proof --}}
    @if(in_array('social_proof', $this->sections))
    @php $sp = $this->section_content['social_proof'] ?? []; @endphp
    <section class="py-16 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">{{ $sp['title'] ?? __('storefront.why_customers_love_us') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                @foreach(($sp['items'] ?? []) as $item)
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 store-bg-primary-soft rounded-full flex items-center justify-center mb-4">
                            <x-edz.icon :name="$item['icon'] ?? 'check'" class="text-2xl w-8 h-8 store-text-primary"></x-edz.icon>
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $item['title'] ?? '' }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $item['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- FAQ --}}
    @if(in_array('faq', $this->sections))
    @php $faq = $this->section_content['faq'] ?? []; @endphp
    <section class="py-16 bg-white dark:bg-gray-800" x-data="{ openFaq: null }">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 text-center">{{ $faq['title'] ?? __('storefront.faq') }}</h2>
            <div class="space-y-4">
                @foreach(($faq['items'] ?? []) as $faqItem)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                        <button
                            x-on:click="openFaq = openFaq === {{ $loop->index }} ? null : {{ $loop->index }}"
                            :aria-expanded="openFaq === {{ $loop->index }}"
                            class="w-full px-6 py-4 text-start flex items-center justify-between"
                        >
                            <span class="font-medium text-gray-900 dark:text-white">{{ $faqItem['question'] ?? '' }}</span>
                            <x-edz.icon :name="openFaq === {{ $loop->index }} ? 'chevron-up' : 'chevron-down'" class="text-gray-500 dark:text-gray-400 w-5 h-5"></x-edz.icon>
                        </button>
                        <div x-show="openFaq === {{ $loop->index }}" x-transition class="px-6 pb-4">
                            <p class="text-gray-600 dark:text-gray-300">{{ $faqItem['answer'] ?? '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Final CTA --}}
    @if(in_array('cta', $this->sections))
    @php $cta = $this->section_content['cta'] ?? []; @endphp
    <section class="py-16 store-gradient">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">{{ $cta['title'] ?? __('storefront.ready_to_order') }}</h2>
            <p class="text-white/80 mb-8 text-lg">{{ $cta['description'] ?? __('storefront.get_yours_now') }}</p>
            <a
                href="#"
                x-on:click.prevent="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="inline-flex items-center gap-2 bg-white dark:bg-gray-100 font-bold py-3 px-8 rounded-lg hover:bg-white/90 dark:hover:bg-white transition text-lg store-text-primary"
            >
                <x-edz.icon name="cart" class="w-5 h-5"></x-edz.icon>
                {{ $cta['button_text'] ?? __('storefront.order_now') }}
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
