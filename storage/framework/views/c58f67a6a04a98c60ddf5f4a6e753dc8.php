<footer
    <?php echo e($attributes->merge([
        'class' => 'mt-24 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-sm text-ink'
    ])); ?>

>
    <div class="max-w-7xl mx-auto px-6 py-12 grid md:grid-cols-4 gap-8">

        
        <div class="md:col-span-1">
            <div class="text-xl font-bold text-brand-600 dark:text-brand-400 mb-3"><?php echo e(config('app.name')); ?></div>
            <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                <?php echo e(__('landing.footer_desc')); ?>

            </p>
            
            <div class="flex gap-3 mt-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['logo-twitter', 'logo-facebook', 'logo-instagram', 'logo-linkedin']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $social): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="#" class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <ion-icon name="<?php echo e($social); ?>" class="text-lg"></ion-icon>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div>
            <h4 class="font-semibold mb-4 text-ink"><?php echo e(__('landing.product')); ?></h4>
            <ul class="space-y-2.5">
                <li><a href="#services" class="text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition"><?php echo e(__('landing.services')); ?></a></li>
                <li><a href="#pricing" class="text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition"><?php echo e(__('landing.pricing')); ?></a></li>
                <li><a href="#faq" class="text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition"><?php echo e(__('landing.faq_title')); ?></a></li>
            </ul>
        </div>

        
        <div>
            <h4 class="font-semibold mb-4 text-ink"><?php echo e(__('landing.company')); ?></h4>
            <ul class="space-y-2.5">
                <li><a href="#" class="text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition"><?php echo e(__('landing.about_us')); ?></a></li>
                <li><a href="<?php echo e(route('contact')); ?>" class="text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition"><?php echo e(__('landing.contact_us')); ?></a></li>
            </ul>
        </div>

        
        <div>
            <h4 class="font-semibold mb-4 text-ink"><?php echo e(__('landing.legal')); ?></h4>
            <ul class="space-y-2.5">
                <li><a href="#" class="text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition"><?php echo e(__('landing.privacy_policy')); ?></a></li>
                <li><a href="#" class="text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition"><?php echo e(__('landing.terms')); ?></a></li>
            </ul>
        </div>

    </div>

    <div class="border-t border-gray-100 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-6 py-5 flex flex-col sm:flex-row justify-between items-center gap-3">
            <p class="text-xs text-gray-400">
                &copy; <?php echo e(now()->year); ?> <?php echo e(config('app.name')); ?>. <?php echo e(__('landing.all_rights_reserved')); ?>

            </p>
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <a href="<?php echo e(route('landing')); ?>" class="hover:text-brand-600 transition"><?php echo e(__('landing.home')); ?></a>
                <a href="#services" class="hover:text-brand-600 transition"><?php echo e(__('landing.services')); ?></a>
                <a href="#pricing" class="hover:text-brand-600 transition"><?php echo e(__('landing.pricing')); ?></a>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\layouts\footer.blade.php ENDPATH**/ ?>