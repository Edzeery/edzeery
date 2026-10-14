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
 * cohort is exactly the ORDER_CONFIRM holders. Delivery (PHASE 37-N9) credits
 * the member whose newest tracking row shipped the order, and walks that same
 * member for its workload, so its cohort is the union of the two workflows.
 * OWNER, ADMIN and MANAGER run the whole store and therefore belong to both
 * cohorts, whichever permissions they hold.
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

    /** The tracking-team section's cohort pick (PHASE 37-N7, D8). */
    public const COHORT_ALL = 'all';

    public const COHORT_WITH = 'with';

    public const COHORT_WITHOUT = 'without';

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

    /**
     * PHASE 37-N7 (D8): narrow a members query to the tracking-team section's
     * cohort pick. "With confirmation" names only the members who belong to the
     * confirmation team (a confirmer or management), "without" names the rest;
     * "all" (and any unknown value) adds no clause.
     *
     * @param  Builder<StoreMembership>  $query
     */
    public function constrainConfirmationCohort(Builder $query, ?string $cohort): void
    {
        if ($cohort === self::COHORT_WITH) {
            $query->where(fn (Builder $confirmation) => $this->confirmationTeam($confirmation));
        } elseif ($cohort === self::COHORT_WITHOUT) {
            $query->where(function (Builder $rest): void {
                $rest->whereNotIn('role', self::MANAGEMENT_ROLES)
                    ->whereDoesntHave(
                        'permissions',
                        fn (Builder $permissions) => $permissions->where('permission', StorePermissionEnum::ORDER_CONFIRM->value),
                    );
            });
        }
    }

    /** The confirmation team: management, or holding the ORDER_CONFIRM right. */
    private function confirmationTeam(Builder $query): void
    {
        $query->whereIn('role', self::MANAGEMENT_ROLES)
            ->orWhereHas(
                'permissions',
                fn (Builder $permissions) => $permissions->where('permission', StorePermissionEnum::ORDER_CONFIRM->value),
            );
    }
}
