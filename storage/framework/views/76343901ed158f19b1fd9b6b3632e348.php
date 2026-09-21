<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Models\Products\Product;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreProductScopeService;

?>

<div @edz-modal-closed.window="$wire.close()">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->membership): ?>
        <?php $count = count($this->assignedIds); ?>
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'md','showCloseButton' => true,'wire:key' => 'product-scope-modal-'.e($this->membership->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => true,'size' => 'md','show-close-button' => true,'wire:key' => 'product-scope-modal-'.e($this->membership->id).'']); ?>
            <div class="p-5">
                <div class="flex items-start justify-between gap-3 pe-10">
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-ink"><?php echo e(__('teams.product_scope_title')); ?></h3>
                        <p class="mt-0.5 truncate text-sm text-ink-muted"><?php echo e($this->membership->user?->name); ?></p>
                    </div>
                    <span class="edz-badge edz-badge--neutral edz-badge--sm shrink-0">
                        <?php echo e(trans_choice('teams.product_scope_assigned_count', $count, ['count' => $count])); ?>

                    </span>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count === 0): ?>
                    <div class="mt-4 rounded-lg border border-surface-border bg-surface-secondary/40 p-4">
                        <p class="text-sm leading-relaxed text-ink"><?php echo e(__('teams.product_scope_unrestricted')); ?></p>
                    </div>
                <?php else: ?>
                    <ul class="mt-4 divide-y divide-surface-border rounded-lg border border-surface-border">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->assigned; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center justify-between gap-3 px-3 py-2.5">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-ink"><?php echo e($item['name']); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['sku'])): ?>
                                        <span class="mt-0.5 block text-xs text-ink-muted"><?php echo e($item['sku']); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                                <button type="button"
                                    class="edz-btn edz-btn--ghost edz-btn--sm shrink-0 text-danger-600 hover:text-danger-700"
                                    x-data
                                    data-confirm-title="<?php echo e(__('teams.product_scope_remove')); ?>"
                                    data-confirm-text="<?php echo e(__('teams.product_scope_remove_confirm')); ?>"
                                    data-scope-product-id="<?php echo e($item['id']); ?>"
                                    @click.prevent="(async () => { if (await EdzSwal.confirmAction($el.dataset.confirmTitle, $el.dataset.confirmText)) await $wire.removeProductScope($el.dataset.scopeProductId) })()">
                                    <?php echo e(__('buttons.remove')); ?>

                                </button>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-5">
                    <label class="edz-label"><?php echo e(__('teams.product_scope_add_label')); ?></label>
                    <div class="relative">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'magnifying-glass','class' => 'absolute start-3 top-1/2 w-4 h-4 -translate-y-1/2 text-ink-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'magnifying-glass','class' => 'absolute start-3 top-1/2 w-4 h-4 -translate-y-1/2 text-ink-muted']); ?>
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
                        <input type="search" wire:model.live.debounce.300ms="search"
                            class="edz-input w-full ps-9"
                            placeholder="<?php echo e(__('teams.product_scope_search_placeholder')); ?>">
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->availableProducts): ?>
                        <ul class="mt-3 max-h-56 divide-y divide-surface-border overflow-y-auto rounded-lg border border-surface-border">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->availableProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center justify-between gap-3 px-3 py-2.5">
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-medium text-ink"><?php echo e($product['name']); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($product['sku'])): ?>
                                            <span class="mt-0.5 block text-xs text-ink-muted"><?php echo e($product['sku']); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                    <button type="button" wire:click="addProductScope('<?php echo e($product['id']); ?>')"
                                        class="edz-btn edz-btn--primary edz-btn--sm shrink-0">
                                        <?php echo e(__('buttons.add')); ?>

                                    </button>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    <?php else: ?>
                        <p class="mt-3 text-sm text-ink-muted"><?php echo e(__('teams.product_scope_no_products')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="mt-5 flex justify-end border-t border-surface-border pt-4">
                    <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="close">
                        <?php echo e(__('buttons.done')); ?>

                    </button>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $attributes = $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $component = $__componentOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\teams\partials\product-scope-modal.blade.php ENDPATH**/ ?>