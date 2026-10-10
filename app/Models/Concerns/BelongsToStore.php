<?php

namespace App\Models\Concerns;

use App\Exceptions\MissingStoreContextException;
use App\Scopes\StoreScope;
use App\Support\StoreContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Marks a model as tenant-scoped (T-02).
 *
 * Registers the fail-closed {@see StoreScope} and fills `store_id` from the
 * active store context on create. A create with neither an explicit `store_id`
 * nor a store context throws {@see MissingStoreContextException} instead of
 * silently writing an unscoped or cross-tenant row.
 *
 * For models with a NULLABLE `store_id` where NULL means "shared/global", use
 * {@see UsesStoreOrGlobalScope} instead.
 *
 * Escape hatches (explicit and greppable):
 *   Model::withoutStoreScope()->...   // read across every store
 *   StoreContext::runAs($store, fn)   // run writes/reads inside a store
 */
trait BelongsToStore
{
    public static function bootBelongsToStore(): void
    {
        static::addGlobalScope(new StoreScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('store_id') !== null) {
                return;
            }

            $context = app(StoreContext::class);

            if ($context->has()) {
                $model->setAttribute('store_id', $context->id());

                return;
            }

            throw MissingStoreContextException::forModel(static::class);
        });
    }

    /**
     * Explicit escape hatch: ignore the tenant scope. Works both as
     * `Model::withoutStoreScope()` and inside relation closures.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithoutStoreScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(StoreScope::class);
    }
}
