<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\InventoryMovement;
use App\Models\Products\ProductVariant;
use App\Services\InventoryService;

?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title"><?php echo e(__('inventories.title')); ?></h1>
            <p class="edz-page-head__subtitle"><?php echo e(__('inventories.subtitle', ['store' => currentStore()?->name])); ?></p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="edz-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ink-soft"><?php echo e(__('inventories.total_stock')); ?></p>
                    <p class="mt-1 text-2xl font-bold text-ink"><?php echo e(number_format($this->stats['total'])); ?></p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300"><?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check-circle','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?></span>
            </div>
        </div>
        <div class="edz-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ink-soft"><?php echo e(__('inventories.low_stock')); ?></p>
                    <p class="mt-1 text-2xl font-bold text-ink"><?php echo e(number_format($this->stats['low'])); ?></p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300"><?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'exclamation-triangle','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'exclamation-triangle','class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?></span>
            </div>
        </div>
        <div class="edz-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ink-soft"><?php echo e(__('inventories.out_of_stock')); ?></p>
                    <p class="mt-1 text-2xl font-bold text-ink"><?php echo e(number_format($this->stats['out'])); ?></p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300"><?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?></span>
            </div>
        </div>
        <div class="edz-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-ink-soft"><?php echo e(__('inventories.movements_7d')); ?></p>
                    <p class="mt-1 text-2xl font-bold text-ink"><?php echo e(number_format($this->stats['movements7'])); ?></p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300"><?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrows-right-left','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrows-right-left','class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?></span>
            </div>
        </div>
    </div>

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title"><?php echo e(__('inventories.list_title')); ?></h2>
                <p class="text-sm text-ink-400"><?php echo e(__('inventories.list_subtitle')); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="<?php echo e(__('inventories.search_placeholder')); ?>"
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.product')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.sku')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.stock')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('general.status')); ?></th>
                        <th class="px-4 py-3 text-end font-semibold"><?php echo e(__('general.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->inventories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-medium text-ink"><?php echo e($variant->product?->name ?? '—'); ?></td>
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft"><?php echo e($variant->sku); ?></td>
                            <td class="px-4 py-3 font-semibold <?php echo e($variant->stock <= 0 ? 'text-danger-600' : 'text-success-600'); ?>"><?php echo e($variant->stock); ?></td>
                            <td class="px-4 py-3">
                                <?php $badge = $this->stockBadge($variant); ?>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo e($badge['class']); ?>"
                                      title="Threshold: <?php echo e($variant->low_stock_threshold); ?>">
                                    <?php echo e($badge['text']); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canAdjust()): ?>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="toggleAdjust('<?php echo e($variant->id); ?>')">
                                             <?php echo e($adjustingId === $variant->id ? __('inventories.cancel') : __('inventories.adjust_stock')); ?>

                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <a href="<?php echo e($this->movementsUrl($variant)); ?>" wire:navigate class="edz-btn edz-btn--ghost edz-btn--sm"><?php echo e(__('inventories.movements')); ?></a>
                                </div>
                            </td>
                        </tr>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($adjustingId === $variant->id): ?>
                            <tr class="bg-surface-secondary/40">
                                <td colspan="5" class="px-4 py-4">
                                    <form wire:submit="adjust('<?php echo e($variant->id); ?>')" class="flex flex-wrap items-end gap-3">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-ink-soft" for="adjust-qty"><?php echo e(__('inventories.new_quantity')); ?></label>
                                            <input id="adjust-qty" type="number" min="0" class="edz-input edz-input--sm"
                                                   wire:model="adjust_quantity" placeholder="<?php echo e($variant->stock); ?>">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['adjust_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="mt-1 text-xs text-danger-600"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="min-w-48">
                                            <label class="mb-1 block text-xs font-medium text-ink-soft" for="adjust-reason"><?php echo e(__('inventories.reason')); ?></label>
                                            <input id="adjust-reason" type="text" maxlength="255" class="edz-input edz-input--sm"
                                                   wire:model="adjust_reason" placeholder="<?php echo e(__('inventories.reason_placeholder')); ?>">
                                        </div>
                                         <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm"><?php echo e(__('inventories.adjust_btn')); ?></button>
                                        <span class="text-xs text-ink-muted"><?php echo e(__('inventories.current_stock', ['count' => $variant->stock])); ?></span>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft"><?php echo e(__('inventories.no_inventory')); ?></p>
                                <p class="mt-1 text-sm text-ink-muted"><?php echo e(__('inventories.try_adjusting')); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->inventories->hasPages()): ?>
            <div class="border-t border-surface-border px-4 py-3">
                <?php echo e($this->inventories->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/inventories/index.blade.php ENDPATH**/ ?>