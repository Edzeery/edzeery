<?php

use App\Filament\SuperAdmin\Resources\Stores\Pages\CreateStore;
use App\Models\Stores\Store;
use App\Support\StoreSlugRules;
use Filament\Facades\Filament;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function guardStoreUser(): \App\Models\User
{
    return \App\Models\User::factory()->create();
}

test('creating a store with each reserved slug is rejected', function (string $slug) {
    Store::query()->create([
        'user_id' => guardStoreUser()->id,
        'name' => 'Reserved',
        'slug' => $slug,
    ]);
})->with(StoreSlugRules::reservedSlugs())->throws(\Illuminate\Validation\ValidationException::class);

test('updating a store onto each reserved slug is rejected', function (string $slug) {
    $store = Store::query()->create([
        'user_id' => guardStoreUser()->id,
        'name' => 'Store',
        'slug' => 'legit-shop',
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    $store->update(['slug' => $slug]);
})->with(StoreSlugRules::reservedSlugs())->throws(\Illuminate\Validation\ValidationException::class);

test('an existing store with an unchanged reserved slug keeps saving', function () {
    $store = Store::withReservedSlug(fn () => Store::query()->create([
        'user_id' => guardStoreUser()->id,
        'name' => 'Demo store',
        'slug' => 'demo',
        'status' => 'active',
        'landing_template' => 'catalog',
    ]));

    expect(fn () => $store->update(['name' => 'Renamed demo store']))->not->toThrow(\Throwable::class);

    expect($store->fresh()->name)->toBe('Renamed demo store');
    expect($store->fresh()->slug)->toBe('demo');
});

test('a normal slug is accepted on create and update', function () {
    $store = Store::query()->create([
        'user_id' => guardStoreUser()->id,
        'name' => 'Store',
        'slug' => 'legit-shop',
        'status' => 'active',
        'landing_template' => 'catalog',
    ]);

    $store->update(['slug' => 'legit-shop-v2']);

    expect($store->fresh()->slug)->toBe('legit-shop-v2');
});

test('case and whitespace variants of a reserved slug are rejected', function (string $variant) {
    Store::query()->create([
        'user_id' => guardStoreUser()->id,
        'name' => 'Evasive',
        'slug' => $variant,
    ]);
})->with(['WWW', ' Www ', 'www ', 'API', 'Demo', " demo\t"])->throws(\Illuminate\Validation\ValidationException::class);

test('the DemoStoreSeeder exemption creates the platform demo store at the reserved slug', function () {
    $store = Store::withReservedSlug(fn () => Store::firstOrCreate(
        ['slug' => 'demo'],
        [
            'user_id' => guardStoreUser()->id,
            'name' => 'Edzeery Demo Store',
            'landing_template' => 'catalog',
            'status' => 'active',
        ]
    ));

    expect($store->exists)->toBeTrue();
    expect($store->slug)->toBe('demo');

    $again = Store::withReservedSlug(fn () => Store::firstOrCreate(['slug' => 'demo'], ['name' => 'Edzeery Demo Store']));

    expect($again->id)->toBe($store->id);
});

test('a normal creation with the reserved demo slug still throws outside the exemption', function () {
    Store::query()->create([
        'user_id' => guardStoreUser()->id,
        'name' => 'Fake Demo',
        'slug' => 'demo',
    ]);
})->throws(\Illuminate\Validation\ValidationException::class);

test('the Filament SuperAdmin store form still rejects a reserved slug', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    Filament::setCurrentPanel(Filament::getPanel('super-admin'));

    Livewire::actingAs(roleUser('super_admin'))
        ->test(CreateStore::class)
        ->fillForm([
            'name' => 'Reserved',
            'slug' => 'www',
            'currency' => 'DZD',
            'language' => 'ar',
        ])
        ->call('createStore')
        ->assertHasFormErrors(['slug']);
});