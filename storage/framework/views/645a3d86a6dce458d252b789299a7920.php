
<div class="contents" @edz-modal-closed.window="$wire.closePermissionGroup()">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->activeGroupMeta): ?>
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => $this->activePermissionGroup !== null,'size' => 'md','showCloseButton' => true,'wire:key' => 'permission-group-modal-'.e($this->activeGroupMeta['group']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->activePermissionGroup !== null),'size' => 'md','show-close-button' => true,'wire:key' => 'permission-group-modal-'.e($this->activeGroupMeta['group']).'']); ?>
            <div class="p-5">
                <div class="flex items-center gap-3 pe-10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-surface-secondary text-ink-muted">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => ''.e($this->activeGroupMeta['icon']).'','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($this->activeGroupMeta['icon']).'','class' => 'w-5 h-5']); ?>
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
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-ink"><?php echo e($this->activeGroupMeta['title']); ?></h3>
                        <p class="text-xs text-ink-muted"><?php echo e($this->activeGroupMeta['description']); ?></p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between gap-2 flex-wrap">
                    <span class="text-xs text-ink-muted"><?php echo e(__('teams.dangerous_hint')); ?></span>
                    <span class="flex items-center gap-1">
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="selectGroupPermissions('<?php echo e($this->activeGroupMeta['group']); ?>')">
                            <?php echo e(__('teams.group_select_all')); ?>

                        </button>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                wire:click="clearGroupPermissions('<?php echo e($this->activeGroupMeta['group']); ?>')">
                            <?php echo e(__('teams.group_clear')); ?>

                        </button>
                    </span>
                </div>

                <ul class="mt-3 divide-y divide-surface-border rounded-lg border border-surface-border">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->activeGroupMeta['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-start justify-between gap-3 px-3 py-2.5">
                            <label class="flex min-w-0 items-start gap-2.5 cursor-pointer">
                                <input type="checkbox"
                                    class="edz-checkbox mt-0.5 h-4 w-4 shrink-0"
                                    value="<?php echo e($row['permission']); ?>"
                                    <?php if($row['checked']): echo 'checked'; endif; ?>
                                    wire:click="togglePermission('<?php echo e($row['permission']); ?>', <?php echo e($row['checked'] ? 'false' : 'true'); ?>)"
                                    wire:key="perm-cb-<?php echo e($row['permission']); ?>"
                                >
                                <span class="min-w-0">
                                    <span class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-sm font-medium text-ink"><?php echo e($row['label']); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['custom']): ?>
                                            <span class="edz-badge edz-badge--neutral edz-badge--sm"><?php echo e(__('teams.custom_badge')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['dangerous']): ?>
                                            <span class="edz-badge edz-badge--danger edz-badge--sm"><?php echo e(__('teams.dangerous_badge')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($row['requires'])): ?>
                                        <span class="mt-0.5 block text-xs text-ink-muted">
                                            <?php echo e(__('teams.requires')); ?>:
                                            <?php echo e(collect($row['requires'])->map(fn ($r) => \App\Support\PermissionGroupMeta::label($r))->implode(', ')); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                            </label>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>

                <div class="mt-4 flex justify-end">
                    <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="savePermissionGroup"
                    wire:loading.attr="disabled" wire:target="savePermissionGroup">
                    <?php echo e(__('buttons.done')); ?>

                </button>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $attributes = $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $component = $__componentOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\teams\partials\permission-group-modal.blade.php ENDPATH**/ ?>