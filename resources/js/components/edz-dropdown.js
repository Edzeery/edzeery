/*
 * EDZEERY — shared <x-edz.dropdown> controller.
 *
 * Same placement strategy as orderEventsMenu/orderMoreMenu/dropdownPosition:
 *  - Phones (<640px): the panel is a full-width CSS bottom sheet, so inline
 *    positioning is cleared and the inset-x-0/bottom-0 classes drive layout.
 *  - sm+: the panel is position:fixed and clamped to the viewport edges so it
 *    never overflows horizontally in either RTL or LTR (no stray scrollbar).
 */
export default function edzDropdown() {
    const EDGE = 8;

    return {
        open: false,
        positioning: false,
        menuStyle: "",

        toggle() {
            if (this.open) {
                this.close();
                return;
            }
            // Phones (<640px): CSS bottom sheet — no inline positioning.
            if (window.matchMedia("(max-width: 639px)").matches) {
                this.menuStyle = "";
                this.open = true;
                return;
            }
            this.open = true;
            this.positioning = true;
            this.$nextTick(() => {
                const trigger = this.$refs.trigger;
                const panel = this.$refs.panel;
                if (!trigger || !panel) {
                    this.positioning = false;
                    return;
                }
                const r = trigger.getBoundingClientRect();
                const w = panel.offsetWidth;
                const h = panel.offsetHeight;

                let top = r.bottom + 4;
                if (top + h > window.innerHeight) {
                    top = Math.max(EDGE, r.top - h);
                }

                // Align toward whichever side of the trigger has more room,
                // then clamp inside 8px gutters so the panel never overflows.
                let left = window.innerWidth - r.right >= r.left ? r.left : r.right - w;
                left = Math.max(EDGE, Math.min(left, window.innerWidth - w - EDGE));

                this.menuStyle = "top:" + top + "px; left:" + left + "px";
                this.$nextTick(() => {
                    this.positioning = false;
                });
            });
        },

        close() {
            this.open = false;
            this.menuStyle = "";
        },
    };
}