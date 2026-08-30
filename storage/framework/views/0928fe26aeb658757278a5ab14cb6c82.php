<?php
    $limits = \App\Support\Storefront\StorefrontSections::TEXT_LIMITS;
?>
<?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'social-proof-title',
    'label' => __('merchant_panel.section_title'),
    'wirePath' => 'section_content.social_proof.title',
    'max' => $limits['title'],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [0, 1, 2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        // Defensive: editor state may be partial mid-edit (tests / stale clients).
        $item = $section_content['social_proof']['items'][$i] ?? [];
    ?>
    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700 space-y-3">
        <p class="text-xs font-medium text-ink-400 uppercase tracking-wider"><?php echo e(__('merchant_panel.item')); ?> <?php echo e($i + 1); ?></p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
                'id' => 'social-proof-item-' . $i . '-title',
                'label' => __('merchant_panel.item_title'),
                'wirePath' => 'section_content.social_proof.items.' . $i . '.title',
                'max' => $limits['item_title'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
                'id' => 'social-proof-item-' . $i . '-description',
                'label' => __('merchant_panel.item_description'),
                'wirePath' => 'section_content.social_proof.items.' . $i . '.description',
                'max' => $limits['item_description'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        
        <div class="flex items-center gap-3 relative" x-data="{ open: false }" data-item-index="<?php echo e($i); ?>">
            <input type="hidden" wire:model="section_content.social_proof.items.<?php echo e($i); ?>.icon" />
            <span class="text-xs font-medium text-ink-400 shrink-0"><?php echo e(__('merchant_panel.item_icon')); ?></span>
            <span class="w-9 h-9 rounded-md bg-brand-50 dark:bg-brand-900/40 flex items-center justify-center text-accent-600 dark:text-accent-400 shrink-0">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => ($item['icon'] ?? '') !== '' ? $item['icon'] : 'grid','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($item['icon'] ?? '') !== '' ? $item['icon'] : 'grid'),'class' => 'w-5 h-5']); ?>
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
            </span>
            <button type="button"
                class="edz-btn edz-btn--ghost edz-btn--sm shrink-0"
                x-on:click="open = !open"
                :aria-expanded="open ? 'true' : 'false'"
                aria-haspopup="true">
                <?php echo e(__('merchant_panel.choose_icon')); ?>

                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3 h-3']); ?>
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

            
            <div x-show="open" x-cloak
                x-transition.opacity.duration.150ms
                class="absolute z-20 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg grid grid-cols-7 gap-1 w-[min(22rem,90vw)] <?php echo e($i === 2 ? 'bottom-full mb-2' : 'top-full mt-2'); ?>"
                role="listbox" aria-label="<?php echo e(__('merchant_panel.choose_icon')); ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Support\Storefront\StorefrontSections::ICONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $iconName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button"
                        data-icon="<?php echo e($iconName); ?>"
                        title="<?php echo e($iconName); ?>"
                        class="p-2 rounded-lg hover:bg-brand-50 dark:hover:bg-brand-900/40 flex items-center justify-center text-ink-600 dark:text-ink-300"
                        x-on:click="(async () => { await $wire.set('section_content.social_proof.items.' + $root.dataset.itemIndex + '.icon', $el.dataset.icon, true); open = false })()">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => ''.e($iconName).'','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($iconName).'','class' => 'w-5 h-5']); ?>
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
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/storefront-settings/fields/social_proof.blade.php ENDPATH**/ ?>