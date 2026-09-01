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
        // 'ecotrack' => \App\Domains\Shipping\Adapters\EcotrackAdapter::class,
        // 'zrexpress' => \App\Domains\Shipping\Adapters\ZRExpressAdapter::class,
        '*' => DefaultDeliveryRatesAdapter::class,
    ],
];
