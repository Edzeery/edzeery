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

/* ───────────────────── Toolbar scanner-button visibility ───────────────────── */

test('the carrier-validate button appears beside the search when a shipment needs validation', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    tbvOrder($store, $provider, $customer, 'TBV-NEED');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertSet('bulkValidateNeedsCount', 1)
        ->assertSeeHtml('wire:click="openBulkValidateModal"');
});

test('the carrier-validate button is hidden when nothing needs validation', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    tbvOrder($store, $provider, $customer, 'TBV-DONE', [
        'carrier_validated_at' => now(),
        'carrier_validated_by_membership_id' => null,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertSet('bulkValidateNeedsCount', 0)
        ->assertDontSeeHtml('wire:click="openBulkValidateModal"');
});

test('the carrier-validate button is hidden on the rider tab', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    tbvOrder($store, $provider, $customer, 'TBV-RIDER');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->set('trackingTab', 'rider')
        ->assertDontSeeHtml('wire:click="openBulkValidateModal"');
});

test('the carrier-validate button is hidden in trash mode', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    tbvOrder($store, $provider, $customer, 'TBV-TRASH');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('toggleTrash')
        ->assertDontSeeHtml('wire:click="openBulkValidateModal"');
});

test('the carrier-validate button is hidden without the order.dispatch_validate permission', function () {
    [$user, $store] = tbvUser(StoreRoleEnum::STAFF->value);
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    tbvOrder($store, $provider, $customer, 'TBV-STAFF');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->assertDontSeeHtml('wire:click="openBulkValidateModal"');
});

/* ───────────────────────────── Scanner modal ───────────────────────────── */

test('the scanner modal opens with the permission even on an empty page', function () {
    [$user, $store] = tbvUser();

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('openBulkValidateModal')
        ->assertSet('showBulkValidateModal', true)
        ->assertSee(__('order_flow.validate_shipment_title'));
});

test('bulkValidateFromBarcode validates the scan immediately and appends the result', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    $order = tbvOrder($store, $provider, $customer, 'TBV-SCAN');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake(['noest.test/*' => Http::response(['success' => true])]);

    Volt::test('merchant.tracking.index')
        ->call('bulkValidateFromBarcode', 'TBV-SCAN')
        ->assertSet('bulkValidateResults', fn ($rows) => count($rows) === 1
            && $rows[0]['tracking_number'] === 'TBV-SCAN'
            && $rows[0]['ok'] === true)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    expect(app(OrderTrackingService::class)->currentTracking($order->fresh())->isCarrierValidated())->toBeTrue();
});

test('bulkValidateFromBarcode reports an unknown tracking number as a failure row', function () {
    [$user, $store] = tbvUser();

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.tracking.index')
        ->call('bulkValidateFromBarcode', 'NO-SUCH-TRACKING')
        ->assertSet('bulkValidateResults', fn ($rows) => count($rows) === 1
            && $rows[0]['tracking_number'] === 'NO-SUCH-TRACKING'
            && $rows[0]['ok'] === false)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');
});

/* ──────────────────────── Bulk bar direct validation ──────────────────────── */

test('runBulkValidate validates the selection, reports per-shipment results, and audits', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    $first = tbvOrder($store, $provider, $customer, 'TBV-ONE');
    $second = tbvOrder($store, $provider, $customer, 'TBV-TWO');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake(['noest.test/*' => Http::response(['success' => true])]);

    Volt::test('merchant.tracking.index')
        ->set('selectedShipments', [(string) $first->id, (string) $second->id])
        ->call('runBulkValidate')
        ->assertSet('showBulkValidateResults', true)
        ->assertSet('showBulkValidateModal', false)
        ->assertSet('selectedShipments', [])
        ->assertSet('bulkValidateResults', fn ($rows) => count($rows) === 2
            && collect($rows)->every(fn ($row) => $row['ok'] === true))
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'success');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/valid/orders'));

    foreach ([$first, $second] as $order) {
        $tracking = app(OrderTrackingService::class)->currentTracking($order->fresh());

        expect($tracking->isCarrierValidated())->toBeTrue()
            ->and(OrderTrackingHistory::where('order_id', $order->id)->where('status', 'carrier_validated')->count())->toBe(1)
            ->and(OrderEvent::where('order_id', $order->id)->where('event_type', 'carrier_validated')->count())->toBe(1);
    }
});

test('runBulkValidate reports the carrier rejection reason per shipment', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    $ok = tbvOrder($store, $provider, $customer, 'TBV-OK');
    $bad = tbvOrder($store, $provider, $customer, 'TBV-BAD');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake([
        'noest.test/*' => Http::response([
            'success' => true,
            'passed' => ['TBV-OK' => true],
            'failed' => ['TBV-BAD' => 'Stock insuffisant'],
        ]),
    ]);

    Volt::test('merchant.tracking.index')
        ->set('selectedShipments', [(string) $ok->id, (string) $bad->id])
        ->call('runBulkValidate')
        ->assertSet('bulkValidateResults', fn ($rows) => count($rows) === 2
            && collect($rows)->firstWhere('tracking_number', 'TBV-BAD')['ok'] === false
            && collect($rows)->firstWhere('tracking_number', 'TBV-BAD')['message'] === 'Stock insuffisant')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'warning');

    $service = app(OrderTrackingService::class);

    expect($service->currentTracking($ok->fresh())->isCarrierValidated())->toBeTrue()
        ->and($service->currentTracking($bad->fresh())->isCarrierValidated())->toBeFalse()
        ->and($service->currentTracking($bad->fresh())->carrier_validation_error)->toBe('Stock insuffisant');
});

test('runBulkValidate reports skipped shipments with their reason', function () {
    [$user, $store] = tbvUser();
    $provider = tbvProvider($store);
    $customer = tbvCustomer($store);
    $news = tbvOrder($store, $provider, $customer, 'TBV-NEW');
    $already = tbvOrder($store, $provider, $customer, 'TBV-DONE', [
        'carrier_validated_at' => now(),
        'carrier_validated_by_membership_id' => null,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake(['noest.test/*' => Http::response(['success' => true])]);

    Volt::test('merchant.tracking.index')
        ->set('selectedShipments', [(string) $news->id, (string) $already->id])
        ->call('runBulkValidate')
        ->assertSet('bulkValidateResults', fn ($rows) => count($rows) === 2
            && collect($rows)->firstWhere('tracking_number', 'TBV-NEW')['ok'] === true
            && collect($rows)->firstWhere('tracking_number', 'TBV-DONE')['ok'] === false
            && collect($rows)->firstWhere('tracking_number', 'TBV-DONE')['message'] === __('order_flow.shipment_already_validated'));
});

test('runBulkValidate chunks large batches into multiple /valid/orders calls', function () {
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
        ->set('selectedShipments', $orders->pluck('id')->map('strval')->values()->toArray())
        ->call('runBulkValidate')
        ->assertSet('bulkValidateResults', fn ($rows) => collect($rows)->every(fn ($row) => $row['ok'] === true))
        ->assertDispatched('swal:toast');

    expect(count($bodies))->toBe(2)
        ->and(collect($bodies)->every(fn ($chunk) => count($chunk) <= 100))->toBeTrue()
        ->and(collect($bodies)->flatten()->unique()->count())->toBe(105);

    $orders->each(function (Order $order) {
        expect(app(OrderTrackingService::class)->currentTracking($order->fresh())->isCarrierValidated())->toBeTrue();
    });
});

/* ────────────────────────────── Permissions ────────────────────────────── */

test('openBulkValidateModal is refused without the dispatch_validate permission', function () {
    [$user, $store] = tbvUser(StoreRoleEnum::STAFF->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake();

    Volt::test('merchant.tracking.index')
        ->call('openBulkValidateModal')
        ->assertSet('showBulkValidateModal', false)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');

    Http::assertNothingSent();
});

test('runBulkValidate is refused without the dispatch_validate permission', function () {
    [$user, $store] = tbvUser(StoreRoleEnum::STAFF->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake();

    Volt::test('merchant.tracking.index')
        ->call('runBulkValidate')
        ->assertSet('showBulkValidateResults', false)
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error');

    Http::assertNothingSent();
});

test('bulkValidateFromBarcode is refused without the dispatch_validate permission', function () {
    [$user, $store] = tbvUser(StoreRoleEnum::STAFF->value);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake();

    Volt::test('merchant.tracking.index')
        ->call('bulkValidateFromBarcode', 'TBV-ANY')
        ->assertDispatched('swal:toast', fn ($name, $params) => ($params[0]['icon'] ?? null) === 'error')
        ->assertSet('bulkValidateResults', []);

    Http::assertNothingSent();
});