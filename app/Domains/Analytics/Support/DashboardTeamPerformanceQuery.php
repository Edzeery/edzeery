<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Enums\Store\OrderStatus;
use Illuminate\Database\Eloquent\Builder;

final class DashboardTeamPerformanceQuery
{
    public function __construct(private DashboardOrderScope $scope) {}

    /**
     * One aggregate query for the whole team table: a fifty-person store costs
     * what a two-person one costs and no member is ever queried alone. Delivery
     * reads one tracking row per order, because order_trackings has no
     * uniqueness on order_id and a re-shipped order would be counted twice.
     *
     * @param  Builder<\App\Models\Orders\Order>  $baseQuery
     * @param  callable(OrderStatus): ?string  $statusId
     * @param  callable(array<int, OrderStatus>): array<int, string>  $statusIds
     * @return list<array<string, mixed>>
     */
    public function run(Builder $baseQuery, DashboardFilter $filter, callable $statusId, callable $statusIds): array
    {
        $delivery = $filter->memberDimension === 'delivery';
        // Delivery groups by each order's newest attempt, read as one indexed
        // probe; Confirmation groups by the order's own assignment. Neither path
        // needs a join, so the columns the WHERE clauses mention stay unambiguous.
        $groupKey = $delivery ? 'membership_id' : 'orders.assigned_to_membership_id';

        $query = (clone $baseQuery);
        // The same window, carrier and member scope the KPIs use, so the Total
        // row always equals summary()['total_orders'] for the active filter.
        $this->scope->apply($query, $filter);

        $conditions = [
            'confirmed' => $this->in($statusIds(DashboardStatusGroups::CONFIRMED)),
            'pending' => $this->is($statusId(OrderStatus::PENDING)),
            'canceled' => $this->in($statusIds(DashboardStatusGroups::CANCELED)),
            'delivered' => $this->is($statusId(OrderStatus::DELIVERED)),
            'returned' => $this->is($statusId(OrderStatus::RETURNED)),
        ];
        // Money is summed only on delivered rows, so a returned order never
        // contributes revenue.
        $revenue = $this->is($statusId(OrderStatus::DELIVERED));

        $latest = $delivery ? $this->latestMembership($filter) : 'orders.assigned_to_membership_id';
        $select = [$latest.' as membership_id', 'COUNT(*) as assigned'];
        $bindings = $delivery ? [$filter->storeId] : [];

        foreach ($conditions as $alias => [$condition, $conditionBindings]) {
            $select[] = $this->countWhen($condition).' as '.$alias;
            $bindings = array_merge($bindings, $conditionBindings);
        }

        $select[] = 'SUM(CASE WHEN '.$revenue[0].' THEN orders.total_amount ELSE 0 END) as revenue';
        $bindings = array_merge($bindings, $revenue[1]);

        return $query
            ->selectRaw(implode(', ', $select), $bindings)
            ->groupBy($groupKey)
            ->toBase()
            ->get()
            ->map(fn ($row) => $this->normalize($row))
            ->all();
    }

    /**
     * @param  object{membership_id: ?string, assigned: int|numeric-string, confirmed: int|numeric-string, pending: int|numeric-string, canceled: int|numeric-string, delivered: int|numeric-string, returned: int|numeric-string, revenue: int|float|numeric-string}  $row
     * @return array<string, mixed>
     */
    private function normalize(object $row): array
    {
        $assigned = (int) $row->assigned;
        $confirmed = (int) $row->confirmed;
        $pending = (int) $row->pending;
        $canceled = (int) $row->canceled;
        $delivered = (int) $row->delivered;
        $returned = (int) $row->returned;

        return [
            'membership_id' => $row->membership_id,
            'assigned' => $assigned,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'canceled' => $canceled,
            // Custom statuses (no_answer_*) fall in the remainder, which can
            // never be negative.
            'other' => max(0, $assigned - $confirmed - $pending - $canceled),
            'delivered' => $delivered,
            'returned' => $returned,
            'in_progress' => max(0, $assigned - $delivered - $returned),
            'revenue' => (float) $row->revenue,
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
