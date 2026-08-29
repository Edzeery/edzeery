<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\ProductVariant;

?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title"><?php echo e(__('stock_alerts.title')); ?></h1>
            <p class="edz-page-head__subtitle"><?php echo e(__('stock_alerts.subtitle', ['store' => currentStore()?->name])); ?></p>
        </div>
    </div>

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title"><?php echo e(__('stock_alerts.list_title')); ?></h2>
                <p class="text-sm text-ink-400"><?php echo e(__('stock_alerts.list_subtitle')); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="<?php echo e(__('stock_alerts.search_placeholder')); ?>"
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('stock_alerts.sku')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('stock_alerts.product')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('stock_alerts.stock_col')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('stock_alerts.threshold')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('stock_alerts.status')); ?></th>
                        <th class="px-4 py-3 text-end font-semibold"><?php echo e(__('stock_alerts.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft"><?php echo e($variant->sku); ?></td>
                            <td class="px-4 py-3 font-medium text-ink"><?php echo e($variant->product?->name ?? '—'); ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    <?php if($variant->stock <= 0): ?> bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300
                                    <?php else: ?> bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300 <?php endif; ?>">
                                    <?php echo e($variant->stock); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-ink-muted"><?php echo e($variant->low_stock_threshold); ?></td>
                            <td class="px-4 py-3">
                                <?php $badge = $this->statusBadge($variant); ?>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo e($badge['class']); ?>">
                                    <?php echo e($badge['text']); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?php echo e(route('merchant.variants.index', currentStore())); ?>" wire:navigate
                                       class="edz-btn edz-btn--ghost edz-btn--sm"><?php echo e(__('inventories.adjust_stock')); ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft"><?php echo e(__('stock_alerts.no_alerts')); ?></p>
                                <p class="mt-1 text-sm text-ink-muted"><?php echo e(__('stock_alerts.all_sufficient')); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->alerts->hasPages()): ?>
            <div class="border-t border-surface-border px-4 py-3">
                <?php echo e($this->alerts->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/stock-alerts/index.blade.php ENDPATH**/ ?>