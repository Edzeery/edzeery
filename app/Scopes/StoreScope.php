<?php

namespace App\Scopes;

use App\Enums\Platform\UserRoleEnum;
use App\Support\StoreContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Tenant scope for models with a non-nullable `store_id` (T-02).
 *
 * FAIL-CLOSED:
 *  - Inside an explicit store context, every row is filtered to that store —
 *    platform admins included (a store context is a deliberate, scoped view).
 *  - With no store context, platform admins (super admin / admin) read across
 *    stores; everyone else gets no rows at all.
 *
 * Explicit escape hatches: `Model::withoutStoreScope()` and
 * `StoreContext::runAs($store, fn)`.
 */
class StoreScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(StoreContext::class);

        if ($context->has()) {
            $builder->where($model->getTable().'.store_id', $context->id());

            return;
        }

        if (self::isPlatformAdmin()) {
            return;
        }

        // No context, not an admin: read nothing rather than everything.
        $builder->whereRaw('1 = 0');
    }

    /**
     * Whether the authenticated user is a platform admin. Platform admins only
     * bypass the scope when there is NO store context (see the class docblock).
     */
    public static function isPlatformAdmin(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasAnyRoleForGuard([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::ADMIN->value,
        ], 'web');
    }
}
