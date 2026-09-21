

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showBulkValidateModal): ?>
<?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'@edzModalClosed' => '$event.target === $event.currentTarget && $wire.closeBulkValidateModal()','size' => 'md','showCloseButton' => true,'wire:key' => 'tracking-bulk-validate']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => true,'@edz-modal-closed' => '$event.target === $event.currentTarget && $wire.closeBulkValidateModal()','size' => 'md','show-close-button' => true,'wire:key' => 'tracking-bulk-validate']); ?>
    <div class="p-5">
        <h3 class="text-lg font-semibold text-ink mb-1"><?php echo e(__('order_flow.validate_shipment_title')); ?></h3>
        <p class="text-xs text-ink-muted mb-4"><?php echo e(__('order_flow.validate_shipment_hint')); ?></p>

        <div class="rounded-xl border border-surface-border bg-surface-tertiary/30 p-3 mb-4">
            <?php if (isset($component)) { $__componentOriginalca6fea2ba32339a230890774ac8ae5fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalca6fea2ba32339a230890774ac8ae5fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.barcode-scan-input','data' => ['wireScanMethod' => 'bulkValidateFromBarcode','label' => __('order_flow.validate_scan_label'),'placeholder' => ''.e(__('order_flow.validate_scan_placeholder')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.barcode-scan-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wireScanMethod' => 'bulkValidateFromBarcode','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('order_flow.validate_scan_label')),'placeholder' => ''.e(__('order_flow.validate_scan_placeholder')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalca6fea2ba32339a230890774ac8ae5fd)): ?>
<?php $attributes = $__attributesOriginalca6fea2ba32339a230890774ac8ae5fd; ?>
<?php unset($__attributesOriginalca6fea2ba32339a230890774ac8ae5fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalca6fea2ba32339a230890774ac8ae5fd)): ?>
<?php $component = $__componentOriginalca6fea2ba32339a230890774ac8ae5fd; ?>
<?php unset($__componentOriginalca6fea2ba32339a230890774ac8ae5fd); ?>
<?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($this->bulkValidateResults) > 0): ?>
            <p class="text-sm font-semibold text-ink mb-2">
                <?php echo e(__('order_flow.scan_results_title')); ?> (<?php echo e(count($this->bulkValidateResults)); ?>)
            </p>
            <ul class="space-y-1 max-h-52 overflow-y-auto edz-scroll mb-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->bulkValidateResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-start justify-between gap-2 rounded-md bg-surface px-2 py-1.5">
                        <span class="inline-flex items-center gap-1.5 <?php echo e($entry['ok'] ? 'text-success-600' : 'text-danger-600'); ?>">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => ''.e($entry['ok'] ? 'check-circle' : 'x-circle').'','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($entry['ok'] ? 'check-circle' : 'x-circle').'','class' => 'w-4 h-4 shrink-0']); ?>
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
                            <span class="text-xs font-medium">#<?php echo e($entry['number']); ?> — <?php echo e($entry['tracking_number']); ?></span>
                        </span>
                        <span class="truncate text-xs text-ink-muted max-w-[45%]" title="<?php echo e($entry['message']); ?>"><?php echo e($entry['message']); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mt-6 flex justify-end">
            <button wire:click="closeBulkValidateModal" type="button" class="edz-btn edz-btn--ghost">
                <?php echo e(__('buttons.cancel')); ?>

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

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showBulkValidateResults): ?>
<?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'@edzModalClosed' => '$event.target === $event.currentTarget && $wire.closeBulkValidateResults()','size' => 'md','showCloseButton' => true,'wire:key' => 'bulk-validate-results']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => true,'@edz-modal-closed' => '$event.target === $event.currentTarget && $wire.closeBulkValidateResults()','size' => 'md','show-close-button' => true,'wire:key' => 'bulk-validate-results']); ?>
    <div class="p-5">
        <h3 class="text-lg font-semibold text-ink mb-1"><?php echo e(__('order_flow.bulk_validate_results_title')); ?></h3>
        <p class="text-xs text-ink-muted mb-4"><?php echo e(__('order_flow.bulk_validate_results_hint')); ?></p>

        <ul class="space-y-1 max-h-72 overflow-y-auto edz-scroll mb-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->bulkValidateResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-start justify-between gap-2 rounded-md bg-surface px-2 py-1.5">
                    <span class="inline-flex items-center gap-1.5 <?php echo e($entry['ok'] ? 'text-success-600' : 'text-danger-600'); ?>">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => ''.e($entry['ok'] ? 'check-circle' : 'x-circle').'','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($entry['ok'] ? 'check-circle' : 'x-circle').'','class' => 'w-4 h-4 shrink-0']); ?>
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
                        <span class="text-xs font-medium">#<?php echo e($entry['number']); ?> — <?php echo e($entry['tracking_number']); ?></span>
                    </span>
                    <span class="truncate text-xs text-ink-muted max-w-[45%]" title="<?php echo e($entry['message']); ?>"><?php echo e($entry['message']); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ul>

        <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2">
            <button wire:click="closeBulkValidateResults" type="button" class="edz-btn edz-btn--ghost">
                <?php echo e(__('buttons.close')); ?>

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
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/tracking/partials/tracking-bulk-validate.blade.php ENDPATH**/ ?>