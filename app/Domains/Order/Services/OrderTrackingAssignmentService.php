<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\Concerns\ResolvesCapacityBalancedCandidates;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreProductScopeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class OrderTrackingAssignmentService
{
    use ResolvesCapacityBalancedCandidates;

    /**
     * Full auto-assignment pipeline for an order tracking.
     */
    public function assign(OrderTracking $tracking): OrderTracking
    {
        $store = $tracking->store;

        if (! $store) {
            return $tracking;
        }

        $storeId = $store->id;

        // 1. Resolve candidate pool (product ids of the underlying order drive
        //    the visibility guard — tracking itself has no product dimension)
        $candidates = $this->resolveCandidatePool($storeId, $this->orderProductIds($tracking));

        // 2. Balance: fewest open assignments, then oldest last assignment,
        //    allowing store-configured overflow when no member fits strictly.
        //    A single availability snapshot drives the whole selection round.
        [$selected, $wasOverflow] = $this->bestCandidateWithOverflow(
            $candidates,
            $storeId,
            'track',
            $this->openAssignmentCounts('order_trackings', $storeId, fn ($q) => $q->whereIn('tracking_status', $this->openTrackingStatusValues())),
            $this->lastAssignedAt('order_trackings', $storeId),
            $this->overflowPercentage($store),
            $this->availabilitySnapshot($storeId, 'track', $candidates),
        );

        if (! $selected) {
            Log::warning('Order tracking auto-assignment skipped: no candidates on active shift', [
                'tracking_id' => $tracking->id,
                'order_id' => $tracking->order_id,
                'store_id' => $storeId,
                'candidate_count' => $candidates->count(),
                'candidate_ids' => $candidates->pluck('id')->toArray(),
            ]);

            $tracking->update([
                'assigned_to_membership_id' => null,
                'assigned_at' => null,
                'assigned_by_membership_id' => null,
                'assignment_method' => null,
                'over_capacity' => false,
            ]);

            $this->notifyCapacityExhausted($store, 'track', $this->unassignedAssignmentCount(OrderTracking::class, $storeId, fn ($q) => $q
                ->whereIn('tracking_status', $this->openTrackingStatusValues())));

            return $tracking;
        }

        $tracking->update([
            'assigned_to_membership_id' => $selected->id,
            'assigned_at' => now(),
            'assignment_method' => 'auto',
            ...($wasOverflow ? ['over_capacity' => true] : []),
        ]);

        Log::info('Order tracking auto-assigned', [
            'tracking_id' => $tracking->id,
            'membership_id' => $selected->id,
            'user_id' => $selected->user_id,
        ]);

        return $tracking;
    }

    /**
     * Manual reassignment — bypasses all eligibility checks.
     */
    public function reassign(OrderTracking $tracking, StoreMembership $to, StoreMembership $by): OrderTracking
    {
        if ($to->store_id !== $tracking->store_id) {
            throw new \InvalidArgumentException('Cannot assign tracking to a member of a different store.');
        }

        $tracking->update([
            'assigned_to_membership_id' => $to->id,
            'assigned_at' => now(),
            'assignment_method' => 'manual',
            'assigned_by_membership_id' => $by->id,
        ]);

        Log::info('Order tracking manually reassigned', [
            'tracking_id' => $tracking->id,
            'to_membership_id' => $to->id,
            'by_membership_id' => $by->id,
        ]);

        return $tracking;
    }

    /**
     * Resolve candidate pool for a store: every active member holding the
     * CRM_ORDER_TRACKING permission whose visibility covers at least one of the
     * order's products (no specialist tier — tracking has no product-matching
     * model yet).
     */
    private function resolveCandidatePool(string $storeId, array $productIds): Collection
    {
        // Eager load storeWithTimezone to avoid N+1 in isOnActiveShift
        $candidates = StoreMembership::where('store_id', $storeId)
            ->where('is_active', true)
            ->with('storeWithTimezone')
            ->get()
            ->filter(fn (StoreMembership $m) => $m->can(StorePermissionEnum::CRM_ORDER_TRACKING))
            ->values();

        return app(StoreProductScopeService::class)->filterByVisibility($candidates, $productIds);
    }

    /**
     * Distinct product ids of the tracked order — the same set the confirm
     * pipeline feeds to the specialist tiering, reused here so a manager whose
     * product scope excludes every item is never auto-assigned the shipment.
     */
    private function orderProductIds(OrderTracking $tracking): array
    {
        if (! $tracking->order_id) {
            return [];
        }

        return OrderItem::query()
            ->where('order_id', $tracking->order_id)
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id')
            ->all();
    }

    /**
     * Open tracking status values (terminal statuses come straight from the
     * enum; tracking has no soft-deletes).
     */
    private function openTrackingStatusValues(): array
    {
        return collect(OrderTrackingStatus::open())
            ->map(fn ($status) => $status->value)
            ->all();
    }
}
