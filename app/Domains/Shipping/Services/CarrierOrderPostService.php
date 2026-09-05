<?php

namespace App\Domains\Shipping\Services;

use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Contracts\CarrierIntegrationContract;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;

/**
 * Pushes an order to its carrier integration and records the resulting
 * tracking/label against order_trackings. Carriers without a registered
 * integration are left untouched (local shipment flow only).
 */
class CarrierOrderPostService
{
    public function __construct(protected OrderTrackingService $trackingService)
    {
    }

    public function resolve(ShippingProvider $provider): ?CarrierIntegrationContract
    {
        $code = $provider->carrier?->code;

        if (! $code) {
            return null;
        }

        $adapterClass = config(
            "delivery.carrier_integrations.{$code}",
            config('delivery.carrier_integrations.*'),
        );

        if (! $adapterClass || ! class_exists($adapterClass)) {
            return null;
        }

        return app($adapterClass);
    }

    /**
     * Post the order to the carrier and fill/update its open tracking row.
     *
     * @throws \RuntimeException When the carrier rejects the order (caller
     *                           decides whether the local shipment proceeds).
     */
    public function postToCarrier(Order $order): ?OrderTracking
    {
        $provider = $order->shippingProvider;

        if (! $provider) {
            return null;
        }

        $adapter = $this->resolve($provider);

        if (! $adapter) {
            return null;
        }

        $result = $adapter->createOrder($provider, $order);

        $tracking = $this->trackingService->startShipment($order, $result['tracking']);

        $tracking->update([
            'shipping_provider_id' => $provider->id,
            'tracking_number' => $result['tracking'],
            'carrier_status' => 'created',
            'carrier_label' => $result['label_url'],
            'carrier_raw' => $result['raw'],
            'last_synced_at' => now(),
        ]);

        return $tracking;
    }
}