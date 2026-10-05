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

/** An aggregate row as DashboardTeamPerformanceQuery::run() returns it. */
function tpRow(?string $id, array $overrides = []): array
{
    return array_merge([
        'membership_id' => $id,
        'assigned' => 0, 'confirmed' => 0, 'pending' => 0, 'canceled' => 0,
        'delivered' => 0, 'returned' => 0, 'revenue' => 0.0,
    ], $overrides);
}

it('names each row after a member the user can actually see', function () {
    $rows = (new DashboardTeamPerformance)->present([
        tpRow('m1', ['assigned' => 3, 'confirmed' => 2]),
        tpRow('m2', ['assigned' => 1, 'confirmed' => 1]),
    ], tpFilter(), tpMembers(['m1' => 'Amir', 'm2' => 'Bella']));

    expect($rows)->toHaveCount(3)
        ->and($rows[0])->toMatchArray(['membership_id' => 'm1', 'type' => 'member', 'name' => 'Amir', 'assigned' => 3])
        ->and($rows[1])->toMatchArray(['membership_id' => 'm2', 'name' => 'Bella'])
        ->and($rows[2])->toMatchArray(['type' => 'total', 'name' => __('dashboard.team_total')]);
});

it('drops a row whose membership the user is not allowed to see', function () {
    $rows = (new DashboardTeamPerformance)->present([
        tpRow('m1', ['assigned' => 2]),
        tpRow('other-store', ['assigned' => 9]),
    ], tpFilter(), tpMembers(['m1' => 'Amir']));

    expect(array_column($rows, 'membership_id'))->toBe(['m1', null])
        ->and(array_column($rows, 'assigned'))->toBe([2, 2]);
});

it('reports a zero rate when nothing could be measured', function () {
    $rows = (new DashboardTeamPerformance)->present([
        tpRow('m1', ['assigned' => 0, 'delivered' => 0, 'returned' => 0]),
    ], tpFilter(['memberScopeIds' => ['m1']]), tpMembers(['m1' => 'Amir']));

    expect($rows[0])->toMatchArray([
        'confirmation_rate' => 0, 'delivery_rate' => 0, 'return_rate' => 0,
    ]);
});

it('rounds rates to whole percents and caps them at one hundred', function () {
    $rows = collect((new DashboardTeamPerformance)->present([
        tpRow('m1', ['assigned' => 3, 'confirmed' => 1]),
        // Impossible in practice, but a bad number must never leak a 333% bar.
        tpRow('m2', ['assigned' => 3, 'confirmed' => 40, 'delivered' => 9]),
    ], tpFilter(['memberScopeIds' => ['m1', 'm2']]), tpMembers(['m1' => 'Amir', 'm2' => 'Bella'])))
        ->keyBy('membership_id');

    expect($rows['m1']['confirmation_rate'])->toBe(33)
        ->and($rows['m2'])->toMatchArray(['confirmation_rate' => 100, 'delivery_rate' => 100]);
});

it('never lets the remainder columns go negative', function () {
    $rows = (new DashboardTeamPerformance)->present([
        tpRow('m1', ['assigned' => 1, 'confirmed' => 2, 'pending' => 1, 'canceled' => 1, 'delivered' => 2, 'returned' => 1]),
    ], tpFilter(['memberScopeIds' => ['m1']]), tpMembers(['m1' => 'Amir']));

    expect($rows[0]['other'])->toBe(0)->and($rows[0]['in_progress'])->toBe(0);
});

it('sorts by the metric the active view leads with, then by name', function (string $dimension, array $expected) {
    $rows = (new DashboardTeamPerformance)->present([
        tpRow('m1', ['confirmed' => 2, 'delivered' => 9]),
        tpRow('m2', ['confirmed' => 5, 'delivered' => 1]),
        tpRow(null, ['confirmed' => 3, 'delivered' => 4]),
    ], tpFilter(['memberDimension' => $dimension]), tpMembers(['m1' => 'Amir', 'm2' => 'Bella']));

    $actual = array_map(fn (array $row) => [$row['type'], $row['membership_id']], $rows);

    expect($actual)->toBe($expected);
})->with([
    'confirmation leads with confirmed' => [
        'confirmation',
        [['member', 'm2'], ['unassigned', null], ['member', 'm1'], ['total', null]],
    ],
    'delivery leads with delivered' => [
        'delivery',
        [['member', 'm1'], ['unassigned', null], ['member', 'm2'], ['total', null]],
    ],
]);

it('shows the unassigned row only when nobody is scoped', function (?array $scope, array $expected) {
    $rows = (new DashboardTeamPerformance)->present([
        tpRow(null, ['assigned' => 4]),
        tpRow('m1', ['assigned' => 2]),
    ], tpFilter(['memberScopeIds' => $scope]), tpMembers(['m1' => 'Amir']));

    expect(array_column($rows, 'type'))->toBe($expected);
})->with([
    'unrestricted store sees it' => [null, ['member', 'unassigned', 'total']],
    'a scoped member does not' => [['m1'], ['member']],
]);

it('shows the total for a team and hides it for the one member already shown', function (?array $scope, bool $withTotal) {
    $given = [
        tpRow('m1', ['assigned' => 2]),
        tpRow('m2', ['assigned' => 1]),
    ];

    // The query only ever returns rows inside the scope, so the input is cut
    // the same way before it reaches the view model.
    if ($scope !== null) {
        $given = array_values(array_filter($given, fn (array $row) => in_array($row['membership_id'], $scope, true)));
    }

    $rows = (new DashboardTeamPerformance)->present($given, tpFilter(['memberScopeIds' => $scope]), tpMembers(['m1' => 'Amir', 'm2' => 'Bella']));

    expect(in_array('total', array_column($rows, 'type'), true))->toBe($withTotal)
        ->and($rows)->toHaveCount($withTotal ? 3 : 1);
})->with([
    'every member' => [null, true],
    'a team of two' => [['m1', 'm2'], true],
    'one member picked' => [['m1'], false],
]);

it('sums the total across exactly the rows it shows', function () {
    $rows = (new DashboardTeamPerformance)->present([
        tpRow('m1', ['assigned' => 3, 'confirmed' => 2, 'pending' => 1, 'delivered' => 1, 'revenue' => 100.5]),
        tpRow('m2', ['assigned' => 1, 'confirmed' => 1, 'delivered' => 1, 'revenue' => 50.0]),
        tpRow('hidden', ['assigned' => 7, 'confirmed' => 7]),
    ], tpFilter(), tpMembers(['m1' => 'Amir', 'm2' => 'Bella']));

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
        'delivery_rate' => 50,
        'return_rate' => 0,
    ]);
});

it('keeps the selected member as a zero row when the period is empty for them', function () {
    $rows = (new DashboardTeamPerformance)->present(
        [],
        tpFilter(['memberId' => 'm1', 'memberScopeIds' => ['m1']]),
        tpMembers(['m1' => 'Amir', 'm2' => 'Bella']),
    );

    expect($rows)->toHaveCount(1)
        ->and($rows[0])->toMatchArray(['membership_id' => 'm1', 'name' => 'Amir', 'assigned' => 0, 'type' => 'member']);
});
