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

        // PHASE 37-L: the report answers the same member question as the rest
        // of the confirmation view (the credit key, via the shared scope), so
        // the cards, the doughnut and the KPI can never disagree about who
        // belongs in the window. The date window and the product EXISTS live in
        // the scope too, shared with every other confirmation block.
        $this->scope->apply($query, $filter);

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
}
