<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreTeamService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title"><?php echo e(__('teams.title')); ?></h1>
            <p class="edz-page-head__subtitle"><?php echo e(__('teams.subtitle', ['store' => currentStore()?->name])); ?></p>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canCreate()): ?>
            <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="openCreate">
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
<?php endif; ?> <?php echo e(__('teams.add_member')); ?>

            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($creating || $editingId): ?>
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title"><?php echo e($editingId ? __('teams.update_member') : __('teams.add_member')); ?></h2>
                    <p class="text-sm text-ink-400"><?php echo e($editingId ? __('teams.update_member') : __('teams.invite_member')); ?></p>
                </div>
            </div>

            <form wire:submit="<?php echo e($editingId ? 'saveEdit' : 'saveNew'); ?>" class="space-y-4 p-4" x-data="edzDirty()">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-name"><?php echo e(__('teams.name')); ?></label>
                        <input id="tm-name" type="text" class="edz-input <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="name" placeholder="<?php echo e(__('teams.name')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-danger-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-email"><?php echo e(__('teams.email')); ?></label>
                        <input id="tm-email" type="email" class="edz-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="email" placeholder="member@example.com">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-danger-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-password"><?php echo e(__('table.password')); ?><?php echo e($editingId ? ' ('.__('teams.password_hint').')' : ''); ?></label>
                        <input id="tm-password" type="password" class="edz-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> edz-input--error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="password" placeholder="<?php echo e($editingId ? '••••••••' : __('teams.min_8_chars')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-danger-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-role"><?php echo e(__('teams.role')); ?></label>
                        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model.live' => 'store_role','options' => collect(StoreRoleEnum::cases())->reject(fn ($r) => $r === StoreRoleEnum::OWNER)->map(fn ($r) => ['value' => $r->value, 'label' => $r->label()])->values()->all(),'placeholder' => ''.e(__('teams.all_roles')).'','error' => $errors->first('store_role')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'store_role','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect(StoreRoleEnum::cases())->reject(fn ($r) => $r === StoreRoleEnum::OWNER)->map(fn ($r) => ['value' => $r->value, 'label' => $r->label()])->values()->all()),'placeholder' => ''.e(__('teams.all_roles')).'','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('store_role'))]); ?>
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
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-country"><?php echo e(__('teams.country')); ?></label>
                        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model.live' => 'country_id','options' => countries(),'placeholder' => ''.e(__('teams.select_country')).'','error' => $errors->first('country_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'country_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(countries()),'placeholder' => ''.e(__('teams.select_country')).'','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('country_id'))]); ?>
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
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-state"><?php echo e(__('teams.state')); ?></label>
                        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model.live' => 'state_id','options' => $this->states,'placeholder' => ''.e(__('teams.select_state')).'','disabled' => empty($this->country_id),'error' => $errors->first('state_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'state_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->states),'placeholder' => ''.e(__('teams.select_state')).'','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(empty($this->country_id)),'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('state_id'))]); ?>
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
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-city"><?php echo e(__('teams.city')); ?></label>
                        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'city_id','options' => $this->cities,'placeholder' => ''.e(__('teams.select_city')).'','disabled' => empty($this->state_id),'error' => $errors->first('city_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'city_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->cities),'placeholder' => ''.e(__('teams.select_city')).'','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(empty($this->state_id)),'error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('city_id'))]); ?>
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
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model="isActive" class="h-4 w-4 rounded border-surface-border">
                        <?php echo e(__('general.active')); ?>

                    </label>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->store_role && $this->allPermissions->isNotEmpty()): ?>
                    <div class="border-t border-surface-border pt-4">
                        <div class="mb-3 flex items-center gap-2">
                            <span class="text-sm font-medium text-ink"><?php echo e(__('titles.permissions')); ?></span>
                            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                    x-on:click="$wire.set('permissions', <?php echo e(json_encode(\App\Support\StoreRoles::permissions(StoreRoleEnum::from($this->store_role)))); ?>)">
                                <?php echo e(__('buttons.select_all')); ?>

                            </button>
                            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                    x-on:click="$wire.set('permissions', [])">
                                <?php echo e(__('buttons.unselect_all')); ?>

                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-ink-400"><?php echo e(ucfirst($group)); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label class="flex items-center gap-2 py-0.5 text-sm text-ink">
                                            <input type="checkbox" wire:model="permissions" value="<?php echo e($perm); ?>" class="h-3.5 w-3.5 rounded border-surface-border">
                                            <?php echo e(__("permissions.{$perm}")); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! in_array($perm, $this->roleTemplatePermissions, true)): ?>
                                                <span class="edz-badge edz-badge--neutral !text-[10px]"><?php echo e(__('teams.custom_badge')); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="flex items-center gap-2">
                    <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm" wire:loading.attr="disabled" wire:loading.class="opacity-50">
                        <span wire:loading.remove wire:target="saveNew,saveEdit"><?php echo e(__('buttons.save')); ?></span>
                        <span wire:loading wire:target="saveNew,saveEdit"><?php echo e(__('buttons.processing')); ?></span>
                    </button>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                            wire:click="<?php echo e($editingId ? 'closeEdit' : 'closeCreate'); ?>"><?php echo e(__('buttons.cancel')); ?></button>
                </div>
            </form>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title"><?php echo e(__('teams.list_title')); ?></h2>
                <p class="text-sm text-ink-400"><?php echo e(__('teams.list_subtitle')); ?></p>
            </div>
        </div>

        <div class="border-b border-surface-border p-4">
            <input type="search" class="edz-input" placeholder="<?php echo e(__('teams.search_placeholder')); ?>"
                   wire:model.live.debounce.300ms="search">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('teams.name')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('teams.email')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('teams.role')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('table.address')); ?></th>
                        <th class="px-4 py-3 text-start font-semibold"><?php echo e(__('teams.status')); ?></th>
                        <th class="px-4 py-3 text-end font-semibold"><?php echo e(__('general.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $membership): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $roleName = $this->memberRoleName($membership);
                        ?>
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-medium text-ink"><?php echo e($membership->user?->name); ?></td>
                            <td class="px-4 py-3 text-ink-soft"><?php echo e($membership->user?->email); ?></td>
                            <td class="px-4 py-3">
                                <?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'role','status' => $roleName]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'role','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($roleName)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-muted">
                                <?php echo e($membership->user?->city?->name); ?>, <?php echo e($membership->user?->state?->name); ?>

                            </td>
                            <td class="px-4 py-3">
                                <?php if (isset($component)) { $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.merchant.status','data' => ['domain' => 'general','status' => $membership->is_active ? 'active' : 'inactive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('merchant.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'general','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($membership->is_active ? 'active' : 'inactive')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $attributes = $__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__attributesOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59)): ?>
<?php $component = $__componentOriginal8f9aa4aa53abf3f09654f8239836dc59; ?>
<?php unset($__componentOriginal8f9aa4aa53abf3f09654f8239836dc59); ?>
<?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canModify($membership)): ?>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="openEdit('<?php echo e($membership->id); ?>')"><?php echo e(__('buttons.edit')); ?></button>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="toggleActive('<?php echo e($membership->id); ?>')">
                                            <?php echo e($membership->is_active ? __('buttons.deactivate') : __('buttons.activate')); ?>

                                        </button>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                x-data
                                                data-confirm-title="<?php echo e(__('teams.remove_member')); ?>"
                                                data-confirm-text="<?php echo e(__('messages.action_confirm_delete')); ?>"
                                                data-delete-id="<?php echo e($membership->id); ?>"
                                                @click.prevent="(async () => { if (await EdzSwal.confirmAction($el.dataset.confirmTitle, $el.dataset.confirmText)) await $wire.remove(Number($el.dataset.deleteId)) })()"
                                                ><?php echo e(__('buttons.remove')); ?></button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft"><?php echo e(__('teams.no_members')); ?></p>
                                <p class="mt-1 text-sm text-ink-muted"><?php echo e(__('teams.try_adjusting')); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->members->hasPages()): ?>
            <div class="border-t border-surface-border px-4 py-3">
                <?php echo e($this->members->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\merchant\teams\index.blade.php ENDPATH**/ ?>