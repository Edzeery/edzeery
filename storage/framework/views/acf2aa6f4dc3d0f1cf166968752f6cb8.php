<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Storage;

?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title"><?php echo e($product->name); ?></h1>
            <p class="edz-page-head__subtitle">
                <?php echo e($product->sku ? 'SKU: '.$product->sku : 'No SKU'); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->barcode): ?>
                    · Barcode: <?php echo e($product->barcode); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('merchant.products.index', currentStore())); ?>" wire:navigate
               class="edz-btn edz-btn--ghost"><?php echo e(__('products.back')); ?></a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canUpdate()): ?>
                <a href="<?php echo e(route('merchant.products.edit', [currentStore(), $product])); ?>" wire:navigate
                   class="edz-btn edz-btn--primary"><?php echo e(__('products.edit')); ?></a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDelete()): ?>
                <button type="button" class="edz-btn edz-btn--danger"
                        x-data
                        @click.prevent="if (await EdzSwal.confirmDelete('<?php echo e(addslashes($product->name)); ?>')) $wire.delete('<?php echo e($product->id); ?>')"
                        ><?php echo e(__('products.delete')); ?></button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title"><?php echo e(__('products.media')); ?></h2>
                        <p class="text-sm text-ink-400"><?php echo e(__('products.image_count', ['count' => $this->images->count()])); ?></p>
                    </div>
                </div>
                <div class="edz-card__body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->images->isNotEmpty()): ?>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="overflow-hidden rounded-lg border border-surface-border">
                                    <img src="<?php echo e($this->imageUrl($image->path)); ?>" alt=""
                                         class="aspect-square w-full object-cover">
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-ink-muted"><?php echo e(__('products.no_images')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title"><?php echo e(__('products.description')); ?></h2>
                    </div>
                </div>
                <div class="edz-card__body space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->short_description): ?>
                        <p class="text-sm font-medium text-ink"><?php echo e($product->short_description); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->description): ?>
                        <div class="prose-sm prose max-w-none text-ink-soft"><?php echo $product->description; ?></div>
                    <?php else: ?>
                        <p class="text-sm text-ink-muted"><?php echo e(__('products.no_description')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->hasVariants()): ?>
                <div class="edz-card">
                    <div class="edz-card__header">
                        <div>
                            <h2 class="edz-card__title"><?php echo e(__('products.variants')); ?></h2>
                            <p class="text-sm text-ink-400"><?php echo e(__('products.variant_count', ['count' => $this->variants->count()])); ?></p>
                        </div>
                    </div>
                    <div class="edz-card__body p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('products.variant')); ?></th>
                                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('products.sku')); ?></th>
                                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('products.price')); ?></th>
                                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('products.cost')); ?></th>
                                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('products.stock')); ?></th>
                                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('products.status')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="border-b border-surface-border last:border-0">
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-ink"><?php echo e($variant->name); ?></p>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variant->barcode): ?>
                                                    <p class="font-mono text-xs text-ink-muted"><?php echo e($variant->barcode); ?></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs text-ink-soft"><?php echo e($variant->sku); ?></td>
                                            <td class="px-4 py-3 text-ink-soft"><?php echo e(number_format($variant->price, 2)); ?></td>
                                            <td class="px-4 py-3 text-ink-soft"><?php echo e(number_format($variant->cost_price, 2)); ?></td>
                                            <td class="px-4 py-3">
                                                <span class="text-ink-soft"><?php echo e($variant->stock); ?></span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'product','status' => $variant->is_active ? 'active' : 'inactive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'product','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($variant->is_active ? 'active' : 'inactive')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="space-y-6">
            <div class="edz-card">
                <div class="edz-card__header">
                    <h2 class="edz-card__title"><?php echo e(__('products.details')); ?></h2>
                </div>
                <div class="edz-card__body grid grid-cols-1 gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'product','status' => $product->is_active ? 'active' : 'inactive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'product','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->is_active ? 'active' : 'inactive')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->is_featured): ?>
                            <?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'general','status' => 'featured']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'general','status' => 'featured']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <dl class="grid grid-cols-1 gap-3">
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted"><?php echo e(__('products.type')); ?></dt>
                            <dd class="font-medium text-ink"><?php echo e($product->hasVariants() ? __('products.variable') : __('products.simple')); ?></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted"><?php echo e(__('products.brand')); ?></dt>
                            <dd class="font-medium text-ink"><?php echo e($product->brand?->name ?? '—'); ?></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted"><?php echo e(__('products.unit')); ?></dt>
                            <dd class="font-medium text-ink"><?php echo e($product->unit ?: '—'); ?></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted"><?php echo e(__('products.primary_category')); ?></dt>
                            <dd class="font-medium text-ink"><?php echo e($product->primaryCategory?->name ?? '—'); ?></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted"><?php echo e(__('products.created')); ?></dt>
                            <dd class="font-medium text-ink"><?php echo e($product->created_at?->format('Y-m-d')); ?></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted"><?php echo e(__('products.updated')); ?></dt>
                            <dd class="font-medium text-ink"><?php echo e($product->updated_at?->format('Y-m-d')); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $product->hasVariants() && $this->singleVariant): ?>
                <div class="edz-card">
                    <div class="edz-card__header">
                        <h2 class="edz-card__title"><?php echo e(__('products.pricing_stock')); ?></h2>
                    </div>
                    <div class="edz-card__body grid grid-cols-1 gap-3 text-sm">
                        <?php
                            $sv = $this->singleVariant;
                            $svProfit = $sv->price - $sv->cost_price;
                            $svMargin = $sv->price > 0 ? round(($svProfit / $sv->price) * 100, 1) : null;
                        ?>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted"><?php echo e(__('products.price')); ?></dt>
                            <dd class="font-semibold text-ink"><?php echo e(number_format($sv->price, 2)); ?></dd>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sv->compare_price): ?>
                            <div class="flex justify-between gap-2">
                                <dt class="text-ink-muted"><?php echo e(__('products.compare_at')); ?></dt>
                                <dd class="font-medium text-ink line-through"><?php echo e(number_format($sv->compare_price, 2)); ?></dd>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted"><?php echo e(__('products.cost')); ?></dt>
                            <dd class="font-medium text-ink"><?php echo e(number_format($sv->cost_price, 2)); ?></dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted"><?php echo e(__('products.profit_margin')); ?></dt>
                            <dd class="font-medium text-ink">
                                <?php echo e(number_format($svProfit, 2)); ?>

                                <span class="text-ink-muted">(<?php echo e($svMargin !== null ? $svMargin.'%' : '—'); ?>)</span>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-muted"><?php echo e(__('products.stock')); ?></dt>
                            <dd class="font-medium text-ink"><?php echo e($sv->stock); ?></dd>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/products/show.blade.php ENDPATH**/ ?>