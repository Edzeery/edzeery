<button <?php echo e($attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-600 text-white font-semibold text-sm rounded-xl
               hover:bg-brand-700 active:bg-brand-800 shadow-sm shadow-brand-600/20 transition-all duration-200
               disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:ring-offset-2 dark:focus:ring-offset-gray-900'
])); ?>>
    <?php echo e($slot); ?>

</button>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\primary-button.blade.php ENDPATH**/ ?>