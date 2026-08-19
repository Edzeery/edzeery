<?php

use App\Domains\Plan\Services\FeatureUsageService;
use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StoreStatusEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\usesFileUploads;

usesFileUploads();

layout('components.layouts.guest');

state([
    'step' => 1,
    'name' => '',
    'slug' => '',
    'description' => '',
    'logo' => null,
    'cover' => null,
    'currency' => 'DZD',
    'currency_symbol' => 'DA',
    'language' => 'ar',
    'inventory_tracking' => true,
    'guest_checkout' => true,
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'primary_color' => '#000000',
    'secondary_color' => '#ffffff',
    'font_family' => 'Cairo',
    'landing_template' => 'single_product',
]);

mount(function (): void {
    $user = auth()->user();

    $hasOwnerRole = $user->hasAnyRoleForGuard(
        [StoreRoleEnum::OWNER->value],
        'merchant'
    );

    $hasOnlyStaffRoles = ! $hasOwnerRole && $user->hasAnyRoleForGuard(
        [StoreRoleEnum::STAFF->value, StoreRoleEnum::MANAGER->value],
        'merchant'
    );

    if ($hasOnlyStaffRoles) {
        abort(403, __('stores.membership_Forbidden_403'));
    }

    $subscription = $user->latestSubscription();
    if (! $subscription) {
        abort(403, __('stores.subscription_required'));
    }
});

$updatedName = function (string $value): void {
    $this->slug = Str::slug($value);
};

$nextStep = function (): void {
    if ($this->step === 1) {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('stores')->whereNull('deleted_at')],
        ]);
    }
    $this->step = min($this->step + 1, 5);
};

$prevStep = function (): void {
    $this->step = max($this->step - 1, 1);
};

$createStore = function (): void {
    $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'slug' => ['required', 'string', 'max:255', Rule::unique('stores')->whereNull('deleted_at')],
        'currency' => ['required', 'string', 'max:10'],
        'landing_template' => ['required', 'string'],
    ]);

    $user = auth()->user();
    $subscription = $user->latestSubscription();

    if (! $subscription) {
        $this->dispatch('swal', type: 'error', title: __('stores.subscription_required'));
        return;
    }

    $featureService = app(FeatureUsageService::class);
    if (! $featureService->canUse($subscription, 'stores_max')) {
        $this->dispatch('swal', type: 'error', title: __('stores.limit_reached'));
        return;
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
        $store = Store::create([
            'user_id' => $user->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'logo' => $this->logo ? uploadPath($this->logo) : null,
            'cover' => $this->cover ? uploadPath($this->cover) : null,
            'status' => StoreStatusEnum::ACTIVE,
            'landing_template' => $this->landing_template,
        ]);

        $store->settings()->create([
            'currency' => $this->currency,
            'currency_symbol' => $this->currency_symbol,
            'language' => $this->language,
            'inventory_tracking' => $this->inventory_tracking,
            'guest_checkout' => $this->guest_checkout,
        ]);

        $store->seo()->create([
            'meta_title' => $this->meta_title ?: null,
            'meta_description' => $this->meta_description ?: null,
            'meta_keywords' => $this->meta_keywords ?: null,
        ]);

        $store->theme()->create([
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'font_family' => $this->font_family,
        ]);

        $membership = StoreMembership::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
            'invited_by' => $user->id,
            'invited_at' => now(),
            'accepted_at' => now(),
            'is_active' => true,
        ]);

        if (! $user->hasRole(StoreRoleEnum::OWNER->value)) {
            $user->assignRole(StoreRoleEnum::OWNER->value);
        }
    });

    $subscription = $user->latestSubscription();
    app(FeatureUsageService::class)->consume($subscription, 'stores_max');

    session(['current_store_id' => $store->id]);

    $this->redirect(route('merchant.dashboard', $store->slug), navigate: true);
};
?>

<div class="min-h-screen bg-surface-primary flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-2xl">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-ink">{{ __('stores.create_your_store') }}</h1>
            <p class="mt-1 text-ink-muted">{{ __('stores.setup_steps_hint') }}</p>
        </div>

        {{-- Steps indicator --}}
        <div class="mb-8 flex items-center justify-center gap-2">
            @foreach ([1 => __('stores.step_info'), 2 => __('stores.step_settings'), 3 => __('stores.step_seo'), 4 => __('stores.step_design'), 5 => __('stores.step_template')] as $s => $label)
                <button type="button" wire:click="$set('step', {{ $s }})"
                        class="flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition
                               {{ $step === $s ? 'bg-brand-600 text-white' : ($step > $s ? 'bg-success-100 text-success-700' : 'bg-surface-secondary text-ink-muted') }}">
                    @if ($step > $s)
                        <x-edz.icon name="check" class="h-3.5 w-3.5" />
                    @else
                        {{ $s }}
                    @endif
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="edz-card p-6">
            <form wire:submit="{{ $step === 5 ? 'createStore' : 'nextStep' }}" x-data="edzDirty()">
                {{-- Step 1: Store Info --}}
                @if ($step === 1)
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">{{ __('stores.store_information') }}</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.store_name') }}</label>
                                <input type="text" class="edz-input" wire:model.live="name" placeholder="{{ __('stores.name_placeholder') }}">
                                @error('name')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.slug') }}</label>
                                <input type="text" class="edz-input" wire:model="slug" readonly>
                                @error('slug')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.description') }}</label>
                            <textarea class="edz-input" wire:model="description" rows="3" placeholder="{{ __('stores.description_placeholder') }}"></textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.logo') }}</label>
                                <input type="file" class="edz-input" wire:model="logo" accept="image/*">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.cover') }}</label>
                                <input type="file" class="edz-input" wire:model="cover" accept="image/*">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Step 2: Settings --}}
                @if ($step === 2)
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">{{ __('stores.general_settings') }}</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.currency') }}</label>
                                <select class="edz-select" wire:model="currency">
                                    <option value="DZD">DZD</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.symbol') }}</label>
                                <input type="text" class="edz-input" wire:model="currency_symbol">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.language') }}</label>
                                <select class="edz-select" wire:model="language">
                                    <option value="ar">{{ __('stores.lang_arabic') }}</option>
                                    <option value="en">{{ __('stores.lang_english') }}</option>
                                    <option value="fr">{{ __('stores.lang_french') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model="inventory_tracking" class="h-4 w-4 rounded border-surface-border">
                                {{ __('stores.inventory_tracking') }}
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model="guest_checkout" class="h-4 w-4 rounded border-surface-border">
                                {{ __('stores.guest_checkout') }}
                            </label>
                        </div>
                    </div>
                @endif

                {{-- Step 3: SEO --}}
                @if ($step === 3)
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">{{ __('stores.seo') }}</h2>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.meta_title') }}</label>
                            <input type="text" class="edz-input" wire:model="meta_title" placeholder="{{ __('stores.meta_title_placeholder') }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.meta_description') }}</label>
                            <textarea class="edz-input" wire:model="meta_description" rows="2" placeholder="{{ __('stores.meta_description_placeholder') }}"></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.meta_keywords') }}</label>
                            <input type="text" class="edz-input" wire:model="meta_keywords" placeholder="{{ __('stores.meta_keywords_placeholder') }}">
                        </div>
                    </div>
                @endif

                {{-- Step 4: Design --}}
                @if ($step === 4)
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">{{ __('stores.design') }}</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.primary_color') }}</label>
                                <input type="color" class="h-10 w-full rounded border border-surface-border" wire:model="primary_color">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.secondary_color') }}</label>
                                <input type="color" class="h-10 w-full rounded border border-surface-border" wire:model="secondary_color">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink">{{ __('stores.font_family') }}</label>
                            <select class="edz-select" wire:model="font_family">
                                <option value="Cairo">Cairo</option>
                                <option value="Roboto">Roboto</option>
                            </select>
                        </div>
                    </div>
                @endif

                {{-- Step 5: Landing Template --}}
                @if ($step === 5)
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">{{ __('stores.step_template') }}</h2>
                        <p class="text-sm text-ink-muted">{{ __('stores.template_description') }}</p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            @foreach ([
                                'single_product' => __('merchant_panel.template_single'),
                                'catalog'        => __('merchant_panel.template_catalog'),
                                'brand'          => __('merchant_panel.template_brand'),
                            ] as $tplKey => $tplLabel)
                                <label class="edz-card edz-card--padded cursor-pointer border-2 transition-all duration-200 text-center
                                    {{ $landing_template === $tplKey ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                    <input type="radio" name="landing_template" value="{{ $tplKey }}"
                                           wire:model.live="landing_template" class="sr-only" />
                                    <div class="py-3">
                                        <p class="font-semibold text-ink">{{ $tplLabel }}</p>
                                        @if ($landing_template === $tplKey)
                                            <span class="mt-1 inline-block text-xs text-accent-600 font-medium">{{ __('buttons.selected') }}</span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Navigation --}}
                <div class="mt-6 flex items-center justify-between">
                    <div>
                        @if ($step > 1)
                            <button type="button" class="edz-btn edz-btn--ghost" wire:click="prevStep">{{ __('buttons.back') }}</button>
                        @endif
                    </div>
                    <div>
                        @if ($step < 5)
                            <button type="submit" class="edz-btn edz-btn--primary">{{ __('buttons.next') }}</button>
                        @else
                            <button type="submit" class="edz-btn edz-btn--primary edz-btn--lg">{{ __('stores.launch_my_store') }}</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <p class="mt-4 text-center text-sm text-ink-muted">
            <a href="{{ route('merchant.choose-store') }}" class="text-brand-600 hover:underline">{{ __('stores.back_to_selection') }}</a>
        </p>
    </div>
</div>
