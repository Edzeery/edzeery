# ORDER DISTRIBUTION RULES (single source of truth)

Canonical rules for who may receive an auto-assigned order/tracking, when an
assignment may change, and what the merchant sees in the distribution queue.
Implemented and verified in PHASE 35.2-C + PHASE 35.3 (+ earlier phases).

## 0) Roles & scopes

Two independent pipelines, mirrored end to end:

| Scope | Assignable row | Role permission | Open-set definition |
| --- | --- | --- | --- |
| `confirm` | `orders` | `ORDER_CONFIRM` | statuses `distribution_stage` = confirmation (or NULL status.stage-like custom status) |
| `track` | `order_trackings` | `CRM_ORDER_TRACKING` | `tracking_status` in `OrderTrackingStatus::open()` |

A row is *eligible* to be auto-assigned only while it is inside its scope's open
set; rows that leave the open set are never touched again by the engine.

## 1) Candidate eligibility (every selection pass)

A store membership is a reachable candidate only when ALL hold:

1. `is_active = true`.
2. The role permission for the scope (`ORDER_CONFIRM` / `CRM_ORDER_TRACKING`) —
   checked via the membership's **granted** permission rows (`can()`), not the
   role label.
3. **On an active shift** of the same role scope at the snapshot instant
   (`ShiftAvailabilityResolver`; covering shifts / per-shift caps apply).
4. **Visibility**: the membership's product visibility scope covers every
   product on the order (`StoreProductScopeService::filterByVisibility`).

The same three-way test (active + permission + on-shift) defines whether an
already-assigned row KEEPS its assignee during the shift sweep (see §6).

## 2) Product-ownership routing (R1–R3)

Source: `confirmation_product_assignments` rows of the store for the scope
(`role_scope`). One batched query per call.

- **R1 Ownership authority.** A product is *owned* when at least one
  role-scoped assignment row belongs to an active membership that currently
  holds the scope permission. Shift, cap and visibility are irrelevant to
  ownership itself.
- **R2 Owned order.** If the order owns any of its products, the candidate pool
  is exactly the eligible candidates assigned to at least one owned product.
  Unowned products ride along. **No fallback outside the pool**: if nobody in
  it is eligible the order waits (capacity alert) — even for overflow or
  handover. Coverage (count of owned products per candidate) is the primary
  ranking key within the pool.
- **R3 Owned nothing.** If the order owns no product, the pool is the eligible
  candidates with **zero** role-scoped assignment rows in the store ("general"
  members). When every eligible candidate owns something, the pool widens to
  every eligible candidate.

Anything outside the routed pool is never assigned to this order.

## 3) Selection & ranking (inside the routed pool)

Ranking per candidate (used everywhere): **most owned products (coverage), then
fewest open assignments, then oldest last assignment**. Candidates on shift and
under cap compete; complete ties are scanned in a shuffled order so no member
has a positional advantage over time.

- **Open assignment counts** (`openAssignmentCounts`): for `confirm`, orders
  joined to statuses where `distribution_stage` = confirmation or NULL; for
  `track`, trackings with open tracking status. Both count only rows with a
  non-null assignee.
- **Soft overflow**: when store settings enable `distribution_overflow_enabled`
  (positive `distribution_overflow_percentage`), a first pass respects base
  caps; only when no eligible candidate is within base caps do effective caps
  grow to `ceil(cap * (1 + pct/100))` and the selection repeats. A selection
  that required overflow sets `over_capacity = true` on the row.

## 4) Concurrency & crash-safety

- Every engine mutation (auto-assign, manual reassign, both shift sweeps) runs
  inside `GuardsDistributionLock` — a Redis-style per-store plus per-role-scope
  serialization with a bounded wait. A lock timeout leaves the row untouched
  and the next scheduled sweep retries it.
- Each piped sweep is a single job per store:
  `ShiftHandoverJob` implements `ShouldBeUniqueUntilProcessing` with
  `uniqueId() = "shift-handover:{store_id}"` and `uniqueFor = 600`, so a burst
  of shift/membership saves coalesces into one sweep per store while different
  stores stay independent.

## 5) Manual reassignment

`reassign()` bypasses every eligibility check, verifies the target belongs to
the same store, writes `assignment_method = 'manual'`, records the acting
membership, and clears `stranded_at` (an item leaving the queue loses its
stranded state even back-office). Auto paths write `assignment_method = 'auto'`;
the sweep writes `'handover'`.

## 6) Shift-boundary sweep & the stranded lifecycle

On shift boundaries `HandlesShiftHandover` (confirm) / `HandlesTrackingHandover`
(track) sweep the scope's open, assigned rows...

1. **Batched stale-flag pass (P35.3):** one UPDATE clears `stranded_at` on
   rows whose status left the scope's open set (orders: `distribution_stage`
   set and ≠ confirmation; trackings: status no longer open) — attention isn't
   needed anymore, and is performed ONCE per sweep, not per row.
2. **Keep/strand/replace loop.** For each assigned open row the assignee's
   `eligibleKeepMap` result decides:
   - **Keep**: active + holds the scope permission + on an active shift of the
     scope. If the row had been stranded, `stranded_at` is cleared (the assignee
     is eligible again) and the row leaves the queue.
   - **Replace**: best on-shift candidate via the same routed-pool selection;
     writes `assignment_method = 'handover'`, resets over-capacity flag, clears
     `stranded_at`.
   - **Strand**: no eligible replacement exists → the assignment is KEPT (never
     dropped to no-owner) and `stranded_at` is recorded exactly once. Later
     sweeps retry; the flag clears itself as soon as a replacement appears or
     the row leaves the open set.
3. Eligibility for keeping is computed in batch (memberships + permissions +
   timezone settings, then ONE `ShiftAvailabilityResolver` snapshot) — no
   per-row shift queries.

## 7) Distribution queue (merchant UI)

`DistributionQueueConcern` surfaces, per tab, only rows needing attention:

```
assigned_to_membership_id IS NULL
  OR stranded_at IS NOT NULL
  OR over_capacity = true
```

scoped to the tab's open set (non-terminal order statuses / open tracking
statuses). Ordering is three-tier then chronological:

1. **Unassigned** `stranded_at` — oldest first; unassigned sorts by
   `created_at` asc.
2. **Stranded** — by `stranded_at` asc (unassigned's `created_at` acts as its
   key in the same CASE).
3. **Over-capacity** — assigned rows, flagged `over_capacity = true`; the CASE
   key falls back to `created_at`.

Each tab paginates 50/page; badges show total matches from a lightweight count;
free-text search filters by order/tracking number and customer.

## 8) Status-classification source (`statuses.distribution_stage`)

`OrderDistributionStage` buckets order-status keys (single source of truth):

- **confirmation** — `pending`, `no_answer_1..3`, `postponed`, `on_hold`, plus
  any unknown/custom store status (defaults to confirmation). Counts as open
  confirmable load; the sweep may reassign.
- **fulfillment** — `confirmed`, `preparing`, `shipped`, `in_transit`,
  `out_for_delivery`, `unclaimed`, `undeliverable`. Never touched by the sweep.
- **closed** — `draft`, `wrong_number`, `duplicate`, `out_of_stock`,
  `cancelled`, `canceled`, `delivered`, `returned`, `completed`, `refunded`,
  `paid` (financially closed even when it still ships). Never touched.

Deliberately separate from `statuses.stage` (PHASE 38-C compensation
bucketing); never reused as a second domain list. Rows with a NULL
`distribution_stage` are treated as confirmation.

## 9) Explicit non-goals (out of scope, by decision)

Assignment ledger, customer-sticky routing, agent break toggle, FIFO dispatch,
fairness dashboard, unassigned-reason codes, no-answer rotation, and changes to
overflow %, notifications, visibility-guard semantics, `membership_product_scopes`,
`statuses.stage`, the 38-C program, `confirmation_shifts` structure, or PHASE
35.1-A shift behavior are deliberately NOT part of these rules.

## 10) Key files

- Routing: `app/Domains/Order/Support/ProductOwnershipRouter.php`
- Classification: `app/Domains/Order/Support/OrderDistributionStage.php`
- Availability/caps: `app/Domains/Order/Support/ShiftAvailabilityResolver.php`
- Selection: `app/Domains/Order/Concerns/ResolvesCapacityBalancedCandidates.php`
- Lock: `app/Domains/Order/Concerns/GuardsDistributionLock.php`
- Sweeps: `app/Domains/Order/Concerns/HandlesShiftHandover.php`,
  `app/Domains/Order/Concerns/HandlesTrackingHandover.php`
- Sweep job: `app/Domains/Order/Jobs/ShiftHandoverJob.php`
- Services: `app/Domains/Order/Services/OrderAssignmentService.php`,
  `app/Domains/Order/Services/OrderTrackingAssignmentService.php`
- Queue: `app/Livewire/Concerns/DistributionQueueConcern.php`
- Tests: `tests/Feature/Order/OrderAssignmentServiceTest.php`,
  `tests/Feature/Order/EngineHardeningTest.php`,
  `tests/Feature/Merchant/OrderDistributionQueueTest.php`,
  `tests/Feature/Merchant/OrderSettingsShiftRoleTest.php`

## 11) Attribution vs assignment (PHASE 35.4 rules — documented, code pending approval)

Two orthogonal notions. The dashboard team table separates them; a credit
bucket is **never** keyed by an assignment column:

| Notion | Key | Meaning | Changes when | Used for |
| --- | --- | --- | --- | --- |
| Work (roster) | `orders.assigned_to_membership_id` / `order_trackings.assigned_to_membership_id` | who currently carries the row | reassign / handover / sweep (never a credit) | queue, shifts, "assigned" (المُسند) column |
| Credit (confirmation) | `orders.confirmed_by_membership_id` | who confirmed the order — the dispatcher to delivery | never after first write (38-C) | `confirmed` / `delivered` / `returned` / revenue in BOTH tabs + KPIs + 38-D settlements |
| Tracking actor | `order_trackings.created_by_membership_id` → planned `tracked_by_membership_id` | who acted to start the shipment | never (snapshot) | future Phase 38-F tracking-team tab only |

Rules:

1. **Work is never credit.** Delivery outcomes are the outcomes of the orders a
   member **confirmed**, even when the tracking rows belong to a different
   member (`confirmed_by_membership_id` is the single credit key).
2. **No `delivered_by`.** The delivery outcome of an order is attributed to its
   confirmer by definition; the only per-order human attribution is
   `confirmed_by`.
3. **"No credit" ≠ "unassigned".** UI keeps two distinct labels: «بلا رصيد»
   (unattributed, `confirmed_by IS NULL` — shown as its own row and selectable
   in the member filter) vs «غير مُسنَدة» (no work, assignment NULL — a queue
   concept).
4. **Dual-role member** (holds `ORDER_CONFIRM` + `CRM_ORDER_TRACKING`): may be
   scheduled in both shifts and appear in both tabs; each tab counts only its
   own cohort; no row is ever double-counted within one tab; tab credits are
   never summed as a single balance.
5. **Rates are activity rates** with explicit denominators: conversion =
   `confirmed ÷ assigned(work)`; delivery = `delivered ÷ confirmed`;
   return = `returned ÷ (delivered + returned)`.
6. **Dashboard team table.** Confirmation tab columns: assigned / pending /
   canceled / other (work key) + confirmed / delivered / returned (credit key),
   conversion rate. Delivery tab columns: assigned (work key) + delivered /
   returned / in-progress / revenue (credit key), delivery & return rates.
   The delivery tab is labelled as the **outcomes of the orders the member
   confirmed** («مصير طلبياته المرسَلة»), not the tracker's ledger.
7. **Snapshot captures unchanged** (38-C): `confirmed_at`/`confirmed_by`,
   the tracking actor and `cod_amount`. No new actor column is introduced.

### Deferred — documented, NOT implemented (decision: revisit later, only if needed)

Rename `order_trackings.created_by_membership_id` → `tracked_by_membership_id`
so it can never be confused with `orders.created_by_membership_id` (who
*entered* the order). Would land as a new migration dated AFTER
`2026_10_06_000004`, plus `FinancialCaptureSchemaTest` (`--step` booking),
`FinanceCaptureHealth`, `OrderTrackingService::startShipment`,
`OrderStatusCaptureTest`, the demo seeder, and the docs. Bundled with the
deferred actor-threading of `CarrierOrderPostService::postToCarrier` (today the
carrier path writes the row with a NULL tracking actor). Awaiting
Phase 38-F.