# Merchant Panel Audit — Roles & Permissions (RBAC)

> Scope: merchant store panel roles, permissions, sidebar, and role-based dashboard.
> Last verified: 2026-08-31 (Session 5).

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

## Files touched

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
- Full merchant suite (40 tests) + storefront (91 tests) green. Whole suite: 202 passed,
  1 pre-existing policy failure (above).
