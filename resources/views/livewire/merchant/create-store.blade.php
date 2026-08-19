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

    $store = \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
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

        $user->guard_name = 'merchant';

        if (! $user->hasRole(StoreRoleEnum::OWNER->value, 'merchant')) {
            $user->assignRole(StoreRoleEnum::OWNER->value);
        }

        return $store;
    });

    $subscription = $user->latestSubscription();
    app(FeatureUsageService::class)->consume($subscription, 'stores_max');

    session(['current_store_id' => $store->id]);

    $this->redirect(route('merchant.dashboard', $store->slug), navigate: true);
};
?>

<div class="min-h-[80vh] flex items-center justify-center px-4 py-8"
     x-data="{
         transitioning: false,
         direction: 'forward',
         goTo(step) {
             if (step === $wire.step) return;
             this.direction = step > $wire.step ? 'forward' : 'backward';
             this.transitioning = true;
             setTimeout(() => {
                 $wire.set('step', step);
                 setTimeout(() => { this.transitioning = false; }, 50);
             }, 150);
         }
     }">

    <div class="w-full max-w-xl">

        {{-- Header --}}
        <div class="text-center mb-8 animate-fade-up">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-100 dark:bg-brand-900/30 mb-4">
                <ion-icon name="storefront-outline" class="text-2xl text-brand-600 dark:text-brand-400"></ion-icon>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-ink tracking-tight">
                {{ __('stores.create_your_store') }}
            </h1>
            <p class="text-sm text-ink-muted mt-2">
                {{ __('stores.setup_steps_hint') }}
            </p>
        </div>

        {{-- Step Progress --}}
        <div class="mb-8 animate-fade-up" style="animation-delay: 0.1s">
            {{-- Mobile: compact --}}
            <div class="sm:hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-ink-muted">
                        {{ __('stores.step_of', ['current' => $step, 'total' => 5]) }}
                    </span>
                    <span class="text-xs font-medium text-brand-600 dark:text-brand-400">
                        {{ [1 => __('stores.step_info'), 2 => __('stores.step_settings'), 3 => __('stores.step_seo'), 4 => __('stores.step_design'), 5 => __('stores.step_template')][$step] }}
                    </span>
                </div>
                <div class="h-1.5 w-full bg-neutral-secondary dark:bg-dark-secondary rounded-full overflow-hidden">
                    <div class="h-full bg-brand-600 rounded-full transition-all duration-500 ease-out"
                         style="width: {{ ($step / 5) * 100 }}%"></div>
                </div>
            </div>

            {{-- Desktop: full stepper --}}
            <div class="hidden sm:flex items-center justify-between relative">
                {{-- Connecting line --}}
                <div class="absolute top-5 left-[10%] right-[10%] h-0.5 bg-neutral-secondary dark:bg-dark-secondary"></div>
                <div class="absolute top-5 left-[10%] h-0.5 bg-brand-600 transition-all duration-500 ease-out"
                     style="width: {{ (($step - 1) / 4) * 80 }}%"></div>

                @foreach ([1 => __('stores.step_info'), 2 => __('stores.step_settings'), 3 => __('stores.step_seo'), 4 => __('stores.step_design'), 5 => __('stores.step_template')] as $s => $label)
                    <div class="relative z-10 flex flex-col items-center cursor-pointer group" wire:key="step-{{ $s }}">
                        <button type="button"
                                @click="goTo({{ $s }})"
                                class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold border-2 transition-all duration-300
                                    @if ($step > $s)
                                        bg-brand-600 border-brand-600 text-white shadow-sm
                                    @elseif ($step === $s)
                                        bg-brand-600 border-brand-600 text-white shadow-md ring-4 ring-brand-100 dark:ring-brand-900/30
                                    @else
                                        bg-surface-primary dark:bg-dark-surface border-neutral-border dark:border-dark-border text-ink-muted group-hover:border-brand-300 group-hover:text-brand-600
                                    @endif">
                            @if ($step > $s)
                                <ion-icon name="checkmark-outline" class="text-base"></ion-icon>
                            @else
                                {{ $s }}
                            @endif
                        </button>
                        <span class="mt-2 text-xs font-medium text-center
                            @if ($step >= $s) text-ink @else text-ink-muted @endif">
                            {{ $label }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border rounded-2xl shadow-card animate-fade-up"
             style="animation-delay: 0.15s">
            <form wire:submit="{{ $step === 5 ? 'createStore' : 'nextStep' }}" x-data="edzDirty()">

                <div class="p-6 sm:p-8">
                    {{-- Step transitions --}}
                    <div class="relative overflow-hidden min-h-[320px]">

                        {{-- Step 1: Store Info --}}
                        @if ($step === 1)
                            <div class="space-y-5"
                                 x-show="true"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">

                                <div>
                                    <h2 class="text-lg font-semibold text-ink tracking-tight">
                                        {{ __('stores.store_information') }}
                                    </h2>
                                    <p class="text-sm text-ink-muted mt-0.5">{{ __('stores.step_1_desc') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.store_name') }}</label>
                                    <input type="text"
                                           class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition placeholder:text-ink-soft"
                                           wire:model.live="name"
                                           placeholder="{{ __('stores.name_placeholder') }}">
                                    @error('name')
                                        <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.slug') }}</label>
                                    <div class="flex items-center gap-0">
                                        <span class="inline-flex items-center px-3 py-2.5 rounded-l-xl border border-r-0 border-neutral-border dark:border-dark-border bg-neutral-secondary dark:bg-dark-secondary text-sm text-ink-muted">
                                            {{ request()->getHost() }}/
                                        </span>
                                        <input type="text"
                                               class="flex-1 px-4 py-2.5 rounded-r-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm font-medium
                                                      focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                                               wire:model="slug" readonly>
                                    </div>
                                    @error('slug')
                                        <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.description') }}</label>
                                    <textarea class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm min-h-[80px] resize-y
                                                   focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition placeholder:text-ink-soft"
                                              wire:model="description"
                                              placeholder="{{ __('stores.description_placeholder') }}"></textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    {{-- Logo upload --}}
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.logo') }}</label>
                                        <label class="flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border
                                                      hover:border-brand-400 dark:hover:border-brand-500 cursor-pointer transition group bg-surface-secondary dark:bg-dark-secondary">
                                            @if ($logo)
                                                <div class="relative w-full h-full flex items-center justify-center p-2">
                                                    <img src="{{ $logo->temporaryUrl() }}" class="max-h-full max-w-full object-contain rounded-lg">
                                                    <button type="button" wire:click="$set('logo', null)"
                                                            class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center text-xs hover:bg-red-600 transition">
                                                        <ion-icon name="close-outline" class="text-sm"></ion-icon>
                                                    </button>
                                                </div>
                                            @else
                                                <ion-icon name="image-outline" class="text-2xl text-ink-soft group-hover:text-brand-500 transition mb-1"></ion-icon>
                                                <span class="text-xs text-ink-muted text-center px-2">{{ __('stores.drag_drop_logo') }}</span>
                                                <span class="text-[10px] text-ink-soft mt-0.5">{{ __('stores.image_formats_hint') }}</span>
                                            @endif
                                            <input type="file" wire:model="logo" accept="image/*" class="sr-only">
                                        </label>
                                        @error('logo')
                                            <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Cover upload --}}
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.cover') }}</label>
                                        <label class="flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border
                                                      hover:border-brand-400 dark:hover:border-brand-500 cursor-pointer transition group bg-surface-secondary dark:bg-dark-secondary">
                                            @if ($cover)
                                                <div class="relative w-full h-full flex items-center justify-center p-2">
                                                    <img src="{{ $cover->temporaryUrl() }}" class="max-h-full max-w-full object-cover rounded-lg">
                                                    <button type="button" wire:click="$set('cover', null)"
                                                            class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center text-xs hover:bg-red-600 transition">
                                                        <ion-icon name="close-outline" class="text-sm"></ion-icon>
                                                    </button>
                                                </div>
                                            @else
                                                <ion-icon name="cloud-upload-outline" class="text-2xl text-ink-soft group-hover:text-brand-500 transition mb-1"></ion-icon>
                                                <span class="text-xs text-ink-muted text-center px-2">{{ __('stores.drag_drop_cover') }}</span>
                                                <span class="text-[10px] text-ink-soft mt-0.5">{{ __('stores.image_formats_hint') }}</span>
                                            @endif
                                            <input type="file" wire:model="cover" accept="image/*" class="sr-only">
                                        </label>
                                        @error('cover')
                                            <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Step 2: Settings --}}
                        @if ($step === 2)
                            <div class="space-y-5"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">

                                <div>
                                    <h2 class="text-lg font-semibold text-ink tracking-tight">
                                        {{ __('stores.general_settings') }}
                                    </h2>
                                    <p class="text-sm text-ink-muted mt-0.5">{{ __('stores.step_2_desc') }}</p>
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.currency') }}</label>
                                        <select class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm appearance-none
                                                       focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                                                wire:model="currency">
                                            <option value="DZD">DZD</option>
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.symbol') }}</label>
                                        <input type="text"
                                               class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm
                                                      focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                                               wire:model="currency_symbol">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.language') }}</label>
                                        <select class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm appearance-none
                                                       focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                                                wire:model="language">
                                            <option value="ar">{{ __('stores.lang_arabic') }}</option>
                                            <option value="en">{{ __('stores.lang_english') }}</option>
                                            <option value="fr">{{ __('stores.lang_french') }}</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Toggles --}}
                                <div class="space-y-3">
                                    <label class="flex items-center justify-between p-4 rounded-xl bg-surface-secondary dark:bg-dark-secondary border border-neutral-border/50 dark:border-dark-border/50 cursor-pointer group hover:border-brand-300 dark:hover:border-brand-600 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                                <ion-icon name="cube-outline" class="text-brand-600 dark:text-brand-400 text-lg"></ion-icon>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-ink">{{ __('stores.inventory_tracking') }}</span>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input type="checkbox" wire:model="inventory_tracking" class="sr-only peer">
                                            <div class="w-11 h-6 bg-neutral-border dark:bg-dark-border rounded-full peer-checked:bg-brand-600 transition-colors"></div>
                                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-sm transform peer-checked:translate-x-5 transition-transform"></div>
                                        </div>
                                    </label>

                                    <label class="flex items-center justify-between p-4 rounded-xl bg-surface-secondary dark:bg-dark-secondary border border-neutral-border/50 dark:border-dark-border/50 cursor-pointer group hover:border-brand-300 dark:hover:border-brand-600 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                                <ion-icon name="person-outline" class="text-brand-600 dark:text-brand-400 text-lg"></ion-icon>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-ink">{{ __('stores.guest_checkout') }}</span>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input type="checkbox" wire:model="guest_checkout" class="sr-only peer">
                                            <div class="w-11 h-6 bg-neutral-border dark:bg-dark-border rounded-full peer-checked:bg-brand-600 transition-colors"></div>
                                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-sm transform peer-checked:translate-x-5 transition-transform"></div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endif

                        {{-- Step 3: SEO --}}
                        @if ($step === 3)
                            <div class="space-y-5"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">

                                <div>
                                    <h2 class="text-lg font-semibold text-ink tracking-tight">
                                        {{ __('stores.seo') }}
                                    </h2>
                                    <p class="text-sm text-ink-muted mt-0.5">{{ __('stores.step_3_desc') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.meta_title') }}</label>
                                    <input type="text"
                                           class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition placeholder:text-ink-soft"
                                           wire:model="meta_title"
                                           placeholder="{{ __('stores.meta_title_placeholder') }}">
                                    @error('meta_title')
                                        <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.meta_description') }}</label>
                                    <textarea class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm min-h-[72px] resize-y
                                                   focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition placeholder:text-ink-soft"
                                              wire:model="meta_description"
                                              placeholder="{{ __('stores.meta_description_placeholder') }}"></textarea>
                                    @error('meta_description')
                                        <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.meta_keywords') }}</label>
                                    <input type="text"
                                           class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition placeholder:text-ink-soft"
                                           wire:model="meta_keywords"
                                           placeholder="{{ __('stores.meta_keywords_placeholder') }}">
                                </div>
                            </div>
                        @endif

                        {{-- Step 4: Design --}}
                        @if ($step === 4)
                            <div class="space-y-5"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">

                                <div>
                                    <h2 class="text-lg font-semibold text-ink tracking-tight">
                                        {{ __('stores.design') }}
                                    </h2>
                                    <p class="text-sm text-ink-muted mt-0.5">{{ __('stores.step_4_desc') }}</p>
                                </div>

                                {{-- Color Pickers --}}
                                <div class="grid grid-cols-2 gap-4">
                                    {{-- Primary Color --}}
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.primary_color') }}</label>
                                        <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-secondary dark:bg-dark-secondary border border-neutral-border/50 dark:border-dark-border/50">
                                            <div class="relative">
                                                <input type="color" wire:model="primary_color"
                                                       class="w-12 h-12 rounded-xl border-2 border-white dark:border-gray-700 shadow-sm cursor-pointer appearance-none bg-transparent [&::-webkit-color-swatch-wrapper]:p-0 [&::-webkit-color-swatch]:rounded-lg [&::-webkit-color-swatch]:border-none">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="h-3 rounded-full mb-1.5" style="background-color: {{ $primary_color }}"></div>
                                                <span class="text-xs text-ink-muted font-mono uppercase">{{ $primary_color }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Secondary Color --}}
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.secondary_color') }}</label>
                                        <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-secondary dark:bg-dark-secondary border border-neutral-border/50 dark:border-dark-border/50">
                                            <div class="relative">
                                                <input type="color" wire:model="secondary_color"
                                                       class="w-12 h-12 rounded-xl border-2 border-white dark:border-gray-700 shadow-sm cursor-pointer appearance-none bg-transparent [&::-webkit-color-swatch-wrapper]:p-0 [&::-webkit-color-swatch]:rounded-lg [&::-webkit-color-swatch]:border-none">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="h-3 rounded-full mb-1.5" style="background-color: {{ $secondary_color }}"></div>
                                                <span class="text-xs text-ink-muted font-mono uppercase">{{ $secondary_color }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick color presets --}}
                                <div>
                                    <span class="text-xs font-medium text-ink-muted mb-2 block">{{ __('stores.color_preview') }}</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach (['#000000', '#465fff', '#039855', '#d92d20', '#b54708', '#7a2e0e', '#6366f1', '#0ea5e9'] as $preset)
                                            <button type="button"
                                                    wire:click="$set('primary_color', '{{ $preset }}')"
                                                    class="w-8 h-8 rounded-lg border-2 transition-all duration-200 hover:scale-110
                                                           {{ $primary_color === $preset ? 'border-ink dark:border-white shadow-md scale-110' : 'border-transparent' }}"
                                                    style="background-color: {{ $preset }}">
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Font Family --}}
                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5">{{ __('stores.font_family') }}</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        @foreach (['Cairo' => __('stores.font_cairo'), 'Inter' => __('stores.font_inter'), 'Tajawal' => __('stores.font_tajawal')] as $fontKey => $fontLabel)
                                            <button type="button"
                                                    wire:click="$set('font_family', '{{ $fontKey }}')"
                                                    class="p-3 rounded-xl border-2 text-center transition-all duration-200
                                                           {{ $font_family === $fontKey
                                                              ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20 shadow-sm'
                                                              : 'border-neutral-border dark:border-dark-border hover:border-brand-300 dark:hover:border-brand-600' }}">
                                                <span class="text-sm font-semibold text-ink" style="font-family: '{{ $fontKey }}', sans-serif">
                                                    {{ $fontLabel }}
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Live preview bar --}}
                                <div class="p-4 rounded-xl border border-neutral-border/50 dark:border-dark-border/50 bg-surface-secondary/50 dark:bg-dark-secondary/50">
                                    <span class="text-[11px] font-medium text-ink-muted uppercase tracking-wider mb-2 block">{{ __('stores.color_preview') }}</span>
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 px-4 rounded-lg text-white text-sm font-semibold flex items-center"
                                             style="background-color: {{ $primary_color }}">
                                            Button
                                        </div>
                                        <div class="h-8 px-4 rounded-lg text-sm font-semibold border"
                                             style="border-color: {{ $primary_color }}; color: {{ $primary_color }}">
                                            Outline
                                        </div>
                                        <div class="flex-1 h-2 rounded-full" style="background: linear-gradient(to right, {{ $primary_color }}, {{ $secondary_color }})"></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Step 5: Template --}}
                        @if ($step === 5)
                            <div class="space-y-5"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">

                                <div>
                                    <h2 class="text-lg font-semibold text-ink tracking-tight">
                                        {{ __('stores.step_template') }}
                                    </h2>
                                    <p class="text-sm text-ink-muted mt-0.5">{{ __('stores.template_description') }}</p>
                                </div>

                                @php
                                    $templates = [
                                        'single_product' => [
                                            'label' => __('merchant_panel.template_single'),
                                            'desc' => __('stores.template_single_desc'),
                                            'icon' => 'cube-outline',
                                        ],
                                        'catalog' => [
                                            'label' => __('merchant_panel.template_catalog'),
                                            'desc' => __('stores.template_catalog_desc'),
                                            'icon' => 'grid-outline',
                                        ],
                                        'brand' => [
                                            'label' => __('merchant_panel.template_brand'),
                                            'desc' => __('stores.template_brand_desc'),
                                            'icon' => 'colorPalette-outline',
                                        ],
                                    ];
                                @endphp

                                <div class="space-y-3">
                                    @foreach ($templates as $tplKey => $tpl)
                                        <button type="button"
                                                wire:click="$set('landing_template', '{{ $tplKey }}')"
                                                class="w-full flex items-start gap-4 p-4 rounded-xl border-2 text-start transition-all duration-200
                                                       @if ($landing_template === $tplKey)
                                                           border-brand-500 bg-brand-50/50 dark:bg-brand-900/15 shadow-sm ring-1 ring-brand-500/20
                                                       @else
                                                           border-neutral-border dark:border-dark-border hover:border-brand-300 dark:hover:border-brand-600 hover:bg-surface-secondary/50 dark:hover:bg-dark-secondary/50
                                                       @endif">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5
                                                        @if ($landing_template === $tplKey) bg-brand-100 dark:bg-brand-900/30 @else bg-neutral-secondary dark:bg-dark-secondary @endif">
                                                <ion-icon name="{{ $tpl['icon'] }}"
                                                          class="text-lg @if ($landing_template === $tplKey) text-brand-600 dark:text-brand-400 @else text-ink-muted @endif">
                                                </ion-icon>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-ink">{{ $tpl['label'] }}</span>
                                                    @if ($landing_template === $tplKey)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-brand-100 dark:bg-brand-900/30 text-[10px] font-semibold text-brand-700 dark:text-brand-300">
                                                            <ion-icon name="checkmark-circle" class="text-xs"></ion-icon>
                                                            {{ __('buttons.selected') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-ink-muted mt-0.5 leading-relaxed">{{ $tpl['desc'] }}</p>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- Navigation Footer --}}
                <div class="px-6 sm:px-8 py-4 border-t border-neutral-border/50 dark:border-dark-border/50 flex items-center justify-between">
                    <div>
                        @if ($step > 1)
                            <button type="button"
                                    wire:click="prevStep"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-ink-muted
                                           hover:text-ink hover:bg-neutral-secondary dark:hover:bg-dark-secondary transition">
                                <ion-icon name="arrow-back-outline" class="text-base"></ion-icon>
                                {{ __('buttons.back') }}
                            </button>
                        @endif
                    </div>
                    <div>
                        @if ($step < 5)
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold
                                           hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition">
                                {{ __('buttons.next') }}
                                <ion-icon name="arrow-forward-outline" class="text-base"></ion-icon>
                            </button>
                        @else
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-brand-600 text-white text-sm font-semibold
                                           hover:bg-brand-700 shadow-md shadow-brand-600/25 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="createStore">
                                    <ion-icon name="rocket-outline" class="text-base"></ion-icon>
                                </span>
                                <span wire:loading wire:target="createStore" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    {{ __('buttons.processing') }}
                                </span>
                                {{ __('stores.launch_my_store') }}
                            </button>
                        @endif
                    </div>
                </div>

            </form>
        </div>

        {{-- Back link --}}
        <div class="text-center mt-6 animate-fade-up" style="animation-delay: 0.2s">
            <a href="{{ route('merchant.choose-store') }}"
               class="inline-flex items-center gap-1.5 text-sm text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition">
                <ion-icon name="arrow-back-outline" class="text-sm"></ion-icon>
                {{ __('stores.back_to_selection') }}
            </a>
        </div>

    </div>
</div>
