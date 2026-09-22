
<div x-show="delivery === 'stopdesk'" x-cloak class="mt-4">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(blank($this->form['delivery_rider_id'] ?? null) && filled($this->form['shipping_provider_id'] ?? null)): ?>
    <div class="flex items-center gap-2">
        <div class="flex-1">
            <label class="edz-label"><?php echo e(__('merchant_panel.office')); ?></label>
            <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'form.stopdesk_point_id','wire:change' => 'onFormOfficePicked','options' => $this->formOffices,'optionValue' => 'value','optionLabel' => 'label','optionHint' => 'hint','optionCode' => 'code','placeholder' => ''.e(__('merchant_panel.select_office')).'','size' => 'sm','search' => true,'disabled' => $loadingOffices,'lazy' => true,'source' => 'loadFormOfficesLazy','scope' => ($this->form['shipping_provider_id'] ?? '') . '|' . ($this->form['state_id'] ?? '') . '|' . ($this->form['city_id'] ?? '') . '|' . $this->formOfficesVersion]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'form.stopdesk_point_id','wire:change' => 'onFormOfficePicked','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->formOffices),'option-value' => 'value','option-label' => 'label','option-hint' => 'hint','option-code' => 'code','placeholder' => ''.e(__('merchant_panel.select_office')).'','size' => 'sm','search' => true,'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($loadingOffices),'lazy' => true,'source' => 'loadFormOfficesLazy','scope' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($this->form['shipping_provider_id'] ?? '') . '|' . ($this->form['state_id'] ?? '') . '|' . ($this->form['city_id'] ?? '') . '|' . $this->formOfficesVersion)]); ?>
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
        <button type="button" wire:click="refreshFormOffices"
            wire:loading.attr="disabled"
            class="edz-btn edz-btn--ghost edz-btn--sm mt-5 shrink-0 disabled:opacity-50 disabled:pointer-events-none <?php echo e($loadingOffices ? 'opacity-50 pointer-events-none' : ''); ?>"
            aria-label="<?php echo e(__('merchant_panel.refresh_offices')); ?>">
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-path','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-path','class' => 'w-4 h-4']); ?>
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
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($this->form['state_id'])): ?>
        <p class="text-xs text-ink-muted mt-1"><?php echo e(__('storefront.select_state_for_desks')); ?></p>
    <?php elseif(empty($this->form['city_id'])): ?>
        <p class="text-xs text-ink-muted mt-1"><?php echo e(__('storefront.select_city_for_desks')); ?></p>
    <?php elseif(! $this->formHasOffices): ?>
        <p class="text-xs text-warning-500 mt-1"><?php echo e(__('merchant_panel.office_none_for_destination')); ?></p>
    <?php else: ?>
        <p class="text-xs text-ink-muted mt-1"><?php echo e(__('merchant_panel.office_hint')); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.stopdesk_point_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\order-office-field.blade.php ENDPATH**/ ?>