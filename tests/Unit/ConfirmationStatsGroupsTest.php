<?php

use App\Domains\Analytics\Support\ConfirmationStatsGroups;

it('counts only the desk cleared states as confirmed', function () {
    expect(ConfirmationStatsGroups::in('confirmed', ConfirmationStatsGroups::CONFIRMED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('preparing', ConfirmationStatsGroups::CONFIRMED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('processing', ConfirmationStatsGroups::CONFIRMED))->toBeTrue()
        // Delivery outcomes never count as confirmed on this tab.
        ->and(ConfirmationStatsGroups::in('delivered', ConfirmationStatsGroups::CONFIRMED))->toBeFalse()
        ->and(ConfirmationStatsGroups::in('returned', ConfirmationStatsGroups::CONFIRMED))->toBeFalse()
        ->and(ConfirmationStatsGroups::in('pending', ConfirmationStatsGroups::CONFIRMED))->toBeFalse();
});

it('sends the unanswered states to failed and both cancel spellings to canceled', function () {
    expect(ConfirmationStatsGroups::in('no_answer_1', ConfirmationStatsGroups::FAILED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('no_answer_2', ConfirmationStatsGroups::FAILED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('no_answer_3', ConfirmationStatsGroups::FAILED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('canceled', ConfirmationStatsGroups::CANCELED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('cancelled', ConfirmationStatsGroups::CANCELED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('wrong_number', ConfirmationStatsGroups::CANCELED))->toBeTrue();
});

it('keeps deferred, duplicate and the split delivery buckets in their own buckets', function () {
    expect(ConfirmationStatsGroups::in('postponed', ConfirmationStatsGroups::DEFERRED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('out_of_stock', ConfirmationStatsGroups::DEFERRED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('duplicate', ConfirmationStatsGroups::DUPLICATE))->toBeTrue();

    // D12: everything that has left the desk stays "confirmed" for the rate.
    foreach (['shipped', 'in_transit', 'out_for_delivery', 'completed', 'delivered', 'returned', 'refunded', 'undeliverable', 'unclaimed'] as $key) {
        expect(ConfirmationStatsGroups::group($key))->toBe(ConfirmationStatsGroups::PASSED_TO_DELIVERY);
    }

    // on_hold/paid/draft are neither confirmed nor written off: the muted extra.
    foreach (['on_hold', 'paid', 'draft'] as $key) {
        expect(ConfirmationStatsGroups::group($key))->toBe(ConfirmationStatsGroups::OTHER);
    }
});

it('surfaces a store custom state as unclassified rather than dropping it', function () {
    expect(ConfirmationStatsGroups::group('custom_store_state'))->toBe(ConfirmationStatsGroups::UNCLASSIFIED)
        ->and(ConfirmationStatsGroups::group(''))->toBe(ConfirmationStatsGroups::UNCLASSIFIED);
});

it('lists the keys of a group straight from the same map', function () {
    expect(ConfirmationStatsGroups::keys(ConfirmationStatsGroups::CONFIRMED))
        ->toBe(['confirmed', 'preparing', 'processing'])
        ->and(ConfirmationStatsGroups::keys(ConfirmationStatsGroups::PENDING))
        ->toBe(['pending'])
        ->and(ConfirmationStatsGroups::all())
        ->toContain('confirmed', 'pending', 'failed', 'canceled', 'deferred', 'duplicate', 'passed_to_delivery', 'other', 'unclassified');
});
