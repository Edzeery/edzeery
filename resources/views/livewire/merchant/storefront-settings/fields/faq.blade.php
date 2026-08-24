@php
    $limits = \App\Support\Storefront\StorefrontSections::TEXT_LIMITS;
@endphp
@include('livewire.merchant.storefront-settings.fields.partials.countered-field', [
    'id' => 'faq-title',
    'label' => __('merchant_panel.section_title'),
    'wirePath' => 'section_content.faq.title',
    'max' => $limits['title'],
])
@foreach ([0, 1, 2] as $i)
    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700 space-y-3">
        <p class="text-xs font-medium text-ink-400 uppercase tracking-wider">{{ __('merchant_panel.faq_item') }} {{ $i + 1 }}</p>
        @include('livewire.merchant.storefront-settings.fields.partials.countered-field', [
            'id' => 'faq-item-' . $i . '-question',
            'label' => __('merchant_panel.question'),
            'wirePath' => 'section_content.faq.items.' . $i . '.question',
            'max' => $limits['question'],
        ])
        @include('livewire.merchant.storefront-settings.fields.partials.countered-field', [
            'id' => 'faq-item-' . $i . '-answer',
            'label' => __('merchant_panel.answer'),
            'wirePath' => 'section_content.faq.items.' . $i . '.answer',
            'max' => $limits['answer'],
            'type' => 'textarea',
            'rows' => 2,
        ])
    </div>
@endforeach
