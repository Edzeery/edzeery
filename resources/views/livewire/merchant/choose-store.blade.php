<?php

use App\Domains\Plan\Services\FeatureUsageService;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\SubscriptionPayment\StatusSubscriptionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreContext;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.guest');

state([
    'stores' => [],
    'user' => [],
    'subscription' => null,
    'maxStores' => null,
    'storeCount' => 0,
    'canCreate' => false,
    'effectiveUsage' => 0,
    'isUnlimited' => false,
]);

mount(function (): void {
    $user = auth()->user();

    // 1) Stores owned by the user
    $ownedStoreIds = Store::where('user_id', $user->id)
        ->whereNull('deleted_at')
        ->pluck('id');

    // 2) Stores where user has an active membership (team member)
    $memberStoreIds = StoreMembership::where('user_id', $user->id)
        ->where('is_active', true)
        ->pluck('store_id');

    // 3) Merge + deduplicate
    $storeIds = $ownedStoreIds->merge($memberStoreIds)->unique();

    // Avatar palette (hashed by name/email)
    $palette = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#f43f5e'];

    // 4) Current user identity + own subscription (drives usage card)
    $this->user = [
        'name' => $user->name,
        'email' => $user->email,
        'initial' => mb_strtoupper(mb_substr(trim($user->name !== '' ? $user->name : '?'), 0, 1)),
        'color' => $palette[abs(crc32($user->email)) % count($palette)],
    ];

    $subscription = $user->latestSubscription();
    $featureService = app(FeatureUsageService::class);
    $storesFeature = $subscription?->plan?->features?->firstWhere('slug', 'stores_max');
    $consumption = $storesFeature ? (int) $featureService->getConsumption($subscription, $storesFeature->id) : 0;

    $this->subscription = $subscription ? [
        'plan_name' => $subscription->plan?->name,
        'status' => ($subscription->status ?? StatusSubscriptionEnum::PENDING)->value,
    ] : null;
    $this->maxStores = $subscription?->plan?->getFeatureValue('stores_max');
    $this->isUnlimited = $this->maxStores === 'unlimited';
    $this->canCreate = $subscription ? $featureService->canUse($subscription, 'stores_max') : false;

    // 5) Load owned + member stores with relation counts
    if ($storeIds->isEmpty()) {
        $this->stores = [];
    } else {
        $this->stores = Store::whereIn('id', $storeIds)
            ->with(['owner'])
            ->withCount(['products', 'orders', 'members'])
            ->orderBy('name')
            ->get()
            ->map(function (Store $store) use ($user, $memberStoreIds, $palette) {
                $isOwner = $store->user_id === $user->id;
                $isMember = $memberStoreIds->contains($store->id);

                // Determine role: owner takes priority
                $role = $isOwner
                    ? StoreRoleEnum::OWNER
                    : ($isMember
                        ? $this->getMembershipRole($user, $store)
                        : StoreRoleEnum::STAFF);

                // Subscription comes from the store OWNER, not the current user
                $ownerSubscription = $store->owner?->latestSubscription();

                return [
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'logo' => $store->logo,
                    'status' => $store->status->value,
                    'role' => $role->value,
                    'is_owner' => $isOwner,
                    'initial' => mb_strtoupper(mb_substr(trim($store->name), 0, 1)),
                    'color' => $palette[abs(crc32($store->name)) % count($palette)],
                    'plan_name' => $ownerSubscription?->plan?->name,
                    'plan_status' => ($ownerSubscription?->status ?? StatusSubscriptionEnum::PENDING)->value,
                    'products_count' => (int) $store->products_count,
                    'orders_count' => (int) $store->orders_count,
                    'members_count' => (int) $store->members_count,
                ];
            })
            ->values()
            ->toArray();
    }

    $this->storeCount = count($this->stores);
    $this->effectiveUsage = max($consumption, $this->storeCount);
});

$selectStore = function (string $slug): void {
    $user = auth()->user();

    $store = Store::where('slug', $slug)->first();

    abort_unless($store !== null, 404);

    // Must be owner OR active member
    $isOwner = $store->user_id === $user->id;
    $isMember = StoreMembership::where('store_id', $store->id)
        ->where('user_id', $user->id)
        ->where('is_active', true)
        ->exists();

    abort_unless($isOwner || $isMember, 403);

    session(['current_store_id' => $store->id]);

    app(StoreContext::class)->set($store);

    $this->redirect(route('merchant.dashboard', ['store' => $store->slug]), navigate: true);
};

$getMembershipRole = function ($user, Store $store): StoreRoleEnum {
    $user->guard_name = 'merchant';
    $permissions = $user->getAllPermissions()->pluck('name')->toArray();

    if (in_array('store.delete.final', $permissions)) {
        return StoreRoleEnum::OWNER;
    }
    if (in_array('store.settings.sensitive', $permissions)) {
        return StoreRoleEnum::ADMIN;
    }
    if (in_array('team.manage.own', $permissions)) {
        return StoreRoleEnum::MANAGER;
    }

    return StoreRoleEnum::STAFF;
};
?>

<div class="w-full space-y-5">

    {{-- Page heading --}}
    <div class="text-center animate-fade-up">
        <h1 class="text-2xl font-bold tracking-tight text-ink">
            {{ __('messages.choose_store_title') }}
        </h1>
        <p class="mt-1 text-sm text-ink-muted">{{ __('messages.choose_store_desc') }}</p>
    </div>

    {{-- 1. User profile header --}}
    <div class="flex items-center gap-4 p-4 rounded-2xl shadow-card border border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface animate-fade-up"
         style="animation-delay: 0.05s">

        <div class="w-12 h-12 shrink-0 rounded-full flex items-center justify-center text-white text-lg font-bold shadow-md"
             style="background-color: {{ $this->user['color'] }};">
            {{ $this->user['initial'] }}
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="font-bold text-ink truncate">{{ $this->user['name'] }}</p>
                @if (!empty($this->subscription['plan_name']))
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs font-semibold">
                        <ion-icon name="star" class="text-xs"></ion-icon>
                        {{ $this->subscription['plan_name'] }}
                    </span>
                @endif
            </div>
            <p class="mt-0.5 text-xs text-ink-muted truncate">{{ $this->user['email'] }}</p>
        </div>
    </div>

    {{-- 2. Quick actions row --}}
    <div class="grid grid-cols-3 gap-2 animate-fade-up" style="animation-delay: 0.1s">
        <a href="{{ route('account.profile') }}"
           class="flex flex-col items-center justify-center gap-1 px-2 py-3 rounded-xl border border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface text-xs font-semibold text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 hover:border-brand-300 dark:hover:border-brand-600 transition-all duration-200">
            <ion-icon name="person-outline" class="text-lg"></ion-icon>
            {{ __('buttons.profile') }}
        </a>
        <a href="{{ route('account.billing') }}"
           class="flex flex-col items-center justify-center gap-1 px-2 py-3 rounded-xl border border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface text-xs font-semibold text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 hover:border-brand-300 dark:hover:border-brand-600 transition-all duration-200">
            <ion-icon name="credit-card-outline" class="text-lg"></ion-icon>
            {{ __('buttons.billing') }}
        </a>
        @if ($this->canCreate)
            <a href="{{ route('merchant.create-store') }}"
               class="flex flex-col items-center justify-center gap-1 px-2 py-3 rounded-xl border border-success-200 dark:border-success-800 bg-success-50 dark:bg-success-900/20 text-xs font-semibold text-success-700 dark:text-success-300 hover:bg-success-100 dark:hover:bg-success-900/30 transition-all duration-200">
                <ion-icon name="add-circle-outline" class="text-lg"></ion-icon>
                {{ __('buttons.create') }} {{ __('buttons.new') }}
            </a>
        @endif
    </div>

    {{-- 3. Subscription usage card --}}
    @if (!empty($this->subscription))
        @php
            $maxInt = is_numeric($this->maxStores) ? (int) $this->maxStores : 0;
            $usagePercent = (!$this->isUnlimited && $maxInt > 0) ? min(100, round(($this->effectiveUsage / $maxInt) * 100)) : 100;
            $atLimit = !$this->isUnlimited && $maxInt > 0 && $this->effectiveUsage >= $maxInt;
        @endphp

        <div class="rounded-xl border border-neutral-border dark:border-dark-border bg-neutral-secondary dark:bg-dark-secondary p-4 animate-fade-up"
             style="animation-delay: 0.15s">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-ink">
                        {{ __('stores.stores_used', ['used' => $this->effectiveUsage, 'max' => $this->isUnlimited ? '∞' : $this->maxStores]) }}
                    </p>
                    <p class="mt-0.5 text-xs text-ink-muted">
                        {{ __('plans.max_stores') }}: {{ $this->subscription['plan_name'] ?? '—' }}
                    </p>
                </div>
                @if ($atLimit)
                    <a href="{{ route('account.billing') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 shrink-0 bg-brand-600 text-white text-xs font-semibold rounded-xl hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition">
                        {{ __('stores.upgrade_plan') }}
                    </a>
                @endif
            </div>
            @if (!$this->isUnlimited && $maxInt > 0)
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-neutral-border dark:bg-dark-border">
                    <div class="h-full rounded-full bg-brand-600 transition-all duration-500 ease-out"
                         style="width: {{ $usagePercent }}%"></div>
                </div>
            @endif
        </div>
    @else
        <div class="flex items-center justify-between gap-3 rounded-xl border border-neutral-border dark:border-dark-border bg-neutral-secondary dark:bg-dark-secondary p-4 animate-fade-up"
             style="animation-delay: 0.15s">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 shrink-0 rounded-lg bg-warning-50 dark:bg-warning-900/20 flex items-center justify-center">
                    <ion-icon name="star" class="text-lg text-warning-500"></ion-icon>
                </div>
                <p class="text-sm font-semibold text-ink truncate">
                    {{ __('merchant_panel.no_active_subscription') }}
                </p>
            </div>
            <a href="{{ route('landing') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 shrink-0 bg-brand-600 text-white text-xs font-semibold rounded-xl hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition">
                <ion-icon name="add-circle-outline" class="text-base"></ion-icon>
                {{ __('landing.subscribe_now') }}
            </a>
        </div>
    @endif

    {{-- 4. Stores grid --}}
    @if (count($this->stores) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-fade-up" style="animation-delay: 0.2s">

            @foreach ($this->stores as $store)
                @php
                    $storefrontUrl = (request()->secure() ? 'https' : 'http') . '://' . $store['slug'] . '.' . rtrim(config('app.domain', 'edzeery.com'), '/');
                    $subStatus = StatusSubscriptionEnum::tryFrom((string) ($store['plan_status'] ?? '')) ?? StatusSubscriptionEnum::PENDING;
                    $storeStatus = \App\Enums\Store\StoreStatusEnum::from((string) $store['status']);
                    $storeRole = StoreRoleEnum::from((string) $store['role']);
                @endphp

                <article wire:key="store-card-{{ $store['slug'] }}"
                    class="relative overflow-hidden flex flex-col gap-3 p-4 rounded-2xl shadow-card border border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface hover:border-brand-300 dark:hover:border-brand-600 hover:shadow-md transition-all duration-200 group">

                    {{-- Owned store: subtle brand-colored left border --}}
                    @if ($store['is_owner'])
                        <span class="absolute inset-y-0 start-0 w-1 bg-brand-500" aria-hidden="true"></span>
                    @endif

                    {{-- Store identity --}}
                    <div class="flex items-start gap-3">
                        @if (!empty($store['logo']))
                            <img src="{{ asset('storage/' . $store['logo']) }}" alt="{{ $store['name'] }}"
                                 class="w-11 h-11 shrink-0 rounded-xl object-cover border border-neutral-border dark:border-dark-border">
                        @else
                            <div class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center text-white font-bold"
                                 style="background-color: {{ $store['color'] }};">
                                {{ $store['initial'] }}
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-ink truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition">
                                {{ $store['name'] }}
                            </h3>
                            <a href="{{ $storefrontUrl }}" target="_blank" rel="noopener noreferrer"
                               class="mt-0.5 inline-flex items-center gap-1 text-[11px] text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition truncate max-w-full">
                                <ion-icon name="globe-outline" class="shrink-0"></ion-icon>
                                {{ $store['slug'] }}.{{ rtrim(config('app.domain', 'edzeery.com'), '/') }}
                            </a>
                        </div>
                    </div>

                    {{-- Role + status badges --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <x-role-badge :role="$storeRole" />
                        <x-status-badge domain="general" :status="$storeStatus->value" />
                    </div>

                    {{-- Stats row --}}
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-neutral-secondary dark:bg-dark-secondary p-2 text-center">
                        <div class="flex flex-col items-center gap-0.5">
                            <ion-icon name="cart-outline" class="text-sm text-ink-muted"></ion-icon>
                            <span class="text-xs font-semibold text-ink">{{ number_format($store['products_count']) }}</span>
                            <span class="text-[10px] text-ink-muted">{{ __('titles.products_management') }}</span>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <ion-icon name="receipt-outline" class="text-sm text-ink-muted"></ion-icon>
                            <span class="text-xs font-semibold text-ink">{{ number_format($store['orders_count']) }}</span>
                            <span class="text-[10px] text-ink-muted">{{ __('titles.orders') }}</span>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <ion-icon name="people-outline" class="text-sm text-ink-muted"></ion-icon>
                            <span class="text-xs font-semibold text-ink">{{ number_format($store['members_count']) }}</span>
                            <span class="text-[10px] text-ink-muted">{{ __('teams.title') }}</span>
                        </div>
                    </div>

                    {{-- Store owner subscription --}}
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="truncate text-ink-muted">
                            {{ __('plans.max_stores') }}: <span class="font-semibold text-ink">{{ $store['plan_name'] ?? '—' }}</span>
                        </span>
                        <x-status-badge domain="general" :status="$subStatus->value" />
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 mt-auto pt-1">
                        <button type="button"
                                wire:click="selectStore('{{ $store['slug'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="selectStore('{{ $store['slug'] }}')"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <ion-icon name="storefront-outline" class="text-base"></ion-icon>
                            {{ __('buttons.open') }}
                            <ion-icon name="chevron-forward-outline" class="text-sm group-hover:translate-x-0.5 transition-transform"></ion-icon>
                        </button>
                        <a href="{{ $storefrontUrl }}" target="_blank" rel="noopener noreferrer"
                           title="{{ __('merchant_panel.visit_store') }}"
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-neutral-border dark:border-dark-border text-xs font-semibold text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 hover:border-brand-300 dark:hover:border-brand-600 transition">
                            <ion-icon name="globe-outline" class="text-base"></ion-icon>
                            {{ __('merchant_panel.visit_store') }}
                        </a>
                    </div>
                </article>
            @endforeach

        </div>
    @else
        {{-- 5. Empty state --}}
        <div class="rounded-2xl border-2 border-dashed border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface p-10 text-center animate-fade-up"
             style="animation-delay: 0.2s">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center mb-4">
                <ion-icon name="storefront-outline" class="text-3xl text-brand-500"></ion-icon>
            </div>
            <h3 class="font-semibold text-ink">{{ __('merchant_panel.no_stores_yet') }}</h3>
            <p class="mt-1 text-sm text-ink-muted">{{ __('messages.no_active_store') }}</p>
            <a href="{{ route('merchant.create-store') }}"
               class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition">
                <ion-icon name="add-circle-outline" class="text-lg"></ion-icon>
                {{ __('buttons.create') }} {{ __('buttons.new') }}
            </a>
        </div>
    @endif

    {{-- 6. Logout --}}
    <div class="pt-2 pb-4 text-center animate-fade-up" style="animation-delay: 0.25s">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-ink-muted
                            hover:text-error-600 dark:hover:text-error-400 rounded-xl hover:bg-error-50 dark:hover:bg-error-900/10 transition-all duration-200">
                <ion-icon name="log-out-outline" class="text-base"></ion-icon>
                {{ __('buttons.logout') }}
            </button>
        </form>
    </div>

</div>
