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
 * float to the top, oldest first, since they are the most urgent. Each tab
 * paginates independently (50 per page); the tab badge shows the TOTAL match
 * count from a separate lightweight count query.
 */
trait DistributionQueueConcern
{
    private const QUEUE_PAGE_SIZE = 50;

    public function loadQueue(): void
    {
        $storeId = currentStoreId();

        $this->confirmationCount = $this->confirmationRows($storeId, true);
        $this->confirmationPage = $this->clampPage((int) $this->confirmationPage, $this->confirmationCount);

        $confirmation = $this->confirmationRows($storeId);
        $this->confirmationQueue = $confirmation['data'] ?? [];
        $this->confirmationPagination = $this->pageMeta($confirmation);

        $this->trackingCount = $this->trackingRows($storeId, true);
        $this->trackingPage = $this->clampPage((int) $this->trackingPage, $this->trackingCount);

        $tracking = $this->trackingRows($storeId);
        $this->trackingQueue = $tracking['data'] ?? [];
        $this->trackingPagination = $this->pageMeta($tracking);
    }

    /**
     * Jump to a page of the given tab, then reload both queues so the badges
     * and per-tab pages stay in sync.
     */
    public function goToTabPage(string $tab, int $page): void
    {
        if ($tab === 'confirmation') {
            $this->confirmationPage = $this->clampPage($page, $this->confirmationCount);
        } else {
            $this->trackingPage = $this->clampPage($page, $this->trackingCount);
        }

        $this->loadQueue();
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
     * @return array<string, mixed>|int
     */
    private function confirmationRows(string $storeId, bool $countOnly = false): array|int
    {
        $query = Order::query()
            ->where('store_id', $storeId)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('assigned_to_membership_id')
                    ->orWhere('over_capacity', true);
            })
            ->whereHas('status', fn ($q) => $q->whereNotIn('key', $this->terminalOrderStatusKeys()))
            ->when(trim((string) ($this->queueSearch ?? '')) !== '', function ($query) {
                $search = trim((string) $this->queueSearch);

                $query->where(function ($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw('CASE WHEN assigned_to_membership_id IS NULL THEN 0 ELSE 1 END ASC, created_at ASC');

        if ($countOnly) {
            return $query->count();
        }

        return $query
            ->with(['customer', 'status', 'assignedMembership.user'])
            ->paginate(self::QUEUE_PAGE_SIZE, ['*'], 'page', $this->confirmationPage)
            ->through(fn (Order $order) => [
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
            ->toArray();
    }

    /**
     * @return array<string, mixed>|int
     */
    private function trackingRows(string $storeId, bool $countOnly = false): array|int
    {
        $openStatuses = collect(OrderTrackingStatus::open())
            ->map(fn (OrderTrackingStatus $status) => $status->value)
            ->all();

        $query = OrderTracking::query()
            ->where('store_id', $storeId)
            ->whereIn('tracking_status', $openStatuses)
            ->where(function ($q) {
                $q->whereNull('assigned_to_membership_id')
                    ->orWhere('over_capacity', true);
            })
            ->when(trim((string) ($this->queueSearch ?? '')) !== '', function ($query) {
                $search = trim((string) $this->queueSearch);

                $query->where(function ($q) use ($search) {
                    $q->where('tracking_number', 'like', "%{$search}%")
                        ->orWhereHas('order', function ($o) use ($search) {
                            $o->where(function ($inner) use ($search) {
                                $inner->where('number', 'like', "%{$search}%")
                                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                            });
                        });
                });
            })
            ->orderByRaw('CASE WHEN assigned_to_membership_id IS NULL THEN 0 ELSE 1 END ASC, created_at ASC');

        if ($countOnly) {
            return $query->count();
        }

        return $query
            ->with(['order.customer', 'order.status', 'assignedTo.user'])
            ->paginate(self::QUEUE_PAGE_SIZE, ['*'], 'page', $this->trackingPage)
            ->through(function (OrderTracking $tracking) {
                $order = $tracking->order;

                return [
                    'id' => (string) $tracking->id,
                    'number' => $order?->number ?? '—',
                    'customer' => $order?->customer?->name ?? '—',
                    'tracking_number' => $tracking->tracking_number,
                    'tracking_status' => $tracking->tracking_status,
                    'assigned_to' => $tracking->assignedTo?->user?->name,
                    'over_capacity' => (bool) $tracking->over_capacity,
                    'created_ago' => $order?->created_at?->diffForHumans() ?? '—',
                ];
            })
            ->toArray();
    }

    /**
     * @param  array<string, mixed>  $pagination
     * @return array<string, mixed>
     */
    private function pageMeta(array $pagination): array
    {
        unset(
            $pagination['data'],
            $pagination['links'],
            $pagination['first_page_url'],
            $pagination['next_page_url'],
            $pagination['prev_page_url'],
            $pagination['path'],
        );

        return $pagination;
    }

    private function clampPage(int $page, int $total): int
    {
        $lastPage = max(1, (int) ceil($total / self::QUEUE_PAGE_SIZE));

        return max(1, min($page, $lastPage));
    }
}
