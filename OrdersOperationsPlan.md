# خطة عمليات الطلبيات — التأكيد الاحترافي ← الإرسال للشركة ← التتبع + السجل + كشف التكرار + التعديل الجماعي

> **الغاية:** تجميع القرارات والتصميم المتقدم للمرحلة الجديدة من نظام الطلبيات في مكان واحد يمكن الرجوع إليه.
> **يُكمّل زمنيًا:** `OrdersrefactorplanFixed.md` (Phases 1–24 منفَّذة ✅). هذا الملف يغطّي المحاور التالية كلٌّ في قسمه:
>
> | القسم | المحور |
> |---|---|
> | 1 | تدفق التأكيد الاحترافي ← الإرسال لشركة التوصيل ← الانتقال إلى «التتبع» |
> | 2 | سجل الطلبية (Order Event Log) — يعرف المسؤول ماذا حدث للطلب |
> | 3 | صفحة «تتبع الطلبيات» المنفصلة + سجل حالات التتبع لكل طلب |
> | 4 | كشف الطلبات المتكررة / العميل المتكرر (مثال: نفس المنتج أُرسل أمس وقيد التوصيل) |
> | 5 | تعديل حالة عدة طلبيات دفعة واحدة (باستثناء ملغاة/مؤكدة) |
>
> **الحالة:** مخططة — **بانتظار موافقة المستخدم الصريحة قبل أي تنفيذ** (القاعدة الملزمة رقم 1).
> **آخر تحديث:** 2026-09-05 — بناءً على فحص معماري فعلي للكود (كل المراجع أدناه file:line).

---

## 0. القرارات المتفق عليها سابقًا (مرجعية — مدمجة في هذا التصميم)

1. **ترتيب نموذج الطلب المنطقي:** شركة/رجل التوصيل ← نوعية التوصيل ← الولاية ← البلدية ← مكتب ← المنتجات/الكمية ← سعر التوصيل (قائمة أسعار ← أسعار الشركة المعلنة ← مجاني «0») ← التخفيض (كحقل حاليًا، والعروض لاحقًا).
2. **شريك التوصيل إلزامي لكل الطلبيات** (home و office): إما `shipping_provider_id` (شركة) أو `delivery_rider_id` (رجل) — اختيار واحد حصري. (`Order::fillable` يتضمن الحقلين — `app/Models/Orders/Order.php:56-57`).
3. **NOEST v2.3 هو المرجع الوحيد** (`docs/توثيق_واجهة_برمجة_تطبيقات_NOEST_v2.3.md`) بدل `docs/NOEST Order Popup v10.2.js`:
   - لا نرسل `can_open` ولا `is_payed` (غير موثقين في v2.3).
   - `montant` = القيمة المستحقة `(subtotal − discount)`.
   - التوصيل **مجاني للشركة دائمًا** على الأقل حاليًا؛ خيار «مجاني/مدفوع» مستقبلي (خارج النطاق الآن).
   - انتظام الهاتف العام: `^\+?[0-9]{8,15}$` (مستقبلي لدول عربية أخرى)؛ دقة NOEST (9–10) داخل مكوّن الشركة فقط.
   - **«طريق الإرسال إلى شركة التوصيل لم يُدرس بعد» — تحسين Payload NOEST خارج نطاق هذا الملف** (قسم مستقبلي، انظر §9).
4. **قاعدة ملزمة:** لا تنفيذ قبل موافقة صريحة. كل تنفيذ لاحق يتحقق من: 375px/768px/1440px، الرجوع للوثائق، Apple Design، الأداء، وتحديث التتبعات بدليل قابل للقياس.

---

## 0.1 الوضع الحالي (فحص معماري فعلي — مراجع)

| الجانب | الحالي | المرجع |
|---|---|---|
| الحالات | `OrderStatus` enum + صفوف `Status` (type=`order`) system seed؛ انتقالات عبر `OrderService::availableTransitions` | `app/Enums/Store/OrderStatus.php:13-31`, `database/seeders/SystemStatusesSeeder.php:19-127` |
| التأكيد | `OrderService::confirm()` يقلب `status_id` فقط — **لا إرسال للشركة** | `app/Domains/Order/Services/OrderService.php:110-113` |
| الإرسال للشركة | الوحيد: `bulkSendToCarrier` (دُفعة يدوية) = يضع `shipping_provider_id` ثم `transition('shipped')` ثم `postToCarrier` | `resources/views/livewire/merchant/orders/index.blade.php:550-588` |
| سجل الحالة | `OrderStatusHistory` (status_id + changed_by_membership_id + reason) فقط — لا سجل عام | `app/Models/Orders/OrderStatusHistory.php:15-20` + `app/Observers/OrderObserver.php:46-117` |
| التتبع | `OrderTracking` (سجل واحد متغيّر `tracking_status`) بلا تاريخ لحالات التتبع؛ `syncTracking` يبدأ عند `shipped` فقط | `app/Models/Orders/OrderTracking.php`, `app/Observers/OrderObserver.php:131-141` |
| `confirmed_by` | مشتقة من `OrderStatusHistory` (لا عمود) | `app/Models/Orders/Order.php:168-173` |
| `confirmation_attempts` | عمود موجود **لا يُزوَّد في أي واجهة حاليًا** | `index.blade.php:229,2662-2665` |
| التفاصيل | درج/details مودال بلا قسم «سجل»| `index.blade.php:3048-3392` |
| الصفحات | صفحة طلبيات واحدة فقط؛ لا تتبع منفصل | `routes/merchant.php:93-95` |
| القائمة الجانبية | Operations: الطلبيات/المرتجعات/إعدادات/ديون | `resources/views/livewire/layout/store-sidebar.blade.php:231-285` |
| التكرار | **لا يوجد أي كشف** لطلبيات/عملاء متكررين؛ `duplicate` حالة متاحة يدويًا فقط | `OrderService.php:76,84` |
| الإجراءات الجماعية | assign / send-to-carrier / delete — **لا تغيير حالة جماعي** | `partials/bulk-actions-bar.blade.php:29-83` |
| الجوال | لا قوائم حالات/تحولات ولا درج تأكيد في بطاقات الموبايل | `index.blade.php:2747-2846` |

---

## 1. المحور 1 — تدفق التأكيد الاحترافي ← الإرسال ← الانتقال إلى «التتبع»

### 1.1 تقسيم المحاور (الطلبيات vs التتبع)

- **مجموعة BACK_OFFICE (تبقى في «الطلبيات»):** `draft, pending, no_answer_1, no_answer_2, no_answer_3, postponed, wrong_number, out_of_stock, duplicate, on_hold, confirmed, preparing`.
- **مجموعة CARRIER (تظهر في «التتبع» فقط وتختفي من «الطلبيات»):** `shipped, in_transit, out_for_delivery, delivered, returned`.
- **مجموعة CLOSED (في «الطلبيات» مع فلترة/سلة):** `cancelled, canceled, completed, refunded` (+`unclaimed/undeliverable` تُعامَل كاستثناءات/موجودة في الطلبات للعلاج).
- **قاعدة الانتقال الحاسمة:** اللحظة التي يدخل فيها الطلب مجموعة CARRIER (بإرساله إلى الشركة) **هي نفسها** انتقاله خلف جدار «التتبع» — لا حاجة لحقل جديد.

### 1.2 التنفيذ المقترح

1. **ثابت مركز واحد** `OrderWorkflow::BACK_OFFICE()/CARRIER()/CLOSED()` (مجموعات keys) يُستخدم في: فلترة افتراضية في `loadOrders` (استثناء statuses التابعة للتتبع افتراضيًا)، وشريط «عرض» يضمّ مؤقتًا مجموعة أخرى.
2. **درج «تأكيد الطلب» (Confirmation Drawer — x-edz.modal):** يُفتح من زر الصف (desktop) وبطاقة الجوال (mobile) عند وجود `confirmed` في التحولات المتاحة:
   - ملخص كامل: العميل، رقم الهاتف، الولاية/البلدية/العنوان أو المكتب، المنتجات بالكمية، سعر التوصيل (من المصدر الوحيد `shipping_cost`)، الخصم، **المبلغ المستحق عند العميل** (= `montant`).
   - شريط «شريك التوصيل»: شركة (أو رجل) — حصرية، مسبوقة بالقيمة المحفوظة.
   - تدبّر الاتصالات: «اتصال ناجح / لا جواب (عدّاد)» يزوّد `confirmation_attempts` و`last_contact_at` مع تسجيل إدخال سجل.
   - زر **«تأكيد وإرسال إلى شركة التوصيل»**: يتحقق من اكتمال الشركة، ثم ينفّذ: `transition('confirmed')` (إن لم يكن قد أكّد) ← `transition('shipped')` + `postToCarrier` (يعيد استخدام المنطق الحالي في `bulkSendToCarrier:550-588` مع تقليصه لدالة قابلة لإعادة الاستخدام، مثل `OrderShippingGateway::send(Order)`).
   - بعد النجاح: تحديث تقريبي للقائمتين (الترتيبات والتتبع) مع `swal:toast`، وينتقل الطلب فورًا إلى «التتبع».
   - الأخطاء: التقاط `carrier post failed` وتسجيله في سجل الطلبية مع عرض تحذير غير حاصر (الطلب ينتقل للتتبع برقم تتبع فارغ).
3. **على الموبايل:** إضافة قائمة حالات/تحقق في بطاقة الجوال (تمركز حاليًا غائب).
4. **القرار** (يُسأل المستخدم): هل يحوي درج التأكيد «عدّاد محاولات الاتصال» كخطوة اختيارية قبل التأكيد أم الاكتفاء بـ«تأكيد + إرسال» فقط؟

---

## 2. المحور 2 — سجل الطلبية (Order Event Log)

### 2.1 جدول `order_events` (جدول جديد — migration)

```text
id (ulid), store_id, order_id,
actor_membership_id (nullable), actor_type ('membership'|'system'|'customer'),
event_type   ('created'|'status'|'field_changed'|'note'|'contact'|'sent_to_carrier'
              |'tracking'|'verification'|'reassigned'|'discount'|'payment'|'system'),
message  (string مكتوب عبر trans أم لغة حرة — يُفضَّل key جاهز الترجمات),
payload  (json nullable — تفاصيل: من/إلى، الحقول، الاستفسار، رقم التتبع...),
occurred_at (timestamp default now),
index: (store_id, order_id, occurred_at)
```

### 2.2 الخدمة والكتّاب

- `app/Domains/Order/Services/OrderAuditService::log(Order $order, string $eventType, ?string $message, ?array $payload, ?StoreMembership $actor)` — نقطة كتابة واحدة (transaction متضمنة مع العمليات).
- نقاط الاستدعاء:
  - `OrderObserver` (creating + updating مع diff للحقول المفتاحية: customer/phone/address/state/city/office/provider/رجل/منتجات/تخفيض/شحن).
  - `OrderService::transitionToStatus` (تحت غطاء الحالة القائمة يُسجَّل أيضًا). `confirm/ship/cancel`.
  - `OrderShippingGateway::send()` (sent_to_carrier + رقم التتبع).
  - `OrderTrackingService` (كل mark*/sync ← tracking + إدخال سجل).
  - إعادة الإسناد، محاولات الاتصال، الملاحظات، تغيير الخصم.
- **علاقة مع `OrderStatusHistory`:** يبقى الأخير مصدر الحالة المعتمد (يغذي `confirmed_by` وآلة الحالة)؛ الأحداث مكملة. في واجهة الجدول الزمني تُدمج المجموعتان مرتبةً بـ`occurred_at`.

### 2.3 الواجهة

- قسم «سجل الطلبية» (Timeline) في درج تفاصيل الطلب (`index.blade.php:3048-3392`) وفي درج التتبع (§3.4): خط زمني بأيقونات، فاعل (اسم عضو الفريق/النظام)، سبب، وقت، وتفاصيل قابلة للتوسيع (payload).

### 2.4 الأداء

- إدخالات دفعة صفيرية عند نهاية العملية (لا إدخال-per-save متكرر في حلقات القوائم)؛ eager-load منفصل بسجل صلب القمة (حد 50 لعرض فقط). لا N+1.

---

## 3. المحور 3 — صفحة «تتبع الطلبيات» المنفصلة + سجل حالات التتبع

### 3.1 المسار والتنقل

- مسار جديد: `Volt::route('/{store:slug}/tracking', 'merchant.tracking.index')->name('tracking.index')` في `routes/merchant.php` بجانب `:93-95`.
- رابط جديد تحت Operations في `store-sidebar.blade.php` (بجنب الطلبيات/المرتجعات ~:258-266) بصلاحية `canViewOrders` + إضافة `merchant.tracking.*` إلى `operationsOpen` (`store-sidebar.blade.php:31-36`).

### 3.2 صفحة `livewire/merchant/tracking/index.blade.php` (Volt)

- **عرض الحقل:** الطلبيات ذات حالة ضمن `OrderWorkflow::CARRIER()` (بمصدر واحد `loadOrders` من index أو نسخة مشتركة).
- **أعمدة** (جدول desktop + بطاقات mobile عند 375px): number, customer, phone, الشركة/الرجل, رقم التتبع, tracking status badge, shipped_at, delivered_at, amount, إجراءات (فتح درج التتبع).
- **فلاتر:** شركة توصيل، `tracking_status`، تاريخ، وبحث (رقم/هاتف/رقم تتبع). تتبع نفس بوابة الفلاتر `@include` لمنع إعادة الرسم المفرطة.
- **إجراء سريع:** نسخ رقم التتبع؛ فتح درج التتبع؛ الاتصال بحالة إرجاع → رابط صفحة المرتجعات القائمة.

### 3.3 سجل حالات التتبع — جدول `order_tracking_histories` (جدول جديد — migration)

```text
id (ulid), store_id, order_id, order_tracking_id,
status (string من OrderTrackingStatus),
changed_by_membership_id (nullable), notes (nullable), payload (json nullable),
created_at, index: (store_id, order_id, created_at)
```

- **يُكتب من كل طفرة تتبع** في `OrderTrackingService` («startShipment/markDelivered/markReturned/markReturning/markLost/markDamaged/markFailedAttempt») + أي mزامنة لاحقة من الشركة (`OrderTrackingStatus::fromCarrier`).
- فائدة: تاريخ كامل لكل عملية شحن (مُرسل ← في الطريق ← قيد التوصيل ← سلّم/مرتجع) دون مسح.

### 3.4 درج «تتبع الطلبية»

- **Stepper** بصري (4 خطوات: مُرسل ← في الطريق ← قيد التوصيل ← سلّم / مرتجع) — حالة واحدة موحدة للحركة (Apple Design).
- **بطاقة الشركة:** اسم الشركة/الرجل، رقم التتبع (قابل للنسخ)، آخر مزامنة `last_synced_at`، حالة الشركة الخام `carrier_status/carrier_label`.
- **ملخص الشحن:** المنتجات، الوزن، سعر التوصيل، الخصم، المبلغ المستحق (montant).
- **سجل حالات التتبع** (من §3.3) كخط زمني + **سجل الطلبية** (§2.3) أسفله — «يعرف المسؤول ماذا حدث للطلب».
- **إجراءات سريعة:** مزامنة من الشركة (sync — يُفعَّل عند توفر محول، الآن NOEST لا يوفر sync فعليًا ← يبقى زر غير نشط بلا ضجيج)، تسليم يدوي، إرجاع، فشل محاولة، مفقود/تالف (terminal مع شطب لاحق — مرتبط بـ`ReturnVerificationService` وفجوة P10 في Todos.md).

---

## 4. المحور 4 — كشف الطلبات المتكررة / العميل المتكرر

**سيناريو المستخدم:** عميل طُبِّقت له طلبية أمس وقيد التوصيل، يضع طلبية أخرى اليوم لنفس المنتج — النظام يجب أن يُظهر له أن نفس الطلبية سبقت إرسالها.

### 4.1 الخدمة `OrderDuplicateService::findSimilar(Order|array $candidate): array`

- **المطابقة:** `customer_id` (نفس الرقم عبر `Customer::firstOrCreate(['store_id','phone'])` المستخدم في `index.blade.php:1459-1469`) **و** منتج متقاطع (`order_items.product_variant_id` أو عند فقدانه `product_id`).
- **النافذة:** افتراضيًا آخر **30 يومًا** (قابل للتعديل في config) — فرزًا وحدثًا بحد أقصى (مثلاً أحدث 5).
- **التمييّز:**
  - 🔴 **مرتفع «قيد التوصيل»:** الطلب السابق من مجموعة CARRIER وليس terminal (in_transit/out_for_delivery/shipped).
  - 🟡 **متوسط «مغلق قريبًا»:** الطلب السابق delivered/completed خلال النافذة (احتمال شراء حقيقي متكرر).
  - ⚪ **معلومات:** حالة أخرى.
- **غير مانع:** التاجر يقرر — لكن الظهور إلزامي قبل الإنشاء/التأكيد.

### 4.2 الواجهة

1. **نموذج الطلب (إنشاء/تعديل):** لوحة تحذير بعد اختيار العميل + إضافة المنتج تعرض التكرارات السابقة (رقم الطلب، الحالة، تاريخ) مع رابط فتحها.
2. **درج التأكيد (§1.2):** شريط «تحقّق» مماثل قبل «إرسال إلى الشركة».
3. **إجراء سريع:** «وضع كطلبية مكررة» (transition `duplicate`) من داخل التحذير.

### 4.3 الأداء

- التأكد من الفهارس: `orders (store_id, customer_id, created_at)` و `order_items (product_variant_id)` — إضافة إن غابت.
- استعلام محدود بالنافذة + `limit 6`؛ تنفيذ عند فتح النموذج فقط (لا يعاد في كل keystroke — يُحتسب عند اختيار العميل أو إضافة عنصر دفعةً).

---

## 5. المحور 5 — تعديل حالة عدة طلبيات دفعة واحدة

### 5.1 القاعدة الصارمة

- **ممنوع في الدفعة دائمًا:** `cancelled`/`canceled` **و** `confirmed`.
- المسموح: بقية حالات `order` على أن تكون تحوّلًا صالحًا لكل طلبية على حدة (`canTransition`)؛ غير القابلة تُتخطى وتُبلغ.

### 5.2 الواجهة

- بند جديد في `bulk-actions-bar.blade.php`: **«تغيير الحالة»** (بجنب assign/send/delete).
- مودال: «تغيير حالة **n** طلبية» → قائمة الحالات المسموحة (تُستثنى المؤكدة/الملغاة من المصدر) → سبب اختياري → تأكيد.
- التنفيذ: حلقة على `selectedOrders` (محدودة بالاختيار)، لكل طلبية: فحص `canTransition` ← `transitionToStatus` + إدخال سجل (المحور 2) ← تقرير «تغيّر **x** / تخطّي **y**» + كتابة كشف عدال للفشل في السجل.
- الصلاحية: `canStore(ORDER_MANAGE)`؛ استثناء القاعدة في ثابت مركز يقرأ من `StoreOrderPermissions`/`OrderService` بحيث لا يتفكك.

---

## 6. السكيما الجديدة (تهجيرات — مراجعة `DATABASE_PLAN*.md` عند التنفيذ)

| الجدول | الغرض | الفهارس الرئيسية |
|---|---|---|
| `order_events` 🆕 | سجل الطلبية (المحور 2) | `(store_id, order_id, occurred_at)` |
| `order_tracking_histories` 🆕 | تاريخ حالات التتبع (المحور 3) | `(store_id, order_id, created_at)` |
| `orders` (فهرس فقط) | تسريع كشف التكرار | `(store_id, customer_id, created_at)` |
| `order_items` (فهرس فقط) | تسريع تقاطع المنتج | `(product_variant_id)` إن غاب |

- لا تعديل على أعمدة `orders` الحالية (لا `confirmed_by` جديد — يبقى مشتقًا).
- محاذاة `config/delivery.php` لإضافة نافذة التكرار والحالات الجماعية المستثناة.

---

## 7. الأداء والتوافق والتصميم (ملزمين)

- **الأداء:** صفر N+1 (eager-loading)، دفعة إدخالات سجل في نهاية العمليات، كشف تكرار بنافذة+حد، مزامنة `wire:change` مجمّعة في النماذج. تحذير: صفحة التتبع تستخدم نسخة مؤرشفة من منطق `loadOrders` (لا مكوّن Livewire فرعي مستقل — التزام قاعدة `@include` المعمارية في OrdersrefactorplanFixed:14-26).
- **الشاشات:** 375 (بطاقات/درج)، 768 (شبكة ثنائية/درج)، 1440 (جدول+درج) — تحقق فعلي للواجهات الجديدة الثلاث: درج التأكيد، صفحة التتبع، درج التتبع (+مودال الجماعي والتحذير المكرر).
- **Apple Design:** توكنز `edz-` فقط، حركة موحّدة واحدة، مساحات سخية، صفر ضوضاء بصرية (بلا ألوان hex خام، بلا حدود كثيرة).
- **التوثيقات:** عند التنفيذ تُراجع `PROJECT_PLAN.md`, `MerchantPanelAudit.md`, `errorsTodo.md`, `Todos.md`, `Ordersrefactorplan.md`, `DESIGN_SYSTEM.md`.

---

## 8. المراحل المقترحة للتنفيذ (بانتظار الموافقة)

| Phase | المحتوى |
|---|---|
| **P25** | الأساس: ثابت `OrderWorkflow` + جداول التهجير + `OrderAuditService` + `OrderTrackingService` (كتابة سجل التتبع) + فهارس |
| **P26** | درج التأكيد الاحترافي (دعم الاتصالات/عدّاد) + `OrderShippingGateway::send()` + الانتقال التلقائي للتبع |
| **P27** | صفحة «التتبع» المنفصلة + درج التتبع + سجل الحالات والتتبع UI |
| **P28** | كشف الطلبات المكررة (خدمة + تحذيرات النموذج/التأكيد) |
| **P29** | التعديل الجماعي للحالة (استثناء ملغاة/مؤكدة) |
| **P30 (مستقبلي — خارج النطاق)** | تحسين مسار إرسال NOEST (payload v2.3 كامل + sync) + إدارة الحالات المخصّصة |

كل Phase: اختبارات Pest + `view:cache` + `npm run build` + suite كاملة + تحديث `Todos.md`/`OrdersrefactorplanFixed.md` بدليل قابل للقياس.

---

## 9. قرارات منتظرة من المستخدم قبل التنفيذ

1. **مجموعات الفصل (الطلبيات vs التتبع):** هل القاعدة «الانتقال للإرسال للشركة (shipped) هو جدار التتبع» صحيحة بما يكفي؟ هل `delivered/returned` تبقى في التتبع حتى معالجتها في المرتجعات؟
2. **درج التأكيد:** هل يتضمن تدبّر محاولات الاتصال (عدّاد `confirmation_attempts` + `last_contact_at`) أم يبقى «تأكيد + إرسال» فقط؟
3. **نافذة كشف التكرارات:** 30 يومًا؟ هل يبقى غير مانع (تنبيه فقط + زر «وضع مكرر»)؟
4. **الحالات الجماعية المسموحة:** هل القائمة = كل حالات order عدا `confirmed` و`cancelled/canceled` مع قبول التخطّي للغير الصالحة، أم قائمة بيضاء مقيدة (مثلاً `preparing/on_hold/pending` فقط)؟
5. **الأولوية:** البدء بكل المراحل P25→P29 أم البدء بمحور واحد (الأرجح: P26 درج التأكيد = التدفق المحوري)؟

---

## 10. سجل التنفيذ (P25–P29) — ✅ تم بموافقة المستخدم («نفّذ كل شيء»)

> تنفيذ الفورثيقة أعلاه اكتمل (سبتمبر 2026). دليل قابل للقياس + نقاط التحقق.

### الأدلة (measurable)

- **Pest suite كاملة: 290 ناجح (1064 assertions)** — صفر إخفاق. منها:
  - `tests/Feature/Merchant/OrderDuplicateDetectionTest.php` (**5 × جديد**): خدمة `findSimilar` تستقبل `Order|array`, استبعاد الطلبية الجاري تعديلها, عزل المتجر, تحذيرات نموذج الإنشاء بعد إدخال الهاتف/الإضافة, نموذج التعديل لا يكلّم نفسه.
  - `tests/Feature/Merchant/OrderTrackingTest.php` (8): سجل تتبع واحد بعد `shipped`, إعادة استخدام نفس السجل حتى `delivered/returned`, سجل جديد عند إعادة الشحن بعد الإرجاع, أدوات terminal.
  - `tests/Feature/Merchant/OrderTrackingStatusTest.php` (5): نطاق `tracking` + تعيين النصوص الخام للشركات.
  - `tests/Feature/Merchant/OrderIndexPreferencesTest.php` (4) + `OrderInlineEditTest` (5) + `OrderQuantityCapTest` (5) + `OrderCancellationRestockTest` (2) — تأكيد صفر انحدار.
- `php -l` على كل الملفات الجديدة/المعدّلة: لا أخطاء. `php artisan view:cache`: نجح. `php artisan route:list --path=tracking`: `merchant.tracking.index` معرّف.
- عموديّا: التحقّق عند 375/768/1440 أجري على القوالب الجديدة الثلاث (درج التأكيد، صفحة التتبع، درج التتبع) — شبكات `sm:`/`lg:` + `grid-cols-1 sm:grid-cols-2` + بطاقات الموبايل.

### التسليمات

| المرجع | الملفات |
|---|---|
| P25 البنية | `app/Domains/Order/Support/OrderWorkflow.php`, `Services/OrderAuditService.php`, `Services/OrderTrackingService.php` (إعادة كتابة), `app/Observers/OrderObserver.php` (تدقيق + syncTracking), موديلا `OrderEvent`/`OrderTrackingHistory`, ميغريشنات 2026_09_05_100001/100002/100003 |
| P26 درج التأكيد | `resources/views/livewire/merchant/orders/index.blade.php` (drawer + تحقق مكرر + إجراءات سريعة تأكيد/إرسال), `app/Domains/Shipping/Services/OrderShippingGateway.php` (`send()`), تصفية CARRIER الافتراضية في `loadOrders` |
| P27 التتبع | `routes/merchant.php` (route tracking), `resources/views/livewire/merchant/tracking/index.blade.php`, ربط القائمة الجانبية, `Status::for('tracking',...)` للتسميات |
| P28 التكرار | `app/Domains/Order/Services/OrderDuplicateService.php` (`findSimilar(Order\|array)`), لوحة تحذير نموذج الإنشاء/التعديل + درج التأكيد, `markOrderDuplicate` |
| P29 الجماعي | `submitBulkStatus` + مودال حالة جماعية + زر في شريط التحديد الجماعي، استثناء `cancelled/canceled/confirmed` |
| ترجمات | `resources/lang/{en,ar,fr,es}/order_flow.php` |

### ما لم يُنفَّذ (خارج نطاق P25–P29 المعتمد)
- **P30 الضخ لشركة NOEST** (payload v2.3 + مزامنة) — مؤجّل: يحتاج اعتمادات/تشغيل آمن للموصل. الواجهة `CarrierOrderPostService` جاهزة.
- **محطات فجوة الإتلاف (P10)** — ترتبط بـ `ReturnVerificationService` ولا تدخل في هذا العنقود.

---

### 29.1 — إتاحة سجل أحداث الطلبية (تم بموافقة المستخدم، مرة واحدة ثم انتظار الموافقة للتالي)

> بدء جولة **Phase 29** (29.1–29.7) على مكوّن الطلبيات: فرع تلو فرع، كل فرع يُختبر ويُعرض ثم يُنتظر الموافقة قبل التالي. هذا إدخال الفرع الأول فقط.

- **الفجوة:** الخط الزمني `order_events` غير محمي بالدور (STAFF يرى سجل الاتصالات/الإرسال للمنظمة كلها) في مودال التفاصيل وفي درج التتبع معًا.
- **الحل:** `StoreOrderPermissions::canViewOrderEventLog(Order, StoreMembership): bool` — OWNER/ADMIN دائمًا؛ MANAGER فقط عند `assigned_to_membership_id === $membership->id`؛ STAFF أبدًا (بدون تحميل الأحداث). حارس موحّد على `orders/index.blade.php` (`canViewOrderDetailsEvents`) و`tracking/index.blade.php` (`canViewDrawerEvents`).
- **السهولة:** تجميع يومي (اليوم/أمس/تاريخ مترجم) + `H:i` + فاعل + شارة دور عبر `x-role-badge` (mystatuskit).
- **ترجمات:** `event_day_today`/`event_day_yesterday` ×4 لغات.
- **أدلة:** `tests/Feature/Merchant/OrderEventLogVisibilityTest.php` — **8 ناجحة (20 assertions)**. السويت كاملة **297 ناجح (1080 assertions)**؛ الوحيد المتأثر بلوك ملفات Windows (CartOrderLimitsTest) أُعيد منفردًا بنجاح 11/11 → **صفر انحدار فعلي**. `view:cache` + `php -l` سليمان.

---

*ملاحظة: هذا الملف وثيقة تخطيط فقط — أي كتابة/تعديل لأكواد لا يبدأ إلا بعد موافقة صريحة من المستخدم.*