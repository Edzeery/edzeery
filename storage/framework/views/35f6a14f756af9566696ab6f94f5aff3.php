<?php
    $limits = \App\Support\Storefront\StorefrontSections::TEXT_LIMITS;
?>
<?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'hero-title',
    'label' => __('merchant_panel.hero_title'),
    'wirePath' => 'section_content.hero.title',
    'max' => $limits['title'],
    'placeholder' => __('merchant_panel.hero_title_placeholder'),
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'hero-description',
    'label' => __('merchant_panel.hero_description'),
    'wirePath' => 'section_content.hero.description',
    'max' => $limits['description'],
    'type' => 'textarea',
    'rows' => 2,
    'placeholder' => __('merchant_panel.hero_description_placeholder'),
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'hero-button-text',
    'label' => __('merchant_panel.hero_button_text'),
    'wirePath' => 'section_content.hero.button_text',
    'max' => $limits['button_text'],
    'placeholder' => __('storefront.order_now'),
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\storefront-settings\fields\hero.blade.php ENDPATH**/ ?>