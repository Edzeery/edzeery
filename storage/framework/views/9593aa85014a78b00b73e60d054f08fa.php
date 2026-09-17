<?php
    $limits = \App\Support\Storefront\StorefrontSections::TEXT_LIMITS;
?>
<?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'faq-title',
    'label' => __('merchant_panel.section_title'),
    'wirePath' => 'section_content.faq.title',
    'max' => $limits['title'],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [0, 1, 2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="p-3 rounded-lg bg-surface-secondary/30 border border-surface-border space-y-3">
        <p class="text-xs font-medium text-ink-400 uppercase tracking-wider"><?php echo e(__('merchant_panel.faq_item')); ?> <?php echo e($i + 1); ?></p>
        <?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
            'id' => 'faq-item-' . $i . '-question',
            'label' => __('merchant_panel.question'),
            'wirePath' => 'section_content.faq.items.' . $i . '.question',
            'max' => $limits['question'],
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
            'id' => 'faq-item-' . $i . '-answer',
            'label' => __('merchant_panel.answer'),
            'wirePath' => 'section_content.faq.items.' . $i . '.answer',
            'max' => $limits['answer'],
            'type' => 'textarea',
            'rows' => 2,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\storefront-settings\fields\faq.blade.php ENDPATH**/ ?>