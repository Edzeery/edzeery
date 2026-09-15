

<?php
    $headerFilterKeys = [
        'state' => 'state',
        'city' => 'city',
        'total' => 'amount',
        'tracking_status' => 'status',
        'products' => 'products',
        'provider' => 'provider',
        'delivery_rider' => 'rider',
        'shipping_date' => 'date',
        'assigned_to' => 'assigned',
        'confirmed_by' => 'confirmed',
    ];

    $headerCol = collect($this->trackingColumns())->firstWhere('key', $colKey);
    $headerHasFilter = $headerCol ? array_key_exists($colKey, $headerFilterKeys) : false;
    $headerFilterKey = $headerHasFilter ? $headerFilterKeys[$colKey] : null;

    $headerLabel = $headerCol
        ? (in_array($colKey, ['tracking_status', 'tracking_number'], true)
            ? __("order_flow.{$headerCol['label_key']}")
            : __("merchant_panel.{$headerCol['label_key']}"))
        : $colKey;

    $headerFilterActive = false;
    if ($headerFilterKey === 'amount') {
        $headerFilterActive = filled($this->filters['amount_min'] ?? null) || filled($this->filters['amount_max'] ?? null);
    } elseif ($headerFilterKey === 'status') {
        $headerFilterActive = count($this->filters['tracking_statuses'] ?? []) > 0;
    } elseif ($headerFilterKey === 'products') {
        $headerFilterActive = count($this->filters['products'] ?? []) > 0;
    } elseif ($headerFilterKey === 'date') {
        $headerFilterActive = filled($this->filters['date_from'] ?? null) || filled($this->filters['date_to'] ?? null);
    } elseif ($headerFilterKey) {
        $headerFilterActive = filled($this->filters[$headerFilterKey] ?? null);
    }
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($headerCol): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($headerHasFilter): ?>
        <th class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase relative group whitespace-nowrap">
            <span class="inline-flex items-center gap-1">
                <?php echo e($headerLabel); ?>

                <button data-filter-btn
                    @click.stop="$dispatch('edz-filter-open', { key: '<?php echo e($headerFilterKey); ?>', el: $event.currentTarget })"
                    class="shrink-0 <?php echo e($headerFilterActive ? 'text-accent-500' : 'text-ink-muted/40 group-hover:text-ink-muted'); ?> transition">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'filter','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'filter','class' => 'w-3 h-3']); ?>
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
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($headerFilterActive): ?>
                    <span class="w-1.5 h-1.5 rounded-full bg-accent-500 shrink-0"></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </span>
        </th>
    <?php else: ?>
        <th class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
            <?php echo e($headerLabel); ?>

        </th>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/tracking/partials/tracking-table-header.blade.php ENDPATH**/ ?>