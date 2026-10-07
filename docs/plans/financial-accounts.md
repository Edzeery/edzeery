# Financial Accounts — Master Plan (Series 38)

**Recorded:** 2026-10-05
**Commit at time of writing:** `929569d54ac8c2464aaaa5ecc5b3a1aabf87c217`
**37-K (Unified status groups + per-view charts) status:** Done - verified (`8e0fe03` + `71065cd`, see phase log). `App\Domains\Analytics\Support\DashboardStatusGroups` is now the single source of status lists (D2 semantics, KPI/team table only, never payroll); `charts.blade.php` and `StoreDashboardAnalyticsService` were rebuilt on top of it (`statusBreakdown()` + `trendSeries()`). 37-K.2 (charts vanishing when an already-active filter is clicked twice) also verified; see its phase log entry. Header line kept honest here rather than in 38-A.

> Anything below marked **[VERIFY]** was written from planning, not from reading the repo. During 38-A the agent must confirm or correct each one and update this file. **38-A is complete: the three markers in the tracker row were resolved and are removed; full evidence is in section 12 "38-A Findings (audit)".**

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
| 37-K | Unified status groups + per-view charts | — | Done - verified (owner approved the 24 screenshots; `8e0fe03`) | 2026-10-06, `8e0fe03` + `71065cd` | See phase log entry below; header note corrected: `DashboardStatusGroups` now exists |
| 37-K.2 | Charts vanish when an already-active dashboard filter is clicked twice | 37-K | Done - verified | 2026-10-06 | See phase log entry below; interplay guard + `wire:ignore` canvases |
| 37-K.3 | Restore a fully green test suite (groups G1-G6) | — | Done - verified (0 failed, 0 risky; `8e21c7d`) | 2026-10-06, `8e21c7d` | See phase log entry; full suite after G6: **1298 passed, 0 failed, 0 risky** |
| 38-A | Audit: existing payroll/accounting modules; does `order_status_histories` store the acting user and exact timestamp; who creates tracking rows and how; which attribution fields exist — **all three now answered from the repo** | 37-K | Done - verified (owner approved 2026-10-06; decisions S8–S12; docs-only; `73f1ea2`) | 2026-10-06, `73f1ea2` | Section **12 "38-A Findings (audit)"**; decisions **S8–S12** in section 11; revised order + final 38-C scope in section 13. `order_status_histories` stores a nullable `changed_by_membership_id` + standard `created_at` (no from-status, no source, actor often null); tracking rows created by `OrderTrackingService::startShipment()` (idempotent only while open — duplicates after close); attribution = nullable membership FKs on orders/trackings/history/events. **No payroll/HR module exists — built from scratch, nothing to reuse or migrate.** |
| 38-C | Capture missing attribution/event fields (`confirmed_at`/`confirmed_by`, `delivered_at`, `returned_at`, `tracked_by`, history `from_status` + `source`, COD snapshot) + worker-cache isolation + transaction-atomic earning capture seam | 38-A | Done - verified (full suite **1324 passed, 0 failed**; see phase log) | 2026-10-07, `f4a0a99` (code) + plan commit | **Runs BEFORE 38-B** (owner decision): un-captured actor/timestamps cannot be backfilled. Final implementation scope in section 13; default `accrual_start_date` = this phase's deploy date per store |
| 38-B | Member compensation plan + member add/edit form + store finance settings | 38-A | Not started | — | S9, S10, S12 decided. Build compensation model **from scratch** (no payroll/HR exists; nothing to reuse or migrate). Include assessment-only evaluation of reusing the shifts module for handover attribution |
| 38-D | Earning ledger + backfill from accrual start date + tests | 38-C, 38-B | Not started | — | S8, S11 decided. Forward capture starts at the 38-C deploy date (`accrual_start_date`); backfill from there |
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
| 37-M | Time analytics (its history part is needed earlier for event dates) | 37-K | Not started | — | 38-A resolved the contingency: history/events already carry event dates (`order_status_histories.created_at`, `order_events.occurred_at`) — the history part does NOT need to be pulled forward |
| 37-P | (reserved) | 37-K | Not started | — | Later as needed |
| 37-Q | (reserved) | 37-K | Not started | — | Later as needed |

## 9. Verification gate (MANDATORY, applies to every phase)

Before a phase may be marked Done, the agent must do ALL of the following and record the evidence in the phase's log entry in this file:

a. Re-read this plan and the phase's own TASKS and ACCEPTANCE; tick each item with evidence (file/line, test name, command output summary).
b. `php artisan test` passes with **0 failed, 0 risky** (state the counts); `./vendor/bin/pint --test` passes on touched files.
c. Size limits respected (PHP classes ≤ 250 lines, services ≤ 250, Volt ≤ 400, partials ≤ 300); record before/after sizes of touched files.
d. `git diff` is limited to what the phase declares; list any deviation and why.
e. Accuracy checks relevant to the phase: ledger sums equal payroll totals, re-running an event does not duplicate entries, timezone boundaries, MySQL-style bucket keys, multi-tenant isolation (a store never sees another's data).
f. Visual check where UI changed: 375 / 768 / 1440 px, RTL and LTR, light and dark.
g. Update the tracker row: Status = "Done - verified", date, commit hash.
h. If ANY check fails, status stays "In progress", fix it, and re-verify. Never move on with a failing check.
i. After a passing gate, **STOP and report to the owner**. Do not start the next phase until the owner approves it.
j. Where the phase touches the dashboard filters or charts, run the interaction regression in `tests/browser/charts-interaction-regression.mjs` against a working local server:
   `php artisan serve` then `npm run test:charts` (drives headless Chrome for the confirmation **and** delivery views: re-click of the active pill must fire zero requests, same-filter roundtrips must leave the canvases sized and live, A→B→A, rapid triple click, reset twice, no-data placeholder swap and theme toggle must all pass with zero console errors).

Test discipline when restoring a red suite (37-K.3 and any later green-suite work): fix root causes first — production bug → fix product code and keep/strengthen the test; stale or broken test → fix the test without skipping, deleting, or loosening its assertions. Commit per group (`G1`, `G2`, ...), run the full suite after each group, and record counts in the phase log. Do not fix symptoms, do not change expectations to match broken output.

Also, before starting a phase: confirm that its dependencies show "Done - verified"; if not, stop and say so.

## 10. Phase log (append-only)

Entry template:

```
### <ID> - <title> - <date> - <commit>
Scope done / Deviations / Checks (a-h with evidence) / Open issues / Decisions needed from owner
```

### 37-K - Unified status groups + per-view charts - 2026-10-06 - (bulk in 8e0fe03; 3 files uncommitted)

**Scope done**
- `app/Domains/Analytics/Support/DashboardStatusGroups.php` (new, 79): single source of status lists. D2 "confirmed" group = confirmed, preparing, processing, shipped, in_transit, out_for_delivery, delivered, completed, returned, undeliverable, unclaimed, refunded — KPI + team table + doughnut only, **never payroll**. CANCELLED/CANCELED both present in the canceled group. `inConfirmed()` used for the doughnut collapse.
- `DashboardStatusBreakdown.php` (new, 120): one grouped query over `orders` x `statuses`, `DashboardOrderScope::trackedOnly()` for the delivery view (D3: orders with ≥1 `order_trackings` row, counted once), collapses the confirmed group only in the confirmation view, mapper, percent with last-slice absorb + deficit correction (rows always sum to 100).
- `DashboardTrendQuery.php` (new, 125): one bucketed multi-metric query via `DateBucket::fill()`; confirmation view = received/confirmed/canceled lines on `y`; delivery view = delivered + returned bars on `y` and revenue line on `y1`. `countWhen()` exists because a bare `status_id in (?)` in a grouped select returns one row's boolean, not a count (bug found and fixed). Colors come from `OrderStatus` (db source preferred, fallback `#9ca3af`); `token:accent` marks received/revenue for the JS theme lookup.
- `StoreDashboardAnalyticsService.php` (235 -> 185): dropped `ordersByStatus/salesByDay/salesSeries`, added `statusBreakdown(?DashboardFilter): Collection` and `trendSeries(?DashboardFilter): array`.
- `DashboardSummaryQuery` / `DashboardTeamPerformanceQuery` refactored onto `DashboardStatusGroups` (87 -> 68, 148 -> 122); `DashboardSeriesQuery` reduced to `series()` (85 -> 78); `DateBucket` gained generic `fill()` with `generateSeries()` delegating to it (170 -> 196).
- `dashboard.blade.php` payload now sends `statusBreakdown` + `trend` (400 -> 386); `charts.blade.php` rewritten (297 -> 277): per-view doughnut (cutout 68%, center-total plugin, `${label}: ${count} (${percent}%)` tooltips) and per-view trend (y = counts, y1 = money, `y1` axis built only when a series uses it, hourly/daily labels from `data.chartLabels`, `new Date()` nowhere - labels are plain strings), MutationObserver for theme/class swaps retained.
- Lang: `chart_confirmation_trend`, `chart_delivery_trend`, `chart_status_confirmation`, `chart_status_delivery`, `chart_total`, `series_{received,confirmed,delivered,returned,canceled,revenue}` x 4 locales.
- Tests: new `DashboardChartsPerViewTest` (9) and `DashboardStatusGroupsTest` (5); `DateBucketTest` gained `fill()` MySQL-key coverage; `DashboardAnalyticsServiceTest`, `DashboardAnalyticsTimezoneTest`, `DashboardFilterTest`, `DashboardFilterWiringTest`, `DashboardTeamPerformanceTest` re-pointed at the new methods (Beta rows `2/1/1/0/0/50%`, totals `5/3/1/1/0/60%`).

**Deviations** - none from the declared scope. The bulk of the code landed in commit `8e0fe03` ("Implement Dashboard Status Breakdown and Trend Query") earlier the same day; the working tree still carries the post-visual-check refinements to `charts.blade.php`, the `DashboardChartsPerViewTest` additions (percent-rounding + owner-review assertions) and this plan update (3 files, see `git status`). All visual-check artifacts live in `%TEMP%\opencode\charts37K1\` out of tree. `php.ini` `memory_limit` was temporarily set to `-1` to run the full suite through paratest child processes, then restored to `512M` (system config, not repo).

**Checks**
- a. Plan re-read; scope, D1-D4 chart semantics and section 9 gate applied as described above.
- b. `php artisan test --compact` full run: **1211 passed, 48 failed, 1 risky**; baseline on a stashed clean tree: **1196 passed, 48 failed**; the failure lists are byte-identical (pre-existing storefront/cart/routing failures, e.g. `CartService::getItems(null)` ViewException), so 37-K adds 15 passing tests and no failures. Dashboard group alone: **109 passed (574 assertions)**. `vendor/bin/pint --test` on all 23 touched files: **PASS** (one `concat_space`/`ordered_imports` issue in `DashboardAnalyticsServiceTest` fixed).
- c. Sizes (before -> after): service 235 -> 185 (<=250), `DateBucket` 170 -> 196 (<=250), new files 120/125/79 (<=250), Volt 400 -> 386 (<=400), partial 297 -> 277 (<=300).
- d. Diff limited to analytics support classes, the service, dashboard + charts views, 4 lang files, dashboard tests (see `git status` above). No routes, permissions, filters UI, KPI or team-table behavior changes.
- e. Accuracy: query budget test re-measured (baseline still exactly 50 queries, ceiling 52 comment unchanged); `DashboardChartsPerViewTest` proves multi-tenant isolation is scoped to `store_id`, delivery cohort counts a twice-tracked order once, percent rows sum to 100, empty windows still return keyed series; hourly bucket keys rebuilt as `Y-m-d H` (MySQL `DATE_FORMAT` style) are covered by the new `DateBucket::fill()` test; timezone window behavior re-verified in `DashboardAnalyticsTimezoneTest`.
- f. **Done** (automated evidence, owner eyeball still requested) - headless Chrome pass over all **24 combos** (confirmation + delivery views x light/dark x ltr/rtl x 375/768/1440 px) on the rendered partials with the app forced to the `ar` locale (the demo storefront is Arabic). Evidence in `%TEMP%\opencode\charts37K1\visual-report.json` + 24 PNGs in `shots/`. Every combo: **0 JS errors, no horizontal overflow** (scrollWidth == viewport); doughnut center draws the count + `chart_total` label (e.g. "16", "الإجمالي") with **contrast ratio 17.74 (light) / 16.98 (dark)** vs the card background (>= 4.5); legend rows render every slice as `label: count (percent)` and wrap cleanly at 375 px; confirmation trend = 3 line datasets on `y` and delivery trend = 2 bar datasets on `y` + revenue line on `y1` (both `stacked: false`, `y1` axis ticks carry the currency, e.g. "0 دج"..."10,000 دج"). Note: Chrome's minimum window width is 500 px, so the 375 px case is emulated by capping the layout frame at 375 px (the mobile single-column branch is active at 500 px viewport).
- g. Tracker row updated: **Done - all checks complete; awaiting owner visual sign-off**.
- h. Not applicable to code (no deps/security impact); visual evidence producer committed to `%TEMP%` out of tree.

**Re-verified pre-existing failures (check b follow-up, item 8 of owner review)** - the 48 failures are unchanged from the baseline and group into root causes unrelated to 37-K:
- 29x `CartService::getItems(): Argument #1 ($storeId) must be of type string, null given` during `storefront/order-form.blade.php` render (blade passes a null store id under Livewire) - `OrderCancellationRestockTest` (2), `CartOrderLimitsTest` (7), `CheckoutAccessControlTest` (2), `StorefrontOrderShippingCascadeTest` (18).
- 4x `Expected response status code [200] but received 404` - host routing (`ExampleTest`, `HostIsolationTest` apex/subdomain/merchant-dashboard).
- 3x `event [swal] was fired` and 3x `array offset on null` - `VariantOrderingRulesTest`/`CartOrderLimitsTest` (sweetalert wire event + variant payload expectations).
- 1 each: `HTTP 20x-redirect expectation got 404` (`HostIsolationTest`), `assert false is true` (`RoleScopingTest`), `Throwable not thrown` (`HostIsolationTest`), `Output does not contain "8"` + `event [edz-notice]` (`CarrierSyncObservabilityTest`, `CartOrderLimitsTest`), 2x `wire:snapshot render mismatch` + `event [cart-updated]` (`VariantOrderingRulesTest`).
Full run that produced this: **48 failed, 1 risky, 1212 passed (1149839 assertions)**, byte-identical failing set to the pre-change baseline (see b). Full suite needs `memory_limit=-1` under Laragon's PHP via `php.ini` (artisan test spawns paratest children); it was temporarily set and **restored to 512M**.

**Open issues** - none in code.

**Decisions needed from owner** - do the visual pass (check f) on the 24 screenshots, then approve marking 37-K **Done - verified**; also confirm whether the 3 remaining uncommitted files (`charts.blade.php`, `DashboardChartsPerViewTest.php`, this plan) should be committed (the rest is already in `8e0fe03`).

### 37-K.2 - Charts vanish when an already-active dashboard filter is clicked twice - 2026-10-06

**Proven root cause (evidence captured in a CDP repro first, no symptom patching)** - clicking a period pill that is already active still ran a full Livewire roundtrip (the pill had no guard), and because the charts partial's `wire:key` is the filter hash **and the hash is identical**, Livewire morphed the keyed subtree **in place**. The fresh server HTML for the `<canvas>` carries no `width`/`height`/`style` (Chart.js sets them client-side), so the morph **removed those attributes** from the live node. The Chart.js instance survives (`Chart.getChart(canvas)` stays LIVE and its datasets still hold the right values) but its backdrop resets to a blank 300x150 canvas and never redraws until a reload. Switching to any *different* filter fixes it instantly (new hash -> root replaced -> `x-init` redraws), exactly matching the report. A probe on the reproduction store showed: after `all` clicked twice, both canvases still `inst=LIVE` with the correct 10/3/1/1/1 slices but `w=null h=null style=null`; zero JS errors throughout (rules out the "Canvas is already in use"/Alpine re-init theory).

**Scope done**
- `DashboardFilterConcern::setPeriod(string)` (new in `app/Livewire/Concerns/DashboardFilterConcern.php`): a no-op when the requested period equals the current one, so a same-value request leaves state and payload untouched; tested in `DashboardFilterWiringTest` ("re-applying the active period leaves state and payload untouched").
- `filter-bar.blade.php`: period pills go through `setPeriod()` and the **active pill is rendered `disabled`** (`aria-pressed` added), so clicking it fires zero requests; the stats-view radio that matches the current view is `disabled` too (same-value `.live` radio clicks can no longer round-trip).
- `charts.blade.php`: both `<canvas>` elements are now `wire:ignore`-protected, so **any** same-filter roundtrip that still reaches the server (carrier/member select with the same value, custom-date blur, URL re-entry) morphs the tree without stripping the Chart.js size attributes; `renderDashboardCharts()` additionally destroys any pre-existing instance on the canvas via `Chart.getChart(canvas)` before creating a fresh one (one instance per canvas). Theme MutationObserver and no-data placeholders are untouched and re-verified working below.
- Regression harness `tests/browser/charts-interaction-regression.mjs` (new, repo-kept; `ws` added as a devDependency). One-line run: `php artisan serve` (terminal 1), `npm run test:charts` (terminal 2). Drives headless Chrome against `/merchant/{store}/dashboard` for **both** views (confirmation, delivery) and every step asserts: both canvases keep `width` + `height` + `style` + a LIVE `Chart.getChart` instance whenever a canvas exists; the active pill is `disabled`; re-clicking the active pill fires **0** livewire requests; same-filter `$wire->set` roundtrips leave canvases untouched; A→B→A redraws weekly then restores `all`; rapid triple-click on `month`; reset twice; the no-data placeholder swaps the doughnut canvas (wire:ignore does not block the swap); theme dark/light toggle re-renders charts; zero console errors/exceptions. Env overrides: `EDZEERY_BASE_URL` / `_EMAIL` / `_PASSWORD` / `_STORE_SLUG` / `_CHROME` / `_EXPECT_DATA=0`.

**Checks**
- a. Scope = charts + dashboard filters component + tests only, per the phase brief; no KPI/status/layout/colour changes.
- b. `php artisan test --filter=DashboardFilterWiringTest|DashboardChartsPerViewTest`: **29 passed (206 assertions)**, including the new unchanged-filter test.
- j. `npm run test:charts` against `127.0.0.1:8000` (artisan serve): **PASS** for both views, zero failures, zero JS errors (full JSON report in the terminal output).
- c-f, h. No size/accuracy/visual/dep change beyond the above (sizes: concern 54 -> 73, filter-bar partial 165 -> 168, charts partial 282 -> 287, all within limits).
- g. Tracker row added and marked **Done - verified** (37-K row also corrected to "Done - verified" per the owner's sign-off).

**Open issues** - none.

### 37-K.3 - Restore a fully green test suite - 2026-10-06 - (G1 `01f94b8`, G2 `5d6888a`, G6 `8e21c7d`)

**Scope** - drive the suite from its 48-failure baseline to 0 failed / 0 risky by fixing root causes (never skip/delete/loosen tests), one commit per group, full suite after each group, counts recorded. The full-suite gate now passes (see G6 below).

**Group G1 (commit `01f94b8`, "test: resolve store context for storefront Volt tests")**
- 40 storefront Volt failures shared ONE root cause: the pages resolve the current store at render time via `currentStoreId()` → `StoreResolver::resolve()`, which needs `StoreContext` — set only on actions, not during render. Fix: `app(\App\Support\StoreContext::class)->set($store)` in `oscStore/colStore/cawStore/orStore` + `matrixComponent`, plus `afterEach(app(...)->clear())` in all 5 files. This absorbed the `edz-notice` and `wire:snapshot` failures too (previously planned as G3/G4). Filament ~4.0; 46/46 green.
- Full suite after G1: **1254 passed, 8 failed, 1 risky** (was 1213/48/1).

**Group G2 (commit `5d6888a`, "feat: guard reserved store slugs and fix storefront host capture")** - three separate root causes:
1. **Host-relative test requests (test-side bug, not production)**: `$this->get('/')` prepends `baseUrl` (APP_URL `https://edzeery.com`) → `Request::create` derives `HTTP_HOST=edzeery.com`, which overrides `withServerVariables(['HTTP_HOST'=>…])` in `MakesHttpRequests::call` (`array_replace($this->serverVariables, $server)` — `$server` wins). Manual kernel dispatch with `HTTP_HOST=example.test` returned 200, proving production was fine. Fix: tests use absolute URLs (`"http://".config('app.domain')."/"` etc.) in `ExampleTest` and `HostIsolationTest`.
2. **Real production bug — reserved storefront subdomains were never reserved (routing)**: `routes/storefront.php` reserved `www/app/admin/api/mail` via `->where(['store' => '^(?!(www|app|admin|api|mail)$)[a-z0-9-]+$'])`, but Laravel embeds the `where` pattern into the compiled host regex verbatim, so the lookahead's `$` anchors to the END OF THE WHOLE HOST string (after `.example.test`), not after the subdomain — it never fires. Every `www.*` request matched `storefront.home` and then 404'd binding a store slug `www`, permanently shadowing the `www.landing` redirect. Kernel-dispatch probe captured route = `storefront.home` and the compiled host regex before the fix. Fix: anchor the lookahead to the dot after the subdomain — `(?!(?:www|app|admin|api|mail)\.)`.
3. **Real production defect — StoreForm currency default**: `Select::default([0])` (array) crashed Filament state casting (`OptionStateCast` "Array to string conversion") whenever the page hydrated; fixed to `->default('DZD')`.

**Reserved-slug guard (owner decisions, both pre-task answers applied)** -
- Single source of truth: `app/Support/StoreSlugRules.php` (`RESERVED_SLUGS` = www, api, admin, mail, app, demo, edzeery, support, help, status, cdn, assets; `isReserved()` trims + lowercases).
- Enforced at BOTH boundaries: `Store::booted() saving` guard (throws `ValidationException`, only on create or when the slug is dirty — an existing store with an unchanged reserved slug keeps saving) and the Filament StoreForm slug rule, which now reads `StoreSlugRules::isReserved()` (no duplicated list).
- One greppable exemption: `Store::withReservedSlug()` (docblocked in `StoreSlugRules`), used ONLY by `DemoStoreSeeder` (wrapped `firstOrCreate(['slug'=>'demo'], …)`). A read-only check command `store:check-reserved-slugs` lists any existing reserved-slug stores (to run on production before/after deploy). Nothing was renamed or deleted.
- Findings listed before enabling the guard: only `demo` conflicted — `DemoStoreSeeder.php:79` seeds it, and the dev DB already has that row (store `01m43ppn02xnvy8fbpbypek7tj`); no other reserved slug appeared in any seeder/factory/test; no `lang/` directory exists (`__('validation.reserved')` renders literally).
- Fresh-DB seeding proven by tests: `DemoStoreSeederTest` runs the REAL seeder on a fresh database (store slug `demo` created, re-run stays idempotent).
- Pest gotcha recorded: `->throws(\Throwable::class)` fails even when the exception is thrown (Pest's `toThrow` uses `class_exists()`, false for interfaces) — use the concrete `ValidationException::class`.

**Group G6 (commit `8e21c7d`, "fix: scope canStore permission memo per store; assert report output on real buffer")** - the last 2 failures + 1 risky, three distinct root causes:
1. **Real production bug — `canStore`'s per-request memo was NOT store-scoped** (`app/Helpers/helpers.php`): the memo was keyed only by the permission name and reset only when the USER changed, so a user switching stores mid-request (decision #6 per-store isolation) received the previous store's stale result. Probe test proved it: the store-B OWNER membership's `permissionNames()` contained `order.manage`, yet `canStore(ORDER_MANAGE)` returned false after the switch to store B — while `hasStoreRole` (not memoized) was correct. Fix: key the memo by the resolved store too (`$storeKey = (string) currentStoreId()` — cheap, `StoreResolver::resolve()` is memoized through the request-scoped `StoreContext` singleton). `RoleScopingTest` ("isolates custom permissions per store membership (decision #6)") passes; the whole `tests/Feature/Merchant` directory re-ran green (849 passed, 0 failed) to prove no regression.
2. **Harness-only quirk — `expectsOutputToContain` vs Symfony table rows** (test-side, not production): Laravel's `PendingCommand` matches each registered substring against individual `doWrite` calls, and each call satisfies only the FIRST matching expectation — two tokens printed in the SAME table row (provider name + the count `8`) cannot both be asserted, so the second (`'8'`) never matched and the test failed even though the real `Artisan::output()` contained both. Fix: assert on the real captured buffer (`Artisan::call` + Pest `expect($output)->toContain(...)`), preserving the exact intents — store + provider name, `updated=8`, `attempted=10`, success rate `80%`, exit code 0.
3. **Leftover debug "test"** (`debug report output`, zero assertions → the risky result): a debugging dump with no assertions; its only purpose (inspect the report output) is now genuinely asserted inside the rewritten report test. Removed; real coverage preserved.

**Tests**
- `RoleScopingTest` now 2 passed (19 assertions) after the memo fix.
- `CarrierSyncObservabilityTest` now 3 passed (45 assertions): the report test asserts provider/count lines against real output; the zero-assertion dump test is gone (count dropped by 1 test item but assertions grew — it never asserted anything).
- Temporary probe (`tests/Feature/ScratchRouteDebugTest.php`) deleted after diagnosing G6.
- `StoreSlugGuardTest` (26): every reserved slug rejected on create + update (dataset driven from `StoreSlugRules::reservedSlugs()`), case/whitespace variants rejected (`WWW`, ` Www `, ` demo `, …), normal slug accepted, unchanged reserved slug saves, seeder exemption path works, normal create with `demo` still throws, Filament form still rejects (`fillForm` + `createStore` → slug form error).
- `DemoStoreSeederTest` (2): fresh-DB seed + idempotent re-run.
- `HostIsolationTest` rewritten (6): apex `/` → landing 200, subdomain `/` → storefront 200, subdomain `/contact-us` → 404, **www → redirects to landing** (regression pin for the routing bug), creating slug `www` is rejected, merchant dashboard resolves 200 (legit OWNER: platform + store roles, membership, synced perms).

**Checks**
- a. Plan re-read; the changes are exactly the declared G1/G2 groups.
- b. Full suite after G2: **1297 passed, 2 failed, 1 risky** (baseline 1213/48/1; after G1 1254/8/1). **Full suite after G6 (final): 1298 passed, 0 failed, 0 risky** (1,151,560 assertions, 472s). Full runs used `memory_limit=-1` (temporarily), restored to `512M` afterward. Pint: the two G6 files pass `pint --test` (the added `canStore` concat style auto-fixed); the four older files (`Store`, `StoreForm`, `DemoStoreSeeder`, `storefront`) remain pre-existing pint-dirty from baseline — zero new style debt introduced across G1/G2/G6.
- c. Sizes (before → after): Store.php 213 → 247 (≤250), StoreForm 169 → 169, `storefront.php` 38 → 38, DemoStoreSeeder 1492 → 1496; new files: `StoreSlugRules` 47, `CheckReservedStoreSlugs` 36, `StoreSlugGuardTest` 115, `DemoStoreSeederTest` 40.
- d. Diff = the 10 declared G1/G2 files (StoreSlugRules, CheckReservedStoreSlugs, Store, StoreForm, DemoStoreSeeder, storefront, 4 test files) + the 2 declared G6 files (helpers.php, CarrierSyncObservabilityTest). 37-K.2's own uncommitted files stay out of these commits (owner hasn't approved committing them).
- e. Multi-tenant / host isolation covered by `HostIsolationTest` (each store only reachable on its own subdomain; platform hosts never resolve a store). Store-scoped permission isolation asserted by `RoleScopingTest` (decision #6).
- g. Tracker row updated (Done - verified, `8e21c7d`).

**Open issues** - none: G3 (`edz-notice`) and G4 (`wire:snapshot`) folded into G1; G6 (the last 2 failures + 1 risky) resolved and committed. Final suite 1298/0/0.

**Decisions needed from owner** - none for G1/G2/G6 (owner decisions from the two pre-task questions already applied end-to-end).

### 38-A - Read-only audit for the Financial Accounts program - 2026-10-06 - (docs-only, no code)

**Scope done** - answered A1..A13, B..F and Q15 from the repo with `file:line` evidence; no code/migration/route/config/lang/test changes. Full findings in the new **section 12 "38-A Findings (audit)"**; summary below.

- **A1-A3 status history**: `order_status_histories` = id, order_id (FK cascade), status_id (FK restrict), `changed_by_membership_id` (nullable FK → store_memberships), `reason` (text), timestamps(), index `[order_id, created_at]` (`database/migrations/2026_02_22_220740_create_order_status_histories_table.php:15-34`). It stores an **acting membership id (nullable)** and an **exact timestamp (`created_at`)**. The 38-C migration `2026_10_06_000003` added **`from_status`** (the key the order transitioned FROM, NULL on creation) and **`source`** (manual|bulk|carrier|webhook|api|storefront|system|userscript). Status-write allowlist (enforced by `OrderStatusWriteArchitectureTest`): **`OrderService.php`** (transitions) and **`OrderObserver.php`** (the `updated`-on-`status_id` history writer) plus the **single sanctioned blade write** — the storefront `order-form.blade.php` submit creating the initial `pending` row (`source = storefront`, `from_status = null`). The `updated` observer (`OrderObserver.php:59-68,111`) writes the history row for every `status_id` change, now carrying the transition meta (`changed_by_membership_id`, `from_status`, `source`); unlabelled access fall back to `source = system`. Manual-order creation and storefront placement write the initial `pending` history row with `from_status = null` (`createManual` passes `source`, storefront passes `storefront`). Carrier sync/webhooks **never** write an order status (`NoestTrackingSyncService.php:121-141`). `confirmed` is reached via `OrderConfirmationService::confirm()` → key `confirmed`; `delivered` is reached **only by manual UI transition** (map `OrderService.php:110-133`); `order_events` is the richer audit store (actor_membership_id + occurred_at + json payload, `2026_09_05_100001:11-24`).
- **B4-B5 attribution/tracking**: no `confirmed_by/confirmed_at` on orders — the confirmer is derived from the latest history row for key `confirmed` (`Order.php:198-213` `confirmedByHistory()`), and its membership can be **null** (carrier/direct flows). Assignment columns exist on orders and trackings (membership FKs + `assigned_at`/`assignment_method`/`assigned_by_membership_id`) and **assignment can change after confirmation** (manual reassign + shift handover). Tracking rows are created by `OrderTrackingService::startShipment()` (`OrderTrackingService.php:18-45`, idempotent only while an open row exists); `tracking_number` is **not unique** (`2026_08_26_000001:46-47`) so multiple rows per order are possible; **"tracked" = the order has ≥1 tracking row** (`DashboardOrderScope.php:41-49`). No `tracked_by`/`created_by_membership_id` column on trackings (actor only in `order_tracking_histories`).
- **C6-C7 delivery/returns/COD**: `delivered_at`/`returned_at` exist **only on `order_trackings`** (not on orders); delivered order status is manual-only; a delivered order may move to returned/completed. No structured return reasons — only free-text `reason` + `inspection_result/inspection_notes` enum (`good|damaged|partial|lost`) on trackings + carrier raw text. COD: no `cod_amount/cod_remit/carrier_fee`/return-fee columns anywhere; collectible is computed on the fly (`NoestIntegrationAdapter.php:216-220`). `orders.subtotal` is a dead column.
- **D8-D9 members/permissions/payroll**: membership + per-store Volt member form + hybrid permission system all exist and are the reuse base; the exact recipe to add `finance.view/manage/pay/close_period` is documented (enum + lang ×4 + StoreRoles + mandatory StoreRolesAndPermissionsSeeder + gates). **No payroll/salary/commission/HR module exists** — only the debts module (ported from the Finance-Manager repo, `Todos.md:124-126`, remain-only), billing payments (subscriptions), and `confirmation_shifts` (scheduling only, no money). Verdicts: reuse shifts for scheduling, reuse the debts pattern, build compensation fresh in 38-B.
- **E10-E12 money/timezone**: product `cost_price` + `shipping_cost` + `discount_*` + `total_amount` exist; profit is computed only at variant/product display level (no order-level profit); money is `decimal(10,2)` with PHP float + `round(...,2)` (no integer-cents/bcmath); orders carry **no currency column**. No expenses/suppliers/cash accounts/ad spend/partners anywhere. Timezone lives in `store_settings.timezone` (not `stores`), with one canonical store-timezone pattern (`DashboardFilterFactory` → `DashboardFilter` → `DashboardOrderScope`/`DateBucket`) that several order/tracking/finance/plan queries bypass (listed in section 12).
- **F13-F14 + Q15 infra/tenant**: API = `/api/v1` (only `/user` + products) + 2 webhooks, Sanctum auth, `X-Store-Id` context; **no outbox**, domain events all synchronous (none `ShouldQueue`); queue driver `database`; observers are the dominant pattern (registered `AppServiceProvider.php:95-99`); tenant isolation = `StoreScope` global scope on **Product/Debt/DebtPayment only** + manual `where store_id` (53 sites) + HTTP middleware chain — orders have no global scope, so write discipline is manual. `finance-manager` appears **only in this plan and `Todos.md`** (debts-port origin; `MathFinanceManager` is a status-label helper name collision). Q15: `StoreContext` is a container singleton with **no auto-clear**; `app('currentMembership')` is bound per request; all store-data caches (`canStore`, `OrderService::$branchCache`, `OrderCompleteness`, `DeliveryRiderService::$listCache`, `StatusResolver` — all store-keyed) plus `StoreContext`/`currentMembership` persist across jobs in a long-lived worker with **no reset hook** (`Queue::before`/Octane absent); every job/command already carries `store_id` in its payload — an earning-entry listener (38-D) must resolve the store from the event payload, never from ambient context, and set `store_id` on every written row.

**Deviations** - none. Docs-only by design; no code tests run (gate items b/c/e/f are N/A for a docs-only phase) — see section 12 for the N/A statement.

**Checks**
- a. Plan re-read; the audit answers A1..F13/Q15 with `file:line` evidence; all three `[VERIFY]` markers removed; tracker row updated; section 12 appended.
- b/c/e/f/h. N/A — no code, migration, route, config, lang or test touched; `git diff` shows only `docs/plans/financial-accounts.md`.
- d. Diff limited to the single planned file.
- g. Tracker row 38-A = **Done - verified**, date 2026-10-06.
- i. STOP — report to the owner (below).

**Open issues** - gaps for reliable attribution/event dates (section 12 "Gaps"); direct status writes that produce actorless history; multiple-tracking-row dedupe; historical backfill cannot restore the actor for carrier/non-meta flows.

**Decisions needed from owner** - the five recommended defaults for D1-D5 (section 12, each with a reason), plus approval of the proposed 38-B/38-C/38-D scope and the phase-order note (38-C before 38-D production rollout).

### 38-A addendum — owner approval (docs-only) — 2026-10-06

**Approved.** Decisions S8–S12 recorded in **section 11** (replaces the D1–D5 open list). Tracker re-ordered: **38-C (capture) runs BEFORE 38-B, then 38-D** — reason: un-captured actor/timestamps cannot be backfilled. Per-store default `accrual_start_date` = the **38-C deploy date**; older orders may later be manually reassigned from the "unattributed" bucket; **attribution is never guessed**.

**38-C scope additions** (final implementation spec in section 13) — (a) reset per-request/per-store caches (`StoreContext`, `canStore` memo, `OrderService::$branchCache`/`$totalCache`, `OrderCompleteness::$exceptionsCache`, `DeliveryRiderService::$listCache`, `StatusResolver::$keyCache`, `app('currentMembership')`) at the **start and end of every queued job and scheduled command**; (b) the 38-D earning listener must write its entry **inside the same DB transaction as the status transition**.

**38-B addition** — evaluate using the existing shifts module (`confirmation_shifts`) for attribution at shift handover — **assessment only**, no build.

**Recorded** — **no payroll/HR module exists in the repo; the Financial Accounts program is built from scratch — nothing to reuse or migrate.**

**Checks** - a/d/g/i applied for this addendum; b/c/e/f/h N/A (docs-only). `git diff` shows only `docs/plans/financial-accounts.md`. Tracker rows re-ordered (38-C before 38-B); 38-A = **Done - verified** (`73f1ea2`).

## 11. Decisions (owner-approved S8–S12, recorded 2026-10-06; replaced the D1–D5 open list)

- **S8 (D1)** Confirmation earning event = the **first** time an order enters **any** status of the earningTriggers group (**not** only key `confirmed`); actor = the history row's membership; a **null actor** routes the entry to the **"unattributed"** bucket and is **never credited silently**. Tracking earning event = **exactly one per order, ever** (the first `order_trackings` row; later rows — including after a closed shipment — never create another event).
- **S9 (D2)** Default payroll period **monthly**; the data model supports **weekly, biweekly, monthly and custom** from day one. Payments/advances are independent of periods.
- **S10 (D3)** Per-entry **ROUND_HALF_UP to 2 decimals**; payroll lines sum the already-rounded entries (**no re-rounding of the total**); **decimal/integer math only**.
- **S11 (D4)** Store setting: **carrier-sourced delivery auto-finalizes**; a manual delivery by a **non-owner** member creates a **"pending review"** entry **excluded from the payable balance** until owner approval or carrier evidence arrives.
- **S12 (D5)** Structured return-reason list **per store**, with an optional **"apply suggested template"** action (customer refused, no answer, wrong number, wrong address, changed mind, damaged, ...) and a **per-reason "member fault" flag** chosen by the merchant. Default return policy = **no deduction**.

> Section 12 keeps the original recommended-defaults text for the reasoning behind these decisions; S8–S12 here are authoritative.

## 12. 38-A Findings (audit) — 2026-10-06

Read-only audit for the Financial Accounts program. Purpose: replace every `[VERIFY]` marker / planning assumption with repo facts, decide what 38-B / 38-C / 38-D need, and recommend defaults for D1–D5. **No code, migration, route, config, lang or test was changed — this whole section is documentation.**

Verification-gate note: this phase applied section 9 gate items a, d, g, h, i. Items b (suites), c (queue worker), e (migration/ml population), f (config), h (browser/craft) are **N/A for a docs-only phase** — no applicative code was touched. `git diff` shows only `docs/plans/financial-accounts.md`.

Evidence style: `file:line` refers to this repo (branch/commit used: the state at the end of 37-K.3, commit `8e21c7d`). Agent-paraphrased evidence already cross-checked by direct file reads is flagged `(spot-checked)`; everything else was read directly.

### A. Order status history — evidence

- `order_status_histories` schema (`database/migrations/2026_02_22_220740_create_order_status_histories_table.php:15-34`):
  - `id` ulid PK; `order_id` FK→orders cascade; `status_id` FK→statuses restrict; `changed_by_membership_id` **nullable** FK→store_memberships nullOnDelete; `reason` text nullable; `created_at`/`updated_at` timestamps; index `[order_id, created_at]`.
  - **Acting user**: yes, but as `changed_by_membership_id` (a membership, not user id), and only when the transition carried meta / explicit actor — frequently **null** (storefront, carrier, direct writes, bulk ops that pass no actor).
  - **Exact timestamp**: `created_at` (standard second precision; query-ordered by `created_at ASC` in `confirmedByHistory`). `order_events.occurred_at` (`2026_09_05_100001:15`) has `useCurrent()` and is the richer exact-time source.
  - **From-status**: NOT on the row. The previous key is carried only in the audit event payload: `OrderObserver::handleStatusChange` builds `$meta` with `'from_key' => $this->initial['status_id']` and passes it to `OrderAuditService::statusChanged` (`app/Observers/OrderObserver.php:115-126`) → stored as `order_events.payload.from_key`.
  - **Source (manual/bulk/carrier/webhook/api)**: no column anywhere on history/events. Would need to be derived at write time (38-C gap).
- Sole status writer (`app/Domains/Order/Services/OrderService.php:17-78`): `transition()` guards via `canTransition()` (:51) then `transitionToStatus()` sets `status_id` (:62) inside a transaction after `Order::setTransitionMeta()` (:60); the observer fires on `updated` when `wasChanged('status_id')` (`OrderObserver.php:59-68`) and calls `handleStatusChange` (:97-130) which writes `OrderStatusHistory::create` (:111). Meta is populated after save and snapshot into the history row (`changed_by_membership_id`, `reason`, `from_status`, `source`) in the observer. In 38-C `setTransitionMeta()` and the observer now carry the **`from_status`** (`$order->status?->key` at transition time) and **`source`** (caller-supplied, defaulting to `system` for unlabelled internal paths).
- Direct `status_id` updates bypassing `transition()` still write a history row (observer safety net) but with null `changed_by`/`reason`/`from_status` and `source = system` when no meta was set. Verified non-transition writes: storefront initial pending (`resources/views/livewire/storefront/order-form.blade.php:415-437`, direct `Order::create` + `OrderStatusHistory::create` with `from_status = null`, `source = storefront`), seeder rows (`Database/Seeders/DemoStoreSeeder.php:1450-1459`).
- `order_events` (`2026_09_05_100001:11-24`): `store_id`, `order_id`, `actor_membership_id` nullable, `actor_type` default `membership`, `event_type`, `message`, `payload` json, `occurred_at` `useCurrent()`; indices `[store_id, order_id, occurred_at]`, `[event_type]`. Written by `OrderAuditService` (`created`, `statusChanged`, `fieldChanges`). Not yet consumed for payroll — it is the recommended event-time source for 38-D.

### A.2 Every status-change path (callers of `transition`, and non-transition writes)

- UI single transition (chat/bubbles): `resources/views/livewire/merchant/orders/index.blade.php:1581-1603` (transition param), `:1966-1978` (confirm modal), `:2063-2067` (send), `:2320` (bulk), `:1355-1359` (bulk-confirm modal).
- Confirmation flow: `OrderConfirmationService::confirm()` (`app/Domains/Order/Services/OrderConfirmationService.php:44-56`) → `transition('confirmed')`; `startPreparing()` (:62-65) → `preparing`.
- Shipping gateway cancellation/revert: `app/Domains/Shipping/Services/OrderShippingGateway.php:143` (`orders->transition`), `:302-306` (`revertTo` shipped/in_transit/out_for_delivery → confirmed).
- Scheduler: `app/Console/Commands/AutoCancelPendingOrders.php:43` (pending → auto_cancel, `routes/console.php:34`).
- Storefront order placement: initial `pending` (see A.1).
- **Carrier sync never writes an order status**: `NoestTrackingSyncService.php:119-141` only refreshes tracking rows (`tracking_status` :124, `delivered_at` :134, `returned_at` :138); `DeliveryWebhookController` reuses that sync path. Order status is updated later by a merchant action.
- Status-machine data: `app/Enums/Store/OrderStatus.php` (key list), `app/Support/OrderWorkflow.php` (map incl. delivered→returned/completed), seed `SystemStatusesSeeder`. Confirmed status reached only via confirm flow — no other caller sets `confirmed` outside `transition` (spot-checked).

### B. Attribution & tracking

- `orders` confirmation-related columns (`2026_08_21_100001:12-24`): `assigned_to_membership_id` (nullable FK), `assigned_at`, `assignment_method`, `assigned_by_membership_id` (nullable FK), `confirmation_attempts`, `last_contact_at`, `weight_kg`, `shipment_type`.
- **Confirmed-by**: no `confirmed_by`/`confirmed_at` column. Derivation: `Order::confirmedByHistory()` (`Order.php:198-213`) = latest history row whose status key is `confirmed`, ordered `created_at DESC`; its `changed_by_membership_id` is the confirmer, **nullable** for carrier/direct flows.
- **Assignment can change after confirmation**: manual reassign + the shift-handover reassign sweep (`OrderAssignmentService.php:110-130` — reassigns every non-terminal order whose member is not on an active shift; dispatched per store in `routes/console.php:22-27`) operate on confirmed orders too. Confirmer vs current assignee therefore diverge; commissions must key on the history confirmer, not current assignment.
- `order_trackings` (creation + who/when): created row via `OrderTrackingService::startShipment()` (`OrderTrackingService.php:18-45`) — actor only passed into `recordHistory` (`order_tracking_histories`), **no `created_by/assigned_by` column on the tracking row**. `tracking_number` non-unique (plain index, `2026_08_26_000001:45-47`); `currentOpenTracking()` (`:285-292`) makes startShipment idempotent **only while an open row exists** → after delivery/return, a later startShipment/ensureRiderTracking creates a second row. Rider-number pattern: `generateRiderTrackingNumber` (:55-65); duplicate-guard is a query against `tracking_number`.
- Writers of tracking rows: `OrderObserver.php:199` (`status→shipped`), `CarrierOrderPostService.php:64` (actor null — carrier creates after payment, no membership), `ensureRiderTracking` (`OrderTrackingService.php:100-141`), seeder `DemoStoreSeeder.php:1504-1530`.
- "tracked" semantics (dashboard): order has ≥1 tracking row — `DashboardOrderScope::trackedOnly()` (:41-49). Verified earlier.

### C. Delivered / returned / COD

- Delivered order status: manual UI transition only (A.2). `delivered_at` / `returned_at` live **on the tracking row**, not on `orders` (`2026_08_26_000001:33-36`), written by `markDelivered()`/`markReturned()` (`OrderTrackingService.php:145-151`) and by carrier sync logic (`NoestTrackingSyncService.php:...` markDelivered). No `orders.delivered_at`.
- Returnable states: delivered→returned/completed allowed (`OrderService.php:126`); `revertTo` restricted to shipped/in_transit/out_for_delivery→confirmed (`OrderShippingGateway.php:302-306`, `OrderService.php:171-182`); rider/order forms block delivered/returned edits (form rules spot-checked).
- Return reasons: **no structured field**. Free text `order_status_histories.reason`; `inspection_result`/`inspection_notes` enum (`good|damaged|partial|lost`, `app/Enums/..../ReturnInspectionResult.php:7-10`) on trackings (`2026_08_26_000002:22-23`); carrier raw event text via `CarrierStatusDictionary` (returned/unclaimed keys).
- Money on orders: `total_amount` (`2025_12_29_144152:38`), `shipping_cost` (`2026_08_18_000002:39`), `subtotal` (`2026_08_23_194616:15`) — **dead** (never written/read by any resolver; verified grep), `discount_type/value/reason` (`2026_08_26_000003:12-14`), `payment_method` default `cod` (`2026_08_18_000002:38`; `OrderService.php:243`).
- **COD**: no `cod_amount`/`cod_remit`/`carrier_fee`/return-fee column anywhere. Collectible computed on the fly: NOEST `montant` = `items.subtotal + shipping_cost − discount_amount` (`app/Domains/Shipping/Adapters/NoestIntegrationAdapter.php:216-220`, deliberately not the stored total); rider daily COD = `SUM(total_amount)` over open shipments (`app/Livewire/Concerns/TrackingGridConcern.php:273-280`). `carrier_sync_runs` rows are counters/timestamps only. This means 38-D's COD method must recompute per order or persist on capture; historically inconsistent (dead subtotal, discount not always computed) — treat as best-effort.

### D. Members / permissions / payroll

- **Nothing exists** for payroll/accounting beyond: the **debts module** (`app/Domains/Finance/DebtService.php`, `Debt` + `DebtPayment` models — ported from the Finance-Manager repo, see `docs/Todos.md:124-126`, remain-only read/write, remove on backend DR); `billing payments` (subscriptions, attributed by user id, no membership); `confirmation_shifts` (scheduling-only: membership, shift_type, start/end time, days_of_week, is_active — **no money**, verified `2026_08_21_100002:11-24`).
- Members: `StoreMembership` (`app/Models/Stores/Team/StoreMembership.php`) + `StoreMembershipPermission` (pivot string ids) + 3 static groups; Volts **per-store** member form (compensation fields to be added there in 38-B, per S2/S3); roles/permissions hybrid (static roles in `StoreRoles`/`StoreRoleEnum` + string permission grants + `SystemStatusesSeeder`… permissions group seeded globally in `StoreRolesAndPermissionsSeeder`). Permission assignment UI + global group grants + gates as listed in prior phases.
- 38-B needs: 4 finance permissions (`finance.view/manage/pay/close_period`); enum + lang ×4 + StoreRoles STAFF/MANAGER decision + mandatory seed; sidebar/report gating; form fields. Recipe fully documented from the members/groups work.
- `assigned_to_membership_id` on orders/trackings is the reliable "who" per order (nullable for unassigned/storefront).

### E. Money, cost, profit, timezone

- Costs: `products.cost_price`, `product_variants.cost_price` exist (`app/Models/Products/Product.php:...` costPriceAccessor; verified migration). Profit computed only at variant/product display level (`ProductCompleteness`/listing bucket); **no order-level profit** stored or computed.
- Discount: `discount_type` (percent|amount) + `discount_value` + `discount_reason` (`2026_08_26_000003:12-14`); `Order::discount_amount()` (`Order.php:238-253`) returns computed value.
- Rounding/totals: `decimal(10,2)` money, PHP `round(...,2)` mutation (G4 rework); **no integer-cents, no bcmath**; total is mutable platform-side (discount edits + status-timestamp edits) — a historical order's `total_amount` may drift from original COD receipt. **No currency column on orders** — currency is store-level (default DZD, `store_settings.currency`, `2025_12_29_000001`). Multi-currency never supported; store-level only.
- **No order-level tax**: taxes exist only on invoices (`tax_ids`, amounts), not per order.
- Expenses/ad-spend/cash accounts/suppliers/partners: **none**.
- Timezone: principle "Day boundaries use stores.timezone" — schema location is `store_settings.timezone` (default `Africa/Algiers`), **not `stores.timezone`** (correct in this finding; section 1 wording to be treated as intent). Canonical pattern: `DashboardFilterFactory` → `DashboardFilter` (store tz) → `DashboardOrderScope` + `DateBucket`. Inconsistent byways (raw `SET time_zone` in `ActivityLogService` / `SubscriptionUsageTracker`, `TrackingGridConcern` orders filter, `orders` filter and `FeatureUsageService`/commands use app tz) — 38-D must centralize tz at capture and pop in consumers.

### F. Infrastructure & out-of-HTTP (Q15)

- API surface (`routes/api.php`): `GET /api/v1/user` (auth:sanctum), `apiResource` products, `POST /webhooks/chargily` (throttle 60,1), `POST /webhooks/delivery/{provider}` (throttle 120,1). No order endpoints. Store context on API: `X-Store-Id` header/query → `StoreContext`.
- Outbox: **no outbox**; domain events all synchronous (none `ShouldQueue`); observers are the dominant hook (registered `AppServiceProvider.php:95-99`). Queue = `database` driver (`QUEUE_CONNECTION=database`, `.env`).
- Scheduler: `routes/console.php` (commands incl. queue:work spin for dev).
- Tenant isolation: explicit `where store_id` everywhere except **StoreScope global scope** (Product/Debt/DebtPayment) + HTTP middleware chain (`SetStoreContext` etc.). Orders rely on manual `store_id` writes by construction; a warning: `StoreScope::apply` leaves the query **unscoped** when `currentStore()` resolves null (admin/super-admin skip entirely) — safe only because RowScope + `hasStore` gates. 38-D earning entries must store `store_id` explicitly per row.
- `finance-manager` references: **only this plan and `docs/Todos.md:124-126`** (debts-port origin). `app/Support/Status/MathFinanceManager.php` is a status-label helper — name collision only, not Finance-Manager code.
- Q15 — tenant context outside HTTP:
  - Container singletons with no auto-clear: `StoreContext` (request-scoped bind on bootstrap; **no `Queue::before`/`Octane` reset hook**), `app('currentMembership')` instance bind, and these store-keyed caches — `canStore` memo (keyed `storeKey.'|'.$permission` after G6), `OrderService::$branchCache`, `OrderCompleteness::$exceptionsCache`, `OrderService::$totalCache`, `DeliveryRiderService::$listCache`, `StatusResolver::$keyCache`. In a long-lived worker they survive across jobs; a job in store A must not read store B's cache. Current jobs/commands carry `storeId` in payloads (delivery sync, carrier sync, notifications) — pattern to preserve. No job writes store-scoped rows via ambient `currentStore()`; write-side uses explicit `store_id`.
  - Jobs cannot resolve `currentStore()` from session/auth (none); they use payload `store_id` → recompute/`StoreContext::set()` scoped **inside** the job (never globally). 37-M analytics jobs same discipline.
  - Recommendation for 38-D listener: resolve store from the **event payload**, set `store_id` explicitly on `earning_entries`, never ambient state; add a `Queue::before`/resolver lane if Octane is ever added.

### Gaps (feed 38-C)

1. Order-level `confirmed_at`/`confirmed_by` (derived from history today; nullable, un-derivable after the fact).
2. Order-level `delivered_at`/`returned_at` (only on tracking rows; order status may lag or be missed).
3. Tracking-row actor: `created_by_membership_id` absent — who actually put the package on the rider is not captured (only history of *status* events).
4. History rows lack `from_status` and `source` (manual/bulk/carrier/webhook/api/storefront/system) — needed for reliable "did member confirm this order" attribution and for 38-D events.
5. History actor nullable on carrier/direct writes (see A.1) — commission attribution incomplete without a backfill/owner-fix lane.
6. Multiple tracking rows per order (non-unique tracking_number, post-close re-runs) — dedupe rule needed (first row wins per shipment).
7. No COD/payout fields at order level (COD collectible recomputed on the fly from mutable totals) — historical COD amount not snapshotted.
8. No order currency; no order-level profit; discount not always materialized — historical reconciliation best-effort.

### Risks

- Backfill cannot reconstruct the confirmer/tracked-by for carrier-driven or non-meta flows (null membership) → 38-D must mark those entries as un-attributed and report, not guess.
- Reassign-after-confirm + shift handovers mean "who confirmed" ≠ "current assignee" — commission on wrong field yields wrong earnings.
- Non-unique tracking rows: double-count risk; dedupe on first-created per order.
- Money purity: float + decimal(10,2) + mutable totals + no integer cents; rounding rule (D3) must be applied consistently, not historically.
- No outbox + synchronous events: a crash between order status write and earning-entry write loses the entry — 38-D should create the entry in the same transaction as the status transition (or accept an idempotent repair pass).
- Store-scoped singletons without reset: cross-store contamination in workers (Q15).
- `StoreScope` unscoped-on-null: only safe via gates; 38-D consumer queries must always bind store_id explicitly.

### Proposals for 38-B / 38-C / 38-D (scope)

- **Revised order (owner-approved, 2026-10-06)** — **38-C (capture) runs BEFORE 38-B, then 38-D**. Reason: un-captured actor/timestamps cannot be backfilled. Per-store default `accrual_start_date` = the **38-C deploy date**; older orders may later be manually reassigned from the "unattributed" bucket; **attribution is never guessed**. The final, implementation-ready 38-C scope is **section 13**.
- Original audit proposals (superseded by the approved scope; kept for rationale):
  - 38-C: `orders.confirmed_at`/`confirmed_by_membership_id` set on the first earningTriggers status event; `orders.delivered_at`/`returned_at` at transitions (first per order); history `from_status` + `source` captured in transition meta; `order_trackings.created_by_membership_id` in `startShipment`/`ensureRiderTracking`; COD collectible snapshot. All rows write `store_id` explicitly.
  - 38-B: `member_compensation_plans` (per store, versioned `effective_from`) + `store_finance_settings` + the 4 finance permissions + member-form compensation fields + sidebar gating; **build from scratch — no payroll/HR module exists to reuse or migrate**; plus an assessment-only evaluation of the shifts module for handover attribution.
  - 38-D: append-only `earning_entries` + listener inside the transition transaction + backfill CLI from `accrual_start_date`; confirmer via history, assignment-agnostic.
- **Phase order note**: 38-D forward capture starts at the 38-C deploy date; backfill covers orders from `store_finance_settings.accrual_start_date`.

### Recommended defaults for D1–D5 (with reasons) — superseded by the owner's decisions S8–S12 (section 11)

> Kept verbatim for the reasoning behind the approved decisions; **section 11 is authoritative.**

- **D1 (earning event)**: confirmed = status key `confirmed` reached via transition (history row created_at = event time); tracked = creation of the **first** `order_trackings` row (startShipment `created_at`; dedupe rule first-per-order) — matches the dashboard's `trackedOnly` existence semantics and avoids double count on re-shipments. Delivered-based triggers key off the new `orders.delivered_at` (38-C) for a single event time.
- **D2 (payroll period)** : default **monthly**. DZD retail, store_settings accrual, matches the permission `close_period`; weekly adds churn without revenue coupling. Custom-period support can come later.
- **D3 (rounding)** : per-entry `ROUND_HALF_UP` to the cent (2 dp), consistent with current `round(...,2)` total mutation (G4). Set once in `earning_entries.amount`.
- **D4 (delivery confirmation)**: **both**. Carrier status is not an authoritative completion today (carrier never writes order status); a merchant manual `delivered` transition remains the trigger, optionally assisted later by carrier `delivered_at`. This matches existing UI (rider/order confirm flow) and needs no behavior change.
- **D5 (return reasons)**: structured list seeded empty by default (merchant opts in); include at least: wrong address, customer refused, changed mind, damaged product, defective/wrong item. `return_reason` maps to member-fault only when the merchant marks it so; never infer from text/`inspection_result` alone.

### Corrections to earlier plan wording

- Section 1 "Day boundaries use `stores.timezone`" — schema stores it in `store_settings.timezone`; the principle stands, the table name is corrected here.
- Section 5 "`ordered_at`/`success_at` snapshotted at the event" — today history only keeps `created_at` + nullable membership; the snapshot columns are the 38-C additions above.
- Section 5 "order_trackings may have several rows per order (non-unique index): count once" — **confirmed** (non-unique index; `trackedOnly` counts the order once).
- 37-M contingency "history part needed earlier for event dates" — **not needed**: `order_status_histories.created_at` and `order_events.occurred_at` already provide the event time; 37-M can keep its original order.

### Checks (mirrored from section 9)

- a. Plan re-read end-to-end; every audit question answered with evidence; all `[VERIFY]` markers removed; tracker + phase log updated.
- d. `git diff` = only `docs/plans/financial-accounts.md`.
- g./h. 38-A row = **Done - verified** (2026-10-06); browser/craft not applicable (docs-only).
- i. Stopped here; results reported to the owner in the task response.

## 13. Owner approval of 38-A (2026-10-06): decisions S8–S12, revised phase order, final 38-C scope

Decision record S8–S12 lives in **section 11** (single source of truth). This section holds what the approval changes for the remaining phases and the **final implementation spec for 38-C**.

### Revised phase order (owner decision)

**38-C (capture) → 38-B (compensation model) → 38-D (earning ledger + backfill).**

Reason: un-captured actor/timestamps cannot be backfilled — capture ships first. Tracker rows are ordered accordingly (38-C above 38-B). Per-store default `accrual_start_date` = the **38-C deploy date**; the merchant may later manually assign older orders from the "unattributed" bucket; **attribution is never guessed**.

### Final 38-C scope (implementation spec — for owner review before the 38-C prompt)

**A. New columns** (migrations; all rows write `store_id` explicitly)
1. `orders.confirmed_at` (timestamp) + `orders.confirmed_by_membership_id` (nullable FK) — set on the **first** transition into any earningTriggers status (S8: any status of the group, not only `confirmed`).
2. `orders.delivered_at` (timestamp) — set on the **first** `delivered` transition (first per order).
3. `orders.returned_at` (timestamp) + `orders.return_reason_key` (string, nullable) + `orders.return_member_fault` (boolean, nullable) — set on the first `returned` transition; the S12 reason/fault fields are filled by the merchant.
4. `order_status_histories.from_status` (string key, nullable) + `order_status_histories.source` (enum `manual|bulk|carrier|webhook|api|storefront|system`) — captured in `Order::setTransitionMeta`/`transition` and written by `OrderObserver::handleStatusChange`.
5. `order_trackings.created_by_membership_id` (nullable FK) — set in `startShipment` (actor already threaded) and `ensureRiderTracking` (member resolved from context).
6. `orders.cod_collectible` (decimal(12,2), nullable) — snapshotted on the first earningTriggers event (S8); reconcilable later.

**B. Event model (S8, S11)**
- earningTriggers group = the statuses defined by section 4 (confirmed group, delivered, returned variants), **minus member-fault returns** (S12) so a member-fault return withholds the earning.
- Earning event: **FIRST** entry into any earningTriggers status; actor = that history row's membership. A null actor → the entry is written with `attribution = null` and lands in the **"unattributed"** bucket; it must **never** be credited to anyone. Per-entry idempotency key: `(order_id, membership_id, trigger)`.
- Tracking event: **exactly one per order, ever** (the first `order_trackings` row). Later rows — including after a closed shipment — never create another event.
- Delivery finalization (S11): carrier-sourced delivery **auto-finalizes**; manual delivery by a **non-owner** member → a **"pending review"** entry that is **excluded from the payable balance** until (a) owner approval or (b) carrier evidence arrives. Owner-performed manual delivery finalizes immediately.

**C. Worker-cache isolation (owner instruction 3)**
- Reset these per-store/per-request caches at the **start and end of every queued job** (and implicitly every scheduled command, which the scheduler already runs as its own subprocess):
  - `StoreContext` (container singleton)
  - `canStore` memo (`app/Helpers/helpers.php`, now backed by `StoreScopedCache::$canStore`/`$canStoreUser`; keyed `storeKey.'|'.$permission`)
  - every static memo found in 38-A: `OrderService::$branchCache` (+ new `$totalCache`), `OrderCompleteness::$exceptionsCache`, `DeliveryRiderService::$listCache`, `StatusResolver::$keyCache`
  - the `app('currentMembership')` instance binding
- Implemented as a **single orchestrator `StoreScopedCache::flush()`** wired once in `AppServiceProvider` via global queue events (`JobProcessing`/`JobProcessed`/`JobFailed` on a **non-"sync"** connection) — no job or command needs to opt in. Jobs dispatched inline via the sync driver (`dispatchSync`, unit tests) deliberately **share the enclosing request's context** and are exempt; this is why the storefront suites (which dispatch sync jobs mid-flow) stay green.
- **Deviation from the "job base / command wrapper" wording above:** global queue events replaced the base-class approach; command-boundary hooks were also considered but are redundant — the scheduler gives every scheduled command its own subprocess, so statics start fresh without a reset hook. This also eliminates the `Artisan::call`-in-tests problem (command events never fire there).

**D. Transaction-atomic earning capture seam (owner instruction 3)**
- The 38-D earning listener must write its `earning_entries` row **inside the same DB transaction as the status transition** (`OrderService::transition`/`transitionToStatus`). 38-C lays this seam (hook in `OrderObserver::handleStatusChange` / transition meta) **without** adding the ledger itself.

**E. Store finance settings (owned by 38-B, defined here)**
- `accrual_start_date` default = the **38-C deploy date** per store; the merchant can set it earlier and later manually assign older orders from "unattributed".
- S11 store flag (carrier-sourced auto-finalize) + S12 return-policy defaults.

**F. Tests (per section 9 gate)**
- Migrations forward + back; `from_status`/`source` captured on transition and on direct `status_id` writes; worker-cache reset verified with a cross-store job; first-entry-once dedupe (confirmed + tracking); "unattributed" bucket behaviour; `store_id` written on every new row; confirmed/delivered/returned first-occurrence stamps stable across re-transitions.

### 38-B additions (owner instructions 4 + 5)

- Build the compensation model **from scratch — nothing to reuse or migrate** (no payroll/HR module exists; verified in 38-A).
- Include an **assessment-only** evaluation of reusing the existing shifts module (`confirmation_shifts`) for attribution at shift handover. Output: a written recommendation; no code unless the recommendation survives review.

### Checks (section 9) for this addendum

- a/d/g/i applied; b/c/e/f/h N/A (docs-only). `git diff` shows only `docs/plans/financial-accounts.md`. Tracker rows re-ordered (38-C before 38-B); 38-A = **Done - verified** (`73f1ea2`).

### 38-C - Capture attribution/event fields + worker-cache isolation + earning seam - 2026-10-07 - (H2 code, H3 plan)

**Scope done**
- 5 new migrations (lt 2026_10_06_000001..000005): statuses.stage (enum pending/confirmed/in_delivery/delivered/returned/canceled/other, default other, resolver backfill); orders.confirmed_at/confirmed_by_membership_id/delivered_at/delivery_evidence_at/eturned_at/eturn_reason_key + 2 store-scoped indexes; order_status_histories.from_status + source; order_trackings.created_by_membership_id + cod_amount (+ index); store_settings.finance_capture_started_at (backfill at 38-C deploy).
- **Retrofit-first-write-wins** on the existing columns (	argeted_update_first_write in OrderObserver) with a CAS UPDATE ... WHERE confirmed_at IS NULL-style guard inside the transition's transaction; cod_amount/created_by_membership_id on order_trackings are snapshotted on first shipment and never rewritten (OrderTrackingService).
- **New enums/support:** OrderStatusStage (stage buckets; earning triggers = confirmed/preparing/processing/shipped/in_transit/out_for_delivery/delivered/completed only; refunded is stage confirmed but never an earner), OrderStatusCapture (earnings/attribution semantics docblock; first-occurrence rules; per-entry idempotency key (store_id, order_id, trigger) without membership so null-actor earners stay countable), StoreScopedCache (worker orchestrator, see section 13C).
- **Source bucket on every write:** transition signature + setTransitionMeta() + observer now carry rom_status + source (manual/bulk/carrier/webhook/api/storefront/system/userscript); unlabelled internal paths default to system. Merchant bulk UI (status_bulk_change), auto-cancel (orders:auto-cancel-pending), requeue, confirm-and-send gateway, manual createManual and the **storefront placement** (order-form.blade.php: rom_status = null, source = storefront) all pass their source through.
- Worker-cache isolation: StoreScopedCache::flush() wired once in AppServiceProvider to JobProcessing/JobProcessed/JobFailed on **non-"sync"** connections (jobs dispatched inline via sync share the request context; scheduled commands run as their own subprocess). canStore memo moved from function-local statics into StoreScopedCache so the orchestrator can reach it.
- **New tests (5 files, 26 tests, 115 assertions) in 	ests/Feature/Finance/:** FinancialCaptureSchemaTest (capture columns+indexes, stage resolver backfill, store_settings capture-date, forward+back migrations preserving pre-existing rows), OrderStatusCaptureTest (first-confirm-wins across cancel/re-confirm, CAS double-stamp winner, null-actor unattributed, delivered vs carrier-evidence separation, returned stamp+reason immutability, custom status = stage other fires nothing, COD snapshot non-rewrite, non-COD null, exactly-one shipping event), OrderTransitionSourceTest (createManual/bulk/manual/system requeue/auto-cancel/gateway/storefront sources), WorkerCacheIsolationTest (direct flush, worker-boundary events, inline-sync exemption), OrderStatusWriteArchitectureTest (app-wide status-write allowlist = OrderService+OrderObserver; blade writes only storefront order-form.blade.php creation).
- **Regression fixed (bonus):** the historical storefront CartService::getItems(null) ViewException flakiness (29 baseline failures) is gone � full Storefront suite 123/123; root cause was StoreContext being wiped mid-request by job-boundary flushes of the new hooks; the sync-exemption made it deterministic.

**Deviations** - worker-cache implementation uses global queue events, not the "job base / command wrapper" wording (section 13C rationale); command-boundary hooks dropped (scheduler subprocess isolation + Artisan::call never fires them in tests). SQLite DROP COLUMN rebuilds a table, and rebuilding orders fires its FK ON DELETE CASCADE to children � so the rollback test asserts row-preservation for the tables whose capture columns roll back directly (tracking/history/store settings) and reversibility-only for orders/statuses (MySQL ALTER TABLE is unaffected). OrderService/OrderTrackingService static memos hoisted to class statics (ranchCache/	otalCache) so lush() can reset them.

**Checks**
- a. Full suite (php.ini memory_limit=-1, restored to 512M): **1324 passed, 0 failed, 0 risky** (1,155,419 assertions, 495s). Pre-change baseline was 1298 pass / green; the 48-failure storefront/cart/routing set from 37-K's baseline is now entirely green.
- b. Per-directory: Merchant + Routing 858 passed; Storefront 123 passed; Finance 26 passed.
- c. endor/bin/pint --test on all 15 touched/new files: **PASS**.
- d. git diff limited to the 38-C migrations, order/status/tracking/shipping services, observer, helpers (canStore memo), AppServiceProvider, HasStoreDefaults, storefront+merchant order blades, seeder, and the 5 new Finance test files. No routes, permissions, or unrelated behavior changes.
- e. Phase-scoped accuracy: first-write CAS + one-shipping-event tests prove no re-run duplicates; MySQL-style stage keys tested; multi-tenant isolation re-proven by the full storefront/routing suites (each store only sees its own context).
- f. N/A (no user-facing UI; storefront/merchant flats unchanged visually).
- g. i. Tracker row updated above; rollback plan = migrate:rollback --step=5 (columns are additive/nullable � safe on MySQL; dev DB re-rolled + re-migrated for the stage default parity).
- h. N/A (no deps/security impact).

**Open issues** - none blocking. 38-D must write earning entries inside the transition's transaction and resolve stores from event payloads (never ambient context, section 12 warning).
