<?php

use App\Enums\Store\OrderTrackingStatus;

it('exposes the tracking domain group across all cases', function () {
    expect(OrderTrackingStatus::cases())->toHaveCount(9);

    foreach (OrderTrackingStatus::cases() as $case) {
        expect($case->statusDomain())->toBe('tracking');
    }
});

it('classifies open and terminal statuses correctly', function () {
    expect(OrderTrackingStatus::DELIVERED->isTerminal())->toBeTrue()
        ->and(OrderTrackingStatus::RETURNED->isTerminal())->toBeTrue()
        ->and(OrderTrackingStatus::LOST->isTerminal())->toBeTrue()
        ->and(OrderTrackingStatus::DAMAGED->isTerminal())->toBeTrue()
        ->and(OrderTrackingStatus::SHIPPED->isOpen())->toBeTrue()
        ->and(OrderTrackingStatus::IN_TRANSIT->isOpen())->toBeTrue()
        ->and(OrderTrackingStatus::OUT_FOR_DELIVERY->isOpen())->toBeTrue()
        ->and(OrderTrackingStatus::RETURNING->isOpen())->toBeTrue()
        ->and(OrderTrackingStatus::FAILED_ATTEMPT->isOpen())->toBeTrue()
        ->and(OrderTrackingStatus::DELIVERED->isOpen())->toBeFalse();
});

it('maps raw carrier strings into normalised statuses', function () {
    expect(OrderTrackingStatus::fromCarrier('Package delivered'))->toBe(OrderTrackingStatus::DELIVERED)
        ->and(OrderTrackingStatus::fromCarrier('out for delivery'))->toBe(OrderTrackingStatus::OUT_FOR_DELIVERY)
        ->and(OrderTrackingStatus::fromCarrier('in transit'))->toBe(OrderTrackingStatus::IN_TRANSIT)
        ->and(OrderTrackingStatus::fromCarrier('failed attempt'))->toBe(OrderTrackingStatus::FAILED_ATTEMPT)
        ->and(OrderTrackingStatus::fromCarrier('returning to sender'))->toBe(OrderTrackingStatus::RETURNING)
        ->and(OrderTrackingStatus::fromCarrier('returned to sender'))->toBe(OrderTrackingStatus::RETURNED)
        ->and(OrderTrackingStatus::fromCarrier('lost'))->toBe(OrderTrackingStatus::LOST)
        ->and(OrderTrackingStatus::fromCarrier('DAMAGE reported'))->toBe(OrderTrackingStatus::DAMAGED)
        ->and(OrderTrackingStatus::fromCarrier(''))->toBeNull()
        ->and(OrderTrackingStatus::fromCarrier(null))->toBeNull();
});

it('resolves localised kit labels for the tracking domain', function () {
    app()->setLocale('ar');

    expect(OrderTrackingStatus::SHIPPED->label())->toBe('تم الشحن')
        ->and(OrderTrackingStatus::LOST->label())->toBe('ضائع')
        ->and(OrderTrackingStatus::DAMAGED->label())->toBe('تالف');

    app()->setLocale('en');

    expect(OrderTrackingStatus::LOST->label())->toBe('Lost')
        ->and(OrderTrackingStatus::DELIVERED->label())->toBe('Delivered');
});

it('exposes colors and icons for the tracking domain', function () {
    expect(OrderTrackingStatus::DELIVERED->color())->not->toBeEmpty()
        ->and(OrderTrackingStatus::DAMAGED->color())->not->toBeEmpty()
        ->and(OrderTrackingStatus::SHIPPED->iconKey())->not->toBeEmpty();
});
