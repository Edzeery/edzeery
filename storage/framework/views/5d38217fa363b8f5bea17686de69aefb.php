
<div class="flex items-center gap-2 pt-1">
    <div class="flex-1 min-w-0">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
            <p class="text-xs text-danger-600"><?php echo e($this->editingError); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="closeItemsModal">
        <?php echo e(__('buttons.cancel')); ?>

    </button>
    <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="saveOrderItems"
        wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
        <span wire:loading.remove wire:target="saveOrderItems"><?php echo e(__('buttons.save')); ?></span>
        <span wire:loading wire:target="saveOrderItems" class="inline-flex items-center gap-1.5">
            <?php if (isset($component)) { $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.spinner','data' => ['class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c)): ?>
<?php $attributes = $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c; ?>
<?php unset($__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf4c9959d3f2732b60b7f028a5155a98c)): ?>
<?php $component = $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c; ?>
<?php unset($__componentOriginalf4c9959d3f2732b60b7f028a5155a98c); ?>
<?php endif; ?>
            <?php echo e(__('buttons.processing')); ?>

        </span>
    </button>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/orders/partials/orders-items-modal-footer.blade.php ENDPATH**/ ?>