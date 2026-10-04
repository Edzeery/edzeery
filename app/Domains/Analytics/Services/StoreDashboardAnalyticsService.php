<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\DashboardFilterFactory;
use App\Domains\Analytics\Support\DashboardOrderScope;
use App\Domains\Analytics\Support\DashboardSeriesQuery;
use App\Domains\Analytics\Support\DashboardSummaryQuery;
use App\Domains\Analytics\Support\OrderStatusChartMapper;
use App\Domains\Analytics\Support\OrderStatusIdMap;
use App\Enums\Store\OrderStatus;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StoreDashboardAnalyticsService
{
    private string $storeId;

    public function __construct(
        ?string $storeId = null,
        private ?DashboardOrderScope $scope = null,
        private ?OrderStatusIdMap $statusIds = null,
        private ?DashboardSummaryQuery $summaryQuery = null,
        private ?DashboardSeriesQuery $seriesQuery = null
    ) {
        $this->storeId = $storeId ?? currentStoreId();
        $this->scope = $this->scope ?? app(DashboardOrderScope::class);
        $this->statusIds = $this->statusIds ?? app(OrderStatusIdMap::class);
        $this->summaryQuery = $this->summaryQuery ?? app(DashboardSummaryQuery::class);
        $this->seriesQuery = $this->seriesQuery ?? app(DashboardSeriesQuery::class);
    }

    public function summary(?DashboardFilter $filter = null): array
    {
        $result = $this->summaryQuery->run(
            $this->baseOrdersQuery(),
            $filter ?? app(DashboardFilterFactory::class)->make([], null),
            fn ($s) => $this->statusIds->id($s),
            fn ($s) => $this->statusIds->ids($s)
        );

        $totalProducts = Product::query()->where('store_id', $this->storeId)->count();
        $activeProducts = Product::query()->where('store_id', $this->storeId)->where('is_active', true)->count();
        $totalMembers = StoreMembership::query()
            ->where('store_id', $this->storeId)
            ->where('is_active', true)
            ->distinct('user_id')
            ->count('user_id');

        $result['total_products'] = $totalProducts;
        $result['active_products'] = $activeProducts;
        $result['total_members'] = $totalMembers;

        return $result;
    }

    public function ordersByStatus(?DashboardFilter $filter = null): Collection
    {
        $filter ??= app(DashboardFilterFactory::class)->make([], null);
        $query = DB::table('orders')
            ->join('statuses', 'statuses.id', '=', 'orders.status_id')
            ->where('orders.store_id', $this->storeId)
            ->whereNull('orders.deleted_at');

        $this->scope->apply($query, $filter);

        $rows = $query
            ->select('statuses.key', DB::raw('COUNT(*) as count'))
            ->groupBy('statuses.key')
            ->orderByDesc('count')
            ->get();

        $mapper = app(OrderStatusChartMapper::class);

        return $mapper->map($rows, $this->storeId);
    }

    public function salesByDay(?DashboardFilter $filter = null): Collection
    {
        $filter ??= app(DashboardFilterFactory::class)->make([], null);
        $res = $this->seriesQuery->salesSeries($this->baseOrdersQuery(), $filter, $this->statusIds->resolver());
        $trend = collect();
        $labels = $res['labels'];
        $revenue = $res['revenue'];
        $orders = $res['orders'];
        $count = count($labels);
        for ($i = 0; $i < $count; $i++) {
            $trend->push((object) [
                'date' => $labels[$i],
                'revenue' => (float) ($revenue[$i] ?? 0),
                'orders' => (int) ($orders[$i] ?? 0),
            ]);
        }

        return $trend;
    }

    public function salesSeries(?DashboardFilter $filter = null): array
    {
        $filter ??= app(DashboardFilterFactory::class)->make([], null);

        return $this->seriesQuery->salesSeries($this->baseOrdersQuery(), $filter, $this->statusIds->resolver());
    }

    public function ordersByState(?DashboardFilter $filter = null): Collection
    {
        $filter ??= app(DashboardFilterFactory::class)->make([], null);
        $query = DB::table('orders')
            ->join('states', 'states.id', '=', 'orders.state_id')
            ->where('orders.store_id', $this->storeId)
            ->whereNull('orders.deleted_at');

        $this->scope->apply($query, $filter);

        return $query
            ->select('states.name', DB::raw('COUNT(*) as count'), DB::raw('SUM(orders.total_amount) as revenue'))
            ->groupBy('states.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
    }

    public function deliveryTypeBreakdown(?DashboardFilter $filter = null): Collection
    {
        $filter ??= app(DashboardFilterFactory::class)->make([], null);
        $query = DB::table('orders')
            ->where('store_id', $this->storeId)
            ->whereNull('deleted_at');

        $this->scope->apply($query, $filter);

        return $query
            ->select('delivery_type', DB::raw('COUNT(*) as count'))
            ->groupBy('delivery_type')
            ->get();
    }

    public function pendingConfirmationOrders(?DashboardFilter $filter = null, int $limit = 5): Collection
    {
        $filter ??= app(DashboardFilterFactory::class)->make([], null);
        $query = DB::table('orders')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.store_id', $this->storeId)
            ->whereNull('orders.deleted_at')
            ->where('orders.status_id', $this->statusIds->id(OrderStatus::PENDING));

        $this->scope->applyPending($query, $filter);

        return $query
            ->select('orders.id', 'orders.number', 'orders.total_amount', 'orders.created_at', 'customers.name as customer_name', 'customers.phone as customer_phone')
            ->orderByDesc('orders.created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Ranked by quantity sold inside the scoped window.
     *
     * This used to hard-code "since the start of the current month" on the app
     * clock, which made the card disagree with every other block on a filtered
     * dashboard. Without a filter the original current-month window is kept.
     */
    public function topSellingProducts(?DashboardFilter $filter = null, int $limit = 5): Collection
    {
        $query = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('orders.store_id', $this->storeId)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_items.deleted_at');

        if ($filter) {
            $this->scope->apply($query, $filter);
        } else {
            $query->where('orders.created_at', '>=', Carbon::now()->startOfMonth());
        }

        return $query
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    public function lowStockVariants(int $limit = 5): Collection
    {
        return ProductVariant::query()
            ->where('store_id', $this->storeId)
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->with('product', 'optionValues')
            ->orderBy('stock')
            ->take($limit)
            ->get();
    }

    private function baseOrdersQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Order::query()->where('store_id', $this->storeId)->whereNull('deleted_at');
    }
}
