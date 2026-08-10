<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Brand;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\updated;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('components.layouts.merchant');

state([
    'search' => '',
    'brand_id' => '',
    'is_active' => '',
    'is_featured' => '',
    'created_at' => '',
    'selected' => [],
    'select_all' => false,
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::PRODUCT_VIEW->value), 403);
});

updated([
    'select_all' => function (string $name, $value): void {
        $this->selected = $value
            ? $this->products->pluck('id')->all()
            : [];
    },
]);

$products = computed(function () {
    return Product::query()
        ->where('store_id', currentStoreId())
        ->with(['primaryImage', 'brand', 'primaryCategory'])
        ->when($this->search !== '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('sku', 'like', '%'.$this->search.'%')
                    ->orWhere('barcode', 'like', '%'.$this->search.'%');
            });
        })
        ->when($this->brand_id !== '', fn ($q) => $q->where('brand_id', $this->brand_id))
        ->when($this->is_active !== '', fn ($q) => $q->where('is_active', filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)))
        ->when($this->is_featured !== '', fn ($q) => $q->where('is_featured', filter_var($this->is_featured, FILTER_VALIDATE_BOOLEAN)))
        ->when($this->created_at !== '', fn ($q) => $q->whereDate('created_at', $this->created_at))
        ->latest()
        ->paginate(15);
});

$brands = computed(fn () => Brand::query()
    ->where('store_id', currentStoreId())
    ->orderBy('name')
    ->pluck('name', 'id'));

$canCreate = fn () => canStore(StorePermissionEnum::PRODUCT_CREATE->value);
$canUpdate = fn () => canStore(StorePermissionEnum::PRODUCT_UPDATE->value);
$canDelete = fn () => canStore(StorePermissionEnum::PRODUCT_DELETE->value);

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

    Product::query()
        ->where('store_id', currentStoreId())
        ->whereIn('id', $this->selected)
        ->delete();

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

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">Products</h1>
            <p class="edz-page-head__subtitle">Manage the catalog of {{ currentStore()?->name }}</p>
        </div>
        @if ($this->canCreate())
            <a href="{{ route('merchant.products.create', currentStore()) }}" wire:navigate
               class="edz-btn edz-btn--primary edz-btn--sm">New product</a>
        @endif
    </div>

    @if (session('merchant.saved'))
        <div class="mb-6 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-800 dark:bg-success-950 dark:text-success-300">
            {{ session('merchant.saved') }}
        </div>
    @endif

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title">Products list</h2>
                <p class="text-sm text-ink-400">All products across your store</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <input type="search" class="edz-input" placeholder="Search by name, SKU or barcode…"
                       wire:model.live.debounce.300ms="search">
            </div>
            <div>
                <select class="edz-select" wire:model.live="brand_id">
                    <option value="">All brands</option>
                    @foreach ($this->brands as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select class="edz-select" wire:model.live="is_active">
                    <option value="">All statuses</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div>
                <select class="edz-select" wire:model.live="is_featured">
                    <option value="">All featured</option>
                    <option value="1">Featured</option>
                    <option value="0">Not featured</option>
                </select>
            </div>
            <div>
                <input type="date" class="edz-input" wire:model.live="created_at">
            </div>
        </div>

        @if (! empty($selected))
            <div class="flex flex-wrap items-center gap-2 border-b border-surface-border bg-brand-50/50 px-4 py-3 dark:bg-brand-950/30">
                <span class="text-sm font-medium text-ink">{{ count($selected) }} selected</span>
                <button type="button" class="edz-btn edz-btn--secondary edz-btn--sm" wire:click="activateSelected">Activate</button>
                <button type="button" class="edz-btn edz-btn--secondary edz-btn--sm" wire:click="deactivateSelected">Deactivate</button>
                <button type="button" class="edz-btn edz-btn--danger edz-btn--sm"
                        wire:click="deleteSelected"
                        wire:confirm="Delete the {{ count($selected) }} selected products?">Delete</button>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-start text-xs uppercase tracking-wider text-gray-400">
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox"
                                   wire:model.live="select_all"
                                   aria-label="Select all">
                        </th>
                        <th class="px-4 py-3 text-start font-semibold">Product</th>
                        <th class="px-4 py-3 text-start font-semibold">SKU</th>
                        <th class="px-4 py-3 text-start font-semibold">Variants</th>
                        <th class="px-4 py-3 text-start font-semibold">Brand</th>
                        <th class="px-4 py-3 text-start font-semibold">Category</th>
                        <th class="px-4 py-3 text-start font-semibold">Status</th>
                        <th class="px-4 py-3 text-start font-semibold">Created</th>
                        <th class="px-4 py-3 text-end font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->products as $product)
                        <tr class="border-b border-gray-100 last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ $product->id }}" aria-label="Select {{ $product->name }}">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $this->imageUrl($product) }}" alt="{{ $product->name }}"
                                         class="h-10 w-10 flex-none rounded-lg border border-surface-border object-cover">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-ink" title="Barcode: {{ $product->barcode }}">{{ $product->name }}</p>
                                        @if ($product->barcode)
                                            <p class="font-mono text-xs text-ink-muted">{{ $product->barcode }}</p>
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
                                    <x-merchant.status domain="product"
                                                       :status="$product->is_active ? 'active' : 'inactive'" />
                                    @if ($product->is_featured)
                                        <x-merchant.status domain="general" status="featured" />
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-muted">{{ $product->created_at?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('merchant.products.show', [currentStore(), $product]) }}" wire:navigate
                                       class="edz-btn edz-btn--ghost edz-btn--sm">View</a>
                                    @if ($this->canUpdate())
                                        <a href="{{ route('merchant.products.edit', [currentStore(), $product]) }}" wire:navigate
                                           class="edz-btn edz-btn--ghost edz-btn--sm">Edit</a>
                                    @endif
                                    @if ($this->canDelete())
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                wire:click="delete({{ $product->id }})"
                                                wire:confirm="Delete &quot;{{ $product->name }}&quot;?">Delete</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">No products found</p>
                                <p class="mt-1 text-sm text-ink-muted">Try adjusting your search or filters.</p>
                                @if ($this->canCreate())
                                    <a href="{{ route('merchant.products.create', currentStore()) }}" wire:navigate
                                       class="edz-btn edz-btn--primary edz-btn--sm mt-4">New product</a>
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
