<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\ProductVariant;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('components.layouts.store');

state([
    'search' => '',
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::INVENTORY_VIEW->value), 403);
});

$alerts = computed(function () {
    return ProductVariant::query()
        ->where('store_id', currentStoreId())
        ->where(function ($q) {
            $q->where('stock', '<=', 0)
                ->orWhereColumn('stock', '<=', 'low_stock_threshold');
        })
        ->with('product')
        ->when($this->search !== '', function ($query) {
            $query->where(function ($q) {
                $q->where('sku', 'like', '%'.$this->search.'%')
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', '%'.$this->search.'%'));
            });
        })
        ->orderBy('stock', 'asc')
        ->paginate(15);
});

$statusBadge = function (ProductVariant $variant): array {
    return match ($variant->stockStatus()) {
        'out' => ['text' => __('inventories.out_of_stock'), 'class' => 'text-danger-fg-strong bg-danger-surface-strong'],
        'low' => ['text' => __('inventories.low_stock'), 'class' => 'text-warning-fg-strong bg-warning-surface-strong'],
        default => ['text' => 'IN', 'class' => 'text-success-fg-strong bg-success-surface-strong'],
    };
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">{{ __('stock_alerts.title') }}</h1>
            <p class="edz-page-head__subtitle">{{ __('stock_alerts.subtitle', ['store' => currentStore()?->name]) }}</p>
        </div>
    </div>

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title">{{ __('stock_alerts.list_title') }}</h2>
                <p class="text-sm text-ink-400">{{ __('stock_alerts.list_subtitle') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="{{ __('stock_alerts.search_placeholder') }}"
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="px-4 py-3 text-start font-semibold">{{ __('stock_alerts.sku') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('stock_alerts.product') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('stock_alerts.stock_col') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('stock_alerts.threshold') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('stock_alerts.status') }}</th>
                        <th class="px-4 py-3 text-end font-semibold">{{ __('stock_alerts.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->alerts as $variant)
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $variant->sku }}</td>
                            <td class="px-4 py-3 font-medium text-ink">{{ $variant->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    @if ($variant->stock <= 0) bg-danger-surface-strong text-danger-fg-strong
                                    @else bg-warning-surface-strong text-warning-fg-strong @endif">
                                    {{ $variant->stock }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-ink-muted">{{ $variant->low_stock_threshold }}</td>
                            <td class="px-4 py-3">
                                @php $badge = $this->statusBadge($variant); @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge['class'] }}">
                                    {{ $badge['text'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('merchant.variants.index', currentStore()) }}" wire:navigate
                                       class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('inventories.adjust_stock') }}</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">{{ __('stock_alerts.no_alerts') }}</p>
                                <p class="mt-1 text-sm text-ink-muted">{{ __('stock_alerts.all_sufficient') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->alerts->hasPages())
            <div class="border-t border-surface-border px-4 py-3">
                {{ $this->alerts->links() }}
            </div>
        @endif
    </div>
</div>
