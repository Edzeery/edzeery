<?php

use App\Models\Orders\Order;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'order' => null,
]);

mount(function (string $order): void {
    $store = currentStore();
    if (!$store) {
        return;
    }

    $this->order = Order::with(['items.product', 'items.variant', 'stopdeskPoint', 'customer', 'city', 'state'])
        ->where('store_id', $store->id)
        ->where('id', $order)
        ->where('created_at', '>=', now()->subMinutes(30))
        ->first();

    if (!$this->order) {
        abort(404);
    }
});
?>

@php
    $order = $this->order;
    $items = $order->items ?? collect();
    $hasItems = $items->count() > 0;

    $subtotal = $hasItems
        ? $items->sum(fn ($i) => (float) $i->price * (int) $i->quantity)
        : 0;
    $shipping = (float) ($order->shipping_cost ?? 0);
    $shippingFree = $shipping <= 0;
    $discount = (float) $order->discount_amount;
    $hasDiscount = $discount > 0;
    $total = (float) $order->grand_total;

    $_cust = $order->customer ?? null;
    $_isStopdesk = ($order->delivery_type ?? '') === 'stopdesk' && !empty($order->stopdeskPoint);
    $_hasAddress = filled($order->address) || $order->city || $order->state;
    $_hasDeliveryInfo = filled($order->delivery_type)
        || ($_cust && (filled($_cust->name) || filled($_cust->phone)))
        || $_hasAddress
        || filled($order->notes)
        || !empty($order->stopdeskPoint);

    $paymentMethod = $order->payment_method ?? null;
    $paymentLabel = match ($paymentMethod) {
        'cod', 'cash_on_delivery' => __('storefront.payment_on_delivery'),
        default => filled($paymentMethod) ? $paymentMethod : null,
    };
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">

    {{-- Success Header --}}
    <div class="text-center mb-8 sm:mb-10">
        <div class="relative w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-5">
            <span class="absolute inset-0 rounded-full bg-green-400/30 animate-ping" aria-hidden="true"></span>
            <div
                class="relative w-16 h-16 sm:w-20 sm:h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                <x-edz.icon name="check-circle" class="text-4xl sm:text-5xl text-green-600 dark:text-green-400" />
            </div>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
            {{ __('storefront.order_placed') }}
        </h1>
        <div
            class="inline-flex items-center gap-2 mt-4 rounded-full store-bg-primary-soft store-border-primary border px-4 py-1.5">
            <x-edz.icon name="tag" class="w-4 h-4 store-text-primary" />
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('storefront.your_order_number') }}
                <span class="font-mono font-bold store-text-primary" dir="ltr">#{{ $order->number }}</span>
            </p>
        </div>
        <div class="mt-4 flex items-center justify-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <x-edz.icon name="phone" class="w-4 h-4" />
            {{ __('storefront.we_will_contact_you') }}
        </div>
    </div>

    {{-- Two-column responsive body --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8 items-start">

        {{-- Order Summary --}}
        <section
            class="md:col-span-7 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 sm:p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2.5">
                <span
                    class="w-8 h-8 rounded-lg store-bg-primary-soft border store-border-primary flex items-center justify-center shrink-0">
                    <x-edz.icon name="document-text" class="w-4 h-4 store-text-primary" />
                </span>
                {{ __('storefront.order_summary') }}
            </h2>

            @if ($hasItems)
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($items as $item)
                        @php
                            $itemImagePath =
                                $item->product?->images?->first()?->path ?? $item->variant?->images?->first()?->path;
                            $itemImage = $itemImagePath ? asset('storage/' . $itemImagePath) : asset('img/icons/noimg.png');
                            $productName = $item->product?->name ?? __('storefront.not_available');
                            $variantName = $item->variant?->name ?? '';
                            $quantity = (int) ($item->quantity ?? 1);
                            $price = (float) ($item->price ?? 0);
                        @endphp
                        <li class="flex items-center gap-3 py-3">
                            <img src="{{ $itemImage }}" alt="{{ $productName }}" loading="lazy"
                                class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl object-cover bg-gray-100 dark:bg-gray-700 shrink-0"
                                onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $productName }}
                                </p>
                                @if ($variantName)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $variantName }}</p>
                                @endif
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 tabular-nums">
                                    {{ currency($price) }} &times; {{ $quantity }}
                                </p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums text-end shrink-0">
                                {{ currency($price * $quantity) }}
                            </p>
                        </li>
                    @endforeach
                </ul>

                <div class="border-t border-gray-200 dark:border-gray-700 mt-3 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('storefront.subtotal') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white tabular-nums">{{ currency($subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('storefront.shipping') }}</span>
                        @if ($shippingFree)
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                <x-edz.icon name="truck" class="w-4 h-4" />
                                {{ __('storefront.free') }}
                            </span>
                        @else
                            <span
                                class="font-medium text-gray-900 dark:text-white tabular-nums">{{ currency($shipping) }}</span>
                        @endif
                    </div>
                    @if ($hasDiscount)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('titles.discount') }}</span>
                            <span class="font-medium text-gray-900 dark:text-white tabular-nums">-{{ currency($discount) }}</span>
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4 flex justify-between items-center">
                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ __('storefront.total') }}</span>
                    <span class="text-xl font-bold store-text-primary tabular-nums">{{ currency($total) }}</span>
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                    {{ __('storefront.not_available') }}
                </p>
                <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4 flex justify-between items-center">
                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ __('storefront.total') }}</span>
                    <span class="text-xl font-bold store-text-primary tabular-nums">{{ currency($total) }}</span>
                </div>
            @endif
        </section>

        {{-- Customer & Delivery --}}
        @if ($_hasDeliveryInfo)
            <section
                class="md:col-span-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 sm:p-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2.5">
                    <span
                        class="w-8 h-8 rounded-lg store-bg-primary-soft border store-border-primary flex items-center justify-center shrink-0">
                        <x-edz.icon name="user" class="w-4 h-4 store-text-primary" />
                    </span>
                    {{ __('storefront.customer_information') }}
                </h2>

                <dl class="space-y-4 text-sm">
                    @if ($_cust && filled($_cust->name))
                        <div class="flex items-start gap-3">
                            <dt class="w-24 shrink-0 text-gray-500 dark:text-gray-400">{{ __('storefront.name') }}</dt>
                            <dd class="font-medium text-gray-900 dark:text-white break-words min-w-0">{{ $_cust->name }}
                            </dd>
                        </div>
                    @endif
                    @if ($_cust && filled($_cust->phone))
                        <div class="flex items-start gap-3">
                            <dt class="w-24 shrink-0 text-gray-500 dark:text-gray-400">{{ __('storefront.phone') }}</dt>
                            <dd class="font-medium text-gray-900 dark:text-white break-words min-w-0" dir="ltr">
                                {{ $_cust->phone }}</dd>
                        </div>
                    @endif
                    @if ($paymentLabel)
                        <div class="flex items-start gap-3">
                            <dt class="w-24 shrink-0 text-gray-500 dark:text-gray-400">
                                {{ __('storefront.payment_method') }}</dt>
                            <dd class="font-medium text-gray-900 dark:text-white break-words min-w-0">{{ $paymentLabel }}
                            </dd>
                        </div>
                    @endif
                </dl>

                {{-- Delivery block --}}
                @if ($_isStopdesk || $_hasAddress)
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-5 pt-5">
                        <h3
                            class="text-xs font-semibold text-gray-400 dark:text-gray-500 tracking-wide mb-3 flex items-center gap-1.5">
                            <x-edz.icon name="truck" class="w-3.5 h-3.5" />
                            {{ __('storefront.delivery_information') }}
                        </h3>

                        @if ($_isStopdesk)
                            <div
                                class="flex items-start gap-3 store-bg-primary-soft rounded-xl p-3.5 store-border-primary border">
                                <span
                                    class="w-9 h-9 rounded-lg bg-white dark:bg-gray-700 flex items-center justify-center shrink-0 store-border-primary border">
                                    <x-edz.icon name="building-store" class="w-4 h-4 store-text-primary" />
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ __('storefront.stop_desk') }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 break-words">
                                        {{ $order->stopdeskPoint->name }}</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-3">
                                <span
                                    class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                    <x-edz.icon name="map-pin" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ __('storefront.home_delivery') }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 break-words">
                                        @if (filled($order->address))
                                            {{ $order->address }}@if ($order->city || $order->state),
                                            @endif
                                        @endif
                                        @if ($order->city)
                                            {{ $order->city->name }}@if ($order->state),
                                            @endif
                                        @endif
                                        {{ $order->state?->name ?? '' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if (filled($order->notes))
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-5 pt-5 flex items-start gap-3">
                        <span class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0">
                            <x-edz.icon name="document-text" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('storefront.notes') }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 break-words whitespace-pre-line">
                                {{ $order->notes }}</p>
                        </div>
                    </div>
                @endif
            </section>
        @endif
    </div>

    {{-- CTA --}}
    <div class="text-center mt-10">
        <a href="{{ route('storefront.home', ['store' => currentStore()?->slug ?? '']) }}"
            class="inline-flex items-center gap-2 store-btn-primary text-white font-semibold py-3 px-6 rounded-xl transition hover:shadow-lg min-h-[44px]">
            <x-edz.icon name="arrow-left" class="text-lg w-5 h-5" />
            {{ __('storefront.back_to_store') }}
        </a>
    </div>
</div>