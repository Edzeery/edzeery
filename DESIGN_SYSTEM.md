# Edzeery Design System

## TailAdmin Component Reuse Pattern

TailAdmin is used as the **structural and component source** for all admin-style UI. It is re-skinned through Edzeery's own design tokens — never used with its default palette.

### Rule: Default to TailAdmin components

When a new UI need arises (a table, a chart, a form layout, a card, a modal), the default should be to **adapt the matching TailAdmin component** into the relevant domain's Livewire views rather than hand-rolling new markup. Only deviate when a genuine Edzeery-specific UX requirement makes that impossible.

### Rule: Always use `--edz-*` tokens

Every adapted component must use Edzeery's design tokens (`--edz-*` via Tailwind classes like `bg-brand-500`, `text-ink`, `bg-surface-bg`, etc.) — never raw TailAdmin default Tailwind color classes. The token system is defined in:

- **Single source of truth**: `resources/css/abstracts/_variables.scss` (SCSS variables like `$edz-brand-500`)
- **Generated CSS custom properties**: `resources/css/design-system/_tokens.scss` (reads from `_variables.scss`)
- **Tailwind mapping**: `tailwind.config.js` (uses `token()` helper referencing `--edz-*` variables)

### Rule: Status/badge colors go through `mystatuskit`

Status and badge colors must always go through `edzeery/mystatuskit` (`resources/css/components/_mystatuskit.scss`), consistent with the color-system work in Section 1 of `Todos.md`. The safelist in `tailwind.config.js` ensures runtime-generated status classes (`text-green-700`, `bg-red-100`, etc.) are available.

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
| `ink-*` | `$edz-color-text` etc. | Semantic text colors |
| `surface-*` | `$edz-color-surface` etc. | Semantic surface colors |

### What was removed (Section 1 cleanup)

All ~17 TailAdmin demo/showcase pages and their backing routes were removed from `routes/web.php` and `resources/views/pages/`. These were the template's own marketing pages, not adapted product UI:

- Dashboard demo (`/welcome`), Calendar (`/calendar`), Form Elements (`/form-elements`), Basic Tables (`/basic-tables`), Blank Page (`/blank`), Error 404 demo (`/error-404`), Charts (`/line-chart`, `/bar-chart`), Auth demos (`/signin`, `/signup`), UI Elements (`/alerts`, `/avatars`, `/badge`, `/buttons`, `/image`, `/videos`), Profile demo (`/pages/profile`)

Demo-only components were also removed: `calender-area`, `ui/button`, `ui/alert`, `ui/badge`, `ui/avatar`, `ui/youtube-embed`, `common/common-grid-shape`, and all `form/form-elements/*` sub-components.

**Preserved** (used by live product views): `common/page-breadcrumb`, `common/component-card`, `common/dropdown-menu`, `common/table-dropdown`, `common/preloader`, `ui/modal`, `profile/*`, `ecommerce/*`, `layouts/*`.
