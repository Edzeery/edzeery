<?php

use App\Enums\Store\InventoryMovementType;
use App\Enums\Store\StorePermissionEnum;
use App\Models\InventoryMovement;

?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title"><?php echo e(__('titles.inventory_movements')); ?></h1>
            <p class="edz-page-head__subtitle"><?php echo e(__('inventories.subtitle', ['store' => currentStore()?->name])); ?></p>
        </div>
    </div>

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title"><?php echo e(__('inventories.movements')); ?></h2>
                <p class="text-sm text-ink-400"><?php echo e(__('inventories.list_subtitle')); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->query('variant_id')): ?>
                <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="clearVariantFilter"><?php echo e(__('buttons.clear')); ?></button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="<?php echo e(__('inventories.search_placeholder')); ?>"
                       wire:model.live.debounce.300ms="search">
            </div>
            <div>
                <select class="edz-select" wire:model.live="typeFilter">
                    <option value=""><?php echo e(__('general.all')); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->typeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if($typeFilter === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.date')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.product')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.sku')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.type')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.qty')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.after')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.by')); ?></th>
                        <th class="px-4 py-3 text-end font-semibold"><?php echo e(__('general.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 text-xs text-ink-muted"><?php echo e($movement->created_at?->format('Y-m-d H:i')); ?></td>
                            <td class="px-4 py-3 font-medium text-ink"><?php echo e($movement->variant?->product?->name ?? '—'); ?></td>
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft"><?php echo e($movement->variant?->sku ?? '—'); ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    <?php if($movement->type->isDecrease()): ?> bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300
                                    <?php elseif($movement->type->isIncrease()): ?> bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300
                                    <?php else: ?> bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300 <?php endif; ?>">
                                    <?php echo e($movement->type->label()); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold <?php echo e($movement->type->isDecrease() ? 'text-danger-600' : 'text-success-600'); ?>">
                                <?php echo e($movement->type->isDecrease() ? '-' : '+'); ?><?php echo e($movement->quantity); ?>

                            </td>
                            <td class="px-4 py-3 font-bold <?php echo e($movement->balance_after <= 0 ? 'text-danger-600' : 'text-ink'); ?>"><?php echo e($movement->balance_after); ?></td>
                            <td class="px-4 py-3 text-xs text-ink-muted"><?php echo e($movement->user?->name ?? __('general.system')); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            wire:click="toggleView('<?php echo e($movement->id); ?>')">
                                        <?php echo e($viewingId === $movement->id ? __('buttons.close') : __('buttons.view')); ?>

                                    </button>
                                </div>
                            </td>
                        </tr>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewingId === $movement->id): ?>
                            <tr class="bg-surface-secondary/40">
                                <td colspan="8" class="px-4 py-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted"><?php echo e(__('table.type')); ?></p>
                                            <p class="mt-1 text-sm font-semibold text-ink"><?php echo e($movement->type->label()); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted"><?php echo e(__('table.date')); ?></p>
                                            <p class="mt-1 text-sm text-ink"><?php echo e($movement->created_at?->format('Y-m-d H:i:s')); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted"><?php echo e(__('table.quantity')); ?></p>
                                            <p class="mt-1 text-sm font-semibold <?php echo e($movement->type->isDecrease() ? 'text-danger-600' : 'text-success-600'); ?>">
                                                <?php echo e($movement->type->isDecrease() ? '-' : '+'); ?><?php echo e($movement->quantity); ?>

                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted"><?php echo e(__('table.after')); ?></p>
                                            <p class="mt-1 text-sm font-semibold text-ink"><?php echo e($movement->balance_after); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted"><?php echo e(__('table.by')); ?></p>
                                            <p class="mt-1 text-sm text-ink"><?php echo e($movement->user?->name ?? __('general.system')); ?>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($movement->user?->email): ?>
                                                    <span class="text-xs text-ink-muted">(<?php echo e($movement->user->email); ?>)</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted"><?php echo e(__('table.product')); ?></p>
                                            <p class="mt-1 text-sm text-ink"><?php echo e($movement->variant?->product?->name ?? '—'); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-ink-muted"><?php echo e(__('table.sku')); ?></p>
                                            <p class="mt-1 font-mono text-sm text-ink"><?php echo e($movement->variant?->sku ?? '—'); ?></p>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($movement->source_type): ?>
                                            <div>
                                                <p class="text-xs font-medium text-ink-muted"><?php echo e(__('general.type')); ?></p>
                                                <p class="mt-1 text-sm text-ink"><?php echo e(class_basename($movement->source_type)); ?></p>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft"><?php echo e(__('inventories.no_inventory')); ?></p>
                                <p class="mt-1 text-sm text-ink-muted"><?php echo e(__('inventories.try_adjusting')); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->movements->hasPages()): ?>
            <div class="border-t border-surface-border px-4 py-3">
                <?php echo e($this->movements->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\inventory-movements\index.blade.php ENDPATH**/ ?>