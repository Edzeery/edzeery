<?php if (isset($component)) { $__componentOriginalcbdb9614ce9918f9093053258b644089 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcbdb9614ce9918f9093053258b644089 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.landing-layout','data' => ['title' => __('landing.contact_us')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.landing-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('landing.contact_us'))]); ?>

    
    <section class="pt-16 pb-12 text-center">
        <div class="max-w-3xl mx-auto px-6" data-aos="fade-up">
            <div class="inline-flex items-center gap-2 bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-300 rounded-full px-4 py-1.5 text-sm font-medium mb-6">
                <ion-icon name="chatbubble-ellipses-outline" class="text-base"></ion-icon>
                <?php echo e(__('landing.contact_us')); ?>

            </div>
            <h1 class="text-4xl lg:text-5xl font-bold text-ink tracking-tight mb-5">
                <?php echo e(__('landing.contact_title')); ?>

            </h1>
            <p class="text-lg text-ink-muted leading-relaxed max-w-xl mx-auto">
                <?php echo e(__('landing.contact_subtitle')); ?>

            </p>
        </div>
    </section>

    
    <section class="pb-24">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid lg:grid-cols-5 gap-12 lg:gap-16">

                
                <div class="lg:col-span-2 space-y-8" data-aos="fade-up" data-aos-delay="100">

                    
                    <div class="group flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center shrink-0 group-hover:bg-brand-100 dark:group-hover:bg-brand-900/30 transition-colors">
                            <ion-icon name="mail-outline" class="text-xl text-brand-600 dark:text-brand-400"></ion-icon>
                        </div>
                        <div>
                            <h3 class="font-semibold text-ink text-sm mb-1">Email</h3>
                            <a href="mailto:<?php echo e(__('landing.contact_email')); ?>" class="text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                                <?php echo e(__('landing.contact_email')); ?>

                            </a>
                        </div>
                    </div>

                    
                    <div class="group flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center shrink-0 group-hover:bg-brand-100 dark:group-hover:bg-brand-900/30 transition-colors">
                            <ion-icon name="call-outline" class="text-xl text-brand-600 dark:text-brand-400"></ion-icon>
                        </div>
                        <div>
                            <h3 class="font-semibold text-ink text-sm mb-1"><?php echo e(__('storefront.phone')); ?></h3>
                            <a href="tel:<?php echo e(__('landing.contact_phone')); ?>" class="text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                                <?php echo e(__('landing.contact_phone')); ?>

                            </a>
                        </div>
                    </div>

                    
                    <div class="group flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center shrink-0 group-hover:bg-brand-100 dark:group-hover:bg-brand-900/30 transition-colors">
                            <ion-icon name="location-outline" class="text-xl text-brand-600 dark:text-brand-400"></ion-icon>
                        </div>
                        <div>
                            <h3 class="font-semibold text-ink text-sm mb-1"><?php echo e(__('landing.links')); ?></h3>
                            <p class="text-ink-muted"><?php echo e(__('landing.contact_address')); ?></p>
                            <p class="text-ink-muted"><?php echo e(__('landing.contact_address_city')); ?></p>
                        </div>
                    </div>

                    
                    <div class="group flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center shrink-0 group-hover:bg-brand-100 dark:group-hover:bg-brand-900/30 transition-colors">
                            <ion-icon name="time-outline" class="text-xl text-brand-600 dark:text-brand-400"></ion-icon>
                        </div>
                        <div>
                            <h3 class="font-semibold text-ink text-sm mb-1"><?php echo e(__('landing.how_it_works_title')); ?></h3>
                            <p class="text-ink-muted"><?php echo e(__('landing.contact_hours')); ?></p>
                        </div>
                    </div>

                    
                    <div class="pt-4 border-t border-neutral-border dark:border-dark-border">
                        <p class="text-sm font-semibold text-ink mb-4"><?php echo e(__('landing.social') ?? 'Follow Us'); ?></p>
                        <div class="flex gap-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['logo-twitter' => 'X', 'logo-facebook' => 'Facebook', 'logo-instagram' => 'Instagram', 'logo-linkedin' => 'LinkedIn']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $icon => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="#" aria-label="<?php echo e($label); ?>"
                                   class="w-10 h-10 rounded-xl bg-neutral-secondary dark:bg-dark-secondary flex items-center justify-center text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all duration-200">
                                    <ion-icon name="<?php echo e($icon); ?>" class="text-lg"></ion-icon>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="lg:col-span-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-white dark:bg-dark-surface rounded-3xl border border-neutral-border dark:border-dark-border p-8 sm:p-10 shadow-sm">

                        <h2 class="text-xl font-bold text-ink mb-8"><?php echo e(__('landing.contact_form_title')); ?></h2>

                        <form x-data="{ sending: false }" @submit.prevent="sending = true; setTimeout(() => sending = false, 2000)" class="space-y-6">

                            
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-ink mb-2"><?php echo e(__('landing.contact_name')); ?> *</label>
                                    <input type="text" id="name" name="name" required
                                        placeholder="<?php echo e(__('landing.contact_name')); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-neutral-border dark:border-dark-border
                                               bg-surface-bg dark:bg-dark-secondary text-ink
                                               placeholder:text-ink-soft
                                               focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500
                                               transition-all duration-200" />
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-ink mb-2"><?php echo e(__('landing.contact_your_email')); ?> *</label>
                                    <input type="email" id="email" name="email" required
                                        placeholder="<?php echo e(__('landing.contact_your_email')); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-neutral-border dark:border-dark-border
                                               bg-surface-bg dark:bg-dark-secondary text-ink
                                               placeholder:text-ink-soft
                                               focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500
                                               transition-all duration-200" />
                                </div>
                            </div>

                            
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-ink mb-2"><?php echo e(__('landing.contact_phone')); ?></label>
                                    <input type="tel" id="phone" name="phone"
                                        placeholder="+966 5X XXX XXXX"
                                        class="w-full px-4 py-3 rounded-xl border border-neutral-border dark:border-dark-border
                                               bg-surface-bg dark:bg-dark-secondary text-ink
                                               placeholder:text-ink-soft
                                               focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500
                                               transition-all duration-200" />
                                </div>
                                <div>
                                    <label for="subject" class="block text-sm font-medium text-ink mb-2"><?php echo e(__('landing.contact_subject')); ?> *</label>
                                    <select id="subject" name="subject" required
                                        class="w-full px-4 py-3 rounded-xl border border-neutral-border dark:border-dark-border
                                               bg-surface-bg dark:bg-dark-secondary text-ink
                                               focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500
                                               transition-all duration-200">
                                        <option value=""><?php echo e(__('landing.contact_subject')); ?></option>
                                        <option value="general"><?php echo e(__('landing.contact_topic_general')); ?></option>
                                        <option value="support"><?php echo e(__('landing.contact_topic_support')); ?></option>
                                        <option value="sales"><?php echo e(__('landing.contact_topic_sales')); ?></option>
                                        <option value="partnership"><?php echo e(__('landing.contact_topic_partnership')); ?></option>
                                    </select>
                                </div>
                            </div>

                            
                            <div>
                                <label for="message" class="block text-sm font-medium text-ink mb-2"><?php echo e(__('landing.contact_message')); ?> *</label>
                                <textarea id="message" name="message" rows="5" required
                                    placeholder="<?php echo e(__('landing.contact_message')); ?>..."
                                    class="w-full px-4 py-3 rounded-xl border border-neutral-border dark:border-dark-border
                                           bg-surface-bg dark:bg-dark-secondary text-ink
                                           placeholder:text-ink-soft
                                           focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500
                                           transition-all duration-200 resize-none"></textarea>
                            </div>

                            
                            <button type="submit"
                                :disabled="sending"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2
                                       bg-brand-600 hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600
                                       text-white font-semibold px-8 py-3.5 rounded-xl
                                       shadow-sm shadow-brand-600/25
                                       transition-all duration-200
                                       disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="!sending">
                                    <span class="flex items-center gap-2">
                                        <ion-icon name="send-outline" class="text-lg"></ion-icon>
                                        <?php echo e(__('landing.contact_us')); ?>

                                    </span>
                                </template>
                                <template x-if="sending">
                                    <span class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <?php echo e(__('buttons.sending') ?? 'Sending...'); ?>

                                    </span>
                                </template>
                            </button>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcbdb9614ce9918f9093053258b644089)): ?>
<?php $attributes = $__attributesOriginalcbdb9614ce9918f9093053258b644089; ?>
<?php unset($__attributesOriginalcbdb9614ce9918f9093053258b644089); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcbdb9614ce9918f9093053258b644089)): ?>
<?php $component = $__componentOriginalcbdb9614ce9918f9093053258b644089; ?>
<?php unset($__componentOriginalcbdb9614ce9918f9093053258b644089); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views/landing/contact.blade.php ENDPATH**/ ?>