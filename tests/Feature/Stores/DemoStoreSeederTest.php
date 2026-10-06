<?php

use App\Models\Stores\Store;
use Database\Seeders\DemoStoreSeeder;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| PHASE 37-K.3 - demo store seeding keeps working under the reserved-slug
|--------------------------------------------------------------------------
|
| The model-level reserved-slug guard must not break the platform's own demo
| store, which legitimately lives at the reserved "demo" slug via the single
| greppable Store::withReservedSlug() exemption. These tests run the real
| DemoStoreSeeder against a fresh database (the exact production path).
|
*/

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

test('a fresh database can seed the platform demo store at the reserved demo slug', function () {
    $this->seed(DemoStoreSeeder::class);

    expect(Store::where('slug', 'demo')->count())->toBe(1);
    expect(Store::where('slug', 'demo')->value('name'))->toBe('Edzeery Demo Store');
});

test('re-running the demo store seeder is idempotent and keeps the reserved demo slug', function () {
    $this->seed(DemoStoreSeeder::class);
    $this->seed(DemoStoreSeeder::class);

    expect(Store::where('slug', 'demo')->count())->toBe(1);
    expect(Store::where('slug', 'demo')->exists())->toBeTrue();
});