import Swal from "sweetalert2";

const icons = {
    success: "success",
    error: "error",
    warning: "warning",
    info: "info",
    question: "question",
};

const EdzSwal = {
    fire(options) {
        const type = options.type || "success";
        return Swal.fire({
            icon: icons[type] || "info",
            title: options.title || "",
            text: options.text || "",
            html: options.html || undefined,
            timer: options.timer ?? (type === "success" ? 3000 : undefined),
            timerProgressBar: type === "success",
            showConfirmButton: type !== "success",
            confirmButtonText: options.confirmButtonText || "OK",
            confirmButtonColor: options.confirmButtonColor || "#6366f1",
            cancelButtonText: options.cancelButtonText || "Cancel",
            showCancelButton: options.showCancelButton ?? (type === "question" || type === "warning"),
            reverseButtons: true,
            toast: options.toast ?? (type === "success"),
            position: options.position || (type === "success" ? "top-end" : "center"),
            customClass: {
                popup: `edz-swal edz-swal--${type}`,
                title: "edz-swal__title",
                htmlContainer: "edz-swal__text",
                confirmButton: "edz-btn edz-btn--primary edz-btn--sm",
                cancelButton: "edz-btn edz-btn--ghost edz-btn--sm",
                actions: "edz-swal__actions",
            },
            showClass: { popup: "edz-swal-show" },
            hideClass: { popup: "edz-swal-hide" },
            ...options,
            icon: icons[type] || "info",
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
            confirmButtonColor: options.confirmColor || "#dc2626",
            ...options,
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
            confirmColor: options.confirmColor || "#6366f1",
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
                confirmButton: "edz-btn edz-btn--danger edz-btn--sm",
                cancelButton: "edz-btn edz-btn--primary edz-btn--sm",
                actions: "edz-swal__actions",
            },
        }).then((result) => {
            if (result.isConfirmed && callback) callback();
            return !!result.isConfirmed;
        });
    },
};

function initSwal() {
    if (typeof window.Livewire !== "undefined") {
        window.Livewire.on("swal", (data) => {
            const payload = Array.isArray(data) ? data[0] : data;
            EdzSwal.fire(payload);
        });
    }

    document.addEventListener("livewire:initialized", () => {
        if (typeof window.Livewire !== "undefined") {
            window.Livewire.on("swal", (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                EdzSwal.fire(payload);
            });
        }
    });
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

window.EdzSwal = EdzSwal;
export default EdzSwal;
