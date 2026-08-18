<button <?php echo e($attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center px-4 py-2 bg-brand-50 text-brand-500 dark:text-white font-semibold rounded-lg hover:bg-brand-600 transition'
])); ?>>
    <?php echo e($slot); ?>

</button>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/primary-button.blade.php ENDPATH**/ ?>