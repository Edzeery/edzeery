 # DATABASE_PLAN_MIGRATIONS.md (V2 – Final, MySQL/MariaDB)
> هذا الملف يشرح “خطة تعديل/تنظيم الميغريشنز” لتتوافق مع مشروعك Platform + Multi‑Store
> حسب القرارات النهائية التي اتفقنا عليها.
>
> أهم قرارات مضمّنة:
> - Multi-tenant: كل بيانات التشغيل مربوطة بـ `store_id`
> - SoftDeletes: `orders`, `order_items`, `inventory_movements` (نعم)
> - `orders.status_id` FK → `statuses.id`
> - `statuses` per-store + System templates (store_id = NULL) + clone عند إنشاء المتجر
> - `brands` templates قابلة للاختيار + clone للمتجر
> - `categories` templates قابلة للاختيار + clone للمتجر + هرمية (parent_id)
> - `product_variants.store_id` = NOT NULL
> - `product_variants.sku` = NOT NULL و **Unique داخل المتجر فقط**
> - `product_variants.barcode` = NULLABLE و Unique داخل المتجر عندما يكون موجودًا
> - Store Roles = Option B (أدوار ثابتة + membership role assignment)
> - `profiles` One-to-One مع `users`

---

## 1) Naming & Conventions (ثوابت)
- جميع جداول الـ Tenant تحتوي `store_id` (NOT NULL) إلا جداول الـ Templates (مسموح `store_id = NULL` فقط لها).
- كل “Unique منطقي” مرتبط بمتجر = `unique(['store_id', 'field'])` بدل `unique('field')`.
- Soft delete:
  - أضف `$table->softDeletes()` في: `orders`, `order_items`, `inventory_movements`
- المفضّل إضافة Indexes للفلترة المتكررة:
  - `index(['store_id', ...])`
  - `index('deleted_at')` للجداول التي فيها softDeletes

---

## 2) Migration Order (الترتيب المقترح للتنفيذ)
الهدف: منع مشاكل FK أثناء `migrate:fresh`.

1) Core Laravel:
   - users
   - cache / jobs / sessions / password reset

2) Profiles (افصلها عن users بمigration مستقل) + One-to-One

3) Locations (Multi-country):
   - countries
   - states
   - cities
   - update_users_table (إضافة country_id/state_id/city_id)

4) Stores + Memberships:
   - stores
   - store_roles (Option B)
   - store_memberships

5) Store configuration:
   - store_settings
   - store_seo
   - store_theme_settings

6) Dictionaries / Templates (ثم clone):
   - statuses
   - categories
   - brands

7) Catalog:
   - products
   - product_variants
   - product_options
   - product_option_values
   - product_variant_option_value (pivot)
   - product_images

8) Sales & Inventory:
   - customers
   - orders
   - order_items
   - inventory_movements

9) Shipping / Notifications / Plans / Subscriptions / Payments / Requests… حسب حاجتك

---

## 3) Required Changes (تفصيلاً: “ماذا نعدّل في كل جدول”)

### 3.1 users
**الوضع الحالي عندك:** جيد عمومًا.
- (موجود) `softDeletes()` ✅
- أضف/ثبت قيود locations عبر migration update_users_table:
  - `country_id`, `state_id`, `city_id` nullable + FK

> ملاحظة MySQL: يفضّل `nullOnDelete()` بدل `cascadeOnDelete()` في user location حتى لا يحذف المستخدم عند حذف مدينة/ولاية.

---

### 3.2 profiles (One-to-One)
**المطلوب:** جعلها one-to-one حقيقي.

- الأفضل عمليًا:
  - `profiles.user_id` يكون:
    - FK → users.id
    - UNIQUE
  - واجعل `profiles.id` (اختياري) إما:
    - Bigint auto increment، أو
    - استخدم `user_id` كمفتاح أساسي (أكثر “صرامة”)

**اقتراح احترافي:**
- اجعل `profiles`:
  - `id` (اختياري) أو استبدله
  - `user_id` UNIQUE + FK
  - احذف `foreignId('user_id')->nullable()` (لا يكون nullable)

---

### 3.3 stores
- `slug` unique (Global) ✅
- SoftDeletes للمتاجر؟ (اختياري) لكن مفيد طويلًا (ليس شرطًا الآن)

---

### 3.4 store_roles + store_memberships (Option B)
**Option B = أدوار ثابتة غير مرتبطة بـ store_id**
- `store_roles`: (global ثابت) مثل owner/admin/manager/staff
  - `key` unique ✅
- `store_memberships`:
  - `store_id` FK
  - `user_id` FK
  - `store_role_id` FK nullable? (يفضل NOT NULL عمليًا، إلا لو عندك حالة “pending invite”)
  - unique(['store_id','user_id']) ✅
  - SoftDeletes ✅ (عندك)

---

### 3.5 statuses (Templates + per-store)
**الهدف:** status templates (store_id NULL) + statuses per store (store_id = X)
مع ضمان uniqueness بشكل صحيح على MySQL.

#### الأعمدة (مقترح ثابت)
- `store_id` nullable (NULL = system template)
- `type`, `key`, `label`, `color`, `is_system`, `affects_inventory`, `movement_type`, `sort_order`
- timestamps

#### مشكلة MySQL مع UNIQUE + NULL
MySQL يسمح بتكرار NULL داخل unique composite، لذلك نستخدم عمود “scope” مولّد.

**أضف عمود GENERATED:**
- `store_scope_id` = IFNULL(store_id, 0)

ثم Unique:
- unique(['store_scope_id', 'type', 'key'])

> هذا يضمن عدم تكرار نفس (type+key) في system templates أو داخل متجر محدد.

---

### 3.6 categories (Templates + Clone + Hierarchy)
**قراراتك:**
- templates مثل brands ✅
- هرمية ✅
- slug unique داخل المتجر فقط ✅

#### شكل الجدول المقترح
- `id`
- `store_id` nullable (NULL = template)
- `parent_id` nullable FK → categories.id (لنفس المتجر بعد النسخ)
- `name`
- `slug`
- `logo` nullable
- `is_active`
- `sort_order` (مفيد للعرض)
- timestamps
- softDeletes (مستحسن، ليس قرارًا إلزاميًا منك لكنه “احترافي” للتصنيفات)

#### Uniqueness (MySQL-friendly)
- أضف `store_scope_id` GENERATED = IFNULL(store_id, 0)
- unique(['store_scope_id', 'slug'])
- (اختياري) unique(['store_scope_id', 'parent_id', 'slug']) لو تبغى slug يتكرر عبر فروع مختلفة (عادة لا)

---

### 3.7 brands (Templates + Clone)
- `store_id` nullable (NULL = template)
- `name`, `slug`, `logo`, `is_active`
- softDeletes (مستحسن)

Uniqueness:
- `store_scope_id` GENERATED = IFNULL(store_id, 0)
- unique(['store_scope_id', 'slug'])

---

### 3.8 products
**الموجود عندك جيد، فقط ثبّت القاعدة:**
- unique(['store_id', 'sku'])
- unique(['store_id', 'slug'])

(اختياري) softDeletes للمنتجات لو تحتاج أرشفة.

---

### 3.9 product_variants (أهم جزء عندك الآن)
**قرارات نهائية:**
- `store_id` NOT NULL
- `sku` NOT NULL
- `sku` Unique داخل المتجر فقط
- `barcode` nullable
- `barcode` Unique داخل المتجر عندما يكون موجودًا

#### التعديلات المطلوبة على migration الحالي
1) غيّر `store_id`:
- من nullable إلى NOT NULL:
  - احذف `->nullable()` و `->nullOnDelete()`
  - خلّها `->constrained()->cascadeOnDelete()` (أو restrict حسب سياستك)
2) `sku`:
- تأكد أنه NOT NULL (string افتراضيًا NOT NULL)
- أزل `->unique()` العالمي
- أضف: `unique(['store_id','sku'])`
3) `barcode`:
- اجعلها `nullable()`
- لا تعمل `unique()` عالمي
- الحل الأفضل في MySQL لتطبيق uniqueness مع nullable:
  - أضف `barcode_normalized` GENERATED = IFNULL(barcode,'')
  - ثم unique(['store_id','barcode_normalized'])
  - بهذه الطريقة:
    - كل NULL تتحول إلى '' (فارغ)
    - لكن هذا سيمنع أكثر من NULL داخل نفس المتجر (لأنه سيصير '' مكرر)

**ملاحظة مهمّة (اختيار احترافي):**
- عادة نريد السماح بعدة NULL (طبيعي) بدون منع.
- MySQL لا يدعم partial unique index.
- الحل الأكثر احترافية دون تشويه البيانات:
  - اترك `barcode` nullable
  - طبّق uniqueness على مستوى التطبيق عند إدخال barcode (Validation + Query)
  - أو استخدم Trigger (أقل تفضيلًا في Laravel)
  - أو اجعل barcode NOT NULL إذا كان لازم uniqueness حتميًا (لكن أنت اخترت nullable)

**الخلاصة العملية المقترحة لمشروعك الآن:**
- `unique(['store_id','sku'])` (حتمي وقوي)
- `barcode` nullable + index(['store_id','barcode'])
- تحقق uniqueness للـ barcode في التطبيق عندما يكون غير null

> هذا يحقق المطلوب بدون حلول ملتوية على مستوى DB في MySQL.

---

### 3.10 customers
- store-scoped ✅
- (اختياري) unique(['store_id','phone']) إذا كان الهاتف هو المعرّف الأهم لديك

---

### 3.11 orders
**قراراتك:**
- SoftDeletes ✅
- ربط status عبر `status_id` ✅
- رقم الطلب unique داخل المتجر (مستحسن جدًا)

#### التعديلات المطلوبة
- أضف `status_id`:
  - FK → statuses.id
- عدّل number:
  - بدل `unique()` عالمي:
  - `unique(['store_id','number'])`
- أضف `$table->softDeletes();` + index

---

### 3.12 order_items
- SoftDeletes ✅
- (يفضل) index(['order_id']) + index(['product_variant_id'])

---

### 3.13 inventory_movements
- SoftDeletes ✅
- indexes قوية:
  - index(['store_id','product_variant_id'])
  - index(['product_variant_id','type'])
  - index(['store_id','created_at']) (مفيد للتقارير)

---

## 4) Seed / Clone Strategy (مهم للـ Templates)
عند إنشاء متجر جديد:
1) انسخ system `statuses` (store_id NULL) إلى `store_id = X`
2) انسخ system `categories` المختارة (templates) إلى `store_id = X` مع بناء الشجرة (parent mapping)
3) انسخ system `brands` المختارة (templates) إلى `store_id = X`

> مهم: بعد النسخ، كل العمليات التشغيلية (Orders/Products) ترتبط بسجلات المتجر فقط، وليس system templates.

---

## 5) Quick “Must Fix” Checklist (قبل ما نكمل تعديل الميغريشنس)
- [ ] product_variants: store_id NOT NULL
- [ ] product_variants: sku unique داخل المتجر فقط: unique(store_id, sku)
- [ ] product_variants: barcode nullable + index(store_id, barcode) (والunique للـ barcode يتم على مستوى التطبيق عند وجوده)
- [ ] orders: add status_id FK
- [ ] orders: unique(store_id, number) بدل unique(number)
- [ ] softDeletes: orders, order_items, inventory_movements
- [ ] profiles: one-to-one بإضافة profiles.user_id UNIQUE + NOT NULL

---
