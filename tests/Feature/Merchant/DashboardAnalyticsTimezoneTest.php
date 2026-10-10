<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Services\StoreDashboardAnalyticsService;
use App\Domains\Analytics\Support\DashboardFilterFactory;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * The values of one metric inside a trend payload, keyed by metric key.
 * Every bucket the axis drew is present, so a sum over them is the window total.
 */
function tzTrendValues(array $trend, string $key): array
{
    $series = collect($trend['series'])->firstWhere('key', $key);

    return $series['values'] ?? [];
}

beforeEach(function () {
    $this->user = User::query()->create([
        'name' => 'Phase 37F',
        'email' => 'p37f-'.uniqid().'@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->store = Store::query()->create([
        'name' => 'Phase 37F Store',
        'slug' => 'p37f-'.uniqid(),
        'currency_code' => 'SAR',
        'locale' => 'ar',
        'is_active' => true,
        'user_id' => $this->user->id,
    ]);

    // A store_setting row already exists (created with the store); the store
    // timezone lives there, not on stores.
    $this->store->settings()->update(['timezone' => 'Africa/Algiers']);

    // Statuses are not seeded by RefreshDatabase; create the ones under test.
    $this->statusIds = collect(['pending', 'confirmed', 'preparing', 'shipped', 'in_transit', 'out_for_delivery', 'delivered', 'completed', 'returned'])
        ->mapWithKeys(function ($key) {
            $id = (string) \Illuminate\Support\Str::ulid();

            DB::table('statuses')->insert([
                'id' => $id,
                'store_id' => null,
                'type' => 'order',
                'key' => $key,
                'label' => ucfirst($key),
                'is_system' => true,
            ]);

            return [$key => $id];
        });

    $this->makeOrder = function (string $statusKey, float $amount, CarbonImmutable $createdAt, ?string $membershipId = null): Order {
        $order = Order::query()->create([
            'store_id' => $this->store->id,
            'number' => 'ORD-'.uniqid(),
            'total_amount' => $amount,
            'status_id' => $this->statusIds[$statusKey],
            'delivery_type' => 'delivery',
            'assigned_to_membership_id' => $membershipId,
            'confirmed_by_membership_id' => $membershipId,
        ]);

        return $order->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save() ? $order : $order;
    };

    $this->service = new StoreDashboardAnalyticsService($this->store->id);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

it('buckets today by hour in the store timezone', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    // Local 00:30 on 2026-03-10 in Algiers (UTC+1) === 23:30 UTC on 2026-03-09.
    $order = ($this->makeOrder)('delivered', 500, CarbonImmutable::parse('2026-03-09 23:30:00', 'UTC'));

    $localStart = CarbonImmutable::parse('2026-03-10 00:00:00', 'Africa/Algiers');

    $filter = new DashboardFilter(
        period: 'today',
        from: $localStart->utc(),
        to: $localStart->endOfDay()->utc(),
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberScopeIds: null,
        storeId: $this->store->id,
    );

    $trend = $this->service->trendSeries($filter);

    expect(count($trend['labels']))->toBe(24)
        ->and($trend['labels'][0])->toBe('00:00')
        ->and($trend['labels'][23])->toBe('23:00')
        ->and(array_sum(tzTrendValues($trend, 'received')))->toBe(1)
        ->and(tzTrendValues($trend, 'received')[0])->toBe(1);

    // The money line only exists on the delivery view, whose cohort is
    // tracked orders, so the delivered amount is read from there.
    OrderTracking::query()->create([
        'order_id' => $order->id,
        'store_id' => $this->store->id,
    ]);

    $delivery = $this->service->trendSeries(new DashboardFilter(
        period: 'today',
        from: $localStart->utc(),
        to: $localStart->endOfDay()->utc(),
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberDimension: 'delivery',
        memberScopeIds: null,
        storeId: $this->store->id,
    ));

    expect(array_sum(tzTrendValues($delivery, 'revenue')))->toBe(500.0);
});

it('counts every scoped order but only delivered revenue, and derives aov from delivered', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $deliveredA = ($this->makeOrder)('delivered', 1000, CarbonImmutable::parse('2026-03-10 09:00:00', 'UTC'));
    $deliveredB = ($this->makeOrder)('delivered', 500, CarbonImmutable::parse('2026-03-10 09:30:00', 'UTC'));
    ($this->makeOrder)('pending', 9000, CarbonImmutable::parse('2026-03-10 10:00:00', 'UTC'));

    $filter = new DashboardFilter(
        period: 'today',
        from: CarbonImmutable::parse('2026-03-10 00:00:00', 'Africa/Algiers')->utc(),
        to: CarbonImmutable::parse('2026-03-10 23:59:59', 'Africa/Algiers')->utc(),
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberScopeIds: null,
        storeId: $this->store->id,
    );

    $summary = $this->service->summary($filter);

    expect($summary['total_orders'])->toBe(3)
        ->and($summary['revenue'])->toEqual(1500)
        ->and($summary['aov'])->toBe(750.0)
        ->and($summary['has_previous'])->toBeTrue();

    // The confirmation view counts every order in the window.
    $trend = $this->service->trendSeries($filter);
    expect(array_sum(tzTrendValues($trend, 'received')))->toBe(3);

    // The delivery view reads money, and its cohort is tracked orders only.
    foreach ([$deliveredA, $deliveredB] as $delivered) {
        OrderTracking::query()->create(['order_id' => $delivered->id, 'store_id' => $this->store->id]);
    }

    $delivery = $this->service->trendSeries(new DashboardFilter(
        period: 'today',
        from: CarbonImmutable::parse('2026-03-10 00:00:00', 'Africa/Algiers')->utc(),
        to: CarbonImmutable::parse('2026-03-10 23:59:59', 'Africa/Algiers')->utc(),
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberDimension: 'delivery',
        memberScopeIds: null,
        storeId: $this->store->id,
    ));

    expect(array_sum(tzTrendValues($delivery, 'delivered')))->toBe(2)
        ->and(array_sum(tzTrendValues($delivery, 'revenue')))->toBe(1500.0);
});

it('compares against the equal-length window immediately before and hides the arrow when there is none', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $from = CarbonImmutable::parse('2026-03-10 00:00:00', 'Africa/Algiers')->utc();
    $to = CarbonImmutable::parse('2026-03-10 23:59:59', 'Africa/Algiers')->utc();

    // Previous window: [2026-03-08 23:00 UTC, 2026-03-09 22:59:59 UTC].
    ($this->makeOrder)('delivered', 100, CarbonImmutable::parse('2026-03-09 10:00:00', 'UTC'));
    ($this->makeOrder)('delivered', 200, CarbonImmutable::parse('2026-03-10 10:00:00', 'UTC'));

    $filter = new DashboardFilter(
        period: 'today',
        from: $from,
        to: $to,
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberScopeIds: null,
        storeId: $this->store->id,
    );

    $previous = $filter->previous();
    expect($previous)->not->toBeNull()
        ->and($previous->from->format('Y-m-d H:i:s'))->toBe('2026-03-08 23:00:00')
        ->and($previous->to->format('Y-m-d H:i:s'))->toBe('2026-03-09 22:59:59')
        ->and($previous->from->diffInSeconds($previous->to) + 1)
        ->toBe($filter->from->diffInSeconds($filter->to) + 1);

    $summary = $this->service->summary($filter);
    expect($summary['has_previous'])->toBeTrue()
        ->and($summary['total_orders'])->toBe(1)
        ->and($summary['total_orders_change'])->toBe(0)
        ->and($summary['revenue'])->toEqual(200)
        ->and($summary['revenue_change'])->toBe(100)
        ->and($summary)->not->toHaveKey('revenue_prev');

    $all = new DashboardFilter(
        period: 'all',
        from: null,
        to: null,
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberScopeIds: null,
        storeId: $this->store->id,
    );

    expect($all->previous())->toBeNull();

    $allSummary = $this->service->summary($all);
    expect($allSummary['has_previous'])->toBeFalse()
        ->and($allSummary['total_orders_change'])->toBe(0)
        ->and($allSummary['revenue_change'])->toBe(0)
        ->and($allSummary['total_orders'])->toBe(2);
});

it('picks the granularity from the window length', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-20 12:00:00', 'UTC'));

    ($this->makeOrder)('delivered', 50, CarbonImmutable::parse('2025-01-01 08:00:00', 'UTC'));

    $local = fn (string $from, string $to) => new DashboardFilter(
        period: 'custom',
        from: CarbonImmutable::parse($from, 'Africa/Algiers')->utc(),
        to: CarbonImmutable::parse($to, 'Africa/Algiers')->utc(),
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberScopeIds: null,
        storeId: $this->store->id,
    );

    // Week: 7 daily points.
    expect(count($this->service->trendSeries($local('2026-06-14', '2026-06-20'))['labels']))->toBe(7);

    // Month to date: one point per day so far.
    expect(count($this->service->trendSeries($local('2026-06-01', '2026-06-20'))['labels']))->toBe(20);

    // Custom up to 62 days stays daily.
    expect(count($this->service->trendSeries($local('2026-04-20', '2026-06-20'))['labels']))->toBe(62);

    // Beyond that it switches to monthly, with m/Y labels.
    $long = $this->service->trendSeries($local('2025-01-01', '2026-06-20'));
    expect($long['labels'])->toContain('01/2025')->toContain('06/2026');

    // "All" runs from the oldest scoped order, monthly when the span is long.
    $all = new DashboardFilter(
        period: 'all',
        from: null,
        to: null,
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberScopeIds: null,
        storeId: $this->store->id,
    );
    expect($this->service->trendSeries($all)['labels'])->toContain('01/2025');
});

it('keeps all daily when the whole span fits in two months', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-20 12:00:00', 'UTC'));

    ($this->makeOrder)('delivered', 10, CarbonImmutable::parse('2026-06-10 08:00:00', 'UTC'));

    $all = new DashboardFilter(
        period: 'all',
        from: null,
        to: null,
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberScopeIds: null,
        storeId: $this->store->id,
    );

    $series = $this->service->trendSeries($all);
    expect($series['labels'][0])->toBe('10/06')
        ->and($series['labels'])->toContain('20/06')
        ->and(count($series['labels']))->toBe(11);
});

it('returns an empty series when there is nothing to plot', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-20 12:00:00', 'UTC'));

    $all = new DashboardFilter(
        period: 'all',
        from: null,
        to: null,
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberScopeIds: null,
        storeId: $this->store->id,
    );

    $empty = $this->service->trendSeries($all);

    // No window means no axis, and every metric keeps its key with no points.
    expect($empty['labels'])->toBe([])
        ->and($empty['series'])->toHaveCount(3)
        ->and(tzTrendValues($empty, 'received'))->toBe([])
        ->and(tzTrendValues($empty, 'confirmed'))->toBe([])
        ->and(tzTrendValues($empty, 'canceled'))->toBe([]);
});

it('scopes both dimensions by the confirmed member', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $membershipA = StoreMembership::query()->create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);

    $membershipB = StoreMembership::query()->create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);

    ($this->makeOrder)('delivered', 300, CarbonImmutable::parse('2026-03-10 08:00:00', 'UTC'), $membershipA->id);
    ($this->makeOrder)('delivered', 700, CarbonImmutable::parse('2026-03-10 09:00:00', 'UTC'), $membershipB->id);

    $build = fn (?string $dimension, array $scope) => new DashboardFilter(
        period: 'today',
        from: CarbonImmutable::parse('2026-03-10 00:00:00', 'Africa/Algiers')->utc(),
        to: CarbonImmutable::parse('2026-03-10 23:59:59', 'Africa/Algiers')->utc(),
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        memberId: $scope[0] ?? null,
        memberDimension: $dimension,
        memberScopeIds: $scope,
        storeId: $this->store->id,
    );

    // Credit belongs to the member who confirmed the order, on both tabs
    // (§ 11 of docs/plans/order-distribution-rules.md).
    expect($this->service->summary($build('confirmation', [$membershipA->id]))['total_orders'])->toBe(1)
        ->and($this->service->summary($build('delivery', [$membershipA->id]))['total_orders'])->toBe(1)
        ->and($this->service->summary($build('delivery', [$membershipB->id]))['total_orders'])->toBe(1);

    // An empty scope matches nothing (no membership = fail closed).
    expect($this->service->summary($build('confirmation', []))['total_orders'])->toBe(0)
        ->and($this->service->summary($build('delivery', []))['total_orders'])->toBe(0);
});

it('derives store-time boundaries, scope and dimension from the factory', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $factory = app(DashboardFilterFactory::class);

    $viewer = StoreMembership::query()->create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);
    $viewer->syncPermissions([StorePermissionEnum::STATS_TEAM_VIEW->value]);

    $order = ($this->makeOrder)('delivered', 100, CarbonImmutable::parse('2026-03-10 08:00:00', 'UTC'), $viewer->id);
    $other = StoreMembership::query()->create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);

    $filter = $factory->make(['period' => 'today'], $viewer);
    expect($filter->timezone)->toBe('Africa/Algiers')
        ->and($filter->utcOffsetSeconds)->toBe(3600)
        ->and($filter->from->format('Y-m-d H:i:s'))->toBe('2026-03-09 23:00:00')
        ->and($filter->localFrom()->format('Y-m-d H:i'))->toBe('2026-03-10 00:00')
        ->and($filter->memberScopeIds)->toBeNull()
        ->and($filter->memberLocked)->toBeFalse()
        // Team-view-only: neither confirmation nor delivery capability, so the
        // documented fallback is delivery.
        ->and($filter->memberDimension)->toBe('delivery');

    // A foreign or unknown member id is discarded.
    $picked = $factory->make(['period' => 'today', 'memberId' => $other->id], $viewer);
    expect($picked->memberId)->toBe($other->id)->and($picked->memberScopeIds)->toBe([$other->id]);

    $foreign = $factory->make(['period' => 'today', 'memberId' => 'missing-id'], $viewer);
    expect($foreign->memberId)->toBeNull()->and($foreign->memberScopeIds)->toBeNull();

    // Locked member (no team permission): own orders only, no choice offered.
    $locked = StoreMembership::query()->create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);
    $locked->syncPermissions([StorePermissionEnum::STATS_CONFIRMATION->value]);

    $lockedFilter = $factory->make(['period' => 'today', 'memberId' => $other->id], $locked);
    expect($lockedFilter->memberLocked)->toBeTrue()
        ->and($lockedFilter->memberId)->toBe($locked->id)
        ->and($lockedFilter->memberScopeIds)->toBe([$locked->id]);

    // TEAM_VIEW_OWN without a pick means self + subordinates.
    $supervisor = StoreMembership::query()->create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);
    $supervisor->syncPermissions([StorePermissionEnum::TEAM_VIEW_OWN->value]);

    $subordinate = StoreMembership::query()->create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'STAFF',
        'is_active' => true,
        'supervisor_membership_id' => $supervisor->id,
    ]);

    $own = $factory->make(['period' => 'today'], $supervisor);
    expect($own->memberScopeIds)->toEqualCanonicalizing([$supervisor->id, $subordinate->id]);

    // Delivery-only capability: dimension falls back to delivery.
    $deliveryOnly = StoreMembership::query()->create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);
    $deliveryOnly->syncPermissions([StorePermissionEnum::STATS_DELIVERY->value]);

    $deliveryFilter = $factory->make(['period' => 'today', 'memberDimension' => 'confirmation'], $deliveryOnly);
    expect($deliveryFilter->memberDimension)->toBe('delivery');

    // No membership at all: fail closed.
    expect($factory->make(['period' => 'today'], null)->memberScopeIds)->toBe([]);

    expect($order->id)->not->toBeNull();
});
