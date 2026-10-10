# 03 — Data Integrity & Money (تكامل البيانات والمال)

> الهدف: كل كتابة مالية تُحسب من قاعدة البيانات لا من مدخل العميل، وعمليات المخزون ذرية، وإضافة قيود فهارس.
> المصدر: `docs/audits/2026-10-readiness-audit.md` A5/A6/A14/A15/A17/A18.
> البوابات: `00-quality-gates.md` §7.

---

## M-01 🟠 P1 — لا تُبنى `total_amount` من أسعار العميل

**الملفات:**
- `app/Livewire/Concerns/TrackingRiderFormConcern.php:907-911` — يعرض `$total = sum(form price*qty)` من مدخل العميل ثم يُكتب كـ `total_amount` في `:911`. سطر `:941` لاحقًا يصحح price لكن لا يعدّل `total_amount` (يعطي تدفق «total=القيم الخاطئة»).
- `resources/views/livewire/merchant/orders/index.blade.php:4644-4648,4683,4696` — نفس النمط.

**الإصلاح:**
1. في `TrackingRiderFormConcern`: بعد تصحيح كل `price` من `$variant->price` (`:941`)، أعد الحساب: `$total = sum(corrected price*qty)` ثم اكتب `total_amount` **مرة واحدة بعد الحلقة**، داخل نفس الـ transaction (يُمدّ `DB::transaction` حول السطور 907-986).
2. العنصر نفسه في `orders/index.blade.php:4644-4696`.
3. أزل «stock return بعد كتابة الطلب»: تحقق المخزون قبل أي كتابة (`:949-955` يجري قبل الكتابة — جيد؛ تأكد أن إرجاع تُزيل لا تترك تعديل جزئي، أي نقل التحقق قبل فتح الـ transaction).
4. لا تُعتمد على `recalculateOrderShipping` لإصلاح totals (تعديل shipping فقط).

**قبول:** اختبار إضافة: إن غيّر عميل `price` فلن ينعكس على `total_amount`. إعادة تشغيل `tests/Feature/Orders/*`.

---

## M-02 🟠 P1 — سباق المخزون (TOCTOU): تأمين ذري

**الملفات:**
- `resources/views/livewire/storefront/order-form.blade.php:465-471` — يقرأ `variant->stock` ثم ينشئ الطلب.
- `app/Domains/Order/Services/OrderService.php:78-89` — `canTransition` خارج الـ transaction (TOCTOU ثانوي).

**الإصلاح:**
1. في مسار إنشاء الطلب: `ProductVariant::whereKey($id)->lockForUpdate()` قبل قراءة stock (نموذج موجود جيد في `InventoryService.php:50`).
2. إنقاص ذرّي مشروط: `where('stock','>=',$qty)->decrement('stock',$qty)` يعيد 1/0 — لا قراءة ثم كتابة.
3. أي دالة `transitionToStatus` ضمن `DB::transaction($order)` مع `lockForUpdate`.
4. أضف مفتاح idempotency على checkout (أنشئ `cart_token`/`checkout_token` في الجلسة، واحد لكل order) — يمنع الدفع المزدوج.

**قبول:** test concurrent: خيطان يطلبان آخر قطعة → واحد فقط ينجح.

---

## M-03 🟡 P2 — قيد فريد على `inventory_movements`

**الملف:** مِغراف لاحق `database/migrations/2026_01_05_125049.php` (إنشاء الجدول) — أضف مِغرافًا جديدًا:
```
unique: (source_type, source_id, product_variant_id, type)
```
**سبب:** `OrderObserver:186-195` وجود-فحص غير ذرّي → تكرار RESERVE تحت التزامن.
**قبول:** تعارض فريد عند insert مكرر يدفع إعادة المحاكاة لا خطأ غامض.

---

## M-04 🟡 P2 — `order_events` بدون FK

**الملف:** مِغراف `order_events` (ulid) — أضف في مِغراف جديد فهارس FK على `order_id` و`store_id` (أو فعلًا عكس — انظر `DATABASE_PLAN.md` هل المتعمد إبقاؤها بدون FK؟ لا تغيّر بدون مشاورات التوثيقة).
**قبول:** مستند: سبب إبقاء بدون FK صراحة في code، أو أضف FK بعد موافقة.

---

## M-05 🟡 P2 — مزامنة حالة موحدة (attribution)

**الملفات:** `app/Models/Orders/OrderObserver.php:99-206` (inventory من status)، `resources/views/livewire/merchant/orders/index.blade.php:2290-2328` (bulk status)

**الإصلاح:** جرّ كل تعديل حالة عبر `OrderService::transitionToStatus()` (نافذة: سجّل `who/when` في `order_status_histories` — بإسناد `{from_status, source}` من 38-C) — لا مباشرة إلى `status_id`.

**قبول:** test: كل nuran case عبر سجل حالة واحدة؛ `stats` لا تُكتب مباشرة.

---

## جدول مرجعية

| المهمة | الوثيقة |
|---|---|
| M-01 | `00-quality-gates` §7 + `docs/audits` A5 |
| M-02 | `00-quality-gates` §7 + `app/Domains/Cart/Support/OrderRules.php` |
| M-03 | `DATABASE_PLAN.md` (فهرس الأحداث) |
| M-04 | `DATABASE_PLAN.md` (هل زوج FK مقصود؟) |
| M-05 | `docs/plans/financial-accounts.md` (38-C attribution) |

## قبول المرحلة

- [ ] لا يُبنى total من عميل.
- [ ] مخزون ذرّي + idempotency.
- [ ] فهارس/قواعد جديدة مِغرافات منفصلة في نسخة واحدة مع الاختبارات الخضراء.