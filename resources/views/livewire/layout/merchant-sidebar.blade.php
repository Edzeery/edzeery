<?php

use App\Domains\Merchant\Actions\GetStoreCardsAction;
use App\Models\Stores\Store;
use function Livewire\Volt\with;

with([
    'user' => auth()->user(),
    'stores' => user() ? user()->stores()->get() : collect(),
    'canCreate' => user()?->canCreateMultiStore() ?? false,
    'activeStoreId' => currentStoreId(),
]);
?>

<div class="edz-sidebar">
    <div class="edz-sidebar__brand">
        <span class="edz-sidebar__logo">E</span>
        <span class="edz-sidebar__brand-name">{{ config('app.name') }}</span>
    </div>

    <nav class="edz-sidebar__nav edz-scroll" aria-label="Merchant">
        <div class="edz-sidebar__group">
            <p class="edz-sidebar__group-title">{{ __('merchant_panel.my_stores') }}</p>

            @foreach ($stores as $store)
                <a href="{{ route('merchant.dashboard', $store) }}" wire:navigate
                   class="edz-sidebar__link @if ($activeStoreId === $store->id) edz-sidebar__link--active @endif">
                    <div class="flex items-center justify-center w-6 h-6 rounded-md bg-accent-100 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400 text-xs font-bold edz-sidebar__icon">
                        {{ strtoupper(mb_substr($store->name, 0, 1)) }}
                    </div>
                    <span class="edz-sidebar__label">{{ $store->name }}</span>
                </a>
            @endforeach

            @if ($stores->isEmpty())
                <p class="edz-sidebar__label text-xs text-ink-muted px-3 py-2">{{ __('merchant_panel.no_stores') }}</p>
            @endif

            @if ($canCreate)
                @php
                    $active = request()->routeIs('merchant.create-store');
                @endphp
                <a href="{{ route('merchant.create-store') }}" wire:navigate
                   class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                    <x-edz.icon name="plus" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('merchant_panel.create_store') }}</span>
                </a>
            @endif
        </div>
    </nav>

    <div class="edz-sidebar__footer">
        <a href="{{ route('account.profile') }}" wire:navigate class="edz-sidebar__user">
            <span class="edz-sidebar__user-avatar">{{ strtoupper(Str::substr($user?->name ?? 'U', 0, 1)) }}</span>
            <div class="edz-sidebar__user-meta">
                <p class="edz-sidebar__user-name">{{ $user?->name ?? __('merchant_panel.guest') }}</p>
                <p class="edz-sidebar__user-role">{{ __('merchant_panel.account') }}</p>
            </div>
        </a>
    </div>
</div>
