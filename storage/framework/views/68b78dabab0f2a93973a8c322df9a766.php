
<?php
    $trkStepKeys  = ['shipped', 'in_transit', 'out_for_delivery', 'delivered', 'returned'];
    $trkCurrent   = $this->statusHistoryMeta['tracking_status'] ?? null;
    $trkStepIndex = array_search($trkCurrent, $trkStepKeys, true);
    $trkStepIndex = $trkStepIndex === false ? null : $trkStepIndex;
    $trkHasBranch = $trkStepIndex === null; // exceptional state → neutral rail + chip
?>

<div class="mb-5 mt-1">
    <div class="relative flex items-center">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $trkStepKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trkI => $trkKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $trkStepKit = \Edzeery\MyStatusKit\Facades\Status::for('tracking', $trkKey);
                $trkDone    = $trkStepIndex !== null && $trkI < $trkStepIndex;
                $trkIsCur   = $trkStepIndex !== null && $trkI === $trkStepIndex;
            ?>
            <div class="flex flex-col items-center shrink-0">
                <span
                    class="<?php echo e($trkIsCur ? ($trkStepKit->color() ?? 'bg-accent-600 text-white') : ($trkDone ? 'bg-accent-600 text-white' : 'bg-surface-tertiary text-ink-muted border border-surface-border')); ?> relative z-10 flex items-center justify-center w-6 h-6 rounded-full">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trkDone): ?>
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-3.5 h-3.5']); ?>
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
                    <?php elseif($trkIsCur): ?>
                        <span class="w-2 h-2 rounded-full bg-white/90"></span>
                    <?php else: ?>
                        <span class="text-[10px] font-semibold leading-none"><?php echo e($trkI + 1); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
                <span class="mt-1.5 text-[10px] font-medium whitespace-nowrap <?php echo e($trkIsCur ? 'text-ink font-semibold' : 'text-ink-muted'); ?>"><?php echo e($trkStepKit->label()); ?></span>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trkI < count($trkStepKeys) - 1): ?>
                <span class="mx-1 sm:mx-2 h-0.5 flex-1 min-w-3 rounded-full <?php echo e($trkStepIndex !== null && $trkI < $trkStepIndex ? 'bg-accent-600' : 'bg-surface-border'); ?>"></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trkHasBranch): ?>
        <?php
            $trkBranchKit = $trkCurrent
                ? \Edzeery\MyStatusKit\Facades\Status::for('tracking', $trkCurrent)
                : null;
        ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trkBranchKit): ?>
            <div class="mt-3 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium <?php echo e($trkBranchKit->color()); ?>">
                <?php echo $trkBranchKit->icon(null, 'w-3.5 h-3.5'); ?>

                <?php echo e($trkBranchKit->label()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/tracking/partials/tracking-status-stepper.blade.php ENDPATH**/ ?>