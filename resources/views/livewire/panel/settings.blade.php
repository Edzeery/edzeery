<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\rules;

state(['store_name' => '', 'locale' => 'en', 'currency' => 'usd', 'status' => 'active']);

rules([
    'store_name' => ['required', 'string', 'max:120'],
    'locale' => ['required', 'in:en,ar'],
    'currency' => ['required', 'in:usd,egp,sar'],
    'status' => ['required', 'in:active,maintenance,disabled'],
])->messages([
    'store_name.required' => 'The store name is required.',
]);

$save = function (): void {
    $this->validate();

    session()->flash('panel.saved', 'Store settings updated successfully.');
};

layout('components.layouts.panel');
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">Settings</h1>
            <p class="edz-page-head__subtitle">Manage your store preferences</p>
        </div>
    </div>

    @if (session('panel.saved'))
        <div class="mb-6 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-800 dark:bg-success-950 dark:text-success-300">
            {{ session('panel.saved') }}
        </div>
    @endif

    <form wire:submit="save" class="edz-card max-w-2xl">
        <div class="edz-card__header">
            <h2 class="edz-card__title">Store profile</h2>
            <p class="text-sm text-ink-400">These settings apply to every store in your workspace</p>
        </div>

        <div class="edz-card__body edz-card__body--padded grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="edz-field sm:col-span-2">
                <label for="store-name" class="edz-field__label">Store name</label>
                <input id="store-name" type="text" class="edz-input" wire:model="store_name"
                       aria-invalid="@error('store_name') true @else false @enderror">
                @error('store_name')
                    <p class="edz-field__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="edz-field">
                <label for="locale" class="edz-field__label">Default locale</label>
                <select id="locale" class="edz-select" wire:model="locale">
                    <option value="en">English</option>
                    <option value="ar">العربية</option>
                </select>
            </div>

            <div class="edz-field">
                <label for="currency" class="edz-field__label">Currency</label>
                <select id="currency" class="edz-select" wire:model="currency">
                    <option value="usd">USD ($)</option>
                    <option value="egp">EGP (ج.م)</option>
                    <option value="sar">SAR (ر.س)</option>
                </select>
            </div>

            <div class="edz-field sm:col-span-2">
                <label for="status" class="edz-field__label">Store status</label>
                <select id="status" class="edz-select" wire:model="status">
                    <option value="active">Active</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="disabled">Disabled</option>
                </select>
                <p class="edz-field__hint">Active stores are visible to customers.</p>
            </div>
        </div>

        <div class="edz-card__footer">
            <button type="submit" class="edz-btn edz-btn--primary">Save changes</button>
        </div>
    </form>
</div>
