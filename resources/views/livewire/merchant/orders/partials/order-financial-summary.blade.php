{{--
    Shared order financial summary — single source for the create & edit order forms.
    Renders a read-only grid: subtotal / weight / delivery cost / discount / total.
    Responsive: 1 col @375, 2 cols @768, 5 cols @1440.
--}}
@php
    $subtotal = collect($form['items'] ?? [])->sum(fn($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 0));
    $itemCount = collect($form['items'] ?? [])->sum('quantity');
    $totalWeight = (float) ($form['weight_kg'] ?? 0);

    $discount = 0;
    if (($form['discount_type'] ?? null) && ($form['discount_value'] ?? null)) {
        $discount = $form['discount_type'] === 'amount'
            ? (float) $form['discount_value']
            : round(($subtotal * (float) $form['discount_value']) / 100, 2);
    }
    $grandTotal = max(0, $subtotal - $discount);

    // Read-only delivery cost — mirrors the ShippingCostCalculator used at persist
    // time. Dynamic: reacts to carrier / delivery type / wilaya / commune / items.
    // True free (free_above or no rate for home) shows the free badge; a delivery
    // whose required fields are incomplete shows a "please select" hint instead
    // of a misleading 0.
    $delivery = null;
    $deliveryIncomplete = false;
    $deliveryUnavailable = false;

    $type = (string) ($form['delivery_type'] ?? 'home');
    if (! empty($form['items'])) {
        $canResolve = $type === 'stopdesk'
            ? filled($form['shipping_provider_id'] ?? null) && filled($form['state_id'] ?? null)
            : filled($form['state_id'] ?? null);

        if (! $canResolve) {
            $deliveryIncomplete = true;
        } else {
            try {
                $store = \App\Models\Stores\Store::find(currentStoreId());
                if ($store) {
                    $productIds = collect($form['items'])->pluck('product_id')->filter()->values()->all();
                    $result = app(\App\Domains\Shipping\Services\ShippingCostCalculator::class)
                        ->calculate(
                            $store,
                            $form['state_id'],
                            $form['city_id'] ?? null,
                            $subtotal,
                            $productIds,
                            filled($form['shipping_provider_id'] ?? null) ? $form['shipping_provider_id'] : null,
                            $type,
                        );

                    if (($result['method'] ?? null) === 'office_unavailable') {
                        $deliveryIncomplete = true;
                    } elseif (($result['method'] ?? null) === 'unavailable') {
                        $deliveryUnavailable = true;
                    } else {
                        $delivery = $result;
                    }
                }
            } catch (\Throwable $e) {
                $deliveryIncomplete = true;
            }
        }
    }

    $deliveryCost = (float) ($delivery['cost'] ?? 0);
    $deliveryIsFree = (bool) ($delivery['is_free'] ?? false);
    $deliveryProvider = $delivery['provider_name'] ?? null;
    $deliverySourceType = $delivery['source_type'] ?? null;
@endphp

<div data-financial-grid class="grid grid-cols-1 md:grid-cols-2 min-[1440px]:grid-cols-5 gap-3">
    {{-- Subtotal (read) --}}
    <div data-financial-subtotal class="bg-surface rounded-lg p-3 min-w-0">
        <span class="block text-xs text-ink-muted">{{ __('merchant_panel.subtotal') }}</span>
        <span class="block text-base font-semibold text-ink tabular-nums mt-1">{{ currency($subtotal) }}</span>
        <span class="block text-xs text-ink-muted mt-1">
            {{ __('merchant_panel.items') }}: <span class="font-medium text-ink-soft">{{ $itemCount }}</span>
        </span>
    </div>

    {{-- Weight (read) --}}
    <div data-financial-weight class="bg-surface rounded-lg p-3 min-w-0">
        <span class="block text-xs text-ink-muted">{{ __('merchant_panel.total_weight') }}</span>
        <span class="block text-base font-semibold text-ink tabular-nums mt-1">
            {{ number_format($totalWeight, 2) }} kg
        </span>
        <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
    </div>

    {{-- Delivery cost (read-only, dynamic) --}}
    <div data-financial-delivery class="bg-surface rounded-lg p-3 min-w-0">
        <span class="block text-xs text-ink-muted">{{ __('merchant_panel.delivery_cost') }}</span>
        @if ($deliveryIncomplete)
            <span class="block text-sm font-semibold text-warning-500 mt-1">{{ __('merchant_panel.shipping_hint_delivery') }}</span>
            <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
        @elseif ($deliveryUnavailable)
            <span class="block text-sm font-semibold text-warning-500 mt-1">{{ __('storefront.shipping_unavailable') }}</span>
            <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
        @else
            <span class="block text-base font-semibold tabular-nums mt-1 {{ $deliveryIsFree ? 'text-success-500' : 'text-ink' }}">
                {{ $deliveryIsFree ? __('merchant_panel.free') : currency($deliveryCost) }}
            </span>
            @if (! $deliveryIsFree && $deliveryProvider)
                <span class="block text-xs {{ $deliverySourceType === 'price_list' ? 'text-brand-600' : 'text-ink-muted' }} mt-1">
                    @if ($deliverySourceType === 'price_list')
                        {{ __('merchant_panel.shipping_source_price_list', ['provider' => $deliveryProvider]) }}
                    @elseif ($deliverySourceType === 'company_flat')
                        {{ __('merchant_panel.shipping_source_flat', ['provider' => $deliveryProvider]) }}
                    @else
                        {{ __('merchant_panel.shipping_source_announced', ['provider' => $deliveryProvider]) }}
                    @endif
                </span>
            @else
                <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
            @endif
        @endif
    </div>

    {{-- Discount (read) --}}
    <div data-financial-discount class="bg-surface rounded-lg p-3 min-w-0">
        <span class="block text-xs text-ink-muted">{{ __('merchant_panel.discount') }}</span>
        <span class="block text-base font-semibold tabular-nums mt-1 {{ $discount > 0 ? 'text-danger-500' : 'text-ink-muted' }}">
            {{ $discount > 0 ? '-' . currency($discount) : '—' }}
        </span>
        <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
    </div>

    {{-- Total (read) --}}
    <div data-financial-total class="bg-brand-surface rounded-lg p-3 min-w-0 md:col-span-2 min-[1440px]:col-span-1">
        <span class="block text-xs text-brand-fg/80">{{ __('merchant_panel.total') }}</span>
        <span class="block text-xl font-bold text-brand-fg tabular-nums mt-1">{{ currency($grandTotal) }}</span>
        <span class="block text-xs text-brand-fg/80 mt-1">&nbsp;</span>
    </div>
</div>

{{-- Discount editor (below the grid, kept interactive) --}}
<div class="flex items-center justify-between gap-4 mt-3 pt-3 border-t border-surface-border">
    <div class="flex items-center gap-2">
        <x-edz.select wire:model="form.discount_type" :options="[
            ['value' => '', 'label' => __('merchant_panel.discount')],
            ['value' => 'amount', 'label' => __('merchant_panel.fixed_amount')],
            ['value' => 'percent', 'label' => __('merchant_panel.percentage')],
        ]" size="sm"
            class="w-28" />
        @if ($form['discount_type'])
            <input type="number" wire:model="form.discount_value"
                class="edz-input text-xs py-1 w-20" min="0"
                placeholder="{{ $form['discount_type'] === 'percent' ? '%' : 'DZD' }}">
        @endif
        @if ($form['discount_type'] && $form['discount_value'])
            <input type="text" wire:model="form.discount_reason"
                class="edz-input text-xs py-1 flex-1 max-w-xs"
                placeholder="{{ __('merchant_panel.discount_reason') }}">
        @endif
    </div>
    <span
        class="text-sm font-medium tabular-nums {{ $discount > 0 ? 'text-danger-500' : 'text-ink-muted' }}">
        {{ $discount > 0 ? '-' . currency($discount) : '—' }}
    </span>
</div>