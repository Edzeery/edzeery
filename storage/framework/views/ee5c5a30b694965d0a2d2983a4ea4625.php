<?php

use App\Domains\Merchant\Actions\GetStoreCardsAction;

?>

<div class="max-w-3xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-ink"><?php echo e(__('titles.stores')); ?></h1>
            <p class="text-sm text-ink-soft mt-0.5"><?php echo e(__('merchant_panel.your_stores')); ?></p>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canCreate): ?>
            <a href="<?php echo e(route('merchant.create-store')); ?>" wire:navigate
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
                <?php echo e(__('buttons.new')); ?> <?php echo e(__('titles.store')); ?>

            </a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <form method="POST" action="<?php echo e(route('merchant.choose-store.select', $store['store_slug'])); ?>" class="contents">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="group w-full text-left edz-card transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="edz-card--padded">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-accent-500 to-brand-600 flex items-center justify-center text-white text-xl font-bold shadow-md">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store['store_logo']): ?>
                                        <img src="<?php echo e($store['store_logo']); ?>" alt="<?php echo e($store['store_name']); ?>" class="w-14 h-14 rounded-2xl object-cover" />
                                    <?php else: ?>
                                        <?php echo e(strtoupper(mb_substr($store['store_name'], 0, 1))); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-ink text-lg"><?php echo e($store['store_name']); ?></p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'roles','status' => $store['membership_role']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'roles','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($store['membership_role'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
                                        <?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'store','status' => $store['store_status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'store','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($store['store_status'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="w-9 h-9 rounded-xl bg-surface-secondary group-hover:bg-surface-tertiary flex items-center justify-center transition-colors">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-right','class' => 'w-5 h-5 text-ink-muted group-hover:text-ink transition-colors']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-right','class' => 'w-5 h-5 text-ink-muted group-hover:text-ink transition-colors']); ?>
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

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-surface-secondary p-3">
                                <p class="text-xs text-ink-muted font-medium"><?php echo e(__('dashboard.total_memberships')); ?></p>
                                <p class="mt-1 text-lg font-bold text-ink"><?php echo e($store['members_count']); ?></p>
                            </div>
                            <div class="rounded-xl bg-surface-secondary p-3">
                                <p class="text-xs text-ink-muted font-medium"><?php echo e(__('titles.plan')); ?></p>
                                <p class="mt-1 text-sm font-semibold text-ink"><?php echo e($store['plan_name']); ?></p>
                            </div>
                        </div>
                    </div>
                </button>
            </form>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="edz-card edz-card--padded text-center py-16">
                <div class="w-16 h-16 rounded-2xl bg-surface-secondary mx-auto mb-4 flex items-center justify-center">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'grid','class' => 'w-8 h-8 text-ink-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'grid','class' => 'w-8 h-8 text-ink-muted']); ?>
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
                <p class="text-ink-muted font-medium"><?php echo e(__('merchant_panel.no_stores')); ?></p>
                <a href="<?php echo e(route('merchant.create-store')); ?>" wire:navigate
                   class="edz-btn edz-btn--primary edz-btn--sm mt-4">
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
                    <?php echo e(__('buttons.create')); ?> <?php echo e(__('titles.store')); ?>

                </a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/stores/index.blade.php ENDPATH**/ ?>