<?php

use App\Models\Products\Product;

?>

<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product): ?>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('hero', $sections)): ?>
    <section class="relative overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->brand): ?>
                        <span class="inline-block text-sm font-semibold store-text-primary mb-3 uppercase tracking-wider"><?php echo e($product->brand->name); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <h1 class="text-2xl sm:text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight mb-6">
                        <?php echo e($product->name); ?>

                    </h1>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->short_description ?? $product->description): ?>
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed">
                            <?php echo e($product->short_description ?? Str::limit($product->description, 200)); ?>

                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="flex items-baseline gap-3 mb-8">
                        <span class="text-2xl sm:text-3xl lg:text-4xl font-bold store-text-primary"><?php echo e(currency($product->price)); ?></span>
                    </div>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->variants->count() > 1): ?>
                        <div class="mb-6" x-data="{ selected: '<?php echo e($product->variants->first()?->id); ?>' }">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?php echo e(__('storefront.options')); ?></label>
                            <div class="flex flex-wrap gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button
                                        type="button"
                                        x-on:click="selected = '<?php echo e($variant->id); ?>'"
                                        :class="selected === '<?php echo e($variant->id); ?>' ? 'store-border-primary store-bg-primary-soft store-text-primary' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300'"
                                        class="border-2 rounded-lg px-4 py-2 text-sm font-medium transition cursor-pointer"
                                    >
                                        <?php echo e($variant->name); ?>

                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <button
                                type="button"
                                x-on:click="$wire.addToCart(selected)"
                                class="mt-4 w-full sm:w-auto store-btn-primary text-white font-bold py-3 px-8 rounded-lg transition text-lg"
                            >
                                <ion-icon name="cart-outline" class="mr-2"></ion-icon>
                                <?php echo e(__('storefront.add_to_cart')); ?>

                            </button>
                        </div>
                    <?php else: ?>
                        <button
                            type="button"
                            wire:click="addToCart(null)"
                            class="store-btn-primary text-white font-bold py-3 px-8 rounded-lg transition text-lg"
                            wire:loading.attr="disabled"
                        >
                            <ion-icon name="cart-outline" class="mr-2"></ion-icon>
                            <?php echo e(__('storefront.add_to_cart')); ?>

                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="relative">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->images->count()): ?>
                        <img
                            src="<?php echo e(asset('storage/' . $product->images->first()->path)); ?>"
                            alt="<?php echo e($product->name); ?>"
                            class="rounded-2xl shadow-2xl w-full object-cover aspect-square"
                            onerror="this.onerror=null;this.src='<?php echo e(asset('img/icons/noimg.png')); ?>'"
                        >
                    <?php else: ?>
                        <div class="rounded-2xl shadow-2xl bg-gray-200 dark:bg-gray-700 w-full aspect-square flex items-center justify-center overflow-hidden">
                            <img src="<?php echo e(asset('img/icons/noimg.png')); ?>" alt="<?php echo e($product->name); ?>" class="w-full h-full object-contain p-8 opacity-60">
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->description && in_array('description', $sections)): ?>
    <section class="py-16 bg-white dark:bg-gray-800">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center"><?php echo e($section_content['description']['title'] ?? __('storefront.product_details')); ?></h2>
            <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                <?php echo nl2br(e($product->description)); ?>

            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('social_proof', $sections)): ?>
    <?php $sp = $section_content['social_proof'] ?? []; ?>
    <section class="py-16 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8"><?php echo e($sp['title'] ?? __('storefront.why_customers_love_us')); ?></h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ($sp['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 store-bg-primary-soft rounded-full flex items-center justify-center mb-4">
                            <ion-icon name="<?php echo e($item['icon'] ?? 'checkmark-outline'); ?>" class="text-2xl store-text-primary"></ion-icon>
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white"><?php echo e($item['title'] ?? ''); ?></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?php echo e($item['description'] ?? ''); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('faq', $sections)): ?>
    <?php $faq = $section_content['faq'] ?? []; ?>
    <section class="py-16 bg-white dark:bg-gray-800" x-data="{ openFaq: null }">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 text-center"><?php echo e($faq['title'] ?? __('storefront.faq')); ?></h2>
            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ($faq['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faqItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                        <button
                            x-on:click="openFaq = openFaq === <?php echo e($loop->index); ?> ? null : <?php echo e($loop->index); ?>"
                            :aria-expanded="openFaq === <?php echo e($loop->index); ?>"
                            class="w-full px-6 py-4 text-start flex items-center justify-between"
                        >
                            <span class="font-medium text-gray-900 dark:text-white"><?php echo e($faqItem['question'] ?? ''); ?></span>
                            <ion-icon :name="openFaq === <?php echo e($loop->index); ?> ? 'chevron-up-outline' : 'chevron-down-outline'" class="text-gray-500 dark:text-gray-400"></ion-icon>
                        </button>
                        <div x-show="openFaq === <?php echo e($loop->index); ?>" x-transition class="px-6 pb-4">
                            <p class="text-gray-600 dark:text-gray-300"><?php echo e($faqItem['answer'] ?? ''); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('cta', $sections)): ?>
    <?php $cta = $section_content['cta'] ?? []; ?>
    <section class="py-16 store-gradient">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4"><?php echo e($cta['title'] ?? __('storefront.ready_to_order')); ?></h2>
            <p class="text-white/80 mb-8 text-lg"><?php echo e($cta['description'] ?? __('storefront.get_yours_now')); ?></p>
            <a
                href="#"
                x-on:click.prevent="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="inline-flex items-center gap-2 bg-white dark:bg-gray-100 font-bold py-3 px-8 rounded-lg hover:bg-white/90 dark:hover:bg-white transition text-lg store-text-primary"
            >
                <ion-icon name="cart-outline"></ion-icon>
                <?php echo e($cta['button_text'] ?? __('storefront.order_now')); ?>

            </a>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
    <div class="text-center py-20">
        <p class="text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.no_product_available')); ?></p>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\storefront\templates\single-product.blade.php ENDPATH**/ ?>