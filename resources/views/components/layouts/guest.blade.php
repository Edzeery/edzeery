<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $dir }}" class="h-full scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    <meta name="description" content="{{ __('landing.meta_description') }}">
    <meta name="keywords" content="ecommerce, stores, saas, payments, edzeery">
    <meta name="author" content="Edzeery">
    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:description" content="{{ __('landing.meta_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/og.png') }}">
    <meta name="twitter:card" content="summary_large_image">

    <title>{{ isset($title) ? config('app.name') . ' | ' . $title : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</head>

<body
    class="min-h-screen flex flex-col items-center justify-center
            bg-surface-bg
              text-ink
             transition-colors duration-300 antialiased">

    {{-- Subtle background decoration --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-brand-500/5 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-accent-500/5 blur-3xl"></div>
    </div>

    {{-- Top Controls: Language + Dark Mode --}}
    <div class="absolute top-4
    {{ $algin }}-4 flex items-center gap-2 z-50">
        {{-- Language Switcher --}}
        <x-lang-switcher />

        {{-- Dark Mode Toggle --}}
        <x-dark-toggle />
    </div>

    <main class="w-full max-w-lg px-4 animate-fade-up">

        {{-- Brand --}}
        <div class="text-center my-6">
            <a href="{{ route('landing') }}" class="flex items-center justify-center gap-3">
                <x-application-logo class="w-16 h-16 text-primary-600 dark:text-primary-400" />
                <span class="font-bold text-2xl tracking-tight text-ink">
                    {{ config('app.name', 'Edzeery') }}
                </span>
            </a>
            <p class="text-sm text-ink-muted mt-2">
                {{ __('auth.platform_subtitle') }}
            </p>
        </div>

        {{-- Slot Content --}}
        <div class="w-full">
            {{ $slot }}
        </div>

    </main>

</body>

</html>
