<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Route;
use function Livewire\Volt\layout;
use function Livewire\Volt\with;

layout('components.layouts.panel');

with([
    'context' => request()->routeIs('merchant.*') ? 'merchant' : 'panel',
    'menu' => config('panel.menu'),
    'user' => auth()->user(),
    'store' => request()->routeIs('merchant.*') ? currentStore() : null,
    'productCount' => request()->routeIs('merchant.*')
        ? Product::query()->where('store_id', currentStoreId())->count()
        : 0,
    'canViewProducts'    => request()->routeIs('merchant.*') ? canStore(StorePermissionEnum::PRODUCT_VIEW->value) : false,
    'canViewBrands'      => request()->routeIs('merchant.*') ? canStore(StorePermissionEnum::PRODUCT_VIEW->value) : false,
    'canViewCategories'  => request()->routeIs('merchant.*') ? canStore(StorePermissionEnum::PRODUCT_VIEW->value) : false,
    'canViewOptions'     => request()->routeIs('merchant.*') ? canStore(StorePermissionEnum::PRODUCT_VIEW->value) : false,
    'canViewVariants'    => request()->routeIs('merchant.*') ? canStore(StorePermissionEnum::PRODUCT_VIEW->value) : false,
    'canViewInventories' => request()->routeIs('merchant.*') ? canStore(StorePermissionEnum::INVENTORY_VIEW->value) : false,
    'canViewStockAlerts' => request()->routeIs('merchant.*') ? canStore(StorePermissionEnum::INVENTORY_VIEW->value) : false,
    'canViewDebts'       => request()->routeIs('merchant.*') ? canStore(StorePermissionEnum::FINANCE_DEBT_VIEW->value) : false,
]);
?>

<div class="edz-sidebar">
    <div class="edz-sidebar__brand">
        <span class="edz-sidebar__logo">E</span>
        <span class="edz-sidebar__brand-name">{{ config('app.name') }}</span>
    </div>

    {{-- Merchant: Store info block --}}
    @if ($context === 'merchant' && $store)
        <div class="edz-sidebar__store">
            <div class="edz-sidebar__store-avatar">
                {{ strtoupper(Str::substr($store?->name ?? 'S', 0, 1)) }}
            </div>
            <div class="edz-sidebar__store-meta">
                <p class="edz-sidebar__store-name">{{ $store?->name }}</p>
                <a href="{{ route('choose-store') }}" class="edz-sidebar__store-switch">
                    {{ __('merchant_panel.switch_store') }}
                </a>
            </div>
        </div>
    @endif

    <nav class="edz-sidebar__nav edz-scroll" aria-label="Main">
        {{-- Panel: Data-driven from config --}}
        @if ($context === 'panel')
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
        @endif

        {{-- Merchant: Permission-based menu --}}
        @if ($context === 'merchant')
            <div class="edz-sidebar__group">
                <p class="edz-sidebar__group-title">{{ __('merchant_panel.store_management') }}</p>

                @php
                    $active = request()->routeIs('merchant.dashboard');
                    $href = $store ? route('merchant.dashboard', $store) : '#';
                @endphp
                <a href="{{ $href }}" wire:navigate
                   class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                    <x-edz.icon name="grid" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('titles.dashboard') }}</span>
                </a>

                @php
                    $active = request()->routeIs('merchant.stores.*');
                    $href = route('merchant.stores.index');
                @endphp
                <a href="{{ $href }}" wire:navigate
                   class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                    <x-edz.icon name="grid" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('titles.stores') }}</span>
                </a>

                @if ($canViewProducts)
                    @php
                        $active = request()->routeIs('merchant.products.*');
                        $href = $store ? route('merchant.products.index', $store) : '#';
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
                        $href = $store ? route('merchant.brands.index', $store) : '#';
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
                        $href = $store ? route('merchant.categories.index', $store) : '#';
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
                        $href = $store ? route('merchant.options.index', $store) : '#';
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
                        $href = $store ? route('merchant.variants.index', $store) : '#';
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
                        $href = $store ? route('merchant.inventories.index', $store) : '#';
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="cart" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.inventories') }}</span>
                    </a>

                    @php
                        $active = request()->routeIs('merchant.inventory-movements.*');
                        $href = $store ? route('merchant.inventory-movements.index', $store) : '#';
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
                        $href = $store ? route('merchant.stock-alerts.index', $store) : '#';
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="bell" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.stock_alerts') }}</span>
                    </a>
                @endif

                <a href="#" class="edz-sidebar__link edz-sidebar__link--disabled">
                    <x-edz.icon name="cart" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('merchant_panel.orders') }}</span>
                </a>

                @if ($canViewDebts)
                    @php
                        $active = request()->routeIs('merchant.debts.*');
                        $href = $store ? route('merchant.debts.index', $store) : '#';
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="credit-card" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('finance.debts') }}</span>
                    </a>
                @endif

                <a href="#" class="edz-sidebar__link edz-sidebar__link--disabled">
                    <x-edz.icon name="settings" class="edz-sidebar__icon" />
                    <span class="edz-sidebar__label">{{ __('merchant_panel.settings') }}</span>
                </a>
            </div>

            @if ($store)
                <div class="edz-sidebar__group">
                    <p class="edz-sidebar__group-title">{{ __('merchant_panel.team_group') }}</p>

                    @php
                        $active = request()->routeIs('merchant.teams.*');
                        $href = $store ? route('merchant.teams.index', $store) : '#';
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="edz-sidebar__link @if ($active) edz-sidebar__link--active @endif">
                        <x-edz.icon name="users" class="edz-sidebar__icon" />
                        <span class="edz-sidebar__label">{{ __('merchant_panel.my_teams') }}</span>
                    </a>
                </div>
            @endif
        @endif
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
