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
        $user = user();
        $store = currentStore();

        if (! $store) {
            session()->forget('current_store_id');
            abort(403, __('stores.membership_Forbidden_403'));
        }

        $membership = $user->storeMemberships()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->first();

        // S-03: a refused store must never leave a poisoned current_store_id
        // in the session, or the next Livewire sub-request would resolve a
        // store the user does not belong to.
        if (! $membership) {
            session()->forget('current_store_id');
            abort(403, __('stores.membership_Forbidden_403'));
        }

        // Legit path only: persist the store for Livewire sub-requests.
        session(['current_store_id' => $store->id]);

        app()->instance('currentMembership', $membership);

        return $next($request);
    }
}
