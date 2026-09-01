<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'domain',
    'status',
    'storeId' => null,
    'set'   => null,
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
    'storeId' => null,
    'set'   => null,
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
    $statusKey = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $result = \App\Domains\Status\StatusResolver::resolve($domain, $statusKey, $storeId ?? currentStoreId());
?>

<span role="img" aria-label="<?php echo e($result->label); ?>"><?php echo $result->renderIcon($set, $class ? $class : null); ?></span>
<?php /**PATH C:\laragon\www\edzeery\resources\views\vendor\status-kit\components\status-icon.blade.php ENDPATH**/ ?>