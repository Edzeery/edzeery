<?php
    $limits = \App\Support\Storefront\StorefrontSections::TEXT_LIMITS;
?>
<?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'cta-title',
    'label' => __('merchant_panel.cta_title'),
    'wirePath' => 'section_content.cta.title',
    'max' => $limits['title'],
    'placeholder' => __('storefront.ready_to_order'),
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'cta-description',
    'label' => __('merchant_panel.cta_description'),
    'wirePath' => 'section_content.cta.description',
    'max' => $limits['description'],
    'placeholder' => __('storefront.get_yours_now'),
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'cta-button-text',
    'label' => __('merchant_panel.hero_button_text'),
    'wirePath' => 'section_content.cta.button_text',
    'max' => $limits['button_text'],
    'placeholder' => __('storefront.order_now'),
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\storefront-settings\fields\cta.blade.php ENDPATH**/ ?>