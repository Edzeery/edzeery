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
