<?php
    // The inline carrier select merges active shipping companies and delivery
    // riders. Companies come first, then a hard divider (mirroring the modal
    // picker) so riders-only stores and mixed stores read the same group list.
    $carrierProviderOptions = collect($this->allProviders)
        ->map(fn ($p) => ['value' => (string) $p['id'], 'label' => $p['name'], 'hint' => null, 'kind' => 'provider'])
        ->values()
        ->all();

    $carrierRiderOptions = collect($this->riderOptions)
        ->map(fn ($r) => $r + ['kind' => 'rider'])
        ->values()
        ->all();

    $carrierSelectOptions = array_merge(
        $carrierProviderOptions,
        ($carrierRiderOptions !== [])
            ? [['value' => '__delimiter__', 'label' => __('merchant_panel.partner_rider'), 'hint' => null, 'kind' => 'delimiter', 'is_divider' => true]]
            : [],
        $carrierRiderOptions,
    );
?>

<div class="edz-inline-edit__edit" wire:key="<?php echo e($wireKeyPrefix); ?>-<?php echo e($orderId); ?>">
    
    <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'editingValue','options' => $carrierSelectOptions,'optionHint' => 'hint','optionDivider' => 'is_divider','size' => 'sm','search' => true,'value' => ''.e((string) ($this->editingValue ?? '')).'','placeholder' => ''.e(__('merchant_panel.shipping_provider')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'editingValue','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($carrierSelectOptions),'option-hint' => 'hint','optionDivider' => 'is_divider','size' => 'sm','search' => true,'value' => ''.e((string) ($this->editingValue ?? '')).'','placeholder' => ''.e(__('merchant_panel.shipping_provider')).'']); ?>
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
    <div class="edz-inline-edit__actions">
        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderProvider"
            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
            <span><?php echo e(__('buttons.save')); ?></span>
        </button>
        <button type="button" class="edz-inline-edit__cancel"
            @click="$wire.cancelOrderEdit()"><?php echo e(__('buttons.cancel')); ?></button>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\inline-carrier-select.blade.php ENDPATH**/ ?>