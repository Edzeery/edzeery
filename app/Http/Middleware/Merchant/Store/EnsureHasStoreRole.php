<?php

namespace App\Http\Middleware\Merchant\Store;

use App\Enums\Store\StoreRoleEnum;
use Closure;
use Illuminate\Http\Request;

class EnsureHasStoreRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $store = currentStore();

        if (! $store) {
            abort(404, __('stores.store_not_found'));
        }

        // 🔹 دمج كل العناصر في string واحد، ثم تقسيم على , أو |
        $roles = implode(',', $roles);
        $roles = preg_split('/[,\|]/', $roles, -1, PREG_SPLIT_NO_EMPTY);

        // 🔹 تحويل الأدوار إلى قيم Enum إذا موجودة
        $roles = collect($roles)
            ->map(fn($role) => StoreRoleEnum::tryFrom($role)?->value ?? $role)
            ->toArray();

        $membership = currentMembership();


        if (! $membership || ! $membership->is_active) {
            abort(403, __('stores.membership_Forbidden_403'));
        }

        if (! $membership->user->hasAnyRoleForGuard($roles, 'merchant')) {
            abort(403, __('stores.membership_Forbidden_403'));
        }

        return $next($request);
    }
}
