import "./bootstrap.js";
import "./swal.js";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import Chart from "chart.js/auto";
import edzSelect from "./components/edz-select.js";
import edzMultiSelect from "./components/edz-multi-select.js";
import orderProductPicker from "./components/order-product-picker.js";
import productSelect from "./components/product-select.js";
import dropdownPosition from "./components/dropdown-position.js";
import orderRowActions, { orderEventsMenu, orderMoreMenu, itemsEditMenu } from "./components/order-row-actions.js";
import edzRowMenu from "./components/edz-row-menu.js";
import edzTooltip from "./components/edz-tooltip.js";
import edzDropdown from "./components/edz-dropdown.js";
import barcodeScanInput from "./components/barcode-scan-input.js";
import orderColumnReorderDraft from "./components/order-column-reorder.js";
import initButtonLoading from "./edz-button-loading.js";

window.flatpickr = flatpickr;
window.Chart = Chart;

function registerEdzPanel() {
    if (window.__edzPanelRegistered) return;
    window.__edzPanelRegistered = true;

    const Alpine = window.Alpine;
    if (!Alpine) return;

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

    const mediaDesktop = window.matchMedia("(min-width: 1024px)");

    Alpine.store("shell", {
        open: false,
        collapsed: localStorage.getItem("edz-sidebar-collapsed") === "1",
        hovered: false,
        // Collapsed icon-only rail is only meaningful on desktop; on
        // small screens the sidebar is an off-canvas drawer.
        get effectiveCollapsed() {
            return this.collapsed && mediaDesktop.matches && !this.hovered;
        },
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            this.hovered = false;
            localStorage.setItem(
                "edz-sidebar-collapsed",
                this.collapsed ? "1" : "0",
            );
        },
        setHovered(val) {
            // Only allow the temporary expand-on-hover behaviour while the
            // rail is collapsed on desktop — never on mobile.
            if (mediaDesktop.matches && this.collapsed) {
                this.hovered = val;
            } else {
                this.hovered = false;
            }
        },
        // Force UI back into a known state when the viewport crosses breakpoints.
        onResize() {
            if (!mediaDesktop.matches) {
                this.hovered = false;
            }
        },
    });

    if (mediaDesktop.addEventListener) {
        mediaDesktop.addEventListener("change", () => {
            Alpine.store("shell").onResize();
        });
    } else if (mediaDesktop.addListener) {
        mediaDesktop.addListener(() => {
            Alpine.store("shell").onResize();
        });
    }

    // --- Global dirty state store ---
    Alpine.store("dirty", {
        forms: new Set(),
        isDirty() {
            return this.forms.size > 0;
        },
        register(id) {
            this.forms.add(id);
        },
        unregister(id) {
            this.forms.delete(id);
        },
        clear() {
            this.forms.clear();
        },
    });

    // --- Livewire hook: abort SPA navigation when dirty ---
    function setupNavigateHook() {
        if (typeof window.Livewire === "undefined") return;
        window.Livewire.hook("navigate", () => {
            if (Alpine.store("dirty").isDirty()) {
                throw new Error("navigate-aborted-by-dirty-guard");
            } else {
                Alpine.store("dirty").clear();
            }
        });
    }
    if (typeof window.Livewire !== "undefined") {
        setupNavigateHook();
    } else {
        document.addEventListener("livewire:initialized", setupNavigateHook, {
            once: true,
        });
    }

    // --- Capture-phase click interceptor for wire:navigate links ---
    document.addEventListener(
        "click",
        (e) => {
            const link = e.target.closest("a[wire\\:navigate]");
            if (!link) return;
            if (!Alpine.store("dirty").isDirty()) return;

            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            // Remove wire:navigate so Livewire's handler ignores this click
            const hadNavigate = link.hasAttribute("wire:navigate");
            if (hadNavigate) link.removeAttribute("wire:navigate");

            EdzSwal.unsavedChanges(() => {
                // User confirmed "Leave" — navigate via Livewire SPA
                Alpine.store("dirty").clear();
                if (hadNavigate) {
                    link.setAttribute("wire:navigate", "");
                    link.click();
                } else {
                    window.location.href = link.href;
                }
            });
            // If user clicked "Stay", wire:navigate stays removed —
            // link becomes a regular <a>, next click re-triggers the guard.
        },
        true,
    );

    // --- Unsaved changes guard for browser-level navigation ---
    window.addEventListener("beforeunload", (e) => {
        if (Alpine.store("dirty").isDirty()) {
            e.preventDefault();
            e.returnValue = "";
        }
    });

    // --- edzSelect custom dropdown component ---
    Alpine.data("edzSelect", edzSelect);

    // --- edzMultiSelect: searchable chips multi-select (x-edz.multi-select) ---
    Alpine.data("edzMultiSelect", edzMultiSelect);

    Alpine.data("dropdownPosition", dropdownPosition);

    // --- Order product picker component ---
    Alpine.data("orderProductPicker", orderProductPicker);

    // --- Order table row actions (status menu + delete confirm) ---
    Alpine.data("orderRowActions", orderRowActions);

    // --- Per-row order event-log dropdown (P29.4) ---
    Alpine.data("orderEventsMenu", orderEventsMenu);

    // --- Mobile card overflow actions popover (P29.7) ---
    Alpine.data("orderMoreMenu", orderMoreMenu);

    // --- Tracking row actions (anchored over the row / bottom sheet on phones) ---
    Alpine.data("edzRowMenu", edzRowMenu);

    // --- Apple-style floating tooltip (x-edz.tooltip) ---
    Alpine.data("edzTooltip", edzTooltip);

    // --- Mobile card items-editor menu (P31.9: products/quantity/price modals trigger) ---
    Alpine.data("itemsEditMenu", itemsEditMenu);

    // --- Shared navbar dropdown (notifications / user menu) with viewport clamping ---
    Alpine.data("edzDropdown", edzDropdown);

    // --- Reusable barcode scan input (Phase 36 / Phase E.3): text input + camera modal.
    //     html5-qrcode itself is code-split and lazy-loaded on first camera open. ---
    Alpine.data("barcodeScanInput", barcodeScanInput);

    // --- Order column settings: drag-and-drop reorder of all columns ---
    Alpine.data("orderColumnReorderDraft", orderColumnReorderDraft);

    // --- Reusable product select (searchable, up to N products, frontend search) ---
    Alpine.data("productSelect", productSelect);

    // --- edzDirty Alpine component ---
    Alpine.data("edzDirty", () => ({
        dirty: false,
        _snapshot: "",
        _formEl: null,
        _id: null,

        init() {
            this._formEl = this.$el.closest("form");
            if (!this._formEl) return;
            this._id = this._formEl.id || "form-" + Math.random().toString(36).slice(2, 9);
            if (!this._formEl.id) this._formEl.id = this._id;

            this._snapshot = this._serialize();
            this._formEl.addEventListener("input", () => {
                this.dirty = this._serialize() !== this._snapshot;
                this._syncStore();
            });
            this._formEl.addEventListener("reset", () => {
                this.$nextTick(() => {
                    this._snapshot = this._serialize();
                    this.dirty = false;
                    this._syncStore();
                });
            });
            this.$el.addEventListener("livewire:updated", () => {
                this.$nextTick(() => {
                    this._snapshot = this._serialize();
                    this.dirty = false;
                    this._syncStore();
                });
            });
        },

        _syncStore() {
            if (this.dirty) {
                Alpine.store("dirty").register(this._id);
            } else {
                Alpine.store("dirty").unregister(this._id);
            }
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
            this._syncStore();
        },
    }));
}

if (window.Alpine) {
    registerEdzPanel();
} else {
    document.addEventListener("alpine:init", registerEdzPanel, { once: true });
}

// Auto-initialize flatpickr on elements with .flatpickr-input class
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.flatpickr-input').forEach(el => {
        if (!el._flatpickr) {
            flatpickr(el, {
                dateFormat: 'Y-m-d',
                allowInput: true,
                clickOpens: true,
                onChange: (selectedDates, dateStr, instance) => {
                    // Trigger Livewire wire:model update
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                },
            });
        }
    });
});

// Re-initialize on Livewire navigation
document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('.flatpickr-input').forEach(el => {
        if (!el._flatpickr) {
            flatpickr(el, {
                dateFormat: 'Y-m-d',
                allowInput: true,
                clickOpens: true,
                onChange: (selectedDates, dateStr, instance) => {
                    // Trigger Livewire wire:model update
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                },
            });
        }
    });
});

initButtonLoading();
