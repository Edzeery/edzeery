<?php

use Illuminate\Support\Facades\Route;
use function Livewire\Volt\layout;
use function Livewire\Volt\with;

layout('components.layouts.panel');

with([
    'menu' => config('panel.menu'),
    'user' => auth()->user(),
]);
?>

<div class="edz-sidebar">
    <div class="edz-sidebar__brand">
        <span class="edz-sidebar__logo">E</span>
        <span class="edz-sidebar__brand-name">{{ config('app.name') }}</span>
    </div>

    <nav class="edz-sidebar__nav edz-scroll" aria-label="Main">
        @foreach ($menu as $group)
            <div class="edz-sidebar__group">
                <p class="edz-sidebar__group-title">{{ $group['group'] }}</p>

                @foreach ($group['items'] as $item)
                    @php
                        $href = Route::has($item['route']) ? route($item['route']) : '#';
                        $active = Route::has($item['route']) && request()->routeIs($item['route']);
                    @endphp

                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon :name="$item['icon']" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="edz-sidebar__footer">
        <div class="edz-sidebar__user">
            <span class="edz-sidebar__user-avatar">{{ strtoupper(Str::substr($user?->name ?? 'U', 0, 1)) }}</span>
            <div class="edz-sidebar__user-meta">
                <p class="edz-sidebar__user-name">{{ $user?->name ?? 'Guest' }}</p>
                <p class="edz-sidebar__user-role">{{ $user?->email ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
