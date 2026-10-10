<?php

namespace App\Models\Concerns;

use App\Scopes\StoreOrGlobalScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Marks a model whose `store_id` is NULLABLE and where a NULL row is a shared /
 * global template (T-02). Registers the fail-closed {@see StoreOrGlobalScope}
 * (current store's rows plus the global NULL rows) and exposes the same
 * `withoutStoreScope()` escape hatch as {@see BelongsToStore}.
 *
 * No automatic `store_id` fill/throw: NULL is a legitimate value here, so the
 * caller decides explicitly whether a row is global or store-owned.
 */
trait UsesStoreOrGlobalScope
{
    public static function bootUsesStoreOrGlobalScope(): void
    {
        static::addGlobalScope(new StoreOrGlobalScope);
    }

    /**
     * Explicit escape hatch: ignore the tenant/global scope. Works both as
     * `Model::withoutStoreScope()` and inside relation closures.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithoutStoreScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(StoreOrGlobalScope::class);
    }
}
