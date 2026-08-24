<?php

use App\Enums\Store\LandingTemplateEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Services\Stores\StorefrontThemeService;
use App\Support\Storefront\StorefrontSections;
use Illuminate\Validation\ValidationException;

?>

<?php
    $store = currentStore();
    $canPreview = $store && $store->isPubliclyActive();

    $tabs = [
        'template' => [
            'icon' => 'color-palette',
            'label' => __('merchant_panel.tab_template'),
            'desc' => __('merchant_panel.tab_template_desc'),
        ],
        'design' => [
            'icon' => 'swatch',
            'label' => __('merchant_panel.tab_design'),
            'desc' => __('merchant_panel.tab_design_desc'),
        ],
        'sections' => [
            'icon' => 'list-bullet',
            'label' => __('merchant_panel.tab_sections'),
            'desc' => __('merchant_panel.tab_sections_desc'),
        ],
    ];

    $sectionConfig = [
        'hero' => ['icon' => 'image', 'label' => __('merchant_panel.section_hero')],
        'social_proof' => ['icon' => 'shield-check', 'label' => __('merchant_panel.section_social_proof')],
        'faq' => ['icon' => 'help-circle', 'label' => __('merchant_panel.section_faq')],
        'cta' => ['icon' => 'megaphone', 'label' => __('merchant_panel.section_cta')],
        'categories' => ['icon' => 'grid', 'label' => __('merchant_panel.section_categories')],
        'brands' => ['icon' => 'ribbon', 'label' => __('merchant_panel.section_brands')],
        'description' => ['icon' => 'document-text', 'label' => __('merchant_panel.section_description')],
    ];

    $sectionDescriptions = [
        'hero' => __('merchant_panel.section_hero_desc'),
        'social_proof' => __('merchant_panel.section_social_proof_desc'),
        'faq' => __('merchant_panel.section_faq_desc'),
        'cta' => __('merchant_panel.section_cta_desc'),
        'categories' => __('merchant_panel.section_categories_desc'),
        'brands' => __('merchant_panel.section_brands_desc'),
        'description' => __('merchant_panel.section_description_desc'),
    ];
?>


<div
    x-data="{
        activeTab: 'template',
        selectedTemplate: null,
        previewOpen: false,

        get previewUrl() { return this.$root.dataset.previewUrl || '' },

        selectTemplate(key) {
            this.selectedTemplate = key;
            this.$wire.template = key;
        },
        openPreview() {
            if (!this.previewUrl) { return; }
            this.previewOpen = true;
        }
    }"
    x-init="selectedTemplate = $root.dataset.selectedTemplate"
    data-selected-template="<?php echo e($template); ?>"
    data-preview-url="<?php echo e($canPreview ? $store->public_url : ''); ?>"
    x-effect="document.documentElement.style.overflow = previewOpen ? 'hidden' : ''">

    
    <script>
        window.edzCopy = window.edzCopy || function (text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }

            return new Promise(function (resolve) {
                var helper = document.createElement('textarea');
                helper.value = text;
                document.body.appendChild(helper);
                helper.select();
                try { document.execCommand('copy'); } finally { helper.remove(); }
                resolve();
            });
        };
    </script>

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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canPreview): ?>
        <div
            class="mb-6 p-4 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-xl">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-accent-100 dark:bg-accent-800/50 flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'storefront','class' => 'w-5 h-5 text-accent-600 dark:text-accent-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'storefront','class' => 'w-5 h-5 text-accent-600 dark:text-accent-400']); ?>
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
                        <p class="text-sm font-medium text-accent-700 dark:text-accent-300">
                            <?php echo e(__('storefront.your_store_link')); ?></p>
                        <p class="text-xs text-accent-500 dark:text-accent-400 font-mono"><?php echo e($store->public_url); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                        data-copy-url="<?php echo e($store->public_url); ?>"
                        x-data="{ copied: false }"
                        x-on:click="window.edzCopy($el.dataset.copyUrl).then(function () {
                            this.copied = true;
                            setTimeout(function () { this.copied = false }, 2000);
                        }.bind(this))"
                        class="edz-btn edz-btn--secondary edz-btn--sm">
                        <span x-show="!copied">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'copy','class' => 'w-4 h-4 me-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'copy','class' => 'w-4 h-4 me-1']); ?>
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
                        </span>
                        <span x-show="copied" x-cloak>
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-4 h-4 me-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-4 h-4 me-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?></span>
                        <span x-show="!copied"><?php echo e(__('buttons.copy_link')); ?></span>
                        <span x-show="copied" x-cloak><?php echo e(__('buttons.copied')); ?></span>
                    </button>
                    <button type="button" x-on:click="openPreview()" data-preview-url="<?php echo e($store->public_url); ?>" class="edz-btn edz-btn--primary edz-btn--sm">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'eye','class' => 'w-4 h-4 me-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'eye','class' => 'w-4 h-4 me-1']); ?>
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
                        <?php echo e(__('storefront.preview')); ?> <?php echo e(__('merchant_panel.store')); ?>

                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form wire:submit="save">

        <div class="flex flex-col lg:flex-row gap-6">

            
            <div class="lg:hidden shrink-0">
                <?php echo $__env->make('livewire.merchant.storefront-settings.partials.tabs-nav', [
                    'tabs' => $tabs,
                    'variant' => 'mobile',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            
            <div class="hidden lg:block w-64 shrink-0">
                <div class="edz-card p-2 sticky top-6">
                    <?php echo $__env->make('livewire.merchant.storefront-settings.partials.tabs-nav', [
                        'tabs' => $tabs,
                        'variant' => 'desktop',
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            
            <div class="flex-1 min-w-0">

                
                <div id="tab-panel-template" role="tabpanel" aria-labelledby="tab-btn-template" tabindex="0"
                    x-show="activeTab === 'template'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="edz-card edz-card--padded">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-ink"><?php echo e(__('merchant_panel.storefront_template')); ?>

                            </h3>
                            <p class="text-xs text-ink-muted mt-1"><?php echo e(__('merchant_panel.storefront_template_desc')); ?>

                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = LandingTemplateEnum::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $key = $case->value; ?>
                                <div class="relative cursor-pointer group"
                                    x-on:click="selectTemplate('<?php echo e($key); ?>')"
                                    x-bind:data-selected="selectedTemplate === '<?php echo e($key); ?>' ? 'true' : null">

                                    <div class="rounded-2xl overflow-hidden transition-all duration-200"
                                        :class="selectedTemplate === '<?php echo e($key); ?>'
                                            ?
                                            'ring-2 ring-accent-500 shadow-lg' :
                                            'ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-gray-300 dark:hover:ring-gray-600'">

                                        <div
                                            class="relative h-40 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 overflow-hidden">
                                            <?php echo $__env->make(
                                                'livewire.merchant.storefront-settings.partials.template-thumbnails',
                                                ['key' => $key]
                                            , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                                            <div class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full flex items-center justify-center shadow-lg transition-all duration-200"
                                                :class="selectedTemplate === '<?php echo e($key); ?>'
                                                    ?
                                                    'bg-accent-500 text-white scale-100 opacity-100' :
                                                    'bg-gray-400/50 text-white scale-75 opacity-0'">
                                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-5 h-5']); ?>
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
                                        </div>

                                        <div class="p-4 bg-white dark:bg-gray-800">
                                            <p class="text-sm font-semibold text-ink"><?php echo e($case->label()); ?></p>
                                            <p class="text-xs text-ink-muted mt-1 leading-relaxed">
                                                <?php echo e($case->description()); ?></p>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canPreview): ?>
                                                <a :href="'<?php echo e($store->public_url); ?>?preview_template=<?php echo e($key); ?>'"
                                                    target="_blank" rel="noopener noreferrer" x-on:click.stop
                                                    class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-accent-600 dark:text-accent-400 hover:text-accent-700 dark:hover:text-accent-300 transition">
                                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'eye','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'eye','class' => 'w-4 h-4']); ?>
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
                                                    <?php echo e(__('storefront.preview_template')); ?>

                                                </a>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700"
                            x-show="selectedTemplate === 'single_product'" x-cloak
                            x-data="{ open: false }" @click.outside="open = false">
                            <label class="edz-label"><?php echo e(__('merchant_panel.template_product')); ?></label>

                            <?php $chosenPickerProduct = $this->chosenPickerProduct; ?>

                            <div class="relative sm:max-w-md">
                                <button type="button"
                                    x-on:click="open = !open"
                                    class="edz-input w-full flex items-center justify-between text-start"
                                    aria-haspopup="listbox" :aria-expanded="open">
                                    <span class="truncate flex items-center gap-2 min-w-0">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($chosenPickerProduct): ?>
                                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check-circle','class' => 'w-4 h-4 store-text-primary shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','class' => 'w-4 h-4 store-text-primary shrink-0']); ?>
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
                                            <span class="truncate font-medium"><?php echo e($chosenPickerProduct->name); ?></span>
                                        <?php else: ?>
                                            <span class="text-ink-muted"><?php echo e(__('merchant_panel.template_product_auto')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200','xBind:class' => 'open ? \'rotate-180\' : \'\'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-4 h-4 text-gray-400 shrink-0 ms-2 transition-transform duration-200','x-bind:class' => 'open ? \'rotate-180\' : \'\'']); ?>
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

                                <div x-show="open" x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="absolute z-30 mt-2 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl overflow-hidden">
                                    <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                                        <input type="search" wire:model.live.debounce.250ms="picker_query"
                                            placeholder="<?php echo e(__('storefront.search_products')); ?>"
                                            class="edz-input w-full text-sm">
                                    </div>

                                    <ul class="max-h-56 overflow-y-auto py-1" role="listbox">
                                        <li>
                                            <button type="button" data-picker-value=""
                                                x-on:click="$wire.set('section_content.single_product.product_id', $el.dataset.pickerValue); $wire.set('picker_query', ''); open = false"
                                                class="w-full text-start px-3 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center justify-between gap-2 <?php echo e($chosenPickerProduct ? '' : 'store-text-primary font-semibold'); ?>">
                                                <span><?php echo e(__('merchant_panel.template_product_auto')); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $chosenPickerProduct): ?>
                                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-4 h-4 shrink-0']); ?>
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
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </button>
                                        </li>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->pickerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pickerProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $isChosen = (string) $chosenPickerProduct?->id === (string) $pickerProduct->id; ?>
                                            <li wire:key="picker-opt-<?php echo e($pickerProduct->id); ?>">
                                                <button type="button" data-picker-value="<?php echo e($pickerProduct->id); ?>"
                                                    x-on:click="$wire.set('section_content.single_product.product_id', $el.dataset.pickerValue); $wire.set('picker_query', ''); open = false"
                                                    class="w-full text-start px-3 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center justify-between gap-2 <?php echo e($isChosen ? 'store-text-primary font-semibold' : ''); ?>">
                                                    <span class="truncate"><?php echo e($pickerProduct->name); ?></span>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isChosen): ?>
                                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-4 h-4 shrink-0']); ?>
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
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </button>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->pickerOptions->isEmpty()): ?>
                                            <li class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo e(__('storefront.no_results_found')); ?>

                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </ul>
                                </div>
                            </div>

                            <p class="text-xs text-ink-muted mt-1"><?php echo e(__('merchant_panel.template_product_hint')); ?></p>
                        </div>
                    </div>
                </div>

                
                <div id="tab-panel-design" role="tabpanel" aria-labelledby="tab-btn-design" tabindex="0"
                    x-show="activeTab === 'design'" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="edz-card edz-card--padded">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-ink"><?php echo e(__('stores.theme')); ?></h3>
                            <p class="text-xs text-ink-muted mt-1"><?php echo e(__('merchant_panel.theme_desc')); ?></p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <label class="edz-label" for="primary-color"><?php echo e(__('stores.primary_color')); ?></label>
                                <div class="flex items-center gap-3">
                                    <input id="primary-color" type="color" wire:model="primary_color"
                                        class="w-10 h-10 rounded-lg border-2 border-gray-200 dark:border-gray-600 cursor-pointer shrink-0" />
                                    <input type="text" wire:model="primary_color"
                                        class="edz-input flex-1 font-mono text-sm" placeholder="#4f46e5" />
                                </div>
                            </div>

                            <div>
                                <label class="edz-label"
                                    for="secondary-color"><?php echo e(__('stores.secondary_color')); ?></label>
                                <div class="flex items-center gap-3">
                                    <input id="secondary-color" type="color" wire:model="secondary_color"
                                        class="w-10 h-10 rounded-lg border-2 border-gray-200 dark:border-gray-600 cursor-pointer shrink-0" />
                                    <input type="text" wire:model="secondary_color"
                                        class="edz-input flex-1 font-mono text-sm" placeholder="#7c3aed" />
                                </div>
                            </div>

                            <div>
                                <label class="edz-label" for="font-family"><?php echo e(__('stores.font_family')); ?></label>
                                <select id="font-family" wire:model="font_family" class="edz-input">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = StorefrontSections::FONTS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $font): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($font); ?>"><?php echo e($font); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                        </div>

                        
                        <div
                            class="mt-6 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <p class="text-xs text-ink-400 mb-3 font-medium"><?php echo e(__('merchant_panel.live_preview')); ?>

                            </p>
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
                                <span class="text-sm text-ink"
                                    :style="'font-family: \'' + $wire.font_family + '\', sans-serif'">
                                    <?php echo e(__('storefront.products')); ?> Aa
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div id="tab-panel-sections" role="tabpanel" aria-labelledby="tab-btn-sections" tabindex="0"
                    x-show="activeTab === 'sections'" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="edz-card edz-card--padded">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-ink"><?php echo e(__('merchant_panel.homepage_sections')); ?>

                            </h3>
                            <p class="text-xs text-ink-muted mt-1"><?php echo e(__('merchant_panel.homepage_sections_desc')); ?>

                            </p>
                        </div>

                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sectionConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($key === 'hero'): ?>
                                    
                                    <div x-show="$wire.template !== 'single_product'">
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <label
                                    class="flex items-start gap-3 p-3.5 rounded-xl border transition-all duration-200 cursor-pointer"
                                    :class="$wire.sections.includes('<?php echo e($key); ?>') ?
                                        'border-accent-500 bg-accent-50 dark:bg-accent-900/10 shadow-sm' :
                                        'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
                                    <input type="checkbox" value="<?php echo e($key); ?>" wire:model="sections"
                                        class="mt-0.5 rounded border-gray-300 text-accent-600 focus:ring-accent-500" />
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => $section['icon'],'class' => 'w-4 h-4 text-ink-400 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($section['icon']),'class' => 'w-4 h-4 text-ink-400 shrink-0']); ?>
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
                                            <p class="text-sm font-medium text-ink truncate"><?php echo e($section['label']); ?>

                                            </p>
                                        </div>
                                        <p class="text-xs text-ink-400 mt-0.5"><?php echo e($sectionDescriptions[$key]); ?></p>
                                    </div>
                                </label>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($key === 'hero'): ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <div class="flex items-center gap-2 mb-4">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'edit','class' => 'w-4 h-4 text-ink-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit','class' => 'w-4 h-4 text-ink-400']); ?>
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
                                <h4 class="text-sm font-semibold text-ink"><?php echo e(__('merchant_panel.section_content')); ?>

                                </h4>
                            </div>
                            <p class="text-xs text-ink-400 mb-5"><?php echo e(__('merchant_panel.section_content_desc')); ?></p>

                            <div class="space-y-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sectionConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($key === 'hero'): ?>
                                        <div x-show="$wire.template !== 'single_product'">
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php echo $__env->make(
                                            'livewire.merchant.storefront-settings.partials.section-editor',
                                            [
                                                'key' => $key,
                                                'section' => $section,
                                                'description' => $sectionDescriptions[$key],
                                            ]
                                        , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($key === 'hero'): ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        
        <div
            class="sticky bottom-0 mt-6 -mx-4 px-4 py-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-t border-gray-200 dark:border-gray-700/50 z-10">
            <div class="flex items-center justify-end gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canPreview): ?>
                    <a href="<?php echo e($store->public_url); ?>" target="_blank" rel="noopener noreferrer"
                        class="edz-btn edz-btn--secondary edz-btn--sm hidden sm:inline-flex">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'external-link','class' => 'w-4 h-4 me-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'external-link','class' => 'w-4 h-4 me-1']); ?>
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
                        <?php echo e(__('storefront.open_store')); ?>

                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button type="submit" wire:target="save" wire:loading.attr="disabled"
                    class="edz-btn edz-btn--primary disabled:opacity-60 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="save" class="inline-flex items-center">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'save','class' => 'w-4 h-4 me-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'save','class' => 'w-4 h-4 me-1']); ?>
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
                    </span>
                    <span wire:loading wire:target="save" class="inline-flex items-center">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-path','class' => 'w-4 h-4 me-1 animate-spin']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-path','class' => 'w-4 h-4 me-1 animate-spin']); ?>
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
                    </span>
                    <?php echo e(__('merchant_panel.save_template')); ?>

                </button>
            </div>
        </div>
    </form>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canPreview): ?>
        <?php echo $__env->make('livewire.merchant.storefront-settings.partials.preview-modal', ['store' => $store], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/storefront-settings.blade.php ENDPATH**/ ?>