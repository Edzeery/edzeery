<?php

namespace App\Domains\Shipping\Services;

use App\Domains\Shipping\Models\ShippingRate;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Locations\State;
use App\Models\Stores\Store;

class ShippingCostCalculator
{
    /**
     * Calculate shipping cost for a given store, state, and optional city.
     *
     * Returns an array with: cost, is_free, provider_name, label, method.
     * method = 'rate' | 'flat' | 'provider_flat' | 'free'
     */
    public function calculate(Store $store, ?string $stateId = null, ?string $cityId = null, float $cartTotal = 0): array
    {
        $state = $stateId ? State::find($stateId) : null;

        if ($state && ! $state->is_cod_available) {
            return [
                'cost'           => 0,
                'is_free'        => false,
                'provider_name'  => null,
                'label'          => __('storefront.shipping_unavailable'),
                'method'         => 'unavailable',
                'available'      => false,
            ];
        }

        // 1. Exact city rate
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

        // 2. State-level rate
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

        // 3. Default provider flat rate
        $defaultProvider = ShippingProvider::where('store_id', $store->id)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if ($defaultProvider && $defaultProvider->flat_rate !== null) {
            $cost = (float) $defaultProvider->flat_rate;

            return [
                'cost'           => $cost,
                'is_free'        => false,
                'provider_name'  => $defaultProvider->name,
                'label'          => __('storefront.fixed_shipping_fee'),
                'method'         => 'provider_flat',
                'available'      => true,
            ];
        }

        // 4. No rate found — default free
        return [
            'cost'           => 0,
            'is_free'        => true,
            'provider_name'  => null,
            'label'          => __('storefront.free_delivery'),
            'method'         => 'free',
            'available'      => true,
        ];
    }

    private function resolveRate(ShippingRate $rate, float $cartTotal): array
    {
        if ($rate->free_above && $cartTotal >= $rate->free_above) {
            return [
                'cost'           => 0,
                'is_free'        => true,
                'provider_name'  => $rate->provider?->name,
                'label'          => $rate->label ?? __('storefront.free_delivery'),
                'method'         => 'free',
                'available'      => true,
            ];
        }

        return [
            'cost'           => (float) $rate->cost,
            'is_free'        => false,
            'provider_name'  => $rate->provider?->name,
            'label'          => $rate->label ?? __('storefront.shipping_fee'),
            'method'         => 'rate',
            'available'      => true,
        ];
    }
}
