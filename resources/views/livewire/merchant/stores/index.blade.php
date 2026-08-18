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

<div>
    <x-edz.page-header
        title="{{ __('titles.stores') }}"
        description="{{ __('merchant_panel.your_stores') }}">
        <x-slot:actions>
            @if ($canCreate)
                <a href="{{ route('merchant.create-store') }}" wire:navigate
                   class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="plus" class="w-4 h-4" />
                    {{ __('buttons.new') }} {{ __('titles.store') }}
                </a>
            @endif
        </x-slot:actions>
    </x-edz.page-header>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($stores as $store)
            <form method="POST" action="{{ route('merchant.choose-store.select', $store['store_slug']) }}" class="contents">
                
                @csrf
                <button type="submit"
                    class="group w-full text-left edz-card edz-card--padded transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-accent-100 text-accent-700 dark:bg-accent-900/30 dark:text-accent-400 font-bold text-lg">
                                @if ($store['store_logo'])
                                    <img src="{{ $store['store_logo'] }}" alt="{{ $store['store_name'] }}" class="w-12 h-12 rounded-xl object-cover" />
                                @else
                                    {{ strtoupper(mb_substr($store['store_name'], 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-ink">{{ $store['store_name'] }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <x-merchant.status domain="roles" :status="$store['membership_role']" />
                                    <x-merchant.status domain="store" :status="$store['store_status']" />
                                </div>
                            </div>
                        </div>
                        <x-edz.icon name="chevron-right" class="w-5 h-5 text-ink-muted group-hover:text-ink transition-colors" />
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-secondary/50 p-3">
                            <p class="text-xs text-ink-muted">{{ __('dashboard.total_memberships') }}</p>
                            <p class="mt-1 text-lg font-bold text-ink">{{ $store['members_count'] }}</p>
                        </div>
                        <div class="rounded-lg bg-secondary/50 p-3">
                            <p class="text-xs text-ink-muted">{{ __('titles.plan') }}</p>
                            <p class="mt-1 text-sm font-semibold text-ink">{{ $store['plan_name'] }}</p>
                        </div>
                    </div>
                </button>
            </form>
        @empty
            <div class="col-span-full">
                <div class="edz-card edz-card--padded text-center py-12">
                    <x-edz.icon name="grid" class="w-12 h-12 text-ink-muted mx-auto mb-4" />
                    <p class="text-ink-muted">{{ __('merchant_panel.no_stores') }}</p>
                    <a href="{{ route('merchant.create-store') }}" wire:navigate
                       class="edz-btn edz-btn--primary edz-btn--sm mt-4">
                        {{ __('buttons.create') }} {{ __('titles.store') }}
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
