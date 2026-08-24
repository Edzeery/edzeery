<div>
    <label class="edz-label" for="cta-title"><?php echo e(__('merchant_panel.cta_title')); ?></label>
    <input id="cta-title" type="text"
        wire:model="section_content.cta.title"
        class="edz-input"
        placeholder="<?php echo e(__('storefront.ready_to_order')); ?>" />
</div>
<div>
    <label class="edz-label" for="cta-description"><?php echo e(__('merchant_panel.cta_description')); ?></label>
    <input id="cta-description" type="text"
        wire:model="section_content.cta.description"
        class="edz-input"
        placeholder="<?php echo e(__('storefront.get_yours_now')); ?>" />
</div>
<div>
    <label class="edz-label" for="cta-button-text"><?php echo e(__('merchant_panel.hero_button_text')); ?></label>
    <input id="cta-button-text" type="text"
        wire:model="section_content.cta.button_text"
        class="edz-input"
        placeholder="<?php echo e(__('storefront.order_now')); ?>" />
</div>
<?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/storefront-settings/fields/cta.blade.php ENDPATH**/ ?>