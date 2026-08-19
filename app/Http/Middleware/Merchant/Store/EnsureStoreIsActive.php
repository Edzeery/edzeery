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

        $latestStatusHistory = $store->latestStatus();

        // if (
        //     $latestStatusHistory
        //     && in_array($latestStatusHistory->status, [
        //         StoreStatusEnum::PENDING,
        //         StoreStatusEnum::CLOSED,
        //         StoreStatusEnum::SUSPENDED,
        //     ], true)
        // ) {
        //     if (! $request->routeIs('account.billing')) {
        //         return redirect()->route('account.billing');
        //     }
        // }

        $subscription = user()?->latestSubscription();

        // if (! $subscription || (! $subscription->isActive() && ! $subscription->onTrial())) {
        //     if (! $request->routeIs('account.billing')) {
        //         return redirect()->route('account.billing')
        //             ->with('subscription_warning', true);
        //     }
        // }

        return $next($request);
    }
}
