<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\with;

layout('components.layouts.panel');

$isMerchant = request()->routeIs('merchant.*');

with([
    'context' => $isMerchant ? 'merchant' : 'panel',
    'store' => $isMerchant ? currentStore() : null,
]);
?>

<header class="edz-topbar">
    <button type="button" class="edz-topbar__trigger"
            @click="$store.shell.toggle()"
            aria-label="Toggle navigation">
        <x-edz.icon name="menu" class="w-5 h-5" />
    </button>

    <div class="edz-topbar__search">
        <x-edz.icon name="search" class="w-4 h-4" />
        <input type="search" placeholder="{{ __('buttons.search') }}…" aria-label="{{ __('buttons.search') }}">
        <kbd class="edz-topbar__kbd">⌘K</kbd>
    </div>

    <div class="edz-topbar__actions">
        @if ($context === 'merchant' && $store)
            <a href="{{ route('choose-store') }}" class="edz-topbar__store-link">
                <x-edz.icon name="grid" class="w-4 h-4" />
                <span class="edz-topbar__store-name">{{ $store?->name }}</span>
            </a>
            <span class="edz-topbar__divider" aria-hidden="true"></span>
        @endif

        <button type="button" class="edz-topbar__icon-btn"
                @click="$store.theme.toggle()"
                aria-label="Toggle theme">
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
