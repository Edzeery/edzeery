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

        // If the route model binding resolved it, we're good.
        // If it's a string slug, look it up.
        if (is_string($store)) {
            $store = Store::where('slug', $store)->where('status', 'active')->first();
        }

        if (! $store instanceof Store || ! $store->exists) {
            abort(404, __('Store not found'));
        }

        // Set in StoreContext (Livewire sub-requests use this)
        app(StoreContext::class)->set($store);

        // Set in session so currentStore() via StoreResolver works
        session(['current_store_id' => $store->id]);

        return $next($request);
    }
}
