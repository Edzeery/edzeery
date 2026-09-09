<?php

namespace App\Domains\Shipping\Contracts;

use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Orders\Order;

/**
 * Per-carrier integration surface: pull offices/stations to offer in the
 * order pickers and push an order to the carrier when handed over.
 *
 * Some carriers expose offices through a public API directly (NOEST),
 * others need a dedicated script/adaptor — implement this contract once per
 * carrier code and register it in config('delivery.carrier_integrations').
 */
interface CarrierIntegrationContract
{
    /**
     * The carriers.code value this integration serves.
     */
    public function carrierCode(): string;

    /**
     * Offices (desks/stations/agences) available for a wilaya/commune.
     *
     * Adapters are free to filter server-side; the sync service reconciles
     * the result into stopdesk_points for fast, responsive selects.
     *
     * @return array<int, array{
     *     external_code: string,
     *     name: string,
     *     city: string|null,
     *     address: string|null,
     *     phone: string|null,
     * }>
     */
    public function offices(ShippingProvider $provider, ?State $state = null, ?City $city = null): array;

    /**
     * Push an order to the carrier.
     *
     * @throws \RuntimeException When the carrier rejects the order.
     * @return array{tracking: string, label_url: string|null, raw: array}
     */
    public function createOrder(ShippingProvider $provider, Order $order): array;

    /**
     * Send a free-text note/remark to the carrier against an existing shipment.
     * Must inspect the response body for success — some carriers (NOEST included)
     * return HTTP 200 even on logical failure.
     *
     * @return array{ok: bool, message: string}
     */
    public function addNote(ShippingProvider $provider, string $trackingNumber, string $content): array;

    /**
     * Bust any internal cache (e.g. office lists) for this provider.
     */
    public function forgetCache(ShippingProvider $provider): void;

    /**
     * Lightweight credential check — must not create side effects on the
     * carrier's system (no order creation). Returns a normalized result so
     * every adapter can report success/failure uniformly to the UI.
     *
     * @return array{ok: bool, message: string}
     */
    public function testConnection(ShippingProvider $provider): array;
}