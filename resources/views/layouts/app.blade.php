<!DOCTYPE html>

<html lang="{{ $lang }}" dir="{{ $dir }}" class="h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | {{ config('app.name') }}</title>

    @vite(['resources/css/app.scss', 'resources/js/panel.js'])

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body class="edz-body" x-data="{
    loaded: true,
    init() {
        $store.shell.init();
    }
}">

    @php
        $isMerchant = request()->routeIs('merchant.*');
        $isCollapsed = $isMerchant && !$isExpanded;
    @endphp

    <div class="edz-shell"
         :class="{
            'edz-shell--collapsed': {{ $isCollapsed ? 'true' : 'false' }},
            'edz-shell--open': $store.shell.open
         }">

        @include('layouts.sidebar')

        <div class="edz-shell__main">
            @include('layouts.app-header')

            <main class="edz-shell__content">
                <div class="edz-shell__inner">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

</body>

@stack('scripts')

</html>
