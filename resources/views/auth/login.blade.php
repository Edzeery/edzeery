<x-layouts.guest title="{{ __('buttons.login') }}">

    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Auth Card --}}
    <x-auth.card title="{{ __('buttons.login') }}" subtitle="{{ __('messages.welcome_back') }}">

        {{-- Switcher Login/Register --}}
        <x-auth.switcher />

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('auth.email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            {{-- Password --}}
            <div>
                <x-input-label for="password" :value="__('auth.password_label')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required
                    autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            {{-- Remember Me & Forgot Password --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2.5 cursor-pointer group">
                    <div class="relative">
                        <input id="remember_me" type="checkbox" name="remember" class="sr-only peer">
                        <div class="w-5 h-5 rounded-md border-2 border-neutral-border dark:border-dark-border
                                    bg-neutral-surface dark:bg-dark-surface peer-checked:bg-brand-600 peer-checked:border-brand-600 transition-all duration-200"></div>
                        <ion-icon name="checkmark-outline"
                                  class="absolute inset-0 m-auto w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity"></ion-icon>
                    </div>
                    <span class="text-sm text-ink-muted group-hover:text-ink transition">{{ __('messages.remember_me') }}</span>
                </label>

                <a href="{{ route('password.request') }}"
                    class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition font-medium">
                    {{ __('messages.forgot_password') }}
                </a>
            </div>

            {{-- Submit Button --}}
            <div class="pt-2">
                <x-primary-button class="w-full justify-center">
                    <ion-icon name="log-in-outline" class="text-lg"></ion-icon>
                    {{ __('buttons.login') }}
                </x-primary-button>
            </div>

            {{-- Register Link --}}
            <p class="text-center text-sm text-ink-muted mt-4">
                {{ __('messages.Dont_have_account') }}
                <a href="{{ route('register') }}" class="text-brand-600 dark:text-brand-400 hover:text-brand-700 font-medium transition">
                    {{ __('buttons.register') }}
                </a>
            </p>

            {{-- Admin Login Link --}}
            <p class="text-center text-xs text-ink-soft mt-2">
                {{ __('messages.merchant_is_staff') }}
                <a href="{{ route('admin.login') }}" class="text-brand-500 dark:text-brand-400 hover:underline font-medium transition">
                    {{ __('auth.admin_login') }}
                </a>
            </p>
        </form>

    </x-auth.card>

</x-layouts.guest>
