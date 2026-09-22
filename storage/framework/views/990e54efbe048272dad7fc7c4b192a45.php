<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'weights' => '300;400;500;600;700',
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
    'weights' => '300;400;500;600;700',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $fontsUrl = 'https://fonts.googleapis.com/css2?family=Inter:wght@' . $weights . '&family=IBM+Plex+Sans+Arabic:wght@' . $weights . '&display=swap';
?>


<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="<?php echo e($fontsUrl); ?>"
    media="print" onload="this.media='all'">
<noscript>
    <link rel="stylesheet" href="<?php echo e($fontsUrl); ?>">
</noscript><?php /**PATH C:\laragon\www\edzeery\resources\views\components\edz\fonts.blade.php ENDPATH**/ ?>