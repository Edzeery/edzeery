<?php

namespace App\Http\Middleware\Merchant\Store;

use App\Models\Stores\Store;
use App\Support\StoreContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStoreFromRoute
{
    /**
     * Bind the {store} route parameter into the store context.
     *
     * The current store is persisted in the session so that Livewire
     * sub-requests (which hit /livewire/update without a {store} param)
     * can still resolve the active store via StoreResolver.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $param = $request->route('store');

        // Volt full-page routes receive raw parameters (no implicit model
        // binding), so {store:slug} may arrive as a plain slug string.
        $store = $param instanceof Store
            ? $param
            : Store::where('slug', (string) $param)->first();

        if ($store) {
            session(['current_store_id' => $store->id]);
            app(StoreContext::class)->set($store);
        }

        abort_unless(currentStore(), 404, __('stores.store_not_found'));

        return $next($request);
    }
}
