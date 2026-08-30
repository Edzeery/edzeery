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
  <link rel="icon" href="{{ asset('img/icons/newlogo.ico') }}" type="image/x-icon" />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/edz-loader.js', 'resources/js/panel.js'])
    <script type="module" src="{{ asset('vendor/ionicons/ionicons.esm.js') }}"></script>

</head>

<body
    class="min-h-screen flex flex-col items-center justify-center
            bg-surface-bg
            text-ink
            transition-colors duration-300 antialiased">

    {{-- Background decorations --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10" aria-hidden="true">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-brand-500/5 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-brand-400/5 blur-3xl"></div>
        <div class="absolute top-1/3 left-1/4 w-64 h-64 rounded-full bg-brand-300/3 blur-2xl"></div>
    </div>

    {{-- Top Controls --}}
    <div class="absolute top-4 {{ $algin }}-4 flex items-center gap-2 z-50">
        <x-lang-switcher />
        <x-dark-toggle />
    </div>

    <main class="w-full max-w-6xl px-4 py-8 animate-fade-up">

        {{-- Brand --}}
        <div class="text-center mb-8">
            <a href="{{ route('landing') }}" class="inline-flex items-center justify-center gap-3 group">
                <x-application-logo class="w-12 h-12 text-brand-600 dark:text-brand-400 transition-transform duration-300 group-hover:scale-110" />
                <span class="font-bold text-xl tracking-tight text-ink">
                    {{ config('app.name', 'Edzeery') }}
                </span>
            </a>
        </div>

        {{-- Slot Content --}}
        <div class="w-full">
            {{ $slot }}
        </div>

    </main>

    {{-- Global loader (boot cover / SPA navigation / heavy actions) --}}
    <x-edz.global-loader />

    @livewireScripts
</body>

</html>
