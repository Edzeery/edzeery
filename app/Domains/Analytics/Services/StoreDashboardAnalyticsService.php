<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\DashboardFilterFactory;
use App\Domains\Analytics\Support\DashboardFilterOptions;
use App\Domains\Analytics\Support\DashboardOrderScope;
use App\Domains\Analytics\Support\DashboardSeriesQuery;
use App\Domains\Analytics\Support\DashboardSummaryQuery;
use App\Domains\Analytics\Support\OrderStatusChartMapper;
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

    /** @var array<string, string|null>|null key => id for type='order' statuses */
    private ?array $orderStatusIdMap = null;

    public function __construct(
        ?string $storeId = null,
        private ?DashboardOrderScope $scope = null,
        private ?DashboardFilterOptions $options = null,
        private ?DashboardSummaryQuery $summaryQuery = null,
        private ?DashboardSeriesQuery $seriesQuery = null
    ) {
        $this->storeId = $storeId ?? currentStoreId();
        $this->scope = $this->scope ?? app(DashboardOrderScope::class);
        $this->options = $this->options ?? app(DashboardFilterOptions::class);
        $this->summaryQuery = $this->summaryQuery ?? app(DashboardSummaryQuery::class);
        $this->seriesQuery = $this->seriesQuery ?? app(DashboardSeriesQuery::class);
    }

    public function summary(?DashboardFilter $filter = null): array
    {
        $result = $this->summaryQuery->run(
            $this->baseOrdersQuery(),
            $filter ?? app(DashboardFilterFactory::class)->make([], null),
            fn ($s) => $this->statusId($s),
            fn ($s) => $this->statusIds($s)
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
        $filter ??= app(\App\Domains\Analytics\Support\DashboardFilterFactory::class)->make([], null);
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
        $filter ??= app(\App\Domains\Analytics\Support\DashboardFilterFactory::class)->make([], null);
        $res = $this->seriesQuery->salesSeries($this->baseOrdersQuery(), $filter, $this->statusIdResolver());
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
        $filter ??= app(\App\Domains\Analytics\Support\DashboardFilterFactory::class)->make([], null);

        return $this->seriesQuery->salesSeries($this->baseOrdersQuery(), $filter, $this->statusIdResolver());
    }

    public function ordersByState(?DashboardFilter $filter = null): Collection
    {
        $filter ??= app(\App\Domains\Analytics\Support\DashboardFilterFactory::class)->make([], null);
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
        $filter ??= app(\App\Domains\Analytics\Support\DashboardFilterFactory::class)->make([], null);
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
        $filter ??= app(\App\Domains\Analytics\Support\DashboardFilterFactory::class)->make([], null);
        $query = DB::table('orders')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.store_id', $this->storeId)
            ->whereNull('orders.deleted_at')
            ->where('orders.status_id', $this->statusId(OrderStatus::PENDING));

        $this->scope->applyPending($query, $filter);

        return $query
            ->select('orders.id', 'orders.number', 'orders.total_amount', 'orders.created_at', 'customers.name as customer_name', 'customers.phone as customer_phone')
            ->orderByDesc('orders.created_at')
            ->limit($limit)
            ->get();
    }

    public function topSellingProducts(int $limit = 5): Collection
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('orders.store_id', $this->storeId)
            ->whereNull('orders.deleted_at')
            ->where('orders.created_at', '>=', $startOfMonth)
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

    private function statusIdsByKey(): array
    {
        // One lookup per request instead of one DB query per statusId() call
        // (the service runs ~5 status lookups on every dashboard render). Kept
        // on the instance, not in a static, so a long-lived worker cannot serve
        // status ids from an earlier request.
        if ($this->orderStatusIdMap === null) {
            $this->orderStatusIdMap = DB::table('statuses')
                ->where('type', 'order')
                ->pluck('id', 'key')
                ->all();
        }

        return $this->orderStatusIdMap;
    }

    private function statusId(OrderStatus $status): ?string
    {
        return $this->statusIdsByKey()[$status->value] ?? null;
    }

    /**
     * The series query resolves the delivered status through the same lookup
     * the summary uses, instead of keeping its own copy of the map.
     *
     * @return callable(OrderStatus): ?string
     */
    private function statusIdResolver(): callable
    {
        return fn (OrderStatus $status) => $this->statusId($status);
    }

    private function statusIds(array $statuses): array
    {
        $map = $this->statusIdsByKey();
        $ids = [];

        foreach ($statuses as $status) {
            $id = $map[$status->value] ?? null;

            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $ids;
    }
}
