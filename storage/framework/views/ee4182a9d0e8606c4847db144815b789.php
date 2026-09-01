<?php

use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Services\ReturnVerificationService;
use App\Enums\Store\ReturnInspectionResult;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\OrderTracking;
use App\Models\Status;

?>

<div>
    
    <div class="mb-6">
        <div class="edz-card">
            <div class="edz-card__body">
                <label class="edz-label"><?php echo e(__('merchant_panel.scan_return_barcode')); ?></label>
                <input
                    type="text"
                    wire:model.live="scanCode"
                    @keydown.enter.prevent="$wire.verifyScan($event.target.value); $event.target.value = ''"
                    class="edz-input"
                    placeholder="<?php echo e(__('merchant_panel.scan_return_barcode_placeholder')); ?>"
                    autofocus
                />
            </div>
        </div>
    </div>

    
    <div class="mb-4 flex gap-2">
        <button
            wire:click="$set('returnTab', 'awaiting_verification')"
            class="edz-btn <?php echo e($returnTab === 'awaiting_verification' ? 'edz-btn--primary' : 'edz-btn--ghost'); ?>"
        >
            <?php echo e(__('merchant_panel.awaiting_verification')); ?>

            <span class="ml-1 text-xs opacity-60">
                (<?php echo e(count(array_filter($trackings, fn ($t) => empty($t['verified_at'])))); ?>)
            </span>
        </button>
        <button
            wire:click="$set('returnTab', 'awaiting_processing')"
            class="edz-btn <?php echo e($returnTab === 'awaiting_processing' ? 'edz-btn--primary' : 'edz-btn--ghost'); ?>"
        >
            <?php echo e(__('merchant_panel.awaiting_processing')); ?>

            <span class="ml-1 text-xs opacity-60">
                (<?php echo e(count(array_filter($trackings, fn ($t) => ! empty($t['verified_at']) && empty($t['processed_at'])))); ?>)
            </span>
        </button>
        <button
            wire:click="$set('returnTab', 'processed')"
            class="edz-btn <?php echo e($returnTab === 'processed' ? 'edz-btn--primary' : 'edz-btn--ghost'); ?>"
        >
            <?php echo e(__('merchant_panel.processed')); ?>

            <span class="ml-1 text-xs opacity-60">
                (<?php echo e(count(array_filter($trackings, fn ($t) => ! empty($t['processed_at'])))); ?>)
            </span>
        </button>
    </div>

    
    <div class="edz-card">
        <div class="edz-card__body p-0">
            <?php $filtered = $this->filteredTrackings(); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($filtered)): ?>
                <div class="p-8 text-center text-gray-500">
                    <?php echo e(__('merchant_panel.no_returns_in_tab')); ?>

                </div>
            <?php else: ?>
                <div class="divide-y">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $filtered; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $order = $t['order'] ?? [];
                            $tracking = $order['latest_tracking'] ?? null;
                        ?>
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900">
                                        #<?php echo e($order['number'] ?? ''); ?>

                                    </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tracking): ?>
                                        <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'status-kit::components.status-badge','data' => ['domain' => 'general','status' => $tracking['status_key'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'general','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tracking['status_key'] ?? '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="text-sm text-gray-500 mt-1">
                                    <?php echo e($order['customer']['name'] ?? ''); ?> — <?php echo e($order['customer']['phone'] ?? ''); ?>

                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    <?php echo e(__('merchant_panel.returned_at')); ?>: <?php echo e($t['returned_at'] ?? ''); ?>

                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($t['verified_at']): ?>
                                    <span class="edz-badge edz-badge--success"><?php echo e(__('merchant_panel.verified')); ?></span>
                                <?php else: ?>
                                    <span class="edz-badge edz-badge--warning"><?php echo e(__('merchant_panel.unverified')); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($t['processed_at']): ?>
                                    <?php $inspResult = \App\Enums\Store\ReturnInspectionResult::tryFrom($t['inspection_result'] ?? ''); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inspResult): ?>
                                        <span class="edz-badge edz-badge--<?php echo e($inspResult->isRequeueEligible() ? 'success' : 'danger'); ?>">
                                            <?php echo e($inspResult->label()); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $t['processed_at'] && canStore(\App\Enums\Store\StorePermissionEnum::RETURNS_PROCESS->value)): ?>
                                    <button
                                        wire:click="openProcessModal('<?php echo e($t['id']); ?>')"
                                        class="edz-btn edz-btn--primary edz-btn--sm"
                                    >
                                        <?php echo e(__('merchant_panel.process')); ?>

                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(
                                    $t['processed_at']
                                    && ($t['inspection_result'] ?? '') === 'good'
                                    && empty($t['requeued_at'])
                                    && canStore(\App\Enums\Store\StorePermissionEnum::RETURNS_PROCESS->value)
                                ): ?>
                                    <button
                                        x-data
                                        @click.prevent="(async () => { if (await EdzSwal.confirmDelete('<?php echo e($order['number'] ?? ''); ?>')) $wire.requeue('<?php echo e($t['id']); ?>') })()"
                                        class="edz-btn edz-btn--primary edz-btn--sm"
                                    >
                                        <?php echo e(__('merchant_panel.requeue')); ?>

                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showProcessModal): ?>
        <div
            x-data
            x-on:keydown.escape.window="$wire.set('showProcessModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div class="edz-card w-full max-w-md" @click.outside="$wire.set('showProcessModal', false)">
                <div class="edz-card__header">
                    <h3 class="edz-card__title"><?php echo e(__('merchant_panel.inspection')); ?></h3>
                </div>
                <div class="edz-card__body space-y-4">
                    
                    <div>
                        <label class="edz-label"><?php echo e(__('merchant_panel.inspection_result')); ?></label>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Enums\Store\ReturnInspectionResult::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        wire:model.live="processResult"
                                        value="<?php echo e($result->value); ?>"
                                        class="edz-radio"
                                    />
                                    <span><?php echo e($result->label()); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    
                    <div>
                        <label class="edz-label"><?php echo e(__('merchant_panel.inspection_notes')); ?></label>
                        <textarea
                            wire:model.live="processNotes"
                            class="edz-textarea"
                            rows="3"
                            placeholder="<?php echo e(__('merchant_panel.inspection_notes_placeholder')); ?>"
                        ></textarea>
                    </div>
                </div>
                <div class="edz-card__footer flex justify-end gap-2">
                    <button
                        wire:click="$set('showProcessModal', false)"
                        class="edz-btn edz-btn--ghost"
                    >
                        <?php echo e(__('buttons.cancel')); ?>

                    </button>
                    <button
                        wire:click="submitProcess"
                        class="edz-btn edz-btn--primary"
                    >
                        <?php echo e(__('merchant_panel.save_inspection')); ?>

                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\returns\index.blade.php ENDPATH**/ ?>