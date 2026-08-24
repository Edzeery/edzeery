<div>
    <label class="edz-label" for="faq-title"><?php echo e(__('merchant_panel.section_title')); ?></label>
    <input id="faq-title" type="text"
        wire:model="section_content.faq.title"
        class="edz-input" />
</div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [0, 1, 2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700 space-y-3">
        <p class="text-xs font-medium text-ink-400 uppercase tracking-wider"><?php echo e(__('merchant_panel.faq_item')); ?> <?php echo e($i + 1); ?></p>
        <div>
            <label class="edz-label text-xs" for="faq-item-<?php echo e($i); ?>-question"><?php echo e(__('merchant_panel.question')); ?></label>
            <input id="faq-item-<?php echo e($i); ?>-question" type="text"
                wire:model="section_content.faq.items.<?php echo e($i); ?>.question"
                class="edz-input" />
        </div>
        <div>
            <label class="edz-label text-xs" for="faq-item-<?php echo e($i); ?>-answer"><?php echo e(__('merchant_panel.answer')); ?></label>
            <textarea id="faq-item-<?php echo e($i); ?>-answer"
                wire:model="section_content.faq.items.<?php echo e($i); ?>.answer"
                class="edz-input" rows="2"></textarea>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\storefront-settings\fields\faq.blade.php ENDPATH**/ ?>