# Order Tracking Separation + Orders Page Performance Refactor

**Goal:** (1) Split shipment/delivery tracking out of `orders` into a dedicated
`order_trackings` table. (2) Refactor the 1898-line orders monolith into
maintainable, performant components. (3) Fix critical N+1 and double-query
performance issues. (4) Implement Returns Verification workflow (scan → inspect → requeue).

**Started:** 2026-08-26
**Files under refactor:** `resources/views/livewire/merchant/orders/index.blade.php` (1898 lines)

---

## Critical Issues Found (Pre-Refactor Audit)

| # | Severity | Issue | Location |
|---|----------|-------|----------|
| 1 | CRITICAL | N+1 on `availableTransitions()` — 50 extra queries per page load (50 × `Status::find()`) | `index.blade.php:243` → `OrderService:72` |
| 2 | CRITICAL | Double query for `filtered_amount` — full replica of main query just for SUM | `index.blade.php:258` |
| 3 | HIGH | 1898-line monolith — create/edit/bulk/trash/reassign/transitions/filters all in one file | `index.blade.php` |
| 4 | HIGH | 4 dead state properties bloating Livewire serialization | `index.blade.php:24,56,87,88` |
| 5 | HIGH | Hardcoded `match()` color logic instead of using installed `mystatuskit` package | `index.blade.php:1367,1382` |
| 6 | MEDIUM | All eager-loads always fetched regardless of visible columns | `index.blade.php:163` |
| 7 | MEDIUM | `$getCurrentMembership()` queries DB repeatedly with no request-level cache | `index.blade.php:153` |
| 8 | MEDIUM | `bulkSendToCarrier` processes orders one-by-one (no batching) | `index.blade.php:385` |
| 9 | LOW | No Livewire component tests — only service layer tested | `tests/` |
| 10 | LOW | LIKE `%term%` on 9 columns prevents index usage | `index.blade.php:165-179` |

---

## Phase Status

| Phase | Title | Status |
|-------|-------|--------|
| 1 | Migration: create `order_trackings` table | ✅ DONE |
| 2 | Model: `OrderTracking` + Order relationship | ✅ DONE |
| 3 | Service: `OrderTrackingService` | ✅ DONE |
| 4 | Observer hook: auto-create tracking on status change | ✅ DONE |
| 5 | Performance fix: eliminate N+1 in `availableTransitions()` | ✅ DONE |
| 6 | Performance fix: eliminate double query for `filtered_amount` | ✅ DONE |
| 7 | Cleanup: remove dead state properties | ✅ DONE |
| 8 | Cleanup: replace hardcoded `match()` with `mystatuskit` | ✅ DONE |
| 9 | Refactor: extract Create/Edit modals into sub-components | ⬜ TODO |
| 10 | Refactor: extract Bulk Actions into sub-component | ⬜ TODO |
| 11 | Refactor: extract Filter Portal into sub-component | ⬜ TODO |
| 12 | Update `bulkSendToCarrier()` to create tracking records | ✅ DONE |
| 13 | Merchant UI: show tracking info in order detail | ✅ DONE |
| 14 | Tests: full tracking lifecycle coverage | ✅ DONE |

---

## Returns Verification Module (independent domain)

| Phase | Title | Status |
|-------|-------|--------|
| R1 | State machine: add `returned → pending` transition | ✅ DONE |
| R2 | Migration: verification fields on `order_trackings` | ✅ DONE |
| R3 | Enum: `ReturnInspectionResult` | ✅ DONE |
| R4 | Service: `ReturnVerificationService` (3-step workflow) | ✅ DONE |
| R5 | Volt page: Return Verification Queue at `/{store:slug}/returns` | ✅ DONE |
| R6 | Sidebar + route registration | ✅ DONE |
| R7 | Tests: `ReturnVerificationTest.php` (6 scenarios) | ✅ DONE |

---

## Order Create/Edit Modal Redesign

| Phase | Title | Status |
|-------|-------|--------|
| 15 | Migration: discount fields on `orders` table | ✅ DONE |
| 16 | Order model: discount fields + helper methods | ✅ DONE |
| 17 | Product search: add SKU/barcode support | ✅ DONE |
| 18 | Modal redesign: product picker, items, summary, toggle, dropdowns | ✅ DONE |

## Out of Scope (future phases, do NOT implement now)

- Noest / Yalidine / ZR Express / Ecotrack API clients
- Webhook endpoints for carrier status push
- Polling jobs for tracking sync
- Real-time tracking UI (customer-facing)
- Database-level full-text search (requires MySQL FULLTEXT index migration)
- `Computed` property migration (Livewire v4 style — not yet available)

---

---

## Phase Details

### Phase 1 — Migration: create `order_trackings` table

**Objective:** Add the tracking table without touching `orders` schema.

**File to create:** `database/migrations/2026_08_26_000001_create_order_trackings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_trackings', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Carrier used for THIS shipment attempt. May differ from
            // orders.shipping_provider_id if the order is re-shipped with
            // a different carrier after a return.
            $table->foreignUlid('shipping_provider_id')
                ->nullable()
                ->constrained('shipping_providers')
                ->nullOnDelete();

            $table->string('tracking_number')->nullable();
            $table->string('carrier_status')->nullable();   // raw code from carrier
            $table->string('carrier_label')->nullable();    // human-readable label

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();

            $table->json('carrier_raw')->nullable();        // last raw API/webhook payload
            $table->timestamp('last_synced_at')->nullable();
            $table->string('webhook_token')->nullable()->unique();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['store_id', 'order_id']);
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_trackings');
    }
};
```

**Scope guard:** Do not modify `orders`, `order_items`, or `order_status_histories` tables. Do not touch `shipping_provider_id` on `orders`.

**Acceptance criteria:**
- [ ] `php artisan migrate` runs clean
- [ ] `php artisan migrate:rollback` reverses cleanly
- [ ] All FKs point to correct existing tables
- [ ] `webhook_token` is unique-indexed

---

### Phase 2 — Model: `OrderTracking` + Order relationship

**Objective:** Add the Eloquent model and wire it into `Order`.

**File to create:** `app/Models/Orders/OrderTracking.php`

```php
<?php

namespace App\Models\Orders;

use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTracking extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'order_id',
        'shipping_provider_id',
        'tracking_number',
        'carrier_status',
        'carrier_label',
        'shipped_at',
        'delivered_at',
        'returned_at',
        'carrier_raw',
        'last_synced_at',
        'webhook_token',
        'notes',
    ];

    protected $casts = [
        'shipped_at'      => 'datetime',
        'delivered_at'    => 'datetime',
        'returned_at'     => 'datetime',
        'last_synced_at'  => 'datetime',
        'carrier_raw'     => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function shippingProvider(): BelongsTo
    {
        return $this->belongsTo(ShippingProvider::class);
    }

    public function isDelivered(): bool
    {
        return $this->delivered_at !== null;
    }

    public function isReturned(): bool
    {
        return $this->returned_at !== null;
    }
}
```

**File to modify:** `app/Models/Orders/Order.php` — add two relationships after `latestStatusHistory()`:

```php
public function trackings(): HasMany
{
    return $this->hasMany(OrderTracking::class);
}

public function latestTracking()
{
    return $this->hasOne(OrderTracking::class)->latestOfMany('created_at');
}
```

**Scope guard:** Do not add tracking logic beyond these two relationship methods.

**Acceptance criteria:**
- [ ] `Order::first()->trackings` returns collection without error
- [ ] `Order::first()->latestTracking` returns null without error
- [ ] `OrderTracking::create([...])` with valid `order_id`/`store_id` succeeds

---

### Phase 3 — Service: `OrderTrackingService`

**Objective:** Centralize all tracking-record logic in one service.

**File to create:** `app/Domains/Order/Services/OrderTrackingService.php`

```php
<?php

namespace App\Domains\Order\Services;

use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;
use Illuminate\Support\Str;

class OrderTrackingService
{
    /**
     * Create a new tracking record for an order being shipped.
     * Idempotent: returns existing open tracking if one exists.
     */
    public function startShipment(Order $order, ?string $trackingNumber = null): OrderTracking
    {
        $open = $order->trackings()
            ->whereNull('delivered_at')
            ->whereNull('returned_at')
            ->latest('created_at')
            ->first();

        if ($open) {
            return $open;
        }

        return $order->trackings()->create([
            'store_id'              => $order->store_id,
            'shipping_provider_id'  => $order->shipping_provider_id,
            'tracking_number'       => $trackingNumber,
            'shipped_at'            => now(),
            'webhook_token'         => Str::random(40),
        ]);
    }

    /**
     * Mark the order's currently open tracking record as delivered.
     */
    public function markDelivered(Order $order): ?OrderTracking
    {
        $tracking = $this->currentTracking($order);

        if (! $tracking) {
            return null;
        }

        $tracking->update(['delivered_at' => now()]);

        return $tracking;
    }

    /**
     * Mark the order's currently open tracking record as returned.
     */
    public function markReturned(Order $order): ?OrderTracking
    {
        $tracking = $this->currentTracking($order);

        if (! $tracking) {
            return null;
        }

        $tracking->update(['returned_at' => now()]);

        return $tracking;
    }

    /**
     * The most relevant tracking record: latest open, or latest overall.
     */
    public function currentTracking(Order $order): ?OrderTracking
    {
        return $order->trackings()
            ->whereNull('delivered_at')
            ->whereNull('returned_at')
            ->latest('created_at')
            ->first()
            ?? $order->trackings()->latest('created_at')->first();
    }
}
```

**Scope guard:** This service only touches `order_trackings`. It must never write to `orders.status_id`.

**Acceptance criteria:**
- [ ] `startShipment()` twice without delivery/return returns SAME record (idempotent)
- [ ] `startShipment()` after `markReturned()` creates NEW record (re-ship)
- [ ] `markDelivered()`/`markReturned()` with no tracking returns `null`

---

### Phase 4 — Observer hook: auto-create tracking on status change

**Objective:** Wire `OrderTrackingService` into the existing status-change pipeline.

**File to modify:** `app/Observers/OrderObserver.php`

In `handleStatusChange()`, after `OrderStatusHistory::create()` and before inventory-movement block, add:

```php
$this->syncTracking($order, $status);
```

Add new protected method:

```php
protected function syncTracking(Order $order, \App\Models\Status $status): void
{
    $service = app(\App\Domains\Order\Services\OrderTrackingService::class);

    match ($status->key) {
        'shipped'   => $service->startShipment($order),
        'delivered' => $service->markDelivered($order),
        'returned'  => $service->markReturned($order),
        default     => null,
    };
}
```

**Scope guard:** Do not modify inventory-movement logic. Do not change `creating()` or order-number generation.

**Acceptance criteria:**
- [ ] `pending → confirmed → preparing → shipped` creates ONE tracking record
- [ ] `shipped → delivered` updates SAME record's `delivered_at`
- [ ] `shipped → returned` sets `returned_at` on same record
- [ ] Re-ship `preparing → shipped` creates SECOND tracking record
- [ ] Existing tests still pass

---

### Phase 5 — Performance fix: eliminate N+1 in `availableTransitions()`

**Objective:** Stop `availableTransitions()` from querying `Status::find()` per order.

**File to modify:** `app/Domains/Order/Services/OrderService.php`

Current code in `availableTransitions()` (line ~72):
```php
$status = Status::find($order->status_id);
```

**Fix:** Use the already-eager-loaded relationship:
```php
$status = $order->status;
```

If `$order->status` is null (shouldn't happen but defensive), fall back to `Status::find()`.

**Why this works:** The `loadOrders()` query already does `->with(['status', ...])`. The `Status` model is already loaded on the order. Using `$order->status` instead of `Status::find()` eliminates 50 queries per page load.

**Scope guard:** Do not change `availableTransitions()` match logic. Do not change other methods.

**Acceptance criteria:**
- [ ] `availableTransitions($order)` still returns correct transitions for every status
- [ ] Page load query count drops by ~50 (verify with `DB::enableQueryLog()`)
- [ ] All existing tests pass

---

### Phase 6 — Performance fix: eliminate double query for `filtered_amount`

**Objective:** Stop running a second full query just for SUM.

**File to modify:** `resources/views/livewire/merchant/orders/index.blade.php`

Current code in `loadOrders()` (line ~258):
```php
$this->orders['filtered_amount'] = $query->clone()->sum('total_amount');
```

**Fix options (pick one):**

**Option A — Remove `filtered_amount` entirely:**
If the filtered amount summary is not critical, remove it. The `filtered_total` (count) is already computed from `$paginated->total()`.

**Option B — Compute from paginated results:**
```php
$this->orders['filtered_amount'] = $paginated->sum('total_amount');
```
This gives the sum of the CURRENT PAGE only, not all matching orders. Lighter but different semantics.

**Option C — Cache with short TTL:**
Use `Cache::remember("filtered_amount_{$storeId}_{$filterHash}", 60, fn() => $query->clone()->sum('total_amount'))`.

**Recommended:** Option A (remove). The filtered amount is a nice-to-have that costs a full query every page load.

**Scope guard:** Do not change other query logic. Do not remove `filtered_total`.

**Acceptance criteria:**
- [ ] Page load has ONE query for orders, not two
- [ ] Filtered count (`filtered_total`) still works
- [ ] All existing tests pass

---

### Phase 7 — Cleanup: remove dead state properties

**Objective:** Remove unused state properties that bloat Livewire serialization.

**File to modify:** `resources/views/livewire/merchant/orders/index.blade.php`

**Properties to remove (lines 24, 56, 87, 88):**
- `showAdvancedFilters` — declared but never read or written
- `bulkAction` — declared but never read or written
- `statusChangeOrderId` — declared but never read or written
- `statusChangeValue` — declared but never read or written

**Scope guard:** Only remove these 4 properties. Do not remove any other state.

**Acceptance criteria:**
- [ ] Component loads without error
- [ ] All existing tests pass
- [ ] Grep confirms zero references to removed property names

---

### Phase 8 — Cleanup: replace hardcoded `match()` with `mystatuskit`

**Objective:** Use the already-installed `mystatuskit` package for status colors.

**File to modify:** `resources/views/livewire/merchant/orders/index.blade.php`

**Current:** Hardcoded `match($statusKey)` at lines ~1367 and ~1382 producing hex colors.

**Fix:** Replace with `mystatuskit` facade calls. The package provides:
```php
use Edzeery\MyStatusKit\Facades\Status;

$status = Status::for('order', $order['status_key']);
// Returns StatusResult with ->hex, ->icon, ->label, etc.
```

**Scope guard:** Only replace the color resolution. Do not change status transition logic.

**Acceptance criteria:**
- [ ] Status badges render with correct colors
- [ ] No `match()` color logic remains for order statuses
- [ ] Grep confirms no new hand-rolled hex color match() blocks

---

### Phase 9 — Refactor: extract Create/Edit modals into sub-component

**Objective:** Break the shared create/edit modal (lines 1579–1774, ~195 lines) into a separate Livewire Volt component.

**New file:** `resources/views/livewire/merchant/orders/order-form-modal.blade.php`

**What moves:**
- `form` state array (12 keys)
- `formProductSearch`, `formProductResults`
- `editProviders`, `editDesks`
- `openCreateModal()`, `openEditModal()`, `searchProducts()`, `addFormItem()`, `removeFormItem()`, `updateFormItemQty()`, `submitCreate()`, `submitEdit()`
- The Blade template for the modal

**Communication:** Parent dispatches events → child listens. Child dispatches `order-created` / `order-updated` events → parent listens and calls `loadOrders()`.

**Scope guard:** The parent `index.blade.php` keeps all other logic. The modal component is self-contained.

**Acceptance criteria:**
- [ ] Create order from modal works
- [ ] Edit order from modal works
- [ ] Product search in modal works
- [ ] Modal opens/closes correctly
- [ ] After create/edit, order list refreshes

---

### Phase 10 — Refactor: extract Bulk Actions into sub-component

**Objective:** Break bulk actions bar (lines 1089–1151, ~63 lines) into a separate component.

**New file:** `resources/views/livewire/merchant/orders/bulk-actions.blade.php`

**What moves:**
- `selectedOrders`, `selectAll`, `showBulkBar` state
- `toggleSelectAll()`, `toggleSelectOrder()`, `clearSelection()`
- `bulkAssignAgent()`, `bulkSendToCarrier()`, `bulkDelete()`
- `allMembers` reference (passed as prop or via event)
- The Blade template for the bulk action bar

**Scope guard:** Bulk actions receive order IDs and act on them. They dispatch events to parent to refresh the list.

**Acceptance criteria:**
- [ ] Select all / individual selection works
- [ ] Bulk assign agent works
- [ ] Bulk send to carrier works
- [ ] Bulk delete works
- [ ] After bulk action, order list refreshes

---

### Phase 11 — Refactor: extract Filter Portal into sub-component

**Objective:** Break the filter portal (lines 1801–1897, ~97 lines) into a separate component.

**New file:** `resources/views/livewire/merchant/orders/filter-portal.blade.php`

**What moves:**
- `filters` state array (16 keys)
- `setFilter()`, `clearFilters()`, `toggleStatusFilter()`, `loadFilterCities()`
- `allStatuses`, `allMembers`, `allStates`, `allCities`, `allProviders` references
- The Blade template for the filter popup

**Communication:** Filter component dispatches `filters-changed` event with the full filters array. Parent `index.blade.php` listens and calls `loadOrders()`.

**Scope guard:** Filter component is display-only + state holder. Parent handles the actual query.

**Acceptance criteria:**
- [ ] All filters work (wilaya, status, agent, amount, date, product, source, delivery, provider)
- [ ] Clear filters resets everything
- [ ] Active filter pills display correctly
- [ ] After filter change, order list refreshes

---

### Phase 12 — Update `bulkSendToCarrier()` to create tracking records

**Objective:** Verify and update the bulk carrier-send action to work with the new tracking table.

**File to modify:** `resources/views/livewire/merchant/orders/index.blade.php` (or the new bulk-actions component if Phase 10 is done)

**Current flow (already correct order):**
```php
$order->update(['shipping_provider_id' => $providerId]);
$service->transition($order, 'shipped', 'Handed to carrier');
```

**After Phase 4:** The `transition()` call auto-creates the tracking record via observer. The provider must be set BEFORE the transition (already the case).

**Changes needed:**
- Verify ordering is correct (provider set before transition)
- Add comment explaining the observer auto-creates tracking

**Scope guard:** Do not reorder operations. Do not add manual `OrderTrackingService` calls.

**Acceptance criteria:**
- [ ] Bulk send creates tracking records with correct `shipping_provider_id`
- [ ] Failed transitions do NOT create orphan tracking records

---

### Phase 13 — Merchant UI: show tracking info in order detail

**Objective:** Surface tracking data in the expanded order detail row.

**File to modify:** `resources/views/livewire/merchant/orders/index.blade.php` (or the order detail section)

**Changes:**
1. Add `latestTracking.shippingProvider` to eager-load in `loadOrders()` query
2. Map tracking data in the order array:
```php
$arr['tracking'] = $order->latestTracking ? [
    'tracking_number' => $order->latestTracking->tracking_number,
    'carrier_status'  => $order->latestTracking->carrier_status,
    'carrier_label'   => $order->latestTracking->carrier_label,
    'shipped_at'      => $order->latestTracking->shipped_at?->format('Y-m-d H:i'),
    'delivered_at'    => $order->latestTracking->delivered_at?->format('Y-m-d H:i'),
    'shipping_provider' => $order->latestTracking->shippingProvider?->name,
] : null;
```
3. Add tracking info row in expanded detail section
4. Use `mystatuskit` for any status badge (not hardcoded colors)

**Scope guard:** Do not add a new visible column to the main table. Detail-expand view only.

**Acceptance criteria:**
- [ ] Expanded order with tracking shows: number + carrier + shipped/delivered dates
- [ ] Expanded order with no tracking shows nothing extra (no error)
- [ ] Status badge uses `mystatuskit`

---

### Phase 14 — Tests: full tracking lifecycle coverage

**Objective:** Lock in behavior with feature tests.

**File to create:** `tests/Feature/Merchant/OrderTrackingTest.php`

**Scenarios to cover:**
1. `shipped` transition creates one tracking record with correct `shipping_provider_id`
2. `delivered` after `shipped` updates same record (no new row)
3. `returned` after `shipped` sets `returned_at` on same record
4. Re-ship after return creates distinct second tracking record
5. `OrderTrackingService::markDelivered()`/`markReturned()` return null gracefully when no tracking
6. `Order::trackings` returns all historical records in creation order

**Acceptance criteria:**
- [ ] All 6 scenarios pass
- [ ] Full test suite passes with no regressions
- [ ] No existing tests broken

---

## Final Step (after Phase 14 only)

Update this file:
- Mark all 14 phases `✅ DONE`
- Add closing section confirming "Out of Scope" items are unchanged
- Do not open PR or merge — leave for manual review

---

## Completion Notes

### Phases 1–4 — Tracking Table Foundation
- Migration: `database/migrations/2026_08_26_000001_create_order_trackings_table.php`
- Model: `app/Models/Orders/OrderTracking.php`
- Service: `app/Domains/Order/Services/OrderTrackingService.php`
- Observer: `app/Observers/OrderObserver.php` (syncTracking hook)
- Order model: added `trackings()` and `latestTracking()` relationships

### Phase 5 — N+1 Performance Fix
- `app/Domains/Order/Services/OrderService.php:72` — `$order->status?->key` instead of `Status::find()`
- `app/Observers/OrderObserver.php` — `$order->unsetRelation('status')` before accessing status in handleStatusChange (fixes stale relationship after update)

### Phase 6 — Double Query Fix
- Removed `$this->orders['filtered_amount'] = $query->clone()->sum('total_amount')` from loadOrders()
- Removed filtered_amount display from page header summary

### Phase 7 — Dead State Cleanup
- Removed 4 unused properties: `showAdvancedFilters`, `bulkAction`, `statusChangeOrderId`, `statusChangeValue`

### Phase 8 — mystatuskit Integration
- Status badge: replaced inline `match()` + `style=` with `\Edzeery\MyStatusKit\Facades\Status::for('general', $color)->color()`
- Status dot: replaced inline `match()` + `style=` with `Status::for('general', $color)->hex()`

### Phase 12 — bulkSendToCarrier
- Added comment explaining provider must be set before transition (observer reads it)

### Phase 13 — Tracking UI
- Added `latestTracking.shippingProvider` to eager-load
- Added `tracking` array mapping in loadOrders()
- Added tracking info section in expanded order detail
- Added translations: tracking, carrier, tracking_number, shipped_at, delivered_at (EN/AR/FR/ES)

### Phase 14 — Tests
- Created `tests/Feature/Merchant/OrderTrackingTest.php` — 6 scenarios, all passing
- Full suite: 178/178 (172 original + 6 new)

### Returns Verification Module (R1–R7)
- **R1:** Added `pending` to `availableTransitions()` from `returned` state
- **R2:** Migration `2026_08_26_000002_add_verification_fields_to_order_trackings_table.php` — adds 9 columns (verification_barcode, verified_at, verified_by_membership_id, inspection_result, inspection_notes, processed_at, processed_by_membership_id, requeued_at, requeued_by_membership_id)
- **R3:** `app/Enums/Store/ReturnInspectionResult.php` — 4 cases (good/damaged/partial/lost) with `isRequeueEligible()` helper
- **R4:** `app/Domains/Order/Services/ReturnVerificationService.php` — `verifyByCode()` (Step 1: barcode scan), `process()` (Step 2: inspection), `requeue()` (Step 3: explicit resend)
- **R5:** `resources/views/livewire/merchant/returns/index.blade.php` — Volt page with 3-tab queue, scan input, process modal, requeue button
- **R6:** Route `/{store:slug}/returns` added to `routes/merchant.php`; sidebar link with `RETURNS_VERIFY_BARCODE` permission guard
- **R7:** `tests/Feature/Merchant/ReturnVerificationTest.php` — 6 scenarios covering full happy path, guard validations, and idempotency
- Translations added: EN/AR/FR/ES (return_good/damaged/partial/lost, scan_return_barcode, awaiting_verification/processing, verified/unverified, process, inspection, requeue, etc.)
- Full suite: 184/184 (178 previous + 6 new returns tests)

### Remaining (deferred)
- Phases 9–11: Sub-component extraction (modals, bulk actions, filter portal) — medium priority, complex refactor

### Order Create/Edit Modal Redesign (Phases 15–18)
- **Phase 15:** Migration `2026_08_26_000003_add_discount_fields_to_orders_table.php` — adds `discount_type`, `discount_value`, `discount_reason` to orders
- **Phase 16:** Order model updated with discount fillable, casts, and helper methods (`discount_amount`, `grand_total` attributes)
- **Phase 17:** Product search now matches by name, SKU, and barcode (3-field query)
- **Phase 18:** Full modal redesign:
  - Delivery type: professional toggle (home/stopdesk) instead of `<select>`
  - Products: search by name/SKU/barcode with stock display, add by barcode scan (Enter)
  - Items: SKU display, inline price editing, quantity controls
  - Order summary: subtotal, total weight, delivery cost (placeholder: free), discount (amount/percent), grand total
  - Submit button moved to bottom (standard pattern)
  - Discount fields: `discount_type` (amount/percent), `discount_value`, `discount_reason`
- Translations added: EN/AR/FR/ES (order_summary, subtotal, total_weight, delivery_cost, free, discount, fixed_amount, percentage, etc.)
- Full suite: 184/184
