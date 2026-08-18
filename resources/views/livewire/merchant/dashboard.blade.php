<?php

use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Team\StoreMembership;
use function Livewire\Volt\layout;
use function Livewire\Volt\with;

layout('components.layouts.store');

with([
    'userName' => user()?->name ?? __('merchant_panel.guest'),
    'totalProducts' => Product::query()->where('store_id', currentStoreId())->count(),
    'activeProducts' => Product::query()->where('store_id', currentStoreId())->where('is_active', true)->count(),
    'totalMembers' => StoreMembership::query()
        ->where('store_id', currentStoreId())
        ->where('user_id', '!=', user()->id)
        ->where('is_active', true)
        ->distinct()
        ->count('user_id') + 1,
    'lowStockCount' => ProductVariant::query()
        ->where('store_id', currentStoreId())
        ->where('stock', '>', 0)
        ->whereColumn('stock', '<=', 'low_stock_threshold')
        ->count(),
    'recentProducts' => Product::query()
        ->where('store_id', currentStoreId())
        ->latest()
        ->take(5)
        ->get(),
    'lowStockVariants' => ProductVariant::query()
        ->where('store_id', currentStoreId())
        ->where('stock', '>', 0)
        ->whereColumn('stock', '<=', 'low_stock_threshold')
        ->orderBy('stock')
        ->take(5)
        ->get(),
]);
?>

<div>
    <x-edz.page-header
        title="{{ __('titles.dashboard') }}"
        description="{{ __('dashboard.welcome_back') }}, {{ $userName }}.">
    </x-edz.page-header>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4 mb-6">
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-ink-400">{{ __('titles.products') }}</p>
                <span class="edz-badge edz-badge--success edz-badge--dot">
                    {{ $activeProducts }}
                </span>
            </div>
            <p class="mt-3 text-2xl font-bold tracking-tight text-ink">{{ $totalProducts }}</p>
            <p class="mt-1 text-xs text-ink-400">{{ __('dashboard.overview') }}</p>
        </div>

        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-ink-400">{{ __('titles.team') }}</p>
            </div>
            <p class="mt-3 text-2xl font-bold tracking-tight text-ink">{{ $totalMembers }}</p>
            <p class="mt-1 text-xs text-ink-400">{{ __('dashboard.total_memberships') }}</p>
        </div>

        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-ink-400">{{ __('titles.stock_alerts') }}</p>
                @if ($lowStockCount > 0)
                    <span class="edz-badge edz-badge--warning edz-badge--dot">
                        {{ $lowStockCount }}
                    </span>
                @endif
            </div>
            <p class="mt-3 text-2xl font-bold tracking-tight text-ink">{{ $lowStockCount }}</p>
            <p class="mt-1 text-xs text-ink-400">{{ __('dashboard.today_summary') }}</p>
        </div>

        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-ink-400">{{ __('titles.store') }}</p>
            </div>
            <p class="mt-3 text-lg font-bold tracking-tight text-ink truncate">{{ currentStore()?->name ?? '-' }}</p>
            <p class="mt-1 text-xs text-ink-400">
                <x-merchant.status domain="stores" :status="currentStore()?->status?->value" />
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <div class="lg:col-span-1 edz-card edz-card--padded">
            <h3 class="text-sm font-semibold text-ink mb-4">{{ __('dashboard.statistics') }}</h3>
            <div class="space-y-3">
                <a href="{{ route('merchant.products.create', currentStore()) }}"
                   class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-50 dark:hover:bg-ink-800 transition-colors">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-success-50 dark:bg-success-900/20 flex items-center justify-center text-success-600 dark:text-success-400">
                        <x-edz.icon name="plus" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-ink">{{ __('buttons.create') }} {{ __('titles.product') }}</p>
                        <p class="text-xs text-ink-400">{{ __('dashboard.today_summary') }}</p>
                    </div>
                </a>
                <a href="{{ route('merchant.products.index', currentStore()) }}"
                   class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-50 dark:hover:bg-ink-800 transition-colors">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-accent-50 dark:bg-accent-900/20 flex items-center justify-center text-accent-600 dark:text-accent-400">
                        <x-edz.icon name="eye" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-ink">{{ __('titles.products') }}</p>
                        <p class="text-xs text-ink-400">{{ __('dashboard.overview') }}</p>
                    </div>
                </a>
                <a href="{{ route('merchant.teams.index', currentStore()) }}"
                   class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-50 dark:hover:bg-ink-800 transition-colors">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-warning-50 dark:bg-warning-900/20 flex items-center justify-center text-warning-600 dark:text-warning-400">
                        <x-edz.icon name="users" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-ink">{{ __('titles.teams') }}</p>
                        <p class="text-xs text-ink-400">{{ __('dashboard.total_memberships') }}</p>
                    </div>
                </a>
                <a href="{{ route('merchant.stock-alerts.index', currentStore()) }}"
                   class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-50 dark:hover:bg-ink-800 transition-colors">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-danger-50 dark:bg-danger-900/20 flex items-center justify-center text-danger-600 dark:text-danger-400">
                        <x-edz.icon name="bell" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-ink">{{ __('titles.stock_alerts') }}</p>
                        <p class="text-xs text-ink-400">{{ $lowStockCount }} {{ __('dashboard.today_summary') }}</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="lg:col-span-1 edz-card edz-card--padded">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-ink">{{ __('titles.recent_activities') }}</h3>
            </div>
            @forelse ($recentProducts as $product)
                <div class="flex items-center gap-3 {{ !$loop->last ? 'pb-3 mb-3 border-b border-surface-100 dark:border-ink-800' : '' }}">
                    <div class="flex-shrink-0 w-8 h-8 rounded bg-surface-100 dark:bg-ink-800 flex items-center justify-center">
                        @if ($product->primaryImage)
                            <img src="{{ asset('storage/' . $product->primaryImage->path) }}"
                                 alt="{{ $product->name }}"
                                 class="w-8 h-8 rounded object-cover" />
                        @else
                            <x-edz.icon name="image" class="w-4 h-4 text-ink-400" />
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-ink truncate">{{ $product->name }}</p>
                        <p class="text-xs text-ink-400">{{ $product->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="edz-badge {{ $product->is_active ? 'edz-badge--success' : 'edz-badge--danger' }}">
                        {{ $product->is_active ? __('merchant_panel.active') : __('merchant_panel.inactive') }}
                    </span>
                </div>
            @empty
                <div class="text-center py-6">
                    <p class="text-sm text-ink-400">{{ __('dashboard.no_data') }}</p>
                </div>
            @endforelse
        </div>

        <div class="lg:col-span-1 edz-card edz-card--padded">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-ink">{{ __('titles.stock_alerts') }}</h3>
                @if ($lowStockCount > 0)
                    <a href="{{ route('merchant.stock-alerts.index', currentStore()) }}"
                       class="text-xs font-medium text-danger-600 hover:text-danger-500">
                        {{ __('buttons.view_all') }}
                    </a>
                @endif
            </div>
            @forelse ($lowStockVariants as $variant)
                <div class="flex items-center gap-3 {{ !$loop->last ? 'pb-3 mb-3 border-b border-surface-100 dark:border-ink-800' : '' }}">
                    <div class="flex-shrink-0 w-8 h-8 rounded bg-warning-50 dark:bg-warning-900/20 flex items-center justify-center">
                        <x-edz.icon name="bell" class="w-4 h-4 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-ink truncate">{{ $variant->product->name }}</p>
                        <p class="text-xs text-ink-400">
                            {{ $variant->optionValues->pluck('value')->implode(' / ') ?: __('titles.variants') }}
                        </p>
                    </div>
                    <span class="edz-badge edz-badge--warning">
                        {{ $variant->stock }}
                    </span>
                </div>
            @empty
                <div class="text-center py-6">
                    <p class="text-sm text-ink-400">{{ __('dashboard.no_data') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
