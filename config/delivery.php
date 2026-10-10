<?php

/**
 * Delivery configuration.
 *
 * 'adapters' maps a carrier code (carriers.code) to a class implementing
 * ShippingProviderAdapterContract. The '*' key is the fallback used when a
 * connected carrier has no dedicated adapter yet (manual pricing entry).
 */

use App\Domains\Shipping\Contracts\DefaultDeliveryRatesAdapter;

return [
    'adapters' => [
        'noest' => \App\Domains\Shipping\Adapters\NoestDeliveryRatesAdapter::class,
        // 'ovred.ecotrack' => \App\Domains\Shipping\Adapters\EcotrackAdapter::class, // overd , anderson , DHD ,
        // 'dhd.ecotrack' => \App\Domains\Shipping\Adapters\EcotrackAdapter::class,
        // 'zrexpress' => \App\Domains\Shipping\Adapters\ZRExpressAdapter::class,
        // 'yalidine' => \App\Domains\Shipping\Adapters\YalidineAdapter::class,
        // 'ecomv2' => \App\Domains\Shipping\Adapters\EcomV2DeliveryAdapter::class,
        // 'newzrexpress' => \App\Domains\Shipping\Adapters\NewZRExpressAdapter::class,

        '*' => DefaultDeliveryRatesAdapter::class,
    ],

    /*
     * Carrier integrations (office lookup + order posting) keyed by
     * carriers.code. The '*' key is null by default: carriers without a
     * dedicated integration keep the manual stopdesk-points flow.
     */
    'carrier_integrations' => [
        'noest' => \App\Domains\Shipping\Adapters\NoestIntegrationAdapter::class,
        '*' => null,
    ],
];
