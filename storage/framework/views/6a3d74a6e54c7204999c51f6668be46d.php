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
    $primaryColor = $invoice->template->settings['primary_color'] ?? '#7c3aed';
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($forPdf): ?>

<div style="font-family: 'DejaVu Sans', sans-serif; font-size: 14px; color: #1f2937;">

    
    <div style="background-color: <?php echo e($primaryColor); ?>; padding: 40px 40px 30px 40px;">
        <table style="width: 100%;">
            <tr>
                <td style="vertical-align: top; color: #ffffff;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_logo): ?>
                        <img src="<?php echo e(storage_path('app/public/' . $invoice->company_logo)); ?>"
                             alt="<?php echo e($invoice->company_name); ?>"
                             style="height: 50px; margin-bottom: 12px;">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div style="font-size: 30px; font-weight: bold; margin-bottom: 8px; color: #ffffff;">
                        <?php echo e($invoice->company_name); ?>

                    </div>
                    <div style="color: rgba(255,255,255,0.9); font-size: 12px;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_email): ?>
                            <p style="margin-bottom: 2px;"><?php echo e($invoice->company_email); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_phone): ?>
                            <p style="margin-bottom: 2px;"><?php echo e($invoice->company_phone); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </td>
                <td style="text-align: right; vertical-align: top; color: #ffffff;">
                    <div style="font-size: 44px; font-weight: 900; margin-bottom: 8px; color: #ffffff;">INVOICE</div>
                    <div style="font-size: 20px; font-weight: bold; color: #ffffff;"><?php echo e($invoice->invoice_number); ?></div>
                </td>
            </tr>
        </table>
    </div>

    
    <div style="padding: 40px;">

        
        <table style="width: 100%; margin-bottom: 35px;">
            <tr>
                <td style="width: 33%; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; color: <?php echo e($primaryColor); ?>;">
                        Invoice Date
                    </div>
                    <div style="font-size: 16px; font-weight: 600;">
                        <?php echo e($invoice->invoice_date->format('M d, Y')); ?>

                    </div>
                </td>
                <td style="width: 33%; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; color: <?php echo e($primaryColor); ?>;">
                        Due Date
                    </div>
                    <div style="font-size: 16px; font-weight: 600;">
                        <?php echo e($invoice->due_date->format('M d, Y')); ?>

                    </div>
                </td>
                <td style="width: 34%; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; color: <?php echo e($primaryColor); ?>;">
                        Billed To
                    </div>
                    <div style="font-size: 12px;">
                        <p style="font-weight: bold; font-size: 16px; margin-bottom: 4px;"><?php echo e($invoice->client_name); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_email): ?>
                            <p style="color: #4b5563;"><?php echo e($invoice->client_email); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>

        
        <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; color: <?php echo e($primaryColor); ?>;">
            Services
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <table style="width: 100%; margin-bottom: 8px; background-color: <?php echo e($primaryColor); ?>08; border-collapse: collapse;">
                <tr>
                    <td style="padding: 12px 15px; vertical-align: middle;">
                        <div style="font-weight: 600; color: #1f2937;"><?php echo e($item->description); ?></div>
                        <div style="font-size: 12px; color: #4b5563; margin-top: 4px;">
                            <?php echo e($item->quantity); ?> × $<?php echo e(number_format($item->unit_price, 2)); ?>

                        </div>
                    </td>
                    <td style="padding: 12px 15px; text-align: right; vertical-align: middle; font-size: 18px; font-weight: bold; color: <?php echo e($primaryColor); ?>;">
                        $<?php echo e(number_format($item->total, 2)); ?>

                    </td>
                </tr>
            </table>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <table style="width: 100%; margin-top: 25px;">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%;">
                    <table style="width: 100%; font-size: 13px;">
                        <tr>
                            <td style="padding: 6px 0; color: #374151;">Subtotal</td>
                            <td style="padding: 6px 0; text-align: right; font-weight: 600;">$<?php echo e(number_format($invoice->subtotal, 2)); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: #374151;">Tax (<?php echo e($invoice->tax_rate); ?>%)</td>
                            <td style="padding: 6px 0; text-align: right; font-weight: 600;">$<?php echo e(number_format($invoice->tax_amount, 2)); ?></td>
                        </tr>
                    </table>

                    <table style="width: 100%; margin-top: 12px; background-color: <?php echo e($primaryColor); ?>; color: #ffffff;">
                        <tr>
                            <td style="padding: 18px 20px; font-size: 18px; font-weight: bold;">TOTAL</td>
                            <td style="padding: 18px 20px; text-align: right; font-size: 24px; font-weight: 900;">$<?php echo e(number_format($invoice->total, 2)); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes || $invoice->terms): ?>
            <div style="margin-top: 40px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes): ?>
                    <div style="background-color: <?php echo e($primaryColor); ?>08; padding: 18px; margin-bottom: 15px;">
                        <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; color: <?php echo e($primaryColor); ?>;">
                            Additional Notes
                        </div>
                        <p style="color: #374151; font-size: 12px;"><?php echo e($invoice->notes); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->terms): ?>
                    <div style="background-color: <?php echo e($primaryColor); ?>08; padding: 18px;">
                        <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; color: <?php echo e($primaryColor); ?>;">
                            Payment Terms
                        </div>
                        <p style="color: #374151; font-size: 12px;"><?php echo e($invoice->terms); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>
</div>

<?php else: ?>

<div class="creative-agency bg-white" style="min-height: 297mm; padding: 48px;">

    
    <div class="p-12 pb-8" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($primaryColor); ?>dd 100%)">
        <div class="flex justify-between items-start text-white">
            <div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_logo): ?>
                    <img src="<?php echo e(Storage::url($invoice->company_logo)); ?>"
                         alt="<?php echo e($invoice->company_name); ?>"
                         class="h-16 mb-4 brightness-0 invert">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <h1 class="text-4xl font-bold mb-2"><?php echo e($invoice->company_name); ?></h1>
                <div class="text-white/90 space-y-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_email): ?>
                        <p><?php echo e($invoice->company_email); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->company_phone): ?>
                        <p><?php echo e($invoice->company_phone); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="text-right">
                <div class="text-6xl font-black mb-2">INVOICE</div>
                <div class="text-2xl font-bold"><?php echo e($invoice->invoice_number); ?></div>
            </div>
        </div>
    </div>

    
    <div class="p-12">

        
        <div class="grid grid-cols-3 gap-8 mb-10">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider mb-3"
                     style="color: <?php echo e($primaryColor); ?>">
                    Invoice Date
                </div>
                <div class="text-lg font-semibold">
                    <?php echo e($invoice->invoice_date->format('M d, Y')); ?>

                </div>
            </div>

            <div>
                <div class="text-xs font-bold uppercase tracking-wider mb-3"
                     style="color: <?php echo e($primaryColor); ?>">
                    Due Date
                </div>
                <div class="text-lg font-semibold">
                    <?php echo e($invoice->due_date->format('M d, Y')); ?>

                </div>
            </div>

            <div>
                <div class="text-xs font-bold uppercase tracking-wider mb-3"
                     style="color: <?php echo e($primaryColor); ?>">
                    Billed To
                </div>
                <div class="text-sm space-y-1">
                    <p class="font-bold text-lg"><?php echo e($invoice->client_name); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->client_email): ?>
                        <p class="text-gray-600"><?php echo e($invoice->client_email); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="mb-10 space-y-3">
            <div class="text-xs font-bold uppercase tracking-wider mb-4"
                 style="color: <?php echo e($primaryColor); ?>">
                Services
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center p-4 rounded-lg"
                     style="background-color: <?php echo e($primaryColor); ?>08">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800"><?php echo e($item->description); ?></div>
                        <div class="text-sm text-gray-600 mt-1">
                            <?php echo e($item->quantity); ?> × $<?php echo e(number_format($item->unit_price, 2)); ?>

                        </div>
                    </div>
                    <div class="text-xl font-bold" style="color: <?php echo e($primaryColor); ?>">
                        $<?php echo e(number_format($item->total, 2)); ?>

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="flex justify-end">
            <div class="w-96">
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-gray-700">
                        <span>Subtotal</span>
                        <span class="font-semibold">$<?php echo e(number_format($invoice->subtotal, 2)); ?></span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>Tax (<?php echo e($invoice->tax_rate); ?>%)</span>
                        <span class="font-semibold">$<?php echo e(number_format($invoice->tax_amount, 2)); ?></span>
                    </div>
                </div>

                <div class="flex justify-between items-center p-6 rounded-lg text-white"
                     style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($primaryColor); ?>dd 100%)">
                    <span class="text-xl font-bold">TOTAL</span>
                    <span class="text-3xl font-black">$<?php echo e(number_format($invoice->total, 2)); ?></span>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes || $invoice->terms): ?>
            <div class="mt-12 space-y-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->notes): ?>
                    <div class="p-6 rounded-lg" style="background-color: <?php echo e($primaryColor); ?>08">
                        <h4 class="text-sm font-bold uppercase tracking-wider mb-3"
                            style="color: <?php echo e($primaryColor); ?>">
                            Additional Notes
                        </h4>
                        <p class="text-gray-700"><?php echo e($invoice->notes); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->terms): ?>
                    <div class="p-6 rounded-lg" style="background-color: <?php echo e($primaryColor); ?>08">
                        <h4 class="text-sm font-bold uppercase tracking-wider mb-3"
                            style="color: <?php echo e($primaryColor); ?>">
                            Payment Terms
                        </h4>
                        <p class="text-gray-700"><?php echo e($invoice->terms); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>

</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\invoices\templates\creative-agency.blade.php ENDPATH**/ ?>