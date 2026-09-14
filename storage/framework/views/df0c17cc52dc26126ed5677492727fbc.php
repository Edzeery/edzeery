<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use Livewire\Volt\Component;

?>

<div>
    
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <?php if (isset($component)) { $__componentOriginal64446345db7363332d7ff2707d878bc4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal64446345db7363332d7ff2707d878bc4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.page-header','data' => ['title' => ''.e(__('order_flow.tracking_page_title')).'','description' => ''.e(__('order_flow.tracking_page_subtitle')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(__('order_flow.tracking_page_title')).'','description' => ''.e(__('order_flow.tracking_page_subtitle')).'']); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal64446345db7363332d7ff2707d878bc4)): ?>
<?php $attributes = $__attributesOriginal64446345db7363332d7ff2707d878bc4; ?>
<?php unset($__attributesOriginal64446345db7363332d7ff2707d878bc4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal64446345db7363332d7ff2707d878bc4)): ?>
<?php $component = $__componentOriginal64446345db7363332d7ff2707d878bc4; ?>
<?php unset($__componentOriginal64446345db7363332d7ff2707d878bc4); ?>
<?php endif; ?>
        <div class="flex items-center gap-2">
            <button wire:click="refresh" class="edz-btn edz-btn--ghost edz-btn--sm" wire:loading.attr="disabled"
                wire:loading.class="opacity-50 pointer-events-none" wire:target="refresh">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-path','wire:loading.remove' => true,'wire:target' => 'refresh','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-path','wire:loading.remove' => true,'wire:target' => 'refresh','class' => 'w-4 h-4']); ?>
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
                <?php if (isset($component)) { $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.spinner','data' => ['wire:target' => 'refresh','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:target' => 'refresh','class' => 'w-4 h-4']); ?>
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
            </button>
        </div>
    </div>

    
    <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->trackingTab === 'carrier'): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showTrash): ?>
            
            <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-trash-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-stats', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-toolbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($this->filteredTotal)): ?>
            <div class="mb-3 flex items-center gap-2 text-sm text-ink-muted">
                <span><?php echo e($this->filteredTotal); ?> <?php echo e(__('order_flow.tracking_count')); ?></span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-list', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-rider-tab', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-filter-portal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-filter-bar-portal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-table-settings-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('livewire.merchant.tracking.partials.order-drawer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-history-popup', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-notes-popup', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('livewire.merchant.tracking.partials.label-print-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('livewire.merchant.orders.partials.order-form-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('livewire.merchant.orders.partials.orders-product-picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        if (! window.__edzTrackingOpenLabel) {
            window.__edzTrackingOpenLabel = true;
            document.addEventListener('open-label', function (e) {
                if (e.detail && e.detail.url) {
                    window.open(e.detail.url, '_blank', 'noopener');
                }
            });
        }
    </script>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/tracking/index.blade.php ENDPATH**/ ?>