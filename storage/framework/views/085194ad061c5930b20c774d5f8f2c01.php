<?php

use App\Domains\Plan\Services\FeatureUsageService;
use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StoreStatusEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

?>

<div class="min-h-screen bg-surface-primary flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-2xl">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-ink"><?php echo e(__('stores.create_your_store')); ?></h1>
            <p class="mt-1 text-ink-muted"><?php echo e(__('stores.setup_steps_hint')); ?></p>
        </div>

        
        <div class="mb-8 flex items-center justify-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [1 => __('stores.step_info'), 2 => __('stores.step_settings'), 3 => __('stores.step_seo'), 4 => __('stores.step_design'), 5 => __('stores.step_template')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" wire:click="$set('step', <?php echo e($s); ?>)"
                        class="flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition
                               <?php echo e($step === $s ? 'bg-brand-600 text-white' : ($step > $s ? 'bg-success-100 text-success-700' : 'bg-surface-secondary text-ink-muted')); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step > $s): ?>
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'h-3.5 w-3.5']); ?>
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
                    <?php else: ?>
                        <?php echo e($s); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php echo e($label); ?>

                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="edz-card p-6">
            <form wire:submit="<?php echo e($step === 5 ? 'createStore' : 'nextStep'); ?>" x-data="edzDirty()">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 1): ?>
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink"><?php echo e(__('stores.store_information')); ?></h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.store_name')); ?></label>
                                <input type="text" class="edz-input" wire:model.live="name" placeholder="<?php echo e(__('stores.name_placeholder')); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-danger-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.slug')); ?></label>
                                <input type="text" class="edz-input" wire:model="slug" readonly>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-danger-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.description')); ?></label>
                            <textarea class="edz-input" wire:model="description" rows="3" placeholder="<?php echo e(__('stores.description_placeholder')); ?>"></textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.logo')); ?></label>
                                <input type="file" class="edz-input" wire:model="logo" accept="image/*">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.cover')); ?></label>
                                <input type="file" class="edz-input" wire:model="cover" accept="image/*">
                            </div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 2): ?>
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink"><?php echo e(__('stores.general_settings')); ?></h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.currency')); ?></label>
                                <select class="edz-select" wire:model="currency">
                                    <option value="DZD">DZD</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.symbol')); ?></label>
                                <input type="text" class="edz-input" wire:model="currency_symbol">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.language')); ?></label>
                                <select class="edz-select" wire:model="language">
                                    <option value="ar"><?php echo e(__('stores.lang_arabic')); ?></option>
                                    <option value="en"><?php echo e(__('stores.lang_english')); ?></option>
                                    <option value="fr"><?php echo e(__('stores.lang_french')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model="inventory_tracking" class="h-4 w-4 rounded border-surface-border">
                                <?php echo e(__('stores.inventory_tracking')); ?>

                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model="guest_checkout" class="h-4 w-4 rounded border-surface-border">
                                <?php echo e(__('stores.guest_checkout')); ?>

                            </label>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 3): ?>
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink"><?php echo e(__('stores.seo')); ?></h2>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.meta_title')); ?></label>
                            <input type="text" class="edz-input" wire:model="meta_title" placeholder="<?php echo e(__('stores.meta_title_placeholder')); ?>">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.meta_description')); ?></label>
                            <textarea class="edz-input" wire:model="meta_description" rows="2" placeholder="<?php echo e(__('stores.meta_description_placeholder')); ?>"></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.meta_keywords')); ?></label>
                            <input type="text" class="edz-input" wire:model="meta_keywords" placeholder="<?php echo e(__('stores.meta_keywords_placeholder')); ?>">
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 4): ?>
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink"><?php echo e(__('stores.design')); ?></h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.primary_color')); ?></label>
                                <input type="color" class="h-10 w-full rounded border border-surface-border" wire:model="primary_color">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.secondary_color')); ?></label>
                                <input type="color" class="h-10 w-full rounded border border-surface-border" wire:model="secondary_color">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-ink"><?php echo e(__('stores.font_family')); ?></label>
                            <select class="edz-select" wire:model="font_family">
                                <option value="Cairo">Cairo</option>
                                <option value="Roboto">Roboto</option>
                            </select>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 5): ?>
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-ink"><?php echo e(__('stores.step_template')); ?></h2>
                        <p class="text-sm text-ink-muted"><?php echo e(__('stores.template_description')); ?></p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                                'single_product' => __('merchant_panel.template_single'),
                                'catalog'        => __('merchant_panel.template_catalog'),
                                'brand'          => __('merchant_panel.template_brand'),
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tplKey => $tplLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="edz-card edz-card--padded cursor-pointer border-2 transition-all duration-200 text-center
                                    <?php echo e($landing_template === $tplKey ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'); ?>">
                                    <input type="radio" name="landing_template" value="<?php echo e($tplKey); ?>"
                                           wire:model.live="landing_template" class="sr-only" />
                                    <div class="py-3">
                                        <p class="font-semibold text-ink"><?php echo e($tplLabel); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($landing_template === $tplKey): ?>
                                            <span class="mt-1 inline-block text-xs text-accent-600 font-medium"><?php echo e(__('buttons.selected')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="mt-6 flex items-center justify-between">
                    <div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step > 1): ?>
                            <button type="button" class="edz-btn edz-btn--ghost" wire:click="prevStep"><?php echo e(__('buttons.back')); ?></button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step < 5): ?>
                            <button type="submit" class="edz-btn edz-btn--primary"><?php echo e(__('buttons.next')); ?></button>
                        <?php else: ?>
                            <button type="submit" class="edz-btn edz-btn--primary edz-btn--lg"><?php echo e(__('stores.launch_my_store')); ?></button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <p class="mt-4 text-center text-sm text-ink-muted">
            <a href="<?php echo e(route('merchant.choose-store')); ?>" class="text-brand-600 hover:underline"><?php echo e(__('stores.back_to_selection')); ?></a>
        </p>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/create-store.blade.php ENDPATH**/ ?>