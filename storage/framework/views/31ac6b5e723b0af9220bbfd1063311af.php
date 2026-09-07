
<?php
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

    // Read-only delivery cost — mirrors the ShippingCostCalculator used at persist time.
    // Only resolvable for home deliveries with a state selected; otherwise free (0).
    $deliveryCost = 0.0;
    $deliveryIsFree = true;
    if (($form['delivery_type'] ?? 'home') === 'home' && !empty($form['state_id']) && !empty($form['items'])) {
        try {
            $store = \App\Models\Stores\Store::find(currentStoreId());
            if ($store) {
                $productIds = collect($form['items'])->pluck('product_id')->filter()->values()->all();
                $result = app(\App\Domains\Shipping\Services\ShippingCostCalculator::class)
                    ->calculate($store, $form['state_id'], $form['city_id'] ?? null, $subtotal, $productIds);
                $deliveryCost = (float) ($result['cost'] ?? 0);
                $deliveryIsFree = $deliveryCost <= 0 || (bool) ($result['is_free'] ?? false);
            }
        } catch (\Throwable $e) {
            $deliveryCost = 0.0;
            $deliveryIsFree = true;
        }
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
        <span class="block text-base font-semibold text-ink tabular-nums mt-1">
            <?php echo e(number_format($totalWeight, 2)); ?> kg
        </span>
        <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
    </div>

    
    <div data-financial-delivery class="bg-surface rounded-lg p-3 min-w-0">
        <span class="block text-xs text-ink-muted"><?php echo e(__('merchant_panel.delivery_cost')); ?></span>
        <span class="block text-base font-semibold tabular-nums mt-1 <?php echo e($deliveryIsFree ? 'text-success-500' : 'text-ink'); ?>">
            <?php echo e($deliveryIsFree ? __('merchant_panel.free') : currency($deliveryCost)); ?>

        </span>
        <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
    </div>

    
    <div data-financial-discount class="bg-surface rounded-lg p-3 min-w-0">
        <span class="block text-xs text-ink-muted"><?php echo e(__('merchant_panel.discount')); ?></span>
        <span class="block text-base font-semibold tabular-nums mt-1 <?php echo e($discount > 0 ? 'text-danger-500' : 'text-ink-muted'); ?>">
            <?php echo e($discount > 0 ? '-' . currency($discount) : '—'); ?>

        </span>
        <span class="block text-xs text-ink-muted mt-1">&nbsp;</span>
    </div>

    
    <div data-financial-total class="bg-brand-surface rounded-lg p-3 min-w-0 md:col-span-2 min-[1440px]:col-span-1">
        <span class="block text-xs text-brand-fg/80"><?php echo e(__('merchant_panel.total')); ?></span>
        <span class="block text-xl font-bold text-brand-fg tabular-nums mt-1"><?php echo e(currency($grandTotal)); ?></span>
        <span class="block text-xs text-brand-fg/80 mt-1">&nbsp;</span>
    </div>
</div>


<div class="flex items-center justify-between gap-4 mt-3 pt-3 border-t border-surface-border">
    <div class="flex items-center gap-2">
        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'form.discount_type','options' => [
            ['value' => '', 'label' => __('merchant_panel.discount')],
            ['value' => 'amount', 'label' => __('merchant_panel.fixed_amount')],
            ['value' => 'percent', 'label' => __('merchant_panel.percentage')],
        ],'size' => 'sm','class' => 'w-28']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'form.discount_type','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['value' => '', 'label' => __('merchant_panel.discount')],
            ['value' => 'amount', 'label' => __('merchant_panel.fixed_amount')],
            ['value' => 'percent', 'label' => __('merchant_panel.percentage')],
        ]),'size' => 'sm','class' => 'w-28']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $attributes = $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $component = $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($form['discount_type']): ?>
            <input type="number" wire:model="form.discount_value"
                class="edz-input text-xs py-1 w-20" min="0"
                placeholder="<?php echo e($form['discount_type'] === 'percent' ? '%' : 'DZD'); ?>">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($form['discount_type'] && $form['discount_value']): ?>
            <input type="text" wire:model="form.discount_reason"
                class="edz-input text-xs py-1 flex-1 max-w-xs"
                placeholder="<?php echo e(__('merchant_panel.discount_reason')); ?>">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <span
        class="text-sm font-medium tabular-nums <?php echo e($discount > 0 ? 'text-danger-500' : 'text-ink-muted'); ?>">
        <?php echo e($discount > 0 ? '-' . currency($discount) : '—'); ?>

    </span>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\order-financial-summary.blade.php ENDPATH**/ ?>