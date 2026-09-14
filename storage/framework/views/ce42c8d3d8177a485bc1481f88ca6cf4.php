
<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showTrash): ?>
        <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-trash-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-toolbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-3 flex items-center gap-2 text-sm text-ink-muted">
            <span><?php echo e($this->filteredTotal); ?> <?php echo e(__('order_flow.tracking_count')); ?></span>
        </div>

        <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-list', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-rider-stats', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <div class="mb-3 flex items-center gap-2 text-sm text-ink-muted">
            <span><?php echo e(trans('order_flow.rider_stats_summary', ['riders' => count($this->riderRiders), 'shipments' => $this->riderStatsActiveShipments])); ?></span>
        </div>

        <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-toolbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-3 flex items-center gap-2 text-sm text-ink-muted">
            <span><?php echo e($this->filteredTotal); ?> <?php echo e(__('order_flow.tracking_count')); ?></span>
        </div>

        <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-list', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\tracking\partials\tracking-rider-tab.blade.php ENDPATH**/ ?>