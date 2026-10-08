<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function assignSearchMember(Store $store, string $name): StoreMembership
{
    return StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => User::factory()->create(['name' => $name])->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => 'staff',
    ]);
}

function assignSearchProduct(Store $store, string $name): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.uniqid(),
        'sku' => 'AS-'.strtoupper(Str::random(6)),
        'type' => 'simple',
        'price' => 100,
        'is_active' => true,
    ]);
}

test('the assignments tab search filters groups by agent or product name', function () {
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate('owner', 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Assignments Search Store',
        'slug' => 'assign-search-'.uniqid(),
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
    $membership->syncPermissions([StorePermissionEnum::ORDER_MANAGE->value]);

    $zoe = assignSearchMember($store, 'Zoe Picker');
    $omar = assignSearchMember($store, 'Omar Picker');

    $sneaker = assignSearchProduct($store, 'Sneaker Flex');
    $jacket = assignSearchProduct($store, 'Denim Jacket');

    ConfirmationProductAssignment::create([
        'store_id' => $store->id,
        'membership_id' => $zoe->id,
        'product_id' => $sneaker->id,
    ]);
    ConfirmationProductAssignment::create([
        'store_id' => $store->id,
        'membership_id' => $omar->id,
        'product_id' => $jacket->id,
    ]);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Volt::test('merchant.order-settings')
        ->call('setTab', 'products')
        ->assertSee('Zoe Picker')
        ->assertSee('Omar Picker')
        ->assertSee('Sneaker Flex')
        ->assertSee('Denim Jacket')
        ->set('assignSearch', 'omar')
        ->assertSee('Omar Picker')
        ->assertDontSee('Zoe Picker')
        ->set('assignSearch', 'sneaker')
        ->assertSee('Zoe Picker')
        ->assertDontSee('Omar Picker')
        ->assertSee('Sneaker Flex')
        ->assertDontSee('Denim Jacket')
        ->set('assignSearch', 'missing')
        ->assertSee(__('merchant_panel.no_search_results'));
});
