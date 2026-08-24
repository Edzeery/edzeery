<div>
    <label class="edz-label" for="social-proof-title"><?php echo e(__('merchant_panel.section_title')); ?></label>
    <input id="social-proof-title" type="text"
        wire:model="section_content.social_proof.title"
        class="edz-input" />
</div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [0, 1, 2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700 space-y-3">
        <p class="text-xs font-medium text-ink-400 uppercase tracking-wider"><?php echo e(__('merchant_panel.item')); ?> <?php echo e($i + 1); ?></p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="edz-label text-xs" for="social-proof-item-<?php echo e($i); ?>-title"><?php echo e(__('merchant_panel.item_title')); ?></label>
                <input id="social-proof-item-<?php echo e($i); ?>-title" type="text"
                    wire:model="section_content.social_proof.items.<?php echo e($i); ?>.title"
                    class="edz-input" />
            </div>
            <div>
                <label class="edz-label text-xs" for="social-proof-item-<?php echo e($i); ?>-description"><?php echo e(__('merchant_panel.item_description')); ?></label>
                <input id="social-proof-item-<?php echo e($i); ?>-description" type="text"
                    wire:model="section_content.social_proof.items.<?php echo e($i); ?>.description"
                    class="edz-input" />
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/storefront-settings/fields/social_proof.blade.php ENDPATH**/ ?>