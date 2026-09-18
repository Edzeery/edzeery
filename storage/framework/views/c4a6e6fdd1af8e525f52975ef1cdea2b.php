<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => null,
    'label' => null,
    'hint' => null,
    'size' => 'md',
    'checked' => null,
    'value' => null,
    'disabled' => false,
    'labelClass' => '',
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
    'id' => null,
    'label' => null,
    'hint' => null,
    'size' => 'md',
    'checked' => null,
    'value' => null,
    'disabled' => false,
    'labelClass' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $uid = $id ?? 'edz-checkbox-' . \Illuminate\Support\Str::random(8);
    $sizeClass = $size === 'sm' ? 'w-4 h-4' : 'w-[18px] h-[18px]';
    $extraAttrs = $attributes->merge(['class' => "edz-checkbox {$sizeClass}"]);
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label || $hint): ?>
    <label for="<?php echo e($uid); ?>"
        class="inline-flex items-center gap-3 cursor-pointer select-none <?php echo e($disabled ? 'opacity-60 cursor-not-allowed' : ''); ?> <?php echo e($labelClass); ?>">
        <input type="checkbox"
            id="<?php echo e($uid); ?>"
            class="<?php echo e($extraAttrs->get('class')); ?>"
            <?php echo e($checked === true ? 'checked' : ''); ?>

            <?php echo e($disabled ? 'disabled' : ''); ?>

            <?php if($value !== null): ?> value="<?php echo e($value); ?>" <?php endif; ?>
            <?php echo e($extraAttrs->whereDoesntStartWith('wire:')->except(['class', 'id'])); ?>

            <?php echo e($attributes->wire('model')); ?>

            <?php echo e($attributes->wire('click')); ?> />
        <span class="min-w-0">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
                <span class="block text-sm font-medium text-ink"><?php echo e($label); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hint): ?>
                <span class="block text-xs text-ink-muted mt-0.5"><?php echo e($hint); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </span>
    </label>
<?php else: ?>
    <input type="checkbox"
        id="<?php echo e($uid); ?>"
        class="<?php echo e($extraAttrs->get('class')); ?>"
        <?php echo e($checked === true ? 'checked' : ''); ?>

        <?php echo e($disabled ? 'disabled' : ''); ?>

        <?php if($value !== null): ?> value="<?php echo e($value); ?>" <?php endif; ?>
        <?php echo e($extraAttrs->whereDoesntStartWith('wire:')->except(['class', 'id'])); ?>

        <?php echo e($attributes->wire('model')); ?>

        <?php echo e($attributes->wire('click')); ?> />
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/edz/checkbox.blade.php ENDPATH**/ ?>