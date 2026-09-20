<?php

use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Order\Services\OrderTrackingAssignmentService;
use App\Domains\Order\Support\AssignmentCandidateResolver;
use App\Enums\Store\OrderStatus;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Team\StoreMembership;
use Livewire\Volt\Component;

?>

<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <?php if (isset($component)) { $__componentOriginal64446345db7363332d7ff2707d878bc4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal64446345db7363332d7ff2707d878bc4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.page-header','data' => ['title' => ''.e(__('merchant_panel.order_distribution_queue')).'','description' => ''.e(__('merchant_panel.order_distribution_queue_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(__('merchant_panel.order_distribution_queue')).'','description' => ''.e(__('merchant_panel.order_distribution_queue_desc')).'']); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal64446345db7363332d7ff2707d878bc4)): ?>
<?php $attributes = $__attributesOriginal64446345db7363332d7ff2707d878bc4; ?>
<?php unset($__attributesOriginal64446345db7363332d7ff2707d878bc4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal64446345db7363332d7ff2707d878bc4)): ?>
<?php $component = $__componentOriginal64446345db7363332d7ff2707d878bc4; ?>
<?php unset($__componentOriginal64446345db7363332d7ff2707d878bc4); ?>
<?php endif; ?>
    </div>

    <?php echo $__env->make('livewire.merchant.order-distribution-queue.partials.tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'confirmation'): ?>
        <?php echo $__env->make('livewire.merchant.order-distribution-queue.partials.queue-table', ['kind' => 'confirm'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('livewire.merchant.order-distribution-queue.partials.queue-table', ['kind' => 'track'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php echo $__env->make('livewire.merchant.orders.partials.reassign-modal', [
        'reassignOpen' => $reassignOpen,
        'reassignSubmit' => 'submitReassign',
        'reassignCloseSet' => 'reassignOpen',
        'reassignModel' => 'reassignMembershipId',
        'reassignTargetId' => $reassignMembershipId,
        'reassignCandidates' => $reassignCandidates,
        'reassignTitle' => __('merchant_panel.reassign_order'),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\order-distribution-queue.blade.php ENDPATH**/ ?>