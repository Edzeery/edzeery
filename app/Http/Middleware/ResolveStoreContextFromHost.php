<?php

namespace App\Http\Middleware;

use App\Models\Stores\Store;
use App\Support\StoreContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Storefront tenant context for Livewire update requests.
 *
 * Storefront pages are scoped by ResolveStoreFromSubdomain from the route
 * param `{store}` of the `{store}.{domain}` group. Livewire component updates
 * POST to `/livewire/update`, which lives outside that group, so a storefront
 * component rehydrating on a wire update would run every store-scoped query
 * under an empty StoreContext — and StoreScope fails closed, returning nothing.
 * Lazy relations resolve to null and price/limit rules crash (variant->product
 * arriving as null in OrderRules::limits).
 *
 * This middleware only fills the context when it is still empty and the host
 * carries an active storefront store. It never overrides an existing context.
 */
class ResolveStoreContextFromHost
{
    private const RESERVED = ['www', 'app', 'admin', 'api', 'mail'];

    public function handle(Request $request, Closure $next): Response
    {
        if (app(StoreContext::class)->has()) {
            return $next($request);
        }

        $subdomain = $this->storefrontSubdomain($request->getHost());

        if ($subdomain !== null) {
            $store = Store::where('slug', $subdomain)
                ->where('status', 'active')
                ->first();

            if ($store) {
                app(StoreContext::class)->set($store);
            }
        }

        return $next($request);
    }

    private function storefrontSubdomain(string $host): ?string
    {
        $domain = config('app.domain');

        if (! is_string($domain) || $domain === '' || ! str_ends_with($host, '.'.$domain)) {
            return null;
        }

        $subdomain = substr($host, 0, -(strlen($domain) + 1));

        if ($subdomain === '' || in_array($subdomain, self::RESERVED, true)) {
            return null;
        }

        return $subdomain;
    }
}
