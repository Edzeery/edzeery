<?php

use App\Domains\Status\StatusResolver;
use App\Models\Status as StatusModel;
use App\Models\Stores\Store;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function srStore(): Store
{
    $user = \App\Models\User::factory()->create();

    return Store::create([
        'user_id' => $user->id,
        'name' => 'Status Store',
        'slug' => 'st-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);
}

it('resolves every domain entry from config plus system DB rows in one batch', function () {
    StatusModel::query()->create([
        'type' => 'stores',
        'key' => 'verified',
        'store_id' => null,
        'label' => 'System Verified',
        'color' => 'success',
        'is_system' => true,
    ]);

    $resolved = StatusResolver::domain('stores');
    StatusResolver::flush();

    expect($resolved)->toHaveKey('verified')
        ->and($resolved['verified']->label)->toBe('System Verified')
        ->and($resolved['verified']->source)->toBe('db')
        ->and($resolved)->toHaveKey('active');
});

it('prefers the store-scoped row over the system row', function () {
    $store = srStore();

    StatusModel::query()->create([
        'type' => 'stores', 'key' => 'verified', 'store_id' => null,
        'label' => 'System Verified', 'color' => 'success', 'is_system' => true,
    ]);
    StatusModel::query()->create([
        'type' => 'stores', 'key' => 'verified', 'store_id' => $store->id,
        'label' => 'Store Verified', 'color' => 'warning',
    ]);

    $storeResolved = StatusResolver::domain('stores', $store->id);
    StatusResolver::flush();
    $systemResolved = StatusResolver::domain('stores');
    StatusResolver::flush();

    expect($storeResolved['verified']->label)->toBe('Store Verified')
        ->and($storeResolved['verified']->variant)->toBe('warning')
        ->and($systemResolved['verified']->label)->toBe('System Verified');
});
