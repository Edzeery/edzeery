<?php

use App\Domains\Order\Models\ConfirmationShift;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function membershipWithShifts(): array
{
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $store = Store::create([
        'user_id' => $owner->id,
        'name' => 'Shift Store',
        'slug' => 'shift-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $member->id,
        'invited_by' => $owner->id,
        'is_active' => true,
        'role' => 'staff',
    ]);

    return [$membership, $store];
}

test('activeBetween matches a whole end minute (off-by-one fix)', function () {
    [$membership] = membershipWithShifts();

    ConfirmationShift::create([
        'store_id' => $membership->store_id,
        'membership_id' => $membership->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ]);

    // 16:59:58 should still be inside the shift (the old code cut off at 16:59:59
    // because 'H:i' 16:59 vs 'H:i:s' end 17:00:00 => false).
    $at = \Illuminate\Support\Carbon::create(2026, 5, 4, 16, 59, 58); // Monday
    expect($membership->isOnActiveShift($at))->toBeTrue();

    // 17:00:00 exactly is outside.
    $at2 = \Illuminate\Support\Carbon::create(2026, 5, 4, 17, 0, 0);
    expect($membership->isOnActiveShift($at2))->toBeFalse();
});

test('isOnActiveShift respects days of week', function () {
    [$membership] = membershipWithShifts();

    ConfirmationShift::create([
        'store_id' => $membership->store_id,
        'membership_id' => $membership->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'days_of_week' => [1, 2, 3, 4, 5], // weekdays only
        'is_active' => true,
    ]);

    $tuesday = \Illuminate\Support\Carbon::create(2026, 5, 5, 10, 0, 0); // Tuesday
    $saturday = \Illuminate\Support\Carbon::create(2026, 5, 9, 10, 0, 0); // Saturday

    expect($membership->isOnActiveShift($tuesday))->toBeTrue();
    expect($membership->isOnActiveShift($saturday))->toBeFalse();
});

test('overnight shift covers both sides of midnight', function () {
    [$membership] = membershipWithShifts();

    ConfirmationShift::create([
        'store_id' => $membership->store_id,
        'membership_id' => $membership->id,
        'shift_type' => 'custom',
        'start_time' => '22:00',
        'end_time' => '06:00',
        'days_of_week' => [1],
        'is_active' => true,
    ]);

    // Monday 23:00
    $mondayNight = \Illuminate\Support\Carbon::create(2026, 5, 4, 23, 0, 0);
    expect($membership->isOnActiveShift($mondayNight))->toBeTrue();

    // Tuesday 03:00 (next day after the Monday shift)
    $tuesdayMorning = \Illuminate\Support\Carbon::create(2026, 5, 5, 3, 0, 0);
    expect($membership->isOnActiveShift($tuesdayMorning))->toBeTrue();

    // Tuesday 12:00 (outside)
    $tuesdayNoon = \Illuminate\Support\Carbon::create(2026, 5, 5, 12, 0, 0);
    expect($membership->isOnActiveShift($tuesdayNoon))->toBeFalse();
});

test('overlapping active shifts are rejected', function () {
    [$membership] = membershipWithShifts();

    ConfirmationShift::create([
        'store_id' => $membership->store_id,
        'membership_id' => $membership->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ]);

    $candidate = [
        'membership_id' => $membership->id,
        'start_time' => '10:00',
        'end_time' => '14:00',
        'days_of_week' => [1, 2, 3],
    ];

    expect(ConfirmationShift::overlapsActiveShift($candidate))->toBeTrue();
});

test('non-overlapping shifts are allowed', function () {
    [$membership] = membershipWithShifts();

    ConfirmationShift::create([
        'store_id' => $membership->store_id,
        'membership_id' => $membership->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ]);

    $candidate = [
        'membership_id' => $membership->id,
        'start_time' => '13:00',
        'end_time' => '17:00',
        'days_of_week' => [1, 2, 3],
    ];

    expect(ConfirmationShift::overlapsActiveShift($candidate))->toBeFalse();
});

test('editing a shift excluded itself from the overlap check', function () {
    [$membership] = membershipWithShifts();

    $shift = ConfirmationShift::create([
        'store_id' => $membership->store_id,
        'membership_id' => $membership->id,
        'shift_type' => 'morning',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
    ]);

    $candidate = [
        'membership_id' => $membership->id,
        'start_time' => '08:00',
        'end_time' => '12:00',
        'days_of_week' => [1, 2, 3, 4, 5],
    ];

    expect(ConfirmationShift::overlapsActiveShift($candidate, $shift->id))->toBeFalse();
});
