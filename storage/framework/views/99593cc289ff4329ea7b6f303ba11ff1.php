
    <div class="contents" @edz-modal-closed.window="$wire.set('showAssignModal', false)">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAssignModal): ?>
    <div @edz-modal-closed.window="$wire.set('showAssignModal', false)">
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'wire:key' => 'assign-modal-'.e($showAssignModal ? 'open' : 'closed').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isOpen' => true,'wire:key' => 'assign-modal-'.e($showAssignModal ? 'open' : 'closed').'']); ?>
        <form wire:submit="saveAssignments">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-ink"><?php echo e(__('merchant_panel.assign_products')); ?></h3>

                <div class="space-y-4">
                    <div class="edz-field">
                        <label class="edz-field__label" for="assign-agent"><?php echo e(__('merchant_panel.agent')); ?> *</label>
                        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'assignForm.membership_id','options' => $members,'optionValue' => 'id','optionLabel' => 'user.name','placeholder' => ''.e(__('merchant_panel.select_agent')).'','error' => $errors->first('assignForm.membership_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'assignForm.membership_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($members),'option-value' => 'id','option-label' => 'user.name','placeholder' => ''.e(__('merchant_panel.select_agent')).'','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('assignForm.membership_id'))]); ?>
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
                        <label class="edz-field__label" for="assign-search"><?php echo e(__('merchant_panel.search_products')); ?></label>
                        <?php if (isset($component)) { $__componentOriginal855390713d03eff0179f92852db7ddbf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal855390713d03eff0179f92852db7ddbf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.product-multi-picker','data' => ['options' => $this->searchAssignProducts,'selected' => $assignForm['product_ids'] ?? [],'selectedNames' => $assignProductNames,'toggle' => 'toggleAssignProduct','model' => 'productSearch','placeholder' => __('merchant_panel.search_products_to_add'),'emptyMessage' => __('merchant_panel.list_no_products_found')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.product-multi-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->searchAssignProducts),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($assignForm['product_ids'] ?? []),'selected-names' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($assignProductNames),'toggle' => 'toggleAssignProduct','model' => 'productSearch','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('merchant_panel.search_products_to_add')),'empty-message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('merchant_panel.list_no_products_found'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal855390713d03eff0179f92852db7ddbf)): ?>
<?php $attributes = $__attributesOriginal855390713d03eff0179f92852db7ddbf; ?>
<?php unset($__attributesOriginal855390713d03eff0179f92852db7ddbf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal855390713d03eff0179f92852db7ddbf)): ?>
<?php $component = $__componentOriginal855390713d03eff0179f92852db7ddbf; ?>
<?php unset($__componentOriginal855390713d03eff0179f92852db7ddbf); ?>
<?php endif; ?>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-border">
                        <button type="button" @click="$wire.set('showAssignModal', false)" class="edz-btn edz-btn--ghost">
                            <?php echo e(__('buttons.cancel')); ?>

                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check-circle','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','class' => 'w-4 h-4']); ?>
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
                            <?php echo e(__('merchant_panel.save')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </form>
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
    </div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\order-settings\partials\assignments-modal.blade.php ENDPATH**/ ?>