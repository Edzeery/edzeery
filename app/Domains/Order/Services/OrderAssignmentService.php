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

        // 3. Filter to on-shift only
        $shiftFiltered = $this->filterOnShift($candidates);

        // 4. If shift-filtered pool is empty, leave unassigned for later dispatch
        if ($shiftFiltered->isEmpty()) {
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

        // 5. Load balancing — fewest open orders, then oldest assigned_at
        $selected = $this->loadBalance($shiftFiltered, $storeId);

        if (! $selected) {
            Log::warning('Order auto-assignment skipped: load balance returned null', [
                'order_id' => $order->id,
                'store_id' => $storeId,
                'shift_filtered_count' => $shiftFiltered->count(),
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
     */
    private function resolveCandidatePool(string $storeId, array $productIds): \Illuminate\Support\Collection
    {
        // Members with ORDER_CONFIRM permission in this store
        // Eager load storeWithTimezone to avoid N+1 in isOnActiveShift
        $allConfirmers = StoreMembership::where('store_id', $storeId)
            ->where('is_active', true)
            ->with('storeWithTimezone')
            ->get()
            ->filter(fn (StoreMembership $m) => $m->can(StorePermissionEnum::ORDER_CONFIRM))
            ->values();

        if ($allConfirmers->isEmpty()) {
            Log::warning('Order auto-assignment skipped: no members with ORDER_CONFIRM permission', [
                'store_id' => $storeId,
            ]);
            return collect();
        }

        if (empty($productIds)) {
            return $allConfirmers;
        }

        // Find specialists mapped to these products
        $specialistMembershipIds = ConfirmationProductAssignment::where('store_id', $storeId)
            ->whereIn('product_id', $productIds)
            ->pluck('membership_id')
            ->unique()
            ->toArray();

        if (! empty($specialistMembershipIds)) {
            return $allConfirmers->filter(
                fn (StoreMembership $m) => in_array($m->id, $specialistMembershipIds)
            )->values();
        }

        // No specialists for these products → general pool
        // Exclude members who ONLY have product-specific assignments (for other products)
        $onlySpecialistIds = ConfirmationProductAssignment::where('store_id', $storeId)
            ->whereNotIn('product_id', $productIds)
            ->pluck('membership_id')
            ->unique()
            ->toArray();

        $generalPool = $allConfirmers->filter(
            fn (StoreMembership $m) => ! in_array($m->id, $onlySpecialistIds)
        )->values();

        // Fallback: if general pool is empty, return all confirmers
        return $generalPool->isEmpty() ? $allConfirmers : $generalPool;
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
    private function loadBalance(\Illuminate\Support\Collection $candidates, string $storeId): ?StoreMembership
    {
        $terminalStatusKeys = ['confirmed', 'cancelled', 'delivered', 'returned', 'completed', 'refunded', 'canceled'];

        $openOrderCounts = DB::table('orders')
            ->join('statuses', 'orders.status_id', '=', 'statuses.id')
            ->where('orders.store_id', $storeId)
            ->whereNull('orders.deleted_at')
            ->whereNotNull('orders.assigned_to_membership_id')
            ->whereNotIn('statuses.key', $terminalStatusKeys)
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
