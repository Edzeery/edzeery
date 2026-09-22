<?php

use App\Domains\Account\DTOs\SettingsData;
use App\Domains\Account\Services\AccountService;
use App\Http\Requests\Account\Settings\UpdateSettingsRequest;

?>

<div class="max-w-3xl mx-auto space-y-6">
    <form wire:submit="saveSettings" x-data="edzDirty()">
        
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'sun','class' => 'w-5 h-5 text-purple-600 dark:text-purple-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sun','class' => 'w-5 h-5 text-purple-600 dark:text-purple-400']); ?>
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
                    </div>
                    <div>
                        <h3 class="edz-card__title"><?php echo e(__('merchant_panel.language_region')); ?></h3>
                        <p class="text-xs text-ink-muted mt-0.5"><?php echo e(__('merchant_panel.personal_data_desc')); ?></p>
                    </div>
                </div>
            </div>
            <div class="edz-card--padded">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="edz-label"><?php echo e(__('general.language')); ?></label>
                        <select wire:model="language" class="edz-input">
                            <option value="ar"><?php echo e(__('merchant_panel.arabic')); ?></option>
                            <option value="en"><?php echo e(__('merchant_panel.english')); ?></option>
                            <option value="fr"><?php echo e(__('merchant_panel.french')); ?></option>
                            <option value="es"><?php echo e(__('merchant_panel.spanish')); ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="edz-label"><?php echo e(__('merchant_panel.timezone')); ?></label>
                        <select wire:model="timezone" class="edz-input">
                            <option value="Africa/Algiers">Africa/Algiers (GMT+1)</option>
                            <option value="Europe/Paris">Europe/Paris (GMT+1/+2)</option>
                            <option value="Europe/London">Europe/London (GMT+0/+1)</option>
                            <option value="America/New_York">America/New_York (GMT-5/-4)</option>
                            <option value="Asia/Dubai">Asia/Dubai (GMT+4)</option>
                        </select>
                    </div>
                    <div>
                        <label class="edz-label"><?php echo e(__('merchant_panel.date_format')); ?></label>
                        <select wire:model="date_format" class="edz-input">
                            <option value="Y-m-d">2026-08-18</option>
                            <option value="d/m/Y">18/08/2026</option>
                            <option value="m/d/Y">08/18/2026</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'moon','class' => 'w-5 h-5 text-amber-600 dark:text-amber-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'moon','class' => 'w-5 h-5 text-amber-600 dark:text-amber-400']); ?>
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
                    </div>
                    <div>
                        <h3 class="edz-card__title"><?php echo e(__('merchant_panel.appearance')); ?></h3>
                        <p class="text-xs text-ink-muted mt-0.5"><?php echo e(__('merchant_panel.theme_desc')); ?></p>
                    </div>
                </div>
            </div>
            <div class="edz-card--padded">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['light' => '☀️ ' . __('merchant_panel.theme_light'), 'dark' => '🌙 ' . __('merchant_panel.theme_dark'), 'system' => '💻 ' . __('merchant_panel.theme_system')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="theme" value="<?php echo e($value); ?>" class="sr-only peer" />
                            <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 text-center transition-all
                                        peer-checked:border-accent-500 peer-checked:bg-accent-50 dark:peer-checked:bg-accent-900/20
                                        hover:border-gray-300 dark:hover:border-gray-600">
                                <span class="text-sm font-medium text-ink"><?php echo e($label); ?></span>
                            </div>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'bell','class' => 'w-5 h-5 text-rose-600 dark:text-rose-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','class' => 'w-5 h-5 text-rose-600 dark:text-rose-400']); ?>
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
                    </div>
                    <div>
                        <h3 class="edz-card__title"><?php echo e(__('merchant_panel.notifications')); ?></h3>
                        <p class="text-xs text-ink-muted mt-0.5"><?php echo e(__('merchant_panel.notif_email')); ?></p>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-surface-border">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                    'email_notifications' => 'merchant_panel.notif_email',
                    'order_notifications' => 'merchant_panel.notif_orders',
                    'stock_notifications' => 'merchant_panel.notif_stock',
                    'marketing_notifications' => 'merchant_panel.notif_marketing',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-ink"><?php echo e(__($label)); ?></p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" wire:model="<?php echo e($field); ?>" class="peer sr-only" />
                            <div class="h-6 w-11 rounded-full bg-gray-300 dark:bg-gray-600 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-all peer-checked:bg-accent-500 peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="edz-btn edz-btn--primary">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check-circle','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','class' => 'w-4 h-4']); ?>
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
                <?php echo e(__('buttons.save')); ?>

            </button>
        </div>
    </form>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/account/personal-data.blade.php ENDPATH**/ ?>