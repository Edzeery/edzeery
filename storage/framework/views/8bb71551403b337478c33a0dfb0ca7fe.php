
<div x-data="{ delivery: $wire.form.delivery_type }"
    x-init="$watch('delivery', v => $wire.set('form.delivery_type', v))"
    x-effect="delivery = $wire.form.delivery_type">
    <label class="edz-label"><?php echo e(__('merchant_panel.shipping_partner')); ?></label>

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
        <div>
            <?php echo $__env->make('livewire.merchant.orders.partials.partner-picker', ['picker' => 'form'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div>
            <?php echo $__env->make('livewire.merchant.orders.partials.order-delivery-type-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <?php echo $__env->make('livewire.merchant.orders.partials.order-destination-fields', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/orders/partials/order-delivery-cascade.blade.php ENDPATH**/ ?>