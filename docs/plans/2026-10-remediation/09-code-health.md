# 09 — Code Health & Refactor (صحة الكود/حجم الملفات/التنظيف)

> الهدف: تطبيق السقوف (PHP ≤250, service ≤250, Volt ≤400, partial ≤300)، إزالة الموتى، سدّ فجوات i18n، إضافة phpstan، تنظيف المخلفات.
> المصدر: `docs/audits/2026-10-readiness-audit.md` A26/A27/A29/A30/A32/P3 + `00-quality-gates.md` §3.
> البوابات: `00-quality-gates` §3/§8.

---

## H-01 🟡 — تقسيم أكبر الملفات (السقف Volt 400 / PHP 250)

القائمة من التدقيق (لقطة 2026-10):

| الملف | السطور | السقف |
|---|---|---|
| `resources/views/livewire/merchant/orders/index.blade.php` | 5,707 | 400 |
| `resources/views/livewire/merchant/announced-rates.blade.php` | 1,373 | 400 |
| `app/Livewire/Concerns/TrackingRiderFormConcern.php` | 1,002 | 250 |
| `resources/views/livewire/storefront/order-form.blade.php` | 1,050 | 400 |
| `resources/views/livewire/merchant/products/form.blade.php` | 874 | 400 |
| `resources/views/livewire/merchant/delivery/providers.blade.php` | 750 | 400 |
| `resources/views/livewire/account/billing.blade.php` | 742 | 400 |
| `app/Livewire/Concerns/TrackingDrawerConcern.php` | 676 | 250 |
| `app/Console/Commands/FinanceCaptureHealth.php` | 466 | 250 |
| `app/Domains/Shipping/Adapters/NoestIntegrationAdapter.php` | 809 | 250 |

**خطة التقسيم لكل ملف (موحّدة):**
1. Volumeلتقنيات: يحتوي كل ملف منطقًا متماسكًا يظهر جزء من قائمة — انقل إلى `Concern` أو `partial`/`component` blades.
2. brand مؤخرات: أعد تجميع القسم دون تغيير سلوك (بدون refactor سلوكي بالتزامن مع الأمان).
3. كل سحق يجب أن يعمل كامل الاختبارات في الملف المرتبط.

**التوصية الوقائية:** لا تقسم أثناء جلسة المرحلة الأمنية P0 — واجعل JIRsha master لبناء العزلة.

---

## H-02 🟡 — كلاسيك المحذوفة (موتى)

من القائمة (مرجع التدقيق): 
- `app/Actions/Product/BuildEditFormDataAction.php` — لاحظ: **يُستخدم؟** في `products/form.blade.php:82` `app(ProductService::class)->buildEditFormData($product)` — هذا **ليس** Action، استبانة قبل الحذف.
- `app/Actions/Product/CreateProductAction.php`
- `app/Domains/Billing/Actions/RenewSubscriptionAction.php`
- `app/Domains/Merchant/Actions/GetDashboardStatsAction.php`
- `app/Domains/User/Actions/CreateUserAction.php`
- `mail/InvoiceMail.php`, `Notifications/StoreStatusChanged.php`
- `Middleware/Merchant/EnsureMerchantAccess.php`, `View/Components/GuestLayout.php`, `RoleBadge.php`, `Providers/ConsoleServiceProvider.php`

**قبل الحذف:** grep لكل اسم عبر `app/**` و`resources/views/**` و`routes/**`. من لا يظهر → احذفه بعد موافقة. إن ظهر استبانه وأخبرك.

- مكونات blade ميتة: `components/modal`, `ui/modal`, `dropdown`, `header/user-dropdown`, `header/notification-dropdown`, `components/status` — نفس فحص الـ grep لـ `<x-modauld?>...` (انتبه: `ui/modal` في `DESIGN_SYSTEM.md:110` Preserved → لا تُحذف).

---

## H-03 🟡 — فجوات الترجمة i18n

من التدقيق:
- `resources/lang/es/products.php` — 64 مفتاح ناقص (144/214).
- `resources/lang/fr/subscription.php` — 5 status keys ناقصة.
- `landing.php` في fr/es — ناقص `contact_email/get_started`.
- `inventories.php` fr — 2.

**الطريقة:** قارن مفتاحًا بمفتاح مع `en` (المرجع)، أكمِل صفوفًا جديدة بترجمة صحيحة يدوية (لا آلي) — واربطhardd في الأكثر استخدامًا. بعد: `php artisan translations:update`.

**قبول:** مقارنة diff: أصفار مفاتيح مفقودة عبر `ar/fr/es` مقابل `en` في الملفات الأربعة.

---

## H-04 🟡 — PHPStan/Pint أدوات مضمنة

- أضف `larastan/larastan` (تثبيت يحتاج موافقة لإضافة جديدة إلى composer).
- أضف `phpstan.neon` مستوى 5 مبدئي، تقييد `app/`.
- شغل `vendor/bin/pint` و`vendor/bin/pint --test` للتنسيق المستمر — حالياً 5 test files لا ترضي Pint (`VariantOrderingRulesTest`, `DemoStoreSeederTest`, `StoreSlugGuardTest`, `BladeInteractivityPolicyTest`, `CarrierPublicTrackingUrlTest`) — صلّح التنسيق (لا منطق) لهد tou explicitly لباخرة.

---

## H-05 🟡 — تنظيف المخلفات في جذر المستودع وstorage

كما في O-08: حذف `junit_*.xml`, `tmp_*`, `swal-repro.html`, `tmp_run_merchant.ps1`, `_read_tracking.ps1`, `Todos.md` 616KB (أعد بنائه إلى docs/؟ القرار للمستخدم). عدم حذف أي ملف دون موافقة — أعد القائمة بأمان ودعها توافق.

---

## H-06 🟡 — اتساق أسماء/كونفنت

- إزالة `x-merchant.body/sidebar` (لا توجد الآن، راجع `Todos.md:15,33,62` القديمة) — الملفات ميتة من قبل.
- تحديث `Todos.md:86` (ادعاء مكرر عن LoginRedirectService — أزيل بالفعل).
- توجه بالسقف في `ErrorsTodo.md`/`MerchantPanelAudit.md` — لا تبني منهج جديد.

---

## مرجعية

| المهمة | الوثيقة |
|---|---|
| H-01 | `00-quality-gates` §3 + audit A26 |
| H-02 | DB مخطط + audit A29 |
| H-03 | `livewire-conventions.md` §111-115 + audit A27 |
| H-04 | `composer.json` + audit A32 |
| H-05/06 | `00-quality-gates` + `docs/audits/...` A30 |

## قبول المرحلة

- [ ] كل ملف فوق السقف مُقسَّم أو documented waiver موقَّع.
- [ ] أصفار مكونات/كلنام ميتة تظل بعد حذف موافق.
- [ ] `pint` و `phpstan` (ًا بعد إضافتها) يعملان.
- [ ] i18n: فرق=0 مقاربة.