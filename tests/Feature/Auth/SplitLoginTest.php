<?php

/* ============================ صفحات الدخول ============================ */

test('admin login screen can be rendered', function () {
    $this->get('/admin/login')->assertStatus(200);
});

test('merchant login screen can be rendered', function () {
    $this->get('/login')->assertStatus(200);
});

test('guests hitting the admin panel are redirected to the admin login', function () {
    $this->get('/super-admin')->assertRedirect('/admin/login');
});

/* ============================ دخول موظفي المنصة ============================ */

$staffRoles = ['super_admin', 'admin', 'support_agent', 'tech_support'];

foreach ($staffRoles as $staffRole) {
    test("{$staffRole} can sign in via the admin login page", function () use ($staffRole) {
        $user = roleUser($staffRole);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect();
        expect($response->headers->get('Location'))->toContain('/super-admin');
    });
}

test('merchants can not sign in via the admin login page', function () {
    $user = roleUser('merchant');

    $response = $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/admin/login');
    $response->assertSessionHasErrors('email');
});

test('regular users can not sign in via the admin login page', function () {
    $user = roleUser('user');

    $response = $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/admin/login');
    $response->assertSessionHasErrors('email');
});

test('admin login rejects invalid credentials', function () {
    $user = roleUser('super_admin');

    $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

/* ============================ دخول التجار / المستخدمين ============================ */

test('merchants can sign in via the merchant login page', function () {
    $user = roleUser('merchant');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect();
    expect($response->headers->get('Location'))->not->toContain('/login');
});

test('regular users can sign in via the merchant login page', function () {
    $user = roleUser('user');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect();
    expect($response->headers->get('Location'))->not->toContain('/login');
});

test('platform staff can not sign in via the merchant login page', function () {
    $user = roleUser('super_admin');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/admin/login');
});
