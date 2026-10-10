<?php

use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Order\Support\OrderStatusCapture;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Services\NoestTrackingSyncService;
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
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function fcoActor(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Capture Flow Store',
        'slug' => 'cf-'.uniqid(),
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

function fcoGeography(): array
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

function fcoProvider(Store $store): ShippingProvider
{
    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'Capture Carrier',
        'code' => 'cf-local',
        'credentials' => [],
        'is_active' => true,
    ]);
}

function fcoVariant(Store $store): ProductVariant
{
    $product = Product::create([
        'store_id' => $store->id,
        'name' => 'Capture Product',
        'slug' => 'cf-pr-'.uniqid(),
        'sku' => 'cf-sku-'.uniqid(),
        'type' => 'simple',
        'price' => 800,
        'is_active' => true,
    ]);

    return ProductVariant::create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'name' => 'Default',
        'sku' => 'cf-v-'.uniqid(),
        'price' => 800,
        'stock' => 10,
        'is_active' => true,
    ]);
}

function fcoOrder(Store $store, array $opts = []): Order
{
    $pending = Status::system()->forType('order')->where('key', 'pending')->firstOrFail();

    [$state, $city] = fcoGeography();

    $order = Order::create([
        'store_id' => $store->id,
        'status_id' => $pending->id,
        'total_amount' => 900,
        'shipping_cost' => $opts['shipping_cost'] ?? 100,
        'payment_method' => $opts['payment_method'] ?? 'cod',
        'state_id' => $state->id,
        'city_id' => $city->id,
        'address' => 'Rue des Cedres 12',
        'delivery_type' => 'home',
        'shipping_provider_id' => fcoProvider($store)->id,
        'discount_type' => $opts['discount_type'] ?? null,
        'discount_value' => $opts['discount_value'] ?? null,
    ]);

    OrderItem::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'product_variant_id' => fcoVariant($store)->id,
        'quantity' => $opts['quantity'] ?? 1,
        'price' => 800,
        'subtotal' => ($opts['quantity'] ?? 1) * 800,
    ]);

    app(\App\Support\StoreContext::class)->set($store);

    return $order->fresh();
}

function fcoAdvance(OrderService $service, Order $order, string $target, ?StoreMembership $member = null): Order
{
    // "done" means shipped-and-delivering is already captured at "preparing";
    // advance up to shipping without needing the final delivery stamp.
    $limit = $target === 'done' ? 'preparing' : $target;

    foreach (['confirmed', 'preparing', 'shipped', 'delivered'] as $step) {
        $order = $service->transition($order, $step, null, $member, 'manual');

        if ($step === $limit) {
            return $order;
        }
    }

    throw new RuntimeException("Unsupported advance target [{$target}].");
}

test('the first confirmation wins and no later transition can rewrite the stamp', function () {
    [, $store, $member1] = fcoActor();
    [, , $member2] = fcoActor();
    $app = app(OrderService::class);

    $order = fcoOrder($store);
    $order = $app->transition($order, 'confirmed', null, $member1, 'manual');

    $first = $order->fresh();
    expect($first->confirmed_by_membership_id)->toBe($member1->id)
        ->and($first->confirmed_at)->not->toBeNull();

    $stamp = $first->confirmed_at->format('Y-m-d H:i:s');

    // Re-transitions (cancel -> re-confirm, reassignment) must not rewrite.
    $order = $app->transition($order, 'cancelled', 'change of mind', $member1, 'manual');
    $order = $app->transition($order, 'pending', null, $member1, 'manual');
    $order = $app->transition($order, 'confirmed', null, $member2, 'manual');

    $after = $order->fresh();
    expect($after->confirmed_by_membership_id)->toBe($member1->id)
        ->and($after->confirmed_at->format('Y-m-d H:i:s'))->toBe($stamp);
});

test('two concurrent confirmations produce exactly one CAS winner', function () {
    [, $store, $member] = fcoActor();
    $order = fcoOrder($store);
    $t1 = Carbon::parse('2026-10-01 09:00:00');
    $t2 = Carbon::parse('2026-10-01 10:00:00');

    expect(OrderStatusCapture::stampConfirmation($order, (string) $member->id, $t1))->toBeTrue();

    $losing = OrderStatusCapture::stampConfirmation($order, 'some-other-membership', $t2);
    expect($losing)->toBeFalse();

    $row = $order->fresh();
    expect($row->confirmed_at->format('Y-m-d H:i:s'))->toBe('2026-10-01 09:00:00')
        ->and($row->confirmed_by_membership_id)->toBe($member->id);
});

test('a missing actor leaves the confirmation unattributed - NULL, never zero', function () {
    [, $store] = fcoActor();
    $app = app(OrderService::class);

    $order = $app->transition(fcoOrder($store), 'confirmed', null, null, 'system');

    expect($order->confirmed_at)->not->toBeNull()
        ->and($order->confirmed_by_membership_id)->toBeNull();
});

test('delivering stamps delivered_at once and carrier evidence is a separate first-write column', function () {
    [, $store, $member] = fcoActor();
    $app = app(OrderService::class);

    $order = fcoAdvance($app, fcoOrder($store), 'delivered', $member);

    $stamp = $order->fresh()->delivered_at->format('Y-m-d H:i:s');

    // Manual delivery writes orders.delivered_at only — never evidence.
    expect($order->delivery_evidence_at)->toBeNull();

    $tracking = $order->trackings()->first();
    expect($tracking)->not->toBeNull();

    // A later carrier-sourced delivered event lands in delivery_evidence_at.
    $carrierDate = '2026-10-02 14:30:00';
    $entry = [
        'OrderInfo' => ['OrderNumber' => (string) $order->number],
        'activity' => [
            ['event' => 'Livre', 'event_key' => 'livre', 'date' => $carrierDate],
        ],
    ];

    app(NoestTrackingSyncService::class)->apply($tracking, $entry);

    expect($order->fresh()->delivery_evidence_at->format('Y-m-d H:i:s'))->toBe($carrierDate)
        ->and($order->fresh()->delivered_at->format('Y-m-d H:i:s'))->toBe($stamp);

    // Re-syncing never rewrites the evidence.
    app(NoestTrackingSyncService::class)->apply($tracking->fresh(), [
        'OrderInfo' => [],
        'activity' => [
            ['event' => 'Livré', 'event_key' => 'livre', 'date' => '2026-10-03 08:00:00'],
        ],
    ]);

    expect($order->fresh()->delivery_evidence_at->format('Y-m-d H:i:s'))->toBe($carrierDate);
});

test('returning stamps returned_at and the reason key once, even across requeue cycles', function () {
    [, $store, $member] = fcoActor();
    $app = app(OrderService::class);

    $order = fcoAdvance($app, fcoOrder($store), 'delivered', $member);
    $order = $app->transition($order, 'returned', null, $member, 'manual', 'wrong_item');

    expect($order->returned_at)->not->toBeNull()
        ->and($order->return_reason_key)->toBe('wrong_item');

    $stamp = $order->returned_at->format('Y-m-d H:i:s');

    // A forced second return event cannot rewrite the first-write-wins stamp.
    $returned = Status::system()->forType('order')->where('key', 'returned')->firstOrFail();
    $order = $app->transitionToStatus($order, $returned, null, $member, true, 'manual', 'bad_address');

    expect($order->fresh()->returned_at->format('Y-m-d H:i:s'))->toBe($stamp)
        ->and($order->fresh()->return_reason_key)->toBe('wrong_item');
});

test('a store-custom status stays stage "other" and never fires a capture', function () {
    [, $store, $member] = fcoActor();
    $pending = Status::system()->forType('order')->where('key', 'pending')->firstOrFail();

    $custom = Status::create([
        'store_id' => $store->id,
        'type' => 'order',
        'key' => 'needs_review',
        'label' => 'Needs review',
        'is_system' => false,
    ]);

    // The migration default keeps runtime-created custom statuses neutral.
    expect($custom->fresh()->stage)->toBe('other');

    $order = fcoOrder($store);

    $app = app(OrderService::class);
    $order = $app->transitionToStatus($order, $custom, 'blocked', $member, true, 'manual');

    $fresh = $order->fresh();
    expect($fresh->confirmed_at)->toBeNull()
        ->and($fresh->delivered_at)->toBeNull()
        ->and($fresh->returned_at)->toBeNull();

    // The history row still carries the provenance it was given.
    expect($fresh->statusHistories()->latest('id')->first()->source)->toBe('manual');
});

test('cod_amount is snapshotted when the shipment starts and never rewritten', function () {
    [, $store, $member] = fcoActor();
    $app = app(OrderService::class);

    $order = fcoOrder($store, [
        'shipping_cost' => 100,
        'discount_type' => 'amount',
        'discount_value' => 50,
    ]);

    $order = fcoAdvance($app, $order, 'done', $member); // confirmed, preparing
    $order = $app->transition($order, 'shipped', null, $member, 'manual');

    $tracking = $order->trackings()->first();
    $order = $order->fresh();

    // 800 subtotal + 100 shipping - 50 discount, floored at 0.
    expect((string) $tracking->cod_amount)->toBe('850.00')
        ->and((string) $tracking->tracked_by_membership_id)->toBe((string) $member->id);

    // A later price change never rewrites what the carrier collects for.
    $order->update(['total_amount' => 5000]);

    app(OrderTrackingService::class)->startShipment($order->fresh(), null, 'other-member');

    expect($order->trackings()->count())->toBe(1)
        ->and((string) $order->trackings()->first()->cod_amount)->toBe('850.00')
        ->and((string) $order->trackings()->first()->tracked_by_membership_id)->toBe((string) $member->id);
});

test('non-COD orders keep the carrier cod_amount collection NULL', function () {
    [, $store, $member] = fcoActor();
    $app = app(OrderService::class);

    $order = fcoOrder($store, ['payment_method' => 'bank']);
    $order = fcoAdvance($app, $order, 'done', $member); // confirmed, preparing
    $order = $app->transition($order, 'shipped', null, $member, 'manual');

    expect($order->trackings()->first()->cod_amount)->toBeNull();
});

test('exactly one shipping event per order ever despite a cancelled and re-sent cycle', function () {
    [, $store, $member] = fcoActor();
    $app = app(OrderService::class);

    $order = fcoOrder($store);
    $order = fcoAdvance($app, $order, 'done', $member); // confirmed, preparing
    $order = $app->transition($order, 'shipped', null, $member, 'manual');

    expect($order->trackings()->count())->toBe(1);

    // Shipment cancelled (parcel deleted off-platform) -> back to confirmed.
    $order = $app->revertTo($order, 'confirmed', 'carrier cancelled', $member, 'manual');

    // Re-sent through the same cycle: still one shipping event, same creator.
    $order = $app->transition($order, 'preparing', null, $member, 'manual');
    $order = $app->transition($order, 'shipped', null, $member, 'manual');

    $trackings = $order->trackings()->get();
    expect($trackings)->toHaveCount(1)
        ->and((string) $trackings->first()->tracked_by_membership_id)->toBe((string) $member->id);
});
