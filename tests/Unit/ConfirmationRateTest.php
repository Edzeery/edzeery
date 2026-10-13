<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\ConfirmationRate;
use App\Domains\Analytics\Support\ConfirmationStatsGroups;

/*
|--------------------------------------------------------------------------
| PHASE 37-N2 - the one confirmation-rate calculator
|--------------------------------------------------------------------------
|
| The card, each team row and the total all call compute(), so the gross and
| net bases and the store's one-shot exclusions can only ever be read one way.
| The figure is confirmed over the basis denominator, and an excluded status
| key is dropped from every term.
|
*/

it('maps the date basis to the rate kind, defaulting to gross', function () {
    expect(ConfirmationRate::kindForBasis(DashboardFilter::DATE_BASIS_LAST_STATUS))->toBe(ConfirmationRate::NET)
        ->and(ConfirmationRate::kindForBasis(DashboardFilter::DATE_BASIS_CREATED))->toBe(ConfirmationRate::GROSS)
        ->and(ConfirmationRate::kindForBasis('anything-else'))->toBe(ConfirmationRate::GROSS);
});

it('reads the gross basis as confirmed over every counted order', function () {
    $result = ConfirmationRate::compute(ConfirmationRate::GROSS, [
        'confirmed' => 4,
        'preparing' => 2,
        'canceled' => 2,
        'no_answer_1' => 1,
        'pending' => 1,
    ], []);

    // 6 confirmed of every 10 lines on the desk = 60%.
    expect($rate = $result)->not->toBeNull()
        ->and($result['numerator'])->toBe(6)
        ->and($result['denominator'])->toBe(10)
        ->and($result['rate'])->toBe(60)
        ->and($result['kind'])->toBe(ConfirmationRate::GROSS)
        ->and($result['excluded'])->toBe(0);
});

it('maps the delivered and returned outcomes into the confirmed numerator', function () {
    $counts = ['delivered' => 2, 'shipped' => 1, 'returned' => 1, 'canceled' => 1];

    $gross = ConfirmationRate::compute(ConfirmationRate::GROSS, $counts, []);

    // Shipping never moves the line: 4 of 5 = 80%.
    expect($gross['numerator'])->toBe(4)
        ->and($gross['denominator'])->toBe(5)
        ->and($gross['rate'])->toBe(80);
});

it('measures the net basis against the orders the desk settled', function () {
    $counts = ['confirmed' => 6, 'canceled' => 2, 'no_answer_1' => 4, 'pending' => 8, 'postponed' => 3];

    $net = ConfirmationRate::compute(ConfirmationRate::NET, $counts, []);

    // Only confirmed + canceled count: 6 of 8 = 75%.
    expect($net['kind'])->toBe(ConfirmationRate::NET)
        ->and($net['denominator'])->toBe(8)
        ->and($net['rate'])->toBe(75);
});

it('drops an excluded status key from every term it appears in', function () {
    $counts = ['confirmed' => 2, 'canceled' => 1, 'no_answer_1' => 7, 'pending' => 5];

    $plain = ConfirmationRate::compute(ConfirmationRate::GROSS, $counts, []);
    $excluded = ConfirmationRate::compute(ConfirmationRate::GROSS, $counts, ['no_answer_1']);

    expect($plain['denominator'])->toBe(15)
        ->and($plain['rate'])->toBe(13)
        ->and($excluded['denominator'])->toBe(8)
        ->and($excluded['rate'])->toBe(25)
        ->and($excluded['excluded'])->toBe(7);
});

it('never excludes the confirmed keys, so the numerator cannot be erased', function () {
    $counts = ['confirmed' => 3, 'canceled' => 1];

    // 'confirmed' is not excludable, but even if a key mapping there were
    // handed over the numerator reads it first.
    $rate = ConfirmationRate::compute(ConfirmationRate::GROSS, $counts, ['preparing']);

    expect($rate['numerator'])->toBe(3)
        ->and($rate['denominator'])->toBe(4);
});

it('flags a group outside the rate when it is not part of the basis or is fully excluded', function () {
    // Gross, no exclusions: nothing is outside.
    expect(ConfirmationRate::groupOutside(ConfirmationRate::GROSS, ConfirmationStatsGroups::FAILED, []))
        ->toBeFalse();

    // Every failed key excluded: the whole bucket sits outside.
    $failed = ConfirmationStatsGroups::keys(ConfirmationStatsGroups::FAILED);
    expect(ConfirmationRate::groupOutside(ConfirmationRate::GROSS, ConfirmationStatsGroups::FAILED, $failed))
        ->toBeTrue()
        ->and(ConfirmationRate::groupOutside(ConfirmationRate::GROSS, ConfirmationStatsGroups::CANCELED, $failed))
        ->toBeFalse();

    // Net leaves everything but confirmed/canceled outside.
    expect(ConfirmationRate::groupOutside(ConfirmationRate::NET, ConfirmationStatsGroups::PENDING, []))
        ->toBeTrue()
        ->and(ConfirmationRate::groupOutside(ConfirmationRate::NET, ConfirmationStatsGroups::CONFIRMED, []))
        ->toBeFalse()
        ->and(ConfirmationRate::groupOutside(ConfirmationRate::NET, ConfirmationStatsGroups::CANCELED, []))
        ->toBeFalse();
});

it('clamps the percentage and guards a zero denominator', function () {
    expect(ConfirmationRate::percent(0, 0))->toBe(0)
        ->and(ConfirmationRate::percent(3, 0))->toBe(0)
        ->and(ConfirmationRate::percent(5, 4))->toBe(100)
        ->and(ConfirmationRate::percent(1, 3))->toBe(33);
});
