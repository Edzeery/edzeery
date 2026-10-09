<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\Concerns\GuardsDistributionLock;
use App\Domains\Order\Concerns\HandlesShiftHandover;
use App\Domains\Order\Concerns\ResolvesCapacityBalancedCandidates;
use App\Domains\Order\Support\OrderDistributionStage;
use App\Domains\Order\Support\ProductOwnershipRouter;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreProductScopeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class OrderAssignmentService
{
    use GuardsDistributionLock, HandlesShiftHandover, ResolvesCapacityBalancedCandidates;

    /**
     * Full auto-assignment pipeline for an order. The critical section is
     * serialised per store + role scope (see GuardsDistributionLock) so a
     * concurrent dispatcher or handover sweep cannot double-book a member.
     */
    public function assign(Order $order): Order
    {
        $store = $order->store;

        if (! $store) {
            return $order;
        }

        $result = $this->withDistributionLock($store->id, 'confirm', function () use ($order, $store) {
            [$selected, $wasOverflow] = $this->selectReplacement($order, $store);

            if (! $selected) {
                $order->update([
                    'assigned_to_membership_id' => null,
                    'assigned_at' => null,
                    'assigned_by_membership_id' => null,
                    'assignment_method' => null,
                ]);

                $this->notifyCapacityExhausted($store, 'confirm', $this->unassignedAssignmentCount(Order::class, $store->id, fn ($q) => $q
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
        });

        // Lock timeout: the order keeps its previous state untouched and the
        // next scheduled sweep retries it.
        return $result ?? $order;
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
            'stranded_at' => null,
        ]);

        Log::info('Order manually reassigned', [
            'order_id' => $order->id,
            'to_membership_id' => $to->id,
            'by_membership_id' => $by->id,
        ]);

        return $order;
    }

    /**
     * Pool build + ownership-ranked selection for one order, without any
     * write — shared by the dispatcher (assign) and the handover sweep
     * (HandlesShiftHandover), which differ only in what they do with the
     * result. Returns [selected, wasOverflow].
     */
    private function selectReplacement(Order $order, Store $store): array
    {
        $storeId = $store->id;

        $productIds = $order->items()
            ->whereNotNull('product_id')
            ->pluck('product_id')
            ->unique()
            ->toArray();

        $roleMembers = $this->roleMembers($storeId);
        $candidates = app(StoreProductScopeService::class)->filterByVisibility($roleMembers, $productIds);

        $selected = $this->selectBest($store, $roleMembers, $candidates, $storeId, $productIds);

        if (! $selected[0]) {
            Log::warning('Order auto-assignment skipped: no candidates on active shift', [
                'order_id' => $order->id,
                'store_id' => $storeId,
                'candidate_count' => $candidates->count(),
                'candidate_ids' => $candidates->pluck('id')->toArray(),
                'product_ids' => $productIds,
            ]);
        }

        return $selected;
    }

    /**
     * Active members holding ORDER_CONFIRM — the ownership authority and the
     * superset the strict pool is drawn from. One query; visibility narrowing
     * happens on top of this collection.
     *
     * @return Collection<int, StoreMembership>
     */
    private function roleMembers(string $storeId): Collection
    {
        return StoreMembership::where('store_id', $storeId)
            ->where('is_active', true)
            ->with('storeWithTimezone')
            ->get()
            ->filter(fn (StoreMembership $m) => $m->can(StorePermissionEnum::ORDER_CONFIRM))
            ->values();
    }

    /**
     * Route the order through the strict ownership rules (ProductOwnershipRouter)
     * and pick one candidate inside the resulting pool: owners ranked by coverage
     * then fewest open orders then oldest last assignment; general/all candidates
     * by load then recency. Caps apply inside the pool; overflow extends caps
     * inside the same pool. There is NO fallback outside the routed pool.
     * Returns [selected, wasOverflow].
     */
    private function selectBest(
        Store $store,
        Collection $roleMembers,
        Collection $candidates,
        string $storeId,
        array $productIds,
    ): array {
        if ($candidates->isEmpty()) {
            Log::warning('Order auto-assignment skipped: no members with ORDER_CONFIRM permission', [
                'store_id' => $storeId,
            ]);

            return [null, false];
        }

        $routed = app(ProductOwnershipRouter::class)
            ->route($storeId, 'confirm', $roleMembers, $candidates, $productIds);

        $pool = $routed['pool'];

        if ($pool->isEmpty()) {
            return [null, false];
        }

        $overflowPercentage = $this->overflowPercentage($store);
        $openCounts = $this->openAssignmentCounts('orders', $storeId, fn ($q) => $q
            ->join('statuses', 'orders.status_id', '=', 'statuses.id')
            ->where(fn ($q) => $q
                ->where('statuses.distribution_stage', OrderDistributionStage::CONFIRMATION)
                ->orWhereNull('statuses.distribution_stage')));
        $lastAssigned = $this->lastAssignedAt('orders', $storeId);
        $availability = $this->availabilitySnapshot($storeId, 'confirm', $pool);

        return $this->bestCandidateWithOverflow(
            $pool,
            $storeId,
            'confirm',
            $openCounts,
            $lastAssigned,
            $overflowPercentage,
            $availability,
            $routed['coverage'],
        );
    }
}
