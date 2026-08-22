<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Domains\Order\Models\ConfirmationShift;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Products\Product;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Facades\Validator;

?>

<div x-data="{ shiftTypeChanging: false }">
    <?php
        $SHIFT_TYPES = [
            'morning'   => __('merchant_panel.shift_morning'),
            'afternoon' => __('merchant_panel.shift_afternoon'),
            'evening'   => __('merchant_panel.shift_evening'),
            'full_day'  => __('merchant_panel.shift_full_day'),
            'custom'    => __('merchant_panel.shift_custom'),
        ];

        $DAYS_OF_WEEK = [
            1 => __('merchant_panel.monday'),
            2 => __('merchant_panel.tuesday'),
            3 => __('merchant_panel.wednesday'),
            4 => __('merchant_panel.thursday'),
            5 => __('merchant_panel.friday'),
            6 => __('merchant_panel.saturday'),
            7 => __('merchant_panel.sunday'),
        ];
    ?>

    
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <?php if (isset($component)) { $__componentOriginal64446345db7363332d7ff2707d878bc4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal64446345db7363332d7ff2707d878bc4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.page-header','data' => ['title' => ''.e(__('merchant_panel.order_settings')).'','description' => ''.e(__('merchant_panel.order_settings_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(__('merchant_panel.order_settings')).'','description' => ''.e(__('merchant_panel.order_settings_desc')).'']); ?>
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
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center">
                    <ion-icon name="time-outline" class="text-lg text-primary-500"></ion-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink"><?php echo e(count($shifts)); ?></p>
                    <p class="text-xs text-ink-muted"><?php echo e(__('merchant_panel.total_shifts')); ?></p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-success-50 dark:bg-success-900/20 flex items-center justify-center">
                    <ion-icon name="checkmark-circle-outline" class="text-lg text-success-500"></ion-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink"><?php echo e(collect($shifts)->where('is_active', true)->count()); ?></p>
                    <p class="text-xs text-ink-muted"><?php echo e(__('merchant_panel.active_shifts')); ?></p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-warning-50 dark:bg-warning-900/20 flex items-center justify-center">
                    <ion-icon name="cube-outline" class="text-lg text-warning-500"></ion-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink"><?php echo e(count($assignments)); ?></p>
                    <p class="text-xs text-ink-muted"><?php echo e(__('merchant_panel.product_rules')); ?></p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-info-50 dark:bg-info-900/20 flex items-center justify-center">
                    <ion-icon name="people-outline" class="text-lg text-info-500"></ion-icon>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink"><?php echo e(collect($shifts)->pluck('membership_id')->unique()->count()); ?></p>
                    <p class="text-xs text-ink-muted"><?php echo e(__('merchant_panel.agents_with_shifts')); ?></p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="flex gap-1 mb-6 border-b border-surface-200 dark:border-ink-700">
        <button wire:click="setTab('shifts')"
                class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px <?php echo e($tab === 'shifts' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-ink-muted hover:text-ink'); ?>">
            <ion-icon name="time-outline" class="inline mr-1"></ion-icon>
            <?php echo e(__('merchant_panel.tab_shifts')); ?>

        </button>
        <button wire:click="setTab('products')"
                class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px <?php echo e($tab === 'products' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-ink-muted hover:text-ink'); ?>">
            <ion-icon name="cube-outline" class="inline mr-1"></ion-icon>
            <?php echo e(__('merchant_panel.tab_product_assignments')); ?>

        </button>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'shifts'): ?>
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-ink-muted"><?php echo e(__('merchant_panel.tab_shifts_desc')); ?></p>
            <button wire:click="openShiftModal" class="edz-btn edz-btn--primary edz-btn--sm">
                <ion-icon name="add-outline" class="text-base"></ion-icon>
                <?php echo e(__('merchant_panel.new_shift')); ?>

            </button>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($shifts)): ?>
            <div class="edz-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-secondary">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase"><?php echo e(__('merchant_panel.agent')); ?></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase"><?php echo e(__('merchant_panel.type')); ?></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase"><?php echo e(__('merchant_panel.hours')); ?></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase"><?php echo e(__('merchant_panel.days')); ?></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-muted uppercase"><?php echo e(__('merchant_panel.status')); ?></th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-muted uppercase"><?php echo e(__('merchant_panel.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-100 dark:divide-ink-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-surface-50 dark:hover:bg-ink-800/50">
                                    <td class="px-4 py-3 font-medium text-ink">
                                        <?php echo e($shift['membership']['user']['name'] ?? '—'); ?>

                                    </td>
                                    <td class="px-4 py-3 text-ink-muted capitalize">
                                        <?php echo e($SHIFT_TYPES[$shift['shift_type']] ?? $shift['shift_type']); ?>

                                    </td>
                                    <td class="px-4 py-3 text-ink-muted text-xs font-mono">
                                        <?php echo e($shift['start_time']); ?> — <?php echo e($shift['end_time']); ?>

                                    </td>
                                    <td class="px-4 py-3 text-xs text-ink-muted">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($shift['days_of_week'])): ?>
                                            <?php
                                                $dayLabels = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
                                            ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $shift['days_of_week']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="inline-block px-1.5 py-0.5 rounded bg-surface-100 dark:bg-ink-700 mr-1 mb-0.5"><?php echo e($dayLabels[$day] ?? $day); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-ink-muted"><?php echo e(__('merchant_panel.all_days')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <button wire:click="toggleShiftActive('<?php echo e($shift['id']); ?>')"
                                                class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full <?php echo e($shift['is_active'] ? 'bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-300' : 'bg-surface-100 text-ink-muted dark:bg-ink-700'); ?>">
                                            <?php echo e($shift['is_active'] ? __('merchant_panel.active') : __('merchant_panel.inactive')); ?>

                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="openShiftModal('<?php echo e($shift['id']); ?>')" class="edz-btn edz-btn--ghost edz-btn--xs">
                                                <ion-icon name="create-outline" class="text-sm"></ion-icon>
                                            </button>
                                            <button class="edz-btn edz-btn--ghost edz-btn--xs text-red-500"
                                                    x-data
                                                    x-on:click="if (await EdzSwal.confirmAction(<?php echo \Illuminate\Support\Js::from(__('merchant_panel.delete_shift'))->toHtml() ?>, <?php echo \Illuminate\Support\Js::from(__('merchant_panel.confirm_delete_shift'))->toHtml() ?>)) $wire.deleteShift('<?php echo e($shift['id']); ?>')">
                                                <ion-icon name="trash-outline" class="text-sm"></ion-icon>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="edz-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-100 dark:bg-ink-700 flex items-center justify-center mx-auto mb-4">
                    <ion-icon name="time-outline" class="text-3xl text-ink-muted opacity-40"></ion-icon>
                </div>
                <p class="text-ink-muted mb-4"><?php echo e(__('merchant_panel.no_shifts_yet')); ?></p>
                <button wire:click="openShiftModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <ion-icon name="add-outline" class="text-base"></ion-icon>
                    <?php echo e(__('merchant_panel.new_shift')); ?>

                </button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'products'): ?>
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-ink-muted"><?php echo e(__('merchant_panel.tab_product_assignments_desc')); ?></p>
            <button wire:click="openAssignModal" class="edz-btn edz-btn--primary edz-btn--sm">
                <ion-icon name="add-outline" class="text-base"></ion-icon>
                <?php echo e(__('merchant_panel.assign_products')); ?>

            </button>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($assignments)): ?>
            <?php
                $grouped = collect($assignments)->groupBy(fn($a) => $a['membership_id']);
            ?>
            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $memberId => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $agentName = $items->first()['membership']['user']['name'] ?? '—';
                    ?>
                    <div class="edz-card overflow-hidden">
                        <div class="px-4 py-3 bg-secondary flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <ion-icon name="person-outline" class="text-sm text-primary-600 dark:text-primary-400"></ion-icon>
                                </div>
                                <span class="font-semibold text-sm text-ink"><?php echo e($agentName); ?></span>
                                <span class="text-xs text-ink-muted bg-surface-100 dark:bg-ink-700 px-2 py-0.5 rounded-full"><?php echo e($items->count()); ?> <?php echo e(__('merchant_panel.products')); ?></span>
                            </div>
                            <button wire:click="openAssignModal('<?php echo e($memberId); ?>')" class="edz-btn edz-btn--ghost edz-btn--xs">
                                <ion-icon name="create-outline" class="text-sm"></ion-icon>
                                <?php echo e(__('merchant_panel.edit')); ?>

                            </button>
                        </div>
                        <div class="divide-y divide-surface-100 dark:divide-ink-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="px-4 py-3 flex items-center justify-between hover:bg-surface-50 dark:hover:bg-ink-800/50">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-surface-100 dark:bg-ink-700 flex items-center justify-center">
                                            <ion-icon name="cube-outline" class="text-sm text-ink-muted"></ion-icon>
                                        </div>
                                        <span class="text-sm text-ink"><?php echo e($a['product']['name'] ?? '—'); ?></span>
                                    </div>
                                    <button class="edz-btn edz-btn--ghost edz-btn--xs text-red-500"
                                            x-data
                                            x-on:click="if (await EdzSwal.confirmAction(<?php echo \Illuminate\Support\Js::from(__('merchant_panel.remove_assignment'))->toHtml() ?>, <?php echo \Illuminate\Support\Js::from(__('merchant_panel.confirm_delete_assignment'))->toHtml() ?>)) $wire.removeAssignment('<?php echo e($a['id']); ?>')">
                                        <ion-icon name="trash-outline" class="text-sm"></ion-icon>
                                    </button>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="edz-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-100 dark:bg-ink-700 flex items-center justify-center mx-auto mb-4">
                    <ion-icon name="cube-outline" class="text-3xl text-ink-muted opacity-40"></ion-icon>
                </div>
                <p class="text-ink-muted mb-4"><?php echo e(__('merchant_panel.no_assignments_yet')); ?></p>
                <button wire:click="openAssignModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <ion-icon name="add-outline" class="text-base"></ion-icon>
                    <?php echo e(__('merchant_panel.assign_products')); ?>

                </button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showShiftModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data
             x-on:keydown.escape.window="$wire.set('showShiftModal', false)">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" x-on:click="$wire.set('showShiftModal', false)"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10">
                    <h3 class="text-lg font-bold text-ink"><?php echo e($editingShiftId ? __('merchant_panel.edit_shift') : __('merchant_panel.new_shift')); ?></h3>
                    <div class="flex items-center gap-2">
                        <button wire:click="saveShift" class="edz-btn edz-btn--primary edz-btn--sm"><?php echo e(__('merchant_panel.save')); ?></button>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                x-on:click="$wire.set('showShiftModal', false)">
                            <ion-icon name="close-outline" class="text-lg"></ion-icon>
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="edz-label"><?php echo e(__('merchant_panel.agent')); ?> *</label>
                        <select wire:model="shiftForm.membership_id" class="edz-input text-sm">
                            <option value="">— <?php echo e(__('merchant_panel.select_agent')); ?> —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($m['id']); ?>"><?php echo e($m['user']['name'] ?? $m['id']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="edz-label"><?php echo e(__('merchant_panel.shift_type')); ?></label>
                        <select wire:model="shiftForm.shift_type"
                                x-on:change="$wire.call('onShiftTypeChange')"
                                class="edz-input text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $SHIFT_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($k); ?>"><?php echo e($v); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="edz-label"><?php echo e(__('merchant_panel.start_time')); ?></label>
                            <input type="time" wire:model="shiftForm.start_time" class="edz-input text-sm">
                        </div>
                        <div>
                            <label class="edz-label"><?php echo e(__('merchant_panel.end_time')); ?></label>
                            <input type="time" wire:model="shiftForm.end_time" class="edz-input text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['shiftForm.end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label class="edz-label"><?php echo e(__('merchant_panel.days_of_week')); ?></label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayNum => $dayLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" wire:click="toggleShiftDay(<?php echo e($dayNum); ?>)"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors <?php echo e(in_array($dayNum, $shiftForm['days_of_week'] ?? []) ? 'bg-primary-500 text-white border-primary-500' : 'bg-white dark:bg-gray-700 text-ink-muted border-gray-200 dark:border-gray-600 hover:border-primary-400'); ?>">
                                    <?php echo e($dayLabel); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <input type="checkbox" wire:model="shiftForm.is_active" id="shift_active"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <label for="shift_active" class="text-sm text-ink"><?php echo e(__('merchant_panel.active')); ?></label>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAssignModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data
             x-on:keydown.escape.window="$wire.set('showAssignModal', false)">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" x-on:click="$wire.set('showAssignModal', false)"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10">
                    <h3 class="text-lg font-bold text-ink"><?php echo e(__('merchant_panel.assign_products')); ?></h3>
                    <div class="flex items-center gap-2">
                        <button wire:click="saveAssignments" class="edz-btn edz-btn--primary edz-btn--sm"><?php echo e(__('merchant_panel.save')); ?></button>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                x-on:click="$wire.set('showAssignModal', false)">
                            <ion-icon name="close-outline" class="text-lg"></ion-icon>
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="edz-label"><?php echo e(__('merchant_panel.agent')); ?> *</label>
                        <select wire:model="assignForm.membership_id" class="edz-input text-sm">
                            <option value="">— <?php echo e(__('merchant_panel.select_agent')); ?> —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($m['id']); ?>"><?php echo e($m['user']['name'] ?? $m['id']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="edz-label"><?php echo e(__('merchant_panel.search_products')); ?></label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="productSearch"
                                   wire:keyup.debounce.500ms="searchAssignProducts"
                                   placeholder="<?php echo e(__('merchant_panel.type_product_name')); ?>"
                                   class="edz-input text-sm ps-9">
                            <ion-icon name="search-outline" class="absolute start-3 top-1/2 -translate-y-1/2 text-ink-muted pointer-events-none"></ion-icon>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($productResults)): ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg max-h-40 overflow-y-auto mt-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $productResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                        <input type="checkbox"
                                               <?php echo e(in_array($p['id'], $assignForm['product_ids'] ?? []) ? 'checked' : ''); ?>

                                               wire:click="toggleAssignProduct('<?php echo e($p['id']); ?>')"
                                               class="rounded border-gray-300 text-primary-600">
                                        <span class="flex-1"><?php echo e($p['name']); ?></span>
                                        <span class="text-xs text-ink-muted"><?php echo e(currency($p['price'] ?? 0)); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($assignForm['product_ids'])): ?>
                        <div>
                            <label class="edz-label"><?php echo e(__('merchant_panel.selected_count')); ?> (<?php echo e(count($assignForm['product_ids'])); ?>)</label>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $assignForm['product_ids']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $pname = collect($allProducts)->firstWhere('id', $pid)['name'] ?? $pid ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                                        <?php echo e($pname); ?>

                                        <button wire:click="toggleAssignProduct('<?php echo e($pid); ?>')" class="hover:text-primary-900 dark:hover:text-white">
                                            <ion-icon name="close-outline" class="text-sm"></ion-icon>
                                        </button>
                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/merchant/order-settings.blade.php ENDPATH**/ ?>