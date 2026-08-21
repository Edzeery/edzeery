<?php

use App\Models\billing\Subscription;
use App\Models\Billing\BillingAddress;
use App\Models\Plans\Plan;
use App\Models\User;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(PlansSeeder::class);
});

test('billing page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/merchant/account/billing');

    $response->assertOk();
});

test('guest cannot access billing page', function () {
    $response = $this->get('/merchant/account/billing');

    $response->assertRedirect();
});

test('billing address can be saved', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('account.billing')
        ->call('openEditBilling')
        ->set('billing_name', 'Test Company')
        ->set('billing_country', 'DZ')
        ->set('billing_state', 'Alger')
        ->set('billing_city', 'Algiers')
        ->set('billing_zip', '16000')
        ->set('billing_address_line_1', '123 Rue Didouche Mourad')
        ->call('saveBilling');

    $this->assertDatabaseHas('billing_addresses', [
        'user_id' => $user->id,
        'name' => 'Test Company',
        'country' => 'DZ',
        'state' => 'Alger',
        'city' => 'Algiers',
    ]);
});

test('billing address can be updated', function () {
    $user = User::factory()->create();

    BillingAddress::create([
        'user_id' => $user->id,
        'name' => 'Old Name',
        'country' => 'DZ',
    ]);

    Livewire::actingAs($user)
        ->test('account.billing')
        ->call('openEditBilling')
        ->set('billing_name', 'New Name')
        ->set('billing_country', 'FR')
        ->call('saveBilling');

    $this->assertDatabaseHas('billing_addresses', [
        'user_id' => $user->id,
        'name' => 'New Name',
        'country' => 'FR',
    ]);

    $this->assertDatabaseCount('billing_addresses', 1);
});

test('subscription cancel updates status', function () {
    $user = User::factory()->create();

    $plan = Plan::first();
    $planPrice = $plan->prices()->first();

    Subscription::where('user_id', $user->id)->delete();

    $sub = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'plan_price_id' => $planPrice?->id,
        'status' => 'active',
        'is_trial' => false,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($user)
        ->test('account.billing')
        ->assertSet('subscription.status.value', 'active')
        ->call('cancelSubscription')
        ->assertSet('subscription.status.value', 'canceled');
});
