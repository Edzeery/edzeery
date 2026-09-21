# ROLES_PERMISSIONS.md — إعادة تصميم أدوار وصلاحيات المتجر (Role / Permission Redesign)

> Scope: النموذج الحالي للصلاحيات (Enum + أدوار) والهدف المعاد تصميمه فقط. **هذه مرحلة توثيق محضة — لا تغيير في أي كود تطبيق.**
> Last verified: 2026-09-21 — كل رقم وكل `file:line` في هذا المستند أُعيد التحقق منه حيًا من الحالة الراهنة للمستودع وقت الكتابة (لا نُسخ من مراجعات قديمة).
> Status: مرجع تخطيط يُراجع قبل فتح مرحلة التنفيذ. الطبقات الثلاث مفصولة صراحةً في القسم 10.

---

## 0. منهجية إعادة التحقق (لماذا تختلف بعض الأرقام عمّا سبق)

كل ادّعاء في الأقسام التالية محدَّث ضد كود اليوم:
- عدد حالات `StorePermissionEnum` **= 47** (أُعيد العد سطرًا سطرًا، انظر القسم 2).
- ملف تدوير خاطئ مُصلَّح: `rg` غير متوفر على `PATH` هنا، فاستُخدم PowerShell `Select-String` (النتيجة: **97** إشارة لـ`ORDER_MANAGE`).
- أثناء الفحص ظهرت **مرجعان لم يُذكرا** في الجرد السابق: `database/seeders/DemoStoreSeeder.php` و`app/Support/PermissionGroupMeta.php` يسندان `crm.orders.confirm` (مُدرجان في قائمة الإزالة، القسم 4).
- علامة «(Soon)» على `returns.*` **قديمة** — الصلاحيتان حيّتان وظيفيًا (القسم 7).

---

## 1. النموذج الحالي: 4 أدوار + مبدأ «الصلاحية لا نوع الدور»

النظام الحالي — كما تقرر في **Decision #6** (`MerchantPanelAudit.md`) — لا يستخدم Spatie Teams حرفيًا (`teams => false`)، بل عزل لكل متجر على `store_memberships`:

| موضع | الوصف |
|------|-------|
| `store_memberships.role` | دور العضو **داخل هذا المتجر** (`StoreRoleEnum`) |
| `store_membership_permissions` | pivot → صلاحيات مخزّنة مخصصة للعضو (تفوق الأدوار) |
| `store_memberships.supervisor_membership_id` | هرمية المشرفين (من نقل مديري الفرق) |

ترتيب الحل (`canStore()` في `app/Helpers/helpers.php`): بلا سياق متجر → `false`؛ `super_admin/admin` (web) → نعم (bypass)؛ ثم الصلاحيات المخزّنة للعضوية إن وُجدت؛ إلا فالعودة إلى Spatie العامة (`guard_name='merchant'`).

**مبدأ التصميم:** الأربعة أدوار **قالب** فقط، والسلوك الفعلي يُقاد بالصلاحيات (`StoreMembership::can()`، `StoreMembership.php:93` — المتجر أولاً ثم الولاية) لا بالنوع. التسلسلات المطلوبة التي بُيّنت عبر المراحل السابقة وترتبط مباشرةً بهذا المستند:

- **هرمية المشرفين** — `managesMember()` (`helpers.php:198`) تقرر «المدير يدير فريقه فقط» عبر `supervisor_membership_id`.
- **النطاق المنتجي** — `StoreProductScopeService::assignedProductIds()` + جزء `product-scope-mount` في `teams/index` (مرحلة 36.3).
- **نطاق رؤية الطلبيات** — `Order::visibleTo(?StoreMembership)` / `HasVisibilityScope` (مرحلة 36.4): owner/admin يرون الكل، manager يرون فريقهم، staff أنفسهم فقط.
- **نطاق رؤية الفوترة** — `canViewStoreBilling(Store, ?User)` (`helpers.php:247`) المعتمد في مرحلة 36.5 (choose-store / لويحة `panel.blade.php` / قائمة `account.stores`).

لذلك أي إضافة/إزالة صلاحية من القوالب **يجب** أن تتفق مع هذه النطاقات الأربعة — وهو المعيار الذي بُنيت عليه قرارات القسم 3.

---

## 2. المخزون الحالي الكامل لـ `StorePermissionEnum` (47) حسب `group()`

`group()` (سطر 132-135 من الـEnum) = **الجزء الأول من القيمة** (`explode('.')[0]`)، وهو ما تعتمده نافذة الـ Permission Hub (`PermissionGroupMeta::GROUP_ORDER`, `PermissionGroupMeta.php:22`). لاحظ أن تعليقات الكتلة في ملف الـ Enum المجموعة أفقيًا (مثل «Team (Global)») **تختلف** عن تقسيم `group()` — مثلًا `store.team.manage` معلَّق تحت الفريق لكن `group()` يضعه تحت `store`، و`team.view.own / team.manage.own` تحت `team`. الجدول المعياري التالي هو تقسيم `group()`:

### `store` (7)
| الحالة | القيمة | ملاحظات |
|--------|--------|---------|
| `STORE_VIEW` | `store.view` | |
| `STORE_UPDATE` | `store.update` | |
| `STORE_DELETE_FINAL` | `store.delete.final` | سيادة — owner فقط |
| `STORE_TRANSFER_OWNERSHIP` | `store.transfer.ownership` | سيادة — owner فقط |
| `STORE_BILLING_MANAGE` | `store.billing.manage` | سيادة — owner فقط (قسم 6) |
| `STORE_SETTINGS_SENSITIVE` | `store.settings.sensitive` | سيادة — owner فقط |
| `STORE_TEAM_MANAGE` | `store.team.manage` | |

### `products` (4)
`PRODUCT_VIEW products.view` · `PRODUCT_CREATE products.create` · `PRODUCT_UPDATE products.update` · `PRODUCT_DELETE products.delete`

### `order` (9)
`ORDER_VIEW order.view` · `ORDER_MANAGE order.manage` · `ORDER_CONFIRM order.confirm` · `ORDER_CANCEL order.cancel` · `ORDER_DELETE order.delete` · `ORDER_DELETE_FINAL order.delete.final` · `ORDER_ASSIGN order.assign` · `ORDER_EDIT_PRICE order.edit.price` · `ORDER_DISPATCH_VALIDATE order.dispatch_validate`

### `inventory` (2)
`INVENTORY_VIEW inventory.view` · `INVENTORY_UPDATE inventory.update`

### `team` (5)
`TEAM_VIEW team.view` · `TEAM_INVITE team.invite` · `TEAM_REMOVE team.remove` · `TEAM_VIEW_OWN team.view.own` · `TEAM_MANAGE_OWN team.manage.own`

### `crm` (4)
`CRM_ORDER_TRACKING crm.orders.track` · `CRM_ORDER_CONFIRMATION crm.orders.confirm` (يُحذف نهائيًا، قسم 4) · `CRM_INVENTORY_TRACKING crm.inventory.track` · `CRM_INVENTORY_MANAGE crm.inventory.manage`

### `delivery` (5)
`DELIVERY_PRICING_MANAGE delivery.pricing.manage` · `DELIVERY_RIDERS_VIEW delivery.riders.view` · `DELIVERY_RIDERS_CREATE delivery.riders.create` · `DELIVERY_RIDERS_UPDATE delivery.riders.update` · `DELIVERY_RIDERS_DELETE delivery.riders.delete`

### `accounting` (1)
`ACCOUNTING_CONFIRM_TEAM accounting.confirm.team` — معلَّمة `// soon` (سطر 96) (قسم 7)

### `finance` (4)
`FINANCE_DEBT_VIEW finance.debt.view` · `FINANCE_DEBT_CREATE finance.debt.create` · `FINANCE_DEBT_UPDATE finance.debt.update` · `FINANCE_DEBT_DELETE finance.debt.delete`

### `returns` (2)
`RETURNS_VERIFY_BARCODE returns.verify.barcode` · `RETURNS_PROCESS returns.process` — معلَّمتان تحت «Returns (Soon)» لكنهما حيّتان وظيفيًا (قسم 7)

### `stats` (4)
`STATS_CONFIRMATION stats.confirmation` · `STATS_DELIVERY stats.delivery` · `STATS_TOP_KPIS stats.top.kpis` · `STATS_TEAM_VIEW stats.team.view`

**المجموع: 7+4+9+2+5+4+5+1+4+2+4 = 47.** (يُراجع مقابل الـ Enum إن تغيّر لاحقًا.)

---

## 3. المصفوفة المعاد تصميمها (الحالة المستهدفة لـ `app/Support/StoreRoles.php`)

### 3.1 القوالب

| الصلاحية | OWNER | ADMIN | MANAGER | STAFF |
|----------|:-----:|:-----:|:-------:|:-----:|
| `store.view` | ✓ | ✓ | ✓ | ✓ |
| `store.update` | ✓ | ✓ | ✓ | — |
| `store.team.manage` | ✓ | ✓ | — | — |
| `store.delete.final` | ✓ | — (سيادة) | — | — |
| `store.transfer.ownership` | ✓ | — | — | — |
| `store.billing.manage` | ✓ | — | — | — |
| `store.settings.sensitive` | ✓ | — | — | — |
| `products.view` | ✓ | ✓ | ✓ | ✓ |
| `products.create` | ✓ | ✓ | ✓ | — |
| `products.update` | ✓ | ✓ | ✓ | — |
| `products.delete` | ✓ | ✓ | ~~✓~~ **—**¹ | — |
| `order.view` | ✓ | ✓ | ✓ | ✓ |
| `order.manage` | ✓ | ✓ | ✓ | — |
| `order.confirm` | ✓ | ✓ | ~~✓~~ **—**² | ✓ |
| `order.cancel` | ✓ | ✓ | ✓ | ~~✓~~ **—**³ |
| `order.delete` | ✓ | ✓ | — | — |
| `order.delete.final` | ✓ | — (سيادة) | — | — |
| `order.assign` | ✓ | ✓ | ✓ | — |
| `order.edit.price` | ✓ | ✓ | — | — |
| `order.dispatch_validate` | ✓ | ✓ | — | — |
| `inventory.view` | ✓ | ✓ | ✓ | ✓ |
| `inventory.update` | ✓ | ✓ | ✓ | — |
| `team.view` | ✓ | ✓ | — | — |
| `team.invite` | ✓ | ✓ | — | — |
| `team.remove` | ✓ | ✓ | — | — |
| `team.view.own` | ✓ | ✓ | ✓ | — |
| `team.manage.own` | ✓ | ✓ | ✓ | — |
| `crm.orders.track` | ✓ | ✓ | ~~✓~~ **—**² | ~~✓~~ **—**³ |
| `crm.orders.confirm` | (يُحذف نهائيًا — قسم 4) | | | |
| `crm.inventory.track` | ✓ | ✓ | ✓ | — |
| `crm.inventory.manage` | ✓ | ✓ | ✓ | — |
| `delivery.pricing.manage` | ✓ | ✓ | ~~✓~~ **—**⁴ | — |
| `delivery.riders.view` | ✓ | ✓ | ✓ | — |
| `delivery.riders.create` | ✓ | ✓ | ✓ | — |
| `delivery.riders.update` | ✓ | ✓ | ✓ | — |
| `delivery.riders.delete` | ✓ | ✓ | — | — |
| `accounting.confirm.team` | ✓ | ✓ | — | — |
| `finance.debt.view` | ✓ | ✓ | ✓ | — |
| `finance.debt.create` | ✓ | ✓ | ✓ | — |
| `finance.debt.update` | ✓ | ✓ | ✓ | — |
| `finance.debt.delete` | ✓ | ✓ | — | — |
| `returns.verify.barcode` | ✓ | ✓ | ✓ | ✓ |
| `returns.process` | ✓ | ✓ | ✓ | — |
| `stats.confirmation` | ✓ | ✓ | ✓ | ✓ |
| `stats.delivery` | ✓ | ✓ | ✓ | — |
| `stats.top.kpis` | ✓ | ✓ | — | — |
| `stats.team.view` | ✓ | ✓ | ✓ | — |

العنصر ~~المشطوب~~ = **يُشطب من القالب الافتراضي** في مرحلة التنفيذ (مع بقائه في الـ Enum ما لم يُذكر خلاف ذلك). Owner وAdmin **بلا تغيير** عن الحالي (`StoreRoles.php:19` و`:26-35`) — Admin يستثني فقط: `store.delete.final`, `store.transfer.ownership`, `store.billing.manage`, `store.settings.sensitive`, `order.delete.final`.

### 3.2 MANAGER — الإزالات الخمس وسبب كل منها

القالب الحالي `StoreRoles.php:42-93` (31 صلاحية) → **26 بعد الإزالة**. الإزالات:

1. **`ORDER_CONFIRM`** — القاعدة الافتراضية للمدير = لا يستلم تأكيد الطلبيات تلقائيًا؛ **يتعارض مع قرار المرحلة 36.2 («المدير لا يستلم الطلبيات تلقائيًا»)**. التأكيد سلوك صريح يُمنح عبر Permission Hub، لا أصلًا في القالب. (يستمر في قالب `STAFF`.)
2. **`CRM_ORDER_CONFIRMATION`** — تُحذف من الـ Enum نهائيًا (قسم 4).
3. **`CRM_ORDER_TRACKING`** — التتبّع = ترقية تشغيلية صريحة؛ المدير الافتراضي لا يتتبّع تلقائيًا، تماشيًا مع نفس القرار أعلاه.
4. **`PRODUCT_DELETE`** — فعل إتلافي على نطاق المتجر كله لا ينسجم مع فلسفة النطاق الفريقي/المنتجي (هو «مدير فريقه فقط»): يمكن للإدارة الإضافية الناتجة عن الفريق إتلاف منتج لا ملكيته لهم. (تبقى للإدارات العليا فقط.)
5. **`DELIVERY_PRICING_MANAGE`** — صلاحية مالية على تسعير كل التوصيل لا تليق بالقالب الفريقي؛ تُمنح صراحةً لمن تحتاجها تشغيليًا. (أُزيلت فعلًا من القائمة بنفس منطق «الأفعال المالية الواسعة لا تكتب في قوالب الفرع».)

### 3.3 STAFF — الإزالات الثلاث وسبب كل منها

القالب الحالي `StoreRoles.php:100-124` (10 صلاحيات) → **7 بعد الإزالة**:

1. **`CRM_ORDER_CONFIRMATION`** — تُحذف من الـ Enum نهائيًا (قسم 4).
2. **`CRM_ORDER_TRACKING`** — خط الأساس الافتراضي للموظف = **تأكيد فقط**؛ التتبّع وثنائية تأكيد+تتبّع أصبحتا **ترقية صريحة** تُمنح من الـ Permission Hub لا افتراضيًا.
3. **`ORDER_CANCEL`** — الإلغاء حاليًا مفعل افتراضيًا للموظف؛ يُحوَّل إلى **ترقية صريحة** (بحاجة لموافقة أصحاب مسار الإلغاء قبل التفعيل الافتراضي). يبقى `ORDER_CONFIRM` في القالب.

---

## 4. قرار: إزالة `CRM_ORDER_CONFIRMATION` نهائيًا من `StorePermissionEnum` + قائمة الملفات

**السبب:** المستهلك الوظيفي الوحيد كان بديلًا OR في لوحة التحكم فقط:

- `resources/views/livewire/merchant/dashboard.blade.php:16`
  ```php
  $canConfirm = canStore(StorePermissionEnum::ORDER_CONFIRM->value) || canStore(StorePermissionEnum::CRM_ORDER_CONFIRMATION->value);
  ```
- كل مسارات التأكيد الوظيفية تتحقق من `ORDER_CONFIRM` حصريًا (خريطة حالة → صلاحية: `StoreOrderPermissions::forStatus()`، وقوالب `orders/index.blade.php`، وشريط الحالات). لا يوجد مسار تشغيل حقيقي يستدعي `crm.orders.confirm` دون `order.confirm`.

**مرجعان إضافيان لم يذكرا في الجرد السابق وظهرا أثناء إعادة التحقق** — يجب معالجتهما في مرحلة التنفيذ:

| # | الملف:السطر | الأثر |
|---|-------------|-------|
| 1 | `app/Enums/Store/StorePermissionEnum.php:82` | حذف الحالة (مصدر الحقيقة) |
| 2 | `app/Support/StoreRoles.php:70` (MANAGER) و`:113` (STAFF) | حذف الإشارتين من القوالب |
| 3 | `resources/views/livewire/merchant/dashboard.blade.php:16` | اختزال إلى `ORDER_CONFIRM` فقط |
| 4 | `resources/lang/{ar,en,fr,es}/permissions.php` | حذف المفتاح المتداخل `crm.orders.confirm` (مثلاً en سطر 15 «Confirm Orders in CRM») — 4 ملفات |
| 5 | **`database/seeders/DemoStoreSeeder.php:498` و`:522`** | صفيفا صلاحيات `demo.confirmer@` و`demo.dual@` (ميثود `seedPermissionScopedTeam`) — حذف الإشارة وإلا مرجع حالة ميتة |
| 6 | **`app/Support/PermissionGroupMeta.php:97`** | `'crm.orders.confirm' => ['order.view']` في `DEPENDENCIES` — مفتاح نصي لن يكسر الترجمة، لكنه يصبح بيانات قديمة بعد الحذف؛ يُحذف من خارطة الاعتماديات |

ملاحظة: الـ Permission Hub يعدد `StorePermissionEnum::values()`، لذا حذف الحالة يُزيل الصلاحية من الواجهة تلقائيًا. ترتيب القائمة أعلاه هو **قائمة تحقق** — الحذف من الـ Enum يكسر الترجمة في أي ملف متبقٍ، لذا ابدأ من الملفات ثم الـ Enum، ثم تحقق بجودة `grep -r "CRM_ORDER_CONFIRMATION"` = صفر.

---

## 5. فجوات الترجمة — مؤكدة حيًا لكل اللغات الأربع (ar / en / fr / es)

ملفات الترجمات **متداخلة** وليست مسطّحة: التسمية تُقرأ عبر `PermissionGroupMeta::label()` بـ `__("permissions.{$permission}")` (`PermissionGroupMeta.php:150-160`)، فيوجد مفتاح `order.view` كعش لـ`'order' => ['view' => …]` وليس سلسلة `'order.view'`. أُعيد الفحص صفًا صفًا لملفات `resources/lang/{ar,en,fr,es}/permissions.php` بتاريخ 2026-09-21:

| المفتاح | الحالة في اللغات الأربع | الأثر في الواجهة |
|---------|:---:|------------------|
| `order.assign` | **مفقود** (لا `assign` تحت `order`، و`ORDER_ASSIGN` موجود في التعداد) | الـ Hub يعرض التسمية الاحتياطية من الـ Enum «Order Assign» |
| `order.dispatch_validate` | **مفقود** (لا `dispatch_validate`) | نفس الاحتياطي «Order Dispatch Validate» |
| `order.delete.final` | **غائب كليًا** — `order.delete` سلسلة نصية («Delete Order») وليست عشًا، فلا يمكن للمفتاح `delete.final` أن يُحلّ إطلاقًا | لا توجد تسمية، ولا احتياطي سليم بعيد المدى |
| `team.view` | **مكسور بنيويًا** — `team.view` عش `['own'=>…]` فقط بدون قيمة سلسلة للمفتاح البسيط | `__('permissions.team.view')` يرجع **مصفوفة** لا سلسلة — الانحدار موثَّق في `tests/Feature/Merchant/StoreAuthorizationGatesTest.php:139` |

**انحدار مطلوب إصلاحه:** العش `team.view` يحتاج قيمة سلسلة (مثل «View Team») مع بقاء `team.view.own` متداخلًا — وإلا أي استدعاء مباشر `permissions.team.view` سيمرر مصفوفة.
**اجعل الإصلاح يشمل اللغات الأربع معًا** (على غرار «مفاتيح متداخلة per locale» في مراجع 31.8).

---

## 6. تحسين التسميات: `order.manage` و`store.billing.manage` (وصف لا تسمية فقط)

| الصلاحية | التسمية الحالية | النطاق الفعلي | المطلوب |
|----------|-----------------|---------------|---------|
| `order.manage` | «Manage Orders» فقط | **97 موضع استدعاء** أُعيد عدّها حيًا عبر `app/` + `resources/views/livewire` (باستبعاد tests وcache): `orders/index.blade.php` = 51، `orders-table-cell.blade.php` = 18، `order-settings.blade.php` = 6، والبقية موزعة على الوحدات — منها `OrderPolicy.php:43`، `TrackingDrawerConcern.php:364/:571`، `TrackingRiderFormConcern.php:754/:829`، `CancelsShipmentFromOrdersTable.php:12`، وعشرات فحصات `canStore(...)` في أعمدة الطلبيات والتتبع ونافذة التأكيد وشريط الإجراءات الجماعية وقائمة التوزيع (`index.blade.php` الحارس `abort_unless` في +20 موضعًا) | **وصفٌ** في نافذة الـ permission Hub يشرح أن الصلاحية تتحكم بالطلبيات + التتبع + طابور التوزيع معًا (لا «إدارة طلبات» بسيطة) — ومستودع الوصفات المقترح: `resources/lang/{locale}/permission_groups.php` (الملف الذي يحمل عناوين/أوصاف المجموعات حاليًا، موجود في اللغات الأربع) أو نطاق مفاتيح `permissions_descriptions.*` |
| `store.billing.manage` | «Manage Store Billing» فقط | سيادة owner حصري في عمليا (قائمة `DANGEROUS` في `PermissionGroupMeta.php:64`؛ استثناء من ADMIN في `StoreRoles.php:30`؛ الحارس `canViewStoreBilling` في `helpers.php:247`)؛ الاسم يوحي بصلاحية إدارة عادية للمدير | **وصفٌ يبيّن الطبيعة السيادية/owner-only** (إدارة الفوترة والاشتراك والمدفوعات للمتجر — لا تمنح إلا للمالك)، وتنبيه بصري إن أمكن في الـ Hub وفق علامة الخطورة |

الهدف: ألا يمنح المشرف في الـ Permission Hub صلاحية بحجم «إدارة الطلبات كلها» أو «سيادة الفوترة» وهو لا يدرك نطاقها.

---

## 7. الصلاحيات «(Soon)» — القرار ووضعها الفعلي (مؤكّد حيًا)

مواقع العلامات في `app/Enums/Store/StorePermissionEnum.php`:
- `ACCOUNTING_CONFIRM_TEAM = 'accounting.confirm.team'; // soon` (سطر 96).
- رأس الكتلة «Verification / Returns (Soon)» (سطر 109-114) فوق `RETURNS_VERIFY_BARCODE` و`RETURNS_PROCESS`.

**إعادة التحقق كشفت عدم تجانس الحالتين:**

1. **`accounting.confirm.team` — فعلاً غير مبنية بعد** ✔: لا مستهلك وظيفي (لا `canStore`، لا صفحة، لا حارس). تظهر فقط كتسمية في `permissions.php×4`.

2. **`returns.verify.barcode` + `returns.process` — العلامة قديمة، الصلاحيتان حيّتان وظيفيًا** ✘: الشريط الجانبي `store-sidebar.blade.php:58` (`canViewReturns`)، وصفحة الإرجاع `merchant/returns/index.blade.php` تحصرهما دفاعيًا ووظيفيًا (سطور `:27` `:64` `:93` `:104` `:132` `:255` `:269`)، مع إسناد `RETURNS_PROCESS` تلقائيًا في قوالب MANAGER والمُصرّح في السيدر. لذلك **لا يصح** وسمها «قريبًا»؛ بل يُقترح في مرحلة التنفيذ إسقاط رأس «(Soon)» من الـ Enum لقطاع Returns.

**القرار (سُجّل من المستخدم بتاريخ 2026-09-21):** تُعرض صلاحيات (Soon) الحقيقية في نافذة الـ Permission Hub **ممكنة/رمادية مع شارة «قريبًا / coming soon»** بدل إخفائها كليًا — بحيث يبقى خارطة الطريق مكتشفة. التطبيق العملي:
- `accounting.confirm.team` → معروضة (رمادية + شارة Soon).
- `returns.*` → تُعرض كصلاحيات عادية (الشارة لا تليق بها) ومعها إسقاط الرأس التعليقي.

---

## 8. مؤجَّل لمرحلة لاحقة — خارج نطاق هذه الإعادة التصميمية (لا تنفَّذ مع جدول 3)

### 8.1 `ORDER_ASSIGN` بلا تقاطع نطاق (بطاقة «scope-blind»)

**الشاهد:** فحص الإسناد في كل مكان قائم على `canStore(ORDER_ASSIGN)` وحدها **دون** `Order::visibleTo()` أو أي نطاق فريقي/منتجي:
- `app/Helpers/helpers.php:169` — `canReassignOrders()` = `canStore(ORDER_ASSIGN)` فقط.
- `app/Livewire/Concerns/TrackingRiderFormConcern.php:29` — `abort_unless(canStore(ORDER_ASSIGN), 403)`.
- `resources/views/livewire/merchant/orders/index.blade.php:1179` — حارس الإسناد الجماعي.
- `resources/views/livewire/merchant/tracking/partials/order-drawer.blade.php:132` — عرض زر «إعادة إسناد قيد التتبع».

**المشكلة:** عضو يمر فحص الدور (وسيط فعليًا) وبيده `ORDER_ASSIGN` يعيد إسناد **أي** طلبية في المتجر بغض النظر عن نطاق `visibleTo()` الفعلي. فرق: الموظف يبقى محجوبًا رغم منحه الصلاحية صراحةً — لأن مسار فتح النافذة يضيف حارس دور إضافيًا (مثبت في `tests/Feature/Merchant/TrackingBulkActionsTest.php:254-278` «staff stay blocked even with an explicit ORDER_ASSIGN») — لكن هذا الحارس الإضافي **غير موجود** لمسار المدير/المسؤول، فهو عندهم معرّى من نطاق الفريق/المنتج.

**الحدود:** خارج نطاق هذا المستند — يحتاج فحصًا بعد الجدول 3 (تُحل بـ GSM لا بالتخبيب، مثل أداء رؤية الطلبيات نفسه).

### 8.2 ازدواجية التفويض بإزالة العضو (`canModifyMember` مقابل الـ Policy)

مساران متعاونان بـ«فلسفتي autorization» مختلفين لسلوك «تعديل/إزالة عضو»:

| المسار | الملف:السطر | المنطق |
|--------|-------------|--------|
| Livewire (نافذة الفريق/أزرار الهوية) | `canModifyMember()` `helpers.php:265-275` → `managesMember()` `helpers.php:198-228` | **هرمي بشكل حصري**، بلا أي إذن صلاحية: owner/admin يديران الجميع؛ المدير يدير مرؤوسيه المباشرين (`supervisor_membership_id`) |
| Policy (`Gate`) | `StoreMembershipPolicy::delete()` `StoreMembershipPolicy.php:81-85` (`deactivate` :87-90) | `canTouch()` + `actor()->can(TEAM_REMOVE)`؛ و`update()` سطر 78 يتطلب `STORE_TEAM_MANAGE` |

**التعارض:** قالب MANAGER (`StoreRoles.php:42-93`) لا يملك `TEAM_REMOVE` ولا `STORE_TEAM_MANAGE` — مع ذلك `canModifyMember()` قد تُرجع `true` لمرؤوسه، فيظهر زر الإزالة في الواجهة بينما يرفضه الـ Policy فعليًا. عكس الاتجاه: `STORE_TEAM_MANAGE` / `TEAM_REMOVE` وحدها (بدون هرمية) تمر الـ Policy لمسؤول قد لا يكون مشرفًا فعليًا. قرار التوحيد (هل يصبح `canModifyMember` مُسمّى «هرمية + صلاحية»؟ أم يكتفي الـ Policy؟) **مؤجل**.

---

## 9. تسريب خصوصية في `account/billing.blade.php` (رابع موضع — منفصل عن مواقع 36.5)

**الوضع الحالي (مؤكّد حيًا):**

- `resources/views/livewire/account/billing.blade.php:71`:
  `$this->stores = $u->stores()->with('payments')->distinct()->get();`
- `User::stores()` (`app/Models/User.php:148-152`): `belongsToMany` عبر `store_memberships` مع `wherePivot('is_active', true)` → أي متجر فيه المستخدم **عضوية نشطة بأي دور** (ليس فقط المالكة؛ المالك نفسه يملك أيضًا صف membership من نوع OWNER).
- العرض «Section 4: Store Billing» (`billing.blade.php:575-615`): يعدّ لكل متجر أحدث دفعة (`:577-580`) ويعرض شارة **مدفوع/غير مدفوع** (`:592-603`).

**الأثر:** مستخدم بدوره STAFF فقط داخل متجر تاجرٍ آخر — شاهده حساب الفوترة الشخصي غالبًا — **يقرأ حالة سداد متجر ليس ملكه** في صفحة حسابه الخاصة. مرحلة 36.5 حجبت ثلاث شاشات داخل سياق المتجر؛ هذه هي **نطاق الحساب الشخصي** (account zone) ولا يشملها ذلك الحجب.

**الإصلاح المقترح (بند تحقق لمرحلة التنفيذ):** تقييد الاستعلام إلى المتاجر المملوكة فقط:
```php
$this->stores = $u->storesOwned()->with('payments')->get();
// أو (بدون تغيير العلاقة): $u->stores()->where('stores.user_id', $u->id)->with('payments')->distinct()->get();
```
مع مراجعة «Section 4» أن تظهر المتاجر المملوكة فقط (وهذا منطقها الأصلي — صفحة فوترة المستخدم).

---

## 10. الطبقات الثلاث — خلاصة صريحة

### طبقة أ: قرارات جاهزة للتنفيذ في مرحلة «إعادة تنظيم الأدوار/الصلاحيات»
1. مصفوفة القسم 3 (Owner/Admin ثابتان؛ Manager `-5`؛ Staff `-3`) في `app/Support/StoreRoles.php`.
2. حذف `CRM_ORDER_CONFIRMATION` نهائيًا + قائمة تحقق القسم 4 (6 ملفات/8 إشارة — من ضمنها السيدر و`PermissionGroupMeta`).
3. إصلاح فجوات الترجمة (`order.assign`, `order.dispatch_validate`, `order.delete.final` غائبة، و`team.view` مكسورة) في اللغات الأربع.
4. إضافة **وصفَي** `order.manage` و`store.billing.manage` في الـ Hub (القسم 6).
5. إصلاح تسريب `account/billing.blade.php` (القسم 9) — `storesOwned()` أو شرط `stores.user_id`.
6. معاملة (Soon): `accounting.confirm.team` ← شارة «قريبًا»؛ إسقاط رأس «(Soon)» عن `returns.*`.

### طبقة ب: أسئلة مفتوحة
- **لا توجد أسئلة مفتوحة معلّقة في هذا المستند** — السؤال المفتوح الوحيد (طرق عرض صلاحيات (Soon) في الـ Hub) أُجيب من المستخدم 2026-09-21 (خيار «مع شارة قريبًا»). أي سؤال يظهر أثناء تنفيذ القسم 3/4 يُضاف هنا قبل التكهن بالحل.

### طبقة ج: مؤجَّلة صراحةً، خارج نطاق مرحلة إعادة التنظيم
- تقاطع نطاق `ORDER_ASSIGN` (القسم 8.1).
- توحيد ازدواجية إزالة العضو `canModifyMember` / `StoreMembershipPolicy` (القسم 8.2).

---

## 11. قيود نطاق ونقاط قبول مرحلة التنفيذ (إنذار فحسب)

- لا تُعدَّل ملفات تطبيق في هذه المرحلة التوثيقية (36.6). ملفات مرجع التنفيذ: `StoreRoles.php`، `StorePermissionEnum.php`، `permissions.php×4` (+`permission_groups.php` إن وُضعت الأوصاف)، سيدر Demo، `PermissionGroupMeta.php`، `dashboard.blade.php`، `account/billing.blade.php`.
- الالتزام بالنطاقات الأربعة (هرمية/منتجات/رؤية طلبيات/فوترة) عند أي تعديل قالب.
- شهادة قبول: `grep` صفري لـ`CRM_ORDER_CONFIRMATION` و`crm.orders.confirm`؛ اختبارات Role/StoreRoles (`RoleScopingTest`, `StoreAuthorizationGatesTest`, `TrackingBulkActionsTest`, `BillingVisibilityScopingTest`) خضراء؛ `php -l` نظيف؛ جولة Merchant كاملة خضراء.
- لا تعديل لإسراف خارج الجدول: مكتبات/خصم، Filament SuperAdmin والقوالب اليتيمة، نطاق الطلبيات أو تدفقات التتبع، الصياغة قبل اتجاه المصفوفة (قسم 3) يتطلب قرار المستخدم.

---

**Implemented: 2026-09-21** — نُفّذت الطبقة A (البند 1–6) كاملةً في مرحلة 36.7: مصفوفة StoreRoles (Manager `-5`، Staff `-3`)، إزالة `CRM_ORDER_CONFIRMATION` عبر قائمة القسم 4 (files أولًا ثم Enum — grep صفري في الكود والاختبارات)، إصلاح الترجمات الأربع + فرع المصفوفات في `PermissionGroupMeta::label()`، أوصاف صلاحيات جديدة (`permissions_descriptions.php×4` + `description()` + عرض في الـ Hub)، إصلاح الخصوصية في `account/billing.blade.php` عبر `storesOwned()`، وشارة «قريبًا» في الـ Hub (`COMING_SOON` + `isComingSoon()` + تعطيل التبديل) مع إزالة تعليقات (Soon) من الـ Enum. الشهادة: Merchant **628 ناجح (2711 تأكيد)**، Account+Auth **43 ناجح (112 تأكيد)**.