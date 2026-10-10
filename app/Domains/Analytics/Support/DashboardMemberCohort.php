<?php

namespace App\Domains\Analytics\Support;

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Database\Eloquent\Builder;

/**
 * The membership cohort a dashboard stats tab is allowed to name
 * (§ 11.4 of docs/plans/order-distribution-rules.md: each tab counts only its
 * own cohort).
 *
 * Credit is always keyed on orders.confirmed_by_membership_id — the member who
 * confirmed, an ORDER_CONFIRM holder — so the Confirmation tab may only ever
 * show members who can confirm. Delivery credits the same confirmer but walks
 * the newest tracking member for its workload, so its cohort is the union of
 * the two workflows. OWNER, ADMIN and MANAGER run the whole store and therefore
 * belong to both cohorts, whichever permissions they hold.
 *
 * The filter is applied as one EXISTS over the query, so it adds no query of its
 * own and cannot turn the dashboard into an N+1.
 */
final class DashboardMemberCohort
{
    /** Roles that run the whole store and appear in every cohort. */
    private const MANAGEMENT_ROLES = [
        StoreRoleEnum::OWNER->value,
        StoreRoleEnum::ADMIN->value,
        StoreRoleEnum::MANAGER->value,
    ];

    /**
     * Restrict a memberships query to the cohort the given dimension may name.
     * A null dimension adds no clause at all (a non-dashboard caller).
     *
     * @param  Builder<StoreMembership>  $query
     */
    public function constrain(Builder $query, ?string $dimension): void
    {
        if ($dimension === null) {
            return;
        }

        $permissions = [StorePermissionEnum::ORDER_CONFIRM->value];

        if ($dimension === 'delivery') {
            $permissions[] = StorePermissionEnum::CRM_ORDER_TRACKING->value;
        }

        $query->where(function (Builder $cohort) use ($permissions) {
            $cohort->whereIn('role', self::MANAGEMENT_ROLES)
                ->orWhereHas(
                    'permissions',
                    fn (Builder $permissionsQuery) => $permissionsQuery->whereIn('permission', $permissions),
                );
        });
    }
}
