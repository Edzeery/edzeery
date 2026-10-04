<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Enums\Store\OrderStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DashboardSeriesQuery
{
    public function __construct(
        private DashboardOrderScope $scope,
        private DateBucket $bucket
    ) {}

    /**
     * @param  Builder<\App\Models\Orders\Order>  $baseQuery
     * @param  callable(OrderStatus): ?string  $statusId  Same resolver the summary uses.
     * @return array{labels: array<int, string>, revenue: array<int, float>, orders: array<int, int>, trend: Collection}
     */
    public function salesSeries(Builder $baseQuery, DashboardFilter $filter, callable $statusId): array
    {
        [$from, $to] = $this->resolveWindow($baseQuery, $filter);

        if ($from === null) {
            return ['labels' => [], 'revenue' => [], 'orders' => [], 'trend' => collect()];
        }

        $driver = DB::getDriverName();
        $granularity = DateBucket::granularityFor($from, $to);

        // The orders line counts every order in scope; only delivered orders
        // contribute revenue. Restricting the whole query to delivered used to
        // hide the other orders entirely.
        $query = (clone $baseQuery)
            ->selectRaw($this->bucket->bucketExpression($driver, $granularity, $filter->utcOffsetSeconds))
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('SUM(CASE WHEN orders.status_id = ? THEN orders.total_amount ELSE 0 END) as revenue', [
                $statusId(OrderStatus::DELIVERED),
            ])
            ->groupBy('bucket')
            ->orderBy('bucket');

        $this->scope->apply($query, $filter);

        $rows = $query->get();

        return $this->bucket->generateSeries(
            $from,
            $to,
            $driver,
            $filter->timezone,
            $filter->utcOffsetSeconds,
            fn () => $rows
        );
    }

    /**
     * "All" has no factory boundaries: anchor the axis at the oldest scoped
     * order, so a chart is drawn only when there is something to show.
     *
     * @param  Builder<\App\Models\Orders\Order>  $baseQuery
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function resolveWindow(Builder $baseQuery, DashboardFilter $filter): array
    {
        if ($filter->from !== null && $filter->to !== null) {
            return [$filter->from, $filter->to];
        }

        $oldest = (clone $baseQuery)->min('orders.created_at');

        if ($oldest === null) {
            return [null, null];
        }

        return [
            CarbonImmutable::parse((string) $oldest, 'UTC'),
            CarbonImmutable::now('UTC'),
        ];
    }
}
