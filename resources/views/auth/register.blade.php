<x-layouts.guest title="{{ __('auth.register') }}">

    <x-auth.card title="{{ __('messages.register_free') }}" subtitle="{{ __('auth.register_subtitle') }}">

        <x-auth.switcher />

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
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
            <label class="flex items-start gap-2.5 cursor-pointer group">
                <div class="relative mt-0.5">
                    <input id="terms" type="checkbox" name="terms" required class="sr-only peer">
                    <div class="w-5 h-5 rounded-md border-2 border-neutral-border dark:border-dark-border
                                bg-neutral-surface dark:bg-dark-surface peer-checked:bg-brand-600 peer-checked:border-brand-600 transition-all duration-200"></div>
                    <ion-icon name="checkmark-outline"
                              class="absolute inset-0 m-auto w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity"></ion-icon>
                </div>
                <span class="text-sm text-ink-muted group-hover:text-ink transition">
                    {!! __('auth.accept_terms', [
                        'terms' => '<a href="#" class="text-brand-600 dark:text-brand-400 hover:underline font-medium">' . __('auth.terms_link') . '</a>',
                        'privacy' => '<a href="#" class="text-brand-600 dark:text-brand-400 hover:underline font-medium">' . __('auth.privacy_link') . '</a>',
                    ]) !!}
                </span>
            </label>

            {{-- Submit Button --}}
            <div class="pt-2">
                <x-primary-button class="w-full justify-center">
                    <ion-icon name="person-add-outline" class="text-lg"></ion-icon>
                    {{ __('buttons.register') }}
                </x-primary-button>
            </div>

            {{-- Already have account --}}
            <p class="text-center text-sm text-ink-muted mt-4">
                {{ __('auth.have_account') }}
                <a href="{{ route('login') }}" class="text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium transition">
                    {{ __('buttons.login') }}
                </a>
            </p>

        </form>

    </x-auth.card>

</x-layouts.guest>
