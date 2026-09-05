const FLICKER_GUARD_MS = 150;
const PENDING_TTL_MS = 1500;
const CLICK_SELECTOR =
    "button[wire\\:click], .edz-btn[wire\\:click], button[wire\\:submit], .edz-btn[wire\\:submit]";

let lastClick = { el: null, at: 0 };
let active = new Map();

function methodOf(el) {
    if (!el || !el.isConnected) return null;
    let raw = el.getAttribute("wire:click") || el.getAttribute("wire:submit");
    if (!raw) {
        const form = el.closest("form[wire\\:submit]");
        if (form) raw = form.getAttribute("wire:submit");
    }
    if (!raw) return null;
    const match = /^\s*([A-Za-z_$][\w$]*)/.exec(raw);
    return match ? match[1] : null;
}

function wireCalls(body) {
    let payload = body;
    if (typeof payload === "string") {
        try {
            payload = JSON.parse(payload);
        } catch {
            return [];
        }
    }
    const calls = Array.isArray(payload?.components)
        ? payload.components.flatMap((c) => (Array.isArray(c?.calls) ? c.calls : []))
        : [];
    return calls.map((c) => c?.method).filter(Boolean);
}

function allButtons() {
    const direct = Array.from(document.querySelectorAll(CLICK_SELECTOR));
    const submitButtons = Array.from(
        document.querySelectorAll("form[wire\\:submit] button[type=submit]"),
    );
    return direct.concat(submitButtons.filter((el) => !direct.includes(el)));
}

function resolveButtons(methods) {
    const targets = new Set();
    const recent = Date.now() - lastClick.at <= PENDING_TTL_MS;
    for (const method of methods) {
        if (!method) continue;
        const clicked =
            recent && lastClick.el && lastClick.el.isConnected ? lastClick.el : null;
        if (clicked && methodOf(clicked) === method) {
            targets.add(clicked);
            continue;
        }
        const matches = allButtons().filter((el) => methodOf(el) === method);
        if (matches.length === 1) targets.add(matches[0]);
    }
    return targets;
}

function activate(element) {
    if (active.has(element)) return;
    element.dataset.edzPending = "1";
    active.set(element, {
        timer: null,
        shown: false,
        hidden: [],
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

function showState(element, state) {
    if (state.shown) return;
    state.shown = true;
    element.dataset.edzLoading = "1";
    element.classList.add("edz-btn--loading");
    if (element instanceof HTMLButtonElement) {
        element.disabled = true;
    } else {
        element.setAttribute("aria-disabled", "true");
    }
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
    if (state.label) {
        if (state.originalAriaLabel) element.setAttribute("aria-label", state.originalAriaLabel);
        else element.removeAttribute("aria-label");
    }
    if (element instanceof HTMLButtonElement) {
        element.disabled = state.originalDisabled;
    } else {
        element.removeAttribute("aria-disabled");
    }
    if (state.originalAriaBusy) element.setAttribute("aria-busy", state.originalAriaBusy);
    else element.removeAttribute("aria-busy");
}

function interceptClick(event) {
    const source = event.target && event.target.closest ? event.target : null;
    const button =
        (source && source.closest(CLICK_SELECTOR)) ||
        (source && source.closest("form[wire\\:submit]")
            ? source.closest("form[wire\\:submit]").querySelector("button[type=submit]")
            : null);
    if (!button) return;
    if (button.dataset.edzPending) {
        event.preventDefault();
        event.stopImmediatePropagation();
        return;
    }
    lastClick.el = button;
    lastClick.at = Date.now();
}

function interceptSubmit(event) {
    const form =
        event.target && event.target.closest ? event.target.closest("form[wire\\:submit]") : null;
    if (!form) return;
    if (form.querySelector(".edz-btn--loading")) {
        event.preventDefault();
        event.stopImmediatePropagation();
        return;
    }
    const submitter =
        (event.submitter && event.submitter.nodeType === 1 ? event.submitter : null) ||
        form.querySelector("button[type=submit], .edz-btn");
    if (submitter && submitter.isConnected) {
        lastClick.el = submitter;
        lastClick.at = Date.now();
    }
}

function bindRequests() {
    if (typeof window.Livewire === "undefined") return;
    window.Livewire.hook("request", ({ payload, succeed, fail }) => {
        if (!payload) return;
        const methods = wireCalls(payload);
        if (!methods.length) return;
        const targets = resolveButtons(methods);
        if (!targets.size) return;
        targets.forEach((element) => activate(element));
        let settled = false;
        const finish = () => {
            if (settled) return;
            settled = true;
            targets.forEach((element) => deactivate(element));
        };
        succeed(finish);
        fail(finish);
    });
}

export function initButtonLoading() {
    document.addEventListener("click", interceptClick, true);
    document.addEventListener("submit", interceptSubmit, true);
    document.addEventListener("livewire:navigated", () => {
        for (const element of Array.from(active.keys())) deactivate(element);
        lastClick.el = null;
    });
    if (typeof window.Livewire !== "undefined") {
        bindRequests();
    } else {
        document.addEventListener("livewire:initialized", bindRequests, { once: true });
    }
}

export default initButtonLoading;