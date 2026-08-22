<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['invoice', 'forPdf' => false]));

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

foreach (array_filter((['invoice', 'forPdf' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $primaryColor = $invoice->template->settings['primary_color'] ?? '#1e3a8a';
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($forPdf): ?>

<div style="font-family: 'DejaVu Sans', sans-serif; font-size: 14px; color: #1f2937; padding: 40px;">

    
    <table style="width: 100%; margin-bottom: 30px; border-bottom: 4px solid <?php echo e($primaryColor); ?>; padding-bottom: 20px;">
        <tr>
            <td style="vertical-align: middle;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_logo): ?>
                    <img src="<?php echo e(storage_path('app/public/' . $invoice->company_logo)); ?>"
                         alt="<?php echo e($invoice->company_name); ?>"
                         style="height: 60px; margin-bottom: 10px;">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div style="font-size: 24px; font-weight: bold; color: <?php echo e($primaryColor); ?>;">
                    <?php echo e($invoice->company_name); ?>

                </div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div style="font-size: 40px; font-weight: bold; color: <?php echo e($primaryColor); ?>; margin-bottom: 8px;">
                    INVOICE
                </div>
                <div style="font-size: 18px; font-weight: 600;">
                    <?php echo e($invoice->invoice_number); ?>

                </div>
            </td>
        </tr>
    </table>

    
    <table style="width: 100%; margin-bottom: 30px;">
        <tr>
            
            <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; color: <?php echo e($primaryColor); ?>;">
                        From
                    </div>
                    <div style="font-size: 12px; color: #1f2937;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_address): ?>
                            <p style="margin-bottom: 2px;"><?php echo e($invoice->company_address); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_email): ?>
                            <p style="margin-bottom: 2px;"><?php echo e($invoice->company_email); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_phone): ?>
                            <p style="margin-bottom: 2px;"><?php echo e($invoice->company_phone); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div>
                    <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; color: <?php echo e($primaryColor); ?>;">
                        Bill To
                    </div>
                    <div style="font-size: 12px; color: #1f2937;">
                        <p style="font-weight: bold; margin-bottom: 2px;"><?php echo e($invoice->client_name); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_address): ?>
                            <p style="margin-bottom: 2px;"><?php echo e($invoice->client_address); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_email): ?>
                            <p style="margin-bottom: 2px;"><?php echo e($invoice->client_email); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_phone): ?>
                            <p style="margin-bottom: 2px;"><?php echo e($invoice->client_phone); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </td>

            
            <td style="width: 50%; vertical-align: top;">
                <table style="width: 100%; font-size: 12px;">
                    <tr style="border-bottom: 1px solid #d1d5db;">
                        <td style="padding: 8px 0; font-weight: 600; color: <?php echo e($primaryColor); ?>;">
                            Invoice Number:
                        </td>
                        <td style="padding: 8px 0; text-align: right;"><?php echo e($invoice->invoice_number); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #d1d5db;">
                        <td style="padding: 8px 0; font-weight: 600; color: <?php echo e($primaryColor); ?>;">
                            Invoice Date:
                        </td>
                        <td style="padding: 8px 0; text-align: right;"><?php echo e($invoice->invoice_date->format('F d, Y')); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #d1d5db;">
                        <td style="padding: 8px 0; font-weight: 600; color: <?php echo e($primaryColor); ?>;">
                            Due Date:
                        </td>
                        <td style="padding: 8px 0; text-align: right;"><?php echo e($invoice->due_date->format('F d, Y')); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: 600; color: <?php echo e($primaryColor); ?>;">
                            Status:
                        </td>
                        <td style="padding: 8px 0; text-align: right;">
                            <span style="background-color: <?php echo e($primaryColor); ?>20; color: <?php echo e($primaryColor); ?>; padding: 2px 8px; font-size: 10px; font-weight: 600;">
                                <?php echo e(ucfirst($invoice->status)); ?>

                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    
    <table style="width: 100%; margin-bottom: 30px; border-collapse: collapse;">
        <thead>
            <tr style="background-color: <?php echo e($primaryColor); ?>; color: #ffffff;">
                <th style="text-align: left; padding: 10px 12px; font-weight: 600; font-size: 12px; text-transform: uppercase;">
                    Description
                </th>
                <th style="text-align: center; padding: 10px 12px; font-weight: 600; font-size: 12px; text-transform: uppercase; width: 60px;">
                    Qty
                </th>
                <th style="text-align: right; padding: 10px 12px; font-weight: 600; font-size: 12px; text-transform: uppercase; width: 100px;">
                    Rate
                </th>
                <th style="text-align: right; padding: 10px 12px; font-weight: 600; font-size: 12px; text-transform: uppercase; width: 110px;">
                    Amount
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr style="background-color: <?php echo e($loop->even ? '#f9fafb' : '#ffffff'); ?>;">
                    <td style="padding: 10px 12px; color: #1f2937;"><?php echo e($item->description); ?></td>
                    <td style="padding: 10px 12px; text-align: center; color: #4b5563;"><?php echo e($item->quantity); ?></td>
                    <td style="padding: 10px 12px; text-align: right; color: #4b5563;">
                        $<?php echo e(number_format($item->unit_price, 2)); ?>

                    </td>
                    <td style="padding: 10px 12px; text-align: right; font-weight: 600; color: #1f2937;">
                        $<?php echo e(number_format($item->total, 2)); ?>

                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    
    <table style="width: 100%; margin-bottom: 30px;">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%;">
                <table style="width: 100%; font-size: 12px;">
                    <tr style="border-bottom: 1px solid #d1d5db;">
                        <td style="padding: 8px 0; color: #374151;">Subtotal:</td>
                        <td style="padding: 8px 0; text-align: right; font-weight: 600;">
                            $<?php echo e(number_format($invoice->subtotal, 2)); ?>

                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #d1d5db;">
                        <td style="padding: 8px 0; color: #374151;">Tax (<?php echo e($invoice->tax_rate); ?>%):</td>
                        <td style="padding: 8px 0; text-align: right; font-weight: 600;">
                            $<?php echo e(number_format($invoice->tax_amount, 2)); ?>

                        </td>
                    </tr>
                    <tr style="background-color: <?php echo e($primaryColor); ?>; color: #ffffff;">
                        <td style="padding: 10px 12px; font-size: 16px; font-weight: bold;">TOTAL:</td>
                        <td style="padding: 10px 12px; text-align: right; font-size: 18px; font-weight: bold;">
                            $<?php echo e(number_format($invoice->total, 2)); ?>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    
    <table style="width: 100%;">
        <tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes): ?>
                <td style="width: 48%; vertical-align: top; border: 2px solid <?php echo e($primaryColor); ?>; padding: 12px;">
                    <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; color: <?php echo e($primaryColor); ?>;">
                        Notes
                    </div>
                    <p style="color: #374151; font-size: 12px;"><?php echo e($invoice->notes); ?></p>
                </td>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes && $invoice->terms): ?>
                <td style="width: 4%;"></td>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->terms): ?>
                <td style="width: 48%; vertical-align: top; border: 2px solid <?php echo e($primaryColor); ?>; padding: 12px;">
                    <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; color: <?php echo e($primaryColor); ?>;">
                        Payment Terms
                    </div>
                    <p style="color: #374151; font-size: 12px;"><?php echo e($invoice->terms); ?></p>
                </td>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tr>
    </table>

</div>

<?php else: ?>

<div class="classic-business bg-white p-12" style="min-height: 297mm; padding: 48px;">

    
    <div class="border-b-4 pb-6 mb-8" style="border-color: <?php echo e($primaryColor); ?>">
        <div class="flex justify-between items-center">
            <div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_logo): ?>
                    <img src="<?php echo e(Storage::url($invoice->company_logo)); ?>"
                         alt="<?php echo e($invoice->company_name); ?>"
                         class="h-20 mb-3">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <h1 class="text-2xl font-bold" style="color: <?php echo e($primaryColor); ?>">
                    <?php echo e($invoice->company_name); ?>

                </h1>
            </div>
            <div class="text-right">
                <div class="text-5xl font-bold mb-2" style="color: <?php echo e($primaryColor); ?>">
                    INVOICE
                </div>
                <div class="text-xl font-semibold">
                    <?php echo e($invoice->invoice_number); ?>

                </div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-2 gap-8 mb-8">
        
        <div class="space-y-6">
            
            <div>
                <div class="text-xs font-bold uppercase tracking-wider mb-2"
                     style="color: <?php echo e($primaryColor); ?>">
                    From
                </div>
                <div class="text-sm text-gray-800 space-y-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_address): ?>
                        <p><?php echo e($invoice->company_address); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_email): ?>
                        <p><?php echo e($invoice->company_email); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_phone): ?>
                        <p><?php echo e($invoice->company_phone); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div>
                <div class="text-xs font-bold uppercase tracking-wider mb-2"
                     style="color: <?php echo e($primaryColor); ?>">
                    Bill To
                </div>
                <div class="text-sm text-gray-800 space-y-1">
                    <p class="font-bold"><?php echo e($invoice->client_name); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_address): ?>
                        <p><?php echo e($invoice->client_address); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_email): ?>
                        <p><?php echo e($invoice->client_email); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_phone): ?>
                        <p><?php echo e($invoice->client_phone); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div>
            <table class="w-full text-sm">
                <tr class="border-b border-gray-300">
                    <td class="py-2 font-semibold" style="color: <?php echo e($primaryColor); ?>">
                        Invoice Number:
                    </td>
                    <td class="py-2 text-right"><?php echo e($invoice->invoice_number); ?></td>
                </tr>
                <tr class="border-b border-gray-300">
                    <td class="py-2 font-semibold" style="color: <?php echo e($primaryColor); ?>">
                        Invoice Date:
                    </td>
                    <td class="py-2 text-right"><?php echo e($invoice->invoice_date->format('F d, Y')); ?></td>
                </tr>
                <tr class="border-b border-gray-300">
                    <td class="py-2 font-semibold" style="color: <?php echo e($primaryColor); ?>">
                        Due Date:
                    </td>
                    <td class="py-2 text-right"><?php echo e($invoice->due_date->format('F d, Y')); ?></td>
                </tr>
                <tr>
                    <td class="py-2 font-semibold" style="color: <?php echo e($primaryColor); ?>">
                        Status:
                    </td>
                    <td class="py-2 text-right">
                        <span class="px-2 py-1 text-xs font-semibold rounded"
                              style="background-color: <?php echo e($primaryColor); ?>20; color: <?php echo e($primaryColor); ?>">
                            <?php echo e(ucfirst($invoice->status)); ?>

                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    
    <div class="mb-8">
        <table class="w-full">
            <thead>
                <tr class="text-white" style="background-color: <?php echo e($primaryColor); ?>">
                    <th class="text-left py-3 px-4 font-semibold text-sm uppercase">
                        Description
                    </th>
                    <th class="text-center py-3 px-4 font-semibold text-sm uppercase w-20">
                        Qty
                    </th>
                    <th class="text-right py-3 px-4 font-semibold text-sm uppercase w-28">
                        Rate
                    </th>
                    <th class="text-right py-3 px-4 font-semibold text-sm uppercase w-32">
                        Amount
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="<?php echo e($loop->even ? 'bg-gray-50' : 'bg-white'); ?>">
                        <td class="py-3 px-4 text-gray-800"><?php echo e($item->description); ?></td>
                        <td class="py-3 px-4 text-center text-gray-600"><?php echo e($item->quantity); ?></td>
                        <td class="py-3 px-4 text-right text-gray-600">
                            $<?php echo e(number_format($item->unit_price, 2)); ?>

                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-gray-800">
                            $<?php echo e(number_format($item->total, 2)); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="flex justify-end mb-8">
        <div class="w-96">
            <table class="w-full text-sm">
                <tr class="border-b border-gray-300">
                    <td class="py-2 text-gray-700">Subtotal:</td>
                    <td class="py-2 text-right font-semibold">
                        $<?php echo e(number_format($invoice->subtotal, 2)); ?>

                    </td>
                </tr>
                <tr class="border-b border-gray-300">
                    <td class="py-2 text-gray-700">Tax (<?php echo e($invoice->tax_rate); ?>%):</td>
                    <td class="py-2 text-right font-semibold">
                        $<?php echo e(number_format($invoice->tax_amount, 2)); ?>

                    </td>
                </tr>
                <tr class="text-white" style="background-color: <?php echo e($primaryColor); ?>">
                    <td class="py-3 px-4 text-lg font-bold">TOTAL:</td>
                    <td class="py-3 px-4 text-right text-xl font-bold">
                        $<?php echo e(number_format($invoice->total, 2)); ?>

                    </td>
                </tr>
            </table>
        </div>
    </div>

    
    <div class="grid grid-cols-2 gap-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes): ?>
            <div class="border-2 p-4" style="border-color: <?php echo e($primaryColor); ?>">
                <h4 class="text-sm font-bold uppercase tracking-wide mb-2"
                    style="color: <?php echo e($primaryColor); ?>">
                    Notes
                </h4>
                <p class="text-gray-700 text-sm"><?php echo e($invoice->notes); ?></p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->terms): ?>
            <div class="border-2 p-4" style="border-color: <?php echo e($primaryColor); ?>">
                <h4 class="text-sm font-bold uppercase tracking-wide mb-2"
                    style="color: <?php echo e($primaryColor); ?>">
                    Payment Terms
                </h4>
                <p class="text-gray-700 text-sm"><?php echo e($invoice->terms); ?></p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\invoices\templates\classic-business.blade.php ENDPATH**/ ?>