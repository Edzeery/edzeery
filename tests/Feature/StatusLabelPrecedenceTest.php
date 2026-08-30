<?php

use App\Domains\Status\StatusResolver;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\User;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function slpStore(): Store
{
    $user = User::factory()->create();

    return Store::create([
        'user_id' => $user->id,
        'name' => 'Precedence Store',
        'slug' => 'pre-'.uniqid(),
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);
}

it('prefers the kit translation for seeded system rows', function () {
    $this->seed(SystemStatusesSeeder::class);

    app()->setLocale('ar');
    StatusResolver::flush();

    $resolved = StatusResolver::resolve('order', 'confirmed');

    expect($resolved->source)->toBe('db')
        ->and($resolved->label)->toBe('مؤكد');

    StatusResolver::flush();
});

it('falls back to the stored label when no translation exists', function () {
    Status::create([
        'type' => 'garage',
        'key' => 'open',
        'label' => 'GARAGE OPEN',
        'is_system' => true,
    ]);

    StatusResolver::flush();

    expect(StatusResolver::resolve('garage', 'open')->label)->toBe('GARAGE OPEN');

    StatusResolver::flush();
});

it('allows a store-scoped row to override the kit translation', function () {
    $this->seed(SystemStatusesSeeder::class);
    $store = slpStore();

    Status::create([
        'store_id' => $store->id,
        'type' => 'order',
        'key' => 'confirmed',
        'label' => 'Custom Confirm',
        'color' => 'blue',
        'is_system' => false,
    ]);

    app()->setLocale('ar');
    StatusResolver::flush();

    expect(StatusResolver::resolve('order', 'confirmed', $store->id)->label)->toBe('Custom Confirm')
        ->and(StatusResolver::resolve('order', 'confirmed')->label)->toBe('مؤكد');

    StatusResolver::flush();
});

it('seeds tracking, inventory and inventory-movement defaults', function () {
    $this->seed(SystemStatusesSeeder::class);

    expect(Status::where('type', 'tracking')->count())->toBe(9)
        ->and(Status::where('type', 'inventory')->count())->toBe(3)
        ->and(Status::where('type', 'inventorymovementtype')->count())->toBe(8)
        ->and(Status::where('type', 'inventorymovementtype')->where('key', 'loss')->where('color', 'danger')->exists())->toBeTrue()
        ->and(Status::where('type', 'inventorymovementtype')->where('key', 'damage')->where('color', 'warning')->exists())->toBeTrue()
        ->and(Status::where('type', 'order')->where('key', 'unclaimed')->exists())->toBeTrue()
        ->and(Status::where('type', 'order')->where('key', 'undeliverable')->exists())->toBeTrue();
});
