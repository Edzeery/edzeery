
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->labelOpen && $this->labelData): ?>
    <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => $this->labelOpen,'@close' => '$wire.closeLabel()','size' => 'md','wire:key' => 'label-print-modal-'.e($this->labelOrderId).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->labelOpen),'@close' => '$wire.closeLabel()','size' => 'md','wire:key' => 'label-print-modal-'.e($this->labelOrderId).'']); ?>
        <style>
            @media print {
                body {
                    visibility: hidden;
                }
                #edz-label-sheet,
                #edz-label-sheet * {
                    visibility: visible;
                }
                #edz-label-sheet {
                    position: absolute;
                    inset: 0;
                    margin: 0;
                    width: 100%;
                    max-width: 100%;
                    border: 0;
                    border-radius: 0;
                    box-shadow: none;
                    padding: 8mm;
                }
            }
        </style>

        <div class="relative">
            <div
                class="edz-label-no-print sticky top-0 z-10 flex items-center justify-between gap-2 border-b border-line-200 bg-white px-4 py-3">
                <p class="text-sm font-bold text-ink"><?php echo e(__('order_flow.label_store_title')); ?></p>
                <div class="flex items-center gap-2">
                    <button wire:click="closeLabel" class="edz-btn edz-btn--ghost edz-btn--sm">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-4 h-4']); ?>
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
                        <?php echo e(__('order_flow.label_close')); ?>

                    </button>
                    <button x-on:click="window.print()" class="edz-btn edz-btn--primary edz-btn--sm">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'printer','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'printer','class' => 'w-4 h-4']); ?>
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
                        <?php echo e(__('order_flow.label_print')); ?>

                    </button>
                </div>
            </div>

            <div id="edz-label-sheet"
                class="mx-auto my-4 w-full max-w-[360px] overflow-hidden rounded-lg border border-line-200 bg-white text-ink shadow-sm">
                <div class="border-b-2 border-ink bg-neutral-50 px-4 py-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-base font-extrabold"><?php echo e($this->labelData['provider'] ?? $this->labelData['rider'] ?? '—'); ?></p>
                        <p class="text-sm font-bold tabular-nums">#<?php echo e($this->labelData['number']); ?></p>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($this->labelData['tracking_number'])): ?>
                        <p class="text-[11px] font-medium text-ink-muted tabular-nums" dir="ltr">
                            <?php echo e($this->labelData['tracking_number']); ?>

                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="space-y-3 px-4 py-3 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold"><?php echo e($this->labelData['customer']); ?></span>
                        <span class="tabular-nums" dir="ltr"><?php echo e($this->labelData['phone']); ?></span>
                    </div>

                    <div class="break-words text-ink-muted leading-snug">
                        <?php echo e($this->labelData['address']); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($this->labelData['stopdesk'])): ?>
                            <span class="mt-1 block font-semibold text-ink">
                                <?php echo e(__('orders.delivery_stopdesk')); ?>: <?php echo e($this->labelData['stopdesk']); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($this->labelData['items'])): ?>
                        <p class="break-words border-t border-dashed border-line-200 pt-2 text-xs leading-snug text-ink-muted">
                            <?php echo e($this->labelData['items']); ?>

                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="flex items-center justify-between border-t-2 border-dashed border-ink pt-2">
                        <span class="font-bold"><?php echo e(__('merchant_panel.total')); ?></span>
                        <span class="text-base font-extrabold tabular-nums"><?php echo e($this->labelData['total']); ?></span>
                    </div>
                </div>

                <div class="flex flex-col items-center border-t border-line-200 bg-neutral-50 px-4 py-3">
                    <?php if (isset($component)) { $__componentOriginal081b2eda89a592c59af9780577d4bb71 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal081b2eda89a592c59af9780577d4bb71 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.barcode','data' => ['value' => $this->labelData['barcode'],'height' => 64,'showText' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.barcode'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->labelData['barcode']),'height' => 64,'show-text' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal081b2eda89a592c59af9780577d4bb71)): ?>
<?php $attributes = $__attributesOriginal081b2eda89a592c59af9780577d4bb71; ?>
<?php unset($__attributesOriginal081b2eda89a592c59af9780577d4bb71); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal081b2eda89a592c59af9780577d4bb71)): ?>
<?php $component = $__componentOriginal081b2eda89a592c59af9780577d4bb71; ?>
<?php unset($__componentOriginal081b2eda89a592c59af9780577d4bb71); ?>
<?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($this->labelData['tracking_number'])): ?>
                        <p class="mt-1 text-[11px] font-semibold text-ink">
                            <?php echo e(__('order_flow.label_pickup_code')); ?>

                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
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
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\tracking\partials\label-print-modal.blade.php ENDPATH**/ ?>