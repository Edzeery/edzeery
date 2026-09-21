<?php

namespace App\Services\Stores\Concerns;

use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;

/**
 * Supervisor-assignment resolution for the team service (Phase 36.2).
 *
 * On create/update, an owner/admin may pin an explicit manager as a staff
 * member's supervisor; on create, a manager auto-routes their own new staff
 * to themselves. Extracted from StoreTeamService so the service stays inside
 * the 250-line class cap — pure move, no logic change.
 */
trait ResolvesSupervisorAssignment
{
    protected function actorMembership(Store $store): ?StoreMembership
    {
        return StoreMembership::where('store_id', $store->id)
            ->where('user_id', user()->id)
            ->where('is_active', true)
            ->first();
    }

    protected function resolveSupervisorId(Store $store, array $data): ?string
    {
        $actor = $this->actorMembership($store);
        if (($data['store_role'] ?? null) !== StoreRoleEnum::STAFF->value) {
            return null;
        }
        $explicit = (string) ($data['supervisor_membership_id'] ?? '');
        if ($explicit === '') {
            return $actor?->isManager() ? $actor->id : null;
        }
        if (! $actor || (! $actor->isOwner() && ! $actor->isAdmin())) {
            return null;
        }
        $this->assertValidSupervisor($store, $explicit);

        return $explicit;
    }

    protected function assertValidSupervisor(Store $store, string $membershipId): void
    {
        $valid = StoreMembership::whereKey($membershipId)
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->where('role', StoreRoleEnum::MANAGER->value)
            ->exists();
        if (! $valid) {
            throw new \Exception(__('teams.invalid_supervisor'));
        }
    }

    protected function applySupervisorOnUpdate(Store $store, StoreMembership $membership, array $data): void
    {
        if (($data['store_role'] ?? null) !== StoreRoleEnum::STAFF->value) {
            $membership->update(['supervisor_membership_id' => null]);
            return;
        }
        $actor = $this->actorMembership($store);
        if (! $actor || (! $actor->isOwner() && ! $actor->isAdmin())) {
            return;
        }
        $explicit = (string) ($data['supervisor_membership_id'] ?? '');
        if ($explicit === '') {
            $membership->update(['supervisor_membership_id' => null]);
            return;
        }
        $this->assertValidSupervisor($store, $explicit);
        $membership->update(['supervisor_membership_id' => $explicit]);
    }
}