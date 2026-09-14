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
 *
 * 🔗 For formatting an order's line items into a carrier text field, use
 * \App\Domains\Orders\Support\OrderItemsFormatter::toCompactString() — never
 * hand-format order lines in an adapter. The formatter is carrier-agnostic and
 * the single source of truth for every product/variant/qty summary across the
 * merchant panel, printed label and carrier payloads.
 */
interface CarrierIntegrationContract
{
    /**
     * The carriers.code value this integration serves.
     */
    public function carrierCode(): string;

    /**
     * Features this carrier's documented API actually accepts. This is the
     * "available in the API" gate: order features (refund_request,
     * send_from_carrier_warehouse, can_open) must never be shown to merchants
     * when the adapter does not declare them, regardless of the admin's
     * structure flags. Declared from the carrier's reference docs, not guesses.
     *
     * @return array{refund_request: bool, send_from_carrier_warehouse: bool, can_open: bool}
     */
    public function capabilities(): array;

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
     * Pre-flight validation of an order against this carrier's documented field
     * rules, before any network call is made. Returns validated=false with a
     * per-field map of human-readable messages when the carrier would reject
     * the order. The send gateway blocks the post (and only then creates local
     * tracking) while any error remains.
     *
     * @return array{validated: bool, errors: array<string, list<string>>}
     */
    public function validateForCarrier(ShippingProvider $provider, Order $order): array;

    /**
     * Push an order to the carrier.
     *
     * @return array{tracking: string, label_url: string|null, raw: array}
     *
     * @throws \RuntimeException When the carrier rejects the order.
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
     * Delete an unvalidated shipment at the carrier (cancel-send pre-expedition).
     * Carriers only allow deleting orders that have not been validated yet; the
     * gateway enforces that gate via order_trackings.carrier_validated_at.
     * Must inspect the response body — NOEST returns HTTP 200 even on logical
     * failure (e.g. "Commande inexistante" or an already-validated order).
     *
     * @return array{ok: bool, message: string}
     */
    public function deleteOrder(ShippingProvider $provider, string $trackingNumber): array;

    /**
     * Validate a shipped order at the carrier (dispatch handover). Once validated
     * the shipment becomes visible to the carrier's logistics and can no longer
     * be modified/deleted remotely — the gateway persists the outcome on
     * order_trackings.carrier_validated_at / carrier_validation_error.
     *
     * @return array{ok: bool, message: string}
     */
    public function validateOrder(ShippingProvider $provider, string $trackingNumber): array;

    /**
     * Validate several shipped orders in one batch.
     *
     * @param  list<string>  $trackingNumbers
     * @return array{
     *     ok: bool,
     *     validated: list<string>,
     *     failed: array<string, string>,
     *     message?: string,
     * }
     */
    public function validateOrders(ShippingProvider $provider, array $trackingNumbers): array;

    /**
     * Resolve a label for an existing shipment.
     *
     * Carriers that can issue a printable label (PDF/ZPL) return
     * ['ok' => true, 'url' => <auth-proxy or public url>]. The URL is fetched
     * with the provider's credentials by the merchant label proxy so printed
     * labels always carry the carrier's bearer token. Adapters without a label
     * endpoint return ['ok' => false] and the UI falls back to our own sheet.
     *
     * @return array{ok: bool, url?: string, message?: string}
     */
    public function getLabel(ShippingProvider $provider, string $trackingNumber): array;

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
