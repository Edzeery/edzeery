<?php

use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderTracking;
use App\Models\Products\Product;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Services\Stores\StoreProductScopeService;
use App\Support\StoreRoles;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
});

// ————— Fixtures —————

function vsUser(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name'    => 'Visibility Store',
        'slug'    => 'visibility-'.uniqid(),
        'status'  => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $user->id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::OWNER->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store, $membership];
}

function vsMembership(Store $store, StoreRoleEnum $role, ?StoreMembership $supervisor = null): StoreMembership
{
    $user = User::factory()->create();

    $membership = StoreMembership::create([
        'store_id'                => $store->id,
        'user_id'                 => $user->id,
        'invited_by'              => $store->user_id,
        'is_active'               => true,
        'role'                    => $role->value,
        'supervisor_membership_id' => $supervisor?->id,
    ]);

    $membership->syncPermissions(StoreRoles::permissions($role));

    return $membership;
}

function vsActAs(User $user, Store $store): void
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);
}

function vsStatus(string $key): Status
{
    return Status::system()->forType('order')->where('key', $key)->firstOrFail();
}

function vsProduct(Store $store, string $name): Product
{
    return Product::create([
        'store_id'  => $store->id,
        'name'      => $name,
        'slug'      => Str::slug($name).'-'.uniqid(),
        'sku'       => 'SKU-'.strtoupper(Str::random(6)),
        'type'      => 'simple',
        'price'     => 500,
        'is_active' => true,
    ]);
}

function vsVariant(Product $product): App\Models\Products\ProductVariant
{
    return App\Models\Products\ProductVariant::create([
        'product_id' => $product->id,
        'store_id'   => $product->store_id,
        'name'       => 'Default',
        'sku'        => 'VSV-'.strtoupper(Str::random(6)),
        'price'      => 500,
        'is_active'  => true,
    ]);
}

function vsOrder(
    Store $store,
    string $statusKey,
    ?StoreMembership $assignee = null,
    array $products = [],
    ?App\Domains\Shipping\Models\ShippingProvider $provider = null,
): Order {
    $customer = Customer::create([
        'store_id' => $store->id,
        'name'     => 'Visibility Customer '.Str::random(5),
        'phone'    => '0553'.fake()->unique()->numerify('######'),
        'status'   => true,
    ]);

    $order = Order::create([
        'store_id'                   => $store->id,
        'customer_id'                => $customer->id,
        'status_id'                  => vsStatus($statusKey)->id,
        'number'                     => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount'               => 900,
        'shipping_cost'              => 0,
        'assigned_to_membership_id'  => $assignee?->id,
        'assigned_at'                => $assignee ? now() : null,
        'assignment_method'          => $assignee ? 'automatic' : null,
        'shipping_provider_id'       => $provider?->id,
    ]);

    foreach ($products as $product) {
        $variant = vsVariant($product);

        OrderItem::create([
            'store_id'           => $store->id,
            'order_id'           => $order->id,
            'product_variant_id'    => $variant->id,
            'product_id'         => $product->id,
            'quantity'           => 1,
            'price'              => 500,
            'subtotal'           => 500,
        ]);
    }

    return $order;
}

function vsShipmentProvider(Store $store): App\Domains\Shipping\Models\ShippingProvider
{
    return App\Domains\Shipping\Models\ShippingProvider::create([
        'store_id'               => $store->id,
        'name'                   => 'Vis Carrier',
        'code'                   => 'VIS',
        'is_active'              => true,
        'credentials'            => [],
        'shipment_types_enabled' => ['delivery'],
    ]);
}

function vsTracking(Order $order, App\Domains\Shipping\Models\ShippingProvider $provider, string $status): OrderTracking
{
    return OrderTracking::create([
        'store_id'            => $order->store_id,
        'order_id'            => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number'     => 'VSTRK'.uniqid(),
        'tracking_status'     => $status,
        'shipped_at'          => now()->subDay(),
    ]);
}

function vsQueueOrder(Store $store, string $statusKey, ?StoreMembership $assignee = null): Order
{
    return vsOrder($store, $statusKey, $assignee);
}

// ————— 1. Owner/admin sees every order —————

it('lets an owner membership see every order regardless of assignment', function () {
    [$owner, $store, $ownerMembership] = vsUser();
    $staff = vsMembership($store, StoreRoleEnum::STAFF);
    $manager = vsMembership($store, StoreRoleEnum::MANAGER);

    vsOrder($store, 'pending', $staff);
    vsOrder($store, 'confirmed', $manager);
    vsOrder($store, 'shipped');

    $ids = Order::query()
        ->visibleTo($ownerMembership)
        ->pluck('id');

    expect($ids)->toHaveCount(3);
});

// ————— 2. Staff sees only their own orders —————

it('lets a staff membership see only orders assigned to its own membership', function () {
    [$owner, $store] = vsUser();
    $staff = vsMembership($store, StoreRoleEnum::STAFF);
    $peer = vsMembership($store, StoreRoleEnum::STAFF);
    $manager = vsMembership($store, StoreRoleEnum::MANAGER);

    $mine = vsOrder($store, 'pending', $staff);
    vsOrder($store, 'pending', $peer);
    vsOrder($store, 'pending', $manager);
    vsOrder($store, 'pending');

    $ids = Order::query()->visibleTo($staff)->pluck('id');

    expect($ids)->toHaveCount(1)
        ->and($ids->first())->toBe($mine->id);
});

// ————— 3. Manager (TEAM_VIEW_OWN, no product scope) sees self + subordinates —————

it('lets a manager without product assignments see their own orders and supervised staff orders only', function () {
    [$owner, $store] = vsUser();
    $manager = vsMembership($store, StoreRoleEnum::MANAGER);
    $supervised = vsMembership($store, StoreRoleEnum::STAFF, $manager);
    $other = vsMembership($store, StoreRoleEnum::STAFF);

    $own = vsOrder($store, 'pending', $manager);
    $staffOrder = vsOrder($store, 'pending', $supervised);
    vsOrder($store, 'pending', $other);

    $ids = Order::query()->visibleTo($manager->fresh())->pluck('id')->sort()->values();

    expect($ids)->toHaveCount(2)
        ->and($ids)->toContain($own->id)
        ->and($ids)->toContain($staffOrder->id);
});

// ————— 4. Manager with a product scope sees only matching-product team orders —————

it('narrows a manager with product assignments to team orders containing that product', function () {
    [$owner, $store] = vsUser();
    $manager = vsMembership($store, StoreRoleEnum::MANAGER);
    $supervised = vsMembership($store, StoreRoleEnum::STAFF, $manager);

    $scopeProduct = vsProduct($store, 'Scoped Product');
    $otherProduct = vsProduct($store, 'Unrelated');

    app(StoreProductScopeService::class)->assign($store, $manager, $scopeProduct);

    $visible = vsOrder($store, 'pending', $supervised, [$scopeProduct]);
    vsOrder($store, 'pending', $supervised, [$otherProduct]);          // team but wrong product
    vsOrder($store, 'pending', $manager, [$scopeProduct]);              // own + scoped product
    vsOrder($store, 'pending', $manager, [$otherProduct]);              // own but wrong product

    $ids = Order::query()->visibleTo($manager->fresh())->pluck('id');

    expect($ids)->toHaveCount(2)
        ->and($ids)->toContain($visible->id);
});

// ————— 5. TrackingGridConcern lists respect the same scoping for a staff member —————

it('scopes the tracking grid main listing, trash listing and trash count for a staff membership', function () {
    [$owner, $store] = vsUser();
    $staff = vsMembership($store, StoreRoleEnum::STAFF);
    $other = vsMembership($store, StoreRoleEnum::STAFF);

    // toggleTrash() gates on ORDER_DELETE; grant it so the staff member can
    // actually reach the trash bin — the scoping under test is visibility,
    // which ORDER_DELETE does not loosen (staff still lacks TEAM_VIEW*).
    $staff->syncPermissions(array_merge(
        StoreRoles::permissions(StoreRoleEnum::STAFF),
        [StorePermissionEnum::ORDER_DELETE->value],
    ));

    $provider = vsShipmentProvider($store);

    $mine = vsOrder($store, 'shipped', $staff, provider: $provider);
    $theirs = vsOrder($store, 'shipped', $other, provider: $provider);

    vsTracking($mine, $provider, OrderTrackingStatus::SHIPPED->value);
    vsTracking($theirs, $provider, OrderTrackingStatus::SHIPPED->value);

    // A second mine/theirs pair is soft-deleted for the trash view.
    $mineTrashed = vsOrder($store, 'shipped', $staff, provider: $provider);
    $theirsTrashed = vsOrder($store, 'shipped', $other, provider: $provider);
    vsTracking($mineTrashed, $provider, OrderTrackingStatus::SHIPPED->value);
    vsTracking($theirsTrashed, $provider, OrderTrackingStatus::SHIPPED->value);
    $mineTrashed->delete();
    $theirsTrashed->delete();

    vsActAs($staff->user, $store);

    $volt = Volt::test('merchant.tracking.index');

    $volt->assertSet('trashCount', 1)
        ->assertSet('shipments', fn ($rows) => count($rows) === 1
            && collect($rows)->pluck('id')->contains((string) $mine->id));

    $volt->call('toggleTrash')->assertSet('showTrash', true);

    $volt->assertSet('shipments', fn ($rows) => count($rows) === 1
        && collect($rows)->pluck('id')->contains((string) $mineTrashed->id));
});

// ————— 6. Queue page regression: unchanged by the visibility work —————

it('leaves the distribution-queue screen row set unchanged (no visibility behavior gained or lost)', function () {
    [$owner, $store, $ownerMembership] = vsUser();
    $confirmAgent = vsMembership($store, StoreRoleEnum::STAFF);
    $confirmAgent->syncPermissions([
        StorePermissionEnum::ORDER_VIEW->value,
        StorePermissionEnum::ORDER_CONFIRM->value,
    ]);

    vsOrder($store, 'pending');                       // unassigned → confirmation tab
    vsOrder($store, 'confirmed', $confirmAgent)->update(['over_capacity' => true]); // over capacity
    vsOrder($store, 'confirmed', $confirmAgent);      // within cap → excluded
    vsOrder($store, 'delivered');                     // terminal → excluded

    // The queue page must show exactly the two expected rows for the actor,
    // proving visibleTo is NOT implicitly chained anywhere on that page.
    vsActAs($owner, $store);

    Volt::test('merchant.order-distribution-queue')
        ->assertSet('confirmationCount', 2);
});