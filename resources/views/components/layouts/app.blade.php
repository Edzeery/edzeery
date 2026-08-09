<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $dir }}" x-data="{ dark: localStorage.getItem('darkMode') === 'true' }" x-init="if (dark) {
    document.documentElement.classList.add('dark')
}
$watch('dark', val => {
    localStorage.setItem('darkMode', val)
    val
        ?
        document.documentElement.classList.add('dark') :
        document.documentElement.classList.remove('dark')
})"
    :class="{ 'dark': dark }" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ isset($title) ? config('app.name', 'Edzeery') . ' | ' . $title : config('app.name', 'Edzeery') }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body
    class="
        min-h-screen
        bg-neutral-bg dark:bg-dark-bg
        text-neutral-text dark:text-dark-text
        antialiased
        transition-colors duration-300
    ">

    {{-- Navbar --}}
    <x-layouts.navbar />

    {{-- Main Content --}}
    <main class="pt-20 min-h-[calc(100vh-5rem)]">
        {{ $slot ?? '' }}
        @yield('content')
        {{-- Footer --}}
        <x-layouts.footer class="mt-0" />
    </main>
</body>

</html>
