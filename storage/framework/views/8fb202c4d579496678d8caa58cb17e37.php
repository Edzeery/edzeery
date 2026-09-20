

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showTableSettings): ?>
    <div @edz-modal-closed.window="$wire.discardTableSettings()">
        <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'lg','wire:key' => 'tracking-table-settings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['isOpen' => true,'size' => 'lg','wire:key' => 'tracking-table-settings']); ?>
            <div x-data="{ tab: 'columns' }" class="p-6">
                
                <div class="mb-5">
                    <h3 class="text-lg font-bold text-ink"><?php echo e(__('merchant_panel.table_settings')); ?></h3>
                </div>

                
                <div class="inline-flex w-full sm:w-auto items-center gap-1 p-1 bg-surface-secondary rounded-xl mb-5">
                    <button @click="tab = 'columns'" type="button"
                        class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-lg transition"
                        :class="tab === 'columns' ? 'bg-surface text-ink shadow-sm' : 'text-ink-muted hover:text-ink'">
                        <span class="inline-flex items-center gap-1.5">
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
                            <?php echo e(__('merchant_panel.tab_columns')); ?>

                        </span>
                    </button>
                    <button @click="tab = 'style'" type="button"
                        class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-lg transition"
                        :class="tab === 'style' ? 'bg-surface text-ink shadow-sm' : 'text-ink-muted hover:text-ink'">
                        <span class="inline-flex items-center gap-1.5">
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'color-palette','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'color-palette','class' => 'w-4 h-4']); ?>
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
                            <?php echo e(__('merchant_panel.tab_style')); ?>

                        </span>
                    </button>
                </div>

                
                <div x-show="tab === 'columns'" x-cloak class="space-y-5">
                    <?php
                        $settingsColumns = $this->trackingColumns();
                        $settingsAllKeys = collect($settingsColumns)->pluck('key')->all();
                        $settingsRequiredKeys = collect($settingsColumns)->where('required', true)->pluck('key')->all();
                        $settingsVisibleDraft = array_values(array_intersect($this->draftColumns, $settingsAllKeys));
                        $settingsSortedAll = array_merge(
                            $settingsVisibleDraft,
                            collect($settingsColumns)->pluck('key')->reject(fn ($k) => in_array($k, $settingsVisibleDraft, true))->values()->all(),
                        );
                    ?>

                    <div>
                        <p class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                            <?php echo e(__('merchant_panel.columns')); ?></p>
                        <p class="text-xs text-ink-muted mb-2"><?php echo e(__('merchant_panel.primary_columns_hint')); ?></p>
                        <p class="text-xs text-ink-muted mb-2"><?php echo e(__('merchant_panel.drag_to_reorder')); ?></p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5"
                            x-data="orderColumnReorderDraft()"
                            @dragstart="onDragStart($event)"
                            @dragover="onDragOver($event)"
                            @drop="onDrop($event)"
                            @dragleave="onDragLeave($event)"
                            @dragend="onDragEnd()">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $settingsSortedAll; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $settingsKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $settingsCol = collect($settingsColumns)->firstWhere('key', $settingsKey);
                                    $settingsLabel = in_array($settingsKey, ['tracking_status', 'tracking_number'], true)
                                        ? __("order_flow.{$settingsCol['label_key']}")
                                        : __("merchant_panel.{$settingsCol['label_key']}");
                                    $settingsIsChecked = in_array($settingsKey, $settingsVisibleDraft, true);
                                    $settingsIndex = array_search($settingsKey, $settingsVisibleDraft, true);
                                    $settingsPos = $settingsIndex !== false ? $settingsIndex + 1 : null;
                                    $settingsCanUp = $settingsIndex !== false && $settingsIndex > 0;
                                    $settingsCanDown = $settingsIndex !== false && $settingsIndex < count($settingsVisibleDraft) - 1;
                                    $settingsIsRequired = in_array($settingsKey, $settingsRequiredKeys, true);
                                ?>
                                <label
                                    class="flex items-center gap-2 px-2.5 py-2 rounded-lg border border-surface-border hover:bg-surface-secondary cursor-pointer text-sm <?php echo e($settingsIsRequired ? 'bg-surface-secondary/60' : ''); ?>"
                                    data-col-key="<?php echo e($settingsKey); ?>"
                                    data-col-row="true">
                                    <span class="cursor-grab text-ink-muted hover:text-ink shrink-0 opacity-70"
                                        draggable="true"
                                        title="<?php echo e(__('merchant_panel.drag_to_reorder')); ?>">
                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'bars-2','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bars-2','class' => 'w-4 h-4']); ?>
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
                                    </span>
                                    <?php if (isset($component)) { $__componentOriginal0283f82cff84f4c646f29d974f5967a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0283f82cff84f4c646f29d974f5967a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.checkbox','data' => ['size' => 'sm','wire:click' => 'toggleDraftColumn(\''.e($settingsKey).'\')','checked' => $settingsIsChecked,'disabled' => $settingsIsRequired,'class' => $settingsIsRequired ? 'opacity-70' : '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','wire:click' => 'toggleDraftColumn(\''.e($settingsKey).'\')','checked' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($settingsIsChecked),'disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($settingsIsRequired),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($settingsIsRequired ? 'opacity-70' : '')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0283f82cff84f4c646f29d974f5967a4)): ?>
<?php $attributes = $__attributesOriginal0283f82cff84f4c646f29d974f5967a4; ?>
<?php unset($__attributesOriginal0283f82cff84f4c646f29d974f5967a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0283f82cff84f4c646f29d974f5967a4)): ?>
<?php $component = $__componentOriginal0283f82cff84f4c646f29d974f5967a4; ?>
<?php unset($__componentOriginal0283f82cff84f4c646f29d974f5967a4); ?>
<?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settingsIsRequired): ?>
                                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'lock-closed','class' => 'w-3.5 h-3.5 shrink-0 text-ink-muted','title' => ''.e(__('merchant_panel.primary_columns')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lock-closed','class' => 'w-3.5 h-3.5 shrink-0 text-ink-muted','title' => ''.e(__('merchant_panel.primary_columns')).'']); ?>
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
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span class="flex-1 min-w-0 truncate">
                                        <?php echo e($settingsLabel); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settingsIsRequired): ?>
                                            <span class="text-[10px] text-ink-muted font-normal">
                                                (<?php echo e(__('merchant_panel.always_visible')); ?>)</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settingsIsChecked): ?>
                                        <span class="text-[10px] text-ink-muted tabular-nums shrink-0" wire:key="col-order-<?php echo e($settingsKey); ?>">
                                            <?php echo e($settingsPos); ?>

                                        </span>
                                        <span class="flex items-center gap-0.5 shrink-0">
                                            <button type="button" title="Up"
                                                wire:click="moveDraftColumn('<?php echo e($settingsKey); ?>', 'up')"
                                                <?php if(!$settingsCanUp): echo 'disabled'; endif; ?>
                                                class="p-1 rounded text-ink-muted hover:text-ink hover:bg-surface-tertiary disabled:opacity-30 disabled:cursor-not-allowed">
                                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-up','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-up','class' => 'w-3.5 h-3.5']); ?>
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
                                            <button type="button" title="Down"
                                                wire:click="moveDraftColumn('<?php echo e($settingsKey); ?>', 'down')"
                                                <?php if(!$settingsCanDown): echo 'disabled'; endif; ?>
                                                class="p-1 rounded text-ink-muted hover:text-ink hover:bg-surface-tertiary disabled:opacity-30 disabled:cursor-not-allowed">
                                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-down','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-down','class' => 'w-3.5 h-3.5']); ?>
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
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div x-show="tab === 'style'" x-cloak>
                    <p class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                        <?php echo e(__('merchant_panel.tab_style')); ?></p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button type="button" wire:click="$set('draftStyle', 'default')"
                            class="flex items-start gap-3 text-start p-4 rounded-xl border transition <?php echo e($this->draftStyle === 'default' ? 'border-accent-500 ring-1 ring-accent-500 bg-accent-50/40' : 'border-surface-border hover:bg-surface-secondary'); ?>">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-ink"><?php echo e(__('merchant_panel.style_default')); ?></p>
                                <div class="mt-2 flex items-center gap-1.5 text-[10px]">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-surface-secondary text-ink-muted">#1001</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-surface-secondary text-ink-muted">#1002</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-surface-secondary text-ink-muted">#1003</span>
                                </div>
                            </div>
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-4 h-4 mt-0.5 shrink-0 '.e($this->draftStyle === 'default' ? 'text-accent-600' : 'text-surface-border').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-4 h-4 mt-0.5 shrink-0 '.e($this->draftStyle === 'default' ? 'text-accent-600' : 'text-surface-border').'']); ?>
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

                        <button type="button" wire:click="$set('draftStyle', 'status')"
                            class="flex items-start gap-3 text-start p-4 rounded-xl border transition <?php echo e($this->draftStyle === 'status' ? 'border-accent-500 ring-1 ring-accent-500 bg-accent-50/40' : 'border-surface-border hover:bg-surface-secondary'); ?>">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-ink"><?php echo e(__('merchant_panel.style_status')); ?></p>
                                <p class="mt-0.5 text-xs text-ink-muted"><?php echo e(__('merchant_panel.style_status_hint')); ?></p>
                                <div class="mt-2 rounded-lg overflow-hidden border border-surface-border text-[10px]">
                                    <table class="w-full">
                                        <tbody>
                                            <tr class="edz-table-row--success">
                                                <td class="px-2.5 py-1.5 font-semibold">#1001</td>
                                                <td class="px-2.5 py-1.5"><?php echo e(__('order_flow.tracking_status')); ?></td>
                                            </tr>
                                            <tr class="edz-table-row--warning">
                                                <td class="px-2.5 py-1.5 font-semibold">#1002</td>
                                                <td class="px-2.5 py-1.5"><?php echo e(__('order_flow.tracking_status')); ?></td>
                                            </tr>
                                            <tr class="edz-table-row--danger">
                                                <td class="px-2.5 py-1.5 font-semibold">#1003</td>
                                                <td class="px-2.5 py-1.5"><?php echo e(__('order_flow.tracking_status')); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'check','class' => 'w-4 h-4 mt-0.5 shrink-0 '.e($this->draftStyle === 'status' ? 'text-accent-600' : 'text-surface-border').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-4 h-4 mt-0.5 shrink-0 '.e($this->draftStyle === 'status' ? 'text-accent-600' : 'text-surface-border').'']); ?>
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

                
                <div class="flex flex-wrap items-center justify-between gap-3 pt-5 mt-6 border-t border-surface-border">
                    <button type="button" wire:click="resetColumns"
                        class="edz-btn edz-btn--ghost edz-btn--sm"><?php echo e(__('merchant_panel.reset_columns')); ?></button>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="discardTableSettings"
                            class="edz-btn edz-btn--ghost edz-btn--sm"><?php echo e(__('merchant_panel.cancel')); ?></button>
                        <button wire:click="saveTableSettings" class="edz-btn edz-btn--primary edz-btn--sm"
                            wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
                            <span><?php echo e(__('merchant_panel.save_settings')); ?></span>
                        </button>
                    </div>
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
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/tracking/partials/tracking-table-settings-modal.blade.php ENDPATH**/ ?>