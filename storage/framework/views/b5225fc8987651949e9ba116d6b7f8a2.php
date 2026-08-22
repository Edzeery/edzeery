<?php

use App\Models\Category;
use App\Models\Products\Product;

?>

<div>
    <?php
        $store = currentStore();
        if (!$store) { return; }

        $categories = Category::where('store_id', $store->id)
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $query = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->with(['images', 'brand', 'categories', 'variants']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->category_id) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $this->category_id));
        }

        match ($this->sortBy) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'oldest' => $query->orderBy('created_at'),
            default => $query->orderByDesc('created_at'),
        };

        $products = $query->paginate(12);
    ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('hero', $this->sections)): ?>
        <?php $hero = $this->section_content['hero'] ?? []; ?>
        <section class="store-gradient text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                                    <h1 class="text-3xl sm:text-4xl font-bold mb-4"><?php echo e($hero['title'] ?? '' ?: $store->name); ?></h1>
                <p class="text-lg text-white/80 mb-8">
                    <?php echo e($hero['description'] ?? '' ?: $store->description ?? __('storefront.browse_our_full_catalog')); ?></p>

                
                <div class="max-w-xl mx-auto">
                    <div class="relative w-full">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="<?php echo e(__('storefront.search_products')); ?>"
                            class="w-full px-5 py-3
                                 <?php echo e(isRTL() ? 'pr-12 pl-5' : 'pl-12 pr-5'); ?>

                                 rounded-full bg-white/20 backdrop-blur-sm
                                 text-white placeholder-white/60
                                 border border-white/30
                                 focus:outline-none focus:ring-2 focus:ring-white/50">

                        <ion-icon name="search-outline"
                                             class="absolute <?php echo e(isRTL() ? 'right-5' : 'left-5'); ?> top-1/2 -translate-y-1/2
                                text-white/70 text-xl pointer-events-none"></ion-icon>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('categories', $this->sections)): ?>

        
        <nav class="py-3 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <ol class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="<?php echo e(route('storefront.home', ['store' => $store->slug])); ?>" class="hover:text-gray-700 dark:hover:text-gray-200 transition"><?php echo e($store->name); ?></a></li>
                <li class="text-gray-300 dark:text-gray-600">/</li>
                <li class="text-gray-900 dark:text-white font-medium"><?php echo e(__('storefront.all_products')); ?></li>
            </ol>
        </nav>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categories->count()): ?>
            <section class="py-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                        <button wire:click="$set('category_id', '')"
                            class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition
                        <?php echo e(empty($this->category_id) ? 'store-bg-primary text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'); ?>">
                            <?php echo e(__('storefront.all')); ?>

                        </button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button wire:click="$set('category_id', '<?php echo e($cat->id); ?>')"
                                class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition
                            <?php echo e($this->category_id === $cat->id ? 'store-bg-primary text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'); ?>">
                                <?php echo e($cat->name); ?> (<?php echo e($cat->products_count); ?>)
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <section class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <?php echo e($products->total() ?? 0); ?> <?php echo e(__('storefront.products')); ?>

            </p>
            <select wire:model.live="sortBy"
                class="text-sm border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--store-primary)_35%,transparent)] focus:border-[var(--store-primary)]">
                <option value="newest"><?php echo e(__('storefront.newest')); ?></option>
                <option value="price_asc"><?php echo e(__('storefront.price_low_high')); ?></option>
                <option value="price_desc"><?php echo e(__('storefront.price_high_low')); ?></option>
            </select>
        </div>
    </section>

    
    <section class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->count()): ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition group">
                            <a href="<?php echo e(route('storefront.product', ['store' => $store->slug, 'product' => $product->slug])); ?>"
                                class="block">
                                <div class="relative aspect-square bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->images->count()): ?>
                                        <img src="<?php echo e(asset('storage/' . $product->images->first()->path)); ?>"
                                            alt="<?php echo e($product->name); ?>"
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                            onerror="this.onerror=null;this.src='<?php echo e(asset('img/icons/noimg.png')); ?>'">
                                    <?php else: ?>
                                        <img src="<?php echo e(asset('img/icons/noimg.png')); ?>" alt="<?php echo e($product->name); ?>"
                                            class="w-full h-full object-contain p-4 opacity-60">
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->is_featured): ?>
                                        <span class="absolute top-2 <?php echo e(isRTL() ? 'end-2' : 'start-2'); ?> z-10 flex items-center gap-1 store-bg-primary text-white text-[10px] font-bold px-2 py-1 rounded-full shadow">
                                            <ion-icon name="star" class="text-xs"></ion-icon>
                                            <?php echo e(__('storefront.featured')); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </a>

                            <div class="p-4">
                                <a
                                    href="<?php echo e(route('storefront.product', ['store' => $store->slug, 'product' => $product->slug])); ?>">
                                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1 line-clamp-2">
                                        <?php echo e($product->name); ?></h3>
                                </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->brand): ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                        <?php echo e($product->brand->name); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="flex items-center justify-between mt-3">
                                    <?php
                                        $_cardMinPrice = (float) ($product->variants->min('price') ?? $product->price);
                                        $_cardMaxCompare = (float) $product->variants->max('compare_price');
                                        $_cardDiscount = ($_cardMaxCompare > 0 && $_cardMinPrice > 0 && $_cardMaxCompare > $_cardMinPrice)
                                            ? (int) round((1 - $_cardMinPrice / $_cardMaxCompare) * 100)
                                            : 0;
                                    ?>
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg font-bold store-text-primary"><?php echo e(currency($_cardMinPrice)); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($_cardDiscount > 0): ?>
                                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 line-through"><?php echo e(currency($_cardMaxCompare)); ?></span>
                                            <span class="text-xs font-bold text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-1.5 py-0.5 rounded-full">
                                                -<?php echo e($_cardDiscount); ?>%
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->variants->count() === 1): ?>
                                        <button wire:click="addToCart('<?php echo e($product->variants->first()->id); ?>')"
                                            wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                            class="store-btn-primary text-white min-h-[44px] min-w-[44px] flex items-center justify-center rounded-lg transition text-sm"
                                            title="<?php echo e(__('storefront.add_to_cart')); ?>">
                                            <ion-icon name="cart-outline"></ion-icon>
                                        </button>
                                    <?php elseif($product->variants->count() > 1): ?>
                                        <a href="<?php echo e(route('storefront.product', ['store' => $store->slug, 'product' => $product->slug])); ?>"
                                            class="store-btn-primary text-white min-h-[44px] min-w-[44px] flex items-center justify-center px-3 rounded-lg transition text-xs font-medium gap-1">
                                            <ion-icon name="options-outline"></ion-icon>
                                            <?php echo e(__('storefront.view_options')); ?>

                                        </a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="mt-8">
                    <?php echo e($products->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-20">
                    <ion-icon name="bag-outline" class="text-6xl text-gray-300 dark:text-gray-600 mb-4"></ion-icon>
                    <p class="text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.no_products_found')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('social_proof', $this->sections ?? [])): ?>
        <?php $sp = ($this->section_content ?? [])['social_proof'] ?? []; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sp && !empty($sp['items'])): ?>
        <section class="py-16 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8"><?php echo e($sp['title'] ?? __('storefront.why_customers_love_us')); ?></h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sp['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\storefront\templates\catalog.blade.php ENDPATH**/ ?>