# Livewire 3 + Volt — Conventions

> Last updated: 2026-08-20

Custom-panel UI in this repo is built with **Livewire 3 + Volt functional components**,
mounted from `app/Providers/VoltServiceProvider.php` on `resources/views/livewire`.

## Directory layout

```
resources/views/livewire/
├── layout/
│   ├── sidebar.blade.php      # <livewire:layout.sidebar />
│   └── topbar.blade.php       # <livewire:layout.topbar />
├── panel/
│   ├── dashboard.blade.php    # GET /panel
│   └── settings.blade.php     # GET /panel/settings
├── storefront/
│   ├── order-form.blade.php   # Checkout wizard (3-step)
│   ├── product-detail.blade.php # Product page with carousel
│   ├── mini-cart.blade.php    # Slide-in sidebar cart
│   ├── catalog.blade.php      # Product listing
│   └── templates/
│       ├── single-product.blade.php
│       ├── catalog.blade.php
│       └── brand.blade.php
└── settings/
    └── storefront.blade.php   # Store settings page
```

## Functional-component API (current Volt version)

Use the **function** API imported at the top of each file — the old `$layout = '...'`
variable assignment is **not** supported by this Volt release.

```blade
<?php

use function Livewire\Volt\layout;
use function Livewire\Volt\with;
use function Livewire\Volt\state;
use function Livewire\Volt\rules;

layout('components.layouts.panel');          // full-page layout
title('Dashboard');                           // optional <title>

state(['store_name' => '']);                  // reactive state

rules(['store_name' => ['required', 'string']])->messages([
    'store_name.required' => 'Required.',
]);

// A closure becomes a public action callable from the template.
$save = function (): void {
    $this->validate();
    session()->flash('panel.saved', 'Saved.');
};

// Plain data exposed to the template. NOTE: arbitrary top-level variables
// (e.g. `$menu = …`) are captured at compile time but are NOT passed to
// the view — always use `with()` (or `computed()`/`state()`).
with([
    'menu' => config('panel.menu'),
    'user' => auth()->user(),
]);
?>
```

## Rules of thumb

- Every file under `resources/views/livewire/` is a component; name = relative path
  (e.g. `layout/sidebar.blade.php` → `<livewire:layout.sidebar />`).
- Full-page Volt routes are rendered through `components.layouts.panel`
  (registered by calling `layout(...)` in the component).
- `auth:verified` is enforced at the route group level — do not re-check inside pages.
- The topbar/sidebar are separate components; shared shell state (drawer open,
  collapsed) and theme live in **Alpine stores** (`$store.shell`, `$store.theme`)
  defined in `resources/js/panel.js`, so any component can read/toggle them.
- Icons come from `<x-edz.icon name="…" />` (inline SVGs, no external icon lib).
- Status badges: `<span class="edz-badge edz-badge--{success|warning|danger|…}">`.

## Storefront conventions

### Routing
- All storefront routes prefixed with `storefront.` name prefix
- Routes in `routes/storefront.php` with middleware: `['web', 'resolve.store', 'store.locale']`
- Store detected from subdomain via `resolve.store` middleware
- Store-specific locale validated by `store.locale` middleware

### Livewire Volt critical rules
1. **`state()` only** — `$props` does NOT work in Volt functional components
2. **Serializes ALL public properties** — paginators become arrays on re-render. Initialize in `mount()` only.
3. **`computed()` does NOT expose to Blade** — use `@php $var = $this->computedMethod @endphp` instead
4. **`wire:click` must NOT use `$wire.` prefix** — causes `$wire.$wire.method` double-proxy error
5. **`@js()` for Alpine** — use `wire:click="addToCart(@js($item->id))"` for passing PHP to JS

### Dark mode
- localStorage key: `edz-theme` (unified across all layouts)
- Init script in `<head>` to prevent FOUC:
  ```html
  <script>
    (localStorage.getItem('edz-theme') === 'dark' ||
     (!localStorage.getItem('edz-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches))
      && document.documentElement.classList.add('dark');
  </script>
  ```
- Toggle: `<x-dark-toggle />` component
- Store front classes: `store-btn-primary`, `store-text-primary`, `store-bg-primary`, etc.
- Auto-contrast: JS luminance calculator sets `--store-btn-text` (black/white)

### Translation keys
- Storefront: `resources/lang/{locale}/storefront.php` (ar/fr/en/es)
- Landing: `resources/lang/{locale}/landing.php` (ar/fr/en/es)
- Panel: `resources/lang/{locale}/panel.php` + `resources/lang/{locale}/buttons.php`
- General: `resources/lang/{locale}/general.php`

### CSS architecture
- Two entry points (TW3):
  - `resources/css/app.css` — Storefront/guest (no SCSS)
  - `resources/css/app.scss` — Admin panel (SCSS 7-1)
- CSS custom properties in `:root` and `.dark` for semantic tokens
- Store-specific vars injected via `<style>` from `StoreThemeSetting` model

### Component patterns
- Storefront dropdown: `<x-storefront-dropdown>`
- Language switcher (admin): `<x-lang-switcher>`
- Language switcher (store): `<x-storefront-lang-switcher>`
- Dark toggle: `<x-dark-toggle>`
- Product images: `ProductImage` polymorphic model (MorphMany), `asset('storage/' . $path)`
- Mini-cart: `CartService` session-based, sidebar slide-in pattern
