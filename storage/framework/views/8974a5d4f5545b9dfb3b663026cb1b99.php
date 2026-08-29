<?php

use App\Domains\Analytics\Services\StoreDashboardAnalyticsService;
use App\Domains\User\Services\SubscriptionGuardService;

?>

<div x-data="{
    chartDays: <?php echo e(json_encode($salesByDay->pluck('date')->values())); ?>,
    chartRevenue: <?php echo e(json_encode($salesByDay->pluck('revenue')->values()->map(fn($v) => (float) $v))); ?>,
    chartOrders: <?php echo e(json_encode($salesByDay->pluck('total')->values()->map(fn($v) => (int) $v))); ?>,
    statusLabels: <?php echo e(json_encode($ordersByStatus->pluck('key')->values())); ?>,
    statusCounts: <?php echo e(json_encode($ordersByStatus->pluck('count')->values()->map(fn($v) => (int) $v))); ?>,
    statusColors: <?php echo e(json_encode($ordersByStatus->pluck('color')->values())); ?>,
    stateLabels: <?php echo e(json_encode($ordersByState->pluck('name')->values())); ?>,
    stateCounts: <?php echo e(json_encode($ordersByState->pluck('count')->values()->map(fn($v) => (int) $v))); ?>,
    stateRevenues: <?php echo e(json_encode($ordersByState->pluck('revenue')->values()->map(fn($v) => (float) $v))); ?>,
    deliveryLabels: <?php echo e(json_encode($deliveryTypeBreakdown->pluck('delivery_type')->values())); ?>,
    deliveryCounts: <?php echo e(json_encode($deliveryTypeBreakdown->pluck('count')->values()->map(fn($v) => (int) $v))); ?>,
    renderCharts() {
        let tries = 0;
        const attempt = () => {
            if (window.Chart && typeof window.renderDashboardCharts === 'function') {
                window.renderDashboardCharts(this);
            } else if (tries < 20) {
                tries++;
                setTimeout(attempt, 50);
            }
        };
        this.$nextTick(attempt);
    }
}" x-init="renderCharts()">

    
    <div class="edz-stagger grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
        
        <div class="edz-card edz-card--padded group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider"><?php echo e(__('dashboard.total_orders')); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['total_orders_change'] != 0): ?>
                    <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                        <?php echo e($summary['total_orders_change'] > 0 ? 'text-success-700 bg-success-50 dark:text-success-300 dark:bg-success-900/30' : 'text-danger-700 bg-danger-50 dark:text-danger-300 dark:bg-danger-900/30'); ?>">
                        <?php echo e($summary['total_orders_change'] > 0 ? '▲' : '▼'); ?> <?php echo e(abs($summary['total_orders_change'])); ?>%
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <p class="text-3xl font-bold tracking-tighter text-ink leading-none"><?php echo e($summary['total_orders']); ?></p>
            <p class="mt-2 text-xs text-ink-muted"><?php echo e(__('dashboard.this_month')); ?></p>
        </div>

        
        <div class="edz-card edz-card--padded group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider"><?php echo e(__('dashboard.revenue')); ?></p>
                <div class="w-8 h-8 rounded-lg bg-success-50 dark:bg-success-900/20 flex items-center justify-center text-success-600">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trending-up','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trending-up','class' => 'w-4 h-4']); ?>
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
            <p class="text-3xl font-bold tracking-tighter text-ink leading-none"><?php echo e(number_format($summary['revenue'], 0)); ?><span class="text-lg font-semibold text-ink-muted ms-1"><?php echo e(__('stores.currency_symbol')); ?></span></p>
            <p class="mt-2 text-xs text-ink-muted"><?php echo e(__('dashboard.delivered_orders')); ?></p>
        </div>

        
        <div class="edz-card edz-card--padded group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider"><?php echo e(__('dashboard.confirmation_rate')); ?></p>
                <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                    <?php echo e($summary['confirmation_rate'] >= 70 ? 'text-success-700 bg-success-50 dark:text-success-300 dark:bg-success-900/30' : 'text-warning-700 bg-warning-50 dark:text-warning-300 dark:bg-warning-900/30'); ?>">
                    <?php echo e($summary['confirmation_rate']); ?>%
                </span>
            </div>
            <div class="w-full bg-surface-secondary rounded-full h-1.5 overflow-hidden">
                <div class="h-1.5 rounded-full transition-all duration-700 ease-out-expo
                    <?php echo e($summary['confirmation_rate'] >= 70 ? 'bg-success-500' : 'bg-warning-500'); ?>"
                     style="width: <?php echo e($summary['confirmation_rate']); ?>%"></div>
            </div>
            <p class="mt-2 text-xs text-ink-muted"><?php echo e(__('dashboard.confirmed_of_total')); ?></p>
        </div>

        
        <div class="edz-card edz-card--padded group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-ink-muted uppercase tracking-wider"><?php echo e(__('dashboard.return_rate')); ?></p>
                <span class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                    <?php echo e($summary['return_rate'] <= 10 ? 'text-success-700 bg-success-50 dark:text-success-300 dark:bg-success-900/30' : 'text-danger-700 bg-danger-50 dark:text-danger-300 dark:bg-danger-900/30'); ?>">
                    <?php echo e($summary['return_rate']); ?>%
                </span>
            </div>
            <div class="w-full bg-surface-secondary rounded-full h-1.5 overflow-hidden">
                <div class="h-1.5 rounded-full transition-all duration-700 ease-out-expo
                    <?php echo e($summary['return_rate'] <= 10 ? 'bg-success-500' : 'bg-danger-500'); ?>"
                     style="width: <?php echo e($summary['return_rate']); ?>%"></div>
            </div>
            <p class="mt-2 text-xs text-ink-muted"><?php echo e(__('dashboard.return_of_processed')); ?></p>
        </div>
    </div>

    
    <div class="edz-stagger grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <div class="edz-card edz-card--padded">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider"><?php echo e(__('dashboard.aov')); ?></p>
            <p class="mt-2 text-2xl font-bold tracking-tighter text-ink"><?php echo e(number_format($summary['aov'], 0)); ?> <span class="text-sm font-medium text-ink-muted"><?php echo e(__('stores.currency_symbol')); ?></span></p>
        </div>
        <div class="edz-card edz-card--padded">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider"><?php echo e(__('titles.products')); ?></p>
            <p class="mt-2 text-2xl font-bold tracking-tighter text-ink"><?php echo e($summary['active_products']); ?> <span class="text-sm font-medium text-ink-muted">/ <?php echo e($summary['total_products']); ?></span></p>
        </div>
        <div class="edz-card edz-card--padded">
            <p class="text-xs font-medium text-ink-muted uppercase tracking-wider"><?php echo e(__('titles.team')); ?></p>
            <p class="mt-2 text-2xl font-bold tracking-tighter text-ink"><?php echo e($summary['total_members']); ?></p>
        </div>
    </div>

    
    <div class="edz-card edz-card--padded mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl <?php echo e(currentStore()?->isPubliclyActive() ? 'bg-success-50 dark:bg-success-900/20 text-success-600' : 'bg-warning-50 dark:bg-warning-900/20 text-warning-600'); ?> flex items-center justify-center">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'external-link','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'external-link','class' => 'w-5 h-5']); ?>
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
                    <p class="text-sm font-semibold text-ink"><?php echo e(__('storefront.your_store_link')); ?></p>
                    <p class="text-xs font-mono text-ink-muted"><?php echo e(currentStore()?->public_url ?? '-'); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                    x-data="{ copied: false }"
                    x-on:click="navigator.clipboard.writeText('<?php echo e(currentStore()?->public_url); ?>'); copied = true; setTimeout(() => copied = false, 2000);"
                    class="edz-btn edz-btn--secondary edz-btn--sm">
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
                    <span x-text="copied ? '<?php echo e(__('buttons.copied')); ?>' : '<?php echo e(__('buttons.copy_link')); ?>'"></span>
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(currentStore()?->isPubliclyActive()): ?>
                    <a href="<?php echo e(currentStore()?->public_url); ?>" target="_blank" rel="noopener noreferrer"
                       class="edz-btn edz-btn--primary edz-btn--sm">
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
                        <?php echo e(__('storefront.visit_store')); ?>

                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="edz-stagger grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        
        <div class="lg:col-span-2 edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4"><?php echo e(__('dashboard.sales_trend')); ?></h3>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4"><?php echo e(__('dashboard.orders_by_status')); ?></h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ordersByStatus->isNotEmpty()): ?>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            <?php else: ?>
                <div class="h-64 flex items-center justify-center">
                    <p class="text-sm text-ink-muted"><?php echo e(__('dashboard.no_data')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="edz-stagger grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4"><?php echo e(__('dashboard.orders_by_state')); ?></h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ordersByState->isNotEmpty()): ?>
                <div class="space-y-2.5">
                    <?php ($maxState = $ordersByState->max('count')); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ordersByState; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-medium text-ink w-24 truncate" title="<?php echo e($state->name); ?>"><?php echo e($state->name); ?></span>
                            <div class="flex-1 bg-surface-secondary rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full bg-accent-500 transition-all duration-700 ease-out-expo"
                                     style="width: <?php echo e($maxState > 0 ? ($state->count / $maxState * 100) : 0); ?>%"></div>
                            </div>
                            <span class="text-xs font-semibold text-ink w-8 text-right"><?php echo e($state->count); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="h-32 flex items-center justify-center">
                    <p class="text-sm text-ink-muted"><?php echo e(__('dashboard.no_data')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4"><?php echo e(__('dashboard.delivery_breakdown')); ?></h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deliveryTypeBreakdown->isNotEmpty()): ?>
                <div class="space-y-4 mt-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $deliveryTypeBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php ($total = $deliveryTypeBreakdown->sum('count')); ?>
                        <?php ($pct = $total > 0 ? round(($dt->count / $total) * 100) : 0); ?>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-sm font-medium text-ink"><?php echo e($dt->delivery_type === 'home' ? __('orders.delivery_home') : __('orders.delivery_stopdesk')); ?></span>
                                <span class="text-sm font-bold tracking-tight text-ink"><?php echo e($pct); ?>%</span>
                            </div>
                            <div class="w-full bg-surface-secondary rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-700 ease-out-expo <?php echo e($dt->delivery_type === 'home' ? 'bg-accent-500' : 'bg-success-500'); ?>"
                                     style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                            <p class="mt-1 text-xs text-ink-muted"><?php echo e($dt->count); ?> <?php echo e(__('orders.orders')); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="h-32 flex items-center justify-center">
                    <p class="text-sm text-ink-muted"><?php echo e(__('dashboard.no_data')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="edz-stagger grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold tracking-tight text-ink"><?php echo e(__('dashboard.pending_confirmation')); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingOrders->count() > 0): ?>
                    <a href="<?php echo e(route('merchant.orders.index', currentStore())); ?>" wire:navigate
                       class="text-xs font-medium text-accent-600 hover:text-accent-500 transition-colors">
                        <?php echo e(__('buttons.view_all')); ?>

                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingOrders->isNotEmpty()): ?>
                <div class="divide-y divide-surface-border">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pendingOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-ink">#<?php echo e($order->number); ?></p>
                                <p class="text-xs text-ink-muted"><?php echo e($order->customer_name ?? '-'); ?> · <?php echo e($order->customer_phone ?? '-'); ?></p>
                            </div>
                            <div class="text-end">
                                <p class="text-sm font-bold tracking-tight text-ink"><?php echo e(number_format($order->total_amount, 0)); ?> <?php echo e(__('stores.currency_symbol')); ?></p>
                                <p class="text-xs text-ink-muted"><?php echo e(\Carbon\Carbon::parse($order->created_at)->diffForHumans()); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'checkmark-circle-outline','class' => 'w-10 h-10 mx-auto text-ink-muted mb-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'checkmark-circle-outline','class' => 'w-10 h-10 mx-auto text-ink-muted mb-2']); ?>
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
                    <p class="text-sm text-ink-muted"><?php echo e(__('dashboard.no_pending_orders')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="edz-card edz-card--padded">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold tracking-tight text-ink"><?php echo e(__('dashboard.top_products')); ?></h3>
                <a href="<?php echo e(route('merchant.products.index', currentStore())); ?>" wire:navigate
                   class="text-xs font-medium text-accent-600 hover:text-accent-500 transition-colors">
                    <?php echo e(__('buttons.view_all')); ?>

                </a>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($topProducts->isNotEmpty()): ?>
                <div class="divide-y divide-surface-border">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-ink truncate"><?php echo e($product->name); ?></p>
                                <p class="text-xs text-ink-muted"><?php echo e($product->total_qty); ?> <?php echo e(__('orders.units_sold')); ?></p>
                            </div>
                            <span class="text-sm font-bold tracking-tight text-ink"><?php echo e(number_format($product->total_revenue, 0)); ?> <?php echo e(__('stores.currency_symbol')); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'bag-outline','class' => 'w-10 h-10 mx-auto text-ink-muted mb-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bag-outline','class' => 'w-10 h-10 mx-auto text-ink-muted mb-2']); ?>
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
                    <p class="text-sm text-ink-muted"><?php echo e(__('dashboard.no_data')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lowStockVariants->isNotEmpty()): ?>
        <div class="edz-card edz-card--padded mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold tracking-tight text-ink"><?php echo e(__('titles.stock_alerts')); ?></h3>
                <a href="<?php echo e(route('merchant.stock-alerts.index', currentStore())); ?>"
                   class="text-xs font-medium text-danger-600 hover:text-danger-500 transition-colors">
                    <?php echo e(__('buttons.view_all')); ?>

                </a>
            </div>
            <div class="divide-y divide-surface-border">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $lowStockVariants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-ink truncate"><?php echo e($variant->product->name); ?></p>
                            <p class="text-xs text-ink-muted">
                                <?php echo e($variant->optionValues->pluck('value')->implode(' / ') ?: $variant->name); ?>

                            </p>
                        </div>
                        <div class="text-end">
                            <span class="edz-badge edz-badge--warning"><?php echo e($variant->stock); ?></span>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <script>
        window.renderDashboardCharts = function(data) {
            const fontColor = getComputedStyle(document.documentElement).getPropertyValue('--edz-color-text-soft') || '#6b7280';
            const gridColor = getComputedStyle(document.documentElement).getPropertyValue('--edz-color-border') || '#e5e7eb';

            Chart.defaults.color = fontColor;
            Chart.defaults.font.family = "'Inter', 'IBM Plex Sans Arabic', sans-serif";

            if (data.chartDays.length > 0) {
                new Chart(document.getElementById('salesChart'), {
                    type: 'line',
                    data: {
                        labels: data.chartDays.map(d => { const dt = new Date(d); return dt.toLocaleDateString('ar-DZ', { month: 'short', day: 'numeric' }); }),
                        datasets: [{
                            label: '<?php echo e(__("dashboard.revenue")); ?>',
                            data: data.chartRevenue,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#6366f1',
                            yAxisID: 'y',
                        }, {
                            label: '<?php echo e(__("dashboard.total_orders")); ?>',
                            data: data.chartOrders,
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.05)',
                            fill: false,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#22c55e',
                            yAxisID: 'y1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 16, usePointStyle: true } } },
                        scales: {
                            x: { grid: { color: gridColor, drawBorder: false }, border: { display: false } },
                            y: { position: 'left', grid: { color: gridColor, drawBorder: false }, border: { display: false }, title: { display: true, text: '<?php echo e(__("stores.currency_symbol")); ?>' } },
                            y1: { position: 'right', grid: { drawOnChartArea: false }, border: { display: false }, title: { display: true, text: '<?php echo e(__("dashboard.orders")); ?>' } },
                        }
                    }
                });
            }

            if (data.statusLabels.length > 0) {
                const colorMap = {
                    'pending': '#f59e0b', 'confirmed': '#3b82f6', 'preparing': '#8b5cf6',
                    'shipped': '#6366f1', 'in_transit': '#06b6d4', 'out_for_delivery': '#14b8a6',
                    'delivered': '#22c55e', 'completed': '#22c55e', 'cancelled': '#ef4444',
                    'canceled': '#ef4444', 'returned': '#f97316', 'refunded': '#ec4899',
                    'on_hold': '#64748b', 'paid': '#3b82f6', 'draft': '#9ca3af',
                };
                const colors = data.statusLabels.map((k, i) => data.statusColors[i] || colorMap[k] || '#6b7280');

                new Chart(document.getElementById('statusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.statusLabels,
                        datasets: [{ data: data.statusCounts, backgroundColor: colors, borderWidth: 0, hoverOffset: 4 }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, usePointStyle: true } } },
                        cutout: '68%',
                    }
                });
            }
        };
    </script>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\dashboard.blade.php ENDPATH**/ ?>