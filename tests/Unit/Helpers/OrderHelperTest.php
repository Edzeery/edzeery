<?php

use App\Enums\Store\OrderStatus;
use App\Models\Orders\Order;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\User;
use Carbon\CarbonInterface;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(Tests\TestCase::class, RefreshDatabase::class);

/**
 * This file lives in tests/Unit, which tests/Pest.php does not bootstrap, so
 * it is bound to the app explicitly through uses().
 */
function edzeeryOrderStatusId(string $key): ?string
{
    return Status::query()->where('type', 'order')->where('key', $key)->value('id');
}

function edzeeryOrder(
    ?string $storeId,
    ?string $statusId,
    string $deliveryType = Order::DELIVERY_HOME,
    float $total = 100.0,
    CarbonInterface|string|null $createdAt = null,
): Order {
    $order = new Order([
        'store_id' => $storeId,
        'status_id' => $statusId,
        'number' => (string) Str::ulid(),
        'total_amount' => $total,
        'delivery_type' => $deliveryType,
    ]);

    if ($createdAt !== null) {
        $order->created_at = $createdAt;
    }

    $order->save();

    return $order;
}

beforeEach(function () {
    $this->seed(SystemStatusesSeeder::class);

    $owner = User::factory()->create();

    $this->storeA = Store::query()->create([
        'user_id' => $owner->id,
        'name' => 'Store A',
        'slug' => 'orders-helper-a-'.Str::random(6),
    ]);
    $this->storeB = Store::query()->create([
        'user_id' => $owner->id,
        'name' => 'Store B',
        'slug' => 'orders-helper-b-'.Str::random(6),
    ]);

    $this->deliveredId = edzeeryOrderStatusId('delivered');
    $this->pendingId = edzeeryOrderStatusId('pending');
    $this->cancelledId = edzeeryOrderStatusId('cancelled');
    $this->canceledId = edzeeryOrderStatusId('canceled');
});

it('filters orders by a status key', function () {
    edzeeryOrder($this->storeA->id, $this->deliveredId);
    edzeeryOrder($this->storeA->id, $this->pendingId);

    expect(AllOrdersCountByStatus(OrderStatus::DELIVERED))->toBe(1);
    expect(AllOrdersByStatus(OrderStatus::DELIVERED))->toHaveCount(1);
    expect(AllOrdersByStatus(OrderStatus::DELIVERED)->pluck('status_id'))
        ->toContain($this->deliveredId);
});

it('groups both cancelled spellings', function () {
    edzeeryOrder($this->storeA->id, $this->cancelledId);
    edzeeryOrder($this->storeA->id, $this->canceledId);
    edzeeryOrder($this->storeA->id, $this->deliveredId);

    expect(AllOrdersCountByStatus([OrderStatus::CANCELED, OrderStatus::CANCELLED]))->toBe(2);
    expect(AllOrdersByStatus([OrderStatus::CANCELED, OrderStatus::CANCELLED]))->toHaveCount(2);
});

it('isolates tenants by store', function () {
    edzeeryOrder($this->storeA->id, $this->deliveredId);
    edzeeryOrder($this->storeA->id, $this->deliveredId);
    edzeeryOrder($this->storeB->id, $this->deliveredId);

    expect(AllOrdersCountByStore($this->storeA->id))->toBe(2);
    expect(AllOrdersCountByStoreAndStatus($this->storeB->id, OrderStatus::DELIVERED))->toBe(1);
    expect(AllOrdersByStore($this->storeA->id))->toHaveCount(2);
});

it('filters by delivery type', function () {
    edzeeryOrder($this->storeA->id, $this->deliveredId, Order::DELIVERY_HOME);
    edzeeryOrder($this->storeA->id, $this->deliveredId, Order::DELIVERY_STOPDESK);

    expect(AllOrdersCountByDeliveryType(Order::DELIVERY_STOPDESK))->toBe(1);
    expect(AllOrdersCountByDeliveryType(Order::DELIVERY_HOME, $this->storeA->id))->toBe(1);
    expect(AllOrdersByDeliveryType(Order::DELIVERY_STOPDESK))->toHaveCount(1);
});

it('makes a date-only `to` inclusive through end of day', function () {
    $first = edzeeryOrder($this->storeA->id, $this->deliveredId, createdAt: '2026-01-01 15:30:00');
    edzeeryOrder($this->storeA->id, $this->deliveredId, createdAt: '2026-01-02 00:00:00');

    $jan1 = AllOrdersByStatusAndDateRange(null, '2026-01-01', '2026-01-01');

    expect($jan1)->toHaveCount(1);
    expect($jan1->first()->id)->toBe($first->id);
    expect(AllOrdersCountByStatusAndDateRange(null, '2026-01-01', '2026-01-01'))->toBe(1);
    expect(AllOrdersCountByStatusAndDateRange(null, '2026-01-02', null))->toBe(1);
});

it('rejects unknown filter keys and invalid delivery types', function () {
    expect(fn () => ordersQuery(['bogus' => 1]))->toThrow(InvalidArgumentException::class);
    expect(fn () => ordersQuery(['delivery_type' => 'drone']))->toThrow(InvalidArgumentException::class);
});

it('keeps every count helper in sync with its collection', function () {
    edzeeryOrder($this->storeA->id, $this->deliveredId, Order::DELIVERY_HOME, 100);
    edzeeryOrder($this->storeA->id, $this->pendingId, Order::DELIVERY_STOPDESK, 50);
    edzeeryOrder($this->storeB->id, $this->deliveredId);

    expect(AllOrdersCount())->toBe(AllOrders()->count());
    expect(AllOrdersCountByStatus(OrderStatus::DELIVERED))
        ->toBe(AllOrdersByStatus(OrderStatus::DELIVERED)->count());
    expect(AllOrdersCountByStatus([OrderStatus::CANCELED, OrderStatus::CANCELLED]))
        ->toBe(AllOrdersByStatus([OrderStatus::CANCELED, OrderStatus::CANCELLED])->count());
    expect(AllOrdersCountByStore($this->storeA->id))
        ->toBe(AllOrdersByStore($this->storeA->id)->count());
    expect(AllOrdersCountByStoreAndStatus($this->storeA->id, OrderStatus::DELIVERED))
        ->toBe(AllOrdersByStoreAndStatus($this->storeA->id, OrderStatus::DELIVERED)->count());
    expect(AllOrdersCountByStoreAndStatusAndDateRange($this->storeA->id, null, '2026-01-01', '2026-01-31'))
        ->toBe(AllOrdersByStoreAndStatusAndDateRange($this->storeA->id, null, '2026-01-01', '2026-01-31')->count());
    expect(AllOrdersCountByStatusAndDateRange(null, '2026-01-01', '2026-01-31'))
        ->toBe(AllOrdersByStatusAndDateRange(null, '2026-01-01', '2026-01-31')->count());
    expect(AllOrdersCountByDeliveryType(Order::DELIVERY_HOME))
        ->toBe(AllOrdersByDeliveryType(Order::DELIVERY_HOME)->count());
});

it('counts only delivered revenue by default', function () {
    edzeeryOrder($this->storeA->id, $this->deliveredId, total: 100);
    edzeeryOrder($this->storeA->id, $this->deliveredId, total: 50);
    edzeeryOrder($this->storeA->id, $this->pendingId, total: 999);
    edzeeryOrder($this->storeB->id, $this->cancelledId, total: 40);
    edzeeryOrder($this->storeB->id, $this->deliveredId, total: 25.50);

    expect(ordersRevenue())->toBe(175.5);
    expect(total_amount())->toBe(175.5);
    expect(ordersRevenue(['store_id' => $this->storeA->id]))->toBe(150.0);
    expect(ordersRevenue(['status' => OrderStatus::PENDING]))->toBe(999.0);
});

it('groups order counts per status key', function () {
    edzeeryOrder($this->storeA->id, $this->deliveredId);
    edzeeryOrder($this->storeA->id, $this->deliveredId);
    edzeeryOrder($this->storeA->id, $this->pendingId);
    edzeeryOrder($this->storeB->id, $this->cancelledId);
    edzeeryOrder($this->storeB->id, $this->canceledId);

    $counts = orderStatusCounts();

    expect($counts['delivered'])->toBe(2);
    expect($counts['pending'])->toBe(1);
    expect($counts['cancelled'])->toBe(1);
    expect($counts['canceled'])->toBe(1);
    expect(orderStatusCounts($this->storeA->id))->toBe(['delivered' => 2, 'pending' => 1]);

    $manual = Order::query()
        ->join('statuses', 'statuses.id', '=', 'orders.status_id')
        ->where('statuses.type', 'order')
        ->selectRaw('statuses.key as status_key, COUNT(*) as aggregate')
        ->groupBy('statuses.key')
        ->pluck('aggregate', 'status_key')
        ->map(fn ($count) => (int) $count)
        ->all();

    expect(orderStatusCounts())->toBe($manual);
});

it('runs aggregate queries without hydrating models', function () {
    edzeeryOrder($this->storeA->id, $this->deliveredId);
    edzeeryOrder($this->storeA->id, $this->deliveredId);
    edzeeryOrder($this->storeA->id, $this->pendingId);

    DB::flushQueryLog();
    DB::enableQueryLog();

    expect(AllOrdersCount())->toBe(3);
    expect(DB::getQueryLog())->toHaveCount(1);
    expect(DB::getQueryLog()[0]['query'])->toContain('count(');

    DB::flushQueryLog();
    expect(AllOrdersCountByStatus(OrderStatus::DELIVERED))->toBe(2);
    expect(DB::getQueryLog())->toHaveCount(1);

    DB::flushQueryLog();
    expect(AllOrdersCountByStore($this->storeA->id))->toBe(3);
    expect(DB::getQueryLog())->toHaveCount(1);

    DB::flushQueryLog();
    expect(AllOrdersCountByDeliveryType(Order::DELIVERY_HOME))->toBe(3);
    expect(DB::getQueryLog())->toHaveCount(1);

    DB::flushQueryLog();
    expect(ordersRevenue())->toBeFloat();
    expect(DB::getQueryLog())->toHaveCount(1);
    expect(DB::getQueryLog()[0]['query'])->toContain('sum(');

    DB::flushQueryLog();
    expect(orderStatusCounts())->toBeArray();
    expect(DB::getQueryLog())->toHaveCount(1);
    expect(DB::getQueryLog()[0]['query'])->toContain('group by');

    DB::flushQueryLog();
    AllOrdersByStatus(OrderStatus::DELIVERED);
    expect(DB::getQueryLog())->toHaveCount(1);
});
