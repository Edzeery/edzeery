# 05 — Ops, Dependencies & Infra (عمليات/تبعيات/بنية)

> الهدف: ترقيات تبعيات، رؤوس أمان، دستور `.env`، قوالب/صفّ الطابور، ويب بوك الشحن آمن.
> المصدر: `docs/audits/2026-10-readiness-audit.md` A3/A4/A12/A13/A19/A20/A22.
> البوابات: `00-quality-gates` §8.

---

## O-01 🟠 P1 — تحديث التبعيات PHP وNPM (معزولة عن تغييرات المعنى)

1. `composer update` محدود إلى الحزم المستهدفة بالتحذيرات العالية: `laravel/framework`, `guzzlehttp/guzzle`, `symfony/http-kernel`, `ueberdosis/tiptap-php`, `dompdf/dompdf`, `filament/*`, `livewire/*` — ثم:
   - `composer audit --locked` (هدف: أصفار عالية أو موثّق فقط).
2. `npm`:
   - `axios` (SSRF NO_PROXY)، `swiper` (prototype pollution critical)، `@vue/server-renderer` (XSS)، `@tailwindcss/typography` (high) → `npm update` ثم `npm audit` (هدف: أصفار critical/high أو acceptance أمامك).
3. **حكم نقاط اختبار:** بعد تحديث الحزم، شغّل مجموعة اختبارات كاملة قبل أي إصلاح أمني آخر (ور-env غير متغير).

**لا تنفذ في نفس اللقطة مع إصلاحات كود أمان — كل مرحلة لقطة مستقلة.**

---

## O-02 🟠 P1 — رؤوس أمان محسوبة (middleware)

**أنشئ** `app/Http/Middleware/SecurityHeaders.php` ثم سجّل عالميًا في `bootstrap/app.php:22` (`$middleware->append(...)` أو global).

الرؤوس (باستثناء ما ينافي عمل `app.css`/`storefront.scss`):
- `Content-Security-Policy`: أساسي (default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' — تحديد قبل ميسر مع Vue/Alpine/Livewire).
- `X-Frame-Options: DENY` (أو SAMEORIGIN عن أداة طباعة البطاقات؟ يقرأ DestopProps: طبع البطاقة يظهر inline في tab لا iframe — DENY آمن).
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Strict-Transport-Security` على prod (HTTP: max-age 31536000; includeSubDomains; preload — تنفيذ في prod فقط وليس على dev بدون TLS).

**تحقق:** `phpunit.xml` الحالي يعمل على localhost بدون TLS → لا تفرض HSTS على dev config. أضف حالة عند `‘production’` في config/deploy.

---

## O-03 🟠 P1 — TrustProxies

**الملف:** `bootstrap/app.php:22` — `trustProxies(at: '*')`.
**الإصلاح:** أجّل إلى قائمة CIDR محددة صحيحة لموازن الحمولة/CDN في prod؛ في dev اترك بدون حرف عام (اشطب `*` أو ضع empty). بعدها اختبار `order-form` 10/دقيقة يحسب على VPN الحقيقي لا على `X-Forwarded-For`.

---

## O-04 🟡 — تقييد `.env` وإزالة الأوضاع غير الآمنة

**ما هو مطلوب (وليس leak):**
- `.env` غير متتبع ✓ (مؤكد).
- في ملف `.env` المحلي: `APP_ENV=local`, `APP_DEBUG=true`, `SESSION_SECURE_COOKIE=false` — ثبّت أن `.env` للإنتاج غير مشابه.
- `.env.example` لا يتضمن `SESSION_SECURE_COOKIE` — أضف خطًا تعليميًا به القيمة الآمنة `true` (تعليق) ليعلم المنسوخ.

**لا تنفذ تغيير الـ`.env` الحالي — فقط التوثيق +`.env.example`.** أي تعديل `.env` يحتاج موافقة صريحة.

---

## O-05 🟡 P2 — ويب بوك الشحن: HMAC بدل توكن في URL

**الملف:** `app/Http/Controllers/Api/Webhooks/DeliveryWebhookController.php:84-88,99-101` + `routes/api.php:44-55`

**الإصلاح:**
1. رايف `webhooks.delivery` يقبل فقط `X-Delivery-Signature` (HMAC للـ payload بقلم provider `webhook_secret`).
2. احذف fallback `?token=` و token-in-path (أزل legacy بعد مهلة ترحيل موثقة).
3. terminal guard: `NoestTrackingSyncService::apply` لا يرجع عن حالة نهائية (فوق README — تحقق من وجود guard، إن غاب أضف).
4. Test: replay قديم بلا sign → 401; تكرار حدث بعد `delivered` لا يعكّبه.

---

## O-06 🟡 P2 — `webhook_last_seen_at` وثبوت القبول

**الملف:** `DeliveryWebhookController.php:34`
**الإصلاح:** لا تُحدّث `webhook_last_seen_at` قبل نجاح التحقق/ثبات المعالجة — انقله بعد `apply`.

---

## O-07 🟡 P2 — طابور مع timeout/tries + PDF خارج الطلب

**الملفات:** `app/Domains/Order/Jobs/*.php`، `app/Domains/Shipping/Jobs/*.php` (طلب timeout)، `app/Services/InvoicePdfService.php:31`

**الإصلاح:**
1. كل `ShouldQueue` يحصل `public $timeout` و`public $tries` (بناءً على نوع العمل).
2. توليد الفاتورة/الملصق/التصدير يحوَّل إلى Job + `failed_jobs` يُراقَب (لوج لوحة مراقبة).
3. (اختياري) لديك `laravel-status-kit` — قيصًا للفشل؟

---

## O-08 🟡 P2 — تنظيف tmp/debug من storage وroot

- حذف `storage/app/root_debug_count_*.html`, `debug-catalog.html`, `diag_roots.php`, `DiagStorefrontRoots.php`, `original_storefront.txt`.
- حذف جذور الـ repo الظرفية: `junit_inventory.xml`, `tmp_full_suite_junit.xml`, `tmp_full_suite_report.txt`, `tmp_junit_*.xml`, `swal-repro.html`, `tmp_run_merchant.ps1`, `_read_tracking.ps1` — (انظر أيضًا `09-code-health` O-10) — بعد موافقة.

---

## O-09 🟡 P2 — CSV/Excel إخراج بدون حقن صيغ

**الملفات:** `app/Filament/Exports/{City,Country,State}Exporter.php`
**الإصلاح:** أي خلية تبدأ بـ `=`, `+`, `-`, `@` يسبقها `'` (أو استخدم Spatie writer import ووثِّق سلوكه — تحقق في Filament v4 سلوك `Csv::`).
**قبول:** تصدير اسم يبدأ `=cmd|...` → خلية نصية في Excel.

---

## مرجعية

| المهمة | الوثيقة |
|---|---|
| O-01 | `00-quality-gates` §8 + audit deps |
| O-02/03 | `00-quality-gates` §5 + `bootstrap/app.php` [جدول] |
| O-05/06 | `docs/plans/order-distribution-rules.md` + `docs/audits/...` A4 |
| O-07 | `docs/livewire-conventions.md` §12 (sys tasks) |

## قبول المرحلة

- [ ] `composer audit` & `npm audit` بلا critical/high جديدة أو مستندة.
- [ ] رؤوس المجموعة ظاهرة في استجابة http.
- [ ] لا `X-Forwarded-For` يسمّم القيد.
- [ ] ويب بوك HMAC + terminal guard.
- [ ] test suite خضراء + STATUS تحديث.