<div x-show="step === 5" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $has_variants): ?>
                <div class="edz-card">
                    <div class="edz-card__header">
                        <div>
                            <h2 class="edz-card__title"><?php echo e(__('products.pricing_stock')); ?></h2>
                            <p class="text-sm text-ink-400"><?php echo e(__('products.pricing_stock_hint')); ?></p>
                        </div>
                    </div>
                    <div class="edz-card__body grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="edz-field">
                        <label class="edz-field__label" for="product-stock"><?php echo e(__('products.stock')); ?></label>
                        <input id="product-stock" type="number" min="0" class="edz-input <?php $__errorArgs = ['stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               wire:model="stock" placeholder="0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="edz-field">
                        <label class="edz-field__label" for="product-low-stock"><?php echo e(__('products.low_stock_threshold')); ?></label>
                        <input id="product-low-stock" type="number" min="0" class="edz-input <?php $__errorArgs = ['low_stock_threshold'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               wire:model="low_stock_threshold" placeholder="5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['low_stock_threshold'];
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
            <?php else: ?>
                <div class="edz-card">
                    <div class="edz-card__body">
                        <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-4 text-sm text-ink-muted">
                            <?php echo e(__('products.variants_hint')); ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title"><?php echo e(__('products.codes')); ?></h2>
                        <p class="text-sm text-ink-400"><?php echo e(__('products.codes_hint')); ?></p>
                    </div>
                </div>
                <div class="edz-card__body grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="edz-field">
                        <label class="edz-field__label" for="product-sku"><?php echo e(__('products.sku')); ?></label>
                        <input id="product-sku" type="text" class="edz-input <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               wire:model="sku" <?php if($auto_generate_sku): echo 'disabled'; endif; ?> placeholder="<?php echo e(__('products.auto_generated')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="edz-field__error"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="product-barcode"><?php echo e(__('products.barcode')); ?></label>
                        <input id="product-barcode" type="text" class="edz-input <?php $__errorArgs = ['barcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               wire:model="barcode" <?php if($auto_generate_barcode): echo 'disabled'; endif; ?> placeholder="<?php echo e(__('products.auto_generated')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['barcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="edz-field__error"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model.live="auto_generate_sku" class="h-4 w-4 rounded border-surface-border text-brand-600">
                        <?php echo e(__('products.auto_generate_sku')); ?>

                    </label>
                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model.live="auto_generate_barcode" class="h-4 w-4 rounded border-surface-border text-brand-600">
                        <?php echo e(__('products.auto_generate_barcode')); ?>

                    </label>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\products\form\step-inventory.blade.php ENDPATH**/ ?>