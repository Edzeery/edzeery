<?php

use App\Domains\Cart\Services\CartService;
use App\Models\Products\ProductVariant;

?>

<div x-data="{
    open: false,
    init() {
        window.addEventListener('cart-updated', () => { this.$wire.refreshCart(); });
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.open) { this.open = false; }
        });
        this.$watch('open', (val) => {
            clearInterval(this._pollInterval);
            if (val) {
                this._pollInterval = setInterval(() => {
                    if (document.visibilityState === 'visible') { this.$wire.refreshCart(); }
                }, 30000);
            }
        });
    },
    destroy() { clearInterval(this._pollInterval); },
    toggle() { this.open = !this.open; },
}" class="relative">

    
    <button x-on:click="toggle()"
        class="relative p-2.5 rounded-lg text-gray-600 dark:text-gray-300
               hover:bg-gray-100 dark:hover:bg-gray-700
               transition-colors duration-150 min-h-[44px] min-w-[44px]
               flex items-center justify-center"
        aria-label="<?php echo e(__('storefront.cart')); ?>">
        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shopping-cart','class' => 'text-[22px] leading-none']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-cart','class' => 'text-[22px] leading-none']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
            <span class="absolute -top-0.5 -end-0.5 store-bg-primary text-white
                         text-[10px] font-bold leading-none
                         min-w-[18px] h-[18px] px-1
                         flex items-center justify-center rounded-full
                         ring-2 ring-white dark:ring-gray-800">
                <?php echo e($count > 99 ? '99+' : $count); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </button>

    
    <div x-show="open"
        x-on:click="open = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60]"
        style="display: none;">
    </div>

    
    <div x-show="open"
        <?php if(app()->getLocale() === 'ar'): ?>
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-[70] w-full sm:w-[420px] bg-white dark:bg-gray-900
                   shadow-2xl shadow-black/20 flex flex-col"
        <?php else: ?>
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-[70] w-full sm:w-[420px] bg-white dark:bg-gray-900
                   shadow-2xl shadow-black/20 flex flex-col"
        <?php endif; ?>
        style="display: none;">

        
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full store-bg-primary flex items-center justify-center">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shopping-cart','class' => 'text-white text-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-cart','class' => 'text-white text-lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white"><?php echo e(__('storefront.cart')); ?></h2>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            <?php echo e($count); ?> <?php echo e(__('storefront.items')); ?>

                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
                    
                    <button x-data
                            data-confirm-title="<?php echo e(__('storefront.clear_cart')); ?>"
                            data-confirm-text="<?php echo e(__('storefront.clear_cart_confirm')); ?>"
                            x-on:click.prevent="(async () => { if (await EdzSwal.confirmAction($el.dataset.confirmTitle, $el.dataset.confirmText)) await $wire.clearCart() })()"
                            class="w-9 h-9 rounded-full flex items-center justify-center
                                   text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400
                                   hover:bg-red-50 dark:hover:bg-red-900/20
                                   transition-colors duration-150"
                            aria-label="<?php echo e(__('storefront.clear_cart')); ?>">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trash','class' => 'text-lg w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'text-lg w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button x-on:click="open = false"
                    class="w-9 h-9 rounded-full flex items-center justify-center
                           text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-200
                           hover:bg-gray-100 dark:hover:bg-gray-700
                           transition-colors duration-150"
                    aria-label="Close">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'text-xl w-5 h-5 ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'text-xl w-5 h-5 ']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                </button>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count === 0): ?>
            
            <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 text-center">
                <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-5">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shopping-bag','class' => 'text-4xl text-gray-300 dark:text-gray-600 w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-bag','class' => 'text-4xl text-gray-300 dark:text-gray-600 w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                </div>
                <p class="text-base font-medium text-gray-900 dark:text-white mb-1.5">
                    <?php echo e(__('storefront.your_cart_is_empty')); ?>

                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-[240px]">
                    <?php echo e(__('storefront.review_cart')); ?>

                </p>
                <button x-on:click="open = false"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                           store-btn-primary text-white text-sm font-semibold
                           transition-all duration-150 hover:shadow-lg">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shopping-bag','class' => 'text-base w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-bag','class' => 'text-base w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                    <?php echo e(__('storefront.back_to_store')); ?>

                </button>
            </div>
        <?php else: ?>
            
            <div class="flex-1 overflow-y-auto overscroll-contain">
                <div class="px-5 py-3 divide-y divide-gray-100 dark:divide-gray-800">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div wire:key="cart-item-<?php echo e($item['variant_id']); ?>"
                             class="flex gap-3.5 py-4 first:pt-0 last:pb-0">

                            
                                        <a href="<?php echo e(route('storefront.product', ['store' => currentStore()?->slug ?? '', 'product' => $item['slug']])); ?>"
                               x-on:click="open = false"
                               class="shrink-0 w-[72px] h-[72px] rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:opacity-80 transition-opacity">
                                <img src="<?php echo e($item['image']); ?>"
                                     alt="<?php echo e($item['product_name']); ?>"
                                     class="w-full h-full object-cover"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='<?php echo e(asset('img/icons/noimg.png')); ?>'">
                            </a>

                            
                            <div class="flex-1 min-w-0 flex flex-col">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                            <a href="<?php echo e(route('storefront.product', ['store' => currentStore()?->slug ?? '', 'product' => $item['slug']])); ?>"
                                           x-on:click="open = false"
                                           class="block text-sm font-medium text-gray-900 dark:text-white leading-snug line-clamp-2 hover:text-[var(--store-primary)] transition-colors">
                                            <?php echo e($item['product_name']); ?>

                                        </a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['variant_name']): ?>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                <?php echo e($item['variant_name']); ?>

                                            </p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <button x-data
                                            data-confirm-title="<?php echo e(__('storefront.remove')); ?>"
                                            data-confirm-text="<?php echo e(__('messages.action_confirm_delete')); ?>"
                                            data-variant-id="<?php echo e($item['variant_id']); ?>"
                                            x-on:click.prevent="(async () => { if (await EdzSwal.confirmAction($el.dataset.confirmTitle, $el.dataset.confirmText)) await $wire.removeItem($el.dataset.variantId) })()"
                                            class="shrink-0 p-1 rounded-lg text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                            aria-label="<?php echo e(__('storefront.remove')); ?>">
                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trash','class' => 'text-base w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'text-base w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                                    </button>
                                </div>

                                
                                <div class="flex items-center justify-between mt-auto pt-2">
                                    <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                                        <button wire:click="updateQty('<?php echo e($item['variant_id']); ?>', <?php echo e($item['quantity'] - 1); ?>)"
                                                wire:loading.attr="disabled"
                                                wire:loading.class="opacity-40"
                                                :disabled="<?php echo e($item['quantity'] <= 1 ? 'true' : 'false'); ?>"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-50 dark:bg-gray-800
                                                       text-gray-600 dark:text-gray-400
                                                       hover:bg-gray-100 dark:hover:bg-gray-700
                                                       transition-colors disabled:opacity-30 disabled:cursor-not-allowed
                                                       text-sm font-medium select-none">
                                            &minus;
                                        </button>
                                        <span class="w-8 text-center text-sm font-semibold text-gray-900 dark:text-white tabular-nums select-none">
                                            <?php echo e($item['quantity']); ?>

                                        </span>
                                        <button wire:click="updateQty('<?php echo e($item['variant_id']); ?>', <?php echo e($item['quantity'] + 1); ?>)"
                                                wire:loading.attr="disabled"
                                                wire:loading.class="opacity-40"
                                                :disabled="<?php echo e($item['max_stock'] && $item['quantity'] >= $item['max_stock'] ? 'true' : 'false'); ?>"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-50 dark:bg-gray-800
                                                       text-gray-600 dark:text-gray-400
                                                       hover:bg-gray-100 dark:hover:bg-gray-700
                                                       transition-colors disabled:opacity-30 disabled:cursor-not-allowed
                                                       text-sm font-medium select-none">
                                            &plus;
                                        </button>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white tabular-nums">
                                        <?php echo e(currency($item['price'] * $item['quantity'])); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="shrink-0 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-5 py-4">
                
                <div class="flex justify-between items-center mb-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.subtotal')); ?></span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white tabular-nums">
                        <?php echo e(currency($subtotal)); ?>

                    </span>
                </div>

                
                <a href="<?php echo e(route('storefront.checkout', ['store' => currentStore()?->slug ?? ''])); ?>"
                   x-on:click="open = false"
                   class="block w-full text-center py-3 px-4 rounded-xl
                          store-btn-primary text-white font-bold text-sm
                          min-h-[48px] flex items-center justify-center gap-2
                          transition-all duration-150 hover:shadow-lg hover:brightness-110">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'lock-closed','class' => 'text-base w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lock-closed','class' => 'text-base w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                    <?php echo e(__('storefront.checkout')); ?>

                </a>

                <p class="text-center text-[11px] text-gray-400 dark:text-gray-500 mt-2.5">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shield-check','class' => 'align-text-bottom text-xs w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield-check','class' => 'align-text-bottom text-xs w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                    <?php echo e(__('storefront.secure_checkout')); ?>

                </p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/storefront/mini-cart.blade.php ENDPATH**/ ?>