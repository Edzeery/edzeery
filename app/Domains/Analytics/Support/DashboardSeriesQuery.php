<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
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
     * One bucketed query for any set of metrics: every entry in $metrics is
     * one SELECT expression aliased by its key, all grouped by the same
     * bucket, so a chart with three lines still costs exactly one query and
     * its lines can never disagree about where a bucket starts.
     *
     * @param  Builder<\App\Models\Orders\Order>  $baseQuery
     * @param  array<int, array{key: string, expression: string, bindings?: array<int, string>, cast?: 'int'|'float'}>  $metrics
     * @return array{labels: array<int, string>, values: array<string, array<int, int|float>>, trend: Collection<string, array<string, int|float>>}
     */
    public function series(Builder $baseQuery, DashboardFilter $filter, array $metrics): array
    {
        $columns = [];

        foreach ($metrics as $metric) {
            $columns[$metric['key']] = $metric['cast'] ?? 'int';
        }

        [$from, $to] = $this->resolveWindow($baseQuery, $filter);

        if ($from === null) {
            return [
                'labels' => [],
                'values' => array_fill_keys(array_keys($columns), []),
                'trend' => collect(),
            ];
        }

        $driver = DB::getDriverName();
        $granularity = DateBucket::granularityFor($from, $to);

        $query = (clone $baseQuery)
            ->selectRaw($this->bucket->bucketExpression($driver, $granularity, $filter->utcOffsetSeconds));

        foreach ($metrics as $metric) {
            $query->selectRaw($metric['expression'].' as '.$metric['key'], $metric['bindings'] ?? []);
        }

        $query->groupBy('bucket')->orderBy('bucket');

        $this->scope->apply($query, $filter);

        $rows = $query->get();

        return $this->bucket->fill(
            $from,
            $to,
            $filter->timezone,
            $filter->utcOffsetSeconds,
            fn () => $rows,
            $columns
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
