export default function productSelect(config) {
    return {
        selected: null,
        open: false,
        query: "",
        highlighted: -1,
        _busy: false,
        popupTop: 0,
        popupLeft: 0,
        popupWidth: 0,
        options: config.options || [],
        placeholder: config.placeholder || "—",
        modelName: config.modelName || null,
        fullModelName: config.fullModelName || null,

        get filteredOptions() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.options;
            return this.options.filter(
                (o) =>
                    (o.name || "").toLowerCase().includes(q) ||
                    (o.sku || "").toLowerCase().includes(q)
            );
        },

        get selectedOption() {
            return this.options.find((o) => String(o.id) === String(this.selected)) || null;
        },

        get currentLabel() {
            return this.selectedOption ? this.selectedOption.name : null;
        },

        get currentSku() {
            return this.selectedOption ? this.selectedOption.sku : null;
        },

        get hasNameFilter() {
            return (config.fullModelName && this.query.trim() !== "") || false;
        },

        get panelStyle() {
            if (!this.open) return "display:none;";
            const isMobile = window.innerWidth < 640;
            if (isMobile) {
                const w = Math.min(this.$refs.trigger?.offsetWidth || 300, window.innerWidth - 16);
                return `position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:${w}px;z-index:70;border-radius:var(--edz-radius-2xl) var(--edz-radius-2xl) 0 0;max-height:60vh;`;
            }
            return `position:fixed;z-index:70;width:${this.popupWidth}px;top:${this.popupTop}px;left:${this.popupLeft}px;`;
        },

        updatePosition() {
            const trigger = this.$refs.trigger;
            if (!trigger) return;
            const rect = trigger.getBoundingClientRect();
            if (window.innerWidth < 640) return;
            this.popupWidth = Math.max(rect.width, 320);
            this.popupLeft = rect.left;
            this.popupTop = rect.bottom + 4;
            if (this.popupLeft + this.popupWidth > window.innerWidth - 8) {
                this.popupLeft = Math.max(8, window.innerWidth - this.popupWidth - 8);
            }
            const spaceBelow = window.innerHeight - this.popupTop;
            if (spaceBelow < 220 && rect.top > spaceBelow) {
                this.popupTop = Math.max(8, rect.top - 4 - 280);
            }
        },

        init() {
            if (this.modelName && this.$wire) {
                try {
                    let v = this.$wire.get(this.modelName);
                    if (v !== null && v !== undefined && v !== "") this.selected = v;
                } catch (e) {}
            }
        },

        toggle() {
            if (this._busy) return;
            this._busy = true;
            setTimeout(() => (this._busy = false), 150);
            this.open = !this.open;
            if (this.open) {
                this.updatePosition();
                this.highlighted = -1;
                this.$nextTick(() => {
                    if (this.$refs.searchInput) this.$refs.searchInput.focus();
                });
            }
        },

        close() {
            this.open = false;
        },

        select(id) {
            this.selected = id;
            this.open = false;
            this.query = "";
            if (this.modelName && this.$wire) {
                this.$wire.set(this.modelName, id);
            }
        },

        applyNameFilter() {
            if (!this.fullModelName || !this.$wire) return;
            this.open = false;
            this.selected = null;
            if (this.modelName && this.$wire) {
                this.$wire.set(this.modelName, null);
            }
            this.$wire.set(this.fullModelName, this.query.trim());
        },

        clear() {
            this.open = false;
            this.query = "";
            this.selected = null;
            if (this.modelName && this.$wire) {
                this.$wire.set(this.modelName, null);
            }
            if (this.fullModelName && this.$wire) {
                this.$wire.set(this.fullModelName, "");
            }
        },

        moveHighlight(delta) {
            const max = this.filteredOptions.length - 1;
            this.highlighted = Math.min(max, Math.max(0, this.highlighted + delta));
        },

        selectHighlighted() {
            const opt = this.filteredOptions[this.highlighted];
            if (opt) this.select(opt.id);
        },
    };
}
