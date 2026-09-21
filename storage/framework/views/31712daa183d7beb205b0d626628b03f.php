<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'align' => 'right',
    'width' => '80',
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
    'align' => 'right',
    'width' => '80',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$widthClass = match ($width) {
    '72' => 'w-72',
    '80' => 'w-80',
    '96' => 'w-96',
    default => "w-{$width}",
};
?>

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.window="open = false">

    
    <div @click="open = ! open">
        <?php echo e($trigger); ?>

    </div>

    
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        class="absolute z-50 mt-2 <?php echo e($widthClass); ?> <?php echo e($alignmentClasses); ?> rounded-xl
               bg-white dark:bg-gray-800
               border border-gray-200 dark:border-gray-700
               shadow-xl shadow-black/5
               overflow-hidden flex flex-col"
        style="display: none;"
    >
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\storefront-dropdown.blade.php ENDPATH**/ ?>