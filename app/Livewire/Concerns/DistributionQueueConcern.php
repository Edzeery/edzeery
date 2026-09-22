<?php

namespace App\Livewire\Concerns;

use App\Enums\Store\OrderStatus;
use App\Enums\Store\OrderTrackingStatus;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;

/**
 * Row sources for the merchant distribution queue (P34.5). Both tabs list only
 * items that need attention: unassigned (assigned_to_membership_id IS NULL) or
 * flagged over capacity — excluding closed/terminal records. Unassigned items
 * float to the top, oldest first, since they are the most urgent.
 */
trait DistributionQueueConcern
{
    public function loadQueue(): void
    {
        $storeId = currentStoreId();

        $this->confirmationCount = $this->confirmationRows($storeId, true);
        $this->confirmationQueue = $this->confirmationRows($storeId);
        $this->trackingCount = $this->trackingRows($storeId, true);
        $this->trackingQueue = $this->trackingRows($storeId);
    }

    /**
     * Terminal order status keys, derived from the canonical OrderStatus enum
     * (single source — the same set the resolver and assignment service use).
     *
     * @return list<string>
     */
    public function terminalOrderStatusKeys(): array
    {
        return collect(OrderStatus::cases())
            ->filter(fn (OrderStatus $status) => $status->isTerminal())
            ->map(fn (OrderStatus $status) => $status->value)
            ->values()
            ->all();
    }

    /**
     * @param string $storeId
     * @param bool $countOnly
     * @return array<int, array<string, mixed>>|int
     */
    private function confirmationRows(string $storeId, bool $countOnly = false): array|int
    {
        $orders = Order::query()
            ->where('store_id', $storeId)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('assigned_to_membership_id')
                    ->orWhere('over_capacity', true);
            })
            ->whereHas('status', fn ($q) => $q->whereNotIn('key', $this->terminalOrderStatusKeys()))
            ->orderByRaw('CASE WHEN assigned_to_membership_id IS NULL THEN 0 ELSE 1 END ASC, created_at ASC');

        if ($countOnly) {
            return $orders->count();
        }

        $orders = $orders
            ->with(['customer', 'status', 'assignedMembership.user'])
            ->take(500)
            ->get();

        return $orders
            ->map(fn (Order $order) => [
                'id' => (string) $order->id,
                'number' => $order->number,
                'customer' => $order->customer?->name ?? '—',
                'status' => [
                    'key' => $order->status?->key ?? 'default',
                    'color' => $order->status?->color ?? 'gray',
                ],
                'assigned_to' => $order->assignedMembership?->user?->name,
                'over_capacity' => (bool) $order->over_capacity,
                'created_ago' => $order->created_at?->diffForHumans() ?? '—',
            ])
            ->values()
            ->all();
    }

    /**
     * @param string $storeId
     * @param bool $countOnly
     * @return array<int, array<string, mixed>>|int
     */
    private function trackingRows(string $storeId, bool $countOnly = false): array|int
    {
        $openStatuses = collect(OrderTrackingStatus::open())
            ->map(fn (OrderTrackingStatus $status) => $status->value)
            ->all();

        $trackings = OrderTracking::query()
            ->where('store_id', $storeId)
            ->whereIn('tracking_status', $openStatuses)
            ->where(function ($q) {
                $q->whereNull('assigned_to_membership_id')
                    ->orWhere('over_capacity', true);
            })
            ->orderByRaw('CASE WHEN assigned_to_membership_id IS NULL THEN 0 ELSE 1 END ASC, created_at ASC');

        if ($countOnly) {
            return $trackings->count();
        }

        $trackings = $trackings
            ->with(['order.customer', 'order.status', 'assignedTo.user'])
            ->take(500)
            ->get();

        return $trackings
            ->map(function (OrderTracking $tracking) {
                $order = $tracking->order;

                return [
                    'id' => (string) $tracking->id,
                    'number' => $order?->number ?? '—',
                    'customer' => $order?->customer?->name ?? '—',
                    'tracking_status' => $tracking->tracking_status,
                    'assigned_to' => $tracking->assignedTo?->user?->name,
                    'over_capacity' => (bool) $tracking->over_capacity,
                    'created_ago' => $order?->created_at?->diffForHumans() ?? '—',
                ];
            })
            ->values()
            ->all();
    }
}