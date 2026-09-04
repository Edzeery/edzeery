<?php

use App\Domains\Shipping\Models\DeliveryRider;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
});

function createRiderStore(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Rider Store',
        'slug' => 'rider-store-'.uniqid(),
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

// ————— Delivery Riders page (merchant.delivery.riders) —————

test('owner can open the delivery riders page', function () {
    [$user, $store] = createRiderStore('owner');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.delivery.riders')
        ->assertOk()
        ->assertSee(__('merchant_panel.tab_riders'))
        ->assertSee(__('merchant_panel.tab_riders_desc'))
        ->assertSee(__('merchant_panel.no_riders_yet'));
});

test('staff without delivery permission is blocked from the riders page', function () {
    [$user, $store] = createRiderStore('staff');

    $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.delivery.riders', $store->slug))
        ->assertForbidden();
});

test('manager holds the fine-grained rider permissions and can create a rider', function () {
    [$user, $store] = createRiderStore('manager');

    expect($user->hasPermissionTo(\App\Enums\Store\StorePermissionEnum::DELIVERY_RIDERS_VIEW->value, 'merchant'))
        ->toBeTrue()
        ->and($user->hasPermissionTo(\App\Enums\Store\StorePermissionEnum::DELIVERY_RIDERS_CREATE->value, 'merchant'))
        ->toBeTrue()
        ->and($user->hasPermissionTo(\App\Enums\Store\StorePermissionEnum::DELIVERY_RIDERS_UPDATE->value, 'merchant'))
        ->toBeTrue()
        ->and($user->hasPermissionTo(\App\Enums\Store\StorePermissionEnum::DELIVERY_RIDERS_DELETE->value, 'merchant'))
        ->toBeFalse();

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.delivery.riders')
        ->assertOk()
        ->call('openRiderModal')
        ->set('riderForm.name', 'Manager Rider')
        ->set('riderForm.phone', '0550888999')
        ->set('riderForm.vehicle_type', DeliveryRider::VEHICLE_CAR)
        ->call('saveRider')
        ->assertDispatched('swal', type: 'success');

    expect(DeliveryRider::where('store_id', $store->id)->count())->toBe(1);
});

test('manager without delete permission cannot delete a rider', function () {
    [$user, $store] = createRiderStore('manager');

    $rider = DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Protected',
        'phone' => '0550777888',
        'vehicle_type' => DeliveryRider::VEHICLE_CAR,
        'is_active' => true,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.delivery.riders')
        ->call('deleteRider', $rider->id);

    expect(DeliveryRider::where('store_id', $store->id)->count())->toBe(1);
});

test('owner creates a rider', function () {
    [$user, $store] = createRiderStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.delivery.riders')
        ->call('openRiderModal')
        ->set('riderForm.name', 'Amina')
        ->set('riderForm.phone', '0550123456')
        ->set('riderForm.email', 'amina@example.com')
        ->set('riderForm.vehicle_type', DeliveryRider::VEHICLE_MOTORCYCLE)
        ->set('riderForm.notes', 'Prefers city centre')
        ->set('riderForm.is_active', true)
        ->call('saveRider')
        ->assertDispatched('swal', type: 'success');

    $rider = DeliveryRider::where('store_id', $store->id)->first();

    expect($rider)->not->toBeNull()
        ->and($rider->name)->toBe('Amina')
        ->and($rider->phone)->toBe('0550123456')
        ->and($rider->email)->toBe('amina@example.com')
        ->and($rider->vehicle_type)->toBe(DeliveryRider::VEHICLE_MOTORCYCLE)
        ->and($rider->notes)->toBe('Prefers city centre')
        ->and($rider->is_active)->toBeTrue();
});

test('rider name and phone are required', function () {
    [$user, $store] = createRiderStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.delivery.riders')
        ->call('openRiderModal')
        ->call('saveRider')
        ->assertHasErrors('riderForm.name')
        ->assertHasErrors('riderForm.phone');

    expect(DeliveryRider::where('store_id', $store->id)->count())->toBe(0);
});

test('owner edits a rider', function () {
    [$user, $store] = createRiderStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $rider = DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Karim',
        'phone' => '0770123456',
        'vehicle_type' => DeliveryRider::VEHICLE_CAR,
        'is_active' => true,
    ]);

    Volt::test('merchant.delivery.riders')
        ->call('openRiderModal', $rider->id)
        ->assertSet('riderForm.name', 'Karim')
        ->assertSet('riderForm.vehicle_type', DeliveryRider::VEHICLE_CAR)
        ->set('riderForm.name', 'Karim B.')
        ->set('riderForm.vehicle_type', DeliveryRider::VEHICLE_VAN)
        ->set('riderForm.is_active', false)
        ->call('saveRider')
        ->assertDispatched('swal', type: 'success');

    $rider->refresh();

    expect($rider->name)->toBe('Karim B.')
        ->and($rider->vehicle_type)->toBe(DeliveryRider::VEHICLE_VAN)
        ->and($rider->is_active)->toBeFalse();
});

test('owner deletes a rider', function () {
    [$user, $store] = createRiderStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $rider = DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Sofiane',
        'phone' => '0660123456',
        'vehicle_type' => DeliveryRider::VEHICLE_BICYCLE,
        'is_active' => true,
    ]);

    Volt::test('merchant.delivery.riders')
        ->call('deleteRider', $rider->id)
        ->assertDispatched('swal', type: 'success');

    expect(DeliveryRider::where('store_id', $store->id)->count())->toBe(0);
});

test('riders are isolated per store', function () {
    [$user, $store] = createRiderStore('owner');
    [$otherUser, $otherStore] = createRiderStore('owner');
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    DeliveryRider::create([
        'store_id' => $store->id,
        'name' => 'Mine',
        'phone' => '0550000001',
        'vehicle_type' => DeliveryRider::VEHICLE_CAR,
        'is_active' => true,
    ]);
    DeliveryRider::create([
        'store_id' => $otherStore->id,
        'name' => 'Theirs',
        'phone' => '0550000002',
        'vehicle_type' => DeliveryRider::VEHICLE_CAR,
        'is_active' => true,
    ]);

    Volt::test('merchant.delivery.riders')
        ->assertCount('riders', 1)
        ->assertSee('Mine')
        ->assertDontSee('Theirs');
});

test('demo store seeder seeds sample riders for the demo store', function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\DemoStoreSeeder::class);

    $store = Store::where('slug', 'demo')->first();

    expect($store)->not->toBeNull();

    $riders = DeliveryRider::where('store_id', $store->id)->get();

    expect($riders)->not->toBeEmpty()
        ->and($riders->count())->toBeGreaterThanOrEqual(4)
        ->and($riders->pluck('phone'))->toContain('0550100011', '0660200022')
        ->and($riders->where('is_active', true)->count())->toBeGreaterThanOrEqual(1)
        ->and($riders->where('is_active', false)->count())->toBeGreaterThanOrEqual(1);
});
