<?php

namespace App\Models\Orders\Concerns;

use App\Enums\Store\OrderStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter orders through their status KEY, never a single status id.
 *
 * Stores may own a custom row for any status key, so an order's status_id is
 * not a stable way to ask for "delivered" / "cancelled". Matching on
 * statuses.type + statuses.key resolves both the seeded system row and every
 * store override in one query.
 */
trait HasStatusKeyScope
{
    /**
     * @param  OrderStatus|array<int, OrderStatus>  $statuses
     */
    public function scopeWhereStatusKey(Builder $query, OrderStatus|array $statuses): Builder
    {
        $statuses = is_array($statuses) ? $statuses : [$statuses];

        // An empty list means "no status filter", so the call is a no-op.
        if ($statuses === []) {
            return $query;
        }

        $keys = array_map(
            static fn (OrderStatus $status): string => $status->value,
            $statuses
        );

        return $query->whereHas(
            'status',
            static fn (Builder $status): Builder => $status
                ->where('type', 'order')
                ->whereIn('key', $keys)
        );
    }
}
