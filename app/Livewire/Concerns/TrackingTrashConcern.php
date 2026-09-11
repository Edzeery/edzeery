<?php

namespace App\Livewire\Concerns;

use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;

/**
 * Soft delete (to trash), restore and permanent purge for the tracking page —
 * every action is gated to ORDER_DELETE. The permanent delete tries to remove
 * an unvalidated carrier shipment at the carrier first; a carrier failure never
 * blocks local purging.
 */
trait TrackingTrashConcern
{
    public function deleteOrder(string $orderId): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);

        $order = Order::where('store_id', currentStoreId())->find($orderId);

        if (! $order) {
            return;
        }

        $order->delete();

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.order_moved_to_trash')]);

        $this->loadShipments();

        if ($this->drawerOrderId !== null && (string) $this->drawerOrderId === (string) $order->id) {
            $this->closeDrawer();
        }
    }

    public function toggleTrash(): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);

        $this->showTrash = ! $this->showTrash;
        $this->page = 1;
        $this->loadShipments();
    }

    public function restoreOrder(string $orderId): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);

        $order = Order::where('store_id', currentStoreId())->onlyTrashed()->find($orderId);

        if (! $order) {
            return;
        }

        $order->restore();

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.orders_restored')]);

        $this->loadShipments();
    }

    public function restoreAll(): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);

        $trashScope = Order::where('store_id', currentStoreId())
            ->onlyTrashed()
            ->when($this->trackingTab === 'rider', fn ($q) => $q->whereNotNull('delivery_rider_id'))
            ->when($this->trackingTab === 'carrier', fn ($q) => $q->whereNotNull('shipping_provider_id'));

        if ($trashScope->count() === 0) {
            return;
        }

        $trashScope->restore();

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.orders_restored')]);

        $this->loadShipments();
    }

    /**
     * Permanent delete. Children that have no FK to orders (order_tracking_histories,
     * order_events) are purged explicitly so nothing survives the order; the rows
     * that do reference the order (trackings, status histories, items) are removed
     * first to avoid FK violations, then the order itself is force-deleted.
     */
    public function purgeOrderRows(Order $order): void
    {
        OrderTrackingHistory::where('store_id', $order->store_id)->where('order_id', $order->id)->delete();
        OrderEvent::where('store_id', $order->store_id)->where('order_id', $order->id)->delete();
        OrderStatusHistory::where('order_id', $order->id)->delete();
        OrderTracking::where('store_id', $order->store_id)->where('order_id', $order->id)->delete();
        OrderItem::where('store_id', $order->store_id)->where('order_id', $order->id)->forceDelete();
        $order->forceDelete();
    }

    public function forceDeleteOrder(string $orderId): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);

        $order = Order::where('store_id', currentStoreId())->onlyTrashed()->find($orderId);

        if (! $order) {
            return;
        }

        // Try to delete an unvalidated carrier shipment at the carrier first; a
        // carrier failure never blocks the local permanent purge.
        app(\App\Domains\Shipping\Services\OrderShippingGateway::class)->deleteAtCarrier($order);

        $this->purgeOrderRows($order);

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.order_deleted_permanently')]);

        $this->loadShipments();
    }

    public function forceDeleteAll(): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);

        $trashed = Order::where('store_id', currentStoreId())
            ->onlyTrashed()
            ->when($this->trackingTab === 'rider', fn ($q) => $q->whereNotNull('delivery_rider_id'))
            ->when($this->trackingTab === 'carrier', fn ($q) => $q->whereNotNull('shipping_provider_id'))
            ->get();

        if ($trashed->isEmpty()) {
            return;
        }

        foreach ($trashed as $order) {
            app(\App\Domains\Shipping\Services\OrderShippingGateway::class)->deleteAtCarrier($order);
            $this->purgeOrderRows($order);
        }

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.empty_trash')]);

        $this->loadShipments();
    }
}