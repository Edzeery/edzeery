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

        /**
         * شرط الدخول للـ Merchant Panel:
         * - عنده أي Role تاجر (Spatie)
         * - أو عنده عضوية متجر
         */


        if (
            ! $user->isMerchant()
            && ! $user->storeMemberships()->exists()
        ) {
            abort(403, __('responses.403'));
        }

        // 🧭 If there is no current store → Directions to registration
        if (
            ! currentStore()
            && ! $request->routeIs('filament.merchant.tenant.registration')
            && ! $request->routeIs('logout')
            && ! $request->routeIs('filament.user.auth.logout')
            && ! $request->routeIs('filament.merchant.auth.logout')
        ) {
            return redirect()->route('filament.merchant.tenant.registration');
        }

        return $next($request);
    }
}
