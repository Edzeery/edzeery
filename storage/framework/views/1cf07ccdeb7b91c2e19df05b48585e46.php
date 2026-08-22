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
    $primaryColor = $invoice->template->settings['primary_color'] ?? '#2563eb';
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($forPdf): ?>

<div style="font-family: 'DejaVu Sans', sans-serif; font-size: 14px; color: #1f2937; padding: 40px;">

    
    <table style="width: 100%; margin-bottom: 40px;">
        <tr>
            
            <td style="vertical-align: top;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_logo): ?>
                    <img src="<?php echo e(storage_path('app/public/' . $invoice->company_logo)); ?>"
                         alt="<?php echo e($invoice->company_name); ?>"
                         style="height: 50px; margin-bottom: 12px;">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div style="font-size: 26px; font-weight: bold; margin-bottom: 8px; color: <?php echo e($primaryColor); ?>;">
                    <?php echo e($invoice->company_name); ?>

                </div>
                <div style="color: #4b5563; font-size: 12px;">
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
            </td>
            
            <td style="text-align: right; vertical-align: top;">
                <div style="font-size: 32px; font-weight: bold; margin-bottom: 20px; color: <?php echo e($primaryColor); ?>;">
                    INVOICE
                </div>
                <div style="font-size: 12px;">
                    <div style="margin-bottom: 6px;">
                        <span style="color: #4b5563;">Invoice Number:</span>
                        <strong style="margin-left: 8px;"><?php echo e($invoice->invoice_number); ?></strong>
                    </div>
                    <div style="margin-bottom: 6px;">
                        <span style="color: #4b5563;">Invoice Date:</span>
                        <strong style="margin-left: 8px;"><?php echo e($invoice->invoice_date->format('M d, Y')); ?></strong>
                    </div>
                    <div>
                        <span style="color: #4b5563;">Due Date:</span>
                        <strong style="margin-left: 8px;"><?php echo e($invoice->due_date->format('M d, Y')); ?></strong>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    
    <div style="margin-bottom: 40px;">
        <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; color: <?php echo e($primaryColor); ?>;">
            Bill To
        </div>
        <div style="color: #1f2937;">
            <p style="font-weight: 600; font-size: 16px; margin-bottom: 4px;"><?php echo e($invoice->client_name); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_address): ?>
                <p style="font-size: 12px; margin-bottom: 2px;"><?php echo e($invoice->client_address); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_email): ?>
                <p style="font-size: 12px; margin-bottom: 2px;"><?php echo e($invoice->client_email); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_phone): ?>
                <p style="font-size: 12px; margin-bottom: 2px;"><?php echo e($invoice->client_phone); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <table style="width: 100%; margin-bottom: 40px; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid <?php echo e($primaryColor); ?>;">
                <th style="text-align: left; padding: 10px 0; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: <?php echo e($primaryColor); ?>;">
                    Description
                </th>
                <th style="text-align: center; padding: 10px 0; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: <?php echo e($primaryColor); ?>; width: 80px;">
                    Qty
                </th>
                <th style="text-align: right; padding: 10px 0; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: <?php echo e($primaryColor); ?>; width: 110px;">
                    Unit Price
                </th>
                <th style="text-align: right; padding: 10px 0; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: <?php echo e($primaryColor); ?>; width: 110px;">
                    Total
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 12px 0; color: #1f2937;"><?php echo e($item->description); ?></td>
                    <td style="padding: 12px 0; text-align: center; color: #4b5563;"><?php echo e($item->quantity); ?></td>
                    <td style="padding: 12px 0; text-align: right; color: #4b5563;">
                        $<?php echo e(number_format($item->unit_price, 2)); ?>

                    </td>
                    <td style="padding: 12px 0; text-align: right; font-weight: 600; color: #1f2937;">
                        $<?php echo e(number_format($item->total, 2)); ?>

                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    
    <table style="width: 100%; margin-bottom: 40px;">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%;">
                <table style="width: 100%; font-size: 13px;">
                    <tr>
                        <td style="padding: 6px 0; color: #374151;">Subtotal:</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">$<?php echo e(number_format($invoice->subtotal, 2)); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #374151;">Tax (<?php echo e($invoice->tax_rate); ?>%):</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">$<?php echo e(number_format($invoice->tax_amount, 2)); ?></td>
                    </tr>
                    <tr style="border-top: 2px solid <?php echo e($primaryColor); ?>;">
                        <td style="padding: 12px 0; font-size: 16px; font-weight: bold; color: <?php echo e($primaryColor); ?>;">Total:</td>
                        <td style="padding: 12px 0; text-align: right; font-size: 16px; font-weight: bold; color: <?php echo e($primaryColor); ?>;">$<?php echo e(number_format($invoice->total, 2)); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes): ?>
        <div style="margin-bottom: 20px;">
            <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; color: <?php echo e($primaryColor); ?>;">
                Notes
            </div>
            <p style="color: #374151; font-size: 12px;"><?php echo e($invoice->notes); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->terms): ?>
        <div>
            <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; color: <?php echo e($primaryColor); ?>;">
                Payment Terms
            </div>
            <p style="color: #374151; font-size: 12px;"><?php echo e($invoice->terms); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>

<?php else: ?>

<div class="modern-minimalist" style="min-height: 297mm; padding: 48px;">

    
    <div class="flex justify-between items-start mb-12">
        
        <div class="flex-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_logo): ?>
                <img src="<?php echo e(Storage::url($invoice->company_logo)); ?>"
                     alt="<?php echo e($invoice->company_name); ?>"
                     class="h-16 mb-4">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <h1 class="text-3xl font-bold mb-2" style="color: <?php echo e($primaryColor); ?>">
                <?php echo e($invoice->company_name); ?>

            </h1>
            <div class="text-gray-600 text-sm space-y-1">
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
        
        <div class="text-right">
            <h2 class="text-4xl font-bold mb-6" style="color: <?php echo e($primaryColor); ?>">
                INVOICE
            </h2>
            <div class="text-sm space-y-2">
                <div>
                    <span class="text-gray-600">Invoice Number:</span>
                    <strong class="ml-2"><?php echo e($invoice->invoice_number); ?></strong>
                </div>
                <div>
                    <span class="text-gray-600">Invoice Date:</span>
                    <strong class="ml-2"><?php echo e($invoice->invoice_date->format('M d, Y')); ?></strong>
                </div>
                <div>
                    <span class="text-gray-600">Due Date:</span>
                    <strong class="ml-2"><?php echo e($invoice->due_date->format('M d, Y')); ?></strong>
                </div>
            </div>
        </div>
    </div>

    
    <div class="mb-12">
        <h3 class="text-sm font-bold uppercase tracking-wide mb-3"
            style="color: <?php echo e($primaryColor); ?>">
            Bill To
        </h3>
        <div class="text-gray-800">
            <p class="font-semibold text-lg mb-1"><?php echo e($invoice->client_name); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_address): ?>
                <p class="text-sm"><?php echo e($invoice->client_address); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_email): ?>
                <p class="text-sm"><?php echo e($invoice->client_email); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_phone): ?>
                <p class="text-sm"><?php echo e($invoice->client_phone); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="mb-12">
        <table class="w-full">
            <thead>
                <tr class="border-b-2" style="border-color: <?php echo e($primaryColor); ?>">
                    <th class="text-left py-3 font-semibold text-sm uppercase tracking-wide"
                        style="color: <?php echo e($primaryColor); ?>">
                        Description
                    </th>
                    <th class="text-center py-3 font-semibold text-sm uppercase tracking-wide w-24"
                        style="color: <?php echo e($primaryColor); ?>">
                        Qty
                    </th>
                    <th class="text-right py-3 font-semibold text-sm uppercase tracking-wide w-32"
                        style="color: <?php echo e($primaryColor); ?>">
                        Unit Price
                    </th>
                    <th class="text-right py-3 font-semibold text-sm uppercase tracking-wide w-32"
                        style="color: <?php echo e($primaryColor); ?>">
                        Total
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b border-gray-200">
                        <td class="py-4 text-gray-800"><?php echo e($item->description); ?></td>
                        <td class="py-4 text-center text-gray-600"><?php echo e($item->quantity); ?></td>
                        <td class="py-4 text-right text-gray-600">
                            $<?php echo e(number_format($item->unit_price, 2)); ?>

                        </td>
                        <td class="py-4 text-right font-semibold text-gray-800">
                            $<?php echo e(number_format($item->total, 2)); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="flex justify-end mb-12">
        <div class="w-80">
            <div class="flex justify-between py-2 text-gray-700">
                <span>Subtotal:</span>
                <span class="font-semibold">$<?php echo e(number_format($invoice->subtotal, 2)); ?></span>
            </div>
            <div class="flex justify-between py-2 text-gray-700">
                <span>Tax (<?php echo e($invoice->tax_rate); ?>%):</span>
                <span class="font-semibold">$<?php echo e(number_format($invoice->tax_amount, 2)); ?></span>
            </div>
            <div class="flex justify-between py-4 border-t-2 text-lg font-bold"
                 style="color: <?php echo e($primaryColor); ?>; border-color: <?php echo e($primaryColor); ?>">
                <span>Total:</span>
                <span>$<?php echo e(number_format($invoice->total, 2)); ?></span>
            </div>
        </div>
    </div>

    
    <div class="space-y-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes): ?>
            <div>
                <h4 class="text-sm font-bold uppercase tracking-wide mb-2"
                    style="color: <?php echo e($primaryColor); ?>">
                    Notes
                </h4>
                <p class="text-gray-700 text-sm"><?php echo e($invoice->notes); ?></p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->terms): ?>
            <div>
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
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\invoices\templates\modern-minimalist.blade.php ENDPATH**/ ?>