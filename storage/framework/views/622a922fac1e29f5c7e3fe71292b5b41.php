<?php

use App\Domains\Cart\Services\CartService;
use App\Models\Products\ProductVariant;

?>

<div
    x-data="{
        open: false,
        init() {
            this.$el.__alpineOpen = () => { this.open = true; }
            setInterval(() => { if (this.$wire) this.$wire.refreshCart(); }, 30000);
            window.addEventListener('cart-updated', () => { if (this.$wire) this.$wire.refreshCart(); });
            window.addEventListener('keydown', (e) => { if (e.key === 'Escape' && this.open) this.open = false; });
        }
    }"
    class="relative"
>
    
    <button
        x-on:click="open = !open"
        x-bind:aria-expanded="open.toString()"
        aria-haspopup="true"
        aria-label="<?php echo e(__('storefront.cart')); ?>"
        class="relative p-2.5 sm:p-2
               text-ink-muted hover:text-ink
               rounded-lg hover:bg-neutral-secondary dark:hover:bg-dark-secondary
               min-h-[44px] min-w-[44px]
               flex items-center justify-center
               transition-colors duration-150"
    >
        <ion-icon name="cart-outline" class="text-[22px] leading-none"></ion-icon>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
            <span
                class="absolute -top-0.5 -end-0.5
                       store-bg-primary text-white
                       text-[10px] font-bold leading-none
                       min-w-[18px] h-[18px] px-1
                       flex items-center justify-center
                       rounded-full"
            ><?php echo e($count > 99 ? '99+' : $count); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </button>

    
    <div
        x-show="open"
        x-on:click="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 z-50 sm:hidden"
        style="display: none;"
    ></div>

    
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 sm:scale-95"
        x-on:click.outside="open = false"
        role="dialog"
        aria-label="<?php echo e(__('storefront.cart')); ?>"
        class="fixed bottom-0 inset-x-0 z-[60]
               sm:absolute sm:top-full sm:end-0 sm:mt-2 sm:w-80
               max-h-[85vh] sm:max-h-[480px]
               bg-neutral-surface dark:bg-dark-surface
               sm:rounded-xl rounded-t-2xl
               border border-neutral-border dark:border-dark-border
               shadow-elevated
               flex flex-col "
        style="display: none;"
    >
        
        <div class="sm:hidden flex justify-center pt-3 pb-1 shrink-0">
            <div class="w-10 h-1 rounded-full bg-neutral-border dark:bg-dark-border"></div>
        </div>

        
        <div class="px-4 pt-4 pb-3 shrink-0 border-b border-neutral-border/50 dark:border-dark-border/50">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-ink flex items-center gap-2">
                    <ion-icon name="cart-outline" class="text-lg store-text-primary"></ion-icon>
                    <span><?php echo e(__('storefront.cart')); ?></span>
                </h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
                    <span class="text-xs font-medium text-ink-muted bg-neutral-secondary dark:bg-dark-secondary px-2 py-0.5 rounded-full">
                        <?php echo e($count); ?> <?php echo e(__('storefront.items')); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count === 0): ?>
            
            <div class="flex-1 flex flex-col items-center justify-center px-6 py-10 text-center">
                <div class="w-16 h-16 rounded-full bg-neutral-secondary dark:bg-dark-secondary flex items-center justify-center mb-4">
                    <ion-icon name="bag-outline" class="text-3xl text-ink-soft"></ion-icon>
                </div>
                <p class="text-sm font-medium text-ink mb-1"><?php echo e(__('storefront.your_cart_is_empty')); ?></p>
                <p class="text-xs text-ink-muted mb-4"><?php echo e(__('storefront.review_cart')); ?></p>
                <button x-on:click="open = false"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium
                           store-btn-primary text-white
                           transition-colors duration-150">
                    <ion-icon name="bag-handle-outline" class="text-base"></ion-icon>
                    <?php echo e(__('storefront.back_to_store')); ?>

                </button>
            </div>
        <?php else: ?>
            
            <div class="  px-4 py-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        wire:key="cart-item-<?php echo e($item['variant_id']); ?>"
                        class="flex items-start gap-3 py-3 first:pt-0 last:pb-0 border-b border-neutral-border/40 dark:border-dark-border/40 last:border-b-0"
                    >
                        
                        <a
                            href="<?php echo e(route('storefront.product', ['store' => currentStore()->slug, 'product' => $item['slug']])); ?>"
                            x-on:click="open = false"
                            class="shrink-0 w-14 h-14 rounded-lg
                                   bg-neutral-secondary dark:bg-dark-secondary
                                   border border-neutral-border/50 dark:border-dark-border/50"
                        >
                            <img
                                src="<?php echo e($item['image']); ?>"
                                alt="<?php echo e($item['product_name']); ?>"
                                class="w-full h-full object-cover"
                                loading="lazy"
                                onerror="this.onerror=null;this.src='<?php echo e(asset('img/icons/noimg.png')); ?>'"
                            >
                        </a>

                        
                        <div class="flex-1 min-w-0">
                            <a
                                href="<?php echo e(route('storefront.product', ['store' => currentStore()->slug, 'product' => $item['slug']])); ?>"
                                x-on:click="open = false"
                                class="block text-sm font-medium text-ink leading-snug line-clamp-2
                                       hover:store-text-primary transition-colors duration-150"
                            ><?php echo e($item['product_name']); ?></a>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['variant_name']): ?>
                                <p class="text-xs text-ink-muted mt-0.5"><?php echo e($item['variant_name']); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="flex items-center justify-between mt-2">
                                
                                <div class="flex items-center rounded-lg border border-neutral-border dark:border-dark-border ">
                                    <button
                                        wire:click="updateQty('<?php echo e($item['variant_id']); ?>', <?php echo e($item['quantity'] - 1); ?>)"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-40"
                                        :disabled="<?php echo e($item['quantity'] <= 1 ? 'true' : 'false'); ?>"
                                        aria-label="<?php echo e(__('buttons.decrease')); ?>"
                                        class="w-7 h-7 flex items-center justify-center
                                               bg-neutral-secondary dark:bg-dark-secondary
                                               text-ink-muted hover:text-ink
                                               transition-colors duration-150
                                               disabled:opacity-30 disabled:cursor-not-allowed
                                               text-xs font-medium select-none"
                                    >&minus;</button>
                                    <span class="w-7 text-center text-xs font-medium text-ink tabular-nums select-none">
                                        <?php echo e($item['quantity']); ?>

                                    </span>
                                    <button
                                        wire:click="updateQty('<?php echo e($item['variant_id']); ?>', <?php echo e($item['quantity'] + 1); ?>)"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-40"
                                        :disabled="<?php echo e($item['max_stock'] && $item['quantity'] >= $item['max_stock'] ? 'true' : 'false'); ?>"
                                        aria-label="<?php echo e(__('buttons.increase')); ?>"
                                        class="w-7 h-7 flex items-center justify-center
                                               bg-neutral-secondary dark:bg-dark-secondary
                                               text-ink-muted hover:text-ink
                                               transition-colors duration-150
                                               disabled:opacity-30 disabled:cursor-not-allowed
                                               text-xs font-medium select-none"
                                    >&plus;</button>
                                </div>

                                
                                <span class="text-sm font-semibold text-ink tabular-nums">
                                    <?php echo e(currency($item['price'] * $item['quantity'])); ?>

                                </span>
                            </div>
                        </div>

                        
                        <button
                            x-data
                            x-on:click.prevent="if (await EdzSwal.confirmAction('<?php echo e(__('storefront.remove')); ?>', '<?php echo e(__('messages.action_confirm_delete')); ?>')) $wire.removeItem('<?php echo e($item['variant_id']); ?>')"
                            aria-label="<?php echo e(__('storefront.remove')); ?>"
                            class="shrink-0 mt-0.5 p-1 rounded
                                   text-ink-soft hover:text-error-500 hover:bg-error-50 dark:hover:bg-error-900/20
                                   transition-colors duration-150"
                        >
                            <ion-icon name="close-outline" class="text-base"></ion-icon>
                        </button>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="shrink-0 px-4 py-3 border-t border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm text-ink-muted"><?php echo e(__('storefront.subtotal')); ?></span>
                    <span class="text-base font-semibold text-ink tabular-nums"><?php echo e(currency($subtotal)); ?></span>
                </div>
                <a
                    href="<?php echo e(route('storefront.checkout', ['store' => currentStore()->slug])); ?>"
                    x-on:click="open = false"
                    class="block w-full text-center py-2.5 px-4 rounded-lg
                          store-btn-primary text-white
                          font-semibold text-sm
                          min-h-[44px]
                          flex items-center justify-center
                          transition-colors duration-150"
                >
                    <?php echo e(__('storefront.checkout')); ?>

                </a>
                <p class="text-center text-xs text-ink-soft mt-2">
                    <ion-icon name="lock-closed-outline" class="align-text-bottom text-[11px]"></ion-icon>
                    <?php echo e(__('storefront.secure_checkout')); ?>

                </p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/storefront/mini-cart.blade.php ENDPATH**/ ?>