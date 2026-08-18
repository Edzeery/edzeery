<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use function Livewire\Volt\with;

$store = currentStore();

with([
    'store' => $store,
    'user' => auth()->user(),
    'productCount' => $store
        ? Product::query()->where('store_id', currentStoreId())->count()
        : 0,
    'canViewProducts'    => canStore(StorePermissionEnum::PRODUCT_VIEW->value),
    'canViewBrands'      => canStore(StorePermissionEnum::PRODUCT_VIEW->value),
    'canViewCategories'  => canStore(StorePermissionEnum::PRODUCT_VIEW->value),
    'canViewOptions'     => canStore(StorePermissionEnum::PRODUCT_VIEW->value),
    'canViewVariants'    => canStore(StorePermissionEnum::PRODUCT_VIEW->value),
    'canViewInventories' => canStore(StorePermissionEnum::INVENTORY_VIEW->value),
    'canViewStockAlerts' => canStore(StorePermissionEnum::INVENTORY_VIEW->value),
    'canViewDebts'       => canStore(StorePermissionEnum::FINANCE_DEBT_VIEW->value),
]);
?>

<div class="edz-sidebar">
    <div class="edz-sidebar__brand">
        <span class="edz-sidebar__logo">E</span>
        <span class="edz-sidebar__brand-name">{{ config('app.name') }}</span>
    </div>

    @if ($store)
        <div class="edz-sidebar__store">
            <div class="edz-sidebar__store-avatar">
                {{ strtoupper(Str::substr($store?->name ?? 'S', 0, 1)) }}
            </div>
            <div class="edz-sidebar__store-meta">
                <p class="edz-sidebar__store-name">{{ $store?->name }}</p>
                <a href="{{ route('merchant.stores.index') }}" wire:navigate class="edz-sidebar__store-switch">
                    {{ __('merchant_panel.all_stores') }}
                </a>
            </div>
        </div>
    @endif

    <nav class="edz-sidebar__nav edz-scroll" aria-label="Store">
        @if ($store)
            <div class="edz-sidebar__group">
                <p class="edz-sidebar__group-title">{{ __('merchant_panel.store_management') }}</p>

                @php
                    $active = request()->routeIs('merchant.dashboard');
                    $href = route('merchant.dashboard', $store);
                @endphp
                <a href="{{ $href }}" wire:navigate
                   class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                    <x-edz.icon name="grid" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('titles.dashboard') }}</span>
                </a>

                @if ($canViewProducts)
                    @php
                        $active = request()->routeIs('merchant.products.*');
                        $href = route('merchant.products.index', $store);
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="package" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.products') }}</span>
                        <span class="edz-sidebar__badge">{{ $productCount }}</span>
                    </a>
                @endif

                @if ($canViewBrands)
                    @php
                        $active = request()->routeIs('merchant.brands.*');
                        $href = route('merchant.brands.index', $store);
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="grid" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.brands') }}</span>
                    </a>
                @endif

                @if ($canViewCategories)
                    @php
                        $active = request()->routeIs('merchant.categories.*');
                        $href = route('merchant.categories.index', $store);
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="menu" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.categories') }}</span>
                    </a>
                @endif

                @if ($canViewOptions)
                    @php
                        $active = request()->routeIs('merchant.options.*');
                        $href = route('merchant.options.index', $store);
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="search" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.options') }}</span>
                    </a>
                @endif

                @if ($canViewVariants)
                    @php
                        $active = request()->routeIs('merchant.variants.*');
                        $href = route('merchant.variants.index', $store);
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="package" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.variants') }}</span>
                    </a>
                @endif

                @if ($canViewInventories)
                    @php
                        $active = request()->routeIs('merchant.inventories.*');
                        $href = route('merchant.inventories.index', $store);
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="cart" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.inventories') }}</span>
                    </a>

                    @php
                        $active = request()->routeIs('merchant.inventory-movements.*');
                        $href = route('merchant.inventory-movements.index', $store);
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="refresh" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('titles.inventory_movements') }}</span>
                    </a>
                @endif

                @if ($canViewStockAlerts)
                    @php
                        $active = request()->routeIs('merchant.stock-alerts.*');
                        $href = route('merchant.stock-alerts.index', $store);
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="bell" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.stock_alerts') }}</span>
                    </a>
                @endif

                @php
                    $active = request()->routeIs('merchant.orders.*');
                    $href = route('merchant.orders.index', $store);
                @endphp
                <a href="{{ $href }}" wire:navigate
                   class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                    <x-edz.icon name="cart" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('merchant_panel.orders') }}</span>
                </a>

                @if ($canViewDebts)
                    @php
                        $active = request()->routeIs('merchant.debts.*');
                        $href = route('merchant.debts.index', $store);
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="credit-card" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('finance.debts') }}</span>
                    </a>
                @endif

                @php
                    $active = request()->routeIs('merchant.store-settings');
                    $href = route('merchant.store-settings', $store);
                @endphp
                <a href="{{ $href }}" wire:navigate
                   class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                    <x-edz.icon name="settings" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('merchant_panel.settings') }}</span>
                </a>

                @php
                    $active = request()->routeIs('merchant.storefront-settings');
                    $href = route('merchant.storefront-settings', $store);
                @endphp
                <a href="{{ $href }}" wire:navigate
                   class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                    <x-edz.icon name="storefront" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('merchant_panel.storefront') }}</span>
                </a>
            </div>

            <div class="edz-sidebar__group">
                <p class="edz-sidebar__group-title">{{ __('merchant_panel.team_group') }}</p>

                @php
                    $active = request()->routeIs('merchant.teams.*');
                    $href = route('merchant.teams.index', $store);
                @endphp
                <a href="{{ $href }}" wire:navigate
                   class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                    <x-edz.icon name="users" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('merchant_panel.my_teams') }}</span>
                </a>
            </div>
        @endif
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
