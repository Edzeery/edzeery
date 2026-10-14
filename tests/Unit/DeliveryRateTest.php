<?php

use App\Domains\Analytics\DTOs\DashboardFilter;
use App\Domains\Analytics\Support\DeliveryRate;
use App\Domains\Analytics\Support\DeliveryStatsGroups;

uses(Tests\TestCase::class);

/*
|--------------------------------------------------------------------------
| PHASE 37-N7 (D3) - the delivery rate, computed in one place
|--------------------------------------------------------------------------
|
| Gross divides the delivered (and returned) orders by every order in the
| window; net divides them by the settled outcomes only. The store may keep
| status keys out of every term, and both the rate cards and the team footer
| go through compute(), so they can never disagree.
|
*/

it('picks the kind from the date basis', function () {
    expect(DeliveryRate::kindForBasis(DashboardFilter::DATE_BASIS_CREATED))->toBe(DeliveryRate::GROSS)
        ->and(DeliveryRate::kindForBasis(DashboardFilter::DATE_BASIS_LAST_STATUS))->toBe(DeliveryRate::NET);
});

it('divides gross deliveries by every order in the window', function () {
    $report = DeliveryRate::compute(DeliveryRate::GROSS, [
        'delivered' => 2,
        'returned' => 1,
        'canceled' => 1,
    ], []);

    expect($report['kind'])->toBe(DeliveryRate::GROSS)
        ->and($report['delivered'])->toBe(2)
        ->and($report['returned'])->toBe(1)
        ->and($report['denominator'])->toBe(4)
        ->and($report['delivery_rate'])->toBe(50)
        ->and($report['return_rate'])->toBe(25)
        ->and($report['excluded'])->toBe(0);
});

it('divides net deliveries by the settled outcomes only', function () {
    $report = DeliveryRate::compute(DeliveryRate::NET, [
        'delivered' => 2,
        'returned' => 1,
        'canceled' => 5,
    ], []);

    expect($report['denominator'])->toBe(3)
        ->and($report['delivery_rate'])->toBe(67)
        ->and($report['return_rate'])->toBe(33);
});

it('drops the excluded status keys from every term and reports their count', function () {
    $report = DeliveryRate::compute(DeliveryRate::GROSS, [
        'delivered' => 2,
        'returned' => 1,
        'undeliverable' => 1,
        'canceled' => 4,
    ], ['undeliverable', 'canceled']);

    expect($report['delivered'])->toBe(2)
        ->and($report['denominator'])->toBe(3)
        ->and($report['delivery_rate'])->toBe(67)
        ->and($report['excluded'])->toBe(5);
});

it('never divides by zero and stays inside 0..100', function () {
    $empty = DeliveryRate::compute(DeliveryRate::GROSS, [], []);

    expect($empty['denominator'])->toBe(0)
        ->and($empty['delivery_rate'])->toBe(0)
        ->and($empty['return_rate'])->toBe(0)
        ->and(DeliveryRate::percent(0, 0))->toBe(0)
        ->and(DeliveryRate::percent(5, 0))->toBe(0);
});

it('maps every status key into one of the six breakdown buckets', function () {
    expect(DeliveryStatsGroups::all())->toBe([
        DeliveryStatsGroups::DELIVERED,
        DeliveryStatsGroups::RETURNED,
        DeliveryStatsGroups::IN_PROGRESS,
        DeliveryStatsGroups::PREPARING,
        DeliveryStatsGroups::CANCELED,
        DeliveryStatsGroups::OTHER,
    ])
        ->and(DeliveryStatsGroups::group('delivered'))->toBe(DeliveryStatsGroups::DELIVERED)
        ->and(DeliveryStatsGroups::group('returned'))->toBe(DeliveryStatsGroups::RETURNED)
        ->and(DeliveryStatsGroups::group('shipped'))->toBe(DeliveryStatsGroups::IN_PROGRESS)
        ->and(DeliveryStatsGroups::group('out_for_delivery'))->toBe(DeliveryStatsGroups::IN_PROGRESS)
        ->and(DeliveryStatsGroups::group('confirmed'))->toBe(DeliveryStatsGroups::PREPARING)
        ->and(DeliveryStatsGroups::group('undeliverable'))->toBe(DeliveryStatsGroups::CANCELED)
        // A store's own status key is never lost: it reads as OTHER.
        ->and(DeliveryStatsGroups::group('custom_status'))->toBe(DeliveryStatsGroups::OTHER)
        // Only the two measured buckets hold a single key; OTHER maps to none.
        ->and(DeliveryStatsGroups::keys(DeliveryStatsGroups::DELIVERED))->toBe(['delivered'])
        ->and(DeliveryStatsGroups::keys(DeliveryStatsGroups::OTHER))->toBe([]);
});

it('marks a bucket outside the rate per basis, mirroring the confirmation rule', function () {
    // Net: only the two measured buckets stay inside.
    expect(DeliveryRate::groupOutside(DeliveryRate::NET, DeliveryStatsGroups::DELIVERED, []))->toBeFalse()
        ->and(DeliveryRate::groupOutside(DeliveryRate::NET, DeliveryStatsGroups::RETURNED, []))->toBeFalse()
        ->and(DeliveryRate::groupOutside(DeliveryRate::NET, DeliveryStatsGroups::CANCELED, []))->toBeTrue()
        ->and(DeliveryRate::groupOutside(DeliveryRate::NET, DeliveryStatsGroups::OTHER, []))->toBeTrue();

    // Gross: a bucket leaves only once every one of its keys is excluded.
    expect(DeliveryRate::groupOutside(DeliveryRate::GROSS, DeliveryStatsGroups::CANCELED, ['undeliverable']))->toBeFalse()
        ->and(DeliveryRate::groupOutside(DeliveryRate::GROSS, DeliveryStatsGroups::CANCELED, [
            'canceled', 'cancelled', 'wrong_number', 'undeliverable', 'unclaimed',
        ]))->toBeTrue()
        // OTHER has no mapped keys, so it can never be marked outside.
        ->and(DeliveryRate::groupOutside(DeliveryRate::GROSS, DeliveryStatsGroups::OTHER, ['anything']))->toBeFalse();
});
