

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'paginator'   => [],          // ['from','to','total','current_page','last_page']
    'method'      => 'setPage',   // اسم الميثود فـ Livewire component
    'maxVisible'  => 5,           // عدد أزرار الصفحات الظاهرة فـ النص
    'size'        => 'md',        // 'sm' | 'md'
    'showInfo'    => true,
    'showJump'    => false,
    'showPerPage' => false,
    'perPageOptions' => [10, 25, 50, 100],
    'perPageMethod'  => 'setPerPage',
    'perPage'        => 5,
]));

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

foreach (array_filter(([
    'paginator'   => [],          // ['from','to','total','current_page','last_page']
    'method'      => 'setPage',   // اسم الميثود فـ Livewire component
    'maxVisible'  => 5,           // عدد أزرار الصفحات الظاهرة فـ النص
    'size'        => 'md',        // 'sm' | 'md'
    'showInfo'    => true,
    'showJump'    => false,
    'showPerPage' => false,
    'perPageOptions' => [10, 25, 50, 100],
    'perPageMethod'  => 'setPerPage',
    'perPage'        => 5,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $current = (int) ($paginator['current_page'] ?? 1);
    $last    = (int) ($paginator['last_page'] ?? 1);
    $total   = (int) ($paginator['total'] ?? 0);
    $from    = $paginator['from'] ?? 0;
    $to      = $paginator['to'] ?? 0;

    $sizeClasses = $size === 'sm'
        ? ['btn' => 'h-7 min-w-[1.75rem] text-xs', 'gap' => 'gap-0.5']
        : ['btn' => 'h-8 min-w-[2rem] text-sm', 'gap' => 'gap-1'];

    // بناء لائحة الصفحات مع ellipsis
    $pages = [];
    if ($last <= 1) {
        $pages = [1];
    } else {
        $delta = (int) floor(($maxVisible - 1) / 2);
        $rangeStart = max(2, $current - $delta);
        $rangeEnd   = min($last - 1, $current + $delta);

        if ($current - $delta <= 2) {
            $rangeEnd = min($last - 1, $rangeEnd + (2 - ($current - $delta)));
        }
        if ($current + $delta >= $last - 1) {
            $rangeStart = max(2, $rangeStart - (($current + $delta) - ($last - 1)));
        }

        $pages = [1];
        if ($rangeStart > 2) $pages[] = '...';
        for ($i = $rangeStart; $i <= $rangeEnd; $i++) $pages[] = $i;
        if ($rangeEnd < $last - 1) $pages[] = '...';
        if ($last > 1) $pages[] = $last;
    }
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($total > 0): ?>
<nav
    role="navigation"
    aria-label="<?php echo e(__('تصفح الصفحات')); ?>"
    <?php echo e($attributes->class(['edz-pagination flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-surface-border'])); ?>

    wire:loading.class="opacity-60 pointer-events-none"
    wire:target="<?php echo e($method); ?>"
>
    
    <div class="flex items-center gap-3 order-2 sm:order-1">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showInfo): ?>
            <span class="text-sm text-ink-muted tabular-nums whitespace-nowrap">
                <?php echo e(__('عرض')); ?>

                <span class="font-medium text-ink"><?php echo e($from); ?>–<?php echo e($to); ?></span>
                <?php echo e(__('من')); ?>

                <span class="font-medium text-ink"><?php echo e($total); ?></span>
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPerPage): ?>
            <select
                wire:change="<?php echo e($perPageMethod); ?>($event.target.value)"
                class="edz-select edz-select--xs h-7 rounded-lg border-surface-border text-xs text-ink-muted
                       focus:ring-2 focus:ring-[--edz-accent] focus:border-transparent transition"
                aria-label="<?php echo e(__('عدد العناصر فـ الصفحة')); ?>"
            >
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $perPageOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($opt); ?>" <?php if((int) $perPage === (int) $opt): echo 'selected'; endif; ?>>
                        <?php echo e($opt); ?> / <?php echo e(__('صفحة')); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($last > 1): ?>
        <div class="flex items-center <?php echo e($sizeClasses['gap']); ?> order-1 sm:order-2">

            
            <button
                type="button"
                wire:click="<?php echo e($method); ?>(<?php echo e($current - 1); ?>)"
                wire:loading.attr="disabled"
                <?php if($current <= 1): echo 'disabled'; endif; ?>
                aria-label="<?php echo e(__('الصفحة السابقة')); ?>"
                class="edz-pagination__btn edz-pagination__btn--nav <?php echo e($sizeClasses['btn']); ?>

                       flex items-center justify-center rounded-lg text-ink-muted
                       hover:bg-surface-hover disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[--edz-accent]
                       transition-all duration-150"
            >
                <svg class="w-4 h-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pg === '...'): ?>
                    <span class="<?php echo e($sizeClasses['btn']); ?> flex items-center justify-center text-ink-muted select-none">
                        &hellip;
                    </span>
                <?php else: ?>
                    <button
                        type="button"
                        wire:click="<?php echo e($method); ?>(<?php echo e($pg); ?>)"
                        wire:loading.attr="disabled"
                        wire:key="edz-page-<?php echo e($pg); ?>"
                        aria-current="<?php echo e($pg === $current ? 'page' : 'false'); ?>"
                        aria-label="<?php echo e(__('الصفحة')); ?> <?php echo e($pg); ?>"
                        class="edz-pagination__btn <?php echo e($sizeClasses['btn']); ?> px-2 rounded-lg font-medium tabular-nums
                               transition-all duration-150 ease-out
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[--edz-accent]
                               <?php echo e($pg === $current
                                   ? 'edz-btn edz-btn--primary edz-btn--sm text-white shadow-sm'
                                   : 'text-ink-muted hover:bg-surface-hover'); ?>"
                    >
                        <?php echo e($pg); ?>

                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <button
                type="button"
                wire:click="<?php echo e($method); ?>(<?php echo e($current + 1); ?>)"
                wire:loading.attr="disabled"
                <?php if($current >= $last): echo 'disabled'; endif; ?>
                aria-label="<?php echo e(__('الصفحة التالية')); ?>"
                class="edz-pagination__btn edz-pagination__btn--nav <?php echo e($sizeClasses['btn']); ?>

                       flex items-center justify-center rounded-lg text-ink-muted
                       hover:bg-surface-hover disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[--edz-accent]
                       transition-all duration-150"
            >
                <svg class="w-4 h-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showJump && $last > 10): ?>
                <div class="flex items-center gap-1 ms-2 ps-2 border-s border-surface-border">
                    <span class="text-xs text-ink-muted whitespace-nowrap"><?php echo e(__('الذهاب إلى')); ?></span>
                    <input
                        type="number"
                        min="1"
                        max="<?php echo e($last); ?>"
                        x-data
                        @keydown.enter="
                            const val = Math.min(<?php echo e($last); ?>, Math.max(1, parseInt($event.target.value) || 1));
                            $wire.<?php echo e($method); ?>(val);
                            $event.target.value = '';
                        "
                        class="w-14 h-7 rounded-lg border-surface-border text-xs text-center tabular-nums
                               focus:ring-2 focus:ring-[--edz-accent] focus:border-transparent transition"
                        placeholder="<?php echo e($current); ?>"
                        aria-label="<?php echo e(__('رقم الصفحة')); ?>"
                    />
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</nav>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>



<?php /**PATH C:\laragon\www\edzeery\resources\views/components/edz/pagination.blade.php ENDPATH**/ ?>