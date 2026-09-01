# Merchant Panel Audit — Roles & Permissions (RBAC)

> Scope: merchant store panel roles, permissions, sidebar, and role-based dashboard.
> Last verified: 2026-08-31 (Session 5 + enforcement P1–P3 pass).

## Decision #6 — Hybrid per-store isolation (NOT literal Spatie Teams)

Spatie Teams was **not** activated (`config/permission.php` → `teams => false`) because
`stores.id` and `users.id` are ULID (`char(26)`) while the Spatie teams pivot migration
uses `unsignedBigInteger`. The 4 store roles are fixed templates that don't need
duplication per store, and existing code is built on `guard_name='merchant'`.

Instead, per-store isolation lives on `store_memberships`:

- `store_memberships.role` → the member's role **within this store** (`StoreRoleEnum` value).
- `store_membership_permissions` → pivot (`membership_id` ULID FK + `permission`), the
  member's **custom permissions within this store**.

### Resolution order
`canStore($permission)` / `hasStoreRole($role)` (in `app/Helpers/helpers.php`):
1. No user / no store context → `false`.
2. Platform `super_admin`/`admin` (web guard) → `true` (bypass).
3. If the current membership has a non-empty `permissionNames()` set → only those apply
   (isolation guaranteed across stores).
4. Otherwise fall back to Spatie global (`$user->can($permission, 'merchant')`).

## Fine-grained Authorization Enforcement (P1–P3)

Closes the gaps where a page/sidebar was gated by a coarse permission while the actual
*business action* was never checked (or checked against the wrong permission).

- **P1 – `$transitionOrder`** (`orders/index.blade.php`): previously the transition was only
  guarded by the state machine (`canTransition`), never by permission — any member with page
  access could drive shipping/follow-up. Now the target status maps to a fine-grained
  permission via `App\Support\StoreOrderPermissions::forStatus($statusKey)` and the action is
  gated with `abort_unless(canStore(...), 403)`. Confirmation → `order.confirm`,
  cancellation → `order.cancel`, everything else (ship/deliver/prepare/return-followup) → `order.manage`.
- **P1 – `$bulkAssignAgent`**: guard switched from `order.manage` to `order.assign`.
- **P2 – teams page for MANAGER** (`teams/index.blade.php`): `mount()` now accepts
  `TEAM_VIEW` **or** `TEAM_VIEW_OWN`; `canManageTeam()` (helpers.php) now also accepts
  `TEAM_MANAGE_OWN`. `StoreMembershipPolicy::viewAny`/`view` accept `TEAM_VIEW_OWN`, and
  `create` accepts `TEAM_MANAGE_OWN`. MANAGER sees/edits only their own team (`invited_by`).
- **P3 – role templates** (`StoreRoles.php`): STAFF loses `order.manage` and gains
  `order.cancel`, `crm.orders.confirm`, `returns.verify.barcode`, `stats.confirmation`;
  MANAGER gains the same returns/stats perms plus `stats.delivery` (keeps `order.manage`,
  `order.assign`, and `store.update`). ADMIN keeps the four sovereignty exclusions.

Decisions locked: **40** permissions in 11 groups (not 35); ADMIN keeps its 4 exclusions;
`store.update` stays on MANAGER (storefront-settings stays reachable). P4 (tracking feature)
is deferred to a separate session — not implemented here.

## Files touched (this enforcement pass)

| File | Change |
|------|--------|
| `app/Enums/Store/StorePermissionEnum.php` | 40 permissions / 11 groups (source of truth) |
| `app/Support/StoreRoles.php` | 4 role templates |
| `app/Helpers/helpers.php` | `canStore`/`hasStoreRole` membership-scoped |
| `app/Models/Stores/Team/StoreMembership.php` | `role` + scoped `can`/methods + `permissions()` |
| `app/Models/Stores/Team/StoreMembershipPermission.php` | new pivot model |
| `app/Services/Stores/StoreTeamService.php` | store role + perms per membership |
| `database/seeders/StoreRolesAndPermissionsSeeder.php` | backfills `role` + membership perms |
| `resources/views/livewire/layout/store-sidebar.blade.php` | permission flags + hide empty groups |
| `resources/views/livewire/merchant/dashboard.blade.php` | widgets gated by permission |
| `resources/views/livewire/merchant/teams/index.blade.php` | STAFF matrix + custom badge |
| `resources/lang/{ar,en,fr,es}/teams.php` | `custom_badge` translation |
| `app/Support/StoreOrderPermissions.php` | NEW: `forStatus()` maps status → fine-grained permission (P1) |
| `resources/views/livewire/merchant/orders/index.blade.php` | `$transitionOrder` gated by `forStatus()`; `$bulkAssignAgent` → `order.assign` |
| `resources/views/livewire/merchant/teams/index.blade.php` | `mount()` accepts `TEAM_VIEW_OWN` (P2) |
| `app/Helpers/helpers.php` | `canManageTeam()` accepts `TEAM_MANAGE_OWN` (P2) |
| `app/Policies/StoreMembershipPolicy.php` | `viewAny`/`view`/`create` accept scoped team perms (P2) |

## Migrations
- `2026_08_31_205611_add_role_to_store_memberships_table` (column `role`)
- `2026_08_31_205445_create_store_membership_permissions_table` (pivot)

Both are `--force` migrated locally.

## Scope guards (untouched by design)
- Order confirmation logic (COD) — untouched.
- Inventory / ATP — untouched.
- Filament SuperAdmin — untouched.
- `resources/views/filament/merchant/pages/store-dashboard.blade.php` /
  `store/team.blade.php` — orphaned Filament views left behind (deferred per decision #10B).

## Known pre-existing (NOT from this work)
- `tests/Unit/BladeInteractivityPolicyTest.php` fails on `@js(...)` inside `x-data` in
  `resources/views/livewire/merchant/orders/index.blade.php` (lines ~1581, ~1902) — a file
  this RBAC work does not modify.
- `dark:` theme-tuning on the dashboard was intentionally left as-is in this session.

## Tests
- `tests/Feature/Merchant/RoleScopingTest.php` — 2 tests (19+ assertions):
  - isolates custom permissions per store membership (two stores, two roles)
  - confirm-only STAFF resolves scoped permissions (sidebar/dashboard flags)
- `tests/Feature/Merchant/StoreAuthorizationGatesTest.php` — 5 tests (18 assertions):
  - `StoreOrderPermissions::forStatus()` mapping (confirm/cancel/manage buckets)
  - confirm-only STAFF can confirm but is blocked from shipping (P1)
  - owner can ship/cancel (P1 positive)
  - MANAGER opens the teams page (P2)
  - confirm-only STAFF stays 403 on the teams page (P2 negative)
- Full merchant suite (40+5 tests this pass) + storefront (91 tests) green. Whole suite:
  202 passed, 1 pre-existing policy failure (below).
