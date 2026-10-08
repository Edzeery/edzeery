<?php

use App\Domains\Order\Models\ConfirmationShift;
use App\Domains\Order\Support\ShiftAvailabilityResolver;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function availStore(): Store
{
    $owner = User::factory()->create();

    return Store::create([
        'user_id' => $owner->id,
        'name' => 'Availability Store',
        'slug' => 'avail-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);
}

function availMember(Store $store, array $permissions = [StorePermissionEnum::ORDER_CONFIRM->value]): StoreMembership
{
    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => User::factory()->create()->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => 'staff',
    ]);

    $membership->syncPermissions($permissions);

    return $membership;
}

function availShift(Store $store, StoreMembership $membership, array $overrides = []): ConfirmationShift
{
    return ConfirmationShift::create(array_merge([
        'store_id' => $store->id,
        'membership_id' => $membership->id,
        'shift_type' => 'custom',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'days_of_week' => [1, 2, 3, 4, 5],
        'is_active' => true,
        'role_scope' => 'confirm',
        'max_concurrent_orders' => null,
    ], $overrides));
}

function availCandidates(Store $store): Illuminate\Support\Collection
{
    return StoreMembership::where('store_id', $store->id)->get();
}

test('the cap at an instant is the highest cap among the shifts covering it', function () {
    $store = availStore();
    $member = availMember($store);
    availShift($store, $member, ['start_time' => '08:00', 'end_time' => '12:00', 'max_concurrent_orders' => 30]);
    availShift($store, $member, ['start_time' => '22:00', 'end_time' => '06:00', 'max_concurrent_orders' => 60]);

    $candidates = availCandidates($store);
    $resolver = app(ShiftAvailabilityResolver::class);

    $mondayMorning = $resolver->resolve($store->id, 'confirm', $candidates, Carbon::create(2026, 5, 4, 10, 0, 0));
    $mondayNight = $resolver->resolve($store->id, 'confirm', $candidates, Carbon::create(2026, 5, 4, 23, 0, 0));
    $tuesdayEarly = $resolver->resolve($store->id, 'confirm', $candidates, Carbon::create(2026, 5, 5, 3, 0, 0));
    $tuesdayAfternoon = $resolver->resolve($store->id, 'confirm', $candidates, Carbon::create(2026, 5, 5, 14, 0, 0));

    expect($mondayMorning[$member->id])->toBe(['on_shift' => true, 'cap' => 30])
        ->and($mondayNight[$member->id])->toBe(['on_shift' => true, 'cap' => 60])
        ->and($tuesdayEarly[$member->id])->toBe(['on_shift' => true, 'cap' => 60])
        ->and($tuesdayAfternoon)->not->toHaveKey($member->id);
});

test('a covering uncapped shift wins over a covering capped shift of the same member', function () {
    $store = availStore();
    $member = availMember($store);
    // Uncapped created first so the capped shift is processed after it — the
    // result must still be uncapped regardless of processing order.
    availShift($store, $member, ['start_time' => '08:00', 'end_time' => '17:00', 'max_concurrent_orders' => null]);
    availShift($store, $member, ['start_time' => '08:00', 'end_time' => '12:00', 'max_concurrent_orders' => 5]);

    $availability = app(ShiftAvailabilityResolver::class)
        ->resolve($store->id, 'confirm', availCandidates($store), Carbon::create(2026, 5, 4, 10, 0, 0));

    expect($availability[$member->id])->toBe(['on_shift' => true, 'cap' => null]);
});

test('a member with no covering shift carries no entry at all', function () {
    $store = availStore();
    $member = availMember($store);
    availShift($store, $member, ['start_time' => '22:00', 'end_time' => '06:00', 'max_concurrent_orders' => 4]);

    $resolver = app(ShiftAvailabilityResolver::class);

    $offShift = $resolver->resolve($store->id, 'confirm', availCandidates($store), Carbon::create(2026, 5, 4, 10, 0, 0));
    $onShift = $resolver->resolve($store->id, 'confirm', availCandidates($store), Carbon::create(2026, 5, 4, 23, 0, 0));

    expect($offShift)->not->toHaveKey($member->id)
        ->and($onShift[$member->id])->toBe(['on_shift' => true, 'cap' => 4]);
});

test('only active shifts of the requested role scope are resolved', function () {
    $store = availStore();
    $dual = availMember($store);
    availShift($store, $dual, ['role_scope' => 'confirm', 'max_concurrent_orders' => 9]);
    availShift($store, $dual, ['role_scope' => 'track', 'max_concurrent_orders' => 5]);
    $inactive = availMember($store);
    availShift($store, $inactive, ['is_active' => false]);

    $candidates = availCandidates($store);
    $resolver = app(ShiftAvailabilityResolver::class);
    $at = Carbon::create(2026, 5, 4, 10, 0, 0);

    $confirm = $resolver->resolve($store->id, 'confirm', $candidates, $at);
    $track = $resolver->resolve($store->id, 'track', $candidates, $at);

    expect($confirm[$dual->id])->toBe(['on_shift' => true, 'cap' => 9])
        ->and($track[$dual->id])->toBe(['on_shift' => true, 'cap' => 5])
        ->and($confirm)->not->toHaveKey($inactive->id)
        ->and($track)->not->toHaveKey($inactive->id);
});

test('the snapshot issues the same number of queries for one or many candidates', function () {
    $smallStore = availStore();
    $one = availMember($smallStore);
    availShift($smallStore, $one);
    $oneCandidates = availCandidates($smallStore);

    $bigStore = availStore();
    foreach (range(1, 4) as $cap) {
        $member = availMember($bigStore);
        availShift($bigStore, $member, ['max_concurrent_orders' => $cap]);
    }
    $manyCandidates = availCandidates($bigStore);

    $resolver = app(ShiftAvailabilityResolver::class);
    $at = Carbon::create(2026, 5, 4, 10, 0, 0);

    DB::connection()->flushQueryLog();
    DB::connection()->enableQueryLog();
    $resolver->resolve($smallStore->id, 'confirm', $oneCandidates, $at);
    $queriesForOne = count(DB::getQueryLog());

    DB::connection()->flushQueryLog();
    $resolver->resolve($bigStore->id, 'confirm', $manyCandidates, $at);
    $queriesForMany = count(DB::getQueryLog());
    DB::connection()->disableQueryLog();

    expect($queriesForOne)->toBe($queriesForMany);
});
