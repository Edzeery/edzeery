<?php

use App\Enums\Store\LandingTemplateEnum;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.storefront');

state([
    'template' => LandingTemplateEnum::SINGLE_PRODUCT->value,
    'isPreview' => false,
]);

mount(function (): void {
    $store = currentStore();
    $this->template = $store->landing_template?->value ?? LandingTemplateEnum::SINGLE_PRODUCT->value;

    $previewTemplate = request('preview_template');
    if ($previewTemplate && in_array($previewTemplate, array_column(LandingTemplateEnum::cases(), 'value'))) {
        $this->template = $previewTemplate;
        $this->isPreview = true;
    }
});


?>

<div>
    @if ($template === 'single_product')
        @livewire('storefront.templates.single-product')
    @elseif($template === 'catalog')
        @livewire('storefront.templates.catalog')
    @elseif($template === 'brand')
        @livewire('storefront.templates.brand')
    @else
        @livewire('storefront.templates.single-product')
    @endif
</div>
