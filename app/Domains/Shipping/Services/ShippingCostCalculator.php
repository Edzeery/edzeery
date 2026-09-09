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
    public const DELIVERY_HOME = 'home';

    public const DELIVERY_STOPDESK = 'stopdesk';

    /**
     * Calculate shipping cost for a given store, state, and optional city.
     *
     * Resolution order:
     *   1. Store-wide price list (home deliveries only), when the whole cart is
     *      covered by a single active list (list state rate, per-municipality
     *      home-cost override). Office (stopdesk) deliveries never use the list.
     *   2. Announced company rates (delivery_rates + delivery_rate_cities) for
     *      the requested provider (or the effective default-first provider when
     *      none is given). Home uses home_cost (+ city override), stopdesk uses
     *      office_cost. free_above applies to both.
     *   3. Legacy shipping_rates (scoped to the requested provider when given).
     *   4. The requested provider flat rate, else the default provider flat rate.
     *   5. Stopdesk with no resolvable office price → 'office_unavailable'
     *      (available=false); home/storefront fallback → free.
     *
     * Returns an array with: cost, is_free, provider_name, label, method,
     * available, source, source_type.
     * method = 'rate' | 'flat' | 'provider_flat' | 'free' | 'unavailable' | 'office_unavailable'
     */
    public function calculate(
        Store $store,
        ?string $stateId = null,
        ?string $cityId = null,
        float $cartTotal = 0,
        array $productIds = [],
        ?string $providerId = null,
        string $deliveryType = self::DELIVERY_HOME,
    ): array {
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

        // 1. Price list rate — home only, applies only when the whole cart
        // belongs to a single active list that has a rate for the requested state.
        if ($deliveryType === self::DELIVERY_HOME && $productIds !== []) {
            $list = $this->coveringPriceList($store, $state, $productIds);

            if ($list) {
                return $this->resolveListRate($list, $state, $cityId);
            }
        }

        // 2. Announced company rates (delivery_rates / delivery_rate_cities)
        $rate = $this->resolveDeliveryRate($store, $state, $cityId, $providerId, $cartTotal, $deliveryType);

        if ($rate) {
            return $this->resolveDeliveryRatePrice($rate, $cartTotal, $deliveryType);
        }

        // 3. Legacy exact city rate
        if ($cityId) {
            $query = ShippingRate::where('store_id', $store->id)
                ->where('city_id', $cityId)
                ->where('is_active', true)
                ->with('provider');

            if ($providerId) {
                $query->where('shipping_provider_id', $providerId);
            }

            $rate = $query->first();

            if ($rate) {
                return $this->resolveRate($rate, $cartTotal);
            }
        }

        // 4. Legacy state-level rate
        if ($stateId) {
            $query = ShippingRate::where('store_id', $store->id)
                ->where('state_id', $stateId)
                ->whereNull('city_id')
                ->where('is_active', true)
                ->with('provider');

            if ($providerId) {
                $query->where('shipping_provider_id', $providerId);
            }

            $rate = $query->first();

            if ($rate) {
                return $this->resolveRate($rate, $cartTotal);
            }
        }

        // 5. Provider flat rate (the requested one when given, else store default)
        $provider = $providerId
            ? ShippingProvider::where('store_id', $store->id)->where('is_active', true)->find($providerId)
            : ShippingProvider::where('store_id', $store->id)
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();

        if ($provider && $provider->flat_rate !== null) {
            $cost = (float) $provider->flat_rate;

            return [
                'cost' => $cost,
                'is_free' => false,
                'provider_name' => $provider->name,
                'label' => __('storefront.fixed_shipping_fee'),
                'method' => 'provider_flat',
                'available' => true,
                'source_type' => 'company_flat',
            ];
        }

        // 6. Stopdesk with no resolvable office price — explicit unresolved state.
        if ($deliveryType === self::DELIVERY_STOPDESK) {
            return [
                'cost' => 0,
                'is_free' => false,
                'provider_name' => null,
                'label' => __('storefront.shipping_unavailable'),
                'method' => 'office_unavailable',
                'available' => false,
                'source_type' => null,
            ];
        }

        // 7. No rate found — default free
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
                'source_type' => 'company',
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
            'source_type' => 'company',
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
            'source_type' => 'price_list',
        ];
    }

    /**
     * The requested carrier's rate for the given state (default-first when no
     * provider is given). Home consumes home_cost with the per-commune override
     * from delivery_rate_cities; stopdesk consumes office_cost (state level).
     * A rate whose relevant column is null is only resolvable through free_above.
     */
    private function resolveDeliveryRate(
        Store $store,
        ?State $state,
        ?string $cityId,
        ?string $providerId,
        float $cartTotal,
        string $deliveryType,
    ): ?DeliveryRate {
        if (! $state) {
            return null;
        }

        $query = DeliveryRate::query()
            ->where('store_id', $store->id)
            ->where('state_id', $state->id)
            ->where('is_active', true)
            ->whereHas('provider', fn ($query) => $query->where('is_active', true))
            ->with('provider');

        $rate = $providerId
            ? $query->where('shipping_provider_id', $providerId)->first()
            : $query->get()
                ->sortByDesc(fn (DeliveryRate $rate) => (int) $rate->provider?->is_default)
                ->first();

        if (! $rate) {
            return null;
        }

        $hasCost = $rate->home_cost !== null;
        $freeAbove = $rate->free_above !== null;

        if ($deliveryType === self::DELIVERY_STOPDESK) {
            $hasCost = $rate->office_cost !== null;

            // Below a free-shipping threshold with no base office price → unresolved.
            if (! $hasCost && (! $freeAbove || $cartTotal < $rate->free_above)) {
                return null;
            }

            return $rate;
        }

        if (! $hasCost && (! $freeAbove || $cartTotal < $rate->free_above)) {
            return null;
        }

        // Home city-level override (delivery_rate_cities carry home costs only).
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

                if ($cityRate->home_cost !== null) {
                    $rate->home_cost = $cityRate->home_cost;
                }

                $rate->free_above = $cityRate->free_above ?? $rate->free_above;
            }
        }

        return $rate;
    }

    private function resolveDeliveryRatePrice(DeliveryRate $rate, float $cartTotal, string $deliveryType): array
    {
        $source = $rate->cityRate && $deliveryType === self::DELIVERY_HOME
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
                'source_type' => 'company',
            ];
        }

        $cost = $deliveryType === self::DELIVERY_STOPDESK
            ? (float) $rate->office_cost
            : (float) $rate->home_cost;

        return [
            'cost' => $cost,
            'is_free' => false,
            'provider_name' => $rate->provider?->name,
            'label' => __('storefront.shipping_fee'),
            'method' => 'rate',
            'available' => true,
            'source' => $source,
            'source_type' => 'company',
        ];
    }
}