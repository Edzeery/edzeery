<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'domain',
    'status',
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
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $result = \Edzeery\MyStatusKit\Facades\Status::for($domain, $status instanceof \BackedEnum ? $status->value : (string) $status);
    $classes = 'status-badge inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium '
        . $result->color(true, 'tailwind');
?>

<span role="status" aria-label="<?php echo e($result->label()); ?>"
      <?php echo e($attributes->merge(['class' => $classes])); ?>>
    <span><?php echo e($result->label()); ?></span>
</span>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\merchant\status.blade.php ENDPATH**/ ?>