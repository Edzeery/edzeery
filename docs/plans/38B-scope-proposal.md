# PHASE 38-B: SCOPE PROPOSAL (docs only, no implementation)

## Overview
This is a full specification for Phase 38-B. No code is to be implemented now; this only proposes scope, data model, files, tests, risks, and open questions for review. Prerequisite: 38-A verified, 38-C done.

## 1) Compensation plans (versioned, audited)
**Objective:** Versioned plans with effective_from, per-membership assignment, immutable history, full audit (who/when/old/new).

### Tables/columns
- compensation_plans (id ulid PK, store_id FK, name, slug, active bool, plan_type enum[confirmation,tracking,hybrid], effective_from datetime, effective_to datetime nullable, created_by_membership_id, updated_by_membership_id, version int, published_at nullable, notes, timestamps, soft_deletes). Unique (store_id, slug). Indexes (store_id, active, effective_from).
- compensation_plan_versions (id ulid PK, compensation_plan_id FK, version int, effective_from datetime, config json, created_by_membership_id, approval_state enum[draft,pending,approved,rejected], approved_at, approved_by_membership_id, config_hash char(64), timestamps). Index (plan_id, version desc).
- compensation_plan_assignments (id ulid PK, store_id FK, membership_id FK, compensation_plan_id FK, compensation_plan_version_id nullable, effective_from datetime, effective_to datetime nullable, reason, assigned_by_membership_id, timestamps). Indexes (store_id,membership_id,effective_from), (store_id,effective_from).
- compensation_plan_audits (id ulid PK, store_id FK, subject_type enum[plan,plan_version,assignment], subject_id, action (create/update/archive/assign/unassign/publish/approve), before json, after json, actor_membership_id nullable, ip, user_agent, occurred_at). Indexes (store_id,subject_type,subject_id,occurred_at).

### Rules
- Effective windows: plan versions/assignments use half-open or inclusive as per S9; history never overwritten (create new version/assignment on change). Effective_to set on deactivation/replace, never deleted.
- Audit: every create/update/archive/publish/assign/unassign/approve writes before/after with actor_membership_id.

### Files (likely)
- Migrations: 4 new migrations (2026_10_06_? or next sequence) for above tables.
- Models: pp/Models/Compensation/CompensationPlan*.php, CompensationPlanAssignment.php, CompensationPlanAudit.php (+ traits).
- Policies/Gates: CompensationPlanPolicy + register in AuthServiceProvider.
- Observers/Services: CompensationPlanService, CompensationPlanAuditService.
- Enums: Enums/Finance/CompensationPlanType, Enums/Finance/ApprovalState, Enums/Finance/AuditSubject.

### Tests (proposed)
- Versioning: creating new version bumps version; old version remains immutable.
- Assignment history: replacing assignment closes old with effective_to, creates new.
- Audit trail: changes record before/after and actor.
- Effective window resolution: at time T pick correct version/assignment.
- Tenant isolation: store cannot see another's plans/assignments/audits.

### Risks/Open questions
- Effective dating strategy (timezone: use app timezone per store vs UTC). Need to confirm.
- Concurrent publish/versioning (optimistic locking). Q: require approval workflow?

## 2) Role triggers & formulas (S2/S3, S10)
**Objective:** Confirmation pays per CONFIRMED OR per CONFIRMED-AND-DELIVERED; Tracking pays per TRACKED OR per TRACKED-AND-DELIVERED. Formulas fixed/percent/tiers, optional base salary, decimal(12,2), rounding per S10.

### Triggers (configurable per plan/role)
- Confirmation role trigger: confirmed_only | confirmed_and_delivered (default per S2/S3 decision; store-configurable per plan).
- Tracking role trigger: 	racked_only | 	racked_and_delivered (define "tracked" = has ≥1 order_trackings row at evaluation time; use capture seam).
- Currency: inherit store currency; amounts decimal(12,2).

### Formula types
- Fixed per order: mount_per_order (decimal 12,2).
- Percentage of order value: percent_of_order_value (0-100, decimal 6,2), apply to order total/net as defined (exclude shipping? discount handling). Proposal: base = order goods subtotal + shipping - discount (matches COD formula), configurable.
- Tiers: 	iers array [{min, max, type, amount, percent}, ...], first-match or range.
- Optional base salary (monthly/period) separate from per-order.

### Config schema (json)
`json
{
  "confirmation": {"trigger":"confirmed_only|confirmed_and_delivered","fixed":50.00,"percent":0,"tiers":[]},
  "tracking": {"trigger":"tracked_only|tracked_and_delivered","fixed":25.00,"percent":0,"tiers":[]},
  "base_salary": {"enabled":false,"period":"monthly","amount":0,"currency":"DZD"},
  "rounding": {"mode":"bankers|half_up","precision":2},
  "eligibility": {"exclude_canceled":true,"exclude_refunded":true,"min_order_amount":0}
}
`

### Files/Tests/Risks
- Support classes: pp/Domains/Finance/Support/CompensationFormula.php, CompensationTriggerResolver.php.
- Tests: trigger matrix (confirmed vs delivered combinations), formula application, tiers edge cases, rounding S10, exclusion rules.
- Risks: "tracked" definition must use 38-C captured fields; avoid double-counting (future ledger). Q: confirm percentage base.

## 3) Member add/edit form integration (Livewire/Volt, size limits, RTL)
**Objective:** Extend existing member management form to include compensation assignment. Respect size limits (Volt ≤400, partials ≤300), RTL/LTR.

### Integration points
- Existing: member add/edit form (Livewire/Volt) under merchant/store team. Identify component path: esources/views/livewire/merchant/members/* or Volt pages esources/views/livewire/merchant/store-members/* (check conventions).
- Add section "Compensation" (collapsed or tab) showing current assignment, effective_from, plan/version, history.

### Fields
- Compensation plan (select: active published plans for store), version (auto latest published at effective date), effective_from (datetime/local), reason, effective_to (only when replacing/ending).

### Validation/permissions
- inance.manage required to assign/change plans. Read with inance.view.
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

## 4) Store finance settings
**Objective:** Add finance settings per store.

### Fields (StoreSetting model extension)
- inance_capture_started_at exists (38-C). 
- ccrual_start_date datetime nullable (default = inance_capture_started_at at creation/time of first finance enablement; merchant can edit). Rationale: per S11/S9 and 38-C note.
- payroll_period enum[weekly,biweekly,monthly,custom] default per S9 (propose monthly until specified), payroll_period_start_day/payroll_period_anchor if custom.
- eturn_policy_default string/enum (S12), eturn_grace_window_hours int (default 24-72? propose 48, configurable).
- carrier_auto_finalize_enabled bool (S11 flag) default false.
- inance_enabled bool (feature flag), inance_close_requires_approval bool.

### Migration/Model
- Migration adds columns to store_settings (nullable/additive). Update StoreSetting fillable/casts.
- Defaulting: when finance enabled first time, set ccrual_start_date = finance_capture_started_at ?? now() (non-destructive).

### Files/Tests
- Migration, model update, settings UI (store settings page) with finance tab gated by inance.manage.
- Tests: defaults, validation, multi-tenant.

### Qs
- Exact payroll_period values per S9? Default recommendation: monthly.

## 5) Structured return reasons + per-reason member-fault flag (S12)
**Objective:** Store-scoped return reasons list with "apply suggested template" and per-reason member-fault flag.

### Tables
- eturn_reasons (id ulid, store_id FK, key, label, description, member_fault bool default false, affects_compensation bool default true, sort_order int, is_active bool, is_system bool default false, timestamps). Unique (store_id,key).

### Seeding/templates
- "Apply suggested template" action: populate common reasons per store (non-destructive, can merge). System defaults suggested (damaged, wrong item, customer change, undeliverable, quality) with member_fault flags proposed per reason.

### Integration
- Use eturn_reason_key on orders (exists via 38-C). Dropdowns read from this list (active). When returning, store key.
- Compensation: if member_fault true for return reason, flag for evaluation (future 38-D; this phase only models structure/UI).

### Files
- Migration, model pp/Models/Finance/ReturnReason.php, service, CRUD Livewire/Volt for store settings → return reasons (gated finance.manage).
- Translations.

### Tests
- CRUD, unique key per store, sort order, template apply idempotent.
- Integration: order return can reference reason key.

### Qs
- Suggested template contents and member-fault mapping.

## 6) Map custom statuses to stage (statuses.stage) + warning
**Objective:** UI to map custom statuses to lifecycle stage; warn when a used status remains 'other'.

### Data
- Existing statuses.stage (string) with enum values: pending/confirmed/in_delivery/delivered/returned/canceled/other (38-C). Custom store statuses may be 'other'.

### UI
- Store → Statuses management: for order type, show stage select (restricted to valid buckets). For system statuses read-only (or show derived). 
- Warning banner: "X custom statuses currently used by orders remain stage 'other' — mapping them is recommended for capture/compensation accuracy."
- Compute "used by orders" since capture start or globally (count distinct orders with that status in last N or any). Use existing queries.

### Validation/logic
- Only order-type custom statuses editable for stage; system order statuses derived via OrderStatusStage (read-only). Non-order remain other.
- Changing stage does not rewrite history; affects future classification (metrics/compensation).

### Files
- Extend statuses management UI (Volt/Livewire). Service to bulk update stage with audit? Minimal audit: log who changed mapping (settings audit or status_events). 
- Tests: mapping updates stage, warning appears when used other exists, system protected.

### Risks/Qs
- Impact on existing reports (stage is derived; stored override allowed? Proposal: stored value is source of truth for custom; system can be regenerated by seeder but UI read-only).

## 7) Permissions
**Objective:** Add inance.view/manage/pay/close_period consistent with existing system.

### Enum/Seeds/Gates
- Add to StorePermissionEnum (or equivalent) values: inance.view, inance.manage, inance.pay, inance.close_period.
- Language files AR/EN/FR under lang/*/permissions.php (or store permissions).
- Update database/seeders/StoreRolesAndPermissionsSeeder.php: grant to roles (owner/manager/accountant/finance roles). Follow existing pattern.
- Register gates in AuthServiceProvider (or use Spatie permissions). 
- Middleware/policies: use existing can/canStore helpers.

### Files/Tests
- Enum, seeder update, lang, policies if needed.
- Tests: role has expected finance permissions, unauthorized blocked.

## 8) Assessment-only: reuse shifts module for handover attribution (no implementation)
**Objective:** Assess reusing confirmation_shifts for handover attribution. Output written recommendation only.

### Scope of assessment
- Analyze confirmation_shifts schema (tables, relations, shift boundaries, membership assignments, handover events), pp/Models/ConfirmationShifts/*, usage, timezone, how attribution is recorded.
- Evaluate: can shift membership at transition time determine actor for confirmed/delivered handovers? Gaps (late handover, multi-shift, reassignments after shift close).
- Pros/cons vs explicit compensation assignments. Impact on 38-C capture (null-actor). 
- Deliverable: written recommendation in plan/doc (no code). Include tables, migration impact, feasibility, risks.

### Files
- None new (docs only).

## 9) Explicitly out of scope (per requirement)
- Ledger, payroll runs, page shell "الحسابات المالية", finance-manager API. These belong to 38-D onward.

## 10) Cross-cutting: sizes, RTL, tests, risks, open questions
**Size limits:** Volt ≤400, partials ≤300, PHP classes ≤250, services ≤250 — enforced in design (split components).
**RTL/LTR:** all new UI respects direction, use existing components.
**Multi-tenant:** store_id on all new tables, scope by currentStoreId, policies enforce.
**Accuracy:** effective windows, immutable history, audit before/after.

### Open questions (for review)
1. Compensation percentage base (goods+shipping-discount vs subtotal)? 
2. Triggers defaults (S2/S3) if not specified? 
3. Payroll period default (S9)? monthly proposed.
4. Rounding mode (bankers vs half_up) per S10?
5. Return grace window default (48h)? member-fault template mapping?
6. Status stage override: allow system order statuses UI edit or read-only? (propose read-only, derived).
7. Approval workflow for plan versions (draft→approved)?
8. Effective window boundary (inclusive/exclusive end)? 
9. Base salary period granularity?
10. Assessment outcome of shifts reuse (go/no-go) — what criteria decide?

### High-level risks
- Effective dating/timezones (DST). Use Carbon with store timezone, store UTC in DB.
- Migration sequence (4 new compensation tables + return_reasons + store_settings columns) — additive only.
- Backward compatibility (existing members get no assignment until set).
- Performance: effective resolution at scale (index effective_from windows).
