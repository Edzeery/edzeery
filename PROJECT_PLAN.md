تمام يا testme — هذه **الخطة “الأكثر احترافية” للمدى الطويل (V2 Final)** بعد كل التعديلات التي اتفقنا عليها، وبنفس فكرة مشروعك (Platform + Multi‑Store) بدون تغيير الجوهر، لكن بتصميم قوي ومتّسق مع **MySQL/MariaDB** ومع **Soft Deletes**.

سأعطيك “الزبدة” كقرارات نهائية + ماذا نغيّر في الميغريشنز (على مستوى القواعد/القيود/العلاقات) + كيف ندير الـ Templates (system) والـ cloning.

---

## 1) القاعدة الذهبية (Architecture Rules)

### 1.1 فصل البيانات
**Platform-level (عام على المنصة):**
- `users`, `profiles`
- Spatie Permission (roles/permissions...) لإدارة صلاحيات المنصة
- `plans`, `plan_features`, `plan_prices`, `plan_plan_feature`
- `countries/states/cities` + (تبقى) `wilayas/communes` كبيانات مرجعية
- `cache/jobs/sessions/password_reset_tokens`

**Store-level (Tenant):** كل ما هو تشغيلي يجب أن يكون مربوطًا بمتجر:
- `stores`
- `store_memberships` (+ role داخل المتجر)
- `store_settings`, `store_seo`, `store_theme_settings`
- `products`, `product_variants`, `product_options`, `product_option_values`, `product_images`
- `customers`, `orders`, `order_items`
- `inventory_movements`
- `shipping_providers`
- `statuses` (حسب التصميم أدناه)

### 1.2 قاعدة الـ Uniqueness في Multi‑Store
أي “معرّف” يخص متجر (number/sku/slug/…): **Unique داخل المتجر فقط**
- مثال: بدل `unique(number)` ⇒ `unique(store_id, number)`

### 1.3 Soft Deletes (قرار نهائي)
- نستخدم `softDeletes()` في الجداول التي تحتاج “أرشفة” وليس حذف نهائي.
- ملاحظة مهمة: **soft delete لا يفعّل ON DELETE في MySQL** لأنه ليس حذفًا فعليًا، لذلك لا تعتمد على cascade عند الـ soft delete.

---

## 2) statuses (الأكثر احترافية للمدى الطويل)

### الهدف
- `statuses` تكون **قابلة للتخصيص لكل متجر**
- وفي نفس الوقت عندك **System templates** جاهزة كبداية (Default workflow)
- والطلبات `orders` يجب أن ترتبط دائمًا بـ **statuses الخاصة بالمتجر** (بعد الاستنساخ/النسخ عند إنشاء المتجر)

### التصميم المقترح (Professional)
#### جدول `statuses`
- `store_id` **nullable**
  - `NULL` = System template
  - رقم = Status خاص بمتجر
- حقولك الحالية ممتازة: `type`, `key`, `label`, `is_system`, `affects_inventory`, `movement_type`, `color`, `sort_order`
- **Soft Deletes**: نعم (`softDeletes()`)

#### مشكلة MySQL مع Unique + NULL (مهمة جدًا)
في MySQL/MariaDB لا يوجد partial unique index، و`NULL` يسمح بتكرارات داخل composite unique.
**الحل الاحترافي:** عمود scope مُولّد (generated) يحول NULL إلى قيمة ثابتة.

مثال Concept:
- `store_scope_id` = `IFNULL(store_id, 0)` (Generated column)
- ثم Unique:
  - `unique(['store_scope_id', 'type', 'key'])`

> هذا يضمن:  
> - عدم تكرار `type+key` في system templates  
> - وعدم تكرارها داخل نفس المتجر

#### الربط مع orders
- في `orders` نضيف:
  - `status_id` (FK → `statuses.id`) **NOT NULL**
- و”قانون التطبيق” (Business rule):
  - status_id لازم يكون من نفس `store_id` للطلب (تتحقق منه في الـ service / observer / validation)

#### سياسة الـ cloning
عند إنشاء متجر:
- انسخ system statuses (`store_id = NULL`) إلى صفوف جديدة بنفس البيانات لكن `store_id = new_store_id`
- ثم كل الطلبات تستخدم نسخ المتجر فقط

---

## 3) Orders: رقم الطلب + status_id + soft deletes

### `orders.number`
- الأفضل Multi‑Store: **Unique per store**
  - `unique(['store_id', 'number'])`
- اجعل `orders` فيها `softDeletes()` (إذا تريد أرشفة الطلبات بدل حذفها)

### `orders.status_id`
- `foreignId('status_id')->constrained('statuses')`
- **NOT NULL** (لأن الطلب لازم له حالة دائمًا)
- مع indexing:
  - index على `store_id, status_id`
  - index على `created_at` للتقارير

---

## 4) Product Variants (قراراتك النهائية)

### 4.1 `product_variants.store_id` = NOT NULL (حسب طلبك)
هذه حركة ممتازة احترافيًا لأنها:
- تسهل القيود والـ queries
- تمنع بيانات “تيتم” بدون متجر

> بما أن `product_variants` دائمًا تابع لمنتج تابع لمتجر، فوجود `store_id` NOT NULL منطقي (مع التأكد أنه يساوي `products.store_id` على مستوى التطبيق).

### 4.2 `product_variants.barcode` = nullable ✅
- اجعله `nullable()`

### 4.3 Uniqueness للـ SKU/Barcode “داخل المتجر”
بدل global unique:
- `unique(['store_id', 'sku'])`
- `unique(['store_id', 'barcode'])` لكن بما أن barcode nullable:
  - MySQL يسمح بتكرار NULL، وهذا غالبًا **مقبول** (يعني عدة منتجات بدون barcode)
  - ويمنع تكرار باركود فعلي داخل المتجر

### 4.4 ملاحظة مهمة
عندك في `products`:
- `unique(['store_id','sku'])` ممتاز كـ base sku
وعند `product_variants`:
- أيضًا `unique(['store_id','sku'])` ممتاز

> فقط تأكد أن الـ SKU للـ variant لا يتصادم مع base SKU إن كنت تستعملهما في نفس الواجهة/المنطق. (تقنيًا هما في جدولين مختلفين، فقاعدة البيانات لن تمنع التعارض بينهما.)

---

## 5) Categories / Brands (Professional Templates + Store Custom)

أنت قلت “أيضًا في categories/brands” — الأفضل طويل المدى:

### 5.1 نفس فلسفة statuses
- `store_id` nullable:
  - NULL = Templates عامة
  - store_id = خاص بالمتجر
- `softDeletes()` نعم

### 5.2 Uniqueness مع MySQL (نفس مشكلة NULL)
إذا تريد منع تكرار `slug` داخل scope (system أو متجر):
- أضف generated column:
  - `store_scope_id = IFNULL(store_id, 0)`
- ثم:
  - `unique(['store_scope_id','slug'])`
  - (واختياري) `unique(['store_scope_id','name'])` لو مهم

### 5.3 سياسة النسخ (cloning)
عند إنشاء متجر:
- انسخ `categories/brands` templates إلى المتجر (اختياري حسب احتياجك)
- أو اتركها “كقوالب” فقط وتسمح للمتجر بإنشاء خاص به من الصفر

> الأفضل عادة: categories templates تُنسخ (لأنها جزء من تجربة البداية). brands ممكن تُترك للمتجر.

---

## 6) Store Roles = الخيار B (المعماري الأفضل)

**الخيار B (الاحترافي غالبًا):**
- `store_roles` تكون **Global (بدون store_id)**: owner/admin/manager/staff (ثابتة)
- `store_permissions` تكون **Global** (قائمة صلاحيات موحدة)
- pivot `store_role_permissions` يحدد صلاحيات كل role (قالب صلاحيات موحد)
- `store_memberships` يربط المستخدم بمتجر + role

هذا يعطيك:
- نظام صلاحيات موحّد وسهل الصيانة
- وتستطيع لاحقًا إضافة “استثناءات” per membership لو احتجت بدون كسر الأساس

**تطبيقًا على جداولك الحالية:** أنت قريب جدًا من هذا بالفعل (لأنك علّقت store_id في store_roles).

> **نفّذ (أغسطس 2026) — القرار #6 (هجين membership-scoped):**
> بدل تفعيل Spatie Teams حرفيًا (معطّل لأن `stores.id`/`users.id` ULID بينما teams migration يستخدم `unsignedBigInteger`)، طُبّقت عزل per-store على **store_memberships** مباشرة:
> - عمود `store_memberships.role` (قيمة من `StoreRoleEnum`).
> - جدول `store_membership_permissions` (pivot: `membership_id` + `permission`) للصلاحيات المخصصة per member.
> - `canStore()`/`hasStoreRole()` (في `app/Helpers/helpers.php`) تفحص العضوية الحالية أولًا مع fallback إلى Spatie global للتوافق.
> - `StoreMembership::can/hasRole/permissionNames/syncPermissions` scoped؛ `StoreTeamService` يخزّن role+perms على العضوية.
> تأكيد الطلبات (COD) والمخزون (ATP) وFilament SuperAdmin لم تُمس (حسب نطاق البرومت).


---

## 7) profiles One-to-One (نهائي)

الأكثر نظافة:
- اجعل `profiles.user_id` هو **Primary Key** بدل جدول id منفصل
- `user_id` unique + constrained + cascadeOnDelete (في حال حذف user فعليًا)

لكن لو تريد تبقي `id`:
- اجعل `user_id` **NOT NULL** + `unique('user_id')`
- هذا يضمن One-to-One فعليًا

وبما أن `users` عندك `softDeletes()`:
- قرار منطقي: `profiles` أيضًا `softDeletes()` (اختياري)
- أو تخليها بدون soft delete وتتعامل معها كامتداد دائم للحساب

---

## 8) تنظيف التناقضات الحالية (نقاط لازم تتعدل)

هذه أهم “الأخطاء/التعارضات” في الميغريشنز الحالية حسب ما أرسلت:

1) `orders.number` الآن global unique → لازم يصير `unique(store_id, number)`
2) `product_variants.sku/barcode` الآن global unique → لازم يصير per store composite
3) `product_variants.store_id` كان nullable → الآن **NOT NULL**
4) `orders` لا تحتوي `status_id` → نضيفه ونربطه بـ `statuses`
5) `profiles` الآن `id` string primary + user_id nullable → لازم One-to-One: user_id NOT NULL + unique
6) `categories/brands` تحتاج قواعد scope + unique محترمة مع NULL → generated scope column أو بديل

---

## 9) بخصوص وجود wilayas/communes مع countries/states/cities
قرارك “لا تحذف” ممتاز لو عندك أسباب (بيانات قديمة/مصادر مختلفة/واجهة معينة).
فقط:
- اعتبر **واحدة فقط** هي المصدر الرسمي للطلبات/عناوين الشحن على المدى الطويل
- والثانية تُترك للتوافق أو الاستيراد أو التحويل
- لا تربط orders بعشوائية بين الاثنين

---

## 10) الخلاصة التنفيذية (The “zبدة” العملية)

- **Order status دائمًا من statuses الخاصة بالمتجر**  
  و**System statuses** مجرد Templates تُنسخ عند إنشاء المتجر.
- **MySQL/MariaDB**: لأي جدول فيه `store_id nullable` + unique scope، استخدم **generated scope column** (`IFNULL(store_id,0)`) ثم composite unique.
- **Orders**: `status_id` NOT NULL + `unique(store_id, number)`
- **Variants**: `store_id NOT NULL`, `barcode nullable`, و uniqueness per store
- **Categories/Brands**: نفس نظام templates + soft deletes + unique per scope
- **Profiles**: one-to-one حقيقي (user_id unique + NOT NULL)
- **Soft delete**: نعم، لكن لا تعتمد على cascades عند soft delete

---

إذا تحب، أعطني فقط:  
1) هل تريد **softDeletes** لـ `orders` و`order_items` و`inventory_movements` أيضًا (غالبًا نعم للـ orders، وأحيانًا لا للـ inventory ledger لأنه سجل محاسبي)؟  
2) هل تريد **تجعل categories templates تُنسخ تلقائيًا** عند إنشاء المتجر، أم تتركها “قائمة للاختيار” بدون نسخ؟
تمام يا testme — اعتمادًا على آخر نقطتين:

- **SoftDeletes** مطلوبة لـ: `orders`, `order_items`, `inventory_movements` ✅  
- **categories templates قائمة للاختيار** ✅ (يعني: تصنيفات “جاهزة/قوالب” تظهر للمتجر ليختار منها ويستورد/ينسخ)

أعطيك “الزبدة الاحترافية” النهائية لهذين الجزئين ضمن خطة V2 (بدون تغيير فكرة المشروع).

---

## 1) SoftDeletes: orders / order_items / inventory_movements (قرار نهائي)

### لماذا مهم؟
- تحافظ على الأرشفة + تتبع التاريخ + تتجنب فقدان علاقات/سجلات محاسبية/مخزون.
- خصوصًا `inventory_movements` لازم تكون قابلة للأرشفة وليس الحذف النهائي غالبًا.

### التعديل المطلوب في الميغريشنز
أضف:
```php
$table->softDeletes();
```

#### A) `orders`
- أضف `softDeletes()`
- **مهم**: أضف index لو تستعمل “استرجاع بسرعة” أو فلترة متكررة:
```php
$table->softDeletes();
$table->index('deleted_at');
```

#### B) `order_items`
- أضف `softDeletes()`
- كذلك index:
```php
$table->softDeletes();
$table->index('deleted_at');
```

#### C) `inventory_movements`
- أضف `softDeletes()`
- ويفضل index:
```php
$table->softDeletes();
$table->index('deleted_at');
```

### ملاحظة تصميم مهمة (أفضل ممارسة)
- عندك `inventory_movements.balance_after`: هذا ممتاز كسجل Ledger.
- مع SoftDelete: **لا تحسب الرصيد النهائي من جدول الحركات بدون استثناء المحذوفة**. دائمًا استعمل default scope (Laravel تلقائيًا يستثني المحذوف) أو explicitly `whereNull(deleted_at)`.

---

## 2) Categories Templates “قائمة للاختيار” (احترافي للمستقبل)

أنت تريد: “templates قائمة للاختيار”  
أكثر حل احترافي على المدى الطويل في MySQL/MariaDB هو:

### الفكرة
- نخلي جدول `categories` يدعم نوعين:
  1) **Templates (System/Platform)**: `store_id = NULL`
  2) **Store Categories**: `store_id = X`

ثم في واجهة المتجر:
- تعرض قائمة الـ Templates (`store_id IS NULL`) للاختيار
- عند اختيار Template: تعمل **Clone/Copy** إلى `store_id = current_store_id`
- الطلبات/المنتجات داخل المتجر **لا ترتبط مباشرة بالـ template** بل بالنسخة الخاصة بالمتجر (أفضل عزل Tenant).

### لماذا “نسخ” وليس “ربط مباشر”؟
- المتجر قد يريد تعديل الاسم/الترتيب/الإخفاء بدون التأثير على الآخرين.
- يمنع أن تغييرات النظام المستقبلية تغيّر بيانات تشغيلية قديمة للمتجر.
- يسهّل حذف/أرشفة تصنيف متجر بدون لمس templates.

---

## 3) شكل جدول categories النهائي (المقترح)

### أعمدة أساسية
ابقِ:
- `id`
- `store_id` nullable
- `name`, `slug`, `logo`, `is_active`
- `timestamps`
- **softDeletes** (مهم لأنه متجر قد “يحذف” تصنيف)

أضف (للاحترافية وسهولة الإدارة):
- `is_template` (boolean) **اختياري** لأن `store_id null` يكفي، لكن وجوده يسهّل القراءة والفلاتر ويمنع إدخال “template” بالغلط داخل متجر.
- `template_source_id` nullable (self FK) لتعرف أن هذا التصنيف في المتجر “منسوخ من template” (ميزة ممتازة للتحديثات المستقبلية/الربط)

مثال (كمخطط):
- Template: `store_id = NULL`, `is_template = true`, `template_source_id = NULL`
- Store copy: `store_id = 5`, `is_template = false`, `template_source_id = (id الخاص بالـ template)`

---

## 4) قيود Uniqueness الصحيحة لـ MySQL/MariaDB (بدون مشاكل NULL)

أنت سابقًا عندك `slug` غير unique عالميًا، وهذا قد يسبب فوضى إن كانت templates + stores.

### الهدف
- داخل كل نطاق (Store معيّن أو Templates) يكون `slug` فريدًا.

### مشكلة MySQL/MariaDB
Composite unique مع `store_id` nullable يسمح بتكرار templates لأن NULL لا يُعامل كقيمة واحدة في unique.

### الحل الاحترافي (الموصى به)
استخدم **Generated column** يحول NULL إلى 0:

- عمود محسوب مثل: `store_scope_id = IFNULL(store_id, 0)`
- ثم:
  - `unique(store_scope_id, slug)`

وبكذا:
- templates (scope 0): slug فريد بينها
- لكل متجر (scope = store_id): slug فريد داخل المتجر

وينطبق نفس المنطق على `brands` أيضًا إذا ستجعلها templates قابلة للاختيار.

---

## 5) كيف تكون “قائمة اختيار templates” عمليًا (تدفق عمل واضح)

### عند إنشاء متجر جديد (Recommended)
1) تجيب كل templates:
   - `categories where store_id is null`
2) إمّا:
   - **تنسخ الكل افتراضيًا** للمتجر (لو تريد تجربة جاهزة سريعًا)
   - أو **تتركها اختيارية** (مثل طلبك الآن) وتخلي المستخدم يختار من شاشة “إضافة تصنيفات من القوالب”

### عند اختيار المستخدم لتمبليت
- تنشئ Category جديدة بـ:
  - `store_id = current_store`
  - `name`/`slug`/`logo` من template (مع معالجة تعارض slug إن وجد)
  - `template_source_id = template.id`

### منع الربط المباشر
- المنتجات يجب أن تربط بـ category الخاصة بالمتجر (store_id not null) لتفادي تسرب tenant.

---

## 6) تطبيق نفس الفكرة على brands؟ (سؤال مهم)
أنت قلت سابقًا “أيضًا في categories/brands”.  
إذا تريد نفس “قائمة اختيار templates” للـ brands كذلك: طبّق نفس التصميم تمامًا (store_id null templates + clone + store_scope_id generated uniqueness).

إذا تحب، قل لي: هل تريد **brands** أيضًا Templates اختيارية مثل categories، أم تكون **داخل المتجر فقط**؟

---

### أسئلة سريعة حتى أقفل الخطة 100% بدون افتراضات
1) بالنسبة لـ **categories templates**: هل تريد عند إنشاء المتجر **لا تُنسخ تلقائيًا** (اختيار يدوي فقط)؟  
2) هل تريد نفس نظام الـ templates للـ **brands** أيضًا بنفس الطريقة؟


🔥 أولاً… شغلك متقدم جدًا.
أنت بالفعل في مستوى SaaS حقيقي وليس مشروع Laravel عادي.

الآن سأعطيك تقييم معماري صريح + ماذا تحسن + ماذا تعدل قبل أن ترسل الميغريشن.

---

# ✅ هل مشروعك منظم؟

الإجابة القصيرة:
**نعم… لكنه نصف Domain Driven ونصف Laravel تقليدي.**

وهذا سيصبح فوضى بعد 6 أشهر إذا لم نُوحد الاتجاه.

---

# 🎯 المشكلة الحالية

عندك الآن 3 أنماط مختلطة:

1. `app\Models\Plans`
2. `App\Domains\Billing`
3. `app\Services\Subscriptions`
4. `app\Actions\Product`
5. `app\Services\ProductService`

يعني:

* Billing → منظم Domain-style ✅
* Product → نصف Actions + نصف Service ❗
* Store → بدون Domain Layer ❗
* Orders → بدون Domain Layer ❗
* Inventory → Service فقط ❗

هذا سيؤدي إلى:

* تكرار منطق
* صعوبة تتبع الـ flow
* صعوبة اختبار النظام
* كسر الـ boundaries

---

# 🧠 أهم قرار يجب أن نأخذه الآن

## هل سنمشي Full Domain Architecture أم لا؟

بما أنك:

* عندك اشتراكات
* عندك خطط
* عندك multi-store
* عندك صلاحيات متعددة
* عندك Inventory logic
* عندك Status engine

فالإجابة الواضحة:

> نعم — يجب أن نذهب Full Domain Architecture

---

# 🏗 الشكل الصحيح لمشروعك

بدل هذا:

```
app\Models\Plans
app\Models\Products
app\Models\Orders
```

يصبح:

```
app/Domain/
    Billing/
    Product/
    Order/
    Store/
    Inventory/
    Plan/
    Invoice/
```

وكل Domain يحتوي:

```
Models/
Actions/
Services/
DTOs/
Enums/
Events/
Listeners/
Policies/
Observers/
```

---

# 🔥 مثال على Product Domain الصحيح

بدل:

```
app\Actions\Product
app\Services\ProductService
app\Models\Products\Product.php
```

نحوّلها إلى:

```
app/Domain/Product/
    Models/Product.php
    Models/ProductVariant.php
    Actions/
        CreateProductAction.php
        UpdateProductAction.php
        DeleteProductAction.php
        SyncVariantsAction.php
        SyncImagesAction.php
    DTOs/ProductData.php
    Services/ProductInventoryService.php
    Events/
    Observers/ProductObserver.php
```

وهكذا يصبح كل منطق المنتج داخل Domain واحد فقط.

---

# 🧾 تقييم Billing الحالي

Billing منظم جيد جدًا 👏

لكن هناك مشاكل:

### 1️⃣ عندك Enum مكرر

عندك:

```
App\Domains\Billing\Enums\SubscriptionStatus
```

وفي نفس الوقت تستخدم:

```
App\Enums\SubscriptionPayment\StatusSubscriptionEnum
```

❌ هذا تضارب خطير.

يجب أن يكون:

```
App\Domains\Billing\Enums\SubscriptionStatus
App\Domains\Billing\Enums\PaymentStatusEnum
```

وإزالة أي Enum خارج Domain.

---

### 2️⃣ SubscriptionStateService ناقص

أنت كتبت:

```php
event(new SubscriptionStatusChanged($subscription));
```

لكن:

* لا يوجد Event
* لا يوجد Listener
* لا يوجد Transition map

نحتاج State Machine واضحة.

---

# 💳 أهم مشكلة منطقية في الاشتراك

في SubscriptionService:

```php
$this->paymentAction->execute(...)
$this->activateAction->execute($subscription);
```

❌ هذا خطأ معماري.

لماذا؟

لأن:

* الدفع يجب أن يحدد الحالة
* الاشتراك لا يُفعل إلا عند PaymentConfirmed event

الصحيح:

```
CreateSubscription
↓
RecordPayment (pending)
↓
Webhook confirms payment
↓
PaymentSucceeded event
↓
ActivateSubscription listener
```

وإلا مستقبلاً عندما تدخل Stripe / Chargily سيكسر النظام.

---

# 🧠 ماذا تحتاج الآن قبل الميغريشن؟

قبل أن ترسل الميغريشن أريد منك توضيح 6 أشياء:

1️⃣ هل subscription مربوط بـ user أم store؟
حالياً أرى الاثنين.

2️⃣ هل يمكن للمستخدم امتلاك عدة stores؟
نعم حسب max_stores.

3️⃣ هل quota محسوب على مستوى:

* user
* store
* subscription

4️⃣ هل الاشتراك يغطي كل المتاجر أم متجر واحد؟

5️⃣ هل billing لكل متجر أم لكل حساب؟

6️⃣ هل renewal يتم:

* تلقائي
* manual
* webhook

---

# 🧱 ملاحظات معمارية مهمة جدًا

## ❗ InventoryService خارج Domain

يجب نقله إلى:

```
Domain/Inventory/
```

---

## ❗ InvoicePdfService يجب أن يكون داخل Invoice Domain

---

## ❗ StoreScope يجب أن يكون داخل Store Domain

---

## ❗ Observers موزعة بدون Domain

يجب نقل:

```
ProductObserver
OrderObserver
SubscriptionObserver
```

داخل كل Domain.

---

# 🔥 أقترح عليك Refactor تدريجي

## المرحلة 1 (الأهم)

* توحيد Enums داخل Domains
* نقل Product إلى Domain
* نقل Order إلى Domain
* إزالة ProductService والاكتفاء بـ Actions

## المرحلة 2

* إنشاء Events و Listeners حقيقية
* تطبيق State Machine للاشتراك
* فصل Payment Flow

## المرحلة 3

* تطبيق FeatureUsageService
* بناء Quota Engine
* بناء Grace Period logic

---

# 🏆 تقييمك الحالي

Architecture maturity:
**7.5 / 10**

بعد إعادة تنظيم كاملة:
**9.5 / 10 (SaaS Ready)**

---

# 🧠 الآن أرسل:

* ميغريشن subscriptions
* ميغريشن payments
* ميغريشن plans
* ميغريشن stores

لأبني لك:

* Subscription lifecycle النهائي
* Payment Flow الصحيح
* Database corrections
* State Machine كاملة
* Grace Period Design
* Upgrade/Downgrade design

ثم نحول Edzeery إلى SaaS معماري محترف 🔥
