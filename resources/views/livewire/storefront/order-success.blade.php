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
    if (!$store) { return; }

    $this->order = Order::with(['items.product', 'items.variant', 'stopdeskPoint', 'customer', 'city', 'state'])
        ->where('store_id', $store->id)
        ->where('number', $order)
        ->first();

    if (!$this->order) {
        $this->order = (object) [
            'number' => $order,
            'items' => collect(),
            'total_amount' => 0,
            'shipping_cost' => 0,
            'delivery_type' => 'home',
            'address' => '',
            'notes' => '',
        ];
    }
});
?>

<div class="max-w-2xl mx-auto py-10 px-4">

    {{-- Success Animation --}}
    <div class="text-center mb-8">
        <div class="relative w-20 h-20 mx-auto mb-6">
            <span class="absolute inset-0 rounded-full bg-green-400/30 animate-ping"></span>
            <div class="relative w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                <ion-icon name="checkmark-circle-outline" class="text-5xl text-green-600 dark:text-green-400"></ion-icon>
            </div>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __('storefront.order_placed') }}</h1>
        <p class="text-gray-600 dark:text-gray-300">
            {{ __('storefront.your_order_number') }}:
            <span class="font-mono font-bold store-text-primary">#{{ $this->order->number }}</span>
        </p>
    </div>

    {{-- Order Summary Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <ion-icon name="receipt-outline" class="text-xl store-text-primary"></ion-icon>
            {{ __('storefront.order_summary') }}
        </h2>

        @if($this->order->items && $this->order->items->count())
            <div class="space-y-4 mb-5">
                @foreach($this->order->items as $item)
                    @php
                        $itemImagePath = $item->product?->images?->first()?->path ?? $item->variant?->images?->first()?->path;
                        $itemImage = $itemImagePath ? asset('storage/' . $itemImagePath) : asset('img/icons/noimg.png');
                        $productName = $item->product?->name ?? __('storefront.not_available');
                        $variantName = $item->variant?->name ?? '';
                        $quantity = $item->quantity ?? 1;
                        $price = $item->price ?? 0;
                    @endphp
                    <div class="flex items-center gap-3">
                        <img src="{{ $itemImage }}" alt="{{ $productName }}"
                             class="w-12 h-12 rounded-lg object-cover bg-gray-100 dark:bg-gray-700 shrink-0"
                             onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $productName }}</p>
                            @if($variantName)
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $variantName }}</p>
                            @endif
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                {{ currency($price) }} &times; {{ $quantity }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums">
                                {{ currency($price * $quantity) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('storefront.subtotal') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white tabular-nums">{{ currency($this->order->items->sum(fn($i) => $i->price * $i->quantity)) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('storefront.shipping') }}</span>
                    <span class="font-medium text-gray-900 dark:text-white tabular-nums">
                        @if((float) ($this->order->shipping_cost ?? 0) <= 0)
                            <span class="text-emerald-600 dark:text-emerald-400">{{ __('storefront.free') }}</span>
                        @else
                            {{ currency($this->order->shipping_cost) }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4 flex justify-between items-center">
                <span class="text-base font-semibold text-gray-900 dark:text-white">{{ __('storefront.total') }}</span>
                <span class="text-xl font-bold store-text-primary tabular-nums">{{ currency($this->order->total_amount ?? 0) }}</span>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                {{ __('storefront.not_available') }}
            </p>
            <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4 flex justify-between items-center">
                <span class="text-base font-semibold text-gray-900 dark:text-white">{{ __('storefront.total') }}</span>
                <span class="text-xl font-bold store-text-primary tabular-nums">{{ currency($this->order->total_amount ?? 0) }}</span>
            </div>
        @endif

        @if(($this->order->delivery_type ?? '') === 'stopdesk' && !empty($this->order->stopdeskPoint))
            <div class="mt-5 flex items-start gap-3 store-bg-primary-soft rounded-xl p-4">
                <div class="w-9 h-9 rounded-lg bg-white dark:bg-gray-700 flex items-center justify-center shrink-0 store-border-primary border">
                    <ion-icon name="business-outline" class="text-lg store-text-primary"></ion-icon>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('storefront.stop_desk') }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300 truncate">{{ $this->order->stopdeskPoint->name }}</p>
                </div>
            </div>
        @endif
    </div>

    @php
        $_cust = $this->order->customer ?? null;
        $_hasCustomerInfo = $_cust || !empty($this->order->address) || !empty($this->order->notes);
    @endphp
    @if($_hasCustomerInfo)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <ion-icon name="person-outline" class="text-xl store-text-primary"></ion-icon>
            {{ __('storefront.customer_information') }}
        </h2>
        <dl class="space-y-3 text-sm">
            @if($_cust && !empty($_cust->name))
                <div class="flex items-start gap-3">
                    <dt class="w-24 shrink-0 text-gray-500 dark:text-gray-400">{{ __('storefront.name') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-white break-words">{{ $_cust->name }}</dd>
                </div>
            @endif
            @if($_cust && !empty($_cust->phone))
                <div class="flex items-start gap-3">
                    <dt class="w-24 shrink-0 text-gray-500 dark:text-gray-400">{{ __('storefront.phone') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-white" dir="ltr">{{ $_cust->phone }}</dd>
                </div>
            @endif
            @if(($this->order->delivery_type ?? '') !== 'stopdesk')
                @if(!empty($this->order->address) || $this->order->city || $this->order->state)
                    <div class="flex items-start gap-3">
                        <dt class="w-24 shrink-0 text-gray-500 dark:text-gray-400">{{ __('storefront.shipping_address') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-white break-words">
                            @if(!empty($this->order->address))
                                {{ $this->order->address }}@if($this->order->city || $this->order->state),@endif
                            @endif
                            @if($this->order->city)
                                {{ $this->order->city->name }}@if($this->order->state),@endif
                            @endif
                            {{ $this->order->state?->name ?? '' }}
                        </dd>
                    </div>
                @endif
            @endif
            @if(!empty($this->order->notes))
                <div class="flex items-start gap-3">
                    <dt class="w-24 shrink-0 text-gray-500 dark:text-gray-400">{{ __('storefront.notes') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-white break-words">{{ $this->order->notes }}</dd>
                </div>
            @endif
        </dl>
        </div>
    @endif

    {{-- Next Steps Timeline --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
            <ion-icon name="git-commit-outline" class="text-xl store-text-primary"></ion-icon>
            {{ __('storefront.what_happens_next') }}
        </h2>
        <ol class="space-y-0">
            {{-- Step 1: Completed --}}
            <li class="relative flex items-start gap-4 pb-6">
                <span class="absolute start-[15px] top-8 bottom-0 w-0.5 store-bg-primary" aria-hidden="true"></span>
                <div class="relative w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center shrink-0 z-10">
                    <ion-icon name="checkmark-outline" class="text-base"></ion-icon>
                </div>
                <div class="pt-1">
                    <p class="text-sm font-semibold store-text-primary">{{ __('storefront.step_order_placed') }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">100%</p>
                </div>
            </li>
            {{-- Step 2: Pending --}}
            <li class="relative flex items-start gap-4 pb-6">
                <span class="absolute start-[15px] top-8 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                <div class="relative w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center text-xs font-bold shrink-0 z-10">2</div>
                <div class="pt-1">
                    <p class="text-sm font-semibold text-gray-400 dark:text-gray-500">{{ __('storefront.step_order_confirmed') }}</p>
                </div>
            </li>
            {{-- Step 3: Pending --}}
            <li class="relative flex items-start gap-4 pb-6">
                <span class="absolute start-[15px] top-8 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                <div class="relative w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center text-xs font-bold shrink-0 z-10">3</div>
                <div class="pt-1">
                    <p class="text-sm font-semibold text-gray-400 dark:text-gray-500">{{ __('storefront.step_out_for_delivery') }}</p>
                </div>
            </li>
            {{-- Step 4: Pending --}}
            <li class="relative flex items-start gap-4">
                <div class="relative w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center text-xs font-bold shrink-0 z-10">4</div>
                <div class="pt-1">
                    <p class="text-sm font-semibold text-gray-400 dark:text-gray-500">{{ __('storefront.step_delivered') }}</p>
                </div>
            </li>
        </ol>
    </div>

    {{-- Contact / CTA --}}
    <div class="text-center">
        <div class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-5">
            <ion-icon name="call-outline" class="text-base"></ion-icon>
            {{ __('storefront.we_will_contact_you') }}
        </div>
        <br>
        <a href="{{ route('storefront.home', ['store' => currentStore()?->slug ?? '']) }}"
           class="inline-flex items-center gap-2 store-btn-primary text-white font-semibold py-3 px-6 rounded-xl transition hover:shadow-lg">
            <ion-icon name="arrow-back-outline" class="text-lg"></ion-icon>
            {{ __('storefront.back_to_store') }}
        </a>
    </div>
</div>
