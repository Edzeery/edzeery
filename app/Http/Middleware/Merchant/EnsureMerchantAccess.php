<?php

namespace App\Http\Middleware\Merchant;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMerchantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = user();

        if (! $user) {
            abort(403, __('responses.403'));
        }

        if (
            ! $user->isMerchant()
            && ! $user->storeMemberships()->exists()
        ) {
            abort(403, __('responses.403'));
        }

        // No current store → redirect to create store (if no memberships)
        if (
            ! currentStore()
            && ! $request->routeIs('merchant.create-store')
            && ! $request->routeIs('choose-store')
            && ! $request->routeIs('logout')
        ) {
            $hasMemberships = $user->storeMemberships()->exists();

            if ($hasMemberships) {
                return redirect()->route('choose-store');
            }

            return redirect()->route('merchant.create-store');
        }

        return $next($request);
    }
}
