# Edzeery Platform — Error Fix Plan

## Phase 1: Critical Bugs (13 issues) 🔴

### 1.1 `payments.status` enum missing `pending_review`
- **File:** Migration for payments table
- **Issue:** DB enum for `payments.status` does not contain `pending_review`, but `StatusPaymentEnum::PENDING_REVIEW='pending_review'` and `SubmitManualPaymentAction` inserts it. Manual payment submission fails.
- **Fix:** Add migration to alter status enum to include `pending_review`
- **Status:** [x] ✅

### 1.2 `SubscriptionStateService` fatal error
- **File:** `app/Domains/Billing/Services/SubscriptionStateService.php`
- **Issue:** Calls undefined `guardTransition()` and fires nonexistent `SubscriptionStatusChanged` event. Any call throws `Error`.
- **Fix:** Implement `guardTransition()` or replace with direct status update; fix event class
- **Status:** [x] ✅

### 1.3 `CheckSubscriptions` overwrites SUSPENDED status
- **File:** `app/Console/Commands/CheckSubscriptions.php`
- **Issue:** After possibly suspending, lines 43-45 unconditionally set `status='expired'` because `ends_at` is past — overwriting SUSPENDED before save.
- **Fix:** Add early return after suspend, fix status logic
- **Status:** [x] ✅

### 1.4 `CheckSubscriptions` never scheduled
- **File:** `routes/console.php`
- **Issue:** `subscriptions:check-expired` command is never scheduled. Subscriptions never expire automatically.
- **Fix:** Add scheduler entry
- **Status:** [x] ✅

### 1.5 `Invoice::payment()` references nonexistent column
- **File:** `app/Models/Invoice.php`
- **Issue:** `payment_id` column does not exist in any migration. Using the relation throws SQL errors.
- **Fix:** Remove or fix the relation, or add migration for payment_id
- **Status:** [x] ✅

### 1.6 `PlansSeeder` wrong variable — subscribes to wrong plan
- **File:** `database/seeders/PlansSeeder.php`
- **Issue:** After plan loop, subscribes demo user using `$plan` (Enterprise) with `$trialPrice`. Should be `$trialPlan`.
- **Fix:** Change `$plan` to `$trialPlan`
- **Status:** [x] ✅

### 1.7 `Order::popTransitionMeta()` TypeError
- **File:** `app/Models/Orders/Order.php`
- **Issue:** `array_pop()` returns last element (reason value) instead of meta array, then second pop returns string violating `?array` type. Status transitions from merchant panel fail with 500.
- **Fix:** Fix `popTransitionMeta()` to return full meta array correctly
- **Status:** [x] ✅

### 1.8 `OrderAssignmentService:159` missing parentheses
- **File:** `app/Domains/Order/Services/OrderAssignmentService.php`
- **Issue:** `->toArray;` (missing parentheses) on pluck chain. Yields null → TypeError. Auto-assignment breaks when mixed specialists.
- **Fix:** Change `->toArray;` to `->toArray();`
- **Status:** [x] ✅

### 1.9 Orders page broken wire:click/wire:change names
- **File:** `resources/views/livewire/merchant/orders/index.blade.php`
- **Issue:** `wire:click="$setPage(...)"` and `wire:change="$setFilter(...)"` use `$` prefix. Volt closures don't need `$`. Pagination and filters are broken.
- **Fix:** Remove `$` prefix from all wire:click/wire:change closures
- **Status:** [x] ✅

### 1.10 `$changePlan` bypasses domain layer
- **File:** `resources/views/livewire/account/billing.blade.php`
- **Issue:** Inline-creates subscription with raw string-literal statuses, instant 'paid' payment, no events. Skips activation pipeline.
- **Fix:** Use `CreateSubscriptionAction` + `RecordPaymentAction` or proper domain flow
- **Status:** [x] ✅

### 1.11 Duplicate `mount()` in teams page — first check dead
- **File:** `resources/views/livewire/merchant/teams/index.blade.php`
- **Issue:** `mount()` called twice, second overwrites first. `TEAM_VIEW` check never runs. STAFF users get 403.
- **Fix:** Merge into single `mount()`, combine permission checks
- **Status:** [x] ✅

### 1.12 `StoreTeamService` — role never changes after creation
- **File:** `app/Services/Stores/StoreTeamService.php`
- **Issue:** `updateMember` skips `assignRole` if user already has merchant roles. Role dropdown silently does nothing.
- **Fix:** Always re-assign role on update, or use syncRoles
- **Status:** [x] ✅

### 1.13 `EnsureMerchantAccess` middleware dead + wrong alias
- **File:** `bootstrap/app.php`, `app/Http/Middleware/Merchant/EnsureMerchantAccess.php`
- **Issue:** Middleware class exists but never used. Alias `merchant.access` points to `EnsureStoreMembership`.
- **Fix:** Remove dead middleware class, fix alias if needed
- **Status:** [x] ✅

---

## Phase 2: High-Severity Bugs (8 issues) 🟠

### 2.1 `date` casting instead of `datetime`
- **File:** `app/Models/billing/Subscription.php`
- **Issue:** Date fields cast as `date` (not `datetime`). After DB round-trip, times truncate to midnight → `isActive()` expires subscriptions ~1 day early.
- **Fix:** Change casts to `datetime`
- **Status:** [x] ✅

### 2.2 `latestSubscription()` ordered by `updated_at`
- **File:** `app/Models/User.php`
- **Issue:** Any touch of an older subscription (cancel, etc.) makes it "latest".
- **Fix:** Order by `starts_at` or `created_at`
- **Status:** [x] ✅

### 2.3 Chargily gateway stub + no webhook route
- **File:** `app/Domains/Billing/Gateways/ChargilyGateway.php`, `routes/api.php`
- **Issue:** `charge()` throws RuntimeException. No webhook endpoint exists anywhere. Online payments impossible.
- **Fix:** Implement Chargily charge + webhook route
- **Status:** [x] ✅

### 2.4 `canUseFeature()` helper denies trial users
- **File:** `app/Helpers/helpers.php` (subscription section)
- **Issue:** Checks only `isActive()`. Trial users have `pending` status → denied features. Contradicts `StoreStatusUpdater` logic.
- **Fix:** Add `onTrial()` check to helper
- **Status:** [x] ✅

### 2.5 `store_id` never populated on subscription payments
- **File:** `resources/views/livewire/account/billing.blade.php`
- **Issue:** Per-store "Paid/Unpaid" badges always show Unpaid because `store_id` is never written.
- **Fix:** Populate `store_id` when recording subscription payments
- **Status:** [x] ✅

### 2.6 `ChangePlanAction` orphaned
- **File:** `app/Domains/Billing/Actions/ChangePlanAction.php`
- **Issue:** Called nowhere. Doesn't cancel old subscription. Picks arbitrary price.
- **Fix:** Delete or integrate into billing flow
- **Status:** [x] ✅

### 2.7 `FeatureUsageService` null-value = unlimited loophole
- **File:** `app/Domains/Plan/Services/FeatureUsageService.php`
- **Issue:** `value === null` treated as unlimited. Misconfigured plan silently grants unlimited everything.
- **Fix:** Default null values to 0 or throw
- **Status:** [x] ✅

### 2.8 Billing page yearly toggle broken + wrong labels
- **File:** `resources/views/livewire/account/billing.blade.php`
- **Issue:** `wire:model.live` on plain buttons (no value binding). "Yearly" label shows "Month (expires)".
- **Fix:** Use radio inputs or proper toggle mechanism; fix labels
- **Status:** [x] ✅

---

## Phase 3: Medium Bugs + Improvements (8 issues) 🟡

### 3.1 Order-success subtotal always 0
- **File:** `resources/views/livewire/storefront/order-success.blade.php`
- **Issue:** `orders` table has no `subtotal` column. Displays 0 always.
- **Fix:** Sum items or derive from `total_amount - shipping_cost`
- **Status:** [x] ✅

### 3.2 Checkout accepts orders when shipping unavailable
- **File:** `resources/views/livewire/storefront/order-form.blade.php`
- **Issue:** Shipping not available → order goes through with `shipping_cost = 0` silently.
- **Fix:** Block checkout when shipping unavailable
- **Status:** [x] ✅

### 3.3 No payment method UI
- **File:** `resources/views/livewire/storefront/order-form.blade.php`
- **Issue:** `payment_method` hardcoded to 'cod' with no UI to choose.
- **Fix:** Add payment method selection step (COD + future online)
- **Status:** [x] ✅

### 3.4 Store Settings save() missing validation + permission check
- **File:** `resources/views/livewire/merchant/store-settings.blade.php`
- **Issue:** No server-side validation for `name` field. `save()` doesn't re-check `STORE_UPDATE` permission.
- **Fix:** Add validation rules and permission check in save action
- **Status:** [x] ✅

### 3.5 Single-product template — no price update on variant select
- **File:** `resources/views/livewire/storefront/templates/single-product.blade.php`
- **Issue:** Variant selector keeps local Alpine `selected` id but no price/stock update. Add-to-cart unguarded.
- **Fix:** Wire variant selection to price/stock display
- **Status:** [x] ✅

### 3.6 OrderSettings modals never open
- **File:** `resources/views/livewire/merchant/order-settings.blade.php`
- **Issue:** `<x-edz.modal :isOpen="$showShiftModal">` — Alpine initializes once with `x-data="{ open: @js($isOpen) }"` but no `x-effect`. Setting server flag doesn't flip Alpine.
- **Fix:** Add `x-effect` or use Livewire polling to sync modal state
- **Status:** [x] ✅

### 3.7 `OrderObserver::handleStatusChange` uses wrong actor
- **File:** `app/Observers/OrderObserver.php`
- **Issue:** Applies inventory movements using `$order->user` (the buyer) instead of the transitioning actor.
- **Fix:** Use the `changedBy` metadata or membership from context
- **Status:** [x] ✅

### 3.8 Removed team members keep global Spatie permissions
- **File:** `app/Services/Stores/StoreTeamService.php`
- **Issue:** `$remove` soft-deletes membership but user's merchant role/permissions persist globally.
- **Fix:** Revoke Spatie role/permissions on member removal
- **Status:** [x] ✅

---

## Summary

| Phase | Count | Status |
|-------|-------|--------|
| Phase 1 — Critical | 13 | 13/13 done ✅ |
| Phase 2 — High | 8 | 8/8 done ✅ |
| Phase 3 — Medium | 8 | 8/8 done ✅ |
| **Total** | **29** | **29/29 done ✅** |
