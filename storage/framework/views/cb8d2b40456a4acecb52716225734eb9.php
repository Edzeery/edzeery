
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->drawerTracking): ?>
    <div @edz-modal-closed.window="$wire.closeDrawer()">
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'lg','wire:key' => 'tracking-drawer']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isOpen' => true,'size' => 'lg','wire:key' => 'tracking-drawer']); ?>
            <div class="p-6">
                
                <div class="flex items-start gap-3">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-accent-surface text-accent-fg-strong shrink-0">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'w-5 h-5']); ?>
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
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <h3 class="text-base sm:text-lg font-bold text-ink">#<?php echo e($this->drawerTracking['number']); ?></h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->drawerTracking['tracking_status']): ?>
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-0.5 rounded-full <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', $this->drawerTracking['tracking_status'])->color()); ?>">
                                    <?php echo \Edzeery\MyStatusKit\Facades\Status::for('tracking', $this->drawerTracking['tracking_status'])->icon(null, 'w-3.5 h-3.5 shrink-0'); ?>

                                    <span><?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', $this->drawerTracking['tracking_status'])->label()); ?></span>
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p class="mt-0.5 text-sm font-medium text-ink"><?php echo e($this->drawerTracking['customer']); ?></p>
                        <p class="text-xs text-ink-muted" dir="ltr"><?php echo e($this->drawerTracking['phone']); ?></p>
                    </div>
                </div>

                
                <section class="mt-5">
                    <h4
                        class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
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
                        <?php echo e(__('order_flow.carrier_card')); ?>

                    </h4>
                    <dl
                        class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0"><?php echo e(__('order_flow.tracking_provider')); ?></dt>
                            <dd class="text-ink text-end"><?php echo e($this->drawerTracking['provider']); ?></dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0"><?php echo e(__('merchant_panel.tracking_number')); ?></dt>
                            <dd class="text-ink text-end font-mono">
                                <?php echo e($this->drawerTracking['tracking_number'] ?? '—'); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($this->drawerTracking['tracking_number'])): ?>
                                    <button
                                        x-on:click="navigator.clipboard.writeText('<?php echo e($this->drawerTracking['tracking_number']); ?>').then(() => EdzSwal.toast ? EdzSwal.toast('<?php echo e(__('order_flow.copy_done')); ?>') : null)"
                                        class="text-accent-600 hover:text-accent-700 ms-1 align-middle"
                                        title="<?php echo e(__('order_flow.tracking_number_copy')); ?>">
                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'clipboard','class' => 'w-3.5 h-3.5 inline-block']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard','class' => 'w-3.5 h-3.5 inline-block']); ?>
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
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0"><?php echo e(__('merchant_panel.shipped_at')); ?></dt>
                            <dd class="text-ink text-end">
                                <?php echo e($this->drawerTracking['shipped_at'] ? \Carbon\Carbon::parse($this->drawerTracking['shipped_at'])->format('M d, Y H:i') : '—'); ?>

                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0"><?php echo e(__('merchant_panel.delivered_at')); ?></dt>
                            <dd class="text-ink text-end">
                                <?php echo e($this->drawerTracking['delivered_at'] ? \Carbon\Carbon::parse($this->drawerTracking['delivered_at'])->format('M d, Y H:i') : '—'); ?>

                            </dd>
                        </div>
                    </dl>
                </section>

                
                <section class="mt-5">
                    <h4
                        class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'bag','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bag','class' => 'w-4 h-4']); ?>
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
                        <?php echo e(__('order_flow.shipment_summary')); ?>

                    </h4>
                    <dl
                        class="rounded-xl border border-surface-border divide-y divide-surface-border overflow-hidden bg-surface-tertiary/30 text-sm">
                        <div class="flex items-start justify-between gap-3 px-3 py-2">
                            <dt class="text-ink-muted shrink-0"><?php echo e(__('merchant_panel.city')); ?></dt>
                            <dd class="text-ink text-end"><?php echo e($this->drawerTracking['city']); ?></dd>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($this->drawerTracking['address'])): ?>
                            <div class="flex items-start justify-between gap-3 px-3 py-2">
                                <dt class="text-ink-muted shrink-0"><?php echo e(__('merchant_panel.address')); ?></dt>
                                <dd class="text-ink text-end min-w-0">
                                    <?php echo e(\Illuminate\Support\Str::limit($this->drawerTracking['address'], 60)); ?></dd>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex items-start justify-between gap-3 px-3 py-2.5 bg-surface font-bold text-ink">
                            <dt><?php echo e(__('merchant_panel.total')); ?></dt>
                            <dd class="tabular-nums"><?php echo e($this->drawerTracking['total']); ?></dd>
                        </div>
                    </dl>
                </section>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value) && $this->drawerOrderId): ?>
                    <section class="mt-5">
                        <h4 class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                            <?php echo e(__('merchant_panel.actions')); ?>

                        </h4>
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="trackingAction('<?php echo e($this->drawerOrderId); ?>', 'in_transit')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', 'in_transit')->label()); ?>

                            </button>
                            <button wire:click="trackingAction('<?php echo e($this->drawerOrderId); ?>', 'out_for_delivery')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', 'out_for_delivery')->label()); ?>

                            </button>
                            <button wire:click="trackingAction('<?php echo e($this->drawerOrderId); ?>', 'failed_attempt')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', 'failed_attempt')->label()); ?>

                            </button>
                            <button wire:click="trackingAction('<?php echo e($this->drawerOrderId); ?>', 'returning')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', 'returning')->label()); ?>

                            </button>
                            <button wire:click="trackingAction('<?php echo e($this->drawerOrderId); ?>', 'delivered')"
                                class="edz-btn edz-btn--primary edz-btn--sm">
                                <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', 'delivered')->label()); ?>

                            </button>
                            <button wire:click="trackingAction('<?php echo e($this->drawerOrderId); ?>', 'returned')"
                                class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600">
                                <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', 'returned')->label()); ?>

                            </button>
                            <button wire:click="trackingAction('<?php echo e($this->drawerOrderId); ?>', 'lost')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', 'lost')->label()); ?>

                            </button>
                            <button wire:click="trackingAction('<?php echo e($this->drawerOrderId); ?>', 'damaged')"
                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('tracking', 'damaged')->label()); ?>

                            </button>
                        </div>
                    </section>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php echo $__env->make('livewire.merchant.tracking.partials.tracking-history-timeline', [
                    'histories' => $this->drawerStatusHistories,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canViewDrawerEvents && !empty($this->drawerEvents)): ?>
                    <?php echo $__env->make('livewire.merchant.orders.partials.order-events-timeline', [
                        'events' => $this->drawerEvents,
                        'icon' => 'list-bullet',
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\tracking\partials\order-drawer.blade.php ENDPATH**/ ?>