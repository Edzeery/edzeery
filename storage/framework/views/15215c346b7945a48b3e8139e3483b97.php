<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'wireScanMethod' => '',
    'label' => null,
    'placeholder' => '',
    'autofocus' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'wireScanMethod' => '',
    'label' => null,
    'placeholder' => '',
    'autofocus' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Only ever interpolate a bare PHP-style identifier into the Livewire
    // call sites below — anything else stays inert (no crash, no injection).
    $scanMethodValid = (bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', (string) $wireScanMethod);
?>

<div
    x-data="barcodeScanInput({ scanMethod: <?php echo \Illuminate\Support\Js::from($wireScanMethod)->toHtml() ?> })"
    @edz-modal-closed="closeCamera()"
>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($label): ?>
        <label class="edz-label mb-1.5 block"><?php echo e($label); ?></label>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex items-stretch gap-2">
        <input
            type="text"
            <?php if($scanMethodValid): ?>
                @keydown.enter.prevent="$wire.<?php echo e($wireScanMethod); ?>($event.target.value); $event.target.value = ''"
            <?php endif; ?>
            placeholder="<?php echo e($placeholder); ?>"
            <?php if($autofocus): ?>
                autofocus
            <?php endif; ?>
            <?php echo e($attributes->merge(['class' => 'edz-input w-full'])); ?>

        />

        <button
            type="button"
            @click="openCamera()"
            class="edz-btn edz-btn--primary shrink-0"
            aria-label="<?php echo e(__('edz.scan_with_camera')); ?>"
            title="<?php echo e(__('edz.scan_with_camera')); ?>"
        >
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'camera','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'camera','class' => 'w-5 h-5']); ?>
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
            <span class="ms-1.5 hidden sm:inline"><?php echo e(__('edz.scan_with_camera')); ?></span>
        </button>
    </div>

    
    <div wire:ignore>
        <template x-if="cameraOpen">
            <?php if (isset($component)) { $__componentOriginal911d914fd97d5405d92c9a7521bf08ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal911d914fd97d5405d92c9a7521bf08ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.modal','data' => ['isOpen' => true,'size' => 'md','dataEdzCamera' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['is-open' => true,'size' => 'md','data-edz-camera' => true]); ?>
                <div class="p-4 pt-1 sm:p-6 sm:pt-5">
                    <span class="edz-modal__handle" aria-hidden="true"></span>

                    <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-ink">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'qr-code','class' => 'w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'qr-code','class' => 'w-5 h-5']); ?>
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
                        <span><?php echo e(__('edz.scan_with_camera')); ?></span>
                    </h3>

                    
                    <div
                        x-ref="cameraContainer"
                        class="relative w-full aspect-video max-h-[55vh] overflow-hidden rounded-lg bg-ink/5"
                    >
                        <div
                            x-show="!scanning && cameraError === ''"
                            class="absolute inset-0 flex items-center justify-center gap-2 text-sm text-ink-muted"
                        >
                            <span class="edz-spinner" aria-hidden="true"></span>
                            <span><?php echo e(__('edz.starting_camera')); ?></span>
                        </div>

                        <div
                            x-show="cameraError !== ''"
                            class="absolute inset-0 flex items-center justify-center p-4"
                        >
                            <div class="w-full">
                                <?php if (isset($component)) { $__componentOriginal3d8e2a92a5397217854be10043063322 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3d8e2a92a5397217854be10043063322 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.alert','data' => ['type' => 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'danger']); ?>
                                    <?php echo e(__('edz.camera_unavailable')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3d8e2a92a5397217854be10043063322)): ?>
<?php $attributes = $__attributesOriginal3d8e2a92a5397217854be10043063322; ?>
<?php unset($__attributesOriginal3d8e2a92a5397217854be10043063322); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3d8e2a92a5397217854be10043063322)): ?>
<?php $component = $__componentOriginal3d8e2a92a5397217854be10043063322; ?>
<?php unset($__componentOriginal3d8e2a92a5397217854be10043063322); ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>

                    
                    <template x-if="cameras.length > 1">
                        <div class="mt-3 flex items-center gap-2">
                            <label for="edz-camera-device" class="edz-label shrink-0">
                                <?php echo e(__('edz.switch_camera')); ?>

                            </label>
                            <select
                                id="edz-camera-device"
                                class="edz-input w-full"
                                @change="selectCamera($event.target.value)"
                            >
                                <option value="" :selected="!selectedCameraId">
                                    <?php echo e(__('edz.auto_camera')); ?>

                                </option>
                                <template x-for="(cam, i) in cameras" :key="cam.id">
                                    <option :value="cam.id" :selected="selectedCameraId === cam.id" x-text="cam.label"></option>
                                </template>
                            </select>
                        </div>
                    </template>
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
        </template>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\components\edz\barcode-scan-input.blade.php ENDPATH**/ ?>