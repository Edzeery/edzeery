<?php

use App\Enums\Finance\DebtStatusEnum;
use App\Enums\Finance\DebtTypeEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Finance\Debt;
use App\Models\Finance\DebtPayment;



use Carbon\Carbon;

?>

<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($debt): ?>
        <div class="edz-page-head">
            <div>
                <h1 class="edz-page-head__title"><?php echo e($debt->counterparty_name ?? __('finance.debt')); ?></h1>
                <p class="edz-page-head__subtitle"><?php echo e($debt->description ?? '—'); ?></p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('merchant.debts.edit', [currentStore(), $debt])); ?>"
                   wire:navigate class="edz-btn edz-btn--secondary edz-btn--sm">
                    <?php echo e(__('finance.edit')); ?>

                </a>
                <a href="<?php echo e(route('merchant.debts.index', currentStore())); ?>"
                   wire:navigate class="edz-btn edz-btn--ghost edz-btn--sm">
                    <?php echo e(__('finance.back')); ?>

                </a>
            </div>
        </div>

        
        <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="edz-card">
                <div class="edz-card__body">
                    <p class="text-sm text-ink-muted"><?php echo e(__('finance.total_amount')); ?></p>
                    <p class="text-xl font-bold text-ink"><?php echo e($this->formatAmount($debt->total_amount)); ?></p>
                </div>
            </div>
            <div class="edz-card">
                <div class="edz-card__body">
                    <p class="text-sm text-ink-muted"><?php echo e(__('finance.paid_amount')); ?></p>
                    <p class="text-xl font-bold text-success-600"><?php echo e($this->formatAmount($debt->paid_amount)); ?></p>
                </div>
            </div>
            <div class="edz-card">
                <div class="edz-card__body">
                    <p class="text-sm text-ink-muted"><?php echo e(__('finance.remaining')); ?></p>
                    <p class="text-xl font-bold text-danger-600"><?php echo e($this->formatAmount($debt->remaining_amount)); ?></p>
                </div>
            </div>
            <div class="edz-card">
                <div class="edz-card__body">
                    <p class="text-sm text-ink-muted"><?php echo e(__('finance.status')); ?></p>
                    <div class="mt-1"><?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'debt','status' => $debt->status->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'debt','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($debt->status->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?></div>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
            <div class="edz-card">
                <div class="edz-card__header">
                    <h3 class="edz-card__title"><?php echo e(__('finance.details')); ?></h3>
                </div>
                <div class="edz-card__body">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-ink-muted"><?php echo e(__('finance.type')); ?></dt>
                            <dd><?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'debt_type','status' => $debt->type->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'debt_type','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($debt->type->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-ink-muted"><?php echo e(__('finance.counterparty')); ?></dt>
                            <dd class="text-sm font-medium text-ink"><?php echo e($debt->counterparty_name ?? '—'); ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-ink-muted"><?php echo e(__('finance.due_date')); ?></dt>
                            <dd class="text-sm text-ink"><?php echo e($debt->due_date?->format('Y-m-d') ?? '—'); ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-ink-muted"><?php echo e(__('finance.reminder_date')); ?></dt>
                            <dd class="text-sm text-ink"><?php echo e($debt->reminder_date?->format('Y-m-d') ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-ink-muted mb-1"><?php echo e(__('finance.progress')); ?></dt>
                            <div class="w-full bg-surface-secondary rounded-full h-2">
                                <div class="bg-success-500 h-2 rounded-full" style="width: <?php echo e($debt->progress); ?>%"></div>
                            </div>
                            <span class="text-xs text-ink-muted"><?php echo e($debt->progress); ?>%</span>
                        </div>
                    </dl>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::FINANCE_DEBT_UPDATE->value)): ?>
                <div class="edz-card">
                    <div class="edz-card__header">
                        <h3 class="edz-card__title"><?php echo e(__('finance.add_payment')); ?></h3>
                    </div>
                    <div class="edz-card__body">
                        <form wire:submit="addPayment" class="space-y-4" x-data="edzDirty()">
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.amount')); ?></label>
                                <input type="number" step="0.01" wire:model="payment_amount"
                                       class="edz-input" required />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['payment_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-danger-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.payment_date')); ?></label>
                                <input type="date" wire:model="payment_date"
                                       class="edz-input" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.notes')); ?></label>
                                <textarea wire:model="payment_notes" class="edz-input" rows="2"></textarea>
                            </div>
                            <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm">
                                <?php echo e(__('finance.add_payment')); ?>

                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="edz-card">
            <div class="edz-card__header">
                <h3 class="edz-card__title"><?php echo e(__('finance.payments_history')); ?></h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                            <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('finance.date')); ?></th>
                            <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('finance.amount')); ?></th>
                            <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('finance.notes')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $debt->payments()->latest('payment_date')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b border-surface-border last:border-0">
                                <td class="px-4 py-3 text-ink-soft"><?php echo e($payment->payment_date->format('Y-m-d')); ?></td>
                                <td class="px-4 py-3 font-medium text-success-600"><?php echo e($this->formatAmount($payment->amount)); ?></td>
                                <td class="px-4 py-3 text-ink-soft"><?php echo e($payment->notes ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="3" class="px-4 py-12 text-center">
                                    <p class="text-sm text-ink-soft"><?php echo e(__('finance.no_payments_yet')); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\debts\show.blade.php ENDPATH**/ ?>