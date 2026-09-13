<?php

namespace App\Livewire\Concerns;

use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;

trait CancelsShipmentFromOrdersTable
{
    public function cancelShipment(string $orderId, ?string $reason = null): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

        $order = Order::where('store_id', currentStoreId())
            ->with(['shippingProvider.carrier', 'status'])
            ->find($orderId);

        if (! $order) {
            return;
        }

        $result = app(\App\Domains\Shipping\Services\OrderShippingGateway::class)->cancel(
            order: $order,
            reason: $reason,
            changedBy: currentMembership(),
        );

        if (! ($result['ok'] ?? false)) {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => $result['error'] ?? __('order_flow.shipment_cancellation_failed'),
            ]);

            return;
        }

        $this->loadOrders();
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => __('order_flow.shipment_cancelled'),
        ]);
    }
}