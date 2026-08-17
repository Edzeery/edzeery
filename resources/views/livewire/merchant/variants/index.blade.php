<?php

use App\Enums\Store\InventoryMovementType;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Services\InventoryService;
use App\Support\SkuGenerator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('components.layouts.merchant');

state([
    'search' => '',
    'selected' => [],
    'select_all' => false,
    'creating' => false,
    'editingId' => null,
    'adjustingId' => null,
    'historyId' => null,
    'product_id' => '',
    'sku' => '',
    'price' => null,
    'compare_price' => null,
    'cost_price' => null,
    'adjust_quantity' => '',
    'adjust_type' => '',
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::PRODUCT_VIEW->value), 403);
});

$variants = computed(function () {
    return ProductVariant::query()
        ->where('store_id', currentStoreId())
        ->with('product')
        ->when($this->search !== '', function ($query) {
            $query->where(function ($q) {
                $q->where('sku', 'like', '%'.$this->search.'%')
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', '%'.$this->search.'%'));
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);
});

$products = computed(fn () => Product::query()
    ->where('store_id', currentStoreId())
    ->orderBy('name')
    ->pluck('name', 'id'));

$manualTypes = computed(fn () => InventoryMovementType::manualOptions());

$canCreate = fn () => canStore(StorePermissionEnum::PRODUCT_CREATE->value);
$canUpdate = fn () => canStore(StorePermissionEnum::PRODUCT_UPDATE->value);
$canDelete = fn () => canStore(StorePermissionEnum::PRODUCT_DELETE->value);

$stockBadge = function (ProductVariant $variant): array {
    return match ($variant->stockStatus()) {
        'out' => ['text' => __('inventories.out_of_stock'), 'class' => 'text-danger-700 bg-danger-100 dark:text-danger-300 dark:bg-danger-900/40'],
        'low' => ['text' => __('inventories.low_stock'), 'class' => 'text-warning-700 bg-warning-100 dark:text-warning-300 dark:bg-warning-900/40'],
        default => ['text' => 'IN', 'class' => 'text-success-700 bg-success-100 dark:text-success-300 dark:bg-success-900/40'],
    };
};

$openCreate = function (): void {
    abort_unless($this->canCreate(), 403);

    $this->reset('editingId', 'adjustingId', 'historyId', 'product_id', 'sku', 'price', 'compare_price', 'cost_price');
    $this->creating = true;
};

$beginEdit = function (ProductVariant $variant): void {
    abort_unless($this->canUpdate(), 403);

    $this->creating = false;
    $this->editingId = $variant->id;
    $this->product_id = $variant->product_id;
    $this->sku = $variant->sku;
    $this->price = $variant->price;
    $this->compare_price = $variant->compare_price;
    $this->cost_price = $variant->cost_price;
};

$toggleAdjust = function (ProductVariant $variant): void {
    $this->adjustingId = $this->adjustingId === $variant->id ? null : $variant->id;
    $this->adjust_quantity = '';
    $this->adjust_type = '';
};

$toggleHistory = function (ProductVariant $variant): void {
    $this->historyId = $this->historyId === $variant->id ? null : $variant->id;
};

$movements = function (ProductVariant $variant): \Illuminate\Database\Eloquent\Collection {
    return $variant->inventoryMovements()
        ->with('user')
        ->orderBy('created_at', 'desc')
        ->limit(25)
        ->get();
};

$save = function (): void {
    abort_unless($this->canCreate() || $this->canUpdate(), 403);

    $validated = $this->validate([
        'product_id' => ['required', 'exists:products,id'],
        'sku' => ['required', 'string', 'max:255'],
        'price' => ['required', 'numeric', 'min:0'],
        'compare_price' => ['nullable', 'numeric', 'min:0'],
        'cost_price' => ['nullable', 'numeric', 'min:0'],
    ]);

    $product = Product::findOrFail($validated['product_id']);

    $data = [
        'product_id' => $validated['product_id'],
        'name' => $product->name,
        'sku' => $validated['sku'],
        'price' => $validated['price'],
        'compare_price' => $validated['compare_price'],
        'cost_price' => $validated['cost_price'],
    ];

    if ($this->editingId) {
        abort_unless($this->canUpdate(), 403);

        ProductVariant::query()
            ->where('store_id', currentStoreId())
            ->findOrFail($this->editingId)
            ->update($data);
    } else {
        abort_unless($this->canCreate(), 403);

        ProductVariant::create([
            'store_id' => currentStoreId(),
            'is_active' => true,
            ...$data,
        ]);
    }

    $this->reset('creating', 'editingId', 'product_id', 'sku', 'price', 'compare_price', 'cost_price');
};

$generateSku = function (): void {
    $product = Product::query()
        ->where('store_id', currentStoreId())
        ->find($this->product_id);

    if (! $product) {
        return;
    }

    $this->sku = SkuGenerator::variant(currentStore()->slug, $product->slug, []);
};

$cancelForm = function (): void {
    $this->reset('creating', 'editingId', 'product_id', 'sku', 'price', 'compare_price', 'cost_price');
};

$applyStock = function (ProductVariant $variant): void {
    abort_unless($this->canUpdate(), 403);

    $validated = $this->validate([
        'adjust_quantity' => ['required', 'integer', 'min:1'],
        'adjust_type' => ['required', Rule::in(array_keys(InventoryMovementType::manualOptions()))],
    ]);

    try {
        InventoryService::apply(
            $variant,
            (int) $validated['adjust_quantity'],
            InventoryMovementType::from($validated['adjust_type']),
            auth()->user()
        );

        session()->flash('merchant.saved', __('inventories.adjust_stock'));
    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->addError('adjust_quantity', $e->getMessage());
    }

    $this->reset('adjustingId', 'adjust_quantity', 'adjust_type');
};

$delete = function (ProductVariant $variant): void {
    abort_unless($this->canDelete(), 403);

    $variant->delete();
};

$deleteSelected = function (): void {
    abort_unless($this->canDelete(), 403);

    ProductVariant::query()
        ->where('store_id', currentStoreId())
        ->whereIn('id', $this->selected)
        ->delete();

    $this->reset('selected', 'select_all');
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">{{ __('variants.title') }}</h1>
            <p class="edz-page-head__subtitle">{{ __('variants.subtitle', ['store' => currentStore()?->name]) }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($this->canCreate())
                <button type="button" class="edz-btn edz-btn--primary" wire:click="openCreate">{{ __('variants.new_variant') }}</button>
            @endif
        </div>
    </div>

    @if (session('merchant.error'))
        <div class="mb-6 rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700 dark:border-danger-800 dark:bg-danger-950 dark:text-danger-300">
            {{ session('merchant.error') }}
        </div>
    @endif

    @if (session('merchant.saved'))
        <div class="mb-6 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-800 dark:bg-success-950 dark:text-success-300">
            {{ session('merchant.saved') }}
        </div>
    @endif

    @if ($creating || $editingId)
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title">{{ $editingId ? __('variants.edit_variant') : __('variants.new_variant') }}</h2>
                    <p class="text-sm text-ink-400">{{ $editingId ? __('variants.edit_variant_desc') : __('variants.new_variant_desc') }}</p>
                </div>
            </div>

            <form wire:submit="save" class="space-y-4 p-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-ink" for="variant-product">{{ __('variants.product') }}</label>
                        <select id="variant-product" class="edz-select @error('product_id') edz-input--error @enderror"
                                wire:model="product_id" @disabled($editingId)>
                            <option value="">{{ __('variants.select_product') }}</option>
                            @foreach ($this->products as $id => $productName)
                                <option value="{{ $id }}" @selected((string) $product_id === (string) $id)>{{ $productName }}</option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="variant-sku">{{ __('variants.sku') }}</label>
                        <input id="variant-sku" type="text" class="edz-input @error('sku') edz-input--error @enderror"
                               wire:model="sku" @disabled($editingId) placeholder="STORE-PRODUCT-SIZE">
                        @if (! $editingId)
                            <button type="button" class="mt-1 text-xs text-brand-600 hover:underline"
                                    wire:click="generateSku">{{ __('variants.generate_sku') }}</button>
                        @endif
                        @error('sku')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink" for="variant-price">{{ __('variants.price') }}</label>
                            <input id="variant-price" type="number" step="0.01" min="0" class="edz-input @error('price') edz-input--error @enderror"
                                   wire:model="price" placeholder="0.00">
                            @error('price')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink" for="variant-compare">{{ __('variants.compare_price') }}</label>
                            <input id="variant-compare" type="number" step="0.01" min="0" class="edz-input"
                                   wire:model="compare_price" placeholder="0.00">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink" for="variant-cost">{{ __('variants.cost_price') }}</label>
                            <input id="variant-cost" type="number" step="0.01" min="0" class="edz-input"
                                   wire:model="cost_price" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm">{{ __('buttons.save') }}</button>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="cancelForm">{{ __('buttons.cancel') }}</button>
                </div>
            </form>
        </div>
    @endif

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title">{{ __('variants.list_title') }}</h2>
                <p class="text-sm text-ink-400">{{ __('variants.list_subtitle') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input"                        placeholder="{{ __('variants.search_placeholder') }}"
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>

        @if (! empty($selected))
            <div class="flex flex-wrap items-center gap-2 border-b border-surface-border bg-brand-50/50 px-4 py-3 dark:bg-brand-950/30">
                <span class="text-sm font-medium text-ink">{{ __('general.selected_count', ['count' => count($selected)]) }}</span>
                <button type="button" class="edz-btn edz-btn--danger edz-btn--sm"
                        wire:click="deleteSelected"
                        wire:confirm="{{ __('general.confirm_delete_selected', ['count' => count($selected)]) }}">{{ __('buttons.delete') }}</button>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox"
                                   wire:model.live="select_all"
                                   aria-label="Select all">
                        </th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('variants.product') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('variants.sku') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('variants.price') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('variants.compare_price') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('variants.cost_price') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('variants.stock') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('variants.created') }}</th>
                        <th class="px-4 py-3 text-end font-semibold">{{ __('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->variants as $variant)
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ $variant->id }}" aria-label="Select {{ $variant->sku }}">
                            </td>
                            <td class="px-4 py-3 font-medium text-ink">{{ $variant->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $variant->sku }}</td>
                            <td class="px-4 py-3 text-ink">{{ number_format($variant->price, 2) }} DZD</td>
                            <td class="px-4 py-3 text-ink-muted">{{ $variant->compare_price !== null ? number_format($variant->compare_price, 2).' DZD' : '—' }}</td>
                            <td class="px-4 py-3 text-ink-muted">{{ $variant->cost_price !== null ? number_format($variant->cost_price, 2).' DZD' : '—' }}</td>
                            <td class="px-4 py-3">
                                @php $badge = $this->stockBadge($variant); @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge['class'] }}"
                                      title="Stock: {{ $variant->stock }} | Threshold: {{ $variant->low_stock_threshold }}">
                                    {{ $badge['text'] }}
                                </span>
                                <span class="ms-1 text-xs text-ink-muted">{{ $variant->stock }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-muted">{{ $variant->created_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            wire:click="toggleHistory('{{ $variant->id }}')">
                                        {{ $historyId === $variant->id ? __('buttons.close') : __('variants.history') }}
                                    </button>
                                    @if ($this->canUpdate())
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="toggleAdjust('{{ $variant->id }}')">{{ __('variants.adjust_stock') }}</button>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="beginEdit('{{ $variant->id }}')">{{ __('buttons.edit') }}</button>
                                    @endif
                                    @if ($this->canDelete())
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                wire:click="delete('{{ $variant->id }}')"
                                                wire:confirm="Delete variant &quot;{{ $variant->sku }}&quot;?">{{ __('buttons.delete') }}</button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        @if ($adjustingId === $variant->id)
                            <tr class="bg-surface-secondary/40">
                                <td colspan="9" class="px-4 py-4">
                                    <form wire:submit="applyStock('{{ $variant->id }}')" class="flex flex-wrap items-end gap-3">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-ink-soft" for="adjust-qty">{{ __('table.quantity') }}</label>
                                            <input id="adjust-qty" type="number" min="1" class="edz-input edz-input--sm"
                                                   wire:model="adjust_quantity" placeholder="1">
                                            @error('adjust_quantity')
                                                <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-ink-soft" for="adjust-type">{{ __('table.type') }}</label>
                                            <select id="adjust-type" class="edz-select edz-input--sm" wire:model="adjust_type">
                                                <option value="">{{ __('product_options.select_type') }}</option>
                                                @foreach ($this->manualTypes as $value => $label)
                                                    <option value="{{ $value }}" @selected($adjust_type === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('adjust_type')
                                                <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm">{{ __('buttons.apply') }}</button>
                                        <span class="text-xs text-ink-muted">{{ __('inventories.current_stock', ['count' => $variant->stock]) }}</span>
                                    </form>
                                </td>
                            </tr>
                        @endif

                        @if ($historyId === $variant->id)
                            <tr class="bg-surface-secondary/40">
                                <td colspan="9" class="px-4 py-4">
                                    @php $movements = $this->movements($variant); @endphp
                                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ __('variants.history') }}</p>
                                    @if ($movements->isEmpty())
                                        <p class="text-sm text-ink-muted">{{ __('variants.no_adjustments') }}</p>
                                    @else
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead>
<tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                                                        <th class="px-3 py-2 text-start font-semibold">{{ __('table.date') }}</th>
                                                        <th class="px-3 py-2 text-start font-semibold">{{ __('table.type') }}</th>
                                                        <th class="px-3 py-2 text-start font-semibold">{{ __('table.qty') }}</th>
                                                        <th class="px-3 py-2 text-start font-semibold">{{ __('table.after') }}</th>
                                                        <th class="px-3 py-2 text-start font-semibold">{{ __('table.by') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($movements as $movement)
                                                        <tr class="border-b border-surface-border last:border-0">
                                                            <td class="px-3 py-2 text-xs text-ink-muted">{{ $movement->created_at?->format('Y-m-d H:i') }}</td>
                                                            <td class="px-3 py-2">
                                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                                                    @if ($movement->type->isDecrease()) bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300
                                                                    @elseif ($movement->type->isIncrease()) bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300
                                                                    @else bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300 @endif">
                                                                    {{ $movement->type->label() }}
                                                                </span>
                                                            </td>
                                                            <td class="px-3 py-2 font-semibold {{ $movement->type->isDecrease() ? 'text-danger-600' : 'text-success-600' }}">
                                                                {{ $movement->type->isDecrease() ? '-' : '+' }}{{ $movement->quantity }}
                                                            </td>
                                                            <td class="px-3 py-2 text-ink-soft">{{ $movement->balance_after }}</td>
                                                            <td class="px-3 py-2 text-xs text-ink-muted">{{ $movement->user?->name ?? __('general.system') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">{{ __('variants.no_variants') }}</p>
                                <p class="mt-1 text-sm text-ink-muted">{{ __('variants.try_adjusting') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->variants->hasPages())
            <div class="border-t border-surface-border px-4 py-3">
                {{ $this->variants->links() }}
            </div>
        @endif
    </div>
</div>
