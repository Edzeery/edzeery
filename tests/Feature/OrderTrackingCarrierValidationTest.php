<?php

use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function carrierValidationStore()
{
    return \App\Models\Stores\Store::create([
        'user_id' => \App\Models\User::factory()->create()->id,
        'name' => 'Tracking Store',
        'slug' => 'tracking-'.uniqid(),
        'status' => 'active',
    ]);
}

it('persists carrier dispatch-validation columns and casts carrier_validated_at', function () {
    $store = carrierValidationStore();
    $order = Order::create([
        'store_id' => $store->id,
        'number' => 'TRK-'.substr(uniqid(), -6),
        'total_amount' => 100,
    ]);

    $at = now();
    $tracking = OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'carrier_status' => 'validated',
        'carrier_validated_at' => $at,
        'carrier_validation_error' => null,
    ]);

    expect($tracking->carrier_validated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($tracking->carrier_validated_at->toDateTimeString())->toBe($at->toDateTimeString())
        ->and($tracking->carrier_status)->toBe('validated')
        ->and($tracking->isCarrierValidated())->toBeTrue();
});

it('sets carrier_validation_error on a rejected validation and leaves validated_at null', function () {
    $store = carrierValidationStore();
    $order = Order::create([
        'store_id' => $store->id,
        'number' => 'TRK-'.substr(uniqid(), -6),
        'total_amount' => 100,
    ]);

    $tracking = OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'carrier_status' => 'created',
        'carrier_validation_error' => 'Stock insuffisant',
    ]);

    expect($tracking->carrier_validated_at)->toBeNull()
        ->and($tracking->isCarrierValidated())->toBeFalse()
        ->and($tracking->carrier_validation_error)->toBe('Stock insuffisant');
});

it('exposes a validatedBy relation to the acting store membership', function () {
    $store = carrierValidationStore();
    $membership = StoreMembership::create([
        'id' => (string) \Illuminate\Support\Str::ulid(),
        'store_id' => $store->id,
        'user_id' => \App\Models\User::factory()->create()->id,
        'role' => 'owner',
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'number' => 'TRK-'.substr(uniqid(), -6),
        'total_amount' => 100,
    ]);

    $tracking = OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'carrier_status' => 'validated',
        'carrier_validated_at' => now(),
        'carrier_validated_by_membership_id' => $membership->id,
    ]);

    expect($tracking->validatedBy)->not->toBeNull()
        ->and($tracking->validatedBy->id)->toBe($membership->id);
});