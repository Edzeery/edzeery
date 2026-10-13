<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\DashboardConfirmationTeam;
use App\Domains\Analytics\Support\DashboardFilterFactory;
use App\Domains\Analytics\Support\DashboardOrderScope;
use App\Domains\Analytics\Support\DashboardStatusBreakdown;
use App\Domains\Analytics\Support\DashboardSummaryQuery;
use App\Domains\Analytics\Support\DashboardTeamPerformance;
use App\Domains\Analytics\Support\DashboardTeamPerformanceQuery;
use App\Domains\Analytics\Support\DashboardTrendQuery;
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

    private StoreConfirmationReportReader $confirmation;

    public function __construct(
        ?string $storeId = null,
        private ?DashboardOrderScope $scope = null,
        private ?OrderStatusIdMap $statusIds = null,
        private ?DashboardSummaryQuery $summaryQuery = null,
    ) {
        $this->storeId = $storeId ?? currentStoreId();
        $this->scope = $this->scope ?? app(DashboardOrderScope::class);
        $this->statusIds = $this->statusIds ?? app(OrderStatusIdMap::class);
        $this->summaryQuery = $this->summaryQuery ?? app(DashboardSummaryQuery::class);
        $this->confirmation = app(StoreConfirmationReportReader::class);
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

    /**
     * The team table: two aggregate queries (credit + workload), presented for
     * the active view.
     *
     * @param  Collection<int, object{id: string, name: string}>  $members
     * @return list<array<string, mixed>>
     */
    public function teamPerformance(?DashboardFilter $filter, Collection $members): array
    {
        $filter ??= app(DashboardFilterFactory::class)->make([], null);
        $teamQuery = app(DashboardTeamPerformanceQuery::class);
        $rows = $teamQuery->run(
            $this->baseOrdersQuery(withTrashed: true),
            $filter,
            $this->statusIds->resolver(),
            $this->statusIds->ids(...)
        );

        return app(DashboardTeamPerformance::class)->present($rows['credit'], $rows['work'], $filter, $members);
    }

    /**
     * PHASE 37-K — the status doughnut for the active stats view. One slice
     * per status in Confirmation (the confirmed group collapsed), one per
     * status of the shipped orders in Delivery, with integer percentages.
     */
    public function statusBreakdown(?DashboardFilter $filter = null): Collection
    {
        $filter ??= app(DashboardFilterFactory::class)->make([], null);

        $breakdown = app(DashboardStatusBreakdown::class);

        // D2: the confirmation doughnut is served from the same report the
        // cards and the team read, so it can never disagree with the rate.
        if ($filter->memberDimension !== 'delivery') {
            return $breakdown->forConfirmation($this->confirmation->report($filter)['groups']);
        }

        return $breakdown->run($filter, $this->storeId);
    }

    /**
     * PHASE 37-K — the trend lines/bars for the active stats view, built from
     * one bucketed query: received/confirmed/canceled for Confirmation,
     * delivered/returned/revenue for Delivery.
     */
    public function trendSeries(?DashboardFilter $filter = null): array
    {
        $filter ??= app(DashboardFilterFactory::class)->make([], null);

        return app(DashboardTrendQuery::class)->run(
            $filter,
            fn ($s) => $this->statusIds->id($s),
            fn ($s) => $this->statusIds->ids($s)
        );
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
     * The confirmation tab (M4): the one report the cards, doughnut and rate read.
     *
     * @return array{groups: array<string, array{total: int, deleted: int}>, confirmed: int, denominator: int, rate: int, kind: string, excluded: int, outside: array<string, bool>}
     */
    public function confirmationStats(?DashboardFilter $filter = null): array
    {
        return $this->confirmation->report($filter ?? app(DashboardFilterFactory::class)->make([], null));
    }

    /**
     * The confirmation team section (M5): the same grouped read as the cards,
     * folded per membership (decision D5). A soft-deleted member is marked.
     *
     * @param  Collection<int, object{id: string, name: string, deleted?: bool}>  $members
     * @return list<array<string, mixed>>
     */
    public function confirmationTeam(?DashboardFilter $filter, Collection $members): array
    {
        $filter ??= app(DashboardFilterFactory::class)->make([], null);

        return app(DashboardConfirmationTeam::class)
            ->present($this->confirmation->perMember($filter), $filter, $members);
    }

    /**
     * Ranked by quantity sold inside the scoped window; the current month is
     * kept only when no filter is given.
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

    private function baseOrdersQuery(bool $withTrashed = false): \Illuminate\Database\Eloquent\Builder
    {
        $query = Order::query()->where('store_id', $this->storeId);

        return $withTrashed ? $query->withTrashed() : $query->whereNull('deleted_at');
    }
}
