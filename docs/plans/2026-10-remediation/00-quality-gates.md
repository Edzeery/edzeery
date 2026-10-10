# 00 — Quality Gates (معايير الجودة الإلزامية)

> يُطبَّق هذا الملف **قبل أي إصلاح UI/CSS/Blade** وبعد أي تعديل كود. خالفته = رفض التغيير.

## 1) التوكينات والألوان

- [ ] لا تُستخدم ألوان hex خام جديدة في أي `blade`/`scss` — كل لون عبر توكينات `brand/success/warning/danger/info/gray/surface/ink` (طالع `tailwind.config.js:66-230` و`DESIGN_SYSTEM.md`).
- [ ] ألوان الحالة الحرجة (تأكيد حذف، تتبع) تُحل عبر توكينات `danger-*` لا عبر `#ef4444` (`components/edz/icon.blade.php:65`، ست مواقع `confirmColor:'#ef4444'` في `tracking-row-cell` و`tracking-mobile-card` — من المهمات A-D في `07`).
- [ ] عند إضافة لون حالة جديد ارفع في `config/statuses.php` أو `config/status-kit-statuses.php` بما يوافقه.

## 2) قاعدة dark/light

- [ ] القرار: **نعتمد `dark:` متغيرات Tailwind** (كما يوثّق `DESIGN_SYSTEM.md:45` و`panel.js:29-48` — مفتاح `edz-theme`).
- [ ] القاعدة المقابلة في بوابات التصميم (التي تمنع `dark:`) تُحدَّث عبر التوكينات المزدوجة — **لا نكتفي بخفض عدد الـ`dark:`** بل نضمن أن كل سطح له زوج light/dark سليم (مفتاح `edz-theme` init anti-FOUC في `<head>` لكل layout).
- [ ] لا تُضاف class=`dark:` لا تستحق؛ لا تحذف `dark:` من ملف يفرضها `DESIGN_SYSTEM` (مثال `storefront.scss`/`sf-scroll`).
- [ ] زر التبديل: `<x-dark-toggle />` يحفظ `localStorage edz-theme`؛ يجب أن يظهر في كل layout (panel + storefront + landing).

## 3) الحجم (Size caps) — سقف الخطوط

| الكيان | السقف |
|---|---|
| PHP class/Concern (app) | ≤ 250 سطر |
| service | ≤ 250 سطر |
| Volt blade (resources/views/livewire) | ≤ 400 سطر |
| partial (views/components) | ≤ 300 سطر |

- [ ] أي ملف فوق السقف **داخل خطة الصحة `09`** يُقسم — لا زيادة على السقف.
- [ ] الملفات فوق السقف اليوم: `orders/index.blade.php` (5,707)، `announced-rates` (1,373)، `TrackingRiderFormConcern.php` (1,002)، `order-form` (1,050)، `products/form` (874)، `orders-table-cell` (877) … القائمة الكاملة في `09-code-health.md`.

## 4) RTL / العربية

- [ ] `dir="rtl"` يُدار من `setRTL()`/`isRtl()` في `AppServiceProvider:93-96` — لا نصلّب اتجاهًا في blade.
- [ ] عمليات flexbox/بكسل لـ RTL عبر `rtl:`/`ltr:` (حسب `DESIGN_SYSTEM.md:48-51`).

## 5) استجابة الأحجام (Responsive)

- [ ] السلوك النقاطي: `sm ≥640`, `md ≥768`, `lg ≥1024`, `xl ≥1280`, `2xl ≥1536` (افتراضي Tailwind، لم يُعدّل في `tailwind.config.js`).
- [ ] الشبكات والطاولات الكبرى (orders-grid، tracking) تختبر على: 360px، 768px، 1024px، 1440px.
- [ ] لا تُستخدم `overflow-x-auto` كحل صدّ دون طاولة اختبار بأن التمرير الأفقي لا يقطع تدفق البيانات.
- [ ] أي إصلاح responsive يُكتب UAT: قاعدة بيانات صغيرة 3 متاجر → صورة لقطة على 4 نقاط أحجام (تسجيل في `STATUS.md`).

## 6) حقوق/أسماء الحقول (Naming)

- [ ] حالة `camelCase` للدوال، `snake_case` لمفاتيح Laravel، أسماء الحقول بـ `snake_case` في قواعد تحقق.
- [ ] لا تُسَمَّى متغيرات بـ`$data`/`$config` في أماكن استقبال مدخلات عميل أثناء تدفقات مصادقة — أعد التسمية إلزامًا إذا أُصلح أمان (نموذج `HasInlineEdit`).

## 7) جودة البيانات

- [ ] أي كتابة مالية: داخل `DB::transaction`؛ تُحدَّث النسب من قاعدة البيانات وليس من مدخل العميل (عودة إلى `03-data-integrity-money`).
- [ ] قواعد `file|mimes|image` إلزامية في أي `->store('…','public')` (راجع `01-security` مهام U-1..3).

## 8) ضمان الاختبارات

- [ ] قبل: `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe -d memory_limit=-1 vendor/bin/pest`
- [ ] بعد كل إصلاح: إعادة تشغيل النطاق المتأثر (Feature → ملف/مجلد الاختبار) ثم كامل الصيغة عند التقاط اللقطة (release).
- [ ] أي إصلاح أمني يضيف test regression (مثال: `tests/Feature/Merchant/StoreTeamCredentialsEmailTest.php`).