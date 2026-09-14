<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'status',
    'domain' => null,
    'storeId' => null,
    'mode' => null,
    'iconOnly' => false,
    'dark' => true,
    'size' => 'sm',
    'label' => null,
    'set' => null,
    'iconClass' => null,
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
    'status',
    'domain' => null,
    'storeId' => null,
    'mode' => null,
    'iconOnly' => false,
    'dark' => true,
    'size' => 'sm',
    'label' => null,
    'set' => null,
    'iconClass' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
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

    $mode = $mode ?: ($iconOnly ? 'icon' : $resolved->displayMode);

    $sizeClass = match ($size) {
        'xs' => 'px-1.5 py-0.5 text-[10px]',
        'md' => 'px-3 py-1 text-sm',
        default => 'px-2.5 py-0.5 text-xs',
    };

    $iconSize = match ($size) {
        'xs' => 'w-3 h-3',
        'md' => 'w-5 h-5',
        default => 'w-4 h-4',
    };

    $text = $label ?? $resolved->label;
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'icon'): ?>
    <span <?php echo e($attributes->merge([
        'class' => 'inline-flex items-center justify-center',
        'title' => $text,
    ])); ?>>
        <?php echo $resolved->renderIcon($set, $iconClass ?? $iconSize); ?>

    </span>
<?php elseif($mode === 'dot'): ?>
    <span <?php echo e($attributes->merge(['class' => 'inline-flex items-center gap-1.5'])); ?> title="<?php echo e($text); ?>">
        <span class="inline-block rounded-full <?php echo e($iconSize); ?>" style="background-color: <?php echo e($resolved->hex); ?>;"></span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $iconOnly): ?>
            <span class="text-xs font-medium"><?php echo e($text); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </span>
<?php elseif($mode === 'text'): ?>
    <span <?php echo e($attributes->merge(['class' => 'text-xs font-medium '.trim($resolved->classes($dark))])); ?>>
        <?php echo e($text); ?>

    </span>
<?php else: ?>
    <span <?php echo e($attributes->merge([
        'class' => 'status-badge inline-flex items-center gap-1 rounded-full font-medium '.$sizeClass.' '.trim($resolved->classes($dark)),
        'title' => $iconOnly ? $text : null,
    ])); ?>>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resolved->icon && ! $iconOnly): ?>
            <?php echo $resolved->renderIcon($set, $iconSize); ?>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $iconOnly): ?>
            <span><?php echo e($text); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\components\status.blade.php ENDPATH**/ ?>