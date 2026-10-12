<?php

use App\Domains\Analytics\Support\ConfirmationRateDenominator;
use App\Domains\Analytics\Support\ConfirmationStatsGroups;

it('falls back to the settled orders when nothing is configured', function () {
    expect(ConfirmationRateDenominator::resolve(null))
        ->toBe([
            ConfirmationStatsGroups::CONFIRMED,
            ConfirmationStatsGroups::CANCELED,
            ConfirmationStatsGroups::FAILED,
        ])
        ->and(ConfirmationRateDenominator::resolve([]))
        ->toBe(ConfirmationRateDenominator::DEFAULT_GROUPS);
});

it('drops unknown buckets a stale setting might name', function () {
    expect(ConfirmationRateDenominator::resolve(['confirmed', 'bogus', 'pending']))
        ->toBe([ConfirmationStatsGroups::CONFIRMED, ConfirmationStatsGroups::PENDING])
        // A list of only unknown buckets is as good as none.
        ->and(ConfirmationRateDenominator::resolve(['bogus']))
        ->toBe(ConfirmationRateDenominator::DEFAULT_GROUPS);
});

it('keeps the ready made presets inside the buckets the tab knows', function () {
    $known = ConfirmationStatsGroups::all();

    expect(array_diff(ConfirmationRateDenominator::DEFAULT_GROUPS, $known))->toBe([])
        ->and(array_diff(ConfirmationRateDenominator::PRESET_EXCLUDE_DUPLICATE_AND_DEFERRED, $known))->toBe([])
        // The preset literally drops the duplicate and the deferred buckets.
        ->and(ConfirmationRateDenominator::PRESET_EXCLUDE_DUPLICATE_AND_DEFERRED)
        ->not->toContain(ConfirmationStatsGroups::DUPLICATE, ConfirmationStatsGroups::DEFERRED);
});
