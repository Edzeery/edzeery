# DATABASE_PLAN.md (V2 – Final)
> Platform + Multi-Store (MySQL/MariaDB)
> Decisions applied:
> - Multi-tenant by `store_id` for all operational data
> - SoftDeletes: `orders`, `order_items`, `inventory_movements` (+ other tables where archiving is needed)
> - `orders.status_id` FK → `statuses.id`
> - `statuses` are per-store, with System templates (store_id = NULL) cloned into each store
> - `brands` templates list selectable → cloned per store
> - `categories` templates list selectable → cloned per store + **hierarchical (tree)**
> - `categories.slug` unique **per store** (not global)
> - `product_variants.barcode` nullable, uniqueness scoped per store when present
> - Store Roles model = Option B (fixed roles table + membership role assignment, scoped by store)
> - `profiles` One-to-One with `users`

---

## 1) Architecture Rules (Non‑Negotiable)

### 1.1 Data Levels
**Platform-level (global):**
- `users`, `profiles`
- Spatie Permission tables (platform RBAC)
- `plans`, `plan_features`, `plan_prices`, `plan_plan_feature`
- Locations: `countries`, `states`, `cities` (future multi-country) 
- Infra: `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`
- (Optional global) `notifications` if used platform-wide

**Store-level (tenant / operational):**
- `stores`
- `store_memberships`
- Store configs: `store_settings`, `store_seo`, `store_theme_settings`
- Catalog: `products`, `product_variants`, `product_options`, `product_option_values`, `product_variant_option_value`, `product_images`
- Taxonomy: `categories`, `brands` (store copies cloned from templates)
- Commerce: `customers`, `orders`, `order_items`
- Inventory: `inventory_movements`
- Shipping: `shipping_providers`
- Support/ops: `store_status_histories`, `store_user_requests`
- Subscription: `subscriptions`, `payments` (store-owned; payments can reference subscription/plan price)

### 1.2 Tenant Isolation
Any operational record **must** be tied to a store:
- Always include `store_id` (NOT NULL) for operational tables (except template rows).
- Uniqueness constraints for business keys must be **scoped by store**:
  - Use composite unique indexes: `unique(store_id, field)`.

### 1.3 Templates Strategy (Professional Long-Term)
Templates are **source-only**:
- System templates use `store_id = NULL`.
- When a store is created (or feature enabled), templates are **cloned** into store-owned rows (`store_id = X`).
- Operational references (orders/products/etc.) must point to **store-owned** rows only.

---

## 2) Key Decisions (Indexes & Uniqueness)

### 2.1 Orders Number
- `orders.number` is unique **per store**: `unique(store_id, number)`.

### 2.2 Product & Variant Identifiers
**Products**
- `products.sku` unique per store: `unique(store_id, sku)`
- `products.slug` unique per store: `unique(store_id, slug)`

**Variants**
- `product_variants.sku` unique per store: `unique(store_id, sku)`
- `product_variants.barcode` is **nullable**
  - Unique per store when present:
    - Recommended: `unique(store_id, barcode)` with `barcode` nullable (allowed in MySQL; multiple NULLs are allowed, which is desired)

> Note: Ensure `product_variants.store_id` is **NOT NULL** (recommended) and consistent with `product.store_id`.

### 2.3 Categories / Brands
- Templates exist (`store_id = NULL`) and are cloned into store records.
- `categories.slug` unique **inside store** only: `unique(store_id, slug)`
- `brands.slug` unique inside store: `unique(store_id, slug)`
- Both should be soft-deletable (recommended) to avoid breaking product relations historically.

---

## 3) Table-by-Table Plan (Final)

## 3.1 Users & Profiles

### `users` (platform)
- Keep standard Laravel fields + your fields.
- Location linkage (future multi-country):
  - `country_id`, `state_id`, `city_id` nullable FKs
- SoftDeletes: optional (you already have softDeletes)

**Indexes**
- `email` unique (global)

### `profiles` (One-to-One)
**Change required:** make it truly one-to-one.
- Use `user_id` as **unique** (or make it primary key).
- Recommended schema:
  - `id` (optional) OR use `user_id` as primary
  - `user_id` FK → `users.id` with `cascadeOnDelete()`
  - other profile fields...
  - timestamps

**Constraint**
- `unique(user_id)` (or `primary(user_id)`)

---

## 3.2 Platform Permissions (Spatie)
Keep Spatie migrations as-is (platform RBAC).
- This RBAC is for platform/admin panel access (UserRoleEnum/PlatformPermissionEnum usage).
- Store team permissions are handled separately via store roles/memberships (see below).

---

## 3.3 Stores & Store Team (Option B)

### `stores`
- `user_id` owner FK (cascade delete)
- `slug` unique (global) — recommended since it’s used in routing and is store identity

**Indexes**
- `slug` unique
- `user_id` index

### `store_roles` (fixed catalog of roles)
Option B meaning:
- Roles are *predefined system roles* (owner/admin/manager/staff) and not customized per store.
- `store_roles.key` unique globally.

### `store_memberships`
- `store_id` + `user_id` unique: `unique(store_id, user_id)`
- `store_role_id` nullable FK → `store_roles` (`nullOnDelete`)
- `is_active`
- **SoftDeletes**: yes (already in your migration)

---

## 3.4 Store Configuration

### `store_settings`
- `store_id` FK (cascade)
- currency, language, timezone, checkout flags, etc.
- Consider `unique(store_id)` (1 row per store) — recommended.

### `store_seo`
- `store_id` FK (cascade)
- Consider `unique(store_id)` — recommended.

### `store_theme_settings`
- `store_id` FK (cascade)
- Consider `unique(store_id)` — recommended.

---

## 3.5 Taxonomy (Templates + Clone + Store-owned)

### `brands`
- `store_id` nullable for templates
- For store-owned brands: `store_id` NOT NULL
- `name`, `slug`, `logo`, `is_active`
- **SoftDeletes**: recommended

**Uniqueness**
- `unique(store_id, slug)` for store-owned rows
- Templates (`store_id = NULL`) can have unique slug globally OR keep as-is:
  - Recommended for templates: enforce uniqueness on slug within templates via an additional column strategy (see §6).
  - Minimal acceptable: allow duplicates in templates if your UI/seed ensures uniqueness.

### `categories` (Hierarchical)
- `store_id` nullable for templates; store-owned categories have `store_id` NOT NULL
- `parent_id` nullable self-FK to `categories.id`
- `name`, `slug`, `logo` (optional), `is_active`
- **SoftDeletes**: recommended

**Uniqueness**
- `unique(store_id, slug)` (store scope)
- Optionally also `unique(store_id, parent_id, slug)` if you want same slug in different branches (usually not needed; you requested slug unique inside store).

**Hierarchy rules**
- When cloning templates, clone the full tree, preserving parent-child relations.
- Deleting a parent should be restricted or handled carefully (soft delete + re-parent strategy).

---

## 3.6 Catalog

### `products`
- `store_id` NOT NULL FK cascade
- `brand_id` nullable FK → `brands` (`nullOnDelete`)
- `category_id` nullable FK → `categories` (`nullOnDelete`)
- `name`, `slug`, `sku`, `barcode` (optional)
- `type` simple/variable
- pricing fields, descriptions, seo, flags

**Constraints**
- `unique(store_id, sku)`
- `unique(store_id, slug)`

### `product_variants`
- `store_id` **NOT NULL** FK cascade (recommended)
- `product_id` FK cascade
- `sku` NOT NULL
- `barcode` nullable
- pricing, stock snapshot, alerts, shipping dims, flags

**Constraints**
- `unique(store_id, sku)`
- `unique(store_id, barcode)` (barcode nullable)
- Consider: `unique(product_id, name)` optional if you want no duplicate variant names per product.

### `product_options` / `product_option_values`
- `store_id` NOT NULL (options belong to store)
- Values can also be store-scoped
- Keep pivot `product_variant_option_value` unique

### `product_images`
- polymorphic (Product or Variant)
- keep `store_id` nullable or not; recommended `store_id` NOT NULL for operational clarity (optional, since morph already implies owner)

---

## 3.7 Customers & Orders

### `customers`
- `store_id` NOT NULL FK cascade
- `status`, `name`, `phone`, `email` nullable
- timestamps

**Indexes**
- `index(store_id, phone)` recommended
- Optional unique per store for phone/email if desired

### `statuses` (Order/Payment/Shipment statuses)
Purpose: store-owned statuses, seeded by cloning templates.
- `store_id` nullable for system templates
- `type` (order/payment/shipment/...)
- `key` (pending/paid/shipped/...)
- `label`, `color`, `is_system`, `affects_inventory`, `movement_type`, `sort_order`
- timestamps

**Critical Rule**
- Orders must reference **store-owned statuses only**.

**Uniqueness**
- In MySQL, composite unique with NULL store_id does not enforce template uniqueness reliably.
- Recommended approach is in §6 (scope key) to enforce:
  - uniqueness for (scope, type, key).

### `orders`
- `store_id` NOT NULL FK cascade
- `user_id` nullable FK nullOnDelete
- `customer_id` nullable FK nullOnDelete
- `status_id` NOT NULL FK → `statuses.id` (restrict/validate store match in app)
- `number` (string)
- totals, notes, phones…
- timestamps
- **softDeletes()** ✅

**Constraints**
- `unique(store_id, number)`
- `index(store_id, status_id)`
- `index(deleted_at)`

### `order_items`
- `order_id` FK cascade
- `store_id` (recommended NOT NULL for reporting consistency)
- `product_variant_id` FK cascade
- qty, price, subtotal
- timestamps
- **softDeletes()** ✅

**Indexes**
- `index(order_id)`
- `index(store_id, product_variant_id)`
- `index(deleted_at)`

---

## 3.8 Inventory

### `inventory_movements`
- `store_id` NOT NULL FK cascade
- `product_variant_id` FK cascade
- `user_id` nullable FK nullOnDelete
- `quantity` unsigned
- `balance_after`
- `type` string (InventoryMovementType enum)
- morph `source` nullable
- timestamps
- **softDeletes()** ✅

**Indexes**
- `index(store_id, product_variant_id)`
- `index(product_variant_id, type)`
- `index(deleted_at)`

**Operational rule**
- Inventory ledger is source of truth; variant `stock` is a snapshot/cache.

---

## 3.9 Shipping

### `shipping_providers`
- `store_id` NOT NULL FK cascade
- name, credentials json, is_active
- timestamps (recommended add timestamps if not present)

---

## 3.10 Subscriptions & Payments

### `subscriptions`
- `store_id` FK cascade
- `plan_id` nullable FK nullOnDelete
- `plan_price_id` FK cascade
- `starts_at`, `ends_at`, `trial_ends_at`, `is_trial`, `status`
- timestamps

**Indexes**
- `index(store_id, status)`
- `index(ends_at)`

### `payments`
- `store_id` FK cascade
- `subscription_id` nullable FK nullOnDelete
- `plan_price_id` FK cascade
- `plan_id` nullable FK nullOnDelete
- gateway, transaction_id, status, amount, currency, meta, paid_at
- timestamps

**Indexes**
- `index(store_id, status)`
- `index(transaction_id)` (if used by gateway callbacks)

---

## 3.11 Support / History

### `store_status_histories`
- `store_id` FK cascade
- status enum (store status lifecycle)
- reason, changed_by user nullable FK nullOnDelete
- timestamps

### `store_user_requests`
- `store_id` FK cascade
- `user_id` FK cascade
- status + assignment
- timestamps

---

## 3.12 Locations (Multi-country Future + Keep Wilayas/Communes)

### Global future-ready
- `countries` (unique code)
- `states` (FK country, unique(country_id, state_code))
- `cities` (FK state, unique(state_id, name), plus post_code, etc.)

### Keep legacy Algeria tables
- `wilayas`, `communes`
- Plan:
  - Keep them as *legacy datasets* or for a specific module.
  - New development should rely on `countries/states/cities` for multi-country.
  - If needed later, you can map wilayas/communes to states/cities via additional mapping table (not required now).

---

## 4) Soft Deletes Policy (Final)

**Required (as requested):**
- `orders` ✅
- `order_items` ✅
- `inventory_movements` ✅

**Strongly recommended for long-term stability:**
- `products`, `product_variants`
- `categories`, `brands`
- `customers`
- `shipping_providers` (optional)
This prevents breaking historical references and reports.

---

## 5) Cloning / Seeding Templates (Operational Workflow)

### 5.1 On Store Creation
Run a service/job that:
1) Clones `statuses` templates → store statuses
2) Clones `categories` templates → store categories (preserve tree)
3) Clones `brands` templates → store brands

### 5.2 Store-owned Only References
- `orders.status_id` must point to a store-owned status.
- `products.category_id` and `brand_id` should point to store-owned rows.
- Enforce in application logic:
  - Validate `status.store_id == order.store_id`
  - Validate `category.store_id == product.store_id` (or null if you allow fallback, but recommended store-owned only after clone)

---

## 6) MySQL/MariaDB Note: Enforcing Uniqueness for Templates (store_id NULL)

MySQL allows multiple NULLs in unique indexes, so:
- `unique(store_id, slug)` will not enforce uniqueness among templates (`store_id = NULL`).

**Professional solution (recommended):**
Add a generated “scope” column for template-aware uniqueness:
- `scope_store_id` = `IFNULL(store_id, 0)` as STORED generated column
- Then enforce:
  - `unique(scope_store_id, slug)` for brands/categories
  - `unique(scope_store_id, type, key)` for statuses

This keeps:
- templates under scope 0
- store data under their store_id

---

## 7) Migration Consistency Checklist

- Every FK column uses the same type (`foreignId` → `unsignedBigInteger`)
- Every store-scoped table includes `store_id` and indexes it
- Composite unique keys replace global unique keys where tenant scope is intended
- Soft deletes indexed when tables are large and frequently filtered by `deleted_at`
- Use `json` columns where you already do (`credentials`, `meta`, `contact_info`)
- Avoid dropping columns with FKs without dropping constraints first (when writing down migrations)

---

## 8) Result Summary (What You Get)

- Clean multi-tenant schema with correct uniqueness scoping
- System templates for statuses/categories/brands that do not leak into operational data
- Per-store customization without breaking historical records
- Future-ready multi-country via `countries/states/cities` while keeping `wilayas/communes`
- Soft delete support for orders, order items, inventory ledger (audit-friendly)

---
