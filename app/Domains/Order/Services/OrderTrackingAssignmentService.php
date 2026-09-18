<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\Concerns\ResolvesCapacityBalancedCandidates;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

        // 1. Resolve candidate pool
        $candidates = $this->resolveCandidatePool($storeId);

        // 2. Balance: fewest open assignments, then oldest last assignment
        $selected = $this->bestCandidateOnShift(
            $candidates,
            $storeId,
            'track',
            $this->openAssignmentCounts($storeId),
            $this->lastAssignedAt($storeId),
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
            ]);
            return $tracking;
        }

        $tracking->update([
            'assigned_to_membership_id' => $selected->id,
            'assigned_at' => now(),
            'assignment_method' => 'auto',
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
     * CRM_ORDER_TRACKING permission (no specialist tier — tracking has no
     * product-matching model yet).
     */
    private function resolveCandidatePool(string $storeId): Collection
    {
        // Eager load storeWithTimezone to avoid N+1 in isOnActiveShift
        return StoreMembership::where('store_id', $storeId)
            ->where('is_active', true)
            ->with('storeWithTimezone')
            ->get()
            ->filter(fn (StoreMembership $m) => $m->can(StorePermissionEnum::CRM_ORDER_TRACKING))
            ->values();
    }

    /**
     * Open trackings per assigned membership: only non-terminal tracking
     * statuses count toward a member's capacity.
     */
    private function openAssignmentCounts(string $storeId): array
    {
        $openStatuses = collect(OrderTrackingStatus::open())
            ->map(fn ($status) => $status->value)
            ->all();

        return DB::table('order_trackings')
            ->where('store_id', $storeId)
            ->whereNotNull('assigned_to_membership_id')
            ->whereIn('tracking_status', $openStatuses)
            ->selectRaw('assigned_to_membership_id, COUNT(*) as open_count')
            ->groupBy('assigned_to_membership_id')
            ->pluck('open_count', 'assigned_to_membership_id')
            ->toArray();
    }

    /**
     * Latest assigned_at per membership (order_trackings table).
     */
    private function lastAssignedAt(string $storeId): array
    {
        return DB::table('order_trackings')
            ->where('store_id', $storeId)
            ->whereNotNull('assigned_to_membership_id')
            ->whereNotNull('assigned_at')
            ->selectRaw('assigned_to_membership_id, MAX(assigned_at) as last_assigned')
            ->groupBy('assigned_to_membership_id')
            ->pluck('last_assigned', 'assigned_to_membership_id')
            ->toArray();
    }
}