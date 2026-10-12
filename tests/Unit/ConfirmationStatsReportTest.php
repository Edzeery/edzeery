<?php

use App\Domains\Analytics\Support\ConfirmationRateDenominator;
use App\Domains\Analytics\Support\ConfirmationStatsGroups;
use App\Domains\Analytics\Support\ConfirmationStatsReport;

function csrRow(?string $member, string $key, int $total, int $deleted = 0): array
{
    return ['membership_id' => $member, 'status_key' => $key, 'total' => $total, 'deleted' => $deleted];
}

function csrReport(?array $denominator = null): ConfirmationStatsReport
{
    return new ConfirmationStatsReport(ConfirmationRateDenominator::resolve($denominator));
}

it('measures confirmed against the settled orders by default', function () {
    $rows = [
        csrRow(null, 'confirmed', 6),
        csrRow(null, 'preparing', 2),
        csrRow(null, 'canceled', 2),
        csrRow(null, 'no_answer_1', 0),
    ];

    $summary = csrReport()->build($rows);

    // 8 confirmed out of 8 + 2 canceled + 0 failed = 80%.
    expect($summary['confirmed'])->toBe(8)
        ->and($summary['denominator'])->toBe(10)
        ->and($summary['rate'])->toBe(80)
        ->and($summary['groups'][ConfirmationStatsGroups::CANCELED]['total'])->toBe(2);
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
        ->and($card['rate'])->toBe(83)
        // A single member row is build() over exactly that member's rows.
        ->and($members['b'])->toBe($report->build([csrRow('b', 'confirmed', 5), csrRow('b', 'canceled', 1)]))
        ->and($members['b']['rate'])->toBe(83);
});

it('reopens the line when the store asks for the wider preset', function () {
    $rows = [
        csrRow(null, 'confirmed', 8),
        csrRow(null, 'canceled', 1),
        csrRow(null, 'failed', 0),
        csrRow(null, 'no_answer_1', 1),
        csrRow(null, 'pending', 5),
        csrRow(null, 'delivered', 45),
    ];

    $recommended = csrReport()->build($rows);
    $wide = csrReport(ConfirmationRateDenominator::PRESET_EXCLUDE_DUPLICATE_AND_DEFERRED)->build($rows);

    // D12: the delivered order stays inside the numerator (it left the desk
    // confirmed), so the recommended line is 8 + 45 over 8 + 45 + 1 + 1 = 96%.
    // The wider preset also carries the pending bucket: 53 over 60 = 88%.
    expect($recommended['denominator'])->toBe(55)
        ->and($recommended['rate'])->toBe(96)
        ->and($wide['denominator'])->toBe(60)
        ->and($wide['rate'])->toBe(88);
});

it('reports a zero rate instead of dividing by nothing', function () {
    expect(csrReport()->build([])['rate'])->toBe(0)
        ->and(csrReport()->build([csrRow(null, 'postponed', 4)])['rate'])->toBe(0);
});
