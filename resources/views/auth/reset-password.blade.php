<x-layouts.guest title="{{ __('titles.reset_password') }}">

    {{-- Auth Card --}}
    <x-auth.card title="{{ __('titles.reset_password') }}" subtitle="{{ __('messages.reset_password_msg') }}">
 

        <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
            @csrf

            {{-- Password Reset Token --}}
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- Email Address --}}
            <div>
                <x-input-label for="email" :value="__('auth.email')" />
                <x-text-input id="email"
                              name="email"
                              type="email"
                              class="mt-1 block w-full"
                              :value="old('email', $request->email)"
                              required
                              autofocus
                              autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Password --}}
            <div>
                <x-input-label for="password" :value="__('auth.password_label')" />
                <x-text-input id="password"
                              name="password"
                              type="password"
                              class="mt-1 block w-full"
                              required
                              autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Confirm Password --}}
            <div>
                <x-input-label for="password_confirmation" :value="__('auth.confirm_password')" />
                <x-text-input id="password_confirmation"
                              name="password_confirmation"
                              type="password"
                              class="mt-1 block w-full"
                              required
                              autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center justify-center">
                <x-primary-button class="py-3">
                    {{ __('buttons.reset') }}
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
