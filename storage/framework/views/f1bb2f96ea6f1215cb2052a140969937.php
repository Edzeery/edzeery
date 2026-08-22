<?php if (isset($component)) { $__componentOriginalb525200bfa976483b4eaa0b7685c6e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widget','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('heading', null, []); ?> 
            <?php echo e(__('filament-activity-log::activity.widgets.heatmap.heading')); ?>

         <?php $__env->endSlot(); ?>

        <?php
            $heatmap = $this->getData();
            $data = $heatmap['data'];
            $max = $heatmap['max'];
            $startDate = now()->subDays(365)->startOfWeek();
            $endDate = now();
            
            // Calculate months with smart spacing to avoid overlap
            $months = [];
            $currentMonth = $startDate->format('M');
            $months[] = ['name' => $currentMonth, 'week_index' => 0];
            $lastLabelWeek = 0;
            
            $dt = $startDate->copy();
            for ($weekIndex = 0; $weekIndex < 52; $weekIndex++) {
                $month = $dt->addWeek()->format('M');
                if ($month !== $currentMonth) {
                    // Only add label if it's been at least 4 weeks since the last one
                    if (($weekIndex - $lastLabelWeek) >= 4) {
                        $months[] = ['name' => $month, 'week_index' => $weekIndex];
                        $lastLabelWeek = $weekIndex;
                        $currentMonth = $month;
                    }
                }
            }
            
            // Grid cell dimensions for label calculation
            $cellSize = 11; // px
            $gap = 3; // px
            $weekWidth = $cellSize + $gap;
        ?>

        <div style="overflow-x: auto; padding: 1rem 0;">
            <!-- Month Labels -->
            <div style="position: relative; height: 16px; margin-bottom: 8px; font-size: 10px; font-weight: 600; color: #9ca3af;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="position: absolute; left: <?php echo e($month['week_index'] * $weekWidth); ?>px; white-space: nowrap;">
                        <?php echo e($month['name']); ?>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Heatmap Grid -->
            <div style="
                display: grid; 
                grid-template-rows: repeat(7, <?php echo e($cellSize); ?>px); 
                grid-auto-flow: column; 
                gap: <?php echo e($gap); ?>px; 
                width: max-content;
            ">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(0, 52); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(0, 6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $currentDate = $startDate->copy()->addWeeks($week)->addDays($day);
                            $dateString = $currentDate->toDateString();
                            $count = $data[$dateString] ?? 0;
                            $intensity = $count > 0 ? ceil(($count / $max) * 4) : 0;
                            
                            // Universal colors (subtler empty state)
                            $bg = match ($intensity) {
                                0 => 'rgba(128, 128, 128, 0.1)', // More subtle empty state
                                1 => '#22c55e40', 
                                2 => '#22c55e80', 
                                3 => '#22c55ebf', 
                                4 => '#22c55e',   
                                default => 'rgba(128, 128, 128, 0.1)',
                            };
                            
                            $tooltip = __('filament-activity-log::activity.widgets.heatmap.tooltip', [
                                'count' => $count,
                                'date' => $currentDate->format('M j, Y'),
                            ]);
                        ?>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentDate <= $endDate): ?>
                            <div 
                                class="filament-activity-log-heatmap-cell group"
                                style="
                                    width: <?php echo e($cellSize); ?>px; 
                                    height: <?php echo e($cellSize); ?>px; 
                                    border-radius: 2px; 
                                    background-color: <?php echo e($bg); ?>;
                                    transition: transform 0.15s ease;
                                    cursor: pointer;
                                "
                                x-tooltip="{ content: '<?php echo e($tooltip); ?>' }"
                                onmouseover="this.style.transform='scale(1.4)'; this.style.zIndex='10';"
                                onmouseout="this.style.transform='scale(1)'; this.style.zIndex='1';"
                            ></div>
                        <?php else: ?>
                            <div style="width: <?php echo e($cellSize); ?>px; height: <?php echo e($cellSize); ?>px;"></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            
            <!-- Legend -->
            <div style="margin-top: 1rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; font-size: 0.75rem; color: #9ca3af;">
                <span><?php echo e(__('filament-activity-log::activity.widgets.heatmap.less')); ?></span>
                <div style="width: <?php echo e($cellSize); ?>px; height: <?php echo e($cellSize); ?>px; border-radius: 2px; background-color: rgba(128, 128, 128, 0.1);"></div>
                <div style="width: <?php echo e($cellSize); ?>px; height: <?php echo e($cellSize); ?>px; border-radius: 2px; background-color: #22c55e40;"></div>
                <div style="width: <?php echo e($cellSize); ?>px; height: <?php echo e($cellSize); ?>px; border-radius: 2px; background-color: #22c55e80;"></div>
                <div style="width: <?php echo e($cellSize); ?>px; height: <?php echo e($cellSize); ?>px; border-radius: 2px; background-color: #22c55ebf;"></div>
                <div style="width: <?php echo e($cellSize); ?>px; height: <?php echo e($cellSize); ?>px; border-radius: 2px; background-color: #22c55e;"></div>
                <span><?php echo e(__('filament-activity-log::activity.widgets.heatmap.more')); ?></span>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $attributes = $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $component = $__componentOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\vendor\alizharb\filament-activity-log\resources\views\widgets\activity-heatmap.blade.php ENDPATH**/ ?>