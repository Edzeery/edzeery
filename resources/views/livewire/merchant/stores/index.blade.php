<?php

use App\Domains\Merchant\Actions\GetStoreCardsAction;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.account');

state([
    'stores' => [],
    'canCreate' => false,
]);

mount(function (GetStoreCardsAction $action): void {
    $this->stores = $action->execute(user());
    $this->canCreate = user()?->canCreateMultiStore() ?? false;
});
?>

<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-ink">{{ __('titles.stores') }}</h1>
            <p class="text-sm text-ink-soft mt-0.5">{{ __('merchant_panel.your_stores') }}</p>
        </div>
        @if ($canCreate)
            <a href="{{ route('merchant.create-store') }}" wire:navigate
               class="edz-btn edz-btn--primary edz-btn--sm">
                <x-edz.icon name="plus" class="w-4 h-4" />
                {{ __('buttons.new') }} {{ __('titles.store') }}
            </a>
        @endif
    </div>

    {{-- Store Cards --}}
    <div class="space-y-4">
        @forelse ($stores as $store)
            <form method="POST" action="{{ route('merchant.choose-store.select', $store['store_slug']) }}" class="contents">
                @csrf
                <button type="submit"
                    class="group w-full text-left edz-card transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="edz-card--padded">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-accent-500 to-brand-600 flex items-center justify-center text-white text-xl font-bold shadow-md">
                                    @if ($store['store_logo'])
                                        <img src="{{ asset('storage/' . $store['store_logo']) }}" alt="{{ $store['store_name'] }}" class="w-14 h-14 rounded-2xl object-cover" />
                                    @else
                                        {{ strtoupper(mb_substr($store['store_name'], 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-ink text-lg">{{ $store['store_name'] }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <x-merchant.status domain="roles" :status="$store['membership_role']" />
                                        <x-merchant.status domain="store" :status="$store['store_status']" />
                                    </div>
                                </div>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-surface-secondary group-hover:bg-surface-tertiary flex items-center justify-center transition-colors">
                                <x-edz.icon name="chevron-right" class="w-5 h-5 text-ink-muted group-hover:text-ink transition-colors" />
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-surface-secondary p-3">
                                <p class="text-xs text-ink-muted font-medium">{{ __('dashboard.total_memberships') }}</p>
                                <p class="mt-1 text-lg font-bold text-ink">{{ $store['members_count'] }}</p>
                            </div>
                            <div class="rounded-xl bg-surface-secondary p-3">
                                <p class="text-xs text-ink-muted font-medium">{{ __('titles.plan') }}</p>
                                <p class="mt-1 text-sm font-semibold text-ink">{{ $store['plan_name'] }}</p>
                            </div>
                        </div>
                    </div>
                </button>
            </form>
        @empty
            <div class="edz-card edz-card--padded text-center py-16">
                <div class="w-16 h-16 rounded-2xl bg-surface-secondary mx-auto mb-4 flex items-center justify-center">
                    <x-edz.icon name="grid" class="w-8 h-8 text-ink-muted" />
                </div>
                <p class="text-ink-muted font-medium">{{ __('merchant_panel.no_stores') }}</p>
                <a href="{{ route('merchant.create-store') }}" wire:navigate
                   class="edz-btn edz-btn--primary edz-btn--sm mt-4">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('buttons.create') }} {{ __('titles.store') }}
                </a>
            </div>
        @endforelse
    </div>
</div>
