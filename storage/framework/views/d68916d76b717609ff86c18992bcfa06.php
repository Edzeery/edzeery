
<?php
    $subtotal = collect($form['items'] ?? [])->sum(fn($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 0));
    $itemCount = collect($form['items'] ?? [])->sum('quantity');
    $totalWeight = (float) ($form['weight_kg'] ?? 0);

    // Compare-price savings (offer flow) — computed by the domain action.
    $suggestion = app(\App\Domains\Order\Actions\SuggestCompareDiscountAction::class)
        ->execute($form['items'] ?? [], $subtotal);
    $compareSavings = (float) $suggestion['amount'];
    $comparePercent = $suggestion['percent'];

    $autoWeight = round(collect($form['items'] ?? [])->sum(fn($i) => ((float) ($i['weight'] ?? 0)) * (int) ($i['quantity'] ?? 0)), 3);

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
    // A rider leg is never "incomplete": the rider IS the charge for the requested
    // delivery, so show the computed value (0 for a stopdesk lane) instead of the
    // "please select" hint that normally guards the carrier-pricing flow.
    $isRiderLeg = filled($form['delivery_rider_id'] ?? null);
    if (! empty($form['items'])) {
        $canResolve = $isRiderLeg
            ? true
            : ($type === 'stopdesk'
                ? filled($form['shipping_provider_id'] ?? null) && filled($form['state_id'] ?? null)
                : filled($form['state_id'] ?? null));

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
                        if ($isRiderLeg) {
                            $delivery = $result;
                        } else {
                            $deliveryIncomplete = true;
                        }
                    } elseif (($result['method'] ?? null) === 'unavailable') {
                        if ($isRiderLeg) {
                            $delivery = $result;
                        } else {
                            $deliveryUnavailable = true;
                        }
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

    // The total card shows the collectible (goods + delivery − discount) whenever
    // delivery is resolvable; while the carrier-pricing gate is incomplete the
    // total stays goods − discount (no unknown delivery added in).
    if (! $deliveryIncomplete && ! $deliveryUnavailable) {
        $grandTotal = max(0, $subtotal + $deliveryCost - $discount);
    }
?>

<div data-financial-grid class="grid grid-cols-1 md:grid-cols-2 min-[1440px]:grid-cols-5 gap-3">
    
    <div data-financial-subtotal class="bg-surface rounded-lg p-3 min-w-0">
        <span class="block text-xs text-ink-muted"><?php echo e(__('merchant_panel.subtotal')); ?></span>
        <span class="block text-base font-semibold text-ink tabular-nums mt-1"><?php echo e(currency($subtotal)); ?></span>
        <span class="block text-xs text-ink-muted mt-1">
            <?php echo e(__('merchant_panel.items')); ?>: <span class="font-medium text-ink-soft"><?php echo e($itemCount); ?></span>
        </span>
    </div>

    
    <div data-financial-weight class="bg-surface rounded-lg p-3 min-w-0">
        <span class="block text-xs text-ink-muted"><?php echo e(__('merchant_panel.total_weight')); ?></span>
        <input type="number" wire:model="form.weight_kg" step="0.01"
            class="edz-input text-sm mt-1 w-full"
            placeholder="<?php echo e(__('merchant_panel.weight_kg')); ?>">
        <span class="block text-xs text-ink-muted mt-1">
            <?php echo e(__('order_flow.weight_auto_hint')); ?>: <span class="font-medium text-ink-soft"><?php echo e(number_format($autoWeight, 2)); ?> kg</span>
        </span>
        <span class="block text-xs mt-1 text-warning-600">
            <?php echo e(__('merchant_panel.weight_max_limit', ['max' => \App\Models\Orders\Order::resolveMaxWeightKg((string) ($this->form['shipping_provider_id'] ?? ''))])); ?>

        </span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.weight_kg'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div data-financial-delivery class="bg-surface rounded-lg p-3 min-w-0">
        <span class="block text-xs text-ink-muted"><?php echo e(__('merchant_panel.delivery_cost')); ?></span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deliveryIncomplete): ?>
            <span class="block text-sm font-semibold text-warning-500 mt-1"><?php echo e(__('merchant_panel.shipping_hint_delivery')); ?></span>
            <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
        <?php elseif($deliveryUnavailable): ?>
            <span class="block text-sm font-semibold text-warning-500 mt-1"><?php echo e(__('storefront.shipping_unavailable')); ?></span>
            <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
        <?php else: ?>
            <span class="block text-base font-semibold tabular-nums mt-1 <?php echo e($deliveryIsFree ? 'text-success-500' : 'text-ink'); ?>">
                <?php echo e($deliveryIsFree ? __('merchant_panel.free') : currency($deliveryCost)); ?>

            </span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $deliveryIsFree && $deliveryProvider): ?>
                <span class="block text-xs <?php echo e($deliverySourceType === 'price_list' ? 'text-brand-600' : 'text-ink-muted'); ?> mt-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deliverySourceType === 'price_list'): ?>
                        <?php echo e(__('merchant_panel.shipping_source_price_list', ['provider' => $deliveryProvider])); ?>

                    <?php elseif($deliverySourceType === 'company_flat'): ?>
                        <?php echo e(__('merchant_panel.shipping_source_flat', ['provider' => $deliveryProvider])); ?>

                    <?php else: ?>
                        <?php echo e(__('merchant_panel.shipping_source_announced', ['provider' => $deliveryProvider])); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
            <?php else: ?>
                <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div data-financial-discount class="bg-surface rounded-lg p-3 min-w-0">
        <span class="block text-xs text-ink-muted"><?php echo e(__('merchant_panel.discount')); ?></span>
        <div class="flex items-center gap-2 mt-1 min-w-0">
            <input type="number" wire:model="form.discount_value" min="0" step="10"
                class="edz-input text-xs py-1 w-24 shrink-0" placeholder="DZD">
            <span class="text-base font-semibold tabular-nums <?php echo e($discount > 0 ? 'text-danger-500' : 'text-ink-muted'); ?>">
                <?php echo e($discount > 0 ? '-' . currency($discount) : '—'); ?>

            </span>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($form['discount_value'] ?? null) !== null && (string) $form['discount_value'] !== ''): ?>
            <input type="text" wire:model="form.discount_reason"
                class="edz-input text-xs py-1 w-full mt-2" placeholder="<?php echo e(__('merchant_panel.discount_reason')); ?>">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.discount_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <span class="block text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discount > 0): ?>
            <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
        <?php elseif($compareSavings > 0): ?>
            <span class="block text-xs text-success-500 mt-1">
                <?php echo e(__('merchant_panel.compare_price_savings', ['amount' => currency($compareSavings)])); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comparePercent !== null): ?>
                    <span class="text-success-600 font-medium">(<?php echo e(number_format($comparePercent, 1)); ?>%)</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </span>
        <?php else: ?>
            <span class="block text-xs text-ink-muted mt-1"><?php echo e(__('merchant_panel.no_offers_available')); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div data-financial-total class="bg-brand-surface rounded-lg p-3 min-w-0 md:col-span-2 min-[1440px]:col-span-1">
        <span class="block text-xs text-brand-fg/80"><?php echo e(__('merchant_panel.total')); ?></span>
        <span class="block text-xl font-bold text-brand-fg tabular-nums mt-1"><?php echo e(currency($grandTotal)); ?></span>
        <span class="block text-xs text-brand-fg/80 mt-1">&nbsp;</span>
    </div>
</div>

<?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/orders/partials/order-financial-summary.blade.php ENDPATH**/ ?>