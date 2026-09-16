<div x-show="step === 6" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title"><?php echo e(__('products.review_section')); ?></h2>
                        <p class="text-sm text-ink-400"><?php echo e(__('products.review_section_desc')); ?></p>
                    </div>
                </div>
                <div class="edz-card__body space-y-3 text-sm">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('products.product_name_label')); ?></p>
                            <p class="mt-0.5 text-ink"><?php echo e($name ?: '—'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('products.slug_label')); ?></p>
                            <p class="mt-0.5 text-ink"><?php echo e($slug ?: '—'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('products.brand_label')); ?></p>
                            <p class="mt-0.5 text-ink"><?php echo e($this->brands[$brand_id] ?? __('products.no_brand')); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('products.categories_label')); ?></p>
                            <p class="mt-0.5 text-ink"><?php echo e(count($categories) ? count($categories).' selected' : '—'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('products.price_label')); ?></p>
                            <p class="mt-0.5 text-ink"><?php echo e($price !== null ? number_format((float) $price, 2) : '—'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('products.status_label')); ?></p>
                            <p class="mt-0.5 text-ink"><?php echo e($is_active ? __('products.active_label') : '—'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('products.options_label')); ?></p>
                            <p class="mt-0.5 text-ink"><?php echo e($has_variants ? count($variants_preview).' variants' : __('products.simple_product')); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('products.images_label')); ?></p>
                            <p class="mt-0.5 text-ink"><?php echo e(count($images)); ?> <?php echo e(__('products.images_label')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="edz-card">
                <div class="edz-card__header">
                    <h2 class="edz-card__title"><?php echo e(__('products.search_engine')); ?></h2>
                </div>
                <div class="edz-card__body grid grid-cols-1 gap-4">
                    <div class="edz-field">
                        <label class="edz-field__label" for="product-meta-title"><?php echo e(__('products.meta_title')); ?></label>
                        <input id="product-meta-title" type="text" class="edz-input" wire:model="meta_title">
                    </div>
                    <div class="edz-field">
                        <label class="edz-field__label" for="product-meta-description"><?php echo e(__('products.meta_description')); ?></label>
                        <textarea id="product-meta-description" class="edz-textarea" wire:model="meta_description"
                                  rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\products\form\step-review.blade.php ENDPATH**/ ?>