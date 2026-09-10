<?php

use App\Domains\Cart\Support\OrderRules;
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
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Blade;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function itemsUser(string $storeRole = 'owner'): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Items Store',
        'slug' => 'items-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store, $membership];
}

function itemsGeography(): array
{
    $country = Country::create([
        'name' => 'Algeria',
        'code' => 'DZ',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $state = State::create([
        'country_id' => $country->id,
        'state_code' => '16',
        'name' => 'Alger',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    $city = City::create([
        'state_id' => $state->id,
        'name' => 'Bab Ezzouar',
        'post_code' => '16028',
        'is_active' => true,
        'is_cod_available' => true,
    ]);

    return [$state, $city];
}

function itemsVariant(Store $store, string $label, float $price = 500, int $stock = 100): array
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => "{$label} Product",
        'slug' => 'items-pr-'.strtolower($label).'-'.uniqid(),
        'sku' => 'items-sku-'.uniqid(),
        'type' => 'variable',
        'price' => $price,
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'items-v-'.uniqid(),
        'price' => $price,
        'stock' => $stock,
        'is_active' => true,
    ]);

    return [$product, $variant];
}

function itemsOrder(Store $store, State $state, City $city, array $items, string $statusKey = 'pending', string $phone = '0550000000'): Order
{
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Items Customer',
        'phone' => $phone,
        'status' => true,
    ]);

    $status = \App\Models\Status::system()->forType('order')->where('key', $statusKey)->first();

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => collect($items)->sum(fn ($i) => $i['quantity'] * $i['price']) ?: 0,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
        'shipping_cost' => 0,
    ]);

    foreach ($items as $item) {
        OrderItem::create([
            'store_id' => $store->id,
            'order_id' => $order->id,
            'product_variant_id' => $item['variant']->id,
            'product_id' => $item['product']->id,
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'subtotal' => $item['quantity'] * $item['price'],
        ]);
    }

    return $order;
}

function itemsVolt(array $userStore): object
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.orders.index');
}

test('products and quantity cells open their per-column modals preloaded from the order items', function () {
    [$staff, $store, $membership] = itemsUser(StoreRoleEnum::STAFF->value);
    $membership->syncPermissions([StorePermissionEnum::ORDER_VIEW, StorePermissionEnum::ORDER_MANAGE]);

    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 2, 'price' => 450],
    ]);

    // Staff without FORM_EDITED (owner) or ORDER_EDIT_PRICE keep the static
    // price column even though allow_price_edit is enabled later by the store.
    itemsVolt([$staff, $store])
        ->assertSeeHtml("openItemsModal('products'")
        ->assertSeeHtml("openItemsModal('quantity'")
        ->assertDontSeeHtml("openItemsModal('price'")
        ->call('openItemsModal', 'products', $order->id)
        ->assertSet('itemsModal.kind', 'products')
        ->assertSet('itemsModal.orderId', $order->id)
        ->assertSet('editingField', 'order.items')
        ->assertSet('editingId', $order->id)
        ->assertSet('form.items.0.product_variant_id', $variantA->id)
        ->assertSet('form.items.0.quantity', 2)
        ->call('closeItemsModal')
        ->assertSet('itemsModal', null)
        ->assertSet('editingField', null)
        ->assertSet('form.items', []);
});

test('without the allow_price_edit setting the modal save forces the DB price even for staff with order.edit.price', function () {
    [$staff, $store, $membership] = itemsUser(StoreRoleEnum::STAFF->value);
    $membership->syncPermissions([StorePermissionEnum::ORDER_VIEW, StorePermissionEnum::ORDER_MANAGE, StorePermissionEnum::ORDER_EDIT_PRICE]);

    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 2, 'price' => 450],
    ]);

    itemsVolt([$staff, $store])
        ->call('openItemsModal', 'products', $order->id)
        ->set('form.items.0.price', 999.0)
        ->set('form.items.0.quantity', 3)
        ->call('saveOrderItems')
        ->assertSet('editingField', null);

    $order->refresh();
    $item = $order->items()->first();

    expect((float) $item->price)->toBe(450.0)
        ->and($item->quantity)->toBe(3)
        ->and((float) $item->subtotal)->toBe(1350.0);
});

test('with allow_price_edit enabled the owner price modal override is honored and totals recompute', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 2, 'price' => 450],
    ]);

    $store->settings()->updateOrCreate([], ['allow_price_edit' => true]);

    itemsVolt([$user, $store])
        ->assertSeeHtml("openItemsModal('price'")
        ->call('openItemsModal', 'price', $order->id)
        ->assertSet('itemsModal.kind', 'price')
        ->assertSeeHtml('updateInlineItemPrice')
        ->set('form.items.0.price', 420.0)
        ->set('form.items.0.quantity', 3)
        ->call('saveOrderItems')
        ->assertSet('editingField', null);

    $order->refresh();
    $item = $order->items()->first();

    expect((float) $item->price)->toBe(420.0)
        ->and($item->quantity)->toBe(3)
        ->and((float) $item->subtotal)->toBe(1260.0)
        ->and((float) $order->total_amount)->toBe(1260.0);
});

test('C3 FORM_EDITED: the store owner keeps the price modal even when allow_price_edit is off and the override is honored', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 1, 'price' => 450],
    ]);

    expect((bool) ($store->settings?->allow_price_edit ?? false))->toBeFalse();

    itemsVolt([$user, $store])
        ->assertSeeHtml("openItemsModal('price'")
        ->call('openItemsModal', 'price', $order->id)
        ->assertSet('itemsModal.kind', 'price')
        ->set('form.items.0.price', 380.0)
        ->call('saveOrderItems')
        ->assertSet('editingField', null);

    $order->refresh();

    expect((float) $order->items()->first()->price)->toBe(380.0);
});

test('staff with a custom order.manage permission gets no price modal and prices stay DB-bound', function () {
    [$staff, $store, $membership] = itemsUser(StoreRoleEnum::STAFF->value);
    $membership->syncPermissions([StorePermissionEnum::ORDER_VIEW, StorePermissionEnum::ORDER_MANAGE]);

    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 2, 'price' => 450],
    ]);

    $store->settings()->updateOrCreate([], ['allow_price_edit' => true]);

    itemsVolt([$staff, $store])
        ->assertSeeHtml("openItemsModal('quantity'")
        ->assertDontSeeHtml("openItemsModal('price'")
        ->call('openItemsModal', 'price', $order->id)
        ->assertSet('itemsModal', null)
        ->call('openItemsModal', 'quantity', $order->id)
        ->assertSet('itemsModal.kind', 'quantity')
        ->assertSeeHtml('updateFormItemQty')
        ->set('form.items.0.price', 999.0)
        ->set('form.items.0.quantity', 4)
        ->call('saveOrderItems')
        ->assertSet('editingField', null);

    $order->refresh();
    $item = $order->items()->first();

    expect((float) $item->price)->toBe(450.0)
        ->and($item->quantity)->toBe(4);
});

test('staff with both order.manage and order.edit.price sees and honors the price modal input', function () {
    [$staff, $store, $membership] = itemsUser(StoreRoleEnum::STAFF->value);
    $membership->syncPermissions([StorePermissionEnum::ORDER_VIEW, StorePermissionEnum::ORDER_MANAGE, StorePermissionEnum::ORDER_EDIT_PRICE]);

    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 1, 'price' => 450],
    ]);

    $store->settings()->updateOrCreate([], ['allow_price_edit' => true]);

    itemsVolt([$staff, $store])
        ->assertSeeHtml("openItemsModal('price'")
        ->call('openItemsModal', 'price', $order->id)
        ->assertSeeHtml('updateInlineItemPrice')
        ->set('form.items.0.price', 380.0)
        ->call('saveOrderItems')
        ->assertSet('editingField', null);

    $order->refresh();

    expect((float) $order->items()->first()->price)->toBe(380.0);
});

test('the price setting alone is not enough: staff with order.manage but no order.edit.price stays DB-bound even when enabled', function () {
    [$staff, $store, $membership] = itemsUser(StoreRoleEnum::STAFF->value);
    $membership->syncPermissions([StorePermissionEnum::ORDER_VIEW, StorePermissionEnum::ORDER_MANAGE]);

    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 1, 'price' => 450],
    ]);

    $store->settings()->updateOrCreate([], ['allow_price_edit' => true]);

    itemsVolt([$staff, $store])
        ->assertDontSeeHtml("openItemsModal('price'")
        ->call('openItemsModal', 'price', $order->id)
        ->assertSet('itemsModal', null)
        ->call('openItemsModal', 'products', $order->id)
        ->call('saveOrderItems')
        ->assertSet('editingField', null);

    $order->refresh();

    expect((float) $order->items()->first()->price)->toBe(450.0);
});

test('removing one item and adding another persists both changes', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450, 5);
    [$productB, $variantB] = itemsVariant($store, 'Beta', 300, 5);
    [$productC, $variantC] = itemsVariant($store, 'Gamma', 800, 5);

    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 1, 'price' => 450],
        ['variant' => $variantB, 'product' => $productB, 'quantity' => 2, 'price' => 300],
    ]);

    itemsVolt([$user, $store])
        ->call('openItemsModal', 'products', $order->id)
        ->call('removeInlineItem', 1)
        ->call('addInlineItem', $variantC->id)
        ->call('saveOrderItems')
        ->assertSet('editingField', null);

    $order->refresh();
    $variantIds = $order->items()->pluck('product_variant_id')->map(fn ($id) => (string) $id)->all();

    expect($variantIds)->toContain((string) $variantA->id)
        ->toContain((string) $variantC->id)
        ->not->toContain((string) $variantB->id);
});

test('backorder-disabled stores block the modal save when the delta exceeds available stock', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450, 2);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 2, 'price' => 450],
    ]);

    $store->settings()->updateOrCreate([], [
        'inventory_tracking' => true,
        'allow_backorder' => false,
    ]);

    itemsVolt([$user, $store])
        ->call('openItemsModal', 'products', $order->id)
        ->set('form.items.0.quantity', 6)
        ->call('saveOrderItems')
        ->assertSet('editingField', 'order.items')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');

    expect($order->refresh()->items()->first()->quantity)->toBe(2);
});

test('items editing stays blocked for shipped orders', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 1, 'price' => 450],
    ], 'shipped');

    itemsVolt([$user, $store])
        ->call('openItemsModal', 'products', $order->id)
        ->set('form.items.0.quantity', 3)
        ->call('saveOrderItems')
        ->assertSet('editingField', 'order.items')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');

    $order->refresh();

    expect($order->items()->first()->quantity)->toBe(1);
});

test('staff without order.manage cannot open any items modal', function () {
    [$staff, $store] = itemsUser(StoreRoleEnum::STAFF->value);
    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 1, 'price' => 450],
    ]);

    itemsVolt([$staff, $store])
        ->assertDontSeeHtml("openItemsModal('products'")
        ->assertDontSeeHtml("openItemsModal('quantity'")
        ->assertDontSeeHtml("openItemsModal('price'")
        ->call('openItemsModal', 'products', $order->id)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error'
            && ($params[0]['title'] ?? null) === __('messages.permission_denied'))
        ->assertSet('itemsModal', null)
        ->assertNotSet('editingField', 'order.items');

    expect($order->refresh()->items()->count())->toBe(1);
});

test('the allow_price_edit store setting defaults to false and round-trips through settings', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);

    expect((bool) ($store->settings?->allow_price_edit ?? false))->toBeFalse();

    $store->settings()->updateOrCreate([], ['allow_price_edit' => true]);

    expect((bool) $store->fresh()->settings->allow_price_edit)->toBeTrue();
});

test('the edz checkbox renders the checked attribute only when the value is truthy', function () {
    $htmlTrue = Blade::render('<x-edz.checkbox :checked="true" />');
    $htmlFalse = Blade::render('<x-edz.checkbox :checked="false" />');
    $htmlNone = Blade::render('<x-edz.checkbox />');

    expect(preg_match('/\schecked\b/', $htmlTrue))->toBe(1)
        ->and($htmlFalse)->not->toMatch('/\schecked\b/')
        ->and($htmlNone)->not->toMatch('/\schecked\b/');
});

test('the products, quantity and price cells show selection hints for an order without items', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = itemsGeography();
    $order = itemsOrder($store, $state, $city, []);

    $volt = itemsVolt([$user, $store]);

    foreach (['products', 'quantity', 'price'] as $kind) {
        $volt->assertSeeHtml("openItemsModal('{$kind}'");
    }

    $volt->assertSee(__('merchant_panel.please_select_product'), false)
        ->assertSee(__('merchant_panel.please_select_quantity'), false);

    $volt->call('openItemsModal', 'products', $order->id)
        ->assertSee(__('merchant_panel.please_select_product'), false)
        ->assertSee(__('merchant_panel.please_select_quantity'), false);

    expect($order->refresh()->items()->count())->toBe(0);
});

test('the items edit modals listen to their own close event only, never a global window one', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 1, 'price' => 450],
    ]);

    itemsVolt([$user, $store])
        ->call('openItemsModal', 'products', $order->id)
        ->assertSet('itemsModal.kind', 'products')
        ->assertSeeHtml('edz-modal-closed="$wire.closeItemsModal()"')
        ->assertDontSeeHtml('edz-modal-closed.window');
});

test('closing the items modal or opening create/edit keeps the picker map in sync with the draft', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450);
    [$productB, $variantB] = itemsVariant($store, 'Beta', 300);
    $orderA = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 1, 'price' => 450],
    ]);
    $orderB = itemsOrder($store, $state, $city, [
        ['variant' => $variantB, 'product' => $productB, 'quantity' => 2, 'price' => 300],
    ], 'pending', '0550000001');

    itemsVolt([$user, $store])
        // Items-edit session for A, then close: the map must clear with the draft.
        ->call('openItemsModal', 'products', $orderA->id)
        ->assertSet('formSelectedItems.' . $variantA->id, 1)
        ->call('closeItemsModal')
        ->assertSet('itemsModal', null)
        ->assertSet('formSelectedItems', [])
        // Create modal starts fresh: nothing may look pre-selected.
        ->call('openCreateModal')
        ->assertSet('formSelectedItems', [])
        // Edit modal rebuilds from THAT order's items only.
        ->call('openEditModal', $orderB->id)
        ->assertSet('formSelectedItems', [$variantB->id => 2]);
});

test('the product picker shows an all-variants-added badge only once every variant is in the draft', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = itemsGeography();

    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Multi Product',
        'slug' => 'multi-pr-' . uniqid(),
        'sku' => 'multi-sku-' . uniqid(),
        'type' => 'variable',
        'price' => 700,
        'is_active' => true,
    ]);

    $variantS = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Size S',
        'sku' => 'multi-v-s-' . uniqid(),
        'price' => 700,
        'stock' => 10,
        'is_active' => true,
    ]);

    $variantL = ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Size L',
        'sku' => 'multi-v-l-' . uniqid(),
        'price' => 800,
        'stock' => 10,
        'is_active' => true,
    ]);

    $volt = itemsVolt([$user, $store])
        ->call('openCreateModal')
        ->set('showProductPickerModal', true)
        ->assertDontSee(__('merchant_panel.all_variants_added'), false);

    $volt->call('addFormItem', $variantS->id)
        ->assertDontSee(__('merchant_panel.all_variants_added'), false);

    $volt->call('addFormItem', $variantL->id)
        ->assertSee(__('merchant_panel.all_variants_added'), false);
});

test('quantity steppers derive preorder from the draft row without a variant database lookup', function () {
    [$user, $store] = itemsUser(StoreRoleEnum::OWNER->value);
    [$state, $city] = itemsGeography();
    [$productA, $variantA] = itemsVariant($store, 'Alpha', 450, 5);
    $order = itemsOrder($store, $state, $city, [
        ['variant' => $variantA, 'product' => $productA, 'quantity' => 1, 'price' => 450],
    ]);

    $store->settings()->updateOrCreate([], [
        'inventory_tracking' => true,
        'allow_backorder' => true,
    ]);

    $volt = itemsVolt([$user, $store]);

    $queries = [];
    \Illuminate\Support\Facades\DB::listen(function ($q) use (&$queries): void {
        $queries[] = $q->sql;
    });

    $volt->call('openItemsModal', 'quantity', $order->id)
        ->call('updateFormItemQty', 0, 4)
        ->assertSet('form.items.0.quantity', 4)
        ->assertSet('form.items.0.preorder', true);

    $variantQueries = array_values(array_filter(
        $queries,
        fn ($sql) => str_contains($sql, 'product_variants')
    ));

    expect($variantQueries)->toBeEmpty();
});