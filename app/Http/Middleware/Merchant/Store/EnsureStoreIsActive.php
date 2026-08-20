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
            if (! $request->routeIs('merchant.billing.index')) {
                return redirect()->route('merchant.billing.index')
                    ->with('warning', __('Your store is not active. Please check your subscription.'));
            }
        }

        $subscription = user()?->latestSubscription();

        if ($subscription && ! $subscription->isActive() && ! $subscription->onTrial()) {
            if (! $request->routeIs('merchant.billing.index')) {
                return redirect()->route('merchant.billing.index')
                    ->with('warning', __('Your subscription has expired.'));
            }
        }

        return $next($request);
    }
}
