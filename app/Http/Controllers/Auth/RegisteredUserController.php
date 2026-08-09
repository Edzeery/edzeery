<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Platform\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\LoginRedirectService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function store(Request $request, LoginRedirectService $redirectService)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // اعطاء صلاحية تاجر
        $role = Role::firstOrCreate(['name' => UserRoleEnum::MERCHANT->value]);
        $user->assignRole($role);

        $request->session()->regenerate();
        event(new Registered($user));
        // تسجيل الدخول مباشرة بعد التسجيل
        // auth()->login($user);
        return redirect()->to(
            $redirectService->handle($user)
        );
    }
}
