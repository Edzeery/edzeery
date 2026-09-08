export default function orderRowActions(el) {
    const orderId = el.dataset.orderId;
    const orderNumber = el.dataset.orderNumber;
    const PANEL_W = 224; // w-56
    const PANEL_H = 260; // max-h-64

    return {
        open: false,
        top: 0,
        left: 0,
        menuStyle: "",
        deleteLoading: false,

        openStatusMenu() {
            const trigger = this.$refs.trigger;
            if (!trigger) return;
            // Phones (<640px): render as a bottom sheet — inline positioning is
            // cleared so the inset-x-0/bottom-0 classes drive the layout.
            if (window.matchMedia("(max-width: 639px)").matches) {
                this.menuStyle = "";
                this.open = !this.open;
                return;
            }
            const r = trigger.getBoundingClientRect();
            let top = r.bottom + 4;
            if (top + PANEL_H > window.innerHeight) top = Math.max(8, r.top - PANEL_H);
            let left = r.left;
            const maxLeft = Math.min(r.right, window.innerWidth - PANEL_W - 8);
            if (left > maxLeft) left = maxLeft;
            if (left < 8) left = 8;
            this.top = top;
            this.left = left;
            this.menuStyle = "top:" + top + "px; left:" + left + "px";
            this.open = !this.open;
        },

        async confirmDelete() {
            if (this.deleteLoading) return;
            if (!(await EdzSwal.confirmDelete(orderNumber))) return;
            this.deleteLoading = true;
            try {
                await this.$wire.deleteOrder(orderId);
            } finally {
                this.deleteLoading = false;
            }
        },
    };
}

export function orderEventsMenu(el) {
    const orderId = el.dataset.orderId;
    const canView = el.dataset.canView === "1";
    const MENU_W = 320; // w-80
    const MENU_H = 320; // max-h-[340px] plus trigger offset

    return {
        open: false,
        top: 0,
        left: 0,
        menuStyle: "",

        toggle() {
            if (!canView) return;
            this.$wire.loadOrderEvents(orderId);
            const trigger = this.$refs.evTrigger;
            if (!trigger) return;
            // Phones (<640px): render as a bottom sheet — inline positioning is
            // cleared so the inset-x-0/bottom-0 classes drive the layout.
            if (window.matchMedia("(max-width: 639px)").matches) {
                this.menuStyle = "";
                this.open = !this.open;
                return;
            }
            const r = trigger.getBoundingClientRect();
            let top = r.bottom + 4;
            if (top + MENU_H > window.innerHeight) top = Math.max(8, r.top - MENU_H);
            let left = r.left;
            const maxLeft = Math.min(r.right, window.innerWidth - MENU_W - 8);
            if (left > maxLeft) left = maxLeft;
            if (left < 8) left = 8;
            this.top = top;
            this.left = left;
            this.menuStyle = "top:" + top + "px; left:" + left + "px";
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },
    };
}

export function orderMoreMenu(el) {
    const PANEL_W = 240; // w-60
    const PANEL_H = 240;

    return {
        open: false,
        top: 0,
        left: 0,
        menuStyle: "",

        toggle() {
            const trigger = this.$refs.moreTrigger;
            if (!trigger) return;
            if (window.matchMedia("(max-width: 639px)").matches) {
                this.menuStyle = "";
                this.open = !this.open;
                return;
            }
            const r = trigger.getBoundingClientRect();
            let top = r.bottom + 4;
            if (top + PANEL_H > window.innerHeight) top = Math.max(8, r.top - PANEL_H);
            let left = r.left;
            const maxLeft = Math.min(r.right, window.innerWidth - PANEL_W - 8);
            if (left > maxLeft) left = maxLeft;
            if (left < 8) left = 8;
            this.top = top;
            this.left = left;
            this.menuStyle = "top:" + top + "px; left:" + left + "px";
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },
    };
}

export function itemsEditMenu(el) {
    const PANEL_W = 240; // w-60
    const PANEL_H = 260; // max-h-64

    return {
        open: false,
        top: 0,
        left: 0,
        menuStyle: "",

        toggle() {
            const trigger = this.$refs.itemsMenuTrigger;
            if (!trigger) return;
            if (window.matchMedia("(max-width: 639px)").matches) {
                this.menuStyle = "";
                this.open = !this.open;
                return;
            }
            const r = trigger.getBoundingClientRect();
            let top = r.bottom + 4;
            if (top + PANEL_H > window.innerHeight) top = Math.max(8, r.top - PANEL_H);
            let left = r.left;
            const maxLeft = Math.min(r.right, window.innerWidth - PANEL_W - 8);
            if (left > maxLeft) left = maxLeft;
            if (left < 8) left = 8;
            this.top = top;
            this.left = left;
            this.menuStyle = "top:" + top + "px; left:" + left + "px";
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },
    };
}
