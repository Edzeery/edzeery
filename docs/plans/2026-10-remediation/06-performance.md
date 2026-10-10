# 06 — Performance (الأداء)

> الهدف: تقليل عام الأهداف على الصفحات العامة، فهارس مبتكرة، لا N+1، تحميل خامل للعناصر الثقيلة، تحسين انتقالات Large Grid.
> المصدر: `docs/audits/2026-10-readiness-audit.md` A6/A9/A19/A24/A25/P.
> البوابات: `00-quality-gates` §5.

---

## P-01 🟡 — ذاكرة مؤقتة للعدّاد العام على الصفحة الرئيسية

**الملف:** `app/Http/Controllers/Front/LandingPageController.php:24-26`
**الإصلاح:** `Cache::remember('landing.stats', 3600, fn () => [...])` على `Store::count()/Order::count()/User::count()`.
**Test:** اختبار مسار `/` مع `Cache::shouldReceive/remember`? — حالة تكامل بسيط: إزالة الكاش وإرجاعه يعمل.

---

## P-02 🟡 — القيود العامة: صفحات العريض

**الملف:** `resources/views/livewire/merchant/orders/index.blade.php` (5,707 سطر)
**التحليلات:** grid يستخدم pagination 50 (`:914`)، ويبدأ بتحميل واسع `:774-787`.
**الإصلاح (لا حسب خطة أخرى):**
- تقسيم الملف إلى `Concerns` (مثل `TrackingGridConcern`/`OrdersTableConcern`) — انظر `09-code-health` أبعاد الكود.
- تأكد أن `computed()` للـ grid لا يُعيد تلَمّّح perm في كل cell (القاعدة موجودة §12 في docs/livewire-conventions).
- استبدال `wire:key` عالية الهامش بـ دالة ضغط لـ grid.
- Pagination 50 مع `simplePaginate` إن أمكن (لا count في كل صفحة).

---

## P-03 🟡 — N+1 هل موجودة؟

قاعدة: أي Volts stores في `computed` تجرى على grid بشكل big يجب أن تبدأ بـ `->with(...)`. من المصدر الحالي `TrackingDrawerConcern.php:26,43,142...` يستخدم with بشكل جيد، و`HasOrderProductPicker.php:29,107` with. أضف إلى الـ checklist راجع:
- `app/Domains/Analytics/Services/StoreDashboardAnalyticsService.php` — vime أي حلقة تستدعي `->count()` لكل نطاق (تحقق).
- التقارير `ReportsShiftCoverage.php:61` — with مستخدم.

**قبول:** `select N+1` بالـ debugbar على صفحات القائمة الرئيسية تنخفض إلى 0.

---

## P-04 🟡 — فهارس لأهم الأعمدة المركّبة

من ملخص التدقيق:
- `order_status_histories` : `(order_id)` فقط → أضف فهرس مركّب لـ `(order_id, from_status)` أو `(order_id, created_at)`.
- `order_trackings` : **الاستعلام الرئيسي لحزم التتبع** في `TrackingGridConcern` حسب (`store_id,created_at`) عدة صفحات — أضف فهرس `(store_id, created_at)`.
- `inventory_movements` : فهرس فريد `(source_type, source_id, product_variant_id, type)` (انظر M-03) + فهرس `(store_id, created_at)` للـ type های پلیتها.
- تأكد قبل الإضافة مدى احتياج expr: أضف مِغرافًا واحدًا لكل شهر النمط (مغلف في مِغراف منفصل بترتيب).

---

## P-05 🟡 — تحميل خامل/تقسيم أصول CSS/JS

- `storefront.scss` هو bootstrap SCSS كامل — تأكد أن `npm run build` يبني bundle وينفع تصفير كود heavy؟ وثّق:
  - أثر CSS `unused` (فحص حجم مبنى storefront.scss).
  - تأجيل script لـ Ionicons/web component؟ (انظر `DESIGN_SYSTEM.md:34` أيقونات) — أي تحسين يتطلب فحص build قبل/بعد القياس.

---

## P-06 🟡 — Lazy/hotpath للصور

- صور المنتجات عبر `asset('storage/'.$path)` — لا lazy؛ على بطاقات الكتالوج أضف `loading="lazy"` + `decoding="async"` في القوالب عديدة (طالع `templates/catalog.blade.php`).
- `<img>` بلا alt (28/66) — انظر `07` §a11y — نفس الملفات.

---

## P-07 🟡 — dashboard/modals تخطيط

- `app/Domains/Analytics/Services/StoreDashboardAnalyticsService.php` — راجع إذا كان يُعاد حساب range 30 يوم في كل request (يُخزّن cached لكل store+range).
- modals (orders ops) loading gate: أي نوع من `openDrawer` يعمل livewire load — تأكد أن `wire:loading` لا يبيّن بطاقة فارغة بثوانٍ (استخدام skeleton component موجود `_skeleton.scss`).

---

## مرجعية

| المهمة | الوثيقة |
|---|---|
| P-01..P-07 | `00-quality-gates` §5 + `docs/livewire-conventions.md` (computed/with) + `docs/audits/...` A25/P |

## قبول المرحلة (METRIC)

مقياس الأداء بعد كل P-x (وادخل في STATUS):
- TTFB المتوسط لمنظّر:
  - land. المحلي: `< 120ms` (Time to First Byte)
  - catalog storefront: `< 300ms`
  - orders grid (10k order dataset): `< 1.5s` first paint

يُقاس عبر Laravel Telescope/clock? — إن لم تتوفر أداة فلاحق أعد القياس عند نشر benchmark دراسي.