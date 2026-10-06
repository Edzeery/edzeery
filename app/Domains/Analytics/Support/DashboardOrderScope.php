<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

final class DashboardOrderScope
{
    public function apply(EloquentBuilder|QueryBuilder $query, DashboardFilter $filter): void
    {
        $from = $filter->from;
        $to = $filter->to;

        if ($from && $to) {
            $query->whereBetween('orders.created_at', [$from->toDateTimeString(), $to->toDateTimeString()]);
        }

        if ($filter->carrierId) {
            $query->where('orders.shipping_provider_id', $filter->carrierId);
        }

        $this->applyMember($query, $filter);
    }

    public function applyPending(EloquentBuilder|QueryBuilder $query, DashboardFilter $filter): void
    {
        if ($filter->carrierId) {
            $query->where('orders.shipping_provider_id', $filter->carrierId);
        }

        $this->applyMember($query, $filter);
    }

    /**
     * Delivery charts answer questions about shipped orders, so their cohort
     * is orders carrying at least one tracking row for this store. Existence
     * is enough: a re-shipped order with several attempts is still one order.
     */
    public function trackedOnly(EloquentBuilder|QueryBuilder $query, DashboardFilter $filter): void
    {
        $query->whereExists(function ($sub) use ($filter) {
            $sub->selectRaw('1')
                ->from('order_trackings')
                ->whereColumn('order_trackings.order_id', 'orders.id')
                ->where('order_trackings.store_id', $filter->storeId);
        });
    }

    /**
     * memberScopeIds is null for "every member", so an unrestricted filter
     * adds no clause at all. An empty list (no membership) matches nothing,
     * which is the intended fail-closed behaviour.
     */
    private function applyMember(EloquentBuilder|QueryBuilder $query, DashboardFilter $filter): void
    {
        $scopeIds = $filter->memberScopeIds;

        if ($scopeIds === null) {
            return;
        }

        if ($filter->memberDimension === 'delivery') {
            $query->whereExists(function ($sub) use ($filter, $scopeIds) {
                $sub->selectRaw('1')
                    ->from('order_trackings')
                    ->whereColumn('order_trackings.order_id', 'orders.id')
                    ->where('order_trackings.store_id', $filter->storeId)
                    ->whereIn('order_trackings.assigned_to_membership_id', $scopeIds);
            });

            return;
        }

        $query->whereIn('orders.assigned_to_membership_id', $scopeIds);
    }
}
