<?php

namespace App\Domains\Order\Concerns;

use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use Illuminate\Support\Facades\Log;

/**
 * PHASE 35.2-C — shift-boundary sweep for open order trackings: the tracking
 * twin of HandlesShiftHandover with the same keep-and-strand /
 * replace-with-'handover' contract, scoped to open tracking statuses and the
 * 'track' role scope. Composed into OrderTrackingAssignmentService, which
 * supplies selectReplacement() and withDistributionLock().
 */
trait HandlesTrackingHandover
{
    public function handleShiftHandover(Store $store): void
    {
        $this->withDistributionLock($store->id, 'track', function () use ($store): void {
            $openTrackings = OrderTracking::where('store_id', $store->id)
                ->whereNotNull('assigned_to_membership_id')
                ->whereIn('tracking_status', $this->openTrackingStatusValues())
                ->with('assignedTo')
                ->get();

            foreach ($openTrackings as $tracking) {
                if ($tracking->assignedTo?->isOnActiveShift(null, 'track')) {
                    continue;
                }

                $this->strandOrReplaceTracking($tracking, $store);
            }
        });
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
