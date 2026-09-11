<?php

use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
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

function bdvUser(string $storeRole = StoreRoleEnum::OWNER->value): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Bdv Store',
        'slug' => 'bdv-'.uniqid(),
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

function bdvProvider(Store $store): ShippingProvider
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

function bdvOrder(
    Store $store,
    ?ShippingProvider $provider,
    ?string $trackingNumber = null,
    array $trackingExtra = [],
): Order {
    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Bdv Customer',
        'phone' => '0552'.fake()->unique()->numerify('######'),
        'status' => true,
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 1500,
        'shipping_provider_id' => $provider?->id,
        'shipping_cost' => 0,
    ]);

    if ($provider && $trackingNumber) {
        OrderTracking::create(array_merge([
            'store_id' => $store->id,
            'order_id' => $order->id,
            'shipping_provider_id' => $provider->id,
            'tracking_number' => $trackingNumber,
            'shipped_at' => now(),
        ], $trackingExtra));
    }

    return $order->fresh();
}

function bdvToastTitle(array $params): ?string
{
    $payload = ($params[0] ?? null) && is_array($params[0]) ? $params[0] : $params;

    return $payload['title'] ?? null;
}

test('bulk validate modal is denied without order.dispatch_validate', function () {
    [$user, $store] = bdvUser(StoreRoleEnum::STAFF->value);
    $provider = bdvProvider($store);
    bdvOrder($store, $provider, 'NOEST1');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.orders.index')
        ->call('openBulkValidateModal')
        ->assertDispatched('swal:toast', fn ($name, $params) => bdvToastTitle($params) === __('messages.permission_denied'))
        ->assertSet('showBulkValidateModal', false);
});

test('classifies selected orders into ready and skipped for dispatch validation', function () {
    [$user, $store] = bdvUser(StoreRoleEnum::OWNER->value);
    $provider = bdvProvider($store);

    $readyA = bdvOrder($store, $provider, 'NOEST-A');
    $readyB = bdvOrder($store, $provider, 'NOEST-B');
    $validated = bdvOrder($store, $provider, 'NOEST-V', ['carrier_validated_at' => now()]);
    $rider = bdvOrder($store, null);
    $noTracking = bdvOrder($store, $provider, null);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    $test = Volt::test('merchant.orders.index')
        ->set('selectedOrders', [$readyA->id, $readyB->id, $validated->id, $rider->id, $noTracking->id])
        ->call('openBulkValidateModal');

    $test->assertSet('showBulkValidateModal', true)
        ->assertSet('bulkValidateReadyCount', 2)
        ->assertSet('bulkValidateSkipCount', 3);

    $analysis = collect($test->get('bulkValidateAnalysis'))->keyBy('number');

    expect($analysis[$readyA->number]['ready'])->toBeTrue()
        ->and($analysis[$validated->number]['ready'])->toBeFalse()
        ->and($analysis[$validated->number]['reasons'])
            ->toContain(__('order_flow.shipment_already_validated'))
        ->and($analysis[$rider->number]['reasons'])
            ->toContain(__('order_flow.validation_carrier_required'))
        ->and($analysis[$noTracking->number]['reasons'])
            ->toContain(__('order_flow.validation_tracking_required'));

    $test->assertSee($readyA->number)
        ->assertSee(__('order_flow.validation_carrier_required'));
});

test('confirmBulkValidate hands eligible shipments over and persists validation', function () {
    [$user, $store] = bdvUser(StoreRoleEnum::OWNER->value);
    $provider = bdvProvider($store);

    $readyA = bdvOrder($store, $provider, 'NOEST-A');
    $readyB = bdvOrder($store, $provider, 'NOEST-B');
    $rider = bdvOrder($store, null);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake(['noest.test/*' => Http::response(['success' => true])]);

    Volt::test('merchant.orders.index')
        ->set('selectedOrders', [$readyA->id, $readyB->id, $rider->id])
        ->call('openBulkValidateModal')
        ->call('confirmBulkValidate')
        ->assertDispatched('swal:toast', fn ($name, $params) => bdvToastTitle($params) === __('order_flow.bulk_validate_done', ['done' => 2]))
        ->assertSet('selectedOrders', [])
        ->assertSet('showBulkValidateModal', false);

    $service = app(OrderTrackingService::class);

    expect($service->currentTracking($readyA->fresh())->isCarrierValidated())->toBeTrue()
        ->and($service->currentTracking($readyB->fresh())->isCarrierValidated())->toBeTrue();
});

test('confirmBulkValidate persists errors for rejected shipments', function () {
    [$user, $store] = bdvUser(StoreRoleEnum::OWNER->value);
    $provider = bdvProvider($store);

    $ok = bdvOrder($store, $provider, 'NOEST-OK');
    $bad = bdvOrder($store, $provider, 'NOEST-BAD');

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Http::fake([
        'noest.test/*' => Http::sequence()
            ->push(['success' => true])
            ->push(['success' => false, 'message' => 'Stock insuffisant']),
    ]);

    Volt::test('merchant.orders.index')
        ->set('selectedOrders', [$ok->id, $bad->id])
        ->call('openBulkValidateModal')
        ->call('confirmBulkValidate')
        ->assertDispatched('swal:toast', fn ($name, $params) => bdvToastTitle($params) === __('order_flow.bulk_validate_failed', ['done' => 1, 'failed' => 1]));

    $service = app(OrderTrackingService::class);

    expect($service->currentTracking($ok->fresh())->isCarrierValidated())->toBeTrue()
        ->and($service->currentTracking($bad->fresh())->isCarrierValidated())->toBeFalse()
        ->and($service->currentTracking($bad->fresh())->carrier_validation_error)->toBe('Stock insuffisant');
});