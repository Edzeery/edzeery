<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Brand;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Storage;

?>

<div>
    <?php if (isset($component)) { $__componentOriginal64446345db7363332d7ff2707d878bc4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal64446345db7363332d7ff2707d878bc4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.page-header','data' => ['title' => __('products.title'),'description' => __('products.subtitle', ['store' => currentStore()?->name])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('products.title')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('products.subtitle', ['store' => currentStore()?->name]))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canCreate()): ?>
                <a href="<?php echo e(route('merchant.products.create', currentStore())); ?>" wire:navigate
                   class="edz-btn edz-btn--primary edz-btn--sm"><?php echo e(__('products.new_product')); ?></a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal64446345db7363332d7ff2707d878bc4)): ?>
<?php $attributes = $__attributesOriginal64446345db7363332d7ff2707d878bc4; ?>
<?php unset($__attributesOriginal64446345db7363332d7ff2707d878bc4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal64446345db7363332d7ff2707d878bc4)): ?>
<?php $component = $__componentOriginal64446345db7363332d7ff2707d878bc4; ?>
<?php unset($__componentOriginal64446345db7363332d7ff2707d878bc4); ?>
<?php endif; ?>

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title"><?php echo e(__('products.list_title')); ?></h2>
                <p class="text-sm text-ink-400"><?php echo e(__('products.list_subtitle')); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <input type="search" class="edz-input" placeholder="<?php echo e(__('products.search_placeholder')); ?>"
                       wire:model.live.debounce.300ms="search">
            </div>
            <div>
                <select class="edz-select" wire:model.live="brand_id">
                    <option value=""><?php echo e(__('products.all_brands')); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div>
                <select class="edz-select" wire:model.live="is_active">
                    <option value=""><?php echo e(__('products.all_statuses')); ?></option>
                    <option value="1"><?php echo e(__('products.active')); ?></option>
                    <option value="0"><?php echo e(__('products.inactive')); ?></option>
                </select>
            </div>
            <div>
                <select class="edz-select" wire:model.live="is_featured">
                    <option value=""><?php echo e(__('products.all_featured')); ?></option>
                    <option value="1"><?php echo e(__('products.featured')); ?></option>
                    <option value="0"><?php echo e(__('products.not_featured')); ?></option>
                </select>
            </div>
            <div>
                <input type="date" class="edz-input" wire:model.live="created_at">
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($selected)): ?>
            <div class="flex flex-wrap items-center gap-2 border-b border-surface-border bg-brand-50/50 px-4 py-3 dark:bg-brand-950/30">
                <span class="text-sm font-medium text-ink"><?php echo e(__('general.selected_count', ['count' => count($selected)])); ?></span>
                <button type="button" class="edz-btn edz-btn--secondary edz-btn--sm" wire:click="activateSelected"><?php echo e(__('products.activate')); ?></button>
                <button type="button" class="edz-btn edz-btn--secondary edz-btn--sm" wire:click="deactivateSelected"><?php echo e(__('products.deactivate')); ?></button>
                <button type="button" class="edz-btn edz-btn--danger edz-btn--sm"
                        x-data
                        @click.prevent="if (await EdzSwal.confirmBulkDelete(<?php echo e(count($selected)); ?>)) $wire.deleteSelected()"><?php echo e(__('buttons.delete')); ?></button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox"
                                   wire:model.live="select_all"
                                   aria-label="Select all">
                        </th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.product')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.sku')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('products.variants_label')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.brand')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.category')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('general.status')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.created')); ?></th>
                        <th class="px-4 py-3 text-end font-semibold"><?php echo e(__('general.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selected" value="<?php echo e($product->id); ?>" aria-label="Select <?php echo e($product->name); ?>">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="<?php echo e($this->imageUrl($product)); ?>" alt="<?php echo e($product->name); ?>"
                                         class="h-10 w-10 flex-none rounded-lg border border-surface-border object-cover">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-ink" title="Barcode: <?php echo e($product->barcode); ?>"><?php echo e($product->name); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->barcode): ?>
                                            <p class="font-mono text-xs text-ink-muted"><?php echo e($product->barcode); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft"><?php echo e($product->sku); ?></td>
                            <td class="px-4 py-3 text-ink-soft">
                                <?php echo e($product->hasVariants() ? 'Yes' : 'No'); ?>

                            </td>
                            <td class="px-4 py-3 text-ink-soft"><?php echo e($product->brand?->name ?? '—'); ?></td>
                            <td class="px-4 py-3 text-ink-soft"><?php echo e($product->primaryCategory?->name ?? '—'); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-1.5">
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
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-muted"><?php echo e($product->created_at?->format('Y-m-d')); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?php echo e(route('merchant.products.show', [currentStore(), $product])); ?>" wire:navigate
                                       class="edz-btn edz-btn--ghost edz-btn--sm"><?php echo e(__('buttons.view')); ?></a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canUpdate()): ?>
                                        <a href="<?php echo e(route('merchant.products.edit', [currentStore(), $product])); ?>" wire:navigate
                                           class="edz-btn edz-btn--ghost edz-btn--sm"><?php echo e(__('buttons.edit')); ?></a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDelete()): ?>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                x-data
                                                @click.prevent="if (await EdzSwal.confirmDelete('<?php echo e(addslashes($product->name)); ?>')) $wire.delete('<?php echo e($product->id); ?>')"
                                                ><?php echo e(__('buttons.delete')); ?></button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft"><?php echo e(__('products.no_products')); ?></p>
                                <p class="mt-1 text-sm text-ink-muted"><?php echo e(__('products.try_adjusting')); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canCreate()): ?>
                                    <a href="<?php echo e(route('merchant.products.create', currentStore())); ?>" wire:navigate
                                       class="edz-btn edz-btn--primary edz-btn--sm mt-4"><?php echo e(__('products.new_product')); ?></a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->products->hasPages()): ?>
            <div class="border-t border-surface-border px-4 py-3">
                <?php echo e($this->products->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/products/index.blade.php ENDPATH**/ ?>