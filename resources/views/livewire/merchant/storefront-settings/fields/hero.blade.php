<div>
    <label class="edz-label" for="hero-title">{{ __('merchant_panel.hero_title') }}</label>
    <input id="hero-title" type="text"
        wire:model="section_content.hero.title"
        class="edz-input"
        placeholder="{{ __('merchant_panel.hero_title_placeholder') }}" />
</div>
<div>
    <label class="edz-label" for="hero-description">{{ __('merchant_panel.hero_description') }}</label>
    <textarea id="hero-description" wire:model="section_content.hero.description"
        class="edz-input" rows="2"
        placeholder="{{ __('merchant_panel.hero_description_placeholder') }}"></textarea>
</div>
<div>
    <label class="edz-label" for="hero-button-text">{{ __('merchant_panel.hero_button_text') }}</label>
    <input id="hero-button-text" type="text"
        wire:model="section_content.hero.button_text"
        class="edz-input"
        placeholder="{{ __('storefront.order_now') }}" />
</div>
