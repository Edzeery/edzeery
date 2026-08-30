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

<div class="min-h-[80vh] flex items-center justify-center px-4 py-8"
     x-data="{
         transitioning: false,
         direction: 'forward',
         goTo(step) {
             if (step === $wire.step) return;
             this.direction = step > $wire.step ? 'forward' : 'backward';
             this.transitioning = true;
             setTimeout(() => {
                 $wire.set('step', step);
                 setTimeout(() => { this.transitioning = false; }, 50);
             }, 150);
         }
     }">

    <div class="w-full max-w-xl">

        
        <div class="text-center mb-8 animate-fade-up">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-100 dark:bg-brand-900/30 mb-4">
                <ion-icon name="storefront-outline" class="text-2xl text-brand-600 dark:text-brand-400"></ion-icon>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-ink tracking-tight">
                <?php echo e(__('stores.create_your_store')); ?>

            </h1>
            <p class="text-sm text-ink-muted mt-2">
                <?php echo e(__('stores.setup_steps_hint')); ?>

            </p>
        </div>

        
        <div class="mb-8 animate-fade-up" style="animation-delay: 0.1s">
            
            <div class="sm:hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-ink-muted">
                        <?php echo e(__('stores.step_of', ['current' => $step, 'total' => 5])); ?>

                    </span>
                    <span class="text-xs font-medium text-brand-600 dark:text-brand-400">
                        <?php echo e([1 => __('stores.step_info'), 2 => __('stores.step_settings'), 3 => __('stores.step_seo'), 4 => __('stores.step_design'), 5 => __('stores.step_template')][$step]); ?>

                    </span>
                </div>
                <div class="h-1.5 w-full bg-neutral-secondary dark:bg-dark-secondary rounded-full overflow-hidden">
                    <div class="h-full bg-brand-600 rounded-full transition-all duration-500 ease-out"
                         style="width: <?php echo e(($step / 5) * 100); ?>%"></div>
                </div>
            </div>

            
            <div class="hidden sm:flex items-center justify-between relative">
                
                <div class="absolute top-5 left-[10%] right-[10%] h-0.5 bg-neutral-secondary dark:bg-dark-secondary"></div>
                <div class="absolute top-5 left-[10%] h-0.5 bg-brand-600 transition-all duration-500 ease-out"
                     style="width: <?php echo e((($step - 1) / 4) * 80); ?>%"></div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [1 => __('stores.step_info'), 2 => __('stores.step_settings'), 3 => __('stores.step_seo'), 4 => __('stores.step_design'), 5 => __('stores.step_template')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="relative z-10 flex flex-col items-center cursor-pointer group" wire:key="step-<?php echo e($s); ?>">
                        <button type="button"
                                @click="goTo(<?php echo e($s); ?>)"
                                class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold border-2 transition-all duration-300
                                    <?php if($step > $s): ?>
                                        bg-brand-600 border-brand-600 text-white shadow-sm
                                    <?php elseif($step === $s): ?>
                                        bg-brand-600 border-brand-600 text-white shadow-md ring-4 ring-brand-100 dark:ring-brand-900/30
                                    <?php else: ?>
                                        bg-surface-primary dark:bg-dark-surface border-neutral-border dark:border-dark-border text-ink-muted group-hover:border-brand-300 group-hover:text-brand-600
                                    <?php endif; ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step > $s): ?>
                                <ion-icon name="checkmark-outline" class="text-base"></ion-icon>
                            <?php else: ?>
                                <?php echo e($s); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                        <span class="mt-2 text-xs font-medium text-center
                            <?php if($step >= $s): ?> text-ink <?php else: ?> text-ink-muted <?php endif; ?>">
                            <?php echo e($label); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border rounded-2xl shadow-card animate-fade-up"
             style="animation-delay: 0.15s">
            <form wire:submit="<?php echo e($step === 5 ? 'createStore' : 'nextStep'); ?>" x-data="edzDirty()">

                <div class="p-6 sm:p-8">
                    
                    <div class="relative overflow-hidden min-h-[320px]">

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 1): ?>
                            <div class="space-y-5"
                                 x-show="true"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">

                                <div>
                                    <h2 class="text-lg font-semibold text-ink tracking-tight">
                                        <?php echo e(__('stores.store_information')); ?>

                                    </h2>
                                    <p class="text-sm text-ink-muted mt-0.5"><?php echo e(__('stores.step_1_desc')); ?></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.store_name')); ?></label>
                                    <input type="text"
                                           class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition placeholder:text-ink-soft"
                                           wire:model.live="name"
                                           placeholder="<?php echo e(__('stores.name_placeholder')); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1.5 text-xs font-medium text-red-500"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.slug')); ?></label>
                                    <div class="flex items-center gap-0">
                                        <span class="inline-flex items-center px-3 py-2.5 rounded-l-xl border border-r-0 border-neutral-border dark:border-dark-border bg-neutral-secondary dark:bg-dark-secondary text-sm text-ink-muted">
                                            <?php echo e(request()->getHost()); ?>/
                                        </span>
                                        <input type="text"
                                               class="flex-1 px-4 py-2.5 rounded-r-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm font-medium
                                                      focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                                               wire:model="slug" readonly>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1.5 text-xs font-medium text-red-500"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.description')); ?></label>
                                    <textarea class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm min-h-[80px] resize-y
                                                   focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition placeholder:text-ink-soft"
                                              wire:model="description"
                                              placeholder="<?php echo e(__('stores.description_placeholder')); ?>"></textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.logo')); ?></label>
                                        <label class="flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border
                                                      hover:border-brand-400 dark:hover:border-brand-500 cursor-pointer transition group bg-surface-secondary dark:bg-dark-secondary">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logo): ?>
                                                <div class="relative w-full h-full flex items-center justify-center p-2">
                                                    <img src="<?php echo e($logo->temporaryUrl()); ?>" class="max-h-full max-w-full object-contain rounded-lg">
                                                     <button type="button" x-on:click="$wire.set('logo', null)"
                                                            class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center text-xs hover:bg-red-600 transition">
                                                        <ion-icon name="close-outline" class="text-sm"></ion-icon>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <ion-icon name="image-outline" class="text-2xl text-ink-soft group-hover:text-brand-500 transition mb-1"></ion-icon>
                                                <span class="text-xs text-ink-muted text-center px-2"><?php echo e(__('stores.drag_drop_logo')); ?></span>
                                                <span class="text-[10px] text-ink-soft mt-0.5"><?php echo e(__('stores.image_formats_hint')); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <input type="file" wire:model="logo" accept="image/*" class="sr-only">
                                        </label>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-1.5 text-xs font-medium text-red-500"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>

                                    
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.cover')); ?></label>
                                        <label class="flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border
                                                      hover:border-brand-400 dark:hover:border-brand-500 cursor-pointer transition group bg-surface-secondary dark:bg-dark-secondary">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cover): ?>
                                                <div class="relative w-full h-full flex items-center justify-center p-2">
                                                    <img src="<?php echo e($cover->temporaryUrl()); ?>" class="max-h-full max-w-full object-cover rounded-lg">
                                                     <button type="button" x-on:click="$wire.set('cover', null)"
                                                            class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center text-xs hover:bg-red-600 transition">
                                                        <ion-icon name="close-outline" class="text-sm"></ion-icon>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <ion-icon name="cloud-upload-outline" class="text-2xl text-ink-soft group-hover:text-brand-500 transition mb-1"></ion-icon>
                                                <span class="text-xs text-ink-muted text-center px-2"><?php echo e(__('stores.drag_drop_cover')); ?></span>
                                                <span class="text-[10px] text-ink-soft mt-0.5"><?php echo e(__('stores.image_formats_hint')); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <input type="file" wire:model="cover" accept="image/*" class="sr-only">
                                        </label>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['cover'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-1.5 text-xs font-medium text-red-500"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 2): ?>
                            <div class="space-y-5"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">

                                <div>
                                    <h2 class="text-lg font-semibold text-ink tracking-tight">
                                        <?php echo e(__('stores.general_settings')); ?>

                                    </h2>
                                    <p class="text-sm text-ink-muted mt-0.5"><?php echo e(__('stores.step_2_desc')); ?></p>
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.currency')); ?></label>
                                        <select class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm appearance-none
                                                       focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                                                wire:model="currency">
                                            <option value="DZD">DZD</option>
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.symbol')); ?></label>
                                        <input type="text"
                                               class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm
                                                      focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                                               wire:model="currency_symbol">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.language')); ?></label>
                                        <select class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm appearance-none
                                                       focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                                                wire:model="language">
                                            <option value="ar"><?php echo e(__('stores.lang_arabic')); ?></option>
                                            <option value="en"><?php echo e(__('stores.lang_english')); ?></option>
                                            <option value="fr"><?php echo e(__('stores.lang_french')); ?></option>
                                        </select>
                                    </div>
                                </div>

                                
                                <div class="space-y-3">
                                    <label class="flex items-center justify-between p-4 rounded-xl bg-surface-secondary dark:bg-dark-secondary border border-neutral-border/50 dark:border-dark-border/50 cursor-pointer group hover:border-brand-300 dark:hover:border-brand-600 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                                <ion-icon name="cube-outline" class="text-brand-600 dark:text-brand-400 text-lg"></ion-icon>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-ink"><?php echo e(__('stores.inventory_tracking')); ?></span>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input type="checkbox" wire:model="inventory_tracking" class="sr-only peer">
                                            <div class="w-11 h-6 bg-neutral-border dark:bg-dark-border rounded-full peer-checked:bg-brand-600 transition-colors"></div>
                                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-sm transform peer-checked:translate-x-5 transition-transform"></div>
                                        </div>
                                    </label>

                                    <label class="flex items-center justify-between p-4 rounded-xl bg-surface-secondary dark:bg-dark-secondary border border-neutral-border/50 dark:border-dark-border/50 cursor-pointer group hover:border-brand-300 dark:hover:border-brand-600 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                                <ion-icon name="person-outline" class="text-brand-600 dark:text-brand-400 text-lg"></ion-icon>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-ink"><?php echo e(__('stores.guest_checkout')); ?></span>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input type="checkbox" wire:model="guest_checkout" class="sr-only peer">
                                            <div class="w-11 h-6 bg-neutral-border dark:bg-dark-border rounded-full peer-checked:bg-brand-600 transition-colors"></div>
                                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-sm transform peer-checked:translate-x-5 transition-transform"></div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 3): ?>
                            <div class="space-y-5"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">

                                <div>
                                    <h2 class="text-lg font-semibold text-ink tracking-tight">
                                        <?php echo e(__('stores.seo')); ?>

                                    </h2>
                                    <p class="text-sm text-ink-muted mt-0.5"><?php echo e(__('stores.step_3_desc')); ?></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.meta_title')); ?></label>
                                    <input type="text"
                                           class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition placeholder:text-ink-soft"
                                           wire:model="meta_title"
                                           placeholder="<?php echo e(__('stores.meta_title_placeholder')); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['meta_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1.5 text-xs font-medium text-red-500"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.meta_description')); ?></label>
                                    <textarea class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm min-h-[72px] resize-y
                                                   focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition placeholder:text-ink-soft"
                                              wire:model="meta_description"
                                              placeholder="<?php echo e(__('stores.meta_description_placeholder')); ?>"></textarea>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['meta_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1.5 text-xs font-medium text-red-500"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.meta_keywords')); ?></label>
                                    <input type="text"
                                           class="w-full px-4 py-2.5 rounded-xl bg-surface-primary dark:bg-dark-surface border border-neutral-border dark:border-dark-border text-ink text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition placeholder:text-ink-soft"
                                           wire:model="meta_keywords"
                                           placeholder="<?php echo e(__('stores.meta_keywords_placeholder')); ?>">
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 4): ?>
                            <div class="space-y-5"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">

                                <div>
                                    <h2 class="text-lg font-semibold text-ink tracking-tight">
                                        <?php echo e(__('stores.design')); ?>

                                    </h2>
                                    <p class="text-sm text-ink-muted mt-0.5"><?php echo e(__('stores.step_4_desc')); ?></p>
                                </div>

                                
                                <div class="grid grid-cols-2 gap-4">
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.primary_color')); ?></label>
                                        <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-secondary dark:bg-dark-secondary border border-neutral-border/50 dark:border-dark-border/50">
                                            <div class="relative">
                                                <input type="color" wire:model="primary_color"
                                                       class="w-12 h-12 rounded-xl border-2 border-white dark:border-gray-700 shadow-sm cursor-pointer appearance-none bg-transparent [&::-webkit-color-swatch-wrapper]:p-0 [&::-webkit-color-swatch]:rounded-lg [&::-webkit-color-swatch]:border-none">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="h-3 rounded-full mb-1.5" style="background-color: <?php echo e($primary_color); ?>"></div>
                                                <span class="text-xs text-ink-muted font-mono uppercase"><?php echo e($primary_color); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div>
                                        <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.secondary_color')); ?></label>
                                        <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-secondary dark:bg-dark-secondary border border-neutral-border/50 dark:border-dark-border/50">
                                            <div class="relative">
                                                <input type="color" wire:model="secondary_color"
                                                       class="w-12 h-12 rounded-xl border-2 border-white dark:border-gray-700 shadow-sm cursor-pointer appearance-none bg-transparent [&::-webkit-color-swatch-wrapper]:p-0 [&::-webkit-color-swatch]:rounded-lg [&::-webkit-color-swatch]:border-none">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="h-3 rounded-full mb-1.5" style="background-color: <?php echo e($secondary_color); ?>"></div>
                                                <span class="text-xs text-ink-muted font-mono uppercase"><?php echo e($secondary_color); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div>
                                    <span class="text-xs font-medium text-ink-muted mb-2 block"><?php echo e(__('stores.color_preview')); ?></span>
                                    <div class="flex flex-wrap gap-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['#000000', '#465fff', '#039855', '#d92d20', '#b54708', '#7a2e0e', '#6366f1', '#0ea5e9']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $preset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button type="button"
                                                    x-on:click="$wire.set('primary_color', '<?php echo e($preset); ?>')"
                                                    class="w-8 h-8 rounded-lg border-2 transition-all duration-200 hover:scale-110
                                                           <?php echo e($primary_color === $preset ? 'border-ink dark:border-white shadow-md scale-110' : 'border-transparent'); ?>"
                                                    style="background-color: <?php echo e($preset); ?>">
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>

                                
                                <div>
                                    <label class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('stores.font_family')); ?></label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Cairo' => __('stores.font_cairo'), 'Inter' => __('stores.font_inter'), 'Tajawal' => __('stores.font_tajawal')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fontKey => $fontLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button type="button"
                                                    x-on:click="$wire.set('font_family', '<?php echo e($fontKey); ?>')"
                                                    class="p-3 rounded-xl border-2 text-center transition-all duration-200
                                                           <?php echo e($font_family === $fontKey
                                                              ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20 shadow-sm'
                                                              : 'border-neutral-border dark:border-dark-border hover:border-brand-300 dark:hover:border-brand-600'); ?>">
                                                <span class="text-sm font-semibold text-ink" style="font-family: '<?php echo e($fontKey); ?>', sans-serif">
                                                    <?php echo e($fontLabel); ?>

                                                </span>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>

                                
                                <div class="p-4 rounded-xl border border-neutral-border/50 dark:border-dark-border/50 bg-surface-secondary/50 dark:bg-dark-secondary/50">
                                    <span class="text-[11px] font-medium text-ink-muted uppercase tracking-wider mb-2 block"><?php echo e(__('stores.color_preview')); ?></span>
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 px-4 rounded-lg text-white text-sm font-semibold flex items-center"
                                             style="background-color: <?php echo e($primary_color); ?>">
                                            Button
                                        </div>
                                        <div class="h-8 px-4 rounded-lg text-sm font-semibold border"
                                             style="border-color: <?php echo e($primary_color); ?>; color: <?php echo e($primary_color); ?>">
                                            Outline
                                        </div>
                                        <div class="flex-1 h-2 rounded-full" style="background: linear-gradient(to right, <?php echo e($primary_color); ?>, <?php echo e($secondary_color); ?>)"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 5): ?>
                            <div class="space-y-5"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">

                                <div>
                                    <h2 class="text-lg font-semibold text-ink tracking-tight">
                                        <?php echo e(__('stores.step_template')); ?>

                                    </h2>
                                    <p class="text-sm text-ink-muted mt-0.5"><?php echo e(__('stores.template_description')); ?></p>
                                </div>

                                <?php
                                    $templates = [
                                        'single_product' => [
                                            'label' => __('merchant_panel.template_single'),
                                            'desc' => __('stores.template_single_desc'),
                                            'icon' => 'cube-outline',
                                        ],
                                        'catalog' => [
                                            'label' => __('merchant_panel.template_catalog'),
                                            'desc' => __('stores.template_catalog_desc'),
                                            'icon' => 'grid-outline',
                                        ],
                                        'brand' => [
                                            'label' => __('merchant_panel.template_brand'),
                                            'desc' => __('stores.template_brand_desc'),
                                            'icon' => 'colorPalette-outline',
                                        ],
                                    ];
                                ?>

                                <div class="space-y-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tplKey => $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <button type="button"
                                                x-on:click="$wire.set('landing_template', '<?php echo e($tplKey); ?>')"
                                                class="w-full flex items-start gap-4 p-4 rounded-xl border-2 text-start transition-all duration-200
                                                       <?php if($landing_template === $tplKey): ?>
                                                           border-brand-500 bg-brand-50/50 dark:bg-brand-900/15 shadow-sm ring-1 ring-brand-500/20
                                                       <?php else: ?>
                                                           border-neutral-border dark:border-dark-border hover:border-brand-300 dark:hover:border-brand-600 hover:bg-surface-secondary/50 dark:hover:bg-dark-secondary/50
                                                       <?php endif; ?>">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5
                                                        <?php if($landing_template === $tplKey): ?> bg-brand-100 dark:bg-brand-900/30 <?php else: ?> bg-neutral-secondary dark:bg-dark-secondary <?php endif; ?>">
                                                <ion-icon name="<?php echo e($tpl['icon']); ?>"
                                                          class="text-lg <?php if($landing_template === $tplKey): ?> text-brand-600 dark:text-brand-400 <?php else: ?> text-ink-muted <?php endif; ?>">
                                                </ion-icon>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-ink"><?php echo e($tpl['label']); ?></span>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($landing_template === $tplKey): ?>
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-brand-100 dark:bg-brand-900/30 text-[10px] font-semibold text-brand-700 dark:text-brand-300">
                                                            <ion-icon name="checkmark-circle" class="text-xs"></ion-icon>
                                                            <?php echo e(__('buttons.selected')); ?>

                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                                <p class="text-xs text-ink-muted mt-0.5 leading-relaxed"><?php echo e($tpl['desc']); ?></p>
                                            </div>
                                        </button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    </div>
                </div>

                
                <div class="px-6 sm:px-8 py-4 border-t border-neutral-border/50 dark:border-dark-border/50 flex items-center justify-between">
                    <div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step > 1): ?>
                            <button type="button"
                                    wire:click="prevStep"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-ink-muted
                                           hover:text-ink hover:bg-neutral-secondary dark:hover:bg-dark-secondary transition">
                                <ion-icon name="arrow-back-outline" class="text-base"></ion-icon>
                                <?php echo e(__('buttons.back')); ?>

                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step < 5): ?>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold
                                           hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition">
                                <?php echo e(__('buttons.next')); ?>

                                <ion-icon name="arrow-forward-outline" class="text-base"></ion-icon>
                            </button>
                        <?php else: ?>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-brand-600 text-white text-sm font-semibold
                                           hover:bg-brand-700 shadow-md shadow-brand-600/25 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="createStore">
                                    <ion-icon name="rocket-outline" class="text-base"></ion-icon>
                                </span>
                                <span wire:loading wire:target="createStore" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <?php echo e(__('buttons.processing')); ?>

                                </span>
                                <?php echo e(__('stores.launch_my_store')); ?>

                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

            </form>
        </div>

        
        <div class="text-center mt-6 animate-fade-up" style="animation-delay: 0.2s">
            <a href="<?php echo e(route('merchant.choose-store')); ?>"
               class="inline-flex items-center gap-1.5 text-sm text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition">
                <ion-icon name="arrow-back-outline" class="text-sm"></ion-icon>
                <?php echo e(__('stores.back_to_selection')); ?>

            </a>
        </div>

    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/create-store.blade.php ENDPATH**/ ?>