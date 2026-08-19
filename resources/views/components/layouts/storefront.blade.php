<!DOCTYPE html>
<html lang="{{ $lang ?? app()->getLocale() }}" dir="{{ $dir ?? 'ltr' }}" class="h-full scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? $store->name ?? config('app.name') }}</title>

    @php
        $theme = $store->theme ?? null;
        $primaryColor = $theme?->primary_color ?? '#4f46e5';
        $secondaryColor = $theme?->secondary_color ?? '#7c3aed';
        $fontFamily = $theme?->font_family ?? 'Cairo';
        $sections = $theme?->homepage_sections ?? ['hero', 'social_proof', 'faq', 'cta'];
    @endphp

    <style>
        :root {
            --store-primary: {{ $primaryColor }};
            --store-secondary: {{ $secondaryColor }};
            --store-font: '{{ $fontFamily }}', sans-serif;
        }
        body { font-family: var(--store-font); }
        .store-btn-primary { background-color: var(--store-primary); }
        .store-btn-primary:hover { filter: brightness(0.9); }
        .store-text-primary { color: var(--store-primary); }
        .store-bg-primary { background-color: var(--store-primary); }
        .store-border-primary { border-color: var(--store-primary); }
        .store-btn-secondary { background-color: var(--store-secondary); }
        .store-btn-secondary:hover { filter: brightness(0.9); }
        .store-text-secondary { color: var(--store-secondary); }
        .store-gradient { background: linear-gradient(135deg, var(--store-primary), var(--store-secondary)); }
    </style>

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/storefront.js'])
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased min-h-screen flex flex-col">

    {{-- Top bar --}}
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                @if($store->logo ?? null)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}" class="h-9 w-9 rounded-full object-cover group-hover:ring-2 group-hover:ring-offset-2 store-border-primary transition">
                @else
                    <div class="h-9 w-9 rounded-full store-bg-primary flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr($store->name, 0, 1)) }}
                    </div>
                @endif
                <span class="font-semibold text-lg text-gray-900 dark:text-white">{{ $store->name }}</span>
            </a>

            <div class="flex items-center gap-2">
                <a href="/" class="text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition hidden sm:inline-flex items-center gap-1">
                    <ion-icon name="home-outline" class="text-base"></ion-icon>
                    {{ __('storefront.back_to_store') }}
                </a>
                @livewire('storefront.mini-cart')
            </div>
        </div>
    </header>

    {{-- Main content --}}
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if($store->logo ?? null)
                        <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}" class="h-7 w-7 rounded-full object-cover">
                    @else
                        <div class="h-7 w-7 rounded-full store-bg-primary flex items-center justify-center text-white font-bold text-xs">
                            {{ strtoupper(substr($store->name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        &copy; {{ date('Y') }} {{ $store->name }} — {{ __('storefront.powered_by') }} <span class="font-semibold store-text-primary">Edzeery</span>
                    </span>
                </div>
                <div class="flex items-center gap-4 text-sm text-gray-400 dark:text-gray-500">
                    <a href="/" class="hover:text-gray-600 dark:hover:text-gray-300 transition">{{ __('storefront.back_to_store') }}</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts

    @if (session('swal.type'))
        <div data-sw="{{ session('swal.type') }}"
             data-sw-title="{{ session('swal.title', '') }}"
             data-sw-message="{{ session('swal.message', '') }}" hidden></div>
    @elseif (session('success'))
        <div data-sw="success" data-sw-message="{{ session('success') }}" hidden></div>
    @elseif (session('error'))
        <div data-sw="error" data-sw-message="{{ session('error') }}" hidden></div>
    @endif

</body>

</html>
