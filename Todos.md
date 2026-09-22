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
- ✅ عنقود الديون والإشعارات من `Finance-Manager` — تم نقله إلى `app/Domains/Finance/*` مع `store_id` + `mystatuskit` + ترجمات 4 لغات.
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

## القسم 5 — عنقود المحاسبة (Finance-Manager) — ✅ تم

تم نقل عنقود الديون بالكامل من `github.com/Edzeery/Finance-Manager` إلى `app/Domains/Finance/*` مع كل التكييفات:

**الملفات المُنشأة/المُعدَّلة:**
- `app/Enums/Finance/DebtTypeEnum.php` — `owed`/`owing` مع `InteractsWithStatusKit`
- `app/Enums/Finance/DebtStatusEnum.php` — `active`/`partial`/`paid`/`overdue` مع `InteractsWithStatusKit`
- `app/Models/Finance/Debt.php` — نموذج مع `store_id` + `StoreScope` + SoftDeletes
- `app/Models/Finance/DebtPayment.php` — نموذج مع `store_id` + `StoreScope`
- `app/Observers/Finance/DebtPaymentObserver.php` — مزامنة حالة الدين عند إضافة/تعديل/حذف الدفعات
- `app/Domains/Finance/Services/DebtSettlementService.php` — حساب التسوية
- `app/Domains/Finance/Services/DebtNotificationService.php` — إشعارات الاستحقاق
- `app/Enums/Store/StorePermissionEnum.php` — 4 أذونات: `FINANCE_DEBT_VIEW/CREATE/UPDATE/DELETE`
- `app/Providers/AppServiceProvider.php` — تسجيل `DebtPaymentObserver`
- `database/migrations/2026_08_17_120000_create_debts_table.php` — جدول `debts` مع `store_id` (UUID)
- `database/migrations/2026_08_17_120001_create_debt_payments_table.php` — جدول `debt_payments` مع `store_id` (UUID)
- `routes/merchant.php` — 4 مسارات Volt: index/create/edit/show
- `resources/views/livewire/merchant/debts/index.blade.php` — صفحة القائمة مع فلترة وملخص
- `resources/views/livewire/merchant/debts/show.blade.php` — صفحة التفاصيل مع إضافة الدفعات
- `resources/views/livewire/merchant/debts/form.blade.php` — نموذج إنشاء/تعديل
- `resources/views/livewire/merchant/layout/sidebar.blade.php` — رابط ديون في القائمة الجانبية
- `resources/views/components/edz/icon.blade.php` — أيقونات جديدة: `credit-card`, `check-circle`, `x-mark`, `plus`, `trash`
- `resources/lang/{en,ar,es,fr}/finance.php` — ترجمات كاملة (35 مفتاحًا × 4 لغات)

**التكيفات الرئيسية:**
- `store_id` (UUID) بدل `workspace_id`
- `StoreScope` بدل `WorkspaceScope`
- `currentStoreId()` بدل `config('app.current_workspace')`
- `<x-merchant.status>` مع `mystatuskit` بدل `kitBadge()` مباشرة
- `config/statuses.php` يحتوي `debt` و`debt_type` بالفعل

✅

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

القسم 1 (ألوان) → القسم 2 (حسم تعارض المسار) → القسم 3 (إكمال Livewire) → القسم 4 (الصفحة الرئيسية) ✅ → القسم 5 (المحاسبة) ✅ → القسم 6 (تنظيف) ✅

---

## حالة التنفيذ الحالية

- ✅ **القسم 1**: استبدال الألوان في 24 ملفًا + حذف نسختي `*.blade copy.php`. المكوّنان `body`/`sidebar` أُبقيا (محجوب حتى نقل Billing إلى Livewire).
- ✅ **القسم 2**: لوحة Filament للتاجر على `/merchant/legacy` + روابط Legacy في القائمة الجانبية + تحديث الاختبار. حُذف بالكامل مع باقي Filament Merchant.
- ✅ **القسم 3**: Brands + Categories + ProductOptions/ProductOptionValues + ProductVariants + Inventories/InventoryMovements/StockAlerts + **MyTeams** — جميعها منقولة إلى Livewire Volt. حُذف `app/Filament/Merchant` بالكامل + `MerchantPanelProvider` + `TeamController`. الفيلامنت لم يعد موجودًا في طبقة التاجر.
- ✅ **القسم 4**: إعادة بناء الصفحة الرئيسية بالكامل — Hero بـ CTA مزدوج + mock dashboard، Social Proof (بيانات حية من قاعدة البيانات)، Services بـ 6 بطاقات ميزات، How It Works بـ 4 خطوات، Plans/أسعار بـ Tailwind بدل Filament، Payments بـ معلومات إضافية، FAQ بـ 6 أسئلة، CTA ختامي، Footer محدّث (4 أعمدة + تواصل اجتماعي). ترجمات كاملة (en/ar/fr/es). إزالة جميع مكونات Filament من الصفحة الرئيسية.
- ✅ **القسم 5**: عنقود الديون بالكامل — Enums + Models + Observer + Services + Permissions + Migrations + Volt views + Routes + Sidebar + Translations (4 لغات). الديون الآن موصولة بـ `store_id` و `mystatuskit`.
- ✅ **القسم 6**: تم بالكامل — حُذف كود الديو التجريبي (~17 صفحة + 16 مكوّن + 17 مسار)، حُذف ملفين فارغين غريبين (`it`، `prepareBindings($bindings)`) من جذر الريبو، أُصلحت تكرارات `package.json` (`@tailwindcss/forms` و`alpinejs`). راجع `DESIGN_SYSTEM.md`.

---

## تنظيف كود الديو التجريبي من TailAdmin (أغسطس 2026)

✅ **تم** — حُذف بالكامل:

**17 مسار من routes/web.php:** `/welcome`, `/calendar`, `/pages/profile`, `/form-elements`, `/basic-tables`, `/blank`, `/error-404`, `/line-chart`, `/bar-chart`, `/signin`, `/signup`, `/alerts`, `/avatars`, `/badge`, `/buttons`, `/image`, `/videos`

**17 صفحة عرض (resources/views/pages/):** `dashboard/ecommerce`, `calender`, `profile`, `form/form-elements`, `tables/basic-tables`, `blank`, `errors/error-404`, `chart/line-chart`, `chart/bar-chart`, `auth/signin`, `auth/signup`, `ui-elements/{alerts,avatars,badges,buttons,images,videos}` + مجلد `pages/` نفسه

**16 مكوّن ميّت:** `calender-area`, `ui/{button,alert,badge,avatar,youtube-embed}`, `common/common-grid-shape`, `form/form-elements/{default-inputs,select-inputs,text-area-inputs,input-states,input-group,file-input-example,checkbox-component,radio-buttons,toggle-switch,dropzone}`

**ملفين فارغين من جذر الريبو:** `it`, `prepareBindings($bindings)`

**إصلاح package.json:** إزالة تكرار `@tailwindcss/forms` (أُبقِي `^0.5.11` في devDependencies فقط)، إزالة تكرار `alpinejs` (أُحدّث إلى `^3.15.6` في devDependencies فقط)

**أُبقي عليه** (مستخدم فعليًا): `common/{page-breadcrumb,component-card,dropdown-menu,table-dropdown,preloader}`, `ui/modal`, `profile/*`, `ecommerce/*`, `layouts/*`

**ملاحظات:**
- `/signin` و`/signup` أُزيلتا لأنهما يُكرّران تدفق Breeze الحقيقي في `routes/auth.php`
- `/error-404` أُزيلت لأنها ليست صفحة الخطأ الحقيقية في Laravel (المسار الصحيح هو `resources/views/errors/404.blade.php`، و`bootstrap/app.php` لا يعرّف handler مخصص)
- `resources/views/pages/errors/error-404.blade.php` كانت في مسار خاطئ وغير مستخدمة كصفحة خطأ حقيقية
- مكوّنات `ecommerce/*` أُبقيت لأنها مستخدمة حيًّا في `merchant/dashboard.blade.php` و`merchant/stores/index.blade.php`
- مكوّن `ui/modal` أُبقي لأنه مستخدم في `profile/*` المُستخدمة حيًّا في `merchant/account/index.blade.php`

✅ **توثيق نمط إعادة استخدام TailAdmin** — أُنشئ `DESIGN_SYSTEM.md` بتوثيق كامل للقواعد: الافتراض هو مكونات TailAdmin، كل مكوّن يجب أن يستخدم توكنات `--edz-*`، ألوان الحالات تمر عبر `mystatuskit`.

✅ **اكتمال الترجمات (i18n)** — تم فحص شامل وسدّ الفجوات:

**ملفات مفقودة أُنشئت:**
- `ar/profile.php`, `fr/profile.php`, `es/profile.php` — مفتاح `country`
- `fr/stores.php`, `es/stores.php` — 14 مفتاح (حالة المتجر، الرسائل، الترقي)
- `fr/productoption.php`, `es/productoption.php` — 4 مفاتيح (select, radio, checkbox, text)

**مفاتيح مفقودة في titles.php أُضيفت:**
- `ar/titles.php`: +23 مفتاح (brand, brands, expire_at, inventory_*, product_*, stock_*, unit, variants, إلخ)
- `fr/titles.php`: +27 مفتاح (نفس المفاتيح + numbers_agents, member, memberships)
- `es/titles.php`: +27 مفتاح (نفس المفاتيح)

**lang-switcher**: موجود في كلا المخطّطين — `layouts/app-header.blade.php` (مخطّط التاجر) و`components/layouts/navbar.blade.php` (الصفحة الرئيسية/المتجر).

**ملاحظة:** مفاتيح `titles.php` غير مرتبة أبجديًا بعد الإضافة (تم إدراجها بعد آخر مفتاح موجود). يمكن تنظيمها لاحقًا إذا لزم الأمر.

---

## حالات التتبّع وحركة المخزون (P7–P9) — ✅ تم (أغسطس 2026)

### P7 — أنواع حركة مخزون `loss`/`damage`
- `app/Enums/Store/InventoryMovementType.php`: أُضيف `LOSS='loss'` و`DAMAGE='damage'` — كلاهما `isDecrease()` + `affectsStock()` + `direction() = -1` + `isManual()`.
- `config/status-kit-statuses.php`: `inventorymovementtype.loss` (danger `#dc2626`، أيقونة `loss`) و`.damage` (warning `#facc15`، أيقونة `damage`) بعد `release`.
- `config/status-kit-icons.php`: مفاتيح `loss/damage/returning/failed_attempt/lost/damaged` في المجموعات الأربع (fa/bi/ion/heroicon).
- ترجمات `inventorymovementtype` باللغات الأربع (ar/en/fr/es) + إصلاح مفتاح `sale` المفقود في ar.
- **اختبار:** `tests/Feature/InventoryMovementTypeTest.php`.

### P8 — نطاق حالات التتبع `tracking` (مستقل عن order)
- `app/Enums/Store/OrderTrackingStatus.php` (جديد): 9 حالات `shipped → delivered / returned / lost / damaged` + `open()/terminal()/isOpen()/isTerminal()` + `fromCarrier(?string)` لخريطة النص الخام لشركات الشحن (يبعّد الفجوات).
- `config/status-kit-statuses.php`: نطاق `tracking` (كاملًا) + إكمال مفاتيح order الناقصة (`wrong_number, undeliverable, unclaimed, no_answer_1/2/3, postponed, duplicate, out_of_stock`).
- ترجمات نطاق `tracking` باللغات الأربع.
- **اختبار:** `tests/Feature/OrderTrackingStatusTest.php`.

### P9 — ربط `tracking_status` بسجل order_trackings
- ميغريشن `2026_08_30_000001_add_tracking_status_to_order_trackings_table.php`: عمود `tracking_status` nullable بعد `carrier_label` + فهرس `[store_id, tracking_status]`.
- `app/Models/Orders/OrderTracking.php`: `tracking_status` في fillable + `trackingStatus(): ?OrderTrackingStatus`.
- `app/Domains/Order/Services/OrderTrackingService.php`: `startShipment`→SHIPPED، `markDelivered`→DELIVERED، `markReturned`→RETURNED، + `markReturning/markLost/markDamaged/markFailedAttempt`، `currentTracking`/`currentOpenTracking` (سجل مفتوح فالأحدث؛ لا ينبش سجلاً مغلقًا). `OrderObserver::syncTracking` لم يتغيّر.
- **اختبار:** `tests/Feature/Merchant/OrderTrackingTest.php` (8 حالات قائمة + جديدتا `tracking_status` وterminal helpers).

### أفضليّة label النظامي + التجهيز الافتراضي
- `app/Domains/Status/Support/ResolvedStatus.php::fromModel`: الصفوف النظامية (`is_system` وبدون `store_id`) تعرض ترجمة `status-kit` إن وُجدت ويتراجعن لـ label الصف إن لم توجد — شفافية i18n للغات الأربع حتى مع وجود صفوف DB بالإنگليزية. صفوف المتجر (المخصصة) تبقى من DB.
- `database/seeders/SystemStatusesSeeder.php`: أُضيفت صفوف افتراضية — `tracking` ×9، `inventory` ×3 (`in_stock/low_stock/out_of_stock`)، `inventorymovementtype` ×8 (بما فيها `loss` danger و`damage` warning)، وإكمال order بـ`unclaimed`/`undeliverable`. بلا `icon`/`display_mode` (متوافق مع قاعدة dev بدون صفوفهما).
- **اختبار:** `tests/Feature/StatusLabelPrecedenceTest.php` (أفضليّة الترجمة + تخزين المتجر + عدّاد seed).

### P10 (مستقبلي — محاسبية الإتلاف)
- فجوة محاسبية مرصودة: عند نطق نتيجة فحص الإرجاع `DAMAGED/PARTIAL/LOST`، حركة `RETURN` ترجع الكمية كاملة سجلًّا، ولا يوجد شطب للكمية التالفة/المفقودة. الخطة المستقبلية: عند وقت النطق إصدار حركة `LOSS`/`DAMAGE` عبر `InventoryService::apply` لوضع خصم. نقاط الاتصال: `OrderObserver::handleStatusChange` + `ReturnVerificationService`.

---

## عنقود شحن الطلبات — التراجع عن التجاوز اليدوي + «توصيل مجاني» (سبتمبر 2026)

✅ **تم** — المراجع: `OrdersrefactorplanFixed.md` **Phase 24** (التفاصيل الكاملة + الأدلة القابلة للقياس).

- حُذف `order_shipping_costs` (ميغريشن 000003 — كانت `Pending` في MySQL + النموذج + `Order::shippingCosts/latestShippingCost` + `OrderService::snapshotShippingCost`) — **`orders.shipping_cost` الآن المصدر الوحيد** لتكلفة الشحن، يُكتب حصريًا من `ShippingCostCalculator` (الإنشاء + recalc عند wilaya/city)، **بلا تحرير يدوي** (حُذف `$saveOrderShippingCost`/`startOrderShippingCostEdit` + الحقل الرقمي؛ العمود read-only).
- **عرض «توصيل مجاني» بدل `0 دج`** عند غياب أسعار مخصّصة (قاعدة موحّدة: أي `shipping_cost <= 0` → شارة `shipping_free` في عمود الجدول وبطاقة الموبايل). بلا سكيما جديدة وبلا استعلامات إضافية.
- ترجمات `shipping_free` ×4 لغات؛ حُذف `manual_override`.
- **دليل الإنجاز:** test suite كاملة **285 ناجحة (1056 assertions)** — منها اختبارات طلبات التاجر 23 ناجحة (109 assertions) مع إبقاء اختبار recalc المدينة (400.0), وحذف اختبار override القديم + `scratch_validator_test`. `view:cache` و`php -l` سليمان. **إغلاق L5** (»ShippingCostCalculator fallback») في OrdersrefactorplanFixed.

---

## عنقود عمليات الطلبيات — التأكيد الاحترافي/التتبع/السجل/التكرار/الجماعي (سبتمبر 2026)

✅ **نُفّذ** — المرجع الكامل: `OrdersOperationsPlan.md` + دليل الإنجاز أدناه. جدول التنفيذ: **290 اختبارًا ناجحًا (1064 assertions)** — منها 5 جديدة في `tests/Feature/Merchant/OrderDuplicateDetectionTest.php` و8 في `OrderTrackingTest` و`OrderTrackingStatusTest` (نطاق tracking) و`v:cache`/`php -l` سليمان و`route:list` يؤكد `merchant.tracking.index`.

- **المرجع الكامل:** `OrdersOperationsPlan.md` (الخطة + القرارات + المراجع file:line + المراحل P25–P30).
- **المحاور المنفّذة:**
  1. **P25 البنية:** `OrderAuditService` + `OrderTrackingService` (سجل التتبع + التدقيق على كل تحوّل) + `OrderWorkflow` (مجموعات backOffice/carrier/closed) + `OrderObserver` (تدقيق إنشاء/تعديل/حالة + مزامنة التتبع) + موديلا `OrderEvent`/`OrderTrackingHistory` + 3 ميغريشن (order_events، order_tracking_histories، فهارس repeated-workflow).
  2. **P26 درج التأكيد** ← تأكيد فقط / تأكيد وإرسال عبر `OrderShippingGateway::send()` + التصفية الافتراضية تستبعد حالات CARRIER من صفحة الطلبيات.
  3. **P27 صفحة «تتبع الطلبيات»** (`merchant/{store}/tracking`) — فلاتر + إحصائيات + جدول/بطاقات + درج طلبية (بطاقة شركة، ملخص، إجراءات سريعة، سجل التتبع، سجل الطلبية).
  4. **P28 كشف التكرار** `OrderDuplicateService::findSimilar(Order|array $candidate)` (نافذة 30 يومًا، حد 5) + تحذيرات في نموذج الإنشاء/التعديل ودرج التأكيد + إجراء «وضع كطلبية مكررة».
  5. **P29 تعديل حالة جماعي** (مستبعد دائمًا: `cancelled/canceled` + `confirmed`؛ غير الصالحة تُتخطّى وتُبلّغ).
- **قرارات §9 (الخمسة) — كلها حُلّت أثناء التنفيذ:** الفصل طلبيات/تتبع (CARRIER تُستبعد من صفحة الطلبيات وتُعالج في التتبع)؛ درج التأكيد «تأكيد + إرسال» بلا عدّاد محاولات اتصال؛ نافذة التكرار 30 يومًا غير مانعة (وتصنيف ثلاثي 29.6)؛ الحالات الجماعية = كل الحالات عدا `cancelled/canceled/confirmed` مع التخطّي والإبلاغ؛ الأولوية = «نفّذ كل شيء» (P25→P29 + جولة 29.1–29.7).

### جولة Phase 29 — فرع 29.1 ✅ (يتبع فرع تلو فرع بموافقة المستخدم)

بدأت جولة Phase 29 (29.1–29.7) على `orders/index.blade.php`. **الفرع الأول (29.1) اكتمل** — إتاحة سجل أحداث الطلبية بالدور:
- `StoreOrderPermissions::canViewOrderEventLog(Order, StoreMembership)` — OWNER/ADMIN دائمًا، MANAGER للمعيَّن لعضويته فقط، STAFF أبدًا. حارس موحّد على مودال التفاصيل + درج التتبع.
- تجميع يومي + وقت + فاعل + شارة دور عبر mystatuskit؛ مفاتيح `event_day_today`/`event_day_yesterday` ×4 لغات.
- `tests/Feature/Merchant/OrderEventLogVisibilityTest.php` — **8 ناجحة (20 assertions)**. السويت كاملة **297 ناجح (1080 assertions)** (الوحيد المتأثر بلوك ملفات Windows عاد 11/11 منفردًا → صفر انحدار). التفاصيل: `OrdersrefactorplanFixed.md` Phase 29.1 + `OrdersOperationsPlan.md` §10.

### جولة Phase 29 — فرع 29.2 ✅ (إرسال مباشر لطلب مُؤكَّد)

اكتمل الفرع الثاني (29.2): إجراء `$sendConfirmedOrder(string $orderId)` — حارس `order.manage` + قبول `confirmed/preparing` فقط (رفض غيرها بتوست `send_requires_confirmation`، لا تأكيد تلقائي أبدًا عبر `confirmFirst: false`)، فحص جهوزية (اسم/هاتف/ولاية/بلدية/عنوان-أو-نقطة استلام/≥1 صنف/شركة-أو-موصّل) مع إدراج الناقص في توست `send_missing_fields` وتبقى الحالة كما هي، ثم إرسال عبر `OrderShippingGateway::send(..., confirmFirst: false)`. زر truck في الجدول + بطاقات الهاتف لهاتين الحالتين فقط. مفاتيح ×4 لغات. إصلاح خطأ كامن: `?int` → `int|string|null` في `OrderTrackingService` (معرّفات العضويات ULID) كان يعطّل أي تحوّل `shipped` بعضوية. `DirectSendConfirmedOrderTest.php` — **7 ناجحة (16 assertions)**؛ السويت **305 ناجح (1100 assertions)** → صفر انحدار.

### جولة Phase 29 — فرع 29.3 ✅ (إرسال جماعي مجمَّع حسب شركة كل طلبية)

اكتمل الفرع الثالث (29.3) وأُعيد العمل عليه بملاحظات المستخدم: استُبدل «إرسال الكل لشركة واحدة» القديم بنافذة تأكيد جماعية تُصنّف كل طلبية قبل الإرسال:
- `$resolveBulkOrderState(Order)` — مصدر الحقيقة الوحيد: ناقل كل طلبية (اسم الشركة **أو اسم الموصّل الحقيقي** عبر `deliveryRider`، وإلا «بلا شركة») + `ready` + `reasons[]` صريحة: `bulk_send_reason_status` (تحتاج تأكيدًا/تجهيزًا — الحالة الحالية)، `bulk_send_reason_missing` (كل الحقول الناقصة)، `bulk_send_reason_no_carrier`.
- `$openBulkSendModal()` — جلب واحد بـ `with(['status','customer','shippingProvider','deliveryRider','items'])` (لا N+1)، يملأ `bulkSendAnalysis`/`bulkSendReadyCount`/`bulkSendSkipCount`، والملخص من **الجاهزة فقط** في `x-edz.modal`.
- المودال قسمان: «ستُرسل» (ملخص الناقلين بأعدادها) + تحذير بارز (`x-edz.alert type="warning"`) يسرد **كل طلبية غير جاهزة برقمها وسببها**؛ عند عدم وجود جاهزة → زر الثأكيد معطَّل (`bulk_send_confirm_none`)، وإلا «إرسال :count الجاهزة فقط» (`bulk_send_confirm_some`).
- `$confirmBulkSend()` — يعيد الجلب طازجًا بنفس eager-loads ويُعيد الحساب بالمولّد نفسه، يرسل الجاهزة فقط كلٌّ لناقلها الفعلي (`providerId: shipping_provider_id ?: null`، لا auto-confirm)، توست واحد صريح: سطور الشركات + رأس `bulk_send_skipped :count` + سطر لكل طلبية `number (السبب)` (أيقونة تحذير إن وُجد تخطٍّ).
- `$sendConfirmedOrder` (29.2) يعيد الآن استخدام `$collectMissingFields` نفسه (مصدر واحد للفحص).
- مفاتيح 16 ×4 لغات: مفاتيح 9 القائمة + 7 جديدة: `bulk_send_ready_title`/`bulk_send_skipped_title`/`bulk_send_reason_status`/`bulk_send_reason_missing`/`bulk_send_reason_no_carrier`/`bulk_send_confirm_some`/`bulk_send_confirm_none`.
- `BulkSendCarrierGroupingTest.php` — **9 ناجحة (48 assertions)** (تشمل تصنيف المودال مع الأرقام والأسباب، التسمية الصريحة للمتخطَّى في التوست، حالة «لا شيء جاهز»). السويت كاملة **314 ناجح (1148 assertions)** → صفر انحدار. `view:cache` + `php -l` سليمان.

### جولة Phase 29 — فرع 29.4 ✅ (عمود حالة التتبع + توحيد الخط الزمني)

اكتمل الفرع الرابع (29.4) — واجهة خالصة، صفر تغيير في السلوك:
- عمود «حالة التتبع» ثانوي قابل للتفعيل (`group: workflow`, `default: false`) في سجل `$orderColumns()`؛ `tracking_status` أُضيف إلى بانيّ `$arr['tracking']` (الجدول في `loadOrders` + fallback الدرج في `openOrderDetails`)؛ خلية الجدول وبطاقة الموبايل تعرضان شارة mystatuskit بنطاق `tracking` (نفس أنماط صفحة التتبع) أو `—`.
- توحيد سجل أحداث الطلبية: القسم المكرَّر في درج التفاصيل (`orders/index`) ودرج التتبع (`tracking/index`) صُهر في جزئية واحدة `partials/order-events-timeline` عبر `@include` (الحرّاسات في موضع الاستدعاء — لا أثر على صلاحيات 29.1).
- مفتاح `merchant_panel.tracking_status` ×4 لغات لرأس الجدول + منتقي الأعمدة. البذرة المحلية (`order_tracking_histories`): **قرار المستخدم: تخطَّاها** — البيانات تنشأ طبيعيًا عند الإرسال والفراغ مُعالَج أصلاً.
- `OrdersTrackingColumnTest.php` — **3 ناجحة** (شارة مع تفعيل العمود، غيابها افتراضيًا، `—` بلا شحنة). ملاحظة: حالة `damaged` مستخدمة في الاختبار لأنها خاصة بنطاق `tracking` فقط (حالة `in_transit` موجودة أيضًا في نطاق `order` فتلوّث الفحص). السويت كاملة **317 ناجح (1158 assertions)** → صفر انحدار. `view:cache` + `php -l` سليمان.
- **متابعة طلب المستخدم (تُقرأ الحالة عبر العلاقة وليس فوق جدول orders):** حارسا زري الإرسال في الجدول (desktop) وبطاقة الموبايل كانا يقرآن الحالة من العنوان المتداخل `$order['status']['key']` (أرشيف علاقة `status` بعد `toArray`) — وهذا غير بديهي ويُفهم خطأً كعمود مباشر في جدول `orders`. الحل: `$arr['status_key'] = $order->status?->key` يُحسب صراحةً في بانيّ الصف داخل `loadOrders` (القراءة من جدول الإحالة عبر `status_id`)، والحارسان يستخدمان `$order['status_key'] ?? null`؛ المؤكد بحدٍّ واحد هو النموذجي القائم `$order->status?->key`. السويت كاملة بعد التعديل **317 ناجح (1158 assertions)** — صفر انحدار 27 ناجح (94 assertions) في الاستهداف.

### جولة Phase 29 — فرع 29.5 ✅ (شارة التكرار + الفحص البطيء)

- **العلامة (غير معطِّلة):** داخل `loadOrders` استعلام تجميعي واحد — عدد طلبيات المتجر في نافذة 30 يومًا (`OrderDuplicateService::WINDOW_DAYS`) مجمَّعًا بـ`customer_id` عبر `havingRaw('COUNT(*) >= 2')` على فهرس `orders_duplicate_scan (store_id, customer_id, created_at)`؛ في بانيّ الصف `$arr['duplicate_count'] = $duplicateCounts[$customer_id] ?? 0`. الشارة `edz-badge--warning --sm` «×N» بعد رقم الطلبية (خلية الرقم في الجدول + رأس بطاقة الموبايل) مشروطة: `!showTrash` + `status_key !== 'duplicate'` + `≥ 2`. بلا مفاتيح ترجمة جديدة.
- **الفحص البطيء:** `$openDuplicateScan(orderId)` تُشغّل `findSimilar()` **عند النقر فقط** (لا شيء لصفوف غير مكبوسة) وتعرض `<x-edz.modal>` بنمط درج التأكيد (رقم • منذ…، ×كمية، انتقال لدرج التفاصيل) + `$closeDuplicateScanModal` عبر `@edz-modal-closed.window`. النافذة معلومات فقط لأعضاء الصفحة (بيانات متجر الخاص نفسه، مرئية أصلًا).
- **أدلة:** `tests/Feature/Merchant/OrderDuplicateBadgeTest.php` — **5 ناجحة (11 assertions)** (مساعدات `dbb*` — Pest لا تشارك الدوال بين الملفات فمنع التداخل مع `dup*`): ① شارة لكلا صفّي الرقم المكرر، ② سطر بلا تكرار بلا شارة، ③ النقر يفحص ويعرض الآخرين فقط (نتيجتان، نفسه مُستبعد، رقم ظاهر)، ④ طلبيّات حالة `duplicate` لا شارة، ⑤ خارج نافذة 30 يومًا لا علامة (ملاحظة تقنية: `created_at` ليس fillable — يُضبط بعد `create()` بصفة الحالة). السويت كاملة **322 ناجح (1169 assertions)** → صفر انحدار. `view:cache` + `php -l` سليمان.
- **متابعة 29.5a — توحيد التعريف (موافقة المستخدم بعد تقرير ناتج، وليس تخمينًا):** الشارة كانت تعدّ طلبيات العميل في 30 يومًا فقط (رقم)، بينما النافذة تشترط الرقم + المنتج ⇒ فرق بينهما (شواهد حية: 00005/00006 بالهاتف 0669633075 — منتجين مختلفين؛ «0 similar» عبر جميع الطلبيات). التعديل: ① `OrderDuplicateService::countsBySiblings($orderIds, $storeId)` — استعلامان (نافذة المتجر مرة + أصنافها مرة على فهرس `orders_duplicate_scan`) يُعيدان `same_phone`/`same_product` لكل طلبية (الأخوة بنفس العميل == نفس الرقم؛ يتجاهل الذات؛ بلا ترشيح حالة)، والشارة تستخدم **`same_product`** بسقف عرض «×9+»، ② `findSimilar` أُصلح من N+1 (`->with(['status','items'])`)، ③ حالة فراغ ذكية في النافذة: إن استُبعدت المنتجات فقط → رسالة محايدة بمفتاح جديد `duplicate_phone_only_orders` ×4 لغات (بدل الأخضر المضلِّل). اختبارات **6** (رقم+منتج ⇒ شارة ×النافذة، رقم بمنتجات مختلفة ⇒ صفر شارة + رسالة، وحيدة، حالة duplicate، خارج النافذة، استبعاد الذات). تحقّق: **323 ناجح (1177 assertions)** → صفر انحدار؛ `view:cache` + `php -l` سليمان.
- **29.6 — التصنيف الثلاثي «مكررة/احتمال تكرار/سبق الطلب» + نقل الوشم بجانب اسم الزبون (قرارات المستخدم: كشف فوري + تأكيد بضغطة لا كتابة حالة؛ معيار «سبق الطلب» = طلبية سابقة وصلت للإرسال بأي عمر):** ① `countsPriorCarrierOrders(customerIds)` — عدّ طلبيات نفس العميل (أي عمر) بلغت الناقل: `shipping_provider_id`/`delivery_rider_id` أو حالة من `OrderWorkflow::carrier()` (shipped/in_transit/out_for_delivery/delivered/returned). ② `loadOrders`: `repeat_count` = العد ناقص الطلبية نفسها (لا تعلِّم ذاتها) + `dup_level` بأولوية duplicate > probable > repeat (وشم واحد كحد أقصى للصف — بلا تشويش). ③ الوشم **بجانب اسم الزبون** (عمود الزبون + بطاقة الموبايل بجانب الاسم) وحُذف نهائيًا من بجانب رقم الطلبية؛ النغمات: danger/مكررة (×N بسقف 9+)، warning/احتمال تكرار (×N)، neutral/سبق الطلب (بلا عدد). ④ `openDuplicateScan` يصنّف: `duplicateScanLevel` + `duplicateScanRepeatCount`؛ النافذة تظهر شريحة تصنيف (مكررة/احتمال تكرار/سبق الطلب/لا تكرار) + سطر دومًا عندها تاريخ «أُرسل سابقًا للناقل» حتى في حالات مكررة. ترجمات جديدة ×4: `dup_badge_duplicate`/`dup_badge_probable`/`dup_badge_repeat`/`dup_repeat_carrier_sent`. اختبارات **10** (مكرر قوي + تصنيف النافذة، احتمال بمنتجات مختلفة، سبق طلب بعمر 60 يومًا، أولوية مكررة مع بقاء سطر التاريخ، طلبية sent وحيدة بلا وشم لذاتها، خارج النافذة، حالة duplicate، soft-delete، سقف ×9+). تحقّق: **326 ناجح (1204 assertions)** — صفر انحدار. ملاحظة: مفتاح الناقل هو `shipped` لا `sent`؛ تسميات مترجمة كـ«Duplicate» تتلوّث بصدفة مع نطاق الحالات (قرينة النغمات).
---

### جولة التنفيذ 2026-09-06 — **بترقيم البرومت** (تداخل تسميات موثّق: البرومت 29.4=نافذة سجل التتبع، 29.5=فحص عدد الاستعلامات، 29.6=توحيد الحرّاس, 29.7=قائمة «المزيد» المتنقلة؛ وثائق المشروع السابقة كان 29.6=التصنيف الثلاثي، 29.7=التروس→توست+تكافؤ البطاقة — اكتملا في جولات سابقة، ومحتوى البرومت/الجولة الحالية أُضيف فوقهما)

- **29.4 (بعد فحص النطاق لكل بند من البرومت + تحقق من المكانين):**
  - القرار النهائي للمستخدم: الطلبية بعد إرسالها للناقل تُعالَج في صفحة التتبع ⇒ **سجل حالة التتبع يخص صفحة التتبع فقط**؛ صفحة الطلبيات تُبقي شارات التتبع معلوماتية (غير قابلة للنقر) وتُظهر **سجل أحداث الطلبية (audit log)** كإجراء صف.
  - **صفحة الطلبيات:** زر clock في خلية الإجراءات (desktop) + بطاقة الموبايل → قائمة منسدلة (مكوّن Alpine `orderEventsMenu` يقرأ orderId/canView من data-* — سليم مع سياسة عدم حقن Blade في خصائص Alpine) تعرض ≤4 أحداث + «عرض المزيد» → نافذة كاملة `order-events-modal`. **حُذف قسم سجل الأحداث من درج تفاصيل الطلبية نهائيًا.** الحارس: `canViewOrderEventLog` (OWNER/ADMIN، MANAGER للمعيَّن، STAFF أبدًا) → `messages.permission_denied` توست للرفض + `can_view_events` لكل صف في `loadOrders` (بلا N+1 — الأحداث تُحمَّل بكسل عند النقر فقط بحد 15).
  - **صفحة التتبع:** شارة حالة التتبع (جدول + بطاقة) أصبحت زرًا → نافذة `tracking-history-popup` تعرض الخط الزمني المشترك `tracking-history-timeline`. التجزئة: `orders/partials/order-events-menu.blade.php` + `order-events-modal.blade.php` (يستورد `order-events-timeline`) + `tracking/partials/tracking-history-popup.blade.php` — **لا حشر في index**.
  - مفاتيح ×4 لغات: `order_flow.show_more` + `order_flow.order_timeline_loading`.
  - اختبارات: `TrackingStatusHistoryPopupTest` **3** (أحدثها أولًا مع meta، الإغلاق يفرّغ، زر wire:click موجود) + نقحة `OrderEventLogVisibilityTest` (تحميل/فتح النافذة للمالك، رفض STAFF بتوست+فراغ، زر مخفي لصفوف STAFF، عدّ المشغّل مرتين = جدول+بطاقة للمدير (2) ) + `OrdersTrackingColumnTest` البنيوي (page تزنّر `order-events-modal` التي تزنّر `order-events-timeline`). **19 ناجح (63 assertions)**.
- **29.5 — فحص عدد الاستعلامات:** `OrdersPageQueryCountTest` يُقارن **استعلامات البيانات فقط** (orders/items/customers/trackings/providers/variants/products) لصفحة بـ6 طلبيات مقابل 16 — فرق ≤2 (تحميل مسبق كامل؛ بلا N+1). (استعلامات `ltu_languages`/الأدوار خارج العدّ لأنها بنيوية مرتبطة بالترجمة والصلاحيات وليس بصفوف القائمة). **1 اختبار**.
- **29.6 — توحيد الحرّاس «توست بدل 403» بالكامل:** كل حرّاس `abort_unless(...403)` في إجراءات `orders/index.blade.php` (تدفق الإرسال + delete/assign/confirm/manage/transition) أصبحت `if (! canStore(...)) → توست `messages.permission_denied` + return`؛ أبقيَ `mount` ORDER_VIEW 403 فقط (مستوى صفحة). حارس `HasInlineEdit::saveEdit` (البنية التحتية المشتركة — brand/product/order) حوِّل هو الآخر. `sendConfirmedOrder`/membership guard: `unauthorized`→`permission_denied`. **صفر `messages.unauthorized` متبقٍّ** في `livewire/merchant`. اختبارات: 3 ملفات التحرير المباشر (403→توست)، `DirectSendConfirmedOrderTest` + `BulkSendCarrierGroupingTest` (unauthorized→permission_denied). Merchant slice **162 ناجح (739)**, add Unicode.
- **29.7 — قائمة «المزيد» في بطاقة الموبايل:** أيقونة `ellipsis-horizontal` (مفتاح `general.more`) → لوحة منسدلة بمكوّن `orderMoreMenu` (fixed top/left، data-* نظيف) تنقل confirm/send/edit/reassign/delete من الصف المكتظ إلى **صفوف 44px** (min-h-[44px]) تُغلق اللوحة بعد الفعل (`confirmDelete(); close()` عبر سلسلة النطاق). `OrdersMobileMoreMenuTest` **2** (مالك = menu+44px، STAFF = menu بلا أي صف). `panel.js` سجّل `orderMoreMenu` + بني `npm run build`.
- **نقحة 29.4b — ظهور القوائم على الشاشات الصغيرة باحترافية (طلب مباشر):** ① قائمة سجل أحداث الطلبية في ≤sm (وأي تراكب للحافة على ≥sm): مكوّن `orderEventsMenu()` الآن **يثبّت اللوحة ضمن حدود الشاشة** (إسقاط يمين/يسار + فلْب صدري بعتبة 8px)، وعلى الهاتف (<640px) تتحول من قائمة معلّقة إلى **ورقة سفلية** كاملة العرض: غمّة `bg-black/40 backdrop-blur-sm` تُغلق بالنقر + مقبض سحب (`h-1 w-10`) + زر إغلاق `x-mark` (`general.close`) + عنوان «سجل الطلبية»، مع `pb-[env(safe-area-inset-bottom)]` وتقليص العرض للبطاقات؛ «عرض المزيد» صارت CTA بارزة `accent-50→accent-100`؛ في ≥sm تحافظ على `w-80` المعلّقة بعنوانها. ② نافذة سجل التتبع (`tracking-history-popup`): مودال `edz-modal` أصلًا ورقة سفلية في الموبايل، فأُضيف **مقبض السحب** (`edz-modal__handle`) + حشو مضبوط `p-4 pt-1 sm:p-6 sm:pt-5` + رأس طلبية بخط عريض (رقم + T.N. بنمط tabular) بدل السطر الرمادي. **348 ناجح (1268 assertions)** — صفر انحدار (عدّ `OrderEventLogVisibilityTest` «عدّ المشغّل مرتين» سليم بعد التعديل)؛ `npm run build` + `view:cache` سليمان.
- **تحقّق نهائي للجولة:** السويت كاملة **348 ناجح (1268 assertions)** — صفر انحدار؛ `view:cache` ناجح ثم `git checkout -- storage/framework/views/`؛ البناء ناجح.
- **جولة توحيد القوائم + السبينرات (طلب مباشر 2026-09-06):** كل القوائم/البوب أوفرز في صفحة الطلبيات تتحول في الموبايل إلى **ورقة سفلية موحدة** (غمّة `bg-black/40 backdrop-blur-sm` تُغلق بالنقر + مقبض `h-1 w-10` + رأس بعنوان + زر `x-mark` + `pb-[env(safe-area-inset-bottom)]` + `max-h-[70vh] overflow-y-auto` + `rounded-t-2xl shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)]`) مع عودة ≥sm إلى اللوحة المعلّقة المثبّتة داخل نطاق الشاشة (`menuStyle` عبر مكوّنات Alpine: `orderEventsMenu` (سابقًا)، `orderMoreMenu`، `orderRowActions.openStatusMenu`، `dropdownPosition`)؛ شملت: قائمتي الحالة (جدول + بطاقة، `sm:w-56`، عنوان `merchant_panel.status`)، قائمة «المزيد» (`sm:w-60`، عنوان `merchant_panel.actions` + سبينرات confirm/send/edit/reassign)، بوابة الفلاتر (`:style="menuStyle"` + عروض `sm:w-48/sm:w-52` + `sm:max-h-64` للقوائم + عنوان `buttons.filter`)، القوائم الثلاث في الشريط (المصدر `merchant_panel.source`/نوع التوصيل `storefront.delivery_type`/شركة الشحن `order_flow.filter_provider`، تنقل `sm:absolute sm:bottom-auto`)، وقائمة الإسناد في شريط الجماعي (`merchant.bulk_assign_agent`، تنقل `sm:right-0`). **السبينرات** أُضيفت لكل أزرار الفعل في الصفحة بلا استثناء: طلب جديد، الأعمدة، تفاصيل/تأكيد/إرسال/تعديل/إعادة إسناد لكل صف (جدول + بطاقة)، تأكيد الحالة، إرسال للناقل، تأكيد الحالة الجماعية، إرسال جماعي — عبر `<x-edz.spinner wire:target="method(...)">` + `wire:loading.remove` + `wire:loading.attr="disabled"` (الأزرار التي كانت تملك سبينرًا، مثل refresh، بقيت). ترجمات العناوين موجودة ×4 لغات (تحقّق آلي). **الاختبارات: 348 ناجح (1268 assertions)** — صفر انحدار؛ `npm run build` + `view:cache` + `git checkout -- storage/framework/views/` سليمة.

- **جولة الأيقونات + القوائم المشتركة + سلسلة اختيار التوصيل (طلب مباشر 2026-09-06):**
  - **الأيقونات:** مراجعة `components/edz/icon.blade.php` — أسماء مُستعملة لم تكن مُعرَّفة فكانت تقع على أيقونة `grid` الاحتياطية (مثل زر سجل الأحداث، قائمة «المزيد» على الهاتف، فلتر، uuid، QR، arrow-u-turn، الاتجاهات...). أُضيفت مسارات SVG (heroicons v2 outline) لـ 14 مفتاحًا (`arrow-uturn-left`, `building-library`, `building-storefront`, `clipboard`, `clock`, `cloud-upload`, `device-phone-mobile`, `ellipsis-horizontal`, `funnel`, `information-circle`, `paper-airplane`, `qr-code`, `trending-up`, `user-plus`) + أسماء مترادفة 5 (`arrow-forward-outline`→`arrow-right`, `bag-outline`→`bag`, `checkmark-circle-outline`→`check-circle`, `search-outline`→`magnifying-glass`, `x`→`x-mark`). إعادة فحص آلي: صفر اسم مفقود. الأيقونات SVG مضمَّنة (تُرندر من PHP) — لا تحتاج `npm run build`.
  - **القوائم المشتركة في كل اللوحة (البوب-أوفرز المتبقية):** `components/edz/dropdown.blade.php` (المستخدم في `user-dropdown` و`notification-dropdown` فقط — القائمة الوحيدة المشتركة) أُعيد بناؤه بالكامل: غمّة `edz-dropdown__scrim` (الغمّة مقابل اللوحة تُغلق بالنقر، `@click.away` لا يتعارض) + لوحة تتحول في الموبايل إلى **ورقة سفلية** (عرض كامل، `max-height min(75vh,480px)`، مقبض/حشو سفلي آمن) و≥640px إلى لوحة معلّقة (`--edz-dropdown-width`، `is-right`/`is-left` عبر `align` — إصلاح خطأ `$alignment` غير المعرّف). SCSS في `resources/css/components/_dropdown.scss` — يتطلّب `npm run build`. مسح اللوحة: باقي «القوائم» فيوردون/مختارات مضمّنة داخل حاوياتها (لا تحتاج تحويلًا).
  - **سلسلة اختيار التوصيل (نموذج طلب التاجر): الترتيب أمسح `شـركة → نوعية → ولاية → بلدية → المكتب`**: أُعيد ترتيب `order-form-modal.blade.php` — الشركة أولاً (ظاهرة دائمًا) ثم أزرار النوع ثم ولاية/بلدية ثم المكتب (نقاط استلام فقط). `rebuildFormOffices` يحصر المكاتب ببلدية المختارة ويثبّت المكتب تلقائيًا إن كان للبلدية مكتب واحد (`delivery_type === 'stopdesk'` + بلدية + مطابق واحد) ويفرّغ الانتقاء المنقضي؛ `$changeDeliveryType` يعيد بناء الخيارات عند القفز إلى stopdesk (دون تغيير الشركة). تلميحات إرشادية حسب الخطوة (اختر الشركة أولاً/اختر المدينة). مفتاح `merchant_panel.select_company_first` ×4 لغات. **صفحة الدفع (الزائر)** تتبع ترتيبها الطبيعي (ولاية → بلدية → نوعية → مكتب مجمّع بالشركة) — سليم دون تغيير.
  - **أدلة 2026-09-06:** استهداف 24 ناجح (76 assertions) — منها `OrdersPageQueryCountTest` (فحص عدد استعلامات الأداء، فرق ≤2).

## جولة إصلاح دروب-داون الشريط العلوي (نافبار الإشعارات + زر قائمة المستخدم) — 2026-09-06 ✅

**عرض المستخدم:** القائمتان تتندلان للأعلى ويظهر منهما الجزء السفلي فقط على الشاشات الصغيرة، ثم بعد محاولة إصلاح بـ `x-teleport` تعطّلتا تمامًا في كل المقاسات.

**التشخيص الجذري:** `.edz-topbar` (sticky) يحمل `backdrop-filter` — أي عنصر له `backdrop-filter` يصبح **containing block** لـ `position: fixed` من نسله. لذا ورقة `edz-dropdown__panel` السفلية للموبايل (`position: fixed; bottom: 0`) والغمّة كانتا تُثبَّتان نسبةً للشريط نفسه (أعلى الشاشة) لا للشاشة ⇒ تُرسم للأعلى ويظهر منها شريط سفلي فقط. (اللوحة المعلّقة للدسكتوب `position: absolute; top: 100%` غير متأثرة لأن `.edz-dropdown` الأقرب `relative` يربطها.)

**ما تَمّ (النسخة النهائية — مبنية ومتحقَّق منها):**
- **تراجُع كامل عن تجربة `x-teleport`** في `components/edz/dropdown.blade.php` (كانت نحنّلها إلى `body` مع تحديد الموضع بالـ JS — لم تعمل في الموبايل والدسكتوب فَأُلغيت نهائيًا). عاد المكوّن للتصميم البسيط المستقر: غمّة `edz-dropdown__scrim` شقيقة تُغلق بالنقر، الزر المطلق للـ trigger، واللوحة `x-show`/`x-cloak`/`x-transition` مع `is-right`/`is-left` عبر `align` و`--edz-dropdown-width`.
- **إصلاح السبب الجذري في `layouts/_topbar.scss`:** أُزيل `backdrop-filter` و`background-color: var(--edz-glass-bg)` من عنصر الشريط نفسه، ونُقل الزجاج إلى `&::before { position:absolute; inset:0; z-index:-1; background-color:var(--edz-glass-bg); backdrop-filter:blur(var(--edz-blur-md)); }` — المظهر الزجاجي محفوظ للتظرية، لكن الشريط لم يعد يحاصر `position: fixed`، فالورقة والغمّة في الموبايل تُثبَّتان للشاشة الآن.
- **`components/_dropdown.scss`:** الموبايل ورقة سفلية كاملة (`fixed; inset-inline:0; bottom:0; width:100%; max-height:min(75vh,480px); overflow-y:auto` + حشو سفلي آمن)، والـ ≥640 لوحة معلّقة (`absolute; top:100%; width:var(...); is-right/is-left`). أُضيف أيضًا أسلوب `.edz-dropdown__title` المفقود (عنوان «الإشعارات» كان بلا تنسيق).
- **تصحيح إجراء العمل (هامة):** نهج `php artisan view:cache` ثم `git checkout -- storage/framework/views/` كان **يستعيد ملفات مضبوطة قديمة متتبَّعة في git** — فكان المتصفح يخدم نسخة قديمة من المكوّن طوال جولات سابقة (تفسير تعارض الشكاوى). الإجراء الصحيح الآن: `php artisan view:clear` ثم `view:cache` و**ترك المجلد نظيفًا حديثًا** (تظهر تغييرات في git ولا يُعاد استرجاعها).
- **أدلة:** مخرَج `Blade::render` المترجَم يطابق المكوّن الحالي تمامًا (scrim + `--edz-dropdown-width` + `is-right` + `x-cloak`/transitions)، وفحص العبوة المبنيّة يؤكد: الشريط بلا `backdrop-filter` مباشر مع `:before` زجاجي، ولوحة الموبايل `fixed` تمتد للشاشة، والدسكتوب `absolute` مع `is-right`. **السويت كاملة 348 ناجح (1268 assertions)** — صفر انحدار.

## متابعة: إصلاح سكرول أفقي عند فتح دروب-داون الإشعارات واليوزر منيو (استكمال لنفس الجولة) — 2026-09-06 ✅

**عرض المستخدم:** عند فتح القائمتين يظهر **سكرول بار أفقي** — عارض مرتبط بطريقة النزول في RTL مقابل LTR.

**التشخيص:** تثبيت لوحة الدسكتوب كان CSS خالصًا (`is-right{right:0}`/`is-left{left:0}` نسبةً لموضع الـ wrapper قرب أقصى نهاية السطر — يمين في LTR ويسار في RTL). فعند RTL تتمدد لوحة 350px جهة اليسار فتتجاور حدود الشاشة وتُحوّل الصفحة إلى تمرير أفقي؛ مطلوب **تقييد (clamp) اللوحة ضمن أبعاد الشاشة** لا تجاهل الحافة.

**الحل الاحترافي (بلا ترقيع — بنفس نمط القوائم القائمة في المشروع `orderEventsMenu`/`orderMoreMenu`/`dropdownPosition`):**
- **`resources/js/components/edz-dropdown.js` جديد** — مكوّن Alpine مسمّى `edzDropdown` بنفس تقنية `menuStyle`: الموبايل (<640px) يمسح inline ويعتمد ورقة CSS السفلية، وsm+ يقيس الفعلية (`offsetWidth/offsetHeight`) عبر `$nextTick` ويعيّن `fixed top/left` مع **تثبيت داخل هامشين 8px** وعكس للأعلى عند ضيق العمود + محاذاة نحو الجهة المتاحة الأوسع → آمن في RTL وLTR بلا سكرول أفقي، وبلا تغيير DOM (`x-teleport` مستبعد نهائيًا).
- **`dropdown.blade.php`:** `x-data="edzDropdown()"`، `x-ref` للزر واللوحة، `:style="menuStyle"`، حارس قياس `edz-dropdown__positioning` (opacity/pointer-events قبل التموضع — بلا وميض)، والـ var `--edz-dropdown-width` انتقل إلى جذر المكوّن ليُورَّث للوحة (فلا تمحوه الربط `:style`).
- **`_dropdown.scss`:** كتلة الدسكتوب أصبحت `position:fixed; inset:auto` بلا `is-right/is-left`/`top:100%` (التموضع من JS)، مع `max-width: calc(100vw - 1rem)` و`max-height: min(60vh, 480px)` ضمانًا للحدود؛ أُضيف `.edz-dropdown__positioning`.
- **`panel.js`:** تسجيل `Alpine.data("edzDropdown", edzDropdown)`.
- **أدلة:** العبوة المبنيّة تتضمن الحارس و`max-width` والكتلة الجديدة، وحزمة panel تحوي `menuStyle`/`matchMedia`/`innerWidth` (clamp داخل الحزمة)، ومخرَج `Blade::render` يطابق المكوّن الجديد. **السويت كاملة 348 ناجح (1268 assertions)** — صفر انحدار.

**مكانها في المراحل المنجزة:** استكمال/تحصين لجولة «إصلاح دروب-داون الشريط العلوي» المنجزة أعلاه (نفس المكوّن المشترك `components/edz/dropdown.blade.php` وزري النافبار) — لا مرحلة جديدة؛ تُعدّ جزءًا من مرحلة واجهة «الإشعارات + قائمة المستخدم» المنجزة.

## زر «فلاتر» الموحّد في شريط الطلبيات (قرار المستخدم: بوب أب + إغلاق فور الاختيار) — 2026-09-06 ✅

**الطلب:** إخفاء فلاتر الشريط الثلاثة (المصدر / نوع التوصيل / شركة الشحن) خلف زر واحد باسم «فلاتر» يفتح بوب أب فيه الفلاتر بدلًا من تشويش الشريط (تأييد أوبل: تقليل الزخرفة وتأخير الإظهار). **القرار: إغلاق البوب أب فور كل اختيار.**

- **`components/edz/dropdown.blade.php`:** إضافة prop اختيارية `triggerClass` تُمزج على زر الـ trigger — الاستخدامات الحالية (إشعارات/مستخدم) بلا تغيير، وبوب أب الفلاتر يستقبل `edz-btn edz-btn--ghost edz-btn--sm`.
- **`components/_dropdown.scss`:** إعادة هيكلة `.edz-dropdown__trigger` بحيث تعطّل التصفير (padding/background/border) **إلا** عبر `&:not(.edz-btn)` — فلا يتعارض مع أزرار `edz-btn` (الشريط أعلى ومنفصل). إضافة `.edz-dropdown__section` (فاصل بين المجموعات) و`.edz-dropdown__section-title` (عنوان صفّي بأحرف كبيرة).
- **`orders/index.blade.php` (شريط الأدوات 2625—2760):** الأزرار الثلاثة المستقلة استُبدلت بـ `<x-edz.dropdown width="340px" trigger-class="edz-btn edz-btn--ghost edz-btn--sm">`: trigger = سبينر `setFilter` + أيقونة `funnel` + «فلاتر» (`merchant_panel.filters`) + **شارة عدّ نشطة** (`min-w-[1.25rem] rounded-full bg-accent-600 text-white`) عند `>0` + سهم؛ كل فلاتر العدد الثلاثة عبر `collect(['source','delivery_type','shipping_provider'])->filter(filled)`. المحتوى = رأس ورقة سفلية (مقبض + عنوان + X) على الهاتف فقط + 3 مجموعات بمفاتيح الترجمة القائمة؛ خيارات «—» وقيمها بصفوف `edz-dropdown__item justify-between` مع `check` (opacity للاختيار الحالي) وتظليل `bg-accent-surface text-accent-fg font-semibold` + `aria-pressed`. **كل خيار**: `wire:click="setFilter(...)"` + `@click="close()"` (إغلاق فور الاختيار).
- **ترجمات:** `merchant_panel.filters` ×4 لغات («فلاتر»/Filters/Filtros/Filtres) في ذيل `merchant_panel.php` لكل لغة.
- **ملاحظات:** لا تغيير في الاستعلامات (`allProviders` قائم)؛ تحسّن أداء: لّوحة + غمّة واحدة بدل 3 (تظهران عند الفتح فقط) وهدف Livewire واحد. البوب أب المعلَّق يبقى داخل حدود الشاشة عبر `edzDropdown` القائم (آمن RTL/LTR بلا سكرول أفقي — مُثبَت في الجولة السابقة). شريط الأعمدة/السلة/العداد والشارات أسفل الشريط بلا تغيير.
- **أدلة:** ① `view:cache` نجح على `index.blade.php` الكامل، ② `Blade::render` للمكوّن يؤكد وضع `trigger-class` على الزر، ③ العبوة المبنيّة تحتوي `.edz-dropdown__section(+)`/`__section-title` و`:not(.edz-btn)` و`edz-dropdown__trigger` و`min-w-\[1.25rem\]` و`bg-accent-600`، ④ **السويت كاملة 348 ناجح (1268 assertions)** — صفر انحدار.

## فحص شامل للتأكد + استكمال قوائم storefront-settings (طلب التحقّق) — 2026-09-06 ✅

**فحص كل بنود قائمة المراجعة آليًا (لا اعتمادًا على التوثيق) — والنتائج:**

- **الأيقونات (بند 1):** قارنت آليًا خريطة `components/edz/icon.blade.php` (95 مفتاحًا) مع كل `x-edz.icon name="…"` الثابتة في `resources/views` (83 اسمًا مستعملًا) → **صفر اسم مفقود**؛ الترادفات (aliases) تُحل عبر `$aliases`، والاسم غير المعرّف يقع بأمان على `grid` الاحتياطية. أزرار سجل الأحداث (`clock`) وقائمة «المزيد» (`ellipsis-horizontal`) مُعرَّفتان وتُعرضان فعلًا.
- **توحيد البوب-أوفرز/القوائم في اللوحة (بند 2):** مسح تام لـ `livewire/merchant` — صفحات الطلبيات (أعمدة/تأكيد/حالة/إرسال/فلاتر/سجل/المزيد) وشريط الجماعي (إسناد) كلها ورقة سفلية موحّدة في الموبايل + لوحة مثبّتة ≥sm. **المنقَّص الوحيد = منتقيا storefront-settings** فأُكملا بنفس النمط الموحّد (غمرة `bg-black/40 backdrop-blur-sm` تُغلق بالنقر + مقبض `h-1 w-10` + رأس بعنوان + `x-mark` + `pb-[env(safe-area-inset-bottom)]` + `max-h-[70vh]` + `rounded-t-2xl` + عودة ≥sm للوحة المعلّقة): ① **منتقي المنتج** (بحث+قائمة) في `storefront-settings.blade.php`، ② **شبكة الأيقونات** في `social_proof.blade.php` (تبقى تفتح أعلى/أسفل في الدسكتوب حسب `$i===2`). أكورديون `section-editor` ليس قائمة (متجاهل). تم البناء (`npm run build` 7.13s) — الأدوات الجديدة (`col-span-7`/`sm:left-0`/`sm:overflow-hidden`/`grid-cols-7`) وُلّدت في العبوة.
- **الأداء (بند 3):** `OrdersPageQueryCountTest` → **1 ناجح** (3 assertions): عدّ استعلامات البيانات مسطّح مع نمو الصفوف (فرق ≤2، تحميل مسبق كامل بلا N+1). مراجعة سلسلة المكاتب: `rebuildFormOffices` استعلام واحد + `with('city:id,name')` (لا N+1 لأسماء المدن)؛ `StopdeskOfficeSync` محادثة API لمرّة واحدة عند تغيير الشركة (خارج حمل الصفحة — مقبول). `loadFilterCities`/`loadFilterStopdeskPoints` استعلام واحد لكل منهما.
- **سلسلة التوصيل في النموذج والفلاتر (بند 4):** النموذج: شركة → نوع (`changeDeliveryType`) → ولاية (`loadCities`) → بلدية → مكتب (قائمة، أو **تثبيت تلقائي عند مطابق واحد** `delivery_type==='stopdesk'`). الفلاتر: ولاية → بلدية (`loadFilterCities`) + شركة → مكتب استلام (`loadFilterStopdeskPoints`). كلا المسارين مترابط وصحيح.
- **فرع 29 كاملًا (بند 5):** استهداف **51 ناجحة (189 assertions)** عبر ملفات مراحل 29.1–29.7 (29.1 `OrderEventLogVisibilityTest`، 29.2 `DirectSendConfirmedOrderTest`، 29.3 `BulkSendCarrierGroupingTest`، 29.4 `OrdersTrackingColumnTest`، 29.5 `OrderDuplicateBadgeTest`+`OrderDuplicateDetectionTest`، 29.6 subdivision، 29.7 `OrdersMobileMoreMenuTest` + نقحة 29.4b `TrackingStatusHistoryPopupTest`) — وإن كان فحص الأداء `OrdersPageQueryCountTest` ناجحًا أعلاه.
- **التحقّق الختامي:** السويت كاملة **348 ناجح (1268 assertions)** — صفر انحدار بعد تعديلات storefront-settings؛ `view:clear` + `view:cache` (بلا استرجاع git) و`npm run build` سليمان.

## إصلاح «الفلاتر النشطة» كرِئات بأسماء الحقول — 2026-09-06 ✅

**عرض المستخدم:** شريط «الفلاتر النشطة» أسفل شريط الطلبيات يعرض قيمًا مجردة دون أسماء الحقول، وبعض الفلاتر (المصدر/المنتج/المبلغ) لا تلحق شاراتها أصلًا.

- أُعيد بناء كتلة «ملخص الفلاتر النشطة + مسح الكل» كاملة: كل شريحة تعرض الآن **اسم الحقل + القيمة** (`<span class="font-semibold opacity-75">{{ __('…') }}:</span>` + القيمة بحرّاس `max-w-[14rem] truncate` + `ps-2`). خريطة أسماء بـ 4 لغات: `merchant_panel.status/state/city/office/assigned_agent/confirmed_by/send_from_carrier_warehouse/date/amount/weight/product/address/notes/shipment_type/home_delivery_label/stop_desk_label/store`، `order_flow.filter_provider`، `storefront.delivery_type/home_delivery/stop_desk`، `merchant.delivery_man`، `buttons.yes/no` (تحقّق آلي: المفاتيح ×4 لغات موجودة).
- شرائح جديدة: **المصدر** (متجر ↔ موصّل عبر `merchant_panel.store` / `merchant.delivery_man`)، **المنتج** (يتعبّأ من `filters.product` النصي، وإلا اسم الصنف الأول من `filterProducts` المطابق لـ`product_id`)، **المبلغ** (مدى). حرّاس القيم: لا تُعرض شريحة فارغة أبدًا (`?? $this->filters[...]`).
- بوابة الشرائح: استبدال `array_filter($this->filters)` بـ `$hasActiveFilters` (معالجة خاصة: `send_from_carrier_warehouse` تُعدّ نشطة عند `$v !== null` وليس truthy — فتبقى `false` ظاهرة كشريحة «إرسال من مخزن الناقل: لا»).
- **أدلة:** `view:cache` (FRESH-CACHE-OK) + `npm run build` 13.05s + الأدوات الجديدة (`pe-2`/`ps-2`/`max-w-\[14rem\]`/`truncate`) وُلّدت في العبوة المبنيّة. السويت بعدها **350 ناجح (1278 assertions)** — صفر انحدار.

## Phase 30 — فرع 30.1 ✅ (شركة الشحن لطلبيات التوصيل المنزلي) — 2026-09-06

**التشخيص (تحقّق من الشيفرة الفعلية لا النصوص):** ① بنية المودال كانت قد أُعيد ترتيبها سابقًا — حقل الشركة **خارج** كتلة stopdesk ويظهر للـ home أصلًا (المكتب فقط مشروط بـ`delivery_type==='stopdesk'`)؛ ② قواعد التحقق صحيحة أصلاً (`shipping_provider_id` `required_if:delivery_type,stopdesk`/`nullable|exists`، `stopdesk_point_id` `required_if:delivery_type,stopdesk` — لم تُرخَ ولم تُشدَّد)؛ ③ **الخلل الحقيقي:** مسار الإنشاء كان يفرض `shipping_provider_id = null` كلما كان `delivery_type === 'home'` (سطر 2340) — فاختيار الشركة في المودال لطلبية home كان يُهمَل عند الحفظ، بينما مسار التعديل كان يحفظه (سطر 2495) ⇒ تناقض؛ ④ `loadFormOffices` كان يزامن/يبني مكاتب حتى للـ home (استدعاء شبكة بلا فائدة).

**ما تَمّ:**
- `submitCreate`: يحفظ الشركة لكلا النوعين (`?: null`)، والمكتب يبقى مشروطًا بـ stopdesk؛ التعليق مُحدَّث.
- `loadFormOffices`: حارس مبكّر — إن كان النوع غير stopdesk → يفرّغ `formOffices` و`stopdesk_point_id` ويُرجع (لا تحميل مكاتب للـ home).
- `changeDeliveryType`: الانتقال إلى stopdesk مع شركة مختارة → استدعاء `loadFormOffices(preserveOffice: true)` (قائمة مكاتب مُزامَنَة مُفعَّلة)، وبلا شركة → `rebuildFormOffices`؛ الانتقال إلى home → يمسح المكتب وخياراته.
- بلا مفاتيح ترجمة جديدة (واجهة HTML تغيّرت فقط). لم تُلمَس قواعد التحقق (منعٌ للتشدّد/التخفيف).

**اختبارات القبول (جديدة، ملف `OrderOfficeSelectionTest.php`):**
- «تُنشأ طلبية منزلية بالشركة المختارة وبلا مكتب» — التكرار عبر المودال، وتأكيد: home + `shipping_provider_id` غير null (يُحفظ فعلًا) + `stopdesk_point_id` null + العلاقة `shippingProvider` سليمة.
- «اختيار شركة لطلبية home لا يحمّل ولا يشترط مكتبًا» — استدعاء `loadFormOffices` بنوع home: `formOffices == []` و`stopdesk_point_id == ''` رغم وجود نقطة استلام.
- **مخرَج فعلي:** الملف المستهدف **6 ناجح (29 assertions)** ثم السويت كاملة **350 ناجح (1278 assertions)** — صفر انحدار؛ `view:clear` + `view:cache` (FRESH-CACHE-OK).

### جولة تحسينات التوصيل 30.2 ✅ (سلسلة اختيار التوصيل بالترتيب + بحث + نافذة عدل سريعة + إصلاح دروب داون)

**قرار المستخدم النهائي (يحل تعارض 30.2 مع السلسلة المعتمدة سابقًا):** الترتيب المعتمد هو نفسه الموجود في المودال: **شركة ← نوع ← ولاية ← بلدية ← مكتب** (في الجدول والبوب أب معًا). مواصفة «الوجهة قبل التوصيل» ملغاة/معدَّلة حسب الطلب. أجوبة التوضيح: الجدول يفتح نافذة سلسلة كاملة؛ البحث محلي فوري بلا استدعاءات خادم؛ معالجة شاملة لمشاكل الدروب داون.

**ما تَمّ:**
- **تفعيل البحث المحلي** (`search` prop) في القوائم الأربع (`x-edz.select`) في `order-form-modal.blade.php` (شركة/ولاية/بلدية/مكتب) + سلسلة بحث (searchable `filteredOptions` بفحص label/hint) — بحث فوري بلا خادم.
- **نطاق المكاتب بالبلدية (إصلاح موجود):** `rebuildFormOffices` كان يفلتر بالشركة والولاية فقط. أُضيف نطاق البلدية `city_id = X OR NULL` (المكاتب الإقليمية غير المقترنة ببلدية تظل ظاهرة، وتُرتَّب أولًا مطابقة البلدية) — فيطابق «مكاتب البلدية» المطلوب.
- **تغيير البلدية يعيد بناء المكاتب:** أُضيف `wire:change="rebuildFormOffices()"` إلى قائمة البلدية في المودالين.
- **نافذة عدل سريعة للتوصيل (جدول + موبايل):** زر «توصيل» (أيقونة truck) في خلية أفعال الديسكتوب وقائمة «المزيد» للموبايل → `partials/delivery-edit-modal.blade.php` بقوائم بحث بأربع خطوات بالسلسلة نفسها. طرق Volt جديدة: `openDeliveryModal` (يستعيد الشركة/النوع/الولاية/البلدية/المكتب عبر `loadFormOffices(preserveOffice:true)` ويحمل بلديات الولاية)، `closeDeliveryModal`، `saveDeliveryModal` (تحقق من حقول التوصيل فقط: شركة، نوع، ولاية، بلدية، مكتب مع فحوصات تطابق الولاية/الشركة/النوع) + إعادة `recalculateOrderShipping` + توست + حدث تدقيق `order_delivery_updated` (قبل/بعد/المتغيّرات).
- **حماية حالة الطلبية:** النافذة تُرفض للطلبيات المشحونة فما بعد (`cannot_edit_shipped`)، وصِلة بالنموذج الرئيسي لا تتعارض (لا يمكن فتح مودالين معًا).
- **تحصين edz-select.js:** ورقة الموبايل بعرض موحّد (max 480px) + إعادة تموضع ثابتة عند تمرير أي scroller/تغيير حجم النافذة أثناء الفتح (كانت تتجمد في مكانها القديم داخل المودال المتمرِّر).
- مفاتيح ×4 لغات: `edit_delivery`, `delivery_updated`, `city_without_state`, `invalid_city_for_state`, `invalid_office`, `office_home_invalid`, `office_provider_mismatch`.

**اختبارات القبول (توسعة `OrderOfficeSelectionTest.php` — 6 جديدة):**
- نافذة التعديل تستعيد الشركة/المكتب/النوع/الولاية/البلدية وتحمّل المكتب.
- التبديل إلى home يمسح المكتب ويحفظ الشركة.
- حفظ ناقل ومكتب جديدين.
- رفض مكتب لا يتبع الناقل (`office_provider_mismatch`).
- حجب النافذة للطلبيات المشحونة.
- نطاق المكاتب بالبلدية: تظهر بلدية المختارة + الإقليمية، ولا تظهر بلدية أخرى.
- **مخرَج فعلي:** الملف المستهدف **12 ناجح (44+ assertions)** ثم السويت كاملة **356 ناجح (1301 assertions)** — صفر انحدار. `view:cache` (FRESH-CACHE-OK) + `php -l` سليمة + `npm run build` (6.96s).

## Phase 30 — فرع 30.3 ✅ (وزن تلقائي من الأصناف — يُبقى قابلًا للتعديل يدويًا) — 2026-09-06

**القراران (أجوبة المستخدم):** الترتيب المعتمد **شركة ← نوع ← ولاية ← بلدية ← مكتب** (يبقى كما هو)، والبدء بـ**30.3 الوزن التلقائي**.

**التشخيص:** `form['weight_kg']` كان حقلًا يدويًا مستقلًا عن الأصناف — لا يُحدَّث عند إضافة/تعديل كمية/حذف أي صنف، بينما الملخص يحتسب الوزن من الأصناف لحظيًا فقط ⇒ انجراف بين الحقل المرفق والوزن المحفوظ فعليًا.

**ما تَمّ (index.blade.php):**
- دالة `$recalcFormWeight` جديدة: مجموع `weight × quantity` عبر `form['items']` (وزن `variant->weight` المضمَّن أصلًا في كل صنف) → `form['weight_kg'] = round(sum,3)` (أو `''` إن كان صفرًا).
- تُستدعى بعد كل تحوّر حقيقي في الأصناف فقط: `addFormItem`، `addFormItemByBarcode` (المساران: تكرار + إضافة)، `removeFormItem`، `updateFormItemQty` — **وليس** `updateFormItemPrice` ⇒ التعديل اليدوي للوزن لا يُكتَب فوقه ما لم تتغيّر الأصناف فعلًا.
- ملخص المودال (order-form-modal) قرأ نفس المصدر الوحيد: `$form['weight_kg']` بدل إعادة الحساب من الأصناف.
- تلميح تحت حقل الوزن + مفتاح ×4 لغات `order_flow.weight_auto_hint` (عربية فصحى/EN/FR/ES) في `order_flow.php` الأربعة.

**اختبارات القبول (جديدة، ملف `tests/Feature/Merchant/OrderWeightAutoCalcTest.php` — 6):**
- `addFormItem` يحسب الوزن التلقائي من وزنَي متغيرين (0.5+2.25=2.75).
- `updateFormItemQty` يضرب الوزن في الكمية (1.5×3=4.5).
- `removeFormItem` يعيد الحساب بعد الحذف.
- `addFormItemByBarcode` يحسب من الشيفرة.
- `updateFormItemPrice` لا يكتُب فوق تعديل يدوي `weight_kg` (يظل 9.5).
- طلبية تُنشأ فعليًا فتُخزَّن `weight_kg` المحسوب (2.75 مع home).
- **مخرَج فعلي:** الملف الجديد **6 ناجح (13 assertions)**، والمجاورات (الكميات/المكاتب/التكرار) **22 ناجح (76 assertions)**، والسويت كاملة **362 ناجح (1314 assertions)** — صفر انحدار. `view:clear`+`view:cache` (FRESH-CACHE-OK) + `php -l` سليمة لكل الملفات + `npm run build` (7.03s).

## Phase 30 — فرع 30.2 ✅ (لا استرجاع صامت لمكتب محدد — الإبطال بتوست مرئي) — 2026-09-06

**السياق:** بعد اعتماد 30.3، بقايا 30.2 هي فقط ضمان عدم مسح مكتب محدد مسبقًا بصمت عند تغيير الوجهة — إذ إن القرار المعتمد سابقًا يُبقي الترتيب **شركة ← نوع ← ولاية ← بلدية ← مكتب** (مواصفة «الوجهة قبل التوصيل» ملغاة).

**التشخيص:** في `rebuildFormOffices`، كانت الوجهة الجديدة (ولاية/بلدية) تسقط المكتب المحدد سابقًا من نطاق القائمة فكان الفرع `elseif` يفرّغ `stopdesk_point_id` بصمت دون أي إشعار.

**ما تَمّ (index.blade.php):**
- التقاط `$wasSelected` أول `rebuildFormOffices` قبل إعادة البناء.
- في الفرع `elseif` (المكتب المحدد خارج نطاق الوجهة الجديدة): إن كان `$wasSelected` غير فارغ وما يزال `form.stopdesk_point_id` مطابقًا له → بث `swal:toast` (icon=warning) بالرابط `__('order_flow.office_reset_for_destination')` قبل التفريغ — أي إبطال مرئي بدل الصامت.
- **لا ينطلق عند:** اختيار سابق فارغ، أو الاستبدال التلقائي المشروع (بلدية بمكتب واحد ← `$cityOffices->count()===1` يختار قبل الوصول للـ elseif)، أو تغيير الشركة (لأن `loadFormOffices` تفرّغ مسبقًا)، أو مكتب ما يزال ضمن النطاق.
- مفتاح ×4 لغات `order_flow.office_reset_for_destination` (عربية فصحى/EN/FR/ES) في `order_flow.php` الأربعة.
- مودال الإنشاء ونافذة التعديل السريع (`delivery-edit-modal`) يشتركان في نفس `rebuildFormOffices` ⇒ الإصلاح يغطي المسارين معًا.

**اختبارات القبول (توسعة `OrderOfficeSelectionTest.php` — 3 جديدة):**
- تغيير البلدية إلى بلدية بلا مكاتب: المكتب يُفرَّغ + توست warning بالمفتاح `office_reset_for_destination`.
- تغيير الولاية إلى ولاية بلا مكاتب (عبر `loadCities`): المكتب يُفرَّغ + نفس التوست.
- مكتب ما يزال في النطاق (بلدية بمكتبَين): المختار يبقى + **لا** توست (`assertNotDispatched`).
- **مخرَج فعلي:** الملفان معًا (مكاتب + وزن) **21 ناجح (74 assertions)**، والسويت كاملة **365 ناجح (1323 assertions)** — ارتفاع من 362/1314 بلا انحدار. `php -l` سليمة لكل الملفات + `view:clear`+`view:cache` (FRESH-CACHE-OK).

## Phase 30 — فرع 30.4 ✅ (دمج الشبكة المالية — partial مشترك بمصدر واحد لكل مساري الإنشاء والتعديل) — 2026-09-06

**القرار (اعتماد المستخدم «اكمل»):** دمج الملخص المالي لمودال الطلب في شبكة واحدة متجاوبة (5 خلايا قراءة فقط) تخدم مودالي الإنشاء والتعديل معًا، لتصبح المصدر الوحيد للأرقام المالية المعروضة.

**ما تَمّ:**
- جزء مشترك جديد `partials/order-financial-summary.blade.php` يحسب بنفسه: `subtotal` (قراءة)، `total_weight` من `form.weight_kg` (قراءة)، `delivery_cost` (قراءة فقط بمثابة المرآة — يُحسب عبر `ShippingCostCalculator` نفسه المستخدم عند الحفظ، ويقتصر على `delivery_type=home` مع تحديد ولاية؛ وإلا «مجاني»)، `discount` (قراءة)، `grand_total = max(0, subtotal − discount)`.
- الشبكة المتجاوبة: **1 عمود @375 → 2 عمود @768 (`md:grid-cols-2`) → 5 أعمدة @1440 (`min-[1440px]:grid-cols-5`)** مع خلية الإجمالي مميزة (`bg-brand-surface`).
- محرر الخصم التفاعلي (select + القيمة + السبب) بقي كاملًا أسفل الشبكة داخل الجزء — والتقييم المعروض مقابل الخلية قراءة فقط.
- `order-form-modal.blade.php`: الكتلة المالية inline القديمة (كانت تقيس وتكرر الحساب) استُبدلت بـ `@include(livewire.merchant.orders.partials.order-financial-summary)` — نفس الجزء يخدم `$showCreateModal` و`$showEditModal` معًا.
- **إصلاح مصاحب (الفجوة الظاهرة):** `OrderService::createManual` لم يكن يخزّن حقول الخصم إطلاقًا (خلافًا لمسار التعديل) — فكان الإجمالي المعروض في الشبكة لا يُطابق المثبَّت للطلب المحدود. أُضيفت `discount_type` / `discount_value` / `discount_reason` إلى الإنشاء بحيث يتطابق المصدر الوحيد مع ما يُحفظ. (لا تعريف جديد: `total_amount` يبقى ما قبل الخصم — التخفيض يُخزن بشكل منفصل بحسب تصميم `getGrandTotalAttribute`، وتأكّد الاختبار أن `grand_total` = 400 مع `total_amount` = 500 وخصم 100.)

**اختبارات القبول (`OrderFinancialSummaryTest.php` — 7 جديدة):**
- الشبكة المشتركة تُعرض في مودال الإنشاء مع عناصر `data-financial-*` والفواصل المتجاوبة (`md:grid-cols-2` + `min-[1440px]:grid-cols-5`).
- الشبكة تتبع تغيّر الكمية مباشرة (مصدر واحد): 350 → ×4 = 1,400.
- خصم نوع `amount` يظهر في الشبكة ويُحفظ على الطلب المحدوث (`discount_type=amount`, `discount_value=100`, `total_amount=500`, `grand_total=400`).
- خصم `percent` 10% على 1,000 يُحوَّل إلى مبلغ (خصم 100 / إجمالي 900).
- تكلفة التوصيل للقراءة فقط: مع `DeliveryRate` مُعلن (Ecotrack + الولاية) تُعرض 400.00 DZD في خلية `data-financial-delivery`.
- الخلية تبقى «مجاني» عند غياب أي تسعيرة (ولاية + بلدية بدون rate).
- مودال التعديل يعيد استخدام نفس الشبكة (مصدر واحد) ويُظهر الإجمالي من الأصناف (1,500).
- **مخرَج فعلي:** `OrderFinancialSummaryTest` **7 ناجح (30 assertions)**؛ الملفات المتأثرة معًا (ملخص مالي + مكاتب + وزن + عدّاد استعلامات + إعدادات توصيل) **36 ناجح (166 assertions)**؛ السويت كاملة **372 ناجح (1353 assertions)** — ارتفاع من 365/1323 بلا انحدار. `php -l` سليمة + `view:clear`+`view:cache` (FRESH-CACHE-OK).

---

## Phase 30 — فرع 30.5 ✅ (شبكة العميل/العنوان 4 أعمدة — 1 @375 / 2 @768 / 4 @1440) — 2026-09-06

**ما تَمّ:**
- دمج قسم العميل + العنوان في `order-form-modal.blade.php` إلى شبكة واحدة: `grid grid-cols-1 md:grid-cols-2 min-[1440px]:grid-cols-4 gap-4` تحمل 4 خلايا: الاسم، الهاتف، الهاتف الثانوي، العنوان. (كانا قسمين منفصلين: الشبكة القديمة `sm:grid-cols-2` بـ 3 خلايا + حقل عنوان منفصل.)
- حُذف كتلة العنوان المنفصلة الأصلية (~سطر 128-132) لإزالة التكرار.
- السلاسلة المعتمدة (شركة ← نوع ← ولاية ← بلدية ← مكتب) لم تتغيّر.

---

## Phase 30 — فرع 30.6 ✅ (خريطة نوع الطلب NOEST: delivery=1 / exchange=2 / pickup=3 + توثيق remboursement) — 2026-09-06

**ما تَمّ:**
- أُضيفت طريقة `NoestIntegrationAdapter::typeId()` تحوّل `order->shipment_type` إلى رقم NOEST: `'delivery'` → 1، `'exchange'` → 2، `'pickup'` → 3، أي شيء آخر → 1 (aaliri افتراضي).
- `'type_id' => 1` الثابت في `createOrder` استُبدل بـ `'type_id' => $this->typeId($order)`.
- `'remboursement' => 0` بقي ثابتًا مع تعليق توضيحي: النموذج لا يدعم في الوقت الحالي سوى COD؛ remboursement=1 مخصص لتدفقات الاسترداد/code_pos. (وثّق المستخدم هذا sebagai fawqa nazariyah — فجوة مقصودة.)
- `NoestIntegrationTest`: الاختبار القائم `createOrder posts the NOEST payload` يحققه لأن الطلب في الاختبار بـ `shipment_type = 'delivery'` → `type_id = 1` ✓.

---

## Phase 30 — فرع 30.7 ✅ (تعديل اسم العميل inline — start/save/cancel + تحقق + audit) — 2026-09-06

**ما تَمّ:**
- `$startOrderNameEdit($orderId)` / `$saveOrderName` / `$cancelOrderNameEdit` + حالة `$nameEditName` — تطبيق تمامًا لنمط تعديل الهاتف السطري (`$phoneEditPhone`) مع الـ trait `HasInlineEdit`.
- `$saveOrderName`: تحقق `required|string|max:255` + `customer.update(['name' => ...])` + `writeInlineAudit(event: 'order_customer_name_updated')` + توست `merchant_panel.name_updated`.
- خلية العميل في جدول سط المكتب: زر `.edz-inline-edit__display` بأيقونة القلم يفتح `startOrderNameEdit` → حقل إدخال + زر حفظ/إلغاء.
- الموبايل (البطاقة): الاسم يتحول أيضًا إلى الزر القابل للتعديل عند الصلاحية.
- ترجمات جديدة: `name_updated` × 4 لغات {en: 'Customer name updated', ar: 'تم تحديث اسم الزبون', fr: 'Nom du client mis à jour', es: 'Nombre del cliente actualizado'}.

---

## Phase 30 — فرع 30.8 ✅ (إعادة ترتيب الأعمدة الثانوية + حفظ الترتيب + عرض ديناميكي) — 2026-09-06

**ما تَمّ:**
- **النماذج:** `visibleColumns` = `primaries (ثابتة بالترتيب الأصلي)` + `secondaries (بالترتيب المخزّن)` — يتوافق مع النموذج الموثّق في `loadColumnPreferences` ("Primary columns are always forced; only secondary columns are configurable").
- **عرض الجدول:** headers و body cells للأعمدة الثانوية تُعرض الآن عبر `@foreach ($visibleSecondaryKeys as $secondaryKey) @switch($secondaryKey) @endswitch` بدلاً من ثوابت في Blade — فقط بعد آخر primary ثابت، قبل عمود الإجراءات.
- **إصلاح فجوة سابقة:** `shipping_cost` كان بدون header `<th>` → أُضيف الآن `th` بسيط في الـ switch (إصلاح مخفي للانحراف بين الأعمدة当时 enabled).
- **تبويب إعدادات الجدول:** الأعمدة الثانوية تُرتَّب حسب الترتيب الحالي في `draftColumns` (Checked أولاً بالترتيب، ثم Unchecked). كل عمود ثانوي مفعّل يحمل أزرار `arrow-up`/`arrow-down` + رقم ترتيب (`tabular-nums`).
- `$moveDraftColumn($column, 'up'|'down')`: يُعيد ترتيب `secondaries` داخل `draftColumns` مع الحفاظ على `primaryKeys` في موضعها الثابت.
- ترجمات جديدة: `column_order_hint` × 4 لغات.
- **التحقق النهائي:** `php -l` سليمة + `view:clear`+`view:cache` (FRESH-CACHE-OK)؛ السويت كاملة **372 ناجح (1353 assertions)** — صفر انحدار من الإصدار 372/1353 السابق.

---

## Phase 30 — ملخص الإنجاز الكامل (30.1–30.8)

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 30.1 شركة الشحن | ✅ | NoestIntegrationAdapter | — |
| 30.2 سلسلة التوصيل | ✅ | index.blade + livewire state | 15 في OrderOfficeSelectionTest |
| 30.3 وزن تلقائي | ✅ | order-form-modal + index | 6 في OrderWeightAutoCalcTest |
| 30.4 شبكة مالية | ✅ | order-financial-summary partial + OrderService | 7 (30) في OrderFinancialSummaryTest |
| 30.5 شبكة العميل/العنوان | ✅ | order-form-modal.blade | — |
| 30.6 خريطة نوع NOEST | ✅ | NoestIntegrationAdapter + docs | — (الاختبار القائم يحققه) |
| 30.7 تعديل اسم العميل | ✅ | index.blade + 4 ملفات ترجمة | — |
| 30.8 إعادة ترتيب الأعمدة | ✅ | index.blade + moveDraftColumn + 4 ملفات ترجمة | — |
| **الإجمالي** | **372 ناجح (1353 assertions)** | | |

---

## Phase 30 — فرع 30.9 ✅ (إصلاح Toast + عمود المصدر + إعادة بناء الجدول بترتيب كل الأعمدة + سحب وإفلات) — 2026-09-06

**ما تَمّ (بموافقة صريحة من المستخدم):**
- **إصلاح Toast (جذر المشكلة):** `resources/js/swal.js` يستمع الآن لكل من `swal` و`swal:toast` مع تطبيع `icon`→`type`، وأُضيف مستمع `failed-validation` (Livewire 3) لتوست خطأ عند فشل التحقق من النموذج؛ البنود `@error` الـ inline تبقى الضمان الأساسي (موجودة في `order-form-modal`).
- **عمود المصدر:** أُضيف `source` للسجل (افتراضي، ثانياً): Manual = `created_by_membership_id` مملوء، Store = فارغ. باقية في الرأس/الخلية (`orders-table-header` + `orders-table-cell` partials) والكارت الموبايل (شارات `merchant.delivery_man` / `merchant_panel.store`).
- **إزالة أعمدة:** حُذف `tracking_status` و`confirmed_by` من سجل `orderColumns()` (طلبات المستخدم القديمة: لا tracking/confirmed/repeat في الجدول).
- **الترتيب الافتراضي الجديد (14):** number, source, customer, phone, products, amount, weight, shipment_type, wilaya, status, assigned_agent, created_at, confirmation_attempts, last_contact.
- **نموذج قائمة كاملة:** `visibleColumns` = قائمة مرتبة كاملة تُحفظ/تُستعاد كاملة في `UserColumnPreference` — لا تقسيم primary/secondary. أُعيدت كتابة `loadColumnPreferences`, `saveColumnPreferences`, `saveTableSettings`, `moveDraftColumn`.
- **إعادة بناء الجدول:** `<thead>` و`<tbody>` يُعرضان بحلقة واحدة `@foreach ($this->visibleColumns as $colKey)` مع partials (`orders-table-header`, `orders-table-cell`) — أُزيلت كل كتل `@if (in_array(...))` و`visibleSecondaryKeys`.
- **إعدادات الجدول:** قائمة موحدة لكل الأعمدة (checkbox + رقم ترتيب + أزرار ↑↓ + مقبض سحب) — كل الأعمدة قابلة للترتيب.
- **سحب وإفلات:** `orderColumnReorderDraft()` في `resources/js/components/order-column-reorder.js` (HTML5 DnD، إشارة موضع قبل/بعد، إرسال الطلب الجديد دفعة واحدة إلى `reorderDraftColumns`). أُزيل الرابط الوهمي `orderColumnReorder()` من `<table>`.
- **PHP جديد:** `$reorderDraftColumns(array $keys)` — يخصّص المفاتيح الصالحة ويزيل التكرار، مع حفظ كامل للقائمة المرئية.
- **ترجمات:** `column_order_hint` محدثة (سحب أو أسهم لكل الأعمدة) + `drag_to_reorder` × 4 لغات؛ أيقونة `bars-2` جديدة في `icon.blade.php`; SCSS `_column_reorder.scss`.
- **التحقق النهائي:** `php -l` + `view:clear`+`view:cache` + `npm run build` (المبنى: panel-BVy63S8G.js)؛ اختبارات مستهدفة (Preferences/DragDrop + TrackingColumn + OfficeSelection + MobileParity + QueryCount + Toast + FinancialSummary) كلها خضراء؛ السويت كاملة **374 ناجح (1363 assertions)** — صفر فشل.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 30.9 إعادة بناء الترتيب + Toast | ✅ | index.blade + partials×3 + order-column-reorder.js + swal.js + icon + 4 ترجمات + _column_reorder.scss | 6 في OrderIndexPreferencesTest (جديد 2) + OrdersTrackingColumnTest معدَّل |
| **الإجمالي** | **374 ناجح (1363 assertions)** | | |

## Phase 30 — فرع 30.10 ✅ (إصلاح المشاكل الخمس الجديدة: سحب وإفلات / causer_id / أعمدة افتراضية / إخفاء / تعديل شامل) — 2026-09-06

**ما تَمّ (بموافقة صريحة من المستخدم):**
- **إصلاح السحب والإفلات (جذر المشكلة):** في `index.blade.php:3936` كان `:data-col-key="{{ $settingsKey }}"` — علامة `:` تجعل Alpine يقيّم `number`/`customer`… كمتغيّر JS غير معرّف داخل نطاق Alpine ⇒ `dataset.colKey === undefined` لكل صف ⇒ `commit()` يخرج مبكرًا ولا يعيد الترتيب. الحل: جعلها سمة ثابتة `data-col-key="{{ $settingsKey }}"`.
- **خطأ `causer_id` Data truncated:** migration جديد `2026_09_06_183355_fix_activity_log_ulid_columns` — `nullableMorphs()` أنشأ `unsignedBigInteger` لكن `User`/`Order` يستخدمان `HasUlids` (سلاسل 26). تم توسيع `causer_id`/`subject_id` إلى `string(26) nullable` مع الحفاظ على الفهارس `causer`/`subject`.
- **الأعمدة الافتراضية الخاطئة + عدم إمكانية الإخفاء (Issues 1/2):** إصدار preference أحادي الاستخدام. migration `2026_09_06_183618_add_prefs_version_to_user_column_preferences_table` أضاف `prefs_version`؛ `loadColumnPreferences` يعيد تعيين القوائم القديمة (قبل إعادة البناء، `prefs_version` فارغ) إلى الافتراضيات الجديدة مرة واحدة ثم يحفظ النسخة. كل الأعمدة قابلة للتبديل/الإخفاء.
- **تعديل شامل عند التحرير من الجدول (Issue 4):** أُضيف `wire:key="order-row-…"` على كل `<tr>` و`wire:key="order-card-…"` على الكارت الموبايل ⇒ Livewire يعيد تركيب الصف المُعدَّل فقط. أُضيف `$decorateOrder()` (المنطق المستخرج من الـ map) و`$refreshSingleOrder()` (يعيد استعلام طلبية واحدة ويبدّلها في `$this->orders`) واستُبدلت استدعاءات `loadOrders()` الأربعة في عمليات الحفظ inline (phone/name/wilaya/city).
- **التحقق النهائي:** `view:clear` + سويت كاملة **374 ناجح (1363 assertions)** — صفر فشل.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 30.10 إصلاح المشاكل الخمس | ✅ | index.blade + migration×2 + UserColumnPreference.php + HasInlineEdit (قراءة) | 374 ناجح (1363 assertions) · 11 في Preferences/TrackingColumn |
| **الإجمالي** | **374 ناجح (1363 assertions)** | | |

---

## Phase 31 — فرع 31.1 ✅ (سجل الأعمدة الجديد + prefs v2 + قفل المطلوب + عمود total/verification + قيود الأدوار) — 2026-09-06

**ما تَمّ (بموافقة المستخدم على خطة مرحلة 31 وقراراته الأربعة: إظهار إجباري مع ترتيب حر / إعادة حساب الإجمالي عند التعديل فقط / ثلاثة أعمدة منفصلة / تخفيض مبلغ+نسبة):**
- **إعادة تعريف `$orderColumns()` بالكامل** (`orders/index.blade.php`): كل عمود يحمل metadata (default/required — ظاهر دائمًا لا يُخفى مع بقاء الترتيب حرًا /editable/roles/info). استُبدل عمود `amount` بـ `total`؛ أُضيفت `verification` (تصميمي)، `quantity`، `price`، `discount`. القائمة القياسية: customer→phone→verification→status→shipping_provider→delivery_type→wilaya→city→stopdesk_point→address→products→quantity→price→total + `confirmation_attempts/last_contact` للأدوار فقط (عبر `hasStoreRole`).
- **`prefs_version` = 2:** `loadColumnPreferences`/`saveColumnPreferences` يعيدان تعيين legacy لمرة واحدة وحفظ الترتيب v2، يفرضان المطلوب دائمًا (إدراج canonical لا append)، ويستبعدان أعمدة الأدوار لمن بلا Owner/Admin/Manager.
- **مذاكرة:** closures `$orderColumn()` (cache) و`$columnAllowedForUser()` للحرّاس؛ `$orderEagerLoads()` موحّد بين `loadOrders` و`refreshSingleOrder`. حرّاس `toggleDraftColumn`/`reorderDraftColumns`/`saveTableSettings` للمطلوب والأدوار.
- **المودال:** المطلوب checkbox معطّل + قفل + شارة «دائمًا ظاهرة» + تنبيه `primary_columns_hint`؛ أعمدة الأدوار تختفي لقائمة STAFF.
- **العمود `total`:** يُعرض دائمًا `subtotal + shipping_cost − discount_amount` عبر `decorateOrder` (`items_subtotal`/`discount_amount`/`display_total`)، ويعاد حسابه في `submitEditInline` لحدوث التغييرات؛ مرشّح `amount` (min/max على total) باقٍ تحت نفس مفتاح البنية الداخلي مع ربط الرأس `'total' => 'amount'`.
- **العرض:** `orders-table-header`/`orders-table-cell` (خلايا total/quantity/price/discount/verification)، البطاقة المتحرّكة، تفاصيل الطلبية، popover الفلاتر — كلها على `total`. عمود `verification` placeholder تصميمي (شارة + «—» + hint `verification_hint`).
- **ترجمات** ×4 لغات: `verification`, `verification_hint`, `quantity`, `price`.
- **التحقق النهائي:** `php -l` + `view:clear`+`view:cache` + **السويت كاملة 377 ناجح (1369 assertions)** — صفر انحدار (+3 في `OrderIndexPreferencesTest`: reset v2 / منع إخفاء المطلوب / أعمدة الأدوار لـ STAFF).

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 31.1 سجل الأعمدة + prefs v2 + قفل المطلوب + total | ✅ | index.blade + orders-table-header + orders-table-cell + 4 ترجمات | 377 ناجح (1369 assertions) · 14 في Preferences/TrackingColumn |
| **الإجمالي** | **377 ناجح (1369 assertions)** | | |

---

## Phase 31 — فرع 31.2 ✅ (حاجز التأكيد: منع تأكيد/إرسال طلبية غير مكتملة) — 2026-09-06

**ما تَمّ (المصدر الوحيد للفحص + حرّاس على كل المسارات):**
- **خدمة `app/Domains/Order/Services/OrderCompleteness.php`**: `missing(Order, bool forSend=false)` تُرجع بنية `key+label` بترتيب ثابت، و`missingLabels()`/`isComplete()`. مستويان بحكم قاعدة المنتج: **التأكيد** = اسم/هاتف الزبون + الولاية/البلدية + أصناف (بدون وجهة/ناقل — نافذة التأكيد تُكملها)، **الإرسال** = نفسها + الوجهة (عنوان منزلي أو مكتب حسب `delivery_type`) + شركة التوصيل.
- **استثناء `app/Domains/Order/Exceptions/OrderIncompleteException.php`** (`fromMissing` + `labels()`).
- **`OrderService::confirm`** يتحقّق قبل التحويل ويرمي `OrderIncompleteException` للطولي الناقص (مستوى التأكيد). **`OrderShippingGateway::send`** يتحقّق بعد `refresh()` وقبل أي انتقال/إرسال (مستوى الإرسال) — أبعد نقطة أمان (تغطي تأكيد+إرسال، الإرسال المباشر، والجماعي الذي يتخطّى غير الجاهز أصلًا).
- **واجهة Volt (`orders/index.blade.php`)**: `collectMissingFields(Order, forSend)` أصبح وكيلًا للخدمة (مصدر واحد)، وبقية مكالماته (إرسال مباشر 1534/تحليل الجماعي 922) بدون معامل = مستوى الإرسال. **`submitConfirmOnly`**: حارس قبل `confirm()` — توست `order_flow.confirm_missing_fields` بالحقول الناقصة والمودال يبقى مفتوحًا (لا إغلاق). **`submitConfirmAndSend`**: حارس قبل فحص الشريك — توست بالحقول الناقصة أولًا.
- **ترجمات** ×4 لغات: `order_flow.confirm_missing_fields`.
- **إصلاح التناقض مع قاعدة متجر المنتجات**: متجر "مكتب اختياري" (CartOrderLimitsTest) يُنشئ طلبية stopdesk بلا مكتب ويؤكدها — الوجهة ليست شرطًا للتأكيد فثبتّ مستوى الإرسال فيه فقط. تحديث إعداد `OrderServiceTest` (سابقًا زبون/جغرافيا/عنوان/منتجات ناقصة — أصبح مكتملًا ليمرّ من الحارس).
- **التحقق النهائي:** `php -l` + `view:clear`+`view:cache` + **السويت كاملة 386 ناجح (1388 assertions)** — صفر انحدار.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 31.2 حاجز التأكيد/الإرسال | ✅ | OrderCompleteness.php + OrderIncompleteException.php + OrderService.php + OrderShippingGateway.php + index.blade + 4 ترجمات | 386 ناجح (1388 assertions) · 9 في OrderCompletenessTest |
| **الإجمالي** | **386 ناجح (1388 assertions)** | | |

## Phase 31 — فرع 31.3 ✅ (قوائم منسدلة بحثية inline للأعمدة المطلوبة) — 2026-09-06

**ما تَمّ — خمسة حقول قابلة للتعديل من الجدول مباشرة بنمط `<x-edz.select>`:**
- **سجل الأعمدة**: `assigned_agent` أصبحت `editable => true` (الموصل قابل للإسناد من الصف). باقي الأعمدة (شركة/نوعية/شحن/مكتب) كانت editable أصلًا؛ wilaya/city تُبقي النموذج الأصلي (native select) لتقليل المخاطرة.
- **خلايا الأعمدة** (`orders-table-cell.blade.php`): محرّرات inline تعرض فقط عندما `editingField === 'order.<key>' && editingId === $orderId`؛ بحث (`search`) لشركة/مكتب/موصل، ومباشر لنوعية/شحن؛ زر حفظ (spinner أثناء الحفظ) + إلغاء + عرض خطأ. شاشات العرض أصبحت أزرار `edz-inline-edit__display` (لكل من يملك ORDER_MANAGE).
- **Closures Volt** بالنمط المثبت (start/save + `guardOrderEditable` + `saveEdit` = تحقق/audit):
  - `startOrderProviderEdit`/`saveOrderProvider`: يخزّن `shipping_provider_id`، **ويسقط مكتبًا لم تعد الشركة الجديدة تخدمه** (تحقق من نطاق الشركة أو النطاق المشترك null)، ويعيد حساب الشحن.
  - `startOrderDeliveryTypeEdit`/`saveOrderDeliveryType`: يحدّث `delivery_type`، **ويمسح المكتب عند التحويل إلى منزلي**.
  - `startOrderShipmentTypeEdit`/`saveOrderShipmentType`: delivery/exchange/pickup.
  - `startOrderStopdeskEdit`/`saveOrderStopdesk`: قائمة مكاتب **مقيدة بشركة الطلبية** (`inlineStopdeskOptions`)، وتحقق يرفض مكتب شركة أخرى (`order_stopdesk_point_updated_validation_failed`).
  - `startOrderAgentEdit`/`saveOrderAgent`: إسناد/إلغاء إسناد عبر `OrderAssignmentService::reassign` (بدون حارس shipped — الإسناد صالح في أي مرحلة؛ خيار "بدون إسناد" `merchant_panel.unassigned` الجديد، ومعالجة null يدويًا لأن `reassign()` يرفض target خالٍ).
- **أحداث audit ×4**: `order_shipping_provider_updated` / `order_delivery_type_updated` / `order_shipment_type_updated` / `order_stopdesk_point_updated` / `order_assigned_agent_updated` (مع `_validation_failed` عند الرفض).
- **Props جديدة**: `editProviderOptions`/`editDeliveryTypeOptions`/`editShipmentTypeOptions` (ثابتة من mount) + `editStopdeskOptions`/`editAgentOptions` (تُحسب عند بدء التحرير). إضافة `shippingProvider` إلى `orderEagerLoads` حتى تعرض الخلية الشركة الحالية لا شركة آخر Tracking.
- **ترجمة**: `merchant_panel.unassigned` ×4 لغات.
- **التحقق النهائي:** `view:clear`+`view:cache` + سويت كاملة **397 ناجح (1440 assertions)** — صفر انحدار.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 31.3 selects بحثية inline | ✅ | index.blade.php (closures + props + registry) + orders-table-cell.blade.php + orderEagerLoads + merchant_panel ×4 | 11 في OrderInlineSelectEditTest (52 assertion) |
| **الإجمالي** | **397 ناجح (1440 assertions)** | | |

## Phase 31 — فرع 31.4 ✅ (تعديلات inline: العنوان/الوزن/التخفيض/الشحن من مستودع الشركة + شارة الحقول الناقصة داخل الصف) — 2026-09-06

**ما تَمّ — أربعة حقول إضافية قابلة للتعديل من الجدول مباشرة + كشف النقص في الصف ذاته:**
- **`weight_kg` الآن عددي دائمًا (NOT NULL default 1.00)**: التعديل يخزّن دائمًا قيمة رقمية (`blank → 1.00`)؛ وبنفس المنطق حُرّر `OrderService::createManual` (blank → 1.00) ومسارا إنشاء/تعديل المودال (`form['weight_kg'] ?: 1.00`) ليتوافقا مع عمود NOT NULL (أصلح 3 إخفاقات pre-existing في OfficeSelection/QuantityCap/FinancialSummary عند إنشاء طلبيات بوزن فارغ).
- **Closures ×7** بالنمط المثبت (`canStore ORDER_MANAGE` عند البداية + `guardOrderEditable` + `saveEdit` = تحقق/audit + `refreshSingleOrder`):
  - `startOrderAddressEdit`/`saveOrderAddress`: نص حر (blank → null) + `recalculateOrderShipping` بعد التحديث + audit `order_address_updated`.
  - `startOrderWeightEdit`/`saveOrderWeight`: `nullable|numeric|min:0|max:9999` + audit `order_weight_updated` (و `_validation_failed`).
  - `startOrderDiscountEdit`/`saveOrderDiscount`: ثلاثة props (`discountEditType`/`discountEditValue`/`discountEditReason`)، نوع `amount`/`percent` عبر `<x-edz.select>` مع `wire:model.live` (إظهار/إخفاء القيمة والسبب)، اعتبارية `percent > 100` → `merchant_panel.discount_percent_max`؛ اختيار «بدون تخفيض» أو قيمة فارغة يمسح `discount_type/value/reason` + audit `order_discount_updated`.
  - `toggleSendFromWarehouse`: عكس `send_from_carrier_warehouse` (علم تخطيطي — يعمل حتى لـ shipped) + audit `order_send_from_warehouse_updated`.
  - `startMissingFieldEdit`: يقفز إلى أول حقل ناقص (عبر `OrderCompleteness::missing` بمستوى confirm/send) — كل key يُعاد توجيهه إلى محرّره inline (name/phone/wilaya/city/stopdesk/address/provider)؛ `items` أو غير المعروف → فتح مودال التعديل.
- **`decorateOrder`** يحسب `missing`/`missing_keys` لكل صف (statuses backOffice فقط، `forSend` = confirmed/preparing)؛ **خلية الزبون** تعرض شارة count تنقلك لأول حقل ناقص (tone `info` — تجنّبًا لاصطدام `OrderDuplicateBadgeTest` الذي يرفض danger/warning/neutral).
- **خلايا الأعمدة**: address/weight/discount/`send_from_carrier_warehouse` بمحرّريها، حاوية `edz-inline-edit__edit--wide` للمودال الواسع (التخفيض)، وأزرار الحفظ بنمط السبينر المستهدف.
- **ترجمات** ×4 لغات: `merchant_panel.no_discount` + `merchant_panel.discount_percent_max`.
- **التحقق النهائي:** `view:clear`+`view:cache` + **السويت كاملة 410 ناجح (1497 assertions)** — صفر انحدار؛ ملاحظة تشغيل واحدة : race Windows معروف (rename Access is denied أثناء compile blade) — مجرد إعادة تشغيل.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 31.4 inline field edits + شارة الناقص | ✅ | index.blade.php (closures + props + decorateOrder + ممرّرا الوزن) + orders-table-cell.blade.php + _inline_edit.scss + OrderService.php + merchant_panel ×4 | 13 في OrderInlineFieldEditTest (57 assertion) |
| **الإجمالي** | **410 ناجح (1497 assertions)** | | |

## Phase 31 — فرع 31.5 ✅ (إزالة زر «توصيل» + بارتيال الأزرار المشترك + توحيد السبينر المركزي + إصلاح «المحدد» في القوائم + تحرير notes inline) — 2026-09-07

**خمسة طلبات من المستخدم بعد موافقة صريحة على الخطة الثلاثية (زر التوصيل / توحيد السبينر على المركزي JS / بارتيال الأزرار):**
- **حذف زر «توصيل» نهائيًا** من عمود الأزرار (ديسكتوب + موبايل)؛ مودال التوصيل تبقى قابلة للوصول عبر التحرير المباشر للحقول، وpopup «معلومات التوصيل» لاحقًا حسب الحاجة.
- **بارتيال أزرار مشترك** `orders-table-actions-column.blade.php` بمعامل `layout` ('compact' للديسكتوب | 'list' للموبايل) — ديسكتوب: details + events (داخل الـpartial) + confirm/send/edit/reassign/delete/restore؛ موبايل: القائمة تعرض فقط أزرار الصلاحيات (confirm/send/edit/reassign/delete) بينما يبقى details + events على يسار الـpopover (كما كان) — إغلاق القائمة بـ`@click="close()"` منفصل (نمط مثبت) و`$editCloser`/`$deleteCloser` لأزرار Alpine.
- **توحيد مؤشر التحميل على النظام المركزي `edz-button-loading.js`**: حُذفت كل مقايضات `wire:loading.remove`/`x-edz.spinner` اليدوية من index.blade.php (شريط الأدوات/التراش/أزرار الحفظ في بطاقة الموبايل/قوائم الحالة/مودالات الإرسال) و`orders-table-cell.blade.php` (أزرار الحفظ ×10) و`bulk-actions-bar.blade.php` و`delivery-edit-modal.blade.php` و`order-form-modal.blade.php`. `edz-button-loading.js` مُحدَّث: دعم `@click="$wire.method()"` عبر `ALPINE_CLICK_SELECTOR` + `WIRE_CALL_RE`، ودقة المطابقة `methodOf` (multi-match عاد للعمل لحالات مثل `transitionOrder`)، وأبقيت `wire:loading.attr="disabled"` و`show="isLoading"` (Alpine) للأزرار المحلية.
- **إصلاح جذري لعدم ظهور «المحدد»** في `x-edz.select`/`product-select`: (أ) خيارات backend تُحوَّل الآن إلى string (`String(r.value ?? r.id ?? r)`) لتفادي فشل `opt.value === selected`؛ (ب) سمة `data-options` جديدة على الجذر + `MutationObserver` في `edz-select.js` و`_syncFromServer()` في `product-select.js` (مع `x-on:livewire:updated`) — تُحدِّث حالة Alpine بعد morph من Livewire بدل البناء مرة واحدة عند mount.
- **تحرير `notes` inline** (ديسكتوب + بطاقة الموبايل): `startOrderNotesEdit`/`saveOrderNotes` بنمط `saveEdit` مع `nullable|string|max:500` + `guardOrderEditable` + apply `blank → null` + audit `order_notes_updated` (و`_validation_failed`)، خلية textarea في `orders-table-cell`، صف في بطاقة الموبايل محروس بـ`in_array('notes', $this->visibleColumns)`، وتسجيل العمود `editable => true`.
- **التحقق النهائي:** `php -l` نظيف + `npm run build` (Sass deprecations فقط، لا أخطاء) + `view:clear`+`view:cache` + **السويت كاملة 413 ناجح (1511 assertions)** — صفر انحدار (أصلح اختبار `OrdersMobileMoreMenuTest` لدقة فرضية min-h-44px للموظف).
- **إصلاح عاجل بعد الاختبار ✓:** خطأ الكونسول `Failed to execute 'closest' ... 'button[@click]' is not a valid selector` من `edz-button-loading.js` — `@click` ليس اسم خاصية CSS صالحًا فكان يُرمي في `closest/querySelectorAll` عند كل نقرة. الحل الجذري: استُبدل بـ`[x-on\:click]` السليم (CSS) + حلّ `@click`/`x-on:click` عبر `hasAttribute()/getAttribute()` (دالة `alpineHandlerOf`/`hasAlpineClick` مع مسح يدوي في `allAlpineButtons()` و`alpineButtonOf()` لاعتراض النقر)، مع كاش `requestButtons` يُصفَّر عند بداية كل طلب/تنقّل حتى لا يُعاد فحص DOM مرارًا لكل method (جانب الأداء) — الـbundle الجديد `panel-Dm-JaIMG.js`.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 31.5 أزرار/سبينر/محدد/notes | ✅ | orders-table-actions-column.blade.php + index.blade.php + orders-table-cell.blade.php + bulk-actions-bar + delivery-edit-modal + order-form-modal + edz-button-loading.js + edz-select.js/product-select.js + select/product-select.blade.php + OrderInlineFieldEditTest | 16 في OrderInlineFieldEditTest (71 assertion) — منها 3 جديدة للتلاحظات |
| **الإجمالي** | **413 ناجح (1511 assertions)** | | |

---

## Phase 31 — فرع 31.6 ✅ (إصلاح رمز «×» المعطوب + التحديد التلقائي لشركة التوصيل الافتراضية + عرض «المحدد» المحفوظ في كل `x-edz.select`) — 2026-09-07

**ثلاثة عيوب أُبلغ عنها بعد جولة 31.5، بموافقة صريحة على الخطة (وأُلحق بها إصلاح شارة «أ—»→«×» و`searchable`→`search` كإصلاحات مكمّلة):**

- **رمز «×» المعطوب في رسائل الحقول الناقصة والفاصل العربي**: كانت `implode` داخل `orders/index.blade.php` تستخدم فاصلًا عربيًا معطوبًا (`'طŒ '`/`'ط› '` بدل `'، '`) في رسائل `send_missing_fields`/`confirm_missing_fields` (الأسطر 954/1003/1582/5296 → `'، '`)؛ ووُحِّدت الفواصل في 1476/1511 عبر replaceAll → `'، '` (مشترك لكل اللغات لأن `implode` في الـblade نفسه). أُصلحت أيضًا شارات «ضرب» المعطوبة `أ—` (القِيم الكمية/السعرية/التكرار) → «×» في 4 مواضع بـ`index.blade.php` (4245/4832/5151/5390) وموضعين بـ`partials/order-form-modal.blade.php` (249/...) — كلها كانت تمثّل رمز القسمة `×` بين القيمة والتفصيلة.
- **عدم التحديد التلقائي لشركة التوصيل الافتراضية**: `ShippingProvider.is_default` موجود ومرتبط بخانات «تعيين افتراضية» في صفحة الشركات، لكنه لم يكن يُقرأ في تدفق الطلبيات. أضيف مصدر واحد داخل `orders/index.blade.php` — closure `$storeDefaultProviderId()` (قراءة `where('store_id', currentStoreId())->where('is_active', true)->where('is_default', true)`) — يُستدعى عند: (1) فتح مودال الإنشاء `openCreateModal` لتهيئة `form['shipping_provider_id']`، و(2) درج التأكيد `openConfirmModal` كـ fallback عندما لا تملك الطلبية شركة (`confirmProviderId`). الأداء: قراءة قاعدة واحدة فقط عند فتح المودال، لا استعلامات زائدة؛ لا تغيير في `/cascade` المكاتب (يبقى `loadFormOffices` يعمل عند اختيار شركة/التحويل للاستلام).
- **عدم عرض «المحدد» المحفوظ بعد الحفظ في كل استخدامات `x-edz.select`** (السبب الجذري السطحي يُعزى لـ`select.blade.php` الذي مرّر `initialValue: @js($attributes->get('wire:model', ''))` — لا يلتقط `wire:model.live` — وJS لم يكن يقرؤه إطلاقًا):
  - `resources/views/components/edz/select.blade.php`: استخراج صحيح لمسار النموذج `$modelName = $attributes->whereStartsWith('wire:model.')->first() ?: $attributes->whereStartsWith('wire:model')->first()` (يلتقط `wire:model.live` أولًا حتى لا يظلله الثنائي `wire:model` الأساسي)، مرورًا كـ `modelName` (رفض `initialValue`).
  - `resources/js/components/edz-select.js`: خزن `modelName` + قراءة `this.$wire.get(modelName)` عند `init()` عبر `syncFromServer()` الجديدة (تُطبيع القيمة بـ`String(v)` فتُصلح ~10 مواضع عدم مطابقة رقمي/نصي من جرد 46 استخدامًا)، دون تغيير آلية الإرسال (الحقل المخفي `x-model` + أحداث change/livewire-change باقية كما هي لضمان استمرار `wire:change` على الجذر: شركة←مكاتب، ولاية←مدينة). النمط مطابق لـ`product-select.js`.
  - **تصحيح استخدام خاطئ**: `orders/index.blade.php:5175` مرّر `searchable` (مُعامل غير مدعوم في هذا المكوّن) بدل `search` لمودال التأكيد → حُوّل إلى `search`.
- **التحقق النهائي:** `php -l` نظيف لـ3 ملفات (index/select/order-form-modal) + `node --check` لـ`edz-select.js` + `npm run build` (بلا أخطاء) + `view:clear`+`view:cache` (تحقق: لا أثر لـ`أ—`، `livewire:updated` و`syncFromServer` و`modelName` ظاهرة في العروض المترجمة، و`searchable` المتبقّي فقط في مكوّنات umi/form الأخرى المشروعة) + **اختبارا `OrderInlineFieldEditTest` + `OrdersMobileMoreMenuTest`: 18 ناجح (79 assertions)** — صفر انحدار في البنود المُصلَحة.
- **إصلاح إضافي بعد التحقق ✓:** ترتيب البوب أبس — عند الضغط على أحد أسطر الطلبيات المكررة في نافذة الفحص (`wire:click="openOrderDetails(...)"` في `index.blade.php`)، كانت تفاصيل الطلبية تُفتح **خلف** نافذة الفحص لأن `showDuplicateScanModal` يبقى `true`. الحل الجذري في مصدر واحد: `openOrderDetails` يصفّر `showDuplicateScanModal` أولًا (نمط موحّد يغطي كل الدخول من بوب أب الفحص، بنفس أسلوب `order-form-modal.blade.php:277` الذي يغلق مودالات الإنشاء/التعديل قبل التفاصيل). أُضيف اختبار `OrderDuplicateBadgeTest` («opening order details from a duplicate-scan row closes the scan popup») — **10 ناجح (49 assertions)**.
- **السبب الجذري الحاسم (جولة ثانية) ✓:** البحث الشامل في `vendor/livewire` + موارد `resources` أثبت أن حدث **`livewire:updated` غير موجود في Livewire v3 إطلاقًا** — فمستمعا `x-on:livewire:updated="syncFromServer"` (و`app.js:44`/`panel.js:224` اللذان تركناهما خارج النطاق) لم يُشعل أحدهما أبدًا، وهذا هو الداء الفعلي لعدم انعكاس القيمة المحفوظة بعد الحفظ. البديل البرميجي الصحيح في v3: **`this.$wire.$watch(path, cb)`** الذي يراقب `dataGet(component.reactive, path)` ويتحول بعد كل رحلة سيرفر، مع قراءة أولية متزامنة عبر `$wire.get` (مؤكد من الـdist: يقرأ `component.reactive` مباشرة). طُبّق العلاج بمكوّنين:
  - `resources/js/components/edz-select.js`: `init()` → `_bindServerValue()` (قراءة `$wire.get(modelName)` موحّدة بـ`String(v)` + تسجيل `$wire.$watch`)، وحُذفت `syncFromServer` القديمة؛ عروض `_syncFromServer` الإرسالية/الخيارات كما هي.
  - `resources/js/components/product-select.js` + `resources/views/components/edz/product-select.blade.php` (سطر 55): نفس المعاملة — `_bindServerValue()` بدل `syncFromServer`، وإزالة مستمع الحدث الميت.
  - مراجعة شاملة: كل استخدامات الـ47 لـ`x-edz.select` و`x-edz.product-select` مربوطة بـ`wire:model` (تحققت الأسطر متعددة الأسطر يدويًا) — لا وجاهة خارج النطاق متأثرة.
- **ترقية fallback للشركة الافتراضية ✓:** closure `$storeDefaultProviderId()` صار يقرأ `where('store_id', currentStoreId())->where('is_active', true)->orderByDesc('is_default')->orderBy('name')` — يختار الشركة المُعلَمة `is_default`، وإن لم توجد يقع على أول شركة نشطة فتعمل المودالات دائمًا على شركة توصيل. الاستدعاءات كما هي (إنشاء + درج تأكيد). أُضيف ملف `OrdersDefaultProviderTest` **(5 اختبارات / 6 assertions)**: auto-select للمُعلَمة، fallback لأول نشطة، عزل المتجر، درج التأكيد بلا شركة وبشركة قائمة.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 31.6 عيوب ما بعد 31.5 | ✅ | orders/index.blade.php + partials/order-form-modal.blade.php + components/edz/select.blade.php + components/edz/product-select.blade.php + resources/js/components/edz-select.js + resources/js/components/product-select.js + a OrderDuplicateBadgeTest + a OrdersDefaultProviderTest + 4 ترجمات (الفاصل في الـblade المشترك) | 18 في OrderInlineFieldEditTest + OrdersMobileMoreMenuTest + 10 في OrderDuplicateBadgeTest + 5 في OrdersDefaultProviderTest (134 assertions) |
| **الإجمالي** | **446 ناجح (1645 assertions)** | | |

---

## Phase 31 — فرع 31.7 ✅ (بوابة التأكيد «الدرج فقط» + زر تأكيد بارز + «مؤجل» واحدة + لون confirmed أخضر) — 2026-09-07

**بموافقة صريحة بعد شرح خطة مُصحّحة (سؤال المستخدم كشف خطأَ النسخة الأولى: حذف `confirmed` من `availableTransitions` كان سيكسر `OrderService::confirm()` لأن درج التأكيد ينتهي بـ`transition('confirmed')` عبر `canTransition`). المبدأ المعتمد: آلة الحالات مصدر حقيقة — يُخفى الاختصار من الواجهة ويُفرض الدرج خادميًا.**

- **G1 — إخفاء عرضي فقط:** `decorateOrder` يضيف `can_confirm` (التحول ينتهي بـconfirmed: صحيح عند pending/on_hold) + `confirm_via_drawer` (pending/on_hold)، والـdropdown الديسكتوب/الموبايل (`orders-table-cell.blade.php` + `index.blade.php`) يستثني خيار `confirmed` ما دام `confirm_via_drawer && !isCurrentStatus` — «مؤكد» الحالية تبقى ظاهرة كحالة سارية، وجميع التحولات الأخرى (مؤجل/لا إجابة/…) كما هي.
- **G2 — درع السيرفر:** `transitionOrder(id,'confirmed')` ومصدره pending/on_hold → `openConfirmModal()` بدل التحويل (لا يتجاوز العملية حتى بطلب يدوي)؛ فحص الصلاحية يسبق الدرع (يحتاج ORDER_CONFIRM).
- **G3 — الدرج مع on_hold:** `submitConfirmOnly` صار يقبل `['pending','on_hold']` (بدل pending فقط) + حارس `OrderShippingGateway::send(confirmFirst:true)` صار يشمل `'on_hold'` (نقطة الاتصال الوحيدة: درج «تأكيد وإرسال») — نقطة الوصول الوحيدة إلى confirmed أصبحت الدرج (يُسجّل `confirmation_attempts`/`last_contact_at`/`contactAttempt`).
- **B — زر بارز:** `orders-table-actions-column.blade.php` — زر «تأكيد الطلبية» تحوّل من ghost إلى `edz-btn--primary` ممتلئ (أيقونة هاتف بيضاء) عبر `$confirmBtnClass`؛ وشرط الظهور صار `$order['can_confirm']` (لا `in_array('confirmed', $transitions)`). الموبايل: صف ممتلئ بعنوان + أيقونة.
- **C — لون confirmed أخضر:** `SystemStatusesSeeder` confirmed `info→success` + migration `2026_09_07_000001_confirm_status_green.php` يعيد تلوين الصفوف النظامية القائمة (`type=order`,`key=confirmed`,`is_system=true`) إلى `success` (والعكس عند rollback). صف الجدول/الشارة/النقطة تستجيب فورًا لأن `edz-table-row--success` جاهز (لا CSS).
- **P — حالة «مؤجل» واحدة:** `pending → […, postponed, …]` في `availableTransitions` (تصبح قابلة للوصول من المعلّق؛ الثابت `postponed→[pending,cancelled]` موجود أصلًا) + `OrderStatus::POSTPONED` في التعداد مع label/color/icon — اللون طبعًا `warning` (مميّز عن الأخضر الجديد).
- **اختبارات (D):** تعديل `OrderActionToastUnificationTest` ليعكس البوابة (المباشر يفتح الدرج؛ النقل الصحيح الناجح عبر `postponed`؛ تحوّل غير صالح `shipped` يبقي الحالة ويُرجع خطأ) + ملف جديد `OrderConfirmGateTest` **(6 اختبارات)**: آلة الحالات سليمة مع فتح الدرج للـconfirmed المباشر، اختفاء خيار confirmed وظهور postponed في القائمة (regex على `transitionOrder(...confirmed/postponed...)`)، تأكيد pending عبر الدرج، تأكيد on_hold عبر الدرج، نقل postponeded، ولون confirmed = success.
- **التحقق النهائي:** `php -l` نظيف لـ7 ملفات + `view:clear`+`view:cache` (FRESH-CACHE-OK) + الجولات المتأثرة **36 ناجح (80 assertions)** (OrderConfirmGate + OrderActionToastUnification + OrderCompleteness + OrdersDefaultProvider + OrdersMobileMoreMenu + StoreAuthorizationGates) ثم **الجولة الكاملة 426 ناجح (1536 assertions)** بلا أي فشل (ملاحظة: فشل عابر واحد في أول تشغيل كان قفل ملف `storage/framework/views` على ويندوز `rename Access is denied` — واجتاز الاختبار بالكامل 9/9 بعد `view:clear`؛ الإجمالي المعتمد من التشغيل النظيف الثاني).

- **إصلاح بعد التحقق (فتح المودال في المتصفح) ✓:** أبلغ المستخدم أن النقر على زر التأكيد لا يفتح المودال رغم صحة المسار الخادمي (الاختبارات تجتاز). التشخيص (استكشاف شامل): مودال التأكيد كان «Pattern B» — موجود دائمًا في DOM مع `:is-open="$showConfirmModal"` ولا يُعاد فيه تهيئة `x-data` من Alpine عند المَورف؛ كما أن حدث الإغلاق `@close="$wire.closeConfirmModal()"` **ميت** لأن المكوّن يصدر `edz-modal-closed` وليس `close` → `showConfirmModal` يعلق `true` بعد أول إغلاق. الحل: نقل مودال التأكيد + مودالي الـbulk (الحالة/الإرسال) إلى «Pattern A» المثبت (نمط التفاصيل/الفحص المزدوج): `@if ($showX)` + غلاف `<div @edz-modal-closed.window="$wire.closeX()">` + `:is-open="true"` + `wire:key`، وتم تحصين `openConfirmModal` ضد null (`$order->customer?->name` و`status_label` بشرط `status?->key`). **426 ناجح (1536 assertions)**.
- **إصلاح عمود شركة الشحن (توافق العروض) ✓:** شكوى المستخدم: الشركة الافتراضية لا تُحدَّد تلقائيًا عند فتح التعديل المباشر، والاسم يبقى `—` بعد الحفظ. السبب الجذري للعرض: `toArray()` في Laravel يُميِّه أسماء العلاقات (**snake_case**) — الحقل يقرأ `$order['shippingProvider']['name']` (camel) بينما المفتاح الحقيقي `$order['shipping_provider']` (تحقّق من tinker) → يُعرض `—` دائمًا. الحل: (1) تصحيح المفتاح إلى `shipping_provider` في `orders-table-cell`؛ (2) `startOrderProviderEdit` يصيغ القيمة الافتراضية للمتجر (مثل درج التأكيد) عندما لا تملك الطلبية شركة؛ (3) عمود مطلوب بلا شركة يعرض تلميح «اختر شركة التوصيل» (`order_flow.select_shipping_provider`) بدل `—`، عبر تمرير `isRequired` للمكوّن الخلوي. **اختبار جديد `OrderShippingProviderColumnTest` (5 حالات: تلقائي الافتراضي، الاحتفاظ بالشركة القائمة، حفظ الاختيار، التلميح، عرض الاسم).**

ملاحظة صراحة عن العدد: الإجمالي المتتبع أعلاه (446/1645 ← نحو 31.6) لم يتطابق مع تشغيل `vendor/bin/pest` الكامل الحالي (426/1536 — يشمل الاختبارات الجديدة)؛ المعتمد الآن هو **431 ناجح (1541 assertions)** من الجولة الكاملة النظيفة.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 31.7 بوابة التأكيد + مؤجل | ✅ | app/Domains/Order/Services/OrderService.php + app/Domains/Shipping/Services/OrderShippingGateway.php + app/Enums/Store/OrderStatus.php + database/seeders/SystemStatusesSeeder.php + database/migrations/2026_09_07_000001_confirm_status_green.php + orders/index.blade.php + partials/orders-table-actions-column.blade.php + partials/orders-table-cell.blade.php + a OrderConfirmGateTest + تعديل OrderActionToastUnificationTest | 6 في OrderConfirmGateTest + 8 في OrderActionToastUnificationTest (بسّلطان مُحدَّث) + 36 في الجولات المتأثرة (80 assertions) |
| **الإجمالي (جولة نظيفة)** | **426 ناجح (1536 assertions)** | | |

## Phase 31.7 — تكميل (أ) تلميحات الأعمدة المطلوبة + (ب) زر تأكيد موبايل بارز + (ج) قائمة «مهام متعددة» — 2026-09-07

**بموافقة صريحة من المستخدم («نفّذ الآن») بعد خطة منقّحة ثُبّتت بأسئلة: (أ) تلميحات لـ5 أعمدة مطلوبة فقط، (ب) زر التأكيد داخل صف إجراءات بطاقة الموبايل، (ج) زر واحد «مهام متعددة» يظهر بعد حقل البحث ويُفتح قائمة منسدلة بكل المهام.**

- **(أ) التلميحات — `orders-table-cell.blade.php`:** متغير «صافي» `$requiredHint` أعلى المكوّن الخلوي عبر `match($colKey)` (delivery_type / shipping_provider / wilaya / city / stopdesk_point) يُرادَف من `$isRequired` الممرّر؛ الخلايا الخمس تعرض `<span class="text-warning font-medium">يرجى اختيار…</span>` بدل `—`/`-` لما يكون الحقل المطلوب فارغًا. شرط stopdesk: التلميح **فقط** عندما `delivery_type === 'stopdesk'` (لا يضجّر الطلبيات العادية). تلميح الولاية أُضيف أيضًا لبطاقة الموبايل في `index.blade.php`. ملاحظة تقنية: `delivery_type` عمود `NOT NULL` في قاعدة البيانات، فمسار تلميحه سُلّمي/غير حي (أُبقي للاتساق مع الـ5 المُعتمدين).
- **الترجمات:** مفاتيح `order_flow.please_select_{shipping_provider,delivery_type,state,city,stopdesk}` في اللغات الأربع (ar/en/es/fr) + **إزالة** `select_shipping_provider` (ar/en) وتوحيده إلى `please_select_shipping_provider` (مع تحديث تأكيد الاختبار).
- **(ب) زر تأكيد الموبايل — `index.blade.php`:** داخل صف الإجراءات (`mt-3 flex gap-2 flex-wrap`) زر `edz-btn--primary` بالعنوان `confirm_title` + أيقونة هاتف، شرط ظهوره `!showTrash && $order['can_confirm'] && canStore(ORDER_CONFIRM)` — يفتح نفس درج التأكيد `openConfirmModal`؛ صف إجراءات البطاقة أصبح وجهة لمسه الأسهل (44px+) وفي المقدمة.
- **(ج) «مهام متعددة» — `bulk-actions-bar.blade.php`:** أُعيدت هيكلته من شريط كامل إلى **زر-قائمة منسدلة واحدة**: زر primary «مهام متعددة» + شارة عدد المحدد + سهم؛ القائمة: عداد، قسم الإسناد (أعضاء)، قسم الإدارة (إرسال للشركة / تغيير الحالة — مقنّع بـ ORDER_MANAGE)، ثم حذف (تأكيد EdzSwal + `bulkDelete` مع `loading-target` الـglobal المحفوظ) وتنظيف التحديد (`merchant.bulk_clear`). M10 محفوظ: تعطيل + حلقة spinner داخل الزر أثناء أي عملية جماعية. لاحظ: `:class` على أيقونة `chevron-down` تمرّر للـcomponent كـPHP فكان «Undefined constant open» — حُوِّل إلى `x-bind:class`.
- **الموقع:** `@include` انتقل من فوق الجدول إلى **داخل شريط الأدوات فورًا بعد حقل البحث** بشرط `count($this->selectedOrders) > 0` (لا يظهر إلا عند التحديد).
- **الاختبارات — `OrderRequiredColumnsHintTest.php` (9):** تلميح wilaya + city + shipping_provider الفارغ، تلميح stopdesk عند stopdesk فقط (لا يعرض مع home)، الأعمدة الاختيارية تبقى `-` (لا تلميح)، زر التأكيد الموبايل يظهر لطلبية `can_confirm` (ويُستدعى بـ`escape: false` لأن `assertSee` يُنمّص علامات الاقتباس) ويختفي بعد الشحن، والقائمة المنسدلة تظهر في شريط الأدوات فقط عند `selectedOrders>0`. فخّ الاختبار: `$opts['x'] ?? default` يبتلع `null` الصريح → بُدِّل إلى `$opts += […]`.
- **التحقق النهائي:** `php -l` نظيف + `view:clear`/`view:cache` (FRESH-CACHE-OK) + الجولات الجديدة 9/9 (17 assertions) + `OrderShippingProviderColumnTest` 5/5 + **الجولة الكاملة النظيفة 440 ناجح (1558 assertions)** (فشل وحيد عابر في تشغيل أول كان قفل `storage/framework/views` على ويندوز — اجتاز 9/9 منفردًا بعد `view:clear`).

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 31.7 تكميل أ+ب+ج | ✅ | orders/index.blade.php + partials/orders-table-cell.blade.php + partials/bulk-actions-bar.blade.php + resources/lang/{ar,en,es,fr}/order_flow.php + merchant.php + OrderRequiredColumnsHintTest (جديد) + تحديث OrderShippingProviderColumnTest | 9 في OrderRequiredColumnsHintTest + 5 في OrderShippingProviderColumnTest |
| **الإجمالي (جولة نظيفة)** | **440 ناجح (1558 assertions)** | | |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — مظهر تلميحات الأعمدة في الجدول والموبايل، بارزوية زر «تأكيد الطلبية» في بطاقة الموبايل، سلوك زر «مهام متعددة» (فتح القائمة من الأسفل موبايل / منسدلة ديسكتوب) واختفائه بعد مسح التحديد.

## إصلاحات الفحص البصري 31.7 (المستخدم كشف 4 نقاط) — 2026-09-07

**نفّذت بعد فحص حي شامل + توثيق الخطة وموافقة المستخدم الصريحة (اختياراته: تحسين بلا سكيما / بحث داخل القائمة / زر في صف الإجراءات فقط):**

- **(1) الأعمدة المطلوبة على الموبايل:** partial جديد `partials/orders-mobile-fields.blade.php` يرسّم حقول الجغرافيا/التوصيل (delivery_type, shipping_provider, city, stopdesk_point, address + دمج) داخل بطاقة الموبايل بنفس منطق التلميحات `please_select_*`؛ أُدرج في البطاقة بعد الولاية، وشرط `shipping_cost` ترِفع القيد `&& !in_array('total', …)` فظهر في البطاقة دائمًا عند تفعيله. **دون استعلامات إضافية** (يعيد استخدام `$order` المحمّل والخيارات الجاهزة `editProviderOptions`/`editCityOptions`/`editStopdeskOptions`).
- **(2) التكليف الجماعي ببحث:** استبدال أسطر الأعضاء (`@foreach allMembers`) بمكوّن `<x-edz.select>` بحث + زر «تطبيق» يرتبط بـ`bulkAssignMembershipId` الجديدة، مع حارس `blank($membershipId)` في `bulkAssignAgent` يمنع مسح الإسناد بالخطأ.
- **(3) بوب-آب التأكيد أذكى (بلا سكيما):** الملخص يعرض الآن `confirmation_attempts` + `last_contact_at` (diffForHumans)، وحقل «ملاحظة» (`confirmNote`) يُخزَّن في `meta['confirm_note']` عند التأكيد فقط (تأكيد فقط / تأكيد وإرسال) ويُعاد تعبئته عند فتح الدرج.
- **(4) زر التأكيد المزدوج:** زر «تأكيد الطلبية» في `orders-table-actions-column` مقيّد بـ`layout === 'compact'` فقط — يختفي من قائمة «…» الموبايل (`list`) ويبقى في صف إجراءات البطاقة مرة واحدة.
- **الاختبارات (+5 في OrderRequiredColumnsHintTest):** ظهور الحقول الخمسة في البطاقة الموبايل، التكليف عبر بحث بالزر تطبيق، الدرج يعرض المحاولات/آخر تواصل + الملاحظة، حفظ الملاحظة في `meta` عند تأكيد فقط، وزن `wire:click` الخاص بالتأكيد = 2 (ديسكتوب + بطاقة) لا 3 (لا تكرار في القائمة). **الجولة الكاملة النظيفة: 445 ناجح (1571 assertions)** (قبلها 440/1558)، `view:clear`+`view:cache` سليمة.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| إصلاحات الفحص البصري 31.7 | ✅ | orders/index.blade.php + partials/orders-mobile-fields.blade.php (جديد) + partials/bulk-actions-bar.blade.php + partials/orders-table-actions-column.blade.php + 4 ترجمات order_flow (confirm_note) + OrderRequiredColumnsHintTest (+5) | 14 في OrderRequiredColumnsHintTest + 6 في OrderConfirmGateTest + 19 في جولات التأكيد/الشحن/التوست |
| **الإجمالي (جولة نظيفة)** | **445 ناجح (1571 assertions)** | | |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — البطاقة الموبايل تعرض الآن شركة التوصيل/نوعية التوصيل/البلدية/العنوان/سعر التوصيل، قائمة «مهام متعددة» بها بحث عند تكليف عضو، ملخص التأكيد الجديد (محاولات + آخر تواصل + ملاحظة)، وزر تأكيد واحد فقط في البطاقة.

## Phase 31.8 — التعديل المضمّن للمنتجات/المتغيرات/الكمية/السعر + إعداد السماح بتعديل السعر + صلاحية ORDER_EDIT_PRICE + مكوّن Checkbox — 2026-09-07

**بموافقة صريحة («نفذ») بعد خطة مثبّتة بأسئلة المستخدم (قراراته: محرر مدمج موحّد للأعمدة الثلاثة بحفظ واحد؛ احترام السعر المكتوب عند `allow_price_edit` + `ORDER_EDIT_PRICE` مع تعديل قاعدة C3؛ إصلاح مصفوفة صفحة الفريق لتظهر كل حالات الإنم مع شارة custom).**

- **(1) الإعداد + الصلاحية + المكوّن:**
  - Migration `2026_09_07_000001_add_allow_price_edit_to_store_settings.php` (boolean **false** افتراضيًا) + `StoreSetting` fillable/cast `allow_price_edit`.
  - `StorePermissionEnum::ORDER_EDIT_PRICE = 'order.edit.price'` + مفاتيح `permissions.order.*` المتداخلة في ar/en/es/fr.
  - مكوّن جديد `resources/views/components/edz/checkbox.blade.php` (دعم `wire:model`/`wire:click` وحجم sm/md وlabel/hint/disabled) + `.edz-checkbox` SCSS في `resources/css/components/_forms.scss` (كانت 5 استخدامات بلا CSS → أصبحت تُعرض صحيحة تلقائيًا).
  - إعادة استخدام checkbox: store-settings (تبويب التجارة: `allow_price_edit` الجديد + `guest_checkout` + مخزون) + orders (selectAll/صفوف ديسكتوب/بطاقة موبايل/مودال إعداد الأعمدة) + teams (`is_active` + مصفوفة الصلاحيات).
  - إصلاح teams: `$allPermissions` = `StorePermissionEnum` كله مجمّعًا (`groupBy` على البادئة) بدل القائمة الجزئية.
- **(2) المحرر المضمّن للعناصر — `orders/index.blade.php` + partial جديد `orders-inline-items-editor.blade.php`:**
  - الخلايا الثلاث (products/quantity/price) في `orders-table-cell.blade.php` صارت أزرار `startOrderItemsEdit(orderId)` عند `ORDER_MANAGE` (ديسكتوب + زر «تعديل المنتجات…» في بطاقة الموبايل).
  - المحرر صف `<tr colspan>` تحت صف الطلب (ديسكتوب) / حقل بالبطاقة (موبايل): كل عنصر بصف (الاسم + SKU، خطوة كمية مطابقة للمودال، سعر الوحدة مقيّدًا، الإجمالي، حذف) + بحث إضافة منتج (`itemsAddSearch` debounce 500ms ← `searchInlineItems` بحد 25، متغير متعدد يعرض قائمة متغيراته inline عبر `toggleInlineAddProduct`, إضافة عبر `addInlineItem`→`addFormItem` لإعادة الاستخدام).
  - **قاعدة السعر (C3 المعدّلة):** `$saveOrderItems` يقرأ `itemsPriceEditable()` (مُذكَّرة `static` لكل طلب؛ تعادل `allow_price_edit && canStore(ORDER_EDIT_PRICE)`) — عند التفعيل يُحترم السعر المكتوب ويُخزَّن (مع `subtotal`/`total_amount`/إعادة حساب shipping)، وإلا يُفرض سعر DB دائمًا. إزالة/إضافة/تعديل الكميات عبر `OrderItem` مباشرة (نفس نمط `submitEdit`)، مع فحص المخزون (delta) وحارس `guardOrderEditable` (الشحن يعطّل الحفظ).
  - ترجمات جديدة: `merchant_panel.no_items` / `delete_item` / `edit_items` في 4 لغات.
- **الاختبارات — `OrderInlineItemsEditTest` (11):** فتح المحرر وتحميل draft، فرض سعر DB بلا الإعداد، احترام السعر عند الإعداد+الصلاحية (owner)، staff مخصص `order.manage` بلا حقل سعر وسعر DB، staff بـ`order.manage+order.edit.price` يحترم السعر، الإعداد وحده يكفي؟ (لا)، إزالة+إضافة عنصرين، حجب backorder، حجب الشحن، رفض بدون `order.manage`، والقيمة الافتراضية false. **فخّ مؤكد:** القوائم المخصصة للعضوية تُلغي صلاحيات الدور كليًا → مجموعة الصلاحيات المختبرة **يجب أن تتضمن `ORDER_VIEW`** وإلا `abort_unless(ORDER_VIEW)` (index.blade.php:512) يقطع العرض خلف «Invalid snapshot» مضلّل في الـharness.
- **التحقق النهائي:** `view:clear`+`view:cache` سليمة، الجولات المتأثرة (inline edits + roles/gates + settings + query-count) **74 ناجح (427 assertions)** ثم **الجولة الكاملة النظيفة 457 ناجح (1622 assertions)**.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 31.8 محرر العناصر + إعداد السعر + الصلاحية | ✅ | database/migrations/2026_09_07_000001_add_allow_price_edit_to_store_settings.php + app/Models/Stores/StoreSetting.php + app/Enums/Store/StorePermissionEnum.php + resources/views/components/edz/checkbox.blade.php (جديد) + resources/css/components/_forms.scss + livewire/merchant/store-settings.blade.php + livewire/merchant/teams/index.blade.php + livewire/merchant/orders/index.blade.php + partials/orders-inline-items-editor.blade.php (جديد) + partials/orders-table-cell.blade.php + permissions.php×4 + merchant_panel.php×4 + OrderInlineItemsEditTest (جديد) | 11 في OrderInlineItemsEditTest (40 assertions) + 74 في الجولات المتأثرة (427 assertions) |
| **الإجمالي (جولة نظيفة)** | **457 ناجح (1622 assertions)** | | |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — فتح المحرر بالنقر على منتج/كمية/سعر (جدول ديسكتوب + زر بطاقة الموبايل)، عناصر المحرر بخطوات الكمية وحذف وبحث الإضافة، إخفاء حقل السعر لعضو بلا `order.edit.price`، ظهور مفتاح «السماح بتعديل السعر» في تبويب التجارة (إعدادات المتجر)، ومصفوفة صفحة الفريق تعرض كل حالات الإنم الجديدة.

---

## Phase 31.9 — بوب أب تعديل بنود الطلبيات لكل عمود على حدة (بدل المحرر المضمّن) + إصلاح خلل Checkbox — 2026-09-08

**بموافقة صريحة («نفذ») بعد فحص السبب الجذري وقرارات المستخدم:** المستخدم وجد التصميم المضمّن السابق (31.8) «سيئًا وغير عمليًا» وأعاد الاتجاه المقرر سابقًا (محرر مدمج بثلاثة أعمدة) → **بوب أب مستقل لكل عمود** (منتجات / كميات / أسعار) بنمط Pattern A، و**كل عمود يعدّل لوحده**. إجابات توضيحية: (أ) الموبايل = زر واحد «تعديل المنتجات…» يفتح ورقة سفلية صغيرة بثلاثة خيارات؛ (ب) عمود السعر عند غياب الصلاحية = **نص ساكن بلا أي زر/قائمة**.

- **(1) إصلاح الـCheckbox — السبب الجذري:** في `components/edz/checkbox.blade.php` كان `$checked !== null ? 'checked' : ''` — مجرد **وجود** الخاصية (أي قيمة) يحسب في HTML بمثابة محدّد، ولهذا بدت الخانات دائمًا محدّدة في: خانات صفوف الطلبيات (ديسكتوب 4454 + موبايل 4516) ومفاتيح مودال إعداد الجدول (~4968). مستخدمات `wire:model` سليمة لأن إدارة Livewire لا تمرر `checked`. **الإصلاح:** `$checked === true ? 'checked' : ''`. أدلة: اختبار ترميز `Blade::render` — `:checked="true"` → تحتوي على `\schecked\b`، و`:checked="false"`/بدونها → لا تحتوي (أُضيف ضمن `OrderInlineItemsEditTest`).
- **(2) إعادة الهيكلة إلى نوافذ لكل عمود:**
  - `index.blade.php`: state `itemsModal = null`؛ استبدال `startOrderItemsEdit`/`cancelOrderItemsEdit` بـ`openItemsModal(kind, orderId)`/`closeItemsModal()`. الأول: حارس `ORDER_MANAGE` (توست `permission_denied` عند الرفض)، قائمة أنواع مسموحة (`products/quantity/price`)، منع `price` بلا `itemsPriceEditable()`، وبناء draft من `items_summary` الموجود أصلًا (لا استعلام إضافي) ثم `startEdit('order.items', ...)`؛ الأخير: صفر `itemsModal` + `cancelEdit()` + تصفير البحث + **`form['items'] = []`** (التخلص من النسخة). `saveOrderItems` تُغلق النافذة عبر `closeItemsModal()` بعد النجاح وتبقيها مفتوحة مع `editingError` عند فشل التحقق.
  - حذف صف `<tr colspan>` (items-editor-row) من الجدول وحقل الموبايل المضمّن مع **حذف اليتيم** `partials/orders-inline-items-editor.blade.php`.
  - الموبايل: زر واحد «تعديل المنتجات…» يعرض `itemsEditMenu` (مكوّن Alpine جديد في `resources/js/components/order-row-actions.js` + تسجيله في `panel.js`) — ورقة سفلية <639px/لوحة مثبتة sm+ (نمط `orderMoreMenu`)، صفوف products/quantity/price (الأخير شرطي بـ`itemsPriceEditable()`)، أزرار 44px+ وhover واضحة.
  - الخلايا: products → `openItemsModal('products', …)`؛ quantity → `openItemsModal('quantity', …)`؛ price → زر فقط عند `ORDER_MANAGE && itemsPriceEditable()` وإلا **نص ساكن**؛ حُذفت فروع `editingField === 'order.items'` من `orders-table-cell.blade.php`.
  - partial جديد `orders-items-edit-modals.blade.php` (ثلاث نوافذ Pattern A عبر `x-edz.modal` + `:is-open="true"` + `wire:key`؛ قوائم `max-h-[45vh]` بحد أقصى، نص «لا منتجات» عند الفراغ) + `orders-items-modal-footer.blade.php` (خطأ التحقق + إلغاء/حفظ مع spinner). **نقطة تقنية مؤكدة:** `:class="…"` على وسوم المكوّنات (`x-edz.*`) يجمّعه Blade كـPHP (اصطدام مع Alpine) → يُستخدم `x-bind:class` (نمط `bulk-actions-bar.blade.php:24`)؛ والسلاسل MUTA-DEFENSIVE `$item['price'] ?? 0`/`$item['quantity'] ?? 1` في كل عرض.
  - قواعد السلوك (من 31.8 وتبقى كما هي): C3 لفرض/احترام السعر، حارس `guardOrderEditable` (الشحن)، فحص المخزون (delta) لحاجز الـbackorder، `addInlineItem`←`addFormItem`، بحث debounce 500ms بحد 25. لا مفاتيح ترجمة جديدة (كلها قائمة).
- **الاختبارات — `OrderInlineItemsEditTest` (12/62):** فتح نافذتي products+quantity مع `itemsModal.kind` وتحميل draft ثم إغلاق يصفّر `form.items`؛ فرض سعر DB بلا الإعداد؛ احترام سعر owner عند `allow_price_edit` + إعادة حساب الإجمالي؛ staff `order.manage` بلا نافذة سعر (فقرة ساكنة) وسعر DB؛ staff بـ`order.manage+order.edit.price` يرى ويحترم السعر؛ الإعداد وحده لا يكفي؛ إزالة+إضافة عنصرين؛ حجب backorder؛ حجب الشحن؛ رفض بدون `order.manage`؛ قيمة افتراضية `false`؛ اختبار ترميز checkbox. **الفخّ المؤكد من 31.8 ما زال ساريًا** (قوائم الصلاحيات المخصصة تستلزم `ORDER_VIEW`).
- **التحقق النهائي:** الجولة الكاملة النظيفة **458 ناجح (1644 assertions)** (كانت 457/1622 — الفرق = اختبار checkbox + 22 تأكيدًا) ثم `view:clear`+`view:cache` سليمة + **`npm run build`** (تغيّرت JS: `panel-C6Dgvy4c.js` / `app-BrEjoHgF.js`).

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 31.9 نوافذ تعديل العناصر + إصلاح Checkbox | ✅ | resources/views/components/edz/checkbox.blade.php (إصلاح) + resources/views/livewire/merchant/orders/index.blade.php (closers/نوافذ/قائمة الموبايل) + partials/orders-items-edit-modals.blade.php (جديد) + partials/orders-items-modal-footer.blade.php (جديد) + partials/orders-table-cell.blade.php (خلايا→openItemsModal) + partials/orders-inline-items-editor.blade.php (حُذف) + resources/js/components/order-row-actions.js + resources/js/panel.js + tests/Feature/Merchant/OrderInlineItemsEditTest.php | 12 في OrderInlineItemsEditTest (62 assertions) + الجولة الكاملة 458 (1644) |
| **الإجمالي (جولة نظيفة)** | **458 ناجح (1644 assertions)** | | |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — النقر على عمود المنـtجات والكمية يفتح نافذته المعنية، عمود السعر **نص ساكن** لمستخدم بلا صلاحية / نافذة سعر لمن يملكها؛ زر الموبايل «تعديل المنتجات…» يفتح ورقة سفلية بثلاثة خيارات؛ خانات row-select والمفاتيح لم تعد محدّدة صفرًا مسبقًا (الإصلاح)؛ ويبقى تحقق 31.8: مفتاح «السماح بتعديل السعر» في إعدادات المتجر ومصفوفة الفريق بكل حالات الإنم.

---

## Phase 32 — التسعير الديناميكي للمكتبي (stopdesk) وترجيح قائمة الأسعار (سبتمبر 2026) ✅

**بموافقة صريحة من المستخدم («نفّذ») على خطة التسعير الديناميكي في نافذتي الإضافة/التعديل والملخص المالي. الملف المرجعي: `OrdersrefactorplanFixed.md` (قرارات §9 + ملاحظة المصدر المؤجلة).**

### قرارات المستخدم (binding) التي تحكم السلوك
- **كشف «يرجى تحديد» بدل «مجاني» المضلِّل:** عند نقص بيانات التسعير (منزلي بلا ولاية، أو المكتبي بلا ولاية/شركة) يُعرض التلميح `shipping_hint_delivery` في الشبكة المالية والمودال، ويبقى مصدر السعر في الجدول هو الوحيد الحقيقي.
- **تغيير الشركة يعيد ضبط الوجهة:** تغيير الشركة في stopdesk يعيد تصفير ولاية/بلدية/مكتب خارج تغطية الشركة مع توست `order_flow.destination_reset_for_carrier` (تلميح `no_company_coverage` عند شركة بلا تغطية على الإطلاق).
- **قائمة الأسعار تتفوق على الشركة المختارة (للتوصيل المنزلي فقط):** بيانات `PriceList` (ولاية/بلدية + free_above) لها الأسبقية على `home_cost` الشركة المختارة عند التوصيل للمنزل.
- **مكتبي stopdesk:** يُسعَّر من `delivery_rates.office_cost` (مع `free_above` بالاتجاهين)، والتراجع عند غيابه → سعر الشحن legacy (`ShippingRate` لشركة `rate`) → وأخيرًا `office_unavailable` (لا «مجاني» أبدًا بلا بيانات).
- **مصدر السعر:** تُؤجَّل إضافة عمود «المصدر» في جدول الطلبيات — `orders.shipping_cost` يبقى المصدر الوحيد المؤكَّد؛ تُعرض «معلنة/من قائمة الأسعار/سعر ثابت» فقط في الملخص حالياً.

### ما تَمّ

- **`app/Domains/Shipping/Services/ShippingCostCalculator.php` — أُعيدت كتابتها (المصدر الوحيد):**
  - التوقيع الجديد `calculate(array $order)` يعمل على بنية موحدة يمكن أن تأتي من بناء مؤقت للمودال أو من نموذج Order؛ تمرير `provider_id` + `delivery_type` صراحةً (حتى يُسعَّر المكتبي قبل حفظ الطلبية).
  - **المكتبي (stopdesk):** ① `delivery_rates.office_cost` (مع `free_above` ناقص المصاريف «بالاتجاهين»: مجاني عند override أو عند تساوي/تجاوز العتبة)، ② التراجع لسعر الشحن legacy `ShippingRate` (`method='rate'`، `source_type='company'`)، ③ `office_unavailable` (مقترنًا بـ `cost=0` و `office_unavailable=true`) — **لا «مجاني» أبدًا بلا بيانات** (كسر للسلوك القديم الذي كان يقرأ قائمة الأسعار للمكتبي بالخطأ).
  - **المنزلي (home):** ① `PriceList` (ولاية البلدية ثم الولاية، `free_above` عند تجاوز العتبة، وارث `source_type='price_list'`) — **يتفوق على الشركة المختارة**، ② ثم `DeliveryRate` المُعلن للشركة (`home_cost`/city)، ③ ثم flat rate الشركة، ④ ثم legacy `ShippingRate`، ⑤ ثم free (source `none`).
  - **قبيطة الشركة (provider_id):** تقيّد البحث عن announced rates؛ flat rate يُسعَّر من `provider_id` المعطاة.
  - إصلاح خطأ: صناديق `office_cost/free_above` في DB كانت strings → cast صريح إلى float قبل الحساب (عبّر عن ذلك في الاختبارات بكشوف `(float)`).

- **`app/Domains/Order/Services/OrderService.php` (createManual ~سطر 145):** تمرير `provider_id` + `delivery_type` من المودال إلى الحاسبة → يُسعَّر stopdesk عند الإنشاء (لم يكن مسبقًا واكتشفته الاختبارات الجديدة).

- **`resources/views/livewire/merchant/orders/index.blade.php` — تتالي الشركة/الوجهة/المكتب (إعادة تطبيق كاملة بعد حادثة ترميز):**
  - closure `$providerOfficeStates($providerId)` (ولايات المكاتب المتاحة لدى شركة عبر `obtained states` + stopdesk points)، و`$applyProviderScope()` (يُستدعى عند `wire:change` على الشركة + عند `changeDeliveryType`) — إذا لم يعد الوجهة/المكتب ضمن تغطية الشركة الجديدة: يُصفَّر مع توست `destination_reset_for_carrier`؛ بلا تغطية إطلاقًا → تلميح `no_company_coverage`.
  - `$loadCities(string $stateId, bool $resetCity = true)`: بلديات stopdesk الآن **مقيّدة بنقاط stopdesk** (حصر فعلي) + تلميح «لا بلدية» `no_company_coverage`؛ `$releaseStaleDestination()` (مكتب/مدينة خارجة عن النطاق).
  - `$loadFormScope()`/`providerOfficeStates`/`releaseStaleDestination` تُستدعى في `openDeliveryModal`/`openEditModal` وصفّرة في `openCreateModal`؛ `recalculateOrderShipping` يمرر `providerId` + `deliveryType` بلا إرجاع مبكر (لم يَعُد منزليًا فحسب).
  - `$saveOrderProvider` (inline) و`$saveOrderService` + نافذة التعديل السريع: إسقاط المكتب الذي لم تعد الشركة الجديدة تخدمه مع نفس التوست.
  - **حادثة ترميز أثناء الاحتواء:** مسّح كتابة سكربت PowerShell (`Set-Content`) لملف index.blade.php حوّل UTF-8 إلى بايتات غير صالحة → استُعيد الملف من HEAD (`git checkout --`) وأعيد تطبيق **كل** التغييرات بأداة التحرير الآمنة (تحقّق `check-encoding.php` + `php -l` + `view:cache`). تحذير أُدرج: لا يُكتب UTF-8 عبر PowerShell PS5.1 `>`/`Set-Content` أبدًا.

- **`partials/order-form-modal.blade.php` (نافذتا الإضافة):** `wire:change` للشركة → `applyProviderScope($event.target.value)`؛ زر home عند التبديل يستدعي `changeDeliveryType('home')` أيضًا؛ خيارات الولاية `formAvailableStates !== [] ? … : allStates` + سطر `formCoverageHint`؛ سلسلة تلميح المكتب المحسّنة بفرع `office_none_for_destination`.

- **`partials/order-financial-summary.blade.php` — أُعيدت كتابتها:** كتلة `@php` تحسب `$delivery/$deliveryIncomplete/$deliveryUnavailable` عبر بوابة (المكتبي: شركة + ولاية؛ المنزلي: ولاية؛ يتطلب أصناف) وتستدعي الحاسبة بنفس `providerId`+`deliveryType` المستخدمين عند الحفظ؛ بطاقة التوصيل تعرض: تلميح `shipping_hint_delivery` عند ناقص → مفتاح `no_company_coverage` عند شركة بلا تغطية → `storefront.shipping_unavailable` عند غير متاح → مجاني → سعر + سطر مصدر (`shipping_source_price_list`/`shipping_source_flat`/`shipping_source_announced` مع :provider).

- **التوصيل السريع (`delivery-edit-modal.blade.php`):** نفس البوابة/الإسقاطات عبر الطرق المشتركة؛ لا مفاتيح جديدة تضاف بخصوصه.

- **ترجمات ×4 لغات (en/ar/fr/es):**
  - `order_flow`: `destination_reset_for_carrier`, `no_company_coverage`.
  - `merchant_panel`: `shipping_hint_delivery`, `shipping_source_announced`, `shipping_source_price_list`, `shipping_source_flat`, `office_none_for_destination`.

### الاختبارات — `tests/Feature/Merchant/OrderShippingDynamicPricingTest.php` (11/30)
- مكتبي: يلتقط office_cost الشركة المطلوبة؛ يقع على office_cost الشركة الافتراضية عند غياب provider؛ بلا office_cost → `office_unavailable` (لا مجاني)؛ **يتجاهل قائمة الأسعار ويقع على legacy** (400 من `ShippingRate` لا 300 من القائمة)؛ `free_above` يعمل للمكتبي دون base.
- منزلي: قائمة الأسعار 650 تتفوق على `home_cost` 1000 للشركة المختارة؛ `provider_id` يقيّد الـannounced؛ flat rate يتبع `provider_id`.
- createManual: حفظ office_cost كـ `shipping_cost`؛ صفر عندما `office_unavailable`؛ المنزلي من قائمة الأسعار 650.

### الدليل النهائي
- `OrderFinancialSummaryTest` (7/30) عدّل تأكيد الاختبار الأول ليطابق القرار: شبكة الإنشاء مع أصناف وبلا ولاية تعرض `shipping_hint_delivery` بدل شارة «مجاني» القديمة المضلِّلة. **بوابة التسعير للمنزلي بلا ولاية تبقي الشبكة محايدة تمامًا.**
- `php -l` لكل الملفات المعدلة + `view:cache` (FRESH-CACHE-OK) + استهداف `OrderOfficeSelectionTest` + `OrderFinancialSummaryTest` + `OrderShippingDynamicPricingTest` → 21 ناجح.
- **السويت كاملة نظيفة: 479 ناجح (1712 assertions)** — مقارنةً بخط الأساس 468/1682 قبل العنقود (+11 اختبار / +30 تأكيد = ملف التسعير الجديد فقط) — **صفر انحدار**.

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — تلميح «يرجى تحديد» في الشبكة المالية عند فتح إضافة بلا ولاية/شركة؛ تغيير الشركة في stopdesk يسقط وجهة خارج التغطية بتوست؛ ترجيح قائمة الأسعار للمنزلي (سطر «من قائمة الأسعار» في الملخص)؛ ومشاركة نفس السلوك في نافذة التعديل السريع.

## Phase 33 — حصر البلديات + مفتاح الأسعار المعلنة + مزامنة نقاط الاستلام + تتبع NOEST الآلي (سبتمبر 2026) ✅

**النطاقات الأربعة المعتمدة من المستخدم (أوامر صريحة) — اكتملت جميعًا مع أدلة قابلة للقياس.**

### (1) حصر البلديات في نموذج الطلبية حسب شركة التوصيل
- `app/Domains/Shipping/Models/StopdeskPoint.php` → `communitiesCoveredFor(string $storeId, string $providerId, string $stateId): array` — نقاط نشطة `city_id NOT NULL` خاصة بالمتجر/الشركة/الولاية، `distinct()->pluck()->all()`.
- `orders/index.blade.php`: `loadCities` يطبّق الحصر لكل شركة (لا بوابة carrier) + مزامنة مسبقة للشركات carrier-backed داخل try/catch؛ شركة بلا تغطية → قائمة فارغة + تلميح `formCoverageHint='no_company_coverage'` (لا fallback لكل البلديات)؛ `startOrderCityEdit` يحصر خيارات المحرر المضمّن بـ`whereIn` أو `whereRaw('1 = 0')`.
- الأدلة: `tests/Feature/Merchant/OrderCityScopeProviderTest.php` — **5 ناجحة (8 assertions)**: حصر المودال، لا تغطية → فارغ + تلميح، home يُبقي الكل، carrier-backed ينجو من فشل المزامنة، المحرر المضمّن محصور. محطتان مؤكَّدتان: `toBeCanonicalizing` غير موجودة في Pest → مقارنة يدوية بعد ترتيب؛ slug منصة فريد لتجنب تصادم `noest` المزروع.

### (2) مفتاح إيقاف/تشغيل الأسعار المعلنة (DeliveryRate)
- `announced-rates.blade.php`: `loadRates` يتضمن `is_active`؛ closure `toggleRateActive(string $stateId)` (يقلب صفًا قائمًا فقط، يحدّث `ratesByState`، حارس DELIVERY_PRICING_MANAGE)؛ switch يظهر فقط عند وجود صف (`$hasRateRow`)، صفوف معطّلة تكتسب شارة + حقول/زر إدارة مقفلة. المفتاح Tailwind خالص (`translate-x-[1.25rem] rtl:-translate-x-[1.25rem]`) — لا أيقونات toggle غير موثَّقة في icon.blade.php.
- ترجمات ×4: `rate_enable`/`rate_disable` (en/ar/fr/es).
- الأدلة: `tests/Feature/Merchant/AnnouncedRatesToggleTest.php` — **3 ناجحة (11 assertions)**: flip في DB+state، لا صف → لا switch + no-op، التعطيل يزيل السعر من الحاسبة (`ShippingCostCalculator` يفلتر `is_active=true`).

### (3) واجهة مزامنة نقاط الاستلام (stopdesk) + شارة المزامنة
- `stopdesk.blade.php`: `syncCandidates` (شركات carrier بكود في `delivery.carrier_integrations`) + `synced` لكل نقطة (`filled(external_code)`) + closure `syncStopdesk` (يستدعي `StopdeskOfficeSync::sync(..., refresh:true)`، رسائل swal نجاح/فارغ/بلا محول، `@disabled` أثناء التنفيذ) + بطاقة المزامنة + شارة check-circle.
- ترجمات ×4 (`stopdesk_sync_*`, `stopdesk_synced`, `sync_no_adapter`).
- الأدلة: `tests/Feature/Merchant/StopdeskSyncUiTest.php` — **4 ناجحة (16 assertions)**: فلترة المرشحين، شارة المزامنة، النقر يجلب المكاتب ويحدّث النقاط عبر `Http::fake(['noest.test/*'=>...])`، شركة بلا اعتمادات → رسالة info. اصطلاح مثبّت: فحص الأحداث عبر `assertDispatched('swal', type: 'success')` (مسمّاة) مقابل `swal:toast` (مصفوفة موضعية).

### (4) NOEST P1 — تتبع آلي (خريطة أحداث + جوب + جدولة)
- `app/Domains/Shipping/Services/NoestTrackingMapper.php` — خريطة `event_key` NOEST (من وثائق v2.3) → `OrderTrackingStatus`: `livre/livred`→DELIVERED، `fdr_activated/nouvel_tentative/return_redispatched_to_livraison`→OUT_FOR_DELIVERY، `mise_a_jour`→FAILED_ATTEMPT، سلسلة `return_asked_*`→RETURNING ثم `retour_dispatched_*/colis_retour_*/livraison_echoue_recu/return_validated`→RETURNED، `upload/customer_validation`→SHIPPED، `validation_*/sent_to_redispatch/annulation/cancel_return_dispatched`→IN_TRANSIT؛ أحداث مالية/تعديلات → null (لا تخمين). Fallback عبر نصوص الأحداث بـ`OrderTrackingStatus::fromCarrier`.
- `NoestIntegrationAdapter::trackingsInfo(...)` — POST `/get/trackings/info` بـBearer، دفعات ≤20، عودة مزوّدة بمفتاح التتبع، خطأ بنفاد مهلة/HTTP.
- `app/Domains/Shipping/Jobs/SyncNoestTrackingJob.php` — لكل شركة noest نشطة: صفوف مستحقّة (`null delivered_at/returned_at` أو بلا مزامنة أو أقدم من 15 دقيقة) → دفعات → تحديث `carrier_status/carrier_label/tracking_status/carrier_raw` + `shipped_at` (أول حدث) + `delivered_at`/`returned_at` (تاريخ حدث الحالة) → تجديد `last_synced_at`؛ لا انحدار للحالات النهائية؛ سجل `OrderTrackingHistory` عند تغيّر الحالة فقط (`payload.carrier_sync`). الأخطاء محبوسة بـ`report()` وتُمرَّر التشغيل.
- `routes/console.php` — `Schedule::call(...)->name('sync-noest-trackings')->everyTenMinutes()->withoutOverlapping()` يوزّع جوبّا لكل متجر له شركة noest.
- الأدلة: `tests/Feature/Shipping/NoestTrackingSyncTest.php` — **6 ناجحة (23 assertions)**: تقدم إلى delivered مع `delivered_at` (تاريخ حدث livre) + سجل حالة، خريطة الإرجاع إلى returned، حدث غير مرسوم → لا تغيير، فشل الخادم محبوس، عدم إعادة سبر النهائيات الطازجة (لا انحدار ولا مضاعفة سجلات)، والـadapter ينشر دفعة Bearer صحيحة. محطتان مؤكَّدتان: `handle()` تستقبل `?StopdeskOfficeSync` (الحِقن من القائمة يملؤه والاستدعاء المباشر عبر `??=`); فحص التاريخ عبر `isSameDay` لا أرقام ثابتة (حساسية منطقة زمنية للجلسة في مسار write/read). **لم تُلمس** ملفات التتبع الثلاثة غير الملتزمة للمستخدم.

### المدى الكلي
| | الخط الأساس (قبل العنقود) | الآن | الدلتا |
|---|---|---|---|
| اختبارات | 479 | **497** | +12 (بنود 1-3) + 6 (بند 4) |
| تأكيدات | 1712 | **1770** | +35 + 23 |
| انحدار | 0 | **0** (جولة كاملة نظيفة، ~350s) | — |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — تلميح «لا بلدية» عند شركة بلا تغطية في المودال/المحرر المضمّن، مفتاح إيقاف الأسعار في صفحة الأسعار المعلنة (وصفوف معطّلة)، بطاقة مزامنة نقاط الاستلام + شارة synced، وعرض أعمدة حالة التتبع المحدّثة في صفحة التتبع بعد التشغيل التلقائي للجوب.

---

## بنود المتجر (أ–ز) — مفتاح الأسعار الشامل + الحصر الصارم + حاجز المتجر + ملاحظة السعر + المزامنة المجدولة + الشارة (سبتمبر 2026) ✅

**سبعة بنود معتمدة من المستخدم (أوامر صريحة) — اكتملت جميعًا مع أدلة قابلة للقياس.**

### أ — مفتاح إيقاف/تشغيل كامل لأسعار كل شركة (Master toggle)
- `announced-rates.blade.php`: closure `toggleAllRatesActive($providerId)` يقلب `is_active` لكل صفوف `DeliveryRate` للشركة دفعة واحدة (`where(...)->update`)، ثم `loadRates()` لإعادة البناء؛ UI: مفتاح رئيسي في رأس بطاقة كل شركة مع شارات حالة (enabled/mixed/disabled) عبر `countByStatus`.
- ترجمات ×4: `rates_disable_all`/`rates_enable_all`/`rates_master_label`/`rates_master_enabled`/`rates_master_mixed`/`rates_master_disabled`/`rates_total` (trans_choice) + `no_rates_yet`.
- الأدلة: `tests/Feature/Merchant/AnnouncedRatesToggleTest.php` — **6 ناجحة (25 assertions)**.

### ب — حصر صارم لنقاط الاستلام بالولاية والبلدية
- `orders/index.blade.php`: `rebuildFormOffices` يعيد البناء بـ **`where('state_id', $form['state_id'])->where('city_id' ...)` بلا `orWhereNull`** — نقاط بلا جغرافيا تختفي من خيارات الإرسال وتبقى في صفحة إدارتها؛ `inlineStopdeskOptions` (محرر الشركة) يحصر بولايتي/بلدية الطلبية فقط.
- ب/أ — الولاية إلزامية في نموذج المكتب: مُتحقَّق أصلًا (`stopdeskForm.state_id => 'required'`).
- الأدلة: `tests/Feature/Merchant/OrderOfficeSelectionTest.php` — **19 ناجحة (63 assertions)**.

### د — حاجز إرسال المتجر (شركة/موصّل نشط)
- `app/Domains/Order/Services/OrderCompleteness.php`: `storeReadyForDispatch(?string $storeId)` — **`?string` وليس `?int`** (Store تستخدم ULID) — يفحص وجود `ShippingProvider::is_active` أو `DeliveryRider::is_active` للمتجر؛ key `carrier_not_configured` يُضاف إلى `missing()` فقط عند `$forSend=true` (الإرسال دون التأكيد).
- ترجمات ×4: `order_flow.carrier_not_configured`.
- الأدلة: 3 اختبارات إضافية في `OrderCompletenessTest` (حجب عند غياب شركة/موصّل، شركة غير نشطة، موصّل نشط يحقق الجاهزية).

### هـ — ملاحظة السعر عند الإرسال (unpriced / zero_cost)
- `app/Domains/Shipping/Services/OrderShippingGateway.php`: `resolveRateNote()` يُرجع `'unpriced'` (لا سعر مُعلن لشركة الطلبية) / `'zero_cost'` (سعر معلن = 0) / `null` (مُسعَّر)؛ يُضاف كـ `$result['rate_note']` بعد `send()`.
- UI (`orders/index.blade.php`): `confirmAndSend`/`sendConfirmedOrder`/`confirmBulkSend` تستعرض `swal:toast` تحذيرية (icon warning) بالمفتاح المناسب عند وجود `rate_note` — إعلام بلا منع.
- ترجمات ×4: `rate_note_unpriced`/`rate_note_zero_cost`/`bulk_send_rate_note`.
- الأدلة: 4 اختبارات إضافية في `OrderCompletenessTest` (unpriced، صفر، مُسعَّر بلا ملاحظة، معلن معطَّل = unpriced).

### و — مزامنة مكاتب الاستلام مجدولة + لحظية
- `app/Domains/Shipping/Jobs/SyncStopdeskOfficesJob.php` (جديد): `chunkById(20)` لكل الشركات carrier-backed النشطة للمتجر، تزامن عبر `StopdeskOfficeSync::sync`، كل شركة معزولة (خطأ واحدة لا تقتل البقية).
- `routes/console.php`: `SyncStopdeskOfficesJob::twiceDaily(3, 15)->withoutOverlapping()`.
- لحظي: `providers.blade.php` `saveProvider` يُرسل الجوب مباشرة بعد حفظ شركة carrier-backed.
- الأدلة: `tests/Feature/Shipping/SyncStopdeskOfficesJobTest.php` — **5 ناجحة (9 assertions)**.

### ز — شارة «لا أسعار بعد» + ترشيح الشركات غير النشطة
- شارة `no_rates_yet` (`edz-badge--warning`) لكل شركة بلا أي صف DeliveryRate في sidebar صفحة الأسعار المعلنة.
- ترشيح الموردين: `allProviders` يفلتر `is_active=true` بنيويًا أصلًا — لا تعديل مطلوب (موثّق فقط).

### المدى الكلي
| | الخط الأساس (قبل العنقود) | الآن | الدلتا |
|---|---|---|---|
| اختبارات | 497 | **513** | +16 (بنود أ–ز) |
| تأكيدات | 1770 | **1810** | +40 |
| انحدار | 0 | **0** (جولة كاملة نظيفة) | — |

> **ملاحظة تنفيذية:** فشل `OrderOfficeSelectionTest` العابر «rename Access is denied on storage/framework/views» هو قفل ويندوز معروف أثناء compile blade — يُعاد التشغيل فقط (اجتاز 18/19 + تِمّ في العزل 8/8). سبب فشل اختبار العزل في `SyncStopdeskOfficesJobTest` السابق: كان قد تُرك الاستجابة الوهمية في الملف بمدخل واحد فقط أثناء جلسة تصحيح — استُعيد المدخلان. وسبقًا كان `OrderInlineFieldEditTest` (513) «badge jumps to carrier editor لطلب confirmed بلا شركة» يفشل لأن مفتاح `carrier_not_configured` الجديد (أول أقفال الإرسال على مستوى المتجر) لم يكن معيّنًا في `startMissingFieldEdit` — أُضيف تعيينه إلى `startOrderProviderEdit` (لا فتح المودال) — ثم اجتاز السويت كاملة.
>
> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — مفتاح الشركة الشامل في صفحة الأسعار المعلنة (enabled/mixed/disabled)، شارة «لا أسعار بعد»، خيارات مكاتب الإرسال بلا نقاط خارج الولاية/البلدية، توست ملاحظة السعر عند إرسال طلبية بلا سعر معلن، وزر مزامنة نقاط الاستلام بعد حفظ شركة carrier.

---

## إعادة تصميم صفحة نقاط الاستلام `/merchant/delivery/stopdesk` (سبتمبر 2026) ✅

**خطة وافق عليها المستخدم صراحةً («موافقة — ننفذ الخطة كاملة») — نُفّذت كاملة بأدلة قابلة للقياس.**

### بنية جديدة (نمط `announced-rates.blade.php`)
- شريط جانبي 260px للشركات + شبكة ولايات بعدد المكاتب (`grid lg:grid-cols-[260px_1fr]`)؛ على الجوال الشريط يتكدس فوق اللوحة مع تمرير أفقي.
- `stopdesk.blade.php` أعيدت كتابته كملف تركيب رفيع (نفس اسم المكوّن `merchant.delivery.stopdesk`) يستدعي 3 partials:
  - `partials/stopdesk-provider-sidebar.blade.php` (أزرار الشركات + carrier subtitle + شارة synced اختيارية + badge count).
  - `partials/stopdesk-state-grid.blade.php` — صفوف ولايات (State/Offices/Actions)؛ تُعرض فقط الولايات التي لديها مكاتب.
  - `partials/stopdesk-offices-popup.blade.php` — نافذة xl بقائمة مكاتب الولاية مع شارة «Syncdesk synced» لكل مكتب (عبر `external_code`) + تعديل/حذف + زر إضافة مسبق التعبئة.
- **زر المزامنة:** داخل لوحة الشركة فقط إذا وُجد `config("delivery.carrier_integrations.{code}")` + `class_exists` (نمط `hasIntegrationAdapter` في `providers.blade.php`) — بلا عمود قدرة جديد. **زر «إضافة مكتب» اليدوي:** داخل لوحة الشركة المختارة فقط مع تعبئة `shipping_provider_id` مسبقًا.
- **ولاية بلا مكاتب مُزامَنة لا تظهر في الشبكة** لشركة ذات تكامل API حتى تُزامن (قرار المستخدم أ) — المزامنة العامة في رأس لوحة الشركة.

### تحسين مُكتشَف أثناء التنفيذ (ربط الولاية/البلدية تلقائيًا)
- `StopdeskOfficeSync::sync($provider)` بلا فلتر ولاية كان يُبقي `state_id=null` لكل المكاتب → لا يمكن عرضها في شبكة الولايات.
- `stateByDeskCode()` جديد: كود مكتب NOEST هو رقم الولاية (مثل '16' أو '34B') و`state_code` مخزن `char(2)` بصفر بادئ → مطابقة عبر `ltrim(state_code,'0') = (string)(int)$deskCode` ثم `resolveCityId(commune, officeState)` — فيُقسم عداد كل ولاية بعد مزامنة كاملة.
- `DeliverySettingsTest` (14/14) يعتمد `openStopdeskModal`/`saveStopdesk`/`deleteStopdesk`/`assertSee(tab_stopdesk)` — المحفوظ بالكامل. `SyncNoestTrackingJob` يستخدم `resolve()` فقط (لا `sync()`) — غير متأثر.
- توافق الاختبارات: أُبقي `syncCandidates`/`selectedSyncProviderId`/`syncStopdesk`/مفتاح `synced`؛ حُذف `stopdeskPoints` المسطّح لصالح `pointsByState` (groupBy `__unassigned__`) و`stateRows`؛ `openStopdeskModal(?string $stopdeskId = null, ?string $defaultStateId = null)` يغلق popup المكاتب عند فتح المودال (منع تكدس الطبقات).
- Fixtures: `sdEnv()` لا تزرع الولايات (المكاتب المُزامَنة تذهب لـ`__unassigned__`)؛ `noestGeography()` تنشئ State16 بالكود '16'.
- ترجمات ×4 (بعد `sync_no_adapter`): `no_providers_yet`/`select_company_hint_offices`/`stopdesk_provider_desc`/`stopdesk_offices`/`stopdesk_manage_offices`/`stopdesk_unassigned`/`stopdesk_no_offices`/`stopdesk_no_offices_desc`.

### الأدلة
- `tests/Feature/Merchant/StopdeskSyncUiTest.php` أُعيدت كتابته (4/4): sidebar يعدّد كل الشركات بينما sync يستهدف carrier-backed فقط؛ شارة synced داخل popup عبر `openOfficesPopup`؛ upsert بعد sync مع فحص `pointsByState.__unassigned__`؛ info بلا credentials.
- `tests/Feature/Merchant/NoestIntegrationTest.php` + اختبار «full sync without a state filter assigns the wilaya and commune from the desk code» (state_id=state16 و city_id=city16 عبر الكود '16').
- Lint نظيف: `StopdeskOfficeSync.php` + ملفات اللغة الأربعة + الاختبارات الثلاثة.

### المدى الكلي
| | الخط الأساس (قبل العنقود) | الآن | الدلتا |
|---|---|---|---|
| اختبارات | 513 | **517** | +4 (إعادة تصميم stopdesk) |
| تأكيدات | 1810 | **1825** | +15 |
| انحدار | 0 | **0** (جولة كاملة 517/517 نظيفة) | — |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — تكدس الشريط الجانبي فوق اللوحة، شبكة الولايات وعداداتها بعد مزامنة كاملة، نافذة مكاتب الولاية + المودال فوقها (قرار أ)، وزر المزامنة/الإضافة داخل لوحة الشركة فقط.

---

## إصلاحات حرجة `/delivery/stopdesk` (سبتمبر 2026) ✅

**خطة وافق عليها المستخدم صراحةً («أوافق — نفذ الخطة» + 3 قرارات: مكاتب الولاية ضمن كل بلدية / إصلاح فقدان النافذة / providers بلا فرع) — نُفّذت P1→P2→P3 بأدلة قابلة للقياس.**

### P1 — مكاتب الولاية ضمن كل بلدية (المرافئ الثلاثة + محرر inline)
- **الشرط:** مكتب بلا بلدية (`city_id IS NULL`) ضمن ولاية المختارة يظل قابلاً للعرض تحت كل بلدية بالولاية، بعد أن كانت `city_id = X` الصارمة تُسقطه (سبب «يتعذر جلب مكاتب لكل بلدية»). مكتب بلدية أخرى من نفس الولاية يبقى مستبعدًا.
- **المواضع:** `rebuildFormOffices` (orders/index) + `inlineStopdeskOptions` (محرر inline طلبية) + `storefront/order-form.blade.php`. الصيغة: `where(fn: city_id = X أو أو city_id IS NULL)` + `orderByRaw('(city_id = ?) DESC, (city_id IS NULL) ASC, name')` — مطابقة البلدية أولًا ثم مكاتب الولاية أبجديًا؛ بدون بلدية → `orderBy('name')` كسابق.
- **اختبارات:** تحديث اختبارَي «الحصر الصارم بالبلدية» (مودال + inline) ليُدخلا المكتب الإقليمي ويُبقيا استبعاد بلدية أخرى؛ جديد ×2 — «المرتبة الافتراضية للبلدية قبل مكاتب الولاية» (نموذج الإنشاء) و«قائمة مكاتب الزائر تدمج بلدية + ولاية بلا عرض بلدية أخرى» (CartOrderLimitsTest).

### P2 — لوحة stopdesk: تجميع البلدية + البقاء داخل النافذة ✅
- **البقاء داخل النافذة:** `openStopdeskModal` لم يعد يغلق popup المكاتب (`stopdesk.blade.php`) — المودال يُعرض فوق النافذة (يشتركان في `--edz-z-modal` و`modal` مُسجَّل بعد popup في الـ DOM)؛ كتلة `saveStopdesk`/`deleteStopdesk` (التي كانت ميتة) أصبحت حية وتبقي المستخدم داخل قائمة الولاية بالقائمة المحدَّثة فورًا.
- **تجميع البلدية:** نافذة مكاتب الولاية تُجمَّع الآن **بالبلدية** (اسم البلدية + عدّاد) مع مجموعة أخيرة «مكاتب الولاية» (بدون بلدية) بشارة `stopdesk_state_wide_hint` — يطابق «مكاتب لكل بلدية» على صفحة الإدارة.
- **ترجمات ×4:** `stopdesk_state_wide` + `stopdesk_state_wide_hint`.
- **أدلة:** StopdeskSyncUiTest +2 («التعديل من النافذة يبقيها مفتوحة ويحدّث قائمتها» + «النافذة تجمّع بالبلدية وتعرض مجموعة الولاية»).

### P3 — providers: إخفاء «فرع شركة التوصيل» ✅
- **شركة بفرع واحد** (ZR Express): `selectProviderPlatform` يختار الفرع الوحيد تلقائيًا ويعبِّئ الاسم والاعتمادات؛ قائمة «فرع شركة التوصيل» تُستبدل بحقل مقروء + تلميح `delivery_company_single_branch` (الفرع مُختار ضمنيًا).
- **شركة بلا فروع**: بدل القائمة تلميح `delivery_company_no_branches` (دفاعي — لا شركة من هذا القبيل حاليًا في الفهرس).
- **شركة بفروع متعددة** (Ecotrack ×3): تبقى قائمة الفروع كما هي دون أي اختيار تلقائي.
- **المجموعة المستقلة `__standalone__`**: خيار «فرع شركة التوصيل» يُعاد تسميته «شركة التوصيل» (اختيار دفاعي — لا ناقل مستقل في الفهرس الحالي).
- **ترجمات:** أُضيف `delivery_company_single_branch` + `delivery_company_no_branches` باللغات الأربع؛ وسُدّت الفجوة القائمة (كانت `delivery_company*`/`select_delivery_company*` معرَّفة بالعربية فقط، فكانت en/fr/es تعرض مفتاحًا خامًا — أُضيفت لكل الملفات).
- **أدلة:** DeliverySettingsTest +2 (الفرع الوحيد يُختار تلقائيًا وتُخفى القائمة؛ الفروع المتعددة تُبقي القائمة بلا اختيار) + إعادة كتابة اختبار «two-level» على Ecotrack بعد تعطُّل مسار ZR التلقائي.

### المدى الكلي الكامل (P1+P2+P3)
| | الخط الأساس | الآن | الدلتا |
|---|---|---|---|
| اختبارات | 517 | **523** | +6 |
| تأكيدات | 1825 | **1850** | +25 |
- التفصيل: P1 (+2/+10 → 519/1835)؛ P2 (+2 — StopdeskSyncUiTest 4→6)؛ P3 (+2 — DeliverySettingsTest 14→16 + إعادة كتابة 1). التحقق من P2+P3: السويت **523 ناجح (1850 assertions)** — صفر انحدار؛ Pint PASS على DeliverySettingsTest؛ `php -l` سليم على كل نص تعدَّل؛ `view:cache`+`view:clear` سليمان.

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — «مكاتب الولاية» تظهر تحت كل بلدية في نموذج الطلب والمتجر، وتلوين حقول المكاتب في المحرر السريع/inline بعد التوسعة.

---

## Phase 34 — نموذج طلب المتجر: سلسلة الشحن (الشركة → نوع التوصيل → الولاية → البلدية → المكتب) بمنسدلة بحثية (سبتمبر 2026) ✅

**خطة وافق عليها المستخدم صراحةً قبل التنفيذ بقرارات مثبّتة بأسئلة (شركة مفقودة → تلميح بدل إعادة تحديد / منتدى المنزل = commue عبر `DeliveryRate` مع تراجع لقائمة الأسعار الشاملة / متجر بلا شركات → التسلسل الكامل بـ legacy / مكتب بلا سعر منشور → مجاني). العنوان: «desk — commune».**

- **مكوّن تحديد جديد خاص بالمتجر** `resources/views/components/storefront/select.blade.php` + `resources/js/components/storefront-select.js` (مقاوَم خفيف مشترك مع قاعدة المتجر `store-primary` — **ليس** `x-edz.select` التجاري)، مسجّل في `resources/js/storefront.js` (حارس `window.Alpine` غيره `alpine:init`). يرفع قائمة الخيارات JSON ويبثّ اختياره عبر حقل مخفي `x-model` + أحداث `change`/`livewire-change` على الجذر.
- **إعادة كتابة `resources/views/livewire/storefront/order-form.blade.php`** — منطق السلسلة كله (الشركة → نوع التوصيل → الولاية → البلدية → المكتب) يُبنى على الطيف المُلاحظ للمتجر الحالي مع **بدون استعلامات زائدة خلال mounting/render**:
  - `mount()` يحدد الشركة تلقائيًا عند `availableProviders->count() === 1`; `updated(['city_id'])` يحدد المكتب الوحيد تلقائيًا (المصدر القاطع — أُزيل الطفرة في `@php`).
  - `submitOrder` يتحقق من نطاق المكتب/الشركة (تأكيد **خادمي** يرفض مكتبًا خارج الشركة/الولاية/البلدية المختارة) + `Rule::exists` محصور بشركة الطلب، و`stopdesk` بدون مكتب → `edz-notice` + عودة مبكرة. سعر المكتب: `office_cost` عند توافره مع تراجع فوري المتجر، وإلا مجاني (قرار المستخدم).
  - سطر ملخص الشحن يعرض **اسم الشركة** عند توافره (`provider_name`)؛ إصلاح `$providers->firstWhere(...)?->flat_rate` وحراسة `$variants` الفارغة.
- **ترجمات ×4 (en/ar/fr/es) — 10 مفاتيح جديدة فقط:** `shipping_via`/`company`/`select_company`/`select_company_first`/`search_company`/`search`/`no_options_found`/`delivery_office`/`deliver_to_this_office`/`select_office_required` (مكانيّا البحث للولاية/البلدية/المكتب تستعمل `storefront.search` المشترك — لم تُضف `search_state/city/office`).
- **اختبارات — `tests/Feature/Storefront/StorefrontOrderShippingCascadeTest.php` (8 ناجحة / 43 assertions):** متجر شركة واحدة يخفي البيكر وينشر الشركة على الطلب، المطابقة الوحيدة تختار المكتب تلقائيًا كبطاقة، اختيار الشركة يحدد نطاق ولايات المكتب، بلديات النطاق فقط تُعرض، stopdesk بلا سعر منشور مجاني، منزلي يفرض `home_cost` ويحفظ العنوان الخام، مكتب خارج الشركة المختارة يُرفض بلا طلب، ومتجر legacy بلا شركات يحافظ على السلسلة كاملة.
- **تحديث اختبارين قائمين:** `CartOrderLimitsTest` → «desk choice is required and the list is scoped to the commune with carrier labels» (مكتب إلزامي `edz-notice` وبلا طلب)؛ `OrderCancellationRestockTest::placeStopdeskOrder()` ينشئ `StopdeskPoint` ويحدد `selectedStopdesk`.
- **التحقق النهائي:** `php -l` + `php artisan view:cache` (سليمة) + `npm run build` (vite 7.3.0 — تحذيرات Sass فقط، لا أخطاء) + **الجولة الكاملة النظيفة: 536 ناجح (1899 assertions)** — قبلها 523/1850 (+8 اختبار / +25 تأكيد للسلسلة + توسعة) — **صفر انحدار**. ملاحظة manual: طفرة `updated` لا تظهر في `assertSet` بنسخة Livewire — الاختبارات تضبط `selectedStopdesk` صراحةً قبل `submitOrder` (التأكيدات على HTML تثبت أن البطاقة المختارة تُرسم فعليًا).

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 34 سلسلة شحن المتجر | ✅ | storefront/order-form.blade.php + components/storefront/select.blade.php (جديد) + resources/js/components/storefront-select.js + resources/js/storefront.js + storefront.php ×4 + StorefrontOrderShippingCascadeTest (جديد) + CartOrderLimitsTest + OrderCancellationRestockTest | 8 في StorefrontOrderShippingCascadeTest (43) + 536 ناجح (1899) |
| **الإجمالي (جولة نظيفة)** | **536 ناجح (1899 assertions)** | | |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — المنسدلات البحثية للشركة/الولاية/البلدية/المكتب (خفيفة، بحدود `store-primary`، وموبايل ورقة سفلية)، التحديد التلقائي للمكتب الوحيد كبطاقة، سطر «عبر شركة» في ملخص الشحن، اختيار مكتب خارج الشركة يُرفض بتوست، وبيوت المتجر الحالي (`store.edzeery.test`/`store.noest.test`).

---

## Phase 34 — إصلاح ما بعد التحقق: عناوين القوائم «[object Object]» في صفحة checkout + ترميز مضاعف لـ data-options (سبتمبر 2026) ✅

**أبلغ المستخدم أن فقرة الشركة والولايات في صفحة checkout تُرسم عناوينها «[object Object]». السبب الجذري (تحقيق معمق):**

- **السبب (1) — الخاصية الجذرية:** `components/storefront/select.blade.php` كان يحسب الخيارات عبر `is_array($item)` فقط؛ لكن `order-form.blade.php` يمرر **مجموعات Eloquent** (`$providers`/`$states`/`$cities` = نماذج) مباشرة إلى `:options="$providers"` إلخ، فلم يطابقها الشرط وذهب كل نموذج للفرع الخاطئ: `label = النموذج كاملًا` → `x-text` يرسّم الكائن «[object Object]» (كانت الاختبارات السابقة تمر لأن `@js` يتضمن JSON النموذج فتظهر الأسماء خامًا في HTML رغم فساد العرض). **الإصلاح في المكوّن:** التطبيع عبر `is_array($item) || is_object($item)` + قراءة القيمة/العنوان/التلميح بـ`data_get` (يعمل على النماذج و stdClass والمصفوفات) مع تنصير قسري `(string)` وfallbacks آمنة (`$key`).
- **السبب (2) — ترميز مضاعف لـ `data-options`:** كان السطر `data-options="{{ $optionsAttr }}"` حيث `$optionsAttr` مُرمَّز أصلًا بـ`htmlspecialchars(ENT_QUOTES)` — فكان `{{ }}` (الذي يطبّق `e()` مرة ثانية) يحوله `&quot;` → `&amp;quot;`، فكان `JSON.parse` لفشل قراءة سمة DOM (كانت تعتمد فعليًا على `@js` داخل `x-data` وحده). **الإصلاح:** `data-options="{!! $optionsAttr !!}"` (الترميز الآمن مرة واحدة فقط) فتقرأ `_syncFromServer` JSON صالحًا وتتزامن مع إعادة التوليد.
- **اختبار الحاجز — `StorefrontOrderShippingCascadeTest` (+1/+5):** «carrier and wilaya option labels render as plain strings, never "[object Object]"» — يثبت أن الحمولة تحمل `&quot;label&quot;:&quot;…&quot;` للشركات والولايات (بعد إصلاح الترميز) ولا تحمل `&quot;label&quot;:{` ولا `[object Object]` (الاختبار فحص عبر `data-options` المرمَّزة لأن `@js` يهرب علامات الاقتباس بـ`\u0022` — جرّب أولًا الصيغتين ثم ثبت الصحيح).
- **التحقق النهائي:** `view:clear`+`view:cache` سليمة + الجولة الكاملة النظيفة **537 ناجح (1904 assertions)** — قبلها 536/1899 (+1 اختبار +5 تأكيدات) — صفر انحدار.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 34 إصلاح عناوين القوائم | ✅ | resources/views/components/storefront/select.blade.php + tests/Feature/Storefront/StorefrontOrderShippingCascadeTest.php (+1) | 9 في StorefrontOrderShippingCascadeTest (48) + 537 ناجح (1904) |
| **الإجمالي (جولة نظيفة)** | **537 ناجح (1904 assertions)** | | |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — بعد هذا الإصلاح تُعرض أسماء الشركات والولايات حاليًا في القوائم، ومكتب-checkout يُجرّب الآن على المتجر المباشر.

---

## Phase 35 — ترقيم الولايات عبر المشروع + مكاتب الاستلام بالمتجر تشمل الولاية كاملة (سبتمبر 2026) ✅

**طلب المستخدم:** (أ) قوائم كل الولايات في المشروع تُعرض الرقم قبل الاسم كشارة («01 Adrar»)، قابلة للبحث بالرقم، ومرتبة برقم الولاية؛ (ب) في checkout المتجر تُعرض **كل مكاتب الولاية المختارة عبر كل البلديات** (لا تقتصر على البلدية)، وتُخفى بلدية الاستلام (الكود = البلدية تُشتق من المكتب)، وقائمة المكاتب تُظهر التفاصيل (الشارة + الاسم + البلدية + العنوان + الهاتف) ومرتبة بكود المكتب؛ (ج) جانب التاجر (المقرات inline/form) يعرض كود المكتب أيضًا. **القرارات:** كامل الخطة + إخفاء البلدية للاستلام + عرض المخزون المتوفر فقط (بدون تغيير schema). **سلوك التاجر بقي نطاقه بالبلدية كما هو** — تغيّر المتجر فقط.

- **أ. ترقيم الولايات:** `app/Models/Locations/State.php` أُضيف `scopeOrderedByCode()` (ترتيب مباشر بـ `state_code` — `state_code` نص char(2) مبطن بالأصفار فالترتيب النصي = الرقمي، بلا أي دوال DB خاصة). المكوّنان `components/storefront/select.blade.php` + `components/edz/select.blade.php` اكتسبا props `option-code`/`option-extra` (شارات كود في المشغّل والخيار، سطور extra للتفاصيل) و JS `currentCode` + بحث يشمل الكود (`storefront-select.js`/`edz-select.js`). شارة موحّدة `.edz-code-badge` في `_badges.scss`. طبّق على: `storefront/order-form` (اسنام states الأربعة + `option-code="state_code"`)، `merchant/orders/index` (`allStates` + `formAvailableStates` بالأكواد، القوائم الأصلية فلاتر/محررinline والـ badge في عرض الفلتر)، `orders-table-cell` (محرر الولاية الأصلي)، `order-form-modal` + `delivery-edit-modal` (selects الولاية)، `teams/index` (`states` المحسوبة تتضمن `state_code` و `option-code`)، delivery `announced-rates`/`stopdesk` (شارة الرقم في جداول الأسعار وشبكة المكاتب + select الولاية).
- **ب. إعادة تصميم مكتب الاستلام بالمتجر (`storefront/order-form.blade.php`):**
  - `officesForSelection()` — أُسقطت بوابة `city_id` (كل مكاتب الولاية تُعرض) + ترتيب رقميّ بالكود الخارجي (فارغًا أخيرًا، مع مجموعات NOEST مثل 02A/02B متجاورة عبر `orderBy('external_code')`).
  - `updated(['state_id'])` — يمسح المكتب فقط إن لم يكن مكتبًا لنفس الولاية، ويختار تلقائيًا المكتب الوحيد للولاية مع اشتقاق بلديتها. `updated(['selectedStopdesk'])` — يشتق `state_id`/`city_id` من المكتب نفسه.
  - الواجهة: **بلدية الاستلام مخفية** للمتجر (`@if ($this->delivery_type === 'home')`)، بطاقة المكتب الوحيد تعرض شارة الكود، قائمة المكاتب تمر `option-code`/`option-extra` (address+phone)، رسالة «لا مكاتب» أصبحت `no_desks_in_state` (مفتاح en أُضيف، ar/fr/es موجودة).
  - التحقق: `city_id` مطلوب فقط للتوصيل للمنزل؛ فحص نطاق المكتب أسقط شرط البلدية (المكتب نفسه هو المرجع) — يبقى شرط المتجر/الولاية/الشركة.
- **ج. جانب التاجر:** `formOffices` + `inlineStopdeskOptions` يتضمنان `code` من `external_code`، و`option-code="code"` في قوائم المكتب (order-form-modal، delivery-edit-modal، orders-table-cell، orders-mobile-fields) — بقي النطاق البلدي للتاجر كما هو.
- **اختبارات:** `StorefrontOrderShippingCascadeTest` — استُبدل «only offices inside the selected commune» بـ 3 اختبارات: «every office of the selected wilaya… ordered by desk code, with details» (تحقق 02A قبل 02B + address/phone + غياب `role="city-select"`)، «an office outside any chosen commune completes the stopdesk order with its own commune» (بدون بلدية إطلاقًا)، «wilaya options carry a numeric-code badge and order by wilaya number» (state_codes 01/02). `CartOrderLimitsTest` — «stopdesk without a state» يتحقق من خطأ الولاية فقط (لا بلدية)، وأُعيدت كتابة اختبارَي النطاق البلدي إلى نطاق الولاية الكامل.
- **التحقق النهائي:** `view:clear`+`view:cache` + `npm run build` (vite 7.3.0، تحذيرات chunk سابقة فقط) + الجولة الكاملة النظيفة **539 ناجح (1918 assertions)** — قبلها 537/1904 (+2 اختبارات +14 تأكيد) — صفر انحدار.
- **إصلاح أخطاء السجل (09-09):** ① `storage/logs/laravel.log` أبلغ 3 حوادث: مرّتان `SQLSTATE[42000] 1582 Incorrect parameter count in the call to native function 'ltrim'` — كان `ltrim(state_code, '0')` بوسمين غير صالح في MySQL (عندما كانت الروابط `CAST(ltrim(...))` في `scopeOrderedByCode` + ما يعادلها في `officesForSelection`)، + مرة `NOEST trackings/info HTTP 503 Upstream down` (انقطاع خارجي تُصرفه الجولة بصبر — اختبار الحقن المتعمد). ② **الجذر:** لُوّنت السويت على SQLite (يقبل `ltrim` بوسمين) بينما التطبيق الحي على MySQL — فاختفى العيب. ③ **الإصلاح بلا ترقيع:** الترتيب مباشرة بالعمود (`orderBy('state_code')` / `orderBy('external_code')`) لأن الأكواد char(2) مبطّنة بالأصفار فالترتيب النصي == الرقمي (يحافظ على 01..58 و مجموعات 02A/02B)، و`StopdeskOfficeSync::stateByDeskCode()` يطابق `state_code` بالحشو `str_pad(...,2,'0',STR_PAD_LEFT)` بدل `whereRaw` — كلها بلا دوال خاصة بالـ vendor. ④ **تحقّق على MySQL الحي مباشرة** (states → 01..08، desks → 10A/10B/11A/12A/12B/13A) + الجولة الكاملة 539/1918 نظيفة، ولا أخطاء جديدة في السجل.
- **تحسين أداء قوائم الاختيار + السبينر + منع الضغط المتكرر (09-09):** ① **المشكلة:** كل render يعيد تضمين قائمة الخيارات الكاملة (`data-options`) في صفحة checkout (مكاتب كل الولاية + كل البلديات) فتتضخم الاستجابة وتُبطئ المورف، بلا مؤشر تحميل ولا مانع ضغط متكرر. ② **البنية العامة (تسري الكُلي على كل select):** المكوّنان `storefront/select` + `edz/select` اكتسبا props `lazy`/`source`/`scope` + كشف `$roundtrip` (آي `wire:model.live*`/`wire:change` أو lazy)، مع data-attrs. JS في `storefront-select.js` + `edz-select.js`: نمط `loading` (سبينر يبدّل الشيفرون + صف تحميل باللوحة)، **منع الفتح أثناء التحميل مع السماح بالإغلاق دائمًا** (toggle)، **بوابة round-trip** على الاختيار: `_waitServerAck` لقفل المشغّل حتى يعترف الخادم عبر `$wire.$watch` في `_bindServerValue.read` (سقف 6 ثوانٍ)، و`applyOptions` يتجاهل المورفات (seed) حين تكون القائمة البعيدة جاهزة للنطاق الحالي. ③ **التحميل الكسول كأفضل نهج:** في checkout فقط، قائمتا **البلديات** (city، `$citiesSelectOptions`) و**المكاتب** (office، `$stopdeskSelectOptions`) تصبحان `lazy source="..." :scope="..."` — لا تُضمّن في الصفحة إلا «seed» (الاختيار الحالي فقط)، وتُجلب عند أول فتح لكل scope (composite `s{state}|p{provider}` / `s{state}|dt{type}`) وتُخزّن في كاش أمامي (حد 12 scope، LRU). أفعال Volt جديدة: `$formatOfficeOptions`/`$citiesForSelection` (استُخرج من render بنفس المنطق بالضبط — stopdesk hasGlobalOffice/officeCityIds، home DeliveryRateCity+ShippingRate) + `$stopdeskSelectOptions`/`$citiesSelectOptions`. الولايات/الشركات بقيت مضمّنة (رخيصة). ④ **اختبارات:** استُبدل فحص HTML المضمّن بفحص حمولة الفعل عبر `$component->instance()->stopdeskSelectOptions("s..|p..")` (الترتيب 02A قبل 02B، التفاصيل، كل مكاتب الولاية مع/بدون شركة) + التأكد أن SSR يحمل `data-lazy="1"`/`data-source` بلا أسماء المكاتب. ⑤ **css:** `animate-spin` تولد تلقائيًا (Tailwind يمسح `resources/views/**/*.blade.php`) — وتحقّق من الحزم (`storefront-*.js`, `panel-*.js` يحملان `ensureRemoteOptions`). ⑥ **الجولة الكاملة بعد التغيير: 539 ناجح (1936 assertions — +18 تأكيدًا)**.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| 35 ترقيم الولايات عبر المشروع | ✅ | State.php (scope) + components/storefront/select + components/edz/select + edz-select.js + _badges.scss + storefront/order-form + merchant/orders/index + orders-table-cell + order-form-modal + delivery-edit-modal + teams/index + delivery/announced-rates + delivery/stopdesk + stopdesk-state-grid | — |
| 35 مكاتب الولاية + اشتقاق البلدية (المتجر) | ✅ | storefront/order-form.blade.php + storefront.php (en: no_desks_in_state) | — |
| 35 كود المكتب بالتاجر | ✅ | merchant/orders/index (formOffices + inlineStopdeskOptions) + قوائم المكتب ×4 | — |
| اختبارات فرعية | ✅ | StorefrontOrderShippingCascadeTest (11/63) + CartOrderLimitsTest (12/66) | 11 + 12 اختبارًا (63+66) |
| **الإجمالي (جولة نظيفة)** | **539 ناجح (1936 assertions)** | | |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — شارات أرقام الولايات في القوائم (search بالرقم + ترتيب)، إخفاء بلدية الاستلام واشتقاقها من المكتب، قائمة مكاتب بأسطر العنوان/الهاتف وشارة الكود، التحديد التلقائي لمكتب الولاية الوحيد، وأداء `updated(['selectedStopdesk'])` الحي على المتجر المباشر (`store.edzeery.test`/`store.noest.test`).

---

## إصلاح بوب اب اختيار المنتج/المتغيرات + مزامنة التحديد + شارة «كل المتغيرات» + أداء المحرر (سبتمبر 2026) ✅

**بموافقة المستخدم الصريحة («نفّذ الخطة كاملة») بعد فحص شامل للكود الحي. النطاق: تغيير/إضافة منتج، كمية، سعر من الجدول مباشرة.**

### التشخيص المعتمد بالأدلة (الجذور)
- **Bug1 — إغلاق بوب اب العناصر عند إغلاق بوب اب المنتج/المتغير:** حاوية نوافذ العناصر (`partials/orders-items-edit-modals.blade.php:16,86,141`) كانت تستمع على مستوى window عبر `@edz-modal-closed.window="$wire.closeItemsModal()"`؛ وأي `x-edz.modal` عند انقلاب `open` يطلق `edz-modal-closed` (`components/edz/modal.blade.php:16-23`). الـ picker الشقيق (`orders-product-picker.blade.php`، يُضمّن في `index.blade.php:6145`) عند اختيار/إغلاق أي متغير → حدث يتفقّع للـ window → يقفل نافذة العناصر. (ذات العلة تجعل أي مودال آخر يُغلق أثناء الجلسة يقفلها كذلك — تسريب نطاق.)
- **Bug2 — المنتج يبقى «محددًا» حتى تحديث الصفحة:** `formSelectedItems` خريطة `variant_id => qty` تُبنى فقط عبر `syncFormSelectedItems()` (HasOrderProductPicker:314)؛ لكن دورة الحياة غير متناظرة: `closeItemsModal` يصفّر `form['items']` دون المزامنة، و`openCreateModal`/`openEditModal` يعيدان بناء الـ draft دون مزامنة → تنتقل الخريطة العالقة عبر الطلبيات/النوافذ وتصدّر check/تعطيلًا في الـ picker (orders-product-picker.blade.php:~99/229) حتى يصفّرها `mount` فقط (index.blade.php:143).

### ما تَمّ
- **أ (Bug1):** الحاويات الثلاث صارت `@edz-modal-closed="$wire.closeItemsModal()"` (بدون `.window`) — الحدث من نافذة العناصر نفسها يتفقّع إليها، ولا تمر أحداث الـ picker الشقيق عبرها؛ بقي إغلاق X/الخلفية/Escape مشتركًا بنفس المسار.
- **ب (Bug2):** توحيد دورة حياة الخريطة — `closeItemsModal`/`openCreateModal`/`openEditModal` (index.blade.php) تستدعي `syncFormSelectedItems()` بعد بناء/تصفير الـ draft (كان `openItemsModal` يستدعيها أصلًا).
- **ج (الميزة):** `pickerProductResults` حمّلت `variant_ids` (مصفوفة ULIDs من علاقة محمّلة أصلًا — بلا استعلام إضافي)؛ صف المنتج متعدد المتغيرات يعرض: شارة check «أُضيفت كل المتغيرات» عند اكتمال كل متغيراته، عدّاد «x/y بالسلة» عند الجزئي، والشيفرون كسابق؛ يظل السطر قابلاً للفتح. مفتاح ترجمة جديد `merchant_panel.all_variants_added` ×4 لغات (en/ar/fr/es).
- **د (الأداء):** `updateFormItemQty` لم تعد تنفذ `ProductVariant::find` لكل نقرة +/− — preorder يُشتق من `stock` المخزّن في سطر الـ draft (`allowBackorder(currentStore())`)؛ و`updated('form.items'|'form.customer_phone')` صارت محصورة بسياق الإنشاء/التعديل (`showCreateModal || showEditModal`) — محرر العناصر لا يعرض تحذيرات استعباد أصلًا.

### الاختبارات — `OrderInlineItemsEditTest` (14→18 / 89 تأكيدًا)
- ① نوافذ العناصر تستمع لـ close الخاصّ بها فقط (`assertDontSeeHtml('edz-modal-closed.window')` + `assertSeeHtml('edz-modal-closed="…")`).
- ② خريطة التحديد تتصفّر مع إغلاق العناصر وتُبنى من طلبية كل edit/إنشاء (جولة A→create→B بلا بقايا).
- ③ شارة «كل المتغيرات» تظهر فقط بعد إضافة كل المتغيرات (متغير واحد → لا تظهر).
- ④ المحرر يشتق preorder من الـ draft بلا استعلام `product_variants` (حصيلة `DB::listen` خلال open+stepper صفر استعلامات على `product_variants`).
- دالة `itemsOrder` المساعدة قبِلت معامل `$phone` اختياريًا لتجنب تضارب unique للزبون (بلا أثر على بقية الاستدعاءات).

### الدليل النهائي
- `php -l` على كل نص تعدَّل + `php artisan view:cache` (success) + الجولة الكاملة `tests\Feature\Order tests\Feature\Merchant` = **345 ناجح (1322 assertions) — صفر انحدار**.

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — اختيار منتج/متغير من الـ picker ثم إغلاقه يبقي نافذة تعديل العناصر مفتوحة؛ إضافة منتج ثم فتح طلبية أخرى/إنشاء لا يُظهر بقايا «محدد» (بلا تحديث للصفحة)؛ شارة «أُضيفت كل المتغيرات» في بوب اب المنتجات عند اكتمال متغيرات منتج (مع شارة الجزيء «x/y بالسلة»)، وإبقاء فتح قائمة المتغيرات ممكنًا.

---

## إعادة تصميم صفحة التتبع — المرحلة A: بنية الصفحة (تبويبان + جزءات) (سبتمبر 2026) 🔄

**طلب المستخدم:** صفحة تتبع احترافية متجاوبة (375/768/1440) بلا حشر في `index`، بحث/فلترة كنمط صفحة الطلبيات، تحديث حالة تلقائي (لا يدوي)، تبويب «رجل التوصيل» + بوابة دخول ولوحة خاصة به، ومراجعة صلاحيات. **القرارات المعتمدة (بعد 4 أسئلة):** التنفيذ بالترتيب A→B→C→D→E→F→G؛ التبويبان في نفس الصفحة؛ تسجيل دخول الرجل بكلمة مرور فوق `delivery_riders` (guard منفصل)؛ تأجيل Phase 32 (32.2→32.8) بعد G.

- **A — البنية والتبويبان:** `index.blade.php` صار منظمًا — state جديد `trackingTab` ('carrier'|'rider') + تضمين جزئيات؛ استُخرجت كتل الإحصائيات/شريط الأدوات/القائمة (جدول الديسكتوب + بطاقات الموبايل + load-more) نهائيًا من الصفحة، مع «@if carrier → إحصائيات+أدوات+قائمة» / «@else → placeholder الرجل» (حتى المرحلة D).
- **جزئيات جديدة:** `partials/tracking-tabs.blade.php` (سطر تبويبين بنمط returns `edz-btn--primary/ghost` عبر `wire:click="$set('trackingTab', …)"`)، `tracking-stats`، `tracking-toolbar`، `tracking-list`، `tracking-rider-placeholder`.
- **الترجمة:** مفاتيح ×4 لكل لغة (ar/en/fr/es): `tracking_tab_carrier` + `tracking_tab_rider` + `rider_tab_empty_title` + `rider_tab_empty_hint`.
- **إصلاح عيب سابق كسر الفرنسية كليًا:** `resources/lang/fr/order_flow.php:30` — `'Veuillez choisir l'agence stopdesk'` (اقتباس مستقيم) → `l’agence` (باقتباس مطبعي كما هو متبع في الملف). `php -l` سليم الآن لكل الملفات الأربعة.
- **التحقق:** `view:cache` success + الجولة المرجعية للتتبع **30 ناجح (123 تأكيد)** — منها تحميل `merchant.tracking.index` عبر Volt في `OrderEventLogVisibilityTest` (درجان يصلان/لا يصلان للحدث حسب الدور) + `OrderTrackingCarrierValidationTest` (32.1) — صفر انحدار.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| A بنية الصفحة (تبويبان + جزءات) | ✅ | merchant/tracking/index.blade.php (state + includes) + جزئيات ×5 + order_flow.php ×4 (7 مفاتيح) + إصلاح fr:30 | الجولة المرجعية 30 ناجح (123 تأكيد) |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px / 768px / 1440px — سطر التبويبين أعلى الصفحة، تبديل المحتوى بين التبويبين، وبقاء التبويب الأول بنفس السلوك الوظيفي السابق تمامًا.

---

## إعادة تصميم صفحة التتبع — المرحلة B: بحث حر + فلاتر احترافية (سبتمبر 2026) ✅

**النطاق:** بحث حر debounce 600ms + popover فلاتر (شركة/حالة متعددة/تاريخ) + شريط فلاتر نشطة + عدّادها + total المصفّى — بنمط `merchant/orders/index` حرفيًا.

- **اكتشاف عيب قائم:** الفلاتر السابقة كانت `wire:model.live` بلا أي `updated` hook ولا استدعاء `loadShipments` — أي **تغيير فلتر لا يعيد جلب القائمة** (تُحدَّث فقط عند refresh يدوي). المرحلة B أصلحت هذا بتفعيل الـ reload الحي.
- **`tracking/index.blade.php`:** state جديد `search` + `filteredTotal`؛ `filters.tracking_status` (مفرد) → `tracking_statuses` (مصفوفة متعددة)؛ `loadShipments` يحوّل البحث إلى where-group (number/customer name/phone/phone_secondary/tracking_number/provider name) + `whereIn` للحالات + يسجّل `filteredTotal = total()`؛ استُبدل `resetFilters` بـ `clearFilters` + `setFilter` + `toggleTrackingStatus` + hook `updated` (استورد `Livewire\Volt\updated`).
- **`partials/tracking-toolbar.blade.php`** أُعيد كتابته بالكامل: بحث موحّد (أيقونة + × + سهم)، popover `x-edz.dropdown` بثلاثة أقسام (شركة radio، حالة toggle-check، تاريخ flatpickr)، عدّاد فلاتر نشطة، وشريط فلاتر نشطة (chips بعلامة × لكل فلتر) + زر `clearFilters` — كلها بنمط orders (`.edz-dropdown__item`، `.edz-dropdown__section`، chips `bg-accent-surface`).
- **الترجمة:** `search_tracking_placeholder` + `tracking_count` ×4 لغات.
- **إصلاح جزئي:** `merchant/tracking/index.blade.php` استورد `use function Livewire\Volt\updated;` (نسيته أول مرة — `Call to undefined function updated()` ظهر في الاختبارات فأُصلح فورًا).
- **التحقق:** `view:cache` success + اختبار جديد `tests/Feature/Merchant/TrackingSearchFilterTest` (6 اختبارات/24 تأكيد): تبويباهما، placeholder الرجل، بحث بالرقم التتبع مع استرجاع، فلتر الحالات المتعددة (toggle/دمج)، فلتر الشركة مع filteredTotal، clearFilters — plus الجولة المرجعية 25 ناجح (86 تأكيد) بلا انحدار.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| B بحث + فلاتر احترافية | ✅ | tracking/index.blade.php (search/filteredTotal/setFilter/toggle/updated) + partials/tracking-toolbar.blade.php + استيراد updated + order_flow.php ×4 (+2 مفاتيح) + TrackingSearchFilterTest (جديد) | 6 (24) جديد + الجولة 25 (86) نظيفة |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** 375/768/1440 — البحث بالرقم/الاسم/رقم التتبع أثناء الكتابة، popover الفلاتر (شركة/حالات متعددة/تاريخ) على الموبايل كـ bottom-sheet، شريط الفلاتر النشطة وزر المسح، وعدّاد `filteredTotal`.

---

## إعادة تصميم صفحة التتبع — المرحلة C: تحديث الحالة تلقائيًا فقط (لا إجراءات يدوية) (سبتمبر 2026) ✅

**النطاق:** حذف «الإجراءات السريعة» اليدوية (in_transit/out_for_delivery/failed_attempt/returning/delivered/returned/lost/damaged) من درج الشحنة وكل سطلها (closures)، وإحلالها بمزامنة تلقائية من شركة الشحن مع زر «تحديث الآن» لكل شحنة يمر عبر نفس مؤثر الكتابة الوحيد الذي يستخدمه الجدولة.

- **`app/Domains/Shipping/Services/NoestTrackingSyncService.php` (جديد):** مصدر حقيقة واحد — `apply(OrderTracking, entry)` (منقول حرفيًا من `SyncNoestTrackingJob` مع إبقائه idempotent: تاريخ يُسجَّل فقط عند تغيّر الحالة، ولا تراجع عن حالة نهائية) + `syncOne(OrderTracking)` لعملية تحديث شحنة مفردة عبر `StopdeskOfficeSync::resolve` ثم `trackingsInfo([رقم واحد])`، مع أخطاء مقننة (`no_provider/no_number/unsupported_carrier/request_failed/no_data`). `status===null` (أحداث غير مُعيّنة) الآن يختم `last_synced_at` — بما يوازي `touchSyncedAt` في المسار الدفعي.
- **`SyncNoestTrackingJob`:** أُعيدت هيكلته ليستخدم `NoestTrackingSyncService::apply` (حُذفت `apply/orderedEvents/eventDate` الخاصة) — أي أن الجدولة وزر المتجر يكتبان بالضبط نفس القاعدة.
- **`tracking/index.blade.php`:** حُذفت `trackingAction` + `trackingTransition` + closure `$membership` (كلها أصبحت ميتة)؛ أُضيف `$syncTracking(trackingId)` محروس ORDER_VIEW: يستدعي `syncOne`، toast نجاح/تحذير (رسالة عامة مع `Log::warning` للتفاصيل)، ثم `loadShipments()` وإعادة `openDrawer` لتحديث درج الشحنة فورًا.
- **`partials/order-drawer.blade.php`:** حُذف قسم «Quick actions» بالكامل؛ أُضيف قسم «تحديث الحالة تلقائيًا» (يظهر فقط عند وجود رقم تتبع): «آخر تزامن» (`last_synced_at` بتاريخه أو «لم تتم المزامنة بعد») + زر `syncTracking` مع spinner/loading، وسطر توضيح أن الحالة تُحدَّث تلقائيًا من الشركة.
- **الترجمة:** 8 مفاتيح ×4 لغات (`tracking_sync_section/now/last_synced/never_synced/synced/hint/failed/no_number`).
- **التحقق:** `php -l` نظيف ×4 + `view:cache` success + **39 ناجح (146 تأكيد)** — منها `NoestTrackingSyncServiceTest` (جديد، 6) وأضيف اختباران لصفحة التتبع (زر التحديث يجلب الحالة ويحدّث الدرج؛ وعدم ظهور القسم بلا رقم تتبع) وبقي `NoestTrackingSyncTest` (6) صالحًا بلا تغيير.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| C تحديث تلقائي (لا يدوي) | ✅ | NoestTrackingSyncService (جديد) + SyncNoestTrackingJob (يعيد توجيه apply) + tracking/index (syncTracking/حذف الإجراءات) + order-drawer (قسم المزامنة) + order_flow.php ×4 (+8 مفاتيح) + NoestTrackingSyncServiceTest (جديد) | 6 جديد بالخدمة + 2 على الصفحة + الجولة المرجعية 39 (146) نظيفة |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** 375/768/1440 — قسم «تحديث الحالة تلقائيًا» داخل درج الشحنة (زر + «آخر تزامن»)، غياب أي أزرار حالة يدوية، وتحدّث الدرج فورًا دون إغلاق.

---

## إعادة تصميم صفحة التتبع — المرحلة D: تبويب «رجل التوصيل» (جانب المتجر) (سبتمبر 2026) ✅

**النطاق:** استبدال placeholder المرحلة A بقائمة حقيقية لرجال التوصيل مع عدّادات شحنات كل رجل + توسعة لعرض شحنات الرجل + تعيين/إلغاء تعيين الرجل من درج الشحنة (مع كتلة تعارض مع الشحن عبر شركة).

- **`tracking/index.blade.php`:** state جديد (`allRiders`, `riderRiders`, `selectedRiderId`, `riderShipments`, `riderShipmentTotal`)؛ `mount` يستدعي `loadRiderOverview()` (أيضًا يملأ `allRiders` لقائمة التعيين في الدرج)؛ `updated.trackingTab` يحمّل العرض عند دخول تبويب الرجل؛ `loadRiderOverview()` — نافذة عبر `OrderWorkflow::carrier()` + `whereNotNull('delivery_rider_id')` + `latestTracking` لعدّادتَي `total/active` لكل رجل في استعلامين فقط (لا N+1)؛ `toggleRider()` / `loadRiderShipments()` — شحنات رجل واحد مع حمل مسبق (`customer/status/latestTracking/deliveryRider/city/state`) بنفس شكل صفوف قائمة الشحنات؛ `assignRider(orderId, riderId|null)` محروس `ORDER_ASSIGN` — يرفض التعيين إذا كانت الطلبية أُرسلت عبر شركة (`shipping_provider_id`) وإلا يحدّث `delivery_rider_id` (يُسجَّل تلقائيًا في audit عبر `OrderObserver`) ثم يعيد التحميل.
- **`partials/tracking-rider-tab.blade.php` (جديد):** حالة فارغة (عنوان + تلميح + زر «إدارة أسماء رجال التوصيل» بشرط `DELIVERY_RIDERS_VIEW`) أو بطاقات رجال (أفاتار بحرف، الاسم، المركبة/الهاتف، حالة غير نشط، عدّادتا active/total، شريط تقدم، سهم يدور عند التوسيع).
- **`partials/tracking-rider-shipments.blade.php` (جديد):** جدول/صفوف شحنات الرجل الموسّع (رقم، badge الحالة، زبون/هاتف/مدينة/رقم تتبع، الإجمالي، زر عرض الدرج) + حالة فارغة.
- **`partials/order-drawer.blade.php`:** قسم «رجل التوصيل» — قراءة الاسم الحالي + قائمة `x-edz.dropdown` (إلغاء التعيين / كل الرجال) تظهر فقط بشرط `ORDER_ASSIGN` وعدم وجود شركة شحن.
- **`tracking-rider-placeholder.blade.php`:** حُذف (حُلّت الحالة الفارغة داخل التبويب الجديد).
- **الترجمة:** 11 مفتاحًا ×4 لغات (`rider_manage_link/shipments_count/active/assign_section/assign/change/unassign/saved/has_provider/no_shipments`).
- **التحقق:** `php -l` ×4 نظيف + `view:cache` success + **54 ناجحًا (210 تأكيدًا)** بلا انحدار، منها **5 اختبارات جديدة** لصفحة التتبع (قائمة الرجال بعدّاداتها، توسعة رجل تعرض شحناته فقط، تعيين رجل مع تحديث العرض، رفض التعيين عند وجود شركة شحن، ومنع `assignRider` بدون صلاحية `order.assign` لـ STAFF → 403).
- **اكتشاف/إصلاح أثناء العمل:** `with('latestTracking:id,order_id,delivered_at,returned_at')` مع `latestOfMany` ينتج `ambiguous column name: order_id` في SQLite → إلغاء تقييد الأعمدة واستخدام `with('latestTracking')` كاملًا (مسار الشحنات يستخدم نفس الأسلوب غير المقيد).

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| D تبويب رجل التوصيل + تعيين | ✅ | tracking/index (riderRiders/toggleRider/loadRiderShipments/assignRider) + partials/tracking-rider-tab + tracking-rider-shipments (جديدان) + order-drawer (قسم الرجل) + حذف placeholder + order_flow.php ×4 (+11 مفتاحًا) + TrackingSearchFilterTest (+5) | +5 على الصفحة + الجولة 54 (210) نظيفة |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** 375/768/1440 — سرد رجال التوصيل وعدّاداته، توسيع رجل لعرض شحناته، تعيين/إلغاء تعيين الرجل من درج الشحنة (واختفاء القائمة عند اختيار شركة شحن)، وشريط تقدم الحالة المتجاوب.

---

## إعادة تصميم صفحة التتبع — المرحلة D-fix: تجانس عرض تبويب «رجل التوصيل» (بصري فقط، لا منطق جديد) (سبتمبر 2026) ✅

**النطاق:** رفع تبويب الرجل إلى تكافؤ بصري مع تبويب الشركات (stat cards + جدول سطح مكتب/بطاقات موبايل + زر نسخ رقم التتبع + ترقيم صفحات حقيقي + سطر ملخص)، بلا أي تغيير في منطق D (`toggleRider`/`assignRider` دون لمس، و`loadRiderShipments` بقي مع تغيير هيكلي واحد: استبدال `limit(200) الصامت` بترقيم صفحات).

- **`partials/tracking-rider-stats.blade.php` (جديد):** 3 بطاقات إحصاء تعكس `tracking-stats` حرفيًا (edz-card + رموز `accent-surface/success/warning` وحدها): رجال نشطون (أيقونة users)، شحنات نشطة لليوم (cube)، مستحقات COD لليوم بالـ `currency()` (banknotes) — `grid-cols-1 sm:grid-cols-3 gap-4 mb-6`.
- **`tracking/index.blade.php`:** state جديد (`riderShipmentsPage`, `riderStatsActiveCount/ActiveShipments/CodDueToday`). في `loadRiderOverview` استعلام تجميعي **واحد** فقط (لا استعلام لكل رجل): `groupBy delivery_rider_id` + `whereDate(created_at, today())` + `whereNotNull('delivery_rider_id')` + `whereIn(status_id, carrier)` + `whereDoesntHave('latestTracking', delivered/returned)` → `count(*)` و`sum(total_amount)` → يغذّي الاستات الثلاثة. أُعيدت هيكلة `loadRiderShipments`: `$mapRiderShipment` (closure مشترك) + `forPage(riderShipmentsPage, 20)` + `riderShipmentTotal = (clone $query)->count()` (الإجمالي الحقيقي عبر كل الصفحات)؛ `$loadMoreRiderShipments` جديد يضيف الصفحة التالية `array_merge` من دون إعادة ضبط `selectedRiderId`. (كلا العملين `use ($mapRiderShipment)` — غياب capture سبّب «Undefined variable» أُصلح.)
- **`partials/tracking-rider-tab.blade.php`:** تضمين الاستات أعلى فرع `@else` + سطر ملخص «:riders رجل · :shipments شحنة نشطة» بنفس وزن موضع سطر `filteredTotal` في تبويب الشركات.
- **`partials/tracking-rider-shipments.blade.php` (إعادة كتابة):** جدول سطح مكتب `hidden md:block` (رقم/زبون/مدينة/الإجمالي/badge الحالة/رقم التتبع بزر نسخ/عرض الدرج — بارتفاع 7 أعمدة) + بطاقات موبايل `md:hidden` بنفس لغة بطاقات `tracking-list` (زر النسخ Alpine `navigator.clipboard` + `EdzSwal.toast` نفسه) + زر «عرض المزيد» يظهر فقط عندما `riderShipmentTotal > count(riderShipments)` بنمط `wire:click="$set('riderShipmentsPage', N); $wire.loadMoreRiderShipments()"` — تحت 300 سطر Blade.
- **الترجمة:** 5 مفاتيح ×4 لغات (`rider_stats_active/active_shipments/cod_due_today/summary` + `rider_shipments_load_more`)؛ fr بلا أي قوس مفرد (استخدم `aujourd’hui` U+2019).
- **التحقق:** `php -l` ×5 نظيف + `view:cache` success + TrackingSearchFilterTest **15 ناجحًا (61 تأكيدًا)** من دون انحدار — منها **اختباران جديدان**: (1) استات التبويب تعرض الأرقام التجميعية الصحيحة (عدّاد رجلان/3 شحنات/COD 5700 مع استبعاد المسلَّمة اليوم)، (2) عرض المزيد يجلب الصفحة التالية (25 شحنة → 20 ثم 25) من دون إعادة ضبط `selectedRiderId`. جولة مجاورة: RiderManagementTest (10) + NoestTrackingSyncTest (6) + NoestTrackingSyncServiceTest (6) + OrderEventLogVisibilityTest (11) خضراء (فشل وحيد كان قفل ملف مؤقت Windows `rename Access denied` عند تجميع Blade متوازٍ — ناجح فورًا عند الإعادة).
- **تركّته عن النطاق عمدًا:** لا بحث/فلاتر داخلي ضمن شحنات الرجل الموسّع (مؤجّل لشاشة «رحلة اليوم» في E.2)؛ عدم لمس `order-drawer.blade.php`.

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| D-fix تجانس تبويب الرجل (بصري) | ✅ | partials/tracking-rider-stats (جديد) + tracking-rider-shipments (إعادة كتابة) + tracking-rider-tab + tracking/index (استات تجميعية + ترقيم صفحات `forPage` + loadMoreRiderShipments) + order_flow.php ×4 (+5 مفاتيح) + TrackingSearchFilterTest (+2) | +2 على الصفحة + TrackingSearchFilterTest 15 (61) نظيفة + جولة مجاورة 33 خضراء |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** 375/768/1440 — التفاف بطاقات الاستات بشكل صحيح على الموبايل، محاذاة أعمدة جدول الرجل مع جدول الشركات على سطح المكتب، عمل زر النسخ، وظهور «عرض المزيد» فقط عندما تتجاوز شحنات الرجل 20.

---

## إعادة تصميم صفحة التتبع — ترقية تبويبي tracking إلى شبكة متقدمة بنمط `/orders` (سبتمبر 2026) ✅

**النطاق:** ترقية كلا تبويبي صفحة `merchant/tracking` (carrier + rider) من قائمتين منفصلتين إلى شبكة بيانات واحدة متطورة بنمط صفحة `/orders`: كل الطلبيات في شبكة موحّدة، فلترة برجل التوصيل في تبويب الرجل، تخصيص/إخفاء/ترتيب الأعمدة مع أعمدة إلزامية لا تُخفى، فلترة من رؤوس الأعمدة (status/provider/rider/city/amount/date) تبقى مع الفلاتر الجانبية الموجودة، بطاقات إحصائية تستجيب للفلترة، وتذكّر آخر تبويب مفتوح (عبر تخزين المتصفح) + تفضيلات الأعمدة لكل تبويب عند إعادة التحميل.

- **`tracking/index.blade.php` (إعادة كتابة PHP):** state جديد — فلاتر `amount_min/amount_max/city/rider` + `allCities`؛ أُزيلت `selectedRiderId/riderShipments/riderShipmentTotal/riderShipmentsPage` و`loadRiderOverview/toggleRider/mapRiderShipment/loadRiderShipments/loadMoreRiderShipments` (استُبدلت بفلتر `rider` في `filters`). `$baseTrackingQuery(bool $forAggregate)` مصدر وحيد للاستعلام (provider/search/statuses/date/amount/city/rider + نطاق workflow) — تستمد منه الشبكة والاستات وعدّادات رأس الأعمدة. `$loadTrackingStats` (carrier: active/delivered_today/returned_today؛ rider: riderStatsActiveCount/ActiveShipments/CodDueToday + riderRiders من `groupBy delivery_rider_id` مرشّحة عبر `DeliveryRiderService::listForStore`)؛ `$loadShipments` ترقيم صفحات حقيقي (paginate) + خريطة صفوف؛ استات تُحسب قبل الترقيم من نفس الاستعلام المفلتر. `updated()` hooks لـ `filters.date_from/date_to/amount_min/amount_max/city/rider` (بـ `use function Livewire\Volt\updated;`) + `trackingTab` (يحمل prefs التبويب → `saveColumnPreferences` → `loadShipments`). closures: `trackingColumns` (carrier/provider+shipping_date، rider/delivery_rider+shipping_date)، `trackingDefaultOrder`، `trackingViewKey`، `getMembership`، `loadTrackingTabPreferences` (legacy `prefs_version!==1` → defaults؛ إعادة إدراج الإلزامي في الموضع الافتراضي؛ tableStyle default/status)، `saveColumnPreferences` (updateOrCreate + إعادة إدراج الإلزامي)، `openTableSettings/discardTableSettings/saveTableSettings/toggleDraftColumn/moveDraftColumn/reorderDraftColumns/resetColumns`، `loadRiderOptions`، `assignRider` (loadRiderOptions + loadShipments). `mount` يملأ providers/cities/riders.
- **partials الجديدة ×3 + إعادة كتابة `tracking-tabs`:** `tracking-table-header.blade.php` (خريطة headerFilterKeys city/total/status/provider/rider/date + شارة فعّال + حدث `edz-filter-open`)، `tracking-filter-portal.blade.php` (dropdownPosition + أقسام مبوّبة بـ visibleColumns + عدّادات ريدر من riderCounts)، `tracking-table-settings-modal.blade.php` (محمي بـ showTableSettings + تبويبا columns/style + `orderColumnReorderDraft` drag/up/down + لوك الإلزامي + footer reset/cancel/save + `@edz-modal-closed.window`). `tracking-tabs.blade.php` (إعادة كتابة): تذكّر آخر تبويب عبر **تخزين المتصفح** `localStorage('edz-tracking-active-tab')` فقط — لا عمود DB (`@click` يحفظ، `x-init` يستعيد بـ `$wire.set` عند اختلاف القيمة) — بدل الاقتراح الأصلي `active_tab` في DB (مرفوض من المستخدم: لا حاجة لإضافته للترحيل، ونُحذف الملف قبل تشغيله).
- **`partials/tracking-list.blade.php` (إعادة كتابة كاملة):** جدول سطح مكتب بأعمدة `visibleColumns` عبر `@include` لكل عمود + صفوف `$rowTint` (status: delivered→success، returned/cancelled/failed→danger) + خلايا @switch (number/customer/city/total/tracking_status pill/openStatusHistory/tracking_number copy→navigator.clipboard/provider/delivery_rider/shipping_date) + بطاقات موبايل + ترقيم prev/next عبر `pagination.*` + صف فارغ colspan=count+1.
- **`tracking-rider-tab.blade.php` (إعادة كتابة):** rider-stats + سطر ملخص `rider_stats_summary` + toolbar + سطر count + `tracking-list` — بلا بطاقات رجل موسّعة. حُذف `partials/tracking-rider-shipments.blade.php` + زر إعدادات columns في `tracking-toolbar` (أيقونة view-columns).
- **لا migration جديدة:** اقتراح `active_tab` (string nullable في user_column_preferences) **أُلغي** بطلب المستخدم — التبويب النشط يُتذكر عبر تخزين المتصفح فقط، ولا ضجة خادم/ترحيل. (`2026_09_10_123500_add_active_tab_...` حُذف الملف قبل تشغيله — حالة Pending لم تُشغّل.)
- **الترجمة:** `order_flow` ×4 لغات +3 مفاتيح (`filter_amount/amount_min_placeholder/amount_max_placeholder` بعد `filter_date`).
- **التحقق:** `php -l` نظيف + `view:cache` success + TrackingSearchFilterTest أعيدت كتابتها بالكامل **20 ناجحًا (89 تأكيدًا)** — استبدال اختبارات التوسعة/load-more بفلتر rider وpagination prev/next، واختبارات prefs (per view_key، إلزامي لا يُخفى، ترتيب يُستعاد، tableStyle، والتبويب يُتذكر في المتصفح لا في DB مع `Schema::hasColumn('active_tab')` false)، واستات تستجيب للفلترة (provider + amount). جولة مجاورة: OrderIndexPreferencesTest (9) — 29 ناجحًا (114 تأكيدًا). grep لا يجد أي مرجع متبقٍّ لـ `toggleRider/loadRiderShipments/loadMoreRiderShipments/selectedRiderId/riderShipmentsPage/tracking-rider-shipments/active_tab` في كود حي.
- **تركّت عن النطاق عمدًا:** لا فرز صفوف في الشبكة (نفس `/orders`؛ لا مسح ضوئي/مكوّن barcode — مؤجل لـ Phase 32)؛ لا سرد رجل داخلي منفصل (الفلترة كافية للمرحلة الحالية).

| فرع | الحالة | الملفات الرئيسية | اختبارات/تأكيدات |
|---|---|---|---|
| ترقية التتبع: شبكة متقدمة + prefs | ✅ | tracking/index (PHP rework) + partials tracking-list/table-header/filter-portal/table-settings-modal (جديدة) + tracking-tabs (localStorage) + tracking-rider-tab (إعادة كتابة) + حذف tracking-rider-shipments + tracking-toolbar (زر إعدادات) + order_flow ×4 (+3) + TrackingSearchFilterTest (إعادة كتابة كاملة) | TrackingSearchFilterTest 20 (89) نظيفة + جولة مجاورة (OrderIndexPreferences 24) — 29 (114) نظيفة |

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** 375/768/1440 — عرض الشبكة الموحّدة في كلا التبويبين، فلاتر رؤوس الأعمدة، modal إعدادات الأعمدة (سحب/ترتيب + أعمدة إلزامية مقيّدة)، استجابة الاستات للفلترة، واسترجاع آخر تبويب+الأعمدة عند إعادة التحميل.

---

## المرحلة E — بوابة رجل التوصيل: قرارات المستخدم المعتمدة (سبتمبر 2026) 📋

**طلب المستخدم (نصي، بتاريخ اليوم):** لوحة رجل توصيل احترافية تنافسية بسوق جزائري:
1. **إحصاءات متقدمة ومتطورة** في لوحة الرجل (بطاقات، تقدم يومي، COD، جغرافيا).
2. **تحكم تفصيلي في حالات التوصيل مع ملاحظات** — لكن **لا حذف ولا تعديل** لأي حالة/ملاحظة (append-only).
3. **العضو المالك لصلاحية التتبع** يرى التتبع في نفس الصفحة الحالية وتبويبات الشركات كما هي الآن.
4. الرجل يرى **طلبياته المكلف بها فقط** (لا يرى متغيرات المتجر).
5. المطلوب: تقديم **خطة/اقتراحات احترافية متقدمة منافسة** موجهة للسوق الجزائري قبل التنفيذ.

**إضافة لدقة الصلاحيات:** عضو الفريق ذو الاطلاع المقيّد (staff/manager) يرى في تبويب الرجل شحنات **المسندة إليه فقط** (`assigned_to_membership_id`) — نفس قاعدة سجل أحداث الطلبية الحالية. (سيُراعى في F/G.)

> **✅ تم (11 سبتمبر 2026):** نطاق تبويب الرجل يُقيّد الآن عبر `StoreOrderPermissions::isRestrictedMembership()` (نفس حد `canViewOrderEventLog`: OWNER/ADMIN غير مقيّدين، والباقي مقيّد) داخل `TrackingGridConcern::baseTrackingQuery` — يغطي صفوف الشبكة والإحصاءات معًا (مصدر واحد)، بلا تعرّض لتبويب الشركات أو سلة المهملات. تـغلق بند **P1-6** من تقرير الـ QA. أدلة: 4 اختبارات في `TrackingSearchFilterTest` (staff: الشبكة + الإحصاءات، manager: الشبكة، owner: يرى الكل) — **402 ناجحًا (1555 تأكيدًا)، صفر انحدار.**

**المطلوب الآن:** عرض خطة مراجعة للموافقة قبل كتابة الكود (انظر الرسالة التالية في الدردشة).

---

## دفعة صفحة التتبع: فلاتر المسنَد/المؤكِّد + عمود الولاية + ملاحظات الناقل + إجراءات الحذف/الإلغاء/التعديل (سبتمبر 2026) ✅

**بقرارات المستخدم المعتمدة:** (1) زر التعديل يفتح مودال التعديل داخل صفحة التتبع نفسها (بورت من orders — لا توجيه)؛ (2) عمود «الملاحظات» = ملاحظة شركة الشحن API في بوب أب (تاريخ + مُرسِل + مُرسِل جديد) بنفس `sendCarrierNote`؛ (3) بوابة التعديل في سياق التتبع = منع الحالات النهائية فقط `delivered/returned` (الشبكة تعرض حالات carrier فقط)؛ (4) إلغاء الإرسال يمر عبر `OrderShippingGateway::cancel` (حالات `shipped/in_transit/out_for_delivery` فقط، حارس إضافي `isCarrierValidated`) — حذف سجل الشركة عبر API ثم إعادة الطلبية لـ `confirmed` (launch leg بعكس مع RESTORE؛ `tracking_number=null` + `carrier_status='cancelled'` + `tracking_status=null`) — **لا حذف فعلي من DB**، والحذف الناعم يبقى سلوكًا قائمًا للزر الثاني.

- **الخلفي:** `CarrierIntegrationContract::deleteOrder(string $number, array $credentials): array` + `NoestIntegrationAdapter::deleteOrder` (POST `/delete/order`، Bearer api_token + user_guid + tracking) + ميغريشن `defines_capability`/`supports_order_delete` (noest→true) + `Carrier::capabilityList()['order_delete']` + `OrderShippingGateway::cancel()` (فئة نطاق الحالة، حذف ضلع الناقل، `OrderService::revertTo` → confirmed، تراجع/إرجاع).
- **الصفحة (`tracking/index.blade.php`):** `baseTrackingQuery` — eager loads `assignedMembership.user`/`confirmedByHistory.changedBy.user` + فلاتر `assigned_to` (assigned_to_membership_id) و`confirmed_by` (whereHas confirmedByHistory.changedBy) + تبويب الرجل `whereNotNull('delivery_rider_id')`؛ `loadShipments` — أحدث carrier-note واحدة (استعلام واحد `whereIn`/groupBy، بلا N+1) + صفوف `state/confirmed_by/assigned_to/latest_note/tracking_id/carrier_supports_api_notes/can_edit_order/can_cancel_shipment`؛ `shipmentCancelable` — flag/حارس موحّد (نطاق الحالة + غير مُصادَق + carrier يدعم `order_delete` أو رجل مُعيَّن)؛ closures منسوخة من orders: `openEditModal` (بوابة terminal) /`submitEdit` (بلا حارس shipped) /`deleteOrder` /`cancelShipment` /`openShipmentNotes`؛ أعمدة جديدة (ترتيب: number, customer, state, city, assigned_to, total, tracking_status, tracking_number, confirmed_by, notes, actions) + default order جديد.
- **Blades:** toolbar (selects `assigned_to`/`confirmed_by` مخفية عند ظهور العمود + chips)؛ table-header (مفاتيح `assigned`/`confirmed`)؛ filter-portal (قسمان جديدان)؛ tracking-list (خلايا state/assigned_to/confirmed_by/notes + قائمة إجراءات منسدلة: Details/Edit/Cancel/Delete عبر `EdzSwal`)؛ `tracking-notes-popup.blade.php` (جديد)؛ تضمين `order-form-modal` + `orders-product-picker` + شيم `openOrderDetails`.
- **الترجمات ×4:** `order_flow`: cancel_shipment_title/confirm/shipment_cancelled/cancellation_status/cancellation_failed/already_validated/carrier_delete_not_supported/carrier_notes/no_carrier_notes/carrier_notes_unsupported؛ `merchant_panel`: cannot_edit_terminal.
- **الأدلة:** `tests/Feature/Merchant/TrackingGridBatchTest.php` (جديد، **10 ناجحة / 40 تأكيدًا** — فلاتر assigned_to/confirmed_by، row-map الجديدة، منع التعديل لنهاية الطلبية، تحميل المودال، submitEdit يحدّث + يعيد الحساب، حذف ناعم + إفراغ الدرج، إلغاء عبر fake HTTP يقتل سجل الشركة ويعيد confirmed مع خلو tracking، رفض بلا قدرة delete، منع المُصادَق) + تحديث `TrackingSearchFilterTest` (تبويب الرجل: شحنات برجل فقط عند تعيينه؛ ترتيب أعمدة افتراضي جديد) — **31 ناجحًا (140 تأكيدًا)** في الملفّين؛ `php -l` نظيف + `view:cache` success.

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** 375/768/1440 — فلاترا المعيّن/المؤكِّد (شريط + رؤوس)، قائمة إجراءات الصف في الجدول والبطاقات، بوب أب الملاحظات، مودال التعديل داخل صفحة التتبع، وتوستهما (توست إلغاء + توست عدم دعم الناقل للدائرة).

---

## دفعة تتبّع: ترتيب الأعمدة الجديد + سلة المهملات + المزامنة الجماعية + ويب هوك الشركات + طباعة الملصق (سبتمبر 2026) ✅

**بقرارات المستخدم المعتمدة:** (1) الأعمدة الثلاثة الجديدة (الولاية/الحالة/الناقل أو الموصّل) أصبحت إلزامية، وترتيبها الافتراضي الجديد 13 عمودًا مع `prefsVersion = 2` للتهجير فيزيائيًا؛ (2) «الحذف» الناعم يتحوّل لنقلٍ إلى سلة المهملات بفراغ ضمن الحالة لمرة واحدة والاسترجاع منها، والنهائي محصور بـ `ORDER_DELETE`؛ (3) السلة/الحذف النهائي/الاسترجاع والويب هوك — كلها تحت تبويب «الشركات» ذاته.

- **ترتيب الأعمدة (`tracking/index.blade.php`):** default = number → tracking_number → customer → **state (إلزامي)** → city → total → tracking_status → **provider (carrier) / delivery_rider (rider) (إلزامي)** → assigned_to → confirmed_by → notes → shipping_date → actions؛ `trackingColumns`/`trackingDefaultOrder`/مواضع الإلزامي في restore/save أُعيدت لتتوافق، و`prefsVersion = 2` يهجّر القديم مرة واحدة.
- **سلة المهملات:** `deleteOrder` = نقل ناعم (توست `order_moved_to_trash` + إغلاق الدرج إن كان مفتوحًا)؛ بلا فراغ جنّي أثناء السلة؛ `trashCount` في الحالة + `showTrash`؛ `restoreOrder`/`restoreAll` (توست `orders_restored`) + `forceDeleteOrder`/`forceDeleteAll` محصورة بـ `ORDER_DELETE`؛ ترتيب `$purgeOrderRows` = OrderTrackingHistory → OrderEvent → OrderStatusHistory → OrderTracking → OrderItem::forceDelete → order->forceDelete؛ بانر إشعاري أعلى الجدول في وضع السلة + `toggleTrash` في شريط الأدوات وإخفاء الإجراءات غير المتعلقة في االسلة.
- **المزامنة الجماعية (`$syncAllTracking`):** عبر `StopdeskOfficeSync::resolve` + `NoestTrackingSyncService::apply` لكل تتبع مفتوح (order + تاریخ + activity) وتوست ملخص `sync_all_tracking_result` بـ `sync_updated/sync_failed/sync_no_open`.
- **ويب هوك التوصيل:** ميغريشن `2026_09_11_200000_add_webhook_token_to_shipping_providers.php` (عمودا `webhook_token` uuid فريد nullable + `webhook_last_seen_at`؛ index على `webhook_token`؛ fillable/casts في `ShippingProvider`)؛ `DeliveryWebhookController@store` — يحدد الشركة بالتوكن (يجب `is_active`، وإلا 404)، يحدّث last_seen، يستخرج رقم التتبع (aliases: tracking_number/tracking/code/data.tracking_number)، يؤكّد OK إن لم يوجد تتبع (لا retry)، يطبّع إحداث الناقل إلى `normalizeEntry` (activity/events/OrderInfo/order_info/صف واحد) ويطبّق عبر `app(NoestTrackingSyncService::class)->apply()`؛ مسار `POST /webhooks/delivery/{token}` باسم `webhooks.delivery` بـ throttle:120,1 خارج أي مصادقة متجر.
- **واجهة الويب هوك (`providers.blade.php`):** كارت لكل شركة — شارة `webhook_ready/webhook_not_configured` + كتلة نسخ للتوكن + زر إعادة توليد بآلية uuid + سطر آخر استقبال `webhook_last_seen` أو زر تفعيل؛ `$enableWebhook/$regenerateWebhook` تحت حارس `DELIVERY_PRICING_MANAGE` وحقل `carrier_id` مطلوب (`$enableAll` يبقى لتعبئة الاعتمادات فقط). أيقونة `link` غير موجودة في `icon.blade.php` → استُخدمت `external-link` (الخريطة تحوي truck-x-mark/printer/arrow-path/shield-check/arrow-uturn-left/clipboard/external-link).
- **طباعة الملصق:** ① `CarrierIntegrationContract::getLabel(ShippingProvider, string $tracking)` (ويتطلب تعبئة الاعتمادات → `connection_missing_credentials`) + `NoestIntegrationAdapter::getLabel` (URL `/get/order/label?tracking=` بتوكن الشركة، وبلا توكن ok=false)؛ ② `DeliveryLabelController@show(Store $store, Request, string $tracking)` — حارس `ORDER_VIEW`، يسترجع التتبع بالشركة، يعرّف المحوّل، يجلب URL الملصق بـ `Bearer` ثم يبثّ البايتات inline (`Content-Disposition: inline; filename=label-{T.N.}`) — CVE سدّ للتوكن أمام المتصفح؛ ③ مسار `GET /merchant/{store:slug}/tracking/label/{tracking}` باسم `merchant.tracking.label` في Layer 3؛ ④ واجهة التتبّع: `$openLabel` (carrier أولًا: حدث متصفح `open-label` + `window.open` ببروكسي) و`$closeLabel` + مودال `label-print-modal` (ورقة `#edz-label-sheet` + CSS طباعة يحصر الرؤية + شريط الباركود) + مكوّن `x-edz.barcode` (Code128 كامل: جدول الأنماط، subset C للأرقام الزوجية، checksum mod 103، SVG `crispEdges`)؛ ⑤ **خدعة Laravel 12 Positional Resolution** اكتُشفت أثناء التصحيح: مع `(Request $request, string $tracking)` ومسارين، حقن `Request` يزيح `$tracking` فاستقبل سلَغ المتجر → الحل وضع `Store $store` أولًا في التوقيع (النموذج يُربط بالقيمة فيُبقى الترتيب صحيحًا).
- **الترجمات ×4 (ar/en/fr/es) `order_flow.php`:** trash_restored/trash_restore_all/permanent_delete_title/permanent_delete_confirm/order_moved_to_trash/orders_restored/delete_confirmation_title/delete_confirmation_message/delete_permanently/trash_count/trash_empty_trash_label+confirmation/all_orders_restored/sync_all_tracking/sync_all_tracking_result/sync_updated/sync_failed/sync_no_open/webhook_enable (+ كل مفتاح الطباعة: print_label/label_print/close/label × المختلفة) — وجُمعت عناصر السلة أسفل `order_moved_to_trash` الحالي.
- **الأدلة:** `tests/Feature/Merchant/TrackingTrashWebhookLabelTest.php` (**10 ناجحة / 46 تأكيدًا** — ترتيب الأعمدة، سلة/استرجاع، restoreAll، purge صفوف كاملة بمنتج/متغير حقيقيين، رفض سلة/نهائي بدون `ORDER_DELETE` (عبر `canStore` + بقاء الحالة لأن Livewire يبتلع HTTPException في `call()` في المختبر)، syncAllTracking، ويب هوك يطبّق الإحداث مثل الاقتراع، رفض توكن مجهول/شركة غير نشطة، `getLabel` بالتوكن/بدونه، بروكسي الملصق يبثّ PDF بأشرطة الإطار) + تحديث `TrackingSearchFilterTest` (الترتيب الجديد + الإلزامي في modal — مع `total` إلزاميًا الآن يستهدف الاختبار إخفاء/تحريك `notes`/`city` بدل `total`)؛ جولة مجاورة: TrackingGridBatchTest + TrackingSearchFilterTest + TrackingStatusHistoryPopupTest + OrdersTrackingColumnTest (**38 ناجحًا بعد إعادةٍ لاحقة**) و 6 سويتات شحن/تتبّع (OrderTracking/BulkSendCarrierGrouping/DirectSendConfirmedOrder/NoestIntegration/OrderShippingProviderColumn/DeliverySettings) **54 ناجحة (236 تأكيدًا)** — صفر انحدار؛ `php -l` نظيف على الملفات المتغيرة + `migrate --force` ناجح للميغريشنات الثلاث (سلة/seed NOEST + الويب هوك).**إصلاح** فردي unrelated: فشل وحيد في جولة كانت قفل ملف Windows مؤقت (`rename Access denied`) — نجح فورًا عند الإعادة.

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** 375/768/1440 — ترتيب الأعمدة الجديد مع الإلزامي، سلة المهملات (بانر/توستس/الاسترجاع/النهائي)، زر المزامنة الجماعية، كارت الويب هوك في الإعدادات، وتدفق الطباعة الكامل (قائمة صف → فتح ملصق الشركة في تبويب أو ملصقنا بباركود + طباعة).

---

## دفعة صفحة التتبع: سلات منفصلة لكل تبويب + رقم تتبع الرجل + نظافة الترميز + وقت التاريخ (سبتمبر 2026) ✅

**بقرارات المستخدم المعتمدة أثناء التنفيذ:** سلة المهملات صارت **لكل تبويب** في الجهة المقابلة (`ms-auto`) مع زر «الرجوع» بدل الأحمر — زر السلة انتقل من هيدر الصفحة إلى `tracking-tabs` ويتبدل عند التفعيل؛ نطاق السلة في `baseTrackingQuery`/`trashCount`/`restoreAll`/`forceDeleteAll` = carrier `whereNotNull('shipping_provider_id')` / rider `whereNotNull('delivery_rider_id')`؛ قوائم إجراءات الصف أصبحت `position:fixed` بفلْب للأعلى قرب أسفل الشاشة (لم تَعُد تُقص داخل `overflow-hidden` للكارد)؛ رقم تتبع الرجل يُولَّد فورًا عند الإرسال؛ الكرون لا يلمس الشركات ذات الويب هوك؛ والسبب الجذري لرموز «â€”» المعطوبة هو **تلف UTF-8 في السورس نفسه** وليس تشفير المتصفح.

- **نظافة الترميز:** `tracking-list.blade.php` — استبدال حرفي `â€”`→`—` و`â€¢`→`•` (بما فيها أعمدة `notes` و`—`/`•` في الواجهة). **بقي تلف مماثل غير مُبلَّغ عنه في `orders/partials/order-form-modal.blade.php`** (الأسطر 20/46/99/208/319) — خارج هذه الدفعة، يُصلَح متى طلب المستخدم.
- **عمود التاريخ:** desktop + بطاقة الموبايل يعرضان `Y-m-d H:i` (تاريخ+وقت كاملان).
- **شارة نوع التوصيل HM/SD:** عمود `tracking_number` (desktop) وموبايل — رموز بجهة مقابلة (`ms-auto`): `HM` (muted) / `SD` (accent) عبر `'delivery_type' => $order->delivery_type` في خريطة loadShipments.
- **زر النسخ:** `truncate max-w-[7rem] group-hover:max-w-none` — يظهر رقم التتبع كاملًا عند الهوفر فقط.
- **القوائم:** desktop+موبايل `x-ref="menu"` + `EdzMenu.position($el, $refs.menu)` الجديدة في سكربت الصفحة (fixed + أيمن + فلْب: تفتح أعلى الصف عند ضيق المساحة السفلية).
- **السلة لكل تبويب:** `tracking-tabs` (زر + عداد + «الرجوع» بأيقونة arrow-left و`text-accent-600`)؛ `tracking-trash-banner` جديد بتوكنز داكنة `bg-danger-surface/text-danger-fg/border-danger-border` (الخامُّ `danger-50/200/700` لا يتكيف مع الدارك)؛ `tracking-rider-tab` بفرع سلة + شريط البحث (تكافؤ مع carrier)؛ `updated('trackingTab')` يعيد `showTrash=false`.
- **رقم تتبع رجل التوصيل:** في `$assignRider` — `OrderTrackingService::ensureRiderTracking($order, $number)` (جديد): إن وُجد تتبع مفتوح يُملأ رقمه الفارغ فقط، وإلا `startShipment` جديد؛ الرقم = `{HM|SD}-{STR(RANDOM 8)}` حسب `delivery_type` مع فحص تفرد في المتجر (حلقة do/while) — اتجاه الرجل لا يُسبَر (سجل `dueTrackings` بلا provider).
- **الكرون/الويب هوك:** `SyncNoestTrackingJob::handle` + `routes/console.php` يضيفان `whereNull('webhook_token')` (الشركات ذات الويب هوك تدفع بنفسها؛ الاقتراع لها فقط بلا ويب هوك).
- **مزامنة بلا فراغ:** `NoestTrackingSyncService::apply` — عندما لا تُطابِق الأحداث حالة والصف بلا حالة سابقة: يكتب `IN_TRANSIT` + سجل حالة `fallback_in_transit` بدل ترك «—» (idempotent بلا سجل مكرر).
- **الترجمات:** `order_flow` ×4 لغات: `back_from_trash` / `delivery_type_home` / `delivery_type_stopdesk`.
- **الأدلة:** `TrackingTrashWebhookLabelTest` 10 ناجح + `TrackingSearchFilterTest` 24 (+3 جديد: توليد رقم HM، بادئة SD لـ stopdesk، وعدم تكرار leg عند إعادة التعيين) + `NoestTrackingSyncTest` 7 (+1: الجوب يتخطّى شركة الويب هوك `Http::assertNothingSent`) + `NoestTrackingSyncServiceTest` 7 (+1: fallback IN_TRANSIT على صف بلا حالة) — **67 ناجحًا بلا انحدار**؛ `php -l` نظيف ×5 + `view:cache` success.

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** 375/768/1440 — زر السلة/الرجوع في التبويبات، القوائم المعلّقة بفلْب الأعلى في آخر صف، شارة HM/SD + رقم التتبع الممتد عند الهوفر، عمود التاريخ+الوقت، كارت سلة الرجل ببحثه، ورقم تتبع الرجل (HM/SD-XXXXXXXX) في درج الطلبية وطباعة الملصق.

---

## دفعة صفحة التتبع (1/5): الجدول القابل للسكرول + قوائم الصف المثبتة + رسالة النسخ + نظافة الترميز (سبتمبر 2026) ✅

**بقرارات المستخدم المعتمدة:** تنفيذ التحسينات **بَدْفعات** تُعرض قبل كل دفعة؛ توكن الويب هوك ينتقل لترويسة `X-Delivery-Token` مع `?token=` احتياطيًا (دفعة 2)؛ `thead` لاصق مع سكرول عمودي (هذه الدفعة)؛ الهيكلة traits بـ closures رفيعة (دفعة 4).

- **الجدول:** غلاف new = `relative` → `overflow-x-auto max-h-[calc(100vh-475px)] overflow-y-auto edz-scroll` (سكرول أفقي+عمودي داخل الكارد)؛ الجدول `w-max min-w-full` (الأعمدة تحتفظ بعرضها الطبيعي والسكرول يأكل الزائد)؛ `thead` = `sticky top-0 z-10 bg-surface [&_th]:bg-surface` (هيدر ثابت أثناء السكرول العمودي).
- **قوائم الصف:** مكوّن Alpine عام **`edzRowMenu`** (`resources/js/components/edz-row-menu.js` — جديد، n=224/280، `x-ref="trigger"`، موبايل <640 = ورقة سفلية، وإلا fixed بفلْب للأعلى/أيمن مع clamppping) + تسجيل في `panel.js` + تم حذف سكربت `window.EdzMenu` المضمّن من `index.blade.php`؛ desktop وَموبايل يَسخدمان `x-edz.mobile-bottom-sheet` (scrim + لوحة موحدة) بدل `x-ref="menu"` الخام — القائمة تظهر **فوق الصف** دائمًا.
- **رسالة النسخ:** السبب الجذري: `EdzSwal.toast` غير موجود إطلاقًا في `swal.js` (يوجد `success/error/...`) — استُبدل بـ `EdzSwal.success('', copy_done)` في المواضع الثلاثة (tracking-list:178/265 + order-drawer:47).
- **نظافة الترميز:** `orders/partials/order-form-modal.blade.php` — تلف UTF-8 المُبقّى (أسطر 20/46/94/99/208/319: `â€”`, `â†’` → `—`, `→`).
- **الأدلة:** `TrackingTrashWebhookLabelTest` 10 ناجح + `TrackingSearchFilterTest` 24 ناجح (9+دُرجة [145 تأكيدًا]) — أول جولة اصطدمت بقفل ملف Windows مؤقت (rename Access denied) ونجحت عند الإعادة؛ `view:cache` + `npm run build` نظيفان.

## دفعة صفحة التتبع (2/5): ويب هوك لكل دومين + لكل شركة (سبتمبر 2026) ✅

**حسب قرار المستخدم:** التوكن ينتقل من المسار إلى ترويسة `X-Delivery-Token` مع احتياطي `?token=`.

- **القرار التصميمي:** استُخدم عمود `shipping_providers.code` الموجود (`noest`) كمعرّف الشركة في الرابط — لا حاجة لميغريشن slug (الـ `code` بلا فهرس فريد، ومكرر بين المتاجر بشكل مقصود؛ التمييز بالتوكن).
- **المسار:** `POST /api/webhooks/delivery/{provider}` باسم `webhooks.delivery` (بدل `/{token}`) — رابط ثابت لكل شركة لكل دومين (`route()` يبني host تلقائيًا من الدومين الحالي).
- **`DeliveryWebhookController::resolveProvider`:** توكن من `X-Delivery-Token` ثم `?token=` → `where code + webhook_token`؛ بلا توكن → الـ segment نفسه هو السر (النمط القديم) → `where webhook_token = segment`؛ يلزم `is_active` في الحالتين (404 وإلا).
- **كارت الإعدادات** (`providers.blade.php`): يعرض الرابط الأساسي `/webhooks/delivery/noest` + صف توكن منفصل (نسخ) + تلميح `X-Delivery-Token`/`?token=`؛ زر النسخ ينسخ الرابط الكامل بـ `?token=` (يعمل فورًا ويخدم الناقل الذي لا يدعم الترويسات)؛ إعادة التوليد تغيّر التوكن «فقط» مع بقاء الرابط ثابتًا.
- **الترجمات ×4:** مفاتيح جديدة `webhook_token_label` / `webhook_token_copy` / `webhook_header_hint` + تحديث قيم `webhook_token_regenerate` و`webhook_regenerated` (توكن لا رابط).
- **الأدلة:** `TrackingTrashWebhookLabelTest` → **12 ناجحة / 52 تأكيدًا** (+3: `?token=` fallback، legacy token-in-path، رفض code مجهول/توكن مجهول/شركة غير نشطة على الشكل الأساسي) + جولة جوار: StopdeskSyncUiTest + NoestTrackingSyncTest + NoestIntegrationTest **22 ناجحة / 97 تأكيدًا** — صفر انحدار؛ `php -l` ×3 + `view:cache` نظيفان.

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** كارت الويب هوك في إعدادات التوصيل (رابط `.../delivery/noest` + صف التوكن + التلميح) وإعادة توليد التوكن (الرابط ثابت). **الدفعة 3 (التالي):** الحذف النهائي يحذف عند شركة التوصيل (`OrderShippingGateway::deleteAtCarrier`).

## دفعة صفحة التتبع (3/5): الحذف النهائي يحذف أيضًا عند شركة التوصيل (سبتمبر 2026) ✅

**حسب قرار المستخدم:** الحذف النهائي للطلبية من سلة المهملات يحذف الشحنة غير المُتحقَّق منها عند الناقل أولًا (بلا حجب الحذف المحلي أبدًا).

- **`OrderShippingGateway::deleteAtCarrier(Order $order): array`** (جديد) — لا يرمي أبدًا؛ يُعيد مصفوفة `ok`/`skipped` بأسباب صريحة (`no_provider`/`no_tracking`/`validated`/`unsupported`) أو `ok`/`message`/`error`؛ يسجّل فشل الناقل عبر `Log::warning`. يستخدم `OrderTrackingService::currentTracking` + `config('delivery.carrier_integrations.{code}')` — بلا ترميز صعب لناقل واحد.
- **السحب:** إجراءا `$forceDeleteOrder` و`$forceDeleteAll` في `tracking/index.blade.php` يستدعيان `app(OrderShippingGateway::class)->deleteAtCarrier($order)` قبل `purgeOrderRows` (الفحص `isCarrierValidated()` يتخطّى ناقل الشحنة المتحقَّق منها تلقائيًا).
- **اختبارات:** أُضيف `Http::fake` لمسار الفشل في اختبار الحذف النهائي القائم + اختباران جديدان: «يُحذف الشحنة غير المتحقَّق منها عند الناقل أولًا» (يؤكد `assertSent` لـ `/delete/order` بـ `tracking=TRK-CDEL-1`) و«يتخطى حذف الناقل لشحنة مُتحقَّق منها» (`assertNothingSent`). **14 ناجحة / 56 تأكيدًا.**
- `php -l` + `view:cache` نظيفان.

## دفعة صفحة التتبع (4/5): هيكلة `index.blade.php` — نقل الـ 68 closure إلى 5 traits (سبتمبر 2026) ✅

**حسب قرار المستخدم:** الهيكلة عبر traits (الـ `state()`/`updated()`/`mount()` تبقى في الـ blade؛ الطرائز تُنقل كتوابع حقيقية بنفس الأسماء فتبقى استدعاءات القالب `$wire.*`/`$this->*()` كما هي تمامًا).

- **5 traits جديدة تحت `app/Livewire/Concerns/`** (مثل `HasOrderProductPicker` القائم):
  - `TrackingGridConcern` — `baseTrackingQuery`/`loadTrackingStats`/`loadShipments`/`refresh`/`clearFilters`/`setFilter`/`toggleTrackingStatus`/`nextPage`/`previousPage`/`shipmentCancelable`.
  - `TrackingColumnConcern` — `trackingColumns`/`trackingDefaultOrder`/`trackingViewKey`/`getMembership`/`loadTrackingTabPreferences`/`saveColumnPreferences`/`openTableSettings`/`discardTableSettings`/`saveTableSettings`/`toggleDraftColumn`/`moveDraftColumn`/`reorderDraftColumns`/`resetColumns`/`loadRiderOptions`.
  - `TrackingDrawerConcern` — `loadDrawerHistories`/`openDrawer`/`closeDrawer`/`openStatusHistory`/`closeStatusHistory`/`syncTracking`/`syncAllTracking`/`sendCarrierNote`/`openShipmentNotes`/`closeShipmentNotes`/`openOrderDetails`/`openLabel`/`closeLabel`/`cancelShipment`.
  - `TrackingRiderFormConcern` — `generateRiderTrackingNumber`/`assignRider`/`storeDefaultProviderId`/`formShipmentTypeOptions`/سلسلة المدن والمكاتب (`cityOptionsFor`/`loadFormCitiesLazy`/`loadCities`/`officeOptionsFor`/`loadFormOfficesLazy`/`rebuildFormOffices`/`loadFormOffices`/`providerOfficeStates`/`providerHomeStates`/`homeCoveredCityIds`/`loadFormScope`/`releaseStaleDestination`/`applyProviderScope`/`changeDeliveryType`/`refreshFormOffices`)/`refreshFormDuplicateWarnings`/`recalculateOrderShipping`/`openEditModal`/`submitEdit`.
  - `TrackingTrashConcern` — `deleteOrder`/`toggleTrash`/`restoreOrder`/`restoreAll`/`purgeOrderRows`/`forceDeleteOrder`/`forceDeleteAll`.
- **التنفيذ:** الأجساد منقولة حرفيًا (نفس الشروط والمفاتيح والنصوص) من الـ closures إلى توابع عامة بنفس الأسماء والتوقيعات؛ استُبدل رأس الـ blade (13 `use` كلاسًا زائدة + `uses([HasOrderProductPicker::class])`) بكتلة خفيفة تسجّل الـ 6 traits في `uses([...])` وأُعيد `use StorePermissionEnum`/`use Order` لأن `mount()` يستخدمهما؛ كتلة «الـ 68 closure» حُذفت من الـ blade بحفظ `state()` و`updated()` و`mount()` كما هما — **2355 سطرًا → 304 سطرًا** مع بقاء القالب/الجزئيات بلا تغيير.
- **التحقق من فجوات السطح:** كل أسماء `$wire.*` و`$this->method()` في partials صفحة التتبع + `order-form-modal`/`orders-product-picker` إمّا من الـ traits الخمسة أو `HasOrderProductPicker` (لا اسم مفقود).
- **الأدلة:** `TrackingTrashWebhookLabelTest` **14 ناجحة / 56 تأكيدًا** + `TrackingSearchFilterTest` **23 ناجحة / 100 تأكيدًا** (37/156) — صفر انحدار؛ `php -l` ×6 + `view:cache` نظيفان.

> **الدفعة 5 (تالية):** توليب تعليمي احترافي بمكوّن `x-edz.tooltip` على عناصر `title` في صفحة التتبع.

## دفعة صفحة التتبع (5/5): توليب تعليمي احترافي `x-edz.tooltip` (سبتمبر 2026) ✅

**المواصفات المعتمدة (Apple):** قالب عائم بلون الحبر بشفافية 92% + `backdrop-blur` + تأخير ظهور ~400ms + مخفي كليًا على أجهزة اللمس/الماوس الخشن.

- **المكوّن الجديد `resources/views/components/edz/tooltip.blade.php`:** `<x-edz.tooltip label="..." side="top|bottom" maxWidth="...">` يلتف حول العنصر ويُضيف فقاعة `role="tooltip"`؛ بلا label يُمرَّر الـ slot كما هو (لا شيء يظهر). الوسم `display:inline-flex` يلتف حول أي عنصر (زر/شارة) دون كسر التخطيط؛ `max-width` عبر متغير CSS على الغلاف.
- **النمط `resources/css/components/_tooltip.scss`** (مُسجَّل في `_index.scss`): الفقاعة `position:fixed` بمستوى `--edz-z-popover` → تهرب من حاوية السكرول الخاصة بالجدول (`overflow-x-auto`) بلا قصّ؛ خلفية ink rgba(17,24,39,.92) + `backdrop-filter: saturate(180%) blur(4px)` + نسخة داكنة أفتح؛ `pointer-events:none` (لا تحجب التمرير/النقر)؛ انتقال `opacity/transform` قصير مثل قوائم `edzDropdown`.
- **المحرك `resources/js/components/edz-tooltip.js`** (مُسجَّل `edzTooltip` في `panel.js`): تحريض دخول `hover: hover and pointer: fine` فقط (بدون لمس)، تأخير ظهور 400ms وتأخير إخفاء 60ms، يُقاس `trigger.getBoundingClientRect()` ثم يثبّت الفقاعة فوق/تحت مع انقلاب تلقائي عند ضيق الرأسي وتثبيت أفقي بحواف 8px؛ `@click.capture` يخفيها فور أي نقرة داخل الغلاف (متوافق مع قائمة الصف `edzRowMenu` المغلَّفة).
- **التطبيق في صفحة التتبع** (استبدال `title` الأصلي بالتوليب، مع فقاعة «رقم التتبع الممتد» على زر النسخ الذي يعرض الرقم مقتطعًا):
  - `tracking-list`: زر النسخ (label = الرقم الكامل) + شارة الحالة/السجل ×2 + زر النوتات + زرا الإجراءات ⋯ (غلاف حول حاوية `edzRowMenu` كاملة حتى لا تتعارض مراجع x-ref/التعبيرات) + شارتا SD/HM ×2 (desktop+mobile).
  - `order-drawer`: زر نسخ رقم التتبع في كارت الناقل.
  - `tracking-toolbar`: زر المزامنة الجماعية + زر إعدادات الأعمدة.
  - `tracking-tabs`: زر سلة المهملات/العودة (label ديناميكي + `ms-auto` على الغلاف).
- **التزامات gated بـ hover:** على الموبايل كل المقابض الفارغة تعمل بلا توليب (سلوك Apple) — لا تسريب توليب على اللمس.
- **الأدلة:** `npm run build` نظيف (CSS يحوي `.edz-tooltip*` والـ panel chunk يحوي `edzTooltip`)؛ `view:cache` نظيف؛ **37 ناجحة / 156 تأكيدًا** (TrackingTrashWebhookLabelTest 14 + TrackingSearchFilterTest 23) — صفر انحدار.

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** على 375/768/1440 — التوليب فوق أزرار النسخ/الإجراءات/شارة الحالة/شارتي SD-HM، وظهوره بعد ~400ms بلا قصّ من حاوية السكرول، واختفاؤه فور النقر، وعدم ظهوره على اللمس إطلاقًا. انتهت الدفعات الخمس لمتجر التتبع.

---

## خطة رجال التوصيل على صفحة الطلبيات: المواضع الأربعة + حصرية «شركة/رجل» + إرسال لرجل (سبتمبر 2026) ✅

**بقرارات المستخدم المعتمدة:** النطاق = المواضع الأربعة على صفحة الطلبيات (الخلية المباشرة، نموذج الإضافة/التعديل، مودال التعديل السريع للتوصيل، درج التأكيد) + مطابقة ذلك على صفحة التتبع؛ المكتب يبقى اختياريًا مع الرجل لكنه **مخفي في الواجهة عند اختيار رجل**؛ العرض مقسّم «شركة/رجل»؛ تنفيذ كامل.

- **المواضع الأربعة (`orders/index.blade.php`):** كلها صارت تتعامل عبر **معرّف إرسال واحد** — «شركة» أو «رجل» — مع حصرية كاملة:
  - الخلية المباشرة: `openOrderProviderEdit` عند اختيار رجل يصفّر `shipping_provider_id`+`stopdesk_point_id` (خطأ `partner_exclusive` إن بقي الاثنان)؛ محرر الـ stopdesk لا يظهر إلا مع شركة.
  - نموذج الإضافة/التعديل (`submitCreate`/`submitEdit`): عند حشو الرجل تُصفَّر الشركة/المكتب؛ فحص stopdesk في `submitCreate` يستخدم `$this->addError('stopdesk_point_id', __('merchant_panel.office_required_for_stopdesk'))` (واجهة `OrderOfficeSelectionTest` تتطلب `assertHasErrors`).
  - مودال التعديل السريع للتوصيل (`openDelivery`/`saveDelivery`): نفس قاعدة الاستبدال الحصرية.
  - درج التأكيد (`submitConfirmAndSend`): يحل الشريك من الدرج قبل أي فحص، ثم يثبّت الساق مسبقًا (persist) ليمر بفحص الاكتمال، وإرسال ساق الرجل عبر `OrderShippingGateway::send(providerId: null)` ثم `ensureRiderTracking($fresh, generateRiderTrackingNumber($fresh))` — توليد فوري لرقم `HM-`/`SD-` (حسب `delivery_type`) عند الإرسال.
- **القطع المشتركة:** `partials/partner-picker.blade.php` (جديد — picker موحّد `$picker = 'form'|'confirm'` مع إخفاء المكتب عند الرجل، واختصار «شركة واحدة» `data-edz-company-single` بلا `edz-company-select` كما يتطلب `OrdersDefaultProviderTest`) + `partials/confirm-drawer.blade.php` (جديد — درجة التأكيد المستخرجة مع `\App\Enums\Store\StorePermissionEnum::…` بـ FQCN — الرمز القصير كسر التحقق).
- **ساق الرجل في `OrderCompleteness(forSend)`:** تتطلب **العنوان دائمًا** حتى لساق stopdesk (عكس ضلع الشركة) ولا تتطلب مكتبًا؛ `blank(provider) && blank(rider)` يرفض الإرسال برسالة `confirm_requires_partner` (سلوك جديد رُفِق بإرخاء اختبار سابق ليقبل الشريك أو تحذير items).
- **صفحة التتبع (المطابقة):** `orders/index`'s `order-form-modal`/`delivery-edit-modal` تُضمَّن عبرها أيضًا — حالة جديدة `delivery_rider_id`/`formPartnerType`/`riderOptions` + `switchFormPartner()` في `TrackingRiderFormConcern` (نفس الحصرية) + `TrackingColumnConcern::loadRiderOptions()` يملأ `riderOptions` (نشطون فقط، شكل value/label/hint/kind مطابق).
- **الأدلة:** `php -l` نظيف على كل الملفات المتغيِّرة (أربعة PHP + اللغات ×4) + `view:cache` success عبر **PHP 8.3.28** (`C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe` — `php` الافتراضي 8.2.12 يفشل قيد Composer ≥8.3). **`OrderCompletenessTest` 22 ناجحًا (46 تأكيدًا)** — منها الجديد: بادئتا HM/SD، عدم تصادم `generateRiderTrackingNumber`، ساق رجل stopdesk تتطلب العنوان لا المكتب، رجل بلا عنوان غير مكتمل عند الإرسال، confirm-and-send لرجل عبر Volt (shipped + `HM-` + حصرية)، ورفض رجل غير نشط/أجنبي. **`OrdersDefaultProviderTest` + `OrderOfficeSelectionTest` 27 ناجحًا (86 تأكيدًا)**. **الجولة الكاملة `Order` + `Merchant`: 398 ناجحًا (1544 تأكيدًا) — صفر انحدار.**

> **يتطلب تحققًا بصريًا يدويًا من المستخدم:** على 375/768/1440 — المواضع الأربعة تعرض اختيار «شركة/رجل» مع الحصرية (اختيار أحدهما يصفّر الآخر)، وإخفاء المكتب عند الرجل، وإرسال طلبية لرجل يولّد رقم تتبع HM/SD فورًا ويغلق الدرج، ومطابقة السلوك نفسه على صفحة التتبع.

---

## مكوّن مسح الباركود الموحّد `x-edz.barcode-scan-input` — أساس مشترك (المرحلة 36 + المرحلة E.3) (سبتمبر 2026) ✅

**النطاق:** مكوّن أساس (foundation) خالٍ من أي منطق تجاري، يُستهلك لاحقًا من مرحلة 36 (التحقق من إرسال NOEST بالباركود — المُشار إليها سابقًا بـ«Phase 32» في سطر إعادة تصميم التتبع) ومن المرحلة E.3 (كتابة حالة الرجل). لم يُربط بأي مستهلك بعد في هذا الإدخال.

- **المكوّن `resources/views/components/edz/barcode-scan-input.blade.php`:** خصائص `wire-scan-method` (اسم طريقة Livewire يُستدعى عند كل مسح — نقطة دخول واحدة للنص والكاميرا)، `placeholder`، `label`، `autofocus` (افتراضي false). حقل النص يقلّد النمط المعتمد في `returns/index` (سطر ~161) حرفيًا: `wire:model.live` عبر `$attributes` + `@keydown.enter.prevent="$wire.<method>($event.target.value); $event.target.value = ''"` + `autofocus`، والاسم محروس بـ `^[A-Za-z_][A-Za-z0-9_]*$` فيبقى الاسم غير الصالح خاملًا (لا كسر/حقن). زر كاميرا بجانبه يفتح `x-edz.modal` (نفس بطاقات/ورقة سفلية بقية التطبيق على 375/768).
- **الخلفية `resources/js/components/barcode-scan-input.js`** (مُسجَّل `Alpine.data("barcodeScanInput", ...)` في `panel.js`): الكاميرا تُركَّب عبر `x-if` فقط عند الطلب؛ **`import("html5-qrcode")` ديناميكي عند أول فتح كاميرا فقط — أبدًا في رأس `panel.js`**؛ كاميرا خلفية `facingMode: { exact: "environment" }` مع سلسلة تراجع environment→user، تعداد الكاميرات (`Html5Qrcode.getCameras()`) ومنتقي جهاز يظهر فقط عند وجود أكثر من كاميرا، `formatsToSupport` = CODE_128/CODE_39/EAN_13/EAN_8/QR_CODE (ملصقات الناقل الجزائرية Code128/EAN تبقى مدعومة)، debounce 1500ms ضد الفك المكرر، **إيقاف الدفق عند أي إغلاق** (نجاح أو إلغاء — بطارية/خصوصية) مع استعادة `body overflow` عند إزالة `x-if` (مسار المسح الناجح يتجاوز watch المودال)، منطقة الكاميرا محمية بـ `wire:ignore` من morph دورات Livewire، ومحاذرة `_abortFlag`/`_waitForContainer` لكل نقاط الـ await.
- **أيقونة `camera`** أُضيفت إلى خريطة `icon.blade.php` بنمط Heroicons outline 24px نفسه (لاحقًا لـ `qr-code`).
- **الترجمات `edz.php` ×4 (en/ar/es/fr):** `scan_with_camera`/`starting_camera`/`camera_unavailable`/`switch_camera`/`auto_camera`.
- **الحزمة:** `html5-qrcode@^2.3.8` في `dependencies` — **chunk مستقل 375.46 kB (gzip 110.72 kB) يُجلب عند أول فتح كاميرا فقط**؛ manifest يؤكد `dynamicImports` من panel.js ولا يضمها للحزمة الأساس. panel.js نما 3.13 kB (gzip +1.06 kB) لجلو المكوّن (قياس فعلي بـ build قبل/بعد: 236.69→239.82 kB قبل إصلاحي الجلو الصغيرين، ثم 240.02 kB بعد `_waitForContainer`/overflow).
- **الأدلة:** `php -l` نظيف على ملفات اللغة الأربعة + `view:cache` success عبر PHP 8.3.28 + `npm run build` نظيف (chunk html5-qrcode منفصل + dynamicImports مؤكَّد). لا تغيير على `resources/views/components/edz/barcode.blade.php` (مولّد SVG Code128 لطباعة الملصق — غير ذي صلة).

> **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** على 375/768/1440 — عاصفة مفاتيح قارئ الأجهزة في حقل النص، إذن الكاميرا، فك 1D (Code128/EAN)/QR، إطفاء مؤشر الكاميرا/الميكروفون عند الإغلاق، ومنتقي الأجهزة متعدد الكاميرات. **المرحلة 36 والمرحلة E.3 تحصلان على إدخالهما الخاص عند الاستهلاك (لا يُبنى فيهما الآن).**

---

## مرحلة إعادة تصميم معالج إنشاء المنتج — المرحلة الفرعية 1 ✅ (محرك الخطوات الديناميكي + استخراج الأجزاء + التنقل الحر المتحقق) — 2026-09-11

**السيناريو:** السيناريو B المعتمد (محرك خطوات مرن) — المرحلة الفرعية 1 من 5. ترحيل شكلي صِرْف (like-for-like) — **لا** تغيير في محتوى/قواعد الخطوات، **لا** خطوات جديدة، **لا** جدول مسودات، **لا** تغيير في ProductService. `ARCHITECTURE_RULES.md` غير موجود — `livewire-conventions.md` هو المرجع الملزم.

**ما تَمّ:**
- **`app/Domains/Product/Support/ProductWizardSteps.php` (جديد):** مصدر حقيقة وحيد لتعريفات الخطوات — ثوابت `STEP_BASIC=1…STEP_REVIEW=5` + `LAST_STEP`، `all()` (id/label/icon/partial)، `ids()`، `count()`، `isExistingStep()`، و`rulesFor($step, $context)` يُعيد قواعد التحقق لكل خطوة عبر `match` مع سياق `store_id`/`product_id`/`min_order_qty` — **المرجع الوحيد** للقواعد (استُخرجت من تدفق `$stepRules` القديم في `form.blade.php` حرفيًا؛ إغلاق `max_order_qty` يستخدم `$minOrderQty` من الكونتكست بدل `$this->min_order_qty` — سلوك مطابق لأن القيم تُقيَّم وقت استدعاء السياق). أُزيل استيراد `ProductOptionInputType` غير المستعمل + مساعد `hasRules()`. الإشارة الأولى في `app/Domains/Product/` (كان فارغًا سابقًا).
- **استخراج الأجزاء (5 ملفات):** `resources/views/livewire/merchant/products/form/step-{basic,pricing,options,inventory,review}.blade.php` — كل جزء يحوي حلقة `x-show="step === N"` + `x-transition.opacity` الخاصة به، مطابق تمامًا للكتل المضمّنة السابقة (أسطر ~572–1107). القرار: المسار المذكور في المهمة `products/form/` (سابقة: `storefront-settings.blade.php` + `storefront-settings/` يتعايشان والأدلة `<module>/partials/` + مسارات فرعية غير `partials` كـ `storefront-settings/fields/`). تستخدم FQCN حيث يلزم (تُثبت عبر `tracking/partials/tracking-tabs`).
- **`form.blade.php` إعادة توجيه المحرك:** إضافة `validated_steps` للدولة (array) — في الوضع **الجادل/الإنشاء يبدأ فارغًا**، وفي **وضع التعديل يُبذر بكامل `ProductWizardSteps::ids()`** (منتجات قائمة اجتازت التحقق فعلاً ⇒ بقاء التنقل الحر دون انحدار في التعديل) — `$stepRules` تفوض لـ `rulesFor` بسياق كامل، `$nextStep` يبذّر/يحتفظ بالخطوة الحالية في `validated_steps` ثم يتقدم بـ `min(ProductWizardSteps::count(), …)`، **`$goToStep` أعيدت كتابته** كله: خطوة غير موجودة/نفس الحالية ← no-op؛ الخطوة المقفلة ← `dispatch('swal', info, step_locked_title/step_locked_text)` + إرجاع (لا تنقّل، لا تحديث حالة)؛ خطوة مفتوحة غير متحققة ← تشغيل محلل تحقق الخطوة المستهدفة وانتظار `ValidationException` (يملأ كيس الأخطاء وتظل الخطوة)؛ ثم تسجيل `validated_steps` والانتقال. `$isStepUnlocked` (protect) — الخطوة N مفتوحة iff كل خطوة < N في `validated_steps` (الخطوة 1 دائمًا). `$lockedSteps` computed — أرقام الخطوات غير المفتوحة. `$wizardSteps` computed ← `array_values(ProductWizardSteps::all())`.
- **الاستبدال:** كتل الخطوات الخمس المضمّنة استُبدلت بـ `@foreach ($this->wizardSteps as $wizardStep) @include($wizardStep['partial']) @endforeach`؛ `:lockedSteps` مرر إلى `<x-merchant.wizard-steps>`.
- **`wizard-steps.blade.php` (component):** يقبل `steps`/`currentStep`/`lockedSteps`؛ يستخدم `$step['id']`؛ الخطوات المفتوحة أزرار `@click="$wire.goToStep(id)"`، والمقفلة `<span aria-disabled>` مع أيقونة `lock-closed` (مفعّلة في `edz/icon` سابقًا)؛ تنظيف التوكنز: `ring-brand-100 dark:ring-brand-900/40`→`ring-brand-ring`، `text-brand-700 dark:text-brand-300`→`text-brand-fg`، `text-success-700 dark:text-success-300`→`text-success-fg`، `bg-success-300 dark:bg-success-700`→`bg-success-fg` — **صفر `dark:`/hex خام** (التوكنز أثبتت في `tailwind.config.js` + `_tokens.scss`).
- **مفاتيح ×4 لغات:** `products.step_locked_title`/`step_locked_text` (بعد `step_review_desc`) في en/ar/fr/es.

**أدلة/sub-proofs:**
- **الاختبارات (جديدة):** `tests/Feature/Merchant/ProductWizardStepEngineTest.php` — 4 ناجحة: ① عرض الخطوات الخمس الديناميكية بالترتيب القانوني (يُفحص مواضع `strpos` بتسلسل؛ `&` أُفلتت إلى `&amp;` كسلوك Blade) ② مبكرة عند الإقلاع `goToStep(4)` = no-op + `swal` + الحالة بلا تغير وبلا أخطاء ③ الفتح التدريجي: nextStep من الخطوة 1 يفتح 2 فقط، الخطوة 3 مقفلة حتى تُتحقق 1 و2، الانتقال للخلف للأمام محفوظ ④ التعديل يفتح كل الخطوات (لا swal عند القفز للخطوة 5). **417 ناجح (1647 assertions)** في `tests/Feature/Merchant` — صفر انحدار (410 سابقة + 7 منتج/شيفرة... تُحسب داخليًا في السويت).
- **خصلة الاستعلام** (سُجلت كملاحظة): Livewire رندِر إعادة الترتيب يضرب قاعدة البيانات (سجل 19 استعلامًا في `nextStep`) حتى مع فتح/قفل goToStep — هذا عبء على إطار Livewire للتحديث/الجلب وليس منطق خطوتنا؛ التصميم لا يضيف أي استعلام تطبيقي لكل نقرة تنقل. (فحص استعلامات الاستراحة أُزيح — لا قيمة مع Livewire.)
- **التجاوب (معتمد على DOM — لا أدوات E2E في repo):** `wizard-steps` يحفظ نقاط التوقف السابقة بالضبط: في ≤640 التسميات `hidden sm:inline` (أرقام/أيقونات فقط)، موصل الخط `flex-1` + `mx-3` يمتد دائماً، والأزرار `h-9 w-9` (36px ≥ حد 44 توشر اللمس هو صفوف الأزرار الفردية — سجل: حجم الزمن لا يتغير). الأجزاء تحمل القوالب المتجاوبة نفسها (grids/edz-card) لا تُلمَس. التحقق اليدوي عند 375/768/1440 مطلوب من المستخدم في النهاية.
- **التوثيق:** هذا القسم في `Todos.md` + ملاحظة `APP/actions/Product/*` غير مستعملة ما زالت تُعيد العلامة (خارج النطاق في هذه المرحلة الفرعية؛ الفروع 2×5 ستستهلكها — أعمل بها قائمة انتظار).

**ملاحظة:** `docs/livewire-conventions.md` — أسطر فارغة تُركت بدون قصد في السطر 128 (من جولة سابقة للترجمة)؛ لا تغيير فني. `resources/lang` الإضافات مفتاحان فقط لكل لغة. لا تغيير في أي شيفرة تحقق/منتج/طلبيات في هذه المرحلة الفرعية.

---

## مرحلة إعادة تصميم معالج إنشاء المنتج — المرحلة الفرعية 2 ✅ (خطوة صور مخصصة) — 2026-09-12

**السيناريو:** المرحلة الفرعية 2 من 5 على السيناريو B المعتمد — ترحيل فتح/تسجيل خطوة صور مخصصة داخل المحرك الديناميكي القائم (المرحلة الفرعية 1 **مؤكدة** ولم يُمَس محركها: `goToStep`/`isStepUnlocked`/`validated_steps` في `form.blade.php` بلا تغيير). **لا** تكامل primary-image (قرار المستخدم: «Relocation only» — أُشير له أدناه)، **لا** سحب وإعادة ترتيب، **لا** رؤية شرطية للخطوات (المرحلة 3)، **لا** إنشاء خيارات/قيم inline (المرحلة 4)، **لا** تصدير/استيراد (المرحلة 5).

**ما تَمّ:**
- **`ProductWizardSteps.php`:** ثابت جديد `STEP_IMAGES = 2` مدخلاة بعد `STEP_BASIC` مباشرة، و«إعادة تسمية» البقية:. `STEP_PRICING=3`، `STEP_OPTIONS=4`، `STEP_INVENTORY=5`، `STEP_REVIEW=6`، `LAST_STEP = STEP_REVIEW`. أُضيف إدخال `step_images` في `all()` (icon `image` — موجودة في `edz/icon`، partial `livewire.merchant.products.form.step-images`) و`rulesFor(STEP_IMAGES) => []` (nullable — بلا منتج إلزامي للصور؛ تحقق فعلي: `ProductService::syncImages` يستقبل مصفوفة فارغة بلا شرط، و`form.blade.php` لحفظ `$data['images']` لا يفرض أي قاعدة `required`). المحرك القائم (نموذج التحقق/التنقل/`@include` loop) بلا تغيير — **no new queries** (تعديل تعريف/نقل عرض فقط، صفر استدعاءات Eloquent جديدة).
- **`form/step-images.blade.php` (جديد، 31 سطر):** نفس نمط `x-show="step === 2"` + `x-transition.opacity` والبنية `grid grid-cols-1 gap-6 xl:grid-cols-3` مع `xl:col-span-2` كباقي الأجزاء. المحتوى = كتلة الصور المنقولة حرفيًا من `step-basic` (حقل الأدخال `wire:model="newImages" multiple accept="image/*"` + شبكة الإطار المصغرة `grid grid-cols-2 gap-3` + زرا `removeImage(index)`/`removeNewImage(index)`). **لا تغيير** في أسماء الحالة `images`/`newImages` ولا في الإجراءين ولا في تدفق الحفظ عند `save()`.
- **`form/step-basic.blade.php` (119 ← 79 سطر):** أزيلت كتلة الصور نهائيًا (حتت el card sidebar) وبقي الحقل الأساسي فقط (name, slug, brand, unit, categories, short_description, description, is_active, is_featured) مع إبقاء حارس `x-show="step === 1"`.
- **إعادة ترقيم حرّاس الأجزاء:** `step-pricing` `=== 2`→`=== 3`، `step-options` `=== 3`→`=== 4`، `step-inventory` `=== 4`→`=== 5`، `step-review` `=== 5`→`=== 6`.
- **`form.blade.php` الأزرار:** حارسا التنقل **لم يعودا مكتوبًا يدويًا** — `step < 5`→`step < {{ ProductWizardSteps::count() }}` و`step === 5`→`step === {{ ProductWizardSteps::LAST_STEP }}` (مصدر ديناميكي واحد، لا إعادة ترقيم مستقبلية).
- **`wizard-steps.blade.php`:** **صفر تغيير** — يُجمع الويلية تلقائيًا من `ProductWizardSteps::all()` (يتحقق: تبويب «Images» في الترتيب رقم 2 تلقائيًا بلا تعديل مكوّن).
- **مفاتيح ×4 لغات:** `products.step_images` + `step_images_desc`، تُدرجت أبجديًا بين `step_basic_info_desc` و`step_inventory` في en/ar/fr/es. يوجد مسبقًا `products.images`/`images_hint`/`images_label`.

**قرار flagged (أكّده المستخدم):** `ProductImage` يحتوي أعمدة `is_primary`+`sort_order` (موجودة، `Product::primaryImage()` علاقة MorphOne) لكن `ProductService::syncImages()` يكتب **فقط** `sort_order` (= ترتيب المصفوفة، فهي أصلًا أول صورة = primary) ويترك `is_primary` بلا استخدام في الـ wizard — ومفتاح `products.images_hint` («The first image is used as the primary product image.») يوثّق هذا السلوك الصريح. **القرار: Relocation only — لم يُربط `is_primary` في هذه المرحلة** (خارج النطاق؛ أُشير له ليُدمج في مرحلة لاحقة).

**أدلة/sub-proofs:**
- **الاختبارات:** `ProductWizardStepEngineTest.php` — 5 ناجحة (كانت 4): ① علامة الاختبار أصبحت «six dynamic steps» (كانت «five») — تُفحص الآن داخل كتلة `<nav aria-label="Progress">…</nav>` فقط (إصلاح إيجابي كاذب: مفتاح حالة Livewire `newImages` يحتوي «Images» فيظهر قبل التبويب — الاختبار السابق أعتمد على الـ`strpos` في كامل الـ html فعثر على مثال سابق؛ الآن يفحص nav حصريًا = مصدر ترتيب التبويبات الحقيقي) ② خطوة مقفلة كقبل + جديدة: `goToStep(STEP_IMAGES)` عند الإقلاع فارغًا محجوب أيضًا ③ فتح تدريجي مع الصور: after nextStep من 1 → الحالي = `STEP_IMAGES`، ثم `goToStep(STEP_PRICING)` مقفول، ثم `nextStep` (قواعد صور فارغة تمر) → يفتح `STEP_PRICING` = الوضع/الفتح الصحيح للصور، ثم مسار التنقل الحر الكامل حتى `STEP_OPTIONS` ④ التعديل يفتح الكل ⑤ **جديد:** «the images step renders persisted images and the upload input» — `images: [...]` تُعرض (img URLs من `Storage::disk('public')->url`) + `type="file"` + `accept="image/*"` + `wire:model="newImages"`. **418 ناجح (1661 assertions)** في `tests/Feature/Merchant` (417 سابقة + 1 جديد) — صفر انحدار. `php -l` نظيف على كل الملفات المتأثرة + `view:cache` ناجح (ثم `view:clear`).
- **التجاوب (DOM-grounded — بلا أدوات E2E في repo):** ① **375px:** تسميات التبويبات `hidden sm:inline` (موروث من المرحلة 1) ⇒ أرقام/أيقونات فقط + أرقام ×6 تناسب؛ شبكة الصور `grid grid-cols-2 gap-3` في عمود واحد (لا حاجة لتركيب عند الضيق)؛ حقل الملف `block w-full` مع `accept=image/*` يعمل على الشاشات الضيقة. ② **768px:** التسميات تظهر عند `≥640px` (`sm:inline`) بستة تبويبات في النافبار؛ الشبكة تبقى عمودين. ③ **1440px:** النافبار الكامل بستة تسميات، وشبكة الصور عمودين داخل بطاقة `xl:col-span-2`. كلها غير متغيرة سلوكيًا عن قبل النقل — التحقق اليدوي البصري النهائي (مطلوب من المستخدم) سيدرج عند المرحلة 5.
- **الأداء:** صفر استعلامات جديدة (تعريف + نقل عرض). تكلفة إعادة رندِر Livewire لمنطقة الصور مطابقة لما قبل — نفس الحقول/الـ wire النماذج (الصور `newImages` ترفع عبر Livewire المواصفات كما كانت).
- **التوثيق:** هذا القسم في `Todos.md` + إعادة إشارة `app/Actions/Product/*` غير المستعملة (مهمة المرحلة 5). `docs/livewire-conventions.md` ملفوف — لا تغيير هنا (سجّل في المرحلة 1 أن ~3 أسطر فارغة بلا معنى وُجدت فيه من جولة سابقة — لم تُلمَس).

---

## إصلاح عاجل لمحرك معالج المنتج: مطابقة تحقق SKU بين الخطوة والمستوى الخدمي + القفز التلقائي لخطوة الخطأ + ميزة: صورة لكل متغيّر (v1) — 2026-09-12

**السيناريو:** خطوتان على السيناريو B المعتمد (المحرك الديناميكي 6 خطوات، المرحلتان الفرعيتان 1+2 **مؤكدتان** بلا مُسّ محركهما). **الجزء A — إصلاح عاجل:** (a) تحقق الخطوة يطابق تحقق `ProductService` — SKU يصبح مطلوبًا على مستوى خطوة Inventory عندما تكون التوليد التلقائي متوقفة. (b) تمكين التوليد التلقائي يزيل الحجب. (c) أي `ValidationException` في وقت الحفظ يقفز `currentStep` تلقائيًا إلى الخطوة المالكة للحقل الفاشل. **الجزء B — ميزة (v1):** صورة واحدة لكل متغيّر داخل جدول المتغيرات الحالي (خطوة Options) عبر الجدول متعددي الأشكال `product_images` القائم — **لا** تغيير في المخطط، **لا** خطوة جديدة، **لا** معرض/متعدد صور، **لا** سحب وإعادة ترتيب.

**ما تَمّ:**
- **الجزء A-1 — `ProductWizardSteps::rulesFor`:** قاعدة `sku` في `STEP_INVENTORY` تحوّلت من `nullable…` إلى `[string, max:255, unique…, Rule::requiredIf(fn () => ! ($context['auto_generate_sku'] ?? false))]` — مورد السياق زاد `auto_generate_sku` في توقيع docblock، و`$stepRules` في `form.blade.php` يمرر `'auto_generate_sku' => (bool) $this->auto_generate_sku` (الفحص في `nextStep`/`goToStep` يمر عبر `$this->all()` فيتضمن الخاصية). **سلوك مطابق تمامًا لـ `ProductService::create()/update()`** (يحصل `$baseSku` فارغًا ويقذف `sku_required`) — التناقض السابق (اسمح بقطع المرور ثم فشل الحفظ) زال. `barcode` ظل `nullable` (بلا تغيير في سلوك الباركود).
- **الجزء A-2 — `ProductWizardSteps::stepForField(string $field): ?int`:** خريطة `match` — `name/slug/brand_id/categories/short_description/description/unit/meta_title/meta_description/is_active/is_featured/primary_category_id` → `STEP_BASIC`؛ `price/compare_price/cost_price/min_order_qty/max_order_qty` → `STEP_PRICING`؛ `str_starts_with($field,'variants_preview')` → `STEP_OPTIONS`؛ `stock/low_stock_threshold/sku/barcode` → `STEP_INVENTORY`؛ غيره `null`.
- **الجزء A-2 — `save()` القفز التلقائي:** أُعيد هيكلة التدفق كله (المصادقة المسطّحة `$v->validate()` + بناء `$data` + `create()/update()`) داخل `try` واحد بإمساكين: `catch (\DomainException)` (كما قبل — `dispatch('swal', error)`) + `catch (ValidationException $e)` الجديد — `$field = array_key_first($e->errors())`، إن وُجدت خطوة مالكة → `$this->currentStep = ProductWizardSteps::stepForField($field)` ثم **`throw $e`** (نعيد رميه: `SupportValidation::exception` يضع errorBag ويثبّت stopPropagation، `SupportTesting::exception` يخزّن المفحّص لـ `assertHasErrors` — رفعت الدولة والتفتيش الخاطئ يظهر على نفس الخطوة مع الحفاظ على `validated_steps`). استيراد `Illuminate\Validation\ValidationException`.
- **تحسين UX:** `nextStep` الناجح يستدعي `$this->resetErrorBag()` — الشارات/الأخطاء العالقة من محاولة فاشلة لا تلتصق بعد التصحيح (يستخدم `Livewire\Component::resetErrorBag`).
- **الجزء B — الحالة:** أُضيف مفتاحان لكل صف `variants_preview`: `image` (string|null — مسار الصورة المخزّنة؛ يُملأ من `$variant->primaryImage()`**) و`new_image` (`TemporaryUploadedFile|null` عبر `WithFileUploads` كـ `newImages`). `VariantPreviewBuilder` **بقي نقيًا** — الدمج وقت التشغيل: `$rebuildPreview` يدمج `['image' => null, 'new_image' => null]` لكل صف بعد البناء؛ `buildEditFormData` يحقن `image`/`new_image` لكل متغيّر؛ `fillPreviewFromExisting` (إعادة توليد عند optionsChanged) ينقل `path` من `$variant->images->firstWhere('is_primary', true)` للصفوف المطابقة.
- **الجزء B — الواجهة (`step-options.blade.php`):** عمود «Image» مضغوط (بعد عمود Variant مباشرة): صورة مصغّرة `h-10 w-10 rounded-md border` تُظهر `temporaryUrl()` للرفع الجديد أو `Storage::disk('public')->url(path)` للمخزّن أو **عنصر أيقونة كاميرا محايد** عند الغياب (لا `<img>` مكسور)، إدخال ملف `sr-only accept="image/*" wire:model="variants_preview.{{ $index }}.new_image"` فوق الإطار، وزر إزالة `x-mark` (يظهر فقط عند وجود صورة) → إجراء `removeVariantImage($index)` يصفّر المفتاحين. يستخدم التوكنز القائمة فقط (`border-surface-border`, `bg-surface-secondary/60`, `text-ink-muted`, `danger-soft`) — **صفر ألوان/هاكس جديدة**، والجدول حافظ `overflow-x-auto`.
- **الجزء B — الحفظ (`ProductService`):** `syncVariants()` — بعد `variants()->create([...])` إن وُجد `new_image` → `store('products','public')` + `images()->create(['path'…,'store_id'=>$product->store_id,'is_primary'=>true,'sort_order'=>0])` (نفس نمط `newImages` في `save()`). `form.blade.php::syncExistingVariants` (مسار التعديل options-unchanged) — يفرّق الصورة **ضمن سلسلة diff القذرة**: eager-load `['images','optionValues']` على `$dbVariants`؛ إن `new_image` → `delete`+`create`؛ أو إن وُجدت مخزّنة و`blank($row['image'])` → `delete` (إزالة)؛ **غير المتغيّر صفر استعلامات صور**. مسارا combos المتكررة/المفتاحية يحلّان من المجموعة المحمّلة (`$variantsById`) بدل `find()` لكل صف — أُزيل N+1 القائم مسبقًا. `update()` — قبل كل `variants()->delete()` (optionsChanged/تحويل variable→simple) يستدعي `deleteVariantImages($product)` الجديد (حذف صفوف `product_images` لمتغيرات المنتج — `product_images.imageable_id` بلا FK/cascade ⇒ بلا تنظيف تُترك يتامى).
- **مفاتيح ×4 لغات:** `products.variant_image` (بعد `variant_count`) و`products.remove_variant_image` (بعد `remove_option`) في en/ar/fr/es (es أُضيفت كمدخلين جددين بجوار `variant_product` لعدم وجودهما فيه).

**أدلة/sub-proofs:**
- **الاختبارات (`ProductWizardStepEngineTest.php` — 12 ناجحة، كانت 5):** الجزء A: ① «empty sku with auto-generation off blocks advancing past inventory» — المشي إلى Inventory ثم `auto_generate_sku=false` + `nextStep` → يبقى على Inventory + `validated_steps` لا يشملها + `assertHasErrors(['sku'])` + الـ html يعرض `edz-field__error` ② «enabling automatic sku generation clears the inventory block» — الفشل ثم التمكين + `nextStep` → `STEP_REVIEW` + `assertHasNoErrors(['sku'])` (بفضل `resetErrorBag`) ③ «save-time sku failure jumps back to the inventory step» — تعديل منتج + `goToStep(REVIEW)` ثم `auto_generate_sku=false` + `sku=''` + `call('save')` → `currentStep === STEP_INVENTORY` + `assertHasErrors(['sku'])`. **الجزء B:** ④ «create» — منتج متعدد 2 متغيّر، رفع صورة للصف 0 فقط → سطر `product_images` واحد مقيّد بمعرّف المتغيّر ذا القيمة S والمتغيّر الآخر بصفر ⑤ «edit/replace» — تعديل يحمّل `variants_preview.0.image` + `upload()` على الصف 0 + `save()` → السجل القديم حُذف (count=1، path ≠ القديم) والمتغيّر 1 بلا صور ⑥ «placeholder» — متغيّر بلا صور يعرض مدخلات الملف للصفّين + عنوان العمود + `overflow-x-auto` + **لا** `removeVariantImage` ولا `temporaryUrl(` (لا مصغّرات مكسورة) ⑦ «N+1» — متغيّرات غير مغيّرة أثناء `save()`: بالضبط استعلام `product_images` **واحد** (eager-load `variants.images`) — لا استعلامات لكل متغيّر. **يشكّلان معًا 18 ناجحًا (102 assertions)** مع `ProductSkuBarcodeTest` (يُشغَّلان معًا — `skuUser()`/`dataShape()` معرّفا هناك؛ وحدهما يفشل «Call to undefined function skuUser()» بالتصميم). **425 ناجح (1699 assertions)** في `tests/Feature/Merchant` (418 سابقة + 7 جديدة) — صفر انحدار. `php -l` نظيف على كل ملفات PHP/blade/lang المتأثرة + `view:cache` ناجح (ثم `view:clear`).
- **التجاوب (DOM-grounded — بلا أدوات E2E):** ① **375px:** خطأ SKU يظهر **داخل نفس حقل الإدخال** (`@error('sku')` تحت input في بطاقة Codes غير المحجوبة — دائمًا فوق/أو أسفل زرّي التنقل؟ لا — داخل `edz-field` في سطرها الخاص) — يمكن رؤيته دون تمرير إضافي؛ عمود الصورة `h-10 w-10` مضغوط والجدول يحفظ `overflow-x-auto` (أثبته الاختبار ⑥ بحضور الـ class) — لا كسر للشبكة ② **768px:** عمود الصورة معبّر والجدول قابل للتمرير أفقيًا عند الحاجة ③ **1440px:** عمود واحد ضيّق لا يزيد عرض الجدول الفعّال (التمرير الأفقي يحوي النمو الزائد عن العرض). التحقق اليدوي البصري النهائي (مطلوب من المستخدم) يُدرج عند المرحلة 5.
- **الأداء:** `buildEditFormData` يضيف `variants.images` إلى قائمة eager-load الحالية (لا N+1 عند قراءة primary image). `syncExistingVariants`: صفر استعلامات صور للمتغيرات غير المغيّرة (أُثبت بالاختبار ⑦)؛ المتغيّر المتغيّر = delete+create فقط. تحسّن جانبي: `variantsById` بدل `variants()->find()` لكل صف قضى على N+1 استعلامات المتغيرات القديم.
- **التوثيق:** هذا القسم في `Todos.md`. لم تُمسّ `app/Actions/Product/*` (مهمة المرحلة 5). المراحل الفرعية 3 (رؤية خطوات Options شرطية حسب `has_variants`) و4 (إنشاء خيارات/قيم inline) تبقى معلّقة كبرومبتات منفصلة.

---

## إنجاز المرحلة الفرعية 3 من 5: إظهار/إخفاء خطوة Options شرطيًا حسب `has_variants` — 2026-09-12

**النطاق:** عند `$has_variants = false` تختفي تبويبة Options كليًا من المعالج فيصبح المسار: Basic → Images → Pricing → Inventory → Review (5 خطوات). عند تفعيل المتغيرات تعود التبويبة لموقعها الطبيعي (بين Pricing وInventory). الثوابت نفسها لا تتغير (`STEP_OPTIONS` يبقى 4) — فقط عضوية الخطوة في التسلسل المرئي تتغير.

**النقاط الرئيسية:**
- **المرئية في `ProductWizardSteps`:** `visible(array $context)` = `all()` مع `unset($steps[self::STEP_OPTIONS])` عند غياب `has_variants`؛ `visibleIds()` (مفاتيح القائمة المرئية)؛ `isStepVisible(int, context)`؛ `firstVisibleStepBefore(int, context)` (أقرب خطوة مرئية قبل خطوة معينة، للخروج من خطوة اختفت). `all()`/`ids()`/`count()`/`isExistingStep()` بقيت كما هي (القائمة الكاملة يحتاجها بعض المستدعين مثل Mount الإصدار). سياق الاستدعاء بنفس شكل `['has_variants' => (bool) $this->has_variants]`.
- **`form.blade.php`:** `$wizardSteps` = `visible($context)`؛ `$lockedSteps` ≈ `visibleIds($context)`؛ `$isStepUnlocked` يتحقق `isStepVisible` ثم يمرّ على `visibleIds` فقط؛ `nextStep`/`prevStep` عبر مساعد مشترك `adjacentVisibleStep($from, $direction)` (يبحث عن الفهرس في `visibleIds` ويحرّك موضعًا، يرجع null عند الحدود ← يثبت الخطوة) بدل ±1 الأعمى؛ `goToStep` يعامل الخطوة المخفية كخطوة غير موجودة (no-op صامت بلا toast)؛ خطاف `has_variants` عند إيقاف التشغيل: إن كان `currentStep === STEP_OPTIONS` ينقل إلى `firstVisibleStepBefore(STEP_OPTIONS, ctx) ?? STEP_PRICING` ويحذف `STEP_OPTIONS` من `validated_steps` (خطوة مخفية لا تُعد "مُتحققًا منها") ثم يصفّر `options`/`variants_preview`/`options_changed` كالسابق. إعادة التفعيل تعيد التبويبة فقط دون إعادة تحقق من الخطوات الماضية (قواعد قفل `isStepUnlocked` القائمة).
- **`form/step-options.blade.php`:** حُذفت فرع `@else` (الـ placeholder الذي أصبح ميتًا) وبقي `@if ($has_variants)` درعًا دفاعيًا — لأن `@foreach ($this->wizardSteps as $wizardStep) @include(...)` سيرفر-سايد ولا يعني بالخطوة المخفية أصلًا (لا يُضمّن partial غير المرئي أبدًا في DOM، خلافًا لـ `x-show` الذي يبقي العناصر معدّلة).
- **`wizard-steps.blade.php`:** بلا تغيير — يكرّر `:steps` الممرّرة؛ `$wizardSteps` أقصر (=5) والـ flex (`flex-1` لكل `<li>` عدا الأخير) يتكيف تلقائيًا. أزرار التنقل في `form.blade.php` تبقى `step < {{ count() }}` و`step === {{ LAST_STEP }}` (=6 دائمًا) — صحيحة لأن Review (6) هو دائمًا آخر خطوة مرئية وفي كلا المسارين.

**بروهات:**
- **الاختبارات (`ProductWizardStepEngineTest.php` — 16 اختبارًا، +4 صافٍ):** استُبدل اختبار "six dynamic steps" باختبارين (5 تبويبات للمنتج البسيط مع `not->toContain` لاسم Options، و6 تبويبات بالترتيب بعد `set('has_variants', true)`)؛ تحديث "completed steps unlock" إلى مسار المتغيرات (`has_variants=true`)؛ `walkWizardToInventory` = 3× `nextStep` (لأنها تتخطى 4 الآن) وتحديث `validated_steps` المتوقعة إلى `[1,2,3]`/`[1,2,3,5]`؛ أُضيف: (a) `visible()`/`visibleIds()`/`isStepVisible()` تستبعد 4 بلا متغيرات وتستعيده معها، (b) nextStep يقفز فوق Options (3→5) وprevStep يعود (5→3)، (c) إلغاء المتغيرات أثناء الوقوف على Options ينقل إلى 3 ويحذف 4 من `validated_steps` + `assertNotDispatched('swal')` للقفز إلى خطوة مخفية (no-op صامت) + إعادة التفعيل تعيد التبويبة بشكل حر. **22 ناجحًا (136 assertion)** بالمشاركة مع `ProductSkuBarcodeTest` (يجب تشغيلهما معًا — خلاف ذلك "undefined function skuUser" بإقليمها). **429 ناجحًا (1733 assertion)** في `tests/Feature/Merchant` (425/1699 سابقًا = +4 اختبارات +34 تأكيدًا، صفر انحدار). `php -l` نظيف على الـ PHP المتغيّر + `view:cache` ناجح (ثمّ `view:clear`).
- **التجاوبي (DOM-grounded — لا E2E في repo):** عدد التبويبات = `count($wizardSteps)` من الـ nav `<ol>`؛ عند b=375px تبقى الدوائر `h-9 w-9` + موصلات `mx-3 flex-1` داخل `flex items-center` (أقل بمجالتبويب واحد عن الوضع السابق → لا تجاوز)؛ عند 768px تظهر التسميات `sm:inline`؛ عند 1440px يبقى الشريط `flex-1` موزّعًا بالتساوي عبر عرض الصفحة. مع اثبات أن partial الخطوة 4 غير مولّد في HTML عند `has_variants=false` (لا `<div x-show="step === 4">` أصلًا).
- **الملاحظات:** بصمة زمنية واحدة فقط عند تبديل `has_variants` (لا استعلامات/تحويلات جديدة — فلترة ذاكرة على static). لا مساس بمنطق الجزأين A (تحقق SKU/القفز) وB (صور المتغيرات) أو inline (المرحلة 4).

**التذييلات:** لا مساس بـ `products/index.blade.php` أو export/import (المرحلة 5). المرحلة الفرعية 4 (إنشاء الخيار/القيمة inline من داخل خطوة Options) تبقى معلّقة كبرومبتات منفصلة بعد اعتماد هذا الدمج.

---

## إنجاز المرحلة الفرعية 4 من 5: إنشاء الخيار/القيمة inline من خطوة Options + ترقية المُنتقيات — 2026-09-12

**السيناريو:** بناءً على قرار المستخدم («نفذ» على خطة التصميم المعتمدة): زر واحد «خيار جديد» (+ Plus) في ترويسة بطاقة Options بجوار «إضافة خيار» — يفتح المودال لإنشاء خيار جديد (بدل زر per-row المزدحم)؛ إصلاح المودال القائم جذريًا (القيم المضافة لم تكن تظهر في draft أصلًا + لا طريقة لإزالة قيمة)؛ استبدال `<select>` الأصلي بمنتقى بحث `<x-edz.select>` (نمط المشروع) وقيم المتغيرات بمنتقى متعدد قابل للبحث جديد `<x-edz.multi-select>` (رقائق + ورقة سفلية على الموبايل) مع أيقونات ×4 لغات جديدة دون خرق التصميم (375/768/1440 + Apple Design Restraint).

**ما تَمّ:**
- **المحرك (`form.blade.php`):** `openCreateOption(?int $index = null)` — بلا وسيط يعيد استخدام أول صف فارغ (`product_option_id` خالٍ) أو يلحق صفًا جديدًا ويضبط `optionModalRow`؛ بوسيط يحل إلى ذلك الصف. **إصلاح الجذر للمودال (ب):** `addOptionValueInline` كان ينشئ القيمة في DB لكنه **لا يكتبها في `$this->options[$row]['values']`** فتبدو draft فارغة عند الإغلاق — الآن يدفع `$newValue->id` (مع `array_unique`) ويستدعي `rebuildPreview()`. **جديد `removeOptionValueInline(string $valueId)`** (بوابة `PRODUCT_UPDATE`): إن كانت القيمة مستخدمة في متغيرات (`variants()->exists()`) → `dispatch('swal', error, product_options.value_in_use)` بلا حذف؛ وإلا تحذف + تُشطب من كل صفوف draft + `rebuildPreview()`. `closeOptionModal` يصفّر الحالة كما قبل.
- **`components/edz/multi-select.blade.php` + `components/edz-multi-select.js` (جديدان):** `<select multiple hidden>` هو عقد Livewire (`wire:model*`=`options.{i}.values` + `wire:change`=valuesChanged)؛ المشغّل يعرض رقائق (نقرة الرقيقة = إزالة) + شارة `+N` (overflow عبر `maxVisibleChips`, افتراضي 3) + نص placeholder المبقيّ؛ اللوحة = بحث محلي (بحث بالكود إن وُجد `option-code`) + صفوف مختارات بخانة `check` (نقرة الصف = تبديل، وزر Done يغلق) + عدّاد؛ تموضع ثابت على الديسكتوب (`position: fixed` بحساب trigger rect) وورقة سفلية على الموبايل — نفس علاّماته «النضج» كـ `edz-select` (`data-state`). مصفوفة `selected` (مبرمج يدويًا بـ`_readSelectedFromDom`/`toggleValue`/`removeValue` — لا `x-model` على الـ select الخفي لتجنب التعارض مع Livewire) → `_writeToDom()` يضبط `option.selected` ويبث `input`+`change` (باتجاه واحد؛ «Livewire/change-event» كافٍ لمصفوفة). `MutationObserver` يعيد قراءة الاختيار بعد morph من Livewire (مثل `edz-select`). مُسجَّل كـ `Alpine.data("edzMultiSelect", ...)` في `panel.js`. المكوّن يدعم `:options` كسجلات Eloquent/مصفوفات ومفاتيح سلسلة.
- **`step-options.blade.php`:** ترويسة `edz-card__header` بعناصر `flex flex-wrap` — زر «خيار جديد» (`wire:click="openCreateOption"` بلا وسيط، أيقونة `plus`, بوابة `PRODUCT_CREATE`) ثم زر «إضافة خيار». منتقى الخيار → `<x-edz.select wire:model="options.{i}.product_option_id" wire:change="optionChanged(i, value)" search icon="cube" size="sm" :options="$this->productOptions->all()" option-value="id" option-label="name" />` (ختامية بحث مدعومة؛ `selected` غير موجودة في `x-edz.select` — تُتوثَّق بـ`wire:model`). القيم → `<x-edz.multi-select wire:model="options.{i}.values" wire:change="valuesChanged(i)" search :options="$this->optionValuesByOption->get(id, collect())->all()" :selected="..." size="sm" search-placeholder />`؛ الفرع TEXT + placeholder (اختر خيارًا أولًا) باقيان. أُزيلت سمات `for` من التسميات (المكوّنات لا تعرض id قابل للتركيز على الـ select).
- **`create-option-modal.blade.php` (إعادة كتابة):** يُحتفظ بنهج `@if ($optionModalRow !== null)` (إعادة المونتاج نظيفة عند كل فتح — لا حاجة لنقل بـ`x-effect`). خطوة الإنشاء: حقل الاسم + `<x-edz.select>` لنوع الإدخال (قيمه `ProductOptionInputType::options()` = خريطة `value => label` يدعمها فرع fallback في `x-edz.select`) + زرا حفظ (check)/تراجع. خطوة القيم (بعد `optionModalCreatedId`): شبكة مبتدأة بـ«قيم الخيار» + لكل قيمة رقيقة قابلة للإزالة (`x-mark` → `removeOptionValueInline`) + نموذج إضافة `wire:submit="addOptionValueInline"` (أيقونة plus) داخل نص البطاقة، وزر Done (check) في التذييل. أيقونات: `plus`, `check`, `x-mark`, `list-bullet` (كلها موجودة في `x-edz.icon`; **`squares-2x2` غير موجودة** فيُستعاض عنها بـ`list-bullet`).
- **`_forms.scss`:** أُضيفت أنماط `.edz-multi-select__*` (الرقائق تحمل خلفية `bg-surface-secondary` + شارات إزالة `x-mark`، خلية `edz-multi-select__check` بدائرتين حالات، تذييل ثابت `border-top` + عدّاد + زر Done). جدول `edz-select--sm` يعطّي ارتفاع الرقائق المصغّرة (تصحيح: كانت `edz-multi-select--sm` الخطأ).
- **مفاتيح ×4 لغات (en/ar/fr/es):** `products.select_values` («Select values…» / «اختر القيم…» / «Sélectionner les valeurs…» / «Seleccionar valores…»).

**بروهات:**
- **الاختبارات (`ProductWizardStepEngineTest.php` — 23 اختبارًا، +7 صافٍ):** ① `openCreateOption` بلا وسيط يلحق صفًا فارغًا ويضبط المودال، والاستدعاء المتكرر يعيد استخدام الصف (لا تكرار) ② بوسيط يحل إلى الصف المطلوب ③ `createOptionInline` يتجاهل الـ name الفارغ (خطأ) ويربط الخيار المنشأ في الصف (id/type/values) ④ `addOptionValueInline` الخامل عند فراغ القيمة بلا إنشاء، وإنشاء + **تحديد فوري في draft** + صفر تكرار (القيمة نفسها مرتين) ⑤ `removeOptionValueInline` يحذف القيمة غير المستخدمة ويشطبها من draft (تختفي زرقاء وتبقى الحمراء) ⑥ القيمة المستخدمة في متغير → `expect NotNull` + تبقى في draft + `assertDispatched('swal', type:'error')` ⑦ العرض: زر «خيار جديد» + `wire:click="openCreateOption"` + `edz-select` + `edz-multi-select` + ترجمتا `products.select_values`/`placeholder` + ربطا `wire:model="options.0.product_option_id"` و`wire:model="options.0.values"`. **29 ناجحًا (171 تأكيدًا)** مع `ProductSkuBarcodeTest` (يُشغَّلان معًا — `skuUser()/dataShape()` يُعرَّفان هناك). `php -l` نظيف على اللغات + `view:cache` ناجح (ثم `view:clear`).
- **البناء:** `npm run build` ناجح — `edzMultiSelect` موجود في `panel-*.js` محسومة وأنماط `edz-multi-select` في `app-*.css` المُضمَّنة (تأكيد byte-check عبر `Contains`).
- **التجاوب (DOM-grounded — لا E2E):** ① **375px:** ترويسة `flex-wrap` تلتفّ (زرا الخيارين في سطر عند الضيق)؛ منتقى القيمة ورقة سفلية عريضة ملتصقة بالأسفل مثل `edz-select` ② **768px:** تنسجم ترتيب البطاقة (عموداه) والرقائق تبقى في سطر المشغّل مع شارة `+N` ③ **1440px:** `xl:grid-cols-3` حاوية الخيارات ثابتة، اللوحة مضمّنة. التحقق اليدوي البصري النهائي (مطلوب من المستخدم) يُدرج عند المرحلة 5.

**الملاحظات:** لا استعلامات جديدة (كل المصادر `productOptions`/`optionValuesByOption` محسوبة قائمة). لا مساس بـ `app/Actions/Product/*` أو `products/index.blade.php` أو export/import (المرحلة 5).

**تحديث تصحيحي بعد التحقق اليدوي للمستخدم (نفس الجلسة):**
- **إصلاح «يُغلق بعد اختيار قيمة واحدة» في `x-edz.multi-select`:** Livewire يعيد تهيئة كل مكونات Alpine بعد أي رحلة دائرية، فكان `open` يُصفَّر بعد أول تحديد (لأن `wire:change` تستدعي `valuesChanged` → إعادة رندر → morph). الحل: خريطة وحدة نمطية `persistence` بمفتاح `modelName` — `_writeToDom()`/`toggle()`/`close()`/المستمع الخارجي يكتبون {open, at}، وفي `init()` تُستعاد الحالة إن كانت أحداثها حديثة (نافذة 4 ثوانٍ؛ تكفي لرحلة الدائرة المحلية وتنتهي قبل أي رندر غير متعلق). كما أُعيد حساب `updatePosition()` عند الاستئناف حتى لا يقفز اللوح إلى 0,0.
- **إعادة تصميم بوب أب «خيار جديد» (المودال):** أُعيدت هيكلته لتفادي «الأزرار الملتصقة بالحقول»: مقبض ورقة سفلية `edz-modal__handle` في الأعلى (موبايل)، ترويسة `edz-card__header` مع زر X (`buttons.close`) يستدعي `closeOptionModal`، منطقة الأزرار في صف تذييل مفصول بحد علوي (`border-t ... pt-4`) بدل الالتصاق تحت الحقول، وزر Done في `edz-card__footer`. عرض القيم: عنوان + عدّاد `selected_count`، ثم الرقائق، ثم صندوق إضافة قيمة بحدود (`border p-4`) مع تسمية «قيمة جديدة» وحقل أدخال + زر إضافة.
- **زر «قيمة جديدة» (quickAddValue) مباشرة في صف الخيار (مثل فكرة «خيار جديد»):** في خلية القيم بأليف `step-options`، تحت `x-edz.multi-select` مباشرة، صف زر `+` «قيمة جديدة» (`products.new_value`، ×4 لغات) مع حقل أدخال — ينشئ القيمة في خيار الصف عبر إجراء Volt جديد `quickAddValue(int $index)` (بوابة `PRODUCT_UPDATE`؛ فارغ بلا خيار = لا-op) ويختارها تلقائيًا في `options.i.values` ثم `valuesChanged` (إعادة بناء المعاينة). `optionValuesByOption` المحسوبة تلتقط القيمة الجديدة في المرة التالية → تظهر في لوحة المنتقى فورًا.
- **اختبارات:** 31 ناجحًا (180 تأكيدًا) مع `ProductSkuBarcodeTest` — جديد: ① `quickAddValue` ينشئ + يختار + يعيد البناء (variants_preview=1) مع no-op عند فراغ الحقل، ② row بلا خيار يتجاهل. اختبار العرض يتحقق الآن من `wire:submit="quickAddValue(0)"` و`products.new_value`. `php -l` نظيف + `view:cache` ناجح + `npm run build` (البانل الجديد `panel-*.js` يحمل `edzMultiSelect` والأنماط في `app-*.css`).

**تحديث ثانٍ — إصلاح «Column sku cannot be null» عند التحديث:**
- **السبب:** `syncVariantRow` في `form.blade.php` (داخل `$syncExistingVariants`) تعيد صياغة كل حقول الصف إلى UPDATE؛ أي صف preview صار sku/barcode فيه `null` (بعد إعادة بناء من `VariantPreviewBuilder` التي تولّد rows بأكواد skeleton فارغة) كان يفرض `UPDATE product_variants SET sku = NULL` → خرق قيد NOT NULL (1048) رغم أن المتغير المخزّن لديه السكو.
- **التصحيح:** داخل مرشّح `$dirty` — إذا كان المفتاح في `['sku','barcode']` والقيمة `blank()` بينما المتغير المخزّن لديه قيمة `filled()` → استبعاد الحقل من التحديث (لا يُفسح الأكواد أبدًا). بقية الحقول (السعر/التكلفة/المخزون…) تُحدَّث كالمعتاد؛ وهكذا تظل التعديلات البسيطة تعمل والأكواد ثابتة.
- **اختبار:** «editing a variable product never wipes an existing variant sku to null» — عبر `makeVariableProduct`، قُدِّمت preview rows بسكو/باركود null وقيم أسعار جديدة ثم `save()`: لا استثناء، السكو/الباركود محفوظان، والأسعار والمخزون المحدّثان محفوظان، ولا زيادة/حذف في المتغيرات (2). ملاحظة: السكو ليس editable في الواجهة (يوجد فقط سكو المنتج في خطوة المخزون) لذا السبيل الوحيد للوصول هو صف preview أعيد بناؤه داخليًا بزر null — وهو ما يغطيه الاختبار. **الإجمالي: 32 ناجحًا (190 تأكيدًا) مع `ProductSkuBarcodeTest`.**

## أيقونات خطوات المعالج + إجابة مرحلة المخزون (بعد موافقة المستخدم) — 2026-09-13

- **سؤال المستخدم (مرحلة المخزون لمنتج متغيرات):** التحقق الحي (`step-inventory`/`step-options`/`OrderRules`) — عند `$has_variants=true` تُستبدل بطاقة «التسعير والمخزون» برسالة `products.variants_hint` فقط، ومخزون كل متغير يُحرَّر في خطوة الخيارات (`variants_preview.*.stock`/`.low_stock_threshold` + «تطبيق على الكل»). **لا يوجد جمع**؛ قصّ سطر الطلب بمخزون ذلك المتغير نفسه (`OrderRules.php` `$caps[] = (int) $variant->stock`)، والتوفر في الواجهة لكل سكو. القرار المعتمد: إبقاء الإدارة لكل متغير (بلا مجموع) — المستخدم فوّض التنفيذ للخطة المُوصى بها.
- **الأيقونات:** كانت تعريفات `ProductWizardSteps::all()` تحمل أيقونات لم تُعرض قط — الدوائر كانت تعرض الرقم فقط والتسمية مخفية <sm (دائرة بلا معنى في الموبايل). كذلك `currency-dollar` و`archive-box` **غير موجودتين** في سبريت `edz/icon` فتنكسر بصمت إلى `grid`. التصحيح: `currency-dollar→banknotes` (Pricing) و`archive-box→cube` (Inventory)؛ باقي الأيقونات موجودة فعلًا (`information-circle`/`image`/`adjustments`/`check-circle`).
- **`wizard-steps.blade.php`:** دائرتا الحالية/القادمة تعرضان الآن `<x-edz.icon :name="$step['icon']" class="h-5 w-5">` بدل الرقم؛ المكتملة تبقي `check-circle` والمقفلة `lock-closed`. أُضيف `aria-label`+`title` (= اسم المرحلة) على الأزرار المفتوحة و`title` على المقفلة (وصولية كاملة في الموبايل بلا ضجيج بصري). **الهندسة ثابتة 1:1** (دائرة `h-9 w-9` + أيقونة `h-5`، والتسميات `hidden sm:inline`) ⇒ صفر إعادة تدفق عند 375/768/1440؛ لا استعلامات/JS جديدة؛ لا مساس بـ `form.blade.php` أو أي صفحة index (بند 8 محفوظ).
- **اختبار جديد:** «nav renders each step icon glyph with an accessible label in place of the raw number» — وضع تعديل لمنتج متغير (كل الخطوات مفتوحة)؛ لكل خطوة: `aria-label`/`title` = التسمية + حلقة SVG الخاصة بأيقونتها (بادئة `d` مميزة؛ تُثبت عدم السقوط إلى `grid`)، ولا أي `d` لـ grid بأي دائرة، وعدد `<svg` = عدد الخطوات. **ملاحظة Pest 3.8.5:** `toContain(...$needles)` **variadic بلا وسيط message** — أي رسالة ثانية تُعدّ needle إضافيًا وتفشل مضللة؛ لذلك تُستخدم `expect(str_contains(...))->toBeTrue($message)` للرسائل الحاملة للسياق.
- **التحقق:** **43 ناجحًا (241 تأكيدًا)** في `ProductWizardStepEngineTest` + `ProductSkuBarcodeTest` (صفر انحدار؛ السابق 42/220 +1/…). `php -l` نظيف (وحذف ملف فحص مؤقت `BisectScratchTest.php`) + `view:cache` ناجح (ثم `view:clear`). التحقق اليدوي البصري (375/768/1440) مطلوب من المستخدم.

## Sub-phase A — Order Validation Parity Fix — 2026-09-13

- **Status:** completed
- **النطاق (بموافقة المستخدم المعتمدة):** تصحيح تكافؤ الفاليديشن فقط بين مسارات الإنشاء/التعديل/التتبع — بلا حقول جديدة، بلا UI، بلا مكوّنات، بلا لمس `Order::DEFAULT_MAX_WEIGHT_KG` (يتبع Sub-phase لاحقة).
- **الملفات المتأثرة (نطاقات خطوط متحققة):**
  - `resources/views/livewire/merchant/orders/index.blade.php` — `$submitCreate` (4279+) / `$submitEdit` (4494+):
    - submitCreate: سقف `discount_value` % (closure percent>100 → `merchant_panel.discount_percent_max`) عند 4314.
    - submitEdit: `$maxWeightKg = Order::resolveMaxWeightKg(...)` (4519)؛ سقف الخصم % (4543)؛ `phone_secondary` (4552) + `weight_kg` (4553) + `notes` (4554)؛ رسالة `weight_kg.max` (4556)؛ فحص `office_required_for_stopdesk` مطابق تمامًا لـ submitCreate (4573-4576).
  - `app/Livewire/Concerns/TrackingRiderFormConcern.php` — `submitEdit` (827): إضافة `notes` (864) + `phone_secondary` (865) بنفس قواعد معرّف submitCreate (`nullable|string|max:20|regex:/^0[5-7]\d{8}$/`؛ الوزن كان موجودًا أصلًا عند 859).
- **وسوم TEMP-PATCH المضافة:** **7** («TEMP-PATCH (Sub-phase A, 2026-09-13)…») — 6 في index.blade.php (4313, 4519, 4536, 4551, 4556, 4573) + 1 في TrackingRiderFormConcern.php (863).
- **`index.blade.php` عدد الأسطر: 5606 → 5642** (+36، زيادة محدودة ومبرّرة: closure متعدد الأسطر ×2 + قواعد + فحص).
- **نتيجة الاختبارات (أمر Pest):** `pest tests/Feature/Merchant/OrderWeightAutoCalcTest.php OrderOfficeSelectionTest.php OrderInlineFieldEditTest.php TrackingGridBatchTest.php` → **59 ناجحًا (234 تأكيدًا)** في 83.15s — صفر انحدار، و`OrderWeightAutoCalcTest` ناجح **دون تعديل** (لم تُمسّ الـ 50/100 الافتراضية هنا). `git status`: ملفان مُعدَّلان فقط، **صفر ملفات جديدة**.
- **مؤجّل إلى مرحلة الـ refactor (وضع علامة في التقرير):** ① توحيد قاعدة سقف الخصم % في trait/concern مشترك بدل التكرار inline في 3 مسارات؛ ② نقل حقل الوزن من المودال إلى الملخص المالي؛ ③ مطابقة سقف الخصم % وفحص المكتب في مسار التعديل الخاص بالـ tracking grid (لم يُلمس خارج نطاق القرار المعتمد)؛ ④ بند التعديلات 2-8 من الخطة (الوزن في الملخص، العروض/سعر المقارنة، القائمة الموحدة بفاصل، /delivery، stopdesk، إعادة تركيب cascade) — لم تبدأ بأيٍّ منها.
- **تحقق يدوي مطلوب من المستخدم:** رفض خصم %=150 بمسارات الإنشاء/التعديل (10% اضغط بعد بناء الواجهة أمام المتصفح).

## مبادرة المعالج/فهرس المنتجات — المرحلة الفرعية 5 من 5: Export/Import (واجهة فقط + «قريبًا») — 2026-09-13

- **Status:** completed (مبادرة المعالج/فهرس المنتجات مكتملة بمشارفها الخمس).
- **النطاق (بموافقة المستخدم):** واجهة تصدير/استيراد **تجريبية UI-only** في صفحة فهرس المنتجات `merchant.products.index` — زرا مشغّلين داخل `<x-slot:actions>` + `@include` واحدة، وكل علامات المودال في partial مخصص. **صفر منطق/مسارات/وحدات تحكم** تصدير/استيراد — الأزرار داخل المودال مُعطّلة (`disabled` أصيلًا، `.edz-btn:disabled` يعطي `opacity: .55; pointer-events: none` من `_buttons.scss:26` — لا no-op صامت) مع ملاحظة «قريبًا» ظاهرة (`clock` + `text-sm text-ink-soft`) بمفتاحي `export_coming_soon`/`import_coming_soon`.
- **الصلاحيات (قرار + مسبب):** زرا المشغّل مقيدان — Export بـ`PRODUCT_VIEW` (تصدير = قراءة بيانات المنتج، وmount الصفحة يـ abort بدونه) وImport بـ`PRODUCT_CREATE` (كتابة/إنشاء، يطابق بوابة «منتج جديد») — كقيمتين closure `$canExport`/`$canImport` في سكربت Volt.
- **البنية (قاعدة لا تُناقش):** صفر علامات export/import مضمّنة في `index.blade.php` — فقط (أ) زرا المشغّل داخل `<x-slot:actions>` الوجود، (ب) سطر `@include('livewire.merchant.products.index.export-import-modal')` واحد قبل `</div>` الأخيرة؛ كل علامات المودالين في `resources/views/livewire/merchant/products/index/export-import-modal.blade.php` (ملف واحد، المودالان، كلٌّ محمي بـ`@if ($show_export_modal)`/`@if ($show_import_modal)`) — يطابق سابقة المعالج `form.blade.php` + `create-option-modal.blade.php` (`@if`-guard + `preventClose` + `$set` للإغلاق).
- **سلوك المودال:** `x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="lg"` — كل مسارات الإغلاق (X في الترويسة/إلغاء) تستدعي `$set('...', false)`؛ لا اعتماد على backdrop/Escape (سينكسر تزامن state). SCSS `_modal.scss` يوفّر بالفعل bottom-sheet بعرض الشاشة (<640px) / كرت ممركز (≥640px، `max-width:60rem` لـ lg) — صفر تغييرات مطلوبة. محددات داخلية خاملة (radio-cards بلا ربط Livewire): export = تنسيق CSV/Excel (CSV `checked`) + نطاق الكل/المفلتر/المحدد (`@disabled(empty($selected))` + `opacity-60` + «المحدد (:count)») عبر `has-[:checked]:border-accent-500 has-[:checked]:bg-accent-50` + `accent-accent-600` (Tailwind 3.4، accent موجود في config)؛ import = dropzone Alpine-only (`x-data="{ dragging: false }"`، `@dragover/dragleave/drop` بصري فقط) + «تصفح الملفات» مُعطّل + «تحميل القالب» مُعطّل.
- **ترجمة:** مفاتيح جديدة ×20 في `resources/lang/{en,ar,fr,es}/products.php` بإدراج أبجدي (نفس المواضع النسبية) — `coming_soon`+`export_*`(title/desc/format/format_hint/scope/scope_hint/csv/csv_desc/excel/excel_desc/all/filtered/selected/coming_soon)+`import_*`(title/desc/dropzone_hint/browse/choose_file/template/template_hint/coming_soon). es ملف بلا `edit_product` فتُثبَّت الكتلة على `description_label`/`featured`؛ تحقّق آلي للغات الأربع: OK (en/ar/fr 177 مفتاحًا، es 109) — صفر مفاتيح ناقصة.
- **الاختبار (`tests/Feature/Merchant/ProductIndexExportImportTest.php` — 6 ناجحة، 38 تأكيدًا):** ① مالك يرى زرا Export+Import؛ ② موظف view-only (`syncPermissions([PRODUCT_VIEW])`) يرى Export دون Import/منتج جديد؛ ③ موظف مقصورة صلاحياته على `ORDER_VIEW` فقط → `canStore(PRODUCT_VIEW)` false + `assertForbidden()` على المسار (ملاحظة Spatie: دور staff يملك `products.view` جوهريًا لهذه المرونة، فـ403 الحقيقي عبر العضويات المقصورة حسب Decision #6)؛ ④ المودال export يفتح/يغلق عبر `$set('show_export_modal', …)` + يفحص الترويسة و`'coming soon'` (حساس للحالة) والفئات المتجاوبة `grid grid-cols-1 gap-2 sm:grid-cols-2`/`sm:grid-cols-3` و`accent-accent-600` والزر الأصلي `disabled` و«محدد (0)»؛ ⑤ المودال import يفتح/يغلق + dropzone/تصفح/قالب/زر معطّل؛ ⑥ المودالان غير مُثبّتين (`assertDontSee`) حتى الفتح، والتقليب مستقل. **المرافقة (تشغيلها معًا):** `ProductWizardStepEngineTest` + `ProductSkuBarcodeTest` → **43 ناجحة (241 تأكيدًا)** — صفر انحدار. `view:cache` ناجح (ثم `view:clear`). `php -l` نظيف على index.blade.php + partial + ملف الاختبار + اللغات الأربع.
- **التجاوب (مستوى الكود/SSR — بلا أدوات E2E):** ① **375px:** سطر الإجراءات `.edz-page-head__actions` هو `flex wrap gap:.5rem` فيُلتفّ الزرّان بسلامة؛ المودال يتحوّل bottom-sheet بشبكتي radio متكدستين `grid-cols-1` وتذييل `flex-wrap`؛ ② **768px:** الكرت الممركز (≥640px) + تنسيق 2 أعمدة `sm:grid-cols-2` + نطاق 3 أعمدة `sm:grid-cols-3`؛ ③ **1440px:** كرت `max-w-60rem` مرتكز + الشبكات 2/3 أعمدة. المتحقَّق آليًا: الفئات المتجاوبة موجودة في HTML المُعاين (الاختباران ④/⑤) + مراجعة `_modal.scss`/`_buttons.scss`. **التحقق البصري اليدوي للمراحل الفرعية 3 و4 (375/768/1440 أمام المتصفح) ما زال غير منفَّذ — مطلوب من المستخدم؛ لا أُوسم بالاكتمال.**
- **مؤجَّل (مبادرة مستقلة مقترحة):** الخلفية الحقيقية للتصدير/الاستيراد (CSV/Excel، قوالب، حدود، `Jobs`، تدريج) — غير منفَّذة ولم تُلمس أي ملفات `app/Domains/Product/` خارج سابقة المعالج القائمة؛ عمل غير مرتبط قائم (سباق حد الوزن، عروض orders، `OrderWeightAutoCalcTest`، هجرتان فائقتي `2026_09_12_100001_*`/`100002_*`) لم يُلمس.

## مرحلة الإصلاح: إظهار تنبيهات التحقق في مودال الطلب (Order Form Validation Display) — 2026-09-13

- **Status:** مكتمل
- **الشكوى (الدليل):** في بوب أب إنشاء/تعديل الطلبية لا يُعرض أي تنبيه في أي حقل عند الإرسال — حتى بعد إزالة `required` من الواجهة يبقى الصمت. السبب الجذري: `Validator::make($this->form, …)->validate()` المسطّح يضع سلة الأخطاء بمفاتيح القواعد الخام (`customer_name`, `weight_kg`, …) بينما القوالب تعرض بـ`@error('form.*')` فقط → لا تطابق → صفر رسائل. كذلك `Utils::hasProperty()` (= `property_exists(قبل أول نقطة)`) يُفلتر المفاتيح المسطّحة من memo الجولة بينما `form.*` تُحفظ (الخاصية `form` موجودة). الرجوع إلى مرجعيّة Livewire: سلة الأخطاء تُملأ بمفاتيح أسماء القواعد كما صيغت.
- **الحل (المسار الأصيل):** استبدال مسارات submit الثلاثة بـ**`$this->validate()` بقواعد `form.*`** — يدخل المسار الأصلي لـ Livewire: رمي `ValidationException` يلتقطه خطاف `SupportValidation::exception` (سلة `form.*` مرفوعة في memo الجولة) ويملأ `testing.validator` في الاختبارات، ووسوم `@error('form.*')` تطابقه. (نموذج catch→`addError` وُجد لاحقًا أنه لا يملأ `testing.validator` وكسر الاختبارات — رُفض).
- **البيانات/الأسطر:** `index.blade.php` — submitCreate ~4289، submitEdit ~4522؛ `TrackingRiderFormConcern::submitEdit` ~843 (قواعد مطابقة). `addError('stopdesk_point_id', …)` → `addError('form.stopdesk_point_id', …)` في المسارين (index 4354/4599) لرسالة «مكتب مطلوب» تحت حقل المكتب. وسوم TEMP-PATCH جديدة: 3 (واحدة فوق كل كتلة validate).
- **وسوم @error المضافة:** `form.phone_secondary`, `form.address`, `form.shipment_type`, `form.weight_kg`, `form.items`, `form.notes` في `order-form-modal.blade.php` (+18 سطرًا) + `form.discount_value` تحت محرر الخصم في `order-financial-summary.blade.php` — حتى سقف `%>100` من Sub-phase A يظهر الآن رسالة تحت الحقل.
- **الاختبارات:** ترقية مفاتيح `assertHasErrors` المسطّحة إلى `form.*` (`OrderWeightAutoCalcTest` ×4: `['form.weight_kg' => 'max']`؛ `OrderOfficeSelectionTest`: `['form.stopdesk_point_id']`) + اختبار تراجع جديد «create-modal field errors resolve under the form.* namespace» يتحقق من `form.customer_name/required`, `form.customer_phone/required`, `form.items/required`, `form.phone_secondary/regex` مع صفر طلبات مخلوقة.
- **أدلة التشغيل (بمفسِّر PHP 8.3 الصريح):** الباقة الأربع (OrderWeightAutoCalcTest + OrderOfficeSelectionTest + OrderInlineFieldEditTest + TrackingGridBatchTest) = **60 ناجحة (247 تأكيدًا)** 72.67s؛ الإضافيتان (OrderFinancialSummaryTest + OrderQuantityCapTest) = **13 ناجحة (49 تأكيدًا)** 30.55s. الإجمالي **73 ناجحة (296 تأكيدًا)**، صفر انحدار. `view:cache` ناجح (يُثبت سلامة صياغة البلاد) ثم `view:clear`. `git status`: 7 ملفات فقط من عملي (index.blade.php، TrackingRiderFormConcern.php، order-form-modal، order-financial-summary، الاختباران، Todos.md) — باقي الشجرة (منتجات/معالج/تصدير-استيراد…) عمل موازٍ للمستخدم لم ألمسه.
- **ملاحظة تشغيل:** `vendor\bin\pest` يستدعي PHP 8.2 من PATH ويفشل بفحص المنصة — التشغيل الصحيح دائمًا عبر `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe vendor\bin\pest …`.
- **مؤجَّل/مطلوب:** تحقق المستخدم البصري (375/768/1440 + ظهور رسائل الحقول تحت كل حقل + سقف %=150) أمام المتصفح.

---

## عنقود تحسينات توليد العرود — المراحل الفرعية 2–6 (بعد إصلاح عرض التنبيهات) — 2026-09-13

اختيار المستخدم: **المراحل الخمس كلها معتمدة** (أداة الأسئلة) — (2) سقف الوزن، (3) الخصم، (4) القائمة الموحدة، (5) أنواع الشحن، (6) stopdesk. الأطر الأربعة المحسّنة أعلاه هذا القسم.

### المرحلة الفرعية 2 ✅ — سقف الوزن الافتراضي 100 كغ
- `app/Models/Orders/Order.php`: `DEFAULT_MAX_WEIGHT_KG` 50→**100** (تعليق مُحدَّث — السقف الافتراضي يُطبق بلا شركة، والسقف المخصّص من `resolveMaxWeightKg(providerId)` يحل مكانه)؛ `resolveMaxWeightKg` (~99-118) بلا تغيير منطقي.
- ترجمات `merchant_panel.max_weight_hint` ×4 لغات (ar/en/fr/es): «جرّ 100 كغ».
- `tests/Feature/Merchant/OrderWeightAutoCalcTest.php` — 4 اختبارات محدَّثة الأسماء والقيم: `25+30 → 50+60 (110 كغ)`, `exactly 50 → exactly 100 (يُحفظ)`, `80→150 / 60→110 (سقف مخصّص يعلو الافتراضي)`, `50.5→100.5 (كسر)`. **13 ناجحة (40 تأكيدًا)**.

### المرحلة الفرعية 3 ✅ — الخصم: placeholder «لا عروض» + اقتراح سعر المقارنة
- مفاتيح `merchant_panel.no_offers_available` + `merchant_panel.compare_price_savings` ×4 لغات.
- `partials/order-financial-summary.blade.php`: `$compareSavings` (مخطط من `ProductVariant::compare_price` عبر أصناف `form.items`) — بطاقة الخصم ثلاث حالات: مبلغ/نسبة محددة → `-X`؛ وإلا اقتراح `compare_price_savings` (نص success، يُظهر الادخار إن وُجد `compare_price` أعلى)؛ وإلا `no_offers_available` (placeholder محايد بدل «+0»).
- `OrderFinancialSummaryTest.php` — اختباران جديدان: «no-offers placeholder» + «savings drawn from comparison prices». **10 ناجحة (52 تأكيدًا)**.

### المرحلة الفرعية 4 ✅ — القائمة الموحدة بفاصل «·»
- `partials/order-form-modal.blade.php`: قائمة أصناف المودال من أبطاقات منفصلة → كرت واحد `divide-y divide-surface-border` مزوّد بـ`data-order-items-list`.
- `orders-table-cell.blade.php` (سطر ~282-296): فاصل عمود المنتجات `,` → **`·`** (موضعا البوي عناصر).
- تأكيدات اختبار الشبكة تحديثًا على الفاصل الجديد.

### المرحلة الفرعية 5 ✅ — أنواع الشحن read-only من قدرات الشركة
- `index.blade.php`: ملف مشترك `$shipmentTypeOptionsFor(?string $providerId)` (~2380-2412) مبني على `Carrier::capabilityList()` — `supports_delivery` → [delivery] فقط، `supports_return → +return`، والافتراضي [delivery, return].
- `$formShipmentTypeOptions()` (~2414-2431) يفوض للملف — بلا فحص الناقل المكرر.
- `$startOrderShipmentTypeEdit` (~3795+): يضبط `editShipmentTypeOptions` من ناقل الطلبية الفعلي (مسار الشبكة/الموبايل يقرآن من الخاصية) — محرر inline **مقصوص** لقدرة شركة الطلبية.
- `OrderInlineSelectEditTest.php` — +1: «inline shipment-type editor capped to the carrier capabilities» (Carrier supports_delivery فقط → خياران [delivery]). **12 ناجحة (52 تأكيدًا)**.

### المرحلة الفرعية 6 ✅ — stopdesk: بلدية ظاهرة كخطوة حقيقية + تسلسل جغرافي «تحديد لا توقع» + عرض رمز/بلدية + lazy (منقَّح بقرارَي A/B)
- **البلدية خطوة أولى ظاهرة لكل الأنواع:** أُزيل `x-show="delivery === 'home'" x-cloak` من عمود `form.city_id` في `order-form-modal` + `delivery-edit-modal` — التضييق ولاية → بلدية → مكتب.
- **تعديل الولاية = تحديد حقيقي:** إغلاق جديد `$changeFormState($id)` (~2581) يضبط الولاية ويُفرّغ `city_id` + `stopdesk_point_id` + `formOffices` ثم `loadCities($id)` — لا «إبقاء إن كان صالحًا» عند تعديل الجغرافيا (قرار A: تفريغ قسري ليعاد الاختيار الفعلي).
- **تعديل البلدية = يحفظ اختيار المستخدم:** إغلاق جديد `$changeFormCity($id)` (~2591) يضبط البلدية ثم `rebuildFormOffices()` — المكتب الصالح يبقى؛ غير الصالح يُمسح بتوست؛ مكتب مفرد للبلدية يثبّت نفسه (قرار B).
- **تغيير الشركة/النوع = يحفظ ويوفّق:** `$applyProviderScope` (~3009) → `loadCities(state, resetCity: false)` — البلدية المغطاة تُحفظ عند تبديل النوع/الشركة (home↔stopdesk) والمكتب يُوفَّق تلقائيًا، بينما تعديل الولاية وحده يفرّغ لأنه إعادة تحديد.
- **لا تثبيت «افتراض» على مستوى الولاية:** أُزيل التثبيت التلقائي عند stopdesk بلا بلدية — مكتب وحيد في الولاية **لا** يُختار دون بلدية؛ التثبيت فقط لبلدية بمكتب واحد.
- **اشتقاق البلدية من المكتب:** `$onFormOfficePicked` (~3051) يُبقي ملء `city_id` من المكتب عند اختيار يدوي بين عدة مكاتب.
- **عرض «رمز + بلدية» فقط:** `StopdeskPoint::scopedOfficeOptions` + بانيّ `rebuildFormOffices` — `hint` = اسم البلدية فقط؛ المصدر المشترك (lazy / cascade / inline) متطابق.
- **مرشدات خطوة-بخطوة:** «اختر شركة → اختر ولاية → اختر بلدية → اختر مكتب» — فرع `select_city_for_desks` أُعيد بين فرعي الولاية والمكتب في المودالين.
- **OrderOfficeSelectionTest.php — اختبارات S6 منقَّحة/جديدة:** ① create form يُبقي حقل البلدية ويرشد ولاية ثم بلدية، ② تغيير الولاية يُفرّغ البلدية+المكتب، ③ بلدية بمكتب واحد تثبّت تلقائيًا بينما مكتب ولاية وحيد بلا بلدية لا يُخمَّن، ④ اختيار يدوي بين مكتبين يعيد ملء البلدية، ⑤ مودال التعديل السريع يُبقي البلدية وتغيير الولاية يفرّغ الحقول، ⑥ التبديل إلى stopdesk يحفظ البلدية ويثبّت مكتبها الوحيد. **حزمة OrderOfficeSelection: 27/27 (109 تأكيدًا)**.

### أدلة التشغيل (المراحل 2–6)
- الباقة الخمس المُتأثرة: **70 ناجحة (269 تأكيدًا)** — OfficeSelection 27 (109) + WeightAuto 13 (40) + InlineSelect 12 (52) + Tracking 8 (16) + FinancialSummary 10 (52) — صفر انحدار.
- `view:cache` ناجح ثم `view:clear`.
- **مؤجَّل/مطلوب:** تحقق المستخدم البصري 375/768/1440 (حقل البلدية ظاهر لـ stopdesk بعد إعادة تصميم S6، المكتب يظهر «رمز + بلدية» فقط، تثبيت بلدية بمكتب واحد) + سقف %=150.

### الملحق 3: منصة المنتجات — فلاتر أبحاث من مستوىين + تحويل مودالَي التصدير/الاستيراد إلى Alpine نقي + رفع تباين الوضع الفاتح (2026-09-13)
- **`partials/filter-bar.blade.php` (جديد):** شريط أدوات — بحث دائم الظهور (بحث ×) + زر **Filters** يطلق `edz-filter-open { key: 'root' }` عبر `dropdownPosition()`، مع **شارة عدّاد** تظهر عند تفعيل أي فلتر.
- **`partials/filter-portal.blade.php` (مُدمج داخل filter-bar):** لوحة متدرّجة من مستوىين (نسخ idiom التتبع/الطلبات): جذر (Brand/Category/Status/Featured/Created) → خيارات؛ زر رجوع لكل قسم؛ عنصر نشط يظهر **علامة ✓** بشارة `bg-accent-surface`؛ قسم الفترة مدخلا flatpickr `created_from`/`created_to` (تلقائي عبر `panel.js`).
- **`index.blade.php`:** حالة جديد `category_id, is_active, is_featured, created_from, created_to` (استبدال `created_at` الواحد)؛ حذف `show_export_modal`/`show_import_modal`؛ استعلام إضافة مرشّحات `primary_category_id` + `whereDate created_at >=/<=`؛ `$setFilter` (يُصفّر `page`) + `$clearFilters` + `$activeFilterCount` (computed/closures)؛ `$categories` (تصنيفات المتجر مع `full_name`)؛ `wire:target` يضم كل الفلاتر السبعة؛ زرّي التصدير/الاستيراد أصبحا `@click="exportOpen = true"` إلخ.
- **`export-import-modal.blade.php`:** تحويل كامل إلى **Alpine نقي** — `x-show="exportOpen"/"importOpen"` من نطاق الجذر `x-data="{ exportOpen, importOpen }"`، نسخ Markup `edz-modal` (+`role="dialog"`, Esc+backdrop), قفل تمرير الجسد عبر `x-effect`، بلا أي `$set`. لا تغيير في المكوّن المشترك (~70 مستخدمًا).
- **تباين وضع فاتح (+درجة واحدة):** subtitle `text-ink-400`→`500`، رأس الجدول/الباركود/التاريخ `text-ink-muted`→`text-ink-soft`.
- **ترجمات ×4:** `products.all_categories` في ar/en/fr/es (ذات الإسناد كباقي الأقسام).
- **`ProductIndexExportImportTest.php`:** إعادة كتابة كاملة لتأكيد المودالين **مثبَّتين دائمًا ومخفيين بـ `x-show` + `x-cloak`** بدل `$set` و`assertDontSee-حتى-الفتح` + أزرار الـ Alpine الثنائية. **حزمة 6/6 (38 تأكيدًا)**.
- **`ProductIndexFilterTest.php` (جديد):** 9 اختبارات — الشارة صفر/عدّاد، brand، category، status، featured، created_from/to (عبر `where id update` لأن `created_at` ليس في fillable)، clearFilters، التسلسل الهرمي `Men > T-Shirts`، wire:target. **9/9 (36 تأكيدًا)**.
- **تشغيل كامل:** حزمة `ProductIndexExportImportTest`+`ProductIndexFilterTest`+`ProductWizardStepEngineTest`+`ProductSkuBarcodeTest` = **58 ناجحة (316 تأكيدًا)**؛ `view:cache` ناجح ثم `view:clear`؛ `php -l` نظيف على الثلاثة.
- **مؤجَّل/مطلوب:** تحقق المستخدم البصري (لوحة الفلاتر 375/768/1440 عبر الاعتماد على النمط الراسخ من لوحة التتبع، انسيابية المودال، وضوح الوضع الفاتح) — لا يوجد متصفح مضمّن.

## دفعة صفحة التتبع: فلاتر «Filters» المتدرّجة بأسلوب المنتجات (شركات التوصيل + رجال التوصيل) (2026-09-13) ✅
- **قاعدة النسخ:** مجموعة الفلتر تُعرض في قائمة Filters **فقط** إذا كان عمودها المقابل غير ظاهر حاليًا في الشبكة (العمود الظاهر يملك فلتر رأسه الخاص في `tracking-table-header`). الاستثناءات: `provider`→`provider` (تبويب الشركات)، `tracking_statuses`→`tracking_status`، `date`→`shipping_date`، `amount`→`total`، `city`→`city`، `rider`→`delivery_rider` (تبويب الرجال)، `assigned_to`→`assigned_to`، `confirmed_by`→`confirmed_by`.
- **`TrackingGridConcern.php`:** `availableFilterGroups()` (يُحتسب في كل `render` — فلترة ذاكرة على `$visibleColumns`، صفر استعلامات جديدة) + `activeFilterCount()` (عدّاد الشارة على مجموعات المتاحة فقط).
- **`partials/tracking-filter-bar-portal.blade.php` (جديد):** لوحة متدرّجة من مستويين (نسخ idiom `products/index/partials/filter-bar`): جذر المجموعات المتاحة → أقسام (provider/tracking_statuses/date/amount/city/rider/assigned_to/confirmed_by)؛ زر رجوع لكل قسم؛ عنصر نشط بعلامة ✓؛ مسح الفلاتر؛ ورقة سفلية موبايل + قائمة منسدلة ديسكتوب عبر `dropdownPosition()`. تستمع على حدث `edz-toolbar-filter-open` (عزلًا عن `edz-filter-open` الخاص ببوابة رؤوس الأعمدة).
- **`partials/tracking-toolbar.blade.php`:** أُزيلت القائمة المسطّحة القديمة و`quick active count` ومنتقيا `assigned_to`/`confirmed_by` المضمّنان؛ زر **Filters** واحد يطلق `edz-toolbar-filter-open { key: 'root', el }` مع شارة العدّاد، مخفي كليًا حين لا تتوفر مجموعات (افتراضيًا كل الأعمدة ظاهرة → الزر مخفي حتى إخفاء عمود). بقية الشريط (بحث/مزامنة/أعمدة/رقائق الفلاتر النشطة) دون تغيير.
- **`index.blade.php`:** تضمين البوابة بعد `tracking-filter-portal`.
- **لا ترجمات جديدة:** أُعيد استخدام المفاتيح القائمة. **لا أدوات متصفح مضمّنة** — كل نقاط التحقق اليدوية البصرية مدرجة لاحقًا.
- **التشغيل:** `php -l` نظيف (PHP 8.3.28) × 4، `view:cache` ناجح، و**`TrackingSearchFilterTest` 28/28 (114 تأكيدًا)**.
- **فشلان قائمان (مُثبَت أنهما سابقان لهذه الدفعة — أُعيد إنتاجهما بعد `git stash` لملفات الدفعة):** `TrackingGridBatchTest::openEditModal`/`submitEdit` — `Undefined variable $optionDivider` في `components/edz/select.blade.php` (تعديلات غير ملتزمة في `select.blade.php`/`edz-select.js`/`order-form-modal.blade.php`). وكسر تحميل فئات إضافي: `NoestIntegrationAdapter` (عدّاد غير ملتزمين: `CarrierIntegrationContract::validateForCarrier` أُضيف بلا تنفيذ في الـ adapter) — يظهر عند مزج `TrackingTrashWebhookLabelTest`/`NoestIntegrationTest` في نفس التشغيل؛ `TrackingSearchFilterTest` وحده يمر كاملًا.
- **مؤجَّل/مطلوب:** تحقق المستخدم البصري 375/768/1440 — ظهور/اختفاء زر Filters عند إخفاء الأعمدة، الورقة السفلية الموبايل، زر الرجوع، وعلامة ✓ للقيمة النشطة، ومنتقيا المناداة السفلية بعد نقلهما من الشريط إلى مدخل الفلتر.
---

## المرحلة 2 (الفرع B): إعادة هيكلة نموذج الطلب (فصل المكونات) (2026-09-13)
ثبّتت وطبقت 5 تغييرات معزولة على مكوّنات نموذج الطلب مع قيد **صفر نمو صافي في `index.blade.php` (سقف 5,699 سطر)**:

- **2.1 نقل حقل الوزن:** أُزيل حقل الوزن من قسم معلومات الطلب ونُقل إلى شبكة الملخص المالي كخلية قابلة للتعديل (تتبع تلقائي `weight_auto_hint` + أقصى وزن + `@error`).
- **2.2 خصم من العروض:** Action مخصص جديد `SuggestCompareDiscountAction` يحسب Σ max(0, سعر المقارنة − السعر) × الكمية مقيّدًا بالمجموع الفرعي، مع عرض النسبة المئوية؛ يُستدعى حصريًا في الملخص المالي.
- **2.3 موحّد منتقي الشريك:** قائمة واحدة مشفّرة (`p:{id}`/`r:{id}`) مع فاصل `__delimiter__`، ووضع الشركة الواحدة (مؤشّر `data-edz-company-single`)؛ استُخرج منطق التبديل إلى trait جديد `OrderDeliveryPartnerConcern`.
- **2.4 تبسيط نقطة الاستلام:** أُبقي حقل البلدية (لا يُخفى مجددًا — يحفظ عقد S6) مع تبسيط التدفق.
- **2.5 إعادة تصميم قسم التوصيل:** partial جديد `order-delivery-cascade.blade.php` مشترك بين النموذجين (إنشاء/تحرير) يشمل تبديل النوع وشبكة الولاية/البلدية ومكتب التسليم مع بوابات الحالة.
- **`index.blade.php`:** أُزيلت 5 closures واستُبدلت بطرق الـ trait (checked @1833/3095/4246/4466، فحص الـ rider @1955، افتراضيات مشفّرة @149/169)؛ الحجم: **5,657 سطرًا (تحت السقف بـ 42)**.
- **`components/edz/select.blade.php` + `edz-select.js`:** دعم `optionDivider`/`isDivider` (تمثيل، تصفية، تنقّل لوحة المفاتيح، منع النقر).
- **التدقيق:** `php -l` نظيف؛ `php artisan view:cache` √؛ أخضر كامل: OrderOfficeSelectionTest 27 + OrdersDefaultProviderTest 7 + OrderFinancialSummaryTest 10 + OrderCompletenessTest 22 + OrderWeightAutoCalcTest 13 + OrderInlineSelectEditTest 12 + OrderTrackingTest 8 + دفعة موسّعة (مدينة/عمود/بوابة/مولّد/حقول/عناصر/استعلامات) 56.
- **ملاحظة:** لم يُشغَّل `npm run build` لتغييرات `edz-select.js` المصدرية (شأن نشر). ملفات تيار العمل الموازي لم تُلمس إطلاقًا.

---

## إرسال الطلبيات إلى شركات التوصيل: تحقق ميداني لكل شركة + إرسال ذرّي (لا إنشاء تتبع قبل ردٍّ سليم) — 2026-09-13 ✅
**القرارات المعتمدة للمستخدم:** إنشاء الطلبية يبقى محليًا كما هو (مودال التاجر + المتجر) **بلا اتصال بالشركة لحظة الإنشاء**؛ الذرّية عند الإرسال فقط (تأكيد-وإرسال / مباشر / جماعي): لا سجل في `order_trackings` ولا حالة `shipped` قبل ردٍّ سليم من الشركة؛ عند رفض الشركة تبقى الطلبية **`confirmed` لإعادة المحاولة**، والتوست يعرض **رسالة الشركة فقط** (لا تفاصيل حقول).

- **الفرع A — تحقق ميداني لكل شركة:** `CarrierIntegrationContract::validateForCarrier(ShippingProvider, Order): array{validated, errors<field, list<string>>}` + تنفيذه في `NoestIntegrationAdapter` وفق قواعد توثيق NOEST v2.3: وجود `api_token`/`guid`، `client` مطلوب و≤255، `phone`/`phone_2` من 9–10 أرقام (تطبيع `+213`/مسافات/شرطات)، `wilaya_id` رقمي من 1–58، `adresse`/`remarque` ≤255، `station_code` مطلوب عند `stop_desk=1`، `reference` ≥5. الشركات بلا адаптер/جابة الرجل → دائمًا صالحة (بلا فحص).
- **الفرع B — إعادة ترتيب `OrderShippingGateway::send()`:** completeness → (confirm عند `confirmFirst`) → `validateForCarrier` (يوقف POST) → **`postToCarrier` أولًا** → فقط بعد نجاحه: تُنشأ `order_trackings` وتُنتقل `preparing→shipped` ويُسجَّل حدث `sent_to_carrier`؛ عند الفشل: commit للمرحلة المحلية (تثبيت `confirmed`) مع إرجاع `error` بلا tracking ولا shipped ولا audit. الناقل بلا تكامل يبقى على المسار المحلي (يُشحن محليًا كما كان).
- **الـ Blade (لا وسم جديد):** `submitConfirmAndSend`/`sendConfirmedOrder`/`confirmBulkSend` — عند `$result['error']`: توست `warning` برسالة الشركة فقط، الطلبية تبقى للحالة القابلة لإعادة المحاولة، والمسار الجماعي يعدّها `skipped` مع سطر رسالة الشركة؛ نجاح التوكيد/الإرسال المباشر يبقى كما هو.
- **ترجمات ×4** في `order_flow`: `carrier_validation_required_field` / `phone_digits` / `max_length` / `min_length` / `wilaya` / `station_required`.
- **يُحلّ انكسارًا موثّقًا سابقًا** (سطر 1646 أعلاه): كانت `validateForCarrier` أُضيفت للعقد بلا تنفيذ في الـ adapter — اكتمل التنفيذ الآن فاختفت التصدّعات عند مزج `TrackingTrashWebhookLabelTest`/`NoestIntegrationTest`.
- **اختبارات جديدة 16/16:** `NoestCarrierValidationTest` 8 (كل قاعدة/حقل) + `SendGatewayCarrierAtomicTest` 5 (نجاح→tracking+shipped؛ رفض→confirmed بلا tracking؛ مباشر-رفض؛ تحقق يوقف POST بلا طلب — `Http::assertNothingSent`؛ محلي بلا تكامل) + `SendCarrierFailureTest` 3 (درج/مباشر/جماعي عبر الـ Volt).
- **التشغيل (صفر انحدار):** Shipping 43/43 (147 تأكيدًا) + Merchant/Order 79/79 (265) + دفعة تكميلية 46/46 (200) → 168 ناجحة؛ `php -l` نظيف (رئيسي × tersi القطع); `view:cache` ناجح ثم `view:clear`.
- **مؤجَّل/مطلوب:** تحقق المستخدم البصري 375/768/1440 — لا تغيير وسمي (توست قائم، سلوك فقط)؛ إعادة التحقق اليدوية من رسائل الشركة في الأوضاع الثلاثة بعد أي تعديل مستقبلي.
---

## Sub-Phase B-2 (2026-09-13) — Order-form UX/perf pass (4 reported issues + office slowness + request reduction)

**Root causes fixed**
- **Picker "can't select" (race):** partner-picker.blade.php used wire:model.live + wire:change on the same select ⇒ TWO concurrent round-trips that overwrote each other (value setter vs. switchFormPartner). Fixed → **deferred wire:model** so the pick + handler land as ONE atomic request. Affects create/edit + confirm drawer (shared partial).
- **"Dropdowns don't close":** edz-select.js select() swallowed every click while loading=true (the lazy office/city fetch window) ⇒ panel stayed open / pick ignored. Now only 
oundtrip && loading blocks, so modal list selects close instantly.
- **Slow office fetch:** loadFormOfficesLazy synced with the carrier API on EVERY dropdown open (GET /desks + ~170 N+1 DB queries per open). Both lazy paths and loadFormOffices now go **DB-first** via new StopdeskOfficeSync::syncIfNeeded() (only contacts the carrier when the store has NO active points for that scope; the refresh button + scheduled job remain the forced-sync paths). Plus per-sync memoization of stateByDeskCode/
esolveCityId.
- **Field order (create/edit modal):** now Partner → Shipment (carrier-capability-scoped; **rider leg ⇒ fixed "delivery"**) → Payment (COD) → Delivery type → Wilaya → Commune → Office. Extracted shared order-delivery-type-toggle + order-destination-fields partials; new order-form-delivery seeds the form; cascade partial slimmed to a composition for the quick-edit modal.
- **Discount:** type selector (amount/percent) removed ⇒ always amount, manually editable; create seeds discount_type='amount'; editing a legacy percent order auto-converts to DZD. Inline table discount editing untouched.
- **Glyph:** أ— → × in order-form-modal items line + duplicate-overlap badge (was re-introduced after a prior fix).

**Verification**
- **Tests: 165 passed** across the merchant order + shipping suites: OfficeSelection 27, Completeness 22, InlineFieldEdit 16, InlineItemsEdit 18, WeightAutoCalc 13, InlineSelectEdit 12, FinancialSummary 10, ConfirmGate 6, DefaultProvider 7, CityScope 8, Tracking 8, DuplicateDetection 5, QuantityCap 5, ProviderColumn 5, ProductPicker 2, PageQueryCount 1.
- iew:cache reused after a Plain-DB data seeding conflict; iew:clear cleared the lock; php -l clean on all touched blade/php; **npm run build** succeeded (public/build regenerated).
- **index.blade.php:** 5,672 physical lines (under the 5,699 Sub-Phase-B ceiling; all new markup lives in partials).
- **Requests per pick:** partner pick 2→1; office lazy open: (network+N+1) → 0 network / 1 DB read; client _remoteCache cap 12→48.

---

## Sub-Phase C (2026-09-13) — Order-form sectioned layout + product-row simplification + partner-picker diagnosis

**Diagnosis report (Section 1, reported before any fix)**
- Complaint: "partner picker doesn't appear" for the Demo Store.
- Root cause: **DATA, not a code bug.** Dev DB has 4 active providers + 3 active riders globally. Per store: "Default Merchant Store" = 1 provider / 0 riders → `singleCompanyMode` static box (auto-selected, asserted by OrdersDefaultProviderTest); **"Demo Store" = 0 providers / 0 riders → empty `<x-edz.select>` renders as a dead, silent control (the reported case)**; "Edzeery Demo Store" = 3/3 → works (proven by OfficeSelection 27 + CityScope 8 + DefaultProvider 7). Code path verified: `mount()` fills `allProviders` (index.blade.php:505) + `riderOptions` (:520); include chain passes via `$this` (no dropped prop); Alpine boot-order race class already defended (alpine:init registration in panel.js + MutationObserver resync in edz-select.js — the genuine race was fixed in Sub-Phase B).
- Resolution: empty-state message + CTA gated on `! $hasProviders && ! $hasRiders` → `route('merchant.delivery', currentStore())` (wire:navigate).

**Changes**
- `partner-picker.blade.php` (79): new `@elseif (! $hasProviders && ! $hasRiders)` branch — dashed surface box + `partner_empty_state` + CTA with arrow icon. singleCompanyMode + unified-select paths untouched.
- `merchant_panel.php` (ar/en/fr/es): new keys `partner_empty_state` + `partner_empty_cta` after `select_provider_first`.
- `order-form-modal.blade.php` (252): three sections using the existing section-header pattern (`text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-3`) + icons user/map-pin/truck; uniform `pt-4 border-t border-surface-border` separators on sections 2–3; Customer grid 1/2/3 cols @375/768/1440; **product-row line-total block removed** (price input + qty stepper kept; totals still compute in order-financial-summary).
- `order-form-delivery.blade.php` (34): redundant `shipping_partner` label removed; destination-fields → office-field composition in the Shipping Partner section.
- `order-destination-fields.blade.php` (36): `$withOffice` param (default true) — delivery quick-edit cascade renders identically.
- `order-office-field.blade.php` (40, NEW): office row extracted from destination-fields (select + refresh btn + hints + error).

**Verification**
- Greps: `data-form-section` × 3; `quantity'\] \* \$item\['price` → 0 matches in order-form-modal; partial line counts 252/34/36/79/40/204 (all ≤ 300); **index.blade.php 5,672 before = after (zero changes)**.
- `php -l` clean ×4 lang files; `artisan view:cache` OK (whole tree compiles, run before tests) then `view:clear`.
- **Tests: 168 passed** across 17 merchant order suites: OfficeSelection 27 · DefaultProvider 7 · FinancialSummary 10 · CityScope 8 · Completeness 22 · InlineSelectEdit 12 · InlineFieldEdit 16 · QuantityCap 5 · InlineEdit 5 · InlineItemsEdit 18 · WeightAutoCalc 13 · ProductPicker 2 · DuplicateDetection 5 · ConfirmGate 6 · PageQueryCount 1 · MobileCardParity 5 · StopdeskSyncUi 6.
- Responsive (375/768/1440): Customer grid 1→2→3 cols (`grid-cols-1 md:grid-cols-2 min-[1440px]:grid-cols-3`); Address + Shipping Partner single-col at all widths with shipment/payment pair stacked at sm+ (`sm:grid-cols-2`); office row keeps `flex-1` select + refresh inline once a stopdesk lane with a company is chosen; product row hides the price input below sm (pre-existing) — freed width flows to the name `flex-1`; edz-select panels are fixed z-70 overlays (unaffected by section gaps). No browser tooling installed (playwright/puppeteer absent) → documented at class level.
- **Deferred:** real-browser pass at 375/768/1440 + screen-zoom audit still owed (no tooling available this run — documented class-level behavior instead).

---

## Sub-Phase C follow-up (2026-09-13) — User review fixes: field order, office into Address, notes grouped with customer, professional >768px

**Reported issues → resolution**
- "Shipping company selection not visible in add/edit popup": the company picker had no label and sat first in a cramped section. Now labeled `delivery_company` and placed in the professional order below shipment/payment with clear spacing.
- "Delivery-type toggle wrongly placed / glued to shipment & payment selects": the home/office toggle is now the LAST control of the Shipping Partner card, separated after the company picker.
- **Correct order implemented (create/edit form):** Shipment type → Payment method → Delivery company/rider → Delivery type (home/office) → then Address section: State → Commune → **Office (shows only for a stopdesk lane with a company)**.
- Notes field removed from below the Order Summary and grouped with the customer info (name/phone/secondary phone + notes inside one card).
- Order Summary content unchanged (subtotal · total weight · delivery cost · editable fixed-amount discount · total) and now framed by an `order_summary` card header.

**Changes**
- `order-form-delivery.blade.php` (27): reordered to shipment+payment grid → labeled company picker → type toggle; office include removed from this partial; no longer owns the `delivery` Alpine scope (hoisted to the modal).
- `order-form-modal.blade.php` (268): card-based sections (`rounded-xl border border-surface-border bg-surface p-4 md:p-5`) for Customer / Shipping Partner / Address / Products / Summary; section order Customer → Shipping Partner → Address (in the shared `x-data` delivery wrapper spanning the two), then Products, warnings, Summary, submit; Customer card gains notes; Address includes `order-destination-fields` with default `withOffice` (office after state/city); items list `max-h-72` (was a fixed `80vh-475px` calc); responsive rhythm `p-4 md:p-6` + `space-y-4 md:space-y-5`.
- `order-destination-fields.blade.php` (36): docblock updated; placement remains shared with the quick-edit cascade.
- Untouched: `index.blade.php` (5,672), `order-delivery-cascade.blade.php` (quick-edit flow keeps its own order), `order-financial-summary.blade.php` (grid classes/markers preserved — tests assert `md:grid-cols-2` + `min-[1440px]:grid-cols-5`), `partner-picker.blade.php`, `order-office-field.blade.php`.

**Verification**
- `artisan view:cache` clean (whole tree compiles) then `view:clear`; `delivery_company` key present in ar/en/fr/es.
- **Tests: 182 passed** across 18 suites re-run after the redesign: OfficeSelection 27 · DefaultProvider 7 · FinancialSummary 10 · Completeness 22 · DuplicateDetection 5 · CityScope 8 · QuantityCap 5 · WeightAutoCalc 13 · InlineItemsEdit 18 · InlineSelectEdit 12 · InlineFieldEdit 16 · InlineEdit 5 · ConfirmGate 6 · PageQueryCount 1 · ProductPicker 2 · MobileCardParity 5 · StopdeskSyncUi 6 · RequiredColumnsHint 14.
- **index.blade.php 5,672 before = after (zero changes)**; partial line counts 268/27/36/40/79 all ≤ 300.
- **Deferred (same as Sub-Phase C):** real-browser screenshot pass 375/768/1440 — no playwright/puppeteer installed; documented at class level.

---

## Sub-Phase C follow-up (2) (2026-09-13) — User-approved: always-visible company picker + discount editor inside its summary cell

**Context (user approval after detailed diagnosis, no execution before approval)**
- The user reported the company picker "still doesn't appear" and the discount "wasn't done" — and clarified (point 3) that the data IS fetched ("لا يظهر فرونت فقط", a frontend-only issue). Deep diagnosis (DB query + code trace) proved:
  - Store data (dev DB): "Default Merchant Store" = 1 provider/0 riders → `singleCompanyMode` static box (no control); "Demo Store" = 0/0 → empty-state box; "Edzeery Demo Store" = 3/3 → real select. → The user's store had exactly ONE company + 0 riders, so the picker genuinely never rendered a selector — by design (old branch), not a bug. The `edz-select` dropdown itself was verified sound (fixed z-70 overlay, repositioned on scroll; modal stacking 60→1→70; no persistent transform → nothing clips it).
  - Discount: the editable fixed-amount editor ALREADY existed (order-financial-summary.blade.php:185-201) since Sub-Phase B and the redesign froze that file — hence "nothing happened". It was also invisible without products (`@if (!empty($form['items']))` gate) and sat under the grid as a thin row.

**Approved changes**
- `partner-picker.blade.php` (72): removed the `singleCompanyMode` static box branch (`data-edz-company-single`) → the picker is now **always a real select** whenever ≥1 partner exists (companies → `__delimiter__` "رجال التوصيل" → riders; sole company auto-selected but visible, `edz-company-select` class kept). Empty-state box retained ONLY for 0/0 stores (a zero-option select is a dead control). Docblock updated.
- `order-financial-summary.blade.php` (195): the discount editor moved **into the discount cell** (`data-financial-discount`): `discount` label → `wire:model="form.discount_value"` number input (min 0 / step 10) + readout (`-currency` / `—`) → optional `discount_reason` input (when value set) → `@error`, then the existing hint line (`compare_price_savings` / `no_offers_available`). Old below-grid `fixed_amount` editor row + trailing `@error` removed. All grid markers (`data-financial-grid…total`), `md:grid-cols-2`, `min-[1440px]:grid-cols-5` untouched.
- `OrdersDefaultProviderTest.php`: the two single-company tests now assert `edz-company-select` + company name visible (`assertSee('Solo Carrier')`) and `assertDontSeeHtml('data-edz-company-single')` (create + edit flows).

**Verification**
- `artisan view:cache` clean (whole tree compiles) then `view:clear` (Windows quirk).
- **Tests: full `tests/Feature/Merchant` run — 485 passed (1992 assertions)** across all suites; targeted suites green first (OrdersDefaultProviderTest 7 + OrderFinancialSummaryTest 10).
- **index.blade.php 5,672 before = after (zero changes)**; partial line counts 72/195 ≤ 300; `data-edz-company-single` gone from all views (only historical Todos.md mention + negative test assertion remain); `fixed_amount` key still used by `orders-table-cell.blade.php` (inline discount type dropdown).
**Sub-Phase C follow-up (3) - REAL root cause found & fixed (the picker never rendered!)**
- User: "la yazal la yazhar select ikhtiyar sharikat al-tawsil... ���� fahs jayyidan 3an almushkil". Deep dumps (Volt::test()->html() written to temp files) revealed the ACTUAL cause: the company <x-edz.select> tag in partner-picker.blade.php was NOT being compiled by Blade at all - it stayed as a literal un-expanded <x-edz.select ... /> tag in the final HTML (browsers render it as an invisible custom element). Root cause: the tag had an @if/@endif DIRECTIVE INSIDE its attributes (@if (! \) :disabled="\" @endif), which breaks Blade anonymous-component compilation. THIS is why the "Delivery company" label rendered with nothing below it, matching the user's complaint across all rounds (single- AND multi-company stores). All prior markup assertions (edz-company-select, ssertSee('Solo Carrier')) were false positives: they matched the literal tag's attributes, not an expanded control.
- Fix (partner-picker.blade.php): hoisted the condition into a computed var in the @php block (@php \ = ! \ && (bool) (\->loadingOffices ?? false); @endphp) and pass :disabled="\". Compiled view now shows AnonymousComponent::resolve(['view' => 'components.edz.select', ...]). Whole codebase audited (regex: directive strictly inside an open component tag before the closing >): ZERO other occurrences.
- Harden Tests (OrdersDefaultProviderTest.php): solo create/edit now assert the EXPANDED control (edz-select__trigger, switchFormPartner(, data-options label marker &amp;quot;label&amp;quot;:&amp;quot;Solo Carrier&amp;quot;) and ssertDontSeeHtml('<x-edz.select'); NEW test: companies?delimiter?riders (asserts __delimiter__ value + rider label in data-options).
- Verification: view:cache clean ? view:clear ? full tests/Feature/Merchant = **486 passed (2005 assertions)**. index.blade.php untouched (5672). Temporary dump test + html dumps deleted.

---

## Round 3 (2026-09-13) - Inline carrier edit list in the orders table: rider preselection + company/rider divider

**Context (user report)**
- In the orders table's inline carrier edit list: (a) the delivery rider is NOT shown as selected even when the order has it; (b) shipping companies and delivery riders should be visually separated (group divider). User clarified both refer to the inline edit list in the table (not the filter, not drawer/modals, not post-save refresh).

**Root cause (proven with temp tests + HTML dumps)**
- Server side was correct: startOrderProviderEdit sets editingValue = rider id + editingValueKind='rider'; rider option present with matching value in desktop AND mobile data-options. The defect was client-side: select.blade.php's hidden input rendered WITHOUT a server-side value attribute (`<input type="hidden" x-model="selected" x-ref="hiddenInput" wire:model="editingValue">`), so Alpine preselection relied on the racy `$wire.$watch` hydration path, and the value-attribute MutationObserver in edz-select.js (applyValue reads getAttribute('value')) was a dead path for wire:model selects (the attribute never existed in server HTML).

**Changes** (all partial/component-side; index.blade.php = zero changes, still 5,672 lines)
- select.blade.php: added `'value' => null` prop; hidden input now renders `@if ($value !== null && $value !== '') value="{{ $value }}" @endif` (backward compatible - no output change when prop not passed).
- NEW resources/views/livewire/merchant/orders/partials/inline-carrier-select.blade.php: shared inline carrier select = providers ($this->allProviders) -> delimiter `__delimiter__` (label merchant_panel.partner_rider, is_divider:true, only when riders exist) -> riders ($this->riderOptions); passes optionDivider="is_divider", value="{{ (string) ($this->editingValue ?? '') }}", option-hint, size="sm", search; retains save/cancel/editingError block.
- orders-table-cell.blade.php: desktop inline carrier block -> @include(...inline-carrier-select) with wireKeyPrefix 'provider-inline'.
- orders-mobile-fields.blade.php: mobile block -> same include with 'provider-mobile' (mobile now also shows editingError - intentional consistency).
- OrderShippingProviderColumnTest.php: +2 tests ("inline carrier edit preselects a rider and groups riders behind a divider"; "inline carrier edit keeps a single undivided list for a company-only store") + DeliveryRider import.

**Verification**
- Temp __TmpRiderSelectTest.php iterated to pass (whitespace-tolerant raw expect()->toContain() assertions - assertSee escapes the needle) then DELETED; rider_select.html dump deleted.
- Rendered dump confirmed: hidden input value = rider id on desktop & mobile (e.g. 01m2edgayf8hhndjg5r290w5ee); data-options = Alpha (isDivider:false) -> divider "Rider" (isDivider:true) -> Rider One.
- Targeted suites green (36 tests): OrderShippingProviderColumnTest (7), OrderInlineSelectEditTest, OrderMobileCardParityTest, OrdersDefaultProviderTest, InlineEditInfrastructureTest.
- **Full tests/Feature/Merchant = 488 passed (2019 assertions).** index.blade.php untouched (5,672). Compiled-view cleanup required restoring storage/framework/views/.gitignore (delete-then-pest workaround for Windows rename lock; do NOT view:cache->view:clear first).

**Notes / possible follow-up**
- Only the two carrier inline selects pass `value`; other inline selects (delivery type, city, stopdesk, agent) still lack the server-side value attribute = same latent hydration race; hardening can be extended if ever reported.

---

## Round 4 (2026-09-13) - Rider still NOT shown as selected in the shipping-company column (display fix + elawer load + defensive JS seed)

**Context (user report, after Round 3)**
- The delivery rider now appears in the inline edit choice list AND gets preselecced correctly, but in the closed (read) column display the orders table still does NOT show the rider as selected — it keeps showing the required hint "Please select delivery company" (and the rider name is absent from the desktop cell and the mobile card).

**Root cause (proven with a permanent test + HTML dump) — two independent defects**
1. `$order['deliveryRider']['name']` was read by the display templates, BUT `Order::toArray()` snake_cases relation keys via `Model::relationsToArray()` (`vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php:403`): a loaded `deliveryRider` relation becomes `$arr['delivery_rider']`, never `$arr['deliveryRider']`. So the rider branch `@elseif (! empty($order['deliveryRider']['name']))` never fired and the cell fell through to the hint. (`shipping_provider` reads were already correct because relation `shippingProvider` → snake `shipping_provider` matches the blade key.)
2. Additionally `deliveryRider` was NOT in `$orderEagerLoads()` (`index.blade.php:750`) — `decorateOrder()`'s `$order->toArray()` had no rider data at all (also absent key). So genuinely two bugs stacked: relation not eager-loaded, AND wrong camelCase key in the templates.

**Changes**
- `index.blade.php`: added `'deliveryRider'` to the `$orderEagerLoads()` `$with` array (line ~750) — first-ever change to this file.
- `orders-table-cell.blade.php` + `orders-mobile-fields.blade.php`: `$order['deliveryRider']['name']` → `$order['delivery_rider']['name']` (4 occurrences each) — matches what `toArray()` actually produces.
- `edz-select.js`: defensive hardening — `_bindServerValue` now uses a conservative `seed()` that only mirrors the Livewire value when no server-baked `value` attribute exists (protects Alpine preseed on re-render). Not the root cause (Livewire reads mergeNewSnapshot BEFORE the morph, ~`livewire.esm.js:8658`), but keeps client-side hydration safe.
- `OrderShippingProviderColumnTest.php`: +1 permanent test "the provider column renders the assigned rider instead of the empty hint" (creates delivery rider, sets `delivery_rider_id`, asserts html contains the rider name and not the hint).

**Verification**
- New test failed BEFORE the template-key fix (dump showed "Please select delivery company") and passes after.
- **Full tests/Feature/Merchant = 489 passed (2021 assertions).** Temp harness files deleted (`storage/harness/`, `__TmpHarnessDumpTest.php`); `storage/framework/views/.gitignore` restored after the delete-then-pest cleanup.

**Notes / possible follow-up**
- Any other template reading a camelCase relation key off the serialized row will silently miss too; audit rule: after `toArray()`, relations are ALWAYS snake_cased. If the browser-harness (puppeteer-core + `php -S` + Alpine CDN) is ever wanted for the client-side preselect proof, the harness design is documented in the Round 4 working notes.

---

## Round 5 (2026-09-14) — Carrier tab shows only shipping-company orders + NOEST stopdesk payload made station-consistent

**Context (user report, in Arabic)**
1. "في تاب شركة الشحن يجب اظهار الطلبيات المرسلة لشركات الشحن فقط وليس التي ترسل لرجل التوصيل ايضا" — the shipping-company tracking tab must list ONLY orders handed to a shipping company, never orders handed to a delivery rider.
2. Commentary on NOEST stopdesk sending: review the NOEST docs vs our payload and make it consistent; a stopdesk order currently cannot be sent because of the office-selection method — the payload must carry only `station_code` + matching destination; a wilaya may have ONE office (Adrar key `"01A"` → inner `code: "1A"`) or MANY (Alger `"16A"`…`"16K"`).

**Diagnosis**
- **Carrier tab:** the trash branch already filters carrier → `whereNotNull('shipping_provider_id')` (`TrackingGridConcern` trash block), but the LIVE carrier branch had NO filter at all — the default tab showed every active shipment, including rider-only orders. Production already enforces rider/provider mutual exclusivity (`TrackingDrawerConcern::saveOrder` refuses both and nulls the counterpart; `assignRider` refuses when a provider exists), so a rider-sent order always has `shipping_provider_id = NULL` → adding the provider filter exactly removes those.
- **NOEST stopdesk:** `createOrder` sent `wilaya_id` from the ORDER's state and `commune` from the ORDER's city, while `station_code` described the SELECTED station. When they disagreed, NOEST rejected with `Le code de wilaya est different de code de station` / `Aucune commune liee a la station choisie` (docs v2.3). The reference userscript builds stopdesk payloads from the station's own wilaya + commune. `desks()` also dropped the response's map keys (`"01A"`) and relied purely on inner `code` → a single-station wilaya (Adrar) could be dropped if inner code were ever absent.

**Changes**
- `app/Livewire/Concerns/TrackingGridConcern.php` (live branch, ~140): carrier else → `$query->whereNotNull('shipping_provider_id')` (mirrors the trash-carrier filter; rider branch untouched). Stats (`baseTrackingQuery(true)`) inherit it automatically.
- `app/Domains/Shipping/Adapters/NoestIntegrationAdapter.php`:
  - `createOrder`: `$isStopdesk` + `$stationCode` computed up front; for stopdesk orders with a station, `wilaya_id` and `commune` are now derived from the station itself (`stopdeskPoint->state->state_code` / `stopdeskPoint->city->name`) with the order's own values as fallback — payload is always station-consistent, and `station_code`/`stop_desk` reuse these derived values.
  - `desks()`: keeps the entry's inner `code`, falling back to the map key (e.g. `"01A"`) only when the inner code is missing — the Adrar single-station padding case can never drop a desk.
  - `validateForCarrier`: new pre-flight error when a stopdesk order's wilaya disagrees with its station's wilaya (`carrier_validation_station_wilaya_mismatch`) — caught before any network call.
- `resources/lang/{ar,fr,en,es}/order_flow.php`: new key `carrier_validation_station_wilaya_mismatch`.

**Verification**
- `php -l` clean on all 6 modified files; `artisan view:cache` fresh (`view:clear` after).
- **NoestIntegrationTest 9 passed (39 assertions)**; **TrackingSearchFilterTest 28 passed (114 assertions)** — every carrier/rider fixture seeds `shipping_provider_id`, so no test encodes the buggy behavior; pagination test (25 provider+rider fixtures) still counts 25 on the carrier tab.
- **TrackingTrashWebhookLabelTest 14 + TrackingGridBatchTest 10 = 24 passed (105 assertions)** — the previously-failing `openEditModal`/`submitEdit` now PASS (the user's in-progress `optionDivider` select work has landed); zero regression.
- **Notes:** a dual-flag order (both provider and rider, only possible via direct DB writes since the app enforces exclusivity) now appears in the carrier tab only if it has `shipping_provider_id`; the rider tab remains `whereNotNull('delivery_rider_id')`. Real-world impact: rider-sent orders (`shipping_provider_id = NULL`) no longer leak into the company tab in either the live list or the trash.

---

## Tooltip batch — Phase 1 (2026-09-14) — Merchant orders table: replace native `title=` with `x-edz.tooltip`

**Scope** (approved: "نفذ" for Phase 1 = orders table only): icon-button tooltips in the orders table + meaningful truncated-cell tooltips. Excluded deliberately: page headers (`layout :title`), `mobile-bottom-sheet` headers, `data-confirm-title` (SweetAlert), and **touch-only surfaces** — `orders/index.blade.php` mobile cards + `orders-mobile-fields.blade.php` are `sm:hidden`/touch-first, and the tooltip is hover-only (`(hover:hover) and (pointer:fine)` media gate), so wrapping them adds Alpine cost with zero touch value. Their native `title=` stay. Toolbar/close buttons in `filter-portal`/`bulk-actions-bar`/`table-settings-modal`/`orders-items-edit-modals` (delete_item) remain `title=` (mobile sheets / outside table-cell scope).

**Component change (no JS touched)**
- `components/edz/tooltip.blade.php`: new `block` prop → adds `edz-tooltip--block` + `edz-tooltip__trigger--block`.
- `css/components/_tooltip.scss`: `.edz-tooltip--block, .edz-tooltip--block .edz-tooltip__trigger { display:block; width:100% }` — for `w-full`/truncated cells (products, quantity/price, notes, address, non-managed branches) so buttons don't collapse to content width. Long labels (items summary, address, notes) live in the bubble; `aria-label` supplies the accessible name instead of `title`.

**Conversion evidence (grep, source files unchanged except these)**
- `orders-table-actions-column.blade.php`: `title=` ~8 → **0**; `x-edz.tooltip` opens **8** (details/confirm/send/cancel/edit/reassign/restore/delete, compact layout only). List layout passes `label="<?= $layout==='compact' ? … : '' ?>"` → inner `label=""` renders the slot bare (no wrapper, no title) → no behavior change.
- `order-events-menu.blade.php`: `title="Order timeline"` → **0** (1 virtual `:title=` on mobile-bottom-sheet remains by design); `x-edz.tooltip` **1** + `aria-label`.
- `orders-table-cell.blade.php`: `title=` ~20 → **0**; `x-edz.tooltip` opens **19** — customer name (2 branches), duplicate/missing badges (×2 partial wrappers), verification span, notes cell (cell `title` removed, both branches wrapped, block on static branch), meta cell, wilaya/city hints (block + `mb-1` wrapper), products cell (cell `title` removed; `$itemsSummaryTitle` via matched comma list; block both branches), quantity/price (block + `aria-label` edit_items), discount button (`$discountTitle` percent→%, amount→currency, else ''), address cell (cell `title` removed; correct `@endif` restored), warehouse toggle (icon-only → tooltip + `aria-label`), status-list close button.
- Total Phase-1 tooltips added: **28** (8 + 1 + 19).

**New test: `tests/Feature/Merchant/OrderTooltipTest.php` (5 tests, helper prefix `otl*` to avoid collisions with `otOrder` in OrderTrackingTest / `ospOrder` in OrderShippingProviderColumnTest)**
- 4 via raw `view()` (compact + list actions columns); products-block coverage goes through `Volt::test('merchant.orders.index')` with `visibleColumns=['products']` (asserts `edz-tooltip--block`, `aria-label` edit_items, and rendered summary text `Default ×2` — the variant name, since the fixture sets `product_variant_id` without `product_id`, so `$i->product?->name ?? variant->name` resolves to "Default").

**Verification**
- `php artisan view:cache` OK (all ~5,672-line index + partials compile).
- **OrderTooltipTest 5 passed (18 assertions)** — one flaky Windows-view-compile `rename Access denied` first run, green on clean re-run (no `view:clear`/delete needed second time).
- **Full `tests/Feature/Merchant` + Unit sweep: 737 passed, 2 failed (2811 assertions) — both failures are NOT from this batch:**
  - `BladeInteractivityPolicyTest` (Unit): fails at HEAD too (offender `merchant/tracking/partials/tracking-tabs.blade.php` conflicts with the @js/@class-never-in-JS-attributes rule) — verified by full stash to clean HEAD, still failing → pre-existing, untouched.
  - `TrackingStatusHistoryPopupTest`: caused by the **Round-5 uncommitted** `TrackingGridConcern` live-carrier filter `whereNotNull('shipping_provider_id')` (its fixture seeds no provider, so the order drops off the carrier tab) — isolated by stashing only that file → 3/3 pass; restoring it reverts to 1 fail. A Round-5 regression the batch missed because it validated only its own suites; needs a follow-up fix (seed `shipping_provider_id` in `tphOrder` or adapt assertion).
- **Follow-ups queued:** real-browser responsive pass 375/768/1440 (especially new `block` cells + long summary bubbles) — browser tooling still not installed (documented class-level); fix `TrackingStatusHistoryPopupTest`; then Phase 2 (products/variants/stock) → Phase 3 (delivery providers + announced-rates) → Phase 4 (`components/status.blade.php` optional `tooltip` prop).

## Tooltip batch — Phase 1 follow-up (2026-09-14) — both pre-existing suite failures fixed; full suite green

- **BladeInteractivityPolicyTest (Unit):** offender `merchant/tracking/partials/tracking-tabs.blade.php` used `@js($this->trackingTab)` inside `x-data` (Blade interpolation in a JS-bearing Alpine attribute). Fixed per the policy itself: server value now goes via `data-edz-active-tab="{{ $this->trackingTab }}"` on the wrapper and is read with `$el.dataset.edzActiveTab` inside `restoreTab()`. `persistTab` unchanged.
- **TrackingStatusHistoryPopupTest:** the Round-5 `TrackingGridConcern` live-carrier filter (`whereNotNull('shipping_provider_id')`) filters the **orders table**, so the fixture needed a shipping provider on the ORDER itself, not only the tracking row. `tphOrder` now seeds a `ShippingProvider` ('Tph Carrier') and sets both `order->shipping_provider_id` and `order_tracking->shipping_provider_id`.
- **Full suite: 739 passed (2812 assertions) green** — was 737 passed / 2 failed. `OrderTooltipTest` 5 + tracking popup 3 + policy 2 = 10 all pass in isolation too.


---

## منسّق عناصر الطلب الموحّد (Unified Order Items Formatter) — 2026-09-14 ✅

**الهدف:** مصدر واحد لتجميع أسطر الطلب حسب المنتج (مع تسميات خيارات المتغيّر + SKU) يُستخدم في كل مواضع العرض، بدل تكرار منطق التجميع عبر الكومبوننتات.

**ما تَمّ:**
- **`app/Domains/Orders/Support/OrderItemsGrouper.php`** (115 سطرًا) — يجمّع الأسطر حسب `product_id` أو المتغيّر مع الحفاظ على ترتيب الظهور، ويدمج كميات المتغيّرات المتطابقة (`keyBy`/`first`/`update`) وينتج `variant_id`, `option_label` (قيم الخيارات مفصولة بـ ` / ` مثل «Medium / Beige»)، `sku`.
- **`app/Domains/Orders/Support/OrderItemsFormatter.php`** (225 سطرًا) — أربعة عوارض من مصدر واحد:
  - `toCompactString()` — «الاسم ×الكمية» بفواصل + فاصل سطر عند 4+ عناصر، وسقوط عدد الأسطر عند كل الأسماء فارغة (`$items->count()` = عدد الأسطر الخام لا مجموع الكميات).
  - `toDetailedLines()` — مجموع الكميات فوق «تسمية الخيار × الكمية» لكل سطر.
  - `toTableGroups()` — مجموعات `{product_id, product_name, chips}` وشظايا `{variant_id, label, sku, qty, price}` (يُضاف `variant_id` ليعيد فكّ التسطيح في نموذج التعديل).
  - `toFlatItems()` — أسطر مسطّحة `{variant_id|product_id|name|sku|price|qty}` تُحافظ على استقرار تحوّر حقول نموذج التعديل (المفتاح = `variant_id`).

**مواضع الربط الخمسة:**
- `NoestIntegrationAdapter::productSummary()` → `toCompactString()` مع `loadMissing('items.variant.optionValues.option')` (كان منطق مقصوصًا يحذف التفرّعات بلا خيارات).
- `TrackingDrawerConcern::openLabel()` → `labelData['items']` = `toDetailedLines()` (قائمة أسطر نصية) + eager-load `items.variant.optionValues.option`.
- `orders/index.blade.php`: مُغلَقَتَا `$buildItemSummary`/`$buildItemGroups` قبل `$decorateOrder` (سطرا 914/920)؛ `decorateOrder` يبني `items_summary` مسطّحًا عبر `$order->items->pipe(fn($items) => app(OrderItemsFormatter::class)->toFlatItems($items))->toArray()` (سطر 975 — يُبقي بحث الاستقبال `items_summary'] = $order->items` = 1) و`item_groups` عبر `$buildItemGroups($order)` (سطر 976) + `use ($buildItemGroups)`؛ `openOrderDetails` يستخدم المغلقَين معًا (`use ($buildItemSummary, $buildItemGroups)`) للمسارين (رواة with وhydrate عند ~1727/1728) — بلا تكرار منطق.
- `orders-table-cell.blade.php` (`@case('products')`) — يقرأ `item_groups` (مع سقوط مسطّح إلى `items_summary`) ويبني `$itemsSummaryTitle` ويرسم `<x-edz.order-item-chips>` في فرعي التعديل وغير التعديل داخل `x-edz.tooltip` (مع تصميم `block`) وبلا `title=` أصلي.
- `order-details-modal.blade.php` — يطوف `item_groups` ويحسب `$groupSubtotal` (مجموع price×qty لكل مجموعة) ويصنع شريحة `<x-edz.order-item-chips :groups="[$group]" />` + المجموع الفرعي.

**المكوّن الجديد:** `components/edz/order-item-chips.blade.php` — اسم المنتج + «تسمية الخيار ×الكمية` لكل تفرّع» وشارات SKU الموحّدة والعنوان، بمفاتيح `variant`/`sku` الموجودة ×4 لغات.

**الإصلاحات الجانبية:** إزالة الـ `@if` الزائدة عن اللازم في خلية العميل، و`eading` تسميات المتغيّرات من المصدر الموحّد بدل خريطة `optionValueId => name` المتناثرة.

**التوثيق:** سطر في `CarrierIntegrationContract` يحيل الباحثين إلى `productSummary()` الجديد (بلا أسلوب واجهة جديد).

**أدلة القبول (تحقّق آلي):** `php -l` نظيف على كل الملفات المغيَّرة + `php artisan view:cache` (FRESH-CACHE-OK) + عدّاد `Blade::render` يعيد 1 للبحث `items_summary'] = $order->items` ومصغًّا 5 للمغلقات و2 لاستخدام `item_groups|order-item-chips` في الـ partials.

**الاختبارات:** ملف جديد `tests/Feature/Merchant/OrderItemsFormatterTest.php` — **16 ناجح (العدّادات والتجميع عبر طلبَين، تفرّعات متعددة، خياران «/», أسطر فارغة، وسقوط `/\d+ items/`)**: كلها خضراء. قيود قاعدة البيانات الموثّقة في الاختبار: `order_items.product_variant_id` NOT NULL (لا أسطر بلا تفرّع)، `product_variants.sku` NOT NULL، `order_items` فريد `(order_id, product_variant_id)`، هواتف العملاء فريدة، و`ProductOption`/`ProductOptionValue` يتطلبان `store_id`.

**السويت كاملة:** **739 ناجح (2812 assertions)** — صفر انحدار عن «Tooltip Phase 1 follow-up». `view:clear`+`view:cache` سليمان.

---

## دفعة شبكة التتبع — المراحل 0–2: تقسيم الشبكة + إعادة ترتيب الأعمدة + عمود المنتجات وفلتره — 2026-09-15 ✅

**النطاق (بموافقة المستخدم على خطة التسع مراحل):** الجولة الأولى من «تحديث شبكة التتبع d9e36ea» — تُنفَّذ مرحلة بمرحلة، كل مرحلة تُختبر وتُعرض، ثم يُطلب الموافقة قبل التالية.

- **المرحلة 0 (تقسيم الشبكة):** `tracking-list.blade.php` من 396 سطرًا → **88** (جدول + بطاقات الموبايل + ترقيم الصفحات)؛ استُخرج `partials/tracking-row-cell.blade.php` (**193**، `@switch($colKey)` خلية لكل عمود) و`partials/tracking-mobile-card.blade.php` (**129**). استخراج صرف بلا تغيير سلوكي.
- **المرحلة 1 (إعادة ترتيب الأعمدة):** `TrackingColumnConcern.php` — ترتيب أساسي جديد `number, tracking_number, customer, state, city, products, total, tracking_status, notes, <provider|delivery_rider>, shipping_date, assigned_to, confirmed_by, actions` مع إدراج عمود التبويب بعد الفهرس 9؛ `trackingDefaultOrder()` للتبويبين؛ `prefs_version` 2→3 في `loadTrackingTabPreferences` و`saveColumnPreferences` (إعادة تعيين legacy لمرة واحدة). نُقّحت توقعات الأعمدة في `TrackingTrashWebhookLabelTest` و`TrackingSearchFilterTest`.
- **المرحلة 2 (عمود المنتجات + فلتر متعدد البحث):** استُخرجت `app/Livewire/Concerns/TrackingFilterConcern.php` (**141**) من `TrackingGridConcern` (التي تجاوزت 457 سطرًا — استخراج قبل الإضافة): `clearFilters`/`availableFilterGroups`/`activeFilterCount`/`setFilter`/`toggleTrackingStatus` + جديد `toggleProductFilter`. `TrackingGridConcern` (349): eager-load `items.product:id,name` (لا N+1)، `products` الصفّي `{name, qty}` مجمّعًا بالاسم (ترشيح الأسماء الفارغة/«—»)، فلتر `whereHas('items', whereIn('product_id'))` بمنطق OR. `index.blade.php` (Volt, 326): `allProducts` من `Order::withTrashed()->join('order_items')` (مؤهَّل `orders.store_id` لرفع غموض العمود). الواجهة: `tracking-table-header` أضاف `products => products` + التفعيل؛ `tracking-filter-portal` أضاف قسم المنتجات (متعدد الاختيار، نمط `status`، `sm:max-h-64`/`sm:w-52`)؛ `tracking-row-cell` خلية المنتجات: اسم أول منتج مقتطع `max-w-[9rem]` + شارة `+N` داخل `x-edz.tooltip` بقائمة كاملة، وإلا `—`.
- **ملاحظة Blade:** `:label="…->implode("\n")"` يكسر محلّل وسوم المكوّنات (`"` داخل تعبير الخاصية) — الحل: بناء السلسلة في `@php` وربط `:label="$var"`؛ وعدم وضع مكوّن داخل فرع `@if` يليه `@else` (ينكسر الاقتران) — الفروع المستقلة `@if…@endif` المتعددة سليمة.

**الاختبارات:** ملف جديد `tests/Feature/Merchant/TrackingProductsColumnTest.php` — **4 ناجح (9 assertions)**: عمود المنتجات إلزامي بالموضع الافتراضي 5 وغير قابل للإخفاء؛ الصف المخطَّط مجمَّع الكميات `{name, qty}` (طلبان، أحدها بمنتجين) + `allProducts`؛ الطلبية بلا أصناف تعرض قائمة فارغة (شرطة `—`)؛ فلتر منتجات متعدد `toggleProductFilter` يجمّع OR عبر طلبَين ثم `setFilter('products', [])`. السويت ذو الصلة (ورد التتبع) **61 ناجح (252 assertions)** منفردة — 57 قائمة + 4 الجديدة → صفر انحدار. `php -l` + `view:cache` سليمان.

- **المرحلة 3 (بحث داخل قوائم الفلاتر + استثناء المالك):** مكوّن Alpine جديد `filterableList()` في `resources/js/panel.js` — يقرأ القائمة من `data-items` والاختيارات من `data-active` (سمات `data-*` عادية، لا `@json` داخل سمات Alpine لسياسة Blade)، مع `query`/`filtered`/`isActive`/`activeCls`/`checkCls`؛ يعيد قراءة `data-active` عند `edz-filter-open`/`edz-toolbar-filter-open` (تنظيف المستمعين في `destroy()`). حوّلت 6 قوائم في `tracking-filter-portal` (products، provider، rider مع الدمج `$riderSearchItems` للعدّادات، assigned_to، confirmed_by، city) و5 في `tracking-filter-bar-portal` (provider، city، rider، assigned_to، confirmed_by) إلى `x-for` + حقل بحث بعنوان `__('general.search')`؛ بقي قسم status (تعداد 9 عناصر) مُخدَّمًا من الخادم. **درس Blade جديد:** `:class="…"` على مكوّن `x-edz.icon` يُجلَّب جافًا كتعبير PHP (ينكسر) — الحل `x-bind:class` داخل المكوّنات فقط (الوسوم العادية تبقي `:` لـ Alpine).
- **استثناء المالك:** `allMembers` في `index.blade.php` يستبعد عضوية `user_id = store.user_id` (المالك ليس وكيل إسناد/تأكيد). حدَّثت `TrackingGridBatchTest`: مساعد `tgbManager` (مستخدم + عضوية `manager`)، اختبارا assigned_to/confirmed_by يعتمدان المدير ويؤكدان استبعاد عضوية المالك، وأسماء الأعمدة «Assign Agent»/«Confirm Agent» عبر العلاقات (تظل أسماء الأعضاء في الصفوف قائمة من العلاقات لا من `allMembers`).
- **التحقق:** السويت ذو الصلة (ورد التتبع) **61 ناجح (252 assertions)** صفر انحدار + `BladeInteractivityPolicyTest` ناجح؛ `npm run build` نظيف؛ `php -l` + `view:cache` سليمان.

- **المرحلة 3 (مراجعة — كاش البحث فرونت فقط، بطلب المستخدم):** البحث أصبح فرونت-أونلي: القوائم تُحمَّل مرة واحدة في كاش JS وتُحدَّث فقط عند تغيّر البيانات الخادمية. جديد `app/Livewire/Concerns/TrackingSearchCacheConcern.php` (`searchCacheSeed`/`searchFingerprint` = md5(json)…/`searchOptionsLists` يُدمج عدّادات الرجّلين خادميًا في مجموعة `riders`)؛ في `panel.js` وحدة `edzSearchCache` (البصمة + القوائم بمفتاح المجموعة) ومكوّن `edzSearchSeed` يقرأ `data-search-cache` فقط؛ `filterableList` يعيد قراءة `data-active` الصغيرة عند المتشرين (ولم يعُد يقرأ `data-items` في كل فتحة). `index.blade.php`: وسيط البذر الخفي `<div class="hidden" x-data="edzSearchSeed()" data-search-cache="@json($this->searchCacheSeed())">` بعد جذر `<div>` — يستبدله Livewire فتُعاد تهيئة Alpine (تحديث الكاش) فقط حين يغيّر JSON. البوَّابتان تحوّلتا إلى `data-group="products|providers|riders|members|cities"` وحُذفت كتل دمج الرجّلين `@php` من كليهما. تحقق: `php -l` + `view:cache` + **42 ناجح (172 assertions)** (TrackingSearchFilterTest+TrackingGridBatchTest+TrackingProductsColumnTest) + سياسة Blade + `npm run build`.

---

## دفعة شبكة التتبع — المراحل 4–7: فلتر التاريخ المستقل + خلية شعار الناقل + درج بلا مؤلّفات + متدرّج الحالات — 2026-09-15 ✅

**النطاق (بموافقة المستخدم «اكمل كل Phases»):** استكمال مراحل إعادة تصميم شبكة التتبع بلا بوابة موافقة لكل مرحلة — تُختبَر كاملًا وتُسجَّل ثم تنتقل للتالية.

- **المرحلة 4 (فلتر التاريخ كمنطلق مستقل):** أُزيل `'date'` من `availableFilterGroups()` في `TrackingFilterConcern` (لم يعد داخل بوابة التمرير) وبَقِي `date_from`/`date_to` معدودًا في `activeFilterCount()` صراحةً خارج الحلقة. زر تقويم مستقل في `tracking-toolbar.blade.php` (شارة عدّاد 1/2 عند الامتلاء، يبث `edz-date-filter-open` مع `{ el }`) + جزئية جديدة `partials/tracking-date-portal.blade.php` (نمط `dropdownPosition()` + ورقة سفلية موبايل/علبة سطح مكتب + حقلا flatpickr `wire:model.blur` + زر مسح) تُضمَّن في `index.blade.php`. أُزيل قسم DATE الميت (أسطر ~142–164) و`'date' => __('order_flow.filter_date')` من `$groupLabels` في `tracking-filter-bar-portal` (قسم التاريخ في بوابة الترويسة للعمودي بقي كما هو). جديد `clearDateFilter()` — مسح الحقلين في رحلة واحدة؛ استخدمته شريحة التواريخ في التولبار.
- **المرحلة 5 (خلية شعار الناقل):** eager-load `shippingProvider.carrier` في `TrackingGridConcern` (كتلتا `with()` — لا N+1) + حقل `provider_logo` في خريطة الصف = `shippingProvider?->carrier?->logo`. خلية `@case('provider')` في `tracking-row-cell`: دائرة حرف-أول (letter-circle) بخلفية `bg-accent-surface` تحمل شعار الناقل `w-6 h-6 rounded-full object-cover` فوقها (`relative` متدرّج — لو انكسر الصورة زال `onerror="this.remove()"` وظهر الحرف) + اسم مقتطع `max-w-[10rem]`. عمود الناقل تلقائيًا خاص بتبويب الناقل فقط.
- **المرحلة 6 (الدرج بلا مؤلّفات):** أُزلت `@include` لمكوّنَي `carrier-note-composer` و`tracking-history-timeline` من `order-drawer.blade.php` (بيانات التحميل محفوظة). حُدِّث `OrdersTrackingColumnTest`: الدرج لم يعُد يضم تطمين `partials.tracking-history-timeline` (`not->toContain`).
- **المرحلة 7 (نافذة الحالات المتدرّج + نقل المؤلّف + رابط التتبع العام):**
  - **المتدرّج:** جديد `partials/tracking-status-stepper.blade.php` — مراحل أفقية `shipped→in_transit→out_for_delivery→delivered→returned` (بترتيب `OrderWorkflow::carrier()`، تسميات/ألوان mystatuskit)، عقدة المنجزة `check` بخلفية accent، الحالية حلقة ملونة، المُتبقية ترقيم محايد، وصلات `flex-1` ملوّنة للمنجز؛ الاستثناءات (failed_attempt/lost/damaged/returning) تُبقي السكة محايدة وتُعرض كرقاقة mystatuskit جانبية.
  - **نافذة الحالات `tracking-history-popup`:** تضم المتدرّج + رابط «تتبّع على موقع الشركة» الخارجي (إن وُجد القالب) + المؤلّف المنقول + الخط الزمني المشترك. `statusHistoryMeta` زادت `tracking_id`/`tracking_status`/`carrier_supports_api_notes`/`public_tracking_url`، و`openStatusHistory` يحمّل `latestTracking.shippingProvider.carrier` (لا N+1). `carrier-note-composer.blade.php` أُعيدت كتابتها لتقرأ سياق `statusHistoryMeta` (ليست `drawerTracking`)؛ بعد الإرسال تُزامَن `statusHistory` مع `drawerStatusHistories` إن كانت النافذة مفتوحة لنفس الطلبية.
  - **تتبع عام:** إضافة `public_tracking_url_template` nullable إلى `carriers` (هجرة `2026_09_16_000002`) + حقل Filament `TextInput` بجوار الشعار (helperText يشرح `{tracking_number}`) + `Carrier::publicTrackingUrl(string): ?string` (يستبدل `{tracking_number}`/`{trackingNumber}` بـ `urlencode`، يفشل آمنًا عند فراغ) + مفاتيح `order_flow.carrier_track_on_site` ×4 لغات.
- **التحقق:** `php -l` نظيف على كل ملفات PHP المتأثرة + هجرة/`view:clear`+`view:cache` + اختبار Unit جديد `tests/Unit/CarrierPublicTrackingUrlTest.php` (3) + سياسة Blade + **سيّورة التتبع 64 ناجح (265 assertions)** صفر انحدار + `npm run build` (تحذير chunk >500kB قائم غير مسبب).

---

## دفعة شبكة التتبع — المرحلة 8: اعتماد جماعي لدى الناقل (FAB + /valid/orders مجزّأة) — 2026-09-16 ✅

**النطاق (استكمال خطة المراحل المعتمدة):** اعتماد التسليم الجماعي من صفحة التتبع عبر FAB (تبويب الناقل فقط) — تحليل صفحة الصفوف الحالية، مسح باركود فردي داخل النافذة، ثم إرسال الجاهز للناقل بواجهة `/valid/orders` المجزّأة (≤100/استدعاء) مع إحداثيات OrderEvent لكل شحنة.

- **الناقل/التدقيق:** `OrderAuditService::carrierValidated()` (نوع حدث `carrier_validated` + حمولة الشركة/رقم التتبع) + مفاتيح `order_flow.event_carrier_validated` ×4 لغات. `OrderShippingGateway`: استُخرج `markValidated()` (طابع `carrier_validated_at` + سجل `OrderTrackingHistory` + حدث التدقيق، بلا HTTP، متطابق) و`recordValidationFailure()`؛ نجاح/فشل `validate()` الأصلي يفوض إليهما — فأصبح الاعتماد الفردي يُدقّق الحدث أيضًا.
- **التتبع (Volt):** جديدة `app/Livewire/Concerns/TrackingBulkValidateConcern.php` — `openBulkValidateModal` (تحليل صفوف الصفحة الحالية إلى `{order_id, number, provider, tracking_number, ready, reasons}` بنفس شروط منطق دفعة الطلبات: ناقل، تكامل يدعم `validateOrder`، رقم تتبع، غير معتمد)، `closeBulkValidateModal`, `confirmBulkValidate` (تجميع حسب الناقل ثم تجزئة `array_chunk(...,100)` → `validateOrders` لكل جزء، تثبيت النجاحات عبر `markValidated` والفشل عبر `recordValidationFailure`، إعادة تحميل الصفوف + توزيع)، `bulkValidateFromBarcode` (يبحث الطلبية برقم التتبع ويسلك مسار الاعتماد الفردي المؤَدلج ثم ينقّح التحليل). ملاحظة: مفاتيح `Order` من نوع ULID نصّي — لا `(int)` عند جمع المعرّفات.
- **الواجهة:** جزء `partials/tracking-bulk-validate.blade.php` — FAB ثابت أسفل-end (يختبئ في تبويب الرجّلين/السلة/بلا صلاحية) + نافذة `x-edz.modal` فيها `x-edz.barcode-scan-input` (`wireScanMethod="bulkValidateFromBarcode"`) وقائمتا جاهز/مؤجَّل وزر تأكيد (معطّل عند صفر جاهز أو أثناء التنفيذ). `index.blade.php`: `uses[]` + 5 مفاتيح حالة + `@include`.
- **الاختبارات:** جديدة `tests/Feature/Merchant/TrackingBulkValidateTest.php` — **12 ناجح (138 assertions)**: رؤية/إخفاء FAB (تبويب/سلة/صلاحية)، تحليل العداد، الاعتماد الجماعي المستمر + `OrderTrackingHistory`/`OrderEvent`، التجزئة (>100 → استدعائان لعناوين `/valid/orders` مع مجموع 105 تتبعات فريدة)، الباركود الفردي، الرفض بلا `order.dispatch_validate` على المداخل الثلاثة. حُصِّر تأكيد `TrackingDispatchValidateTest` بشعار النافذة ليقيس الدرج نفسه (`wireScanMethod="validateShipmentFromBarcode"` غائب عند الاعتماد) بدل النص الشامل المتسرّب من النافذة الجديدة.
- **التحقق:** `php -l` نظيف + `view:clear`+`view:cache` + تشغيل مجمَّع 102 ناجح (486 assertions) ثم السويت المعني المغلق (TrackingDispatchValidate 7 + TrackingBulkValidate 12 + OrdersTrackingColumn 5 + سياسة Blade 2) — **26 ناجح (184 assertions)** صفر انحدار + `npm run build` (تحذيرات Sass/chunk قائمة غير مسببة).
---

## الفلاتر الموحّدة + بحث أمامي + التحقق المجمّع للطلبات — 2026-09-15

**الهدف (بموافقة صريحة "نفذ"):** إصلاح صفحة التتبع (فلاتر الروؤس وزر Filters لا تُظهر شيئًا)، توحيد نمط القوائم مع `/orders`، جلب القوائم مرة واحدة + بحث أمامي (بلا كاش ثقيل حتى لا تتأخر البيانات المحدّثة)، وربط التحقق المجمّع لصفحة الطلبات بنفس تدفق `/valid/orders` المقسّم.

- **المكوّن/JS:** في `resources/js/panel.js` حُذف `edzSearchCache` و`edzSearchSeed` و`filterableList`، وأُضيف `edzSearchableList` (يقرأ `data-items` + `data-active` المُخدّمين، `query`/`filtered`/`isActive`/`activeCls`/`checkCls`/`toggleActive(id)` للمتعدد، يعيد قراءة النشط عند فتح البوابة وينظّف المستمعين في `destroy()`). حُذف `app/Livewire/Concerns/TrackingSearchCacheConcern.php` وdiv البذرة من `tracking/index.blade.php` (لا كاش عام — القوائم تُخدَّم من الحالة الحالية دائمًا).
- **التتبع:** تحويل بوابتي `tracking-filter-portal` و`tracking-filter-bar-portal` بالكامل إلى `edzSearchableList` (products/providers/riders مع دمج العدادات/المستخدمون×2/المدن). فلاتر الموضع STATE: `where('state_id', $f['state'])` في `baseTrackingQuery`، مفتاح رأس في `tracking-table-header`، قسم قابل للبحث في البوابتين، رقاقة STATE+CITY في الشريط، `'state' => null` في القيم الافتراضية و`clearFilters`، ومراقب `filters.state`.
- **زر Filters:** أُزيلت بوابة `@if(!empty($this->availableFilterGroups()))` — الزر ظاهر دائمًا؛ `availableFilterGroups()` تُعيد كل المجموعات (مقيّدة بالتبويب فقط: state/provider/tracking_statuses/amount/city/rider/assigned_to/confirmed_by).
- **الطلبات:** حوّلت 7 قوائم في `orders/partials/filter-portal.blade.php` (wilaya/status/provider/stopdesk/city/assigned_to/confirmed_by) و6 في `orders/partials/orders-filter-bar-portal.blade.php` إلى `edzSearchableList`. **درس Blade:** `@json` داخل سمة يُغلق عند أول توازن أقواس — تعبير سهم/مصفوفة فيه `Status::for(...)->label()` يُبتر منتصفًا → نُقلت العناصر إلى خاصيتي مكوّن `searchableStatuses`/`searchableMembers` (في mount: `{id,name,color}` من facade وحل `user.name`) ورُبطت كمعرّف بسيط.
- **التحقق المجمّع للطلبات:** استُبدل الحلقة التسلسلية (`$gateway->validate` لكل طلب) في `$confirmBulkValidate` بالتدفق المقسّم من `TrackingBulkValidateConcern`: تجميع حسب المزوّد + `array_chunk(...,100)` + `$adapter->validateOrders()` + `markValidated`/`recordValidationFailure`؛ أُثرت صفوف `bulkValidateAnalysis` بـ `order_id/provider/tracking_number`. حُدّث `BulkDispatchValidateTest` (اختبار الرفض أصبح استجابة واحدة بخرائط `passed`/`failed` ضمن الدفعة).
- **التحقق:** `php -l` + `view:clear`+`view:cache` + الاختبارات المستهدفة **67 نجاحًا** (BulkDispatchValidate 4 + BladeInteractivity 2 + TrackingSearchFilter + TrackingBulkValidate + TrackingDispatchValidate + OrdersPageQueryCount + OrdersTrackingColumn + OrderShippingProviderColumn) + `npm run build`. **دين يُسجَّل:** أحجام الملفات تتجاوز ميزانية 300 سطر الموروثة (filter-portal 402 / orders-filter-bar 580 / tracking-filter-bar 357) — استخراجها إلى بارتيات لاحقًا.

---

## إصلاح أخطاء قوائم الاختيار القابلة للبحث في صفحة التتبع — 2026-09-15

**الباغ (المستخدم):** "يوجد مشاكل في قوائم الاختيار التي لها البحث ليست متناسقة ولا تظهر اي معلومات في صفحة تتبع الطلبيات سواء في رؤوس الاعمدة او في القوائم الفرعية يجب الفحص بشكل معمق".

**الأخطاء المكتشفة:**
1. **BUG 1 (حرج — عدم التوافق بين الأنواع):** `panel.js` `isActive(id)` يستخدم `String(id)` لكن `parseActive()` يُخزّن القيم الخام من JSON (أعداد) → `[5].includes("5")` دائمًا `false` → لا تظليل ولا `aria-pressed`.
2. **BUG 2 (حرج — فقدان أعلام HEX):** `@json` متعدد الأسطر لقائمة الراكبين يُترجم إلى `json_encode(..., 512)` فقط — تظهر فقط `512` وتفقد `15` (`JSON_HEX_TAG|APOS|AMP|QUOT`). إذا احتوت اسم أي راكب `"` أو `<` أو `'` → السمة تُقطَع → `JSON.parse` يُرمي خطأ → `items = []` → القائمة فارغة.
3. **BUG 3 (متوسط — قراءة `parseItems()` مرة واحدة فقط):** `parseItems()` تُنفَّذ عند التهيئة فقط؛ عند تغيير الفلاتر يُعيد Livewire رسم العناصر بالـ morph لكن Alpine تحتفظ بالحالة القديمة (`items` فارغ أو قديم).

**الإصلاحات المنفّذة:**
- **BUG 1:** في `panel.js` `parseActive()`: إضافة `.map(String).filter(Boolean)` بعد JSON.parse → القيم تُوحَّد دائمًا كنصوص. الآن `isActive("5")` يتطابق مع `this.active = ["5"]`. (`panel.js:254-258`)
- **BUG 3:** في `_onPortalOpen` (المستمع لأحداث `edz-filter-open` و`edz-toolbar-filter-open`) → كلا الدالتيين `parseItems()` و`parseActive()` تُستدعيان عند فتح أي بوابة → البيانات تبقى طازجة بعد كل رسم. (`panel.js:234-237`)
- **BUG 2 + BUG 3 معاً:** نُقلت عناصر الراكبين إلى خاصية خادمية `searchableRiders` (نفس نمط `searchableStatuses`/`searchableMembers` في الطلبات). في `TrackingGridConcern::loadTrackingStats()` — يُحسب القائمة الكاملة لكل الراكبين مع دمج العدادات من `riderRiders` keyed by id، حتى لو لم توجد شحنات (العداد = 0). (`TrackingGridConcern.php:209-250`). في `tracking/index.blade.php` — أُضيف `'searchableRiders' => []` إلى `state()`. (`tracking/index.blade.php:50-53`). في البوابتين — استُبدل `@json(collect(...)...)` المتعدد الأسطر بـ `@json($this->searchableRiders)` بسيط ومحصّن. (`tracking-filter-portal.blade.php:113`، `tracking-filter-bar-portal.blade.php:257`)

**التحقق:** `php -l` + `view:clear`+`view:cache` — Compiled views تؤكد أن `json_encode($this->searchableRiders, 15, 512)` يحتفظ بالأعلام. الاختبارات المستهدفة: TrackingGridBatchTest 18 + TrackingSearchFilterTest 20 = **38 نجاحًا (326 شهادة)** + BladeInteractivityPolicy 2 + `npm run build` (246.7 kB panel).

---

## إكمال فلاتر المنتجات + المبالغ + عدّاد الفلاتر في صفحة التتبع — 2026-09-15

**السياق (استكمال جولة "إصلاح أخطاء قوائم الاختيار"):** كانت صفحة التتبع تفتقد فلتر المنتجات في `availableFilterGroups`، ورقاقة المبالغ، ودعم `hasActiveFilters` للمنتجات/المبالغ — لم يكتمل دمج المنتجات من محرك الفرز الموحّد.

**الإصلاحات المنفّذة (داخل البوابتين بدون حشر في index):**
- **`availableFilterGroups()`** (`TrackingFilterConcern.php`): أُضيف `'products' => null` إلى `$map` → المنتجات متاحة دائمًا في بوابة الشريط.
- **`activeFilterCount()`** (`TrackingFilterConcern.php`): أُضيف `case 'products'` (يحسب فلاتر `filters['products']`) + `case 'amount'` كان موجودًا.
- **`$hasActiveFilters`** (`tracking-toolbar.blade.php`): أُضيف شرط `count($this->filters['products']) > 0` + `filled(amount_min) || filled(amount_max)`.
- **رقاقتا المنتجات والمبالغ** في الشريط قبل زر Clear: رقاقة منتجات تعرض أسماء المنتجات (lookup في `allProducts`) ورُقاقة مبالغ تعرض `min—max`.
- **قسم منتجات كامل قابل للبحث** في `tracking-filter-bar-portal.blade.php` (بعد المبالغ، قبل المدينة): `edzSearchableList` + `@json($this->allProducts)` مع `toggleProductFilter()` متعدد التحديد.
- **`$groupLabels`** في `tracking-filter-bar-portal.blade.php`: أُضيف `'products' => __('merchant_panel.products')`.

**الاختبارات الجديدة:** 5 اختبارات في `TrackingSearchFilterTest` + دالتا مساعدة `tsfCreateProducts`/`tsfAttachProduct` (مع إنشاء ProductVariant، إعادة استخدام الفاريانت، وربط `store_id`). الاختباران اللذان افترضا `activeFilterCount` خاصية عمومية نُقلا إلى `instance()->activeFilterCount()` لأنها **دالة** (ليست خاصية). اختبار الشبكة يثبت دلالة **OR** للمنتجات (`whereIn`): اختيار p1 → طلبان، +p2 → 3، ثم p1 فقط → 2.

**التحقق:** `php -l` + `view:clear`+`view:cache` (الـ compiled يؤكد قسم open === 'products' في بوابة الشريط) + `TrackingSearchFilterTest` **33 نجاحًا (148 شهادة)** + `BladeInteractivityPolicy` 2 + **سويت التجار كاملًا 531 نجاحًا (2276 شهادة)** + `npm run build`.

**دين يُسجَّل:** `tsfAttachProduct` ينشئ ProductVariant واحدًا لكل منتج (يُعاد استخدامه عبر الطلبات) لتفادي فخ UNIQUE `product_variants.store_id+sku` — إذا تحوّل المنتج لاحقًا لنظام variants متعددة، سيحتاج المساعد إعادة نظر.

---

## اصطلاح أخطاء تدقيق الفلاتر/البطاقات في صفحة التتبع — 2026-09-15

**السياق:** تدقيق حيّ على الكوميت `d9e36ea` رفع 9 مشاكل (3 حرجة/4 متوسطة/2 صيانة). عند الفحص على **الكود الحالي** تبيّن أن أغلبها أُصلح في جولات سابقة؛ بقي 3 مشاكل فعلية أُصلحت الآن.

**تقرير التحقق (قديم vs حالي):**
- **أُصلحت سابقًا (ادعاءات متقادمة):** #2 زر Filters ظاهر دائمًا (tracking-toolbar.blade.php:58 بلا `@if(!empty(...))`)، #3 كل القوائم في البورتين تستخدم `edzSearchableList` مع حقل بحث، #4 المالك مستثنى من `allMembers` (index.blade.php:240)، #1 جزئيًا `city`/`amount` موجودان في `$hasActiveFilters` مع شرائح.
- **الباقي حقيقي وأُصلح الآن:**
  1. **شريحة الراكب الناقصة:** الراكب كان في `$hasActiveFilters` لكن بلا شريحة عرض → إذا فُلتِر براكب فقط يظهر الشريط فارغًا. أُضيفت شريحة راكب (تسمية + lookup في `searchableRiders` + زر مسح) في `tracking-toolbar.blade.php` قبل شريحة `assigned_to`.
  2. **`allProviders` يستثني الشركات المعطّلة:** `where('is_active', true)` يمنع فلترة الطلبيات التاريخية لشركة أُهملت. أصبح `is_active OR id ∈ طلبيات المتجر` (نفس نمط الاشتقاق من الطلبيات لـ allCities/allStates) في `tracking/index.blade.php`.
  3. **بطاقة الموبايل تعرض «شركة الشحن» في تاب الراكب:** `tracking-mobile-card.blade.php` كانت تعرض `$s['provider']` دائمًا (وتظهر `• —`) بينما سطح المكتب يخفي عمود provider في تاب الراكب. أصبح `@if(trackingTab==='rider') راكب @elseif(provider ∈ visibleColumns) شركة @endif` — مطابقة للسلوك المكتبي.
- **يحتاج قرار المستخدم:** #7 فلتر الملاحظات (نص حر — الأقل فائدة). **يحتاج تحققًا بصريًا:** #8 موضع القوائم المنبثقة RTL (dropdown-position.js uses كتابة `left` فيزيائية، مع قصّ داخل الشاشة). #9 دين حجم الملفات مسجّل مسبقًا.

**قرارات المستخدم (2026-09-15):**
- **#7 فلتر الملاحظات:** مرفوض — لن يُضاف فلتر للملاحظات.
- **#8 موضع القوائم RTL:** المستخدم سيتحقق بصريًا عند 375/768 ثم يقرر.
- **#9 حجم `tracking-filter-bar-portal.blade.php`:** مؤجل — سيُدرس لاحقًا لرفاكتور احترافي (تفكيك حسب مجموعة الفلتر).

**التحقق:** `view:clear`+`view:cache` — compiled يثبت شريحة الراكب + فرع `elseif(in_array('provider', ...))` في البطاقة. الاختبارات: TrackingSearchFilterTest **33** + TrackingGridBatch/TrackingProductsColumn/TrackingStatusHistory **19** + BladeInteractivity **2** = **54 نجاحًا.**

---

## إصلاح جذري: قائمة الولاية المكسورة في الفلاتر + رأس العمود — 2026-09-15

**العرض:** قائمة الولاية في بوابة الفلاتر (شريط الأدوات) ورأس العمود تظهر مشوهة: الاقتباسات المزدوجة للـ JSON تكسر سمة `data-items` فيفشل `JSON.parse` وترتيب الأسماء يبدو خاطئًا.

**السبب الجذري (ليس ترقيعًا):** `data-items="@json($this->allStates)"` تضع JSON خامًا في سمة **مزدوجة الاقتباس**. `@json` يترجم إلى `json_encode(..., 15, 512)` الذي يهرب `"` **داخل القيم** فقط (`\u0022`) — لكن الاقتباسات **البنيوية** للـ JSON (حول المفاتيح: `[{"id":...` ) تبقى `"` حرفية فتُغلق السمة مبكرًا ويفكك المتصفح البنية. بديل عن فرضية «بيانات قذرة بها `"`» — القاعدة نظيفة (58 ولاية بلا أي اقتباس) والملفات المترجمة كانت سليمة `15,512`.

**الحل الجذري المطبق:** تحويل كل `data-items`/`data-active` في ملفي تتبع-المسارات إلى سمة **مفردة الاقتباس** `data-items='@json(...)'` — فتصبح `"` البنيوية آمنة داخل السمة، بينما أي `'` في القيم (مثل `M'Sila`) مهربة تلقائيًا بـ `\u0027` (لأن 15 يتضمن JSON_HEX_APOS). تحقّق تجريبيًا: `@json` لا يُنتج أي `'` خام — أمان تام للقيم.

**الملفات (7 مواضع لكل ملف):**
- `tracking-filter-portal.blade.php` — products/provider/rider/assigned_to/confirmed_by/state/city
- `tracking-filter-bar-portal.blade.php` — state/provider/products/city/rider/assigned_to/confirmed_by

**التحقق:** `view:clear`+`view:cache` — compiled يثبت `data-items='<?php echo json_encode(..., 15, 512) ?>'` في كل المواضع الـ 14. `TrackingSearchFilterTest` **33 نجاحًا (148 شهادة)**. لا تبقى أي `data-items="@json` في partials/tracking.

**قرار للجولة القادمة (معلق):** نفس الجذر موجود أيضًا في `orders/partials/filter-portal.blade.php` + `orders/partials/orders-filter-bar-portal.blade.php` (~13 موضعًا). القرار: التوحيد على النطاقين أم الاكتفاء بالتتبع. **لم يُطبَّق بعد — بانتظار قرار المستخدم.**

---

## جلب كل الولايات والبلديات + سكرول عمودي في فلاتر التتبع — 2026-09-15

**المشكلة:** قوائم الولاية والبلدية كانت تُجلب من **الطلبيات فقط** (`Order::whereIn('state_id'...)` / `whereIn('city_id'...)`) — أي تُعرض فقط الولايات/البلديّات التي ظهرت فعليًا في طلبيات المتجر، وليست كل القائمة الوطنية (58 ولاية / 1540 بلدية). **قرار المستخدم:** جلب **الكل** وعرضه مع بحث + سكرول عمودي.

**ما طُبّق (`tracking/index.blade.php`):**
- `allStates` ← `State::orderBy('name')` (كل الولايات — 58) مع `id` سلسلة.
- `allCities` ← `City::orderBy('name')` (كل البلديات — 1540) مع `id` سلسلة.
- `Order` import ما زال مستخدمًا (بوابة المنتجات/الشركات) — لم يُكسر شيء.

**سكرول عمودي (يحافظ على البحث ثابتًا):** كل قائمة `edzSearchableList` في البورتين أصبحت تحوي لفًّا داخليًا `<div class="max-h-[40vh] sm:max-h-60 overflow-y-auto edz-scroll">` حول الـ `template x-for` — على الموبايل 40vh، وعلى الديسكتوب `sm:max-h-60` (15rem). أقسام state/city تحديدًا رُفع سقف حاوية البورتمن إلى `sm:max-h-[70vh]` لأنها الآن تضم 1540 بلدية.

**الملفات (14 قائمة أصلحت):**
- `tracking-filter-portal.blade.php` — 7 أقسام (products/provider/rider/assigned_to/confirmed_by/state/city) + `sm:max-h-[70vh]` لقسمي state/city.
- `tracking-filter-bar-portal.blade.php` — 7 أقسام + `sm:max-h-[70vh]` للحاوية (كل الأقسام).

**التحقق:** `view:clear`+`view:cache` — compiled يثبت الألفاف والسقوف الجديدة. `npm run build` — كلاسات `max-h-[40vh]`/`sm:max-h-60`/`sm:max-h-[70vh]`/`overflow-y-auto` موجودة في CSS النهائي. الاختبارات: TrackingSearchFilterTest **33 نجاحًا (148 شهادة)** + OrderCityScopeProviderTest **8 نجاحًا (13 شهادة)** (لم يتأثر — سكوبه الخاص للطلبات لا يستخدم قوائم التتبع).

**ملاحظة أداء:** 1540 بلدية تُحمَّل كـ JSON داخل سمة `data-items` مرتين (بوابتان) — ~40KB. البحث كاملٌ client-side عبر `filtered`، والسكرول يمنع بناء 1540 زرًا مرئيًا فيُخفف ضغط الـ DOM. احتياطي إن اصطدمنا بالأداء لاحقًا: بوابة lazy-load عبر Livewire (تجربة DB على `filters.state`) — **غير مطبَّق الآن**.

**التوحيد مع orders:** مؤجّل بقرار المستخدم حتى الحصول على نتيجة التتبع الاحترافية.

---

## دفعة الفلاتر والمصطلحات والمظهر — 2026-09-15 (2)

**قرار المستخدم:** "في كثير من العمليات سأقول النتيجة النهائية فقط" — تنفيذ مباشر للدفعة الثانية دون انتظار.

**1) إزالة فلتر إجمالي الطلبية من رأس عمود المجموع:**
- حُذف `'total' => 'amount'` من `$headerFilterKeys` في `tracking-table-header.blade.php` → عمود المجموع لم يعد يعرض أيقونة فلتر.
- حُذف قسم Amount من بوابة رأس العمود (`tracking-filter-portal.blade.php`) لأنه لم يعد قابلًا للفتح من الهيدر.
- فلتر المجموع ما زال متاحًا في بوابة شريط الأدوات (قائمة Filters) — قرار بقائه هناك لأنه مستقل عن رأس العمود.

**2) تحسين حجم القائمة المنسدلة على الشاشات الكبيرة (max-height: 350px):**
- كل لفّات السكرول الداخلية الـ 14: `sm:max-h-60` (240px) ← `sm:max-h-[350px]` في البورتين.
- حاوية الهيدر: `sm:max-h-64` ← `sm:max-h-[350px]` للأقسام غير state/city (التي تبقى `sm:max-h-[70vh]`).

**3) ترتيب الأعمدة — عمود الإجراءات دائمًا الأخير افتراضيًا:**
- `TrackColumnConcern::toggleDraftColumn()`: عند تفعيل عمود جديد يُدرج **قبل** `actions` بدل إلحاقه في نهاية القائمة — فيبقى actions الأخير حتى يعدِّل المستخدم مكانه يدويًا (أسهم/سحب في لوحة الإعدادات).

**4) فلاتر جديدة في التتبع:**
- `can_open` (السماح بفتح الطلبية) — ثلاثي All/N/A: `orders.can_open`.
- `send_from_carrier_warehouse` (يُرسل من مخزن الناقل) — ثلاثي: `orders.send_from_carrier_warehouse`.
- `shipment_type` (التوصيل/الاستبدال/التقاط الطرد) — فردي: `orders.shipment_type`.
- المس: `filters` state (index.blade.php + clearFilters)، `availableFilterGroups` (للكل — لا تقييد بتبويب)، `activeFilterCount` (حالة `!== null` للثاليات لأن `filled(false)` خاطئة)، `setFilter` (تحويل قيمة منطقية للثاليات + اعتماد `delivery|exchange|pickup` فقط)، WHERE في `TrackingGridConcern::baseTrackingQuery`، بوابة شريط الأدوات (`$groupLabels` + 3 أقسام).
- الترجمة: `merchant_panel.can_open` (كان ناقصًا رغم استخدامه في orders) + `refund_request` + كلمات مفتاحية للقدرات (أدناه) — أُضيفت للغات الأربع.

**5) تصحيح المصطلحات (ar/fr/en/es):**
- `merchant_panel.delivery`: ar 'التسليم' ← 'التوصيل' (fr بالفعل Livraison).
- `merchant_panel.exchange_label`: ar 'تبديل' ← 'استبدال الطرد'.
- `merchant_panel.pickup_label`: ar 'استلام' ← 'التقاط الطرد'.
- مفاتيح جديدة في `merchant_panel.php` للغات الأربع: `capability_free_shipping_mode` (إرسال مجاني متوفر في API)، `capability_express_economic` (أنواع التوصيل الاقتصادي والعادي)، `capability_api_notes` (ملاحظات التوصيل عبر API)، `capability_order_delete` (حذف الطلبيات قبل الفاليديشن عبر API)، `capability_price_sync` (مزامنة أسعار التوصيل).

**التحقق:** `php artisan view:clear`+`view:cache` (PHP 8.3.28) — compiled يتضمن `setFilter('can_open'/'send_from_carrier_warehouse'/'shipment_type')` في بوابة التتبع. `npm run build` — `max-h-\[350px\]` في CSS النهائي. الاختبارات: `vendor/bin/pest --filter=TrackingSearchFilter` **33 نجاحًا (148 شهادة)** + `--filter=OrderCityScopeProvider` **8 نجاحًا (13 شهادة)**.

**ملاحظة:** `sm:max-h-60` لم تعد مستخدمة في أي من البورتين (كل المواضع أصبحت `sm:max-h-[350px]`).

---

## توحيد صفحات orders مع tracking (القوائم 350px) + تدقيق الأداء والأخطاء — 2026-09-16 (3)

**قرار المستخدم:** "ابدأ التوحيد حيث ارتفاع قوائم الفلاتر ماكس 350px، وافحص مشاكل الأداء والأخطاء وقم باصلاحها" — توحيد بوابات فلاتر orders على نفس النمط المعمَّد في tracking.

**1) توحيد بوابة رأس العمود `orders/partials/filter-portal.blade.php`:**
- إصلاح خطأ JSON: كل `data-items="@json(...)"`/`data-active="@json(...)"` المزدوجة ← مفردة `data-items='@json(...)'` (7 قوائم: wilaya/status/assigned_to/shipping_provider/stopdesk_point/city/confirmed_by). كانت القيمة المغلفة بمزدوجة تكسر JSON عند وجود علامات داخل المحتوى (مشكلة حقيقية في العمليات).
- لفّ القوائم السبع بالحاوية الداخلية `max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll` (نفس نمط tracking).
- تقسيم سقف الحاوية: `sm:max-h-[70vh]` لـ wilaya/city (قوائم ضخمة)؛ `sm:max-h-[350px]` لبقية أقسام القائمة؛ `sm:w-*` بقيت كما هي.

**2) توحيد بوابة شريط الأدوات `orders/partials/orders-filter-bar-portal.blade.php`:**
- إصلاح JSON المزدوج (7 قوائم) ← مفردة (نفس الفئات).
- لفّ القوائم السبع بالحاوية `max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll`.
- حاوية البوابة: أُضيف `sm:max-h-[70vh]` (كانت بلا سقف على الشاشات الكبيرة — `max-h-[75vh]` تبقى للموبايل).

**3) المقياس الكلي بعد التوحيد:**
- 28 حاوية سكرول داخلية موحّدة (14 في tracking + 14 في orders) = `max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll`.
- صفر `data-items="@json` مزدوجة في كل merchant (بحث شامل: 0)، وصفر `sm:max-h-60`/`sm:max-h-64` في أي من البورات الأربع.

**4) تدقيق الأداء والأخطاء (ما فُحص وما لم يُغيّر):**
- `edzSearchableList` (panel.js): يقرأ `dataset.items`/`dataset.active` من جذر Alpine — متوافق تمامًا مع السمة المفردة؛ `filtered` يعمل filter محلي فقط عند الكتابة (لا شبكة)؛ `parseItems` يُعاد على فتح البوابة عبر `edz-filter-open`/`edz-toolbar-filter-open` + عند كل إعادة render (مقصود ليعكس أحدث حالة خادم).
- حجم البيانات: قائمة المدن ≈ 1540 بلدية تُشحن في `data-items` (≈40KB) عند ظهور عمود city — تصميم قائم في tracking أيضًا؛ **مؤجّل** (لا تلفيق الآن): lazy-load عبر Livewire عند الفتح بدل الشحن مع كل render.
- `filtered` يمرر كامل القائمة عند الكتابة (≤1540 عنصرًا) — مقبول، لا حاجة للفضاء الافتراضي الآن.
- قائمتا حالة منسدلتان في orders خارج النطاق (ليست فلاتر): `orders/index` + `orders-table-cell` (قوائم تغيير الحالة لكل صف، `sm:max-h-64`) — تُرك كما هما؛ بند توحيد تالٍ محتمل.

**التحقق:** `php artisan view:clear`+`view:cache` (PHP 8.3.28) — compile نظيف، وبالكاش 7 ملفات بصيغة `data-items='` وصفر بصيغة مزدوجة. الاختبارات: `vendor/bin/pest` (TrackingSearchFilterTest + OrderCityScopeProviderTest) **41 نجاحًا (161 شهادة)** — لا تراجعات.

**مؤجل (توحيد تالٍ محتمل):** قوائم حالة الأوامر `sm:max-h-64` في `orders/index` + `orders-table-cell`؛ lazy-load لقوائم الفلاتر الضخمة عبر Livewire.

---

## إصلاح خطأ "Undefined array key product_id" في orders — 2026-09-16 (4)

**العرض:** عند الفلترة بنوع التوصيل (shipment_type/delivery_type) بعد مسح الفلاتر، ظهر `Undefined array key "product_id"` داخل `orders/index.blade.php` (في الجزء المترجم من `loadOrders`).

**السبب الجذري:** `mount` (state) يُهيّئ `filters` مع `'product_id' => null`، لكن `$clearFilters` حذف المفتاح `product_id` من القائمة (كان فيه `'product' => ''` فقط). بعد مسح الفلاتر يصبح `filters` بلا `product_id`، فأي فلتر لاحق (مثل نوع التوصيل) → `$setFilter` → `loadOrders` → سطر بناء الاستعلام `if (!empty($f['product_id']))` يقرأ مفتاحًا غير موجود → خطأ PHP 8. المفتاحان `source` و`send_from_carrier_warehouse` موجودان في clearFilters (لهذا لم يظهر الخطأ معهما من قبل).

**الإصلاح (`resources/views/livewire/merchant/orders/index.blade.php`):**
- أُضيف `'product_id' => null` إلى `$clearFilters` بعد `'shipping_provider'` — تطابق تام بين مفتاحي mount (26) وclearFilters (26 الآن).
- تحصين دفاعي: شرط الاستعلام أصبح `!empty($f['product_id'] ?? null)`.
- تحقّق شامل: كل مفاتيح الـ 24 المقروءة في `loadOrders` موجودة في clearFilters (لا فجوات أخرى).

**التحقق:** `view:clear`+`view:cache` ناجح (PHP 8.3.28). `OrderCityScopeProviderTest` **8 نجاحًا (13 شهادة)** — لا تراجعات.

---

## إصلاح تتابع فلترة الولاية → البلدية في orders — 2026-09-16 (5)

**العرض:** مشاكل عند الفلترة بالولايات والبلديات (قائمة بلدية فارغة من شريط الأدوات، نتائج خاطئة/فارغة عند تغيير الولاية مع بقاء بلدية قديمة).

**السبب الجذري (تشخيص عميق):**
- orders يملأ `allCities` من mount فارغًا ويعتمد على استدعاء يدوي `loadFilterCities` **حصرًا من رأس العمود** (filter-portal)؛ بينما tracking يملأ كل بلديات الجزائر مرة واحدة في mount (بدون cascade أصلًا) — لهذا القائمة تعمل هناك من أي بوابة.
- زر الولاية في **شريط الأدوات** (orders-filter-bar-portal:158) كان يستدعي `setFilter('wilaya')` فقط بلا `loadFilterCities` → قائمة البلدية تبقى فارغة/قديمة.
- `$setFilter` لا يصفّر `filters['city']` عند تغيير الولاية → `state_id=جديدة AND city_id=قديمة` → نتائج فارغة/مغل�لوطة.
- زر "الكل" للولاية (شريط الأدوات) لا يمسح البلدية، عكس زر "—" في رأس العمود الذي كان يمسحها.

**الإصلاح (`orders/index.blade.php` + `orders/partials/filter-portal.blade.php`):**
- التتابع نُقل إلى **نقطة الدخول الواحدة** `$setFilter`: عند `key === 'wilaya'` يصفّر `filters['city']` ويملأ `allCities` ببلديات الولاية (أو `[]` عند null/0) قبل `loadOrders` — فيصحيح نتيجة الطلب الحالي نفسه.
- وحّدت أزرار رأس العمود: `@click="setFilter('wilaya', item.id); close()"` بلا استدعاء `loadFilterCities` (زائدة الآن)؛ وزر "—" بلا `setFilter('city')` مكرر (كان يرسل طلبًا ثانيًا يُعيد تحميل الطلبات). زر "الكل" في شريط الأدوات يعمل تلقائيًا عبر `$setFilter`.
- لا يوجد مسار مباشر آخر يغيّر `filters['wilaya']` (تحقق: كل الكتابات عبر `setFilter`، بما فيها شريحة الحذف في عرض الفلاتر النشطة).

**التحقق:** `view:clear`+`view:cache` ناجح (PHP 8.3.28). `loadFilterCities` أصبحت كسجودًا غير مُستدعاة (تُركت دون حذف).

**ملاحظة:** تطبيق نفس النمط على تتابع `shipping_provider` → `stopdesk_point` في شريط الأدوات (أزرار "كل"/الاختيار لا تمسح stopdesk_point ولا تستدعي `loadFilterStopdeskPoints`) — بند تالٍ محتمل.

---

## إصلاح الجذر "الفلتر يحدد رقم 1 دائمًا" في orders (UID مقابل int) — 2026-09-16 (6)

**العرض:** اختيار أي ولاية/بلدية/شركة توصيل في الفلترة → القيمة المطبَّقة دائمًا "1"، الشريط يعرض الرقم بدل الاسم، والفلترة لا تصفي (كل النتائج أو صفر).

**السبب الجذري (تشخيص عميق + إثبات بالبيانات):** قاعدة البيانات بالكامل تستخدم **معرّفات نصية UID** (مثال: ولياية `01m1ppwt6z0dy9vphxtw8nhg9s`، مزوّد `01m1pr6aq2033qqvtbjmceaxpt`، منتج، عضو، حالة — كلها نصوص طويلة). لكن `$setFilter` في orders كان يمرّر `wilaya`/`city`/`assigned_to`/`shipping_provider` عبر `$intFilters` → `(int)` لأي UID = **1** (PHP يقرأ "01" فقط). ما زاد الطين بلةً: MySQL يقارن عمود النص `state_id` برقم `1` بتحويل النص إلى رقم → كل UID يبدأ بـ "01…" يطابق `1` → الفلتر يختار **كل** الطلبات لا واحدًا.

**الإثبات بالداتا:** `where('state_id', UID-حقيقي)` = 9 طلبات، بينما `where('state_id', 1)` القديم = 13 (الكل). المزوّد: 10 مقابل 11.

**الإصلاح (`orders/index.blade.php`):**
- حذف `$intFilters` من `$setFilter` (القيم تُخزَّن كـ UID نصي كما ترسلها JS — نفس سلوك tracking الذي يعمل).
- `status`: استبدال `array_map('intval', …)` بـ `array_values(array_filter(…))` (حاضر لقفل المسار الاحتياطي؛ المسار الفعلي `toggleStatusFilter` كان يخزّن النص الصحيح أصلًا).
- إزالة `(int)` عن `product_id` في استعلام loadOrders (خط 861) — كان نفس الخلل للمنتج.
- إزالة `(int)` عن ربط `city_id` في `orderByRaw` لترتيب مكاتب النموذج (خطا 2741 و3690) — نفس الفئة (التفضيل "بلدي أولًا" لم يكن يعمل).

**التحقق:** `view:clear`+`view:cache` ناجح؛ `OrderCityScopeProviderTest` **8/8 (13 شهادة)**؛ إثبات بالداتا أن الفلتر بالـ UID يحسب 9/10 صحيحة والقديم 13/11 خاطئة.

**ملاحظة:** بقية `(int)` في الملف مشروعة (كميات/ترقيم صفحات/عدّادات). كسجود `loadFilterCities`/`loadFilterStopdeskPoints` (استُغني عنهما برأس العمود) تُركتا دون حذف — بند تنظيف محتمل.

---

## دمج رجال التوصيل في فلتر "شركة التوصيل" (orders) — 2026-09-16 (7)

**الطلب:** في رأس عمود شركة التوصيل تظهر رجال التوصيل مع شركات التوصيل، وليس الشركات فقط.

**الإصلاح (`orders/index.blade.php` + البوابتان):**
- ملكية جديدة `allCarrierFilters`: قائمة موحّدة (شركات `kind=provider` + أجراء `kind=rider`) للفلاتر فقط — `allProviders` بقيت شركات فقط حتى لا تتأثر المحرّرات المضمنة/مقطاعة الشريك (inline-carrier-select, partner-picker, editProviderOptions).
- `$setFilter` تقبل بارامتر ثالث `$kind` وتخزّن `filters['shipping_provider_kind']`؛ عند اختيار راجل تصفّر `stopdesk_point` و`allStopdeskPoints` (الراجل توصيل منزلي بلا نقاط).
- الاستعلام: `kind=rider` → `where('delivery_rider_id', …)` وإلا `where('shipping_provider_id', …)`.
- `loadFilterStopdeskPoints`: لا استعلام عندما يكون الفلتر راجلًا (توفير + تناسق).
- البوابتان (رأس العمود + شريط الأدوات) والقائمة القديمة في أسفل الرأس: `data-items='@json($this->allCarrierFilters)'` + تمرير `item.kind` في النقرة.
- الشريط (active chip) يقرأ الاسم من `allCarrierFilters` (يعمل مع الراجل والشركة).
- قسم stopdesk في بوابة رأس العمود: عند اختيار راجل يعرض "اختر المزوّد أولًا" بدل قائمة فارغة.

**التحقق:** `view:cache` نظيف؛ إثبات بالداتا: `where('delivery_rider_id', UID-راجل)` يعطي 1/1 طلبات والـ `where('shipping_provider_id', UID-مزوّد)` يعطي 10؛ `OrderCityScopeProviderTest` **8/8 (13 شهادة)**.

---

## عنقود تخصيص الحالات وحالات تتبع شركات التوصيل — خطة مرقّمة (2026-09-16)

> **الحالة: تخطيط فقط — لا تنفيذ قبل موافقة صريحة على كل مرحلة على حدة.**
> **الترتيب المقرر:** 1 ← 2 ← 3 ← 4 ← 5 ← 6. كل مرحلة خطة مستقلة بذاتها (لا دمج)؛
> تُنفَّذ بعد موافقة المستخدم، وتُعلَّم ✅ في Todos.md فور اكتمالها مع أدلة التحقق.
> **قيود إلزامية على كل مرحلة:** استجابة 375/768/1440؛ مراجعة الوثائق (PROJECT_PLAN.md، MerchantPanelAudit.md، errorsTodo.md، Todos.md، OrdersrefactorplanFixed.md، DESIGN_SYSTEM.md) قبل التنفيذ؛ فحص الكود الحي لا الافتراض؛ هوية Apple Design (توكنز `--edz-*`/`ink-*`/`surface-*` فقط)؛ الأداء (بلا استعلامات/إعادة رسم زائدة)؛ عدم حشر كل شيء في صفحة index الرئيسية.

### المرحلة 1 — بنية «التخصيص»: الشريط الجانبي + التوجيه + صفحة الحالات (إطار + تابات) ✅

**الهدف:** منفذ جديد في «إعدادات المتجر» باسم **التخصيص**، مع صفحة حالات بتابات (تأكيد / تتبع الشركة / تتبع الراجل) بإطارها أولًا ثم تُملأ بالمراحل التالية.

**الأدلة المثبتة:**
- التوجيه: مجموعة Layer-3 في `routes/merchant.php:59-115` (`prefix merchant` + `{store:slug}` + السلسلة الوسطية: `ResolveStoreFromRoute/EnsureStoreResolved/EnsureStoreMembership/EnsureHasStoreRole: 'owner,admin,manager,staff'/EnsureStoreIsActive`). كل صفحات التاجر Volt عبر `Volt::route(...)` (orders 94، tracking 95، order-settings 99، storefront 107، settings 108).
- السايد بار: مجموعة «إعدادات المتجر» في `store-sidebar.blade.php:358-396` (المجموعة الفرعية `edz-sub-store`)؛ حالة التوسّع `storeOpen` في الأسطر 43-46؛ بوابة الصلاحيات `withData[...]` في 58-64.
- قالب الصفحات: Volt المضمّن `layout('components.layouts.store')` (يُغلّف `x-layouts.panel` + sidebar=store) + `<x-edz.page-header>` + `edz-card edz-card--padded` + تابات بصقفة `$tab` (نمط `order-settings.blade.php:436-449`) أو تعليمة Alpine-pills (نمط `store-settings.blade.php:141-204`).

**التعديلات المقترحة (بالملفات):**
1. `routes/merchant.php` (بعد سطر 99):
   ```php
   Volt::route('/{store:slug}/customization/statuses', 'merchant.customization.statuses')
       ->name('customization.statuses');
   ```
   **قرار مفتوح (اختيار المستخدم):** المسار المقترح `…/customization/statuses` مع «حالات التأكيد» أول تاب داخليًا (أنظف)، أو المسارات العميقة القابلة للاشتراك `…/customization/statuses/confirmation` (تلبيةً لطلب المستخدم حرفيًا).
2. `store-sidebar.blade.php`:
   - سطر ~62: `$withData['canViewCustomization'] = canStore(StorePermissionEnum::STORE_UPDATE->value);`
   - الأسطر 43-46: إضافة `'merchant.customization.*'` إلى قائمة `storeOpen` (توسّع تلقائي عند الدخول).
   - بعد سطر 393 (رابط storefront): رابط فرعي «الحالات» بشرط `@if ($canViewCustomization)` وأيقونة `adjustments` (نسخ نمط 387-393) → `route('merchant.customization.statuses', $store)` + تفعيل `goods()` عند `merchant.customization.*`.
   - سطر 358: الجارد الافتتاحي → `@if ($canViewStoreSettings || $canViewStorefront || $canViewCustomization)`.
3. صفحة جديدة `resources/views/livewire/merchant/customization/statuses.blade.php` (Volt مضمّن):
   - `layout('components.layouts.store')` + mount: `abort_unless(canStore(STORE_UPDATE->value), 403)`.
   - `state('tab' => 'confirmation')` + `<x-edz.page-header>` (title/description) + تابات `$tab` بقاع الـ pills:
     - `confirmation` (حالات التأكيد) — تُملأ بالمرحلة 2.
     - `carrier_tracking` (حالات تتبع شركة التوصيل) — تُملأ بالمرحلة 3.
     - `rider_tracking` (تخصيص حالات تتبع الراجل) — تُملأ بالمرحلة 4.
   - كل تاب داخل `edz-card edz-card--padded` بحالة فارغة مؤقتة (`empty state` بقالب `x-edz.icon` + نص).

**الترجمة ×4** (`resources/lang/{ar,en,fr,es}/merchant_panel.php` + `order_flow.php`): `customization`، `statuses`، `tab_confirmation`، `tab_carrier_tracking`، `tab_rider_tracking` + العنوان/الوصف/حالة الفراغ.

**التصميم/الأداء:** توكنز `--edz-*` فقط (قاعدة TailAdmin + DESIGN_SYSTEM قسم 59-65)؛ لا استعلامات إضافية في هذه المرحلة (إطار فارغ)؛ فحص 375/768/1440 (بطاقات/درج للشاشات الصغيرة).

**التحقق:** `php -l` على الملف الجديد + `view:cache` ناجح + فتح الرابط بعضو STORE_UPDATE وعضو بدونه (403) + توسّع مجموعة «إعدادات المتجر» تلقائيًا + تسمية الرابط.

**الاعتماديات:** أساس المراحل 2/3/4.

**✅ منجز (2026-09-16).** **قرار المستخدم:** مسار واحد `customization/statuses` (أنظف) بدل المسارات العميقة. **ما نُفّذ:**
- `routes/merchant.php` (إعدادات المتجر): `Volt::route('/{store:slug}/customization/statuses', 'merchant.customization.statuses')->name('customization.statuses')`.
- `store-sidebar.blade.php`: `canViewCustomization = STORE_UPDATE` (سطر ~63)؛ `storeOpen` + `merchant.customization.*` (توسّع تلقائي)؛ رابط «الحالات» بأيقونة `adjustments` بعد storefront (شرط `@if ($canViewCustomization)` + تفعيل عند `merchant.customization.*`)؛ الجارد الافتتاحي + `$canViewCustomization`.
- `livewire/merchant/customization/statuses.blade.php` (جديد): `layout('components.layouts.store')` + mount `abort_unless(canStore(STORE_UPDATE->value), 403)` + `$tab` (confirmation/carrier_tracking/rider_tracking) بنمط أسطر order-settings 436-449 + 3 تابات داخل `edz-card edz-card--padded` بحالات فارغة.
- الترجمات ×4 (`merchant_panel.php`): `customization`، `customization_desc`، `statuses`، `tab_confirmation`، `tab_carrier_tracking`، `tab_rider_tracking`، `customization_empty`.
**التحقق:** php -l نظيف (صفحة+ترجمات ×4) + `route:list` يُظهر المسار + **`StatusCustomizationPageTest`** (اختبار Feature جديد، نمط MerchantDeepLinkTest): owner 200 (تابات ×3 + رابط سايد بار `Statuses` + `edz-sidebar__sub-link--active` + `store: true` + نص الحالة الفارغة) + staff 403 — 2 ناجح (12 تأكيدًا) + السلسلة الكاملة **781 (3066) نظيفة** + Pint (3 ملفات، 3 إصلاحات).

### المرحلة 2 — حالات التأكيد: عرض + تخصيص لكل متجر + تاب الترتيب ✅

**الهدف:** تاب «حالات التأكيد» (بذرة Confirmation Pipeline الفعلية — **10 حالات** مُبذورة: pending, confirmed, no_answer_1/2/3, postponed, wrong_number, out_of_stock, duplicate, on_hold، sort 1..10؛ **`draft` غير مبذور** للـ order — اعتُمد الكود الحي) قابلة للعرض والتخصيص لكل متجر (تسمية/لون) و«ترتيب حالات التأكيد» سطرًا سطر (↑/↓ ⇒ `sort_order`).

**الأدلة المثبتة:**
- جدول `statuses` يدعم `store_id` nullable + unique `(store_scope_id, type, key)` + `store_scope_id` افتراضية؛ النموذج fillable يشمل store_id/type/key/label/color/is_system/affects_inventory/movement_type/icon/display_mode/sort_order (`app/Models/Status.php:15-27`).
- `StatusResolver::resolve()` يفضّل صف المتجر ثم صف النظام ثم kit (`app/Domains/Status/StatusResolver.php:21-44`) → **آلية الـ override جاهزة**.
- **لا يوجد أي كاتب لصفوف متجر اليوم** (لا `Status::create` في app/؛ رسالة CRUD حصرًا Filament SuperAdmin) — هذه المرحلة أول كاتب.
- سيدر النظام: `SystemStatusesSeeder` order block (أسطر 19-289) بتعليق Confirmation Pipeline (0-10).

**التعديلات المقترحة:**
1. قراءة القائمة: `Status::where('type','order')->where(fn $q => $q->whereNull('store_id')->orWhere('store_id',$storeId))->orderBy('sort_order')` مفلترة بمفاتيح قبل الإرسال (11).
2. **قاعدة الـ override:** عند أي تعديل/ترتيب لحالة نظامية → `updateOrCreate` صف متجر بنفس `(store_id, type='order', key)` يحمل القيم المعدّلة (يتوجّه الـ Resolver إليه تلقائيًا؛ لا نلمس صف النظام).
3. نقاط التحرير: تسمية (نص)، لون (من `general` kit)، تفعيل/تعطيل، وأسهم ترتيب ↑/↓ مع مؤشر موضع (sort_order).
4. تاب «الترتيب»: قائمة بترتيب `sort_order` + أزرار ↑/↓ (wire:click مباشر — بلا سحب لتبسيط الأداء).
5. خدمة صغيرة `app/Services/Stores/StoreStatusService.php` لضبط قواعد الـ override والكتابة (تُستخدم في المرحلتين 2 و4).

**الترجمة:** تسميات النظام تُترجم عبر status-kit (`systemLabel()` يعيد اشتقاقها) — لا دوم نصوص إنجليزية للصفوف النظامية؛ المخصصات لها `label` نصي في صف المتجر (أياً كانت لغته كما أدخلها التاجر).

**التحقق:** `StoreStatusCustomizationTest` (**10 ناجح / 32 تأكيدًا**): القائمة المُبذورة بالترتيب، إنشاء override عبر Save، الـ Resolver يفضّل صف المتجر، كحل الـ label الفارغ → ترجمة kit، عزل المتجر، التحرك أعلى/أسفل + إعادة التسلسل، حدود no-op، رفض المفاتيح/الألوان غير الصالحة، الحفظ عبر المكوّن (تسمية+لون معًا — **fix: `writeOverride` كانت تمحو label بالتحديث اللاحق**)، الترتيب عبر المكوّن. `StatusCustomizationPageTest` (م1) حُدِّث التأكيد لنمط المرحلة 2 الفعلي. **الجولة الكاملة: 791 ناجح (3100 تأكيدًا)** + `view:cache` نظيف + Pint. **ملاحظة أمان:** انتقالات الطلبيات تستخدم دائمًا `Status::system()` (OrderService/Observer) → الـ override يؤثر على العرض فقط، لا على `affects_inventory`/`movement_type`.

**الاعتماديات:** المرحلة 1. يدعم مباشرة فلتر الطلبيات بالمرحلة 6 (نفس القائمة الـ 10 عبر `StoreOrderPhases`).

### المرحلة 3 — حالات تتبع شركات التوصيل (API): جمع + مفاتيح ترجمة + بذر في `statuses` ✅

**الهدف:** قاموس موحّد لحالات كل شركة توصيل (`raw → key داخلي`) + مفاتيح ترجمة ×4 + بذرها في `statuses` (`type='tracking'`) بحيث لا تصلنا حالة بلا label داخل نظامنا.

**الأدلة المثبتة (من فحص التكامل):**
- التكامل الوحيد المُفعّل: **NOEST** — Webhook `DeliveryWebhookController` (accepted payload shapes: OrderInfo+activity/events/Flat) + Poll `SyncNoestTrackingJob`؛ كلاهما يتقاطعان في `NoestTrackingSyncService::apply()`. القيم RAW نحو 40+ `event_key` في `NoestTrackingMapper::MAP` + مطابقة نصية `eventTextToStatus` + `terminalKeys`؛ غير المحدد → fallback `IN_TRANSIT` (apply سطر 88-110).
- **Ecotrack** / **Yalidine**: وثائق في الريبو فقط (11 نشاطًا + 19 حالة / 30 سلسلة `last_status`).
- **ZR Express v2 / Anderson**: أسطر كتالوج فقط (`CarrierCatalogSeeder:40-49`)، **بلا توثيق** → إبقاؤها fallback مؤقتًا أو مصدر خارجي.
- الحالات الداخلية `type='tracking'` الحالية (9): shipped, in_transit, out_for_delivery, delivered, returned, returning, failed_attempt, lost, damaged (`SystemStatusesSeeder:466-521`)؛ ≥ `OrderTrackingStatus` (9 حالات، labels عبر status-kit ×4 لغات).
- `order_trackings` يحفظ `carrier_status/carrier_label/tracking_status/carrier_raw` + history عبر `order_tracking_histories`.

**التعديلات المقترحة:**
1. `app/Domains/Shipping/Support/CarrierStatusDictionary.php` (جديد): جدول لكل شركة `raw_status → internal_key` (قيم NOEST حرفيًا من MAP الوثائق، Ecotrack/Yalidine من وثائقهما) + `internal_key → label_key`.
2. `SystemStatusesSeeder` (قسم tracking): إضافة مفاتيح جديدة تنشأ من القاموس (لم تكن معرفة) ومواءمة label عبر مخفاتيح الترجمة بدل النص الإنجليزي (توحيد مع `systemLabel()`).
3. ترجمة ×4: مفاتيح كل قيمة مُصطادة في status-kit/lang + `order_flow.php`.
4. `NoestTrackingSyncService`/`NoestTrackingMapper`: استبدال fallback `IN_TRANSIT` بالقاموس (كل قيمة واردة لها key دائمة) + الإبقاء على `carrier_raw` للعرض الأصلي.
5. تاب `carrier_tracking` في صفحة الحالات (من م1): جدول `raw ⇦ القيمة المُطبَّقة ⇦ التسمية المترجمة` للشركة المختارة (قراءة من القاموس + statuses).

**التحقق:** تحديث/إضافة اختبارات `NoestTrackingSyncService*`: كل قيمة من القاموس → key محدد (لا fallback)؛ `view:cache`؛ جولة التتبع المرجعية صفر انحدار.

**الاعتماديات:** المرحلة 1. مرتبط بالمرحلة 5: إن ظهرت مفاتيح تتجاوز الخمسة الثابتة في الخط الزمني (shipped/in_transit/out_for_delivery/delivered/returned) نمدّد الخط الزمني/الـ stepper هناك.

**✅ منجز (2026-09-16).** **نطاق التنفيذ وكل قراراته المعتمدة كما وُثّقت خلال الجلسة:**
- **`app/Domains/Shipping/Support/CarrierStatusDictionary.php` (جديد):** قاموس موحّد `raw → OrderTrackingStatus` لكل شركة — **NOEST** (أحداث MAP الحالية + مفاتيح الوثيقة الجديدة: `colis_suspendu`/`colis_pickup_transmit_to_partner`/`echange_valide`/`echange_valid_by_hub`/`verssement_admin_cust`/`validation_reception_cash_by_partener` + الملغاة `*_canceled` + `ask_to_delete_*`)؛ **Ecotrack** (11 activity + 19 status حرفيًا — `annule`→cancelled، `all` مستبعد كفلتر)؛ **Yalidine** (36 history statuses حرفيًا بفرنسيتها، `Bloqué`/`En alerte`→on_hold، `Colis abandonné`→lost، `Annulé`→cancelled).
  - API: `carriers()`, `carrierOptions()` (أسماء موطَّنة ×4), `statusFor(carrier, raw)`, `list(carrier)`, `keysFor(carrier, status)`. **تُستبعد الأحداث الإدارية/المالية** (`edited_informations`, `edit_price`, `edit_wilaya`, `extra_fee`) → null (تُبقي الحالة السابقة؛ يطابق اختبار unmapped القائم).
- **`OrderTrackingStatus` توسّع 9 → 11:** `ON_HOLD = 'on_hold'` (open) و`CANCELLED = 'cancelled'` (terminal) — labels ×4 (en/ar/fr/es) + إدخالا **`config/status-kit-statuses.php`** في مجموعة `tracking` بأيقونتين مسجلتين (`on_hold`, `cancelled`) + تحديث `fromCarrier` (hold/blocked/suspended → ON_HOLD؛ cancel/annul → CANCELLED؛ abandoned → LOST).
- **`SystemStatusesSeeder` (قسم tracking):** الصفان الجديدان `on_hold` (sort 4) و`cancelled` (sort 9)، بإعادة تسلسل delivered→5 .. failed_attempt→8 .. damaged→11.
- **`NoestTrackingMapper`**: أُفرغ من `MAP` ويتفرّد للقاموس (`toStatus`/`terminalKeys` عبر `CarrierStatusDictionary`) مع الإبقاء على `eventTextToStatus`. **`NoestTrackingSyncService::apply()`**: حُذف fallback `IN_TRANSIT` — الحدث غير المعيّن (حتى على صف فارغ) يبقى بلا حالة ولا History مع تحديث `last_synced_at` فقط + `eventDate` للـ delivered/returned تتحد الفرعي من `terminalKeys` + status value.
- **تاب `carrier_tracking` في صفحة الحالات:** `x-edz.select` بشركة (noest/ecotrack/yalidine، `wire:model="carrier"`) + جدول `raw (mono) ⇦ القيمة المُطبَّقة (badge/classes) ⇦ التسمية المترجمة` عبر `StatusResolver::resolve('tracking', key, storeId)` (row جديد `$carrierRows`). مفاتيح `merchant_panel` ×4 الجديدة: `carrier_tracking_select/hint/raw/applied/label` + `carrier_noest/ecotrack/yalidine` (الفرنسية: Libellé/Statut brut/…).
**التحقق:** `CarrierStatusDictionaryTest` (جديد — 7 اختبارات: الشركات الـ 3 + NOEST + on_hold/cancelled + Events إدارية null + Ecotrack + Yalidine + lists/keysFor) + `OrderTrackingStatusTest` (11 حالة + تصنيفات + matcher + labels ×2 + icons) + `NoestTrackingSyncServiceTest` (اختبار fallback المُستبدل: صف فارغ بلا حالة بلا History + اختبار dict جديد `colis_suspendu`→on_hold و`ask_to_delete_by_admin`→cancelled) + `StatusCustomizationPageTest` (جديد: Livewire tab test — `setTab('carrier_tracking')` + تبديل `carrier` إلى `yalidine`) + `StatusLabelPrecedenceTest` (9→11 + on_hold/cancelled). **الجولة الكاملة: 800 ناجح (3222 تأكيدًا)** (`php -l` نظيف ×5 + Pint 10 ملفات + `view:cache` ناجح). **ملاحظة قرار:** تركت تسميات صفوف البذرة على نمط التسمية الإنجليزية الرائج لبقية صفوف السيدر (override `label=''` كي تُترجم من status-kit) — لا تغيير على `systemLabel()` في هذه المرحلة.

### المرحلة 4 — حالات تتبع راجل التوصيل: تخصيص + تاب الترتيب ✅

**الهدف:** تاب «تخصيص حالات تتبع الراجل» (إضافة/تعديل تسمية/لون/تفعيل) + «ترتيب حالات تتبع الراجل» (↑/↓ ⇒ sort_order) — حالاتنا المحلية لا تُستقبل من أي API.

**الأدلة المثبتة:** حالة الراجل محلية بالكامل — `OrderTrackingService::startShipment` يبذر `tracking_status='shipped'` و`mark*()` يكتب delivered/returned/in_transit... (`app/Domains/.../OrderTrackingService.php:17-44, 86-152`)؛ رقم تتبع محلي `HM-…/SD-…` (`generateRiderTrackingNumber:52-61`)؛ لا `shipping_provider_id` على مسار الراجل.

**التعديلات المقترحة:**
1. في صفحة الحالات (م1) تاب `rider_tracking`: نفس نمط المرحلة 2 بنطاق `type='tracking'` للراجل، مع إمكانية إضافة حالات جديدة يحددها المتجر (صفوف متجر بـ store_scope_id).
2. تاب «الترتيب» للراجل: نفس آلية ↑/↓ عبر `StoreStatusService`؛ لا يتداخل مع صفوف الشركة (فلتر بنطاق الراجل).
3. ربط التشغيل: حالات الراجل المخصصة تظهر في قائمة/stepper/إحصاءات الراجل فقط من خلال قراءة `OrderTracking::tracking_status` ذكية (status-kit + صفوف المتجر) — **بلا تعديل على `mark*`** (حالات التبديل الأساسية تبقى enum).

**التحقق:** إضافة حالة راجل مخصصة لمتجر → تظهر في قائمة/ترتيب/stepper الراجل فقط ولا تسرّب للشركة؛ Feature test + `view:cache`.

**الاعتماديات:** المراحل 1 و2 (نفس آلية override).

**✅ منجز (2026-09-17) — بقرارات المستخدم خلال الجلسة** («التفاصيل → المرحلة 4 فقط»؛ الترجمة «المصدران معًا»؛ عمود «المعنى الحقيقي» في تبويب الشركة؛ حالات تأكيد مخصصة مرتبطة بحالة أصلية = فرع وظيفي). ما نُفّذ:
- **`Status` (+ هجرة جديدة `2026_09_17_000000_add_linked_to_to_statuses_table.php`):** عمود `linked_to` nullable (مفتاح الحالة الأصلية لصفوف المتجر المركّبة/المخصصة).
- **`StoreStatusService`:** إعادة كتابة بالكامل — `TRACKING_TYPE`، `riderList()/riderSave*/moveRider()/addStatus()/deleteStatus()`، `canonicalKey()`، قواعد المرحلة 2ْ للـ override + فرع `linked_to`؛ كتابة صفوف متجر `(store_scope_id, type, key)`؛ `scopeType` للحالة غير النظامية (rider/custom order).
- **صفحة `statuses.blade.php`:** إعادة كتابة — تابات 3 (confirmation/carrier_tracking/rider_tracking) + «ترتيب»؛ دارة `$tab`; عرض/ترتيب ↑/↓؛ إضافة/حذف حالة راجل باسم ولون؛ إضافة حالة تأكيد مخصصة (label/color) باختيار حالة أصلية `linked_to` تُعرض شارة الربط.
- **مفاتيح المفاتيح المخصصة:**
  - `OrderService::transition()`: المفتاح النظامي → الصف النظامي (الـ override للعرض فقط محفوظ)؛ مفتاح خاص بالمتجر فقط → صف المتجر عبر `firstOrFail` (لا `ModelNotFoundException` لمفاتيح الفروع).
  - `OrderService::availableTransitions()`: إن كانت حالة الطلبية الحالية صف متجر ذا `linked_to` → تُؤخذ انتقالات الأصل (الفرع يرث مخارج أصله — ليس طريقًا مسدودًا).
  - `OrderService::canTransition()`: يقبل المفتاح المخصص إن كان `linked_to` سمح له النّتقال (أصله هدف مسموح).
  - `OrderObserver::handleStatusChange`: يقرأ `movement_type`/`affects_inventory` من الصف الحي (نظامي أو متجر) → احتياط المخزون يُطبق لمرة واحدة عند دخول فرع مخصص.
- **`OrderWorkflow::carrierStatusIds()`** (introduced بالمرحلة المبكرة) + `StoreOrderPermissions::forStatus($key, $storeId)`.
- **الترجمة «المصدران معًا»:** مفاتيح status-kit ×4 تعكس معنى NOEST الحقيقي + تسميات عربية تُبذر في `SystemStatusesSeeder` (label عربي) و`CarrierStatusDictionary` (`RAW_TO_STATUS` + `RAW_TO_MEANING`: 35 noest + 30 ecotrack + 36 yalidine، و`list()` بمفتاح `meaning`).
- **تبويب الشركة — عمود «المعنى الحقيقي»:** `RAW_TO_MEANING` يُعرض ثالثًا بجانب raw/الحالة المُطبَّقة؛ أحداث NOEST الإدارية الخمسة بلا مفتاح داخلي لا تُعرض.
- **إصلاح bug سابق الوجود للوصول لسويت أخضر:** `OrderConfirmationService` (`use App\Domains\Order\Support\OrderCompleteness` — كلاس غير موجود) → حُذف الاستيراد (الكلاس في نفس النيم سبيس `App\Domains\Order\Services\OrderCompleteness`)؛ كان يكسر حاو الحل (BindingResolutionException) في `submitConfirmAndSend`/`sendConfirmedOrder` وبقية مسار الإرسال. يلمس نجاحه: `SendGatewayCarrierAtomicTest` + `SendCarrierFailureTest`.

**التحقق (الأدلة):**
- `StoreStatusCustomizationTest` + `OrderTrackingStatusTest`: **22 ناجح (119 تأكيدًا)** (30.21s).
- `StatusCustomizationPageTest`: **7 ناجح (43 تأكيدًا)** — تبويب الشركة (عمود المعنى العربي عبر `RAW_TO_MEANING`)، تبويب الراجل (عرض قائمتين «تخصيص» و«ترتيب» + زر إضافة + حذف حالة مخصصة عبر المكوّن)، تاب التأكيد (إضافة حالة مخصصة مرتبطة + شارة الربط).
- `CustomStatusBranchTest` (جديد): **4 ناجح (18 تأكيدًا)** — ظهور مفاتيح المتجر لدى الـ Resolver؛ الفرع الوظيفي (canTransition→true، انتقال إلى صف المتجر، reserve 1× عند 10→8، تاريخ الحالة بالصف المخصص، والخروج عبر 'preparing' بلا حركة إضافية)؛ حالة الجلوس على فرع ترث انتقالات الأصل (contains 'preparing'/'pending'/'cancelled')؛ انتقال بمفتاح مجهول → `ModelNotFoundException`.
- **السويت الكاملة: 829 ناجح (3342 تأكيدًا)** (685.58s) + `php -l` نظيف على `OrderService` و`OrderConfirmationService` + `view:cache` سليم. قاموس العزل: فشلا `SendGatewayCarrierAtomicTest`/`SendCarrierFailureTest` كانا بسبب استيراد `OrderCompleteness` الخاطئ المُسجَّل أعلاه (لا علاقة بتعديلات `OrderService`) — أثبته توقف الاختبارين (8/8) بعد إصلاح الاستيراد وحده.

### المرحلة 5 — بوب أب التتبع: تابان فرعيان «تتبع الطلبية» / «ملاحظات شركة التوصيل» ⬜

**الهدف:** داخل `tracking-history-popup` مبدّل تبويب فرعي:
- «تتبع الطلبية»: الخط الزمني (stepper) + سجل `statusHistory`.
- «ملاحظات شركة التوصيل»: قائمة ملاحظات + كاتب ملاحظة.
يعمل تلقائيًا على كلا القسمين (كوم्प أي/راجل) — الشركة عبر API notes، الراجل عبر آلية ملاحظات محلية جديدة.

**الأدلة المثبتة:**
- البوب أب الحالي يكدّس stepper + رابط التتبع + composer + timeline عموديًا (`tracking-history-popup.blade.php:26-42`) بلا تابات.
- الحالة: `statusHistoryFor/statusHistory/statusHistoryMeta` + `shipmentNotes*` + `noteDraft/sendingNote` (index.blade.php:68-80) والطرق `openStatusHistory/openShipmentNotes/sendCarrierNote` (`TrackingDrawerConcern.php:139-348`). البوب أب مشترك على البطينين (index.blade.php:348).
- ملاحظات الراجل **لا وجود لها اليوم**: composer محجوب بـ `carrier_supports_api_notes` (false للراجل؛ `carrier-note-composer.blade.php:3-7`) و`sendCarrierNote` يحرس بـ supports_api_notes + non-null tracking_number (`TrackingDrawerConcern.php:279-301`). خلية الملاحظات في الجدول عمودًا `—` للراجل (`tracking-row-cell.blade.php:42-56`).
- مفاتيح الترجمة الحالية: `carrier_note_section/carrier_note_status/carrier_notes/no_carrier_notes` (`order_flow.php:144-151`)؛ لا يوجد مفتاح حرفي «تتبع الطلبية»/«ملاحظات شركة التوصيل» — جديدة.

**التعديلات المقترحة:**
1. `tracking-history-popup.blade.php`: حالة `statusSubTab` ('tracking'|'notes') بتباين فئات pills (نمط `tracking-tabs.blade.php:24-37`) بلا توجيه/حفظ localStorage.
   - تاب tracking: stepper + `tracking-history-timeline` (كالحالي).
   - تاب notes: قائمة `statusHistory` المُفلترة بـ `carrier_note` (صفر استعلام إضافي) + composer.
2. ملاحظات الراجل (آلية جديدة): السماح بكتابة `carrier_note` محليًا لصفوف الراجل — تحديث الـ gate في `carrier-note-composer` (شركة → supports_api_notes؛ راجل → يُسمح محليًا) + `sendCarrierNote` يكتب صف حالة مباشرةً عند عدم وجود provider-API (لا استدعاء adapter). خلية الجدول للملاحظات تظهر للراجل.
3. الترجمة ×4: `tracking_popup_tab_tracking`، `tracking_popup_tab_notes` (+ أسماء هامشية إن لزم).

**التحقق:** تبديل التابين بلا استعلام إضافي (نفس الصفائف)؛ بعد إرسال ملاحظة في أي تاب يتحدّث سجل `statusHistory` وسلوك الراجل بلا API؛ `view:cache`؛ جولة التتبع المرجعية صفر انحدار.

**الاعتماديات:** المرحلة 3 (أي مفاتيح تتبع جديدة توسّع القائمة/الخط الزمني).

### المرحلة 6 — المتفق عليه سابقًا: فلتر حالات الطلبيات (قبل الإرسال) + فصل أدوار STAFF + بوابة التتبع ⬜

**الهدف (قرارات المستخدم المسبقة):** فلتر حالة الطلبيات يعرض حالات التأكيد فقط (عام لكل الأدوار؛ `preparing` = بعد الإرسال)؛ فصل أدوار STAFF (مؤكِّد/متتبِّع) مع إبقاء STAFF القديم للتوافق؛ بوابة التتبع `ORDER_VIEW || CRM_ORDER_TRACKING`؛ المنح المخصص لكل موظف يبقى عبر `store_membership_permissions`.

**الأدلة المثبتة:**
- `searchableStatuses` يُبنى من كل `type='order'` (`orders/index.blade.php:504-513`) ويزوّد البوابتين (`filter-portal:112`, `orders-filter-bar-portal:363`).
- قائمة تغيير الحالة داخل الصف مقيدة سلفًا بـ `$transitions` (`orders-table-cell:811` عبر `StoreOrderPermissions::forStatus` عند 1732) — لا تُمَسّ.
- `StoreRoles::permissions()`: STAFF الحالي يجمع التأكيد والتتبع (`StoreRoles.php:99-123`)؛ `CRM_ORDER_TRACKING` غير مستخدم كبوابة UI (يظهر فقط في StoreRoles و`dashboard.blade.php:16`).
- السايد بار يُظهر التتبع بنفس علم الطلبيات (`store-sidebar.blade.php:58/260-267`)؛ بوابة صفحة التتبع `ORDER_VIEW` (`tracking/index.blade.php:222`).
- `StoreRoleEnum` (owner/admin/manager/staff) + واجهة الفريق تعرض قوائم الأدوار ديناميكيًا (`teams/index.blade.php:299`) + شارة الدور `x-merchant.status domain="role"` (السطر 427) — إضافة الدورين تعرضان تلقائيًا مع مفاتيح labels في status-kit/lang.

**التعديلات المقترحة:**
1. `app/Support/StoreOrderPhases.php` (جديد): `PRE_CARRIER` (11) / `POST_CARRIER` (شاملاً preparing + المتبقية) + `isPreCarrier(string $key)` — مصدر وحيد للفلتر (يُمطّق مع المرحلة 2).
2. `orders/index.blade.php:504-513`: `searchableStatuses` ← الإبقاء على `allStatuses` كاملًا للـ override/التسميات لكن فلترة القائمة المفلترة بـ `isPreCarrier`؛ البوابتان تستهلكانه تلقائيًا؛ بلا مساس بالاستعلام و`$transitions`.
3. `StoreRoleEnum`: + `STAFF_CONFIRMATION = 'staff_confirmation'` + `STAFF_TRACKING = 'staff_tracking'` (إبقاء `STAFF` القديم كقالب توافق).
4. `StoreRoles::permissions()`:
   - `staff_confirmation`: ORDER_VIEW + ORDER_CONFIRM + ORDER_CANCEL + CRM_ORDER_CONFIRMATION + STATS_CONFIRMATION + PRODUCT_VIEW + INVENTORY_VIEW + RETURNS_VERIFY_BARCODE.
   - `staff_tracking`: ORDER_VIEW + CRM_ORDER_TRACKING + STATS_DELIVERY + INVENTORY_VIEW.
5. `store-sidebar.blade.php:58/260-267`: بوابة ربط التتبع = `canStore(ORDER_VIEW) || canStore(CRM_ORDER_TRACKING)`.
6. `tracking/index.blade.php:222`: البقاء على ORDER_VIEW (شمول) — لا تغيير إلزامي إلّا إن رُبطت بالدور تحديدًا.
7. تسميات/ألوان الدورين في config status-kit (`role` group) + ترجمات `roles.*` ×4 — تعرضان تلقائيًا في منسق الفريق والشارة.

**التحقق:** `view:cache` + pest (الملفات المتأثرة + الجولات المرجعية: orders/tracking/teams) + إثبات: فلتر الحالة = 11 حالة فقط، وبدون تسريب حالة توصيل؛ بوابة التتبع للـ tracker فقط؛ الـ staff القديم يبقى شاملاً؛ استجابة (الدوران + 375/768/1440) + تحديث Todos.md (ج8).

**الاعتماديات:** المرحلة 2 (مطابقة القائمة الـ 11 عبر `StoreOrderPhases`).

---
**ملاحظات عامة للعنقود:**
- بعد موافقة المستخدم على مرحلة تُنفَّذ بالترتيب فقط (لا دمج ولا قفز).
- كل مرحلة تُحدَّث ✏️ هنا فور الانتهاء بـ (✅) + أدلة التحقق + `php -l`/`view:cache`/الاختبارات ـ كما في الأقسام السابقة.
- قراران مفتوحان ينتظران المستخدم: (أ) شكل المسار (العادي أو المسارات العميقة القابلة للاشتراك) في المرحلة 1 — **تم البت: مسار واحد `customization/statuses` (أنظف)**؛ (ب) معالجة ZR Express/Anderson بلا وثائق في المرحلة 3 (fallback مؤقت أو مصدر خارجي).

---

## إصلاح سريع — مزامنة حالات NOEST (404 «Trackings non trouvés») ✅ (2026-09-16)

**العرض:** في تبويب شركات الشحن، «مزامنة الحالات» تنتج متكررًا `local.ERROR NOEST trackings/info request failed (HTTP 404): Trackings non trouvés` وتترك الصفوف عالقة بحالة `shipped` إلى الأبد. سببه الفعلي: صفّان مفتوحان (`YESH-28B-20317534`, `YESH-28B-20325652`) رقماهما لم يعودا موجودين لدى حساب NOEST، و`trackingsInfo` كان يرمي `RuntimeException` على أي HTTP failure — بما فيه 404 الدال على «الرقم غير معروف» رغم أن العقد الموثّق داخل الكود نفسه (`NoestIntegrationAdapter.php:261-265`) ينص على أن الرقم المجهول يُغفل من الخريطة (404 لا يظهر إلا عند فشل الكل).

**التعديلات المنفّذة:**
1. `NoestIntegrationAdapter::trackingsInfo` — عند HTTP 404 يعيد `[]` (غير معروف = غائب) بدل رمي استثناء؛ بقية أخطاء HTTP تبقى رميةً. لا ERROR spam بعد الآن؛ `syncOne` ترجع `no_data` بصمت، والمهمة تجاوزها بصمت (مسار `touchSyncedAt`).
2. `TrackingDrawerConcern::syncAllTracking` — صار يجمّع حسب `shipping_provider_id` ويرسل دفعات من 20 (طلب واحد بدل طلب لكل صف)، ويحسب `done/failed/missing`؛ وصّال توست مخصص «رقم التتبع غير موجود لدى شركة الشحن» عندما تكون كل الإخفاقات أرقامًا مجهولة.
3. `syncTracking` (تحديث الآن في الدرج) — يميّز `no_data` برسالة مخصصة بدل «تعذّر التحديث» العام.
4. ترجمة ×4: `order_flow.tracking_unknown_carrier`.
5. اختباران جديدان: (أ) `trackingsInfo` يرد على 404 بـ `[]`؛ (ب) `syncOne` يرجع `no_data` بلا لمس الصف.

**الشهادة:** pest `NoestTrackingSyncTest` + `NoestTrackingSyncServiceTest` + `TrackingTrashWebhookLabelTest` = 31 pass (131 assertions). `php -l` نظيف.

**متبقٍ (قرار داتا للمستخدم، خارج الكود):** مصير الصفّين `YESH-28B-20317534` / `YESH-28B-20325652` — إعادة تحقّق من التوكين/الحساب (احتمال إعادة ربط)، أو إغلاقهما يدويًا، أو تركهما (سيستمران بالفشل الصامت). كل مزوّدي `noest` (صفّان نشطان باسمي «Noest»/«NOEST») لديهما `webhook_token` — لذا المزامنة هنا يدوية فقط عبر التبويب، والـ Poll المجدول يجتازهما أصلاً (`webhook_token != null`).

**إضافة (2026-09-16، نفس الجلسة):** مع رسالة «غير موجود لدى الشركة» تُعرض الآن **أرقام الطلبيات المتأثرة** ليتعامل معها التاجر:
- `syncAllTracking` يجمع الصفوف المجهولة ويبثّ `html` في الـ `swal:toast` بقائمة «طلب #:order — التتبع: :tracking» (كل قيمة معزولة بـ `<bdi>` ومهرّبة بـ `e()`)، وتظهر عند حصرية المجهول أو في الخلط (بجانب العداد)؛ حد 25 سطرًا ثم «بالإضافة إلى :count طلبية أخرى».
- `syncTracking` (تحديث الآن) يعرض نفس القائمة للطلب الواحد.
- الترجمة ×4: `tracking_unknown_item` + `tracking_unknown_more`.
- اختباران جديدان في `TrackingTrashWebhookLabelTest`: لا يعودا يتمرر إلا بوجود رقم الطلبية + رقم التتبع في `html`.
- شهادة: 25 pass (107 assertions) في ملفّي `TrackingTrashWebhookLabelTest` + `NoestTrackingSyncServiceTest`؛ `php -l` نظيف.

## إصلاح سريع — «إلغاء الإرسال» لشحنة شركة توصيل لا تعرفها (422 «The given data was invalid.») ✅ (2026-09-16)

**العرض:** الطلبيات السليمة لدينا لكن رقمها غير موجود لدى شركة التوصيل (لم تُنشأ هناك، أو حُذفت خارجيًا) ترفض الإلغاء عند الشركة برسالة Laravel الخام من NOEST «The given data was invalid.»، فتعلق عملية الإلغاء المحلي نهائيًا.

**الحل المعتمد (المسايرة بمنطق ثقة):**
1. **عمود جديد `order_trackings.carrier_unknown_at`** (migration `2026_09_16_000001…`): يخزّن حقيقة «الشركة لا تعرف الرقم» المثبتة من المزامنة (لا تخمينًا من نص الخطأ):
   - `NoestTrackingSyncService::syncOne` يضبطه عند `no_data`؛ `apply()` يسحبه عند أي نجاح.
   - `SyncNoestTrackingJob` يضبطه للصفوف الغائبة من خريطة NOEST.
2. **`NoestIntegrationAdapter::deleteOrder`** يصدّر `not_found` (مطابقة نصوص معروفة: «The given data was invalid.»/introuvable/not found/… + فحص `errors`) مقابل بقية الأخطاء (شبكة/اعتماد) بلا تصنيف.
3. **`OrderShippingGateway::cancel`**: إذا كان `carrier_unknown_at` مضبوطًا **أو** أبلغ الأدابتور `not_found` → لا شيء للحذف لدى الشركة: يُكمل الإلغاء المحلي (فصل رقم التتبع + العودة إلى confirmed) مع سطر تاريخ و`payload.carrier_not_found_at_cancel=true` وLOG، ويعيد `notice=carrier_unknown`. أي فشل آخر يبقى حاجزًا (لا إلغاء محلي زائف لشحنة قد تكون حيّة لدى الشركة).
4. توست مميز `shipment_cancelled_unknown_carrier` ×4 في `TrackingDrawerConcern` و`CancelsShipmentFromOrdersTable`.

**الشهادة:** ملف اختبار جديد `tests/Feature/Shipping/ShipmentCancelCarrierUnknownTest.php` (3 اختبارات: مُعلَّمة/إبلاغ not_found → مضي محلي؛ فشل عام → حجب) + اختبارا المزامنة (stamp/clear في الاتجاهين، Job) + اختبارا `deleteOrder`. pest: 24 pass (101) في ملفات الشحن الثلاثة + عدم انحدار: `TrackingTrashWebhookLabelTest` (16)، `OrderCarrierValidationDispatchTest`+`SendGatewayCarrierAtomicTest` (16). migrate + `php -l` + `view:cache` سالمة.

---

## إصلاح فراغ صفحة التتبع — الفصل المنطقي بين حالات «الطلبية» و«التتبع» (عدم التكرار) ✅ (2026-09-17)

**العرض:** تبويبا صفحة التتبع (`merchant.tracking.index`) لا يعرضان أي صفوف إطلاقًا. السبب الجذري (بتشخيص حي): الكوميت `8e121b3` غيّر في `TrackingGridConcern.php:179` فلترة `orders.status_id` من `Status::system()->forType('order')` إلى `forType('tracking')` عند مفاتيح `OrderWorkflow::carrier()`.

**الخلط التصنيفي (حلّه القرار المعماري):** لكل مفتاح مثل `shipped` صفّان مختلفا ULID: واحد `type='order'` (حالة الطلبية) وآخر `type='tracking'` (حالة الشحنة لدى الشركة). `orders.status_id` يشير دائمًا لنوع `order` — فلترة بنوع `tracking` لا تطابق أي طلب → صفر صف في التبويبين. القرار المعتمد: **الفصل المنطقي الصحيح مع عدم التكرار** — التجارب الثلاثة لم تعد مصادر مكرّرة يدوية بل:
- **`orders.status_id` (نوع order)** = حالة الطلبية، ومنها تمرّ تسليمات الشركة والراجل معًا (`OrderShippingGateway` ينتقل إلى `shipped` عبر `OrderService::transition` الذي يقرأ `forType('order')` دائمًا) → هي التي تقرر ظهور الطلبية في صفحة التتبع.
- **`order_trackings.tracking_status` (نوع tracking)** = حالة الشحنة لدى الشركة (أوسع: on_hold/returning/failed_attempt/lost/damaged…).
- **التأكيد (نوع order، قائمة `StoreOrderPhases`/Confirmation Pipeline)** = مرحلة ما قبل الإرسال.

**ما نُفِّذ:**
1. `database/seeders/SystemStatusesSeeder.php` — حُذفت كتلة TRACKING اليدوية (11 صفًا) وأصبحت تُبنى من `OrderTrackingStatus::cases()` (وحيد المصدر) مع **فحصَي اتساق يرمي `RuntimeException`**: (أ) مفاتيح tracking المزروعة == حالات enum؛ (ب) مفاتيح كل مجموعة `OrderWorkflow::backOffice()/carrier()/closed()` ⊆ مفاتيح صفوف type=order.
2. `app/Domains/Order/Support/OrderWorkflow.php` — **نقطة دخول موحّدة جديدة `carrierStatusIds()`**: «مفاتيح `carrier()` → معرّفات `type='order'`». هي الوحيدة المسموحة لفلترة `orders.status_id` في صفحة التتبع.
3. `app/Livewire/Concerns/TrackingGridConcern.php:179` — يستدعي `OrderWorkflow::carrierStatusIds()` بدل الاستعلام المضمّن، مع تعليق توثيق الفصل الثلاثي.
4. `tests/Feature/Merchant/TrackingGridStatusScopeTest.php` (جديد) — 3 اختبارات: ظهور طلب شركة في تبويب carrier؛ ظهور طلب موصِّل في تبويب rider؛ «التركيز»: `carrierStatusIds()` لا تتقاطع أبدًا مع معرّفات نوع tracking ولا يطابقها أي `orders.status_id`.

**الأدلة/الشهادة:** بذر حي ناجح بلا استثناء (صفوف tracking الـ 11 مطابقة لـ enum label/color/ترتيب)؛ pest: **52/52** في مجموعة التتبع (`TrackingGridStatusScopeTest` 3 + `TrackingSearchFilterTest` 33 — كانت 18 فاشلًا + `TrackingTrashWebhookLabelTest` 16 — كان 1 فاشلًا) و**24/24** في سويتات الشحن (`ShipmentCancelCarrierUnknownTest` + `NoestTrackingSyncTest` + `NoestTrackingSyncServiceTest`) — صفر انحدار. `php -l` نظيف ×4.

> القاعدة لمن يعدّل لاحقًا: لا يُستعمل `forType('tracking')` لفلترة `orders.status_id` إطلاقًا؛ مرّر بفلاتر صفحة التتبع عبر `OrderWorkflow::carrierStatusIds()` فقط.
>
> مرتبط بالعنقود أعلاه: هذه الحالات (11) تخدم المرحلة 4 (راجل/متجر، unittest-المرحلة 2) والمرحلة 6 (فلتر التأكيد) — نقطة الدخول الموحّدة تبقى مصدر الحقيقة ولا ينبغي تكرار قوائم المفاتيح الثابتة في أي صفحة جديدة.

---

## إصلاح ثلاثي — store_scope_id (SQLSTATE 1265) + حالة تتبع الراجل الفارغة + «لا توجد شحنات قابلة للمزامنة» ✅ (2026-09-17)

**العرض (3 تقارير مترابطة):** (1) `SQLSTATE[01000] … Data truncated for column 'store_scope_id'` عند إضافة/تخصيص/تعديل حالة؛ (2) عمود «الحالة» في جدول تتبع الطلبيات (تبويبا الشركة والراجل) فارغ؛ (3) «مزامنة الكل» تقول «لا توجد شحنات قابلة للمزامنة مع شركات التوصيل» رغم وجود شحنة بمُعرِّف تتبع فعلي. تم التشخيص حيًّا على قاعدة بيانات المتجر الحالية (وبموافقة المستخدم الصريحة «نفّذ هجرة الإصلاح»/«افحص وأصلح»).

**الأسباب الجذرية (ثلاثة مستقلة):**
1. **`store_scope_id` عصريًا خاطئ:** العمود مولّد `unsignedBigInteger` بالصيغة `IFNULL(store_id, 0)` بينما `store_id` هو ULID (char 26). MySQL يقطّع أي ULID عند التحويل العددي (أثبت حيًّا: `CAST(IFNULL('01m2qnh10v7w009ffhz1h7qbtn',0) AS UNSIGNED)` = `1`) → كل المتاجر تتصادم في scope=1، ومع كسر strict يكتب `1` (truncated) أو يرفض الصف (1265).
2. **`OrderTracking::tracking_status` فارغة على مسار الراجل:** `ensureRiderTracking` كان يملأ `tracking_number` فقط على صف مفتوح موجود ولا يضبط الحالة أبدًا → الشبكة تعرض «—». (بيانات حية: طلب 00002 راجل رتبة تتبع `tracking_status=NULL, carrier_status='cancelled'` ومزوّد قديم لم يُسحب.)
3. **`OrderTrackingStatus::open()` تنقص `failed_attempt`:** `syncAllTracking` يفلتر `whereIn('tracking_status', open())` وهذه الحالة (غير نهائية — `isOpen()` تُرجع true لها) كانت مستثناة → الشحنة الوحيدة بمُعرِّف تتبع فعلي (`YESH-28B-20573449`, `failed_attempt`) مستبعدة من المزامنة دائمًا.

**ما نُفّذ:**
1. **هجرة جديدة `2026_09_17_163636_fix_statuses_store_scope_id_ulid_scope.php`:** `store_scope_id` يُعاد إنشاؤه كـ `string(26)` مولّد = `IFNULL(store_id, '0')` (سنتينال: `'0'` للنظام، ULID للمتجر) مع إعادة بناء `unique(statuses_scope_type_key_unique)`؛ `down()` يعكس (`unsignedBigInteger`). أُصلح كذلك مرجعا Filament SuperAdmin: `StatusForm::store_scope_id` بلا `->numeric()` (عمود مولّد يُقرأ فقط → `->disabled()`)، `StatusesTable::store_scope_id` بلا `->numeric()->sortable()` فقط.
2. **`OrderTrackingService::ensureRiderTracking`:** على صف مفتوح موجود — يملأ `tracking_number`، ويلفّ `tracking_status='shipped'` إن كانت فارغة (مع سطر History `rider_backfill`)، ويمسح `shipping_provider_id` القديم (مسار الراجل لا يُبثّ أبدًا؛ إبقاؤه كان سيُدخل رقم HM/SD المحلي في مزامنة الشركة). Idempotent.
3. **`OrderTrackingStatus::open()`:** أُضيف `FAILED_ATTEMPT` (القائمة صارت مطابقة تمامًا للحالات غير النهائية — اختبار يثبت التطابق لكل حالة). التعبير `::open()` لا يُستخدم إلا في `TrackingDrawerConcern::syncAllTracking:272` فلذلك لا أثر جانبي.

**الشهادة (على قاعدة بيانات حية لمتجر حقيقي):**
- `php artisan migrate` (forward + rollback/re-migrate) نظيف؛ `SHOW CREATE TABLE statuses` يظهر `varchar(26) GENERATED ALWAYS AS (ifnull(store_id,'0'))`.
- `StoreStatusService::addStatus` + `riderSaveLabel` (override بلا مساس بترجمة kit) + `moveRider` + `riderList(count=12)` + `deleteStatus` على المتجر الحي بنجاح — لا `SQLSTATE[01000]` (كان يفشل قبل الهجرة).
- إصلاح حي للطلب 00002: `ensureRiderTracking` ملأ `SD-EE9LLDZN` + `shipped` ومسح المزوّد القديم؛ وحيد المصدر بعد تكرار الاستدعاء (لم يُلمس `SD-...`/`shipped`).
- مزامنة الكل: `open()` الحالية = `shipped,in_transit,out_for_delivery,on_hold,failed_attempt,returning` والاستعلام الحي يلتقط الشحنة `YESH-28B-20573449/failed_attempt` (كانت صفرًا قبله).
- pest: `OrderTrackingStatusTest` (6/6 — الجديد: تطابق open() مع isOpen لكل حالة) + `OrderCompletenessTest` (25/25 — الجديدان: backfill الحالة + إبقاء حالة موجودة + مسح المزوّد، مع الاتّساقية: لا استبدال رقم/حالة موجودة) + تتبع المراجعة (`OrderTrackingTest`, `TrackingGrid*`, `TrackingStatusHistoryPopupTest`, `NoestTrackingSyncService*`) 45/45 + **Shipping كامل 60/60 (280)** + **Merchant Order* 337/337 (1310)** + **Merchant Tracking* 117/117 (575)** + سويتات الحالات (`StatusLabelPrecedence`, `StatusResolverDomain`, `CustomStatusBranch`, `StatusCustomizationPage`, `StoreStatusCustomization`) 34/34. `php -l` نظيف ×8 + rollback/re-migrate نظيف.
- توثيق: `DATABASE_PLAN_Schema.md` و`DATABASE_PLAN_MIGRATIONS.md` حُدّثا للنوع النصّي الجديد مع سبب التغيير.

---

## إصلاح سلبي زائف «لا شحنات للمزامنة» — مزامنة الكل مع `tracking_status = NULL` ✅ (2026-09-17)

**مرجع المرحلة:** Phase "Fix No shipments to sync False Negative (Bulk Sync Filter)" — ضد commit `47166ac9`.

**التشخيص:** الشحنة الحقيقية `YESH-28B-20576482` غير موجودة في قاعدة التطوير المحلية (بيانات إنتاج فقط) — لا يمكن تقرير أي فرضية من الثلاث على البيانات الحقيقية هنا؛ النهج المطبَّق هو الفرضية 2-الأولى (صف بشركة ورقم صالحين وحالة NULL ممسوحة بمسار خارج `startShipment()` — مثل مسار الإلغاء ثم إعادة الإرسال). فحص النطاق: `OrderTrackingStatus::open()` لا يُستعمل إلا في `syncAllTracking()` (grep كامل في `app/`) — بلا أثر جانبي لتوسيع هذا الاستعلام وحده؛ بقي استعلام فلتر الشبكة في `TrackingGridConcern:107` (حساسية فلتر، لا صلة).

**ما نُفّذ (كان جزئيًا في الشجرة غير الملتزمة، أُكمل وأُثبت):**
1. **`app/Livewire/Concerns/TrackingDrawerConcern.php` (syncAllTracking):** الاستعلام أصبح `whereIn(tracking_status, open()) OR whereNull(tracking_status)` — نفس تعريف «synable» لـ `SyncNoestTrackingJob::dueTrackings()` كي لا يختلف السحب اليدوي أبدًا عن المهام المجدولة. لم يُلمس `OrderTrackingStatus::open()/terminal()` ولا فلتر `webhook_token` في المهمة (بالتصميم).
2. **العرض:** `tracking-row-cell.blade.php` + `tracking-mobile-card.blade.php` — عندما يكون `tracking_status` فارغًا وله رقم تتبع → شارة «لم تُزامَن بعد — الحالة غير معروفة» (`order_flow.tracking_status_unknown` ×4 لغات) بدل الخلية الفارغة.
3. **نقطة بمفردها:** Pint على الملفين (كانت عليهما مخالفتا أسلوب).
4. **اختبار الانحدار:** `tests/Feature/Shipping/SyncAllTrackingNullStatusTest.php` (ملف جديد، 2):
   - صف `tracking_status = NULL` + مزوّد صالح + رقم → يظهر في `syncAllTracking()` ويُحل إلى `DELIVERED` مع `delivered_at`/`last_synced_at`/History.
   - صفان (NULL + `in_transit`) يُسحبان معًا في تشغيل واحد — الرقم الحقيقي `YESH-28B-20576482` مستخدم حرفيًا في كلا الاختبارين (التحقق القياسي لمعيار القبول بدل الوصول إلى PPROD).

**الشهادة:** `SyncAllTrackingNullStatusTest` 2/2 (9 تأكيدات) + `TrackingTrashWebhookLabelTest` 22/22 (82 تأكيدًا) خضراء. السويت الكاملة 839 ناجح (3417 تأكيدًا) — الفشل الثلاثة **سابقة التأسيس وغير مرتبطة** بها: اثنان قفل ملفات Windows في `BulkDispatchValidateTest` (`rename storage/framework/views … Access is denied` — معروف ومؤرَّخ)، وواحد في `CarrierSyncObservabilityTest` (مشروع مراقبة غير ملتزم، `expectsOutputToContain('8')` لا يطابق جدول الـ artisan command). `php -l` نظيف ×4، Pint نظيف، الجولة الكاملة السابقة (4 أخطاء متقلّبة) تؤكد نفس النمط. التنبيه: لا توجد قاعدة بيانات إنتاج/Staging محلية لإثبات YESH-28B-20576482 بعينها — الإثبات القياسي عبر رقم التتبع الحرفي داخل الاختبار؛ إن أمكن الوصول إلى نسخة من الإنتاج، فالتأكيد النهائي استعلام السطر (1) في خطة المرحلة.

---

## إصلاح سلسلة الدفع (الزائر): hooks `updated` المعطوبة + تضمين قوائم البلديات/المكاتب بدل الجلب الكسول ✅ (2026-09-17)

**طلب المستخدم:** 4 أخطاء بالدفع: (1) توصيل للمنزل لا تُجلب البلديات ويوجد بطء؛ (2) ولاية ذات مكتب واحد ترفض الطلب بـ"اختر مكتب الاستلام"؛ (3) طلبات المكتب تدخل بلا البلدية؛ (4) لا يختبئ منتقي شركة التوصيل عند شركة واحدة.

### التشخيص المعتمد بالأدلة

- **الجذر (Bug 2 و 3):** توقيع `updated()` خاطئ في `storefront/order-form.blade.php`. الملف استخدم `updated(['state_id'], function(){…})` — وسيطاً ثانياً منفصلاً — بينما الصيغة الوحيدة المعتمدة في Volt ترابطية: `updated(['state_id' => function(){…}])`. بالصيغة الخاطئة يُخزَّن `CompileContext::$updated` بمفاتيح رقمية `[0..9]` بدل `['state_id' => …]`، و`CallPropertyHook::execute` يبحث بالمفتاح الاسمي `$context->updated[$propertyName] ?? fn()=>null` → **ردّ فارغ دائماً (لم يعمل أي من الـ 5 hooks قط)**. إثبات: `[DBG CTX] updated keys=[0..9] hook_state_id_present=false`؛ واستدعاء يدوي `$i->updated('state_id')` بلا أثر؛ واستبقا\ Todos.md:1049 اعترف ضمناً ("طفرة updated لا تظهر في assertSet") إذ عُوّض الاختبار بضبط `selectedStopdesk` صراحةً.
- **Bug 4** كان منفَّذاً أصلاً (`mount` + render: `availableProviders->count()===1` → `selectedProvider` مثبت + `role="company-select"` مخفي) — حدَّدته اختبار إعادة مباشرة.
- **Bug 1 (لا تُجلب البلديات + بطء):** الخادم سليم (سجل laravel فارغ؛ `citiesSelectOptions` مصدره المترجم سليم واختبارات الخادم تمر). العطل في **مسار الجلب الكسول العميل** (`ensureRemoteOptions` → `$wire.call('citiesSelectOptions', scope)` + بوابة `_waitServerAck` بسقف 6 ثوانٍ) — مسار **لم يُتحقق منه بصرياً منذ بنائه** (كل مراحل 34-35/1092 علّمت "يتطلب تحققاً بصرياً يدوياً"). «الطريقة الأفضل» المعتمدة: **تضمين القوائم مباشرة** (محصورة بالنطاق الحالي)، إذ أن `$citiesForSelection()` و`officesForSelection()` كنتيجة تُحسب أصلاً عند كل render — القوائم تصل مع الصفحة، لا تعتمد على جلب العميل، وتزول مهلة الـ 6 ثوانٍ والتأخّر والسباق نهائياً.

### ما نُفّذ (`resources/views/livewire/storefront/order-form.blade.php` فقط — لا حشر منطق خارجي حاجة)

1. **الـ 5 hooks** (state_id / selectedProvider / delivery_type / city_id / selectedStopdesk) حُوِّلت إلى `updated(['x' => fn()])` الصحيحة.
2. **إصلاح `return` غير المشروط في `updated('state_id')`:** المكتب القديم يبقى فقط إن كان لولاية المختارة؛ وإلا يُمسح ويتابع التدفّق للـ auto-pick (الانتقال من ولاية لمكتب واحد → أخرى لمكتب واحد يثبّت الجديد الآن).
3. **حذف الجلب الكسول من الـ checkout:** `role="city-select"` و`role="office-select"` أصبحا مضمَّنين inline (:options="$cities" / :options="$officeOptions") بلا `lazy/source/scope`؛ حُذفت closureا `$citiesSelectOptions`/`$stopdeskSelectOptions` وبذور الـ seeds (dead code)؛ بقي `$citiesForSelection`/`$officesForSelection`/`$formatOfficeOptions` (تُستخدم في render).
4. **المراجعة:** `storefront-select.js`/`select.blade.php` لم يتغيّرا (لا يزالان يدعمان lazy لمن يحتاجه — لأنه لا أحد من checkout).

### التحديثات والشهادة

- **اختبارات دائمة +4 في `StorefrontOrderShippingCascadeTest` (تُثبت أن الـ hooks تشتغل الآن):** ولاية مكتب واحد (ببلدية) تثبّت المكتب ذاتياً وتحمل البلدية للطلب **بلا ضبط صريح**؛ ولاية مكتب واحد (بدون بلدية) تثبّت وتبقى بلدية الطلب NULL؛ الانتقال لولاية أخرى يُسقط المكتب القديم ويثبّت الجديد مع بلديتها؛ بلديات المنزل مضمّنة ومحصورة بالولاية المختارة (لا data-lazy، ولا بلديات ولاية أخرى).
- **تحديث 3 تأكيدات lazy → inline** في `StorefrontOrderShippingCascadeTest` + `CartOrderLimitsTest` (قوائم المكتب بالمصادر تُفحص الآن عبر `formatOfficeOptions(officesForSelection())` + الأسماء ظاهرة في HTML).
- **إعادة ترتيب `set()` في 7 اختبارات قديمة** (كانت «state_id/city_id ثم delivery_type» — عُلّقت على السلوك المعطوب؛ الـ hook الحي الآن يصفّر الجغرافيا عند تبديل نوع التوصيل كما في المتصفح الواقعي).
- **الشهادة:** `pest tests/Feature/Storefront` = **119 ناجح (416 تأكيد)** قبلها 116 (‎-1 ملف إعادة مؤقت +4 دائمة). `php -l` نظيفـ، Pint نظيف (أصلح 1 مخالفة تركيب في CartOrderLimitsTest)، `view:clear`+`view:cache` سليمان، `npm` غير مطلوب (لا تغيير JS). الجولة الكاملة قيد التدوير بعد الإلغاء (عدد النهائي يُعتمد بالتوازي).
- **تحقق بصري يدوي يُتوقَّع من المستخدم على `demo.edzeery.com` (375/768/1440):** ① المنزل ← اختر ولاية → البلديات **تظهر فوراً** (لا سبينر طويل) وتُخزَّن مع الطلب؛ ② المكتب ← ولاية بمكتب واحد (مثل 01 Adrar) → بطاقة المكتب تُثبَّت تلقائياً والطلب يُنشأ (ببلدية أو بدونها حسب المكتب)؛ ③ المكتب ← ولاية بمكاتب متعددة (مثل 16 Alger) → اختيار المكتب يحمل بلدية المكتب إلى الطلب؛ ④ شركة واحدة → لا منتقي شركة وسطر «عبر الشركة» يظهر.

---

## إعادة تصميم صفحة نجاح الطلب `/order/success/{order:id}` — متجاوبة بلا خط زمني ✅ (2026-09-18)

**طلب المستخدم:** صفحة المتجر «طلب ناجح» — تبديل كامل التخطيط إلى عمودين (معلومات العميل في جهة + ملخص الطلب في الجهة الأخرى) بهوية Apple Design Restraint، متجاوبة على 375/768/1440px، وإزالة بلوك تتبع الحالة («ماذا يحدث بعد ذلك؟») نهائيًا.

### ما نُفّذ (`resources/views/livewire/storefront/order-success.blade.php` فقط — بلا مفاتيح ترجمة أو استعلامات أو JS جديدة)

1. **حذف بلوك «ماذا يحدث بعد ذلك؟» كاملًا** (كان بالأسطر 196–261: `what_happens_next` + `step_order_placed/100%/step_out_for_delivery/step_delivered`) — لا تعرض الحالة إطلاقًا بعد الآن؛ أُبقي سطر «سنتصل بك» كلمسة تواصل وليست حالة.
2. **تخطيط عمودين متجاوب:** حاوية `max-w-5xl mx-auto`؛ شبكة `grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8 items-start` — الملخص `md:col-span-7` والعميل/التوصيل `md:col-span-5`؛ 375px مكدّس (الملخص أولًا)، 768px عمودان جنبًا إلى جنب، 1440px مسافات/حشوة أوسع (`lg:gap-8` + `lg:px-8`). بطاقات بكلاسات التصميم المعيارية (`rounded-2xl border shadow-sm bg-white dark:bg-gray-800`) وهويات المتجر `store-*`.
3. **ملخص الطلب:** عناصر بصورة 48–56px (`rounded-xl object-cover` + fallback `noimg.png`) مع المتغير و«سعر × كمية» و`tabular-nums`؛ أسطر subtotal / shipping («مجانًا» بالأخضر عند الصفر) / **خصم شرطي** (عبر `titles.discount` الموجود باللغات الأربع — صفر مفاتيح جديدة) / الإجمالي من accessor `grand_total`.
4. **بطاقة العميل/التوصيل:** الاسم، الهاتف (`dir="ltr"`)، طريقة الدفع (`cod → storefront.payment_on_delivery` وإلا القيمة الخام)، كتلة توصيل: stopdesk بارزة بهوية المتجر (نُقلت من بطاقة الملخص) أو منزلي (address + city + state بـ `break-words`)، وملاحظات بحدود `border-t`.
5. **الأداء:** أُزيل `'status'` من eager-load في `mount()` (استعلام أقل)؛ لا جلب كسول جديد (تجنّب `stopdeskPoint.city`) ولا JS/SCSS.

### الشهادة

- `php -l` نظيف، `view:clear` + `view:cache` سليمان، `npm run build` ناجح (تحذيرات Sass الخاصة بـ Bootstrap موجودة سابقًا).
- **الجولة الكاملة: 849 ناجح (3468 تأكيدًا) — فشل وحيد سابق التأسيس** في `CarrierSyncObservabilityTest` (`expectsOutputToContain('8')` لا يطابق مخرجات `carrier-sync:report`) + 1 risky (نفس الملف)؛ أُثبت أنه مسبق: يفشل منفردًا وبعد إخفاء التعديل (`git stash`). يُشغَّل بالذاكرة الموسعة `-d memory_limit=-1`.
- **التحقق البصري اليدوي المُتوقَّع على المتجر الحي (375/768/1440):** ① 375px — بطاقة الملخص ثم العميل مكدّستين، لا خط زمني؛ ② 768px — الملخص يسار/العميل يمين جنبًا إلى جنب في صف واحد؛ ③ 1440px — بطاقتان متساويتا الارتفاع تقريبًا مع مسافات أوسع وCTR في الوسط؛ ④ ملخص مالي: subtotal + shipping − discount = الإجمالي (عند وجود خصم على الطلب).

---

## Phase 33-rider — إصلاح «إرسال الطلبية إلى رجل التوصيل بلا رقم تتبع» + تنسيق الرقم الجديد (سبتمبر 2026) ✅

**طلب المستخدم (بالعربية):** «لا يزال إرسال الطلبية إلى رجل التوصيل بلا رقم التتبع» — إصلاح إنشاء تتبع بطريقة جديدة: 3 حروف من اسم المتجر — نوع التوصيل `HM|SD` — رقم عشوائي 6 أرقام.

- **التنسيق الجديد في `OrderTrackingService::generateRiderTrackingNumber()`:** `{PREFIX}-{HM|SD}-{6 أرقام}` (مثال `EDZ-HM-402731`) — أُزيل مقطع الولاية نهائيًا مع حذف `stateCode()` والاعتماد على `State`؛ بقيت `storePrefix()` (أول 3 أحرف ASCII، حشو آخر حرف: `Lo`→`LOO`، سقوط بالـ slug عند اسم بلا أحرف لاتينية، fallback `STO`) مع حلقات تجاوز الاصطدام وحماية Code128.
- **الجذر الفعلي:** مسار إرسال الراجل في `OrderShippingGateway::send()` كان ينشئ صف `OrderTracking` برقم تتبع **NULL**، والمسار الوحيد الذي كان يملؤه = درج التأكيد فقط (`submitConfirmAndSend`)؛ مسارا الإرسال المباشر (`sendConfirmedOrder`) والإرسال الجماعي (`confirmBulkSend`) تجاوزا التخزين. **الحل:** closure مشتركة `$ensureRiderTrackingAfterSend(Order $order)` في `merchant/orders/index.blade.php` (حراس مسار الراجل: `delivery_rider_id` مضمّن و`shipping_provider_id` فارغ؛ محايدة عبر `ensureRiderTracking`) ومربوطة بالمسارات الثلاثة.
- **الشهادة:** `OrderCompletenessTest` 29/29 التأكيدات `OCO-HM-`/`EDZ-HM-`/`LOO-HM-`… بما فيها اختبارا انحدار جديدان («إرسال طلبية مؤكَّدة مباشرة إلى الراجل يسترجع رقم تتبعها» و«الإرسال الجماعي إلى الراجل يسترجع رقم تتبعها» بتأكيد `/^OCO-HM-\d{6}$/`) + `TrackingSearchFilterTest` 33/33 (`TRA-HM-`/`TRA-SD-`) — عبر `view:cache` ثم `view:clear` (تجنّب قفل تحويل ملفات Windows).

---

## Phase 34.1 — أساسات فصل سعة التأكيد عن التتبع (توزيع الطلبيات) — مخطط + نماذج + إعدادات فقط ✅ (2026-09-18)

**طلب المستخدم/قرار المالك:** لكل متجر سقفا سعة منفصلان لفريقي التأكيد والتتبع حتى لعضو يملك الصلاحيتين معًا؛ تجاوز ناعم تلقائي حتى نسبة يقوّمها التاجر فوق `max_concurrent_orders` (افتراضي 10%)، يُطبَّق لاحقًا وبالتساوي على الجانبين (المراحل 34.2–34.4 خارج النطاق). هذه المرحلة تضع الأساسات فقط — **بلا منطق تعيين/تجاوز**.

**ما نُفّذ (ضد commit `9dc4b6d`):**

1. **هجرات `2026_09_18_*` (4):**
   - `confirmation_shifts.role_scope` (string، افتراضي `'confirm'` — كل الصفوف القائمة تبقى صالحة بلا إعادة بذر) + فهرس مركّب `confirmation_shifts_store_role_active_idx` على `(store_id, membership_id, role_scope, is_active)` (الاسم القصير تجنّبًا لخطأ MySQL 1059، حُدّد صراحةً).
   - `order_trackings`: `assigned_to_membership_id`/`assigned_by_membership_id` (foreignUlid → `store_memberships` nullOnDelete) + `assigned_at` (timestamp) + `assignment_method` (string) — مرآة حرفية لأعمدة جانب التأكيد — + فهرس على `assigned_to_membership_id`.
   - `over_capacity` (boolean افتراضي false) على `orders` و`order_trackings`.
   - `store_settings`: `distribution_overflow_enabled` (boolean افتراضي true) + `distribution_overflow_percentage` (unsignedTinyInteger افتراضي 10؛ المدى 0–100 في طبقة التحقق فقط).
2. **النماذج:** `ConfirmationShift` (+`role_scope` في `$fillable`/`$casts` + `scopeConfirm()`/`scopeTrack()`؛ 153→165 سطر) ▪ `StoreSetting` (+الحقلان في `$fillable` + casts boolean/integer؛ 59→63) ▪ `OrderTracking` (+الحقلان وجداول/تحقق التواريخ في `$fillable`/`$casts` + `assignedTo()`/`assignedBy()` BelongsTo→StoreMembership؛ 136→153).
3. **وصفحة جديدة** `livewire/merchant/order-distribution-settings/` (Volt `index.blade.php` **51 سطر** تحت 400 + قسم `partials/overflow-settings.blade.php` **42 سطر** تحت 300): تبديل «تفعيل التجاوز التلقائي» (x-edz.checkbox بنمط بطاقات toggle القائمة) + حقل نسبة 0–100 بمدخل number والحرف ٪، معطّل عند الإيقاف، تحقق خادمي `required|integer|min:0|max:100`، حفظ عبر `$store->settings()->updateOrCreate()` + توست `settings_saved`. حارس `canStore(STORE_UPDATE)` في mount والحفظ. مسار `merchant.order-distribution-settings` في `routes/merchant.php` + رابط قائمة جانبية (عمليات، حارس `$canViewOrderSettings`) + 8 مفاتيح ترجمة ×4 لغات. واجهة متجاوبة بطبيعتها (عرض max-w-2xl/48، لا نوافذ ثابتة): 375px مكدّسة، 768/1440 بحد أقصى 672px.
4. **الشهادة:** `migrate:fresh --seed` نظيف؛ الأعمدة الخمسة عشر + الفهارس مؤكَّدة بـ `SHOW COLUMNS`/`SHOW INDEX` (راجع `verify_cols.php`)؛ `ConfirmationShiftTest` (6) + `OrderAssignmentServiceTest` (6) = 12/12 بنجاح **بدون تعديل** (توافق رجعي: الجهات بلا `role_scope` تحصل `confirm` افتراضيًا)؛ اختبار عابر مؤقّت (حُذف لاحقًا) أثبت عرض/حفظ/رفض 150 على الصفحة الجديدة 3/3؛ `php -l` نظيف ×9؛ لا استعلامات في حلقة (mount: قراءة settings واحدة؛ حفظ: updateOrCreate واحدة).

| فرع | الحالة | الملفات الرئيسية | تحقق |
|---|---|---|---|
| 34.1 أساسات توزيع الطلبيات | ✅ | 4 هجرات `2026_09_18_*` + ConfirmationShift + StoreSetting + OrderTracking + order-distribution-settings/{index, partials/overflow-settings} + routes/merchant.php + store-sidebar + merchant_panel.php ×4 | migrate:fresh --seed + 12/12 (بدون تعديل) + فهارس الـ cols + تجربة عرض مؤقتة 3/3 |

> **الاعتماد المباشر للمرحلة 34.2:** تعيين سعة التتبع يستهلك `role_scope='track'` (نطاق الـ scopes `scopeConfirm()/scopeTrack()` والفهرس المركّب الجديد) وعمودي `order_trackings.assigned_to/assigned_at/assignment_method/over_capacity`؛ والتجاوز الناعم (34.3) يستهلك `store_settings.distribution_overflow_*` و`over_capacity`. يُمنع تعديل `OrderAssignmentService`/`OrderConfirmationService`/`DispatchPendingAssignmentsJob`/`reassign-modal.blade.php`/`order-settings.blade.php` قبل فتح 34.2.

---

## Phase 34.2 � ����� ����� ��� ������: Trait + ������ + ������ �������� + �������� ? (2026-09-18)

**�����:** ������� ���� ������� �������� ������ (on-shift + ���/���� + ������ ������) ��� Trait ����� ������� �� ���� ������� (��� �� ����� �� ������) ����� ������ �����ϡ �� ���� ����� `OrderTracking` ������ ���� ������ �� 15 �����. **�� ������/Blade� �� ���� ����� ���� (���� ������ ��� 34.3)� �� ����� ����/������ ������.**

**�� �����:**

1. **`App\Domains\Order\Concerns\ResolvesCapacityBalancedCandidates` (Trait� 107 ��� ? 120):** `bestCandidateOnShift(candidates, storeId, roleScope, openCounts, lastAssignedAt)` ��� ������ ��� ����� �������Ѻ `quotaCapsByMember()` ������� ���� ���� (`MAX(max_concurrent_orders)` groupBy ������ ���� ����� ��� `confirm()/track()`)� `isOnShift()` ����� `isOnActiveShift(roleScope: �)` �� `Log::debug('Member not on active shift', membership_id/user_id)`� `withinQuota()` (��� null = ��� ��ݺ `openCount < cap`)� `outranks()` (��� ������ �� ���� ��� ����� fallback `1970-01-01`).
2. **����� ����� `OrderAssignmentService` (297 ? 248 ��� < 250):** ������ ��� Trait (������ `membershipCap/filterOnShift/loadBalance` �import ��� ConfirmationShift)� `selectBest` ���� ������ tier (������ �� ����) ��� `bestCandidateOnShift` ������ `'confirm'`� ��������� `openOrderCounts`/`lastAssignedAt` ����� ������ɺ **������ ����� ������** � `OrderAssignmentServiceTest` (6) + `ConfirmationShiftTest` (6) = 12/12 ���� ���� �����.
3. **`StoreMembership::isOnActiveShift(?Carbon $at = null, string $roleScope = 'confirm')`:** `->when($roleScope === 'track', track(), confirm())` ��� ������� `confirmationShifts()`� ������� ������ ����� (�� �������� �������� ��� ����).
4. **`App\Domains\Order\Services\OrderTrackingAssignmentService` (151 ��� < 250):** `assign(tracking)` � ���� null store� ��� �������� = ����� ����� ��`CRM_ORDER_TRACKING` (�� `with('storeWithTimezone')` ���� N+1)� ��� ����� �� `ConfirmationShift::track()`� `openAssignmentCounts()` ��� `order_trackings` (assigned ��� null + `tracking_status` �� `OrderTrackingStatus::open()`)� `lastAssignedAt()` ��� MAX(assigned_at)� �� `bestCandidateOnShift(...,'track',...)`� �� ���� ? `Log::warning('Order tracking auto-assignment skipped: no candidates on active shift', �)` + ��� ������ɺ ������ ? `assigned_to/assigned_at=now()/assignment_method='auto'` + `Log::info`. `reassign(tracking, to, by)` � ���� ��� ������ (`InvalidArgumentException`)� `assigned_by_membership_id` + `assignment_method='manual'`.
5. **`App\Domains\Order\Jobs\DispatchPendingTrackingAssignmentsJob` (41 ���):** ���� `DispatchPendingAssignmentsJob` � `OrderTracking` ��� `assigned_to_membership_id` null + `assignment_method` null + `tracking_status = SHIPPED->value` �� `with('store')`� ��� �� assign ���� try/catch + `Log::error('Failed to auto-assign order tracking', tracking_id/error)`� ����� �� `routes/console.php` ����� �����: `Schedule::job(new DispatchPendingTrackingAssignmentsJob)->everyFifteenMinutes();`.
6. **`tests/Feature/Order/OrderTrackingAssignmentServiceTest` (9 �������ʡ 15 ������):** ����� ����� ��� ���� ���� ���ɺ ��� `role_scope='track'` ������ ��� `role_scope='confirm'` �� ��� ��� �����ں ������ ��� ����� ���Ǻ ��� ������� ����� ��� ����� ��� ���� (���� confirm ���) ? ���� ��� ����� ��� ������� ORDER_CONFIRM ��� �� ������ ����� (������� �� CRM_ORDER_TRACKING ��� ����)� `reassign` ��� ������ ������ manual� ��� ��� ���� ���. (����: `SystemStatusesSeeder` + `Africa/Algiers` + `Carbon::setTestNow(2026-05-04 10:00)` �������.)

7. **�������:** `php -l` ���� �7 ����ʺ `OrderAssignmentServiceTest`+`ConfirmationShiftTest` **12/12 ���� �����**� `OrderTrackingAssignmentServiceTest` 9/9� ���� PHP ��������: Trait 107 ? 120� `OrderAssignmentService` 248 < 250� `OrderTrackingAssignmentService` 151 < 250� Job 41� routes/console 53� �� N+1 (����� `storeWithTimezone` ���� ������� ���������� ���� ��� ���� ���� �����).

| ��� | ������ | ������� �������� | ���� |
|---|---|---|---|
| 34.2 ����� ����� ��� ������ | ? | ResolvesCapacityBalancedCandidates + OrderAssignmentService + StoreMembership::isOnActiveShift + OrderTrackingAssignmentService + DispatchPendingTrackingAssignmentsJob + routes/console.php + OrderTrackingAssignmentServiceTest | php -l �7 + 12/12 (���� �����) + 9/9 + ���� 248/151/107/41/53 + �� N+1 |

> **�������� ������� 34.3:** ������� ������ ������ `store_settings.distribution_overflow_enabled/percentage` �`orders.over_capacity`/`order_trackings.over_capacity` (�� ������� ��� � ���� false) �������� �������� ��� ����� ������� �������. ����� ����� `OrderAssignmentService`/`OrderTrackingAssignmentService`/`DispatchPendingAssignmentsJob`/`DispatchPendingTrackingAssignmentsJob` ��� ��� 34.3.
---

## Order-Settings Cleanup — دمج تبويب التجاوز بعد 34.3: نظافة + إصلاح regression ✅ (2026-09-18)

**السياق/القرار:** بعد 34.3 التجاوز الناعم عاش في مكانين (تبويب داخل `/{store:slug}/order-settings` + صفحة مستقلة `/{store:slug}/order-distribution-settings`). قرار المالك: تبقى التبويب فقط، والحذف للمرجعية المستقلة، وهذا تنظيف هيكلي/كود ميت فقط — بلا وظائف جديدة.

1. **إصلاح عطل `storage/logs/laravel.log`:** سطر `$this->members = StoreMembership::where(...)` كان يضع `Eloquent\Builder` في حالة Livewire فتُرمى `Property type not supported in Livewire for property ... members` عند mount الصفحة — أُعيد `->with('user')->get()->toArray()`.
2. **حذف الصفحة المستقلة:** `order-distribution-settings/index.blade.php` حُذف (المسار مزال مسبقًا من `routes/merchant.php`، فلا route cache — ترجع 404)؛ أُزيل اسم المسار `merchant.order-distribution-settings` من `store-sidebar.blade.php` (مصفوفة `operationsOpen`)؛ حُذف المفتاحان الميتان `order_distribution_settings`/`order_distribution_settings_desc` من `ar/en/fr/es`. الـ partial المشترك `order-distribution-settings/partials/overflow-settings.blade.php` بقي مكانه ويُضمَّن من `order-settings.blade.php` كأحد التبويبات.
3. **إجراء حفظ واحد:** حُذف closure `$save` الميت (كان شادوًا/alias سابقًا ثم إملاء كاملًا بلا ربط)؛ بقي `saveOverflow` الوحيد (تحقق `required|integer|min:0|max:100` + `settings()->updateOrCreate` + toast `settings_saved`) — لا `wire:click="save"` معلّق في أي Blade (grep).
4. **تقسيم `order-settings.blade.php` إلى partials** بنمط `confirm-drawer`: `livewire/merchant/order-settings/partials/{overview,tabs,shifts-tab,assignments-tab,shift-modal,assignments-modal}.blade.php`؛ كامل كتلة Volt (state/actions) في الملف الرئيسي، والـ overflow tab inline يضمّن الـ partial المشترك. **816 → 393 سطرًا** (تحت سقف 400).
5. **الشهادة:** `php -l` نظيف ×4 ملفات لغة؛ `view:cache`/`view:clear` نظيف؛ الـ markup بعد الفصل مطابق حرفيًا للمصدر (تقسيم مكاني فقط → لا تغيير في 375/768/1440)؛ `ConfirmationShiftTest`+`OrderAssignmentServiceTest`+`OrderTrackingAssignmentServiceTest` = 23/23؛ مجموعة `tests/Feature/Merchant` كاملة = 575 اختبارًا (2448 assertion) خضراء.

> **التالي — Phase 34.4 (بانتظار موافقة منفصلة):** إعادة بناء `reassign-modal.blade.php`. غير مسموح بلمس الـ modal/الخدمات قبل فتحها.

## Reassignment UI (confirm + tracking) — إعادة التكليف اليدوي (مرحلة 34.4)

**الوضع: مكتملة ✅**

1. **منشئ قوائم المرشحين (`AssignmentCandidateResolver`):** `app/Domains/Order/Support/AssignmentCandidateResolver.php` (**206 سطر** < 250). يُرجع الأعضاء النشطين في المتجر الحاملين للصلاحية المطلوبة فقط، مع حقل لكل مرشح: `open` (حمله الحالي ضمن النطاق)، `cap` (سقف الوردية المعني، `null` = غير محدود)، `on_shift` (هل هو في ورديته الآن)، `dual_role` (يحمل Confirm + Track). جميع الاستعلامات مجمّعة ولا يوجد N+1 (اختبار يُثبت ثبات عدد الاستعلامات لنفس المتجر مع 1 مقابل 4 مرشحين). نموذج قراءة للواجهة فقط — `reassign()` في الخدمتين لم يُتعدَّ (التجاوز اليدوي محفوظ لكل من `OrderAssignmentService` و`OrderTrackingAssignmentService`).
2. **إعادة بناء `reassign-modal.blade.php`** كقطعة مشتركة (**89 سطر** < 300) تُضمَّن بالبارامترات: قائمة اختيار بصيغة radio مع الاسم + شارة "في الوردية" (خضراء) + شارة الدور المزدوج + الحمل الحالي "عدد/سقف" أو "غير محدود"؛ تحذير غير مانع بالبرتقالي عند اختيار مرشح بلغ سقفه؛ حالة فارغة عند غياب المرشحين؛ أزرار إلغاء/تكليف. الواجهة محققة وفق تصميم Apple في DESIGN_SYSTEM.md.
3. **صفحة الطلبات (`orders/index.blade.php`):** `$openReassignModal` أصبح يملأ `reassignCandidates` من المُنشئ بصلاحية `ORDER_CONFIRM` ونطاق `confirm`، و`$submitReassign` يضيف إعادة فحص صلاحية الهدف (`ORDER_CONFIRM`) كخط دفاع ثانٍ؛ استدعاء الـ modal عند السطر 5758 يمرر البارامترات المُشاركة. `$allMembers` بقي كما هو (يُستخدم في 515/517/575/5139/5148). شارة الـ trigger موجودة أصلاً في `orders-table-actions-column.blade.php` (106).
4. **صفحة التتبع (`tracking/index.blade.php`):** **373 → 394 سطر** (< 400). traits جديدة `app/Livewire/Concerns/TrackingReassignConcern.php` (**65 سطر**) فيها `openTrackingReassignModal`/`submitTrackingReassign` بصلاحية `ORDER_MANAGE` (نفس نمط أفعـال Drawer في مقاس التتبع) ونطاق `track` مع `CRM_ORDER_TRACKING`، ثم `OrderTrackingAssignmentService::reassign` + toast `order_flow.tracking_reassigned`. 4 حالات state جديدة + include للـmodal المشترك. الشارة أُضيفت في `tracking-row-cell.blade.php` و`tracking-mobile-card.blade.php` (مقيّدة بـ `ORDER_MANAGE` وتبويب `carrier` فقط).
5. **مفاتيح ترجمة جديدة** في `ar/en/fr/es` (`merchant_panel`): `on_shift`, `dual_role_badge`, `current_load`, `reassign_no_candidates`, `reassign_over_capacity_warning`; و`order_flow.tracking_reassigned`. كلها سليمة لـ `php -l`.
6. **التحقق:** `php -l` نظيف (PHP جديد + 8 ملفات لغة)، `view:cache`/`view:clear` نظيف، اختبار `tests/Feature/Order/AssignmentCandidateResolverTest.php` (**7 اختبارات**) + `tests/Feature/Order` كاملة = 30/30، و`tests/Feature/Merchant` كاملة = **575 ناجح (2448 assertion)**. الفحص البصري على 375/768/1440 لم يُجرَ من الجهاز (لا متصفح) ويحتاج مراجعة المستخدم.

## Phase 34.5 — صفحة طابور التوزيع (overflow queue) + رابط البريد — 2026-09-18 ✅

**إغلاق مبادرة Phase 34 كاملة (34.1 → 34.5):** عبر 34.1 (settings overflow)، 34.2/34.3 (توزيع موزون + تجاوز + علم over_capacity)، 34.4 (أعضاء محدودو الصلاحيات + إعادة التكليف المشتركة) إلى 34.5 — مبادرة توزيع/تأكيد/تتبع الطلبات انتهت وظيفيًا.

1. **الصفحة الجديدة:** `Volt::route('/{store:slug}/order-distribution-queue')` (`routes/merchant.php`) محمية بـ `ORDER_MANAGE`، تتبويبان (تأكيد/تتبع) بنمط تبويبات `order-settings` مع شارة عدّاد حي. `livewire/merchant/order-distribution-queue.blade.php` (**158 سطر** < 400) + partials (`tabs` **20**، `queue-table` **80**).
2. **مصدرا الصف:** `app/Livewire/Concerns/DistributionQueueConcern.php` (**116 سطر** < 250) — `orders`/`order_trackings` حيث `assigned_to_membership_id IS NULL` أو `over_capacity=true`؛ استبعاد الحالات النهائية بمفتاح مُستمد من `OrderStatus::isTerminal()` (مصدر واحد، لا نسخة ثالثة) وقصر التتبع على `OrderTrackingStatus::open()`؛ ترتيب «غير المُسند ثم الأقدم»؛ eager-load بلا N+1.
3. **إعادة التكليف:** إعادة استخدام `reassign-modal.blade.php` المشترك بعقد `@include` نفسه + `AssignmentCandidateResolver` (confirm/track حسب التبويب) + الخدمتين الأصليتين؛ عند التقديم تُمسح `over_capacity` (تكليف يدوي — الحقل غير متتبع في الآرودت، بلا ضجيج) فتغادر الصف الطابور Livewire فورًا.
4. **الوصول:** رابط شقيق لـ `order-settings` في الفئة «العمليات» بـ `store-sidebar.blade.php` (مقيّد بـ `ORDER_MANAGE`) + زر action في بريد `AssignmentCapacityExhaustedNotification` (`route('merchant.order-distribution-queue', ['store' => $store->slug])`).
5. **ترجمة:** 10 مفاتيح جديدة في `merchant_panel` عبر `ar/en/fr/es`.
6. **التحقق:** `tests/Feature/Merchant/OrderDistributionQueueTest.php` (**8 اختبارات/47 assertion**) — حارس الصلاحية، تضمين/استثناء التبويبين + ترتيب الأقدم أولًا، إعادة تكليف حية (تأكيد + تتبع) مع مسح العلم، رفض عضو بلا صلاحية، حالات الفراغ، ثبات عدد الاستعلامات. `tests/Feature/Order` = 30/30، `tests/Feature/Merchant` = **583 ناجح (2495 assertion)**، `BladeInteractivityPolicyTest` = 2/2، `php -l` + `view:cache` سليمان. الفحص البصري 375/768/1440 يبقى للمستخدم (لا متصفح في البيئة).

## حساب الديمو — بيانات شبه حقيقية + أعضاء محدودو الصلاحيات (نسخة بذرة 2026-09-18) ✅

**السياق/القرار:** بناءً على طلب «جهّز حساب الديمو في السيدر ببيانات تجريبية وضف موظف تأكيد فقط وموظف تتبع فقط وموظف تأكيد وتتبع وراجع المشروع كامل وضف بيانات تجريبية» — تم تطوير `database/seeders/DemoStoreSeeder.php` (ازداد من 447 إلى **1210 سطرًا**) ليغطي كل التحسينات الأخيرة ببيانات صافية للعرض (الإسناد/السعات/التجاوز، التتبع، التأكيد، bulk validate، المرتجعات، تعديل الأسعار).

1. **فريق بصلاحيات مُحدّدة (مرحلة 34.4):** ثلاثة أعضاء `staff` في متجر `demo` بصلاحيات مخزّنة فقط (Decision #6) — `demo.confirmer@edzeery.com` (تأكيد فقط: order.confirm، crm.orders.confirm، returns.verify.barcode، returns.process…)، `demo.tracker@edzeery.com` (تتبع فقط: crm.orders.track، delivery.riders.view…)، `demo.dual@edzeery.com` (تأكيد+تتبع: يحمل المنطقتين ويظهر كمرشح «dual_role» في نافذتي إعادة التكليف). كلمة المرور للجميع `password`.
2. **إعدادات الميزات:** `enableFeatureSettings` يفعل `distribution_overflow_enabled=true` + `distribution_overflow_percentage=20` + `allow_price_edit=true`، فتعمل التجاوز/شارة «over capacity»/محرر الأسعار خارج الصندوق.
3. **قواعد التدوير:** `seedConfirmationShifts` — ورديات Sun–Thu (ISO 7,1,2,3,4) بسقوف: تأكيد confirmer 15، تتبع tracker 20، وورديتا dual (تأكيد 25 + تتبع 25). `seedSpecialistAssignment` — dual متخصص في منتج Wireless Earbuds Pro (يفضَّل في المرشحين).
4. **شركات التوصيل المحلية:** `seedShippingProviders` — Ecotrack (افتراضي، 450 DA، 30kg) وZR Express v2 (400 DA، 20kg) مرتبطتان بكتالوج الناقلين العالمي (شعارات/ألوان في شبكة التتبع).
5. **عملاء جزائريون (`seedDemoCustomers`):** 5 عملاء بأسماء/هواتف/عناوين واقعية عبر `algeriaLocation` (DZ + ولاية `state_code` + بلدية برمز بريدي، مع fallback آمن للبذرة المستقلة).
6. **15 طلبية (`seedDemoOrders`، نطاق 21001–21015 محجوز، always-fresh):** موزعة على كل الحالات (pending ×3 منها 2 مُسندة، no_answer_1 بمحاولة اتصال وهاتف ثانوي، postponed، confirmed ×2 منها واحدة بشركة محددة، preparing، shipped ×2 (ناقل + رجّال Yacine)، out_for_delivery (التي عليها `over_capacity=true` + `carrier_validated_at` لتوضيح bulk validate)، delivered بدورة كاملة عبر ZR، returned بشريط `DEMO-RET-001` موثَّق+مُعالَج، cancelled، out_for_delivery رجّال Ahmed). كل طلبية: أصناف/مجموع/عناوين، `OrderStatusHistory` (مصدر confirmed_by)، `OrderEvent` بخط زمني عربي متدرج بواقعية + `reassigned`، و`OrderTracking` + `OrderTrackingHistory` (webhook_token عشوائي، إسناد، over_capacity). البذرة طاقية الإعادة (بالنتائج نفسها بعد كل تشغيل).
7. **إصلاحان اكتشفتهما بيانات الديمو (تعليق صلاحية):**
   - `app/Models/Orders/Order.php` — `confirmedByHistory` كانت `->whereHas('status,confirmed')->latestOfMany('created_at')` والفلتر خارج subquery الإجمال، فيعطي null بمجرد وجود تاريخ أحدث (شحن/تسليم). أصبحت `->ofMany(['created_at'=>'max'], closure)` ليدخل الفلتر داخل التجميع — تتحل `confirmed_by` على الطلبيات المتقدمة أيضًا (اختبار تطبيقي: 21011 الدائمة يعرض المُؤكِّد الآن).
   - السيدر يحذف حدث `created` التلقائي من `OrderObserver` بعد الحفظ ليبقى خطنا الزمني العربي الوحيد (لا ازدواج `created`).
8. **التحقق:** `php -l` نظيف (السيدر + Order.php)، `view:cache`/`view:clear` نظيف، `db:seed --class=DemoStoreSeeder` ناجح (عدّادات حية: 15 طلبية/6 تتبع/37 سجل حالة/≈95 حدث/4 ورديات/2 شركة/3 أعضاء محدودو الصلاحيات)، `tests/Feature/Order` = 30/30، و`tests/Feature/Merchant` كاملة = **575 ناجح (2448 assertion)** صفر انحدار (بعد إصلاح العلاقة). تنسيقات أعضاء الديمو: `demo.confirmer`/`demo.tracker`/`demo.dual` @edzeery.com.

---

## ملخص طلب الدفع المحسّن + سكرول بار بهوية المتجر + الشركة الافتراضية + حارس السلة الفارغة + تذكّر هوية الزبون ✅ (2026-09-18)

**طلب المستخدم (3 محاور ثم محوران إضافيان):** ① ملخص طلب أفضل بهوية المتجر ومتجاوب 375/768/1440؛ ② سكرول بار حسب تخصيص المتجر (لون/حجم/خلفية)؛ ③ عند شركة واحدة تُحدَّد الافتراضية تلقائيًا بلا إظهار شركة غير نشطة ولا احتساب رجال التوصيل (جدول منفصل)؛ ④ **الوصول لصفحة checkout يُمنع مع سلة فارغة**؛ ⑤ **اسم الزبون لا يتعبّأ تلقائيًا من الجلسة**.

### أ. إعادة تصميم الملخص + السكرول + الاختيار التلقائي (`resources/views/livewire/storefront/order-form.blade.php` + `resources/css/app.css` + `components/storefront/select.blade.php`)

1. **ملخص بهوية المتجر:** header بأيقونة `bag` في chip `store-bg-primary-soft` + شارة عدّ `store-bg-primary` + سطر «{{ $cartCount }} items»؛ قائمة الأصناف بفواصل `border-b` وصور بإطار 1px؛ قيم `tabular-nums`؛ **الإجمالي في لوحة `store-bg-primary-soft` بارتفاع واضح** (`text-xl font-bold store-text-primary tabular-nums leading-none`).
2. **شبكة متجاوبة:** حاوية `max-w-4xl` → `xl:max-w-6xl`؛ `grid-cols-1 lg:grid-cols-3` → `grid-cols-1 md:grid-cols-5 lg:grid-cols-3 gap-6 md:gap-8`؛ العمود الأيسر `md:col-span-3 lg:col-span-2`؛ الملخص `md:col-span-2 lg:col-span-1` مع `md:sticky md:top-24` (لا تلصّق ثابتة على الموبايل). 375px مكدّس، 768px 2/5، 1440px حاوية أعرض.
3. **سكرول بار `.sf-scroll` جديد في `app.css`** (صفر JS): `scrollbar-width: thin` + `scrollbar-color`؛ webkit 6px، track بصبغة 8% من `--store-primary`، إبهام `--store-primary`، hover `--store-secondary`، نسخة `.dark` مفتّحة بمخلّط أبيض 60–65%. طُبّق على قائمة الملخص (`max-h-64 overflow-y-auto sf-scroll ps-1 -me-1`) وعلى لوحة الخيارات `components/storefront/select.blade.php` (`<ul>` sf-scroll).
4. **الاختيار التلقائي:** `availableProviders` يضيف `is_default` ويُرتَّب `orderByDesc('is_default')->orderBy('name')`؛ في `mount` وكتلة `@php`: شركة واحدة ← تُثبَّت كالسابق؛ عدة شركات + `is_default` ← تُسبَق الافتراضية (يبقى للزبون تغييرها)؛ بلا افتراضية ← السلوك الكلاسيكي. **بلا أي `whereNull('rider_name')`** — رجال التوصيل جدول منفصل أصلاً. صفر استعلامات/round-trips جديدة (من collection محمّلة).

### ب. حارس السلة الفارغة + تذكّر هوية الزبون

5. **حارس مسار checkout** `app/Http/Middleware/Store/EnsureCartNotEmpty.php` (21 سطرًا): سلة فارغة → `redirect()->route('storefront.home', ['store' => slug])`. أُسجّل كألياس `storefront.cart` في `bootstrap/app.php` وأُطُبّق على `/checkout` (`routes/storefront.php`) بعد `resolve.store` مباشرة.
6. **هوية الزبون في الجلسة:** بعد نجاح الطلب يُحفظ `session(['storefront_customer_{storeId}' => name/phone/email])`؛ في `mount` تُتعبّأ الحقول من الجلسة (مفتاح لكل متجر) مع أسبقية حساب المنصة المسجّل. اسم/هاتف/بريد معًا للاتساق.
7. **اختبارات +4:** `tests/Feature/Storefront/CheckoutAccessControlTest.php` — (1) سلة فارغة → redirect للرئيسية، (2) سلة ممتلئة → 200، (3) تعبئة القيم من الجلسة، (4) طلب ناجح يحفظ الهوية في الجلسة.

### الشهادة

- `php -l` نظيف (الـ Middleware الجديد)؛ `view:clear`+`view:cache` سليمان؛ `npm run build` ناجح (25 ثانية، تحذيرات Bootstrap Sass سابقة).
- **`vendor/bin/pest tests/Feature/Storefront` = 123 ناجح (430 تأكيد)** (119 قديمة + 4 جديدة)؛ كلاسات الـ cascade الثمانية عالقة.
- **ملاحظة بيئية لكتابة هامة:** `php artisan test` يعيد إطلاق PHP بحد ذاكرة 512MB (يرمى `Allowed memory size of 536870912 bytes exhausted` في `BladeCompiler`/`SortableIterator` بعد `StorefrontTemplatesTest` رغم `-d memory_limit=-1` على العملية الأم). **الاستعمال الصحيح على هذا الجهاز: `php -d memory_limit=-1 vendor/bin/pest` مباشرة** (يرث العلم فلا انقطاع). فحصٌ: `php artisan tinker -d` يطبع `string(-1)` لكن `php artisan test` لا.
- **فشل واحد مسبق غير مرتبط بالتعديل:** `CarrierSyncObservabilityTest` («the carrier-sync:report…» — `expectsOutputToContain('8')` مقابل جدول CRLF) — يفشل منفردًا أيضًا كما في الجولات السابقة.
- **التحقق البصري اليدوي المُتوقَّع (375/768/1440):** ① checkout بسلة فارغة يوجَّه فورًا للرئيسية؛ ② زبون عاد بعد طلب سابق يجد اسمه/هاتفه مملوءين في نفس المتجر (وليس في متجر آخر)؛ ③ 375px مكدّس بلا sticky، 768px الملخص 2/5، 1440px أعمدة أعرض؛ ④ متجر بألوان خضراء → سكرول الملخص أخضر، أرجواني → أرجواني؛ ⑤ عدة شركات مع افتراضية → تُسبَق تلقائيًا وقابلة للتغيير.

---

## Phase 36.3 — «Tiny fix»: فريق `teams/index.blade.php` تحت سقف 400 سطر ✅ (2026-09-21)

طلب مباشر (لا تغيير وظيفي): الملف كان **402 سطرًا** (>400). استُخرج حرفيًا كتلة المودال الشرطي لمنتج-النطاق (`@if ($scopeProduct)` … `@endif`) إلى جزئية جديدة `resources/views/livewire/merchant/teams/partials/product-scope-mount.blade.php` (**6 أسطر**) وحُلّ محلها `@include`. النتيجة **399 سطرًا ≤ 400** — انخفاض 3 أسطر فقط بلا أي تغيير في المنطق/القوالب/المفاتيح. `php -l` + `view:cache` سليمان.

## Phase 36.4 — نطاق رؤية الطلبيات/التتبع حسب العضوية (`visibleTo`) ✅ (2026-09-21)

**الطلب (مقبول البناء مع انحراف موقّع واحد):** إضافة حارس رؤية جداري اختياري على قائمة الطلبيات والتتبع: المالك/المدير/الموظف يرون نطاقًا متدرجًا، مع حماية `?StoreMembership` (nullable) بدل `StoreMembership` الصارم لأن `canStore()` يعبر فحص `super_admin/admin` قبل وجود عضوية — وإلا TypeError لموظفي المنصة.

1. **`app/Models/Orders/Concerns/HasVisibilityScope.php`** (53 سطرًا، trait — إبقاء `Order.php` عند 226 الإجمالي +سطرين فقط بدل تضخيم النموذج): `scopeVisibleTo(?StoreMembership)` غير عام (يُستخدم صراحةً فقط ولا يُفعَّل ضمنيًا):
   - `null`/بلا عضوية → no-op.
   - `TEAM_VIEW` (owner/admin) → دون لمس الاستعلام.
   - `TEAM_VIEW_OWN` (manager) → `assigned_to_membership_id IN [self + subordinates]`، وإذا كان لإدارة المحل نطاق منتجات (`StoreProductScopeService::assignedProductIds`) يضيّق `whereHas('items', product_id IN …)`.
   - غيره (staff) → `assigned_to_membership_id = self`.
2. **نقاط الربط (5 — عدد أدنى، سطر واحد لكلٍّ):** قائمة الطلبيات `orders/index.blade.php` (مسار loading ~798 ومسار trash ~1035) عبر `user()->storeMembership(currentStore())`؛ وشبكة التتبع `app/Livewire/Concerns/TrackingGridConcern.php` (القائمة الرئيسية ~72، المهملة ~25، وعدّاد المهملة `trashCount` ~357) عبر `$this->getMembership()` (من `TrackingColumnConcern`).
3. **الضمانات:** صفحة طابور التوزيع **لا** تسلسل `visibleTo` (قاعدة صفّه بالتقاطع: غير مُسندة/over_capacity — أثبته اختبار 6 أدناه)؛ لا لمس لأي service/AdminsAssignment إلخ.
4. **`tests/Feature/Merchant/OrderVisibilityScopingTest.php`** (6 اختبارات/13 تأكيد): ① owner يرى كل طلبية بأي إسناد ② staff يرى مُسنداته فقط (لا زميل/مدير/غير مُسند) ③ manager بلا نطاق منتجات: ذاتي + مُشرِفيهم فقط ④ manager بنطاق منتج: طلبات الفريق التي تحوي المنتج المعيّن فقط ⑤ التتبع: القائمة الرئيسية/المهملة/العدّاد لنفس النطاق (مع منح staff صلاحية `ORDER_DELETE` فقط لبلوغ سلة المهملة — الحارس «صلاحية» لا يعطي TEAM_VIEW) ⑥ طابور التوزيع: صفّ لا يتغيّر (بلا سلوك رؤية مكتسب أو مفقود).
5. **تكييف 3 اختبارات قديمة مثّل السلوك السابق (كانت تفترض أن staff/manager يرى كل الطلبيات):** `OrderInlineItemsEditTest` (5 حالات staff — تمرير `assignee: $membership` للطلب)، `OrderEventLogVisibilityTest` (طلب آخر غير مُسند صار غير معروض للمدير → `toBeNull()` بدل `toBeFalse()`)، `OrdersMobileMoreMenuTest` (إسناد الطلب لعضو staff ليظهر الصف). **لا تغيير في الكود الخاضع للاختبار** — فقط إصلاح الفرضية.
6. **الشهادة:** `php -l` نظيف على كل الملفات؛ **`tests/Feature/Merchant` كاملة = 614 ناجح (2618 تأكيد)** صفر انحدار (607 سابقة + 7)؛ `tests/Feature/Order` + `tests/Feature/Shipping` سليمان (باستثناء فشل `CarrierSyncObservabilityTest` المسبق الموثّق — `expectsOutputToContain('8')`/جدول CRLF). حجم `orders/index.blade.php` = **5,641 سطر < سقف 5,699** الموثّق. الذاكرة: التشغيل الصحيح على هذا الجهاز `php -d memory_limit=2048M vendor/bin/pest` (موثّق أدناه في ملاحظة 34.5 الجولة السابقة).
7. **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px/768px/1440px على قائمة الطلبيات وشبكة التتبع بنطاقَي staff (عرض اسمه فقط) وmanager (ذاته + فرقه) وowner (الكل).

---

## Phase 36.5 — حجب كل بيانات الاشتراك/الخطة/الفواتير عن أعضاء المتجر دون `STORE_BILLING_MANAGE` (2026-09-21)

**الطلب (معتمد من المستخدم مع تعديل واحد):** الموظف/المدير/المشرف لا يرى أي أثر لخطة الاشتراك أو حالة الفوترة للمتجر (شارة الخطة، زر «الفواتير» السريع، بطاقة الاستهلاك، شريط انتهاء الاشتراك، بلاطة الخطة في قائمة المتاجر) — ولا حتى عبر تسلسل الحالة في Livewire (`wire:snapshot`). حُشرت الصلاحية في مستخلص مشترك `canViewStoreBilling(Store, ?User): bool` بنمط `canStore()`/`canManageTeam()`، وشريط النبأ مخفي **بالكامل** للموظف (بدون نسخة بلا رابط).

1. **حذف ملفَين ميّتين بلا أي مرجع (grep صفري):** `app/Services/Stores/SubscriptionAlertService.php` + `resources/views/filament/merchant/modals/subscription-alerts.blade.php`.
2. **`app/Helpers/helpers.php`:** `canViewStoreBilling(Store $store, ?User $user = null): bool` — `$store->user_id === $user->id` (مالك) أو `$user->storeMembership($store)?->can(\App\Enums\Store\StorePermissionEnum::STORE_BILLING_MANAGE->value)`.
3. **المنع الثلاثي:**
   - `choose-store.blade.php` — حالة `canViewBilling` (يُحسب بوجود متجر `billing_visible`)، و`billing_visible` لكل متجر؛ شارة الخطة وزر «الفواتير» وبطاقة الاستهلاك (فرعا المالك/المستخدم العادي) وسطر خطة المالك لكل متجر كلها داخل الشرط. **قطع التسريب في السيريالايز:** عند `!canViewBilling` تُفرَّغ `subscription`/`maxStores`/`isUnlimited` وتُصفَّر `plan_name`/`plan_status` في خريطة المتاجر.
   - `components/layouts/panel.blade.php` — شريط انتهاء الاشتراك مشروط بـ`hasStoreContext() && canViewStoreBilling(currentStore())` فيبقى المالك في السياق كالسابق والموظف لا يراه إطلاقًا.
   - قائمة `account.stores` — `StoreCardData::billingVisible` + `GetStoreCardsAction` يمرّرها، بلاطة الخطة تحت `@if ($store['billing_visible'])` وخانة `planName` تُفرَّغ (``) عند عدم الإظهار لكسر تسريب `wire:snapshot` («Trial»).
4. **`tests/Feature/Merchant/BillingVisibilityScopingTest.php` (6 اختبارات/26 تأكيد):** ① helper يمنح رؤية أخرى ② choose-store لموظف بلا صلاحية: لا يرى خطة/استهلاك/فواتير مع `assertSet('canViewBilling', false)` و`stores.0.billing_visible=false` ③ choose-store للمالك: يرى الخطة والاستهلاك ④ عضو مُصرَّح صراحةً بالصلاحية: يرى الخطة ⑤ البانر: المالك بلا اشتراك يراه، الموظف لا — مع حذف اشتراك التجربة التلقائي (كل مستخدم جديد يأخذ واحدة عبر `User::booted`) ⑥ بلاطة الخطة في قائمة المتاجر للمالك فقط.
5. **أسباب إصلاحات أثناء التنفيذ:** «Billing» في فحص `assertDontSee('Billing')` تصادم اسم المتجر الثابت «Billing Visibility Store» (أُعيدت تسميته «Visibility Store»)؛ البانر لم يظهر للمالك بسبب اشتراك التجربة التلقائي (`hasActive=true`)؛ «Trial» تسرّب عبر `wire:snapshot` رغم إخفاء الوسم.
6. **الشهادة:** `php -l` نظيف على كافة الملفات المعدّلة؛ **`tests/Feature/Merchant` كاملة = 620 ناجح (2644 تأكيد)** (614 سابقة + 6)؛ `tests/Feature/Account` + `tests/Feature/Auth` = 42 ناجح؛ grep صفري لـ`SubscriptionAlertService|subscription-alerts`؛ `view:clear` قبل كل تشغيل. لا تسليم بدون طلب صريح، وملاحظة (اختياري) للمستخدم: تحقق بصري من `choose-store` و`account/stores` بحساب موظف.

---

## Phase 36.6 — مرجع توثيقي: `ROLES_PERMISSIONS.md` (2026-09-21)

**توثيق محض — صفر تغيير كود.** أُنشئ `ROLES_PERMISSIONS.md` في جذر المستودع: مرجع التصميم المعاد لأدوار/صلاحيات المتجر — النموذج الحالي (47 صلاحية مجمّعة بـ`group()` + 4 أدوار) والمصفوفة المستهدفة (Manager `-5`، Staff `-3`، Owner/Admin ثابتان)، قرار إزالة `CRM_ORDER_CONFIRMATION` (مع **مصدرين جديدين** اكتشفهما التحقق الحي: `DemoStoreSeeder.php:498/:522` و`PermissionGroupMeta.php:97`)، فجوات الترجمة المؤكدة (order.assign / order.dispatch_validate / order.delete.final غائبة، team.view مكسورة)، طلبات الوصف للـ Hub (order.manage = 97 إشارة، store.billing.manage)، معاملة (Soon) (قرار المستخدم بتاريخ 2026-09-21: «شارة قريبًا» — مع تصحيح: `returns.*` حيّتان وظيفيًا)، ومؤجّلات خارج النطاق (`ORDER_ASSIGN` بلا نطاق؛ ازدواجية إزالة العضو `canModifyMember`/Policy)، وتسريب `account/billing.blade.php` (إصلاح مقترح: `storesOwned()`). الطبقات الثلاث مفصولة في القسم 10 من المستند.

---

## Phase 36.7 — تنفيذ الطبقة A من `ROLES_PERMISSIONS.md` (2026-09-21)

1. **مصفوفة `StoreRoles.php`:** Manager `-5` (أسقط `ORDER_CONFIRM` و`CRM_ORDER_TRACKING` و`PRODUCT_DELETE` و`DELIVERY_PRICING_MANAGE` — تبقى فقط `CRM_INVENTORY_*` في كتلة CRM) أي 31→26؛ Staff `-3` (أسقط `ORDER_CANCEL` و`CRM_ORDER_TRACKING` و`crm.orders.confirm`) أي 10→7 (تأكيد فقط). Owner/Admin ثابتان.
2. **إزالة `CRM_ORDER_CONFIRMATION` نهائيًا (قائمة تحقق القسم 4، files أولًا ثم Enum):** `StoreRoles.php` (مدير + موظف)، `dashboard.blade.php:16` (انحسار OR إلى `ORDER_CONFIRM` فقط)، `DemoStoreSeeder.php:498/:522`، `PermissionGroupMeta.php:97` (من `DEPENDENCIES`)، المفتاح المتداخل `crm.orders.confirm` في `permissions.php×4`، ثم حذف الحالة من `StorePermissionEnum.php:82`. **grep صفري** في `app/`+`resources/`+`database/`+`tests/` (بقيت فقط تأكيدات عدم الوجود في الاختبار الجديد).
3. **فجوات الترجمة الأربع (`permissions.php×4`):** أُضيف `order.assign` وإغاثة `order.dispatch_validate`؛ أُعيد هيكلة `order.delete` إلى `['label'=>.., 'final'=>..]` (مرآة صياغة `store.delete.final`) و`team.view` إلى `['own'=>.., 'label'=>..]`؛ وأُضيف فرع معالجة المصفوفات إلى `PermissionGroupMeta::label()` (فلطحة `label`/`own`) — بلا مستهلكين خارج الـ Hub لتفادي كسر النصي المباشر.
4. **أوصاف الـ Hub:** ملفات جديدة `permissions_descriptions.php×4` (order.manage + store.billing.manage)، `PermissionGroupMeta::description()`، وعرض الوصف تحت تسمية الصف في `permission-group-modal.blade.php` (مع `description` في صفوف `activeGroupMeta`).
5. **خصوصية `account/billing.blade.php:71`:** استُبدل `$u->stores()` (كل العضويات النشطة أي دور) بـ`$u->storesOwned()->with('payments')` — عضو بلا `store.billing.manage` لا يرى بلاطة متجرٍ لا يملكه.
6. **صلاحيات (Soon):** `PermissionGroupMeta::COMING_SOON = ['accounting.confirm.team']` + `isComingSoon()` + guard في `togglePermission` + صف مكتوم/خانة معطّلة + شارة `teams.soon_badge×4` («قريبًا»/«Coming soon»/«Bientôt»/«Próximamente»)؛ أُزيلت تعليقات (Soon) القديمة (`// soon` على `ACCOUNTING_CONFIRM_TEAM` ورأس «Verification / Returns (Soon)») لأن `returns.*` حيّتان وظيفيًا.
7. **الاختبارات:** جديد `tests/Feature/Merchant/StoreRolesTemplateTest.php` (8: قالب المدير −5، قالب الموظف −3، غياب الحالة من الـ Enum، حلّ مفاتيح الصلاحيات الأربعة إلى نصوص في اللغات الأربع، وعنقود منع `order.delete` المتداخل من كسر إخوته)؛ جديد «billing page lists only stores the user owns» في `BillingTest.php`؛ تحديث **مسند** `TrackingBulkActionsTest` (المديران يحصلان الآن على منحة `CRM_ORDER_TRACKING` الصريحة ليبقيا مرشّحَي إعادة توزيع) وتحديث تعليق الانحدار في `StoreAuthorizationGatesTest`.
8. **الشهادة:** `php -l` نظيف على كافة الملفات (21 ملف PHP/جزء Blade)؛ `view:clear` قبل كل تشغيل؛ `tests/Feature/Merchant` كاملة = **628 ناجح (2711 تأكيد)** (620 + 8 الجدد)؛ `tests/Feature/Account` + `Auth` = **43 ناجح (112 تأكيد)** (42 + 1)؛ سطّر `Implemented: 2026-09-21` في نهاية `ROLES_PERMISSIONS.md`. — ملاحظات اختيارية: «dangerous_badge/dangerous_hint/requires/group_select_all/group_clear» غير موجودة في `teams.php×4` (بقايا مرحلة 36.1 تُعرض كمفاتيح خام في الـ Hub — خارج نطاق 36.7).

---

## Phase 36.8 — توحيد توست SweetAlert2 مع هوية المشروع (أيقونات + موضع) ✅ (2026-09-21)

**طلب المستخدم (معتمد «نفذ» بعد عرض الخطة + قراران عبر أداة السؤال):** الأيقونة ومكانها في التوست غير متناسقين مع الهوية المعتمدة. القراران: **(1) الموضع = أسفل الزاوية** (bottom-start في RTL / bottom-end في LTR، مطابق لتوستات المتجر الأصلية `edz-notice`/`cartToast` في الأسفل)، **(2) قناة `swal:toast` كل أنواعها (نجاح/خطأ/تحذير/معلومة) تُعرض توستًا مدمجًا** لا مودالًا.

**التشخيص الجذري:** `swal.js` كان يمرر `iconHtml: undefined` فأيقونات SweetAlert2 الافتراضية (هندسة مبنية لحاوية 5em≈80px مسجلة في `sweetalert2.css:647-801`) تُعرض داخل شارة مقلّصة 32–40px → علامات مكسورة/مزاحة؛ `swal:toast` (199 موضع بث) يعرض كل غير-success مودالًا وسطًا بزر OK رغم اسمها؛ وحشوة التوست `padding:0` → التصاق المحتوى بالحافة؛ `[dir=rtl] .swal2-icon {margin:0!important}` كان يكسر توسيط أيقونة المودال.

1. **`resources/js/swal.js`:** `EDZ_SWAL_ICONS` — خريطة 5 أيقونات SVG مضمّن بمسارات **مطابقة حرفيًا لـ`x-edz.icon`** (check-circle/x-circle/exclamation-triangle/info-circle/help-circle، `stroke="currentColor"`, عرض خط 1.5) تُحقن عبر `iconHtml: rest.iconHtml ?? EDZ_SWAL_ICONS[t]`؛ `toastPosition()` → `bottom-start`/`bottom-end`؛ **جديدة `EdzSwal.toast(options)`** (toast:true دائمًا، position الأسفل، timer 3500 / خطأ 5000، بلا أزرار)؛ معالج `swal:toast` → `EdzSwal.toast` و`failed-validation` → `EdzSwal.toast({type:'error'})`؛ `timerProgressBar`/`showConfirmButton` صارا يُحترمان استجابةً لـ`rest.*` (كانا مقسومين بقيمة ثابتة فيفسدان الثوابت). قناة `swal` (81 موضع بث) بلا تغيير (خطأ/سؤال = مودال وسط، نجاح = توست).
2. **CSS بالتوازي في `resources/css/components/_swal.scss` و`resources/css/app.css`:** الشارة قرص مطفأ بلا حدود (بدل تأطير 2px) — 48px في المودال / 32px في التوست، `rounded-full`، `flex` توسيط، `svg 1.5rem`/`1.125rem`، ألوان per-type light (`#dcfce7/#16a34a`, `#fee2e2/#dc2626`, `#fef3c7/#d97706`, `#dbeafe/#2563eb`, `#e0e7ff/#6366f1`) وdark (ألفا 900/35 مع نص 400-scale)؛ التوست `padding:0.75rem 1rem` + `gap:0.75rem` + `max-width:360px` (وعند ≤639px `calc(100vw - 1.5rem)`) + العنوان/النص `margin:0` + `min-width:0` + `max-height:40vh; overflow-y:auto` للقوائم النصية (توست `syncAllTracking`); شريط المدة بنصف قطر الكبسولة؛ حُذفت قواعد ميتة (تصغير `success-line`, خدعة `[dir=rtl] .swal2-icon`, مُحدد `swal2-timerprogress-bar` الوهمي) واستُبدل `margin:0!important` بـ`margin:0 auto` لتوسيط أيقونة المودال أفقيًا.
3. **الشهادة:** `npm run build` ناجح (تحذيرات Sass)؛ فحص `public/build` مباشرة (الملفات المبنية app-BIaEA5q4.css للوحة + app-iEjkz8ke.css للمتجر): `swal2-icon svg {1.5rem/1.125rem}` حاضرة، toast base `padding:.75rem 1rem;gap:.75rem;align-items:center`، `max-width:calc(100vw - 1.5rem)`، timer `border-radius:0 0 .875rem .875rem`، dark `background:#14532d59` (=rgba(20,83,45,.35))، وصفر من `swal2-success-line`/`swal2-timerprogress-bar`/`border-width:1.5px` (بقيت .border-[1.5px] الخاصة بـTailwind فقط). شريحة `swal-*.js`: الأيقونات الخمس (علامات `9 12.75 11.25 15 15 9.75`, `9.303`, `1.063.852`, `9.879 7.519`) + `bottom-start`. عدّاد البث دون تغيير (`swal`=81، `swal:toast`=199) — اختبارات Feature تفحص payloads فقط.
4. **يتطلب تحققًا بصريًا يدويًا من المستخدم (لا يمكن عبر CLI):** 375px/768px/1440px — توست نجاح أسفل الزاوية بشعار دائري أخضر مطفأ، توست خطأ (تحقق فضلي + قائمة html) يبقى كبسولة بلا أزرار، توست warning/بإبطال مكتب بتوست، مودال تأكيد (question) أيقونته مركزة بشعار indigo، والوضع الداكن في لوحتين اللوحة والمتجر.

---

## Phase 38 — الأداء: فصل حزم Landing/Guest (المرحلة 1) ✅ (2026-09-22)

**الهدف المعتمد:** إزالة ~1.9MB من الجافاسكربت المنقول على صفحات الهبوط والدخول وضغط CSS، عبر حذف الحزم القديمة وإبقاء خطوط الأيقونات حية على المتجر فقط.

**الحقائق المؤكدة بفحص فعل:** `landing-layout:30` و`guest:23` كانا يحملان `app.js` (1,168KB: axios+swal+iconify+ApexCharts+flatpickr+FullCalendar+lucide+edzDirty) و`guest:23` إضافة إلى `panel.js` (241KB مكوّنات اللوحة الـ16 بلا استخدام). grep صفري لاستخدام ApexCharts/flatpickr/FullCalendar/lucide/axios في blades الصفحات العامة، ولا `#chartOne/#mapOne/#calendar` في أي view، prism بلا استخدام، FA/bi مستخدمان فقط في storefront (`product-detail:199/257/522`, `single-product:123`, `catalog:198`, `order-form:652/659` عبر `IconManager::render`)، `edzDirty` مسجّل مكررًا (app.js:17+panel.js:301) و`confirmLeave` بلا استدعاء.

**ما نُفِّذ:**
1. **`vite.config.js`** — إضافة `guest.js`+`landing.js`؛ حذف `app.js` (أصبح ميتًا — آخر مرجعاته التعليقات/التوثيق).
2. **`resources/js/guest.js`** (1KB) — `edzDirty` فقط.
3. **`resources/js/landing.js`** (38.8KB + swal 83.5KB مشترك) — swal+iconify+AOS بنفس خيارات `AOS.init` السابقة.
4. **`resources/js/storefront.js`** — استيراد FA + bootstrap-icons (خطوط الأيقونات تخرج chunk CSS تلقائي `storefront-dqBiEyH7.css` 163KB). **درس معماري:** مدخل CSS بـ `@import "pkg/css"` فشل في Vite على ويندوز ("Unclosed string") — الحل عبر استيراد JS.
5. **`resources/css/app.css`** — حذف @importات الأسطر 1-5 (خطوط×2 بلوكية، prism، FA، bi).
6. **`resources/css/base/_typography.scss:7`** — حذف @import الخطوط (كان يحوي وزن `590` غير صالح).
7. **`components/edz/fonts.blade.php`** (جديد) — خطوط غير بلوكية (preconnect+`media="print" onload`). **درس Blade:** `wght@{{ }}` تُطبع حرفيًا لأن `@{{` escape → أُصلح ببناء URL عبر `@php` ثم `{{ $fontsUrl }}`.
8. **layouts:** guest:23 → `app.css+guest.js+edz-loader` (حذف panel.js)؛ landing:30 → `landing.js`؛ app.blade:23 (ميت) → `landing.js`؛ panel→`<x-edz.fonts weights="400;500;600;700" />`؛ storefront دون تغيير مراجع + fonts عبر partial.
9. **`package.json`** — حذف `prismjs`.

**الأثر المقيس (public/build، `npm run build` ناجح):** Guest: JS ~1950KB→~4KB، CSS 292→128KB. Landing: JS ~1291KB→~125KB. Storefront: FA/bi في chunk مستقل بنفس الحجم تقريبًا + خطوط async. فحص MStestal: `/` (landing) 200 يحمل landing.js لا app.js/panel.js؛ `/login` 200 يحمل guest.js فقط؛ storefront عبر Host `default-store.edzeery.com` 200 يحمل icons chunk+swal+fonts async. `app-DxMIUNTW.css` بلا `.fa-`/`.bi`/`@import url(`. public/build بلا أصول stale.

**يتطلب تحققًا بصريًا يدويًا (قاعدة المستخدم #2):** 375/768/1440 — landing (AOS + iconify + ionicons)، login/register/create-store (edzDirty + شارات heroicon)، صفحة منتج storefront (شارات `bi-*` مثل `bi-star-fill` وأيقونات order-form). إزالة الخطوط البلوكية تحسّن LCP وقد تُحدث FOIT طفيف مؤقت — راجعها بصريًا.

**مقترحات المرحلة القادمة (لم تُلمس):** استبدال `set="fa"|"bi"` في storefront بـ heroicon/ion (—~380KB خطوط أيقونات من critical path)، تحويل html5-qrcode (366.7KB مع كل صفحة لوحة) إلى dynamic-import، إسقاط axios من storefront.js، flatpickr حسب الطلب.
---

## Phase 39 - إصلاحات الفحص البصري: Alpine + الأيقونات + التوحيد اللوني + السبينر (2026-09-22)

**الهدف:** إصلاح ما أبلغ عنه المستخدم بعد الفحص البصري: (1) Alpine معطّل في landing (FAQ collapse، تبديل billing، قائمة الموبايل، فورم التواصل - وافق: تثبيت collapse)، (2) أيقونات ناقصة في بعض الأزرار، (3) ألوان غير متناسقة dark/light من كلاسات غير معرّفة، (4) توحيد سبينر الحمل على كل أزرار login/create-store/landing.

**اكتشاف جذري:** pp.js القديمة لم تستورد Alpine أصلًا (bootstrap.js فيها axios فقط) وlanding-layout بلا Livewire/CDN => x-data كانت معطلة سابقًا أيضًا؛ الحل في landing.js.

**تم تنفيذه:**
1. **
pm install @alpinejs/collapse@^3.15.6** + esources/js/landing.js: استيراد Alpine + collapse + Alpine.start() + initNativeButtonLoading().
2. **esources/js/native-button-loading.js** (جديد): سبينر على النماذج النطبية orm:not([x-data]) وروابط [data-edz-loading] (فلتر flicker 150ms + .edz-btn--loading/__ring/__hide). مُستورد في guest.js وlanding.js (chunk مشترك ~1.9KB فقط).
3. **_buttons.scss**: قواعد عامة .edz-btn__ring/.edz-spinner خارج .edz-btn.
4. **الأيقونات**: plans CTA (rrow-forward-outline + data-edz-loading)، hero register/dashboard + final-cta register/contact (data-edz-loading)، navbar logout ×2 (log-out-outline + inline-flex gap)، choose-store upgrade (rrow-up + data-edz-loading) وcreate-new (data-edz-loading).
5. **سبينر التواصل**: استبدال SVG اليدوي بـ .edz-spinner في landing/contact.blade.php.
6. **توحيد الألوان (27 ملفًا + يدوي):** تسوية كل كلاسات الـ tokens (g-surface*/	ext-ink*/order-surface-border) بلا بادئة dark: (tokens تتبدل تلقائيًا تحت .dark) في Landing+sections وauth/* وcomponents (auth/layouts/header/ecommerce/dropdown/nav-link/responsive-nav-link/secondary-button/sidebar-link/text-input/edz) وfooter + بقايا storefront خارج النطاق (store-settings:455 + label-print-modal). توليد خريطة استبدال كاملة ({light dark:}→token ثم الفردية)، **النتيجة: صفر كلاسات غير معرّفة** في esources/views.
7. تنظيف: lang-switcher (indigo→brand، توكن الألوان)، dark-toggle (توكن)، choose-store usage-bar، footer (gray→tokens).

**النتائج/التحقق (public/build بعد 
pm run build):** landing.js 87KB (يحتوي Alpine+start+collapse) ✓، guest.js ~1KB + shared native-button-loading 1.9KB ✓، pp-DAlgERXa.css يحتوي .edz-btn__ring+.edz-spinner ✓. فحص ميداني: / (landing) 200، /login 200، storefront (Host default-store.edzeery.com) 200.

**بقي للتحقق بصريًا (375/768/1440):** landing (collapse، تبديل billing، قائمة الموبايل المنزلقة، أيقونات، ألوان dark/light، سبينر الأزرار) + login/register/create-store (سبينر + ألوان) + storefront (خارج نطاق التوحيد اللوني - خلفياته الرمادية معرّفة ورسمية).
**إصلاح لاحق (استكمال Phase 39):**
- الكونسول أظهر أخطاء Alpine على اللاندينغ: edzLoader is not defined + overlay is not defined + label is not defined عند Alpine.start().
- **السبب:** <x-edz.global-loader> (مستدعى في landing-layout/guest/app) يستخدم x-data="edzLoader()"، والمكوّن كان يُسجّل عبر evento lpine:init في حزمة منفصلة edz-loader.js — لاحقًا بعد تشغيل landing.js لألباين (سياق تسجيل الـ listener لا يلحق ببدء Alpine). قبل Phase 38 لم يكن ألباين يعمل على اللاندينغ أصلًا فبقيت معطلةً بصمت.
- **الحل:** landing.js تستورد الآن ./components/edz-loader.js وتستدعي Alpine.data("edzLoader", edzLoader) قبل Alpine.start() (تأكيد بالحزمة المبنية: .data("edzLoader",...);Ce.start();). edz-loader.js يبقى للتسجيل في اللوحة/storefront (ألباين من Livewire) وللـ CSS، والتسجيل مكرر idempotent.
- النتيجة: boot overlay للعالمية يتحرر الآن على اللاندينغ (BOOT_MIN_MS + fonts) والأخطاء تختفي؛ القياسات: landing.js 85.2KB، والفحص الميداني /, /login, /register, storefront = 200.
**مراجعة نهائية (استكمال Phase 39):**
- مراجعة git diff لكل الملفات المعدلة: المسح اللوني وadd icon والسبينر سليمة؛ لا تغييرات دلالية خاطئة.
- تنظيف كلاسات مكررة نتجت عن المسح (استبدال أزواج متقاطعة على أسطر): hero (border-surface-border ×2)، footer (hover ×2)، user-dropdown (text-ink-muted ×2)، stores-metrics (bg ×2)، landing-layout body.
- تصحيح landing-layout body إلى g-surface-bg text-ink (نمط guest) بدل g-surface-bg bg-surface المزدوج.
- التحقق النهائي: 
pm run build ناجح (landing.js 85.2KB، guest.js 1.1KB، native-button-loading 1.9KB، edz-loader 2.8KB)؛ فحص الصفحات: /, /login, /register, /forgot-password, /contact-us, storefront = 200 (المسار الصحيح للتواصل /contact-us وليس /contact).
**إصلاح RTL (استكمال Phase 39):** landing/sections/how-it-works.blade.php:58 — الخطوط المتقطعة بين المراحل كانت left-[60%] w-[80%] (اتجاه فيزيائي ثابت): تعمل في LTR، لكن في RTL عند كل خطوة تشير يمينًا بينما التدفق من اليمين لليسار (step01 لا يعرف خطًا يمتد خارج الحاوية). الحل: start-[60%] (inset-inline-start) — في LTR = left:60% (نفس الشكل تمامًا)، وفي RTL = ight:60% فيشير الخط نحو الخطوة التالية يسارًا. مؤكَّد في CSS المبني: .start-\[60\%\]{inset-inline-start:60%}. الفحص: / و/?lang=ar = 200.
**تكملة إصلاحات RTL (استكمال Phase 39):**
- how-it-works.blade.php:64: شارة رقم الخطوة -right-2 -> -end-2 (تنعكس للزاوية المعكوسة في RTL).
- hero.blade.php:116: شارة النمو العائمة -left-4 -> -start-4 (تنتقل للزاوية المعكوسة في RTL).
- فحص شامل: لا space-x-* في landing/auth/layouts (المشروع يستخدم gap-* المتوافق مع الاتجاه)؛ guest.blade.php:36-37 فقاعات خلفية متماثلة تُترك؛ storefront:171 ml-3 فاصل فقط ويبقى خارج النطاق.
- مؤكَّد في CSS المبني: inset-inline-start:-1rem (من -start-4) وinset-inline-end:-.5rem (من -end-2). الفحص: LTR/RTL = 200.
**تحسين الأداء P1-P4 (Phase 40):**
- **P1** pp/Helpers/Language_Translation.php: إضافة ctiveLanguages() بكاش static لكل طلب؛ getLanguages/getLanguageCodes/getLanguageNames تبني منه. أثبت الاختبار: 3 استدعاءات = استعلام واحد (كان 3+)، والرابع = صفر. يُلغي مئات استعلامات languages من View::composer('*').
- **P2** pp/Helpers/helpers.php: currentMembership() يقرأ ربط pp('currentMembership') من EnsureStoreMembership قبل أي استعلام (مطابقة store_id+user_id فقط). canStore() بكاش لكل طلب keyed بـ user id + permission (تحقق super-admin مرة، membership/permissions مرة). pp/Models/Stores/Team/StoreMembership.php: permissionNames() memoized لكل instance (أثبت الاختبار: دعوتان = استعلام واحد).
- **P3** pp/Domains/Order/Services/OrderDuplicateService.php::countsBySiblings: أُعيدت كاستعلامات مُجمعة/محدودة (targets فقط + COUNT مجمع + overlap عبر join bounded بالعملاء) بدل سحب كامل حمولة 30 يوم + items للبيئة. مخرجات مطابقة تمامًا للخوارزمية القديمة (تحقق بمقارنة على بيانات فعلية: 5/5 صفوف متطابقة) مع تصحيح استثناء الذات فقط (وليس كل الصفحة).
- **P4** pp/Domains/Order/Services/OrderService.php::availableTransitions: كاش Static للبحث عن status الفرعي لكل store+key (يسقط استعلامًا لكل صف). pp/Domains/Order/Services/OrderCompleteness.php::storeReadyForDispatch: كاش static لكل store id (يسقط استعلامي exists لكل صف confirmed/preparing). صفحة الطلبات: getCurrentMembership() وisibleTo(currentMembership()) بدل الاستعلام المتكرر.
- التحقق: php -l سليم لكل الملفات (7/7)، tinker أثبت تقليل الاستعلامات، iew:clear نظيف، / و/login = 200 وorders/tracking = 302 → login (المتوقع دون جلسة). لا تغييرات JS/CSS → لا build.
**تحسين الأداء P5-P8 (مواصلة Phase 40) — يستهدف ثقل تنقل wire:navigate:**
- **P5** esources/views/livewire/merchant/tracking/index.blade.php: فلتر المنتجات في mount مقصور على آخر 180 يوم + حد 2000 (بدل فحص كامل التاريخ عبر join order_items)، وناقلون على نافذة سنة. pp/Livewire/Concerns/TrackingColumnConcern.php::getMembership() يستخدم currentMembership() (الربط من middleware) بدل استعلام membership في كل baseTrackingQuery (كان ~6-10 استعلامات/صفحة).
- **P6** pp/Livewire/Concerns/TrackingGridConcern.php::loadTrackingStats: عدادات الصفحة (active/delivered_today/returned_today) + عدّاد "اعتماد لدى الناقل" في **استعلام SUM واحد** بدل 4 فحوص EXISTS منفصلة (مع الحفاظ على دلالة latestOfMany عبر subquery created_at=MAX). حصيلة السائقين من **تجميع واحد** (count+sum+distinct → groupBy) بدل 4 استعلامات. DeliveryRiderService::listForStore بكاش لكل طلب (كان يُستدعى 2-3 مرات مع withCount). إجمالي فحوص صفحة التتبع من ~12 إلى ~6.
- **P7** pp/Livewire/Concerns/DistributionQueueConcern.php: صفوف الطابور محدودة بـ 500 مع عدّادات COUNT دقيقة منفصلة. esources/views/livewire/merchant/returns/index.blade.php: تبويبات المرتجعات تُرشَّح في SQL (3 عدّادات سريعة + سطر الصفحة النشطة فقط) مع تقسيم صفحات 25/صفحة بدل تحميل كل تاريخ المرتجعات.
- **P8** pp/Domains/Analytics/Services/StoreDashboardAnalyticsService.php: خريطة status_id→key تُحمَّل مرة/طلب (كان ~5 استعلامات statuses لكل رسم داشبورد).
- **P3 إصلاح انحدار** pp/Domains/Order/Services/OrderDuplicateService.php: أُضيف whereNull(o.deleted_at) لاستعلام تداخل المنتجات (Query Builder يجتاز SoftDeletes — أظهره OrderDuplicateBadgeTest::soft-deleted siblings excluded).
- التحقق: php -l نظيف لكل الملفات؛ view:cache يجمّع كل القوالب؛ اختبار ميزة مؤقت (تم حذفه) رفعت 4 صفحات كبيرة بـ 200؛ المجموعات القائمة خضراء: Tracking + Search + BulkValidate + Duplicates (15) + Queue + Returns + OrdersPageQueryCount (بلا N+1).
