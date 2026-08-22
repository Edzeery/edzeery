<div class="max-w-lg mx-auto text-center py-16">
    <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
        <ion-icon name="checkmark-circle-outline" class="text-4xl text-green-600 dark:text-green-400"></ion-icon>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2"><?php echo e(__('storefront.order_placed')); ?></h1>
    <p class="text-gray-600 dark:text-gray-300 mb-3">
        <?php echo e(__('storefront.your_order_number')); ?>

    </p>
    <p class="text-lg font-mono font-bold store-text-primary mb-6">#<?php echo e($orderNumber); ?></p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-8"><?php echo e(__('storefront.we_will_contact_you')); ?></p>
    <a href="<?php echo e(route('storefront.home', ['store' => currentStore()?->slug ?? ''])); ?>"
       class="inline-flex items-center gap-2 store-btn-primary text-white font-semibold py-3 px-6 rounded-xl transition hover:shadow-lg">
        <ion-icon name="arrow-back-outline" class="text-lg"></ion-icon>
        <?php echo e(__('storefront.back_to_store')); ?>

    </a>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\storefront\order-success.blade.php ENDPATH**/ ?>