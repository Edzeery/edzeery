<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use function Livewire\Volt\with;

$store = currentStore();

$withData = [
    'store' => $store,
    'productsOpen' => false,
];

if ($store) {
    $withData['productsOpen'] = request()->routeIs(
        'merchant.products.*',
        'merchant.brands.*',
        'merchant.categories.*',
        'merchant.options.*',
        'merchant.variants.*'
    );
    $withData['productCount'] = Product::query()->where('store_id', currentStoreId())->count();
    $withData['canViewProducts'] = canStore(StorePermissionEnum::PRODUCT_VIEW->value);
    $withData['canViewBrands'] = canStore(StorePermissionEnum::PRODUCT_VIEW->value);
    $withData['canViewCategories'] = canStore(StorePermissionEnum::PRODUCT_VIEW->value);
    $withData['canViewOptions'] = canStore(StorePermissionEnum::PRODUCT_VIEW->value);
    $withData['canViewVariants'] = canStore(StorePermissionEnum::PRODUCT_VIEW->value);
    $withData['canViewInventories'] = canStore(StorePermissionEnum::INVENTORY_VIEW->value);
    $withData['canViewStockAlerts'] = canStore(StorePermissionEnum::INVENTORY_VIEW->value);
    $withData['canViewDebts'] = canStore(StorePermissionEnum::FINANCE_DEBT_VIEW->value);
    $withData['canViewReturns'] = canStore(StorePermissionEnum::RETURNS_VERIFY_BARCODE->value);
}

with($withData);
?>

<div class="edz-sidebar"
     x-data="{ productsOpen: {{ $productsOpen ? 'true' : 'false' }} }"
     @mouseenter="$store.shell.setHovered(true)"
     @mouseleave="$store.shell.setHovered(false)"
     :class="{ 'edz-sidebar--hover': $store.shell.hovered }">
    <div class="edz-sidebar__brand">
        <span class="edz-sidebar__logo">E</span>
        <span class="edz-sidebar__brand-name">{{ config('app.name') }}</span>
        <button type="button" class="edz-sidebar__collapse"
                @click="$store.shell.toggleCollapse()"
                aria-label="{{ __('buttons.toggle_sidebar_collapse') }}" title="{{ __('buttons.toggle_sidebar_collapse') }}">
            <x-edz.icon x-show="!$store.shell.collapsed"
                        :name="app()->getLocale() === 'ar' ? 'chevron-right' : 'chevron-left'"
                        class="w-5 h-5" />
            <x-edz.icon x-show="$store.shell.collapsed" x-cloak
                        :name="app()->getLocale() === 'ar' ? 'chevron-left' : 'chevron-right'"
                        class="w-5 h-5" />
        </button>
    </div>

    @if ($store)
        <div class="edz-sidebar__store">
            <div class="edz-sidebar__store-avatar">
                {{ strtoupper(Str::substr($store->name, 0, 1)) }}
            </div>
            <div class="edz-sidebar__store-meta">
                <p class="edz-sidebar__store-name">{{ $store->name }}</p>
                <a href="{{ route('account.stores') }}" wire:navigate class="edz-sidebar__store-switch">
                    {{ __('merchant_panel.all_stores') }}
                </a>
            </div>
        </div>
        @if ($store->isPubliclyActive())
            <div class="edz-sidebar__store-link">
                <a href="{{ $store->public_url }}" target="_blank" rel="noopener noreferrer"
                   class="edz-sidebar__link">
                    <x-edz.icon name="external-link" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('storefront.visit_store') }}</span>
                </a>
            </div>
        @endif
    @endif

    <nav class="edz-sidebar__nav edz-scroll" aria-label="{{ __('merchant_panel.store_management') }}">
        <div class="edz-sidebar__group">
            <p class="edz-sidebar__group-title">{{ __('merchant_panel.store_management') }}</p>

            <a href="{{ route('merchant.dashboard', $store) }}" wire:navigate
               class="edz-sidebar__link @if (request()->routeIs('merchant.dashboard')) edz-sidebar__link--active @endif">
                <x-edz.icon name="grid" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">{{ __('titles.dashboard') }}</span>
            </a>
        </div>

        @if ($canViewProducts)
            <div class="edz-sidebar__group">
                <p class="edz-sidebar__group-title">{{ __('merchant_panel.products_group') }}</p>

                <button type="button"
                        @click="productsOpen = !productsOpen"
                        class="edz-sidebar__sub-toggle"
                        :class="{ 'edz-sidebar__sub-toggle--open': productsOpen }"
                        :aria-expanded="productsOpen.toString()"
                        aria-label="{{ __('merchant_panel.products') }}">
                    <x-edz.icon name="package" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('merchant_panel.products') }}</span>
                    <span class="edz-sidebar__badge">{{ $productCount }}</span>
                    <x-edz.icon x-show="!productsOpen"
                                :name="app()->getLocale() === 'ar' ? 'chevron-down' : 'chevron-down'"
                                class="edz-sidebar__sub-chevron w-4 h-4" />
                    <x-edz.icon x-show="productsOpen" x-cloak
                                :name="app()->getLocale() === 'ar' ? 'chevron-up' : 'chevron-up'"
                                class="edz-sidebar__sub-chevron w-4 h-4" />
                </button>

                <div class="edz-sidebar__sub" x-show="productsOpen" x-cloak>
                    <a href="{{ route('merchant.products.index', $store) }}" wire:navigate
                       class="edz-sidebar__sub-link @if (request()->routeIs('merchant.products.*')) edz-sidebar__sub-link--active @endif">
                        <x-edz.icon name="package" class="edz-sidebar__icon edz-sidebar__sub-icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.products') }}</span>
                    </a>

                    @if ($canViewBrands)
                        <a href="{{ route('merchant.brands.index', $store) }}" wire:navigate
                           class="edz-sidebar__sub-link @if (request()->routeIs('merchant.brands.*')) edz-sidebar__sub-link--active @endif">
                            <x-edz.icon name="grid" class="edz-sidebar__icon edz-sidebar__sub-icon" />
                            <span class="edz-sidebar__label">{{ __('merchant_panel.brands') }}</span>
                        </a>
                    @endif

                    @if ($canViewCategories)
                        <a href="{{ route('merchant.categories.index', $store) }}" wire:navigate
                           class="edz-sidebar__sub-link @if (request()->routeIs('merchant.categories.*')) edz-sidebar__sub-link--active @endif">
                            <x-edz.icon name="menu" class="edz-sidebar__icon edz-sidebar__sub-icon" />
                            <span class="edz-sidebar__label">{{ __('merchant_panel.categories') }}</span>
                        </a>
                    @endif

                    @if ($canViewOptions)
                        <a href="{{ route('merchant.options.index', $store) }}" wire:navigate
                           class="edz-sidebar__sub-link @if (request()->routeIs('merchant.options.*')) edz-sidebar__sub-link--active @endif">
                            <x-edz.icon name="search" class="edz-sidebar__icon edz-sidebar__sub-icon" />
                            <span class="edz-sidebar__label">{{ __('merchant_panel.options') }}</span>
                        </a>
                    @endif

                    @if ($canViewVariants)
                        <a href="{{ route('merchant.variants.index', $store) }}" wire:navigate
                           class="edz-sidebar__sub-link @if (request()->routeIs('merchant.variants.*')) edz-sidebar__sub-link--active @endif">
                            <x-edz.icon name="package" class="edz-sidebar__icon edz-sidebar__sub-icon" />
                            <span class="edz-sidebar__label">{{ __('merchant_panel.variants') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if ($canViewInventories || $canViewStockAlerts)
            <div class="edz-sidebar__group">
                <p class="edz-sidebar__group-title">{{ __('merchant_panel.inventory_group') }}</p>

                @if ($canViewInventories)
                    <a href="{{ route('merchant.inventories.index', $store) }}" wire:navigate
                       class="edz-sidebar__link @if (request()->routeIs('merchant.inventories.*')) edz-sidebar__link--active @endif">
                        <x-edz.icon name="cart" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.inventories') }}</span>
                    </a>

                    <a href="{{ route('merchant.inventory-movements.index', $store) }}" wire:navigate
                       class="edz-sidebar__link @if (request()->routeIs('merchant.inventory-movements.*')) edz-sidebar__link--active @endif">
                        <x-edz.icon name="refresh" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('titles.inventory_movements') }}</span>
                    </a>
                @endif

                @if ($canViewStockAlerts)
                    <a href="{{ route('merchant.stock-alerts.index', $store) }}" wire:navigate
                       class="edz-sidebar__link @if (request()->routeIs('merchant.stock-alerts.*')) edz-sidebar__link--active @endif">
                        <x-edz.icon name="bell" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.stock_alerts') }}</span>
                    </a>
                @endif
            </div>
        @endif

        <div class="edz-sidebar__group">
            <p class="edz-sidebar__group-title">{{ __('merchant_panel.operations_group') }}</p>

            <a href="{{ route('merchant.orders.index', $store) }}" wire:navigate
               class="edz-sidebar__link @if (request()->routeIs('merchant.orders.*')) edz-sidebar__link--active @endif">
                <x-edz.icon name="cart" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">{{ __('merchant_panel.orders') }}</span>
            </a>

            @if ($canViewReturns)
                <a href="{{ route('merchant.returns.index', $store) }}" wire:navigate
                   class="edz-sidebar__link @if (request()->routeIs('merchant.returns.*')) edz-sidebar__link--active @endif">
                    <x-edz.icon name="arrow-uturn-left" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('merchant_panel.returns') }}</span>
                </a>
            @endif

            <a href="{{ route('merchant.order-settings', $store) }}" wire:navigate
               class="edz-sidebar__link @if (request()->routeIs('merchant.order-settings')) edz-sidebar__link--active @endif">
                <x-edz.icon name="settings" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">{{ __('merchant_panel.order_settings') }}</span>
            </a>

            @if ($canViewDebts)
                <a href="{{ route('merchant.debts.index', $store) }}" wire:navigate
                   class="edz-sidebar__link @if (request()->routeIs('merchant.debts.*')) edz-sidebar__link--active @endif">
                    <x-edz.icon name="credit-card" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('finance.debts') }}</span>
                </a>
            @endif
        </div>

        <div class="edz-sidebar__group">
            <p class="edz-sidebar__group-title">{{ __('merchant_panel.team_group') }}</p>

            <a href="{{ route('merchant.teams.index', $store) }}" wire:navigate
               class="edz-sidebar__link @if (request()->routeIs('merchant.teams.*')) edz-sidebar__link--active @endif">
                <x-edz.icon name="users" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">{{ __('merchant_panel.my_teams') }}</span>
            </a>
        </div>

        <div class="edz-sidebar__group">
            <p class="edz-sidebar__group-title">{{ __('merchant_panel.store_group') }}</p>

            <a href="{{ route('merchant.store-settings', $store) }}" wire:navigate
               class="edz-sidebar__link @if (request()->routeIs('merchant.store-settings')) edz-sidebar__link--active @endif">
                <x-edz.icon name="settings" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">{{ __('merchant_panel.settings') }}</span>
            </a>

            <a href="{{ route('merchant.storefront-settings', $store) }}" wire:navigate
               class="edz-sidebar__link @if (request()->routeIs('merchant.storefront-settings')) edz-sidebar__link--active @endif">
                <x-edz.icon name="storefront" class="edz-sidebar__icon" />
                <span class="edz-sidebar__label">{{ __('merchant_panel.storefront') }}</span>
            </a>
        </div>
    </nav>

    <div class="edz-sidebar__footer">
        <a href="{{ route('account.profile') }}" wire:navigate class="edz-sidebar__user">
            <span class="edz-sidebar__user-avatar">{{ strtoupper(Str::substr(auth()->user()?->name ?? 'U', 0, 1)) }}</span>
            <div class="edz-sidebar__user-meta">
                <p class="edz-sidebar__user-name">{{ auth()->user()?->name ?? __('merchant_panel.guest') }}</p>
                <p class="edz-sidebar__user-role">{{ auth()->user()?->email ?? '—' }}</p>
            </div>
        </a>
    </div>
</div>
