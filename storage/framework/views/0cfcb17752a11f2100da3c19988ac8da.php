
<?php
    $timelineEvents = $events ?? [];
    $timelineDays = collect($timelineEvents)
        ->groupBy(fn ($ev) => \Carbon\Carbon::parse($ev['occurred_at'] ?? now())->format('Y-m-d'));
    $timelineNewestEventId = $timelineEvents[0]['id'] ?? null;
    $timelineIcon = $icon ?? 'clock';
?>

<section class="mt-5">
    <h4
        class="text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-2">
        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => $timelineIcon,'class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($timelineIcon),'class' => 'w-4 h-4']); ?>
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
        <?php echo e(__('order_flow.order_timeline')); ?>

    </h4>
    <div
        class="rounded-xl border border-surface-border overflow-hidden bg-surface-tertiary/30">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $timelineDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayKey => $dayEvents): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $evDay = \Carbon\Carbon::parse($dayKey);
            ?>
            <div class="px-3 pt-3">
                <p
                    class="text-[11px] font-semibold uppercase tracking-wide text-ink-muted">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($evDay->isToday()): ?>
                        <?php echo e(__('order_flow.event_day_today')); ?>

                    <?php elseif($evDay->isYesterday()): ?>
                        <?php echo e(__('order_flow.event_day_yesterday')); ?>

                    <?php else: ?>
                        <?php echo e($evDay->translatedFormat('l, M j')); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
            </div>
            <ol class="divide-y divide-surface-border">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dayEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-start gap-3 px-3 py-2.5 text-sm">
                        <span
                            class="mt-1.5 w-2 h-2 rounded-full shrink-0 <?php echo e(($ev['id'] ?? null) === $timelineNewestEventId ? 'bg-accent-600' : 'bg-surface-border'); ?>"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-ink leading-snug"><?php echo e($ev['message'] ?? '—'); ?></p>
                            <p
                                class="text-xs text-ink-muted mt-0.5 flex flex-wrap items-center gap-x-2">
                                <span><?php echo e(__('order_flow.event_type_' . ($ev['event_type'] ?? 'note'))); ?></span>
                                <span>•</span>
                                <span><?php echo e(\Carbon\Carbon::parse($ev['occurred_at'])->format('H:i')); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ev['actor']['user']['name'])): ?>
                                    <span>•</span>
                                    <span><?php echo e($ev['actor']['user']['name']); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ev['actor']['role'])): ?>
                                    <?php if (isset($component)) { $__componentOriginal7e9b0c606fa761bc150c63a2e28951e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7e9b0c606fa761bc150c63a2e28951e4 = $attributes; } ?>
<?php $component = App\View\Components\RoleBadge::resolve(['role' => $ev['actor']['role']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>
                        </div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ol>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</section><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\orders\partials\order-events-timeline.blade.php ENDPATH**/ ?>