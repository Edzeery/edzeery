<?php

use App\Enums\Store\OrderStatus;
use App\Models\Orders\Order;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

if (! function_exists('ordersQuery')) {
    /**
     * One core for every order helper: applies store_id, status (key-based),
     * delivery_type and created_at from/to filters. Unknown keys or an invalid
     * delivery_type throw; a date-only `to` is inclusive through end of day.
     */
    function ordersQuery(array $filters = []): Builder
    {
        $query = Order::query();

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'store_id':
                    if ($value !== null) {
                        $query->where('orders.store_id', $value);
                    }
                    break;

                case 'status':
                    if ($value !== null) {
                        $query->whereStatusKey($value);
                    }
                    break;

                case 'delivery_type':
                    if (! in_array($value, [Order::DELIVERY_HOME, Order::DELIVERY_STOPDESK], true)) {
                        throw new InvalidArgumentException("Invalid delivery_type filter [{$value}].");
                    }
                    $query->where('orders.delivery_type', $value);
                    break;

                case 'from':
                    if ($value !== null) {
                        $query->where('orders.created_at', '>=', ordersDateBoundary($value, false));
                    }
                    break;

                case 'to':
                    if ($value !== null) {
                        $query->where('orders.created_at', '<=', ordersDateBoundary($value, true));
                    }
                    break;

                default:
                    throw new InvalidArgumentException("Unknown order filter key [{$key}].");
            }
        }

        return $query;
    }
}

if (! function_exists('ordersDateBoundary')) {
    /**
     * Normalise a date filter to Carbon; a date-only value is widened to the
     * start of the day (lower bound) or its end (upper bound), so a date-only
     * `to` still includes everything recorded that day.
     */
    function ordersDateBoundary(CarbonInterface|string $value, bool $endOfDay): CarbonInterface
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            $date = Carbon::parse($value);

            return $endOfDay ? $date->endOfDay() : $date->startOfDay();
        }

        $date = $value instanceof CarbonInterface ? $value->copy() : Carbon::parse($value);

        if ($endOfDay && $date->format('H:i:s') === '00:00:00') {
            return $date->endOfDay();
        }

        return $date;
    }
}

if (! function_exists('AllOrders')) {
    function AllOrders()
    {
        return ordersQuery()->get();
    }
}

if (! function_exists('AllOrdersCount')) {
    function AllOrdersCount()
    {
        return ordersQuery()->count();
    }
}

if (! function_exists('AllOrdersByStatus')) {
    function AllOrdersByStatus(OrderStatus|array|null $status = null)
    {
        return ordersQuery(['status' => $status])->get();
    }
}

if (! function_exists('AllOrdersCountByStatus')) {
    function AllOrdersCountByStatus(OrderStatus|array|null $status = null)
    {
        return ordersQuery(['status' => $status])->count();
    }
}

if (! function_exists('AllOrdersByStore')) {
    function AllOrdersByStore($storeId)
    {
        return ordersQuery(['store_id' => $storeId])->get();
    }
}

if (! function_exists('AllOrdersCountByStore')) {
    function AllOrdersCountByStore($storeId)
    {
        return ordersQuery(['store_id' => $storeId])->count();
    }
}

if (! function_exists('AllOrdersByStoreAndStatus')) {
    function AllOrdersByStoreAndStatus($storeId, OrderStatus|array|null $status = null)
    {
        return ordersQuery(['store_id' => $storeId, 'status' => $status])->get();
    }
}

if (! function_exists('AllOrdersCountByStoreAndStatus')) {
    function AllOrdersCountByStoreAndStatus($storeId, OrderStatus|array|null $status = null)
    {
        return ordersQuery(['store_id' => $storeId, 'status' => $status])->count();
    }
}

if (! function_exists('AllOrdersByStoreAndStatusAndDateRange')) {
    function AllOrdersByStoreAndStatusAndDateRange($storeId, OrderStatus|array|null $status = null, $startDate = null, $endDate = null)
    {
        return ordersQuery([
            'store_id' => $storeId,
            'status' => $status,
            'from' => $startDate,
            'to' => $endDate,
        ])->get();
    }
}

if (! function_exists('AllOrdersCountByStoreAndStatusAndDateRange')) {
    function AllOrdersCountByStoreAndStatusAndDateRange($storeId, OrderStatus|array|null $status = null, $startDate = null, $endDate = null)
    {
        return ordersQuery([
            'store_id' => $storeId,
            'status' => $status,
            'from' => $startDate,
            'to' => $endDate,
        ])->count();
    }
}

if (! function_exists('AllOrdersByStatusAndDateRange')) {
    function AllOrdersByStatusAndDateRange(OrderStatus|array|null $status = null, $startDate = null, $endDate = null)
    {
        return ordersQuery([
            'status' => $status,
            'from' => $startDate,
            'to' => $endDate,
        ])->get();
    }
}

if (! function_exists('AllOrdersCountByStatusAndDateRange')) {
    function AllOrdersCountByStatusAndDateRange(OrderStatus|array|null $status = null, $startDate = null, $endDate = null)
    {
        return ordersQuery([
            'status' => $status,
            'from' => $startDate,
            'to' => $endDate,
        ])->count();
    }
}

if (! function_exists('AllOrdersByDeliveryType')) {
    function AllOrdersByDeliveryType(string $type, ?string $storeId = null)
    {
        return ordersQuery(['delivery_type' => $type, 'store_id' => $storeId])->get();
    }
}

if (! function_exists('AllOrdersCountByDeliveryType')) {
    function AllOrdersCountByDeliveryType(string $type, ?string $storeId = null)
    {
        return ordersQuery(['delivery_type' => $type, 'store_id' => $storeId])->count();
    }
}

if (! function_exists('ordersRevenue')) {
    /**
     * Delivered-only revenue, matching DashboardSummaryQuery. Pass a `status`
     * filter to override the delivered default; DB-level SUM, never a load.
     */
    function ordersRevenue(array $filters = []): float
    {
        if (! array_key_exists('status', $filters)) {
            $filters['status'] = OrderStatus::DELIVERED;
        }

        return (float) ordersQuery($filters)->sum('total_amount');
    }
}

if (! function_exists('total_amount')) {
    function total_amount(): float
    {
        return ordersRevenue();
    }
}

if (! function_exists('orderStatusCounts')) {
    /**
     * One grouped query: status key => order count, honouring the optional
     * store and date scope, via statuses.key.
     *
     * @return array<string, int>
     */
    function orderStatusCounts(?string $storeId = null, $from = null, $to = null): array
    {
        $filters = array_filter([
            'store_id' => $storeId,
            'from' => $from,
            'to' => $to,
        ], static fn ($value): bool => $value !== null);

        return ordersQuery($filters)
            ->join('statuses', 'statuses.id', '=', 'orders.status_id')
            ->where('statuses.type', 'order')
            ->selectRaw('statuses.key as status_key, COUNT(*) as aggregate')
            ->groupBy('statuses.key')
            ->pluck('aggregate', 'status_key')
            ->map(static fn ($count): int => (int) $count)
            ->all();
    }
}
