<?php

use App\Domains\Cart\Services\CartService;

?>

<div x-data="{ open: false }" class="relative" wire:poll.5s="refreshCart" x-on:cart-updated.window="$wire.refreshCart()">
    
    <button x-on:click="open = !open"
        class="relative p-2.5 sm:p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white min-h-[44px] min-w-[44px] flex items-center justify-center">
        <ion-icon name="cart-outline" class="text-2xl"></ion-icon>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
            <span
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                <?php echo e($count); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </button>

    
    <div x-show="open" x-on:click.away="open = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 sm:static sm:z-auto">
        
        <div x-show="open" class="fixed inset-0 bg-black/40 sm:hidden" x-on:click="open = false" x-transition.opacity></div>
        
        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:opacity-0"
            x-transition:enter-end="translate-y-0 sm:opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 sm:opacity-100"
            x-transition:leave-end="translate-y-full sm:opacity-0"
            class="fixed bottom-0 inset-x-0 sm:absolute sm:top-full sm:<?php echo e($alignment); ?> sm:mt-2 sm:w-80 max-h-[85vh] bg-white dark:bg-gray-800 sm:rounded-xl rounded-t-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            
            <div class="sm:hidden flex justify-center pt-3 pb-1">
                <div class="w-10 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></div>
            </div>
            <div class="p-4 max-h-[80vh] sm:max-h-[400px] overflow-y-auto">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center justify-between">
                <span><?php echo e(__('storefront.cart')); ?></span>
                <span class="text-sm font-normal text-gray-500"><?php echo e($count); ?> <?php echo e(__('storefront.items')); ?></span>
            </h3>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count === 0): ?>
                <div class="py-8 text-center">
                    <ion-icon name="bag-outline" class="text-5xl text-gray-300 dark:text-gray-600 mb-3"></ion-icon>
                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.your_cart_is_empty')); ?></p>
                    <button x-on:click="open = false"
                        class="mt-3 text-sm font-medium store-text-primary hover:underline"><?php echo e(__('storefront.back_to_store')); ?></button>
                </div>
            <?php else: ?>
                <div class="space-y-3 max-h-60 overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3 text-sm">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white truncate">
                                    <?php echo e($item['product_name']); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['variant_name']): ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($item['variant_name']); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="flex items-center gap-2 mt-1">
                                    <button
                                        wire:click="updateQty('<?php echo e($item['variant_id']); ?>', <?php echo e($item['quantity'] - 1); ?>)"
                                        class="w-8 h-8 sm:w-6 sm:h-6 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">-</button>
                                    <span class="text-gray-700 dark:text-gray-200"><?php echo e($item['quantity']); ?></span>
                                    <button
                                        wire:click="updateQty('<?php echo e($item['variant_id']); ?>', <?php echo e($item['quantity'] + 1); ?>)"
                                        class="w-8 h-8 sm:w-6 sm:h-6 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">+</button>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    <?php echo e(currency($item['price'] * $item['quantity'])); ?></p>
                                <button x-data
                                    @click.prevent="if (await EdzSwal.confirmAction('<?php echo e(__('storefront.remove')); ?>', '<?php echo e(__('messages.action_confirm_delete')); ?>')) $wire.removeItem('<?php echo e($item['variant_id']); ?>')"
                                    class="text-xs text-red-500 hover:text-red-700 mt-1"><?php echo e(__('storefront.remove')); ?></button>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 mt-3 pt-3">
                    <div class="flex justify-between font-semibold text-gray-900 dark:text-white">
                        <span><?php echo e(__('storefront.subtotal')); ?></span>
                        <span><?php echo e(currency($subtotal)); ?></span>
                    </div>
                    <a href="<?php echo e(route('storefront.checkout', ['store' => currentStore()->slug])); ?>"
                        class="mt-3 block w-full text-center store-btn-primary text-white font-semibold py-2.5 px-4 rounded-lg transition">
                        <?php echo e(__('storefront.checkout')); ?>

                    </a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/storefront/mini-cart.blade.php ENDPATH**/ ?>