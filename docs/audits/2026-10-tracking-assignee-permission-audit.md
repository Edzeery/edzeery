# Phase AUDIT-2026-10 — Tracking Assignee Permission Integrity (read-only diagnostic)

- **Date:** 2026-10-08
- **Repo:** `Edzeery/edzeery`
- **Scope:** read-only. No production data modified; no migration/cleanup script written. This file is the only artifact created.
- **Environment:** Windows/Laragon; default `php` is 8.2.12 (incompatible — `composer.json` requires `php ^8.3`). All commands used `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe` (PHP 8.3.28) against local MySQL DB `edzeery`.
- **Question:** Are there `order_trackings.assigned_to_membership_id` rows pointing at a membership that does **not** currently hold `CRM_ORDER_TRACKING`? The three live write paths (`OrderTrackingAssignmentService::assign()`, `TrackingReassignConcern::submitTrackingReassign()`, `OrderTrackingService::startShipment()`) are already guarded, so the hypothesis was stale/historical data.

---

## Method

Permission is resolved exactly as `App\Models\Stores\Team\StoreMembership::can()` does (`app/Models/Stores/Team/StoreMembership.php:99-109`):

1. Custom per-membership permissions from `store_membership_permissions` — **if the list is non-empty it is authoritative** (`in_array` check).
2. Otherwise fall back to `$user->hasPermissionTo($permission, 'merchant')` (Spatie, guard `merchant`, `teams=false`; covers role-derived and direct user perms).

Because branch 2 is role/Spatie-derived (not a stored column), the decision was evaluated in PHP once per distinct assignee membership via the real model method (script: temp/opencode/tracking_assignment_diag.php), over the structural join below.

**Reference SQL (structural join only):**

```sql
SELECT
    t.id AS tracking_id,
    t.order_id,
    t.store_id,
    t.assigned_to_membership_id,
    m.role AS assigned_role,
    m.is_active AS assigned_is_active,
    m.deleted_at AS assigned_deleted_at,
    u.email AS assigned_user_email,
    m.user_id AS assigned_user_id,
    GROUP_CONCAT(DISTINCT smp.permission ORDER BY smp.permission SEPARATOR ',') AS assigned_custom_permissions,
    t.assignment_method,
    t.assigned_at,
    t.assigned_by_membership_id,
    t.created_at,
    t.created_by_membership_id
FROM order_trackings t
LEFT JOIN store_memberships m ON m.id = t.assigned_to_membership_id
LEFT JOIN users u ON u.id = m.user_id
LEFT JOIN store_membership_permissions smp ON smp.membership_id = m.id
WHERE t.assigned_to_membership_id IS NOT NULL
GROUP BY t.id, m.id, u.id
ORDER BY t.store_id, t.created_at;
```

---

## Results

### Totals

| Metric | Count |
|---|---|
| `order_trackings` rows total | 7 |
| Rows with `assigned_to_membership_id` set | 7 |
| Rows where assignee does **NOT** hold `CRM_ORDER_TRACKING` | **1** |

### Matching rows grouped by store

| store_id | Store | count | assignment_methods |
|---|---|---|---|
| `01m4b2entvj9f7apdxcjd0xt00` | Edzeery Demo Store | 1 | `auto` × 1 |

Only 1 of 3 stores has any tracking rows; the entire dataset lives in the demo store. The mismatch is **isolated to a single row**.

### The matching row (full detail)

| Field | Value |
|---|---|
| `tracking_id` | `01m4b2eqb7h448rq2az9gkqz5k` |
| `order_id` | `01m4b2eqayr9bvtz8em4trkgmb` |
| `store_id` | `01m4b2entvj9f7apdxcjd0xt00` (Edzeery Demo Store) |
| `assigned_to_membership_id` | `01m4b2epqjh0vvsxd6z7y4vjvt` |
| — user email | `demo.confirmer@edzeery.com` |
| — role / status | `staff` / ACTIVE |
| — custom perms (stored, authoritative) | `inventory.view, order.confirm, order.status.manage.own, order.view, returns.process, returns.verify.barcode, stats.confirmation` |
| — holds `CRM_ORDER_TRACKING`? | **NO** (not in stored list; list is non-empty so no Spatie fallback) |
| `assignment_method` | `auto` |
| `assigned_at` | 2026-09-27 15:00:00 |
| `assigned_by_membership_id` | `01m4b2env4tfzk67nfpj4zkf67` (`demo@edzeery.com`, owner) |
| `created_at` (tracking row) | 2026-09-26 09:00:00 |
| `created_by_membership_id` | **NULL** |
| assignee == creator? | No — `created_by_membership_id` is NULL, so the literal "assignee is the shipper" symptom is **not** reproduced; the practical symptom (confirmation-only member shown as tracking assignee) is. |

### Context — the other 6 assigned rows (all valid)

| tracking_id | assignee | method | created_at |
|---|---|---|---|
| `01m4b2eqb7h448rq2az9gkqz5k` | **demo.confirmer@edzeery.com** | auto | 2026-09-26 — *the only offender* |
| `01m4b2eqamx3vw2897tnavx2g9` | demo.tracker@edzeery.com | auto | 2026-09-28 |
| `01m4b2eq9x5p1gfx7cy4bq522r` | demo.tracker@edzeery.com | auto | 2026-09-30 |
| `01m4b2eqc934e80ch5njtb1bwm` | demo.dual@edzeery.com | auto | 2026-10-01 |
| `01m4b2eqcvwe20j7csbwb29aqe` | demo.tracker@edzeery.com | auto | 2026-10-01 |
| `01m4b2eq9fantve3v3s6sa3kzw` | demo.tracker@edzeery.com | auto | 2026-10-02 |
| `01m4b2eqbwwyrw7hgqd3xbvnj9` | demo.tracker@edzeery.com | auto | 2026-10-04 |

`demo.tracker` and `demo.dual` hold `CRM_ORDER_TRACKING`; `demo.confirmer` does not (consistent with `DemoSeederPermissionParityTest` at `tests/Feature/Merchant/DemoSeederPermissionParityTest.php:39`).

---

## Root cause (not a live write path)

The offender is **synthetic demo-seeder data**, not a historical live write:

- `database/seeders/DemoStoreSeeder.php:1517` — `seedOrderTracking()` sets `$tracking->assigned_to_membership_id = $assignTo?->id`, where `$assignTo` is the **confirmation** assignee passed from `seedOrder()` (the order spec `assign_to` = `demo.confirmer@edzeery.com`, confirmed server-side only for the confirm flow).
- The demo seeder never routes through `resolveCandidatePool()` / `submitTrackingReassign()` / `startShipment()`, so the `CRM_ORDER_TRACKING` guard is bypassed by construction in seed data.
- `:1520` — `assignment_method` copies `$order->assignment_method` (the confirmation method), which is why the row is marked `auto` even though the tracking assignment path could never produce this.
- Row `created_at` 2026-09-26 predates the membership row itself (`store_memberships.created_at` 2026-10-07), further confirming fabricated demo data, not an operational write.

The seeding intent was UI realism — demo.confirmer processed the return (`history` shows `returned` by `demo.confirmer`) and the tracking tab displays them as the assignee — but this contradicts the permission-parity design (the tracking page requires `CRM_ORDER_TRACKING`).

---

## Conclusion / recommended next-pass scope

- **Symptom is confirmed but limited to 1 demo row.** The hypothesis "stale rows from before the guards existed" is **not** supported — the row is seed data written directly by the seeder.
- No live-code path can currently produce a non-`CRM_ORDER_TRACKING` assignee; the fix should target the **seeder** (`DemoStoreSeeder::seedOrderTracking()`) to assign a `demo.tracker`/`demo.dual` membership to tracking rows (or set `assignment_method`/assignee only from a tracking-eligible pool), not production data.
- If real (non-demo) data must be checked, the guard test above is the reference — it returned zero non-demo offenders.
- **No data was modified and no migration/cleanup script was written in this pass.**