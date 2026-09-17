
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBulkValidateModal): ?>
    <div @edz-modal-closed.window="$wire.closeBulkValidateModal()">
    <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'md','showCloseButton' => true,'wire:key' => 'bulk-validate-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => true,'size' => 'md','show-close-button' => true,'wire:key' => 'bulk-validate-modal']); ?>
        <div class="p-5">
            <h3 class="text-lg font-semibold text-ink mb-1"><?php echo e(__('order_flow.validate_shipment_title')); ?></h3>
            <p class="text-xs text-ink-muted mb-4"><?php echo e(__('order_flow.validate_shipment_hint')); ?></p>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->bulkValidateSkipCount === 0): ?>
                <p class="text-sm text-ink-muted"><?php echo e(__('order_flow.bulk_validate_ready_title')); ?>: <?php echo e($this->bulkValidateReadyCount); ?></p>
            <?php else: ?>
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
                    <p class="font-semibold mb-1"><?php echo e(__('order_flow.bulk_validate_skipped_title', ['count' => $this->bulkValidateSkipCount])); ?></p>
                    <ul class="space-y-1 max-h-40 overflow-y-auto edz-scroll">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = collect($this->bulkValidateAnalysis)->where('ready', false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="leading-relaxed break-words text-xs">
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

            <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2">
                <button wire:click="closeBulkValidateModal" type="button"
                    class="edz-btn edz-btn--ghost">
                    <?php echo e(__('buttons.cancel')); ?>

                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->bulkValidateSkipCount === 0): ?>
                    <button wire:click="confirmBulkValidate" type="button"
                        class="edz-btn edz-btn--primary"
                        wire:loading.attr="disabled" wire:target="confirmBulkValidate">
                        <?php if (isset($component)) { $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.spinner','data' => ['wire:target' => 'confirmBulkValidate','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:target' => 'confirmBulkValidate','class' => 'w-4 h-4']); ?>
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
                        <span><?php echo e(__('order_flow.bulk_validate_confirm')); ?></span>
                    </button>
                <?php elseif($this->bulkValidateReadyCount > 0): ?>
                    <button wire:click="confirmBulkValidate" type="button"
                        class="edz-btn edz-btn--primary"
                        wire:loading.attr="disabled" wire:target="confirmBulkValidate">
                        <?php if (isset($component)) { $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.spinner','data' => ['wire:target' => 'confirmBulkValidate','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:target' => 'confirmBulkValidate','class' => 'w-4 h-4']); ?>
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
                        <span><?php echo e(__('order_flow.bulk_validate_confirm_some', ['count' => $this->bulkValidateReadyCount])); ?></span>
                    </button>
                <?php else: ?>
                    <button type="button" disabled
                        class="edz-btn edz-btn--primary opacity-50 cursor-not-allowed">
                        <?php echo e(__('order_flow.bulk_validate_confirm_none')); ?>

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
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\bulk-validate-modal.blade.php ENDPATH**/ ?>