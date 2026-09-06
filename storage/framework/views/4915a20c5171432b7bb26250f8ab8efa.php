<?php

use App\Domains\Cart\Support\OrderRules;
use App\Models\Products\Product;
use Illuminate\Support\Collection;

?>

<?php
    // Explicit component reads keep the template independent of Volt's
    // variable-injection behaviour.
    $matrixLimits = $this->limits;
    $matrixGroups = $this->orderGroups;
    $matrixTracksInventory = $this->tracksInventory;
?>

<div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5"
    data-matrix-root
    data-min="<?php echo e($matrixLimits['min']); ?>"
    x-data="{
        pick(box) {
            const root = box.closest('[data-matrix-root]');
            const row = box.closest('li');
            const input = row ? row.querySelector('input[type=number]') : null;
            if (!input || box.disabled) { return; }
            if (box.checked) {
                const group = box.getAttribute('data-exclusive-group');
                if (group) {
                    root.querySelectorAll('input[data-exclusive-group=\'' + group + '\']').forEach((other) => {
                        if (other !== box) {
                            other.checked = false;
                            const otherInput = other.closest('li').querySelector('input[type=number]');
                            otherInput.value = 0;
                            otherInput.dispatchEvent(new Event('input'));
                        }
                    });
                }
                const min = parseInt(root.dataset.min) || 1;
                if ((parseInt(input.value) || 0) < min) { input.value = String(min); }
                input.dispatchEvent(new Event('input'));
            } else {
                input.value = '0';
                input.dispatchEvent(new Event('input'));
            }
        },
        dec(input) {
            input.value = String(Math.max(0, (parseInt(input.value) || 0) - 1));
            input.dispatchEvent(new Event('input'));
        },
        inc(input, cap) {
            const next = (parseInt(input.value) || 0) + 1;
            const limit = parseInt(cap);
            input.value = Number.isFinite(limit) && limit > 0 ? String(Math.min(next, limit)) : String(next);
            input.dispatchEvent(new Event('input'));
        }
    }">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'adjustments-horizontal','class' => 'w-4 h-4 store-text-primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'adjustments-horizontal','class' => 'w-4 h-4 store-text-primary']); ?>
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
        <?php echo e(__('storefront.variants_matrix_title')); ?>

    </h3>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($matrixLimits['min'] > 1 || $matrixLimits['max']): ?>
        <p class="text-xs text-ink-muted mb-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($matrixLimits['min'] > 1): ?>
                <?php echo e(__('storefront.min_order_hint', ['min' => $matrixLimits['min']])); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($matrixLimits['max']): ?>
                <?php echo e(__('storefront.max_order_hint', ['max' => $matrixLimits['max']])); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($matrixGroups->isEmpty()): ?>
        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.no_products_found')); ?></p>
    <?php else: ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $matrixGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($group['label']): ?>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-2"><?php echo e($group['label']); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <ul class="divide-y divide-gray-100 dark:divide-gray-800 <?php if($loop->last): ?> mb-4 <?php endif; ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $group['units']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $matrixVariant = $unit['variant'];
                        $matrixBlocked = $unit['out'] && ! $unit['preorder'];
                    ?>
                    <li wire:key="vm-row-<?php echo e($matrixVariant->id); ?>"
                        data-cap="<?php echo e($unit['cap'] ?? ''); ?>"
                        class="py-3 first:pt-0 last:pb-0"
                        x-data="{ on: false, full: false, sync() { const q = this.$refs.q; const v = (q && parseInt(q.value)) || 0; this.on = v > 0; const c = parseInt(this.$el.dataset.cap); this.full = !isNaN(c) && c > 0 && v >= c; if (this.$refs.box) { this.$refs.box.checked = this.on; } } }"
                        x-init="sync()">
                        <div class="flex items-center justify-between gap-3">
                            
                            <label class="flex items-start gap-3 cursor-pointer select-none min-w-0 flex-1">
                                <input type="checkbox" x-ref="box" x-on:change="pick($el)" <?php if($matrixBlocked): ?> disabled <?php endif; ?>
                                    <?php if($group['exclusive']): ?> data-exclusive-group="<?php echo e($group['key']); ?>" <?php endif; ?>
                                    class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 dark:border-gray-600 store-bg-primary focus:ring-0 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white truncate">
                                        <?php echo e($unit['label']); ?>

                                    </span>
                                    <span class="block text-xs mt-0.5">
                                        <span class="store-text-primary font-semibold"><?php echo e(currency($matrixVariant->price)); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($matrixTracksInventory): ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit['preorder']): ?>
                                                <span class="ms-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"><?php echo e(__('storefront.pre_order')); ?></span>
                                            <?php elseif($unit['out']): ?>
                                                <span class="ms-1 text-red-500 dark:text-red-400"><?php echo e(__('storefront.out_of_stock')); ?></span>
                                            <?php else: ?>
                                                <span class="ms-1 text-emerald-600 dark:text-emerald-400"><?php echo e(__('storefront.in_stock')); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                </span>
                            </label>

                            
                            <div class="shrink-0 flex items-center rounded-xl border border-gray-300 dark:border-gray-600 overflow-hidden transition-opacity duration-150"
                                x-show="on"
                                x-cloak>
                                <button type="button" tabindex="-1"
                                    x-on:click="dec($el.parentElement.querySelector('input'))"
                                    <?php if($matrixBlocked): ?> disabled <?php endif; ?>
                                    class="w-9 h-9 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition disabled:opacity-40"
                                    aria-label="-">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'minus','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'minus','class' => 'w-3.5 h-3.5']); ?>
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
                                </button>
                                <input type="number" min="0" value="0" x-ref="q"
                                    wire:model="quantities.<?php echo e($matrixVariant->id); ?>"
                                    x-on:input="sync()"
                                    <?php if($matrixBlocked): ?> disabled <?php endif; ?>
                                    class="w-12 h-9 text-center text-sm font-semibold bg-transparent border-x border-gray-300 dark:border-gray-600 focus:outline-none disabled:opacity-50 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" tabindex="-1"
                                    x-on:click="inc($el.parentElement.querySelector('input'), $el.closest('li').dataset.cap)"
                                    <?php if($matrixBlocked): ?> disabled <?php endif; ?>
                                    :disabled="full"
                                    class="w-9 h-9 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                    aria-label="+">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'plus','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'w-3.5 h-3.5']); ?>
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
                                </button>
                            </div>
                        </div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mt-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($direct): ?>
                <button type="button" wire:click="buyNowFromMatrix"
                    class="store-btn-primary w-full h-12 rounded-xl font-semibold flex items-center justify-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shopping-bag','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-bag','class' => 'w-5 h-5']); ?>
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
                    <?php echo e(__('storefront.order_now')); ?>

                </button>
            <?php else: ?>
                <button type="button" wire:click="addAllToCart"
                    class="store-btn-primary w-full h-12 rounded-xl font-semibold flex items-center justify-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shopping-cart','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-cart','class' => 'w-5 h-5']); ?>
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
                    <?php echo e(__('storefront.add_all_to_cart')); ?>

                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\storefront\variant-matrix.blade.php ENDPATH**/ ?>