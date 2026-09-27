
<div
    x-data="edzLoader()"
    :aria-busy="overlay ? 'true' : 'false'"
>
    <?php if (isset($component)) { $__componentOriginal270db04661da0eaab1d7bb2c87fdce3e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal270db04661da0eaab1d7bb2c87fdce3e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.loading-overlay','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.loading-overlay'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal270db04661da0eaab1d7bb2c87fdce3e)): ?>
<?php $attributes = $__attributesOriginal270db04661da0eaab1d7bb2c87fdce3e; ?>
<?php unset($__attributesOriginal270db04661da0eaab1d7bb2c87fdce3e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal270db04661da0eaab1d7bb2c87fdce3e)): ?>
<?php $component = $__componentOriginal270db04661da0eaab1d7bb2c87fdce3e; ?>
<?php unset($__componentOriginal270db04661da0eaab1d7bb2c87fdce3e); ?>
<?php endif; ?>
</div>

<script>
    (function () {
        var el = document.querySelector('.edz-loader-overlay');
        if (!el) return;

        // SPA arrival: same window, Livewire/Alpine already loaded —
        // navigation events drive the cover, do not show the boot cover.
        if (window.Livewire && window.Alpine) return;

        // Hard load: reveal immediately, before deferred scripts run.
        el.setAttribute('data-boot', '1');

        // No-Alpine safety net (static/marketing pages without Livewire):
        // the component can't release the cover, so drop it once the
        // document is fully loaded. Pages with Alpine never get here.
        if (document.readyState === 'complete') {
            window.setTimeout(function () {
                if (!window.Alpine) el.removeAttribute('data-boot');
            }, 400);
        } else {
            window.addEventListener('load', function onLoad() {
                if (!window.Alpine) el.removeAttribute('data-boot');
                window.removeEventListener('load', onLoad);
            });
        }
    })();
</script><?php /**PATH C:\laragon\www\edzeery\resources\views/components/edz/global-loader.blade.php ENDPATH**/ ?>