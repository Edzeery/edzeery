<?php

use App\Domains\Order\Models\UserColumnPreference;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
});

function createOrdersStore(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Orders Store',
        'slug' => 'orders-store-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
    ]);

    return [$user, $store];
}

function ordersMembership(Store $store, \App\Models\User $user): StoreMembership
{
    return StoreMembership::where('store_id', $store->id)->where('user_id', $user->id)->firstOrFail();
}

// ————— Orders index column/table preferences (merchant.orders.index) —————

test('orders page renders with default table settings when no preference row exists', function () {
    [$user, $store] = createOrdersStore('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->assertOk()
        ->assertSet('tableStyle', 'default')
        ->assertSet('showTrash', false);

    expect(
        UserColumnPreference::where('membership_id', ordersMembership($store, $user)->id)
            ->where('view_key', 'orders_index')
            ->exists(),
    )->toBeFalse();
});

test('saving status table style persists and is re-applied on render', function () {
    [$user, $store] = createOrdersStore('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('openTableSettings')
        ->set('draftStyle', 'status')
        ->call('saveTableSettings')
        ->assertSet('tableStyle', 'status')
        ->assertDispatched('swal:toast');

    expect(
        UserColumnPreference::where('membership_id', ordersMembership($store, $user)->id)
            ->where('view_key', 'orders_index')
            ->value('table_style'),
    )->toBe('status');
});

test('a stored preference row with status style is honoured on a fresh render', function () {
    [$user, $store] = createOrdersStore('owner');

    UserColumnPreference::create([
        'membership_id' => ordersMembership($store, $user)->id,
        'view_key' => 'orders_index',
        'visible_columns' => ['notes'],
        'table_style' => 'status',
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->assertOk()
        ->assertSet('tableStyle', 'status');
});

test('orders page stays on the default style when a stored row has a malformed table_style', function () {
    [$user, $store] = createOrdersStore('owner');

    UserColumnPreference::create([
        'membership_id' => ordersMembership($store, $user)->id,
        'view_key' => 'orders_index',
        'visible_columns' => [],
        'table_style' => 'neon',
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->assertOk()
        ->assertSet('tableStyle', 'default');
});