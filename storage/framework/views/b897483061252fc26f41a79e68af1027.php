
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(
    $this->trackingTab === 'carrier'
    && ! $this->showTrash
    && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DISPATCH_VALIDATE->value)
): ?>
    <button wire:click="openBulkValidateModal" type="button"
        title="<?php echo e(__('order_flow.bulk_validate_btn')); ?>"
        class="fixed bottom-6 end-6 z-40 inline-flex items-center gap-2 rounded-full edz-btn edz-btn--primary edz-btn--sm shadow-lg shadow-ink/20">
        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shield-check','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield-check','class' => 'w-4 h-4']); ?>
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
        <span class="hidden sm:inline"><?php echo e(__('order_flow.bulk_validate_btn')); ?></span>
    </button>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => $this->showBulkValidateModal,'@close' => '$wire.closeBulkValidateModal()','size' => 'md','showCloseButton' => true,'wire:key' => 'tracking-bulk-validate']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->showBulkValidateModal),'@close' => '$wire.closeBulkValidateModal()','size' => 'md','show-close-button' => true,'wire:key' => 'tracking-bulk-validate']); ?>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->bulkValidateReadyCount > 0): ?>
            <p class="text-sm font-semibold text-ink mb-2">
                <?php echo e(__('order_flow.bulk_validate_ready_title')); ?> (<?php echo e($this->bulkValidateReadyCount); ?>)
            </p>
            <ul class="space-y-1 max-h-44 overflow-y-auto edz-scroll mb-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = collect($this->bulkValidateAnalysis)->where('ready', true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-center justify-between gap-2 rounded-md bg-surface px-2 py-1">
                        <span class="inline-flex items-center gap-1.5 text-success-600">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check-circle','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','class' => 'w-4 h-4']); ?>
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
                            <span class="text-xs font-medium">#<?php echo e($entry['number']); ?></span>
                        </span>
                        <span class="truncate text-xs text-ink-muted max-w-[55%]"><?php echo e($entry['tracking_number']); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->bulkValidateSkipCount > 0): ?>
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
            <button wire:click="closeBulkValidateModal" type="button" class="edz-btn edz-btn--ghost"
                wire:loading.attr="disabled" wire:target="confirmBulkValidate">
                <?php echo e(__('buttons.cancel')); ?>

            </button>
            <button wire:click="confirmBulkValidate" type="button"
                <?php if($this->bulkValidateReadyCount === 0 || $this->bulkValidateBusy): echo 'disabled'; endif; ?>
                wire:loading.attr="disabled" wire:target="confirmBulkValidate"
                class="edz-btn edz-btn--primary <?php echo e($this->bulkValidateReadyCount === 0 || $this->bulkValidateBusy ? 'opacity-50 cursor-not-allowed' : ''); ?>">
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
                <span><?php echo e($this->bulkValidateReadyCount === 0
                    ? __('order_flow.bulk_validate_confirm_none')
                    : ($this->bulkValidateReadyCount === count($this->bulkValidateAnalysis)
                        ? __('order_flow.bulk_validate_confirm')
                        : __('order_flow.bulk_validate_confirm_some', ['count' => $this->bulkValidateReadyCount]))); ?></span>
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
<?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\tracking\partials\tracking-bulk-validate.blade.php ENDPATH**/ ?>