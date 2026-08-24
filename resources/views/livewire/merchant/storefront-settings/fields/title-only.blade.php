{{-- Shared single-field editor for title-only sections (categories / brands / description) --}}
@include('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => $id,
    'label' => __('merchant_panel.section_title'),
    'wirePath' => $path . '.title',
    'max' => \App\Support\Storefront\StorefrontSections::TEXT_LIMITS['title'],
])
