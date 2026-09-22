// ============================================================
//  EDZEERY — LANDING BUNDLE (marketing pages)
//  ------------------------------------------------------------
//  Replaces the legacy resources/js/app.js (which dragged in
//  ApexCharts, flatpickr, FullCalendar, lucide — all unused).
//  Only what the landing shell actually uses lives here:
//    - Alpine     (navbar / FAQ x-collapse / billing toggle / contact)
//    - EdzSwal    (session flash bridge  -> data-sw blocks)
//    - Iconify    (payments logos        -> .iconify / data-icon)
//    - AOS        (scroll reveal         -> [data-aos])
// ============================================================

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import edzLoader from "./components/edz-loader.js";
import "./swal.js";
import "@iconify/iconify";
import initNativeButtonLoading from "./native-button-loading.js";

import AOS from "aos";
import "aos/dist/aos.css";

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.data("edzLoader", edzLoader);
Alpine.start();

document.addEventListener("DOMContentLoaded", () => {
    AOS.init({
        once: true,
        duration: 700,
        easing: "ease-out-cubic",
    });
});

initNativeButtonLoading();