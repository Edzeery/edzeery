import Alpine from "alpinejs";
import "./bootstrap.js";

window.Alpine = Alpine;

document.addEventListener("alpine:init", () => {
    const theme = Alpine.store("theme", {
        theme:
            localStorage.getItem("edz-theme") ||
            (window.matchMedia?.("(prefers-color-scheme: dark)").matches
                ? "dark"
                : "light"),
        toggle() {
            this.theme = this.theme === "dark" ? "light" : "dark";
            localStorage.setItem("edz-theme", this.theme);
            this.apply();
        },
        apply() {
            document.documentElement.classList.toggle(
                "dark",
                this.theme === "dark",
            );
        },
    });

    // Shared shell state so the topbar trigger can open the sidebar
    // even though both are separate Livewire components.
    Alpine.store("shell", {
        open: false,
        collapsed: localStorage.getItem("edz-sidebar-collapsed") === "1",
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            localStorage.setItem(
                "edz-sidebar-collapsed",
                this.collapsed ? "1" : "0",
            );
        },
    });

    theme.apply();
});

Alpine.start();
