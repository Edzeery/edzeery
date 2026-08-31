<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'width' => '260px',
    'trigger' => null,
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
    'width' => '260px',
    'trigger' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $alignClass = $alignment;
?>

<div class="edz-dropdown" x-data="{
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; }
}" @click.away="close()">
    <button @click="toggle()" type="button" class="edz-dropdown__trigger">
        <?php echo e($trigger); ?>

    </button>

    <div
        x-show="open"
        x-transition:enter="edz-dropdown-enter"
        x-transition:enter-start="edz-dropdown-enter-start"
        x-transition:enter-end="edz-dropdown-enter-end"
        x-transition:leave="edz-dropdown-leave"
        x-transition:leave-start="edz-dropdown-leave-start"
        x-transition:leave-end="edz-dropdown-leave-end"
        class="edz-dropdown__panel <?php echo e($alignClass); ?>"
        style="width: <?php echo e($width); ?>; display: none;"
    >
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\edz\dropdown.blade.php ENDPATH**/ ?>