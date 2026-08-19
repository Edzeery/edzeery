<x-layouts.guest title="{{ __('titles.forgot_password') }}">

    {{-- Auth Card --}}
    <x-auth.card title="{{ __('titles.forgot_password') }}" subtitle="{{ __('messages.forgot_password_msg') }}">

        {{-- Session Status --}}
        <x-auth-session-status class="mb-4" :status="session('status')" />

        {{-- Forgot Password Form --}}
        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            {{-- Email Address --}}
            <div>
                <x-input-label for="email" :value="__('auth.email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                    :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            {{-- Submit Button --}}
            <div class="pt-2">
                <x-primary-button class="w-full justify-center">
                    <ion-icon name="mail-outline" class="text-lg"></ion-icon>
                    {{ __('buttons.send') }}
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
