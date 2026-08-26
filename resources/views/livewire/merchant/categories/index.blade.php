<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Category;
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
    'creating' => false,
    'editingId' => null,
    'parent_id' => '',
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
        $this->slug = $this->slug ?: Str::slug($this->name);
    },
    'select_all' => function (string $name, $value): void {
        $this->selected = $value
            ? $this->categories->pluck('id')->all()
            : [];
    },
]);

$categories = computed(function () {
    return Category::query()
        ->where('store_id', currentStoreId())
        ->with('parent')
        ->when($this->search !== '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('slug', 'like', '%'.$this->search.'%');
            });
        })
        ->when($this->is_active !== '', fn ($q) => $q->where('is_active', filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)))
        ->orderBy('name')
        ->paginate(15);
});

$parentOptions = computed(function () {
    return Category::query()
        ->where('store_id', currentStoreId())
        ->orderBy('name')
        ->get()
        ->pluck('full_name', 'id');
});

$canCreate = fn () => canStore(StorePermissionEnum::PRODUCT_CREATE->value);
$canUpdate = fn () => canStore(StorePermissionEnum::PRODUCT_UPDATE->value);
$canDelete = fn () => canStore(StorePermissionEnum::PRODUCT_DELETE->value);

$logoUrl = function (Category $category): string {
    return $category->logo ? Storage::disk('public')->url($category->logo) : asset('img/icons/noimg.png');
};

$openCreate = function (): void {
    abort_unless($this->canCreate(), 403);

    $this->reset('editingId', 'parent_id', 'name', 'slug', 'logo');
    $this->isActive = true;
    $this->creating = true;
};

$beginEdit = function (Category $category): void {
    abort_unless($this->canUpdate(), 403);

    $this->creating = false;
    $this->editingId = $category->id;
    $this->parent_id = $category->parent_id;
    $this->name = $category->name;
    $this->slug = $category->slug;
    $this->isActive = (bool) $category->is_active;
    $this->logo = null;
};

$toggleActive = function (Category $category): void {
    abort_unless($this->canUpdate(), 403);

    $category->update(['is_active' => ! $category->is_active]);
};

$save = function (): void {
    abort_unless($this->canCreate() || $this->canUpdate(), 403);

    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'slug' => [
            'required',
            'string',
            'max:255',
            Rule::unique('categories', 'slug')
                ->where('store_id', currentStoreId())
                ->whereNull('deleted_at')
                ->ignore($this->editingId),
        ],
        'parent_id' => ['nullable', 'string', 'max:26'],
        'isActive' => ['boolean'],
        'logo' => ['nullable', 'image', 'max:2048'],
    ]);

    $parentId = $this->parent_id ?: null;

    if ($this->editingId) {
        abort_unless($this->canUpdate(), 403);

        $category = Category::query()
            ->where('store_id', currentStoreId())
            ->findOrFail($this->editingId);

        if ($parentId && $this->isDescendant($this->editingId, $parentId)) {
            $this->addError('parent_id', 'A category cannot be its own parent or descendant.');

            return;
        }

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'parent_id' => $parentId,
            'is_active' => $validated['isActive'],
        ];

        if ($this->logo instanceof TemporaryUploadedFile) {
            $data['logo'] = $this->logo->store('categories', 'public');
        }

        $category->update($data);
    } else {
        abort_unless($this->canCreate(), 403);

        $data = [
            'store_id' => currentStoreId(),
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'parent_id' => $parentId,
            'is_active' => $validated['isActive'],
        ];

        if ($this->logo instanceof TemporaryUploadedFile) {
            $data['logo'] = $this->logo->store('categories', 'public');
        }

        Category::create($data);
    }

    $this->cancelForm();
};

$isDescendant = function (string $id, string $possibleParentId): bool {
    $current = Category::find($possibleParentId);

    while ($current) {
        if ($current->id === $id) {
            return true;
        }

        $current = $current->parent_id ? Category::find($current->parent_id) : null;
    }

    return false;
};

$cancelForm = function (): void {
    $this->reset('creating', 'editingId', 'parent_id', 'name', 'slug', 'logo');
    $this->isActive = true;
};

$delete = function (Category $category): void {
    abort_unless($this->canDelete(), 403);

    $category->delete();
};

$deleteSelected = function (): void {
    abort_unless($this->canDelete(), 403);

    Category::query()
        ->where('store_id', currentStoreId())
        ->whereIn('id', $this->selected)
        ->delete();

    $this->reset('selected', 'select_all');
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">{{ __('categories.title') }}</h1>
            <p class="edz-page-head__subtitle">{{ __('categories.subtitle', ['store' => currentStore()?->name]) }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($this->canCreate())
                <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="openCreate">{{ __('categories.new_category') }}</button>
            @endif
        </div>
    </div>

    @if ($creating || $editingId)
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title">{{ $editingId ? __('categories.edit_category') : __('categories.new_category') }}</h2>
                    <p class="text-sm text-ink-400">{{ $editingId ? __('categories.edit_category_desc') : __('categories.new_category_desc') }}</p>
                </div>
            </div>

            <form wire:submit="save" class="space-y-4 p-4" x-data="edzDirty()">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="category-name">{{ __('categories.name') }}</label>
                        <input id="category-name" type="text" class="edz-input @error('name') edz-input--error @enderror"
                               wire:model="name" placeholder="{{ __('categories.category_name') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="category-slug">{{ __('categories.slug') }}</label>
                        <input id="category-slug" type="text" class="edz-input @error('slug') edz-input--error @enderror"
                               wire:model="slug" placeholder="electronics">
                        @error('slug')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="category-parent">{{ __('categories.parent_category') }}</label>
                        <x-edz.select
                            wire:model="parent_id"
                            :options="collect($this->parentOptions)->filter(fn ($name, $id) => !$editingId || (string) $id !== (string) $editingId)->all()"
                            placeholder="{{ __('categories.no_parent') }}"
                            :error="$errors->first('parent_id')"
                        />
                        @error('parent_id')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm font-medium text-ink">
                            <input type="checkbox" wire:model="isActive" class="h-4 w-4 rounded border-surface-border">
                            {{ __('categories.category_active') }}
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink" for="category-logo">{{ __('categories.logo') }}</label>
                    <input id="category-logo" type="file" class="edz-input" wire:model="logo" accept="image/*">
                    @error('logo')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
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
                <h2 class="edz-card__title">{{ __('categories.list_title') }}</h2>
                <p class="text-sm text-ink-400">{{ __('categories.list_subtitle') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="{{ __('categories.search_placeholder') }}"
                       wire:model.live.debounce.300ms="search">
            </div>
            <div>
                <x-edz.select
                    wire:model.live="is_active"
                    :options="[
                        ['value' => '', 'label' => __('categories.all_statuses')],
                        ['value' => '1', 'label' => __('categories.active')],
                        ['value' => '0', 'label' => __('categories.inactive')],
                    ]"
                />
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
                        <th class="px-4 py-3 text-start font-semibold">{{ __('categories.logo') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('categories.name') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('categories.slug') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('categories.status') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('categories.created') }}</th>
                        <th class="px-4 py-3 text-end font-semibold">{{ __('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->categories as $category)
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ $category->id }}" aria-label="Select {{ $category->name }}">
                            </td>
                            <td class="px-4 py-3">
                                <img src="{{ $this->logoUrl($category) }}" alt="{{ $category->name }}"
                                     class="h-10 w-10 flex-none rounded-full border border-surface-border object-cover">
                            </td>
                            <td class="px-4 py-3 font-medium text-ink">{{ $category->full_name }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft">{{ $category->slug }}</td>
                            <td class="px-4 py-3">
                                <x-merchant.status domain="general"
                                                   :status="$category->is_active ? 'active' : 'inactive'" />
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-muted">{{ $category->created_at?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($this->canUpdate())
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="beginEdit('{{ $category->id }}')">{{ __('buttons.edit') }}</button>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="toggleActive('{{ $category->id }}')">
                                            {{ $category->is_active ? __('categories.deactivate') : __('categories.activate') }}
                                        </button>
                                    @endif
                                    @if ($this->canDelete())
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                x-data
                                                data-delete-name="{{ $category->name }}"
                                                data-delete-id="{{ $category->id }}"
                                                @click.prevent="(async () => { if (await EdzSwal.confirmDelete($el.dataset.deleteName)) await $wire.delete(Number($el.dataset.deleteId)) })()"
                                                >{{ __('buttons.delete') }}</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">{{ __('categories.no_categories') }}</p>
                                <p class="mt-1 text-sm text-ink-muted">{{ __('categories.try_adjusting') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->categories->hasPages())
            <div class="border-t border-surface-border px-4 py-3">
                {{ $this->categories->links() }}
            </div>
        @endif
    </div>
</div>
