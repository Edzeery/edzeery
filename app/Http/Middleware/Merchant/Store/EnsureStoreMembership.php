<?php

namespace App\Http\Middleware\Merchant\Store;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreMembership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user =  user();
        $store = currentStore();
        $membership = $user->storeMemberships()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->first();

        abort_unless($membership, 403, __('stores.membership_Forbidden_403'));

        app()->instance('currentMembership', $membership);

        return $next($request);
    }
}
