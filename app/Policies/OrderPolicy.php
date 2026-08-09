<?php

namespace App\Policies;

use App\Models\Orders\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{




    public function confirm(User $user, Order $order): bool
    {
        return $user->can('order.confirm');
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->can('order.cancel');
    }

    public function assignDelivery(User $user, Order $order): bool
    {
        return $user->can('order.assign_delivery');
    }

    public function complete(User $user, Order $order): bool
    {
        return $user->can('order.complete');
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
   public function delete(User $user): bool
    {

        return $user->can('order.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}
