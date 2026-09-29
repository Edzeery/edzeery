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

---

## Phase 36.11 — Granular Order & Tracking Permissions (تصنيف دقيق لصلاحيات الطلبيات والتتبع)

> **نطاق هذه المرحلة: توثيق فقط.** صفر تغيير في أي ملف تطبيق. لا حالة في `StorePermissionEnum`، لا تغيير في `StoreRoles.php`، لا سطر واحد يُمس في `orders/index.blade.php` (5639 سطرًا).
> **Last re-verified: 2026-09-27** على `HEAD = cdc4402` (شجرة عمل نظيفة، `git status --porcelain` فارغ). كل `file:line` أدناه أُعيد جلبه حيًا من الملفات، لا من أي مراجعة سابقة.

### 0. منهجية إعادة التحقق + الأرقام الحقيقية (كل رقم أُعيد قياسه)

| # | الادّعاء الوارد في تخطيط الجلسة | ما الذي أُعيد قياسه حيًا اليوم | الحكم |
|---|-------------------------------|----------------------|-------|
| 1 | `orders/index.blade.php` فيه «~96 موضع فحص» | **51 سطر** يحوي `ORDER_MANAGE` | ❌ **خاطئ — الرقم 51** (مطابق للقسم 6 من هذا المستند؛ الرقم «96» يخصّ التطبيق كله لا هذا الملف) |
| 2 | الملف 5639 سطرًا | **5639** | ✔ صحيح (ملاحظة: `Get-Content \| Measure-Object -Line` يُرجع 4854 لأنه لا يعدّ الأسطر الفارغة — خطأ قياس شائع) |
| 3 | الأعمدة مُجمّعة في **ثلاث** مجموعات | **أربع** مجموعات: `identity`، `products_financial`، `geography`، **و`workflow`** | ❌ **ناقص** — راجع 0.2 |
| 4 | `TrackingRiderFormConcern.php` + تقييد تبويب الراكب في `TrackingGridConcern.php` + `orders/index.blade.php` + `order-drawer.blade.php` = كل مواضع `ORDER_ASSIGN` | **لا وجود لأي فحص `ORDER_ASSIGN` في `TrackingGridConcern.php`** إطلاقًا | ❌ **مُختلق** — راجع 3.1 |
| 5 | `ORDER_ASSIGN` في `orders/index.blade.php` | سطر **واحد فقط** (`:1177`، الإسناد الجماعي) | ✔ صحيح |
| 6 | عدد حالات `StorePermissionEnum` | **46** (لا 47 — أُزيلت `CRM_ORDER_CONFIRMATION` فعلًا في 36.7) | ✔ متسق مع القسم 2 |

**0.1 — عدّاد التطبيق كله:** `app/` + `resources/` (بدون tests/cache) = **99 سطرًا** يحوي `ORDER_MANAGE`. منها 3 ليست «مواضع فحص» بل: تعريف الـ Enum (`StorePermissionEnum.php:41`)، وإدراج القالب (`StoreRoles.php:55`)، والـ fallback غير المادي في `StoreOrderPermissions.php:61`. ⇒ **96 موضع فحص وظيفيًا** في التطبيق كله (51 منها في `orders/index.blade.php`).

**0.2 — اكتشاف جوهري: مجموعات الأعمدة أربع لا ثلاث.** مصدر التجميع هو الدالة `$orderColumns` (مغلَّفة في الـ Livewire component نفسه) في `resources/views/livewire/merchant/orders/index.blade.php:581-630`، وليس config ولا enum مستقل. رأس كل مجموعة عند:

| المجموعة | سطر رأسها | الأعمدة القابلة للتحرير فيها (`editable => true`) | صلاحيتها الحالية |
|---------|-----------|---------------------------------------------------|-------------------|
| `identity` | `:595` | `customer` (`:598`)، `phone` (`:599`)، `notes` (`:601`) | `ORDER_MANAGE` |
| `products_financial` | `:604` | `products` (`:605`)، `quantity` (`:606`)، `price` (`:607`)، `weight` (`:610`)، `shipment_type` (`:611`)، `discount` (`:612`) | `ORDER_MANAGE` — عدا `price` فعليًا: `ORDER_EDIT_PRICE` + `store.settings.allow_price_edit` عبر `$itemsPriceEditable()` (`:299-310`) |
| `geography` | `:614` | `wilaya` (`:615`)، `city` (`:616`)، `address` (`:617`)، `delivery_type` (`:618`)، `shipping_provider` (`:619`)، `stopdesk_point` (`:620`)، `send_from_carrier_warehouse` (`:621`) | `ORDER_MANAGE` |
| **`workflow`** | **`:623`** | **`status` (`:624`)، `assigned_agent` (`:625`)** | `ORDER_MANAGE` |

**الأثر على التصميم:** عمود `status` وعمود `assigned_agent` — وهما بالضبط العمودان الأكثر حساسية تشغيليًا — يقعان في `workflow`، **وليس** في المجموعات الثلاث. لذلك ثلاث صلاحيات `order.edit.*` لن تغطّي تعديل الحالة ولا إعادة إسناد الوكيل من الجدول. هذا قرار صريح لا نقص: `status` يذهب إلى `order.status.manage.own` (البند 2)، و`assigned_agent` **يبقى على `order.manage`** كـ ADMIN-OTHER لأنه فعل إسناد فريق حقيقي لا تحرير حقل (البند 1.2، المواضع 30–32).

**0.3 — `visibleTo()` ليس دالة كائن بل نطاق استعلام (تصحيح صاغة).** القسم 1 من هذا المستند يكتب `Order::visibleTo(?StoreMembership)`؛ الصيغة الحقيقية هي نطاق builder من `HasVisibilityScope` (مستخدم في `app/Models/Orders/Order.php:14` import و`:26` use) — `app/Models/Orders/Concerns/HasVisibilityScope.php:26-52`:
- `scopeVisibleTo(Builder $query, ?StoreMembership $membership)` (`:26`)
- عضو بـ`TEAM_VIEW` (owner/admin) ⇒ الاستعلام بلا تغيير (`:32-34`)
- عضو بـ`TEAM_VIEW_OWN` (manager) ⇒ `assigned_to_membership_id IN (فريقه + هو)` + تقييد المنتجات إن وُجد (`:36-49`)
- غير ذلك (staff) ⇒ `assigned_to_membership_id = $membership->id` (`:51`)

⇒ عمليًا: **مطابقة النطاق على مستوى صف واحد = `Order::query()->where('store_id', …)->whereKey($id)->visibleTo($membership)->exists()`** (أو `!== ` على `Order::find($id)`). هذه هي الصورة الوحيدة المعتمدة في المرحلة 36.12.

---

### 1. جرد وتصنيف كامل لمواضع `ORDER_MANAGE` داخل `orders/index.blade.php` — 51 موضعًا، 51 مُصنَّفة

> **قاعدة honesty المُطبَّقة:** كل موضع في الجدول التالي يقع في **سلة واحدة بالضبط**، والمجموع = 51 = عدد الأسطر الفعلية. لا موضع غير مصنَّف، ولا موضع مصنَّف مرتين.

#### 1.1 — خلاصة السلال (والتصحيح الصريح للتوقّع)

| السلة | العدد | النسبة | مرشَّحة لـ |
|-------|------:|-------:|-----------|
| **EDIT-GEOGRAPHY** | 15 | 29% | `order.edit.geography` |
| **ADMIN-OTHER** | 15 | 29% | 14 تبقى على `order.manage` (بلا تغيير) + `:1886` تنتقل إلى `order.dispatch.carrier` (D2) |
| **EDIT-PRODUCTS** | 10 | 20% | `order.edit.products` |
| **EDIT-IDENTITY** | 9 | 18% | `order.edit.identity` |
| **STATUS** | **2** | 4% | `order.status.manage.own` |
| **المجموع** | **51** | 100% | |

**تصحيح التوقّع الوارد في تخطيط الجلسة:** التوقّع كان أن ADMIN-OTHER «يبقى الأغلبية». **القياس الفعلي ينفي ذلك**: تحرير الحقول (34 موضعًا = 67%) هو الأغلبية، وADMIN-OTHER 29% فقط. سبب ذلك أن هذا الملف تفاعلي بالأساس (محرّر جدول بـ26 محرّرًا مستقلًا) وليس لوحة إجراءات. القسمة هذه هي التي تجعل المرحلة 36.12 مجدية فعلًا: 26 محرّرًا يمكن تضييقها بدقة إلى ثلاث صلاحيات بدل `order.manage` واحدة.

**وقابل STATUS الصادم:** موضعان فقط في خانة التصنيف (4%): `:2177` (`$markOrderDuplicate`) و`:2233` (`$submitBulkStatus`). والسبب أن مسار تغيير الحالة الأساسي **لا يستخدم `ORDER_MANAGE` حرفًا** — انظر 1.4. هذا يعني أن `order.status.manage.own` يجب أن يتداخل مع غلاف `StoreOrderPermissions` (خارج هذا الملف)، لا مع أي سطر `canStore(...)` داخله.

> **توضيح العدد (يمنع الالتباس مع §6.1):** الرقم **2** في خانة STATUS أعلاه هو عددُ **النفّاذات التي تُنهي انتقالًا**، لأن `:2233`Bulk ينفّذ فعليًا. أمّا `:2202` (`$openBulkStatusModal`) فهو **فتّاحة نافذة فقط** لا تنفّذ شيئًا، فصُنِّف ADMIN-OTHER في الجدول 1.2 (صف 9) بحكم 1.3-و. لكن لأن زر النافذة يتبع `:2233` تبعًا إجباريًا (لو عُطّلت النافذة بقي `:2233` بلا مدخل)، فإن §6.1 يعدّ **3** مواضع: منفّذَين + تويمة العرض. ⇒ **لا تعارض: 2 في خانة التصنيف (وظيفي)، 3 في قائمة التنفيذ (تشمل التويمة).** والرقمان صحيحان في سياقيهما.

#### 1.2 — جدول التصنيف الكامل (51/51)

| # | file:line | الصيغة | العنصر / الإجراء | السلة | الصلاحية المقترحة (OR مع `order.manage`) |
|--:|-----------|------|------------------|------|------------------------------------------|
| 1 | `orders/index.blade.php:313` | `canStore` | `$openItemsModal` — نافذة تعديل أصناف/كميات/أسعار | EDIT-PRODUCTS | `order.edit.products` |
| 2 | `:369` | `canStore` | `$addInlineItem` — إضافة صنف داخل النافذة | EDIT-PRODUCTS | `order.edit.products` |
| 3 | `:377` | `canStore` | `$saveOrderItems` — حفظ أصناف/كميات/أسعار الطلبية | EDIT-PRODUCTS | `order.edit.products` |
| 4 | `:1207` | `canStore` | `$openBulkSendModal` — فتح نافذة «إرسال جماعي للناقل» | ADMIN-OTHER | — (يبقى) |
| 5 | `:1309` | `canStore` | `$confirmBulkSend` — تنفيذ الإرسال الجماعي فعليًا | ADMIN-OTHER | — (يبقى) |
| 6 | `:1886` | `canStore` | `$submitConfirmAndSend` — تأكيد **+** إرسال للناقل في خطوة واحدة | ADMIN-OTHER ⚠️ | **`order.dispatch.carrier`** مع `order.confirm` (شرط مزدوج) — انظر 1.3-أ و§3.6 |
| 7 | `:2014` | `canStore` | `$sendConfirmedOrder` — تسليم طلبية مؤكَّدة بالفعل للناقل | ADMIN-OTHER | **`order.dispatch.carrier`** (OR مع `order.manage`) — §3.6 |
| 8 | `:2177` | `canStore` | `$markOrderDuplicate` — انتقال الحالة إلى `duplicate` | **STATUS** | `order.status.manage.own` |
| 9 | `:2202` | `canStore` | `$openBulkStatusModal` — فتح نافذة تغيير الحالة الجماعي | ADMIN-OTHER | — (يبقى) |
| 10 | `:2233` | `canStore` | `$submitBulkStatus` — **تنفيذ** انتقال الحالة على مجموعة طلبيات | **STATUS** ⚠️ | `order.status.manage.own` + تصفية لكل-طلبية (انظر 1.3) |
| 11 | `:2285` | `canStore` | `$openReassignModal` — فتح نافذة إعادة الإسناد بين أعضاء الفريق | ADMIN-OTHER ⚠️ | — (يبقى) — انظر 3.4 |
| 12 | `:2298` | `canStore` | `$submitReassign` — تنفيذ إعادة الإسناد بين أعضاء الفريق | ADMIN-OTHER ⚠️ | — (يبقى) — انظر 3.4 |
| 13 | `:3044` | `canStore` | `$openDeliveryModal` — نافذة التوصيل السريعة (نقل/مكتب/ولاية/بلدية) | EDIT-GEOGRAPHY | `order.edit.geography` |
| 14 | `:3224` | `canStore` | `$startOrderPhoneEdit` — بدء تحرير الهاتف | EDIT-IDENTITY | `order.edit.identity` |
| 15 | `:3248` | `canStore` | `$saveOrderPhone` — حفظ الهاتف + الهاتف الثانوي | EDIT-IDENTITY | `order.edit.identity` |
| 16 | `:3301` | `canStore` | `$startOrderNameEdit` — بدء تحرير اسم العميل | EDIT-IDENTITY | `order.edit.identity` |
| 17 | `:3323` | `canStore` | `$saveOrderName` — حفظ اسم العميل | EDIT-IDENTITY | `order.edit.identity` |
| 18 | `:3410` | `canStore` | `$startOrderWilayaEdit` — بدء تحرير الولاية | EDIT-GEOGRAPHY | `order.edit.geography` |
| 19 | `:3419` | `canStore` | `$startOrderCityEdit` — بدء تحرير البلدية (مع نطاق المكاتب) | EDIT-GEOGRAPHY | `order.edit.geography` |
| 20 | `:3467` | `'permission' =>` | `$saveOrderWilaya` — حفظ الولاية | EDIT-GEOGRAPHY | `order.edit.geography` |
| 21 | `:3503` | `'permission' =>` | `$saveOrderCity` — حفظ البلدية | EDIT-GEOGRAPHY | `order.edit.geography` |
| 22 | `:3579` | `canStore` | `$startOrderProviderEdit` — بدء محرّر الناقل/الراكب | EDIT-GEOGRAPHY ⚠️ | `order.edit.geography` — انظر 1.3 |
| 23 | `:3624` | `'permission' =>` | `$saveOrderProvider` — حفظ الناقل **أو** الراكب | EDIT-GEOGRAPHY ⚠️ | `order.edit.geography` — انظر 1.3 |
| 24 | `:3700` | `canStore` | `$startOrderDeliveryTypeEdit` — بدء تحرير نوع التوصيل | EDIT-GEOGRAPHY | `order.edit.geography` |
| 25 | `:3725` | `'permission' =>` | `$saveOrderDeliveryType` — حفظ نوع التوصيل | EDIT-GEOGRAPHY | `order.edit.geography` |
| 26 | `:3748` | `canStore` | `$startOrderShipmentTypeEdit` — بدء تحرير نوع الشحن | EDIT-PRODUCTS | `order.edit.products` |
| 27 | `:3776` | `'permission' =>` | `$saveOrderShipmentType` — حفظ نوع الشحن | EDIT-PRODUCTS | `order.edit.products` |
| 28 | `:3788` | `canStore` | `$startOrderStopdeskEdit` — بدء تحرير مكتب التسليم | EDIT-GEOGRAPHY | `order.edit.geography` |
| 29 | `:3817` | `'permission' =>` | `$saveOrderStopdesk` — حفظ مكتب التسليم | EDIT-GEOGRAPHY | `order.edit.geography` |
| 30 | `:3850` | `canStore` | `$startOrderAgentEdit` — بدء تحرير الوكيل المسؤول | ADMIN-OTHER ⚠️ | — (يبقى) — انظر 3.4 |
| 31 | `:3863` | `canStore` | `$saveOrderAgent` — حفظ الوكيل المسؤول | ADMIN-OTHER ⚠️ | — (يبقى) — انظر 3.4 |
| 32 | `:3886` | `'permission' =>` | `$saveOrderAgent` — بوابة `saveEdit` (تستدعي `OrderAssignmentService::reassign`) | ADMIN-OTHER ⚠️ | — (يبقى) — انظر 3.4 |
| 33 | `:3941` | `canStore` | `$startOrderAddressEdit` — بدء تحرير العنوان | EDIT-GEOGRAPHY | `order.edit.geography` |
| 34 | `:3962` | `'permission' =>` | `$saveOrderAddress` — حفظ العنوان | EDIT-GEOGRAPHY | `order.edit.geography` |
| 35 | `:3981` | `canStore` | `$startOrderWeightEdit` — بدء تحرير الوزن | EDIT-PRODUCTS | `order.edit.products` |
| 36 | `:4008` | `'permission' =>` | `$saveOrderWeight` — حفظ الوزن (مع حدّ الناقل) | EDIT-PRODUCTS | `order.edit.products` |
| 37 | `:4020` | `canStore` | `$startOrderNotesEdit` — بدء تحرير الملاحظات | EDIT-IDENTITY | `order.edit.identity` |
| 38 | `:4045` | `'permission' =>` | `$saveOrderNotes` — حفظ الملاحظات | EDIT-IDENTITY | `order.edit.identity` |
| 39 | `:4057` | `canStore` | `$startOrderDiscountEdit` — بدء تحرير الخصم | EDIT-PRODUCTS | `order.edit.products` |
| 40 | `:4088` | `'permission' =>` | `$saveOrderDiscount` — حفظ الخصم (مالي) | EDIT-PRODUCTS | `order.edit.products` |
| 41 | `:4126` | `canStore` | `$toggleSendFromWarehouse` — علم «الشحن من مستودع الناقل» | EDIT-GEOGRAPHY | `order.edit.geography` |
| 42 | `:4192` | `canStore` | `$openCreateModal` — فتح نافذة إنشاء طلبية | ADMIN-OTHER | — (يبقى) |
| 43 | `:4238` | `canStore` | `$submitCreate` — إنشاء طلبية يدويًا (سلّم+مخزون) | ADMIN-OTHER | — (يبقى) |
| 44 | `:4391` | `canStore` | `$openEditModal` — نافذة التحرير الكاملة | ADMIN-OTHER | — (يبقى) — غير قابلة للتجزئة، انظر 1.3 |
| 45 | `:4476` | `canStore` | `$submitEdit` — حفظ النافذة الكاملة | ADMIN-OTHER | — (يبقى) — غير قابلة للتجزئة، انظر 1.3 |
| 46 | `:4698` | `@if` | زر «طلبية جديدة» في رأس الصفحة | ADMIN-OTHER | — (يبقى) |
| 47 | `:5264` | `@elseif` | بطاقة الجوال: زر تحرير الاسم | EDIT-IDENTITY | `order.edit.identity` |
| 48 | `:5311` | `@elseif` | بطاقة الجوال: زر تحرير الهاتف | EDIT-IDENTITY | `order.edit.identity` |
| 49 | `:5389` | `@elseif` | بطاقة الجوال: زر تحرير الملاحظات | EDIT-IDENTITY | `order.edit.identity` |
| 50 | `:5445` | `@elseif` | بطاقة الجوال: زر تحرير الولاية | EDIT-GEOGRAPHY | `order.edit.geography` |
| 51 | `:5494` | `@if` | بطاقة الجوال: قائمة «تعديل الأصناف» | EDIT-PRODUCTS | `order.edit.products` |

**تحقّق الاكتمال:** 51 سطرًا مطابقًا لناتج البحث الحيّ بالضبط، وكل سطر في عنصر واحد لا في اثنين. لا تكرار ولا نقصان.

**دور `saveEdit`'s `'permission' =>`:** المواضع 20, 21, 23, 25, 27, 29, 32, 34, 36, 38, 40 (11 موضعًا) ليست حارسًا مستقلًا — هي **بوابة الحفظ** التي يمرّرها `$this->saveEdit([...])` إلى `canStore($this->...)`. لذلك **كل** سطر من محرّري `start*` نظيره إلزامي في `save*`: لو أضيفت الصلاحية الجديدة إلى `start*` فقط، ظهر الزر للعضو الجديد ثم رُفض الحفظ. وكل مِحرِّر `start*` له نظير `save*`، و`writeInlineAudit` يمرّر نفس بوابة `permission`. هذا يفرض **ازواجًا إجباريًا** بين `start*` و`save*`.

#### 1.3 — الأحكام التحليلية (نقاط لا يمكن تجزئتها — تُوثَّق صراحةً)

| ⚠️ | الموضع | الحكم |
|---|---------|-------|
| **أ** ⛔ | `:1886` `$submitConfirmAndSend` | **مُلغى ومُستبدل بالقرار D2 (§3.6).** كان التصنيف ADMIN-OTHER صحيحًا: الإجراء المهيمن هو **الإرسال للناقل** لا تغيير حالة، ونصفه التأكيدي متاح أصلًا تحت `order.confirm` عبر `$submitConfirmOnly` (`:1840-1883`، حارسه `ORDER_CONFIRM` في `:1841`). لكن **البند 2 كان مغطيًا لرخصة `status.manage.own` دون أي غطاء للإرسال**، فلم يكن في الخطة أي رخصة تُجيز الإرسال لمن لا يملك `order.manage` — ثغرة تصميمية لا مجرد تصنيف. **الحسم النهائي:** لا علاقة لهذا الإجراء بـ`status.manage.own` إطلاقًا، ويأخذ الشرط المزدوج `(ORDER_MANAGE \|\| ORDER_CONFIRM) && (ORDER_MANAGE \|\| ORDER_DISPATCH_CARRIER)` معًا في الحارس (`:1886`) وزر العرض (`:127-128`) في نفس الـ commit. |
| **ب** | `:2233` `$submitBulkStatus` | **STATUS رغم أنه جماعي.** يفرض التطبيق قاعدتين: (1) `order.status.manage.own` لا يُمنح إلا لأعضاء مرئيين، فالجملة `canStore(ORDER_STATUS_MANAGE_OWN)` وحدها تفتح نافذة Changes لمجموعة طلبيات خارج نطاق العضو؛ (2) الحل الصحيح: **تصفية على مستوى كل طلبية** داخل حلقة `each` الموجودة أصلًا في `:2259-2271` — تخطَّ أي طلبية لا يمر `visibleTo($membership)` واحسبها `skipped++` بدل `$done++`. أي: `done` يجب أن يبقى **مطابقًا للنطاق** لا للعدد الخام. |
| **ج** | `:3579` + `:3624` | **تحرير الناقل هو أيضًا إسناد راكب.** `$saveOrderProvider` يكتب `delivery_rider_id` فعليًا في فرع `$isRider` (`:3646-3657`). أي أن هذا المحرّر — وهو في سلة `geography` — **يدمج فعلَين**: تبديل شركة التوصيل (جغرافيا) وإسناد الراكب (dispatch). القرار: يبقى **كاملًا** على `order.edit.geography`، لأن `order.edit.geography` تغطي أصلًا `shipping_provider`؛ و**لا** يُضاف إليه شرط `order.dispatch.rider` في هذه المرحلة (قاعدة «لا تشديد» — انظر 5). إن أراد المالك لاحقًا فصلًا حقيقيًا، فذلك قرار منفصل يتطلّب منفذَي كتابة. **موثَّق كحدّ معروف لا كحالة نسيان.** |
| **د** | `:4391` + `:4476` | **النافذة الكاملة غير قابلة للتجزئة.** حقل واحد في `:4401-4434` يجمع `customer_name` + `customer_phone` + `notes` (هوية) + `weight_kg` + `discount` + `shipment_type` + `items` (منتجات) + `address` + `state_id` + `city_id` + `delivery_type` + الناقل/الراكب/المكتب (جغرافيا). أي تفصيل هنا = 7 تحويلات في مسار واحد. **تُبقي كاملةً على `order.manage`.** هذا يعني أن عضوًا بـ`order.edit.identity` فقط **لن يستطيع** استخدام نافذة التحرير الكاملة — سلوك مقصود وموثَّق (يُستخدم المحرّرات المستقلة بدلًا منها). |
| **هـ** | `:3044` | `$openDeliveryModal` يفتح نافذة التوصيل فقط (لا هوية ولا منتجات) ⇒ قابل للتجزئة إلى `order.edit.geography` بأمان. |
| **و** | `:2202` | فتّاحة النافذة فقط؛ **تأثيره على الواجهة فقط**. إن أُضيفت الصلاحية إلى `:2233` وحدها، تبقى النافذة مغلقة. يجب إضافتها إلى **الاثنين** `:2202` و`:2233` معًا. |

#### 1.4 — فجوة STATUS الحقيقية: `transitionOrder` لا يستخدم `ORDER_MANAGE` إطلاقًا

| file:line | الشاهد |
|-----------|--------|
| `orders/index.blade.php:1574-1602` | `$transitionOrder` — **المسار الوحيد** الذي ينقل الحالة من قائمة الحالة (قالب الجوال `:5354`) |
| `orders/index.blade.php:1578` | `if (! canStore(\App\Support\StoreOrderPermissions::forStatus($statusKey, (string) currentStoreId()))) {` — **لا `ORDER_MANAGE` مكتوب هنا** |
| `orders/index.blade.php:1569-1573` | تعليق Phase P1 يشرح: التأكيد → `order.confirm`، الإلغاء → `order.cancel`، **«everything else … → order.manage»** |
| `app/Support/StoreOrderPermissions.php:47-62` | `forStatus()` — `:53-55` تأكيد، `:57-59`إلغاء، **`:61` fallback = `ORDER_MANAGE->value`** |
| `app/Support/StoreOrderPermissions.php:21-25` | `CONFIRM_STATUSES = ['confirmed','preparing','on_hold']` |
| `app/Support/StoreOrderPermissions.php:30-41` | `CANCEL_STATUSES = ['cancelled','canceled','rejected','no_answer_1..3','wrong_number','out_of_stock','duplicate','postponed']` |

**الاستنتاج التنفيذي:** الحالات التشغيلية الحقيقية (shipped / delivering / delivered / returned / follow-up …) تمرّ عبر `forStatus()` → `order.manage` **دون أي سطر في الـ blade**. لذلك:

> **`order.status.manage.own` يجب أن يُنفَّذ داخل `StoreOrderPermissions::forStatus()` (أو في غلاف حوله) — لا داخل `orders/index.blade.php` إطلاقًا.**

وهذا يعني أن المرحلة 36.12 **تتجاوز** نطاق `orders/index.blade.php` إلى `app/Support/StoreOrderPermissions.php`. ومنطق التنفيذ المقترح: يُرجع `forStatus()` قائمة/منطوق بدل نص واحد، أو يُضاف غلاف `canTransitionStatus($orderId, $statusKey, $membership)` في `StoreOrderPermissions` يعيد:

```php
canStore(StoreOrderPermissions::forStatus($statusKey, $storeId))
|| (canStore(StorePermissionEnum::ORDER_STATUS_MANAGE_OWN->value)
    && $membership !== null
    && Order::where('store_id', $storeId)->whereKey($orderId)->visibleTo($membership)->exists())
```

ومن ثم **يُستبدل** الاستدعاء في `:1578` بالغلاف الجديد.

> **القرار D1 (مُثبَّت، يُلغي التقييد السابق):** فرع الـ OR في `canTransitionStatus()` يغطي **كل** مفاتيح الحالات بلا استثناء — مجموعة التأكيد (`confirmed`/`preparing`/`on_hold`)، ومجموعة الإلغاء والنتائج الميدانية (`cancelled`/`canceled`/`rejected`/`no_answer_1..3`/`wrong_number`/`out_of_stock`/`duplicate`/`postponed`)، وحالات الـ fallback في `:61` (`shipped`/`delivering`/`delivered`/… ). **الشرط الوحيد** أن تكون الطلبية داخل نطاق `visibleTo($membership)` للعضو.

**سبب توحيد النطاق (D1):** تقييد النفوذ على `:61` وحده كان سيُنتج صلاحية **ناقصة وغامدة**: عضو يحمل `status.manage.own` يستطيع أن يُبحر طلبية من `preparing` إلى `shipped`، لكنه **لا يستطيع** أن يؤكّدها (`order.confirm`) ولا أن يسجّل `no_answer_1` (`order.cancel`). أي أن الرخصة تسمح بالحركة داخل المسار وتُقفل عند بدايته ونهايته — وهذا عكس المقصود. متطلب المالك صريح: **منحة واحدة ضيّقة تُدير حالة الطلبيات في نطاق العضو، شاملةً التأكيد ونتائج الاتصال.** ومندوب المتجر الذي لا يملك `order.manage` يحتاج بالضبط `no_answer_1` و`postponed` — وهذه حالة عمل يومية: مكالمة لم تُردّ، أو عميل يؤجّل. من حرمها منها تبقي `order.manage` الواسعة هي الخيار الوحيد، فيفقد الفصل التام معناه.

**الأثر على الدوال القائمة (لا تغيّر سلوك أحد):** `forStatus()` نفسها **تبقى كما هي** (`:47-62`) لأنها تخدم `order.confirm`/`order.cancel` لحامليهما كما هي. `canTransitionStatus()` غلاف **جديد** يغلّفها ولا يستبدلها. ولأن التفريع يبدأ بـ`canStore(forStatus(...))` أولًا، فإن من يملك `order.confirm` أو `order.cancel` أو `order.manage` يطابق سلوكه بايتًا ببايت. والفرع الثاني لا يُضاف إلا لـ`ORDER_STATUS_MANAGE_OWN` وحدها. ⇒ **لا تشديد على أحد.**

#### 1.5 — التوائم الإلزامية خارج `orders/index.blade.php` (45 موضعًا — خارج نطاق البند 1 لكن شرط لصحة التنفيذ)

> **القاعدة:** كل سطر في الجدول 1.2 يُنتج **زرًّا** في العرض. مواقع العرض ليست في نفس الملف. **إن أُضيفت الصلاحية في `index.blade.php` ولم تُضَف في العرض، سيظهر الزر ثم يرفض الخادم الحفظ** (أو العكس: زر مخفي مع حق فعلي). القسم 6 يفرض تعديل التوائم في نفس الـ commit.

| الملف | العدد | المواضع |
|-------|------:|---------|
| `orders/partials/orders-table-cell.blade.php` | 18 | `:65` customer · `:109` customer (شارة الحقول الناقصة) · `:147` phone · `:198` notes · `:256` wilaya · `:297` products · `:319` quantity · `:345` price · `:414` discount · `:475` weight · `:507` shipment_type · `:545` city · `:589` address · `:622` delivery_type · `:651` shipping_provider · `:704` stopdesk_point · `:735` send_from_carrier_warehouse · `:846` assigned_agent |
| `orders/partials/orders-table-actions-column.blade.php` | 3 | `:61` · `:76` · `:93` |
| `orders/partials/orders-mobile-fields.blade.php` | 1 | `:18` (`$canManage`) |
| `orders/partials/bulk-actions-bar.blade.php` | 1 | `:79` |
| `orders/partials/confirm-drawer.blade.php` | 1 | `:127` |
| `order-settings.blade.php` | 6 | `:50 :90 :167 :212 :301 :348` (كلها `abort_unless` ⇒ إعدادات الطلبية) |
| `order-distribution-queue.blade.php` | 3 | `:47 :72 :94` |
| `tracking/index.blade.php` | 2 | `:244` (تعليق) · `:246` (حارس `CRM_ORDER_TRACKING \|\| ORDER_MANAGE`) |
| `app/Livewire/Concerns/TrackingDrawerConcern.php` | 2 | `:364` · `:571` |
| `app/Livewire/Concerns/TrackingRiderFormConcern.php` | 2 | `:754` (`openEditModal`) · `:829` (`submitEdit`) |
| `app/Livewire/Concerns/CancelsShipmentFromOrdersTable.php` | 1 | `:12` |
| `app/Policies/OrderPolicy.php` | 1 | `:43` (`hasPermission`) |
| `tracking/partials/tracking-row-cell.blade.php` | 1 | `:91` |
| `tracking/partials/tracking-mobile-card.blade.php` | 1 | `:107` |
| `tracking/partials/carrier-note-composer.blade.php` | 1 | `:4` |
| `layout/store-sidebar.blade.php` | 1 | `:64` (`canViewQueue`) |
| **المجموع** | **45** | 51 + 45 = **96** ✓ مطابق لعدّاد 0.1 |

---

### 2. تعريفات الصلاحيات الجديدة — **ستّ** لا أربع (العدّاد مُصحَّح)

> **تصحيح العدّ:** نص التخطيط يذكر «الأربع الجديدة» وتعدادها فعليًا **ستّ**: واحدة للحالة + ثلاث للتحرير + واحدة لتوزيع الراكبين (البند 3.1) + **واحدة لتسليم الطلبية لشركة الشحن** (القرار D2، البند 3.6). الجدول التالي يعرّف الستّ. كلها في نفس بلوك الـ Enum ⇒ `group()` = **`order`** للجميع (`StorePermissionEnum.php:131-134`: `explode('.')[0]`)، فتظهر كلها في مجموعة `order` واحدة داخل الـ Hub بلا أي تعديل في `GROUP_ORDER`/`GROUP_ICON`.

#### 2.1 — الجدول التعريفي

| # | حالة الـ Enum | القيمة | `group()` | نوع النطاق | يتركّب مع `visibleTo()`؟ | الأسلوب الوظيفي |
|--:|---------------|--------|-----------|-----------|-----------------------|------------------|
| 1 | `ORDER_STATUS_MANAGE_OWN` | `order.status.manage.own` | `order` | **own-scoped** | **نعم — إلزامي** | غلاف `canTransitionStatus()` لكل مفاتيح الحالات (D1) + `markOrderDuplicate` + `submitBulkStatus` |
| 2 | `ORDER_EDIT_IDENTITY` | `order.edit.identity` | `order` | **store-wide** | لا | 9 مواقع (الجدول 1.2) |
| 3 | `ORDER_EDIT_PRODUCTS` | `order.edit.products` | `order` | **store-wide** | لا | 10 مواقع |
| 4 | `ORDER_EDIT_GEOGRAPHY` | `order.edit.geography` | `order` | **store-wide** | لا | 15 موقعًا |
| 5 | `ORDER_DISPATCH_RIDER` | `order.dispatch.rider` | `order` | **store-wide** | لا | موقعان من `ORDER_ASSIGN` (البند 3) |
| 6 | `ORDER_DISPATCH_CARRIER` | `order.dispatch.carrier` | `order` | **store-wide** | لا | 4 مواضع إرسال: `:1886`، `:1309`، `:2014` + 3 أزرار عرض (البند 3.6) |

#### 2.2 — قرار النطاق: `status.manage.own` مقيَّدة بالعضو، وبقية الرخص على مستوى المتجر (قرار مُثبَّت، ليس سؤالًا مفتوحًا)

**القرار:** `order.status.manage.own` = **own-scoped** (مقيَّد بنطاق `visibleTo()`). الثلاث `order.edit.*` + `order.dispatch.rider` + `order.dispatch.carrier` = **store-wide** على مستوى المتجر.

**لماذا `dispatch.carrier` store-wide (D2):** التسليم لشركة الشحن **إجراء خارجي لا راجع له** — ينقل أصلًا خارج النظام إلى مشغّل خارجي، وينشئ عنده سجلَّ تتبّع. أي أن النطاق الجزئي هنا لا يحدّ من ضرر (لا يوجد ضرر) ولا يحمي خصوصية (الطلبية في النطاق أصلًا)، بينما `order.status.manage.own` يحدّ من **ترحيل حالة** قد يغيّر ما يراه زميل آخر في مسار عمل مشترك. الفصل المعقول: **من يملك صلاحية شركة الشحن** يُمنح إياها على مستوى المتجر كله، مع إبقاء `visibleTo` في طبقة *الاستعلام* (الانتقال يبقى مقيَّدًا بالحالة كما هو اليوم). ولا تعارض: `dispatch.carrier` لا يمنح رؤية على الطلبيات إطلاقًا.

**التبرير — لماذا النطاق مقيَّد بالعضو:**
الحالة هي **نقل حالة تشغيلية عبر جدار التوصيل** (shipped/delivering/delivered/return). هيكل الرؤية (36.4) مبنيّ على «المالك/الأدمن يرى الكل، المدير يرى فريقه، الموظف يرى طلبياته». البوابة `ORDER_MANAGE` اليوم تُبقي كل مسارات التتبع **مفتوحة على مستوى المتجر كله**، وهو بالضبط ما يجعل `order.manage` خطيرًا إداريًا. منح عضو «يتقدّم بطلباتي» يجب ألّا يمنحه ضمنًا «يَرسل طلبيات زميلٍ آخر إلى الناقل». ⇒ `own` هو المعنى الحقيقي للاسم، **وعدم** إخفائه خلف `order.manage` يُفرغ الغرض منه.

**التبرير — لماذا النطاق على مستوى المتجر:**
حرية الحقول **لا تُنتج أثرًا خارجيًا** في حد ذاتها: تعديل اسم عميل أو وزن أو عنوان لا ينقل مالًا ولا يغيّر من يقرأ الطلبية. نطاقاتُ المستوى الجزئي (مثلًا «عدّل الطلبات التي تراها فقط») تُنتج **رفيقًا مزعجًا** في هذا الملف تحديدًا، لأن: (1) محرّرات الجدول مربوطة بالصفوف لا بالاستعلام، (2) `$openEditModal`/`:4391` غير قابلة للتجزئة (حكم 1.3-د)، (3) `order.edit.price` القائم — المرجع التأسيسي — **store-wide أصلًا** وبلا `visibleTo` (يُتحقَّق منه عبر `$itemsPriceEditable()` في `:299-310`، وهو `ORDER_EDIT_PRICE` + `store.settings.allow_price_edit` فقط)، و(4) غياب الاتساق أسوأ من التساهل: ثلاثة تصاريح متفاوتة النطاق على نفس الجدول تُربك المالك في الـ Hub.
**الاستثناء المسجَّل:** إن أضاف مالك يومًا نطاقًا جزئيًا لحرية الحقول، فذلك **المرحلة 36.13+**، ويُتعارض مع `:4391` ويُحسم في قرار منفصل.

**أثر التسعير — نقطة لا تُنسى:** `order.edit.products` **لا تلغي** `order.edit.price`. حقل `price` يبقى بوابته مزدوجة: `order.edit.products || order.manage` **و** `itemsPriceEditable()` (= owner أو (`allow_price_edit` + `order.edit.price`)). أي لمنح `order.edit.products` لأحد ما زال يحتاج `order.edit.price` ليغيّر السعر فعليًا. وهذا مقصود: تحرير المنتج ليس تحريك المال.

#### 2.3 — الربط اللغوي والـ Hub (يُنفَّذ في 36.12، موثَّق هنا)

| الملف | التغيير | التفصيل |
|-------|---------|---------|
| `resources/lang/{ar,en,fr,es}/permissions.php` | **إلزامي ×4** | `'order' => [` يضاف: `'status' => ['manage' => ['own' => '…']]`، `'identity'/'products'/'geography'` داخل `'edit'` القائم، `'dispatch' => ['rider' => '…', 'carrier' => '…']` |
| `resources/lang/{ar,en,fr,es}/permissions_descriptions.php` | **إلزامي ×4** | انظر 2.4 — بنية التداخل حاسمة |
| `app/Support/PermissionGroupMeta.php:90-117` (`DEPENDENCIES`) | **إلزامي** | الستّ ← `['order.view']`، بنفس نمط `order.edit.price` (`:101`) و`order.assign` (`:100`) |
| `app/Enums/Store/StorePermissionEnum.php:40-48` | إلزامي | إضافة الحالات الستّ داخل بلوك Orders |
| `app/Support/StoreRoles.php` | **لا شيء** | انظر البند 7 |

**فخّ التداخل الذي يجب تحنّبه (تحقّق حيًّا):** `PermissionGroupMeta::label()` (`:180-194`) تفكّ المصفوفات المتداخلة عبر `['label'] ?? ['own'] ?? reset()`. و`description()` (`:144-150`) تقرأ `permissions_descriptions.{$permission}` حرفيًّا. النتيجة:
- `label('order.manage')` سيبقى **نصًّا** بعد إضافة `'status' => ['manage' => …]`، لأن `'manage'` و`'status'` **مفاتيح شقيقة** داخل `'order'` لا متداخلان. ✔
- `label('order.edit.price')` يبقى يعمل مع إضافة `identity/products/geography` كإخوة. ✔
- `label('order.dispatch.rider')` يبقى نصًّا بعد إضافة `carrier` كشقيقة داخل `'dispatch'`. ✔
- **لا** يوجد أي كود في `app/` أو `resources/` يقرأ مفاتيح على مستوى المجموعة (`permissions.order.edit` / `.status` / `.dispatch`) — بحث حيًّا = **صفر نتيجة**. ✔ فلا انحدار متوقّع.
- قيم مثل `order.edit.price` لا وجود لقيمة `order.edit` ⇒ لن يُستدعى `label()` بمفتاح يُرجع مصفوفة لهذه العائلة. نفس الأمر لـ`order.status` و`order.dispatch`. ✔

#### 2.4 — نصوص الأوصاف المقترحة (تُعتمد في 36.12 ×4 لغات)

| المفتاح | الوصف (en — يُترجم لـar/fr/es) |
|--------|--------------------------------|
| `order.manage` (قائم، **يُوسَّع**) | «Move any order through the full workflow — prepare, ship, deliver and return — and edit any field on any order, plus bulk actions, the order settings panel and the distribution queue. Store-wide: it covers every order in the store. For a narrower grant see order.status.manage.own, the order.edit.* permissions and the order.dispatch.* permissions.» |
| `order.status.manage.own` | «Move the orders **you can see** through the full workflow without store-wide order management. Covers **every** status transition on those orders — not only the shipping ones: confirming a new order, call outcomes (no answer, wrong number, out of stock), postponing, duplicating, cancelling, as well as preparing, shipping, delivering and returning. Strictly limited to your visibility scope: your own assigned orders, plus your supervised staff's if you supervise anyone. This is the narrow alternative to order.manage for staff who handle their own order flow end to end.» |
| `order.edit.identity` | «Edit customer-identifying details on any order — customer name, phone numbers and internal notes. Store-wide: it covers every order in the store, not only the ones in your own visibility scope. This is the narrow alternative to order.manage for identity corrections.» |
| `order.edit.products` | «Edit an order's products, quantities, weight, shipment type and discount on any order. Store-wide: it covers every order in the store. **Price is still governed separately by order.edit.price plus the store's allow_price_edit setting** — this permission does not by itself allow changing prices.» |
| `order.edit.geography` | «Edit an order's delivery destination and carrier on any order — wilaya, commune, address, delivery type, shipping company, delivery rider, office and the ship-from-carrier-warehouse flag. Store-wide: it covers every order in the store. This is the narrow alternative to order.manage for fixing delivery details.» |
| `order.dispatch.rider` | «Hand an order over to a delivery rider, or take it back from one, so the rider collects and delivers it. **Distinct from order.assign, which moves an order between your own team members** — grant this one when a member should place parcels with riders but not reassign work inside the team.» |
| `order.dispatch.carrier` | «Hand an order over to a shipping company so the carrier collects and tracks it. This is the action that actually **sends** the order to the carrier's API. **Distinct from order.dispatch_validate, which only checks the shipment against the carrier's handover record and sends nothing**, and from order.dispatch.rider, which hands the parcel to a delivery rider rather than a company. Grant this one when a member should dispatch to carriers but not hold full order.manage.» |
| `order.dispatch_validate` (قائم، **جديد**) | «Check a shipment against the carrier's handover record before or after dispatch — parcel count, weight and cash-on-delivery amount. **This does not send the order to the carrier**; sending is covered by order.dispatch.carrier or order.manage.» |

---

### 3. فصل `order.dispatch.rider` عن `order.assign`

#### 3.1 — جرد `ORDER_ASSIGN` الكامل (أُعيد عدّه حيًّا عبر 1261 ملف PHP/blade في `app`+`resources`+`routes`+`config`+`database`)

| # | file:line | النوع | الحكم |
|--:|-----------|-------|-------|
| 1 | `app/Enums/Store/StorePermissionEnum.php:46` | تعريف الحالة `case ORDER_ASSIGN = 'order.assign';` | ليس موضع فحص |
| 2 | `app/Helpers/helpers.php:192` | فحص وظيفي داخل `canReassignOrders()` (`:176-193`) | **إسناد حقيقي — يبقى** |
| 3 | `app/Livewire/Concerns/TrackingRiderFormConcern.php:29` | `abort_unless(canStore(ORDER_ASSIGN), 403)` في `assignRider()` (`:27-63`) | **توزيع راكب — ينتقل** |
| 4 | `app/Support/StoreRoles.php:57` | إدراج في قالب MANAGER | **إسناد حقيقي — يبقى** (القالب بلا تغيير؛ و`dispatch.rider` بديل إضافي في `\|\|`، انظر 3.4) |
| 5 | `resources/.../orders/index.blade.php:1177` | `canStore(ORDER_ASSIGN)` في `$bulkAssignAgent` (`:1176-1199`) | **إسناد حقيقي — يبقى** |
| 6 | `resources/.../orders/index.blade.php:3932` | **إيجابية كاذبة** — نص `'audit_event' => 'order_assigned_agent_updated'` | ليس فحص صلاحية |
| 7 | `resources/.../tracking/partials/order-drawer.blade.php:132` | `canStore(ORDER_ASSIGN) && ! $this->drawerTracking['has_provider']` — زر «إسناد/تغيير راكب» | **توزيع راكب — ينتقل** |

**⇒ 5 مواضع فحص وظيفية فقط** (2، 3، 4، 5، 7)، لا 6 كما يوحي التخطيط. **وتصحيح صريح:** لا يوجد أي فحص `ORDER_ASSIGN` في `app/Livewire/Concerns/TrackingGridConcern.php` — بحث حيّ عن `ORDER_ASSIGN` في الملف = **صفر**. تبويب «الراكب» في `TrackingGridConcern.php` مقيَّد بـ**استعلام فقط** لا بصلاحية (`:31-32` و`:141-146` و`:155` و`:263` و`:395` و`:436` — كلها `whereNotNull('delivery_rider_id')`)، فوجود تبويب الراكبين عند دور ما **ليس** موضع `ORDER_ASSIGN` أصلًا، وإضافة `dispatch.rider` لا تغيّره.

#### 3.2 — التصنيف: حقيقي-إسناد مقابل توزيع-راكب

| الفعل | المعنى | الصلاحية **بعد** الفصل |
|------|--------|------------------------|
| نقل طلبية من عضو إلى **عضو آخر في الفريق** (إسناد داخلي) | إعادة توزيع العمل | `order.assign` — **بلا تغيير** |
| تسليم طلبية إلى **راكب توصيل خارج الفريق** | لوجستيات ميدانية | `order.dispatch.rider` — **جديد**، بديل إضافي لـ`order.assign` (اتحاد `||`) |

الدافع المفاهيمي: الإسناد يغيّر **من يملك** الطلبية ويؤثر على `visibleTo()` نفسه (لأن النطاق مبني على `assigned_to_membership_id` — `HasVisibilityScope.php:39` و`:51`)؛ **بينما** توزيع الراكب يغيّر **من يسلّم** فقط ولا يمس ملكية الطلبية إطلاقًا. خلطهما في صلاحية واحدة يعني أن أي عضو يستحق «أن يضع الطرود مع الراكبين» يُمنح ضمنًا «أن يعيد توزيع الطلبية على زميله» — وهذا تسريب إداري.

#### 3.3 — الـ diff المطلوب لكل ملف (بالترتيب)

| الملف | السطر | قبل | بعد |
|------|-------|------|------|
| `app/Enums/Store/StorePermissionEnum.php` | `:48` (بعد `ORDER_ASSIGN`) | — | `case ORDER_DISPATCH_RIDER = 'order.dispatch.rider';` |
| `app/Livewire/Concerns/TrackingRiderFormConcern.php` | **`:29`** | `abort_unless(canStore(StorePermissionEnum::ORDER_ASSIGN->value), 403);` | `abort_unless(canStore(StorePermissionEnum::ORDER_DISPATCH_RIDER->value) \|\| canStore(StorePermissionEnum::ORDER_ASSIGN->value), 403);` |
| `resources/.../tracking/partials/order-drawer.blade.php` | **`:132`** | `canStore(\App\Enums\Store\StorePermissionEnum::ORDER_ASSIGN->value)` | `canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DISPATCH_RIDER->value) \|\| canStore(\App\Enums\Store\StorePermissionEnum::ORDER_ASSIGN->value)` |
| `app/Helpers/helpers.php` | `:192` | `canStore(ORDER_ASSIGN->value, $user)` | **بلا تغيير** |
| `resources/.../orders/index.blade.php` | `:1177` | `canStore(ORDER_ASSIGN->value)` | **بلا تغيير** |
| `app/Support/StoreRoles.php` | `:57` | قائمة MANAGER | **بلا تغيير إطلاقًا** (البند 7) |
| `resources/lang/{ar,en,fr,es}/permissions.php` | بلوك `'order'` | `'assign' => …` | **إضافة `'dispatch' => ['rider' => …]` فقط** — `'assign'` بلا تغيير |
| `app/Support/PermissionGroupMeta.php` | `:90-117` | `order.assign => ['order.view']` | **إضافة** `'order.dispatch.rider' => ['order.view']` |

**قابلية التطابق (اختبار القبول الإلزامي):** `order-drawer.blade.php:132` هو **العرض** و`TrackingRiderFormConcern.php:29` هو **الحارس**، ويجب أن يتغيّرا في نفس الـ commit. وطريقة الاتساق هنا **اتحاد** لا استبدال: كل سطر يقبل `ORDER_DISPATCH_RIDER` **أو** `ORDER_ASSIGN`. فعضو يملك `dispatch.rider` فقط يرى الزر ويسمح له الخادم، وعضو يملك `order.assign` فقط (القالب الحالي للمدير مثلًا) يرى الزر ويسمح له الخادم أيضًا، **وكلاهما يُرفض إذا لم يكن يملك أيًّا منهما**. الاختبار الحاسم: استدعاء `$wire.assignRider(...)` مباشرة من وحدة التحكّم بلا أيٍّ من `:29` و`:132` يتغيّرين ⇒ 403؛ وبانعكاسه، استدعاؤها بعضو يملك `order.assign` فقط ⇒ ينجح بلا 403 (اختبار عدم-التراجع).

#### 3.4 — ✅ لا تحفّظ: الاتحاد في موضعي الراكب يجعل الوعد قائمًا بلا استثناء

البند 5 يَعِد بأن **لا يُشدَّد شيء على أحد**. التحقّق من هذا الوعد في موضعي التوزيع-الراكب (`:29` و`:132`) مبدئيًا: هما يتحققان من `ORDER_ASSIGN`، **لا** من `ORDER_MANAGE`، فلا يمسّان وعد `order.manage`. لكن **المبادئ التي التزمت بها هذه المرحلة تمنع التسريب**: لا تشديد على من يملك `order.assign`، ولا تعديل على القوالب. الحل الذي يحترم الأمرين معًا هو **الاتحاد** بدل الاستبدال:

1. **ماذا يحدث بالضبط:** يبقى `ORDER_ASSIGN` بديلًا فعّالًا في `:29` و`:132`، وتُضاف `ORDER_DISPATCH_RIDER` بجانبه في `||`. فكل حامل قائم لـ`order.assign` — سواء عبر قالب MANAGER (`:57`) أو عبر pivot مخصّص — **يحتفظ بالسلوك كما هو بايتًا ببايت**، ولا يُشَدَّد عليه أحد. والعضو الذي يمنحه المالك `order.dispatch.rider` فقط يحصل على **إذن توزيع راكب جديد دون إعادة إسناد** — وهذا هو الفصل المطلوب. وبما أن OWNER = `StorePermissionEnum::values()` (`StoreRoles.php:19`) وADMIN = `values()` ناقصًا السيادة (`:26-35`)، فإن المالك والأدمن يأخذان الحالة الجديدة تلقائيًا بلا سطر. **النتيجة: صفر تغيير سلوكي لأي عضو قائم، وصفر تعديل على `StoreRoles.php` (البند 7 محترم حرفيًا).**

2. **شذوذ لم يُطلب إصلاحه — لا يُصلَح في 36.12.** خمسة مواضع في `orders/index.blade.php` تفعل **إسنادًا حقيقيًا لفريق** لكنها محجوبة بـ`ORDER_MANAGE` بدل `ORDER_ASSIGN`: `:2285`، `:2298` (نافذة إعادة الإسناد)، `:3850`، `:3863`، `:3886` (محرّر الوكيل → `OrderAssignmentService::reassign`). تصحيحها **سيُشدِّد** على من يملك `order.manage` بلا `order.assign` — أي أنه يناقض صراحةً قسمي 5. **القرار: لا تغيير في 36.12**؛ يُسجَّل كبند دَين وظيفي (deferred) يُفحص في مرحلة منفصلة. نفس المعالجة للسطور `TrackingDrawerConcern.php:364`/`:571` و`TrackingRiderFormConcern.php:754`/`:829`.

---

#### 3.6 — القرار D2: فصل `order.dispatch.carrier` (تسليم الطلبية لشركة الشحن)

**الفجوة التي يعالجها D2:** قبل 36.11 كان «إرسال الطلبية إلى شركة الشحن» مربوطًا حصريًا بـ`order.manage`. أي عضو يُرسل إلى الناقل يجب أن يُملك `order.manage` بكل ثقله: تعديل كل حقل في **أي** طلبية + كل الإجراءات الجماعية + لوحة إعدادات الطلبيات + طابور التوزيع + إعادة إسناد الفريق. هذا تناقض وظيفي: **العمليات التي تتصل بالناقل يوميًا** (دفع ليلية للطلبيات المؤكَّدة) ممنوعة على موظف يخدم طلباته الخاصة. و`order.dispatch_validate` لا يحلّ شيئًا: هو يتحقق من سجل التسليم ولا يرسل (انظر §4). ⇒ نحتاج رخصة ثانية من عائلة `dispatch`، واثنتان منفصلتان في معناها: `rider` (يدفع الطرد إلى راكب) و`carrier` (يسلّم الطلبية لشركة شحن).

**الجرد الحيّ لكل موضع يسلّم طلبية إلى شركة شحن أو يتصل بواجهة الناقل** (بحث عن كل إشارة إلى `OrderShippingGateway` في `app`+`resources`+`routes`+`database`، 1261 ملفًا):

| # | file:line | العنصر / الفعل | التصنيف |
|--:|-----------|------------------|---------|
| 1 | `orders/index.blade.php:1886` | `$submitConfirmAndSend` — **الحارس** لـ`:1885`؛ يؤكّد ثم يستدعي `->send(..., confirmFirst: true)` في `:1962` | **ينتقل إلى `dispatch.carrier`** + شرط مزدوج (أدناه) |
| 2 | `orders/index.blade.php:1309` | `$confirmBulkSend` — **الحارس** لـ`:1308`، الإرسال الجماعي؛ يستدعي `->send()` في `:1348` عبر `$gateway` (`:1332`) | **ينتقل إلى `dispatch.carrier`** |
| 3 | `orders/index.blade.php:2014` | `$sendConfirmedOrder` — **الحارس** لـ`:2013`، تسليم طلبية مؤكَّدة سلفًا بلا نافذة؛ يستدعي `->send()` في `:2050` | **ينتقل إلى `dispatch.carrier`** |
| 4 | `orders/partials/confirm-drawer.blade.php:127-128` | **زر** «تأكيد + إرسال» — `@if (canStore(ORDER_MANAGE))` | **ينتقل** — **تويم إلزامي** مع #1 |
| 5 | `orders/partials/bulk-actions-bar.blade.php:79` | **`@if` واحد** يحجب معًا زري «إرسال جماعي للناقل» (`:83`) و«تغيير الحالة» (`:89`) | **انشقاق الحجب** — انظر التحذير |
| 6 | `orders/partials/orders-table-actions-column.blade.php:61` | زر «إرسال للناقل» لطلبية مؤكَّدة — `@if (canStore(ORDER_MANAGE) && in_array(status_key, ['confirmed','preparing']))` | **ينتقل** — **تويم إلزامي** مع #3 |
| 7 | `app/Livewire/Concerns/CancelsShipmentFromOrdersTable.php:12` | `abort_unless(canStore(ORDER_MANAGE))` ثم `->cancel()` في `:22` — إلغاء شحنة عند الناقل | **يبقى على `order.manage`** |
| 8 | `app/Livewire/Concerns/TrackingDrawerConcern.php:581` | `->cancel()` عند الناقل من لوحة التتبّع | **يبقى على `order.manage`** |
| 9 | `app/Livewire/Concerns/TrackingTrashConcern.php:193` | `->deleteAtCarrier($order)` — حذف الطلبية من عند الناقل | **يبقى على `order.manage`** |
| 10 | `app/Livewire/Concerns/TrackingBulkValidateConcern.php:329` | `->validate($order, …)` — تحقّق من سجل التسليم | **يبقى** على `order.dispatch_validate` (§4) |
| 11 | `app/Livewire/Concerns/TrackingDrawerConcern.php:619` | `->validate($order, …)` من لوحة التتبّع | **يبقى** على `order.dispatch_validate` (§4) |

**لماذا #7 و#8 و#9 تبقى على `order.manage`:** هي إجراءات **سحب/إتلاف** لا إرسال. `dispatch.carrier` رخصة تسليم موجبة؛ توسيعها لتشملها كان سيمنح من يريد الإرسال فقط صلاحية إلغاء شحنة مبحوحة عند مشغّل خارجي أو حذفها منه — وهي إجراءات **لا رادّ لها** خارجيًا. تركها على `order.manage` قرار **أضيق** لا أوسع، ومتسق مع قاعدة «لا تشديد» في البند 5.

**التحذير على #5 (يجب ألا يُنسى أثناء التنفيذ):** السطر `:79` في `bulk-actions-bar.blade.php` يحجب **فعلين مختلفين** معًا تحت `@if` واحد: الإرسال الجماعي (`dispatch.carrier`) وتغيير الحالة الجماعي (`order.status.manage.own`). فإعادة كتابة `:79` إلى `canStore(ORDER_MANAGE) || canStore(ORDER_DISPATCH_CARRIER)` **تُسقط زر تغيير الحالة** عن من يحمل `status.manage.own` بلا `order.manage`، أي كسر لالتزام D1. ⇒ **فرض إلزامي:** تفكيك الـ `@if` الواحد إلى **`@if` منفصلين** (سطر لكل فعل) في نفس الـ commit. هذا هو التويم الإلزامي الخامس لـD2، وهو الأخطر في القائمة لأن كسره صامت: الزر يختفي بلا رسالة خطأ.

**الشرط المزدوج في `$submitConfirmAndSend` (D2):** الإجراء يفعل شيئين مستقلين — يؤكّد الطلبية **و** يرسلها للناقل — فالرخصة الواحدة لا تكفي:

```php
(canStore(ORDER_MANAGE) || canStore(ORDER_CONFIRM))            // القدرة على التأكيد
&& (canStore(ORDER_MANAGE) || canStore(ORDER_DISPATCH_CARRIER)) // القدرة على الإرسال
```

وهذا هو **Supersede** للحكم **أ** في §1.3: كان يقضي بإبقاء الإجراء كله على `order.manage`، والقرار D2 يستبدله بالشرط المزدوج أعلاه. والنتيجة العملية أن `submitConfirmOnly` (`:1840`، حارسه `ORDER_CONFIRM` في `:1841`) يبقى كما هو، فعضو يحمل `order.confirm` **و** `order.dispatch.carrier` يستطيع التأكيد ثم الإرسال عبر نفس النافذة، بينما `order.confirm` وحده يبقى محصورًا في زر «تأكيد فقط» (`:121` في نفس الـ partial). وهذا تسلسل صريح: **رخصتان، زرّان، لا زرّ واحد بسلطتين.**

### 4. إعادة تسمية/وصف `order.dispatch_validate` — إصلاح تسميقي فقط (بلا تغيير وظيفي)

**الوظيفة سليمة اليوم ولا تحتاج أي تغيير في الكود.** التحقق من صلاحية واحدة: 6 مواضع في `app/Livewire/Concerns/TrackingBulkValidateConcern.php` (`:35`، `:68`، `:293` + تعليق `:15`) و2 في `app/Livewire/Concerns/TrackingDrawerConcern.php` (`:609`، `:648`)، و4 مواضع عرض في `tracking/partials/dispatch-validate.blade.php` (`:5`، `:42`)، `tracking-bulk-actions-bar.blade.php` (`:78` + تعليق `:7`)، `tracking-list.blade.php` (`:12`)، `tracking-toolbar.blade.php` (`:32`). ⇒ **12 موضعًا، كلها صحيحة.** المشكلة الوحيدة: **الاسم** لا يشرح ما يفعله، لا السلوك نفسه.

| العنصر | الحالي | المقترح | الملف |
|--------|--------|---------|------|
| `permissions.order.dispatch_validate` (en) | «Validate Order Dispatch» | «Validate Carrier Handover» | `resources/lang/en/permissions.php` |
| (ar) | «التحقق من إرسال الطلبات» | «التحقق من تسليم الطلبية للناقل» | `resources/lang/ar/permissions.php` |
| (fr) | «Valider l'expédition des commandes» | «Valider la remise au transporteur» | `resources/lang/fr/permissions.php` |
| (es) | «Validar despacho de pedidos» | «Validar la entrega al transportista» | `resources/lang/es/permissions.php` |
| `permissions_descriptions.order.dispatch_validate` | **مفقود** (لا وجود لـ`dispatch_validate` في أي `permissions_descriptions.php` — منفَّذ في 36.7 للـ 4 غيرها فقط) | **يُضاف** — النص في 2.4 | `resources/lang/{ar,en,fr,es}/permissions_descriptions.php` |

**بلا** تغيير في قيمة الـ Enum (تبقى `'order.dispatch_validate'` حرفًا بحرف — `StorePermissionEnum.php:48`) وبلا تغيير في `DEPENDENCIES` (السطر `:102` قائم وصحيح) وبلا أي `canStore` يُمس. **قائمة تحقق (§4):** (أ) `permissions.php` ×4، (ب) `permissions_descriptions.php` ×4، (ج) لا شيء غير ذلك. ونقطة دقيقة: `description()` تقرأ `permissions_descriptions.{$permission}` فالمفتاح المطلوب **`order.dispatch_validate` = عشّ تحت `order`** — أي `'order' => ['dispatch_validate' => '…']`، وهو **مفتاح مختلف تمامًا** عن `dispatch.rider` و`dispatch.carrier` (انتبه لأحرف الواصلة: `dispatch_validate` واحدة بشرطة سفلية، و`dispatch.carrier` نقطتان). وحديثًا عن «الإرسال»: **sending is covered by order.dispatch.carrier or order.manage** — رخصة `order.dispatch_validate` تتحقق من سجل التسليم ولا ترسل شيئًا.

---

### 5. استراتيجية الترحيل — **إضافية بالكامل** (لا كسر، لا تشديد، لا حذف)

> هذا القسم هو **العقد** الذي يقرأه منفّذ 36.12. يُرجى قراءته حرفيًا.

**5.1 — لا يُحذف شيء.** لا حالة `StorePermissionEnum` تُحذف. لا سطر `canStore(ORDER_MANAGE)` في `orders/index.blade.php` (51 سطرًا) أو في أي ملف آخر (45 موضعًا) **يُحذف أو يُستبدل أو يُعدَّل بنيويًّا**. **96 موضع فحص** تبقى عاملة كما هي.

**5.2 — لا يُشدَّد شيء.** كل تحديد جديد **يُضاف كـ OR وليس كبديل**:
```php
// النمط الوحيد المعتمد في 36.12 — حرفيًّا:
canStore(StorePermissionEnum::ORDER_MANAGE->value)                 // كما هو اليوم، أولًا
|| (canStore(StorePermissionEnum::ORDER_EDIT_IDENTITY->value)      // الجديد
    && $orderInScope)                                               // للسطر 1 فقط (المقيَّد بالنطاق)
```
لمن يملك `order.manage` اليوم، **`||` يُرجع `true` فورًا ويُطابق السلوك السابق بايتًا ببايت**. لا عضو واحد — لا مالك ولا أدمن ولا مدير ولا ذا صلاحيات مخصّصة — يرى أي فرق.

**5.3 — الرؤساء يُبقَون وصولهم الكامل عبر `order.manage` كما هم.** OWNER = `StorePermissionEnum::values()` (`StoreRoles.php:19`) ⇒ يأخذ الحالات الستّ الجديدة **تلقائيًا بلا سطر**. ADMIN = `values()` ناقصًا السيادة (`:26-35`) ⇒ يأخذها كذلك. MANAGER (`:43-89`) وSTAFF (`:96-115`) **لا يُمسّان إطلاقًا** (البند 7). ⇒ **«المالكون والمديرون لهم وصول كامل عبر `order.manage` تمامًا كما اليوم»** — وهذا صحيح بالحرف، ولم يتغيّر.

**5.4 — ما الذي تضيفه الصلاحيات الجديدة فعليًا:** **خيار أضيق جديد للمالك** لمنحها لموظف بدل `order.manage` الواسعة. الحالة المعروفة: موظف يحتاج «ينفّذ طلبياتي من التأكيد حتى التسليم» — اليوم هذا يستلزم `order.manage` أي «يعدّل أي حقل في أي طلبية + كل الإجراءات الجماعية + لوحة إعدادات الطلبيات + طابور التوزيع». بعد 36.11 يكفي `order.status.manage.own`. وهذا هو **المكسب**، وهو وحده. **لا شيء يُنزع.**

**5.5 — لا استثناء ولا تحفّظ واحد:** موضعا الراكب (3.4) اتُّخذا **باتحاد `||`** لا باستبدال، فلم يُشدَّد على أي حامل لـ`order.assign` ولا حامل لـ`order.manage`. هذه هي الحالة الوحيدة التي كان يمكن أن تُكسر فيها قاعدة «إضافي فقط»، وقد مُنعت بالتصميم لا بالحيلة ولا بالتنازل.

**5.6 — لا تغيّر في `role templates` (البند 7)، ولا في `crm.orders.confirm` (حُذف في 36.7)، ولا في أي بند من الطبقة A في القسم 10.**

**5.7 — خلاصة الجملة للاختيار:** «الصلاحيات الستّ **جديدة تمامًا وإضافية**. `order.manage` بكل مواضعه الـ 96 يبقى سليمًا ومبنيًا بطريقته القديمة. owner/admin/manager يبقون (وصولًا كاملًا عبر `order.manage`) تمامًا كاليوم. الجديد الوحيد: للمالك خيارٌ أضيق ليتنازل به — `order.status.manage.own` وحرّاسات الحقل وتوزيع الراكبين وتسليم شركة الشحن — بدل تنازل `order.manage` الواسعة. **لا شيء يُزال ولا يُشدَّد على أحد يملك `order.manage` اليوم.**»

---

### 6. قائمة تنفيذ مرتَّبة — النطاق الدقيق لمرحلة 36.12 (مبنية على جدول 1.2 لا على التخطيط)

> **ترتيب تنفيذي إجباري** (سبب كل تجميعة مذكور). المجموعة **صفر** شرط مسبق للمجموعات 1–3.

### 6.0 — المجموعة صفر: التأسيس (بلا تغيير سلوكي)
1. `app/Enums/Store/StorePermissionEnum.php` — إضافة **6** حالات داخل بلوك Orders (`:40-48`): `ORDER_STATUS_MANAGE_OWN`، `ORDER_EDIT_IDENTITY`، `ORDER_EDIT_PRODUCTS`، `ORDER_EDIT_GEOGRAPHY`، `ORDER_DISPATCH_RIDER`، `ORDER_DISPATCH_CARRIER` (46 ⇒ **52**).
2. `app/Support/PermissionGroupMeta.php:90-117` — `DEPENDENCIES` += **6** مدخلات ← `['order.view']`.
3. `resources/lang/{ar,en,fr,es}/permissions.php` — **6** تسميات (البنية المتداخلة في 2.3)، و`'dispatch'` يحمل الآن `rider` **و** `carrier` كشقيقتين.
4. `resources/lang/{ar,en,fr,es}/permissions_descriptions.php` — **8** أوصاف (2.4): الستّ الجديدة + توسيع `order.manage` القائم + توضيح `order.dispatch_validate`.
5. `app/Support/StoreRoles.php` — **بلا تغيير** (لا سطر واحد).
6. **اختبار سريع للـ Hub:** عضو يحمل إحدى الصلاحيات الجديدة فقط + بلا `order.manage` ⇒ يظهر الصف في المجموعة الصحيحة بتسمية مترجمة ووصف غير فارغ، ومفتاح الاعتماديات يُفعّل `order.view` تلقائيًا. **اكتمال المجموعة صفر دون تغيير أي موضع سلوكي.**

### 6.1 — `order.status.manage.own` (3 مواضع داخل الملف + 1 خارجه)
| الترتيب | file:line | التعديل | تحقّق |
|:---:|-----------|---------|-------|
| 1 | **`app/Support/StoreOrderPermissions.php`** | غلاف جديد: `canTransitionStatus(string $orderId, string $statusKey, ?StoreMembership $membership): bool` يجمع `canStore(forStatus(...))` **أو** (`canStore(ORDER_STATUS_MANAGE_OWN)` **و** `Order::where('store_id',…)->whereKey($orderId)->visibleTo($membership)->exists()`) — انظر 1.4. **بلا تعديل في `forStatus()` نفسها** (تبقى `:61` كما هي) | عضو `status.manage.own` على طلبية **غير مرئية له** ⇒ `false`؛ على طلبيته ⇒ `true` |
| 2 | `orders/index.blade.php:1578` | استبدال `canStore(forStatus(...))` بـ`canTransitionStatus($this->getCurrentMembership() …)` مع تمرير `$orderId` | ممرّ `/switch status` يعمل كاليوم لـ`order.manage` |
| 3 | `orders/index.blade.php:2177` `$markOrderDuplicate` | `canStore(ORDER_MANAGE) \|\| (canStore(ORDER_STATUS_MANAGE_OWN) && $order->visibleTo($m))` — `findOrFail` في `:2182` يصبح **بعد** الفحص | طلبية موظف آخر ⇒ لا يمكن تعليمها duplicate |
| 4 | `orders/index.blade.php:2202` `$openBulkStatusModal` | نفس OR (حكم 1.3-و) | لا نافذة لغير المخوَّل |
| 5 | `orders/index.blade.php:2233` `$submitBulkStatus` | نفس OR **+ تصفية لكل-طلبية داخل حلقة `:2259-2271`**: تخطَّ ما لا يمر `visibleTo($m)` واحسبه `skipped++` | `done` محصور في نطاق العضو — **هذا هو الاختبار الحاسم** |
| 6 | التوائم العرضية | **لا شيء** — `duplicate` وbulk status لا لهما عرض في partials التي تفحص `ORDER_MANAGE` | — |

### 6.2 — `order.edit.identity` (9 مواضع)
| file:line | العنصر | ملاحظة |
|-----------|--------|--------|
| `:3224` | `$startOrderPhoneEdit` | |
| `:3248` | `$saveOrderPhone` | |
| `:3301` | `$startOrderNameEdit` | |
| `:3323` | `$saveOrderName` | |
| `:4020` | `$startOrderNotesEdit` | |
| `:4045` | `$saveOrderNotes` | `saveEdit` |
| `:5264` | بطاقة الجوال — زر الاسم | **تويم عرض** |
| `:5311` | بطاقة الجوال — زر الهاتف | **تويم عرض** |
| `:5389` | بطاقة الجوال — زر الملاحظات | **تويم عرض** |
| **`orders-table-cell.blade.php:65`** | customer (جدول سطح المكتب) | **تويم إلزامي** |
| **`:147`** | phone | **تويم إلزامي** |
| **`:198`** | notes | **تويم إلزامي** |
| **`orders-mobile-fields.blade.php:18`** | `$canManage` المجمَّع | **تويم إلزامي** |

> قيمة `order.edit.identity` **store-wide** ⇒ **لا** تُستخدم `visibleTo` في أي منها؛ الشرط `canStore(A) || canStore(B)` فقط. هذا التبسيط مقصود (2.2).

### 6.3 — `order.edit.products` (10 مواضع)
| file:line | العنصر | ملاحظة |
|-----------|--------|--------|
| `:313` `:369` `:377` | `$openItemsModal` / `$addInlineItem` / `$saveOrderItems` | `price` داخل النافذة يبقى محكومًا بـ`itemsPriceEditable()` (`:320`) — **لا** يُلمس |
| `:3748` `:3776` | `$startOrderShipmentTypeEdit` / `$saveOrderShipmentType` | |
| `:3981` `:4008` | `$startOrderWeightEdit` / `$saveOrderWeight` | |
| `:4057` `:4088` | `$startOrderDiscountEdit` / `$saveOrderDiscount` | مالي |
| `:5494` | بطاقة الجوال — قائمة «تعديل الأصناف» | **تويم عرض** |
| **`orders-table-cell.blade.php:297 :319 :345 :414 :475 :507`** | products / quantity / price / discount / weight / shipment_type | **توائم إلزامية**؛ `:345` تُبقي `&& $this->itemsPriceEditable()` |

> **تحذير التسعير الإلزامي:** لا تُبدَّل `ORDER_EDIT_PRICE` في `:345` ولا `itemsPriceEditable()` (`:299-310`) ولا `:320`. حقل السعر يحتاج `order.edit.products` **و** `order.edit.price` معًا.

### 6.4 — `order.edit.geography` (15 موضعًا)
| file:line | العنصر |
|-----------|--------|
| `:3044` | `$openDeliveryModal` |
| `:3410` `:3467` | `$startOrderWilayaEdit` / `$saveOrderWilaya` |
| `:3419` `:3503` | `$startOrderCityEdit` / `$saveOrderCity` |
| `:3579` `:3624` | `$startOrderProviderEdit` / `$saveOrderProvider` — **بلا شرط `dispatch.rider`** (حكم 1.3-ج) |
| `:3700` `:3725` | `$startOrderDeliveryTypeEdit` / `$saveOrderDeliveryType` |
| `:3788` `:3817` | `$startOrderStopdeskEdit` / `$saveOrderStopdesk` |
| `:3941` `:3962` | `$startOrderAddressEdit` / `$saveOrderAddress` |
| `:4126` | `$toggleSendFromWarehouse` |
| `:5445` | بطاقة الجوال — زر الولاية (**تويم عرض**) |
| **`orders-table-cell.blade.php:256 :545 :589 :622 :651 :704 :735`** | wilaya / city / address / delivery_type / shipping_provider / stopdesk_point / send_from_carrier_warehouse — **توائم إلزامية** |

> **لا تقني** `:4391`/`:4476` (النافذة الكاملة) — حكم 1.3-د. ولا `order-settings.blade.php` (6 مواضع) ولا `order-distribution-queue.blade.php` (3).

### 6.5 — `order.dispatch.rider` (موضعان)
1. `app/Livewire/Concerns/TrackingRiderFormConcern.php:29` — `canStore(ORDER_ASSIGN)` becomes `canStore(ORDER_DISPATCH_RIDER) || canStore(ORDER_ASSIGN)`.
2. `resources/.../tracking/partials/order-drawer.blade.php:132` — نفس الاتحاد، حرفيًا نفس التعبير (عرض وحارس متطابقان).
3. **بلا تغيير** في `helpers.php:192` و`orders/index.blade.php:1177` و`StoreRoles.php:57` — وبخاصة **لا إضافة** إلى قائمة MANAGER (البند 7).

### 6.5b — `order.dispatch.carrier` (3 حرّاس + 3 توائم عرض = 6 مواضع)

| الترتيب | file:line | التعديل | تحقّق |
|:---:|-----------|---------|-------|
| 1 | `orders/index.blade.php:1886` `$submitConfirmAndSend` | `(canStore(ORDER_MANAGE) \|\| canStore(ORDER_CONFIRM)) && (canStore(ORDER_MANAGE) \|\| canStore(ORDER_DISPATCH_CARRIER))` | `order.confirm` + `dispatch.carrier` بلا `order.manage` ⇒ ينجح؛ `order.confirm` وحده ⇒ 403 |
| 2 | `orders/partials/confirm-drawer.blade.php:127-128` | **نفس الشرط المزدوج** على `@if` الزر، في نفس الـ commit مع #1 | الزر يظهر لمن يملك القدرتين معًا فقط |
| 3 | `orders/index.blade.php:1309` `$confirmBulkSend` | `canStore(ORDER_MANAGE) \|\| canStore(ORDER_DISPATCH_CARRIER)` | إرسال جماعي بلا `order.manage` |
| 4 | `orders/index.blade.php:2014` `$sendConfirmedOrder` | نفس OR | إرسال طلبية مؤكَّدة بلا `order.manage` |
| 5 | `orders/partials/orders-table-actions-column.blade.php:61` | نفس OR على `@if` الزر (مع إبقاء `&& in_array(status_key, ['confirmed','preparing'])`)، في نفس الـ commit مع #4 | الزر يظهر لمن يملك `dispatch.carrier` على طلبية مؤكَّدة |
| 6 | `orders/partials/bulk-actions-bar.blade.php:79` | ⚠️ **تفكيك الـ `@if` الواحد إلى اثنين** — `@if (canStore(ORDER_MANAGE) \|\| canStore(ORDER_DISPATCH_CARRIER))` لزر `:83`، و`@if (canStore(ORDER_MANAGE) \|\| canStore(ORDER_STATUS_MANAGE_OWN))` لزر `:89` | **الاختبار الحاسم:** عضو `status.manage.own` **بلا** `order.manage` يجب أن يظل يرى زر «تغيير الحالة». لو اختفى الزر ⇒ الـ`@if` لم يُفكَّك |
| 7 | `StoreRoles.php` | **بلا تغيير** (البند 7 يسري على الستّ) | — |
| 8 | `CancelsShipmentFromOrdersTable.php:12`، `TrackingDrawerConcern.php:581`، `TrackingTrashConcern.php:193` | **بلا تغيير عمدًا** — إجراءات سحب/إتلاف تبقى على `order.manage` (§3.6) | — |

> **قابلية التطابق الإلزامية لـD2:** الأزواج (1↔2) و(4↔5) لا يجوز فصلهما بين commitَين. الحارس بلا زر = زر يظهر ويصدر 403؛ والزر بلا حارس = زر مخفي مع حق فعلي عند من يستدعي `$wire` مباشرة. الزوج (3,6) هو الأخطر لأن `#6` يحجب فعلين في `@if` واحد.

### 6.6 — `order.dispatch_validate` (تسميات فقط)
قائمة 4 أ (§4): `permissions.php` ×4 + `permissions_descriptions.php` ×4. **صفر سطر وظيفي.**

### 6.7 — الشهادة المطلوبة (بنفس أسلوب 36.7)
- `grep` على `TrackingRiderFormConcern.php` و`order-drawer.blade.php`: `ORDER_ASSIGN` **لم يبقَ منفردًا** — صار بديلًا داخل `||` مع `ORDER_DISPATCH_RIDER` (بلا سطر يبقى بلا بديل).
- `grep` ≥ **6** تعريفات جديدة في `StorePermissionEnum` (46 ⇒ 52)، و≥ **6** مدخلات في `DEPENDENCIES`، و≥ **7** أوصاف في `permissions_descriptions.php` (لكل لغة).
- `grep` على `bulk-actions-bar.blade.php`: عدد أسطر `@if` التي تحجب `openBulkSendModal` **1**، والتي تحجب `openBulkStatusModal` **1** — أي **مفصولان** (6.5b-6). لو بقي `@if` واحد يغطّهما ⇒ الاختبار الحاسم يفشل.
- **اختبار non-regression الأهم:** كل اختبارات Merchant خضراء بلا تعديل على التوقعات (مثل Phase 36.7: 628 ناجح / 2711 تأكيد؛ إعادة القياس مطلوبة).
- **اختباران جديدان نَعلان:** (أ) **اختبار نطاق D1** — عضو `status.manage.own`: على **طلبيته** ينجح انتقال `confirmed` **و** ينجح انتقال `no_answer_1` (كلاهما من مجموعتَي `forStatus()` لا من fallback `:61`)؛ وعلى **طلبية زميله** يُرفض الاثنان. هذا يثبت أن فرع الـ OR يغطي **كل** مفاتيح الحالات وأن القيد الوحيد هو `visibleTo()` — وهو بالضبط ما كانت ساخنه الجملة المقتصرة على `:61` فقط. (ب) **اختبار عدم-التراجع للانقسام** — عضو `order.assign` عبر pivot **بلا** `dispatch.rider` ⇒ `assignRider` **تنجح** (لا 403) لأن `ORDER_ASSIGN` بديل باقٍ، بينما عضو بلا كليهما ⇒ 403. هذا هو الفرق بين Union وReplacement، وهو ما يمنع كسر قالب MANAGER.
- **اختبار ثالث جديد (D2):** عضو يملك `order.confirm` **و** `order.dispatch.carrier` بلا `order.manage` ⇒ زر «تأكيد + إرسال» يظهر **و** `$submitConfirmAndSend` تنجح. وعضو يملك `order.confirm` وحده ⇒ الزر «تأكيد فقط» يعمل، وزر «تأكيد + إرسال» لا يظهر. وعضو يملك `dispatch.carrier` وحده ⇒ لا يستطيع التأكيد (يُرفض في `:1886` رغم أنه يملك نصف الشرط) — وهذا يثبت أن الشرط **مزدوج (AND)** لا بديل واحد (OR).
- `php -l` نظيف؛ جولة Merchant كاملة خضراء.

---

### 7. قوالب الأدوار — **قرار مُثبَّت، ليس سؤالًا مفتوحًا**

> **القرار: لا تُضاف أي من الصلاحيات الستّ الجديدة إلى أي قالب افتراضي. قوالب MANAGER وSTAFF من 36.7 تبقى كما هي بلا مسّ واحد.**

| القالب | القرار | السبب |
|--------|--------|-------|
| **OWNER** (`StoreRoles.php:19`) | **بلا سطر** | `StorePermissionEnum::values()` ⇒ يسحب الستّ **تلقائيًا**. الخيار الوحيد الممكن، والبلا سطر.
| **ADMIN** (`:26-35`) | **بلا سطر** | `values()` ناقصًا السيادة ⇒ يسحب الستّ تلقائيًا. |
| **MANAGER** (`:43-89`) | **بلا سطر** | **(1)** لا يحتاج: `order.manage` (`:55`) يغطي سلوكه الحالي بالكامل — بما فيه الإرسال للناقل، لأن شرط `(ORDER_MANAGE \|\| …)` يُرجع `true` عنده فورًا. **(2)** فلسفة 36.7: القوالب تُكتب للسلوك **الافتراضي**، والترقيات الصريحة تُمنح من الـ Hub. **(3)** إعطاؤه `status.manage.own` أو `dispatch.carrier` كان سيخلق تكرارًا بلا فائدة — `order.manage` يسبقهما في OR دائمًا. |
| **STAFF** (`:96-115`) | **بلا سطر** | أساس STAFF = «تأكيد فقط» (`order.confirm` في `:105`، بلا `order.manage`). و`status.manage.own` ليست «تأكيدًا فقط»: هي **جدولة حالة متقدمة** تشمل التأكيد ونتائج الاتصال (D1)، ولذلك لا تُكتب في القوالب الافتراضية. وكذلك `dispatch.carrier`: تسليم الطلبية لشركة شحن **إجراء خارجي** لا تتبعه STAFF «تأكيدًا فقط» تحت أي ظرف. |



**العبارة المعتمدة للنشر:** «الصلاحيات الستّ الجديدة **لا تُكتب في أي قالب افتراضي**. غرضها تحديدًا أن يمنحها المالك **فرديًا** من نافذة الـ Permission Hub لموظف يحتاج — مثلًا — «أدير حالة طلبياتي فقط» دون `order.manage` الواسعة. أي إضافتها إلى STAFF/MANAGER ستُلغي وجودها كخيار، وتُعيدنا إلى مشكلة 36.7 (ترقيات صريحة تُدفن في القوالب).»

---

### 8. أرقام الشهادة (كلها مقيسة في هذه الجلسة، 2026-09-27، `HEAD=cdc4402`؛ صفّ 36.11.1 أُعيد قياسه في 2026-09-28 على `HEAD` نفسه)

| المقياس | القيمة | كيف قِيست |
|---------|--------|----------|
| أسطر `orders/index.blade.php` | **5639** | `.Count` على `Get-Content` (لا `Measure-Object -Line`) |
| `ORDER_MANAGE` في `orders/index.blade.php` | **51** | `Select-String -Pattern ORDER_MANAGE` |
| `ORDER_MANAGE` في التطبيق كله (`app`+`resources`) | **99 سطرًا** | مسح متكرر |
| مواضع فحص `ORDER_MANAGE` الوظيفية | **96** | 99 − (Enum `:41` + قالب `StoreRoles` `:55` + fallback `StoreOrderPermissions` `:61`) |
| `ORDER_ASSIGN` — مواضع فحص وظيفية | **5** | من 7 إشارات (1 تعريف + 1 إيجابية كاذبة `:3932`) |
| `ORDER_ASSIGN` في `TrackingGridConcern.php` | **0** | بحث حيّ = صفر (ادّعاء التخطيط مُلغى) |
| `ORDER_DISPATCH_VALIDATE` — مواضع | **12** (13 تُنقص تعريف الـ Enum) | 4 `TrackingBulkValidateConcern` + 2 `TrackingDrawerConcern` + 6 في 4 ملفات tracking blade |
| حالات `StorePermissionEnum` | **46** | عدّ `^\s*case ` |
| مجموعات أعمدة جدول الطلبيات | **4** (لا 3) | `:595 :604 :614 :623` |
| سلال التصنيف | 15 / 15 / 10 / 9 / 2 = **51** | جدول 1.2 |
| حالات `StorePermissionEnum` **بعد** التنفيذ | **52** | 46 + 6 (§6.0-1) |
| الصلاحيات الجديدة الإجمالية | **6** | حالة + 3 تحرير + `dispatch.rider` + `dispatch.carrier` (D2) |
| أوصاف مطلوبة لكل لغة | **7** | 6 جديدة + توسيع `order.manage` (§6.0-4) |
| مواضع إرسال الطلبية لشركة شحن (D2) | **3 حرّاس + 3 توائم** | `:1886`، `:1309`، `:2014` + `:127-128`، `:79`، `:61` (§3.6) |
| استدعاءات `->send()` (تسليم فعلي) | **3** | `:1962`، `:2050`، `:1348` (عبر `$gateway`) |
| إشارات `OrderShippingGateway` في `app`+`resources`+`routes`+`database` | **14 في 6 ملفات** | مسح 1261 ملف PHP/blade |

---

**القرارات المُثبَّتة في هذه الإضافة (لا تحتاج إعادة فتح):** (D1) فرع `canTransitionStatus()` يغطي **كل** مفاتيح الحالات على طلبيات العضو، شاملةً التأكيد ونتائج الاتصال — والشرط الوحيد `visibleTo()` (§1.4). (D2) رخصة سادسة `order.dispatch.carrier` بستة مواضع إرسال، وشرط مزدوج (AND) في `$submitConfirmAndSend`، و`bulk-actions-bar.blade.php:79` يجب تفكيكه (§3.6، §6.5b). **(الحكم أ في §1.3-أ مُلغى ومستبدل بـD2.)**

**قرارات لا تزال مفتوحة قبل فتح 36.12:**
- **(ب)** تصفية لكل-طلبية داخل `$submitBulkStatus` (`:2259-2271`) حتى يبقى `done` محصورًا في نطاق العضو (1.3-ب، 6.1-5).
- **(ج)** تغطية `order.edit.products` المالية في قائمة `DANGEROUS` — **تُترك خارج النطاق** متسقةً مع `order.edit.price` غير المُدرج فيها.
- **(د)** دَين «إسناد الفريق محجوب بـ`order.manage`» — خمسة مواضع (`:2285`، `:2298`، `:3850`، `:3863`، `:3886`) مؤجَّلة لمرحلة منفصلة (3.4-2).

**Status: documented, awaiting implementation approval** — هذا إدخال **تخطيطي بحت** (addendum 36.11.1 على 36.11). لم تُنفَّذ أي صلاحية جديدة ولم يتغيّر أي سطر سلوكي؛ D1 وD2 مُثبَّتان. القرارات المفتوحة المتبقية ثلاثة: **(ب)** تصفية bulk-نطاق، **(ج)** `order.edit.products` في `DANGEROUS` (تُترك خارج النطاق)، **(د)** دَين إسناد الفريق المؤجَّل. تالية مقترحة: **36.12** عبر قائمة القسم 6 أعلاه حرفيًّا.
