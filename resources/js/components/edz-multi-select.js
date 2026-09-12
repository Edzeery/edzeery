// Advanced multi-select built on the same primitives as edz-select:
// a hidden <select multiple> is the Livewire contract, while chips, a
// client-side search and a bottom-sheet/panel are rendered by Alpine.

// Livewire re-initialises Alpine components after every round-trip, which
// would reset `open` and close the panel on the very first selection. We keep
// the panel decision in a module scoped map keyed by the bound model so the
// re-initialised instance can resume the previous state for a short window.
const persistence = new Map();

const RESUME_WINDOW_MS = 4000;

export default function edzMultiSelect(config) {
    const key = config.modelName || null;
    const prior = key ? persistence.get(key) : null;
    const resumeOpen =
        prior && Date.now() - prior.at < RESUME_WINDOW_MS ? !!prior.open : false;

    return {
        open: resumeOpen,
        highlighted: -1,
        query: "",
        selected: [],
        _toggleBusy: false,
        openUpward: false,
        popupTop: 0,
        popupLeft: 0,
        popupWidth: 0,
        options: [],
        searchable: config.searchable || false,
        searchMinChars: config.searchMinChars || 2,
        modelName: config.modelName || null,
        roundtrip: config.roundtrip || false,
        maxChips: config.maxChips || 3,

        get filteredOptions() {
            if (!this.searchable || this.query.trim() === "") return this.options;
            const q = this.query.toLowerCase();
            return this.options.filter(
                (o) =>
                    o.label.toLowerCase().includes(q) ||
                    (o.hint && o.hint.toLowerCase().includes(q)),
            );
        },

        get selectedOptions() {
            return this.options.filter((o) => this.selected.includes(o.value));
        },

        get visibleChips() {
            return this.selectedOptions.slice(0, this.maxChips);
        },

        get moreCount() {
            return Math.max(0, this.selectedOptions.length - this.maxChips);
        },

        get panelStyle() {
            if (!this.open) return "display:none;";
            const isMobile = window.innerWidth < 640;
            if (isMobile) {
                const w = Math.min(480, window.innerWidth - 16);
                return `position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:${w}px;z-index:70;border-radius:var(--edz-radius-2xl) var(--edz-radius-2xl) 0 0;max-height:60vh;`;
            }
            const s = `position:fixed;z-index:70;width:${this.popupWidth}px;`;
            if (this.openUpward) {
                return s + `bottom:${window.innerHeight - this.popupTop}px;left:${this.popupLeft}px;`;
            }
            return s + `top:${this.popupTop}px;left:${this.popupLeft}px;`;
        },

        isSelected(value) {
            return this.selected.includes(String(value));
        },

        init() {
            this.$nextTick(() => {
                this._readOptionsFromDom();
                this._readSelectedFromDom();
                // The panel can resume already open after a Livewire round-trip;
                // recompute its fixed position instead of starting from 0,0.
                if (this.open) this.updatePosition();
            });

            this._documentClickHandler = (e) => {
                if (this.open && !this.$el.contains(e.target)) {
                    this.open = false;
                    if (key) persistence.set(key, { open: false, at: Date.now() });
                }
            };
            this._repositionHandler = () => {
                if (this.open && window.innerWidth >= 640) {
                    this.updatePosition();
                }
            };
            document.addEventListener("click", this._documentClickHandler);
            document.addEventListener("scroll", this._repositionHandler, true);
            window.addEventListener("resize", this._repositionHandler);

            this._bindServerValue();
            this._syncFromServer();
        },

        _readOptionsFromDom() {
            const input = this.$refs.hiddenInput;
            if (!input) return;
            const opts = [];
            input.querySelectorAll("option").forEach((opt) => {
                opts.push({
                    value: String(opt.value),
                    label: opt.textContent.trim() || opt.value,
                    hint: opt.dataset.hint || null,
                    code: opt.dataset.code || null,
                });
            });
            this.options = opts;
        },

        _readSelectedFromDom() {
            const input = this.$refs.hiddenInput;
            if (!input) return;
            const vals = [];
            input.querySelectorAll("option").forEach((opt) => {
                if (opt.selected) vals.push(String(opt.value));
            });
            this.selected = vals;
        },

        _writeToDom() {
            const input = this.$refs.hiddenInput;
            if (!input) return;
            input.querySelectorAll("option").forEach((opt) => {
                opt.selected = this.selected.includes(opt.value);
            });
            input.dispatchEvent(new Event("input", { bubbles: true }));
            input.dispatchEvent(new Event("change", { bubbles: true }));
            if (key) persistence.set(key, { open: this.open, at: Date.now() });
        },

        _bindServerValue() {
            if (!this.modelName || !this.$wire) return;
            const read = () => {
                let v;
                try {
                    v = this.$wire.get(this.modelName);
                } catch (e) {
                    return;
                }
                this.selected = Array.isArray(v)
                    ? v.map(String)
                    : v
                      ? [String(v)]
                      : [];
            };
            read();
            try {
                this.$wire.$watch(this.modelName, read);
            } catch (e) {}
        },

        // Livewire morphs the hidden select in place after every round-trip:
        // the option list and the `selected` markers change on the DOM, so the
        // reactive lists (chips, panel) stay fresh by re-reading from it.
        _syncFromServer() {
            if (typeof window.MutationObserver === "undefined") return;
            const input = this.$refs.hiddenInput;
            if (!input) return;

            this._syncObserver = new MutationObserver(() => {
                this._readSelectedFromDom();
                this._readOptionsFromDom();
            });

            this._syncObserver.observe(input, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ["selected", "value"],
            });
        },

        destroy() {
            this._syncObserver?.disconnect();
            if (this._documentClickHandler) {
                document.removeEventListener("click", this._documentClickHandler);
            }
            if (this._repositionHandler) {
                document.removeEventListener("scroll", this._repositionHandler, true);
                window.removeEventListener("resize", this._repositionHandler);
            }
        },

        toggle() {
            if (this._toggleBusy) return;
            if (this.$el.querySelector(".edz-select__trigger")?.disabled) return;
            this._toggleBusy = true;
            setTimeout(() => {
                this._toggleBusy = false;
            }, 150);

            if (this.open) {
                this.close();
                return;
            }

            this.open = true;
            if (key) persistence.set(key, { open: true, at: Date.now() });
            this._readOptionsFromDom();
            this.query = "";
            this.highlighted =
                this.selected.length > 0
                    ? this.filteredOptions.findIndex(
                          (o) => o.value === String(this.selected[0]),
                      )
                    : -1;
            this.updatePosition();
            this.$nextTick(() => {
                if (this.searchable && this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        close() {
            this.open = false;
            if (key) persistence.set(key, { open: false, at: Date.now() });
            this.$nextTick(() => this.$refs.trigger?.focus());
        },

        updatePosition() {
            const trigger = this.$refs.trigger;
            if (!trigger) return;
            const rect = trigger.getBoundingClientRect();
            if (window.innerWidth < 640) return;

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

        toggleValue(value) {
            const v = String(value);
            if (this.selected.includes(v)) {
                this.selected = this.selected.filter((x) => x !== v);
            } else {
                this.selected = [...this.selected, v];
            }
            this._writeToDom();
        },

        removeValue(value) {
            const v = String(value);
            this.selected = this.selected.filter((x) => x !== v);
            this._writeToDom();
        },

        onQueryChange() {
            this.highlighted = this.filteredOptions.length > 0 ? 0 : -1;
        },

        moveHighlight(delta) {
            if (!this.open) {
                this.toggle();
                return;
            }
            const max = this.filteredOptions.length - 1;
            this.highlighted = Math.min(max, Math.max(0, this.highlighted + delta));
        },

        selectHighlighted() {
            const opt = this.filteredOptions[this.highlighted];
            if (opt) this.toggleValue(opt.value);
        },
    };
}