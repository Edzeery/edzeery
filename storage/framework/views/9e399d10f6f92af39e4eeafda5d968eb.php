<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($languages)): ?>
    <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => ''.e($algin).'','width' => '38','contentClasses' => 'py-2 bg-surface-bg
        border border-gray-200 dark:border-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => ''.e($algin).'','width' => '38','contentClasses' => 'py-2 bg-surface-bg
        border border-gray-200 dark:border-gray-700']); ?>
         <?php $__env->slot('trigger', null, []); ?> 

            <img src="<?php echo e(asset('images/icons/' . $lang . '.png')); ?>" alt="<?php echo e(__('general.language')); ?>"
                class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 shadow-sm">
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('content', null, []); ?> 
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button
                    class="w-full text-<?php echo e($algin); ?> px-4 py-2 text-sm rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800
                       <?php echo e($lang === $language->code ? 'bg-primary/20 font-semibold' : ''); ?>"
                    @click.prevent="
                    fetch('<?php echo e(route('lang.switch', $language->code)); ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                            'Content-Type': 'application/json'
                        }
                    }).then(() => location.reload())
                ">
                    <span class="flex gap-1"> <img src="<?php echo e(asset('images/icons/' . $language->code . '.png')); ?>"
                            alt="<?php echo e(__('general.' . $language->name)); ?>"
                            class="w-5 h-5 rounded-full object-cover border-2 border-gray-200 shadow-sm">
                        <?php echo e(__('general.' . Str::lower($language->name))); ?></span>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/lang-switcher.blade.php ENDPATH**/ ?>