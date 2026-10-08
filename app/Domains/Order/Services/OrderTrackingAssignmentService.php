<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\Concerns\GuardsDistributionLock;
use App\Domains\Order\Concerns\HandlesTrackingHandover;
use App\Domains\Order\Concerns\ResolvesCapacityBalancedCandidates;
use App\Domains\Order\Concerns\ResolvesProductOwnership;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreProductScopeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class OrderTrackingAssignmentService
{
    use GuardsDistributionLock, HandlesTrackingHandover, ResolvesCapacityBalancedCandidates, ResolvesProductOwnership;

    /**
     * Full auto-assignment pipeline for an order tracking. Serialised per
     * store + role scope like the confirmation pipeline (see
     * GuardsDistributionLock).
     */
    public function assign(OrderTracking $tracking): OrderTracking
    {
        $store = $tracking->store;

        if (! $store) {
            return $tracking;
        }

        $result = $this->withDistributionLock($store->id, 'track', function () use ($tracking, $store) {
            [$selected, $wasOverflow] = $this->selectReplacement($tracking, $store);

            if (! $selected) {
                $tracking->update([
                    'assigned_to_membership_id' => null,
                    'assigned_at' => null,
                    'assigned_by_membership_id' => null,
                    'assignment_method' => null,
                    'over_capacity' => false,
                ]);

                $this->notifyCapacityExhausted($store, 'track', $this->unassignedAssignmentCount(OrderTracking::class, $store->id, fn ($q) => $q
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
        });

        // Lock timeout: the tracking keeps its previous state untouched.
        return $result ?? $tracking;
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
     * Pool build + ownership-ranked selection for one tracking, without any
     * write — shared by the dispatcher (assign) and the handover sweep
     * (HandlesTrackingHandover). Returns [selected, wasOverflow].
     */
    private function selectReplacement(OrderTracking $tracking, Store $store): array
    {
        $storeId = $store->id;

        // Product ids of the underlying order drive both the visibility
        // guard and the ownership pool (tracking has no product dimension).
        $productIds = $this->orderProductIds($tracking);
        $candidates = $this->resolveCandidatePool($storeId, $productIds);

        // Ownership pool first (owners of the ordered products, ranked by
        // coverage then load then recency), then the general pool — strict
        // caps then store-configured overflow inside each pool, all driven
        // by one shared availability snapshot.
        $ownership = $this->ownershipCounts($storeId, $productIds);
        $openCounts = $this->openAssignmentCounts('order_trackings', $storeId, fn ($q) => $q->whereIn('tracking_status', $this->openTrackingStatusValues()));
        $lastAssigned = $this->lastAssignedAt('order_trackings', $storeId);
        $overflowPercentage = $this->overflowPercentage($store);
        $availability = $this->availabilitySnapshot($storeId, 'track', $candidates);

        $selected = [null, false];

        foreach ([true, false] as $owners) {
            $pass = $this->bestCandidateWithOverflow(
                $candidates->filter(fn (StoreMembership $m) => isset($ownership[$m->id]) === $owners),
                $storeId,
                'track',
                $openCounts,
                $lastAssigned,
                $overflowPercentage,
                $availability,
                $ownership,
            );

            if ($pass[0]) {
                $selected = $pass;
                break;
            }
        }

        if (! $selected[0]) {
            Log::warning('Order tracking auto-assignment skipped: no candidates on active shift', [
                'tracking_id' => $tracking->id,
                'order_id' => $tracking->order_id,
                'store_id' => $storeId,
                'candidate_count' => $candidates->count(),
                'candidate_ids' => $candidates->pluck('id')->toArray(),
            ]);
        }

        return $selected;
    }

    /**
     * Resolve candidate pool for a store: every active member holding the
     * CRM_ORDER_TRACKING permission whose visibility covers at least one of the
     * order's products. Ownership tiering (which of those members own the
     * ordered products) is decided later in assign() so a store without an
     * ownership roster still falls back to general trackers.
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
