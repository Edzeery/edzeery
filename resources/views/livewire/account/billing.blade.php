<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
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

<div class="max-w-3xl mx-auto space-y-6">
    {{-- Current Plan --}}
    @if ($subscription)
        <div class="edz-card">
            <div class="edz-card__header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <x-edz.icon name="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <h3 class="edz-card__title">{{ __('merchant_panel.current_plan') }}</h3>
                        <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.billing_desc') }}</p>
                    </div>
                </div>
            </div>
            <div class="edz-card--padded">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-ink-soft">{{ __('merchant_panel.plan') }}</p>
                        <p class="text-lg font-bold text-ink mt-0.5">{{ $subscription->plan?->name ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-ink-soft">{{ __('merchant_panel.status') }}</p>
                        <div class="mt-1">
                            <x-merchant.status domain="subscriptions" :status="$subscription->status instanceof \BackedEnum ? $subscription->status->value : (string) $subscription->status" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Store Billing --}}
    <div class="edz-card">
        <div class="edz-card__header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                    <x-edz.icon name="credit-card" class="w-5 h-5 text-brand-600 dark:text-brand-400" />
                </div>
                <div>
                    <h3 class="edz-card__title">{{ __('merchant_panel.store_billing') }}</h3>
                    <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.billing_desc') }}</p>
                </div>
            </div>
        </div>

        @forelse ($stores as $store)
            @php
                $latestPayment = $store->payments()->latest('created_at')->first();
                $isPaid = $latestPayment?->isPaid();
            @endphp
            <div class="px-6 py-4 border-t border-surface-border {{ !$loop->last ? '' : '' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-accent-500 to-brand-600 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                            {{ strtoupper(mb_substr($store->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-ink">{{ $store->name }}</p>
                            <p class="text-xs text-ink-muted">{{ $store->slug }}.edzeery.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if ($isPaid)
                            <span class="edz-badge edz-badge--success">
                                <x-edz.icon name="check-circle" class="w-3.5 h-3.5" />
                                {{ __('merchant_panel.paid') }}
                            </span>
                        @else
                            <span class="edz-badge edz-badge--warning">
                                <x-edz.icon name="exclamation-triangle" class="w-3.5 h-3.5" />
                                {{ __('merchant_panel.unpaid') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="w-16 h-16 rounded-2xl bg-surface-secondary mx-auto mb-4 flex items-center justify-center">
                    <x-edz.icon name="credit-card" class="w-8 h-8 text-ink-muted" />
                </div>
                <p class="text-ink-muted font-medium">{{ __('merchant_panel.no_stores_yet') }}</p>
            </div>
        @endforelse
    </div>
</div>
