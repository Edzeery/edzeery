<?php

use App\Domains\Account\Actions\Profile\GetProfileAction;
use App\Domains\Account\DTOs\ProfileData;
use App\Domains\Account\Services\AccountService;
use App\Http\Requests\Account\Profile\UpdateProfileRequest;

?>

<div>
    <?php if (isset($component)) { $__componentOriginal64446345db7363332d7ff2707d878bc4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal64446345db7363332d7ff2707d878bc4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.page-header','data' => ['title' => ''.e(__('merchant_panel.profile')).'','description' => ''.e(__('merchant_panel.profile_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e(__('merchant_panel.profile')).'','description' => ''.e(__('merchant_panel.profile_desc')).'']); ?>
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

    <form wire:submit="updateProfile" x-data="edzDirty()">
        <div class="edz-card edz-card--padded space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="edz-label"><?php echo e(__('general.name')); ?></label>
                    <input type="text" id="name" wire:model="name"
                           class="edz-input" required />
                </div>

                <div>
                    <label for="email" class="edz-label"><?php echo e(__('general.email')); ?></label>
                    <input type="email" id="email" wire:model="email"
                           class="edz-input" required />
                </div>

                <div>
                    <label for="phone" class="edz-label"><?php echo e(__('general.phone')); ?></label>
                    <input type="text" id="phone" wire:model="phone"
                           class="edz-input" />
                </div>

                <div>
                    <label for="country" class="edz-label"><?php echo e(__('general.country')); ?></label>
                    <select id="country" wire:model="country" class="edz-input">
                        <option value=""><?php echo e(__('general.select_country')); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->code); ?>"><?php echo e($c->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                <div>
                    <label for="birthdate" class="edz-label"><?php echo e(__('general.birthdate')); ?></label>
                    <input type="date" id="birthdate" wire:model="birthdate"
                           class="edz-input" />
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="edz-label"><?php echo e(__('general.address')); ?></label>
                    <input type="text" id="address" wire:model="address"
                           class="edz-input" />
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="edz-btn edz-btn--primary">
                    <?php echo e(__('buttons.save')); ?>

                </button>
            </div>
        </div>
    </form>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire/account/profile.blade.php ENDPATH**/ ?>