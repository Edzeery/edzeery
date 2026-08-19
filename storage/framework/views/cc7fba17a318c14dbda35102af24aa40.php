<?php

use App\Enums\Store\LandingTemplateEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\StoreThemeSetting;
use Illuminate\Support\Facades\DB;

?>

<div x-data="{ previewOpen: <?php if ((object) ('showPreview') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showPreview'->value()); ?>')<?php echo e('showPreview'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showPreview'); ?>')<?php endif; ?> }"
     x-on:open-preview.window="previewOpen = true"
     x-on:close-preview.window="previewOpen = false">

    <?php if (isset($component)) { $__componentOriginal64446345db7363332d7ff2707d878bc4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal64446345db7363332d7ff2707d878bc4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.page-header','data' => ['title' => ''.e(__('merchant_panel.storefront_template')).'','description' => ''.e(__('merchant_panel.storefront_template_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(__('merchant_panel.storefront_template')).'','description' => ''.e(__('merchant_panel.storefront_template_desc')).'']); ?>
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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(currentStore()?->isPubliclyActive()): ?>
        <div class="mb-6 p-4 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-xl">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-accent-100 dark:bg-accent-800/50 flex items-center justify-center">
                        <ion-icon name="storefront-outline" class="text-xl text-accent-600 dark:text-accent-400"></ion-icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-accent-700 dark:text-accent-300"><?php echo e(__('storefront.your_store_link')); ?></p>
                        <p class="text-xs text-accent-500 dark:text-accent-400 font-mono"><?php echo e(currentStore()->public_url); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                        x-data="{ copied: false }"
                        x-on:click="navigator.clipboard.writeText('<?php echo e(currentStore()->public_url); ?>'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="edz-btn edz-btn--secondary edz-btn--sm">
                        <ion-icon :name="copied ? 'checkmark-outline' : 'copy-outline'" class="w-4 h-4 me-1"></ion-icon>
                        <span x-text="copied ? '<?php echo e(__('buttons.copied')); ?>' : '<?php echo e(__('buttons.copy_link')); ?>'"></span>
                    </button>
                    <button type="button" wire:click="openPreview" class="edz-btn edz-btn--primary edz-btn--sm">
                        <ion-icon name="eye-outline" class="w-4 h-4 me-1"></ion-icon>
                        <?php echo e(__('storefront.preview')); ?>

                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form wire:submit="save" x-data="edzDirty()">

        
        <div class="edz-card edz-card--padded mb-6">
            <div class="mb-6">
                <h3 class="text-base font-semibold text-ink"><?php echo e(__('merchant_panel.storefront_template')); ?></h3>
                <p class="text-xs text-ink-400 mt-1"><?php echo e(__('merchant_panel.storefront_template_desc')); ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = LandingTemplateEnum::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $key = $case->value;
                    ?>
                    <div class="relative cursor-pointer group"
                         x-on:click="$wire.set('template', '<?php echo e($key); ?>')"
                         :class="$wire.template === '<?php echo e($key); ?>' ? 'ring-2 ring-accent-500 shadow-lg shadow-accent-500/10' : 'ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-gray-300 dark:hover:ring-gray-600'">

                        <div class="rounded-xl overflow-hidden transition-all duration-200">

                            <div class="relative h-44 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 overflow-hidden">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($key === 'single_product'): ?>
                                    <div class="absolute inset-0 p-4 flex gap-3">
                                        <div class="flex-1 space-y-2">
                                            <div class="h-2 w-12 rounded bg-accent-400/60"></div>
                                            <div class="h-4 w-3/4 rounded bg-gray-300 dark:bg-gray-600"></div>
                                            <div class="h-3 w-full rounded bg-gray-200 dark:bg-gray-700"></div>
                                            <div class="h-3 w-2/3 rounded bg-gray-200 dark:bg-gray-700"></div>
                                            <div class="h-8 w-24 rounded-lg mt-3 bg-accent-500/80"></div>
                                        </div>
                                        <div class="w-28 h-full rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <ion-icon name="image-outline" class="text-2xl text-gray-400 dark:text-gray-500"></ion-icon>
                                        </div>
                                    </div>
                                <?php elseif($key === 'catalog'): ?>
                                    <div class="absolute inset-0 p-3 flex flex-col gap-2">
                                        <div class="flex gap-1.5">
                                            <div class="h-5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                            <div class="h-5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                            <div class="h-5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                                        </div>
                                        <div class="flex-1 grid grid-cols-3 gap-1.5">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 6; $i++): ?>
                                                <div class="rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                    <ion-icon name="bag-outline" class="text-xs text-gray-400"></ion-icon>
                                                </div>
                                            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="absolute inset-0 p-3 flex flex-col gap-2">
                                        <div class="h-8 w-8 rounded-full bg-accent-400/40 mx-auto"></div>
                                        <div class="h-2 w-20 rounded bg-gray-300 dark:bg-gray-600 mx-auto"></div>
                                        <div class="flex-1 grid grid-cols-2 gap-1.5 mt-1">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 4; $i++): ?>
                                                <div class="rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                    <ion-icon name="bag-outline" class="text-xs text-gray-400"></ion-icon>
                                                </div>
                                            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <div x-show="$wire.template === '<?php echo e($key); ?>'"
                                     x-transition
                                     class="absolute top-2 right-2 w-6 h-6 rounded-full bg-accent-500 text-white flex items-center justify-center shadow-lg">
                                    <ion-icon name="checkmark-outline" class="text-sm"></ion-icon>
                                </div>
                            </div>

                            <div class="p-4 bg-white dark:bg-gray-800">
                                <p class="text-sm font-semibold text-ink"><?php echo e($case->label()); ?></p>
                                <p class="text-xs text-ink-400 mt-1 leading-relaxed"><?php echo e($case->description()); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="edz-card edz-card--padded mb-6">
            <div class="mb-6">
                <h3 class="text-base font-semibold text-ink"><?php echo e(__('stores.theme')); ?></h3>
                <p class="text-xs text-ink-400 mt-1"><?php echo e(__('merchant_panel.theme_desc')); ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="edz-label"><?php echo e(__('stores.primary_color')); ?></label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model.live="primary_color"
                            class="w-10 h-10 rounded-lg border-2 border-gray-200 dark:border-gray-600 cursor-pointer" />
                        <input type="text" wire:model.live="primary_color"
                            class="edz-input flex-1 font-mono text-sm" placeholder="#4f46e5" />
                    </div>
                </div>

                <div>
                    <label class="edz-label"><?php echo e(__('stores.secondary_color')); ?></label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model.live="secondary_color"
                            class="w-10 h-10 rounded-lg border-2 border-gray-200 dark:border-gray-600 cursor-pointer" />
                        <input type="text" wire:model.live="secondary_color"
                            class="edz-input flex-1 font-mono text-sm" placeholder="#7c3aed" />
                    </div>
                </div>

                <div>
                    <label class="edz-label"><?php echo e(__('stores.font_family')); ?></label>
                    <select wire:model.live="font_family" class="edz-input">
                        <option value="Cairo">Cairo</option>
                        <option value="Tajawal">Tajawal</option>
                        <option value="Inter">Inter</option>
                        <option value="Poppins">Poppins</option>
                        <option value="Playfair Display">Playfair Display</option>
                        <option value="Montserrat">Montserrat</option>
                        <option value="Lato">Lato</option>
                        <option value="Nunito">Nunito</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <p class="text-xs text-ink-400 mb-3 font-medium"><?php echo e(__('merchant_panel.live_preview')); ?></p>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="h-10 px-5 rounded-lg text-white text-sm font-semibold flex items-center"
                         :style="'background-color: ' + $wire.primary_color">
                        <?php echo e(__('storefront.add_to_cart')); ?>

                    </div>
                    <div class="h-10 px-5 rounded-lg text-white text-sm font-semibold flex items-center"
                         :style="'background-color: ' + $wire.secondary_color">
                        <?php echo e(__('storefront.checkout')); ?>

                    </div>
                    <div class="h-10 px-5 rounded-lg border-2 text-sm font-semibold flex items-center"
                         :style="'border-color: ' + $wire.primary_color + '; color: ' + $wire.primary_color">
                        <?php echo e(__('storefront.options')); ?>

                    </div>
                    <span class="text-sm text-ink" :style="'font-family: ' + $wire.font_family + ', sans-serif'">
                        <?php echo e(__('storefront.products')); ?> Aa
                    </span>
                </div>
            </div>
        </div>

        
        <div class="edz-card edz-card--padded mb-6">
            <div class="mb-6">
                <h3 class="text-base font-semibold text-ink"><?php echo e(__('merchant_panel.homepage_sections')); ?></h3>
                <p class="text-xs text-ink-400 mt-1"><?php echo e(__('merchant_panel.homepage_sections_desc')); ?></p>
            </div>

            <?php
                $availableSections = [
                    'hero'         => ['label' => __('merchant_panel.section_hero'),         'description' => __('merchant_panel.section_hero_desc'),         'icon' => 'image-outline'],
                    'social_proof' => ['label' => __('merchant_panel.section_social_proof'), 'description' => __('merchant_panel.section_social_proof_desc'), 'icon' => 'shield-checkmark-outline'],
                    'faq'          => ['label' => __('merchant_panel.section_faq'),           'description' => __('merchant_panel.section_faq_desc'),           'icon' => 'help-circle-outline'],
                    'cta'          => ['label' => __('merchant_panel.section_cta'),            'description' => __('merchant_panel.section_cta_desc'),            'icon' => 'megaphone-outline'],
                    'categories'   => ['label' => __('merchant_panel.section_categories'),    'description' => __('merchant_panel.section_categories_desc'),    'icon' => 'grid-outline'],
                    'brands'       => ['label' => __('merchant_panel.section_brands'),        'description' => __('merchant_panel.section_brands_desc'),        'icon' => 'ribbon-outline'],
                    'description'  => ['label' => __('merchant_panel.section_description'),   'description' => __('merchant_panel.section_description_desc'),   'icon' => 'document-text-outline'],
                ];
            ?>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border transition-all duration-200 cursor-pointer
                        <?php echo e(in_array($key, $sections) ? 'border-accent-500 bg-accent-50 dark:bg-accent-900/10 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'); ?>">
                        <input type="checkbox" name="sections[]" value="<?php echo e($key); ?>"
                               wire:model.live="sections"
                               class="mt-0.5 rounded border-gray-300 text-accent-600 focus:ring-accent-500" />
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <ion-icon name="<?php echo e($section['icon']); ?>" class="text-base text-ink-400 shrink-0"></ion-icon>
                                <p class="text-sm font-medium text-ink truncate"><?php echo e($section['label']); ?></p>
                            </div>
                            <p class="text-xs text-ink-400 mt-0.5"><?php echo e($section['description']); ?></p>
                        </div>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex items-center gap-2 mb-4">
                    <ion-icon name="create-outline" class="text-lg text-ink-400"></ion-icon>
                    <h4 class="text-sm font-semibold text-ink"><?php echo e(__('merchant_panel.section_content')); ?></h4>
                </div>
                <p class="text-xs text-ink-400 mb-5"><?php echo e(__('merchant_panel.section_content_desc')); ?></p>

                <?php
                    $sectionIcons = [
                        'hero'         => 'image-outline',
                        'social_proof' => 'shield-checkmark-outline',
                        'faq'          => 'help-circle-outline',
                        'cta'          => 'megaphone-outline',
                        'categories'   => 'grid-outline',
                        'brands'       => 'ribbon-outline',
                        'description'  => 'document-text-outline',
                    ];
                ?>

                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($key, $sections)): ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden transition-all"
                                 x-data="{ open: $wire.expanded_section === '<?php echo e($key); ?>' }">
                                
                                <button type="button"
                                    x-on:click="
                                        open = !open;
                                        $wire.set('expanded_section', open ? '<?php echo e($key); ?>' : '');
                                    "
                                    class="w-full px-5 py-4 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                    <div class="flex items-center gap-3">
                                        <ion-icon name="<?php echo e($sectionIcons[$key]); ?>" class="text-lg text-accent-500"></ion-icon>
                                        <span class="text-sm font-semibold text-ink"><?php echo e($section['label']); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($section_content[$key])): ?>
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0" title="Customized"></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <ion-icon :name="open ? 'chevron-up-outline' : 'chevron-down-outline'" class="text-ink-400 transition-transform" x-bind:class="open && 'rotate-180'"></ion-icon>
                                </button>

                                
                                <div x-show="open" x-transition.duration.200ms>
                                    <div class="p-5 space-y-4">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($key === 'hero'): ?>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.hero_title')); ?></label>
                                                <input type="text"
                                                    wire:model.live="section_content.hero.title"
                                                    class="edz-input"
                                                    placeholder="<?php echo e(__('merchant_panel.hero_title_placeholder')); ?>" />
                                            </div>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.hero_description')); ?></label>
                                                <textarea wire:model.live="section_content.hero.description"
                                                    class="edz-input" rows="2"
                                                    placeholder="<?php echo e(__('merchant_panel.hero_description_placeholder')); ?>"></textarea>
                                            </div>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.hero_button_text')); ?></label>
                                                <input type="text"
                                                    wire:model.live="section_content.hero.button_text"
                                                    class="edz-input"
                                                    placeholder="<?php echo e(__('storefront.order_now')); ?>" />
                                            </div>

                                        <?php elseif($key === 'social_proof'): ?>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.section_title')); ?></label>
                                                <input type="text"
                                                    wire:model.live="section_content.social_proof.title"
                                                    class="edz-input" />
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [0, 1, 2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700 space-y-3">
                                                    <p class="text-xs font-medium text-ink-400 uppercase tracking-wider"><?php echo e(__('merchant_panel.item')); ?> <?php echo e($i + 1); ?></p>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="edz-label text-xs"><?php echo e(__('merchant_panel.item_title')); ?></label>
                                                            <input type="text"
                                                                wire:model.live="section_content.social_proof.items.<?php echo e($i); ?>.title"
                                                                class="edz-input" />
                                                        </div>
                                                        <div>
                                                            <label class="edz-label text-xs"><?php echo e(__('merchant_panel.item_description')); ?></label>
                                                            <input type="text"
                                                                wire:model.live="section_content.social_proof.items.<?php echo e($i); ?>.description"
                                                                class="edz-input" />
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <?php elseif($key === 'faq'): ?>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.section_title')); ?></label>
                                                <input type="text"
                                                    wire:model.live="section_content.faq.title"
                                                    class="edz-input" />
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [0, 1, 2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700 space-y-3">
                                                    <p class="text-xs font-medium text-ink-400 uppercase tracking-wider"><?php echo e(__('merchant_panel.faq_item')); ?> <?php echo e($i + 1); ?></p>
                                                    <div>
                                                        <label class="edz-label text-xs"><?php echo e(__('merchant_panel.question')); ?></label>
                                                        <input type="text"
                                                            wire:model.live="section_content.faq.items.<?php echo e($i); ?>.question"
                                                            class="edz-input" />
                                                    </div>
                                                    <div>
                                                        <label class="edz-label text-xs"><?php echo e(__('merchant_panel.answer')); ?></label>
                                                        <textarea wire:model.live="section_content.faq.items.<?php echo e($i); ?>.answer"
                                                            class="edz-input" rows="2"></textarea>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <?php elseif($key === 'cta'): ?>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.cta_title')); ?></label>
                                                <input type="text"
                                                    wire:model.live="section_content.cta.title"
                                                    class="edz-input"
                                                    placeholder="<?php echo e(__('storefront.ready_to_order')); ?>" />
                                            </div>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.cta_description')); ?></label>
                                                <input type="text"
                                                    wire:model.live="section_content.cta.description"
                                                    class="edz-input"
                                                    placeholder="<?php echo e(__('storefront.get_yours_now')); ?>" />
                                            </div>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.hero_button_text')); ?></label>
                                                <input type="text"
                                                    wire:model.live="section_content.cta.button_text"
                                                    class="edz-input"
                                                    placeholder="<?php echo e(__('storefront.order_now')); ?>" />
                                            </div>

                                        <?php elseif($key === 'categories'): ?>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.section_title')); ?></label>
                                                <input type="text"
                                                    wire:model.live="section_content.categories.title"
                                                    class="edz-input" />
                                            </div>

                                        <?php elseif($key === 'brands'): ?>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.section_title')); ?></label>
                                                <input type="text"
                                                    wire:model.live="section_content.brands.title"
                                                    class="edz-input" />
                                            </div>

                                        <?php elseif($key === 'description'): ?>
                                            <div>
                                                <label class="edz-label"><?php echo e(__('merchant_panel.section_title')); ?></label>
                                                <input type="text"
                                                    wire:model.live="section_content.description.title"
                                                    class="edz-input" />
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="flex items-center justify-between">
            <button type="button" wire:click="openPreview"
                class="edz-btn edz-btn--secondary">
                <ion-icon name="eye-outline" class="w-4 h-4 me-1"></ion-icon>
                <?php echo e(__('storefront.preview_template')); ?>

            </button>
            <button type="submit" class="edz-btn edz-btn--primary">
                <ion-icon name="save-outline" class="w-4 h-4 me-1"></ion-icon>
                <?php echo e(__('merchant_panel.save_template')); ?>

            </button>
        </div>
    </form>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(currentStore()?->isPubliclyActive()): ?>
        <div x-show="previewOpen" x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="previewOpen = false; $wire.set('showPreview', false)"></div>
            <div class="relative w-full h-[90vh] max-w-7xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-accent-100 dark:bg-accent-900/30 flex items-center justify-center">
                            <ion-icon name="eye-outline" class="text-lg text-accent-600 dark:text-accent-400"></ion-icon>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink"><?php echo e(__('storefront.preview')); ?> — <?php echo e(currentStore()->name); ?></p>
                            <p class="text-xs text-ink-400 font-mono"><?php echo e(currentStore()->public_url); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="<?php echo e(currentStore()->public_url . '?preview=1'); ?>" target="_blank" rel="noopener noreferrer"
                           class="edz-btn edz-btn--secondary edz-btn--sm">
                            <ion-icon name="open-outline" class="w-4 h-4 me-1"></ion-icon>
                            <?php echo e(__('storefront.open_in_new_tab')); ?>

                        </a>
                        <button type="button" @click="previewOpen = false; $wire.set('showPreview', false)"
                            class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                            <ion-icon name="close-outline" class="text-xl text-ink-400"></ion-icon>
                        </button>
                    </div>
                </div>

                <div class="flex-1 relative bg-white">
                    <iframe
                        x-ref="previewFrame"
                        x-show="previewOpen"
                        src="<?php echo e(currentStore()->public_url . '?preview=1'); ?>"
                        class="absolute inset-0 w-full h-full border-0"
                        loading="lazy"
                    ></iframe>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/storefront-settings.blade.php ENDPATH**/ ?>