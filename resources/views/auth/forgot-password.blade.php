<x-layouts.guest title="{{ __('titles.forgot_password') }}">

    {{-- Auth Card --}}
    <x-auth.card title="{{ __('titles.forgot_password') }}" subtitle="{{ __('messages.forgot_password_msg') }}">
 
        {{-- Session Status --}}
        <x-auth-session-status class="mb-4" :status="session('status')" />

        {{-- Forgot Password Form --}}
        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf

            {{-- Email Address --}}
            <div>
                <x-input-label for="email" :value="__('auth.email')" />
                <x-text-input id="email"
                              name="email"
                              type="email"
                              class="mt-1 block w-full"
                              :value="old('email')"
                              required
                              autofocus
                              autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Submit Button --}}
             <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                    {{ __('buttons.send') }}
                </x-primary-button>
            </div>

            {{-- Optional: Back to Login --}}
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                {{ __('messages.remembered_password') }}
                <a href="{{ route('login') }}" class="text-primary-600 dark:text-primary-400 underline">
                    {{ __('buttons.login') }}
                </a>
            </p>
        </form>

    </x-auth.card>

</x-layouts.guest>
