<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // Registration creates the user but does not auto-login
    // (auth()->login() is commented out in RegisteredUserController)
    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});
