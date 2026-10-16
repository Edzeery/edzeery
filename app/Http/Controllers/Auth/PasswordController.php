<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\PasswordChangedNotification;
use App\Services\Auth\AuthSecurityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request, AuthSecurityLogger $logger): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $logger->passwordChanged($user);

        $user->notify(new PasswordChangedNotification($user->email));

        return back()->with('status', 'password-updated');
    }
}
