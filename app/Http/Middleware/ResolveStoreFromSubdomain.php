<?php

namespace App\Http\Middleware;

use App\Models\Stores\Store;
use App\Support\StoreContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStoreFromSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $store = $request->route('store');

        if (is_string($store)) {
            $store = Store::where('slug', $store)->where('status', 'active')->first();
        }

        if (! $store instanceof Store || ! $store->exists) {
            abort(404, __('Store not found'));
        }

        app(StoreContext::class)->set($store);

        // S-03: persist the store only for an authenticated user who actually
        // belongs to it. Writing an arbitrary storefront's id into the session
        // would poison a merchant's current_store_id on their next request.
        if (auth()->check() && auth()->user()?->storeMemberships()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->exists()) {
            session(['current_store_id' => $store->id]);
        }

        return $next($request);
    }
}
