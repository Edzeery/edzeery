<?php if (isset($component)) { $__componentOriginal1e6834b7596effc838ab3adb1475b477 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e6834b7596effc838ab3adb1475b477 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.guest','data' => ['title' => __('messages.select_store')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.guest'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('messages.select_store'))]); ?>

    <div class="min-h-[70vh] flex items-center justify-center px-4">

        <?php if (isset($component)) { $__componentOriginalb9468a5a236188da95d7472adf747435 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb9468a5a236188da95d7472adf747435 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.card','data' => ['title' => __('messages.choose_store_title'),'subtitle' => __('messages.choose_store_desc')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('messages.choose_store_title')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('messages.choose_store_desc'))]); ?>

            
            <?php
                $user = auth()->user();
                $subscription = $user->latestSubscription();
                $featureService = app(\App\Domains\Plan\Services\FeatureUsageService::class);
                $storesFeature = $subscription?->plan?->features?->firstWhere('slug', 'stores_max');
                $maxStores = $subscription?->plan?->getFeatureValue('stores_max');
                $consumption = $storesFeature ? (int) $featureService->getConsumption($subscription, $storesFeature->id) : 0;
                $storeCount = $stores->count();
                $effectiveUsage = max($consumption, $storeCount);
                $isUnlimited = $maxStores === 'unlimited';
                $canCreate = $subscription ? $featureService->canUse($subscription, 'stores_max') : false;
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subscription && $maxStores): ?>
                <div class="mb-6 rounded-xl border border-neutral-border dark:border-dark-border bg-neutral-secondary dark:bg-dark-secondary p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-ink"><?php echo e(__('stores.stores_used', ['used' => $effectiveUsage, 'max' => $isUnlimited ? '∞' : $maxStores])); ?></p>
                            <p class="mt-0.5 text-xs text-ink-muted"><?php echo e(__('plans.max_stores')); ?>: <?php echo e($subscription->plan->name); ?></p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $canCreate): ?>
                            <a href="<?php echo e(route('account.billing')); ?>"
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-xs font-semibold rounded-xl hover:bg-brand-700 shadow-sm shadow-brand-600/20 transition">
                                <?php echo e(__('stores.upgrade_plan')); ?>

                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isUnlimited && $maxStores > 0): ?>
                        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-neutral-border dark:bg-dark-border">
                            <div class="h-full rounded-full bg-brand-600 transition-all duration-500 ease-out"
                                 style="width: <?php echo e(min(100, ($effectiveUsage / (int) $maxStores) * 100)); ?>%"></div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canCreate): ?>
                <div class="mb-6 text-center">
                    <a href="<?php echo e(route('merchant.create-store')); ?>"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-success-50 dark:bg-success-900/20 text-success-700 dark:text-success-300
                              text-sm font-semibold rounded-xl border border-success-200 dark:border-success-800
                              hover:bg-success-100 dark:hover:bg-success-900/30 transition-all duration-200">
                        <ion-icon name="add-circle-outline" class="text-lg"></ion-icon>
                        <?php echo e(__('buttons.create')); ?> <?php echo e(__('buttons.new')); ?>

                    </a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="space-y-3">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <form method="POST" action="<?php echo e(route('merchant.choose-store.select', $item->store)); ?>">
                        <?php echo csrf_field(); ?>

                        <button type="submit"
                            class="w-full flex items-center justify-between p-4 rounded-xl
                                   border border-neutral-border dark:border-dark-border
                                   bg-neutral-secondary dark:bg-dark-secondary
                                   hover:border-brand-300 dark:hover:border-brand-600
                                   hover:shadow-sm transition-all duration-200 group">

                            
                            <div class="text-left space-y-1">
                                <div class="font-semibold text-ink group-hover:text-brand-600 dark:group-hover:text-brand-400 transition">
                                    <?php echo e($item->store->name); ?>

                                </div>
                                <div class="text-xs">
                                    <?php if (isset($component)) { $__componentOriginal7e9b0c606fa761bc150c63a2e28951e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7e9b0c606fa761bc150c63a2e28951e4 = $attributes; } ?>
<?php $component = App\View\Components\RoleBadge::resolve(['role' => $item->role] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'status-kit::components.status-badge','data' => ['domain' => 'general','status' => $item->store->currentStatus()->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'general','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->store->currentStatus()->value)]); ?>
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

                            
                            <div class="text-right space-y-1">
                                <div class="font-semibold text-ink">
                                    <?php echo e($item->subscription?->plan?->name ?? '—'); ?>

                                </div>
                                <?php
                                    $subStatus = $item->subscription?->status
                                        ?? App\Enums\SubscriptionPayment\StatusSubscriptionEnum::PENDING;
                                ?>
                                <div class="text-xs">
                                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'status-kit::components.status-badge','data' => ['domain' => 'general','status' => $subStatus->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'general','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subStatus->value)]); ?>
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

                            
                            <ion-icon name="chevron-forward-outline"
                                      class="text-lg text-brand-500 group-hover:translate-x-1 transition-transform duration-200"></ion-icon>
                        </button>
                    </form>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>

            
            <div class="mt-6 text-center">
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

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb9468a5a236188da95d7472adf747435)): ?>
<?php $attributes = $__attributesOriginalb9468a5a236188da95d7472adf747435; ?>
<?php unset($__attributesOriginalb9468a5a236188da95d7472adf747435); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb9468a5a236188da95d7472adf747435)): ?>
<?php $component = $__componentOriginalb9468a5a236188da95d7472adf747435; ?>
<?php unset($__componentOriginalb9468a5a236188da95d7472adf747435); ?>
<?php endif; ?>

    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e6834b7596effc838ab3adb1475b477)): ?>
<?php $attributes = $__attributesOriginal1e6834b7596effc838ab3adb1475b477; ?>
<?php unset($__attributesOriginal1e6834b7596effc838ab3adb1475b477); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e6834b7596effc838ab3adb1475b477)): ?>
<?php $component = $__componentOriginal1e6834b7596effc838ab3adb1475b477; ?>
<?php unset($__componentOriginal1e6834b7596effc838ab3adb1475b477); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views\auth\choose-store.blade.php ENDPATH**/ ?>