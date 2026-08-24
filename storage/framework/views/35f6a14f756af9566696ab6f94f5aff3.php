<div>
    <label class="edz-label" for="hero-title"><?php echo e(__('merchant_panel.hero_title')); ?></label>
    <input id="hero-title" type="text"
        wire:model="section_content.hero.title"
        class="edz-input"
        placeholder="<?php echo e(__('merchant_panel.hero_title_placeholder')); ?>" />
</div>
<div>
    <label class="edz-label" for="hero-description"><?php echo e(__('merchant_panel.hero_description')); ?></label>
    <textarea id="hero-description" wire:model="section_content.hero.description"
        class="edz-input" rows="2"
        placeholder="<?php echo e(__('merchant_panel.hero_description_placeholder')); ?>"></textarea>
</div>
<div>
    <label class="edz-label" for="hero-button-text"><?php echo e(__('merchant_panel.hero_button_text')); ?></label>
    <input id="hero-button-text" type="text"
        wire:model="section_content.hero.button_text"
        class="edz-input"
        placeholder="<?php echo e(__('storefront.order_now')); ?>" />
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\storefront-settings\fields\hero.blade.php ENDPATH**/ ?>