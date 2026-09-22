<footer
    {{ $attributes->merge([
        'class' => 'mt-24 border-t border-surface-border bg-surface text-sm text-ink'
    ]) }}
>
    <div class="max-w-7xl mx-auto px-6 py-12 grid md:grid-cols-4 gap-8">

        {{-- Brand --}}
        <div class="md:col-span-1">
            <div class="text-xl font-bold text-brand-600 dark:text-brand-400 mb-3">{{ config('app.name') }}</div>
            <p class="text-ink-muted text-sm leading-relaxed">
                {{ __('landing.footer_desc') }}
            </p>
            {{-- Social --}}
            <div class="flex gap-3 mt-5">
                @foreach (['logo-twitter', 'logo-facebook', 'logo-instagram', 'logo-linkedin'] as $social)
                    <a href="#" class="w-9 h-9 rounded-lg bg-surface-secondary flex items-center justify-center text-ink-soft hover:text-brand-600 dark:hover:text-brand-400 hover:bg-surface-tertiary transition">
                        <ion-icon name="{{ $social }}" class="text-lg"></ion-icon>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Product --}}
        <div>
            <h4 class="font-semibold mb-4 text-ink">{{ __('landing.product') }}</h4>
            <ul class="space-y-2.5">
                <li><a href="#services" class="text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition">{{ __('landing.services') }}</a></li>
                <li><a href="#pricing" class="text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition">{{ __('landing.pricing') }}</a></li>
                <li><a href="#faq" class="text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition">{{ __('landing.faq_title') }}</a></li>
            </ul>
        </div>

        {{-- Company --}}
        <div>
            <h4 class="font-semibold mb-4 text-ink">{{ __('landing.company') }}</h4>
            <ul class="space-y-2.5">
                <li><a href="#" class="text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition">{{ __('landing.about_us') }}</a></li>
                <li><a href="{{ route('contact') }}" class="text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition">{{ __('landing.contact_us') }}</a></li>
            </ul>
        </div>

        {{-- Legal --}}
        <div>
            <h4 class="font-semibold mb-4 text-ink">{{ __('landing.legal') }}</h4>
            <ul class="space-y-2.5">
                <li><a href="#" class="text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition">{{ __('landing.privacy_policy') }}</a></li>
                <li><a href="#" class="text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition">{{ __('landing.terms') }}</a></li>
            </ul>
        </div>

    </div>

    <div class="border-t border-surface-border">
        <div class="max-w-7xl mx-auto px-6 py-5 flex flex-col sm:flex-row justify-between items-center gap-3">
            <p class="text-xs text-ink-soft">
                &copy; {{ now()->year }} {{ config('app.name') }}. {{ __('landing.all_rights_reserved') }}
            </p>
            <div class="flex items-center gap-4 text-xs text-ink-soft">
                <a href="{{ route('landing') }}" class="hover:text-brand-600 transition">{{ __('landing.home') }}</a>
                <a href="#services" class="hover:text-brand-600 transition">{{ __('landing.services') }}</a>
                <a href="#pricing" class="hover:text-brand-600 transition">{{ __('landing.pricing') }}</a>
            </div>
        </div>
    </div>
</footer>
