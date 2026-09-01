<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['invoice', 'forPdf' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['invoice', 'forPdf' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($invoice->template->slug):
    case ('modern-minimalist'): ?>
        <?php if (isset($component)) { $__componentOriginal9033e9ae7a1b59af4e31f3f5b9aafe81 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9033e9ae7a1b59af4e31f3f5b9aafe81 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.invoices.templates.modern-minimalist','data' => ['invoice' => $invoice,'forPdf' => $forPdf]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('invoices.templates.modern-minimalist'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invoice' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice),'forPdf' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($forPdf)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9033e9ae7a1b59af4e31f3f5b9aafe81)): ?>
<?php $attributes = $__attributesOriginal9033e9ae7a1b59af4e31f3f5b9aafe81; ?>
<?php unset($__attributesOriginal9033e9ae7a1b59af4e31f3f5b9aafe81); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9033e9ae7a1b59af4e31f3f5b9aafe81)): ?>
<?php $component = $__componentOriginal9033e9ae7a1b59af4e31f3f5b9aafe81; ?>
<?php unset($__componentOriginal9033e9ae7a1b59af4e31f3f5b9aafe81); ?>
<?php endif; ?>
        <?php break; ?>
    <?php case ('classic-business'): ?>
        <?php if (isset($component)) { $__componentOriginald9d6ef32165c59b32146f94c2e81fb17 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald9d6ef32165c59b32146f94c2e81fb17 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.invoices.templates.classic-business','data' => ['invoice' => $invoice,'forPdf' => $forPdf]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('invoices.templates.classic-business'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invoice' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice),'forPdf' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($forPdf)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald9d6ef32165c59b32146f94c2e81fb17)): ?>
<?php $attributes = $__attributesOriginald9d6ef32165c59b32146f94c2e81fb17; ?>
<?php unset($__attributesOriginald9d6ef32165c59b32146f94c2e81fb17); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald9d6ef32165c59b32146f94c2e81fb17)): ?>
<?php $component = $__componentOriginald9d6ef32165c59b32146f94c2e81fb17; ?>
<?php unset($__componentOriginald9d6ef32165c59b32146f94c2e81fb17); ?>
<?php endif; ?>
        <?php break; ?>
    <?php case ('creative-agency'): ?>
        <?php if (isset($component)) { $__componentOriginal13e837d27238472dbfa1e8ef3882ed0b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal13e837d27238472dbfa1e8ef3882ed0b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.invoices.templates.creative-agency','data' => ['invoice' => $invoice,'forPdf' => $forPdf]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('invoices.templates.creative-agency'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invoice' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice),'forPdf' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($forPdf)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal13e837d27238472dbfa1e8ef3882ed0b)): ?>
<?php $attributes = $__attributesOriginal13e837d27238472dbfa1e8ef3882ed0b; ?>
<?php unset($__attributesOriginal13e837d27238472dbfa1e8ef3882ed0b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal13e837d27238472dbfa1e8ef3882ed0b)): ?>
<?php $component = $__componentOriginal13e837d27238472dbfa1e8ef3882ed0b; ?>
<?php unset($__componentOriginal13e837d27238472dbfa1e8ef3882ed0b); ?>
<?php endif; ?>
        <?php break; ?>

    <?php case ('corporate-blue'): ?>
        
        <?php if (isset($component)) { $__componentOriginald9d6ef32165c59b32146f94c2e81fb17 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald9d6ef32165c59b32146f94c2e81fb17 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.invoices.templates.classic-business','data' => ['invoice' => $invoice,'forPdf' => $forPdf]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('invoices.templates.classic-business'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invoice' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice),'forPdf' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($forPdf)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald9d6ef32165c59b32146f94c2e81fb17)): ?>
<?php $attributes = $__attributesOriginald9d6ef32165c59b32146f94c2e81fb17; ?>
<?php unset($__attributesOriginald9d6ef32165c59b32146f94c2e81fb17); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald9d6ef32165c59b32146f94c2e81fb17)): ?>
<?php $component = $__componentOriginald9d6ef32165c59b32146f94c2e81fb17; ?>
<?php unset($__componentOriginald9d6ef32165c59b32146f94c2e81fb17); ?>
<?php endif; ?>
        <?php break; ?>

    <?php default: ?>
        <?php if (isset($component)) { $__componentOriginal9033e9ae7a1b59af4e31f3f5b9aafe81 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9033e9ae7a1b59af4e31f3f5b9aafe81 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.invoices.templates.modern-minimalist','data' => ['invoice' => $invoice,'forPdf' => $forPdf]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('invoices.templates.modern-minimalist'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invoice' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice),'forPdf' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($forPdf)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9033e9ae7a1b59af4e31f3f5b9aafe81)): ?>
<?php $attributes = $__attributesOriginal9033e9ae7a1b59af4e31f3f5b9aafe81; ?>
<?php unset($__attributesOriginal9033e9ae7a1b59af4e31f3f5b9aafe81); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9033e9ae7a1b59af4e31f3f5b9aafe81)): ?>
<?php $component = $__componentOriginal9033e9ae7a1b59af4e31f3f5b9aafe81; ?>
<?php unset($__componentOriginal9033e9ae7a1b59af4e31f3f5b9aafe81); ?>
<?php endif; ?>
<?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\invoices\invoice-renderer.blade.php ENDPATH**/ ?>