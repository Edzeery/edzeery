
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBulkStatusModal): ?>
    <div @edz-modal-closed.window="$wire.closeBulkStatusModal()">
    <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'md','showCloseButton' => true,'wire:key' => 'bulk-status-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => true,'size' => 'md','show-close-button' => true,'wire:key' => 'bulk-status-modal']); ?>
        <div class="p-5">
            <h3 class="text-lg font-semibold text-ink mb-4"><?php echo e(__('order_flow.bulk_status_title')); ?></h3>

            <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">
                <?php echo e(__('order_flow.bulk_status_target')); ?>

            </label>
            <select wire:model="bulkStatusTarget"
                class="edz-input w-full">
                <option value="">—</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!in_array($s['key'] ?? '', ['cancelled', 'canceled', 'confirmed'], true)): ?>
                        <option value="<?php echo e($s['key']); ?>">
                            <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label()); ?>

                        </option>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>

            <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mt-4 mb-1.5">
                <?php echo e(__('order_flow.bulk_status_reason')); ?>

            </label>
            <textarea wire:model="bulkStatusReason" rows="2"
                class="edz-input w-full"
                placeholder="<?php echo e(__('order_flow.bulk_status_reason_placeholder')); ?>"></textarea>

            <div class="mt-6 flex justify-end gap-2">
                <button wire:click="closeBulkStatusModal" type="button"
                    class="edz-btn edz-btn--ghost">
                    <?php echo e(__('buttons.cancel')); ?>

                </button>
                <button wire:click="submitBulkStatus" type="button"
                    class="edz-btn edz-btn--primary"
                    wire:loading.attr="disabled">
                    <span><?php echo e(__('buttons.save')); ?></span>
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
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\bulk-status-modal.blade.php ENDPATH**/ ?>