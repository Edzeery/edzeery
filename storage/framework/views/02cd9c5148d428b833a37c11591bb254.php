
<?php
    $itemsModalKind = ($itemsModal ?? [])['kind'] ?? null;
    $itemsModalOrderId = ($itemsModal ?? [])['orderId'] ?? null;
    $orderItems = $form['items'] ?? [];
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($itemsModalKind === 'products'): ?>
    <div @edz-modal-closed="$wire.closeItemsModal()">
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'md','wire:key' => 'items-products-modal-'.e($itemsModalOrderId).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => true,'size' => 'md','wire:key' => 'items-products-modal-'.e($itemsModalOrderId).'']); ?>
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shopping-bag','class' => 'w-5 h-5 text-ink-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-bag','class' => 'w-5 h-5 text-ink-muted']); ?>
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
                    <h3 class="text-base font-bold text-ink"><?php echo e(__('merchant_panel.products')); ?></h3>
                    <span class="text-xs text-ink-muted">(<?php echo e(count($orderItems)); ?>)</span>
                </div>

                <div class="max-h-[45vh] overflow-y-auto edz-scroll divide-y divide-surface-border/70 rounded-xl border border-surface-border">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-3 px-3 py-2.5">
                            <div class="flex-1 min-w-0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($item['name'])): ?>
                                    <div class="text-sm font-medium text-ink truncate"><?php echo e($item['name']); ?></div>
                                <?php else: ?>
                                    <div class="text-sm text-ink-muted">
                                        <?php echo e(__('merchant_panel.please_select_product')); ?>

                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="text-[11px] text-ink-muted truncate" dir="ltr"><?php echo e($item['sku'] ?? ''); ?></div>
                            </div>
                            <div class="text-end shrink-0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) ($item['price'] ?? 0) > 0): ?>
                                    <div class="text-xs font-semibold text-ink tabular-nums">
                                        <?php echo e(currency((float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1))); ?>

                                    </div>
                                    <div class="text-[11px] text-ink-muted tabular-nums">
                                        <?php echo e((int) ($item['quantity'] ?? 1)); ?> × <?php echo e(currency($item['price'] ?? 0)); ?>

                                    </div>
                                <?php else: ?>
                                    <div class="text-xs text-ink-muted">
                                        <?php echo e(__('merchant_panel.please_select_product')); ?>

                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <button type="button" wire:click="removeInlineItem(<?php echo e($idx); ?>)"
                                class="text-danger-400 hover:text-danger-600 shrink-0 p-1.5 rounded-lg hover:bg-danger-surface transition-colors"
                                title="<?php echo e(__('merchant_panel.delete_item')); ?>">
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
                            </button>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-ink-muted py-6 text-center"><?php echo e(__('merchant_panel.no_items')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="pt-2 border-t border-surface-border" x-data="orderProductPicker()">
                    <button type="button" @click="openProductPicker()" :disabled="isLoadingProducts"
                        class="w-full flex items-center gap-3 px-4 py-3 bg-surface-secondary
                            border border-dashed border-surface-border rounded-xl
                            hover:border-brand-400 hover:bg-brand-50
                            transition-colors text-sm text-ink-muted group disabled:opacity-50">
                        <?php if (isset($component)) { $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.spinner','data' => ['show' => 'isLoadingProducts','class' => 'w-5 h-5 text-brand-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('isLoadingProducts'),'class' => 'w-5 h-5 text-brand-500']); ?>
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
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'qr-code','xShow' => '!isLoadingProducts','class' => 'w-5 h-5 text-ink-muted group-hover:text-brand-500 transition-colors']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'qr-code','x-show' => '!isLoadingProducts','class' => 'w-5 h-5 text-ink-muted group-hover:text-brand-500 transition-colors']); ?>
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
                        <span class="flex-1 text-start"><?php echo e(__('merchant_panel.search_products_barcode')); ?></span>
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'plus','class' => 'w-4 h-4 text-ink-muted group-hover:text-brand-500 transition-colors']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'w-4 h-4 text-ink-muted group-hover:text-brand-500 transition-colors']); ?>
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
                    </button>
                </div>

                <?php echo $__env->make('livewire.merchant.orders.partials.orders-items-modal-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($itemsModalKind === 'quantity'): ?>
    <div @edz-modal-closed="$wire.closeItemsModal()">
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'md','wire:key' => 'items-quantity-modal-'.e($itemsModalOrderId).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => true,'size' => 'md','wire:key' => 'items-quantity-modal-'.e($itemsModalOrderId).'']); ?>
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'list-bullet','class' => 'w-5 h-5 text-ink-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'list-bullet','class' => 'w-5 h-5 text-ink-muted']); ?>
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
                    <h3 class="text-base font-bold text-ink"><?php echo e(__('merchant_panel.quantity')); ?></h3>
                    <span class="text-xs text-ink-muted">(<?php echo e(count($orderItems)); ?>)</span>
                </div>

                <div class="max-h-[45vh] overflow-y-auto edz-scroll divide-y divide-surface-border/70 rounded-xl border border-surface-border">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $capReached = ($item['cap'] ?? null) !== null && ($item['quantity'] ?? 0) >= $item['cap'];
                        ?>
                        <div class="flex items-center gap-3 px-3 py-2.5">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-ink truncate"><?php echo e($item['name'] ?? '—'); ?></div>
                                <div class="text-[11px] text-ink-muted truncate" dir="ltr"><?php echo e($item['sku'] ?? ''); ?></div>
                            </div>
                            <div class="flex items-center rounded-lg border border-surface-border overflow-hidden shrink-0">
                                <button type="button"
                                    wire:click="updateFormItemQty(<?php echo e($idx); ?>, <?php echo e(max(1, (int) ($item['quantity'] ?? 1) - 1)); ?>)"
                                    :disabled="<?php echo e(((int) ($item['quantity'] ?? 1) <= 1) ? 'true' : 'false'); ?>"
                                    class="w-8 h-8 flex items-center justify-center bg-surface text-ink-muted hover:bg-surface-secondary transition-colors disabled:opacity-30 disabled:cursor-not-allowed text-sm font-medium select-none">
                                    &minus;
                                </button>
                                <input type="number" value="<?php echo e($item['quantity'] ?? 1); ?>"
                                    wire:change="updateFormItemQty(<?php echo e($idx); ?>, parseInt($event.target.value))"
                                    min="1" <?php if(($item['cap'] ?? null) !== null): ?> max="<?php echo e($item['cap']); ?>" <?php endif; ?>
                                    class="w-10 h-8 text-center border-x border-surface-border bg-transparent text-sm font-semibold text-ink focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button"
                                    wire:click="updateFormItemQty(<?php echo e($idx); ?>, <?php echo e((int) ($item['quantity'] ?? 1) + 1); ?>)"
                                    :disabled="<?php echo e($capReached ? 'true' : 'false'); ?>"
                                    class="w-8 h-8 flex items-center justify-center bg-surface text-ink-muted hover:bg-surface-secondary transition-colors disabled:opacity-30 disabled:cursor-not-allowed text-sm font-medium select-none">
                                    &plus;
                                </button>
                            </div>
                            <div class="text-end shrink-0 w-20">
                                <div class="text-xs font-semibold text-ink tabular-nums">
                                    <?php echo e(currency((float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1))); ?>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-ink-muted py-6 text-center"><?php echo e(__('merchant_panel.no_items')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php echo $__env->make('livewire.merchant.orders.partials.orders-items-modal-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($itemsModalKind === 'price'): ?>
    <div @edz-modal-closed="$wire.closeItemsModal()">
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'md','wire:key' => 'items-price-modal-'.e($itemsModalOrderId).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => true,'size' => 'md','wire:key' => 'items-price-modal-'.e($itemsModalOrderId).'']); ?>
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'tag','class' => 'w-5 h-5 text-ink-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'tag','class' => 'w-5 h-5 text-ink-muted']); ?>
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
                    <h3 class="text-base font-bold text-ink"><?php echo e(__('merchant_panel.price')); ?></h3>
                    <span class="text-xs text-ink-muted">(<?php echo e(count($orderItems)); ?>)</span>
                </div>

                <div class="max-h-[45vh] overflow-y-auto edz-scroll divide-y divide-surface-border/70 rounded-xl border border-surface-border">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-3 px-3 py-2.5">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-ink truncate"><?php echo e($item['name'] ?? '—'); ?></div>
                                <div class="text-[11px] text-ink-muted truncate" dir="ltr"><?php echo e($item['sku'] ?? ''); ?></div>
                            </div>
                            <input type="number" value="<?php echo e($item['price'] ?? 0); ?>"
                                wire:change="updateInlineItemPrice(<?php echo e($idx); ?>, parseFloat($event.target.value))"
                                step="10" min="0" class="edz-input text-sm w-24 text-center py-1.5"
                                placeholder="<?php echo e(__('merchant_panel.price')); ?>">
                            <div class="text-end shrink-0 w-20">
                                <div class="text-xs font-semibold text-ink tabular-nums">
                                    <?php echo e(currency((float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1))); ?>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-ink-muted py-6 text-center"><?php echo e(__('merchant_panel.no_items')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php echo $__env->make('livewire.merchant.orders.partials.orders-items-modal-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\orders-items-edit-modals.blade.php ENDPATH**/ ?>