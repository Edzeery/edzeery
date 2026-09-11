// Shared barcode-scan-input component (Phase 36 / Phase E.3).
// Dual-mode: hardware-scanner text input (in Blade) + camera modal (here).
// html5-qrcode is NEVER imported statically — only via dynamic import on first
// camera open, so every page that never opens the camera pays zero bytes.

const SAFE_METHOD_RE = /^[A-Za-z_][A-Za-z0-9_]*$/;
const REAR_RE = /rear|back|environment/i;
const DEBOUNCE_MS = 1500;

export default function barcodeScanInput(config = {}) {
    const rawScanMethod = config.scanMethod ?? "";
    const scanMethod = SAFE_METHOD_RE.test(rawScanMethod) ? rawScanMethod : null;

    return {
        cameraOpen: false,
        scanning: false,
        cameras: [],
        selectedCameraId: null,
        cameraError: "",

        _scanner: null,
        _Html5QrcodeClass: null,
        _Html5QrcodeSupportedFormats: null,
        _abortFlag: false,
        _lastDecodeAt: 0,

        /* ── public helpers ─────────────────────────────────── */

        get preferredCameraId() {
            if (this.selectedCameraId) return this.selectedCameraId;
            const rear = this.cameras.find((c) => REAR_RE.test(c.label || ""));
            return rear ? rear.id : null;
        },

        /* ── open / close camera ────────────────────────────── */

        async openCamera() {
            this.cameraError = "";
            this.cameraOpen = true;
            this._abortFlag = false;
            await this.$nextTick();

            // Lazy-load the library (creates a separate Vite chunk).
            if (!this._Html5QrcodeClass) {
                try {
                    const mod = await import("html5-qrcode");
                    this._Html5QrcodeClass = mod.Html5Qrcode;
                    this._Html5QrcodeSupportedFormats = mod.Html5QrcodeSupportedFormats;
                } catch {
                    this._setCameraError("camera_unavailable");
                    return;
                }
            }
            if (this._abortFlag) return;

            // Enumerate devices (may need permission).
            try {
                const devices = (await this._Html5QrcodeClass.getCameras()) || [];
                this.cameras = devices.map((d, i) => ({
                    id: d.id,
                    label: d.label || `Camera ${i + 1}`,
                }));
            } catch {
                // Permissions may still allow start() via facingMode.
                this.cameras = [];
            }
            if (this._abortFlag) return;

            if (this.cameras.length === 1) {
                this.selectedCameraId = this.cameras[0].id;
            }

            await this._startScanner();
        },

        async closeCamera() {
            this._abortFlag = true;
            await this._stopScanner();
            this.cameraOpen = false;
            this.scanning = false;
            // On a successful scan the modal unmounts via x-if before its own
            // open-watch runs, so restore the scroll-lock the modal set on open.
            document.body.style.overflow = "unset";
        },

        /* ── camera picker (re-renders only the <select>) ───── */

        async selectCamera(id) {
            if (id === this.selectedCameraId) return;
            this.selectedCameraId = id || null;
            await this._restartScanner();
        },

        /* ── scanner internals ──────────────────────────────── */

        async _startScanner() {
            if (this._scanner || this._abortFlag) return;
            if (!this._Html5QrcodeClass) return;
            if (!(await this._waitForContainer())) return;

            const container = this.$refs.cameraContainer;
            const scanner = new this._Html5QrcodeClass(container, /* verbose= */ false);
            this._scanner = scanner;

            const attempts = this._buildCameraAttempts();
            const config = this._buildScanConfig();

            for (const camConfig of attempts) {
                if (this._abortFlag) {
                    await this._safelyDestroy(scanner);
                    this._scanner = null;
                    return;
                }
                try {
                    await scanner.start(
                        camConfig,
                        config,
                        (text) => this._onDecoded(text),
                        () => {},
                    );
                    this.scanning = true;
                    this.cameraError = "";
                    return;
                } catch {
                    await this._safelyDestroy(scanner);
                }
            }

            // All attempts exhausted.
            this._scanner = null;
            this._setCameraError("camera_unavailable");
        },

        async _stopScanner() {
            const s = this._scanner;
            if (!s) return;
            this._scanner = null;
            await this._safelyDestroy(s);
        },

        async _restartScanner() {
            await this._stopScanner();
            this.scanning = false;
            if (this.cameraOpen) {
                await this.$nextTick();
                await this._startScanner();
            }
        },

        /* ── decoded handler ─────────────────────────────────── */

        _onDecoded(text) {
            const value = (text || "").trim();
            if (!value) return;

            const now = Date.now();
            if (now - this._lastDecodeAt < DEBOUNCE_MS) return;
            this._lastDecodeAt = now;

            if (!scanMethod || !this.$wire) return;

            // Stop camera + close modal, then call Livewire method.
            this.closeCamera().then(() => {
                if (typeof this.$wire[scanMethod] === "function") {
                    this.$wire[scanMethod](value);
                }
            });
        },

        /* ── config builders ─────────────────────────────────── */

        _buildCameraAttempts() {
            const id = this.preferredCameraId;
            if (id) return [{ deviceId: { exact: id } }];
            return [
                { facingMode: { exact: "environment" } },
                { facingMode: "environment" },
                { facingMode: "user" },
            ];
        },

        _buildScanConfig() {
            const F = this._Html5QrcodeSupportedFormats;
            return {
                fps: 10,
                qrbox: (vw, vh) => ({
                    width: Math.min(vw - 24, 340),
                    height: Math.min(vh - 160, 220),
                }),
                aspectRatio: 1.777,
                formatsToSupport: F
                    ? [F.CODE_128, F.CODE_39, F.EAN_13, F.EAN_8, F.QR_CODE]
                    : [],
            };
        },

        /* ── helpers ─────────────────────────────────────────── */

        async _waitForContainer() {
            // The camera viewport lives inside an x-if template; wait for
            // Alpine to mount it (and register the x-ref) before measuring.
            for (let i = 0; i < 20; i++) {
                if (this._abortFlag) return false;
                if (this.$refs?.cameraContainer) return true;
                await this.$nextTick();
            }
            return false;
        },

        async _safelyDestroy(scanner) {
            try { await scanner.stop(); } catch { /* not started */ }
            try { await scanner.clear(); } catch { /* ignore */ }
            try {
                if (this.$refs?.cameraContainer) {
                    this.$refs.cameraContainer.innerHTML = "";
                }
            } catch { /* ignore */ }
        },

        _setCameraError(key) {
            // Keep the modal open so the message stays visible (and the user
            // can still try another device from the picker); only mark the
            // error and stop.
            this.cameraError = key;
            this.scanning = false;
        },
    };
}
