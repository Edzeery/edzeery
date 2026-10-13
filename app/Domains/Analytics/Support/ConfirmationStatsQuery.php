<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;

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
    public function __construct(
        private readonly DashboardOrderScope $scope,
        private readonly OrderStatusIdMap $statusIds,
    ) {}

    /**
     * @return list<array{membership_id: ?string, status_key: string, total: int, deleted: int}>
     */
    public function run(DashboardFilter $filter): array
    {
        // PHASE 37-N1 (D2): the bucket is "ever confirmed", not the current
        // status. The predicate is computed once per row in a derived table and
        // folded into the bucket key in the outer GROUP BY. The CASE cannot sit
        // in the inner SELECT beside a `(orders.confirmed_at is not null)` GROUP
        // BY: MySQL rejects a SELECT that reads `orders.confirmed_at` when only
        // the boolean expression is grouped (ONLY_FULL_GROUP_BY). Here every
        // non-aggregated column the outer CASE reads is itself a grouped column.
        $evidence = new ConfirmationEvidence($this->statusIds);

        $inner = Order::query()
            ->where('orders.store_id', $filter->storeId)
            ->withTrashed()
            ->join('statuses', 'statuses.id', '=', 'orders.status_id')
            ->select([
                'orders.assigned_to_membership_id as membership_id',
                'orders.status_id as status_id',
                'statuses.key as status_key',
                'orders.deleted_at as deleted_at',
            ])
            ->selectRaw($evidence->condition().' as was_confirmed', $evidence->bindings());

        // PHASE 37-M1: the report answers the same member question as the rest
        // of the confirmation view (the assignment key, via the shared scope),
        // so the cards, the doughnut and the KPI can never disagree about who
        // belongs in the window. The date window and the product EXISTS live in
        // the scope too, shared with every other confirmation block.
        $this->scope->apply($inner, $filter);

        $rows = DB::query()
            ->fromSub($inner->toBase(), 'confirmation_rows')
            ->selectRaw('confirmation_rows.membership_id as membership_id')
            ->selectRaw("CASE WHEN confirmation_rows.was_confirmed THEN 'confirmed' ELSE confirmation_rows.status_key END as status_key")
            ->selectRaw('SUM(CASE WHEN confirmation_rows.deleted_at IS NULL THEN 1 ELSE 0 END) as total')
            ->selectRaw('SUM(CASE WHEN confirmation_rows.deleted_at IS NOT NULL THEN 1 ELSE 0 END) as deleted')
            ->groupBy(
                'confirmation_rows.membership_id',
                'confirmation_rows.status_id',
                'confirmation_rows.status_key',
                'confirmation_rows.was_confirmed',
            )
            ->get();

        return collect($rows)->map(static fn ($row): array => [
            'membership_id' => $row->membership_id,
            'status_key' => (string) $row->status_key,
            'total' => (int) $row->total,
            'deleted' => (int) $row->deleted,
        ])->all();
    }
}
