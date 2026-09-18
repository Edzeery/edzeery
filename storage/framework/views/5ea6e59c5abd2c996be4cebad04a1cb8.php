
<div class="edz-card edz-card--padded">
    <h3 class="text-base font-semibold text-ink mb-1 flex items-center gap-2">
        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trending-up','class' => 'w-5 h-5 text-accent-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trending-up','class' => 'w-5 h-5 text-accent-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
        <?php echo e(__('merchant_panel.distribution_overflow_group')); ?>

    </h3>
    <p class="text-xs text-ink-muted mb-5"><?php echo e(__('merchant_panel.distribution_overflow_group_desc')); ?></p>

    <div class="space-y-6">
        <label
            class="flex items-start gap-3 p-4 rounded-xl border transition-all cursor-pointer
            <?php echo e($overflowEnabled ? 'border-accent-500 bg-accent-surface-subtle' : 'border-surface-border'); ?>">
            <?php if (isset($component)) { $__componentOriginal0283f82cff84f4c646f29d974f5967a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0283f82cff84f4c646f29d974f5967a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.checkbox','data' => ['wire:model' => 'overflowEnabled','class' => 'mt-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'overflowEnabled','class' => 'mt-0.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0283f82cff84f4c646f29d974f5967a4)): ?>
<?php $attributes = $__attributesOriginal0283f82cff84f4c646f29d974f5967a4; ?>
<?php unset($__attributesOriginal0283f82cff84f4c646f29d974f5967a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0283f82cff84f4c646f29d974f5967a4)): ?>
<?php $component = $__componentOriginal0283f82cff84f4c646f29d974f5967a4; ?>
<?php unset($__componentOriginal0283f82cff84f4c646f29d974f5967a4); ?>
<?php endif; ?>
            <span>
                <span class="block text-sm font-medium text-ink"><?php echo e(__('merchant_panel.distribution_overflow_enabled')); ?></span>
                <span class="block text-xs text-ink-muted mt-0.5"><?php echo e(__('merchant_panel.distribution_overflow_enabled_desc')); ?></span>
            </span>
        </label>

        <div>
            <div class="max-w-xs">
                <label for="distribution_overflow_percentage" class="edz-label"><?php echo e(__('merchant_panel.distribution_overflow_percentage')); ?></label>
                <div class="relative">
                    <input id="distribution_overflow_percentage" type="number" min="0" max="100"
                        wire:model="overflowPercentage" <?php if(! $overflowEnabled): echo 'disabled'; endif; ?>
                        placeholder="10"
                        class="edz-input w-full pe-8 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none <?php echo e($overflowEnabled ? '' : 'opacity-60'); ?>">
                    <span class="absolute inset-y-0 end-0 flex items-center pe-3 text-sm text-ink-muted">٪</span>
                </div>
            </div>
            <p class="text-xs text-ink-muted mt-1"><?php echo e(__('merchant_panel.distribution_overflow_percentage_desc')); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['overflowPercentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-danger-fg text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="mt-6 pt-6 border-t border-surface-border flex justify-end">
        <button type="button" wire:click="save" class="edz-btn edz-btn--primary">
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'save','class' => 'w-4 h-4 me-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'save','class' => 'w-4 h-4 me-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
            <?php echo e(__('buttons.save')); ?>

        </button>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/order-distribution-settings/partials/overflow-settings.blade.php ENDPATH**/ ?>