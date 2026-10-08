<?php

use App\Domains\Order\Models\ConfirmationShift;
use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    config(['app.timezone' => 'Africa/Algiers']);
    Carbon::setTestNow(Carbon::create(2026, 5, 4, 10, 0, 0, 'Africa/Algiers'));
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    test()->artisan('db:seed', ['--class' => Database\Seeders\SystemStatusesSeeder::class, '--force' => true]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function shiftRoleSettingsStore(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Shift Role Settings Store',
        'slug' => 'shift-role-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => 'owner',
    ]);

    // Only order.manage: the acting owner must not leak into the agent
    // pickers or the setup-gap lists the assertions below inspect.
    $membership->syncPermissions([StorePermissionEnum::ORDER_MANAGE->value]);

    return [$user, $store];
}

function shiftRoleMember(Store $store, string $name, array $permissions): StoreMembership
{
    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => User::factory()->create(['name' => $name])->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => 'staff',
    ]);

    $membership->syncPermissions($permissions);

    return $membership;
}

function shiftRoleOrder(Store $store): Order
{
    $status = \App\Models\Status::system()->forType('order')->where('key', 'pending')->first();

    return Order::create([
        'store_id' => $store->id,
        'status_id' => $status?->id,
        'number' => (new Order(['store_id' => $store->id]))->nextOrderNumber(),
        'total_amount' => 500,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
    ]);
}

function shiftRoleTracking(Store $store, Order $order): OrderTracking
{
    return OrderTracking::create([
        'store_id' => $store->id,
        'order_id' => $order->id,
        'tracking_status' => \App\Enums\Store\OrderTrackingStatus::SHIPPED->value,
        'shipped_at' => now(),
    ]);
}

test('saving a tracking shift from the ui persists its role scope and feeds the tracking engine', function () {
    [$user, $store] = shiftRoleSettingsStore();
    $tracker = shiftRoleMember($store, 'Tracking Agent', [StorePermissionEnum::CRM_ORDER_TRACKING->value]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-settings')
        ->call('openShiftModal')
        ->assertSet('shiftForm.role_scope', 'confirm')
        ->call('setShiftRole', 'track')
        ->assertSet('shiftForm.role_scope', 'track')
        ->set('shiftForm.membership_id', $tracker->id)
        ->call('saveShift')
        ->assertHasNoErrors();

    $shift = ConfirmationShift::sole();

    expect($shift->role_scope)->toBe('track')
        ->and($shift->membership_id)->toBe($tracker->id)
        ->and($shift->is_active)->toBeTrue();

    // End to end: the UI-created track shift is what the engine sees.
    $tracking = shiftRoleTracking($store, shiftRoleOrder($store));
    app(OrderTrackingAssignmentService::class)->assign($tracking);

    expect($tracking->fresh()->assigned_to_membership_id)->toBe($tracker->id)
        ->and($tracking->fresh()->assignment_method)->toBe('auto');
});

test('an agent without the selected role permission is cleared from the picker and rejected on save', function () {
    [$user, $store] = shiftRoleSettingsStore();
    $confirmer = shiftRoleMember($store, 'Confirmer Solo', [StorePermissionEnum::ORDER_CONFIRM->value]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-settings')
        ->call('openShiftModal')
        ->set('shiftForm.membership_id', $confirmer->id)
        ->call('setShiftRole', 'track')
        ->assertSet('shiftForm.role_scope', 'track')
        ->assertSet('shiftForm.membership_id', '')
        ->set('shiftForm.membership_id', $confirmer->id)
        ->call('saveShift')
        ->assertHasErrors('shiftForm.membership_id');

    expect(ConfirmationShift::count())->toBe(0);
});

test('an agent belonging to another store cannot be assigned a shift here', function () {
    [$user, $store] = shiftRoleSettingsStore();
    $foreignStore = Store::create([
        'user_id' => $store->user_id,
        'name' => 'Foreign Store',
        'slug' => 'foreign-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);
    $foreign = shiftRoleMember($foreignStore, 'Foreign Agent', [StorePermissionEnum::ORDER_CONFIRM->value]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-settings')
        ->call('openShiftModal')
        ->set('shiftForm.membership_id', $foreign->id)
        ->call('saveShift')
        ->assertHasErrors('shiftForm.membership_id');

    expect(ConfirmationShift::where('store_id', $store->id)->count())->toBe(0);
});

test('identical hours are allowed across roles but overlapping hours are rejected within a role', function () {
    [$user, $store] = shiftRoleSettingsStore();
    $dual = shiftRoleMember($store, 'Dual Role Agent', [
        StorePermissionEnum::ORDER_CONFIRM->value,
        StorePermissionEnum::CRM_ORDER_TRACKING->value,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-settings')
        ->call('openShiftModal')
        ->set('shiftForm.membership_id', $dual->id)
        ->call('saveShift')
        ->assertHasNoErrors()
        ->call('openShiftModal')
        ->call('setShiftRole', 'track')
        ->set('shiftForm.membership_id', $dual->id)
        ->call('saveShift')
        ->assertHasNoErrors()
        ->call('openShiftModal')
        ->set('shiftForm.membership_id', $dual->id)
        ->set('shiftForm.start_time', '10:00')
        ->call('saveShift')
        ->assertHasErrors('shiftForm.start_time');

    expect(ConfirmationShift::pluck('role_scope')->sort()->values()->all())->toBe(['confirm', 'track']);
});

test('the shift modal picker lists only the agents holding the selected role permission', function () {
    [$user, $store] = shiftRoleSettingsStore();
    $confirmer = shiftRoleMember($store, 'Picker Confirmer', [StorePermissionEnum::ORDER_CONFIRM->value]);
    $tracker = shiftRoleMember($store, 'Picker Tracker', [StorePermissionEnum::CRM_ORDER_TRACKING->value]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    // The agent picker's options reach the DOM as escaped JSON in the
    // select's data-options attribute — match that exact shape so the plain
    // member names the setup-gap notice prints never pollute the assertion.
    $option = fn (string $name) => '&amp;quot;label&amp;quot;:&amp;quot;'.$name.'&amp;quot;';

    Volt::test('merchant.order-settings')
        ->call('openShiftModal')
        ->assertSee($option('Picker Confirmer'), false)
        ->assertDontSee($option('Picker Tracker'), false)
        ->call('setShiftRole', 'track')
        ->assertSee($option('Picker Tracker'), false)
        ->assertDontSee($option('Picker Confirmer'), false);
});

test('the setup gap notice flags the role whose eligible agents have no active shift', function () {
    [$user, $store] = shiftRoleSettingsStore();
    $confirmer = shiftRoleMember($store, 'Gap Confirmer', [StorePermissionEnum::ORDER_CONFIRM->value]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-settings')
        ->assertSee(__('merchant_panel.setup_gap_title'))
        ->assertSee(__('merchant_panel.setup_gap_missing', ['names' => $confirmer->user->name]));
});

test('the shifts tab filters its rows by role', function () {
    [$user, $store] = shiftRoleSettingsStore();
    $confirmer = shiftRoleMember($store, 'Table Confirmer', [StorePermissionEnum::ORDER_CONFIRM->value]);
    $tracker = shiftRoleMember($store, 'Table Tracker', [StorePermissionEnum::CRM_ORDER_TRACKING->value]);

    foreach ([
        ['membership_id' => $confirmer->id, 'role_scope' => 'confirm', 'start_time' => '08:00', 'end_time' => '12:00'],
        ['membership_id' => $tracker->id, 'role_scope' => 'track', 'start_time' => '13:00', 'end_time' => '17:00'],
    ] as $shift) {
        ConfirmationShift::create(array_merge([
            'store_id' => $store->id,
            'shift_type' => 'custom',
            'days_of_week' => [1, 2, 3, 4, 5],
            'is_active' => true,
        ], $shift));
    }

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-settings')
        ->assertSee('Table Confirmer')
        ->assertSee('Table Tracker')
        ->call('setShiftRoleFilter', 'track')
        ->assertSet('shiftRoleFilter', 'track')
        ->assertSee('Table Tracker')
        ->assertDontSee('Table Confirmer')
        ->call('setShiftRoleFilter', 'confirm')
        ->assertSet('shiftRoleFilter', 'confirm')
        ->assertSee('Table Confirmer')
        ->assertDontSee('Table Tracker');
});

test('typing an agent name filters the shifts rows', function () {
    [$user, $store] = shiftRoleSettingsStore();
    $confirmer = shiftRoleMember($store, 'Searchable Zoe', [StorePermissionEnum::ORDER_CONFIRM->value]);
    $tracker = shiftRoleMember($store, 'Searchable Omar', [StorePermissionEnum::CRM_ORDER_TRACKING->value]);

    foreach ([
        ['membership_id' => $confirmer->id, 'role_scope' => 'confirm', 'start_time' => '08:00', 'end_time' => '12:00'],
        ['membership_id' => $tracker->id, 'role_scope' => 'track', 'start_time' => '13:00', 'end_time' => '17:00'],
    ] as $shift) {
        ConfirmationShift::create(array_merge([
            'store_id' => $store->id,
            'shift_type' => 'custom',
            'days_of_week' => [1, 2, 3, 4, 5],
            'is_active' => true,
        ], $shift));
    }

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-settings')
        ->assertSee('Searchable Zoe')
        ->assertSee('Searchable Omar')
        ->set('shiftSearch', 'omar')
        ->assertSet('shiftSearch', 'omar')
        ->assertSee('Searchable Omar')
        ->assertDontSee('Searchable Zoe')
        ->set('shiftSearch', 'zoe')
        ->assertSee('Searchable Zoe')
        ->assertDontSee('Searchable Omar')
        ->set('shiftSearch', 'nobody')
        ->assertSee(__('merchant_panel.no_search_results'));
});
