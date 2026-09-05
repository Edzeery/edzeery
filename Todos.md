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
- **قرارات معلّقة (من الخطة §9):** 5 أسئلة تنتظر إجابات المستخدم.

### جولة Phase 29 (الطلبيات) — فرع 29.1 ✅ (يتبع فرع تلو فرع بموافقة المستخدم)

بدأت جولة Phase 29 (29.1–29.7) على `orders/index.blade.php`. **الفرع الأول (29.1) اكتمل** — إتاحة سجل أحداث الطلبية بالدور:
- `StoreOrderPermissions::canViewOrderEventLog(Order, StoreMembership)` — OWNER/ADMIN دائمًا، MANAGER للمعيَّن لعضويته فقط، STAFF أبدًا. حارس موحّد على مودال التفاصيل + درج التتبع.
- تجميع يومي + وقت + فاعل + شارة دور عبر mystatuskit؛ مفاتيح `event_day_today`/`event_day_yesterday` ×4 لغات.
- `tests/Feature/Merchant/OrderEventLogVisibilityTest.php` — **8 ناجحة (20 assertions)**. السويت كاملة **297 ناجح (1080 assertions)** (الوحيد المتأثر بلوك ملفات Windows عاد 11/11 منفردًا → صفر انحدار). التفاصيل: `OrdersrefactorplanFixed.md` Phase 29.1 + `OrdersOperationsPlan.md` §10.
