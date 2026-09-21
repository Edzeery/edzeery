
<?php
    $queueRows = $kind === 'confirm' ? $confirmationQueue : $trackingQueue;
    $queueEmpty = $kind === 'confirm'
        ? __('merchant_panel.queue_empty_confirmation')
        : __('merchant_panel.queue_empty_tracking');
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($queueRows)): ?>
    <div class="rounded-2xl border border-surface-border bg-white p-10 text-center">
        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'list-bullet','class' => 'w-8 h-8 mx-auto mb-3 text-ink-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'list-bullet','class' => 'w-8 h-8 mx-auto mb-3 text-ink-muted']); ?>
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
        <p class="text-sm text-ink"><?php echo e($queueEmpty); ?></p>
        <p class="text-xs text-ink-muted mt-1"><?php echo e(__('merchant_panel.queue_empty_hint')); ?></p>
    </div>
<?php else: ?>
    <div class="overflow-hidden rounded-2xl border border-surface-border bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-ink-muted border-b border-surface-border">
                        <th class="px-4 py-3 font-medium"><?php echo e(__('merchant_panel.order_number')); ?></th>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('merchant_panel.customer')); ?></th>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('merchant_panel.status')); ?></th>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('merchant_panel.queue_current_assignee')); ?></th>
                        <th class="px-4 py-3 font-medium"><?php echo e(__('general.created_at')); ?></th>
                        <th class="px-4 py-3 font-medium text-end"><?php echo e(__('merchant_panel.actions')); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $queueRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queueRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr wire:key="queue-<?php echo e($kind); ?>-<?php echo e($queueRow['id']); ?>" class="hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-mono font-semibold text-ink">#<?php echo e($queueRow['number']); ?></td>
                            <td class="px-4 py-3 text-ink"><?php echo e($queueRow['customer']); ?></td>
                            <td class="px-4 py-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kind === 'confirm'): ?>
                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('general', $queueRow['status']['color'] ?? 'gray')->color()); ?>">
                                        <?php echo \Edzeery\MyStatusKit\Facades\Status::for('order', $queueRow['status']['key'] ?? 'default')->icon(null, 'w-3 h-3 shrink-0'); ?>

                                        <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('order', $queueRow['status']['key'] ?? 'default')->label()); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', $queueRow['tracking_status'] ?? 'default')->color()); ?>">
                                        <?php echo \Edzeery\MyStatusKit\Facades\Status::for('tracking', $queueRow['tracking_status'] ?? 'default')->icon(null, 'w-3 h-3 shrink-0'); ?>

                                        <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', $queueRow['tracking_status'] ?? 'default')->label()); ?>

                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <div class="flex items-center gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($queueRow['assigned_to'])): ?>
                                        <span class="inline-flex items-center gap-1 text-ink-muted">
                                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'user','class' => 'w-3 h-3 text-ink-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user','class' => 'w-3 h-3 text-ink-muted']); ?>
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
                                            <?php echo e($queueRow['assigned_to']); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="font-medium text-ink"><?php echo e(__('merchant_panel.queue_unassigned')); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($queueRow['over_capacity'])): ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-warning-500/10 text-warning-700 text-[10px] font-semibold px-2 py-0.5">
                                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'exclamation-triangle','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'exclamation-triangle','class' => 'w-3 h-3']); ?>
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
                                            <?php echo e(__('merchant_panel.queue_over_capacity')); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-muted whitespace-nowrap"><?php echo e($queueRow['created_ago']); ?></td>
                            <td class="px-4 py-3 text-end">
                                <button type="button" wire:click="openReassignModal('<?php echo e($queueRow['id']); ?>')"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-brand-700 hover:text-brand-600">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrows-right-left','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrows-right-left','class' => 'w-3.5 h-3.5']); ?>
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
                                    <?php echo e(__('merchant_panel.reassign')); ?>

                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\order-distribution-queue\partials\queue-table.blade.php ENDPATH**/ ?>