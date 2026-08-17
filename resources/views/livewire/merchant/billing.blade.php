<?php

use App\Models\User;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;

layout('components.layouts.merchant');

mount(function (): void {
    $this->stores = user()->stores()->get();
});
?>

<div>
    <x-edz.page-header
        title="{{ __('titles.billing') }}"
        description="{{ __('titles.current_plan') }}">
    </x-edz.page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($stores as $store)
            <div class="edz-card edz-card--padded">
                <h3 class="font-semibold text-ink">{{ $store->name }}</h3>
                <p class="text-sm text-ink-400 mt-1">
                    {{ __('titles.current_plan') }}: {{ $store->latestSubscription()?->plan?->name ?? '-' }}
                </p>
                <p class="text-sm text-ink-400">
                    {{ __('titles.billing_status') }}:
                    {{ $store->latestPayment()?->isPaid() ? __('titles.paid') : __('titles.unpaid') }}
                </p>
            </div>
        @empty
            <div class="col-span-full">
                <div class="edz-card edz-card--padded text-center">
                    <p class="text-ink-400">{{ __('dashboard.no_data') }}</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
