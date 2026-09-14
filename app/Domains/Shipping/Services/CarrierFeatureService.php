<?php

namespace App\Domains\Shipping\Services;

use App\Domains\Shipping\Contracts\CarrierIntegrationContract;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\ShippingProvider;

/**
 * Single source of truth for the order-level delivery features
 * (refund_request / send_from_carrier_warehouse / can_open).
 *
 * A feature is only available when it passes THREE gates:
 *   1. the carrier adapter declares it in its documented API capabilities()
 *      (config('delivery.carrier_integrations') — the "available in the API" plane);
 *   2. the carrier structure flags it (Carrier::capabilityList());
 *   3. for a specific provider, the merchant opted in (shipping_providers
 *      *_enabled columns).
 *
 * Everything is pure, in-memory and memoized per request — no network calls and
 * no extra queries beyond the provider rows already loaded for the page.
 */
class CarrierFeatureService
{
    /** Order features that obey the API ∧ structure ∧ opt-in gates. */
    public const ORDER_FEATURES = [
        'refund_request',
        'send_from_carrier_warehouse',
        'can_open',
    ];

    /** @var array<string, array<string, bool>> carrier code → declared API caps */
    private array $apiCapabilitiesCache = [];

    /**
     * Capabilities declared by the carrier's registered adapter, keyed by the
     * stable feature names. Unregistered carriers resolve to all-false (never
     * surface a feature the API cannot accept).
     *
     * @return array<string, bool>
     */
    public function apiCapabilities(?Carrier $carrier = null): array
    {
        $default = array_fill_keys(self::ORDER_FEATURES, false);

        if (! $carrier || blank($carrier->code)) {
            return $default;
        }

        if (isset($this->apiCapabilitiesCache[$carrier->code])) {
            return $this->apiCapabilitiesCache[$carrier->code];
        }

        $adapterClass = config(
            "delivery.carrier_integrations.{$carrier->code}",
            config('delivery.carrier_integrations.*'),
        );

        $caps = $default;
        if ($adapterClass && class_exists($adapterClass)) {
            $adapter = app($adapterClass);
            if ($adapter instanceof CarrierIntegrationContract) {
                $declared = $adapter->capabilities();
                foreach (self::ORDER_FEATURES as $feature) {
                    $caps[$feature] = (bool) ($declared[$feature] ?? false);
                }
            }
        }

        return $this->apiCapabilitiesCache[$carrier->code] = $caps;
    }

    /**
     * Features a carrier actually offers: API ∧ structure.
     *
     * @return array<string, bool>
     */
    public function featuresForCarrier(?Carrier $carrier = null): array
    {
        $features = array_fill_keys(self::ORDER_FEATURES, false);

        if (! $carrier) {
            return $features;
        }

        $api = $this->apiCapabilities($carrier);
        $structure = $carrier->capabilityList();

        foreach (self::ORDER_FEATURES as $feature) {
            $features[$feature] = ($api[$feature] ?? false)
                && ! empty($structure[$feature]);
        }

        return $features;
    }

    /**
     * Features a specific connected provider makes available to its orders:
     * API ∧ structure ∧ merchant opt-in.
     *
     * @return array<string, bool>
     */
    public function featuresForProvider(?ShippingProvider $provider = null): array
    {
        $features = array_fill_keys(self::ORDER_FEATURES, false);

        if (! $provider) {
            return $features;
        }

        $base = $this->featuresForCarrier($provider->carrier);

        $features['refund_request'] = $base['refund_request']
            && (bool) $provider->refund_request_enabled;
        $features['send_from_carrier_warehouse'] = $base['send_from_carrier_warehouse']
            && (bool) $provider->send_from_carrier_warehouse_enabled;
        $features['can_open'] = $base['can_open']
            && (bool) $provider->can_open_enabled;

        return $features;
    }

    /**
     * Store-wide feature availability for the order grid: a feature column is
     * shown when ANY active carrier of the store offers it (API ∧ structure).
     * Opt-in is intentionally not part of this gate — one connected provider
     * with the lane enabled keeps the column/filters/detail surface present.
     *
     * @param  iterable<ShippingProvider>  $providers
     * @return array<string, bool>
     */
    public function storeActiveFeatures(iterable $providers): array
    {
        $features = array_fill_keys(self::ORDER_FEATURES, false);

        foreach ($providers as $provider) {
            $base = $this->featuresForCarrier($provider->carrier);
            foreach ($features as $feature => $active) {
                if (! $active && $base[$feature]) {
                    $features[$feature] = true;
                }
            }
        }

        return $features;
    }
}