<?php

use App\Domains\Product\Support\ProductWizardSteps;
use App\Domains\User\Services\SubscriptionGuardService;
use App\Enums\Store\ProductOptionInputType;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Products\Product;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use App\Models\Products\ProductVariant;
use App\Services\ProductService;
use App\Support\VariantPreviewBuilder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.wizard-steps','data' => ['steps' => $this->wizardSteps,'currentStep' => $currentStep,'lockedSteps' => $this->lockedSteps]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.wizard-steps'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->wizardSteps),'currentStep' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentStep),'lockedSteps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->lockedSteps)]); ?>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $this->hasActiveSubscription): ?>
        <div class="mb-6 rounded-lg border border-warning-border bg-warning-surface px-5 py-4 text-sm text-warning-800">
            <div class="flex items-start gap-3">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'exclamation-triangle','class' => 'mt-0.5 h-5 w-5 flex-shrink-0 text-warning-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'exclamation-triangle','class' => 'mt-0.5 h-5 w-5 flex-shrink-0 text-warning-500']); ?>
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
                <div>
                    <p class="font-semibold"><?php echo e(__('messages.subscription_required')); ?></p>
                    <p class="mt-1"><?php echo e(__('messages.subscription_expired_text')); ?></p>
                    <a href="<?php echo e(route('account.billing')); ?>" wire:navigate
                       class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-warning-fg underline hover:text-warning-fg-strong">
                        <?php echo e(__('messages.go_to_billing')); ?>

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
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="mb-6 rounded-lg border border-danger-border bg-danger-surface px-4 py-3 text-sm text-danger-fg-strong">
            <p class="font-semibold"><?php echo e(__('products.fix_errors')); ?></p>
            <ul class="mt-1 list-inside list-disc">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form wire:submit="save" x-data="edzDirty()">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->wizardSteps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wizardStep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo $__env->make($wizardStep['partial'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php echo $__env->make('livewire.merchant.products.form.create-option-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
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
                        x-show="step < <?php echo e(ProductWizardSteps::count()); ?>"
                        @click="$wire.nextStep()"
                        wire:loading.attr="disabled"
                        :disabled="! $wire.hasActiveSubscription"
                        class="edz-btn edz-btn--primary"
                        :class="{ 'opacity-50 cursor-not-allowed': ! $wire.hasActiveSubscription }">
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
                        x-show="step === <?php echo e(ProductWizardSteps::LAST_STEP); ?>"
                        wire:loading.attr="disabled"
                        :disabled="! $wire.hasActiveSubscription"
                        class="edz-btn edz-btn--primary"
                        :class="{ 'opacity-50 cursor-not-allowed': ! $wire.hasActiveSubscription }">
                    <span wire:loading.remove><?php echo e(__('products.save_product')); ?></span>
                    <span wire:loading class="inline-flex items-center gap-1">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"></path></svg>
                        <?php echo e(__('buttons.processing')); ?>

                    </span>
                </button>
            </div>
        </div>
    </form>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/products/form.blade.php ENDPATH**/ ?>