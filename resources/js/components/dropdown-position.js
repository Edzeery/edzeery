export default function dropdownPosition() {
    return {
        open: false,
        activeKey: null,
        top: 0,
        left: 0,

        init() {
            // "use" the passed dropdown element via $refs.dropdown when present.
            this.dropdown = this.$refs.dropdown || null;
        },

        positionAt(triggerEl) {
            if (!triggerEl) return;
            const rect = triggerEl.getBoundingClientRect();
            let top = rect.bottom + 4;
            let left = rect.left;

            // Constrain within viewport width/height.
            const width = this.dropdown ? this.dropdown.offsetWidth : 0;
            const maxLeft = window.innerWidth - Math.max(width, 240) - 8;
            if (left > maxLeft) left = maxLeft;
            if (left < 8) left = 8;
            if (top + 260 > window.innerHeight) top = rect.top - 260;

            this.top = top;
            this.left = left;
            this.open = true;
        },

        close() {
            this.open = false;
            this.activeKey = null;
        },

        toggle(event, data) {
            const key = data?.key ?? null;
            const el = data?.el || event?.currentTarget;

            if ((this.activeKey === key && this.open) || (!key && this.open)) {
                this.close();
                return;
            }

            this.activeKey = key;
            this.positionAt(el);
            this.open = key || true;
        },
    };
}
