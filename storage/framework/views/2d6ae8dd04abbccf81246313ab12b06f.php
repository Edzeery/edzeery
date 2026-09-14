
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="edz-label"><?php echo e(__('merchant_panel.shipment')); ?></label>
        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'form.shipment_type','options' => $this->formShipmentTypeOptions(),'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'form.shipment_type','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->formShipmentTypeOptions()),'size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $attributes = $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $component = $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.shipment_type'];
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
        <label class="edz-label"><?php echo e(__('merchant_panel.payment_method')); ?></label>
        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'form.payment_method','options' => [['value' => 'cod', 'label' => __('merchant_panel.cod')]],'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'form.payment_method','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['value' => 'cod', 'label' => __('merchant_panel.cod')]]),'size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $attributes = $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $component = $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
    </div>
</div>


<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
    <div>
        <label class="edz-label"><?php echo e(__('merchant_panel.delivery_company')); ?></label>
        <?php echo $__env->make('livewire.merchant.orders.partials.partner-picker', ['picker' => 'form'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <div>
        <?php echo $__env->make('livewire.merchant.orders.partials.order-delivery-type-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>


<?php
    // The tracking page reuses this partial too, but only the orders page
    // exposes formPartnerCapabilities() — degrade gracefully there.
    $formCaps = method_exists($this, 'formPartnerCapabilities')
        ? $this->formPartnerCapabilities()
        : ['refund_request' => false, 'can_open' => false, 'send_from_carrier_warehouse' => false];
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($formCaps['refund_request'] ?? false) || ($formCaps['can_open'] ?? false) || ($formCaps['send_from_carrier_warehouse'] ?? false)): ?>
    <div class="mt-4 flex flex-wrap items-center gap-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($formCaps['refund_request']): ?>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="form.refund_request" class="edz-checkbox" />
                <span class="text-sm text-ink"><?php echo e(__('merchant_panel.refund_request')); ?></span>
                <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['block' => true,'label' => ''.e(__('merchant_panel.refund_request_tooltip', ['definition' => __('merchant_panel.refund_request_definition')])).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['block' => true,'label' => ''.e(__('merchant_panel.refund_request_tooltip', ['definition' => __('merchant_panel.refund_request_definition')])).'']); ?>
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'information-circle','class' => 'w-4 h-4 text-ink-muted cursor-help']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'information-circle','class' => 'w-4 h-4 text-ink-muted cursor-help']); ?>
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
            </label>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($formCaps['can_open']): ?>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="form.can_open" class="edz-checkbox" />
                <span class="text-sm text-ink"><?php echo e(__('merchant_panel.can_open')); ?></span>
                <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['block' => true,'label' => ''.e(__('merchant_panel.can_open_tooltip', ['definition' => __('merchant_panel.can_open_definition')])).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['block' => true,'label' => ''.e(__('merchant_panel.can_open_tooltip', ['definition' => __('merchant_panel.can_open_definition')])).'']); ?>
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'information-circle','class' => 'w-4 h-4 text-ink-muted cursor-help']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'information-circle','class' => 'w-4 h-4 text-ink-muted cursor-help']); ?>
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
            </label>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($formCaps['send_from_carrier_warehouse']): ?>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="form.send_from_carrier_warehouse" class="edz-checkbox" />
                <span class="text-sm text-ink"><?php echo e(__('merchant_panel.send_from_carrier_warehouse')); ?></span>
                <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['block' => true,'label' => ''.e(__('merchant_panel.send_from_carrier_warehouse_tooltip', ['definition' => __('merchant_panel.send_from_carrier_warehouse_definition')])).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['block' => true,'label' => ''.e(__('merchant_panel.send_from_carrier_warehouse_tooltip', ['definition' => __('merchant_panel.send_from_carrier_warehouse_definition')])).'']); ?>
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'information-circle','class' => 'w-4 h-4 text-ink-muted cursor-help']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'information-circle','class' => 'w-4 h-4 text-ink-muted cursor-help']); ?>
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
            </label>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/orders/partials/order-form-delivery.blade.php ENDPATH**/ ?>