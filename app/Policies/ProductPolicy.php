<?php

namespace App\Policies;

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $store = currentStore();

        return $store
            && $store->membershipFor($user)
            && $store->membershipFor($user)->can(StorePermissionEnum::PRODUCT_VIEW);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product  $Product): bool
    {
        $store = currentStore();

        return $store
            && $store->membershipFor($user)
            && $store->membershipFor($user)->can(StorePermissionEnum::PRODUCT_VIEW);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $store = currentStore();

        return $store
            && $store->membershipFor($user)
            && $store->membershipFor($user)->can(StorePermissionEnum::PRODUCT_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return currentStore()
            ?->membershipFor($user)
            ?->can(StorePermissionEnum::PRODUCT_UPDATE)
            ?? false;
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        return  $user->can('product.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return  $user->can('product.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return  $user->can('product.forceDelete');
    }
}
