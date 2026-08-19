<x-layouts.guest title="{{ __('auth.verify_email_title') }}">
    <x-auth.card title="{{ __('auth.verify_email_title') }}" subtitle="{{ __('auth.verify_email_notice') }}">

        @if (session('status') == 'verification-link-sent')
            <div class="mb-5 p-4 rounded-xl bg-success-50 dark:bg-success-900/20 text-sm font-medium text-success-700 dark:text-success-300
                        flex items-center gap-2 border border-success-200 dark:border-success-800 animate-scale-in">
                <ion-icon name="checkmark-circle-outline" class="text-xl flex-shrink-0"></ion-icon>
                {{ __('auth.verification_link_sent') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
            @csrf
            <x-primary-button class="w-full justify-center">
                <ion-icon name="mail-outline" class="text-lg"></ion-icon>
                {{ __('auth.resend_verification') }}
            </x-primary-button>
        </form>

        <div class="mt-5 pt-5 border-t border-neutral-border/50 dark:border-dark-border/50 text-center">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 text-sm text-ink-muted hover:text-ink transition font-medium">
                    <ion-icon name="log-out-outline" class="text-base"></ion-icon>
                    {{ __('buttons.logout') }}
                </button>
            </form>
        </div>

    </x-auth.card>
</x-layouts.guest>