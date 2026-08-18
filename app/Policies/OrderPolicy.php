<?php

namespace App\Policies;

use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\User;

class OrderPolicy
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
        return $this->hasPermission($user, StorePermissionEnum::ORDER_VIEW->value);
    }

    public function view(User $user, Order $order): bool
    {
        return $this->hasPermission($user, StorePermissionEnum::ORDER_VIEW->value);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Order $order): bool
    {
        return $this->hasPermission($user, StorePermissionEnum::ORDER_MANAGE->value);
    }

    public function delete(User $user, Order $order): bool
    {
        return $this->hasPermission($user, StorePermissionEnum::ORDER_DELETE->value);
    }

    public function confirm(User $user, Order $order): bool
    {
        return $this->hasPermission($user, StorePermissionEnum::ORDER_CONFIRM->value);
    }

    public function cancel(User $user, Order $order): bool
    {
        return $this->hasPermission($user, StorePermissionEnum::ORDER_CANCEL->value);
    }

    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}
