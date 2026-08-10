<x-layouts.guest :title="__('auth.admin_login')">

    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Auth Card --}}
    <x-auth.card :title="__('auth.admin_login')" :subtitle="__('auth.admin_subtitle')">

        {{-- Admin Login Form --}}
        <form method="POST" action="{{ route('admin.login') }}" class="space-y-6 mt-4">
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

            {{-- Optional: Merchant Login Link --}}
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                {{ __('messages.admin_not_staff') }}
                <a href="{{ route('login') }}" class="text-primary-600 dark:text-primary-400 underline">
                    {{ __('messages.merchant_login') }}
                </a>
            </p>
        </form>

    </x-auth.card>

</x-layouts.guest>
