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

        // The pending queue is a workload ("المُسند") concept: it always walks
        // the assignment list, never the credit list. Every pending order is
        // unattributed by definition, so the sentinel adds no clause at all.
        $this->applyPendingMember($query, $filter);
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
     * Team walks are keyed on the confirmed member for both tabs (§11 of
     * docs/plans/order-distribution-rules.md): credit belongs to the member
     * who confirmed the order, never to the tracking assignee, and there is no
     * delivered_by at all. The unattributed cohort is confirmed_by IS NULL.
     *
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

        $includeUnattributed = in_array(DashboardFilter::UNATTRIBUTED, $scopeIds, true);
        $memberIds = array_values(array_filter($scopeIds, static fn ($id) => $id !== DashboardFilter::UNATTRIBUTED));

        if ($memberIds === []) {
            if ($includeUnattributed) {
                $query->whereNull('orders.confirmed_by_membership_id');

                return;
            }

            // Fail closed: an empty scope (no membership) matches nothing.
            $query->whereIn('orders.confirmed_by_membership_id', []);

            return;
        }

        $query->where(function ($q) use ($memberIds, $includeUnattributed) {
            $q->whereIn('orders.confirmed_by_membership_id', $memberIds);

            if ($includeUnattributed) {
                $q->orWhereNull('orders.confirmed_by_membership_id');
            }
        });
    }

    /**
     * Workload scoping for the pending queue: always by the assignment list.
     * Sentinel-only means the whole queue (every pending order is unattributed);
     * an empty list fails closed.
     */
    private function applyPendingMember(EloquentBuilder|QueryBuilder $query, DashboardFilter $filter): void
    {
        $scopeIds = $filter->memberScopeIds;

        if ($scopeIds === null) {
            return;
        }

        $includeUnattributed = in_array(DashboardFilter::UNATTRIBUTED, $scopeIds, true);
        $memberIds = array_values(array_filter($scopeIds, static fn ($id) => $id !== DashboardFilter::UNATTRIBUTED));

        if ($memberIds === []) {
            if ($includeUnattributed) {
                return;
            }

            $query->whereIn('orders.assigned_to_membership_id', []);

            return;
        }

        $query->whereIn('orders.assigned_to_membership_id', $memberIds);
    }
}
