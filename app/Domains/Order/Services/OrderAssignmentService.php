<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\Concerns\ResolvesCapacityBalancedCandidates;
use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreProductScopeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class OrderAssignmentService
{
    use ResolvesCapacityBalancedCandidates;

    /**
     * Full auto-assignment pipeline for an order.
     */
    public function assign(Order $order): Order
    {
        $store = $order->store;

        if (! $store) {
            return $order;
        }

        $storeId = $store->id;

        // 1. Get product IDs from order items
        $productIds = $order->items()
            ->whereNotNull('product_id')
            ->pluck('product_id')
            ->unique()
            ->toArray();

        // 2. Resolve candidate pool
        $candidates = $this->resolveCandidatePool($storeId, $productIds);

        // 3. Prioritize: specialists (product-matched) → general on-shift pool
        [$selected, $wasOverflow] = $this->selectBest($store, $candidates, $storeId, $productIds);

        if (! $selected) {
            Log::warning('Order auto-assignment skipped: no candidates on active shift', [
                'order_id' => $order->id,
                'store_id' => $storeId,
                'candidate_count' => $candidates->count(),
                'candidate_ids' => $candidates->pluck('id')->toArray(),
                'product_ids' => $productIds,
            ]);

            $order->update([
                'assigned_to_membership_id' => null,
                'assigned_at' => null,
                'assigned_by_membership_id' => null,
                'assignment_method' => null,
            ]);

            $this->notifyCapacityExhausted($store, 'confirm', $this->unassignedAssignmentCount(Order::class, $storeId, fn ($q) => $q
                ->whereHas('status', fn ($q) => $q->where('key', 'pending'))));

            return $order;
        }

        $order->update([
            'assigned_to_membership_id' => $selected->id,
            'assigned_at' => now(),
            'assignment_method' => 'auto',
            ...($wasOverflow ? ['over_capacity' => true] : []),
        ]);

        Log::info('Order auto-assigned', [
            'order_id' => $order->id,
            'membership_id' => $selected->id,
            'user_id' => $selected->user_id,
        ]);

        return $order;
    }

    /**
     * Manual reassignment — bypasses all eligibility checks.
     */
    public function reassign(Order $order, StoreMembership $to, StoreMembership $by): Order
    {
        if ($to->store_id !== $order->store_id) {
            throw new \InvalidArgumentException('Cannot assign order to a member of a different store.');
        }

        $order->update([
            'assigned_to_membership_id' => $to->id,
            'assigned_at' => now(),
            'assignment_method' => 'manual',
            'assigned_by_membership_id' => $by->id,
        ]);

        Log::info('Order manually reassigned', [
            'order_id' => $order->id,
            'to_membership_id' => $to->id,
            'by_membership_id' => $by->id,
        ]);

        return $order;
    }

    /**
     * Reassignment sweep at shift boundaries.
     */
    public function handleShiftHandover(Store $store): void
    {
        $openOrders = Order::where('store_id', $store->id)
            ->whereNotNull('assigned_to_membership_id')
            ->whereHas('status', fn ($q) => $q->whereNotIn('key', $this->terminalStatusKeys()))
            ->with('store.settings')
            ->get();

        foreach ($openOrders as $order) {
            $assignedMembership = $order->assignedMembership;

            if ($assignedMembership && ! $assignedMembership->isOnActiveShift()) {
                $this->assign($order);

                Log::info('Order reassigned during shift handover', [
                    'order_id' => $order->id,
                    'previous_membership_id' => $assignedMembership->id,
                ]);
            }
        }
    }

    /**
     * Resolve candidate pool for a store and set of product IDs.
     *
     * Returns every active ORDER_CONFIRM member whose visibility covers at
     * least one of the order's products; tier selection (specialists vs
     * general) is decided later in selectBest() so a store with a narrow
     * specialist roster still falls back to general confirmers.
     */
    private function resolveCandidatePool(string $storeId, array $productIds): Collection
    {
        // Members with ORDER_CONFIRM permission in this store
        // Eager load storeWithTimezone to avoid N+1 in isOnActiveShift
        $candidates = StoreMembership::where('store_id', $storeId)
            ->where('is_active', true)
            ->with('storeWithTimezone')
            ->get()
            ->filter(fn (StoreMembership $m) => $m->can(StorePermissionEnum::ORDER_CONFIRM))
            ->values();

        // Never hand an order to someone it would be invisible to: a manager
        // whose product scope misses every item would be unable to open it.
        // Specialist tiering below is untouched — this is an extra exclusion.
        return app(StoreProductScopeService::class)->filterByVisibility($candidates, $productIds);
    }

    /**
     * Membership IDs assigned to any of the given products (specialists).
     */
    private function specialistMembershipIds(string $storeId, array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        return ConfirmationProductAssignment::where('store_id', $storeId)
            ->whereIn('product_id', $productIds)
            ->pluck('membership_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Select the best candidate following priority tiers:
     *  1. On-shift specialists (product-matched to the order)
     *  2. On-shift general confirmers
     * Within each tier, balance by fewest open orders then oldest last
     * assignment. Members who reached their time-aware max_concurrent_orders
     * cap are skipped; when the store enables soft overflow, the cap is
     * extended by the configured percentage before giving up (see
     * ResolvesCapacityBalancedCandidates). Returns [selected, wasOverflow].
     */
    private function selectBest(
        Store $store,
        Collection $candidates,
        string $storeId,
        array $productIds
    ): array {
        if ($candidates->isEmpty()) {
            Log::warning('Order auto-assignment skipped: no members with ORDER_CONFIRM permission', [
                'store_id' => $storeId,
            ]);

            return [null, false];
        }

        $overflowPercentage = $this->overflowPercentage($store);
        $specialistIds = $this->specialistMembershipIds($storeId, $productIds);
        $openCounts = $this->openAssignmentCounts('orders', $storeId, fn ($q) => $q
            ->join('statuses', 'orders.status_id', '=', 'statuses.id')
            ->whereNotIn('statuses.key', $this->terminalStatusKeys()));
        $lastAssigned = $this->lastAssignedAt('orders', $storeId);

        // One availability snapshot for both tier passes — the shifts do not
        // change mid-call, so the specialist and general tiers must see the
        // same on-shift/cap view (and it costs one query, not one per tier).
        $availability = $this->availabilitySnapshot($storeId, 'confirm', $candidates);

        // Specialists (product-matched) first, then general confirmers.
        foreach ([true, false] as $specialists) {
            [$best, $wasOverflow] = $this->bestCandidateWithOverflow(
                $candidates->filter(fn (StoreMembership $m) => (in_array($m->id, $specialistIds)) === $specialists),
                $storeId,
                'confirm',
                $openCounts,
                $lastAssigned,
                $overflowPercentage,
                $availability,
            );

            if ($best) {
                return [$best, $wasOverflow];
            }
        }

        return [null, false];
    }
}
