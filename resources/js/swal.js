import Swal from "sweetalert2";

function isDark() {
    return document.documentElement.classList.contains("dark");
}

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
            timerProgressBar: t === "success",
            showConfirmButton: t !== "success",
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
            iconHtml: undefined,
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
            window.Livewire.on("swal", (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                EdzSwal.fire(payload);
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
