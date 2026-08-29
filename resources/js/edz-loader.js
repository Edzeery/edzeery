// ============================================================
//  EDZEERY — GLOBAL LOADER ENTRY (shared by every layout)
//  ------------------------------------------------------------
//  Self-contained bundle: loader CSS + the edzLoader Alpine
//  component. Register on `alpine:init`; wherever Livewire loads
//  Alpine (panel, auth, storefront) the component becomes
//  available. On Alpine-less pages the pre-parse CSS cover in
//  <x-edz.global-loader> is released by its no-Alpine fallback.
// ============================================================
import "../css/components/_loader.scss";
import edzLoader from "./components/edz-loader.js";

document.addEventListener("alpine:init", () => {
    if (!window.Alpine) return;
    window.Alpine.data("edzLoader", edzLoader);
});