<x-layouts.guest title="{{ __('auth.confirm_password') }}">
    <x-auth.card title="{{ __('auth.confirm_password') }}" subtitle="{{ __('auth.confirm_password_subtitle') }}">

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="password" :value="__('auth.password_label')" />
                <x-text-input id="password" class="block mt-1 w-full"
                    type="password" name="password"
                    required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <div class="pt-2">
                <x-primary-button class="w-full justify-center">
                    <ion-icon name="lock-closed-outline" class="text-lg"></ion-icon>
                    {{ __('buttons.confirm') }}
                </x-primary-button>
            </div>
        </form>

    </x-auth.card>
</x-layouts.guest>