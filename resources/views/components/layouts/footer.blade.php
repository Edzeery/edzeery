<footer
{{ $attributes->merge([
    'class' => 'mt-24 border-t border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface text-sm text-neutral-text dark:text-dark-text'
]) }} >
    <div class="max-w-7xl mx-auto px-6 py-10 grid md:grid-cols-3 gap-8">

        {{-- Brand --}}
        <div>
            <div class="text-xl font-bold text-primary mb-3">{{   config('app.name') }}</div>
            <p class="text-gray-500 dark:text-gray-400">
                {{ __('landing.footer_desc') ?? 'منصة ذكية لإدارة المتاجر والطلبات باحترافية.' }}
            </p>
        </div>

        {{-- Links --}}
        <div>
            <h4 class="font-semibold mb-3">{{ __('landing.links') ?? 'روابط' }}</h4>
            <ul class="space-y-2">
                <li><a href="#services" class="hover:text-primary transition">{{ __('landing.services') }}</a></li>
                <li><a href="#pricing" class="hover:text-primary transition">{{ __('landing.pricing') }}</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-primary transition">{{ __('buttons.login') }}</a></li>
            </ul>
        </div>

        {{-- Legal --}}
        <div>
            <h4 class="font-semibold mb-3">{{ __('landing.legal') ?? 'قانوني' }}</h4>
            <ul class="space-y-2">
                <li><a href="#" class="hover:text-primary transition">{{ __('landing.privacy_policy') }}</a></li>
                <li><a href="#" class="hover:text-primary transition">{{ __('landing.terms') }}</a></li>
            </ul>
        </div>

    </div>

    <div class="text-center text-xs text-gray-400 pb-6">
        © {{ now()->year }} Edzeery. All rights reserved.
    </div>
</footer>
