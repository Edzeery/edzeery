<?php

namespace App\Domains\Order\Services;

use App\Models\Orders\Order;

class OrderCompleteness
{
    public function missing(Order $order, bool $forSend = false): array
    {
        $missing = [];

        if ($forSend && ! $this->storeReadyForDispatch($order->store_id)) {
            $missing[] = ['key' => 'carrier_not_configured', 'label' => __('order_flow.carrier_not_configured')];
        }

        if (blank($order->customer?->name)) {
            $missing[] = ['key' => 'customer_name', 'label' => __('merchant_panel.customer_name')];
        }

        if (blank($order->customer?->phone)) {
            $missing[] = ['key' => 'customer_phone', 'label' => __('merchant_panel.customer_phone')];
        }

        if (blank($order->state_id)) {
            $missing[] = ['key' => 'state', 'label' => __('merchant_panel.state')];
        }

        if (blank($order->city_id)) {
            $missing[] = ['key' => 'city', 'label' => __('merchant_panel.city')];
        }

        if ($order->items->isEmpty()) {
            $missing[] = ['key' => 'items', 'label' => __('merchant_panel.items')];
        }

        if ($forSend) {
            if ($order->delivery_type === Order::DELIVERY_STOPDESK) {
                if (blank($order->stopdesk_point_id)) {
                    $missing[] = ['key' => 'stopdesk_point', 'label' => __('merchant_panel.stopdesk_point')];
                }
            } elseif (blank($order->address)) {
                $missing[] = ['key' => 'delivery_address', 'label' => __('merchant_panel.address')];
            }

            if (blank($order->shipping_provider_id) && blank($order->delivery_rider_id)) {
                $missing[] = ['key' => 'carrier', 'label' => __('order_flow.confirm_partner')];
            }
        }

        return $missing;
    }

    public function missingLabels(Order $order, bool $forSend = false): array
    {
        return array_column($this->missing($order, $forSend), 'label');
    }

    public function isComplete(Order $order, bool $forSend = false): bool
    {
        return $this->missing($order, $forSend) === [];
    }

    /**
     * Store-level dispatch readiness: at least one active shipping company OR at
     * least one active delivery rider must exist before any order can be sent.
     */
    protected function storeReadyForDispatch(?string $storeId): bool
    {
        if (blank($storeId)) {
            return false;
        }

        $hasActiveProvider = \App\Domains\Shipping\Models\ShippingProvider::query()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveProvider) {
            return true;
        }

        return \App\Domains\Shipping\Models\DeliveryRider::query()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->exists();
    }
}