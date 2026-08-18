import "./bootstrap.js";
import "./swal.js";

document.addEventListener("alpine:init", () => {
    const Alpine = window.Alpine;

    Alpine.store("theme", {
        theme:
            localStorage.getItem("edz-theme") ||
            (window.matchMedia?.("(prefers-color-scheme: dark)").matches
                ? "dark"
                : "light"),
        toggle() {
            this.theme = this.theme === "dark" ? "light" : "dark";
            localStorage.setItem("edz-theme", this.theme);
            this.apply();
        },
        apply() {
            document.documentElement.classList.toggle(
                "dark",
                this.theme === "dark",
            );
        },
    });

    Alpine.store("theme").apply();

    Alpine.store("shell", {
        open: false,
        collapsed: localStorage.getItem("edz-sidebar-collapsed") === "1",
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            localStorage.setItem(
                "edz-sidebar-collapsed",
                this.collapsed ? "1" : "0",
            );
        },
    });

    Alpine.data("edzDirty", () => ({
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

    document.addEventListener("click", (e) => {
        const link = e.target.closest("a[wire\\:navigate]");
        if (!link) return;
        const form = document.querySelector("form[x-data*='edzDirty']");
        if (!form) return;
        const comp = Alpine.$data(form);
        if (comp && comp.dirty) {
            e.preventDefault();
            e.stopPropagation();
            comp.confirmLeave(() => {
                window.location.href = link.href;
            });
        }
    }, true);
});
