export default function orderRowActions(el) {
    const orderId = el.dataset.orderId;
    const orderNumber = el.dataset.orderNumber;

    return {
        open: false,
        top: 0,
        left: 0,
        deleteLoading: false,

        openStatusMenu() {
            const trigger = this.$refs.trigger;
            if (!trigger) return;
            const r = trigger.getBoundingClientRect();
            this.top = r.bottom + 4;
            this.left = r.left;
            // Avoid the menu overflowing the bottom (flip above).
            if (this.top + 260 > window.innerHeight) this.top = r.top - 260;
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

    return {
        open: false,
        top: 0,
        left: 0,

        toggle() {
            if (!canView) return;
            this.$wire.loadOrderEvents(orderId);
            const trigger = this.$refs.evTrigger;
            if (!trigger) return;
            const r = trigger.getBoundingClientRect();
            this.top = r.bottom + 4;
            this.left = r.left;
            if (this.top + 320 > window.innerHeight) this.top = r.top - 320;
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },
    };
}

export function orderMoreMenu(el) {
    return {
        open: false,
        top: 0,
        left: 0,

        toggle() {
            const trigger = this.$refs.moreTrigger;
            if (!trigger) return;
            const r = trigger.getBoundingClientRect();
            this.top = r.bottom + 4;
            this.left = r.left;
            if (this.top + 320 > window.innerHeight) this.top = r.top - 320;
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },
    };
}
