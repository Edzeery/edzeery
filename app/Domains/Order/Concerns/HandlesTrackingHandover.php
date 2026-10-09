<?php

namespace App\Domains\Order\Concerns;

use App\Domains\Order\Support\ShiftAvailabilityResolver;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * PHASE 35.2-C — shift-boundary sweep for open order trackings: the tracking
 * twin of HandlesShiftHandover with the same keep-and-strand /
 * replace-with-'handover' contract, scoped to open tracking statuses and the
 * 'track' role scope. A keep-candidate must be an active member holding
 * CRM_ORDER_TRACKING on an active track shift. Composed into
 * OrderTrackingAssignmentService, which supplies selectReplacement() and
 * withDistributionLock().
 */
trait HandlesTrackingHandover
{
    public function handleShiftHandover(Store $store): void
    {
        $this->withDistributionLock($store->id, 'track', function () use ($store): void {
            // Stale out-of-scope flags (P35.3): a stranded row whose tracking
            // left the open set no longer needs attention — clear in one
            // batched UPDATE instead of per-row in the loop.
            OrderTracking::where('store_id', $store->id)
                ->whereNotNull('stranded_at')
                ->whereNotIn('tracking_status', $this->openTrackingStatusValues())
                ->update(['stranded_at' => null]);

            $openTrackings = OrderTracking::where('store_id', $store->id)
                ->whereNotNull('assigned_to_membership_id')
                ->whereIn('tracking_status', $this->openTrackingStatusValues())
                ->with('assignedTo')
                ->get();

            $eligible = $this->eligibleKeepMap(
                $store,
                'track',
                StorePermissionEnum::CRM_ORDER_TRACKING->value,
                $openTrackings,
            );

            foreach ($openTrackings as $tracking) {
                $assignee = $tracking->assignedTo;

                // On-role-shift assignees who are still active members holding
                // the tracking permission keep their rows; an eligible assignee
                // that was previously stranded is no longer in that state —
                // clear the flag (P35.3).
                if ($assignee !== null && ($eligible[$assignee->id] ?? false)) {
                    if ($tracking->stranded_at !== null) {
                        $tracking->update(['stranded_at' => null]);
                    }

                    continue;
                }

                $this->strandOrReplaceTracking($tracking, $store);
            }
        });
    }

    /**
     * Tracking twin of the confirmation eligibleKeepMap: active member + role
     * permission + active track-role shift, both batch-read instead of
     * per-row isOnActiveShift(null, 'track') checks.
     *
     * @param  Collection<int, OrderTracking>  $rows
     * @return array<int, bool>
     */
    private function eligibleKeepMap(Store $store, string $roleScope, string $permission, Collection $rows): array
    {
        $memberIds = $rows
            ->map(fn (OrderTracking $tracking) => $tracking->assignedTo?->id)
            ->filter()
            ->unique()
            ->values();

        if ($memberIds->isEmpty()) {
            return [];
        }

        $memberships = StoreMembership::with(['permissions', 'storeWithTimezone.settings'])
            ->whereIn('id', $memberIds->all())
            ->get();

        $availability = app(ShiftAvailabilityResolver::class)->resolve(
            $store->id,
            $roleScope,
            $memberships,
        );

        $eligible = [];

        foreach ($memberships as $member) {
            $eligible[$member->id] = (bool) $member->is_active
                && $member->can($permission)
                && ($availability[$member->id]['on_shift'] ?? false);
        }

        return $eligible;
    }

    /**
     * Tracking twin of strandOrReplaceOrder: replace, else keep and flag
     * stranded_at exactly once.
     */
    private function strandOrReplaceTracking(OrderTracking $tracking, Store $store): void
    {
        $previous = $tracking->assignedTo;
        [$selected, $wasOverflow] = $this->selectReplacement($tracking, $store);

        if ($selected) {
            $tracking->update([
                'assigned_to_membership_id' => $selected->id,
                'assigned_at' => now(),
                'assignment_method' => 'handover',
                'assigned_by_membership_id' => null,
                'over_capacity' => (bool) $wasOverflow,
                'stranded_at' => null,
            ]);

            Log::info('Order tracking reassigned during shift handover', [
                'tracking_id' => $tracking->id,
                'previous_membership_id' => $previous?->id,
                'membership_id' => $selected->id,
            ]);

            return;
        }

        if ($tracking->stranded_at === null) {
            $tracking->update(['stranded_at' => now()]);
        }

        Log::warning('Order tracking stranded during shift handover: no replacement on shift', [
            'tracking_id' => $tracking->id,
            'store_id' => $tracking->store_id,
            'previous_membership_id' => $previous?->id,
        ]);
    }
}
