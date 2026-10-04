<?php

use App\Domains\Analytics\Services\StoreDashboardAnalyticsService;
use App\Domains\Analytics\Support\DashboardFilterFactory;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\CarbonImmutable;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = \App\Models\User::query()->create([
        'name' => 'Test User',
        'email' => 'test-' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->store = Store::query()->create([
        'name' => 'Test Store',
        'slug' => 'test-store-' . uniqid(),
        'domain' => null,
        'currency_code' => 'SAR',
        'locale' => 'ar',
        'timezone' => 'Asia/Riyadh',
        'is_active' => true,
        'user_id' => $this->user->id,
    ]);
});

it('runs analytics methods with default filter', function () {
    Order::query()->create([
        'store_id' => $this->store->id,
        'number' => 'ORD-001',
        'total_amount' => 100,
        'status_id' => \App\Models\Status::query()->where('type', 'order')->first()?->id,
        'customer_id' => null,
        'shipping_provider_id' => null,
        'delivery_type' => 'pickup',
        'state_id' => null,
    ]);

    $service = new StoreDashboardAnalyticsService($this->store->id);
    $factory = app(DashboardFilterFactory::class);
    $filter = $factory->make([], null);

    $summary = $service->summary($filter);
    expect($summary)->toBeArray()->toHaveKeys(['total_orders', 'revenue']);

    $byStatus = $service->ordersByStatus($filter);
    expect($byStatus)->toBeInstanceOf(\Illuminate\Support\Collection::class);

    $series = $service->salesSeries($filter);
    expect($series)->toBeArray()->toHaveKeys(['labels', 'revenue', 'orders']);

    $byState = $service->ordersByState($filter);
    expect($byState)->toBeInstanceOf(\Illuminate\Support\Collection::class);

    $delivery = $service->deliveryTypeBreakdown($filter);
    expect($delivery)->toBeInstanceOf(\Illuminate\Support\Collection::class);

    // Named argument: topSellingProducts() takes the filter first, like every
    // other block, so the limit must not be passed positionally.
    $top = $service->topSellingProducts(limit: 5);
    expect($top)->toBeInstanceOf(\Illuminate\Support\Collection::class);

    $pending = $service->pendingConfirmationOrders($filter, 5);
    expect($pending)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

it('runs analytics methods with custom range', function () {
    $now = CarbonImmutable::now();
    $service = new StoreDashboardAnalyticsService($this->store->id);
    $factory = app(DashboardFilterFactory::class);
    $filter = $factory->make([
        'period' => 'custom',
        'dateFrom' => $now->subDays(5)->format('Y-m-d'),
        'dateTo' => $now->format('Y-m-d'),
    ], null);

    expect(fn () => $service->summary($filter))->not->toThrow(\Throwable::class);
    expect(fn () => $service->ordersByStatus($filter))->not->toThrow(\Throwable::class);
    expect(fn () => $service->salesSeries($filter))->not->toThrow(\Throwable::class);
    expect(fn () => $service->ordersByState($filter))->not->toThrow(\Throwable::class);
    expect(fn () => $service->deliveryTypeBreakdown($filter))->not->toThrow(\Throwable::class);
    expect(fn () => $service->pendingConfirmationOrders($filter, 5))->not->toThrow(\Throwable::class);
});
