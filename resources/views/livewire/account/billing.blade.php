<?php

use App\Models\billing\Payment;
use App\Models\billing\Subscription;
use App\Models\Billing\BillingAddress;
use App\Models\Plans\Plan;
use App\Models\Plans\PlanPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function Livewire\Volt\action;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.account');

state([
    'user' => null,
    'subscription' => null,
    'billingAddress' => null,
    'plans' => [],
    'payments' => [],
    'stores' => [],
    'editBilling' => false,
    'billing_name' => '',
    'billing_company' => '',
    'billing_vat_number' => '',
    'billing_country' => '',
    'billing_state' => '',
    'billing_city' => '',
    'billing_address_line_1' => '',
    'billing_address_line_2' => '',
    'billing_zip' => '',
    'billing_phone' => '',
    'selectedBillingPeriod' => 'monthly',
]);

mount(function (): void {
    $u = user();
    $this->user = $u;
    $this->subscription = $u->latestSubscription();
    $this->billingAddress = $u->billingAddress;

    $this->plans = Plan::public()
        ->with(['prices', 'features'])
        ->orderBy('sort_order')
        ->get();

    $this->payments = Payment::where('user_id', $u->id)
        ->with('subscription.plan')
        ->latest('created_at')
        ->get();

    $this->stores = $u->stores()->with('payments')->distinct()->get();
});

$changePlan = action(function (string $planId): void {
    $plan = Plan::findOrFail($planId);
    $period = $this->selectedBillingPeriod;

    if ($this->subscription && $this->subscription->plan_id === $plan->id) {
        $this->dispatch('swal', type: 'info', title: __('merchant_panel.plan_change_requested'));
        return;
    }

    $price = $plan->prices->firstWhere('billing_period', $period);
    if (! $price) {
        $this->dispatch('swal', type: 'error', title: __('merchant_panel.no_plans_available'));
        return;
    }

    DB::transaction(function () use ($plan, $price) {
        $u = $this->user;

        if ($this->subscription && $this->subscription->isActive()) {
            $this->subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'was_switched' => true,
            ]);
        }

        $newSub = $u->subscriptions()->create([
            'plan_id' => $plan->id,
            'plan_price_id' => $price->id,
            'status' => 'active',
            'is_trial' => false,
            'starts_at' => now(),
            'ends_at' => $price->endsAt(),
        ]);

        $newSub->payments()->create([
            'user_id' => $u->id,
            'plan_price_id' => $price->id,
            'status' => 'paid',
            'amount' => $price->price,
            'currency' => $plan->currency ?? 'DZD',
            'paid_at' => now(),
            'gateway' => 'manual',
        ]);

        $this->subscription = $newSub->fresh();
    });

    $this->dispatch('swal', type: 'success', title: __('merchant_panel.plan_change_requested'));
});

$cancelSubscription = action(function (): void {
    $sub = $this->subscription;
    if (! $sub || ! $sub->isActive()) {
        return;
    }
    $sub->update(['status' => 'canceled', 'canceled_at' => now()]);
    $this->subscription = $sub->refresh();
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.subscription_canceled'));
});

$openEditBilling = action(function (): void {
    $this->editBilling = true;
    $ba = $this->billingAddress;
    $this->billing_name = $ba->name ?? $this->user->name ?? '';
    $this->billing_company = $ba->company ?? '';
    $this->billing_vat_number = $ba->vat_number ?? '';
    $this->billing_country = $ba->country ?? '';
    $this->billing_state = $ba->state ?? '';
    $this->billing_city = $ba->city ?? '';
    $this->billing_address_line_1 = $ba->address_line_1 ?? '';
    $this->billing_address_line_2 = $ba->address_line_2 ?? '';
    $this->billing_zip = $ba->zip ?? '';
    $this->billing_phone = $ba->phone ?? '';
});

$saveBilling = action(function (): void {
    $v = Validator::make(
        $this->only([
            'billing_name', 'billing_company', 'billing_vat_number',
            'billing_country', 'billing_state', 'billing_city',
            'billing_address_line_1', 'billing_address_line_2',
            'billing_zip', 'billing_phone',
        ]),
        [
            'billing_name' => ['required', 'string', 'max:255'],
            'billing_company' => ['nullable', 'string', 'max:255'],
            'billing_vat_number' => ['nullable', 'string', 'max:50'],
            'billing_country' => ['nullable', 'string', 'max:2'],
            'billing_state' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:255'],
            'billing_address_line_1' => ['nullable', 'string', 'max:255'],
            'billing_address_line_2' => ['nullable', 'string', 'max:255'],
            'billing_zip' => ['nullable', 'string', 'max:20'],
            'billing_phone' => ['nullable', 'string', 'max:30'],
        ]
    );
    $v->validate();

    BillingAddress::updateOrCreate(
        ['user_id' => $this->user->id],
        [
            'name' => $this->billing_name,
            'company' => $this->billing_company ?: null,
            'vat_number' => $this->billing_vat_number ?: null,
            'country' => $this->billing_country ?: null,
            'state' => $this->billing_state ?: null,
            'city' => $this->billing_city ?: null,
            'address_line_1' => $this->billing_address_line_1 ?: null,
            'address_line_2' => $this->billing_address_line_2 ?: null,
            'zip' => $this->billing_zip ?: null,
            'phone' => $this->billing_phone ?: null,
        ]
    );

    $this->billingAddress = $this->user->billingAddress;
    $this->editBilling = false;
    $this->dispatch('swal', type: 'success', title: __('merchant_panel.settings_saved'));
});
?>

<div class="max-w-4xl mx-auto space-y-6" x-data="edzDirty()">

    {{-- ═══ Section 1: Plan Selection ═══ --}}
    <div class="edz-card">
        <div class="edz-card__header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-success-50 dark:bg-success-900/30 flex items-center justify-center">
                    <x-edz.icon name="check-circle" class="w-5 h-5 text-success-600 dark:text-success-400" />
                </div>
                <div>
                    <h3 class="edz-card__title">{{ __('merchant_panel.change_plan') }}</h3>
                    <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.change_plan_desc') }}</p>
                </div>
            </div>
        </div>

        <div class="edz-card--padded">
            {{-- Billing period toggle --}}
            <div class="flex items-center gap-2 mb-5">
                <button type="button" wire:model.live="selectedBillingPeriod" value="monthly"
                    class="px-4 py-1.5 text-xs font-semibold rounded-lg transition
                        {{ $selectedBillingPeriod === 'monthly' ? 'bg-accent-600 text-white shadow-sm' : 'bg-surface-secondary text-ink-muted hover:text-ink' }}">
                    {{ __('merchant_panel.month') }}
                </button>
                <button type="button" wire:model.live="selectedBillingPeriod" value="yearly"
                    class="px-4 py-1.5 text-xs font-semibold rounded-lg transition
                        {{ $selectedBillingPeriod === 'yearly' ? 'bg-accent-600 text-white shadow-sm' : 'bg-surface-secondary text-ink-muted hover:text-ink' }}">
                    {{ __('merchant_panel.month') }} ({{ __('merchant_panel.expires') }})
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @forelse ($plans as $plan)
                    @php
                        $price = $plan->prices->firstWhere('billing_period', $selectedBillingPeriod)
                            ?? $plan->prices->first();
                        $isCurrent = $subscription?->plan_id === $plan->id;
                    @endphp
                    <button type="button"
                        wire:click="changePlan('{{ $plan->id }}')"
                        class="relative text-start p-5 rounded-xl border-2 transition-all duration-200
                            {{ $isCurrent
                                ? 'border-success-500 bg-success-50 dark:bg-success-900/10 ring-1 ring-success-500/30'
                                : 'border-surface-border hover:border-accent-400 dark:hover:border-accent-500 bg-white dark:bg-ink-900' }}">
                        @if ($isCurrent)
                            <span class="absolute top-3 end-3 edz-badge edz-badge--success">
                                <x-edz.icon name="check-circle" class="w-3 h-3" />
                                {{ __('merchant_panel.current') }}
                            </span>
                        @endif

                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">{{ __($plan->name) }}</p>

                        <div class="mt-3 flex items-baseline gap-1">
                            @if ($price)
                                <span class="text-3xl font-bold text-ink">{{ number_format($price->price, 0) }}</span>
                                <span class="text-sm text-ink-muted">/ {{ $selectedBillingPeriod === 'monthly' ? __('merchant_panel.month') : __('merchant_panel.month') }}</span>
                            @else
                                <span class="text-lg font-semibold text-ink-muted">{{ __('merchant_panel.contact_us') }}</span>
                            @endif
                        </div>

                        @if ($price && $price->isYearly())
                            <p class="mt-1 text-xs text-success-600 dark:text-success-400 font-medium">
                                {{ number_format($price->duration / 12, 0) }} {{ __('merchant_panel.month') }}
                            </p>
                        @endif

                        <p class="mt-2 text-xs text-ink-soft line-clamp-2">{{ $plan->description }}</p>

                        <ul class="mt-4 space-y-2">
                            @foreach ($plan->features->take(5) as $feature)
                                <li class="flex items-start gap-2 text-xs text-ink-soft">
                                    <x-edz.icon name="check-circle" class="w-3.5 h-3.5 text-success-500 mt-0.5 shrink-0" />
                                    <span>{{ __($feature->name) }}: {{ $feature->pivot->value === 'unlimited' ? '∞' : $feature->pivot->value }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </button>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-surface-secondary mx-auto mb-3 flex items-center justify-center">
                            <x-edz.icon name="credit-card" class="w-7 h-7 text-ink-muted" />
                        </div>
                        <p class="text-sm text-ink-muted">{{ __('merchant_panel.no_plans_available') }}</p>
                    </div>
                @endforelse
            </div>

            {{-- Current subscription info --}}
            @if ($subscription && $subscription->plan)
                <div class="mt-5 flex items-center justify-between pt-5 border-t border-surface-border">
                    <div class="flex items-center gap-3">
                        <x-merchant.status domain="subscription" :status="$subscription->status instanceof \BackedEnum ? $subscription->status->value : (string) $subscription->status" />
                        @if ($subscription->ends_at)
                            <span class="text-xs text-ink-muted">
                                {{ __('merchant_panel.expires') }} {{ $subscription->ends_at->format('Y-m-d') }}
                            </span>
                        @endif
                    </div>
                    @if ($subscription->isActive())
                        <button type="button"
                            x-data
                            @click.prevent="if (await EdzSwal.confirmAction('{{ __('merchant_panel.cancel_subscription') }}', '{{ __('merchant_panel.cancel_subscription_confirm') }}')) $wire.cancelSubscription()"
                            class="edz-btn edz-btn--danger edz-btn--sm">
                            {{ __('merchant_panel.cancel_subscription') }}
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ Section 2: Billing Details ═══ --}}
    <div class="edz-card">
        <div class="edz-card__header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-accent-50 dark:bg-accent-900/30 flex items-center justify-center">
                    <x-edz.icon name="credit-card" class="w-5 h-5 text-accent-600 dark:text-accent-400" />
                </div>
                <div>
                    <h3 class="edz-card__title">{{ __('merchant_panel.billing_details') }}</h3>
                    <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.billing_details_desc') }}</p>
                </div>
            </div>
            <button type="button" wire:click="openEditBilling"
                class="edz-btn edz-btn--secondary edz-btn--sm">
                <x-edz.icon name="edit" class="w-4 h-4 me-1" />
                {{ __('buttons.edit') }}
            </button>
        </div>

        <div class="edz-card--padded">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                <div>
                    <dt class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('merchant_panel.name') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">{{ $billingAddress?->name ?? $user->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('merchant_panel.email') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">{{ $user->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('merchant_panel.country') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">{{ $billingAddress?->country ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('merchant_panel.state') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">{{ $billingAddress?->state ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('merchant_panel.city') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">{{ $billingAddress?->city ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('merchant_panel.zip') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">{{ $billingAddress?->zip ?? '-' }}</dd>
                </div>
                @if ($billingAddress?->address_line_1)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('general.address') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">
                            {{ $billingAddress->address_line_1 }}
                            @if ($billingAddress->address_line_2), {{ $billingAddress->address_line_2 }} @endif
                        </dd>
                    </div>
                @endif
                @if ($billingAddress?->company)
                    <div>
                        <dt class="text-xs font-medium text-ink-muted uppercase tracking-wider">{{ __('merchant_panel.plan') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ $billingAddress->company }}</dd>
                    </div>
                @endif
                @if ($billingAddress?->vat_number)
                    <div>
                        <dt class="text-xs font-medium text-ink-muted uppercase tracking-wider">VAT</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ $billingAddress->vat_number }}</dd>
                    </div>
                @endif
            </dl>

            <p class="mt-5 pt-4 border-t border-surface-border text-xs text-ink-muted">
                {{ __('merchant_panel.no_refund_policy') }}
            </p>
        </div>
    </div>

    {{-- ═══ Section 3: Invoice History ═══ --}}
    <div class="edz-card">
        <div class="edz-card__header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center">
                    <x-edz.icon name="download" class="w-5 h-5 text-brand-600 dark:text-brand-400" />
                </div>
                <div>
                    <h3 class="edz-card__title">{{ __('merchant_panel.invoice_history') }}</h3>
                    <p class="text-xs text-ink-muted mt-0.5">{{ __('merchant_panel.invoice_history_desc') }}</p>
                </div>
            </div>
        </div>

        @forelse ($payments as $payment)
            <div class="px-6 py-4 {{ !$loop->last ? 'border-t border-surface-border' : '' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-surface-secondary flex items-center justify-center">
                            <x-edz.icon name="credit-card" class="w-5 h-5 text-ink-muted" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-ink">
                                {{ $payment->subscription?->plan?->name ?? '-' }}
                            </p>
                            <p class="text-xs text-ink-muted">
                                {{ $payment->created_at->format('Y-m-d') }}
                                @if ($payment->transaction_id)
                                    · {{ $payment->transaction_id }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-ink">
                            {{ number_format($payment->amount, 2) }} {{ $payment->currency ?? 'DZD' }}
                        </span>
                        <x-merchant.status domain="payment" :status="$payment->status instanceof \BackedEnum ? $payment->status->value : (string) $payment->status" />
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-surface-secondary mx-auto mb-3 flex items-center justify-center">
                    <x-edz.icon name="download" class="w-7 h-7 text-ink-muted" />
                </div>
                <p class="text-sm text-ink-muted font-medium">{{ __('merchant_panel.no_invoices_yet') }}</p>
            </div>
        @endforelse
    </div>

    {{-- ═══ Section 4: Store Billing ═══ --}}
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
            <div class="px-6 py-4 border-t border-surface-border">
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

    {{-- ═══ Edit Billing Modal ═══ --}}
    <x-edz.modal :isOpen="$editBilling">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-ink mb-4">{{ __('merchant_panel.edit_billing_details') }}</h3>

            <form wire:submit="saveBilling" class="space-y-4">
                <div class="edz-field">
                    <label class="edz-field__label">{{ __('merchant_panel.name') }} *</label>
                    <input type="text" wire:model="billing_name" class="edz-input @error('billing_name') edz-input--error @enderror" required />
                    @error('billing_name') <span class="edz-field__error">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="edz-field">
                        <label class="edz-field__label">{{ __('general.company') }}</label>
                        <input type="text" wire:model="billing_company" class="edz-input" />
                    </div>
                    <div class="edz-field">
                        <label class="edz-field__label">VAT {{ __('merchant_panel.zip') }}</label>
                        <input type="text" wire:model="billing_vat_number" class="edz-input" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="edz-field">
                        <label class="edz-field__label">{{ __('merchant_panel.country') }}</label>
                        <input type="text" wire:model="billing_country" class="edz-input" maxlength="2" placeholder="DZ" />
                    </div>
                    <div class="edz-field">
                        <label class="edz-field__label">{{ __('merchant_panel.state') }}</label>
                        <input type="text" wire:model="billing_state" class="edz-input" />
                    </div>
                    <div class="edz-field">
                        <label class="edz-field__label">{{ __('merchant_panel.city') }}</label>
                        <input type="text" wire:model="billing_city" class="edz-input" />
                    </div>
                    <div class="edz-field">
                        <label class="edz-field__label">{{ __('merchant_panel.zip') }}</label>
                        <input type="text" wire:model="billing_zip" class="edz-input" />
                    </div>
                </div>

                <div class="edz-field">
                    <label class="edz-field__label">{{ __('general.address') }}</label>
                    <input type="text" wire:model="billing_address_line_1" class="edz-input @error('billing_address_line_1') edz-input--error @enderror" />
                    @error('billing_address_line_1') <span class="edz-field__error">{{ $message }}</span> @enderror
                </div>

                <div class="edz-field">
                    <label class="edz-field__label">{{ __('general.address') }} 2</label>
                    <input type="text" wire:model="billing_address_line_2" class="edz-input" />
                </div>

                <div class="edz-field">
                    <label class="edz-field__label">{{ __('merchant_panel.phone') }}</label>
                    <input type="text" wire:model="billing_phone" class="edz-input" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-border">
                    <button type="button" @click="open = false" class="edz-btn edz-btn--ghost">
                        {{ __('buttons.cancel') }}
                    </button>
                    <button type="submit" class="edz-btn edz-btn--primary">
                        <x-edz.icon name="check-circle" class="w-4 h-4" />
                        {{ __('buttons.save') }}
                    </button>
                </div>
            </form>
        </div>
    </x-edz.modal>
</div>
