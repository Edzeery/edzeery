import Swal from "sweetalert2";

// Project icon set — same outline glyphs as components/edz/icon.blade.php
// (Heroicons-style, 1.5px stroke, `stroke="currentColor"`). Rendered through
// SweetAlert2's `iconHtml` inside a flat tinted circular badge, so every
// Edzeery toast/dialog shares the storefront's cartToast / edz-notice look
// instead of SweetAlert2's default icons (geometrically built for 80px and
// visibly broken at the compact badge sizes we use).
const EDZ_SWAL_ICONS = {
    success: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>`,
    error: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9.75 9.75l4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>`,
    warning: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>`,
    info: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>`,
    question: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>`,
};

function isRTL() {
    return document.documentElement.dir === "rtl";
}

function toastPosition() {
    return isRTL() ? "top-start" : "top-end";
}

const EdzSwal = {
    fire(options) {
        const { type, ...rest } = options;
        const t = type || "success";

        return Swal.fire({
            ...rest,
            icon: t,
            title: rest.title || "",
            text: rest.text || "",
            html: rest.html || undefined,
            timer: rest.timer ?? (t === "success" ? 3000 : undefined),
            timerProgressBar: rest.timerProgressBar ?? (t === "success"),
            showConfirmButton: rest.showConfirmButton ?? (t !== "success"),
            confirmButtonText: rest.confirmButtonText || "OK",
            confirmButtonColor: rest.confirmButtonColor || undefined,
            cancelButtonText: rest.cancelButtonText || "Cancel",
            showCancelButton: rest.showCancelButton ?? (t === "question" || t === "warning"),
            reverseButtons: true,
            toast: rest.toast ?? (t === "success"),
            position: rest.position || (t === "success" ? toastPosition() : "center"),
            customClass: {
                popup: `edz-swal edz-swal--${t}`,
                title: "edz-swal__title",
                htmlContainer: "edz-swal__text",
                actions: "edz-swal__actions",
                confirmButton: "swal2-styled",
                cancelButton: "swal2-styled",
            },
            showClass: { popup: "edz-swal-show" },
            hideClass: { popup: "edz-swal-hide" },
            iconHtml: rest.iconHtml ?? EDZ_SWAL_ICONS[t],
        });
    },

    // `swal:toast` channel — every type renders as a compact bottom-corner
    // pill (no buttons); errors keep the badge open a touch longer to read.
    toast(options) {
        const { type, icon, title, text, ...rest } = options || {};
        const t = (type || icon || "success").toLowerCase();
        return this.fire({
            type: t,
            title: title || "",
            text: text || "",
            toast: true,
            position: toastPosition(),
            timer: rest.timer ?? (t === "error" ? 5000 : 3500),
            timerProgressBar: true,
            showConfirmButton: false,
            showCancelButton: false,
            reverseButtons: true,
            showCloseButton: rest.showCloseButton ?? false,
            ...rest,
        });
    },

    success(title, text) {
        return this.fire({ type: "success", title, text });
    },

    error(title, text) {
        return this.fire({ type: "error", title, text, timer: undefined, toast: false });
    },

    warning(title, text) {
        return this.fire({ type: "warning", title, text, timer: undefined, toast: false });
    },

    info(title, text) {
        return this.fire({ type: "info", title, text });
    },

    confirm(title, text, options = {}) {
        return this.fire({
            type: "question",
            title,
            text,
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: options.confirmText || "OK",
            cancelButtonText: options.cancelText || "Cancel",
            confirmButtonColor: options.confirmColor || undefined,
            ...options,
            customClass: {
                popup: "edz-swal edz-swal--question",
                title: "edz-swal__title",
                htmlContainer: "edz-swal__text",
                confirmButton: "swal2-styled",
                cancelButton: "swal2-styled",
                actions: "edz-swal__actions",
            },
        }).then((result) => !!result.isConfirmed);
    },

    confirmDelete(name) {
        const title = window.__swal_i18n?.confirm_delete_title || "Are you sure?";
        const text = name
            ? (window.__swal_i18n?.confirm_delete_named || 'Delete "{name}"? This cannot be undone.').replace("{name}", name)
            : (window.__swal_i18n?.confirm_delete || "This action cannot be undone.");
        return this.confirm(title, text, {
            confirmText: window.__swal_i18n?.delete || "Delete",
            confirmColor: "#dc2626",
        });
    },

    confirmBulkDelete(count) {
        const title = window.__swal_i18n?.confirm_delete_title || "Are you sure?";
        const text = (window.__swal_i18n?.confirm_bulk_delete || "Delete {count} selected items?")
            .replace("{count}", count);
        return this.confirm(title, text, {
            confirmText: window.__swal_i18n?.delete || "Delete",
            confirmColor: "#dc2626",
        });
    },

    confirmAction(title, text, options = {}) {
        return this.confirm(title, text, {
            confirmText: options.confirmText || "OK",
            confirmColor: options.confirmColor || undefined,
            ...options,
        });
    },

    unsavedChanges(callback) {
        const title = window.__swal_i18n?.unsaved_title || "Unsaved Changes";
        const text = window.__swal_i18n?.unsaved_text || "You have unsaved changes. Are you sure you want to leave?";
        return this.fire({
            type: "warning",
            title,
            text,
            showConfirmButton: true,
            showCancelButton: true,
            confirmButtonText: window.__swal_i18n?.leave || "Leave",
            cancelButtonText: window.__swal_i18n?.stay || "Stay",
            confirmButtonColor: "#dc2626",
            reverseButtons: true,
            customClass: {
                popup: "edz-swal edz-swal--warning",
                title: "edz-swal__title",
                htmlContainer: "edz-swal__text",
                confirmButton: "swal2-styled",
                cancelButton: "swal2-styled",
                actions: "edz-swal__actions",
            },
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
            return !!result.isConfirmed;
        });
    },
};

let swalBound = false;

function initSwal() {
    const bind = () => {
        if (typeof window.Livewire !== "undefined" && !swalBound) {
            swalBound = true;
// Canonical event: `swal` (payload `{ type, title, text }`, plus
            // any SweetAlert2 option). Errors/questions still surface as
            // centred modals; success renders as a bottom-corner toast.
            const handle = (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                if (!payload) return;
                const { icon, type, title, text, ...rest } = payload;
                EdzSwal.fire({ type: type || icon, title, text, ...rest });
            };

            // Legacy orders component broadcasts `swal:toast` with `{ icon, ... }`
            // — always rendered as a compact toast pill, every type included.
            const handleToast = (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                if (!payload) return;
                const { icon, type, title, text, ...rest } = payload;
                EdzSwal.toast({ type: type || icon, title, text, ...rest });
            };
            window.Livewire.on("swal", handle);
            window.Livewire.on("swal:toast", handleToast);

            // Livewire 3 broadcasts `failed-validation` whenever a component
            // validation fails ($this->validate() / rules). Surface a clear,
            // prominent error toast (first message + total count) so required
            // fields are never missed even if they sit outside the viewport.
            window.Livewire.on("failed-validation", (data) => {
                const info = Array.isArray(data) ? data[0] : data;
                const errors = Object.keys(info?.errors || {}).map(
                    (k) => `${k}: ${info.errors[k][0]}`
                );
                if (errors.length === 0) return;
                const message = errors[0];
                const suffix = errors.length > 1 ? ` (+${errors.length - 1} more)` : "";
                EdzSwal.toast({
                    type: "error",
                    title: window.__swal_i18n?.validation_title || "Please check the form",
                    text: message + suffix,
                });
            });
        }
    };
    if (typeof window.Livewire !== "undefined") {
        bind();
    } else {
        document.addEventListener("livewire:initialized", bind, { once: true });
    }
}

function checkSessionFlash() {
    const el = document.querySelector("[data-sw]");
    if (!el) return;

    const type = el.dataset.sw;
    const message = el.dataset.swMessage || "";
    const title = el.dataset.swTitle || "";

    EdzSwal.fire({ type, title: title || message, text: title ? message : "" });
    el.remove();
}

function registerI18n() {
    const meta = document.querySelector('meta[name="swal-i18n"]');
    if (meta) {
        try {
            window.__swal_i18n = JSON.parse(meta.content);
        } catch (e) {
            window.__swal_i18n = {};
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    registerI18n();
    initSwal();
    checkSessionFlash();
});

document.addEventListener("livewire:navigated", () => {
    initSwal();
    checkSessionFlash();
});

window.EdzSwal = EdzSwal;
export default EdzSwal;
