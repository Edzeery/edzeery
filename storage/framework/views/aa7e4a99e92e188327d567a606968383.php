<div x-data="orderProductPicker()">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCreateModal || $showEditModal): ?>
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'showCloseButton' => false,'preventClose' => true,'size' => 'lg','class' => ' edz-scroll','wire:key' => 'order-create-edit-'.e($showCreateModal ? 'create' : 'edit').'-'.e($showEditModal ? $editingOrderId : 'new').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isOpen' => true,'showCloseButton' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'preventClose' => true,'size' => 'lg','class' => ' edz-scroll','wire:key' => 'order-create-edit-'.e($showCreateModal ? 'create' : 'edit').'-'.e($showEditModal ? $editingOrderId : 'new').'']); ?>
            <form wire:submit="<?php echo e($showEditModal ? 'submitEdit' : 'submitCreate'); ?>">
                <div class="p-4 md:p-6 space-y-4 md:space-y-5">
                    
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">
                            <?php echo e($showEditModal ? __('merchant_panel.edit_order') : __('merchant_panel.new_order')); ?>

                        </h3>
                        <div class="flex items-center gap-2">
                            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="<?php echo e($showEditModal ? 'set(\'showEditModal\', false)' : 'set(\'showCreateModal\', false)'); ?>">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-5 h-5']); ?>
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
                    </div>

                    
                    <section data-form-section="customer"
                        class="rounded-xl border border-surface-border bg-surface p-4 md:p-5">
                        <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-3">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'user','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user','class' => 'w-4 h-4']); ?>
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
                            <?php echo e(__('merchant_panel.customer')); ?>

                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 min-[1440px]:grid-cols-3 gap-4">
                            <div>
                                <label class="edz-label"><?php echo e(__('merchant_panel.name')); ?> *</label>
                                <input type="text" wire:model="form.customer_name" class="edz-input text-sm" required>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.customer_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label class="edz-label"><?php echo e(__('merchant_panel.phone')); ?> *</label>
                                <input type="tel" wire:model="form.customer_phone" class="edz-input text-sm" required>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.customer_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label class="edz-label"><?php echo e(__('merchant_panel.phone_secondary')); ?></label>
                                <input type="tel" wire:model="form.phone_secondary" class="edz-input text-sm">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.phone_secondary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-4 border-t border-surface-border pt-4">
                            <label class="edz-label"><?php echo e(__('merchant_panel.notes')); ?></label>
                            <textarea wire:model="form.notes" rows="2" class="edz-input text-sm"></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </section>

                    
                    <div x-data="{ delivery: $wire.form.delivery_type }"
                        x-init="$watch('delivery', v => $wire.set('form.delivery_type', v))"
                        x-effect="delivery = $wire.form.delivery_type"
                        class="space-y-4 md:space-y-5">

                        
                        <section data-form-section="partner"
                            class="rounded-xl border border-surface-border bg-surface p-4 md:p-5">
                            <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-3">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'w-4 h-4']); ?>
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
                                <?php echo e(__('merchant_panel.shipping_partner')); ?>

                            </h4>
                            <?php echo $__env->make('livewire.merchant.orders.partials.order-form-delivery', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </section>

                        
                        <section data-form-section="address"
                            class="rounded-xl border border-surface-border bg-surface p-4 md:p-5">
                            <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-3">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'map-pin','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'map-pin','class' => 'w-4 h-4']); ?>
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
                                <?php echo e(__('merchant_panel.address')); ?>

                            </h4>
                            <div>
                                <label class="edz-label"><?php echo e(__('merchant_panel.address')); ?></label>
                                <input type="text" wire:model="form.address" class="edz-input text-sm">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <?php echo $__env->make('livewire.merchant.orders.partials.order-destination-fields', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </section>
                    </div>

                    
                    <section data-form-section="products"
                        class="rounded-xl border border-surface-border bg-surface p-4 md:p-5">
                        <label class="edz-label"><?php echo e(__('merchant_panel.products')); ?></label>

                        
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.items'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($form['items'])): ?>
                            <div data-order-items-list
                                class="mt-3 rounded-xl border border-surface-border divide-y divide-surface-border bg-surface-secondary overflow-y-auto max-h-72 edz-scroll">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $form['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div
                                        class="flex items-center gap-3 p-3">
                                        
                                        <img src="<?php echo e($item['image_url'] ?? asset('img/icons/noimg.png')); ?>"
                                            alt=""
                                            class="w-12 h-12 rounded-lg object-cover bg-surface shrink-0">

                                        
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-ink truncate">
                                                <?php echo e($item['name']); ?>

                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5">
                                                SKU: <?php echo e($item['sku'] ?? '—'); ?>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($item['stock'] ?? 0) <= 0): ?>
                                                    <span
                                                        class="text-danger-500 ml-2"><?php echo e(__('merchant_panel.out_of_stock')); ?></span>
                                                <?php elseif(($item['stock'] ?? 0) <= 5): ?>
                                                    <span class="text-warning-500 ml-2"><?php echo e($item['stock']); ?>

                                                        <?php echo e(__('merchant_panel.left')); ?></span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($item['preorder'] ?? false)): ?>
                                                    <span
                                                        class="text-success-600 ml-2"><?php echo e(__('merchant_panel.pre_order')); ?></span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>

                                        
                                        <?php
                                            $capReached = ($item['cap'] ?? null) !== null && ($item['quantity'] ?? 0) >= $item['cap'];
                                        ?>
                                        <div
                                            class="flex items-center rounded-lg border border-surface-border overflow-hidden shrink-0">
                                            <button type="button"
                                                wire:click="updateFormItemQty(<?php echo e($idx); ?>, <?php echo e(max(1, $item['quantity'] - 1)); ?>)"
                                                :disabled="<?php echo e($item['quantity'] <= 1 ? 'true' : 'false'); ?>"
                                                class="w-8 h-8 flex items-center justify-center bg-surface
                                                    text-ink-muted hover:bg-surface-secondary
                                                    transition-colors disabled:opacity-30 disabled:cursor-not-allowed
                                                    text-sm font-medium select-none">
                                                &minus;
                                            </button>
                                            <input type="number" value="<?php echo e($item['quantity']); ?>"
                                                wire:change="updateFormItemQty(<?php echo e($idx); ?>, parseInt($event.target.value))"
                                                min="1" <?php if(($item['cap'] ?? null) !== null): ?> max="<?php echo e($item['cap']); ?>" <?php endif; ?>
                                                class="w-10 h-8 text-center border-x border-surface-border
                                                    bg-transparent text-sm font-semibold text-ink
                                                    focus:outline-none focus:ring-0
                                                    [appearance:textfield]
                                                    [&::-webkit-outer-spin-button]:appearance-none
                                                    [&::-webkit-inner-spin-button]:appearance-none">
                                            <button type="button"
                                                wire:click="updateFormItemQty(<?php echo e($idx); ?>, <?php echo e($item['quantity'] + 1); ?>)"
                                                :disabled="<?php echo e($capReached ? 'true' : 'false'); ?>"
                                                class="w-8 h-8 flex items-center justify-center bg-surface
                                                    text-ink-muted hover:bg-surface-secondary
                                                    transition-colors disabled:opacity-30 disabled:cursor-not-allowed
                                                    text-sm font-medium select-none">
                                                &plus;
                                            </button>
                                        </div>

                                        
                                        <div class="shrink-0 hidden sm:block">
                                            <input type="number" value="<?php echo e($item['price']); ?>"
                                                wire:change="updateFormItemPrice(<?php echo e($idx); ?>, parseFloat($event.target.value))"
                                                step="10" min="0"
                                                class="edz-input text-xs w-20 text-center py-1"
                                                placeholder="<?php echo e(__('merchant_panel.price')); ?>">
                                        </div>

                                        
                                        <button type="button" wire:click="removeFormItem(<?php echo e($idx); ?>)"
                                            class="text-danger-400 hover:text-danger-600 shrink-0 p-1 rounded hover:bg-danger-surface transition-colors">
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
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </section>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($formDuplicateWarnings)): ?>
                        <div class="rounded-xl border border-warning/40 bg-warning/5 p-3">
                            <div class="flex items-center gap-2 text-warning mb-2">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'exclamation-triangle','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'exclamation-triangle','class' => 'w-4 h-4']); ?>
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
                                <span class="text-sm font-medium">
                                    <?php echo e(__('order_flow.duplicate_detected', ['count' => count($formDuplicateWarnings)])); ?>

                                </span>
                            </div>
                            <ul class="space-y-1.5 text-sm">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $formDuplicateWarnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-center justify-between gap-2">
                                        <button type="button"
                                            wire:click="set('showCreateModal', false); set('showEditModal', false); openOrderDetails('<?php echo e($dup['order_id']); ?>')"
                                            class="flex items-center gap-2 text-ink hover:text-brand-600 truncate">
                                            <span class="text-xs text-ink-muted"><?php echo e(__('merchant_panel.status')); ?></span>
                                            <span class="text-xs">
                                                <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('order', $dup['status_key'])->label()); ?>

                                            </span>
                                            <span class="truncate font-medium">#<?php echo e($dup['number']); ?></span>
                                            <span class="text-ink-muted text-xs shrink-0">
                                                <?php echo e(\Carbon\Carbon::parse($dup['created_at'])->diffForHumans()); ?>

                                            </span>
                                        </button>
                                        <span class="shrink-0 text-xs text-ink-muted">
                                            ×<?php echo e($dup['total_overlap_qty']); ?>

                                        </span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($form['items'])): ?>
                        <div class="rounded-xl border border-surface-border bg-surface p-4 md:p-5">
                            <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-3">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'banknotes','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'banknotes','class' => 'w-4 h-4']); ?>
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
                                <?php echo e(__('merchant_panel.order_summary')); ?>

                            </h4>
                            <?php echo $__env->make('livewire.merchant.orders.partials.order-financial-summary', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div class="flex justify-end gap-2 pt-4 border-t border-surface-border">
                        <button type="button" class="edz-btn edz-btn--ghost"
                            wire:click="<?php echo e($showEditModal ? 'set(\'showEditModal\', false)' : 'set(\'showCreateModal\', false)'); ?>">
                            <?php echo e(__('buttons.cancel')); ?>

                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 pointer-events-none">
                            <span><?php echo e($showEditModal ? __('merchant_panel.update') : __('merchant_panel.create')); ?></span>
                            <span
                                class="sr-only"><?php echo e($showEditModal ? __('merchant_panel.update') : __('merchant_panel.create')); ?></span>
                        </button>
                    </div>
                </div>
            </form>
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

</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\order-form-modal.blade.php ENDPATH**/ ?>