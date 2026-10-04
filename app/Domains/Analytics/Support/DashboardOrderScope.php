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

        if ($filter->memberId && $filter->memberDimension && ! empty($filter->allowedMembershipIds)) {
            if (in_array($filter->memberId, $filter->allowedMembershipIds, true)) {
                if ($filter->memberDimension === 'confirmation') {
                    $query->where('orders.assigned_to_membership_id', $filter->memberId);
                } elseif ($filter->memberDimension === 'delivery') {
                    $query->whereExists(function ($sub) use ($filter) {
                        $sub->selectRaw('1')
                            ->from('order_trackings')
                            ->whereColumn('order_trackings.order_id', 'orders.id')
                            ->where('order_trackings.store_id', $filter->storeId)
                            ->where('order_trackings.assigned_to_membership_id', $filter->memberId);
                    });
                }
            }
        }
    }

    public function applyPending(EloquentBuilder|QueryBuilder $query, DashboardFilter $filter): void
    {

        if ($filter->carrierId) {
            $query->where('orders.shipping_provider_id', $filter->carrierId);
        }

        if ($filter->memberId && $filter->memberDimension && ! empty($filter->allowedMembershipIds)) {
            if (in_array($filter->memberId, $filter->allowedMembershipIds, true)) {
                if ($filter->memberDimension === 'confirmation') {
                    $query->where('orders.assigned_to_membership_id', $filter->memberId);
                } elseif ($filter->memberDimension === 'delivery') {
                    $query->whereExists(function ($sub) use ($filter) {
                        $sub->selectRaw('1')
                            ->from('order_trackings')
                            ->whereColumn('order_trackings.order_id', 'orders.id')
                            ->where('order_trackings.store_id', $filter->storeId)
                            ->where('order_trackings.assigned_to_membership_id', $filter->memberId);
                    });
                }
            }
        }
    }
}
