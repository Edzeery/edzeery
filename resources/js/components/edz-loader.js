// ============================================================
//  EDZEERY — GLOBAL LOADER (edzLoader)
//  ------------------------------------------------------------
//  Alpine.data component bound once in the root panel layout.
//
//  Loading surfaces:
//
//    navigation  — Livewire's own NProgress bar, restyled to the
//                  brand via `--livewire-progress-bar-color` (see
//                  _loader.scss). We deliberately do NOT replace or
//                  disable it: the plugin starts it *before* the SPA
//                  fetch, so it covers the entire navigation window —
//                  custom `livewire:navigating` listeners fire too
//                  late (after the fetch, right before the swap) and
//                  can't survive the body swap.
//
//    overlay     — full-viewport cover for the hard-load boot and for
//                  actions opted in via <x-edz.loading-target />.
//
//  The BOOT overlay cannot rely on X-show/X-cloak alone: Alpine boots only
//  at DOMContentLoaded, after the page has already painted and components
//  have rendered — the cover would appear far too late. Instead a layout
//  inline script (non-deferred, runs during parse) flips `data-boot` on the
//  cover so it is on screen BEFORE anything boots; the component adopts it
//  in init(), holds it for a guaranteed minimum (BOOT_MIN_MS) and until the
//  page + fonts are genuinely ready, then hands visibility back to CSS.
//
//  The ACTION overlay keeps a flicker guard because trivial `$wire` calls
//  must never flash a full-viewport cover.
// ============================================================

// Anti-flash guard: only applied to opted-in action overlays.
const FLICKER_GUARD_MS = 150;

// Guaranteed minimum on-screen time for the boot overlay so entry
// feedback is perceived even when the load finishes almost instantly.
const BOOT_MIN_MS = 500;

// `document.fonts.ready` must never be able to hang the boot overlay
// (e.g. blocked font CDNs); race it against this timeout.
const BOOT_FONTS_TIMEOUT_MS = 3000;

// If a navigation is started (`livewire:navigate`) but the swap event
// never arrives (prevented by another listener, abort, …), release the
// cover after this long so the UI can never stick.
const NAV_WATCHDOG_MS = 10000;

// The wrapper element lives in the shared layout, so a single instance
// survives wire:navigate. `active` is refreshed at every mount so hooks
// bound once at module level always reach the live component.
let active = null;
let hooksBound = false;

// True while an SPA navigation is in flight. Module-level so the swapped-in
// page can keep a cover up until `livewire:navigated` fires on its instance.
let navActive = false;
let navWatchdog = null;

export default function edzLoader() {
    return {
        phase: "idle", // 'idle' | 'loading' | 'settling'
        overlay: false,
        label: null,

        _overlayCount: 0,
        _overlayTimer: null,
        _overlayHideTimer: null,
        _overlayShownAt: 0,
        _overlayEl: null,
        _adoptedBoot: false,

        init() {
            active = this;

            // A layout inline script reveals the cover at HTML-parse time
            // (before Alpine/Livewire boot) on hard loads. Adopt it here so
            // state, aria-busy, and the boot min-time all stay consistent.
            this._overlayEl = this.$el.querySelector(".edz-loader-overlay");
            if (this._overlayEl && this._overlayEl.hasAttribute("data-boot")) {
                this.overlay = true;
                this._adoptedBoot = true;
                this._overlayShownAt = performance.now();
            }

            // SPA arrival: if a navigation is still in flight (Livewire
            // dispatches `livewire:navigated` only after this page's Alpine
            // initTree, which we're part of), keep a cover on the fresh
            // page until that event releases it.
            if (navActive && !this.overlay) {
                this.overlay = true;
                this._overlayShownAt = performance.now();
            }

            if (hooksBound) return;
            hooksBound = true;

            this.bindHardLoad();
            this.bindNavigation();
            this.bindRequests();
        },

        // ---------------------------------------------------------- //
        //  Fresh per-request registry of overlay-eligible actions.   //
        //  Read at request time (never cached): `<x-edz.loading-target>`
        //  merges itself into `window.EdzLoaderActions` on every render,
        //  and the panel layout snapshots the full set on loads/navigates.
        // ---------------------------------------------------------- //
        registry() {
            return typeof window.EdzLoaderActions === "object" &&
                window.EdzLoaderActions !== null
                ? window.EdzLoaderActions
                : {};
        },

        // ---------------------------------------------------------- //
        //  App boot — overlay shows immediately, holds for BOOT_MIN_MS //
        //  and until the page + fonts are genuinely ready.            //
        // ---------------------------------------------------------- //
        bindHardLoad() {
            if (!this.overlay) {
                // No pre-parse CSS cover (e.g. the inline script was skipped)
                // — fall back to an Alpine-driven reveal, still immediate.
                this.beginOverlay(null, true);
            }

            const fontsReady =
                document.fonts && typeof document.fonts.ready === "object"
                    ? Promise.race([
                          document.fonts.ready,
                          new Promise((resolve) =>
                              setTimeout(
                                  () => resolve(true),
                                  BOOT_FONTS_TIMEOUT_MS,
                              ),
                          ),
                      ])
                    : Promise.resolve(true);

            const pageLoaded = new Promise((resolve) => {
                if (document.readyState === "complete") {
                    resolve(true);
                } else {
                    window.addEventListener("load", () => resolve(true), {
                        once: true,
                    });
                }
            });

            Promise.all([pageLoaded, fontsReady])
                .catch(() => true)
                .then(() => {
                    if (!active) return;
                    active._finishBoot();
                });
        },

        _finishBoot() {
            const self = this;
            const elapsed = performance.now() - this._overlayShownAt;
            const remaining = BOOT_MIN_MS - Math.max(0, elapsed);
            const hide = () => {
                if (!active || active !== self) return;
                // Release the pre-parse attribute so later action overlays
                // start (and fade out) from the CSS-hidden state.
                if (self._adoptedBoot && self._overlayEl) {
                    self._overlayEl.removeAttribute("data-boot");
                    self._adoptedBoot = false;
                }
                self.endOverlay();
            };
            if (remaining > 0) {
                this._overlayHideTimer = setTimeout(hide, remaining);
            } else {
                hide();
            }
        },

        // ---------------------------------------------------------- //
        //  SPA navigation — cover the whole wire:navigate window.     //
        //  `livewire:navigate` fires at click time (before the fetch),//
        //  `livewire:navigated` after the swap + new-page Alpine init.//
        // ---------------------------------------------------------- //
        bindNavigation() {
            document.addEventListener("livewire:navigate", () => {
                if (!active) return;
                navActive = true;
                active.beginOverlay(null, true);

                clearTimeout(navWatchdog);
                navWatchdog = setTimeout(() => {
                    if (!navActive) return;
                    navActive = false;
                    if (active) active.endOverlay();
                }, NAV_WATCHDOG_MS);
            });

            // Fetch done, synchronous swap imminent; `livewire:navigated`
            // is guaranteed to follow, so the watchdog can stand down.
            document.addEventListener("livewire:navigating", () => {
                clearTimeout(navWatchdog);
            });

            document.addEventListener("livewire:navigated", () => {
                navActive = false;
                clearTimeout(navWatchdog);
                if (!active) return;
                active.endOverlay();
            });
        },

        // ---------------------------------------------------------- //
        //  Opted-in heavy actions — overlay keyed to the real request.//
        // ---------------------------------------------------------- //
        bindRequests() {
            if (typeof window.Livewire === "undefined") return;

            window.Livewire.hook("request", ({ payload, succeed, fail }) => {
                if (!payload) return;

                let body = payload;
                if (typeof body === "string") {
                    try {
                        body = JSON.parse(body);
                    } catch {
                        return;
                    }
                }

                const calls = Array.isArray(body?.components)
                    ? body.components.flatMap((c) =>
                          Array.isArray(c?.calls) ? c.calls : [],
                      )
                    : [];
                const names = new Set(
                    calls.map((c) => c?.method).filter(Boolean),
                );
                if (!names.size) return;

                // Read the registry fresh so morph-registered actions
                // (e.g. the bulk bar that only exists once orders are
                // selected) are matched on this request.
                const registry = active.registry();
                const action = Object.keys(registry).find((name) =>
                    names.has(name),
                );
                if (!action) return;

                const label = registry[action] ?? null;
                active.beginOverlay(label);

                let settled = false;
                succeed(() => {
                    if (!active || settled) return;
                    settled = true;
                    active.endOverlay();
                });
                fail(() => {
                    if (!active || settled) return;
                    settled = true;
                    active.endOverlay();
                });
            });
        },

        // ---------------------------------------------------------- //
        //  Overlay state machine                                     //
        // ---------------------------------------------------------- //
        beginOverlay(label, immediate = false) {
            this._overlayCount++;
            if (this._overlayCount > 1) {
                if (label && !this.label) this.label = label;
                return;
            }

            if (label) this.label = label;

            clearTimeout(this._overlayTimer);
            this._overlayTimer = setTimeout(() => {
                if (this._overlayCount === 0) return;
                this._showOverlayNow();
            }, immediate ? 0 : FLICKER_GUARD_MS);
        },

        _showOverlayNow() {
            this.phase = "loading";
            this.overlay = true;
            this._overlayShownAt = performance.now();
        },

        endOverlay() {
            this._overlayCount = Math.max(0, this._overlayCount - 1);
            if (this._overlayCount > 0) return;

            clearTimeout(this._overlayTimer);
            this._overlayHideTimer = null;
            if (this.overlay) {
                this.overlay = false;
            }
            if (this.phase === "loading") {
                this.phase = "settling";
            }
            this.label = null;
        },
    };
}