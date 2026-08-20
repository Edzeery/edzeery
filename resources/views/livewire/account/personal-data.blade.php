<?php

use App\Domains\Account\DTOs\SettingsData;
use App\Domains\Account\Services\AccountService;
use App\Http\Requests\Account\Settings\UpdateSettingsRequest;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.account');

state([
    'language' => 'ar',
    'theme' => 'system',
    'timezone' => 'Africa/Algiers',
    'date_format' => 'Y-m-d',
    'email_notifications' => true,
    'order_notifications' => true,
    'stock_notifications' => true,
    'marketing_notifications' => false,
]);

mount(function (): void {
    $prefs = user()->settings?->preferences ?? [];
    $this->language = $prefs['language'] ?? 'ar';
    $this->theme = $prefs['theme'] ?? 'system';
    $this->timezone = $prefs['timezone'] ?? 'Africa/Algiers';
    $this->date_format = $prefs['date_format'] ?? 'Y-m-d';
    $this->email_notifications = $prefs['email_notifications'] ?? true;
    $this->order_notifications = $prefs['order_notifications'] ?? true;
    $this->stock_notifications = $prefs['stock_notifications'] ?? true;
    $this->marketing_notifications = $prefs['marketing_notifications'] ?? false;
});

$saveSettings = function (UpdateSettingsRequest $request, AccountService $service): void {
    $service->updateSettings(
        auth()->user(),
        SettingsData::fromArray($request->validated())
    );

    $this->dispatch('swal', type: 'success', title: __('notifications.settings_updated'));
};
?>

<div class="max-w-3xl mx-auto space-y-6">
    <form wire:submit="saveSettings" x-data="edzDirty()">
        {{-- Language & Region --}}
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <x-edz.icon name="sun" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <h3 class="edz-card__title">{{ __('merchant_panel.language_region') }}</h3>
                        <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.personal_data_desc') }}</p>
                    </div>
                </div>
            </div>
            <div class="edz-card--padded">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="edz-label">{{ __('general.language') }}</label>
                        <select wire:model="language" class="edz-input">
                            <option value="ar">{{ __('merchant_panel.arabic') }}</option>
                            <option value="en">{{ __('merchant_panel.english') }}</option>
                            <option value="fr">{{ __('merchant_panel.french') }}</option>
                            <option value="es">{{ __('merchant_panel.spanish') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.timezone') }}</label>
                        <select wire:model="timezone" class="edz-input">
                            <option value="Africa/Algiers">Africa/Algiers (GMT+1)</option>
                            <option value="Europe/Paris">Europe/Paris (GMT+1/+2)</option>
                            <option value="Europe/London">Europe/London (GMT+0/+1)</option>
                            <option value="America/New_York">America/New_York (GMT-5/-4)</option>
                            <option value="Asia/Dubai">Asia/Dubai (GMT+4)</option>
                        </select>
                    </div>
                    <div>
                        <label class="edz-label">{{ __('merchant_panel.date_format') }}</label>
                        <select wire:model="date_format" class="edz-input">
                            <option value="Y-m-d">2026-08-18</option>
                            <option value="d/m/Y">18/08/2026</option>
                            <option value="m/d/Y">08/18/2026</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Appearance --}}
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <x-edz.icon name="moon" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <h3 class="edz-card__title">{{ __('merchant_panel.appearance') }}</h3>
                        <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.theme_desc') }}</p>
                    </div>
                </div>
            </div>
            <div class="edz-card--padded">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach (['light' => '☀️ ' . __('merchant_panel.theme_light'), 'dark' => '🌙 ' . __('merchant_panel.theme_dark'), 'system' => '💻 ' . __('merchant_panel.theme_system')] as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="theme" value="{{ $value }}" class="sr-only peer" />
                            <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 text-center transition-all
                                        peer-checked:border-accent-500 peer-checked:bg-accent-50 dark:peer-checked:bg-accent-900/20
                                        hover:border-gray-300 dark:hover:border-gray-600">
                                <span class="text-sm font-medium text-ink">{{ $label }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                        <x-edz.icon name="bell" class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                    </div>
                    <div>
                        <h3 class="edz-card__title">{{ __('merchant_panel.notifications') }}</h3>
                        <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.notif_email') }}</p>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-surface-border">
                @foreach ([
                    'email_notifications' => 'merchant_panel.notif_email',
                    'order_notifications' => 'merchant_panel.notif_orders',
                    'stock_notifications' => 'merchant_panel.notif_stock',
                    'marketing_notifications' => 'merchant_panel.notif_marketing',
                ] as $field => $label)
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-ink">{{ __($label) }}</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" wire:model="{{ $field }}" class="peer sr-only" />
                            <div class="h-6 w-11 rounded-full bg-gray-300 dark:bg-gray-600 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-all peer-checked:bg-accent-500 peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="edz-btn edz-btn--primary">
                <x-edz.icon name="check-circle" class="w-4 h-4" />
                {{ __('buttons.save') }}
            </button>
        </div>
    </form>
</div>
