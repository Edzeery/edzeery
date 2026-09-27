<?php

use App\Models\Products\Product;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use Livewire\Volt\Component;

?>

<div x-data="{ get open() { return $wire.open } }"
     x-on:keydown.escape.window="$wire.set('open', false)"
     x-on:keydown.meta.k.window.prevent="$wire.set('open', !open)"
     x-on:keydown.ctrl.k.window.prevent="$wire.set('open', !open)"
     x-on:command-palette-toggle.window="$wire.set('open', !open)"
     x-effect="if (open) { $nextTick(() => $refs.searchInput?.focus()) }">

    
    <div x-show="open" x-transition.opacity
         class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
         @click="$wire.set('open', false)"
         x-cloak></div>

    
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-x-0 top-[15vh] z-50 mx-auto max-w-xl px-4"
         x-cloak>
        <div class="rounded-2xl bg-surface border border-surface-border shadow-floating overflow-hidden">

            
            <div class="flex items-center gap-3 px-4 py-3 border-b border-surface-border">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'search','class' => 'w-5 h-5 text-ink-muted flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'w-5 h-5 text-ink-muted flex-shrink-0']); ?>
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
                <input x-ref="searchInput"
                       type="text"
                       wire:model.live.debounce.200ms="query"
                       placeholder="<?php echo e(__('buttons.search')); ?>…"
                       class="flex-1 bg-transparent border-0 outline-none text-sm text-ink placeholder:text-ink-muted" />
                <kbd class="px-1.5 py-0.5 text-[10px] font-mono text-ink-muted bg-surface-secondary border border-surface-border rounded">ESC</kbd>
            </div>

            
            <div class="max-h-80 overflow-y-auto p-2" wire:loading.class="opacity-50">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($results) && strlen($query) >= 2): ?>
                    <div class="px-4 py-8 text-center text-sm text-ink-muted">
                        <?php echo e(__('messages.no_results') ?? 'No results found'); ?>

                    </div>
                <?php elseif(empty($query)): ?>
                    <div class="px-4 py-3 text-xs text-ink-muted">
                        <?php echo e(__('messages.search_hint') ?? 'Type to search products, orders, and pages…'); ?>

                    </div>
                <?php else: ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($result['url']); ?>"
                           wire:navigate
                           @click="$wire.set('open', false)"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-ink
                                  hover:bg-surface-secondary transition-colors duration-150
                                  <?php echo e($i === 0 ? 'bg-surface-secondary' : ''); ?>">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => $result['icon'] ?? 'search-outline','class' => 'w-4 h-4 text-ink-muted flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($result['icon'] ?? 'search-outline'),'class' => 'w-4 h-4 text-ink-muted flex-shrink-0']); ?>
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
                            <div class="min-w-0 flex-1">
                                <div class="font-medium truncate"><?php echo e($result['name']); ?></div>
                                <div class="text-xs text-ink-muted"><?php echo e($result['type']); ?></div>
                            </div>
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-forward-outline','class' => 'w-3.5 h-3.5 text-ink-muted opacity-0 group-hover:opacity-100']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-forward-outline','class' => 'w-3.5 h-3.5 text-ink-muted opacity-0 group-hover:opacity-100']); ?>
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
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/layout/command-palette.blade.php ENDPATH**/ ?>