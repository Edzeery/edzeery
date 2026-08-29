<?php

use App\Enums\Finance\DebtStatusEnum;
use App\Enums\Finance\DebtTypeEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Finance\Debt;
use Illuminate\Support\Facades\Validator;



use Carbon\Carbon;

?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">
                <?php echo e($debtId ? __('finance.edit_debt') : __('finance.add_debt')); ?>

            </h1>
        </div>
        <a href="<?php echo e(route('merchant.debts.index', currentStore())); ?>"
           wire:navigate class="edz-btn edz-btn--ghost edz-btn--sm">
            <?php echo e(__('finance.back')); ?>

        </a>
    </div>

    <div class="edz-card">
        <div class="edz-card__body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="mb-6 rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700 dark:border-danger-800 dark:bg-danger-950 dark:text-danger-300">
                    <p class="font-semibold"><?php echo e(__('messages.validation_error')); ?></p>
                    <ul class="mt-1 list-inside list-disc">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form wire:submit="save" x-data="edzDirty()">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.type')); ?></label>
                        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'type','options' => collect(DebtTypeEnum::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()])->values()->all()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'type','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect(DebtTypeEnum::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()])->values()->all())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $attributes = $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $component = $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.status')); ?></label>
                        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'status','options' => collect(DebtStatusEnum::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])->values()->all()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'status','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect(DebtStatusEnum::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])->values()->all())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $attributes = $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf)): ?>
<?php $component = $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf; ?>
<?php unset($__componentOriginal98f6a35728186ef8bbcf8d819e3363cf); ?>
<?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.counterparty')); ?></label>
                        <input type="text" wire:model="counterparty_name"
                               class="edz-input" placeholder="<?php echo e(__('finance.counterparty_placeholder')); ?>" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.total_amount')); ?></label>
                        <input type="number" step="0.01" wire:model="total_amount"
                               class="edz-input <?php $__errorArgs = ['total_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['total_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="edz-field__error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.due_date')); ?></label>
                        <input type="date" wire:model="due_date" class="edz-input" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.reminder_date')); ?></label>
                        <input type="date" wire:model="reminder_date" class="edz-input" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.description')); ?></label>
                        <textarea wire:model="description" class="edz-input" rows="2"></textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink mb-1"><?php echo e(__('finance.notes')); ?></label>
                        <textarea wire:model="notes" class="edz-input" rows="3"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="edz-btn edz-btn--primary">
                        <?php echo e($debtId ? __('finance.update') : __('finance.create')); ?>

                    </button>
                    <a href="<?php echo e(route('merchant.debts.index', currentStore())); ?>"
                       wire:navigate class="edz-btn edz-btn--ghost">
                        <?php echo e(__('finance.back')); ?>

                    </a>
                </div>
            </form>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\debts\form.blade.php ENDPATH**/ ?>