import "./bootstrap";
import "./swal.js";
import "@iconify/iconify";

import ApexCharts from "apexcharts";

// flatpickr
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
// FullCalendar
import { Calendar } from "@fullcalendar/core";

window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

document.addEventListener("alpine:init", () => {
    window.Alpine.data("edzDirty", () => ({
        dirty: false,
        _snapshot: "",
        _formEl: null,

        init() {
            this._formEl = this.$el.closest("form");
            if (!this._formEl) return;
            this._snapshot = this._serialize();
            this._formEl.addEventListener("input", () => {
                this.dirty = this._serialize() !== this._snapshot;
            });
            this._formEl.addEventListener("reset", () => {
                this.$nextTick(() => {
                    this._snapshot = this._serialize();
                    this.dirty = false;
                });
            });
            window.addEventListener("beforeunload", (e) => {
                if (this.dirty) {
                    e.preventDefault();
                    e.returnValue = "";
                }
            });
            document.addEventListener("livewire:navigating", () => {
                this.dirty = false;
            });
            this.$el.addEventListener("livewire:updated", () => {
                this.$nextTick(() => {
                    this._snapshot = this._serialize();
                    this.dirty = false;
                });
            });
        },

        _serialize() {
            const fd = new FormData(this._formEl);
            const entries = [];
            for (const [key, value] of fd.entries()) {
                if (value instanceof File) {
                    entries.push(`${key}=${value.name}:${value.size}`);
                } else {
                    entries.push(`${key}=${value}`);
                }
            }
            return entries.join("&");
        },

        markClean() {
            this._snapshot = this._serialize();
            this.dirty = false;
        },

        confirmLeave(callback) {
            if (!this.dirty) {
                callback();
                return;
            }
            EdzSwal.unsavedChanges(() => {
                this.dirty = false;
                callback();
            });
        },
    }));
});

import AOS from "aos";
import "aos/dist/aos.css";

import * as lucide from 'lucide';

window.LucideIcons = lucide;
// Initialize components on DOM ready
document.addEventListener("DOMContentLoaded", () => {
    AOS.init({
        once: true,
        duration: 700,
        easing: "ease-out-cubic",
    });

    // Map imports
    if (document.querySelector("#mapOne")) {
        import("./components/map").then((module) => module.initMap());
    }

    // Chart imports
    if (document.querySelector("#chartOne")) {
        import("./components/chart/chart-1").then((module) =>
            module.initChartOne(),
        );
    }
    if (document.querySelector("#chartTwo")) {
        import("./components/chart/chart-2").then((module) =>
            module.initChartTwo(),
        );
    }
    if (document.querySelector("#chartThree")) {
        import("./components/chart/chart-3").then((module) =>
            module.initChartThree(),
        );
    }
    if (document.querySelector("#chartSix")) {
        import("./components/chart/chart-6").then((module) =>
            module.initChartSix(),
        );
    }
    if (document.querySelector("#chartEight")) {
        import("./components/chart/chart-8").then((module) =>
            module.initChartEight(),
        );
    }
    if (document.querySelector("#chartThirteen")) {
        import("./components/chart/chart-13").then((module) =>
            module.initChartThirteen(),
        );
    }

    // Calendar init
    if (document.querySelector("#calendar")) {
        import("./components/calendar-init").then((module) =>
            module.calendarInit(),
        );
    }
});

