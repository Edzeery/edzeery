

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($colKey):
    case ('number'): ?>
        <td class="px-4 py-3 font-mono font-semibold text-ink">
            <span class="inline-flex items-center">#<?php echo e($order['number']); ?></span>
        </td>
        <?php break; ?>

    <?php case ('source'): ?>
        <td class="px-4 py-3 text-xs">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($order['created_by_membership_id'])): ?>
                <?php if (isset($component)) { $__componentOriginal0e22455320c9b930cb121e68fdfb47bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.badge','data' => ['tone' => 'neutral','sm' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tone' => 'neutral','sm' => true]); ?>
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'user','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user','class' => 'w-3 h-3']); ?>
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
                    <?php echo e(__('merchant.delivery_man')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $attributes = $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $component = $__componentOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginal0e22455320c9b930cb121e68fdfb47bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.badge','data' => ['tone' => 'accent','sm' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tone' => 'accent','sm' => true]); ?>
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shopping-bag','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shopping-bag','class' => 'w-3 h-3']); ?>
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
                    <?php echo e(__('merchant_panel.store')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $attributes = $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $component = $__componentOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('customer'): ?>
        <td class="px-4 py-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.customer_name' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="name-inline-<?php echo e($orderId); ?>">
                    <input type="text" wire:model="nameEditName" wire:keydown.enter="saveOrderName"
                        placeholder="<?php echo e(__('merchant_panel.name')); ?>"
                        class="edz-inline-edit__input <?php if($this->editingError): ?> edz-inline-edit__input--error <?php endif; ?>">
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderName"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            wire:click="cancelOrderNameEdit"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    wire:click="startOrderNameEdit('<?php echo e($orderId); ?>')"
                    title="<?php echo e($order['customer']['name'] ?? '-'); ?>">
                    <span
                        class="edz-inline-edit__value"><?php echo e(\Illuminate\Support\Str::limit($order['customer']['name'] ?? '-', 30)); ?></span>
                </button>
            <?php else: ?>
                <div class="text-ink font-medium text-xs max-w-[120px] truncate"
                    title="<?php echo e($order['customer']['name'] ?? '-'); ?>">
                    <?php echo e($order['customer']['name'] ?? '-'); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php
                $dupTone = match ($order['dup_level'] ?? null) {
                    'duplicate' => 'danger',
                    'probable' => 'warning',
                    'repeat' => 'neutral',
                    default => null,
                };
                $dupLabel = match ($order['dup_level'] ?? null) {
                    'duplicate' => __('order_flow.dup_badge_duplicate'),
                    'probable' => __('order_flow.dup_badge_probable'),
                    'repeat' => __('order_flow.dup_badge_repeat'),
                    default => null,
                };
                $dupCount = (int) ($order['duplicate_count'] ?? $order['repeat_count'] ?? 0);
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$this->showTrash && ($order['status_key'] ?? null) !== 'duplicate' && $dupTone): ?>
                <div class="flex items-center gap-1.5 min-w-0 mt-1">
                    <button type="button" wire:click="openDuplicateScan('<?php echo e($orderId); ?>')"
                        title="<?php echo e(__('order_flow.duplicate_warnings_title')); ?>"
                        class="edz-badge edz-badge--<?php echo e($dupTone); ?> edz-badge--sm shrink-0 cursor-pointer transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-warning/40">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'copy','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'copy','class' => 'w-3 h-3']); ?>
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
                        <?php echo e($dupLabel); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($order['dup_level'] ?? null) !== 'repeat'): ?>
                            ×<?php echo e(min($dupCount, 9)); ?><?php echo e($dupCount > 9 ? '+' : ''); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$this->showTrash && !empty($order['missing']) && canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <div class="flex items-center gap-1.5 min-w-0 mt-1">
                    <button type="button" wire:click="startMissingFieldEdit('<?php echo e($orderId); ?>')"
                        title="<?php echo e(__('order_flow.bulk_send_reason_missing', ['fields' => implode('، ', $order['missing'])])); ?>"
                        class="edz-badge edz-badge--info edz-badge--sm shrink-0 cursor-pointer transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-warning/40">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'exclamation-triangle','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'exclamation-triangle','class' => 'w-3 h-3']); ?>
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
                        ×<?php echo e(min(count($order['missing']), 9)); ?><?php echo e(count($order['missing']) > 9 ? '+' : ''); ?>

                    </button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('phone'): ?>
        <td class="px-4 py-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.phone' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="phone-inline-<?php echo e($orderId); ?>">
                    <input type="tel" wire:model="phoneEditPhone" wire:keydown.enter="saveOrderPhone"
                        placeholder="<?php echo e(__('merchant_panel.phone')); ?>"
                        class="edz-inline-edit__input <?php if($this->editingError): ?> edz-inline-edit__input--error <?php endif; ?>">
                    <input type="tel" wire:model="phoneEditSecondary" wire:keydown.enter="saveOrderPhone"
                        placeholder="<?php echo e(__('merchant_panel.phone_secondary')); ?>"
                        class="edz-inline-edit__input <?php if($this->editingError): ?> edz-inline-edit__input--error <?php endif; ?>">
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderPhone"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            wire:click="cancelOrderPhoneEdit"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    wire:click="startOrderPhoneEdit('<?php echo e($orderId); ?>')">
                    <span class="edz-inline-edit__value"
                        dir="ltr"><?php echo e($order['customer']['phone'] ?? '—'); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($order['phone_secondary'])): ?>
                            <span class="text-ink-muted/60"> · <?php echo e($order['phone_secondary']); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </span>
                </button>
            <?php else: ?>
                <span dir="ltr"><?php echo e($order['customer']['phone'] ?? '-'); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($order['phone_secondary'])): ?>
                        · <?php echo e($order['phone_secondary']); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('verification'): ?>
        <td class="px-4 py-3">
            <span class="inline-flex items-center gap-1 text-ink-muted text-xs"
                title="<?php echo e(__('merchant_panel.verification_hint')); ?>">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shield-check','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield-check','class' => 'w-3.5 h-3.5']); ?>
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
                —
            </span>
        </td>
        <?php break; ?>

    <?php case ('notes'): ?>
        <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px]"
            <?php if($this->editingField !== 'order.notes' || $this->editingId !== $orderId): ?>
                title="<?php echo e($order['notes'] ?? ''); ?>"
            <?php endif; ?>>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.notes' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="notes-inline-<?php echo e($orderId); ?>">
                    <textarea wire:model="editingValue" wire:keydown.enter="saveOrderNotes"
                        rows="2" placeholder="<?php echo e(__('merchant_panel.notes')); ?>"
                        class="edz-inline-edit__input <?php if($this->editingError): ?> edz-inline-edit__input--error <?php endif; ?>"></textarea>
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderNotes"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            wire:click="cancelOrderEdit"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display text-left"
                    wire:click="startOrderNotesEdit('<?php echo e($orderId); ?>')"
                    title="<?php echo e($order['notes'] ?? ''); ?>">
                    <span
                        class="edz-inline-edit__value break-words"><?php echo e($order['notes'] ? \Illuminate\Support\Str::limit($order['notes'], 40) : '—'); ?></span>
                </button>
            <?php else: ?>
                <span class="truncate block" title="<?php echo e($order['notes'] ?? ''); ?>">
                    <?php echo e($order['notes'] ? \Illuminate\Support\Str::limit($order['notes'], 30) : '-'); ?>

                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('meta'): ?>
        <?php
            $metaEntries = collect($order['meta'] ?? [])
                ->map(fn($v, $k) => "{$k}: {$v}")
                ->implode(', ');
        ?>
        <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate" title="<?php echo e($metaEntries); ?>">
            <?php echo e($metaEntries ?: '-'); ?>

        </td>
        <?php break; ?>

    <?php case ('wilaya'): ?>
        <td class="px-4 py-3 text-ink-muted text-xs">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.wilaya' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="wilaya-inline-<?php echo e($orderId); ?>">
                    <select wire:change="saveOrderWilaya($event.target.value)"
                        class="edz-inline-edit__input <?php if($this->editingError): ?> edz-inline-edit__input--error <?php endif; ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allStates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($st['id']); ?>"
                                <?php if((string) $this->editingValue === (string) $st['id']): ?> selected <?php endif; ?>>
                                <?php echo e($st['name']); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()">Cancel</button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderWilayaEdit('<?php echo e($orderId); ?>')">
                    <span class="edz-inline-edit__value"><?php echo e($order['state']['name'] ?? '—'); ?></span>
                </button>
            <?php else: ?>
                <?php echo e($order['state']['name'] ?? '-'); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('products'): ?>
        <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate"
            title="<?php echo e(collect($order['items_summary'] ?? [])->map(fn($i) => $i['name'] . ' ×' . $i['qty'])->implode(', ')); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order['items_summary'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo e($item['name']); ?> ×<?php echo e($item['qty']); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>,<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('quantity'): ?>
        <td class="px-4 py-3 text-xs text-ink-muted tabular-nums text-center">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order['items_summary'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($item['qty']); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('price'): ?>
        <td class="px-4 py-3 text-xs text-ink-muted tabular-nums">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order['items_summary'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e(currency($item['price'])); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('total'): ?>
        <td class="px-4 py-3 font-semibold text-ink tabular-nums">
            <?php echo e(currency($order['display_total'] ?? $order['total_amount'] ?? 0)); ?>

        </td>
        <?php break; ?>

    <?php case ('discount'): ?>
        <td class="px-4 py-3 text-ink-muted text-xs tabular-nums">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.discount' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit edz-inline-edit__edit--wide" wire:key="discount-inline-<?php echo e($orderId); ?>">
                    <div class="flex flex-col gap-1.5">
                        <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model.live' => 'discountEditType','options' => [
                            ['value' => '', 'label' => __('merchant_panel.no_discount')],
                            ['value' => 'amount', 'label' => __('merchant_panel.fixed_amount')],
                            ['value' => 'percent', 'label' => __('merchant_panel.percentage')],
                        ],'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'discountEditType','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                            ['value' => '', 'label' => __('merchant_panel.no_discount')],
                            ['value' => 'amount', 'label' => __('merchant_panel.fixed_amount')],
                            ['value' => 'percent', 'label' => __('merchant_panel.percentage')],
                        ]),'size' => 'sm']); ?>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->discountEditType): ?>
                            <input type="number" step="0.01" min="0" wire:model="discountEditValue"
                                placeholder="<?php echo e($this->discountEditType === 'percent' ? '%' : 'DZD'); ?>"
                                class="edz-inline-edit__input <?php if($this->editingError): ?> edz-inline-edit__input--error <?php endif; ?>">
                            <input type="text" maxlength="255" wire:model="discountEditReason"
                                placeholder="<?php echo e(__('merchant_panel.discount_reason')); ?>"
                                class="edz-inline-edit__input">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderDiscount"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            wire:click="cancelOrderEdit"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderDiscountEdit('<?php echo e($orderId); ?>')"
                    title="<?php if(($order['discount_type'] ?? '') === 'percent'): ?><?php echo e($order['discount_value'] ?? ''); ?>%<?php elseif(($order['discount_type'] ?? '') === 'amount'): ?><?php echo e(currency($order['discount_value'] ?? 0)); ?><?php endif; ?>">
                    <span class="edz-inline-edit__value">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) ($order['discount_amount'] ?? 0) > 0): ?>
                            −<?php echo e(currency($order['discount_amount'])); ?>

                        <?php else: ?>
                            —
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </span>
                </button>
            <?php else: ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) ($order['discount_amount'] ?? 0) > 0): ?>
                    −<?php echo e(currency($order['discount_amount'])); ?>

                <?php else: ?>
                    —
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('shipping_cost'): ?>
        <td class="px-4 py-3 text-ink-muted text-xs">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float) ($order['shipping_cost'] ?? 0) <= 0): ?>
                <?php if (isset($component)) { $__componentOriginal0e22455320c9b930cb121e68fdfb47bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.badge','data' => ['tone' => 'neutral','sm' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tone' => 'neutral','sm' => true]); ?>
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'w-3 h-3']); ?>
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
                    <?php echo e(__('merchant_panel.shipping_free')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $attributes = $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $component = $__componentOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
            <?php else: ?>
                <span class="tabular-nums"><?php echo e(currency((float) ($order['shipping_cost'] ?? 0))); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('weight'): ?>
        <td class="px-4 py-3 text-ink-muted text-xs">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.weight' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="weight-inline-<?php echo e($orderId); ?>">
                    <input type="number" step="0.01" min="0" wire:model="editingValue" wire:keydown.enter="saveOrderWeight"
                        placeholder="0.00"
                        class="edz-inline-edit__input <?php if($this->editingError): ?> edz-inline-edit__input--error <?php endif; ?>">
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderWeight"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            wire:click="cancelOrderEdit"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    wire:click="startOrderWeightEdit('<?php echo e($orderId); ?>')">
                    <span class="edz-inline-edit__value"><?php echo e($order['weight_kg'] ? $order['weight_kg'] . ' kg' : '—'); ?></span>
                </button>
            <?php else: ?>
                <?php echo e($order['weight_kg'] ? $order['weight_kg'] . ' kg' : '—'); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('shipment_type'): ?>
        <?php
            $shipmentLabel = collect($this->editShipmentTypeOptions ?? [])
                ->firstWhere('value', $order['shipment_type'] ?? null);
        ?>
        <td class="px-4 py-3 text-ink-muted text-xs">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.shipment_type' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="shipment-type-inline-<?php echo e($orderId); ?>">
                    <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'editingValue','options' => $this->editShipmentTypeOptions,'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'editingValue','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->editShipmentTypeOptions),'size' => 'sm']); ?>
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
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderShipmentType"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderShipmentTypeEdit('<?php echo e($orderId); ?>')">
                    <span class="edz-inline-edit__value"><?php echo e($shipmentLabel['label'] ?? ($order['shipment_type'] ?? '—')); ?></span>
                </button>
            <?php else: ?>
                <?php echo e($shipmentLabel['label'] ?? ($order['shipment_type'] ?? '-')); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('city'): ?>
        <td class="px-4 py-3 text-ink-muted text-xs">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.city' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="city-inline-<?php echo e($orderId); ?>">
                    <select wire:change="saveOrderCity($event.target.value)"
                        class="edz-inline-edit__input <?php if($this->editingError): ?> edz-inline-edit__input--error <?php endif; ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->editCityOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ct['id']); ?>"
                                <?php if((string) $this->editingValue === (string) $ct['id']): ?> selected <?php endif; ?>>
                                <?php echo e($ct['name']); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()">Cancel</button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value) && !empty($order['state_id'])): ?>
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderCityEdit('<?php echo e($orderId); ?>')">
                    <span class="edz-inline-edit__value"><?php echo e($order['city']['name'] ?? '—'); ?></span>
                </button>
            <?php else: ?>
                <?php echo e($order['city']['name'] ?? '-'); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('address'): ?>
        <td class="px-4 py-3 text-xs text-ink-muted max-w-[200px] truncate"
            <?php if($this->editingField !== 'order.address' || $this->editingId !== $orderId): ?>
                title="<?php echo e($order['address'] ?? ''); ?>"
            <?php endif; ?>>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.address' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="address-inline-<?php echo e($orderId); ?>">
                    <input type="text" wire:model="editingValue" wire:keydown.enter="saveOrderAddress"
                        placeholder="<?php echo e(__('merchant_panel.address')); ?>"
                        class="edz-inline-edit__input <?php if($this->editingError): ?> edz-inline-edit__input--error <?php endif; ?>">
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderAddress"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            wire:click="cancelOrderEdit"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    wire:click="startOrderAddressEdit('<?php echo e($orderId); ?>')"
                    title="<?php echo e($order['address'] ?? ''); ?>">
                    <span
                        class="edz-inline-edit__value"><?php echo e($order['address'] ? \Illuminate\Support\Str::limit($order['address'], 40) : '—'); ?></span>
                </button>
            <?php else: ?>
                <?php echo e($order['address'] ? \Illuminate\Support\Str::limit($order['address'], 40) : '-'); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('delivery_type'): ?>
        <td class="px-4 py-3 text-ink-muted text-xs">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.delivery_type' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="delivery-type-inline-<?php echo e($orderId); ?>">
                    <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'editingValue','options' => $this->editDeliveryTypeOptions,'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'editingValue','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->editDeliveryTypeOptions),'size' => 'sm']); ?>
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
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderDeliveryType"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderDeliveryTypeEdit('<?php echo e($orderId); ?>')">
                    <span class="edz-inline-edit__value"><?php echo e($order['delivery_type'] === 'stopdesk' ? __('merchant_panel.stop_desk_label') : ($order['delivery_type'] === 'home' ? __('merchant_panel.home_delivery_label') : $order['delivery_type'] ?? '—')); ?></span>
                </button>
            <?php else: ?>
                <?php echo e($order['delivery_type'] === 'stopdesk' ? __('merchant_panel.stop_desk_label') : ($order['delivery_type'] === 'home' ? __('merchant_panel.home_delivery_label') : $order['delivery_type'] ?? '-')); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('shipping_provider'): ?>
        <td class="px-4 py-3 text-ink-muted text-xs">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.shipping_provider' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="provider-inline-<?php echo e($orderId); ?>">
                    <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'editingValue','options' => $this->editProviderOptions,'size' => 'sm','search' => true,'placeholder' => ''.e(__('merchant_panel.shipping_provider')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'editingValue','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->editProviderOptions),'size' => 'sm','search' => true,'placeholder' => ''.e(__('merchant_panel.shipping_provider')).'']); ?>
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
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderProvider"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderProviderEdit('<?php echo e($orderId); ?>')">
                    <span class="edz-inline-edit__value"><?php echo e($order['shippingProvider']['name'] ?? '—'); ?></span>
                </button>
            <?php else: ?>
                <?php echo e($order['shippingProvider']['name'] ?? '-'); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('stopdesk_point'): ?>
        <td class="px-4 py-3 text-xs text-ink-muted">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.stopdesk_point' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="stopdesk-inline-<?php echo e($orderId); ?>">
                    <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'editingValue','options' => $this->editStopdeskOptions,'size' => 'sm','search' => true,'placeholder' => ''.e(__('merchant_panel.stop_desk_label')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'editingValue','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->editStopdeskOptions),'size' => 'sm','search' => true,'placeholder' => ''.e(__('merchant_panel.stop_desk_label')).'']); ?>
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
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderStopdesk"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderStopdeskEdit('<?php echo e($orderId); ?>')">
                    <span class="edz-inline-edit__value"><?php echo e($order['stopdesk_point']['name'] ?? '—'); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($order['stopdesk_point']['city']['name'])): ?>
                            (<?php echo e($order['stopdesk_point']['city']['name']); ?>)
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></span>
                </button>
            <?php else: ?>
                <?php echo e($order['stopdesk_point']['name'] ?? '-'); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($order['stopdesk_point']['city']['name'])): ?>
                    (<?php echo e($order['stopdesk_point']['city']['name']); ?>)
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('send_from_carrier_warehouse'): ?>
        <td class="px-4 py-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value) && !$this->showTrash): ?>
                <button type="button" wire:click="toggleSendFromWarehouse('<?php echo e($orderId); ?>')"
                    wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading" wire:target="toggleSendFromWarehouse"
                    title="<?php echo e(__('merchant_panel.send_from_carrier_warehouse')); ?>"
                    class="inline-flex items-center cursor-pointer transition hover:opacity-80">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order['send_from_carrier_warehouse'] ?? false): ?>
                        <?php if (isset($component)) { $__componentOriginal0e22455320c9b930cb121e68fdfb47bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.badge','data' => ['tone' => 'success','sm' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tone' => 'success','sm' => true]); ?>
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-3 h-3']); ?>
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
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $attributes = $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $component = $__componentOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
                    <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginal0e22455320c9b930cb121e68fdfb47bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.badge','data' => ['tone' => 'neutral','sm' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tone' => 'neutral','sm' => true]); ?>
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-3 h-3']); ?>
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
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $attributes = $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $component = $__componentOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </button>
            <?php else: ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order['send_from_carrier_warehouse'] ?? false): ?>
                    <?php if (isset($component)) { $__componentOriginal0e22455320c9b930cb121e68fdfb47bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.badge','data' => ['tone' => 'success','sm' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tone' => 'success','sm' => true]); ?>
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-3 h-3']); ?>
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
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $attributes = $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $component = $__componentOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginal0e22455320c9b930cb121e68fdfb47bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.badge','data' => ['tone' => 'neutral','sm' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tone' => 'neutral','sm' => true]); ?>
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'x-mark','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'w-3 h-3']); ?>
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
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $attributes = $__attributesOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__attributesOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd)): ?>
<?php $component = $__componentOriginal0e22455320c9b930cb121e68fdfb47bd; ?>
<?php unset($__componentOriginal0e22455320c9b930cb121e68fdfb47bd); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('status'): ?>
        <td class="px-4 py-3">
            <div class="relative" @click.away="open = false">
                <button @click="openStatusMenu()" x-ref="trigger"
                    class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full cursor-pointer hover:opacity-80 <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('general', $order['status']['color'] ?? 'gray')->color()); ?>">
                    <?php echo \Edzeery\MyStatusKit\Facades\Status::for('order', $order['status']['key'] ?? 'default')->icon(
                        null,
                        'w-3 h-3 shrink-0',
                    ); ?>

                    <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('order', $order['status']['key'] ?? 'default')->label()); ?>

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
                <div x-show="open" x-cloak class="fixed inset-0 z-[205] bg-black/40 backdrop-blur-sm sm:hidden"
                    @click="open = false"></div>
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-3" :style="menuStyle"
                    class="fixed inset-x-0 bottom-0 z-[210] w-full rounded-t-2xl border border-b-0 border-surface-border bg-surface
                           p-3 pb-[calc(1rem+env(safe-area-inset-bottom))]
                           sm:inset-x-auto sm:bottom-auto sm:z-[200] sm:w-56 sm:rounded-xl sm:border-b sm:p-1.5 sm:pb-1.5
                           sm:shadow-lg shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)] max-h-[70vh] overflow-y-auto edz-scroll sm:max-h-64">
                    <span class="pointer-events-none mx-auto mb-2 block h-1 w-10 rounded-full bg-surface-border sm:hidden"></span>
                    <div class="flex items-center justify-between gap-2 px-1 mb-1.5 sm:hidden">
                        <p class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink uppercase tracking-wide">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3.5 h-3.5 text-ink-muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3.5 h-3.5 text-ink-muted']); ?>
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
                            <span><?php echo e(__('merchant_panel.status')); ?></span>
                        </p>
                        <button @click="open = false" type="button"
                            class="-m-1 p-1 rounded-lg text-ink-muted hover:text-ink hover:bg-surface-tertiary"
                            title="<?php echo e(__('general.close')); ?>">
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->allStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($s['key'], $transitions) || $s['id'] == $order['status_id']): ?>
                            <button
                                wire:click="transitionOrder('<?php echo e($orderId); ?>', '<?php echo e($s['key']); ?>')"
                                wire:loading.attr="disabled" @click="open = false"
                                class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs hover:bg-surface-tertiary disabled:opacity-50 <?php echo e($s['id'] == $order['status_id'] ? 'font-bold' : ''); ?>">
                                <?php echo \Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->icon(null, 'w-3 h-3 shrink-0'); ?>

                                <span class="w-2 h-2 rounded-full shrink-0"
                                    style="background: <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('general', $s['color'] ?? 'gray')->hex()); ?>"></span>
                                <?php echo e(\Edzeery\MyStatusKit\Facades\Status::for('order', $s['key'] ?? 'default')->label()); ?>

                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </td>
        <?php break; ?>

    <?php case ('assigned_agent'): ?>
        <td class="px-4 py-3 text-xs text-ink-muted">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingField === 'order.assigned_agent' && $this->editingId === $orderId): ?>
                <div class="edz-inline-edit__edit" wire:key="agent-inline-<?php echo e($orderId); ?>">
                    <?php if (isset($component)) { $__componentOriginal98f6a35728186ef8bbcf8d819e3363cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98f6a35728186ef8bbcf8d819e3363cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.select','data' => ['wire:model' => 'editingValue','options' => $this->editAgentOptions,'size' => 'sm','search' => true,'placeholder' => ''.e(__('merchant_panel.assigned_agent')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'editingValue','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->editAgentOptions),'size' => 'sm','search' => true,'placeholder' => ''.e(__('merchant_panel.assigned_agent')).'']); ?>
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
                    <div class="edz-inline-edit__actions">
                        <button type="button" class="edz-inline-edit__save" wire:click="saveOrderAgent"
                            wire:loading.attr="disabled" wire:loading.class="edz-inline-edit__save--loading">
                            <span><?php echo e(__('buttons.save')); ?></span>
                        </button>
                        <button type="button" class="edz-inline-edit__cancel"
                            @click="$wire.cancelOrderEdit()"><?php echo e(__('buttons.cancel')); ?></button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->editingError): ?>
                        <p class="edz-inline-edit__error"><?php echo e($this->editingError); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php elseif(canStore(\App\Enums\Store\StorePermissionEnum::ORDER_MANAGE->value)): ?>
                <button type="button" class="edz-inline-edit__display"
                    @click="$wire.startOrderAgentEdit('<?php echo e($orderId); ?>')">
                    <span class="edz-inline-edit__value"><?php echo e($order['assigned_membership']['user']['name'] ?? __('merchant_panel.unassigned')); ?></span>
                </button>
            <?php else: ?>
                <?php echo e($order['assigned_membership']['user']['name'] ?? '—'); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </td>
        <?php break; ?>

    <?php case ('created_at'): ?>
        <td class="px-4 py-3 text-ink-muted text-xs">
            <?php echo e(\Carbon\Carbon::parse($order['created_at'])->format('M d, Y')); ?>

        </td>
        <?php break; ?>

    <?php case ('confirmation_attempts'): ?>
        <td class="px-4 py-3 text-ink-muted text-xs">
            <?php echo e($order['confirmation_attempts'] ?? 0); ?>

        </td>
        <?php break; ?>

    <?php case ('last_contact'): ?>
        <td class="px-4 py-3 text-ink-muted text-xs">
            <?php echo e($order['last_contact_at'] ? \Carbon\Carbon::parse($order['last_contact_at'])->diffForHumans() : '—'); ?>

        </td>
        <?php break; ?>

    <?php default: ?>
        <td class="px-4 py-3 text-ink-muted text-xs">—</td>
<?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/orders/partials/orders-table-cell.blade.php ENDPATH**/ ?>