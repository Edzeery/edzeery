<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\DashboardTeamPerformance;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

// tests/Unit is not covered by the Pest.php bootstrap, so bind the case here.
uses(Tests\TestCase::class);

/** The filter shape the factory produces for an unrestricted store viewer. */
function tpFilter(array $overrides = []): DashboardFilter
{
    $args = array_merge([
        'period' => 'today',
        'from' => CarbonImmutable::parse('2026-03-09 23:00:00', 'UTC'),
        'to' => CarbonImmutable::parse('2026-03-10 22:59:59', 'UTC'),
        'timezone' => 'Africa/Algiers',
        'utcOffsetSeconds' => 3600,
    ], $overrides);

    return new DashboardFilter(...$args);
}

/** The visible membership collection the dashboard hands over: id => name. */
function tpMembers(array $pairs): Collection
{
    $members = [];

    foreach ($pairs as $id => $name) {
        $members[] = (object) ['id' => $id, 'name' => $name];
    }

    return collect($members);
}

/** A credit aggregate row (grouped by orders.confirmed_by_membership_id). */
function tpCredit(?string $id, array $overrides = []): array
{
    return array_merge([
        'membership_id' => $id,
        'confirmed' => 0, 'delivered' => 0, 'returned' => 0, 'revenue' => 0.0,
    ], $overrides);
}

/** A workload aggregate row (grouped by the assignment the tab walks). */
function tpWork(?string $id, array $overrides = []): array
{
    return array_merge([
        'membership_id' => $id,
        'assigned' => 0, 'pending' => 0, 'canceled' => 0,
    ], $overrides);
}

/** Shorthand: run the presenter with the given credit, work and filter. */
function tpPresent(array $credit, array $work, DashboardFilter $filter, array $members): array
{
    return (new DashboardTeamPerformance)->present($credit, $work, $filter, tpMembers($members));
}

it('names each row after a member the user can actually see', function () {
    $rows = tpPresent(
        [tpCredit('m1', ['confirmed' => 2]), tpCredit('m2', ['confirmed' => 1])],
        [tpWork('m1', ['assigned' => 3]), tpWork('m2', ['assigned' => 1])],
        tpFilter(),
        ['m1' => 'Amir', 'm2' => 'Bella'],
    );

    expect($rows)->toHaveCount(3)
        ->and($rows[0])->toMatchArray(['membership_id' => 'm1', 'type' => 'member', 'name' => 'Amir', 'assigned' => 3])
        ->and($rows[1])->toMatchArray(['membership_id' => 'm2', 'name' => 'Bella'])
        ->and($rows[2])->toMatchArray(['type' => 'total', 'name' => __('dashboard.team_total')]);
});

it('drops a row whose membership the user is not allowed to see', function () {
    $rows = tpPresent(
        [],
        [tpWork('m1', ['assigned' => 2]), tpWork('other-store', ['assigned' => 9])],
        tpFilter(),
        ['m1' => 'Amir'],
    );

    expect(array_column($rows, 'membership_id'))->toBe(['m1', null])
        ->and(array_column($rows, 'assigned'))->toBe([2, 2]);
});

it('reports a zero rate when nothing could be measured', function () {
    $rows = tpPresent(
        [tpCredit('m1')],
        [tpWork('m1')],
        tpFilter(['memberScopeIds' => ['m1']]),
        ['m1' => 'Amir'],
    );

    expect($rows[0])->toMatchArray([
        'confirmation_rate' => 0, 'delivery_rate' => 0, 'return_rate' => 0,
    ]);
});

it('rounds rates to whole percents and caps them at one hundred', function () {
    $rows = collect(tpPresent(
        [tpCredit('m1', ['confirmed' => 1]), tpCredit('m2', ['confirmed' => 40, 'delivered' => 9])],
        [tpWork('m1', ['assigned' => 3]), tpWork('m2', ['assigned' => 3])],
        tpFilter(['memberScopeIds' => ['m1', 'm2']]),
        ['m1' => 'Amir', 'm2' => 'Bella'],
    ))->keyBy('membership_id');

    expect($rows['m1']['confirmation_rate'])->toBe(33)
        // Confirmation is huge, so it clamps at 100; a bad number must never
        // leak a 1333% bar. Delivery is measured against the confirmed base,
        // so it stays a natural 9 / 40 = 23%.
        ->and($rows['m2'])->toMatchArray(['confirmation_rate' => 100, 'delivery_rate' => 23]);
});

it('never lets the remainder columns go negative', function () {
    $rows = tpPresent(
        [tpCredit('m1', ['confirmed' => 2, 'delivered' => 2, 'returned' => 1])],
        [tpWork('m1', ['assigned' => 1, 'pending' => 1, 'canceled' => 1])],
        tpFilter(['memberScopeIds' => ['m1']]),
        ['m1' => 'Amir'],
    );

    expect($rows[0]['other'])->toBe(0)->and($rows[0]['in_progress'])->toBe(0);
});

it('sorts by the metric the active view leads with, then by name', function (string $dimension, array $expected) {
    $rows = tpPresent(
        [
            tpCredit('m1', ['confirmed' => 2, 'delivered' => 9]),
            tpCredit('m2', ['confirmed' => 5, 'delivered' => 1]),
            tpCredit(null, ['confirmed' => 3, 'delivered' => 4]),
        ],
        [],
        tpFilter(['memberDimension' => $dimension]),
        ['m1' => 'Amir', 'm2' => 'Bella'],
    );

    $actual = array_map(fn (array $row) => [$row['type'], $row['membership_id']], $rows);

    expect($actual)->toBe($expected);
})->with([
    'confirmation leads with confirmed' => [
        'confirmation',
        [['member', 'm2'], ['unattributed', null], ['member', 'm1'], ['total', null]],
    ],
    'delivery leads with delivered' => [
        'delivery',
        [['member', 'm1'], ['unattributed', null], ['member', 'm2'], ['total', null]],
    ],
]);

it('shows the unattributed row only when nobody is scoped', function (?array $scope, array $expected) {
    $rows = tpPresent(
        [],
        [tpWork('m1', ['assigned' => 2]), tpWork(null, ['assigned' => 4])],
        tpFilter(['memberScopeIds' => $scope]),
        ['m1' => 'Amir'],
    );

    expect(array_column($rows, 'type'))->toBe($expected);
})->with([
    'unrestricted store sees it' => [null, ['member', 'unattributed', 'total']],
    'a scoped member does not' => [['m1'], ['member']],
]);

it('shows the total for a team and hides it for the one member already shown', function (?array $scope, bool $withTotal) {
    $givenCredit = [tpCredit('m1'), tpCredit('m2')];
    $givenWork = [tpWork('m1', ['assigned' => 2]), tpWork('m2', ['assigned' => 1])];

    // The query only ever returns rows inside the scope, so the input is cut
    // the same way before it reaches the view model.
    if ($scope !== null) {
        $givenCredit = array_values(array_filter($givenCredit, fn (array $row) => in_array($row['membership_id'], $scope, true)));
        $givenWork = array_values(array_filter($givenWork, fn (array $row) => in_array($row['membership_id'], $scope, true)));
    }

    $rows = tpPresent($givenCredit, $givenWork, tpFilter(['memberScopeIds' => $scope]), ['m1' => 'Amir', 'm2' => 'Bella']);

    expect(in_array('total', array_column($rows, 'type'), true))->toBe($withTotal)
        ->and($rows)->toHaveCount($withTotal ? 3 : 1);
})->with([
    'every member' => [null, true],
    'a team of two' => [['m1', 'm2'], true],
    'one member picked' => [['m1'], false],
]);

it('sums the total across exactly the rows it shows', function () {
    $rows = tpPresent(
        [
            tpCredit('m1', ['confirmed' => 2, 'delivered' => 1, 'revenue' => 100.5]),
            tpCredit('m2', ['confirmed' => 1, 'delivered' => 1, 'revenue' => 50.0]),
        ],
        [
            tpWork('m1', ['assigned' => 3, 'pending' => 1]),
            tpWork('m2', ['assigned' => 1]),
            tpWork('hidden', ['assigned' => 7]),
        ],
        tpFilter(),
        ['m1' => 'Amir', 'm2' => 'Bella'],
    );

    expect($rows[2])->toMatchArray([
        'type' => 'total',
        'assigned' => 4,
        'confirmed' => 3,
        'pending' => 1,
        'canceled' => 0,
        'delivered' => 2,
        'returned' => 0,
        'revenue' => 150.5,
        'confirmation_rate' => 75,
        // Conversion is measured against the confirmed base (§ 11).
        'delivery_rate' => round((2 / 3) * 100),
        'return_rate' => 0,
    ]);
});

it('keeps the selected member as a zero row when the period is empty for them', function () {
    $rows = tpPresent(
        [],
        [],
        tpFilter(['memberId' => 'm1', 'memberScopeIds' => ['m1']]),
        ['m1' => 'Amir', 'm2' => 'Bella'],
    );

    expect($rows)->toHaveCount(1)
        ->and($rows[0])->toMatchArray(['membership_id' => 'm1', 'name' => 'Amir', 'assigned' => 0, 'type' => 'member']);
});

it('renders the sentinel pick as one unattributed row that sums every work row', function () {
    $rows = tpPresent(
        [tpCredit(null, ['confirmed' => 2, 'delivered' => 1, 'revenue' => 300.0])],
        [
            tpWork(null, ['assigned' => 2, 'pending' => 1]),
            tpWork('m1', ['assigned' => 3, 'pending' => 1, 'canceled' => 1]),
        ],
        tpFilter(['memberId' => DashboardFilter::UNATTRIBUTED, 'memberScopeIds' => [DashboardFilter::UNATTRIBUTED], 'memberUnattributedOnly' => true]),
        ['m1' => 'Amir'],
    );

    expect($rows)->toHaveCount(1)
        ->and($rows[0])->toMatchArray([
            'membership_id' => null,
            'type' => 'unattributed',
            'name' => __('dashboard.team_unattributed'),
            'assigned' => 5,
            'pending' => 2,
            'canceled' => 1,
            'other' => 2,
            'confirmed' => 2,
            'delivered' => 1,
            'in_progress' => 1,
            'revenue' => 300.0,
            'confirmation_rate' => 40,
        ]);
});
