<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\with;

layout('components.layouts.merchant');

with([
    'store' => currentStore(),
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
        <input type="search" placeholder="Search…" aria-label="Search">
    </div>

    <div class="edz-topbar__actions">
        <a href="{{ route('choose-store') }}" class="hidden items-center gap-1.5 rounded-lg px-2 py-1.5 text-sm text-ink-soft hover:bg-surface-secondary sm:inline-flex">
            <x-edz.icon name="menu" class="w-4 h-4" />
            <span class="max-w-40 truncate font-medium">{{ $store?->name }}</span>
        </a>

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

        <button type="button" class="edz-topbar__icon-btn" aria-label="Notifications">
            <x-edz.icon name="bell" class="w-5 h-5" />
            <span class="edz-topbar__dot"></span>
        </button>
    </div>
</header>
