export default function edzTooltip(preferredSide = "top") {
    const SHOW_DELAY = 400; // Apple-style: hover must be sustained before the label appears
    const HIDE_DELAY = 60;
    const GAP = 8;
    const EDGE = 8;

    return {
        visible: false,
        positioning: false,
        side: preferredSide === "bottom" ? "bottom" : "top",
        bubbleStyle: "",
        showTimer: null,
        hideTimer: null,

        init() {
            // Tooltips never surface on touch-only / coarse-pointer devices.
            if (!window.matchMedia("(hover: hover) and (pointer: fine)").matches) {
                this.enter = () => {};
                this.leave = () => {};
                this.hide = () => {};
            }
        },

        enter() {
            clearTimeout(this.hideTimer);
            clearTimeout(this.showTimer);
            this.showTimer = setTimeout(() => this.show(), SHOW_DELAY);
        },

        leave() {
            clearTimeout(this.showTimer);
            clearTimeout(this.hideTimer);
            this.hideTimer = setTimeout(() => this.hide(), HIDE_DELAY);
        },

        hide() {
            clearTimeout(this.showTimer);
            clearTimeout(this.hideTimer);
            this.visible = false;
        },

        show() {
            const trigger = this.$refs.trigger;
            const bubble = this.$refs.bubble;
            if (!trigger || !bubble) return;

            // Render the bubble invisibly (positioning class) so we can measure
            // its real size, then pin it to the viewport — fixed positioning
            // escapes the table's overflow scroll container.
            this.positioning = true;
            this.visible = true;
            this.$nextTick(() => {
                const r = trigger.getBoundingClientRect();
                const b = bubble.getBoundingClientRect();
                const { innerWidth, innerHeight } = window;

                let top;
                if (this.side === "bottom") {
                    top = r.bottom + GAP;
                    if (top + b.height > innerHeight - EDGE) {
                        top = r.top - b.height - GAP;
                        this.side = "top";
                    }
                } else {
                    top = r.top - b.height - GAP;
                    if (top < EDGE) {
                        top = r.bottom + GAP;
                        this.side = "bottom";
                    }
                }

                let left = r.left + r.width / 2 - b.width / 2;
                left = Math.max(EDGE, Math.min(left, innerWidth - b.width - EDGE));

                this.bubbleStyle = "top:" + top + "px;left:" + left + "px";
                this.positioning = false;
            });
        },
    };
}