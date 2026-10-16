<?php

use App\Domains\Account\Models\Profile;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

function createGeoHierarchy(): array
{
    $country = Country::create(['name' => 'Algeria', 'code' => 'DZ', 'is_active' => true]);
    $state = State::create(['country_id' => $country->id, 'state_code' => '16', 'name' => 'Alger', 'is_active' => true]);
    $city = City::create(['state_id' => $state->id, 'name' => 'Algiers', 'post_code' => '16000', 'is_active' => true]);

    return [$country, $state, $city];
}

test('account pages render', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/merchant/account/profile')->assertOk();
    $this->actingAs($user)->get('/merchant/account/security')->assertOk();
    $this->actingAs($user)->get('/merchant/account/personal-data')->assertOk();
});

test('profile update persists name, phone and location', function () {
    [$country, $state, $city] = createGeoHierarchy();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('account.profile')
        ->set('name', 'Sara Test')
        ->set('phone', '0550000000')
        ->set('country_id', $country->id)
        ->set('state_id', $state->id)
        ->set('city_id', $city->id)
        ->call('updateProfile')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->name)->toBe('Sara Test');
    expect($user->country_id)->toBe($country->id);
    expect($user->state_id)->toBe($state->id);
    expect($user->city_id)->toBe($city->id);
    expect($user->profile->phone)->toBe('0550000000');
});

test('profile update normalizes an empty location to null', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('account.profile')
        ->set('country_id', '')
        ->set('state_id', '')
        ->set('city_id', '')
        ->call('updateProfile')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->country_id)->toBeNull();
    expect($user->state_id)->toBeNull();
    expect($user->city_id)->toBeNull();
});

test('changing the email requires the current password', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('account.profile')
        ->set('email', 'changed@example.com')
        ->call('updateProfile')
        ->assertHasErrors('current_password');

    expect($user->refresh()->email)->not->toBe('changed@example.com');
});

test('changing the email with the current password updates and unverifies it', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('account.profile')
        ->set('email', 'changed@example.com')
        ->set('current_password', 'password')
        ->call('updateProfile')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->email)->toBe('changed@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('profile update rejects an email already taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('account.profile')
        ->set('email', 'taken@example.com')
        ->set('current_password', 'password')
        ->call('updateProfile')
        ->assertHasErrors('email');
});

test('profile update persists changes to an existing profile', function () {
    $user = User::factory()->create();
    Profile::create(['user_id' => $user->id, 'phone' => '0500000000', 'address' => 'Old address']);

    Livewire::actingAs($user)
        ->test('account.profile')
        ->set('phone', '0661112233')
        ->set('address', 'New address')
        ->call('updateProfile')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->profile->phone)->toBe('0661112233');
    expect($user->profile->address)->toBe('New address');
});

test('removing the avatar clears the stored picture', function () {
    $user = User::factory()->create();
    Profile::create(['user_id' => $user->id, 'profile_picture' => 'profiles/old.jpg']);

    Livewire::actingAs($user)
        ->test('account.profile')
        ->set('remove_avatar', true)
        ->call('updateProfile')
        ->assertHasNoErrors();

    expect($user->refresh()->profile->profile_picture)->toBeNull();
});

test('settings save persists preferences', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('account.personal-data')
        ->set('language', 'en')
        ->set('theme', 'dark')
        ->call('saveSettings')
        ->assertHasNoErrors();

    $prefs = $user->refresh()->settings->preferences;
    expect($prefs['language'])->toBe('en');
    expect($prefs['theme'])->toBe('dark');
});

test('password can be updated through the security component', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('account.security')
        ->set('current_password', 'password')
        ->set('new_password', 'New-password-123!')
        ->set('new_password_confirmation', 'New-password-123!')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('New-password-123!', $user->refresh()->password))->toBeTrue();
});

test('security component rejects a wrong current password', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('account.security')
        ->set('current_password', 'wrong-password')
        ->set('new_password', 'New-password-123!')
        ->set('new_password_confirmation', 'New-password-123!')
        ->call('updatePassword')
        ->assertHasErrors('current_password');

    expect(Hash::check('New-password-123!', $user->refresh()->password))->toBeFalse();
});
