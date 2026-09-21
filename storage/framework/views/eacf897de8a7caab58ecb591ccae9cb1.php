
<div class="edz-card">
    <div class="edz-card__header">
        <div>
            <h2 class="edz-card__title"><?php echo e(__('teams.list_title')); ?></h2>
            <p class="text-sm text-ink-400"><?php echo e(__('teams.list_subtitle')); ?></p>
        </div>
    </div>

    <div class="border-b border-surface-border p-4">
        <input type="search" class="edz-input" placeholder="<?php echo e(__('teams.search_placeholder')); ?>"
               wire:model.live.debounce.300ms="search">
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                    <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('teams.name')); ?></th>
                    <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('teams.email')); ?></th>
                    <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('teams.role')); ?></th>
                    <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.address')); ?></th>
                    <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('teams.status')); ?></th>
                    <th class="px-4 py-3 text-end font-semibold"><?php echo e(__('general.actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $membership): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $roleName = $this->memberRoleName($membership);
                    ?>
                    <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                        <td class="px-4 py-3 font-medium text-ink"><?php echo e($membership->user?->name); ?></td>
                        <td class="px-4 py-3 text-ink-soft"><?php echo e($membership->user?->email); ?></td>
                        <td class="px-4 py-3">
                            <?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'role','status' => $roleName]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'role','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($roleName)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-xs text-ink-muted">
                            <?php echo e($membership->user?->city?->name); ?>, <?php echo e($membership->user?->state?->name); ?>

                        </td>
                        <td class="px-4 py-3">
                            <?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'general','status' => $membership->is_active ? 'active' : 'inactive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'general','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($membership->is_active ? 'active' : 'inactive')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canManageScope($membership)): ?>
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            wire:click="openProductScope('<?php echo e($membership->id); ?>')"><?php echo e(__('teams.product_scope')); ?></button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canModify($membership)): ?>
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            wire:click="openEdit('<?php echo e($membership->id); ?>')"><?php echo e(__('buttons.edit')); ?></button>
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            wire:click="toggleActive('<?php echo e($membership->id); ?>')">
                                        <?php echo e($membership->is_active ? __('buttons.deactivate') : __('buttons.activate')); ?>

                                    </button>
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                            x-data
                                            data-confirm-title="<?php echo e(__('teams.remove_member')); ?>"
                                            data-confirm-text="<?php echo e(__('messages.action_confirm_delete')); ?>"
                                            data-delete-id="<?php echo e($membership->id); ?>"
                                            @click.prevent="(async () => { if (await EdzSwal.confirmAction($el.dataset.confirmTitle, $el.dataset.confirmText)) await $wire.remove(Number($el.dataset.deleteId)) })()"
                                            ><?php echo e(__('buttons.remove')); ?></button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <p class="text-sm font-medium text-ink-soft"><?php echo e(__('teams.no_members')); ?></p>
                            <p class="mt-1 text-sm text-ink-muted"><?php echo e(__('teams.try_adjusting')); ?></p>
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->members->hasPages()): ?>
        <div class="border-t border-surface-border px-4 py-3">
            <?php echo e($this->members->links()); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\teams\partials\members-table.blade.php ENDPATH**/ ?>