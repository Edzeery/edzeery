<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreRoles;
use Carbon\CarbonImmutable;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| PHASE 37-N7 (D4) - one-shot delivery-rate exclusions
|--------------------------------------------------------------------------
|
| The delivery rate answers a different question than the confirmation rate,
| so the store keeps an independent set of status keys out of its sums. Like
| the confirmation pair, the choice is frozen at the model and read back by
| the dashboard, so the rate card text can never drift from what was chosen.
|
*/

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function dexOwnerStore(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Delivery Exclusions Store',
        'slug' => 'dex-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store];
}

function dexStatusId(string $key): string
{
    return DB::table('statuses')->where('type', 'order')->where('key', $key)->value('id');
}

function dexOrder(Store $store, string $statusKey, string $number): Order
{
    $order = Order::create([
        'store_id' => $store->id,
        'number' => $number,
        'total_amount' => 500,
        'status_id' => dsqStatusId($statusKey),
        'delivery_type' => 'home',
    ]);

    // PHASE 37-N9: the delivery cohort is orders carrying a tracking row.
    OrderTracking::create(['store_id' => $store->id, 'order_id' => $order->id]);

    return $order;
}

function dexDeliverySettings(Store $store, array $keys, bool $locked = true)
{
    return $store->settings()->updateOrCreate([], [
        'delivery_excluded_status_keys' => $keys,
        'delivery_exclusions_locked_at' => $locked ? now() : null,
    ]);
}

test('the delivery exclusion columns allow their first save and then refuse to move', function () {
    [$user, $store] = dexOwnerStore();
    $settings = $store->settings()->firstOrFail();

    expect($settings->deliveryExclusionsLocked())->toBeFalse();

    $settings->update(['delivery_excluded_status_keys' => [], 'delivery_exclusions_locked_at' => now()]);

    $locked = $settings->fresh();

    expect($locked->deliveryExclusionsLocked())->toBeTrue()
        ->and(fn () => $locked->update(['delivery_excluded_status_keys' => ['undeliverable']]))
        ->toThrow(RuntimeException::class);

    $store->settings()->first()->update(['currency' => 'USD']);

    expect($store->settings()->first()->currency)->toBe('USD');
});

test('an unlocked delivery setting keeps its choice open until the merchant saves', function () {
    [$user, $store] = dexOwnerStore();

    dexDeliverySettings($store, ['undeliverable'], locked: false);

    $settings = $store->settings()->firstOrFail();

    expect($settings->excludedDeliveryStatusKeys())->toBe(['undeliverable'])
        ->and($settings->deliveryExclusionsLocked())->toBeFalse();
});

test('the store-settings tab saves the delivery choice once and refuses the second attempt', function () {
    [$user, $store] = dexOwnerStore();

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    $volt = Volt::test('merchant.store-settings')
        ->assertSet('delivery_exclusions_locked', false)
        ->set('delivery_excluded_status_keys', ['undeliverable', 'wrong_number'])
        ->call('saveDeliveryExclusions')
        ->assertSet('delivery_exclusions_locked', true);

    $saved = $store->settings()->firstOrFail();

    expect($volt)->not->toBeNull()
        ->and($saved->excludedDeliveryStatusKeys())->toBe(['undeliverable', 'wrong_number'])
        ->and($saved->deliveryExclusionsLocked())->toBeTrue();

    $volt->set('delivery_excluded_status_keys', ['canceled'])->call('saveDeliveryExclusions');

    expect($store->settings()->first()->excludedDeliveryStatusKeys())->toBe(['undeliverable', 'wrong_number']);
});

test('the delivery view reads the store exclusions on the gross basis', function () {
    [$user, $store] = dexOwnerStore();

    dexOrder($store, 'delivered', 'DEX-G-1');
    dexOrder($store, 'delivered', 'DEX-G-2');
    dexOrder($store, 'canceled', 'DEX-G-3');
    dexOrder($store, 'undeliverable', 'DEX-G-4');

    // Keep the failed-delivery status out of the rate: the gross denominator
    // drops from 4 to 3, so 2 delivered read as 67% not 50%.
    dexDeliverySettings($store, ['undeliverable']);

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery']))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain(__('dashboard.delivery_rate_gross'))
        ->toContain('67%')
        ->toContain(__('dashboard.delivery_rate_gross_basis', ['delivered' => 2, 'denominator' => 3]))
        ->toContain(__('dashboard.excluded_from_rate', ['count' => 1]));
});

test('the net delivery basis divides by the settled outcomes only', function () {
    [$user, $store] = dexOwnerStore();

    dexOrder($store, 'delivered', 'DEX-N-1');
    dexOrder($store, 'delivered', 'DEX-N-2');
    dexOrder($store, 'returned', 'DEX-N-3');
    dexOrder($store, 'canceled', 'DEX-N-4');

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'md' => 'delivery', 'db' => 'last_status_changed']))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain(__('dashboard.delivery_rate_net'))
        ->toContain(__('dashboard.delivery_rate_net_basis', ['delivered' => 2, 'denominator' => 3]))
        ->toContain('67%')
        ->toContain('33%');
});
