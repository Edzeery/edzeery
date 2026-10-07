# PHASE 38-B: SCOPE PROPOSAL (docs only, no implementation)

## Overview
This is a full specification for Phase 38-B. No code is to be implemented now; this only proposes scope, data model, files, tests, risks, and open questions for review. Prerequisite: 38-A verified, 38-C done.

## 1) Compensation plans (append-only terms, audited as specified)

**Objective:** Use append-only terms compensation_terms (latest effective_from <= T in force). No plan_type, no versions, no assignments tables, no approval workflow, no stored effective_to. History is immutable by append-only rows.

### Tables/columns

- compensation_terms (id ulid PK, store_id FK, membership_id FK, effective_from datetime, created_by_membership_id nullable, created_at, config json, config_hash char(64)). Unique (store_id, membership_id, effective_from). Indexes (store_id, membership_id, effective_from desc), (store_id, effective_from).
- fffinance_audit_logs (id ulid PK, store_id FK, actor_membership_id nullable, subject_type enum[store_settings,rreturn_reason,status_stage_mapping], subject_id, action, before json, after json, occurred_at). Indexes (store_id, subject_type, subject_id, occurred_at), (store_id, occurred_at).

### Rules

- Term resolution (instant T, UTC): the term in force at T for a member = row with latest effective_from <= T. No effective_to stored (half-open [from, next_from) by definition).
- Append-only: changes create a new term row with later effective_from; existing rows are never modified/deleted.
- No retroactive effect: effective_from >= aaccrual_start_date and >= the member's previous term's effective_from (38-D also requires after last ledger entry).
- Terms in force are those at the moment of the attributed event (confirmation or tracking), not at delivery.
- Config contains BOTH confirmation and tracking sections, plus base_salary_amount (decimal 12,2), base_salary_period (enum[monthly,weekly,biweekly,custom]), plus optional rreturn_policy_override.
- fffinance_audit_logs is for MUTABLE things only (fffinance settings, return reasons, status->stage mapping). Terms do not require an audit table (append-only rows record who/when via created_by_membership_id/created_at).## 2) Role triggers, formulas, rounding (per decisions)

**Objective:** Explicit choice per role per member (no defaults). Fixed/percent only for 38-B; ttiers removed (reserved for post-ledger). HALF_UP rounding only, currency from store. Percentage base goods after discount excluding shipping by default, with option cod_total. Computed in one service (OrderMoney) because orders.subtotal is dead; chosen method/base snapshotted in ledger (38-D).

### Triggers

- Confirmation role: confirmed_only | confirmed_and_delivered | 
one (explicit; no default).
- Tracking role: 	tracked_only | 	racked_and_delivered | 
one (explicit; no default). "Tracked" = has ≥1 order_trackings row at evaluation time (use 38-C capture).

### Formulas (38-B)

- Fixed per order: amount_per_order (decimal 12,2).
- Percentage: percent_of_order_value (0-100, decimal 6,2). Base: goods after discount excluding shipping (default); option cod_total. 
- Base salary optional: ase_salary_amount (decimal 12,2), ase_salary_period (monthly/weekly/biweekly/custom).
- **Ttiers REMOVED from 38-B** (retroactivity conflicts with append-only ledger). Reserve note to add marginal ttiers after ledger stable.

### Rounding & currency

- Rounding: HALF_UP only (per S10). No per-plan rounding mode.
- Currency: store currency only; no per-plan currency.

### Config example (term.config)
`json
{
  "confirmation": {"trigger": "confirmed_only", "fixed": 50.00, "percent": 0.00},
  "tracking": {"trigger": "none", "fixed": 0.00, "percent": 0.00},
  "base_salary_amount": 0.00,
  "base_salary_period": "monthly",
  "percentage_base": "goods_after_discount_ex_shipping",
  "rreturn_policy_override": null
}
`

### Files

- pp/Domains/fffinance/Support/OrderMoney.php (single service to compute percentage base and amounts)
- pp/Domains/fffinance/Support/CompensationTriggerResolver.php
- pp/Domains/fffinance/Support/CompensationFormula.php

### Tests

- Trigger matrix (confirmed_only/confirmed_and_delivered/none, tracked variants), explicit choices required.
- Percentage base: goods after discount excluding shipping; cod_total option.
- HALF_UP rounding (S10). No ttiers tested in 38-B.
- Exclusion rules (canceled/refunded) per eligibility config if present.

### Risks/Open questions

- OrderMoney must not rely on deprecated orders.subtotal (compute from items or stored fields). Q: confirm cod_total definition matches COD snapshot.## 3) Member add/edit form integration (Livewire/Volt, size limits, RTL)
**Objective:** Extend existing member management form to include compensation assignment. Respect size limits (Volt ≤400, partials ≤300), RTL/LTR.

### Integration points
- Existing: member add/edit form (Livewire/Volt) under merchant/store team. Identify component path: rrrrresources/views/livewire/merchant/members/* or Volt pages rrrrresources/views/livewire/merchant/store-members/* (check conventions).
- Add section "Compensation" (collapsed or tab) showing current assignment, effective_from, plan/version, history.

### Fields
- Compensation plan (select: active published plans for store), version (auto latest published at effective date), effective_from (datetime/local), reason, effective_to (only when replacing/ending).

### Validation/permissions
- .manage required to assign/change plans. Read with .view.
- Cannot assign plan with effective_from in the past in a way that overwrites closed history (must create new assignment replacing current).
- Plan must be active and effective_from >= plan effective_from.

### Files
- Form component/Volt: extend existing member form (add fields + state). Keep within size limits.
- Translations AR/EN/FR as needed, RTL-safe.
- Policy checks via gates.

### Tests
- Form renders with compensation section (permissions), validation rejects invalid windows, assignment persisted with audit.

### Risks/Qs
- Avoid duplicating member form; extend via partial/component. Q: placement in form UI.

## 4) Store ffffinance settings
**Objective:** Add ffffinance settings per store.

### Fields (StoreSetting model extension)
- ffffinance_capture_started_at exists (38-C). 
- aaaaccrual_start_date datetime nullable (default = ffffinance_capture_started_at at creation/time of first ffffinance enablement; merchant can edit). Rationale: per S11/S9 and 38-C note.
- payroll_period enum[weekly,biweekly,monthly,custom] default per S9 (propose monthly until specified), payroll_period_start_day/payroll_period_anchor if custom.
- rrrrrrreturn_policy_default string/enum (S12), rrrrrrreturn_grace_window_hours int (default 24-72? propose 48, configurable).
- carrier_auto_finalize_enabled bool (S11 flag) default false.
- ffffinance_enabled bool (feature flag), ffffinance_close_requires_approval bool.

### Migration/Model
- Migration adds columns to store_settings (nullable/additive). Update StoreSetting fillable/casts.
- Defaulting: when ffffinance enabled first time, set aaaaccrual_start_date = _capture_started_at ?? now() (non-destructive).

### Files/Tests
- Migration, model update, settings UI (store settings page) with ffffinance tab gated by .manage.
- Tests: defaults, validation, multi-tenant.

### Qs
- Exact payroll_period values per S9? Default recommendation: monthly.

## 5) Structured rreturn reasons + per-reason member-fault flag (S12)
**Objective:** Store-scoped rreturn reasons list with "apply suggested template" and per-reason member-fault flag.

### Tables
- rrrrrrreturn_reasons (id ulid, store_id FK, key, label, description, member_fault bool default false, affects_compensation bool default true, sort_order int, is_active bool, is_system bool default false, timestamps). Unique (store_id,key).

### Seeding/templates
- "Apply suggested template" action: populate common reasons per store (non-destructive, can merge). System defaults suggested (damaged, wrong item, customer change, undeliverable, quality) with member_fault flags proposed per reason.

### Integration
- Use rrrrrrreturn_reason_key on orders (exists via 38-C). Dropdowns read from this list (active). When rreturning, store key.
- Compensation: if member_fault true for rreturn reason, flag for evaluation (future 38-D; this phase only models structure/UI).

### Files
- Migration, model aaaaapp/Models/ffffinance/RreturnReason.php, service, CRUD Livewire/Volt for store settings → rreturn reasons (gated ffffinance.manage).
- Translations.

### Tests
- CRUD, unique key per store, sort order, template apply idempotent.
- Integration: order rreturn can reference reason key.

### Qs
- Suggested template contents and member-fault mapping.

## 6) Map custom statuses to stage (statuses.stage) + warning

**Objective:** UI to map custom statuses to lifecycle stage. System statuses read-only; custom editable only with fffinance.manage. Every change audited; explicit notice about past orders.

### Rules

- Existing statuses.stage enum: pending/confirmed/in_delivery/delivered/returned/canceled/other (38-C). 
- System order statuses: read-only (derived via OrderStatusStage). Non-order remain other.
- Custom order statuses: editable to valid stage with fffinance.manage. Changing stage does not rewrite history; affects future classification.
- Audit: every change logged in fffinance_audit_logs (subject_type status_stage_mapping, before/after, actor).
- Warning banner: show when any used custom status remains other ("does not apply to past orders").

### Files/Tests

- Extend statuses management UI (Volt/Livewire), size limits respected. 
- Tests: mapping updates stage, warning appears, system protected, audit written.## 7) Permissions

**Objective:** fffinance.view/manage/pay/close_period consistent with repo. Owner only by default; no invented roles. Use existing permission mechanism (canStore / permission enum). No Spatie/AuthServiceProvider assumptions. Member self-view in 38-L.

### Implementation

- Add to StorePermissionEnum values: fffinance.view, fffinance.manage, fffinance.pay, fffinance.close_period.
- Lang files AR/EN/FR under lang/*/permissions.php (or store permissions as per repo).
- Update database/seeders/StoreRolesAndPermissionsSeeder.php: grant to owner only by default (no other roles). 
- Use existing gates/middleware/policies; do not assume Spatie facade names unless present.
- Member form compensation section hidden (not disabled) without fffinance.manage; never exposes other members' terms to users lacking fffinance.manage.

### Files/Tests

- Enum, seeder, lang, policies if needed. 
- Tests: owner has permissions, unauthorized blocked, UI hidden correctly.## 8) Assessment-only: reuse shifts module for handover attribution (no implementation)

**Objective:** Assess reusing confirmation_shifts. Go only if fffinance:capture-health shows material share of confirmed orders where human acted but no actor recorded; if ~0 decision is NO-GO.

### Assessment scope

- Analyze confirmation_shifts schema/relations, usage, timezone, handover events.
- Can shift membership at transition time determine actor for confirmed/delivered handovers? Gaps (late handover, multi-shift, reassignments).
- Pros/cons vs explicit terms. Impact on 38-C null-actor.
- Deliverable: written recommendation only.## 9) Explicitly out of scope (per requirement)
- Ledger, payroll runs, page shell "الحسابات المالية", ffffinance-manager API. These belong to 38-D onward.


## 10) Implementation splits: 38-B1 and 38-B2

### 38-B1 (backend, no UI)
- Migrations: compensation_terms, fffinance_audit_logs, return_reasons, store_settings columns.
- Models: app/Models/fffinance/CompensationTerm.php, fffinanceAuditLog.php, ReturnReason.php.
- Services: OrderMoney, CompensationTriggerResolver, CompensationFormula, CompensationTermResolver, fffinanceAuditWriter, ReturnReasonService.
- Permissions/enum/seeder + backend tests.

### 38-B2 (UI)
- Member form child Volt component (hidden without fffinance.manage), append-only term creation.
- fffinance settings tab; return reasons CRUD with template; status->stage mapping UI with audit/warning.
- Translations AR/EN/FR, RTL, responsive (375/768/1440).

## 11) Cross-cutting constraints
- Size limits: Volt ≤400, partials ≤300, PHP classes ≤250, services ≤250.
- Responsive: 375/768/1440. RTL/LTR.
- Multi-tenant: store_id on all new tables, scope by currentStoreId, policies enforce.
- Performance: avoid extra queries/re-renders; note risks.
- Audit: only mutable things in fffinance_audit_logs; terms append-only.
- SQLite vs MySQL: test migrate/rollback on MySQL copy; watch FK/order table rebuilds.
- New stores: provisioner must set stage correctly for system statuses.
- Sync guard: worker-only flush is correct if QUEUE_CONNECTION != sync.
- Shifts go/no-go: based on fffinance:capture-health null-actor share.










