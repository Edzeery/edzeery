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
