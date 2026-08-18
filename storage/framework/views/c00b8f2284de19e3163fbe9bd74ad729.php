<?php

use App\Models\Brand;
use App\Models\Products\Product;

?>

<div>
    <?php
        $store = currentStore();
        $brands = Brand::where('store_id', $store->id)
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $query = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->with(['images', 'brand', 'categories']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($brand_id) {
            $query->where('brand_id', $brand_id);
        }

        match ($sortBy) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default      => $query->orderByDesc('created_at'),
        };

        $products = $query->paginate(12);
    ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('hero', $sections)): ?>
    <section class="relative overflow-hidden text-white">
        <div class="absolute inset-0 store-gradient opacity-90"></div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->cover): ?>
            <img src="<?php echo e(asset('storage/' . $store->cover)); ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-30">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->logo): ?>
                <img src="<?php echo e(asset('storage/' . $store->logo)); ?>" alt="<?php echo e($store->name); ?>" class="w-20 h-20 rounded-full mx-auto mb-6 object-cover border-4 border-white/20">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <h1 class="text-4xl lg:text-5xl font-bold mb-4"><?php echo e($store->name); ?></h1>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store->description): ?>
                <p class="text-lg text-white/80 max-w-2xl mx-auto"><?php echo e($store->description); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="max-w-xl mx-auto mt-8">
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="<?php echo e(__('Search products...')); ?>"
                        class="w-full px-5 py-3 pl-12 rounded-full bg-white/10 backdrop-blur-sm text-white placeholder-white/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/40"
                    >
                    <ion-icon name="search-outline" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/50 text-xl"></ion-icon>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('brands', $sections) && $brands->count()): ?>
    <section class="py-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4"><?php echo e(__('Collections')); ?></h2>
            <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                <button
                    wire:click="$set('brand_id', '')"
                    class="shrink-0 px-5 py-2.5 rounded-lg text-sm font-medium transition border
                        <?php echo e(empty($brand_id) ? 'store-bg-primary text-white store-border-primary' : 'bg-transparent text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:store-border-primary'); ?>"
                >
                    <?php echo e(__('All Collections')); ?>

                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button
                        wire:click="$set('brand_id', '<?php echo e($brand->id); ?>')"
                        class="shrink-0 px-5 py-2.5 rounded-lg text-sm font-medium transition border
                            <?php echo e($brand_id === $brand->id ? 'store-bg-primary text-white store-border-primary' : 'bg-transparent text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:store-border-primary'); ?>"
                    >
                        <?php echo e($brand->name); ?>

                        <span class="ml-1 text-xs opacity-70">(<?php echo e($brand->products_count); ?>)</span>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <section class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">

                <?php echo e($products->total() ?? 0); ?> <?php echo e(__('templates.products')); ?>

            </p>
            <select wire:model.live="sortBy" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                <option value="newest"><?php echo e(__('Newest')); ?></option>
                <option value="price_asc"><?php echo e(__('Price: Low to High')); ?></option>
                <option value="price_desc"><?php echo e(__('Price: High to Low')); ?></option>
            </select>
        </div>
    </section>

    
    <section class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->count()): ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition group">
                            <a href="<?php echo e(route('storefront.product', ['store' => $store->slug, 'product' => $product])); ?>" class="block">
                                <div class="aspect-square bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->images->count()): ?>
                                        <img
                                            src="<?php echo e(asset('storage/' . $product->images->first()->path)); ?>"
                                            alt="<?php echo e($product->name); ?>"
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                        >
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center">
                                            <ion-icon name="image-outline" class="text-4xl text-gray-400"></ion-icon>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </a>

                            <div class="p-4">
                                <a href="<?php echo e(route('storefront.product', ['store' => $store->slug, 'product' => $product])); ?>">
                                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1 line-clamp-2"><?php echo e($product->name); ?></h3>
                                </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->brand): ?>
                                    <p class="text-xs store-text-primary font-medium mb-2"><?php echo e($product->brand->name); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="flex items-center justify-between mt-3">
                                    <span class="text-lg font-bold store-text-primary"><?php echo e(currency($product->min_price ?? $product->price)); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->variants->count() === 1): ?>
                                        <button
                                            wire:click="$wire.addToCart('<?php echo e($product->variants->first()->id); ?>')"
                                            class="store-btn-primary text-white p-2 rounded-lg transition text-sm"
                                            title="<?php echo e(__('Add to cart')); ?>"
                                        >
                                            <ion-icon name="cart-outline"></ion-icon>
                                        </button>
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
                    <p class="text-gray-500 dark:text-gray-400"><?php echo e(__('No products found')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/storefront/templates/brand.blade.php ENDPATH**/ ?>