<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['disabled' => false]));

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

foreach (array_filter((['disabled' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<input <?php if($disabled): echo 'disabled'; endif; ?>
    <?php echo e($attributes->merge([
        'class' =>
            'w-full px-4 py-2.5 rounded-xl border border-neutral-border dark:border-dark-border
             bg-neutral-surface dark:bg-dark-surface text-ink text-sm
             placeholder:text-ink-soft placeholder:font-normal
             focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500
             disabled:opacity-60 disabled:cursor-not-allowed
             transition-all duration-200',
    ])); ?> />
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/text-input.blade.php ENDPATH**/ ?>