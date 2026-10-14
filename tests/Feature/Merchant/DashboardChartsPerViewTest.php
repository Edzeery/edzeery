<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Services\StoreDashboardAnalyticsService;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * PHASE 37-K: the doughnut and the trend are one block per stats view, so the
 * same fixture is read twice and the two readings must differ exactly where D2
 * (the confirmed group) and D3 (the tracked cohort) say they should.
 */

uses(RefreshDatabase::class);

beforeEach(function () {
    // PHASE 37-N6: freeze mid-day (store in Africa/Algiers) so the default
    // "today" window cannot drift off an order's implicit created_at.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

/** A store in Algiers with the statuses both charts read. */
function dcvStore(): array
{
    $user = User::query()->create([
        'name' => 'Charts Viewer',
        'email' => 'dcv-'.uniqid().'@example.com',
        'password' => bcrypt('password'),
    ]);

    $store = Store::query()->create([
        'name' => 'Charts Store',
        'slug' => 'dcv-'.uniqid(),
        'currency_code' => 'SAR',
        'locale' => 'ar',
        'is_active' => true,
        'user_id' => $user->id,
    ]);
    $store->settings()->update(['timezone' => 'Africa/Algiers']);

    $statuses = collect([
        'pending', 'confirmed', 'delivered', 'returned', 'cancelled',
        'shipped', 'on_hold', 'wrong_number', 'postponed',
    ])->mapWithKeys(function ($key) {
        $id = (string) Str::ulid();
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

    return [$user, $store, $statuses];
}

function dcvOrder(Store $store, Collection $statuses, string $statusKey, array $attributes = []): Order
{
    $createdAt = $attributes['created_at'] ?? CarbonImmutable::parse('2026-03-10 08:00:00', 'UTC');

    $order = Order::query()->create(array_merge([
        'store_id' => $store->id,
        'number' => 'ORD-'.uniqid(),
        'total_amount' => 100,
        'status_id' => $statuses[$statusKey],
        'delivery_type' => 'delivery',
    ], $attributes));

    $order->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

    return $order;
}

/** A window the store clock can read: today in Algiers, unless overridden. */
function dcvFilter(string $storeId, array $overrides = []): DashboardFilter
{
    return new DashboardFilter(
        period: $overrides['period'] ?? 'today',
        from: $overrides['from'] ?? CarbonImmutable::parse('2026-03-10 00:00:00', 'Africa/Algiers')->utc(),
        to: $overrides['to'] ?? CarbonImmutable::parse('2026-03-10 23:59:59', 'Africa/Algiers')->utc(),
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        carrierId: $overrides['carrierId'] ?? null,
        memberDimension: $overrides['memberDimension'] ?? null,
        memberScopeIds: $overrides['memberScopeIds'] ?? null,
        storeId: $storeId,
    );
}

/** One metric of a trend payload: every bucket the axis drew. */
function dcvTrend(array $trend, string $key): array
{
    $series = collect($trend['series'])->firstWhere('key', $key);

    return $series['values'] ?? [];
}

/** PHASE 37-M2: the confirmation trend stacks the seven groups, so the
 *  "orders that arrived" total is the sum over every series. */
function dcvTrendTotal(array $trend): int|float
{
    return collect($trend['series'])->sum(fn ($s) => array_sum($s['values'] ?? []));
}

/** The seven confirmation groups, in the order `ConfirmationStatsGroups::all()` returns. */
function dcvGroupKeys(): array
{
    return ['confirmed', 'pending', 'failed', 'canceled', 'deferred', 'duplicate', 'other'];
}

it('collapses the shipping flow into one confirmed slice that reads like the KPI', function () {
    [, $store, $statuses] = dcvStore();

    foreach (['pending', 'confirmed', 'delivered', 'delivered', 'returned', 'cancelled'] as $key) {
        dcvOrder($store, $statuses, $key);
    }

    $service = new StoreDashboardAnalyticsService($store->id);
    $filter = dcvFilter($store->id);
    $breakdown = $service->statusBreakdown($filter);
    $summary = $service->summary($filter);

    expect($breakdown->pluck('key')->sort()->values()->all())
        ->toBe(['canceled', 'confirmed', 'pending'])
        // Confirmed, delivered and returned all passed the confirmation desk and
        // now share the one confirmed slice (D2), so the doughnut can never
        // disagree with the confirmation-rate KPI beside it.
        ->and($breakdown->firstWhere('key', 'confirmed')->count)->toBe(4)
        ->and($breakdown->sum('count'))->toBe($summary['total_orders'])
        ->and($breakdown->pluck('percent')->sum())->toBe(100)
        // The slices are the report's own groups: they can never disagree.
        ->and($breakdown->firstWhere('key', 'confirmed')->count)
        ->toBe($service->confirmationStats($filter)['groups']['confirmed']['total']);
});

it('reads delivery slices from tracked orders only and counts an order once', function () {
    [, $store, $statuses] = dcvStore();

    $attempted = dcvOrder($store, $statuses, 'delivered');
    $otherDelivered = dcvOrder($store, $statuses, 'delivered');
    $returned = dcvOrder($store, $statuses, 'returned');
    $shipped = dcvOrder($store, $statuses, 'shipped');
    // Never shipped, so it belongs to Confirmation, not to Delivery.
    dcvOrder($store, $statuses, 'pending');

    // A re-shipped order with two attempts is still one order.
    OrderTracking::query()->create(['order_id' => $attempted->id, 'store_id' => $store->id]);
    OrderTracking::query()->create(['order_id' => $attempted->id, 'store_id' => $store->id]);
    foreach ([$otherDelivered, $returned, $shipped] as $tracked) {
        OrderTracking::query()->create(['order_id' => $tracked->id, 'store_id' => $store->id]);
    }

    $service = new StoreDashboardAnalyticsService($store->id);
    $delivery = $service->statusBreakdown(dcvFilter($store->id, ['memberDimension' => 'delivery']));
    $confirmation = $service->statusBreakdown(dcvFilter($store->id));

    // D2 does not collapse here: the individual delivery steps are the story.
    expect($delivery->pluck('key')->sort()->values()->all())->toBe(['delivered', 'returned', 'shipped'])
        ->and($delivery->firstWhere('key', 'delivered')->count)->toBe(2)
        ->and($delivery->sum('count'))->toBe(4)
        ->and($delivery->pluck('percent')->sum())->toBe(100)
        // The confirmation view still sees the unshipped order, and folds the
        // shipped ones into the single confirmed slice (D2).
        ->and($confirmation->sum('count'))->toBe(5)
        ->and($confirmation->pluck('key')->sort()->values()->all())->toBe(['confirmed', 'pending']);
});

it('plots the two views over the same axis with the metrics each view leads with', function () {
    [, $store, $statuses] = dcvStore();

    dcvOrder($store, $statuses, 'pending', ['total_amount' => 9000]);
    dcvOrder($store, $statuses, 'delivered', ['total_amount' => 1000]);
    dcvOrder($store, $statuses, 'delivered', ['total_amount' => 500]);
    $returned = dcvOrder($store, $statuses, 'returned', ['total_amount' => 60]);
    dcvOrder($store, $statuses, 'cancelled', ['total_amount' => 50]);

    OrderTracking::query()->create(['order_id' => $returned->id, 'store_id' => $store->id]);
    foreach (Order::query()->where('status_id', $statuses['delivered'])->get() as $delivered) {
        OrderTracking::query()->create(['order_id' => $delivered->id, 'store_id' => $store->id]);
    }

    $service = new StoreDashboardAnalyticsService($store->id);
    $confirmation = $service->trendSeries(dcvFilter($store->id));
    $delivery = $service->trendSeries(dcvFilter($store->id, ['memberDimension' => 'delivery']));

    expect($confirmation['view'])->toBe('confirmation')
        ->and($delivery['view'])->toBe('delivery')
        // Same window, same buckets: only the metrics change with the view.
        ->and($delivery['labels'])->toBe($confirmation['labels'])
        // 37-M2: the confirmation trend stacks the seven groups; the stack
        // total is the orders that arrived (the old `received` series).
        ->and(dcvTrendTotal($confirmation))->toBe(5)
        ->and(array_sum(dcvTrend($confirmation, 'confirmed')))->toBe(3)
        ->and(array_sum(dcvTrend($confirmation, 'canceled')))->toBe(1)
        ->and(array_sum(dcvTrend($delivery, 'delivered')))->toBe(2)
        ->and(array_sum(dcvTrend($delivery, 'returned')))->toBe(1)
        // The 9000 pending order never reaches the delivered revenue line.
        ->and(array_sum(dcvTrend($delivery, 'revenue')))->toBe(1500.0)
        ->and(collect($confirmation['series'])->pluck('axis')->unique()->all())->toBe(['y'])
        ->and(collect($delivery['series'])->firstWhere('key', 'revenue')['axis'])->toBe('y1')
        // PHASE 37-N9: delivery draws smooth monotone curves, not bars.
        ->and(collect($delivery['series'])->firstWhere('key', 'delivered')['type'])->toBe('line')
        ->and(collect($delivery['series'])->firstWhere('key', 'returned')['type'])->toBe('line');
});

it('answers period, carrier and member filters the same way on both charts', function () {
    [$user, $store, $statuses] = dcvStore();

    $member = StoreMembership::query()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);
    $carrierA = ShippingProvider::query()->create([
        'store_id' => $store->id, 'name' => 'Carrier A', 'credentials' => [], 'is_active' => true,
    ]);
    $carrierB = ShippingProvider::query()->create([
        'store_id' => $store->id, 'name' => 'Carrier B', 'credentials' => [], 'is_active' => true,
    ]);

    $todayDelivered = dcvOrder($store, $statuses, 'delivered', [
        'shipping_provider_id' => $carrierA->id,
        'assigned_to_membership_id' => $member->id,
        'confirmed_by_membership_id' => $member->id,
    ]);
    dcvOrder($store, $statuses, 'pending', ['shipping_provider_id' => $carrierB->id]);
    dcvOrder($store, $statuses, 'delivered', [
        'shipping_provider_id' => $carrierA->id,
        'created_at' => CarbonImmutable::parse('2026-03-09 08:00:00', 'UTC'),
    ]);

    foreach (Order::query()->where('status_id', $statuses['delivered'])->get() as $delivered) {
        OrderTracking::query()->create(['order_id' => $delivered->id, 'store_id' => $store->id]);
    }

    $service = new StoreDashboardAnalyticsService($store->id);
    $yesterday = [
        'period' => 'custom',
        'from' => CarbonImmutable::parse('2026-03-09 00:00:00', 'Africa/Algiers')->utc(),
        'to' => CarbonImmutable::parse('2026-03-09 23:59:59', 'Africa/Algiers')->utc(),
    ];

    // [overrides, confirmation view total, delivery view total]
    foreach ([
        [[], 2, 1],
        [['carrierId' => $carrierA->id], 1, 1],
        [['carrierId' => $carrierB->id], 1, 0],
        [$yesterday, 1, 1],
    ] as [$overrides, $received, $deliveredCount]) {
        $confirmation = dcvFilter($store->id, $overrides);
        $delivery = dcvFilter($store->id, $overrides + ['memberDimension' => 'delivery']);

        expect(dcvTrendTotal($service->trendSeries($confirmation)))->toBe($received)
            ->and(array_sum(dcvTrend($service->trendSeries($delivery), 'delivered')))->toBe($deliveredCount)
            ->and($service->statusBreakdown($confirmation)->sum('count'))->toBe($received)
            ->and($service->statusBreakdown($delivery)->sum('count'))->toBe($deliveredCount);
    }

    // A confirmation member scope narrows the cohort to their own orders.
    $scoped = dcvFilter($store->id, [
        'memberDimension' => 'confirmation',
        'memberScopeIds' => [$member->id],
    ]);

    expect(dcvTrendTotal($service->trendSeries($scoped)))->toBe(1)
        ->and($service->statusBreakdown($scoped)->sum('count'))->toBe(1)
        ->and($todayDelivered->assigned_to_membership_id)->toBe($member->id)
        ->and($todayDelivered->confirmed_by_membership_id)->toBe($member->id);
});

it('keeps an axis of empty buckets when a window holds no orders', function () {
    [, $store, $statuses] = dcvStore();

    $service = new StoreDashboardAnalyticsService($store->id);
    $empty = dcvFilter($store->id, [
        'period' => 'custom',
        'from' => CarbonImmutable::parse('2026-01-01 00:00:00', 'Africa/Algiers')->utc(),
        'to' => CarbonImmutable::parse('2026-01-02 23:59:59', 'Africa/Algiers')->utc(),
    ]);

    $trend = $service->trendSeries($empty);

    expect($service->statusBreakdown($empty))->toBeEmpty()
        ->and($trend['labels'])->toHaveCount(2)
        ->and(dcvTrendTotal($trend))->toBe(0)
        // 37-M2: the confirmation trend is one stacked series per group, not
        // the old four-figure line set.
        ->and(collect($trend['series'])->pluck('key')->all())->toBe(dcvGroupKeys());

    // Nothing at all: no axis to draw, and still a keyed series per metric.
    $bare = $service->trendSeries(new DashboardFilter(
        period: 'all',
        from: null,
        to: null,
        timezone: 'Africa/Algiers',
        utcOffsetSeconds: 3600,
        storeId: $store->id,
    ));

    expect($bare['labels'])->toBe([])
        ->and(dcvTrend($bare, 'confirmed'))->toBe([]);

    // Statuses exist but no order inside that window carries them.
    dcvOrder($store, $statuses, 'pending');
    expect($service->statusBreakdown($empty))->toBeEmpty();
});

it('rounds slice percentages so the legend always adds up to 100', function (array $fixture, int $slices) {
    [, $store, $statuses] = dcvStore();

    foreach ($fixture as $key) {
        dcvOrder($store, $statuses, $key);
    }

    $breakdown = (new StoreDashboardAnalyticsService($store->id))->statusBreakdown(dcvFilter($store->id));

    expect($breakdown)->toHaveCount($slices)
        ->and($breakdown->pluck('percent')->sum())->toBe(100)
        ->and($breakdown->every(fn ($slice) => $slice->percent >= 0 && $slice->percent <= 100))->toBeTrue();
})->with([
    'one slice' => [['pending', 'pending'], 1],
    'three equal slices' => [['pending', 'cancelled', 'on_hold'], 3],
    // Five statuses fold into four confirmation groups: cancelled + wrong_number
    // share the CANCELED bucket, on_hold is OTHER, postponed is DEFERRED.
    'four groups from five statuses' => [['pending', 'cancelled', 'on_hold', 'wrong_number', 'postponed'], 4],
    'eighty twenty' => [['pending', 'pending', 'pending', 'pending', 'cancelled'], 2],
]);

test('the chart block paints the fixes owner review asked for', function () {
    $source = file_get_contents(
        resource_path('views/livewire/merchant/dashboard/partials/charts.blade.php')
    );

    // 1. Centre total: a large number plus the label, in a colour that exists
    //    in both themes (--edz-color-ink was never defined, so the fallback
    //    near-black disappeared against the dark card).
    expect($source)
        ->toContain("themeColor('--edz-color-text', '#101828')")
        ->toContain("ctx.font = '800 26px Inter, sans-serif'")
        ->toContain('ctx.fillText(num(total), cx, cy - 10)')
        ->toContain("ctx.fillText(@js(__('dashboard.chart_total')), cx, cy + 15)");

    // 2. The legend is server-rendered HTML (D3), so a label and its digits
    //    can never swap under RTL: count and percent sit in their own <bdi>.
    expect($source)
        ->toContain('<bdi>{{ $slice->count }}</bdi> (<bdi dir="ltr">{{ $slice->percent }}%</bdi>)')
        // The canvas legend is switched off for both charts.
        ->toContain('legend: { display: false }');

    // 3. Slices are separated by the card background, never by a new colour.
    expect($source)
        ->toContain("themeColor('--edz-color-surface', 'rgb(255, 255, 255)')")
        ->toContain('borderColor: data.statusKeys.map(() => resolvedSurface)')
        ->toContain('borderWidth: 2');

    // 4. D3: one colour per group, resolved from the single config map through
    //    the theme token, shared by the trend, the doughnut and the legend.
    expect($source)
        ->toContain('groupColors: {{ json_encode($groupColors) }}')
        ->toContain("themeColor('--edz-color-' + groupColors[key], fallback)")
        ->toContain('legend: { display: false }');

    // 5. PHASE 37-N4: the confirmation trend is one smooth monotone curve per
    //    group plus a browser-derived total band - never a stacked bar.
    expect($source)
        ->toContain("cubicInterpolationMode: 'monotone'")
        ->toContain('_isTotal: true')
        ->toContain("const isConfirmation = data.trendView !== 'delivery'")
        ->not->toContain('stackedBars');

    // 6. The delivery view keeps its money line: the axis still speaks in
    //    currency, and the metric type still reaches the dataset.
    expect($source)
        ->toContain('stacked: false')
        ->toContain('callback: (value) => `${num(value)} ${currency}`')
        ->toContain("context.dataset.yAxisID === 'y1'");
});
