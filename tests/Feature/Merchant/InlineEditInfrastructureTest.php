<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Brand;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\Support\InlineEditComponent;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
});

function inlineEditStoreEmployee(string $storeRole): array
{
    $user = roleUser('merchant');

    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Inline Edit Store',
        'slug' => 'inline-edit-' . $storeRole . '-' . uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => $storeRole,
    ]);

    return [$user, $store];
}

function inlineEditBrand(Store $store, string $name = 'Original Brand'): Brand
{
    return Brand::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => \Illuminate\Support\Str::slug($name),
    ]);
}

test('owner can inline-edit a brand name, persist and record an audit log', function () {
    [$user, $store] = inlineEditStoreEmployee(StoreRoleEnum::OWNER->value);
    $brand = inlineEditBrand($store);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Livewire::test(InlineEditComponent::class, ['brandId' => $brand->id])
        ->call('startEditName', $brand->id)
        ->assertSet('editingField', 'brand.name')
        ->assertSet('editingId', $brand->id)
        ->set('editingValue', 'Renamed Brand')
        ->call('saveName')
        ->assertSet('editingField', null)
        ->assertSet('editingError', null);

    $brand->refresh();

    expect($brand->name)->toBe('Renamed Brand')
        ->and($brand->slug)->toBe('renamed-brand');

    $activity = Activity::query()->where('event', 'brand_renamed')->latest()->first();

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe(config('activitylog.default_log_name'))
        ->and($activity->description)->toBe('Applied brand name')
        ->and((string) $activity->subject_id)->toBe((string) $brand->id)
        ->and($activity->subject_type)->toBe(Brand::class)
        ->and((string) $activity->causer_id)->toBe((string) $user->id)
        ->and($activity->properties->get('field'))->toBe('brand.name')
        ->and($activity->properties->get('value'))->toBe('Renamed Brand')
        ->and($activity->properties->get('record_id'))->toBe($brand->id);
});

test('invalid value is rejected, nothing persisted and a validation_failed audit is recorded', function () {
    [$user, $store] = inlineEditStoreEmployee(StoreRoleEnum::OWNER->value);
    $brand = inlineEditBrand($store);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Livewire::test(InlineEditComponent::class, ['brandId' => $brand->id])
        ->call('startEditName', $brand->id)
        ->set('editingValue', 'ab')
        ->call('saveName')
        ->assertSet('editingField', 'brand.name')
        ->assertSet('editingError', 'The value field must contain at least 3 characters.');

    expect($brand->fresh()->name)->toBe('Original Brand')
        ->and($brand->fresh()->slug)->toBe('original-brand');

    $activity = Activity::query()->where('event', 'brand_renamed_validation_failed')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('value'))->toBe('ab');
});

test('member without update permission is forbidden from saving an inline edit', function () {
    [$staff, $store] = inlineEditStoreEmployee(StoreRoleEnum::STAFF->value);

    $brand = inlineEditBrand($store);

    actingAs($staff)->withSession(['current_store_id' => $store->id]);

    // Sanity check: staff role cannot update products.
    expect(canStore(StorePermissionEnum::PRODUCT_UPDATE->value))->toBeFalse();

    Livewire::test(InlineEditComponent::class, ['brandId' => $brand->id])
        ->set('editingField', 'brand.name')
        ->set('editingId', $brand->id)
        ->set('editingValue', 'Hacker Name')
        ->call('saveName')
        ->assertStatus(403);

    expect($brand->fresh()->name)->toBe('Original Brand')
        ->and(Activity::query()->count())->toBe(0);
});

test('cancel restores the snapshot and clears edit state', function () {
    [$user, $store] = inlineEditStoreEmployee(StoreRoleEnum::OWNER->value);
    $brand = inlineEditBrand($store);

    actingAs($user)->withSession(['current_store_id' => $store->id]);

    Livewire::test(InlineEditComponent::class, ['brandId' => $brand->id])
        ->call('startEditName', $brand->id)
        ->set('editingValue', 'Changed Without Saving')
        ->call('cancelName')
        ->assertSet('editingField', null)
        ->assertSet('editingValue', 'Original Brand');

    expect($brand->fresh()->name)->toBe('Original Brand');
});
