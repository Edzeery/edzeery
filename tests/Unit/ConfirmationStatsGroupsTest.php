<?php

use App\Domains\Analytics\Support\ConfirmationStatsGroups;

it('counts the desk cleared states and everything past the desk as confirmed', function () {
    expect(ConfirmationStatsGroups::in('confirmed', ConfirmationStatsGroups::CONFIRMED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('preparing', ConfirmationStatsGroups::CONFIRMED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('processing', ConfirmationStatsGroups::CONFIRMED))->toBeTrue()
        // D2: an order that moved past the desk stays confirmed on this tab.
        ->and(ConfirmationStatsGroups::in('delivered', ConfirmationStatsGroups::CONFIRMED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('returned', ConfirmationStatsGroups::CONFIRMED))->toBeTrue()
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

it('keeps deferred and duplicate in their own buckets and folds the moved-on orders into confirmed', function () {
    expect(ConfirmationStatsGroups::in('postponed', ConfirmationStatsGroups::DEFERRED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('out_of_stock', ConfirmationStatsGroups::DEFERRED))->toBeTrue()
        ->and(ConfirmationStatsGroups::in('duplicate', ConfirmationStatsGroups::DUPLICATE))->toBeTrue();

    // D2: everything that has left the desk stays inside "confirmed", so no
    // delivery bucket exists on the confirmation tab.
    foreach (['shipped', 'in_transit', 'out_for_delivery', 'completed', 'delivered', 'returned', 'refunded', 'undeliverable', 'unclaimed'] as $key) {
        expect(ConfirmationStatsGroups::group($key))->toBe(ConfirmationStatsGroups::CONFIRMED);
    }

    // on_hold/paid/draft are neither confirmed nor written off: the muted extra.
    foreach (['on_hold', 'paid', 'draft'] as $key) {
        expect(ConfirmationStatsGroups::group($key))->toBe(ConfirmationStatsGroups::OTHER);
    }
});

it('folds a store custom state into the muted other bucket rather than dropping it', function () {
    expect(ConfirmationStatsGroups::group('custom_store_state'))->toBe(ConfirmationStatsGroups::OTHER)
        ->and(ConfirmationStatsGroups::group(''))->toBe(ConfirmationStatsGroups::OTHER);
});

it('lists the keys of a group straight from the same map', function () {
    expect(ConfirmationStatsGroups::keys(ConfirmationStatsGroups::CONFIRMED))
        ->toBe(['confirmed', 'preparing', 'processing', 'shipped', 'in_transit', 'out_for_delivery', 'completed', 'delivered', 'returned', 'refunded', 'undeliverable', 'unclaimed'])
        ->and(ConfirmationStatsGroups::keys(ConfirmationStatsGroups::PENDING))
        ->toBe(['pending'])
        ->and(ConfirmationStatsGroups::all())
        ->toBe(['confirmed', 'pending', 'failed', 'canceled', 'deferred', 'duplicate', 'other']);
});
