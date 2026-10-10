<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Enums\Store\OrderStatus;
use Illuminate\Database\Eloquent\Builder;

final class DashboardTeamPerformanceQuery
{
    public function __construct(private DashboardOrderScope $scope) {}

    /**
     * Two aggregate queries for the whole team table: a fifty-person store costs
     * what a two-person one costs and no member is ever queried alone. Workload
     * ("المُسند") and credit are deliberately separate (§ 11 of
     * docs/plans/order-distribution-rules.md):
     *
     *  - credit groups by orders.confirmed_by_membership_id, the single key of
     *    both team tabs; it stops counting at the confirmed member's credit
     *    (confirmed / delivered / returned / revenue).
     *  - work groups by the assignment the tab walks (orders.assigned_to_membership_id
     *    for Confirmation, the newest tracking member for Delivery) and counts
     *    the workload (assigned / pending / canceled).
     *
     * Delivery reads one tracking row per order, because order_trackings has no
     * uniqueness on order_id and a re-shipped order would be counted twice.
     *
     * @param  Builder<\App\Models\Orders\Order>  $baseQuery
     * @param  callable(OrderStatus): ?string  $statusId
     * @param  callable(array<int, OrderStatus>): array<int, string>  $statusIds
     * @return array{credit: list<array<string, mixed>>, work: list<array<string, mixed>>}
     */
    public function run(Builder $baseQuery, DashboardFilter $filter, callable $statusId, callable $statusIds): array
    {
        $delivery = $filter->memberDimension === 'delivery';

        $delivered = $this->is($statusId(OrderStatus::DELIVERED));
        $returned = $this->is($statusId(OrderStatus::RETURNED));
        $pending = $this->is($statusId(OrderStatus::PENDING));
        $canceled = $this->in($statusIds(DashboardStatusGroups::CANCELED));
        $confirmed = $this->in($statusIds(DashboardStatusGroups::CONFIRMED));

        $credit = $this->aggregate($baseQuery, $filter, 'orders.confirmed_by_membership_id', [
            'confirmed' => [$this->countWhen($confirmed[0]), $confirmed[1]],
            'delivered' => [$this->countWhen($delivered[0]), $delivered[1]],
            'returned' => [$this->countWhen($returned[0]), $returned[1]],
            // Money is summed only on delivered rows, so a returned order never
            // contributes revenue.
            'revenue' => ['SUM(CASE WHEN '.$delivered[0].' THEN orders.total_amount ELSE 0 END)', $delivered[1]],
        ]);

        // Confirmation walks each order's own assignment; Delivery walks each
        // order's newest attempt. Neither path needs a join, so the columns the
        // WHERE clauses mention stay unambiguous.
        $groupKey = $delivery ? $this->latestMembership($filter) : 'orders.assigned_to_membership_id';

        $work = $this->aggregate($baseQuery, $filter, $groupKey, [
            'assigned' => ['COUNT(*)', []],
            'pending' => [$this->countWhen($pending[0]), $pending[1]],
            'canceled' => [$this->countWhen($canceled[0]), $canceled[1]],
        ]);

        return [
            'credit' => array_map(fn ($row) => $this->cast($row), $credit),
            'work' => array_map(fn ($row) => $this->cast($row), $work),
        ];
    }

    /**
     * @param  Builder<\App\Models\Orders\Order>  $baseQuery
     * @param  array<string, array{0: string, 1: array<int, string>}>  $columns
     * @return list<object>
     */
    private function aggregate(Builder $baseQuery, DashboardFilter $filter, string $groupKey, array $columns): array
    {
        $query = (clone $baseQuery);
        // The same window, carrier and member scope the KPIs use, so the Total
        // row always equals summary()['total_orders'] for the active filter.
        $this->scope->apply($query, $filter);

        $select = [$groupKey.' as membership_id'];
        $bindings = [];

        $rawGroup = str_starts_with($groupKey, '(select');

        // The correlated newest-tracking probe carries its own store binding,
        // and it must come first because its "?" is the first in the list.
        if ($rawGroup) {
            $bindings[] = $filter->storeId;
        }

        foreach ($columns as $alias => [$sql, $columnBindings]) {
            $select[] = $sql.' as '.$alias;
            $bindings = array_merge($bindings, $columnBindings);
        }

        // MySQL's only_full_group_by cannot prove that the correlated probe in
        // the SELECT list is the same expression as the probe repeated in the
        // GROUP BY: it stops functional-dependency reasoning at the subquery
        // boundary, so the GROUP BY "did not" contain orders.id (error 1055).
        // Grouping by the select alias names the exact expression that already
        // sits in the SELECT list, which MySQL accepts on both MySQL and
        // SQLite, and the GROUP BY then needs no binding of its own. Plain
        // column keys (assigned_to/confirmed_by) keep grouping by the column.
        if ($rawGroup) {
            $query->groupByRaw('membership_id');
        } else {
            $query->groupBy($groupKey);
        }

        return $query
            ->selectRaw(implode(', ', $select), $bindings)
            ->toBase()
            ->get()
            ->all();
    }

    /** @return array<string, int|string|float|null> */
    private function cast(object $row): array
    {
        return [
            'membership_id' => $row->membership_id,
            'assigned' => (int) ($row->assigned ?? 0),
            'confirmed' => (int) ($row->confirmed ?? 0),
            'pending' => (int) ($row->pending ?? 0),
            'canceled' => (int) ($row->canceled ?? 0),
            'delivered' => (int) ($row->delivered ?? 0),
            'returned' => (int) ($row->returned ?? 0),
            'revenue' => (float) ($row->revenue ?? 0),
        ];
    }

    /**
     * order_trackings is not unique per order, so only the newest attempt counts;
     * MAX(id) is the newest because ULIDs sort by time, and its assignment is
     * what the Delivery view groups by. The lookup is correlated per order
     * instead of a set built once: SQLite re-runs an IN (SELECT MAX(id) ...)
     * list for every row it joins, which turns a busy window into minutes, while
     * one probe per order stays linear on every driver.
     */
    private function latestMembership(DashboardFilter $filter): string
    {
        return '(select order_trackings.assigned_to_membership_id from order_trackings'
            .' where order_trackings.id = (select MAX(m.id) from order_trackings as m'
            .' where m.store_id = ? and m.order_id = orders.id))';
    }

    /**
     * A condition plus its bindings, kept together so the placeholders can
     * never drift out of step with the values they replace.
     *
     * @param  array<int, string>  $ids
     * @return array{0: string, 1: array<int, string>}
     */
    private function in(array $ids): array
    {
        // The store has not defined these statuses, so nothing matches.
        return $ids === []
            ? ['1 = 0', []]
            : ['orders.status_id IN ('.implode(', ', array_fill(0, count($ids), '?')).')', $ids];
    }

    /** @return array{0: string, 1: array<int, string>} */
    private function is(?string $id): array
    {
        return $id === null ? ['1 = 0', []] : ['orders.status_id = ?', [$id]];
    }

    private function countWhen(string $condition): string
    {
        return 'SUM(CASE WHEN '.$condition.' THEN 1 ELSE 0 END)';
    }
}
