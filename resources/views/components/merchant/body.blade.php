@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      x-data="{ dark: localStorage.getItem('darkMode') === 'true' }"
      x-init="
        if(dark){ document.documentElement.classList.add('dark') }
        $watch('dark', val => {
            localStorage.setItem('darkMode', val)
            val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')
        })
      "
      class="scroll-smooth"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? config('app.name') . ' | ' . $title : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-surface-bg text-ink antialiased transition-colors duration-300">

    {{-- Layout --}}
    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <x-merchant.sidebar />

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col">

            {{-- Topbar --}}
            <x-merchant.topbar />

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-6 bg-surface-bg sm:ml-64">
                @if ($title)
                    <h1 class="text-2xl font-semibold text-ink mb-6">
                        {{ $title }}
                    </h1>
                @endif

                {{ $slot }}
            </main>

        </div>

    </div>

</body>
</html>
