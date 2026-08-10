<x-layouts.guest title="{{ __('buttons.login') }}">

    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Auth Card --}}
    <x-auth.card title="{{ __('buttons.login') }}" subtitle="{{ __('messages.welcome_back') }}">

        {{-- Switcher Login/Register --}}
        <x-auth.switcher />

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-6 mt-4">
            @csrf

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('auth.email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Password --}}
            <div>
                <x-input-label for="password" :value="__('auth.password_label')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required
                    autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Remember Me & Forgot Password --}}
            <div class="flex items-center justify-between text-sm mt-2">
                <label class="flex items-center gap-2">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900
                                  text-primary-600 shadow-sm focus:ring-primary-500 dark:focus:ring-offset-gray-800" />
                    {{ __('messages.remember_me') }}
                </label>

                <a href="{{ route('password.request') }}"
                    class="text-primary-600 dark:text-primary-400 hover:underline">
                    {{ __('messages.forgot_password') }}
                </a>
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                   {{ __('buttons.login') }}
                </x-primary-button>
            </div>

            {{-- Optional: Register Link --}}
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                {{ __('messages.Dont_have_account') }}
                <a href="{{ route('register') }}" class="text-primary-600 dark:text-primary-400 underline">
                    {{ __('buttons.register') }}
                </a>
            </p>

            {{-- Admin Login Link --}}
            <p class="text-center text-xs text-gray-400 dark:text-gray-500 mt-2">
                {{ __('messages.merchant_is_staff') }}
                <a href="{{ route('admin.login') }}" class="text-primary-500 dark:text-primary-400 underline">
                    {{ __('auth.admin_login') }}
                </a>
            </p>
        </form>

    </x-auth.card>

</x-layouts.guest>
