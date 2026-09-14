<ul
    <?php echo e($getExtraAttributeBag()
            ->grid($getColumns(), \Filament\Support\Enums\GridDirection::Column)
            ->class([
                'fi-sc-unordered-list',
                (($size = $getSize()) instanceof \Filament\Support\Enums\TextSize) ? "fi-size-{$size->value}" : $size,
            ])); ?>

>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $getChildSchema()->getComponents(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schemaComponent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li>
            <?php echo e($schemaComponent); ?>

        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</ul>
<?php /**PATH C:\laragon\www\edzeery\vendor\filament\schemas\resources\views\components\unordered-list.blade.php ENDPATH**/ ?>