@props([
    'title' => null,
    'description' => null,
    'context' => 'panel',
    'sidebar' => 'account',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="swal-i18n" content="{{ json_encode([
        'confirm_delete_title' => __('messages.action_confirm'),
        'confirm_delete' => __('messages.action_confirm_delete'),
        'confirm_delete_named' => __('messages.action_confirm_delete') . ' "{name}"?',
        'confirm_bulk_delete' => __('messages.ask_delete'),
        'delete' => __('buttons.delete'),
        'confirm' => __('buttons.confirm'),
        'cancel' => __('buttons.cancel'),
        'unsaved_title' => __('messages.unsaved_changes_title'),
        'unsaved_text' => __('messages.unsaved_changes_text'),
        'leave' => __('messages.leave'),
        'stay' => __('buttons.cancel'),
    ]) }}">

    <title>{{ $title ? $title . ' · ' . config('app.name') : config('app.name') }}</title>

    <script>
        (function(){var t=localStorage.getItem('edz-theme');if(t==='dark'||(!t&&window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches)){document.documentElement.classList.add('dark')}})()
    </script>
  <link rel="icon" href="{{ asset('img/icons/newlogo.ico') }}" type="image/x-icon" />
    @vite(['resources/css/app.scss', 'resources/js/panel.js'])
    @livewireStyles
</head>
<body class="edz-body">

    <div class="edz-shell"
         x-data
         :class="{ 'edz-shell--open': $store.shell.open, 'edz-shell--collapsed': $store.shell.collapsed }">

        @if ($sidebar === 'store')
            <livewire:layout.store-sidebar />
        @elseif ($sidebar === 'merchant')
            <livewire:layout.merchant-sidebar />
        @else
            <livewire:layout.account-sidebar />
        @endif

        <div class="edz-overlay"
             @click="$store.shell.close()"
             x-show="$store.shell.open"
             x-transition.opacity
             x-cloak></div>

        <div class="edz-shell__main">
            <livewire:layout.topbar />

            <main class="edz-shell__content">
                <div class="edz-shell__inner animate-fade-up">
                    @if ($context === 'store' && user() && ! app(\App\Domains\User\Services\SubscriptionGuardService::class)->hasActiveSubscription())
                        <div class="mb-6 rounded-lg border border-warning-200 bg-warning-50 px-5 py-3 text-sm text-warning-800 dark:border-warning-700 dark:bg-warning-950 dark:text-warning-300">
                            <div class="flex items-center gap-3">
                                <x-edz.icon name="exclamation-triangle" class="h-5 w-5 flex-shrink-0 text-warning-500" />
                                <span class="flex-1">{{ __('messages.subscription_expired_text') }}</span>
                                <a href="{{ route('account.billing') }}" wire:navigate
                                   class="edz-btn edz-btn--warning edz-btn--sm flex-shrink-0">
                                    {{ __('messages.go_to_billing') }}
                                </a>
                            </div>
                        </div>
                    @endif

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

    <livewire:layout.command-palette />

    @if (session('swal.type'))
        <div data-sw="{{ session('swal.type') }}"
             data-sw-title="{{ session('swal.title', '') }}"
             data-sw-message="{{ session('swal.message', '') }}" hidden></div>
    @elseif (session('success') || session('merchant.saved'))
        <div data-sw="success" data-sw-message="{{ session('success') ?: session('merchant.saved') }}" hidden></div>
    @elseif (session('error') || session('merchant.error'))
        <div data-sw="error" data-sw-message="{{ session('error') ?: session('merchant.error') }}" hidden></div>
    @elseif (session('status'))
        <div data-sw="success" data-sw-message="{{ session('status') }}" hidden></div>
    @endif
</body>
</html>
