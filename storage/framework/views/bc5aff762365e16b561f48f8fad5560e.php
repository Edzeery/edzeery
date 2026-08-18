<?php

use App\Enums\Store\ProductOptionInputType;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Products\Product;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use App\Services\ProductService;
use App\Support\VariantPreviewBuilder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;

?>

<div x-data="{ step: <?php if ((object) ('currentStep') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('currentStep'->value()); ?>')<?php echo e('currentStep'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('currentStep'); ?>')<?php endif; ?> }">
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title"><?php echo e($product ? __('products.edit_product') : __('products.new_product')); ?></h1>
            <p class="edz-page-head__subtitle"><?php echo e(__('products.subtitle', ['store' => currentStore()?->name])); ?></p>
        </div>
        <a href="<?php echo e(route('merchant.products.index', currentStore())); ?>" wire:navigate
           class="edz-btn edz-btn--ghost"><?php echo e(__('products.cancel')); ?></a>
    </div>

    <?php if (isset($component)) { $__componentOriginale221d74fa526b0c5591d586097923f1d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale221d74fa526b0c5591d586097923f1d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.wizard-steps','data' => ['steps' => $this->wizardSteps,'currentStep' => $currentStep]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.wizard-steps'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->wizardSteps),'currentStep' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentStep)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale221d74fa526b0c5591d586097923f1d)): ?>
<?php $attributes = $__attributesOriginale221d74fa526b0c5591d586097923f1d; ?>
<?php unset($__attributesOriginale221d74fa526b0c5591d586097923f1d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale221d74fa526b0c5591d586097923f1d)): ?>
<?php $component = $__componentOriginale221d74fa526b0c5591d586097923f1d; ?>
<?php unset($__componentOriginale221d74fa526b0c5591d586097923f1d); ?>
<?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="mb-6 rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700 dark:border-danger-800 dark:bg-danger-950 dark:text-danger-300">
            <p class="font-semibold"><?php echo e(__('products.fix_errors')); ?></p>
            <ul class="mt-1 list-inside list-disc">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form wire:submit="save" x-data="edzDirty()">
        
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
                                <select id="product-brand" class="edz-select" wire:model="brand_id">
                                    <option value=""><?php echo e(__('products.no_brand')); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $brandName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>" <?php if((string) $brand_id === (string) $id): echo 'selected'; endif; ?>><?php echo e($brandName); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
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

                <div class="space-y-6">
                    <div class="edz-card">
                        <div class="edz-card__header">
                            <div>
                                <h2 class="edz-card__title"><?php echo e(__('products.images')); ?></h2>
                                <p class="text-sm text-ink-400"><?php echo e(__('products.images_hint')); ?></p>
                            </div>
                        </div>
                        <div class="edz-card__body space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($images) || count($newImages)): ?>
                                <div class="grid grid-cols-2 gap-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="relative overflow-hidden rounded-lg border border-surface-border">
                                            <img src="<?php echo e(Storage::disk('public')->url($path)); ?>" alt=""
                                                 class="h-24 w-full object-cover">
                                            <button type="button" wire:click="removeImage(<?php echo e($index); ?>)"
                                                    class="absolute right-1 top-1 rounded-full bg-surface/90 px-2 py-0.5 text-xs font-semibold text-danger-600 hover:bg-danger-600 hover:text-white">
                                                &times;
                                            </button>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $newImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $upload): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="relative overflow-hidden rounded-lg border border-surface-border">
                                            <img src="<?php echo e($upload->temporaryUrl()); ?>" alt=""
                                                 class="h-24 w-full object-cover">
                                            <button type="button" wire:click="removeNewImage(<?php echo e($index); ?>)"
                                                    class="absolute right-1 top-1 rounded-full bg-surface/90 px-2 py-0.5 text-xs font-semibold text-danger-600 hover:bg-danger-600 hover:text-white">
                                                &times;
                                            </button>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <input type="file" wire:model="newImages" multiple accept="image/*"
                                   class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-brand-700">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div x-show="step === 2" x-transition.opacity>
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    <div class="edz-card">
                        <div class="edz-card__header">
                            <div>
                                <h2 class="edz-card__title"><?php echo e(__('products.pricing_stock')); ?></h2>
                                <p class="text-sm text-ink-400"><?php echo e(__('products.pricing_stock_hint')); ?></p>
                            </div>
                        </div>
                        <div class="edz-card__body">
                            <div class="mb-4">
                                <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                    <input type="checkbox" wire:model.live="has_variants" class="h-4 w-4 rounded border-surface-border text-brand-600">
                                    <?php echo e(__('products.has_variants')); ?>

                                </label>
                                <p class="mt-1 text-xs text-ink-muted"><?php echo e(__('products.product_type_hint')); ?></p>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $has_variants): ?>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="edz-field">
                                        <label class="edz-field__label" for="product-price"><?php echo e(__('products.price')); ?></label>
                                        <input id="product-price" type="number" step="0.01" min="0" class="edz-input <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               wire:model="price" placeholder="0.00">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="edz-field">
                                        <label class="edz-field__label" for="product-compare-price"><?php echo e(__('products.compare_at_price')); ?></label>
                                        <input id="product-compare-price" type="number" step="0.01" min="0" class="edz-input <?php $__errorArgs = ['compare_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               wire:model="compare_price" placeholder="0.00">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['compare_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="edz-field">
                                        <label class="edz-field__label" for="product-cost-price"><?php echo e(__('products.cost_price')); ?></label>
                                        <input id="product-cost-price" type="number" step="0.01" min="0" class="edz-input <?php $__errorArgs = ['cost_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                               wire:model="cost_price" placeholder="0.00">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['cost_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-3 sm:col-span-2 lg:col-span-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted"><?php echo e(__('products.profit_margin')); ?></p>
                                        <?php
                                            $sp = $price !== null && $price !== '' ? (float) $price : null;
                                            $sc = $cost_price !== null && $cost_price !== '' ? (float) $cost_price : null;
                                            $sProfit = $sp !== null && $sc !== null ? $sp - $sc : null;
                                            $sMargin = $sProfit !== null && $sp > 0 ? round(($sProfit / $sp) * 100, 1) : null;
                                        ?>
                                        <p class="mt-1 text-sm font-semibold text-ink">
                                            <?php echo e($sProfit !== null ? number_format($sProfit, 2) : '—'); ?>

                                            <span class="text-ink-muted">(<?php echo e($sMargin !== null ? $sMargin.'%' : '—'); ?>)</span>
                                        </p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-4 text-sm text-ink-muted">
                                    <?php echo e(__('products.add_options_hint')); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div x-show="step === 3" x-transition.opacity>
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($has_variants): ?>
                        <div class="edz-card">
                            <div class="edz-card__header">
                                <div>
                                    <h2 class="edz-card__title"><?php echo e(__('products.options')); ?></h2>
                                    <p class="text-sm text-ink-400"><?php echo e(__('products.options_hint')); ?></p>
                                </div>
                                <button type="button" wire:click="addOption" wire:loading.attr="disabled" class="edz-btn edz-btn--secondary edz-btn--sm"><?php echo e(__('products.add_option')); ?></button>
                            </div>
                            <div class="edz-card__body space-y-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="grid grid-cols-1 gap-3 rounded-lg border border-surface-border p-4 md:grid-cols-2">
                                        <div class="edz-field">
                                            <label class="edz-field__label" for="option-<?php echo e($index); ?>"><?php echo e(__('products.option')); ?></label>
                                            <select id="option-<?php echo e($index); ?>" class="edz-select"
                                                    wire:change="optionChanged(<?php echo e($index); ?>, $event.target.value)">
                                                <option value=""><?php echo e(__('products.select_option')); ?></option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->productOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($opt->id); ?>"
                                                            <?php if(($option['product_option_id'] ?? null) === $opt->id): echo 'selected'; endif; ?>>
                                                        <?php echo e($opt->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </select>
                                        </div>

                                        <div class="edz-field">
                                            <label class="edz-field__label" for="option-values-<?php echo e($index); ?>"><?php echo e(__('products.values')); ?></label>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($option['type'] ?? null) === ProductOptionInputType::TEXT->value): ?>
                                                <div class="rounded-md border border-surface-border px-3 py-2 text-sm text-ink-muted">
                                                    <?php echo e(__('products.text_options_hint')); ?>

                                                </div>
                                            <?php elseif(! empty($option['product_option_id'])): ?>
                                                <select id="option-values-<?php echo e($index); ?>" class="edz-select" multiple size="3"
                                                        wire:model="options.<?php echo e($index); ?>.values"
                                                        wire:change="valuesChanged(<?php echo e($index); ?>)">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->optionValuesByOption->get($option['product_option_id'], collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($value->id); ?>"
                                                                <?php if(in_array($value->id, $option['values'] ?? [])): echo 'selected'; endif; ?>>
                                                            <?php echo e($value->value); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </select>
                                            <?php else: ?>
                                                <div class="rounded-md border border-surface-border px-3 py-2 text-sm text-ink-muted">
                                                    <?php echo e(__('products.select_option_to_configure')); ?>

                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>

                                        <div class="md:col-span-2">
                                            <button type="button" wire:click="removeOption(<?php echo e($index); ?>)"
                                                    class="text-sm font-semibold text-danger-600 hover:text-danger-700">
                                                <?php echo e(__('products.remove_option')); ?>

                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <p class="text-sm text-ink-muted"><?php echo e(__('products.no_options_yet')); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div class="edz-card">
                            <div class="edz-card__header">
                                <div>
                                    <h2 class="edz-card__title"><?php echo e(__('products.variants')); ?></h2>
                                    <p class="text-sm text-ink-400"><?php echo e(__('products.variants_hint')); ?></p>
                                </div>
                            </div>
                            <div class="edz-card__body">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($variants_preview) === 0): ?>
                                    <p class="text-sm text-ink-muted"><?php echo e(__('products.add_options_hint')); ?></p>
                                <?php else: ?>
                                    <div class="mb-4 grid grid-cols-1 gap-3 rounded-lg border border-surface-border bg-surface-secondary/50 p-4 sm:grid-cols-2 lg:grid-cols-5">
                                        <div class="edz-field">
                                            <label class="edz-field__label" for="apply-price"><?php echo e(__('products.price')); ?></label>
                                            <input id="apply-price" type="number" step="0.01" min="0" class="edz-input"
                                                   wire:model="apply_all_price" placeholder="0.00">
                                        </div>
                                        <div class="edz-field">
                                            <label class="edz-field__label" for="apply-cost"><?php echo e(__('products.cost')); ?></label>
                                            <input id="apply-cost" type="number" step="0.01" min="0" class="edz-input"
                                                   wire:model="apply_all_cost_price" placeholder="0.00">
                                        </div>
                                        <div class="edz-field">
                                            <label class="edz-field__label" for="apply-stock"><?php echo e(__('products.stock')); ?></label>
                                            <input id="apply-stock" type="number" min="0" class="edz-input"
                                                   wire:model="apply_all_stock" placeholder="0">
                                        </div>
                                        <div class="edz-field">
                                            <label class="edz-field__label" for="apply-low-stock"><?php echo e(__('products.low_stock')); ?></label>
                                            <input id="apply-low-stock" type="number" min="0" class="edz-input"
                                                   wire:model="apply_all_low_stock" placeholder="5">
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" wire:click="applyAll" wire:loading.attr="disabled" class="edz-btn edz-btn--secondary w-full"><?php echo e(__('products.apply_to_all')); ?></button>
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                                                    <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.variant')); ?></th>
                                                    <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.price')); ?></th>
                                                    <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.cost')); ?></th>
                                                    <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.compare')); ?></th>
                                                    <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.stock')); ?></th>
                                                    <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.low_stock')); ?></th>
                                                    <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.profit')); ?></th>
                                                    <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.margin')); ?></th>
                                                    <th class="px-3 py-2 text-start font-semibold"><?php echo e(__('products.active')); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $variants_preview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr class="border-b border-surface-border last:border-0">
                                                        <td class="max-w-48 px-3 py-2 align-top text-xs font-medium text-ink-soft">
                                                            <?php echo e($variant['labels'] ?? $variant['name'] ?? '—'); ?>

                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                                   wire:model.blur="variants_preview.<?php echo e($index); ?>.price">
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                                   wire:model.blur="variants_preview.<?php echo e($index); ?>.cost_price">
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" step="0.01" min="0" class="edz-input min-w-24 px-2 py-1 text-xs"
                                                                   wire:model.blur="variants_preview.<?php echo e($index); ?>.compare_price">
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" min="0" class="edz-input min-w-20 px-2 py-1 text-xs"
                                                                   wire:model.blur="variants_preview.<?php echo e($index); ?>.stock">
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" min="0" class="edz-input min-w-20 px-2 py-1 text-xs"
                                                                   wire:model.blur="variants_preview.<?php echo e($index); ?>.low_stock_threshold">
                                                        </td>
                                                        <?php
                                                            $vp = $variant['price'] ?? null;
                                                            $vc = $variant['cost_price'] ?? null;
                                                            $vProfit = ($vp !== null && $vp !== '' && $vc !== null && $vc !== '')
                                                                ? (float) $vp - (float) $vc
                                                                : null;
                                                            $vMargin = $vProfit !== null && (float) $vp > 0
                                                                ? round(($vProfit / (float) $vp) * 100, 1)
                                                                : null;
                                                        ?>
                                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-ink-soft">
                                                            <?php echo e($vProfit !== null ? number_format($vProfit, 2) : '—'); ?>

                                                        </td>
                                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-ink-soft">
                                                            <?php echo e($vMargin !== null ? $vMargin.'%' : '—'); ?>

                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="checkbox" class="h-4 w-4 rounded border-surface-border text-brand-600"
                                                                   wire:model="variants_preview.<?php echo e($index); ?>.is_active">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="edz-card">
                            <div class="edz-card__body">
                                <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-8 text-center">
                                    <p class="text-sm text-ink-muted"><?php echo e(__('products.add_options_hint')); ?></p>
                                    <p class="mt-1 text-xs text-ink-muted"><?php echo e(__('products.product_type_hint')); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div x-show="step === 4" x-transition.opacity>
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $has_variants): ?>
                        <div class="edz-card">
                            <div class="edz-card__header">
                                <div>
                                    <h2 class="edz-card__title"><?php echo e(__('products.pricing_stock')); ?></h2>
                                    <p class="text-sm text-ink-400"><?php echo e(__('products.pricing_stock_hint')); ?></p>
                                </div>
                            </div>
                            <div class="edz-card__body grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-stock"><?php echo e(__('products.stock')); ?></label>
                                <input id="product-stock" type="number" min="0" class="edz-input <?php $__errorArgs = ['stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model="stock" placeholder="0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-low-stock"><?php echo e(__('products.low_stock_threshold')); ?></label>
                                <input id="product-low-stock" type="number" min="0" class="edz-input <?php $__errorArgs = ['low_stock_threshold'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model="low_stock_threshold" placeholder="5">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['low_stock_threshold'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="edz-card">
                            <div class="edz-card__body">
                                <div class="rounded-lg border border-surface-border bg-surface-secondary/60 p-4 text-sm text-ink-muted">
                                    <?php echo e(__('products.variants_hint')); ?>

                                </div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="edz-card">
                        <div class="edz-card__header">
                            <div>
                                <h2 class="edz-card__title"><?php echo e(__('products.codes')); ?></h2>
                                <p class="text-sm text-ink-400"><?php echo e(__('products.codes_hint')); ?></p>
                            </div>
                        </div>
                        <div class="edz-card__body grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="edz-field">
                                <label class="edz-field__label" for="product-sku"><?php echo e(__('products.sku')); ?></label>
                                <input id="product-sku" type="text" class="edz-input <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model="sku" <?php if($auto_generate_sku): echo 'disabled'; endif; ?> placeholder="<?php echo e(__('products.auto_generated')); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sku'];
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
                                <label class="edz-field__label" for="product-barcode"><?php echo e(__('products.barcode')); ?></label>
                                <input id="product-barcode" type="text" class="edz-input <?php $__errorArgs = ['barcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model="barcode" <?php if($auto_generate_barcode): echo 'disabled'; endif; ?> placeholder="<?php echo e(__('products.auto_generated')); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['barcode'];
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

                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model.live="auto_generate_sku" class="h-4 w-4 rounded border-surface-border text-brand-600">
                                <?php echo e(__('products.auto_generate_sku')); ?>

                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model.live="auto_generate_barcode" class="h-4 w-4 rounded border-surface-border text-brand-600">
                                <?php echo e(__('products.auto_generate_barcode')); ?>

                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div x-show="step === 5" x-transition.opacity>
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
                                    <p class="mt-0.5 text-ink"><?php echo e(count($images) + count($newImages)); ?> images</p>
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
        </div>

        
        <div class="mt-6 flex items-center justify-between">
            <button type="button"
                    x-show="step > 1"
                    @click="$wire.prevStep()"
                    class="edz-btn edz-btn--ghost">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-left','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-left','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                <?php echo e(__('buttons.previous')); ?>

            </button>
            <div x-show="step <= 1"></div>

            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('merchant.products.index', currentStore())); ?>" wire:navigate
                   class="edz-btn edz-btn--ghost"><?php echo e(__('products.cancel')); ?></a>

                <button type="button"
                        x-show="step < 5"
                        @click="$wire.nextStep()"
                        wire:loading.attr="disabled"
                        class="edz-btn edz-btn--primary">
                    <span wire:loading.remove><?php echo e(__('buttons.next')); ?></span>
                    <span wire:loading class="inline-flex items-center gap-1">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"></path></svg>
                        <?php echo e(__('buttons.processing')); ?>

                    </span>
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-right','class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right','class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                </button>

                <button type="submit"
                        x-show="step === 5"
                        wire:loading.attr="disabled"
                        class="edz-btn edz-btn--primary">
                    <span wire:loading.remove><?php echo e(__('products.save_product')); ?></span>
                    <span wire:loading class="inline-flex items-center gap-1">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"></path></svg>
                        <?php echo e(__('buttons.processing')); ?>

                    </span>
                </button>
                </button>
            </div>
        </div>
    </form>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/products/form.blade.php ENDPATH**/ ?>