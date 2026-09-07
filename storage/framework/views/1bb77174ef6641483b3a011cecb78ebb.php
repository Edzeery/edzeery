<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['role', 'dark' => false]));

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

foreach (array_filter((['role', 'dark' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $resolved = $role instanceof \BackedEnum
        ? $role
        : (\App\Enums\Store\StoreRoleEnum::tryFrom((string) $role)
            ?? \App\Enums\Platform\UserRoleEnum::tryFrom((string) $role));
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resolved): ?>
    <span <?php echo e($attributes->merge(['class' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-sm '.trim($resolved->color($dark))])); ?>>
        <?php echo $resolved->icon('heroicon', 'w-4 h-4'); ?>

        <span><?php echo e($resolved->label()); ?></span>
    </span>
<?php else: ?>
    <span <?php echo e($attributes); ?>><?php echo e($role); ?></span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\components\role-badge.blade.php ENDPATH**/ ?>