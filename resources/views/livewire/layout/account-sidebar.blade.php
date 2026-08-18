<?php

use function Livewire\Volt\with;

with([
    'user' => auth()->user(),
]);
?>

<div class="edz-sidebar">
    <div class="edz-sidebar__brand">
        <span class="edz-sidebar__logo">E</span>
        <span class="edz-sidebar__brand-name">{{ config('app.name') }}</span>
    </div>

    <nav class="edz-sidebar__nav edz-scroll" aria-label="Account">
        <div class="edz-sidebar__group">
            <p class="edz-sidebar__group-title">{{ __('merchant_panel.account') }}</p>

            @php
                $active = request()->routeIs('account.profile');
            @endphp
            <a href="{{ route('account.profile') }}" wire:navigate
               class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                <x-edz.icon name="user" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">{{ __('merchant_panel.profile') }}</span>
            </a>

            @php
                $active = request()->routeIs('account.billing');
            @endphp
            <a href="{{ route('account.billing') }}" wire:navigate
               class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                <x-edz.icon name="credit-card" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">{{ __('merchant_panel.billing') }}</span>
            </a>
        </div>
    </nav>

    <div class="edz-sidebar__footer">
        <div class="edz-sidebar__user">
            <span class="edz-sidebar__user-avatar">{{ strtoupper(Str::substr($user?->name ?? 'U', 0, 1)) }}</span>
            <div class="edz-sidebar__user-meta">
                <p class="edz-sidebar__user-name">{{ $user?->name ?? __('merchant_panel.guest') }}</p>
                <p class="edz-sidebar__user-role">{{ $user?->email ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
