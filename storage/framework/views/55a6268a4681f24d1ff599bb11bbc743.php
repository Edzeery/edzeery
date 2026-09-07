<div class="invoice-container bg-white"
    style="font-family: <?php echo e($invoice->template->settings['font-family'] ?? 'Arial'); ?>">

    
    <div class="invoice-content">
        <?php echo e($slot); ?>

    </div>
</div>

<style>
    @media print {
        body {
            margin: 0;
            padding: 0;
        }

        .invoice-container {
            width: 100%;
            max-width: none;
        }

        .invoice-container {
            @apply max-w-4xl mz-auto p-0;
        }
    }
</style>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\invoices\invoice-template.blade.php ENDPATH**/ ?>