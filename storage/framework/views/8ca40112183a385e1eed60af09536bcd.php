<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'tone' => 'neutral',
    'dot' => false,
    'sm' => false,
    'lg' => false,
]));

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

foreach (array_filter(([
    'tone' => 'neutral',
    'dot' => false,
    'sm' => false,
    'lg' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = ['edz-badge', "edz-badge--{$tone}"];
    if ($dot) $classes[] = 'edz-badge--dot';
    if ($sm) $classes[] = 'edz-badge--sm';
    if ($lg) $classes[] = 'edz-badge--lg';
?>

<span <?php echo e($attributes->merge(['class' => implode(' ', $classes)])); ?>>
    <?php echo e($slot); ?>

</span>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/edz/badge.blade.php ENDPATH**/ ?>