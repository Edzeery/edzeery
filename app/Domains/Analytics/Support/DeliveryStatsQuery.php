<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;

/**
 * PHASE 37-N7 (D10) — one grouped read of the delivery tab.
 *
 * A single aggregate query, folded by {@see DeliveryStatsReport}: the counts per
 * raw order status behind the rate cards and the headline KPIs. Live and soft-
 * deleted orders travel together (withTrashed) in their own columns — a deleted
 * order is never part of a ratio (D7), so it is counted between brackets, not
 * filtered out. The rows are grouped by status only: unlike confirmation, the
 * delivery rate is a single store-wide line, so no membership key is involved.
 */
final class DeliveryStatsQuery
{
    public function __construct(
        private readonly DashboardOrderScope $scope,
    ) {}

    /**
     * @return list<array{status_key: string, total: int, deleted: int}>
     */
    public function run(DashboardFilter $filter): array
    {
        $inner = Order::query()
            ->where('orders.store_id', $filter->storeId)
            ->withTrashed()
            ->join('statuses', 'statuses.id', '=', 'orders.status_id')
            ->select([
                'orders.status_id as status_id',
                'statuses.key as status_key',
                'orders.deleted_at as deleted_at',
            ]);

        $this->scope->apply($inner, $filter);
        // PHASE 37-N9: the delivery tab counts the tracking cohort only.
        $this->scope->deliveryCohort($inner, $filter);

        $rows = DB::query()
            ->fromSub($inner->toBase(), 'delivery_rows')
            ->selectRaw('delivery_rows.status_id as status_id')
            ->selectRaw('delivery_rows.status_key as status_key')
            ->selectRaw('SUM(CASE WHEN delivery_rows.deleted_at IS NULL THEN 1 ELSE 0 END) as total')
            ->selectRaw('SUM(CASE WHEN delivery_rows.deleted_at IS NOT NULL THEN 1 ELSE 0 END) as deleted')
            ->groupBy('delivery_rows.status_id', 'delivery_rows.status_key')
            ->get();

        return collect($rows)->map(static fn ($row): array => [
            'status_key' => (string) $row->status_key,
            'total' => (int) $row->total,
            'deleted' => (int) $row->deleted,
        ])->all();
    }
}
