// ============================================================
//  EDZEERY — GUEST BUNDLE (auth / create-store / choose-store)
//  ------------------------------------------------------------
//  Lean public bundle. Alpine comes from Livewire (@livewireScripts).
//  Only what the guest shell actually uses lives here.
//  edzDirty was moved out of the legacy resources/js/app.js (now dead).
// ============================================================

import initNativeButtonLoading from "./native-button-loading.js";

initNativeButtonLoading();

document.addEventListener("alpine:init", () => {
    window.Alpine.data("edzDirty", () => ({
        dirty: false,
        _snapshot: "",
        _formEl: null,

        init() {
            this._formEl = this.$el.closest("form");
            if (!this._formEl) return;
            this._snapshot = this._serialize();
            this._formEl.addEventListener("input", () => {
                this.dirty = this._serialize() !== this._snapshot;
            });
            this._formEl.addEventListener("reset", () => {
                this.$nextTick(() => {
                    this._snapshot = this._serialize();
                    this.dirty = false;
                });
            });
            window.addEventListener("beforeunload", (e) => {
                if (this.dirty) {
                    e.preventDefault();
                    e.returnValue = "";
                }
            });
            document.addEventListener("livewire:navigating", () => {
                this.dirty = false;
            });
            this.$el.addEventListener("livewire:updated", () => {
                this.$nextTick(() => {
                    this._snapshot = this._serialize();
                    this.dirty = false;
                });
            });
        },

        _serialize() {
            const fd = new FormData(this._formEl);
            const entries = [];
            for (const [key, value] of fd.entries()) {
                if (value instanceof File) {
                    entries.push(`${key}=${value.name}:${value.size}`);
                } else {
                    entries.push(`${key}=${value}`);
                }
            }
            return entries.join("&");
        },

        markClean() {
            this._snapshot = this._serialize();
            this.dirty = false;
        },

        confirmLeave(callback) {
            if (!this.dirty) {
                callback();
                return;
            }
            EdzSwal.unsavedChanges(() => {
                this.dirty = false;
                callback();
            });
        },
    }));
});