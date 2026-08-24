<?php

use App\Enums\Store\ProductOptionInputType;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use Illuminate\Validation\Rule;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\usesPagination;

usesPagination();

layout('components.layouts.store');

state([
    'search' => '',
    'selected' => [],
    'select_all' => false,
    'creating' => false,
    'editingId' => null,
    'name' => '',
    'type' => '',
    'activeOptionId' => null,
    'newValue' => '',
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::PRODUCT_VIEW->value), 403);
});

$options = computed(function () {
    return ProductOption::query()
        ->where('store_id', currentStoreId())
        ->withCount('values')
        ->when($this->search !== '', function ($query) {
            $query->where('name', 'like', '%'.$this->search.'%');
        })
        ->orderBy('name')
        ->paginate(15);
});

$typeOptions = computed(fn () => ProductOptionInputType::options());

$canCreate = fn () => canStore(StorePermissionEnum::PRODUCT_CREATE->value);
$canUpdate = fn () => canStore(StorePermissionEnum::PRODUCT_UPDATE->value);
$canDelete = fn () => canStore(StorePermissionEnum::PRODUCT_DELETE->value);

$openCreate = function (): void {
    abort_unless($this->canCreate(), 403);

    $this->reset('editingId', 'name', 'type');
    $this->creating = true;
};

$beginEdit = function (ProductOption $option): void {
    abort_unless($this->canUpdate(), 403);

    $this->creating = false;
    $this->editingId = $option->id;
    $this->name = $option->name;
    $this->type = $option->type?->value ?? '';
};

$toggleActive = function (ProductOption $option): void {
    $this->activeOptionId = $this->activeOptionId === $option->id ? null : $option->id;
    $this->newValue = '';
};

$save = function (): void {
    abort_unless($this->canCreate() || $this->canUpdate(), 403);

    $validated = $this->validate([
        'name' => ['required', 'string', 'max:100'],
        'type' => ['required', Rule::in(array_column(ProductOptionInputType::cases(), 'value'))],
    ]);

    $data = [
        'name' => $validated['name'],
        'type' => $validated['type'],
    ];

    if ($this->editingId) {
        abort_unless($this->canUpdate(), 403);

        ProductOption::query()
            ->where('store_id', currentStoreId())
            ->findOrFail($this->editingId)
            ->update($data);
    } else {
        abort_unless($this->canCreate(), 403);

        ProductOption::create([
            'store_id' => currentStoreId(),
            ...$data,
        ]);
    }

    $this->reset('creating', 'editingId', 'name', 'type');
};

$cancelForm = function (): void {
    $this->reset('creating', 'editingId', 'name', 'type');
};

$optionValues = function (ProductOption $option): \Illuminate\Database\Eloquent\Collection {
    return $option->values()
        ->withCount('variants')
        ->orderBy('value')
        ->get();
};

$addValue = function (ProductOption $option): void {
    abort_unless($this->canUpdate(), 403);

    $value = trim($this->newValue);

    if ($value === '') {
        return;
    }

    $option->addValue($value);
    $this->newValue = '';
};

$generateSizes = function (ProductOption $option): void {
    abort_unless($this->canUpdate(), 403);

    if (strtolower($option->name) !== 'size') {
        return;
    }

    foreach (range(25, 45) as $size) {
        $option->addValue((string) $size);
    }
};

$deleteValue = function (ProductOptionValue $value): void {
    abort_unless($this->canDelete(), 403);

    if ($value->variants()->exists()) {
        $this->dispatch('swal', type: 'warning', title: __('merchant_panel.cannot_delete_variant_value'));

        return;
    }

    $value->delete();
};

$delete = function (ProductOption $option): void {
    abort_unless($this->canDelete(), 403);

    if ($option->isUsedInVariants()) {
        $this->dispatch('swal', type: 'warning', title: __('merchant_panel.cannot_delete_option'));

        return;
    }

    $option->delete();
};

$deleteSelected = function (): void {
    abort_unless($this->canDelete(), 403);

    $options = ProductOption::query()
        ->where('store_id', currentStoreId())
        ->whereIn('id', $this->selected)
        ->get();

    if ($options->contains(fn ($option) => $option->isUsedInVariants())) {
        $this->dispatch('swal', type: 'warning', title: __('merchant_panel.cannot_delete_options'));

        return;
    }

    $options->each->delete();

    $this->reset('selected', 'select_all');
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">{{ __('product_options.title') }}</h1>
            <p class="edz-page-head__subtitle">{{ __('product_options.subtitle', ['store' => currentStore()?->name]) }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($this->canCreate())
                <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="openCreate">{{ __('product_options.new_option') }}</button>
            @endif
        </div>
    </div>

    @if ($creating || $editingId)
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title">{{ $editingId ? __('product_options.edit_option') : __('product_options.new_option') }}</h2>
                    <p class="text-sm text-ink-400">{{ $editingId ? __('product_options.edit_option_desc') : __('product_options.new_option_desc') }}</p>
                </div>
            </div>

            <form wire:submit="save" class="space-y-4 p-4" x-data="edzDirty()">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="option-name">{{ __('product_options.name') }}</label>
                        <input id="option-name" type="text" class="edz-input @error('name') edz-input--error @enderror"
                               wire:model="name" placeholder="{{ __('product_options.option_name') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="option-type">{{ __('product_options.input_type') }}</label>
                        <select id="option-type" class="edz-select" wire:model="type">
                            <option value="">{{ __('product_options.select_type') }}</option>
                            @foreach ($this->typeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
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
                <h2 class="edz-card__title">{{ __('product_options.list_title') }}</h2>
                <p class="text-sm text-ink-400">{{ __('product_options.list_subtitle') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="{{ __('product_options.search_placeholder') }}"
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>

        @if (! empty($selected))
            <div class="flex flex-wrap items-center gap-2 border-b border-surface-border bg-brand-50/50 px-4 py-3 dark:bg-brand-950/30">
                <span class="text-sm font-medium text-ink">{{ __('general.selected_count', ['count' => count($selected)]) }}</span>
                <button type="button" class="edz-btn edz-btn--danger edz-btn--sm"
                        x-data
                        data-confirm-count="{{ count($selected) }}"
                        @click.prevent="(async () => { if (await EdzSwal.confirmBulkDelete(Number($el.dataset.confirmCount))) await $wire.deleteSelected() })()">{{ __('buttons.delete') }}</button>
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
                        <th class="px-4 py-3 text-start font-semibold">{{ __('product_options.name') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('product_options.type') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('product_options.values') }}</th>
                        <th class="px-4 py-3 text-end font-semibold">{{ __('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->options as $option)
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ $option->id }}" aria-label="Select {{ $option->name }}">
                            </td>
                            <td class="px-4 py-3 font-medium text-ink">{{ $option->name }}</td>
                            <td class="px-4 py-3 text-xs text-ink-soft">{{ $option->type?->value }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-surface-secondary px-2.5 py-0.5 text-xs font-semibold text-ink-soft">{{ $option->values_count }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            wire:click="toggleActive('{{ $option->id }}')">
                                        {{ $activeOptionId === $option->id ? __('product_options.hide_values') : __('product_options.show_values') }}
                                    </button>
                                    @if ($this->canUpdate())
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="beginEdit('{{ $option->id }}')">{{ __('buttons.edit') }}</button>
                                    @endif
                                    @if ($this->canDelete())
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                x-data
                                                data-delete-name="{{ $option->name }}"
                                                data-delete-id="{{ $option->id }}"
                                                @click.prevent="(async () => { if (await EdzSwal.confirmDelete($el.dataset.deleteName)) await $wire.delete(Number($el.dataset.deleteId)) })()"
                                                >{{ __('buttons.delete') }}</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if ($activeOptionId === $option->id)
                            <tr class="bg-surface-secondary/40">
                                <td colspan="5" class="px-4 py-4">
                                    @php $values = $this->optionValues($option); @endphp

                                    <div class="flex flex-wrap items-center gap-2">
                                        @if (strtolower($option->name) === 'size' && $this->canUpdate())
                                            <button type="button" class="edz-btn edz-btn--secondary edz-btn--sm"
                                                    wire:click="generateSizes('{{ $option->id }}')">{{ __('product_options.generate_sizes') }}</button>
                                        @endif
                                        @if ($this->canUpdate())
                                            <form wire:submit="addValue('{{ $option->id }}')" class="flex items-center gap-2">
                                                <input type="text" class="edz-input edz-input--sm" placeholder="{{ __('product_options.add_value_placeholder') }}"
                                                       wire:model="newValue">
                                                <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm">{{ __('buttons.add') }}</button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @forelse ($values as $value)
                                            <span class="inline-flex items-center gap-2 rounded-full border border-surface-border bg-surface px-3 py-1 text-sm text-ink">
                                                {{ $value->value }}
                                                <span class="text-xs text-ink-muted">{{ trans_choice('product_options.variant_count', $value->variants_count, ['count' => $value->variants_count]) }}</span>
                                                @if ($this->canDelete() && ! $value->variants()->exists())
                                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--xs text-danger-600 hover:text-danger-700"
                                                            x-data
                                                            data-delete-name="{{ $value->value }}"
                                                            data-delete-id="{{ $value->id }}"
                                                            @click.prevent="(async () => { if (await EdzSwal.confirmDelete($el.dataset.deleteName)) await $wire.deleteValue(Number($el.dataset.deleteId)) })()"
                                                            ><x-edz.icon name="x-mark" class="w-3 h-3" /></button>
                                                @endif
                                            </span>
                                        @empty
                                            <span class="text-sm text-ink-muted">{{ __('product_options.no_values') }}</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">{{ __('product_options.no_options') }}</p>
                                <p class="mt-1 text-sm text-ink-muted">{{ __('product_options.try_adjusting') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->options->hasPages())
            <div class="border-t border-surface-border px-4 py-3">
                {{ $this->options->links() }}
            </div>
        @endif
    </div>
</div>
