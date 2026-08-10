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

    // المسجل الجديد يصبح تاجراً ويدخل مباشرة إلى بوابة التجار
    $this->assertAuthenticated('merchant');
    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('/merchant');
});
