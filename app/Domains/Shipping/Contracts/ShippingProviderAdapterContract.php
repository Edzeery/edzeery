<?php

namespace App\Domains\Shipping\Contracts;

use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Locations\State;

/**
 * Contract for fetching delivery prices from a carrier's API.
 *
 * Each carrier integration (Ecotrack, ZR Express, ...) implements this contract.
 * Until a carrier adapter is built, the DeliveryRatesManager falls back to the
 * DefaultDeliveryRatesContract (manual entry).
 *
 * Implementations MUST be registered in the delivery.adapters config mapping
 * a carrier code to its concrete adapter class.
 */
interface ShippingProviderAdapterContract
{
    /**
     * The carrier "code" this adapter serves (matches carriers.code).
     */
    public static function carrierCode(): string;

    /**
     * Fetch delivery prices for a carrier connection and a state.
     *
     * @return array{office_cost: float|null, home_cost: float|null, free_above: int|null, fetched_at: string|null}
     */
    public function quote(ShippingProvider $provider, State $state, array $context = []): array;
}
