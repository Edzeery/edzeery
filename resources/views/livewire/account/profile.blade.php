<?php

use App\Domains\Account\Actions\Profile\GetProfileAction;
use App\Domains\Account\DTOs\ProfileData;
use App\Domains\Account\Services\AccountService;
use App\Http\Requests\Account\Profile\UpdateProfileRequest;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\usesFileUploads;

layout('components.layouts.account');

usesFileUploads();

state([
    'name' => '',
    'originalEmail' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'birthdate' => '',
    'country_id' => '',
    'state_id' => '',
    'city_id' => '',
    'profile_picture' => null,
    'remove_avatar' => false,
    'existingAvatar' => null,
    'current_password' => '',
    'countries' => [],
]);

mount(function (GetProfileAction $action): void {
    $profile = $action->execute(user());

    $this->name = $profile->name;
    $this->originalEmail = $profile->email;
    $this->email = $profile->email;
    $this->phone = $profile->phone ?? '';
    $this->address = $profile->address ?? '';
    $this->birthdate = $profile->birthdate ?? '';
    $this->country_id = $profile->country_id ?? '';
    $this->state_id = $profile->state_id ?? '';
    $this->city_id = $profile->city_id ?? '';
    $this->existingAvatar = $profile->avatar;
    $this->countries = Country::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
});

$states = computed(fn () => $this->country_id
    ? State::where('country_id', $this->country_id)->orderBy('name')->get(['id', 'name'])->toArray()
    : []);

$cities = computed(fn () => $this->state_id
    ? City::where('state_id', $this->state_id)->orderBy('name')->pluck('name', 'id')->toArray()
    : []);

$emailChanged = computed(fn () => $this->email !== $this->originalEmail);

$updatedCountryId = function (): void {
    $this->state_id = '';
    $this->city_id = '';
};

$updatedStateId = function (): void {
    $this->city_id = '';
};

$updatedProfilePicture = function (): void {
    $this->remove_avatar = false;
};

$clearAvatar = function (): void {
    $this->profile_picture = null;
    $this->remove_avatar = true;
};

$updateProfile = function (AccountService $service): void {
    $validated = $this->validate(
        UpdateProfileRequest::rulesFor(['email' => $this->email])
    );

    $service->updateProfile(auth()->user(), ProfileData::fromArray($validated));

    $this->remove_avatar = false;
    $this->profile_picture = null;
    $this->current_password = '';
    $fresh = auth()->user()->fresh();
    $this->existingAvatar = $fresh->profile?->avatarUrl() ?? asset('img/icons/noimg.png');
    $this->originalEmail = $fresh->email;
    $this->email = $fresh->email;

    $this->dispatch('profile-updated');
    $this->dispatch('swal', type: 'success', title: __('notifications.profile_updated'));
};
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="edz-card edz-card--padded">
        <div class="flex flex-col sm:flex-row items-center gap-5">
            <div class="relative">
                <div class="w-24 h-24 rounded-2xl overflow-hidden bg-gradient-to-br from-accent-500 to-brand-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                    @if ($profile_picture)
                        <img src="{{ $profile_picture->temporaryUrl() }}" alt="{{ $name }}" class="w-full h-full object-cover" />
                    @elseif ($existingAvatar && ! $remove_avatar)
                        <img src="{{ $existingAvatar }}" alt="{{ $name }}" class="w-full h-full object-cover" />
                    @else
                        {{ strtoupper(mb_substr($name ?: 'U', 0, 1)) }}
                    @endif
                </div>

                <label class="absolute -bottom-2 -end-2 w-9 h-9 rounded-full bg-accent-500 text-white flex items-center justify-center cursor-pointer shadow-lg hover:bg-accent-600 transition-colors">
                    <x-edz.icon name="camera" class="w-4 h-4" />
                    <input type="file" wire:model="profile_picture" accept="image/png,image/jpeg,image/webp" class="sr-only" />
                    <span class="sr-only">{{ __('account.change_photo') }}</span>
                </label>

                @if (($profile_picture || ($existingAvatar && ! $remove_avatar)))
                    <button type="button" wire:click="clearAvatar"
                        class="absolute -top-2 -end-2 w-7 h-7 rounded-full bg-surface border border-surface-border text-ink-muted flex items-center justify-center shadow hover:text-danger-500 transition-colors">
                        <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                        <span class="sr-only">{{ __('account.remove_photo') }}</span>
                    </button>
                @endif
            </div>

            <div class="flex-1 min-w-0 text-center sm:text-start">
                <h2 class="text-lg font-bold text-ink truncate">{{ $name ?: __('merchant_panel.guest') }}</h2>
                <p class="text-sm text-ink-soft truncate">{{ $email }}</p>
                <p class="text-xs text-ink-muted mt-1">{{ __('account.photo_hint') }}</p>
                <div wire:loading wire:target="profile_picture" class="text-xs text-accent-600 mt-1">...</div>
            </div>
        </div>
    </div>

    <form wire:submit="updateProfile" x-data="edzDirty()">
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
            <div class="edz-card--padded space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="edz-label">{{ __('general.name') }}</label>
                        <input type="text" id="name" wire:model="name" class="edz-input" required />
                        @error('name')
                            <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="edz-label">{{ __('general.email') }}</label>
                        <input type="email" id="email" wire:model.live.debounce.700ms="email" class="edz-input" required />
                        @error('email')
                            <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($this->emailChanged)
                    <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-700 dark:bg-warning-950">
                        <p class="text-xs text-warning-700 dark:text-warning-300">{{ __('account.email_change_hint') }}</p>
                        <div class="mt-3">
                            <label for="current_password" class="edz-label">{{ __('account.current_password') }}</label>
                            <input type="password" id="current_password" wire:model="current_password" class="edz-input mt-1" autocomplete="current-password" required />
                            @error('current_password')
                                <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="phone" class="edz-label">{{ __('general.phone') }}</label>
                        <input type="text" id="phone" wire:model="phone" class="edz-input" />
                        @error('phone')
                            <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="country_id" class="edz-label">{{ __('general.country') }}</label>
                        <x-edz.select
                            wire:model.live="country_id"
                            :options="$countries"
                            option-value="id"
                            option-label="name"
                            :placeholder="__('general.select_country')"
                            search
                            :searchPlaceholder="__('merchant_panel.search')"
                            :error="$errors->first('country_id')"
                        />
                        @error('country_id')
                            <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="state_id" class="edz-label">{{ __('merchant_panel.state') }}</label>
                        <x-edz.select
                            wire:model.live="state_id"
                            :options="$this->states"
                            option-value="id"
                            option-label="name"
                            :placeholder="__('account.select_state')"
                            search
                            :searchPlaceholder="__('merchant_panel.search')"
                            :disabled="empty($country_id)"
                            :error="$errors->first('state_id')"
                        />
                        @error('state_id')
                            <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city_id" class="edz-label">{{ __('merchant_panel.city') }}</label>
                        <x-edz.select
                            wire:model="city_id"
                            :options="$this->cities"
                            option-value="id"
                            option-label="name"
                            :placeholder="__('account.select_city')"
                            search
                            :searchPlaceholder="__('merchant_panel.search')"
                            :disabled="empty($state_id)"
                            :error="$errors->first('city_id')"
                        />
                        @error('city_id')
                            <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="birthdate" class="edz-label">{{ __('general.birthdate') }}</label>
                        <input type="text" id="birthdate" x-data="{}"
                            x-init="flatpickr($el,{dateFormat:'Y-m-d', allowInput:true})"
                            wire:model.blur="birthdate"
                            class="edz-input"
                            placeholder="YYYY-MM-DD"
                            autocomplete="off" />
                        @error('birthdate')
                            <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="edz-label">{{ __('general.address') }}</label>
                        <input type="text" id="address" wire:model="address" class="edz-input" />
                        @error('address')
                            <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
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
