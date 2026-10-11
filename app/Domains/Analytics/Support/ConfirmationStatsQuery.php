<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Models\Orders\Order;

/**
 * One grouped read of the confirmation tab (docs/plans/confirmation-statistics-redesign.md § 7).
 *
 * Ships a single aggregate query — grouped by (assigned member, status) — that
 * counts live and soft-deleted orders together. Deleted orders are never part
 * of a ratio (D7), so they travel in their own column and are rendered between
 * brackets; nothing is filtered out here, which keeps the tab to one query and
 * makes the deleted figure and the live figure answer the same scan.
 */
final class ConfirmationStatsQuery
{
    public function __construct(private readonly DashboardOrderScope $scope) {}

    /**
     * @return list<array{membership_id: ?string, status_key: string, total: int, deleted: int}>
     */
    public function run(DashboardFilter $filter): array
    {
        $query = Order::query()
            ->where('orders.store_id', $filter->storeId)
            ->withTrashed();

        $this->applyDateWindow($query, $filter);

        // The confirmation member dimension is the assignment list (D1), which
        // is exactly what the pending workload scope walks.
        $this->scope->applyPending($query, $filter);

        $this->applyProductFilter($query, $filter);

        $rows = $query
            ->join('statuses', 'statuses.id', '=', 'orders.status_id')
            ->selectRaw('orders.assigned_to_membership_id as membership_id')
            ->selectRaw('statuses.key as status_key')
            ->selectRaw('SUM(CASE WHEN orders.deleted_at IS NULL THEN 1 ELSE 0 END) as total')
            ->selectRaw('SUM(CASE WHEN orders.deleted_at IS NOT NULL THEN 1 ELSE 0 END) as deleted')
            ->groupBy('orders.assigned_to_membership_id', 'statuses.key')
            ->toBase()
            ->get();

        return $rows->map(static fn ($row): array => [
            'membership_id' => $row->membership_id,
            'status_key' => (string) $row->status_key,
            'total' => (int) $row->total,
            'deleted' => (int) $row->deleted,
        ])->all();
    }

    private function applyDateWindow($query, DashboardFilter $filter): void
    {
        if (! $filter->from || ! $filter->to) {
            return;
        }

        $column = $filter->dateBasis === DashboardFilter::DATE_BASIS_LAST_STATUS
            ? 'COALESCE(orders.last_status_changed_at, orders.created_at)'
            : 'orders.created_at';

        $query->whereRaw(
            $column.' BETWEEN ? AND ?',
            [$filter->from->toDateTimeString(), $filter->to->toDateTimeString()]
        );
    }

    /**
     * The product picker (D3/D10) narrows to orders holding any of the picked
     * products or variants. An order with several matching lines still counts
     * once, so the clause is an EXISTS rather than a join.
     */
    private function applyProductFilter($query, DashboardFilter $filter): void
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
}
