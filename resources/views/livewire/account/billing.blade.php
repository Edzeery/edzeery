<?php

use App\Models\User;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;

layout('components.layouts.account');

mount(function (): void {
    $this->stores = user()->stores()->with('latestSubscription.plan', 'latestPayment')->get();
});

$stores = [];
?>

<div>
    <x-edz.page-header
        title="{{ __('merchant_panel.billing') }}"
        description="{{ __('merchant_panel.billing_desc') }}">
    </x-edz.page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($stores as $store)
            <div class="edz-card edz-card--padded">
                <h3 class="font-semibold text-ink">{{ $store->name }}</h3>
                <p class="text-sm text-ink-400 mt-1">
                    {{ __('merchant_panel.plan') }}: {{ $store->latestSubscription()?->plan?->name ?? '-' }}
                </p>
                <p class="text-sm text-ink-400">
                    {{ __('merchant_panel.billing_status') }}:
                    {{ $store->latestPayment()?->isPaid() ? __('merchant_panel.paid') : __('merchant_panel.unpaid') }}
                </p>
            </div>
        @empty
            <div class="col-span-full">
                <div class="edz-card edz-card--padded text-center py-12">
                    <x-edz.icon name="credit-card" class="w-12 h-12 text-ink-muted mx-auto mb-4" />
                    <p class="text-ink-muted">{{ __('merchant_panel.no_stores_yet') }}</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
