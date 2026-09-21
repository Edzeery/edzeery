
<div class="border-t border-surface-border pt-4">
    <div class="mb-3 flex items-center justify-between gap-2 flex-wrap">
        <div>
            <span class="text-sm font-medium text-ink"><?php echo e(__('titles.permissions')); ?></span>
            <span class="ms-2 text-xs text-ink-muted"><?php echo e(__('teams.hub_hint')); ?></span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                    wire:click="selectRoleTemplate">
                <?php echo e(__('buttons.select_all')); ?>

            </button>
            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                    wire:click="clearAllPermissions">
                <?php echo e(__('buttons.unselect_all')); ?>

            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->permissionGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupCard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button" wire:click="openPermissionGroup('<?php echo e($groupCard['group']); ?>')"
                    class="group rounded-xl border border-surface-border bg-surface-secondary p-4 text-start transition hover:shadow-card focus:outline-none">
                <div class="flex items-start justify-between gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-surface-secondary text-ink-muted">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => ''.e($groupCard['icon']).'','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($groupCard['icon']).'','class' => 'w-5 h-5']); ?>
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
                    </span>
                    <span class="edz-badge <?php echo e($groupCard['checked'] === $groupCard['total'] && $groupCard['total'] > 0 ? 'edz-badge--success' : 'edz-badge--neutral'); ?>">
                        <?php echo e(__('teams.granted_count', ['selected' => $groupCard['checked'], 'total' => $groupCard['total']])); ?>

                    </span>
                </div>
                <span class="mt-3 block text-sm font-semibold text-ink"><?php echo e($groupCard['title']); ?></span>
                <span class="mt-1 block text-xs leading-relaxed text-ink-muted"><?php echo e($groupCard['description']); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($groupCard['has_dangerous']): ?>
                    <span class="edz-badge edz-badge--danger edz-badge--sm mt-2"><?php echo e(__('teams.dangerous_badge')); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\teams\partials\permission-hub.blade.php ENDPATH**/ ?>