<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $dir }}" class="h-full scroll-smooth">

<head>
    <meta charset="UTF-8">
    <script>
        (function() {
            var t = localStorage.getItem('edz-theme');
            if (t === 'dark' || (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme:dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
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
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/edz-loader.js'])
    <script type="module" src="{{ asset('vendor/ionicons/ionicons.esm.js') }}"></script>

</head>

<body class="bg-surface-bg dark:bg-dark-surface
text-ink dark:text-gray-100
antialiased">

    {{-- Navbar --}}
    <x-layouts.navbar />

    {{-- Main content --}}
    <main class="pt-24">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <x-layouts.footer />

    {{-- Global loader (boot cover / heavy actions) --}}
    <x-edz.global-loader />

    @if (session('swal.type'))
        <div data-sw="{{ session('swal.type') }}" data-sw-title="{{ session('swal.title', '') }}"
            data-sw-message="{{ session('swal.message', '') }}" hidden></div>
    @elseif (session('success'))
        <div data-sw="success" data-sw-message="{{ session('success') }}" hidden></div>
    @elseif (session('error'))
        <div data-sw="error" data-sw-message="{{ session('error') }}" hidden></div>
    @endif

</body>

</html>
