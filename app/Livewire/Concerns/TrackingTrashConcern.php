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
 * Soft delete (to trash), restore and permanent purge for the tracking page.
 * Soft delete + restore are gated to ORDER_DELETE; the permanent purge in
 * addition requires ORDER_DELETE_FINAL (owner, or an explicit permission —
 * admins/managers/staff never derive it from their role). Both the soft delete
 * and the permanent purge mirror OrderShippingGateway::cancel(): before touching
 * a shipped order locally they first delete an unvalidated carrier shipment at
 * the carrier, and a carrier failure (anything but a skip, or a tracking the
 * system already knows is absent at the carrier) hard-blocks the local action.
 */
trait TrackingTrashConcern
{
    /**
     * Bulk soft delete — moves every selected shipment to the trash, running the
     * carrier leg per order first. Selection is reusable across pages, so the
     * scope is trimmed to the store's non-deleted orders on the fly.
     */
    public function bulkDeleteOrders(): void
    {
        if (! canStore(StorePermissionEnum::ORDER_DELETE->value)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
            return;
        }

        $orders = Order::where('store_id', currentStoreId())
            ->whereIn('id', $this->selectedShipments)
            ->whereNull('deleted_at')
            ->get();

        if ($orders->isEmpty()) {
            $this->clearSelection();
            $this->loadShipments();

            return;
        }

        $deleted = 0;
        $blocked = 0;

        foreach ($orders as $order) {
            $outcome = $this->resolveCarrierDelete($order);

            if (! $outcome['allowed']) {
                $blocked++;

                continue;
            }

            $order->delete();
            $deleted++;
        }

        $this->clearSelection();
        $this->loadShipments();

        if ($blocked > 0) {
            $this->dispatch('swal:toast', [
                'icon' => 'warning',
                'title' => __('order_flow.bulk_delete_blocked', ['count' => $blocked]),
            ]);

            return;
        }

        if ($deleted > 0) {
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => __('order_flow.bulk_delete_done', ['count' => $deleted]),
            ]);
        }
    }
    public function deleteOrder(string $orderId): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_DELETE->value), 403);

        $order = Order::where('store_id', currentStoreId())->find($orderId);

        if (! $order) {
            return;
        }

        // Carrier leg first: never soft-delete a live, unvalidated carrier
        // shipment without deleting it at the carrier (see deleteAtCarrier).
        $outcome = $this->resolveCarrierDelete($order);

        if (! $outcome['allowed']) {
            $this->dispatch('swal:toast', $this->blockedCarrierDeleteToast(
                $outcome,
                __('order_flow.move_to_trash_carrier_failed', ['number' => $order->number]),
            ));

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
        $this->clearSelection();
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

    /**
     * Run the carrier leg for a pending local delete (soft or permanent) and
     * report whether the local action may proceed. Mirrors the same guarantee as
     * OrderShippingGateway::cancel(): an ok/'skipped' result — or a tracking the
     * system already knows is absent at the carrier (carrier_unknown_at) —
     * allows the local action; anything else hard-blocks so a live carrier
     * shipment is never deleted locally without being removed off-platform first.
     *
     * @return array{allowed: bool, result: array}
     */
    protected function resolveCarrierDelete(Order $order): array
    {
        $result = app(\App\Domains\Shipping\Services\OrderShippingGateway::class)->deleteAtCarrier($order);

        if (($result['ok'] ?? false) === true) {
            return ['allowed' => true, 'result' => $result];
        }

        $tracking = app(\App\Domains\Order\Services\OrderTrackingService::class)->currentTracking($order);

        return [
            'allowed' => $tracking?->carrier_unknown_at !== null,
            'result' => $result,
        ];
    }

    protected function blockedCarrierDeleteToast(array $outcome, string $title): array
    {
        $toast = ['icon' => 'error', 'title' => $title];

        $detail = $outcome['result']['error'] ?? $outcome['result']['message'] ?? null;

        if ($detail !== null) {
            $toast['text'] = (string) $detail;
        }

        return $toast;
    }

    public function forceDeleteOrder(string $orderId): void
    {
        abort_unless(canFinalDeleteOrders(), 403);

        $order = Order::where('store_id', currentStoreId())->onlyTrashed()->find($orderId);

        if (! $order) {
            return;
        }

        // Carrier leg first: an unvalidated live shipment must be deleted at the
        // carrier before the local permanent purge; a failure hard-blocks.
        $outcome = $this->resolveCarrierDelete($order);

        if (! $outcome['allowed']) {
            $this->dispatch('swal:toast', $this->blockedCarrierDeleteToast(
                $outcome,
                __('order_flow.permanent_delete_carrier_failed', ['number' => $order->number]),
            ));

            return;
        }

        $this->purgeOrderRows($order);

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.order_deleted_permanently')]);

        $this->loadShipments();
    }

    public function forceDeleteAll(): void
    {
        abort_unless(canFinalDeleteOrders(), 403);

        $trashed = Order::where('store_id', currentStoreId())
            ->onlyTrashed()
            ->when($this->trackingTab === 'rider', fn ($q) => $q->whereNotNull('delivery_rider_id'))
            ->when($this->trackingTab === 'carrier', fn ($q) => $q->whereNotNull('shipping_provider_id'))
            ->get();

        if ($trashed->isEmpty()) {
            return;
        }

        foreach ($trashed as $order) {
            $outcome = $this->resolveCarrierDelete($order);

            if (! $outcome['allowed']) {
                $this->dispatch('swal:toast', $this->blockedCarrierDeleteToast(
                    $outcome,
                    __('order_flow.empty_trash_carrier_failed', ['number' => $order->number]),
                ));

                return;
            }

            $this->purgeOrderRows($order);
        }

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.empty_trash')]);

        $this->loadShipments();
    }
}