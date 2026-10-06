<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Enums\Store\OrderStatus;
use Illuminate\Database\Eloquent\Builder;

final class DashboardSummaryQuery
{
    public function __construct(private DashboardOrderScope $scope) {}

    /**
     * @param  Builder<\App\Models\Orders\Order>  $baseQuery
     * @param  callable(OrderStatus): ?string  $statusId
     * @param  callable(array<int, OrderStatus>): array<int, string>  $statusIds
     * @return array<string, int|float|bool>
     */
    public function run(Builder $baseQuery, DashboardFilter $filter, callable $statusId, callable $statusIds): array
    {
        $deliveredId = $statusId(OrderStatus::DELIVERED);

        $current = (clone $baseQuery);
        $this->scope->apply($current, $filter);

        $totalCurrent = (clone $current)->count();
        $revenueCurrent = (clone $current)->where('status_id', $deliveredId)->sum('total_amount');
        $deliveredCount = (clone $current)->where('status_id', $deliveredId)->count();

        // The previous window reuses the exact same scoping rules, so a member
        // or carrier filter is applied to both sides of the comparison.
        $previous = $filter->previous();
        $totalPrev = 0;
        $revenuePrev = 0;

        if ($previous !== null) {
            $prev = (clone $baseQuery);
            $this->scope->apply($prev, $previous);

            $totalPrev = (clone $prev)->count();
            $revenuePrev = (clone $prev)->where('status_id', $deliveredId)->sum('total_amount');
        }

        $confirmedCount = (clone $current)->whereIn('status_id', $statusIds(DashboardStatusGroups::CONFIRMED))->count();

        $confirmationRate = $totalCurrent > 0 ? round(($confirmedCount / $totalCurrent) * 100) : 0;

        $returnedCount = (clone $current)->where('status_id', $statusId(OrderStatus::RETURNED))->count();
        $returnRate = ($deliveredCount + $returnedCount) > 0
            ? round(($returnedCount / ($deliveredCount + $returnedCount)) * 100)
            : 0;

        // The Confirmation view leads with the two states an operator acts on.
        // Both spellings of cancelled are counted, because stores configure
        // either key and the doughnut already treats them as one alias.
        $pendingCount = (clone $current)->whereIn('status_id', $statusIds(DashboardStatusGroups::PENDING))->count();
        $canceledCount = (clone $current)
            ->whereIn('status_id', $statusIds(DashboardStatusGroups::CANCELED))
            ->count();

        return [
            'total_orders' => $totalCurrent,
            'total_orders_change' => $this->change($totalCurrent, $totalPrev, $previous !== null),
            'revenue' => $revenueCurrent,
            'revenue_change' => $this->change($revenueCurrent, $revenuePrev, $previous !== null),
            'has_previous' => $previous !== null,
            'confirmation_rate' => $confirmationRate,
            'return_rate' => $returnRate,
            // Average value of a delivered order, not of a confirmed one.
            'aov' => $deliveredCount > 0 ? round($revenueCurrent / $deliveredCount, 2) : 0,
            'pending_count' => $pendingCount,
            'canceled_count' => $canceledCount,
        ];
    }

    private function change(int|float $current, int|float $previous, bool $comparable): int
    {
        if (! $comparable || $previous <= 0) {
            return 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }
}
