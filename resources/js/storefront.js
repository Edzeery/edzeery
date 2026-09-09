import "./bootstrap";
import "./swal";
import storefrontSelect from "./components/storefront-select.js";

// Searchable dropdowns on public storefront pages (checkout cascade): the
// storefront bundle stays Lightweight — this tiny component is registered
// only here, never in the panel bundle.
if (window.Alpine) {
    window.Alpine.data("storefrontSelect", storefrontSelect);
} else {
    document.addEventListener("alpine:init", () => window.Alpine?.data("storefrontSelect", storefrontSelect), {
        once: true,
    });
}
