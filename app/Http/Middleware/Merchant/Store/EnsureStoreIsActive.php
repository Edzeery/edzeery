<?php

namespace App\Http\Middleware\Merchant\Store;

use App\Enums\Store\StoreStatusEnum;
use Closure;
use Illuminate\Http\Request;

class EnsureStoreIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $store = currentStore();

        if (! $store) {
            abort(404);
        }

        $latestStatusHistory = $store->latestStatus();

        if (
            $latestStatusHistory
            && in_array($latestStatusHistory->status, [
                StoreStatusEnum::PENDING,
                StoreStatusEnum::CLOSED,
                StoreStatusEnum::SUSPENDED,
            ], true)
        ) {
            if (! $request->routeIs('account.billing')) {
                return redirect()->route('account.billing')

                    ->with('warning',  __('subscription.store_not_active')
                        ?: __('Your store is not active. Please check your subscription.'));
            }
        }

        $ownerSubscription = currentStore()->user?->latestSubscription();

        if ($ownerSubscription && ! $ownerSubscription->isActive() && ! $ownerSubscription->onTrial()) {
            if (! $request->routeIs('account.billing')) {
                return redirect()->route('account.billing')
                    ->with('warning',
                     __('subscription.subscription_expired')
                        ?: __('Your subscription has expired.'));
            }
        }

        return $next($request);
    }
}
