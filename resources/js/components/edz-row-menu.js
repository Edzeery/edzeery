export default function edzRowMenu() {
    const PANEL_W = 224; // w-56
    const PANEL_H = 280;

    return {
        open: false,
        top: 0,
        left: 0,
        menuStyle: "",

        toggle() {
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

        close() {
            this.open = false;
        },
    };
}