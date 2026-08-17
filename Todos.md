# Todos.md — خطة تنفيذ Edzeery الكاملة

> نتيجة تدقيق كامل للريبو الحالي (Edzeery/edzeery). كل بند أدناه مبني على فحص فعلي للكود،
> وليس افتراضًا. نفّذ الأقسام بترتيبها؛ كل قسم قابل للصق مباشرة كبرومت مستقل في Claude Code.
> اعرض نتيجة كل قسم كاملة قبل الانتقال للتالي، وحدّث هذا الملف (✅/⬜) عند إنجاز كل بند.

---

## القسم 0 — نتائج التدقيق (سياق، ليس عمل تنفيذي)

- Livewire مطبّق فعليًا على **جميع** موارد التاجر الـ 9 (Products, Brands, Categories, ProductOptions, ProductOptionValues, ProductVariants, Inventories, InventoryMovements, StockAlerts, MyTeams). حُذف `app/Filament/Merchant` بالكامل — Filament لا يوجد الآن إلا للإدارة العامة (`SuperAdminPanelProvider`).
- `MerchantPanelProvider` (Filament، `path('merchant')`) لا يزال مسجّلاً ونشطًا **بالتوازي** مع `routes/merchant.php` الجديد (`Route::prefix('merchant')`) — نفس البادئة، نظامان مختلفان يعملان في نفس الوقت تحت `/merchant/*`
- يوجد نظامان مصمّمان للألوان يعملان بالتوازي في الكود:
  - **صحيح ومتّصل بالـ design tokens**: `bg-surface-bg`, `text-ink` — مستعمل فقط في `components/layouts/panel.blade.php`, `components/layouts/merchant.blade.php`, وكل `resources/views/livewire/merchant/*`
  - **غير معرَّف إطلاقًا في `tailwind.config.js`** (كلاسات لا تُنتج أي CSS فعلي، أي بدون أي خلفية/لون نص حقيقي في الوضعين): `bg-neutral-bg`, `dark:bg-dark-bg`, `text-neutral-text`, `dark:text-dark-text` — مستعمل في ~23 ملفًا تشمل: الصفحة الرئيسية بالكامل (`landing/*`)، `layouts/app.blade.php`, `landing-layout.blade.php`, `navbar`, `guest`, `footer`, كل مكونات الفورم العامة (`text-input`, `input-label`, `secondary-button`...)، ونسخة قديمة مكرّرة من لوحة التاجر (`components/merchant/body.blade.php` و`sidebar.blade.php`) لم تُحذف بعد نقل التاجر الحقيقي إلى `livewire/merchant/layout/*`
- الصفحة الرئيسية (`landing/index.blade.php`) أساسية جدًا: hero (24 سطر)، services (38 سطر)، payments (32 سطر)، plans (144 سطر) — ويوجد ملف مكرر زائد `plans.blade copy.php` يجب حذفه
- عنقود الديون والإشعارات من `Finance-Manager` (مطلوب في برومت سابق) لم يُنفَّذ إطلاقًا — `app/Domains/Finance` غير موجود
- ملفان فارغان غريبان في جذر الريبو: `it` و`prepareBindings($bindings)`
- `package.json`: مفتاح `@tailwindcss/forms` مكرر مرتين بقيمتين مختلفتين
- `routes/web.php`: ~11 مسار ديمو متبقٍ من قالب TailAdmin (calendar/buttons/badges/ui-elements)

---

## القسم 1 — توحيد نظام الألوان (أولوية قصوى، يمنع أي عمل تصميمي لاحق من التراكم على أساس خاطئ)

استبدل في كل الملفات التالية كل كلاس من العمود الأيسر بمقابله في العمود الأيمن (الاستبدال يشمل الوضع الفاتح تلقائيًا لأن `--edz-color-bg`/`--edz-color-text` يتغيّران أصلاً تحت `.dark`، لذلك **لا حاجة لبادئة `dark:` منفصلة مع هذه التوكنز**):

| القديم (غير معرَّف) | الجديد (متصل بالـ tokens) |
|---|---|
| `bg-neutral-bg dark:bg-dark-bg` | `bg-surface-bg` |
| `text-neutral-text dark:text-dark-text` | `text-ink` |

**الملفات المتأثرة:** ✅ (24 ملفًا: الـ 22 المذكورة أدناه + `components/merchant/body.blade.php` و `components/merchant/sidebar.blade.php` التي أُبقيَت لأن `x-merchant.body` لا يزال مستخدمًا فعليًا في `merchant/billing/index.blade.php`)

```
resources/views/components/layouts/app.blade.php
resources/views/components/layouts/landing-layout.blade.php
resources/views/components/layouts/navbar.blade.php
resources/views/components/layouts/guest.blade.php
resources/views/components/layouts/footer.blade.php
resources/views/components/auth/card.blade.php
resources/views/components/text-input.blade.php
resources/views/components/secondary-button.blade.php
resources/views/components/responsive-nav-link.blade.php
resources/views/components/input-label.blade.php
resources/views/components/dropdown-link.blade.php
resources/views/components/nav-link.blade.php
resources/views/components/ecommerce/stores-metrics.blade.php
resources/views/components/dark-toggle.blade.php
resources/views/components/sidebar-link.blade.php
resources/views/components/lang-switcher.blade.php
resources/views/layouts/app.blade.php
resources/views/layouts/backdrop.blade.php
resources/views/layouts/sidebar.blade.php
resources/views/auth/choose-store.blade.php
resources/views/landing/sections/hero.blade.php
resources/views/merchant1/stores/index.blade.php
```

**بعد الاستبدال، احذف مكونات لوحة التاجر القديمة المستبدَلة فعليًا بنظام Livewire الجديد (تحقق أولاً أن لا شيء يستدعيها):**
```bash
grep -rln "x-merchant.body\|x-merchant.sidebar\b" resources/views routes app --include=*.php
# إن كانت النتيجة فارغة أو تشير فقط لملفات قديمة غير مستعملة:
git rm resources/views/components/merchant/body.blade.php
git rm resources/views/components/merchant/sidebar.blade.php
```
⬜ **محجوب مؤقتًا** — الفحص أظهر أن `x-merchant.body` ما زال مستخدمًا فعليًا في `resources/views/merchant/billing/index.blade.php` (يُقدَّم عبر `BillingController` ← `view('merchant.billing.index')`). المكوّنان أُبقيا وطُبّق عليهما استبدال الألوان. يجب حذفهما بعد نقل صفحة Billing/الفواتير إلى Livewire.

✅ احذف أيضًا `resources/views/landing/sections/plans.blade copy.php` (نسخة مكررة زائدة). — تم الحذف، مع حذف `resources/views/components/ecommerce/stores-metrics.blade copy.php` المكرر المماثل.

---

## القسم 2 — حسم تعارض المسار بين Filament و Livewire على `/merchant/*`

`MerchantPanelProvider` (Filament) و`routes/merchant.php` (Livewire) يتشاركان نفس البادئة `merchant` حاليًا. طبّق الحل التالي: أبقِ Filament يخدم فقط الموارد التي لم تُنقل بعد، عبر تضييق مساره ليكون `merchant/legacy` بدل `merchant`:

```php
// app/Providers/Filament/MerchantPanelProvider.php
->path('merchant/legacy')   // بدل ->path('merchant')
```

هذا يفصل فعليًا مسارات Filament المتبقية (`/merchant/legacy/products`...) عن مسارات Livewire الجديدة (`/merchant/{store:slug}/products`)، ويلغي أي تضارب أو التباس أثناء الانتقال التدريجي. أضف رابط مؤقت في القائمة الجانبية لأي مورد لم يُنقل بعد يوجّه لمساره تحت `legacy`.

✅ **تم** — `->path('merchant/legacy')` في `MerchantPanelProvider`، وأُضيفت روابط Legacy في القائمة الجانبية Livewire (`livewire/merchant/layout/sidebar.blade.php`) لكل مورد لم يُنقل بعد (Brands, Categories, Product Options, Product Variants, Inventories, Stock Alerts, My Teams) عبر `route('filament.merchant...', $store)`. حدّث الاختبار المتأثر (`SplitLoginTest` → `/merchant/legacy`). كل المراجع الأخرى تستخدم `route()`/`Filament::getPanel()->getUrl()` فتتحدث تلقائيًا. الاختبارات: 39 ناجح.

> ملاحظة: يوجد تكرار لخدمة `LoginRedirectService` (نسخة `app/Domains/User/Services/` غير مستعملة + نسخة `app/Services/Auth/` المستعملة) — مرشحة للتنظيف في القسم 6.

---

## القسم 3 — إكمال نقل موارد التاجر المتبقية إلى Livewire (بالترتيب)

اتبع نفس نمط `products/{index,form,show}.blade.php` + `routes/merchant.php` + بادجات `<x-merchant.status>` الذي طُبّق بنجاح على Products. لكل مورد أدناه: انقله، تحقق من التكافؤ الوظيفي مع نسخة Filament، ثم احذف نسخته من `app/Filament/Merchant/Resources/*` و`MerchantPanelProvider`.

1. ✅ **Brands** — مستقل، بسيط، ابدأ به
2. ✅ **Categories** — مستقل
3. ✅ **ProductOptions** + **ProductOptionValues** — يعتمدان على Products (منجز)
4. ✅ **ProductVariants** — يعتمد على Products + ProductOptions (منجز)
5. ✅ **Inventories** + **InventoryMovements** + **StockAlerts** — يعتمدون على Products/Variants (منجز)
6. ✅ **MyTeams** — مستقل، يمكن تنفيذه بالتوازي مع أي مرحلة

بعد اكتمال آخر مورد: احذف `app/Filament/Merchant` بالكامل و`MerchantPanelProvider.php` وتسجيله في `bootstrap/providers.php`. ✅ تم الحذف بالكامل.

---

## القسم 4 — تطوير الصفحة الرئيسية

أعد بناء `resources/views/landing/index.blade.php` وأقسامه كصفحة تسويقية احترافية كاملة (Bootstrap+SCSS حسب القرار المعماري، بعد تطبيق القسم 1 لضمان دعم الوضع الداكن بشكل صحيح من البداية). الأقسام المطلوبة بالترتيب:

1. **Hero** — عنوان رئيسي واضح لهوية Edzeery (منصة SaaS متكاملة عربية الهوية)، عبارة فرعية، CTA مزدوج (ابدأ الآن / شاهد العرض)، صورة/رسم توضيحي للمنتج
2. **الشعار الاجتماعي** (Social proof) — شعارات عملاء/إحصائيات (عدد المتاجر، الطلبات المُعالجة...) إن توفرت بيانات، وإلا placeholder واضح
3. **الميزات الأساسية** (services.blade.php الحالي كنواة، وسّعه) — بطاقات لكل نظام فرعي: منشئ صفحات الهبوط، المتجر الإلكتروني، CRM، ERP، HR وجدولة العمل، محاسبة العمال — كل بطاقة أيقونة + عنوان + وصف سطرين
4. **كيف يعمل** — 3-4 خطوات توضح رحلة التاجر من التسجيل إلى الإطلاق
5. **الخطط والأسعار** (plans.blade.php الحالي — نظّفه، احذف النسخة المكررة، اربطه بـ `App\Domains\Plan\Services` الفعلية بدل أي بيانات ثابتة إن وُجدت)
6. **بوابات الدفع المدعومة** (payments.blade.php الحالي كنواة)
7. **الأسئلة الشائعة**
8. **CTA ختامي** + Footer كامل (روابط، تواصل اجتماعي، لغة)

كل قسم مكوّن Blade منفصل تحت `resources/views/landing/sections/`، RTL افتراضي، ودعم Dark mode كامل عبر tokens (القسم 1).

⬜

---

## القسم 5 — عنقود المحاسبة (Finance-Manager) — لم يُنفَّذ بعد

راجع البرومت الكامل الذي أُعطي سابقًا لنقل `Debt`, `DebtPayment`, `DebtSettlementService`, `NotificationService` من `github.com/Edzeery/Finance-Manager` إلى `app/Domains/Finance/*`، بكل التكييفات (`store_id` بدل `workspace_id`، ربط الحالات بـ `mystatuskit`، `counterparty_name` نصي حر). لم يُطبَّق أي جزء منه في الريبو الحالي — نفّذه كاملاً.

⬜

---

## القسم 6 — تنظيف عام (منخفض الخطورة، ينفَّذ بالتوازي مع أي قسم آخر)

```bash
git rm "it" "prepareBindings(\$bindings)"
```
- أزل التكرار في `package.json` لمفتاح `@tailwindcss/forms` (أبقِ القيمة الأحدث فقط)
- أزل من `routes/web.php` المسارات المتبقية من قالب TailAdmin التجريبي (calendar, ui-elements, buttons, badges) غير المستعملة فعليًا في المنتج

⬜

---

## ترتيب التنفيذ الموصى به

القسم 1 (ألوان) → القسم 2 (حسم تعارض المسار) → القسم 3 (إكمال Livewire) → القسم 4 (الصفحة الرئيسية) → القسم 5 (المحاسبة) → القسم 6 (تنظيف، بالتوازي في أي وقت)

---

## حالة التنفيذ الحالية

- ✅ **القسم 1**: استبدال الألوان في 24 ملفًا + حذف نسختي `*.blade copy.php`. المكوّنان `body`/`sidebar` أُبقيا (محجوب حتى نقل Billing إلى Livewire).
- ✅ **القسم 2**: لوحة Filament للتاجر على `/merchant/legacy` + روابط Legacy في القائمة الجانبية + تحديث الاختبار. حُذف بالكامل مع باقي Filament Merchant.
- ✅ **القسم 3**: Brands + Categories + ProductOptions/ProductOptionValues + ProductVariants + Inventories/InventoryMovements/StockAlerts + **MyTeams** — جميعها منقولة إلى Livewire Volt. حُذف `app/Filament/Merchant` بالكامل + `MerchantPanelProvider` + `TeamController`. الفيلامنت لم يعد موجودًا في طبقة التاجر.
- ⬜ **القسم 4**: لم يبدأ.
- ⬜ **القسم 5**: لم يبدأ.
- ⬜ **القسم 6**: لم يبدأ.
