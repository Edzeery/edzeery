<x-layouts.guest title="{{ __('auth.register') }}">

    <x-auth.card title="{{ __('messages.register_free') }}" subtitle="{{ __('auth.register_subtitle') }}">

        {{-- Switcher (مثلاً للتسجيل كتاجر أو مستخدم) --}}
        <x-auth.switcher />

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            {{-- Name --}}
            <div>
                <x-input-label for="name" :value="__('auth.full_name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required autofocus
                    autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('auth.email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" required
                    autocomplete="email" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            {{-- Password --}}
            <div>
                <x-input-label for="password" :value="__('auth.password_label')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required
                    autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" />
            </div>

            {{-- Password Confirmation --}}
            <div>
                <x-input-label for="password_confirmation" :value="__('auth.confirm_password')" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                    class="mt-1 block w-full" required autocomplete="new-password" />
            </div>

            {{-- Terms & Conditions --}}
            <div class="flex items-start gap-2 text-sm">
                <input id="terms" type="checkbox" name="terms" required
                    class="mt-1 h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900
                           text-primary-600 shadow-sm focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                <label for="terms" class="text-gray-600 dark:text-gray-400">
                    {!! __('auth.accept_terms', [
                        'terms' => '<a href="#" class="underline text-primary-600 dark:text-primary-400">Terms</a>',
                        'privacy' => '<a href="#" class="underline text-primary-600 dark:text-primary-400">Privacy Policy</a>',
                    ]) !!}
                </label>
            </div>

            {{-- Submit Button --}}

            <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                    {{ __('buttons.save') }}
                </x-primary-button>
            </div>

            {{-- Optional: Already have account --}}
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-2">
                {{ __('auth.have_account') }}
                <a href="{{ route('login') }}" class="text-primary-600 dark:text-primary-400 underline">
                    {{ __('buttons.login') }}
                </a>
            </p>

        </form>

    </x-auth.card>

</x-layouts.guest>
