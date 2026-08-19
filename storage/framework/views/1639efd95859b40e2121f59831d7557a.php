<section class="py-16 border-y border-gray-100 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-6">

        
        <div class="grid grid-cols-3 gap-8 mb-16">
            <?php
                $stats = [
                    ['value' => number_format($storeCount), 'label' => __('landing.stat_stores'), 'icon' => 'storefront-outline'],
                    ['value' => number_format($orderCount), 'label' => __('landing.stat_orders'), 'icon' => 'cart-outline'],
                    ['value' => number_format($userCount), 'label' => __('landing.stat_users'), 'icon' => 'people-outline'],
                ];
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div
                    data-aos="fade-up"
                    data-aos-delay="<?php echo e($loop->index * 100); ?>"
                    class="text-center"
                >
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-950/40 mb-3">
                        <ion-icon name="<?php echo e($stat['icon']); ?>" class="text-brand-600 dark:text-brand-400 text-xl"></ion-icon>
                    </div>
                    <div class="text-title-md lg:text-title-lg font-bold text-ink"><?php echo e($stat['value']); ?></div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?php echo e($stat['label']); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div
            data-aos="fade-up"
            class="text-center"
        >
            <p class="text-sm text-gray-400 dark:text-gray-500 mb-6"><?php echo e(__('landing.trusted_by')); ?></p>
            <div class="flex flex-wrap justify-center items-center gap-x-10 gap-y-4 opacity-40">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Company A', 'Company B', 'Company C', 'Company D', 'Company E']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="text-lg font-bold text-gray-400 dark:text-gray-600"><?php echo e($name); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

    </div>
</section>
<?php /**PATH C:\laragon\www\edzeery\resources\views/landing/sections/social-proof.blade.php ENDPATH**/ ?>