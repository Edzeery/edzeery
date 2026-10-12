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
 * PHASE 37-M (D1): the Confirmation tab's member rows are keyed on
 * orders.assigned_to_membership_id, and an assignment counts as a confirmation
 * row only when the assigned member holds ORDER_CONFIRM — so the Confirmation
 * cohort is exactly the ORDER_CONFIRM holders. Delivery credits
 * orders.confirmed_by_membership_id (the confirmer, also an ORDER_CONFIRM
 * holder) and walks the newest tracking member for its workload, so its cohort
 * is the union of the two workflows. OWNER, ADMIN and MANAGER run the whole
 * store and therefore belong to both cohorts, whichever permissions they hold.
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
