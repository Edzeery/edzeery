
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($languages)): ?>
    <?php
        $currentLanguage = $languages->first(fn($l) => $l->code === $lang);
    ?>
    
    <div class="relative" id="lang-dropdown-<?php echo e($lang); ?>">
        
        <button onclick="document.getElementById('lang-dropdown-menu-<?php echo e($lang); ?>').classList.toggle('hidden'); event.stopPropagation();"
                class="flex items-center justify-center w-10 h-10 rounded-full border-2 border-gray-200 dark:border-gray-600
                       bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer">
            <img src="<?php echo e(asset('images/icons/' . $lang . '.png')); ?>" alt="<?php echo e(__('general.language')); ?>"
                 class="w-6 h-6 rounded-full object-cover">
        </button>

        
        <div id="lang-dropdown-menu-<?php echo e($lang); ?>"
             class="hidden absolute <?php echo e(($algin ?? 'right') === 'left' ? 'left-0' : 'right-0'); ?> mt-2 w-44 rounded-xl
                    bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                    shadow-lg py-1 z-50
                    origin-top-<?php echo e(($algin ?? 'right') === 'left' ? 'left' : 'right'); ?>

                    animate-[scale-in_0.15s_ease-out]">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button
                    class="w-full text-<?php echo e(($algin ?? 'right') === 'left' ? 'left' : 'right'); ?> px-4 py-2.5 text-sm flex items-center gap-2.5
                           transition-colors duration-150
                           <?php echo e($lang === $language->code
                              ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-semibold'
                              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'); ?>"
                    onclick="event.stopPropagation();
                        fetch('<?php echo e(route('lang.switch', $language->code)); ?>', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                'Content-Type': 'application/json'
                            }
                        }).then(function(r) {
                            if (r.ok) { location.reload(); }
                            else { console.error('Language switch failed:', r.status); }
                        }).catch(function(e) {
                            console.error('Language switch error:', e);
                        })">
                    <img src="<?php echo e(asset('images/icons/' . $language->code . '.png')); ?>"
                         alt="<?php echo e(__('general.' . $language->name)); ?>"
                         class="w-5 h-5 rounded-full object-cover border border-gray-200 dark:border-gray-600">
                    <span><?php echo e(__('general.' . Str::lower($language->name))); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lang === $language->code): ?>
                        <ion-icon name="checkmark-outline" class="text-indigo-500 text-sm ms-auto"></ion-icon>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            var menu = document.getElementById('lang-dropdown-menu-<?php echo e($lang); ?>');
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    </script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views/components/lang-switcher.blade.php ENDPATH**/ ?>