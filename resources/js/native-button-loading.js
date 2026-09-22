// ============================================================
//  EDZEERY — NATIVE BUTTON LOADING
//  Custom ring spinner (.edz-spinner) for NON-Livewire buttons:
//    - any native <form> submit button (auth, create-store)
//    - navigation CTAs tagged with [data-edz-loading]
//  Livewire buttons are handled by edz-button-loading.js.
//  Reuses the .edz-btn--loading / .edz-btn__ring / .edz-btn__hide
//  CSS contract (top-level rules in _buttons.scss).
// ============================================================

const FLICKER_GUARD_MS = 150;

const active = new Map();

function showState(element, state) {
    if (state.shown) return;
    state.shown = true;
    element.dataset.edzLoading = "1";
    element.classList.add("edz-btn--loading");
    element.disabled = true;
    element.setAttribute("aria-busy", "true");
    if (state.label) element.setAttribute("aria-label", state.label);

    const width = element.getBoundingClientRect().width;
    if (width > 0) element.style.minWidth = `${Math.round(width)}px`;

    for (const node of Array.from(element.childNodes)) {
        if (node.nodeType === 1) {
            node.classList.add("edz-btn__hide");
            state.hidden.push({ el: node });
        } else if (node.nodeType === 3 && node.textContent.trim()) {
            const wrap = document.createElement("span");
            wrap.className = "edz-btn__hide";
            node.parentNode.replaceChild(wrap, node);
            wrap.appendChild(node);
            state.hidden.push({ wrap, text: node });
        }
    }

    const ring = document.createElement("span");
    ring.className = "edz-btn__ring";
    ring.setAttribute("aria-hidden", "true");
    ring.innerHTML =
        '<svg class="edz-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><circle cx="12" cy="12" r="9" stroke-dasharray="42.41 56.55" /></svg>';
    element.appendChild(ring);
}

function deactivate(element) {
    const state = active.get(element);
    if (!state) return;
    clearTimeout(state.timer);
    active.delete(element);
    if (!element.isConnected) return;

    delete element.dataset.edzPending;

    if (!state.shown) return;

    const ring = element.querySelector(".edz-btn__ring");
    if (ring) ring.remove();
    for (const item of state.hidden) {
        if (item.wrap && item.wrap.isConnected && item.text) {
            item.wrap.parentNode.insertBefore(item.text, item.wrap);
            item.wrap.remove();
        } else if (item.el && item.el.isConnected) {
            item.el.classList.remove("edz-btn__hide");
        }
    }
    delete element.dataset.edzLoading;
    element.classList.remove("edz-btn--loading");
    element.style.minWidth = "";
    element.disabled = false;
    if (state.label) {
        if (state.originalAriaLabel) element.setAttribute("aria-label", state.originalAriaLabel);
        else element.removeAttribute("aria-label");
    }
    if (state.originalAriaBusy) element.setAttribute("aria-busy", state.originalAriaBusy);
    else element.removeAttribute("aria-busy");
}

function activate(element) {
    if (active.has(element)) return;
    element.dataset.edzPending = "1";
    active.set(element, {
        timer: null,
        shown: false,
        label: (element.textContent || "").trim().slice(0, 80),
        originalAriaLabel: element.getAttribute("aria-label"),
        originalDisabled: element.disabled,
        originalAriaBusy: element.getAttribute("aria-busy"),
    });
    const state = active.get(element);
    state.timer = window.setTimeout(() => {
        if (!active.has(element)) return;
        showState(element, state);
    }, FLICKER_GUARD_MS);
}

function bindSubmit() {
    document.addEventListener(
        "submit",
        (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.hasAttribute("x-data")) return;
            if (form.closest("[wire\\:submit]")) return;
            const submitter =
                (event.submitter && event.submitter.matches("button") ? event.submitter : null) ||
                form.querySelector("button[type=submit], button[data-edz-submit]");
            if (!submitter || submitter.classList.contains("edz-btn--loading")) return;
            activate(submitter);
        },
        true,
    );
}

function bindLoadingLinks() {
    document.addEventListener(
        "click",
        (event) => {
            const link = event.target && event.target.closest
                ? event.target.closest("a[data-edz-loading]")
                : null;
            if (!link) return;
            if (link.classList.contains("edz-btn--loading")) {
                event.preventDefault();
                event.stopImmediatePropagation();
                return;
            }
            activate(link);
        },
        true,
    );
}

export function initNativeButtonLoading() {
    bindSubmit();
    bindLoadingLinks();
}

export default initNativeButtonLoading;