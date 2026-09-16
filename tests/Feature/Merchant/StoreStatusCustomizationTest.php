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
