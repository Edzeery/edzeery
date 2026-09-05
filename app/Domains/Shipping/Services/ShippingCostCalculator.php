<?php

namespace App\Domains\Shipping\Services;

use App\Domains\Shipping\Models\DeliveryPriceList;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\DeliveryRateCity;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\ShippingRate;
use App\Models\Locations\State;
use App\Models\Stores\Store;

class ShippingCostCalculator
{
    /**
     * Calculate shipping cost for a given store, state, and optional city.
     *
     * Resolution order (storefront home delivery):
     *   1. Store-wide price list, when the whole cart is covered by a single
     *      active list (list state rate, per-municipality override).
     *   2. Announced company rates (delivery_rates + delivery_rate_cities)
     *      for the effective provider (default first), city-rate override.
     *   3. Legacy shipping_rates, then the default provider flat rate, then free.
     *
     * Returns an array with: cost, is_free, provider_name, label, method.
     * method = 'rate' | 'flat' | 'provider_flat' | 'free'
     */
    public function calculate(Store $store, ?string $stateId = null, ?string $cityId = null, float $cartTotal = 0, array $productIds = []): array
    {
        $state = $stateId ? State::find($stateId) : null;

        if ($state && ! $state->is_cod_available) {
            return [
                'cost' => 0,
                'is_free' => false,
                'provider_name' => null,
                'label' => __('storefront.shipping_unavailable'),
                'method' => 'unavailable',
                'available' => false,
            ];
        }

        // 1. Price list rate — applies only when the whole cart belongs to a
        // single active list that has a rate for the requested state.
        if ($productIds !== []) {
            $list = $this->coveringPriceList($store, $state, $productIds);

            if ($list) {
                return $this->resolveListRate($list, $state, $cityId);
            }
        }

        // 2. Announced company rates (delivery_rates / delivery_rate_cities)
        $rate = $this->resolveDeliveryRate($store, $state, $cityId);

        if ($rate) {
            return $this->resolveDeliveryRatePrice($rate, $cartTotal);
        }

        // 3. Legacy exact city rate
        if ($cityId) {
            $rate = ShippingRate::where('store_id', $store->id)
                ->where('city_id', $cityId)
                ->where('is_active', true)
                ->with('provider')
                ->first();

            if ($rate) {
                return $this->resolveRate($rate, $cartTotal);
            }
        }

        // 4. Legacy state-level rate
        if ($stateId) {
            $rate = ShippingRate::where('store_id', $store->id)
                ->where('state_id', $stateId)
                ->whereNull('city_id')
                ->where('is_active', true)
                ->with('provider')
                ->first();

            if ($rate) {
                return $this->resolveRate($rate, $cartTotal);
            }
        }

        // 5. Default provider flat rate
        $defaultProvider = ShippingProvider::where('store_id', $store->id)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if ($defaultProvider && $defaultProvider->flat_rate !== null) {
            $cost = (float) $defaultProvider->flat_rate;

            return [
                'cost' => $cost,
                'is_free' => false,
                'provider_name' => $defaultProvider->name,
                'label' => __('storefront.fixed_shipping_fee'),
                'method' => 'provider_flat',
                'available' => true,
            ];
        }

        // 6. No rate found — default free
        return [
            'cost' => 0,
            'is_free' => true,
            'provider_name' => null,
            'label' => __('storefront.free_delivery'),
            'method' => 'free',
            'available' => true,
        ];
    }

    private function resolveRate(ShippingRate $rate, float $cartTotal): array
    {
        $source = ['rate_type' => ShippingRate::class, 'rate_id' => $rate->id];

        if ($rate->free_above && $cartTotal >= $rate->free_above) {
            return [
                'cost' => 0,
                'is_free' => true,
                'provider_name' => $rate->provider?->name,
                'label' => $rate->label ?? __('storefront.free_delivery'),
                'method' => 'free',
                'available' => true,
                'source' => $source,
            ];
        }

        return [
            'cost' => (float) $rate->cost,
            'is_free' => false,
            'provider_name' => $rate->provider?->name,
            'label' => $rate->label ?? __('storefront.shipping_fee'),
            'method' => 'rate',
            'available' => true,
            'source' => $source,
        ];
    }

    /**
     * The single active price list covering every cart product and carrying a
     * rate for the requested state. Returns null when the cart is mixed or
     * when no list qualifies, so the caller falls back to company rates.
     */
    private function coveringPriceList(Store $store, ?State $state, array $productIds): ?DeliveryPriceList
    {
        if (! $state) {
            return null;
        }

        $productIds = array_values(array_unique($productIds));

        $lists = DeliveryPriceList::query()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->whereIn('products.id', $productIds))
            ->with(['stateRates' => fn ($query) => $query->where('state_id', $state->id)])
            ->get();

        if ($lists->isEmpty()) {
            return null;
        }

        $covering = $lists->filter(function (DeliveryPriceList $list) use ($productIds): bool {
            if ($list->stateRates->isEmpty()) {
                return false;
            }

            return $list->products()->whereIn('products.id', $productIds)->count() === count($productIds);
        });

        if ($covering->count() !== 1) {
            return null;
        }

        return $covering->first();
    }

    private function resolveListRate(DeliveryPriceList $list, ?State $state, ?string $cityId): array
    {
        $stateRate = $list->stateRates->first();
        $cost = $stateRate ? (float) $stateRate->home_cost : 0;

        $source = [
            'rate_type' => $stateRate ? $stateRate::class : null,
            'rate_id' => $stateRate?->id,
        ];

        if ($cityId) {
            $cityRate = $list->cityRates()->where('city_id', $cityId)->first();

            if ($cityRate && $cityRate->home_cost !== null) {
                $cost = (float) $cityRate->home_cost;
                $source = [
                    'rate_type' => $cityRate::class,
                    'rate_id' => $cityRate->id,
                ];
            }
        }

        return [
            'cost' => $cost,
            'is_free' => false,
            'provider_name' => $list->name,
            'label' => __('storefront.shipping_fee'),
            'method' => 'rate',
            'available' => true,
            'source' => $source,
        ];
    }

    /**
     * The effective delivery rate for the store's default (or first active)
     * carrier that has prices for the requested state; the city-level home
     * cost overrides the state-level home cost when set.
     */
    private function resolveDeliveryRate(Store $store, ?State $state, ?string $cityId): ?DeliveryRate
    {
        if (! $state) {
            return null;
        }

        $rate = DeliveryRate::query()
            ->where('store_id', $store->id)
            ->where('state_id', $state->id)
            ->where('is_active', true)
            ->whereHas('provider', fn ($query) => $query->where('is_active', true))
            ->with('provider')
            ->get()
            ->sortByDesc(fn (DeliveryRate $rate) => (int) $rate->provider?->is_default)
            ->first();

        if (! $rate) {
            return null;
        }

        if ($cityId) {
            $cityRate = DeliveryRateCity::query()
                ->where('store_id', $store->id)
                ->where('shipping_provider_id', $rate->shipping_provider_id)
                ->where('state_id', $state->id)
                ->where('city_id', $cityId)
                ->where('is_active', true)
                ->first();

            if ($cityRate) {
                $rate->cityRate = $cityRate;
                $rate->home_cost = $cityRate->home_cost;
                $rate->free_above = $cityRate->free_above ?? $rate->free_above;
            }
        }

        return $rate;
    }

    private function resolveDeliveryRatePrice(DeliveryRate $rate, float $cartTotal): array
    {
        $source = isset($rate->cityRate) && $rate->cityRate
            ? ['rate_type' => DeliveryRateCity::class, 'rate_id' => $rate->cityRate->id]
            : ['rate_type' => DeliveryRate::class, 'rate_id' => $rate->id];

        if ($rate->free_above && $cartTotal >= $rate->free_above) {
            return [
                'cost' => 0,
                'is_free' => true,
                'provider_name' => $rate->provider?->name,
                'label' => __('storefront.free_delivery'),
                'method' => 'free',
                'available' => true,
                'source' => $source,
            ];
        }

        return [
            'cost' => (float) $rate->home_cost,
            'is_free' => false,
            'provider_name' => $rate->provider?->name,
            'label' => __('storefront.shipping_fee'),
            'method' => 'rate',
            'available' => true,
            'source' => $source,
        ];
    }
}
