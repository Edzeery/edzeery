<?php

use App\Domains\Plan\Services\FeatureUsageService;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StoreStatusEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use function Livewire\Volt\layout;
use function Livewire\Volt\state;

uses([WithFileUploads::class]);

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
    'theme' => 'default',
    'primary_color' => '#000000',
    'secondary_color' => '#ffffff',
    'font_family' => 'Cairo',
]);

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
    $this->step = min($this->step + 1, 4);
};

$prevStep = function (): void {
    $this->step = max($this->step - 1, 1);
};

$createStore = function (): void {
    $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'slug' => ['required', 'string', 'max:255', Rule::unique('stores')->whereNull('deleted_at')],
        'currency' => ['required', 'string', 'max:10'],
    ]);

    $user = auth()->user();
    $subscription = $user->latestSubscription();

    if ($subscription) {
        $featureService = app(FeatureUsageService::class);
        if (! $featureService->canUse($subscription, 'stores_max')) {
            session()->flash('error', __('stores.limit_reached'));
            return;
        }
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($user, $subscription) {
        $store = Store::create([
            'user_id' => $user->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'logo' => $this->logo ? uploadPath($this->logo) : null,
            'cover' => $this->cover ? uploadPath($this->cover) : null,
            'status' => StoreStatusEnum::ACTIVE,
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
            'theme' => $this->theme,
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

        if (! $user->hasRole(StoreRoleEnum::OWNER)) {
            $user->assignRole(StoreRoleEnum::OWNER);
        }
    });

    if ($subscription) {
        app(FeatureUsageService::class)->consume($subscription, 'stores_max');
    }

    session(['current_store_id' => $store->id]);

    $this->redirect(route('merchant.dashboard', $store->slug), navigate: true);
};
?>

<div class="min-h-screen bg-surface-primary flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-2xl">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-ink">Create your store</h1>
            <p class="mt-1 text-ink-muted">Set up your online store in a few steps.</p>
        </div>

        {{-- Steps indicator --}}
        <div class="mb-8 flex items-center justify-center gap-2">
            @foreach ([1 => 'Info', 2 => 'Settings', 3 => 'SEO', 4 => 'Design'] as $s => $label)
                <button type="button" wire:click="$set('step', {{ $s }})"
                        class="flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition
                               {{ $step === $s ? 'bg-brand-600 text-white' : ($step > $s ? 'bg-success-100 text-success-700' : 'bg-surface-secondary text-ink-muted') }}">
                    @if ($step > $s)
                        <x-heroicon-s-check class="h-3.5 w-3.5" />
                    @else
                        {{ $s }}
                    @endif
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="edz-card p-6">
            @if (session('error'))
                <div class="mb-4 rounded-lg bg-danger-50 p-4 text-sm text-danger-700">{{ session('error') }}</div>
            @endif

            <form wire:submit="{{ $step === 4 ? 'createStore' : 'nextStep' }}">
                {{-- Step 1: Store Info --}}
                @if ($step === 1)
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">Store Information</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">Store Name</label>
                                <input type="text" class="edz-input" wire:model.live="name" placeholder="My Store">
                                @error('name')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">Slug</label>
                                <input type="text" class="edz-input" wire:model="slug" readonly>
                                @error('slug')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink">Description</label>
                            <textarea class="edz-input" wire:model="description" rows="3" placeholder="Describe your store…"></textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">Logo</label>
                                <input type="file" class="edz-input" wire:model="logo" accept="image/*">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">Cover</label>
                                <input type="file" class="edz-input" wire:model="cover" accept="image/*">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Step 2: Settings --}}
                @if ($step === 2)
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">General Settings</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">Currency</label>
                                <select class="edz-select" wire:model="currency">
                                    <option value="DZD">DZD</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">Symbol</label>
                                <input type="text" class="edz-input" wire:model="currency_symbol">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">Language</label>
                                <select class="edz-select" wire:model="language">
                                    <option value="ar">Arabic</option>
                                    <option value="en">English</option>
                                    <option value="fr">French</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model="inventory_tracking" class="h-4 w-4 rounded border-surface-border">
                                Inventory Tracking
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model="guest_checkout" class="h-4 w-4 rounded border-surface-border">
                                Guest Checkout
                            </label>
                        </div>
                    </div>
                @endif

                {{-- Step 3: SEO --}}
                @if ($step === 3)
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">SEO</h2>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink">Meta Title</label>
                            <input type="text" class="edz-input" wire:model="meta_title" placeholder="My Store – Online Shopping">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink">Meta Description</label>
                            <textarea class="edz-input" wire:model="meta_description" rows="2" placeholder="Describe your store for search engines…"></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink">Meta Keywords</label>
                            <input type="text" class="edz-input" wire:model="meta_keywords" placeholder="shop, online, algeria">
                        </div>
                    </div>
                @endif

                {{-- Step 4: Design --}}
                @if ($step === 4)
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink">Design</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">Theme</label>
                                <select class="edz-select" wire:model="theme">
                                    <option value="default">Default</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">Primary Color</label>
                                <input type="color" class="h-10 w-full rounded border border-surface-border" wire:model="primary_color">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink">Secondary Color</label>
                                <input type="color" class="h-10 w-full rounded border border-surface-border" wire:model="secondary_color">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink">Font Family</label>
                            <select class="edz-select" wire:model="font_family">
                                <option value="Cairo">Cairo</option>
                                <option value="Roboto">Roboto</option>
                            </select>
                        </div>
                    </div>
                @endif

                {{-- Navigation --}}
                <div class="mt-6 flex items-center justify-between">
                    <div>
                        @if ($step > 1)
                            <button type="button" class="edz-btn edz-btn--ghost" wire:click="prevStep">Back</button>
                        @endif
                    </div>
                    <div>
                        @if ($step < 4)
                            <button type="submit" class="edz-btn edz-btn--primary">Next</button>
                        @else
                            <button type="submit" class="edz-btn edz-btn--primary edz-btn--lg">Launch My Store</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <p class="mt-4 text-center text-sm text-ink-muted">
            <a href="{{ route('choose-store') }}" class="text-brand-600 hover:underline">Back to store selection</a>
        </p>
    </div>
</div>
