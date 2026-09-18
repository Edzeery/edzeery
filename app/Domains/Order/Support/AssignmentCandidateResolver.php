<?php

namespace App\Domains\Order\Support;

use App\Domains\Order\Models\ConfirmationShift;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Team\StoreMembership;
use App\Models\Stores\Team\StoreMembershipPermission;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Permission-scoped candidate listing for the manual reassignment modals
 * (confirm and tracking contexts). Given a store id and role scope it returns
 * the active members holding the matching permission, annotated with their
 * current open-assignment count for that scope, the effective role-scoped cap
 * (null = uncapped, taken from their active ConfirmationShifts), their on-shift
 * state at the current store time, and whether they hold BOTH permissions
 * (dual-role). All lookups are batched — no query per member.
 *
 * This is a read model for the reassign UI only. Manual reassignment itself
 * still bypasses eligibility checks (see the reassign() services); nothing here
 * enforces capacity.
 */
class AssignmentCandidateResolver
{
    /**
     * @param string $roleScope 'confirm' | 'track'
     * @param string $permission Permission value the candidates must hold.
     * @return Collection<int, array<string, mixed>>
     */
    public function resolve(string $storeId, string $roleScope, string $permission): Collection
    {
        $permissionsByMember = $this->permissionsByMember($storeId);

        $members = StoreMembership::where('store_id', $storeId)
            ->where('is_active', true)
            ->with('user', 'storeWithTimezone')
            ->get()
            ->filter(fn (StoreMembership $m) => $this->holdsPermission($m, $permission, $permissionsByMember))
            ->values();

        if ($members->isEmpty()) {
            return collect();
        }

        $otherPermission = $roleScope === 'track'
            ? StorePermissionEnum::ORDER_CONFIRM->value
            : StorePermissionEnum::CRM_ORDER_TRACKING->value;

        $memberIds = $members->pluck('id');
        $openCounts = $this->openCountsByMember($storeId, $roleScope, $memberIds);
        $shiftInfo = $this->shiftInfoByMember($storeId, $roleScope, $memberIds);
        $currentTime = $this->currentTime($members);

        return $members
            ->map(function (StoreMembership $m) use ($openCounts, $shiftInfo, $otherPermission, $permissionsByMember, $currentTime) {
                $id = $m->id;

                return [
                    'id' => (string) $id,
                    'user_id' => $m->user_id,
                    'name' => $m->user?->name ?: '—',
                    'open' => (int) ($openCounts[$id] ?? 0),
                    'cap' => $shiftInfo['caps'][$id] ?? null,
                    'on_shift' => $this->onShift($shiftInfo['shifts'][$id] ?? [], $currentTime),
                    'dual_role' => $this->holdsPermission($m, $otherPermission, $permissionsByMember),
                ];
            })
            ->sortBy('name')
            ->values();
    }

    /**
     * Stored per-membership permissions of the store's active members, keyed by
     * membership id — one query, mirrors StoreMembership::can() precedence.
     */
    private function permissionsByMember(string $storeId): array
    {
        $activeIds = StoreMembership::where('store_id', $storeId)
            ->where('is_active', true)
            ->pluck('id');

        return StoreMembershipPermission::whereIn('membership_id', $activeIds)
            ->get(['membership_id', 'permission'])
            ->groupBy('membership_id')
            ->map(fn ($rows) => $rows->pluck('permission')->all())
            ->all();
    }

    /**
     * Same semantics as StoreMembership::can(): custom stored permissions are
     * authoritative; otherwise fall back to the user's global merchant role.
     */
    private function holdsPermission(StoreMembership $member, string $permission, array $permissionsByMember): bool
    {
        $stored = $permissionsByMember[$member->id] ?? [];

        if (! empty($stored)) {
            return in_array($permission, $stored, true);
        }

        return (bool) $member->user?->hasPermissionTo($permission, 'merchant');
    }

    /**
     * Open assignment counts for the role scope across the members (orders
     * exclude terminal statuses; order_trackings count open statuses only).
     */
    private function openCountsByMember(string $storeId, string $roleScope, Collection $memberIds): array
    {
        if ($roleScope === 'track') {
            $openStatuses = collect(OrderTrackingStatus::open())
                ->map(fn ($status) => $status->value)
                ->all();

            return DB::table('order_trackings')
                ->where('store_id', $storeId)
                ->whereIn('assigned_to_membership_id', $memberIds)
                ->whereIn('tracking_status', $openStatuses)
                ->selectRaw('assigned_to_membership_id, COUNT(*) as open_count')
                ->groupBy('assigned_to_membership_id')
                ->pluck('open_count', 'assigned_to_membership_id')
                ->toArray();
        }

        return DB::table('orders')
            ->join('statuses', 'orders.status_id', '=', 'statuses.id')
            ->where('orders.store_id', $storeId)
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.assigned_to_membership_id', $memberIds)
            ->whereNotIn('statuses.key', $this->terminalStatusKeys())
            ->selectRaw('orders.assigned_to_membership_id, COUNT(*) as open_count')
            ->groupBy('orders.assigned_to_membership_id')
            ->pluck('open_count', 'assigned_to_membership_id')
            ->toArray();
    }

    /**
     * Single batched shift read yields both the per-member cap (highest
     * max_concurrent_orders across their active role-scoped shifts, mirroring
     * ResolvesCapacityBalancedCandidates::quotaCapsByMember) and the raw shift
     * rows used to compute on-shift state.
     */
    private function shiftInfoByMember(string $storeId, string $roleScope, Collection $memberIds): array
    {
        $rows = ConfirmationShift::query()
            ->where('store_id', $storeId)
            ->whereIn('membership_id', $memberIds)
            ->where('is_active', true)
            ->when($roleScope === 'track', fn ($q) => $q->track(), fn ($q) => $q->confirm())
            ->get(['membership_id', 'days_of_week', 'start_time', 'end_time', 'is_active', 'max_concurrent_orders']);

        $caps = [];
        $shifts = [];

        foreach ($rows as $shift) {
            $memberId = $shift->membership_id;
            $shifts[$memberId][] = $shift;

            if ($shift->max_concurrent_orders !== null) {
                $caps[$memberId] = max($caps[$memberId] ?? 0, (int) $shift->max_concurrent_orders);
            }
        }

        return ['caps' => $caps, 'shifts' => $shifts];
    }

    /**
     * Whether any of the member's role-scoped shifts covers "now" in the
     * store's timezone (same rule as StoreMembership::isOnActiveShift).
     */
    private function onShift(array $shifts, Carbon $at): bool
    {
        $dayOfWeek = $at->dayOfWeekIso;
        $time = $at->format('H:i');

        foreach ($shifts as $shift) {
            if ($shift->coversDayTime($dayOfWeek, $time)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Store timezone "now" resolved without an extra query: the memberships
     * eager-load storeWithTimezone (> settings), so the first member's store
     * timezone is already in memory.
     */
    private function currentTime(Collection $members): Carbon
    {
        $timezone = $members->first()->storeWithTimezone?->settings?->timezone
            ?? config('app.timezone');

        return now($timezone);
    }

    private function terminalStatusKeys(): array
    {
        return ['cancelled', 'delivered', 'returned', 'completed', 'refunded', 'canceled'];
    }
}