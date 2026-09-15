<?php

use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function tbvUser(string $role = StoreRoleEnum::OWNER->value): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($role, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Tbv Store',
        'slug' => 'tbv-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $role,
    ]);

    return [$user, $store, $membership];
}

function tbvProvider(Store $store): ShippingProvider
{
    $platform = CarrierPlatform::updateOrCreate(
        ['slug' => 'noest'],
        ['name' => 'NOEST', 'is_active' => true],
    );

    $carrier = Carrier::updateOrCreate(
        ['code' => 'noest'],
        [
            'platform_id' => $platform->id,
            'name' => 'NOEST',
            'is_active' => true,
        ],
    );

    return ShippingProvider::create([
        'store_id' => $store->id,
        'name' => 'NOEST DZ',
        'code' => 'noest',
        'carrier_id' => $carrier->id,
        'credentials' => [
            'api_token' => 'tok-'.uniqid(),
            'guid' => 'guid-'.uniqid(),
            'api_base' => 'https://noest.test/api/public',
        ],
        'is_active' => true,
    ]);
}

function tbvCustomer(Store $store): Customer
{
    return Customer::create([
        'store_id' => $store->id,
        'name' => 'Tbv Customer',
        'phone' => '0552'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);
}

function tbvOrder(Store $store, ShippingProvider $provider, Customer $customer, string $trackingNumber, array $trackingExtra = []): Order
{
    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1500,
        'shipping_provider_id' => $provider->id,
        'shipping_cost' => 0,
        'status_id' => \App\Models\Status::system()->forType('order')->where('key', 'shipped')->firstOrFail()->id,
    ]);

    OrderTracking::create(array_merge([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'shipping_provider_id' => $provider->id,
        'tracking_number' => $trackingNumber,
        'shipped_at' => now(),
    ], $trackingExtra));

    return $order->fresh();
}

test('the bulk-validate FAB is shown on the carrier tab with the permission', function () {
    [$user, $store] = tbvUser();

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertSeeHtml('wire:click="openBulkValidateModal"');
});

test('the bulk-validate FAB is hidden on the rider tab', function () {
    [$user, $store] = tbvUser();

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->assertDontSeeHtml('wire:click="openBulkValidateModal"');
});

test('the bulk-validate FAB is hidden in trash mode', function () {
    [$user, $store] = tbvUser();

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('toggleTrash')
        ->assertDontSeeHtml('wire:click="openBulkValidateModal"');
});

test('the bulk-validate FAB is hidden without the order.dispatch_validate permission', function () {
    [$user, $store] = tbvUser(StoreRoleEnum::STAFF->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertDontSeeHtml('wire:click="openBulkValidateModal"');
});

test('openBulkValidateModal analyzes the current page and counts ready/skipped', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    $ready = tbvOrder($store, $provider, $customer, 'TBV-READY');
    $validated = tbvOrder($store, $provider, $customer, 'TBV-DONE', [
        'carrier_validated_at' => now(),
        'carrier_validated_by_membership_id' => null,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('openBulkValidateModal')
        ->assertSet('showBulkValidateModal', true)
        ->assertSet('bulkValidateReadyCount', 1)
        ->assertSet('bulkValidateSkipCount', 1)
        ->assertSee(__('order_flow.bulk_validate_ready_title'));
});

test('confirmBulkValidate hands ready shipments over and audits the events', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    $first = tbvOrder($store, $provider, $customer, 'TBV-ONE');
    $second = tbvOrder($store, $provider, $customer, 'TBV-TWO');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake(['noest.test/*' => Http::response(['success' => true])]);

    Volt::test('merchant.tracking.index')
        ->call('openBulkValidateModal')
        ->assertSet('bulkValidateReadyCount', 2)
        ->call('confirmBulkValidate')
        ->assertDispatched('swal:toast');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/valid/orders'));

    foreach ([$first, $second] as $order) {
        $tracking = app(OrderTrackingService::class)->currentTracking($order->fresh());

        expect($tracking->isCarrierValidated())->toBeTrue()
            ->and(OrderTrackingHistory::where('order_id', $order->id)->where('status', 'carrier_validated')->count())->toBe(1)
            ->and(OrderEvent::where('order_id', $order->id)->where('event_type', 'carrier_validated')->count())->toBe(1);
    }
});

test('confirmBulkValidate chunks large batches into multiple /valid/orders calls', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);

    $orders = collect(range(1, 105))->map(
        fn ($i) => tbvOrder($store, $provider, $customer, sprintf('TBV-%03d', $i))
    );

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $bodies = [];
    Http::fake(function ($request) use (&$bodies) {
        if (str_contains($request->url(), '/valid/orders')) {
            $bodies[] = $request->data()['trackings'] ?? [];

            return Http::response(['success' => true]);
        }

        return Http::response([], 404);
    });

    Volt::test('merchant.tracking.index')
        ->set('perPage', 150)
        ->call('refresh')
        ->call('openBulkValidateModal')
        ->assertSet('bulkValidateReadyCount', 105)
        ->call('confirmBulkValidate')
        ->assertDispatched('swal:toast');

    expect(count($bodies))->toBe(2)
        ->and(collect($bodies)->every(fn ($chunk) => count($chunk) <= 100))->toBeTrue()
        ->and(collect($bodies)->flatten()->unique()->count())->toBe(105);

    $orders->each(function (Order $order) {
        expect(app(OrderTrackingService::class)->currentTracking($order->fresh())->isCarrierValidated())->toBeTrue();
    });
});

test('bulkValidateFromBarcode validates a scanned shipment and updates the analysis', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    $order = tbvOrder($store, $provider, $customer, 'TBV-SCAN');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake(['noest.test/*' => Http::response(['success' => true])]);

    Volt::test('merchant.tracking.index')
        ->call('openBulkValidateModal')
        ->assertSet('bulkValidateReadyCount', 1)
        ->call('bulkValidateFromBarcode', 'TBV-SCAN')
        ->assertSet('bulkValidateReadyCount', 0)
        ->assertDispatched('swal:toast');

    expect(app(OrderTrackingService::class)->currentTracking($order->fresh())->isCarrierValidated())->toBeTrue();
});

test('openBulkValidateModal does nothing when the page has no shipments', function () {
    [$user, $store] = tbvUser();

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('openBulkValidateModal')
        ->assertSet('showBulkValidateModal', false)
        ->assertDispatched('swal:toast');
});

test('bulk validation entry points are refused without the dispatch_validate permission', function () {
    [$user, $store] = tbvUser(StoreRoleEnum::STAFF->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake();

    Volt::test('merchant.tracking.index')
        ->call('openBulkValidateModal')
        ->assertStatus(403);

    Http::assertNothingSent();
});

test('confirmBulkValidate is refused without the dispatch_validate permission', function () {
    [$user, $store] = tbvUser(StoreRoleEnum::STAFF->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('confirmBulkValidate')
        ->assertStatus(403);
});

test('bulkValidateFromBarcode is refused without the dispatch_validate permission', function () {
    [$user, $store] = tbvUser(StoreRoleEnum::STAFF->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake();

    Volt::test('merchant.tracking.index')
        ->call('bulkValidateFromBarcode', 'TBV-ANY')
        ->assertStatus(403);

    Http::assertNothingSent();
});