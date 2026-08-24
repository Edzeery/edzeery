<div>
    <label class="edz-label" for="cta-title">{{ __('merchant_panel.cta_title') }}</label>
    <input id="cta-title" type="text"
        wire:model="section_content.cta.title"
        class="edz-input"
        placeholder="{{ __('storefront.ready_to_order') }}" />
</div>
<div>
    <label class="edz-label" for="cta-description">{{ __('merchant_panel.cta_description') }}</label>
    <input id="cta-description" type="text"
        wire:model="section_content.cta.description"
        class="edz-input"
        placeholder="{{ __('storefront.get_yours_now') }}" />
</div>
<div>
    <label class="edz-label" for="cta-button-text">{{ __('merchant_panel.hero_button_text') }}</label>
    <input id="cta-button-text" type="text"
        wire:model="section_content.cta.button_text"
        class="edz-input"
        placeholder="{{ __('storefront.order_now') }}" />
</div>
