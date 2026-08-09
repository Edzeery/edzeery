<?php

namespace App\Http\Middleware\Api;

use App\Enums\Platform\UserRoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreContext
{
    /**
     * Guarantee every API request runs inside a tenant context.
     *
     * Platform admins (super admin / admin) are exempt — the StoreScope
     * already skips them. Everyone else must identify their store via
     * the `X-Store-Id` header (or `store_id` query param); without it we
     * refuse to run un-scoped queries to prevent cross-tenant leakage.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (
            $user->hasRole(UserRoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(UserRoleEnum::ADMIN->value)
        )) {
            return $next($request);
        }

        if (currentStore() === null) {
            return response()->json([
                'message' => 'A store context is required. Provide the X-Store-Id header.',
                'errors' => ['store' => ['The X-Store-Id header is required.']],
            ], 422);
        }

        return $next($request);
    }
}
