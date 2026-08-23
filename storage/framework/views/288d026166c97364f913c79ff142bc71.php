<?php

use App\Models\Products\Product;

?>

<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->product): ?>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('hero', $this->sections)): ?>
    <section class="relative overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->product->brand): ?>
                        <span class="inline-block text-sm font-semibold store-text-primary mb-3 uppercase tracking-wider"><?php echo e($this->product->brand->name); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <h1 class="text-2xl sm:text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight mb-6">
                        <?php echo e($this->product->name); ?>

                    </h1>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->product->short_description ?? $this->product->description): ?>
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed">
                            <?php echo e($this->product->short_description ?? Str::limit($this->product->description, 200)); ?>

                        </p>
<?php endif; ?>

                    <div x-data="{
                            selectedVariantId: <?php echo \Illuminate\Support\Js::from($this->product->variants->first()?->id)->toHtml() ?>,
                            variants: @js($this->product->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => (float) $v->price, 'compare_price' => (float) ($v->compare_price ?? 0), 'stock' => $v->stock, 'low_stock_threshold' => $v->low_stock_threshold]),
                            getVariant(id) { return this.variants.find(v => v.id === id); },
                            getVariantPrice(v) { return v ? (float) v.price : 0; },
                            getVariantCompare(v) { return v ? (float) (v.compare_price ?? 0) : 0; },
                            getVariantStock(v) { return v ? v.stock : 0; },
                            getVariantLowStockThreshold(v) { return v ? v.low_stock_threshold : 0; },
                            getStockStatus(v) {
                                if (!v) return 'in';
                                if (v.stock <= 0) return 'out';
                                if (v.stock > 0 && v.stock <= v.low_stock_threshold) return 'low';
                                return 'in';
                            },
                            getStockText(v) {
                                const status = this.getStockStatus(v);
                                if (status === 'low') return '<?php echo e(__(\'storefront.low_stock\', [\'count\' => 1])); ?>'.replace('1', v.stock);
                                if (status === 'out') return '<?php echo e(__(\'storefront.out_of_stock\')); ?>';
                                return '<?php echo e(__(\'storefront.in_stock\')); ?>';
                            },
                            getStockClass(status) {
                                return status === 'out' ? 'text-red-500 dark:text-red-400' :
                                       status === 'low' ? 'text-amber-600 dark:text-amber-400' :
                                       'text-emerald-600 dark:text-emerald-400';
                            }
                        }"
                        x-init="selectedVariantId = <?php echo \Illuminate\Support\Js::from($this->product->variants->first()?->id)->toHtml() ?>">

                    
                    <template x-if="getVariant(getVariant(selectedVariantId))">
                        <div class="flex items-center flex-wrap gap-x-3 gap-y-1 mb-2">
                            <span class="text-2xl sm:text-3xl lg:text-4xl font-bold store-text-primary" x-text="currency(getVariantPrice(getVariant(selectedVariantId)))"></span>
                            <template x-if="getVariantCompare(getVariant(selectedVariantId)) > 0 && getVariantCompare(getVariant(selectedVariantId)) > getVariantPrice(getVariant(selectedVariantId))">
                                <span class="text-lg text-gray-400 dark:text-gray-500 line-through" x-text="currency(getVariantCompare(getVariant(selectedVariantId)))"></span>
                                <span class="text-sm font-bold text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full" x-text="'-' + Math.round((1 - getVariantPrice(getVariant(selectedVariantId)) / getVariantCompare(getVariant(selectedVariantId))) * 100) + '%'"></span>
                            </template>
                        </div>

                        
                        <p class="mb-6 text-sm font-medium" :class="getStockClass(getStockStatus(getVariant(selectedVariantId)))" x-text="getStockText(getVariant(selectedVariantId))"></p>
                    </template>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->product->variants->count() > 1): ?>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?php echo e(__('storefront.options')); ?></label>
                            <div class="flex flex-wrap gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button
                                        type="button"
                                        @click="selectedVariantId = '<?php echo e($variant->id); ?>'; $wire.selectVariant('<?php echo e($variant->id); ?>')"
                                        :class="selectedVariantId === '<?php echo e($variant->id); ?>'
                                            ? 'store-border-primary store-bg-primary-soft store-text-primary'
                                            : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300'"
                                        class="border-2 rounded-lg px-4 py-2 text-sm font-medium transition cursor-pointer"
                                    >
                                        <?php echo e($variant->name); ?>

                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <button
                                type="button"
                                wire:click="addToCart"
                                :disabled="getStockStatus(getVariant(selectedVariantId)) === 'out'"
                                class="mt-4 w-full sm:w-auto store-btn-primary text-white font-bold py-3 px-8 rounded-lg transition text-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled"
                            >
<?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'cart','class' => 'mr-2 w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','class' => 'mr-2 w-5 h-5']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                                <?php echo e(__('storefront.add_to_cart')); ?>

                            </button>
                        </div>
                    <?php else: ?>
                        <button
                            type="button"
                            wire:click="addToCart"
                            :disabled="getStockStatus(getVariant(selectedVariantId)) === 'out'"
                            class="store-btn-primary text-white font-bold py-3 px-8 rounded-lg transition text-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                        >
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'cart','class' => 'mr-2 w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','class' => 'mr-2 w-5 h-5']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                            <?php echo e(__('storefront.add_to_cart')); ?>

                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="relative">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->product->images->count()): ?>
                        <img
                            src="<?php echo e(asset('storage/' . $this->product->images->first()->path)); ?>"
                            alt="<?php echo e($this->product->name); ?>"
                            class="rounded-2xl shadow-2xl w-full object-cover aspect-square"
                            onerror="this.onerror=null;this.src='<?php echo e(asset('img/icons/noimg.png')); ?>'"
                        >
                    <?php else: ?>
                        <div class="rounded-2xl shadow-2xl bg-gray-200 dark:bg-gray-700 w-full aspect-square flex items-center justify-center overflow-hidden">
                            <img src="<?php echo e(asset('img/icons/noimg.png')); ?>" alt="<?php echo e($this->product->name); ?>" class="w-full h-full object-contain p-8 opacity-60">
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->product->is_featured): ?>
                        <span class="absolute top-4 <?php echo e(isRTL() ? 'end-4' : 'start-4'); ?> z-10 flex items-center gap-1.5 store-bg-primary text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'star','class' => 'text-sm w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','class' => 'text-sm w-4 h-4']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                            <?php echo e(__('storefront.featured')); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php
                        $_badgeVariant = $this->selectedVariant ?? $this->product->variants->first();
                        $_heroMaxCompare = (float) ($_badgeVariant?->compare_price ?? 0);
                        $_heroMinPrice = (float) ($_badgeVariant?->price ?? $this->product->price);
                        $_heroDiscount = ($_heroMaxCompare > 0 && $_heroMinPrice > 0 && $_heroMaxCompare > $_heroMinPrice)
                            ? (int) round((1 - $_heroMinPrice / $_heroMaxCompare) * 100)
                            : 0;
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($_heroDiscount > 0): ?>
                        <span class="absolute top-4 <?php echo e(isRTL() ? 'start-4' : 'end-4'); ?> z-10 bg-red-500 text-white text-xs font-bold px-2.5 py-1.5 rounded-full shadow-lg">
                            -<?php echo e($_heroDiscount); ?>%
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->product->description && in_array('description', $this->sections)): ?>
    <section class="py-16 bg-white dark:bg-gray-800">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center"><?php echo e($this->section_content['description']['title'] ?? __('storefront.product_details')); ?></h2>
            <div class="prose prose-lg dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                <?php echo nl2br(e($this->product->description)); ?>

            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('social_proof', $this->sections)): ?>
    <?php $sp = $this->section_content['social_proof'] ?? []; ?>
    <section class="py-16 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8"><?php echo e($sp['title'] ?? __('storefront.why_customers_love_us')); ?></h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ($sp['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 store-bg-primary-soft rounded-full flex items-center justify-center mb-4">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => $item['icon'] ?? 'check','class' => 'text-2xl w-8 h-8 store-text-primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['icon'] ?? 'check'),'class' => 'text-2xl w-8 h-8 store-text-primary']); ?> <?php echo $__env->renderComponent(); ?>
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
                        <h3 class="font-semibold text-gray-900 dark:text-white"><?php echo e($item['title'] ?? ''); ?></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?php echo e($item['description'] ?? ''); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('faq', $this->sections)): ?>
    <?php $faq = $this->section_content['faq'] ?? []; ?>
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
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => openFaq === <?php echo e($loop->index); ?> ? 'chevron-up' : 'chevron-down','class' => 'text-gray-500 dark:text-gray-400 w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(openFaq === {{ $loop->index }} ? 'chevron-up' : 'chevron-down'),'class' => 'text-gray-500 dark:text-gray-400 w-5 h-5']); ?> <?php echo $__env->renderComponent(); ?>
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
                        <div x-show="openFaq === <?php echo e($loop->index); ?>" x-transition class="px-6 pb-4">
                            <p class="text-gray-600 dark:text-gray-300"><?php echo e($faqItem['answer'] ?? ''); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('cta', $this->sections)): ?>
    <?php $cta = $this->section_content['cta'] ?? []; ?>
    <section class="py-16 store-gradient">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4"><?php echo e($cta['title'] ?? __('storefront.ready_to_order')); ?></h2>
            <p class="text-white/80 mb-8 text-lg"><?php echo e($cta['description'] ?? __('storefront.get_yours_now')); ?></p>
            <a
                href="#"
                x-on:click.prevent="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="inline-flex items-center gap-2 bg-white dark:bg-gray-100 font-bold py-3 px-8 rounded-lg hover:bg-white/90 dark:hover:bg-white transition text-lg store-text-primary"
            >
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'cart','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','class' => 'w-5 h-5']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
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