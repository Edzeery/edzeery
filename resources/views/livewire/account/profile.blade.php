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
    session()->flash('success', __('notifications.profile_updated'));
};
?>

<div>
    <x-edz.page-header
        title="{{ __('merchant_panel.profile') }}"
        description="{{ __('merchant_panel.profile_desc') }}">
    </x-edz.page-header>

    @if (session('success'))
        <div class="mb-6 p-4 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg text-success-700 dark:text-success-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="updateProfile">
        <div class="edz-card edz-card--padded space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="edz-btn edz-btn--primary">
                    {{ __('buttons.save') }}
                </button>
            </div>
        </div>
    </form>
</div>
