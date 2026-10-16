<?php

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;

const STRONG_PASSWORD = 'Str0ng!Passw0rd#2026';

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('forgot password response does not reveal whether an account exists', function () {
    Notification::fake();

    $user = User::factory()->create();

    $known = $this->post('/forgot-password', ['email' => $user->email]);
    $unknown = $this->post('/forgot-password', ['email' => 'ghost-'.uniqid().'@example.com']);

    foreach ([$known, $unknown] as $response) {
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', __('passwords.sent'));
    }
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('reset password page suppresses the referrer to protect the token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $this->get('/reset-password/'.$notification->token)
            ->assertOk()
            ->assertHeader('Referrer-Policy', 'no-referrer');

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => STRONG_PASSWORD,
            'password_confirmation' => STRONG_PASSWORD,
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });

    expect(Hash::check(STRONG_PASSWORD, $user->refresh()->password))->toBeTrue();
});

test('a password change notification is sent after a successful reset', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => STRONG_PASSWORD,
            'password_confirmation' => STRONG_PASSWORD,
        ]);

        return true;
    });

    Notification::assertSentTo($user, PasswordChangedNotification::class);
});

test('a reset token can only be used once', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $payload = [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => STRONG_PASSWORD,
            'password_confirmation' => STRONG_PASSWORD,
        ];

        $this->post('/reset-password', $payload)->assertSessionHasNoErrors();

        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');

        expect(session('errors')->first('email'))->toBe(__('passwords.token'));

        return true;
    });
});

test('reset submission does not reveal whether an account exists', function () {
    Notification::fake();

    $response = $this->post('/reset-password', [
        'token' => 'not-a-real-token',
        'email' => 'ghost-'.uniqid().'@example.com',
        'password' => STRONG_PASSWORD,
        'password_confirmation' => STRONG_PASSWORD,
    ]);

    $response->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toBe(__('passwords.token'));
});

test('reset with an invalid token surfaces the generic token error', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    $response = $this->post('/reset-password', [
        'token' => 'totally-wrong-token',
        'email' => $user->email,
        'password' => STRONG_PASSWORD,
        'password_confirmation' => STRONG_PASSWORD,
    ]);

    $response->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toBe(__('passwords.token'));
});

test('reset rejects a password that does not meet the policy', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');

        return true;
    });
});

test('forgot password requests are rate limited per email', function () {
    $email = 'ratelimit-'.uniqid().'@example.com';

    for ($i = 0; $i < 3; $i++) {
        $this->post('/forgot-password', ['email' => $email])->assertStatus(302);
    }

    $this->post('/forgot-password', ['email' => $email])->assertStatus(429);
});

test('password reset submissions are rate limited', function () {
    $email = 'reset-limit-'.uniqid().'@example.com';

    $payload = [
        'token' => 'whatever',
        'email' => $email,
        'password' => STRONG_PASSWORD,
        'password_confirmation' => STRONG_PASSWORD,
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->post('/reset-password', $payload)->assertStatus(302);
    }

    $this->post('/reset-password', $payload)->assertStatus(429);
});

test('login attempts are rate limited', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(302);
    }

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

test('changing the password invalidates sessions established with the old hash', function () {
    $user = User::factory()->create(['password' => Hash::make(STRONG_PASSWORD)]);

    $this->actingAs($user);

    // Simulate another device whose session still holds the previous hash.
    $this->withSession(['password_hash_web' => $user->getAuthPassword()]);

    $user->forceFill(['password' => Hash::make('Rotated!Passw0rd#2027')])->save();

    $this->get(route('password.confirm'))->assertRedirect(route('login'));
});

test('password reset activity is audited without storing raw identifiers', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => STRONG_PASSWORD,
            'password_confirmation' => STRONG_PASSWORD,
        ]);

        return true;
    });

    $events = Activity::where('log_name', 'auth')->pluck('event')->all();

    expect($events)->toContain('password_reset_requested');
    expect($events)->toContain('password_reset_succeeded');

    $properties = Activity::where('log_name', 'auth')
        ->get()
        ->flatMap(fn ($activity) => array_values($activity->properties->toArray()))
        ->map(fn ($value) => (string) $value)
        ->implode(' ');

    expect($properties)->not->toContain($user->email);
});
