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

test('stale prefs_version row is reset once to the v2 default layout', function () {
    [$user, $store] = createOrdersStore('owner');

    UserColumnPreference::create([
        'membership_id' => ordersMembership($store, $user)->id,
        'view_key' => 'orders_index',
        'visible_columns' => ['number', 'amount', 'source'],
        'table_style' => 'default',
        'prefs_version' => 1,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $v2Defaults = ['customer', 'phone', 'verification', 'status', 'shipping_provider', 'delivery_type', 'wilaya', 'city', 'stopdesk_point', 'address', 'products', 'quantity', 'price', 'total', 'confirmation_attempts', 'last_contact'];

    Volt::test('merchant.orders.index')
        ->assertSet('visibleColumns', $v2Defaults);

    expect(
        UserColumnPreference::where('membership_id', ordersMembership($store, $user)->id)
            ->where('view_key', 'orders_index')
            ->value('prefs_version'),
    )->toBe(2);
});

test('required columns cannot be hidden from the settings draft', function () {
    [$user, $store] = createOrdersStore('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('openTableSettings')
        ->call('toggleDraftColumn', 'phone')
        ->call('toggleDraftColumn', 'notes')
        ->assertSet('draftColumns', fn ($cols) => in_array('phone', $cols, true));
});

test('role-gated columns (attempts, last contact) are hidden for non-privileged staff', function () {
    [$user, $store] = createOrdersStore('staff');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->assertSet('visibleColumns', fn ($cols) => !in_array('confirmation_attempts', $cols, true))
        ->assertSet('visibleColumns', fn ($cols) => !in_array('last_contact', $cols, true))
        ->assertSet('visibleColumns', fn ($cols) => in_array('customer', $cols, true));
});

test('drag-and-drop reorder reorders the draft and persists the full visible list', function () {
    [$user, $store] = createOrdersStore('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $defaults = ['customer', 'phone', 'verification', 'status', 'shipping_provider', 'delivery_type', 'wilaya', 'city', 'stopdesk_point', 'address', 'products', 'quantity', 'price', 'total', 'confirmation_attempts', 'last_contact'];
    $reordered = array_merge(['total'], array_values(array_diff($defaults, ['total'])));

    Volt::test('merchant.orders.index')
        ->call('openTableSettings')
        ->assertSet('draftColumns', $defaults)
        ->call('reorderDraftColumns', $reordered)
        ->assertSet('draftColumns', $reordered)
        ->call('saveTableSettings')
        ->assertSet('visibleColumns', $reordered)
        ->assertDispatched('swal:toast');

    expect(
        UserColumnPreference::where('membership_id', ordersMembership($store, $user)->id)
            ->where('view_key', 'orders_index')
            ->value('visible_columns'),
    )->toBe($reordered);
});

test('reorderDraftColumns dedupes and drops unknown keys before saving', function () {
    [$user, $store] = createOrdersStore('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('openTableSettings')
        ->call('reorderDraftColumns', ['unknown_key', 'customer', 'customer', 'amount', 'number'])
        ->assertSet('draftColumns', ['customer', 'number'])
        ->call('saveTableSettings')
        ->assertSet('visibleColumns', ['customer', 'phone', 'number', 'status', 'shipping_provider', 'delivery_type', 'wilaya', 'city', 'stopdesk_point', 'address', 'products', 'quantity', 'price', 'total']);
});