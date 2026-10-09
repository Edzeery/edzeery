<?php

namespace App\Domains\Order\Concerns;

use App\Domains\Order\Support\OrderDistributionStage;
use App\Domains\Order\Support\ShiftAvailabilityResolver;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * PHASE 35.2-C — shift-boundary sweep for confirmation orders.
 *
 * Composed into OrderAssignmentService, which supplies selectReplacement()
 * (pool + ownership-ranked selection, no write) and withDistributionLock()
 * from GuardsDistributionLock. Contract: only confirmation-stage statuses
 * (statuses.distribution_stage = confirmation, NULL treated as confirmation)
 * are touched — fulfillment and closed orders keep their assignment; an
 * assignee who is no longer a valid keep-candidate (inactive membership, lost
 * ORDER_CONFIRM permission, or off-shift) is replaced with assignment_method
 * 'handover'; when nobody eligible remains the assignment is kept and flagged
 * stranded_at once instead of cleared.
 */
trait HandlesShiftHandover
{
    public function handleShiftHandover(Store $store): void
    {
        $this->withDistributionLock($store->id, 'confirm', function () use ($store): void {
            // Stale out-of-scope flags (P35.3): a stranded row whose status
            // moved out of the confirmation stage no longer needs attention —
            // clear in one batched UPDATE instead of per-row in the loop.
            Order::where('store_id', $store->id)
                ->whereNotNull('stranded_at')
                ->whereHas('status', fn ($q) => $q
                    ->whereNotNull('distribution_stage')
                    ->where('distribution_stage', '!=', OrderDistributionStage::CONFIRMATION))
                ->update(['stranded_at' => null]);

            $openOrders = Order::where('store_id', $store->id)
                ->whereNotNull('assigned_to_membership_id')
                ->whereHas('status', fn ($q) => $q
                    ->where('distribution_stage', OrderDistributionStage::CONFIRMATION)
                    ->orWhereNull('distribution_stage'))
                ->with('assignedMembership')
                ->get();

            $eligible = $this->eligibleKeepMap(
                $store,
                'confirm',
                StorePermissionEnum::ORDER_CONFIRM->value,
                $openOrders,
            );

            foreach ($openOrders as $order) {
                $assignee = $order->assignedMembership;

                // On-shift assignees who are still active members holding the
                // role permission keep their orders; a removed membership
                // resolves to null and counts as ineligible (reassigned here).
                // An eligible assignee that was previously stranded is no
                // longer in that state — clear the flag (P35.3).
                if ($assignee !== null && ($eligible[$assignee->id] ?? false)) {
                    if ($order->stranded_at !== null) {
                        $order->update(['stranded_at' => null]);
                    }

                    continue;
                }

                $this->strandOrReplaceOrder($order, $store);
            }
        });
    }

    /**
     * Membership eligibility for KEEPING an assignment during the sweep, keyed
     * by membership id: the member must still be an active store member who
     * holds the role permission and is on an active shift of this role scope.
     * The permission rows and the shift/cap availability are both batch-read
     * (one query each) instead of per-row isOnActiveShift() checks.
     *
     * @param  Collection<int, Order>  $rows
     * @return array<int, bool>
     */
    private function eligibleKeepMap(Store $store, string $roleScope, string $permission, Collection $rows): array
    {
        $memberIds = $rows
            ->map(fn (Order $order) => $order->assignedMembership?->id)
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
     * Replace an off-shift assignee with the best on-shift candidate; with
     * no eligible replacement keep the current assignment and record
     * stranded_at once — later sweeps retry until a replacement appears and
     * clears it.
     */
    private function strandOrReplaceOrder(Order $order, Store $store): void
    {
        $previous = $order->assignedMembership;
        [$selected, $wasOverflow] = $this->selectReplacement($order, $store);

        if ($selected) {
            $order->update([
                'assigned_to_membership_id' => $selected->id,
                'assigned_at' => now(),
                'assignment_method' => 'handover',
                'assigned_by_membership_id' => null,
                'over_capacity' => (bool) $wasOverflow,
                'stranded_at' => null,
            ]);

            Log::info('Order reassigned during shift handover', [
                'order_id' => $order->id,
                'previous_membership_id' => $previous?->id,
                'membership_id' => $selected->id,
            ]);

            return;
        }

        if ($order->stranded_at === null) {
            $order->update(['stranded_at' => now()]);
        }

        Log::warning('Order stranded during shift handover: no replacement on shift', [
            'order_id' => $order->id,
            'store_id' => $order->store_id,
            'previous_membership_id' => $previous?->id,
        ]);
    }
}
