<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PHASE 37-K — the status doughnut for the active stats view.
 *
 * Confirmation collapses the confirmed group into one slice: the operator
 * cares that the order got through, not which shipping step it sits in, and
 * one slice keeps the chart, the confirmation-rate KPI and the team table
 * reading the same confirmed total. Delivery keeps every status separate,
 * because there the individual steps are the story, and its cohort is the
 * shipped orders only (a doughnut of delivery statuses drawn over
 * never-shipped orders would answer a question nobody asked).
 *
 * Percentages are whole numbers whose last slice absorbs the rounding, so
 * the legend always adds up to exactly 100.
 */
final class DashboardStatusBreakdown
{
    public function __construct(
        private DashboardOrderScope $scope,
        private OrderStatusChartMapper $mapper,
    ) {}

    /**
     * @return Collection<int, object{key: string, count: int, label: string, hex: string, percent: int}>
     */
    public function run(DashboardFilter $filter, string $storeId): Collection
    {
        $delivery = $filter->memberDimension === 'delivery';

        $query = DB::table('orders')
            ->join('statuses', 'statuses.id', '=', 'orders.status_id')
            ->where('orders.store_id', $storeId)
            ->whereNull('orders.deleted_at');

        if ($delivery) {
            $this->scope->trackedOnly($query, $filter);
        }

        $this->scope->apply($query, $filter);

        $rows = $query
            ->select('statuses.key', DB::raw('COUNT(*) as count'))
            ->groupBy('statuses.key')
            ->orderByDesc('count')
            ->get();

        if (! $delivery) {
            $rows = $this->collapseConfirmed($rows);
        }

        return $this->withPercent($this->mapper->map($rows, $storeId));
    }

    /**
     * Every confirmed-group key becomes the one `confirmed` slice; all other
     * statuses (pending, no_answer_*, postponed, on_hold, wrong_number, the
     * cancelled aliases, custom keys) keep their own slice.
     *
     * @param  Collection<int, object>  $rows
     * @return Collection<int, object>
     */
    private function collapseConfirmed(Collection $rows): Collection
    {
        $confirmed = 0;
        $rest = collect();

        foreach ($rows as $row) {
            if (DashboardStatusGroups::inConfirmed((string) $row->key)) {
                $confirmed += (int) $row->count;

                continue;
            }

            $rest->push($row);
        }

        if ($confirmed > 0) {
            $rest->push((object) ['key' => 'confirmed', 'count' => $confirmed]);
        }

        return $rest->sortByDesc('count')->values();
    }

    /**
     * @param  Collection<int, object>  $rows  Mapped slices carrying key/label/hex/count.
     * @return Collection<int, object>
     */
    private function withPercent(Collection $rows): Collection
    {
        $total = (int) $rows->sum('count');

        if ($total < 1) {
            return $rows;
        }

        $last = $rows->count() - 1;
        $percents = [];
        $running = 0;

        foreach ($rows as $index => $row) {
            if ($index === $last) {
                break;
            }

            $percents[$index] = (int) round(((int) $row->count / $total) * 100);
            $running += $percents[$index];
        }

        $tail = 100 - $running;

        // Rounding every earlier slice up can push the running total past 100
        // when the chart has many small slices; the excess comes off the
        // biggest slices first, so no slice goes negative and the legend
        // always reads exactly 100%.
        if ($tail < 0) {
            $deficit = -$tail;

            foreach ($percents as $index => $percent) {
                $take = min($percent, $deficit);
                $percents[$index] = $percent - $take;
                $deficit -= $take;

                if ($deficit === 0) {
                    break;
                }
            }

            $tail = 100 - array_sum($percents);
        }

        $percents[$last] = $tail;

        return $rows->map(fn (object $row, int $index) => (object) [
            'key' => (string) $row->key,
            'count' => (int) $row->count,
            'label' => (string) $row->label,
            'hex' => (string) $row->hex,
            'percent' => $percents[$index],
        ])->values();
    }
}
