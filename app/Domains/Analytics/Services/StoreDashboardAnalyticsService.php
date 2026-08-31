<?php

namespace App\Domains\Analytics\Services;

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

    public function __construct(?string $storeId = null)
    {
        $this->storeId = $storeId ?? currentStoreId();
    }

    public function summary(): array
    {
        $now = Carbon::now();
        $startOfPeriod = $now->copy()->startOfMonth();
        $startOfPrev = $startOfPeriod->copy()->subMonth();

        $orders = $this->baseOrdersQuery();
        $currentOrders = (clone $orders)->where('created_at', '>=', $startOfPeriod);
        $prevOrders = (clone $orders)->where('created_at', '>=', $startOfPrev)->where('created_at', '<', $startOfPeriod);

        $totalCurrent = (clone $currentOrders)->count();
        $totalPrev = (clone $prevOrders)->count();
        $totalChange = $totalPrev > 0 ? round((($totalCurrent - $totalPrev) / $totalPrev) * 100) : 0;

        $revenueCurrent = (clone $currentOrders)->where('status_id', $this->statusId(OrderStatus::DELIVERED))->sum('total_amount');
        $revenuePrev = (clone $prevOrders)->where('status_id', $this->statusId(OrderStatus::DELIVERED))->sum('total_amount');

        $confirmedCount = (clone $currentOrders)->whereIn('status_id', $this->statusIds([
            OrderStatus::CONFIRMED, OrderStatus::PREPARING, OrderStatus::SHIPPED,
            OrderStatus::IN_TRANSIT, OrderStatus::OUT_FOR_DELIVERY, OrderStatus::DELIVERED, OrderStatus::COMPLETED,
        ]))->count();

        $confirmationRate = $totalCurrent > 0 ? round(($confirmedCount / $totalCurrent) * 100) : 0;

        $returnedCount = (clone $currentOrders)->where('status_id', $this->statusId(OrderStatus::RETURNED))->count();
        $deliveredCount = (clone $currentOrders)->where('status_id', $this->statusId(OrderStatus::DELIVERED))->count();
        $returnRate = ($deliveredCount + $returnedCount) > 0
            ? round(($returnedCount / ($deliveredCount + $returnedCount)) * 100)
            : 0;

        $totalProducts = Product::query()->where('store_id', $this->storeId)->count();
        $activeProducts = Product::query()->where('store_id', $this->storeId)->where('is_active', true)->count();
        $totalMembers = StoreMembership::query()
            ->where('store_id', $this->storeId)
            ->where('is_active', true)
            ->distinct('user_id')
            ->count('user_id');

        return [
            'total_orders' => $totalCurrent,
            'total_orders_change' => $totalChange,
            'revenue' => $revenueCurrent,
            'revenue_prev' => $revenuePrev,
            'confirmation_rate' => $confirmationRate,
            'return_rate' => $returnRate,
            'aov' => $confirmedCount > 0 ? round($revenueCurrent / max($confirmedCount, 1), 2) : 0,
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'total_members' => $totalMembers,
        ];
    }

    public function ordersByStatus(): Collection
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return DB::table('orders')
            ->join('statuses', 'statuses.id', '=', 'orders.status_id')
            ->where('orders.store_id', $this->storeId)
            ->whereNull('orders.deleted_at')
            ->where('orders.created_at', '>=', $startOfMonth)
            ->select('statuses.key', 'statuses.color', DB::raw('COUNT(*) as count'))
            ->groupBy('statuses.key', 'statuses.color')
            ->orderByDesc('count')
            ->get();
    }

    public function salesByDay(): Collection
    {
        $days = Carbon::now()->subDays(29)->startOfDay();

        return DB::table('orders')
            ->where('store_id', $this->storeId)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $days)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status_id = ? THEN total_amount ELSE 0 END) as revenue')
            )
            ->addBinding([$this->statusId(OrderStatus::DELIVERED)], 'select')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
    }

    public function ordersByState(): Collection
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return DB::table('orders')
            ->join('states', 'states.id', '=', 'orders.state_id')
            ->where('orders.store_id', $this->storeId)
            ->whereNull('orders.deleted_at')
            ->where('orders.created_at', '>=', $startOfMonth)
            ->select('states.name', DB::raw('COUNT(*) as count'), DB::raw('SUM(orders.total_amount) as revenue'))
            ->groupBy('states.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
    }

    public function deliveryTypeBreakdown(): Collection
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return DB::table('orders')
            ->where('store_id', $this->storeId)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $startOfMonth)
            ->select('delivery_type', DB::raw('COUNT(*) as count'))
            ->groupBy('delivery_type')
            ->get();
    }

    public function pendingConfirmationOrders(int $limit = 5): Collection
    {
        return DB::table('orders')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.store_id', $this->storeId)
            ->whereNull('orders.deleted_at')
            ->where('orders.status_id', $this->statusId(OrderStatus::PENDING))
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

    private function statusId(OrderStatus $status): ?string
    {
        return DB::table('statuses')
            ->where('key', $status->value)
            ->where('type', 'order')
            ->value('id');
    }

    private function statusIds(array $statuses): array
    {
        return DB::table('statuses')
            ->whereIn('key', array_map(fn (OrderStatus $s) => $s->value, $statuses))
            ->where('type', 'order')
            ->pluck('id')
            ->toArray();
    }
}
