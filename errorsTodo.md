# Frontend Audit — errorsTodo.md

> Last updated: 2026-08-31 (Session 6 — Fine-grained authorization enforcement P1–P3)

---

## ✅ Session 6 — Fine-grained Authorization Enforcement (P1–P3)

Closes the coarse-permission gaps: gated a business action only by the state machine, or by
the wrong permission, instead of the permission that actually governs the action.

### P1 — Order status transitions (`$transitionOrder`)
- [x] **`orders/index.blade.php`** — `$transitionOrder` now gated by
      `abort_unless(canStore(StoreOrderPermissions::forStatus($statusKey)), 403)`
      (previously only the state-machine `canTransition` check ran → any page-viewer could ship).
- [x] **`app/Support/StoreOrderPermissions.php`** (new) — `forStatus()` maps:
      confirm bucket (`confirmed`, `preparing`, `on_hold`) → `order.confirm`;
      cancel bucket (`cancelled`, `rejected`, `no_answer_1/2/3`, `wrong_number`,
      `out_of_stock`, `duplicate`, `postponed`) → `order.cancel`;
      everything else (ship/deliver/return-followup) → `order.manage`.
- [x] **`$bulkAssignAgent`** — guard switched from `order.manage` → `order.assign`.

### P2 — MANAGER access to the teams page
- [x] **`teams/index.blade.php`** — `mount()` accepts `TEAM_VIEW` **or** `TEAM_VIEW_OWN`.
- [x] **`helpers.php`** — `canManageTeam()` also accepts `TEAM_MANAGE_OWN`.
- [x] **`StoreMembershipPolicy`** — `viewAny`/`view` accept `TEAM_VIEW_OWN`; `create` accepts `TEAM_MANAGE_OWN`.
- [x] Result: MANAGER opens the page and manages only their own team (`invited_by`).

### P3 — Role templates (`StoreRoles.php`)
- [x] **STAFF** — removed `order.manage`; added `order.cancel`, `crm.orders.confirm`,
      `returns.verify.barcode`, `stats.confirmation` (10 perms).
- [x] **MANAGER** — added `returns.verify.barcode`, `returns.process`, `stats.confirmation`,
      `stats.delivery`; kept `order.manage`, `order.assign`, `store.update` (28 perms).
- [x] **ADMIN** — unchanged: keeps the four sovereignty exclusions (36 of 40 perms).

### Verification
- [x] `Seeder` re-run: owner 40 / admin 36 / manager 28 / staff 10; only demo `default-store`
      memberships re-synced — custom perms on other stores are not wiped.
- [x] `tests/Feature/Merchant/StoreAuthorizationGatesTest.php` (5 tests, 18 assertions) green.
- [x] `RoleScopingTest` + order/return/storefront suites green (44 tests this pass).

### Deferred (not in scope this pass)
- **P4 — Tracking feature** → separate session. `STATS_TOP_KPIS`/`STATS_DELIVERY` dashboard
  guards remain unassigned to roles (no UI change here). `blade-interactivity` policy test
  still fails on pre-existing `@js` in the orders blade (unchanged file region).

---

## ✅ Session 5 — Store Roles & Permissions (RBAC, per-store isolation)

### Core RBAC (decision #6 — hybrid membership-scoped)
- [x] **Per-store role** — `store_memberships.role` via new migration `2026_08_31_205611_add_role_to_store_memberships_table`
- [x] **Per-store custom permissions** — new `store_membership_permissions` pivot table (`2026_08_31_205445`)
- [x] **`StoreMembership`** — `role` fillable/cast + scoped `can/hasRole/isOwner/isAdmin/isManager/permissionNames/hasPermission/syncPermissions/permissions()`
- [x] **`StoreTeamService`** — `addMember`/`updateMember` store role + sync permissions per membership; `removeMember` cleans the pivot
- [x] **`helpers.php`** — `canStore()`/`hasStoreRole()` check current membership first, fallback to Spatie global
- [x] **Tests** — `tests/Feature/Merchant/RoleScopingTest.php` (2 tests, per-store isolation + confirm-only staff)

### UI per role
- [x] **Sidebar (R2)** — permission flags (`canViewTeam/canViewOrders/canViewOrderSettings/canViewStoreSettings/canViewStorefront`) + hide empty groups
- [x] **Dashboard (R3)** — widgets gated by `STATS_TOP_KPIS/STATS_DELIVERY/ORDER_CONFIRM/CRM_ORDER_CONFIRMATION/INVENTORY_VIEW/PRODUCT_VIEW`; tables row collapses to one column
- [x] **Team matrix (R4)** — STAFF role now shows the permissions matrix; adds a "custom" badge for perms outside the role template

### Bug fixed
- [x] **Blade `$__empty_-1` compile error** in `storefront/order-form.blade.php` — caused by an unbalanced `@forelse`/`@empty`/`@if` directive in an editor view; resolved after reconstruction, all views compile clean under `view:cache`

### N+1
- [x] **`lowStockVariants()`** — eager-load `optionValues` (was lazy-loaded per variant inside the dashboard loop)

---

## ✅ Session 4 — CSS Foundation + Apple Design Account Pages

### CSS Foundation (4 fixes)
- [x] **SVG Icon Bug** — `icon.blade.php` computed `$fill` but never applied → added `fill="none" stroke="currentColor"` directly to `<svg>` element
- [x] **Tailwind Config** — `surface-*` and `ink-*` missing numeric scales (50–950) → added full numeric scales matching gray palette
- [x] **Personal Data** — broken `bg-surface-300 dark:bg-ink-600` toggles + `border-surface-200 dark:border-ink-700` radios → fixed to `bg-gray-300 dark:bg-gray-600` / `border-gray-200 dark:border-gray-700`
- [x] **Billing** — undefined `text-ink-400` → `text-ink-soft`

### Apple Design Account Pages (4 redesigns)
- [x] **Account Profile** — full Apple redesign: avatar header, sectioned form with icon badges, `max-w-3xl` centered
- [x] **Account Billing** — full Apple redesign: current plan card with status badge, store billing list with avatar + badges
- [x] **Personal Data** — full Apple redesign: icon-card headers per section (language, appearance, notifications), proper toggle switches
- [x] **Stores Index** — full Apple redesign: header + card list layout with gradient avatars, arrow chevron, stat grids

### Audit
- [x] **Merchant view audit** — verified all `dark:bg-gray-*`, `dark:border-gray-*` in merchant views are valid TW3 utilities; no issues found

---

## ✅ Session 3 — New Features

- [x] **Contact Us page** — Full Apple-design page with form, contact info, social links, AOS animations, RTL/Dark, 4 languages (en/ar/fr/es)
- [x] **Topbar** — Replaced custom dark toggle with `<x-dark-toggle />`, added `<x-lang-switcher />`
- [x] **Product Carousel** — Upgraded from simple x-show to full carousel: nav arrows, keyboard (← →), swipe gestures, lightbox, counter, thumbnail scroll, smooth transitions
- [x] **Broken #contact links** — Fixed navbar, footer, final-cta to use `route('contact')`
- [x] **Missing translations** — Added 21 contact keys + 5 footer keys to all 4 languages

---

## ✅ Session 2 — Bug Fixes (26/29)

### CRITICAL (4/4 fixed)
- [x] **C1** storefront-dropdown — zero dark mode → dark:bg-gray-800
- [x] **C2** order-form — broken focus:ring → focus:ring-brand-500/20 + inline var
- [x] **C3** app.scss vs app.css — conflicting vars → aligned to app.css values
- [x] **C4** landing-layout — missing dark init → added init script + dark body classes

### HIGH (7/8 fixed)
- [x] **H1** lang-switcher — zero dark mode → full dark support
- [x] **H2** storefront hover — darkens in dark → brightness(1.2) in dark
- [x] **H3** CTA white text — invisible on light primary → JS luminance auto-contrast
- [x] **H4** FAQ accordion — missing aria-expanded → added
- [x] **H5** Flash dismiss — missing aria-label → added
- [x] **H6** dropdown — raw gray-800 → dark:bg-dark-surface
- [x] **H7** dark-toggle — missing aria-label → added
- [ ] **H8** app-header — raw gray-* → SKIPPED (admin panel)

### MEDIUM (8/10 fixed)
- [x] **M1** store-* classes dark hover → via H2 fix
- [ ] **M2** Dual dark mode system → SKIPPED (architectural)
- [x] **M3** Auth pages — missing dark:hover:text-brand-300 → added
- [x] **M4** navbar — missing dark:text-error-400 → added
- [x] **M5** nav-link — active state missing dark: → dark:bg-brand-500
- [x] **M6** single-product CTA — bg-white no dark: → dark:bg-gray-100
- [x] **M7** input-error — missing role="alert" → added
- [x] **M8** navbar hamburger — missing aria → added
- [x] **M9** text-red-500 — missing dark: → added everywhere
- [x] **M10** catalog/brand selects — missing bg-white → added

### LOW (6/7 fixed)
- [x] **L1** plans.blade — hardcoded hex → dark:bg-dark-surface
- [x] **L2** faq.blade — different hex → dark:bg-dark-secondary
- [x] **L3** storefront-lang-switcher — min-h-[40px] → h-10
- [x] **L4** storefront-dropdown — rounded-xl → intentional (kept)
- [x] **L5** guest.blade — missing aria-hidden → added
- [x] **L6** brand.blade cover — alt="" → alt="" role="presentation"
- [ ] **L7** storefront dark toggle — inline onclick → SKIPPED (works fine)

---

## Status Summary

| Category | Total | Fixed | Skipped |
|----------|-------|-------|---------|
| Session 2 Critical | 4 | 4 | 0 |
| Session 2 High | 8 | 7 | 1 |
| Session 2 Medium | 10 | 8 | 2 |
| Session 2 Low | 7 | 6 | 1 |
| **Session 3 Features** | **5** | **5** | **0** |
| **TOTAL** | **34** | **30** | **4** |
