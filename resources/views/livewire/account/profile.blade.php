<?php

use App\Domains\Account\Actions\Profile\GetProfileAction;
use App\Domains\Account\DTOs\ProfileData;
use App\Domains\Account\Services\AccountService;
use App\Http\Requests\Account\Profile\UpdateProfileRequest;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.account');

state([
    'name'        => '',
    'email'       => '',
    'phone'       => '',
    'address'     => '',
    'birthdate'   => '',
    'country'     => '',
    'countries'   => [],
]);

mount(function (GetProfileAction $action): void {
    $profile = $action->execute(user());
    $this->name = $profile->name;
    $this->email = $profile->email;
    $this->phone = $profile->phone ?? '';
    $this->address = $profile->address ?? '';
    $this->birthdate = $profile->birthdate ?? '';
    $this->country = $profile->country ?? '';
    $this->countries = \App\Models\Locations\Country::where('is_active', true)->orderBy('name')->get();
});

$updateProfile = function (UpdateProfileRequest $request, AccountService $service): void {
    $service->updateProfile(
        auth()->user(),
        ProfileData::fromArray($request->validated())
    );

    $this->dispatch('profile-updated');
    $this->dispatch('swal', type: 'success', title: __('notifications.profile_updated'));
};
?>

<div class="max-w-3xl mx-auto space-y-6">
    {{-- Avatar Header --}}
    <div class="edz-card edz-card--padded">
        <div class="flex items-center gap-5">
            <div class="relative group">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-accent-500 to-brand-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                    {{ strtoupper(mb_substr($name ?: 'U', 0, 1)) }}
                </div>
                <div class="absolute inset-0 rounded-2xl bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                    <x-edz.icon name="edit" class="w-5 h-5 text-white" />
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-lg font-bold text-ink truncate">{{ $name ?: __('merchant_panel.guest') }}</h2>
                <p class="text-sm text-ink-soft truncate">{{ $email }}</p>
            </div>
        </div>
    </div>

    {{-- Profile Form --}}
    <form wire:submit="updateProfile" x-data="edzDirty()">
        {{-- Personal Info --}}
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                        <x-edz.icon name="user" class="w-5 h-5 text-brand-600 dark:text-brand-400" />
                    </div>
                    <div>
                        <h3 class="edz-card__title">{{ __('merchant_panel.profile') }}</h3>
                        <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.profile_desc') }}</p>
                    </div>
                </div>
            </div>
            <div class="edz-card--padded">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="edz-label">{{ __('general.name') }}</label>
                        <input type="text" id="name" wire:model="name"
                               class="edz-input" required />
                    </div>

                    <div>
                        <label for="email" class="edz-label">{{ __('general.email') }}</label>
                        <input type="email" id="email" wire:model="email"
                               class="edz-input" required />
                    </div>

                    <div>
                        <label for="phone" class="edz-label">{{ __('general.phone') }}</label>
                        <input type="text" id="phone" wire:model="phone"
                               class="edz-input" />
                    </div>

                    <div>
                        <label for="country" class="edz-label">{{ __('general.country') }}</label>
                        <select id="country" wire:model="country" class="edz-input">
                            <option value="">{{ __('general.select_country') }}</option>
                            @foreach ($countries as $c)
                                <option value="{{ $c->code }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="birthdate" class="edz-label">{{ __('general.birthdate') }}</label>
                        <input type="date" id="birthdate" wire:model="birthdate"
                               class="edz-input" />
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="edz-label">{{ __('general.address') }}</label>
                        <input type="text" id="address" wire:model="address"
                               class="edz-input" />
                    </div>
                </div>
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
