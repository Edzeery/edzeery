<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\ProductVariant;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('components.layouts.merchant');

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
        'out' => ['text' => 'OUT', 'class' => 'text-red-700 bg-red-100 dark:text-red-300 dark:bg-red-900/40'],
        'low' => ['text' => 'LOW', 'class' => 'text-yellow-700 bg-yellow-100 dark:text-yellow-300 dark:bg-yellow-900/40'],
        default => ['text' => 'IN', 'class' => 'text-green-700 bg-green-100 dark:text-green-300 dark:bg-green-900/40'],
    };
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">Stock alerts</h1>
            <p class="edz-page-head__subtitle">Variants running low or out of stock for {{ currentStore()?->name }}</p>
        </div>
    </div>

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title">Alerts</h2>
                <p class="text-sm text-ink-400">Out-of-stock and below-threshold variants</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="Search by SKU or product name…"
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-start text-xs uppercase tracking-wider text-gray-400">
                        <th class="px-4 py-3 text-start font-semibold">SKU</th>
                        <th class="px-4 py-3 text-start font-semibold">Product</th>
                        <th class="px-4 py-3 text-start font-semibold">Stock</th>
                        <th class="px-4 py-3 text-start font-semibold">Threshold</th>
                        <th class="px-4 py-3 text-start font-semibold">Status</th>
                        <th class="px-4 py-3 text-end font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->alerts as $variant)
                        <tr class="border-b border-gray-100 last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $variant->sku }}</td>
                            <td class="px-4 py-3 font-medium text-ink">{{ $variant->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    @if ($variant->stock <= 0) bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300
                                    @else bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300 @endif">
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
                                       class="edz-btn edz-btn--ghost edz-btn--sm">Adjust Stock</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">No stock alerts</p>
                                <p class="mt-1 text-sm text-ink-muted">All variants are sufficiently stocked.</p>
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
