<?php

namespace App\Domains\Order\Concerns;

use App\Domains\Order\Models\ConfirmationShift;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Capacity-balanced candidate resolution shared by the confirmation and tracking
 * auto-assignment pipelines: on-shift filtering, shift-role-scoped quota and load
 * balancing (fewest open assignments, then oldest last assignment). The consumer
 * supplies the permission-filtered candidate pool plus source-specific open-count
 * and last-assigned maps (orders vs order_trackings).
 */
trait ResolvesCapacityBalancedCandidates
{
    /**
     * Best on-shift candidate within quota for the role scope, or null when
     * nobody can take the assignment.
     */
    protected function bestCandidateOnShift(
        Collection $candidates,
        string $storeId,
        string $roleScope,
        array $openCounts,
        array $lastAssignedAt,
    ): ?StoreMembership {
        $caps = $this->quotaCapsByMember($storeId, $roleScope, $candidates);
        $best = null;

        foreach ($candidates as $member) {
            if (! $this->isOnShift($member, $roleScope) || ! $this->withinQuota($member, $openCounts, $caps)) {
                continue;
            }

            if ($best === null || $this->outranks($member, $best, $openCounts, $lastAssignedAt)) {
                $best = $member;
            }
        }

        return $best;
    }

    /**
     * Effective cap per member: highest max_concurrent_orders across their
     * active shifts of the given role scope (a member owns the total, the cap
     * is shift-scoped). Members with no capped shift are uncapped.
     */
    protected function quotaCapsByMember(string $storeId, string $roleScope, Collection $candidates): array
    {
        $memberIds = $candidates->pluck('id')->all();

        if (empty($memberIds)) {
            return [];
        }

        return ConfirmationShift::query()
            ->where('store_id', $storeId)
            ->whereIn('membership_id', $memberIds)
            ->where('is_active', true)
            ->whereNotNull('max_concurrent_orders')
            ->when($roleScope === 'track', fn ($q) => $q->track(), fn ($q) => $q->confirm())
            ->selectRaw('membership_id, MAX(max_concurrent_orders) as cap')
            ->groupBy('membership_id')
            ->pluck('cap', 'membership_id')
            ->toArray();
    }

    protected function isOnShift(StoreMembership $member, string $roleScope): bool
    {
        $onShift = $member->isOnActiveShift(roleScope: $roleScope);

        if (! $onShift) {
            Log::debug('Member not on active shift', [
                'membership_id' => $member->id,
                'user_id' => $member->user_id,
            ]);
        }

        return $onShift;
    }

    protected function withinQuota(StoreMembership $member, array $openCounts, array $caps): bool
    {
        $cap = $caps[$member->id] ?? null;

        return $cap === null || (($openCounts[$member->id] ?? 0) < $cap);
    }

    protected function outranks(
        StoreMembership $member,
        StoreMembership $current,
        array $openCounts,
        array $lastAssignedAt,
    ): bool {
        $memberOpen = $openCounts[$member->id] ?? 0;
        $currentOpen = $openCounts[$current->id] ?? 0;

        if ($memberOpen !== $currentOpen) {
            return $memberOpen < $currentOpen;
        }

        return ($lastAssignedAt[$member->id] ?? '1970-01-01') < ($lastAssignedAt[$current->id] ?? '1970-01-01');
    }
}