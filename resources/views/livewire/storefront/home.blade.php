<?php

use App\Enums\Store\LandingTemplateEnum;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;

layout('components.layouts.storefront');

mount(function (): void {
    $store = currentStore();
    $this->template = $store->landing_template?->value ?? LandingTemplateEnum::SINGLE_PRODUCT->value;
});

$props = ['template' => 'single_product'];
?>

<div>
    @if($template === 'single_product')
        @livewire('storefront.templates.single-product')
    @elseif($template === 'catalog')
        @livewire('storefront.templates.catalog')
    @elseif($template === 'brand')
        @livewire('storefront.templates.brand')
    @else
        @livewire('storefront.templates.single-product')
    @endif
</div>
