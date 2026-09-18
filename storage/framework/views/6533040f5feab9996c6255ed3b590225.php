
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canViewEvents): ?>
    <div class="relative shrink-0" x-data="orderEventsMenu($el)" @click.away="close()" data-order-id="<?php echo e($orderId); ?>"
        data-can-view="<?php echo e($canViewEvents ? '1' : '0'); ?>">
        <?php if (isset($component)) { $__componentOriginaldc6b8a3f696fa5e7823376deba19f536 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.tooltip','data' => ['label' => ''.e(__('order_flow.order_timeline')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('order_flow.order_timeline')).'']); ?>
            <button @click="toggle()" x-ref="evTrigger" type="button"
                class="edz-btn edz-btn--ghost edz-btn--xs shrink-0 <?php echo e(($touch ?? false) ? 'min-h-11 min-w-11' : ''); ?>"
                aria-label="<?php echo e(__('order_flow.order_timeline')); ?>" :aria-expanded="open.toString()" aria-haspopup="dialog">
                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'clock','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','class' => 'w-4 h-4 shrink-0']); ?>
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
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $attributes = $__attributesOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__attributesOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536)): ?>
<?php $component = $__componentOriginaldc6b8a3f696fa5e7823376deba19f536; ?>
<?php unset($__componentOriginaldc6b8a3f696fa5e7823376deba19f536); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal7087b0753e523d7f0d0628a70db89f2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7087b0753e523d7f0d0628a70db89f2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.mobile-bottom-sheet','data' => ['title' => __('order_flow.order_timeline'),'icon' => 'clock','closeExpr' => 'close()','smWidth' => 'sm:w-80','smMaxHeight' => 'sm:max-h-[340px]','smPad' => 'sm:p-2 sm:pb-2','smZ' => '','keepHeaderSm' => true,'scrimFade' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.mobile-bottom-sheet'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('order_flow.order_timeline')),'icon' => 'clock','close-expr' => 'close()','sm-width' => 'sm:w-80','sm-max-height' => 'sm:max-h-[340px]','sm-pad' => 'sm:p-2 sm:pb-2','sm-z' => '','keep-header-sm' => true,'scrim-fade' => true]); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->eventsPreviewOrderId === $orderId && !empty($this->eventsPreview)): ?>
                <ol class="divide-y divide-surface-border">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_slice($this->eventsPreview, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-start gap-2 px-1 py-2.5 text-xs">
                            <span
                                class="mt-1 w-1.5 h-1.5 rounded-full shrink-0 <?php echo e($loop->first ? 'bg-accent-600' : 'bg-surface-border'); ?>"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-ink leading-snug"><?php echo e($ev['message'] ?? '—'); ?></p>
                                <p class="text-xs text-ink-muted mt-0.5">
                                    <?php echo e(__('order_flow.event_type_' . ($ev['event_type'] ?? 'note'))); ?>

                                    • <?php echo e(\Carbon\Carbon::parse($ev['occurred_at'])->diffForHumans()); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ev['actor']['user']['name'])): ?>
                                        • <?php echo e($ev['actor']['user']['name']); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ol>
                <button type="button" wire:click="openOrderEventsModal('<?php echo e($orderId); ?>')" @click="close()"
                    class="w-full mt-1.5 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-accent-700 bg-accent-50 hover:bg-accent-100 sm:py-1.5 sm:text-xs">
                    <?php echo e(__('order_flow.show_more')); ?>

                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'chevron-down','class' => 'w-3.5 h-3.5 sm:w-3 sm:h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'w-3.5 h-3.5 sm:w-3 sm:h-3']); ?>
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
                <p class="px-1 py-3 text-xs text-ink-muted"><?php echo e(__('order_flow.order_timeline_loading')); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7087b0753e523d7f0d0628a70db89f2f)): ?>
<?php $attributes = $__attributesOriginal7087b0753e523d7f0d0628a70db89f2f; ?>
<?php unset($__attributesOriginal7087b0753e523d7f0d0628a70db89f2f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7087b0753e523d7f0d0628a70db89f2f)): ?>
<?php $component = $__componentOriginal7087b0753e523d7f0d0628a70db89f2f; ?>
<?php unset($__componentOriginal7087b0753e523d7f0d0628a70db89f2f); ?>
<?php endif; ?>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/orders/partials/order-events-menu.blade.php ENDPATH**/ ?>