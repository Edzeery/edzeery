
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reassignOpen): ?>
    <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'showCloseButton' => false,'wire:key' => 'shared-reassign-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isOpen' => true,'showCloseButton' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'wire:key' => 'shared-reassign-modal']); ?>
        <div class="p-6 space-y-4">
            <h3 class="text-lg font-bold text-ink"><?php echo e($reassignTitle); ?></h3>

            <?php
                $reassignSelected = collect($reassignCandidates)->firstWhere('id', $reassignTargetId);
                $reassignOverCap = $reassignSelected
                    ? ($reassignSelected['cap'] !== null && (int) $reassignSelected['open'] >= (int) $reassignSelected['cap'])
                    : false;
            ?>

            <fieldset>
                <label class="edz-label mb-2"><?php echo e(__('merchant_panel.assign_to')); ?> *</label>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($reassignCandidates)): ?>
                    <p class="text-sm text-ink-muted"><?php echo e(__('merchant_panel.reassign_no_candidates')); ?></p>
                <?php else: ?>
                    <div class="space-y-2 max-h-72 overflow-y-auto pe-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reassignCandidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label wire:key="candidate-<?php echo e($candidate['id']); ?>"
                                class="flex items-center gap-3 rounded-xl border px-3 py-2.5 cursor-pointer transition-colors <?php echo e($candidate['id'] === $reassignTargetId ? 'border-brand-500 bg-brand-500/5' : 'border-surface-border hover:border-brand-300 bg-white'); ?>">
                                <input type="radio" class="sr-only" wire:model.live="<?php echo e($reassignModel); ?>"
                                    value="<?php echo e($candidate['id']); ?>">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-surface-tertiary text-ink-muted text-sm font-semibold shrink-0">
                                    <?php echo e(mb_substr($candidate['name'], 0, 1)); ?>

                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-medium text-ink truncate"><?php echo e($candidate['name']); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($candidate['on_shift']): ?>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-success-700 bg-success-500/10 rounded-full px-1.5 py-0.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-success-500"></span>
                                                <?php echo e(__('merchant_panel.on_shift')); ?>

                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($candidate['dual_role']): ?>
                                            <span class="inline-flex items-center text-[10px] font-semibold text-accent-fg bg-accent-surface rounded-full px-1.5 py-0.5">
                                                <?php echo e(__('merchant_panel.dual_role_badge')); ?>

                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                    <span class="block text-xs text-ink-muted mt-0.5">
                                        <?php echo e(__('merchant_panel.current_load')); ?>:
                                        <span class="font-medium tabular-nums text-ink">
                                            <?php echo e($candidate['cap'] !== null
                                                ? $candidate['open'] . ' / ' . $candidate['cap']
                                                : __('merchant_panel.unlimited')); ?>

                                        </span>
                                    </span>
                                </span>
                                <span class="shrink-0 ms-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($candidate['id'] === $reassignTargetId): ?>
                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-4 h-4 text-brand-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-4 h-4 text-brand-500']); ?>
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
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </fieldset>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reassignOverCap): ?>
                <div class="flex items-start gap-2 rounded-xl border border-warning-300/60 bg-warning-500/5 px-3 py-2.5">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'exclamation-triangle','class' => 'w-4 h-4 shrink-0 mt-0.5 text-warning-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'exclamation-triangle','class' => 'w-4 h-4 shrink-0 mt-0.5 text-warning-600']); ?>
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
                    <p class="text-sm text-warning-800"><?php echo e(__('merchant_panel.reassign_over_capacity_warning')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="flex justify-end gap-2">
                <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                    wire:click="set('<?php echo e($reassignCloseSet); ?>', false)"><?php echo e(__('merchant_panel.cancel')); ?></button>
                <button wire:click="<?php echo e($reassignSubmit); ?>" class="edz-btn edz-btn--primary edz-btn--sm"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
                    <span><?php echo e(__('merchant_panel.reassign')); ?></span>
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
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/orders/partials/reassign-modal.blade.php ENDPATH**/ ?>