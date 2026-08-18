<?php

use App\Domains\Cart\Services\CartService;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Domains\Shipping\Services\ShippingCostCalculator;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Rule;
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

$rules = [
    'name'          => 'required|string|max:255',
    'phone'         => 'required|string|max:20',
    'email'         => 'nullable|email|max:255',
    'state_id'      => 'required|exists:states,id',
    'city_id'       => 'nullable|exists:cities,id',
    'address'       => 'nullable|string|max:1000',
    'delivery_type' => 'required|in:home,stopdesk',
    'payment_method' => 'required|in:cod',
    'notes'         => 'nullable|string|max:500',
    'selectedStopdesk' => 'required_if:delivery_type,stopdesk|nullable|string',
];

$getStateOptions = function () {
    return State::active()->orderBy('name')->get();
};

$getCityOptions = function () {
    if (! $this->state_id) {
        return collect();
    }
    return City::where('state_id', $this->state_id)->active()->orderBy('name')->get();
};

$getStopdeskPoints = function () {
    if (! $this->state_id || $this->delivery_type !== 'stopdesk') {
        return collect();
    }

    return StopdeskPoint::where('store_id', currentStoreId())
        ->where('state_id', $this->state_id)
        ->where('is_active', true)
        ->get();
};

$getShippingInfo = function () {
    $calculator = app(ShippingCostCalculator::class);
    $cart = app(CartService::class);

    $subtotal = $cart->getSubtotal(currentStoreId());
    $result = $calculator->calculate(
        currentStore(),
        $this->state_id ?: null,
        $this->city_id ?: null,
        $subtotal
    );

    return $result;
};

$submitOrder = function () {
    Validator::make($this->only([
        'name', 'phone', 'email', 'state_id', 'city_id',
        'address', 'delivery_type', 'payment_method', 'notes', 'selectedStopdesk',
    ]), $this->rules)->validate();

    $cartService = app(CartService::class);
    $storeId = currentStoreId();

    if ($cartService->isEmpty($storeId)) {
        session()->flash('error', __('storefront.cart_is_empty'));
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

        $order = Order::create([
            'store_id'     => $storeId,
            'user_id'      => auth()->id(),
            'customer_id'  => $customer->id,
            'status_id'    => $status?->id,
            'number'       => (new Order)->nextOrderNumber(),
            'total_amount' => $subtotal + $shippingCost,
            'state_id'     => $this->state_id,
            'city_id'      => $this->city_id,
            'address'      => $this->address,
            'delivery_type' => $this->delivery_type,
            'payment_method' => $this->payment_method,
            'shipping_cost' => $shippingCost,
            'notes'        => $this->notes,
            'phone_secondary' => $this->email,
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'store_id'            => $storeId,
                'order_id'            => $order->id,
                'product_variant_id'  => $item['variant_id'],
                'quantity'            => $item['quantity'],
                'price'               => $item['price'],
                'subtotal'            => $item['price'] * $item['quantity'],
            ]);
        }

        DB::commit();

        $cartService->clear($storeId);

        session()->flash('success', __('storefront.order_placed') . ' #' . $order->number);
        return redirect()->route('storefront.order.success', ['store' => currentStore()->slug, 'order' => $order->number]);
    } catch (\Exception $e) {
        DB::rollBack();
        session()->flash('error', __('storefront.failed_to_place_order'));
    }
};
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">
        {{ __('Checkout') }}
    </h1>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @php
        $states = $getStateOptions();
        $cities = $getCityOptions();
        $stopdesks = $getStopdeskPoints();
        $shipping = $getShippingInfo();
        $cartItems = app(\App\Domains\Cart\Services\CartService::class)->getItems(currentStoreId());
        $subtotal = app(\App\Domains\Cart\Services\CartService::class)->getSubtotal(currentStoreId());
    @endphp

    <form wire:submit="submitOrder" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left: Form --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Customer Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Customer Information') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Name') }} *</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Phone') }} *</label>
                        <input type="text" wire:model="phone" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Email') }}</label>
                        <input type="email" wire:model="email" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>
            </div>

            {{-- Delivery --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Delivery Information') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('State') }} *</label>
                        <select wire:model.live="state_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Select State') }}</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                        @error('state_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('City') }}</label>
                        <select wire:model.live="city_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Select City') }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Delivery Type --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Delivery Type') }}</label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" wire:model.live="delivery_type" value="home" class="peer sr-only">
                                <div class="border-2 rounded-lg p-3 text-center peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/30 border-gray-200 dark:border-gray-600 transition">
                                    <ion-icon name="home-outline" class="text-xl text-gray-600 dark:text-gray-300"></ion-icon>
                                    <p class="text-sm mt-1 font-medium">{{ __('Home Delivery') }}</p>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" wire:model.live="delivery_type" value="stopdesk" class="peer sr-only">
                                <div class="border-2 rounded-lg p-3 text-center peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/30 border-gray-200 dark:border-gray-600 transition">
                                    <ion-icon name="location-outline" class="text-xl text-gray-600 dark:text-gray-300"></ion-icon>
                                    <p class="text-sm mt-1 font-medium">{{ __('Stop Desk') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    @if($delivery_type === 'stopdesk' && $stopdesks->count())
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Select Stopdesk Point') }} *</label>
                            <select wire:model="selectedStopdesk" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('Select Point') }}</option>
                                @foreach($stopdesks as $point)
                                    <option value="{{ $point->id }}">{{ $point->name }} — {{ $point->address }}</option>
                                @endforeach
                            </select>
                            @error('selectedStopdesk') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Address') }}</label>
                        <textarea wire:model="address" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Notes') }}</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Order notes (optional)') }}"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Order Summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 sticky top-24">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Order Summary') }}</h2>

                <div class="space-y-3 mb-4">
                    @forelse($cartItems as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-300">
                                {{ $item['product_name'] }}
                                @if($item['variant_name']) <span class="text-gray-400">({{ $item['variant_name'] }})</span> @endif
                                × {{ $item['quantity'] }}
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ currency($item['price'] * $item['quantity']) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-sm">{{ __('Cart is empty') }}</p>
                    @endforelse
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('Subtotal') }}</span>
                        <span class="font-medium">{{ currency($subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('Shipping') }}</span>
                        <span class="font-medium">
                            @if($shipping['is_free'] ?? false)
                                {{ __('Free') }}
                            @elseif(($shipping['available'] ?? true))
                                {{ currency($shipping['cost'] ?? 0) }}
                            @else
                                <span class="text-red-500">{{ __('Not available') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 mt-3 pt-3">
                    <div class="flex justify-between">
                        <span class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Total') }}</span>
                        <span class="text-base font-bold text-gray-900 dark:text-white">
                            {{ currency($subtotal + ($shipping['cost'] ?? 0)) }}
                        </span>
                    </div>
                </div>

                <button
                    type="submit"
                    class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition disabled:opacity-50"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="submitOrder">{{ __('Place Order') }}</span>
                    <span wire:loading wire:target="submitOrder" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        {{ __('Placing...') }}
                    </span>
                </button>

                <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-3">
                    {{ __('Payment on delivery (COD)') }}
                </p>
            </div>
        </div>
    </form>
</div>
