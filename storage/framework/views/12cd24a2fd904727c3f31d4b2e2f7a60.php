<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status', 'dark' => true, 'domain' => null, 'set' => null, 'iconOnly' => false, 'storeId' => null]));

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

foreach (array_filter((['status', 'dark' => true, 'domain' => null, 'set' => null, 'iconOnly' => false, 'storeId' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $resolved = $status instanceof \App\Domains\Status\Support\ResolvedStatus
        ? $status
        : ($status instanceof \BackedEnum
            ? $status->resolved($storeId)
            : \App\Domains\Status\StatusResolver::resolve($domain, (string) $status, $storeId));
?>

<span <?php echo e($attributes->merge([
    'class' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-sm '.trim($resolved->classes($dark)),
    'title' => $iconOnly ? $resolved->label : null,
])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resolved->icon): ?>
        <?php echo $resolved->renderIcon($set, 'w-4 h-4'); ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $iconOnly): ?>
        <span><?php echo e($resolved->label); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</span><?php /**PATH C:\laragon\www\edzeery\resources\views\components\status-badge.blade.php ENDPATH**/ ?>