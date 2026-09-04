<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['size' => 'w-4 h-4', 'show' => null]));

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

foreach (array_filter((['size' => 'w-4 h-4', 'show' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $attrs = $show !== null
        ? $attributes->merge(['class' => 'edz-spinner ' . $size, 'x-show' => $show, 'x-cloak' => ''])
        : $attributes->merge(['class' => 'edz-spinner ' . $size, 'x-cloak' => '', 'wire:loading' => '']);
?>

<svg <?php echo e($attrs); ?> viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
    <circle cx="12" cy="12" r="9" stroke-dasharray="42.41 56.55" />
</svg><?php /**PATH C:\laragon\www\edzeery\resources\views/components/edz/spinner.blade.php ENDPATH**/ ?>