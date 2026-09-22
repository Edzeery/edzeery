<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Domains\Order\Models\ConfirmationShift;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

?>

<div>
    <?php
        $SHIFT_TYPES = [
            'morning'   => __('merchant_panel.shift_morning'),
            'afternoon' => __('merchant_panel.shift_afternoon'),
            'evening'   => __('merchant_panel.shift_evening'),
            'full_day'  => __('merchant_panel.shift_full_day'),
            'custom'    => __('merchant_panel.shift_custom'),
        ];

        $DAYS_OF_WEEK = [
            1 => __('merchant_panel.monday'),
            2 => __('merchant_panel.tuesday'),
            3 => __('merchant_panel.wednesday'),
            4 => __('merchant_panel.thursday'),
            5 => __('merchant_panel.friday'),
            6 => __('merchant_panel.saturday'),
            7 => __('merchant_panel.sunday'),
        ];
    ?>

    <?php echo $__env->make('livewire.merchant.order-settings.partials.overview', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('livewire.merchant.order-settings.partials.tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('livewire.merchant.order-settings.partials.shifts-tab', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'overflow'): ?>
        <div class="max-w-2xl">
            <?php echo $__env->make('livewire.merchant.order-distribution-settings.partials.overflow-settings', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('livewire.merchant.order-settings.partials.assignments-tab', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('livewire.merchant.order-settings.partials.shift-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('livewire.merchant.order-settings.partials.assignments-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/order-settings.blade.php ENDPATH**/ ?>