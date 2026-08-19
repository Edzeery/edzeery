<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex flex-col items-center justify-center bg-surface-bg">
    <div class="text-center px-6 animate-fade-up">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-warning-50 dark:bg-warning-950/30 mb-8">
            <span class="text-6xl font-bold text-warning-500 dark:text-warning-400 tracking-tighter">403</span>
        </div>
        <h1 class="text-2xl font-bold text-ink tracking-tight mb-3">{{ __('errors.403_title') }}</h1>
        <p class="text-ink-muted max-w-md mx-auto mb-8 leading-relaxed">{{ __('errors.403_description') }}</p>
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-brand-500 text-white font-semibold hover:bg-brand-600 transition-all duration-200 hover:shadow-lg">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('errors.go_home') }}
        </a>
    </div>
</body>
</html>
