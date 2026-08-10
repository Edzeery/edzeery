<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use function Livewire\Volt\layout;
use function Livewire\Volt\with;

layout('components.layouts.merchant');

with([
    'store' => currentStore(),
    'user' => auth()->user(),
    'productCount' => Product::query()
        ->where('store_id', currentStoreId())
        ->count(),
    'canViewProducts' => canStore(StorePermissionEnum::PRODUCT_VIEW->value),
]);
?>

<div class="edz-sidebar">
    <div class="edz-sidebar__brand">
        <span class="edz-sidebar__logo">E</span>
        <span class="edz-sidebar__brand-name">{{ config('app.name') }}</span>
    </div>

    <div class="border-b border-surface-border px-5 py-3">
        <div class="flex items-center gap-2">
            <div class="grid h-8 w-8 flex-none place-items-center rounded-lg bg-accent-600 text-sm font-bold text-white">
                {{ strtoupper(Str::substr($store?->name ?? 'S', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-ink">{{ $store?->name }}</p>
                <a href="{{ route('choose-store') }}" class="text-xs text-brand-600 hover:underline dark:text-brand-300">
                    Switch store
                </a>
            </div>
        </div>
    </div>

    <nav class="edz-sidebar__nav edz-scroll" aria-label="Merchant">
        <div class="edz-sidebar__group">
            <p class="edz-sidebar__group-title">Store management</p>

            @if ($canViewProducts)
                @php
                    $productsActive = request()->routeIs('merchant.products.*');
                    $productsHref = $store ? route('merchant.products.index', $store) : '#';
                @endphp
                <a href="{{ $productsHref }}" wire:navigate
                   class="edz-sidebar__link @if ($productsActive) edz-sidebar__link--active @endif">
                    <x-edz.icon name="package" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">Products</span>
                    <span class="edz-sidebar__label ms-auto rounded-full bg-brand-600/10 px-2 py-0.5 text-xs font-semibold text-brand-700 dark:text-brand-300">{{ $productCount }}</span>
                </a>
            @endif

            <a href="#" class="edz-sidebar__link opacity-50">
                <x-edz.icon name="cart" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">Orders</span>
            </a>

            <a href="#" class="edz-sidebar__link opacity-50">
                <x-edz.icon name="settings" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">Settings</span>
            </a>
        </div>
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
