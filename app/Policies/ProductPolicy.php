<?php

namespace App\Policies;

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\User;

class ProductPolicy
{
    protected function hasPermission(User $user, string $permission): bool
    {
        $store = currentStore();
        if (! $store) {
            return false;
        }

        $membership = $store->membershipFor($user);
        if (! $membership) {
            return false;
        }

        return $membership->can(StorePermissionEnum::tryFrom($permission));
    }

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, StorePermissionEnum::PRODUCT_VIEW->value);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->hasPermission($user, StorePermissionEnum::PRODUCT_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, StorePermissionEnum::PRODUCT_CREATE->value);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->hasPermission($user, StorePermissionEnum::PRODUCT_UPDATE->value);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->hasPermission($user, StorePermissionEnum::PRODUCT_DELETE->value);
    }

    public function restore(User $user, Product $product): bool
    {
        return false;
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return false;
    }
}
