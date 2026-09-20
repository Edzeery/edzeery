<div x-show="step === 1" x-transition.opacity>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <div>
                        <h2 class="edz-card__title"><?php echo e(__('products.basic_information')); ?></h2>
                        <p class="text-sm text-ink-400"><?php echo e(__('products.basic_information_hint')); ?></p>
                    </div>
                </div>
                <div class="edz-card__body grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="edz-field md:col-span-2">
                        <label class="edz-field__label" for="product-name"><?php echo e(__('products.name')); ?></label>
                        <input id="product-name" type="text" class="edz-input <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               wire:model.live="name" placeholder="e.g. Premium Cotton T-Shirt">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="edz-field__error"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="edz-field md:col-span-2">
                        <label class="edz-field__label" for="product-slug"><?php echo e(__('products.slug')); ?></label>
                        <input id="product-slug" type="text" class="edz-input <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               wire:model="slug" placeholder="premium-cotton-t-shirt">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="edz-field__error"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="product-brand"><?php echo e(__('products.brand')); ?></label>
                        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'brand_id','options' => $this->brands,'placeholder' => ''.e(__('products.no_brand')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'brand_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->brands),'placeholder' => ''.e(__('products.no_brand')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $attributes = $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $component = $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
                    </div>

                    <div class="edz-field">
                        <label class="edz-field__label" for="product-unit"><?php echo e(__('products.unit')); ?></label>
                        <input id="product-unit" type="text" class="edz-input" wire:model="unit"
                               placeholder="e.g. pcs, kg, box">
                    </div>

                    <div class="edz-field md:col-span-2">
                        <label class="edz-field__label" for="product-categories"><?php echo e(__('products.categories')); ?></label>
                        <select id="product-categories" class="edz-select" wire:model="categories" multiple size="4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->categoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $categoryName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>" <?php if(in_array($id, $categories)): echo 'selected'; endif; ?>><?php echo e($categoryName); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <p class="edz-field__hint"><?php echo e(__('products.categories_hint')); ?></p>
                    </div>

                    <div class="edz-field md:col-span-2">
                        <label class="edz-field__label" for="product-short-description"><?php echo e(__('products.short_description')); ?></label>
                        <textarea id="product-short-description" class="edz-textarea" wire:model="short_description"
                                  rows="2" placeholder="<?php echo e(__('products.short_description_placeholder')); ?>"></textarea>
                    </div>

                    <div class="edz-field md:col-span-2">
                        <label class="edz-field__label" for="product-description"><?php echo e(__('products.description')); ?></label>
                        <textarea id="product-description" class="edz-textarea" wire:model="description"
                                  rows="6" placeholder="<?php echo e(__('products.description_placeholder')); ?>"></textarea>
                    </div>

                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model.live="is_active" class="h-4 w-4 rounded border-surface-border text-brand-600">
                        <?php echo e(__('products.active')); ?>

                    </label>
                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model.live="is_featured" class="h-4 w-4 rounded border-surface-border text-brand-600">
                        <?php echo e(__('products.featured')); ?>

                    </label>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/products/form/step-basic.blade.php ENDPATH**/ ?>