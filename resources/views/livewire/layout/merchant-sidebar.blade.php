<?php

use App\Enums\Store\StoreRoleEnum;
use function Livewire\Volt\with;

$user = auth()->user();
$stores = $user ? $user->stores()->distinct()->get() : collect();

$canCreateStore = $user
    ? ($user->hasAnyRoleForGuard([StoreRoleEnum::OWNER->value], 'merchant')
       || $user->storeMemberships()->count() === 0)
    : false;

with([
    'user' => $user,
    'stores' => $stores,
    'canCreateStore' => $canCreateStore,
]);
?>

<div class="edz-sidebar">
    <div class="edz-sidebar__brand">
        <span class="edz-sidebar__logo">E</span>
        <span class="edz-sidebar__brand-name">{{ config('app.name') }}</span>
    </div>

    <nav class="edz-sidebar__nav edz-scroll" aria-label="{{ __('merchant_panel.account') }}">
        <div class="edz-sidebar__group">
            <p class="edz-sidebar__group-title">{{ __('merchant_panel.account') }}</p>

            <a href="{{ route('account.stores') }}" wire:navigate
               class="edz-sidebar__link @if (request()->routeIs('account.stores')) edz-sidebar__link--active @endif">
                <x-edz.icon name="grid" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">{{ __('merchant_panel.my_stores') }}</span>
                @if ($stores->isNotEmpty())
                    <span class="edz-sidebar__badge">{{ $stores->count() }}</span>
                @endif
            </a>

            @if ($canCreateStore)
                <a href="{{ route('merchant.create-store') }}" wire:navigate
                   class="edz-sidebar__link @if (request()->routeIs('merchant.create-store')) edz-sidebar__link--active @endif">
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
                <p class="edz-sidebar__user-role">{{ $user?->email ?? '—' }}</p>
            </div>
        </a>
    </div>
</div>
