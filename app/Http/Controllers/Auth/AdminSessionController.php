<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminSessionController extends Controller
{
    /**
     * Display the admin/staff login view.
     */
    public function create(): View
    {
        return view('auth.admin-login', [
            'title' => __('auth.admin_login'),
        ]);
    }

    /**
     * Handle an incoming admin/staff authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => __('auth.invalid_credentials'),
            ])->onlyInput('email');
        }

        $user = Auth::user();

        // هذا السيرفر خاص بموظفي المنصة فقط
        if (! $user->isPlatformStaff()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'email' => __('auth.admin_only'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(
            Filament::getPanel('super-admin')->getUrl()
        );
    }
}
