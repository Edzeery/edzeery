<div x-show="step === 3" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title"><?php echo e(__('products.pricing_stock')); ?></h2>
                        <p class="text-sm text-ink-400"><?php echo e(__('products.pricing_stock_hint')); ?></p>
                    </div>
                </div>
                <div class="edz-card__body">
                    <div class="mb-4">
                        <label class="flex items-center gap-2 text-sm font-medium text-ink">
                            <input type="checkbox" wire:model.live="has_variants" class="h-4 w-4 rounded border-surface-border text-brand-600">
                            <?php echo e(__('products.has_variants')); ?>

                        </label>
                        <p class="mt-1 text-xs text-ink-muted"><?php echo e(__('products.product_type_hint')); ?></p>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $has_variants): ?>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-price"><?php echo e(__('products.price')); ?></label>
                                <input id="product-price" type="number" step="0.01" min="0" class="edz-input <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model="price" placeholder="0.00">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-compare-price"><?php echo e(__('products.compare_at_price')); ?></label>
                                <input id="product-compare-price" type="number" step="0.01" min="0" class="edz-input <?php $__errorArgs = ['compare_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model="compare_price" placeholder="0.00">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['compare_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-cost-price"><?php echo e(__('products.cost_price')); ?></label>
                                <input id="product-cost-price" type="number" step="0.01" min="0" class="edz-input <?php $__errorArgs = ['cost_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model="cost_price" placeholder="0.00">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['cost_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-3 sm:col-span-2 lg:col-span-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('products.profit_margin')); ?></p>
                                <?php
                                    $sp = $price !== null && $price !== '' ? (float) $price : null;
                                    $sc = $cost_price !== null && $cost_price !== '' ? (float) $cost_price : null;
                                    $sProfit = $sp !== null && $sc !== null ? $sp - $sc : null;
                                    $sMargin = $sProfit !== null && $sp > 0 ? round(($sProfit / $sp) * 100, 1) : null;
                                ?>
                                <p class="mt-1 text-sm font-semibold text-ink">
                                    <?php echo e($sProfit !== null ? number_format($sProfit, 2) : '—'); ?>

                                    <span class="text-ink-muted">(<?php echo e($sMargin !== null ? $sMargin.'%' : '—'); ?>)</span>
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-4 text-sm text-ink-muted">
                            <?php echo e(__('products.add_options_hint')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-1">
                        <div class="edz-field">
                            <label class="edz-field__label" for="product-min-order-qty"><?php echo e(__('products.min_order_qty')); ?></label>
                            <input id="product-min-order-qty" type="number" min="1" class="edz-input <?php $__errorArgs = ['min_order_qty'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   wire:model="min_order_qty" placeholder="1">
                            <p class="text-xs text-ink-muted mt-1"><?php echo e(__('products.min_order_qty_hint')); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['min_order_qty'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="edz-field">
                            <label class="edz-field__label" for="product-max-order-qty"><?php echo e(__('products.max_order_qty')); ?></label>
                            <input id="product-max-order-qty" type="number" min="1" class="edz-input <?php $__errorArgs = ['max_order_qty'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   wire:model="max_order_qty" placeholder="<?php echo e(__('products.unlimited')); ?>">
                            <p class="text-xs text-ink-muted mt-1"><?php echo e(__('products.max_order_qty_hint')); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['max_order_qty'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/products/form/step-pricing.blade.php ENDPATH**/ ?>