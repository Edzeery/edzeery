# إحصائيات التوصيل + نقل مؤشرات «المؤشرات» + فلاتر فريق التتبّع

**PHASE 37-N7** — خطة تفصيلية.

---

## 0) نطاق الخطة
تُغطّي هذه المرحلة أربعة مسارات في لوحة التاجر (`resources/views/livewire/merchant/dashboard.blade.php`):

1. **نقل** كتلتي «الطلبات حسب الولاية» و«توزيع طرق التوصيل» من عرض **التوصيل** إلى عرض **المؤشرات (Top)**.
2. **تطوير إحصائيات التوصيل**: معدّل توصيل (كلي/صافي) + معدّل إرجاع، وفلاتر أساس التاريخ والمنتج، على نمط إحصائيات التأكيد.
3. **تحسين سكشن فريق التتبّع** (بطاقات أداء الفريق في عرض التوصيل): بحث موسّط + زر تصفية (dropdown) + cohort التأكيد + سلة المهملات، وتصميم عمودي مطابق لبطاقات التأكيد.
4. **تسمية التابات**: التأكيد / التوصيل / المؤشرات.

**خارج النطاق:** أي تغيير في آلة التأكيد نفسها (`ConfirmationRate`/`ConfirmationStatsReport`)، وأي عمل مالي/رواتب، وجداول ledger جديدة.

**بلا كود في هذه الوثيقة**؛ هذه الوثيقة هي المخرَج المطلوب للمراجعة والموافقة.

---

## 1) القرارات المثبّتة (Locked)
| # | القرار |
|---|---|
| **D1** | أساس «آخر حالة» في التوصيل يقرأ `orders.last_status_changed_at` (يُختم عند الإنشاء ومع كل انتقال، `OrderObserver`)، مع نفس fallback إلى `created_at` للصفوف غير المختمة الذي يستخدمه أساس التأكيد. |
| **D2** | **الراجعة** = تتبّع فيه `order_trackings.returned_at` مضبوط و`processed_at` فارغ؛ **المرجعة** = `processed_at` مضبوط. تُحسب صفّة واحدة لكل طلب (أحدث تتبّع) لمنع العد المزدوج. |
| **D3** | **معادلات معدّل التوصيل** (تطابق شكل `ConfirmationRate`):<br>• كلي (`basis=created`): `نسبة التوصيل = delivered×100 / كل الطلبات`، `نسبة الإرجاع = returned×100 / كل الطلبات`.<br>• صافي (`basis=last_status`): `نسبة التوصيل = delivered×100 / (delivered+returned)`، `نسبة الإرجاع = returned×100 / (delivered+returned)`.<br>`delivered` = الحالة `delivered`؛ `returned` = الحالة `returned`. |
| **D4** | **استثناءات معدّل التوصيل إعداد مستقل بقفل لمرة واحدة**: عمودان جديدان `delivery_excluded_status_keys` + `delivery_exclusions_locked_at`، والحماية في `StoreSetting::booted()` كما في استثناءات التأكيد. تُحذف مفاتيحها من كل حدود المعدّل. |
| **D5** | عرض **المؤشرات** هو المالك الوحيد لكتلتي «الطلبات حسب الولاية» و«توزيع طرق التوصيل»؛ عرض التوصيل لا يعرضهما. الحجب ينتقل من `$canStatsDelivery && $isDeliveryView` إلى `$canTopKpis && $isTopView`. |
| **D6** | **بطاقتا المعدّل** (نسبة التوصيل + نسبة الإرجاع) تظهران في صفّ KPI أعلى عرض التوصيل (نمط `kpi-top`)، وتتبعان أساس التاريخ النشط. |
| **D7** | **تسمية التابات** (أربع اللغات): `tab_confirmation = إحصائيات التأكيد`، `tab_delivery = إحصائيات التوصيل`، `tab_top = أفضل المؤشرات`. |
| **D8** | **فلاتر فريق التتبّع**: زر تصفية (dropdown) يضمّ اختيار cohort «الكل / مع فريق التأكيد / بلا فريق التأكيد» + مفتاح «مع/بلا سلة المهملات». «فريق التأكيد» = cohort التأكيد المعرّف في `DashboardMemberCohort` (حاملو `ORDER_CONFIRM` أو الإدارة)، و«سلة المهملات» يوسّع إلى الأعضاء المحذوفين كـ تأكيد (D7). |
| **D9** | **تصميم بطاقات الفريق** في التوصيل يصبح عمودياً مطابقاً لبطاقات التأكيد (`confirmation-team.blade.php`): رأس موسّط (حرف/صورة + الاسم + الفترة)، رقم كبير، ثم صفوف الحالات، ونسبة التوصيل في الأسفل. |
| **D10** | **لا تكرار للحساب**: كل رقم توصيلي في العرض يأتي من مسح واحد (`DeliveryStatsQuery` → `DeliveryStatsReport`) كما في التأكيد، فلا تتعارض البطاقة مع صفّ الفريق. |

---

## 2) الواقع الحالي ونقاط الاحتكاك
| # | الواقع | الدليل |
|---|---|---|
| C1 | المصنع يُجبر التوصيل على `dateBasis=created` ويُسقط `productIds/variantIds` | `DashboardFilterFactory.php:67-70` |
| C2 | نافذة `last_status` تقرأ دائماً `confirmation_status_changed_at` (غير صحيحة للتوصيل) | `DashboardOrderScope.php:67-79` |
| C3 | كتلتا الولاية/طريقة التوصيل محجوبتان في عرض التوصيل | `dashboard.blade.php:147-148, 191-245` |
| C4 | فريق التوصيل بلا بحث/تصفية، وبطاقاته ليست على نمط التأكيد | `team-performance.blade.php` (كامل) |
| C5 | لا نموذج معدّل توصيل موحّد ولا استثناءات مستقلة | `ConfirmationRate.php` (نظير مفقود للتوصيل) |
| C6 | `teamTrash` يُمرَّر لتأكيد فقط | `dashboard.blade.php:59-63` |

---

## 3) خريطة الملفات
### باكند (جديد)
- `app/Domains/Analytics/Support/DeliveryRate.php`
- `app/Domains/Analytics/Support/DeliveryStatsReport.php`
- `app/Domains/Analytics/Support/DeliveryStatsQuery.php`
- `app/Domains/Analytics/Services/StoreDeliveryReportReader.php`
- `database/migrations/2026_10_14_000001_add_delivery_exclusions_to_store_settings_table.php`

### باكند (تعديل)
- `app/Domains/Analytics/Support/DashboardFilterFactory.php` — عدم إجبار التوصيل على `created`، وإبقاء `productIds/variantIds` للتوصيل، وإبقاء `carrierId` للتوصيل.
- `app/Domains/Analytics/Support/DashboardOrderScope.php` — اختيار عمود الأساس حسب `memberDimension`.
- `app/Domains/Analytics/Support/DashboardTeamPerformanceQuery.php` — أعمدة `delivered / returned_coming / returned_processed / all_orders` في فرع التوصيل (أحدث تتبّع).
- `app/Domains/Analytics/Support/DashboardTeamPerformance.php` — إظهار الحقول الجديدة + `delivery_rate_net`.
- `app/Domains/Analytics/Services/StoreDashboardAnalyticsService.php` — `deliveryStats()`؛ وإعادة حجب `ordersByState/deliveryTypeBreakdown` على `$canTopKpis && $isTopView` (تمرَّر كـ `isTopView`).
- `app/Models/Stores/StoreSetting.php` — عمودان + `excludedDeliveryStatusKeys()` + `deliveryExclusionsLocked()` + حماية `booted()`.
- `config/analytics.php` — `targets.delivery_rate` (افتراضي 70).
- `app/Livewire/Concerns/DashboardFilterConcern.php` — `teamCohort` (`all|with|without`) + `setTeamCohort()` + إعادة الضبط.

### واجهات (جديد)
- `resources/views/livewire/merchant/dashboard/partials/delivery-stats.blade.php` — بطاقة معدّل التوصيل/الإرجاع.
- `resources/views/livewire/merchant/dashboard/partials/kpi-delivery.blade.php` — بطاقتا المعدّل أعلى عرض التوصيل.
- `resources/views/livewire/merchant/dashboard/partials/top-breakdown.blade.php` — الولاية + طريقة التوصيل (المنقولة).

### واجهات (تعديل)
- `dashboard.blade.php` — نقل الكتلتين لفرع Top؛ إدراج `kpi-delivery` + `delivery-stats` في فرع Delivery؛ تمرير `teamTrash` للتوصيل؛ تمرير `$isTopView` للـ service للحجب.
- `filter-bar.blade.php` — إظهار أساس التاريخ + منتقي المنتج للتوصيل أيضاً؛ إبقاء شركة التوصيل للتوصيل.
- `partials/team-performance.blade.php` — بحث موسّط + زر تصفية dropdown + بطاقات عمودية.
- `partials/confirmation-team.blade.php` — لا تغيير وظيفي (مرجع تصميم).
- `resources/lang/{ar,fr,en,es}/dashboard.php` — التسميات + مفاتيح معدّل التوصيل + مفاتيح التصفية.

---

## 4) المراحل

### 37-N7-A — فتح أُسس فلترة التوصيل (باكند)
- في `DashboardFilterFactory`: حذف فرع إجبار التوصيل (`productIds=null; variantIds=null; dateBasis=CREATED`)؛ إبقاء `carrierId` للتوصيل؛ توسيع `resolveDateBasis` ليعمل ضمن العرض النشط.
- في `DashboardOrderScope::applyDateWindow`: اختيار العمود:
  - `delivery` → `orders.last_status_changed_at`
  - غير ذلك → `orders.confirmation_status_changed_at`
  مع بقاء `whereNull(col)->whereBetween(created_at)` كـ fallback.
- **اختبارات:** امتداد `DashboardFilterTest` (توصيل + أساس آخر حالة + فلتر منتج يصل إلى DTO ونافذة الاستعلام).

### 37-N7-B — نموذج معدّل التوصيل + الاستثناءات (باكند)
- `DeliveryRate`: `GROSS/NET`، `kindForBasis`، `compute(kind, counts[], excludedKeys[])` يُعيد `delivered/returned/denominator/delivered_rate/return_rate/excluded`، و`percent()` مشترك.
- `DeliveryStatsReport`: يطوي صفوف `(membership_id, status_key, total, deleted)` إلى النتيجة أعلاه + `perMember()`.
- `DeliveryStatsQuery`: قراءة مجمّعة واحدة بـ `withTrashed` عبر `DashboardOrderScope` (نظير `ConfirmationStatsQuery`).
- `StoreDeliveryReportReader`: memoised بـ `$filter->hash()`، يقرأ الاستثناءات من `currentStore()->settings`.
- `StoreSetting` + migration + `config/analytics.php`.
- `StoreDashboardAnalyticsService::deliveryStats()`.
- **اختبارات:** `DeliveryRateTest` (وحدة)، `DeliveryStatsQueryTest`، `DeliveryExclusionsTest` (نظير `ConfirmationExclusionsTest`).

### 37-N7-C — قياسات الفريق + الراجعة/المرجعة (باكند)
- توسيع `DashboardTeamPerformanceQuery` (فرع التوصيل) بحسب **أحدث تتبّع** لكل طلب:
  - `delivered` (تتبّع `delivered_at` مضبوط)
  - `returned_coming` (`returned_at` مضبوط و`processed_at` فارغ)
  - `returned_processed` (`processed_at` مضبوط)
  - `all_orders`
- توسيع `DashboardTeamPerformance::row()` بالحقول + `delivery_rate_net` (delivered/(delivered+returned)) مع الحفاظ على الحقول القائمة.
- **اختبارات:** توسيع `DashboardTeamPerformanceTest` و`DashboardTeamPerformanceViewModelTest`.

### 37-N7-D — نقل الكتلتين إلى المؤشرات
- حذف كتلتي الولاية/طريقة التوصيل من فرع Delivery، وإنشاء `top-breakdown.blade.php` وإدراجها في فرع Top (بعد `kpi-top` وقبل `top-members`).
- حجب بياناتهما بـ `$canTopKpis && $isTopView`.
- **اختبارات:** wiring: لا تظهر في delivery/confirmation، وتظهر في top.

### 37-N7-E — واجهة إحصائيات التوصيل + التسمية
- `kpi-delivery.blade.php` (بطاقتا المعدّل، تتبعان الأساس) + `delivery-stats.blade.php`.
- `filter-bar.blade.php`: أساس التاريخ + المنتج للتوصيل.
- تسميات التابات في اللغات الأربع (D7).
- **اختبارات:** `DashboardFilterWiringTest` (فلاتر كل عرض)، فحص ظهور أساس التاريخ + المنتج في التوصيل.

### 37-N7-E/F — إعادة تصميم سكشن فريق التتبّع
- `team-performance.blade.php`: بحث موسّط + زر تصفية dropdown (cohort + trash) + بطاقات عمودية.
- `DashboardFilterConcern`: `teamCohort` + `setTeamCohort()`؛ تمرير `teamTrash` للتوصيل.
- `DashboardFilterOptions::members()` / `DashboardMemberCohort`: تمرير تصفية cohort الاختيارية.
- **اختبارات:** فحص wiring للعناصر الجديدة + فلاتر الأعضاء.

### 37-N7-G — i18n + حراس التصميم + Pint
- مفاتيح الأربع لغات (ar أولاً).
- عدم إدخال `dark:`/`gray-*` (`DashboardDesignContrastTest`)؛ الألوان عبر `GroupColor`.
- تشغيل موجّه: `--filter=Dashboard` ثم `--filter=Delivery` على sqlite وMySQL، ثم Pint نظيف.

---

## 5) الاختبارات المتأثّرة (موجودة)
- `tests/Feature/Merchant/DashboardFilterWiringTest.php`
- `tests/Feature/Merchant/DashboardAnalyticsServiceTest.php`
- `tests/Feature/Merchant/DashboardFilterTest.php`
- `tests/Feature/Merchant/DashboardTeamPerformanceTest.php`
- `tests/Feature/Merchant/DashboardChartsPerViewTest.php`
- `tests/Feature/Merchant/DashboardConfirmationSectionTest.php`
- `tests/Unit/DashboardTeamPerformanceViewModelTest.php`
- `tests/Feature/Merchant/GroupColorDotsTest.php`, `DashboardLayoutTest.php`

### اختبارات جديدة مقترحة
- `tests/Unit/DeliveryRateTest.php`
- `tests/Feature/Merchant/DeliveryStatsQueryTest.php`
- `tests/Feature/Merchant/DeliveryExclusionsTest.php`
- امتدادات wiring للتصفية والبطاقات العمودية.

---

## 6) البرومبت الجاهز للتنفيذ

```text
PHASE 37-N7 — Delivery analytics, Top relocation, tracking-team filters.

Read first: docs/plans/confirmation-statistics-redesign.md (§4, §7) and
docs/plans/order-distribution-rules.md (§11). Mirror their rules; do not invent a
parallel vocabulary or a second computation for a number that already exists.

Locked decisions (do not re-litigate):
- D1: delivery "last status" basis reads orders.last_status_changed_at, with the
  same NULL fallback to created_at used for confirmation_status_changed_at.
- D2: "الراجعة" = order_trackings.returned_at NOT NULL AND processed_at NULL;
  "المرجعة" = processed_at NOT NULL. One row per order (newest tracking).
- D3: delivery rate mirrors ConfirmationRate:
  gross (basis=created): delivered/all_orders and returned/all_orders;
  net (basis=last_status): delivered/(delivered+returned) and returned/(delivered+returned).
- D4: a store keeps an INDEPENDENT one-shot exclusions list for the delivery rate
  (delivery_excluded_status_keys + delivery_exclusions_locked_at), enforced in
  StoreSetting::booted() exactly like confirmation exclusions.
- D5: the Top view owns ordersByState + deliveryTypeBreakdown; Delivery no longer
  renders them. Gate on $canTopKpis && $isTopView.
- D6: two rate cards (delivery rate + return rate, basis-aware) lead the Delivery view.
- D7: tab labels: confirmation = "إحصائيات التأكيد", delivery = "إحصائيات التوصيل",
  top = "أفضل المؤشرات" (ar, fr, en, es).
- D8: tracking-team section gains a centred search and a filter dropdown with a
  cohort pick (all / with-confirmation / without-confirmation) and a with/without
  trash toggle; "confirmation team" = DashboardMemberCohort (ORDER_CONFIRM or management).
- D9: delivery team cards become vertical, matching confirmation-team's design,
  with a bottom delivery-rate bar.

Do NOT: run the full suite locally (memory) — use --filter=; add dark:/gray-*
classes; create docs unless asked; commit; change the confirmation machinery.

Backend:
1. DashboardFilterFactory: stop forcing dateBasis=created and dropping
   productIds/variantIds for the delivery dimension; keep carrierId for delivery.
2. DashboardOrderScope::applyDateWindow: pick the basis column by
   $filter->memberDimension (delivery => last_status_changed_at, else
   confirmation_status_changed_at), keeping the unstamped fallback.
3. Add DeliveryRate, DeliveryStatsReport, DeliveryStatsQuery under
   app/Domains/Analytics/Support and StoreDeliveryReportReader under
   app/Domains/Analytics/Services, mirroring the confirmation classes;
   add StoreDashboardAnalyticsService::deliveryStats().
4. StoreSetting: two new columns + accessors + booted() lock guard; migration
   add_delivery_exclusions_to_store_settings_table; config targets.delivery_rate.
5. DashboardTeamPerformanceQuery (delivery branch): group by the newest tracking
   member and add delivered / returned_coming / returned_processed / all_orders;
   DashboardTeamPerformance::row() exposes them + a net delivery_rate.
6. Gate ordersByState/deliveryTypeBreakdown on $canTopKpis && $isTopView.

Frontend:
7. dashboard.blade.php: move the two breakdown cards into the Top branch (new
   partials/top-breakdown.blade.php); add partials/kpi-delivery.blade.php (two
   basis-aware rate cards) and partials/delivery-stats.blade.php to Delivery;
   pass teamTrash to Delivery.
8. filter-bar.blade.php: render the date-basis and product pickers for Delivery too.
9. team-performance.blade.php: centred search + a filter dropdown (cohort +
   trash) + vertical cards matching confirmation-team, with a bottom rate bar.
10. DashboardFilterConcern: add teamCohort state + setTeamCohort() + reset;
    thread it through DashboardFilterOptions::members()/DashboardMemberCohort.
11. Rename the three tab labels in resources/lang/{ar,fr,en,es}/dashboard.php.

Tests: add Unit/DeliveryRateTest, Feature/Merchant/DeliveryStatsQueryTest,
Feature/Merchant/DeliveryExclusionsTest; extend DashboardFilterWiringTest,
DashboardFilterTest, DashboardAnalyticsServiceTest, DashboardTeamPerformanceTest,
DashboardTeamPerformanceViewModelTest, DashboardChartsPerViewTest,
DashboardConfirmationSectionTest. Keep GroupColorDotsTest and
DashboardDesignContrastTest green.

Verify (scoped, both lanes + Pint):
  php vendor/bin/pest --filter=Delivery
  php vendor/bin/pest --filter=Dashboard
  php vendor/bin/pest -c phpunit.mysql.xml --filter=Delivery
  php vendor/bin/pint --test

Report: files changed, phase-by-phase, tests run with pass/skip counts, and any
assumption you had to make.
```

---

## 7) نقاط مفتوحة (للبتّ قبل التنفيذ)
1. **موضع كتلتي المؤشرات** داخل عرض Top: بعد `kpi-top` وقبل `top-members` (الافتراضي المقترح).
2. **دلالة «مع فريق التأكيد»** نهائياً: cohort التأكيد كما في `DashboardMemberCohort` (الافتراضي المقترح).
3. **ألوان بطاقتي المعدّل**: نجاح/خطر حسب `config('analytics.targets.*')` (مقترح).
