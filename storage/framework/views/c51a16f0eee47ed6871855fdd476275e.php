<?php

use App\Domains\Order\Models\UserColumnPreference;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Order\Services\OrderService;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Customers\Customer;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Validator;

?>

<div x-data="{ openFilter: null, openColToggle: false }">
    
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <?php if (isset($component)) { $__componentOriginal64446345db7363332d7ff2707d878bc4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal64446345db7363332d7ff2707d878bc4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.page-header','data' => ['title' => ''.e(__('merchant_panel.orders')).'','description' => ''.e(__('merchant_panel.manage_customer_orders')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(__('merchant_panel.orders')).'','description' => ''.e(__('merchant_panel.manage_customer_orders')).'']); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal64446345db7363332d7ff2707d878bc4)): ?>
<?php $attributes = $__attributesOriginal64446345db7363332d7ff2707d878bc4; ?>
<?php unset($__attributesOriginal64446345db7363332d7ff2707d878bc4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal64446345db7363332d7ff2707d878bc4)): ?>
<?php $component = $__componentOriginal64446345db7363332d7ff2707d878bc4; ?>
<?php unset($__componentOriginal64446345db7363332d7ff2707d878bc4); ?>
<?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($orders['filtered_total'])): ?>
            <div class="flex items-center gap-6 px-4 py-2 mb-4 text-sm text-ink-muted">
                <span><?php echo e($orders['filtered_total']); ?> <?php echo e(__('merchant.orders_count')); ?></span>
                <span class="font-semibold text-ink"><?php echo e(currency($orders['filtered_amount'] ?? 0)); ?></span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="flex items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button wire:click="openCreateModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'plus','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'w-4 h-4']); ?>
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
                    <?php echo e(__('merchant_panel.new_order')); ?>

                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <button wire:click="$refresh" class="edz-btn edz-btn--ghost edz-btn--sm">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-path','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-path','class' => 'w-4 h-4']); ?>
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
        </div>
    </div>

    
    <div class="edz-card edz-card--padded mb-4">
        
        <div class="flex flex-wrap items-center gap-3">
            
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="<?php echo e(__('merchant.search_orders')); ?>" class="edz-input text-sm ps-9">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'search','class' => 'absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-muted pointer-events-none']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-muted pointer-events-none']); ?>
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

            
            <div class="relative" @click.away="openColToggle = false">
                <button @click="openColToggle = !openColToggle" class="edz-btn edz-btn--ghost edz-btn--sm">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'view-columns','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'view-columns','class' => 'w-4 h-4']); ?>
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
                    <?php echo e(__('merchant_panel.columns')); ?>

                </button>
                <div x-show="openColToggle" x-transition
                    class="absolute z-40 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-2 space-y-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['number' => '#', 'customer' => 'Customer', 'phone' => 'Phone', 'wilaya' => 'Wilaya', 'products' => 'Products', 'amount' => 'Amount', 'status' => 'Status', 'assigned_agent' => 'Agent', 'created_at' => 'Date', 'confirmation_attempts' => 'Attempts', 'last_contact' => 'Last Contact', 'weight' => 'Weight', 'shipment_type' => 'Shipment']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label
                            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-surface-secondary dark:hover:bg-ink-700 cursor-pointer text-sm">
                            <input type="checkbox" wire:click="toggleColumn('<?php echo e($col); ?>')"
                                <?php echo e(in_array($col, $this->visibleColumns) ? 'checked' : ''); ?>

                                class="rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                            <?php echo e($label); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <button x-on:click="$wire.set('showAdvancedFilters', !$wire.get('showAdvancedFilters'))"
                class="edz-btn edz-btn--ghost edz-btn--sm">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'adjustments','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'adjustments','class' => 'w-4 h-4']); ?>
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
                <?php echo e(__('merchant_panel.filters')); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(array_filter($this->filters)): ?>
                    <span
                        class="ml-1 px-1.5 py-0.5 text-[10px] rounded-full bg-accent-100 text-accent-700"><?php echo e(count(array_filter($this->filters))); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>

            
            <button wire:click="toggleTrash"
                class="edz-btn edz-btn--ghost edz-btn--sm <?php echo e($this->showTrash ? 'text-danger-600' : ''); ?>">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trash','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'w-4 h-4']); ?>
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
                <?php echo e(__('merchant.trash_bin')); ?>

            </button>
            <div class="flex items-center gap-1 text-xs text-ink-muted" x-data="{ pp: <?php echo e($this->perPage); ?> }">
                <span><?php echo e(__('merchant.per_page')); ?></span>
                <select x-model="pp" x-on:change="$wire.setPerPage(parseInt($event.target.value))"
                    class="text-xs border border-surface-border rounded-lg px-2 py-1 bg-surface dark:bg-ink-800 text-ink focus:outline-none focus:ring-1 focus:ring-[var(--store-primary)]">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        
        <div x-show="$wire.showAdvancedFilters" x-transition class="mt-3 pt-3 border-t border-surface-border">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">

                
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block"><?php echo e(__('merchant_panel.source')); ?></label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs">
                        <?php echo e($this->filters['source'] === 'manual' ? __('merchant.delivery_man') : ($this->filters['source'] === 'store' ? __('merchant_panel.store') : '—')); ?>

                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3 h-3']); ?>
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
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
                        <button wire:click="setFilter('source', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        <button wire:click="setFilter('source', 'store')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"><?php echo e(__('merchant_panel.store')); ?></button>
                        <button wire:click="setFilter('source', 'manual')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"><?php echo e(__('merchant.delivery_man')); ?></button>
                    </div>
                </div>

                
                <div x-data="{ open: false, q: '' }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block"><?php echo e(__('merchant_panel.wilaya')); ?></label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs truncate">
                        <?php echo e(collect($this->allStates)->firstWhere('id', $this->filters['wilaya'])['name'] ?? '—'); ?>

                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']); ?>
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
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <input type="text" x-model="q" placeholder="Search..."
                            class="w-full text-xs px-2 py-1 mb-1 rounded border border-surface-border bg-surface dark:bg-ink-700 focus:outline-none">
                        <button wire:click="setFilter('wilaya', null); $wire.setFilter('city', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allStates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button
                                wire:click="setFilter('wilaya', '<?php echo e($st['id']); ?>'); $wire.loadFilterCities('<?php echo e($st['id']); ?>')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"
                                x-show="!q || '<?php echo e($st['name']); ?>'.toLowerCase().includes(q.toLowerCase())">
                                <?php echo e($st['name']); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div x-data="{ open: false, q: '' }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block"><?php echo e(__('merchant_panel.commune')); ?></label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs truncate">
                        <?php echo e(collect($this->allCities)->firstWhere('id', $this->filters['city'])['name'] ?? '—'); ?>

                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']); ?>
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
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <input type="text" x-model="q" placeholder="Search..."
                            class="w-full text-xs px-2 py-1 mb-1 rounded border border-surface-border bg-surface dark:bg-ink-700 focus:outline-none">
                        <button wire:click="setFilter('city', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allCities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button wire:click="setFilter('city', '<?php echo e($ci['id']); ?>')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"
                                x-show="!q || '<?php echo e($ci['name']); ?>'.toLowerCase().includes(q.toLowerCase())">
                                <?php echo e($ci['name']); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div x-data="{ open: false, q: '' }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block"><?php echo e(__('merchant_panel.status')); ?></label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs">
                        <?php echo e(count($this->filters['status'] ?? []) ? count($this->filters['status']) . ' ' . __('merchant.selected') : '—'); ?>

                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']); ?>
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
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <input type="text" x-model="q" placeholder="Search..."
                            class="w-full text-xs px-2 py-1 mb-1 rounded border border-surface-border bg-surface dark:bg-ink-700 focus:outline-none">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label
                                class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-surface-secondary cursor-pointer text-xs"
                                x-show="!q || '<?php echo e($s['label']); ?>'.toLowerCase().includes(q.toLowerCase())">
                                <input type="checkbox" value="<?php echo e($s['id']); ?>"
                                    wire:click="toggleStatusFilter('<?php echo e($s['id']); ?>')"
                                    <?php echo e(in_array($s['id'], $this->filters['status'] ?? []) ? 'checked' : ''); ?>

                                    class="rounded border-gray-300">
                                <span class="w-2 h-2 rounded-full shrink-0"
                                    style="background: <?php echo e(match ($s['color'] ?? 'gray') {'success' => '#22c55e','info' => '#3b82f6','warning' => '#f59e0b','danger' => '#ef4444',default => '#6b7280'}); ?>"></span>
                                <?php echo e($s['label']); ?>

                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div>
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block"><?php echo e(__('merchant_panel.products')); ?></label>
                    <input type="text" wire:model.live.debounce.500ms="filters.product" placeholder="..."
                        class="edz-input text-xs w-full">
                </div>

                
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block"><?php echo e(__('storefront.delivery_type')); ?></label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs">
                        <?php echo e($this->filters['delivery_type'] === 'stopdesk' ? __('storefront.stop_desk') : ($this->filters['delivery_type'] === 'home' ? __('storefront.home_delivery') : '—')); ?>

                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']); ?>
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
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
                        <button wire:click="setFilter('delivery_type', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        <button wire:click="setFilter('delivery_type', 'home')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"><?php echo e(__('storefront.home_delivery')); ?></button>
                        <button wire:click="setFilter('delivery_type', 'stopdesk')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"><?php echo e(__('storefront.stop_desk')); ?></button>
                    </div>
                </div>

                
                <div x-data="{ open: false, q: '' }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block"><?php echo e(__('merchant.assign_delivery_man')); ?></label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs truncate">
                        <?php echo e(collect($this->allProviders)->firstWhere('id', $this->filters['shipping_provider'])['name'] ?? '—'); ?>

                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']); ?>
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
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <input type="text" x-model="q" placeholder="Search..."
                            class="w-full text-xs px-2 py-1 mb-1 rounded border border-surface-border bg-surface dark:bg-ink-700 focus:outline-none">
                        <button wire:click="setFilter('shipping_provider', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button wire:click="setFilter('shipping_provider', '<?php echo e($pr['id']); ?>')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"
                                x-show="!q || '<?php echo e($pr['name']); ?>'.toLowerCase().includes(q.toLowerCase())">
                                <?php echo e($pr['name']); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div x-data="{ open: false, q: '' }" @click.away="open = false" class="relative">
                    <label
                        class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block"><?php echo e(__('merchant_panel.assigned_to')); ?></label>
                    <button @click="open = !open"
                        class="edz-btn edz-btn--ghost edz-btn--sm w-full justify-between text-xs truncate">
                        <?php echo e(collect($this->allMembers)->firstWhere('id', $this->filters['assigned_to'])['user']['name'] ?? '—'); ?>

                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3 h-3 shrink-0']); ?>
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
                    <div x-show="open" x-transition
                        class="absolute z-40 mt-1 w-full bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <input type="text" x-model="q" placeholder="Search..."
                            class="w-full text-xs px-2 py-1 mb-1 rounded border border-surface-border bg-surface dark:bg-ink-700 focus:outline-none">
                        <button wire:click="setFilter('assigned_to', null)"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">—</button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button wire:click="setFilter('assigned_to', '<?php echo e($m['id']); ?>')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary"
                                x-show="!q || '<?php echo e($m['user']['name']); ?>'.toLowerCase().includes(q.toLowerCase())">
                                <?php echo e($m['user']['name']); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div class="flex gap-1">
                    <div class="flex-1">
                        <label
                            class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block"><?php echo e(__('merchant_panel.amount')); ?></label>
                        <input type="number" wire:model.live.debounce.500ms="filters.amount_min" placeholder="Min"
                            class="edz-input text-xs w-full">
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">&nbsp;</label>
                        <input type="number" wire:model.live.debounce.500ms="filters.amount_max" placeholder="Max"
                            class="edz-input text-xs w-full">
                    </div>
                </div>

                
                <div class="flex gap-1">
                    <div class="flex-1">
                        <label
                            class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block"><?php echo e(__('merchant_panel.created_at')); ?></label>
                        <input type="text" wire:model="filters.date_from"
                            class="edz-input text-xs w-full flatpickr-input"
                            placeholder="YYYY-MM-DD" autocomplete="off">
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] uppercase font-semibold text-ink-muted mb-1 block">&nbsp;</label>
                        <input type="text" wire:model="filters.date_to"
                            class="edz-input text-xs w-full flatpickr-input"
                            placeholder="YYYY-MM-DD" autocomplete="off">
                    </div>
                </div>

            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($this->search) || array_filter($this->filters)): ?>
                <div class="mt-3 flex justify-end">
                    <button x-on:click="$wire.set('search', ''); $wire.clearFilters()"
                        class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-circle','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-circle','class' => 'w-4 h-4']); ?>
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
                        <?php echo e(__('merchant_panel.clear_filters')); ?>

                    </button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>



    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showTrash): ?>
        <div class="mb-4 p-3 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-700 rounded-xl flex items-center justify-between">
            <span class="text-sm text-warning-700 dark:text-warning-400 font-medium">
                <?php echo e(__('merchant.trash_bin')); ?> — <?php echo e($orders['total'] ?? 0); ?>

            </span>
            <div class="flex gap-2">
                <button wire:click="restoreAll" class="edz-btn edz-btn--ghost edz-btn--sm"><?php echo e(__('merchant.restore_all')); ?></button>
                <button wire:click="forceDeleteAll" x-data x-on:click.prevent="EdzSwal.confirmDelete(() => { $wire.forceDeleteAll() })" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600"><?php echo e(__('merchant.empty_trash')); ?></button>
            </div>
        </div>
    <?php elseif(count($this->selectedOrders) > 0): ?>
        <div class="mb-4 p-3 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-700 rounded-xl flex items-center justify-between sticky top-0 z-30">
            <span class="text-sm text-accent-700 dark:text-accent-400 font-medium">
                <?php echo e(count($this->selectedOrders)); ?> <?php echo e(__('merchant.orders_count')); ?>

            </span>
            <div class="flex gap-2 flex-wrap">
                
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" class="edz-btn edz-btn--ghost edz-btn--sm">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'user-plus','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user-plus','class' => 'w-4 h-4']); ?>
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
                        <?php echo e(__('merchant.bulk_assign_agent')); ?>

                    </button>
                    <div x-show="open" x-transition class="absolute z-50 right-0 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-60 overflow-y-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button wire:click="bulkAssignAgent('<?php echo e($m['id']); ?>')"
                                class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">
                                <?php echo e($m['user']['name']); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($this->allProviders) > 0): ?>
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" class="edz-btn edz-btn--ghost edz-btn--sm">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'w-4 h-4']); ?>
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
                            <?php echo e(__('merchant.bulk_send_carrier')); ?>

                        </button>
                        <div x-show="open" x-transition class="absolute z-50 right-0 mt-1 w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button wire:click="bulkSendToCarrier('<?php echo e($pr['id']); ?>')"
                                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary">
                                    <?php echo e($pr['name']); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <button wire:click="bulkDelete" x-data x-on:click.prevent="EdzSwal.confirmDelete(() => { $wire.bulkDelete() })" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trash','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'w-4 h-4']); ?>
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
                    <?php echo e(__('merchant.bulk_delete')); ?>

                </button>

                <button wire:click="clearSelection" class="edz-btn edz-btn--ghost edz-btn--sm">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-4 h-4']); ?>
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
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="edz-card overflow-hidden">
        <div class="relative">
            
            <div wire:loading
                class="absolute inset-0 z-10 bg-surface/80 backdrop-blur-sm p-4 space-y-3 overflow-hidden"
                wire:target="filters,loadOrders">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 5; $i++): ?>
                    <div class="flex items-center gap-4 py-2">
                        <?php if (isset($component)) { $__componentOriginal5de3ae0055df979b9147956bfeaefa52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5de3ae0055df979b9147956bfeaefa52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.skeleton','data' => ['width' => '5rem','height' => '0.875rem']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.skeleton'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '5rem','height' => '0.875rem']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $attributes = $__attributesOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $component = $__componentOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__componentOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
                        <div class="flex-1 space-y-1.5">
                            <?php if (isset($component)) { $__componentOriginal5de3ae0055df979b9147956bfeaefa52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5de3ae0055df979b9147956bfeaefa52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.skeleton','data' => ['width' => '40%']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.skeleton'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '40%']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $attributes = $__attributesOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $component = $__componentOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__componentOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal5de3ae0055df979b9147956bfeaefa52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5de3ae0055df979b9147956bfeaefa52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.skeleton','data' => ['width' => '6rem','height' => '0.75rem']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.skeleton'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '6rem','height' => '0.75rem']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $attributes = $__attributesOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $component = $__componentOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__componentOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
                        </div>
                        <?php if (isset($component)) { $__componentOriginal5de3ae0055df979b9147956bfeaefa52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5de3ae0055df979b9147956bfeaefa52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.skeleton','data' => ['width' => '5rem','height' => '1.5rem','rounded' => 'full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.skeleton'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '5rem','height' => '1.5rem','rounded' => 'full']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $attributes = $__attributesOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $component = $__componentOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__componentOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal5de3ae0055df979b9147956bfeaefa52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5de3ae0055df979b9147956bfeaefa52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.skeleton','data' => ['width' => '4rem']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.skeleton'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '4rem']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $attributes = $__attributesOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $component = $__componentOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__componentOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal5de3ae0055df979b9147956bfeaefa52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5de3ae0055df979b9147956bfeaefa52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.skeleton','data' => ['width' => '6rem','height' => '0.75rem']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.skeleton'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['width' => '6rem','height' => '0.75rem']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $attributes = $__attributesOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__attributesOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5de3ae0055df979b9147956bfeaefa52)): ?>
<?php $component = $__componentOriginal5de3ae0055df979b9147956bfeaefa52; ?>
<?php unset($__componentOriginal5de3ae0055df979b9147956bfeaefa52); ?>
<?php endif; ?>
                    </div>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div wire:loading.class="opacity-40 pointer-events-none" wire:target="filters,loadOrders">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($orders['data'])): ?>
                    <div class="overflow-x-auto max-h-[calc(100vh-320px)] overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-secondary">
                                <tr>
                                    <th class="px-3 py-3 w-10">
                                        <input type="checkbox" wire:model="selectAll" wire:click="toggleSelectAll"
                                            class="rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                    </th>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('number', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.number')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('customer', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.customer')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('phone', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.phone')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('wilaya', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.state')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('products', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.products')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('amount', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.amount')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('status', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.status')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('assigned_agent', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.agent')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('created_at', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.date')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('confirmation_attempts', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.attempts')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('last_contact', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.last_contact')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('weight', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.weight')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('shipment_type', $this->visibleColumns)): ?>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold text-ink-muted uppercase">
                                            <?php echo e(__('merchant_panel.shipment')); ?></th>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <th class="px-4 py-3 text-end text-xs font-semibold text-ink-muted uppercase">
                                        <?php echo e(__('merchant_panel.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-100 dark:divide-ink-800">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $orders['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $transitions = $order['transitions'] ?? [];
                                        $orderId = $order['id'] ?? '';
                                    ?>
                                    <tr class="hover:bg-surface-50 dark:hover:bg-ink-800/50 <?php echo e(in_array($orderId, $this->selectedOrders) ? 'bg-accent-50 dark:bg-accent-900/10' : ''); ?>">
                                        <td class="px-3 py-3 w-10">
                                            <input type="checkbox" value="<?php echo e($orderId); ?>"
                                                wire:click="toggleSelectOrder('<?php echo e($orderId); ?>')"
                                                <?php echo e(in_array($orderId, $this->selectedOrders) ? 'checked' : ''); ?>

                                                class="rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                        </td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('number', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 font-mono font-semibold text-ink">
                                                #<?php echo e($order['number']); ?>

                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('customer', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3">
                                                <div class="text-ink font-medium">
                                                    <?php echo e($order['customer']['name'] ?? '-'); ?></div>
                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('phone', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                <?php echo e($order['customer']['phone'] ?? '-'); ?>

                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('wilaya', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                <?php echo e($order['state']['name'] ?? '-'); ?>

                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('products', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate"
                                                title="<?php echo e(collect($order['items_summary'] ?? [])->map(fn($i) => $i['name'] . ' ×' . $i['qty'])->implode(', ')); ?>">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order['items_summary'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php echo e($item['name']); ?> ×<?php echo e($item['qty']); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>
                                                        ,
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('amount', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 font-semibold text-ink">
                                                <?php echo e(currency($order['total_amount'])); ?>

                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('status', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3">
                                                <div class="relative" x-data="{ open: false, top: 0, left: 0 }"
                                                    @click.away="open = false">
                                                    <button
                                                        @click="
                                                        const r = $refs.trigger.getBoundingClientRect();
                                                        top = r.bottom + 4;
                                                        left = r.left;
                                                        if (top + 260 > window.innerHeight) top = r.top - 260;
                                                        open = !open;
                                                    "
                                                        x-ref="trigger"
                                                        class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full cursor-pointer hover:opacity-80"
                                                        style="background: <?php echo e(match ($order['status']['color'] ?? 'gray') {'success' => '#dcfce7','info' => '#dbeafe','warning' => '#fef3c7','danger' => '#fecaca',default => '#f3f4f6'}); ?>; color: <?php echo e(match ($order['status']['color'] ?? 'gray') {'success' => '#166534','info' => '#1e40af','warning' => '#92400e','danger' => '#991b1b',default => '#374151'}); ?>;">
                                                        <?php echo e($order['status']['label'] ?? '—'); ?>

                                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3 h-3']); ?>
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
                                                    <div x-show="open" x-transition x-cloak
                                                        class="fixed z-[200] w-56 bg-surface dark:bg-ink-800 border border-surface-border rounded-xl shadow-lg p-1.5 max-h-64 overflow-y-auto"
                                                        :style="'top:' + top + 'px; left:' + Math.min(left, window.innerWidth -
                                                            240) + 'px'">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($s['key'], $transitions) || $s['id'] == $order['status_id']): ?>
                                                                <button
                                                                    wire:click="transitionOrder('<?php echo e($orderId); ?>', '<?php echo e($s['key']); ?>')"
                                                                    @click="open = false"
                                                                    class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-secondary dark:hover:bg-ink-700 <?php echo e($s['id'] == $order['status_id'] ? 'font-bold' : ''); ?>">
                                                                    <span class="w-2 h-2 rounded-full shrink-0"
                                                                        style="background: <?php echo e(match ($s['color'] ?? 'gray') {'success' => '#22c55e','info' => '#3b82f6','warning' => '#f59e0b','danger' => '#ef4444',default => '#6b7280'}); ?>"></span>
                                                                    <?php echo e($s['label']); ?>

                                                                </button>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('assigned_agent', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 text-xs text-ink-muted">
                                                <?php echo e($order['assigned_membership']['user']['name'] ?? '—'); ?>

                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('created_at', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                <?php echo e(\Carbon\Carbon::parse($order['created_at'])->format('M d, Y')); ?>

                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('confirmation_attempts', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                <?php echo e($order['confirmation_attempts'] ?? 0); ?>

                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('last_contact', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                <?php echo e($order['last_contact_at'] ? \Carbon\Carbon::parse($order['last_contact_at'])->diffForHumans() : '—'); ?>

                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('weight', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 text-ink-muted text-xs">
                                                <?php echo e($order['weight_kg'] ? $order['weight_kg'] . ' kg' : '—'); ?>

                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('shipment_type', $this->visibleColumns)): ?>
                                            <td class="px-4 py-3 text-ink-muted text-xs capitalize">
                                                <?php echo e($order['shipment_type'] ?? '—'); ?>

                                            </td>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1 flex-nowrap">
                                                <button wire:click="toggleDetail('<?php echo e($orderId); ?>')"
                                                    class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                    title="<?php echo e(__('merchant.order_details')); ?>">
                                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-right','class' => 'w-4 h-4 shrink-0 transition-transform duration-200 '.e($this->expandedOrderId === $orderId ? 'rotate-90' : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-right','class' => 'w-4 h-4 shrink-0 transition-transform duration-200 '.e($this->expandedOrderId === $orderId ? 'rotate-90' : '').'']); ?>
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
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                                                    <button wire:click="openEditModal('<?php echo e($orderId); ?>')"
                                                        class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                        title="Edit">
                                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'edit','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit','class' => 'w-4 h-4 shrink-0']); ?>
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
                                                    <button wire:click="openReassignModal('<?php echo e($orderId); ?>')"
                                                        class="edz-btn edz-btn--ghost edz-btn--xs shrink-0"
                                                        title="Reassign">
                                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrows-right-left','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrows-right-left','class' => 'w-4 h-4 shrink-0']); ?>
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
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE->value)): ?>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showTrash): ?>
                                                        <button wire:click="restoreOrder('<?php echo e($orderId); ?>')"
                                                            class="edz-btn edz-btn--ghost edz-btn--xs shrink-0 text-success-600"
                                                            title="<?php echo e(__('merchant.restore_order')); ?>">
                                                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-uturn-left','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-uturn-left','class' => 'w-4 h-4 shrink-0']); ?>
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
                                                    <?php else: ?>
                                                        <button
                                                            class="edz-btn edz-btn--ghost edz-btn--xs text-danger-600 hover:text-danger-700 shrink-0"
                                                            x-data
                                                            x-on:click.prevent="EdzSwal.confirmDelete(() => { $wire.deleteOrder('<?php echo e($orderId); ?>') })"
                                                            title="<?php echo e(__('merchant.delete_permanently')); ?>">
                                                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'trash','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','class' => 'w-4 h-4 shrink-0']); ?>
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
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->expandedOrderId === $orderId): ?>
                                        <tr>
                                            <td colspan="99" class="px-4 py-4 bg-surface-50 dark:bg-ink-800/30">
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                                                    <div>
                                                        <h4 class="font-semibold text-ink mb-2">Items</h4>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order['items_summary'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <div
                                                                class="flex justify-between py-1 border-b border-surface-200 dark:border-ink-700">
                                                                <span class="text-ink-muted"><?php echo e($item['name']); ?>

                                                                    ×<?php echo e($item['qty']); ?></span>
                                                                <span
                                                                    class="font-medium text-ink"><?php echo e(currency($item['price'] * $item['qty'])); ?></span>
                                                            </div>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        <div class="flex justify-between pt-2 font-bold text-ink">
                                                            <span>Total</span>
                                                            <span><?php echo e(currency($order['total_amount'])); ?></span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-semibold text-ink mb-2">Details</h4>
                                                        <dl class="space-y-1 text-ink-muted">
                                                            <div class="flex justify-between">
                                                                <dt>Delivery:</dt>
                                                                <dd class="text-ink capitalize">
                                                                    <?php echo e($order['delivery_type'] ?? '—'); ?></dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>Shipment:</dt>
                                                                <dd class="text-ink capitalize">
                                                                    <?php echo e($order['shipment_type'] ?? '—'); ?></dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>Payment:</dt>
                                                                <dd class="text-ink uppercase">
                                                                    <?php echo e($order['payment_method'] ?? '—'); ?></dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>Weight:</dt>
                                                                <dd class="text-ink">
                                                                    <?php echo e($order['weight_kg'] ? $order['weight_kg'] . ' kg' : '—'); ?>

                                                                </dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>Phone 2:</dt>
                                                                <dd class="text-ink">
                                                                    <?php echo e($order['phone_secondary'] ?? '—'); ?></dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>Address:</dt>
                                                                <dd class="text-ink"><?php echo e($order['address'] ?? '—'); ?>

                                                                </dd>
                                                            </div>
                                                        </dl>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-semibold text-ink mb-2">Assignment</h4>
                                                        <dl class="space-y-1 text-ink-muted">
                                                            <div class="flex justify-between">
                                                                <dt>Agent:</dt>
                                                                <dd class="text-ink">
                                                                    <?php echo e($order['assigned_membership']['user']['name'] ?? '—'); ?>

                                                                </dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>Method:</dt>
                                                                <dd class="text-ink capitalize">
                                                                    <?php echo e($order['assignment_method'] ?? '—'); ?></dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>Created by:</dt>
                                                                <dd class="text-ink">
                                                                    <?php echo e($order['created_by_membership_id'] ? $order['created_by_membership']['user']['name'] ?? '—' : '—'); ?>

                                                                </dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>Attempts:</dt>
                                                                <dd class="text-ink">
                                                                    <?php echo e($order['confirmation_attempts'] ?? 0); ?></dd>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <dt>Last contact:</dt>
                                                                <dd class="text-ink">
                                                                    <?php echo e($order['last_contact_at'] ? \Carbon\Carbon::parse($order['last_contact_at'])->diffForHumans() : '—'); ?>

                                                                </dd>
                                                            </div>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($order['notes'])): ?>
                                                                <div
                                                                    class="mt-2 p-2 bg-surface-100 dark:bg-ink-700 rounded-lg text-ink-muted italic">
                                                                    "<?php echo e($order['notes']); ?>"</div>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </dl>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    
                    <div class="p-4 border-t border-surface-border">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-ink-muted">
                                    <?php echo e($orders['from'] ?? 0); ?>–<?php echo e($orders['to'] ?? 0); ?> / <?php echo e($orders['total']); ?>

                                </span>

                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders['last_page'] > 1): ?>
                                <div class="flex gap-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders['current_page'] > 1): ?>
                                        <button wire:click="setPage(<?php echo e($orders['current_page'] - 1); ?>)"
                                            class="edz-btn edz-btn--ghost edz-btn--xs">&laquo;</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(max(1, $orders['current_page'] - 2), min($orders['last_page'], $orders['current_page'] + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <button wire:click="setPage(<?php echo e($pg); ?>)"
                                            class="edz-btn edz-btn--xs <?php echo e($pg === $orders['current_page'] ? 'edz-btn--primary' : 'edz-btn--ghost'); ?>">
                                            <?php echo e($pg); ?>

                                        </button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders['current_page'] < $orders['last_page']): ?>
                                        <button wire:click="setPage(<?php echo e($orders['current_page'] + 1); ?>)"
                                            class="edz-btn edz-btn--ghost edz-btn--xs">&raquo;</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-8 text-center text-ink-muted">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'cart','class' => 'w-12 h-12 mx-auto mb-3 text-ink-muted opacity-40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','class' => 'w-12 h-12 mx-auto mb-3 text-ink-muted opacity-40']); ?>
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
                        <p><?php echo e(__('No orders found')); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCreateModal || $showEditModal): ?>
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'lg','wire:key' => 'order-create-edit-'.e($showCreateModal ? 'create' : 'edit').'-'.e($showEditModal ? $editingOrderId : 'new').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isOpen' => true,'size' => 'lg','wire:key' => 'order-create-edit-'.e($showCreateModal ? 'create' : 'edit').'-'.e($showEditModal ? $editingOrderId : 'new').'']); ?>
            <form wire:submit="<?php echo e($showEditModal ? 'submitEdit' : 'submitCreate'); ?>">
                <div class="p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-ink">
                            <?php echo e($showEditModal ? __('Edit Order') : __('New Order')); ?></h3>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm">
                                <?php echo e($showEditModal ? __('Update') : __('Create')); ?>

                            </button>
                            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="<?php echo e($showEditModal ? 'set(\'showEditModal\', false)' : 'set(\'showCreateModal\', false)'); ?>">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-5 h-5']); ?>
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
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label">Name *</label>
                            <input type="text" wire:model="form.customer_name" class="edz-input text-sm" required>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.customer_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="edz-label">Phone *</label>
                            <input type="tel" wire:model="form.customer_phone" class="edz-input text-sm"
                                required>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.customer_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="edz-label">Phone 2</label>
                            <input type="tel" wire:model="form.phone_secondary" class="edz-input text-sm">
                        </div>
                        <div>
                            <label class="edz-label">Wilaya</label>
                            <select wire:model="form.state_id" wire:change="loadCities($event.target.value)"
                                class="edz-input text-sm">
                                <option value="">—</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allStates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($st['id']); ?>"><?php echo e($st['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.state_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="edz-label">City</label>
                            <select wire:model="form.city_id" class="edz-input text-sm">
                                <option value="">—</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allCities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($city['id']); ?>"><?php echo e($city['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.city_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger-500 text-xs mt-1"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="edz-label">Address</label>
                            <input type="text" wire:model="form.address" class="edz-input text-sm">
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="edz-label">Delivery</label>
                            <select wire:model="form.delivery_type" class="edz-input text-sm">
                                <option value="home">Home</option>
                                <option value="stopdesk">Stop Desk</option>
                            </select>
                        </div>
                        <div>
                            <label class="edz-label">Shipment</label>
                            <select wire:model="form.shipment_type" class="edz-input text-sm">
                                <option value="delivery">Delivery</option>
                                <option value="exchange">Exchange</option>
                                <option value="pickup">Pickup</option>
                            </select>
                        </div>
                        <div>
                            <label class="edz-label">Payment</label>
                            <select wire:model="form.payment_method" class="edz-input text-sm">
                                <option value="cod">COD</option>
                            </select>
                        </div>
                        <div>
                            <label class="edz-label">Weight (kg)</label>
                            <input type="number" wire:model="form.weight_kg" step="0.01"
                                class="edz-input text-sm">
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{
                        get desks() {
                            const all = <?php echo e(\Illuminate\Support\Js::from($editDesks)); ?>;
                            const pid = $wire.form.shipping_provider_id || '';
                            const sid = $wire.form.state_id || '';
                            const sel = $wire.form.stopdesk_point_id || '';
                            return all
                                .filter(d =>
                                    d.id === sel ||
                                    (!pid || d.shipping_provider_id === pid) &&
                                    (!sid || d.state_id === sid))
                                .sort((a, b) =>
                                    (b.city_id === ($wire.form.city_id || '')) -
                                    (a.city_id === ($wire.form.city_id || '')));
                        }
                    }">
                        <div>
                            <label class="edz-label">Shipping Company</label>
                            <select wire:model="form.shipping_provider_id" class="edz-input text-sm">
                                <option value="">—</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $editProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($provider['id']); ?>"><?php echo e($provider['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <span class="text-xs text-gray-400 mt-1 block">Assigned at confirmation</span>
                        </div>
                        <div>
                            <label class="edz-label">Pickup Desk</label>
                            <select wire:model="form.stopdesk_point_id" class="edz-input text-sm">
                                <option value="">—</option>
                                <template x-for="desk in desks" :key="desk.id">
                                    <option :value="desk.id" x-text="desk.name + ' - ' + (desk.address || '')">
                                    </option>
                                </template>
                            </select>
                            <span class="text-xs text-gray-400 mt-1 block">Stopdesk orders only</span>
                        </div>
                    </div>

                    
                    <div>
                        <label class="edz-label">Products</label>
                        <input type="text" wire:model.live.debounce.300ms="formProductSearch"
                            placeholder="Search products..." class="edz-input text-sm mb-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($formProductResults)): ?>
                            <div class="border border-surface-border rounded-lg max-h-40 overflow-y-auto mb-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $formProductResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button wire:click="addFormItem('<?php echo e($pv['id']); ?>')"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-surface-secondary dark:hover:bg-ink-700 flex justify-between">
                                        <span><?php echo e($pv['product']['name'] ?? ''); ?> — <?php echo e($pv['name']); ?></span>
                                        <span class="text-ink-muted"><?php echo e(currency($pv['price'] ?? 0)); ?></span>
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($form['items'])): ?>
                            <div class="space-y-2 mt-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $form['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div
                                        class="flex items-center gap-3 p-2 bg-surface-secondary dark:bg-ink-800 rounded-lg text-sm">
                                        <span class="flex-1 truncate text-ink"><?php echo e($item['name']); ?></span>
                                        <input type="number" value="<?php echo e($item['quantity']); ?>"
                                            wire:change="updateFormItemQty(<?php echo e($idx); ?>, parseInt($event.target.value))"
                                            min="1" class="edz-input text-xs w-16 text-center">
                                        <span
                                            class="text-ink-muted w-20 text-right"><?php echo e(currency($item['price'] * $item['quantity'])); ?></span>
                                        <button wire:click="removeFormItem(<?php echo e($idx); ?>)"
                                            class="text-danger-400 hover:text-danger-600">
                                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-4 h-4']); ?>
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
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="text-right font-bold text-ink pt-2 border-t border-surface-border">
                                    Total:
                                    <?php echo e(currency(collect($form['items'])->sum(fn($i) => $i['price'] * $i['quantity']))); ?>

                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div>
                        <label class="edz-label">Notes</label>
                        <textarea wire:model="form.notes" rows="2" class="edz-input text-sm"></textarea>
                    </div>
                </div>
            </form>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $attributes = $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $component = $__componentOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showReassignModal): ?>
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'wire:key' => 'order-reassign-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isOpen' => true,'wire:key' => 'order-reassign-modal']); ?>
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-bold text-ink"><?php echo e(__('Reassign Order')); ?></h3>
                <div>
                    <label class="edz-label"><?php echo e(__('Assign to')); ?> *</label>
                    <select wire:model="reassignMembershipId" class="edz-input text-sm">
                        <option value="">— <?php echo e(__('Select Agent')); ?> —</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m['id']); ?>"><?php echo e($m['user']['name'] ?? $m['id']); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                        wire:click="set('showReassignModal', false)"><?php echo e(__('Cancel')); ?></button>
                    <button wire:click="submitReassign"
                        class="edz-btn edz-btn--primary edz-btn--sm"><?php echo e(__('Reassign')); ?></button>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $attributes = $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__attributesOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef)): ?>
<?php $component = $__componentOriginal911d914fd97d5405d92c9a7521bf08ef; ?>
<?php unset($__componentOriginal911d914fd97d5405d92c9a7521bf08ef); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/orders/index.blade.php ENDPATH**/ ?>