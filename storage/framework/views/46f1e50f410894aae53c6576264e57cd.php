
<?php
    $isConfirm = ($picker ?? 'form') === 'confirm';

    $typeBinding = $isConfirm ? 'confirmPartnerType' : 'formPartnerType';
    $providerBinding = $isConfirm ? 'confirmProviderId' : 'form.shipping_provider_id';
    $riderBinding = $isConfirm ? 'confirmRiderId' : 'form.delivery_rider_id';
    $switchAction = $isConfirm ? 'switchConfirmPartner' : 'switchFormPartner';

    $activeValue = (string) ($this->{$typeBinding} ?? '');

    $providerOptions = collect($this->allProviders)
        ->map(fn ($p) => ['value' => 'p:' . $p['id'], 'label' => $p['name'], 'hint' => null, 'kind' => 'provider'])
        ->values()
        ->all();

    $riderOptions = collect($this->riderOptions)
        ->map(fn ($r) => ['value' => 'r:' . (string) ($r['value'] ?? $r['id'] ?? ''), 'label' => $r['label'] ?? $r['name'] ?? '', 'hint' => $r['hint'] ?? null, 'kind' => 'rider'])
        ->values()
        ->all();

    $hasProviders = $providerOptions !== [];
    $hasRiders    = $riderOptions !== [];
    $soloCompany  = count($providerOptions) === 1;

    // Livewire disables the picker while the office dropdown is loading. The
    // condition is hoisted OUT of the component tag on purpose: Blade fails to
    // compile an anonymous component whose attributes contain raw @if/@endif.
    $pickerDisabled = ! $isConfirm && (bool) ($this->loadingOffices ?? false);

    // Unified option list with optional delimiter when both groups exist.
    $unifiedOptions = array_merge(
        $providerOptions,
        ($hasProviders && $hasRiders)
            ? [['value' => '__delimiter__', 'label' => __('merchant_panel.partner_rider'), 'hint' => null, 'kind' => 'delimiter', 'is_divider' => true]]
            : [],
        $riderOptions,
    );
?>

<div class="space-y-3">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $hasProviders && ! $hasRiders): ?>
        
        <div class="rounded-xl border border-dashed border-surface-border bg-surface-secondary px-4 py-3">
            <p class="text-sm text-ink-muted"><?php echo e(__('merchant_panel.partner_empty_state')); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settingsStore = currentStore()): ?>
                <a href="<?php echo e(route('merchant.delivery', $settingsStore)); ?>" wire:navigate
                    class="mt-1.5 inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700">
                    <?php echo e(__('merchant_panel.partner_empty_cta')); ?>

                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-right','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right','class' => 'w-4 h-4']); ?>
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
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php else: ?>
        
        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => ''.e($typeBinding).'','wire:change' => ''.e($switchAction).'($event.target.value)','options' => $unifiedOptions,'optionValue' => 'value','optionLabel' => 'label','optionHint' => 'hint','optionDivider' => 'is_divider','placeholder' => ''.e(__('merchant_panel.select_company')).'','size' => 'sm','search' => true,'disabled' => $pickerDisabled,'class' => ''.e($soloCompany ? 'edz-company-select' : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => ''.e($typeBinding).'','wire:change' => ''.e($switchAction).'($event.target.value)','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($unifiedOptions),'option-value' => 'value','option-label' => 'label','option-hint' => 'hint','optionDivider' => 'is_divider','placeholder' => ''.e(__('merchant_panel.select_company')).'','size' => 'sm','search' => true,'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pickerDisabled),'class' => ''.e($soloCompany ? 'edz-company-select' : '').'']); ?>
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
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = [$providerBinding];
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
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\partner-picker.blade.php ENDPATH**/ ?>