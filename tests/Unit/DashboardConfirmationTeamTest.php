<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\ConfirmationStatsGroups;
use App\Domains\Analytics\Support\DashboardConfirmationTeam;

/*
|--------------------------------------------------------------------------
| PHASE 39-M5 - confirmation team presenter
|--------------------------------------------------------------------------
|
| The presenter turns the per-member summaries of ConfirmationStatsReport into
| the rows of the confirmation team section. Deleted orders travel in their own
| column and never enter the rate (D7); the members without/with-trash toggle
| only decides which members are named. No database: the summaries are built by
| hand so the maths is asserted in isolation.
|
*/

/** A summary whose group totals are given per group key. */
function ctSummary(array $totals, array $deleted = [], ?int $confirmed = null, ?int $denominator = null): array
{
    $groups = [];

    foreach (ConfirmationStatsGroups::all() as $group) {
        $groups[$group] = ['total' => $totals[$group] ?? 0, 'deleted' => $deleted[$group] ?? 0];
    }

    $confirmed ??= $totals[ConfirmationStatsGroups::CONFIRMED] ?? 0;
    $denominator ??= ($totals[ConfirmationStatsGroups::CONFIRMED] ?? 0)
        + ($totals[ConfirmationStatsGroups::CANCELED] ?? 0)
        + ($totals[ConfirmationStatsGroups::FAILED] ?? 0);

    return [
        'groups' => $groups,
        'confirmed' => $confirmed,
        'denominator' => $denominator,
        'rate' => $denominator > 0 ? (int) round(($confirmed / $denominator) * 100) : 0,
    ];
}

function ctFilter(array $overrides = []): DashboardFilter
{
    return new DashboardFilter(
        period: 'today',
        from: null,
        to: null,
        timezone: 'UTC',
        utcOffsetSeconds: 0,
        memberId: $overrides['memberId'] ?? null,
        memberScopeIds: $overrides['memberScopeIds'] ?? null,
        memberUnattributedOnly: $overrides['memberUnattributedOnly'] ?? false,
    );
}

function ctPresent(array $summaries, array $members = [], array $overrides = []): array
{
    $collection = collect(array_map(
        fn (array $m) => (object) ['id' => $m[0], 'name' => $m[1], 'deleted' => $m[2] ?? false],
        $members,
    ));

    return app(DashboardConfirmationTeam::class)->present($summaries, ctFilter($overrides), $collection);
}

function ctRowOf(array $rows, string $type): ?array
{
    foreach ($rows as $row) {
        if ($row['type'] === $type) {
            return $row;
        }
    }

    return null;
}

test('a member row sums its orders and keeps deleted ones in their own column', function () {
    $rows = ctPresent([
        'm1' => ctSummary(
            [ConfirmationStatsGroups::CONFIRMED => 3, ConfirmationStatsGroups::CANCELED => 2],
            [ConfirmationStatsGroups::CANCELED => 1],
        ),
    ], [['m1', 'Alpha']]);

    $alpha = ctRowOf($rows, 'member');

    expect($alpha['name'])->toBe('Alpha')
        ->and($alpha['total'])->toBe(5)
        ->and($alpha['trash'])->toBe(1)
        ->and($alpha['deleted_member'])->toBeFalse();
});

test('the total row sums confirmed and denominator so its rate is the aggregate rate', function () {
    $rows = ctPresent([
        'm1' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 2], confirmed: 2, denominator: 4),
        'm2' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 3], confirmed: 3, denominator: 6),
    ], [['m1', 'Alpha'], ['m2', 'Beta']]);

    $total = ctRowOf($rows, 'total');

    // 5 of 10 => 50%; one member alone would have been 50% too, so pick an
    // asymmetric split to prove it is summed, not copied.
    expect($total['confirmed'])->toBe(5)
        ->and($total['denominator'])->toBe(10)
        ->and($total['rate'])->toBe(50);
});

test('the total rate is not simply the first row rate', function () {
    $rows = ctPresent([
        'm1' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 9, ConfirmationStatsGroups::CANCELED => 1]),
        'm2' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 0, ConfirmationStatsGroups::CANCELED => 10]),
    ], [['m1', 'Alpha'], ['m2', 'Beta']]);

    expect(ctRowOf($rows, 'member')['name'])->toBe('Alpha')
        ->and(ctRowOf($rows, 'total')['rate'])->toBe(45);
});

test('rows are sorted by order count descending, then by name', function () {
    $rows = ctPresent([
        'm1' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 1]),
        'm2' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 5]),
        'm3' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 1]),
    ], [['m1', 'Zoe'], ['m2', 'Beta'], ['m3', 'Ada']]);

    $names = array_column(array_slice($rows, 0, 3), 'name');

    expect($names)->toBe(['Beta', 'Ada', 'Zoe']);
});

test('the unattributed bucket is a row under an unrestricted section', function () {
    $rows = ctPresent([
        '' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 2]),
        'm1' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 1]),
    ], [['m1', 'Alpha']]);

    expect(ctRowOf($rows, 'member')['name'])->toBe('Alpha')
        ->and(ctRowOf($rows, 'unattributed')['total'])->toBe(2)
        ->and(ctRowOf($rows, 'total')['total'])->toBe(3);
});

test('the sentinel pick returns only the unattributed row', function () {
    $rows = ctPresent([
        '' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 2]),
        'm1' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 1]),
    ], [['m1', 'Alpha']], ['memberId' => DashboardFilter::UNATTRIBUTED, 'memberUnattributedOnly' => true]);

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['type'])->toBe('unattributed');
});

test('a membership outside the visible list is dropped rather than named', function () {
    $rows = ctPresent([
        'm1' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 1]),
        'foreign' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 4]),
    ], [['m1', 'Alpha']]);

    expect(ctRowOf($rows, 'member')['name'])->toBe('Alpha')
        ->and(collect($rows)->pluck('name'))->not->toContain('foreign');
});

test('a soft-deleted member is named and flagged as deleted', function () {
    $rows = ctPresent([
        'm1' => ctSummary([ConfirmationStatsGroups::CONFIRMED => 1]),
    ], [['m1', 'Gone', true]]);

    $row = ctRowOf($rows, 'member');

    expect($row['name'])->toBe('Gone')
        ->and($row['deleted_member'])->toBeTrue();
});

test('a picked member with no orders keeps a zeroed row and no total', function () {
    $rows = ctPresent([], [['m1', 'Alpha']], ['memberId' => 'm1', 'memberScopeIds' => ['m1']]);

    $alpha = ctRowOf($rows, 'member');

    expect($alpha['name'])->toBe('Alpha')
        ->and($alpha['total'])->toBe(0)
        ->and(ctRowOf($rows, 'total'))->toBeNull();
});
