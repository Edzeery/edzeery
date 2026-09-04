<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Domains\Order\Models\ConfirmationShift;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderAssignmentService
{
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
        $selected = $this->selectBest($candidates, $storeId, $productIds);

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
            return $order;
        }

        $order->update([
            'assigned_to_membership_id' => $selected->id,
            'assigned_at' => now(),
            'assignment_method' => 'auto',
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
        $terminalStatuses = ['cancelled', 'delivered', 'returned', 'completed', 'refunded', 'canceled'];

        $openOrders = Order::where('store_id', $store->id)
            ->whereNotNull('assigned_to_membership_id')
            ->whereHas('status', fn ($q) => $q->whereNotIn('key', $terminalStatuses))
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
     * Returns every active ORDER_CONFIRM member; tier selection (specialists
     * vs general) is decided later in selectBest() so a store with a narrow
     * specialist roster still falls back to general confirmers.
     */
    private function resolveCandidatePool(string $storeId, array $productIds): \Illuminate\Support\Collection
    {
        // Members with ORDER_CONFIRM permission in this store
        // Eager load storeWithTimezone to avoid N+1 in isOnActiveShift
        return StoreMembership::where('store_id', $storeId)
            ->where('is_active', true)
            ->with('storeWithTimezone')
            ->get()
            ->filter(fn (StoreMembership $m) => $m->can(StorePermissionEnum::ORDER_CONFIRM))
            ->values();
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
     * Effective open-order cap for a member: the highest max_concurrent_orders
     * across their active shifts (a cap is shift-scoped, the member owns the total).
     */
    private function membershipCap(string $storeId, string $membershipId): ?int
    {
        return ConfirmationShift::where('store_id', $storeId)
            ->where('membership_id', $membershipId)
            ->where('is_active', true)
            ->whereNotNull('max_concurrent_orders')
            ->max('max_concurrent_orders');
    }

    /**
     * Status keys considered "terminal" (excluded from the open-order count).
     */
    private function terminalStatusKeys(): array
    {
        return ['cancelled', 'delivered', 'returned', 'completed', 'refunded', 'canceled'];
    }

    /**
     * Select the best candidate following priority tiers:
     *  1. On-shift specialists (product-matched to the order)
     *  2. On-shift general confirmers
     * Within each tier, balance by fewest open orders then oldest assignment.
     * Members who reached their max_concurrent_orders cap are skipped entirely.
     */
    private function selectBest(
        \Illuminate\Support\Collection $candidates,
        string $storeId,
        array $productIds
    ): ?StoreMembership {
        if ($candidates->isEmpty()) {
            Log::warning('Order auto-assignment skipped: no members with ORDER_CONFIRM permission', [
                'store_id' => $storeId,
            ]);
            return null;
        }

        $specialistIds = $this->specialistMembershipIds($storeId, $productIds);

        $openCounts = DB::table('orders')
            ->join('statuses', 'orders.status_id', '=', 'statuses.id')
            ->where('orders.store_id', $storeId)
            ->whereNull('orders.deleted_at')
            ->whereNotNull('orders.assigned_to_membership_id')
            ->whereNotIn('statuses.key', $this->terminalStatusKeys())
            ->select('orders.assigned_to_membership_id', DB::raw('COUNT(*) as open_count'))
            ->groupBy('orders.assigned_to_membership_id')
            ->pluck('open_count', 'assigned_to_membership_id')
            ->toArray();

        $withinQuota = function (StoreMembership $m) use ($storeId, $openCounts): bool {
            $cap = $this->membershipCap($storeId, $m->id);
            if ($cap === null) {
                return true;
            }
            return ($openCounts[$m->id] ?? 0) < $cap;
        };

        $onShiftSpecialists = $this->filterOnShift(
            $candidates->filter(fn (StoreMembership $m) => in_array($m->id, $specialistIds))
        )->filter($withinQuota);

        if ($onShiftSpecialists->isNotEmpty()) {
            return $this->loadBalance($onShiftSpecialists, $storeId, $openCounts);
        }

        $onShiftGeneral = $this->filterOnShift(
            $candidates->filter(fn (StoreMembership $m) => ! in_array($m->id, $specialistIds))
        )->filter($withinQuota);

        if ($onShiftGeneral->isNotEmpty()) {
            return $this->loadBalance($onShiftGeneral, $storeId, $openCounts);
        }

        return null;
    }

    /**
     * Filter candidates to those on active shift.
     */
    private function filterOnShift(\Illuminate\Support\Collection $candidates): \Illuminate\Support\Collection
    {
        return $candidates->filter(function (StoreMembership $m) {
            $isOnShift = $m->isOnActiveShift();
            if (! $isOnShift) {
                Log::debug('Member not on active shift', [
                    'membership_id' => $m->id,
                    'user_id' => $m->user_id,
                ]);
            }
            return $isOnShift;
        })->values();
    }

    /**
     * Load-balance: fewest open orders, then oldest assigned_at.
     */
    /**
     * Load-balance: fewest open orders, then oldest assigned_at.
     * Accepts an optional precomputed open-count map to avoid a duplicate query.
     */
    private function loadBalance(
        \Illuminate\Support\Collection $candidates,
        string $storeId,
        array $openOrderCounts = []
    ): ?StoreMembership {
        $openOrderCounts = $openOrderCounts ?: DB::table('orders')
            ->join('statuses', 'orders.status_id', '=', 'statuses.id')
            ->where('orders.store_id', $storeId)
            ->whereNull('orders.deleted_at')
            ->whereNotNull('orders.assigned_to_membership_id')
            ->whereNotIn('statuses.key', $this->terminalStatusKeys())
            ->select('orders.assigned_to_membership_id', DB::raw('COUNT(*) as open_count'))
            ->groupBy('orders.assigned_to_membership_id')
            ->pluck('open_count', 'assigned_to_membership_id')
            ->toArray();

        $lastAssigned = DB::table('orders')
            ->where('store_id', $storeId)
            ->whereNull('deleted_at')
            ->whereNotNull('assigned_to_membership_id')
            ->whereNotNull('assigned_at')
            ->select('assigned_to_membership_id', DB::raw('MAX(assigned_at) as last_assigned'))
            ->groupBy('assigned_to_membership_id')
            ->pluck('last_assigned', 'assigned_to_membership_id')
            ->toArray();

        return $candidates->sortBy([
            fn (StoreMembership $m) => $openOrderCounts[$m->id] ?? 0,
            fn (StoreMembership $m) => $lastAssigned[$m->id] ?? '1970-01-01',
        ])->first();
    }
}
