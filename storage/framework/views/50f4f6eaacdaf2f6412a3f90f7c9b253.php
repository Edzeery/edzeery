<?php

use App\Enums\Store\InventoryMovementType;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Services\InventoryService;
use App\Support\SkuGenerator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title"><?php echo e(__('variants.title')); ?></h1>
            <p class="edz-page-head__subtitle"><?php echo e(__('variants.subtitle', ['store' => currentStore()?->name])); ?></p>
        </div>
        <div class="flex items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canCreate()): ?>
                <button type="button" class="edz-btn edz-btn--primary" wire:click="openCreate"><?php echo e(__('variants.new_variant')); ?></button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($creating || $editingId): ?>
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title"><?php echo e($editingId ? __('variants.edit_variant') : __('variants.new_variant')); ?></h2>
                    <p class="text-sm text-ink-400"><?php echo e($editingId ? __('variants.edit_variant_desc') : __('variants.new_variant_desc')); ?></p>
                </div>
            </div>

            <form wire:submit="save" class="space-y-4 p-4" x-data="edzDirty()">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-ink" for="variant-product"><?php echo e(__('variants.product')); ?></label>
                        <select id="variant-product" class="edz-select <?php $__errorArgs = ['product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                wire:model="product_id" <?php if($editingId): echo 'disabled'; endif; ?>>
                            <option value=""><?php echo e(__('variants.select_product')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $productName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>" <?php if((string) $product_id === (string) $id): echo 'selected'; endif; ?>><?php echo e($productName); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-danger-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="variant-sku"><?php echo e(__('variants.sku')); ?></label>
                        <input id="variant-sku" type="text" class="edz-input <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               wire:model="sku" <?php if($editingId): echo 'disabled'; endif; ?> placeholder="STORE-PRODUCT-SIZE">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $editingId): ?>
                            <button type="button" class="mt-1 text-xs text-brand-600 hover:underline"
                                    wire:click="generateSku"><?php echo e(__('variants.generate_sku')); ?></button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-danger-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink" for="variant-price"><?php echo e(__('variants.price')); ?></label>
                            <input id="variant-price" type="number" step="0.01" min="0" class="edz-input <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   wire:model="price" placeholder="0.00">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-danger-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink" for="variant-compare"><?php echo e(__('variants.compare_price')); ?></label>
                            <input id="variant-compare" type="number" step="0.01" min="0" class="edz-input"
                                   wire:model="compare_price" placeholder="0.00">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink" for="variant-cost"><?php echo e(__('variants.cost_price')); ?></label>
                            <input id="variant-cost" type="number" step="0.01" min="0" class="edz-input"
                                   wire:model="cost_price" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm"><?php echo e(__('buttons.save')); ?></button>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="cancelForm"><?php echo e(__('buttons.cancel')); ?></button>
                </div>
            </form>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title"><?php echo e(__('variants.list_title')); ?></h2>
                <p class="text-sm text-ink-400"><?php echo e(__('variants.list_subtitle')); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input"                        placeholder="<?php echo e(__('variants.search_placeholder')); ?>"
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($selected)): ?>
            <div class="flex flex-wrap items-center gap-2 border-b border-surface-border bg-brand-50/50 px-4 py-3 dark:bg-brand-950/30">
                <span class="text-sm font-medium text-ink"><?php echo e(__('general.selected_count', ['count' => count($selected)])); ?></span>
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
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('variants.product')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('variants.sku')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('variants.price')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('variants.compare_price')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('variants.cost_price')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('variants.stock')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('variants.created')); ?></th>
                        <th class="px-4 py-3 text-end font-semibold"><?php echo e(__('general.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selected" value="<?php echo e($variant->id); ?>" aria-label="Select <?php echo e($variant->sku); ?>">
                            </td>
                            <td class="px-4 py-3 font-medium text-ink"><?php echo e($variant->product?->name ?? '—'); ?></td>
                            <td class="px-4 py-3 font-mono text-xs text-ink-soft"><?php echo e($variant->sku); ?></td>
                            <td class="px-4 py-3 text-ink"><?php echo e(number_format($variant->price, 2)); ?> DZD</td>
                            <td class="px-4 py-3 text-ink-muted"><?php echo e($variant->compare_price !== null ? number_format($variant->compare_price, 2).' DZD' : '—'); ?></td>
                            <td class="px-4 py-3 text-ink-muted"><?php echo e($variant->cost_price !== null ? number_format($variant->cost_price, 2).' DZD' : '—'); ?></td>
                            <td class="px-4 py-3">
                                <?php $badge = $this->stockBadge($variant); ?>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo e($badge['class']); ?>"
                                      title="Stock: <?php echo e($variant->stock); ?> | Threshold: <?php echo e($variant->low_stock_threshold); ?>">
                                    <?php echo e($badge['text']); ?>

                                </span>
                                <span class="ms-1 text-xs text-ink-muted"><?php echo e($variant->stock); ?></span>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-muted"><?php echo e($variant->created_at?->diffForHumans()); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            wire:click="toggleHistory('<?php echo e($variant->id); ?>')">
                                        <?php echo e($historyId === $variant->id ? __('buttons.close') : __('variants.history')); ?>

                                    </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canUpdate()): ?>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="toggleAdjust('<?php echo e($variant->id); ?>')"><?php echo e(__('variants.adjust_stock')); ?></button>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="beginEdit('<?php echo e($variant->id); ?>')"><?php echo e(__('buttons.edit')); ?></button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDelete()): ?>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                x-data
                                                @click.prevent="if (await EdzSwal.confirmDelete('<?php echo e(addslashes($variant->sku)); ?>')) $wire.delete('<?php echo e($variant->id); ?>')"
                                                ><?php echo e(__('buttons.delete')); ?></button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($adjustingId === $variant->id): ?>
                            <tr class="bg-surface-secondary/40">
                                <td colspan="9" class="px-4 py-4">
                                    <form wire:submit="applyStock('<?php echo e($variant->id); ?>')" class="flex flex-wrap items-end gap-3">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-ink-soft" for="adjust-qty"><?php echo e(__('table.quantity')); ?></label>
                                            <input id="adjust-qty" type="number" min="1" class="edz-input edz-input--sm"
                                                   wire:model="adjust_quantity" placeholder="1">
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
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-ink-soft" for="adjust-type"><?php echo e(__('table.type')); ?></label>
                                            <select id="adjust-type" class="edz-select edz-input--sm" wire:model="adjust_type">
                                                <option value=""><?php echo e(__('product_options.select_type')); ?></option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->manualTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($value); ?>" <?php if($adjust_type === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </select>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['adjust_type'];
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
                                        <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm"><?php echo e(__('buttons.apply')); ?></button>
                                        <span class="text-xs text-ink-muted"><?php echo e(__('inventories.current_stock', ['count' => $variant->stock])); ?></span>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($historyId === $variant->id): ?>
                            <tr class="bg-surface-secondary/40">
                                <td colspan="9" class="px-4 py-4">
                                    <?php $movements = $this->movements($variant); ?>
                                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('variants.history')); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($movements->isEmpty()): ?>
                                        <p class="text-sm text-ink-muted"><?php echo e(__('variants.no_adjustments')); ?></p>
                                    <?php else: ?>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead>
<tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                                                        <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('table.date')); ?></th>
                                                        <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('table.type')); ?></th>
                                                        <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('table.qty')); ?></th>
                                                        <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('table.after')); ?></th>
                                                        <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('table.by')); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr class="border-b border-surface-border last:border-0">
                                                            <td class="px-3 py-2 text-xs text-ink-muted"><?php echo e($movement->created_at?->format('Y-m-d H:i')); ?></td>
                                                            <td class="px-3 py-2">
                                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                                                    <?php if($movement->type->isDecrease()): ?> bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300
                                                                    <?php elseif($movement->type->isIncrease()): ?> bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300
                                                                    <?php else: ?> bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300 <?php endif; ?>">
                                                                    <?php echo e($movement->type->label()); ?>

                                                                </span>
                                                            </td>
                                                            <td class="px-3 py-2 font-semibold <?php echo e($movement->type->isDecrease() ? 'text-danger-600' : 'text-success-600'); ?>">
                                                                <?php echo e($movement->type->isDecrease() ? '-' : '+'); ?><?php echo e($movement->quantity); ?>

                                                            </td>
                                                            <td class="px-3 py-2 text-ink-soft"><?php echo e($movement->balance_after); ?></td>
                                                            <td class="px-3 py-2 text-xs text-ink-muted"><?php echo e($movement->user?->name ?? __('general.system')); ?></td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft"><?php echo e(__('variants.no_variants')); ?></p>
                                <p class="mt-1 text-sm text-ink-muted"><?php echo e(__('variants.try_adjusting')); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->variants->hasPages()): ?>
            <div class="border-t border-surface-border px-4 py-3">
                <?php echo e($this->variants->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\variants\index.blade.php ENDPATH**/ ?>