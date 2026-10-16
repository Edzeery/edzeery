# Phase E.1 - Delivery Rider Portal (Foundation) Planning Document

## Overview
This document prepares implementation of the rider portal foundation (rider login, per-store rider accounts, invitation flow) with approved decisions D1-D3 and decision matrix for D4-D7. No application code is written in this phase.

Status: ANALYSIS & PREPARATION ONLY (complies with task constraints)


## 1) Readiness Report

### 1.a) Existing plan for Phase E/E.1
- Phase E/E.1 plan: NOT FOUND
- Searched: docs/plans/, docs/ (glob for rider, portal, E.1, phase-e)
- Found: Only remediation status/docs; no E/E.1 planning doc exists.

### 1.b) Blocker/dependency classification
S-04 (store-ownership checks on Livewire typed-model params): SOFT DEPENDENCY. E.1 adds rider auth/portal (separate guard, not touching merchant Livewire typed models directly). No direct conflict with 01-security.md:95. Can proceed with preparation; verify if any E.1 Livewire components use typed params against DeliveryRider/Store - none planned yet.

T-02 batches (1)-(4): T-02 Foundation closed but (1)-(4) open. (1) models/relations: DeliveryRider exists (Model, HasUlids, SoftDeletes, scopeForStore). (2) scopes/filters: TrackingGridConcern uses rider filters; scoping exists. (3) policies/permissions: StorePermissionEnum has delivery.riders.*; group exists. (4) factories/seeders: may exist or not - check not required for analysis. No direct blocker for E.1 analysis.

S-03: closed. 
PR-01..PR-11, A-03..A-05: open; A-04=2FA - separate from rider portal auth design (consider D4-D7). 
No known hard blockers for analysis/preparation.

### 1.c) Current auth baseline (evidence)
- config/auth.php:38-43 guards web,merchant on users provider; 66-67 provider users. No rider guard/provider.
- App\Http\Requests\Auth\LoginRequest: validates email required+email, password required; ensureIsNotRateLimited uses RateLimiter (auth-login). 
- App\Http\Controllers\Auth\AuthenticatedSessionController: Auth::attempt, redirect intended.
- Rate limiters: AppServiceProvider ~138-159 (auth-login, auth-register, auth-password-email, auth-password-reset).
- Password defaults: AppServiceProvider ~174 Password::min(12)->mixedCase()->numbers()->symbols().
- AuthenticateSession in web middleware group (bootstrap/app.php:36).
- DeliveryRider model: not Authenticatable; scopeForStore() exists (app/Domains/Shipping/Models/DeliveryRider.php). Fillable: store_id,name,phone,email,vehicle_type,is_active,notes.
- Migration: 2026_09_01_100001_create_delivery_riders_table.php - store_id foreignUlid, index ['store_id','is_active'] (line ~28), no unique on phone/email; phone required, email nullable; soft deletes, timestamps.

### 1.d) Rider surface & scoping matrix (read paths)
Key paths using riders:
- Merchant riders page: routes/merchant.php:104 -> Volt/view resources/views/livewire/merchant/delivery/riders.blade.php (305 lines). Permissions delivery.riders.view/create/update/delete (StorePermissionEnum ~97-100), order.dispatch.rider (~53).
- Assignment: TrackingRiderFormConcern::assignRider (app/Livewire/Concerns/TrackingRiderFormConcern.php:27). 
- Tracking: OrderTrackingService::ensureRiderTracking (app/Domains/Order/Services/OrderTrackingService.php:110). TrackingGridConcern::baseTrackingQuery applies rider filters (~32, ~142-150) and restricted-membership scoping (~150).
- DeliveryRider::scopeForStore() (model ~63) - explicit store scoping.
All merchant-side operations go through store context + middleware (ResolveStoreFromRoute, EnsureStoreMembership, EnsureHasStoreRole, EnsureStoreIsActive). No rider guard currently.


### 1.e) Data audit (read-only SQL, MySQL target)
Use MySQL-compatible queries (read-only). Run against target DB only if needed; examples:

- Duplicates per store (phone): 
  SELECT store_id, phone, COUNT(*) c FROM delivery_riders WHERE deleted_at IS NULL GROUP BY store_id, phone HAVING c > 1;
- Duplicates per store (email, non-null): 
  SELECT store_id, email, COUNT(*) c FROM delivery_riders WHERE deleted_at IS NULL AND email IS NOT NULL GROUP BY store_id, email HAVING c > 1;
- Soft-deleted riders: 
  SELECT id, store_id, name, phone, email, deleted_at FROM delivery_riders WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC LIMIT 50;
- NULL emails: 
  SELECT store_id, COUNT(*) c FROM delivery_riders WHERE deleted_at IS NULL AND (email IS NULL OR email='') GROUP BY store_id;
- Phone normalization violations (Algerian-like pattern per existing usage ^0[5-7]\d{8}$): 
  SELECT id, store_id, phone FROM delivery_riders WHERE deleted_at IS NULL AND phone NOT REGEXP '^0[5-7][0-9]{8}$';

Notes: No unique constraints on phone/email; email nullable. Soft deletes present.

Baseline tests: Merchant rider mgmt + order dispatch rider: 18 passed (87 assertions). Tenant+Security: 26 passed (40). Auth: 43 passed (120). Total rider+tenant+security: 44 passed. All green with PHP 8.3.28/Pest.

Verdict: READY TO START (analysis only). No hard blockers identified. D4-D7 require decisions (see matrix). Soft dependency S-04 noted.

ARCHITECTURE_RULES.md: NOT FOUND in repo. File-size checks not assuming limits; only existing files cited (e.g. riders.blade.php 305 lines). No size violations asserted.


## 2) Planning

### Approved decisions (D1-D3)
- D1: Identity model - DeliveryRider remains domain model; introduce RiderAccount (separate Authenticatable) linked to DeliveryRider (per-store). Do not make DeliveryRider Authenticatable (keeps domain purity, avoids auth concerns mixed). Rationale: existing model not Authenticatable, scopeForStore explicit, soft deletes, multiple stores per rider impossible by design (per-store accounts). 
- D2: Auth guard - dedicated 'rider' guard + 'riders' provider (separate from web/merchant). Reuse rate limiters (auth-login), password policy (AppServiceProvider defaults), non-enumerating errors, throttle on login.
- D3: Credentials/invitations - per-store rider accounts created via invitation (no public self-registration). Reuse pattern: StoreTeamService::addMember creates account with random hashed password + mail; adapt to RiderAccount + invitation token (hashed, single-use, expiring). Password required only on acceptance. Deactivation/soft-deleted rider must lock out immediately (including existing sessions).

### D4-D7 Decision Matrix (OPTIONS ONLY - do not decide)
D4: Login identifier (phone-or-email vs email-only vs phone-only)
- Option A (Recommended for UX): phone-or-email with normalization (strip spaces/+00, enforce ^0[5-7]\d{8}$). Impact: needs normalization util, validation rules. Risk: enumeration if not careful (use generic errors). 
- Option B: email-only (simple, matches existing). Impact: breaks riders with only phone; migration/backfill. Risk: excludes existing nullable-email cases. 
- Option C: phone-only. Impact: email optional only for contact. Risk: lost email recovery unless added.

D5: Unique constraints (per-store)
- Option A (Recommended): enforce per-store uniqueness on phone (and email if not null) considering soft deletes. Impact: migration changes (partial unique or filtered unique index conceptually). Need to handle duplicates found in audit. 
- Option B: global unique on phone - conflicts with per-store model. Rejected by isolation. 
- Option C: no DB unique, app-level only. Impact: race conditions, data integrity.

D6: Session/scoping
- Option A (Recommended): separate rider guard; rider session isolated; enforce store context via middleware (resolve store from route or from rider->store). Cannot cross-store. 
- Option B: share web guard - breaks tenant isolation, mixes concerns.
- Option C: token-based (no sessions) - not needed for web portal.

D7: 2FA (A-04 context)
- Option A: defer to post-E.1 (or integrate later). Impact: no change now.
- Option B: include TOTP now (affects login flow, recovery). Impact: scope creep vs E.1 foundation.
- Option C: SMS OTP - cost/complexity; avoid.

Recommendation: D4A, D5A, D6A, D7A (defer). Final decision by owner.

### 2.a) Schema plan (proposed, not implemented)
New tables/fields (analysis only):
- rider_accounts: id ulid/uuid? follow project (HasUlids used). Fields: id, delivery_rider_id (FK, cascade), store_id (FK, denorm for fast checks), email (nullable), phone (nullable or mirrored), password (hashed, nullable until accepted), remember_token, last_login_at, is_active, invited_at, invited_by (user id), invitation_token (hashed, nullable), invitation_expires_at, invitation_accepted_at, timestamps, soft deletes? 
Constraints: unique (store_id, phone) where phone not null and not deleted (conceptual), unique (store_id, email) where email not null and not deleted. Indexes: [store_id,is_active], [delivery_rider_id].
- delivery_riders: may add 'last_invited_at','invited_by' (optional) - avoid reused columns (verification_barcode/verified_at not to be reused). 
Backfill/rollback considerations noted; no migrations written.

### 2.b) Routes/middleware
- routes/rider.php (new) or namespaced under auth? Separate rider session. Middleware: rider guard, ResolveStoreFromRoute? or derive store from auth()->user()->store_id. Also ensure account active, rider not soft-deleted, store active.
- Reuse AuthenticateSession pattern? customize for rider guard.

### 2.c) Invitation flow
Create/invite rider -> generate token (hashed), store expires_at (e.g. 48h), single-use, invalidate previous tokens on resend. Accept: validate token, set password (policy), mark accepted, clear token, login. Audit events (invited/resent/accepted/disabled). Non-enumerating on accept endpoints.

### 2.d) Permissions
No new store permission required beyond existing delivery.riders.* (view/create/update/delete). May need to ensure grouping in PermissionGroupMeta; ROLES_PERMISSIONS.md unchanged now (analysis). mystatuskit only for status colors.

### 2.e) i18n
Add rider portal keys if UI added later (en/ar/fr/es). Existing rider keys in order_flow.* (e.g. rider_assign, rider_manage_link) reused where applicable.

### 2.f) Tests (plan)
Tenant isolation tests (cross-store cannot access), invitation expiry/single-use, deactivation locks out, throttle on login, soft-deleted rider blocked. Append-only for E.2 (separates concerns). Use Pest, MySQL not required for unit/feature isolation with policies; phpunit.mysql.xml exists if needed.

## 3) Ordered tasks (E.1-1..E.1-n)
E.1-1: Readiness report + plan (this doc) - DONE (analysis)
E.1-2: Data audit (read-only SQL) against DB if available - run manually
E.1-3: Approve D4-D7 (owner decision)
E.1-4: Schema migration + models (RiderAccount, link) - post-approval
E.1-5: Auth (guard/provider/middleware/controllers) - post-approval
E.1-6: Invitation flow (mail, tokens) - post-approval
E.1-7: Routes/views (minimal) + tests - post-approval


## 4) Scope guards
- Analysis only in this phase. No code, migrations, config changes, routes, tests written.
- Exactly 2 files changed total (this doc + STATUS.md +1 line). 
- No reuse of verification_barcode/verified_at or returns-only columns. 
- mystatuskit exclusively for status colors.
- Do not touch Todos.md, docs/serverpass.txt, other paths.

## 5) Acceptance criteria (measurable)
- docs/plans/rider-portal-e1.md exists with sections 1-4 and D4-D7 matrix.
- docs/plans/2026-10-remediation/STATUS.md has +1 line (ASCII-safe). 
- git diff --stat shows exactly 2 files.
- All claims cite file paths/lines where applicable. 
- Baseline tests remain green (documented).

## 6) Out of scope
D4-D7 implementation, migrations, UI implementation, API beyond auth, 2FA details (A-04), E.2 features.

## 7) Risk register
- Enumeration: mitigate with non-enumerating auth errors, generic messages.
- Duplication: audit first (E.1-2); enforce per-store unique after cleanup.
- Session leakage: separate guard, middleware checks (active account, not soft-deleted, store active).
- Token security: hashed tokens, single-use, expiry, invalidate on resend.

## Evidence references (file:line)
- config/auth.php:38-43,66-67
- app/Domains/Shipping/Models/DeliveryRider.php: (model, scopeForStore ~63)
- database/migrations/2026_09_01_100001_create_delivery_riders_table.php:28
- routes/merchant.php:104
- app/Livewire/Concerns/TrackingRiderFormConcern.php:27
- app/Domains/Order/Services/OrderTrackingService.php:110
- app/Livewire/Concerns/TrackingGridConcern.php:32,142-150
- app/Providers/AppServiceProvider.php:138-159,174
- bootstrap/app.php:36
- app/Enums/Store/StorePermissionEnum.php:97-100,53
- resources/views/livewire/merchant/delivery/riders.blade.php:305
- tests: Merchant/RiderManagementTest, OrderDispatchRiderTest, Security, Tenant, Auth

