@props([
    'title' => null,
    'description' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' · ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.scss', 'resources/js/panel.js'])
    @livewireStyles
</head>
<body class="antialiased bg-surface-bg text-ink">

    <div class="edz-shell"
         x-data
         :class="{ 'edz-shell--open': $store.shell.open, 'edz-shell--collapsed': $store.shell.collapsed }">

        <livewire:layout.sidebar />

        <div class="edz-overlay"
             @click="$store.shell.close()"
             x-show="$store.shell.open"
             x-transition.opacity
             x-cloak></div>

        <div class="edz-shell__main">
            <livewire:layout.topbar />

            <main class="edz-shell__content">
                <div class="edz-shell__inner">
                    @isset($header)
                        {{ $header }}
                    @else
                        @if ($title)
                            <div class="edz-page-head">
                                <div>
                                    @if ($title)
                                        <h1 class="edz-page-head__title">{{ $title }}</h1>
                                    @endif
                                    @if ($description)
                                        <p class="edz-page-head__subtitle">{{ $description }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endisset

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
