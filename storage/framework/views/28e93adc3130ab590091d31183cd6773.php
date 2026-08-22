<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['stores']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['stores']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <form method="POST" action="<?php echo e(route('merchant.choose-store.select', $store['store_slug'])); ?>">
            <?php echo csrf_field(); ?>

            <button type="submit"
                class="group w-full rounded-2xl border border-neutral-border
                 bg-white p-5 text-left transition-all duration-300
                  hover:-translate-y-1 hover:shadow-lg dark:border-dark-border
                   dark:bg-white/[0.03]">

                
                <div class="flex items-center justify-between">
                    
                    <div
                        class="flex items-center justify-center w-12 h-12
                  text-ink
                   bg-gray-100 rounded-xl dark:bg-gray-800">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($store['store_logo'])): ?>
                            <img src="<?php echo e($store['store_logo']); ?>" alt="User" />
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="bi bi-shop" viewBox="0 0 16 16">
                                <path
                                    d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5M4 15h3v-5H4zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zm3 0h-2v3h2z" />
                            </svg>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    </div>
                    
                    <div class="text-left space-y-1">
                        <div class="font-semibold text-ink">
                            <?php echo e($store['store_name']); ?>

                        </div>
                        <div class="flex justify-between gap-2 ">
                            <div class="text-xs text-neutral-soft dark:text-dark-soft">

                                <?php if (isset($component)) { $__componentOriginal7e9b0c606fa761bc150c63a2e28951e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7e9b0c606fa761bc150c63a2e28951e4 = $attributes; } ?>
<?php $component = App\View\Components\RoleBadge::resolve(['role' => $store['membership_role']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('role-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\RoleBadge::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7e9b0c606fa761bc150c63a2e28951e4)): ?>
<?php $attributes = $__attributesOriginal7e9b0c606fa761bc150c63a2e28951e4; ?>
<?php unset($__attributesOriginal7e9b0c606fa761bc150c63a2e28951e4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7e9b0c606fa761bc150c63a2e28951e4)): ?>
<?php $component = $__componentOriginal7e9b0c606fa761bc150c63a2e28951e4; ?>
<?php unset($__componentOriginal7e9b0c606fa761bc150c63a2e28951e4); ?>
<?php endif; ?>

                            </div>

                            <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'status-kit::components.status-badge','data' => ['domain' => 'store','status' => $store['store_status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'store','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($store['store_status'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                        </div>
                    </div>

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-neutral-soft transition group-hover:translate-x-1" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>

                
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs text-neutral-soft">
                            <?php echo e(__('dashboard.total_memberships')); ?>

                        </p>
                        <h4 class="mt-1 text-lg font-bold text-ink">
                            <?php echo e($store['members_count']); ?>

                        </h4>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs text-neutral-soft">
                            <?php echo e(__('titles.plan')); ?>

                        </p>
                        <h4 class="mt-1 text-sm font-semibold text-ink">
                            <?php echo e($store['plan_name']); ?>

                        </h4>
                    </div>
                </div>
            </button>
        </form>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(user()?->canCreateMultiStore()): ?>
        <a href="<?php echo e(route('merchant.create-store')); ?>"
            class="flex min-h-[220px] flex-col items-center justify-center rounded-2xl border-2 border-dashed border-primary-300 bg-primary-50/40 p-6 text-center transition hover:bg-primary-50 dark:border-primary-700 dark:bg-primary-900/10">

            <div
                class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-600 text-white shadow-md">
                +
            </div>

            <h3 class="text-lg font-bold text-primary-700 dark:text-primary-400">
                <?php echo e(__('buttons.create')); ?> <?php echo e(__('buttons.new')); ?>

            </h3>
        </a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\edzeery\resources\views\components\ecommerce\stores-metrics.blade.php ENDPATH**/ ?>