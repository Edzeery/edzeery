<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Services\Auth\AuthSecurityLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     *
     * The token is part of the URL, so we suppress the Referer header on this
     * page to keep the token out of any outbound third-party requests.
     */
    public function create(Request $request): Response
    {
        return response()
            ->view('auth.reset-password', ['request' => $request])
            ->header('Referrer-Policy', 'no-referrer');
    }

    /**
     * Handle an incoming new password request.
     *
     * The token consumption is serialized per account (cache lock) and wrapped
     * in a transaction so a token cannot be redeemed twice under concurrency.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, AuthSecurityLogger $logger): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = (string) $request->input('email');
        $lock = Cache::lock('password-reset:'.Str::lower($email), 10);
        $resetUser = null;

        try {
            $lock->block(3);

            $status = DB::transaction(function () use ($request, &$resetUser) {
                return Password::reset(
                    $request->only('email', 'password', 'password_confirmation', 'token'),
                    function (User $user) use ($request, &$resetUser) {
                        $user->forceFill([
                            'password' => Hash::make($request->password),
                            'remember_token' => Str::random(60),
                        ])->save();

                        $resetUser = $user;

                        event(new PasswordReset($user));
                    }
                );
            });
        } catch (LockTimeoutException) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('passwords.throttled')]);
        } finally {
            $lock->release();
        }

        if ($status === Password::PASSWORD_RESET && $resetUser instanceof User) {
            $logger->passwordResetSucceeded($resetUser);
            $resetUser->notify(new PasswordChangedNotification($resetUser->email));

            return redirect()->route('login')->with('status', __($status));
        }

        $logger->passwordResetFailed($request);

        // Never disclose whether the account exists — invalid user and invalid
        // token both surface as a generic "link is invalid" message.
        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __('passwords.token')]);
    }
}
