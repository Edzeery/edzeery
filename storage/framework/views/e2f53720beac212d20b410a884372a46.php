<?php

use App\Domains\Plan\Services\FeatureUsageService;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\SubscriptionPayment\StatusSubscriptionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreContext;

?>

<div class="w-full space-y-5">

    
    <div class="text-center animate-fade-up">
        <h1 class="text-2xl font-bold tracking-tight text-ink">
            <?php echo e(__('messages.choose_store_title')); ?>

        </h1>
        <p class="mt-1 text-sm text-ink-muted"><?php echo e(__('messages.choose_store_desc')); ?></p>
    </div>

    
    <div class="flex items-center gap-4 p-4 rounded-2xl shadow-card border border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface animate-fade-up"
         style="animation-delay: 0.05s">

        <div class="w-12 h-12 shrink-0 rounded-full flex items-center justify-center text-white text-lg font-bold shadow-md"
             style="background-color: <?php echo e($this->user['color']); ?>;">
            <?php echo e($this->user['initial']); ?>

        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="font-bold text-ink truncate"><?php echo e($this->user['name']); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($this->subscription['plan_name'])): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs font-semibold">
                        <ion-icon name="star" class="text-xs"></ion-icon>
                        <?php echo e($this->subscription['plan_name']); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <p class="mt-0.5 text-xs text-ink-muted truncate"><?php echo e($this->user['email']); ?></p>
        </div>
    </div>

    
    <div class="grid grid-cols-3 gap-2 animate-fade-up" style="animation-delay: 0.1s">
        <a href="<?php echo e(route('account.profile')); ?>"
           class="flex flex-col items-center justify-center gap-1 px-2 py-3 rounded-xl border border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface text-xs font-semibold text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 hover:border-brand-300 dark:hover:border-brand-600 transition-all duration-200">
            <ion-icon name="person-outline" class="text-lg"></ion-icon>
            <?php echo e(__('buttons.profile')); ?>

        </a>
        <a href="<?php echo e(route('account.billing')); ?>"
           class="flex flex-col items-center justify-center gap-1 px-2 py-3 rounded-xl border border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface text-xs font-semibold text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 hover:border-brand-300 dark:hover:border-brand-600 transition-all duration-200">
            <ion-icon name="credit-card-outline" class="text-lg"></ion-icon>
            <?php echo e(__('buttons.billing')); ?>

        </a>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canCreate): ?>
            <a href="<?php echo e(route('merchant.create-store')); ?>"
               class="flex flex-col items-center justify-center gap-1 px-2 py-3 rounded-xl border border-success-200 dark:border-success-800 bg-success-50 dark:bg-success-900/20 text-xs font-semibold text-success-700 dark:text-success-300 hover:bg-success-100 dark:hover:bg-success-900/30 transition-all duration-200">
                <ion-icon name="add-circle-outline" class="text-lg"></ion-icon>
                <?php echo e(__('buttons.create')); ?> <?php echo e(__('buttons.new')); ?>

            </a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($this->subscription)): ?>
        <?php
            $maxInt = is_numeric($this->maxStores) ? (int) $this->maxStores : 0;
            $usagePercent = (!$this->isUnlimited && $maxInt > 0) ? min(100, round(($this->effectiveUsage / $maxInt) * 100)) : 100;
            $atLimit = !$this->isUnlimited && $maxInt > 0 && $this->effectiveUsage >= $maxInt;
        ?>

        <div class="rounded-xl border border-neutral-border dark:border-dark-border bg-neutral-secondary dark:bg-dark-secondary p-4 animate-fade-up"
             style="animation-delay: 0.15s">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-ink">
                        <?php echo e(__('stores.stores_used', ['used' => $this->effectiveUsage, 'max' => $this->isUnlimited ? '∞' : $this->maxStores])); ?>

                    </p>
                    <p class="mt-0.5 text-xs text-ink-muted">
                        <?php echo e(__('plans.max_stores')); ?>: <?php echo e($this->subscription['plan_name'] ?? '—'); ?>

                    </p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($atLimit): ?>
                    <a href="<?php echo e(route('account.billing')); ?>"
                       class="inline-flex items-center gap-1.5 px-4 py-2 shrink-0 bg-brand-600 text-white text-xs font-semibold rounded-xl hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition">
                        <?php echo e(__('stores.upgrade_plan')); ?>

                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$this->isUnlimited && $maxInt > 0): ?>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-neutral-border dark:bg-dark-border">
                    <div class="h-full rounded-full bg-brand-600 transition-all duration-500 ease-out"
                         style="width: <?php echo e($usagePercent); ?>%"></div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php else: ?>
        <div class="flex items-center justify-between gap-3 rounded-xl border border-neutral-border dark:border-dark-border bg-neutral-secondary dark:bg-dark-secondary p-4 animate-fade-up"
             style="animation-delay: 0.15s">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 shrink-0 rounded-lg bg-warning-50 dark:bg-warning-900/20 flex items-center justify-center">
                    <ion-icon name="star" class="text-lg text-warning-500"></ion-icon>
                </div>
                <p class="text-sm font-semibold text-ink truncate">
                    <?php echo e(__('merchant_panel.no_active_subscription')); ?>

                </p>
            </div>
            <a href="<?php echo e(route('landing')); ?>"
               class="inline-flex items-center gap-1.5 px-4 py-2 shrink-0 bg-brand-600 text-white text-xs font-semibold rounded-xl hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition">
                <ion-icon name="add-circle-outline" class="text-base"></ion-icon>
                <?php echo e(__('landing.subscribe_now')); ?>

            </a>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($this->stores) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-fade-up" style="animation-delay: 0.2s">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $storefrontUrl = (request()->secure() ? 'https' : 'http') . '://' . $store['slug'] . '.' . rtrim(config('app.domain', 'edzeery.com'), '/');
                    $subStatus = StatusSubscriptionEnum::tryFrom((string) ($store['plan_status'] ?? '')) ?? StatusSubscriptionEnum::PENDING;
                    $storeStatus = \App\Enums\Store\StoreStatusEnum::from((string) $store['status']);
                    $storeRole = StoreRoleEnum::from((string) $store['role']);
                ?>

                <article wire:key="store-card-<?php echo e($store['slug']); ?>"
                    class="relative overflow-hidden flex flex-col gap-3 p-4 rounded-2xl shadow-card border border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface hover:border-brand-300 dark:hover:border-brand-600 hover:shadow-md transition-all duration-200 group">

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($store['is_owner']): ?>
                        <span class="absolute inset-y-0 start-0 w-1 bg-brand-500" aria-hidden="true"></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div class="flex items-start gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($store['logo'])): ?>
                            <img src="<?php echo e(asset('storage/' . $store['logo'])); ?>" alt="<?php echo e($store['name']); ?>"
                                 class="w-11 h-11 shrink-0 rounded-xl object-cover border border-neutral-border dark:border-dark-border">
                        <?php else: ?>
                            <div class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center text-white font-bold"
                                 style="background-color: <?php echo e($store['color']); ?>;">
                                <?php echo e($store['initial']); ?>

                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-ink truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition">
                                <?php echo e($store['name']); ?>

                            </h3>
                            <a href="<?php echo e($storefrontUrl); ?>" target="_blank" rel="noopener noreferrer"
                               class="mt-0.5 inline-flex items-center gap-1 text-[11px] text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 transition truncate max-w-full">
                                <ion-icon name="globe-outline" class="shrink-0"></ion-icon>
                                <?php echo e($store['slug']); ?>.<?php echo e(rtrim(config('app.domain', 'edzeery.com'), '/')); ?>

                            </a>
                        </div>
                    </div>

                    
                    <div class="flex items-center gap-2 flex-wrap">
                        <?php if (isset($component)) { $__componentOriginal7e9b0c606fa761bc150c63a2e28951e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7e9b0c606fa761bc150c63a2e28951e4 = $attributes; } ?>
<?php $component = App\View\Components\RoleBadge::resolve(['role' => $storeRole] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                        <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'status-kit::components.status-badge','data' => ['domain' => 'general','status' => $storeStatus]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'general','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($storeStatus)]); ?>
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

                    
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-neutral-secondary dark:bg-dark-secondary p-2 text-center">
                        <div class="flex flex-col items-center gap-0.5">
                            <ion-icon name="cart-outline" class="text-sm text-ink-muted"></ion-icon>
                            <span class="text-xs font-semibold text-ink"><?php echo e(number_format($store['products_count'])); ?></span>
                            <span class="text-[10px] text-ink-muted"><?php echo e(__('titles.products_management')); ?></span>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <ion-icon name="receipt-outline" class="text-sm text-ink-muted"></ion-icon>
                            <span class="text-xs font-semibold text-ink"><?php echo e(number_format($store['orders_count'])); ?></span>
                            <span class="text-[10px] text-ink-muted"><?php echo e(__('titles.orders')); ?></span>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <ion-icon name="people-outline" class="text-sm text-ink-muted"></ion-icon>
                            <span class="text-xs font-semibold text-ink"><?php echo e(number_format($store['members_count'])); ?></span>
                            <span class="text-[10px] text-ink-muted"><?php echo e(__('teams.title')); ?></span>
                        </div>
                    </div>

                    
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="truncate text-ink-muted">
                            <?php echo e(__('plans.max_stores')); ?>: <span class="font-semibold text-ink"><?php echo e($store['plan_name'] ?? '—'); ?></span>
                        </span>
                        <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'status-kit::components.status-badge','data' => ['domain' => 'general','status' => $subStatus]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'general','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subStatus)]); ?>
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

                    
                    <div class="flex items-center gap-2 mt-auto pt-1">
                        <button type="button"
                                wire:click="selectStore('<?php echo e($store['slug']); ?>')"
                                wire:loading.attr="disabled"
                                wire:target="selectStore('<?php echo e($store['slug']); ?>')"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <ion-icon name="storefront-outline" class="text-base"></ion-icon>
                            <?php echo e(__('buttons.open')); ?>

                            <ion-icon name="chevron-forward-outline" class="text-sm group-hover:translate-x-0.5 transition-transform"></ion-icon>
                        </button>
                        <a href="<?php echo e($storefrontUrl); ?>" target="_blank" rel="noopener noreferrer"
                           title="<?php echo e(__('merchant_panel.visit_store')); ?>"
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-neutral-border dark:border-dark-border text-xs font-semibold text-ink-muted hover:text-brand-600 dark:hover:text-brand-400 hover:border-brand-300 dark:hover:border-brand-600 transition">
                            <ion-icon name="globe-outline" class="text-base"></ion-icon>
                            <?php echo e(__('merchant_panel.visit_store')); ?>

                        </a>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>
    <?php else: ?>
        
        <div class="rounded-2xl border-2 border-dashed border-neutral-border dark:border-dark-border bg-neutral-surface dark:bg-dark-surface p-10 text-center animate-fade-up"
             style="animation-delay: 0.2s">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center mb-4">
                <ion-icon name="storefront-outline" class="text-3xl text-brand-500"></ion-icon>
            </div>
            <h3 class="font-semibold text-ink"><?php echo e(__('merchant_panel.no_stores_yet')); ?></h3>
            <p class="mt-1 text-sm text-ink-muted"><?php echo e(__('messages.no_active_store')); ?></p>
            <a href="<?php echo e(route('merchant.create-store')); ?>"
               class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition">
                <ion-icon name="add-circle-outline" class="text-lg"></ion-icon>
                <?php echo e(__('buttons.create')); ?> <?php echo e(__('buttons.new')); ?>

            </a>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="pt-2 pb-4 text-center animate-fade-up" style="animation-delay: 0.25s">
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-ink-muted
                            hover:text-error-600 dark:hover:text-error-400 rounded-xl hover:bg-error-50 dark:hover:bg-error-900/10 transition-all duration-200">
                <ion-icon name="log-out-outline" class="text-base"></ion-icon>
                <?php echo e(__('buttons.logout')); ?>

            </button>
        </form>
    </div>

</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\choose-store.blade.php ENDPATH**/ ?>