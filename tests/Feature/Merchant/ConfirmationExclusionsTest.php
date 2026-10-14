<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
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
| PHASE 37-N2 - one-shot rate exclusions
|--------------------------------------------------------------------------
|
| The store picks the statuses it keeps out of the confirmation rate once. The
| choice is frozen at the model, mirrored by the store-settings tab and read
| back by the dashboard, so the headline basis text can never drift from what
| the store chose.
|
*/

beforeEach(function () {
    // PHASE 37-N6: pin the clock mid-day (store timezone resolves to
    // Africa/Algiers) so the default "today" window is deterministic.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-10 12:00:00', 'UTC'));

    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function cexOwnerStore(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Exclusion Store',
        'slug' => 'exclusion-'.uniqid(),
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

function cexStatusId(string $key): string
{
    return DB::table('statuses')->where('type', 'order')->where('key', $key)->value('id');
}

function cexOrder(Store $store, string $statusKey, string $number): Order
{
    return Order::create([
        'store_id' => $store->id,
        'number' => $number,
        'total_amount' => 500,
        'status_id' => cexStatusId($statusKey),
        'delivery_type' => 'home',
    ]);
}

function cexSettings(Store $store, array $keys, bool $locked = true)
{
    return $store->settings()->updateOrCreate([], [
        'confirmation_excluded_status_keys' => $keys,
        'confirmation_exclusions_locked_at' => $locked ? now() : null,
    ]);
}

test('the exclusion columns allow their first save and then refuse to move', function () {
    [$user, $store] = cexOwnerStore();
    $settings = $store->settings()->firstOrFail();

    expect($settings->confirmationExclusionsLocked())->toBeFalse();

    // The first save (an empty list is a valid choice) stamps the lock.
    $settings->update(['confirmation_excluded_status_keys' => [], 'confirmation_exclusions_locked_at' => now()]);

    $locked = $settings->fresh();

    expect($locked->confirmationExclusionsLocked())->toBeTrue()
        ->and(fn () => $locked->update(['confirmation_excluded_status_keys' => ['no_answer_1']]))
        ->toThrow(RuntimeException::class);

    // Unrelated settings still save normally after the choice is frozen.
    $store->settings()->first()->update(['currency' => 'USD']);

    expect($store->settings()->first()->currency)->toBe('USD');
});

test('an unlocked setting keeps its choice open until the merchant saves', function () {
    [$user, $store] = cexOwnerStore();

    cexSettings($store, ['no_answer_1'], locked: false);

    $settings = $store->settings()->firstOrFail();

    expect($settings->excludedConfirmationStatusKeys())->toBe(['no_answer_1'])
        ->and($settings->confirmationExclusionsLocked())->toBeFalse();
});

test('the store-settings tab saves the choice once and refuses the second attempt', function () {
    [$user, $store] = cexOwnerStore();

    $this->actingAs($user)->withSession(['current_store_id' => $store->id]);

    $volt = Volt::test('merchant.store-settings')
        ->assertSet('confirmation_exclusions_locked', false)
        ->set('confirmation_excluded_status_keys', ['no_answer_1', 'postponed'])
        ->call('saveConfirmationExclusions')
        ->assertSet('confirmation_exclusions_locked', true);

    $saved = $store->settings()->firstOrFail();

    expect($volt)->not->toBeNull()
        ->and($saved->excludedConfirmationStatusKeys())->toBe(['no_answer_1', 'postponed'])
        ->and($store->settings()->first()->confirmationExclusionsLocked())->toBeTrue();

    // A second attempt is refused in the action and never rewrites the choice.
    $volt->set('confirmation_excluded_status_keys', ['pending'])->call('saveConfirmationExclusions');

    expect($store->settings()->first()->excludedConfirmationStatusKeys())->toBe(['no_answer_1', 'postponed']);
});

test('the dashboard reads the store exclusions on the gross basis', function () {
    [$user, $store] = cexOwnerStore();

    cexOrder($store, 'confirmed', 'CEX-G-1');
    cexOrder($store, 'confirmed', 'CEX-G-2');
    cexOrder($store, 'canceled', 'CEX-G-3');
    cexOrder($store, 'no_answer_1', 'CEX-G-4');

    // Keep every failed status out of the rate: the gross denominator drops
    // from 4 to 3, so 2 confirmed read as 67% not 50%.
    cexSettings($store, ['no_answer_1', 'no_answer_2', 'no_answer_3']);

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'ct' => 'rate']))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain(__('dashboard.confirmation_rate_gross'))
        ->toContain('67%')
        ->toContain(__('dashboard.confirmation_rate_gross_basis', ['confirmed' => 2, 'denominator' => 3]))
        ->toContain(__('dashboard.excluded_from_rate', ['count' => 1]))
        ->toContain(__('dashboard.outside_rate'));
});

test('switching to the last-status basis reads the rate against settled orders', function () {
    [$user, $store] = cexOwnerStore();

    cexOrder($store, 'confirmed', 'CEX-N-1');
    cexOrder($store, 'confirmed', 'CEX-N-2');
    cexOrder($store, 'canceled', 'CEX-N-3');
    cexOrder($store, 'no_answer_1', 'CEX-N-4');

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'ct' => 'rate', 'db' => 'last_status_changed']))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain(__('dashboard.confirmation_rate_net'))
        ->toContain(__('dashboard.confirmation_rate_net_basis', ['confirmed' => 2, 'denominator' => 3]))
        ->toContain('67%');
});
