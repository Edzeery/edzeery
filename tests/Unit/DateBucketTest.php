<?php

use App\Domains\Analytics\Support\DateBucket;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(Tests\TestCase::class)->use(RefreshDatabase::class);

const DATE_BUCKET_TZ = 'Africa/Algiers';

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

it('spells the bucket expression the way each driver expects', function (string $driver, string $granularity, int $offset, string $expected) {
    expect(app(DateBucket::class)->bucketExpression($driver, $granularity, $offset))
        ->toBe($expected);
})->with([
    'sqlite hour' => [
        'driver' => 'sqlite', 'granularity' => 'hour', 'offset' => 3600,
        'expected' => "strftime('%Y-%m-%d %H', orders.created_at, '3600 seconds') as bucket",
    ],
    'sqlite day' => [
        'driver' => 'sqlite', 'granularity' => 'day', 'offset' => 3600,
        'expected' => "strftime('%Y-%m-%d', orders.created_at, '3600 seconds') as bucket",
    ],
    'sqlite month' => [
        'driver' => 'sqlite', 'granularity' => 'month', 'offset' => 3600,
        'expected' => "strftime('%Y-%m', orders.created_at, '3600 seconds') as bucket",
    ],
    // Negative offsets must keep their sign in both the shift and the interval.
    'sqlite negative offset' => [
        'driver' => 'sqlite', 'granularity' => 'day', 'offset' => -18000,
        'expected' => "strftime('%Y-%m-%d', orders.created_at, '-18000 seconds') as bucket",
    ],
    'mysql hour' => [
        'driver' => 'mysql', 'granularity' => 'hour', 'offset' => 3600,
        'expected' => "DATE_FORMAT(DATE_ADD(orders.created_at, INTERVAL 3600 SECOND), 'Y-m-d H') as bucket",
    ],
    'mysql day' => [
        'driver' => 'mysql', 'granularity' => 'day', 'offset' => 3600,
        'expected' => "DATE_FORMAT(DATE_ADD(orders.created_at, INTERVAL 3600 SECOND), 'Y-m-d') as bucket",
    ],
    'mysql month' => [
        'driver' => 'mysql', 'granularity' => 'month', 'offset' => 3600,
        'expected' => "DATE_FORMAT(DATE_ADD(orders.created_at, INTERVAL 3600 SECOND), 'Y-m') as bucket",
    ],
    'mariadb shares the mysql spelling' => [
        'driver' => 'mariadb', 'granularity' => 'day', 'offset' => 0,
        'expected' => "DATE_FORMAT(DATE_ADD(orders.created_at, INTERVAL 0 SECOND), 'Y-m-d') as bucket",
    ],
    'pgsql hour' => [
        'driver' => 'pgsql', 'granularity' => 'hour', 'offset' => 3600,
        'expected' => "to_char(orders.created_at + interval '3600 seconds', 'YYYY-MM-DD HH24') as bucket",
    ],
    'pgsql day' => [
        'driver' => 'pgsql', 'granularity' => 'day', 'offset' => 3600,
        'expected' => "to_char(orders.created_at + interval '3600 seconds', 'YYYY-MM-DD') as bucket",
    ],
    'pgsql month' => [
        'driver' => 'pgsql', 'granularity' => 'month', 'offset' => 3600,
        'expected' => "to_char(orders.created_at + interval '3600 seconds', 'YYYY-MM') as bucket",
    ],
    // An unknown driver still emits something runnable rather than throwing.
    'unknown driver' => [
        'driver' => 'sqlsrv', 'granularity' => 'day', 'offset' => 0,
        'expected' => "DATE_FORMAT(orders.created_at, 'Y-m-d') as bucket",
    ],
]);

it('picks the granularity from the inclusive window length', function (string $from, string $to, string $granularity) {
    expect(DateBucket::granularityFor(
        CarbonImmutable::parse($from, 'UTC'),
        CarbonImmutable::parse($to, 'UTC'),
    ))->toBe($granularity);
})->with([
    'a single instant' => ['2026-03-10 08:00:00', '2026-03-10 08:00:00', 'hour'],
    'part of a day' => ['2026-03-10 08:00:00', '2026-03-10 20:00:00', 'hour'],
    'exactly 24 hours' => ['2026-03-10 00:00:00', '2026-03-10 23:59:59', 'hour'],
    'just over 24 hours is two days' => ['2026-03-10 00:00:00', '2026-03-11 00:00:00', 'day'],
    '2 days' => ['2026-03-01 00:00:00', '2026-03-02 23:59:59', 'day'],
    '61 days' => ['2026-01-01 00:00:00', '2026-03-02 00:00:00', 'day'],
    '62 days' => ['2026-01-01 00:00:00', '2026-03-03 00:00:00', 'day'],
    '63 days' => ['2026-01-01 00:00:00', '2026-03-04 00:00:00', 'month'],
    'a whole year' => ['2025-01-01 00:00:00', '2026-01-01 00:00:00', 'month'],
]);

it('reads the granularity the same way for a reversed window', function () {
    $from = CarbonImmutable::parse('2026-03-10 00:00:00', 'UTC');
    $to = CarbonImmutable::parse('2026-03-12 00:00:00', 'UTC');

    expect(DateBucket::granularityFor($from, $to))->toBe('day')
        ->and(DateBucket::granularityFor($to, $from))->toBe('day')
        ->and(DateBucket::granularityFor($to, $from))->toBe(DateBucket::granularityFor($from, $to));
});

/**
 * Seeds one store plus the given UTC instants and runs the real SQLite bucket
 * expression over them, returning bucket key => revenue/orders rows. The SQL and
 * the PHP axis are then compared against the same database rather than against
 * hand-written expectations. Everything is created per call because
 * RefreshDatabase rolls the schema back between tests.
 *
 * @return array<string, object>
 */
function sqliteBuckets(array $instants, string $granularity, int $offset): array
{
    $userId = \App\Models\User::query()->create([
        'name' => 'Bucket',
        'email' => 'bucket-'.uniqid().'@example.com',
        'password' => bcrypt('password'),
    ])->id;

    $statusId = DB::table('statuses')->where('type', 'order')->where('key', 'delivered')->value('id');

    if ($statusId === null) {
        $statusId = (string) Str::ulid();
        DB::table('statuses')->insert([
            'id' => $statusId,
            'type' => 'order',
            'key' => 'delivered',
            'label' => 'Delivered',
            'is_system' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $storeId = (string) Str::ulid();
    DB::table('stores')->insert([
        'id' => $storeId,
        'name' => 'Bucket Store',
        'slug' => 'bucket-'.uniqid(),
        'user_id' => $userId,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $expression = app(DateBucket::class)->bucketExpression('sqlite', $granularity, $offset);

    foreach ($instants as $index => $instant) {
        DB::table('orders')->insert([
            'id' => (string) Str::ulid(),
            'store_id' => $storeId,
            'status_id' => $statusId,
            'number' => 'ORD-'.$index,
            'total_amount' => 100,
            'created_at' => $instant,
            'updated_at' => $instant,
        ]);
    }

    return DB::table('orders')
        ->selectRaw($expression)
        ->selectRaw('COUNT(*) as orders')
        ->selectRaw('SUM(orders.total_amount) as revenue')
        ->where('store_id', $storeId)
        ->whereNull('deleted_at')
        ->groupBy('bucket')
        ->orderBy('bucket')
        ->get()
        ->keyBy('bucket')
        ->all();
}

it('zero-fills an hourly axis across the whole local day', function () {
    $from = CarbonImmutable::parse('2026-03-10 00:00:00', DATE_BUCKET_TZ)->utc();
    $to = CarbonImmutable::parse('2026-03-10 23:59:59', DATE_BUCKET_TZ)->utc();

    // 08:00 UTC is 09:00 in Algiers and 09:30 UTC is 10:30 local.
    $rows = sqliteBuckets([
        '2026-03-10 08:00:00',
        '2026-03-10 09:30:00',
    ], 'hour', 3600);

    $series = app(DateBucket::class)->generateSeries($from, $to, 'sqlite', DATE_BUCKET_TZ, 3600, fn () => $rows);

    expect($series['labels'])->toHaveCount(24)
        ->and($series['labels'][0])->toBe('00:00')
        ->and($series['labels'][23])->toBe('23:00')
        // Every point exists, so the chart has no gaps.
        ->and($series['orders'])->toHaveCount(24)
        ->and($series['revenue'])->toHaveCount(24)
        // Only the two buckets that hold an order are non-zero, and they sit on
        // the local hours the instants map to.
        ->and($series['orders'][9])->toBe(1)
        ->and($series['orders'][10])->toBe(1)
        ->and($series['orders'][8])->toBe(0)
        ->and(array_sum($series['orders']))->toBe(2)
        ->and($series['revenue'][9])->toBe(100.0)
        ->and($series['revenue'][0])->toBe(0.0)
        ->and($series['revenue'][23])->toBe(0.0);
});

it('zero-fills a daily axis and labels it d/m', function () {
    $from = CarbonImmutable::parse('2026-03-01 00:00:00', DATE_BUCKET_TZ)->utc();
    $to = CarbonImmutable::parse('2026-03-07 23:59:59', DATE_BUCKET_TZ)->utc();

    $rows = sqliteBuckets([
        '2026-03-02 10:00:00',
        '2026-03-05 10:00:00',
        '2026-03-05 18:00:00',
    ], 'day', 3600);

    $series = app(DateBucket::class)->generateSeries($from, $to, 'sqlite', DATE_BUCKET_TZ, 3600, fn () => $rows);

    expect($series['labels'])->toBe(['01/03', '02/03', '03/03', '04/03', '05/03', '06/03', '07/03'])
        ->and($series['orders'])->toBe([0, 1, 0, 0, 2, 0, 0])
        ->and($series['revenue'])->toBe([0.0, 100.0, 0.0, 0.0, 200.0, 0.0, 0.0]);
});

it('zero-fills a monthly axis and labels it m/Y', function () {
    $from = CarbonImmutable::parse('2025-11-01 00:00:00', DATE_BUCKET_TZ)->utc();
    $to = CarbonImmutable::parse('2026-02-28 23:59:59', DATE_BUCKET_TZ)->utc();

    $rows = sqliteBuckets([
        '2025-12-15 10:00:00',
        '2026-01-05 10:00:00',
        '2026-01-20 10:00:00',
        '2026-01-25 10:00:00',
    ], 'month', 3600);

    $series = app(DateBucket::class)->generateSeries($from, $to, 'sqlite', DATE_BUCKET_TZ, 3600, fn () => $rows);

    expect($series['labels'])->toBe(['11/2025', '12/2025', '01/2026', '02/2026'])
        ->and($series['orders'])->toBe([0, 1, 3, 0])
        ->and($series['revenue'])->toBe([0.0, 100.0, 300.0, 0.0]);
});

it('moves a UTC instant into the local day it belongs to', function () {
    // 2026-03-09 23:30 UTC is 00:30 on 2026-03-10 in Algiers (UTC+1).
    $instant = '2026-03-09 23:30:00';

    $asLocal = sqliteBuckets([$instant], 'hour', 3600);
    $asUtc = sqliteBuckets([$instant], 'hour', 0);

    $bucket = app(DateBucket::class);

    $algiers = $bucket->generateSeries(
        CarbonImmutable::parse('2026-03-10 00:00:00', DATE_BUCKET_TZ)->utc(),
        CarbonImmutable::parse('2026-03-10 23:59:59', DATE_BUCKET_TZ)->utc(),
        'sqlite', DATE_BUCKET_TZ, 3600,
        fn () => $asLocal,
    );

    // Read as Algiers the order belongs to the first hour of the local day.
    expect($algiers['labels'][0])->toBe('00:00')
        ->and($algiers['orders'][0])->toBe(1)
        ->and($algiers['orders'][23])->toBe(0)
        ->and($algiers['revenue'][0])->toBe(100.0);

    // The same instant read as UTC belongs to the last hour of the previous day.
    $utc = $bucket->generateSeries(
        CarbonImmutable::parse('2026-03-09 00:00:00', 'UTC'),
        CarbonImmutable::parse('2026-03-09 23:59:59', 'UTC'),
        'sqlite', 'UTC', 0,
        fn () => $asUtc,
    );

    expect($utc['orders'])->toHaveCount(24)
        ->and($utc['orders'][23])->toBe(1)   // 2026-03-09 23:00
        ->and($utc['orders'][0])->toBe(0)
        ->and($utc['revenue'][23])->toBe(100.0);
});

it('walks a whole-day bucket boundary with the store offset', function () {
    // Two instants 30 minutes either side of UTC midnight.
    $instants = ['2026-03-09 23:30:00', '2026-03-10 00:30:00'];

    $bucket = app(DateBucket::class);

    // Bucketed for Algiers, both instants are 2026-03-10 locally (00:30 and
    // 01:30), so they share one daily bucket and the day before stays empty.
    $asLocal = $bucket->generateSeries(
        CarbonImmutable::parse('2026-03-09 00:00:00', DATE_BUCKET_TZ)->utc(),
        CarbonImmutable::parse('2026-03-11 23:59:59', DATE_BUCKET_TZ)->utc(),
        'sqlite', DATE_BUCKET_TZ, 3600,
        fn () => sqliteBuckets($instants, 'day', 3600),
    );

    expect($asLocal['labels'])->toBe(['09/03', '10/03', '11/03'])
        ->and($asLocal['orders'])->toBe([0, 2, 0]);

    // Bucketed for UTC they fall either side of midnight instead.
    $asUtc = $bucket->generateSeries(
        CarbonImmutable::parse('2026-03-09 00:00:00', 'UTC'),
        CarbonImmutable::parse('2026-03-11 00:00:00', 'UTC'),
        'sqlite', 'UTC', 0,
        fn () => sqliteBuckets($instants, 'day', 0),
    );

    expect($asUtc['orders'])->toBe([1, 1, 0]);
});

it('handles an offset with no daylight saving, in either direction', function (string $timezone, int $offset, string $utcInstant, string $localFrom, int $points, int $index, array $labels) {
    $rows = sqliteBuckets([$utcInstant], 'hour', $offset);

    $series = app(DateBucket::class)->generateSeries(
        CarbonImmutable::parse($localFrom, $timezone)->utc(),
        CarbonImmutable::parse($localFrom, $timezone)->endOfDay()->utc(),
        'sqlite', $timezone, $offset,
        fn () => $rows,
    );

    expect($series['labels'])->toBe($labels)
        ->and($series['labels'])->toHaveCount($points)
        ->and($series['orders'])->toHaveCount($points)
        ->and($series['orders'][$index])->toBe(1)
        ->and(array_sum($series['orders']))->toBe(1);
})->with([
    // UTC+8 has no DST: local midnight is 16:00 UTC the day before.
    'shanghai' => [
        'timezone' => 'Asia/Shanghai', 'offset' => 28800, 'utcInstant' => '2026-03-09 16:30:00',
        'localFrom' => '2026-03-10 00:00:00', 'points' => 24, 'index' => 0,
        'labels' => array_map(fn ($h) => sprintf('%02d:00', $h), range(0, 23)),
    ],
    // UTC-5 on a winter date, when New York is genuinely on EST. A summer date
    // would be EDT (UTC-4) and the fixed offset would silently disagree with the
    // zone, which is exactly what utcOffsetSeconds is meant to prevent.
    'new york' => [
        'timezone' => 'America/New_York', 'offset' => -18000, 'utcInstant' => '2026-01-15 05:30:00',
        'localFrom' => '2026-01-15 00:00:00', 'points' => 24, 'index' => 0,
        'labels' => array_map(fn ($h) => sprintf('%02d:00', $h), range(0, 23)),
    ],
    // A half-hour offset still lines up.
    'kolkata' => [
        'timezone' => 'Asia/Kolkata', 'offset' => 19800, 'utcInstant' => '2026-03-09 18:30:00',
        'localFrom' => '2026-03-10 00:00:00', 'points' => 24, 'index' => 0,
        'labels' => array_map(fn ($h) => sprintf('%02d:00', $h), range(0, 23)),
    ],
]);

it('still draws a zero-filled axis when nothing matches the window', function () {
    $bucket = app(DateBucket::class);

    // An empty fetcher still yields a full axis, so the chart does not jump.
    $noRows = $bucket->generateSeries(
        CarbonImmutable::parse('2026-03-10 00:00:00', DATE_BUCKET_TZ)->utc(),
        CarbonImmutable::parse('2026-03-10 23:59:59', DATE_BUCKET_TZ)->utc(),
        'sqlite', DATE_BUCKET_TZ, 3600,
        fn () => [],
    );

    expect($noRows['labels'])->toHaveCount(24)
        ->and(array_sum($noRows['orders']))->toBe(0)
        ->and(array_sum($noRows['revenue']))->toBe(0.0);

    // Rows exist but sit outside the window, so every point stays zero.
    $rows = sqliteBuckets(['2020-01-01 10:00:00'], 'day', 3600);

    $outside = $bucket->generateSeries(
        CarbonImmutable::parse('2026-03-01 00:00:00', DATE_BUCKET_TZ)->utc(),
        CarbonImmutable::parse('2026-03-02 00:00:00', DATE_BUCKET_TZ)->utc(),
        'sqlite', DATE_BUCKET_TZ, 3600,
        fn () => $rows,
    );

    expect($outside['labels'])->toBe(['01/03', '02/03'])
        ->and($outside['orders'])->toBe([0, 0])
        ->and($outside['revenue'])->toBe([0.0, 0.0]);
});

it('defaults a missing revenue or orders column to zero', function () {
    $series = app(DateBucket::class)->generateSeries(
        CarbonImmutable::parse('2026-03-10 00:00:00', DATE_BUCKET_TZ)->utc(),
        CarbonImmutable::parse('2026-03-10 23:59:59', DATE_BUCKET_TZ)->utc(),
        'sqlite', DATE_BUCKET_TZ, 3600,
        fn () => [
            (object) ['bucket' => '2026-03-10 08', 'revenue' => null, 'orders' => null],
        ],
    );

    expect($series['orders'][8])->toBe(0)
        ->and($series['revenue'][8])->toBe(0.0)
        ->and($series['trend']->get('2026-03-10 08'))->toBe(['revenue' => 0.0, 'orders' => 0]);
});

it('still produces an axis when handed a reversed window', function () {
    // Unreachable from DashboardSeriesQuery, which only ever passes a window the
    // factory has already normalised. The swap snaps to UTC day boundaries and
    // then walks local days, so the axis keeps the day granularity and includes
    // the partial day on each edge rather than returning nothing.
    $series = app(DateBucket::class)->generateSeries(
        CarbonImmutable::parse('2026-03-05 23:59:59', DATE_BUCKET_TZ)->utc(),
        CarbonImmutable::parse('2026-03-01 00:00:00', DATE_BUCKET_TZ)->utc(),
        'sqlite', DATE_BUCKET_TZ, 3600,
        fn () => [],
    );

    expect($series['labels'])->toBe(['28/02', '01/03', '02/03', '03/03', '04/03', '05/03', '06/03'])
        ->and($series['orders'])->toHaveCount(7)
        ->and(array_sum($series['orders']))->toBe(0);
});
