<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Enums\Store\OrderStatus;
use Illuminate\Database\Eloquent\Builder;

final class DashboardSummaryQuery
{
    public function __construct(
        private DashboardOrderScope $scope,
        private DashboardFilterOptions $options
    ) {}

    /**
     * @param  Builder<\App\Models\Orders\Order>  $baseQuery
     */
    public function run(Builder $baseQuery, DashboardFilter $filter, callable $statusId, callable $statusIds): array
    {
        $current = (clone $baseQuery);
        $this->scope->apply($current, $filter);

        $prev = (clone $baseQuery);
        if ($filter->previousFrom && $filter->previousTo) {
            $prev->whereBetween('orders.created_at', [
                $filter->previousFrom->toDateTimeString(),
                $filter->previousTo->toDateTimeString(),
            ]);
            if ($filter->carrierId) {
                $prev->where('orders.shipping_provider_id', $filter->carrierId);
            }
            if ($filter->memberId && $filter->memberDimension && ! empty($filter->allowedMembershipIds)) {
                if (in_array($filter->memberId, $filter->allowedMembershipIds, true)) {
                    if ($filter->memberDimension === 'confirmation') {
                        $prev->where('orders.assigned_to_membership_id', $filter->memberId);
                    } elseif ($filter->memberDimension === 'delivery') {
                        $prev->whereExists(function ($sub) use ($filter) {
                            $sub->selectRaw('1')
                                ->from('order_trackings')
                                ->whereColumn('order_trackings.order_id', 'orders.id')
                                ->where('order_trackings.store_id', $filter->storeId)
                                ->where('order_trackings.assigned_to_membership_id', $filter->memberId);
                        });
                    }
                }
            }
        } else {
            $prev->whereRaw('1=0');
        }

        $totalCurrent = (clone $current)->count();
        $totalPrev = (clone $prev)->count();
        $totalChange = $totalPrev > 0 ? round((($totalCurrent - $totalPrev) / $totalPrev) * 100) : 0;

        $revenueCurrent = (clone $current)->where('status_id', $statusId(OrderStatus::DELIVERED))->sum('total_amount');
        $revenuePrev = (clone $prev)->where('status_id', $statusId(OrderStatus::DELIVERED))->sum('total_amount');

        $confirmedCount = (clone $current)->whereIn('status_id', $statusIds([
            OrderStatus::CONFIRMED, OrderStatus::PREPARING, OrderStatus::SHIPPED,
            OrderStatus::IN_TRANSIT, OrderStatus::OUT_FOR_DELIVERY, OrderStatus::DELIVERED, OrderStatus::COMPLETED,
        ]))->count();

        $confirmationRate = $totalCurrent > 0 ? round(($confirmedCount / $totalCurrent) * 100) : 0;

        $returnedCount = (clone $current)->where('status_id', $statusId(OrderStatus::RETURNED))->count();
        $deliveredCount = (clone $current)->where('status_id', $statusId(OrderStatus::DELIVERED))->count();
        $returnRate = ($deliveredCount + $returnedCount) > 0
            ? round(($returnedCount / ($deliveredCount + $returnedCount)) * 100)
            : 0;

        return [
            'total_orders' => $totalCurrent,
            'total_orders_change' => $totalChange,
            'revenue' => $revenueCurrent,
            'revenue_prev' => $revenuePrev,
            'confirmation_rate' => $confirmationRate,
            'return_rate' => $returnRate,
            'aov' => $confirmedCount > 0 ? round($revenueCurrent / max($confirmedCount, 1), 2) : 0,
        ];
    }
}
