<?php

namespace App\Policies;

use App\Enums\Platform\PlatformPermissionEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;

class StorePolicy
{
    protected function hasPermission(User $user, Store $store, string $permission): bool
    {
        $membership = StoreMembership::query()
            ->where('store_id', $store->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $membership) {
            return false;
        }

        $perm = StorePermissionEnum::tryFrom($permission);

        if (! $perm) {
            return false;
        }

        return $user->hasPermissionTo($perm->value, 'merchant');
    }

    public function viewAny(User $user): bool
    {
        return $user->can(PlatformPermissionEnum::STORES_VIEW);
    }

    public function view(User $user, Store $store): bool
    {
        return $this->hasPermission($user, $store, StorePermissionEnum::STORE_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Store $store): bool
    {
        return $this->hasPermission($user, $store, StorePermissionEnum::STORE_UPDATE->value);
    }

    public function delete(User $user, Store $store): bool
    {
        return $user->isSuperAdmin();
    }

    public function manageTeam(User $user, Store $store): bool
    {
        return $this->hasPermission($user, $store, StorePermissionEnum::STORE_TEAM_MANAGE->value);
    }

    public function restore(User $user, Store $store): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Store $store): bool
    {
        return false;
    }

    public function approve(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function transferOwnership(User $user, Store $store): bool
    {
        return $this->hasPermission($user, $store, StorePermissionEnum::STORE_TRANSFER_OWNERSHIP->value);
    }

    public function manageBilling(User $user, Store $store): bool
    {
        return $this->hasPermission($user, $store, StorePermissionEnum::STORE_BILLING_MANAGE->value);
    }
}
