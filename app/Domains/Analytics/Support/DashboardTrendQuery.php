<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Status\StatusResolver;
use App\Enums\Store\OrderStatus;
use App\Models\Orders\Order;

/**
 * PHASE 37-K — the trend chart behind the dashboard, per stats view.
 *
 * Confirmation plots every order in the window: received, confirmed, cured
 * of nothing — received is COUNT(*), the others are conditional sums. Delivery
 * plots only shipped orders (the doughnut's cohort), because a delivered
 * revenue line over a window that includes never-shipped orders would not be
 * the number the view leads with. Both views come from one bucketed query, so
 * the lines of one chart can never disagree about where a bucket starts.
 */
final class DashboardTrendQuery
{
    /** Token the chart script resolves against the theme's accent colour. */
    public const ACCENT_TOKEN = 'token:accent';

    public function __construct(
        private DashboardOrderScope $scope,
        private DashboardSeriesQuery $seriesQuery,
    ) {}

    /**
     * @param  callable(OrderStatus): ?string  $statusId
     * @param  callable(array<int, OrderStatus>): array<int, string>  $statusIds
     * @return array{view: 'confirmation'|'delivery', labels: array<int, string>, series: list<array{key: string, label: string, hex: string, axis: 'y'|'y1', type: 'line'|'bar', values: array<int, int|float>}>}
     */
    public function run(DashboardFilter $filter, callable $statusId, callable $statusIds): array
    {
        $delivery = $filter->memberDimension === 'delivery';

        $query = Order::query()->where('store_id', $filter->storeId)->whereNull('deleted_at');

        if ($delivery) {
            // D3: the delivery cohort is orders with a tracking row, counted
            // once however many attempts they have.
            $this->scope->trackedOnly($query, $filter);
        }

        $this->scope->apply($query, $filter);

        return $delivery
            ? $this->delivery($query, $filter, $statusId, $statusIds)
            : $this->confirmation($query, $filter, $statusId, $statusIds);
    }

    /** @return array{view: 'confirmation', labels: array<int, string>, series: list<array<string, mixed>>} */
    private function confirmation($query, DashboardFilter $filter, callable $statusId, callable $statusIds): array
    {
        $confirmedIds = $statusIds(DashboardStatusGroups::CONFIRMED);
        $canceledIds = $statusIds(DashboardStatusGroups::CANCELED);

        $result = $this->seriesQuery->series($query, $filter, [
            ['key' => 'received', 'expression' => 'COUNT(*)'],
            ['key' => 'confirmed', 'expression' => $this->countWhen($this->in('orders.status_id', $confirmedIds)), 'bindings' => $confirmedIds],
            ['key' => 'canceled', 'expression' => $this->countWhen($this->in('orders.status_id', $canceledIds)), 'bindings' => $canceledIds],
        ]);

        return $this->assemble('confirmation', $result, [
            ['key' => 'received', 'label' => __('dashboard.series_received'), 'hex' => self::ACCENT_TOKEN, 'axis' => 'y', 'type' => 'line'],
            ['key' => 'confirmed', 'label' => __('dashboard.series_confirmed'), 'hex' => $this->hex($filter->storeId, OrderStatus::CONFIRMED), 'axis' => 'y', 'type' => 'line'],
            ['key' => 'canceled', 'label' => __('dashboard.series_canceled'), 'hex' => $this->hex($filter->storeId, OrderStatus::CANCELED, OrderStatus::CANCELLED), 'axis' => 'y', 'type' => 'line'],
        ]);
    }

    /** @return array{view: 'delivery', labels: array<int, string>, series: list<array<string, mixed>>} */
    private function delivery($query, DashboardFilter $filter, callable $statusId, callable $statusIds): array
    {
        $deliveredIds = $statusIds(DashboardStatusGroups::DELIVERED);
        $returnedIds = $statusIds(DashboardStatusGroups::RETURNED);

        $result = $this->seriesQuery->series($query, $filter, [
            ['key' => 'delivered', 'expression' => $this->countWhen($this->in('orders.status_id', $deliveredIds)), 'bindings' => $deliveredIds],
            ['key' => 'returned', 'expression' => $this->countWhen($this->in('orders.status_id', $returnedIds)), 'bindings' => $returnedIds],
            ['key' => 'revenue', 'expression' => 'SUM(CASE WHEN '.$this->in('orders.status_id', $deliveredIds).' THEN orders.total_amount ELSE 0 END)', 'bindings' => $deliveredIds, 'cast' => 'float'],
        ]);

        return $this->assemble('delivery', $result, [
            ['key' => 'delivered', 'label' => __('dashboard.series_delivered'), 'hex' => $this->hex($filter->storeId, OrderStatus::DELIVERED), 'axis' => 'y', 'type' => 'bar'],
            ['key' => 'returned', 'label' => __('dashboard.series_returned'), 'hex' => $this->hex($filter->storeId, OrderStatus::RETURNED), 'axis' => 'y', 'type' => 'bar'],
            ['key' => 'revenue', 'label' => __('dashboard.series_revenue'), 'hex' => self::ACCENT_TOKEN, 'axis' => 'y1', 'type' => 'line'],
        ]);
    }

    /**
     * @param  array{labels: array<int, string>, values: array<string, array<int, int|float>>}  $result
     * @param  list<array{key: string, label: string, hex: string, axis: string, type: string}>  $defs
     * @return array{view: string, labels: array<int, string>, series: list<array<string, mixed>>}
     */
    private function assemble(string $view, array $result, array $defs): array
    {
        $series = [];

        foreach ($defs as $def) {
            $series[] = $def + ['values' => $result['values'][$def['key']] ?? []];
        }

        return ['view' => $view, 'labels' => $result['labels'], 'series' => $series];
    }

    /**
     * A condition plus its bindings, or a condition that matches nothing when
     * the store has not defined any status of the group.
     *
     * @param  array<int, string>  $ids
     */
    private function in(string $column, array $ids): string
    {
        return $ids === []
            ? '1 = 0'
            : $column.' in ('.implode(', ', array_fill(0, count($ids), '?')).')';
    }

    /**
     * One bucketed count of the rows meeting the condition. The condition has
     * to sit inside an aggregate: a bare `status_id in (?)` in a grouped
     * SELECT makes every bucket report one row's boolean instead of a count.
     */
    private function countWhen(string $condition): string
    {
        return 'SUM(CASE WHEN '.$condition.' THEN 1 ELSE 0 END)';
    }

    /**
     * The stored colour of a status, resolved once per key through the same
     * resolver <x-status> reads. Stores configure cancelled under either
     * spelling, so both are tried before the kit fallback.
     */
    private function hex(string $storeId, OrderStatus ...$statuses): string
    {
        foreach ($statuses as $status) {
            $resolved = StatusResolver::resolve('order', $status->value, $storeId);

            if ($resolved->source === 'db') {
                return $resolved->hex ?: '#9ca3af';
            }
        }

        return StatusResolver::resolve('order', $statuses[0]->value, $storeId)->hex ?: '#9ca3af';
    }
}
