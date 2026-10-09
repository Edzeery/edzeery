<?php

namespace App\Domains\Order\Concerns;

use App\Domains\Order\Support\ShiftAvailabilityResolver;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Notifications\AssignmentCapacityExhaustedNotification;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Capacity-balanced candidate resolution shared by the confirmation and tracking
 * auto-assignment pipelines: on-shift filtering, time-aware shift caps, product
 * ownership coverage ranking, load balancing (fewest open assignments, then
 * oldest last assignment), optional soft overflow (store-configured % headroom
 * above a member's base cap) and the capacity-exhausted alert (throttled per
 * store + role scope). The consumer supplies the permission-filtered candidate
 * pool, the ownership-coverage map resolved by ProductOwnershipRouter and may
 * pass a ShiftAvailabilityResolver snapshot shared by every selection pass of
 * one assignment call; without it a snapshot is resolved once per call.
 *
 * The shared query helpers build the source-specific open-count,
 * last-assigned and unassigned maps for both orders and order_trackings.
 */
trait ResolvesCapacityBalancedCandidates
{
    /**
     * Availability snapshot for the pool: one query per assignment call
     * instead of one per member. See ShiftAvailabilityResolver for the
     * covering-shift/cap semantics.
     */
    protected function availabilitySnapshot(
        string $storeId,
        string $roleScope,
        Collection $candidates,
        ?Carbon $at = null,
    ): array {
        return app(ShiftAvailabilityResolver::class)->resolve($storeId, $roleScope, $candidates, $at);
    }

    /**
     * Overflow-aware selection: strict-quota pass first, then effective caps
     * extended by the store's overflow percentage (capped members only).
     * The ownership map (membership id => owned ordered products) ranks
     * before load within the pool. Returns [selected, wasOverflow].
     */
    protected function bestCandidateWithOverflow(
        Collection $candidates,
        string $storeId,
        string $roleScope,
        array $openCounts,
        array $lastAssignedAt,
        ?int $overflowPercentage,
        ?array $availability = null,
        array $ownership = [],
    ): array {
        $availability ??= $this->availabilitySnapshot($storeId, $roleScope, $candidates);

        $selected = $this->bestOnShiftWithinCaps(
            $candidates,
            $openCounts,
            $lastAssignedAt,
            $this->capsFromAvailability($candidates, $availability),
            $availability,
            $ownership,
        );

        if ($selected) {
            return [$selected, false];
        }

        if ($overflowPercentage === null || $overflowPercentage <= 0) {
            return [null, false];
        }

        $extendedCaps = [];

        foreach ($this->capsFromAvailability($candidates, $availability) as $memberId => $cap) {
            $extendedCaps[$memberId] = max($cap, (int) ceil($cap * (1 + $overflowPercentage / 100)));
        }

        $selected = $this->bestOnShiftWithinCaps($candidates, $openCounts, $lastAssignedAt, $extendedCaps, $availability, $ownership);

        return $selected ? [$selected, true] : [null, false];
    }

    /**
     * Overflow percentage from store settings, or null when disabled/missing
     * so selection falls back to the strict-quota behavior.
     */
    protected function overflowPercentage(Store $store): ?int
    {
        $settings = $store->settings;

        if (! $settings || ! $settings->distribution_overflow_enabled) {
            return null;
        }

        $percentage = $settings->distribution_overflow_percentage;

        return ($percentage !== null && $percentage > 0) ? $percentage : null;
    }

    /**
     * Throttled capacity-exhausted alert to the store owner: only the first
     * failure of a store/role-scope within a 30-minute window sends.
     */
    protected function notifyCapacityExhausted(Store $store, string $roleScope, int $unassignedCount): void
    {
        if (! Cache::add("assignment_overflow_alert:{$store->id}:{$roleScope}", true, now()->addMinutes(30))) {
            return;
        }

        $store->owner?->notify(new AssignmentCapacityExhaustedNotification($store, $roleScope, $unassignedCount));
    }

    protected function openAssignmentCounts(string $table, string $storeId, ?Closure $statusScope = null): array
    {
        $query = DB::table($table)
            ->where("$table.store_id", $storeId)
            ->whereNotNull("$table.assigned_to_membership_id");

        if ($statusScope !== null) {
            $statusScope($query);
        }

        return $query
            ->when($table === 'orders', fn ($q) => $q->whereNull("$table.deleted_at"))
            ->select("$table.assigned_to_membership_id", DB::raw('COUNT(*) as open_count'))
            ->groupBy("$table.assigned_to_membership_id")
            ->pluck('open_count', "$table.assigned_to_membership_id")
            ->toArray();
    }

    protected function lastAssignedAt(string $table, string $storeId): array
    {
        return DB::table($table)
            ->where("$table.store_id", $storeId)
            ->when($table === 'orders', fn ($q) => $q->whereNull("$table.deleted_at"))
            ->whereNotNull("$table.assigned_to_membership_id")
            ->whereNotNull("$table.assigned_at")
            ->select("$table.assigned_to_membership_id", DB::raw('MAX(assigned_at) as last_assigned'))
            ->groupBy("$table.assigned_to_membership_id")
            ->pluck('last_assigned', "$table.assigned_to_membership_id")
            ->toArray();
    }

    /**
     * Unassigned items left in the dispatcher's target state for the alert.
     */
    protected function unassignedAssignmentCount(string $model, string $storeId, Closure $statusScope): int
    {
        return $model::where('store_id', $storeId)
            ->whereNull('assigned_to_membership_id')
            ->whereNull('assignment_method')
            ->where($statusScope)
            ->count();
    }

    private function bestOnShiftWithinCaps(
        Collection $candidates,
        array $openCounts,
        array $lastAssignedAt,
        array $caps,
        array $availability,
        array $ownership = [],
    ): ?StoreMembership {
        $best = null;
        // Full ties (coverage/load/recency) scan shuffled: uniform winner.
        foreach ($candidates->shuffle() as $member) {
            if (! ($availability[$member->id]['on_shift'] ?? false) || ! $this->withinQuota($member, $openCounts, $caps)) {
                continue;
            }

            if ($best === null || $this->outranks($member, $best, $openCounts, $lastAssignedAt, $ownership)) {
                $best = $member;
            }
        }

        return $best;
    }

    /**
     * Caps that apply at the snapshot instant, keyed by member. Members the
     * snapshot reports as uncapped (or without a covering shift) are absent,
     * and withinQuota() treats an absent cap as unlimited.
     */
    private function capsFromAvailability(Collection $candidates, array $availability): array
    {
        $caps = [];

        foreach ($candidates as $member) {
            $cap = $availability[$member->id]['cap'] ?? null;

            if ($cap !== null) {
                $caps[$member->id] = (int) $cap;
            }
        }

        return $caps;
    }

    protected function withinQuota(StoreMembership $member, array $openCounts, array $caps): bool
    {
        $cap = $caps[$member->id] ?? null;

        return $cap === null || (($openCounts[$member->id] ?? 0) < $cap);
    }

    /**
     * Ranking within a pool: most ordered products owned (coverage — counts
     * compare exactly because the denominator is the order), then fewest open
     * assignments, then oldest last assignment.
     */
    protected function outranks(
        StoreMembership $member,
        StoreMembership $current,
        array $openCounts,
        array $lastAssignedAt,
        array $ownership = [],
    ): bool {
        $memberOwned = $ownership[$member->id] ?? 0;
        $currentOwned = $ownership[$current->id] ?? 0;

        if ($memberOwned !== $currentOwned) {
            return $memberOwned > $currentOwned;
        }

        $memberOpen = $openCounts[$member->id] ?? 0;
        $currentOpen = $openCounts[$current->id] ?? 0;

        if ($memberOpen !== $currentOpen) {
            return $memberOpen < $currentOpen;
        }

        return ($lastAssignedAt[$member->id] ?? '1970-01-01') < ($lastAssignedAt[$current->id] ?? '1970-01-01');
    }
}
