<?php

use App\Domains\Cart\Services\CartService;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Plan\Services\FeatureUsageService;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\DeliveryRateCity;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\ShippingRate;
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
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\computed;
use function Livewire\Volt\updated;

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
    'selectedProvider' => '',
]);

mount(function (): void {
    $this->name = auth()->user()?->name ?? '';
    $this->phone = auth()->user()?->phone ?? '';
    $this->email = auth()->user()?->email ?? '';

    // Single-carrier stores skip the company picker entirely.
    if ($this->availableProviders->count() === 1) {
        $this->selectedProvider = (string) $this->availableProviders->first()->id;
    }
});

updated(['state_id'], function (): void {
    $this->city_id = '';

    // A new wilaya invalidates a previously picked desk unless the desk belongs
    // to that same wilaya (fires again when the office auto-fills the commune).
    if ($this->selectedStopdesk) {
        $officeState = StopdeskPoint::whereKey($this->selectedStopdesk)->value('state_id');
        if ($officeState !== null && (string) $officeState !== (string) $this->state_id) {
            $this->selectedStopdesk = '';
        }
        return;
    }

    if ($this->delivery_type !== 'stopdesk' || ! $this->state_id) {
        return;
    }

    // A wilaya with a single office auto-picks itself.
    $points = $this->officesForSelection();
    if ($points->count() === 1) {
        $first = $points->first();
        $this->selectedStopdesk = (string) $first->id;
        if ($first->city_id) {
            $this->city_id = (string) $first->city_id;
        }
    }
});

updated(['selectedProvider'], function (): void {
    $this->state_id = '';
    $this->city_id = '';
    $this->selectedStopdesk = '';
});

updated(['delivery_type'], function (): void {
    $this->state_id = '';
    $this->city_id = '';
    $this->selectedStopdesk = '';
});

updated(['city_id'], function (): void {
    $this->selectedStopdesk = '';

    if ($this->delivery_type !== 'stopdesk' || ! $this->state_id || ! $this->city_id) {
        return;
    }

    $points = $this->officesForSelection();
    if ($points->count() === 1) {
        $this->selectedStopdesk = (string) $points->first()->id;
    }
});

updated(['selectedStopdesk'], function (): void {
    if ($this->delivery_type !== 'stopdesk' || ! $this->selectedStopdesk) {
        return;
    }

    // Picking an office carries its own geo scope: the commune (and wilaya for
    // wilaya-wide offices) are derived from the desk so the stored address and
    // the validation scope stay truthful without an extra commune selection.
    $office = StopdeskPoint::whereKey($this->selectedStopdesk)->first(['id', 'state_id', 'city_id']);
    if (! $office) {
        return;
    }

    if ($office->state_id !== null && (string) $office->state_id !== (string) $this->state_id) {
        $this->state_id = (string) $office->state_id;
    }
    $this->city_id = $office->city_id !== null ? (string) $office->city_id : '';
});

$availableProviders = computed(function (): \Illuminate\Support\Collection {
    return ShippingProvider::where('store_id', currentStoreId())
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name', 'flat_rate']);
});

$paymentMethods = computed(function (): array {
    $store = currentStore();
    $settings = $store?->settings;
    $methods = $settings?->payment_methods ?? ['cod'];
    if (! is_array($methods) || empty($methods)) {
        return ['cod'];
    }
    return $methods;
});

$officesForSelection = function (): \Illuminate\Support\Collection {
    $providerId = $this->selectedProvider;
    $query = StopdeskPoint::where('store_id', currentStoreId())
        ->where('state_id', $this->state_id)
        ->where('is_active', true);

    if ($providerId) {
        $query->where('shipping_provider_id', $providerId);
    }

// All offices of the chosen wilaya are offered, whatever their commune.
    // Desks sort by their external code (empty codes last) so NOEST families
    // like 02A / 02B stay grouped in order.
    return $query
        ->with('city')
        ->orderByRaw("(external_code IS NULL OR external_code = '') ASC")
        ->orderBy('external_code')
        ->orderBy('name')
        ->get();
};

// Shared shape for the office select payloads: the code chip, the desk name,
// the commune as hint and the address + phone lines as extra details.
$formatOfficeOptions = function (\Illuminate\Support\Collection $stopdesks): \Illuminate\Support\Collection {
    return $stopdesks->map(function ($office) {
        return [
            'value' => (string) $office->id,
            'label' => $office->name,
            'hint'  => filled($office->city?->name) ? $office->city->name : null,
            'code'  => filled($office->external_code) ? (string) $office->external_code : null,
            'extra' => array_values(array_filter([
                filled($office->address) ? $office->address : null,
                filled($office->phone) ? $office->phone : null,
            ], fn ($v) => $v !== null)),
        ];
    })->values();
};

// Stopdesk offices scoped to the current (wilaya + carrier): fetched lazily by
// the select on first open per scope, then cached client-side.
$stopdeskSelectOptions = function (string $scope = ''): array {
    return $this->formatOfficeOptions($this->officesForSelection())->all();
};

// Communes of the chosen wilaya, scoped to the delivery type: the checkout
// still sends intensity-reduced payloads even after the select moved to lazy
// loading, so the city list lives behind the same on-open fetch as the desks.
$citiesForSelection = function (): \Illuminate\Support\Collection {
    $storeId = currentStoreId();
    $providers = $this->availableProviders;
    $hasProviders = $providers->isNotEmpty();
    $providerId = $this->selectedProvider
        ? (string) $this->selectedProvider
        : ($providers->first()?->id ? (string) $providers->first()->id : null);

    $cities = collect();
    if (! $this->state_id) {
        return $cities;
    }

    if (! $hasProviders || ! $providerId) {
        return City::where('state_id', $this->state_id)->active()->orderBy('name')->get();
    }

    if ($this->delivery_type === 'stopdesk') {
        $hasGlobalOffice = StopdeskPoint::where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('state_id', $this->state_id)
            ->whereNull('city_id')
            ->where('is_active', true)
            ->exists();
        if ($hasGlobalOffice) {
            return City::where('state_id', $this->state_id)->active()->orderBy('name')->get();
        }
        $officeCityIds = StopdeskPoint::where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('state_id', $this->state_id)
            ->whereNotNull('city_id')
            ->where('is_active', true)
            ->distinct()
            ->pluck('city_id');
        return City::whereIn('id', $officeCityIds)->active()->orderBy('name')->get();
    }

    $pricedCityIds = DeliveryRateCity::where('store_id', $storeId)
        ->where('shipping_provider_id', $providerId)
        ->where('state_id', $this->state_id)
        ->where('is_active', true)
        ->distinct()
        ->pluck('city_id');
    $legacyCityIds = ShippingRate::where('store_id', $storeId)
        ->where('shipping_provider_id', $providerId)
        ->where('state_id', $this->state_id)
        ->whereNotNull('city_id')
        ->where('is_active', true)
        ->distinct()
        ->pluck('city_id');
    $scopedCityIds = $pricedCityIds->merge($legacyCityIds)->unique()->values();

    if ($scopedCityIds->isEmpty()) {
        return City::where('state_id', $this->state_id)->active()->orderBy('name')->get();
    }
    return City::whereIn('id', $scopedCityIds)->active()->orderBy('name')->get();
};

$citiesSelectOptions = function (string $scope = ''): array {
    return $this->citiesForSelection()->map(fn ($city) => [
        'value' => (string) $city->id,
        'label' => $city->name,
        'hint'  => null,
        'code'  => null,
    ])->values()->all();
};

$quoteShipping = function (float $subtotal, array $shippingProductIds): array {
    return app(ShippingCostCalculator::class)->calculate(
        currentStore(),
        $this->state_id ?: null,
        $this->city_id ?: null,
        $subtotal,
        $shippingProductIds,
        $this->selectedProvider ?: null,
        $this->delivery_type,
    );
};

$submitOrder = function () {
    // Rate limit: 10 orders per minute per store+IP
    $rateLimitKey = 'storefront-order:' . currentStoreId() . ':' . request()->ip();
    if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
        $seconds = RateLimiter::availableIn($rateLimitKey);
        $this->dispatch('edz-notice', tone: 'danger', title: __('storefront.rate_limited', ['seconds' => $seconds]));
        return;
    }
    RateLimiter::hit($rateLimitKey, 60);

    $methods = $this->paymentMethods;
    $providers = $this->availableProviders;
    $hasProviders = $providers->isNotEmpty();

    $rules = [
        'name'          => 'required|string|max:255',
        'phone'         => 'required|string|max:20|regex:/^0[5-7]\d{8}$/',
        'email'         => 'nullable|email|max:255',
        'state_id'      => 'required|exists:states,id',
        'city_id'       => $this->delivery_type === 'home' ? 'required|exists:cities,id' : 'nullable|exists:cities,id',
        'address'       => 'required_if:delivery_type,home|nullable|string|max:1000',
        'delivery_type' => 'required|in:home,stopdesk',
        'payment_method' => 'required|in:' . implode(',', $this->paymentMethods),
        'notes'         => 'nullable|string|max:500',
        'selectedStopdesk' => ['nullable', 'string', 'exists:stopdesk_points,id'],
    ];

    if ($hasProviders) {
        $rules['selectedProvider'] = [
            'required',
            Rule::exists('shipping_providers', 'id')->where('store_id', currentStoreId()),
        ];
    }

    $validated = Validator::make($this->only([
        'name', 'phone', 'email', 'state_id', 'city_id',
        'address', 'delivery_type', 'payment_method', 'notes', 'selectedStopdesk', 'selectedProvider',
    ]), $rules)->validate();

    $cartService = app(CartService::class);
    $storeId = currentStoreId();

    if ($cartService->isEmpty($storeId)) {
        $this->dispatch('edz-notice', tone: 'danger', title: __('storefront.cart_is_empty'));
        return;
    }

    $items = $cartService->getItems($storeId);
    $subtotal = $cartService->getSubtotal($storeId);
    $shippingCost = 0;

    $variantIds = collect($items)->pluck('variant_id')->filter()->values()->all();
    $shippingProductIds = ProductVariant::where('store_id', $storeId)
        ->whereIn('id', $variantIds)
        ->pluck('product_id')->filter()->unique()->values()->all();

// Office pickup: the drawer must already belong to the selected scope
        // (store, carrier, wilaya). The commune is derived from the desk itself
        // and never re-checked — every office of the wilaya is offered.
        if ($this->delivery_type === 'stopdesk') {
            if (! filled($this->selectedStopdesk)) {
                $this->dispatch('edz-notice', tone: 'danger', title: __('storefront.select_office_required'));
                return;
            }

            $office = StopdeskPoint::where('store_id', $storeId)
                ->where('id', $this->selectedStopdesk)
                ->first();

            if (! $office
                || (string) $office->state_id !== (string) $this->state_id
                || ($this->selectedProvider && (string) $office->shipping_provider_id !== (string) $this->selectedProvider)) {
                $this->dispatch('edz-notice', tone: 'danger', title: __('storefront.select_office_required'));
                return;
            }
        }

    $shipping = $this->quoteShipping($subtotal, $shippingProductIds);

    if (! ($shipping['available'] ?? false)) {
        // Office pickup without a published courier price stays free.
        if ($this->delivery_type === 'stopdesk') {
            $shippingCost = 0;
        } else {
            $this->dispatch('edz-notice', tone: 'danger', title: __('storefront.shipping_not_available'));
            return;
        }
    } else {
        $shippingCost = (float) ($shipping['cost'] ?? 0);
    }

    $deliveryAddress = $this->address;
    if ($this->delivery_type === 'stopdesk') {
        $office = StopdeskPoint::with('city')->find($this->selectedStopdesk);
        $cityName = $office?->city?->name
            ?? City::where('id', $this->city_id)->first()?->name
            ?? '';
        $deliveryAddress = trim(($office?->name ?? '') . ($cityName ? ' — ' . $cityName : ''));
    }

    DB::beginTransaction();

    try {
        $customer = Customer::firstOrCreate(
            ['store_id' => $storeId, 'phone' => $this->phone],
            [
                'name'      => $this->name,
                'email'     => $this->email,
                'address'   => filled($deliveryAddress) ? $deliveryAddress : null,
                // Stopdesk orders legitimately skip city/address: never send
                // empty strings into foreign-key columns.
                'state_id'  => filled($this->state_id) ? $this->state_id : null,
                'city_id'   => filled($this->city_id) ? $this->city_id : null,
                'status'    => true,
            ]
        );

        $status = Status::system()
            ->forType('order')
            ->where('key', 'pending')
            ->first();

        if (! $status) {
            DB::rollBack();
            $this->dispatch('edz-notice', tone: 'danger', title: __('storefront.failed_to_place_order'));
            return;
        }

        $order = Order::create([
            'store_id'     => $storeId,
            'user_id'      => auth()->id(),
            'customer_id'  => $customer->id,
            'status_id'    => $status->id,
            'number'       => (new Order(['store_id' => $storeId]))->nextOrderNumber(),
            'total_amount' => $subtotal + $shippingCost,
            'state_id'     => filled($this->state_id) ? $this->state_id : null,
            'city_id'      => filled($this->city_id) ? $this->city_id : null,
            'address'      => filled($deliveryAddress) ? $deliveryAddress : null,
            'delivery_type' => $this->delivery_type,
            'payment_method' => $this->payment_method,
            'shipping_cost' => $shippingCost,
            'shipping_provider_id' => filled($this->selectedProvider) ? $this->selectedProvider : null,
            'notes'        => filled($this->notes) ? $this->notes : null,
            'stopdesk_point_id' => $this->delivery_type === 'stopdesk' && filled($this->selectedStopdesk) ? $this->selectedStopdesk : null,
        ]);

        OrderStatusHistory::create([
            'order_id'  => $order->id,
            'status_id' => $status->id,
            'reason'    => 'Order placed via storefront',
        ]);

        $store = currentStore();
        $tracksInventory = \App\Domains\Cart\Support\OrderRules::tracksInventory($store);
        $allowsBackorder = \App\Domains\Cart\Support\OrderRules::allowsBackorder($store);

        foreach ($items as $item) {
            $variant = ProductVariant::where('store_id', $storeId)
                ->find($item['variant_id']);

            if (! $variant) {
                throw new \Exception(__('storefront.invalid_variant', ['id' => $item['variant_id']]));
            }

            OrderItem::create([
                'store_id'            => $storeId,
                'order_id'            => $order->id,
                'product_variant_id'  => $item['variant_id'],
                'product_id'          => $variant?->product_id,
                'quantity'            => $item['quantity'],
                'price'               => $variant->price,
                'subtotal'            => $variant->price * $item['quantity'],
            ]);

            // Stock check only — no movement yet. Movements are created by
            // the OrderObserver when the status transitions (confirmed → RESERVE).
            if ($tracksInventory && ! $allowsBackorder) {
                $available = (int) ($variant->stock ?? 0);

                if ($available < (int) $item['quantity']) {
                    throw new \Exception(__('storefront.out_of_stock'));
                }
            }
        }

        // Recalculate total from DB prices (not session prices)
        $dbSubtotal = $order->items()->sum('subtotal');
        $order->update(['total_amount' => $dbSubtotal + $shippingCost]);

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
        return redirect()->route('storefront.order.success', ['store' => currentStore()?->slug, 'order' => $order->id]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Illuminate\Support\Facades\Log::error('Order placement failed', [
            'store_id' => $storeId,
            'error' => $e->getMessage(),
        ]);
        $this->dispatch('edz-notice', tone: 'danger', title: __('storefront.failed_to_place_order'));
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
        $storeId = currentStoreId();
        $cartService = app(CartService::class);
        $cartItems = $cartService->getItems($storeId)->toArray();
        $cartCount = $cartService->getCount($storeId);
        $cartSubtotal = $cartService->getSubtotal($storeId);

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

        $providers = $this->availableProviders;
        $hasProviders = $providers->isNotEmpty();
        $isSingleProvider = $providers->count() === 1;

        if ($isSingleProvider) {
            $this->selectedProvider = (string) $providers->first()->id;
        }
        $providerId = $this->selectedProvider
            ? (string) $this->selectedProvider
            : ($providers->first()?->id ? (string) $providers->first()->id : null);

        $providerFlat = $providers->firstWhere('id', $providerId)?->flat_rate ?? null;

        // Wilayas scoped to (carrier + delivery type): office-bearing states for
        // stopdesk, announced home coverage otherwise. A flat-rate carrier (or a
        // legacy store without carriers) covers every wilaya.
        $states = collect();
        if (! $hasProviders || ! $providerId) {
            $states = State::active()->orderedByCode()->get();
        } elseif ($this->delivery_type === 'stopdesk') {
            $officeStateIds = DeliveryRate::where('store_id', $storeId)
                ->where('shipping_provider_id', $providerId)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNotNull('office_cost')->orWhereNotNull('free_above'))
                ->distinct()
                ->pluck('state_id');
            $pointStateIds = StopdeskPoint::where('store_id', $storeId)
                ->where('shipping_provider_id', $providerId)
                ->where('is_active', true)
                ->whereNotNull('state_id')
                ->distinct()
                ->pluck('state_id');
            $stateIds = $officeStateIds->merge($pointStateIds)->unique()->values();
            $states = State::whereIn('id', $stateIds)->active()->orderedByCode()->get();
        } else {
            $homeStateIds = DeliveryRate::where('store_id', $storeId)
                ->where('shipping_provider_id', $providerId)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNotNull('home_cost')->orWhereNotNull('free_above'))
                ->distinct()
                ->pluck('state_id');
            $legacyStateIds = ShippingRate::where('store_id', $storeId)
                ->where('shipping_provider_id', $providerId)
                ->where('is_active', true)
                ->distinct()
                ->pluck('state_id');
            $stateIds = $homeStateIds->merge($legacyStateIds)->unique()->values();

            if ($stateIds->isEmpty() || $providerFlat !== null) {
                $states = State::active()->orderedByCode()->get();
            } else {
                $states = State::whereIn('id', $stateIds)->active()->orderedByCode()->get();
            }
        }

        // Communes scoped to (carrier + wilaya + delivery type). Stopdesk lists
        // only communes with an office; home falls back to every commune of the
        // wilaya when no per-commune pricing narrows it down.
        $cities = $this->citiesForSelection();

        // Offices: a single one auto-picks itself (shown as a compact card),
        // several become a picker, none keeps the explanatory note visible.
        $stopdesks = collect();
        if ($this->state_id && $this->delivery_type === 'stopdesk') {
            $stopdesks = $this->officesForSelection();
        }

        $officeOptions = $this->formatOfficeOptions($stopdesks);

        // Seeds for the lazily-fed lists: only the currently selected option so
        // the initial HTML never carries the whole (wilaya-wide) option set.
        $officeSeed = $this->selectedStopdesk
            ? $officeOptions->filter(fn ($o) => (string) $o['value'] === (string) $this->selectedStopdesk)->values()
            : collect();
        $citySeed = $this->city_id
            ? collect([[
                'value' => (string) $this->city_id,
                'label' => (string) ($cities->first(fn ($c) => (string) $c->id === (string) $this->city_id)?->name ?? ''),
                'hint' => null,
                'code' => null,
            ]])
            : collect();

        $shippingProductIds = ! empty($variants)
            ? $variants->pluck('product_id')->filter()->unique()->values()->all()
            : [];
        $shippingInfo = $this->quoteShipping($cartSubtotal, $shippingProductIds);
        if ($this->delivery_type === 'stopdesk' && ! ($shippingInfo['available'] ?? false)) {
            $shippingInfo = ['cost' => 0, 'is_free' => true, 'available' => true];
        }
        $paymentMethods = $this->paymentMethods;
    @endphp

    {{-- Back to Cart --}}
    <div class="mb-6">
        <a href="{{ route('storefront.home', ['store' => currentStore()?->slug ?? '']) }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
            <x-edz.icon name="arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}" class="w-4 h-4 text-base" />
            {{ __('storefront.back_to_store') }}
        </a>
    </div>

    {{-- Progress Steps --}}
    <div class="mb-8 flex items-center justify-between max-w-md">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center text-sm font-bold">

                <x-status-icon domain="order" status="completed" set="bi" />
            </div>
            <span class="ms-2 text-sm font-medium store-text-primary hidden sm:inline">{{ __('storefront.cart') }}</span>
        </div>
        <div class="flex-1 h-0.5 mx-2 sm:mx-3 store-bg-primary"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center text-sm font-bold">
                <x-status-icon domain="order" status="confirmed" set="bi" />
            </div>
            <span class="ms-2 text-sm font-medium store-text-primary hidden sm:inline">{{ __('storefront.delivery') }}</span>
        </div>
        <div class="flex-1 h-0.5 mx-2 sm:mx-3 store-bg-primary"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center text-sm font-bold">3</div>
            <span class="ms-2 text-sm font-medium store-text-primary hidden sm:inline">{{ __('storefront.confirm') }}</span>
        </div>
    </div>

    <form wire:submit="submitOrder" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Customer Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <x-edz.icon name="user" class="text-xl store-text-primary w-5 h-5 " />
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
                        <input type="text" wire:model="phone" name="phone"
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
                        @error('email') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Delivery --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <x-edz.icon name="truck" class="text-xl store-text-primary w-5 h-5" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('storefront.delivery_information') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('storefront.where_to_deliver') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- 1. Shipping company offered by this store --}}
                    @if ($hasProviders)
                        @if ($isSingleProvider)
                            <div class="sm:col-span-2 flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3">
                                <x-edz.icon name="truck" class="text-xl store-text-primary w-5 h-5 shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ __('storefront.shipping_via') }}</p>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $providers->first()->name }}</p>
                                </div>
                            </div>
                            <input type="hidden" wire:model.live="selectedProvider" value="{{ $providers->first()->id }}" />
                        @else
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.company') }} *</label>
                                <x-storefront.select :options="$providers" option-value="id" option-label="name"
                                    wire:model.live="selectedProvider"
                                    :search="true"
                                    search-placeholder="{{ __('storefront.search_company') }}"
                                    placeholder="{{ __('storefront.select_company') }}"
                                    icon="business" role="company-select" />
                                @error('selectedProvider') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    @endif

                    {{-- 2. Delivery type --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('storefront.delivery_type') }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="delivery_type" value="home" class="peer sr-only">
                                <div class="border-2 rounded-xl p-4 text-center peer-checked:border-[var(--store-primary)] peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_10%,transparent)] dark:peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_20%,transparent)] border-gray-200 dark:border-gray-600 transition">
                                    <x-edz.icon name="home" class="text-2xl text-gray-500 dark:text-gray-400 w-8 h-8 mx-auto" />
                                    <p class="text-sm mt-1 font-medium text-gray-700 dark:text-gray-300">{{ __('storefront.home_delivery') }}</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="delivery_type" value="stopdesk" class="peer sr-only">
                                <div class="border-2 rounded-xl p-4 text-center peer-checked:border-[var(--store-primary)] peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_10%,transparent)] dark:peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_20%,transparent)] border-gray-200 dark:border-gray-600 transition">
                                    <x-edz.icon name="map-pin" class="text-2xl text-gray-500 dark:text-gray-400  w-8 h-8 mx-auto" />
                                    <p class="text-sm mt-1 font-medium text-gray-700 dark:text-gray-300">{{ __('storefront.stop_desk') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 3. Wilaya scoped to the carrier --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.state') }} *</label>
                        <x-storefront.select :options="$states" option-value="id" option-label="name" option-code="state_code"
                            wire:model.live="state_id"
                            :search="true"
                            search-placeholder="{{ __('storefront.search') }}"
                            placeholder="{{ __('storefront.select_state') }}"
                            :disabled="! $hasProviders || ! $providerId || $states->isEmpty()"
                            icon="map" role="state-select" />
                        @if ($hasProviders && ! $providerId)
                            <p class="text-amber-600 dark:text-amber-400 text-xs mt-1.5">{{ __('storefront.select_company_first') }}</p>
                        @endif
                        @error('state_id') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    {{-- 4. Commune: priced communes for home only — stopdesk derives it from the desk --}}
                    @if ($this->delivery_type === 'home')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.city') }}</label>
                            <x-storefront.select :options="$citySeed" option-value="value" option-label="label"
                                wire:model.live="city_id"
                                lazy source="citiesSelectOptions"
                                :scope="'s' . ($this->state_id ?: '') . '|dt' . $this->delivery_type"
                                :search="true"
                                search-placeholder="{{ __('storefront.search') }}"
                                placeholder="{{ __('storefront.select_city') }}"
                                :disabled="! $this->state_id"
                                icon="location" role="city-select" />
                            @error('city_id') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- 5. Office / Address --}}
                    @if($this->delivery_type === 'stopdesk')
                        <div class="sm:col-span-2">
                            @if($stopdesks->count() === 1)
                                @php $singleOffice = $stopdesks->first(); @endphp
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.delivery_office') }}</label>
                                <div class="rounded-xl border border-[color-mix(in_srgb,var(--store-primary)_30%,transparent)] bg-[color-mix(in_srgb,var(--store-primary)_8%,transparent)] p-4" role="office-card">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center shrink-0">
                                            <x-edz.icon name="business" class="text-xl store-text-primary w-5 h-5" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                @if (filled($singleOffice->external_code))
                                                    <span class="inline-flex items-center justify-center min-w-6 px-1.5 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-[11px] font-semibold leading-none tabular-nums align-middle mr-1.5">{{ $singleOffice->external_code }}</span>
                                                @endif
                                                {{ $singleOffice->name }}
                                            </p>
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 shrink-0">
                                                <x-edz.icon name="checkmark-circle" class="w-4 h-4 text-base" />
                                                {{ __('storefront.deliver_to_this_office') }}
                                            </span>
                                        </div>
                                            <div class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                                @if($singleOffice->city?->name)
                                                    <p class="flex items-center gap-1.5"><x-edz.icon name="location" class="text-sm w-3.5 h-3.5" /> {{ $singleOffice->city->name }}</p>
                                                @endif
                                                @if($singleOffice->address)
                                                    <p class="flex items-center gap-1.5"><x-edz.icon name="map" class="text-sm w-3.5 h-3.5" /> {{ $singleOffice->address }}</p>
                                                @endif
                                                @if($singleOffice->phone)
                                                    <p class="flex items-center gap-1.5" dir="ltr"><x-edz.icon name="call" class="text-sm w-3.5 h-3.5" /> {{ $singleOffice->phone }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" value="{{ $singleOffice->id }}" data-role="selected-office" />
                            @elseif($stopdesks->count() > 1)
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('storefront.select_stopdesk_point') }}</label>
                                <x-storefront.select :options="$officeSeed" option-value="value" option-label="label"
                                    option-code="code" option-extra="extra"
                                    wire:model.live="selectedStopdesk"
                                    lazy source="stopdeskSelectOptions"
                                    :scope="'s' . ($this->state_id ?: '') . '|p' . $providerId"
                                    :search="true"
                                    search-placeholder="{{ __('storefront.search') }}"
                                    placeholder="{{ __('storefront.select_stopdesk_point') }}"
                                    icon="business" role="office-select" />
                                @error('selectedStopdesk') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                            @else
                                {{-- No offices to list: keep the failure reason visible
                                     instead of a silent validation dead-end. --}}
                                <div class="rounded-xl border px-4 py-3 text-sm
                                            bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700
                                            text-amber-700 dark:text-amber-400">
                                    @if(! $this->state_id)
                                        {{ __('storefront.select_state_for_desks') }}
                                    @else
                                        {{ __('storefront.no_desks_in_state') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
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
                            @error('address') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- 6. Order notes --}}
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
                        @error('notes') <p class="text-red-500 dark:text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Payment Method --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <x-edz.icon name="banknotes" class="text-xl store-text-primary w-5 h-5" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('storefront.payment_method') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('storefront.payment_method_desc') }}</p>
                    </div>
                </div>
                @error('payment_method') <p class="text-red-500 dark:text-red-400 text-xs mb-3">{{ $message }}</p> @enderror
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($paymentMethods as $method)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="payment_method" value="{{ $method }}" class="peer sr-only">
                            <div class="border-2 rounded-xl p-4 flex items-center gap-3 peer-checked:border-[var(--store-primary)] peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_10%,transparent)] dark:peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_20%,transparent)] border-gray-200 dark:border-gray-600 transition">
                                <x-edz.icon :name="$method === 'cod' ? 'banknotes' : 'credit-card'" class="text-2xl text-gray-500 dark:text-gray-400 w-8 h-8" />
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $method === 'cod' ? __('storefront.payment_on_delivery') : ucfirst($method) }}
                                    </p>
                                    @if($method === 'cod')
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ __('storefront.pay_on_delivery') }}</p>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
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
                                <p class="text-xs text-gray-400 dark:text-gray-500">&times; {{ $item['quantity'] }}</p>
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
                        <span class="text-gray-500 dark:text-gray-400">{{ __('storefront.shipping') }}
                            @if($shippingInfo['provider_name'] ?? null)
                                <span class="text-gray-400 dark:text-gray-500">· {{ $shippingInfo['provider_name'] }}</span>
                            @endif
                        </span>
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
                    <span wire:loading.remove wire:target="submitOrder" class="flex items-center gap-2 text-white">
                        <x-edz.icon name="lock-closed" class="text-lg  w-5 h-5  mx-auto" />
                        {{ __('storefront.place_order') }}
                    </span>
                    <span wire:loading wire:target="submitOrder" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        {{ __('storefront.placing') }}
                    </span>
                </button>

                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                    <x-edz.icon name="shield-check" class="text-base w-5 h-5" />
                    <span>{{ __('storefront.secure_checkout') }}</span>
                </div>
            </div>
        </div>
    </form>
</div>
