# STATUS — سجل المهام المنجزة (تحديث إلزامي بعد كل مهمة)

> القاعدة: بعد **كل** مهمة مكتملة، أضف سطرًا هنا بتاريخها + دليل التحقق + أعد حالة checkbox في وثيقة النطاق إلى `[x]`.
> التحديث يتم بواسطة المساعد بعد كل إصلاح، ويُعرض في بهو المحادثة.

التنسيق لكل إنجاز:
`- [x] {التاريخ} · {النطاق-الرقم} · {وصف} · التحقق: {اختبار/أمر}`

---

## 2026-10-09 (مرحلة التخطيط — لا تنفيذ بعد)

- [x] **S-01 🔴 P0 — RCE في `HasInlineEdit` أغلق**: تم التنفيذ 2026-10-09. التحقق: `php -d memory_limit=-1 vendor/bin/pest tests\Feature\Merchant` — 863 اختبار ناجح (1,172,171 تأكيدًا) + اختبار RCE جديد.
- [x] **S-02 🔴 P0 — إعادة تعيين كلمة مرور حسابات موجودة عبر `StoreTeamService` أغلق**: تم التنفيذ 2026-10-09. التحقق: `tests\Feature\Merchant` — 866 اختبار ناجح (3 إضافيين لـS-02)؛ `addMember` يرفض منح كلمة مرور فوق حساب موجود (fail-closed) و`updateMember` يتطلب `current_password`. `\$data`→`\$payload` (§6).
- [x] **S-03 🔴 P0 — ثغرة «جلسة قابلة للتسمم» بين المتاجر (عزل جلسة) أغلق**: تم التنفيذ 2026-10-09. التحقق: `tests\Feature\Security\StoreSessionIsolationTest` — 7 اختبارات جديدة (17 تأكيدًا) + `tests\Feature\Merchant` كاملة — 866 اختبار ناجح (1,173,050 تأكيدًا)؛ الكتابة لـ`current_store_id` فقط بعد تحقق العضوية (ResolveStoreFromRoute لا يكتب، EnsureStoreMembership يمسح الجلسة عند 403 ويستمر بنفسه بعد العضوية، StoreResolver يفحص العضوية النشطة قبل trust، ResolveStoreFromSubdomain لا يكتب الجلسة لغير الأعضاء).

## 2026-10-10 (تنفيذ T-03 + أساس T-02)

- [x] **T-03 🟠 P1 — عزل سياق API (لا host-subdomain بلا عضوية) أغلق**: `StoreResolver::resolveFromApi()`: `X-Store-Id` هو المصدر الوحيد؛ غيابه → 422؛ متجر أجنبي (أو host بلا عضوية) → 403 بلا fallback إلى الـsubdomain. التحقق: `tests\Feature\Security\ApiStoreContextIsolationTest` (5 اختبارات جديدة) + `StoreSessionIsolationTest` — 12 ناجح.
- [x] **T-02 🟠 P1 — أساس موحّد (قبل الدفعات)**: `StoreScope` أصبح **fail-closed** (سياق متجر → تصفية صارمة حتى للأدمن؛ بلا سياق → أدمن يقرأ الكل، غيره صفر صفوف)، `StoreOrGlobalScope` للجداول ذات `store_id` nullable، `BelongsToStore` (يسجّل السكوب + يملأ `store_id` عند الإنشاء أو يرمي `MissingStoreContextException`)، `UsesStoreOrGlobalScope`، `StoreContext::has()/runAs()`، ومخارج `withoutStoreScope()`. تطبيق: `Product`, `Debt`, `DebtPayment`. أدوات الاختبار: `actingInStore()`. التحقق: `tests\Feature\Tenant\StoreScopeTest` (13 اختبارًا) + كل `tests\Feature` — **11 فشل قديم (ecotrack) فقط، صفر فشل جديد (1292 ناجح)**.
- ⏳ **T-02 الدفعات المتبقية**: (1) Order/OrderItem/OrderTracking/Customer<sup>*</sup>؛ (2) Payment/Invoice/InventoryMovement/Returns؛ (3) Brand/Category/ProductVariant/ProductOption؛ (4) ShippingProvider/StopdeskPoint/DeliveryRider/Status. ثم اختبار البنية (كل جدول فيه `store_id` يستخدم الترايت أو مستثنى بسبب مبرّر) + اختبار قائمة `withoutStoreScope`.
- ⚠️ **ملاحظة نطاق:** `order_status_histories` **لا يملك عمود `store_id`** (يُعزل ضمنيًا عبر `order_id`) → خارج نطاق T-02. الدفعة (1) جُرّبت فعليًا (إضافة الترايت + ربط `withoutGlobalScope` للعلاقات + إصلاح مولّد رقم الطلب) لكن نطاق التصادم بلغ **118 فشلًا في ~40 ملف اختبار** علاوة على خدمات/jobs/webhooks (سكوب علاقات الأبناء، `firstOrCreate` بلا سياق، تحميل بالمعرّف) → **تم التراجع** للاحتفاظ بحالة خضراء مستقرة (11 فشل ecotrack قديم فقط). القرار: اعتماد استراتيجية الترحيل (كامل مقابل allowlist بمبرّر) قبل إعادة التطبيق.

## سجل التخطيط/الفحص (قراءة فقط — تمت في هذه الجلسة)

| النطاق | الحالة | الملاحظة |
|---|---|---|
| 01-security | 🟢 قيد التنفيذ | S-01، S-02، S-03 مكتملان؛ التالي S-04 |
| 02-auth-session | 📋 مخطط | A-01..A-05 + PR-01..PR-11 (تقوية استعادة كلمة المرور) |
| 03-data-integrity-money | 📋 مخطط | M-01..M-05 |
| 04-tenant-isolation | 🟢 قيد التنفيذ | T-03 مكتمل؛ أساس T-02 مكتمل، الدفعات (1)–(4) جارية |
| 05-ops-deps-infra | 📋 مخطط | O-01..O-09 |
| 06-performance | 📋 مخطط | P-01..P-07 |
| 07-design-system-apple | 📋 مخطط | DS-01..DS-20 + قرارات مفتوحة |
| 08-fintech-program | 📋 مخطط | F-01..F-05 |
| 09-code-health | 📋 مخطط | H-01..H-06 |

## قرارات المستخدم (مقررة 2026-10-09) — أساس التنفيذ

1. ✅ **dark:** اعتماد أسلوب `dark:` Tailwind (الغالب في المشروع) وتحديث قاعدة القيد في `00-quality-gates` (بدل حذف `dark:` الكلاسات). المرجع: `DESIGN_SYSTEM.md` + `panel.js` (edz-theme).
2. ✅ **الأيقونات:** اعتماد `<x-edz.icon>` (inline SVG) وتحديث `DESIGN_SYSTEM.md` (إزالة ذكر Ionicons).
3. ✅ **التبعيات:** ترقية composer/npm في لقطة معزولة (S-10/O-01) — منفصلة عن إصلاحات الكود.
4. ✅ **الملفات المؤقتة:** حذف مؤقتات root/storage إن لم تعد لازمة (قائمة H-05/O-08) — قبل الحذف تُعرض القائمة.
5. ✅ **الديون (F-02):** **إزالة الديون من Edzeery نهائيًا** (لا إبقاء ملكية). تُحذف مكونات Debt/DebtPayment من النطاق بالكامل.
6. ✅ **المكتبات:** إضافة larastan/phpstan (H-04) + 2FA (A-04) بموافقة صريحة.
7. ✅ **استعادة كلمة المرور (2026-10-10):** إبقاء تدفق «رابط البريد» وتقويته؛ **لا OTP** (بريد/SMS) ولا تعديل schema. الخطة المرصودة: `PR-01..PR-11` في `02-auth-session.md` (A-01/A-02 مُدمجان).

## كيف تلتزم هذه الحالة؟

تُحدَّث هذه اللوحة تلقائيًا بعد كل مهمة "مكتملة" (checkbox → [x] + سطر التاريخ). يعرض المساعد الإنجاز في نهاية كل جلسة عمل.