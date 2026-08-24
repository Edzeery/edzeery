@php
    $limits = \App\Support\Storefront\StorefrontSections::TEXT_LIMITS;
@endphp
@include('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'hero-title',
    'label' => __('merchant_panel.hero_title'),
    'wirePath' => 'section_content.hero.title',
    'max' => $limits['title'],
    'placeholder' => __('merchant_panel.hero_title_placeholder'),
])
@include('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'hero-description',
    'label' => __('merchant_panel.hero_description'),
    'wirePath' => 'section_content.hero.description',
    'max' => $limits['description'],
    'type' => 'textarea',
    'rows' => 2,
    'placeholder' => __('merchant_panel.hero_description_placeholder'),
])
@include('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'hero-button-text',
    'label' => __('merchant_panel.hero_button_text'),
    'wirePath' => 'section_content.hero.button_text',
    'max' => $limits['button_text'],
    'placeholder' => __('storefront.order_now'),
])
