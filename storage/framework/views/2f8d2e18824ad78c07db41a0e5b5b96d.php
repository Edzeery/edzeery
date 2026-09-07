<?php

use App\Enums\Store\StoreRoleEnum;

?>

<div class="edz-sidebar"
     @mouseenter="$store.shell.setHovered(true)"
     @mouseleave="$store.shell.setHovered(false)"
     :class="{ 'edz-sidebar--hover': $store.shell.hovered }">
    <div class="edz-sidebar__brand">
        <span class="edz-sidebar__logo">E</span>
        <span class="edz-sidebar__brand-name"><?php echo e(config('app.name')); ?></span>
    </div>

    <nav class="edz-sidebar__nav edz-scroll" aria-label="<?php echo e(__('merchant_panel.account')); ?>">
        <div class="edz-sidebar__group">
            <p class="edz-sidebar__group-title"><?php echo e(__('merchant_panel.account')); ?></p>

            <a href="<?php echo e(route('account.stores')); ?>" wire:navigate
               class="edz-sidebar__link <?php if(request()->routeIs('account.stores')): ?> edz-sidebar__link--active <?php endif; ?>">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'grid','class' => 'edz-sidebar__icon']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'grid','class' => 'edz-sidebar__icon']); ?>
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
                <span class="edz-sidebar__label"><?php echo e(__('merchant_panel.my_stores')); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stores->isNotEmpty()): ?>
                    <span class="edz-sidebar__badge"><?php echo e($stores->count()); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </a>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canCreateStore): ?>
                <a href="<?php echo e(route('merchant.create-store')); ?>" wire:navigate
                   class="edz-sidebar__link <?php if(request()->routeIs('merchant.create-store')): ?> edz-sidebar__link--active <?php endif; ?>">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'plus','class' => 'edz-sidebar__icon']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'edz-sidebar__icon']); ?>
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
                    <span class="edz-sidebar__label"><?php echo e(__('merchant_panel.create_store')); ?></span>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </nav>

    <div class="edz-sidebar__footer">
        <a href="<?php echo e(route('account.profile')); ?>" wire:navigate class="edz-sidebar__user">
            <span class="edz-sidebar__user-avatar"><?php echo e(strtoupper(Str::substr($user?->name ?? 'U', 0, 1))); ?></span>
            <div class="edz-sidebar__user-meta">
                <p class="edz-sidebar__user-name"><?php echo e($user?->name ?? __('merchant_panel.guest')); ?></p>
                <p class="edz-sidebar__user-role"><?php echo e($user?->email ?? '—'); ?></p>
            </div>
        </a>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\layout\merchant-sidebar.blade.php ENDPATH**/ ?>