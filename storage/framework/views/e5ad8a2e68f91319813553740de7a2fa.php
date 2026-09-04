<?php

use App\Domains\Shipping\Contracts\DeliveryRatesManager;
use App\Domains\Shipping\Models\DeliveryPriceList;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\DeliveryRateCity;
use App\Domains\Shipping\Models\DeliveryRateListCity;
use App\Domains\Shipping\Models\DeliveryRateListState;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Products\Product;
use Illuminate\Support\Facades\DB;

?>

<div>
    <?php if (isset($component)) { $__componentOriginal64446345db7363332d7ff2707d878bc4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal64446345db7363332d7ff2707d878bc4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.page-header','data' => ['title' => ''.e(__('merchant_panel.announced_rates')).'','description' => ''.e(__('merchant_panel.announced_rates_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(__('merchant_panel.announced_rates')).'','description' => ''.e(__('merchant_panel.announced_rates_desc')).'']); ?>
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

    
    <div class="flex gap-1 p-1 bg-surface-secondary rounded-xl overflow-x-auto mb-5 max-w-md" role="tablist"
        aria-label="<?php echo e(__('merchant_panel.announced_rates')); ?>">
        <button type="button" role="tab"
            wire:click="setTab('company')"
            aria-selected="<?php echo e($tab === 'company' ? 'true' : 'false'); ?>"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 flex-1 justify-center
            <?php echo e($tab === 'company'
                ? 'bg-surface text-brand-fg shadow-sm'
                : 'text-ink-muted hover:text-ink-soft'); ?>">
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'w-4 h-4 shrink-0']); ?>
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
            <span><?php echo e(__('merchant_panel.announced_by_company')); ?></span>
        </button>
        <button type="button" role="tab"
            wire:click="setTab('lists')"
            aria-selected="<?php echo e($tab === 'lists' ? 'true' : 'false'); ?>"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200 flex-1 justify-center
            <?php echo e($tab === 'lists'
                ? 'bg-surface text-brand-fg shadow-sm'
                : 'text-ink-muted hover:text-ink-soft'); ?>">
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'list-bullet','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'list-bullet','class' => 'w-4 h-4 shrink-0']); ?>
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
            <span><?php echo e(__('merchant_panel.announced_by_list')); ?></span>
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'company'): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($providers)): ?>
        <div class="edz-card p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'banknotes','class' => 'w-8 h-8 text-ink-muted opacity-40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'banknotes','class' => 'w-8 h-8 text-ink-muted opacity-40']); ?>
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
            <p class="text-ink-muted"><?php echo e(__('merchant_panel.no_rates_yet')); ?></p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-5 items-start">

            
            <aside class="edz-card edz-card--padded lg:sticky lg:top-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-3">
                    <?php echo e(__('merchant_panel.select_company')); ?>

                </p>
                <div class="flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button"
                            wire:click="selectProvider('<?php echo e($provider['id']); ?>')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-start transition-colors whitespace-nowrap lg:whitespace-normal
                            <?php echo e($selectedProviderId === $provider['id'] ? 'bg-brand-surface ring-1 ring-brand-ring' : 'hover:bg-surface-secondary'); ?>">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck','class' => 'w-4 h-4 shrink-0 '.e($selectedProviderId === $provider['id'] ? 'text-brand-500' : 'text-ink-muted').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'w-4 h-4 shrink-0 '.e($selectedProviderId === $provider['id'] ? 'text-brand-500' : 'text-ink-muted').'']); ?>
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
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium text-ink truncate"><?php echo e($provider['name']); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($provider['carrier']): ?>
                                    <span class="block text-xs text-ink-muted truncate"><?php echo e($provider['carrier']); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($provider['rates_count'] > 0 && $selectedProviderId === $provider['id']): ?>
                                <span class="edz-badge edz-badge--neutral shrink-0"><?php echo e($provider['rates_count']); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </aside>

            
            <section>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $selectedProviderId): ?>
                    <div class="edz-card p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'list-bullet','class' => 'w-8 h-8 text-ink-muted opacity-40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'list-bullet','class' => 'w-8 h-8 text-ink-muted opacity-40']); ?>
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
                        <p class="text-ink-muted"><?php echo e(__('merchant_panel.select_company_hint')); ?></p>
                    </div>
                <?php else: ?>
                    <?php $currentProvider = collect($providers)->firstWhere('id', $selectedProviderId); ?>

                    
                    <div class="edz-card edz-card--padded mb-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-11 h-11 rounded-xl bg-brand-surface flex items-center justify-center shrink-0">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck','class' => 'w-5 h-5 text-brand-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'w-5 h-5 text-brand-500']); ?>
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
                                    <h2 class="font-semibold text-ink"><?php echo e($currentProvider['name'] ?? ''); ?></h2>
                                    <p class="text-sm text-ink-muted"><?php echo e(__('merchant_panel.announced_provider_desc')); ?></p>
                                </div>
                            </div>

                            <button type="button" wire:click="syncProvider"
                                class="edz-btn edz-btn--ghost edz-btn--sm" wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 pointer-events-none" wire:target="syncProvider">
                                <?php if (isset($component)) { $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.spinner','data' => ['wire:target' => 'syncProvider']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:target' => 'syncProvider']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c)): ?>
<?php $attributes = $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c; ?>
<?php unset($__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf4c9959d3f2732b60b7f028a5155a98c)): ?>
<?php $component = $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c; ?>
<?php unset($__componentOriginalf4c9959d3f2732b60b7f028a5155a98c); ?>
<?php endif; ?>
                                <span wire:loading.remove wire:target="syncProvider">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-path','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-path','class' => 'w-4 h-4']); ?>
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
                                <span><?php echo e($syncing ? __('merchant_panel.syncing_rates') : __('merchant_panel.sync_rates')); ?></span>
                            </button>
                        </div>
                    </div>

                    
                    <div class="edz-card overflow-hidden">
                        
                        <div class="grid grid-cols-12 gap-3 px-5 py-3 bg-surface-secondary text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            <div class="col-span-12 sm:col-span-6 lg:col-span-4"><?php echo e(__('merchant_panel.state')); ?></div>
                            <div class="col-span-6 sm:col-span-3 lg:col-span-2"><?php echo e(__('merchant_panel.home_cost')); ?></div>
                            <div class="col-span-6 sm:col-span-3 lg:col-span-2"><?php echo e(__('merchant_panel.office_cost')); ?></div>
                            <div class="hidden lg:block lg:col-span-2 text-end"><?php echo e(__('merchant_panel.source')); ?></div>
                            <div class="col-span-12 sm:col-span-12 lg:col-span-2 text-end"><?php echo e(__('merchant_panel.actions')); ?></div>
                        </div>

                        
                        <div class="border-t border-surface-border"></div>

                        <div class="divide-y divide-surface-border">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $cell = array_merge(
                                        ['home_cost' => '', 'office_cost' => '', 'source' => 'manual'],
                                        $ratesByState[$state['id']] ?? []
                                    );
                                ?>
                                <div wire:key="rate-row-<?php echo e($selectedProviderId); ?>-<?php echo e($state['id']); ?>"
                                     class="grid grid-cols-12 gap-3 px-5 py-3.5 items-center hover:bg-surface-secondary/60 transition-colors">
                                    <div class="col-span-12 sm:col-span-6 lg:col-span-4 flex items-center gap-2 min-w-0">
                                        <span class="text-sm font-medium text-ink truncate"><?php echo e($state['name']); ?></span>
                                    </div>

                                    
                                    <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                        <div class="relative max-w-[9rem]">
                                            <input type="number" step="0.01" min="0"
                                                value="<?php echo e($cell['home_cost']); ?>"
                                                wire:change="updateStateCost('<?php echo e($state['id']); ?>', 'home_cost', $event.target.value)"
                                                class="edz-input text-sm pr-8" placeholder="—">
                                            <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                        </div>
                                    </div>

                                    
                                    <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                        <div class="relative max-w-[9rem]">
                                            <input type="number" step="0.01" min="0"
                                                value="<?php echo e($cell['office_cost']); ?>"
                                                wire:change="updateStateCost('<?php echo e($state['id']); ?>', 'office_cost', $event.target.value)"
                                                class="edz-input text-sm pr-8" placeholder="—">
                                            <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                        </div>
                                    </div>

                                    <div class="hidden lg:block lg:col-span-2">
                                        <span class="edz-badge <?php echo e($cell['source'] === 'announced' ? 'edz-badge--info' : 'edz-badge--neutral'); ?>">
                                            <?php echo e($cell['source'] === 'announced' ? __('merchant_panel.source_announced') : __('merchant_panel.source_manual')); ?>

                                        </span>
                                    </div>

                                    
                                    <div class="col-span-12 sm:col-span-12 lg:col-span-2 lg:justify-self-end">
                                        <button type="button"
                                            wire:click="openStatePopup('<?php echo e($state['id']); ?>')"
                                            class="edz-btn edz-btn--ghost edz-btn--sm">
                                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'pencil','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pencil','class' => 'w-4 h-4']); ?>
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
                                            <?php echo e(__('merchant_panel.manage_municipalities')); ?>

                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($lists)): ?>
            <div class="edz-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'list-bullet','class' => 'w-8 h-8 text-ink-muted opacity-40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'list-bullet','class' => 'w-8 h-8 text-ink-muted opacity-40']); ?>
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
                <p class="text-ink-muted mb-5"><?php echo e(__('merchant_panel.no_lists_yet')); ?></p>
                <button type="button" wire:click="openListModal"
                    class="edz-btn edz-btn--primary edz-btn--sm">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'plus','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'w-4 h-4']); ?>
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
                    <?php echo e(__('merchant_panel.add_list')); ?>

                </button>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-5 items-start">

                
                <aside class="edz-card edz-card--padded lg:sticky lg:top-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            <?php echo e(__('merchant_panel.select_list')); ?>

                        </p>
                        <button type="button" wire:click="openListModal"
                            class="edz-btn edz-btn--primary edz-btn--sm shrink-0">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'plus','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'w-4 h-4']); ?>
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
                            <span class="hidden sm:inline"><?php echo e(__('merchant_panel.add_list')); ?></span>
                        </button>
                    </div>

                    <div class="flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $lists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button"
                                wire:click="selectList('<?php echo e($list['id']); ?>')"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-start transition-colors whitespace-nowrap lg:whitespace-normal
                                <?php echo e($selectedListId === $list['id'] ? 'bg-brand-surface ring-1 ring-brand-ring' : 'hover:bg-surface-secondary'); ?>">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'list-bullet','class' => 'w-4 h-4 shrink-0 '.e($selectedListId === $list['id'] ? 'text-brand-500' : 'text-ink-muted').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'list-bullet','class' => 'w-4 h-4 shrink-0 '.e($selectedListId === $list['id'] ? 'text-brand-500' : 'text-ink-muted').'']); ?>
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
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-medium text-ink truncate"><?php echo e($list['name']); ?></span>
                                    <span class="block text-xs text-ink-muted truncate">
                                        <?php echo e(__('merchant_panel.list_products_count', ['count' => $list['products_count']])); ?>

                                    </span>
                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $list['is_active']): ?>
                                    <span class="edz-badge edz-badge--warning shrink-0"><?php echo e(__('merchant_panel.list_inactive')); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </aside>

                
                <section>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $selectedListId): ?>
                        <div class="edz-card p-12 text-center">
                            <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'list-bullet','class' => 'w-8 h-8 text-ink-muted opacity-40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'list-bullet','class' => 'w-8 h-8 text-ink-muted opacity-40']); ?>
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
                            <p class="text-ink-muted"><?php echo e(__('merchant_panel.select_list_hint')); ?></p>
                        </div>
                    <?php else: ?>
                        <?php $currentList = collect($lists)->firstWhere('id', $selectedListId); ?>

                        
                        <div class="edz-card edz-card--padded mb-4">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-brand-surface flex items-center justify-center shrink-0">
                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'list-bullet','class' => 'w-5 h-5 text-brand-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'list-bullet','class' => 'w-5 h-5 text-brand-500']); ?>
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
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h2 class="font-semibold text-ink"><?php echo e($currentList['name'] ?? ''); ?></h2>
                                            <span class="edz-badge <?php echo e($currentList['is_active'] ? 'edz-badge--success' : 'edz-badge--warning'); ?>">
                                                <?php echo e($currentList['is_active'] ? __('merchant_panel.list_active') : __('merchant_panel.list_inactive')); ?>

                                            </span>
                                        </div>
                                        <p class="text-sm text-ink-muted mt-0.5">
                                            <?php echo e(__('merchant_panel.list_products_count', ['count' => $currentList['products_count'] ?? 0])); ?>

                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" wire:click="openListModal('<?php echo e($selectedListId); ?>')"
                                        class="edz-btn edz-btn--ghost edz-btn--sm">
                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'pencil','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pencil','class' => 'w-4 h-4']); ?>
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
                                        <?php echo e(__('merchant_panel.edit')); ?>

                                    </button>
                                    <button type="button" wire:click="toggleListActive('<?php echo e($selectedListId); ?>')"
                                        class="edz-btn edz-btn--ghost edz-btn--sm">
                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-path','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-path','class' => 'w-4 h-4']); ?>
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
                                        <?php echo e($currentList['is_active'] ? __('merchant_panel.list_disable') : __('merchant_panel.list_enable')); ?>

                                    </button>
                                    <button type="button" aria-label="<?php echo e(__('merchant_panel.confirm_delete_list')); ?>"
                                        class="edz-btn edz-btn--ghost edz-btn--sm"
                                        x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete()) await $wire.deleteList('<?php echo e($selectedListId); ?>') })()">
                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trash','class' => 'w-4 h-4 text-danger-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'w-4 h-4 text-danger-600']); ?>
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
                                        <?php echo e(__('merchant_panel.delete')); ?>

                                    </button>
                                </div>
                            </div>
                        </div>

                        
                        <div class="edz-card overflow-hidden">
                            <div class="grid grid-cols-12 gap-3 px-5 py-3 bg-surface-secondary text-xs font-semibold uppercase tracking-wide text-ink-muted">
                                <div class="col-span-12 sm:col-span-6 lg:col-span-4"><?php echo e(__('merchant_panel.state')); ?></div>
                                <div class="col-span-6 sm:col-span-3 lg:col-span-2"><?php echo e(__('merchant_panel.home_cost')); ?></div>
                                <div class="col-span-6 sm:col-span-3 lg:col-span-2"><?php echo e(__('merchant_panel.office_cost')); ?></div>
                                <div class="col-span-12 sm:col-span-12 lg:col-span-4 text-end"><?php echo e(__('merchant_panel.actions')); ?></div>
                            </div>

                            <div class="border-t border-surface-border"></div>

                            <div class="divide-y divide-surface-border">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $cell = array_merge(
                                            ['home_cost' => '', 'office_cost' => ''],
                                            $listRatesByState[$state['id']] ?? []
                                        );
                                    ?>
                                    <div wire:key="list-rate-row-<?php echo e($selectedListId); ?>-<?php echo e($state['id']); ?>"
                                         class="grid grid-cols-12 gap-3 px-5 py-3.5 items-center hover:bg-surface-secondary/60 transition-colors">
                                        <div class="col-span-12 sm:col-span-6 lg:col-span-4 flex items-center gap-2 min-w-0">
                                            <span class="text-sm font-medium text-ink truncate"><?php echo e($state['name']); ?></span>
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <div class="relative max-w-[9rem]">
                                                <input type="number" step="0.01" min="0"
                                                    value="<?php echo e($cell['home_cost']); ?>"
                                                    wire:change="updateListStateCost('<?php echo e($state['id']); ?>', 'home_cost', $event.target.value)"
                                                    class="edz-input text-sm pr-8" placeholder="—">
                                                <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                            </div>
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <div class="relative max-w-[9rem]">
                                                <input type="number" step="0.01" min="0"
                                                    value="<?php echo e($cell['office_cost']); ?>"
                                                    wire:change="updateListStateCost('<?php echo e($state['id']); ?>', 'office_cost', $event.target.value)"
                                                    class="edz-input text-sm pr-8" placeholder="—">
                                                <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                            </div>
                                        </div>

                                        <div class="col-span-12 sm:col-span-12 lg:col-span-4 lg:justify-self-end">
                                            <button type="button"
                                                wire:click="openListStatePopup('<?php echo e($state['id']); ?>')"
                                                class="edz-btn edz-btn--ghost edz-btn--sm">
                                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'pencil','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pencil','class' => 'w-4 h-4']); ?>
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
                                                <?php echo e(__('merchant_panel.manage_municipalities')); ?>

                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </section>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showStatePopup): ?>
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'showCloseButton' => false,'preventClose' => true,'size' => 'xl','wire:key' => 'state-popup-'.e($selectedProviderId).'-'.e($popupStateId).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isOpen' => true,'showCloseButton' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'preventClose' => true,'size' => 'xl','wire:key' => 'state-popup-'.e($selectedProviderId).'-'.e($popupStateId).'']); ?>
            <div class="p-6">
                
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-bold text-ink"><?php echo e($popupStateName); ?></h3>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                            wire:click="closeStatePopup">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-5 h-5']); ?>
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
                </div>
                <p class="text-sm text-ink-muted mb-5"><?php echo e(__('merchant_panel.state_popup_desc')); ?></p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    
                    <div class="edz-card edz-card--padded">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <p class="font-semibold text-ink"><?php echo e(__('merchant_panel.municipalities')); ?></p>

                            
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <input type="number" step="0.01" min="0"
                                        wire:model="applyAllHomeCost"
                                        wire:keydown.enter="applyAllHomeCost('<?php echo e($popupStateId); ?>')"
                                        class="edz-input text-sm pr-8 w-32" placeholder="0">
                                    <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                </div>
                                <button type="button"
                                    wire:click="applyAllHomeCost('<?php echo e($popupStateId); ?>')"
                                    class="edz-btn edz-btn--primary edz-btn--sm"
                                    wire:loading.attr="disabled" wire:target="applyAllHomeCost">
                                    <?php echo e(__('merchant_panel.apply_all')); ?>

                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-12 gap-3 px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            <div class="col-span-7"><?php echo e(__('merchant_panel.municipality')); ?></div>
                            <div class="col-span-5 text-end"><?php echo e(__('merchant_panel.home_cost')); ?></div>
                        </div>

                        <div class="divide-y divide-surface-border max-h-[26rem] overflow-y-auto edz-scroll">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $popupCitiesWithPrices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div wire:key="city-row-<?php echo e($city['id']); ?>" class="grid grid-cols-12 gap-3 px-2 py-2.5 items-center">
                                    <div class="col-span-7 text-sm text-ink truncate"><?php echo e($city['name']); ?></div>
                                    <div class="col-span-5">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                value="<?php echo e($city['home_cost']); ?>"
                                                wire:change="saveMunicipalityCost('<?php echo e($popupStateId); ?>', '<?php echo e($city['id']); ?>', $event.target.value)"
                                                class="edz-input text-sm pr-8 w-full" placeholder="—">
                                            <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="edz-card edz-card--padded">
                        <p class="font-semibold text-ink mb-4"><?php echo e(__('merchant_panel.default_center')); ?></p>

                        <div class="space-y-4">
                            <div>
                                <label class="edz-label"><?php echo e(__('merchant_panel.select_default_center')); ?></label>
                                <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'stateDefaultCenterId','wire:change' => 'saveDefaultCenter(\''.e($popupStateId).'\', $event.target.value)','options' => collect($popupCenters)->map(fn ($c) => ['value' => $c['id'], 'label' => $c['name']])->all(),'placeholder' => ''.e(__('merchant_panel.no_center_selected')).'','size' => 'sm','search' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'stateDefaultCenterId','wire:change' => 'saveDefaultCenter(\''.e($popupStateId).'\', $event.target.value)','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($popupCenters)->map(fn ($c) => ['value' => $c['id'], 'label' => $c['name']])->all()),'placeholder' => ''.e(__('merchant_panel.no_center_selected')).'','size' => 'sm','search' => true]); ?>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($popupCenters)): ?>
                                    <p class="text-xs text-ink-muted mt-2"><?php echo e(__('merchant_panel.no_centers_hint')); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div>
                                <label class="edz-label"><?php echo e(__('merchant_panel.office_cost')); ?></label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0"
                                        value="<?php echo e($stateOfficeCost); ?>"
                                        wire:change="saveStateOffice('<?php echo e($popupStateId); ?>', $event.target.value)"
                                        class="edz-input text-sm pr-8" placeholder="—">
                                    <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                </div>
                                <p class="text-xs text-ink-muted mt-1"><?php echo e(__('merchant_panel.office_cost_hint')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $attributes = $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $component = $__componentOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showListModal): ?>
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'showCloseButton' => false,'preventClose' => true,'size' => 'lg','wire:key' => 'list-modal-'.e($editingListId ?? 'new').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isOpen' => true,'showCloseButton' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'preventClose' => true,'size' => 'lg','wire:key' => 'list-modal-'.e($editingListId ?? 'new').'']); ?>
            <div class="p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-bold text-ink">
                        <?php echo e($editingListId ? __('merchant_panel.edit_list') : __('merchant_panel.new_list')); ?>

                    </h3>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="closeListModal">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-5 h-5']); ?>
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
                </div>
                <p class="text-sm text-ink-muted mb-5"><?php echo e(__('merchant_panel.list_modal_desc')); ?></p>

                <form wire:submit="saveList" class="space-y-5">
                    <div>
                        <label for="list-name" class="edz-label"><?php echo e(__('merchant_panel.list_name')); ?></label>
                        <input id="list-name" type="text" wire:model="listName"
                            class="edz-input w-full" placeholder="<?php echo e(__('merchant_panel.list_name_placeholder')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['listName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-xs font-medium mt-1.5 text-danger-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="edz-label"><?php echo e(__('merchant_panel.select_products')); ?></label>
                        <div class="relative">
                            <?php if (isset($component)) { $__componentOriginal855390713d03eff0179f92852db7ddbf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal855390713d03eff0179f92852db7ddbf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.product-multi-picker','data' => ['options' => $this->listProductResults,'selected' => $listSelectedProductIds,'selectedNames' => $listSelectedProducts,'toggle' => 'toggleListProduct','model' => 'listProductSearch','placeholder' => __('merchant_panel.search_products_to_add'),'emptyMessage' => __('merchant_panel.list_no_products_found')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.product-multi-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->listProductResults),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listSelectedProductIds),'selected-names' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listSelectedProducts),'toggle' => 'toggleListProduct','model' => 'listProductSearch','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('merchant_panel.search_products_to_add')),'empty-message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('merchant_panel.list_no_products_found'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal855390713d03eff0179f92852db7ddbf)): ?>
<?php $attributes = $__attributesOriginal855390713d03eff0179f92852db7ddbf; ?>
<?php unset($__attributesOriginal855390713d03eff0179f92852db7ddbf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal855390713d03eff0179f92852db7ddbf)): ?>
<?php $component = $__componentOriginal855390713d03eff0179f92852db7ddbf; ?>
<?php unset($__componentOriginal855390713d03eff0179f92852db7ddbf); ?>
<?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-surface-border">
                        <button type="button" wire:click="closeListModal" class="edz-btn edz-btn--ghost edz-btn--sm">
                            <?php echo e(__('merchant_panel.cancel')); ?>

                        </button>
                        <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm"
                            wire:loading.attr="disabled" wire:target="saveList">
                            <span wire:loading.remove wire:target="saveList">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-4 h-4']); ?>
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
                            <?php if (isset($component)) { $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.spinner','data' => ['wire:target' => 'saveList']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.spinner'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:target' => 'saveList']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c)): ?>
<?php $attributes = $__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c; ?>
<?php unset($__attributesOriginalf4c9959d3f2732b60b7f028a5155a98c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf4c9959d3f2732b60b7f028a5155a98c)): ?>
<?php $component = $__componentOriginalf4c9959d3f2732b60b7f028a5155a98c; ?>
<?php unset($__componentOriginalf4c9959d3f2732b60b7f028a5155a98c); ?>
<?php endif; ?>
                            <?php echo e(__('merchant_panel.save_list')); ?>

                        </button>
                    </div>
                </form>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $attributes = $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $component = $__componentOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showListStatePopup): ?>
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'showCloseButton' => false,'preventClose' => true,'size' => 'xl','wire:key' => 'list-state-popup-'.e($selectedListId).'-'.e($listPopupStateId).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isOpen' => true,'showCloseButton' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'preventClose' => true,'size' => 'xl','wire:key' => 'list-state-popup-'.e($selectedListId).'-'.e($listPopupStateId).'']); ?>
            <div class="p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-bold text-ink"><?php echo e($listPopupStateName); ?></h3>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" wire:click="closeListStatePopup">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-5 h-5']); ?>
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
                </div>
                <p class="text-sm text-ink-muted mb-5"><?php echo e(__('merchant_panel.state_popup_desc')); ?></p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    
                    <div class="edz-card edz-card--padded">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <p class="font-semibold text-ink"><?php echo e(__('merchant_panel.municipalities')); ?></p>

                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <input type="number" step="0.01" min="0"
                                        wire:model="listApplyAllHomeCost"
                                        wire:keydown.enter="applyAllListHomeCost('<?php echo e($listPopupStateId); ?>')"
                                        class="edz-input text-sm pr-8 w-32" placeholder="0">
                                    <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                </div>
                                <button type="button"
                                    wire:click="applyAllListHomeCost('<?php echo e($listPopupStateId); ?>')"
                                    class="edz-btn edz-btn--primary edz-btn--sm"
                                    wire:loading.attr="disabled" wire:target="applyAllListHomeCost">
                                    <?php echo e(__('merchant_panel.apply_all')); ?>

                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-12 gap-3 px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-ink-muted">
                            <div class="col-span-7"><?php echo e(__('merchant_panel.municipality')); ?></div>
                            <div class="col-span-5 text-end"><?php echo e(__('merchant_panel.home_cost')); ?></div>
                        </div>

                        <div class="divide-y divide-surface-border max-h-[26rem] overflow-y-auto edz-scroll">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $listPopupCitiesWithPrices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div wire:key="list-city-row-<?php echo e($city['id']); ?>"
                                    class="grid grid-cols-12 gap-3 px-2 py-2.5 items-center">
                                    <div class="col-span-7 text-sm text-ink truncate"><?php echo e($city['name']); ?></div>
                                    <div class="col-span-5">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                value="<?php echo e($city['home_cost']); ?>"
                                                wire:change="saveListMunicipalityCost('<?php echo e($listPopupStateId); ?>', '<?php echo e($city['id']); ?>', $event.target.value)"
                                                class="edz-input text-sm pr-8 w-full" placeholder="—">
                                            <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="edz-card edz-card--padded">
                        <p class="font-semibold text-ink mb-4"><?php echo e(__('merchant_panel.office_cost')); ?></p>
                        <div>
                            <label class="edz-label"><?php echo e(__('merchant_panel.office_cost')); ?></label>
                            <div class="relative">
                                <input type="number" step="0.01" min="0"
                                    value="<?php echo e($listStateOfficeCost); ?>"
                                    wire:change="saveListOffice('<?php echo e($listPopupStateId); ?>', $event.target.value)"
                                    class="edz-input text-sm pr-8" placeholder="—">
                                <span class="absolute inset-y-0 end-2 flex items-center text-xs text-ink-muted">DA</span>
                            </div>
                            <p class="text-xs text-ink-muted mt-1"><?php echo e(__('merchant_panel.office_cost_hint')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $attributes = $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $component = $__componentOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/delivery/announced-rates.blade.php ENDPATH**/ ?>