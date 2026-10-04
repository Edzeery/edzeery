<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class DashboardSeriesQuery
{
    public function __construct(
        private DashboardOrderScope $scope,
        private DateBucket $bucket
    ) {}

    /**
     * @param  Builder<\App\Models\Orders\Order>  $baseQuery
     */
    public function salesSeries(Builder $baseQuery, DashboardFilter $filter): array
    {
        $driver = DB::getDriverName();
        $bucketExpr = $this->bucket->bucketExpression($driver);

        $from = $filter->from;
        $to = $filter->to;
        if (! $from) {
            return [
                'labels' => [],
                'revenue' => [],
                'orders' => [],
                'trend' => collect(),
            ];
        }

        $query = (clone $baseQuery)
            ->selectRaw($bucketExpr)
            ->selectRaw('SUM(total_amount) as revenue')
            ->selectRaw('COUNT(*) as orders')
            ->where('status_id', $this->statusDeliveredId($baseQuery, $filter))
            ->groupBy('bucket')
            ->orderBy('bucket');

        $scope = $this->scope;
        $scope->apply($query, $filter);

        $rows = $query->get();

        return $this->bucket->generateSeries($from, $to, $filter->granularity(), function () use ($rows) {
            return $rows;
        });
    }

    private function statusDeliveredId($baseQuery, DashboardFilter $filter): ?string
    {
        $storeId = $filter->storeId;
        static $map = [];
        if (isset($map[$storeId])) {
            return $map[$storeId];
        }

        $id = DB::table('statuses')
            ->where('type', 'order')
            ->where('key', 'delivered')
            ->where('store_id', $storeId)
            ->value('id');

        $map[$storeId] = $id;

        return $id;
    }
}
