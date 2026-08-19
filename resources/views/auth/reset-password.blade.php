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
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                    :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            {{-- Password --}}
            <div>
                <x-input-label for="password" :value="__('auth.password_label')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                    required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            {{-- Confirm Password --}}
            <div>
                <x-input-label for="password_confirmation" :value="__('auth.confirm_password')" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                    class="mt-1 block w-full" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
            </div>

            {{-- Submit Button --}}
            <div class="pt-2">
                <x-primary-button class="w-full justify-center">
                    <ion-icon name="key-outline" class="text-lg"></ion-icon>
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
