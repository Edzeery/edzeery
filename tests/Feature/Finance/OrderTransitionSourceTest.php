<?php

use App\Domains\Cart\Services\CartService;
use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Services\ReturnVerificationService;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Domains\Shipping\Services\OrderShippingGateway;
use App\Enums\Store\ReturnInspectionResult;
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
use Illuminate\Support\Facades\Artisan;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

afterEach(function () {
    app(\App\Support\StoreContext::class)->clear();
});

function fcsActor(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Source Store',
        'slug' => 'src-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => 'owner',
    ]);

    app(\App\Support\StoreContext::class)->set($store);

    return [$user, $store, $membership];
}

function fcsGeography(): array
{
    $country = Country::firstOrCreate(
        ['code' => 'DZ'],
        ['name' => 'Algeria', 'is_active' => true],
    );

    $state = State::firstOrCreate(
        ['country_id' => $country->id, 'state_code' => '01'],
        ['name' => 'Adrar', 'is_active' => true],
    );

    $city = City::firstOrCreate(
        ['state_id' => $state->id, 'name' => 'Adrar Centre'],
        ['post_code' => '01000', 'is_active' => true],
    );

    return [$state, $city];
}

function fcsProvider(Store $store): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Source Carrier',
        'code' => 'src-local',
        'credentials' => [],
        'is_active' => true,
    ]);
}

function fcsVariant(Store $store): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Source Product',
        'slug' => 'src-pr-'.uniqid(),
        'sku' => 'src-sku-'.uniqid(),
        'type' => 'simple',
        'price' => 800,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'src-v-'.uniqid(),
        'price' => 800,
        'stock' => 10,
        'is_active' => true,
    ]);
}

function fcsOrder(Store $store, string $statusKey = 'pending'): Order
{
    $status = Status::system()->forType('order')->where('key', $statusKey)->firstOrFail();

    [$state, $city] = fcsGeography();

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Source Customer',
        'phone' => '0559'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'status_id' => $status->id,
        'total_amount' => 900,
        'shipping_cost' => 100,
        'payment_method' => 'cod',
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => 'Rue des Cedres 12',
        'delivery_type' => 'home',
        'shipping_provider_id' => fcsProvider($store)->id,
    ]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => fcsVariant($store)->id,
        'quantity' => 1,
        'price' => 800,
        'subtotal' => 800,
    ]);

    return $order->fresh();
}

function fcsAdvance(OrderService $service, Order $order, string $target, ?StoreMembership $member = null): Order
{
    foreach (['confirmed', 'preparing', 'shipped', 'delivered'] as $step) {
        $order = $service->transition($order, $step, null, $member, 'manual');

        if ($step === $target) {
            return $order;
        }
    }

    throw new RuntimeException("Unsupported advance target [{$target}].");
}

test('manual order creation records a NULL from_status with the manual source', function () {
    [, $store, $membership] = fcsActor();
    [$state, $city] = fcsGeography();
    $variant = fcsVariant($store);

    $order = app(OrderService::class)->createManual([
        'customer_id' => Customer::create([
            'store_id' => $store->id,
            'name' => 'Manual Customer',
            'phone' => '0551'.fake()->unique()->numerify('######'),
            'status' => true,
        ])->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => 'Rue de la Source 3',
        'delivery_type' => 'home',
        'payment_method' => 'cod',
        'shipping_provider_id' => fcsProvider($store)->id,
        'items' => [
            ['product_variant_id' => $variant->id, 'product_id' => $variant->product_id, 'quantity' => 1, 'price' => 800],
        ],
    ], $membership);

    $history = $order->statusHistories()->first();

    expect($history->from_status)->toBeNull()
        ->and($history->source)->toBe('manual')
        ->and((string) $history->changed_by_membership_id)->toBe((string) $membership->id)
        ->and($order->fresh()->confirmed_at)->toBeNull();
});

test('a single UI transition records the previous status and the manual source', function () {
    [, $store, $member] = fcsActor();
    $order = app(OrderService::class)->transition(fcsOrder($store), 'confirmed', null, $member, 'manual');

    $history = $order->statusHistories()->latest('id')->first();

    expect($history->from_status)->toBe('pending')
        ->and($history->source)->toBe('manual')
        ->and((string) $history->changed_by_membership_id)->toBe((string) $member->id);
});

test('bulk transitions are tagged with the bulk source', function () {
    [, $store, $member] = fcsActor();
    $order = app(OrderService::class)->transition(fcsOrder($store), 'confirmed', null, $member, 'bulk');

    $history = $order->statusHistories()->latest('id')->first();

    expect($history->from_status)->toBe('pending')
        ->and($history->source)->toBe('bulk');
});

test('the scheduled auto-cancel command sources its transitions as system', function () {
    [, $store] = fcsActor();
    fcsOrder($store);

    // Backdate so the second-resolution created_at is safely inside the cutoff
    // window (a same-second boundary would flakily exclude it).
    Order::where('store_id', $store->id)->update(['created_at' => now()->subHours(2)]);

    Artisan::call('orders:auto-cancel-pending', ['--hours' => 1]);

    $order = Order::where('store_id', $store->id)->firstOrFail();
    expect($order->fresh()->status?->key)->toBe('cancelled');

    $history = $order->statusHistories()->latest('id')->first();

    expect($history->from_status)->toBe('pending')
        ->and($history->source)->toBe('system')
        ->and($history->changed_by_membership_id)->toBeNull();
});

test('a return requeue after verification sources its transition as system', function () {
    [, $store, $member] = fcsActor();
    $order = fcsAdvance(app(OrderService::class), fcsOrder($store), 'delivered', $member);
    $order = app(OrderService::class)->transition($order, 'returned', null, $member, 'manual');

    $tracking = $order->trackings()->first();

    // Simulated barcode verification + inspection (good condition).
    $tracking->update([
        'verified_at' => now(),
        'verified_by_membership_id' => $member->id,
    ]);

    app(ReturnVerificationService::class)->process($tracking, ReturnInspectionResult::GOOD, 'sellable again', $member);

    $requeued = app(ReturnVerificationService::class)->requeue($tracking, $member);

    expect($requeued->fresh()->status?->key)->toBe('pending');

    $history = $requeued->statusHistories()->latest('id')->first();

    expect($history->from_status)->toBe('returned')
        ->and($history->source)->toBe('system');
});

test('the confirm-and-send gateway forwards the UI source to every transition', function () {
    [, $store, $member] = fcsActor();

    $service = app(OrderService::class);
    $gateway = app(OrderShippingGateway::class);

    $result = $gateway->send(fcsOrder($store), null, null, $member, true, 'manual');

    $order = $result['order'];

    expect($order->status?->key)->toBe('shipped');

    $sources = $order->statusHistories()->orderBy('id')->pluck('source')->all();

    expect($sources)->toBe(['manual', 'manual', 'manual']);
});

test('a storefront placement records the storefront source with no earning stamp', function () {
    $state = fcsState('Storefront Source');
    $city = fcsCity($state, 'Front Com');
    $store = fcsStorefrontStore();
    $provider = fcsProvider($store);

    DeliveryRate::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'home_cost' => 400,
        'office_cost' => 350,
        'is_active' => true,
    ]);

    $point = StopdeskPoint::create([
        'store_id' => $store->id,
        'shipping_provider_id' => $provider->id,
        'state_id' => $state->id,
        'city_id' => $city->id,
        'name' => 'Front Desk',
        'address' => 'Front Road 1',
        'is_active' => true,
    ]);

    test()->artisan('db:seed', ['--class' => \Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);

    $cart = app(CartService::class);
    $cart->addItem($store->id, fcsVariant($store)->id, 1);

    Volt::test('storefront.order-form')
        ->set('name', 'Front Customer')
        ->set('phone', '0551000000')
        ->set('state_id', (string) $state->id)
        ->set('city_id', (string) $city->id)
        ->set('delivery_type', 'stopdesk')
        ->set('selectedStopdesk', (string) $point->id)
        ->set('payment_method', 'cod')
        ->call('submitOrder')
        ->assertHasNoErrors();

    $order = Order::where('store_id', $store->id)->latest('id')->first();

    expect($order)->not->toBeNull();

    $history = $order->statusHistories()->first();

    expect($history->from_status)->toBeNull()
        ->and($history->source)->toBe('storefront')
        ->and($order->fresh()->confirmed_at)->toBeNull();
});

// ---- Storefront-domain helpers (mirror the cascade suite's store/geo setup) ----

function fcsStorefrontStore(): Store
{
    $user = \App\Models\User::factory()->create();

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Storefront Source Store',
        'slug' => 'src-front-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    config(['app.domain' => 'example.test']);
    test()->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->set($store);

    return $store;
}

function fcsState(string $name): State
{
    $code = strtoupper(substr(md5($name), 0, 2));
    $country = Country::create(['name' => $name.'land', 'code' => $code.'L', 'is_active' => true]);

    return State::create([
        'country_id' => $country->id,
        'state_code' => $code.'-01',
        'name' => $name,
        'is_active' => true,
        'is_cod_available' => true,
    ]);
}

function fcsCity(State $state, string $name): City
{
    return City::create([
        'state_id' => $state->id,
        'name' => $name,
        'post_code' => (string) random_int(1000, 9999),
        'is_active' => true,
    ]);
}
