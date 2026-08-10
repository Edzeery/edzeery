<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.merchant');

state([
    'product' => null,
]);

mount(function (Product $product): void {
    abort_unless($product->store_id === currentStoreId(), 404);
    abort_unless(canStore(StorePermissionEnum::PRODUCT_VIEW->value), 403);

    $this->product = $product;
});

$variants = computed(fn () => $this->product->variants()->with('optionValues.option')->get());

$images = computed(fn () => $this->product->images);

$singleVariant = computed(fn () => $this->product->hasVariants()
    ? null
    : ($this->product->variants()->where('is_default', true)->first() ?? $this->product->variants()->first()));

$imageUrl = fn (string $path): string => Storage::disk('public')->url($path);

$canUpdate = fn () => canStore(StorePermissionEnum::PRODUCT_UPDATE->value);
$canDelete = fn () => canStore(StorePermissionEnum::PRODUCT_DELETE->value);

$delete = function (Product $product): void {
    abort_unless(canStore(StorePermissionEnum::PRODUCT_DELETE->value), 403);

    $product->delete();

    session()->flash('merchant.saved', 'Product deleted.');
    $this->redirectRoute('merchant.products.index', currentStore());
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">{{ $product->name }}</h1>
            <p class="edz-page-head__subtitle">
                {{ $product->sku ? 'SKU: '.$product->sku : 'No SKU' }}
                @if ($product->barcode)
                    · Barcode: {{ $product->barcode }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('merchant.products.index', currentStore()) }}" wire:navigate
               class="edz-btn edz-btn--ghost">Back</a>
            @if ($this->canUpdate())
                <a href="{{ route('merchant.products.edit', [currentStore(), $product]) }}" wire:navigate
                   class="edz-btn edz-btn--primary">Edit</a>
            @endif
            @if ($this->canDelete())
                <button type="button" class="edz-btn edz-btn--danger"
                        wire:click="delete({{ $product->id }})"
                        wire:confirm="Delete &quot;{{ $product->name }}&quot;? This cannot be undone.">Delete</button>
            @endif
        </div>
    </div>

    @if (session('merchant.saved'))
        <div class="mb-6 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-800 dark:bg-success-950 dark:text-success-300">
            {{ session('merchant.saved') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title">Media</h2>
                        <p class="text-sm text-ink-400">{{ $this->images->count() }} image(s)</p>
                    </div>
                </div>
                <div class="edz-card__body">
                    @if ($this->images->isNotEmpty())
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                            @foreach ($this->images as $image)
                                <div class="overflow-hidden rounded-lg border border-surface-border">
                                    <img src="{{ $this->imageUrl($image->path) }}" alt=""
                                         class="aspect-square w-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-ink-muted">No images yet.</p>
                    @endif
                </div>
            </div>

            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title">Description</h2>
                    </div>
                </div>
                <div class="edz-card__body space-y-4">
                    @if ($product->short_description)
                        <p class="text-sm font-medium text-ink">{{ $product->short_description }}</p>
                    @endif
                    @if ($product->description)
                        <div class="prose-sm prose max-w-none text-ink-soft">{!! $product->description !!}</div>
                    @else
                        <p class="text-sm text-ink-muted">No description.</p>
                    @endif
                </div>
            </div>

            @if ($product->hasVariants())
                <div class="edz-card">
                    <div class="edz-card__header">
                        <div>
                            <h2 class="edz-card__title">Variants</h2>
                            <p class="text-sm text-ink-400">{{ $this->variants->count() }} combination(s)</p>
                        </div>
                    </div>
                    <div class="edz-card__body p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 text-start text-xs uppercase tracking-wider text-gray-400">
                                        <th class="px-4 py-3 text-start font-semibold">Variant</th>
                                        <th class="px-4 py-3 text-start font-semibold">SKU</th>
                                        <th class="px-4 py-3 text-start font-semibold">Price</th>
                                        <th class="px-4 py-3 text-start font-semibold">Cost</th>
                                        <th class="px-4 py-3 text-start font-semibold">Stock</th>
                                        <th class="px-4 py-3 text-start font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->variants as $variant)
                                        <tr class="border-b border-gray-100 last:border-0">
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-ink">{{ $variant->name }}</p>
                                                @if ($variant->barcode)
                                                    <p class="font-mono text-xs text-ink-muted">{{ $variant->barcode }}</p>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $variant->sku }}</td>
                                            <td class="px-4 py-3 text-ink-soft">{{ number_format($variant->price, 2) }}</td>
                                            <td class="px-4 py-3 text-ink-soft">{{ number_format($variant->cost_price, 2) }}</td>
                                            <td class="px-4 py-3">
                                                <span class="text-ink-soft">{{ $variant->stock }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <x-merchant.status domain="product"
                                                                   :status="$variant->is_active ? 'active' : 'inactive'" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="edz-card">
                <div class="edz-card__header">
                    <h2 class="edz-card__title">Details</h2>
                </div>
                <div class="edz-card__body grid grid-cols-1 gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <x-merchant.status domain="product" :status="$product->is_active ? 'active' : 'inactive'" />
                        @if ($product->is_featured)
                            <x-merchant.status domain="general" status="featured" />
                        @endif
                    </div>
                    <dl class="grid grid-cols-1 gap-3">
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted">Type</dt>
                            <dd class="font-medium text-ink">{{ $product->hasVariants() ? 'Variable' : 'Simple' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted">Brand</dt>
                            <dd class="font-medium text-ink">{{ $product->brand?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted">Unit</dt>
                            <dd class="font-medium text-ink">{{ $product->unit ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted">Primary category</dt>
                            <dd class="font-medium text-ink">{{ $product->primaryCategory?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted">Created</dt>
                            <dd class="font-medium text-ink">{{ $product->created_at?->format('Y-m-d') }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted">Updated</dt>
                            <dd class="font-medium text-ink">{{ $product->updated_at?->format('Y-m-d') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if (! $product->hasVariants() && $this->singleVariant)
                <div class="edz-card">
                    <div class="edz-card__header">
                        <h2 class="edz-card__title">Pricing &amp; stock</h2>
                    </div>
                    <div class="edz-card__body grid grid-cols-1 gap-3 text-sm">
                        @php
                            $sv = $this->singleVariant;
                            $svProfit = $sv->price - $sv->cost_price;
                            $svMargin = $sv->price > 0 ? round(($svProfit / $sv->price) * 100, 1) : null;
                        @endphp
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted">Price</dt>
                            <dd class="font-semibold text-ink">{{ number_format($sv->price, 2) }}</dd>
                        </div>
                        @if ($sv->compare_price)
                            <div class="flex justify-between gap-2">
                                <dt class="text-ink-muted">Compare at</dt>
                                <dd class="font-medium text-ink line-through">{{ number_format($sv->compare_price, 2) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted">Cost</dt>
                            <dd class="font-medium text-ink">{{ number_format($sv->cost_price, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted">Profit / margin</dt>
                            <dd class="font-medium text-ink">
                                {{ number_format($svProfit, 2) }}
                                <span class="text-ink-muted">({{ $svMargin !== null ? $svMargin.'%' : '—' }})</span>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted">Stock</dt>
                            <dd class="font-medium text-ink">{{ $sv->stock }}</dd>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
