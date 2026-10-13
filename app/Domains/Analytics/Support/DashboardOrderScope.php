<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

final class DashboardOrderScope
{
    public function apply(EloquentBuilder|QueryBuilder $query, DashboardFilter $filter): void
    {
        // PHASE 37-L: the date window and the product EXISTS live here now, so
        // every block of a view walks the exact same rows. The builder only
        // ever sees the fields DashboardFilterFactory left for the active view
        // (D3), so a delivery walk gets a created-at window and no product
        // clause, and a confirmation walk gets the basis and the products.
        $this->applyDateWindow($query, $filter);
        $this->applyProductFilter($query, $filter);

        if ($filter->carrierId) {
            $query->where('orders.shipping_provider_id', $filter->carrierId);
        }

        $this->applyMember($query, $filter);
    }

    public function applyPending(EloquentBuilder|QueryBuilder $query, DashboardFilter $filter): void
    {
        // The pending queue is a workload ("المُسند") concept: it always walks
        // the assignment list, never the credit list. Every pending order is
        // unattributed by definition, so the sentinel adds no clause at all.
        // It is a confirmation block, so it takes the date basis and the
        // product pick (D4) but never the carrier (D3).
        $this->applyDateWindow($query, $filter);
        $this->applyProductFilter($query, $filter);

        if ($filter->carrierId) {
            $query->where('orders.shipping_provider_id', $filter->carrierId);
        }

        $this->applyPendingMember($query, $filter);
    }

    /**
     * The window on the chosen date basis (D2). PHASE 37-N1 (D1/D3): the
     * "last status" basis reads `confirmation_status_changed_at` — the last
     * confirmation attempt, frozen once the order is confirmed — so a
     * confirmed order is never restated by a later ship/deliver/return. It is
     * written sargable — a plain `confirmation_status_changed_at BETWEEN`
     * short-circuitable by the (store_id, confirmation_status_changed_at)
     * index — with the created-at range only as the fallback for a row the
     * backfill could not stamp, so those are still counted rather than lost.
     */
    private function applyDateWindow(EloquentBuilder|QueryBuilder $query, DashboardFilter $filter): void
    {
        $from = $filter->from;
        $to = $filter->to;

        if (! $from || ! $to) {
            return;
        }

        $from = $from->toDateTimeString();
        $to = $to->toDateTimeString();

        if ($filter->dateBasis !== DashboardFilter::DATE_BASIS_LAST_STATUS) {
            $query->whereBetween('orders.created_at', [$from, $to]);

            return;
        }

        $query->where(function ($window) use ($from, $to) {
            $window->whereBetween('orders.confirmation_status_changed_at', [$from, $to])
                ->orWhere(function ($unstamped) use ($from, $to) {
                    $unstamped->whereNull('orders.confirmation_status_changed_at')
                        ->whereBetween('orders.created_at', [$from, $to]);
                });
        });
    }

    /**
     * The product picker (D3/D10) narrows to orders holding any of the picked
     * products or variants. An order with several matching lines still counts
     * once, so the clause is an EXISTS rather than a join.
     */
    private function applyProductFilter(EloquentBuilder|QueryBuilder $query, DashboardFilter $filter): void
    {
        $products = $filter->productIds ?? [];
        $variants = $filter->variantIds ?? [];

        if ($products === [] && $variants === []) {
            return;
        }

        $query->whereExists(function ($sub) use ($products, $variants) {
            $sub->selectRaw('1')
                ->from('order_items')
                ->whereColumn('order_items.order_id', 'orders.id')
                ->whereNull('order_items.deleted_at');

            $sub->where(function ($w) use ($products, $variants) {
                if ($products !== []) {
                    $w->whereIn('order_items.product_id', $products);
                }

                if ($variants !== []) {
                    $products !== []
                        ? $w->orWhereIn('order_items.product_variant_id', $variants)
                        : $w->whereIn('order_items.product_variant_id', $variants);
                }
            });
        });
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
     * PHASE 37-M (D1): the member key is the dimension's own key (§11 of
     * docs/plans/order-distribution-rules.md). The confirmation tab's member
     * rows and member filter read `orders.assigned_to_membership_id` — the
     * "work" key — so they agree with {@see ConfirmationStatsQuery}, which
     * groups by that same column. The delivery tab keeps the financial credit
     * key `orders.confirmed_by_membership_id`; there is no delivered_by at all.
     * The sentinel therefore resolves to `whereNull` on the active key, never
     * on the other one.
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

        $column = $filter->memberDimension === 'delivery'
            ? 'orders.confirmed_by_membership_id'
            : 'orders.assigned_to_membership_id';

        $includeUnattributed = in_array(DashboardFilter::UNATTRIBUTED, $scopeIds, true);
        $memberIds = array_values(array_filter($scopeIds, static fn ($id) => $id !== DashboardFilter::UNATTRIBUTED));

        if ($memberIds === []) {
            if ($includeUnattributed) {
                $query->whereNull($column);

                return;
            }

            // Fail closed: an empty scope (no membership) matches nothing.
            $query->whereIn($column, []);

            return;
        }

        $query->where(function ($q) use ($memberIds, $includeUnattributed, $column) {
            $q->whereIn($column, $memberIds);

            if ($includeUnattributed) {
                $q->orWhereNull($column);
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
