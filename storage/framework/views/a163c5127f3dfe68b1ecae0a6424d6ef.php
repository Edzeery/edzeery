
<script>window.EdzLoaderActions = <?php echo \Illuminate\Support\Js::from(\App\Support\Loading\LoadingActions::all())->toHtml() ?>;</script>


<div
    class="edz-loader-overlay"
    :class="overlay ? 'edz-loader-overlay--visible' : ''"
    :aria-hidden="overlay ? 'false' : 'true'"
    role="status"
    aria-live="polite"
>
    <span class="sr-only"><?php echo e(__('messages.loading')); ?></span>
    <div class="edz-loader-overlay__inner">
        <div class="edz-loader-overlay__mark" aria-hidden="true">E</div>
        <p class="edz-loader-overlay__label" x-show="label" x-cloak x-text="label"></p>
    </div>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\components\edz\loading-overlay.blade.php ENDPATH**/ ?>