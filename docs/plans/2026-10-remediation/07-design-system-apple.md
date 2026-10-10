# 07 — Design System, Apple Identity & Responsive (الهوية البصرية/تصميم Apple/أحجام الشاشات)

> الهدف: ضبط المستودع على معايير `DESIGN_SYSTEM.md` — مبادئ **Apple Design Language**، توكينات مزدوجة light/dark، واتساق كامل عبر أحجام الشاشات مع RTL.
> المصدر: `DESIGN_SYSTEM.md`، `docs/livewire-conventions.md` §7/§16، `docs/audits/2026-10-readiness-audit.md` A28/A31.
> البوابات: `00-quality-gates` §1/§2/§4/§5.

---

## قرار القاعدة الحرج (BASE)

> **قرار اتُخذ داخل هذه الوثيقة (يُعرض على المستخدم قبل تنفيذ أي تغيير):**
> نعتمد أسلوب **`dark:` متغيرات Tailwind / class strategy** كما يDOCUMENT `DESIGN_SYSTEM.md:45` و`resources/js/panel.js:29-48` (مفتاح `edz-theme`).
> لذلك القاعدة الرابطة ضد `dark:` — التي جعلت 808 شخصًا يظهرون كـ «انتهاكات» — تُعالج لا بحذف `dark:` بل بـ:
> 1. توحيد كل سطح عبر توكين مزدوج (لكل `--edz-*` قيمة light و dark) — موجود أصلًا.
> 2. تحديث قواعد الـ gate (مثال `NoDarkRule`) لتصبح «يجب أن يتزامن مع تعريف TOKEN مطابق» بدل «no dark:».
> 3. جرد كل `dark:` المتبقية مقابل توكنات `app.css:48-...` (تتوفر light/dark أزواج) وضبط البقايا.

---

## 1) المعايير المرجعية — Apple Design (كما في DESIGN_SYSTEM.md)

| المعيار | المستند | واقع اليوم |
|---|---|---|
| Clarity (خط Inter + IBM Plex Sans Arabic) | `tailwind.config.js:53-58` | موجود ✓ |
| Deference (content-first) | — | راجع صفحات مثل grid — لا أزرار تعقيد هائلة |
| Depth (backdrop-blur, layered shadows) | توكينات `--edz-shadow-*` في `tailwind.config.js:241-256` | موجود ✓ |
| Motion (apple-out / apple-spring) | `tailwind.config.js:257-266` | موجود ✓ |
| Rounded (radius) | `tailwind.config.js:232-240` | موجود ✓ |
| Icons: Ionicons 7.1.0 (web component) | `DESIGN_SYSTEM.md:34` | تأكد توابق الدخول مع `<x-edz.icon>` inline SVG — CONFLICT تنظيفي: DESIGN_SYSTEM يقول Ionicons، لكن `livewire-conventions.md:79` يقول inline SVG (`<x-edz.icon>`) — **قرار: أصلّ استخدام `<x-edz.icon>` (مكتبة موجودة وأكثر أمنًا/أداءً) ويُحدَّث DESIGN_SYSTEM.md بعده** — يُعرض على المستخدم. |
| Store theming (`.store-btn-*`, `--store-primary`) | `DESIGN_SYSTEM.md:36-40` | موجود ✓ |

**مهمات من هذه القاعدة:**
- DS-01: تحديث `DESIGN_SYSTEM.md` §Component icons لتعكس `<x-edz.icon>` 1:1 (بعد موافقة على القرار).
- DS-02: جرد توكينات store theme: تأكد أن كل `store-btn-*` له زوج dark (انظر `.sf-scroll` في `app.css:32-40` الطريقة المرجعية).

---

## 2) تطهير الألوان الخام (hex)

**الإحصاء:** 103 hex في 18 blade؛ الحرجة:
- `components/edz/icon.blade.php:65` — `stroke="#ef4444"`
- `confirmColor:'#ef4444'` في 6 مواقع.`tracking-row-cell.blade.php:84,102,127` + `tracking-mobile-card.blade.php:100,118,142`

التوصية:
- DS-03: كل `confirmColor` يستخدم `'var(--edz-color-danger-500)'` أو `danger-500` توكن — ليتفاعل مع الوضعين.
- DS-04: `icon.blade.php` نمرّر `class="text-danger-500"` بدل stroke هارد.
- DS-05: باقي الـ103 تُكشف في قائمة (grep) وتُستبدل بالتوكن الأقرب.

**القابلية للتحقق:** تشغيل grep: `rg -o -- '#[0-9a-fA-F]{3,8}' resources/views/livewire resources/views/components` → لإنتاج sheet؛ ثم وراءها أصفار.

---

## 3) الأوضاع الداكنة/الفاتحة — قائمة تنفيذ

- DS-06: مراجعة init anti-FOUC في كل layout (storefront، panel، landing) — موجود حسب `livewire-conventions.md:99-106`؛ تأكد أن `<x-dark-toggle>` في topbar+storefront+landing.
- DS-07: جرد كل استخدام `dark:` (808) مقابل توكنم. أنشئ checklist:
  - النواطة الطينية العمومية (`bg-white dark:bg-...`):  — توكين `bg-surface`.
  - النصوص (`text-gray-900 dark:text-gray-100`) — توكين `text-ink`.
  - يجب أن تنتج جردة «لا hex» بعد DS-05.
- DS-08: سايتها عوارض: تأكد `store-dark-*` لكل متجر (وَالتي قد لا تكون موجودة — أضف قسم Doc لتعريف متغيرات store لدعم dark).
- DS-09: `system preference` fallback (في حالة toggle غير محس) — موجود في `panel.js:32`.

---

## 4) التوافق مع أحجام الشاشات (Responsive) — خطة فحص/إصلاح كاملة

**النقطة:** استخدم توكينات breakpoints الافتراضية (640/768/1024/1280/1536). صفحات حرجة يجب فحصها على 4 أحجام:

| الصفحة | الحواف المعروفة | المطلوب |
|---|---|---|
| Storefront catalog/templates | grid متعدد الأعمدة | تجربة `sm:grid-cols-2`, `md:3`, `lg:4` على كل template |
| Order form (checkout wizard) | أسطر وselects | تجربة max-w و `sm:` على العمود الواحد |
| Merchant orders grid | جداول عريضة | `overflow-x-auto` + أعمدة قابل للطي عبر `hidden md:table-cell` |
| Tracking (drawer/tabملات) | drawer width | `w-full sm:max-w-xl` |
| Panel sidebar | sidebar width (توكين `--edz-sidebar-width`) | `sidebar_collapsed` في 768-1024 |
| Modals | z-index layers | `w-full sm:max-w-lg` نمط موحد |

- DS-10: جرد أعمدة `hidden md:` لتقليل المحتوى على الجوال وتحديد «أعمدة أساسية» في الجداول.
- DS-11: جرد `px-*` المدى: لا أقفاص `px-2` منفردة على ملء الشاشات — توحيد spacing عبر `--edz-*` spacing tokens إن وجد (فارغ ننشئ توكن `--edz-gutter`).
- DS-12: مقاومة تحفظ: يدوي؟ ننشئ `tests/Browser/...` إن وُجد Pest/Pest Browser handler؟ غير موجود — نستخدم اختبار عقد لعينة الصور.

---

## 5) RTL/Writing direction

- DS-13: كل flex درايفر من RTL أو LTR عبر `dir` من `setRTL()` — لا ص：[`left`/`right` هارد مستثنى إلافي حالات واعتبارات واضحة].
- DS-14: كل `translate-x` أو origin لـ dropdowns يستخدم `rtl:`/`ltr:` (وفق `DESIGN_SYSTEM.md:50`).

---

## 6) الوصولية (a11y) — خطة

- DS-15: 28 صورة بلا `alt` في القوالب → إضافة alt وصفي (لا تجاهل `alt=""` للأيقونات الزخرفية).
- DS-16: أزرار أيقونات بلا label (71 موقع بدون aria-label) → حدد `aria-label` من النصوص المترجمة.
- DS-17: focus states: تأكد `focus:ring-2` (توكينات) على كل أزرار — موجود أصلًا للـ Inputs في `DESIGN_SYSTEM.md:32`.
- DS-18: تباين: راجع توكن dark `--edz-color-text-muted` في dark (يرجع low contrast 98a2b3 on 0b0f19 — نجعل `#98a2b3` على `#101828` أفضل، راجع نسب WCAG AA تدريجيًا في تمريرة واحدة).

---

## 7) محتوى قابل للتخصيص (store theming)

- DS-19: أضف المستند `docs/plans/store-theming-rules.md` جديد (إن لم يوجد) يوثق contract: `--store-primary/secondary/font`, `.store-btn-*` classes, auto-contrast. راجع أن المستخدم يوافق على إنشاء مستند جديد.
- DS-20: تحقق أن `create-store.blade.php` (متجر جديد) يمدّد توكينات store عبر `StoreThemeSetting` (جزء من متطلبات الهوية).

---

## مرجعية

| المهمة | الوثيقة |
|---|---|
| DS-01..04 | `DESIGN_SYSTEM.md` + `00-quality-gates` §1 |
| DS-06..09 | `livewire-conventions.md` §97-109 + `panel.js` |
| DS-10..12 | `00-quality-gates` §5 |
| DS-13..14 | `DESIGN_SYSTEM.md:48-51` |
| DS-15..18 | `docs/audits/...` A31 |
| DS-19..20 | `docs/plans/financial-accounts.md` (تزود المتجر) |

## قبول المرحلة

- [ ] Command binary: لا hex جديد؛ لا color رمادي في blade.
- [ ] جدولة الصور: 4 أحجام لكل صفحة حرجة موثقة في `STATUS.md`.
- [ ] a11y: أصفار؛ كل opat-صيغة مركزة.
- [ ] dark toggle يعمل بلا FOUC في الثلاث layouts.