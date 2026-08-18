<section id="faq" class="py-24 bg-gray-50 dark:bg-[#0a0a0a]">
    <div class="max-w-3xl mx-auto px-6">

        <div class="text-center mb-14">
            <h2
                data-aos="fade-up"
                class="text-title-md lg:text-title-lg font-bold text-ink"
            >
                <?php echo e(__('landing.faq_title')); ?>

            </h2>
            <p
                data-aos="fade-up"
                data-aos-delay="100"
                class="mt-4 text-theme-xl text-gray-500 dark:text-gray-400"
            >
                <?php echo e(__('landing.faq_subtitle')); ?>

            </p>
        </div>

        <div class="space-y-4" x-data="{ openFaq: null }">
            <?php
                $faqs = [
                    ['key' => 'what_is_edzeery', 'question' => __('landing.faq_q1'), 'answer' => __('landing.faq_a1')],
                    ['key' => 'free_trial', 'question' => __('landing.faq_q2'), 'answer' => __('landing.faq_a2')],
                    ['key' => 'payment_methods', 'question' => __('landing.faq_q3'), 'answer' => __('landing.faq_a3')],
                    ['key' => 'can_cancel', 'question' => __('landing.faq_q4'), 'answer' => __('landing.faq_a4')],
                    ['key' => 'support', 'question' => __('landing.faq_q5'), 'answer' => __('landing.faq_a5')],
                    ['key' => 'multi_store', 'question' => __('landing.faq_q6'), 'answer' => __('landing.faq_a6')],
                ];
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div
                    data-aos="fade-up"
                    data-aos-delay="<?php echo e($loop->index * 60); ?>"
                    class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden"
                >
                    <button
                        @click="openFaq = openFaq === '<?php echo e($faq['key']); ?>' ? null : '<?php echo e($faq['key']); ?>'"
                        class="w-full flex items-center justify-between px-6 py-4 text-start"
                    >
                        <span class="text-sm font-semibold text-ink"><?php echo e($faq['question']); ?></span>
                        <ion-icon
                            name="chevron-down-outline"
                            class="text-gray-400 text-lg transition-transform duration-200"
                            :class="openFaq === '<?php echo e($faq['key']); ?>' ? 'rotate-180' : ''"
                        ></ion-icon>
                    </button>
                    <div
                        x-show="openFaq === '<?php echo e($faq['key']); ?>'"
                        x-collapse
                        class="px-6 pb-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                            <?php echo e($faq['answer']); ?>

                        </p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    </div>
</section>
<?php /**PATH C:\laragon\www\edzeery\resources\views/landing/sections/faq.blade.php ENDPATH**/ ?>