<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'domain',
    'status',
    'size' => 'md', // sm | md | lg
    'class' => '',
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
    'domain',
    'status',
    'size' => 'md', // sm | md | lg
    'class' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $result = \Edzeery\MyStatusKit\Facades\Status::for($domain, $status);
    $sizeClass = match($size) {
        'sm' => 'w-2 h-2',
        'lg' => 'w-4 h-4',
        default => 'w-3 h-3',
    };
?>

<span role="img" aria-label="<?php echo e($result->label()); ?>" <?php echo e($attributes->merge(['class' => "$sizeClass rounded-full inline-block"])); ?> style="background-color: <?php echo e($result->hex()); ?>;"></span>
<?php /**PATH C:\laragon\www\edzeery\resources\views\vendor\status-kit\components\status-dot.blade.php ENDPATH**/ ?>