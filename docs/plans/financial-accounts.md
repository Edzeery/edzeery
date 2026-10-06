# Financial Accounts — Master Plan (Series 38)

**Recorded:** 2026-10-05
**Commit at time of writing:** `929569d54ac8c2464aaaa5ecc5b3a1aabf87c217`
**37-K (Unified status groups + per-view charts) status:** Implemented 2026-10-06 (work uncommitted, see phase log). `App\Domains\Analytics\Support\DashboardStatusGroups` is now the single source of status lists (D2 semantics, KPI/team table only, never payroll); `charts.blade.php` and `StoreDashboardAnalyticsService` were rebuilt on top of it (`statusBreakdown()` + `trendSeries()`). Header line kept honest here rather than in 38-A.

> Anything below marked **[VERIFY]** was written from planning, not from reading the repo. During 38-A the agent must confirm or correct each one and update this file.

---

## 1. Purpose and principles

- **Compute once, at the source.** Never two calculators for the same number. If Edzeery and finance-manager both need a figure, one side computes it and the other consumes it.
- **Append-only ledger.** Corrections are reversing entries, never edits or deletes. Closed periods are immutable.
- **Rule snapshot at event time.** Rates, plan versions, and policy settings that were in force when an event happened are snapshotted with the entry, so later plan edits never change history.
- **Money is decimal/integer only, never float.** Day boundaries use `stores.timezone`.
- **Explainability.** Every merchant-facing number must be explainable — a "how was this computed" drill-down is required, not optional.

## 2. Settled decisions (owner-approved, do not change without asking)

- **S1** Each store = one finance-manager workspace, API-linked.
- **S2** Confirmation-team members: the merchant chooses per member at add/edit time — pay per **CONFIRMED** order, or per **CONFIRMED-AND-DELIVERED** order.
- **S3** Tracking-team members: the merchant chooses per member — pay per **TRACKED** order, or per **TRACKED-AND-DELIVERED** order.
- **S4** Return handling is decided by the merchant, fully flexible per store (with optional per-member override).
- **S5** New dashboard sub-page named **"الحسابات المالية"** with tabs: نظرة عامة، سجل حساب الأرباح، محاسبة فريق التأكيد، محاسبة فريق التتبع، حسابات التوصيل، تنظيم مدير الأعمال.
- **S6** **"تنظيم مدير الأعمال"** = expenses (fixed/recurring), suppliers and purchases, cash accounts (cash, CCP/BaridiMob, bank), budgets, partners and profit sharing, tasks and reminders.
- **S7** The product is meant to be a complete professional all-in-one platform; improvements beyond this list are welcome but must be proposed in this plan file before implementation.

## 3. Ownership split (source of truth)

| Entity | Owner |
| --- | --- |
| orders, statuses, tracking, members | Edzeery |
| member compensation plans | Edzeery |
| earning entries (accrual ledger) | Edzeery (generated at event time) |
| payments, advances, deductions, bonuses | finance-manager |
| expenses, cash accounts, carrier remittance reconciliation | finance-manager |
| balances (earned minus paid) | finance-manager (computed in one place only) |

- Edzeery pushes earning entries to finance-manager through an **outbox with idempotency keys**; finance-manager stores them read-only.
- Edzeery's **"الحسابات المالية"** page reads balances from the finance-manager API and **degrades gracefully** (last cached balances + banner) when the API is unavailable.
- Advanced reports and zakat open in finance-manager's own UI via SSO.
- **Do NOT build interim payment/payroll tables in Edzeery**: payments are built once, in finance-manager.

## 4. Compensation model

- Plan **per member**, versioned with `effective_from` — history is never overwritten.
- **Trigger:** `confirmed | confirmed_and_delivered` (confirmation role); `tracked | tracked_and_delivered` (tracking role).
- **Formula:** fixed amount per order | percentage of order value | tiers; optional base salary; optional overrides per product / carrier / wilaya.
- **Return policy** (store default + per-member override): no deduction | full deduction | percentage deduction | deduct only when the return reason is marked as member fault | grace window of N days before a commission becomes final. A return after payment creates a **negative entry** applied to the next payout.
- **Store finance settings:** accrual start date, return policy default, grace window, payroll period.
- **Earning triggers live in a SEPARATE group from the dashboard "confirmed" group.** DashboardStatusGroups D2 is for performance KPIs only; add an `earningTriggers` group and **never** reuse `confirmed` for payroll.

## 5. Edge cases (each needs an explicit rule and a test)

- Attribution (`confirmed_by`, `tracked_by`) is **snapshotted at the event**; manual reassignment creates adjustment entries.
- Confirm → cancel → re-confirm = **one** commission. Two members confirming the same order = **first one earns**.
- Manual change of status to "delivered" is **flagged**; the delivery entry is finalized only by owner confirmation or a carrier update.
- Status moved back from delivered = **automatic reversing entry**.
- Test/duplicate orders are **flagged and excluded**.
- Payroll uses **event date (`occurred_at` from status history)**, not `orders.created_at`; orders delivered in a later month belong to that later month.
- `order_trackings` may have **several rows per order** (non-unique index): count once.

## 6. Page structure "الحسابات المالية" (six tabs from S5)

1. **نظرة عامة (Overview):** expected COD cash, accrued earnings, approximate net profit, alerts.
2. **سجل حساب الأرباح (Profit ledger):** revenue − returns − product cost − shipping (both ways) − team commissions − ads − expenses = net profit, plus per-order and per-product profitability.
3. **محاسبة فريق التأكيد (Confirmation team):** per member orders, confirmed, delivered, returned, earned, paid, balance; filters: date, product, carrier, member, wilaya, status.
4. **محاسبة فريق التتبع (Tracking team):** same member-level columns and filters as the confirmation tab.
5. **حسابات التوصيل (Delivery accounts):** per carrier COD collected, delivery and return fees, remittances received, reconciliation, overdue, disputes.
6. **تنظيم مدير الأعمال (Business-manager organization):** the S6 items — expenses, suppliers/purchases, cash accounts, budgets, partners/profit sharing, tasks/reminders.

## 7. Additions (approved for planning)

- Payslip PDF per member and a member portal showing own earnings with an **objection button**.
- **Rules simulator**: preview the impact of a plan change on last month without applying it.
- **Confirmer quality indicator** (return rate per confirmer) with optional quality bonuses.
- **Cash-flow forecast** from each carrier's remittance delay.
- **Product cost history** with dates.
- **Ad spend** with a manually entered exchange rate.
- **Data-quality board** (orders without product cost, delivered orders without carrier remittance, mismatches).
- **Full audit log and granular permissions** (view, manage, pay, close period).
- **Alerts** (return spikes by carrier or wilaya, late COD remittance, abnormal status-edit activity).
- **CSV/Excel/PDF exports** matching on-screen numbers exactly.

## 8. Phase tracker

| ID | Phase | Depends on | Status | Verified on (date, commit) | Notes |
| --- | --- | --- | --- | --- | --- |
| 37-K | Unified status groups + per-view charts | — | In progress - code + tests verified (checks a-e, g); check f pending owner visual pass | 2026-10-06 (work uncommitted) | See phase log entry below; header note corrected: `DashboardStatusGroups` now exists |
| 38-A | Audit: existing payroll/accounting modules; does `order_status_histories` store the acting user and exact timestamp **[VERIFY]**; who creates tracking rows and how **[VERIFY]**; which attribution fields exist **[VERIFY]** | 37-K | Not started | — | Findings must be written back into this plan |
| 38-B | Member compensation plan + member add/edit form + store finance settings | 38-A | Not started | — | Needs open decisions D2, D3, D5 |
| 38-C | Capture missing attribution/event fields (`confirmed_by`, `tracked_by`, `delivered_at`, ...) if 38-A finds gaps | 38-A | Not started | — | Only if 38-A confirms gaps |
| 38-D | Earning ledger + backfill from accrual start date + tests | 38-B, 38-C | Not started | — | Needs open decisions D1, D4 |
| 38-E | "الحسابات المالية" page shell, navigation, permissions, Overview tab | 38-D | Not started | — | |
| 38-F | Confirmation-team accounting, then tracking-team accounting | 38-E | Not started | — | |
| 37-L | Conversion funnel | 37-K | Not started | — | |
| 37-N | Carriers and wilayas comparison | 37-K | Not started | — | |
| 38-G | Delivery accounts and remittance reconciliation | 38-F | Not started | — | |
| 38-H | Profit ledger (product cost, ad spend) = 37-O | 38-E | Not started | — | |
| 38-I | Business-manager organization | 38-E | Not started | — | |
| 38-J | finance-manager workspace provisioning + API v1 + outbox push + reconciliation command | 38-D | Not started | — | Start only after finance-manager's own audit remediation is finished |
| 38-K | finance-manager UI: payroll runs, payments, advances, period close | 38-J | Not started | — | |
| 38-L | Payslips, member portal, objections, exports, alerts (= 37-R, 37-S) | 38-K | Not started | — | |
| 37-M | Time analytics (its history part is needed earlier for event dates) | 37-K | Not started | — | Later as needed; pull the history part forward if 38-D requires it |
| 37-P | (reserved) | 37-K | Not started | — | Later as needed |
| 37-Q | (reserved) | 37-K | Not started | — | Later as needed |

## 9. Verification gate (MANDATORY, applies to every phase)

Before a phase may be marked Done, the agent must do ALL of the following and record the evidence in the phase's log entry in this file:

a. Re-read this plan and the phase's own TASKS and ACCEPTANCE; tick each item with evidence (file/line, test name, command output summary).
b. `php artisan test` passes (state the counts); `./vendor/bin/pint --test` passes on touched files.
c. Size limits respected (PHP classes ≤ 250 lines, services ≤ 250, Volt ≤ 400, partials ≤ 300); record before/after sizes of touched files.
d. `git diff` is limited to what the phase declares; list any deviation and why.
e. Accuracy checks relevant to the phase: ledger sums equal payroll totals, re-running an event does not duplicate entries, timezone boundaries, MySQL-style bucket keys, multi-tenant isolation (a store never sees another's data).
f. Visual check where UI changed: 375 / 768 / 1440 px, RTL and LTR, light and dark.
g. Update the tracker row: Status = "Done - verified", date, commit hash.
h. If ANY check fails, status stays "In progress", fix it, and re-verify. Never move on with a failing check.
i. After a passing gate, **STOP and report to the owner**. Do not start the next phase until the owner approves it.

Also, before starting a phase: confirm that its dependencies show "Done - verified"; if not, stop and say so.

## 10. Phase log (append-only)

Entry template:

```
### <ID> - <title> - <date> - <commit>
Scope done / Deviations / Checks (a-h with evidence) / Open issues / Decisions needed from owner
```

### 37-K - Unified status groups + per-view charts - 2026-10-06 - (work uncommitted)

**Scope done**
- `app/Domains/Analytics/Support/DashboardStatusGroups.php` (new, 79): single source of status lists. D2 "confirmed" group = confirmed, preparing, processing, shipped, in_transit, out_for_delivery, delivered, completed, returned, undeliverable, unclaimed, refunded — KPI + team table + doughnut only, **never payroll**. CANCELLED/CANCELED both present in the canceled group. `inConfirmed()` used for the doughnut collapse.
- `DashboardStatusBreakdown.php` (new, 120): one grouped query over `orders` x `statuses`, `DashboardOrderScope::trackedOnly()` for the delivery view (D3: orders with ≥1 `order_trackings` row, counted once), collapses the confirmed group only in the confirmation view, mapper, percent with last-slice absorb + deficit correction (rows always sum to 100).
- `DashboardTrendQuery.php` (new, 125): one bucketed multi-metric query via `DateBucket::fill()`; confirmation view = received/confirmed/canceled lines on `y`; delivery view = delivered + returned bars on `y` and revenue line on `y1`. `countWhen()` exists because a bare `status_id in (?)` in a grouped select returns one row's boolean, not a count (bug found and fixed). Colors come from `OrderStatus` (db source preferred, fallback `#9ca3af`); `token:accent` marks received/revenue for the JS theme lookup.
- `StoreDashboardAnalyticsService.php` (235 -> 185): dropped `ordersByStatus/salesByDay/salesSeries`, added `statusBreakdown(?DashboardFilter): Collection` and `trendSeries(?DashboardFilter): array`.
- `DashboardSummaryQuery` / `DashboardTeamPerformanceQuery` refactored onto `DashboardStatusGroups` (87 -> 68, 148 -> 122); `DashboardSeriesQuery` reduced to `series()` (85 -> 78); `DateBucket` gained generic `fill()` with `generateSeries()` delegating to it (170 -> 196).
- `dashboard.blade.php` payload now sends `statusBreakdown` + `trend` (400 -> 386); `charts.blade.php` rewritten (297 -> 277): per-view doughnut (cutout 68%, center-total plugin, `${label}: ${count} (${percent}%)` tooltips) and per-view trend (y = counts, y1 = money, `y1` axis built only when a series uses it, hourly/daily labels from `data.chartLabels`, `new Date()` nowhere - labels are plain strings), MutationObserver for theme/class swaps retained.
- Lang: `chart_confirmation_trend`, `chart_delivery_trend`, `chart_status_confirmation`, `chart_status_delivery`, `chart_total`, `series_{received,confirmed,delivered,returned,canceled,revenue}` x 4 locales.
- Tests: new `DashboardChartsPerViewTest` (9) and `DashboardStatusGroupsTest` (5); `DateBucketTest` gained `fill()` MySQL-key coverage; `DashboardAnalyticsServiceTest`, `DashboardAnalyticsTimezoneTest`, `DashboardFilterTest`, `DashboardFilterWiringTest`, `DashboardTeamPerformanceTest` re-pointed at the new methods (Beta rows `2/1/1/0/0/50%`, totals `5/3/1/1/0/60%`).

**Deviations** - none from the declared scope; `git status` shows only the files listed above (18 modified, 5 new). `php.ini` `memory_limit` was temporarily set to `-1` to run the full suite through paratest child processes, then restored to `512M` (system config, not repo).

**Checks**
- a. Plan re-read; scope, D1-D4 chart semantics and section 9 gate applied as described above.
- b. `php artisan test --compact` full run: **1211 passed, 48 failed, 1 risky**; baseline on a stashed clean tree: **1196 passed, 48 failed**; the failure lists are byte-identical (pre-existing storefront/cart/routing failures, e.g. `CartService::getItems(null)` ViewException), so 37-K adds 15 passing tests and no failures. Dashboard group alone: **109 passed (574 assertions)**. `vendor/bin/pint --test` on all 23 touched files: **PASS** (one `concat_space`/`ordered_imports` issue in `DashboardAnalyticsServiceTest` fixed).
- c. Sizes (before -> after): service 235 -> 185 (<=250), `DateBucket` 170 -> 196 (<=250), new files 120/125/79 (<=250), Volt 400 -> 386 (<=400), partial 297 -> 277 (<=300).
- d. Diff limited to analytics support classes, the service, dashboard + charts views, 4 lang files, dashboard tests (see `git status` above). No routes, permissions, filters UI, KPI or team-table behavior changes.
- e. Accuracy: query budget test re-measured (baseline still exactly 50 queries, ceiling 52 comment unchanged); `DashboardChartsPerViewTest` proves multi-tenant isolation is scoped to `store_id`, delivery cohort counts a twice-tracked order once, percent rows sum to 100, empty windows still return keyed series; hourly bucket keys rebuilt as `Y-m-d H` (MySQL `DATE_FORMAT` style) are covered by the new `DateBucket::fill()` test; timezone window behavior re-verified in `DashboardAnalyticsTimezoneTest`.
- f. **Not done** - needs a human pass at 375/768/1440 px, RTL + LTR, light + dark.
- g. Tracker row updated (status kept **In progress** until f passes).
- h. Not applicable while f is open.

**Open issues** - none in code.

**Decisions needed from owner** - do the visual pass (check f) on the dashboard, then approve marking 37-K **Done - verified**; also confirm whether this work should be committed.

## 11. Open decisions (owner to answer before the phase that needs them)

- **D1** Which event creates the earning for "confirmed" and for "tracked" (status key / action)? *(38-A, 38-D)*
- **D2** Default payroll period (weekly / monthly / custom)? *(38-B)*
- **D3** Rounding rule for percentage commissions? *(38-B)*
- **D4** Is delivery confirmed only by carrier status, or can the owner confirm manually? *(38-D)*
- **D5** Return reasons list the merchant can mark as "member fault"? *(38-B)*
