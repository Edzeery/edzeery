<?php

use App\Domains\Account\DTOs\PasswordData;
use App\Domains\Account\Services\AccountService;
use App\Http\Requests\Account\Password\UpdatePasswordRequest;

use function Livewire\Volt\layout;
use function Livewire\Volt\state;

layout('components.layouts.account');

state([
    'current_password' => '',
    'new_password' => '',
    'new_password_confirmation' => '',
]);

$updatePassword = function (AccountService $service): void {
    $validated = $this->validate(UpdatePasswordRequest::rulesFor());

    $service->updatePassword(auth()->user(), PasswordData::fromArray($validated));

    $this->reset('current_password', 'new_password', 'new_password_confirmation');

    $this->dispatch('swal', type: 'success', title: __('notifications.password_updated'));
};
?>

<div class="max-w-2xl mx-auto space-y-6">
    <div class="edz-card">
        <div class="edz-card__header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <x-edz.icon name="lock-closed" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <h3 class="edz-card__title">{{ __('account.security') }}</h3>
                    <p class="text-xs text-ink-muted mt-0.5">{{ __('account.security_desc') }}</p>
                </div>
            </div>
        </div>
        <div class="edz-card--padded">
            <form wire:submit="updatePassword" class="space-y-5" x-data="edzDirty()">
                <div>
                    <x-password-input
                        id="current_password"
                        name="current_password"
                        wire:model="current_password"
                        :label="__('account.current_password')"
                        autocomplete="current-password"
                        required
                    />
                    @error('current_password')
                        <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-password-input
                        id="new_password"
                        name="new_password"
                        wire:model="new_password"
                        :label="__('account.new_password')"
                        autocomplete="new-password"
                        required
                    />
                    <p class="text-xs text-ink-muted mt-1">{{ __('account.password_hint') }}</p>
                    @error('new_password')
                        <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-password-input
                        id="new_password_confirmation"
                        name="new_password_confirmation"
                        wire:model="new_password_confirmation"
                        :label="__('account.confirm_new_password')"
                        autocomplete="new-password"
                        required
                    />
                    @error('new_password_confirmation')
                        <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="edz-btn edz-btn--primary">
                        <x-edz.icon name="check-circle" class="w-4 h-4" />
                        {{ __('buttons.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
