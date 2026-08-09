# Livewire 3 + Volt — Conventions

Custom-panel UI in this repo is built with **Livewire 3 + Volt functional components**,
mounted from `app/Providers/VoltServiceProvider.php` on `resources/views/livewire`.

## Directory layout

```
resources/views/livewire/
├── layout/
│   ├── sidebar.blade.php      # <livewire:layout.sidebar />
│   └── topbar.blade.php       # <livewire:layout.topbar />
└── panel/
    ├── dashboard.blade.php    # GET /panel
    └── settings.blade.php     # GET /panel/settings
```

Routes are declared in `routes/panel.php` (loaded via `withRouting(then: …)` in
`bootstrap/app.php`) under the `auth,verified` middleware, name prefix `panel.`.

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
