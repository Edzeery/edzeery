
<?php
    $type = $type ?? 'input';
?>
<div>
    <label class="edz-label" for="<?php echo e($id); ?>"><?php echo e($label); ?></label>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'textarea'): ?>
        <div class="relative" x-data="{ len: 0 }"
            x-init="len = ($el.querySelector('textarea')?.value ?? '').length">
            <textarea id="<?php echo e($id); ?>" rows="<?php echo e($rows ?? 2); ?>" maxlength="<?php echo e($max); ?>"
                x-on:input="len = $el.value.length"
                wire:model="<?php echo e($wirePath); ?>"
                class="edz-input pe-14"
                placeholder="<?php echo e($placeholder ?? ''); ?>"></textarea>
            <span class="pointer-events-none absolute bottom-2 end-3 flex items-center text-xs text-ink-400" aria-hidden="true">
                <span x-text="len"></span>/<span><?php echo e($max); ?></span>
            </span>
        </div>
    <?php else: ?>
        <div class="relative" x-data="{ len: 0 }"
            x-init="len = ($el.querySelector('input')?.value ?? '').length">
            <input id="<?php echo e($id); ?>" type="text" maxlength="<?php echo e($max); ?>"
                x-on:input="len = $el.value.length"
                wire:model="<?php echo e($wirePath); ?>"
                class="edz-input pe-16"
                placeholder="<?php echo e($placeholder ?? ''); ?>" />
            <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-xs text-ink-400" aria-hidden="true">
                <span x-text="len"></span>/<span><?php echo e($max); ?></span>
            </span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\storefront-settings\fields\partials\countered-field.blade.php ENDPATH**/ ?>