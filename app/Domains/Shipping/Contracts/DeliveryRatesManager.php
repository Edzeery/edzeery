<?php

namespace App\Domains\Shipping\Contracts;

use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Locations\State;

/**
 * Resolves the adapter for a connected carrier and quotes prices.
 *
 * - Looks up an adapter by the carrier's code (carriers.code).
 * - Falls back to the default (manual) adapter.
 * - Persists the quoted office/home costs onto the delivery_rates row.
 */
class DeliveryRatesManager
{
    public function adapterFor(ShippingProvider $provider): ShippingProviderAdapterContract
    {
        $code = $provider->carrier?->code;

        if ($code) {
            $class = config("delivery.adapters.{$code}");
            if ($class && class_exists($class)) {
                return app($class);
            }
        }

        return app(config('delivery.adapters.*'));
    }

    /**
     * Pull the carrier's announced prices for a single provider across all states,
     * persisting them onto each delivery_rate row.
     *
     * @return array{count: int, states: array<int, array{state_id: string, office_cost: float|null, home_cost: float|null, free_above: int|null}>}
     */
    public function syncProvider(ShippingProvider $provider, iterable $states): array
    {
        $adapter = $this->adapterFor($provider);
        $result = [
            'count' => 0,
            'states' => [],
        ];

        foreach ($states as $state) {
            $prices = $adapter->quote($provider, $state);

            $rate = DeliveryRate::firstOrNew([
                'store_id' => $provider->store_id,
                'shipping_provider_id' => $provider->id,
                'state_id' => $state->id,
            ]);

            // Keep existing manually-adjusted values when the adapter returns null.
            $rate->office_cost = $prices['office_cost'] ?? ($rate->office_cost ?? null);
            $rate->home_cost = $prices['home_cost'] ?? ($rate->home_cost ?? null);
            $rate->free_above = $prices['free_above'] ?? ($rate->free_above ?? null);

            if ($rate->office_cost !== null || $rate->home_cost !== null) {
                $rate->source = 'announced';
                $rate->synced_at = now();
                $rate->save();
                $result['count']++;
            }

            $result['states'][] = [
                'state_id' => $state->id,
                'office_cost' => $rate->office_cost,
                'home_cost' => $rate->home_cost,
                'free_above' => $rate->free_above,
            ];
        }

        return $result;
    }

    /**
     * Quote and persist prices for a provider/state.
     */
    public function quoteAndSave(ShippingProvider $provider, State $state, array $context = []): array
    {
        $prices = $this->adapterFor($provider)->quote($provider, $state, $context);

        $rate = DeliveryRate::firstOrNew([
            'store_id' => $provider->store_id,
            'shipping_provider_id' => $provider->id,
            'state_id' => $state->id,
        ]);

        $rate->office_cost = $prices['office_cost'] ?? ($rate->office_cost ?? null);
        $rate->home_cost = $prices['home_cost'] ?? ($rate->home_cost ?? null);
        $rate->free_above = $prices['free_above'] ?? ($rate->free_above ?? null);
        $rate->save();

        return [
            'office_cost' => $rate->office_cost,
            'home_cost' => $rate->home_cost,
            'free_above' => $rate->free_above,
        ];
    }
}
