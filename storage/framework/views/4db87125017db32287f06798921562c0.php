<button <?php echo e($attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center px-4
    py-2 bg-surface-secondary
    text-ink
    font-semibold hover:bg-surface-tertiary transition'
])); ?>>
    <?php echo e($slot); ?>

</button>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\secondary-button.blade.php ENDPATH**/ ?>