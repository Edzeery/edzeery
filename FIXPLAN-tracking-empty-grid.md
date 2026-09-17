# خطة إصلاح — صفحة تتبع الطلبيات لا تعرض أي صفوف (merchant.tracking.index)

> التاريخ: 2026-09-17 — النطاق: فحص + خطة فقط (لم يُعدَّل أي ملف مصدر).
> المشروع: `C:\laragon\www\edzeery` — PHP `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe`

> **✅ حالة التنفيذ (2026-09-17):** نُفِّذ الإصلاح المعماري فعليًا كما وافق عليه المستخدم (الفصل بين حالات التتبع وحالات الطلبية بلا تكرار، انظر البند 4) ولا تنطبق المراحل الواردة أدناه «كما هي» إلا كسياق. المُنفَّذ: (1) `SystemStatusesSeeder` يبني صفوف `tracking` حصريًا من `OrderTrackingStatus::cases()` بفحصَي اتساق (مفاتيح tracking == حالات enum؛ مفاتيح كل مجموعة `OrderWorkflow::backOffice/carrier/closed` ⊆ مفاتيح صفوف type=order); (2) نقطة دخول موحّدة جديدة `OrderWorkflow::carrierStatusIds()`; (3) `TrackingGridConcern` يستدعيها مع تعليق التوثيق; (4) اختبار الحماية `TrackingGridStatusScopeTest` (3 اختبارات). **النتائج:** بذر حي ناجح بلا استثناء (صفوف tracking الـ 11 مطابقة للـ enum label/color/ترتيب) + 52/52 في مجموعة التتبع (كانت 19 فشلًا) + 24/24 في سويتات الشحن — صفر انحدار (التفاصيل في البند 6).

---

## 1) الخلاصة التنفيذية

الكوميت `8e121b3` غيّر في `app/Livewire/Concerns/TrackingGridConcern.php` سطر فلترة `orders.status_id` من
`Status::system()->forType('order')` إلى `Status::system()->forType('tracking')` عند مفاتيح
`OrderWorkflow::carrier()` (shipped/in_transit/out_for_delivery/delivered/returned).

طلبات المتجر تحمل `status_id` يشير إلى صفوف `statuses` من **النوع `order`** بمعرّفات ULID مختلفة تمامًا عن صفوف النوع `tracking`
(لكل مفتاح صفّان مختلفان: `order` و`tracking`). الفلترة بالمفاتيح `tracking` لا تطابق أي طلب → `whereIn('status_id', $ids)` يفقد الصفوف → الشبكة فارغة في التبويبين (carrier وrider)، والبطاقات الإحصائية صفر.

الحل المباشر: استعادة `forType('order')` في السطر 179 من نفس الملف (سطر واحد، مُثبت تجريبيًا أنه يعيد الصفوف).

> لتوضيح الخلط بين المجموعات الثلاث (حالات التوصيل / التتبع / تأكيد الطلبية) وتوثيق قواعد الفصل بينها، راجع **القسم 8**.

---

## 2) سجل الأدلة

### 2.1 مراجعة git (إعادة إنتاج التغيير)

```powershell
git log --oneline -15            # 8e121b3 = HEAD (تغيير forType)
git show 8e121b3 -- app/Livewire/Concerns/TrackingGridConcern.php
git show 17f72eb:app/Livewire/Concerns/TrackingGridConcern.php | Select-String "forType" -Context 2,2
```

**النتيجة:** `17f72eb` (الذي أنشأ الملف) كان يحوي:
```php
$trackingStatusIds = \App\Models\Status::system()->forType('order')
```
و`8e121b3` غيّرها إلى:
```php
$trackingStatusIds = \App\Models\Status::system()->forType('tracking')
```
والفرق الوحيد المؤثر في هذا الملف بين الكوميتين هو هذا السطر (بقية الديف = مسافات/سطر جديد).

### 2.2 استعلامات حقيقية (سكربت `diag_tracking.php` + `diag_full.php` المعدّ)

(i) معرّفات `Status::system()` لمفاتيح carrier — مقارنة النوعين (المخرج نفسه في السكربتين):

| key | type=order (id) | type=tracking (id) |
|---|---|---|
| shipped | `01m2qngwyr4v7pxz5amgzxxwwn` | `01m2qngwzwjfxgpq13j580tvpc` |
| in_transit | `01m2qngwyt3jtavkg70spdta3t` | `01m2qngwzx11x6rag1tfxvrh9d` |
| out_for_delivery | `01m2qngwyvkf26cyw7jcwxmzx8` | `01m2qngwzzajgx71ve3k7fpjhc` |
| delivered | `01m2qngwyw06gs9ps545f6cdbn` | `01m2qngx02a02b4y2263b4bgbn` |
| returned | `01m2qngwyyw8rse2s0gavnhygt` | `01m2qngx036zdnpvtgm0twnt9t` |

`ids equal: false` — **كل مفتاح له معرّفان مختلفان حسب النوع**. قاعدة البيانات تحتوي 46 حالة فيها 10 صفوف لنفس المفاتيح الخمسة (5 `order` + 5 `tracking`).

(ii) عدّ لكل متجر (متأثر: `Edzeery Demo Store` فقط، أوامره 2):

```
STORE 01m2qnh10v7w009ffhz1h7qbtn (Edzeery Demo Store)
orders total=2  with_provider=2  with_rider=0
carrier tab rows: forType(order)=1   forType(tracking)=0     ← التبويب فارغ حاليًا
rider   tab rows: forType(order)=0   forType(tracking)=0
loading: status_id => key/type:
  01m2qngwy8q5nzrhd6kpg56qwa => pending  (type=order)  count=1
  01m2qngwyr4v7pxz5amgzxxwwn => shipped  (type=order)  count=1
```

باقي المتاجر: صفر طلبيات أصلاً (`Default Merchant Store` و`Demo Store` كلاهما `0`).

(iii) حالة كل طلب حيًّا:

```
00001 store=Edzeery Demo Store status:[type=order/key=shipped]   provider=NOEST  tracking_status=shipped
00002 store=Edzeery Demo Store status:[type=order/key=pending]   provider=NOEST  tracking_status=
```

**الخلاصة:** الطلب الأوّل (00001) يطابق `forType('order')` ولن يطابق `forType('tracking')` لأن `status_id` من نوع `order`. هذا هو السبب المباشر.

### 2.3 التحقق الطبقي (اختبار Pest مؤقت، ثم حُذف)

أنشأتُ مؤقتًا `tests/Feature/Merchant/ZzTrackingEmptyGridProofTest.php` (نفس نسق سكربت `TrackingTrashWebhookLabelTest`) ثم حذفتُه بعد التشغيل. نتيجته:

```
PASS  order-status ids and tracking-status ids differ for the carrier workflow keys          1.30s
PASS  current baseTrackingQuery (forType tracking) yields an empty carrier grid ...          21.04s
Tests: 2 passed (9 assertions)
```

ما يثبت:
- `orderIds ≠ trackingIds` (قائمتان من 5 ULID متمايزتين).
- عبر `Volt::test('merchant.tracking.index')` (session `current_store_id` = المتجر):  
  `assertSet('filteredTotal', 0)` و`assertSet('shipments', [])` — أي النسخة الحالية تعطي **0** لطلب `status_id`=order/shipped + `shipping_provider_id`.
- نفس بيانات المتجر: استعلام `whereIn('status_id', $trackingIds)` → **0 صف**، و`whereIn('status_id', $orderIds)` → **≥ 1 صف** (أثبت أن استعادة `forType('order')` تُظهر الطلب).

### 2.4 اختبارات الانحدار — النتائج قبل الإصلاح (كود مكسور الآن)

| الملف | النتيجة (قبل الإصلاح) |
|---|---|
| `tests/Feature/Merchant/TrackingTrashWebhookLabelTest.php` | **1 فاشل / 15 ناجح** (58 assertions) — الفشل: «restore brings them back» السطر 177: `count($rows) === 1` (يتوقع ظهور الشحنة المستعادة) |
| `tests/Feature/Shipping/ShipmentCancelCarrierUnknownTest.php` | 3 ناجح (16 assertions) |
| `tests/Feature/Shipping/NoestTrackingSyncTest.php` | 11 ناجح (34 assertions) |
| `tests/Feature/Shipping/NoestTrackingSyncServiceTest.php` | 10 ناجح (51 assertions) |
| `tests/Feature/Merchant/TrackingSearchFilterTest.php` (إضافة وقائية — الأنطق تغطية) | **18 فاشل / 15 ناجح** (96 assertions) |

قائمة الفشل الـ 18 في `TrackingSearchFilterTest` (كلها من جذر واحد — غياب الصفوف):
`switching to the rider tab equips the unified grid with the rider column`؛ `the rider tab only lists orders with an assigned rider`؛ `search narrows ... tracking number`؛ `multi-status filter toggles ...`؛ `provider filter narrows ...`؛ `amount and city header filters ...`؛ `clearFilters resets ...`؛ `the drawer sync action re-polls ...`؛ `the rider tab shows configured riders ... per-rider counts`؛ `filtering the rider column ...`؛ `assigning a rider from the drawer ...`؛ `the rider tab renders aggregate stats ...`؛ `the unified grid paginates ...`؛ `a restricted staff member sees only shipments ... rider tab`؛ `... rider-tab stats include only their own ...`؛ `a restricted manager ...`؛ `an unrestricted owner still sees every rider shipment ...`؛ `products filter narrows ...`.

> ملاحظة قيّمة: السويتة القائمة كانت **تكشف** الانحدار أصلًا (مثلما أعلن `17f72eb` لمنطقة tracking) — فشل `TrackingTrashWebhookLabelTest` يعيد نموذج «الفشل الذي ينبغي أن يمنع هذا الكوميت».

### 2.5 `storage/logs/laravel.log`

- **لا يوجد `local.ERROR`** سببه هذه الصفحة: الفراغ صامت (استعلام يُرجع 0 صفوف، لا استثناء). 
- موجودة لكن غير ذات صلة بالفراغ:
  - `testing.ERROR: NOEST trackings/info request failed (HTTP 503)` — من اختبارات المحاكاة (توقُّع).
  - `local.ERROR: rename(...storage\framework\views\...) Access is denied (code: 5)` — قفل ملفات Windows على الـ view cache (معروف في بيئة Laragon)، ليس فراغًا وظيفيًا.
  - `local.ERROR: SQLSTATE[01000] ... Data truncated for column 'store_scope_id'` (2026-09-16) — سابقة للإصلاح، في عمود غير مستخدم بهذا السياق، لا تسبب الفراغ.

### 2.6 استبعاد متغيرات أخرى محتملة

- **`filters`**: الافتراضي في `state([])` كلها null/فارغة؛ تعطيل فلتر الحالة `tracking_statuses=[]` (يعمل على `latestTracking.tracking_status` أصلاً وليس `status_id`).
- **`showTrash`**: الافتراضي `false`؛ ومسار trash (الأسطر 24–67) **لا يطبّق** فلتر `status_id` إطلاقًا → غير متأثر (وهذا سبب نجاح اختبارات trash داخل `TrackingTrashWebhookLabelTest` وتمريرها حتى مع الخلل، باستثناء استعادة الظهور).
- **`currentStoreId()`**: `helpers.php:236` ← `currentStore()->id` ← `StoreResolver::resolve()` (`app\Support\StoreResolver.php`) من `Filament tenant → StoreContext → session('current_store_id') → X-Store-Id/query → subdomain`. الاختبارات تضبط `withSession(['current_store_id' => ...])` وتعمل.
- **الصلاحية**: `mount` يحرس `abort_unless(canStore(ORDER_VIEW), 403)` (`index.blade.php:222`) — مستوى صفحة؛ السيناريوهات الموزّعة (`restricted staff/manager`) فشلت في السويتة بسبب **الصفوف المفقودة** لا بسبب الصلاحية (الحرّاس تخطّوا الصفحة بنجاح في اختبارات role-scoping الأخرى).
- **الفرق الوحيد بين الكوميت السليم والمكسور في هذا الملف**: سلوك السطر 179.

---

## 3) السبب الجذري (بتفصيل)

**الموقع:** `app/Livewire/Concerns/TrackingGridConcern.php:179–181`

```php
→ 179  $trackingStatusIds = \App\Models\Status::system()->forType('tracking')    // <-- خطأ
   180      ->whereIn('key', \App\Domains\Order\Support\OrderWorkflow::carrier())
   181      ->pluck('id')->all();
   183  if (! empty($trackingStatusIds)) {
   184      $query->whereIn('status_id', $trackingStatusIds);   // فلتر status_id بمعرّفات tracking
   185  }
```

- `orders.status_id` يرتبط (علاقة `Order::status`) بجدول `statuses` حيث صفوف حالة الطلبية من النوع **`order`** (تعبئة `OrderService.php:24` و`:126` و`:173`، و`OrderObserver.php:39` كلها تستخدم `forType('order')` عند كتابة/قراءة حالة الطلب).
- النوع **`tracking`** صُمِّم (برومت P8 في `Todos.md`) لعمود `order_trackings.tracking_status` ولـ mystatuskit، وليس لعمود `orders.status_id`. عند `8e121b3` أُدخلت مفاتيح`OrderWorkflow::carrier()` على النوع الخاطئ، فجُمعت 5 معرّفات tracking لا يملك أي `orders.status_id` واحدًا منها.
- النتيجة: `whereIn('status_id', $ids)` لا يطابق شيئًا في كل المتاجر → `loadShipments()` (`TrackingGridConcern.php:280`) يضع `filteredTotal = 0` و`shipments = []`، و`loadTrackingStats()` (السطر 194) يقرأ القاعدة المفلترة نفسها (`baseTrackingQuery(true)`) فتعطي `active/delivered_today/returned_today` كلها `0`.
- التبويبان (carrier/rider) يتفرعان داخل `baseTrackingQuery` نفسها (السطر 138–154) قبل فلتر الحالة المشترك (179–185) → **كلاهما مصاب** بنفس الجذر، ولهذا فشلت اختبارات rider أيضًا.
- `showTrash` يستخدم مسار `onlyTrashed()` مستقل بلا فلتر حالة → غير متأثر (يتوافق مع نتائج الاختبارات).

---

## 4) خطة الإصلاح (مراحل)

> **التنفيذ الفعلي (بديل المراحل أدناه):** بتوجيه المستخدم المصرِّح («نغذ الاصلاح» + «الاصلاح الفعلي هو الفصل بين حالات التتبع وحالات تأكيد الطلبية بشكل منطقي وصحيح وعدم التكرار» + «اصلح الحالات في database\seeders\SystemStatusesSeeder.php»)، حُوِّل إصلاح السطر الواحد إلى إصلاح معماري: مصدر وحيد للحالات الجدولية بدل القوائم اليدوية الثلاث المكررة.

**المنفَّذ فعليًا — المرحلة 1 (الهيكلة):** `database/seeders/SystemStatusesSeeder.php` — حُذفت كتلة TRACKING اليدوية (11 صفًا) وأُغلقت مصفوفة `$statuses` بعد كتلة `order`؛ تُبنى صفوف `tracking` الآن من `OrderTrackingStatus::cases()` (value/kitLabel/kitVariant/sort_order) مع فحصَي اتساق يرمي `RuntimeException` عند التضارب: (أ) مفاتيح tracking المزروعة == حالات enum `OrderTrackingStatus`; (ب) مفاتيح كل مجموعة `OrderWorkflow::backOffice()/carrier()/closed()` ⊆ مفاتيح صفوف type=order.

**المنفَّذ فعليًا — المرحلة 2 (دخول موحّد):** `app/Domains/Order/Support/OrderWorkflow.php` — طريقة جديدة `carrierStatusIds()` تُحلّ «مفاتيح `carrier()` → معرّفات id من `Status::system()->forType('order')`» وهي **نقطة الدخول الوحيدة** لفلترة `orders.status_id` في صفحة التتبع. `TrackingGridConcern.php` يستدعيها بدل الاستعلام المضمّن.

**المنفَّذ فعليًا — المرحلة 3 (اختبار حماية):** `tests/Feature/Merchant/TrackingGridStatusScopeTest.php` — 3 اختبارات: ظهور طلب شركة في تبويب carrier (النطاق type=order)، ظهور طلب موصِّل في تبويب rider، واختبار «التركيز» الذي يُثبت أن `OrderWorkflow::carrierStatusIds()` لا تتقاطع أبدًا مع معرّفات نوع tracking ولا يُطابقها أي `orders.status_id`.

> خطوات الإصلاح «السطر الواحد» أدناه (المرحلة القديمة 1) بقيت للسياق التاريخي فقط؛ أثبتتها النسخة المُراجعة من القسم 1.

---

## 5) اختبارات الإثبات (وصف اختبار Pest مقترح)

الموضع المقترح: ملف جديد `tests/Feature/Merchant/TrackingGridStatusScopeTest.php`
(بنسق `TrackingTrashWebhookLabelTest`/`TrackingSearchFilterTest`: `roleUser('merchant')` + `Role OWNER` + `Store` + `StoreMembership` + `SystemStatusesSeeder` + `Volt::test('merchant.tracking.index')` مع `withSession(['current_store_id' => $store->id])`).

```php
it('shows a provider-sent order on the carrier grid (status scope must be type=order)', function () {
    // بذرة الأدوار + الحالات؛ OWNER + Store + NOEST provider (مثل tsfNoestProvider)
    // Order::create(['status_id' => Status::system()->forType('order')
    //     ->where('key','shipped')->firstOrFail()->id, 'shipping_provider_id' => $provider->id, ...])
    // + OrderTracking::create(['tracking_status' => OrderTrackingStatus::IN_TRANSIT->value])

    Volt::test('merchant.tracking.index')
        ->assertSet('filteredTotal', 1)                      // قبل الإصلاح: 0 → مكسور
        ->assertSet('shipments', fn ($rows) => count($rows) === 1);   // وقيمة number للطلب

    // حماية من نكسة forType:
    $keys = OrderWorkflow::carrier();
    expect(Status::system()->forType('order')->whereIn('key', $keys)->pluck('id'))
        ->not->toBe(Status::system()->forType('tracking')->whereIn('key', $keys)->pluck('id'))
        ->and(Order::where('store_id', $store->id)->whereNotNull('shipping_provider_id')
            ->whereIn('status_id', Status::system()->forType('order')->whereIn('key', $keys)->pluck('id'))
            ->count())->toBe(1);
});

it('shows a rider-assigned order on the rider grid', function () {
    // نفس البذرة لكن DeliveryRider + delivery_rider_id:
    // $volt->set('trackingTab', 'rider')->call('loadShipments')
    //     ->assertSet('filteredTotal', 1)
    //     ->assertSet('shipments', fn ($rows) => count($rows) === 1);
});
```

**حيث يُضاف:** تحت `tests/Feature/Merchant/` (مسار الحزمة الحالية لاختبارات التتبع). هذان الاختباران يعيدان الـ `0` المرصود حاليًا (فشل قبل الإصلاح) ويلتقطان أي عودة لـ `forType('tracking')` لأن `filteredTotal` سيصير `0`.

---

## 6) معايير القبول (checklist)

- [x] `app/Livewire/Concerns/TrackingGridConcern.php` يستخدم `OrderWorkflow::carrierStatusIds()` (نقطة دخول موحّدة type=order) مع تعليق التوثيق — لا تعليق على `forType('tracking')`.
- [x] `grep -r forType('tracking') app/` يعيد **لا شيء** (لا بقاء لأي استخدام لنوع tracking على `orders.status_id`).
- [x] `grep -r forType('order') app/` يعيد 5 مواضع سليمة: OrderObserver:39، OrderService:24/126/173، + `OrderWorkflow::carrierStatusIds()` (النقطة الموحّدة لجريد التتبع).
- [x] `SystemStatusesSeeder`: صفوف `tracking` من `OrderTrackingStatus::cases()` فقط + فحصا الاتساق (enum==seeded؛ مجموعات `OrderWorkflow` ⊆ مفاتيح type=order) — بذر حي على DB المتجر ناجح بلا `RuntimeException` وصفوف الـ 11 مطابقة label/color/ترتيب بعد إعادة البذر.
- [x] `php -l` سليم على: `SystemStatusesSeeder.php`، `OrderWorkflow.php`، `TrackingGridConcern.php`، `TrackingGridStatusScopeTest.php`.
- [x] `tests/Feature/Merchant/TrackingGridStatusScopeTest.php` (الجديد): **3/3 ناجحة** — carrier يعرض طلب الشركة، rider يعرض طلب الموصل، ونطاق tracking (type tracking) لا يطابق `orders.status_id` أبدًا.
- [x] `tests/Feature/Merchant/TrackingTrashWebhookLabelTest.php`: **16/16 ناجحة** (كانت 1 فاشل).
- [x] `tests/Feature/Merchant/TrackingSearchFilterTest.php`: **33/33 ناجحة** (كانت 18 فاشل).
- [x] `tests/Feature/Shipping/ShipmentCancelCarrierUnknownTest.php` + `NoestTrackingSyncTest.php` + `NoestTrackingSyncServiceTest.php`: **24/24 ناجحة بلا تغيير** (صفر انحدار).
- [ ] تحقق يدوي في المتصفح: فتح صفحة التتبع لمتجر لديه شحنات — تبويب carrier يعرض الصفوف، وrider يعرض شحنات الموصلين، وبطاقات `active/delivered_today/returned_today` بقيم غير صفرية.
- [x] لا تغيير schema/migrations في الـ diff.

---

## 7) مناطق الانحدار المحتملة وكيفية اكتشافها مبكرًا

| المنطقة | لماذا قد تنحرف | الكشف المبكر |
|---|---|---|
| **`loadShipments()`/`filteredTotal`** (السطر 280/285) | أي تغيير مستقبلي في فلتر `status_id` داخل `baseTrackingQuery` | اختباران في القسم 5 ينكسران (`filteredTotal==0`) |
| **`loadTrackingStats()`** (السطر 194، `baseTrackingQuery(true)`) | يشترك بنفس القاعدة المفلاترة؛ أي فراغ فيها يجعل `stats` و`riderStats*` صفر | اختبارا `rider tab renders aggregate stats` و`amount and city header filters ... stats` في `TrackingSearchFilterTest` |
| **تبويب rider** (السطر 138–153 + 179–185) | الحارس `delivery_rider_id` + نفس فلتر الحالة | اختبارات rider الـ 8 في `TrackingSearchFilterTest` + الاختبار الثاني في القسم 5 |
| **وضع trash `showTrash`** | لا يطبق فلتر الحالة (مسار مستقل) — يُعاد الاختبار لضمان عدم «إصلاح» يكسر الاستعادة | `TrackingTrashWebhookLabelTest` (استعادة ← ظهور) |
| **فلتر `tracking_statuses`** (السطر 106–108) | يعمل على `latestTracking.tracking_status` وليس `status_id`؛ أي خلط مستقبلي بين العمودين | `multi-status filter toggles and combines tracking statuses` |
| **الحالات المخصّصة للتاجر** | الفلتر يستخدم `Status::system()` فقط (قل الحالي ≠ مخصص). أي نقلة مستقبلية إلى statuses مخصصة تحتاج قرارًا منفصلًا | اختبارات `StoreStatusCustomizationTest`/`StatusCustomizationPageTest` |
| **شركاء الإرسال (OrderShippingGateway / Noest sync)** | تغير أثناء إرسال/مزامنة لا علاقة له بالفلتر لكن قد يغيّر `status_key` المتوقع | سويتا `NoestTrackingSyncTest` و`NoestTrackingSyncServiceTest` و`ShipmentCancelCarrierUnknownTest` |

**نصيحة وقائية:** بعد تطبيق المرحلة 1، شغّل `vendor/bin/pest tests/Feature/Merchant/TrackingSearchFilterTest.php tests/Feature/Merchant/TrackingTrashWebhookLabelTest.php` قبل أي شيء آخر — فشلهما هو المؤشر الأول لعودة النكسة.

---

## 8) تصنيف حالات النظام — توضيح الخلط (التأكيد / التوصيل / التتبع)

هذا القسم يجيب عن الخلط التاريخي بين «حالات تأكيد الطلبية» و«حالات التوصيل» و«حالات التتبع»، وموثّق بالكود والقاعدة (نتيجة مسح `forType(` و`OrderWorkflow::` في `app/`).

### 8.1 المجموعات الثلاث (المصدر: `database/seeders/SystemStatusesSeeder.php`)

| المجموعة | `type` | المفاتيح (المرجع الدلالي) | تُخزَّن في | تظهر في |
|---|---|---|---|---|
| **حالات تأكيد/معالجة الطلبية (Back-office)** | `order` | `OrderWorkflow::backOffice()` — draft, pending, no_answer_1/2/3, postponed, wrong_number, out_of_stock, duplicate, on_hold, confirmed, preparing, unclaimed, undeliverable (مع مرحلتي `preparing` [سطر 133] و`unclaimed`/`undeliverable` [سطر 258–289]) | `orders.status_id` | صفحة «الطلبيات» فقط |
| **حالات التوصيل (Fulfillment/Carrier)** | `order` | `OrderWorkflow::carrier()` — shipped, in_transit, out_for_delivery, delivered, returned (سطور 144–197) | `orders.status_id` | صفحة «التتبع» فقط (التبويبان carrier وrider معًا) |
| **حالات التتبع (Tracking lifecycle)** | `tracking` | shipped, in_transit, out_for_delivery, on_hold, delivered, returned, returning, failed_attempt, cancelled, lost, damaged (سطور 424–543) | `order_trackings.tracking_status` بإرث `OrderTrackingStatusHistory` | أدوات التتبع/السحب/الرسم — **لا تُكتب أبدًا في `orders.status_id`** |

### 8.2 قواعد الفصل المثبتة في الكود

- كل قراءة/كتابة لـ `orders.status_id` تمر عبر `Status::system()->forType('order')->where('key', ...)`:
  - `app/Domains/Order/Services/OrderService.php:24` (`transition`), `:126`, `:173`؛ `app/Observers/OrderObserver.php:39` (الحالة الافتراضية `pending`).
- الانتقال إلى مجموعة «التوصيل» (شركة أو راجل) يتم بنفس السكّانة: `OrderShippingGateway::send` يكمل `['preparing','shipped']` عبر `OrderService::transition` (مؤكد: `OrderShippingGateway.php:134–143`) → أي أن كلا التبويبين يصف حالات الطلب من **نوع order**، وهذا يبرّر فلترة التبويبين بنفس `OrderWorkflow::carrier()`.
- نوع `tracking` لا يُستعمل في أي مكان ضمن `app/` سوى السطر الخاطئ (المُصلَح): `grep forType(` في `app/` أعاد **5 مواضع فقط** — 4 سليمة (OrderObserver:39، OrderService:24/126/173) والـ 5 هو `TrackingGridConcern.php:179` (سبب الانحدار). لا يوجد `forType('tracking')` في أي ملف آخر.
- `OrderDuplicateService.php:205` يستخدم مفاتيح `carrier()` عبر علاقة `status` (نوع order ضمنًا) → سليم ولا يحتاج تعديلًا.

### 8.3 مصيدة المفاتيح المكررة (جذر الخلط)

مفاتيح shipped/in_transit/out_for_delivery/delivered/returned موجودة بنسختين في جدول `statuses`:
- `type='order'`: `shipped → 01m2qngwyr4...`، `in_transit → ...yt3...`…
- `type='tracking'`: `shipped → 01m2qngwzwjf...`، `in_transit → ...zx11...`…

أي اختيار للمفاتيح **دون تحديد النوع** يجمّع النسختين أو يحشر النسخة الخاطئة (كما فعله `8e121b3`)؛ ولهذا قاعدة صارمة: **عند فلترة `orders.status_id` يجب تحديد `type='order'` صراحةً**، وعند قراءة `tracking_status` يُستخدم نوع `tracking`.

### 8.4 ما يجب عدم فعله في أي خطة لاحقة

- لا دمج/حذف صفوف `statuses` ولا تعديل `SystemStatusesSeeder` — النسختان مقصودتان (واحدة لمستوى الطلب وأخرى لمستوى التتبع)، والمشكلة كانت في **اختيار النوع** لا في البيانات.
- لا إضافة عمود جديد — `order_trackings.tracking_status` (مفاتيح tracking) + `orders.status_id` (مفاتيح order) يغطان المسارين.

---

*نهاية الخطة. أُعدّت دون أي تعديل على ملفات المصدر أو الاختبارات الحالية؛ الوحيد المُنشأ هو `FIXPLAN-tracking-empty-grid.md`.*