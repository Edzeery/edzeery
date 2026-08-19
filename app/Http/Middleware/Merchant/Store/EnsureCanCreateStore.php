<?php

namespace App\Http\Middleware\Merchant\Store;

use App\Enums\Store\StoreRoleEnum;
use Closure;
use Illuminate\Http\Request;

class EnsureCanCreateStore
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $hasOwnerRole = $user->hasAnyRoleForGuard(
            [StoreRoleEnum::OWNER->value],
            'merchant'
        );

        $hasOnlyStaffRoles = ! $hasOwnerRole && $user->hasAnyRoleForGuard(
            [StoreRoleEnum::STAFF->value, StoreRoleEnum::MANAGER->value],
            'merchant'
        );

        if ($hasOnlyStaffRoles) {
            abort(403, __('stores.membership_Forbidden_403'));
        }

        $subscription = $user->latestSubscription();
        if (! $subscription) {
            abort(403, __('stores.subscription_required'));
        }

        return $next($request);
    }
}
