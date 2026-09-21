<section class="py-24">
    <div class="max-w-6xl mx-auto px-6 text-center">

        <h2
            data-aos="fade-up"
            class="text-title-md lg:text-title-lg font-bold text-ink"
        >
            <?php echo e(__('landing.payments_title')); ?>

        </h2>

        <p
            data-aos="fade-up"
            data-aos-delay="100"
            class="mt-4 text-theme-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto"
        >
            <?php echo e(__('landing.payments_subtitle')); ?>

        </p>

        <div
            data-aos="fade-up"
            data-aos-delay="200"
            class="mt-14 flex flex-wrap justify-center gap-10 items-center"
        >
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                'logos:visa',
                'logos:mastercard',
                'logos:stripe',
                'logos:paypal',
                'cryptocurrency-color:usdt',
                'token-branded:binance',
                'arcticons:redotpay',
                'logos:google-pay',
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $icon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="text-5xl opacity-60 hover:opacity-100 transition">
                    <span class="iconify" data-icon="<?php echo e($icon); ?>"></span>
                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div
            data-aos="fade-up"
            data-aos-delay="300"
            class="mt-12 grid sm:grid-cols-3 gap-6 max-w-3xl mx-auto"
        >
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                ['icon' => 'shield-checkmark-outline', 'title' => __('landing.payment_secure'), 'desc' => __('landing.payment_secure_desc')],
                ['icon' => 'flash-outline', 'title' => __('landing.payment_instant'), 'desc' => __('landing.payment_instant_desc')],
                ['icon' => 'globe-outline', 'title' => __('landing.payment_global'), 'desc' => __('landing.payment_global_desc')],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-center">
                    <ion-icon name="<?php echo e($item['icon']); ?>" class="text-brand-600 dark:text-brand-400 text-3xl mb-3"></ion-icon>
                    <h4 class="text-sm font-semibold text-ink"><?php echo e($item['title']); ?></h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo e($item['desc']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    </div>
</section>
<?php /**PATH C:\laragon\www\edzeery\resources\views\landing\sections\payments.blade.php ENDPATH**/ ?>