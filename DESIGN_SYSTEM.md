# Edzeery Design System

> Last updated: 2026-08-20

## 1. Apple Design System (Storefront)

All storefront pages follow Apple's design language:

### Core Principles
- **Clarity**: Clean typography with Inter + IBM Plex Sans Arabic
- **Deference**: Content-first, minimal chrome
- **Depth**: Layered surfaces with `backdrop-blur`, shadows, and z-index hierarchy

### Design Tokens (CSS Custom Properties)

| Token | Light | Dark | Usage |
|-------|-------|------|-------|
| `--color-surface-bg` | `#f9fafb` | `#0b0f19` | Page background |
| `--color-surface-primary` | `#ffffff` | `#101828` | Card backgrounds |
| `--color-surface-secondary` | `#f3f4f6` | `#1d2939` | Secondary surfaces |
| `--color-neutral-border` | `#e4e7ec` | `#344054` | Borders |
| `--color-ink` | `#101828` | `#f2f4f7` | Primary text |
| `--color-ink-muted` | `#667085` | `#98a2b3` | Secondary text |
| `--color-brand` | `#465fff` | `#7592ff` | Brand/accent color |

### Component Patterns

| Component | Classes |
|-----------|---------|
| Cards | `rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm` |
| Buttons | `rounded-xl px-6 py-3 font-semibold transition-all duration-200` |
| Inputs | `rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 px-4 py-3 focus:ring-2 focus:ring-brand-500/20` |
| Modals | `backdrop-blur-sm bg-black/50` overlay + `rounded-3xl` panel |
| Icons | Ionicons 7.1.0 (web component) |

### Store-Specific Theming
Each store defines its own primary/secondary colors via `StoreThemeSetting`:
- `--store-primary` / `--store-secondary` / `--store-font` (CSS vars in `<style>`)
- Auto-contrast text: JS luminance calculator sets `--store-btn-text` (black/white)
- Classes: `store-btn-primary`, `store-text-primary`, `store-bg-primary`, `store-border-primary`

### Dark Mode
- Toggle key: `localStorage edz-theme` → `dark` / `light`
- Init script in `<head>` to prevent FOUC
- `.dark` class on `<html>` triggers Tailwind `dark:` variants
- All semantic tokens have `.dark` overrides

### RTL Support
- `dir="rtl"` on `<html>` when Arabic is active
- LTR/RTL-aware alignment: `ltr:origin-top-left rtl:origin-top-right`
- Flex direction auto-adjusts

---

## 2. TailAdmin Component Reuse (Admin Panel)

TailAdmin is used as the **structural and component source** for all admin-style UI. It is re-skinned through Edzeery's own design tokens — never used with its default palette.

### Rule: Default to TailAdmin components

When a new UI need arises (a table, a chart, a form layout, a card, a modal), the default should be to **adapt the matching TailAdmin component** into the relevant domain's Livewire views rather than hand-rolling new markup.

### Rule: Always use `--edz-*` tokens

Every adapted component must use Edzeery's design tokens (`--edz-*` via Tailwind classes like `bg-brand-500`, `text-ink`, `bg-surface-bg`, etc.) — never raw TailAdmin default Tailwind color classes.

### Available token scales

| Tailwind class prefix | Token source | Purpose |
|---|---|---|
| `brand-*` | `$edz-brand-*` | Primary action color (blue scale) |
| `accent-*` | `$edz-accent-*` | Secondary emphasis (indigo scale) |
| `success-*` | `$edz-success-*` | Positive/success states |
| `warning-*` | `$edz-warning-*` | Warning/caution states |
| `danger-*` | `$edz-danger-*` | Error/danger states |
| `info-*` | `$edz-info-*` | Informational states |
| `gray-*` | `$edz-gray-*` | Neutral gray scale |
| `ink-{50–950}` | `$edz-color-text` etc. | Semantic text colors (includes numeric scales) |
| `surface-{50–950}` | `$edz-color-surface` etc. | Semantic surface colors (includes numeric scales) |

---

## 3. Frontend Architecture

### CSS Entry Points
- `resources/css/app.css` — Storefront/guest (TW3, no SCSS)
- `resources/css/app.scss` — Admin panel (TW3/PostCSS + SCSS 7-1)

### JavaScript Entry Points
- `resources/js/app.js` — Alpine data components (panel/guest)
- `resources/js/storefront.js` — Storefront-specific (imports bootstrap.js + swal.js)
- `resources/js/panel.js` — Alpine store "theme" with `edz-theme` localStorage

### Key Components
| File | Purpose |
|------|---------|
| `components/edz/icon.blade.php` | SVG icon component — `fill="none" stroke="currentColor"` (auto-inherits text color) |
| `components/layouts/storefront.blade.php` | Store layout: header, footer, dark init, CSS vars, cart toast |
| `components/layouts/landing-layout.blade.php` | Landing page layout |
| `components/layouts/guest.blade.php` | Auth pages layout |
| `components/storefront-dropdown.blade.php` | Reusable dropdown with dark mode |
| `components/storefront-lang-switcher.blade.php` | Store-specific language switcher |
| `components/lang-switcher.blade.php` | Admin/guest language switcher |
| `components/dark-toggle.blade.php` | Dark mode toggle button |

---

## 4. Removed Components

All ~17 TailAdmin demo/showcase pages and their backing routes were removed from `routes/web.php` and `resources/views/pages/`. Demo-only components were also removed.

**Preserved** (used by live product views): `common/page-breadcrumb`, `common/component-card`, `common/dropdown-menu`, `common/table-dropdown`, `common/preloader`, `ui/modal`, `profile/*`, `ecommerce/*`, `layouts/*`.
