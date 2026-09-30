<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
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

/**
 * §6.3 is about order.edit.products. Per §2.2 this grant is store-wide, so no
 * visibleTo() is consulted. order.edit.price is deliberately ABSENT: price is
 * double-gated and needs BOTH permissions plus the store's allow_price_edit
 * setting (itemsPriceEditable(), index.blade.php:299-310).
 */
const PRODUCTS_GRANT = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::ORDER_EDIT_PRODUCTS->value,
];

const PRODUCTS_PRICE_GRANT = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::ORDER_EDIT_PRODUCTS->value,
    StorePermissionEnum::ORDER_EDIT_PRICE->value,
];

const MANAGE_GRANT = [
    StorePermissionEnum::ORDER_VIEW->value,
    StorePermissionEnum::ORDER_MANAGE->value,
];

function oepOwner(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name'    => 'Products Store',
        'slug'    => 'oep-'.uniqid(),
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

function oepStaff(Store $store, array $permissions, string $name = 'Rep'): array
{
    $user = User::factory()->create(['name' => $name]);

    $membership = StoreMembership::create([
        'store_id'   => $store->id,
        'user_id'    => $user->id,
        'invited_by' => $store->user_id,
        'is_active'  => true,
        'role'       => StoreRoleEnum::STAFF->value,
    ]);
    $membership->syncPermissions($permissions);

    return [$user, $membership];
}

function oepOrder(Store $store, string $statusKey = 'pending', ?StoreMembership $assignee = null): Order
{
    $status = Status::system()->forType('order')->where('key', $statusKey)->firstOrFail();

    $country = Country::firstOrCreate(['code' => 'DZ'], ['name' => 'Algeria', 'arabic_name' => 'الجزائر', 'is_active' => true]);
    $state = State::firstOrCreate(['country_id' => $country->id, 'state_code' => '01'], ['name' => 'Adrar', 'arabic_name' => 'أدرار', 'is_active' => true]);
    $city = City::firstOrCreate(['state_id' => $state->id, 'name' => 'Adrar Centre'], ['post_code' => '01000', 'is_active' => true]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name'     => 'OEP Customer '.Str::random(5),
        'phone'    => '0553'.fake()->unique()->numerify('######'),
        'status'   => true,
    ]);

    $product = Product::create([
        'store_id'  => $store->id,
        'name'      => 'OEP Product',
        'slug'      => 'oep-pr-'.uniqid(),
        'sku'       => 'OEP-'.strtoupper(Str::random(6)),
        'type'      => 'simple',
        'price'     => 400,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id'  => $store->id,
        'product_id' => $product->id,
        'name'      => 'Default',
        'sku'       => 'oep-v-'.uniqid(),
        'price'     => 400,
        'stock'     => 10,
        'is_active' => true,
    ]);

    $order = Order::create([
        'store_id'                  => $store->id,
        'customer_id'               => $customer->id,
        'status_id'                 => $status->id,
        'number'                    => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount'              => 400,
        'shipping_cost'             => 0,
        'assigned_to_membership_id' => $assignee?->id,
        'assigned_at'               => $assignee ? now() : null,
        'assignment_method'         => $assignee ? 'automatic' : null,
        'state_id'                  => $state->id,
        'city_id'                   => $city->id,
        'address'                   => 'Rue des Cedres',
        'delivery_type'             => 'home',
    ]);

    OrderItem::create([
        'store_id'           => $store->id,
        'order_id'           => $order->id,
        'product_id'         => $product->id,
        'product_variant_id' => $variant->id,
        'quantity'           => 1,
        'price'              => 400,
        'subtotal'           => 400,
    ]);

    return $order->fresh();
}

/** itemsPriceEditable() also needs the store setting, not just the permission. */
function oepAllowPriceEdit(Store $store, bool $allowed = true): void
{
    $store->settings()->updateOrCreate(['store_id' => $store->id], ['allow_price_edit' => $allowed]);
}

function oepVolt(User $user, Store $store)
{
    actingAs($user)->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();

    return Volt::test('merchant.orders.index');
}

function oepDenied(): Closure
{
    return fn ($n, $p) => ($p[0]['title'] ?? null) === __('messages.permission_denied');
}

// ————— 1. store-wide scope, including unassigned orders —————

it('lets a products-only member edit products fields on an unassigned order', function () {
    [$ownerUser, $store] = oepOwner();
    [$repUser, $rep] = oepStaff($store, PRODUCTS_GRANT, 'Rep');

    // Unassigned on purpose: §2.2 makes this grant store-wide, so a stray
    // visibleTo() would surface here as a denial.
    $order = oepOrder($store, 'pending', null);

    oepVolt($repUser, $store)
        ->call('openItemsModal', 'products', $order->id)
        ->assertSet('itemsModal.kind', 'products');

    oepVolt($repUser, $store)
        ->call('openItemsModal', 'quantity', $order->id)
        ->assertSet('itemsModal.kind', 'quantity');

    oepVolt($repUser, $store)
        ->call('startOrderWeightEdit', $order->id)
        ->set('editingValue', '5.5')
        ->call('saveOrderWeight', '5.5');

    expect($order->fresh()->weight_kg)->toEqual(5.5);

    oepVolt($repUser, $store)
        ->call('startOrderShipmentTypeEdit', $order->id)
        ->call('saveOrderShipmentType', 'delivery');

    expect($order->fresh()->shipment_type)->toBe('delivery');

    oepVolt($repUser, $store)
        ->call('startOrderDiscountEdit', $order->id)
        ->set('discountEditType', 'percent')
        ->set('discountEditValue', '10')
        ->set('discountEditReason', 'rep discount')
        ->call('saveOrderDiscount');

    expect($order->fresh()->discount_type)->toBe('percent')
        ->and($order->fresh()->discount_value)->toEqual(10);
});

it('saves a new item set for a products-only member', function () {
    [$ownerUser, $store] = oepOwner();
    [$repUser, $rep] = oepStaff($store, PRODUCTS_GRANT, 'Rep');
    $order = oepOrder($store, 'pending', $rep);

    $variant = ProductVariant::where('store_id', $store->id)->firstOrFail();

    oepVolt($repUser, $store)
        ->call('openItemsModal', 'products', $order->id)
        ->set('form.items', [[
            'product_variant_id' => $variant->id,
            'quantity'           => 3,
            'price'              => 400,
        ]])
        ->call('saveOrderItems')
        ->assertNotDispatched('swal:toast', oepDenied());

    expect($order->items()->first()->quantity)->toBe(3);
});

// ————— 2. price stays double-gated —————

it('denies the price field to a products-only member even when the store allows it', function () {
    [$ownerUser, $store] = oepOwner();
    [$repUser, $rep] = oepStaff($store, PRODUCTS_GRANT, 'Rep');
    $order = oepOrder($store, 'pending', $rep);

    oepAllowPriceEdit($store, true);

    // The store setting is on and the member holds order.edit.products, but
    // order.edit.price is missing, so itemsPriceEditable() stays false and the
    // :320 sub-gate in $openItemsModal still refuses.
    oepVolt($repUser, $store)
        ->call('openItemsModal', 'price', $order->id)
        ->assertSet('itemsModal', null);

    // And the save path must not honour a submitted price either.
    $variant = ProductVariant::where('store_id', $store->id)->firstOrFail();
    $before = $order->items()->first()->price;

    oepVolt($repUser, $store)
        ->call('openItemsModal', 'products', $order->id)
        ->set('form.items', [[
            'product_variant_id' => $variant->id,
            'quantity'           => 2,
            'price'              => 9999,
        ]])
        ->call('saveOrderItems');

    $item = $order->items()->first();

    expect($item->quantity)->toBe(2)
        ->and((float) $item->price)->toEqual((float) $before)
        ->and((float) $item->price)->not->toEqual(9999.0);
});

it('lets a products+price member edit price once the store allows it', function () {
    [$ownerUser, $store] = oepOwner();
    [$repUser, $rep] = oepStaff($store, PRODUCTS_PRICE_GRANT, 'Rep');
    $order = oepOrder($store, 'pending', $rep);

    oepAllowPriceEdit($store, true);

    oepVolt($repUser, $store)
        ->call('openItemsModal', 'price', $order->id)
        ->assertSet('itemsModal.kind', 'price');

    $variant = ProductVariant::where('store_id', $store->id)->firstOrFail();

    oepVolt($repUser, $store)
        ->call('openItemsModal', 'price', $order->id)
        ->set('form.items', [[
            'product_variant_id' => $variant->id,
            'quantity'           => 2,
            'price'              => 9999,
        ]])
        ->call('saveOrderItems');

    expect((float) $order->items()->first()->price)->toEqual(9999.0);
});

it('still refuses price for a products+price member when the store setting is off', function () {
    [$ownerUser, $store] = oepOwner();
    [$repUser, $rep] = oepStaff($store, PRODUCTS_PRICE_GRANT, 'Rep');
    $order = oepOrder($store, 'pending', $rep);

    oepAllowPriceEdit($store, false);

    oepVolt($repUser, $store)
        ->call('openItemsModal', 'price', $order->id)
        ->assertSet('itemsModal', null);
});

// ————— 3. no widening beyond products —————

it('denies identity and geography to a products-only member', function () {
    [$ownerUser, $store] = oepOwner();
    [$repUser, $rep] = oepStaff($store, PRODUCTS_GRANT, 'Rep');
    $order = oepOrder($store, 'pending', $rep);

    foreach (['startOrderNameEdit', 'startOrderPhoneEdit', 'startOrderNotesEdit', 'startOrderWilayaEdit', 'startOrderCityEdit'] as $handler) {
        oepVolt($repUser, $store)
            ->call($handler, $order->id)
            ->assertDispatched('swal:toast', oepDenied());
    }
});

it('denies products edits to a member with neither order.manage nor order.edit.products', function () {
    [$ownerUser, $store] = oepOwner();
    [$plainUser, $plain] = oepStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Plain');
    $order = oepOrder($store, 'pending', $plain);

    oepAllowPriceEdit($store, true);

    foreach (['startOrderWeightEdit', 'startOrderShipmentTypeEdit', 'startOrderDiscountEdit'] as $handler) {
        oepVolt($plainUser, $store)
            ->call($handler, $order->id)
            ->assertDispatched('swal:toast', oepDenied());
    }

    foreach (['products', 'quantity', 'price'] as $kind) {
        oepVolt($plainUser, $store)
            ->call('openItemsModal', $kind, $order->id)
            ->assertSet('itemsModal', null);
    }

    // The save handler is gated too, so a forced editingId cannot bypass it.
    $before = $order->fresh()->weight_kg;

    oepVolt($plainUser, $store)
        ->set('editingId', $order->id)
        ->set('editingValue', '7')
        ->call('saveOrderWeight', '7')
        ->assertDispatched('swal:toast', oepDenied());

    expect((float) $order->fresh()->weight_kg)->toEqual((float) $before);
});

// ————— 4. order.manage holder: zero regression —————

it('keeps an order.manage holder working exactly as before', function () {
    [$ownerUser, $store] = oepOwner();
    [$mgrUser, $mgr] = oepStaff($store, MANAGE_GRANT, 'Manager');
    $order = oepOrder($store, 'pending', $mgr);

    foreach (['products', 'quantity'] as $kind) {
        oepVolt($mgrUser, $store)
            ->call('openItemsModal', $kind, $order->id)
            ->assertSet('itemsModal.kind', $kind);
    }

    oepVolt($mgrUser, $store)
        ->call('startOrderWeightEdit', $order->id)
        ->set('editingValue', '2.25')
        ->call('saveOrderWeight', '2.25');

    expect($order->fresh()->weight_kg)->toEqual(2.25);

    oepVolt($mgrUser, $store)
        ->call('startOrderDiscountEdit', $order->id)
        ->set('discountEditType', 'amount')
        ->set('discountEditValue', '50')
        ->set('discountEditReason', 'manager')
        ->call('saveOrderDiscount');

    expect($order->fresh()->discount_type)->toBe('amount')
        ->and($order->fresh()->discount_value)->toEqual(50);
});

it('keeps the owner price path intact for an order.manage holder', function () {
    [$ownerUser, $store] = oepOwner();
    [$mgrUser, $mgr] = oepStaff($store, MANAGE_GRANT, 'Manager');
    $order = oepOrder($store, 'pending', $mgr);

    // ORDER_MANAGE alone never unlocked price before this phase, and the
    // products OR must not change that: order.edit.price is still required.
    oepAllowPriceEdit($store, true);

    oepVolt($mgrUser, $store)
        ->call('openItemsModal', 'price', $order->id)
        ->assertSet('itemsModal', null);
});

// ————— 5. display twins —————

it('renders the products twins for products-only and order.manage members, and hides them otherwise', function () {
    [$ownerUser, $store] = oepOwner();
    [$repUser, $rep] = oepStaff($store, PRODUCTS_GRANT, 'Rep');
    [$mgrUser, $mgr] = oepStaff($store, MANAGE_GRANT, 'Manager');
    [$plainUser, $plain] = oepStaff($store, [StorePermissionEnum::ORDER_VIEW->value], 'Plain');

    $columns = ['products', 'quantity', 'discount', 'weight', 'shipment_type'];

    // openItemsModal kinds appear twice: desktop cell plus the mobile
    // "edit items" menu entry. The three saveEdit cells have a desktop twin
    // only, because orders-mobile-fields is still a single aggregate
    // canManage() block that §6.3 deliberately does not touch.
    $expected = [
        "openItemsModal('products'"             => 2,
        "openItemsModal('quantity'"             => 2,
        'startOrderDiscountEdit'               => 1,
        'startOrderWeightEdit'                 => 1,
        'startOrderShipmentTypeEdit'           => 1,
    ];

    // The orders list is assignment-scoped for staff (36.4), so each viewer
    // needs a row of their own for the twins to be on screen at all.
    foreach (['products-only' => [$repUser, $rep], 'order.manage' => [$mgrUser, $mgr]] as $label => [$user, $membership]) {
        oepOrder($store, 'pending', $membership);
        $html = oepVolt($user, $store)->set('visibleColumns', $columns)->html();

        foreach ($expected as $trigger => $min) {
            $count = substr_count($html, $trigger);
            expect($count)->toBeGreaterThanOrEqual($min, "{$label} should expose {$trigger} {$min}x (saw {$count})");
        }
    }

    oepOrder($store, 'pending', $plain);
    $html = oepVolt($plainUser, $store)->set('visibleColumns', $columns)->html();

    foreach (array_keys($expected) as $trigger) {
        expect(substr_count($html, $trigger))->toBe(0, "plain member must not see {$trigger}");
    }
});

it('gates the price cell on itemsPriceEditable whatever unlocked the rest of the row', function () {
    [$ownerUser, $store] = oepOwner();
    [$repUser, $rep] = oepStaff($store, PRODUCTS_GRANT, 'Rep');
    [$bothUser, $both] = oepStaff($store, PRODUCTS_PRICE_GRANT, 'Both');

    oepOrder($store, 'pending', $rep);
    oepOrder($store, 'pending', $both);

    // Store setting off: neither member may reach the price cell.
    oepAllowPriceEdit($store, false);
    $html = oepVolt($repUser, $store)->set('visibleColumns', ['price'])->html();
    expect(substr_count($html, "openItemsModal('price'"))->toBe(0);

    $html = oepVolt($bothUser, $store)->set('visibleColumns', ['price'])->html();
    expect(substr_count($html, "openItemsModal('price'"))->toBe(0);

    // Store setting on: only the member who also holds order.edit.price.
    oepAllowPriceEdit($store, true);
    $html = oepVolt($repUser, $store)->set('visibleColumns', ['price'])->html();
    expect(substr_count($html, "openItemsModal('price'"))->toBe(0)
        ->and(substr_count($html, "openItemsModal('products'"))->toBeGreaterThanOrEqual(1);

    $html = oepVolt($bothUser, $store)->set('visibleColumns', ['price'])->html();
    expect(substr_count($html, "openItemsModal('price'"))->toBeGreaterThanOrEqual(1);
});
