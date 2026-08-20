# Frontend Audit — errorsTodo.md

> Last updated: 2026-08-20 (Session 4)

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
