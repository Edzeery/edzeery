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
    'canViewBrands' => canStore(StorePermissionEnum::PRODUCT_VIEW->value),
    'canViewCategories' => canStore(StorePermissionEnum::PRODUCT_VIEW->value),
    'canViewOptions' => canStore(StorePermissionEnum::PRODUCT_VIEW->value),
    'canViewVariants' => canStore(StorePermissionEnum::PRODUCT_VIEW->value),
    'canViewInventories' => canStore(StorePermissionEnum::INVENTORY_VIEW->value),
    'canViewStockAlerts' => canStore(StorePermissionEnum::INVENTORY_VIEW->value),
    'canViewDebts' => canStore(StorePermissionEnum::FINANCE_DEBT_VIEW->value),
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

            @if ($canViewBrands)
                @php
                    $brandsActive = request()->routeIs('merchant.brands.*');
                    $brandsHref = $store ? route('merchant.brands.index', $store) : '#';
                @endphp
                <a href="{{ $brandsHref }}" wire:navigate
                   class="edz-sidebar__link @if ($brandsActive) edz-sidebar__link--active @endif">
                    <x-edz.icon name="grid" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">Brands</span>
                </a>
            @endif

            @if ($canViewCategories)
                @php
                    $categoriesActive = request()->routeIs('merchant.categories.*');
                    $categoriesHref = $store ? route('merchant.categories.index', $store) : '#';
                @endphp
                <a href="{{ $categoriesHref }}" wire:navigate
                   class="edz-sidebar__link @if ($categoriesActive) edz-sidebar__link--active @endif">
                    <x-edz.icon name="menu" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">Categories</span>
                </a>
            @endif

            @if ($canViewOptions)
                @php
                    $optionsActive = request()->routeIs('merchant.options.*');
                    $optionsHref = $store ? route('merchant.options.index', $store) : '#';
                @endphp
                <a href="{{ $optionsHref }}" wire:navigate
                   class="edz-sidebar__link @if ($optionsActive) edz-sidebar__link--active @endif">
                    <x-edz.icon name="search" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">Options</span>
                </a>
            @endif

            @if ($canViewVariants)
                @php
                    $variantsActive = request()->routeIs('merchant.variants.*');
                    $variantsHref = $store ? route('merchant.variants.index', $store) : '#';
                @endphp
                <a href="{{ $variantsHref }}" wire:navigate
                   class="edz-sidebar__link @if ($variantsActive) edz-sidebar__link--active @endif">
                    <x-edz.icon name="package" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">Variants</span>
                </a>
            @endif

            @if ($canViewInventories)
                @php
                    $inventoriesActive = request()->routeIs('merchant.inventories.*');
                    $inventoriesHref = $store ? route('merchant.inventories.index', $store) : '#';
                @endphp
                <a href="{{ $inventoriesHref }}" wire:navigate
                   class="edz-sidebar__link @if ($inventoriesActive) edz-sidebar__link--active @endif">
                    <x-edz.icon name="cart" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">Inventories</span>
                </a>
            @endif

            @if ($canViewStockAlerts)
                @php
                    $stockAlertsActive = request()->routeIs('merchant.stock-alerts.*');
                    $stockAlertsHref = $store ? route('merchant.stock-alerts.index', $store) : '#';
                @endphp
                <a href="{{ $stockAlertsHref }}" wire:navigate
                   class="edz-sidebar__link @if ($stockAlertsActive) edz-sidebar__link--active @endif">
                    <x-edz.icon name="bell" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">Stock Alerts</span>
                </a>
            @endif

            <a href="#" class="edz-sidebar__link opacity-50">
                <x-edz.icon name="cart" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">Orders</span>
            </a>

            @if ($canViewDebts)
                @php
                    $debtsActive = request()->routeIs('merchant.debts.*');
                    $debtsHref = $store ? route('merchant.debts.index', $store) : '#';
                @endphp
                <a href="{{ $debtsHref }}" wire:navigate
                   class="edz-sidebar__link @if ($debtsActive) edz-sidebar__link--active @endif">
                    <x-edz.icon name="credit-card" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('finance.debts') }}</span>
                </a>
            @endif

            <a href="#" class="edz-sidebar__link opacity-50">
                <x-edz.icon name="settings" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">Settings</span>
            </a>
        </div>

        @if ($store)
            <div class="edz-sidebar__group">
                <p class="edz-sidebar__group-title">Team</p>

                @php
                    $teamActive = request()->routeIs('merchant.teams.*');
                    $teamHref = $store ? route('merchant.teams.index', $store) : '#';
                @endphp
                <a href="{{ $teamHref }}" wire:navigate
                   class="edz-sidebar__link @if ($teamActive) edz-sidebar__link--active @endif">
                    <x-edz.icon name="users" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">My Teams</span>
                </a>
            </div>
        @endif
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
