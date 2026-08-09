<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\LoginRedirectService;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        $title = __('messages.login');
        return view('auth.login', compact('title'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request, LoginRedirectService $redirectService)
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


        $request->session()->regenerate();
        return redirect()->to(
            $redirectService->handle(Auth::user())
        );
    }

    /**
     * Destroy an authenticated session.
     */

    public function destroy(Request $request)
    {
        $user = Auth::user();
        Auth::guard('web')->logout();

        // مسح الجلسة
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // مسح Tenant من Filament
        Filament::setTenant(null);

        return redirect()->route('login');
    }
}
