<?php

use App\Enums\Store\ProductOptionInputType;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use Illuminate\Validation\Rule;

?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title"><?php echo e(__('product_options.title')); ?></h1>
            <p class="edz-page-head__subtitle"><?php echo e(__('product_options.subtitle', ['store' => currentStore()?->name])); ?></p>
        </div>
        <div class="flex items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canCreate()): ?>
                <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="openCreate"><?php echo e(__('product_options.new_option')); ?></button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($creating || $editingId): ?>
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title"><?php echo e($editingId ? __('product_options.edit_option') : __('product_options.new_option')); ?></h2>
                    <p class="text-sm text-ink-400"><?php echo e($editingId ? __('product_options.edit_option_desc') : __('product_options.new_option_desc')); ?></p>
                </div>
            </div>

            <form wire:submit="save" class="space-y-4 p-4" x-data="edzDirty()">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="option-name"><?php echo e(__('product_options.name')); ?></label>
                        <input id="option-name" type="text" class="edz-input <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               wire:model="name" placeholder="<?php echo e(__('product_options.option_name')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
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
                        <label class="mb-1 block text-sm font-medium text-ink" for="option-type"><?php echo e(__('product_options.input_type')); ?></label>
                        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'type','options' => $this->typeOptions,'placeholder' => ''.e(__('product_options.select_type')).'','error' => $errors->first('type')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'type','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->typeOptions),'placeholder' => ''.e(__('product_options.select_type')).'','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('type'))]); ?>
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
                <h2 class="edz-card__title"><?php echo e(__('product_options.list_title')); ?></h2>
                <p class="text-sm text-ink-400"><?php echo e(__('product_options.list_subtitle')); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 border-b border-surface-border p-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" class="edz-input" placeholder="<?php echo e(__('product_options.search_placeholder')); ?>"
                       wire:model.live.debounce.300ms="search">
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($selected)): ?>
            <div class="flex flex-wrap items-center gap-2 border-b border-surface-border bg-brand-surface px-4 py-3">
                <span class="text-sm font-medium text-ink"><?php echo e(__('general.selected_count', ['count' => count($selected)])); ?></span>
                <button type="button" class="edz-btn edz-btn--danger edz-btn--sm"
                        x-data
                        data-confirm-count="<?php echo e(count($selected)); ?>"
                        @click.prevent="(async () => { if (await EdzSwal.confirmBulkDelete(Number($el.dataset.confirmCount))) await $wire.deleteSelected() })()"><?php echo e(__('buttons.delete')); ?></button>
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
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('product_options.name')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('product_options.type')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('product_options.values')); ?></th>
                        <th class="px-4 py-3 text-end font-semibold"><?php echo e(__('general.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selected" value="<?php echo e($option->id); ?>" aria-label="Select <?php echo e($option->name); ?>">
                            </td>
                            <td class="px-4 py-3 font-medium text-ink"><?php echo e($option->name); ?></td>
                            <td class="px-4 py-3 text-xs text-ink-soft"><?php echo e($option->type?->value); ?></td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-surface-secondary px-2.5 py-0.5 text-xs font-semibold text-ink-soft"><?php echo e($option->values_count); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            wire:click="toggleActive('<?php echo e($option->id); ?>')">
                                        <?php echo e($activeOptionId === $option->id ? __('product_options.hide_values') : __('product_options.show_values')); ?>

                                    </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canUpdate()): ?>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="beginEdit('<?php echo e($option->id); ?>')"><?php echo e(__('buttons.edit')); ?></button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDelete()): ?>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                x-data
                                                data-delete-name="<?php echo e($option->name); ?>"
                                                data-delete-id="<?php echo e($option->id); ?>"
                                                @click.prevent="(async () => { if (await EdzSwal.confirmDelete($el.dataset.deleteName)) await $wire.delete(Number($el.dataset.deleteId)) })()"
                                                ><?php echo e(__('buttons.delete')); ?></button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeOptionId === $option->id): ?>
                            <tr class="bg-surface-secondary/40">
                                <td colspan="5" class="px-4 py-4">
                                    <?php $values = $this->optionValues($option); ?>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(strtolower($option->name) === 'size' && $this->canUpdate()): ?>
                                            <button type="button" class="edz-btn edz-btn--secondary edz-btn--sm"
                                                    wire:click="generateSizes('<?php echo e($option->id); ?>')"><?php echo e(__('product_options.generate_sizes')); ?></button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canUpdate()): ?>
                                            <form wire:submit="addValue('<?php echo e($option->id); ?>')" class="flex items-center gap-2">
                                                <input type="text" class="edz-input edz-input--sm" placeholder="<?php echo e(__('product_options.add_value_placeholder')); ?>"
                                                       wire:model="newValue">
                                                <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm"><?php echo e(__('buttons.add')); ?></button>
                                            </form>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                            <span class="inline-flex items-center gap-2 rounded-full border border-surface-border bg-surface px-3 py-1 text-sm text-ink">
                                                <?php echo e($value->value); ?>

                                                <span class="text-xs text-ink-muted"><?php echo e(trans_choice('product_options.variant_count', $value->variants_count, ['count' => $value->variants_count])); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDelete() && ! $value->variants()->exists()): ?>
                                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--xs text-danger-600 hover:text-danger-700"
                                                            x-data
                                                            data-delete-name="<?php echo e($value->value); ?>"
                                                            data-delete-id="<?php echo e($value->id); ?>"
                                                            @click.prevent="(async () => { if (await EdzSwal.confirmDelete($el.dataset.deleteName)) await $wire.deleteValue(Number($el.dataset.deleteId)) })()"
                                                            ><?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-3 h-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?></button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                            <span class="text-sm text-ink-muted"><?php echo e(__('product_options.no_values')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft"><?php echo e(__('product_options.no_options')); ?></p>
                                <p class="mt-1 text-sm text-ink-muted"><?php echo e(__('product_options.try_adjusting')); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->options->hasPages()): ?>
            <div class="border-t border-surface-border px-4 py-3">
                <?php echo e($this->options->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\options\index.blade.php ENDPATH**/ ?>