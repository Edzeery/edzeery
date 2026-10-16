<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthSecurityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * The response is deliberately identical whether or not the address is
     * registered (and whether or not the per-account throttle is active), so
     * the endpoint cannot be used to enumerate accounts. The real outcome is
     * recorded server-side only.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, AuthSecurityLogger $logger): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        $logger->passwordResetRequested($request, $status !== Password::INVALID_USER);

        return back()->with('status', __('passwords.sent'));
    }
}
