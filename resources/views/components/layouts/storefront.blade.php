<!DOCTYPE html>
<html lang="{{ $lang ?? app()->getLocale() }}" dir="{{ $dir ?? 'ltr' }}" class="h-full scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? $store->name ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased min-h-screen flex flex-col">

    {{-- Top bar --}}
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                @if($store->logo ?? null)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}" class="h-9 w-9 rounded-full object-cover">
                @else
                    <div class="h-9 w-9 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr($store->name, 0, 1)) }}
                    </div>
                @endif
                <span class="font-semibold text-lg text-gray-900 dark:text-white">{{ $store->name }}</span>
            </a>

            <div class="flex items-center gap-4">
                {{-- Mini Cart Livewire component --}}
                @livewire('storefront.mini-cart')
            </div>
        </div>
    </header>

    {{-- Main content --}}
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-6 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} {{ $store->name }} — {{ __('Powered by') }} Edzeery
        </div>
    </footer>

</body>

</html>
