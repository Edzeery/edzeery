<?php

namespace App\Http\Middleware\Store;

use App\Domains\Cart\Services\CartService;
use App\Support\StoreContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Storefront guard: the checkout page is meaningless without a cart, so an
 * empty cart is bounced back to the store home instead of rendering a dead form.
 */
class EnsureCartNotEmpty
{
    public function handle(Request $request, Closure $next): Response
    {
        $store = app(StoreContext::class)->get();

        if ($store && app(CartService::class)->isEmpty($store->id)) {
            return redirect()->route('storefront.home', ['store' => $store->slug]);
        }

        return $next($request);
    }
}