<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\DashboardFilterFactory;
use App\Domains\Analytics\Support\DateBucket;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// tests/Unit is not covered by the Pest.php bootstrap, so bind the app here.
uses(Tests\TestCase::class)->use(RefreshDatabase::class);

const DASHBOARD_FILTER_TZ = 'Africa/Algiers';

/**
 * Builds the filter the factory would build for a membership with the given
 * permissions, so the period tests are not coupled to the permission resolver.
 */
function memberWith(array $permissions): StoreMembership
{
    $user = User::query()->create([
        'name' => 'Unit',
        'email' => 'unit-'.uniqid().'@example.com',
        'password' => bcrypt('password'),
    ]);

    $store = Store::query()->create([
        'name' => 'Unit Store',
        'slug' => 'unit-'.uniqid(),
        'user_id' => $user->id,
    ]);

    $membership = StoreMembership::query()->create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'role' => 'MANAGER',
        'is_active' => true,
    ]);

    $membership->syncPermissions($permissions);

    return $membership->setRelation('store', $store);
}

beforeEach(function () {
    // A fixed instant so every boundary assertion is deterministic. Algiers is
    // UTC+1 all year round, so the local/UTC arithmetic below stays simple.
    Carbon\CarbonImmutable::setTestNow(Carbon\CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));
});

afterEach(function () {
    Carbon\CarbonImmutable::setTestNow();
});

it('bounds every named period in the store timezone and hands over UTC', function (array $input, ?string $from, ?string $to, ?string $localFrom) {
    $filter = app(DashboardFilterFactory::class)->make($input, memberWith([]));

    if ($from === null) {
        expect($filter->from)->toBeNull()->and($filter->to)->toBeNull();

        return;
    }

    // The UTC window is what every query compares created_at against...
    expect($filter->from->format('Y-m-d H:i:s'))->toBe($from)
        ->and($filter->to->format('Y-m-d H:i:s'))->toBe($to)
        // ...and the local window is what the user actually asked for.
        ->and($filter->localFrom()->format('Y-m-d H:i:s'))->toBe($localFrom)
        ->and($filter->from->timezoneName)->toBe('UTC')
        ->and($filter->to->timezoneName)->toBe('UTC')
        ->and($filter->timezone)->toBe(DASHBOARD_FILTER_TZ)
        ->and($filter->utcOffsetSeconds)->toBe(3600);
})->with([
    'all' => [['period' => 'all'], null, null, null],
    'today' => [['period' => 'today'], '2026-03-09 23:00:00', '2026-03-10 22:59:59', '2026-03-10 00:00:00'],
    'yesterday' => [['period' => 'yesterday'], '2026-03-08 23:00:00', '2026-03-09 22:59:59', '2026-03-09 00:00:00'],
    'week' => [['period' => 'week'], '2026-03-03 23:00:00', '2026-03-10 22:59:59', '2026-03-04 00:00:00'],
    'month' => [['period' => 'month'], '2026-02-28 23:00:00', '2026-03-10 22:59:59', '2026-03-01 00:00:00'],
]);

it('leaves the window unbounded for all', function () {
    $filter = app(DashboardFilterFactory::class)->make(['period' => 'all'], memberWith([]));

    expect($filter->from)->toBeNull()
        ->and($filter->to)->toBeNull()
        ->and($filter->localFrom())->toBeNull()
        ->and($filter->localTo())->toBeNull()
        // The offset still has to be known to bucket "all" by the store clock.
        ->and($filter->utcOffsetSeconds)->toBe(3600);
});

it('reads the local boundaries of each named period', function () {
    $factory = app(DashboardFilterFactory::class);
    $membership = memberWith([]);

    $local = fn (string $period) => [
        $factory->make(['period' => $period], $membership)->localFrom()?->format('Y-m-d H:i:s'),
        $factory->make(['period' => $period], $membership)->localTo()?->format('Y-m-d H:i:s'),
    ];

    expect($local('today'))->toBe(['2026-03-10 00:00:00', '2026-03-10 23:59:59'])
        ->and($local('yesterday'))->toBe(['2026-03-09 00:00:00', '2026-03-09 23:59:59'])
        // The week is the last 7 local days including today.
        ->and($local('week'))->toBe(['2026-03-04 00:00:00', '2026-03-10 23:59:59'])
        // The month runs from the local 1st to now, not to the end of the month.
        ->and($local('month'))->toBe(['2026-03-01 00:00:00', '2026-03-10 23:59:59']);
});

it('honours a custom range and swaps it when the dates come back reversed', function () {
    $factory = app(DashboardFilterFactory::class);
    $membership = memberWith([]);

    $range = fn (?string $from, ?string $to) => [
        $factory->make(['period' => 'custom', 'dateFrom' => $from, 'dateTo' => $to], $membership)
            ->localFrom()?->format('Y-m-d'),
        $factory->make(['period' => 'custom', 'dateFrom' => $from, 'dateTo' => $to], $membership)
            ->localTo()?->format('Y-m-d'),
    ];

    expect($range('2026-03-01', '2026-03-05'))->toBe(['2026-03-01', '2026-03-05'])
        ->and($range('2026-03-05', '2026-03-01'))->toBe(['2026-03-01', '2026-03-05'])
        ->and($range('2026-03-05', '2026-03-05'))->toBe(['2026-03-05', '2026-03-05']);
});

it('caps an over-long custom range 366 days after the requested start', function () {
    $filter = app(DashboardFilterFactory::class)->make([
        'period' => 'custom',
        'dateFrom' => '2020-01-01',
        'dateTo' => '2026-03-05',
    ], memberWith([]));

    // The requested end (2026-03-05) is years away and is not honoured.
    expect($filter->localFrom()->format('Y-m-d'))->toBe('2020-01-01')
        ->and($filter->localTo()->format('Y-m-d'))->toBe('2021-01-01')
        ->and((int) $filter->localFrom()->diffInDays($filter->localTo()))->toBe(366)
        ->and($filter->localTo()->format('H:i:s'))->toBe('23:59:59');
});

it('names an unknown or missing period today', function (array $input) {
    $filter = app(DashboardFilterFactory::class)->make($input, memberWith([]));

    expect($filter->period)->toBe('today')
        ->and($filter->localFrom()->format('Y-m-d H:i'))->toBe('2026-03-10 00:00')
        ->and($filter->localTo()->format('Y-m-d H:i'))->toBe('2026-03-10 23:59');
})->with([
    'unknown period' => [['period' => 'quarterly']],
    'missing period' => [[]],
    'empty period' => [['period' => '']],
]);

it('keeps the custom period but falls back to today when its dates are unusable', function (array $input) {
    $filter = app(DashboardFilterFactory::class)->make($input, memberWith([]));

    // The period stays "custom" on purpose: the view compares the typed dates
    // against localFrom()/localTo() to show the "range adjusted" warning.
    expect($filter->period)->toBe('custom')
        ->and($filter->localFrom()->format('Y-m-d H:i'))->toBe('2026-03-10 00:00')
        ->and($filter->localTo()->format('Y-m-d H:i'))->toBe('2026-03-10 23:59');
})->with([
    'no dates' => [['period' => 'custom']],
    'missing start' => [['period' => 'custom', 'dateTo' => '2026-03-05']],
    'missing end' => [['period' => 'custom', 'dateFrom' => '2026-03-01']],
    'unparseable start' => [['period' => 'custom', 'dateFrom' => 'not-a-date', 'dateTo' => '2026-03-05']],
]);

it('accepts a period name in any case', function () {
    $filter = app(DashboardFilterFactory::class)->make(['period' => 'YESTERDAY'], memberWith([]));

    expect($filter->period)->toBe('yesterday')
        ->and($filter->localFrom()->format('Y-m-d H:i'))->toBe('2026-03-09 00:00');
});

it('derives the previous window of exactly the same length immediately before', function (string $period) {
    $filter = app(DashboardFilterFactory::class)->make(['period' => $period], memberWith([]));
    $previous = $filter->previous();

    $length = $filter->from->diffInSeconds($filter->to) + 1;

    expect($previous)->not->toBeNull()
        ->and($previous->from->format('Y-m-d H:i:s'))
        ->toBe($filter->from->subSeconds($length)->format('Y-m-d H:i:s'))
        ->and($previous->to->format('Y-m-d H:i:s'))
        ->toBe($filter->from->subSecond()->format('Y-m-d H:i:s'))
        // Same number of seconds, and it ends the instant the current one starts.
        ->and($previous->from->diffInSeconds($previous->to) + 1)->toBe($length)
        ->and($previous->to->addSecond()->format('Y-m-d H:i:s'))->toBe($filter->from->format('Y-m-d H:i:s'))
        // The comparison keeps every scoping rule.
        ->and($previous->timezone)->toBe($filter->timezone)
        ->and($previous->utcOffsetSeconds)->toBe($filter->utcOffsetSeconds)
        ->and($previous->memberScopeIds)->toBe($filter->memberScopeIds)
        ->and($previous->memberDimension)->toBe($filter->memberDimension);
})->with(['today', 'yesterday', 'week', 'month', 'custom']);

it('has no previous window for the unbounded period', function () {
    $filter = app(DashboardFilterFactory::class)->make(['period' => 'all'], memberWith([]));

    expect($filter->previous())->toBeNull();
});

it('resolves the previous window once and reuses it', function () {
    $filter = app(DashboardFilterFactory::class)->make(['period' => 'today'], memberWith([]));

    expect($filter->previous())->toBe($filter->previous());
});

it('resolves the granularity from the window each period produces', function (string $period, string $granularity) {
    $filter = app(DashboardFilterFactory::class)->make(['period' => $period], memberWith([]));

    expect(DateBucket::granularityFor($filter->from, $filter->to))->toBe($granularity);
})->with([
    'today is hourly' => ['today', 'hour'],
    'yesterday is hourly' => ['yesterday', 'hour'],
    // 7 days inclusive.
    'the week is daily' => ['week', 'day'],
    // 10 days into the month.
    'month to date is daily' => ['month', 'day'],
]);

it('switches to monthly granularity past 62 days', function () {
    $factory = app(DashboardFilterFactory::class);
    $membership = memberWith([]);

    $granularity = function (string $from, string $to) use ($factory, $membership) {
        $filter = $factory->make(['period' => 'custom', 'dateFrom' => $from, 'dateTo' => $to], $membership);

        return DateBucket::granularityFor($filter->from, $filter->to);
    };

    expect($granularity('2026-01-01', '2026-03-02'))->toBe('day')   // 61 days
        ->and($granularity('2026-01-01', '2026-03-03'))->toBe('day')  // 62 days
        ->and($granularity('2026-01-01', '2026-03-04'))->toBe('month'); // 63 days
});

it('changes the hash when any filter input changes', function () {
    $factory = app(DashboardFilterFactory::class);

    // Both capabilities, so the requested dimension is actually honoured and the
    // dimension is a free input rather than something the factory overwrites.
    $membership = memberWith([
        StorePermissionEnum::STATS_TEAM_VIEW->value,
        StorePermissionEnum::STATS_CONFIRMATION->value,
        StorePermissionEnum::STATS_DELIVERY->value,
    ]);

    $other = StoreMembership::query()->create([
        'store_id' => $membership->store_id,
        'user_id' => $membership->user_id,
        'role' => 'STAFF',
        'is_active' => true,
    ]);

    $carrier = \App\Domains\Shipping\Models\ShippingProvider::query()->create([
        'store_id' => $membership->store_id,
        'name' => 'Carrier',
        'code' => 'c-'.uniqid(),
        'credentials' => [],
        'is_active' => true,
    ]);

    $baseline = [
        'period' => 'today',
        'carrierId' => $carrier->id,
        'memberId' => $membership->id,
        'memberDimension' => 'confirmation',
    ];

    $hash = fn (array $overrides = []) => $factory->make($overrides + $baseline, $membership)->hash();

    $reference = $hash();

    expect($hash())->toBe($reference)
        ->and($hash(['period' => 'yesterday']))->not->toBe($reference)
        ->and($hash(['carrierId' => null]))->not->toBe($reference)
        ->and($hash(['memberId' => $other->id]))->not->toBe($reference)
        ->and($hash(['memberDimension' => 'delivery']))->not->toBe($reference)
        // A different date range changes from/to, which the hash covers too.
        ->and($hash(['period' => 'custom', 'dateFrom' => '2026-03-01', 'dateTo' => '2026-03-05']))
        ->not->toBe($hash(['period' => 'custom', 'dateFrom' => '2026-03-02', 'dateTo' => '2026-03-05']));
});

it('gives an identical filter the same hash and separates different ones', function () {
    $window = fn (string $from, string $to) => new DashboardFilter(
        period: 'today',
        from: Carbon\CarbonImmutable::parse($from, DASHBOARD_FILTER_TZ)->utc(),
        to: Carbon\CarbonImmutable::parse($to, DASHBOARD_FILTER_TZ)->utc(),
        timezone: DASHBOARD_FILTER_TZ,
        utcOffsetSeconds: 3600,
    );

    $baseline = $window('2026-03-10', '2026-03-10 23:59:59')->hash();

    expect($window('2026-03-10', '2026-03-10 23:59:59')->hash())->toBe($baseline)
        // A different start.
        ->and($window('2026-03-11', '2026-03-11 23:59:59')->hash())->not->toBe($baseline)
        // The same dates in a different zone.
        ->and((new DashboardFilter(
            period: 'today',
            from: Carbon\CarbonImmutable::parse('2026-03-10 00:00:00', 'UTC'),
            to: Carbon\CarbonImmutable::parse('2026-03-10 23:59:59', 'UTC'),
            timezone: 'UTC',
            utcOffsetSeconds: 0,
        ))->hash())->not->toBe($baseline)
        // A different offset for the same instant is a different bucket layout.
        ->and((new DashboardFilter(
            period: 'today',
            from: Carbon\CarbonImmutable::parse('2026-03-10 00:00:00', DASHBOARD_FILTER_TZ)->utc(),
            to: Carbon\CarbonImmutable::parse('2026-03-10 23:59:59', DASHBOARD_FILTER_TZ)->utc(),
            timezone: DASHBOARD_FILTER_TZ,
            utcOffsetSeconds: 7200,
        ))->hash())->not->toBe($baseline);
});

it('serialises the window in UTC and exposes the local copy for display', function () {
    $filter = app(DashboardFilterFactory::class)->make(['period' => 'today'], memberWith([]));

    expect($filter->from->timezoneName)->toBe('UTC')
        ->and($filter->to->timezoneName)->toBe('UTC')
        ->and($filter->localFrom()->timezoneName)->toBe(DASHBOARD_FILTER_TZ)
        ->and($filter->toArray())
        ->toMatchArray([
            'period' => 'today',
            'timezone' => DASHBOARD_FILTER_TZ,
            'utcOffsetSeconds' => 3600,
            'carrierId' => null,
            'memberLocked' => true,
        ]);
});
