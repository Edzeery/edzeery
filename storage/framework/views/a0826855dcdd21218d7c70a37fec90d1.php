<?php

use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\StoreSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

?>

<div>
    <?php if (isset($component)) { $__componentOriginal64446345db7363332d7ff2707d878bc4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal64446345db7363332d7ff2707d878bc4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.page-header','data' => ['title' => ''.e(__('merchant_panel.store_settings')).'','description' => ''.e(__('merchant_panel.store_settings_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(__('merchant_panel.store_settings')).'','description' => ''.e(__('merchant_panel.store_settings_desc')).'']); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal64446345db7363332d7ff2707d878bc4)): ?>
<?php $attributes = $__attributesOriginal64446345db7363332d7ff2707d878bc4; ?>
<?php unset($__attributesOriginal64446345db7363332d7ff2707d878bc4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal64446345db7363332d7ff2707d878bc4)): ?>
<?php $component = $__componentOriginal64446345db7363332d7ff2707d878bc4; ?>
<?php unset($__componentOriginal64446345db7363332d7ff2707d878bc4); ?>
<?php endif; ?>

    <form wire:submit="save" x-data="edzDirty()">
        
        <div class="edz-card edz-card--padded mb-6">
            <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                <ion-icon name="color-palette-outline" class="text-lg text-accent-500"></ion-icon>
                <?php echo e(__('merchant_panel.branding')); ?>

            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="edz-label"><?php echo e(__('stores.logo')); ?></label>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-20 h-20 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border overflow-hidden flex items-center justify-center bg-neutral-secondary dark:bg-dark-secondary shrink-0">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logo): ?>
                                <img src="<?php echo e($logo->temporaryUrl()); ?>" class="w-full h-full object-cover" />
                            <?php elseif(currentStore()?->logo): ?>
                                <img src="<?php echo e(asset('storage/' . currentStore()->logo)); ?>"
                                    class="w-full h-full object-cover" />
                            <?php else: ?>
                                <ion-icon name="image-outline" class="text-2xl text-ink-muted"></ion-icon>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="logo" accept="image/*" class="edz-input text-sm" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div>
                    <label class="edz-label"><?php echo e(__('stores.cover')); ?></label>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-32 h-20 rounded-xl border-2 border-dashed border-neutral-border dark:border-dark-border overflow-hidden flex items-center justify-center bg-neutral-secondary dark:bg-dark-secondary shrink-0">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cover): ?>
                                <img src="<?php echo e($cover->temporaryUrl()); ?>" class="w-full h-full object-cover" />
                            <?php elseif(currentStore()?->cover): ?>
                                <img src="<?php echo e(asset('storage/' . currentStore()->cover)); ?>"
                                    class="w-full h-full object-cover" />
                            <?php else: ?>
                                <ion-icon name="image-outline" class="text-2xl text-ink-muted"></ion-icon>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="cover" accept="image/*" class="edz-input text-sm" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['cover'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div>
                    <label class="edz-label"><?php echo e(__('merchant_panel.favicon')); ?></label>
                    <p class="text-xs text-ink-muted mb-2"><?php echo e(__('merchant_panel.favicon_help')); ?></p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg border-2 border-dashed border-neutral-border dark:border-dark-border overflow-hidden flex items-center justify-center bg-neutral-secondary dark:bg-dark-secondary shrink-0">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($favicon): ?>
                                <img src="<?php echo e($favicon->temporaryUrl()); ?>" class="w-full h-full object-cover" />
                            <?php elseif(currentStore()?->seo?->favicon): ?>
                                <img src="<?php echo e(asset('storage/' . currentStore()->seo->favicon)); ?>" class="w-full h-full object-cover" />
                            <?php else: ?>
                                <ion-icon name="globe-outline" class="text-xl text-ink-muted"></ion-icon>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="favicon" accept="image/png,image/svg+xml" class="edz-input text-sm" />
                            <p class="text-[11px] text-ink-muted mt-1"><?php echo e(__('merchant_panel.favicon_help')); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['favicon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="edz-card edz-card--padded mb-6">
            <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                <ion-icon name="information-circle-outline" class="text-lg text-accent-500"></ion-icon>
                <?php echo e(__('merchant_panel.general_info')); ?>

            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="edz-label"><?php echo e(__('general.store_name')); ?></label>
                    <input type="text" wire:model="name" class="edz-input" required />
                </div>

                <div>
                    <label class="edz-label"><?php echo e(__('merchant_panel.phone')); ?></label>
                    <input type="tel" wire:model="phone" class="edz-input" placeholder="0XXX XX XX XX" />
                </div>

                <div class="md:col-span-2">
                    <label class="edz-label"><?php echo e(__('general.description')); ?></label>
                    <textarea wire:model="description" rows="3" class="edz-input"></textarea>
                </div>
            </div>
        </div>

        
        <div class="edz-card edz-card--padded mb-6">
            <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                <ion-icon name="card-outline" class="text-lg text-accent-500"></ion-icon>
                <?php echo e(__('merchant_panel.commerce')); ?>

            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="edz-label"><?php echo e(__('merchant_panel.currency')); ?></label>
                    <select wire:model="currency" class="edz-input">
                        <option value="DZD">DZD — <?php echo e(__('merchant_panel.algerian_dinar')); ?></option>
                        <option value="USD">USD — US Dollar</option>
                        <option value="EUR">EUR — Euro</option>
                        <option value="MAD">MAD — Moroccan Dirham</option>
                        <option value="TND">TND — Tunisian Dinar</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" id="guest_checkout" wire:model="guest_checkout" class="edz-checkbox" />
                    <label for="guest_checkout"
                        class="edz-label mb-0"><?php echo e(__('merchant_panel.guest_checkout')); ?></label>
                </div>
            </div>
        </div>

        
        <div class="edz-card edz-card--padded mb-6">
            <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                <ion-icon name="cube-outline" class="text-lg text-accent-500"></ion-icon>
                <?php echo e(__('merchant_panel.inventory_group')); ?>

            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <label
                    class="flex items-start gap-3 p-4 rounded-xl border transition-all cursor-pointer
                    <?php echo e($inventory_tracking ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10' : 'border-neutral-border dark:border-dark-border'); ?>">
                    <input type="checkbox" wire:model="inventory_tracking"
                        class="mt-0.5 rounded border-neutral-border text-accent-600 focus:ring-accent-500" />
                    <div>
                        <p class="text-sm font-medium text-ink"><?php echo e(__('merchant_panel.inventory_tracking')); ?></p>
                        <p class="text-xs text-ink-muted mt-0.5"><?php echo e(__('merchant_panel.inventory_tracking_desc')); ?></p>
                    </div>
                </label>

                <label
                    class="flex items-start gap-3 p-4 rounded-xl border transition-all cursor-pointer
                    <?php echo e($allow_backorder ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10' : 'border-neutral-border dark:border-dark-border'); ?>">
                    <input type="checkbox" wire:model="allow_backorder"
                        class="mt-0.5 rounded border-neutral-border text-accent-600 focus:ring-accent-500" />
                    <div>
                        <p class="text-sm font-medium text-ink"><?php echo e(__('merchant_panel.allow_backorder')); ?></p>
                        <p class="text-xs text-ink-muted mt-0.5"><?php echo e(__('merchant_panel.allow_backorder_desc')); ?></p>
                    </div>
                </label>

                <label
                    class="flex items-start gap-3 p-4 rounded-xl border transition-all cursor-pointer
                    <?php echo e($show_out_of_stock ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10' : 'border-neutral-border dark:border-dark-border'); ?>">
                    <input type="checkbox" wire:model="show_out_of_stock"
                        class="mt-0.5 rounded border-neutral-border text-accent-600 focus:ring-accent-500" />
                    <div>
                        <p class="text-sm font-medium text-ink"><?php echo e(__('merchant_panel.show_out_of_stock')); ?></p>
                        <p class="text-xs text-ink-muted mt-0.5"><?php echo e(__('merchant_panel.show_out_of_stock_desc')); ?></p>
                    </div>
                </label>
            </div>
        </div>

        
        <div class="edz-card edz-card--padded mb-6">
            <h3 class="text-base font-semibold text-ink mb-5 flex items-center gap-2">
                <ion-icon name="language-outline" class="text-lg text-accent-500"></ion-icon>
                <?php echo e(__('merchant_panel.languages')); ?>

            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="edz-label"><?php echo e(__('merchant_panel.default_language')); ?></label>
                    <select wire:model="language" class="edz-input">
                        <option value="ar"><?php echo e(__('merchant_panel.arabic')); ?></option>
                        <option value="fr"><?php echo e(__('merchant_panel.french')); ?></option>
                        <option value="en"><?php echo e(__('merchant_panel.english')); ?></option>
                        <option value="es"><?php echo e(__('merchant_panel.spanish')); ?></option>
                    </select>
                </div>

                <div>
                    <label class="edz-label"><?php echo e(__('merchant_panel.supported_languages')); ?></label>
                    <div class="flex flex-wrap gap-2 mt-2" x-data>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['ar' => __('merchant_panel.arabic'), 'fr' => __('merchant_panel.french'), 'en' => __('merchant_panel.english'), 'es' => __('merchant_panel.spanish')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm cursor-pointer transition"
                                :class="$wire.supported_languages.includes('<?php echo e($code); ?>') ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10 text-accent-700 dark:text-accent-400' : 'border-neutral-border dark:border-dark-border text-ink-muted hover:border-neutral-border'">
                                <input type="checkbox" value="<?php echo e($code); ?>"
                                    wire:model.live="supported_languages" class="sr-only" />
                                <span
                                    class="w-2 h-2 rounded-full"
                                    :class="$wire.supported_languages.includes('<?php echo e($code); ?>') ? 'bg-accent-500' : 'bg-neutral-border dark:bg-dark-border'"></span>
                                <?php echo e($label); ?>

                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <p class="text-xs text-ink-muted mt-2"><?php echo e(__('merchant_panel.supported_languages_desc')); ?></p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="edz-btn edz-btn--primary">
                <ion-icon name="save-outline" class="w-4 h-4 me-1"></ion-icon>
                <?php echo e(__('buttons.save')); ?>

            </button>
        </div>
    </form>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\store-settings.blade.php ENDPATH**/ ?>