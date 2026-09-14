<div x-show="step === 4" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($has_variants): ?>
                <div class="edz-card">
                    <div class="edz-card__header">
                        <div>
                            <h2 class="edz-card__title"><?php echo e(__('products.options')); ?></h2>
                            <p class="text-sm text-ink-400"><?php echo e(__('products.options_hint')); ?></p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::PRODUCT_CREATE->value)): ?>
                                <button type="button" wire:click="openCreateOption" wire:loading.attr="disabled"
                                        class="edz-btn edz-btn--secondary edz-btn--sm">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'plus','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'h-4 w-4']); ?>
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
                                    <?php echo e(__('products.new_option')); ?>

                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button type="button" wire:click="addOption" wire:loading.attr="disabled"
                                    class="edz-btn edz-btn--secondary edz-btn--sm"><?php echo e(__('products.add_option')); ?></button>
                        </div>
                    </div>
                    <div class="edz-card__body space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="grid grid-cols-1 gap-3 rounded-lg border border-surface-border p-4 md:grid-cols-2">
                                <div class="edz-field">
                                    <label class="edz-field__label"><?php echo e(__('products.option')); ?></label>
                                    <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'options.'.e($index).'.product_option_id','wire:change' => 'optionChanged('.e($index).', $event.target.value)','options' => $this->productOptions->all(),'optionValue' => 'id','optionLabel' => 'name','placeholder' => ''.e(__('products.select_option')).'','search' => true,'icon' => 'cube','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'options.'.e($index).'.product_option_id','wire:change' => 'optionChanged('.e($index).', $event.target.value)','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->productOptions->all()),'option-value' => 'id','option-label' => 'name','placeholder' => ''.e(__('products.select_option')).'','search' => true,'icon' => 'cube','size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $attributes = $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $component = $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
                                </div>

                                <div class="edz-field">
                                    <label class="edz-field__label"><?php echo e(__('products.values')); ?></label>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($option['type'] ?? null) === \App\Enums\Store\ProductOptionInputType::TEXT->value): ?>
                                        <div class="rounded-md border border-surface-border px-3 py-2 text-sm text-ink-muted">
                                            <?php echo e(__('products.text_options_hint')); ?>

                                        </div>
                                    <?php elseif(! empty($option['product_option_id'])): ?>
                                        <?php if (isset($component)) { $__componentOriginal3466d7a8f1818e31efc9bf8c26922dc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3466d7a8f1818e31efc9bf8c26922dc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.multi-select','data' => ['wire:model' => 'options.'.e($index).'.values','wire:change' => 'valuesChanged('.e($index).')','options' => $this->optionValuesByOption->get($option['product_option_id'], collect())->all(),'optionValue' => 'id','optionLabel' => 'value','selected' => $option['values'] ?? [],'placeholder' => ''.e(__('products.select_values')).'','search' => true,'searchPlaceholder' => ''.e(__('products.values')).'','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.multi-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'options.'.e($index).'.values','wire:change' => 'valuesChanged('.e($index).')','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->optionValuesByOption->get($option['product_option_id'], collect())->all()),'option-value' => 'id','option-label' => 'value','selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($option['values'] ?? []),'placeholder' => ''.e(__('products.select_values')).'','search' => true,'search-placeholder' => ''.e(__('products.values')).'','size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3466d7a8f1818e31efc9bf8c26922dc2)): ?>
<?php $attributes = $__attributesOriginal3466d7a8f1818e31efc9bf8c26922dc2; ?>
<?php unset($__attributesOriginal3466d7a8f1818e31efc9bf8c26922dc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3466d7a8f1818e31efc9bf8c26922dc2)): ?>
<?php $component = $__componentOriginal3466d7a8f1818e31efc9bf8c26922dc2; ?>
<?php unset($__componentOriginal3466d7a8f1818e31efc9bf8c26922dc2); ?>
<?php endif; ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::PRODUCT_UPDATE->value)): ?>
                                            <div class="mt-2 flex items-center gap-2">
                                                <input type="text" class="edz-input min-w-0 flex-1"
                                                       wire:model="quickValueDraft"
                                                       @keydown.enter.prevent="$wire.quickAddValue(<?php echo e($index); ?>)"
                                                       placeholder="<?php echo e(__('product_options.add_value_placeholder')); ?>">
                                                <button type="button"
                                                        wire:click="quickAddValue(<?php echo e($index); ?>)"
                                                        class="edz-btn edz-btn--secondary edz-btn--sm shrink-0"
                                                        title="<?php echo e(__('products.new_value')); ?>">
                                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'plus','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'h-4 w-4']); ?>
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
                                                    <span class="hidden sm:inline"><?php echo e(__('products.new_value')); ?></span>
                                                </button>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        <div class="rounded-md border border-surface-border px-3 py-2 text-sm text-ink-muted">
                                            <?php echo e(__('products.select_option_to_configure')); ?>

                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="md:col-span-2">
                                    <button type="button" wire:click="removeOption(<?php echo e($index); ?>)"
                                            class="text-sm font-semibold text-danger-600 hover:text-danger-700">
                                        <?php echo e(__('products.remove_option')); ?>

                                    </button>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-sm text-ink-muted"><?php echo e(__('products.no_options_yet')); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="edz-card">
                    <div class="edz-card__header">
                        <div>
                            <h2 class="edz-card__title"><?php echo e(__('products.variants')); ?></h2>
                            <p class="text-sm text-ink-400"><?php echo e(__('products.variants_hint')); ?></p>
                        </div>
                    </div>
                    <div class="edz-card__body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($variants_preview) === 0): ?>
                            <p class="text-sm text-ink-muted"><?php echo e(__('products.add_options_hint')); ?></p>
                        <?php else: ?>
                            <div class="mb-4 grid grid-cols-1 gap-3 rounded-lg border border-surface-border bg-surface-secondary/50 p-4 sm:grid-cols-2 lg:grid-cols-5">
                                <div class="edz-field">
                                    <label class="edz-field__label" for="apply-price"><?php echo e(__('products.price')); ?></label>
                                    <input id="apply-price" type="number" step="0.01" min="0" class="edz-input"
                                           wire:model="apply_all_price" placeholder="0.00">
                                </div>
                                <div class="edz-field">
                                    <label class="edz-field__label" for="apply-cost"><?php echo e(__('products.cost')); ?></label>
                                    <input id="apply-cost" type="number" step="0.01" min="0" class="edz-input"
                                           wire:model="apply_all_cost_price" placeholder="0.00">
                                </div>
                                <div class="edz-field">
                                    <label class="edz-field__label" for="apply-stock"><?php echo e(__('products.stock')); ?></label>
                                    <input id="apply-stock" type="number" min="0" class="edz-input"
                                           wire:model="apply_all_stock" placeholder="0">
                                </div>
                                <div class="edz-field">
                                    <label class="edz-field__label" for="apply-low-stock"><?php echo e(__('products.low_stock')); ?></label>
                                    <input id="apply-low-stock" type="number" min="0" class="edz-input"
                                           wire:model="apply_all_low_stock" placeholder="5">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" wire:click="applyAll" wire:loading.attr="disabled" class="edz-btn edz-btn--secondary w-full"><?php echo e(__('products.apply_to_all')); ?></button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                                            <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.variant')); ?></th>
                                            <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.variant_image')); ?></th>
                                            <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.price')); ?></th>
                                            <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.cost')); ?></th>
                                            <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.compare')); ?></th>
                                            <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.stock')); ?></th>
                                            <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.low_stock')); ?></th>
                                            <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.profit')); ?></th>
                                            <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.margin')); ?></th>
                                            <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.active')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $variants_preview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="border-b border-surface-border last:border-0">
                                                <td class="max-w-48 px-3 py-2 align-top text-xs font-medium text-ink-soft">
                                                    <?php echo e($variant['labels'] ?? $variant['name'] ?? '—'); ?>

                                                </td>
                                                <td class="px-3 py-2">
                                                    <div class="flex items-center gap-1.5">
                                                        <label class="group relative block h-10 w-10 cursor-pointer overflow-hidden rounded-md border border-surface-border bg-surface-secondary/60">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($variant['new_image'] ?? null)): ?>
                                                                <img src="<?php echo e($variant['new_image']->temporaryUrl()); ?>" alt="" class="h-full w-full object-cover">
                                                            <?php elseif(($variant['image'] ?? null)): ?>
                                                                <img src="<?php echo e(Storage::disk('public')->url($variant['image'])); ?>" alt="" class="h-full w-full object-cover">
                                                            <?php else: ?>
                                                                <span class="flex h-full w-full items-center justify-center text-ink-muted">
                                                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'camera','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'camera','class' => 'h-4 w-4']); ?>
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
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            <span class="pointer-events-none absolute inset-0 hidden items-center justify-center bg-surface-secondary/60 text-ink-muted group-hover:flex">
                                                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'camera','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'camera','class' => 'h-4 w-4']); ?>
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
                                                            <input type="file" accept="image/*" class="sr-only"
                                                                   wire:model="variants_preview.<?php echo e($index); ?>.new_image">
                                                        </label>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($variant['new_image'] ?? null) || ($variant['image'] ?? null)): ?>
                                                            <button type="button"
                                                                    wire:click="removeVariantImage(<?php echo e($index); ?>)"
                                                                    title="<?php echo e(__('products.remove_variant_image')); ?>"
                                                                    class="flex h-6 w-6 items-center justify-center rounded-full text-ink-muted transition hover:bg-danger-soft hover:text-danger-600">
                                                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'h-3 w-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'h-3 w-3']); ?>
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
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                           wire:model.blur="variants_preview.<?php echo e($index); ?>.price">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                           wire:model.blur="variants_preview.<?php echo e($index); ?>.cost_price">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                           wire:model.blur="variants_preview.<?php echo e($index); ?>.compare_price">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" min="0" class="edz-input min-w-20 px-2 py-1 text-xs"
                                                           wire:model.blur="variants_preview.<?php echo e($index); ?>.stock">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" min="0" class="edz-input min-w-20 px-2 py-1 text-xs"
                                                           wire:model.blur="variants_preview.<?php echo e($index); ?>.low_stock_threshold">
                                                </td>
                                                <?php
                                                    $vp = $variant['price'] ?? null;
                                                    $vc = $variant['cost_price'] ?? null;
                                                    $vProfit = ($vp !== null && $vp !== '' && $vc !== null && $vc !== '')
                                                        ? (float) $vp - (float) $vc
                                                        : null;
                                                    $vMargin = $vProfit !== null && (float) $vp > 0
                                                        ? round(($vProfit / (float) $vp) * 100, 1)
                                                        : null;
                                                ?>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-ink-soft">
                                                    <?php echo e($vProfit !== null ? number_format($vProfit, 2) : '—'); ?>

                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-ink-soft">
                                                    <?php echo e($vMargin !== null ? $vMargin.'%' : '—'); ?>

                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="checkbox" class="h-4 w-4 rounded border-surface-border text-brand-600"
                                                           wire:model="variants_preview.<?php echo e($index); ?>.is_active">
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\products\form\step-options.blade.php ENDPATH**/ ?>