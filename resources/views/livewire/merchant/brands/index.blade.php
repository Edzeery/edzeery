<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\updated;
use function Livewire\Volt\uses;
use function Livewire\Volt\usesPagination;

uses([WithFileUploads::class]);
usesPagination();

layout('components.layouts.store');

state([
    'search' => '',
    'is_active' => '',
    'selected' => [],
    'select_all' => false,
    'isNew' => false,
    'editingId' => null,
    'name' => '',
    'slug' => '',
    'logo' => null,
    'isActive' => true,
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::PRODUCT_VIEW->value), 403);
});

updated([
    'name' => function (): void {
        $this->slug = Str::slug($this->name);
    },
    'select_all' => function (string $name, $value): void {
        $this->selected = $value
            ? $this->brands->pluck('id')->all()
            : [];
    },
]);

$brands = computed(function () {
    return Brand::query()
        ->where('store_id', currentStoreId())
        ->when($this->search !== '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->is_active !== '', fn ($q) => $q->where('is_active', filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)))
        ->orderBy('name')
        ->paginate(15);
});

$canUpdate = fn () => canStore(StorePermissionEnum::PRODUCT_UPDATE->value);
$canDelete = fn () => canStore(StorePermissionEnum::PRODUCT_DELETE->value);

$logoUrl = function (Brand $brand): string {
    return $brand->logo ? Storage::disk('public')->url($brand->logo) : asset('img/icons/noimg.png');
};

$toggleActive = function (Brand $brand): void {
    abort_unless($this->canUpdate(), 403);

    $brand->update(['is_active' => ! $brand->is_active]);
};

$openCreate = function (): void {
    abort_unless($this->canUpdate(), 403);

    $this->isNew = true;
    $this->editingId = null;
    $this->name = '';
    $this->slug = '';
    $this->isActive = true;
    $this->logo = null;
};

$beginEdit = function (Brand $brand): void {
    abort_unless($this->canUpdate(), 403);

    $this->isNew = false;
    $this->editingId = $brand->id;
    $this->name = $brand->name;
    $this->slug = $brand->slug;
    $this->isActive = (bool) $brand->is_active;
    $this->logo = null;
};

$save = function (): void {
    abort_unless($this->canUpdate(), 403);

    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'slug' => [
            'required',
            'string',
            'max:255',
            Rule::unique('brands')->ignore($this->editingId)->whereNull('deleted_at'),
        ],
        'isActive' => ['boolean'],
        'logo' => ['nullable', 'image', 'max:2048'],
    ]);

    $data = [
        'store_id' => currentStoreId(),
        'name' => $validated['name'],
        'slug' => $validated['slug'],
        'is_active' => $validated['isActive'],
    ];

    if ($this->logo instanceof TemporaryUploadedFile) {
        $data['logo'] = $this->logo->store('brands', 'public');
    }

    if ($this->isNew) {
        Brand::create($data);
    } else {
        Brand::query()
            ->where('store_id', currentStoreId())
            ->findOrFail($this->editingId)
            ->update($data);
    }

    $this->reset('isNew', 'editingId', 'name', 'slug', 'logo', 'isActive');
};

$cancelEdit = function (): void {
    $this->reset('isNew', 'editingId', 'name', 'slug', 'logo', 'isActive');
};

$delete = function (Brand $brand): void {
    abort_unless($this->canDelete(), 403);

    $brand->delete();
};

$deleteSelected = function (): void {
    abort_unless($this->canDelete(), 403);

    Brand::query()
        ->where('store_id', currentStoreId())
        ->whereIn('id', $this->selected)
        ->delete();

    $this->reset('selected', 'select_all');
};
?>

<div>
    <x-edz.page-header
        title="{{ __('brands.title') }}"
        description="{{ __('brands.subtitle', ['store' => currentStore()?->name]) }}">
        <x-slot:actions>
            @if ($this->canUpdate())
                <button wire:click="openCreate" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('brands.add_brand') }}
                </button>
            @endif
        </x-slot:actions>
    </x-edz.page-header>

    @if ($isNew || $editingId)
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title">{{ $isNew ? __('brands.add_brand') : __('brands.edit_brand') }}</h2>
                    <p class="text-sm text-ink-400">{{ $isNew ? __('brands.create_details') : __('brands.update_details') }}</p>
                </div>
            </div>

            <form wire:submit="save" class="space-y-4 p-4" x-data="edzDirty()">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="brand-name">{{ __('brands.brand_name') }}</label>
                        <input id="brand-name" type="text" class="edz-input" wire:model="name" placeholder="{{ __('brands.brand_name') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="brand-slug">{{ __('brands.slug') }}</label>
                        <input id="brand-slug" type="text" class="edz-input" wire:model="slug" placeholder="{{ __('brands.brand_slug') }}">
                        @error('slug')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="brand-logo">{{ __('brands.logo') }}</label>
                        <input id="brand-logo" type="file" class="edz-input" wire:model="logo" accept="image/*">
                        @error('logo')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm font-medium text-ink">
                            <input type="checkbox" wire:model="isActive" class="h-4 w-4 rounded border-surface-border">
                            {{ __('brands.brand_active') }}
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm">{{ __('buttons.save') }}</button>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="cancelEdit">{{ __('buttons.cancel') }}</button>
                </div>
            </form>
        </div>
    @endif

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title">{{ __('brands.list_title') }}</h2>
                <p class="text-sm text-ink-400">{{ __('brands.list_subtitle') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="{{ __('brands.search_placeholder') }}"
                       wire:model.live.debounce.300ms="search">
            </div>
            <div>
                <x-edz.select
                    wire:model.live="is_active"
                    :options="[
                        ['value' => '', 'label' => __('brands.all_statuses')],
                        ['value' => '1', 'label' => __('brands.active')],
                        ['value' => '0', 'label' => __('brands.inactive')],
                    ]"
                />
            </div>
        </div>

        @if (! empty($selected))
            <div class="flex flex-wrap items-center gap-2 border-b border-surface-border bg-brand-surface px-4 py-3">
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
                        <th class="px-4 py-3 text-start font-semibold">{{ __('brands.logo') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('brands.name') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('brands.slug') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('brands.status') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('brands.created') }}</th>
                        <th class="px-4 py-3 text-end font-semibold">{{ __('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->brands as $brand)
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ $brand->id }}" aria-label="Select {{ $brand->name }}">
                            </td>
                            <td class="px-4 py-3">
                                <img src="{{ $this->logoUrl($brand) }}" alt="{{ $brand->name }}"
                                     class="h-10 w-10 flex-none rounded-full border border-surface-border object-cover">
                            </td>
                            <td class="px-4 py-3 font-medium text-ink">{{ $brand->name }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $brand->slug }}</td>
                            <td class="px-4 py-3">
                                <x-merchant.status domain="general"
                                                   :status="$brand->is_active ? 'active' : 'inactive'" />
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-muted">{{ $brand->created_at?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($this->canUpdate())
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="beginEdit('{{ $brand->id }}')">{{ __('buttons.edit') }}</button>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="toggleActive('{{ $brand->id }}')">
                                            {{ $brand->is_active ? __('brands.deactivate') : __('brands.activate') }}
                                        </button>
                                    @endif
                                    @if ($this->canDelete())
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                x-data
                                                data-delete-name="{{ $brand->name }}"
                                                data-delete-id="{{ $brand->id }}"
                                                @click.prevent="(async () => { if (await EdzSwal.confirmDelete($el.dataset.deleteName)) await $wire.delete(Number($el.dataset.deleteId)) })()"
                                                >{{ __('buttons.delete') }}</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">{{ __('brands.no_brands') }}</p>
                                <p class="mt-1 text-sm text-ink-muted">{{ __('brands.try_adjusting') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->brands->hasPages())
            <div class="border-t border-surface-border px-4 py-3">
                {{ $this->brands->links() }}
            </div>
        @endif
    </div>
</div>
