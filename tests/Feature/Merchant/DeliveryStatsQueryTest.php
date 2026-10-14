<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\DeliveryRate;
use App\Domains\Analytics\Support\DeliveryStatsQuery;
use App\Domains\Analytics\Support\DeliveryStatsReport;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use Carbon\CarbonImmutable;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| PHASE 37-N7 (D10) - one grouped read behind the delivery tab
|--------------------------------------------------------------------------
|
| The delivery rate is a single store-wide line, so the read groups by status
| key only. Live and soft-deleted orders travel together: a trashed order is
| never part of a ratio, but it is still counted between brackets.
|
*/

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function dsqStore(): Store
{
    $user = roleUser('merchant');

    return Store::create([
        'user_id' => $user->id,
        'name' => 'Delivery Stats Store',
        'slug' => 'dsq-'.uniqid(),
        'status' => 'active',
    ]);
}

function dsqStatusId(string $key): string
{
    return DB::table('statuses')->where('type', 'order')->where('key', $key)->value('id');
}

function dsqOrder(Store $store, string $statusKey, string $number): Order
{
    $order = Order::create([
        'store_id' => $store->id,
        'number' => $number,
        'total_amount' => 500,
        'status_id' => dsqStatusId($statusKey),
        'delivery_type' => 'home',
    ]);

    // PHASE 37-N9: the delivery cohort is orders carrying a tracking row.
    OrderTracking::create(['store_id' => $store->id, 'order_id' => $order->id]);

    return $order;
}

function dsqFilter(Store $store, array $overrides = []): DashboardFilter
{
    return new DashboardFilter(
        period: $overrides['period'] ?? 'all',
        from: $overrides['from'] ?? null,
        to: $overrides['to'] ?? null,
        timezone: 'UTC',
        utcOffsetSeconds: 0,
        storeId: $store->id,
        memberDimension: 'delivery',
        dateBasis: $overrides['dateBasis'] ?? DashboardFilter::DATE_BASIS_CREATED,
    );
}

function dsqRows(DashboardFilter $filter): array
{
    $keyed = [];

    foreach (app(DeliveryStatsQuery::class)->run($filter) as $row) {
        $keyed[$row['status_key']] = $row;
    }

    return $keyed;
}

test('counts live and trashed orders per status in one read', function () {
    $store = dsqStore();

    dsqOrder($store, 'delivered', 'DSQ-D-1');
    dsqOrder($store, 'delivered', 'DSQ-D-2');
    dsqOrder($store, 'delivered', 'DSQ-D-3');
    dsqOrder($store, 'returned', 'DSQ-R-1');
    dsqOrder($store, 'canceled', 'DSQ-C-1');
    dsqOrder($store, 'canceled', 'DSQ-C-2');

    dsqOrder($store, 'delivered', 'DSQ-D-TRASH')->delete();

    $rows = dsqRows(dsqFilter($store));

    expect($rows['delivered'])->toMatchArray(['total' => 3, 'deleted' => 1])
        ->and($rows['returned'])->toMatchArray(['total' => 1, 'deleted' => 0])
        ->and($rows['canceled'])->toMatchArray(['total' => 2, 'deleted' => 0])
        ->and($rows)->toHaveCount(3);
});

it('counts an order on the last-status axis through last_status_changed_at', function () {
    $store = dsqStore();

    $order = dsqOrder($store, 'delivered', 'DSQ-AXIS-1');
    $order->forceFill([
        'created_at' => now()->subDays(10),
        'last_status_changed_at' => now(),
    ])->save();

    $window = [
        'period' => 'custom',
        'from' => CarbonImmutable::parse(now()->startOfDay())->utc(),
        'to' => CarbonImmutable::parse(now()->endOfDay())->utc(),
    ];

    expect(dsqRows(dsqFilter($store, $window)))->toHaveCount(0)
        ->and(dsqRows(dsqFilter($store, $window + ['dateBasis' => DashboardFilter::DATE_BASIS_LAST_STATUS])))
        ->toHaveKey('delivered');
});

it('folds the rows so the delivered count, the rate and the brackets agree', function () {
    $store = dsqStore();

    dsqOrder($store, 'delivered', 'DSQ-R-1');
    dsqOrder($store, 'delivered', 'DSQ-R-2');
    dsqOrder($store, 'returned', 'DSQ-R-3');
    dsqOrder($store, 'canceled', 'DSQ-R-4');

    dsqOrder($store, 'delivered', 'DSQ-R-TRASH')->delete();

    $rows = app(DeliveryStatsQuery::class)->run(dsqFilter($store));
    $report = (new DeliveryStatsReport(DeliveryRate::GROSS, []))->build($rows);

    expect($report['delivered'])->toBe(2)
        ->and($report['returned'])->toBe(1)
        ->and($report['denominator'])->toBe(4)
        ->and($report['delivery_rate'])->toBe(50)
        ->and($report['return_rate'])->toBe(25)
        ->and($report['deleted'])->toBe(1);
});

it('groups the rows into the six breakdown buckets with their brackets', function () {
    $store = dsqStore();

    dsqOrder($store, 'delivered', 'DSQ-G-1');
    dsqOrder($store, 'delivered', 'DSQ-G-2');
    dsqOrder($store, 'returned', 'DSQ-G-3');
    dsqOrder($store, 'shipped', 'DSQ-G-4');
    dsqOrder($store, 'completed', 'DSQ-G-5'); // seeded, but not a mapped bucket key
    dsqOrder($store, 'delivered', 'DSQ-G-TRASH')->delete();

    $rows = app(DeliveryStatsQuery::class)->run(dsqFilter($store));
    $report = (new DeliveryStatsReport(DeliveryRate::GROSS, []))->build($rows);

    expect($report['groups'])->toMatchArray([
        'delivered' => ['total' => 2, 'deleted' => 1],
        'returned' => ['total' => 1, 'deleted' => 0],
        'in_progress' => ['total' => 1, 'deleted' => 0],
        'other' => ['total' => 1, 'deleted' => 0],
    ])
        ->and($report['total_orders'])->toBe(5)
        ->and($report['trash_orders'])->toBe(1)
        // Gross with no exclusions: every bucket stays inside the rate.
        ->and(array_filter($report['outside']))->toBe([]);
});
