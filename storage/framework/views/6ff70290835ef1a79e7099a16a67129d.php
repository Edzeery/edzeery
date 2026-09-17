<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['active' => false, 'icon' => null]));

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

foreach (array_filter((['active' => false, 'icon' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<a
    <?php echo e($attributes->merge([
        'class' =>
            'flex items-center px-4 py-2
                            rounded-md text-sm font-medium transition ' .
            ($active
                ? 'bg-brand text-white '
                : 'text-ink
                            hover:bg-neutral-secondary
                            dark:hover:bg-dark-secondary'),
    ])); ?>>

    <?php echo e($slot); ?>

</a>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\sidebar-link.blade.php ENDPATH**/ ?>