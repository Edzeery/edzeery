<?php

use App\Domains\Analytics\Support\ConfirmationRate;
use App\Domains\Analytics\Support\ConfirmationStatsGroups;
use App\Domains\Analytics\Support\ConfirmationStatsReport;

function csrRow(?string $member, string $key, int $total, int $deleted = 0): array
{
    return ['membership_id' => $member, 'status_key' => $key, 'total' => $total, 'deleted' => $deleted];
}

function csrReport(string $kind = ConfirmationRate::GROSS, array $excluded = []): ConfirmationStatsReport
{
    return new ConfirmationStatsReport($kind, $excluded);
}

it('measures the gross rate over every order created in the window', function () {
    $rows = [
        csrRow(null, 'confirmed', 6),
        csrRow(null, 'preparing', 2),
        csrRow(null, 'canceled', 2),
        csrRow(null, 'no_answer_1', 4),
        csrRow(null, 'pending', 6),
    ];

    $summary = csrReport()->build($rows);

    // 8 confirmed (confirmed + moved-on) of every 20 created = 40%.
    expect($summary['kind'])->toBe(ConfirmationRate::GROSS)
        ->and($summary['confirmed'])->toBe(8)
        ->and($summary['denominator'])->toBe(20)
        ->and($summary['rate'])->toBe(40)
        ->and($summary['groups'][ConfirmationStatsGroups::CANCELED]['total'])->toBe(2);
});

it('measures the net rate over the settled desk outcomes only', function () {
    $rows = [
        csrRow(null, 'confirmed', 8),
        csrRow(null, 'canceled', 2),
        csrRow(null, 'no_answer_1', 9),
        csrRow(null, 'pending', 5),
    ];

    $summary = csrReport(ConfirmationRate::NET)->build($rows);

    // 8 confirmed of (8 + 2) settled = 80%; the pending and failed stay out.
    expect($summary['kind'])->toBe(ConfirmationRate::NET)
        ->and($summary['confirmed'])->toBe(8)
        ->and($summary['denominator'])->toBe(10)
        ->and($summary['rate'])->toBe(80);
});

it('drops an excluded status key from the gross denominator', function () {
    $rows = [
        csrRow(null, 'confirmed', 2),
        csrRow(null, 'canceled', 1),
        csrRow(null, 'no_answer_1', 7),
    ];

    $plain = csrReport()->build($rows);
    $excluded = csrReport(ConfirmationRate::GROSS, ['no_answer_1'])->build($rows);

    // 2 of 10 = 20%, then no_answer_1 leaves the line: 2 of 3 = 67%.
    expect($plain['denominator'])->toBe(10)
        ->and($plain['rate'])->toBe(20)
        ->and($excluded['denominator'])->toBe(3)
        ->and($excluded['rate'])->toBe(67)
        ->and($excluded['excluded'])->toBe(7);
});

it('never lets a trashed order move the rate but still counts it in the bucket', function () {
    $rows = [
        csrRow(null, 'confirmed', 5, 3),
        csrRow(null, 'canceled', 1, 2),
    ];

    $summary = csrReport()->build($rows);

    expect($summary['confirmed'])->toBe(5)
        ->and($summary['denominator'])->toBe(6)
        ->and($summary['rate'])->toBe(83)
        ->and($summary['groups'][ConfirmationStatsGroups::CONFIRMED]['deleted'])->toBe(3);
});

it('keeps the headline card equal to the team total and to the sum of its rows', function () {
    $rows = [
        csrRow('a', 'confirmed', 3),
        csrRow('a', 'no_answer_1', 1),
        csrRow('b', 'confirmed', 5),
        csrRow('b', 'canceled', 1),
        csrRow('c', 'postponed', 9),
        csrRow('', 'confirmed', 2),
    ];

    $report = csrReport();
    $card = $report->build($rows);
    $members = $report->perMember($rows);

    $confirmed = array_sum(array_column($members, 'confirmed'));
    $denominator = array_sum(array_column($members, 'denominator'));

    expect($card['confirmed'])->toBe($confirmed)
        ->and($card['denominator'])->toBe($denominator)
        // A single member row is build() over exactly that member's rows.
        ->and($members['b'])->toBe($report->build([csrRow('b', 'confirmed', 5), csrRow('b', 'canceled', 1)]))
        ->and($members['b']['rate'])->toBe(83)
        ->and($members['b']['kind'])->toBe(ConfirmationRate::GROSS);
});

it('flags the groups that sit outside the rate', function () {
    $gross = csrReport()->build([]);

    // Nothing is outside on a bare gross basis.
    expect(array_filter($gross['outside']))->toBe([]);

    // Excluding every failed status takes the whole failed bucket out.
    $excluded = csrReport(ConfirmationRate::GROSS, ConfirmationStatsGroups::keys(ConfirmationStatsGroups::FAILED))->build([]);
    expect($excluded['outside'][ConfirmationStatsGroups::FAILED])->toBeTrue()
        ->and($excluded['outside'][ConfirmationStatsGroups::PENDING])->toBeFalse();

    // The net basis leaves every desk bucket but confirmed/canceled out.
    $net = csrReport(ConfirmationRate::NET)->build([]);
    expect($net['outside'][ConfirmationStatsGroups::PENDING])->toBeTrue()
        ->and($net['outside'][ConfirmationStatsGroups::CONFIRMED])->toBeFalse()
        ->and($net['outside'][ConfirmationStatsGroups::CANCELED])->toBeFalse();
});

it('reports a zero rate instead of dividing by nothing', function () {
    expect(csrReport()->build([])['rate'])->toBe(0)
        ->and(csrReport()->build([csrRow(null, 'postponed', 4)])['rate'])->toBe(0)
        ->and(csrReport(ConfirmationRate::NET)->build([csrRow(null, 'postponed', 4)])['rate'])->toBe(0);
});
