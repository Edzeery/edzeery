<?php

use App\Domains\Analytics\Support\DashboardStatusGroups;
use App\Enums\Store\OrderStatus;

it('lists the confirmed group the KPI, the table and the doughnut all read', function () {
    $confirmed = DashboardStatusGroups::confirmedKeys();

    expect($confirmed)->toContain(
        'confirmed', 'preparing', 'processing', 'shipped', 'in_transit',
        'out_for_delivery', 'delivered', 'completed', 'returned',
        'undeliverable', 'unclaimed', 'refunded',
    )
        // States the confirmation desk never cleared stay out of the group.
        ->not->toContain('pending', 'on_hold', 'postponed', 'cancelled', 'draft', 'paid');
});

it('keeps every group as key strings so a GROUP BY can read them directly', function () {
    expect(DashboardStatusGroups::keys(DashboardStatusGroups::PENDING))->toBe(['pending'])
        ->and(DashboardStatusGroups::keys(DashboardStatusGroups::CANCELED))->toBe(['canceled', 'cancelled'])
        ->and(DashboardStatusGroups::keys(DashboardStatusGroups::DELIVERED))->toBe(['delivered'])
        ->and(DashboardStatusGroups::keys(DashboardStatusGroups::RETURNED))->toBe(['returned']);
});

it('treats both spellings of cancelled as the same dashboard state', function () {
    expect(DashboardStatusGroups::keys(DashboardStatusGroups::CANCELED))
        ->toContain(OrderStatus::CANCELED->value, OrderStatus::CANCELLED->value);
});

it('scopes the delivery flow to statuses that actually reached a courier', function () {
    $flow = DashboardStatusGroups::keys(DashboardStatusGroups::DELIVERY_FLOW);

    expect($flow)->toContain('shipped', 'in_transit', 'out_for_delivery', 'delivered', 'completed')
        ->not->toContain('pending', 'confirmed', 'on_hold');
});

it('answers membership of the confirmed group without spelling the list again', function () {
    expect(DashboardStatusGroups::inConfirmed('delivered'))->toBeTrue()
        ->and(DashboardStatusGroups::inConfirmed('returned'))->toBeTrue()
        ->and(DashboardStatusGroups::inConfirmed('pending'))->toBeFalse()
        ->and(DashboardStatusGroups::inConfirmed('cancelled'))->toBeFalse()
        ->and(DashboardStatusGroups::inConfirmed('custom_store_state'))->toBeFalse();
});
