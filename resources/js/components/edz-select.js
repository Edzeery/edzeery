export default function edzSelect(config) {
    return {
        open: false,
        highlighted: -1,
        query: '',
        selected: null,
        _toggleBusy: false,
        _selectBusy: false,
        openUpward: false,
        popupTop: 0,
        popupLeft: 0,
        popupWidth: 0,
        options: config.options || [],
        backendOptions: [],
        loading: false,
        searchTimeout: null,
        searchable: config.searchable || false,
        hasBackendSearch: config.hasBackendSearch || false,
        searchMinChars: config.searchMinChars || 2,
        wireMethodName: config.wireMethodName || null,

        get allOptions() {
            return [...this.options, ...this.backendOptions];
        },

        get filteredOptions() {
            if (!this.searchable || this.query.trim() === '') return this.allOptions;
            const q = this.query.toLowerCase();
            return this.allOptions.filter(o =>
                o.label.toLowerCase().includes(q) ||
                (o.hint && o.hint.toLowerCase().includes(q))
            );
        },

        get currentLabel() {
            const opt = this.allOptions.find(o => o.value === this.selected);
            return opt ? opt.label : null;
        },

        get currentHint() {
            const opt = this.allOptions.find(o => o.value === this.selected);
            return opt && opt.hint ? opt.hint : null;
        },

        get panelStyle() {
            if (!this.open) return 'display:none;';
            const isMobile = window.innerWidth < 640;
            if (isMobile) {
                const w = Math.min(this.$refs.trigger?.offsetWidth || 300, window.innerWidth - 16);
                return `position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:${w}px;z-index:70;border-radius:var(--edz-radius-2xl) var(--edz-radius-2xl) 0 0;max-height:60vh;`;
            }
            const s = `position:fixed;z-index:70;width:${this.popupWidth}px;`;
            if (this.openUpward) {
                return s + `bottom:${window.innerHeight - this.popupTop}px;left:${this.popupLeft}px;`;
            }
            return s + `top:${this.popupTop}px;left:${this.popupLeft}px;`;
        },

        init() {
            this.selected = this.$refs.hiddenInput?.value || null;
        },

        toggle() {
            if (this._toggleBusy) return;
            if (this.$el.querySelector('.edz-select__trigger')?.disabled) return;
            this._toggleBusy = true;
            setTimeout(() => { this._toggleBusy = false; }, 150);

            this.open = !this.open;
            if (this.open) {
                this.query = '';
                this.backendOptions = [];
                this.highlighted = this.allOptions.findIndex(o => o.value === this.selected);
                this.updatePosition();
                this.$nextTick(() => {
                    if (this.searchable && this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            }
        },

        close() {
            this.open = false;
        },

        updatePosition() {
            const trigger = this.$refs.trigger;
            if (!trigger) return;
            const rect = trigger.getBoundingClientRect();
            const isMobile = window.innerWidth < 640;
            if (isMobile) return;

            this.popupWidth = rect.width;
            this.popupLeft = rect.left;
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            if (spaceBelow < 280 && spaceAbove > spaceBelow) {
                this.popupTop = rect.top - 4;
                this.openUpward = true;
            } else {
                this.popupTop = rect.bottom + 4;
                this.openUpward = false;
            }

            if (this.popupLeft + this.popupWidth > window.innerWidth - 8) {
                this.popupLeft = window.innerWidth - this.popupWidth - 8;
            }
            if (this.popupLeft < 8) {
                this.popupLeft = 8;
            }
        },

        select(value) {
            if (this._selectBusy) return;
            this._selectBusy = true;
            setTimeout(() => { this._selectBusy = false; }, 200);

            this.selected = value;
            this.open = false;
            this.$nextTick(() => {
                const input = this.$refs.hiddenInput;
                if (input) {
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    input.dispatchEvent(new CustomEvent('livewire-change', { bubbles: true }));
                }
            });
        },

        onQueryChange() {
            this.highlighted = this.filteredOptions.length > 0 ? 0 : -1;
            if (!this.hasBackendSearch || !this.wireMethodName) return;

            clearTimeout(this.searchTimeout);
            const q = this.query.trim();
            if (q.length < this.searchMinChars) {
                this.backendOptions = [];
                return;
            }
            const localResults = this.options.filter(o =>
                o.label.toLowerCase().includes(q) ||
                (o.hint && o.hint.toLowerCase().includes(q))
            );
            if (localResults.length > 0) {
                this.backendOptions = [];
                return;
            }
            this.loading = true;
            this.searchTimeout = setTimeout(() => {
                this.$wire.call(this.wireMethodName, q)
                    .then(results => {
                        this.backendOptions = (results || []).map(r => ({
                            value: r.value ?? r.id ?? r,
                            label: r.label ?? r.name ?? String(r),
                            hint: r.hint ?? null,
                        }));
                        this.loading = false;
                        this.highlighted = this.filteredOptions.length > 0 ? 0 : -1;
                    })
                    .catch(() => { this.loading = false; });
            }, 300);
        },

        moveHighlight(delta) {
            if (!this.open) { this.toggle(); return; }
            const max = this.filteredOptions.length - 1;
            this.highlighted = Math.min(max, Math.max(0, this.highlighted + delta));
        },

        selectHighlighted() {
            const opt = this.filteredOptions[this.highlighted];
            if (opt) this.select(opt.value);
        }
    };
}
