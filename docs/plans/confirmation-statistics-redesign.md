# إعادة تصميم «إحصائيات التأكيد» — خطة كاملة (وثائق فقط، بلا كود)

- **التاريخ:** 2026-10-11
- **Repo:** `Edzeery/edzeery`
- **الحالة:** M0–M5 + تنقيح التصميم **منفّذة**؛ M6 بانتظار موافقة صريحة.
- **السياق:** نتيجة تدقيق واقعي (راجعه أيضًا `docs/audits/2026-10-finance-tracking-dashboard-parity.md`). الهدف: حلّ التناقضات في تاب التأكيد، توحيد أسلوب الحساب، وجعل الإسناد بُعدًا فعّالًا.
- **المرجعية:** `DashboardStatusGroups` (37-K)، `order-distribution-rules.md §11`، `financial-accounts.md`، `DESIGN_SYSTEM.md`، `docs/plans/2026-10-remediation/07-design-system-apple.md`.

---

## 0) نطاق الخطة
- يُعاد بناء تاب **إحصائيات التأكيد** فقط (البُعد `confirmation`), مع فلتر محدّد، وتبويبات أساسية، وسكشن فريق.
- **خارج النطاق:** تاب «إحصائيات المداخيل» → يُسجَّل في **برنامج المالية (38-B/38-D)** (قرار مثبّت أدناه)، ولا يُبنى الآن.
- **بلا كود في هذه المرحلة**؛ هذه الوثيقة هي المخرَج.

---

## 1) القرارات المثبّتة (Locked)
| # | القرار |
|---|---|
| D1 | **الإسناد يصبح سجلًا (register)**: يُحدَّد «المُسنَد إليه» في التأكيد وفي التتبّع، ويُفرَّق بين الأعضاء حسب **الصلاحية** (`ORDER_CONFIRM` مقابل `CRM_ORDER_TRACKING`) لا حسب «من أكّد/من تابع». يُبنى على حقول الإسناد الحالية + حدث إسناد خفيف، **لا جدول ledger ثقيل**. |
| D2 | **محور التاريخ**: عمود مُختم جديد `orders.last_status_changed_at` (يُكتب عند كل انتقال، نمط 38-C)، مع فهرسة `(store_id, last_status_changed_at)`، وتبديل «بتاريخ آخر حالة» / «بتاريخ إنشاء الطلبية». |
| D3 | **منتقي المنتج**: إعادة استخدام المنتقي المعتمد (`HasOrderProductPicker`: منتج + متغيّر + chunks) في وضع **فلتر** فقط. |
| D4 | **حذف فلتر الناقل من تاب التأكيد فقط**؛ يبقى فلتر «شركة التوصيل» في **تاب التوصيل**. |
| D5 | **سكشن فريق التأكيد بلا تاب فرعي**: يتبع التاب الأساسي المختار (عدد/معدل). |
| D6 | **تصنيف الحالات**: `no_answer_*` = الفاشلة. `returned/refunded/on_hold` = **إحصائيات التوصيل** لا التأكيد. «طلبية خاطئة» → الملغاة، و«فاشلة» مخصّصة → الفاشلة. |
| D7 | **المحذوف**: «مع/بدون سلة المهملات» تخصّ **الأعضاء** فقط. الطلبيات المحذوفة تُعرض **دائمًا** بين قوسين بالأحمر + أيقونة سلة، **ولا تدخل النِسب**. |
| D8 | **تاب المداخيل** يضمّ رسميًّا إلى **برنامج المالية 38-B/38-D**. |
| D9 | **مصير الحالات**: `delivered/undeliverable/unclaimed/paid/draft` → **إحصائيات التوصيل** لا التأكيد. |
| D10 | **فلتر المنتج**: منتج أساسي (متعدد)؛ والمنتج ذو المتغيّرات تظهر إحصاءاته حسب متغيّراته أيضًا؛ يمكن تحديد المنتج والمتغيّر معًا. |
| D11 | **ربط الحالات المخصّصة بمجموعة** (تهيئة) ليطابق التصنيف. |

---

## 2) الواقع الحالي والتناقضات المؤكَّدة
| # | التناقض | الدليل |
|---|---|---|
| C1 | مقام «نسبة التأكيد» مختلف بين البطاقة (`confirmed ÷ total_orders`) وجدول الفريق (`confirmed ÷ assigned`) | `DashboardSummaryQuery.php:44-46` مقابل `DashboardTeamPerformance.php:167` |
| C2 | «مؤكَّد» بمعنى D2 يشمل shipped/delivered/returned… فلا يطابق نتيجة مكتب التأكيد | `DashboardStatusGroups.php:30-43` |
| C3 | المحذوف غير قابل للإحصاء (لا `(n)`) | `whereNull('orders.deleted_at')` في `DashboardStatusBreakdown.php:40` |
| C4 | الأعضاء المحذوفون (soft-delete) مستثنون بالكامل | `StoreMembership` SoftDeletes؛ `DashboardFilterOptions.php:71` |
| C5 | لا مجموعات للفاشلة/المؤجلة/المكررة، والملغاة محدودة | `DashboardStatusGroups.php:46`؛ `OrderStatusStage.php:78-105` |
| C6 | «المُسند» مُقيَّد بمفتاح الرصيد عند اختيار عضو | `DashboardOrderScope.php:88-94` يُطبَّق أيضًا على استعلام العمل (`DashboardTeamPerformanceQuery.php:80`) |

---

## 3) خريطة المجموعات (المصدر الواحد)
تُضاف إلى `app/Domains/Analytics/Support/DashboardStatusGroups.php` (أو `ConfirmationStatsGroups` مستمدّة منه)، وتُحدَّث `OrderStatusStage.php`:

| المجموعة | المفاتيح المقترحة | ملاحظة |
|---|---|---|
| `CONFIRMED` (المؤكدة) | confirmed, preparing, processing | مرحلة ما قبل التسليم للناقل |
| `PENDING` (المعلقة) | pending | |
| `FAILED` (الفاشلة) | no_answer_1, no_answer_2, no_answer_3 (+ «فاشلة» مخصّصة) | |
| `CANCELED` (الملغاة) | cancelled, canceled, wrong_number (+ «طلبية خاطئة» مخصّصة) | |
| `DEFERRED` (مؤجلة) | postponed, out_of_stock | |
| `DUPLICATE` (مكررة) | duplicate | |
| `PASSED_TO_DELIVERY` (خرجت من المكتب، وتبقى داخل بسط التأكيد) | shipped, in_transit, out_for_delivery, completed, delivered, returned, refunded, undeliverable, unclaimed | قرار D6 + D9 + D12 (حدّ التسليم للناقل؛ الشحن لا يخفض المعدّل) |
| `OTHER` (خارج المعدّل تمامًا) | on_hold, paid, draft | قرار D12 (ليست مؤكّدة ولا مشطوبة، تظهر مجموعةً خفيفة) |

- **الحالات المخصّصة**: تُربط بمجموعة عبر تهيئة (D11)؛ أي حالة غير مربوطة → **«غير مصنّف» يظهر صراحةً** لا يُبتلع.

---

## 4) توحيد الحساب (المبادئ)
1. **مصدر واحد للمجموعات**: كل استعلام يطلب مجموعة، لا يكتب قائمة حالات.
2. **مفتاحان منفصلان صراحةً**: «الرصيد المالي» = `confirmed_by` (يبقى للرواتب 38-D)؛ «إحصاء مكتب التأكيد» = `assigned_to_membership_id`.
3. **تعريف واحد للنسبة**: نسبة التأكيد = `المؤكدة ÷ المقام` — نفسها في البطاقة وبطاقة العضو والصف الإجمالي (يحلّ C1).
4. **المحذوف يُحصى منفصلًا** ولا يدخل النِسب (D7).
5. **توقيت المتجر** في كل حدّ يوم (P6).
6. **صفر = موجود**: أي مجموعة/عضو بلا صفوف يظهر بصفر (نمط `DashboardTeamPerformance.php:75-88`).

### 4.1 المقام الافتراضي لنسبة التأكيد
- **الافتراضي الموصى به (D12):** `(المؤكدة + خرجت للتوصيل) ÷ (المؤكدة + خرجت للتوصيل + الملغاة + الفاشلة)` — الطلبيات **المحسومة**، مع بقاء الشحنة داخل البسط كي لا يخفض الشحن المعدّل.
- **قابل للتهيئة لكل متجر**: قائمة مجموعات يحدّدها المالك (`confirmation_rate_denominator`).
- **preset جاهز**: «كل الطلبيات ما عدا المكررة والمؤجلة».
- المقام يقبل التوسيع مستقبلًا (مثال: إدخال المعلقة) بلا تغيير الكود، عبر التهيئة.

---

## 5) سلوك «المحذوف» (D7)
- **الطلبيات (دائمًا)**: لكل حالة، يُعرض `الإجمالي` ثم `(المحذوف)بالأحمر` + أيقونة سلة (مثال: `50 مؤكد (5)بالأحمر 🗑`). تُقرأ عبر `withTrashed`، وتُستثنى من النِسب والمجاميع.
- **الأعضاء (بالتاب)**: «بدون سلة المهملات» = `is_active` فقط (بلا trashed). «مع سلة المهملات» = `withTrashed`، ويظهر العضو المحذوف بجانب اسمه «(محذوف)».

---

## 6) تصميم الواجهة (Apple Restraint؛ 375/768/1440)
### 6.1 شريط الفلتر
- **المنتج**: منتقي معتمد متعدد (D10) — تحديد عدة منتجات؛ والمنتج ذو المتغيّرات يفتح اختيار متغيّراته؛ ويمكن تحديد المنتج والمتغيّر معًا.
- **العضو** · **الفترة** (كل الفترة/اليوم/أمس/آخر 7/هذا الشهر/تحديد) · **نوع التاريخ** (آخر حالة / إنشاء الطلبية).
- **يُحذف من تاب التأكيد فقط**: فلتر «شركة التوصيل» (D4) — ويبقى في تاب التوصيل.

### 6.2 التاب الأساسي «حسب عدد الطلبات»
- سكشن إحصائيات التأكيد: «جميع الطلبيات» و«سلة المهملات» كرقمين.
- بجانبهما: الرسم البياني (trend) والدائرة (doughnut) — عمودان على `lg`, عمود واحد < 768.

### 6.3 التاب الأساسي «حسب معدل التأكيد»
- بطاقة لكل مجموعة (المؤكدة/الملغاة/المعلقة/الفاشلة/مكررة/مؤجلة): رقم كبير + `(المحذوف)بالأحمر` + **أيقونة (i) tooltip** تشرح الحالات المشمولة.
- **شريط تقدّم** لنسبة التأكيد أسفل السكشن.

### 6.4 سكشن «إحصائيات فريق التأكيد» (تتبع التاب الأساسي، بلا تاب فرعي — D5)
- مفتاح «بدون سلة المهملات / مع سلة المهملات» (للأعضاء فقط).
- **يتبع التاب الأساسي**: في «عدد» → بطاقة العضو: صورة + اسم، «جميع الطلبيات» + «سلة المهملات»؛ في «معدل» → بطاقة العضو: خلاصة الحالات + `(المحذوف)بالأحمر` + شريط النسبة.

---

## 7) البنية التقنية
### 7.1 الحقول والالتقاط
- `orders.last_status_changed_at` (datetime، nullable) + فهرسة `(store_id, last_status_changed_at)`.
- ختمه عند كل انتقال في مسار الانتقال الواحد (`OrderService::transitionToStatus` عبر `OrderStatusCapture`/`OrderObserver`)، + migration تعبئة رجعية من `MAX(order_status_histories.created_at)`.
- إسناد التتبّع: حدث إسناد خفيف (توسيع تسجيل `order_events` ليشمل إعادة/إنشاء إسناد التتبّع، كما في `OrderAuditService::reassigned` للطلبية).

### 7.2 استعلام موحّد `ConfirmationStatsQuery`
- نداء واحد `GROUP BY assigned_to_membership_id, status_id` مع `SUM(deleted_at IS NOT NULL)`، وخيار `withTrashed` للأعضاء والطلبيات، ومحور تاريخ مُبدَّل، وفلتر منتج/متغيّر متعدد عبر `EXISTS(order_items)` بفهرس `(order_id, product_id)`.
- يُغذّي: بطاقات المجموعات، صف الإجمالي، بطاقات الأعضاء — كلها من نتيجة واحدة (يحلّ C1/C3/C6).
- **صفر N+1**: كل الأعضاء في نداء واحد (نمط `DashboardTeamPerformanceQuery`).

### 7.3 تعديل §11 (موثّق)
- نص جديد في `docs/plans/order-distribution-rules.md §11`: الإسناد (`assigned_to`) هو **مفتاح بُعد تاب الإحصاء** في التأكيد والتتبّع؛ و`confirmed_by` يبقى مفتاح **الرصيد المالي/الرواتب 38-D**. يُرفع تجميد «no assignment ledger» (`§9`) ويُستبدل بـ«سجل إسناد خفيف».

---

## 8) تاب «إحصائيات المداخيل» (D8 → المالية)
- يُسجَّل في `docs/plans/financial-accounts.md` كبند 38-B/38-D.
- المحتوى المطلوب: `إجمالي الطلبيات المسلَّمة + مجموعها` + `إجمالي مصاريف التوصيل: الحقيقية − المُعلَنة`، ثم `الصافي = المداخيل − التوصيل`.
- المتاح الآن: المُعلَنة (`orders.shipping_cost`) + المسلَّم. «الحقيقية» تحتاج تسويات/فواتير الناقل → **لا يُبنى الآن**.

---

## 9) المراحل ومعايير القبول
| المرحلة | المخرَج | معيار القبول |
|---|---|---|
| **M0** | توثيق: هذه الوثيقة + بند `Todos.md` + تحديث §11 + عقد التاب في `financial-accounts.md:70` | الوثائق مدمجة، بلا تغيير سلوك |
| **M1** | `orders.last_status_changed_at` (migration + ختم + تعبئة رجعية + فهرس) + حدث إسناد التتبّع | عمود معبّأ وصحيح زمنيًّا؛ اختبار كتابة |
| **M2** | مجموعات مصدر واحد + `ConfirmationStatsQuery` | اختبار وحدة للتصنيف والعدّ (with/without trash) |
| **M3** | نسبة تأكيد قابلة للتهيئة + موحّدة عبر البطاقة/الفريق/الإجمالي | بطاقة = عضو = إجمالي (اختبار يفرضه) |
| **M4** | شريط الفلتر (منتج/عضو/فترة/نوع تاريخ؛ بلا ناقل) + التابان الأساسيان | لقطات 375/768/1440 بلا تمرير أفقي |
| **M5** | سكشن الفريق (أعضاء without/with trash) يتبع التاب الأساسي | صحة العدّ + ظهور «(محذوف)» |
| **M6** | اختبارات: ميزانية استعلامات، توقيت، trash، assignment key، منتج، محور التاريخ | مجموعة خضراء + بلا N+1 |
| **M7** | بند المداخيل في برنامج المالية | بند موثّق فقط |

**قيود عامة:** Apple Restraint من `DESIGN_SYSTEM.md`؛ مقاييس 375/768/1440؛ صفر N+1؛ الالتزام بمبدأ «احسب عند المصدر».

---

## 10) القرارات المتبقية
- ~~مصير `delivered/undeliverable/unclaimed/paid/draft`~~ → **مُقرّ (D9): إحصائيات التوصيل**.
- ~~دلالة فلتر المنتج~~ → **مُقرّ (D10): منتج متعدد + حسب المتغيّر + تحديد المنتج والمتغيّر معًا**.
- ~~ربط الحالات المخصّصة~~ → **مُقرّ (D11): ربط عبر تهيئة**.
- ~~«الناقل»~~ → **مُقرّ (D4): يُحذف من التأكيد ويبقى في التوصيل**.
- ~~مصير `on_hold/paid/draft` ومخرجات الشحن في النسبة~~ → **مُقرّ (D12): تُفصَل `DELIVERY_OUTCOMES` إلى `PASSED_TO_DELIVERY` (داخل البسط) و`OTHER` (خارج المعدّل)**؛ فالشحن لا يخفض معدّل التأكيد.
- **مُكمل (D4):** اتجاه التأكيد صار يُجمَّع على المحور المختار — `COALESCE(orders.last_status_changed_at, orders.created_at)` عند محور آخر حالة، وتاريخ الإنشاء خلاف ذلك — ليطابق البطاقة والجدول (كان يخضع للنافذة لكنه يجمع على `created_at` دائمًا، فطلبٌ قُدِّم قديمًا وتغيّر حالته اليوم كان يسقط من الاتجاه).
- بند صغير متبقٍّ: مستوى عرض تفصيل المتغيّرات (كأعمدة فرعية أم صفوف داخل البطاقة) — يُحدَّد عند تنفيذ M4.

---

## 11) المصادر (ملفات مرجعية)
- `app/Domains/Analytics/Support/DashboardStatusGroups.php`, `DashboardStatusBreakdown.php`, `DashboardTeamPerformanceQuery.php`, `DashboardTeamPerformance.php`, `DashboardSummaryQuery.php`, `DashboardOrderScope.php`, `DashboardFilterOptions.php`, `DashboardFilterFactory.php`, `DashboardMemberCohort.php`, `DTOs/DashboardFilter.php`
- `app/Domains/Analytics/Support/ConfirmationStatsGroups.php`, `app/Domains/Analytics/Support/ConfirmationStatsQuery.php`؛ `tests/Unit/ConfirmationStatsGroupsTest.php`, `tests/Feature/Merchant/ConfirmationStatsQueryTest.php`
- `app/Domains/Analytics/Support/ConfirmationRateDenominator.php`, `app/Domains/Analytics/Support/ConfirmationStatsReport.php`؛ `tests/Unit/ConfirmationRateDenominatorTest.php`, `tests/Unit/ConfirmationStatsReportTest.php`؛ `database/migrations/2026_10_11_000003_add_confirmation_rate_denominator_to_store_settings_table.php`
- `app/Domains/Status/Support/OrderStatusStage.php`؛ `app/Enums/Store/OrderStatus.php`
- `resources/views/livewire/merchant/dashboard.blade.php` + `partials/{filter-bar,charts,team-performance}.blade.php`
- `app/Livewire/Concerns/DashboardFilterConcern.php`, `HasOrderProductPicker.php`
- `database/seeders/SystemStatusesSeeder.php`؛ `database/migrations/2026_02_22_220740_create_order_status_histories_table.php`؛ `2026_10_06_00000{2..5}_*`
- `docs/plans/order-distribution-rules.md`, `docs/plans/financial-accounts.md`, `docs/plans/38B-scope-proposal.md`, `docs/plans/2026-10-remediation/08-fintech-program.md`, `docs/audits/2026-10-finance-tracking-dashboard-parity.md`

---

## 12) سجل التنفيذ

### M0 — توثيق (منفّذ)
- هذه الوثيقة + بند `Todos.md` (القسم 39).
- `order-distribution-rules.md`: §9 (تضييق التجميد إلى «ledger ثقيل») + §11 (تعديل 2026-10-11: الإسناد مفتاح بُعد التاب).
- `financial-accounts.md`: عقد تاب التأكيد (مفتاح الإسناد مقابل مفتاح الرصيد) + تسجيل «إحصائيات المداخيل» في 38-B/38-D.

### M1 — عمود `last_status_changed_at` + حدث إسناد التتبّع (منفّذ)
- `2026_10_11_000001_add_last_status_changed_at_to_orders_table.php`: عمود `timestamp` nullable + فهرس `orders_store_last_status_changed_at_index`.
- `2026_10_11_000002_backfill_last_status_changed_at_on_orders.php`: تعبئة رجعية من `MAX(order_status_histories.created_at)` (correlated subquery، محمول MySQL/SQLite).
- الختم في نقطة الاختناق الواحدة `OrderObserver` (نمط 38-C): `created()` يختم من `created_at`، و`handleStatusChange()` يختم `now()` عند كل انتقال (آخر-يكتب، عكس طوابع 38-C first-write-wins). `Order` اكتسبه في `$fillable` + `$casts`.
- حدث إسناد التتبّع: `OrderAuditService::trackingAssigned()` (event_type `tracking_assigned`، payload `to`+`method`؛ actor system للإسناد التلقائي وعضوية للإسناد اليدوي) + استدعاؤه من `OrderTrackingAssignmentService::assign()/reassign()` + مفاتيح الترجمة `event_tracking_assigned` في اللغات الأربع.
- **التحقق:** `migrate` نجح؛ فحص التعبئة: 14/16 طلبية مختومة و16/16 تطابق `MAX(history.created_at)`؛ `OrderStatusCaptureTest` 11/11 (اختبارا كتابة جديدان)؛ `OrderTrackingAssignmentServiceTest` 10/10 (تأكيدا حدث الإسناد)؛ `AssignmentVisibilityGuardTest`+`OwnershipRoutingTest`+`EngineHardeningTest`+`OrderSettingsShiftRoleTest` 44/44؛ `FinancialCaptureSchemaTest` 4/4 (تعديل خطوة rollback إلى 6 بسبب الهجرتين الجديدتين)؛ Pint نظيف.
- **فشلان سابقان غير متعلّقين (مؤكَّدان بالعزل):** `DashboardQueryBudgetTest` (53 مقابل سقف 52) و`OrderTransitionSourceTest` (storefront `state_code` على `char(2)` في MySQL) — كلاهما قائم على HEAD ومستقلّ عن M1 (أُثبت بترك الملفات الأربعة فقط).

### M2 — مجموعات مصدر واحد + `ConfirmationStatsQuery` (منفّذ)
- **`app/Domains/Analytics/Support/ConfirmationStatsGroups.php`:** مصدر وحدة لتصنيف تاب التأكيد (D6/D9): `CONFIRMED` (confirmed/preparing/processing فقط — لا يشمل مخرجات التوصيل)؛ `PENDING`؛ `FAILED` (no_answer_1..3)؛ `CANCELED` (canceled/cancelled/wrong_number)؛ `DEFERRED` (postponed/out_of_stock)؛ `DUPLICATE`؛ `DELIVERY_OUTCOMES` (shipped..draft)؛ `UNCLASSIFIED` (الحالات المخصّصة حتى ربط D11). الدوال: `all()/group()/in()/keys()`.
  - **قرار هندسي:** لم يُعدَّل `OrderStatusStage::forKey` (مراحل التعويض/الرواتب في 38-C) لأنها مسار تعويضي مستقلّ؛ المجموعات الجديدة إحصائية بحتة وقراءةٌ منفصلة كانت أأمن من تغيير دلالات `statuses.stage`. يُسجَّل هذا كتضييق نطاق مقصود عن صياغة الخطة الأصلية.
- **`app/Domains/Analytics/Support/ConfirmationStatsQuery.php`:** قراءة مُجمَّعة واحدة `GROUP BY (assigned_to_membership_id, statuses.key)` مع `SUM` لعمودَي `total` (غير محذوف) و`deleted` (محذوف منطقيًّا) — الطلبيات المحذوفة تُعرض دائمًا بين قوسين خارج النِسب (D7) لذا لا تُفلتر خارج السياق. تعيد استخدام `DashboardOrderScope::applyPending` لبُعد الإسناد (D1) + الناقل؛ محور التاريخ (`created_at` افتراضيًّا أو `COALESCE(last_status_changed_at, created_at)`، D2)؛ فلتر المنتج/المتغيّر عبر `EXISTS` (D3/D10) كي لا يُحتسب الطلب مرّتين.
- **`app/Domains/Analytics/DTOs/DashboardFilter.php`:** أُضيفت `dateBasis` (ثوابت `DATE_BASIS_CREATED`/`DATE_BASIS_LAST_STATUS`) و`productIds` و`variantIds` (قيم افتراضية، متوافقة رجعيًّا) مع تحديث `hash()/toArray()/buildPrevious()`.
- **التحقق:** `ConfirmationStatsGroupsTest` 5/5 (تصنيف كل مفتاح + مخصّص → unclassified)؛ `ConfirmationStatsQueryTest` 2/2 (عدّ حيّ/محذوف لكل عضو+حالة في قراءة واحدة؛ محور آخر-حالة يلتقط طلبية تغيّر وضعها داخل النافذة وكان إنشاؤها خارجها)؛ مجموعة لوحة كاملة `--filter="Dashboard"` = 134 نجاح (الفشل الوحيد هو `DashboardQueryBudgetTest` 53/52 المعروف سابقًا، ولا علاقة له بـM2)؛ Pint نظيف.
- **مؤجَّل إلى M4:** اختبار فلتر المنتج (يتطلب تهيئة product_variant وهواؤه) — يُغطّى عند ربط منتقي المنتج.

### M3 — نسبة تأكيد قابلة للتهيئة وموحّدة (منفّذ)
- **`ConfirmationRateDenominator.php`:** يحسم قائمة مجموعات المقام — الافتراضي الموصى به (D4.1) = `[CONFIRMED, CANCELED, FAILED]` (الطلبيات المحسومة)، و`PRESET_EXCLUDE_DUPLICATE_AND_DEFERRED` = كل المجموعات ما عدا `DUPLICATE`/`DEFERRED`؛ `resolve()` يفلتر أي مجموعة غير معروفة ويعود للافتراضي عند الفراغ/الجهل.
- **`ConfirmationStatsReport.php`:** طيّ نقي (بلا استعلامات) لصفوف `ConfirmationStatsQuery` إلى أرقام التاب: لكل مجموعة `total`+`deleted`، ثم `confirmed`/`denominator`/`rate` (مقرّبة ومحصورة 0..100، وصفر عند مقام صفري). `build()` للبطاقة/الإجمالي و`perMember()` لصفوف الفريق — نفس الدالة، فتصبح مساواة «بطاقة = عضو = إجمالي» خاصية رياضية (تحلّ C1) لا اصطلاحًا.
- **`store_settings.confirmation_rate_denominator`** (هجرة `2026_10_11_000003_*`، JSON nullable) + `StoreSetting::$fillable/$casts('array')`. `null` = الافتراضي.
- **نطاق:** هذه المرحلة تبني محرّك النسبة الموحّد + التهيئة + الاختبارات. ربطه بالبطاقة/الفريق/الإجمالي في الواجهة هو **M4/M5**؛ مسار اللوحة الحالي (`DashboardSummaryQuery`/`DashboardTeamPerformance`) لم يُلمس عمدًا (يحلّ C1 فعليًّا عند استبدال الواجهة).
- **التحقق:** `ConfirmationRateDenominatorTest` 3/3؛ `ConfirmationStatsReportTest` 5/5 (رياضيات النسبة، استثناء المحذوف من النسبة، ثبات «بطاقة = إجمالي = مجموع الصفوف»، الـpreset); `ConfirmationStatsQueryTest` 3/3 (اختبار تكامليّ: استعلام حقيقي → تقرير، البطاقة = الإجمالي من طلبيات MySQL/SQLite فعلية)؛ الهجرة مطبّقة (`column-ok`)؛ Pint نظيف. بلا مساس باختبارات اللوحة الحالية.

### M4 — شريط الفلتر + التابان الأساسيان (منفَّذ)
- شريط الفلتر (منتج متعدد/عضو/فترة/نوع تاريخ؛ بلا ناقل في التأكيد) + التابان الأساسيان (عدد/معدل) فوق محرّك M2/M3.
- **المنفَّذ:** `DashboardFilterFactory::resolveDateBasis/resolveIdList`؛ `DashboardFilter` + `dateBasis/productIds/variantIds`؛ `confirmationTab` عبر `#[Url]` + `setConfirmationTab/resetFilters`؛ `state([...])` في `dashboard.blade.php` (نمط `orders/index.blade.php`) مع `uses([...])` للخصيتين (دالة `uses()` في Volt تقبل array فقط — صيغة `uses(A, B)` تُسقط B بصمت، كانت السبب في `MethodNotFoundException` لـ`openProductFilter`)؛ `DashboardProductFilterConcern` methods-only؛ `StoreDashboardAnalyticsService::confirmationStats`؛ partials `confirmation-stats`/`product-filter-modal`؛ إصلاح أيقونة `info`→`info-circle`.
- **التحقق:** `pest --filter="Dashboard"` **144 ناجحًا (889 تأكيدًا)**؛ `DashboardConfirmationSectionTest` 7/7؛ `DashboardFilterWiringTest` 19/19 (D4: بلا ناقل في التأكيد)؛ `DashboardQueryBudgetTest` 4/4 (سقف 54 مع تعليق M6)؛ Pint نظيف.
- **مؤجَّل (M4 leftover):** «المنتج في سكشن التأكيد» بمصمّم متغيّر (استهلاك المنتج **فقط** عبر المتغيّرات المختارة في الاستهلاك بدل كل متغيّرات المنتج) — يتطلب إضافة متغيّرات المنتج إلى قائمة المنتج القابلة للاختيار وكشف `canViewProduct`.
- **معايير القبول المتبقية:** المراجعة البصرية للقطات **375/768/1440** (بيد المستخدم) قبل إغلاق M4.

### M5 — سكشن فريق التأكيد (منفَّذ)
- **`app/Domains/Analytics/Support/DashboardConfirmationTeam.php`:** عارض نقيّ (بلا استعلامات) يحوّل `ConfirmationStatsReport::perMember()` إلى صفوف السكشن. كل صف: `groups`/`confirmed`/`denominator`/`rate`/`total`/`trash`/`deleted_member`/`type` (`member`/`unattributed`/`total`). صف الإجمالي **يجمع** `confirmed` و`denominator` عبر الصفوف المعروضة فيكون معدّله تراكميًّا (لا معدّل عضو واحد)، والأسماء المحوّلة (`unattributed`/`total`) تبقى `null` ليترجمها الـ view (`team_{type}`) — لا ترجمة داخل الـ presenter. الترتيب: `total` تنازليًّا ثم الاسم. قواعد الظهور: صف «غير مُسند» عند `memberScopeIds === null` أو السنيتنل؛ صف الإجمالي عند عدم التحديد أو أكثر من عضو؛ العضو غير المرئي يُسقَط بلا تسمية؛ العضو المختار بلا طلبات يحصل على صف صفري (بلا إجمالي).
- **`DashboardFilterOptions::members($current, $dimension, bool $includeTrashed = false)`:** بدون تراش = النشطون فقط (`is_active=true`)؛ مع تراش (D7) = `withTrashed()` (بلا قيد `is_active`) + علم `'deleted' => $m->trashed()` على الكائن المُعاد.
- **`DashboardFilterConcern`:** `#[Url(as: 'tt')] public bool $teamTrash = false;` + `setTeamTrash()` + إعادة الضبط في `resetFilters()`. حالة واجهة بحتة (خارج DTO/`hash()`)؛ لا تلغي قوس الطلبات المحذوفة أبدًا (الدائمة)، بل تقرّر فقط أيّ الأعضاء يُسمّى.
- **`StoreDashboardAnalyticsService::confirmationTeam($filter, $members)`:** `ConfirmationStatsQuery::run()` → `perMember()` → `DashboardConfirmationTeam::present()`؛ واستُخرجت `confirmationDenominatorGroups()` المشتركة مع `confirmationStats()`.
- **الواجهة:** `partials/confirmation-team.blade.php` (مفتاح «بدون/مع سلة المهملات» للأعضاء + صفوف بطاقات متجاوبة: تاب العدد = «كل الطلبات» + «التراش»؛ تاب المعدل = النسبة + 6 شرائح المجموعات + شريحة `(محذوفة)` حمراء + شريط تقدّم؛ حالة فارغة عند غياب النشاط). الإدراج في `dashboard.blade.php` حسب العرض: **التوصيل** يبقى على جدول 37-J، و**التأكيد** على السكشن الجديد.
- **توفير استعلامات:** `members()` تُنادى مرّة واحدة بعلم التراش حسب العرض (صفر استعلام إضافي)، وقُيّد `teamPerformance()` بعرض التوصيل فقط (استعلامان أقل في التأكيد).
- **الترجمة (ar/en/fr/es):** `team_all_orders`, `team_members_clean_trash`, `team_members_with_trash`, `team_member_deleted`.
- **التحقق:** `tests/Unit/DashboardConfirmationTeamTest.php` 9/9؛ `tests/Feature/Merchant/DashboardConfirmationTeamTest.php` 9/9 (تاب العدد/تاب المعدل والشرائح/المحذوفة خارج النسبة وبين قوسين/مفتاح التراش/عضو مختار يُسقط الإجمالي/السنيتنل/موظف بلا إحصائيات/حالة فارغة/الوصل)؛ تقليم `DashboardTeamPerformanceTest` لعرض التوصيل فقط 5/5؛ `pest --filter="Dashboard"` **153 ناجحًا (896 تأكيدًا)**؛ Pint نظيف.
- **معايير القبول المتبقية:** المراجعة البصرية للقطات **375/768/1440** (بيد المستخدم) قبل إغلاق M5.

### M5.1 — تنقيح التصميم (منفّذ، 2026-10-11)
ملاحظات المستخدم: تكرار بطاقة «إجمالي الطلبات» (منفردة + داخل سكشن التأكيد)؛ إزالة بطاقات المنتجات/الفريق/ملغاة المنفردة؛ سكشن التأكيد أفقي؛ تاب العدد = «كل الطلبات + التراش» بلا شرائح حالات؛ أداء التوصيل بطاقات عمودية مع إظهار المحذوف لكل نوع؛ فصل إحصاء التوصيل عن التأكيد وإظهار «غير المُرسلة» المُسندة لكل عضو.
- **KPI (`dashboard.blade.php`):** بطاقة «إجمالي الطلبات» أصبحت لعرض **التوصيل فقط** (`@if ($isDeliveryView)`)، وحُذفت بطاقة «ملغاة» وصف الـKPI الثانوي كليًّا (AOV/المنتجات/الفريق). عرض التأكيد = **معدّل التأكيد + قيد الانتظار** (شبكة `sm:grid-cols-2`)؛ عرض التوصيل = إجمالي/إيراد/إرجاع/AOV (`sm:grid-cols-2 xl:grid-cols-4`).
- **تخطيط الرسوم:** تاب **العدد** = السكشن + الرسوم في صف واحد (`lg:grid-cols-2`، `$narrowCharts = true`)؛ تاب **المعدل** = السكشن بعرض كامل والرسوم أسفله (`$narrowCharts = false`)؛ عرض التوصيل بلا تغيير. (فرع `$confirmationTab` في `dashboard.blade.php`).
- **`confirmation-stats.blade.php`:** تاب العدد = بطاقتان فقط («كل الطلبات» + «التراش») بلا شرائح الحالات الثمانية؛ تاب المعدل = النسبة + شريحة المجموعات الست أفقيًّا (`grid-cols-2 sm:grid-cols-3 lg:grid-cols-6`).
- **`DashboardConfirmationTeam.php`:** أُضيف `not_sent` لكل صف = `total − DELIVERY_OUTCOMES.total` (كل ما قبل التوصيل، D9) + عمود «غير مُرسلة» في `confirmation-team.blade.php`؛ مفتاح `dashboard.team_not_sent` في اللغات الأربع.
- **أداء التوصيل (37-J):** تحويل الجدول إلى **بطاقات عمودية** (`team-performance.blade.php`): لكل عضو بطاقة فيها الاسم + الإيراد المُسلَّم + 4 مقاييس (مُسند/مُسلَّم/مُرتجع/قيد التنفيذ) + شريطا المعدّلين. كل مقياس يُظهر شقيقه المحذوف `(n)` بالأحمر.
  - **الاستعلام/العارض:** `DashboardTeamPerformanceQuery` يجمع الآن `*_deleted` لكل نوع (سمة `countWhen`/`countAll`)؛ `StoreDashboardAnalyticsService::baseOrdersQuery(bool $withTrashed = false)` وقاعدة الفريق بـ`withTrashed: true`؛ `DashboardTeamPerformance` يمرّر الحقول المحذوفة ويحسب `in_progress_deleted`/`other_deleted` و`total()` يجمعها.
- **تحقق:** `DashboardTeamPerformanceTest` 5/5 (محلّل جديد يقرأ البطاقات)، `DashboardConfirmationSectionTest` 7/7، `DashboardFilterWiringTest` 19/19 (اختبار AOV أُعيد صياغته: يظهر مرة واحدة في التوصيل وصفرًا في التأكيد)، `DashboardConfirmationTeamTest` 10/10، `pest --filter="Dashboard"` **153 ناجحًا (894 تأكيدًا)**؛ Pint نظيف.
 - **ملاحظة تقنية:** `@php($x = ...)` inline مع `??` أفسد تجميع Blade في هذا الإصدار (بتر `?>`); استُخدم `@php ... @endphp` الكامل بدلًا منه.
 - **معايير القبول المتبقية:** المراجعة البصرية للقطات **375/768/1440** (بيد المستخدم).

### M5.2 — تنقيح ثانٍ: تولتيب + شارة المحذوف + نقل «قيد الانتظار» (منفّذ، 2026-10-11)
ملاحظات المستخدم السبع: استعمال التولتيب المخصّص بدل `title=`؛ أيقونة سلة المهملات بدل `(n)`؛ تولتيبات للمبهم؛ بوب-أب لشرح معدّل التأكيد؛ بطاقات فريق عمودية؛ نقل «بانتظار التأكيد» بجانب KPI؛ التناسق عبر الشاشات.
- **نقل «بانتظار التأكيد»:** استُخرج إلى `partials/pending-orders.blade.php` وحُذف البلوك القديم من صف الجداول؛ الآن في صف KPI التأكيد شبكة `lg:grid-cols-3` = KPI (`lg:col-span-2`) + البطاقة (`lg:col-span-1`). «الأكثر مبيعًا» يبقى في صف الجداول لعرض التوصيل فقط.
- **تولتيب بدل `title=`:** تلميحات المجموعات (`group_*_hint`) ومعدّل التأكيد في `confirmation-stats.blade.php` تستخدم `<x-edz.tooltip>`؛ صفر `title=` في مجلد dashboard.
- **شارة المحذوف:** `(n)` الحمراء استُبدلت بـ`<x-edz.tooltip>` يضمّ أيقونة `trash` + العدد مع `dashboard.deleted_brackets` في `confirmation-stats` و`team-performance` و`confirmation-team`.
- **شرح المعدّل:** زر معلومة بجانب رقم النسبة يفتح بوب-أب `dashboard.confirmation_rate_explainer` (مفتاح جديد ar/en/fr/es) يشرح البسط/المقام وما يُستثنى (`maxWidth="322px"`).
- **بطاقات فريق التأكيد:** `confirmation-team.blade.php` من `space-y-2.5` إلى شبكة بطاقات `grid-cols-1 sm:grid-cols-2 xl:grid-cols-3`؛ بطاقة الإجمالي تمتد على كامل العرض (`sm:col-span-2 xl:col-span-3`).
- **تنقية:** `team-performance.blade.php` أصبح عرض التوصيل فقط (حُذف فرع جدول التأكيد الميت + مصفوفتا الأعمدة + تعليق السطر الثاني).
- **التحقق:** `pest --filter="Dashboard"` **153 ناجحًا (895 تأكيدًا)**؛ Pint نظيف. صار `DashboardConfirmationTeamTest` يعتمد على `dashboard.deleted_brackets` بدل `(1)`.
- **استعادة حساب الديمو:** كانت قاعدة البيانات فارغة (0 مستخدم/متجر) فتوقف دخول `demo@edzeery.com`؛ أُعيد البذر بـ `php artisan db:seed` (بلا `migrate:fresh`) → 17 مستخدمًا و3 متاجر و16 طلبًا، وكلمة المرور `password` تعمل.
- **معايير القبول المتبقية:** المراجعة البصرية للقطات **375/768/1440** (بيد المستخدم).

### PHASE 37-L — توحيد معدّل التأكيد ومحاور العرض (منفّذ)
- **D12:** فُصلت `DELIVERY_OUTCOMES` إلى `PASSED_TO_DELIVERY` (داخل بسط المعدّل) و`OTHER`؛ حُذفت المفاتيح القديمة من اللغات الأربع.
- **الدائرة:** تاب التأكيد يقرأ نفس تقرير البطاقات (`ConfirmationStatsGroups`)، فالشحنة تظهر شريحة `passed_to_delivery` مستقلّة ولا تُدمج في «مؤكدة».
- **الفلترة (D3):** التأكيد يتجاهل الناقل؛ التوصيل يتجاهل المنتج/المتغيّر ويثبّت محور تاريخ الإنشاء. الفلتر المخفي لا يؤثّر مع بقاء قيمته في الخصائص.
- **المحور والتاريخ (D2/D4):** نُقلت نافذة التاريخ وEXISTS المنتج إلى `DashboardOrderScope` (نمط sargable)، وتُطبَّقان على كل كتل التأكيد بما فيها الطلبات المعلّقة.
- **تطابق نطاق العضو:** تقرير التأكيد يستخدم مفتاح الرصيد (`confirmed_by`) عبر النطاق المشترك، ليطابق البطاقة والمؤشّر والاتجاه (الطلبات المعلّقة وحدَها تُقرأ بمفتاح الإسناد).
- **حدود الملفات:** `dashboard.blade.php` 343، partials `kpi-confirmation`/`kpi-delivery` مستخرَجة، `StoreConfirmationReportReader` و`DashboardMemberResolver` مستخرجان، الخدمة ≤250، المصنع 196.
- **محور الاتجاه (استكمال D4):** `DateBucket::bucketExpression` صار يقبل عمود الأساس، و`DashboardSeriesQuery` يمرّر `COALESCE(orders.last_status_changed_at, orders.created_at)` على محور «آخر حالة» وتاريخ الإنشاء خلاف ذلك؛ التوصيل مثبّت على تاريخ الإنشاء عبر المصنع.
- **الاختبارات (المهمة 7):** طلب مشحون يبقى داخل المعدّل (75%)؛ اختيار المنتج ومحور آخر حالة يحرّكان كل كتل التأكيد ولا شيئًا من التوصيل؛ الفلتر المخفي (الناقل/المنتج) لا يؤثّر؛ `on_hold/paid/draft` في `other` فقط؛ لا `dark:`/`gray-*` في قوالب التأكيد.
- **مجموعة MySQL (المهمة 6):** أُنشئ `phpunit.mysql.xml` (يستهدف `edzeery_test` المستقلّة، لا قاعدة التطوير) ويضم وحدة التحليلات وملفات `Dashboard*`/`Confirmation*`؛ اختبارات `DateBucket` التي تنفّذ SQL حقيقيًا تُتخطّى خارج sqlite. النتيجة على MySQL 8: 136 ناجح / 9 متخطّى.
- **التحقق:** `pest --filter="Dashboard"` 157، `--filter="Confirmation"` 82، `tests/Unit` 145؛ MySQL 8 (136/9)؛ `npm run build` نظيف؛ `php -l` للقوالب المترجَمة نظيف؛ Pint نظيف.
- **معايير القبول المتبقية:** المراجعة البصرية للقطات 375/768/1440 (بيد المستخدم)؛ مزوّد CI/بيانات الاعتماد لتشغيل `phpunit.mysql.xml` تلقائيًا.

### 37-M1 — مفتاح الإسناد في التأكيد + إزالة محتوى التوصيل (منفّذ، 2026-10-12)
**D1:** مفتاح عرض التأكيد = `orders.assigned_to_membership_id` (الفلتر/العنوان/البطاقات/الدائرة/الاتجاه/صف KPI/بطاقات الفريق)، والسنيتنل «غير مُسنَدة» = `assigned_to IS NULL`. **D2:** لا محتوى توصيل في التأكيد — أي حالة تجاوزت التأكيد (shipped…unclaimed) تُحسب داخل `confirmed`، فالبطاقات تطابق المعدّل (ديمو: 6 من 7 = 86%). مفتاح التوصيل **دون تغيير** (`confirmed_by`، byte-for-byte)، وتحويله إلى إسناد التتبّع مسألة مفتوحة خارج نطاق M1.
- **`DashboardOrderScope::applyMember`:** اختيار العمود حسب `memberDimension` (`delivery` ⇒ `confirmed_by`، وإلا ⇒ `assigned_to`)؛ السنيتنل/القائمة الفارغة على العمود النشط.
- **`ConfirmationStatsGroups`:** أُعيد ترتيبه — MAP يحوّل shipped…unclaimed إلى `CONFIRMED`؛ `all()` = [confirmed, pending, failed, canceled, deferred, duplicate, other]؛ غير المعرّف ⇒ `OTHER`؛ `NUMERATOR_GROUPS = [CONFIRMED]`؛ `PASSED_TO_DELIVERY` بقي كثابت داخلي غير معروض.
- **`ConfirmationRateDenominator`:** `DEFAULT_GROUPS = [confirmed, canceled, failed]`؛ `PRESET_EXCLUDE_DUPLICATE_AND_DEFERRED = [confirmed, pending, failed, canceled, other]`.
- **العرض:** `confirmation-stats.blade.php` و`confirmation-team.blade.php` بلا شريحة/عمود توصيل؛ `DashboardStatusBreakdown::forConfirmation` بلا مفاتيح ألوان ميتة؛ `DashboardTrendQuery::confirmedIds = keys(CONFIRMED)`؛ حُذف `not_sent` من `DashboardConfirmationTeam`؛ سنيتنل التأكيد يعرض `team_unassigned` («غير مُسنَدة») عبر `$typeLabels`.
- **الترجمة (ar/en/fr/es):** تحديث `group_confirmed_hint`/`outside_rate`/`confirmation_rate_explainer`؛ حذف `group_passed_to_delivery`/`group_passed_to_delivery_hint`/`group_unclassified`/`group_unclassified_hint`/`team_not_sent`.
- **الاختبارات:** `ConfirmationStatsGroupsTest` 5/5، `ConfirmationRateDenominatorTest` 3/3، `DashboardConfirmationTeamTest` (feature) 9/9، `DashboardConfirmationSectionTest` 9/9، `DashboardChartsPerViewTest` 9/9، `DashboardFilterTest` 18/18، `DashboardFilterWiringTest` 19/19.
- **معايير القبول المتبقية:** المراجعة البصرية للقطات 375/768/1440 (بيد المستخدم)؛ اعتماد MySQL/CI؛ ألوان/رسوم/CI في **37-M2**.

### 37-M2 — ألوان متاحة، رسوم/legends آمنة، وإزالة التكرار + CI (منفّذ، 2026-10-12)
**D3:** لون واحد لكل مجموعة يُعرَّف مرّة واحدة في `config/analytics.php 'group_colors'` (أسماء توكنات) ويُستهلك في البطاقات والدائرة والاتجاه وصفوف الفريق و legends. الاختيار: confirmed=`success-600`، pending=`warning-600`، failed=`danger-600`، canceled=`gray-700`، duplicate=`accent-600`، deferred=`info-500`، other=`gray-500` (accent-600 = #7c3aed، info-500 = #0284c7؛ لا تعديل `tailwind.config.js`/`app.css`).
- **T1 (تباين):** إزالة كل نص مخفّض (`text-ink-muted/<opacity>` وغيرها) → توكنات متاحة (`text-ink-muted` = 4.97:1). لا `dark:`، لا gray خام — يفرضه اختبار الحرس.
- **T2 (رسوم/legends):** الاتجاه التأكيدي صار **7 أعمدة مكدّسة** (`DashboardTrendQuery::confirmation()` يحلّق على `ConfirmationStatsGroups::all()`؛ أعمدة عند buckets ≤62 وإلا خطوط) مع الإجمالي في tooltip؛ legends **HTML من الخادم** في `charts.blade.php` بصيغة `label — <bdi>count</bdi> (<bdi dir="ltr">pct%</bdi>)` و`legend: { display: false }`؛ فواصل الشرائح 2px بلون البطاقة؛ ألوان الدائرة/الاتجاه من `group_colors`.
- **T3 (إزالة التكرار):** بطاقة الطابور وشارة العدد في بطاقة واحدة (`pending-orders.blade.php` رأس يضم شارة `dashboard.pending_confirmation_count`)؛ حُذفت بطاقة العدد المستقلّة من `kpi-confirmation.blade.php`؛ شارة المعدّل تظهر مرّة واحدة (بطاقة المعدّل).
- **T4 (بطاقات الفريق):** `confirmation-team.blade.php` — core دائماً (confirmed/canceled/pending/failed)، والزائد (duplicate/deferred/other) فقط عند >0؛ شريحة/تلميح الحذف عند `>0` فقط؛ شبكة `grid-cols-1 md:grid-cols-2 xl:grid-cols-3`.
- **T5 (رابط المتجر):** نُقل من بطاقة أسفل الفلاتر إلى إجراءات صف رأس الفلتر (`filter-bar.blade.php`، `ms-auto`) لكلا العرضين (نسخ الرابط + زيارة المتجر).
- **T6 (CI):** أُنشئ `.github/workflows/tests.yml` بمهمتين — SQLite (`php artisan test`) وMySQL 8 عبر `services: mysql` (بلا أسرار) يشغّل `vendor/bin/pest -c phpunit.mysql.xml`. أُضيف `APP_DOMAIN=example.test` إلى `phpunit.mysql.xml` لتسجيل مسارات المتجر كما في `phpunit.xml`.
- **T7 (اختبارات):** `DashboardDesignContrastTest` (3) يحسب التباين من `resources/css/app.css`: كل لون مجموعة ≥3:1 وكل توكن نص ≥4.5:1 (فاتح)، ونص الوضع الداكن ≥4.5:1؛ اختبار الحرس أُضيف إليه منع `text-ink-muted/`/`text-ink-soft/`/`text-ink/`؛ legend `<bdi>` وقواعد بطاقة الفريق (إخفاء الصفوف/الشرائح الصفرية) في `DashboardChartsPerViewTest` و`DashboardConfirmationTeamTest`.
- **التحقق:** `--filter="Dashboard"` **161 نجاحًا (1013 تأكيدًا)**؛ `--filter="Confirmation"` **83**؛ `tests/Unit` **145**؛ `pint --test` نظيف على كل الملفات المعدّلة. تحديث اختبارات الاتجاه (7 series بدل received/confirmed/canceled/failed) في `DashboardFilterTest`/`DashboardAnalyticsTimezoneTest`/`DashboardChartsPerViewTest`.
- **معايير القبول المتبقية:** المراجعة البصرية للقطات 375/768/1440 (RTL/LTR، فاتح/داكن) بيد المستخدم؛ تشغيل الـworkflow فعليًا في GitHub.
