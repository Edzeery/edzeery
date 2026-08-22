<?php

use App\Domains\Cart\Services\CartService;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Plan\Services\FeatureUsageService;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Domains\Shipping\Services\ShippingCostCalculator;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'name'         => '',
    'phone'        => '',
    'email'        => '',
    'state_id'     => '',
    'city_id'      => '',
    'address'      => '',
    'delivery_type' => 'home',
    'payment_method' => 'cod',
    'notes'        => '',
    'selectedStopdesk' => '',
]);

mount(function (): void {
    $this->name = auth()->user()?->name ?? '';
    $this->phone = auth()->user()?->phone ?? '';
    $this->email = auth()->user()?->email ?? '';
});

$submitOrder = function () {
    $validated = Validator::make($this->only([
        'name', 'phone', 'email', 'state_id', 'city_id',
        'address', 'delivery_type', 'payment_method', 'notes', 'selectedStopdesk',
    ]), [
        'name'          => 'required|string|max:255',
        'phone'         => 'required|string|max:20',
        'email'         => 'nullable|email|max:255',
        'state_id'      => 'required|exists:states,id',
        'city_id'       => 'required_if:delivery_type,home|nullable|exists:cities,id',
        'address'       => 'required_if:delivery_type,home|nullable|string|max:1000',
        'delivery_type' => 'required|in:home,stopdesk',
        'payment_method' => 'required|in:cod',
        'notes'         => 'nullable|string|max:500',
        'selectedStopdesk' => 'required_if:delivery_type,stopdesk|nullable|integer',
    ])->validate();

    $cartService = app(CartService::class);
    $storeId = currentStoreId();

    if ($cartService->isEmpty($storeId)) {
        $this->dispatch('swal', type: 'error', title: __('storefront.cart_is_empty'));
        return;
    }

    $items = $cartService->getItems($storeId);
    $calculator = app(ShippingCostCalculator::class);
    $shipping = $calculator->calculate(
        currentStore(),
        $this->state_id,
        $this->city_id,
        $cartService->getSubtotal($storeId)
    );

    $subtotal = $cartService->getSubtotal($storeId);
    $shippingCost = $shipping['available'] ? $shipping['cost'] : 0;

    DB::beginTransaction();

    try {
        $customer = Customer::firstOrCreate(
            ['store_id' => $storeId, 'phone' => $this->phone],
            [
                'name'      => $this->name,
                'email'     => $this->email,
                'address'   => $this->address,
                'state_id'  => $this->state_id,
                'city_id'   => $this->city_id,
                'status'    => true,
            ]
        );

        $status = Status::system()
            ->forType('order')
            ->where('key', 'pending')
            ->first();

        if (! $status) {
            DB::rollBack();
            $this->dispatch('swal', type: 'error', title: __('storefront.failed_to_place_order'));
            return;
        }

        $order = Order::create([
            'store_id'     => $storeId,
            'user_id'      => auth()->id(),
            'customer_id'  => $customer->id,
            'status_id'    => $status->id,
            'number'       => (new Order(['store_id' => $storeId]))->nextOrderNumber(),
            'total_amount' => $subtotal + $shippingCost,
            'state_id'     => $this->state_id,
            'city_id'      => $this->city_id,
            'address'      => $this->address,
            'delivery_type' => $this->delivery_type,
            'payment_method' => $this->payment_method,
            'shipping_cost' => $shippingCost,
            'notes'        => $this->notes,
            'stopdesk_point_id' => $this->delivery_type === 'stopdesk' ? $this->selectedStopdesk : null,
        ]);

        OrderStatusHistory::create([
            'order_id'  => $order->id,
            'status_id' => $status->id,
            'reason'    => 'Order placed via storefront',
        ]);

        foreach ($items as $item) {
            $variant = ProductVariant::find($item['variant_id']);
            OrderItem::create([
                'store_id'            => $storeId,
                'order_id'            => $order->id,
                'product_variant_id'  => $item['variant_id'],
                'product_id'          => $variant?->product_id,
                'quantity'            => $item['quantity'],
                'price'               => $item['price'],
                'subtotal'            => $item['price'] * $item['quantity'],
            ]);
        }

        $store = currentStore();
        $subscription = $store?->user?->latestSubscription();
        if ($subscription && $subscription->plan) {
            app(FeatureUsageService::class)->consume($subscription, 'daily_orders_limit');
        }

        DB::commit();

        $cartService->clear($storeId);

        // Auto-assign order
        try {
            app(OrderAssignmentService::class)->assign($order);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Order auto-assign failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        session()->flash('success', __('storefront.order_placed') . ' #' . $order->number);
        return redirect()->route('storefront.order.success', ['store' => currentStore()?->slug, 'order' => $order->number]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Illuminate\Support\Facades\Log::error('Order placement failed', [
            'store_id' => $storeId,
            'error' => $e->getMessage(),
        ]);
        $this->dispatch('swal', type: 'error', title: __('storefront.failed_to_place_order'));
    }
};
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
            {{ __('storefront.checkout') }}
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('storefront.complete_your_order') }}
        </p>
    </div>

    @php
        $cartService = app(CartService::class);
        $cartItems = $cartService->getItems(currentStoreId())->toArray();
        $cartCount = $cartService->getCount(currentStoreId());
        $cartSubtotal = $cartService->getSubtotal(currentStoreId());

        // Enrich cart items with images and slugs
        if (!empty($cartItems)) {
            $variantIds = array_column($cartItems, 'variant_id');
            $variants = \App\Models\Products\ProductVariant::with('product.images')
                ->whereIn('id', $variantIds)->get()->keyBy('id');
            foreach ($cartItems as &$ci) {
                $v = $variants[$ci['variant_id']] ?? null;
                $p = $v?->product;
                $img = $p?->images?->first()?->path;
                $ci['image'] = $img ? asset('storage/' . $img) : asset('img/icons/noimg.png');
                $ci['slug'] = $p?->slug ?? '';
            }
            unset($ci);
        }

        $states = State::active()->orderBy('name')->get();
        $cities = $this->state_id ? City::where('state_id', $this->state_id)->active()->orderBy('name')->get() : collect();
        $stopdesks = ($this->state_id && $this->delivery_type === 'stopdesk')
            ? StopdeskPoint::where('store_id', currentStoreId())->where('state_id', $this->state_id)->where('is_active', true)->get()
            : collect();
        $calculator = app(ShippingCostCalculator::class);
        $shippingInfo = $calculator->calculate(currentStore(), $this->state_id ?: null, $this->city_id ?: null, $cartSubtotal);
    @endphp

    {{-- Back to Cart --}}
    <div class="mb-6">
        <a href="{{ route('storefront.home', ['store' => currentStore()?->slug ?? '']) }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
            <ion-icon name="arrow-back-outline" class="text-base"></ion-icon>
            {{ __('storefront.back_to_store') }}
        </a>
    </div>

    {{-- Progress Steps --}}
    <div class="mb-8 flex items-center justify-between max-w-md">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center text-sm font-bold">
                <ion-icon name="checkmark-outline" class="text-base"></ion-icon>
            </div>
            <span class="ms-2 text-sm font-medium store-text-primary hidden sm:inline">{{ __('storefront.cart') }}</span>
        </div>
        <div class="flex-1 h-0.5 mx-2 sm:mx-3 store-bg-primary"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center text-sm font-bold">2</div>
            <span class="ms-2 text-sm font-medium store-text-primary hidden sm:inline">{{ __('storefront.delivery') }}</span>
        </div>
        <div class="flex-1 h-0.5 mx-2 sm:mx-3 bg-gray-200 dark:bg-gray-700"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
            <span class="ms-2 text-sm font-medium text-gray-400 dark:text-gray-500 hidden sm:inline">{{ __('storefront.confirm') }}</span>
        </div>
    </div>

    <form wire:submit="submitOrder" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Customer Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <ion-icon name="person-outline" class="text-xl store-text-primary"></ion-icon>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('storefront.customer_information') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('storefront.who_is_receiving') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.name') }} *</label>
                        <input type="text" wire:model="name"
                            placeholder="{{ __('storefront.full_name') }}"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   placeholder:text-gray-400 dark:placeholder:text-gray-500
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200" />
                        @error('name') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.phone') }} *</label>
                        <input type="text" wire:model="phone"
                            placeholder="0XXX XX XX XX"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   placeholder:text-gray-400 dark:placeholder:text-gray-500
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200" />
                        @error('phone') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.email') }}</label>
                        <input type="email" wire:model="email"
                            placeholder="{{ __('storefront.email_optional') }}"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   placeholder:text-gray-400 dark:placeholder:text-gray-500
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200" />
                    </div>
                </div>
            </div>

            {{-- Delivery --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <ion-icon name="car-outline" class="text-xl store-text-primary"></ion-icon>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('storefront.delivery_information') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('storefront.where_to_deliver') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.state') }} *</label>
                        <select wire:model.live="state_id"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200">
                            <option value="">{{ __('storefront.select_state') }}</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                        @error('state_id') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.city') }}</label>
                        <select wire:model.live="city_id"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200">
                            <option value="">{{ __('storefront.select_city') }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('storefront.delivery_type') }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="delivery_type" value="home" class="peer sr-only">
                                <div class="border-2 rounded-xl p-4 text-center peer-checked:border-[var(--store-primary)] peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_10%,transparent)] dark:peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_20%,transparent)] border-gray-200 dark:border-gray-600 transition">
                                    <ion-icon name="home-outline" class="text-2xl text-gray-500 dark:text-gray-400"></ion-icon>
                                    <p class="text-sm mt-1 font-medium text-gray-700 dark:text-gray-300">{{ __('storefront.home_delivery') }}</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="delivery_type" value="stopdesk" class="peer sr-only">
                                <div class="border-2 rounded-xl p-4 text-center peer-checked:border-[var(--store-primary)] peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_10%,transparent)] dark:peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_20%,transparent)] border-gray-200 dark:border-gray-600 transition">
                                    <ion-icon name="location-outline" class="text-2xl text-gray-500 dark:text-gray-400"></ion-icon>
                                    <p class="text-sm mt-1 font-medium text-gray-700 dark:text-gray-300">{{ __('storefront.stop_desk') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    @if($this->delivery_type === 'stopdesk' && $stopdesks->count())
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.select_stopdesk_point') }} *</label>
                            <select wire:model="selectedStopdesk"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200">
                                <option value="">{{ __('storefront.select_point') }}</option>
                                @foreach($stopdesks as $point)
                                    <option value="{{ $point->id }}">{{ $point->name }} — {{ $point->address }}</option>
                                @endforeach
                            </select>
                            @error('selectedStopdesk') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($this->delivery_type === 'home')
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.address') }} *</label>
                            <textarea wire:model="address" rows="2"
                                placeholder="{{ __('storefront.address_placeholder') }}"
                                style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                       bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                       placeholder:text-gray-400 dark:placeholder:text-gray-500
                                       shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                       transition-all duration-200 resize-none"></textarea>
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.notes') }}</label>
                            <textarea wire:model="notes" rows="2"
                            placeholder="{{ __('storefront.order_notes_optional') }}"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   placeholder:text-gray-400 dark:placeholder:text-gray-500
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200 resize-none"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Order Summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm sticky top-24">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('storefront.order_summary') }}</h2>

                <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                    @forelse($cartItems as $item)
                        <div class="flex items-center gap-3">
                            <img src="{{ $item['image'] }}" alt="{{ $item['product_name'] }}"
                                 class="w-10 h-10 rounded-lg object-cover bg-gray-100 dark:bg-gray-700 shrink-0"
                                 onerror="this.onerror=null;this.src='{{ asset('img/icons/noimg.png') }}'">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item['product_name'] }}</p>
                                @if($item['variant_name'])
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['variant_name'] }}</p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white tabular-nums">{{ currency($item['price'] * $item['quantity']) }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">× {{ $item['quantity'] }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-sm text-center py-4">{{ __('storefront.cart_is_empty') }}</p>
                    @endforelse
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('storefront.subtotal') }} ({{ $cartCount }} {{ __('storefront.items') }})</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ currency($cartSubtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('storefront.shipping') }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            @if($shippingInfo['is_free'] ?? false)
                                <span class="text-emerald-600 dark:text-emerald-400">{{ __('storefront.free') }}</span>
                            @elseif(($shippingInfo['available'] ?? true))
                                {{ currency($shippingInfo['cost'] ?? 0) }}
                            @else
                                <span class="text-red-500 dark:text-red-400">{{ __('storefront.not_available') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4">
                    <div class="flex justify-between">
                        <span class="text-base font-semibold text-gray-900 dark:text-white">{{ __('storefront.total') }}</span>
                        <span class="text-xl font-bold store-text-primary">
                            {{ currency($cartSubtotal + ($shippingInfo['cost'] ?? 0)) }}
                        </span>
                    </div>
                </div>

                <button
                    type="submit"
                    class="mt-6 w-full store-btn-primary text-white font-semibold py-3.5 px-4 rounded-xl transition disabled:opacity-50 flex items-center justify-center gap-2"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="submitOrder" class="flex items-center gap-2">
                        <ion-icon name="lock-closed-outline" class="text-lg"></ion-icon>
                        {{ __('storefront.place_order') }}
                    </span>
                    <span wire:loading wire:target="submitOrder" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        {{ __('storefront.placing') }}
                    </span>
                </button>

                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                    <ion-icon name="shield-checkmark-outline" class="text-base"></ion-icon>
                    <span>{{ __('storefront.secure_checkout') }}</span>
                </div>
            </div>
        </div>
    </form>
</div>
