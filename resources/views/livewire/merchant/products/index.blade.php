<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\updated;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('components.layouts.store');

state([
    'search' => '',
    'brand_id' => '',
    'category_id' => '',
    'is_active' => '',
    'is_featured' => '',
    'created_from' => '',
    'created_to' => '',
    'selected' => [],
    'select_all' => false,
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::PRODUCT_VIEW->value), 403);
});

updated([
    'select_all' => function (string $name, $value): void {
        $this->selected = $value ? $this->products->pluck('id')->all() : [];
    },
]);

$products = computed(function () {
    return Product::query()
        ->where('store_id', currentStoreId())
        ->with(['primaryImage', 'brand', 'primaryCategory'])
        ->when($this->search !== '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->brand_id !== '', fn($q) => $q->where('brand_id', $this->brand_id))
        ->when($this->category_id !== '', fn($q) => $q->where('primary_category_id', $this->category_id))
        ->when($this->is_active !== '', fn($q) => $q->where('is_active', filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)))
        ->when($this->is_featured !== '', fn($q) => $q->where('is_featured', filter_var($this->is_featured, FILTER_VALIDATE_BOOLEAN)))
        ->when($this->created_from !== '', fn($q) => $q->whereDate('created_at', '>=', $this->created_from))
        ->when($this->created_to !== '', fn($q) => $q->whereDate('created_at', '<=', $this->created_to))
        ->latest()
        ->paginate(15);
});

$brands = computed(fn() => Brand::query()->where('store_id', currentStoreId())->orderBy('name')->pluck('name', 'id'));

$categories = computed(function () {
    return Category::query()
        ->where('store_id', currentStoreId())
        ->orderBy('name')
        ->get(['id', 'parent_id', 'name']);
});

$activeFilterCount = function (): int {
    return collect([
        filled($this->brand_id),
        filled($this->category_id),
        filled($this->is_active),
        filled($this->is_featured),
        filled($this->created_from) || filled($this->created_to),
    ])->filter()->count();
};

$setFilter = function (string $key, ?string $value): void {
    $this->{$key} = $value ?? '';
    $this->page = 1;
};

$clearFilters = function (): void {
    $this->brand_id = '';
    $this->category_id = '';
    $this->is_active = '';
    $this->is_featured = '';
    $this->created_from = '';
    $this->created_to = '';
    $this->page = 1;
};

$canCreate = fn() => canStore(StorePermissionEnum::PRODUCT_CREATE->value);
$canUpdate = fn() => canStore(StorePermissionEnum::PRODUCT_UPDATE->value);
$canDelete = fn() => canStore(StorePermissionEnum::PRODUCT_DELETE->value);
$canExport = fn() => canStore(StorePermissionEnum::PRODUCT_VIEW->value);
$canImport = fn() => canStore(StorePermissionEnum::PRODUCT_CREATE->value);

$imageUrl = function (Product $product): string {
    $path = $product->primaryImage?->path;

    return $path ? Storage::disk('public')->url($path) : asset('img/icons/noimg.png');
};

$delete = function (Product $product): void {
    abort_unless(canStore(StorePermissionEnum::PRODUCT_DELETE->value), 403);

    $product->delete();
};

$deleteSelected = function (): void {
    abort_unless(canStore(StorePermissionEnum::PRODUCT_DELETE->value), 403);

    Product::query()->where('store_id', currentStoreId())->whereIn('id', $this->selected)->delete();

    $this->selected = [];
    $this->select_all = false;
};

$activateSelected = function (): void {
    abort_unless(canStore(StorePermissionEnum::PRODUCT_UPDATE->value), 403);

    Product::query()
        ->where('store_id', currentStoreId())
        ->whereIn('id', $this->selected)
        ->update(['is_active' => true]);

    $this->selected = [];
    $this->select_all = false;
};

$deactivateSelected = function (): void {
    abort_unless(canStore(StorePermissionEnum::PRODUCT_UPDATE->value), 403);

    Product::query()
        ->where('store_id', currentStoreId())
        ->whereIn('id', $this->selected)
        ->update(['is_active' => false]);

    $this->selected = [];
    $this->select_all = false;
};
?>

<div x-data="{ exportOpen: false, importOpen: false }">
    <x-edz.page-header :title="__('products.title')" :description="__('products.subtitle', ['store' => currentStore()?->name])">
        <x-slot:actions>
            @if ($this->canExport())
                <button type="button" @click="exportOpen = true"
                    class="edz-btn edz-btn--secondary edz-btn--sm">
                    <x-edz.icon name="download" class="h-4 w-4" />
                    {{ __('products.export') }}
                </button>
            @endif
            @if ($this->canImport())
                <button type="button" @click="importOpen = true"
                    class="edz-btn edz-btn--secondary edz-btn--sm">
                    <x-edz.icon name="upload" class="h-4 w-4" />
                    {{ __('products.import') }}
                </button>
            @endif
            @if ($this->canCreate())
                <a href="{{ route('merchant.products.create', currentStore()) }}" wire:navigate
                    class="edz-btn edz-btn--primary edz-btn--sm">{{ __('products.new_product') }}</a>
            @endif
        </x-slot:actions>
    </x-edz.page-header>

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title">{{ __('products.list_title') }}</h2>
                <p class="text-sm text-ink-500">{{ __('products.list_subtitle') }}</p>
            </div>
        </div>

        @include('livewire.merchant.products.index.partials.filter-bar')

        @if (!empty($selected))
            <div
                class="flex flex-wrap items-center gap-2 border-b border-surface-border bg-brand-surface px-4 py-3">
                <span
                    class="text-sm font-medium text-ink">{{ __('general.selected_count', ['count' => count($selected)]) }}</span>
                <button type="button" class="edz-btn edz-btn--secondary edz-btn--sm"
                    wire:click="activateSelected">{{ __('products.activate') }}</button>
                <button type="button" class="edz-btn edz-btn--secondary edz-btn--sm"
                    wire:click="deactivateSelected">{{ __('products.deactivate') }}</button>
                <button type="button" class="edz-btn edz-btn--danger edz-btn--sm" x-data
                    data-confirm-count="{{ count($selected) }}"
                    @click.prevent="(async () => { if (await EdzSwal.confirmBulkDelete(Number($el.dataset.confirmCount))) await $wire.deleteSelected() })()">{{ __('buttons.delete') }}</button>
            </div>
        @endif

        <div class="relative">
            <div wire:loading class="absolute inset-0 z-10 bg-surface/80 backdrop-blur-sm p-4 space-y-3 overflow-hidden"
                wire:target="search,brand_id,category_id,is_active,is_featured,created_from,created_to">
                @for ($i = 0; $i < 5; $i++)
                    <div class="flex items-center gap-3 py-2">
                        <x-edz.skeleton width="2.5rem" height="2.5rem" rounded="lg" />
                        <div class="flex-1 space-y-1.5">
                            <x-edz.skeleton width="{{ 40 + $i * 8 }}%" />
                            <x-edz.skeleton width="5rem" height="0.75rem" />
                        </div>
                        <x-edz.skeleton width="4rem" />
                        <x-edz.skeleton width="3rem" />
                        <x-edz.skeleton width="4rem" />
                        <x-edz.skeleton width="2.5rem" height="1.5rem" rounded="full" />
                        <x-edz.skeleton width="4rem" height="0.75rem" />
                        <x-edz.skeleton width="5rem" />
                    </div>
                @endfor
            </div>

            <div class="overflow-x-auto" wire:loading.class="opacity-40 pointer-events-none"
                wire:target="search,brand_id,category_id,is_active,is_featured,created_from,created_to">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-soft">
                            <th class="w-10 px-4 py-3">
                                <input type="checkbox" wire:model.live="select_all" aria-label="Select all">
                            </th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('table.product') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('table.sku') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('products.variants_label') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('table.brand') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('table.category') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('general.status') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('table.created') }}</th>
                            <th class="px-4 py-3 text-end font-semibold">{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->products as $product)
                            <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                                <td class="px-4 py-3">
                                    <input type="checkbox" wire:model.live="selected" value="{{ $product->id }}"
                                        aria-label="Select {{ $product->name }}">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $this->imageUrl($product) }}" alt="{{ $product->name }}"
                                            class="h-10 w-10 flex-none rounded-lg border border-surface-border object-cover">
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-ink"
                                                title="Barcode: {{ $product->barcode }}">
                                                <a href="{{ route('storefront.product', [currentStore()->slug, $product]) }}"
                                                    target="_blank" rel="noopener"
                                                    class="edz-btn edz-btn--ghost edz-btn--sm"
                                                    title="{{ __('merchant_panel.visit_store') }}">
                                                    {{ $product->name }}
                                                </a>

                                            </p>
@if ($product->barcode)
                                                <p class="font-mono text-xs text-ink-soft">{{ $product->barcode }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $product->sku }}</td>
                                <td class="px-4 py-3 text-ink-soft">
                                    {{ $product->hasVariants() ? 'Yes' : 'No' }}
                                </td>
                                <td class="px-4 py-3 text-ink-soft">{{ $product->brand?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-ink-soft">{{ $product->primaryCategory?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <x-merchant.status domain="product" :status="$product->is_active ? 'active' : 'inactive'" />
                                        @if ($product->is_featured)
                                            <x-merchant.status domain="general" status="featured" />
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-ink-soft">
                                    {{ $product->created_at?->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('storefront.product', [currentStore()->slug, $product]) }}"
                                            target="_blank" rel="noopener" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            title="{{ __('merchant_panel.visit_store') }}">
                                            <x-edz.icon name="external-link" class="w-4 h-4" />
                                        </a>
                                        <a href="{{ route('merchant.products.show', [currentStore(), $product]) }}"
                                            wire:navigate
                                            class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('buttons.view') }}</a>
                                        @if ($this->canUpdate())
                                            <a href="{{ route('merchant.products.edit', [currentStore(), $product]) }}"
                                                wire:navigate
                                                class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('buttons.edit') }}</a>
                                        @endif
                                        @if ($this->canDelete())
                                            <button type="button"
                                                class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                x-data
                                                data-delete-name="{{ $product->name }}"
                                                data-delete-id="{{ $product->id }}"
                                                @click.prevent="(async () => { if (await EdzSwal.confirmDelete($el.dataset.deleteName)) await $wire.delete($el.dataset.deleteId) })()">{{ __('buttons.delete') }}</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-16 text-center">
                                    <p class="text-sm font-medium text-ink-soft">{{ __('products.no_products') }}</p>
                                    <p class="mt-1 text-sm text-ink-muted">{{ __('products.try_adjusting') }}</p>
                                    @if ($this->canCreate())
                                        <a href="{{ route('merchant.products.create', currentStore()) }}"
                                            wire:navigate
                                            class="edz-btn edz-btn--primary edz-btn--sm mt-4">{{ __('products.new_product') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($this->products->hasPages())
                <div class="border-t border-surface-border px-4 py-3">
                    {{ $this->products->links() }}
                </div>
            @endif
        </div>
    </div>

    @include('livewire.merchant.products.index.export-import-modal')
</div>
