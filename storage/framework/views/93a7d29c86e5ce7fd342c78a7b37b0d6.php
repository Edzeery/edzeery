<?php

use App\Domains\Cart\Services\CartService;
use App\Models\Products\Product;

?>

<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product): ?>
    <div class="max-w-6xl mx-auto">
        
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-8">
            <a href="/" class="hover:text-gray-700 dark:hover:text-gray-200 transition"><?php echo e($product->store->name ?? __('storefront.back_to_store')); ?></a>
            <span>/</span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->categories->first()): ?>
                <span class="hover:text-gray-700 dark:hover:text-gray-200 transition"><?php echo e($product->categories->first()->name); ?></span>
                <span>/</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <span class="text-gray-900 dark:text-white"><?php echo e($product->name); ?></span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <div x-data="{ active: 0 }" class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->images->count()): ?>
                    <div class="aspect-square rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <img
                                x-show="active === <?php echo e($i); ?>"
                                src="<?php echo e(asset('storage/' . $img->path)); ?>"
                                alt="<?php echo e($product->name); ?>"
                                class="w-full h-full object-cover"
                                onerror="this.onerror=null;this.src='<?php echo e(asset('img/icons/noimg.png')); ?>'"
                            >
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->images->count() > 1): ?>
                        <div class="flex gap-3 overflow-x-auto pb-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button
                                    x-on:click="active = <?php echo e($i); ?>"
                                    :class="active === <?php echo e($i); ?> ? 'ring-2 ring-offset-2 store-border-primary' : 'ring-1 ring-gray-200 dark:ring-gray-700'"
                                    class="shrink-0 w-20 h-20 rounded-lg overflow-hidden ring-offset-white dark:ring-offset-gray-900"
                                >
                                    <img src="<?php echo e(asset('storage/' . $img->path)); ?>" alt="" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='<?php echo e(asset('img/icons/noimg.png')); ?>'">
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <div class="aspect-square rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden">
                        <img src="<?php echo e(asset('img/icons/noimg.png')); ?>" alt="<?php echo e($product->name); ?>" class="w-full h-full object-contain p-8 opacity-60">
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="flex flex-col">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->brand): ?>
                    <span class="text-sm font-semibold store-text-primary uppercase tracking-wider mb-2"><?php echo e($product->brand->name); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4"><?php echo e($product->name); ?></h1>

                <div class="mb-6">
                    <span class="text-3xl font-bold store-text-primary"><?php echo e(currency($product->price)); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->variants->count() === 1 && $product->variants->first()->compare_price): ?>
                        <span class="text-lg text-gray-400 line-through ml-3"><?php echo e(currency($product->variants->first()->compare_price)); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->short_description): ?>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6"><?php echo e($product->short_description); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->variants->count() > 1): ?>
                    <div class="mb-6" x-data="{ selected: '<?php echo e($product->variants->first()?->id); ?>' }">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"><?php echo e(__('storefront.options')); ?></label>
                        <div class="flex flex-wrap gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button
                                    type="button"
                                    x-on:click="selected = '<?php echo e($variant->id); ?>'"
                                    :class="selected === '<?php echo e($variant->id); ?>' ? 'store-border-primary store-bg-primary/10 store-text-primary ring-1' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500'"
                                    class="border-2 rounded-lg px-5 py-2.5 text-sm font-medium transition ring-transparent"
                                >
                                    <?php echo e($variant->name); ?>

                                    <span class="ml-1 text-xs opacity-70"><?php echo e(currency($variant->price)); ?></span>
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <button
                            type="button"
                            x-on:click="$wire.addToCart(selected)"
                            class="mt-6 w-full sm:w-auto store-btn-primary text-white font-bold py-3.5 px-10 rounded-lg transition text-base flex items-center justify-center gap-2"
                        >
                            <ion-icon name="cart-outline" class="text-xl"></ion-icon>
                            <?php echo e(__('storefront.add_to_cart')); ?>

                        </button>
                    </div>
                <?php else: ?>
                    <button
                        type="button"
                        wire:click="addToCart('<?php echo e($product->variants->first()?->id); ?>')"
                        class="w-full sm:w-auto store-btn-primary text-white font-bold py-3.5 px-10 rounded-lg transition text-base flex items-center justify-center gap-2"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                    >
                        <ion-icon name="cart-outline" class="text-xl"></ion-icon>
                        <span wire:loading.remove><?php echo e(__('storefront.add_to_cart')); ?></span>
                        <span wire:loading><?php echo e(__('storefront.placing')); ?></span>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="mt-10 pt-8 border-t border-gray-200 dark:border-gray-700 grid grid-cols-3 gap-4">
                    <div class="flex flex-col items-center text-center">
                        <ion-icon name="shield-checkmark-outline" class="text-2xl store-text-primary mb-1"></ion-icon>
                        <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.secure_payment')); ?></span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <ion-icon name="car-outline" class="text-2xl store-text-primary mb-1"></ion-icon>
                        <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.fast_delivery')); ?></span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <ion-icon name="refresh-outline" class="text-2xl store-text-primary mb-1"></ion-icon>
                        <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.easy_returns')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->description): ?>
        <div class="mt-16 pt-12 border-t border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6"><?php echo e(__('storefront.product_details')); ?></h2>
            <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                <?php echo nl2br(e($product->description)); ?>

            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-20">
        <ion-icon name="bag-outline" class="text-6xl text-gray-300 dark:text-gray-600 mb-4"></ion-icon>
        <p class="text-gray-500 dark:text-gray-400 text-lg"><?php echo e(__('storefront.product_not_found')); ?></p>
        <a href="/" class="mt-4 inline-block store-btn-primary text-white font-semibold py-2.5 px-6 rounded-lg transition">
            <?php echo e(__('storefront.back_to_store')); ?>

        </a>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/storefront/product-detail.blade.php ENDPATH**/ ?>