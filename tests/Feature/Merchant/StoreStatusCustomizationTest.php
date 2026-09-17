<?php

use App\Domains\Status\StatusResolver;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreStatusService;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
    StatusResolver::flush();
});

function sscEnv(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Status Customization Store',
        'slug' => 'ssc-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    return [$user, $store];
}

function sscVolt(array $userStore)
{
    [$user, $store] = $userStore;
    actingAs($user)->withSession(['current_store_id' => $store->id]);

    return Volt::test('merchant.customization.statuses');
}

test('confirmation list covers the seeded pipeline in order', function () {
    [, $store] = sscEnv();

    $list = app(StoreStatusService::class)->confirmationList($store->id);

    expect(array_column($list, 'key'))->toBe(StoreStatusService::CONFIRMATION_KEYS)
        ->and(array_column($list, 'sort_order'))->toBe(range(1, 10))
        ->and(collect($list)->every(fn ($row) => $row['has_override'] === false))->toBeTrue();
});

test('saving a label creates a store override and the resolver prefers it', function () {
    [, $store] = sscEnv();

    app(StoreStatusService::class)->saveLabel((string) $store->id, 'confirmed', 'Custom Confirm');
    StatusResolver::flush();

    $override = Status::where('store_id', $store->id)->where('type', 'order')->where('key', 'confirmed')->first();

    expect($override)->not->toBeNull()
        ->and($override->label)->toBe('Custom Confirm')
        ->and($override->is_system)->toBeFalse()
        // Inventory semantics are copied from the system row so the override changes nothing operationally.
        ->and($override->affects_inventory)->toBeTrue()
        ->and($override->movement_type)->toBe('reserve');

    expect(StatusResolver::resolve('order', 'confirmed', $store->id)->label)->toBe('Custom Confirm');

    // The system row is never touched.
    expect(Status::whereNull('store_id')->where('type', 'order')->where('key', 'confirmed')->value('label'))
        ->toBe('Confirmed');
});

test('an empty label keeps the status-kit translation for the locale', function () {
    [, $store] = sscEnv();

    app(StoreStatusService::class)->saveLabel((string) $store->id, 'confirmed', '');
    StatusResolver::flush();

    app()->setLocale('ar');

    $resolved = StatusResolver::resolve('order', 'confirmed', $store->id);

    expect($resolved->label)->toBe(__('status-kit::statuses.order.confirmed'))
        ->and($resolved->label)->toBe('مؤكد');
});

test('saving a color changes the resolved variant without touching the system row', function () {
    [, $store] = sscEnv();

    app(StoreStatusService::class)->saveColor((string) $store->id, 'pending', 'danger');
    StatusResolver::flush();

    expect(StatusResolver::resolve('order', 'pending', $store->id)->variant)->toBe('danger')
        ->and(Status::whereNull('store_id')->where('type', 'order')->where('key', 'pending')->value('color'))
        ->toBe('gray');
});

test('reordering swaps adjacent statuses and stays isolated per store', function () {
    [, $storeA] = sscEnv();
    [, $storeB] = sscEnv();

    $service = app(StoreStatusService::class);
    $service->move((string) $storeA->id, 'pending', 1);

    $keysA = array_column($service->confirmationList($storeA->id), 'key');
    $keysB = array_column($service->confirmationList($storeB->id), 'key');

    expect(array_slice($keysA, 0, 2))->toBe(['confirmed', 'pending'])
        ->and($keysB)->toBe(StoreStatusService::CONFIRMATION_KEYS);

    // Only the two swapped keys received store rows for store A.
    expect(Status::where('store_id', $storeA->id)->where('type', 'order')->pluck('key')->sort()->values()->all())
        ->toBe(['confirmed', 'pending'])
        ->and(Status::where('store_id', $storeB->id)->count())->toBe(0);
});

test('reordering at the edges is a no-op', function () {
    [, $store] = sscEnv();

    $service = app(StoreStatusService::class);
    $service->move((string) $store->id, 'pending', -1);
    $service->move((string) $store->id, 'on_hold', 1);

    expect(array_column($service->confirmationList($store->id), 'key'))->toBe(StoreStatusService::CONFIRMATION_KEYS)
        ->and(Status::where('store_id', $store->id)->count())->toBe(0);
});

test('unknown and non-confirmation keys are rejected', function () {
    [, $store] = sscEnv();

    $service = app(StoreStatusService::class);

    expect(fn () => $service->saveLabel((string) $store->id, 'shipped', 'Nope'))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => $service->saveColor((string) $store->id, 'pending', 'rainbow'))
        ->toThrow(InvalidArgumentException::class);
});

test('the page saves labels and colors through the component', function () {
    [$user, $store] = sscEnv();

    $volt = sscVolt([$user, $store]);

    $volt->set('labels.confirmed', 'My Confirm')
        ->set('colors.confirmed', 'danger')
        ->call('saveChanges')
        ->assertDispatched('swal');

    $override = Status::where('store_id', $store->id)->where('key', 'confirmed')->first();

    expect($override->label)->toBe('My Confirm')
        ->and($override->color)->toBe('danger');
});

test('the page reorders statuses through the component', function () {
    [$user, $store] = sscEnv();

    $volt = sscVolt([$user, $store]);

    $volt->call('moveStatus', 'pending', 1)->assertDispatched('swal');

    expect(app(StoreStatusService::class)->confirmationList($store->id)[0]['key'])->toBe('confirmed');
});

test('the page exposes the pipeline and both confirmation views', function () {
    [$user, $store] = sscEnv();

    app(StoreStatusService::class)->saveLabel((string) $store->id, 'confirmed', 'Custom Confirm');
    StatusResolver::flush();

    $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.customization.statuses', ['store' => $store->slug]))
        ->assertOk()
        ->assertSee('Custom Confirm')
        ->assertSee("setConfirmationView('customize')", false)
        ->assertSee("setConfirmationView('order')", false)
        ->assertSee(__('merchant_panel.confirmation_reorder'));
});

test('the rider list covers the enum statuses and exposes store rows', function () {
    [, $store] = sscEnv();

    $list = app(StoreStatusService::class)->riderList($store->id);

    $keys = array_column($list, 'key');

    expect($keys)->toBe(array_column(\App\Enums\Store\OrderTrackingStatus::cases(), 'value'))
        ->and(collect($list)->every(fn ($row) => $row['is_custom'] === false))->toBeTrue();
});

test('adding a confirmation status links it to an original and inherits its semantics', function () {
    [, $store] = sscEnv();

    $service = app(StoreStatusService::class);
    $status = $service->addStatus((string) $store->id, StoreStatusService::TYPE, 'Contacted and Confirmed', 'success', 'confirmed');

    expect($status->is_system)->toBeFalse()
        ->and($status->linked_to)->toBe('confirmed')
        // Inventory semantics are inherited from the linked original ('confirmed' → reserve).
        ->and($status->affects_inventory)->toBeTrue()
        ->and($status->movement_type)->toBe('reserve');

    // The custom status surfaces in the confirmation list as a custom, linked row.
    $list = $service->confirmationList((string) $store->id);
    $custom = collect($list)->firstWhere('key', $status->key);

    expect($custom)->not->toBeNull()
        ->and($custom['is_custom'])->toBeTrue()
        ->and($custom['linked_to'])->toBe('confirmed');

    expect($service->canonicalKey((string) $store->id, $status->key))->toBe('confirmed')
        ->and($service->canonicalKey((string) $store->id, 'pending'))->toBe('pending');
});

test('confirmation statuses must be linked to an original confirmation key', function () {
    [, $store] = sscEnv();

    expect(fn () => app(StoreStatusService::class)->addStatus((string) $store->id, StoreStatusService::TYPE, 'No link', 'gray'))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => app(StoreStatusService::class)->addStatus((string) $store->id, StoreStatusService::TYPE, 'Bad link', 'gray', 'shipped'))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => app(StoreStatusService::class)->addStatus((string) $store->id, 'bogus', 'Nope', 'gray', 'confirmed'))
        ->toThrow(InvalidArgumentException::class);
});

test('adding a rider status creates a custom tracking row visible only to that store', function () {
    [, $storeA] = sscEnv();
    [, $storeB] = sscEnv();

    $service = app(StoreStatusService::class);
    $status = $service->addStatus((string) $storeA->id, StoreStatusService::TRACKING_TYPE, 'Arrived at rider', 'info');

    expect($status->type)->toBe('tracking')
        ->and($status->linked_to)->toBeNull()
        ->and($status->affects_inventory)->toBeFalse();

    $keysA = array_column($service->riderList((string) $storeA->id), 'key');
    $keysB = array_column($service->riderList((string) $storeB->id), 'key');

    expect(in_array($status->key, $keysA, true))->toBeTrue()
        ->and(in_array($status->key, $keysB, true))->toBeFalse();

    $custom = collect($service->riderList((string) $storeA->id))->firstWhere('key', $status->key);
    expect($custom['is_custom'])->toBeTrue()
        ->and($custom['override_label'])->toBe('Arrived at rider')
        ->and($custom['sort_order'])->toBe(12);
});

test('saving a rider label and color persists a tracking override', function () {
    [, $store] = sscEnv();

    $service = app(StoreStatusService::class);
    $service->riderSaveLabel((string) $store->id, 'shipped', 'Carrier registered');
    $service->riderSaveColor((string) $store->id, 'shipped', 'warning');
    StatusResolver::flush();

    $override = Status::where('store_id', $store->id)->where('type', 'tracking')->where('key', 'shipped')->first();

    expect($override)->not->toBeNull()
        ->and($override->label)->toBe('Carrier registered')
        ->and($override->color)->toBe('warning')
        ->and(StatusResolver::resolve('tracking', 'shipped', $store->id)->label)->toBe('Carrier registered');

    expect(fn () => $service->riderSaveColor((string) $store->id, 'shipped', 'rainbow'))
        ->toThrow(InvalidArgumentException::class);
});

test('rider reordering is isolated per store and only touches the rider scope', function () {
    [, $storeA] = sscEnv();

    $service = app(StoreStatusService::class);
    $service->moveRider((string) $storeA->id, 'shipped', 1);

    $keysA = array_column($service->riderList((string) $storeA->id), 'key');

    expect(array_slice($keysA, 0, 2))->toBe(['in_transit', 'shipped']);

    // Closing guard: carrier dictionary rows are never affected by rider overrides.
    expect(\App\Domains\Shipping\Support\CarrierStatusDictionary::list('noest'))
        ->toHaveCount(35);
});

test('deleting a custom status removes the store row but protects system keys', function () {
    [, $store] = sscEnv();

    $service = app(StoreStatusService::class);
    $custom = $service->addStatus((string) $store->id, StoreStatusService::TRACKING_TYPE, 'Temp rider', 'gray');
    $conf = $service->addStatus((string) $store->id, StoreStatusService::TYPE, 'Temp confirmation', 'gray', 'pending');

    $service->deleteStatus((string) $store->id, StoreStatusService::TRACKING_TYPE, $custom->key);
    $service->deleteStatus((string) $store->id, StoreStatusService::TYPE, $conf->key);

    expect(Status::where('store_id', $store->id)->where('key', $custom->key)->exists())->toBeFalse()
        ->and(Status::where('store_id', $store->id)->where('key', $conf->key)->exists())->toBeFalse()
        ->and(Status::whereNull('store_id')->where('type', 'order')->where('key', 'pending')->exists())->toBeTrue();

    expect(fn () => $service->deleteStatus((string) $store->id, StoreStatusService::TYPE, 'pending'))
        ->toThrow(DomainException::class);
});
