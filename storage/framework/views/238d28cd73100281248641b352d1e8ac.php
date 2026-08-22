<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div
            x-data
            class="border rounded-xl p-6 cursor-pointer transition
                   hover:shadow-lg
                   <?php echo e($getState('plan_id') == $plan->id ? 'ring-2 ring-primary-500' : ''); ?>"
            @click="$wire.set('data.plan_id', <?php echo e($plan->id); ?>)"
        >
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-bold"><?php echo e($plan->name); ?></h3>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->is_default): ?>
                    <span class="text-xs px-2 py-1 rounded bg-primary-500 text-white">
                        Most Popular
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="text-2xl font-extrabold mb-4">
                <?php echo e($plan->price); ?> <?php echo e($plan->currency); ?>

                <span class="text-sm text-gray-500">
                    / <?php echo e($plan->duration); ?> days
                </span>
            </div>

            <ul class="space-y-2 text-sm text-gray-600">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $plan->features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>✔ <?php echo e($feature->name); ?>:
                        <?php echo e($feature->pivot->value); ?> <?php echo e($feature->unit); ?>

                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\edzeery\resources\views\filament\components\plan-selector.blade.php ENDPATH**/ ?>