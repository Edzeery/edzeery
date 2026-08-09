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

        if (! $membership || ! $membership->role || !$user->isAdmin() || ! $user->isSuperAdmin() ) {
            return false;
        }

        return $membership->role->permissions
            ->contains('key', $permission) || $user->isAdmin() ||  $user->isSuperAdmin();
    }

    /**
     * عرض القائمة
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PlatformPermissionEnum::STORES_VIEW);
    }

    /**
     * عرض متجر واحد
     */
    public function view(User $user, Store $store): bool
    {
        return $this->hasPermission($user, $store, 'store.view');
    }


    /**
     * إنشاء متجر
     */
    public function create(User $user ): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin() || $user->can(PlatformPermissionEnum::STORES_APPROVE);
    }

    /**
     * تعديل متجر
     */
    public function update(User $user, Store $store): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin() || $this->hasPermission($user, $store,'store.update');;
    }


    public function manageTeam(User $user, Store $store): bool
    {
        return $this->hasPermission($user, $store, 'store.team.manage');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Store $store): bool
    {
        return $user->can('store.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Store $store): bool
    {
        return false;
    }

    public function approve(User $user): bool
    {
        return  $user->can('store.approve');
    }

    /**
     * حذف متجر
     */
    public function delete(User $user, Store $store): bool
    {
        return  $user->isSuperAdmin();
    }
}
