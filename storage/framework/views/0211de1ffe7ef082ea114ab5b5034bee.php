
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBulkSendModal): ?>
    <div @edz-modal-closed.window="$wire.closeBulkSendModal()">
    <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'md','showCloseButton' => true,'wire:key' => 'bulk-send-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => true,'size' => 'md','show-close-button' => true,'wire:key' => 'bulk-send-modal']); ?>
        <div class="p-5">
            <h3 class="text-lg font-semibold text-ink mb-1"><?php echo e(__('order_flow.bulk_send_summary_title')); ?></h3>
            <p class="text-xs text-ink-muted mb-4"><?php echo e(__('order_flow.bulk_send_summary_subtitle')); ?></p>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($this->bulkSendSummary) && $this->bulkSendSkipCount === 0): ?>
                <p class="text-sm text-ink-muted"><?php echo e(__('order_flow.bulk_send_no_groups')); ?></p>
            <?php else: ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($this->bulkSendSummary)): ?>
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-2">
                        <?php echo e(__('order_flow.bulk_send_ready_title')); ?>

                    </h4>
                    <ul class="space-y-2 mb-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->bulkSendSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center justify-between gap-2 rounded-lg border border-surface-border bg-surface-secondary px-3 py-2">
                                <span class="text-sm font-medium text-ink"><?php echo e($g['name']); ?></span>
                                <span class="text-xs font-semibold text-ink-muted"><?php echo e(__('order_flow.bulk_send_group_count', ['count' => $g['count']])); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->bulkSendSkipCount > 0): ?>
                    <?php if (isset($component)) { $__componentOriginal3d8e2a92a5397217854be10043063322 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3d8e2a92a5397217854be10043063322 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.alert','data' => ['type' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning']); ?>
                        <p class="font-semibold mb-1"><?php echo e(__('order_flow.bulk_send_skipped_title', ['count' => $this->bulkSendSkipCount])); ?></p>
                        <ul class="space-y-1 max-h-40 overflow-y-auto edz-scroll">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = collect($this->bulkSendAnalysis)->where('ready', false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="leading-relaxed break-words">
                                    #<?php echo e($entry['number']); ?> — <?php echo e(implode('، ', $entry['reasons'])); ?>

                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3d8e2a92a5397217854be10043063322)): ?>
<?php $attributes = $__attributesOriginal3d8e2a92a5397217854be10043063322; ?>
<?php unset($__attributesOriginal3d8e2a92a5397217854be10043063322); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3d8e2a92a5397217854be10043063322)): ?>
<?php $component = $__componentOriginal3d8e2a92a5397217854be10043063322; ?>
<?php unset($__componentOriginal3d8e2a92a5397217854be10043063322); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2">
                <button wire:click="closeBulkSendModal" type="button"
                    class="edz-btn edz-btn--ghost">
                    <?php echo e(__('buttons.cancel')); ?>

                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->bulkSendSkipCount === 0): ?>
                    <button wire:click="confirmBulkSend" type="button"
                        class="edz-btn edz-btn--primary"
                        wire:loading.attr="disabled">
                        <span><?php echo e(__('order_flow.bulk_send_confirm')); ?></span>
                    </button>
                <?php elseif($this->bulkSendReadyCount > 0): ?>
                    <button wire:click="confirmBulkSend" type="button"
                        class="edz-btn edz-btn--primary"
                        wire:loading.attr="disabled">
                        <span><?php echo e(__('order_flow.bulk_send_confirm_some', ['count' => $this->bulkSendReadyCount])); ?></span>
                    </button>
                <?php else: ?>
                    <button type="button" disabled
                        class="edz-btn edz-btn--primary opacity-50 cursor-not-allowed">
                        <?php echo e(__('order_flow.bulk_send_confirm_none')); ?>

                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\bulk-send-modal.blade.php ENDPATH**/ ?>