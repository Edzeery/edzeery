

<?php
    // Mobile rows live inside the orderMoreMenu Alpine scope: a separate
    // @click="close()" mirrors the established confirm/send pattern.
    $icon = 'w-4 h-4 shrink-0';
    $btnClass = $layout === 'list'
        ? 'w-full text-left flex items-center gap-2 px-2.5 min-h-[44px] rounded-lg text-sm hover:bg-surface-tertiary disabled:opacity-50'
        : 'edz-btn edz-btn--ghost edz-btn--xs shrink-0';
    $confirmBtnClass = $layout === 'list'
        ? 'w-full text-left flex items-center gap-2 px-2.5 min-h-[44px] rounded-lg text-sm font-semibold bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50'
        : 'edz-btn edz-btn--primary edz-btn--xs shrink-0';
?>

<div class="<?php echo e($layout === 'list' ? 'flex flex-col gap-0.5' : 'flex items-center justify-end gap-1 flex-nowrap'); ?>">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layout === 'compact'): ?>
        <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e(__('merchant.order_details')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('merchant.order_details')).'']); ?>
            <button wire:click="openOrderDetails('<?php echo e($orderId); ?>')"
                class="<?php echo e($btnClass); ?>"
                aria-label="<?php echo e(__('merchant.order_details')); ?>">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'info-circle','class' => ''.e($icon).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'info-circle','class' => ''.e($icon).'']); ?>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($events ?? false): ?>
            <?php echo $__env->make('livewire.merchant.orders.partials.order-events-menu', [
                'orderId' => $orderId,
                'order' => $order,
                'canViewEvents' => $order['can_view_events'] ?? false,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layout === 'compact'
    && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_CONFIRM->value)
    && !$showTrash && ($order['can_confirm'] ?? false)): ?>
        <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e($layout === 'compact' ? __('order_flow.confirm_title') : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e($layout === 'compact' ? __('order_flow.confirm_title') : '').'']); ?>
        <button wire:click="openConfirmModal('<?php echo e($orderId); ?>')"
            <?php if($layout === 'list'): ?> @click="close()" <?php endif; ?>
            class="<?php echo e($confirmBtnClass); ?>"
            <?php if($layout === 'compact'): ?> aria-label="<?php echo e(__('order_flow.confirm_title')); ?>" <?php endif; ?>>
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'phone','class' => ''.e($icon).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'phone','class' => ''.e($icon).'']); ?>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layout === 'list'): ?>
                <span><?php echo e(__('order_flow.confirm_title')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)
     && !$showTrash && in_array($order['status_key'] ?? null, ['confirmed', 'preparing'], true)): ?>
        <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e($layout === 'compact' ? __('order_flow.send_to_carrier') : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e($layout === 'compact' ? __('order_flow.send_to_carrier') : '').'']); ?>
        <button wire:click="sendConfirmedOrder('<?php echo e($orderId); ?>')"
            <?php if($layout === 'list'): ?> @click="close()" <?php endif; ?>
            class="<?php echo e($btnClass); ?>"
            <?php if($layout === 'compact'): ?> aria-label="<?php echo e(__('order_flow.send_to_carrier')); ?>" <?php endif; ?>>
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck','class' => ''.e($icon).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => ''.e($icon).'']); ?>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layout === 'list'): ?>
                <span><?php echo e(__('order_flow.send_to_carrier')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)
     && !$showTrash && in_array($order['status_key'] ?? null, ['shipped', 'in_transit', 'out_for_delivery'], true)): ?>
        <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e($layout === 'compact' ? __('order_flow.cancel_shipment') : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e($layout === 'compact' ? __('order_flow.cancel_shipment') : '').'']); ?>
        <button x-on:click="EdzSwal.confirmAction('<?php echo e(__('order_flow.cancel_shipment_title')); ?>', '<?php echo e(__('order_flow.cancel_shipment_confirm')); ?>', { confirmText: '<?php echo e(__('order_flow.cancel_shipment')); ?>', confirmColor: '#d97706' }).then((ok) => { if (ok) $wire.cancelShipment('<?php echo e($orderId); ?>'); })"
            <?php if($layout === 'list'): ?> @click="close()" <?php endif; ?>
            class="<?php echo e($layout === 'list'
                ? $btnClass . ' text-warning-600'
                : $btnClass . ' text-warning-600 hover:text-warning-700'); ?>"
            <?php if($layout === 'compact'): ?> aria-label="<?php echo e(__('order_flow.cancel_shipment')); ?>" <?php endif; ?>>
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-circle','class' => ''.e($icon).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-circle','class' => ''.e($icon).'']); ?>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layout === 'list'): ?>
                <span><?php echo e(__('order_flow.cancel_shipment')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value) && !$showTrash): ?>
        <?php $editCloser = $layout === 'list' ? '; close()' : ''; ?>
        <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e($layout === 'compact' ? __('merchant_panel.edit') : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e($layout === 'compact' ? __('merchant_panel.edit') : '').'']); ?>
        <button @click="$wire.openEditModal('<?php echo e($orderId); ?>')<?php echo e($editCloser); ?>"
            class="<?php echo e($btnClass); ?>"
            <?php if($layout === 'compact'): ?> aria-label="<?php echo e(__('merchant_panel.edit')); ?>" <?php endif; ?>>
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'edit','class' => ''.e($icon).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit','class' => ''.e($icon).'']); ?>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layout === 'list'): ?>
                <span><?php echo e(__('merchant_panel.edit')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
        <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e($layout === 'compact' ? __('merchant_panel.reassign') : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e($layout === 'compact' ? __('merchant_panel.reassign') : '').'']); ?>
        <button wire:click="openReassignModal('<?php echo e($orderId); ?>')"
            <?php if($layout === 'list'): ?> @click="close()" <?php endif; ?>
            class="<?php echo e($btnClass); ?>"
            <?php if($layout === 'compact'): ?> aria-label="<?php echo e(__('merchant_panel.reassign')); ?>" <?php endif; ?>>
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrows-right-left','class' => ''.e($icon).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrows-right-left','class' => ''.e($icon).'']); ?>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layout === 'list'): ?>
                <span><?php echo e(__('merchant_panel.reassign')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value)): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTrash): ?>
            <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e($layout === 'compact' ? __('merchant.restore_order') : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e($layout === 'compact' ? __('merchant.restore_order') : '').'']); ?>
            <button wire:click="restoreOrder('<?php echo e($orderId); ?>')"
                <?php if($layout === 'list'): ?> @click="close()" <?php endif; ?>
                class="<?php echo e($btnClass . ' text-success-600'); ?>"
                <?php if($layout === 'compact'): ?> aria-label="<?php echo e(__('merchant.restore_order')); ?>" <?php endif; ?>>
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-uturn-left','class' => ''.e($icon).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-uturn-left','class' => ''.e($icon).'']); ?>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layout === 'list'): ?>
                    <span><?php echo e(__('merchant.restore_order')); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
        <?php else: ?>
            <?php $deleteCloser = $layout === 'list' ? 'confirmDelete(); close()' : 'confirmDelete()'; ?>
            <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e($layout === 'compact' ? __('merchant.delete_permanently') : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e($layout === 'compact' ? __('merchant.delete_permanently') : '').'']); ?>
            <button
                class="<?php echo e($layout === 'list'
                    ? $btnClass . ' text-danger-600'
                    : $btnClass . ' text-danger-600 hover:text-danger-700'); ?>"
                x-on:click.prevent="<?php echo e($deleteCloser); ?>" :disabled="deleteLoading"
                :class="deleteLoading ? 'opacity-50' : ''"
                <?php if($layout === 'compact'): ?> aria-label="<?php echo e(__('merchant.delete_permanently')); ?>" <?php endif; ?>>
                <?php if (isset($component)) { $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.spinner','data' => ['show' => 'deleteLoading','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => 'deleteLoading','class' => 'w-3.5 h-3.5']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trash','xShow' => '!deleteLoading','class' => ''.e($icon).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','x-show' => '!deleteLoading','class' => ''.e($icon).'']); ?>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($layout === 'list'): ?>
                    <span x-show="!deleteLoading"><?php echo e(__('merchant.delete_permanently')); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\orders-table-actions-column.blade.php ENDPATH**/ ?>