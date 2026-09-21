<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'domain',
    'status',
    'value'  => 100,
    'size'   => 'md', // sm | md | lg
    'showLabel' => true,
    'class'  => '',
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
    'value'  => 100,
    'size'   => 'md', // sm | md | lg
    'showLabel' => true,
    'class'  => '',
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
        'sm' => 'h-1',
        'lg' => 'h-4',
        default => 'h-2',
    };
    $clampedValue = max(0, min(100, $value));
?>

<div
    role="progressbar"
    aria-valuenow="<?php echo e($clampedValue); ?>"
    aria-valuemin="0"
    aria-valuemax="100"
    aria-label="<?php echo e($result->label()); ?>"
    <?php echo e($attributes->merge(['class' => trim("w-full rounded-full overflow-hidden $sizeClass $class")])); ?>

    style="background-color: <?php echo e($result->hex()); ?>20;"
>
    <div
        class="<?php echo e($sizeClass); ?> rounded-full transition-all duration-300"
        style="width: <?php echo e($clampedValue); ?>%; background-color: <?php echo e($result->hex()); ?>;"
    ></div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLabel): ?>
        <span class="sr-only"><?php echo e($result->label()); ?>: <?php echo e($clampedValue); ?>%</span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\edzeery\vendor\edzeery\mystatuskit\resources\views\components\status-progress.blade.php ENDPATH**/ ?>