<?php

namespace App\Scopes;

use App\Support\StoreContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Tenant scope for models whose `store_id` is NULLABLE and where NULL rows are
 * shared/global templates (e.g. `statuses`) (T-02).
 *
 * FAIL-CLOSED:
 *  - Inside a store context: rows of the current store plus the global (NULL)
 *    rows.
 *  - With no context: platform admins read everything; everyone else sees only
 *    the global (NULL) rows — never another store's.
 */
class StoreOrGlobalScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(StoreContext::class);
        $table = $model->getTable();

        if ($context->has()) {
            $builder->where(function (Builder $query) use ($table, $context): void {
                $query->where($table.'.store_id', $context->id())
                    ->orWhereNull($table.'.store_id');
            });

            return;
        }

        if (StoreScope::isPlatformAdmin()) {
            return;
        }

        $builder->whereNull($table.'.store_id');
    }
}
