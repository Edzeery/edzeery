<?php

use function Livewire\Volt\with;

$store = currentStore();

with([
    'store' => $store,
]);
?>

<header class="edz-topbar">
    <button type="button" class="edz-topbar__trigger" @click="$store.shell.toggle()"
        aria-label="{{ __('buttons.toggle_navigation') }}">
        <x-edz.icon name="menu" class="w-5 h-5" />
    </button>

    <div class="edz-topbar__search" wire:click="$dispatch('command-palette-toggle')">
        <x-edz.icon name="search" class="w-4 h-4" />
        <span class="text-sm text-ink-muted pointer-events-none">{{ __('buttons.search') }}…</span>
        <kbd class="edz-topbar__kbd">⌘K</kbd>
    </div>

    <div class="edz-topbar__actions">
        @if ($store)
            <a href="{{ route('account.stores') }}" wire:navigate class="edz-topbar__store-link">
                <x-edz.icon name="grid" class="w-4 h-4" />
                <span class="edz-topbar__store-name">{{ $store?->name }}</span>
            </a>
            <span class="edz-topbar__divider" aria-hidden="true"></span>
        @endif

        <button type="button" class="edz-topbar__icon-btn" @click="$store.theme.toggle()"
            aria-label="{{ __('buttons.toggle_theme') }}">
            <template x-if="$store.theme.theme === 'dark'">
                <x-edz.icon name="sun" class="w-5 h-5" />
            </template>
            <template x-if="$store.theme.theme === 'light'">
                <x-edz.icon name="moon" class="w-5 h-5" />
            </template>
        </button>

        <span class="edz-topbar__divider" aria-hidden="true"></span>

        <x-edz.notification-dropdown />

        <span class="edz-topbar__divider" aria-hidden="true"></span>

        <x-edz.user-dropdown />
    </div>
</header>
