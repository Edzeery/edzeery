<?php

namespace App\Domains\Order\Support;

use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Team\StoreMembership;
use App\Models\Stores\Team\StoreMembershipPermission;
use App\Services\Stores\StoreProductScopeService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Permission-scoped candidate listing for the manual reassignment modals
 * (confirm and tracking contexts). Given a store id, role scope and the
 * order's product ids it returns the active members holding the matching
 * permission — excluding anyone whose product visibility scope would hide the
 * order — annotated with their current open-assignment count for that scope,
 * the time-aware role-scoped cap (null = uncapped, taken from the shifts
 * covering the current instant), their on-shift state at the current store
 * time, and whether they hold BOTH permissions (dual-role). The cap and
 * on-shift annotation come from the same ShiftAvailabilityResolver snapshot
 * the auto-assignment engine uses, so the modal always shows exactly what the
 * engine enforces. All lookups are batched — no query per member.
 *
 * This is a read model for the reassign UI only. Manual reassignment itself
 * still bypasses eligibility checks (see the reassign() services); nothing here
 * enforces capacity, and the visibility filter only narrows what is offered.
 */
class AssignmentCandidateResolver
{
    /**
     * @param  string  $roleScope  'confirm' | 'track'
     * @param  string  $permission  Permission value the candidates must hold.
     * @param  array<int, string>  $productIds  Order product ids; empty = no visibility narrowing.
     * @return Collection<int, array<string, mixed>>
     */
    public function resolve(string $storeId, string $roleScope, string $permission, array $productIds = []): Collection
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

        // Prime the permissions relation with the batch we already read so the
        // visibility guard below can check TEAM_VIEW_OWN without re-querying.
        $this->primePermissionsRelation($members, $permissionsByMember);

        $members = app(StoreProductScopeService::class)->filterByVisibility($members, $productIds);

        if ($members->isEmpty()) {
            return collect();
        }

        $otherPermission = $roleScope === 'track'
            ? StorePermissionEnum::ORDER_CONFIRM->value
            : StorePermissionEnum::CRM_ORDER_TRACKING->value;

        $memberIds = $members->pluck('id');
        $openCounts = $this->openCountsByMember($storeId, $roleScope, $memberIds);
        $currentTime = $this->currentTime($members);
        $availability = app(ShiftAvailabilityResolver::class)->resolve($storeId, $roleScope, $members, $currentTime);

        return $members
            ->map(function (StoreMembership $m) use ($openCounts, $availability, $otherPermission, $permissionsByMember) {
                $id = $m->id;

                return [
                    'id' => (string) $id,
                    'user_id' => $m->user_id,
                    'name' => $m->user?->name ?: '—',
                    'open' => (int) ($openCounts[$id] ?? 0),
                    'cap' => $availability[$id]['cap'] ?? null,
                    'on_shift' => $availability[$id]['on_shift'] ?? false,
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
     * Hand the already-batched permission rows to the memberships as a loaded
     * relation, so later StoreMembership::can() checks (the visibility guard)
     * read from memory instead of issuing one pivot query per member.
     */
    private function primePermissionsRelation(Collection $members, array $permissionsByMember): void
    {
        $members->each(function (StoreMembership $member) use ($permissionsByMember) {
            $member->setRelation('permissions', collect($permissionsByMember[$member->id] ?? [])
                ->map(fn (string $permission) => new StoreMembershipPermission(['permission' => $permission])));
        });
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
            ->where(fn ($q) => $q
                ->where('statuses.distribution_stage', OrderDistributionStage::CONFIRMATION)
                ->orWhereNull('statuses.distribution_stage'))
            ->selectRaw('orders.assigned_to_membership_id, COUNT(*) as open_count')
            ->groupBy('orders.assigned_to_membership_id')
            ->pluck('open_count', 'orders.assigned_to_membership_id')
            ->toArray();
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
}
