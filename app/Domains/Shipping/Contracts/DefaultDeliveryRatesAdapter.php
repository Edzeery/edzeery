<?php

namespace App\Domains\Shipping\Contracts;

use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Locations\State;

/**
 * Default adapter used when no real carrier API integration exists yet for a
 * connected carrier. Returns nulls so the merchant can enter office/home costs
 * manually. Later, this is replaced per carrier by a real adapter (Ecotrack, ZR...).
 */
class DefaultDeliveryRatesAdapter implements ShippingProviderAdapterContract
{
    public static function carrierCode(): string
    {
        return '*';
    }

    public function quote(ShippingProvider $provider, State $state, array $context = []): array
    {
        return [
            'office_cost' => null,
            'home_cost'   => null,
            'free_above'  => null,
            'fetched_at'  => null,
        ];
    }
}
