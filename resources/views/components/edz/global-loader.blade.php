{{-- ============================================================
     EDZEERY — GLOBAL LOADER (included by every page layout)
     ------------------------------------------------------------
     Full-viewport cover for app boot, SPA navigation, and opted-in
     heavy Livewire actions.

     - JS/CSS come from the shared `edz-loader.js` Vite entry.
     - On hard loads a plain inline (non-deferred) script flips
       `data-boot` so the cover is on screen at parse time — before
       Livewire/Alpine boot (which only begins at DOMContentLoaded).
     - On SPA arrivals the window already has Livewire + Alpine, so the
       cover is skipped here and driven by the navigation events instead.
     ============================================================ --}}
<div
    x-data="edzLoader()"
    :aria-busy="overlay ? 'true' : 'false'"
>
    <x-edz.loading-overlay />
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
</script>