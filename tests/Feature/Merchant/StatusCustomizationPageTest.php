<?php

use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
});

function createCustomizationMember(string $storeRole): array
{
    $user = roleUser('merchant');

    $user->assignRole(Role::findOrCreate($storeRole, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Customization Store',
        'slug' => 'customization-store-'.uniqid(),
        'status' => 'active',
    ]);

    StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
    ]);

    return [$user, $store];
}

test('status customization page renders for members with STORE_UPDATE', function () {
    [$user, $store] = createCustomizationMember('owner');

    $html = $this->actingAs($user)
        ->get(route('merchant.customization.statuses', ['store' => $store->slug]))
        ->assertOk()
        ->getContent();

    // Page header + description.
    expect($html)->toContain('Status Customization')
        ->and($html)->toContain('Customize the statuses your team uses');

    // The three interior tabs.
    expect($html)->toContain("setTab('confirmation')")
        ->and($html)->toContain("setTab('carrier_tracking')")
        ->and($html)->toContain("setTab('rider_tracking')");

    // Default tab shows the confirmation panel (customize + reorder views).
    expect($html)->toContain("setConfirmationView('customize')")
        ->and($html)->toContain("setConfirmationView('order')")
        ->and($html)->toContain('edz-table');

    // Sidebar: customization sub-link present, group auto-expanded, sub-link active.
    expect($html)->toContain('merchant.customization.statuses')
        ->and($html)->toContain('>Statuses</span>')
        ->and($html)->toContain('edz-sidebar__sub-link--active')
        ->and($html)->toContain('store: true');
});

test('status customization page enforces STORE_UPDATE gate', function () {
    [$user, $store] = createCustomizationMember('staff');

    $this->actingAs($user)
        ->get(route('merchant.customization.statuses', ['store' => $store->slug]))
        ->assertForbidden();
});

test('carrier tracking tab renders the status dictionary as a reference table', function () {
    [$user, $store] = createCustomizationMember('owner');

    $this->actingAs($user);
    $this->session(['current_store_id' => $store->id]);

    $component = Livewire::test('merchant.customization.statuses')
        ->call('setTab', 'carrier_tracking')
        ->assertOk()
        ->assertSet('tab', 'carrier_tracking')
        ->assertSet('carrier', 'noest');

    // NOEST raw values + a resolved label from the kit/DB.
    $component->assertSee('livre', false)
        ->assertSee('colis_suspendu', false)
        ->assertSee('ask_to_delete_by_admin', false)
        ->assertSee('delivered', false)
        ->assertSee('Delivered', false);

    // Switching carrier re-renders the rows from the Yalidine vocabulary.
    $component->set('carrier', 'yalidine')
        ->assertSee('Sorti en livraison', false)
        ->assertSee('Bloqué', false)
        ->assertSee('Colis abandonné', false);
});

test('carrier tracking tab shows the Arabic real meaning column', function () {
    [$user, $store] = createCustomizationMember('owner');

    $this->actingAs($user);
    $this->session(['current_store_id' => $store->id]);

    Livewire::test('merchant.customization.statuses')
        ->call('setTab', 'carrier_tracking')
        ->assertSet('tab', 'carrier_tracking')
        ->assertSee('تم تسليم الشحنة للزبون', false)
        ->assertSee('أُوقفت المعالجة بانتظار تدخل الإدارة', false);
});

test('rider tracking tab exposes customize/reorder views and add button', function () {
    [$user, $store] = createCustomizationMember('owner');

    $this->actingAs($user);
    $this->session(['current_store_id' => $store->id]);

    $component = Livewire::test('merchant.customization.statuses')
        ->call('setTab', 'rider_tracking')
        ->assertSet('tab', 'rider_tracking')
        ->assertSee("setRiderView('customize')", false)
        ->assertSee("setRiderView('order')", false)
        ->assertSee("showAddRider", false);

    // Add a custom rider status through the component.
    $component->set('newRiderLabel', 'Arrived at rider')
        ->set('newRiderColor', 'info')
        ->call('addRiderStatus')
        ->assertDispatched('swal');

    expect(\App\Models\Status::where('store_id', $store->id)->where('type', 'tracking')->exists())->toBeTrue();

    $riderKeys = collect($component->get('riderStatusList'))->pluck('key')->all();
    expect($riderKeys)->toBe(\App\Models\Status::where('store_id', $store->id)->where('type', 'tracking')->pluck('key')->all());
});

test('rider tab deletes a custom status through the component', function () {
    [$user, $store] = createCustomizationMember('owner');

    $this->actingAs($user);
    $this->session(['current_store_id' => $store->id]);

    $service = app(\App\Services\Stores\StoreStatusService::class);
    $custom = $service->addStatus((string) $store->id, \App\Services\Stores\StoreStatusService::TRACKING_TYPE, 'Temp rider', 'gray');

    Livewire::test('merchant.customization.statuses')
        ->call('setTab', 'rider_tracking')
        ->call('deleteRiderStatus', $custom->key)
        ->assertDispatched('swal');

    expect(\App\Models\Status::where('id', $custom->id)->exists())->toBeFalse();
});

test('confirmation tab adds a linked custom status and shows the link badge', function () {
    $this->seed(\Database\Seeders\SystemStatusesSeeder::class);

    [$user, $store] = createCustomizationMember('owner');

    $this->actingAs($user);
    $this->session(['current_store_id' => $store->id]);

    $component = Livewire::test('merchant.customization.statuses')
        ->assertSee("showAddConfirmation", false);

    $component->set('newConfirmationLabel', 'Contacted and confirmed')
        ->set('newConfirmationColor', 'success')
        ->set('newConfirmationLinkedTo', 'confirmed')
        ->call('addConfirmationStatus')
        ->assertDispatched('swal');

    $custom = \App\Models\Status::where('store_id', $store->id)->where('type', 'order')->where('linked_to', 'confirmed')->first();

    expect($custom)->not->toBeNull()
        ->and($custom->label)->toBe('Contacted and confirmed')
        ->and($custom->movement_type)->toBe('reserve');

    // The custom row is included in the on-page confirmation list.
    $keys = collect($component->get('statusList'))->pluck('key')->all();
    expect(in_array($custom->key, $keys, true))->toBeTrue();
});
