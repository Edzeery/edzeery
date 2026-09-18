

<?php
    $mobileStatusKit = $s['tracking_status']
        ? \Edzeery\MyStatusKit\Facades\Status::for('tracking', $s['tracking_status'])
        : null;
?>
<div class="edz-card p-4" wire:key="card-<?php echo e($s['id']); ?>">
    <div class="flex items-center justify-between gap-2">
        <div class="font-mono font-medium text-ink">#<?php echo e($s['number']); ?></div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mobileStatusKit): ?>
            <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e(__('order_flow.tracking_history')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('order_flow.tracking_history')).'']); ?>
                <button type="button" wire:click="openStatusHistory('<?php echo e($s['id']); ?>')"
                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer hover:opacity-80 <?php echo e($mobileStatusKit->color()); ?>">
                    <?php echo $mobileStatusKit->icon(null, 'w-3.5 h-3.5 shrink-0'); ?>

                    <?php echo e($mobileStatusKit->label()); ?>

                </button>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $attributes = $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $component = $__componentOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
        <?php elseif(! empty($s['tracking_number'])): ?>
            <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-surface-tertiary text-ink-muted">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'clock','class' => 'w-3.5 h-3.5 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','class' => 'w-3.5 h-3.5 shrink-0']); ?>
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
                <?php echo e(__('order_flow.tracking_status_unknown')); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <div class="mt-2 text-sm text-ink"><?php echo e($s['customer']); ?>

        <span class="text-xs text-ink-muted" dir="ltr">• <?php echo e($s['phone']); ?></span>
    </div>
    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-muted">
        <span><?php echo e($s['city']); ?></span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($s['state'])): ?>
            <span>•</span>
            <span><?php echo e($s['state']); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->trackingTab === 'rider'): ?>
            <span>•</span>
            <span><?php echo e($s['delivery_rider'] ?: '—'); ?></span>
        <?php elseif(in_array('provider', $this->visibleColumns, true)): ?>
            <span>•</span>
            <span><?php echo e($s['provider']); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($s['tracking_number'])): ?>
                <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e($s['tracking_number']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e($s['tracking_number']).'']); ?>
                    <button
                        x-on:click="navigator.clipboard.writeText('<?php echo e($s['tracking_number']); ?>').then(() => EdzSwal.success('', '<?php echo e(__('order_flow.copy_done')); ?>'))"
                        class="inline-flex items-center gap-1 font-mono text-accent-600">
                        <?php echo e($s['tracking_number']); ?>

                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'clipboard','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard','class' => 'w-3 h-3']); ?>
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
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $attributes = $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $component = $__componentOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($s['delivery_type'] ?? 'home') === 'stopdesk'): ?>
                    <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e(__('order_flow.delivery_type_stopdesk')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('order_flow.delivery_type_stopdesk')).'']); ?>
                        <span class="inline-flex items-center rounded-md bg-accent-surface text-accent-fg text-[10px] font-bold px-1.5 py-0.5 leading-tight">SD</span>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $attributes = $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $component = $__componentOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e(__('order_flow.delivery_type_home')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('order_flow.delivery_type_home')).'']); ?>
                        <span class="inline-flex items-center rounded-md bg-surface-tertiary text-ink-muted text-[10px] font-bold px-1.5 py-0.5 leading-tight">HM</span>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $attributes = $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $component = $__componentOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <div class="mt-3 flex items-center justify-between gap-2">
        <span
            class="font-semibold text-ink tabular-nums"><?php echo e($s['total']); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s['shipped_at']): ?>
                <span class="ms-1 font-normal text-xs text-ink-muted">• <?php echo e(\Carbon\Carbon::parse($s['shipped_at'])->format('Y-m-d H:i')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </span>
        <div class="flex items-center gap-1.5">
            <button wire:click="openDrawer('<?php echo e($s['id']); ?>')"
                class="edz-btn edz-btn--ghost edz-btn--xs">
                <?php echo e(__('buttons.view')); ?>

            </button>
            <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e(__('merchant_panel.actions')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('merchant_panel.actions')).'']); ?>
                <div class="relative shrink-0" x-data="edzRowMenu($el)" @click.away="close()">
                    <button type="button" x-ref="trigger" @click.prevent="toggle()"
                        class="edz-btn edz-btn--ghost edz-btn--xs"
                        aria-haspopup="menu" :aria-expanded="open">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'ellipsis-horizontal','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'ellipsis-horizontal','class' => 'w-4 h-4']); ?>
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
                <?php if (isset($component)) { $__componentOriginal7087b0753e523d7f0d0628a70db89f2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7087b0753e523d7f0d0628a70db89f2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.mobile-bottom-sheet','data' => ['title' => __('merchant_panel.actions'),'icon' => 'ellipsis-horizontal','closeExpr' => 'close()','smWidth' => 'sm:w-52','smPad' => 'sm:p-1.5 sm:pb-1.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.mobile-bottom-sheet'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('merchant_panel.actions')),'icon' => 'ellipsis-horizontal','close-expr' => 'close()','sm-width' => 'sm:w-52','sm-pad' => 'sm:p-1.5 sm:pb-1.5']); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($s['is_trashed'] ?? false) && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value)): ?>
                            <button type="button" wire:click="restoreOrder('<?php echo e($s['id']); ?>')"
                                @click="open = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-surface-secondary">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-uturn-left','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-uturn-left','class' => 'w-4 h-4']); ?>
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
                                <?php echo e(__('merchant.restore_order')); ?>

                            </button>
                            <button type="button"
                                x-on:click="EdzSwal.confirmAction('<?php echo e(__('order_flow.permanent_delete_title')); ?>', '<?php echo e(__('order_flow.permanent_delete_confirm')); ?>', { confirmText: '<?php echo e(__('merchant.delete_permanently')); ?>', confirmColor: '#ef4444' }).then((ok) => { if (ok) $wire.forceDeleteOrder('<?php echo e($s['id']); ?>'); })"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-danger-600 hover:bg-surface-secondary">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trash','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'w-4 h-4']); ?>
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
                                <?php echo e(__('merchant.delete_permanently')); ?>

                            </button>
                        <?php else: ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($s['can_edit_order'] ?? false) && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                                <button type="button" wire:click="openEditModal('<?php echo e($s['id']); ?>')"
                                    @click="open = false"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-surface-secondary">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'pencil-square','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pencil-square','class' => 'w-4 h-4']); ?>
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
                                    <?php echo e(__('merchant_panel.edit')); ?>

                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s['can_cancel_shipment'] ?? false): ?>
                                <button type="button" @click="open = false"
                                    x-on:click="EdzSwal.confirmAction('<?php echo e(__('order_flow.cancel_shipment_title')); ?>', '<?php echo e(__('order_flow.cancel_shipment_confirm')); ?>', { confirmText: '<?php echo e(__('order_flow.cancel_shipment')); ?>', confirmColor: '#d97706' }).then((ok) => { if (ok) $wire.cancelShipment('<?php echo e($s['id']); ?>'); })"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-warning-600 hover:bg-surface-secondary">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck-x-mark','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck-x-mark','class' => 'w-4 h-4']); ?>
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
                                    <?php echo e(__('order_flow.cancel_shipment')); ?>

                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <button type="button" wire:click="openLabel('<?php echo e($s['id']); ?>')" @click="open = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-surface-secondary">
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
                                <?php echo e(__('order_flow.print_label')); ?>

                            </button>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value)): ?>
                                <button type="button"
                                    x-on:click="EdzSwal.confirmAction('<?php echo e(__('order_flow.move_to_trash_title')); ?>', '<?php echo e(__('order_flow.move_to_trash_confirm')); ?>', { confirmText: '<?php echo e(__('merchant_panel.delete')); ?>', confirmColor: '#ef4444' }).then((ok) => { if (ok) $wire.deleteOrder('<?php echo e($s['id']); ?>'); })"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-danger-600 hover:bg-surface-secondary">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trash','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'w-4 h-4']); ?>
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
                                    <?php echo e(__('merchant_panel.delete')); ?>

                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7087b0753e523d7f0d0628a70db89f2f)): ?>
<?php $attributes = $__attributesOriginal7087b0753e523d7f0d0628a70db89f2f; ?>
<?php unset($__attributesOriginal7087b0753e523d7f0d0628a70db89f2f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7087b0753e523d7f0d0628a70db89f2f)): ?>
<?php $component = $__componentOriginal7087b0753e523d7f0d0628a70db89f2f; ?>
<?php unset($__componentOriginal7087b0753e523d7f0d0628a70db89f2f); ?>
<?php endif; ?>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $attributes = $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $component = $__componentOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/tracking/partials/tracking-mobile-card.blade.php ENDPATH**/ ?>