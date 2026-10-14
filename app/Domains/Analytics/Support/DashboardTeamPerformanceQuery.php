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

        // PHASE 37-N9: Delivery credits the member who shipped the order, not
        // the confirmer, so its figures all come from the work aggregate and the
        // confirmed_by ledger is never read. Confirmation keeps the credit side.
        $credit = $delivery ? [] : $this->aggregate($baseQuery, $filter, 'orders.confirmed_by_membership_id', [
            'confirmed' => $this->countWhen($confirmed, false),
            'confirmed_deleted' => $this->countWhen($confirmed, true),
            'delivered' => $this->countWhen($delivered, false),
            'delivered_deleted' => $this->countWhen($delivered, true),
            'returned' => $this->countWhen($returned, false),
            'returned_deleted' => $this->countWhen($returned, true),
            // Money is summed only on live delivered rows, so a returned order
            // never contributes revenue.
            'revenue' => ['SUM(CASE WHEN '.$delivered[0].' THEN orders.total_amount ELSE 0 END)', $delivered[1]],
        ]);

        // Confirmation walks each order's own assignment; Delivery walks each
        // order's newest attempt. Neither path needs a join, so the columns the
        // WHERE clauses mention stay unambiguous.
        $groupKey = $delivery ? $this->latestMembership($filter) : 'orders.assigned_to_membership_id';

        $work = $this->aggregate($baseQuery, $filter, $groupKey, $this->workColumns($filter, $delivery, [
            'pending' => $pending,
            'canceled' => $canceled,
            'delivered' => $delivered,
            'returned' => $returned,
        ]));

        return [
            'credit' => array_map(fn ($row) => $this->cast($row), $credit),
            'work' => array_map(fn ($row) => $this->cast($row), $work),
        ];
    }

    /**
     * The workload aggregate's columns. Confirmation keeps the 37-J shape.
     * Delivery (PHASE 37-N7, D2/D9; PHASE 37-N9) carries every figure the card
     * shows: the settled statuses (delivered / returned) and their money are
     * credited to the member whose newest tracking row shipped the order, and
     * the two return states read off that same row ("الراجعة" is a registered
     * return not yet processed, "المرجعة" is one that has been). Status columns
     * stay keyed to orders.status_id; the tracking ones probe the newest row, so
     * a re-shipped order counts once.
     *
     * @param  array{pending: array{0: string, 1: array<int, string>}, canceled: array{0: string, 1: array<int, string>}, delivered: array{0: string, 1: array<int, string>}, returned: array{0: string, 1: array<int, string>}}  $conditions
     * @return array<string, array{0: string, 1: array<int, string>}>
     */
    private function workColumns(DashboardFilter $filter, bool $delivery, array $conditions): array
    {
        $columns = [
            'assigned' => $this->countAll(false),
            'assigned_deleted' => $this->countAll(true),
            'pending' => $this->countWhen($conditions['pending'], false),
            'pending_deleted' => $this->countWhen($conditions['pending'], true),
            'canceled' => $this->countWhen($conditions['canceled'], false),
            'canceled_deleted' => $this->countWhen($conditions['canceled'], true),
        ];

        if (! $delivery) {
            return $columns;
        }

        $columns['delivered'] = $this->countWhen($conditions['delivered'], false);
        $columns['delivered_deleted'] = $this->countWhen($conditions['delivered'], true);
        $columns['returned'] = $this->countWhen($conditions['returned'], false);
        $columns['returned_deleted'] = $this->countWhen($conditions['returned'], true);
        $columns['returned_coming'] = $this->countWhen(
            $this->newestTrackingWhen($filter, 'order_trackings.returned_at IS NOT NULL AND order_trackings.processed_at IS NULL'),
            false,
        );
        $columns['returned_processed'] = $this->countWhen(
            $this->newestTrackingWhen($filter, 'order_trackings.processed_at IS NOT NULL'),
            false,
        );
        // PHASE 37-N9: the money the member's shipped orders brought, summed on
        // live delivered rows only, so a returned order never contributes.
        $columns['revenue'] = ['SUM(CASE WHEN '.$conditions['delivered'][0].' AND orders.deleted_at IS NULL THEN orders.total_amount ELSE 0 END)', $conditions['delivered'][1]];

        return $columns;
    }

    /**
     * A condition on the order's newest tracking row. order_trackings is not
     * unique per order, so the probe is pinned to MAX(id) (ULIDs sort by time);
     * the store binding mirrors latestMembership().
     *
     * @return array{0: string, 1: array<int, string>}
     */
    private function newestTrackingWhen(DashboardFilter $filter, string $predicate): array
    {
        $sql = 'EXISTS (select 1 from order_trackings where order_trackings.id = ('
            .'select MAX(m.id) from order_trackings as m where m.store_id = ? and m.order_id = orders.id)'
            .' and '.$predicate.')';

        return [$sql, [$filter->storeId]];
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

        // PHASE 37-N9: the Delivery team counts the same tracking cohort as the
        // other delivery blocks, so its member rows describe orders actually
        // handed to a carrier rather than the whole order ledger.
        if ($filter->memberDimension === 'delivery') {
            $this->scope->deliveryCohort($query, $filter);
        }

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
            'assigned_deleted' => (int) ($row->assigned_deleted ?? 0),
            'confirmed' => (int) ($row->confirmed ?? 0),
            'confirmed_deleted' => (int) ($row->confirmed_deleted ?? 0),
            'pending' => (int) ($row->pending ?? 0),
            'pending_deleted' => (int) ($row->pending_deleted ?? 0),
            'canceled' => (int) ($row->canceled ?? 0),
            'canceled_deleted' => (int) ($row->canceled_deleted ?? 0),
            'delivered' => (int) ($row->delivered ?? 0),
            'delivered_deleted' => (int) ($row->delivered_deleted ?? 0),
            'returned' => (int) ($row->returned ?? 0),
            'returned_deleted' => (int) ($row->returned_deleted ?? 0),
            'returned_coming' => (int) ($row->returned_coming ?? 0),
            'returned_processed' => (int) ($row->returned_processed ?? 0),
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

    /**
     * A count of the rows matching a condition pair, split by the soft-delete
     * flag so the cards can show a live figure and its trashed sibling. The
     * condition keeps its bindings next to its SQL so the placeholders never
     * drift.
     *
     * @param  array{0: string, 1: array<int, string>}  $condition
     * @return array{0: string, 1: array<int, string>}
     */
    private function countWhen(array $condition, bool $deleted): array
    {
        [$sql, $bindings] = $condition;
        $clause = $deleted ? 'IS NOT NULL' : 'IS NULL';

        return ['SUM(CASE WHEN ('.$sql.') AND orders.deleted_at '.$clause.' THEN 1 ELSE 0 END)', $bindings];
    }

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    private function countAll(bool $deleted): array
    {
        $clause = $deleted ? 'IS NOT NULL' : 'IS NULL';

        return ['SUM(CASE WHEN orders.deleted_at '.$clause.' THEN 1 ELSE 0 END)', []];
    }
}
