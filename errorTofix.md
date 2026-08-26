# Orders System — Error/Fix Checklist
> Phase 8T audit — generated 2026-08-25

## CRITICAL (C1-C7)

- [x] **C1** `OrderItem::variant()` → add FK `'product_variant_id'` — ROOT CAUSE of search bug (log confirms `Unknown column 'order_items.variant_id'`)
- [x] **C2** `addFormItem` (index.blade.php:504) — scope `ProductVariant` by `store_id`
- [x] **C3** `submitEdit` (index.blade.php:711) — validate price against variant DB price
- [x] **C4** `submitCreate` + `submitEdit` — add inventory/stock check before creating items
- [x] **C5** `OrderService::transitionToStatus()` — add `canTransition()` guard before update
- [x] **C6** `Order::nextOrderNumber()` — add unique constraint (already existed) + lockForUpdate
- [x] **C7** `order-form.blade.php:178` — re-fetch variant price from DB, ignore cart session price

## HIGH (H1-H8)

- [x] **H1** `resources/lang/fr/merchant.php:56-79` + `es/merchant.php:56-77` — Arabic text → translate to FR/ES
- [x] **H2** `ShippingCostCalculator.php:28,74,83,97,107` — hardcoded Arabic → use `__()` translations + storefront.php keys (all 4 langs)
- [x] **H3** `merchant_panel.php` (all 4 langs) — remove 15 duplicate keys (agent ×3, date ×2, search_products ×2, etc.) + rename search_products_placeholder
- [x] **H4** Phone validation — add Algerian regex `0[5-7]\d{8}` in storefront + merchant forms (create + edit)
- [x] **H5** `bulkSendToCarrier` (index.blade.php:367) — log errors per order instead of swallowing
- [x] **H6** `bulkAssignAgent` (index.blade.php:343) — validate membershipId exists + belongs to store
- [x] **H7** `OrderAssignmentService::loadBalance()` — remove `confirmed` from terminal statuses
- [x] **H8** Create `resources/lang/es/subscription.php` (missing file)

## MEDIUM (M1-M17)

- [x] **M1** `$this->page` — validate bounds, clamp to max page in $setPage
- [ ] **M2** `filtered_amount` — cache or lazy-load the SUM query
- [x] **M3** `setFilter` — sanitize input types (int/float/array casts)
- [ ] **M4** Create form — require address/state/city when delivery_type=home
- [ ] **M5** `firstOrCreate` customer — add unique index on phone for race safety
- [ ] **M6** Edit blocked statuses — use status IDs or enum, not hardcoded strings
- [ ] **M7** `submitEdit` — route through OrderService instead of direct model update
- [ ] **M8** `positionFilter` — add scroll listener or use Alpine `x-intersect`
- [x] **M9** Search Enter — removed redundant `wire:keydown.enter` (debounce handles it)
- [ ] **M10** Loading skeleton — extend to bulk actions, pagination, status transitions
- [x] **M11** Submit buttons — added `wire:loading` disable state on create/edit + reassign
- [ ] **M12** `createManual()` — add inventory RESERVE movement
- [x] **M13** `order-form.blade.php:568` — replaced hardcoded `أ—` with `×`
- [x] **M14** `order-form.blade.php:298` — fixed undefined `$algin` → RTL-aware arrow direction
- [x] **M15** `order-success.blade.php` — scope order query with `created_at >= 30min` (guest store, no customer auth)
- [ ] **M16** Checkout — add rate limiting on `submitOrder`
- [ ] **M17** `SystemStatusesSeeder` — use `__()` for status labels (deferred — requires key→label refactor)

## LOW (L1-L7)

- [ ] **L1** Remove duplicate `canceled` status (keep `cancelled`) — deferred (data migration needed)
- [ ] **L2** Add `product_name` + `variant_name` snapshot columns to OrderItem — deferred (schema change)
- [x] **L3** `OrderItem` events — verified safe: `auth()->user()` returns null in queue/artisan, InventoryService handles it
- [x] **L4** Create `resources/lang/es/subscription.php` — done (same as H8)
- [ ] **L5** `ShippingCostCalculator` fallback — require explicit config, not free default — low risk
- [x] **L6** Verify `AutoCancelPendingOrders` is in Kernel schedule — confirmed in `routes/console.php:28`
- [x] **L7** `perPage` cast — already has `?? 50` fallback on line 237

## Additional fixes (discovered during audit)

- [x] **FIX** `OrderService::availableTransitions()` — resolved stale relationship cache bug: `$order->status?->key` was caching the old relationship, causing `OrderObserver::handleStatusChange()` to record wrong status in `order_status_histories`. Fixed by loading status from DB directly.
- [x] **FIX** `OrderItem::product()` — added FK `'product_id'` for correctness.

---

## FUTURE ENHANCEMENTS — NOT YEXECUTED
> suggestions from Phase 8T audit — queued for future phases, do NOT execute now

### FE1: COD Fraud Protection (Rate Limiting + Blacklist)
### FE2: WhatsApp Order Notifications
### FE3: Order Export (CSV/Excel)
### FE4: Multi-Status Bulk Update
### FE5: Additional Order Statuses (exchanged + partial_return)
### FE6: Custom Status Management UI
