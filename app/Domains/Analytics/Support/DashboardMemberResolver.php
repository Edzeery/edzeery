<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Team\StoreMembership;

/**
 * Resolves who the dashboard is looking at, and what they are allowed to see
 * (PHASE 37-L split out of {@see DashboardFilterFactory} to stay under the
 * class size cap). Fail-closed: no active membership means no visibility.
 */
final class DashboardMemberResolver
{
    /**
     * Returns [memberId, dimension, memberScopeIds, memberLocked,
     * memberUnattributedOnly], where memberScopeIds is null for "every member"
     * and an explicit list otherwise. The sentinel UNATTRIBUTED selects the
     * unattributed cohort (confirmed_by IS NULL).
     *
     * @return array{0: ?string, 1: string, 2: ?array<int, string>, 3: bool, 4: bool}
     */
    public function resolve(?StoreMembership $current, ?string $memberId, ?string $memberDimension): array
    {
        if (! $current || ! $current->is_active) {
            // Fail closed: without a membership nothing is visible.
            return [null, 'confirmation', [], true, false];
        }

        // OWNER/ADMIN are roles, not permissions: StoreRoles grants both of them
        // TEAM_VIEW, so team visibility is fully described by these two checks.
        $hasTeamView = $current->hasPermission(StorePermissionEnum::STATS_TEAM_VIEW->value)
            || $current->hasPermission(StorePermissionEnum::TEAM_VIEW->value);

        $hasTeamViewOwn = ! $hasTeamView
            && $current->hasPermission(StorePermissionEnum::TEAM_VIEW_OWN->value);

        $canConfirm = $current->hasPermission(StorePermissionEnum::STATS_CONFIRMATION->value)
            || $current->hasPermission(StorePermissionEnum::ORDER_CONFIRM->value);

        $canDeliver = $current->hasPermission(StorePermissionEnum::STATS_DELIVERY->value);

        $dimension = $this->resolveDimension($memberDimension, $canConfirm, $canDeliver);

        // The unattributed cohort (confirmed_by IS NULL) is a store-wide pick,
        // exposed when the member can see the whole team's numbers. The sentinel
        // is not a membership id, so it can never reach keepIfAllowed.
        if ($hasTeamView && $memberId === DashboardFilter::UNATTRIBUTED) {
            return [DashboardFilter::UNATTRIBUTED, $dimension, [DashboardFilter::UNATTRIBUTED], false, true];
        }

        if ($hasTeamView) {
            $allowed = StoreMembership::query()
                ->where('store_id', $current->store_id)
                ->where('is_active', true)
                ->pluck('id')
                ->all();

            $selected = $this->keepIfAllowed($memberId, $allowed);

            // No pick means no restriction: this is the whole point of a free choice.
            return [$selected, $dimension, $selected ? [$selected] : null, false, false];
        }

        if ($hasTeamViewOwn) {
            $allowed = $current->subordinates()
                ->where('is_active', true)
                ->pluck('id')
                ->push($current->id)
                ->unique()
                ->all();

            $selected = $this->keepIfAllowed($memberId, $allowed);

            // No pick means the whole team: self plus subordinates.
            return [$selected, $dimension, $selected ? [$selected] : $allowed, false, false];
        }

        return [$current->id, $dimension, [$current->id], true, false];
    }

    /**
     * @param  array<int, string>  $allowed
     */
    private function keepIfAllowed(?string $memberId, array $allowed): ?string
    {
        return $memberId !== null && in_array($memberId, $allowed, true) ? $memberId : null;
    }

    /**
     * A dimension is only honoured when the member actually holds that
     * capability; otherwise they fall back to confirmation when they can
     * confirm, and to delivery when they cannot.
     */
    private function resolveDimension(?string $requested, bool $canConfirm, bool $canDeliver): string
    {
        $requested = strtolower($requested ?? '');

        if ($requested === 'confirmation' && $canConfirm) {
            return 'confirmation';
        }

        if ($requested === 'delivery' && $canDeliver) {
            return 'delivery';
        }

        return $canConfirm ? 'confirmation' : 'delivery';
    }
}
