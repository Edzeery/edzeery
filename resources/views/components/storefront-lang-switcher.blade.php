@php
    $StoreContext = app(\App\Support\StoreContext::class);
    $store = $StoreContext->get();

    $settings = $store?->settings ?? null;
    $supported = $settings?->supported_languages ?? [];
    $supported = array_values(array_filter($supported));
    if (empty($supported)) {
        $supported = ['ar', 'fr', 'en', 'es'];
    }
    $current = app()->getLocale();
@endphp

@if (count($supported) > 1)
    <div x-data="{ open: false }" class="relative">
        <button x-on:click="open = !open" x-on:click.outside="open = false"
            class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium
                   text-gray-600 dark:text-gray-300
                   hover:bg-gray-100 dark:hover:bg-gray-700
                   transition-colors duration-150 h-10"
            aria-label="{{ __('general.language') }}">
            <ion-icon name="language-outline" class="text-lg"></ion-icon>
            <span class="hidden sm:inline uppercase tracking-wide">{{ $current }}</span>
            <ion-icon name="chevron-down-outline" class="text-xs opacity-60"></ion-icon>
        </button>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
            class="absolute end-0 mt-2 w-44 rounded-xl bg-white dark:bg-gray-800
                   border border-gray-200 dark:border-gray-700
                   shadow-xl shadow-black/5
                   py-1 z-50 origin-top-right"
            style="display: none;">

            <div class="px-3 py-1.5">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    {{ __('general.language') }}
                </p>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700"></div>

            @foreach ($supported as $code)
                @php
                    $flag = match($code) { 'ar' => '🇸🇦', 'fr' => '🇫🇷', 'en' => '🇬🇧', 'es' => '🇪🇸', default => '🌐' };
                    $name = match($code) { 'ar' => 'العربية', 'fr' => 'Français', 'en' => 'English', 'es' => 'Español', default => strtoupper($code) };
                    $isActive = $current === $code;
                @endphp
                <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['lang' => $code])) }}"
                    class="flex items-center gap-2.5 px-3 py-2 text-sm transition-colors duration-100
                           {{ $isActive
                              ? 'store-text-primary font-semibold bg-gray-50 dark:bg-gray-700/50'
                              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <span class="text-base leading-none">{{ $flag }}</span>
                    <span>{{ $name }}</span>
                    @if ($isActive)
                        <ion-icon name="checkmark-outline" class="text-sm ms-auto store-text-primary"></ion-icon>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@endif
