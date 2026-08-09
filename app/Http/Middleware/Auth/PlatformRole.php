<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;

class PlatformRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $roles = collect($roles)
            ->map(fn($role) => UserRoleEnum::tryFrom($role)?->value ?? $role)
            ->toArray();



        if (! $user->hasAnyRoleForGuard($roles, 'web')) {
         abort(403, __('responses.403'));
        }

        return $next($request);
    }
}
