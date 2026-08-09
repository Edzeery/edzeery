<?php

namespace App\Http\Middleware;

use App\Support\StoreContext;
use Closure;

class ResolveStoreFromSubdomain
{
    public function handle($request, Closure $next)
    {
        $store = $request->route('store');

        app(StoreContext::class)->set($store);

        return $next($request);
    }
}
