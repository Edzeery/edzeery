<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/merchant/account/profile');

    $response->assertOk();
});

test('profile page shows user name', function () {
    $user = User::factory()->create(['name' => 'Ahmed Test']);

    $response = $this
        ->actingAs($user)
        ->get('/merchant/account/profile');

    $response->assertOk();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Ahmed Test']);
});

test('password can be updated via security endpoint', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/merchant/account/password', [
            'current_password' => 'password',
            'new_password' => 'new-password-123',
            'new_password_confirmation' => 'new-password-123',
        ]);

    $response->assertRedirect();
    $this->assertTrue(
        \Illuminate\Support\Facades\Hash::check('new-password-123', $user->fresh()->password)
    );
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/merchant/account/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'new-password-123',
            'new_password_confirmation' => 'new-password-123',
        ]);

    $response->assertSessionHasErrors();
});

test('guest cannot access profile page', function () {
    $response = $this->get('/merchant/account/profile');

    $response->assertRedirect();
});
