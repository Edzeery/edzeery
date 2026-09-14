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
        modelName: config.modelName || null,
        lazy: config.lazy || false,
        roundtrip: config.roundtrip || false,
        remoteSource: config.remoteSource || null,
        remoteScope: config.remoteScope || null,
        loadingLabel: config.loadingLabel || 'Loading...',
        _pendingAck: null,
        _ackTimeout: null,
        _remoteCache: new Map(),
        _remoteReadyScope: null,

        get labelForType() {
            // Backend live-search has its own apt wording; lazy fetches are a
            // plain "loading" state.
            return (this.lazy && this.remoteSource) ? this.loadingLabel : 'Searching...';
        },

        get allOptions() {
            return [...this.options, ...this.backendOptions];
        },

        get filteredOptions() {
            if (!this.searchable || this.query.trim() === '') return this.allOptions;
            const q = this.query.toLowerCase();
            return this.allOptions.filter(o => !o.isDivider && (
                (o.code && o.code.toLowerCase().includes(q)) ||
                o.label.toLowerCase().includes(q) ||
                (o.hint && o.hint.toLowerCase().includes(q))
            ));
        },

        get currentLabel() {
            const opt = this.allOptions.find(o => o.value === this.selected);
            return opt ? opt.label : null;
        },

        get currentHint() {
            const opt = this.allOptions.find(o => o.value === this.selected);
            return opt && opt.hint ? opt.hint : null;
        },

        get currentCode() {
            const opt = this.allOptions.find(o => o.value === this.selected);
            return opt && opt.code ? opt.code : null;
        },

        get panelStyle() {
            if (!this.open) return 'display:none;';
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

        init() {
            this._readRemoteConfig();
            // The server-rendered `value` attribute (when the caller passes
            // `value=`) is the authoritative initial seed. It must survive
            // _bindServerValue's first read: that read reflects the Livewire
            // reactive mirror which is still stale during the morph that
            // inserts this fresh element, so a null/previous value there must
            // NOT wipe a seed we already hold.
            this.selected = this.$refs.hiddenInput?.value || null;
            this._bindServerValue();

            this._documentClickHandler = (e) => {
                if (this.open && !this.$el.contains(e.target)) {
                    this.open = false;
                }
            };
            // Reposition the fixed panel while it stays open: the trigger may
            // move when the modal body scrolls or the window resizes.
            this._repositionHandler = () => {
                if (this.open && window.innerWidth >= 640) {
                    this.updatePosition();
                }
            };
            document.addEventListener('click', this._documentClickHandler);
            document.addEventListener('scroll', this._repositionHandler, true);
            window.addEventListener('resize', this._repositionHandler);
            this._syncFromServer();
        },

        // Livewire v3 keeps the bound model path inside component.reactive, the
        // JS mirror of the server state. Read it once at mount, then watch it:
        // the mirror is mutated after every round-trip, so the trigger label and
        // the selected check follow whatever the server persisted (numeric-safe
        // via String()). This is the real v3 primitive — the old fake listener
        // (livewire:updated) never fired because Livewire ships no such event.
        _bindServerValue() {
            if (!this.modelName || !this.$wire) return;

            const read = () => {
                let v;
                try {
                    v = this.$wire.get(this.modelName);
                } catch (e) {
                    return;
                }
                this.selected = v === null || v === undefined || v === '' ? null : String(v);
                if (this._pendingAck !== null) {
                    // Server applied our pick: the round-trip is over, unlock.
                    this._releaseAck();
                }
            };

            // Seed from the reactive mirror ONLY when there is no server-baked
            // `value` attribute to trust. When the attribute exists it is the
            // freshest render of the model, so reading the mirror here would
            // clobber a correct preselect with a stale (usually empty) value
            // captured mid-morph; the $watch below still corrects any genuine
            // later change.
            const seed = () => {
                let v;
                try {
                    v = this.$wire.get(this.modelName);
                } catch (e) {
                    return;
                }
                if ((v === null || v === undefined || v === '') && this.selected !== null) {
                    return;
                }
                this.selected = v === null || v === undefined || v === '' ? null : String(v);
            };

            seed();
            try {
                this.$wire.$watch(this.modelName, read);
            } catch (e) {}
        },

        // Livewire applies the picked value to the bound property on the next
        // round-trip; until then the trigger stays locked with a spinner so a
        // fast second tap cannot fire a second, racing request. Any server data
        // change on the model counts as the ack — re-picking the same option is
        // a no-op server-side and is skipped entirely.
        _waitServerAck(value) {
            this.loading = true;
            this._pendingAck = value;
            clearTimeout(this._ackTimeout);
            this._ackTimeout = setTimeout(() => this._releaseAck(true), 6000);
        },

        _releaseAck(timeout = false) {
            if (this._pendingAck === null && ! timeout) return;
            this._pendingAck = null;
            clearTimeout(this._ackTimeout);
            this.loading = false;
        },

        _readRemoteConfig() {
            this.lazy = this.$el.getAttribute('data-lazy') === '1';
            this.roundtrip = this.$el.getAttribute('data-roundtrip') === '1';
            this.remoteSource = this.$el.getAttribute('data-source') || null;
            this.remoteScope = this.$el.getAttribute('data-scope') || '';
        },

        _scope() {
            return this.remoteScope || '';
        },

        _onScopeChanged() {
            // The server switched the scope behind a lazily fetched list (e.g. a
            // new wilaya): drop back to the embedded seed until its fetch lands.
            if (this._remoteReadyScope !== null && this._remoteReadyScope !== this._scope()) {
                this._remoteReadyScope = null;
                this._applySeed();
            }
        },

        _applySeed() {
            const raw = this.$el?.getAttribute('data-options');
            if (! raw) return;
            try {
                this.options = JSON.parse(raw);
            } catch (e) {}
        },

        _normalizeRemote(raw) {
            return {
                value: String(raw?.value ?? raw?.id ?? raw),
                label: String(raw?.label ?? raw?.name ?? raw),
                hint: raw?.hint ?? null,
                code: raw?.code ?? null,
            };
        },

        _trimCache() {
            // 48 scopes ≈ the cascade of a busy session (partner × wilaya ×
            // version bumps) — high enough to avoid refetches on a round trip
            // through the order form, low enough to not hold stale payloads.
            while (this._remoteCache.size > 48) {
                this._remoteCache.delete(this._remoteCache.keys().next().value);
            }
        },

        // Lazy mode: the heavy option list is fetched from the server the first
        // time the panel opens for a given scope and then cached client-side, so
        // re-opening is instant and the full list is never embedded in the page.
        async ensureRemoteOptions() {
            if (! this.lazy || ! this.remoteSource || ! this.$wire) return;
            const scope = this._scope();

            if (this._remoteCache.has(scope)) {
                this.options = this._remoteCache.get(scope);
                this._remoteReadyScope = scope;
                return;
            }

            this.loading = true;
            const requestedScope = scope;
            try {
                const results = await this.$wire.call(this.remoteSource, scope);
                if (requestedScope !== this._scope()) {
                    // The scope moved while the fetch was in flight: cache the
                    // payload under its own key but never show it in the UI.
                    if (Array.isArray(results)) this._remoteCache.set(requestedScope, results.map(this._normalizeRemote));
                    return;
                }
                const options = (results || []).map(this._normalizeRemote);
                this._remoteCache.set(scope, options);
                this._trimCache();
                this.options = options;
                this._remoteReadyScope = scope;
                this.highlighted = this.filteredOptions.length > 0 ? 0 : -1;
            } catch (e) {
                // Keep whatever list we already hold; a re-open retries.
            } finally {
                if (requestedScope === this._scope()) {
                    this.loading = false;
                }
            }
        },

        // Livewire morphs re-render this element in place: the option list
        // (data-options) and the wire:model value (hidden input `value` attr)
        // change on the DOM but Alpine state would otherwise stay stale, so the
        // selected check, the trigger label and the re-built option list would
        // never reflect the server. Resync from those attributes whenever they
        // change instead of relying on a (not guaranteed) remount.
        _syncFromServer() {
            if (typeof window.MutationObserver === 'undefined') return;

            const input = this.$refs.hiddenInput;

            const applyOptions = () => {
                // Under lazy mode the server only carries the tiny seed; once a
                // fetch for the current scope has landed, the fetched list is
                // authoritative and morphing the seed must not clobber it.
                if (this.lazy && this._remoteReadyScope === this._scope()) return;
                const raw = this.$el?.getAttribute('data-options');
                if (!raw) return;
                try {
                    this.options = JSON.parse(raw);
                } catch (e) {}
            };
            const applyValue = () => {
                const v = input?.getAttribute('value');
                if (v !== null && v !== undefined && String(this.selected) !== v) {
                    this.selected = v;
                }
            };

            applyOptions();

            this._syncObserver = new MutationObserver((mutations) => {
                for (const mutation of mutations) {
                    if (mutation.attributeName === 'data-options') {
                        applyOptions();
                    } else if (mutation.attributeName === 'data-scope') {
                        this.remoteScope = this.$el?.getAttribute('data-scope') ?? '';
                        this._onScopeChanged();
                    } else if (mutation.attributeName === 'value') {
                        applyValue();
                    }
                }
            });

            if (this.$el) {
                this._syncObserver.observe(this.$el, {
                    attributes: true,
                    attributeFilter: ['data-options', 'data-scope', 'data-source', 'data-lazy', 'data-roundtrip'],
                });
            }
            if (input) {
                this._syncObserver.observe(input, {
                    attributes: true,
                    attributeFilter: ['value'],
                });
            }
        },

        destroy() {
            this._syncObserver?.disconnect();
            clearTimeout(this._ackTimeout);
            if (this._documentClickHandler) {
                document.removeEventListener('click', this._documentClickHandler);
            }
            if (this._repositionHandler) {
                document.removeEventListener('scroll', this._repositionHandler, true);
                window.removeEventListener('resize', this._repositionHandler);
            }
        },

        toggle() {
            if (this._toggleBusy) return;
            if (this.$el.querySelector('.edz-select__trigger')?.disabled) return;
            this._toggleBusy = true;
            setTimeout(() => { this._toggleBusy = false; }, 150);

            // Closing is always allowed; opening is refused while the list is
            // loading so a rapid double-tap cannot fire a second fetch.
            if (this.open) {
                this.close();
                return;
            }
            if (this.loading) return;

            this.open = true;
            this.query = '';
            this.backendOptions = [];
            this.highlighted = this.allOptions.findIndex(o => o.value === this.selected);
            this.updatePosition();
            this.$nextTick(() => {
                if (this.searchable && this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
            this.ensureRemoteOptions();
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
            // A roundtrip (server-ack) select must not be re-picked while its
            // request is still in flight — that starts a second racing request.
            // Plain list selects (the modal cascade) are allowed: with the pick
            // landing in ONE deferred round-trip, the panel closes immediately
            // instead of swallowing the click while the lazy list is loading.
            if ((this.roundtrip && this.loading) || this._selectBusy) return;
            const opt = this.allOptions.find(o => o.value === value);
            if (opt?.isDivider) return;
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
                if (this.roundtrip) {
                    const current = input?.getAttribute('value');
                    if (current !== String(value)) {
                        this._waitServerAck(String(value));
                    }
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
                            value: String(r.value ?? r.id ?? r),
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
            const list = this.filteredOptions;
            let idx = this.highlighted + delta;
            while (idx >= 0 && idx < list.length && list[idx].isDivider) {
                idx += delta;
            }
            const max = list.length - 1;
            this.highlighted = Math.min(max, Math.max(0, idx));
        },

        selectHighlighted() {
            const opt = this.filteredOptions[this.highlighted];
            if (opt && !opt.isDivider) this.select(opt.value);
        }
    };
}
