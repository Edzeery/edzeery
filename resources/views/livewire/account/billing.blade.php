<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\with;

use function Livewire\Volt\state;

layout('components.layouts.account');

state([
    'user' => null,
    'subscription' => null,
    'stores' => [],
]);

mount(function (): void {
    $this->user = user();
    $this->subscription = user()->latestSubscription();
    $this->stores = user()->stores()->with('payments')->distinct()->get();
});
?>

<div>
    <x-edz.page-header
        title="{{ __('merchant_panel.billing') }}"
        description="{{ __('merchant_panel.billing_desc') }}">
    </x-edz.page-header>

    @if ($subscription)
        <div class="edz-card edz-card--padded mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-ink">{{ __('merchant_panel.plan') }}: {{ $subscription->plan?->name ?? '-' }}</h3>
                    <p class="text-sm text-ink-400 mt-1">
                        {{ __('merchant_panel.billing_status') }}:
                        <x-merchant.status domain="subscriptions" :status="$subscription->status instanceof \BackedEnum ? $subscription->status->value : (string) $subscription->status" />
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($stores as $store)
            <div class="edz-card edz-card--padded">
                <h3 class="font-semibold text-ink">{{ $store->name }}</h3>
                <p class="text-sm text-ink-400 mt-1">
                    {{ __('merchant_panel.billing_status') }}:
                    @php
                        $latestPayment = $store->payments()->latest('created_at')->first();
                    @endphp
                    {{ $latestPayment?->isPaid() ? __('merchant_panel.paid') : __('merchant_panel.unpaid') }}
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
