<x-layouts.guest title="{{ __('titles.reset_password') }}">

    {{-- Auth Card --}}
    <x-auth.card title="{{ __('titles.reset_password') }}" subtitle="{{ __('messages.forgot_password_msg') }}">

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf

            {{-- Password Reset Token --}}
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- Email Address --}}
            <div>
                <x-input-label for="email" :value="__('auth.email')" />
                <x-text-input id="email" name="email" type="email" inputmode="email" class="mt-1 block w-full"
                    :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            {{-- Password --}}
            <div>
                <x-password-input id="password" name="password" :label="__('auth.password_label')"
                    autocomplete="new-password" required />
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            {{-- Confirm Password --}}
            <div>
                <x-password-input id="password_confirmation" name="password_confirmation" :label="__('auth.confirm_password')"
                    autocomplete="new-password" required />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
            </div>

            {{-- Submit Button --}}
            <div class="pt-2">
                <x-primary-button class="w-full justify-center">
                    <x-edz.icon name="lock-closed" class="w-5 h-5" aria-hidden="true" />
                    {{ __('buttons.reset') }}
                </x-primary-button>
            </div>

            {{-- Back to Login --}}
            <p class="text-center text-sm text-ink-muted mt-4">
                {{ __('messages.remembered_password') }}
                <a href="{{ route('login') }}" class="text-brand-600 dark:text-brand-400 hover:text-brand-700 font-medium transition">
                    {{ __('buttons.login') }}
                </a>
            </p>

        </form>

    </x-auth.card>

</x-layouts.guest>
