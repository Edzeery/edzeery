# Todos.md — ÎØÉ ÊäİíĞ Edzeery ÇáßÇãáÉ

> äÊíÌÉ ÊÏŞíŞ ßÇãá ááÑíÈæ ÇáÍÇáí (Edzeery/edzeery). ßá ÈäÏ ÃÏäÇå ãÈäí Úáì İÍÕ İÚáí ááßæÏ¡
> æáíÓ ÇİÊÑÇÖğÇ. äİøĞ ÇáÃŞÓÇã ÈÊÑÊíÈåÇº ßá ŞÓã ŞÇÈá ááÕŞ ãÈÇÔÑÉ ßÈÑæãÊ ãÓÊŞá İí Claude Code.
> ÇÚÑÖ äÊíÌÉ ßá ŞÓã ßÇãáÉ ŞÈá ÇáÇäÊŞÇá ááÊÇáí¡ æÍÏøË åĞÇ Çáãáİ (?/?) ÚäÏ ÅäÌÇÒ ßá ÈäÏ.

---

## ÇáŞÓã 0 — äÊÇÆÌ ÇáÊÏŞíŞ (ÓíÇŞ¡ áíÓ Úãá ÊäİíĞí)

- Livewire ãØÈøŞ İÚáíğÇ Úáì **ÌãíÚ** ãæÇÑÏ ÇáÊÇÌÑ ÇáÜ 9 (Products, Brands, Categories, ProductOptions, ProductOptionValues, ProductVariants, Inventories, InventoryMovements, StockAlerts, MyTeams). ÍõĞİ `app/Filament/Merchant` ÈÇáßÇãá — Filament áÇ íæÌÏ ÇáÂä ÅáÇ ááÅÏÇÑÉ ÇáÚÇãÉ (`SuperAdminPanelProvider`).
- `MerchantPanelProvider` (Filament¡ `path('merchant')`) áÇ íÒÇá ãÓÌøáÇğ æäÔØğÇ **ÈÇáÊæÇÒí** ãÚ `routes/merchant.php` ÇáÌÏíÏ (`Route::prefix('merchant')`) — äİÓ ÇáÈÇÏÆÉ¡ äÙÇãÇä ãÎÊáİÇä íÚãáÇä İí äİÓ ÇáæŞÊ ÊÍÊ `/merchant/*`
- íæÌÏ äÙÇãÇä ãÕãøãÇä ááÃáæÇä íÚãáÇä ÈÇáÊæÇÒí İí ÇáßæÏ:
  - **ÕÍíÍ æãÊøÕá ÈÇáÜ design tokens**: `bg-surface-bg`, `text-ink` — ãÓÊÚãá İŞØ İí `components/layouts/panel.blade.php`, `components/layouts/merchant.blade.php`, æßá `resources/views/livewire/merchant/*`
  - **ÛíÑ ãÚÑóøİ ÅØáÇŞğÇ İí `tailwind.config.js`** (ßáÇÓÇÊ áÇ ÊõäÊÌ Ãí CSS İÚáí¡ Ãí ÈÏæä Ãí ÎáİíÉ/áæä äÕ ÍŞíŞí İí ÇáæÖÚíä): `bg-neutral-bg`, `dark:bg-dark-bg`, `text-neutral-text`, `dark:text-dark-text` — ãÓÊÚãá İí ~23 ãáİğÇ ÊÔãá: ÇáÕİÍÉ ÇáÑÆíÓíÉ ÈÇáßÇãá (`landing/*`)¡ `layouts/app.blade.php`, `landing-layout.blade.php`, `navbar`, `guest`, `footer`, ßá ãßæäÇÊ ÇáİæÑã ÇáÚÇãÉ (`text-input`, `input-label`, `secondary-button`...)¡ æäÓÎÉ ŞÏíãÉ ãßÑøÑÉ ãä áæÍÉ ÇáÊÇÌÑ (`components/merchant/body.blade.php` æ`sidebar.blade.php`) áã ÊõÍĞİ ÈÚÏ äŞá ÇáÊÇÌÑ ÇáÍŞíŞí Åáì `livewire/merchant/layout/*`
- ÇáÕİÍÉ ÇáÑÆíÓíÉ (`landing/index.blade.php`) ÃÓÇÓíÉ ÌÏğÇ: hero (24 ÓØÑ)¡ services (38 ÓØÑ)¡ payments (32 ÓØÑ)¡ plans (144 ÓØÑ) — æíæÌÏ ãáİ ãßÑÑ ÒÇÆÏ `plans.blade copy.php` íÌÈ ÍĞİå
- ? ÚäŞæÏ ÇáÏíæä æÇáÅÔÚÇÑÇÊ ãä `Finance-Manager` — Êã äŞáå Åáì `app/Domains/Finance/*` ãÚ `store_id` + `mystatuskit` + ÊÑÌãÇÊ 4 áÛÇÊ.
- ãáİÇä İÇÑÛÇä ÛÑíÈÇä İí ÌĞÑ ÇáÑíÈæ: `it` æ`prepareBindings($bindings)`
- `package.json`: ãİÊÇÍ `@tailwindcss/forms` ãßÑÑ ãÑÊíä ÈŞíãÊíä ãÎÊáİÊíä
- `routes/web.php`: ~11 ãÓÇÑ Ïíãæ ãÊÈŞò ãä ŞÇáÈ TailAdmin (calendar/buttons/badges/ui-elements)

---

## ÇáŞÓã 1 — ÊæÍíÏ äÙÇã ÇáÃáæÇä (ÃæáæíÉ ŞÕæì¡ íãäÚ Ãí Úãá ÊÕãíãí áÇÍŞ ãä ÇáÊÑÇßã Úáì ÃÓÇÓ ÎÇØÆ)

ÇÓÊÈÏá İí ßá ÇáãáİÇÊ ÇáÊÇáíÉ ßá ßáÇÓ ãä ÇáÚãæÏ ÇáÃíÓÑ ÈãŞÇÈáå İí ÇáÚãæÏ ÇáÃíãä (ÇáÇÓÊÈÏÇá íÔãá ÇáæÖÚ ÇáİÇÊÍ ÊáŞÇÆíğÇ áÃä `--edz-color-bg`/`--edz-color-text` íÊÛíøÑÇä ÃÕáÇğ ÊÍÊ `.dark`¡ áĞáß **áÇ ÍÇÌÉ áÈÇÏÆÉ `dark:` ãäİÕáÉ ãÚ åĞå ÇáÊæßäÒ**):

| ÇáŞÏíã (ÛíÑ ãÚÑóøİ) | ÇáÌÏíÏ (ãÊÕá ÈÇáÜ tokens) |
|---|---|
| `bg-neutral-bg dark:bg-dark-bg` | `bg-surface-bg` |
| `text-neutral-text dark:text-dark-text` | `text-ink` |

**ÇáãáİÇÊ ÇáãÊÃËÑÉ:** ? (24 ãáİğÇ: ÇáÜ 22 ÇáãĞßæÑÉ ÃÏäÇå + `components/merchant/body.blade.php` æ `components/merchant/sidebar.blade.php` ÇáÊí ÃõÈŞíóÊ áÃä `x-merchant.body` áÇ íÒÇá ãÓÊÎÏãğÇ İÚáíğÇ İí `merchant/billing/index.blade.php`)

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

**ÈÚÏ ÇáÇÓÊÈÏÇá¡ ÇÍĞİ ãßæäÇÊ áæÍÉ ÇáÊÇÌÑ ÇáŞÏíãÉ ÇáãÓÊÈÏóáÉ İÚáíğÇ ÈäÙÇã Livewire ÇáÌÏíÏ (ÊÍŞŞ ÃæáÇğ Ãä áÇ ÔíÁ íÓÊÏÚíåÇ):**
```bash
grep -rln "x-merchant.body\|x-merchant.sidebar\b" resources/views routes app --include=*.php
# Åä ßÇäÊ ÇáäÊíÌÉ İÇÑÛÉ Ãæ ÊÔíÑ İŞØ áãáİÇÊ ŞÏíãÉ ÛíÑ ãÓÊÚãáÉ:
git rm resources/views/components/merchant/body.blade.php
git rm resources/views/components/merchant/sidebar.blade.php
```
? **ãÍÌæÈ ãÄŞÊğÇ** — ÇáİÍÕ ÃÙåÑ Ãä `x-merchant.body` ãÇ ÒÇá ãÓÊÎÏãğÇ İÚáíğÇ İí `resources/views/merchant/billing/index.blade.php` (íõŞÏóøã ÚÈÑ `BillingController` ? `view('merchant.billing.index')`). ÇáãßæøäÇä ÃõÈŞíÇ æØõÈøŞ ÚáíåãÇ ÇÓÊÈÏÇá ÇáÃáæÇä. íÌÈ ÍĞİåãÇ ÈÚÏ äŞá ÕİÍÉ Billing/ÇáİæÇÊíÑ Åáì Livewire.

? ÇÍĞİ ÃíÖğÇ `resources/views/landing/sections/plans.blade copy.php` (äÓÎÉ ãßÑÑÉ ÒÇÆÏÉ). — Êã ÇáÍĞİ¡ ãÚ ÍĞİ `resources/views/components/ecommerce/stores-metrics.blade copy.php` ÇáãßÑÑ ÇáããÇËá.

---

## ÇáŞÓã 2 — ÍÓã ÊÚÇÑÖ ÇáãÓÇÑ Èíä Filament æ Livewire Úáì `/merchant/*`

`MerchantPanelProvider` (Filament) æ`routes/merchant.php` (Livewire) íÊÔÇÑßÇä äİÓ ÇáÈÇÏÆÉ `merchant` ÍÇáíğÇ. ØÈøŞ ÇáÍá ÇáÊÇáí: ÃÈŞö Filament íÎÏã İŞØ ÇáãæÇÑÏ ÇáÊí áã ÊõäŞá ÈÚÏ¡ ÚÈÑ ÊÖííŞ ãÓÇÑå áíßæä `merchant/legacy` ÈÏá `merchant`:

```php
// app/Providers/Filament/MerchantPanelProvider.php
->path('merchant/legacy')   // ÈÏá ->path('merchant')
```

åĞÇ íİÕá İÚáíğÇ ãÓÇÑÇÊ Filament ÇáãÊÈŞíÉ (`/merchant/legacy/products`...) Úä ãÓÇÑÇÊ Livewire ÇáÌÏíÏÉ (`/merchant/{store:slug}/products`)¡ æíáÛí Ãí ÊÖÇÑÈ Ãæ ÇáÊÈÇÓ ÃËäÇÁ ÇáÇäÊŞÇá ÇáÊÏÑíÌí. ÃÖİ ÑÇÈØ ãÄŞÊ İí ÇáŞÇÆãÉ ÇáÌÇäÈíÉ áÃí ãæÑÏ áã íõäŞá ÈÚÏ íæÌøå áãÓÇÑå ÊÍÊ `legacy`.

? **Êã** — `->path('merchant/legacy')` İí `MerchantPanelProvider`¡ æÃõÖíİÊ ÑæÇÈØ Legacy İí ÇáŞÇÆãÉ ÇáÌÇäÈíÉ Livewire (`livewire/merchant/layout/sidebar.blade.php`) áßá ãæÑÏ áã íõäŞá ÈÚÏ (Brands, Categories, Product Options, Product Variants, Inventories, Stock Alerts, My Teams) ÚÈÑ `route('filament.merchant...', $store)`. ÍÏøË ÇáÇÎÊÈÇÑ ÇáãÊÃËÑ (`SplitLoginTest` ? `/merchant/legacy`). ßá ÇáãÑÇÌÚ ÇáÃÎÑì ÊÓÊÎÏã `route()`/`Filament::getPanel()->getUrl()` İÊÊÍÏË ÊáŞÇÆíğÇ. ÇáÇÎÊÈÇÑÇÊ: 39 äÇÌÍ.

> ãáÇÍÙÉ: íæÌÏ ÊßÑÇÑ áÎÏãÉ `LoginRedirectService` (äÓÎÉ `app/Domains/User/Services/` ÛíÑ ãÓÊÚãáÉ + äÓÎÉ `app/Services/Auth/` ÇáãÓÊÚãáÉ) — ãÑÔÍÉ ááÊäÙíİ İí ÇáŞÓã 6.

---

## ÇáŞÓã 3 — ÅßãÇá äŞá ãæÇÑÏ ÇáÊÇÌÑ ÇáãÊÈŞíÉ Åáì Livewire (ÈÇáÊÑÊíÈ)

ÇÊÈÚ äİÓ äãØ `products/{index,form,show}.blade.php` + `routes/merchant.php` + ÈÇÏÌÇÊ `<x-merchant.status>` ÇáĞí ØõÈøŞ ÈäÌÇÍ Úáì Products. áßá ãæÑÏ ÃÏäÇå: ÇäŞáå¡ ÊÍŞŞ ãä ÇáÊßÇİÄ ÇáæÙíİí ãÚ äÓÎÉ Filament¡ Ëã ÇÍĞİ äÓÎÊå ãä `app/Filament/Merchant/Resources/*` æ`MerchantPanelProvider`.

1. ? **Brands** — ãÓÊŞá¡ ÈÓíØ¡ ÇÈÏÃ Èå
2. ? **Categories** — ãÓÊŞá
3. ? **ProductOptions** + **ProductOptionValues** — íÚÊãÏÇä Úáì Products (ãäÌÒ)
4. ? **ProductVariants** — íÚÊãÏ Úáì Products + ProductOptions (ãäÌÒ)
5. ? **Inventories** + **InventoryMovements** + **StockAlerts** — íÚÊãÏæä Úáì Products/Variants (ãäÌÒ)
6. ? **MyTeams** — ãÓÊŞá¡ íãßä ÊäİíĞå ÈÇáÊæÇÒí ãÚ Ãí ãÑÍáÉ

ÈÚÏ ÇßÊãÇá ÂÎÑ ãæÑÏ: ÇÍĞİ `app/Filament/Merchant` ÈÇáßÇãá æ`MerchantPanelProvider.php` æÊÓÌíáå İí `bootstrap/providers.php`. ? Êã ÇáÍĞİ ÈÇáßÇãá.

---

## ÇáŞÓã 4 — ÊØæíÑ ÇáÕİÍÉ ÇáÑÆíÓíÉ

ÃÚÏ ÈäÇÁ `resources/views/landing/index.blade.php` æÃŞÓÇãå ßÕİÍÉ ÊÓæíŞíÉ ÇÍÊÑÇİíÉ ßÇãáÉ (Bootstrap+SCSS ÍÓÈ ÇáŞÑÇÑ ÇáãÚãÇÑí¡ ÈÚÏ ÊØÈíŞ ÇáŞÓã 1 áÖãÇä ÏÚã ÇáæÖÚ ÇáÏÇßä ÈÔßá ÕÍíÍ ãä ÇáÈÏÇíÉ). ÇáÃŞÓÇã ÇáãØáæÈÉ ÈÇáÊÑÊíÈ:

1. **Hero** — ÚäæÇä ÑÆíÓí æÇÖÍ áåæíÉ Edzeery (ãäÕÉ SaaS ãÊßÇãáÉ ÚÑÈíÉ ÇáåæíÉ)¡ ÚÈÇÑÉ İÑÚíÉ¡ CTA ãÒÏæÌ (ÇÈÏÃ ÇáÂä / ÔÇåÏ ÇáÚÑÖ)¡ ÕæÑÉ/ÑÓã ÊæÖíÍí ááãäÊÌ
2. **ÇáÔÚÇÑ ÇáÇÌÊãÇÚí** (Social proof) — ÔÚÇÑÇÊ ÚãáÇÁ/ÅÍÕÇÆíÇÊ (ÚÏÏ ÇáãÊÇÌÑ¡ ÇáØáÈÇÊ ÇáãõÚÇáÌÉ...) Åä ÊæİÑÊ ÈíÇäÇÊ¡ æÅáÇ placeholder æÇÖÍ
3. **ÇáãíÒÇÊ ÇáÃÓÇÓíÉ** (services.blade.php ÇáÍÇáí ßäæÇÉ¡ æÓøÚå) — ÈØÇŞÇÊ áßá äÙÇã İÑÚí: ãäÔÆ ÕİÍÇÊ ÇáåÈæØ¡ ÇáãÊÌÑ ÇáÅáßÊÑæäí¡ CRM¡ ERP¡ HR æÌÏæáÉ ÇáÚãá¡ ãÍÇÓÈÉ ÇáÚãÇá — ßá ÈØÇŞÉ ÃíŞæäÉ + ÚäæÇä + æÕİ ÓØÑíä
4. **ßíİ íÚãá** — 3-4 ÎØæÇÊ ÊæÖÍ ÑÍáÉ ÇáÊÇÌÑ ãä ÇáÊÓÌíá Åáì ÇáÅØáÇŞ
5. **ÇáÎØØ æÇáÃÓÚÇÑ** (plans.blade.php ÇáÍÇáí — äÙøİå¡ ÇÍĞİ ÇáäÓÎÉ ÇáãßÑÑÉ¡ ÇÑÈØå ÈÜ `App\Domains\Plan\Services` ÇáİÚáíÉ ÈÏá Ãí ÈíÇäÇÊ ËÇÈÊÉ Åä æõÌÏÊ)
6. **ÈæÇÈÇÊ ÇáÏİÚ ÇáãÏÚæãÉ** (payments.blade.php ÇáÍÇáí ßäæÇÉ)
7. **ÇáÃÓÆáÉ ÇáÔÇÆÚÉ**
8. **CTA ÎÊÇãí** + Footer ßÇãá (ÑæÇÈØ¡ ÊæÇÕá ÇÌÊãÇÚí¡ áÛÉ)

ßá ŞÓã ãßæøä Blade ãäİÕá ÊÍÊ `resources/views/landing/sections/`¡ RTL ÇİÊÑÇÖí¡ æÏÚã Dark mode ßÇãá ÚÈÑ tokens (ÇáŞÓã 1).

?

---

## ÇáŞÓã 5 — ÚäŞæÏ ÇáãÍÇÓÈÉ (Finance-Manager) — ? Êã

Êã äŞá ÚäŞæÏ ÇáÏíæä ÈÇáßÇãá ãä `github.com/Edzeery/Finance-Manager` Åáì `app/Domains/Finance/*` ãÚ ßá ÇáÊßííİÇÊ:

**ÇáãáİÇÊ ÇáãõäÔÃÉ/ÇáãõÚÏóøáÉ:**
- `app/Enums/Finance/DebtTypeEnum.php` — `owed`/`owing` ãÚ `InteractsWithStatusKit`
- `app/Enums/Finance/DebtStatusEnum.php` — `active`/`partial`/`paid`/`overdue` ãÚ `InteractsWithStatusKit`
- `app/Models/Finance/Debt.php` — äãæĞÌ ãÚ `store_id` + `StoreScope` + SoftDeletes
- `app/Models/Finance/DebtPayment.php` — äãæĞÌ ãÚ `store_id` + `StoreScope`
- `app/Observers/Finance/DebtPaymentObserver.php` — ãÒÇãäÉ ÍÇáÉ ÇáÏíä ÚäÏ ÅÖÇİÉ/ÊÚÏíá/ÍĞİ ÇáÏİÚÇÊ
- `app/Domains/Finance/Services/DebtSettlementService.php` — ÍÓÇÈ ÇáÊÓæíÉ
- `app/Domains/Finance/Services/DebtNotificationService.php` — ÅÔÚÇÑÇÊ ÇáÇÓÊÍŞÇŞ
- `app/Enums/Store/StorePermissionEnum.php` — 4 ÃĞæäÇÊ: `FINANCE_DEBT_VIEW/CREATE/UPDATE/DELETE`
- `app/Providers/AppServiceProvider.php` — ÊÓÌíá `DebtPaymentObserver`
- `database/migrations/2026_08_17_120000_create_debts_table.php` — ÌÏæá `debts` ãÚ `store_id` (UUID)
- `database/migrations/2026_08_17_120001_create_debt_payments_table.php` — ÌÏæá `debt_payments` ãÚ `store_id` (UUID)
- `routes/merchant.php` — 4 ãÓÇÑÇÊ Volt: index/create/edit/show
- `resources/views/livewire/merchant/debts/index.blade.php` — ÕİÍÉ ÇáŞÇÆãÉ ãÚ İáÊÑÉ æãáÎÕ
- `resources/views/livewire/merchant/debts/show.blade.php` — ÕİÍÉ ÇáÊİÇÕíá ãÚ ÅÖÇİÉ ÇáÏİÚÇÊ
- `resources/views/livewire/merchant/debts/form.blade.php` — äãæĞÌ ÅäÔÇÁ/ÊÚÏíá
- `resources/views/livewire/merchant/layout/sidebar.blade.php` — ÑÇÈØ Ïíæä İí ÇáŞÇÆãÉ ÇáÌÇäÈíÉ
- `resources/views/components/edz/icon.blade.php` — ÃíŞæäÇÊ ÌÏíÏÉ: `credit-card`, `check-circle`, `x-mark`, `plus`, `trash`
- `resources/lang/{en,ar,es,fr}/finance.php` — ÊÑÌãÇÊ ßÇãáÉ (35 ãİÊÇÍğÇ × 4 áÛÇÊ)

**ÇáÊßíİÇÊ ÇáÑÆíÓíÉ:**
- `store_id` (UUID) ÈÏá `workspace_id`
- `StoreScope` ÈÏá `WorkspaceScope`
- `currentStoreId()` ÈÏá `config('app.current_workspace')`
- `<x-merchant.status>` ãÚ `mystatuskit` ÈÏá `kitBadge()` ãÈÇÔÑÉ
- `config/statuses.php` íÍÊæí `debt` æ`debt_type` ÈÇáİÚá

?

---

## ÇáŞÓã 6 — ÊäÙíİ ÚÇã (ãäÎİÖ ÇáÎØæÑÉ¡ íäİóøĞ ÈÇáÊæÇÒí ãÚ Ãí ŞÓã ÂÎÑ)

```bash
git rm "it" "prepareBindings(\$bindings)"
```
- ÃÒá ÇáÊßÑÇÑ İí `package.json` áãİÊÇÍ `@tailwindcss/forms` (ÃÈŞö ÇáŞíãÉ ÇáÃÍÏË İŞØ)
- ÃÒá ãä `routes/web.php` ÇáãÓÇÑÇÊ ÇáãÊÈŞíÉ ãä ŞÇáÈ TailAdmin ÇáÊÌÑíÈí (calendar, ui-elements, buttons, badges) ÛíÑ ÇáãÓÊÚãáÉ İÚáíğÇ İí ÇáãäÊÌ

?

---

## ÊÑÊíÈ ÇáÊäİíĞ ÇáãæÕì Èå

ÇáŞÓã 1 (ÃáæÇä) ? ÇáŞÓã 2 (ÍÓã ÊÚÇÑÖ ÇáãÓÇÑ) ? ÇáŞÓã 3 (ÅßãÇá Livewire) ? ÇáŞÓã 4 (ÇáÕİÍÉ ÇáÑÆíÓíÉ) ? ? ÇáŞÓã 5 (ÇáãÍÇÓÈÉ) ? ? ÇáŞÓã 6 (ÊäÙíİ) ?

---

## ÍÇáÉ ÇáÊäİíĞ ÇáÍÇáíÉ

- ? **ÇáŞÓã 1**: ÇÓÊÈÏÇá ÇáÃáæÇä İí 24 ãáİğÇ + ÍĞİ äÓÎÊí `*.blade copy.php`. ÇáãßæøäÇä `body`/`sidebar` ÃõÈŞíÇ (ãÍÌæÈ ÍÊì äŞá Billing Åáì Livewire).
- ? **ÇáŞÓã 2**: áæÍÉ Filament ááÊÇÌÑ Úáì `/merchant/legacy` + ÑæÇÈØ Legacy İí ÇáŞÇÆãÉ ÇáÌÇäÈíÉ + ÊÍÏíË ÇáÇÎÊÈÇÑ. ÍõĞİ ÈÇáßÇãá ãÚ ÈÇŞí Filament Merchant.
- ? **ÇáŞÓã 3**: Brands + Categories + ProductOptions/ProductOptionValues + ProductVariants + Inventories/InventoryMovements/StockAlerts + **MyTeams** — ÌãíÚåÇ ãäŞæáÉ Åáì Livewire Volt. ÍõĞİ `app/Filament/Merchant` ÈÇáßÇãá + `MerchantPanelProvider` + `TeamController`. ÇáİíáÇãäÊ áã íÚÏ ãæÌæÏğÇ İí ØÈŞÉ ÇáÊÇÌÑ.
- ? **ÇáŞÓã 4**: ÅÚÇÏÉ ÈäÇÁ ÇáÕİÍÉ ÇáÑÆíÓíÉ ÈÇáßÇãá — Hero ÈÜ CTA ãÒÏæÌ + mock dashboard¡ Social Proof (ÈíÇäÇÊ ÍíÉ ãä ŞÇÚÏÉ ÇáÈíÇäÇÊ)¡ Services ÈÜ 6 ÈØÇŞÇÊ ãíÒÇÊ¡ How It Works ÈÜ 4 ÎØæÇÊ¡ Plans/ÃÓÚÇÑ ÈÜ Tailwind ÈÏá Filament¡ Payments ÈÜ ãÚáæãÇÊ ÅÖÇİíÉ¡ FAQ ÈÜ 6 ÃÓÆáÉ¡ CTA ÎÊÇãí¡ Footer ãÍÏøË (4 ÃÚãÏÉ + ÊæÇÕá ÇÌÊãÇÚí). ÊÑÌãÇÊ ßÇãáÉ (en/ar/fr/es). ÅÒÇáÉ ÌãíÚ ãßæäÇÊ Filament ãä ÇáÕİÍÉ ÇáÑÆíÓíÉ.
- ? **ÇáŞÓã 5**: ÚäŞæÏ ÇáÏíæä ÈÇáßÇãá — Enums + Models + Observer + Services + Permissions + Migrations + Volt views + Routes + Sidebar + Translations (4 áÛÇÊ). ÇáÏíæä ÇáÂä ãæÕæáÉ ÈÜ `store_id` æ `mystatuskit`.
- ? **ÇáŞÓã 6**: Êã ÈÇáßÇãá — ÍõĞİ ßæÏ ÇáÏíæ ÇáÊÌÑíÈí (~17 ÕİÍÉ + 16 ãßæøä + 17 ãÓÇÑ)¡ ÍõĞİ ãáİíä İÇÑÛíä ÛÑíÈíä (`it`¡ `prepareBindings($bindings)`) ãä ÌĞÑ ÇáÑíÈæ¡ ÃõÕáÍÊ ÊßÑÇÑÇÊ `package.json` (`@tailwindcss/forms` æ`alpinejs`). ÑÇÌÚ `DESIGN_SYSTEM.md`.

---

## ÊäÙíİ ßæÏ ÇáÏíæ ÇáÊÌÑíÈí ãä TailAdmin (ÃÛÓØÓ 2026)

? **Êã** — ÍõĞİ ÈÇáßÇãá:

**17 ãÓÇÑ ãä routes/web.php:** `/welcome`, `/calendar`, `/pages/profile`, `/form-elements`, `/basic-tables`, `/blank`, `/error-404`, `/line-chart`, `/bar-chart`, `/signin`, `/signup`, `/alerts`, `/avatars`, `/badge`, `/buttons`, `/image`, `/videos`

**17 ÕİÍÉ ÚÑÖ (resources/views/pages/):** `dashboard/ecommerce`, `calender`, `profile`, `form/form-elements`, `tables/basic-tables`, `blank`, `errors/error-404`, `chart/line-chart`, `chart/bar-chart`, `auth/signin`, `auth/signup`, `ui-elements/{alerts,avatars,badges,buttons,images,videos}` + ãÌáÏ `pages/` äİÓå

**16 ãßæøä ãíøÊ:** `calender-area`, `ui/{button,alert,badge,avatar,youtube-embed}`, `common/common-grid-shape`, `form/form-elements/{default-inputs,select-inputs,text-area-inputs,input-states,input-group,file-input-example,checkbox-component,radio-buttons,toggle-switch,dropzone}`

**ãáİíä İÇÑÛíä ãä ÌĞÑ ÇáÑíÈæ:** `it`, `prepareBindings($bindings)`

**ÅÕáÇÍ package.json:** ÅÒÇáÉ ÊßÑÇÑ `@tailwindcss/forms` (ÃõÈŞöí `^0.5.11` İí devDependencies İŞØ)¡ ÅÒÇáÉ ÊßÑÇÑ `alpinejs` (ÃõÍÏøË Åáì `^3.15.6` İí devDependencies İŞØ)

**ÃõÈŞí Úáíå** (ãÓÊÎÏã İÚáíğÇ): `common/{page-breadcrumb,component-card,dropdown-menu,table-dropdown,preloader}`, `ui/modal`, `profile/*`, `ecommerce/*`, `layouts/*`

**ãáÇÍÙÇÊ:**
- `/signin` æ`/signup` ÃõÒíáÊÇ áÃäåãÇ íõßÑøÑÇä ÊÏİŞ Breeze ÇáÍŞíŞí İí `routes/auth.php`
- `/error-404` ÃõÒíáÊ áÃäåÇ áíÓÊ ÕİÍÉ ÇáÎØÃ ÇáÍŞíŞíÉ İí Laravel (ÇáãÓÇÑ ÇáÕÍíÍ åæ `resources/views/errors/404.blade.php`¡ æ`bootstrap/app.php` áÇ íÚÑøİ handler ãÎÕÕ)
- `resources/views/pages/errors/error-404.blade.php` ßÇäÊ İí ãÓÇÑ ÎÇØÆ æÛíÑ ãÓÊÎÏãÉ ßÕİÍÉ ÎØÃ ÍŞíŞíÉ
- ãßæøäÇÊ `ecommerce/*` ÃõÈŞíÊ áÃäåÇ ãÓÊÎÏãÉ ÍíğøÇ İí `merchant/dashboard.blade.php` æ`merchant/stores/index.blade.php`
- ãßæøä `ui/modal` ÃõÈŞí áÃäå ãÓÊÎÏã İí `profile/*` ÇáãõÓÊÎÏãÉ ÍíğøÇ İí `merchant/account/index.blade.php`

? **ÊæËíŞ äãØ ÅÚÇÏÉ ÇÓÊÎÏÇã TailAdmin** — ÃõäÔÆ `DESIGN_SYSTEM.md` ÈÊæËíŞ ßÇãá ááŞæÇÚÏ: ÇáÇİÊÑÇÖ åæ ãßæäÇÊ TailAdmin¡ ßá ãßæøä íÌÈ Ãä íÓÊÎÏã ÊæßäÇÊ `--edz-*`¡ ÃáæÇä ÇáÍÇáÇÊ ÊãÑ ÚÈÑ `mystatuskit`.

? **ÇßÊãÇá ÇáÊÑÌãÇÊ (i18n)** — Êã İÍÕ ÔÇãá æÓÏø ÇáİÌæÇÊ:

**ãáİÇÊ ãİŞæÏÉ ÃõäÔÆÊ:**
- `ar/profile.php`, `fr/profile.php`, `es/profile.php` — ãİÊÇÍ `country`
- `fr/stores.php`, `es/stores.php` — 14 ãİÊÇÍ (ÍÇáÉ ÇáãÊÌÑ¡ ÇáÑÓÇÆá¡ ÇáÊÑŞí)
- `fr/productoption.php`, `es/productoption.php` — 4 ãİÇÊíÍ (select, radio, checkbox, text)

**ãİÇÊíÍ ãİŞæÏÉ İí titles.php ÃõÖíİÊ:**
- `ar/titles.php`: +23 ãİÊÇÍ (brand, brands, expire_at, inventory_*, product_*, stock_*, unit, variants, ÅáÎ)
- `fr/titles.php`: +27 ãİÊÇÍ (äİÓ ÇáãİÇÊíÍ + numbers_agents, member, memberships)
- `es/titles.php`: +27 ãİÊÇÍ (äİÓ ÇáãİÇÊíÍ)

**lang-switcher**: ãæÌæÏ İí ßáÇ ÇáãÎØøØíä — `layouts/app-header.blade.php` (ãÎØøØ ÇáÊÇÌÑ) æ`components/layouts/navbar.blade.php` (ÇáÕİÍÉ ÇáÑÆíÓíÉ/ÇáãÊÌÑ).

**ãáÇÍÙÉ:** ãİÇÊíÍ `titles.php` ÛíÑ ãÑÊÈÉ ÃÈÌÏíğÇ ÈÚÏ ÇáÅÖÇİÉ (Êã ÅÏÑÇÌåÇ ÈÚÏ ÂÎÑ ãİÊÇÍ ãæÌæÏ). íãßä ÊäÙíãåÇ áÇÍŞğÇ ÅĞÇ áÒã ÇáÃãÑ.

---

## ÍÇáÇÊ ÇáÊÊÈøÚ æÍÑßÉ ÇáãÎÒæä (P7–P9) — ? Êã (ÃÛÓØÓ 2026)

### P7 — ÃäæÇÚ ÍÑßÉ ãÎÒæä `loss`/`damage`
- `app/Enums/Store/InventoryMovementType.php`: ÃõÖíİ `LOSS='loss'` æ`DAMAGE='damage'` — ßáÇåãÇ `isDecrease()` + `affectsStock()` + `direction() = -1` + `isManual()`.
- `config/status-kit-statuses.php`: `inventorymovementtype.loss` (danger `#dc2626`¡ ÃíŞæäÉ `loss`) æ`.damage` (warning `#facc15`¡ ÃíŞæäÉ `damage`) ÈÚÏ `release`.
- `config/status-kit-icons.php`: ãİÇÊíÍ `loss/damage/returning/failed_attempt/lost/damaged` İí ÇáãÌãæÚÇÊ ÇáÃÑÈÚ (fa/bi/ion/heroicon).
- ÊÑÌãÇÊ `inventorymovementtype` ÈÇááÛÇÊ ÇáÃÑÈÚ (ar/en/fr/es) + ÅÕáÇÍ ãİÊÇÍ `sale` ÇáãİŞæÏ İí ar.
- **ÇÎÊÈÇÑ:** `tests/Feature/InventoryMovementTypeTest.php`.

### P8 — äØÇŞ ÍÇáÇÊ ÇáÊÊÈÚ `tracking` (ãÓÊŞá Úä order)
- `app/Enums/Store/OrderTrackingStatus.php` (ÌÏíÏ): 9 ÍÇáÇÊ `shipped ? delivered / returned / lost / damaged` + `open()/terminal()/isOpen()/isTerminal()` + `fromCarrier(?string)` áÎÑíØÉ ÇáäÕ ÇáÎÇã áÔÑßÇÊ ÇáÔÍä (íÈÚøÏ ÇáİÌæÇÊ).
- `config/status-kit-statuses.php`: äØÇŞ `tracking` (ßÇãáğÇ) + ÅßãÇá ãİÇÊíÍ order ÇáäÇŞÕÉ (`wrong_number, undeliverable, unclaimed, no_answer_1/2/3, postponed, duplicate, out_of_stock`).
- ÊÑÌãÇÊ äØÇŞ `tracking` ÈÇááÛÇÊ ÇáÃÑÈÚ.
- **ÇÎÊÈÇÑ:** `tests/Feature/OrderTrackingStatusTest.php`.

### P9 — ÑÈØ `tracking_status` ÈÓÌá order_trackings
- ãíÛÑíÔä `2026_08_30_000001_add_tracking_status_to_order_trackings_table.php`: ÚãæÏ `tracking_status` nullable ÈÚÏ `carrier_label` + İåÑÓ `[store_id, tracking_status]`.
- `app/Models/Orders/OrderTracking.php`: `tracking_status` İí fillable + `trackingStatus(): ?OrderTrackingStatus`.
- `app/Domains/Order/Services/OrderTrackingService.php`: `startShipment`?SHIPPED¡ `markDelivered`?DELIVERED¡ `markReturned`?RETURNED¡ + `markReturning/markLost/markDamaged/markFailedAttempt`¡ `currentTracking`/`currentOpenTracking` (ÓÌá ãİÊæÍ İÇáÃÍÏËº áÇ íäÈÔ ÓÌáÇğ ãÛáŞğÇ). `OrderObserver::syncTracking` áã íÊÛíøÑ.
- **ÇÎÊÈÇÑ:** `tests/Feature/Merchant/OrderTrackingTest.php` (8 ÍÇáÇÊ ŞÇÆãÉ + ÌÏíÏÊÇ `tracking_status` æterminal helpers).

### ÃİÖáíøÉ label ÇáäÙÇãí + ÇáÊÌåíÒ ÇáÇİÊÑÇÖí
- `app/Domains/Status/Support/ResolvedStatus.php::fromModel`: ÇáÕİæİ ÇáäÙÇãíÉ (`is_system` æÈÏæä `store_id`) ÊÚÑÖ ÊÑÌãÉ `status-kit` Åä æõÌÏÊ æíÊÑÇÌÚä áÜ label ÇáÕİ Åä áã ÊæÌÏ — ÔİÇİíÉ i18n ááÛÇÊ ÇáÃÑÈÚ ÍÊì ãÚ æÌæÏ Õİæİ DB ÈÇáÅäáíÒíÉ. Õİæİ ÇáãÊÌÑ (ÇáãÎÕÕÉ) ÊÈŞì ãä DB.
- `database/seeders/SystemStatusesSeeder.php`: ÃõÖíİÊ Õİæİ ÇİÊÑÇÖíÉ — `tracking` ×9¡ `inventory` ×3 (`in_stock/low_stock/out_of_stock`)¡ `inventorymovementtype` ×8 (ÈãÇ İíåÇ `loss` danger æ`damage` warning)¡ æÅßãÇá order ÈÜ`unclaimed`/`undeliverable`. ÈáÇ `icon`/`display_mode` (ãÊæÇİŞ ãÚ ŞÇÚÏÉ dev ÈÏæä ÕİæİåãÇ).
- **ÇÎÊÈÇÑ:** `tests/Feature/StatusLabelPrecedenceTest.php` (ÃİÖáíøÉ ÇáÊÑÌãÉ + ÊÎÒíä ÇáãÊÌÑ + ÚÏøÇÏ seed).

### P10 (ãÓÊŞÈáí — ãÍÇÓÈíÉ ÇáÅÊáÇİ)
- İÌæÉ ãÍÇÓÈíÉ ãÑÕæÏÉ: ÚäÏ äØŞ äÊíÌÉ İÍÕ ÇáÅÑÌÇÚ `DAMAGED/PARTIAL/LOST`¡ ÍÑßÉ `RETURN` ÊÑÌÚ ÇáßãíÉ ßÇãáÉ ÓÌáğøÇ¡ æáÇ íæÌÏ ÔØÈ ááßãíÉ ÇáÊÇáİÉ/ÇáãİŞæÏÉ. ÇáÎØÉ ÇáãÓÊŞÈáíÉ: ÚäÏ æŞÊ ÇáäØŞ ÅÕÏÇÑ ÍÑßÉ `LOSS`/`DAMAGE` ÚÈÑ `InventoryService::apply` áæÖÚ ÎÕã. äŞÇØ ÇáÇÊÕÇá: `OrderObserver::handleStatusChange` + `ReturnVerificationService`.

---

## ÚäŞæÏ ÔÍä ÇáØáÈÇÊ — ÇáÊÑÇÌÚ Úä ÇáÊÌÇæÒ ÇáíÏæí + «ÊæÕíá ãÌÇäí» (ÓÈÊãÈÑ 2026)

? **Êã** — ÇáãÑÇÌÚ: `OrdersrefactorplanFixed.md` **Phase 24** (ÇáÊİÇÕíá ÇáßÇãáÉ + ÇáÃÏáÉ ÇáŞÇÈáÉ ááŞíÇÓ).

- ÍõĞİ `order_shipping_costs` (ãíÛÑíÔä 000003 — ßÇäÊ `Pending` İí MySQL + ÇáäãæĞÌ + `Order::shippingCosts/latestShippingCost` + `OrderService::snapshotShippingCost`) — **`orders.shipping_cost` ÇáÂä ÇáãÕÏÑ ÇáæÍíÏ** áÊßáİÉ ÇáÔÍä¡ íõßÊÈ ÍÕÑíğÇ ãä `ShippingCostCalculator` (ÇáÅäÔÇÁ + recalc ÚäÏ wilaya/city)¡ **ÈáÇ ÊÍÑíÑ íÏæí** (ÍõĞİ `$saveOrderShippingCost`/`startOrderShippingCostEdit` + ÇáÍŞá ÇáÑŞãíº ÇáÚãæÏ read-only).
- **ÚÑÖ «ÊæÕíá ãÌÇäí» ÈÏá `0 ÏÌ`** ÚäÏ ÛíÇÈ ÃÓÚÇÑ ãÎÕøÕÉ (ŞÇÚÏÉ ãæÍøÏÉ: Ãí `shipping_cost <= 0` ? ÔÇÑÉ `shipping_free` İí ÚãæÏ ÇáÌÏæá æÈØÇŞÉ ÇáãæÈÇíá). ÈáÇ ÓßíãÇ ÌÏíÏÉ æÈáÇ ÇÓÊÚáÇãÇÊ ÅÖÇİíÉ.
- ÊÑÌãÇÊ `shipping_free` ×4 áÛÇÊº ÍõĞİ `manual_override`.
- **Ïáíá ÇáÅäÌÇÒ:** test suite ßÇãáÉ **285 äÇÌÍÉ (1056 assertions)** — ãäåÇ ÇÎÊÈÇÑÇÊ ØáÈÇÊ ÇáÊÇÌÑ 23 äÇÌÍÉ (109 assertions) ãÚ ÅÈŞÇÁ ÇÎÊÈÇÑ recalc ÇáãÏíäÉ (400.0), æÍĞİ ÇÎÊÈÇÑ override ÇáŞÏíã + `scratch_validator_test`. `view:cache` æ`php -l` ÓáíãÇä. **ÅÛáÇŞ L5** (»ShippingCostCalculator fallback») İí OrdersrefactorplanFixed.

---

## ÚäŞæÏ ÚãáíÇÊ ÇáØáÈíÇÊ — ÇáÊÃßíÏ ÇáÇÍÊÑÇİí/ÇáÊÊÈÚ/ÇáÓÌá/ÇáÊßÑÇÑ/ÇáÌãÇÚí (ÓÈÊãÈÑ 2026)

? **äõİøĞ** — ÇáãÑÌÚ ÇáßÇãá: `OrdersOperationsPlan.md` + Ïáíá ÇáÅäÌÇÒ ÃÏäÇå. ÌÏæá ÇáÊäİíĞ: **290 ÇÎÊÈÇÑğÇ äÇÌÍğÇ (1064 assertions)** — ãäåÇ 5 ÌÏíÏÉ İí `tests/Feature/Merchant/OrderDuplicateDetectionTest.php` æ8 İí `OrderTrackingTest` æ`OrderTrackingStatusTest` (äØÇŞ tracking) æ`v:cache`/`php -l` ÓáíãÇä æ`route:list` íÄßÏ `merchant.tracking.index`.

- **ÇáãÑÌÚ ÇáßÇãá:** `OrdersOperationsPlan.md` (ÇáÎØÉ + ÇáŞÑÇÑÇÊ + ÇáãÑÇÌÚ file:line + ÇáãÑÇÍá P25–P30).
- **ÇáãÍÇæÑ ÇáãäİøĞÉ:**
  1. **P25 ÇáÈäíÉ:** `OrderAuditService` + `OrderTrackingService` (ÓÌá ÇáÊÊÈÚ + ÇáÊÏŞíŞ Úáì ßá ÊÍæøá) + `OrderWorkflow` (ãÌãæÚÇÊ backOffice/carrier/closed) + `OrderObserver` (ÊÏŞíŞ ÅäÔÇÁ/ÊÚÏíá/ÍÇáÉ + ãÒÇãäÉ ÇáÊÊÈÚ) + ãæÏíáÇ `OrderEvent`/`OrderTrackingHistory` + 3 ãíÛÑíÔä (order_events¡ order_tracking_histories¡ İåÇÑÓ repeated-workflow).
  2. **P26 ÏÑÌ ÇáÊÃßíÏ** ? ÊÃßíÏ İŞØ / ÊÃßíÏ æÅÑÓÇá ÚÈÑ `OrderShippingGateway::send()` + ÇáÊÕİíÉ ÇáÇİÊÑÇÖíÉ ÊÓÊÈÚÏ ÍÇáÇÊ CARRIER ãä ÕİÍÉ ÇáØáÈíÇÊ.
  3. **P27 ÕİÍÉ «ÊÊÈÚ ÇáØáÈíÇÊ»** (`merchant/{store}/tracking`) — İáÇÊÑ + ÅÍÕÇÆíÇÊ + ÌÏæá/ÈØÇŞÇÊ + ÏÑÌ ØáÈíÉ (ÈØÇŞÉ ÔÑßÉ¡ ãáÎÕ¡ ÅÌÑÇÁÇÊ ÓÑíÚÉ¡ ÓÌá ÇáÊÊÈÚ¡ ÓÌá ÇáØáÈíÉ).
  4. **P28 ßÔİ ÇáÊßÑÇÑ** `OrderDuplicateService::findSimilar(Order|array $candidate)` (äÇİĞÉ 30 íæãğÇ¡ ÍÏ 5) + ÊÍĞíÑÇÊ İí äãæĞÌ ÇáÅäÔÇÁ/ÇáÊÚÏíá æÏÑÌ ÇáÊÃßíÏ + ÅÌÑÇÁ «æÖÚ ßØáÈíÉ ãßÑÑÉ».
  5. **P29 ÊÚÏíá ÍÇáÉ ÌãÇÚí** (ãÓÊÈÚÏ ÏÇÆãğÇ: `cancelled/canceled` + `confirmed`º ÛíÑ ÇáÕÇáÍÉ ÊõÊÎØøì æÊõÈáøÛ).
- **ŞÑÇÑÇÊ §9 (ÇáÎãÓÉ) — ßáåÇ ÍõáøÊ ÃËäÇÁ ÇáÊäİíĞ:** ÇáİÕá ØáÈíÇÊ/ÊÊÈÚ (CARRIER ÊõÓÊÈÚÏ ãä ÕİÍÉ ÇáØáÈíÇÊ æÊõÚÇáÌ İí ÇáÊÊÈÚ)º ÏÑÌ ÇáÊÃßíÏ «ÊÃßíÏ + ÅÑÓÇá» ÈáÇ ÚÏøÇÏ ãÍÇæáÇÊ ÇÊÕÇáº äÇİĞÉ ÇáÊßÑÇÑ 30 íæãğÇ ÛíÑ ãÇäÚÉ (æÊÕäíİ ËáÇËí 29.6)º ÇáÍÇáÇÊ ÇáÌãÇÚíÉ = ßá ÇáÍÇáÇÊ ÚÏÇ `cancelled/canceled/confirmed` ãÚ ÇáÊÎØøí æÇáÅÈáÇÛº ÇáÃæáæíÉ = «äİøĞ ßá ÔíÁ» (P25?P29 + ÌæáÉ 29.1–29.7).

### ÌæáÉ Phase 29 — İÑÚ 29.1 ? (íÊÈÚ İÑÚ Êáæ İÑÚ ÈãæÇİŞÉ ÇáãÓÊÎÏã)

ÈÏÃÊ ÌæáÉ Phase 29 (29.1–29.7) Úáì `orders/index.blade.php`. **ÇáİÑÚ ÇáÃæá (29.1) ÇßÊãá** — ÅÊÇÍÉ ÓÌá ÃÍÏÇË ÇáØáÈíÉ ÈÇáÏæÑ:
- `StoreOrderPermissions::canViewOrderEventLog(Order, StoreMembership)` — OWNER/ADMIN ÏÇÆãğÇ¡ MANAGER ááãÚíóøä áÚÖæíÊå İŞØ¡ STAFF ÃÈÏğÇ. ÍÇÑÓ ãæÍøÏ Úáì ãæÏÇá ÇáÊİÇÕíá + ÏÑÌ ÇáÊÊÈÚ.
- ÊÌãíÚ íæãí + æŞÊ + İÇÚá + ÔÇÑÉ ÏæÑ ÚÈÑ mystatuskitº ãİÇÊíÍ `event_day_today`/`event_day_yesterday` ×4 áÛÇÊ.
- `tests/Feature/Merchant/OrderEventLogVisibilityTest.php` — **8 äÇÌÍÉ (20 assertions)**. ÇáÓæíÊ ßÇãáÉ **297 äÇÌÍ (1080 assertions)** (ÇáæÍíÏ ÇáãÊÃËÑ Èáæß ãáİÇÊ Windows ÚÇÏ 11/11 ãäİÑÏğÇ ? ÕİÑ ÇäÍÏÇÑ). ÇáÊİÇÕíá: `OrdersrefactorplanFixed.md` Phase 29.1 + `OrdersOperationsPlan.md` §10.

### ÌæáÉ Phase 29 — İÑÚ 29.2 ? (ÅÑÓÇá ãÈÇÔÑ áØáÈ ãõÄßóøÏ)

ÇßÊãá ÇáİÑÚ ÇáËÇäí (29.2): ÅÌÑÇÁ `$sendConfirmedOrder(string $orderId)` — ÍÇÑÓ `order.manage` + ŞÈæá `confirmed/preparing` İŞØ (ÑİÖ ÛíÑåÇ ÈÊæÓÊ `send_requires_confirmation`¡ áÇ ÊÃßíÏ ÊáŞÇÆí ÃÈÏğÇ ÚÈÑ `confirmFirst: false`)¡ İÍÕ ÌåæÒíÉ (ÇÓã/åÇÊİ/æáÇíÉ/ÈáÏíÉ/ÚäæÇä-Ãæ-äŞØÉ ÇÓÊáÇã/?1 Õäİ/ÔÑßÉ-Ãæ-ãæÕøá) ãÚ ÅÏÑÇÌ ÇáäÇŞÕ İí ÊæÓÊ `send_missing_fields` æÊÈŞì ÇáÍÇáÉ ßãÇ åí¡ Ëã ÅÑÓÇá ÚÈÑ `OrderShippingGateway::send(..., confirmFirst: false)`. ÒÑ truck İí ÇáÌÏæá + ÈØÇŞÇÊ ÇáåÇÊİ áåÇÊíä ÇáÍÇáÊíä İŞØ. ãİÇÊíÍ ×4 áÛÇÊ. ÅÕáÇÍ ÎØÃ ßÇãä: `?int` ? `int|string|null` İí `OrderTrackingService` (ãÚÑøİÇÊ ÇáÚÖæíÇÊ ULID) ßÇä íÚØøá Ãí ÊÍæøá `shipped` ÈÚÖæíÉ. `DirectSendConfirmedOrderTest.php` — **7 äÇÌÍÉ (16 assertions)**º ÇáÓæíÊ **305 äÇÌÍ (1100 assertions)** ? ÕİÑ ÇäÍÏÇÑ.

### ÌæáÉ Phase 29 — İÑÚ 29.3 ? (ÅÑÓÇá ÌãÇÚí ãÌãóøÚ ÍÓÈ ÔÑßÉ ßá ØáÈíÉ)

ÇßÊãá ÇáİÑÚ ÇáËÇáË (29.3) æÃõÚíÏ ÇáÚãá Úáíå ÈãáÇÍÙÇÊ ÇáãÓÊÎÏã: ÇÓÊõÈÏá «ÅÑÓÇá Çáßá áÔÑßÉ æÇÍÏÉ» ÇáŞÏíã ÈäÇİĞÉ ÊÃßíÏ ÌãÇÚíÉ ÊõÕäøİ ßá ØáÈíÉ ŞÈá ÇáÅÑÓÇá:
- `$resolveBulkOrderState(Order)` — ãÕÏÑ ÇáÍŞíŞÉ ÇáæÍíÏ: äÇŞá ßá ØáÈíÉ (ÇÓã ÇáÔÑßÉ **Ãæ ÇÓã ÇáãæÕøá ÇáÍŞíŞí** ÚÈÑ `deliveryRider`¡ æÅáÇ «ÈáÇ ÔÑßÉ») + `ready` + `reasons[]` ÕÑíÍÉ: `bulk_send_reason_status` (ÊÍÊÇÌ ÊÃßíÏğÇ/ÊÌåíÒğÇ — ÇáÍÇáÉ ÇáÍÇáíÉ)¡ `bulk_send_reason_missing` (ßá ÇáÍŞæá ÇáäÇŞÕÉ)¡ `bulk_send_reason_no_carrier`.
- `$openBulkSendModal()` — ÌáÈ æÇÍÏ ÈÜ `with(['status','customer','shippingProvider','deliveryRider','items'])` (áÇ N+1)¡ íãáÃ `bulkSendAnalysis`/`bulkSendReadyCount`/`bulkSendSkipCount`¡ æÇáãáÎÕ ãä **ÇáÌÇåÒÉ İŞØ** İí `x-edz.modal`.
- ÇáãæÏÇá ŞÓãÇä: «ÓÊõÑÓá» (ãáÎÕ ÇáäÇŞáíä ÈÃÚÏÇÏåÇ) + ÊÍĞíÑ ÈÇÑÒ (`x-edz.alert type="warning"`) íÓÑÏ **ßá ØáÈíÉ ÛíÑ ÌÇåÒÉ ÈÑŞãåÇ æÓÈÈåÇ**º ÚäÏ ÚÏã æÌæÏ ÌÇåÒÉ ? ÒÑ ÇáËÃßíÏ ãÚØóøá (`bulk_send_confirm_none`)¡ æÅáÇ «ÅÑÓÇá :count ÇáÌÇåÒÉ İŞØ» (`bulk_send_confirm_some`).
- `$confirmBulkSend()` — íÚíÏ ÇáÌáÈ ØÇÒÌğÇ ÈäİÓ eager-loads æíõÚíÏ ÇáÍÓÇÈ ÈÇáãæáøÏ äİÓå¡ íÑÓá ÇáÌÇåÒÉ İŞØ ßáñø áäÇŞáåÇ ÇáİÚáí (`providerId: shipping_provider_id ?: null`¡ áÇ auto-confirm)¡ ÊæÓÊ æÇÍÏ ÕÑíÍ: ÓØæÑ ÇáÔÑßÇÊ + ÑÃÓ `bulk_send_skipped :count` + ÓØÑ áßá ØáÈíÉ `number (ÇáÓÈÈ)` (ÃíŞæäÉ ÊÍĞíÑ Åä æõÌÏ ÊÎØòø).
- `$sendConfirmedOrder` (29.2) íÚíÏ ÇáÂä ÇÓÊÎÏÇã `$collectMissingFields` äİÓå (ãÕÏÑ æÇÍÏ ááİÍÕ).
- ãİÇÊíÍ 16 ×4 áÛÇÊ: ãİÇÊíÍ 9 ÇáŞÇÆãÉ + 7 ÌÏíÏÉ: `bulk_send_ready_title`/`bulk_send_skipped_title`/`bulk_send_reason_status`/`bulk_send_reason_missing`/`bulk_send_reason_no_carrier`/`bulk_send_confirm_some`/`bulk_send_confirm_none`.
- `BulkSendCarrierGroupingTest.php` — **9 äÇÌÍÉ (48 assertions)** (ÊÔãá ÊÕäíİ ÇáãæÏÇá ãÚ ÇáÃÑŞÇã æÇáÃÓÈÇÈ¡ ÇáÊÓãíÉ ÇáÕÑíÍÉ ááãÊÎØóøì İí ÇáÊæÓÊ¡ ÍÇáÉ «áÇ ÔíÁ ÌÇåÒ»). ÇáÓæíÊ ßÇãáÉ **314 äÇÌÍ (1148 assertions)** ? ÕİÑ ÇäÍÏÇÑ. `view:cache` + `php -l` ÓáíãÇä.

### ÌæáÉ Phase 29 — İÑÚ 29.4 ? (ÚãæÏ ÍÇáÉ ÇáÊÊÈÚ + ÊæÍíÏ ÇáÎØ ÇáÒãäí)

ÇßÊãá ÇáİÑÚ ÇáÑÇÈÚ (29.4) — æÇÌåÉ ÎÇáÕÉ¡ ÕİÑ ÊÛííÑ İí ÇáÓáæß:
- ÚãæÏ «ÍÇáÉ ÇáÊÊÈÚ» ËÇäæí ŞÇÈá ááÊİÚíá (`group: workflow`, `default: false`) İí ÓÌá `$orderColumns()`º `tracking_status` ÃõÖíİ Åáì ÈÇäíø `$arr['tracking']` (ÇáÌÏæá İí `loadOrders` + fallback ÇáÏÑÌ İí `openOrderDetails`)º ÎáíÉ ÇáÌÏæá æÈØÇŞÉ ÇáãæÈÇíá ÊÚÑÖÇä ÔÇÑÉ mystatuskit ÈäØÇŞ `tracking` (äİÓ ÃäãÇØ ÕİÍÉ ÇáÊÊÈÚ) Ãæ `—`.
- ÊæÍíÏ ÓÌá ÃÍÏÇË ÇáØáÈíÉ: ÇáŞÓã ÇáãßÑóøÑ İí ÏÑÌ ÇáÊİÇÕíá (`orders/index`) æÏÑÌ ÇáÊÊÈÚ (`tracking/index`) ÕõåÑ İí ÌÒÆíÉ æÇÍÏÉ `partials/order-events-timeline` ÚÈÑ `@include` (ÇáÍÑøÇÓÇÊ İí ãæÖÚ ÇáÇÓÊÏÚÇÁ — áÇ ÃËÑ Úáì ÕáÇÍíÇÊ 29.1).
- ãİÊÇÍ `merchant_panel.tracking_status` ×4 áÛÇÊ áÑÃÓ ÇáÌÏæá + ãäÊŞí ÇáÃÚãÏÉ. ÇáÈĞÑÉ ÇáãÍáíÉ (`order_tracking_histories`): **ŞÑÇÑ ÇáãÓÊÎÏã: ÊÎØóøÇåÇ** — ÇáÈíÇäÇÊ ÊäÔÃ ØÈíÚíğÇ ÚäÏ ÇáÅÑÓÇá æÇáİÑÇÛ ãõÚÇáóÌ ÃÕáÇğ.
- `OrdersTrackingColumnTest.php` — **3 äÇÌÍÉ** (ÔÇÑÉ ãÚ ÊİÚíá ÇáÚãæÏ¡ ÛíÇÈåÇ ÇİÊÑÇÖíğÇ¡ `—` ÈáÇ ÔÍäÉ). ãáÇÍÙÉ: ÍÇáÉ `damaged` ãÓÊÎÏãÉ İí ÇáÇÎÊÈÇÑ áÃäåÇ ÎÇÕÉ ÈäØÇŞ `tracking` İŞØ (ÍÇáÉ `in_transit` ãæÌæÏÉ ÃíÖğÇ İí äØÇŞ `order` İÊáæøË ÇáİÍÕ). ÇáÓæíÊ ßÇãáÉ **317 äÇÌÍ (1158 assertions)** ? ÕİÑ ÇäÍÏÇÑ. `view:cache` + `php -l` ÓáíãÇä.
- **ãÊÇÈÚÉ ØáÈ ÇáãÓÊÎÏã (ÊõŞÑÃ ÇáÍÇáÉ ÚÈÑ ÇáÚáÇŞÉ æáíÓ İæŞ ÌÏæá orders):** ÍÇÑÓÇ ÒÑí ÇáÅÑÓÇá İí ÇáÌÏæá (desktop) æÈØÇŞÉ ÇáãæÈÇíá ßÇäÇ íŞÑÂä ÇáÍÇáÉ ãä ÇáÚäæÇä ÇáãÊÏÇÎá `$order['status']['key']` (ÃÑÔíİ ÚáÇŞÉ `status` ÈÚÏ `toArray`) — æåĞÇ ÛíÑ ÈÏíåí æíõİåã ÎØÃğ ßÚãæÏ ãÈÇÔÑ İí ÌÏæá `orders`. ÇáÍá: `$arr['status_key'] = $order->status?->key` íõÍÓÈ ÕÑÇÍÉğ İí ÈÇäíø ÇáÕİ ÏÇÎá `loadOrders` (ÇáŞÑÇÁÉ ãä ÌÏæá ÇáÅÍÇáÉ ÚÈÑ `status_id`)¡ æÇáÍÇÑÓÇä íÓÊÎÏãÇä `$order['status_key'] ?? null`º ÇáãÄßÏ ÈÍÏòø æÇÍÏ åæ ÇáäãæĞÌí ÇáŞÇÆã `$order->status?->key`. ÇáÓæíÊ ßÇãáÉ ÈÚÏ ÇáÊÚÏíá **317 äÇÌÍ (1158 assertions)** — ÕİÑ ÇäÍÏÇÑ 27 äÇÌÍ (94 assertions) İí ÇáÇÓÊåÏÇİ.

### ÌæáÉ Phase 29 — İÑÚ 29.5 ? (ÔÇÑÉ ÇáÊßÑÇÑ + ÇáİÍÕ ÇáÈØíÁ)

- **ÇáÚáÇãÉ (ÛíÑ ãÚØöøáÉ):** ÏÇÎá `loadOrders` ÇÓÊÚáÇã ÊÌãíÚí æÇÍÏ — ÚÏÏ ØáÈíÇÊ ÇáãÊÌÑ İí äÇİĞÉ 30 íæãğÇ (`OrderDuplicateService::WINDOW_DAYS`) ãÌãóøÚğÇ ÈÜ`customer_id` ÚÈÑ `havingRaw('COUNT(*) >= 2')` Úáì İåÑÓ `orders_duplicate_scan (store_id, customer_id, created_at)`º İí ÈÇäíø ÇáÕİ `$arr['duplicate_count'] = $duplicateCounts[$customer_id] ?? 0`. ÇáÔÇÑÉ `edz-badge--warning --sm` «×N» ÈÚÏ ÑŞã ÇáØáÈíÉ (ÎáíÉ ÇáÑŞã İí ÇáÌÏæá + ÑÃÓ ÈØÇŞÉ ÇáãæÈÇíá) ãÔÑæØÉ: `!showTrash` + `status_key !== 'duplicate'` + `? 2`. ÈáÇ ãİÇÊíÍ ÊÑÌãÉ ÌÏíÏÉ.
- **ÇáİÍÕ ÇáÈØíÁ:** `$openDuplicateScan(orderId)` ÊõÔÛøá `findSimilar()` **ÚäÏ ÇáäŞÑ İŞØ** (áÇ ÔíÁ áÕİæİ ÛíÑ ãßÈæÓÉ) æÊÚÑÖ `<x-edz.modal>` ÈäãØ ÏÑÌ ÇáÊÃßíÏ (ÑŞã • ãäĞ…¡ ×ßãíÉ¡ ÇäÊŞÇá áÏÑÌ ÇáÊİÇÕíá) + `$closeDuplicateScanModal` ÚÈÑ `@edz-modal-closed.window`. ÇáäÇİĞÉ ãÚáæãÇÊ İŞØ áÃÚÖÇÁ ÇáÕİÍÉ (ÈíÇäÇÊ ãÊÌÑ ÇáÎÇÕ äİÓå¡ ãÑÆíÉ ÃÕáğÇ).
- **ÃÏáÉ:** `tests/Feature/Merchant/OrderDuplicateBadgeTest.php` — **5 äÇÌÍÉ (11 assertions)** (ãÓÇÚÏÇÊ `dbb*` — Pest áÇ ÊÔÇÑß ÇáÏæÇá Èíä ÇáãáİÇÊ İãäÚ ÇáÊÏÇÎá ãÚ `dup*`): ? ÔÇÑÉ áßáÇ Õİøí ÇáÑŞã ÇáãßÑÑ¡ ? ÓØÑ ÈáÇ ÊßÑÇÑ ÈáÇ ÔÇÑÉ¡ ? ÇáäŞÑ íİÍÕ æíÚÑÖ ÇáÂÎÑíä İŞØ (äÊíÌÊÇä¡ äİÓå ãõÓÊÈÚÏ¡ ÑŞã ÙÇåÑ)¡ ? ØáÈíøÇÊ ÍÇáÉ `duplicate` áÇ ÔÇÑÉ¡ ? ÎÇÑÌ äÇİĞÉ 30 íæãğÇ áÇ ÚáÇãÉ (ãáÇÍÙÉ ÊŞäíÉ: `created_at` áíÓ fillable — íõÖÈØ ÈÚÏ `create()` ÈÕİÉ ÇáÍÇáÉ). ÇáÓæíÊ ßÇãáÉ **322 äÇÌÍ (1169 assertions)** ? ÕİÑ ÇäÍÏÇÑ. `view:cache` + `php -l` ÓáíãÇä.
- **ãÊÇÈÚÉ 29.5a — ÊæÍíÏ ÇáÊÚÑíİ (ãæÇİŞÉ ÇáãÓÊÎÏã ÈÚÏ ÊŞÑíÑ äÇÊÌ¡ æáíÓ ÊÎãíäğÇ):** ÇáÔÇÑÉ ßÇäÊ ÊÚÏø ØáÈíÇÊ ÇáÚãíá İí 30 íæãğÇ İŞØ (ÑŞã)¡ ÈíäãÇ ÇáäÇİĞÉ ÊÔÊÑØ ÇáÑŞã + ÇáãäÊÌ ? İÑŞ ÈíäåãÇ (ÔæÇåÏ ÍíÉ: 00005/00006 ÈÇáåÇÊİ 0669633075 — ãäÊÌíä ãÎÊáİíäº «0 similar» ÚÈÑ ÌãíÚ ÇáØáÈíÇÊ). ÇáÊÚÏíá: ? `OrderDuplicateService::countsBySiblings($orderIds, $storeId)` — ÇÓÊÚáÇãÇä (äÇİĞÉ ÇáãÊÌÑ ãÑÉ + ÃÕäÇİåÇ ãÑÉ Úáì İåÑÓ `orders_duplicate_scan`) íõÚíÏÇä `same_phone`/`same_product` áßá ØáÈíÉ (ÇáÃÎæÉ ÈäİÓ ÇáÚãíá == äİÓ ÇáÑŞãº íÊÌÇåá ÇáĞÇÊº ÈáÇ ÊÑÔíÍ ÍÇáÉ)¡ æÇáÔÇÑÉ ÊÓÊÎÏã **`same_product`** ÈÓŞİ ÚÑÖ «×9+»¡ ? `findSimilar` ÃõÕáÍ ãä N+1 (`->with(['status','items'])`)¡ ? ÍÇáÉ İÑÇÛ ĞßíÉ İí ÇáäÇİĞÉ: Åä ÇÓÊõÈÚÏÊ ÇáãäÊÌÇÊ İŞØ ? ÑÓÇáÉ ãÍÇíÏÉ ÈãİÊÇÍ ÌÏíÏ `duplicate_phone_only_orders` ×4 áÛÇÊ (ÈÏá ÇáÃÎÖÑ ÇáãÖáöøá). ÇÎÊÈÇÑÇÊ **6** (ÑŞã+ãäÊÌ ? ÔÇÑÉ ×ÇáäÇİĞÉ¡ ÑŞã ÈãäÊÌÇÊ ãÎÊáİÉ ? ÕİÑ ÔÇÑÉ + ÑÓÇáÉ¡ æÍíÏÉ¡ ÍÇáÉ duplicate¡ ÎÇÑÌ ÇáäÇİĞÉ¡ ÇÓÊÈÚÇÏ ÇáĞÇÊ). ÊÍŞøŞ: **323 äÇÌÍ (1177 assertions)** ? ÕİÑ ÇäÍÏÇÑº `view:cache` + `php -l` ÓáíãÇä.
- **29.6 — ÇáÊÕäíİ ÇáËáÇËí «ãßÑÑÉ/ÇÍÊãÇá ÊßÑÇÑ/ÓÈŞ ÇáØáÈ» + äŞá ÇáæÔã ÈÌÇäÈ ÇÓã ÇáÒÈæä (ŞÑÇÑÇÊ ÇáãÓÊÎÏã: ßÔİ İæÑí + ÊÃßíÏ ÈÖÛØÉ áÇ ßÊÇÈÉ ÍÇáÉº ãÚíÇÑ «ÓÈŞ ÇáØáÈ» = ØáÈíÉ ÓÇÈŞÉ æÕáÊ ááÅÑÓÇá ÈÃí ÚãÑ):** ? `countsPriorCarrierOrders(customerIds)` — ÚÏø ØáÈíÇÊ äİÓ ÇáÚãíá (Ãí ÚãÑ) ÈáÛÊ ÇáäÇŞá: `shipping_provider_id`/`delivery_rider_id` Ãæ ÍÇáÉ ãä `OrderWorkflow::carrier()` (shipped/in_transit/out_for_delivery/delivered/returned). ? `loadOrders`: `repeat_count` = ÇáÚÏ äÇŞÕ ÇáØáÈíÉ äİÓåÇ (áÇ ÊÚáöøã ĞÇÊåÇ) + `dup_level` ÈÃæáæíÉ duplicate > probable > repeat (æÔã æÇÍÏ ßÍÏ ÃŞÕì ááÕİ — ÈáÇ ÊÔæíÔ). ? ÇáæÔã **ÈÌÇäÈ ÇÓã ÇáÒÈæä** (ÚãæÏ ÇáÒÈæä + ÈØÇŞÉ ÇáãæÈÇíá ÈÌÇäÈ ÇáÇÓã) æÍõĞİ äåÇÆíğÇ ãä ÈÌÇäÈ ÑŞã ÇáØáÈíÉº ÇáäÛãÇÊ: danger/ãßÑÑÉ (×N ÈÓŞİ 9+)¡ warning/ÇÍÊãÇá ÊßÑÇÑ (×N)¡ neutral/ÓÈŞ ÇáØáÈ (ÈáÇ ÚÏÏ). ? `openDuplicateScan` íÕäøİ: `duplicateScanLevel` + `duplicateScanRepeatCount`º ÇáäÇİĞÉ ÊÙåÑ ÔÑíÍÉ ÊÕäíİ (ãßÑÑÉ/ÇÍÊãÇá ÊßÑÇÑ/ÓÈŞ ÇáØáÈ/áÇ ÊßÑÇÑ) + ÓØÑ ÏæãğÇ ÚäÏåÇ ÊÇÑíÎ «ÃõÑÓá ÓÇÈŞğÇ ááäÇŞá» ÍÊì İí ÍÇáÇÊ ãßÑÑÉ. ÊÑÌãÇÊ ÌÏíÏÉ ×4: `dup_badge_duplicate`/`dup_badge_probable`/`dup_badge_repeat`/`dup_repeat_carrier_sent`. ÇÎÊÈÇÑÇÊ **10** (ãßÑÑ Şæí + ÊÕäíİ ÇáäÇİĞÉ¡ ÇÍÊãÇá ÈãäÊÌÇÊ ãÎÊáİÉ¡ ÓÈŞ ØáÈ ÈÚãÑ 60 íæãğÇ¡ ÃæáæíÉ ãßÑÑÉ ãÚ ÈŞÇÁ ÓØÑ ÇáÊÇÑíÎ¡ ØáÈíÉ sent æÍíÏÉ ÈáÇ æÔã áĞÇÊåÇ¡ ÎÇÑÌ ÇáäÇİĞÉ¡ ÍÇáÉ duplicate¡ soft-delete¡ ÓŞİ ×9+). ÊÍŞøŞ: **326 äÇÌÍ (1204 assertions)** — ÕİÑ ÇäÍÏÇÑ. ãáÇÍÙÉ: ãİÊÇÍ ÇáäÇŞá åæ `shipped` áÇ `sent`º ÊÓãíÇÊ ãÊÑÌãÉ ßÜ«Duplicate» ÊÊáæøË ÈÕÏİÉ ãÚ äØÇŞ ÇáÍÇáÇÊ (ŞÑíäÉ ÇáäÛãÇÊ).
---

### ÌæáÉ ÇáÊäİíĞ 2026-09-06 — **ÈÊÑŞíã ÇáÈÑæãÊ** (ÊÏÇÎá ÊÓãíÇÊ ãæËøŞ: ÇáÈÑæãÊ 29.4=äÇİĞÉ ÓÌá ÇáÊÊÈÚ¡ 29.5=İÍÕ ÚÏÏ ÇáÇÓÊÚáÇãÇÊ¡ 29.6=ÊæÍíÏ ÇáÍÑøÇÓ, 29.7=ŞÇÆãÉ «ÇáãÒíÏ» ÇáãÊäŞáÉº æËÇÆŞ ÇáãÔÑæÚ ÇáÓÇÈŞÉ ßÇä 29.6=ÇáÊÕäíİ ÇáËáÇËí¡ 29.7=ÇáÊÑæÓ?ÊæÓÊ+ÊßÇİÄ ÇáÈØÇŞÉ — ÇßÊãáÇ İí ÌæáÇÊ ÓÇÈŞÉ¡ æãÍÊæì ÇáÈÑæãÊ/ÇáÌæáÉ ÇáÍÇáíÉ ÃõÖíİ İæŞåãÇ)

- **29.4 (ÈÚÏ İÍÕ ÇáäØÇŞ áßá ÈäÏ ãä ÇáÈÑæãÊ + ÊÍŞŞ ãä ÇáãßÇäíä):**
  - ÇáŞÑÇÑ ÇáäåÇÆí ááãÓÊÎÏã: ÇáØáÈíÉ ÈÚÏ ÅÑÓÇáåÇ ááäÇŞá ÊõÚÇáóÌ İí ÕİÍÉ ÇáÊÊÈÚ ? **ÓÌá ÍÇáÉ ÇáÊÊÈÚ íÎÕ ÕİÍÉ ÇáÊÊÈÚ İŞØ**º ÕİÍÉ ÇáØáÈíÇÊ ÊõÈŞí ÔÇÑÇÊ ÇáÊÊÈÚ ãÚáæãÇÊíÉ (ÛíÑ ŞÇÈáÉ ááäŞÑ) æÊõÙåÑ **ÓÌá ÃÍÏÇË ÇáØáÈíÉ (audit log)** ßÅÌÑÇÁ Õİ.
  - **ÕİÍÉ ÇáØáÈíÇÊ:** ÒÑ clock İí ÎáíÉ ÇáÅÌÑÇÁÇÊ (desktop) + ÈØÇŞÉ ÇáãæÈÇíá ? ŞÇÆãÉ ãäÓÏáÉ (ãßæøä Alpine `orderEventsMenu` íŞÑÃ orderId/canView ãä data-* — Óáíã ãÚ ÓíÇÓÉ ÚÏã ÍŞä Blade İí ÎÕÇÆÕ Alpine) ÊÚÑÖ ?4 ÃÍÏÇË + «ÚÑÖ ÇáãÒíÏ» ? äÇİĞÉ ßÇãáÉ `order-events-modal`. **ÍõĞİ ŞÓã ÓÌá ÇáÃÍÏÇË ãä ÏÑÌ ÊİÇÕíá ÇáØáÈíÉ äåÇÆíğÇ.** ÇáÍÇÑÓ: `canViewOrderEventLog` (OWNER/ADMIN¡ MANAGER ááãÚíóøä¡ STAFF ÃÈÏğÇ) ? `messages.permission_denied` ÊæÓÊ ááÑİÖ + `can_view_events` áßá Õİ İí `loadOrders` (ÈáÇ N+1 — ÇáÃÍÏÇË ÊõÍãóøá ÈßÓá ÚäÏ ÇáäŞÑ İŞØ ÈÍÏ 15).
  - **ÕİÍÉ ÇáÊÊÈÚ:** ÔÇÑÉ ÍÇáÉ ÇáÊÊÈÚ (ÌÏæá + ÈØÇŞÉ) ÃÕÈÍÊ ÒÑğÇ ? äÇİĞÉ `tracking-history-popup` ÊÚÑÖ ÇáÎØ ÇáÒãäí ÇáãÔÊÑß `tracking-history-timeline`. ÇáÊÌÒÆÉ: `orders/partials/order-events-menu.blade.php` + `order-events-modal.blade.php` (íÓÊæÑÏ `order-events-timeline`) + `tracking/partials/tracking-history-popup.blade.php` — **áÇ ÍÔÑ İí index**.
  - ãİÇÊíÍ ×4 áÛÇÊ: `order_flow.show_more` + `order_flow.order_timeline_loading`.
  - ÇÎÊÈÇÑÇÊ: `TrackingStatusHistoryPopupTest` **3** (ÃÍÏËåÇ ÃæáğÇ ãÚ meta¡ ÇáÅÛáÇŞ íİÑøÛ¡ ÒÑ wire:click ãæÌæÏ) + äŞÍÉ `OrderEventLogVisibilityTest` (ÊÍãíá/İÊÍ ÇáäÇİĞÉ ááãÇáß¡ ÑİÖ STAFF ÈÊæÓÊ+İÑÇÛ¡ ÒÑ ãÎİí áÕİæİ STAFF¡ ÚÏø ÇáãÔÛøá ãÑÊíä = ÌÏæá+ÈØÇŞÉ ááãÏíÑ (2) ) + `OrdersTrackingColumnTest` ÇáÈäíæí (page ÊÒäøÑ `order-events-modal` ÇáÊí ÊÒäøÑ `order-events-timeline`). **19 äÇÌÍ (63 assertions)**.
- **29.5 — İÍÕ ÚÏÏ ÇáÇÓÊÚáÇãÇÊ:** `OrdersPageQueryCountTest` íõŞÇÑä **ÇÓÊÚáÇãÇÊ ÇáÈíÇäÇÊ İŞØ** (orders/items/customers/trackings/providers/variants/products) áÕİÍÉ ÈÜ6 ØáÈíÇÊ ãŞÇÈá 16 — İÑŞ ?2 (ÊÍãíá ãÓÈŞ ßÇãáº ÈáÇ N+1). (ÇÓÊÚáÇãÇÊ `ltu_languages`/ÇáÃÏæÇÑ ÎÇÑÌ ÇáÚÏø áÃäåÇ ÈäíæíÉ ãÑÊÈØÉ ÈÇáÊÑÌãÉ æÇáÕáÇÍíÇÊ æáíÓ ÈÕİæİ ÇáŞÇÆãÉ). **1 ÇÎÊÈÇÑ**.
- **29.6 — ÊæÍíÏ ÇáÍÑøÇÓ «ÊæÓÊ ÈÏá 403» ÈÇáßÇãá:** ßá ÍÑøÇÓ `abort_unless(...403)` İí ÅÌÑÇÁÇÊ `orders/index.blade.php` (ÊÏİŞ ÇáÅÑÓÇá + delete/assign/confirm/manage/transition) ÃÕÈÍÊ `if (! canStore(...)) ? ÊæÓÊ `messages.permission_denied` + return`º ÃÈŞíó `mount` ORDER_VIEW 403 İŞØ (ãÓÊæì ÕİÍÉ). ÍÇÑÓ `HasInlineEdit::saveEdit` (ÇáÈäíÉ ÇáÊÍÊíÉ ÇáãÔÊÑßÉ — brand/product/order) Íæöøá åæ ÇáÂÎÑ. `sendConfirmedOrder`/membership guard: `unauthorized`?`permission_denied`. **ÕİÑ `messages.unauthorized` ãÊÈŞòø** İí `livewire/merchant`. ÇÎÊÈÇÑÇÊ: 3 ãáİÇÊ ÇáÊÍÑíÑ ÇáãÈÇÔÑ (403?ÊæÓÊ)¡ `DirectSendConfirmedOrderTest` + `BulkSendCarrierGroupingTest` (unauthorized?permission_denied). Merchant slice **162 äÇÌÍ (739)**, add Unicode.
- **29.7 — ŞÇÆãÉ «ÇáãÒíÏ» İí ÈØÇŞÉ ÇáãæÈÇíá:** ÃíŞæäÉ `ellipsis-horizontal` (ãİÊÇÍ `general.more`) ? áæÍÉ ãäÓÏáÉ Èãßæøä `orderMoreMenu` (fixed top/left¡ data-* äÙíİ) ÊäŞá confirm/send/edit/reassign/delete ãä ÇáÕİ ÇáãßÊÙ Åáì **Õİæİ 44px** (min-h-[44px]) ÊõÛáŞ ÇááæÍÉ ÈÚÏ ÇáİÚá (`confirmDelete(); close()` ÚÈÑ ÓáÓáÉ ÇáäØÇŞ). `OrdersMobileMoreMenuTest` **2** (ãÇáß = menu+44px¡ STAFF = menu ÈáÇ Ãí Õİ). `panel.js` ÓÌøá `orderMoreMenu` + Èäí `npm run build`.
- **äŞÍÉ 29.4b — ÙåæÑ ÇáŞæÇÆã Úáì ÇáÔÇÔÇÊ ÇáÕÛíÑÉ ÈÇÍÊÑÇİíÉ (ØáÈ ãÈÇÔÑ):** ? ŞÇÆãÉ ÓÌá ÃÍÏÇË ÇáØáÈíÉ İí ?sm (æÃí ÊÑÇßÈ ááÍÇİÉ Úáì ?sm): ãßæøä `orderEventsMenu()` ÇáÂä **íËÈøÊ ÇááæÍÉ Öãä ÍÏæÏ ÇáÔÇÔÉ** (ÅÓŞÇØ íãíä/íÓÇÑ + İáúÈ ÕÏÑí ÈÚÊÈÉ 8px)¡ æÚáì ÇáåÇÊİ (<640px) ÊÊÍæá ãä ŞÇÆãÉ ãÚáøŞÉ Åáì **æÑŞÉ ÓİáíÉ** ßÇãáÉ ÇáÚÑÖ: ÛãøÉ `bg-black/40 backdrop-blur-sm` ÊõÛáŞ ÈÇáäŞÑ + ãŞÈÖ ÓÍÈ (`h-1 w-10`) + ÒÑ ÅÛáÇŞ `x-mark` (`general.close`) + ÚäæÇä «ÓÌá ÇáØáÈíÉ»¡ ãÚ `pb-[env(safe-area-inset-bottom)]` æÊŞáíÕ ÇáÚÑÖ ááÈØÇŞÇÊº «ÚÑÖ ÇáãÒíÏ» ÕÇÑÊ CTA ÈÇÑÒÉ `accent-50?accent-100`º İí ?sm ÊÍÇİÙ Úáì `w-80` ÇáãÚáøŞÉ ÈÚäæÇäåÇ. ? äÇİĞÉ ÓÌá ÇáÊÊÈÚ (`tracking-history-popup`): ãæÏÇá `edz-modal` ÃÕáğÇ æÑŞÉ ÓİáíÉ İí ÇáãæÈÇíá¡ İÃõÖíİ **ãŞÈÖ ÇáÓÍÈ** (`edz-modal__handle`) + ÍÔæ ãÖÈæØ `p-4 pt-1 sm:p-6 sm:pt-5` + ÑÃÓ ØáÈíÉ ÈÎØ ÚÑíÖ (ÑŞã + T.N. ÈäãØ tabular) ÈÏá ÇáÓØÑ ÇáÑãÇÏí. **348 äÇÌÍ (1268 assertions)** — ÕİÑ ÇäÍÏÇÑ (ÚÏø `OrderEventLogVisibilityTest` «ÚÏø ÇáãÔÛøá ãÑÊíä» Óáíã ÈÚÏ ÇáÊÚÏíá)º `npm run build` + `view:cache` ÓáíãÇä.
- **ÊÍŞøŞ äåÇÆí ááÌæáÉ:** ÇáÓæíÊ ßÇãáÉ **348 äÇÌÍ (1268 assertions)** — ÕİÑ ÇäÍÏÇÑº `view:cache` äÇÌÍ Ëã `git checkout -- storage/framework/views/`º ÇáÈäÇÁ äÇÌÍ.
- **ÌæáÉ ÊæÍíÏ ÇáŞæÇÆã + ÇáÓÈíäÑÇÊ (ØáÈ ãÈÇÔÑ 2026-09-06):** ßá ÇáŞæÇÆã/ÇáÈæÈ ÃæİÑÒ İí ÕİÍÉ ÇáØáÈíÇÊ ÊÊÍæá İí ÇáãæÈÇíá Åáì **æÑŞÉ ÓİáíÉ ãæÍÏÉ** (ÛãøÉ `bg-black/40 backdrop-blur-sm` ÊõÛáŞ ÈÇáäŞÑ + ãŞÈÖ `h-1 w-10` + ÑÃÓ ÈÚäæÇä + ÒÑ `x-mark` + `pb-[env(safe-area-inset-bottom)]` + `max-h-[70vh] overflow-y-auto` + `rounded-t-2xl shadow-[0_-16px_48px_-12px_rgba(15,23,42,.25)]`) ãÚ ÚæÏÉ ?sm Åáì ÇááæÍÉ ÇáãÚáøŞÉ ÇáãËÈøÊÉ ÏÇÎá äØÇŞ ÇáÔÇÔÉ (`menuStyle` ÚÈÑ ãßæøäÇÊ Alpine: `orderEventsMenu` (ÓÇÈŞğÇ)¡ `orderMoreMenu`¡ `orderRowActions.openStatusMenu`¡ `dropdownPosition`)º ÔãáÊ: ŞÇÆãÊí ÇáÍÇáÉ (ÌÏæá + ÈØÇŞÉ¡ `sm:w-56`¡ ÚäæÇä `merchant_panel.status`)¡ ŞÇÆãÉ «ÇáãÒíÏ» (`sm:w-60`¡ ÚäæÇä `merchant_panel.actions` + ÓÈíäÑÇÊ confirm/send/edit/reassign)¡ ÈæÇÈÉ ÇáİáÇÊÑ (`:style="menuStyle"` + ÚÑæÖ `sm:w-48/sm:w-52` + `sm:max-h-64` ááŞæÇÆã + ÚäæÇä `buttons.filter`)¡ ÇáŞæÇÆã ÇáËáÇË İí ÇáÔÑíØ (ÇáãÕÏÑ `merchant_panel.source`/äæÚ ÇáÊæÕíá `storefront.delivery_type`/ÔÑßÉ ÇáÔÍä `order_flow.filter_provider`¡ ÊäŞá `sm:absolute sm:bottom-auto`)¡ æŞÇÆãÉ ÇáÅÓäÇÏ İí ÔÑíØ ÇáÌãÇÚí (`merchant.bulk_assign_agent`¡ ÊäŞá `sm:right-0`). **ÇáÓÈíäÑÇÊ** ÃõÖíİÊ áßá ÃÒÑÇÑ ÇáİÚá İí ÇáÕİÍÉ ÈáÇ ÇÓÊËäÇÁ: ØáÈ ÌÏíÏ¡ ÇáÃÚãÏÉ¡ ÊİÇÕíá/ÊÃßíÏ/ÅÑÓÇá/ÊÚÏíá/ÅÚÇÏÉ ÅÓäÇÏ áßá Õİ (ÌÏæá + ÈØÇŞÉ)¡ ÊÃßíÏ ÇáÍÇáÉ¡ ÅÑÓÇá ááäÇŞá¡ ÊÃßíÏ ÇáÍÇáÉ ÇáÌãÇÚíÉ¡ ÅÑÓÇá ÌãÇÚí — ÚÈÑ `<x-edz.spinner wire:target="method(...)">` + `wire:loading.remove` + `wire:loading.attr="disabled"` (ÇáÃÒÑÇÑ ÇáÊí ßÇäÊ Êãáß ÓÈíäÑğÇ¡ ãËá refresh¡ ÈŞíÊ). ÊÑÌãÇÊ ÇáÚäÇæíä ãæÌæÏÉ ×4 áÛÇÊ (ÊÍŞøŞ Âáí). **ÇáÇÎÊÈÇÑÇÊ: 348 äÇÌÍ (1268 assertions)** — ÕİÑ ÇäÍÏÇÑº `npm run build` + `view:cache` + `git checkout -- storage/framework/views/` ÓáíãÉ.

- **ÌæáÉ ÇáÃíŞæäÇÊ + ÇáŞæÇÆã ÇáãÔÊÑßÉ + ÓáÓáÉ ÇÎÊíÇÑ ÇáÊæÕíá (ØáÈ ãÈÇÔÑ 2026-09-06):**
  - **ÇáÃíŞæäÇÊ:** ãÑÇÌÚÉ `components/edz/icon.blade.php` — ÃÓãÇÁ ãõÓÊÚãáÉ áã Êßä ãõÚÑóøİÉ İßÇäÊ ÊŞÚ Úáì ÃíŞæäÉ `grid` ÇáÇÍÊíÇØíÉ (ãËá ÒÑ ÓÌá ÇáÃÍÏÇË¡ ŞÇÆãÉ «ÇáãÒíÏ» Úáì ÇáåÇÊİ¡ İáÊÑ¡ uuid¡ QR¡ arrow-u-turn¡ ÇáÇÊÌÇåÇÊ...). ÃõÖíİÊ ãÓÇÑÇÊ SVG (heroicons v2 outline) áÜ 14 ãİÊÇÍğÇ (`arrow-uturn-left`, `building-library`, `building-storefront`, `clipboard`, `clock`, `cloud-upload`, `device-phone-mobile`, `ellipsis-horizontal`, `funnel`, `information-circle`, `paper-airplane`, `qr-code`, `trending-up`, `user-plus`) + ÃÓãÇÁ ãÊÑÇÏİÉ 5 (`arrow-forward-outline`?`arrow-right`, `bag-outline`?`bag`, `checkmark-circle-outline`?`check-circle`, `search-outline`?`magnifying-glass`, `x`?`x-mark`). ÅÚÇÏÉ İÍÕ Âáí: ÕİÑ ÇÓã ãİŞæÏ. ÇáÃíŞæäÇÊ SVG ãÖãóøäÉ (ÊõÑäÏÑ ãä PHP) — áÇ ÊÍÊÇÌ `npm run build`.
  - **ÇáŞæÇÆã ÇáãÔÊÑßÉ İí ßá ÇááæÍÉ (ÇáÈæÈ-ÃæİÑÒ ÇáãÊÈŞíÉ):** `components/edz/dropdown.blade.php` (ÇáãÓÊÎÏã İí `user-dropdown` æ`notification-dropdown` İŞØ — ÇáŞÇÆãÉ ÇáæÍíÏÉ ÇáãÔÊÑßÉ) ÃõÚíÏ ÈäÇÄå ÈÇáßÇãá: ÛãøÉ `edz-dropdown__scrim` (ÇáÛãøÉ ãŞÇÈá ÇááæÍÉ ÊõÛáŞ ÈÇáäŞÑ¡ `@click.away` áÇ íÊÚÇÑÖ) + áæÍÉ ÊÊÍæá İí ÇáãæÈÇíá Åáì **æÑŞÉ ÓİáíÉ** (ÚÑÖ ßÇãá¡ `max-height min(75vh,480px)`¡ ãŞÈÖ/ÍÔæ Óİáí Âãä) æ?640px Åáì áæÍÉ ãÚáøŞÉ (`--edz-dropdown-width`¡ `is-right`/`is-left` ÚÈÑ `align` — ÅÕáÇÍ ÎØÃ `$alignment` ÛíÑ ÇáãÚÑøİ). SCSS İí `resources/css/components/_dropdown.scss` — íÊØáøÈ `npm run build`. ãÓÍ ÇááæÍÉ: ÈÇŞí «ÇáŞæÇÆã» İíæÑÏæä/ãÎÊÇÑÇÊ ãÖãøäÉ ÏÇÎá ÍÇæíÇÊåÇ (áÇ ÊÍÊÇÌ ÊÍæíáğÇ).
  - **ÓáÓáÉ ÇÎÊíÇÑ ÇáÊæÕíá (äãæĞÌ ØáÈ ÇáÊÇÌÑ): ÇáÊÑÊíÈ ÃãÓÍ `ÔÜÑßÉ ? äæÚíÉ ? æáÇíÉ ? ÈáÏíÉ ? ÇáãßÊÈ`**: ÃõÚíÏ ÊÑÊíÈ `order-form-modal.blade.php` — ÇáÔÑßÉ ÃæáÇğ (ÙÇåÑÉ ÏÇÆãğÇ) Ëã ÃÒÑÇÑ ÇáäæÚ Ëã æáÇíÉ/ÈáÏíÉ Ëã ÇáãßÊÈ (äŞÇØ ÇÓÊáÇã İŞØ). `rebuildFormOffices` íÍÕÑ ÇáãßÇÊÈ ÈÈáÏíÉ ÇáãÎÊÇÑÉ æíËÈøÊ ÇáãßÊÈ ÊáŞÇÆíğÇ Åä ßÇä ááÈáÏíÉ ãßÊÈ æÇÍÏ (`delivery_type === 'stopdesk'` + ÈáÏíÉ + ãØÇÈŞ æÇÍÏ) æíİÑøÛ ÇáÇäÊŞÇÁ ÇáãäŞÖíº `$changeDeliveryType` íÚíÏ ÈäÇÁ ÇáÎíÇÑÇÊ ÚäÏ ÇáŞİÒ Åáì stopdesk (Ïæä ÊÛííÑ ÇáÔÑßÉ). ÊáãíÍÇÊ ÅÑÔÇÏíÉ ÍÓÈ ÇáÎØæÉ (ÇÎÊÑ ÇáÔÑßÉ ÃæáÇğ/ÇÎÊÑ ÇáãÏíäÉ). ãİÊÇÍ `merchant_panel.select_company_first` ×4 áÛÇÊ. **ÕİÍÉ ÇáÏİÚ (ÇáÒÇÆÑ)** ÊÊÈÚ ÊÑÊíÈåÇ ÇáØÈíÚí (æáÇíÉ ? ÈáÏíÉ ? äæÚíÉ ? ãßÊÈ ãÌãøÚ ÈÇáÔÑßÉ) — Óáíã Ïæä ÊÛííÑ.
  - **ÃÏáÉ 2026-09-06:** ÇÓÊåÏÇİ 24 äÇÌÍ (76 assertions) — ãäåÇ `OrdersPageQueryCountTest` (İÍÕ ÚÏÏ ÇÓÊÚáÇãÇÊ ÇáÃÏÇÁ¡ İÑŞ ?2).

## ÌæáÉ ÅÕáÇÍ ÏÑæÈ-ÏÇæä ÇáÔÑíØ ÇáÚáæí (äÇİÈÇÑ ÇáÅÔÚÇÑÇÊ + ÒÑ ŞÇÆãÉ ÇáãÓÊÎÏã) — 2026-09-06 ?

**ÚÑÖ ÇáãÓÊÎÏã:** ÇáŞÇÆãÊÇä ÊÊäÏáÇä ááÃÚáì æíÙåÑ ãäåãÇ ÇáÌÒÁ ÇáÓİáí İŞØ Úáì ÇáÔÇÔÇÊ ÇáÕÛíÑÉ¡ Ëã ÈÚÏ ãÍÇæáÉ ÅÕáÇÍ ÈÜ `x-teleport` ÊÚØøáÊÇ ÊãÇãğÇ İí ßá ÇáãŞÇÓÇÊ.

**ÇáÊÔÎíÕ ÇáÌĞÑí:** `.edz-topbar` (sticky) íÍãá `backdrop-filter` — Ãí ÚäÕÑ áå `backdrop-filter` íÕÈÍ **containing block** áÜ `position: fixed` ãä äÓáå. áĞÇ æÑŞÉ `edz-dropdown__panel` ÇáÓİáíÉ ááãæÈÇíá (`position: fixed; bottom: 0`) æÇáÛãøÉ ßÇäÊÇ ÊõËÈóøÊÇä äÓÈÉğ ááÔÑíØ äİÓå (ÃÚáì ÇáÔÇÔÉ) áÇ ááÔÇÔÉ ? ÊõÑÓã ááÃÚáì æíÙåÑ ãäåÇ ÔÑíØ Óİáí İŞØ. (ÇááæÍÉ ÇáãÚáøŞÉ ááÏÓßÊæÈ `position: absolute; top: 100%` ÛíÑ ãÊÃËÑÉ áÃä `.edz-dropdown` ÇáÃŞÑÈ `relative` íÑÈØåÇ.)

**ãÇ Êóãø (ÇáäÓÎÉ ÇáäåÇÆíÉ — ãÈäíÉ æãÊÍŞóøŞ ãäåÇ):**
- **ÊÑÇÌõÚ ßÇãá Úä ÊÌÑÈÉ `x-teleport`** İí `components/edz/dropdown.blade.php` (ßÇäÊ äÍäøáåÇ Åáì `body` ãÚ ÊÍÏíÏ ÇáãæÖÚ ÈÇáÜ JS — áã ÊÚãá İí ÇáãæÈÇíá æÇáÏÓßÊæÈ İóÃõáÛíÊ äåÇÆíğÇ). ÚÇÏ Çáãßæøä ááÊÕãíã ÇáÈÓíØ ÇáãÓÊŞÑ: ÛãøÉ `edz-dropdown__scrim` ÔŞíŞÉ ÊõÛáŞ ÈÇáäŞÑ¡ ÇáÒÑ ÇáãØáŞ ááÜ trigger¡ æÇááæÍÉ `x-show`/`x-cloak`/`x-transition` ãÚ `is-right`/`is-left` ÚÈÑ `align` æ`--edz-dropdown-width`.
- **ÅÕáÇÍ ÇáÓÈÈ ÇáÌĞÑí İí `layouts/_topbar.scss`:** ÃõÒíá `backdrop-filter` æ`background-color: var(--edz-glass-bg)` ãä ÚäÕÑ ÇáÔÑíØ äİÓå¡ æäõŞá ÇáÒÌÇÌ Åáì `&::before { position:absolute; inset:0; z-index:-1; background-color:var(--edz-glass-bg); backdrop-filter:blur(var(--edz-blur-md)); }` — ÇáãÙåÑ ÇáÒÌÇÌí ãÍİæÙ ááÊÙÑíÉ¡ áßä ÇáÔÑíØ áã íÚÏ íÍÇÕÑ `position: fixed`¡ İÇáæÑŞÉ æÇáÛãøÉ İí ÇáãæÈÇíá ÊõËÈóøÊÇä ááÔÇÔÉ ÇáÂä.
- **`components/_dropdown.scss`:** ÇáãæÈÇíá æÑŞÉ ÓİáíÉ ßÇãáÉ (`fixed; inset-inline:0; bottom:0; width:100%; max-height:min(75vh,480px); overflow-y:auto` + ÍÔæ Óİáí Âãä)¡ æÇáÜ ?640 áæÍÉ ãÚáøŞÉ (`absolute; top:100%; width:var(...); is-right/is-left`). ÃõÖíİ ÃíÖğÇ ÃÓáæÈ `.edz-dropdown__title` ÇáãİŞæÏ (ÚäæÇä «ÇáÅÔÚÇÑÇÊ» ßÇä ÈáÇ ÊäÓíŞ).
- **ÊÕÍíÍ ÅÌÑÇÁ ÇáÚãá (åÇãÉ):** äåÌ `php artisan view:cache` Ëã `git checkout -- storage/framework/views/` ßÇä **íÓÊÚíÏ ãáİÇÊ ãÖÈæØÉ ŞÏíãÉ ãÊÊÈóøÚÉ İí git** — İßÇä ÇáãÊÕİÍ íÎÏã äÓÎÉ ŞÏíãÉ ãä Çáãßæøä ØæÇá ÌæáÇÊ ÓÇÈŞÉ (ÊİÓíÑ ÊÚÇÑÖ ÇáÔßÇæì). ÇáÅÌÑÇÁ ÇáÕÍíÍ ÇáÂä: `php artisan view:clear` Ëã `view:cache` æ**ÊÑß ÇáãÌáÏ äÙíİğÇ ÍÏíËğÇ** (ÊÙåÑ ÊÛííÑÇÊ İí git æáÇ íõÚÇÏ ÇÓÊÑÌÇÚåÇ).
- **ÃÏáÉ:** ãÎÑóÌ `Blade::render` ÇáãÊÑÌóã íØÇÈŞ Çáãßæøä ÇáÍÇáí ÊãÇãğÇ (scrim + `--edz-dropdown-width` + `is-right` + `x-cloak`/transitions)¡ æİÍÕ ÇáÚÈæÉ ÇáãÈäíøÉ íÄßÏ: ÇáÔÑíØ ÈáÇ `backdrop-filter` ãÈÇÔÑ ãÚ `:before` ÒÌÇÌí¡ æáæÍÉ ÇáãæÈÇíá `fixed` ÊãÊÏ ááÔÇÔÉ¡ æÇáÏÓßÊæÈ `absolute` ãÚ `is-right`. **ÇáÓæíÊ ßÇãáÉ 348 äÇÌÍ (1268 assertions)** — ÕİÑ ÇäÍÏÇÑ.

## ãÊÇÈÚÉ: ÅÕáÇÍ ÓßÑæá ÃİŞí ÚäÏ İÊÍ ÏÑæÈ-ÏÇæä ÇáÅÔÚÇÑÇÊ æÇáíæÒÑ ãäíæ (ÇÓÊßãÇá áäİÓ ÇáÌæáÉ) — 2026-09-06 ?

**ÚÑÖ ÇáãÓÊÎÏã:** ÚäÏ İÊÍ ÇáŞÇÆãÊíä íÙåÑ **ÓßÑæá ÈÇÑ ÃİŞí** — ÚÇÑÖ ãÑÊÈØ ÈØÑíŞÉ ÇáäÒæá İí RTL ãŞÇÈá LTR.

**ÇáÊÔÎíÕ:** ÊËÈíÊ áæÍÉ ÇáÏÓßÊæÈ ßÇä CSS ÎÇáÕğÇ (`is-right{right:0}`/`is-left{left:0}` äÓÈÉğ áãæÖÚ ÇáÜ wrapper ŞÑÈ ÃŞÕì äåÇíÉ ÇáÓØÑ — íãíä İí LTR æíÓÇÑ İí RTL). İÚäÏ RTL ÊÊãÏÏ áæÍÉ 350px ÌåÉ ÇáíÓÇÑ İÊÊÌÇæÑ ÍÏæÏ ÇáÔÇÔÉ æÊõÍæøá ÇáÕİÍÉ Åáì ÊãÑíÑ ÃİŞíº ãØáæÈ **ÊŞííÏ (clamp) ÇááæÍÉ Öãä ÃÈÚÇÏ ÇáÔÇÔÉ** áÇ ÊÌÇåá ÇáÍÇİÉ.

**ÇáÍá ÇáÇÍÊÑÇİí (ÈáÇ ÊÑŞíÚ — ÈäİÓ äãØ ÇáŞæÇÆã ÇáŞÇÆãÉ İí ÇáãÔÑæÚ `orderEventsMenu`/`orderMoreMenu`/`dropdownPosition`):**
- **`resources/js/components/edz-dropdown.js` ÌÏíÏ** — ãßæøä Alpine ãÓãøì `edzDropdown` ÈäİÓ ÊŞäíÉ `menuStyle`: ÇáãæÈÇíá (<640px) íãÓÍ inline æíÚÊãÏ æÑŞÉ CSS ÇáÓİáíÉ¡ æsm+ íŞíÓ ÇáİÚáíÉ (`offsetWidth/offsetHeight`) ÚÈÑ `$nextTick` æíÚíøä `fixed top/left` ãÚ **ÊËÈíÊ ÏÇÎá åÇãÔíä 8px** æÚßÓ ááÃÚáì ÚäÏ ÖíŞ ÇáÚãæÏ + ãÍÇĞÇÉ äÍæ ÇáÌåÉ ÇáãÊÇÍÉ ÇáÃæÓÚ ? Âãä İí RTL æLTR ÈáÇ ÓßÑæá ÃİŞí¡ æÈáÇ ÊÛííÑ DOM (`x-teleport` ãÓÊÈÚÏ äåÇÆíğÇ).
- **`dropdown.blade.php`:** `x-data="edzDropdown()"`¡ `x-ref` ááÒÑ æÇááæÍÉ¡ `:style="menuStyle"`¡ ÍÇÑÓ ŞíÇÓ `edz-dropdown__positioning` (opacity/pointer-events ŞÈá ÇáÊãæÖÚ — ÈáÇ æãíÖ)¡ æÇáÜ var `--edz-dropdown-width` ÇäÊŞá Åáì ÌĞÑ Çáãßæøä áíõæÑóøË ááæÍÉ (İáÇ ÊãÍæå ÇáÑÈØ `:style`).
- **`_dropdown.scss`:** ßÊáÉ ÇáÏÓßÊæÈ ÃÕÈÍÊ `position:fixed; inset:auto` ÈáÇ `is-right/is-left`/`top:100%` (ÇáÊãæÖÚ ãä JS)¡ ãÚ `max-width: calc(100vw - 1rem)` æ`max-height: min(60vh, 480px)` ÖãÇäğÇ ááÍÏæÏº ÃõÖíİ `.edz-dropdown__positioning`.
- **`panel.js`:** ÊÓÌíá `Alpine.data("edzDropdown", edzDropdown)`.
- **ÃÏáÉ:** ÇáÚÈæÉ ÇáãÈäíøÉ ÊÊÖãä ÇáÍÇÑÓ æ`max-width` æÇáßÊáÉ ÇáÌÏíÏÉ¡ æÍÒãÉ panel ÊÍæí `menuStyle`/`matchMedia`/`innerWidth` (clamp ÏÇÎá ÇáÍÒãÉ)¡ æãÎÑóÌ `Blade::render` íØÇÈŞ Çáãßæøä ÇáÌÏíÏ. **ÇáÓæíÊ ßÇãáÉ 348 äÇÌÍ (1268 assertions)** — ÕİÑ ÇäÍÏÇÑ.

**ãßÇäåÇ İí ÇáãÑÇÍá ÇáãäÌÒÉ:** ÇÓÊßãÇá/ÊÍÕíä áÌæáÉ «ÅÕáÇÍ ÏÑæÈ-ÏÇæä ÇáÔÑíØ ÇáÚáæí» ÇáãäÌÒÉ ÃÚáÇå (äİÓ Çáãßæøä ÇáãÔÊÑß `components/edz/dropdown.blade.php` æÒÑí ÇáäÇİÈÇÑ) — áÇ ãÑÍáÉ ÌÏíÏÉº ÊõÚÏø ÌÒÁğÇ ãä ãÑÍáÉ æÇÌåÉ «ÇáÅÔÚÇÑÇÊ + ŞÇÆãÉ ÇáãÓÊÎÏã» ÇáãäÌÒÉ.

## ÒÑ «İáÇÊÑ» ÇáãæÍøÏ İí ÔÑíØ ÇáØáÈíÇÊ (ŞÑÇÑ ÇáãÓÊÎÏã: ÈæÈ ÃÈ + ÅÛáÇŞ İæÑ ÇáÇÎÊíÇÑ) — 2026-09-06 ?

**ÇáØáÈ:** ÅÎİÇÁ İáÇÊÑ ÇáÔÑíØ ÇáËáÇËÉ (ÇáãÕÏÑ / äæÚ ÇáÊæÕíá / ÔÑßÉ ÇáÔÍä) Îáİ ÒÑ æÇÍÏ ÈÇÓã «İáÇÊÑ» íİÊÍ ÈæÈ ÃÈ İíå ÇáİáÇÊÑ ÈÏáğÇ ãä ÊÔæíÔ ÇáÔÑíØ (ÊÃííÏ ÃæÈá: ÊŞáíá ÇáÒÎÑİÉ æÊÃÎíÑ ÇáÅÙåÇÑ). **ÇáŞÑÇÑ: ÅÛáÇŞ ÇáÈæÈ ÃÈ İæÑ ßá ÇÎÊíÇÑ.**

- **`components/edz/dropdown.blade.php`:** ÅÖÇİÉ prop ÇÎÊíÇÑíÉ `triggerClass` ÊõãÒÌ Úáì ÒÑ ÇáÜ trigger — ÇáÇÓÊÎÏÇãÇÊ ÇáÍÇáíÉ (ÅÔÚÇÑÇÊ/ãÓÊÎÏã) ÈáÇ ÊÛííÑ¡ æÈæÈ ÃÈ ÇáİáÇÊÑ íÓÊŞÈá `edz-btn edz-btn--ghost edz-btn--sm`.
- **`components/_dropdown.scss`:** ÅÚÇÏÉ åíßáÉ `.edz-dropdown__trigger` ÈÍíË ÊÚØøá ÇáÊÕİíÑ (padding/background/border) **ÅáÇ** ÚÈÑ `&:not(.edz-btn)` — İáÇ íÊÚÇÑÖ ãÚ ÃÒÑÇÑ `edz-btn` (ÇáÔÑíØ ÃÚáì æãäİÕá). ÅÖÇİÉ `.edz-dropdown__section` (İÇÕá Èíä ÇáãÌãæÚÇÊ) æ`.edz-dropdown__section-title` (ÚäæÇä Õİøí ÈÃÍÑİ ßÈíÑÉ).
- **`orders/index.blade.php` (ÔÑíØ ÇáÃÏæÇÊ 2625—2760):** ÇáÃÒÑÇÑ ÇáËáÇËÉ ÇáãÓÊŞáÉ ÇÓÊõÈÏáÊ ÈÜ `<x-edz.dropdown width="340px" trigger-class="edz-btn edz-btn--ghost edz-btn--sm">`: trigger = ÓÈíäÑ `setFilter` + ÃíŞæäÉ `funnel` + «İáÇÊÑ» (`merchant_panel.filters`) + **ÔÇÑÉ ÚÏø äÔØÉ** (`min-w-[1.25rem] rounded-full bg-accent-600 text-white`) ÚäÏ `>0` + Óåãº ßá İáÇÊÑ ÇáÚÏÏ ÇáËáÇËÉ ÚÈÑ `collect(['source','delivery_type','shipping_provider'])->filter(filled)`. ÇáãÍÊæì = ÑÃÓ æÑŞÉ ÓİáíÉ (ãŞÈÖ + ÚäæÇä + X) Úáì ÇáåÇÊİ İŞØ + 3 ãÌãæÚÇÊ ÈãİÇÊíÍ ÇáÊÑÌãÉ ÇáŞÇÆãÉº ÎíÇÑÇÊ «—» æŞíãåÇ ÈÕİæİ `edz-dropdown__item justify-between` ãÚ `check` (opacity ááÇÎÊíÇÑ ÇáÍÇáí) æÊÙáíá `bg-accent-surface text-accent-fg font-semibold` + `aria-pressed`. **ßá ÎíÇÑ**: `wire:click="setFilter(...)"` + `@click="close()"` (ÅÛáÇŞ İæÑ ÇáÇÎÊíÇÑ).
- **ÊÑÌãÇÊ:** `merchant_panel.filters` ×4 áÛÇÊ («İáÇÊÑ»/Filters/Filtros/Filtres) İí Ğíá `merchant_panel.php` áßá áÛÉ.
- **ãáÇÍÙÇÊ:** áÇ ÊÛííÑ İí ÇáÇÓÊÚáÇãÇÊ (`allProviders` ŞÇÆã)º ÊÍÓøä ÃÏÇÁ: áøæÍÉ + ÛãøÉ æÇÍÏÉ ÈÏá 3 (ÊÙåÑÇä ÚäÏ ÇáİÊÍ İŞØ) æåÏİ Livewire æÇÍÏ. ÇáÈæÈ ÃÈ ÇáãÚáóøŞ íÈŞì ÏÇÎá ÍÏæÏ ÇáÔÇÔÉ ÚÈÑ `edzDropdown` ÇáŞÇÆã (Âãä RTL/LTR ÈáÇ ÓßÑæá ÃİŞí — ãõËÈóÊ İí ÇáÌæáÉ ÇáÓÇÈŞÉ). ÔÑíØ ÇáÃÚãÏÉ/ÇáÓáÉ/ÇáÚÏÇÏ æÇáÔÇÑÇÊ ÃÓİá ÇáÔÑíØ ÈáÇ ÊÛííÑ.
- **ÃÏáÉ:** ? `view:cache` äÌÍ Úáì `index.blade.php` ÇáßÇãá¡ ? `Blade::render` ááãßæøä íÄßÏ æÖÚ `trigger-class` Úáì ÇáÒÑ¡ ? ÇáÚÈæÉ ÇáãÈäíøÉ ÊÍÊæí `.edz-dropdown__section(+)`/`__section-title` æ`:not(.edz-btn)` æ`edz-dropdown__trigger` æ`min-w-\[1.25rem\]` æ`bg-accent-600`¡ ? **ÇáÓæíÊ ßÇãáÉ 348 äÇÌÍ (1268 assertions)** — ÕİÑ ÇäÍÏÇÑ.

## İÍÕ ÔÇãá ááÊÃßÏ + ÇÓÊßãÇá ŞæÇÆã storefront-settings (ØáÈ ÇáÊÍŞøŞ) — 2026-09-06 ?

**İÍÕ ßá ÈäæÏ ŞÇÆãÉ ÇáãÑÇÌÚÉ ÂáíğÇ (áÇ ÇÚÊãÇÏğÇ Úáì ÇáÊæËíŞ) — æÇáäÊÇÆÌ:**

- **ÇáÃíŞæäÇÊ (ÈäÏ 1):** ŞÇÑäÊ ÂáíğÇ ÎÑíØÉ `components/edz/icon.blade.php` (95 ãİÊÇÍğÇ) ãÚ ßá `x-edz.icon name="…"` ÇáËÇÈÊÉ İí `resources/views` (83 ÇÓãğÇ ãÓÊÚãáğÇ) ? **ÕİÑ ÇÓã ãİŞæÏ**º ÇáÊÑÇÏİÇÊ (aliases) ÊõÍá ÚÈÑ `$aliases`¡ æÇáÇÓã ÛíÑ ÇáãÚÑøİ íŞÚ ÈÃãÇä Úáì `grid` ÇáÇÍÊíÇØíÉ. ÃÒÑÇÑ ÓÌá ÇáÃÍÏÇË (`clock`) æŞÇÆãÉ «ÇáãÒíÏ» (`ellipsis-horizontal`) ãõÚÑóøİÊÇä æÊõÚÑÖÇä İÚáğÇ.
- **ÊæÍíÏ ÇáÈæÈ-ÃæİÑÒ/ÇáŞæÇÆã İí ÇááæÍÉ (ÈäÏ 2):** ãÓÍ ÊÇã áÜ `livewire/merchant` — ÕİÍÇÊ ÇáØáÈíÇÊ (ÃÚãÏÉ/ÊÃßíÏ/ÍÇáÉ/ÅÑÓÇá/İáÇÊÑ/ÓÌá/ÇáãÒíÏ) æÔÑíØ ÇáÌãÇÚí (ÅÓäÇÏ) ßáåÇ æÑŞÉ ÓİáíÉ ãæÍøÏÉ İí ÇáãæÈÇíá + áæÍÉ ãËÈøÊÉ ?sm. **ÇáãäŞóøÕ ÇáæÍíÏ = ãäÊŞíÇ storefront-settings** İÃõßãáÇ ÈäİÓ ÇáäãØ ÇáãæÍøÏ (ÛãÑÉ `bg-black/40 backdrop-blur-sm` ÊõÛáŞ ÈÇáäŞÑ + ãŞÈÖ `h-1 w-10` + ÑÃÓ ÈÚäæÇä + `x-mark` + `pb-[env(safe-area-inset-bottom)]` + `max-h-[70vh]` + `rounded-t-2xl` + ÚæÏÉ ?sm ááæÍÉ ÇáãÚáøŞÉ): ? **ãäÊŞí ÇáãäÊÌ** (ÈÍË+ŞÇÆãÉ) İí `storefront-settings.blade.php`¡ ? **ÔÈßÉ ÇáÃíŞæäÇÊ** İí `social_proof.blade.php` (ÊÈŞì ÊİÊÍ ÃÚáì/ÃÓİá İí ÇáÏÓßÊæÈ ÍÓÈ `$i===2`). ÃßæÑÏíæä `section-editor` áíÓ ŞÇÆãÉ (ãÊÌÇåá). Êã ÇáÈäÇÁ (`npm run build` 7.13s) — ÇáÃÏæÇÊ ÇáÌÏíÏÉ (`col-span-7`/`sm:left-0`/`sm:overflow-hidden`/`grid-cols-7`) æõáøÏÊ İí ÇáÚÈæÉ.
- **ÇáÃÏÇÁ (ÈäÏ 3):** `OrdersPageQueryCountTest` ? **1 äÇÌÍ** (3 assertions): ÚÏø ÇÓÊÚáÇãÇÊ ÇáÈíÇäÇÊ ãÓØøÍ ãÚ äãæ ÇáÕİæİ (İÑŞ ?2¡ ÊÍãíá ãÓÈŞ ßÇãá ÈáÇ N+1). ãÑÇÌÚÉ ÓáÓáÉ ÇáãßÇÊÈ: `rebuildFormOffices` ÇÓÊÚáÇã æÇÍÏ + `with('city:id,name')` (áÇ N+1 áÃÓãÇÁ ÇáãÏä)º `StopdeskOfficeSync` ãÍÇÏËÉ API áãÑøÉ æÇÍÏÉ ÚäÏ ÊÛííÑ ÇáÔÑßÉ (ÎÇÑÌ Íãá ÇáÕİÍÉ — ãŞÈæá). `loadFilterCities`/`loadFilterStopdeskPoints` ÇÓÊÚáÇã æÇÍÏ áßá ãäåãÇ.
- **ÓáÓáÉ ÇáÊæÕíá İí ÇáäãæĞÌ æÇáİáÇÊÑ (ÈäÏ 4):** ÇáäãæĞÌ: ÔÑßÉ ? äæÚ (`changeDeliveryType`) ? æáÇíÉ (`loadCities`) ? ÈáÏíÉ ? ãßÊÈ (ŞÇÆãÉ¡ Ãæ **ÊËÈíÊ ÊáŞÇÆí ÚäÏ ãØÇÈŞ æÇÍÏ** `delivery_type==='stopdesk'`). ÇáİáÇÊÑ: æáÇíÉ ? ÈáÏíÉ (`loadFilterCities`) + ÔÑßÉ ? ãßÊÈ ÇÓÊáÇã (`loadFilterStopdeskPoints`). ßáÇ ÇáãÓÇÑíä ãÊÑÇÈØ æÕÍíÍ.
- **İÑÚ 29 ßÇãáğÇ (ÈäÏ 5):** ÇÓÊåÏÇİ **51 äÇÌÍÉ (189 assertions)** ÚÈÑ ãáİÇÊ ãÑÇÍá 29.1–29.7 (29.1 `OrderEventLogVisibilityTest`¡ 29.2 `DirectSendConfirmedOrderTest`¡ 29.3 `BulkSendCarrierGroupingTest`¡ 29.4 `OrdersTrackingColumnTest`¡ 29.5 `OrderDuplicateBadgeTest`+`OrderDuplicateDetectionTest`¡ 29.6 subdivision¡ 29.7 `OrdersMobileMoreMenuTest` + äŞÍÉ 29.4b `TrackingStatusHistoryPopupTest`) — æÅä ßÇä İÍÕ ÇáÃÏÇÁ `OrdersPageQueryCountTest` äÇÌÍğÇ ÃÚáÇå.
- **ÇáÊÍŞøŞ ÇáÎÊÇãí:** ÇáÓæíÊ ßÇãáÉ **348 äÇÌÍ (1268 assertions)** — ÕİÑ ÇäÍÏÇÑ ÈÚÏ ÊÚÏíáÇÊ storefront-settingsº `view:clear` + `view:cache` (ÈáÇ ÇÓÊÑÌÇÚ git) æ`npm run build` ÓáíãÇä.

## ÅÕáÇÍ «ÇáİáÇÊÑ ÇáäÔØÉ» ßÑöÆÇÊ ÈÃÓãÇÁ ÇáÍŞæá — 2026-09-06 ?

**ÚÑÖ ÇáãÓÊÎÏã:** ÔÑíØ «ÇáİáÇÊÑ ÇáäÔØÉ» ÃÓİá ÔÑíØ ÇáØáÈíÇÊ íÚÑÖ ŞíãğÇ ãÌÑÏÉ Ïæä ÃÓãÇÁ ÇáÍŞæá¡ æÈÚÖ ÇáİáÇÊÑ (ÇáãÕÏÑ/ÇáãäÊÌ/ÇáãÈáÛ) áÇ ÊáÍŞ ÔÇÑÇÊåÇ ÃÕáğÇ.

- ÃõÚíÏ ÈäÇÁ ßÊáÉ «ãáÎÕ ÇáİáÇÊÑ ÇáäÔØÉ + ãÓÍ Çáßá» ßÇãáÉ: ßá ÔÑíÍÉ ÊÚÑÖ ÇáÂä **ÇÓã ÇáÍŞá + ÇáŞíãÉ** (`<span class="font-semibold opacity-75">{{ __('…') }}:</span>` + ÇáŞíãÉ ÈÍÑøÇÓ `max-w-[14rem] truncate` + `ps-2`). ÎÑíØÉ ÃÓãÇÁ ÈÜ 4 áÛÇÊ: `merchant_panel.status/state/city/office/assigned_agent/confirmed_by/send_from_carrier_warehouse/date/amount/weight/product/address/notes/shipment_type/home_delivery_label/stop_desk_label/store`¡ `order_flow.filter_provider`¡ `storefront.delivery_type/home_delivery/stop_desk`¡ `merchant.delivery_man`¡ `buttons.yes/no` (ÊÍŞøŞ Âáí: ÇáãİÇÊíÍ ×4 áÛÇÊ ãæÌæÏÉ).
- ÔÑÇÆÍ ÌÏíÏÉ: **ÇáãÕÏÑ** (ãÊÌÑ ? ãæÕøá ÚÈÑ `merchant_panel.store` / `merchant.delivery_man`)¡ **ÇáãäÊÌ** (íÊÚÈøÃ ãä `filters.product` ÇáäÕí¡ æÅáÇ ÇÓã ÇáÕäİ ÇáÃæá ãä `filterProducts` ÇáãØÇÈŞ áÜ`product_id`)¡ **ÇáãÈáÛ** (ãÏì). ÍÑøÇÓ ÇáŞíã: áÇ ÊõÚÑÖ ÔÑíÍÉ İÇÑÛÉ ÃÈÏğÇ (`?? $this->filters[...]`).
- ÈæÇÈÉ ÇáÔÑÇÆÍ: ÇÓÊÈÏÇá `array_filter($this->filters)` ÈÜ `$hasActiveFilters` (ãÚÇáÌÉ ÎÇÕÉ: `send_from_carrier_warehouse` ÊõÚÏø äÔØÉ ÚäÏ `$v !== null` æáíÓ truthy — İÊÈŞì `false` ÙÇåÑÉ ßÔÑíÍÉ «ÅÑÓÇá ãä ãÎÒä ÇáäÇŞá: áÇ»).
- **ÃÏáÉ:** `view:cache` (FRESH-CACHE-OK) + `npm run build` 13.05s + ÇáÃÏæÇÊ ÇáÌÏíÏÉ (`pe-2`/`ps-2`/`max-w-\[14rem\]`/`truncate`) æõáøÏÊ İí ÇáÚÈæÉ ÇáãÈäíøÉ. ÇáÓæíÊ ÈÚÏåÇ **350 äÇÌÍ (1278 assertions)** — ÕİÑ ÇäÍÏÇÑ.

## Phase 30 — İÑÚ 30.1 ? (ÔÑßÉ ÇáÔÍä áØáÈíÇÊ ÇáÊæÕíá ÇáãäÒáí) — 2026-09-06

**ÇáÊÔÎíÕ (ÊÍŞøŞ ãä ÇáÔíİÑÉ ÇáİÚáíÉ áÇ ÇáäÕæÕ):** ? ÈäíÉ ÇáãæÏÇá ßÇäÊ ŞÏ ÃõÚíÏ ÊÑÊíÈåÇ ÓÇÈŞğÇ — ÍŞá ÇáÔÑßÉ **ÎÇÑÌ** ßÊáÉ stopdesk æíÙåÑ ááÜ home ÃÕáğÇ (ÇáãßÊÈ İŞØ ãÔÑæØ ÈÜ`delivery_type==='stopdesk'`)º ? ŞæÇÚÏ ÇáÊÍŞŞ ÕÍíÍÉ ÃÕáÇğ (`shipping_provider_id` `required_if:delivery_type,stopdesk`/`nullable|exists`¡ `stopdesk_point_id` `required_if:delivery_type,stopdesk` — áã ÊõÑÎó æáã ÊõÔÏóøÏ)º ? **ÇáÎáá ÇáÍŞíŞí:** ãÓÇÑ ÇáÅäÔÇÁ ßÇä íİÑÖ `shipping_provider_id = null` ßáãÇ ßÇä `delivery_type === 'home'` (ÓØÑ 2340) — İÇÎÊíÇÑ ÇáÔÑßÉ İí ÇáãæÏÇá áØáÈíÉ home ßÇä íõåãóá ÚäÏ ÇáÍİÙ¡ ÈíäãÇ ãÓÇÑ ÇáÊÚÏíá ßÇä íÍİÙå (ÓØÑ 2495) ? ÊäÇŞÖº ? `loadFormOffices` ßÇä íÒÇãä/íÈäí ãßÇÊÈ ÍÊì ááÜ home (ÇÓÊÏÚÇÁ ÔÈßÉ ÈáÇ İÇÆÏÉ).

**ãÇ Êóãø:**
- `submitCreate`: íÍİÙ ÇáÔÑßÉ áßáÇ ÇáäæÚíä (`?: null`)¡ æÇáãßÊÈ íÈŞì ãÔÑæØğÇ ÈÜ stopdeskº ÇáÊÚáíŞ ãõÍÏóøË.
- `loadFormOffices`: ÍÇÑÓ ãÈßøÑ — Åä ßÇä ÇáäæÚ ÛíÑ stopdesk ? íİÑøÛ `formOffices` æ`stopdesk_point_id` æíõÑÌÚ (áÇ ÊÍãíá ãßÇÊÈ ááÜ home).
- `changeDeliveryType`: ÇáÇäÊŞÇá Åáì stopdesk ãÚ ÔÑßÉ ãÎÊÇÑÉ ? ÇÓÊÏÚÇÁ `loadFormOffices(preserveOffice: true)` (ŞÇÆãÉ ãßÇÊÈ ãõÒÇãóäóÉ ãõİÚóøáÉ)¡ æÈáÇ ÔÑßÉ ? `rebuildFormOffices`º ÇáÇäÊŞÇá Åáì home ? íãÓÍ ÇáãßÊÈ æÎíÇÑÇÊå.
- ÈáÇ ãİÇÊíÍ ÊÑÌãÉ ÌÏíÏÉ (æÇÌåÉ HTML ÊÛíøÑÊ İŞØ). áã ÊõáãóÓ ŞæÇÚÏ ÇáÊÍŞŞ (ãäÚñ ááÊÔÏøÏ/ÇáÊÎİíİ).

**ÇÎÊÈÇÑÇÊ ÇáŞÈæá (ÌÏíÏÉ¡ ãáİ `OrderOfficeSelectionTest.php`):**
- «ÊõäÔÃ ØáÈíÉ ãäÒáíÉ ÈÇáÔÑßÉ ÇáãÎÊÇÑÉ æÈáÇ ãßÊÈ» — ÇáÊßÑÇÑ ÚÈÑ ÇáãæÏÇá¡ æÊÃßíÏ: home + `shipping_provider_id` ÛíÑ null (íõÍİÙ İÚáğÇ) + `stopdesk_point_id` null + ÇáÚáÇŞÉ `shippingProvider` ÓáíãÉ.
- «ÇÎÊíÇÑ ÔÑßÉ áØáÈíÉ home áÇ íÍãøá æáÇ íÔÊÑØ ãßÊÈğÇ» — ÇÓÊÏÚÇÁ `loadFormOffices` ÈäæÚ home: `formOffices == []` æ`stopdesk_point_id == ''` ÑÛã æÌæÏ äŞØÉ ÇÓÊáÇã.
- **ãÎÑóÌ İÚáí:** Çáãáİ ÇáãÓÊåÏİ **6 äÇÌÍ (29 assertions)** Ëã ÇáÓæíÊ ßÇãáÉ **350 äÇÌÍ (1278 assertions)** — ÕİÑ ÇäÍÏÇÑº `view:clear` + `view:cache` (FRESH-CACHE-OK).

### ÌæáÉ ÊÍÓíäÇÊ ÇáÊæÕíá 30.2 ? (ÓáÓáÉ ÇÎÊíÇÑ ÇáÊæÕíá ÈÇáÊÑÊíÈ + ÈÍË + äÇİĞÉ ÚÏá ÓÑíÚÉ + ÅÕáÇÍ ÏÑæÈ ÏÇæä)

**ŞÑÇÑ ÇáãÓÊÎÏã ÇáäåÇÆí (íÍá ÊÚÇÑÖ 30.2 ãÚ ÇáÓáÓáÉ ÇáãÚÊãÏÉ ÓÇÈŞğÇ):** ÇáÊÑÊíÈ ÇáãÚÊãÏ åæ äİÓå ÇáãæÌæÏ İí ÇáãæÏÇá: **ÔÑßÉ ? äæÚ ? æáÇíÉ ? ÈáÏíÉ ? ãßÊÈ** (İí ÇáÌÏæá æÇáÈæÈ ÃÈ ãÚğÇ). ãæÇÕİÉ «ÇáæÌåÉ ŞÈá ÇáÊæÕíá» ãáÛÇÉ/ãÚÏóøáÉ ÍÓÈ ÇáØáÈ. ÃÌæÈÉ ÇáÊæÖíÍ: ÇáÌÏæá íİÊÍ äÇİĞÉ ÓáÓáÉ ßÇãáÉº ÇáÈÍË ãÍáí İæÑí ÈáÇ ÇÓÊÏÚÇÁÇÊ ÎÇÏãº ãÚÇáÌÉ ÔÇãáÉ áãÔÇßá ÇáÏÑæÈ ÏÇæä.

**ãÇ Êóãø:**
- **ÊİÚíá ÇáÈÍË ÇáãÍáí** (`search` prop) İí ÇáŞæÇÆã ÇáÃÑÈÚ (`x-edz.select`) İí `order-form-modal.blade.php` (ÔÑßÉ/æáÇíÉ/ÈáÏíÉ/ãßÊÈ) + ÓáÓáÉ ÈÍË (searchable `filteredOptions` ÈİÍÕ label/hint) — ÈÍË İæÑí ÈáÇ ÎÇÏã.
- **äØÇŞ ÇáãßÇÊÈ ÈÇáÈáÏíÉ (ÅÕáÇÍ ãæÌæÏ):** `rebuildFormOffices` ßÇä íİáÊÑ ÈÇáÔÑßÉ æÇáæáÇíÉ İŞØ. ÃõÖíİ äØÇŞ ÇáÈáÏíÉ `city_id = X OR NULL` (ÇáãßÇÊÈ ÇáÅŞáíãíÉ ÛíÑ ÇáãŞÊÑäÉ ÈÈáÏíÉ ÊÙá ÙÇåÑÉ¡ æÊõÑÊóøÈ ÃæáğÇ ãØÇÈŞÉ ÇáÈáÏíÉ) — İíØÇÈŞ «ãßÇÊÈ ÇáÈáÏíÉ» ÇáãØáæÈ.
- **ÊÛííÑ ÇáÈáÏíÉ íÚíÏ ÈäÇÁ ÇáãßÇÊÈ:** ÃõÖíİ `wire:change="rebuildFormOffices()"` Åáì ŞÇÆãÉ ÇáÈáÏíÉ İí ÇáãæÏÇáíä.
- **äÇİĞÉ ÚÏá ÓÑíÚÉ ááÊæÕíá (ÌÏæá + ãæÈÇíá):** ÒÑ «ÊæÕíá» (ÃíŞæäÉ truck) İí ÎáíÉ ÃİÚÇá ÇáÏíÓßÊæÈ æŞÇÆãÉ «ÇáãÒíÏ» ááãæÈÇíá ? `partials/delivery-edit-modal.blade.php` ÈŞæÇÆã ÈÍË ÈÃÑÈÚ ÎØæÇÊ ÈÇáÓáÓáÉ äİÓåÇ. ØÑŞ Volt ÌÏíÏÉ: `openDeliveryModal` (íÓÊÚíÏ ÇáÔÑßÉ/ÇáäæÚ/ÇáæáÇíÉ/ÇáÈáÏíÉ/ÇáãßÊÈ ÚÈÑ `loadFormOffices(preserveOffice:true)` æíÍãá ÈáÏíÇÊ ÇáæáÇíÉ)¡ `closeDeliveryModal`¡ `saveDeliveryModal` (ÊÍŞŞ ãä ÍŞæá ÇáÊæÕíá İŞØ: ÔÑßÉ¡ äæÚ¡ æáÇíÉ¡ ÈáÏíÉ¡ ãßÊÈ ãÚ İÍæÕÇÊ ÊØÇÈŞ ÇáæáÇíÉ/ÇáÔÑßÉ/ÇáäæÚ) + ÅÚÇÏÉ `recalculateOrderShipping` + ÊæÓÊ + ÍÏË ÊÏŞíŞ `order_delivery_updated` (ŞÈá/ÈÚÏ/ÇáãÊÛíøÑÇÊ).
- **ÍãÇíÉ ÍÇáÉ ÇáØáÈíÉ:** ÇáäÇİĞÉ ÊõÑİÖ ááØáÈíÇÊ ÇáãÔÍæäÉ İãÇ ÈÚÏ (`cannot_edit_shipped`)¡ æÕöáÉ ÈÇáäãæĞÌ ÇáÑÆíÓí áÇ ÊÊÚÇÑÖ (áÇ íãßä İÊÍ ãæÏÇáíä ãÚğÇ).
- **ÊÍÕíä edz-select.js:** æÑŞÉ ÇáãæÈÇíá ÈÚÑÖ ãæÍøÏ (max 480px) + ÅÚÇÏÉ ÊãæÖÚ ËÇÈÊÉ ÚäÏ ÊãÑíÑ Ãí scroller/ÊÛííÑ ÍÌã ÇáäÇİĞÉ ÃËäÇÁ ÇáİÊÍ (ßÇäÊ ÊÊÌãÏ İí ãßÇäåÇ ÇáŞÏíã ÏÇÎá ÇáãæÏÇá ÇáãÊãÑöøÑ).
- ãİÇÊíÍ ×4 áÛÇÊ: `edit_delivery`, `delivery_updated`, `city_without_state`, `invalid_city_for_state`, `invalid_office`, `office_home_invalid`, `office_provider_mismatch`.

**ÇÎÊÈÇÑÇÊ ÇáŞÈæá (ÊæÓÚÉ `OrderOfficeSelectionTest.php` — 6 ÌÏíÏÉ):**
- äÇİĞÉ ÇáÊÚÏíá ÊÓÊÚíÏ ÇáÔÑßÉ/ÇáãßÊÈ/ÇáäæÚ/ÇáæáÇíÉ/ÇáÈáÏíÉ æÊÍãøá ÇáãßÊÈ.
- ÇáÊÈÏíá Åáì home íãÓÍ ÇáãßÊÈ æíÍİÙ ÇáÔÑßÉ.
- ÍİÙ äÇŞá æãßÊÈ ÌÏíÏíä.
- ÑİÖ ãßÊÈ áÇ íÊÈÚ ÇáäÇŞá (`office_provider_mismatch`).
- ÍÌÈ ÇáäÇİĞÉ ááØáÈíÇÊ ÇáãÔÍæäÉ.
- äØÇŞ ÇáãßÇÊÈ ÈÇáÈáÏíÉ: ÊÙåÑ ÈáÏíÉ ÇáãÎÊÇÑÉ + ÇáÅŞáíãíÉ¡ æáÇ ÊÙåÑ ÈáÏíÉ ÃÎÑì.
- **ãÎÑóÌ İÚáí:** Çáãáİ ÇáãÓÊåÏİ **12 äÇÌÍ (44+ assertions)** Ëã ÇáÓæíÊ ßÇãáÉ **356 äÇÌÍ (1301 assertions)** — ÕİÑ ÇäÍÏÇÑ. `view:cache` (FRESH-CACHE-OK) + `php -l` ÓáíãÉ + `npm run build` (6.96s).

## Phase 30 — İÑÚ 30.3 ? (æÒä ÊáŞÇÆí ãä ÇáÃÕäÇİ — íõÈŞì ŞÇÈáğÇ ááÊÚÏíá íÏæíğÇ) — 2026-09-06

**ÇáŞÑÇÑÇä (ÃÌæÈÉ ÇáãÓÊÎÏã):** ÇáÊÑÊíÈ ÇáãÚÊãÏ **ÔÑßÉ ? äæÚ ? æáÇíÉ ? ÈáÏíÉ ? ãßÊÈ** (íÈŞì ßãÇ åæ)¡ æÇáÈÏÁ ÈÜ**30.3 ÇáæÒä ÇáÊáŞÇÆí**.

**ÇáÊÔÎíÕ:** `form['weight_kg']` ßÇä ÍŞáğÇ íÏæíğÇ ãÓÊŞáğÇ Úä ÇáÃÕäÇİ — áÇ íõÍÏóøË ÚäÏ ÅÖÇİÉ/ÊÚÏíá ßãíÉ/ÍĞİ Ãí Õäİ¡ ÈíäãÇ ÇáãáÎÕ íÍÊÓÈ ÇáæÒä ãä ÇáÃÕäÇİ áÍÙíğÇ İŞØ ? ÇäÌÑÇİ Èíä ÇáÍŞá ÇáãÑİŞ æÇáæÒä ÇáãÍİæÙ İÚáíğÇ.

**ãÇ Êóãø (index.blade.php):**
- ÏÇáÉ `$recalcFormWeight` ÌÏíÏÉ: ãÌãæÚ `weight × quantity` ÚÈÑ `form['items']` (æÒä `variant->weight` ÇáãÖãóøä ÃÕáğÇ İí ßá Õäİ) ? `form['weight_kg'] = round(sum,3)` (Ãæ `''` Åä ßÇä ÕİÑğÇ).
- ÊõÓÊÏÚì ÈÚÏ ßá ÊÍæøÑ ÍŞíŞí İí ÇáÃÕäÇİ İŞØ: `addFormItem`¡ `addFormItemByBarcode` (ÇáãÓÇÑÇä: ÊßÑÇÑ + ÅÖÇİÉ)¡ `removeFormItem`¡ `updateFormItemQty` — **æáíÓ** `updateFormItemPrice` ? ÇáÊÚÏíá ÇáíÏæí ááæÒä áÇ íõßÊóÈ İæŞå ãÇ áã ÊÊÛíøÑ ÇáÃÕäÇİ İÚáğÇ.
- ãáÎÕ ÇáãæÏÇá (order-form-modal) ŞÑÃ äİÓ ÇáãÕÏÑ ÇáæÍíÏ: `$form['weight_kg']` ÈÏá ÅÚÇÏÉ ÇáÍÓÇÈ ãä ÇáÃÕäÇİ.
- ÊáãíÍ ÊÍÊ ÍŞá ÇáæÒä + ãİÊÇÍ ×4 áÛÇÊ `order_flow.weight_auto_hint` (ÚÑÈíÉ İÕÍì/EN/FR/ES) İí `order_flow.php` ÇáÃÑÈÚÉ.

**ÇÎÊÈÇÑÇÊ ÇáŞÈæá (ÌÏíÏÉ¡ ãáİ `tests/Feature/Merchant/OrderWeightAutoCalcTest.php` — 6):**
- `addFormItem` íÍÓÈ ÇáæÒä ÇáÊáŞÇÆí ãä æÒäóí ãÊÛíÑíä (0.5+2.25=2.75).
- `updateFormItemQty` íÖÑÈ ÇáæÒä İí ÇáßãíÉ (1.5×3=4.5).
- `removeFormItem` íÚíÏ ÇáÍÓÇÈ ÈÚÏ ÇáÍĞİ.
- `addFormItemByBarcode` íÍÓÈ ãä ÇáÔíİÑÉ.
- `updateFormItemPrice` áÇ íßÊõÈ İæŞ ÊÚÏíá íÏæí `weight_kg` (íÙá 9.5).
- ØáÈíÉ ÊõäÔÃ İÚáíğÇ İÊõÎÒóøä `weight_kg` ÇáãÍÓæÈ (2.75 ãÚ home).
- **ãÎÑóÌ İÚáí:** Çáãáİ ÇáÌÏíÏ **6 äÇÌÍ (13 assertions)**¡ æÇáãÌÇæÑÇÊ (ÇáßãíÇÊ/ÇáãßÇÊÈ/ÇáÊßÑÇÑ) **22 äÇÌÍ (76 assertions)**¡ æÇáÓæíÊ ßÇãáÉ **362 äÇÌÍ (1314 assertions)** — ÕİÑ ÇäÍÏÇÑ. `view:clear`+`view:cache` (FRESH-CACHE-OK) + `php -l` ÓáíãÉ áßá ÇáãáİÇÊ + `npm run build` (7.03s).

## Phase 30 — İÑÚ 30.2 ? (áÇ ÇÓÊÑÌÇÚ ÕÇãÊ áãßÊÈ ãÍÏÏ — ÇáÅÈØÇá ÈÊæÓÊ ãÑÆí) — 2026-09-06

**ÇáÓíÇŞ:** ÈÚÏ ÇÚÊãÇÏ 30.3¡ ÈŞÇíÇ 30.2 åí İŞØ ÖãÇä ÚÏã ãÓÍ ãßÊÈ ãÍÏÏ ãÓÈŞğÇ ÈÕãÊ ÚäÏ ÊÛííÑ ÇáæÌåÉ — ÅĞ Åä ÇáŞÑÇÑ ÇáãÚÊãÏ ÓÇÈŞğÇ íõÈŞí ÇáÊÑÊíÈ **ÔÑßÉ ? äæÚ ? æáÇíÉ ? ÈáÏíÉ ? ãßÊÈ** (ãæÇÕİÉ «ÇáæÌåÉ ŞÈá ÇáÊæÕíá» ãáÛÇÉ).

**ÇáÊÔÎíÕ:** İí `rebuildFormOffices`¡ ßÇäÊ ÇáæÌåÉ ÇáÌÏíÏÉ (æáÇíÉ/ÈáÏíÉ) ÊÓŞØ ÇáãßÊÈ ÇáãÍÏÏ ÓÇÈŞğÇ ãä äØÇŞ ÇáŞÇÆãÉ İßÇä ÇáİÑÚ `elseif` íİÑøÛ `stopdesk_point_id` ÈÕãÊ Ïæä Ãí ÅÔÚÇÑ.

**ãÇ Êóãø (index.blade.php):**
- ÇáÊŞÇØ `$wasSelected` Ãæá `rebuildFormOffices` ŞÈá ÅÚÇÏÉ ÇáÈäÇÁ.
- İí ÇáİÑÚ `elseif` (ÇáãßÊÈ ÇáãÍÏÏ ÎÇÑÌ äØÇŞ ÇáæÌåÉ ÇáÌÏíÏÉ): Åä ßÇä `$wasSelected` ÛíÑ İÇÑÛ æãÇ íÒÇá `form.stopdesk_point_id` ãØÇÈŞğÇ áå ? ÈË `swal:toast` (icon=warning) ÈÇáÑÇÈØ `__('order_flow.office_reset_for_destination')` ŞÈá ÇáÊİÑíÛ — Ãí ÅÈØÇá ãÑÆí ÈÏá ÇáÕÇãÊ.
- **áÇ íäØáŞ ÚäÏ:** ÇÎÊíÇÑ ÓÇÈŞ İÇÑÛ¡ Ãæ ÇáÇÓÊÈÏÇá ÇáÊáŞÇÆí ÇáãÔÑæÚ (ÈáÏíÉ ÈãßÊÈ æÇÍÏ ? `$cityOffices->count()===1` íÎÊÇÑ ŞÈá ÇáæÕæá ááÜ elseif)¡ Ãæ ÊÛííÑ ÇáÔÑßÉ (áÃä `loadFormOffices` ÊİÑøÛ ãÓÈŞğÇ)¡ Ãæ ãßÊÈ ãÇ íÒÇá Öãä ÇáäØÇŞ.
- ãİÊÇÍ ×4 áÛÇÊ `order_flow.office_reset_for_destination` (ÚÑÈíÉ İÕÍì/EN/FR/ES) İí `order_flow.php` ÇáÃÑÈÚÉ.
- ãæÏÇá ÇáÅäÔÇÁ æäÇİĞÉ ÇáÊÚÏíá ÇáÓÑíÚ (`delivery-edit-modal`) íÔÊÑßÇä İí äİÓ `rebuildFormOffices` ? ÇáÅÕáÇÍ íÛØí ÇáãÓÇÑíä ãÚğÇ.

**ÇÎÊÈÇÑÇÊ ÇáŞÈæá (ÊæÓÚÉ `OrderOfficeSelectionTest.php` — 3 ÌÏíÏÉ):**
- ÊÛííÑ ÇáÈáÏíÉ Åáì ÈáÏíÉ ÈáÇ ãßÇÊÈ: ÇáãßÊÈ íõİÑóøÛ + ÊæÓÊ warning ÈÇáãİÊÇÍ `office_reset_for_destination`.
- ÊÛííÑ ÇáæáÇíÉ Åáì æáÇíÉ ÈáÇ ãßÇÊÈ (ÚÈÑ `loadCities`): ÇáãßÊÈ íõİÑóøÛ + äİÓ ÇáÊæÓÊ.
- ãßÊÈ ãÇ íÒÇá İí ÇáäØÇŞ (ÈáÏíÉ ÈãßÊÈóíä): ÇáãÎÊÇÑ íÈŞì + **áÇ** ÊæÓÊ (`assertNotDispatched`).
- **ãÎÑóÌ İÚáí:** ÇáãáİÇä ãÚğÇ (ãßÇÊÈ + æÒä) **21 äÇÌÍ (74 assertions)**¡ æÇáÓæíÊ ßÇãáÉ **365 äÇÌÍ (1323 assertions)** — ÇÑÊİÇÚ ãä 362/1314 ÈáÇ ÇäÍÏÇÑ. `php -l` ÓáíãÉ áßá ÇáãáİÇÊ + `view:clear`+`view:cache` (FRESH-CACHE-OK).

## Phase 30 — İÑÚ 30.4 ? (ÏãÌ ÇáÔÈßÉ ÇáãÇáíÉ — partial ãÔÊÑß ÈãÕÏÑ æÇÍÏ áßá ãÓÇÑí ÇáÅäÔÇÁ æÇáÊÚÏíá) — 2026-09-06

**ÇáŞÑÇÑ (ÇÚÊãÇÏ ÇáãÓÊÎÏã «Çßãá»):** ÏãÌ ÇáãáÎÕ ÇáãÇáí áãæÏÇá ÇáØáÈ İí ÔÈßÉ æÇÍÏÉ ãÊÌÇæÈÉ (5 ÎáÇíÇ ŞÑÇÁÉ İŞØ) ÊÎÏã ãæÏÇáí ÇáÅäÔÇÁ æÇáÊÚÏíá ãÚğÇ¡ áÊÕÈÍ ÇáãÕÏÑ ÇáæÍíÏ ááÃÑŞÇã ÇáãÇáíÉ ÇáãÚÑæÖÉ.

**ãÇ Êóãø:**
- ÌÒÁ ãÔÊÑß ÌÏíÏ `partials/order-financial-summary.blade.php` íÍÓÈ ÈäİÓå: `subtotal` (ŞÑÇÁÉ)¡ `total_weight` ãä `form.weight_kg` (ŞÑÇÁÉ)¡ `delivery_cost` (ŞÑÇÁÉ İŞØ ÈãËÇÈÉ ÇáãÑÂÉ — íõÍÓÈ ÚÈÑ `ShippingCostCalculator` äİÓå ÇáãÓÊÎÏã ÚäÏ ÇáÍİÙ¡ æíŞÊÕÑ Úáì `delivery_type=home` ãÚ ÊÍÏíÏ æáÇíÉº æÅáÇ «ãÌÇäí»)¡ `discount` (ŞÑÇÁÉ)¡ `grand_total = max(0, subtotal ? discount)`.
- ÇáÔÈßÉ ÇáãÊÌÇæÈÉ: **1 ÚãæÏ @375 ? 2 ÚãæÏ @768 (`md:grid-cols-2`) ? 5 ÃÚãÏÉ @1440 (`min-[1440px]:grid-cols-5`)** ãÚ ÎáíÉ ÇáÅÌãÇáí ããíÒÉ (`bg-brand-surface`).
- ãÍÑÑ ÇáÎÕã ÇáÊİÇÚáí (select + ÇáŞíãÉ + ÇáÓÈÈ) ÈŞí ßÇãáğÇ ÃÓİá ÇáÔÈßÉ ÏÇÎá ÇáÌÒÁ — æÇáÊŞííã ÇáãÚÑæÖ ãŞÇÈá ÇáÎáíÉ ŞÑÇÁÉ İŞØ.
- `order-form-modal.blade.php`: ÇáßÊáÉ ÇáãÇáíÉ inline ÇáŞÏíãÉ (ßÇäÊ ÊŞíÓ æÊßÑÑ ÇáÍÓÇÈ) ÇÓÊõÈÏáÊ ÈÜ `@include(livewire.merchant.orders.partials.order-financial-summary)` — äİÓ ÇáÌÒÁ íÎÏã `$showCreateModal` æ`$showEditModal` ãÚğÇ.
- **ÅÕáÇÍ ãÕÇÍÈ (ÇáİÌæÉ ÇáÙÇåÑÉ):** `OrderService::createManual` áã íßä íÎÒøä ÍŞæá ÇáÎÕã ÅØáÇŞğÇ (ÎáÇİğÇ áãÓÇÑ ÇáÊÚÏíá) — İßÇä ÇáÅÌãÇáí ÇáãÚÑæÖ İí ÇáÔÈßÉ áÇ íõØÇÈŞ ÇáãËÈóøÊ ááØáÈ ÇáãÍÏæÏ. ÃõÖíİÊ `discount_type` / `discount_value` / `discount_reason` Åáì ÇáÅäÔÇÁ ÈÍíË íÊØÇÈŞ ÇáãÕÏÑ ÇáæÍíÏ ãÚ ãÇ íõÍİÙ. (áÇ ÊÚÑíİ ÌÏíÏ: `total_amount` íÈŞì ãÇ ŞÈá ÇáÎÕã — ÇáÊÎİíÖ íõÎÒä ÈÔßá ãäİÕá ÈÍÓÈ ÊÕãíã `getGrandTotalAttribute`¡ æÊÃßøÏ ÇáÇÎÊÈÇÑ Ãä `grand_total` = 400 ãÚ `total_amount` = 500 æÎÕã 100.)

**ÇÎÊÈÇÑÇÊ ÇáŞÈæá (`OrderFinancialSummaryTest.php` — 7 ÌÏíÏÉ):**
- ÇáÔÈßÉ ÇáãÔÊÑßÉ ÊõÚÑÖ İí ãæÏÇá ÇáÅäÔÇÁ ãÚ ÚäÇÕÑ `data-financial-*` æÇáİæÇÕá ÇáãÊÌÇæÈÉ (`md:grid-cols-2` + `min-[1440px]:grid-cols-5`).
- ÇáÔÈßÉ ÊÊÈÚ ÊÛíøÑ ÇáßãíÉ ãÈÇÔÑÉ (ãÕÏÑ æÇÍÏ): 350 ? ×4 = 1,400.
- ÎÕã äæÚ `amount` íÙåÑ İí ÇáÔÈßÉ æíõÍİÙ Úáì ÇáØáÈ ÇáãÍÏæË (`discount_type=amount`, `discount_value=100`, `total_amount=500`, `grand_total=400`).
- ÎÕã `percent` 10% Úáì 1,000 íõÍæóøá Åáì ãÈáÛ (ÎÕã 100 / ÅÌãÇáí 900).
- ÊßáİÉ ÇáÊæÕíá ááŞÑÇÁÉ İŞØ: ãÚ `DeliveryRate` ãõÚáä (Ecotrack + ÇáæáÇíÉ) ÊõÚÑÖ 400.00 DZD İí ÎáíÉ `data-financial-delivery`.
- ÇáÎáíÉ ÊÈŞì «ãÌÇäí» ÚäÏ ÛíÇÈ Ãí ÊÓÚíÑÉ (æáÇíÉ + ÈáÏíÉ ÈÏæä rate).
- ãæÏÇá ÇáÊÚÏíá íÚíÏ ÇÓÊÎÏÇã äİÓ ÇáÔÈßÉ (ãÕÏÑ æÇÍÏ) æíõÙåÑ ÇáÅÌãÇáí ãä ÇáÃÕäÇİ (1,500).
- **ãÎÑóÌ İÚáí:** `OrderFinancialSummaryTest` **7 äÇÌÍ (30 assertions)**º ÇáãáİÇÊ ÇáãÊÃËÑÉ ãÚğÇ (ãáÎÕ ãÇáí + ãßÇÊÈ + æÒä + ÚÏøÇÏ ÇÓÊÚáÇãÇÊ + ÅÚÏÇÏÇÊ ÊæÕíá) **36 äÇÌÍ (166 assertions)**º ÇáÓæíÊ ßÇãáÉ **372 äÇÌÍ (1353 assertions)** — ÇÑÊİÇÚ ãä 365/1323 ÈáÇ ÇäÍÏÇÑ. `php -l` ÓáíãÉ + `view:clear`+`view:cache` (FRESH-CACHE-OK).

---

## Phase 30 — İÑÚ 30.5 ? (ÔÈßÉ ÇáÚãíá/ÇáÚäæÇä 4 ÃÚãÏÉ — 1 @375 / 2 @768 / 4 @1440) — 2026-09-06

**ãÇ Êóãø:**
- ÏãÌ ŞÓã ÇáÚãíá + ÇáÚäæÇä İí `order-form-modal.blade.php` Åáì ÔÈßÉ æÇÍÏÉ: `grid grid-cols-1 md:grid-cols-2 min-[1440px]:grid-cols-4 gap-4` ÊÍãá 4 ÎáÇíÇ: ÇáÇÓã¡ ÇáåÇÊİ¡ ÇáåÇÊİ ÇáËÇäæí¡ ÇáÚäæÇä. (ßÇäÇ ŞÓãíä ãäİÕáíä: ÇáÔÈßÉ ÇáŞÏíãÉ `sm:grid-cols-2` ÈÜ 3 ÎáÇíÇ + ÍŞá ÚäæÇä ãäİÕá.)
- ÍõĞİ ßÊáÉ ÇáÚäæÇä ÇáãäİÕáÉ ÇáÃÕáíÉ (~ÓØÑ 128-132) áÅÒÇáÉ ÇáÊßÑÇÑ.
- ÇáÓáÇÓáÉ ÇáãÚÊãÏÉ (ÔÑßÉ ? äæÚ ? æáÇíÉ ? ÈáÏíÉ ? ãßÊÈ) áã ÊÊÛíøÑ.

---

## Phase 30 — İÑÚ 30.6 ? (ÎÑíØÉ äæÚ ÇáØáÈ NOEST: delivery=1 / exchange=2 / pickup=3 + ÊæËíŞ remboursement) — 2026-09-06

**ãÇ Êóãø:**
- ÃõÖíİÊ ØÑíŞÉ `NoestIntegrationAdapter::typeId()` ÊÍæøá `order->shipment_type` Åáì ÑŞã NOEST: `'delivery'` ? 1¡ `'exchange'` ? 2¡ `'pickup'` ? 3¡ Ãí ÔíÁ ÂÎÑ ? 1 (aaliri ÇİÊÑÇÖí).
- `'type_id' => 1` ÇáËÇÈÊ İí `createOrder` ÇÓÊõÈÏá ÈÜ `'type_id' => $this->typeId($order)`.
- `'remboursement' => 0` ÈŞí ËÇÈÊğÇ ãÚ ÊÚáíŞ ÊæÖíÍí: ÇáäãæĞÌ áÇ íÏÚã İí ÇáæŞÊ ÇáÍÇáí Óæì CODº remboursement=1 ãÎÕÕ áÊÏİŞÇÊ ÇáÇÓÊÑÏÇÏ/code_pos. (æËøŞ ÇáãÓÊÎÏã åĞÇ sebagai fawqa nazariyah — İÌæÉ ãŞÕæÏÉ.)
- `NoestIntegrationTest`: ÇáÇÎÊÈÇÑ ÇáŞÇÆã `createOrder posts the NOEST payload` íÍŞŞå áÃä ÇáØáÈ İí ÇáÇÎÊÈÇÑ ÈÜ `shipment_type = 'delivery'` ? `type_id = 1` ?.

---

## Phase 30 — İÑÚ 30.7 ? (ÊÚÏíá ÇÓã ÇáÚãíá inline — start/save/cancel + ÊÍŞŞ + audit) — 2026-09-06

**ãÇ Êóãø:**
- `$startOrderNameEdit($orderId)` / `$saveOrderName` / `$cancelOrderNameEdit` + ÍÇáÉ `$nameEditName` — ÊØÈíŞ ÊãÇãğÇ áäãØ ÊÚÏíá ÇáåÇÊİ ÇáÓØÑí (`$phoneEditPhone`) ãÚ ÇáÜ trait `HasInlineEdit`.
- `$saveOrderName`: ÊÍŞŞ `required|string|max:255` + `customer.update(['name' => ...])` + `writeInlineAudit(event: 'order_customer_name_updated')` + ÊæÓÊ `merchant_panel.name_updated`.
- ÎáíÉ ÇáÚãíá İí ÌÏæá ÓØ ÇáãßÊÈ: ÒÑ `.edz-inline-edit__display` ÈÃíŞæäÉ ÇáŞáã íİÊÍ `startOrderNameEdit` ? ÍŞá ÅÏÎÇá + ÒÑ ÍİÙ/ÅáÛÇÁ.
- ÇáãæÈÇíá (ÇáÈØÇŞÉ): ÇáÇÓã íÊÍæá ÃíÖğÇ Åáì ÇáÒÑ ÇáŞÇÈá ááÊÚÏíá ÚäÏ ÇáÕáÇÍíÉ.
- ÊÑÌãÇÊ ÌÏíÏÉ: `name_updated` × 4 áÛÇÊ {en: 'Customer name updated', ar: 'Êã ÊÍÏíË ÇÓã ÇáÒÈæä', fr: 'Nom du client mis à jour', es: 'Nombre del cliente actualizado'}.

---

## Phase 30 — İÑÚ 30.8 ? (ÅÚÇÏÉ ÊÑÊíÈ ÇáÃÚãÏÉ ÇáËÇäæíÉ + ÍİÙ ÇáÊÑÊíÈ + ÚÑÖ ÏíäÇãíßí) — 2026-09-06

**ãÇ Êóãø:**
- **ÇáäãÇĞÌ:** `visibleColumns` = `primaries (ËÇÈÊÉ ÈÇáÊÑÊíÈ ÇáÃÕáí)` + `secondaries (ÈÇáÊÑÊíÈ ÇáãÎÒøä)` — íÊæÇİŞ ãÚ ÇáäãæĞÌ ÇáãæËøŞ İí `loadColumnPreferences` ("Primary columns are always forced; only secondary columns are configurable").
- **ÚÑÖ ÇáÌÏæá:** headers æ body cells ááÃÚãÏÉ ÇáËÇäæíÉ ÊõÚÑÖ ÇáÂä ÚÈÑ `@foreach ($visibleSecondaryKeys as $secondaryKey) @switch($secondaryKey) @endswitch` ÈÏáÇğ ãä ËæÇÈÊ İí Blade — İŞØ ÈÚÏ ÂÎÑ primary ËÇÈÊ¡ ŞÈá ÚãæÏ ÇáÅÌÑÇÁÇÊ.
- **ÅÕáÇÍ İÌæÉ ÓÇÈŞÉ:** `shipping_cost` ßÇä ÈÏæä header `<th>` ? ÃõÖíİ ÇáÂä `th` ÈÓíØ İí ÇáÜ switch (ÅÕáÇÍ ãÎİí ááÇäÍÑÇİ Èíä ÇáÃÚãÏÉ?? enabled).
- **ÊÈæíÈ ÅÚÏÇÏÇÊ ÇáÌÏæá:** ÇáÃÚãÏÉ ÇáËÇäæíÉ ÊõÑÊóøÈ ÍÓÈ ÇáÊÑÊíÈ ÇáÍÇáí İí `draftColumns` (Checked ÃæáÇğ ÈÇáÊÑÊíÈ¡ Ëã Unchecked). ßá ÚãæÏ ËÇäæí ãİÚøá íÍãá ÃÒÑÇÑ `arrow-up`/`arrow-down` + ÑŞã ÊÑÊíÈ (`tabular-nums`).
- `$moveDraftColumn($column, 'up'|'down')`: íõÚíÏ ÊÑÊíÈ `secondaries` ÏÇÎá `draftColumns` ãÚ ÇáÍİÇÙ Úáì `primaryKeys` İí ãæÖÚåÇ ÇáËÇÈÊ.
- ÊÑÌãÇÊ ÌÏíÏÉ: `column_order_hint` × 4 áÛÇÊ.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `php -l` ÓáíãÉ + `view:clear`+`view:cache` (FRESH-CACHE-OK)º ÇáÓæíÊ ßÇãáÉ **372 äÇÌÍ (1353 assertions)** — ÕİÑ ÇäÍÏÇÑ ãä ÇáÅÕÏÇÑ 372/1353 ÇáÓÇÈŞ.

---

## Phase 30 — ãáÎÕ ÇáÅäÌÇÒ ÇáßÇãá (30.1–30.8)

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 30.1 ÔÑßÉ ÇáÔÍä | ? | NoestIntegrationAdapter | — |
| 30.2 ÓáÓáÉ ÇáÊæÕíá | ? | index.blade + livewire state | 15 İí OrderOfficeSelectionTest |
| 30.3 æÒä ÊáŞÇÆí | ? | order-form-modal + index | 6 İí OrderWeightAutoCalcTest |
| 30.4 ÔÈßÉ ãÇáíÉ | ? | order-financial-summary partial + OrderService | 7 (30) İí OrderFinancialSummaryTest |
| 30.5 ÔÈßÉ ÇáÚãíá/ÇáÚäæÇä | ? | order-form-modal.blade | — |
| 30.6 ÎÑíØÉ äæÚ NOEST | ? | NoestIntegrationAdapter + docs | — (ÇáÇÎÊÈÇÑ ÇáŞÇÆã íÍŞŞå) |
| 30.7 ÊÚÏíá ÇÓã ÇáÚãíá | ? | index.blade + 4 ãáİÇÊ ÊÑÌãÉ | — |
| 30.8 ÅÚÇÏÉ ÊÑÊíÈ ÇáÃÚãÏÉ | ? | index.blade + moveDraftColumn + 4 ãáİÇÊ ÊÑÌãÉ | — |
| **ÇáÅÌãÇáí** | **372 äÇÌÍ (1353 assertions)** | | |

---

## Phase 30 — İÑÚ 30.9 ? (ÅÕáÇÍ Toast + ÚãæÏ ÇáãÕÏÑ + ÅÚÇÏÉ ÈäÇÁ ÇáÌÏæá ÈÊÑÊíÈ ßá ÇáÃÚãÏÉ + ÓÍÈ æÅİáÇÊ) — 2026-09-06

**ãÇ Êóãø (ÈãæÇİŞÉ ÕÑíÍÉ ãä ÇáãÓÊÎÏã):**
- **ÅÕáÇÍ Toast (ÌĞÑ ÇáãÔßáÉ):** `resources/js/swal.js` íÓÊãÚ ÇáÂä áßá ãä `swal` æ`swal:toast` ãÚ ÊØÈíÚ `icon`?`type`¡ æÃõÖíİ ãÓÊãÚ `failed-validation` (Livewire 3) áÊæÓÊ ÎØÃ ÚäÏ İÔá ÇáÊÍŞŞ ãä ÇáäãæĞÌº ÇáÈäæÏ `@error` ÇáÜ inline ÊÈŞì ÇáÖãÇä ÇáÃÓÇÓí (ãæÌæÏÉ İí `order-form-modal`).
- **ÚãæÏ ÇáãÕÏÑ:** ÃõÖíİ `source` ááÓÌá (ÇİÊÑÇÖí¡ ËÇäíÇğ): Manual = `created_by_membership_id` ããáæÁ¡ Store = İÇÑÛ. ÈÇŞíÉ İí ÇáÑÃÓ/ÇáÎáíÉ (`orders-table-header` + `orders-table-cell` partials) æÇáßÇÑÊ ÇáãæÈÇíá (ÔÇÑÇÊ `merchant.delivery_man` / `merchant_panel.store`).
- **ÅÒÇáÉ ÃÚãÏÉ:** ÍõĞİ `tracking_status` æ`confirmed_by` ãä ÓÌá `orderColumns()` (ØáÈÇÊ ÇáãÓÊÎÏã ÇáŞÏíãÉ: áÇ tracking/confirmed/repeat İí ÇáÌÏæá).
- **ÇáÊÑÊíÈ ÇáÇİÊÑÇÖí ÇáÌÏíÏ (14):** number, source, customer, phone, products, amount, weight, shipment_type, wilaya, status, assigned_agent, created_at, confirmation_attempts, last_contact.
- **äãæĞÌ ŞÇÆãÉ ßÇãáÉ:** `visibleColumns` = ŞÇÆãÉ ãÑÊÈÉ ßÇãáÉ ÊõÍİÙ/ÊõÓÊÚÇÏ ßÇãáÉ İí `UserColumnPreference` — áÇ ÊŞÓíã primary/secondary. ÃõÚíÏÊ ßÊÇÈÉ `loadColumnPreferences`, `saveColumnPreferences`, `saveTableSettings`, `moveDraftColumn`.
- **ÅÚÇÏÉ ÈäÇÁ ÇáÌÏæá:** `<thead>` æ`<tbody>` íõÚÑÖÇä ÈÍáŞÉ æÇÍÏÉ `@foreach ($this->visibleColumns as $colKey)` ãÚ partials (`orders-table-header`, `orders-table-cell`) — ÃõÒíáÊ ßá ßÊá `@if (in_array(...))` æ`visibleSecondaryKeys`.
- **ÅÚÏÇÏÇÊ ÇáÌÏæá:** ŞÇÆãÉ ãæÍÏÉ áßá ÇáÃÚãÏÉ (checkbox + ÑŞã ÊÑÊíÈ + ÃÒÑÇÑ ?? + ãŞÈÖ ÓÍÈ) — ßá ÇáÃÚãÏÉ ŞÇÈáÉ ááÊÑÊíÈ.
- **ÓÍÈ æÅİáÇÊ:** `orderColumnReorderDraft()` İí `resources/js/components/order-column-reorder.js` (HTML5 DnD¡ ÅÔÇÑÉ ãæÖÚ ŞÈá/ÈÚÏ¡ ÅÑÓÇá ÇáØáÈ ÇáÌÏíÏ ÏİÚÉ æÇÍÏÉ Åáì `reorderDraftColumns`). ÃõÒíá ÇáÑÇÈØ Çáæåãí `orderColumnReorder()` ãä `<table>`.
- **PHP ÌÏíÏ:** `$reorderDraftColumns(array $keys)` — íÎÕøÕ ÇáãİÇÊíÍ ÇáÕÇáÍÉ æíÒíá ÇáÊßÑÇÑ¡ ãÚ ÍİÙ ßÇãá ááŞÇÆãÉ ÇáãÑÆíÉ.
- **ÊÑÌãÇÊ:** `column_order_hint` ãÍÏËÉ (ÓÍÈ Ãæ ÃÓåã áßá ÇáÃÚãÏÉ) + `drag_to_reorder` × 4 áÛÇÊº ÃíŞæäÉ `bars-2` ÌÏíÏÉ İí `icon.blade.php`; SCSS `_column_reorder.scss`.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `php -l` + `view:clear`+`view:cache` + `npm run build` (ÇáãÈäì: panel-BVy63S8G.js)º ÇÎÊÈÇÑÇÊ ãÓÊåÏİÉ (Preferences/DragDrop + TrackingColumn + OfficeSelection + MobileParity + QueryCount + Toast + FinancialSummary) ßáåÇ ÎÖÑÇÁº ÇáÓæíÊ ßÇãáÉ **374 äÇÌÍ (1363 assertions)** — ÕİÑ İÔá.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 30.9 ÅÚÇÏÉ ÈäÇÁ ÇáÊÑÊíÈ + Toast | ? | index.blade + partials×3 + order-column-reorder.js + swal.js + icon + 4 ÊÑÌãÇÊ + _column_reorder.scss | 6 İí OrderIndexPreferencesTest (ÌÏíÏ 2) + OrdersTrackingColumnTest ãÚÏóøá |
| **ÇáÅÌãÇáí** | **374 äÇÌÍ (1363 assertions)** | | |

## Phase 30 — İÑÚ 30.10 ? (ÅÕáÇÍ ÇáãÔÇßá ÇáÎãÓ ÇáÌÏíÏÉ: ÓÍÈ æÅİáÇÊ / causer_id / ÃÚãÏÉ ÇİÊÑÇÖíÉ / ÅÎİÇÁ / ÊÚÏíá ÔÇãá) — 2026-09-06

**ãÇ Êóãø (ÈãæÇİŞÉ ÕÑíÍÉ ãä ÇáãÓÊÎÏã):**
- **ÅÕáÇÍ ÇáÓÍÈ æÇáÅİáÇÊ (ÌĞÑ ÇáãÔßáÉ):** İí `index.blade.php:3936` ßÇä `:data-col-key="{{ $settingsKey }}"` — ÚáÇãÉ `:` ÊÌÚá Alpine íŞíøã `number`/`customer`… ßãÊÛíøÑ JS ÛíÑ ãÚÑøİ ÏÇÎá äØÇŞ Alpine ? `dataset.colKey === undefined` áßá Õİ ? `commit()` íÎÑÌ ãÈßÑğÇ æáÇ íÚíÏ ÇáÊÑÊíÈ. ÇáÍá: ÌÚáåÇ ÓãÉ ËÇÈÊÉ `data-col-key="{{ $settingsKey }}"`.
- **ÎØÃ `causer_id` Data truncated:** migration ÌÏíÏ `2026_09_06_183355_fix_activity_log_ulid_columns` — `nullableMorphs()` ÃäÔÃ `unsignedBigInteger` áßä `User`/`Order` íÓÊÎÏãÇä `HasUlids` (ÓáÇÓá 26). Êã ÊæÓíÚ `causer_id`/`subject_id` Åáì `string(26) nullable` ãÚ ÇáÍİÇÙ Úáì ÇáİåÇÑÓ `causer`/`subject`.
- **ÇáÃÚãÏÉ ÇáÇİÊÑÇÖíÉ ÇáÎÇØÆÉ + ÚÏã ÅãßÇäíÉ ÇáÅÎİÇÁ (Issues 1/2):** ÅÕÏÇÑ preference ÃÍÇÏí ÇáÇÓÊÎÏÇã. migration `2026_09_06_183618_add_prefs_version_to_user_column_preferences_table` ÃÖÇİ `prefs_version`º `loadColumnPreferences` íÚíÏ ÊÚííä ÇáŞæÇÆã ÇáŞÏíãÉ (ŞÈá ÅÚÇÏÉ ÇáÈäÇÁ¡ `prefs_version` İÇÑÛ) Åáì ÇáÇİÊÑÇÖíÇÊ ÇáÌÏíÏÉ ãÑÉ æÇÍÏÉ Ëã íÍİÙ ÇáäÓÎÉ. ßá ÇáÃÚãÏÉ ŞÇÈáÉ ááÊÈÏíá/ÇáÅÎİÇÁ.
- **ÊÚÏíá ÔÇãá ÚäÏ ÇáÊÍÑíÑ ãä ÇáÌÏæá (Issue 4):** ÃõÖíİ `wire:key="order-row-…"` Úáì ßá `<tr>` æ`wire:key="order-card-…"` Úáì ÇáßÇÑÊ ÇáãæÈÇíá ? Livewire íÚíÏ ÊÑßíÈ ÇáÕİ ÇáãõÚÏóøá İŞØ. ÃõÖíİ `$decorateOrder()` (ÇáãäØŞ ÇáãÓÊÎÑÌ ãä ÇáÜ map) æ`$refreshSingleOrder()` (íÚíÏ ÇÓÊÚáÇã ØáÈíÉ æÇÍÏÉ æíÈÏøáåÇ İí `$this->orders`) æÇÓÊõÈÏáÊ ÇÓÊÏÚÇÁÇÊ `loadOrders()` ÇáÃÑÈÚÉ İí ÚãáíÇÊ ÇáÍİÙ inline (phone/name/wilaya/city).
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `view:clear` + ÓæíÊ ßÇãáÉ **374 äÇÌÍ (1363 assertions)** — ÕİÑ İÔá.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 30.10 ÅÕáÇÍ ÇáãÔÇßá ÇáÎãÓ | ? | index.blade + migration×2 + UserColumnPreference.php + HasInlineEdit (ŞÑÇÁÉ) | 374 äÇÌÍ (1363 assertions) · 11 İí Preferences/TrackingColumn |
| **ÇáÅÌãÇáí** | **374 äÇÌÍ (1363 assertions)** | | |

---

## Phase 31 — İÑÚ 31.1 ? (ÓÌá ÇáÃÚãÏÉ ÇáÌÏíÏ + prefs v2 + Şİá ÇáãØáæÈ + ÚãæÏ total/verification + ŞíæÏ ÇáÃÏæÇÑ) — 2026-09-06

**ãÇ Êóãø (ÈãæÇİŞÉ ÇáãÓÊÎÏã Úáì ÎØÉ ãÑÍáÉ 31 æŞÑÇÑÇÊå ÇáÃÑÈÚÉ: ÅÙåÇÑ ÅÌÈÇÑí ãÚ ÊÑÊíÈ ÍÑ / ÅÚÇÏÉ ÍÓÇÈ ÇáÅÌãÇáí ÚäÏ ÇáÊÚÏíá İŞØ / ËáÇËÉ ÃÚãÏÉ ãäİÕáÉ / ÊÎİíÖ ãÈáÛ+äÓÈÉ):**
- **ÅÚÇÏÉ ÊÚÑíİ `$orderColumns()` ÈÇáßÇãá** (`orders/index.blade.php`): ßá ÚãæÏ íÍãá metadata (default/required — ÙÇåÑ ÏÇÆãğÇ áÇ íõÎİì ãÚ ÈŞÇÁ ÇáÊÑÊíÈ ÍÑğÇ /editable/roles/info). ÇÓÊõÈÏá ÚãæÏ `amount` ÈÜ `total`º ÃõÖíİÊ `verification` (ÊÕãíãí)¡ `quantity`¡ `price`¡ `discount`. ÇáŞÇÆãÉ ÇáŞíÇÓíÉ: customer?phone?verification?status?shipping_provider?delivery_type?wilaya?city?stopdesk_point?address?products?quantity?price?total + `confirmation_attempts/last_contact` ááÃÏæÇÑ İŞØ (ÚÈÑ `hasStoreRole`).
- **`prefs_version` = 2:** `loadColumnPreferences`/`saveColumnPreferences` íÚíÏÇä ÊÚííä legacy áãÑÉ æÇÍÏÉ æÍİÙ ÇáÊÑÊíÈ v2¡ íİÑÖÇä ÇáãØáæÈ ÏÇÆãğÇ (ÅÏÑÇÌ canonical áÇ append)¡ æíÓÊÈÚÏÇä ÃÚãÏÉ ÇáÃÏæÇÑ áãä ÈáÇ Owner/Admin/Manager.
- **ãĞÇßÑÉ:** closures `$orderColumn()` (cache) æ`$columnAllowedForUser()` ááÍÑøÇÓº `$orderEagerLoads()` ãæÍøÏ Èíä `loadOrders` æ`refreshSingleOrder`. ÍÑøÇÓ `toggleDraftColumn`/`reorderDraftColumns`/`saveTableSettings` ááãØáæÈ æÇáÃÏæÇÑ.
- **ÇáãæÏÇá:** ÇáãØáæÈ checkbox ãÚØøá + Şİá + ÔÇÑÉ «ÏÇÆãğÇ ÙÇåÑÉ» + ÊäÈíå `primary_columns_hint`º ÃÚãÏÉ ÇáÃÏæÇÑ ÊÎÊİí áŞÇÆãÉ STAFF.
- **ÇáÚãæÏ `total`:** íõÚÑÖ ÏÇÆãğÇ `subtotal + shipping_cost ? discount_amount` ÚÈÑ `decorateOrder` (`items_subtotal`/`discount_amount`/`display_total`)¡ æíÚÇÏ ÍÓÇÈå İí `submitEditInline` áÍÏæË ÇáÊÛííÑÇÊº ãÑÔøÍ `amount` (min/max Úáì total) ÈÇŞò ÊÍÊ äİÓ ãİÊÇÍ ÇáÈäíÉ ÇáÏÇÎáí ãÚ ÑÈØ ÇáÑÃÓ `'total' => 'amount'`.
- **ÇáÚÑÖ:** `orders-table-header`/`orders-table-cell` (ÎáÇíÇ total/quantity/price/discount/verification)¡ ÇáÈØÇŞÉ ÇáãÊÍÑøßÉ¡ ÊİÇÕíá ÇáØáÈíÉ¡ popover ÇáİáÇÊÑ — ßáåÇ Úáì `total`. ÚãæÏ `verification` placeholder ÊÕãíãí (ÔÇÑÉ + «—» + hint `verification_hint`).
- **ÊÑÌãÇÊ** ×4 áÛÇÊ: `verification`, `verification_hint`, `quantity`, `price`.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `php -l` + `view:clear`+`view:cache` + **ÇáÓæíÊ ßÇãáÉ 377 äÇÌÍ (1369 assertions)** — ÕİÑ ÇäÍÏÇÑ (+3 İí `OrderIndexPreferencesTest`: reset v2 / ãäÚ ÅÎİÇÁ ÇáãØáæÈ / ÃÚãÏÉ ÇáÃÏæÇÑ áÜ STAFF).

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 31.1 ÓÌá ÇáÃÚãÏÉ + prefs v2 + Şİá ÇáãØáæÈ + total | ? | index.blade + orders-table-header + orders-table-cell + 4 ÊÑÌãÇÊ | 377 äÇÌÍ (1369 assertions) · 14 İí Preferences/TrackingColumn |
| **ÇáÅÌãÇáí** | **377 äÇÌÍ (1369 assertions)** | | |

---

## Phase 31 — İÑÚ 31.2 ? (ÍÇÌÒ ÇáÊÃßíÏ: ãäÚ ÊÃßíÏ/ÅÑÓÇá ØáÈíÉ ÛíÑ ãßÊãáÉ) — 2026-09-06

**ãÇ Êóãø (ÇáãÕÏÑ ÇáæÍíÏ ááİÍÕ + ÍÑøÇÓ Úáì ßá ÇáãÓÇÑÇÊ):**
- **ÎÏãÉ `app/Domains/Order/Services/OrderCompleteness.php`**: `missing(Order, bool forSend=false)` ÊõÑÌÚ ÈäíÉ `key+label` ÈÊÑÊíÈ ËÇÈÊ¡ æ`missingLabels()`/`isComplete()`. ãÓÊæíÇä ÈÍßã ŞÇÚÏÉ ÇáãäÊÌ: **ÇáÊÃßíÏ** = ÇÓã/åÇÊİ ÇáÒÈæä + ÇáæáÇíÉ/ÇáÈáÏíÉ + ÃÕäÇİ (ÈÏæä æÌåÉ/äÇŞá — äÇİĞÉ ÇáÊÃßíÏ ÊõßãáåÇ)¡ **ÇáÅÑÓÇá** = äİÓåÇ + ÇáæÌåÉ (ÚäæÇä ãäÒáí Ãæ ãßÊÈ ÍÓÈ `delivery_type`) + ÔÑßÉ ÇáÊæÕíá.
- **ÇÓÊËäÇÁ `app/Domains/Order/Exceptions/OrderIncompleteException.php`** (`fromMissing` + `labels()`).
- **`OrderService::confirm`** íÊÍŞøŞ ŞÈá ÇáÊÍæíá æíÑãí `OrderIncompleteException` ááØæáí ÇáäÇŞÕ (ãÓÊæì ÇáÊÃßíÏ). **`OrderShippingGateway::send`** íÊÍŞøŞ ÈÚÏ `refresh()` æŞÈá Ãí ÇäÊŞÇá/ÅÑÓÇá (ãÓÊæì ÇáÅÑÓÇá) — ÃÈÚÏ äŞØÉ ÃãÇä (ÊÛØí ÊÃßíÏ+ÅÑÓÇá¡ ÇáÅÑÓÇá ÇáãÈÇÔÑ¡ æÇáÌãÇÚí ÇáĞí íÊÎØøì ÛíÑ ÇáÌÇåÒ ÃÕáğÇ).
- **æÇÌåÉ Volt (`orders/index.blade.php`)**: `collectMissingFields(Order, forSend)` ÃÕÈÍ æßíáğÇ ááÎÏãÉ (ãÕÏÑ æÇÍÏ)¡ æÈŞíÉ ãßÇáãÇÊå (ÅÑÓÇá ãÈÇÔÑ 1534/ÊÍáíá ÇáÌãÇÚí 922) ÈÏæä ãÚÇãá = ãÓÊæì ÇáÅÑÓÇá. **`submitConfirmOnly`**: ÍÇÑÓ ŞÈá `confirm()` — ÊæÓÊ `order_flow.confirm_missing_fields` ÈÇáÍŞæá ÇáäÇŞÕÉ æÇáãæÏÇá íÈŞì ãİÊæÍğÇ (áÇ ÅÛáÇŞ). **`submitConfirmAndSend`**: ÍÇÑÓ ŞÈá İÍÕ ÇáÔÑíß — ÊæÓÊ ÈÇáÍŞæá ÇáäÇŞÕÉ ÃæáğÇ.
- **ÊÑÌãÇÊ** ×4 áÛÇÊ: `order_flow.confirm_missing_fields`.
- **ÅÕáÇÍ ÇáÊäÇŞÖ ãÚ ŞÇÚÏÉ ãÊÌÑ ÇáãäÊÌÇÊ**: ãÊÌÑ "ãßÊÈ ÇÎÊíÇÑí" (CartOrderLimitsTest) íõäÔÆ ØáÈíÉ stopdesk ÈáÇ ãßÊÈ æíÄßÏåÇ — ÇáæÌåÉ áíÓÊ ÔÑØğÇ ááÊÃßíÏ İËÈÊø ãÓÊæì ÇáÅÑÓÇá İíå İŞØ. ÊÍÏíË ÅÚÏÇÏ `OrderServiceTest` (ÓÇÈŞğÇ ÒÈæä/ÌÛÑÇİíÇ/ÚäæÇä/ãäÊÌÇÊ äÇŞÕÉ — ÃÕÈÍ ãßÊãáğÇ áíãÑø ãä ÇáÍÇÑÓ).
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `php -l` + `view:clear`+`view:cache` + **ÇáÓæíÊ ßÇãáÉ 386 äÇÌÍ (1388 assertions)** — ÕİÑ ÇäÍÏÇÑ.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 31.2 ÍÇÌÒ ÇáÊÃßíÏ/ÇáÅÑÓÇá | ? | OrderCompleteness.php + OrderIncompleteException.php + OrderService.php + OrderShippingGateway.php + index.blade + 4 ÊÑÌãÇÊ | 386 äÇÌÍ (1388 assertions) · 9 İí OrderCompletenessTest |
| **ÇáÅÌãÇáí** | **386 äÇÌÍ (1388 assertions)** | | |

## Phase 31 — İÑÚ 31.3 ? (ŞæÇÆã ãäÓÏáÉ ÈÍËíÉ inline ááÃÚãÏÉ ÇáãØáæÈÉ) — 2026-09-06

**ãÇ Êóãø — ÎãÓÉ ÍŞæá ŞÇÈáÉ ááÊÚÏíá ãä ÇáÌÏæá ãÈÇÔÑÉ ÈäãØ `<x-edz.select>`:**
- **ÓÌá ÇáÃÚãÏÉ**: `assigned_agent` ÃÕÈÍÊ `editable => true` (ÇáãæÕá ŞÇÈá ááÅÓäÇÏ ãä ÇáÕİ). ÈÇŞí ÇáÃÚãÏÉ (ÔÑßÉ/äæÚíÉ/ÔÍä/ãßÊÈ) ßÇäÊ editable ÃÕáğÇº wilaya/city ÊõÈŞí ÇáäãæĞÌ ÇáÃÕáí (native select) áÊŞáíá ÇáãÎÇØÑÉ.
- **ÎáÇíÇ ÇáÃÚãÏÉ** (`orders-table-cell.blade.php`): ãÍÑøÑÇÊ inline ÊÚÑÖ İŞØ ÚäÏãÇ `editingField === 'order.<key>' && editingId === $orderId`º ÈÍË (`search`) áÔÑßÉ/ãßÊÈ/ãæÕá¡ æãÈÇÔÑ áäæÚíÉ/ÔÍäº ÒÑ ÍİÙ (spinner ÃËäÇÁ ÇáÍİÙ) + ÅáÛÇÁ + ÚÑÖ ÎØÃ. ÔÇÔÇÊ ÇáÚÑÖ ÃÕÈÍÊ ÃÒÑÇÑ `edz-inline-edit__display` (áßá ãä íãáß ORDER_MANAGE).
- **Closures Volt** ÈÇáäãØ ÇáãËÈÊ (start/save + `guardOrderEditable` + `saveEdit` = ÊÍŞŞ/audit):
  - `startOrderProviderEdit`/`saveOrderProvider`: íÎÒøä `shipping_provider_id`¡ **æíÓŞØ ãßÊÈğÇ áã ÊÚÏ ÇáÔÑßÉ ÇáÌÏíÏÉ ÊÎÏãå** (ÊÍŞŞ ãä äØÇŞ ÇáÔÑßÉ Ãæ ÇáäØÇŞ ÇáãÔÊÑß null)¡ æíÚíÏ ÍÓÇÈ ÇáÔÍä.
  - `startOrderDeliveryTypeEdit`/`saveOrderDeliveryType`: íÍÏøË `delivery_type`¡ **æíãÓÍ ÇáãßÊÈ ÚäÏ ÇáÊÍæíá Åáì ãäÒáí**.
  - `startOrderShipmentTypeEdit`/`saveOrderShipmentType`: delivery/exchange/pickup.
  - `startOrderStopdeskEdit`/`saveOrderStopdesk`: ŞÇÆãÉ ãßÇÊÈ **ãŞíÏÉ ÈÔÑßÉ ÇáØáÈíÉ** (`inlineStopdeskOptions`)¡ æÊÍŞŞ íÑİÖ ãßÊÈ ÔÑßÉ ÃÎÑì (`order_stopdesk_point_updated_validation_failed`).
  - `startOrderAgentEdit`/`saveOrderAgent`: ÅÓäÇÏ/ÅáÛÇÁ ÅÓäÇÏ ÚÈÑ `OrderAssignmentService::reassign` (ÈÏæä ÍÇÑÓ shipped — ÇáÅÓäÇÏ ÕÇáÍ İí Ãí ãÑÍáÉº ÎíÇÑ "ÈÏæä ÅÓäÇÏ" `merchant_panel.unassigned` ÇáÌÏíÏ¡ æãÚÇáÌÉ null íÏæíğÇ áÃä `reassign()` íÑİÖ target ÎÇáò).
- **ÃÍÏÇË audit ×4**: `order_shipping_provider_updated` / `order_delivery_type_updated` / `order_shipment_type_updated` / `order_stopdesk_point_updated` / `order_assigned_agent_updated` (ãÚ `_validation_failed` ÚäÏ ÇáÑİÖ).
- **Props ÌÏíÏÉ**: `editProviderOptions`/`editDeliveryTypeOptions`/`editShipmentTypeOptions` (ËÇÈÊÉ ãä mount) + `editStopdeskOptions`/`editAgentOptions` (ÊõÍÓÈ ÚäÏ ÈÏÁ ÇáÊÍÑíÑ). ÅÖÇİÉ `shippingProvider` Åáì `orderEagerLoads` ÍÊì ÊÚÑÖ ÇáÎáíÉ ÇáÔÑßÉ ÇáÍÇáíÉ áÇ ÔÑßÉ ÂÎÑ Tracking.
- **ÊÑÌãÉ**: `merchant_panel.unassigned` ×4 áÛÇÊ.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `view:clear`+`view:cache` + ÓæíÊ ßÇãáÉ **397 äÇÌÍ (1440 assertions)** — ÕİÑ ÇäÍÏÇÑ.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 31.3 selects ÈÍËíÉ inline | ? | index.blade.php (closures + props + registry) + orders-table-cell.blade.php + orderEagerLoads + merchant_panel ×4 | 11 İí OrderInlineSelectEditTest (52 assertion) |
| **ÇáÅÌãÇáí** | **397 äÇÌÍ (1440 assertions)** | | |

## Phase 31 — İÑÚ 31.4 ? (ÊÚÏíáÇÊ inline: ÇáÚäæÇä/ÇáæÒä/ÇáÊÎİíÖ/ÇáÔÍä ãä ãÓÊæÏÚ ÇáÔÑßÉ + ÔÇÑÉ ÇáÍŞæá ÇáäÇŞÕÉ ÏÇÎá ÇáÕİ) — 2026-09-06

**ãÇ Êóãø — ÃÑÈÚÉ ÍŞæá ÅÖÇİíÉ ŞÇÈáÉ ááÊÚÏíá ãä ÇáÌÏæá ãÈÇÔÑÉ + ßÔİ ÇáäŞÕ İí ÇáÕİ ĞÇÊå:**
- **`weight_kg` ÇáÂä ÚÏÏí ÏÇÆãğÇ (NOT NULL default 1.00)**: ÇáÊÚÏíá íÎÒøä ÏÇÆãğÇ ŞíãÉ ÑŞãíÉ (`blank ? 1.00`)º æÈäİÓ ÇáãäØŞ ÍõÑøÑ `OrderService::createManual` (blank ? 1.00) æãÓÇÑÇ ÅäÔÇÁ/ÊÚÏíá ÇáãæÏÇá (`form['weight_kg'] ?: 1.00`) áíÊæÇİŞÇ ãÚ ÚãæÏ NOT NULL (ÃÕáÍ 3 ÅÎİÇŞÇÊ pre-existing İí OfficeSelection/QuantityCap/FinancialSummary ÚäÏ ÅäÔÇÁ ØáÈíÇÊ ÈæÒä İÇÑÛ).
- **Closures ×7** ÈÇáäãØ ÇáãËÈÊ (`canStore ORDER_MANAGE` ÚäÏ ÇáÈÏÇíÉ + `guardOrderEditable` + `saveEdit` = ÊÍŞŞ/audit + `refreshSingleOrder`):
  - `startOrderAddressEdit`/`saveOrderAddress`: äÕ ÍÑ (blank ? null) + `recalculateOrderShipping` ÈÚÏ ÇáÊÍÏíË + audit `order_address_updated`.
  - `startOrderWeightEdit`/`saveOrderWeight`: `nullable|numeric|min:0|max:9999` + audit `order_weight_updated` (æ `_validation_failed`).
  - `startOrderDiscountEdit`/`saveOrderDiscount`: ËáÇËÉ props (`discountEditType`/`discountEditValue`/`discountEditReason`)¡ äæÚ `amount`/`percent` ÚÈÑ `<x-edz.select>` ãÚ `wire:model.live` (ÅÙåÇÑ/ÅÎİÇÁ ÇáŞíãÉ æÇáÓÈÈ)¡ ÇÚÊÈÇÑíÉ `percent > 100` ? `merchant_panel.discount_percent_max`º ÇÎÊíÇÑ «ÈÏæä ÊÎİíÖ» Ãæ ŞíãÉ İÇÑÛÉ íãÓÍ `discount_type/value/reason` + audit `order_discount_updated`.
  - `toggleSendFromWarehouse`: ÚßÓ `send_from_carrier_warehouse` (Úáã ÊÎØíØí — íÚãá ÍÊì áÜ shipped) + audit `order_send_from_warehouse_updated`.
  - `startMissingFieldEdit`: íŞİÒ Åáì Ãæá ÍŞá äÇŞÕ (ÚÈÑ `OrderCompleteness::missing` ÈãÓÊæì confirm/send) — ßá key íõÚÇÏ ÊæÌíåå Åáì ãÍÑøÑå inline (name/phone/wilaya/city/stopdesk/address/provider)º `items` Ãæ ÛíÑ ÇáãÚÑæİ ? İÊÍ ãæÏÇá ÇáÊÚÏíá.
- **`decorateOrder`** íÍÓÈ `missing`/`missing_keys` áßá Õİ (statuses backOffice İŞØ¡ `forSend` = confirmed/preparing)º **ÎáíÉ ÇáÒÈæä** ÊÚÑÖ ÔÇÑÉ count ÊäŞáß áÃæá ÍŞá äÇŞÕ (tone `info` — ÊÌäøÈğÇ áÇÕØÏÇã `OrderDuplicateBadgeTest` ÇáĞí íÑİÖ danger/warning/neutral).
- **ÎáÇíÇ ÇáÃÚãÏÉ**: address/weight/discount/`send_from_carrier_warehouse` ÈãÍÑøÑíåÇ¡ ÍÇæíÉ `edz-inline-edit__edit--wide` ááãæÏÇá ÇáæÇÓÚ (ÇáÊÎİíÖ)¡ æÃÒÑÇÑ ÇáÍİÙ ÈäãØ ÇáÓÈíäÑ ÇáãÓÊåÏİ.
- **ÊÑÌãÇÊ** ×4 áÛÇÊ: `merchant_panel.no_discount` + `merchant_panel.discount_percent_max`.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `view:clear`+`view:cache` + **ÇáÓæíÊ ßÇãáÉ 410 äÇÌÍ (1497 assertions)** — ÕİÑ ÇäÍÏÇÑº ãáÇÍÙÉ ÊÔÛíá æÇÍÏÉ : race Windows ãÚÑæİ (rename Access is denied ÃËäÇÁ compile blade) — ãÌÑÏ ÅÚÇÏÉ ÊÔÛíá.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 31.4 inline field edits + ÔÇÑÉ ÇáäÇŞÕ | ? | index.blade.php (closures + props + decorateOrder + ããÑøÑÇ ÇáæÒä) + orders-table-cell.blade.php + _inline_edit.scss + OrderService.php + merchant_panel ×4 | 13 İí OrderInlineFieldEditTest (57 assertion) |
| **ÇáÅÌãÇáí** | **410 äÇÌÍ (1497 assertions)** | | |

## Phase 31 — İÑÚ 31.5 ? (ÅÒÇáÉ ÒÑ «ÊæÕíá» + ÈÇÑÊíÇá ÇáÃÒÑÇÑ ÇáãÔÊÑß + ÊæÍíÏ ÇáÓÈíäÑ ÇáãÑßÒí + ÅÕáÇÍ «ÇáãÍÏÏ» İí ÇáŞæÇÆã + ÊÍÑíÑ notes inline) — 2026-09-07

**ÎãÓÉ ØáÈÇÊ ãä ÇáãÓÊÎÏã ÈÚÏ ãæÇİŞÉ ÕÑíÍÉ Úáì ÇáÎØÉ ÇáËáÇËíÉ (ÒÑ ÇáÊæÕíá / ÊæÍíÏ ÇáÓÈíäÑ Úáì ÇáãÑßÒí JS / ÈÇÑÊíÇá ÇáÃÒÑÇÑ):**
- **ÍĞİ ÒÑ «ÊæÕíá» äåÇÆíğÇ** ãä ÚãæÏ ÇáÃÒÑÇÑ (ÏíÓßÊæÈ + ãæÈÇíá)º ãæÏÇá ÇáÊæÕíá ÊÈŞì ŞÇÈáÉ ááæÕæá ÚÈÑ ÇáÊÍÑíÑ ÇáãÈÇÔÑ ááÍŞæá¡ æpopup «ãÚáæãÇÊ ÇáÊæÕíá» áÇÍŞğÇ ÍÓÈ ÇáÍÇÌÉ.
- **ÈÇÑÊíÇá ÃÒÑÇÑ ãÔÊÑß** `orders-table-actions-column.blade.php` ÈãÚÇãá `layout` ('compact' ááÏíÓßÊæÈ | 'list' ááãæÈÇíá) — ÏíÓßÊæÈ: details + events (ÏÇÎá ÇáÜpartial) + confirm/send/edit/reassign/delete/restoreº ãæÈÇíá: ÇáŞÇÆãÉ ÊÚÑÖ İŞØ ÃÒÑÇÑ ÇáÕáÇÍíÇÊ (confirm/send/edit/reassign/delete) ÈíäãÇ íÈŞì details + events Úáì íÓÇÑ ÇáÜpopover (ßãÇ ßÇä) — ÅÛáÇŞ ÇáŞÇÆãÉ ÈÜ`@click="close()"` ãäİÕá (äãØ ãËÈÊ) æ`$editCloser`/`$deleteCloser` áÃÒÑÇÑ Alpine.
- **ÊæÍíÏ ãÄÔÑ ÇáÊÍãíá Úáì ÇáäÙÇã ÇáãÑßÒí `edz-button-loading.js`**: ÍõĞİÊ ßá ãŞÇíÖÇÊ `wire:loading.remove`/`x-edz.spinner` ÇáíÏæíÉ ãä index.blade.php (ÔÑíØ ÇáÃÏæÇÊ/ÇáÊÑÇÔ/ÃÒÑÇÑ ÇáÍİÙ İí ÈØÇŞÉ ÇáãæÈÇíá/ŞæÇÆã ÇáÍÇáÉ/ãæÏÇáÇÊ ÇáÅÑÓÇá) æ`orders-table-cell.blade.php` (ÃÒÑÇÑ ÇáÍİÙ ×10) æ`bulk-actions-bar.blade.php` æ`delivery-edit-modal.blade.php` æ`order-form-modal.blade.php`. `edz-button-loading.js` ãõÍÏóøË: ÏÚã `@click="$wire.method()"` ÚÈÑ `ALPINE_CLICK_SELECTOR` + `WIRE_CALL_RE`¡ æÏŞÉ ÇáãØÇÈŞÉ `methodOf` (multi-match ÚÇÏ ááÚãá áÍÇáÇÊ ãËá `transitionOrder`)¡ æÃÈŞíÊ `wire:loading.attr="disabled"` æ`show="isLoading"` (Alpine) ááÃÒÑÇÑ ÇáãÍáíÉ.
- **ÅÕáÇÍ ÌĞÑí áÚÏã ÙåæÑ «ÇáãÍÏÏ»** İí `x-edz.select`/`product-select`: (Ã) ÎíÇÑÇÊ backend ÊõÍæóøá ÇáÂä Åáì string (`String(r.value ?? r.id ?? r)`) áÊİÇÏí İÔá `opt.value === selected`º (È) ÓãÉ `data-options` ÌÏíÏÉ Úáì ÇáÌĞÑ + `MutationObserver` İí `edz-select.js` æ`_syncFromServer()` İí `product-select.js` (ãÚ `x-on:livewire:updated`) — ÊõÍÏöøË ÍÇáÉ Alpine ÈÚÏ morph ãä Livewire ÈÏá ÇáÈäÇÁ ãÑÉ æÇÍÏÉ ÚäÏ mount.
- **ÊÍÑíÑ `notes` inline** (ÏíÓßÊæÈ + ÈØÇŞÉ ÇáãæÈÇíá): `startOrderNotesEdit`/`saveOrderNotes` ÈäãØ `saveEdit` ãÚ `nullable|string|max:500` + `guardOrderEditable` + apply `blank ? null` + audit `order_notes_updated` (æ`_validation_failed`)¡ ÎáíÉ textarea İí `orders-table-cell`¡ Õİ İí ÈØÇŞÉ ÇáãæÈÇíá ãÍÑæÓ ÈÜ`in_array('notes', $this->visibleColumns)`¡ æÊÓÌíá ÇáÚãæÏ `editable => true`.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `php -l` äÙíİ + `npm run build` (Sass deprecations İŞØ¡ áÇ ÃÎØÇÁ) + `view:clear`+`view:cache` + **ÇáÓæíÊ ßÇãáÉ 413 äÇÌÍ (1511 assertions)** — ÕİÑ ÇäÍÏÇÑ (ÃÕáÍ ÇÎÊÈÇÑ `OrdersMobileMoreMenuTest` áÏŞÉ İÑÖíÉ min-h-44px ááãæÙİ).
- **ÅÕáÇÍ ÚÇÌá ÈÚÏ ÇáÇÎÊÈÇÑ ?:** ÎØÃ ÇáßæäÓæá `Failed to execute 'closest' ... 'button[@click]' is not a valid selector` ãä `edz-button-loading.js` — `@click` áíÓ ÇÓã ÎÇÕíÉ CSS ÕÇáÍğÇ İßÇä íõÑãí İí `closest/querySelectorAll` ÚäÏ ßá äŞÑÉ. ÇáÍá ÇáÌĞÑí: ÇÓÊõÈÏá ÈÜ`[x-on\:click]` ÇáÓáíã (CSS) + Íáø `@click`/`x-on:click` ÚÈÑ `hasAttribute()/getAttribute()` (ÏÇáÉ `alpineHandlerOf`/`hasAlpineClick` ãÚ ãÓÍ íÏæí İí `allAlpineButtons()` æ`alpineButtonOf()` áÇÚÊÑÇÖ ÇáäŞÑ)¡ ãÚ ßÇÔ `requestButtons` íõÕİóøÑ ÚäÏ ÈÏÇíÉ ßá ØáÈ/ÊäŞøá ÍÊì áÇ íõÚÇÏ İÍÕ DOM ãÑÇÑğÇ áßá method (ÌÇäÈ ÇáÃÏÇÁ) — ÇáÜbundle ÇáÌÏíÏ `panel-Dm-JaIMG.js`.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 31.5 ÃÒÑÇÑ/ÓÈíäÑ/ãÍÏÏ/notes | ? | orders-table-actions-column.blade.php + index.blade.php + orders-table-cell.blade.php + bulk-actions-bar + delivery-edit-modal + order-form-modal + edz-button-loading.js + edz-select.js/product-select.js + select/product-select.blade.php + OrderInlineFieldEditTest | 16 İí OrderInlineFieldEditTest (71 assertion) — ãäåÇ 3 ÌÏíÏÉ ááÊáÇÍÙÇÊ |
| **ÇáÅÌãÇáí** | **413 äÇÌÍ (1511 assertions)** | | |

---

## Phase 31 — İÑÚ 31.6 ? (ÅÕáÇÍ ÑãÒ «×» ÇáãÚØæÈ + ÇáÊÍÏíÏ ÇáÊáŞÇÆí áÔÑßÉ ÇáÊæÕíá ÇáÇİÊÑÇÖíÉ + ÚÑÖ «ÇáãÍÏÏ» ÇáãÍİæÙ İí ßá `x-edz.select`) — 2026-09-07

**ËáÇËÉ ÚíæÈ ÃõÈáÛ ÚäåÇ ÈÚÏ ÌæáÉ 31.5¡ ÈãæÇİŞÉ ÕÑíÍÉ Úáì ÇáÎØÉ (æÃõáÍŞ ÈåÇ ÅÕáÇÍ ÔÇÑÉ «Ã—»?«×» æ`searchable`?`search` ßÅÕáÇÍÇÊ ãßãøáÉ):**

- **ÑãÒ «×» ÇáãÚØæÈ İí ÑÓÇÆá ÇáÍŞæá ÇáäÇŞÕÉ æÇáİÇÕá ÇáÚÑÈí**: ßÇäÊ `implode` ÏÇÎá `orders/index.blade.php` ÊÓÊÎÏã İÇÕáğÇ ÚÑÈíğÇ ãÚØæÈğÇ (`'ØŒ '`/`'Ø› '` ÈÏá `'¡ '`) İí ÑÓÇÆá `send_missing_fields`/`confirm_missing_fields` (ÇáÃÓØÑ 954/1003/1582/5296 ? `'¡ '`)º ææõÍöøÏÊ ÇáİæÇÕá İí 1476/1511 ÚÈÑ replaceAll ? `'¡ '` (ãÔÊÑß áßá ÇááÛÇÊ áÃä `implode` İí ÇáÜblade äİÓå). ÃõÕáÍÊ ÃíÖğÇ ÔÇÑÇÊ «ÖÑÈ» ÇáãÚØæÈÉ `Ã—` (ÇáŞöíã ÇáßãíÉ/ÇáÓÚÑíÉ/ÇáÊßÑÇÑ) ? «×» İí 4 ãæÇÖÚ ÈÜ`index.blade.php` (4245/4832/5151/5390) æãæÖÚíä ÈÜ`partials/order-form-modal.blade.php` (249/...) — ßáåÇ ßÇäÊ ÊãËøá ÑãÒ ÇáŞÓãÉ `×` Èíä ÇáŞíãÉ æÇáÊİÕíáÉ.
- **ÚÏã ÇáÊÍÏíÏ ÇáÊáŞÇÆí áÔÑßÉ ÇáÊæÕíá ÇáÇİÊÑÇÖíÉ**: `ShippingProvider.is_default` ãæÌæÏ æãÑÊÈØ ÈÎÇäÇÊ «ÊÚííä ÇİÊÑÇÖíÉ» İí ÕİÍÉ ÇáÔÑßÇÊ¡ áßäå áã íßä íõŞÑÃ İí ÊÏİŞ ÇáØáÈíÇÊ. ÃÖíİ ãÕÏÑ æÇÍÏ ÏÇÎá `orders/index.blade.php` — closure `$storeDefaultProviderId()` (ŞÑÇÁÉ `where('store_id', currentStoreId())->where('is_active', true)->where('is_default', true)`) — íõÓÊÏÚì ÚäÏ: (1) İÊÍ ãæÏÇá ÇáÅäÔÇÁ `openCreateModal` áÊåíÆÉ `form['shipping_provider_id']`¡ æ(2) ÏÑÌ ÇáÊÃßíÏ `openConfirmModal` ßÜ fallback ÚäÏãÇ áÇ Êãáß ÇáØáÈíÉ ÔÑßÉ (`confirmProviderId`). ÇáÃÏÇÁ: ŞÑÇÁÉ ŞÇÚÏÉ æÇÍÏÉ İŞØ ÚäÏ İÊÍ ÇáãæÏÇá¡ áÇ ÇÓÊÚáÇãÇÊ ÒÇÆÏÉº áÇ ÊÛííÑ İí `/cascade` ÇáãßÇÊÈ (íÈŞì `loadFormOffices` íÚãá ÚäÏ ÇÎÊíÇÑ ÔÑßÉ/ÇáÊÍæíá ááÇÓÊáÇã).
- **ÚÏã ÚÑÖ «ÇáãÍÏÏ» ÇáãÍİæÙ ÈÚÏ ÇáÍİÙ İí ßá ÇÓÊÎÏÇãÇÊ `x-edz.select`** (ÇáÓÈÈ ÇáÌĞÑí ÇáÓØÍí íõÚÒì áÜ`select.blade.php` ÇáĞí ãÑøÑ `initialValue: @js($attributes->get('wire:model', ''))` — áÇ íáÊŞØ `wire:model.live` — æJS áã íßä íŞÑÄå ÅØáÇŞğÇ):
  - `resources/views/components/edz/select.blade.php`: ÇÓÊÎÑÇÌ ÕÍíÍ áãÓÇÑ ÇáäãæĞÌ `$modelName = $attributes->whereStartsWith('wire:model.')->first() ?: $attributes->whereStartsWith('wire:model')->first()` (íáÊŞØ `wire:model.live` ÃæáğÇ ÍÊì áÇ íÙááå ÇáËäÇÆí `wire:model` ÇáÃÓÇÓí)¡ ãÑæÑğÇ ßÜ `modelName` (ÑİÖ `initialValue`).
  - `resources/js/components/edz-select.js`: ÎÒä `modelName` + ŞÑÇÁÉ `this.$wire.get(modelName)` ÚäÏ `init()` ÚÈÑ `syncFromServer()` ÇáÌÏíÏÉ (ÊõØÈíÚ ÇáŞíãÉ ÈÜ`String(v)` İÊõÕáÍ ~10 ãæÇÖÚ ÚÏã ãØÇÈŞÉ ÑŞãí/äÕí ãä ÌÑÏ 46 ÇÓÊÎÏÇãğÇ)¡ Ïæä ÊÛííÑ ÂáíÉ ÇáÅÑÓÇá (ÇáÍŞá ÇáãÎİí `x-model` + ÃÍÏÇË change/livewire-change ÈÇŞíÉ ßãÇ åí áÖãÇä ÇÓÊãÑÇÑ `wire:change` Úáì ÇáÌĞÑ: ÔÑßÉ?ãßÇÊÈ¡ æáÇíÉ?ãÏíäÉ). ÇáäãØ ãØÇÈŞ áÜ`product-select.js`.
  - **ÊÕÍíÍ ÇÓÊÎÏÇã ÎÇØÆ**: `orders/index.blade.php:5175` ãÑøÑ `searchable` (ãõÚÇãá ÛíÑ ãÏÚæã İí åĞÇ Çáãßæøä) ÈÏá `search` áãæÏÇá ÇáÊÃßíÏ ? Íõæøá Åáì `search`.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `php -l` äÙíİ áÜ3 ãáİÇÊ (index/select/order-form-modal) + `node --check` áÜ`edz-select.js` + `npm run build` (ÈáÇ ÃÎØÇÁ) + `view:clear`+`view:cache` (ÊÍŞŞ: áÇ ÃËÑ áÜ`Ã—`¡ `livewire:updated` æ`syncFromServer` æ`modelName` ÙÇåÑÉ İí ÇáÚÑæÖ ÇáãÊÑÌãÉ¡ æ`searchable` ÇáãÊÈŞøí İŞØ İí ãßæøäÇÊ umi/form ÇáÃÎÑì ÇáãÔÑæÚÉ) + **ÇÎÊÈÇÑÇ `OrderInlineFieldEditTest` + `OrdersMobileMoreMenuTest`: 18 äÇÌÍ (79 assertions)** — ÕİÑ ÇäÍÏÇÑ İí ÇáÈäæÏ ÇáãõÕáóÍÉ.
- **ÅÕáÇÍ ÅÖÇİí ÈÚÏ ÇáÊÍŞŞ ?:** ÊÑÊíÈ ÇáÈæÈ ÃÈÓ — ÚäÏ ÇáÖÛØ Úáì ÃÍÏ ÃÓØÑ ÇáØáÈíÇÊ ÇáãßÑÑÉ İí äÇİĞÉ ÇáİÍÕ (`wire:click="openOrderDetails(...)"` İí `index.blade.php`)¡ ßÇäÊ ÊİÇÕíá ÇáØáÈíÉ ÊõİÊÍ **Îáİ** äÇİĞÉ ÇáİÍÕ áÃä `showDuplicateScanModal` íÈŞì `true`. ÇáÍá ÇáÌĞÑí İí ãÕÏÑ æÇÍÏ: `openOrderDetails` íÕİøÑ `showDuplicateScanModal` ÃæáğÇ (äãØ ãæÍøÏ íÛØí ßá ÇáÏÎæá ãä ÈæÈ ÃÈ ÇáİÍÕ¡ ÈäİÓ ÃÓáæÈ `order-form-modal.blade.php:277` ÇáĞí íÛáŞ ãæÏÇáÇÊ ÇáÅäÔÇÁ/ÇáÊÚÏíá ŞÈá ÇáÊİÇÕíá). ÃõÖíİ ÇÎÊÈÇÑ `OrderDuplicateBadgeTest` («opening order details from a duplicate-scan row closes the scan popup») — **10 äÇÌÍ (49 assertions)**.
- **ÇáÓÈÈ ÇáÌĞÑí ÇáÍÇÓã (ÌæáÉ ËÇäíÉ) ?:** ÇáÈÍË ÇáÔÇãá İí `vendor/livewire` + ãæÇÑÏ `resources` ÃËÈÊ Ãä ÍÏË **`livewire:updated` ÛíÑ ãæÌæÏ İí Livewire v3 ÅØáÇŞğÇ** — İãÓÊãÚÇ `x-on:livewire:updated="syncFromServer"` (æ`app.js:44`/`panel.js:224` ÇááĞÇä ÊÑßäÇåãÇ ÎÇÑÌ ÇáäØÇŞ) áã íõÔÚá ÃÍÏåãÇ ÃÈÏğÇ¡ æåĞÇ åæ ÇáÏÇÁ ÇáİÚáí áÚÏã ÇäÚßÇÓ ÇáŞíãÉ ÇáãÍİæÙÉ ÈÚÏ ÇáÍİÙ. ÇáÈÏíá ÇáÈÑãíÌí ÇáÕÍíÍ İí v3: **`this.$wire.$watch(path, cb)`** ÇáĞí íÑÇŞÈ `dataGet(component.reactive, path)` æíÊÍæá ÈÚÏ ßá ÑÍáÉ ÓíÑİÑ¡ ãÚ ŞÑÇÁÉ ÃæáíÉ ãÊÒÇãäÉ ÚÈÑ `$wire.get` (ãÄßÏ ãä ÇáÜdist: íŞÑÃ `component.reactive` ãÈÇÔÑÉ). ØõÈøŞ ÇáÚáÇÌ Èãßæøäíä:
  - `resources/js/components/edz-select.js`: `init()` ? `_bindServerValue()` (ŞÑÇÁÉ `$wire.get(modelName)` ãæÍøÏÉ ÈÜ`String(v)` + ÊÓÌíá `$wire.$watch`)¡ æÍõĞİÊ `syncFromServer` ÇáŞÏíãÉº ÚÑæÖ `_syncFromServer` ÇáÅÑÓÇáíÉ/ÇáÎíÇÑÇÊ ßãÇ åí.
  - `resources/js/components/product-select.js` + `resources/views/components/edz/product-select.blade.php` (ÓØÑ 55): äİÓ ÇáãÚÇãáÉ — `_bindServerValue()` ÈÏá `syncFromServer`¡ æÅÒÇáÉ ãÓÊãÚ ÇáÍÏË ÇáãíÊ.
  - ãÑÇÌÚÉ ÔÇãáÉ: ßá ÇÓÊÎÏÇãÇÊ ÇáÜ47 áÜ`x-edz.select` æ`x-edz.product-select` ãÑÈæØÉ ÈÜ`wire:model` (ÊÍŞŞÊ ÇáÃÓØÑ ãÊÚÏÏÉ ÇáÃÓØÑ íÏæíğÇ) — áÇ æÌÇåÉ ÎÇÑÌ ÇáäØÇŞ ãÊÃËÑÉ.
- **ÊÑŞíÉ fallback ááÔÑßÉ ÇáÇİÊÑÇÖíÉ ?:** closure `$storeDefaultProviderId()` ÕÇÑ íŞÑÃ `where('store_id', currentStoreId())->where('is_active', true)->orderByDesc('is_default')->orderBy('name')` — íÎÊÇÑ ÇáÔÑßÉ ÇáãõÚáóãÉ `is_default`¡ æÅä áã ÊæÌÏ íŞÚ Úáì Ãæá ÔÑßÉ äÔØÉ İÊÚãá ÇáãæÏÇáÇÊ ÏÇÆãğÇ Úáì ÔÑßÉ ÊæÕíá. ÇáÇÓÊÏÚÇÁÇÊ ßãÇ åí (ÅäÔÇÁ + ÏÑÌ ÊÃßíÏ). ÃõÖíİ ãáİ `OrdersDefaultProviderTest` **(5 ÇÎÊÈÇÑÇÊ / 6 assertions)**: auto-select ááãõÚáóãÉ¡ fallback áÃæá äÔØÉ¡ ÚÒá ÇáãÊÌÑ¡ ÏÑÌ ÇáÊÃßíÏ ÈáÇ ÔÑßÉ æÈÔÑßÉ ŞÇÆãÉ.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 31.6 ÚíæÈ ãÇ ÈÚÏ 31.5 | ? | orders/index.blade.php + partials/order-form-modal.blade.php + components/edz/select.blade.php + components/edz/product-select.blade.php + resources/js/components/edz-select.js + resources/js/components/product-select.js + a OrderDuplicateBadgeTest + a OrdersDefaultProviderTest + 4 ÊÑÌãÇÊ (ÇáİÇÕá İí ÇáÜblade ÇáãÔÊÑß) | 18 İí OrderInlineFieldEditTest + OrdersMobileMoreMenuTest + 10 İí OrderDuplicateBadgeTest + 5 İí OrdersDefaultProviderTest (134 assertions) |
| **ÇáÅÌãÇáí** | **446 äÇÌÍ (1645 assertions)** | | |

---

## Phase 31 — İÑÚ 31.7 ? (ÈæÇÈÉ ÇáÊÃßíÏ «ÇáÏÑÌ İŞØ» + ÒÑ ÊÃßíÏ ÈÇÑÒ + «ãÄÌá» æÇÍÏÉ + áæä confirmed ÃÎÖÑ) — 2026-09-07

**ÈãæÇİŞÉ ÕÑíÍÉ ÈÚÏ ÔÑÍ ÎØÉ ãõÕÍøÍÉ (ÓÄÇá ÇáãÓÊÎÏã ßÔİ ÎØÃó ÇáäÓÎÉ ÇáÃæáì: ÍĞİ `confirmed` ãä `availableTransitions` ßÇä ÓíßÓÑ `OrderService::confirm()` áÃä ÏÑÌ ÇáÊÃßíÏ íäÊåí ÈÜ`transition('confirmed')` ÚÈÑ `canTransition`). ÇáãÈÏÃ ÇáãÚÊãÏ: ÂáÉ ÇáÍÇáÇÊ ãÕÏÑ ÍŞíŞÉ — íõÎİì ÇáÇÎÊÕÇÑ ãä ÇáæÇÌåÉ æíõİÑÖ ÇáÏÑÌ ÎÇÏãíğÇ.**

- **G1 — ÅÎİÇÁ ÚÑÖí İŞØ:** `decorateOrder` íÖíİ `can_confirm` (ÇáÊÍæá íäÊåí ÈÜconfirmed: ÕÍíÍ ÚäÏ pending/on_hold) + `confirm_via_drawer` (pending/on_hold)¡ æÇáÜdropdown ÇáÏíÓßÊæÈ/ÇáãæÈÇíá (`orders-table-cell.blade.php` + `index.blade.php`) íÓÊËäí ÎíÇÑ `confirmed` ãÇ ÏÇã `confirm_via_drawer && !isCurrentStatus` — «ãÄßÏ» ÇáÍÇáíÉ ÊÈŞì ÙÇåÑÉ ßÍÇáÉ ÓÇÑíÉ¡ æÌãíÚ ÇáÊÍæáÇÊ ÇáÃÎÑì (ãÄÌá/áÇ ÅÌÇÈÉ/…) ßãÇ åí.
- **G2 — ÏÑÚ ÇáÓíÑİÑ:** `transitionOrder(id,'confirmed')` æãÕÏÑå pending/on_hold ? `openConfirmModal()` ÈÏá ÇáÊÍæíá (áÇ íÊÌÇæÒ ÇáÚãáíÉ ÍÊì ÈØáÈ íÏæí)º İÍÕ ÇáÕáÇÍíÉ íÓÈŞ ÇáÏÑÚ (íÍÊÇÌ ORDER_CONFIRM).
- **G3 — ÇáÏÑÌ ãÚ on_hold:** `submitConfirmOnly` ÕÇÑ íŞÈá `['pending','on_hold']` (ÈÏá pending İŞØ) + ÍÇÑÓ `OrderShippingGateway::send(confirmFirst:true)` ÕÇÑ íÔãá `'on_hold'` (äŞØÉ ÇáÇÊÕÇá ÇáæÍíÏÉ: ÏÑÌ «ÊÃßíÏ æÅÑÓÇá») — äŞØÉ ÇáæÕæá ÇáæÍíÏÉ Åáì confirmed ÃÕÈÍÊ ÇáÏÑÌ (íõÓÌøá `confirmation_attempts`/`last_contact_at`/`contactAttempt`).
- **B — ÒÑ ÈÇÑÒ:** `orders-table-actions-column.blade.php` — ÒÑ «ÊÃßíÏ ÇáØáÈíÉ» ÊÍæøá ãä ghost Åáì `edz-btn--primary` ããÊáÆ (ÃíŞæäÉ åÇÊİ ÈíÖÇÁ) ÚÈÑ `$confirmBtnClass`º æÔÑØ ÇáÙåæÑ ÕÇÑ `$order['can_confirm']` (áÇ `in_array('confirmed', $transitions)`). ÇáãæÈÇíá: Õİ ããÊáÆ ÈÚäæÇä + ÃíŞæäÉ.
- **C — áæä confirmed ÃÎÖÑ:** `SystemStatusesSeeder` confirmed `info?success` + migration `2026_09_07_000001_confirm_status_green.php` íÚíÏ Êáæíä ÇáÕİæİ ÇáäÙÇãíÉ ÇáŞÇÆãÉ (`type=order`,`key=confirmed`,`is_system=true`) Åáì `success` (æÇáÚßÓ ÚäÏ rollback). Õİ ÇáÌÏæá/ÇáÔÇÑÉ/ÇáäŞØÉ ÊÓÊÌíÈ İæÑğÇ áÃä `edz-table-row--success` ÌÇåÒ (áÇ CSS).
- **P — ÍÇáÉ «ãÄÌá» æÇÍÏÉ:** `pending ? […, postponed, …]` İí `availableTransitions` (ÊÕÈÍ ŞÇÈáÉ ááæÕæá ãä ÇáãÚáøŞº ÇáËÇÈÊ `postponed?[pending,cancelled]` ãæÌæÏ ÃÕáğÇ) + `OrderStatus::POSTPONED` İí ÇáÊÚÏÇÏ ãÚ label/color/icon — Çááæä ØÈÚğÇ `warning` (ããíøÒ Úä ÇáÃÎÖÑ ÇáÌÏíÏ).
- **ÇÎÊÈÇÑÇÊ (D):** ÊÚÏíá `OrderActionToastUnificationTest` áíÚßÓ ÇáÈæÇÈÉ (ÇáãÈÇÔÑ íİÊÍ ÇáÏÑÌº ÇáäŞá ÇáÕÍíÍ ÇáäÇÌÍ ÚÈÑ `postponed`º ÊÍæøá ÛíÑ ÕÇáÍ `shipped` íÈŞí ÇáÍÇáÉ æíõÑÌÚ ÎØÃ) + ãáİ ÌÏíÏ `OrderConfirmGateTest` **(6 ÇÎÊÈÇÑÇÊ)**: ÂáÉ ÇáÍÇáÇÊ ÓáíãÉ ãÚ İÊÍ ÇáÏÑÌ ááÜconfirmed ÇáãÈÇÔÑ¡ ÇÎÊİÇÁ ÎíÇÑ confirmed æÙåæÑ postponed İí ÇáŞÇÆãÉ (regex Úáì `transitionOrder(...confirmed/postponed...)`)¡ ÊÃßíÏ pending ÚÈÑ ÇáÏÑÌ¡ ÊÃßíÏ on_hold ÚÈÑ ÇáÏÑÌ¡ äŞá postponeded¡ æáæä confirmed = success.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `php -l` äÙíİ áÜ7 ãáİÇÊ + `view:clear`+`view:cache` (FRESH-CACHE-OK) + ÇáÌæáÇÊ ÇáãÊÃËÑÉ **36 äÇÌÍ (80 assertions)** (OrderConfirmGate + OrderActionToastUnification + OrderCompleteness + OrdersDefaultProvider + OrdersMobileMoreMenu + StoreAuthorizationGates) Ëã **ÇáÌæáÉ ÇáßÇãáÉ 426 äÇÌÍ (1536 assertions)** ÈáÇ Ãí İÔá (ãáÇÍÙÉ: İÔá ÚÇÈÑ æÇÍÏ İí Ãæá ÊÔÛíá ßÇä Şİá ãáİ `storage/framework/views` Úáì æíäÏæÒ `rename Access is denied` — æÇÌÊÇÒ ÇáÇÎÊÈÇÑ ÈÇáßÇãá 9/9 ÈÚÏ `view:clear`º ÇáÅÌãÇáí ÇáãÚÊãÏ ãä ÇáÊÔÛíá ÇáäÙíİ ÇáËÇäí).

- **ÅÕáÇÍ ÈÚÏ ÇáÊÍŞŞ (İÊÍ ÇáãæÏÇá İí ÇáãÊÕİÍ) ?:** ÃÈáÛ ÇáãÓÊÎÏã Ãä ÇáäŞÑ Úáì ÒÑ ÇáÊÃßíÏ áÇ íİÊÍ ÇáãæÏÇá ÑÛã ÕÍÉ ÇáãÓÇÑ ÇáÎÇÏãí (ÇáÇÎÊÈÇÑÇÊ ÊÌÊÇÒ). ÇáÊÔÎíÕ (ÇÓÊßÔÇİ ÔÇãá): ãæÏÇá ÇáÊÃßíÏ ßÇä «Pattern B» — ãæÌæÏ ÏÇÆãğÇ İí DOM ãÚ `:is-open="$showConfirmModal"` æáÇ íõÚÇÏ İíå ÊåíÆÉ `x-data` ãä Alpine ÚäÏ ÇáãóæÑİº ßãÇ Ãä ÍÏË ÇáÅÛáÇŞ `@close="$wire.closeConfirmModal()"` **ãíÊ** áÃä Çáãßæøä íÕÏÑ `edz-modal-closed` æáíÓ `close` ? `showConfirmModal` íÚáŞ `true` ÈÚÏ Ãæá ÅÛáÇŞ. ÇáÍá: äŞá ãæÏÇá ÇáÊÃßíÏ + ãæÏÇáí ÇáÜbulk (ÇáÍÇáÉ/ÇáÅÑÓÇá) Åáì «Pattern A» ÇáãËÈÊ (äãØ ÇáÊİÇÕíá/ÇáİÍÕ ÇáãÒÏæÌ): `@if ($showX)` + ÛáÇİ `<div @edz-modal-closed.window="$wire.closeX()">` + `:is-open="true"` + `wire:key`¡ æÊã ÊÍÕíä `openConfirmModal` ÖÏ null (`$order->customer?->name` æ`status_label` ÈÔÑØ `status?->key`). **426 äÇÌÍ (1536 assertions)**.
- **ÅÕáÇÍ ÚãæÏ ÔÑßÉ ÇáÔÍä (ÊæÇİŞ ÇáÚÑæÖ) ?:** Ôßæì ÇáãÓÊÎÏã: ÇáÔÑßÉ ÇáÇİÊÑÇÖíÉ áÇ ÊõÍÏóøÏ ÊáŞÇÆíğÇ ÚäÏ İÊÍ ÇáÊÚÏíá ÇáãÈÇÔÑ¡ æÇáÇÓã íÈŞì `—` ÈÚÏ ÇáÍİÙ. ÇáÓÈÈ ÇáÌĞÑí ááÚÑÖ: `toArray()` İí Laravel íõãíöøå ÃÓãÇÁ ÇáÚáÇŞÇÊ (**snake_case**) — ÇáÍŞá íŞÑÃ `$order['shippingProvider']['name']` (camel) ÈíäãÇ ÇáãİÊÇÍ ÇáÍŞíŞí `$order['shipping_provider']` (ÊÍŞøŞ ãä tinker) ? íõÚÑÖ `—` ÏÇÆãğÇ. ÇáÍá: (1) ÊÕÍíÍ ÇáãİÊÇÍ Åáì `shipping_provider` İí `orders-table-cell`º (2) `startOrderProviderEdit` íÕíÛ ÇáŞíãÉ ÇáÇİÊÑÇÖíÉ ááãÊÌÑ (ãËá ÏÑÌ ÇáÊÃßíÏ) ÚäÏãÇ áÇ Êãáß ÇáØáÈíÉ ÔÑßÉº (3) ÚãæÏ ãØáæÈ ÈáÇ ÔÑßÉ íÚÑÖ ÊáãíÍ «ÇÎÊÑ ÔÑßÉ ÇáÊæÕíá» (`order_flow.select_shipping_provider`) ÈÏá `—`¡ ÚÈÑ ÊãÑíÑ `isRequired` ááãßæøä ÇáÎáæí. **ÇÎÊÈÇÑ ÌÏíÏ `OrderShippingProviderColumnTest` (5 ÍÇáÇÊ: ÊáŞÇÆí ÇáÇİÊÑÇÖí¡ ÇáÇÍÊİÇÙ ÈÇáÔÑßÉ ÇáŞÇÆãÉ¡ ÍİÙ ÇáÇÎÊíÇÑ¡ ÇáÊáãíÍ¡ ÚÑÖ ÇáÇÓã).**

ãáÇÍÙÉ ÕÑÇÍÉ Úä ÇáÚÏÏ: ÇáÅÌãÇáí ÇáãÊÊÈÚ ÃÚáÇå (446/1645 ? äÍæ 31.6) áã íÊØÇÈŞ ãÚ ÊÔÛíá `vendor/bin/pest` ÇáßÇãá ÇáÍÇáí (426/1536 — íÔãá ÇáÇÎÊÈÇÑÇÊ ÇáÌÏíÏÉ)º ÇáãÚÊãÏ ÇáÂä åæ **431 äÇÌÍ (1541 assertions)** ãä ÇáÌæáÉ ÇáßÇãáÉ ÇáäÙíİÉ.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 31.7 ÈæÇÈÉ ÇáÊÃßíÏ + ãÄÌá | ? | app/Domains/Order/Services/OrderService.php + app/Domains/Shipping/Services/OrderShippingGateway.php + app/Enums/Store/OrderStatus.php + database/seeders/SystemStatusesSeeder.php + database/migrations/2026_09_07_000001_confirm_status_green.php + orders/index.blade.php + partials/orders-table-actions-column.blade.php + partials/orders-table-cell.blade.php + a OrderConfirmGateTest + ÊÚÏíá OrderActionToastUnificationTest | 6 İí OrderConfirmGateTest + 8 İí OrderActionToastUnificationTest (ÈÓøáØÇä ãõÍÏóøË) + 36 İí ÇáÌæáÇÊ ÇáãÊÃËÑÉ (80 assertions) |
| **ÇáÅÌãÇáí (ÌæáÉ äÙíİÉ)** | **426 äÇÌÍ (1536 assertions)** | | |

## Phase 31.7 — Êßãíá (Ã) ÊáãíÍÇÊ ÇáÃÚãÏÉ ÇáãØáæÈÉ + (È) ÒÑ ÊÃßíÏ ãæÈÇíá ÈÇÑÒ + (Ì) ŞÇÆãÉ «ãåÇã ãÊÚÏÏÉ» — 2026-09-07

**ÈãæÇİŞÉ ÕÑíÍÉ ãä ÇáãÓÊÎÏã («äİøĞ ÇáÂä») ÈÚÏ ÎØÉ ãäŞøÍÉ ËõÈøÊÊ ÈÃÓÆáÉ: (Ã) ÊáãíÍÇÊ áÜ5 ÃÚãÏÉ ãØáæÈÉ İŞØ¡ (È) ÒÑ ÇáÊÃßíÏ ÏÇÎá Õİ ÅÌÑÇÁÇÊ ÈØÇŞÉ ÇáãæÈÇíá¡ (Ì) ÒÑ æÇÍÏ «ãåÇã ãÊÚÏÏÉ» íÙåÑ ÈÚÏ ÍŞá ÇáÈÍË æíõİÊÍ ŞÇÆãÉ ãäÓÏáÉ Èßá ÇáãåÇã.**

- **(Ã) ÇáÊáãíÍÇÊ — `orders-table-cell.blade.php`:** ãÊÛíÑ «ÕÇİí» `$requiredHint` ÃÚáì Çáãßæøä ÇáÎáæí ÚÈÑ `match($colKey)` (delivery_type / shipping_provider / wilaya / city / stopdesk_point) íõÑÇÏóİ ãä `$isRequired` ÇáããÑøÑº ÇáÎáÇíÇ ÇáÎãÓ ÊÚÑÖ `<span class="text-warning font-medium">íÑÌì ÇÎÊíÇÑ…</span>` ÈÏá `—`/`-` áãÇ íßæä ÇáÍŞá ÇáãØáæÈ İÇÑÛğÇ. ÔÑØ stopdesk: ÇáÊáãíÍ **İŞØ** ÚäÏãÇ `delivery_type === 'stopdesk'` (áÇ íÖÌøÑ ÇáØáÈíÇÊ ÇáÚÇÏíÉ). ÊáãíÍ ÇáæáÇíÉ ÃõÖíİ ÃíÖğÇ áÈØÇŞÉ ÇáãæÈÇíá İí `index.blade.php`. ãáÇÍÙÉ ÊŞäíÉ: `delivery_type` ÚãæÏ `NOT NULL` İí ŞÇÚÏÉ ÇáÈíÇäÇÊ¡ İãÓÇÑ ÊáãíÍå Óõáøãí/ÛíÑ Íí (ÃõÈŞí ááÇÊÓÇŞ ãÚ ÇáÜ5 ÇáãõÚÊãÏíä).
- **ÇáÊÑÌãÇÊ:** ãİÇÊíÍ `order_flow.please_select_{shipping_provider,delivery_type,state,city,stopdesk}` İí ÇááÛÇÊ ÇáÃÑÈÚ (ar/en/es/fr) + **ÅÒÇáÉ** `select_shipping_provider` (ar/en) æÊæÍíÏå Åáì `please_select_shipping_provider` (ãÚ ÊÍÏíË ÊÃßíÏ ÇáÇÎÊÈÇÑ).
- **(È) ÒÑ ÊÃßíÏ ÇáãæÈÇíá — `index.blade.php`:** ÏÇÎá Õİ ÇáÅÌÑÇÁÇÊ (`mt-3 flex gap-2 flex-wrap`) ÒÑ `edz-btn--primary` ÈÇáÚäæÇä `confirm_title` + ÃíŞæäÉ åÇÊİ¡ ÔÑØ ÙåæÑå `!showTrash && $order['can_confirm'] && canStore(ORDER_CONFIRM)` — íİÊÍ äİÓ ÏÑÌ ÇáÊÃßíÏ `openConfirmModal`º Õİ ÅÌÑÇÁÇÊ ÇáÈØÇŞÉ ÃÕÈÍ æÌåÉ áãÓå ÇáÃÓåá (44px+) æİí ÇáãŞÏãÉ.
- **(Ì) «ãåÇã ãÊÚÏÏÉ» — `bulk-actions-bar.blade.php`:** ÃõÚíÏÊ åíßáÊå ãä ÔÑíØ ßÇãá Åáì **ÒÑ-ŞÇÆãÉ ãäÓÏáÉ æÇÍÏÉ**: ÒÑ primary «ãåÇã ãÊÚÏÏÉ» + ÔÇÑÉ ÚÏÏ ÇáãÍÏÏ + Óåãº ÇáŞÇÆãÉ: ÚÏÇÏ¡ ŞÓã ÇáÅÓäÇÏ (ÃÚÖÇÁ)¡ ŞÓã ÇáÅÏÇÑÉ (ÅÑÓÇá ááÔÑßÉ / ÊÛííÑ ÇáÍÇáÉ — ãŞäøÚ ÈÜ ORDER_MANAGE)¡ Ëã ÍĞİ (ÊÃßíÏ EdzSwal + `bulkDelete` ãÚ `loading-target` ÇáÜglobal ÇáãÍİæÙ) æÊäÙíİ ÇáÊÍÏíÏ (`merchant.bulk_clear`). M10 ãÍİæÙ: ÊÚØíá + ÍáŞÉ spinner ÏÇÎá ÇáÒÑ ÃËäÇÁ Ãí ÚãáíÉ ÌãÇÚíÉ. áÇÍÙ: `:class` Úáì ÃíŞæäÉ `chevron-down` ÊãÑøÑ ááÜcomponent ßÜPHP İßÇä «Undefined constant open» — Íõæöøá Åáì `x-bind:class`.
- **ÇáãæŞÚ:** `@include` ÇäÊŞá ãä İæŞ ÇáÌÏæá Åáì **ÏÇÎá ÔÑíØ ÇáÃÏæÇÊ İæÑğÇ ÈÚÏ ÍŞá ÇáÈÍË** ÈÔÑØ `count($this->selectedOrders) > 0` (áÇ íÙåÑ ÅáÇ ÚäÏ ÇáÊÍÏíÏ).
- **ÇáÇÎÊÈÇÑÇÊ — `OrderRequiredColumnsHintTest.php` (9):** ÊáãíÍ wilaya + city + shipping_provider ÇáİÇÑÛ¡ ÊáãíÍ stopdesk ÚäÏ stopdesk İŞØ (áÇ íÚÑÖ ãÚ home)¡ ÇáÃÚãÏÉ ÇáÇÎÊíÇÑíÉ ÊÈŞì `-` (áÇ ÊáãíÍ)¡ ÒÑ ÇáÊÃßíÏ ÇáãæÈÇíá íÙåÑ áØáÈíÉ `can_confirm` (æíõÓÊÏÚì ÈÜ`escape: false` áÃä `assertSee` íõäãøÕ ÚáÇãÇÊ ÇáÇŞÊÈÇÓ) æíÎÊİí ÈÚÏ ÇáÔÍä¡ æÇáŞÇÆãÉ ÇáãäÓÏáÉ ÊÙåÑ İí ÔÑíØ ÇáÃÏæÇÊ İŞØ ÚäÏ `selectedOrders>0`. İÎø ÇáÇÎÊÈÇÑ: `$opts['x'] ?? default` íÈÊáÚ `null` ÇáÕÑíÍ ? ÈõÏöøá Åáì `$opts += […]`.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `php -l` äÙíİ + `view:clear`/`view:cache` (FRESH-CACHE-OK) + ÇáÌæáÇÊ ÇáÌÏíÏÉ 9/9 (17 assertions) + `OrderShippingProviderColumnTest` 5/5 + **ÇáÌæáÉ ÇáßÇãáÉ ÇáäÙíİÉ 440 äÇÌÍ (1558 assertions)** (İÔá æÍíÏ ÚÇÈÑ İí ÊÔÛíá Ãæá ßÇä Şİá `storage/framework/views` Úáì æíäÏæÒ — ÇÌÊÇÒ 9/9 ãäİÑÏğÇ ÈÚÏ `view:clear`).

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 31.7 Êßãíá Ã+È+Ì | ? | orders/index.blade.php + partials/orders-table-cell.blade.php + partials/bulk-actions-bar.blade.php + resources/lang/{ar,en,es,fr}/order_flow.php + merchant.php + OrderRequiredColumnsHintTest (ÌÏíÏ) + ÊÍÏíË OrderShippingProviderColumnTest | 9 İí OrderRequiredColumnsHintTest + 5 İí OrderShippingProviderColumnTest |
| **ÇáÅÌãÇáí (ÌæáÉ äÙíİÉ)** | **440 äÇÌÍ (1558 assertions)** | | |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ãÙåÑ ÊáãíÍÇÊ ÇáÃÚãÏÉ İí ÇáÌÏæá æÇáãæÈÇíá¡ ÈÇÑÒæíÉ ÒÑ «ÊÃßíÏ ÇáØáÈíÉ» İí ÈØÇŞÉ ÇáãæÈÇíá¡ Óáæß ÒÑ «ãåÇã ãÊÚÏÏÉ» (İÊÍ ÇáŞÇÆãÉ ãä ÇáÃÓİá ãæÈÇíá / ãäÓÏáÉ ÏíÓßÊæÈ) æÇÎÊİÇÆå ÈÚÏ ãÓÍ ÇáÊÍÏíÏ.

## ÅÕáÇÍÇÊ ÇáİÍÕ ÇáÈÕÑí 31.7 (ÇáãÓÊÎÏã ßÔİ 4 äŞÇØ) — 2026-09-07

**äİøĞÊ ÈÚÏ İÍÕ Íí ÔÇãá + ÊæËíŞ ÇáÎØÉ æãæÇİŞÉ ÇáãÓÊÎÏã ÇáÕÑíÍÉ (ÇÎÊíÇÑÇÊå: ÊÍÓíä ÈáÇ ÓßíãÇ / ÈÍË ÏÇÎá ÇáŞÇÆãÉ / ÒÑ İí Õİ ÇáÅÌÑÇÁÇÊ İŞØ):**

- **(1) ÇáÃÚãÏÉ ÇáãØáæÈÉ Úáì ÇáãæÈÇíá:** partial ÌÏíÏ `partials/orders-mobile-fields.blade.php` íÑÓøã ÍŞæá ÇáÌÛÑÇİíÇ/ÇáÊæÕíá (delivery_type, shipping_provider, city, stopdesk_point, address + ÏãÌ) ÏÇÎá ÈØÇŞÉ ÇáãæÈÇíá ÈäİÓ ãäØŞ ÇáÊáãíÍÇÊ `please_select_*`º ÃõÏÑÌ İí ÇáÈØÇŞÉ ÈÚÏ ÇáæáÇíÉ¡ æÔÑØ `shipping_cost` ÊÑöİÚ ÇáŞíÏ `&& !in_array('total', …)` İÙåÑ İí ÇáÈØÇŞÉ ÏÇÆãğÇ ÚäÏ ÊİÚíáå. **Ïæä ÇÓÊÚáÇãÇÊ ÅÖÇİíÉ** (íÚíÏ ÇÓÊÎÏÇã `$order` ÇáãÍãøá æÇáÎíÇÑÇÊ ÇáÌÇåÒÉ `editProviderOptions`/`editCityOptions`/`editStopdeskOptions`).
- **(2) ÇáÊßáíİ ÇáÌãÇÚí ÈÈÍË:** ÇÓÊÈÏÇá ÃÓØÑ ÇáÃÚÖÇÁ (`@foreach allMembers`) Èãßæøä `<x-edz.select>` ÈÍË + ÒÑ «ÊØÈíŞ» íÑÊÈØ ÈÜ`bulkAssignMembershipId` ÇáÌÏíÏÉ¡ ãÚ ÍÇÑÓ `blank($membershipId)` İí `bulkAssignAgent` íãäÚ ãÓÍ ÇáÅÓäÇÏ ÈÇáÎØÃ.
- **(3) ÈæÈ-ÂÈ ÇáÊÃßíÏ ÃĞßì (ÈáÇ ÓßíãÇ):** ÇáãáÎÕ íÚÑÖ ÇáÂä `confirmation_attempts` + `last_contact_at` (diffForHumans)¡ æÍŞá «ãáÇÍÙÉ» (`confirmNote`) íõÎÒóøä İí `meta['confirm_note']` ÚäÏ ÇáÊÃßíÏ İŞØ (ÊÃßíÏ İŞØ / ÊÃßíÏ æÅÑÓÇá) æíõÚÇÏ ÊÚÈÆÊå ÚäÏ İÊÍ ÇáÏÑÌ.
- **(4) ÒÑ ÇáÊÃßíÏ ÇáãÒÏæÌ:** ÒÑ «ÊÃßíÏ ÇáØáÈíÉ» İí `orders-table-actions-column` ãŞíøÏ ÈÜ`layout === 'compact'` İŞØ — íÎÊİí ãä ŞÇÆãÉ «…» ÇáãæÈÇíá (`list`) æíÈŞì İí Õİ ÅÌÑÇÁÇÊ ÇáÈØÇŞÉ ãÑÉ æÇÍÏÉ.
- **ÇáÇÎÊÈÇÑÇÊ (+5 İí OrderRequiredColumnsHintTest):** ÙåæÑ ÇáÍŞæá ÇáÎãÓÉ İí ÇáÈØÇŞÉ ÇáãæÈÇíá¡ ÇáÊßáíİ ÚÈÑ ÈÍË ÈÇáÒÑ ÊØÈíŞ¡ ÇáÏÑÌ íÚÑÖ ÇáãÍÇæáÇÊ/ÂÎÑ ÊæÇÕá + ÇáãáÇÍÙÉ¡ ÍİÙ ÇáãáÇÍÙÉ İí `meta` ÚäÏ ÊÃßíÏ İŞØ¡ æÒä `wire:click` ÇáÎÇÕ ÈÇáÊÃßíÏ = 2 (ÏíÓßÊæÈ + ÈØÇŞÉ) áÇ 3 (áÇ ÊßÑÇÑ İí ÇáŞÇÆãÉ). **ÇáÌæáÉ ÇáßÇãáÉ ÇáäÙíİÉ: 445 äÇÌÍ (1571 assertions)** (ŞÈáåÇ 440/1558)¡ `view:clear`+`view:cache` ÓáíãÉ.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| ÅÕáÇÍÇÊ ÇáİÍÕ ÇáÈÕÑí 31.7 | ? | orders/index.blade.php + partials/orders-mobile-fields.blade.php (ÌÏíÏ) + partials/bulk-actions-bar.blade.php + partials/orders-table-actions-column.blade.php + 4 ÊÑÌãÇÊ order_flow (confirm_note) + OrderRequiredColumnsHintTest (+5) | 14 İí OrderRequiredColumnsHintTest + 6 İí OrderConfirmGateTest + 19 İí ÌæáÇÊ ÇáÊÃßíÏ/ÇáÔÍä/ÇáÊæÓÊ |
| **ÇáÅÌãÇáí (ÌæáÉ äÙíİÉ)** | **445 äÇÌÍ (1571 assertions)** | | |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ÇáÈØÇŞÉ ÇáãæÈÇíá ÊÚÑÖ ÇáÂä ÔÑßÉ ÇáÊæÕíá/äæÚíÉ ÇáÊæÕíá/ÇáÈáÏíÉ/ÇáÚäæÇä/ÓÚÑ ÇáÊæÕíá¡ ŞÇÆãÉ «ãåÇã ãÊÚÏÏÉ» ÈåÇ ÈÍË ÚäÏ Êßáíİ ÚÖæ¡ ãáÎÕ ÇáÊÃßíÏ ÇáÌÏíÏ (ãÍÇæáÇÊ + ÂÎÑ ÊæÇÕá + ãáÇÍÙÉ)¡ æÒÑ ÊÃßíÏ æÇÍÏ İŞØ İí ÇáÈØÇŞÉ.

## Phase 31.8 — ÇáÊÚÏíá ÇáãÖãøä ááãäÊÌÇÊ/ÇáãÊÛíÑÇÊ/ÇáßãíÉ/ÇáÓÚÑ + ÅÚÏÇÏ ÇáÓãÇÍ ÈÊÚÏíá ÇáÓÚÑ + ÕáÇÍíÉ ORDER_EDIT_PRICE + ãßæøä Checkbox — 2026-09-07

**ÈãæÇİŞÉ ÕÑíÍÉ («äİĞ») ÈÚÏ ÎØÉ ãËÈøÊÉ ÈÃÓÆáÉ ÇáãÓÊÎÏã (ŞÑÇÑÇÊå: ãÍÑÑ ãÏãÌ ãæÍøÏ ááÃÚãÏÉ ÇáËáÇËÉ ÈÍİÙ æÇÍÏº ÇÍÊÑÇã ÇáÓÚÑ ÇáãßÊæÈ ÚäÏ `allow_price_edit` + `ORDER_EDIT_PRICE` ãÚ ÊÚÏíá ŞÇÚÏÉ C3º ÅÕáÇÍ ãÕİæİÉ ÕİÍÉ ÇáİÑíŞ áÊÙåÑ ßá ÍÇáÇÊ ÇáÅäã ãÚ ÔÇÑÉ custom).**

- **(1) ÇáÅÚÏÇÏ + ÇáÕáÇÍíÉ + Çáãßæøä:**
  - Migration `2026_09_07_000001_add_allow_price_edit_to_store_settings.php` (boolean **false** ÇİÊÑÇÖíğÇ) + `StoreSetting` fillable/cast `allow_price_edit`.
  - `StorePermissionEnum::ORDER_EDIT_PRICE = 'order.edit.price'` + ãİÇÊíÍ `permissions.order.*` ÇáãÊÏÇÎáÉ İí ar/en/es/fr.
  - ãßæøä ÌÏíÏ `resources/views/components/edz/checkbox.blade.php` (ÏÚã `wire:model`/`wire:click` æÍÌã sm/md ælabel/hint/disabled) + `.edz-checkbox` SCSS İí `resources/css/components/_forms.scss` (ßÇäÊ 5 ÇÓÊÎÏÇãÇÊ ÈáÇ CSS ? ÃÕÈÍÊ ÊõÚÑÖ ÕÍíÍÉ ÊáŞÇÆíğÇ).
  - ÅÚÇÏÉ ÇÓÊÎÏÇã checkbox: store-settings (ÊÈæíÈ ÇáÊÌÇÑÉ: `allow_price_edit` ÇáÌÏíÏ + `guest_checkout` + ãÎÒæä) + orders (selectAll/Õİæİ ÏíÓßÊæÈ/ÈØÇŞÉ ãæÈÇíá/ãæÏÇá ÅÚÏÇÏ ÇáÃÚãÏÉ) + teams (`is_active` + ãÕİæİÉ ÇáÕáÇÍíÇÊ).
  - ÅÕáÇÍ teams: `$allPermissions` = `StorePermissionEnum` ßáå ãÌãøÚğÇ (`groupBy` Úáì ÇáÈÇÏÆÉ) ÈÏá ÇáŞÇÆãÉ ÇáÌÒÆíÉ.
- **(2) ÇáãÍÑÑ ÇáãÖãøä ááÚäÇÕÑ — `orders/index.blade.php` + partial ÌÏíÏ `orders-inline-items-editor.blade.php`:**
  - ÇáÎáÇíÇ ÇáËáÇË (products/quantity/price) İí `orders-table-cell.blade.php` ÕÇÑÊ ÃÒÑÇÑ `startOrderItemsEdit(orderId)` ÚäÏ `ORDER_MANAGE` (ÏíÓßÊæÈ + ÒÑ «ÊÚÏíá ÇáãäÊÌÇÊ…» İí ÈØÇŞÉ ÇáãæÈÇíá).
  - ÇáãÍÑÑ Õİ `<tr colspan>` ÊÍÊ Õİ ÇáØáÈ (ÏíÓßÊæÈ) / ÍŞá ÈÇáÈØÇŞÉ (ãæÈÇíá): ßá ÚäÕÑ ÈÕİ (ÇáÇÓã + SKU¡ ÎØæÉ ßãíÉ ãØÇÈŞÉ ááãæÏÇá¡ ÓÚÑ ÇáæÍÏÉ ãŞíøÏğÇ¡ ÇáÅÌãÇáí¡ ÍĞİ) + ÈÍË ÅÖÇİÉ ãäÊÌ (`itemsAddSearch` debounce 500ms ? `searchInlineItems` ÈÍÏ 25¡ ãÊÛíÑ ãÊÚÏÏ íÚÑÖ ŞÇÆãÉ ãÊÛíÑÇÊå inline ÚÈÑ `toggleInlineAddProduct`, ÅÖÇİÉ ÚÈÑ `addInlineItem`?`addFormItem` áÅÚÇÏÉ ÇáÇÓÊÎÏÇã).
  - **ŞÇÚÏÉ ÇáÓÚÑ (C3 ÇáãÚÏøáÉ):** `$saveOrderItems` íŞÑÃ `itemsPriceEditable()` (ãõĞßóøÑÉ `static` áßá ØáÈº ÊÚÇÏá `allow_price_edit && canStore(ORDER_EDIT_PRICE)`) — ÚäÏ ÇáÊİÚíá íõÍÊÑã ÇáÓÚÑ ÇáãßÊæÈ æíõÎÒóøä (ãÚ `subtotal`/`total_amount`/ÅÚÇÏÉ ÍÓÇÈ shipping)¡ æÅáÇ íõİÑÖ ÓÚÑ DB ÏÇÆãğÇ. ÅÒÇáÉ/ÅÖÇİÉ/ÊÚÏíá ÇáßãíÇÊ ÚÈÑ `OrderItem` ãÈÇÔÑÉ (äİÓ äãØ `submitEdit`)¡ ãÚ İÍÕ ÇáãÎÒæä (delta) æÍÇÑÓ `guardOrderEditable` (ÇáÔÍä íÚØøá ÇáÍİÙ).
  - ÊÑÌãÇÊ ÌÏíÏÉ: `merchant_panel.no_items` / `delete_item` / `edit_items` İí 4 áÛÇÊ.
- **ÇáÇÎÊÈÇÑÇÊ — `OrderInlineItemsEditTest` (11):** İÊÍ ÇáãÍÑÑ æÊÍãíá draft¡ İÑÖ ÓÚÑ DB ÈáÇ ÇáÅÚÏÇÏ¡ ÇÍÊÑÇã ÇáÓÚÑ ÚäÏ ÇáÅÚÏÇÏ+ÇáÕáÇÍíÉ (owner)¡ staff ãÎÕÕ `order.manage` ÈáÇ ÍŞá ÓÚÑ æÓÚÑ DB¡ staff ÈÜ`order.manage+order.edit.price` íÍÊÑã ÇáÓÚÑ¡ ÇáÅÚÏÇÏ æÍÏå íßİí¿ (áÇ)¡ ÅÒÇáÉ+ÅÖÇİÉ ÚäÕÑíä¡ ÍÌÈ backorder¡ ÍÌÈ ÇáÔÍä¡ ÑİÖ ÈÏæä `order.manage`¡ æÇáŞíãÉ ÇáÇİÊÑÇÖíÉ false. **İÎø ãÄßÏ:** ÇáŞæÇÆã ÇáãÎÕÕÉ ááÚÖæíÉ ÊõáÛí ÕáÇÍíÇÊ ÇáÏæÑ ßáíğÇ ? ãÌãæÚÉ ÇáÕáÇÍíÇÊ ÇáãÎÊÈÑÉ **íÌÈ Ãä ÊÊÖãä `ORDER_VIEW`** æÅáÇ `abort_unless(ORDER_VIEW)` (index.blade.php:512) íŞØÚ ÇáÚÑÖ Îáİ «Invalid snapshot» ãÖáøá İí ÇáÜharness.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `view:clear`+`view:cache` ÓáíãÉ¡ ÇáÌæáÇÊ ÇáãÊÃËÑÉ (inline edits + roles/gates + settings + query-count) **74 äÇÌÍ (427 assertions)** Ëã **ÇáÌæáÉ ÇáßÇãáÉ ÇáäÙíİÉ 457 äÇÌÍ (1622 assertions)**.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 31.8 ãÍÑÑ ÇáÚäÇÕÑ + ÅÚÏÇÏ ÇáÓÚÑ + ÇáÕáÇÍíÉ | ? | database/migrations/2026_09_07_000001_add_allow_price_edit_to_store_settings.php + app/Models/Stores/StoreSetting.php + app/Enums/Store/StorePermissionEnum.php + resources/views/components/edz/checkbox.blade.php (ÌÏíÏ) + resources/css/components/_forms.scss + livewire/merchant/store-settings.blade.php + livewire/merchant/teams/index.blade.php + livewire/merchant/orders/index.blade.php + partials/orders-inline-items-editor.blade.php (ÌÏíÏ) + partials/orders-table-cell.blade.php + permissions.php×4 + merchant_panel.php×4 + OrderInlineItemsEditTest (ÌÏíÏ) | 11 İí OrderInlineItemsEditTest (40 assertions) + 74 İí ÇáÌæáÇÊ ÇáãÊÃËÑÉ (427 assertions) |
| **ÇáÅÌãÇáí (ÌæáÉ äÙíİÉ)** | **457 äÇÌÍ (1622 assertions)** | | |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — İÊÍ ÇáãÍÑÑ ÈÇáäŞÑ Úáì ãäÊÌ/ßãíÉ/ÓÚÑ (ÌÏæá ÏíÓßÊæÈ + ÒÑ ÈØÇŞÉ ÇáãæÈÇíá)¡ ÚäÇÕÑ ÇáãÍÑÑ ÈÎØæÇÊ ÇáßãíÉ æÍĞİ æÈÍË ÇáÅÖÇİÉ¡ ÅÎİÇÁ ÍŞá ÇáÓÚÑ áÚÖæ ÈáÇ `order.edit.price`¡ ÙåæÑ ãİÊÇÍ «ÇáÓãÇÍ ÈÊÚÏíá ÇáÓÚÑ» İí ÊÈæíÈ ÇáÊÌÇÑÉ (ÅÚÏÇÏÇÊ ÇáãÊÌÑ)¡ æãÕİæİÉ ÕİÍÉ ÇáİÑíŞ ÊÚÑÖ ßá ÍÇáÇÊ ÇáÅäã ÇáÌÏíÏÉ.

---

## Phase 31.9 — ÈæÈ ÃÈ ÊÚÏíá ÈäæÏ ÇáØáÈíÇÊ áßá ÚãæÏ Úáì ÍÏÉ (ÈÏá ÇáãÍÑÑ ÇáãÖãøä) + ÅÕáÇÍ Îáá Checkbox — 2026-09-08

**ÈãæÇİŞÉ ÕÑíÍÉ («äİĞ») ÈÚÏ İÍÕ ÇáÓÈÈ ÇáÌĞÑí æŞÑÇÑÇÊ ÇáãÓÊÎÏã:** ÇáãÓÊÎÏã æÌÏ ÇáÊÕãíã ÇáãÖãøä ÇáÓÇÈŞ (31.8) «ÓíÆğÇ æÛíÑ ÚãáíğÇ» æÃÚÇÏ ÇáÇÊÌÇå ÇáãŞÑÑ ÓÇÈŞğÇ (ãÍÑÑ ãÏãÌ ÈËáÇËÉ ÃÚãÏÉ) ? **ÈæÈ ÃÈ ãÓÊŞá áßá ÚãæÏ** (ãäÊÌÇÊ / ßãíÇÊ / ÃÓÚÇÑ) ÈäãØ Pattern A¡ æ**ßá ÚãæÏ íÚÏøá áæÍÏå**. ÅÌÇÈÇÊ ÊæÖíÍíÉ: (Ã) ÇáãæÈÇíá = ÒÑ æÇÍÏ «ÊÚÏíá ÇáãäÊÌÇÊ…» íİÊÍ æÑŞÉ ÓİáíÉ ÕÛíÑÉ ÈËáÇËÉ ÎíÇÑÇÊº (È) ÚãæÏ ÇáÓÚÑ ÚäÏ ÛíÇÈ ÇáÕáÇÍíÉ = **äÕ ÓÇßä ÈáÇ Ãí ÒÑ/ŞÇÆãÉ**.

- **(1) ÅÕáÇÍ ÇáÜCheckbox — ÇáÓÈÈ ÇáÌĞÑí:** İí `components/edz/checkbox.blade.php` ßÇä `$checked !== null ? 'checked' : ''` — ãÌÑÏ **æÌæÏ** ÇáÎÇÕíÉ (Ãí ŞíãÉ) íÍÓÈ İí HTML ÈãËÇÈÉ ãÍÏøÏ¡ æáåĞÇ ÈÏÊ ÇáÎÇäÇÊ ÏÇÆãğÇ ãÍÏøÏÉ İí: ÎÇäÇÊ Õİæİ ÇáØáÈíÇÊ (ÏíÓßÊæÈ 4454 + ãæÈÇíá 4516) æãİÇÊíÍ ãæÏÇá ÅÚÏÇÏ ÇáÌÏæá (~4968). ãÓÊÎÏãÇÊ `wire:model` ÓáíãÉ áÃä ÅÏÇÑÉ Livewire áÇ ÊãÑÑ `checked`. **ÇáÅÕáÇÍ:** `$checked === true ? 'checked' : ''`. ÃÏáÉ: ÇÎÊÈÇÑ ÊÑãíÒ `Blade::render` — `:checked="true"` ? ÊÍÊæí Úáì `\schecked\b`¡ æ`:checked="false"`/ÈÏæäåÇ ? áÇ ÊÍÊæí (ÃõÖíİ Öãä `OrderInlineItemsEditTest`).
- **(2) ÅÚÇÏÉ ÇáåíßáÉ Åáì äæÇİĞ áßá ÚãæÏ:**
  - `index.blade.php`: state `itemsModal = null`º ÇÓÊÈÏÇá `startOrderItemsEdit`/`cancelOrderItemsEdit` ÈÜ`openItemsModal(kind, orderId)`/`closeItemsModal()`. ÇáÃæá: ÍÇÑÓ `ORDER_MANAGE` (ÊæÓÊ `permission_denied` ÚäÏ ÇáÑİÖ)¡ ŞÇÆãÉ ÃäæÇÚ ãÓãæÍÉ (`products/quantity/price`)¡ ãäÚ `price` ÈáÇ `itemsPriceEditable()`¡ æÈäÇÁ draft ãä `items_summary` ÇáãæÌæÏ ÃÕáğÇ (áÇ ÇÓÊÚáÇã ÅÖÇİí) Ëã `startEdit('order.items', ...)`º ÇáÃÎíÑ: ÕİÑ `itemsModal` + `cancelEdit()` + ÊÕİíÑ ÇáÈÍË + **`form['items'] = []`** (ÇáÊÎáÕ ãä ÇáäÓÎÉ). `saveOrderItems` ÊõÛáŞ ÇáäÇİĞÉ ÚÈÑ `closeItemsModal()` ÈÚÏ ÇáäÌÇÍ æÊÈŞíåÇ ãİÊæÍÉ ãÚ `editingError` ÚäÏ İÔá ÇáÊÍŞŞ.
  - ÍĞİ Õİ `<tr colspan>` (items-editor-row) ãä ÇáÌÏæá æÍŞá ÇáãæÈÇíá ÇáãÖãøä ãÚ **ÍĞİ ÇáíÊíã** `partials/orders-inline-items-editor.blade.php`.
  - ÇáãæÈÇíá: ÒÑ æÇÍÏ «ÊÚÏíá ÇáãäÊÌÇÊ…» íÚÑÖ `itemsEditMenu` (ãßæøä Alpine ÌÏíÏ İí `resources/js/components/order-row-actions.js` + ÊÓÌíáå İí `panel.js`) — æÑŞÉ ÓİáíÉ <639px/áæÍÉ ãËÈÊÉ sm+ (äãØ `orderMoreMenu`)¡ Õİæİ products/quantity/price (ÇáÃÎíÑ ÔÑØí ÈÜ`itemsPriceEditable()`)¡ ÃÒÑÇÑ 44px+ æhover æÇÖÍÉ.
  - ÇáÎáÇíÇ: products ? `openItemsModal('products', …)`º quantity ? `openItemsModal('quantity', …)`º price ? ÒÑ İŞØ ÚäÏ `ORDER_MANAGE && itemsPriceEditable()` æÅáÇ **äÕ ÓÇßä**º ÍõĞİÊ İÑæÚ `editingField === 'order.items'` ãä `orders-table-cell.blade.php`.
  - partial ÌÏíÏ `orders-items-edit-modals.blade.php` (ËáÇË äæÇİĞ Pattern A ÚÈÑ `x-edz.modal` + `:is-open="true"` + `wire:key`º ŞæÇÆã `max-h-[45vh]` ÈÍÏ ÃŞÕì¡ äÕ «áÇ ãäÊÌÇÊ» ÚäÏ ÇáİÑÇÛ) + `orders-items-modal-footer.blade.php` (ÎØÃ ÇáÊÍŞŞ + ÅáÛÇÁ/ÍİÙ ãÚ spinner). **äŞØÉ ÊŞäíÉ ãÄßÏÉ:** `:class="…"` Úáì æÓæã ÇáãßæøäÇÊ (`x-edz.*`) íÌãøÚå Blade ßÜPHP (ÇÕØÏÇã ãÚ Alpine) ? íõÓÊÎÏã `x-bind:class` (äãØ `bulk-actions-bar.blade.php:24`)º æÇáÓáÇÓá MUTA-DEFENSIVE `$item['price'] ?? 0`/`$item['quantity'] ?? 1` İí ßá ÚÑÖ.
  - ŞæÇÚÏ ÇáÓáæß (ãä 31.8 æÊÈŞì ßãÇ åí): C3 áİÑÖ/ÇÍÊÑÇã ÇáÓÚÑ¡ ÍÇÑÓ `guardOrderEditable` (ÇáÔÍä)¡ İÍÕ ÇáãÎÒæä (delta) áÍÇÌÒ ÇáÜbackorder¡ `addInlineItem`?`addFormItem`¡ ÈÍË debounce 500ms ÈÍÏ 25. áÇ ãİÇÊíÍ ÊÑÌãÉ ÌÏíÏÉ (ßáåÇ ŞÇÆãÉ).
- **ÇáÇÎÊÈÇÑÇÊ — `OrderInlineItemsEditTest` (12/62):** İÊÍ äÇİĞÊí products+quantity ãÚ `itemsModal.kind` æÊÍãíá draft Ëã ÅÛáÇŞ íÕİøÑ `form.items`º İÑÖ ÓÚÑ DB ÈáÇ ÇáÅÚÏÇÏº ÇÍÊÑÇã ÓÚÑ owner ÚäÏ `allow_price_edit` + ÅÚÇÏÉ ÍÓÇÈ ÇáÅÌãÇáíº staff `order.manage` ÈáÇ äÇİĞÉ ÓÚÑ (İŞÑÉ ÓÇßäÉ) æÓÚÑ DBº staff ÈÜ`order.manage+order.edit.price` íÑì æíÍÊÑã ÇáÓÚÑº ÇáÅÚÏÇÏ æÍÏå áÇ íßİíº ÅÒÇáÉ+ÅÖÇİÉ ÚäÕÑíäº ÍÌÈ backorderº ÍÌÈ ÇáÔÍäº ÑİÖ ÈÏæä `order.manage`º ŞíãÉ ÇİÊÑÇÖíÉ `false`º ÇÎÊÈÇÑ ÊÑãíÒ checkbox. **ÇáİÎø ÇáãÄßÏ ãä 31.8 ãÇ ÒÇá ÓÇÑíğÇ** (ŞæÇÆã ÇáÕáÇÍíÇÊ ÇáãÎÕÕÉ ÊÓÊáÒã `ORDER_VIEW`).
- **ÇáÊÍŞŞ ÇáäåÇÆí:** ÇáÌæáÉ ÇáßÇãáÉ ÇáäÙíİÉ **458 äÇÌÍ (1644 assertions)** (ßÇäÊ 457/1622 — ÇáİÑŞ = ÇÎÊÈÇÑ checkbox + 22 ÊÃßíÏğÇ) Ëã `view:clear`+`view:cache` ÓáíãÉ + **`npm run build`** (ÊÛíøÑÊ JS: `panel-C6Dgvy4c.js` / `app-BrEjoHgF.js`).

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 31.9 äæÇİĞ ÊÚÏíá ÇáÚäÇÕÑ + ÅÕáÇÍ Checkbox | ? | resources/views/components/edz/checkbox.blade.php (ÅÕáÇÍ) + resources/views/livewire/merchant/orders/index.blade.php (closers/äæÇİĞ/ŞÇÆãÉ ÇáãæÈÇíá) + partials/orders-items-edit-modals.blade.php (ÌÏíÏ) + partials/orders-items-modal-footer.blade.php (ÌÏíÏ) + partials/orders-table-cell.blade.php (ÎáÇíÇ?openItemsModal) + partials/orders-inline-items-editor.blade.php (ÍõĞİ) + resources/js/components/order-row-actions.js + resources/js/panel.js + tests/Feature/Merchant/OrderInlineItemsEditTest.php | 12 İí OrderInlineItemsEditTest (62 assertions) + ÇáÌæáÉ ÇáßÇãáÉ 458 (1644) |
| **ÇáÅÌãÇáí (ÌæáÉ äÙíİÉ)** | **458 äÇÌÍ (1644 assertions)** | | |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ÇáäŞÑ Úáì ÚãæÏ ÇáãäÜtÌÇÊ æÇáßãíÉ íİÊÍ äÇİĞÊå ÇáãÚäíÉ¡ ÚãæÏ ÇáÓÚÑ **äÕ ÓÇßä** áãÓÊÎÏã ÈáÇ ÕáÇÍíÉ / äÇİĞÉ ÓÚÑ áãä íãáßåÇº ÒÑ ÇáãæÈÇíá «ÊÚÏíá ÇáãäÊÌÇÊ…» íİÊÍ æÑŞÉ ÓİáíÉ ÈËáÇËÉ ÎíÇÑÇÊº ÎÇäÇÊ row-select æÇáãİÇÊíÍ áã ÊÚÏ ãÍÏøÏÉ ÕİÑğÇ ãÓÈŞğÇ (ÇáÅÕáÇÍ)º æíÈŞì ÊÍŞŞ 31.8: ãİÊÇÍ «ÇáÓãÇÍ ÈÊÚÏíá ÇáÓÚÑ» İí ÅÚÏÇÏÇÊ ÇáãÊÌÑ æãÕİæİÉ ÇáİÑíŞ Èßá ÍÇáÇÊ ÇáÅäã.

---

## Phase 32 — ÇáÊÓÚíÑ ÇáÏíäÇãíßí ááãßÊÈí (stopdesk) æÊÑÌíÍ ŞÇÆãÉ ÇáÃÓÚÇÑ (ÓÈÊãÈÑ 2026) ?

**ÈãæÇİŞÉ ÕÑíÍÉ ãä ÇáãÓÊÎÏã («äİøĞ») Úáì ÎØÉ ÇáÊÓÚíÑ ÇáÏíäÇãíßí İí äÇİĞÊí ÇáÅÖÇİÉ/ÇáÊÚÏíá æÇáãáÎÕ ÇáãÇáí. Çáãáİ ÇáãÑÌÚí: `OrdersrefactorplanFixed.md` (ŞÑÇÑÇÊ §9 + ãáÇÍÙÉ ÇáãÕÏÑ ÇáãÄÌáÉ).**

### ŞÑÇÑÇÊ ÇáãÓÊÎÏã (binding) ÇáÊí ÊÍßã ÇáÓáæß
- **ßÔİ «íÑÌì ÊÍÏíÏ» ÈÏá «ãÌÇäí» ÇáãÖáöøá:** ÚäÏ äŞÕ ÈíÇäÇÊ ÇáÊÓÚíÑ (ãäÒáí ÈáÇ æáÇíÉ¡ Ãæ ÇáãßÊÈí ÈáÇ æáÇíÉ/ÔÑßÉ) íõÚÑÖ ÇáÊáãíÍ `shipping_hint_delivery` İí ÇáÔÈßÉ ÇáãÇáíÉ æÇáãæÏÇá¡ æíÈŞì ãÕÏÑ ÇáÓÚÑ İí ÇáÌÏæá åæ ÇáæÍíÏ ÇáÍŞíŞí.
- **ÊÛííÑ ÇáÔÑßÉ íÚíÏ ÖÈØ ÇáæÌåÉ:** ÊÛííÑ ÇáÔÑßÉ İí stopdesk íÚíÏ ÊÕİíÑ æáÇíÉ/ÈáÏíÉ/ãßÊÈ ÎÇÑÌ ÊÛØíÉ ÇáÔÑßÉ ãÚ ÊæÓÊ `order_flow.destination_reset_for_carrier` (ÊáãíÍ `no_company_coverage` ÚäÏ ÔÑßÉ ÈáÇ ÊÛØíÉ Úáì ÇáÅØáÇŞ).
- **ŞÇÆãÉ ÇáÃÓÚÇÑ ÊÊİæŞ Úáì ÇáÔÑßÉ ÇáãÎÊÇÑÉ (ááÊæÕíá ÇáãäÒáí İŞØ):** ÈíÇäÇÊ `PriceList` (æáÇíÉ/ÈáÏíÉ + free_above) áåÇ ÇáÃÓÈŞíÉ Úáì `home_cost` ÇáÔÑßÉ ÇáãÎÊÇÑÉ ÚäÏ ÇáÊæÕíá ááãäÒá.
- **ãßÊÈí stopdesk:** íõÓÚóøÑ ãä `delivery_rates.office_cost` (ãÚ `free_above` ÈÇáÇÊÌÇåíä)¡ æÇáÊÑÇÌÚ ÚäÏ ÛíÇÈå ? ÓÚÑ ÇáÔÍä legacy (`ShippingRate` áÔÑßÉ `rate`) ? æÃÎíÑğÇ `office_unavailable` (áÇ «ãÌÇäí» ÃÈÏğÇ ÈáÇ ÈíÇäÇÊ).
- **ãÕÏÑ ÇáÓÚÑ:** ÊõÄÌóøá ÅÖÇİÉ ÚãæÏ «ÇáãÕÏÑ» İí ÌÏæá ÇáØáÈíÇÊ — `orders.shipping_cost` íÈŞì ÇáãÕÏÑ ÇáæÍíÏ ÇáãÄßóøÏº ÊõÚÑÖ «ãÚáäÉ/ãä ŞÇÆãÉ ÇáÃÓÚÇÑ/ÓÚÑ ËÇÈÊ» İŞØ İí ÇáãáÎÕ ÍÇáíÇğ.

### ãÇ Êóãø

- **`app/Domains/Shipping/Services/ShippingCostCalculator.php` — ÃõÚíÏÊ ßÊÇÈÊåÇ (ÇáãÕÏÑ ÇáæÍíÏ):**
  - ÇáÊæŞíÚ ÇáÌÏíÏ `calculate(array $order)` íÚãá Úáì ÈäíÉ ãæÍÏÉ íãßä Ãä ÊÃÊí ãä ÈäÇÁ ãÄŞÊ ááãæÏÇá Ãæ ãä äãæĞÌ Orderº ÊãÑíÑ `provider_id` + `delivery_type` ÕÑÇÍÉğ (ÍÊì íõÓÚóøÑ ÇáãßÊÈí ŞÈá ÍİÙ ÇáØáÈíÉ).
  - **ÇáãßÊÈí (stopdesk):** ? `delivery_rates.office_cost` (ãÚ `free_above` äÇŞÕ ÇáãÕÇÑíİ «ÈÇáÇÊÌÇåíä»: ãÌÇäí ÚäÏ override Ãæ ÚäÏ ÊÓÇæí/ÊÌÇæÒ ÇáÚÊÈÉ)¡ ? ÇáÊÑÇÌÚ áÓÚÑ ÇáÔÍä legacy `ShippingRate` (`method='rate'`¡ `source_type='company'`)¡ ? `office_unavailable` (ãŞÊÑäğÇ ÈÜ `cost=0` æ `office_unavailable=true`) — **áÇ «ãÌÇäí» ÃÈÏğÇ ÈáÇ ÈíÇäÇÊ** (ßÓÑ ááÓáæß ÇáŞÏíã ÇáĞí ßÇä íŞÑÃ ŞÇÆãÉ ÇáÃÓÚÇÑ ááãßÊÈí ÈÇáÎØÃ).
  - **ÇáãäÒáí (home):** ? `PriceList` (æáÇíÉ ÇáÈáÏíÉ Ëã ÇáæáÇíÉ¡ `free_above` ÚäÏ ÊÌÇæÒ ÇáÚÊÈÉ¡ æÇÑË `source_type='price_list'`) — **íÊİæŞ Úáì ÇáÔÑßÉ ÇáãÎÊÇÑÉ**¡ ? Ëã `DeliveryRate` ÇáãõÚáä ááÔÑßÉ (`home_cost`/city)¡ ? Ëã flat rate ÇáÔÑßÉ¡ ? Ëã legacy `ShippingRate`¡ ? Ëã free (source `none`).
  - **ŞÈíØÉ ÇáÔÑßÉ (provider_id):** ÊŞíøÏ ÇáÈÍË Úä announced ratesº flat rate íõÓÚóøÑ ãä `provider_id` ÇáãÚØÇÉ.
  - ÅÕáÇÍ ÎØÃ: ÕäÇÏíŞ `office_cost/free_above` İí DB ßÇäÊ strings ? cast ÕÑíÍ Åáì float ŞÈá ÇáÍÓÇÈ (ÚÈøÑ Úä Ğáß İí ÇáÇÎÊÈÇÑÇÊ ÈßÔæİ `(float)`).

- **`app/Domains/Order/Services/OrderService.php` (createManual ~ÓØÑ 145):** ÊãÑíÑ `provider_id` + `delivery_type` ãä ÇáãæÏÇá Åáì ÇáÍÇÓÈÉ ? íõÓÚóøÑ stopdesk ÚäÏ ÇáÅäÔÇÁ (áã íßä ãÓÈŞğÇ æÇßÊÔİÊå ÇáÇÎÊÈÇÑÇÊ ÇáÌÏíÏÉ).

- **`resources/views/livewire/merchant/orders/index.blade.php` — ÊÊÇáí ÇáÔÑßÉ/ÇáæÌåÉ/ÇáãßÊÈ (ÅÚÇÏÉ ÊØÈíŞ ßÇãáÉ ÈÚÏ ÍÇÏËÉ ÊÑãíÒ):**
  - closure `$providerOfficeStates($providerId)` (æáÇíÇÊ ÇáãßÇÊÈ ÇáãÊÇÍÉ áÏì ÔÑßÉ ÚÈÑ `obtained states` + stopdesk points)¡ æ`$applyProviderScope()` (íõÓÊÏÚì ÚäÏ `wire:change` Úáì ÇáÔÑßÉ + ÚäÏ `changeDeliveryType`) — ÅĞÇ áã íÚÏ ÇáæÌåÉ/ÇáãßÊÈ Öãä ÊÛØíÉ ÇáÔÑßÉ ÇáÌÏíÏÉ: íõÕİóøÑ ãÚ ÊæÓÊ `destination_reset_for_carrier`º ÈáÇ ÊÛØíÉ ÅØáÇŞğÇ ? ÊáãíÍ `no_company_coverage`.
  - `$loadCities(string $stateId, bool $resetCity = true)`: ÈáÏíÇÊ stopdesk ÇáÂä **ãŞíøÏÉ ÈäŞÇØ stopdesk** (ÍÕÑ İÚáí) + ÊáãíÍ «áÇ ÈáÏíÉ» `no_company_coverage`º `$releaseStaleDestination()` (ãßÊÈ/ãÏíäÉ ÎÇÑÌÉ Úä ÇáäØÇŞ).
  - `$loadFormScope()`/`providerOfficeStates`/`releaseStaleDestination` ÊõÓÊÏÚì İí `openDeliveryModal`/`openEditModal` æÕİøÑÉ İí `openCreateModal`º `recalculateOrderShipping` íãÑÑ `providerId` + `deliveryType` ÈáÇ ÅÑÌÇÚ ãÈßÑ (áã íóÚõÏ ãäÒáíğÇ İÍÓÈ).
  - `$saveOrderProvider` (inline) æ`$saveOrderService` + äÇİĞÉ ÇáÊÚÏíá ÇáÓÑíÚ: ÅÓŞÇØ ÇáãßÊÈ ÇáĞí áã ÊÚÏ ÇáÔÑßÉ ÇáÌÏíÏÉ ÊÎÏãå ãÚ äİÓ ÇáÊæÓÊ.
  - **ÍÇÏËÉ ÊÑãíÒ ÃËäÇÁ ÇáÇÍÊæÇÁ:** ãÓøÍ ßÊÇÈÉ ÓßÑÈÊ PowerShell (`Set-Content`) áãáİ index.blade.php Íæøá UTF-8 Åáì ÈÇíÊÇÊ ÛíÑ ÕÇáÍÉ ? ÇÓÊõÚíÏ Çáãáİ ãä HEAD (`git checkout --`) æÃÚíÏ ÊØÈíŞ **ßá** ÇáÊÛííÑÇÊ ÈÃÏÇÉ ÇáÊÍÑíÑ ÇáÂãäÉ (ÊÍŞøŞ `check-encoding.php` + `php -l` + `view:cache`). ÊÍĞíÑ ÃõÏÑÌ: áÇ íõßÊÈ UTF-8 ÚÈÑ PowerShell PS5.1 `>`/`Set-Content` ÃÈÏğÇ.

- **`partials/order-form-modal.blade.php` (äÇİĞÊÇ ÇáÅÖÇİÉ):** `wire:change` ááÔÑßÉ ? `applyProviderScope($event.target.value)`º ÒÑ home ÚäÏ ÇáÊÈÏíá íÓÊÏÚí `changeDeliveryType('home')` ÃíÖğÇº ÎíÇÑÇÊ ÇáæáÇíÉ `formAvailableStates !== [] ? … : allStates` + ÓØÑ `formCoverageHint`º ÓáÓáÉ ÊáãíÍ ÇáãßÊÈ ÇáãÍÓøäÉ ÈİÑÚ `office_none_for_destination`.

- **`partials/order-financial-summary.blade.php` — ÃõÚíÏÊ ßÊÇÈÊåÇ:** ßÊáÉ `@php` ÊÍÓÈ `$delivery/$deliveryIncomplete/$deliveryUnavailable` ÚÈÑ ÈæÇÈÉ (ÇáãßÊÈí: ÔÑßÉ + æáÇíÉº ÇáãäÒáí: æáÇíÉº íÊØáÈ ÃÕäÇİ) æÊÓÊÏÚí ÇáÍÇÓÈÉ ÈäİÓ `providerId`+`deliveryType` ÇáãÓÊÎÏãíä ÚäÏ ÇáÍİÙº ÈØÇŞÉ ÇáÊæÕíá ÊÚÑÖ: ÊáãíÍ `shipping_hint_delivery` ÚäÏ äÇŞÕ ? ãİÊÇÍ `no_company_coverage` ÚäÏ ÔÑßÉ ÈáÇ ÊÛØíÉ ? `storefront.shipping_unavailable` ÚäÏ ÛíÑ ãÊÇÍ ? ãÌÇäí ? ÓÚÑ + ÓØÑ ãÕÏÑ (`shipping_source_price_list`/`shipping_source_flat`/`shipping_source_announced` ãÚ :provider).

- **ÇáÊæÕíá ÇáÓÑíÚ (`delivery-edit-modal.blade.php`):** äİÓ ÇáÈæÇÈÉ/ÇáÅÓŞÇØÇÊ ÚÈÑ ÇáØÑŞ ÇáãÔÊÑßÉº áÇ ãİÇÊíÍ ÌÏíÏÉ ÊÖÇİ ÈÎÕæÕå.

- **ÊÑÌãÇÊ ×4 áÛÇÊ (en/ar/fr/es):**
  - `order_flow`: `destination_reset_for_carrier`, `no_company_coverage`.
  - `merchant_panel`: `shipping_hint_delivery`, `shipping_source_announced`, `shipping_source_price_list`, `shipping_source_flat`, `office_none_for_destination`.

### ÇáÇÎÊÈÇÑÇÊ — `tests/Feature/Merchant/OrderShippingDynamicPricingTest.php` (11/30)
- ãßÊÈí: íáÊŞØ office_cost ÇáÔÑßÉ ÇáãØáæÈÉº íŞÚ Úáì office_cost ÇáÔÑßÉ ÇáÇİÊÑÇÖíÉ ÚäÏ ÛíÇÈ providerº ÈáÇ office_cost ? `office_unavailable` (áÇ ãÌÇäí)º **íÊÌÇåá ŞÇÆãÉ ÇáÃÓÚÇÑ æíŞÚ Úáì legacy** (400 ãä `ShippingRate` áÇ 300 ãä ÇáŞÇÆãÉ)º `free_above` íÚãá ááãßÊÈí Ïæä base.
- ãäÒáí: ŞÇÆãÉ ÇáÃÓÚÇÑ 650 ÊÊİæŞ Úáì `home_cost` 1000 ááÔÑßÉ ÇáãÎÊÇÑÉº `provider_id` íŞíøÏ ÇáÜannouncedº flat rate íÊÈÚ `provider_id`.
- createManual: ÍİÙ office_cost ßÜ `shipping_cost`º ÕİÑ ÚäÏãÇ `office_unavailable`º ÇáãäÒáí ãä ŞÇÆãÉ ÇáÃÓÚÇÑ 650.

### ÇáÏáíá ÇáäåÇÆí
- `OrderFinancialSummaryTest` (7/30) ÚÏøá ÊÃßíÏ ÇáÇÎÊÈÇÑ ÇáÃæá áíØÇÈŞ ÇáŞÑÇÑ: ÔÈßÉ ÇáÅäÔÇÁ ãÚ ÃÕäÇİ æÈáÇ æáÇíÉ ÊÚÑÖ `shipping_hint_delivery` ÈÏá ÔÇÑÉ «ãÌÇäí» ÇáŞÏíãÉ ÇáãÖáöøáÉ. **ÈæÇÈÉ ÇáÊÓÚíÑ ááãäÒáí ÈáÇ æáÇíÉ ÊÈŞí ÇáÔÈßÉ ãÍÇíÏÉ ÊãÇãğÇ.**
- `php -l` áßá ÇáãáİÇÊ ÇáãÚÏáÉ + `view:cache` (FRESH-CACHE-OK) + ÇÓÊåÏÇİ `OrderOfficeSelectionTest` + `OrderFinancialSummaryTest` + `OrderShippingDynamicPricingTest` ? 21 äÇÌÍ.
- **ÇáÓæíÊ ßÇãáÉ äÙíİÉ: 479 äÇÌÍ (1712 assertions)** — ãŞÇÑäÉğ ÈÎØ ÇáÃÓÇÓ 468/1682 ŞÈá ÇáÚäŞæÏ (+11 ÇÎÊÈÇÑ / +30 ÊÃßíÏ = ãáİ ÇáÊÓÚíÑ ÇáÌÏíÏ İŞØ) — **ÕİÑ ÇäÍÏÇÑ**.

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ÊáãíÍ «íÑÌì ÊÍÏíÏ» İí ÇáÔÈßÉ ÇáãÇáíÉ ÚäÏ İÊÍ ÅÖÇİÉ ÈáÇ æáÇíÉ/ÔÑßÉº ÊÛííÑ ÇáÔÑßÉ İí stopdesk íÓŞØ æÌåÉ ÎÇÑÌ ÇáÊÛØíÉ ÈÊæÓÊº ÊÑÌíÍ ŞÇÆãÉ ÇáÃÓÚÇÑ ááãäÒáí (ÓØÑ «ãä ŞÇÆãÉ ÇáÃÓÚÇÑ» İí ÇáãáÎÕ)º æãÔÇÑßÉ äİÓ ÇáÓáæß İí äÇİĞÉ ÇáÊÚÏíá ÇáÓÑíÚ.

## Phase 33 — ÍÕÑ ÇáÈáÏíÇÊ + ãİÊÇÍ ÇáÃÓÚÇÑ ÇáãÚáäÉ + ãÒÇãäÉ äŞÇØ ÇáÇÓÊáÇã + ÊÊÈÚ NOEST ÇáÂáí (ÓÈÊãÈÑ 2026) ?

**ÇáäØÇŞÇÊ ÇáÃÑÈÚÉ ÇáãÚÊãÏÉ ãä ÇáãÓÊÎÏã (ÃæÇãÑ ÕÑíÍÉ) — ÇßÊãáÊ ÌãíÚğÇ ãÚ ÃÏáÉ ŞÇÈáÉ ááŞíÇÓ.**

### (1) ÍÕÑ ÇáÈáÏíÇÊ İí äãæĞÌ ÇáØáÈíÉ ÍÓÈ ÔÑßÉ ÇáÊæÕíá
- `app/Domains/Shipping/Models/StopdeskPoint.php` ? `communitiesCoveredFor(string $storeId, string $providerId, string $stateId): array` — äŞÇØ äÔØÉ `city_id NOT NULL` ÎÇÕÉ ÈÇáãÊÌÑ/ÇáÔÑßÉ/ÇáæáÇíÉ¡ `distinct()->pluck()->all()`.
- `orders/index.blade.php`: `loadCities` íØÈøŞ ÇáÍÕÑ áßá ÔÑßÉ (áÇ ÈæÇÈÉ carrier) + ãÒÇãäÉ ãÓÈŞÉ ááÔÑßÇÊ carrier-backed ÏÇÎá try/catchº ÔÑßÉ ÈáÇ ÊÛØíÉ ? ŞÇÆãÉ İÇÑÛÉ + ÊáãíÍ `formCoverageHint='no_company_coverage'` (áÇ fallback áßá ÇáÈáÏíÇÊ)º `startOrderCityEdit` íÍÕÑ ÎíÇÑÇÊ ÇáãÍÑÑ ÇáãÖãøä ÈÜ`whereIn` Ãæ `whereRaw('1 = 0')`.
- ÇáÃÏáÉ: `tests/Feature/Merchant/OrderCityScopeProviderTest.php` — **5 äÇÌÍÉ (8 assertions)**: ÍÕÑ ÇáãæÏÇá¡ áÇ ÊÛØíÉ ? İÇÑÛ + ÊáãíÍ¡ home íõÈŞí Çáßá¡ carrier-backed íäÌæ ãä İÔá ÇáãÒÇãäÉ¡ ÇáãÍÑÑ ÇáãÖãøä ãÍÕæÑ. ãÍØÊÇä ãÄßóøÏÊÇä: `toBeCanonicalizing` ÛíÑ ãæÌæÏÉ İí Pest ? ãŞÇÑäÉ íÏæíÉ ÈÚÏ ÊÑÊíÈº slug ãäÕÉ İÑíÏ áÊÌäÈ ÊÕÇÏã `noest` ÇáãÒÑæÚ.

### (2) ãİÊÇÍ ÅíŞÇİ/ÊÔÛíá ÇáÃÓÚÇÑ ÇáãÚáäÉ (DeliveryRate)
- `announced-rates.blade.php`: `loadRates` íÊÖãä `is_active`º closure `toggleRateActive(string $stateId)` (íŞáÈ ÕİğÇ ŞÇÆãğÇ İŞØ¡ íÍÏøË `ratesByState`¡ ÍÇÑÓ DELIVERY_PRICING_MANAGE)º switch íÙåÑ İŞØ ÚäÏ æÌæÏ Õİ (`$hasRateRow`)¡ Õİæİ ãÚØøáÉ ÊßÊÓÈ ÔÇÑÉ + ÍŞæá/ÒÑ ÅÏÇÑÉ ãŞİáÉ. ÇáãİÊÇÍ Tailwind ÎÇáÕ (`translate-x-[1.25rem] rtl:-translate-x-[1.25rem]`) — áÇ ÃíŞæäÇÊ toggle ÛíÑ ãæËóøŞÉ İí icon.blade.php.
- ÊÑÌãÇÊ ×4: `rate_enable`/`rate_disable` (en/ar/fr/es).
- ÇáÃÏáÉ: `tests/Feature/Merchant/AnnouncedRatesToggleTest.php` — **3 äÇÌÍÉ (11 assertions)**: flip İí DB+state¡ áÇ Õİ ? áÇ switch + no-op¡ ÇáÊÚØíá íÒíá ÇáÓÚÑ ãä ÇáÍÇÓÈÉ (`ShippingCostCalculator` íİáÊÑ `is_active=true`).

### (3) æÇÌåÉ ãÒÇãäÉ äŞÇØ ÇáÇÓÊáÇã (stopdesk) + ÔÇÑÉ ÇáãÒÇãäÉ
- `stopdesk.blade.php`: `syncCandidates` (ÔÑßÇÊ carrier ÈßæÏ İí `delivery.carrier_integrations`) + `synced` áßá äŞØÉ (`filled(external_code)`) + closure `syncStopdesk` (íÓÊÏÚí `StopdeskOfficeSync::sync(..., refresh:true)`¡ ÑÓÇÆá swal äÌÇÍ/İÇÑÛ/ÈáÇ ãÍæá¡ `@disabled` ÃËäÇÁ ÇáÊäİíĞ) + ÈØÇŞÉ ÇáãÒÇãäÉ + ÔÇÑÉ check-circle.
- ÊÑÌãÇÊ ×4 (`stopdesk_sync_*`, `stopdesk_synced`, `sync_no_adapter`).
- ÇáÃÏáÉ: `tests/Feature/Merchant/StopdeskSyncUiTest.php` — **4 äÇÌÍÉ (16 assertions)**: İáÊÑÉ ÇáãÑÔÍíä¡ ÔÇÑÉ ÇáãÒÇãäÉ¡ ÇáäŞÑ íÌáÈ ÇáãßÇÊÈ æíÍÏøË ÇáäŞÇØ ÚÈÑ `Http::fake(['noest.test/*'=>...])`¡ ÔÑßÉ ÈáÇ ÇÚÊãÇÏÇÊ ? ÑÓÇáÉ info. ÇÕØáÇÍ ãËÈøÊ: İÍÕ ÇáÃÍÏÇË ÚÈÑ `assertDispatched('swal', type: 'success')` (ãÓãøÇÉ) ãŞÇÈá `swal:toast` (ãÕİæİÉ ãæÖÚíÉ).

### (4) NOEST P1 — ÊÊÈÚ Âáí (ÎÑíØÉ ÃÍÏÇË + ÌæÈ + ÌÏæáÉ)
- `app/Domains/Shipping/Services/NoestTrackingMapper.php` — ÎÑíØÉ `event_key` NOEST (ãä æËÇÆŞ v2.3) ? `OrderTrackingStatus`: `livre/livred`?DELIVERED¡ `fdr_activated/nouvel_tentative/return_redispatched_to_livraison`?OUT_FOR_DELIVERY¡ `mise_a_jour`?FAILED_ATTEMPT¡ ÓáÓáÉ `return_asked_*`?RETURNING Ëã `retour_dispatched_*/colis_retour_*/livraison_echoue_recu/return_validated`?RETURNED¡ `upload/customer_validation`?SHIPPED¡ `validation_*/sent_to_redispatch/annulation/cancel_return_dispatched`?IN_TRANSITº ÃÍÏÇË ãÇáíÉ/ÊÚÏíáÇÊ ? null (áÇ ÊÎãíä). Fallback ÚÈÑ äÕæÕ ÇáÃÍÏÇË ÈÜ`OrderTrackingStatus::fromCarrier`.
- `NoestIntegrationAdapter::trackingsInfo(...)` — POST `/get/trackings/info` ÈÜBearer¡ ÏİÚÇÊ ?20¡ ÚæÏÉ ãÒæøÏÉ ÈãİÊÇÍ ÇáÊÊÈÚ¡ ÎØÃ ÈäİÇÏ ãåáÉ/HTTP.
- `app/Domains/Shipping/Jobs/SyncNoestTrackingJob.php` — áßá ÔÑßÉ noest äÔØÉ: Õİæİ ãÓÊÍŞøÉ (`null delivered_at/returned_at` Ãæ ÈáÇ ãÒÇãäÉ Ãæ ÃŞÏã ãä 15 ÏŞíŞÉ) ? ÏİÚÇÊ ? ÊÍÏíË `carrier_status/carrier_label/tracking_status/carrier_raw` + `shipped_at` (Ãæá ÍÏË) + `delivered_at`/`returned_at` (ÊÇÑíÎ ÍÏË ÇáÍÇáÉ) ? ÊÌÏíÏ `last_synced_at`º áÇ ÇäÍÏÇÑ ááÍÇáÇÊ ÇáäåÇÆíÉº ÓÌá `OrderTrackingHistory` ÚäÏ ÊÛíøÑ ÇáÍÇáÉ İŞØ (`payload.carrier_sync`). ÇáÃÎØÇÁ ãÍÈæÓÉ ÈÜ`report()` æÊõãÑóøÑ ÇáÊÔÛíá.
- `routes/console.php` — `Schedule::call(...)->name('sync-noest-trackings')->everyTenMinutes()->withoutOverlapping()` íæÒøÚ ÌæÈøÇ áßá ãÊÌÑ áå ÔÑßÉ noest.
- ÇáÃÏáÉ: `tests/Feature/Shipping/NoestTrackingSyncTest.php` — **6 äÇÌÍÉ (23 assertions)**: ÊŞÏã Åáì delivered ãÚ `delivered_at` (ÊÇÑíÎ ÍÏË livre) + ÓÌá ÍÇáÉ¡ ÎÑíØÉ ÇáÅÑÌÇÚ Åáì returned¡ ÍÏË ÛíÑ ãÑÓæã ? áÇ ÊÛííÑ¡ İÔá ÇáÎÇÏã ãÍÈæÓ¡ ÚÏã ÅÚÇÏÉ ÓÈÑ ÇáäåÇÆíÇÊ ÇáØÇÒÌÉ (áÇ ÇäÍÏÇÑ æáÇ ãÖÇÚİÉ ÓÌáÇÊ)¡ æÇáÜadapter íäÔÑ ÏİÚÉ Bearer ÕÍíÍÉ. ãÍØÊÇä ãÄßóøÏÊÇä: `handle()` ÊÓÊŞÈá `?StopdeskOfficeSync` (ÇáÍöŞä ãä ÇáŞÇÆãÉ íãáÄå æÇáÇÓÊÏÚÇÁ ÇáãÈÇÔÑ ÚÈÑ `??=`); İÍÕ ÇáÊÇÑíÎ ÚÈÑ `isSameDay` áÇ ÃÑŞÇã ËÇÈÊÉ (ÍÓÇÓíÉ ãäØŞÉ ÒãäíÉ ááÌáÓÉ İí ãÓÇÑ write/read). **áã ÊõáãÓ** ãáİÇÊ ÇáÊÊÈÚ ÇáËáÇËÉ ÛíÑ ÇáãáÊÒãÉ ááãÓÊÎÏã.

### ÇáãÏì Çáßáí
| | ÇáÎØ ÇáÃÓÇÓ (ŞÈá ÇáÚäŞæÏ) | ÇáÂä | ÇáÏáÊÇ |
|---|---|---|---|
| ÇÎÊÈÇÑÇÊ | 479 | **497** | +12 (ÈäæÏ 1-3) + 6 (ÈäÏ 4) |
| ÊÃßíÏÇÊ | 1712 | **1770** | +35 + 23 |
| ÇäÍÏÇÑ | 0 | **0** (ÌæáÉ ßÇãáÉ äÙíİÉ¡ ~350s) | — |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ÊáãíÍ «áÇ ÈáÏíÉ» ÚäÏ ÔÑßÉ ÈáÇ ÊÛØíÉ İí ÇáãæÏÇá/ÇáãÍÑÑ ÇáãÖãøä¡ ãİÊÇÍ ÅíŞÇİ ÇáÃÓÚÇÑ İí ÕİÍÉ ÇáÃÓÚÇÑ ÇáãÚáäÉ (æÕİæİ ãÚØøáÉ)¡ ÈØÇŞÉ ãÒÇãäÉ äŞÇØ ÇáÇÓÊáÇã + ÔÇÑÉ synced¡ æÚÑÖ ÃÚãÏÉ ÍÇáÉ ÇáÊÊÈÚ ÇáãÍÏøËÉ İí ÕİÍÉ ÇáÊÊÈÚ ÈÚÏ ÇáÊÔÛíá ÇáÊáŞÇÆí ááÌæÈ.

---

## ÈäæÏ ÇáãÊÌÑ (Ã–Ò) — ãİÊÇÍ ÇáÃÓÚÇÑ ÇáÔÇãá + ÇáÍÕÑ ÇáÕÇÑã + ÍÇÌÒ ÇáãÊÌÑ + ãáÇÍÙÉ ÇáÓÚÑ + ÇáãÒÇãäÉ ÇáãÌÏæáÉ + ÇáÔÇÑÉ (ÓÈÊãÈÑ 2026) ?

**ÓÈÚÉ ÈäæÏ ãÚÊãÏÉ ãä ÇáãÓÊÎÏã (ÃæÇãÑ ÕÑíÍÉ) — ÇßÊãáÊ ÌãíÚğÇ ãÚ ÃÏáÉ ŞÇÈáÉ ááŞíÇÓ.**

### Ã — ãİÊÇÍ ÅíŞÇİ/ÊÔÛíá ßÇãá áÃÓÚÇÑ ßá ÔÑßÉ (Master toggle)
- `announced-rates.blade.php`: closure `toggleAllRatesActive($providerId)` íŞáÈ `is_active` áßá Õİæİ `DeliveryRate` ááÔÑßÉ ÏİÚÉ æÇÍÏÉ (`where(...)->update`)¡ Ëã `loadRates()` áÅÚÇÏÉ ÇáÈäÇÁº UI: ãİÊÇÍ ÑÆíÓí İí ÑÃÓ ÈØÇŞÉ ßá ÔÑßÉ ãÚ ÔÇÑÇÊ ÍÇáÉ (enabled/mixed/disabled) ÚÈÑ `countByStatus`.
- ÊÑÌãÇÊ ×4: `rates_disable_all`/`rates_enable_all`/`rates_master_label`/`rates_master_enabled`/`rates_master_mixed`/`rates_master_disabled`/`rates_total` (trans_choice) + `no_rates_yet`.
- ÇáÃÏáÉ: `tests/Feature/Merchant/AnnouncedRatesToggleTest.php` — **6 äÇÌÍÉ (25 assertions)**.

### È — ÍÕÑ ÕÇÑã áäŞÇØ ÇáÇÓÊáÇã ÈÇáæáÇíÉ æÇáÈáÏíÉ
- `orders/index.blade.php`: `rebuildFormOffices` íÚíÏ ÇáÈäÇÁ ÈÜ **`where('state_id', $form['state_id'])->where('city_id' ...)` ÈáÇ `orWhereNull`** — äŞÇØ ÈáÇ ÌÛÑÇİíÇ ÊÎÊİí ãä ÎíÇÑÇÊ ÇáÅÑÓÇá æÊÈŞì İí ÕİÍÉ ÅÏÇÑÊåÇº `inlineStopdeskOptions` (ãÍÑÑ ÇáÔÑßÉ) íÍÕÑ ÈæáÇíÊí/ÈáÏíÉ ÇáØáÈíÉ İŞØ.
- È/Ã — ÇáæáÇíÉ ÅáÒÇãíÉ İí äãæĞÌ ÇáãßÊÈ: ãõÊÍŞóøŞ ÃÕáğÇ (`stopdeskForm.state_id => 'required'`).
- ÇáÃÏáÉ: `tests/Feature/Merchant/OrderOfficeSelectionTest.php` — **19 äÇÌÍÉ (63 assertions)**.

### Ï — ÍÇÌÒ ÅÑÓÇá ÇáãÊÌÑ (ÔÑßÉ/ãæÕøá äÔØ)
- `app/Domains/Order/Services/OrderCompleteness.php`: `storeReadyForDispatch(?string $storeId)` — **`?string` æáíÓ `?int`** (Store ÊÓÊÎÏã ULID) — íİÍÕ æÌæÏ `ShippingProvider::is_active` Ãæ `DeliveryRider::is_active` ááãÊÌÑº key `carrier_not_configured` íõÖÇİ Åáì `missing()` İŞØ ÚäÏ `$forSend=true` (ÇáÅÑÓÇá Ïæä ÇáÊÃßíÏ).
- ÊÑÌãÇÊ ×4: `order_flow.carrier_not_configured`.
- ÇáÃÏáÉ: 3 ÇÎÊÈÇÑÇÊ ÅÖÇİíÉ İí `OrderCompletenessTest` (ÍÌÈ ÚäÏ ÛíÇÈ ÔÑßÉ/ãæÕøá¡ ÔÑßÉ ÛíÑ äÔØÉ¡ ãæÕøá äÔØ íÍŞŞ ÇáÌÇåÒíÉ).

### åÜ — ãáÇÍÙÉ ÇáÓÚÑ ÚäÏ ÇáÅÑÓÇá (unpriced / zero_cost)
- `app/Domains/Shipping/Services/OrderShippingGateway.php`: `resolveRateNote()` íõÑÌÚ `'unpriced'` (áÇ ÓÚÑ ãõÚáä áÔÑßÉ ÇáØáÈíÉ) / `'zero_cost'` (ÓÚÑ ãÚáä = 0) / `null` (ãõÓÚóøÑ)º íõÖÇİ ßÜ `$result['rate_note']` ÈÚÏ `send()`.
- UI (`orders/index.blade.php`): `confirmAndSend`/`sendConfirmedOrder`/`confirmBulkSend` ÊÓÊÚÑÖ `swal:toast` ÊÍĞíÑíÉ (icon warning) ÈÇáãİÊÇÍ ÇáãäÇÓÈ ÚäÏ æÌæÏ `rate_note` — ÅÚáÇã ÈáÇ ãäÚ.
- ÊÑÌãÇÊ ×4: `rate_note_unpriced`/`rate_note_zero_cost`/`bulk_send_rate_note`.
- ÇáÃÏáÉ: 4 ÇÎÊÈÇÑÇÊ ÅÖÇİíÉ İí `OrderCompletenessTest` (unpriced¡ ÕİÑ¡ ãõÓÚóøÑ ÈáÇ ãáÇÍÙÉ¡ ãÚáä ãÚØóøá = unpriced).

### æ — ãÒÇãäÉ ãßÇÊÈ ÇáÇÓÊáÇã ãÌÏæáÉ + áÍÙíÉ
- `app/Domains/Shipping/Jobs/SyncStopdeskOfficesJob.php` (ÌÏíÏ): `chunkById(20)` áßá ÇáÔÑßÇÊ carrier-backed ÇáäÔØÉ ááãÊÌÑ¡ ÊÒÇãä ÚÈÑ `StopdeskOfficeSync::sync`¡ ßá ÔÑßÉ ãÚÒæáÉ (ÎØÃ æÇÍÏÉ áÇ ÊŞÊá ÇáÈŞíÉ).
- `routes/console.php`: `SyncStopdeskOfficesJob::twiceDaily(3, 15)->withoutOverlapping()`.
- áÍÙí: `providers.blade.php` `saveProvider` íõÑÓá ÇáÌæÈ ãÈÇÔÑÉ ÈÚÏ ÍİÙ ÔÑßÉ carrier-backed.
- ÇáÃÏáÉ: `tests/Feature/Shipping/SyncStopdeskOfficesJobTest.php` — **5 äÇÌÍÉ (9 assertions)**.

### Ò — ÔÇÑÉ «áÇ ÃÓÚÇÑ ÈÚÏ» + ÊÑÔíÍ ÇáÔÑßÇÊ ÛíÑ ÇáäÔØÉ
- ÔÇÑÉ `no_rates_yet` (`edz-badge--warning`) áßá ÔÑßÉ ÈáÇ Ãí Õİ DeliveryRate İí sidebar ÕİÍÉ ÇáÃÓÚÇÑ ÇáãÚáäÉ.
- ÊÑÔíÍ ÇáãæÑÏíä: `allProviders` íİáÊÑ `is_active=true` ÈäíæíğÇ ÃÕáğÇ — áÇ ÊÚÏíá ãØáæÈ (ãæËøŞ İŞØ).

### ÇáãÏì Çáßáí
| | ÇáÎØ ÇáÃÓÇÓ (ŞÈá ÇáÚäŞæÏ) | ÇáÂä | ÇáÏáÊÇ |
|---|---|---|---|
| ÇÎÊÈÇÑÇÊ | 497 | **513** | +16 (ÈäæÏ Ã–Ò) |
| ÊÃßíÏÇÊ | 1770 | **1810** | +40 |
| ÇäÍÏÇÑ | 0 | **0** (ÌæáÉ ßÇãáÉ äÙíİÉ) | — |

> **ãáÇÍÙÉ ÊäİíĞíÉ:** İÔá `OrderOfficeSelectionTest` ÇáÚÇÈÑ «rename Access is denied on storage/framework/views» åæ Şİá æíäÏæÒ ãÚÑæİ ÃËäÇÁ compile blade — íõÚÇÏ ÇáÊÔÛíá İŞØ (ÇÌÊÇÒ 18/19 + Êöãø İí ÇáÚÒá 8/8). ÓÈÈ İÔá ÇÎÊÈÇÑ ÇáÚÒá İí `SyncStopdeskOfficesJobTest` ÇáÓÇÈŞ: ßÇä ŞÏ ÊõÑß ÇáÇÓÊÌÇÈÉ ÇáæåãíÉ İí Çáãáİ ÈãÏÎá æÇÍÏ İŞØ ÃËäÇÁ ÌáÓÉ ÊÕÍíÍ — ÇÓÊõÚíÏ ÇáãÏÎáÇä. æÓÈŞğÇ ßÇä `OrderInlineFieldEditTest` (513) «badge jumps to carrier editor áØáÈ confirmed ÈáÇ ÔÑßÉ» íİÔá áÃä ãİÊÇÍ `carrier_not_configured` ÇáÌÏíÏ (Ãæá ÃŞİÇá ÇáÅÑÓÇá Úáì ãÓÊæì ÇáãÊÌÑ) áã íßä ãÚíøäğÇ İí `startMissingFieldEdit` — ÃõÖíİ ÊÚííäå Åáì `startOrderProviderEdit` (áÇ İÊÍ ÇáãæÏÇá) — Ëã ÇÌÊÇÒ ÇáÓæíÊ ßÇãáÉ.
>
> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ãİÊÇÍ ÇáÔÑßÉ ÇáÔÇãá İí ÕİÍÉ ÇáÃÓÚÇÑ ÇáãÚáäÉ (enabled/mixed/disabled)¡ ÔÇÑÉ «áÇ ÃÓÚÇÑ ÈÚÏ»¡ ÎíÇÑÇÊ ãßÇÊÈ ÇáÅÑÓÇá ÈáÇ äŞÇØ ÎÇÑÌ ÇáæáÇíÉ/ÇáÈáÏíÉ¡ ÊæÓÊ ãáÇÍÙÉ ÇáÓÚÑ ÚäÏ ÅÑÓÇá ØáÈíÉ ÈáÇ ÓÚÑ ãÚáä¡ æÒÑ ãÒÇãäÉ äŞÇØ ÇáÇÓÊáÇã ÈÚÏ ÍİÙ ÔÑßÉ carrier.

---

## ÅÚÇÏÉ ÊÕãíã ÕİÍÉ äŞÇØ ÇáÇÓÊáÇã `/merchant/delivery/stopdesk` (ÓÈÊãÈÑ 2026) ?

**ÎØÉ æÇİŞ ÚáíåÇ ÇáãÓÊÎÏã ÕÑÇÍÉğ («ãæÇİŞÉ — ääİĞ ÇáÎØÉ ßÇãáÉ») — äõİøĞÊ ßÇãáÉ ÈÃÏáÉ ŞÇÈáÉ ááŞíÇÓ.**

### ÈäíÉ ÌÏíÏÉ (äãØ `announced-rates.blade.php`)
- ÔÑíØ ÌÇäÈí 260px ááÔÑßÇÊ + ÔÈßÉ æáÇíÇÊ ÈÚÏÏ ÇáãßÇÊÈ (`grid lg:grid-cols-[260px_1fr]`)º Úáì ÇáÌæÇá ÇáÔÑíØ íÊßÏÓ İæŞ ÇááæÍÉ ãÚ ÊãÑíÑ ÃİŞí.
- `stopdesk.blade.php` ÃÚíÏÊ ßÊÇÈÊå ßãáİ ÊÑßíÈ ÑİíÚ (äİÓ ÇÓã Çáãßæøä `merchant.delivery.stopdesk`) íÓÊÏÚí 3 partials:
  - `partials/stopdesk-provider-sidebar.blade.php` (ÃÒÑÇÑ ÇáÔÑßÇÊ + carrier subtitle + ÔÇÑÉ synced ÇÎÊíÇÑíÉ + badge count).
  - `partials/stopdesk-state-grid.blade.php` — Õİæİ æáÇíÇÊ (State/Offices/Actions)º ÊõÚÑÖ İŞØ ÇáæáÇíÇÊ ÇáÊí áÏíåÇ ãßÇÊÈ.
  - `partials/stopdesk-offices-popup.blade.php` — äÇİĞÉ xl ÈŞÇÆãÉ ãßÇÊÈ ÇáæáÇíÉ ãÚ ÔÇÑÉ «Syncdesk synced» áßá ãßÊÈ (ÚÈÑ `external_code`) + ÊÚÏíá/ÍĞİ + ÒÑ ÅÖÇİÉ ãÓÈŞ ÇáÊÚÈÆÉ.
- **ÒÑ ÇáãÒÇãäÉ:** ÏÇÎá áæÍÉ ÇáÔÑßÉ İŞØ ÅĞÇ æõÌÏ `config("delivery.carrier_integrations.{code}")` + `class_exists` (äãØ `hasIntegrationAdapter` İí `providers.blade.php`) — ÈáÇ ÚãæÏ ŞÏÑÉ ÌÏíÏ. **ÒÑ «ÅÖÇİÉ ãßÊÈ» ÇáíÏæí:** ÏÇÎá áæÍÉ ÇáÔÑßÉ ÇáãÎÊÇÑÉ İŞØ ãÚ ÊÚÈÆÉ `shipping_provider_id` ãÓÈŞğÇ.
- **æáÇíÉ ÈáÇ ãßÇÊÈ ãõÒÇãóäÉ áÇ ÊÙåÑ İí ÇáÔÈßÉ** áÔÑßÉ ĞÇÊ ÊßÇãá API ÍÊì ÊõÒÇãä (ŞÑÇÑ ÇáãÓÊÎÏã Ã) — ÇáãÒÇãäÉ ÇáÚÇãÉ İí ÑÃÓ áæÍÉ ÇáÔÑßÉ.

### ÊÍÓíä ãõßÊÔóİ ÃËäÇÁ ÇáÊäİíĞ (ÑÈØ ÇáæáÇíÉ/ÇáÈáÏíÉ ÊáŞÇÆíğÇ)
- `StopdeskOfficeSync::sync($provider)` ÈáÇ İáÊÑ æáÇíÉ ßÇä íõÈŞí `state_id=null` áßá ÇáãßÇÊÈ ? áÇ íãßä ÚÑÖåÇ İí ÔÈßÉ ÇáæáÇíÇÊ.
- `stateByDeskCode()` ÌÏíÏ: ßæÏ ãßÊÈ NOEST åæ ÑŞã ÇáæáÇíÉ (ãËá '16' Ãæ '34B') æ`state_code` ãÎÒä `char(2)` ÈÕİÑ ÈÇÏÆ ? ãØÇÈŞÉ ÚÈÑ `ltrim(state_code,'0') = (string)(int)$deskCode` Ëã `resolveCityId(commune, officeState)` — İíõŞÓã ÚÏÇÏ ßá æáÇíÉ ÈÚÏ ãÒÇãäÉ ßÇãáÉ.
- `DeliverySettingsTest` (14/14) íÚÊãÏ `openStopdeskModal`/`saveStopdesk`/`deleteStopdesk`/`assertSee(tab_stopdesk)` — ÇáãÍİæÙ ÈÇáßÇãá. `SyncNoestTrackingJob` íÓÊÎÏã `resolve()` İŞØ (áÇ `sync()`) — ÛíÑ ãÊÃËÑ.
- ÊæÇİŞ ÇáÇÎÊÈÇÑÇÊ: ÃõÈŞí `syncCandidates`/`selectedSyncProviderId`/`syncStopdesk`/ãİÊÇÍ `synced`º ÍõĞİ `stopdeskPoints` ÇáãÓØøÍ áÕÇáÍ `pointsByState` (groupBy `__unassigned__`) æ`stateRows`º `openStopdeskModal(?string $stopdeskId = null, ?string $defaultStateId = null)` íÛáŞ popup ÇáãßÇÊÈ ÚäÏ İÊÍ ÇáãæÏÇá (ãäÚ ÊßÏÓ ÇáØÈŞÇÊ).
- Fixtures: `sdEnv()` áÇ ÊÒÑÚ ÇáæáÇíÇÊ (ÇáãßÇÊÈ ÇáãõÒÇãóäÉ ÊĞåÈ áÜ`__unassigned__`)º `noestGeography()` ÊäÔÆ State16 ÈÇáßæÏ '16'.
- ÊÑÌãÇÊ ×4 (ÈÚÏ `sync_no_adapter`): `no_providers_yet`/`select_company_hint_offices`/`stopdesk_provider_desc`/`stopdesk_offices`/`stopdesk_manage_offices`/`stopdesk_unassigned`/`stopdesk_no_offices`/`stopdesk_no_offices_desc`.

### ÇáÃÏáÉ
- `tests/Feature/Merchant/StopdeskSyncUiTest.php` ÃõÚíÏÊ ßÊÇÈÊå (4/4): sidebar íÚÏøÏ ßá ÇáÔÑßÇÊ ÈíäãÇ sync íÓÊåÏİ carrier-backed İŞØº ÔÇÑÉ synced ÏÇÎá popup ÚÈÑ `openOfficesPopup`º upsert ÈÚÏ sync ãÚ İÍÕ `pointsByState.__unassigned__`º info ÈáÇ credentials.
- `tests/Feature/Merchant/NoestIntegrationTest.php` + ÇÎÊÈÇÑ «full sync without a state filter assigns the wilaya and commune from the desk code» (state_id=state16 æ city_id=city16 ÚÈÑ ÇáßæÏ '16').
- Lint äÙíİ: `StopdeskOfficeSync.php` + ãáİÇÊ ÇááÛÉ ÇáÃÑÈÚÉ + ÇáÇÎÊÈÇÑÇÊ ÇáËáÇËÉ.

### ÇáãÏì Çáßáí
| | ÇáÎØ ÇáÃÓÇÓ (ŞÈá ÇáÚäŞæÏ) | ÇáÂä | ÇáÏáÊÇ |
|---|---|---|---|
| ÇÎÊÈÇÑÇÊ | 513 | **517** | +4 (ÅÚÇÏÉ ÊÕãíã stopdesk) |
| ÊÃßíÏÇÊ | 1810 | **1825** | +15 |
| ÇäÍÏÇÑ | 0 | **0** (ÌæáÉ ßÇãáÉ 517/517 äÙíİÉ) | — |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ÊßÏÓ ÇáÔÑíØ ÇáÌÇäÈí İæŞ ÇááæÍÉ¡ ÔÈßÉ ÇáæáÇíÇÊ æÚÏÇÏÇÊåÇ ÈÚÏ ãÒÇãäÉ ßÇãáÉ¡ äÇİĞÉ ãßÇÊÈ ÇáæáÇíÉ + ÇáãæÏÇá İæŞåÇ (ŞÑÇÑ Ã)¡ æÒÑ ÇáãÒÇãäÉ/ÇáÅÖÇİÉ ÏÇÎá áæÍÉ ÇáÔÑßÉ İŞØ.

---

## ÅÕáÇÍÇÊ ÍÑÌÉ `/delivery/stopdesk` (ÓÈÊãÈÑ 2026) ?

**ÎØÉ æÇİŞ ÚáíåÇ ÇáãÓÊÎÏã ÕÑÇÍÉğ («ÃæÇİŞ — äİĞ ÇáÎØÉ» + 3 ŞÑÇÑÇÊ: ãßÇÊÈ ÇáæáÇíÉ Öãä ßá ÈáÏíÉ / ÅÕáÇÍ İŞÏÇä ÇáäÇİĞÉ / providers ÈáÇ İÑÚ) — äõİøĞÊ P1?P2?P3 ÈÃÏáÉ ŞÇÈáÉ ááŞíÇÓ.**

### P1 — ãßÇÊÈ ÇáæáÇíÉ Öãä ßá ÈáÏíÉ (ÇáãÑÇİÆ ÇáËáÇËÉ + ãÍÑÑ inline)
- **ÇáÔÑØ:** ãßÊÈ ÈáÇ ÈáÏíÉ (`city_id IS NULL`) Öãä æáÇíÉ ÇáãÎÊÇÑÉ íÙá ŞÇÈáÇğ ááÚÑÖ ÊÍÊ ßá ÈáÏíÉ ÈÇáæáÇíÉ¡ ÈÚÏ Ãä ßÇäÊ `city_id = X` ÇáÕÇÑãÉ ÊõÓŞØå (ÓÈÈ «íÊÚĞÑ ÌáÈ ãßÇÊÈ áßá ÈáÏíÉ»). ãßÊÈ ÈáÏíÉ ÃÎÑì ãä äİÓ ÇáæáÇíÉ íÈŞì ãÓÊÈÚÏğÇ.
- **ÇáãæÇÖÚ:** `rebuildFormOffices` (orders/index) + `inlineStopdeskOptions` (ãÍÑÑ inline ØáÈíÉ) + `storefront/order-form.blade.php`. ÇáÕíÛÉ: `where(fn: city_id = X Ãæ Ãæ city_id IS NULL)` + `orderByRaw('(city_id = ?) DESC, (city_id IS NULL) ASC, name')` — ãØÇÈŞÉ ÇáÈáÏíÉ ÃæáğÇ Ëã ãßÇÊÈ ÇáæáÇíÉ ÃÈÌÏíğÇº ÈÏæä ÈáÏíÉ ? `orderBy('name')` ßÓÇÈŞ.
- **ÇÎÊÈÇÑÇÊ:** ÊÍÏíË ÇÎÊÈÇÑóí «ÇáÍÕÑ ÇáÕÇÑã ÈÇáÈáÏíÉ» (ãæÏÇá + inline) áíõÏÎáÇ ÇáãßÊÈ ÇáÅŞáíãí æíõÈŞíÇ ÇÓÊÈÚÇÏ ÈáÏíÉ ÃÎÑìº ÌÏíÏ ×2 — «ÇáãÑÊÈÉ ÇáÇİÊÑÇÖíÉ ááÈáÏíÉ ŞÈá ãßÇÊÈ ÇáæáÇíÉ» (äãæĞÌ ÇáÅäÔÇÁ) æ«ŞÇÆãÉ ãßÇÊÈ ÇáÒÇÆÑ ÊÏãÌ ÈáÏíÉ + æáÇíÉ ÈáÇ ÚÑÖ ÈáÏíÉ ÃÎÑì» (CartOrderLimitsTest).

### P2 — áæÍÉ stopdesk: ÊÌãíÚ ÇáÈáÏíÉ + ÇáÈŞÇÁ ÏÇÎá ÇáäÇİĞÉ ?
- **ÇáÈŞÇÁ ÏÇÎá ÇáäÇİĞÉ:** `openStopdeskModal` áã íÚÏ íÛáŞ popup ÇáãßÇÊÈ (`stopdesk.blade.php`) — ÇáãæÏÇá íõÚÑÖ İæŞ ÇáäÇİĞÉ (íÔÊÑßÇä İí `--edz-z-modal` æ`modal` ãõÓÌóøá ÈÚÏ popup İí ÇáÜ DOM)º ßÊáÉ `saveStopdesk`/`deleteStopdesk` (ÇáÊí ßÇäÊ ãíÊÉ) ÃÕÈÍÊ ÍíÉ æÊÈŞí ÇáãÓÊÎÏã ÏÇÎá ŞÇÆãÉ ÇáæáÇíÉ ÈÇáŞÇÆãÉ ÇáãÍÏóøËÉ İæÑğÇ.
- **ÊÌãíÚ ÇáÈáÏíÉ:** äÇİĞÉ ãßÇÊÈ ÇáæáÇíÉ ÊõÌãóøÚ ÇáÂä **ÈÇáÈáÏíÉ** (ÇÓã ÇáÈáÏíÉ + ÚÏøÇÏ) ãÚ ãÌãæÚÉ ÃÎíÑÉ «ãßÇÊÈ ÇáæáÇíÉ» (ÈÏæä ÈáÏíÉ) ÈÔÇÑÉ `stopdesk_state_wide_hint` — íØÇÈŞ «ãßÇÊÈ áßá ÈáÏíÉ» Úáì ÕİÍÉ ÇáÅÏÇÑÉ.
- **ÊÑÌãÇÊ ×4:** `stopdesk_state_wide` + `stopdesk_state_wide_hint`.
- **ÃÏáÉ:** StopdeskSyncUiTest +2 («ÇáÊÚÏíá ãä ÇáäÇİĞÉ íÈŞíåÇ ãİÊæÍÉ æíÍÏøË ŞÇÆãÊåÇ» + «ÇáäÇİĞÉ ÊÌãøÚ ÈÇáÈáÏíÉ æÊÚÑÖ ãÌãæÚÉ ÇáæáÇíÉ»).

### P3 — providers: ÅÎİÇÁ «İÑÚ ÔÑßÉ ÇáÊæÕíá» ?
- **ÔÑßÉ ÈİÑÚ æÇÍÏ** (ZR Express): `selectProviderPlatform` íÎÊÇÑ ÇáİÑÚ ÇáæÍíÏ ÊáŞÇÆíğÇ æíÚÈöøÆ ÇáÇÓã æÇáÇÚÊãÇÏÇÊº ŞÇÆãÉ «İÑÚ ÔÑßÉ ÇáÊæÕíá» ÊõÓÊÈÏá ÈÍŞá ãŞÑæÁ + ÊáãíÍ `delivery_company_single_branch` (ÇáİÑÚ ãõÎÊÇÑ ÖãäíğÇ).
- **ÔÑßÉ ÈáÇ İÑæÚ**: ÈÏá ÇáŞÇÆãÉ ÊáãíÍ `delivery_company_no_branches` (ÏİÇÚí — áÇ ÔÑßÉ ãä åĞÇ ÇáŞÈíá ÍÇáíğÇ İí ÇáİåÑÓ).
- **ÔÑßÉ ÈİÑæÚ ãÊÚÏÏÉ** (Ecotrack ×3): ÊÈŞì ŞÇÆãÉ ÇáİÑæÚ ßãÇ åí Ïæä Ãí ÇÎÊíÇÑ ÊáŞÇÆí.
- **ÇáãÌãæÚÉ ÇáãÓÊŞáÉ `__standalone__`**: ÎíÇÑ «İÑÚ ÔÑßÉ ÇáÊæÕíá» íõÚÇÏ ÊÓãíÊå «ÔÑßÉ ÇáÊæÕíá» (ÇÎÊíÇÑ ÏİÇÚí — áÇ äÇŞá ãÓÊŞá İí ÇáİåÑÓ ÇáÍÇáí).
- **ÊÑÌãÇÊ:** ÃõÖíİ `delivery_company_single_branch` + `delivery_company_no_branches` ÈÇááÛÇÊ ÇáÃÑÈÚº æÓõÏøÊ ÇáİÌæÉ ÇáŞÇÆãÉ (ßÇäÊ `delivery_company*`/`select_delivery_company*` ãÚÑóøİÉ ÈÇáÚÑÈíÉ İŞØ¡ İßÇäÊ en/fr/es ÊÚÑÖ ãİÊÇÍğÇ ÎÇãğÇ — ÃõÖíİÊ áßá ÇáãáİÇÊ).
- **ÃÏáÉ:** DeliverySettingsTest +2 (ÇáİÑÚ ÇáæÍíÏ íõÎÊÇÑ ÊáŞÇÆíğÇ æÊõÎİì ÇáŞÇÆãÉº ÇáİÑæÚ ÇáãÊÚÏÏÉ ÊõÈŞí ÇáŞÇÆãÉ ÈáÇ ÇÎÊíÇÑ) + ÅÚÇÏÉ ßÊÇÈÉ ÇÎÊÈÇÑ «two-level» Úáì Ecotrack ÈÚÏ ÊÚØõøá ãÓÇÑ ZR ÇáÊáŞÇÆí.

### ÇáãÏì Çáßáí ÇáßÇãá (P1+P2+P3)
| | ÇáÎØ ÇáÃÓÇÓ | ÇáÂä | ÇáÏáÊÇ |
|---|---|---|---|
| ÇÎÊÈÇÑÇÊ | 517 | **523** | +6 |
| ÊÃßíÏÇÊ | 1825 | **1850** | +25 |
- ÇáÊİÕíá: P1 (+2/+10 ? 519/1835)º P2 (+2 — StopdeskSyncUiTest 4?6)º P3 (+2 — DeliverySettingsTest 14?16 + ÅÚÇÏÉ ßÊÇÈÉ 1). ÇáÊÍŞŞ ãä P2+P3: ÇáÓæíÊ **523 äÇÌÍ (1850 assertions)** — ÕİÑ ÇäÍÏÇÑº Pint PASS Úáì DeliverySettingsTestº `php -l` Óáíã Úáì ßá äÕ ÊÚÏóøáº `view:cache`+`view:clear` ÓáíãÇä.

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — «ãßÇÊÈ ÇáæáÇíÉ» ÊÙåÑ ÊÍÊ ßá ÈáÏíÉ İí äãæĞÌ ÇáØáÈ æÇáãÊÌÑ¡ æÊáæíä ÍŞæá ÇáãßÇÊÈ İí ÇáãÍÑÑ ÇáÓÑíÚ/inline ÈÚÏ ÇáÊæÓÚÉ.

---

## Phase 34 — äãæĞÌ ØáÈ ÇáãÊÌÑ: ÓáÓáÉ ÇáÔÍä (ÇáÔÑßÉ ? äæÚ ÇáÊæÕíá ? ÇáæáÇíÉ ? ÇáÈáÏíÉ ? ÇáãßÊÈ) ÈãäÓÏáÉ ÈÍËíÉ (ÓÈÊãÈÑ 2026) ?

**ÎØÉ æÇİŞ ÚáíåÇ ÇáãÓÊÎÏã ÕÑÇÍÉğ ŞÈá ÇáÊäİíĞ ÈŞÑÇÑÇÊ ãËÈøÊÉ ÈÃÓÆáÉ (ÔÑßÉ ãİŞæÏÉ ? ÊáãíÍ ÈÏá ÅÚÇÏÉ ÊÍÏíÏ / ãäÊÏì ÇáãäÒá = commue ÚÈÑ `DeliveryRate` ãÚ ÊÑÇÌÚ áŞÇÆãÉ ÇáÃÓÚÇÑ ÇáÔÇãáÉ / ãÊÌÑ ÈáÇ ÔÑßÇÊ ? ÇáÊÓáÓá ÇáßÇãá ÈÜ legacy / ãßÊÈ ÈáÇ ÓÚÑ ãäÔæÑ ? ãÌÇäí). ÇáÚäæÇä: «desk — commune».**

- **ãßæøä ÊÍÏíÏ ÌÏíÏ ÎÇÕ ÈÇáãÊÌÑ** `resources/views/components/storefront/select.blade.php` + `resources/js/components/storefront-select.js` (ãŞÇæóã Îİíİ ãÔÊÑß ãÚ ŞÇÚÏÉ ÇáãÊÌÑ `store-primary` — **áíÓ** `x-edz.select` ÇáÊÌÇÑí)¡ ãÓÌøá İí `resources/js/storefront.js` (ÍÇÑÓ `window.Alpine` ÛíÑå `alpine:init`). íÑİÚ ŞÇÆãÉ ÇáÎíÇÑÇÊ JSON æíÈËø ÇÎÊíÇÑå ÚÈÑ ÍŞá ãÎİí `x-model` + ÃÍÏÇË `change`/`livewire-change` Úáì ÇáÌĞÑ.
- **ÅÚÇÏÉ ßÊÇÈÉ `resources/views/livewire/storefront/order-form.blade.php`** — ãäØŞ ÇáÓáÓáÉ ßáå (ÇáÔÑßÉ ? äæÚ ÇáÊæÕíá ? ÇáæáÇíÉ ? ÇáÈáÏíÉ ? ÇáãßÊÈ) íõÈäì Úáì ÇáØíİ ÇáãõáÇÍÙ ááãÊÌÑ ÇáÍÇáí ãÚ **ÈÏæä ÇÓÊÚáÇãÇÊ ÒÇÆÏÉ ÎáÇá mounting/render**:
  - `mount()` íÍÏÏ ÇáÔÑßÉ ÊáŞÇÆíğÇ ÚäÏ `availableProviders->count() === 1`; `updated(['city_id'])` íÍÏÏ ÇáãßÊÈ ÇáæÍíÏ ÊáŞÇÆíğÇ (ÇáãÕÏÑ ÇáŞÇØÚ — ÃõÒíá ÇáØİÑÉ İí `@php`).
  - `submitOrder` íÊÍŞŞ ãä äØÇŞ ÇáãßÊÈ/ÇáÔÑßÉ (ÊÃßíÏ **ÎÇÏãí** íÑİÖ ãßÊÈğÇ ÎÇÑÌ ÇáÔÑßÉ/ÇáæáÇíÉ/ÇáÈáÏíÉ ÇáãÎÊÇÑÉ) + `Rule::exists` ãÍÕæÑ ÈÔÑßÉ ÇáØáÈ¡ æ`stopdesk` ÈÏæä ãßÊÈ ? `edz-notice` + ÚæÏÉ ãÈßÑÉ. ÓÚÑ ÇáãßÊÈ: `office_cost` ÚäÏ ÊæÇİÑå ãÚ ÊÑÇÌÚ İæÑí ÇáãÊÌÑ¡ æÅáÇ ãÌÇäí (ŞÑÇÑ ÇáãÓÊÎÏã).
  - ÓØÑ ãáÎÕ ÇáÔÍä íÚÑÖ **ÇÓã ÇáÔÑßÉ** ÚäÏ ÊæÇİÑå (`provider_name`)º ÅÕáÇÍ `$providers->firstWhere(...)?->flat_rate` æÍÑÇÓÉ `$variants` ÇáİÇÑÛÉ.
- **ÊÑÌãÇÊ ×4 (en/ar/fr/es) — 10 ãİÇÊíÍ ÌÏíÏÉ İŞØ:** `shipping_via`/`company`/`select_company`/`select_company_first`/`search_company`/`search`/`no_options_found`/`delivery_office`/`deliver_to_this_office`/`select_office_required` (ãßÇäíøÇ ÇáÈÍË ááæáÇíÉ/ÇáÈáÏíÉ/ÇáãßÊÈ ÊÓÊÚãá `storefront.search` ÇáãÔÊÑß — áã ÊõÖİ `search_state/city/office`).
- **ÇÎÊÈÇÑÇÊ — `tests/Feature/Storefront/StorefrontOrderShippingCascadeTest.php` (8 äÇÌÍÉ / 43 assertions):** ãÊÌÑ ÔÑßÉ æÇÍÏÉ íÎİí ÇáÈíßÑ æíäÔÑ ÇáÔÑßÉ Úáì ÇáØáÈ¡ ÇáãØÇÈŞÉ ÇáæÍíÏÉ ÊÎÊÇÑ ÇáãßÊÈ ÊáŞÇÆíğÇ ßÈØÇŞÉ¡ ÇÎÊíÇÑ ÇáÔÑßÉ íÍÏÏ äØÇŞ æáÇíÇÊ ÇáãßÊÈ¡ ÈáÏíÇÊ ÇáäØÇŞ İŞØ ÊõÚÑÖ¡ stopdesk ÈáÇ ÓÚÑ ãäÔæÑ ãÌÇäí¡ ãäÒáí íİÑÖ `home_cost` æíÍİÙ ÇáÚäæÇä ÇáÎÇã¡ ãßÊÈ ÎÇÑÌ ÇáÔÑßÉ ÇáãÎÊÇÑÉ íõÑİÖ ÈáÇ ØáÈ¡ æãÊÌÑ legacy ÈáÇ ÔÑßÇÊ íÍÇİÙ Úáì ÇáÓáÓáÉ ßÇãáÉ.
- **ÊÍÏíË ÇÎÊÈÇÑíä ŞÇÆãíä:** `CartOrderLimitsTest` ? «desk choice is required and the list is scoped to the commune with carrier labels» (ãßÊÈ ÅáÒÇãí `edz-notice` æÈáÇ ØáÈ)º `OrderCancellationRestockTest::placeStopdeskOrder()` íäÔÆ `StopdeskPoint` æíÍÏÏ `selectedStopdesk`.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `php -l` + `php artisan view:cache` (ÓáíãÉ) + `npm run build` (vite 7.3.0 — ÊÍĞíÑÇÊ Sass İŞØ¡ áÇ ÃÎØÇÁ) + **ÇáÌæáÉ ÇáßÇãáÉ ÇáäÙíİÉ: 536 äÇÌÍ (1899 assertions)** — ŞÈáåÇ 523/1850 (+8 ÇÎÊÈÇÑ / +25 ÊÃßíÏ ááÓáÓáÉ + ÊæÓÚÉ) — **ÕİÑ ÇäÍÏÇÑ**. ãáÇÍÙÉ manual: ØİÑÉ `updated` áÇ ÊÙåÑ İí `assertSet` ÈäÓÎÉ Livewire — ÇáÇÎÊÈÇÑÇÊ ÊÖÈØ `selectedStopdesk` ÕÑÇÍÉğ ŞÈá `submitOrder` (ÇáÊÃßíÏÇÊ Úáì HTML ÊËÈÊ Ãä ÇáÈØÇŞÉ ÇáãÎÊÇÑÉ ÊõÑÓã İÚáíğÇ).

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 34 ÓáÓáÉ ÔÍä ÇáãÊÌÑ | ? | storefront/order-form.blade.php + components/storefront/select.blade.php (ÌÏíÏ) + resources/js/components/storefront-select.js + resources/js/storefront.js + storefront.php ×4 + StorefrontOrderShippingCascadeTest (ÌÏíÏ) + CartOrderLimitsTest + OrderCancellationRestockTest | 8 İí StorefrontOrderShippingCascadeTest (43) + 536 äÇÌÍ (1899) |
| **ÇáÅÌãÇáí (ÌæáÉ äÙíİÉ)** | **536 äÇÌÍ (1899 assertions)** | | |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ÇáãäÓÏáÇÊ ÇáÈÍËíÉ ááÔÑßÉ/ÇáæáÇíÉ/ÇáÈáÏíÉ/ÇáãßÊÈ (ÎİíİÉ¡ ÈÍÏæÏ `store-primary`¡ æãæÈÇíá æÑŞÉ ÓİáíÉ)¡ ÇáÊÍÏíÏ ÇáÊáŞÇÆí ááãßÊÈ ÇáæÍíÏ ßÈØÇŞÉ¡ ÓØÑ «ÚÈÑ ÔÑßÉ» İí ãáÎÕ ÇáÔÍä¡ ÇÎÊíÇÑ ãßÊÈ ÎÇÑÌ ÇáÔÑßÉ íõÑİÖ ÈÊæÓÊ¡ æÈíæÊ ÇáãÊÌÑ ÇáÍÇáí (`store.edzeery.test`/`store.noest.test`).

---

## Phase 34 — ÅÕáÇÍ ãÇ ÈÚÏ ÇáÊÍŞŞ: ÚäÇæíä ÇáŞæÇÆã «[object Object]» İí ÕİÍÉ checkout + ÊÑãíÒ ãÖÇÚİ áÜ data-options (ÓÈÊãÈÑ 2026) ?

**ÃÈáÛ ÇáãÓÊÎÏã Ãä İŞÑÉ ÇáÔÑßÉ æÇáæáÇíÇÊ İí ÕİÍÉ checkout ÊõÑÓã ÚäÇæíäåÇ «[object Object]». ÇáÓÈÈ ÇáÌĞÑí (ÊÍŞíŞ ãÚãŞ):**

- **ÇáÓÈÈ (1) — ÇáÎÇÕíÉ ÇáÌĞÑíÉ:** `components/storefront/select.blade.php` ßÇä íÍÓÈ ÇáÎíÇÑÇÊ ÚÈÑ `is_array($item)` İŞØº áßä `order-form.blade.php` íãÑÑ **ãÌãæÚÇÊ Eloquent** (`$providers`/`$states`/`$cities` = äãÇĞÌ) ãÈÇÔÑÉ Åáì `:options="$providers"` ÅáÎ¡ İáã íØÇÈŞåÇ ÇáÔÑØ æĞåÈ ßá äãæĞÌ ááİÑÚ ÇáÎÇØÆ: `label = ÇáäãæĞÌ ßÇãáğÇ` ? `x-text` íÑÓøã ÇáßÇÆä «[object Object]» (ßÇäÊ ÇáÇÎÊÈÇÑÇÊ ÇáÓÇÈŞÉ ÊãÑ áÃä `@js` íÊÖãä JSON ÇáäãæĞÌ İÊÙåÑ ÇáÃÓãÇÁ ÎÇãğÇ İí HTML ÑÛã İÓÇÏ ÇáÚÑÖ). **ÇáÅÕáÇÍ İí Çáãßæøä:** ÇáÊØÈíÚ ÚÈÑ `is_array($item) || is_object($item)` + ŞÑÇÁÉ ÇáŞíãÉ/ÇáÚäæÇä/ÇáÊáãíÍ ÈÜ`data_get` (íÚãá Úáì ÇáäãÇĞÌ æ stdClass æÇáãÕİæİÇÊ) ãÚ ÊäÕíÑ ŞÓÑí `(string)` æfallbacks ÂãäÉ (`$key`).
- **ÇáÓÈÈ (2) — ÊÑãíÒ ãÖÇÚİ áÜ `data-options`:** ßÇä ÇáÓØÑ `data-options="{{ $optionsAttr }}"` ÍíË `$optionsAttr` ãõÑãóøÒ ÃÕáğÇ ÈÜ`htmlspecialchars(ENT_QUOTES)` — İßÇä `{{ }}` (ÇáĞí íØÈøŞ `e()` ãÑÉ ËÇäíÉ) íÍæáå `&quot;` ? `&amp;quot;`¡ İßÇä `JSON.parse` áİÔá ŞÑÇÁÉ ÓãÉ DOM (ßÇäÊ ÊÚÊãÏ İÚáíğÇ Úáì `@js` ÏÇÎá `x-data` æÍÏå). **ÇáÅÕáÇÍ:** `data-options="{!! $optionsAttr !!}"` (ÇáÊÑãíÒ ÇáÂãä ãÑÉ æÇÍÏÉ İŞØ) İÊŞÑÃ `_syncFromServer` JSON ÕÇáÍğÇ æÊÊÒÇãä ãÚ ÅÚÇÏÉ ÇáÊæáíÏ.
- **ÇÎÊÈÇÑ ÇáÍÇÌÒ — `StorefrontOrderShippingCascadeTest` (+1/+5):** «carrier and wilaya option labels render as plain strings, never "[object Object]"» — íËÈÊ Ãä ÇáÍãæáÉ ÊÍãá `&quot;label&quot;:&quot;…&quot;` ááÔÑßÇÊ æÇáæáÇíÇÊ (ÈÚÏ ÅÕáÇÍ ÇáÊÑãíÒ) æáÇ ÊÍãá `&quot;label&quot;:{` æáÇ `[object Object]` (ÇáÇÎÊÈÇÑ İÍÕ ÚÈÑ `data-options` ÇáãÑãóøÒÉ áÃä `@js` íåÑÈ ÚáÇãÇÊ ÇáÇŞÊÈÇÓ ÈÜ`\u0022` — ÌÑøÈ ÃæáğÇ ÇáÕíÛÊíä Ëã ËÈÊ ÇáÕÍíÍ).
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `view:clear`+`view:cache` ÓáíãÉ + ÇáÌæáÉ ÇáßÇãáÉ ÇáäÙíİÉ **537 äÇÌÍ (1904 assertions)** — ŞÈáåÇ 536/1899 (+1 ÇÎÊÈÇÑ +5 ÊÃßíÏÇÊ) — ÕİÑ ÇäÍÏÇÑ.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 34 ÅÕáÇÍ ÚäÇæíä ÇáŞæÇÆã | ? | resources/views/components/storefront/select.blade.php + tests/Feature/Storefront/StorefrontOrderShippingCascadeTest.php (+1) | 9 İí StorefrontOrderShippingCascadeTest (48) + 537 äÇÌÍ (1904) |
| **ÇáÅÌãÇáí (ÌæáÉ äÙíİÉ)** | **537 äÇÌÍ (1904 assertions)** | | |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ÈÚÏ åĞÇ ÇáÅÕáÇÍ ÊõÚÑÖ ÃÓãÇÁ ÇáÔÑßÇÊ æÇáæáÇíÇÊ ÍÇáíğÇ İí ÇáŞæÇÆã¡ æãßÊÈ-checkout íõÌÑøÈ ÇáÂä Úáì ÇáãÊÌÑ ÇáãÈÇÔÑ.

---

## Phase 35 — ÊÑŞíã ÇáæáÇíÇÊ ÚÈÑ ÇáãÔÑæÚ + ãßÇÊÈ ÇáÇÓÊáÇã ÈÇáãÊÌÑ ÊÔãá ÇáæáÇíÉ ßÇãáÉ (ÓÈÊãÈÑ 2026) ?

**ØáÈ ÇáãÓÊÎÏã:** (Ã) ŞæÇÆã ßá ÇáæáÇíÇÊ İí ÇáãÔÑæÚ ÊõÚÑÖ ÇáÑŞã ŞÈá ÇáÇÓã ßÔÇÑÉ («01 Adrar»)¡ ŞÇÈáÉ ááÈÍË ÈÇáÑŞã¡ æãÑÊÈÉ ÈÑŞã ÇáæáÇíÉº (È) İí checkout ÇáãÊÌÑ ÊõÚÑÖ **ßá ãßÇÊÈ ÇáæáÇíÉ ÇáãÎÊÇÑÉ ÚÈÑ ßá ÇáÈáÏíÇÊ** (áÇ ÊŞÊÕÑ Úáì ÇáÈáÏíÉ)¡ æÊõÎİì ÈáÏíÉ ÇáÇÓÊáÇã (ÇáßæÏ = ÇáÈáÏíÉ ÊõÔÊŞ ãä ÇáãßÊÈ)¡ æŞÇÆãÉ ÇáãßÇÊÈ ÊõÙåÑ ÇáÊİÇÕíá (ÇáÔÇÑÉ + ÇáÇÓã + ÇáÈáÏíÉ + ÇáÚäæÇä + ÇáåÇÊİ) æãÑÊÈÉ ÈßæÏ ÇáãßÊÈº (Ì) ÌÇäÈ ÇáÊÇÌÑ (ÇáãŞÑÇÊ inline/form) íÚÑÖ ßæÏ ÇáãßÊÈ ÃíÖğÇ. **ÇáŞÑÇÑÇÊ:** ßÇãá ÇáÎØÉ + ÅÎİÇÁ ÇáÈáÏíÉ ááÇÓÊáÇã + ÚÑÖ ÇáãÎÒæä ÇáãÊæİÑ İŞØ (ÈÏæä ÊÛííÑ schema). **Óáæß ÇáÊÇÌÑ ÈŞí äØÇŞå ÈÇáÈáÏíÉ ßãÇ åæ** — ÊÛíøÑ ÇáãÊÌÑ İŞØ.

- **Ã. ÊÑŞíã ÇáæáÇíÇÊ:** `app/Models/Locations/State.php` ÃõÖíİ `scopeOrderedByCode()` (ÊÑÊíÈ ãÈÇÔÑ ÈÜ `state_code` — `state_code` äÕ char(2) ãÈØä ÈÇáÃÕİÇÑ İÇáÊÑÊíÈ ÇáäÕí = ÇáÑŞãí¡ ÈáÇ Ãí ÏæÇá DB ÎÇÕÉ). ÇáãßæøäÇä `components/storefront/select.blade.php` + `components/edz/select.blade.php` ÇßÊÓÈÇ props `option-code`/`option-extra` (ÔÇÑÇÊ ßæÏ İí ÇáãÔÛøá æÇáÎíÇÑ¡ ÓØæÑ extra ááÊİÇÕíá) æ JS `currentCode` + ÈÍË íÔãá ÇáßæÏ (`storefront-select.js`/`edz-select.js`). ÔÇÑÉ ãæÍøÏÉ `.edz-code-badge` İí `_badges.scss`. ØÈøŞ Úáì: `storefront/order-form` (ÇÓäÇã states ÇáÃÑÈÚÉ + `option-code="state_code"`)¡ `merchant/orders/index` (`allStates` + `formAvailableStates` ÈÇáÃßæÇÏ¡ ÇáŞæÇÆã ÇáÃÕáíÉ İáÇÊÑ/ãÍÑÑinline æÇáÜ badge İí ÚÑÖ ÇáİáÊÑ)¡ `orders-table-cell` (ãÍÑÑ ÇáæáÇíÉ ÇáÃÕáí)¡ `order-form-modal` + `delivery-edit-modal` (selects ÇáæáÇíÉ)¡ `teams/index` (`states` ÇáãÍÓæÈÉ ÊÊÖãä `state_code` æ `option-code`)¡ delivery `announced-rates`/`stopdesk` (ÔÇÑÉ ÇáÑŞã İí ÌÏÇæá ÇáÃÓÚÇÑ æÔÈßÉ ÇáãßÇÊÈ + select ÇáæáÇíÉ).
- **È. ÅÚÇÏÉ ÊÕãíã ãßÊÈ ÇáÇÓÊáÇã ÈÇáãÊÌÑ (`storefront/order-form.blade.php`):**
  - `officesForSelection()` — ÃõÓŞØÊ ÈæÇÈÉ `city_id` (ßá ãßÇÊÈ ÇáæáÇíÉ ÊõÚÑÖ) + ÊÑÊíÈ ÑŞãíø ÈÇáßæÏ ÇáÎÇÑÌí (İÇÑÛğÇ ÃÎíÑğÇ¡ ãÚ ãÌãæÚÇÊ NOEST ãËá 02A/02B ãÊÌÇæÑÉ ÚÈÑ `orderBy('external_code')`).
  - `updated(['state_id'])` — íãÓÍ ÇáãßÊÈ İŞØ Åä áã íßä ãßÊÈğÇ áäİÓ ÇáæáÇíÉ¡ æíÎÊÇÑ ÊáŞÇÆíğÇ ÇáãßÊÈ ÇáæÍíÏ ááæáÇíÉ ãÚ ÇÔÊŞÇŞ ÈáÏíÊåÇ. `updated(['selectedStopdesk'])` — íÔÊŞ `state_id`/`city_id` ãä ÇáãßÊÈ äİÓå.
  - ÇáæÇÌåÉ: **ÈáÏíÉ ÇáÇÓÊáÇã ãÎİíÉ** ááãÊÌÑ (`@if ($this->delivery_type === 'home')`)¡ ÈØÇŞÉ ÇáãßÊÈ ÇáæÍíÏ ÊÚÑÖ ÔÇÑÉ ÇáßæÏ¡ ŞÇÆãÉ ÇáãßÇÊÈ ÊãÑ `option-code`/`option-extra` (address+phone)¡ ÑÓÇáÉ «áÇ ãßÇÊÈ» ÃÕÈÍÊ `no_desks_in_state` (ãİÊÇÍ en ÃõÖíİ¡ ar/fr/es ãæÌæÏÉ).
  - ÇáÊÍŞŞ: `city_id` ãØáæÈ İŞØ ááÊæÕíá ááãäÒáº İÍÕ äØÇŞ ÇáãßÊÈ ÃÓŞØ ÔÑØ ÇáÈáÏíÉ (ÇáãßÊÈ äİÓå åæ ÇáãÑÌÚ) — íÈŞì ÔÑØ ÇáãÊÌÑ/ÇáæáÇíÉ/ÇáÔÑßÉ.
- **Ì. ÌÇäÈ ÇáÊÇÌÑ:** `formOffices` + `inlineStopdeskOptions` íÊÖãäÇä `code` ãä `external_code`¡ æ`option-code="code"` İí ŞæÇÆã ÇáãßÊÈ (order-form-modal¡ delivery-edit-modal¡ orders-table-cell¡ orders-mobile-fields) — ÈŞí ÇáäØÇŞ ÇáÈáÏí ááÊÇÌÑ ßãÇ åæ.
- **ÇÎÊÈÇÑÇÊ:** `StorefrontOrderShippingCascadeTest` — ÇÓÊõÈÏá «only offices inside the selected commune» ÈÜ 3 ÇÎÊÈÇÑÇÊ: «every office of the selected wilaya… ordered by desk code, with details» (ÊÍŞŞ 02A ŞÈá 02B + address/phone + ÛíÇÈ `role="city-select"`)¡ «an office outside any chosen commune completes the stopdesk order with its own commune» (ÈÏæä ÈáÏíÉ ÅØáÇŞğÇ)¡ «wilaya options carry a numeric-code badge and order by wilaya number» (state_codes 01/02). `CartOrderLimitsTest` — «stopdesk without a state» íÊÍŞŞ ãä ÎØÃ ÇáæáÇíÉ İŞØ (áÇ ÈáÏíÉ)¡ æÃõÚíÏÊ ßÊÇÈÉ ÇÎÊÈÇÑóí ÇáäØÇŞ ÇáÈáÏí Åáì äØÇŞ ÇáæáÇíÉ ÇáßÇãá.
- **ÇáÊÍŞŞ ÇáäåÇÆí:** `view:clear`+`view:cache` + `npm run build` (vite 7.3.0¡ ÊÍĞíÑÇÊ chunk ÓÇÈŞÉ İŞØ) + ÇáÌæáÉ ÇáßÇãáÉ ÇáäÙíİÉ **539 äÇÌÍ (1918 assertions)** — ŞÈáåÇ 537/1904 (+2 ÇÎÊÈÇÑÇÊ +14 ÊÃßíÏ) — ÕİÑ ÇäÍÏÇÑ.
- **ÅÕáÇÍ ÃÎØÇÁ ÇáÓÌá (09-09):** ? `storage/logs/laravel.log` ÃÈáÛ 3 ÍæÇÏË: ãÑøÊÇä `SQLSTATE[42000] 1582 Incorrect parameter count in the call to native function 'ltrim'` — ßÇä `ltrim(state_code, '0')` ÈæÓãíä ÛíÑ ÕÇáÍ İí MySQL (ÚäÏãÇ ßÇäÊ ÇáÑæÇÈØ `CAST(ltrim(...))` İí `scopeOrderedByCode` + ãÇ íÚÇÏáåÇ İí `officesForSelection`)¡ + ãÑÉ `NOEST trackings/info HTTP 503 Upstream down` (ÇäŞØÇÚ ÎÇÑÌí ÊõÕÑİå ÇáÌæáÉ ÈÕÈÑ — ÇÎÊÈÇÑ ÇáÍŞä ÇáãÊÚãÏ). ? **ÇáÌĞÑ:** áõæøäÊ ÇáÓæíÊ Úáì SQLite (íŞÈá `ltrim` ÈæÓãíä) ÈíäãÇ ÇáÊØÈíŞ ÇáÍí Úáì MySQL — İÇÎÊİì ÇáÚíÈ. ? **ÇáÅÕáÇÍ ÈáÇ ÊÑŞíÚ:** ÇáÊÑÊíÈ ãÈÇÔÑÉ ÈÇáÚãæÏ (`orderBy('state_code')` / `orderBy('external_code')`) áÃä ÇáÃßæÇÏ char(2) ãÈØøäÉ ÈÇáÃÕİÇÑ İÇáÊÑÊíÈ ÇáäÕí == ÇáÑŞãí (íÍÇİÙ Úáì 01..58 æ ãÌãæÚÇÊ 02A/02B)¡ æ`StopdeskOfficeSync::stateByDeskCode()` íØÇÈŞ `state_code` ÈÇáÍÔæ `str_pad(...,2,'0',STR_PAD_LEFT)` ÈÏá `whereRaw` — ßáåÇ ÈáÇ ÏæÇá ÎÇÕÉ ÈÇáÜ vendor. ? **ÊÍŞøŞ Úáì MySQL ÇáÍí ãÈÇÔÑÉ** (states ? 01..08¡ desks ? 10A/10B/11A/12A/12B/13A) + ÇáÌæáÉ ÇáßÇãáÉ 539/1918 äÙíİÉ¡ æáÇ ÃÎØÇÁ ÌÏíÏÉ İí ÇáÓÌá.
- **ÊÍÓíä ÃÏÇÁ ŞæÇÆã ÇáÇÎÊíÇÑ + ÇáÓÈíäÑ + ãäÚ ÇáÖÛØ ÇáãÊßÑÑ (09-09):** ? **ÇáãÔßáÉ:** ßá render íÚíÏ ÊÖãíä ŞÇÆãÉ ÇáÎíÇÑÇÊ ÇáßÇãáÉ (`data-options`) İí ÕİÍÉ checkout (ãßÇÊÈ ßá ÇáæáÇíÉ + ßá ÇáÈáÏíÇÊ) İÊÊÖÎã ÇáÇÓÊÌÇÈÉ æÊõÈØÆ ÇáãæÑİ¡ ÈáÇ ãÄÔÑ ÊÍãíá æáÇ ãÇäÚ ÖÛØ ãÊßÑÑ. ? **ÇáÈäíÉ ÇáÚÇãÉ (ÊÓÑí Çáßõáí Úáì ßá select):** ÇáãßæøäÇä `storefront/select` + `edz/select` ÇßÊÓÈÇ props `lazy`/`source`/`scope` + ßÔİ `$roundtrip` (Âí `wire:model.live*`/`wire:change` Ãæ lazy)¡ ãÚ data-attrs. JS İí `storefront-select.js` + `edz-select.js`: äãØ `loading` (ÓÈíäÑ íÈÏøá ÇáÔíİÑæä + Õİ ÊÍãíá ÈÇááæÍÉ)¡ **ãäÚ ÇáİÊÍ ÃËäÇÁ ÇáÊÍãíá ãÚ ÇáÓãÇÍ ÈÇáÅÛáÇŞ ÏÇÆãğÇ** (toggle)¡ **ÈæÇÈÉ round-trip** Úáì ÇáÇÎÊíÇÑ: `_waitServerAck` áŞİá ÇáãÔÛøá ÍÊì íÚÊÑİ ÇáÎÇÏã ÚÈÑ `$wire.$watch` İí `_bindServerValue.read` (ÓŞİ 6 ËæÇäò)¡ æ`applyOptions` íÊÌÇåá ÇáãæÑİÇÊ (seed) Ííä Êßæä ÇáŞÇÆãÉ ÇáÈÚíÏÉ ÌÇåÒÉ ááäØÇŞ ÇáÍÇáí. ? **ÇáÊÍãíá ÇáßÓæá ßÃİÖá äåÌ:** İí checkout İŞØ¡ ŞÇÆãÊÇ **ÇáÈáÏíÇÊ** (city¡ `$citiesSelectOptions`) æ**ÇáãßÇÊÈ** (office¡ `$stopdeskSelectOptions`) ÊÕÈÍÇä `lazy source="..." :scope="..."` — áÇ ÊõÖãøä İí ÇáÕİÍÉ ÅáÇ «seed» (ÇáÇÎÊíÇÑ ÇáÍÇáí İŞØ)¡ æÊõÌáÈ ÚäÏ Ãæá İÊÍ áßá scope (composite `s{state}|p{provider}` / `s{state}|dt{type}`) æÊõÎÒøä İí ßÇÔ ÃãÇãí (ÍÏ 12 scope¡ LRU). ÃİÚÇá Volt ÌÏíÏÉ: `$formatOfficeOptions`/`$citiesForSelection` (ÇÓÊõÎÑÌ ãä render ÈäİÓ ÇáãäØŞ ÈÇáÖÈØ — stopdesk hasGlobalOffice/officeCityIds¡ home DeliveryRateCity+ShippingRate) + `$stopdeskSelectOptions`/`$citiesSelectOptions`. ÇáæáÇíÇÊ/ÇáÔÑßÇÊ ÈŞíÊ ãÖãøäÉ (ÑÎíÕÉ). ? **ÇÎÊÈÇÑÇÊ:** ÇÓÊõÈÏá İÍÕ HTML ÇáãÖãøä ÈİÍÕ ÍãæáÉ ÇáİÚá ÚÈÑ `$component->instance()->stopdeskSelectOptions("s..|p..")` (ÇáÊÑÊíÈ 02A ŞÈá 02B¡ ÇáÊİÇÕíá¡ ßá ãßÇÊÈ ÇáæáÇíÉ ãÚ/ÈÏæä ÔÑßÉ) + ÇáÊÃßÏ Ãä SSR íÍãá `data-lazy="1"`/`data-source` ÈáÇ ÃÓãÇÁ ÇáãßÇÊÈ. ? **css:** `animate-spin` ÊæáÏ ÊáŞÇÆíğÇ (Tailwind íãÓÍ `resources/views/**/*.blade.php`) — æÊÍŞøŞ ãä ÇáÍÒã (`storefront-*.js`, `panel-*.js` íÍãáÇä `ensureRemoteOptions`). ? **ÇáÌæáÉ ÇáßÇãáÉ ÈÚÏ ÇáÊÛííÑ: 539 äÇÌÍ (1936 assertions — +18 ÊÃßíÏğÇ)**.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| 35 ÊÑŞíã ÇáæáÇíÇÊ ÚÈÑ ÇáãÔÑæÚ | ? | State.php (scope) + components/storefront/select + components/edz/select + edz-select.js + _badges.scss + storefront/order-form + merchant/orders/index + orders-table-cell + order-form-modal + delivery-edit-modal + teams/index + delivery/announced-rates + delivery/stopdesk + stopdesk-state-grid | — |
| 35 ãßÇÊÈ ÇáæáÇíÉ + ÇÔÊŞÇŞ ÇáÈáÏíÉ (ÇáãÊÌÑ) | ? | storefront/order-form.blade.php + storefront.php (en: no_desks_in_state) | — |
| 35 ßæÏ ÇáãßÊÈ ÈÇáÊÇÌÑ | ? | merchant/orders/index (formOffices + inlineStopdeskOptions) + ŞæÇÆã ÇáãßÊÈ ×4 | — |
| ÇÎÊÈÇÑÇÊ İÑÚíÉ | ? | StorefrontOrderShippingCascadeTest (11/63) + CartOrderLimitsTest (12/66) | 11 + 12 ÇÎÊÈÇÑğÇ (63+66) |
| **ÇáÅÌãÇáí (ÌæáÉ äÙíİÉ)** | **539 äÇÌÍ (1936 assertions)** | | |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ÔÇÑÇÊ ÃÑŞÇã ÇáæáÇíÇÊ İí ÇáŞæÇÆã (search ÈÇáÑŞã + ÊÑÊíÈ)¡ ÅÎİÇÁ ÈáÏíÉ ÇáÇÓÊáÇã æÇÔÊŞÇŞåÇ ãä ÇáãßÊÈ¡ ŞÇÆãÉ ãßÇÊÈ ÈÃÓØÑ ÇáÚäæÇä/ÇáåÇÊİ æÔÇÑÉ ÇáßæÏ¡ ÇáÊÍÏíÏ ÇáÊáŞÇÆí áãßÊÈ ÇáæáÇíÉ ÇáæÍíÏ¡ æÃÏÇÁ `updated(['selectedStopdesk'])` ÇáÍí Úáì ÇáãÊÌÑ ÇáãÈÇÔÑ (`store.edzeery.test`/`store.noest.test`).

---

## ÅÕáÇÍ ÈæÈ ÇÈ ÇÎÊíÇÑ ÇáãäÊÌ/ÇáãÊÛíÑÇÊ + ãÒÇãäÉ ÇáÊÍÏíÏ + ÔÇÑÉ «ßá ÇáãÊÛíÑÇÊ» + ÃÏÇÁ ÇáãÍÑÑ (ÓÈÊãÈÑ 2026) ?

**ÈãæÇİŞÉ ÇáãÓÊÎÏã ÇáÕÑíÍÉ («äİøĞ ÇáÎØÉ ßÇãáÉ») ÈÚÏ İÍÕ ÔÇãá ááßæÏ ÇáÍí. ÇáäØÇŞ: ÊÛííÑ/ÅÖÇİÉ ãäÊÌ¡ ßãíÉ¡ ÓÚÑ ãä ÇáÌÏæá ãÈÇÔÑÉ.**

### ÇáÊÔÎíÕ ÇáãÚÊãÏ ÈÇáÃÏáÉ (ÇáÌĞæÑ)
- **Bug1 — ÅÛáÇŞ ÈæÈ ÇÈ ÇáÚäÇÕÑ ÚäÏ ÅÛáÇŞ ÈæÈ ÇÈ ÇáãäÊÌ/ÇáãÊÛíÑ:** ÍÇæíÉ äæÇİĞ ÇáÚäÇÕÑ (`partials/orders-items-edit-modals.blade.php:16,86,141`) ßÇäÊ ÊÓÊãÚ Úáì ãÓÊæì window ÚÈÑ `@edz-modal-closed.window="$wire.closeItemsModal()"`º æÃí `x-edz.modal` ÚäÏ ÇäŞáÇÈ `open` íØáŞ `edz-modal-closed` (`components/edz/modal.blade.php:16-23`). ÇáÜ picker ÇáÔŞíŞ (`orders-product-picker.blade.php`¡ íõÖãøä İí `index.blade.php:6145`) ÚäÏ ÇÎÊíÇÑ/ÅÛáÇŞ Ãí ãÊÛíÑ ? ÍÏË íÊİŞøÚ ááÜ window ? íŞİá äÇİĞÉ ÇáÚäÇÕÑ. (ĞÇÊ ÇáÚáÉ ÊÌÚá Ãí ãæÏÇá ÂÎÑ íõÛáŞ ÃËäÇÁ ÇáÌáÓÉ íŞİáåÇ ßĞáß — ÊÓÑíÈ äØÇŞ.)
- **Bug2 — ÇáãäÊÌ íÈŞì «ãÍÏÏğÇ» ÍÊì ÊÍÏíË ÇáÕİÍÉ:** `formSelectedItems` ÎÑíØÉ `variant_id => qty` ÊõÈäì İŞØ ÚÈÑ `syncFormSelectedItems()` (HasOrderProductPicker:314)º áßä ÏæÑÉ ÇáÍíÇÉ ÛíÑ ãÊäÇÙÑÉ: `closeItemsModal` íÕİøÑ `form['items']` Ïæä ÇáãÒÇãäÉ¡ æ`openCreateModal`/`openEditModal` íÚíÏÇä ÈäÇÁ ÇáÜ draft Ïæä ãÒÇãäÉ ? ÊäÊŞá ÇáÎÑíØÉ ÇáÚÇáŞÉ ÚÈÑ ÇáØáÈíÇÊ/ÇáäæÇİĞ æÊÕÏøÑ check/ÊÚØíáğÇ İí ÇáÜ picker (orders-product-picker.blade.php:~99/229) ÍÊì íÕİøÑåÇ `mount` İŞØ (index.blade.php:143).

### ãÇ Êóãø
- **Ã (Bug1):** ÇáÍÇæíÇÊ ÇáËáÇË ÕÇÑÊ `@edz-modal-closed="$wire.closeItemsModal()"` (ÈÏæä `.window`) — ÇáÍÏË ãä äÇİĞÉ ÇáÚäÇÕÑ äİÓåÇ íÊİŞøÚ ÅáíåÇ¡ æáÇ ÊãÑ ÃÍÏÇË ÇáÜ picker ÇáÔŞíŞ ÚÈÑåÇº ÈŞí ÅÛáÇŞ X/ÇáÎáİíÉ/Escape ãÔÊÑßğÇ ÈäİÓ ÇáãÓÇÑ.
- **È (Bug2):** ÊæÍíÏ ÏæÑÉ ÍíÇÉ ÇáÎÑíØÉ — `closeItemsModal`/`openCreateModal`/`openEditModal` (index.blade.php) ÊÓÊÏÚí `syncFormSelectedItems()` ÈÚÏ ÈäÇÁ/ÊÕİíÑ ÇáÜ draft (ßÇä `openItemsModal` íÓÊÏÚíåÇ ÃÕáğÇ).
- **Ì (ÇáãíÒÉ):** `pickerProductResults` ÍãøáÊ `variant_ids` (ãÕİæİÉ ULIDs ãä ÚáÇŞÉ ãÍãøáÉ ÃÕáğÇ — ÈáÇ ÇÓÊÚáÇã ÅÖÇİí)º Õİ ÇáãäÊÌ ãÊÚÏÏ ÇáãÊÛíÑÇÊ íÚÑÖ: ÔÇÑÉ check «ÃõÖíİÊ ßá ÇáãÊÛíÑÇÊ» ÚäÏ ÇßÊãÇá ßá ãÊÛíÑÇÊå¡ ÚÏøÇÏ «x/y ÈÇáÓáÉ» ÚäÏ ÇáÌÒÆí¡ æÇáÔíİÑæä ßÓÇÈŞº íÙá ÇáÓØÑ ŞÇÈáÇğ ááİÊÍ. ãİÊÇÍ ÊÑÌãÉ ÌÏíÏ `merchant_panel.all_variants_added` ×4 áÛÇÊ (en/ar/fr/es).
- **Ï (ÇáÃÏÇÁ):** `updateFormItemQty` áã ÊÚÏ ÊäİĞ `ProductVariant::find` áßá äŞÑÉ +/? — preorder íõÔÊŞ ãä `stock` ÇáãÎÒøä İí ÓØÑ ÇáÜ draft (`allowBackorder(currentStore())`)º æ`updated('form.items'|'form.customer_phone')` ÕÇÑÊ ãÍÕæÑÉ ÈÓíÇŞ ÇáÅäÔÇÁ/ÇáÊÚÏíá (`showCreateModal || showEditModal`) — ãÍÑÑ ÇáÚäÇÕÑ áÇ íÚÑÖ ÊÍĞíÑÇÊ ÇÓÊÚÈÇÏ ÃÕáğÇ.

### ÇáÇÎÊÈÇÑÇÊ — `OrderInlineItemsEditTest` (14?18 / 89 ÊÃßíÏğÇ)
- ? äæÇİĞ ÇáÚäÇÕÑ ÊÓÊãÚ áÜ close ÇáÎÇÕø ÈåÇ İŞØ (`assertDontSeeHtml('edz-modal-closed.window')` + `assertSeeHtml('edz-modal-closed="…")`).
- ? ÎÑíØÉ ÇáÊÍÏíÏ ÊÊÕİøÑ ãÚ ÅÛáÇŞ ÇáÚäÇÕÑ æÊõÈäì ãä ØáÈíÉ ßá edit/ÅäÔÇÁ (ÌæáÉ A?create?B ÈáÇ ÈŞÇíÇ).
- ? ÔÇÑÉ «ßá ÇáãÊÛíÑÇÊ» ÊÙåÑ İŞØ ÈÚÏ ÅÖÇİÉ ßá ÇáãÊÛíÑÇÊ (ãÊÛíÑ æÇÍÏ ? áÇ ÊÙåÑ).
- ? ÇáãÍÑÑ íÔÊŞ preorder ãä ÇáÜ draft ÈáÇ ÇÓÊÚáÇã `product_variants` (ÍÕíáÉ `DB::listen` ÎáÇá open+stepper ÕİÑ ÇÓÊÚáÇãÇÊ Úáì `product_variants`).
- ÏÇáÉ `itemsOrder` ÇáãÓÇÚÏÉ ŞÈöáÊ ãÚÇãá `$phone` ÇÎÊíÇÑíğÇ áÊÌäÈ ÊÖÇÑÈ unique ááÒÈæä (ÈáÇ ÃËÑ Úáì ÈŞíÉ ÇáÇÓÊÏÚÇÁÇÊ).

### ÇáÏáíá ÇáäåÇÆí
- `php -l` Úáì ßá äÕ ÊÚÏóøá + `php artisan view:cache` (success) + ÇáÌæáÉ ÇáßÇãáÉ `tests\Feature\Order tests\Feature\Merchant` = **345 äÇÌÍ (1322 assertions) — ÕİÑ ÇäÍÏÇÑ**.

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ÇÎÊíÇÑ ãäÊÌ/ãÊÛíÑ ãä ÇáÜ picker Ëã ÅÛáÇŞå íÈŞí äÇİĞÉ ÊÚÏíá ÇáÚäÇÕÑ ãİÊæÍÉº ÅÖÇİÉ ãäÊÌ Ëã İÊÍ ØáÈíÉ ÃÎÑì/ÅäÔÇÁ áÇ íõÙåÑ ÈŞÇíÇ «ãÍÏÏ» (ÈáÇ ÊÍÏíË ááÕİÍÉ)º ÔÇÑÉ «ÃõÖíİÊ ßá ÇáãÊÛíÑÇÊ» İí ÈæÈ ÇÈ ÇáãäÊÌÇÊ ÚäÏ ÇßÊãÇá ãÊÛíÑÇÊ ãäÊÌ (ãÚ ÔÇÑÉ ÇáÌÒíÁ «x/y ÈÇáÓáÉ»)¡ æÅÈŞÇÁ İÊÍ ŞÇÆãÉ ÇáãÊÛíÑÇÊ ããßäğÇ.

---

## ÅÚÇÏÉ ÊÕãíã ÕİÍÉ ÇáÊÊÈÚ — ÇáãÑÍáÉ A: ÈäíÉ ÇáÕİÍÉ (ÊÈæíÈÇä + ÌÒÁÇÊ) (ÓÈÊãÈÑ 2026) ??

**ØáÈ ÇáãÓÊÎÏã:** ÕİÍÉ ÊÊÈÚ ÇÍÊÑÇİíÉ ãÊÌÇæÈÉ (375/768/1440) ÈáÇ ÍÔÑ İí `index`¡ ÈÍË/İáÊÑÉ ßäãØ ÕİÍÉ ÇáØáÈíÇÊ¡ ÊÍÏíË ÍÇáÉ ÊáŞÇÆí (áÇ íÏæí)¡ ÊÈæíÈ «ÑÌá ÇáÊæÕíá» + ÈæÇÈÉ ÏÎæá æáæÍÉ ÎÇÕÉ Èå¡ æãÑÇÌÚÉ ÕáÇÍíÇÊ. **ÇáŞÑÇÑÇÊ ÇáãÚÊãÏÉ (ÈÚÏ 4 ÃÓÆáÉ):** ÇáÊäİíĞ ÈÇáÊÑÊíÈ A?B?C?D?E?F?Gº ÇáÊÈæíÈÇä İí äİÓ ÇáÕİÍÉº ÊÓÌíá ÏÎæá ÇáÑÌá ÈßáãÉ ãÑæÑ İæŞ `delivery_riders` (guard ãäİÕá)º ÊÃÌíá Phase 32 (32.2?32.8) ÈÚÏ G.

- **A — ÇáÈäíÉ æÇáÊÈæíÈÇä:** `index.blade.php` ÕÇÑ ãäÙãğÇ — state ÌÏíÏ `trackingTab` ('carrier'|'rider') + ÊÖãíä ÌÒÆíÇÊº ÇÓÊõÎÑÌÊ ßÊá ÇáÅÍÕÇÆíÇÊ/ÔÑíØ ÇáÃÏæÇÊ/ÇáŞÇÆãÉ (ÌÏæá ÇáÏíÓßÊæÈ + ÈØÇŞÇÊ ÇáãæÈÇíá + load-more) äåÇÆíğÇ ãä ÇáÕİÍÉ¡ ãÚ «@if carrier ? ÅÍÕÇÆíÇÊ+ÃÏæÇÊ+ŞÇÆãÉ» / «@else ? placeholder ÇáÑÌá» (ÍÊì ÇáãÑÍáÉ D).
- **ÌÒÆíÇÊ ÌÏíÏÉ:** `partials/tracking-tabs.blade.php` (ÓØÑ ÊÈæíÈíä ÈäãØ returns `edz-btn--primary/ghost` ÚÈÑ `wire:click="$set('trackingTab', …)"`)¡ `tracking-stats`¡ `tracking-toolbar`¡ `tracking-list`¡ `tracking-rider-placeholder`.
- **ÇáÊÑÌãÉ:** ãİÇÊíÍ ×4 áßá áÛÉ (ar/en/fr/es): `tracking_tab_carrier` + `tracking_tab_rider` + `rider_tab_empty_title` + `rider_tab_empty_hint`.
- **ÅÕáÇÍ ÚíÈ ÓÇÈŞ ßÓÑ ÇáİÑäÓíÉ ßáíğÇ:** `resources/lang/fr/order_flow.php:30` — `'Veuillez choisir l'agence stopdesk'` (ÇŞÊÈÇÓ ãÓÊŞíã) ? `l’agence` (ÈÇŞÊÈÇÓ ãØÈÚí ßãÇ åæ ãÊÈÚ İí Çáãáİ). `php -l` Óáíã ÇáÂä áßá ÇáãáİÇÊ ÇáÃÑÈÚÉ.
- **ÇáÊÍŞŞ:** `view:cache` success + ÇáÌæáÉ ÇáãÑÌÚíÉ ááÊÊÈÚ **30 äÇÌÍ (123 ÊÃßíÏ)** — ãäåÇ ÊÍãíá `merchant.tracking.index` ÚÈÑ Volt İí `OrderEventLogVisibilityTest` (ÏÑÌÇä íÕáÇä/áÇ íÕáÇä ááÍÏË ÍÓÈ ÇáÏæÑ) + `OrderTrackingCarrierValidationTest` (32.1) — ÕİÑ ÇäÍÏÇÑ.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| A ÈäíÉ ÇáÕİÍÉ (ÊÈæíÈÇä + ÌÒÁÇÊ) | ? | merchant/tracking/index.blade.php (state + includes) + ÌÒÆíÇÊ ×5 + order_flow.php ×4 (7 ãİÇÊíÍ) + ÅÕáÇÍ fr:30 | ÇáÌæáÉ ÇáãÑÌÚíÉ 30 äÇÌÍ (123 ÊÃßíÏ) |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px / 768px / 1440px — ÓØÑ ÇáÊÈæíÈíä ÃÚáì ÇáÕİÍÉ¡ ÊÈÏíá ÇáãÍÊæì Èíä ÇáÊÈæíÈíä¡ æÈŞÇÁ ÇáÊÈæíÈ ÇáÃæá ÈäİÓ ÇáÓáæß ÇáæÙíİí ÇáÓÇÈŞ ÊãÇãğÇ.

---

## ÅÚÇÏÉ ÊÕãíã ÕİÍÉ ÇáÊÊÈÚ — ÇáãÑÍáÉ B: ÈÍË ÍÑ + İáÇÊÑ ÇÍÊÑÇİíÉ (ÓÈÊãÈÑ 2026) ?

**ÇáäØÇŞ:** ÈÍË ÍÑ debounce 600ms + popover İáÇÊÑ (ÔÑßÉ/ÍÇáÉ ãÊÚÏÏÉ/ÊÇÑíÎ) + ÔÑíØ İáÇÊÑ äÔØÉ + ÚÏøÇÏåÇ + total ÇáãÕİøì — ÈäãØ `merchant/orders/index` ÍÑİíğÇ.

- **ÇßÊÔÇİ ÚíÈ ŞÇÆã:** ÇáİáÇÊÑ ÇáÓÇÈŞÉ ßÇäÊ `wire:model.live` ÈáÇ Ãí `updated` hook æáÇ ÇÓÊÏÚÇÁ `loadShipments` — Ãí **ÊÛííÑ İáÊÑ áÇ íÚíÏ ÌáÈ ÇáŞÇÆãÉ** (ÊõÍÏóøË İŞØ ÚäÏ refresh íÏæí). ÇáãÑÍáÉ B ÃÕáÍÊ åĞÇ ÈÊİÚíá ÇáÜ reload ÇáÍí.
- **`tracking/index.blade.php`:** state ÌÏíÏ `search` + `filteredTotal`º `filters.tracking_status` (ãİÑÏ) ? `tracking_statuses` (ãÕİæİÉ ãÊÚÏÏÉ)º `loadShipments` íÍæøá ÇáÈÍË Åáì where-group (number/customer name/phone/phone_secondary/tracking_number/provider name) + `whereIn` ááÍÇáÇÊ + íÓÌøá `filteredTotal = total()`º ÇÓÊõÈÏá `resetFilters` ÈÜ `clearFilters` + `setFilter` + `toggleTrackingStatus` + hook `updated` (ÇÓÊæÑÏ `Livewire\Volt\updated`).
- **`partials/tracking-toolbar.blade.php`** ÃõÚíÏ ßÊÇÈÊå ÈÇáßÇãá: ÈÍË ãæÍøÏ (ÃíŞæäÉ + × + Óåã)¡ popover `x-edz.dropdown` ÈËáÇËÉ ÃŞÓÇã (ÔÑßÉ radio¡ ÍÇáÉ toggle-check¡ ÊÇÑíÎ flatpickr)¡ ÚÏøÇÏ İáÇÊÑ äÔØÉ¡ æÔÑíØ İáÇÊÑ äÔØÉ (chips ÈÚáÇãÉ × áßá İáÊÑ) + ÒÑ `clearFilters` — ßáåÇ ÈäãØ orders (`.edz-dropdown__item`¡ `.edz-dropdown__section`¡ chips `bg-accent-surface`).
- **ÇáÊÑÌãÉ:** `search_tracking_placeholder` + `tracking_count` ×4 áÛÇÊ.
- **ÅÕáÇÍ ÌÒÆí:** `merchant/tracking/index.blade.php` ÇÓÊæÑÏ `use function Livewire\Volt\updated;` (äÓíÊå Ãæá ãÑÉ — `Call to undefined function updated()` ÙåÑ İí ÇáÇÎÊÈÇÑÇÊ İÃõÕáÍ İæÑğÇ).
- **ÇáÊÍŞŞ:** `view:cache` success + ÇÎÊÈÇÑ ÌÏíÏ `tests/Feature/Merchant/TrackingSearchFilterTest` (6 ÇÎÊÈÇÑÇÊ/24 ÊÃßíÏ): ÊÈæíÈÇåãÇ¡ placeholder ÇáÑÌá¡ ÈÍË ÈÇáÑŞã ÇáÊÊÈÚ ãÚ ÇÓÊÑÌÇÚ¡ İáÊÑ ÇáÍÇáÇÊ ÇáãÊÚÏÏÉ (toggle/ÏãÌ)¡ İáÊÑ ÇáÔÑßÉ ãÚ filteredTotal¡ clearFilters — plus ÇáÌæáÉ ÇáãÑÌÚíÉ 25 äÇÌÍ (86 ÊÃßíÏ) ÈáÇ ÇäÍÏÇÑ.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| B ÈÍË + İáÇÊÑ ÇÍÊÑÇİíÉ | ? | tracking/index.blade.php (search/filteredTotal/setFilter/toggle/updated) + partials/tracking-toolbar.blade.php + ÇÓÊíÑÇÏ updated + order_flow.php ×4 (+2 ãİÇÊíÍ) + TrackingSearchFilterTest (ÌÏíÏ) | 6 (24) ÌÏíÏ + ÇáÌæáÉ 25 (86) äÙíİÉ |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** 375/768/1440 — ÇáÈÍË ÈÇáÑŞã/ÇáÇÓã/ÑŞã ÇáÊÊÈÚ ÃËäÇÁ ÇáßÊÇÈÉ¡ popover ÇáİáÇÊÑ (ÔÑßÉ/ÍÇáÇÊ ãÊÚÏÏÉ/ÊÇÑíÎ) Úáì ÇáãæÈÇíá ßÜ bottom-sheet¡ ÔÑíØ ÇáİáÇÊÑ ÇáäÔØÉ æÒÑ ÇáãÓÍ¡ æÚÏøÇÏ `filteredTotal`.

---

## ÅÚÇÏÉ ÊÕãíã ÕİÍÉ ÇáÊÊÈÚ — ÇáãÑÍáÉ C: ÊÍÏíË ÇáÍÇáÉ ÊáŞÇÆíğÇ İŞØ (áÇ ÅÌÑÇÁÇÊ íÏæíÉ) (ÓÈÊãÈÑ 2026) ?

**ÇáäØÇŞ:** ÍĞİ «ÇáÅÌÑÇÁÇÊ ÇáÓÑíÚÉ» ÇáíÏæíÉ (in_transit/out_for_delivery/failed_attempt/returning/delivered/returned/lost/damaged) ãä ÏÑÌ ÇáÔÍäÉ æßá ÓØáåÇ (closures)¡ æÅÍáÇáåÇ ÈãÒÇãäÉ ÊáŞÇÆíÉ ãä ÔÑßÉ ÇáÔÍä ãÚ ÒÑ «ÊÍÏíË ÇáÂä» áßá ÔÍäÉ íãÑ ÚÈÑ äİÓ ãÄËÑ ÇáßÊÇÈÉ ÇáæÍíÏ ÇáĞí íÓÊÎÏãå ÇáÌÏæáÉ.

- **`app/Domains/Shipping/Services/NoestTrackingSyncService.php` (ÌÏíÏ):** ãÕÏÑ ÍŞíŞÉ æÇÍÏ — `apply(OrderTracking, entry)` (ãäŞæá ÍÑİíğÇ ãä `SyncNoestTrackingJob` ãÚ ÅÈŞÇÆå idempotent: ÊÇÑíÎ íõÓÌóøá İŞØ ÚäÏ ÊÛíøÑ ÇáÍÇáÉ¡ æáÇ ÊÑÇÌÚ Úä ÍÇáÉ äåÇÆíÉ) + `syncOne(OrderTracking)` áÚãáíÉ ÊÍÏíË ÔÍäÉ ãİÑÏÉ ÚÈÑ `StopdeskOfficeSync::resolve` Ëã `trackingsInfo([ÑŞã æÇÍÏ])`¡ ãÚ ÃÎØÇÁ ãŞääÉ (`no_provider/no_number/unsupported_carrier/request_failed/no_data`). `status===null` (ÃÍÏÇË ÛíÑ ãõÚíøäÉ) ÇáÂä íÎÊã `last_synced_at` — ÈãÇ íæÇÒí `touchSyncedAt` İí ÇáãÓÇÑ ÇáÏİÚí.
- **`SyncNoestTrackingJob`:** ÃõÚíÏÊ åíßáÊå áíÓÊÎÏã `NoestTrackingSyncService::apply` (ÍõĞİÊ `apply/orderedEvents/eventDate` ÇáÎÇÕÉ) — Ãí Ãä ÇáÌÏæáÉ æÒÑ ÇáãÊÌÑ íßÊÈÇä ÈÇáÖÈØ äİÓ ÇáŞÇÚÏÉ.
- **`tracking/index.blade.php`:** ÍõĞİÊ `trackingAction` + `trackingTransition` + closure `$membership` (ßáåÇ ÃÕÈÍÊ ãíÊÉ)º ÃõÖíİ `$syncTracking(trackingId)` ãÍÑæÓ ORDER_VIEW: íÓÊÏÚí `syncOne`¡ toast äÌÇÍ/ÊÍĞíÑ (ÑÓÇáÉ ÚÇãÉ ãÚ `Log::warning` ááÊİÇÕíá)¡ Ëã `loadShipments()` æÅÚÇÏÉ `openDrawer` áÊÍÏíË ÏÑÌ ÇáÔÍäÉ İæÑğÇ.
- **`partials/order-drawer.blade.php`:** ÍõĞİ ŞÓã «Quick actions» ÈÇáßÇãáº ÃõÖíİ ŞÓã «ÊÍÏíË ÇáÍÇáÉ ÊáŞÇÆíğÇ» (íÙåÑ İŞØ ÚäÏ æÌæÏ ÑŞã ÊÊÈÚ): «ÂÎÑ ÊÒÇãä» (`last_synced_at` ÈÊÇÑíÎå Ãæ «áã ÊÊã ÇáãÒÇãäÉ ÈÚÏ») + ÒÑ `syncTracking` ãÚ spinner/loading¡ æÓØÑ ÊæÖíÍ Ãä ÇáÍÇáÉ ÊõÍÏóøË ÊáŞÇÆíğÇ ãä ÇáÔÑßÉ.
- **ÇáÊÑÌãÉ:** 8 ãİÇÊíÍ ×4 áÛÇÊ (`tracking_sync_section/now/last_synced/never_synced/synced/hint/failed/no_number`).
- **ÇáÊÍŞŞ:** `php -l` äÙíİ ×4 + `view:cache` success + **39 äÇÌÍ (146 ÊÃßíÏ)** — ãäåÇ `NoestTrackingSyncServiceTest` (ÌÏíÏ¡ 6) æÃÖíİ ÇÎÊÈÇÑÇä áÕİÍÉ ÇáÊÊÈÚ (ÒÑ ÇáÊÍÏíË íÌáÈ ÇáÍÇáÉ æíÍÏøË ÇáÏÑÌº æÚÏã ÙåæÑ ÇáŞÓã ÈáÇ ÑŞã ÊÊÈÚ) æÈŞí `NoestTrackingSyncTest` (6) ÕÇáÍğÇ ÈáÇ ÊÛííÑ.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| C ÊÍÏíË ÊáŞÇÆí (áÇ íÏæí) | ? | NoestTrackingSyncService (ÌÏíÏ) + SyncNoestTrackingJob (íÚíÏ ÊæÌíå apply) + tracking/index (syncTracking/ÍĞİ ÇáÅÌÑÇÁÇÊ) + order-drawer (ŞÓã ÇáãÒÇãäÉ) + order_flow.php ×4 (+8 ãİÇÊíÍ) + NoestTrackingSyncServiceTest (ÌÏíÏ) | 6 ÌÏíÏ ÈÇáÎÏãÉ + 2 Úáì ÇáÕİÍÉ + ÇáÌæáÉ ÇáãÑÌÚíÉ 39 (146) äÙíİÉ |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** 375/768/1440 — ŞÓã «ÊÍÏíË ÇáÍÇáÉ ÊáŞÇÆíğÇ» ÏÇÎá ÏÑÌ ÇáÔÍäÉ (ÒÑ + «ÂÎÑ ÊÒÇãä»)¡ ÛíÇÈ Ãí ÃÒÑÇÑ ÍÇáÉ íÏæíÉ¡ æÊÍÏøË ÇáÏÑÌ İæÑğÇ Ïæä ÅÛáÇŞ.

---

## ÅÚÇÏÉ ÊÕãíã ÕİÍÉ ÇáÊÊÈÚ — ÇáãÑÍáÉ D: ÊÈæíÈ «ÑÌá ÇáÊæÕíá» (ÌÇäÈ ÇáãÊÌÑ) (ÓÈÊãÈÑ 2026) ?

**ÇáäØÇŞ:** ÇÓÊÈÏÇá placeholder ÇáãÑÍáÉ A ÈŞÇÆãÉ ÍŞíŞíÉ áÑÌÇá ÇáÊæÕíá ãÚ ÚÏøÇÏÇÊ ÔÍäÇÊ ßá ÑÌá + ÊæÓÚÉ áÚÑÖ ÔÍäÇÊ ÇáÑÌá + ÊÚííä/ÅáÛÇÁ ÊÚííä ÇáÑÌá ãä ÏÑÌ ÇáÔÍäÉ (ãÚ ßÊáÉ ÊÚÇÑÖ ãÚ ÇáÔÍä ÚÈÑ ÔÑßÉ).

- **`tracking/index.blade.php`:** state ÌÏíÏ (`allRiders`, `riderRiders`, `selectedRiderId`, `riderShipments`, `riderShipmentTotal`)º `mount` íÓÊÏÚí `loadRiderOverview()` (ÃíÖğÇ íãáÃ `allRiders` áŞÇÆãÉ ÇáÊÚííä İí ÇáÏÑÌ)º `updated.trackingTab` íÍãøá ÇáÚÑÖ ÚäÏ ÏÎæá ÊÈæíÈ ÇáÑÌáº `loadRiderOverview()` — äÇİĞÉ ÚÈÑ `OrderWorkflow::carrier()` + `whereNotNull('delivery_rider_id')` + `latestTracking` áÚÏøÇÏÊóí `total/active` áßá ÑÌá İí ÇÓÊÚáÇãíä İŞØ (áÇ N+1)º `toggleRider()` / `loadRiderShipments()` — ÔÍäÇÊ ÑÌá æÇÍÏ ãÚ Íãá ãÓÈŞ (`customer/status/latestTracking/deliveryRider/city/state`) ÈäİÓ Ôßá Õİæİ ŞÇÆãÉ ÇáÔÍäÇÊº `assignRider(orderId, riderId|null)` ãÍÑæÓ `ORDER_ASSIGN` — íÑİÖ ÇáÊÚííä ÅĞÇ ßÇäÊ ÇáØáÈíÉ ÃõÑÓáÊ ÚÈÑ ÔÑßÉ (`shipping_provider_id`) æÅáÇ íÍÏøË `delivery_rider_id` (íõÓÌóøá ÊáŞÇÆíğÇ İí audit ÚÈÑ `OrderObserver`) Ëã íÚíÏ ÇáÊÍãíá.
- **`partials/tracking-rider-tab.blade.php` (ÌÏíÏ):** ÍÇáÉ İÇÑÛÉ (ÚäæÇä + ÊáãíÍ + ÒÑ «ÅÏÇÑÉ ÃÓãÇÁ ÑÌÇá ÇáÊæÕíá» ÈÔÑØ `DELIVERY_RIDERS_VIEW`) Ãæ ÈØÇŞÇÊ ÑÌÇá (ÃİÇÊÇÑ ÈÍÑİ¡ ÇáÇÓã¡ ÇáãÑßÈÉ/ÇáåÇÊİ¡ ÍÇáÉ ÛíÑ äÔØ¡ ÚÏøÇÏÊÇ active/total¡ ÔÑíØ ÊŞÏã¡ Óåã íÏæÑ ÚäÏ ÇáÊæÓíÚ).
- **`partials/tracking-rider-shipments.blade.php` (ÌÏíÏ):** ÌÏæá/Õİæİ ÔÍäÇÊ ÇáÑÌá ÇáãæÓøÚ (ÑŞã¡ badge ÇáÍÇáÉ¡ ÒÈæä/åÇÊİ/ãÏíäÉ/ÑŞã ÊÊÈÚ¡ ÇáÅÌãÇáí¡ ÒÑ ÚÑÖ ÇáÏÑÌ) + ÍÇáÉ İÇÑÛÉ.
- **`partials/order-drawer.blade.php`:** ŞÓã «ÑÌá ÇáÊæÕíá» — ŞÑÇÁÉ ÇáÇÓã ÇáÍÇáí + ŞÇÆãÉ `x-edz.dropdown` (ÅáÛÇÁ ÇáÊÚííä / ßá ÇáÑÌÇá) ÊÙåÑ İŞØ ÈÔÑØ `ORDER_ASSIGN` æÚÏã æÌæÏ ÔÑßÉ ÔÍä.
- **`tracking-rider-placeholder.blade.php`:** ÍõĞİ (ÍõáøÊ ÇáÍÇáÉ ÇáİÇÑÛÉ ÏÇÎá ÇáÊÈæíÈ ÇáÌÏíÏ).
- **ÇáÊÑÌãÉ:** 11 ãİÊÇÍğÇ ×4 áÛÇÊ (`rider_manage_link/shipments_count/active/assign_section/assign/change/unassign/saved/has_provider/no_shipments`).
- **ÇáÊÍŞŞ:** `php -l` ×4 äÙíİ + `view:cache` success + **54 äÇÌÍğÇ (210 ÊÃßíÏğÇ)** ÈáÇ ÇäÍÏÇÑ¡ ãäåÇ **5 ÇÎÊÈÇÑÇÊ ÌÏíÏÉ** áÕİÍÉ ÇáÊÊÈÚ (ŞÇÆãÉ ÇáÑÌÇá ÈÚÏøÇÏÇÊåÇ¡ ÊæÓÚÉ ÑÌá ÊÚÑÖ ÔÍäÇÊå İŞØ¡ ÊÚííä ÑÌá ãÚ ÊÍÏíË ÇáÚÑÖ¡ ÑİÖ ÇáÊÚííä ÚäÏ æÌæÏ ÔÑßÉ ÔÍä¡ æãäÚ `assignRider` ÈÏæä ÕáÇÍíÉ `order.assign` áÜ STAFF ? 403).
- **ÇßÊÔÇİ/ÅÕáÇÍ ÃËäÇÁ ÇáÚãá:** `with('latestTracking:id,order_id,delivered_at,returned_at')` ãÚ `latestOfMany` íäÊÌ `ambiguous column name: order_id` İí SQLite ? ÅáÛÇÁ ÊŞííÏ ÇáÃÚãÏÉ æÇÓÊÎÏÇã `with('latestTracking')` ßÇãáğÇ (ãÓÇÑ ÇáÔÍäÇÊ íÓÊÎÏã äİÓ ÇáÃÓáæÈ ÛíÑ ÇáãŞíÏ).

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| D ÊÈæíÈ ÑÌá ÇáÊæÕíá + ÊÚííä | ? | tracking/index (riderRiders/toggleRider/loadRiderShipments/assignRider) + partials/tracking-rider-tab + tracking-rider-shipments (ÌÏíÏÇä) + order-drawer (ŞÓã ÇáÑÌá) + ÍĞİ placeholder + order_flow.php ×4 (+11 ãİÊÇÍğÇ) + TrackingSearchFilterTest (+5) | +5 Úáì ÇáÕİÍÉ + ÇáÌæáÉ 54 (210) äÙíİÉ |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** 375/768/1440 — ÓÑÏ ÑÌÇá ÇáÊæÕíá æÚÏøÇÏÇÊå¡ ÊæÓíÚ ÑÌá áÚÑÖ ÔÍäÇÊå¡ ÊÚííä/ÅáÛÇÁ ÊÚííä ÇáÑÌá ãä ÏÑÌ ÇáÔÍäÉ (æÇÎÊİÇÁ ÇáŞÇÆãÉ ÚäÏ ÇÎÊíÇÑ ÔÑßÉ ÔÍä)¡ æÔÑíØ ÊŞÏã ÇáÍÇáÉ ÇáãÊÌÇæÈ.

---

## ÅÚÇÏÉ ÊÕãíã ÕİÍÉ ÇáÊÊÈÚ — ÇáãÑÍáÉ D-fix: ÊÌÇäÓ ÚÑÖ ÊÈæíÈ «ÑÌá ÇáÊæÕíá» (ÈÕÑí İŞØ¡ áÇ ãäØŞ ÌÏíÏ) (ÓÈÊãÈÑ 2026) ?

**ÇáäØÇŞ:** ÑİÚ ÊÈæíÈ ÇáÑÌá Åáì ÊßÇİÄ ÈÕÑí ãÚ ÊÈæíÈ ÇáÔÑßÇÊ (stat cards + ÌÏæá ÓØÍ ãßÊÈ/ÈØÇŞÇÊ ãæÈÇíá + ÒÑ äÓÎ ÑŞã ÇáÊÊÈÚ + ÊÑŞíã ÕİÍÇÊ ÍŞíŞí + ÓØÑ ãáÎÕ)¡ ÈáÇ Ãí ÊÛííÑ İí ãäØŞ D (`toggleRider`/`assignRider` Ïæä áãÓ¡ æ`loadRiderShipments` ÈŞí ãÚ ÊÛííÑ åíßáí æÇÍÏ: ÇÓÊÈÏÇá `limit(200) ÇáÕÇãÊ` ÈÊÑŞíã ÕİÍÇÊ).

- **`partials/tracking-rider-stats.blade.php` (ÌÏíÏ):** 3 ÈØÇŞÇÊ ÅÍÕÇÁ ÊÚßÓ `tracking-stats` ÍÑİíğÇ (edz-card + ÑãæÒ `accent-surface/success/warning` æÍÏåÇ): ÑÌÇá äÔØæä (ÃíŞæäÉ users)¡ ÔÍäÇÊ äÔØÉ ááíæã (cube)¡ ãÓÊÍŞÇÊ COD ááíæã ÈÇáÜ `currency()` (banknotes) — `grid-cols-1 sm:grid-cols-3 gap-4 mb-6`.
- **`tracking/index.blade.php`:** state ÌÏíÏ (`riderShipmentsPage`, `riderStatsActiveCount/ActiveShipments/CodDueToday`). İí `loadRiderOverview` ÇÓÊÚáÇã ÊÌãíÚí **æÇÍÏ** İŞØ (áÇ ÇÓÊÚáÇã áßá ÑÌá): `groupBy delivery_rider_id` + `whereDate(created_at, today())` + `whereNotNull('delivery_rider_id')` + `whereIn(status_id, carrier)` + `whereDoesntHave('latestTracking', delivered/returned)` ? `count(*)` æ`sum(total_amount)` ? íÛĞøí ÇáÇÓÊÇÊ ÇáËáÇËÉ. ÃõÚíÏÊ åíßáÉ `loadRiderShipments`: `$mapRiderShipment` (closure ãÔÊÑß) + `forPage(riderShipmentsPage, 20)` + `riderShipmentTotal = (clone $query)->count()` (ÇáÅÌãÇáí ÇáÍŞíŞí ÚÈÑ ßá ÇáÕİÍÇÊ)º `$loadMoreRiderShipments` ÌÏíÏ íÖíİ ÇáÕİÍÉ ÇáÊÇáíÉ `array_merge` ãä Ïæä ÅÚÇÏÉ ÖÈØ `selectedRiderId`. (ßáÇ ÇáÚãáíä `use ($mapRiderShipment)` — ÛíÇÈ capture ÓÈøÈ «Undefined variable» ÃõÕáÍ.)
- **`partials/tracking-rider-tab.blade.php`:** ÊÖãíä ÇáÇÓÊÇÊ ÃÚáì İÑÚ `@else` + ÓØÑ ãáÎÕ «:riders ÑÌá · :shipments ÔÍäÉ äÔØÉ» ÈäİÓ æÒä ãæÖÚ ÓØÑ `filteredTotal` İí ÊÈæíÈ ÇáÔÑßÇÊ.
- **`partials/tracking-rider-shipments.blade.php` (ÅÚÇÏÉ ßÊÇÈÉ):** ÌÏæá ÓØÍ ãßÊÈ `hidden md:block` (ÑŞã/ÒÈæä/ãÏíäÉ/ÇáÅÌãÇáí/badge ÇáÍÇáÉ/ÑŞã ÇáÊÊÈÚ ÈÒÑ äÓÎ/ÚÑÖ ÇáÏÑÌ — ÈÇÑÊİÇÚ 7 ÃÚãÏÉ) + ÈØÇŞÇÊ ãæÈÇíá `md:hidden` ÈäİÓ áÛÉ ÈØÇŞÇÊ `tracking-list` (ÒÑ ÇáäÓÎ Alpine `navigator.clipboard` + `EdzSwal.toast` äİÓå) + ÒÑ «ÚÑÖ ÇáãÒíÏ» íÙåÑ İŞØ ÚäÏãÇ `riderShipmentTotal > count(riderShipments)` ÈäãØ `wire:click="$set('riderShipmentsPage', N); $wire.loadMoreRiderShipments()"` — ÊÍÊ 300 ÓØÑ Blade.
- **ÇáÊÑÌãÉ:** 5 ãİÇÊíÍ ×4 áÛÇÊ (`rider_stats_active/active_shipments/cod_due_today/summary` + `rider_shipments_load_more`)º fr ÈáÇ Ãí ŞæÓ ãİÑÏ (ÇÓÊÎÏã `aujourd’hui` U+2019).
- **ÇáÊÍŞŞ:** `php -l` ×5 äÙíİ + `view:cache` success + TrackingSearchFilterTest **15 äÇÌÍğÇ (61 ÊÃßíÏğÇ)** ãä Ïæä ÇäÍÏÇÑ — ãäåÇ **ÇÎÊÈÇÑÇä ÌÏíÏÇä**: (1) ÇÓÊÇÊ ÇáÊÈæíÈ ÊÚÑÖ ÇáÃÑŞÇã ÇáÊÌãíÚíÉ ÇáÕÍíÍÉ (ÚÏøÇÏ ÑÌáÇä/3 ÔÍäÇÊ/COD 5700 ãÚ ÇÓÊÈÚÇÏ ÇáãÓáóøãÉ Çáíæã)¡ (2) ÚÑÖ ÇáãÒíÏ íÌáÈ ÇáÕİÍÉ ÇáÊÇáíÉ (25 ÔÍäÉ ? 20 Ëã 25) ãä Ïæä ÅÚÇÏÉ ÖÈØ `selectedRiderId`. ÌæáÉ ãÌÇæÑÉ: RiderManagementTest (10) + NoestTrackingSyncTest (6) + NoestTrackingSyncServiceTest (6) + OrderEventLogVisibilityTest (11) ÎÖÑÇÁ (İÔá æÍíÏ ßÇä Şİá ãáİ ãÄŞÊ Windows `rename Access denied` ÚäÏ ÊÌãíÚ Blade ãÊæÇÒò — äÇÌÍ İæÑğÇ ÚäÏ ÇáÅÚÇÏÉ).
- **ÊÑßøÊå Úä ÇáäØÇŞ ÚãÏğÇ:** áÇ ÈÍË/İáÇÊÑ ÏÇÎáí Öãä ÔÍäÇÊ ÇáÑÌá ÇáãæÓøÚ (ãÄÌøá áÔÇÔÉ «ÑÍáÉ Çáíæã» İí E.2)º ÚÏã áãÓ `order-drawer.blade.php`.

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| D-fix ÊÌÇäÓ ÊÈæíÈ ÇáÑÌá (ÈÕÑí) | ? | partials/tracking-rider-stats (ÌÏíÏ) + tracking-rider-shipments (ÅÚÇÏÉ ßÊÇÈÉ) + tracking-rider-tab + tracking/index (ÇÓÊÇÊ ÊÌãíÚíÉ + ÊÑŞíã ÕİÍÇÊ `forPage` + loadMoreRiderShipments) + order_flow.php ×4 (+5 ãİÇÊíÍ) + TrackingSearchFilterTest (+2) | +2 Úáì ÇáÕİÍÉ + TrackingSearchFilterTest 15 (61) äÙíİÉ + ÌæáÉ ãÌÇæÑÉ 33 ÎÖÑÇÁ |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** 375/768/1440 — ÇáÊİÇİ ÈØÇŞÇÊ ÇáÇÓÊÇÊ ÈÔßá ÕÍíÍ Úáì ÇáãæÈÇíá¡ ãÍÇĞÇÉ ÃÚãÏÉ ÌÏæá ÇáÑÌá ãÚ ÌÏæá ÇáÔÑßÇÊ Úáì ÓØÍ ÇáãßÊÈ¡ Úãá ÒÑ ÇáäÓÎ¡ æÙåæÑ «ÚÑÖ ÇáãÒíÏ» İŞØ ÚäÏãÇ ÊÊÌÇæÒ ÔÍäÇÊ ÇáÑÌá 20.

---

## ÅÚÇÏÉ ÊÕãíã ÕİÍÉ ÇáÊÊÈÚ — ÊÑŞíÉ ÊÈæíÈí tracking Åáì ÔÈßÉ ãÊŞÏãÉ ÈäãØ `/orders` (ÓÈÊãÈÑ 2026) ?

**ÇáäØÇŞ:** ÊÑŞíÉ ßáÇ ÊÈæíÈí ÕİÍÉ `merchant/tracking` (carrier + rider) ãä ŞÇÆãÊíä ãäİÕáÊíä Åáì ÔÈßÉ ÈíÇäÇÊ æÇÍÏÉ ãÊØæÑÉ ÈäãØ ÕİÍÉ `/orders`: ßá ÇáØáÈíÇÊ İí ÔÈßÉ ãæÍøÏÉ¡ İáÊÑÉ ÈÑÌá ÇáÊæÕíá İí ÊÈæíÈ ÇáÑÌá¡ ÊÎÕíÕ/ÅÎİÇÁ/ÊÑÊíÈ ÇáÃÚãÏÉ ãÚ ÃÚãÏÉ ÅáÒÇãíÉ áÇ ÊõÎİì¡ İáÊÑÉ ãä ÑÄæÓ ÇáÃÚãÏÉ (status/provider/rider/city/amount/date) ÊÈŞì ãÚ ÇáİáÇÊÑ ÇáÌÇäÈíÉ ÇáãæÌæÏÉ¡ ÈØÇŞÇÊ ÅÍÕÇÆíÉ ÊÓÊÌíÈ ááİáÊÑÉ¡ æÊĞßøÑ ÂÎÑ ÊÈæíÈ ãİÊæÍ (ÚÈÑ ÊÎÒíä ÇáãÊÕİÍ) + ÊİÖíáÇÊ ÇáÃÚãÏÉ áßá ÊÈæíÈ ÚäÏ ÅÚÇÏÉ ÇáÊÍãíá.

- **`tracking/index.blade.php` (ÅÚÇÏÉ ßÊÇÈÉ PHP):** state ÌÏíÏ — İáÇÊÑ `amount_min/amount_max/city/rider` + `allCities`º ÃõÒíáÊ `selectedRiderId/riderShipments/riderShipmentTotal/riderShipmentsPage` æ`loadRiderOverview/toggleRider/mapRiderShipment/loadRiderShipments/loadMoreRiderShipments` (ÇÓÊõÈÏáÊ ÈİáÊÑ `rider` İí `filters`). `$baseTrackingQuery(bool $forAggregate)` ãÕÏÑ æÍíÏ ááÇÓÊÚáÇã (provider/search/statuses/date/amount/city/rider + äØÇŞ workflow) — ÊÓÊãÏ ãäå ÇáÔÈßÉ æÇáÇÓÊÇÊ æÚÏøÇÏÇÊ ÑÃÓ ÇáÃÚãÏÉ. `$loadTrackingStats` (carrier: active/delivered_today/returned_todayº rider: riderStatsActiveCount/ActiveShipments/CodDueToday + riderRiders ãä `groupBy delivery_rider_id` ãÑÔøÍÉ ÚÈÑ `DeliveryRiderService::listForStore`)º `$loadShipments` ÊÑŞíã ÕİÍÇÊ ÍŞíŞí (paginate) + ÎÑíØÉ Õİæİº ÇÓÊÇÊ ÊõÍÓÈ ŞÈá ÇáÊÑŞíã ãä äİÓ ÇáÇÓÊÚáÇã ÇáãİáÊÑ. `updated()` hooks áÜ `filters.date_from/date_to/amount_min/amount_max/city/rider` (ÈÜ `use function Livewire\Volt\updated;`) + `trackingTab` (íÍãá prefs ÇáÊÈæíÈ ? `saveColumnPreferences` ? `loadShipments`). closures: `trackingColumns` (carrier/provider+shipping_date¡ rider/delivery_rider+shipping_date)¡ `trackingDefaultOrder`¡ `trackingViewKey`¡ `getMembership`¡ `loadTrackingTabPreferences` (legacy `prefs_version!==1` ? defaultsº ÅÚÇÏÉ ÅÏÑÇÌ ÇáÅáÒÇãí İí ÇáãæÖÚ ÇáÇİÊÑÇÖíº tableStyle default/status)¡ `saveColumnPreferences` (updateOrCreate + ÅÚÇÏÉ ÅÏÑÇÌ ÇáÅáÒÇãí)¡ `openTableSettings/discardTableSettings/saveTableSettings/toggleDraftColumn/moveDraftColumn/reorderDraftColumns/resetColumns`¡ `loadRiderOptions`¡ `assignRider` (loadRiderOptions + loadShipments). `mount` íãáÃ providers/cities/riders.
- **partials ÇáÌÏíÏÉ ×3 + ÅÚÇÏÉ ßÊÇÈÉ `tracking-tabs`:** `tracking-table-header.blade.php` (ÎÑíØÉ headerFilterKeys city/total/status/provider/rider/date + ÔÇÑÉ İÚøÇá + ÍÏË `edz-filter-open`)¡ `tracking-filter-portal.blade.php` (dropdownPosition + ÃŞÓÇã ãÈæøÈÉ ÈÜ visibleColumns + ÚÏøÇÏÇÊ ÑíÏÑ ãä riderCounts)¡ `tracking-table-settings-modal.blade.php` (ãÍãí ÈÜ showTableSettings + ÊÈæíÈÇ columns/style + `orderColumnReorderDraft` drag/up/down + áæß ÇáÅáÒÇãí + footer reset/cancel/save + `@edz-modal-closed.window`). `tracking-tabs.blade.php` (ÅÚÇÏÉ ßÊÇÈÉ): ÊĞßøÑ ÂÎÑ ÊÈæíÈ ÚÈÑ **ÊÎÒíä ÇáãÊÕİÍ** `localStorage('edz-tracking-active-tab')` İŞØ — áÇ ÚãæÏ DB (`@click` íÍİÙ¡ `x-init` íÓÊÚíÏ ÈÜ `$wire.set` ÚäÏ ÇÎÊáÇİ ÇáŞíãÉ) — ÈÏá ÇáÇŞÊÑÇÍ ÇáÃÕáí `active_tab` İí DB (ãÑİæÖ ãä ÇáãÓÊÎÏã: áÇ ÍÇÌÉ áÅÖÇİÊå ááÊÑÍíá¡ æäõÍĞİ Çáãáİ ŞÈá ÊÔÛíáå).
- **`partials/tracking-list.blade.php` (ÅÚÇÏÉ ßÊÇÈÉ ßÇãáÉ):** ÌÏæá ÓØÍ ãßÊÈ ÈÃÚãÏÉ `visibleColumns` ÚÈÑ `@include` áßá ÚãæÏ + Õİæİ `$rowTint` (status: delivered?success¡ returned/cancelled/failed?danger) + ÎáÇíÇ @switch (number/customer/city/total/tracking_status pill/openStatusHistory/tracking_number copy?navigator.clipboard/provider/delivery_rider/shipping_date) + ÈØÇŞÇÊ ãæÈÇíá + ÊÑŞíã prev/next ÚÈÑ `pagination.*` + Õİ İÇÑÛ colspan=count+1.
- **`tracking-rider-tab.blade.php` (ÅÚÇÏÉ ßÊÇÈÉ):** rider-stats + ÓØÑ ãáÎÕ `rider_stats_summary` + toolbar + ÓØÑ count + `tracking-list` — ÈáÇ ÈØÇŞÇÊ ÑÌá ãæÓøÚÉ. ÍõĞİ `partials/tracking-rider-shipments.blade.php` + ÒÑ ÅÚÏÇÏÇÊ columns İí `tracking-toolbar` (ÃíŞæäÉ view-columns).
- **áÇ migration ÌÏíÏÉ:** ÇŞÊÑÇÍ `active_tab` (string nullable İí user_column_preferences) **ÃõáÛí** ÈØáÈ ÇáãÓÊÎÏã — ÇáÊÈæíÈ ÇáäÔØ íõÊĞßÑ ÚÈÑ ÊÎÒíä ÇáãÊÕİÍ İŞØ¡ æáÇ ÖÌÉ ÎÇÏã/ÊÑÍíá. (`2026_09_10_123500_add_active_tab_...` ÍõĞİ Çáãáİ ŞÈá ÊÔÛíáå — ÍÇáÉ Pending áã ÊõÔÛøá.)
- **ÇáÊÑÌãÉ:** `order_flow` ×4 áÛÇÊ +3 ãİÇÊíÍ (`filter_amount/amount_min_placeholder/amount_max_placeholder` ÈÚÏ `filter_date`).
- **ÇáÊÍŞŞ:** `php -l` äÙíİ + `view:cache` success + TrackingSearchFilterTest ÃÚíÏÊ ßÊÇÈÊåÇ ÈÇáßÇãá **20 äÇÌÍğÇ (89 ÊÃßíÏğÇ)** — ÇÓÊÈÏÇá ÇÎÊÈÇÑÇÊ ÇáÊæÓÚÉ/load-more ÈİáÊÑ rider æpagination prev/next¡ æÇÎÊÈÇÑÇÊ prefs (per view_key¡ ÅáÒÇãí áÇ íõÎİì¡ ÊÑÊíÈ íõÓÊÚÇÏ¡ tableStyle¡ æÇáÊÈæíÈ íõÊĞßÑ İí ÇáãÊÕİÍ áÇ İí DB ãÚ `Schema::hasColumn('active_tab')` false)¡ æÇÓÊÇÊ ÊÓÊÌíÈ ááİáÊÑÉ (provider + amount). ÌæáÉ ãÌÇæÑÉ: OrderIndexPreferencesTest (9) — 29 äÇÌÍğÇ (114 ÊÃßíÏğÇ). grep áÇ íÌÏ Ãí ãÑÌÚ ãÊÈŞòø áÜ `toggleRider/loadRiderShipments/loadMoreRiderShipments/selectedRiderId/riderShipmentsPage/tracking-rider-shipments/active_tab` İí ßæÏ Íí.
- **ÊÑßøÊ Úä ÇáäØÇŞ ÚãÏğÇ:** áÇ İÑÒ Õİæİ İí ÇáÔÈßÉ (äİÓ `/orders`º áÇ ãÓÍ ÖæÆí/ãßæøä barcode — ãÄÌá áÜ Phase 32)º áÇ ÓÑÏ ÑÌá ÏÇÎáí ãäİÕá (ÇáİáÊÑÉ ßÇİíÉ ááãÑÍáÉ ÇáÍÇáíÉ).

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÇÎÊÈÇÑÇÊ/ÊÃßíÏÇÊ |
|---|---|---|---|
| ÊÑŞíÉ ÇáÊÊÈÚ: ÔÈßÉ ãÊŞÏãÉ + prefs | ? | tracking/index (PHP rework) + partials tracking-list/table-header/filter-portal/table-settings-modal (ÌÏíÏÉ) + tracking-tabs (localStorage) + tracking-rider-tab (ÅÚÇÏÉ ßÊÇÈÉ) + ÍĞİ tracking-rider-shipments + tracking-toolbar (ÒÑ ÅÚÏÇÏÇÊ) + order_flow ×4 (+3) + TrackingSearchFilterTest (ÅÚÇÏÉ ßÊÇÈÉ ßÇãáÉ) | TrackingSearchFilterTest 20 (89) äÙíİÉ + ÌæáÉ ãÌÇæÑÉ (OrderIndexPreferences 24) — 29 (114) äÙíİÉ |

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** 375/768/1440 — ÚÑÖ ÇáÔÈßÉ ÇáãæÍøÏÉ İí ßáÇ ÇáÊÈæíÈíä¡ İáÇÊÑ ÑÄæÓ ÇáÃÚãÏÉ¡ modal ÅÚÏÇÏÇÊ ÇáÃÚãÏÉ (ÓÍÈ/ÊÑÊíÈ + ÃÚãÏÉ ÅáÒÇãíÉ ãŞíøÏÉ)¡ ÇÓÊÌÇÈÉ ÇáÇÓÊÇÊ ááİáÊÑÉ¡ æÇÓÊÑÌÇÚ ÂÎÑ ÊÈæíÈ+ÇáÃÚãÏÉ ÚäÏ ÅÚÇÏÉ ÇáÊÍãíá.

---

## ÇáãÑÍáÉ E — ÈæÇÈÉ ÑÌá ÇáÊæÕíá: ŞÑÇÑÇÊ ÇáãÓÊÎÏã ÇáãÚÊãÏÉ (ÓÈÊãÈÑ 2026) ??

**ØáÈ ÇáãÓÊÎÏã (äÕí¡ ÈÊÇÑíÎ Çáíæã):** áæÍÉ ÑÌá ÊæÕíá ÇÍÊÑÇİíÉ ÊäÇİÓíÉ ÈÓæŞ ÌÒÇÆÑí:
1. **ÅÍÕÇÁÇÊ ãÊŞÏãÉ æãÊØæÑÉ** İí áæÍÉ ÇáÑÌá (ÈØÇŞÇÊ¡ ÊŞÏã íæãí¡ COD¡ ÌÛÑÇİíÇ).
2. **ÊÍßã ÊİÕíáí İí ÍÇáÇÊ ÇáÊæÕíá ãÚ ãáÇÍÙÇÊ** — áßä **áÇ ÍĞİ æáÇ ÊÚÏíá** áÃí ÍÇáÉ/ãáÇÍÙÉ (append-only).
3. **ÇáÚÖæ ÇáãÇáß áÕáÇÍíÉ ÇáÊÊÈÚ** íÑì ÇáÊÊÈÚ İí äİÓ ÇáÕİÍÉ ÇáÍÇáíÉ æÊÈæíÈÇÊ ÇáÔÑßÇÊ ßãÇ åí ÇáÂä.
4. ÇáÑÌá íÑì **ØáÈíÇÊå Çáãßáİ ÈåÇ İŞØ** (áÇ íÑì ãÊÛíÑÇÊ ÇáãÊÌÑ).
5. ÇáãØáæÈ: ÊŞÏíã **ÎØÉ/ÇŞÊÑÇÍÇÊ ÇÍÊÑÇİíÉ ãÊŞÏãÉ ãäÇİÓÉ** ãæÌåÉ ááÓæŞ ÇáÌÒÇÆÑí ŞÈá ÇáÊäİíĞ.

**ÅÖÇİÉ áÏŞÉ ÇáÕáÇÍíÇÊ:** ÚÖæ ÇáİÑíŞ Ğæ ÇáÇØáÇÚ ÇáãŞíøÏ (staff/manager) íÑì İí ÊÈæíÈ ÇáÑÌá ÔÍäÇÊ **ÇáãÓäÏÉ Åáíå İŞØ** (`assigned_to_membership_id`) — äİÓ ŞÇÚÏÉ ÓÌá ÃÍÏÇË ÇáØáÈíÉ ÇáÍÇáíÉ. (ÓíõÑÇÚì İí F/G.)

> **? Êã (11 ÓÈÊãÈÑ 2026):** äØÇŞ ÊÈæíÈ ÇáÑÌá íõŞíøÏ ÇáÂä ÚÈÑ `StoreOrderPermissions::isRestrictedMembership()` (äİÓ ÍÏ `canViewOrderEventLog`: OWNER/ADMIN ÛíÑ ãŞíøÏíä¡ æÇáÈÇŞí ãŞíøÏ) ÏÇÎá `TrackingGridConcern::baseTrackingQuery` — íÛØí Õİæİ ÇáÔÈßÉ æÇáÅÍÕÇÁÇÊ ãÚğÇ (ãÕÏÑ æÇÍÏ)¡ ÈáÇ ÊÚÑøÖ áÊÈæíÈ ÇáÔÑßÇÊ Ãæ ÓáÉ ÇáãåãáÇÊ. ÊÜÛáŞ ÈäÏ **P1-6** ãä ÊŞÑíÑ ÇáÜ QA. ÃÏáÉ: 4 ÇÎÊÈÇÑÇÊ İí `TrackingSearchFilterTest` (staff: ÇáÔÈßÉ + ÇáÅÍÕÇÁÇÊ¡ manager: ÇáÔÈßÉ¡ owner: íÑì Çáßá) — **402 äÇÌÍğÇ (1555 ÊÃßíÏğÇ)¡ ÕİÑ ÇäÍÏÇÑ.**

**ÇáãØáæÈ ÇáÂä:** ÚÑÖ ÎØÉ ãÑÇÌÚÉ ááãæÇİŞÉ ŞÈá ßÊÇÈÉ ÇáßæÏ (ÇäÙÑ ÇáÑÓÇáÉ ÇáÊÇáíÉ İí ÇáÏÑÏÔÉ).

---

## ÏİÚÉ ÕİÍÉ ÇáÊÊÈÚ: İáÇÊÑ ÇáãÓäóÏ/ÇáãÄßöøÏ + ÚãæÏ ÇáæáÇíÉ + ãáÇÍÙÇÊ ÇáäÇŞá + ÅÌÑÇÁÇÊ ÇáÍĞİ/ÇáÅáÛÇÁ/ÇáÊÚÏíá (ÓÈÊãÈÑ 2026) ?

**ÈŞÑÇÑÇÊ ÇáãÓÊÎÏã ÇáãÚÊãÏÉ:** (1) ÒÑ ÇáÊÚÏíá íİÊÍ ãæÏÇá ÇáÊÚÏíá ÏÇÎá ÕİÍÉ ÇáÊÊÈÚ äİÓåÇ (ÈæÑÊ ãä orders — áÇ ÊæÌíå)º (2) ÚãæÏ «ÇáãáÇÍÙÇÊ» = ãáÇÍÙÉ ÔÑßÉ ÇáÔÍä API İí ÈæÈ ÃÈ (ÊÇÑíÎ + ãõÑÓöá + ãõÑÓöá ÌÏíÏ) ÈäİÓ `sendCarrierNote`º (3) ÈæÇÈÉ ÇáÊÚÏíá İí ÓíÇŞ ÇáÊÊÈÚ = ãäÚ ÇáÍÇáÇÊ ÇáäåÇÆíÉ İŞØ `delivered/returned` (ÇáÔÈßÉ ÊÚÑÖ ÍÇáÇÊ carrier İŞØ)º (4) ÅáÛÇÁ ÇáÅÑÓÇá íãÑ ÚÈÑ `OrderShippingGateway::cancel` (ÍÇáÇÊ `shipped/in_transit/out_for_delivery` İŞØ¡ ÍÇÑÓ ÅÖÇİí `isCarrierValidated`) — ÍĞİ ÓÌá ÇáÔÑßÉ ÚÈÑ API Ëã ÅÚÇÏÉ ÇáØáÈíÉ áÜ `confirmed` (launch leg ÈÚßÓ ãÚ RESTOREº `tracking_number=null` + `carrier_status='cancelled'` + `tracking_status=null`) — **áÇ ÍĞİ İÚáí ãä DB**¡ æÇáÍĞİ ÇáäÇÚã íÈŞì ÓáæßğÇ ŞÇÆãğÇ ááÒÑ ÇáËÇäí.

- **ÇáÎáİí:** `CarrierIntegrationContract::deleteOrder(string $number, array $credentials): array` + `NoestIntegrationAdapter::deleteOrder` (POST `/delete/order`¡ Bearer api_token + user_guid + tracking) + ãíÛÑíÔä `defines_capability`/`supports_order_delete` (noest?true) + `Carrier::capabilityList()['order_delete']` + `OrderShippingGateway::cancel()` (İÆÉ äØÇŞ ÇáÍÇáÉ¡ ÍĞİ ÖáÚ ÇáäÇŞá¡ `OrderService::revertTo` ? confirmed¡ ÊÑÇÌÚ/ÅÑÌÇÚ).
- **ÇáÕİÍÉ (`tracking/index.blade.php`):** `baseTrackingQuery` — eager loads `assignedMembership.user`/`confirmedByHistory.changedBy.user` + İáÇÊÑ `assigned_to` (assigned_to_membership_id) æ`confirmed_by` (whereHas confirmedByHistory.changedBy) + ÊÈæíÈ ÇáÑÌá `whereNotNull('delivery_rider_id')`º `loadShipments` — ÃÍÏË carrier-note æÇÍÏÉ (ÇÓÊÚáÇã æÇÍÏ `whereIn`/groupBy¡ ÈáÇ N+1) + Õİæİ `state/confirmed_by/assigned_to/latest_note/tracking_id/carrier_supports_api_notes/can_edit_order/can_cancel_shipment`º `shipmentCancelable` — flag/ÍÇÑÓ ãæÍøÏ (äØÇŞ ÇáÍÇáÉ + ÛíÑ ãõÕÇÏóŞ + carrier íÏÚã `order_delete` Ãæ ÑÌá ãõÚíóøä)º closures ãäÓæÎÉ ãä orders: `openEditModal` (ÈæÇÈÉ terminal) /`submitEdit` (ÈáÇ ÍÇÑÓ shipped) /`deleteOrder` /`cancelShipment` /`openShipmentNotes`º ÃÚãÏÉ ÌÏíÏÉ (ÊÑÊíÈ: number, customer, state, city, assigned_to, total, tracking_status, tracking_number, confirmed_by, notes, actions) + default order ÌÏíÏ.
- **Blades:** toolbar (selects `assigned_to`/`confirmed_by` ãÎİíÉ ÚäÏ ÙåæÑ ÇáÚãæÏ + chips)º table-header (ãİÇÊíÍ `assigned`/`confirmed`)º filter-portal (ŞÓãÇä ÌÏíÏÇä)º tracking-list (ÎáÇíÇ state/assigned_to/confirmed_by/notes + ŞÇÆãÉ ÅÌÑÇÁÇÊ ãäÓÏáÉ: Details/Edit/Cancel/Delete ÚÈÑ `EdzSwal`)º `tracking-notes-popup.blade.php` (ÌÏíÏ)º ÊÖãíä `order-form-modal` + `orders-product-picker` + Ôíã `openOrderDetails`.
- **ÇáÊÑÌãÇÊ ×4:** `order_flow`: cancel_shipment_title/confirm/shipment_cancelled/cancellation_status/cancellation_failed/already_validated/carrier_delete_not_supported/carrier_notes/no_carrier_notes/carrier_notes_unsupportedº `merchant_panel`: cannot_edit_terminal.
- **ÇáÃÏáÉ:** `tests/Feature/Merchant/TrackingGridBatchTest.php` (ÌÏíÏ¡ **10 äÇÌÍÉ / 40 ÊÃßíÏğÇ** — İáÇÊÑ assigned_to/confirmed_by¡ row-map ÇáÌÏíÏÉ¡ ãäÚ ÇáÊÚÏíá áäåÇíÉ ÇáØáÈíÉ¡ ÊÍãíá ÇáãæÏÇá¡ submitEdit íÍÏøË + íÚíÏ ÇáÍÓÇÈ¡ ÍĞİ äÇÚã + ÅİÑÇÛ ÇáÏÑÌ¡ ÅáÛÇÁ ÚÈÑ fake HTTP íŞÊá ÓÌá ÇáÔÑßÉ æíÚíÏ confirmed ãÚ Îáæ tracking¡ ÑİÖ ÈáÇ ŞÏÑÉ delete¡ ãäÚ ÇáãõÕÇÏóŞ) + ÊÍÏíË `TrackingSearchFilterTest` (ÊÈæíÈ ÇáÑÌá: ÔÍäÇÊ ÈÑÌá İŞØ ÚäÏ ÊÚííäåº ÊÑÊíÈ ÃÚãÏÉ ÇİÊÑÇÖí ÌÏíÏ) — **31 äÇÌÍğÇ (140 ÊÃßíÏğÇ)** İí Çáãáİøíäº `php -l` äÙíİ + `view:cache` success.

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** 375/768/1440 — İáÇÊÑÇ ÇáãÚíøä/ÇáãÄßöøÏ (ÔÑíØ + ÑÄæÓ)¡ ŞÇÆãÉ ÅÌÑÇÁÇÊ ÇáÕİ İí ÇáÌÏæá æÇáÈØÇŞÇÊ¡ ÈæÈ ÃÈ ÇáãáÇÍÙÇÊ¡ ãæÏÇá ÇáÊÚÏíá ÏÇÎá ÕİÍÉ ÇáÊÊÈÚ¡ æÊæÓÊåãÇ (ÊæÓÊ ÅáÛÇÁ + ÊæÓÊ ÚÏã ÏÚã ÇáäÇŞá ááÏÇÆÑÉ).

---

## ÏİÚÉ ÊÊÈøÚ: ÊÑÊíÈ ÇáÃÚãÏÉ ÇáÌÏíÏ + ÓáÉ ÇáãåãáÇÊ + ÇáãÒÇãäÉ ÇáÌãÇÚíÉ + æíÈ åæß ÇáÔÑßÇÊ + ØÈÇÚÉ ÇáãáÕŞ (ÓÈÊãÈÑ 2026) ?

**ÈŞÑÇÑÇÊ ÇáãÓÊÎÏã ÇáãÚÊãÏÉ:** (1) ÇáÃÚãÏÉ ÇáËáÇËÉ ÇáÌÏíÏÉ (ÇáæáÇíÉ/ÇáÍÇáÉ/ÇáäÇŞá Ãæ ÇáãæÕøá) ÃÕÈÍÊ ÅáÒÇãíÉ¡ æÊÑÊíÈåÇ ÇáÇİÊÑÇÖí ÇáÌÏíÏ 13 ÚãæÏğÇ ãÚ `prefsVersion = 2` ááÊåÌíÑ İíÒíÇÆíğÇº (2) «ÇáÍĞİ» ÇáäÇÚã íÊÍæøá áäŞáò Åáì ÓáÉ ÇáãåãáÇÊ ÈİÑÇÛ Öãä ÇáÍÇáÉ áãÑÉ æÇÍÏÉ æÇáÇÓÊÑÌÇÚ ãäåÇ¡ æÇáäåÇÆí ãÍÕæÑ ÈÜ `ORDER_DELETE`º (3) ÇáÓáÉ/ÇáÍĞİ ÇáäåÇÆí/ÇáÇÓÊÑÌÇÚ æÇáæíÈ åæß — ßáåÇ ÊÍÊ ÊÈæíÈ «ÇáÔÑßÇÊ» ĞÇÊå.

- **ÊÑÊíÈ ÇáÃÚãÏÉ (`tracking/index.blade.php`):** default = number ? tracking_number ? customer ? **state (ÅáÒÇãí)** ? city ? total ? tracking_status ? **provider (carrier) / delivery_rider (rider) (ÅáÒÇãí)** ? assigned_to ? confirmed_by ? notes ? shipping_date ? actionsº `trackingColumns`/`trackingDefaultOrder`/ãæÇÖÚ ÇáÅáÒÇãí İí restore/save ÃõÚíÏÊ áÊÊæÇİŞ¡ æ`prefsVersion = 2` íåÌøÑ ÇáŞÏíã ãÑÉ æÇÍÏÉ.
- **ÓáÉ ÇáãåãáÇÊ:** `deleteOrder` = äŞá äÇÚã (ÊæÓÊ `order_moved_to_trash` + ÅÛáÇŞ ÇáÏÑÌ Åä ßÇä ãİÊæÍğÇ)º ÈáÇ İÑÇÛ Ìäøí ÃËäÇÁ ÇáÓáÉº `trashCount` İí ÇáÍÇáÉ + `showTrash`º `restoreOrder`/`restoreAll` (ÊæÓÊ `orders_restored`) + `forceDeleteOrder`/`forceDeleteAll` ãÍÕæÑÉ ÈÜ `ORDER_DELETE`º ÊÑÊíÈ `$purgeOrderRows` = OrderTrackingHistory ? OrderEvent ? OrderStatusHistory ? OrderTracking ? OrderItem::forceDelete ? order->forceDeleteº ÈÇäÑ ÅÔÚÇÑí ÃÚáì ÇáÌÏæá İí æÖÚ ÇáÓáÉ + `toggleTrash` İí ÔÑíØ ÇáÃÏæÇÊ æÅÎİÇÁ ÇáÅÌÑÇÁÇÊ ÛíÑ ÇáãÊÚáŞÉ İí ÇÇáÓáÉ.
- **ÇáãÒÇãäÉ ÇáÌãÇÚíÉ (`$syncAllTracking`):** ÚÈÑ `StopdeskOfficeSync::resolve` + `NoestTrackingSyncService::apply` áßá ÊÊÈÚ ãİÊæÍ (order + ÊÇÑíÎ + activity) æÊæÓÊ ãáÎÕ `sync_all_tracking_result` ÈÜ `sync_updated/sync_failed/sync_no_open`.
- **æíÈ åæß ÇáÊæÕíá:** ãíÛÑíÔä `2026_09_11_200000_add_webhook_token_to_shipping_providers.php` (ÚãæÏÇ `webhook_token` uuid İÑíÏ nullable + `webhook_last_seen_at`º index Úáì `webhook_token`º fillable/casts İí `ShippingProvider`)º `DeliveryWebhookController@store` — íÍÏÏ ÇáÔÑßÉ ÈÇáÊæßä (íÌÈ `is_active`¡ æÅáÇ 404)¡ íÍÏøË last_seen¡ íÓÊÎÑÌ ÑŞã ÇáÊÊÈÚ (aliases: tracking_number/tracking/code/data.tracking_number)¡ íÄßøÏ OK Åä áã íæÌÏ ÊÊÈÚ (áÇ retry)¡ íØÈøÚ ÅÍÏÇË ÇáäÇŞá Åáì `normalizeEntry` (activity/events/OrderInfo/order_info/Õİ æÇÍÏ) æíØÈøŞ ÚÈÑ `app(NoestTrackingSyncService::class)->apply()`º ãÓÇÑ `POST /webhooks/delivery/{token}` ÈÇÓã `webhooks.delivery` ÈÜ throttle:120,1 ÎÇÑÌ Ãí ãÕÇÏŞÉ ãÊÌÑ.
- **æÇÌåÉ ÇáæíÈ åæß (`providers.blade.php`):** ßÇÑÊ áßá ÔÑßÉ — ÔÇÑÉ `webhook_ready/webhook_not_configured` + ßÊáÉ äÓÎ ááÊæßä + ÒÑ ÅÚÇÏÉ ÊæáíÏ ÈÂáíÉ uuid + ÓØÑ ÂÎÑ ÇÓÊŞÈÇá `webhook_last_seen` Ãæ ÒÑ ÊİÚíáº `$enableWebhook/$regenerateWebhook` ÊÍÊ ÍÇÑÓ `DELIVERY_PRICING_MANAGE` æÍŞá `carrier_id` ãØáæÈ (`$enableAll` íÈŞì áÊÚÈÆÉ ÇáÇÚÊãÇÏÇÊ İŞØ). ÃíŞæäÉ `link` ÛíÑ ãæÌæÏÉ İí `icon.blade.php` ? ÇÓÊõÎÏãÊ `external-link` (ÇáÎÑíØÉ ÊÍæí truck-x-mark/printer/arrow-path/shield-check/arrow-uturn-left/clipboard/external-link).
- **ØÈÇÚÉ ÇáãáÕŞ:** ? `CarrierIntegrationContract::getLabel(ShippingProvider, string $tracking)` (æíÊØáÈ ÊÚÈÆÉ ÇáÇÚÊãÇÏÇÊ ? `connection_missing_credentials`) + `NoestIntegrationAdapter::getLabel` (URL `/get/order/label?tracking=` ÈÊæßä ÇáÔÑßÉ¡ æÈáÇ Êæßä ok=false)º ? `DeliveryLabelController@show(Store $store, Request, string $tracking)` — ÍÇÑÓ `ORDER_VIEW`¡ íÓÊÑÌÚ ÇáÊÊÈÚ ÈÇáÔÑßÉ¡ íÚÑøİ ÇáãÍæøá¡ íÌáÈ URL ÇáãáÕŞ ÈÜ `Bearer` Ëã íÈËø ÇáÈÇíÊÇÊ inline (`Content-Disposition: inline; filename=label-{T.N.}`) — CVE ÓÏø ááÊæßä ÃãÇã ÇáãÊÕİÍº ? ãÓÇÑ `GET /merchant/{store:slug}/tracking/label/{tracking}` ÈÇÓã `merchant.tracking.label` İí Layer 3º ? æÇÌåÉ ÇáÊÊÈøÚ: `$openLabel` (carrier ÃæáğÇ: ÍÏË ãÊÕİÍ `open-label` + `window.open` ÈÈÑæßÓí) æ`$closeLabel` + ãæÏÇá `label-print-modal` (æÑŞÉ `#edz-label-sheet` + CSS ØÈÇÚÉ íÍÕÑ ÇáÑÄíÉ + ÔÑíØ ÇáÈÇÑßæÏ) + ãßæøä `x-edz.barcode` (Code128 ßÇãá: ÌÏæá ÇáÃäãÇØ¡ subset C ááÃÑŞÇã ÇáÒæÌíÉ¡ checksum mod 103¡ SVG `crispEdges`)º ? **ÎÏÚÉ Laravel 12 Positional Resolution** ÇßÊõÔİÊ ÃËäÇÁ ÇáÊÕÍíÍ: ãÚ `(Request $request, string $tracking)` æãÓÇÑíä¡ ÍŞä `Request` íÒíÍ `$tracking` İÇÓÊŞÈá ÓáóÛ ÇáãÊÌÑ ? ÇáÍá æÖÚ `Store $store` ÃæáğÇ İí ÇáÊæŞíÚ (ÇáäãæĞÌ íõÑÈØ ÈÇáŞíãÉ İíõÈŞì ÇáÊÑÊíÈ ÕÍíÍğÇ).
- **ÇáÊÑÌãÇÊ ×4 (ar/en/fr/es) `order_flow.php`:** trash_restored/trash_restore_all/permanent_delete_title/permanent_delete_confirm/order_moved_to_trash/orders_restored/delete_confirmation_title/delete_confirmation_message/delete_permanently/trash_count/trash_empty_trash_label+confirmation/all_orders_restored/sync_all_tracking/sync_all_tracking_result/sync_updated/sync_failed/sync_no_open/webhook_enable (+ ßá ãİÊÇÍ ÇáØÈÇÚÉ: print_label/label_print/close/label × ÇáãÎÊáİÉ) — æÌõãÚÊ ÚäÇÕÑ ÇáÓáÉ ÃÓİá `order_moved_to_trash` ÇáÍÇáí.
- **ÇáÃÏáÉ:** `tests/Feature/Merchant/TrackingTrashWebhookLabelTest.php` (**10 äÇÌÍÉ / 46 ÊÃßíÏğÇ** — ÊÑÊíÈ ÇáÃÚãÏÉ¡ ÓáÉ/ÇÓÊÑÌÇÚ¡ restoreAll¡ purge Õİæİ ßÇãáÉ ÈãäÊÌ/ãÊÛíÑ ÍŞíŞííä¡ ÑİÖ ÓáÉ/äåÇÆí ÈÏæä `ORDER_DELETE` (ÚÈÑ `canStore` + ÈŞÇÁ ÇáÍÇáÉ áÃä Livewire íÈÊáÚ HTTPException İí `call()` İí ÇáãÎÊÈÑ)¡ syncAllTracking¡ æíÈ åæß íØÈøŞ ÇáÅÍÏÇË ãËá ÇáÇŞÊÑÇÚ¡ ÑİÖ Êæßä ãÌåæá/ÔÑßÉ ÛíÑ äÔØÉ¡ `getLabel` ÈÇáÊæßä/ÈÏæäå¡ ÈÑæßÓí ÇáãáÕŞ íÈËø PDF ÈÃÔÑØÉ ÇáÅØÇÑ) + ÊÍÏíË `TrackingSearchFilterTest` (ÇáÊÑÊíÈ ÇáÌÏíÏ + ÇáÅáÒÇãí İí modal — ãÚ `total` ÅáÒÇãíğÇ ÇáÂä íÓÊåÏİ ÇáÇÎÊÈÇÑ ÅÎİÇÁ/ÊÍÑíß `notes`/`city` ÈÏá `total`)º ÌæáÉ ãÌÇæÑÉ: TrackingGridBatchTest + TrackingSearchFilterTest + TrackingStatusHistoryPopupTest + OrdersTrackingColumnTest (**38 äÇÌÍğÇ ÈÚÏ ÅÚÇÏÉò áÇÍŞÉ**) æ 6 ÓæíÊÇÊ ÔÍä/ÊÊÈøÚ (OrderTracking/BulkSendCarrierGrouping/DirectSendConfirmedOrder/NoestIntegration/OrderShippingProviderColumn/DeliverySettings) **54 äÇÌÍÉ (236 ÊÃßíÏğÇ)** — ÕİÑ ÇäÍÏÇÑº `php -l` äÙíİ Úáì ÇáãáİÇÊ ÇáãÊÛíÑÉ + `migrate --force` äÇÌÍ ááãíÛÑíÔäÇÊ ÇáËáÇË (ÓáÉ/seed NOEST + ÇáæíÈ åæß).**ÅÕáÇÍ** İÑÏí unrelated: İÔá æÍíÏ İí ÌæáÉ ßÇäÊ Şİá ãáİ Windows ãÄŞÊ (`rename Access denied`) — äÌÍ İæÑğÇ ÚäÏ ÇáÅÚÇÏÉ.

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** 375/768/1440 — ÊÑÊíÈ ÇáÃÚãÏÉ ÇáÌÏíÏ ãÚ ÇáÅáÒÇãí¡ ÓáÉ ÇáãåãáÇÊ (ÈÇäÑ/ÊæÓÊÓ/ÇáÇÓÊÑÌÇÚ/ÇáäåÇÆí)¡ ÒÑ ÇáãÒÇãäÉ ÇáÌãÇÚíÉ¡ ßÇÑÊ ÇáæíÈ åæß İí ÇáÅÚÏÇÏÇÊ¡ æÊÏİŞ ÇáØÈÇÚÉ ÇáßÇãá (ŞÇÆãÉ Õİ ? İÊÍ ãáÕŞ ÇáÔÑßÉ İí ÊÈæíÈ Ãæ ãáÕŞäÇ ÈÈÇÑßæÏ + ØÈÇÚÉ).

---

## ÏİÚÉ ÕİÍÉ ÇáÊÊÈÚ: ÓáÇÊ ãäİÕáÉ áßá ÊÈæíÈ + ÑŞã ÊÊÈÚ ÇáÑÌá + äÙÇİÉ ÇáÊÑãíÒ + æŞÊ ÇáÊÇÑíÎ (ÓÈÊãÈÑ 2026) ?

**ÈŞÑÇÑÇÊ ÇáãÓÊÎÏã ÇáãÚÊãÏÉ ÃËäÇÁ ÇáÊäİíĞ:** ÓáÉ ÇáãåãáÇÊ ÕÇÑÊ **áßá ÊÈæíÈ** İí ÇáÌåÉ ÇáãŞÇÈáÉ (`ms-auto`) ãÚ ÒÑ «ÇáÑÌæÚ» ÈÏá ÇáÃÍãÑ — ÒÑ ÇáÓáÉ ÇäÊŞá ãä åíÏÑ ÇáÕİÍÉ Åáì `tracking-tabs` æíÊÈÏá ÚäÏ ÇáÊİÚíáº äØÇŞ ÇáÓáÉ İí `baseTrackingQuery`/`trashCount`/`restoreAll`/`forceDeleteAll` = carrier `whereNotNull('shipping_provider_id')` / rider `whereNotNull('delivery_rider_id')`º ŞæÇÆã ÅÌÑÇÁÇÊ ÇáÕİ ÃÕÈÍÊ `position:fixed` ÈİáúÈ ááÃÚáì ŞÑÈ ÃÓİá ÇáÔÇÔÉ (áã ÊóÚõÏ ÊõŞÕ ÏÇÎá `overflow-hidden` ááßÇÑÏ)º ÑŞã ÊÊÈÚ ÇáÑÌá íõæáóøÏ İæÑğÇ ÚäÏ ÇáÅÑÓÇáº ÇáßÑæä áÇ íáãÓ ÇáÔÑßÇÊ ĞÇÊ ÇáæíÈ åæßº æÇáÓÈÈ ÇáÌĞÑí áÑãæÒ «â€”» ÇáãÚØæÈÉ åæ **Êáİ UTF-8 İí ÇáÓæÑÓ äİÓå** æáíÓ ÊÔİíÑ ÇáãÊÕİÍ.

- **äÙÇİÉ ÇáÊÑãíÒ:** `tracking-list.blade.php` — ÇÓÊÈÏÇá ÍÑİí `â€”`?`—` æ`â€¢`?`•` (ÈãÇ İíåÇ ÃÚãÏÉ `notes` æ`—`/`•` İí ÇáæÇÌåÉ). **ÈŞí Êáİ ããÇËá ÛíÑ ãõÈáóøÛ Úäå İí `orders/partials/order-form-modal.blade.php`** (ÇáÃÓØÑ 20/46/99/208/319) — ÎÇÑÌ åĞå ÇáÏİÚÉ¡ íõÕáóÍ ãÊì ØáÈ ÇáãÓÊÎÏã.
- **ÚãæÏ ÇáÊÇÑíÎ:** desktop + ÈØÇŞÉ ÇáãæÈÇíá íÚÑÖÇä `Y-m-d H:i` (ÊÇÑíÎ+æŞÊ ßÇãáÇä).
- **ÔÇÑÉ äæÚ ÇáÊæÕíá HM/SD:** ÚãæÏ `tracking_number` (desktop) æãæÈÇíá — ÑãæÒ ÈÌåÉ ãŞÇÈáÉ (`ms-auto`): `HM` (muted) / `SD` (accent) ÚÈÑ `'delivery_type' => $order->delivery_type` İí ÎÑíØÉ loadShipments.
- **ÒÑ ÇáäÓÎ:** `truncate max-w-[7rem] group-hover:max-w-none` — íÙåÑ ÑŞã ÇáÊÊÈÚ ßÇãáğÇ ÚäÏ ÇáåæİÑ İŞØ.
- **ÇáŞæÇÆã:** desktop+ãæÈÇíá `x-ref="menu"` + `EdzMenu.position($el, $refs.menu)` ÇáÌÏíÏÉ İí ÓßÑÈÊ ÇáÕİÍÉ (fixed + Ãíãä + İáúÈ: ÊİÊÍ ÃÚáì ÇáÕİ ÚäÏ ÖíŞ ÇáãÓÇÍÉ ÇáÓİáíÉ).
- **ÇáÓáÉ áßá ÊÈæíÈ:** `tracking-tabs` (ÒÑ + ÚÏÇÏ + «ÇáÑÌæÚ» ÈÃíŞæäÉ arrow-left æ`text-accent-600`)º `tracking-trash-banner` ÌÏíÏ ÈÊæßäÒ ÏÇßäÉ `bg-danger-surface/text-danger-fg/border-danger-border` (ÇáÎÇãõø `danger-50/200/700` áÇ íÊßíİ ãÚ ÇáÏÇÑß)º `tracking-rider-tab` ÈİÑÚ ÓáÉ + ÔÑíØ ÇáÈÍË (ÊßÇİÄ ãÚ carrier)º `updated('trackingTab')` íÚíÏ `showTrash=false`.
- **ÑŞã ÊÊÈÚ ÑÌá ÇáÊæÕíá:** İí `$assignRider` — `OrderTrackingService::ensureRiderTracking($order, $number)` (ÌÏíÏ): Åä æõÌÏ ÊÊÈÚ ãİÊæÍ íõãáÃ ÑŞãå ÇáİÇÑÛ İŞØ¡ æÅáÇ `startShipment` ÌÏíÏº ÇáÑŞã = `{HM|SD}-{STR(RANDOM 8)}` ÍÓÈ `delivery_type` ãÚ İÍÕ ÊİÑÏ İí ÇáãÊÌÑ (ÍáŞÉ do/while) — ÇÊÌÇå ÇáÑÌá áÇ íõÓÈóÑ (ÓÌá `dueTrackings` ÈáÇ provider).
- **ÇáßÑæä/ÇáæíÈ åæß:** `SyncNoestTrackingJob::handle` + `routes/console.php` íÖíİÇä `whereNull('webhook_token')` (ÇáÔÑßÇÊ ĞÇÊ ÇáæíÈ åæß ÊÏİÚ ÈäİÓåÇº ÇáÇŞÊÑÇÚ áåÇ İŞØ ÈáÇ æíÈ åæß).
- **ãÒÇãäÉ ÈáÇ İÑÇÛ:** `NoestTrackingSyncService::apply` — ÚäÏãÇ áÇ ÊõØÇÈöŞ ÇáÃÍÏÇË ÍÇáÉ æÇáÕİ ÈáÇ ÍÇáÉ ÓÇÈŞÉ: íßÊÈ `IN_TRANSIT` + ÓÌá ÍÇáÉ `fallback_in_transit` ÈÏá ÊÑß «—» (idempotent ÈáÇ ÓÌá ãßÑÑ).
- **ÇáÊÑÌãÇÊ:** `order_flow` ×4 áÛÇÊ: `back_from_trash` / `delivery_type_home` / `delivery_type_stopdesk`.
- **ÇáÃÏáÉ:** `TrackingTrashWebhookLabelTest` 10 äÇÌÍ + `TrackingSearchFilterTest` 24 (+3 ÌÏíÏ: ÊæáíÏ ÑŞã HM¡ ÈÇÏÆÉ SD áÜ stopdesk¡ æÚÏã ÊßÑÇÑ leg ÚäÏ ÅÚÇÏÉ ÇáÊÚííä) + `NoestTrackingSyncTest` 7 (+1: ÇáÌæÈ íÊÎØøì ÔÑßÉ ÇáæíÈ åæß `Http::assertNothingSent`) + `NoestTrackingSyncServiceTest` 7 (+1: fallback IN_TRANSIT Úáì Õİ ÈáÇ ÍÇáÉ) — **67 äÇÌÍğÇ ÈáÇ ÇäÍÏÇÑ**º `php -l` äÙíİ ×5 + `view:cache` success.

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** 375/768/1440 — ÒÑ ÇáÓáÉ/ÇáÑÌæÚ İí ÇáÊÈæíÈÇÊ¡ ÇáŞæÇÆã ÇáãÚáøŞÉ ÈİáúÈ ÇáÃÚáì İí ÂÎÑ Õİ¡ ÔÇÑÉ HM/SD + ÑŞã ÇáÊÊÈÚ ÇáããÊÏ ÚäÏ ÇáåæİÑ¡ ÚãæÏ ÇáÊÇÑíÎ+ÇáæŞÊ¡ ßÇÑÊ ÓáÉ ÇáÑÌá ÈÈÍËå¡ æÑŞã ÊÊÈÚ ÇáÑÌá (HM/SD-XXXXXXXX) İí ÏÑÌ ÇáØáÈíÉ æØÈÇÚÉ ÇáãáÕŞ.

---

## ÏİÚÉ ÕİÍÉ ÇáÊÊÈÚ (1/5): ÇáÌÏæá ÇáŞÇÈá ááÓßÑæá + ŞæÇÆã ÇáÕİ ÇáãËÈÊÉ + ÑÓÇáÉ ÇáäÓÎ + äÙÇİÉ ÇáÊÑãíÒ (ÓÈÊãÈÑ 2026) ?

**ÈŞÑÇÑÇÊ ÇáãÓÊÎÏã ÇáãÚÊãÏÉ:** ÊäİíĞ ÇáÊÍÓíäÇÊ **ÈóÏúİÚÇÊ** ÊõÚÑÖ ŞÈá ßá ÏİÚÉº Êæßä ÇáæíÈ åæß íäÊŞá áÊÑæíÓÉ `X-Delivery-Token` ãÚ `?token=` ÇÍÊíÇØíğÇ (ÏİÚÉ 2)º `thead` áÇÕŞ ãÚ ÓßÑæá ÚãæÏí (åĞå ÇáÏİÚÉ)º ÇáåíßáÉ traits ÈÜ closures ÑİíÚÉ (ÏİÚÉ 4).

- **ÇáÌÏæá:** ÛáÇİ new = `relative` ? `overflow-x-auto max-h-[calc(100vh-475px)] overflow-y-auto edz-scroll` (ÓßÑæá ÃİŞí+ÚãæÏí ÏÇÎá ÇáßÇÑÏ)º ÇáÌÏæá `w-max min-w-full` (ÇáÃÚãÏÉ ÊÍÊİÙ ÈÚÑÖåÇ ÇáØÈíÚí æÇáÓßÑæá íÃßá ÇáÒÇÆÏ)º `thead` = `sticky top-0 z-10 bg-surface [&_th]:bg-surface` (åíÏÑ ËÇÈÊ ÃËäÇÁ ÇáÓßÑæá ÇáÚãæÏí).
- **ŞæÇÆã ÇáÕİ:** ãßæøä Alpine ÚÇã **`edzRowMenu`** (`resources/js/components/edz-row-menu.js` — ÌÏíÏ¡ n=224/280¡ `x-ref="trigger"`¡ ãæÈÇíá <640 = æÑŞÉ ÓİáíÉ¡ æÅáÇ fixed ÈİáúÈ ááÃÚáì/Ãíãä ãÚ clamppping) + ÊÓÌíá İí `panel.js` + Êã ÍĞİ ÓßÑÈÊ `window.EdzMenu` ÇáãÖãøä ãä `index.blade.php`º desktop æóãæÈÇíá íóÓÎÏãÇä `x-edz.mobile-bottom-sheet` (scrim + áæÍÉ ãæÍÏÉ) ÈÏá `x-ref="menu"` ÇáÎÇã — ÇáŞÇÆãÉ ÊÙåÑ **İæŞ ÇáÕİ** ÏÇÆãğÇ.
- **ÑÓÇáÉ ÇáäÓÎ:** ÇáÓÈÈ ÇáÌĞÑí: `EdzSwal.toast` ÛíÑ ãæÌæÏ ÅØáÇŞğÇ İí `swal.js` (íæÌÏ `success/error/...`) — ÇÓÊõÈÏá ÈÜ `EdzSwal.success('', copy_done)` İí ÇáãæÇÖÚ ÇáËáÇËÉ (tracking-list:178/265 + order-drawer:47).
- **äÙÇİÉ ÇáÊÑãíÒ:** `orders/partials/order-form-modal.blade.php` — Êáİ UTF-8 ÇáãõÈŞøì (ÃÓØÑ 20/46/94/99/208/319: `â€”`, `â†’` ? `—`, `?`).
- **ÇáÃÏáÉ:** `TrackingTrashWebhookLabelTest` 10 äÇÌÍ + `TrackingSearchFilterTest` 24 äÇÌÍ (9+ÏõÑÌÉ [145 ÊÃßíÏğÇ]) — Ãæá ÌæáÉ ÇÕØÏãÊ ÈŞİá ãáİ Windows ãÄŞÊ (rename Access denied) æäÌÍÊ ÚäÏ ÇáÅÚÇÏÉº `view:cache` + `npm run build` äÙíİÇä.

## ÏİÚÉ ÕİÍÉ ÇáÊÊÈÚ (2/5): æíÈ åæß áßá Ïæãíä + áßá ÔÑßÉ (ÓÈÊãÈÑ 2026) ?

**ÍÓÈ ŞÑÇÑ ÇáãÓÊÎÏã:** ÇáÊæßä íäÊŞá ãä ÇáãÓÇÑ Åáì ÊÑæíÓÉ `X-Delivery-Token` ãÚ ÇÍÊíÇØí `?token=`.

- **ÇáŞÑÇÑ ÇáÊÕãíãí:** ÇÓÊõÎÏã ÚãæÏ `shipping_providers.code` ÇáãæÌæÏ (`noest`) ßãÚÑøİ ÇáÔÑßÉ İí ÇáÑÇÈØ — áÇ ÍÇÌÉ áãíÛÑíÔä slug (ÇáÜ `code` ÈáÇ İåÑÓ İÑíÏ¡ æãßÑÑ Èíä ÇáãÊÇÌÑ ÈÔßá ãŞÕæÏº ÇáÊãííÒ ÈÇáÊæßä).
- **ÇáãÓÇÑ:** `POST /api/webhooks/delivery/{provider}` ÈÇÓã `webhooks.delivery` (ÈÏá `/{token}`) — ÑÇÈØ ËÇÈÊ áßá ÔÑßÉ áßá Ïæãíä (`route()` íÈäí host ÊáŞÇÆíğÇ ãä ÇáÏæãíä ÇáÍÇáí).
- **`DeliveryWebhookController::resolveProvider`:** Êæßä ãä `X-Delivery-Token` Ëã `?token=` ? `where code + webhook_token`º ÈáÇ Êæßä ? ÇáÜ segment äİÓå åæ ÇáÓÑ (ÇáäãØ ÇáŞÏíã) ? `where webhook_token = segment`º íáÒã `is_active` İí ÇáÍÇáÊíä (404 æÅáÇ).
- **ßÇÑÊ ÇáÅÚÏÇÏÇÊ** (`providers.blade.php`): íÚÑÖ ÇáÑÇÈØ ÇáÃÓÇÓí `/webhooks/delivery/noest` + Õİ Êæßä ãäİÕá (äÓÎ) + ÊáãíÍ `X-Delivery-Token`/`?token=`º ÒÑ ÇáäÓÎ íäÓÎ ÇáÑÇÈØ ÇáßÇãá ÈÜ `?token=` (íÚãá İæÑğÇ æíÎÏã ÇáäÇŞá ÇáĞí áÇ íÏÚã ÇáÊÑæíÓÇÊ)º ÅÚÇÏÉ ÇáÊæáíÏ ÊÛíøÑ ÇáÊæßä «İŞØ» ãÚ ÈŞÇÁ ÇáÑÇÈØ ËÇÈÊğÇ.
- **ÇáÊÑÌãÇÊ ×4:** ãİÇÊíÍ ÌÏíÏÉ `webhook_token_label` / `webhook_token_copy` / `webhook_header_hint` + ÊÍÏíË Şíã `webhook_token_regenerate` æ`webhook_regenerated` (Êæßä áÇ ÑÇÈØ).
- **ÇáÃÏáÉ:** `TrackingTrashWebhookLabelTest` ? **12 äÇÌÍÉ / 52 ÊÃßíÏğÇ** (+3: `?token=` fallback¡ legacy token-in-path¡ ÑİÖ code ãÌåæá/Êæßä ãÌåæá/ÔÑßÉ ÛíÑ äÔØÉ Úáì ÇáÔßá ÇáÃÓÇÓí) + ÌæáÉ ÌæÇÑ: StopdeskSyncUiTest + NoestTrackingSyncTest + NoestIntegrationTest **22 äÇÌÍÉ / 97 ÊÃßíÏğÇ** — ÕİÑ ÇäÍÏÇÑº `php -l` ×3 + `view:cache` äÙíİÇä.

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** ßÇÑÊ ÇáæíÈ åæß İí ÅÚÏÇÏÇÊ ÇáÊæÕíá (ÑÇÈØ `.../delivery/noest` + Õİ ÇáÊæßä + ÇáÊáãíÍ) æÅÚÇÏÉ ÊæáíÏ ÇáÊæßä (ÇáÑÇÈØ ËÇÈÊ). **ÇáÏİÚÉ 3 (ÇáÊÇáí):** ÇáÍĞİ ÇáäåÇÆí íÍĞİ ÚäÏ ÔÑßÉ ÇáÊæÕíá (`OrderShippingGateway::deleteAtCarrier`).

## ÏİÚÉ ÕİÍÉ ÇáÊÊÈÚ (3/5): ÇáÍĞİ ÇáäåÇÆí íÍĞİ ÃíÖğÇ ÚäÏ ÔÑßÉ ÇáÊæÕíá (ÓÈÊãÈÑ 2026) ?

**ÍÓÈ ŞÑÇÑ ÇáãÓÊÎÏã:** ÇáÍĞİ ÇáäåÇÆí ááØáÈíÉ ãä ÓáÉ ÇáãåãáÇÊ íÍĞİ ÇáÔÍäÉ ÛíÑ ÇáãõÊÍŞóøŞ ãäåÇ ÚäÏ ÇáäÇŞá ÃæáğÇ (ÈáÇ ÍÌÈ ÇáÍĞİ ÇáãÍáí ÃÈÏğÇ).

- **`OrderShippingGateway::deleteAtCarrier(Order $order): array`** (ÌÏíÏ) — áÇ íÑãí ÃÈÏğÇº íõÚíÏ ãÕİæİÉ `ok`/`skipped` ÈÃÓÈÇÈ ÕÑíÍÉ (`no_provider`/`no_tracking`/`validated`/`unsupported`) Ãæ `ok`/`message`/`error`º íÓÌøá İÔá ÇáäÇŞá ÚÈÑ `Log::warning`. íÓÊÎÏã `OrderTrackingService::currentTracking` + `config('delivery.carrier_integrations.{code}')` — ÈáÇ ÊÑãíÒ ÕÚÈ áäÇŞá æÇÍÏ.
- **ÇáÓÍÈ:** ÅÌÑÇÁÇ `$forceDeleteOrder` æ`$forceDeleteAll` İí `tracking/index.blade.php` íÓÊÏÚíÇä `app(OrderShippingGateway::class)->deleteAtCarrier($order)` ŞÈá `purgeOrderRows` (ÇáİÍÕ `isCarrierValidated()` íÊÎØøì äÇŞá ÇáÔÍäÉ ÇáãÊÍŞóøŞ ãäåÇ ÊáŞÇÆíğÇ).
- **ÇÎÊÈÇÑÇÊ:** ÃõÖíİ `Http::fake` áãÓÇÑ ÇáİÔá İí ÇÎÊÈÇÑ ÇáÍĞİ ÇáäåÇÆí ÇáŞÇÆã + ÇÎÊÈÇÑÇä ÌÏíÏÇä: «íõÍĞİ ÇáÔÍäÉ ÛíÑ ÇáãÊÍŞóøŞ ãäåÇ ÚäÏ ÇáäÇŞá ÃæáğÇ» (íÄßÏ `assertSent` áÜ `/delete/order` ÈÜ `tracking=TRK-CDEL-1`) æ«íÊÎØì ÍĞİ ÇáäÇŞá áÔÍäÉ ãõÊÍŞóøŞ ãäåÇ» (`assertNothingSent`). **14 äÇÌÍÉ / 56 ÊÃßíÏğÇ.**
- `php -l` + `view:cache` äÙíİÇä.

## ÏİÚÉ ÕİÍÉ ÇáÊÊÈÚ (4/5): åíßáÉ `index.blade.php` — äŞá ÇáÜ 68 closure Åáì 5 traits (ÓÈÊãÈÑ 2026) ?

**ÍÓÈ ŞÑÇÑ ÇáãÓÊÎÏã:** ÇáåíßáÉ ÚÈÑ traits (ÇáÜ `state()`/`updated()`/`mount()` ÊÈŞì İí ÇáÜ bladeº ÇáØÑÇÆÒ ÊõäŞá ßÊæÇÈÚ ÍŞíŞíÉ ÈäİÓ ÇáÃÓãÇÁ İÊÈŞì ÇÓÊÏÚÇÁÇÊ ÇáŞÇáÈ `$wire.*`/`$this->*()` ßãÇ åí ÊãÇãğÇ).

- **5 traits ÌÏíÏÉ ÊÍÊ `app/Livewire/Concerns/`** (ãËá `HasOrderProductPicker` ÇáŞÇÆã):
  - `TrackingGridConcern` — `baseTrackingQuery`/`loadTrackingStats`/`loadShipments`/`refresh`/`clearFilters`/`setFilter`/`toggleTrackingStatus`/`nextPage`/`previousPage`/`shipmentCancelable`.
  - `TrackingColumnConcern` — `trackingColumns`/`trackingDefaultOrder`/`trackingViewKey`/`getMembership`/`loadTrackingTabPreferences`/`saveColumnPreferences`/`openTableSettings`/`discardTableSettings`/`saveTableSettings`/`toggleDraftColumn`/`moveDraftColumn`/`reorderDraftColumns`/`resetColumns`/`loadRiderOptions`.
  - `TrackingDrawerConcern` — `loadDrawerHistories`/`openDrawer`/`closeDrawer`/`openStatusHistory`/`closeStatusHistory`/`syncTracking`/`syncAllTracking`/`sendCarrierNote`/`openShipmentNotes`/`closeShipmentNotes`/`openOrderDetails`/`openLabel`/`closeLabel`/`cancelShipment`.
  - `TrackingRiderFormConcern` — `generateRiderTrackingNumber`/`assignRider`/`storeDefaultProviderId`/`formShipmentTypeOptions`/ÓáÓáÉ ÇáãÏä æÇáãßÇÊÈ (`cityOptionsFor`/`loadFormCitiesLazy`/`loadCities`/`officeOptionsFor`/`loadFormOfficesLazy`/`rebuildFormOffices`/`loadFormOffices`/`providerOfficeStates`/`providerHomeStates`/`homeCoveredCityIds`/`loadFormScope`/`releaseStaleDestination`/`applyProviderScope`/`changeDeliveryType`/`refreshFormOffices`)/`refreshFormDuplicateWarnings`/`recalculateOrderShipping`/`openEditModal`/`submitEdit`.
  - `TrackingTrashConcern` — `deleteOrder`/`toggleTrash`/`restoreOrder`/`restoreAll`/`purgeOrderRows`/`forceDeleteOrder`/`forceDeleteAll`.
- **ÇáÊäİíĞ:** ÇáÃÌÓÇÏ ãäŞæáÉ ÍÑİíğÇ (äİÓ ÇáÔÑæØ æÇáãİÇÊíÍ æÇáäÕæÕ) ãä ÇáÜ closures Åáì ÊæÇÈÚ ÚÇãÉ ÈäİÓ ÇáÃÓãÇÁ æÇáÊæŞíÚÇÊº ÇÓÊõÈÏá ÑÃÓ ÇáÜ blade (13 `use` ßáÇÓğÇ ÒÇÆÏÉ + `uses([HasOrderProductPicker::class])`) ÈßÊáÉ ÎİíİÉ ÊÓÌøá ÇáÜ 6 traits İí `uses([...])` æÃõÚíÏ `use StorePermissionEnum`/`use Order` áÃä `mount()` íÓÊÎÏãåãÇº ßÊáÉ «ÇáÜ 68 closure» ÍõĞİÊ ãä ÇáÜ blade ÈÍİÙ `state()` æ`updated()` æ`mount()` ßãÇ åãÇ — **2355 ÓØÑğÇ ? 304 ÓØÑğÇ** ãÚ ÈŞÇÁ ÇáŞÇáÈ/ÇáÌÒÆíÇÊ ÈáÇ ÊÛííÑ.
- **ÇáÊÍŞŞ ãä İÌæÇÊ ÇáÓØÍ:** ßá ÃÓãÇÁ `$wire.*` æ`$this->method()` İí partials ÕİÍÉ ÇáÊÊÈÚ + `order-form-modal`/`orders-product-picker` ÅãøÇ ãä ÇáÜ traits ÇáÎãÓÉ Ãæ `HasOrderProductPicker` (áÇ ÇÓã ãİŞæÏ).
- **ÇáÃÏáÉ:** `TrackingTrashWebhookLabelTest` **14 äÇÌÍÉ / 56 ÊÃßíÏğÇ** + `TrackingSearchFilterTest` **23 äÇÌÍÉ / 100 ÊÃßíÏğÇ** (37/156) — ÕİÑ ÇäÍÏÇÑº `php -l` ×6 + `view:cache` äÙíİÇä.

> **ÇáÏİÚÉ 5 (ÊÇáíÉ):** ÊæáíÈ ÊÚáíãí ÇÍÊÑÇİí Èãßæøä `x-edz.tooltip` Úáì ÚäÇÕÑ `title` İí ÕİÍÉ ÇáÊÊÈÚ.

## ÏİÚÉ ÕİÍÉ ÇáÊÊÈÚ (5/5): ÊæáíÈ ÊÚáíãí ÇÍÊÑÇİí `x-edz.tooltip` (ÓÈÊãÈÑ 2026) ?

**ÇáãæÇÕİÇÊ ÇáãÚÊãÏÉ (Apple):** ŞÇáÈ ÚÇÆã Èáæä ÇáÍÈÑ ÈÔİÇİíÉ 92% + `backdrop-blur` + ÊÃÎíÑ ÙåæÑ ~400ms + ãÎİí ßáíğÇ Úáì ÃÌåÒÉ ÇááãÓ/ÇáãÇæÓ ÇáÎÔä.

- **Çáãßæøä ÇáÌÏíÏ `resources/views/components/edz/tooltip.blade.php`:** `<x-edz.tooltip label="..." side="top|bottom" maxWidth="...">` íáÊİ Íæá ÇáÚäÕÑ æíõÖíİ İŞÇÚÉ `role="tooltip"`º ÈáÇ label íõãÑóøÑ ÇáÜ slot ßãÇ åæ (áÇ ÔíÁ íÙåÑ). ÇáæÓã `display:inline-flex` íáÊİ Íæá Ãí ÚäÕÑ (ÒÑ/ÔÇÑÉ) Ïæä ßÓÑ ÇáÊÎØíØº `max-width` ÚÈÑ ãÊÛíÑ CSS Úáì ÇáÛáÇİ.
- **ÇáäãØ `resources/css/components/_tooltip.scss`** (ãõÓÌóøá İí `_index.scss`): ÇáİŞÇÚÉ `position:fixed` ÈãÓÊæì `--edz-z-popover` ? ÊåÑÈ ãä ÍÇæíÉ ÇáÓßÑæá ÇáÎÇÕÉ ÈÇáÌÏæá (`overflow-x-auto`) ÈáÇ ŞÕøº ÎáİíÉ ink rgba(17,24,39,.92) + `backdrop-filter: saturate(180%) blur(4px)` + äÓÎÉ ÏÇßäÉ ÃİÊÍº `pointer-events:none` (áÇ ÊÍÌÈ ÇáÊãÑíÑ/ÇáäŞÑ)º ÇäÊŞÇá `opacity/transform` ŞÕíÑ ãËá ŞæÇÆã `edzDropdown`.
- **ÇáãÍÑß `resources/js/components/edz-tooltip.js`** (ãõÓÌóøá `edzTooltip` İí `panel.js`): ÊÍÑíÖ ÏÎæá `hover: hover and pointer: fine` İŞØ (ÈÏæä áãÓ)¡ ÊÃÎíÑ ÙåæÑ 400ms æÊÃÎíÑ ÅÎİÇÁ 60ms¡ íõŞÇÓ `trigger.getBoundingClientRect()` Ëã íËÈøÊ ÇáİŞÇÚÉ İæŞ/ÊÍÊ ãÚ ÇäŞáÇÈ ÊáŞÇÆí ÚäÏ ÖíŞ ÇáÑÃÓí æÊËÈíÊ ÃİŞí ÈÍæÇİ 8pxº `@click.capture` íÎİíåÇ İæÑ Ãí äŞÑÉ ÏÇÎá ÇáÛáÇİ (ãÊæÇİŞ ãÚ ŞÇÆãÉ ÇáÕİ `edzRowMenu` ÇáãÛáóøİÉ).
- **ÇáÊØÈíŞ İí ÕİÍÉ ÇáÊÊÈÚ** (ÇÓÊÈÏÇá `title` ÇáÃÕáí ÈÇáÊæáíÈ¡ ãÚ İŞÇÚÉ «ÑŞã ÇáÊÊÈÚ ÇáããÊÏ» Úáì ÒÑ ÇáäÓÎ ÇáĞí íÚÑÖ ÇáÑŞã ãŞÊØÚğÇ):
  - `tracking-list`: ÒÑ ÇáäÓÎ (label = ÇáÑŞã ÇáßÇãá) + ÔÇÑÉ ÇáÍÇáÉ/ÇáÓÌá ×2 + ÒÑ ÇáäæÊÇÊ + ÒÑÇ ÇáÅÌÑÇÁÇÊ ? (ÛáÇİ Íæá ÍÇæíÉ `edzRowMenu` ßÇãáÉ ÍÊì áÇ ÊÊÚÇÑÖ ãÑÇÌÚ x-ref/ÇáÊÚÈíÑÇÊ) + ÔÇÑÊÇ SD/HM ×2 (desktop+mobile).
  - `order-drawer`: ÒÑ äÓÎ ÑŞã ÇáÊÊÈÚ İí ßÇÑÊ ÇáäÇŞá.
  - `tracking-toolbar`: ÒÑ ÇáãÒÇãäÉ ÇáÌãÇÚíÉ + ÒÑ ÅÚÏÇÏÇÊ ÇáÃÚãÏÉ.
  - `tracking-tabs`: ÒÑ ÓáÉ ÇáãåãáÇÊ/ÇáÚæÏÉ (label ÏíäÇãíßí + `ms-auto` Úáì ÇáÛáÇİ).
- **ÇáÊÒÇãÇÊ gated ÈÜ hover:** Úáì ÇáãæÈÇíá ßá ÇáãŞÇÈÖ ÇáİÇÑÛÉ ÊÚãá ÈáÇ ÊæáíÈ (Óáæß Apple) — áÇ ÊÓÑíÈ ÊæáíÈ Úáì ÇááãÓ.
- **ÇáÃÏáÉ:** `npm run build` äÙíİ (CSS íÍæí `.edz-tooltip*` æÇáÜ panel chunk íÍæí `edzTooltip`)º `view:cache` äÙíİº **37 äÇÌÍÉ / 156 ÊÃßíÏğÇ** (TrackingTrashWebhookLabelTest 14 + TrackingSearchFilterTest 23) — ÕİÑ ÇäÍÏÇÑ.

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** Úáì 375/768/1440 — ÇáÊæáíÈ İæŞ ÃÒÑÇÑ ÇáäÓÎ/ÇáÅÌÑÇÁÇÊ/ÔÇÑÉ ÇáÍÇáÉ/ÔÇÑÊí SD-HM¡ æÙåæÑå ÈÚÏ ~400ms ÈáÇ ŞÕø ãä ÍÇæíÉ ÇáÓßÑæá¡ æÇÎÊİÇÄå İæÑ ÇáäŞÑ¡ æÚÏã ÙåæÑå Úáì ÇááãÓ ÅØáÇŞğÇ. ÇäÊåÊ ÇáÏİÚÇÊ ÇáÎãÓ áãÊÌÑ ÇáÊÊÈÚ.

---

## ÎØÉ ÑÌÇá ÇáÊæÕíá Úáì ÕİÍÉ ÇáØáÈíÇÊ: ÇáãæÇÖÚ ÇáÃÑÈÚÉ + ÍÕÑíÉ «ÔÑßÉ/ÑÌá» + ÅÑÓÇá áÑÌá (ÓÈÊãÈÑ 2026) ?

**ÈŞÑÇÑÇÊ ÇáãÓÊÎÏã ÇáãÚÊãÏÉ:** ÇáäØÇŞ = ÇáãæÇÖÚ ÇáÃÑÈÚÉ Úáì ÕİÍÉ ÇáØáÈíÇÊ (ÇáÎáíÉ ÇáãÈÇÔÑÉ¡ äãæĞÌ ÇáÅÖÇİÉ/ÇáÊÚÏíá¡ ãæÏÇá ÇáÊÚÏíá ÇáÓÑíÚ ááÊæÕíá¡ ÏÑÌ ÇáÊÃßíÏ) + ãØÇÈŞÉ Ğáß Úáì ÕİÍÉ ÇáÊÊÈÚº ÇáãßÊÈ íÈŞì ÇÎÊíÇÑíğÇ ãÚ ÇáÑÌá áßäå **ãÎİí İí ÇáæÇÌåÉ ÚäÏ ÇÎÊíÇÑ ÑÌá**º ÇáÚÑÖ ãŞÓøã «ÔÑßÉ/ÑÌá»º ÊäİíĞ ßÇãá.

- **ÇáãæÇÖÚ ÇáÃÑÈÚÉ (`orders/index.blade.php`):** ßáåÇ ÕÇÑÊ ÊÊÚÇãá ÚÈÑ **ãÚÑøİ ÅÑÓÇá æÇÍÏ** — «ÔÑßÉ» Ãæ «ÑÌá» — ãÚ ÍÕÑíÉ ßÇãáÉ:
  - ÇáÎáíÉ ÇáãÈÇÔÑÉ: `openOrderProviderEdit` ÚäÏ ÇÎÊíÇÑ ÑÌá íÕİøÑ `shipping_provider_id`+`stopdesk_point_id` (ÎØÃ `partner_exclusive` Åä ÈŞí ÇáÇËäÇä)º ãÍÑÑ ÇáÜ stopdesk áÇ íÙåÑ ÅáÇ ãÚ ÔÑßÉ.
  - äãæĞÌ ÇáÅÖÇİÉ/ÇáÊÚÏíá (`submitCreate`/`submitEdit`): ÚäÏ ÍÔæ ÇáÑÌá ÊõÕİóøÑ ÇáÔÑßÉ/ÇáãßÊÈº İÍÕ stopdesk İí `submitCreate` íÓÊÎÏã `$this->addError('stopdesk_point_id', __('merchant_panel.office_required_for_stopdesk'))` (æÇÌåÉ `OrderOfficeSelectionTest` ÊÊØáÈ `assertHasErrors`).
  - ãæÏÇá ÇáÊÚÏíá ÇáÓÑíÚ ááÊæÕíá (`openDelivery`/`saveDelivery`): äİÓ ŞÇÚÏÉ ÇáÇÓÊÈÏÇá ÇáÍÕÑíÉ.
  - ÏÑÌ ÇáÊÃßíÏ (`submitConfirmAndSend`): íÍá ÇáÔÑíß ãä ÇáÏÑÌ ŞÈá Ãí İÍÕ¡ Ëã íËÈøÊ ÇáÓÇŞ ãÓÈŞğÇ (persist) áíãÑ ÈİÍÕ ÇáÇßÊãÇá¡ æÅÑÓÇá ÓÇŞ ÇáÑÌá ÚÈÑ `OrderShippingGateway::send(providerId: null)` Ëã `ensureRiderTracking($fresh, generateRiderTrackingNumber($fresh))` — ÊæáíÏ İæÑí áÑŞã `HM-`/`SD-` (ÍÓÈ `delivery_type`) ÚäÏ ÇáÅÑÓÇá.
- **ÇáŞØÚ ÇáãÔÊÑßÉ:** `partials/partner-picker.blade.php` (ÌÏíÏ — picker ãæÍøÏ `$picker = 'form'|'confirm'` ãÚ ÅÎİÇÁ ÇáãßÊÈ ÚäÏ ÇáÑÌá¡ æÇÎÊÕÇÑ «ÔÑßÉ æÇÍÏÉ» `data-edz-company-single` ÈáÇ `edz-company-select` ßãÇ íÊØáÈ `OrdersDefaultProviderTest`) + `partials/confirm-drawer.blade.php` (ÌÏíÏ — ÏÑÌÉ ÇáÊÃßíÏ ÇáãÓÊÎÑÌÉ ãÚ `\App\Enums\Store\StorePermissionEnum::…` ÈÜ FQCN — ÇáÑãÒ ÇáŞÕíÑ ßÓÑ ÇáÊÍŞŞ).
- **ÓÇŞ ÇáÑÌá İí `OrderCompleteness(forSend)`:** ÊÊØáÈ **ÇáÚäæÇä ÏÇÆãğÇ** ÍÊì áÓÇŞ stopdesk (ÚßÓ ÖáÚ ÇáÔÑßÉ) æáÇ ÊÊØáÈ ãßÊÈğÇº `blank(provider) && blank(rider)` íÑİÖ ÇáÅÑÓÇá ÈÑÓÇáÉ `confirm_requires_partner` (Óáæß ÌÏíÏ ÑõİöŞ ÈÅÑÎÇÁ ÇÎÊÈÇÑ ÓÇÈŞ áíŞÈá ÇáÔÑíß Ãæ ÊÍĞíÑ items).
- **ÕİÍÉ ÇáÊÊÈÚ (ÇáãØÇÈŞÉ):** `orders/index`'s `order-form-modal`/`delivery-edit-modal` ÊõÖãóøä ÚÈÑåÇ ÃíÖğÇ — ÍÇáÉ ÌÏíÏÉ `delivery_rider_id`/`formPartnerType`/`riderOptions` + `switchFormPartner()` İí `TrackingRiderFormConcern` (äİÓ ÇáÍÕÑíÉ) + `TrackingColumnConcern::loadRiderOptions()` íãáÃ `riderOptions` (äÔØæä İŞØ¡ Ôßá value/label/hint/kind ãØÇÈŞ).
- **ÇáÃÏáÉ:** `php -l` äÙíİ Úáì ßá ÇáãáİÇÊ ÇáãÊÛíöøÑÉ (ÃÑÈÚÉ PHP + ÇááÛÇÊ ×4) + `view:cache` success ÚÈÑ **PHP 8.3.28** (`C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe` — `php` ÇáÇİÊÑÇÖí 8.2.12 íİÔá ŞíÏ Composer ?8.3). **`OrderCompletenessTest` 22 äÇÌÍğÇ (46 ÊÃßíÏğÇ)** — ãäåÇ ÇáÌÏíÏ: ÈÇÏÆÊÇ HM/SD¡ ÚÏã ÊÕÇÏã `generateRiderTrackingNumber`¡ ÓÇŞ ÑÌá stopdesk ÊÊØáÈ ÇáÚäæÇä áÇ ÇáãßÊÈ¡ ÑÌá ÈáÇ ÚäæÇä ÛíÑ ãßÊãá ÚäÏ ÇáÅÑÓÇá¡ confirm-and-send áÑÌá ÚÈÑ Volt (shipped + `HM-` + ÍÕÑíÉ)¡ æÑİÖ ÑÌá ÛíÑ äÔØ/ÃÌäÈí. **`OrdersDefaultProviderTest` + `OrderOfficeSelectionTest` 27 äÇÌÍğÇ (86 ÊÃßíÏğÇ)**. **ÇáÌæáÉ ÇáßÇãáÉ `Order` + `Merchant`: 398 äÇÌÍğÇ (1544 ÊÃßíÏğÇ) — ÕİÑ ÇäÍÏÇÑ.**

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã:** Úáì 375/768/1440 — ÇáãæÇÖÚ ÇáÃÑÈÚÉ ÊÚÑÖ ÇÎÊíÇÑ «ÔÑßÉ/ÑÌá» ãÚ ÇáÍÕÑíÉ (ÇÎÊíÇÑ ÃÍÏåãÇ íÕİøÑ ÇáÂÎÑ)¡ æÅÎİÇÁ ÇáãßÊÈ ÚäÏ ÇáÑÌá¡ æÅÑÓÇá ØáÈíÉ áÑÌá íæáøÏ ÑŞã ÊÊÈÚ HM/SD İæÑğÇ æíÛáŞ ÇáÏÑÌ¡ æãØÇÈŞÉ ÇáÓáæß äİÓå Úáì ÕİÍÉ ÇáÊÊÈÚ.

---

## ãßæøä ãÓÍ ÇáÈÇÑßæÏ ÇáãæÍøÏ `x-edz.barcode-scan-input` — ÃÓÇÓ ãÔÊÑß (ÇáãÑÍáÉ 36 + ÇáãÑÍáÉ E.3) (ÓÈÊãÈÑ 2026) ?

**ÇáäØÇŞ:** ãßæøä ÃÓÇÓ (foundation) ÎÇáò ãä Ãí ãäØŞ ÊÌÇÑí¡ íõÓÊåáß áÇÍŞğÇ ãä ãÑÍáÉ 36 (ÇáÊÍŞŞ ãä ÅÑÓÇá NOEST ÈÇáÈÇÑßæÏ — ÇáãõÔÇÑ ÅáíåÇ ÓÇÈŞğÇ ÈÜ«Phase 32» İí ÓØÑ ÅÚÇÏÉ ÊÕãíã ÇáÊÊÈÚ) æãä ÇáãÑÍáÉ E.3 (ßÊÇÈÉ ÍÇáÉ ÇáÑÌá). áã íõÑÈØ ÈÃí ãÓÊåáß ÈÚÏ İí åĞÇ ÇáÅÏÎÇá.

- **Çáãßæøä `resources/views/components/edz/barcode-scan-input.blade.php`:** ÎÕÇÆÕ `wire-scan-method` (ÇÓã ØÑíŞÉ Livewire íõÓÊÏÚì ÚäÏ ßá ãÓÍ — äŞØÉ ÏÎæá æÇÍÏÉ ááäÕ æÇáßÇãíÑÇ)¡ `placeholder`¡ `label`¡ `autofocus` (ÇİÊÑÇÖí false). ÍŞá ÇáäÕ íŞáøÏ ÇáäãØ ÇáãÚÊãÏ İí `returns/index` (ÓØÑ ~161) ÍÑİíğÇ: `wire:model.live` ÚÈÑ `$attributes` + `@keydown.enter.prevent="$wire.<method>($event.target.value); $event.target.value = ''"` + `autofocus`¡ æÇáÇÓã ãÍÑæÓ ÈÜ `^[A-Za-z_][A-Za-z0-9_]*$` İíÈŞì ÇáÇÓã ÛíÑ ÇáÕÇáÍ ÎÇãáğÇ (áÇ ßÓÑ/ÍŞä). ÒÑ ßÇãíÑÇ ÈÌÇäÈå íİÊÍ `x-edz.modal` (äİÓ ÈØÇŞÇÊ/æÑŞÉ ÓİáíÉ ÈŞíÉ ÇáÊØÈíŞ Úáì 375/768).
- **ÇáÎáİíÉ `resources/js/components/barcode-scan-input.js`** (ãõÓÌóøá `Alpine.data("barcodeScanInput", ...)` İí `panel.js`): ÇáßÇãíÑÇ ÊõÑßóøÈ ÚÈÑ `x-if` İŞØ ÚäÏ ÇáØáÈº **`import("html5-qrcode")` ÏíäÇãíßí ÚäÏ Ãæá İÊÍ ßÇãíÑÇ İŞØ — ÃÈÏğÇ İí ÑÃÓ `panel.js`**º ßÇãíÑÇ ÎáİíÉ `facingMode: { exact: "environment" }` ãÚ ÓáÓáÉ ÊÑÇÌÚ environment?user¡ ÊÚÏÇÏ ÇáßÇãíÑÇÊ (`Html5Qrcode.getCameras()`) æãäÊŞí ÌåÇÒ íÙåÑ İŞØ ÚäÏ æÌæÏ ÃßËÑ ãä ßÇãíÑÇ¡ `formatsToSupport` = CODE_128/CODE_39/EAN_13/EAN_8/QR_CODE (ãáÕŞÇÊ ÇáäÇŞá ÇáÌÒÇÆÑíÉ Code128/EAN ÊÈŞì ãÏÚæãÉ)¡ debounce 1500ms ÖÏ Çáİß ÇáãßÑÑ¡ **ÅíŞÇİ ÇáÏİŞ ÚäÏ Ãí ÅÛáÇŞ** (äÌÇÍ Ãæ ÅáÛÇÁ — ÈØÇÑíÉ/ÎÕæÕíÉ) ãÚ ÇÓÊÚÇÏÉ `body overflow` ÚäÏ ÅÒÇáÉ `x-if` (ãÓÇÑ ÇáãÓÍ ÇáäÇÌÍ íÊÌÇæÒ watch ÇáãæÏÇá)¡ ãäØŞÉ ÇáßÇãíÑÇ ãÍãíÉ ÈÜ `wire:ignore` ãä morph ÏæÑÇÊ Livewire¡ æãÍÇĞÑÉ `_abortFlag`/`_waitForContainer` áßá äŞÇØ ÇáÜ await.
- **ÃíŞæäÉ `camera`** ÃõÖíİÊ Åáì ÎÑíØÉ `icon.blade.php` ÈäãØ Heroicons outline 24px äİÓå (áÇÍŞğÇ áÜ `qr-code`).
- **ÇáÊÑÌãÇÊ `edz.php` ×4 (en/ar/es/fr):** `scan_with_camera`/`starting_camera`/`camera_unavailable`/`switch_camera`/`auto_camera`.
- **ÇáÍÒãÉ:** `html5-qrcode@^2.3.8` İí `dependencies` — **chunk ãÓÊŞá 375.46 kB (gzip 110.72 kB) íõÌáÈ ÚäÏ Ãæá İÊÍ ßÇãíÑÇ İŞØ**º manifest íÄßÏ `dynamicImports` ãä panel.js æáÇ íÖãåÇ ááÍÒãÉ ÇáÃÓÇÓ. panel.js äãÇ 3.13 kB (gzip +1.06 kB) áÌáæ Çáãßæøä (ŞíÇÓ İÚáí ÈÜ build ŞÈá/ÈÚÏ: 236.69?239.82 kB ŞÈá ÅÕáÇÍí ÇáÌáæ ÇáÕÛíÑíä¡ Ëã 240.02 kB ÈÚÏ `_waitForContainer`/overflow).
- **ÇáÃÏáÉ:** `php -l` äÙíİ Úáì ãáİÇÊ ÇááÛÉ ÇáÃÑÈÚÉ + `view:cache` success ÚÈÑ PHP 8.3.28 + `npm run build` äÙíİ (chunk html5-qrcode ãäİÕá + dynamicImports ãÄßóøÏ). áÇ ÊÛííÑ Úáì `resources/views/components/edz/barcode.blade.php` (ãæáøÏ SVG Code128 áØÈÇÚÉ ÇáãáÕŞ — ÛíÑ Ğí ÕáÉ).

> **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** Úáì 375/768/1440 — ÚÇÕİÉ ãİÇÊíÍ ŞÇÑÆ ÇáÃÌåÒÉ İí ÍŞá ÇáäÕ¡ ÅĞä ÇáßÇãíÑÇ¡ İß 1D (Code128/EAN)/QR¡ ÅØİÇÁ ãÄÔÑ ÇáßÇãíÑÇ/ÇáãíßÑæİæä ÚäÏ ÇáÅÛáÇŞ¡ æãäÊŞí ÇáÃÌåÒÉ ãÊÚÏÏ ÇáßÇãíÑÇÊ. **ÇáãÑÍáÉ 36 æÇáãÑÍáÉ E.3 ÊÍÕáÇä Úáì ÅÏÎÇáåãÇ ÇáÎÇÕ ÚäÏ ÇáÇÓÊåáÇß (áÇ íõÈäì İíåãÇ ÇáÂä).**

---

## ãÑÍáÉ ÅÚÇÏÉ ÊÕãíã ãÚÇáÌ ÅäÔÇÁ ÇáãäÊÌ — ÇáãÑÍáÉ ÇáİÑÚíÉ 1 ? (ãÍÑß ÇáÎØæÇÊ ÇáÏíäÇãíßí + ÇÓÊÎÑÇÌ ÇáÃÌÒÇÁ + ÇáÊäŞá ÇáÍÑ ÇáãÊÍŞŞ) — 2026-09-11

**ÇáÓíäÇÑíæ:** ÇáÓíäÇÑíæ B ÇáãÚÊãÏ (ãÍÑß ÎØæÇÊ ãÑä) — ÇáãÑÍáÉ ÇáİÑÚíÉ 1 ãä 5. ÊÑÍíá Ôßáí ÕöÑúİ (like-for-like) — **áÇ** ÊÛííÑ İí ãÍÊæì/ŞæÇÚÏ ÇáÎØæÇÊ¡ **áÇ** ÎØæÇÊ ÌÏíÏÉ¡ **áÇ** ÌÏæá ãÓæÏÇÊ¡ **áÇ** ÊÛííÑ İí ProductService. `ARCHITECTURE_RULES.md` ÛíÑ ãæÌæÏ — `livewire-conventions.md` åæ ÇáãÑÌÚ ÇáãáÒã.

**ãÇ Êóãø:**
- **`app/Domains/Product/Support/ProductWizardSteps.php` (ÌÏíÏ):** ãÕÏÑ ÍŞíŞÉ æÍíÏ áÊÚÑíİÇÊ ÇáÎØæÇÊ — ËæÇÈÊ `STEP_BASIC=1…STEP_REVIEW=5` + `LAST_STEP`¡ `all()` (id/label/icon/partial)¡ `ids()`¡ `count()`¡ `isExistingStep()`¡ æ`rulesFor($step, $context)` íõÚíÏ ŞæÇÚÏ ÇáÊÍŞŞ áßá ÎØæÉ ÚÈÑ `match` ãÚ ÓíÇŞ `store_id`/`product_id`/`min_order_qty` — **ÇáãÑÌÚ ÇáæÍíÏ** ááŞæÇÚÏ (ÇÓÊõÎÑÌÊ ãä ÊÏİŞ `$stepRules` ÇáŞÏíã İí `form.blade.php` ÍÑİíğÇº ÅÛáÇŞ `max_order_qty` íÓÊÎÏã `$minOrderQty` ãä ÇáßæäÊßÓÊ ÈÏá `$this->min_order_qty` — Óáæß ãØÇÈŞ áÃä ÇáŞíã ÊõŞíóøã æŞÊ ÇÓÊÏÚÇÁ ÇáÓíÇŞ). ÃõÒíá ÇÓÊíÑÇÏ `ProductOptionInputType` ÛíÑ ÇáãÓÊÚãá + ãÓÇÚÏ `hasRules()`. ÇáÅÔÇÑÉ ÇáÃæáì İí `app/Domains/Product/` (ßÇä İÇÑÛğÇ ÓÇÈŞğÇ).
- **ÇÓÊÎÑÇÌ ÇáÃÌÒÇÁ (5 ãáİÇÊ):** `resources/views/livewire/merchant/products/form/step-{basic,pricing,options,inventory,review}.blade.php` — ßá ÌÒÁ íÍæí ÍáŞÉ `x-show="step === N"` + `x-transition.opacity` ÇáÎÇÕÉ Èå¡ ãØÇÈŞ ÊãÇãğÇ ááßÊá ÇáãÖãøäÉ ÇáÓÇÈŞÉ (ÃÓØÑ ~572–1107). ÇáŞÑÇÑ: ÇáãÓÇÑ ÇáãĞßæÑ İí ÇáãåãÉ `products/form/` (ÓÇÈŞÉ: `storefront-settings.blade.php` + `storefront-settings/` íÊÚÇíÔÇä æÇáÃÏáÉ `<module>/partials/` + ãÓÇÑÇÊ İÑÚíÉ ÛíÑ `partials` ßÜ `storefront-settings/fields/`). ÊÓÊÎÏã FQCN ÍíË íáÒã (ÊõËÈÊ ÚÈÑ `tracking/partials/tracking-tabs`).
- **`form.blade.php` ÅÚÇÏÉ ÊæÌíå ÇáãÍÑß:** ÅÖÇİÉ `validated_steps` ááÏæáÉ (array) — İí ÇáæÖÚ **ÇáÌÇÏá/ÇáÅäÔÇÁ íÈÏÃ İÇÑÛğÇ**¡ æİí **æÖÚ ÇáÊÚÏíá íõÈĞÑ ÈßÇãá `ProductWizardSteps::ids()`** (ãäÊÌÇÊ ŞÇÆãÉ ÇÌÊÇÒÊ ÇáÊÍŞŞ İÚáÇğ ? ÈŞÇÁ ÇáÊäŞá ÇáÍÑ Ïæä ÇäÍÏÇÑ İí ÇáÊÚÏíá) — `$stepRules` ÊİæÖ áÜ `rulesFor` ÈÓíÇŞ ßÇãá¡ `$nextStep` íÈĞøÑ/íÍÊİÙ ÈÇáÎØæÉ ÇáÍÇáíÉ İí `validated_steps` Ëã íÊŞÏã ÈÜ `min(ProductWizardSteps::count(), …)`¡ **`$goToStep` ÃÚíÏÊ ßÊÇÈÊå** ßáå: ÎØæÉ ÛíÑ ãæÌæÏÉ/äİÓ ÇáÍÇáíÉ ? no-opº ÇáÎØæÉ ÇáãŞİáÉ ? `dispatch('swal', info, step_locked_title/step_locked_text)` + ÅÑÌÇÚ (áÇ ÊäŞøá¡ áÇ ÊÍÏíË ÍÇáÉ)º ÎØæÉ ãİÊæÍÉ ÛíÑ ãÊÍŞŞÉ ? ÊÔÛíá ãÍáá ÊÍŞŞ ÇáÎØæÉ ÇáãÓÊåÏİÉ æÇäÊÙÇÑ `ValidationException` (íãáÃ ßíÓ ÇáÃÎØÇÁ æÊÙá ÇáÎØæÉ)º Ëã ÊÓÌíá `validated_steps` æÇáÇäÊŞÇá. `$isStepUnlocked` (protect) — ÇáÎØæÉ N ãİÊæÍÉ iff ßá ÎØæÉ < N İí `validated_steps` (ÇáÎØæÉ 1 ÏÇÆãğÇ). `$lockedSteps` computed — ÃÑŞÇã ÇáÎØæÇÊ ÛíÑ ÇáãİÊæÍÉ. `$wizardSteps` computed ? `array_values(ProductWizardSteps::all())`.
- **ÇáÇÓÊÈÏÇá:** ßÊá ÇáÎØæÇÊ ÇáÎãÓ ÇáãÖãøäÉ ÇÓÊõÈÏáÊ ÈÜ `@foreach ($this->wizardSteps as $wizardStep) @include($wizardStep['partial']) @endforeach`º `:lockedSteps` ãÑÑ Åáì `<x-merchant.wizard-steps>`.
- **`wizard-steps.blade.php` (component):** íŞÈá `steps`/`currentStep`/`lockedSteps`º íÓÊÎÏã `$step['id']`º ÇáÎØæÇÊ ÇáãİÊæÍÉ ÃÒÑÇÑ `@click="$wire.goToStep(id)"`¡ æÇáãŞİáÉ `<span aria-disabled>` ãÚ ÃíŞæäÉ `lock-closed` (ãİÚøáÉ İí `edz/icon` ÓÇÈŞğÇ)º ÊäÙíİ ÇáÊæßäÒ: `ring-brand-100 dark:ring-brand-900/40`?`ring-brand-ring`¡ `text-brand-700 dark:text-brand-300`?`text-brand-fg`¡ `text-success-700 dark:text-success-300`?`text-success-fg`¡ `bg-success-300 dark:bg-success-700`?`bg-success-fg` — **ÕİÑ `dark:`/hex ÎÇã** (ÇáÊæßäÒ ÃËÈÊÊ İí `tailwind.config.js` + `_tokens.scss`).
- **ãİÇÊíÍ ×4 áÛÇÊ:** `products.step_locked_title`/`step_locked_text` (ÈÚÏ `step_review_desc`) İí en/ar/fr/es.

**ÃÏáÉ/sub-proofs:**
- **ÇáÇÎÊÈÇÑÇÊ (ÌÏíÏÉ):** `tests/Feature/Merchant/ProductWizardStepEngineTest.php` — 4 äÇÌÍÉ: ? ÚÑÖ ÇáÎØæÇÊ ÇáÎãÓ ÇáÏíäÇãíßíÉ ÈÇáÊÑÊíÈ ÇáŞÇäæäí (íõİÍÕ ãæÇÖÚ `strpos` ÈÊÓáÓáº `&` ÃõİáÊÊ Åáì `&amp;` ßÓáæß Blade) ? ãÈßÑÉ ÚäÏ ÇáÅŞáÇÚ `goToStep(4)` = no-op + `swal` + ÇáÍÇáÉ ÈáÇ ÊÛíÑ æÈáÇ ÃÎØÇÁ ? ÇáİÊÍ ÇáÊÏÑíÌí: nextStep ãä ÇáÎØæÉ 1 íİÊÍ 2 İŞØ¡ ÇáÎØæÉ 3 ãŞİáÉ ÍÊì ÊõÊÍŞŞ 1 æ2¡ ÇáÇäÊŞÇá ááÎáİ ááÃãÇã ãÍİæÙ ? ÇáÊÚÏíá íİÊÍ ßá ÇáÎØæÇÊ (áÇ swal ÚäÏ ÇáŞİÒ ááÎØæÉ 5). **417 äÇÌÍ (1647 assertions)** İí `tests/Feature/Merchant` — ÕİÑ ÇäÍÏÇÑ (410 ÓÇÈŞÉ + 7 ãäÊÌ/ÔíİÑÉ... ÊõÍÓÈ ÏÇÎáíğÇ İí ÇáÓæíÊ).
- **ÎÕáÉ ÇáÇÓÊÚáÇã** (ÓõÌáÊ ßãáÇÍÙÉ): Livewire ÑäÏöÑ ÅÚÇÏÉ ÇáÊÑÊíÈ íÖÑÈ ŞÇÚÏÉ ÇáÈíÇäÇÊ (ÓÌá 19 ÇÓÊÚáÇãğÇ İí `nextStep`) ÍÊì ãÚ İÊÍ/Şİá goToStep — åĞÇ ÚÈÁ Úáì ÅØÇÑ Livewire ááÊÍÏíË/ÇáÌáÈ æáíÓ ãäØŞ ÎØæÊäÇº ÇáÊÕãíã áÇ íÖíİ Ãí ÇÓÊÚáÇã ÊØÈíŞí áßá äŞÑÉ ÊäŞá. (İÍÕ ÇÓÊÚáÇãÇÊ ÇáÇÓÊÑÇÍÉ ÃõÒíÍ — áÇ ŞíãÉ ãÚ Livewire.)
- **ÇáÊÌÇæÈ (ãÚÊãÏ Úáì DOM — áÇ ÃÏæÇÊ E2E İí repo):** `wizard-steps` íÍİÙ äŞÇØ ÇáÊæŞİ ÇáÓÇÈŞÉ ÈÇáÖÈØ: İí ?640 ÇáÊÓãíÇÊ `hidden sm:inline` (ÃÑŞÇã/ÃíŞæäÇÊ İŞØ)¡ ãæÕá ÇáÎØ `flex-1` + `mx-3` íãÊÏ ÏÇÆãÇğ¡ æÇáÃÒÑÇÑ `h-9 w-9` (36px ? ÍÏ 44 ÊæÔÑ ÇááãÓ åæ Õİæİ ÇáÃÒÑÇÑ ÇáİÑÏíÉ — ÓÌá: ÍÌã ÇáÒãä áÇ íÊÛíÑ). ÇáÃÌÒÇÁ ÊÍãá ÇáŞæÇáÈ ÇáãÊÌÇæÈÉ äİÓåÇ (grids/edz-card) áÇ ÊõáãóÓ. ÇáÊÍŞŞ ÇáíÏæí ÚäÏ 375/768/1440 ãØáæÈ ãä ÇáãÓÊÎÏã İí ÇáäåÇíÉ.
- **ÇáÊæËíŞ:** åĞÇ ÇáŞÓã İí `Todos.md` + ãáÇÍÙÉ `APP/actions/Product/*` ÛíÑ ãÓÊÚãáÉ ãÇ ÒÇáÊ ÊõÚíÏ ÇáÚáÇãÉ (ÎÇÑÌ ÇáäØÇŞ İí åĞå ÇáãÑÍáÉ ÇáİÑÚíÉº ÇáİÑæÚ 2×5 ÓÊÓÊåáßåÇ — ÃÚãá ÈåÇ ŞÇÆãÉ ÇäÊÙÇÑ).

**ãáÇÍÙÉ:** `docs/livewire-conventions.md` — ÃÓØÑ İÇÑÛÉ ÊõÑßÊ ÈÏæä ŞÕÏ İí ÇáÓØÑ 128 (ãä ÌæáÉ ÓÇÈŞÉ ááÊÑÌãÉ)º áÇ ÊÛííÑ İäí. `resources/lang` ÇáÅÖÇİÇÊ ãİÊÇÍÇä İŞØ áßá áÛÉ. áÇ ÊÛííÑ İí Ãí ÔíİÑÉ ÊÍŞŞ/ãäÊÌ/ØáÈíÇÊ İí åĞå ÇáãÑÍáÉ ÇáİÑÚíÉ.

---

## ãÑÍáÉ ÅÚÇÏÉ ÊÕãíã ãÚÇáÌ ÅäÔÇÁ ÇáãäÊÌ — ÇáãÑÍáÉ ÇáİÑÚíÉ 2 ? (ÎØæÉ ÕæÑ ãÎÕÕÉ) — 2026-09-12

**ÇáÓíäÇÑíæ:** ÇáãÑÍáÉ ÇáİÑÚíÉ 2 ãä 5 Úáì ÇáÓíäÇÑíæ B ÇáãÚÊãÏ — ÊÑÍíá İÊÍ/ÊÓÌíá ÎØæÉ ÕæÑ ãÎÕÕÉ ÏÇÎá ÇáãÍÑß ÇáÏíäÇãíßí ÇáŞÇÆã (ÇáãÑÍáÉ ÇáİÑÚíÉ 1 **ãÄßÏÉ** æáã íõãóÓ ãÍÑßåÇ: `goToStep`/`isStepUnlocked`/`validated_steps` İí `form.blade.php` ÈáÇ ÊÛííÑ). **áÇ** ÊßÇãá primary-image (ŞÑÇÑ ÇáãÓÊÎÏã: «Relocation only» — ÃõÔíÑ áå ÃÏäÇå)¡ **áÇ** ÓÍÈ æÅÚÇÏÉ ÊÑÊíÈ¡ **áÇ** ÑÄíÉ ÔÑØíÉ ááÎØæÇÊ (ÇáãÑÍáÉ 3)¡ **áÇ** ÅäÔÇÁ ÎíÇÑÇÊ/Şíã inline (ÇáãÑÍáÉ 4)¡ **áÇ** ÊÕÏíÑ/ÇÓÊíÑÇÏ (ÇáãÑÍáÉ 5).

**ãÇ Êóãø:**
- **`ProductWizardSteps.php`:** ËÇÈÊ ÌÏíÏ `STEP_IMAGES = 2` ãÏÎáÇÉ ÈÚÏ `STEP_BASIC` ãÈÇÔÑÉ¡ æ«ÅÚÇÏÉ ÊÓãíÉ» ÇáÈŞíÉ:. `STEP_PRICING=3`¡ `STEP_OPTIONS=4`¡ `STEP_INVENTORY=5`¡ `STEP_REVIEW=6`¡ `LAST_STEP = STEP_REVIEW`. ÃõÖíİ ÅÏÎÇá `step_images` İí `all()` (icon `image` — ãæÌæÏÉ İí `edz/icon`¡ partial `livewire.merchant.products.form.step-images`) æ`rulesFor(STEP_IMAGES) => []` (nullable — ÈáÇ ãäÊÌ ÅáÒÇãí ááÕæÑº ÊÍŞŞ İÚáí: `ProductService::syncImages` íÓÊŞÈá ãÕİæİÉ İÇÑÛÉ ÈáÇ ÔÑØ¡ æ`form.blade.php` áÍİÙ `$data['images']` áÇ íİÑÖ Ãí ŞÇÚÏÉ `required`). ÇáãÍÑß ÇáŞÇÆã (äãæĞÌ ÇáÊÍŞŞ/ÇáÊäŞá/`@include` loop) ÈáÇ ÊÛííÑ — **no new queries** (ÊÚÏíá ÊÚÑíİ/äŞá ÚÑÖ İŞØ¡ ÕİÑ ÇÓÊÏÚÇÁÇÊ Eloquent ÌÏíÏÉ).
- **`form/step-images.blade.php` (ÌÏíÏ¡ 31 ÓØÑ):** äİÓ äãØ `x-show="step === 2"` + `x-transition.opacity` æÇáÈäíÉ `grid grid-cols-1 gap-6 xl:grid-cols-3` ãÚ `xl:col-span-2` ßÈÇŞí ÇáÃÌÒÇÁ. ÇáãÍÊæì = ßÊáÉ ÇáÕæÑ ÇáãäŞæáÉ ÍÑİíğÇ ãä `step-basic` (ÍŞá ÇáÃÏÎÇá `wire:model="newImages" multiple accept="image/*"` + ÔÈßÉ ÇáÅØÇÑ ÇáãÕÛÑÉ `grid grid-cols-2 gap-3` + ÒÑÇ `removeImage(index)`/`removeNewImage(index)`). **áÇ ÊÛííÑ** İí ÃÓãÇÁ ÇáÍÇáÉ `images`/`newImages` æáÇ İí ÇáÅÌÑÇÁíä æáÇ İí ÊÏİŞ ÇáÍİÙ ÚäÏ `save()`.
- **`form/step-basic.blade.php` (119 ? 79 ÓØÑ):** ÃÒíáÊ ßÊáÉ ÇáÕæÑ äåÇÆíğÇ (ÍÊÊ el card sidebar) æÈŞí ÇáÍŞá ÇáÃÓÇÓí İŞØ (name, slug, brand, unit, categories, short_description, description, is_active, is_featured) ãÚ ÅÈŞÇÁ ÍÇÑÓ `x-show="step === 1"`.
- **ÅÚÇÏÉ ÊÑŞíã ÍÑøÇÓ ÇáÃÌÒÇÁ:** `step-pricing` `=== 2`?`=== 3`¡ `step-options` `=== 3`?`=== 4`¡ `step-inventory` `=== 4`?`=== 5`¡ `step-review` `=== 5`?`=== 6`.
- **`form.blade.php` ÇáÃÒÑÇÑ:** ÍÇÑÓÇ ÇáÊäŞá **áã íÚæÏÇ ãßÊæÈğÇ íÏæíğÇ** — `step < 5`?`step < {{ ProductWizardSteps::count() }}` æ`step === 5`?`step === {{ ProductWizardSteps::LAST_STEP }}` (ãÕÏÑ ÏíäÇãíßí æÇÍÏ¡ áÇ ÅÚÇÏÉ ÊÑŞíã ãÓÊŞÈáíÉ).
- **`wizard-steps.blade.php`:** **ÕİÑ ÊÛííÑ** — íõÌãÚ ÇáæíáíÉ ÊáŞÇÆíğÇ ãä `ProductWizardSteps::all()` (íÊÍŞŞ: ÊÈæíÈ «Images» İí ÇáÊÑÊíÈ ÑŞã 2 ÊáŞÇÆíğÇ ÈáÇ ÊÚÏíá ãßæøä).
- **ãİÇÊíÍ ×4 áÛÇÊ:** `products.step_images` + `step_images_desc`¡ ÊõÏÑÌÊ ÃÈÌÏíğÇ Èíä `step_basic_info_desc` æ`step_inventory` İí en/ar/fr/es. íæÌÏ ãÓÈŞğÇ `products.images`/`images_hint`/`images_label`.

**ŞÑÇÑ flagged (ÃßøÏå ÇáãÓÊÎÏã):** `ProductImage` íÍÊæí ÃÚãÏÉ `is_primary`+`sort_order` (ãæÌæÏÉ¡ `Product::primaryImage()` ÚáÇŞÉ MorphOne) áßä `ProductService::syncImages()` íßÊÈ **İŞØ** `sort_order` (= ÊÑÊíÈ ÇáãÕİæİÉ¡ İåí ÃÕáğÇ Ãæá ÕæÑÉ = primary) æíÊÑß `is_primary` ÈáÇ ÇÓÊÎÏÇã İí ÇáÜ wizard — æãİÊÇÍ `products.images_hint` («The first image is used as the primary product image.») íæËøŞ åĞÇ ÇáÓáæß ÇáÕÑíÍ. **ÇáŞÑÇÑ: Relocation only — áã íõÑÈØ `is_primary` İí åĞå ÇáãÑÍáÉ** (ÎÇÑÌ ÇáäØÇŞº ÃõÔíÑ áå áíõÏãÌ İí ãÑÍáÉ áÇÍŞÉ).

**ÃÏáÉ/sub-proofs:**
- **ÇáÇÎÊÈÇÑÇÊ:** `ProductWizardStepEngineTest.php` — 5 äÇÌÍÉ (ßÇäÊ 4): ? ÚáÇãÉ ÇáÇÎÊÈÇÑ ÃÕÈÍÊ «six dynamic steps» (ßÇäÊ «five») — ÊõİÍÕ ÇáÂä ÏÇÎá ßÊáÉ `<nav aria-label="Progress">…</nav>` İŞØ (ÅÕáÇÍ ÅíÌÇÈí ßÇĞÈ: ãİÊÇÍ ÍÇáÉ Livewire `newImages` íÍÊæí «Images» İíÙåÑ ŞÈá ÇáÊÈæíÈ — ÇáÇÎÊÈÇÑ ÇáÓÇÈŞ ÃÚÊãÏ Úáì ÇáÜ`strpos` İí ßÇãá ÇáÜ html İÚËÑ Úáì ãËÇá ÓÇÈŞº ÇáÂä íİÍÕ nav ÍÕÑíğÇ = ãÕÏÑ ÊÑÊíÈ ÇáÊÈæíÈÇÊ ÇáÍŞíŞí) ? ÎØæÉ ãŞİáÉ ßŞÈá + ÌÏíÏÉ: `goToStep(STEP_IMAGES)` ÚäÏ ÇáÅŞáÇÚ İÇÑÛğÇ ãÍÌæÈ ÃíÖğÇ ? İÊÍ ÊÏÑíÌí ãÚ ÇáÕæÑ: after nextStep ãä 1 ? ÇáÍÇáí = `STEP_IMAGES`¡ Ëã `goToStep(STEP_PRICING)` ãŞİæá¡ Ëã `nextStep` (ŞæÇÚÏ ÕæÑ İÇÑÛÉ ÊãÑ) ? íİÊÍ `STEP_PRICING` = ÇáæÖÚ/ÇáİÊÍ ÇáÕÍíÍ ááÕæÑ¡ Ëã ãÓÇÑ ÇáÊäŞá ÇáÍÑ ÇáßÇãá ÍÊì `STEP_OPTIONS` ? ÇáÊÚÏíá íİÊÍ Çáßá ? **ÌÏíÏ:** «the images step renders persisted images and the upload input» — `images: [...]` ÊõÚÑÖ (img URLs ãä `Storage::disk('public')->url`) + `type="file"` + `accept="image/*"` + `wire:model="newImages"`. **418 äÇÌÍ (1661 assertions)** İí `tests/Feature/Merchant` (417 ÓÇÈŞÉ + 1 ÌÏíÏ) — ÕİÑ ÇäÍÏÇÑ. `php -l` äÙíİ Úáì ßá ÇáãáİÇÊ ÇáãÊÃËÑÉ + `view:cache` äÇÌÍ (Ëã `view:clear`).
- **ÇáÊÌÇæÈ (DOM-grounded — ÈáÇ ÃÏæÇÊ E2E İí repo):** ? **375px:** ÊÓãíÇÊ ÇáÊÈæíÈÇÊ `hidden sm:inline` (ãæÑæË ãä ÇáãÑÍáÉ 1) ? ÃÑŞÇã/ÃíŞæäÇÊ İŞØ + ÃÑŞÇã ×6 ÊäÇÓÈº ÔÈßÉ ÇáÕæÑ `grid grid-cols-2 gap-3` İí ÚãæÏ æÇÍÏ (áÇ ÍÇÌÉ áÊÑßíÈ ÚäÏ ÇáÖíŞ)º ÍŞá Çáãáİ `block w-full` ãÚ `accept=image/*` íÚãá Úáì ÇáÔÇÔÇÊ ÇáÖíŞÉ. ? **768px:** ÇáÊÓãíÇÊ ÊÙåÑ ÚäÏ `?640px` (`sm:inline`) ÈÓÊÉ ÊÈæíÈÇÊ İí ÇáäÇİÈÇÑº ÇáÔÈßÉ ÊÈŞì ÚãæÏíä. ? **1440px:** ÇáäÇİÈÇÑ ÇáßÇãá ÈÓÊÉ ÊÓãíÇÊ¡ æÔÈßÉ ÇáÕæÑ ÚãæÏíä ÏÇÎá ÈØÇŞÉ `xl:col-span-2`. ßáåÇ ÛíÑ ãÊÛíÑÉ ÓáæßíğÇ Úä ŞÈá ÇáäŞá — ÇáÊÍŞŞ ÇáíÏæí ÇáÈÕÑí ÇáäåÇÆí (ãØáæÈ ãä ÇáãÓÊÎÏã) ÓíÏÑÌ ÚäÏ ÇáãÑÍáÉ 5.
- **ÇáÃÏÇÁ:** ÕİÑ ÇÓÊÚáÇãÇÊ ÌÏíÏÉ (ÊÚÑíİ + äŞá ÚÑÖ). ÊßáİÉ ÅÚÇÏÉ ÑäÏöÑ Livewire áãäØŞÉ ÇáÕæÑ ãØÇÈŞÉ áãÇ ŞÈá — äİÓ ÇáÍŞæá/ÇáÜ wire ÇáäãÇĞÌ (ÇáÕæÑ `newImages` ÊÑİÚ ÚÈÑ Livewire ÇáãæÇÕİÇÊ ßãÇ ßÇäÊ).
- **ÇáÊæËíŞ:** åĞÇ ÇáŞÓã İí `Todos.md` + ÅÚÇÏÉ ÅÔÇÑÉ `app/Actions/Product/*` ÛíÑ ÇáãÓÊÚãáÉ (ãåãÉ ÇáãÑÍáÉ 5). `docs/livewire-conventions.md` ãáİæİ — áÇ ÊÛííÑ åäÇ (ÓÌøá İí ÇáãÑÍáÉ 1 Ãä ~3 ÃÓØÑ İÇÑÛÉ ÈáÇ ãÚäì æõÌÏÊ İíå ãä ÌæáÉ ÓÇÈŞÉ — áã ÊõáãóÓ).

---

## ÅÕáÇÍ ÚÇÌá áãÍÑß ãÚÇáÌ ÇáãäÊÌ: ãØÇÈŞÉ ÊÍŞŞ SKU Èíä ÇáÎØæÉ æÇáãÓÊæì ÇáÎÏãí + ÇáŞİÒ ÇáÊáŞÇÆí áÎØæÉ ÇáÎØÃ + ãíÒÉ: ÕæÑÉ áßá ãÊÛíøÑ (v1) — 2026-09-12

**ÇáÓíäÇÑíæ:** ÎØæÊÇä Úáì ÇáÓíäÇÑíæ B ÇáãÚÊãÏ (ÇáãÍÑß ÇáÏíäÇãíßí 6 ÎØæÇÊ¡ ÇáãÑÍáÊÇä ÇáİÑÚíÊÇä 1+2 **ãÄßÏÊÇä** ÈáÇ ãõÓø ãÍÑßåãÇ). **ÇáÌÒÁ A — ÅÕáÇÍ ÚÇÌá:** (a) ÊÍŞŞ ÇáÎØæÉ íØÇÈŞ ÊÍŞŞ `ProductService` — SKU íÕÈÍ ãØáæÈğÇ Úáì ãÓÊæì ÎØæÉ Inventory ÚäÏãÇ Êßæä ÇáÊæáíÏ ÇáÊáŞÇÆí ãÊæŞİÉ. (b) Êãßíä ÇáÊæáíÏ ÇáÊáŞÇÆí íÒíá ÇáÍÌÈ. (c) Ãí `ValidationException` İí æŞÊ ÇáÍİÙ íŞİÒ `currentStep` ÊáŞÇÆíğÇ Åáì ÇáÎØæÉ ÇáãÇáßÉ ááÍŞá ÇáİÇÔá. **ÇáÌÒÁ B — ãíÒÉ (v1):** ÕæÑÉ æÇÍÏÉ áßá ãÊÛíøÑ ÏÇÎá ÌÏæá ÇáãÊÛíÑÇÊ ÇáÍÇáí (ÎØæÉ Options) ÚÈÑ ÇáÌÏæá ãÊÚÏÏí ÇáÃÔßÇá `product_images` ÇáŞÇÆã — **áÇ** ÊÛííÑ İí ÇáãÎØØ¡ **áÇ** ÎØæÉ ÌÏíÏÉ¡ **áÇ** ãÚÑÖ/ãÊÚÏÏ ÕæÑ¡ **áÇ** ÓÍÈ æÅÚÇÏÉ ÊÑÊíÈ.

**ãÇ Êóãø:**
- **ÇáÌÒÁ A-1 — `ProductWizardSteps::rulesFor`:** ŞÇÚÏÉ `sku` İí `STEP_INVENTORY` ÊÍæøáÊ ãä `nullable…` Åáì `[string, max:255, unique…, Rule::requiredIf(fn () => ! ($context['auto_generate_sku'] ?? false))]` — ãæÑÏ ÇáÓíÇŞ ÒÇÏ `auto_generate_sku` İí ÊæŞíÚ docblock¡ æ`$stepRules` İí `form.blade.php` íãÑÑ `'auto_generate_sku' => (bool) $this->auto_generate_sku` (ÇáİÍÕ İí `nextStep`/`goToStep` íãÑ ÚÈÑ `$this->all()` İíÊÖãä ÇáÎÇÕíÉ). **Óáæß ãØÇÈŞ ÊãÇãğÇ áÜ `ProductService::create()/update()`** (íÍÕá `$baseSku` İÇÑÛğÇ æíŞĞİ `sku_required`) — ÇáÊäÇŞÖ ÇáÓÇÈŞ (ÇÓãÍ ÈŞØÚ ÇáãÑæÑ Ëã İÔá ÇáÍİÙ) ÒÇá. `barcode` Ùá `nullable` (ÈáÇ ÊÛííÑ İí Óáæß ÇáÈÇÑßæÏ).
- **ÇáÌÒÁ A-2 — `ProductWizardSteps::stepForField(string $field): ?int`:** ÎÑíØÉ `match` — `name/slug/brand_id/categories/short_description/description/unit/meta_title/meta_description/is_active/is_featured/primary_category_id` ? `STEP_BASIC`º `price/compare_price/cost_price/min_order_qty/max_order_qty` ? `STEP_PRICING`º `str_starts_with($field,'variants_preview')` ? `STEP_OPTIONS`º `stock/low_stock_threshold/sku/barcode` ? `STEP_INVENTORY`º ÛíÑå `null`.
- **ÇáÌÒÁ A-2 — `save()` ÇáŞİÒ ÇáÊáŞÇÆí:** ÃõÚíÏ åíßáÉ ÇáÊÏİŞ ßáå (ÇáãÕÇÏŞÉ ÇáãÓØøÍÉ `$v->validate()` + ÈäÇÁ `$data` + `create()/update()`) ÏÇÎá `try` æÇÍÏ ÈÅãÓÇßíä: `catch (\DomainException)` (ßãÇ ŞÈá — `dispatch('swal', error)`) + `catch (ValidationException $e)` ÇáÌÏíÏ — `$field = array_key_first($e->errors())`¡ Åä æõÌÏÊ ÎØæÉ ãÇáßÉ ? `$this->currentStep = ProductWizardSteps::stepForField($field)` Ëã **`throw $e`** (äÚíÏ Ñãíå: `SupportValidation::exception` íÖÚ errorBag æíËÈøÊ stopPropagation¡ `SupportTesting::exception` íÎÒøä ÇáãİÍøÕ áÜ `assertHasErrors` — ÑİÚÊ ÇáÏæáÉ æÇáÊİÊíÔ ÇáÎÇØÆ íÙåÑ Úáì äİÓ ÇáÎØæÉ ãÚ ÇáÍİÇÙ Úáì `validated_steps`). ÇÓÊíÑÇÏ `Illuminate\Validation\ValidationException`.
- **ÊÍÓíä UX:** `nextStep` ÇáäÇÌÍ íÓÊÏÚí `$this->resetErrorBag()` — ÇáÔÇÑÇÊ/ÇáÃÎØÇÁ ÇáÚÇáŞÉ ãä ãÍÇæáÉ İÇÔáÉ áÇ ÊáÊÕŞ ÈÚÏ ÇáÊÕÍíÍ (íÓÊÎÏã `Livewire\Component::resetErrorBag`).
- **ÇáÌÒÁ B — ÇáÍÇáÉ:** ÃõÖíİ ãİÊÇÍÇä áßá Õİ `variants_preview`: `image` (string|null — ãÓÇÑ ÇáÕæÑÉ ÇáãÎÒøäÉº íõãáÃ ãä `$variant->primaryImage()`**) æ`new_image` (`TemporaryUploadedFile|null` ÚÈÑ `WithFileUploads` ßÜ `newImages`). `VariantPreviewBuilder` **ÈŞí äŞíğÇ** — ÇáÏãÌ æŞÊ ÇáÊÔÛíá: `$rebuildPreview` íÏãÌ `['image' => null, 'new_image' => null]` áßá Õİ ÈÚÏ ÇáÈäÇÁº `buildEditFormData` íÍŞä `image`/`new_image` áßá ãÊÛíøÑº `fillPreviewFromExisting` (ÅÚÇÏÉ ÊæáíÏ ÚäÏ optionsChanged) íäŞá `path` ãä `$variant->images->firstWhere('is_primary', true)` ááÕİæİ ÇáãØÇÈŞÉ.
- **ÇáÌÒÁ B — ÇáæÇÌåÉ (`step-options.blade.php`):** ÚãæÏ «Image» ãÖÛæØ (ÈÚÏ ÚãæÏ Variant ãÈÇÔÑÉ): ÕæÑÉ ãÕÛøÑÉ `h-10 w-10 rounded-md border` ÊõÙåÑ `temporaryUrl()` ááÑİÚ ÇáÌÏíÏ Ãæ `Storage::disk('public')->url(path)` ááãÎÒøä Ãæ **ÚäÕÑ ÃíŞæäÉ ßÇãíÑÇ ãÍÇíÏ** ÚäÏ ÇáÛíÇÈ (áÇ `<img>` ãßÓæÑ)¡ ÅÏÎÇá ãáİ `sr-only accept="image/*" wire:model="variants_preview.{{ $index }}.new_image"` İæŞ ÇáÅØÇÑ¡ æÒÑ ÅÒÇáÉ `x-mark` (íÙåÑ İŞØ ÚäÏ æÌæÏ ÕæÑÉ) ? ÅÌÑÇÁ `removeVariantImage($index)` íÕİøÑ ÇáãİÊÇÍíä. íÓÊÎÏã ÇáÊæßäÒ ÇáŞÇÆãÉ İŞØ (`border-surface-border`, `bg-surface-secondary/60`, `text-ink-muted`, `danger-soft`) — **ÕİÑ ÃáæÇä/åÇßÓ ÌÏíÏÉ**¡ æÇáÌÏæá ÍÇİÙ `overflow-x-auto`.
- **ÇáÌÒÁ B — ÇáÍİÙ (`ProductService`):** `syncVariants()` — ÈÚÏ `variants()->create([...])` Åä æõÌÏ `new_image` ? `store('products','public')` + `images()->create(['path'…,'store_id'=>$product->store_id,'is_primary'=>true,'sort_order'=>0])` (äİÓ äãØ `newImages` İí `save()`). `form.blade.php::syncExistingVariants` (ãÓÇÑ ÇáÊÚÏíá options-unchanged) — íİÑøŞ ÇáÕæÑÉ **Öãä ÓáÓáÉ diff ÇáŞĞÑÉ**: eager-load `['images','optionValues']` Úáì `$dbVariants`º Åä `new_image` ? `delete`+`create`º Ãæ Åä æõÌÏÊ ãÎÒøäÉ æ`blank($row['image'])` ? `delete` (ÅÒÇáÉ)º **ÛíÑ ÇáãÊÛíøÑ ÕİÑ ÇÓÊÚáÇãÇÊ ÕæÑ**. ãÓÇÑÇ combos ÇáãÊßÑÑÉ/ÇáãİÊÇÍíÉ íÍáøÇä ãä ÇáãÌãæÚÉ ÇáãÍãøáÉ (`$variantsById`) ÈÏá `find()` áßá Õİ — ÃõÒíá N+1 ÇáŞÇÆã ãÓÈŞğÇ. `update()` — ŞÈá ßá `variants()->delete()` (optionsChanged/ÊÍæíá variable?simple) íÓÊÏÚí `deleteVariantImages($product)` ÇáÌÏíÏ (ÍĞİ Õİæİ `product_images` áãÊÛíÑÇÊ ÇáãäÊÌ — `product_images.imageable_id` ÈáÇ FK/cascade ? ÈáÇ ÊäÙíİ ÊõÊÑß íÊÇãì).
- **ãİÇÊíÍ ×4 áÛÇÊ:** `products.variant_image` (ÈÚÏ `variant_count`) æ`products.remove_variant_image` (ÈÚÏ `remove_option`) İí en/ar/fr/es (es ÃõÖíİÊ ßãÏÎáíä ÌÏÏíä ÈÌæÇÑ `variant_product` áÚÏã æÌæÏåãÇ İíå).

**ÃÏáÉ/sub-proofs:**
- **ÇáÇÎÊÈÇÑÇÊ (`ProductWizardStepEngineTest.php` — 12 äÇÌÍÉ¡ ßÇäÊ 5):** ÇáÌÒÁ A: ? «empty sku with auto-generation off blocks advancing past inventory» — ÇáãÔí Åáì Inventory Ëã `auto_generate_sku=false` + `nextStep` ? íÈŞì Úáì Inventory + `validated_steps` áÇ íÔãáåÇ + `assertHasErrors(['sku'])` + ÇáÜ html íÚÑÖ `edz-field__error` ? «enabling automatic sku generation clears the inventory block» — ÇáİÔá Ëã ÇáÊãßíä + `nextStep` ? `STEP_REVIEW` + `assertHasNoErrors(['sku'])` (ÈİÖá `resetErrorBag`) ? «save-time sku failure jumps back to the inventory step» — ÊÚÏíá ãäÊÌ + `goToStep(REVIEW)` Ëã `auto_generate_sku=false` + `sku=''` + `call('save')` ? `currentStep === STEP_INVENTORY` + `assertHasErrors(['sku'])`. **ÇáÌÒÁ B:** ? «create» — ãäÊÌ ãÊÚÏÏ 2 ãÊÛíøÑ¡ ÑİÚ ÕæÑÉ ááÕİ 0 İŞØ ? ÓØÑ `product_images` æÇÍÏ ãŞíøÏ ÈãÚÑøİ ÇáãÊÛíøÑ ĞÇ ÇáŞíãÉ S æÇáãÊÛíøÑ ÇáÂÎÑ ÈÕİÑ ? «edit/replace» — ÊÚÏíá íÍãøá `variants_preview.0.image` + `upload()` Úáì ÇáÕİ 0 + `save()` ? ÇáÓÌá ÇáŞÏíã ÍõĞİ (count=1¡ path ? ÇáŞÏíã) æÇáãÊÛíøÑ 1 ÈáÇ ÕæÑ ? «placeholder» — ãÊÛíøÑ ÈáÇ ÕæÑ íÚÑÖ ãÏÎáÇÊ Çáãáİ ááÕİøíä + ÚäæÇä ÇáÚãæÏ + `overflow-x-auto` + **áÇ** `removeVariantImage` æáÇ `temporaryUrl(` (áÇ ãÕÛøÑÇÊ ãßÓæÑÉ) ? «N+1» — ãÊÛíøÑÇÊ ÛíÑ ãÛíøÑÉ ÃËäÇÁ `save()`: ÈÇáÖÈØ ÇÓÊÚáÇã `product_images` **æÇÍÏ** (eager-load `variants.images`) — áÇ ÇÓÊÚáÇãÇÊ áßá ãÊÛíøÑ. **íÔßøáÇä ãÚğÇ 18 äÇÌÍğÇ (102 assertions)** ãÚ `ProductSkuBarcodeTest` (íõÔÛóøáÇä ãÚğÇ — `skuUser()`/`dataShape()` ãÚÑøİÇ åäÇßº æÍÏåãÇ íİÔá «Call to undefined function skuUser()» ÈÇáÊÕãíã). **425 äÇÌÍ (1699 assertions)** İí `tests/Feature/Merchant` (418 ÓÇÈŞÉ + 7 ÌÏíÏÉ) — ÕİÑ ÇäÍÏÇÑ. `php -l` äÙíİ Úáì ßá ãáİÇÊ PHP/blade/lang ÇáãÊÃËÑÉ + `view:cache` äÇÌÍ (Ëã `view:clear`).
- **ÇáÊÌÇæÈ (DOM-grounded — ÈáÇ ÃÏæÇÊ E2E):** ? **375px:** ÎØÃ SKU íÙåÑ **ÏÇÎá äİÓ ÍŞá ÇáÅÏÎÇá** (`@error('sku')` ÊÍÊ input İí ÈØÇŞÉ Codes ÛíÑ ÇáãÍÌæÈÉ — ÏÇÆãğÇ İæŞ/Ãæ ÃÓİá ÒÑøí ÇáÊäŞá¿ áÇ — ÏÇÎá `edz-field` İí ÓØÑåÇ ÇáÎÇÕ) — íãßä ÑÄíÊå Ïæä ÊãÑíÑ ÅÖÇİíº ÚãæÏ ÇáÕæÑÉ `h-10 w-10` ãÖÛæØ æÇáÌÏæá íÍİÙ `overflow-x-auto` (ÃËÈÊå ÇáÇÎÊÈÇÑ ? ÈÍÖæÑ ÇáÜ class) — áÇ ßÓÑ ááÔÈßÉ ? **768px:** ÚãæÏ ÇáÕæÑÉ ãÚÈøÑ æÇáÌÏæá ŞÇÈá ááÊãÑíÑ ÃİŞíğÇ ÚäÏ ÇáÍÇÌÉ ? **1440px:** ÚãæÏ æÇÍÏ ÖíøŞ áÇ íÒíÏ ÚÑÖ ÇáÌÏæá ÇáİÚøÇá (ÇáÊãÑíÑ ÇáÃİŞí íÍæí Çáäãæ ÇáÒÇÆÏ Úä ÇáÚÑÖ). ÇáÊÍŞŞ ÇáíÏæí ÇáÈÕÑí ÇáäåÇÆí (ãØáæÈ ãä ÇáãÓÊÎÏã) íõÏÑÌ ÚäÏ ÇáãÑÍáÉ 5.
- **ÇáÃÏÇÁ:** `buildEditFormData` íÖíİ `variants.images` Åáì ŞÇÆãÉ eager-load ÇáÍÇáíÉ (áÇ N+1 ÚäÏ ŞÑÇÁÉ primary image). `syncExistingVariants`: ÕİÑ ÇÓÊÚáÇãÇÊ ÕæÑ ááãÊÛíÑÇÊ ÛíÑ ÇáãÛíøÑÉ (ÃõËÈÊ ÈÇáÇÎÊÈÇÑ ?)º ÇáãÊÛíøÑ ÇáãÊÛíøÑ = delete+create İŞØ. ÊÍÓøä ÌÇäÈí: `variantsById` ÈÏá `variants()->find()` áßá Õİ ŞÖì Úáì N+1 ÇÓÊÚáÇãÇÊ ÇáãÊÛíÑÇÊ ÇáŞÏíã.
- **ÇáÊæËíŞ:** åĞÇ ÇáŞÓã İí `Todos.md`. áã ÊõãÓø `app/Actions/Product/*` (ãåãÉ ÇáãÑÍáÉ 5). ÇáãÑÇÍá ÇáİÑÚíÉ 3 (ÑÄíÉ ÎØæÇÊ Options ÔÑØíÉ ÍÓÈ `has_variants`) æ4 (ÅäÔÇÁ ÎíÇÑÇÊ/Şíã inline) ÊÈŞì ãÚáøŞÉ ßÈÑæãÈÊÇÊ ãäİÕáÉ.

---

## ÅäÌÇÒ ÇáãÑÍáÉ ÇáİÑÚíÉ 3 ãä 5: ÅÙåÇÑ/ÅÎİÇÁ ÎØæÉ Options ÔÑØíğÇ ÍÓÈ `has_variants` — 2026-09-12

**ÇáäØÇŞ:** ÚäÏ `$has_variants = false` ÊÎÊİí ÊÈæíÈÉ Options ßáíğÇ ãä ÇáãÚÇáÌ İíÕÈÍ ÇáãÓÇÑ: Basic ? Images ? Pricing ? Inventory ? Review (5 ÎØæÇÊ). ÚäÏ ÊİÚíá ÇáãÊÛíÑÇÊ ÊÚæÏ ÇáÊÈæíÈÉ áãæŞÚåÇ ÇáØÈíÚí (Èíä Pricing æInventory). ÇáËæÇÈÊ äİÓåÇ áÇ ÊÊÛíÑ (`STEP_OPTIONS` íÈŞì 4) — İŞØ ÚÖæíÉ ÇáÎØæÉ İí ÇáÊÓáÓá ÇáãÑÆí ÊÊÛíÑ.

**ÇáäŞÇØ ÇáÑÆíÓíÉ:**
- **ÇáãÑÆíÉ İí `ProductWizardSteps`:** `visible(array $context)` = `all()` ãÚ `unset($steps[self::STEP_OPTIONS])` ÚäÏ ÛíÇÈ `has_variants`º `visibleIds()` (ãİÇÊíÍ ÇáŞÇÆãÉ ÇáãÑÆíÉ)º `isStepVisible(int, context)`º `firstVisibleStepBefore(int, context)` (ÃŞÑÈ ÎØæÉ ãÑÆíÉ ŞÈá ÎØæÉ ãÚíäÉ¡ ááÎÑæÌ ãä ÎØæÉ ÇÎÊİÊ). `all()`/`ids()`/`count()`/`isExistingStep()` ÈŞíÊ ßãÇ åí (ÇáŞÇÆãÉ ÇáßÇãáÉ íÍÊÇÌåÇ ÈÚÖ ÇáãÓÊÏÚíä ãËá Mount ÇáÅÕÏÇÑ). ÓíÇŞ ÇáÇÓÊÏÚÇÁ ÈäİÓ Ôßá `['has_variants' => (bool) $this->has_variants]`.
- **`form.blade.php`:** `$wizardSteps` = `visible($context)`º `$lockedSteps` ? `visibleIds($context)`º `$isStepUnlocked` íÊÍŞŞ `isStepVisible` Ëã íãÑø Úáì `visibleIds` İŞØº `nextStep`/`prevStep` ÚÈÑ ãÓÇÚÏ ãÔÊÑß `adjacentVisibleStep($from, $direction)` (íÈÍË Úä ÇáİåÑÓ İí `visibleIds` æíÍÑøß ãæÖÚğÇ¡ íÑÌÚ null ÚäÏ ÇáÍÏæÏ ? íËÈÊ ÇáÎØæÉ) ÈÏá ±1 ÇáÃÚãìº `goToStep` íÚÇãá ÇáÎØæÉ ÇáãÎİíÉ ßÎØæÉ ÛíÑ ãæÌæÏÉ (no-op ÕÇãÊ ÈáÇ toast)º ÎØÇİ `has_variants` ÚäÏ ÅíŞÇİ ÇáÊÔÛíá: Åä ßÇä `currentStep === STEP_OPTIONS` íäŞá Åáì `firstVisibleStepBefore(STEP_OPTIONS, ctx) ?? STEP_PRICING` æíÍĞİ `STEP_OPTIONS` ãä `validated_steps` (ÎØæÉ ãÎİíÉ áÇ ÊõÚÏ "ãõÊÍŞŞğÇ ãäåÇ") Ëã íÕİøÑ `options`/`variants_preview`/`options_changed` ßÇáÓÇÈŞ. ÅÚÇÏÉ ÇáÊİÚíá ÊÚíÏ ÇáÊÈæíÈÉ İŞØ Ïæä ÅÚÇÏÉ ÊÍŞŞ ãä ÇáÎØæÇÊ ÇáãÇÖíÉ (ŞæÇÚÏ Şİá `isStepUnlocked` ÇáŞÇÆãÉ).
- **`form/step-options.blade.php`:** ÍõĞİÊ İÑÚ `@else` (ÇáÜ placeholder ÇáĞí ÃÕÈÍ ãíÊğÇ) æÈŞí `@if ($has_variants)` ÏÑÚğÇ ÏİÇÚíğÇ — áÃä `@foreach ($this->wizardSteps as $wizardStep) @include(...)` ÓíÑİÑ-ÓÇíÏ æáÇ íÚäí ÈÇáÎØæÉ ÇáãÎİíÉ ÃÕáğÇ (áÇ íõÖãøä partial ÛíÑ ÇáãÑÆí ÃÈÏğÇ İí DOM¡ ÎáÇİğÇ áÜ `x-show` ÇáĞí íÈŞí ÇáÚäÇÕÑ ãÚÏøáÉ).
- **`wizard-steps.blade.php`:** ÈáÇ ÊÛííÑ — íßÑøÑ `:steps` ÇáããÑøÑÉº `$wizardSteps` ÃŞÕÑ (=5) æÇáÜ flex (`flex-1` áßá `<li>` ÚÏÇ ÇáÃÎíÑ) íÊßíİ ÊáŞÇÆíğÇ. ÃÒÑÇÑ ÇáÊäŞá İí `form.blade.php` ÊÈŞì `step < {{ count() }}` æ`step === {{ LAST_STEP }}` (=6 ÏÇÆãğÇ) — ÕÍíÍÉ áÃä Review (6) åæ ÏÇÆãğÇ ÂÎÑ ÎØæÉ ãÑÆíÉ æİí ßáÇ ÇáãÓÇÑíä.

**ÈÑæåÇÊ:**
- **ÇáÇÎÊÈÇÑÇÊ (`ProductWizardStepEngineTest.php` — 16 ÇÎÊÈÇÑğÇ¡ +4 ÕÇİò):** ÇÓÊõÈÏá ÇÎÊÈÇÑ "six dynamic steps" ÈÇÎÊÈÇÑíä (5 ÊÈæíÈÇÊ ááãäÊÌ ÇáÈÓíØ ãÚ `not->toContain` áÇÓã Options¡ æ6 ÊÈæíÈÇÊ ÈÇáÊÑÊíÈ ÈÚÏ `set('has_variants', true)`)º ÊÍÏíË "completed steps unlock" Åáì ãÓÇÑ ÇáãÊÛíÑÇÊ (`has_variants=true`)º `walkWizardToInventory` = 3× `nextStep` (áÃäåÇ ÊÊÎØì 4 ÇáÂä) æÊÍÏíË `validated_steps` ÇáãÊæŞÚÉ Åáì `[1,2,3]`/`[1,2,3,5]`º ÃõÖíİ: (a) `visible()`/`visibleIds()`/`isStepVisible()` ÊÓÊÈÚÏ 4 ÈáÇ ãÊÛíÑÇÊ æÊÓÊÚíÏå ãÚåÇ¡ (b) nextStep íŞİÒ İæŞ Options (3?5) æprevStep íÚæÏ (5?3)¡ (c) ÅáÛÇÁ ÇáãÊÛíÑÇÊ ÃËäÇÁ ÇáæŞæİ Úáì Options íäŞá Åáì 3 æíÍĞİ 4 ãä `validated_steps` + `assertNotDispatched('swal')` ááŞİÒ Åáì ÎØæÉ ãÎİíÉ (no-op ÕÇãÊ) + ÅÚÇÏÉ ÇáÊİÚíá ÊÚíÏ ÇáÊÈæíÈÉ ÈÔßá ÍÑ. **22 äÇÌÍğÇ (136 assertion)** ÈÇáãÔÇÑßÉ ãÚ `ProductSkuBarcodeTest` (íÌÈ ÊÔÛíáåãÇ ãÚğÇ — ÎáÇİ Ğáß "undefined function skuUser" ÈÅŞáíãåÇ). **429 äÇÌÍğÇ (1733 assertion)** İí `tests/Feature/Merchant` (425/1699 ÓÇÈŞğÇ = +4 ÇÎÊÈÇÑÇÊ +34 ÊÃßíÏğÇ¡ ÕİÑ ÇäÍÏÇÑ). `php -l` äÙíİ Úáì ÇáÜ PHP ÇáãÊÛíøÑ + `view:cache` äÇÌÍ (Ëãø `view:clear`).
- **ÇáÊÌÇæÈí (DOM-grounded — áÇ E2E İí repo):** ÚÏÏ ÇáÊÈæíÈÇÊ = `count($wizardSteps)` ãä ÇáÜ nav `<ol>`º ÚäÏ b=375px ÊÈŞì ÇáÏæÇÆÑ `h-9 w-9` + ãæÕáÇÊ `mx-3 flex-1` ÏÇÎá `flex items-center` (ÃŞá ÈãÌÇáÊÈæíÈ æÇÍÏ Úä ÇáæÖÚ ÇáÓÇÈŞ ? áÇ ÊÌÇæÒ)º ÚäÏ 768px ÊÙåÑ ÇáÊÓãíÇÊ `sm:inline`º ÚäÏ 1440px íÈŞì ÇáÔÑíØ `flex-1` ãæÒøÚğÇ ÈÇáÊÓÇæí ÚÈÑ ÚÑÖ ÇáÕİÍÉ. ãÚ ÇËÈÇÊ Ãä partial ÇáÎØæÉ 4 ÛíÑ ãæáøÏ İí HTML ÚäÏ `has_variants=false` (áÇ `<div x-show="step === 4">` ÃÕáğÇ).
- **ÇáãáÇÍÙÇÊ:** ÈÕãÉ ÒãäíÉ æÇÍÏÉ İŞØ ÚäÏ ÊÈÏíá `has_variants` (áÇ ÇÓÊÚáÇãÇÊ/ÊÍæíáÇÊ ÌÏíÏÉ — İáÊÑÉ ĞÇßÑÉ Úáì static). áÇ ãÓÇÓ ÈãäØŞ ÇáÌÒÃíä A (ÊÍŞŞ SKU/ÇáŞİÒ) æB (ÕæÑ ÇáãÊÛíÑÇÊ) Ãæ inline (ÇáãÑÍáÉ 4).

**ÇáÊĞííáÇÊ:** áÇ ãÓÇÓ ÈÜ `products/index.blade.php` Ãæ export/import (ÇáãÑÍáÉ 5). ÇáãÑÍáÉ ÇáİÑÚíÉ 4 (ÅäÔÇÁ ÇáÎíÇÑ/ÇáŞíãÉ inline ãä ÏÇÎá ÎØæÉ Options) ÊÈŞì ãÚáøŞÉ ßÈÑæãÈÊÇÊ ãäİÕáÉ ÈÚÏ ÇÚÊãÇÏ åĞÇ ÇáÏãÌ.

---

## ÅäÌÇÒ ÇáãÑÍáÉ ÇáİÑÚíÉ 4 ãä 5: ÅäÔÇÁ ÇáÎíÇÑ/ÇáŞíãÉ inline ãä ÎØæÉ Options + ÊÑŞíÉ ÇáãõäÊŞíÇÊ — 2026-09-12

**ÇáÓíäÇÑíæ:** ÈäÇÁğ Úáì ŞÑÇÑ ÇáãÓÊÎÏã («äİĞ» Úáì ÎØÉ ÇáÊÕãíã ÇáãÚÊãÏÉ): ÒÑ æÇÍÏ «ÎíÇÑ ÌÏíÏ» (+ Plus) İí ÊÑæíÓÉ ÈØÇŞÉ Options ÈÌæÇÑ «ÅÖÇİÉ ÎíÇÑ» — íİÊÍ ÇáãæÏÇá áÅäÔÇÁ ÎíÇÑ ÌÏíÏ (ÈÏá ÒÑ per-row ÇáãÒÏÍã)º ÅÕáÇÍ ÇáãæÏÇá ÇáŞÇÆã ÌĞÑíğÇ (ÇáŞíã ÇáãÖÇİÉ áã Êßä ÊÙåÑ İí draft ÃÕáğÇ + áÇ ØÑíŞÉ áÅÒÇáÉ ŞíãÉ)º ÇÓÊÈÏÇá `<select>` ÇáÃÕáí ÈãäÊŞì ÈÍË `<x-edz.select>` (äãØ ÇáãÔÑæÚ) æŞíã ÇáãÊÛíÑÇÊ ÈãäÊŞì ãÊÚÏÏ ŞÇÈá ááÈÍË ÌÏíÏ `<x-edz.multi-select>` (ÑŞÇÆŞ + æÑŞÉ ÓİáíÉ Úáì ÇáãæÈÇíá) ãÚ ÃíŞæäÇÊ ×4 áÛÇÊ ÌÏíÏÉ Ïæä ÎÑŞ ÇáÊÕãíã (375/768/1440 + Apple Design Restraint).

**ãÇ Êóãø:**
- **ÇáãÍÑß (`form.blade.php`):** `openCreateOption(?int $index = null)` — ÈáÇ æÓíØ íÚíÏ ÇÓÊÎÏÇã Ãæá Õİ İÇÑÛ (`product_option_id` ÎÇáò) Ãæ íáÍŞ ÕİğÇ ÌÏíÏğÇ æíÖÈØ `optionModalRow`º ÈæÓíØ íÍá Åáì Ğáß ÇáÕİ. **ÅÕáÇÍ ÇáÌĞÑ ááãæÏÇá (È):** `addOptionValueInline` ßÇä íäÔÆ ÇáŞíãÉ İí DB áßäå **áÇ íßÊÈåÇ İí `$this->options[$row]['values']`** İÊÈÏæ draft İÇÑÛÉ ÚäÏ ÇáÅÛáÇŞ — ÇáÂä íÏİÚ `$newValue->id` (ãÚ `array_unique`) æíÓÊÏÚí `rebuildPreview()`. **ÌÏíÏ `removeOptionValueInline(string $valueId)`** (ÈæÇÈÉ `PRODUCT_UPDATE`): Åä ßÇäÊ ÇáŞíãÉ ãÓÊÎÏãÉ İí ãÊÛíÑÇÊ (`variants()->exists()`) ? `dispatch('swal', error, product_options.value_in_use)` ÈáÇ ÍĞİº æÅáÇ ÊÍĞİ + ÊõÔØÈ ãä ßá Õİæİ draft + `rebuildPreview()`. `closeOptionModal` íÕİøÑ ÇáÍÇáÉ ßãÇ ŞÈá.
- **`components/edz/multi-select.blade.php` + `components/edz-multi-select.js` (ÌÏíÏÇä):** `<select multiple hidden>` åæ ÚŞÏ Livewire (`wire:model*`=`options.{i}.values` + `wire:change`=valuesChanged)º ÇáãÔÛøá íÚÑÖ ÑŞÇÆŞ (äŞÑÉ ÇáÑŞíŞÉ = ÅÒÇáÉ) + ÔÇÑÉ `+N` (overflow ÚÈÑ `maxVisibleChips`, ÇİÊÑÇÖí 3) + äÕ placeholder ÇáãÈŞíøº ÇááæÍÉ = ÈÍË ãÍáí (ÈÍË ÈÇáßæÏ Åä æõÌÏ `option-code`) + Õİæİ ãÎÊÇÑÇÊ ÈÎÇäÉ `check` (äŞÑÉ ÇáÕİ = ÊÈÏíá¡ æÒÑ Done íÛáŞ) + ÚÏøÇÏº ÊãæÖÚ ËÇÈÊ Úáì ÇáÏíÓßÊæÈ (`position: fixed` ÈÍÓÇÈ trigger rect) ææÑŞÉ ÓİáíÉ Úáì ÇáãæÈÇíá — äİÓ ÚáÇøãÇÊå «ÇáäÖÌ» ßÜ `edz-select` (`data-state`). ãÕİæİÉ `selected` (ãÈÑãÌ íÏæíğÇ ÈÜ`_readSelectedFromDom`/`toggleValue`/`removeValue` — áÇ `x-model` Úáì ÇáÜ select ÇáÎİí áÊÌäÈ ÇáÊÚÇÑÖ ãÚ Livewire) ? `_writeToDom()` íÖÈØ `option.selected` æíÈË `input`+`change` (ÈÇÊÌÇå æÇÍÏº «Livewire/change-event» ßÇİò áãÕİæİÉ). `MutationObserver` íÚíÏ ŞÑÇÁÉ ÇáÇÎÊíÇÑ ÈÚÏ morph ãä Livewire (ãËá `edz-select`). ãõÓÌóøá ßÜ `Alpine.data("edzMultiSelect", ...)` İí `panel.js`. Çáãßæøä íÏÚã `:options` ßÓÌáÇÊ Eloquent/ãÕİæİÇÊ æãİÇÊíÍ ÓáÓáÉ.
- **`step-options.blade.php`:** ÊÑæíÓÉ `edz-card__header` ÈÚäÇÕÑ `flex flex-wrap` — ÒÑ «ÎíÇÑ ÌÏíÏ» (`wire:click="openCreateOption"` ÈáÇ æÓíØ¡ ÃíŞæäÉ `plus`, ÈæÇÈÉ `PRODUCT_CREATE`) Ëã ÒÑ «ÅÖÇİÉ ÎíÇÑ». ãäÊŞì ÇáÎíÇÑ ? `<x-edz.select wire:model="options.{i}.product_option_id" wire:change="optionChanged(i, value)" search icon="cube" size="sm" :options="$this->productOptions->all()" option-value="id" option-label="name" />` (ÎÊÇãíÉ ÈÍË ãÏÚæãÉº `selected` ÛíÑ ãæÌæÏÉ İí `x-edz.select` — ÊõÊæËóøŞ ÈÜ`wire:model`). ÇáŞíã ? `<x-edz.multi-select wire:model="options.{i}.values" wire:change="valuesChanged(i)" search :options="$this->optionValuesByOption->get(id, collect())->all()" :selected="..." size="sm" search-placeholder />`º ÇáİÑÚ TEXT + placeholder (ÇÎÊÑ ÎíÇÑğÇ ÃæáğÇ) ÈÇŞíÇä. ÃõÒíáÊ ÓãÇÊ `for` ãä ÇáÊÓãíÇÊ (ÇáãßæøäÇÊ áÇ ÊÚÑÖ id ŞÇÈá ááÊÑßíÒ Úáì ÇáÜ select).
- **`create-option-modal.blade.php` (ÅÚÇÏÉ ßÊÇÈÉ):** íõÍÊİÙ ÈäåÌ `@if ($optionModalRow !== null)` (ÅÚÇÏÉ ÇáãæäÊÇÌ äÙíİÉ ÚäÏ ßá İÊÍ — áÇ ÍÇÌÉ áäŞá ÈÜ`x-effect`). ÎØæÉ ÇáÅäÔÇÁ: ÍŞá ÇáÇÓã + `<x-edz.select>` áäæÚ ÇáÅÏÎÇá (Şíãå `ProductOptionInputType::options()` = ÎÑíØÉ `value => label` íÏÚãåÇ İÑÚ fallback İí `x-edz.select`) + ÒÑÇ ÍİÙ (check)/ÊÑÇÌÚ. ÎØæÉ ÇáŞíã (ÈÚÏ `optionModalCreatedId`): ÔÈßÉ ãÈÊÏÃÉ ÈÜ«Şíã ÇáÎíÇÑ» + áßá ŞíãÉ ÑŞíŞÉ ŞÇÈáÉ ááÅÒÇáÉ (`x-mark` ? `removeOptionValueInline`) + äãæĞÌ ÅÖÇİÉ `wire:submit="addOptionValueInline"` (ÃíŞæäÉ plus) ÏÇÎá äÕ ÇáÈØÇŞÉ¡ æÒÑ Done (check) İí ÇáÊĞííá. ÃíŞæäÇÊ: `plus`, `check`, `x-mark`, `list-bullet` (ßáåÇ ãæÌæÏÉ İí `x-edz.icon`; **`squares-2x2` ÛíÑ ãæÌæÏÉ** İíõÓÊÚÇÖ ÚäåÇ ÈÜ`list-bullet`).
- **`_forms.scss`:** ÃõÖíİÊ ÃäãÇØ `.edz-multi-select__*` (ÇáÑŞÇÆŞ ÊÍãá ÎáİíÉ `bg-surface-secondary` + ÔÇÑÇÊ ÅÒÇáÉ `x-mark`¡ ÎáíÉ `edz-multi-select__check` ÈÏÇÆÑÊíä ÍÇáÇÊ¡ ÊĞííá ËÇÈÊ `border-top` + ÚÏøÇÏ + ÒÑ Done). ÌÏæá `edz-select--sm` íÚØøí ÇÑÊİÇÚ ÇáÑŞÇÆŞ ÇáãÕÛøÑÉ (ÊÕÍíÍ: ßÇäÊ `edz-multi-select--sm` ÇáÎØÃ).
- **ãİÇÊíÍ ×4 áÛÇÊ (en/ar/fr/es):** `products.select_values` («Select values…» / «ÇÎÊÑ ÇáŞíã…» / «Sélectionner les valeurs…» / «Seleccionar valores…»).

**ÈÑæåÇÊ:**
- **ÇáÇÎÊÈÇÑÇÊ (`ProductWizardStepEngineTest.php` — 23 ÇÎÊÈÇÑğÇ¡ +7 ÕÇİò):** ? `openCreateOption` ÈáÇ æÓíØ íáÍŞ ÕİğÇ İÇÑÛğÇ æíÖÈØ ÇáãæÏÇá¡ æÇáÇÓÊÏÚÇÁ ÇáãÊßÑÑ íÚíÏ ÇÓÊÎÏÇã ÇáÕİ (áÇ ÊßÑÇÑ) ? ÈæÓíØ íÍá Åáì ÇáÕİ ÇáãØáæÈ ? `createOptionInline` íÊÌÇåá ÇáÜ name ÇáİÇÑÛ (ÎØÃ) æíÑÈØ ÇáÎíÇÑ ÇáãäÔÃ İí ÇáÕİ (id/type/values) ? `addOptionValueInline` ÇáÎÇãá ÚäÏ İÑÇÛ ÇáŞíãÉ ÈáÇ ÅäÔÇÁ¡ æÅäÔÇÁ + **ÊÍÏíÏ İæÑí İí draft** + ÕİÑ ÊßÑÇÑ (ÇáŞíãÉ äİÓåÇ ãÑÊíä) ? `removeOptionValueInline` íÍĞİ ÇáŞíãÉ ÛíÑ ÇáãÓÊÎÏãÉ æíÔØÈåÇ ãä draft (ÊÎÊİí ÒÑŞÇÁ æÊÈŞì ÇáÍãÑÇÁ) ? ÇáŞíãÉ ÇáãÓÊÎÏãÉ İí ãÊÛíÑ ? `expect NotNull` + ÊÈŞì İí draft + `assertDispatched('swal', type:'error')` ? ÇáÚÑÖ: ÒÑ «ÎíÇÑ ÌÏíÏ» + `wire:click="openCreateOption"` + `edz-select` + `edz-multi-select` + ÊÑÌãÊÇ `products.select_values`/`placeholder` + ÑÈØÇ `wire:model="options.0.product_option_id"` æ`wire:model="options.0.values"`. **29 äÇÌÍğÇ (171 ÊÃßíÏğÇ)** ãÚ `ProductSkuBarcodeTest` (íõÔÛóøáÇä ãÚğÇ — `skuUser()/dataShape()` íõÚÑóøİÇä åäÇß). `php -l` äÙíİ Úáì ÇááÛÇÊ + `view:cache` äÇÌÍ (Ëã `view:clear`).
- **ÇáÈäÇÁ:** `npm run build` äÇÌÍ — `edzMultiSelect` ãæÌæÏ İí `panel-*.js` ãÍÓæãÉ æÃäãÇØ `edz-multi-select` İí `app-*.css` ÇáãõÖãóøäÉ (ÊÃßíÏ byte-check ÚÈÑ `Contains`).
- **ÇáÊÌÇæÈ (DOM-grounded — áÇ E2E):** ? **375px:** ÊÑæíÓÉ `flex-wrap` ÊáÊİø (ÒÑÇ ÇáÎíÇÑíä İí ÓØÑ ÚäÏ ÇáÖíŞ)º ãäÊŞì ÇáŞíãÉ æÑŞÉ ÓİáíÉ ÚÑíÖÉ ãáÊÕŞÉ ÈÇáÃÓİá ãËá `edz-select` ? **768px:** ÊäÓÌã ÊÑÊíÈ ÇáÈØÇŞÉ (ÚãæÏÇå) æÇáÑŞÇÆŞ ÊÈŞì İí ÓØÑ ÇáãÔÛøá ãÚ ÔÇÑÉ `+N` ? **1440px:** `xl:grid-cols-3` ÍÇæíÉ ÇáÎíÇÑÇÊ ËÇÈÊÉ¡ ÇááæÍÉ ãÖãøäÉ. ÇáÊÍŞŞ ÇáíÏæí ÇáÈÕÑí ÇáäåÇÆí (ãØáæÈ ãä ÇáãÓÊÎÏã) íõÏÑÌ ÚäÏ ÇáãÑÍáÉ 5.

**ÇáãáÇÍÙÇÊ:** áÇ ÇÓÊÚáÇãÇÊ ÌÏíÏÉ (ßá ÇáãÕÇÏÑ `productOptions`/`optionValuesByOption` ãÍÓæÈÉ ŞÇÆãÉ). áÇ ãÓÇÓ ÈÜ `app/Actions/Product/*` Ãæ `products/index.blade.php` Ãæ export/import (ÇáãÑÍáÉ 5).

**ÊÍÏíË ÊÕÍíÍí ÈÚÏ ÇáÊÍŞŞ ÇáíÏæí ááãÓÊÎÏã (äİÓ ÇáÌáÓÉ):**
- **ÅÕáÇÍ «íõÛáŞ ÈÚÏ ÇÎÊíÇÑ ŞíãÉ æÇÍÏÉ» İí `x-edz.multi-select`:** Livewire íÚíÏ ÊåíÆÉ ßá ãßæäÇÊ Alpine ÈÚÏ Ãí ÑÍáÉ ÏÇÆÑíÉ¡ İßÇä `open` íõÕİóøÑ ÈÚÏ Ãæá ÊÍÏíÏ (áÃä `wire:change` ÊÓÊÏÚí `valuesChanged` ? ÅÚÇÏÉ ÑäÏÑ ? morph). ÇáÍá: ÎÑíØÉ æÍÏÉ äãØíÉ `persistence` ÈãİÊÇÍ `modelName` — `_writeToDom()`/`toggle()`/`close()`/ÇáãÓÊãÚ ÇáÎÇÑÌí íßÊÈæä {open, at}¡ æİí `init()` ÊõÓÊÚÇÏ ÇáÍÇáÉ Åä ßÇäÊ ÃÍÏÇËåÇ ÍÏíËÉ (äÇİĞÉ 4 ËæÇäòº Êßİí áÑÍáÉ ÇáÏÇÆÑÉ ÇáãÍáíÉ æÊäÊåí ŞÈá Ãí ÑäÏÑ ÛíÑ ãÊÚáŞ). ßãÇ ÃõÚíÏ ÍÓÇÈ `updatePosition()` ÚäÏ ÇáÇÓÊÆäÇİ ÍÊì áÇ íŞİÒ ÇááæÍ Åáì 0,0.
- **ÅÚÇÏÉ ÊÕãíã ÈæÈ ÃÈ «ÎíÇÑ ÌÏíÏ» (ÇáãæÏÇá):** ÃõÚíÏÊ åíßáÊå áÊİÇÏí «ÇáÃÒÑÇÑ ÇáãáÊÕŞÉ ÈÇáÍŞæá»: ãŞÈÖ æÑŞÉ ÓİáíÉ `edz-modal__handle` İí ÇáÃÚáì (ãæÈÇíá)¡ ÊÑæíÓÉ `edz-card__header` ãÚ ÒÑ X (`buttons.close`) íÓÊÏÚí `closeOptionModal`¡ ãäØŞÉ ÇáÃÒÑÇÑ İí Õİ ÊĞííá ãİÕæá ÈÍÏ Úáæí (`border-t ... pt-4`) ÈÏá ÇáÇáÊÕÇŞ ÊÍÊ ÇáÍŞæá¡ æÒÑ Done İí `edz-card__footer`. ÚÑÖ ÇáŞíã: ÚäæÇä + ÚÏøÇÏ `selected_count`¡ Ëã ÇáÑŞÇÆŞ¡ Ëã ÕäÏæŞ ÅÖÇİÉ ŞíãÉ ÈÍÏæÏ (`border p-4`) ãÚ ÊÓãíÉ «ŞíãÉ ÌÏíÏÉ» æÍŞá ÃÏÎÇá + ÒÑ ÅÖÇİÉ.
- **ÒÑ «ŞíãÉ ÌÏíÏÉ» (quickAddValue) ãÈÇÔÑÉ İí Õİ ÇáÎíÇÑ (ãËá İßÑÉ «ÎíÇÑ ÌÏíÏ»):** İí ÎáíÉ ÇáŞíã ÈÃáíİ `step-options`¡ ÊÍÊ `x-edz.multi-select` ãÈÇÔÑÉ¡ Õİ ÒÑ `+` «ŞíãÉ ÌÏíÏÉ» (`products.new_value`¡ ×4 áÛÇÊ) ãÚ ÍŞá ÃÏÎÇá — íäÔÆ ÇáŞíãÉ İí ÎíÇÑ ÇáÕİ ÚÈÑ ÅÌÑÇÁ Volt ÌÏíÏ `quickAddValue(int $index)` (ÈæÇÈÉ `PRODUCT_UPDATE`º İÇÑÛ ÈáÇ ÎíÇÑ = áÇ-op) æíÎÊÇÑåÇ ÊáŞÇÆíğÇ İí `options.i.values` Ëã `valuesChanged` (ÅÚÇÏÉ ÈäÇÁ ÇáãÚÇíäÉ). `optionValuesByOption` ÇáãÍÓæÈÉ ÊáÊŞØ ÇáŞíãÉ ÇáÌÏíÏÉ İí ÇáãÑÉ ÇáÊÇáíÉ ? ÊÙåÑ İí áæÍÉ ÇáãäÊŞì İæÑğÇ.
- **ÇÎÊÈÇÑÇÊ:** 31 äÇÌÍğÇ (180 ÊÃßíÏğÇ) ãÚ `ProductSkuBarcodeTest` — ÌÏíÏ: ? `quickAddValue` íäÔÆ + íÎÊÇÑ + íÚíÏ ÇáÈäÇÁ (variants_preview=1) ãÚ no-op ÚäÏ İÑÇÛ ÇáÍŞá¡ ? row ÈáÇ ÎíÇÑ íÊÌÇåá. ÇÎÊÈÇÑ ÇáÚÑÖ íÊÍŞŞ ÇáÂä ãä `wire:submit="quickAddValue(0)"` æ`products.new_value`. `php -l` äÙíİ + `view:cache` äÇÌÍ + `npm run build` (ÇáÈÇäá ÇáÌÏíÏ `panel-*.js` íÍãá `edzMultiSelect` æÇáÃäãÇØ İí `app-*.css`).

**ÊÍÏíË ËÇäò — ÅÕáÇÍ «Column sku cannot be null» ÚäÏ ÇáÊÍÏíË:**
- **ÇáÓÈÈ:** `syncVariantRow` İí `form.blade.php` (ÏÇÎá `$syncExistingVariants`) ÊÚíÏ ÕíÇÛÉ ßá ÍŞæá ÇáÕİ Åáì UPDATEº Ãí Õİ preview ÕÇÑ sku/barcode İíå `null` (ÈÚÏ ÅÚÇÏÉ ÈäÇÁ ãä `VariantPreviewBuilder` ÇáÊí ÊæáøÏ rows ÈÃßæÇÏ skeleton İÇÑÛÉ) ßÇä íİÑÖ `UPDATE product_variants SET sku = NULL` ? ÎÑŞ ŞíÏ NOT NULL (1048) ÑÛã Ãä ÇáãÊÛíÑ ÇáãÎÒøä áÏíå ÇáÓßæ.
- **ÇáÊÕÍíÍ:** ÏÇÎá ãÑÔøÍ `$dirty` — ÅĞÇ ßÇä ÇáãİÊÇÍ İí `['sku','barcode']` æÇáŞíãÉ `blank()` ÈíäãÇ ÇáãÊÛíÑ ÇáãÎÒøä áÏíå ŞíãÉ `filled()` ? ÇÓÊÈÚÇÏ ÇáÍŞá ãä ÇáÊÍÏíË (áÇ íõİÓÍ ÇáÃßæÇÏ ÃÈÏğÇ). ÈŞíÉ ÇáÍŞæá (ÇáÓÚÑ/ÇáÊßáİÉ/ÇáãÎÒæä…) ÊõÍÏóøË ßÇáãÚÊÇÏº æåßĞÇ ÊÙá ÇáÊÚÏíáÇÊ ÇáÈÓíØÉ ÊÚãá æÇáÃßæÇÏ ËÇÈÊÉ.
- **ÇÎÊÈÇÑ:** «editing a variable product never wipes an existing variant sku to null» — ÚÈÑ `makeVariableProduct`¡ ŞõÏöøãÊ preview rows ÈÓßæ/ÈÇÑßæÏ null æŞíã ÃÓÚÇÑ ÌÏíÏÉ Ëã `save()`: áÇ ÇÓÊËäÇÁ¡ ÇáÓßæ/ÇáÈÇÑßæÏ ãÍİæÙÇä¡ æÇáÃÓÚÇÑ æÇáãÎÒæä ÇáãÍÏøËÇä ãÍİæÙÇä¡ æáÇ ÒíÇÏÉ/ÍĞİ İí ÇáãÊÛíÑÇÊ (2). ãáÇÍÙÉ: ÇáÓßæ áíÓ editable İí ÇáæÇÌåÉ (íæÌÏ İŞØ Óßæ ÇáãäÊÌ İí ÎØæÉ ÇáãÎÒæä) áĞÇ ÇáÓÈíá ÇáæÍíÏ ááæÕæá åæ Õİ preview ÃÚíÏ ÈäÇÄå ÏÇÎáíğÇ ÈÒÑ null — æåæ ãÇ íÛØíå ÇáÇÎÊÈÇÑ. **ÇáÅÌãÇáí: 32 äÇÌÍğÇ (190 ÊÃßíÏğÇ) ãÚ `ProductSkuBarcodeTest`.**

## ÃíŞæäÇÊ ÎØæÇÊ ÇáãÚÇáÌ + ÅÌÇÈÉ ãÑÍáÉ ÇáãÎÒæä (ÈÚÏ ãæÇİŞÉ ÇáãÓÊÎÏã) — 2026-09-13

- **ÓÄÇá ÇáãÓÊÎÏã (ãÑÍáÉ ÇáãÎÒæä áãäÊÌ ãÊÛíÑÇÊ):** ÇáÊÍŞŞ ÇáÍí (`step-inventory`/`step-options`/`OrderRules`) — ÚäÏ `$has_variants=true` ÊõÓÊÈÏá ÈØÇŞÉ «ÇáÊÓÚíÑ æÇáãÎÒæä» ÈÑÓÇáÉ `products.variants_hint` İŞØ¡ æãÎÒæä ßá ãÊÛíÑ íõÍÑóøÑ İí ÎØæÉ ÇáÎíÇÑÇÊ (`variants_preview.*.stock`/`.low_stock_threshold` + «ÊØÈíŞ Úáì Çáßá»). **áÇ íæÌÏ ÌãÚ**º ŞÕø ÓØÑ ÇáØáÈ ÈãÎÒæä Ğáß ÇáãÊÛíÑ äİÓå (`OrderRules.php` `$caps[] = (int) $variant->stock`)¡ æÇáÊæİÑ İí ÇáæÇÌåÉ áßá Óßæ. ÇáŞÑÇÑ ÇáãÚÊãÏ: ÅÈŞÇÁ ÇáÅÏÇÑÉ áßá ãÊÛíÑ (ÈáÇ ãÌãæÚ) — ÇáãÓÊÎÏã İæøÖ ÇáÊäİíĞ ááÎØÉ ÇáãõæÕì ÈåÇ.
- **ÇáÃíŞæäÇÊ:** ßÇäÊ ÊÚÑíİÇÊ `ProductWizardSteps::all()` ÊÍãá ÃíŞæäÇÊ áã ÊõÚÑÖ ŞØ — ÇáÏæÇÆÑ ßÇäÊ ÊÚÑÖ ÇáÑŞã İŞØ æÇáÊÓãíÉ ãÎİíÉ <sm (ÏÇÆÑÉ ÈáÇ ãÚäì İí ÇáãæÈÇíá). ßĞáß `currency-dollar` æ`archive-box` **ÛíÑ ãæÌæÏÊíä** İí ÓÈÑíÊ `edz/icon` İÊäßÓÑ ÈÕãÊ Åáì `grid`. ÇáÊÕÍíÍ: `currency-dollar?banknotes` (Pricing) æ`archive-box?cube` (Inventory)º ÈÇŞí ÇáÃíŞæäÇÊ ãæÌæÏÉ İÚáğÇ (`information-circle`/`image`/`adjustments`/`check-circle`).
- **`wizard-steps.blade.php`:** ÏÇÆÑÊÇ ÇáÍÇáíÉ/ÇáŞÇÏãÉ ÊÚÑÖÇä ÇáÂä `<x-edz.icon :name="$step['icon']" class="h-5 w-5">` ÈÏá ÇáÑŞãº ÇáãßÊãáÉ ÊÈŞí `check-circle` æÇáãŞİáÉ `lock-closed`. ÃõÖíİ `aria-label`+`title` (= ÇÓã ÇáãÑÍáÉ) Úáì ÇáÃÒÑÇÑ ÇáãİÊæÍÉ æ`title` Úáì ÇáãŞİáÉ (æÕæáíÉ ßÇãáÉ İí ÇáãæÈÇíá ÈáÇ ÖÌíÌ ÈÕÑí). **ÇáåäÏÓÉ ËÇÈÊÉ 1:1** (ÏÇÆÑÉ `h-9 w-9` + ÃíŞæäÉ `h-5`¡ æÇáÊÓãíÇÊ `hidden sm:inline`) ? ÕİÑ ÅÚÇÏÉ ÊÏİŞ ÚäÏ 375/768/1440º áÇ ÇÓÊÚáÇãÇÊ/JS ÌÏíÏÉº áÇ ãÓÇÓ ÈÜ `form.blade.php` Ãæ Ãí ÕİÍÉ index (ÈäÏ 8 ãÍİæÙ).
- **ÇÎÊÈÇÑ ÌÏíÏ:** «nav renders each step icon glyph with an accessible label in place of the raw number» — æÖÚ ÊÚÏíá áãäÊÌ ãÊÛíÑ (ßá ÇáÎØæÇÊ ãİÊæÍÉ)º áßá ÎØæÉ: `aria-label`/`title` = ÇáÊÓãíÉ + ÍáŞÉ SVG ÇáÎÇÕÉ ÈÃíŞæäÊåÇ (ÈÇÏÆÉ `d` ããíÒÉº ÊõËÈÊ ÚÏã ÇáÓŞæØ Åáì `grid`)¡ æáÇ Ãí `d` áÜ grid ÈÃí ÏÇÆÑÉ¡ æÚÏÏ `<svg` = ÚÏÏ ÇáÎØæÇÊ. **ãáÇÍÙÉ Pest 3.8.5:** `toContain(...$needles)` **variadic ÈáÇ æÓíØ message** — Ãí ÑÓÇáÉ ËÇäíÉ ÊõÚÏø needle ÅÖÇİíğÇ æÊİÔá ãÖááÉº áĞáß ÊõÓÊÎÏã `expect(str_contains(...))->toBeTrue($message)` ááÑÓÇÆá ÇáÍÇãáÉ ááÓíÇŞ.
- **ÇáÊÍŞŞ:** **43 äÇÌÍğÇ (241 ÊÃßíÏğÇ)** İí `ProductWizardStepEngineTest` + `ProductSkuBarcodeTest` (ÕİÑ ÇäÍÏÇÑº ÇáÓÇÈŞ 42/220 +1/…). `php -l` äÙíİ (æÍĞİ ãáİ İÍÕ ãÄŞÊ `BisectScratchTest.php`) + `view:cache` äÇÌÍ (Ëã `view:clear`). ÇáÊÍŞŞ ÇáíÏæí ÇáÈÕÑí (375/768/1440) ãØáæÈ ãä ÇáãÓÊÎÏã.

## Sub-phase A — Order Validation Parity Fix — 2026-09-13

- **Status:** completed
- **ÇáäØÇŞ (ÈãæÇİŞÉ ÇáãÓÊÎÏã ÇáãÚÊãÏÉ):** ÊÕÍíÍ ÊßÇİÄ ÇáİÇáíÏíÔä İŞØ Èíä ãÓÇÑÇÊ ÇáÅäÔÇÁ/ÇáÊÚÏíá/ÇáÊÊÈÚ — ÈáÇ ÍŞæá ÌÏíÏÉ¡ ÈáÇ UI¡ ÈáÇ ãßæøäÇÊ¡ ÈáÇ áãÓ `Order::DEFAULT_MAX_WEIGHT_KG` (íÊÈÚ Sub-phase áÇÍŞÉ).
- **ÇáãáİÇÊ ÇáãÊÃËÑÉ (äØÇŞÇÊ ÎØæØ ãÊÍŞŞÉ):**
  - `resources/views/livewire/merchant/orders/index.blade.php` — `$submitCreate` (4279+) / `$submitEdit` (4494+):
    - submitCreate: ÓŞİ `discount_value` % (closure percent>100 ? `merchant_panel.discount_percent_max`) ÚäÏ 4314.
    - submitEdit: `$maxWeightKg = Order::resolveMaxWeightKg(...)` (4519)º ÓŞİ ÇáÎÕã % (4543)º `phone_secondary` (4552) + `weight_kg` (4553) + `notes` (4554)º ÑÓÇáÉ `weight_kg.max` (4556)º İÍÕ `office_required_for_stopdesk` ãØÇÈŞ ÊãÇãğÇ áÜ submitCreate (4573-4576).
  - `app/Livewire/Concerns/TrackingRiderFormConcern.php` — `submitEdit` (827): ÅÖÇİÉ `notes` (864) + `phone_secondary` (865) ÈäİÓ ŞæÇÚÏ ãÚÑøİ submitCreate (`nullable|string|max:20|regex:/^0[5-7]\d{8}$/`º ÇáæÒä ßÇä ãæÌæÏğÇ ÃÕáğÇ ÚäÏ 859).
- **æÓæã TEMP-PATCH ÇáãÖÇİÉ:** **7** («TEMP-PATCH (Sub-phase A, 2026-09-13)…») — 6 İí index.blade.php (4313, 4519, 4536, 4551, 4556, 4573) + 1 İí TrackingRiderFormConcern.php (863).
- **`index.blade.php` ÚÏÏ ÇáÃÓØÑ: 5606 ? 5642** (+36¡ ÒíÇÏÉ ãÍÏæÏÉ æãÈÑøÑÉ: closure ãÊÚÏÏ ÇáÃÓØÑ ×2 + ŞæÇÚÏ + İÍÕ).
- **äÊíÌÉ ÇáÇÎÊÈÇÑÇÊ (ÃãÑ Pest):** `pest tests/Feature/Merchant/OrderWeightAutoCalcTest.php OrderOfficeSelectionTest.php OrderInlineFieldEditTest.php TrackingGridBatchTest.php` ? **59 äÇÌÍğÇ (234 ÊÃßíÏğÇ)** İí 83.15s — ÕİÑ ÇäÍÏÇÑ¡ æ`OrderWeightAutoCalcTest` äÇÌÍ **Ïæä ÊÚÏíá** (áã ÊõãÓø ÇáÜ 50/100 ÇáÇİÊÑÇÖíÉ åäÇ). `git status`: ãáİÇä ãõÚÏóøáÇä İŞØ¡ **ÕİÑ ãáİÇÊ ÌÏíÏÉ**.
- **ãÄÌøá Åáì ãÑÍáÉ ÇáÜ refactor (æÖÚ ÚáÇãÉ İí ÇáÊŞÑíÑ):** ? ÊæÍíÏ ŞÇÚÏÉ ÓŞİ ÇáÎÕã % İí trait/concern ãÔÊÑß ÈÏá ÇáÊßÑÇÑ inline İí 3 ãÓÇÑÇÊº ? äŞá ÍŞá ÇáæÒä ãä ÇáãæÏÇá Åáì ÇáãáÎÕ ÇáãÇáíº ? ãØÇÈŞÉ ÓŞİ ÇáÎÕã % æİÍÕ ÇáãßÊÈ İí ãÓÇÑ ÇáÊÚÏíá ÇáÎÇÕ ÈÇáÜ tracking grid (áã íõáãÓ ÎÇÑÌ äØÇŞ ÇáŞÑÇÑ ÇáãÚÊãÏ)º ? ÈäÏ ÇáÊÚÏíáÇÊ 2-8 ãä ÇáÎØÉ (ÇáæÒä İí ÇáãáÎÕ¡ ÇáÚÑæÖ/ÓÚÑ ÇáãŞÇÑäÉ¡ ÇáŞÇÆãÉ ÇáãæÍÏÉ ÈİÇÕá¡ /delivery¡ stopdesk¡ ÅÚÇÏÉ ÊÑßíÈ cascade) — áã ÊÈÏÃ ÈÃíòø ãäåÇ.
- **ÊÍŞŞ íÏæí ãØáæÈ ãä ÇáãÓÊÎÏã:** ÑİÖ ÎÕã %=150 ÈãÓÇÑÇÊ ÇáÅäÔÇÁ/ÇáÊÚÏíá (10% ÇÖÛØ ÈÚÏ ÈäÇÁ ÇáæÇÌåÉ ÃãÇã ÇáãÊÕİÍ).

## ãÈÇÏÑÉ ÇáãÚÇáÌ/İåÑÓ ÇáãäÊÌÇÊ — ÇáãÑÍáÉ ÇáİÑÚíÉ 5 ãä 5: Export/Import (æÇÌåÉ İŞØ + «ŞÑíÈğÇ») — 2026-09-13

- **Status:** completed (ãÈÇÏÑÉ ÇáãÚÇáÌ/İåÑÓ ÇáãäÊÌÇÊ ãßÊãáÉ ÈãÔÇÑİåÇ ÇáÎãÓ).
- **ÇáäØÇŞ (ÈãæÇİŞÉ ÇáãÓÊÎÏã):** æÇÌåÉ ÊÕÏíÑ/ÇÓÊíÑÇÏ **ÊÌÑíÈíÉ UI-only** İí ÕİÍÉ İåÑÓ ÇáãäÊÌÇÊ `merchant.products.index` — ÒÑÇ ãÔÛøáíä ÏÇÎá `<x-slot:actions>` + `@include` æÇÍÏÉ¡ æßá ÚáÇãÇÊ ÇáãæÏÇá İí partial ãÎÕÕ. **ÕİÑ ãäØŞ/ãÓÇÑÇÊ/æÍÏÇÊ ÊÍßã** ÊÕÏíÑ/ÇÓÊíÑÇÏ — ÇáÃÒÑÇÑ ÏÇÎá ÇáãæÏÇá ãõÚØøáÉ (`disabled` ÃÕíáğÇ¡ `.edz-btn:disabled` íÚØí `opacity: .55; pointer-events: none` ãä `_buttons.scss:26` — áÇ no-op ÕÇãÊ) ãÚ ãáÇÍÙÉ «ŞÑíÈğÇ» ÙÇåÑÉ (`clock` + `text-sm text-ink-soft`) ÈãİÊÇÍí `export_coming_soon`/`import_coming_soon`.
- **ÇáÕáÇÍíÇÊ (ŞÑÇÑ + ãÓÈÈ):** ÒÑÇ ÇáãÔÛøá ãŞíÏÇä — Export ÈÜ`PRODUCT_VIEW` (ÊÕÏíÑ = ŞÑÇÁÉ ÈíÇäÇÊ ÇáãäÊÌ¡ æmount ÇáÕİÍÉ íÜ abort ÈÏæäå) æImport ÈÜ`PRODUCT_CREATE` (ßÊÇÈÉ/ÅäÔÇÁ¡ íØÇÈŞ ÈæÇÈÉ «ãäÊÌ ÌÏíÏ») — ßŞíãÊíä closure `$canExport`/`$canImport` İí ÓßÑÈÊ Volt.
- **ÇáÈäíÉ (ŞÇÚÏÉ áÇ ÊõäÇŞÔ):** ÕİÑ ÚáÇãÇÊ export/import ãÖãøäÉ İí `index.blade.php` — İŞØ (Ã) ÒÑÇ ÇáãÔÛøá ÏÇÎá `<x-slot:actions>` ÇáæÌæÏ¡ (È) ÓØÑ `@include('livewire.merchant.products.index.export-import-modal')` æÇÍÏ ŞÈá `</div>` ÇáÃÎíÑÉº ßá ÚáÇãÇÊ ÇáãæÏÇáíä İí `resources/views/livewire/merchant/products/index/export-import-modal.blade.php` (ãáİ æÇÍÏ¡ ÇáãæÏÇáÇä¡ ßáñø ãÍãí ÈÜ`@if ($show_export_modal)`/`@if ($show_import_modal)`) — íØÇÈŞ ÓÇÈŞÉ ÇáãÚÇáÌ `form.blade.php` + `create-option-modal.blade.php` (`@if`-guard + `preventClose` + `$set` ááÅÛáÇŞ).
- **Óáæß ÇáãæÏÇá:** `x-edz.modal :isOpen="true" :showCloseButton="false" :preventClose="true" size="lg"` — ßá ãÓÇÑÇÊ ÇáÅÛáÇŞ (X İí ÇáÊÑæíÓÉ/ÅáÛÇÁ) ÊÓÊÏÚí `$set('...', false)`º áÇ ÇÚÊãÇÏ Úáì backdrop/Escape (ÓíäßÓÑ ÊÒÇãä state). SCSS `_modal.scss` íæİøÑ ÈÇáİÚá bottom-sheet ÈÚÑÖ ÇáÔÇÔÉ (<640px) / ßÑÊ ããÑßÒ (?640px¡ `max-width:60rem` áÜ lg) — ÕİÑ ÊÛííÑÇÊ ãØáæÈÉ. ãÍÏÏÇÊ ÏÇÎáíÉ ÎÇãáÉ (radio-cards ÈáÇ ÑÈØ Livewire): export = ÊäÓíŞ CSV/Excel (CSV `checked`) + äØÇŞ Çáßá/ÇáãİáÊÑ/ÇáãÍÏÏ (`@disabled(empty($selected))` + `opacity-60` + «ÇáãÍÏÏ (:count)») ÚÈÑ `has-[:checked]:border-accent-500 has-[:checked]:bg-accent-50` + `accent-accent-600` (Tailwind 3.4¡ accent ãæÌæÏ İí config)º import = dropzone Alpine-only (`x-data="{ dragging: false }"`¡ `@dragover/dragleave/drop` ÈÕÑí İŞØ) + «ÊÕİÍ ÇáãáİÇÊ» ãõÚØøá + «ÊÍãíá ÇáŞÇáÈ» ãõÚØøá.
- **ÊÑÌãÉ:** ãİÇÊíÍ ÌÏíÏÉ ×20 İí `resources/lang/{en,ar,fr,es}/products.php` ÈÅÏÑÇÌ ÃÈÌÏí (äİÓ ÇáãæÇÖÚ ÇáäÓÈíÉ) — `coming_soon`+`export_*`(title/desc/format/format_hint/scope/scope_hint/csv/csv_desc/excel/excel_desc/all/filtered/selected/coming_soon)+`import_*`(title/desc/dropzone_hint/browse/choose_file/template/template_hint/coming_soon). es ãáİ ÈáÇ `edit_product` İÊõËÈóøÊ ÇáßÊáÉ Úáì `description_label`/`featured`º ÊÍŞøŞ Âáí ááÛÇÊ ÇáÃÑÈÚ: OK (en/ar/fr 177 ãİÊÇÍğÇ¡ es 109) — ÕİÑ ãİÇÊíÍ äÇŞÕÉ.
- **ÇáÇÎÊÈÇÑ (`tests/Feature/Merchant/ProductIndexExportImportTest.php` — 6 äÇÌÍÉ¡ 38 ÊÃßíÏğÇ):** ? ãÇáß íÑì ÒÑÇ Export+Importº ? ãæÙİ view-only (`syncPermissions([PRODUCT_VIEW])`) íÑì Export Ïæä Import/ãäÊÌ ÌÏíÏº ? ãæÙİ ãŞÕæÑÉ ÕáÇÍíÇÊå Úáì `ORDER_VIEW` İŞØ ? `canStore(PRODUCT_VIEW)` false + `assertForbidden()` Úáì ÇáãÓÇÑ (ãáÇÍÙÉ Spatie: ÏæÑ staff íãáß `products.view` ÌæåÑíğÇ áåĞå ÇáãÑæäÉ¡ İÜ403 ÇáÍŞíŞí ÚÈÑ ÇáÚÖæíÇÊ ÇáãŞÕæÑÉ ÍÓÈ Decision #6)º ? ÇáãæÏÇá export íİÊÍ/íÛáŞ ÚÈÑ `$set('show_export_modal', …)` + íİÍÕ ÇáÊÑæíÓÉ æ`'coming soon'` (ÍÓÇÓ ááÍÇáÉ) æÇáİÆÇÊ ÇáãÊÌÇæÈÉ `grid grid-cols-1 gap-2 sm:grid-cols-2`/`sm:grid-cols-3` æ`accent-accent-600` æÇáÒÑ ÇáÃÕáí `disabled` æ«ãÍÏÏ (0)»º ? ÇáãæÏÇá import íİÊÍ/íÛáŞ + dropzone/ÊÕİÍ/ŞÇáÈ/ÒÑ ãÚØøáº ? ÇáãæÏÇáÇä ÛíÑ ãõËÈøÊíä (`assertDontSee`) ÍÊì ÇáİÊÍ¡ æÇáÊŞáíÈ ãÓÊŞá. **ÇáãÑÇİŞÉ (ÊÔÛíáåÇ ãÚğÇ):** `ProductWizardStepEngineTest` + `ProductSkuBarcodeTest` ? **43 äÇÌÍÉ (241 ÊÃßíÏğÇ)** — ÕİÑ ÇäÍÏÇÑ. `view:cache` äÇÌÍ (Ëã `view:clear`). `php -l` äÙíİ Úáì index.blade.php + partial + ãáİ ÇáÇÎÊÈÇÑ + ÇááÛÇÊ ÇáÃÑÈÚ.
- **ÇáÊÌÇæÈ (ãÓÊæì ÇáßæÏ/SSR — ÈáÇ ÃÏæÇÊ E2E):** ? **375px:** ÓØÑ ÇáÅÌÑÇÁÇÊ `.edz-page-head__actions` åæ `flex wrap gap:.5rem` İíõáÊİø ÇáÒÑøÇä ÈÓáÇãÉº ÇáãæÏÇá íÊÍæøá bottom-sheet ÈÔÈßÊí radio ãÊßÏÓÊíä `grid-cols-1` æÊĞííá `flex-wrap`º ? **768px:** ÇáßÑÊ ÇáããÑßÒ (?640px) + ÊäÓíŞ 2 ÃÚãÏÉ `sm:grid-cols-2` + äØÇŞ 3 ÃÚãÏÉ `sm:grid-cols-3`º ? **1440px:** ßÑÊ `max-w-60rem` ãÑÊßÒ + ÇáÔÈßÇÊ 2/3 ÃÚãÏÉ. ÇáãÊÍŞóøŞ ÂáíğÇ: ÇáİÆÇÊ ÇáãÊÌÇæÈÉ ãæÌæÏÉ İí HTML ÇáãõÚÇíä (ÇáÇÎÊÈÇÑÇä ?/?) + ãÑÇÌÚÉ `_modal.scss`/`_buttons.scss`. **ÇáÊÍŞŞ ÇáÈÕÑí ÇáíÏæí ááãÑÇÍá ÇáİÑÚíÉ 3 æ4 (375/768/1440 ÃãÇã ÇáãÊÕİÍ) ãÇ ÒÇá ÛíÑ ãäİóøĞ — ãØáæÈ ãä ÇáãÓÊÎÏãº áÇ ÃõæÓã ÈÇáÇßÊãÇá.**
- **ãÄÌóøá (ãÈÇÏÑÉ ãÓÊŞáÉ ãŞÊÑÍÉ):** ÇáÎáİíÉ ÇáÍŞíŞíÉ ááÊÕÏíÑ/ÇáÇÓÊíÑÇÏ (CSV/Excel¡ ŞæÇáÈ¡ ÍÏæÏ¡ `Jobs`¡ ÊÏÑíÌ) — ÛíÑ ãäİóøĞÉ æáã ÊõáãÓ Ãí ãáİÇÊ `app/Domains/Product/` ÎÇÑÌ ÓÇÈŞÉ ÇáãÚÇáÌ ÇáŞÇÆãÉº Úãá ÛíÑ ãÑÊÈØ ŞÇÆã (ÓÈÇŞ ÍÏ ÇáæÒä¡ ÚÑæÖ orders¡ `OrderWeightAutoCalcTest`¡ åÌÑÊÇä İÇÆŞÊí `2026_09_12_100001_*`/`100002_*`) áã íõáãÓ.

## ãÑÍáÉ ÇáÅÕáÇÍ: ÅÙåÇÑ ÊäÈíåÇÊ ÇáÊÍŞŞ İí ãæÏÇá ÇáØáÈ (Order Form Validation Display) — 2026-09-13

- **Status:** ãßÊãá
- **ÇáÔßæì (ÇáÏáíá):** İí ÈæÈ ÃÈ ÅäÔÇÁ/ÊÚÏíá ÇáØáÈíÉ áÇ íõÚÑÖ Ãí ÊäÈíå İí Ãí ÍŞá ÚäÏ ÇáÅÑÓÇá — ÍÊì ÈÚÏ ÅÒÇáÉ `required` ãä ÇáæÇÌåÉ íÈŞì ÇáÕãÊ. ÇáÓÈÈ ÇáÌĞÑí: `Validator::make($this->form, …)->validate()` ÇáãÓØøÍ íÖÚ ÓáÉ ÇáÃÎØÇÁ ÈãİÇÊíÍ ÇáŞæÇÚÏ ÇáÎÇã (`customer_name`, `weight_kg`, …) ÈíäãÇ ÇáŞæÇáÈ ÊÚÑÖ ÈÜ`@error('form.*')` İŞØ ? áÇ ÊØÇÈŞ ? ÕİÑ ÑÓÇÆá. ßĞáß `Utils::hasProperty()` (= `property_exists(ŞÈá Ãæá äŞØÉ)`) íõİáÊÑ ÇáãİÇÊíÍ ÇáãÓØøÍÉ ãä memo ÇáÌæáÉ ÈíäãÇ `form.*` ÊõÍİÙ (ÇáÎÇÕíÉ `form` ãæÌæÏÉ). ÇáÑÌæÚ Åáì ãÑÌÚíøÉ Livewire: ÓáÉ ÇáÃÎØÇÁ ÊõãáÃ ÈãİÇÊíÍ ÃÓãÇÁ ÇáŞæÇÚÏ ßãÇ ÕíÛÊ.
- **ÇáÍá (ÇáãÓÇÑ ÇáÃÕíá):** ÇÓÊÈÏÇá ãÓÇÑÇÊ submit ÇáËáÇËÉ ÈÜ**`$this->validate()` ÈŞæÇÚÏ `form.*`** — íÏÎá ÇáãÓÇÑ ÇáÃÕáí áÜ Livewire: Ñãí `ValidationException` íáÊŞØå ÎØÇİ `SupportValidation::exception` (ÓáÉ `form.*` ãÑİæÚÉ İí memo ÇáÌæáÉ) æíãáÃ `testing.validator` İí ÇáÇÎÊÈÇÑÇÊ¡ ææÓæã `@error('form.*')` ÊØÇÈŞå. (äãæĞÌ catch?`addError` æõÌÏ áÇÍŞğÇ Ãäå áÇ íãáÃ `testing.validator` æßÓÑ ÇáÇÎÊÈÇÑÇÊ — ÑõİÖ).
- **ÇáÈíÇäÇÊ/ÇáÃÓØÑ:** `index.blade.php` — submitCreate ~4289¡ submitEdit ~4522º `TrackingRiderFormConcern::submitEdit` ~843 (ŞæÇÚÏ ãØÇÈŞÉ). `addError('stopdesk_point_id', …)` ? `addError('form.stopdesk_point_id', …)` İí ÇáãÓÇÑíä (index 4354/4599) áÑÓÇáÉ «ãßÊÈ ãØáæÈ» ÊÍÊ ÍŞá ÇáãßÊÈ. æÓæã TEMP-PATCH ÌÏíÏÉ: 3 (æÇÍÏÉ İæŞ ßá ßÊáÉ validate).
- **æÓæã @error ÇáãÖÇİÉ:** `form.phone_secondary`, `form.address`, `form.shipment_type`, `form.weight_kg`, `form.items`, `form.notes` İí `order-form-modal.blade.php` (+18 ÓØÑğÇ) + `form.discount_value` ÊÍÊ ãÍÑÑ ÇáÎÕã İí `order-financial-summary.blade.php` — ÍÊì ÓŞİ `%>100` ãä Sub-phase A íÙåÑ ÇáÂä ÑÓÇáÉ ÊÍÊ ÇáÍŞá.
- **ÇáÇÎÊÈÇÑÇÊ:** ÊÑŞíÉ ãİÇÊíÍ `assertHasErrors` ÇáãÓØøÍÉ Åáì `form.*` (`OrderWeightAutoCalcTest` ×4: `['form.weight_kg' => 'max']`º `OrderOfficeSelectionTest`: `['form.stopdesk_point_id']`) + ÇÎÊÈÇÑ ÊÑÇÌÚ ÌÏíÏ «create-modal field errors resolve under the form.* namespace» íÊÍŞŞ ãä `form.customer_name/required`, `form.customer_phone/required`, `form.items/required`, `form.phone_secondary/regex` ãÚ ÕİÑ ØáÈÇÊ ãÎáæŞÉ.
- **ÃÏáÉ ÇáÊÔÛíá (ÈãİÓöøÑ PHP 8.3 ÇáÕÑíÍ):** ÇáÈÇŞÉ ÇáÃÑÈÚ (OrderWeightAutoCalcTest + OrderOfficeSelectionTest + OrderInlineFieldEditTest + TrackingGridBatchTest) = **60 äÇÌÍÉ (247 ÊÃßíÏğÇ)** 72.67sº ÇáÅÖÇİíÊÇä (OrderFinancialSummaryTest + OrderQuantityCapTest) = **13 äÇÌÍÉ (49 ÊÃßíÏğÇ)** 30.55s. ÇáÅÌãÇáí **73 äÇÌÍÉ (296 ÊÃßíÏğÇ)**¡ ÕİÑ ÇäÍÏÇÑ. `view:cache` äÇÌÍ (íõËÈÊ ÓáÇãÉ ÕíÇÛÉ ÇáÈáÇÏ) Ëã `view:clear`. `git status`: 7 ãáİÇÊ İŞØ ãä Úãáí (index.blade.php¡ TrackingRiderFormConcern.php¡ order-form-modal¡ order-financial-summary¡ ÇáÇÎÊÈÇÑÇä¡ Todos.md) — ÈÇŞí ÇáÔÌÑÉ (ãäÊÌÇÊ/ãÚÇáÌ/ÊÕÏíÑ-ÇÓÊíÑÇÏ…) Úãá ãæÇÒò ááãÓÊÎÏã áã ÃáãÓå.
- **ãáÇÍÙÉ ÊÔÛíá:** `vendor\bin\pest` íÓÊÏÚí PHP 8.2 ãä PATH æíİÔá ÈİÍÕ ÇáãäÕÉ — ÇáÊÔÛíá ÇáÕÍíÍ ÏÇÆãğÇ ÚÈÑ `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe vendor\bin\pest …`.
- **ãÄÌóøá/ãØáæÈ:** ÊÍŞŞ ÇáãÓÊÎÏã ÇáÈÕÑí (375/768/1440 + ÙåæÑ ÑÓÇÆá ÇáÍŞæá ÊÍÊ ßá ÍŞá + ÓŞİ %=150) ÃãÇã ÇáãÊÕİÍ.

---

## ÚäŞæÏ ÊÍÓíäÇÊ ÊæáíÏ ÇáÚÑæÏ — ÇáãÑÇÍá ÇáİÑÚíÉ 2–6 (ÈÚÏ ÅÕáÇÍ ÚÑÖ ÇáÊäÈíåÇÊ) — 2026-09-13

ÇÎÊíÇÑ ÇáãÓÊÎÏã: **ÇáãÑÇÍá ÇáÎãÓ ßáåÇ ãÚÊãÏÉ** (ÃÏÇÉ ÇáÃÓÆáÉ) — (2) ÓŞİ ÇáæÒä¡ (3) ÇáÎÕã¡ (4) ÇáŞÇÆãÉ ÇáãæÍÏÉ¡ (5) ÃäæÇÚ ÇáÔÍä¡ (6) stopdesk. ÇáÃØÑ ÇáÃÑÈÚÉ ÇáãÍÓøäÉ ÃÚáÇå åĞÇ ÇáŞÓã.

### ÇáãÑÍáÉ ÇáİÑÚíÉ 2 ? — ÓŞİ ÇáæÒä ÇáÇİÊÑÇÖí 100 ßÛ
- `app/Models/Orders/Order.php`: `DEFAULT_MAX_WEIGHT_KG` 50?**100** (ÊÚáíŞ ãõÍÏóøË — ÇáÓŞİ ÇáÇİÊÑÇÖí íõØÈŞ ÈáÇ ÔÑßÉ¡ æÇáÓŞİ ÇáãÎÕøÕ ãä `resolveMaxWeightKg(providerId)` íÍá ãßÇäå)º `resolveMaxWeightKg` (~99-118) ÈáÇ ÊÛííÑ ãäØŞí.
- ÊÑÌãÇÊ `merchant_panel.max_weight_hint` ×4 áÛÇÊ (ar/en/fr/es): «ÌÑø 100 ßÛ».
- `tests/Feature/Merchant/OrderWeightAutoCalcTest.php` — 4 ÇÎÊÈÇÑÇÊ ãÍÏóøËÉ ÇáÃÓãÇÁ æÇáŞíã: `25+30 ? 50+60 (110 ßÛ)`, `exactly 50 ? exactly 100 (íõÍİÙ)`, `80?150 / 60?110 (ÓŞİ ãÎÕøÕ íÚáæ ÇáÇİÊÑÇÖí)`, `50.5?100.5 (ßÓÑ)`. **13 äÇÌÍÉ (40 ÊÃßíÏğÇ)**.

### ÇáãÑÍáÉ ÇáİÑÚíÉ 3 ? — ÇáÎÕã: placeholder «áÇ ÚÑæÖ» + ÇŞÊÑÇÍ ÓÚÑ ÇáãŞÇÑäÉ
- ãİÇÊíÍ `merchant_panel.no_offers_available` + `merchant_panel.compare_price_savings` ×4 áÛÇÊ.
- `partials/order-financial-summary.blade.php`: `$compareSavings` (ãÎØØ ãä `ProductVariant::compare_price` ÚÈÑ ÃÕäÇİ `form.items`) — ÈØÇŞÉ ÇáÎÕã ËáÇË ÍÇáÇÊ: ãÈáÛ/äÓÈÉ ãÍÏÏÉ ? `-X`º æÅáÇ ÇŞÊÑÇÍ `compare_price_savings` (äÕ success¡ íõÙåÑ ÇáÇÏÎÇÑ Åä æõÌÏ `compare_price` ÃÚáì)º æÅáÇ `no_offers_available` (placeholder ãÍÇíÏ ÈÏá «+0»).
- `OrderFinancialSummaryTest.php` — ÇÎÊÈÇÑÇä ÌÏíÏÇä: «no-offers placeholder» + «savings drawn from comparison prices». **10 äÇÌÍÉ (52 ÊÃßíÏğÇ)**.

### ÇáãÑÍáÉ ÇáİÑÚíÉ 4 ? — ÇáŞÇÆãÉ ÇáãæÍÏÉ ÈİÇÕá «·»
- `partials/order-form-modal.blade.php`: ŞÇÆãÉ ÃÕäÇİ ÇáãæÏÇá ãä ÃÈØÇŞÇÊ ãäİÕáÉ ? ßÑÊ æÇÍÏ `divide-y divide-surface-border` ãÒæøÏ ÈÜ`data-order-items-list`.
- `orders-table-cell.blade.php` (ÓØÑ ~282-296): İÇÕá ÚãæÏ ÇáãäÊÌÇÊ `,` ? **`·`** (ãæÖÚÇ ÇáÈæí ÚäÇÕÑ).
- ÊÃßíÏÇÊ ÇÎÊÈÇÑ ÇáÔÈßÉ ÊÍÏíËğÇ Úáì ÇáİÇÕá ÇáÌÏíÏ.

### ÇáãÑÍáÉ ÇáİÑÚíÉ 5 ? — ÃäæÇÚ ÇáÔÍä read-only ãä ŞÏÑÇÊ ÇáÔÑßÉ
- `index.blade.php`: ãáİ ãÔÊÑß `$shipmentTypeOptionsFor(?string $providerId)` (~2380-2412) ãÈäí Úáì `Carrier::capabilityList()` — `supports_delivery` ? [delivery] İŞØ¡ `supports_return ? +return`¡ æÇáÇİÊÑÇÖí [delivery, return].
- `$formShipmentTypeOptions()` (~2414-2431) íİæÖ ááãáİ — ÈáÇ İÍÕ ÇáäÇŞá ÇáãßÑÑ.
- `$startOrderShipmentTypeEdit` (~3795+): íÖÈØ `editShipmentTypeOptions` ãä äÇŞá ÇáØáÈíÉ ÇáİÚáí (ãÓÇÑ ÇáÔÈßÉ/ÇáãæÈÇíá íŞÑÂä ãä ÇáÎÇÕíÉ) — ãÍÑÑ inline **ãŞÕæÕ** áŞÏÑÉ ÔÑßÉ ÇáØáÈíÉ.
- `OrderInlineSelectEditTest.php` — +1: «inline shipment-type editor capped to the carrier capabilities» (Carrier supports_delivery İŞØ ? ÎíÇÑÇä [delivery]). **12 äÇÌÍÉ (52 ÊÃßíÏğÇ)**.

### ÇáãÑÍáÉ ÇáİÑÚíÉ 6 ? — stopdesk: ÈáÏíÉ ÙÇåÑÉ ßÎØæÉ ÍŞíŞíÉ + ÊÓáÓá ÌÛÑÇİí «ÊÍÏíÏ áÇ ÊæŞÚ» + ÚÑÖ ÑãÒ/ÈáÏíÉ + lazy (ãäŞóøÍ ÈŞÑÇÑóí A/B)
- **ÇáÈáÏíÉ ÎØæÉ Ãæáì ÙÇåÑÉ áßá ÇáÃäæÇÚ:** ÃõÒíá `x-show="delivery === 'home'" x-cloak` ãä ÚãæÏ `form.city_id` İí `order-form-modal` + `delivery-edit-modal` — ÇáÊÖííŞ æáÇíÉ ? ÈáÏíÉ ? ãßÊÈ.
- **ÊÚÏíá ÇáæáÇíÉ = ÊÍÏíÏ ÍŞíŞí:** ÅÛáÇŞ ÌÏíÏ `$changeFormState($id)` (~2581) íÖÈØ ÇáæáÇíÉ æíõİÑøÛ `city_id` + `stopdesk_point_id` + `formOffices` Ëã `loadCities($id)` — áÇ «ÅÈŞÇÁ Åä ßÇä ÕÇáÍğÇ» ÚäÏ ÊÚÏíá ÇáÌÛÑÇİíÇ (ŞÑÇÑ A: ÊİÑíÛ ŞÓÑí áíÚÇÏ ÇáÇÎÊíÇÑ ÇáİÚáí).
- **ÊÚÏíá ÇáÈáÏíÉ = íÍİÙ ÇÎÊíÇÑ ÇáãÓÊÎÏã:** ÅÛáÇŞ ÌÏíÏ `$changeFormCity($id)` (~2591) íÖÈØ ÇáÈáÏíÉ Ëã `rebuildFormOffices()` — ÇáãßÊÈ ÇáÕÇáÍ íÈŞìº ÛíÑ ÇáÕÇáÍ íõãÓÍ ÈÊæÓÊº ãßÊÈ ãİÑÏ ááÈáÏíÉ íËÈøÊ äİÓå (ŞÑÇÑ B).
- **ÊÛííÑ ÇáÔÑßÉ/ÇáäæÚ = íÍİÙ æíæİøŞ:** `$applyProviderScope` (~3009) ? `loadCities(state, resetCity: false)` — ÇáÈáÏíÉ ÇáãÛØÇÉ ÊõÍİÙ ÚäÏ ÊÈÏíá ÇáäæÚ/ÇáÔÑßÉ (home?stopdesk) æÇáãßÊÈ íõæİóøŞ ÊáŞÇÆíğÇ¡ ÈíäãÇ ÊÚÏíá ÇáæáÇíÉ æÍÏå íİÑøÛ áÃäå ÅÚÇÏÉ ÊÍÏíÏ.
- **áÇ ÊËÈíÊ «ÇİÊÑÇÖ» Úáì ãÓÊæì ÇáæáÇíÉ:** ÃõÒíá ÇáÊËÈíÊ ÇáÊáŞÇÆí ÚäÏ stopdesk ÈáÇ ÈáÏíÉ — ãßÊÈ æÍíÏ İí ÇáæáÇíÉ **áÇ** íõÎÊÇÑ Ïæä ÈáÏíÉº ÇáÊËÈíÊ İŞØ áÈáÏíÉ ÈãßÊÈ æÇÍÏ.
- **ÇÔÊŞÇŞ ÇáÈáÏíÉ ãä ÇáãßÊÈ:** `$onFormOfficePicked` (~3051) íõÈŞí ãáÁ `city_id` ãä ÇáãßÊÈ ÚäÏ ÇÎÊíÇÑ íÏæí Èíä ÚÏÉ ãßÇÊÈ.
- **ÚÑÖ «ÑãÒ + ÈáÏíÉ» İŞØ:** `StopdeskPoint::scopedOfficeOptions` + ÈÇäíø `rebuildFormOffices` — `hint` = ÇÓã ÇáÈáÏíÉ İŞØº ÇáãÕÏÑ ÇáãÔÊÑß (lazy / cascade / inline) ãÊØÇÈŞ.
- **ãÑÔÏÇÊ ÎØæÉ-ÈÎØæÉ:** «ÇÎÊÑ ÔÑßÉ ? ÇÎÊÑ æáÇíÉ ? ÇÎÊÑ ÈáÏíÉ ? ÇÎÊÑ ãßÊÈ» — İÑÚ `select_city_for_desks` ÃõÚíÏ Èíä İÑÚí ÇáæáÇíÉ æÇáãßÊÈ İí ÇáãæÏÇáíä.
- **OrderOfficeSelectionTest.php — ÇÎÊÈÇÑÇÊ S6 ãäŞóøÍÉ/ÌÏíÏÉ:** ? create form íõÈŞí ÍŞá ÇáÈáÏíÉ æíÑÔÏ æáÇíÉ Ëã ÈáÏíÉ¡ ? ÊÛííÑ ÇáæáÇíÉ íõİÑøÛ ÇáÈáÏíÉ+ÇáãßÊÈ¡ ? ÈáÏíÉ ÈãßÊÈ æÇÍÏ ÊËÈøÊ ÊáŞÇÆíğÇ ÈíäãÇ ãßÊÈ æáÇíÉ æÍíÏ ÈáÇ ÈáÏíÉ áÇ íõÎãóøä¡ ? ÇÎÊíÇÑ íÏæí Èíä ãßÊÈíä íÚíÏ ãáÁ ÇáÈáÏíÉ¡ ? ãæÏÇá ÇáÊÚÏíá ÇáÓÑíÚ íõÈŞí ÇáÈáÏíÉ æÊÛííÑ ÇáæáÇíÉ íİÑøÛ ÇáÍŞæá¡ ? ÇáÊÈÏíá Åáì stopdesk íÍİÙ ÇáÈáÏíÉ æíËÈøÊ ãßÊÈåÇ ÇáæÍíÏ. **ÍÒãÉ OrderOfficeSelection: 27/27 (109 ÊÃßíÏğÇ)**.

### ÃÏáÉ ÇáÊÔÛíá (ÇáãÑÇÍá 2–6)
- ÇáÈÇŞÉ ÇáÎãÓ ÇáãõÊÃËÑÉ: **70 äÇÌÍÉ (269 ÊÃßíÏğÇ)** — OfficeSelection 27 (109) + WeightAuto 13 (40) + InlineSelect 12 (52) + Tracking 8 (16) + FinancialSummary 10 (52) — ÕİÑ ÇäÍÏÇÑ.
- `view:cache` äÇÌÍ Ëã `view:clear`.
- **ãÄÌóøá/ãØáæÈ:** ÊÍŞŞ ÇáãÓÊÎÏã ÇáÈÕÑí 375/768/1440 (ÍŞá ÇáÈáÏíÉ ÙÇåÑ áÜ stopdesk ÈÚÏ ÅÚÇÏÉ ÊÕãíã S6¡ ÇáãßÊÈ íÙåÑ «ÑãÒ + ÈáÏíÉ» İŞØ¡ ÊËÈíÊ ÈáÏíÉ ÈãßÊÈ æÇÍÏ) + ÓŞİ %=150.

### ÇáãáÍŞ 3: ãäÕÉ ÇáãäÊÌÇÊ — İáÇÊÑ ÃÈÍÇË ãä ãÓÊæìíä + ÊÍæíá ãæÏÇáóí ÇáÊÕÏíÑ/ÇáÇÓÊíÑÇÏ Åáì Alpine äŞí + ÑİÚ ÊÈÇíä ÇáæÖÚ ÇáİÇÊÍ (2026-09-13)
- **`partials/filter-bar.blade.php` (ÌÏíÏ):** ÔÑíØ ÃÏæÇÊ — ÈÍË ÏÇÆã ÇáÙåæÑ (ÈÍË ×) + ÒÑ **Filters** íØáŞ `edz-filter-open { key: 'root' }` ÚÈÑ `dropdownPosition()`¡ ãÚ **ÔÇÑÉ ÚÏøÇÏ** ÊÙåÑ ÚäÏ ÊİÚíá Ãí İáÊÑ.
- **`partials/filter-portal.blade.php` (ãõÏãÌ ÏÇÎá filter-bar):** áæÍÉ ãÊÏÑøÌÉ ãä ãÓÊæìíä (äÓÎ idiom ÇáÊÊÈÚ/ÇáØáÈÇÊ): ÌĞÑ (Brand/Category/Status/Featured/Created) ? ÎíÇÑÇÊº ÒÑ ÑÌæÚ áßá ŞÓãº ÚäÕÑ äÔØ íÙåÑ **ÚáÇãÉ ?** ÈÔÇÑÉ `bg-accent-surface`º ŞÓã ÇáİÊÑÉ ãÏÎáÇ flatpickr `created_from`/`created_to` (ÊáŞÇÆí ÚÈÑ `panel.js`).
- **`index.blade.php`:** ÍÇáÉ ÌÏíÏ `category_id, is_active, is_featured, created_from, created_to` (ÇÓÊÈÏÇá `created_at` ÇáæÇÍÏ)º ÍĞİ `show_export_modal`/`show_import_modal`º ÇÓÊÚáÇã ÅÖÇİÉ ãÑÔøÍÇÊ `primary_category_id` + `whereDate created_at >=/<=`º `$setFilter` (íõÕİøÑ `page`) + `$clearFilters` + `$activeFilterCount` (computed/closures)º `$categories` (ÊÕäíİÇÊ ÇáãÊÌÑ ãÚ `full_name`)º `wire:target` íÖã ßá ÇáİáÇÊÑ ÇáÓÈÚÉº ÒÑøí ÇáÊÕÏíÑ/ÇáÇÓÊíÑÇÏ ÃÕÈÍÇ `@click="exportOpen = true"` ÅáÎ.
- **`export-import-modal.blade.php`:** ÊÍæíá ßÇãá Åáì **Alpine äŞí** — `x-show="exportOpen"/"importOpen"` ãä äØÇŞ ÇáÌĞÑ `x-data="{ exportOpen, importOpen }"`¡ äÓÎ Markup `edz-modal` (+`role="dialog"`, Esc+backdrop), Şİá ÊãÑíÑ ÇáÌÓÏ ÚÈÑ `x-effect`¡ ÈáÇ Ãí `$set`. áÇ ÊÛííÑ İí Çáãßæøä ÇáãÔÊÑß (~70 ãÓÊÎÏãğÇ).
- **ÊÈÇíä æÖÚ İÇÊÍ (+ÏÑÌÉ æÇÍÏÉ):** subtitle `text-ink-400`?`500`¡ ÑÃÓ ÇáÌÏæá/ÇáÈÇÑßæÏ/ÇáÊÇÑíÎ `text-ink-muted`?`text-ink-soft`.
- **ÊÑÌãÇÊ ×4:** `products.all_categories` İí ar/en/fr/es (ĞÇÊ ÇáÅÓäÇÏ ßÈÇŞí ÇáÃŞÓÇã).
- **`ProductIndexExportImportTest.php`:** ÅÚÇÏÉ ßÊÇÈÉ ßÇãáÉ áÊÃßíÏ ÇáãæÏÇáíä **ãËÈóøÊíä ÏÇÆãğÇ æãÎİííä ÈÜ `x-show` + `x-cloak`** ÈÏá `$set` æ`assertDontSee-ÍÊì-ÇáİÊÍ` + ÃÒÑÇÑ ÇáÜ Alpine ÇáËäÇÆíÉ. **ÍÒãÉ 6/6 (38 ÊÃßíÏğÇ)**.
- **`ProductIndexFilterTest.php` (ÌÏíÏ):** 9 ÇÎÊÈÇÑÇÊ — ÇáÔÇÑÉ ÕİÑ/ÚÏøÇÏ¡ brand¡ category¡ status¡ featured¡ created_from/to (ÚÈÑ `where id update` áÃä `created_at` áíÓ İí fillable)¡ clearFilters¡ ÇáÊÓáÓá ÇáåÑãí `Men > T-Shirts`¡ wire:target. **9/9 (36 ÊÃßíÏğÇ)**.
- **ÊÔÛíá ßÇãá:** ÍÒãÉ `ProductIndexExportImportTest`+`ProductIndexFilterTest`+`ProductWizardStepEngineTest`+`ProductSkuBarcodeTest` = **58 äÇÌÍÉ (316 ÊÃßíÏğÇ)**º `view:cache` äÇÌÍ Ëã `view:clear`º `php -l` äÙíİ Úáì ÇáËáÇËÉ.
- **ãÄÌóøá/ãØáæÈ:** ÊÍŞŞ ÇáãÓÊÎÏã ÇáÈÕÑí (áæÍÉ ÇáİáÇÊÑ 375/768/1440 ÚÈÑ ÇáÇÚÊãÇÏ Úáì ÇáäãØ ÇáÑÇÓÎ ãä áæÍÉ ÇáÊÊÈÚ¡ ÇäÓíÇÈíÉ ÇáãæÏÇá¡ æÖæÍ ÇáæÖÚ ÇáİÇÊÍ) — áÇ íæÌÏ ãÊÕİÍ ãÖãøä.

## ÏİÚÉ ÕİÍÉ ÇáÊÊÈÚ: İáÇÊÑ «Filters» ÇáãÊÏÑøÌÉ ÈÃÓáæÈ ÇáãäÊÌÇÊ (ÔÑßÇÊ ÇáÊæÕíá + ÑÌÇá ÇáÊæÕíá) (2026-09-13) ?
- **ŞÇÚÏÉ ÇáäÓÎ:** ãÌãæÚÉ ÇáİáÊÑ ÊõÚÑÖ İí ŞÇÆãÉ Filters **İŞØ** ÅĞÇ ßÇä ÚãæÏåÇ ÇáãŞÇÈá ÛíÑ ÙÇåÑ ÍÇáíğÇ İí ÇáÔÈßÉ (ÇáÚãæÏ ÇáÙÇåÑ íãáß İáÊÑ ÑÃÓå ÇáÎÇÕ İí `tracking-table-header`). ÇáÇÓÊËäÇÁÇÊ: `provider`?`provider` (ÊÈæíÈ ÇáÔÑßÇÊ)¡ `tracking_statuses`?`tracking_status`¡ `date`?`shipping_date`¡ `amount`?`total`¡ `city`?`city`¡ `rider`?`delivery_rider` (ÊÈæíÈ ÇáÑÌÇá)¡ `assigned_to`?`assigned_to`¡ `confirmed_by`?`confirmed_by`.
- **`TrackingGridConcern.php`:** `availableFilterGroups()` (íõÍÊÓÈ İí ßá `render` — İáÊÑÉ ĞÇßÑÉ Úáì `$visibleColumns`¡ ÕİÑ ÇÓÊÚáÇãÇÊ ÌÏíÏÉ) + `activeFilterCount()` (ÚÏøÇÏ ÇáÔÇÑÉ Úáì ãÌãæÚÇÊ ÇáãÊÇÍÉ İŞØ).
- **`partials/tracking-filter-bar-portal.blade.php` (ÌÏíÏ):** áæÍÉ ãÊÏÑøÌÉ ãä ãÓÊæííä (äÓÎ idiom `products/index/partials/filter-bar`): ÌĞÑ ÇáãÌãæÚÇÊ ÇáãÊÇÍÉ ? ÃŞÓÇã (provider/tracking_statuses/date/amount/city/rider/assigned_to/confirmed_by)º ÒÑ ÑÌæÚ áßá ŞÓãº ÚäÕÑ äÔØ ÈÚáÇãÉ ?º ãÓÍ ÇáİáÇÊÑº æÑŞÉ ÓİáíÉ ãæÈÇíá + ŞÇÆãÉ ãäÓÏáÉ ÏíÓßÊæÈ ÚÈÑ `dropdownPosition()`. ÊÓÊãÚ Úáì ÍÏË `edz-toolbar-filter-open` (ÚÒáğÇ Úä `edz-filter-open` ÇáÎÇÕ ÈÈæÇÈÉ ÑÄæÓ ÇáÃÚãÏÉ).
- **`partials/tracking-toolbar.blade.php`:** ÃõÒíáÊ ÇáŞÇÆãÉ ÇáãÓØøÍÉ ÇáŞÏíãÉ æ`quick active count` æãäÊŞíÇ `assigned_to`/`confirmed_by` ÇáãÖãøäÇäº ÒÑ **Filters** æÇÍÏ íØáŞ `edz-toolbar-filter-open { key: 'root', el }` ãÚ ÔÇÑÉ ÇáÚÏøÇÏ¡ ãÎİí ßáíğÇ Ííä áÇ ÊÊæİÑ ãÌãæÚÇÊ (ÇİÊÑÇÖíğÇ ßá ÇáÃÚãÏÉ ÙÇåÑÉ ? ÇáÒÑ ãÎİí ÍÊì ÅÎİÇÁ ÚãæÏ). ÈŞíÉ ÇáÔÑíØ (ÈÍË/ãÒÇãäÉ/ÃÚãÏÉ/ÑŞÇÆŞ ÇáİáÇÊÑ ÇáäÔØÉ) Ïæä ÊÛííÑ.
- **`index.blade.php`:** ÊÖãíä ÇáÈæÇÈÉ ÈÚÏ `tracking-filter-portal`.
- **áÇ ÊÑÌãÇÊ ÌÏíÏÉ:** ÃõÚíÏ ÇÓÊÎÏÇã ÇáãİÇÊíÍ ÇáŞÇÆãÉ. **áÇ ÃÏæÇÊ ãÊÕİÍ ãÖãøäÉ** — ßá äŞÇØ ÇáÊÍŞŞ ÇáíÏæíÉ ÇáÈÕÑíÉ ãÏÑÌÉ áÇÍŞğÇ.
- **ÇáÊÔÛíá:** `php -l` äÙíİ (PHP 8.3.28) × 4¡ `view:cache` äÇÌÍ¡ æ**`TrackingSearchFilterTest` 28/28 (114 ÊÃßíÏğÇ)**.
- **İÔáÇä ŞÇÆãÇä (ãõËÈóÊ ÃäåãÇ ÓÇÈŞÇä áåĞå ÇáÏİÚÉ — ÃõÚíÏ ÅäÊÇÌåãÇ ÈÚÏ `git stash` áãáİÇÊ ÇáÏİÚÉ):** `TrackingGridBatchTest::openEditModal`/`submitEdit` — `Undefined variable $optionDivider` İí `components/edz/select.blade.php` (ÊÚÏíáÇÊ ÛíÑ ãáÊÒãÉ İí `select.blade.php`/`edz-select.js`/`order-form-modal.blade.php`). æßÓÑ ÊÍãíá İÆÇÊ ÅÖÇİí: `NoestIntegrationAdapter` (ÚÏøÇÏ ÛíÑ ãáÊÒãíä: `CarrierIntegrationContract::validateForCarrier` ÃõÖíİ ÈáÇ ÊäİíĞ İí ÇáÜ adapter) — íÙåÑ ÚäÏ ãÒÌ `TrackingTrashWebhookLabelTest`/`NoestIntegrationTest` İí äİÓ ÇáÊÔÛíáº `TrackingSearchFilterTest` æÍÏå íãÑ ßÇãáğÇ.
- **ãÄÌóøá/ãØáæÈ:** ÊÍŞŞ ÇáãÓÊÎÏã ÇáÈÕÑí 375/768/1440 — ÙåæÑ/ÇÎÊİÇÁ ÒÑ Filters ÚäÏ ÅÎİÇÁ ÇáÃÚãÏÉ¡ ÇáæÑŞÉ ÇáÓİáíÉ ÇáãæÈÇíá¡ ÒÑ ÇáÑÌæÚ¡ æÚáÇãÉ ? ááŞíãÉ ÇáäÔØÉ¡ æãäÊŞíÇ ÇáãäÇÏÇÉ ÇáÓİáíÉ ÈÚÏ äŞáåãÇ ãä ÇáÔÑíØ Åáì ãÏÎá ÇáİáÊÑ.
---

## ÇáãÑÍáÉ 2 (ÇáİÑÚ B): ÅÚÇÏÉ åíßáÉ äãæĞÌ ÇáØáÈ (İÕá ÇáãßæäÇÊ) (2026-09-13)
ËÈøÊÊ æØÈŞÊ 5 ÊÛííÑÇÊ ãÚÒæáÉ Úáì ãßæøäÇÊ äãæĞÌ ÇáØáÈ ãÚ ŞíÏ **ÕİÑ äãæ ÕÇİí İí `index.blade.php` (ÓŞİ 5,699 ÓØÑ)**:

- **2.1 äŞá ÍŞá ÇáæÒä:** ÃõÒíá ÍŞá ÇáæÒä ãä ŞÓã ãÚáæãÇÊ ÇáØáÈ æäõŞá Åáì ÔÈßÉ ÇáãáÎÕ ÇáãÇáí ßÎáíÉ ŞÇÈáÉ ááÊÚÏíá (ÊÊÈÚ ÊáŞÇÆí `weight_auto_hint` + ÃŞÕì æÒä + `@error`).
- **2.2 ÎÕã ãä ÇáÚÑæÖ:** Action ãÎÕÕ ÌÏíÏ `SuggestCompareDiscountAction` íÍÓÈ ? max(0, ÓÚÑ ÇáãŞÇÑäÉ ? ÇáÓÚÑ) × ÇáßãíÉ ãŞíøÏğÇ ÈÇáãÌãæÚ ÇáİÑÚí¡ ãÚ ÚÑÖ ÇáäÓÈÉ ÇáãÆæíÉº íõÓÊÏÚì ÍÕÑíğÇ İí ÇáãáÎÕ ÇáãÇáí.
- **2.3 ãæÍøÏ ãäÊŞí ÇáÔÑíß:** ŞÇÆãÉ æÇÍÏÉ ãÔİøÑÉ (`p:{id}`/`r:{id}`) ãÚ İÇÕá `__delimiter__`¡ ææÖÚ ÇáÔÑßÉ ÇáæÇÍÏÉ (ãÄÔøÑ `data-edz-company-single`)º ÇÓÊõÎÑÌ ãäØŞ ÇáÊÈÏíá Åáì trait ÌÏíÏ `OrderDeliveryPartnerConcern`.
- **2.4 ÊÈÓíØ äŞØÉ ÇáÇÓÊáÇã:** ÃõÈŞí ÍŞá ÇáÈáÏíÉ (áÇ íõÎİì ãÌÏÏğÇ — íÍİÙ ÚŞÏ S6) ãÚ ÊÈÓíØ ÇáÊÏİŞ.
- **2.5 ÅÚÇÏÉ ÊÕãíã ŞÓã ÇáÊæÕíá:** partial ÌÏíÏ `order-delivery-cascade.blade.php` ãÔÊÑß Èíä ÇáäãæĞÌíä (ÅäÔÇÁ/ÊÍÑíÑ) íÔãá ÊÈÏíá ÇáäæÚ æÔÈßÉ ÇáæáÇíÉ/ÇáÈáÏíÉ æãßÊÈ ÇáÊÓáíã ãÚ ÈæÇÈÇÊ ÇáÍÇáÉ.
- **`index.blade.php`:** ÃõÒíáÊ 5 closures æÇÓÊõÈÏáÊ ÈØÑŞ ÇáÜ trait (checked @1833/3095/4246/4466¡ İÍÕ ÇáÜ rider @1955¡ ÇİÊÑÇÖíÇÊ ãÔİøÑÉ @149/169)º ÇáÍÌã: **5,657 ÓØÑğÇ (ÊÍÊ ÇáÓŞİ ÈÜ 42)**.
- **`components/edz/select.blade.php` + `edz-select.js`:** ÏÚã `optionDivider`/`isDivider` (ÊãËíá¡ ÊÕİíÉ¡ ÊäŞøá áæÍÉ ÇáãİÇÊíÍ¡ ãäÚ ÇáäŞÑ).
- **ÇáÊÏŞíŞ:** `php -l` äÙíİº `php artisan view:cache` ?º ÃÎÖÑ ßÇãá: OrderOfficeSelectionTest 27 + OrdersDefaultProviderTest 7 + OrderFinancialSummaryTest 10 + OrderCompletenessTest 22 + OrderWeightAutoCalcTest 13 + OrderInlineSelectEditTest 12 + OrderTrackingTest 8 + ÏİÚÉ ãæÓøÚÉ (ãÏíäÉ/ÚãæÏ/ÈæÇÈÉ/ãæáøÏ/ÍŞæá/ÚäÇÕÑ/ÇÓÊÚáÇãÇÊ) 56.
- **ãáÇÍÙÉ:** áã íõÔÛóøá `npm run build` áÊÛííÑÇÊ `edz-select.js` ÇáãÕÏÑíÉ (ÔÃä äÔÑ). ãáİÇÊ ÊíÇÑ ÇáÚãá ÇáãæÇÒí áã ÊõáãÓ ÅØáÇŞğÇ.

---

## ÅÑÓÇá ÇáØáÈíÇÊ Åáì ÔÑßÇÊ ÇáÊæÕíá: ÊÍŞŞ ãíÏÇäí áßá ÔÑßÉ + ÅÑÓÇá ĞÑøí (áÇ ÅäÔÇÁ ÊÊÈÚ ŞÈá ÑÏòø Óáíã) — 2026-09-13 ?
**ÇáŞÑÇÑÇÊ ÇáãÚÊãÏÉ ááãÓÊÎÏã:** ÅäÔÇÁ ÇáØáÈíÉ íÈŞì ãÍáíğÇ ßãÇ åæ (ãæÏÇá ÇáÊÇÌÑ + ÇáãÊÌÑ) **ÈáÇ ÇÊÕÇá ÈÇáÔÑßÉ áÍÙÉ ÇáÅäÔÇÁ**º ÇáĞÑøíÉ ÚäÏ ÇáÅÑÓÇá İŞØ (ÊÃßíÏ-æÅÑÓÇá / ãÈÇÔÑ / ÌãÇÚí): áÇ ÓÌá İí `order_trackings` æáÇ ÍÇáÉ `shipped` ŞÈá ÑÏòø Óáíã ãä ÇáÔÑßÉº ÚäÏ ÑİÖ ÇáÔÑßÉ ÊÈŞì ÇáØáÈíÉ **`confirmed` áÅÚÇÏÉ ÇáãÍÇæáÉ**¡ æÇáÊæÓÊ íÚÑÖ **ÑÓÇáÉ ÇáÔÑßÉ İŞØ** (áÇ ÊİÇÕíá ÍŞæá).

- **ÇáİÑÚ A — ÊÍŞŞ ãíÏÇäí áßá ÔÑßÉ:** `CarrierIntegrationContract::validateForCarrier(ShippingProvider, Order): array{validated, errors<field, list<string>>}` + ÊäİíĞå İí `NoestIntegrationAdapter` æİŞ ŞæÇÚÏ ÊæËíŞ NOEST v2.3: æÌæÏ `api_token`/`guid`¡ `client` ãØáæÈ æ?255¡ `phone`/`phone_2` ãä 9–10 ÃÑŞÇã (ÊØÈíÚ `+213`/ãÓÇİÇÊ/ÔÑØÇÊ)¡ `wilaya_id` ÑŞãí ãä 1–58¡ `adresse`/`remarque` ?255¡ `station_code` ãØáæÈ ÚäÏ `stop_desk=1`¡ `reference` ?5. ÇáÔÑßÇÊ ÈáÇ ???????/ÌÇÈÉ ÇáÑÌá ? ÏÇÆãğÇ ÕÇáÍÉ (ÈáÇ İÍÕ).
- **ÇáİÑÚ B — ÅÚÇÏÉ ÊÑÊíÈ `OrderShippingGateway::send()`:** completeness ? (confirm ÚäÏ `confirmFirst`) ? `validateForCarrier` (íæŞİ POST) ? **`postToCarrier` ÃæáğÇ** ? İŞØ ÈÚÏ äÌÇÍå: ÊõäÔÃ `order_trackings` æÊõäÊŞá `preparing?shipped` æíõÓÌóøá ÍÏË `sent_to_carrier`º ÚäÏ ÇáİÔá: commit ááãÑÍáÉ ÇáãÍáíÉ (ÊËÈíÊ `confirmed`) ãÚ ÅÑÌÇÚ `error` ÈáÇ tracking æáÇ shipped æáÇ audit. ÇáäÇŞá ÈáÇ ÊßÇãá íÈŞì Úáì ÇáãÓÇÑ ÇáãÍáí (íõÔÍä ãÍáíğÇ ßãÇ ßÇä).
- **ÇáÜ Blade (áÇ æÓã ÌÏíÏ):** `submitConfirmAndSend`/`sendConfirmedOrder`/`confirmBulkSend` — ÚäÏ `$result['error']`: ÊæÓÊ `warning` ÈÑÓÇáÉ ÇáÔÑßÉ İŞØ¡ ÇáØáÈíÉ ÊÈŞì ááÍÇáÉ ÇáŞÇÈáÉ áÅÚÇÏÉ ÇáãÍÇæáÉ¡ æÇáãÓÇÑ ÇáÌãÇÚí íÚÏøåÇ `skipped` ãÚ ÓØÑ ÑÓÇáÉ ÇáÔÑßÉº äÌÇÍ ÇáÊæßíÏ/ÇáÅÑÓÇá ÇáãÈÇÔÑ íÈŞì ßãÇ åæ.
- **ÊÑÌãÇÊ ×4** İí `order_flow`: `carrier_validation_required_field` / `phone_digits` / `max_length` / `min_length` / `wilaya` / `station_required`.
- **íõÍáø ÇäßÓÇÑğÇ ãæËøŞğÇ ÓÇÈŞğÇ** (ÓØÑ 1646 ÃÚáÇå): ßÇäÊ `validateForCarrier` ÃõÖíİÊ ááÚŞÏ ÈáÇ ÊäİíĞ İí ÇáÜ adapter — ÇßÊãá ÇáÊäİíĞ ÇáÂä İÇÎÊİÊ ÇáÊÕÏøÚÇÊ ÚäÏ ãÒÌ `TrackingTrashWebhookLabelTest`/`NoestIntegrationTest`.
- **ÇÎÊÈÇÑÇÊ ÌÏíÏÉ 16/16:** `NoestCarrierValidationTest` 8 (ßá ŞÇÚÏÉ/ÍŞá) + `SendGatewayCarrierAtomicTest` 5 (äÌÇÍ?tracking+shippedº ÑİÖ?confirmed ÈáÇ trackingº ãÈÇÔÑ-ÑİÖº ÊÍŞŞ íæŞİ POST ÈáÇ ØáÈ — `Http::assertNothingSent`º ãÍáí ÈáÇ ÊßÇãá) + `SendCarrierFailureTest` 3 (ÏÑÌ/ãÈÇÔÑ/ÌãÇÚí ÚÈÑ ÇáÜ Volt).
- **ÇáÊÔÛíá (ÕİÑ ÇäÍÏÇÑ):** Shipping 43/43 (147 ÊÃßíÏğÇ) + Merchant/Order 79/79 (265) + ÏİÚÉ ÊßãíáíÉ 46/46 (200) ? 168 äÇÌÍÉº `php -l` äÙíİ (Ñí?íÓí × tersi ÇáŞØÚ); `view:cache` äÇÌÍ Ëã `view:clear`.
- **ãÄÌóøá/ãØáæÈ:** ÊÍŞŞ ÇáãÓÊÎÏã ÇáÈÕÑí 375/768/1440 — áÇ ÊÛííÑ æÓãí (ÊæÓÊ ŞÇÆã¡ Óáæß İŞØ)º ÅÚÇÏÉ ÇáÊÍŞŞ ÇáíÏæíÉ ãä ÑÓÇÆá ÇáÔÑßÉ İí ÇáÃæÖÇÚ ÇáËáÇËÉ ÈÚÏ Ãí ÊÚÏíá ãÓÊŞÈáí.
---

## Sub-Phase B-2 (2026-09-13) — Order-form UX/perf pass (4 reported issues + office slowness + request reduction)

**Root causes fixed**
- **Picker "can't select" (race):** partner-picker.blade.php used wire:model.live + wire:change on the same select ? TWO concurrent round-trips that overwrote each other (value setter vs. switchFormPartner). Fixed ? **deferred wire:model** so the pick + handler land as ONE atomic request. Affects create/edit + confirm drawer (shared partial).
- **"Dropdowns don't close":** edz-select.js select() swallowed every click while loading=true (the lazy office/city fetch window) ? panel stayed open / pick ignored. Now only 
oundtrip && loading blocks, so modal list selects close instantly.
- **Slow office fetch:** loadFormOfficesLazy synced with the carrier API on EVERY dropdown open (GET /desks + ~170 N+1 DB queries per open). Both lazy paths and loadFormOffices now go **DB-first** via new StopdeskOfficeSync::syncIfNeeded() (only contacts the carrier when the store has NO active points for that scope; the refresh button + scheduled job remain the forced-sync paths). Plus per-sync memoization of stateByDeskCode/
esolveCityId.
- **Field order (create/edit modal):** now Partner ? Shipment (carrier-capability-scoped; **rider leg ? fixed "delivery"**) ? Payment (COD) ? Delivery type ? Wilaya ? Commune ? Office. Extracted shared order-delivery-type-toggle + order-destination-fields partials; new order-form-delivery seeds the form; cascade partial slimmed to a composition for the quick-edit modal.
- **Discount:** type selector (amount/percent) removed ? always amount, manually editable; create seeds discount_type='amount'; editing a legacy percent order auto-converts to DZD. Inline table discount editing untouched.
- **Glyph:** Ã— ? × in order-form-modal items line + duplicate-overlap badge (was re-introduced after a prior fix).

**Verification**
- **Tests: 165 passed** across the merchant order + shipping suites: OfficeSelection 27, Completeness 22, InlineFieldEdit 16, InlineItemsEdit 18, WeightAutoCalc 13, InlineSelectEdit 12, FinancialSummary 10, ConfirmGate 6, DefaultProvider 7, CityScope 8, Tracking 8, DuplicateDetection 5, QuantityCap 5, ProviderColumn 5, ProductPicker 2, PageQueryCount 1.
- iew:cache reused after a Plain-DB data seeding conflict; iew:clear cleared the lock; php -l clean on all touched blade/php; **npm run build** succeeded (public/build regenerated).
- **index.blade.php:** 5,672 physical lines (under the 5,699 Sub-Phase-B ceiling; all new markup lives in partials).
- **Requests per pick:** partner pick 2?1; office lazy open: (network+N+1) ? 0 network / 1 DB read; client _remoteCache cap 12?48.

---

## Sub-Phase C (2026-09-13) — Order-form sectioned layout + product-row simplification + partner-picker diagnosis

**Diagnosis report (Section 1, reported before any fix)**
- Complaint: "partner picker doesn't appear" for the Demo Store.
- Root cause: **DATA, not a code bug.** Dev DB has 4 active providers + 3 active riders globally. Per store: "Default Merchant Store" = 1 provider / 0 riders ? `singleCompanyMode` static box (auto-selected, asserted by OrdersDefaultProviderTest); **"Demo Store" = 0 providers / 0 riders ? empty `<x-edz.select>` renders as a dead, silent control (the reported case)**; "Edzeery Demo Store" = 3/3 ? works (proven by OfficeSelection 27 + CityScope 8 + DefaultProvider 7). Code path verified: `mount()` fills `allProviders` (index.blade.php:505) + `riderOptions` (:520); include chain passes via `$this` (no dropped prop); Alpine boot-order race class already defended (alpine:init registration in panel.js + MutationObserver resync in edz-select.js — the genuine race was fixed in Sub-Phase B).
- Resolution: empty-state message + CTA gated on `! $hasProviders && ! $hasRiders` ? `route('merchant.delivery', currentStore())` (wire:navigate).

**Changes**
- `partner-picker.blade.php` (79): new `@elseif (! $hasProviders && ! $hasRiders)` branch — dashed surface box + `partner_empty_state` + CTA with arrow icon. singleCompanyMode + unified-select paths untouched.
- `merchant_panel.php` (ar/en/fr/es): new keys `partner_empty_state` + `partner_empty_cta` after `select_provider_first`.
- `order-form-modal.blade.php` (252): three sections using the existing section-header pattern (`text-xs font-semibold text-ink-muted uppercase tracking-wide flex items-center gap-1.5 mb-3`) + icons user/map-pin/truck; uniform `pt-4 border-t border-surface-border` separators on sections 2–3; Customer grid 1/2/3 cols @375/768/1440; **product-row line-total block removed** (price input + qty stepper kept; totals still compute in order-financial-summary).
- `order-form-delivery.blade.php` (34): redundant `shipping_partner` label removed; destination-fields ? office-field composition in the Shipping Partner section.
- `order-destination-fields.blade.php` (36): `$withOffice` param (default true) — delivery quick-edit cascade renders identically.
- `order-office-field.blade.php` (40, NEW): office row extracted from destination-fields (select + refresh btn + hints + error).

**Verification**
- Greps: `data-form-section` × 3; `quantity'\] \* \$item\['price` ? 0 matches in order-form-modal; partial line counts 252/34/36/79/40/204 (all ? 300); **index.blade.php 5,672 before = after (zero changes)**.
- `php -l` clean ×4 lang files; `artisan view:cache` OK (whole tree compiles, run before tests) then `view:clear`.
- **Tests: 168 passed** across 17 merchant order suites: OfficeSelection 27 · DefaultProvider 7 · FinancialSummary 10 · CityScope 8 · Completeness 22 · InlineSelectEdit 12 · InlineFieldEdit 16 · QuantityCap 5 · InlineEdit 5 · InlineItemsEdit 18 · WeightAutoCalc 13 · ProductPicker 2 · DuplicateDetection 5 · ConfirmGate 6 · PageQueryCount 1 · MobileCardParity 5 · StopdeskSyncUi 6.
- Responsive (375/768/1440): Customer grid 1?2?3 cols (`grid-cols-1 md:grid-cols-2 min-[1440px]:grid-cols-3`); Address + Shipping Partner single-col at all widths with shipment/payment pair stacked at sm+ (`sm:grid-cols-2`); office row keeps `flex-1` select + refresh inline once a stopdesk lane with a company is chosen; product row hides the price input below sm (pre-existing) — freed width flows to the name `flex-1`; edz-select panels are fixed z-70 overlays (unaffected by section gaps). No browser tooling installed (playwright/puppeteer absent) ? documented at class level.
- **Deferred:** real-browser pass at 375/768/1440 + screen-zoom audit still owed (no tooling available this run — documented class-level behavior instead).

---

## Sub-Phase C follow-up (2026-09-13) — User review fixes: field order, office into Address, notes grouped with customer, professional >768px

**Reported issues ? resolution**
- "Shipping company selection not visible in add/edit popup": the company picker had no label and sat first in a cramped section. Now labeled `delivery_company` and placed in the professional order below shipment/payment with clear spacing.
- "Delivery-type toggle wrongly placed / glued to shipment & payment selects": the home/office toggle is now the LAST control of the Shipping Partner card, separated after the company picker.
- **Correct order implemented (create/edit form):** Shipment type ? Payment method ? Delivery company/rider ? Delivery type (home/office) ? then Address section: State ? Commune ? **Office (shows only for a stopdesk lane with a company)**.
- Notes field removed from below the Order Summary and grouped with the customer info (name/phone/secondary phone + notes inside one card).
- Order Summary content unchanged (subtotal · total weight · delivery cost · editable fixed-amount discount · total) and now framed by an `order_summary` card header.

**Changes**
- `order-form-delivery.blade.php` (27): reordered to shipment+payment grid ? labeled company picker ? type toggle; office include removed from this partial; no longer owns the `delivery` Alpine scope (hoisted to the modal).
- `order-form-modal.blade.php` (268): card-based sections (`rounded-xl border border-surface-border bg-surface p-4 md:p-5`) for Customer / Shipping Partner / Address / Products / Summary; section order Customer ? Shipping Partner ? Address (in the shared `x-data` delivery wrapper spanning the two), then Products, warnings, Summary, submit; Customer card gains notes; Address includes `order-destination-fields` with default `withOffice` (office after state/city); items list `max-h-72` (was a fixed `80vh-475px` calc); responsive rhythm `p-4 md:p-6` + `space-y-4 md:space-y-5`.
- `order-destination-fields.blade.php` (36): docblock updated; placement remains shared with the quick-edit cascade.
- Untouched: `index.blade.php` (5,672), `order-delivery-cascade.blade.php` (quick-edit flow keeps its own order), `order-financial-summary.blade.php` (grid classes/markers preserved — tests assert `md:grid-cols-2` + `min-[1440px]:grid-cols-5`), `partner-picker.blade.php`, `order-office-field.blade.php`.

**Verification**
- `artisan view:cache` clean (whole tree compiles) then `view:clear`; `delivery_company` key present in ar/en/fr/es.
- **Tests: 182 passed** across 18 suites re-run after the redesign: OfficeSelection 27 · DefaultProvider 7 · FinancialSummary 10 · Completeness 22 · DuplicateDetection 5 · CityScope 8 · QuantityCap 5 · WeightAutoCalc 13 · InlineItemsEdit 18 · InlineSelectEdit 12 · InlineFieldEdit 16 · InlineEdit 5 · ConfirmGate 6 · PageQueryCount 1 · ProductPicker 2 · MobileCardParity 5 · StopdeskSyncUi 6 · RequiredColumnsHint 14.
- **index.blade.php 5,672 before = after (zero changes)**; partial line counts 268/27/36/40/79 all ? 300.
- **Deferred (same as Sub-Phase C):** real-browser screenshot pass 375/768/1440 — no playwright/puppeteer installed; documented at class level.

---

## Sub-Phase C follow-up (2) (2026-09-13) — User-approved: always-visible company picker + discount editor inside its summary cell

**Context (user approval after detailed diagnosis, no execution before approval)**
- The user reported the company picker "still doesn't appear" and the discount "wasn't done" — and clarified (point 3) that the data IS fetched ("áÇ íÙåÑ İÑæäÊ İŞØ", a frontend-only issue). Deep diagnosis (DB query + code trace) proved:
  - Store data (dev DB): "Default Merchant Store" = 1 provider/0 riders ? `singleCompanyMode` static box (no control); "Demo Store" = 0/0 ? empty-state box; "Edzeery Demo Store" = 3/3 ? real select. ? The user's store had exactly ONE company + 0 riders, so the picker genuinely never rendered a selector — by design (old branch), not a bug. The `edz-select` dropdown itself was verified sound (fixed z-70 overlay, repositioned on scroll; modal stacking 60?1?70; no persistent transform ? nothing clips it).
  - Discount: the editable fixed-amount editor ALREADY existed (order-financial-summary.blade.php:185-201) since Sub-Phase B and the redesign froze that file — hence "nothing happened". It was also invisible without products (`@if (!empty($form['items']))` gate) and sat under the grid as a thin row.

**Approved changes**
- `partner-picker.blade.php` (72): removed the `singleCompanyMode` static box branch (`data-edz-company-single`) ? the picker is now **always a real select** whenever ?1 partner exists (companies ? `__delimiter__` "ÑÌÇá ÇáÊæÕíá" ? riders; sole company auto-selected but visible, `edz-company-select` class kept). Empty-state box retained ONLY for 0/0 stores (a zero-option select is a dead control). Docblock updated.
- `order-financial-summary.blade.php` (195): the discount editor moved **into the discount cell** (`data-financial-discount`): `discount` label ? `wire:model="form.discount_value"` number input (min 0 / step 10) + readout (`-currency` / `—`) ? optional `discount_reason` input (when value set) ? `@error`, then the existing hint line (`compare_price_savings` / `no_offers_available`). Old below-grid `fixed_amount` editor row + trailing `@error` removed. All grid markers (`data-financial-grid…total`), `md:grid-cols-2`, `min-[1440px]:grid-cols-5` untouched.
- `OrdersDefaultProviderTest.php`: the two single-company tests now assert `edz-company-select` + company name visible (`assertSee('Solo Carrier')`) and `assertDontSeeHtml('data-edz-company-single')` (create + edit flows).

**Verification**
- `artisan view:cache` clean (whole tree compiles) then `view:clear` (Windows quirk).
- **Tests: full `tests/Feature/Merchant` run — 485 passed (1992 assertions)** across all suites; targeted suites green first (OrdersDefaultProviderTest 7 + OrderFinancialSummaryTest 10).
- **index.blade.php 5,672 before = after (zero changes)**; partial line counts 72/195 ? 300; `data-edz-company-single` gone from all views (only historical Todos.md mention + negative test assertion remain); `fixed_amount` key still used by `orders-table-cell.blade.php` (inline discount type dropdown).
**Sub-Phase C follow-up (3) - REAL root cause found & fixed (the picker never rendered!)**
- User: "la yazal la yazhar select ikhtiyar sharikat al-tawsil... ???? fahs jayyidan 3an almushkil". Deep dumps (Volt::test()->html() written to temp files) revealed the ACTUAL cause: the company <x-edz.select> tag in partner-picker.blade.php was NOT being compiled by Blade at all - it stayed as a literal un-expanded <x-edz.select ... /> tag in the final HTML (browsers render it as an invisible custom element). Root cause: the tag had an @if/@endif DIRECTIVE INSIDE its attributes (@if (! \) :disabled="\" @endif), which breaks Blade anonymous-component compilation. THIS is why the "Delivery company" label rendered with nothing below it, matching the user's complaint across all rounds (single- AND multi-company stores). All prior markup assertions (edz-company-select, ssertSee('Solo Carrier')) were false positives: they matched the literal tag's attributes, not an expanded control.
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
1. `$order['deliveryRider']['name']` was read by the display templates, BUT `Order::toArray()` snake_cases relation keys via `Model::relationsToArray()` (`vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php:403`): a loaded `deliveryRider` relation becomes `$arr['delivery_rider']`, never `$arr['deliveryRider']`. So the rider branch `@elseif (! empty($order['deliveryRider']['name']))` never fired and the cell fell through to the hint. (`shipping_provider` reads were already correct because relation `shippingProvider` ? snake `shipping_provider` matches the blade key.)
2. Additionally `deliveryRider` was NOT in `$orderEagerLoads()` (`index.blade.php:750`) — `decorateOrder()`'s `$order->toArray()` had no rider data at all (also absent key). So genuinely two bugs stacked: relation not eager-loaded, AND wrong camelCase key in the templates.

**Changes**
- `index.blade.php`: added `'deliveryRider'` to the `$orderEagerLoads()` `$with` array (line ~750) — first-ever change to this file.
- `orders-table-cell.blade.php` + `orders-mobile-fields.blade.php`: `$order['deliveryRider']['name']` ? `$order['delivery_rider']['name']` (4 occurrences each) — matches what `toArray()` actually produces.
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
1. "İí ÊÇÈ ÔÑßÉ ÇáÔÍä íÌÈ ÇÙåÇÑ ÇáØáÈíÇÊ ÇáãÑÓáÉ áÔÑßÇÊ ÇáÔÍä İŞØ æáíÓ ÇáÊí ÊÑÓá áÑÌá ÇáÊæÕíá ÇíÖÇ" — the shipping-company tracking tab must list ONLY orders handed to a shipping company, never orders handed to a delivery rider.
2. Commentary on NOEST stopdesk sending: review the NOEST docs vs our payload and make it consistent; a stopdesk order currently cannot be sent because of the office-selection method — the payload must carry only `station_code` + matching destination; a wilaya may have ONE office (Adrar key `"01A"` ? inner `code: "1A"`) or MANY (Alger `"16A"`…`"16K"`).

**Diagnosis**
- **Carrier tab:** the trash branch already filters carrier ? `whereNotNull('shipping_provider_id')` (`TrackingGridConcern` trash block), but the LIVE carrier branch had NO filter at all — the default tab showed every active shipment, including rider-only orders. Production already enforces rider/provider mutual exclusivity (`TrackingDrawerConcern::saveOrder` refuses both and nulls the counterpart; `assignRider` refuses when a provider exists), so a rider-sent order always has `shipping_provider_id = NULL` ? adding the provider filter exactly removes those.
- **NOEST stopdesk:** `createOrder` sent `wilaya_id` from the ORDER's state and `commune` from the ORDER's city, while `station_code` described the SELECTED station. When they disagreed, NOEST rejected with `Le code de wilaya est different de code de station` / `Aucune commune liee a la station choisie` (docs v2.3). The reference userscript builds stopdesk payloads from the station's own wilaya + commune. `desks()` also dropped the response's map keys (`"01A"`) and relied purely on inner `code` ? a single-station wilaya (Adrar) could be dropped if inner code were ever absent.

**Changes**
- `app/Livewire/Concerns/TrackingGridConcern.php` (live branch, ~140): carrier else ? `$query->whereNotNull('shipping_provider_id')` (mirrors the trash-carrier filter; rider branch untouched). Stats (`baseTrackingQuery(true)`) inherit it automatically.
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

**Scope** (approved: "äİĞ" for Phase 1 = orders table only): icon-button tooltips in the orders table + meaningful truncated-cell tooltips. Excluded deliberately: page headers (`layout :title`), `mobile-bottom-sheet` headers, `data-confirm-title` (SweetAlert), and **touch-only surfaces** — `orders/index.blade.php` mobile cards + `orders-mobile-fields.blade.php` are `sm:hidden`/touch-first, and the tooltip is hover-only (`(hover:hover) and (pointer:fine)` media gate), so wrapping them adds Alpine cost with zero touch value. Their native `title=` stay. Toolbar/close buttons in `filter-portal`/`bulk-actions-bar`/`table-settings-modal`/`orders-items-edit-modals` (delete_item) remain `title=` (mobile sheets / outside table-cell scope).

**Component change (no JS touched)**
- `components/edz/tooltip.blade.php`: new `block` prop ? adds `edz-tooltip--block` + `edz-tooltip__trigger--block`.
- `css/components/_tooltip.scss`: `.edz-tooltip--block, .edz-tooltip--block .edz-tooltip__trigger { display:block; width:100% }` — for `w-full`/truncated cells (products, quantity/price, notes, address, non-managed branches) so buttons don't collapse to content width. Long labels (items summary, address, notes) live in the bubble; `aria-label` supplies the accessible name instead of `title`.

**Conversion evidence (grep, source files unchanged except these)**
- `orders-table-actions-column.blade.php`: `title=` ~8 ? **0**; `x-edz.tooltip` opens **8** (details/confirm/send/cancel/edit/reassign/restore/delete, compact layout only). List layout passes `label="<?= $layout==='compact' ? … : '' ?>"` ? inner `label=""` renders the slot bare (no wrapper, no title) ? no behavior change.
- `order-events-menu.blade.php`: `title="Order timeline"` ? **0** (1 virtual `:title=` on mobile-bottom-sheet remains by design); `x-edz.tooltip` **1** + `aria-label`.
- `orders-table-cell.blade.php`: `title=` ~20 ? **0**; `x-edz.tooltip` opens **19** — customer name (2 branches), duplicate/missing badges (×2 partial wrappers), verification span, notes cell (cell `title` removed, both branches wrapped, block on static branch), meta cell, wilaya/city hints (block + `mb-1` wrapper), products cell (cell `title` removed; `$itemsSummaryTitle` via matched comma list; block both branches), quantity/price (block + `aria-label` edit_items), discount button (`$discountTitle` percent?%, amount?currency, else ''), address cell (cell `title` removed; correct `@endif` restored), warehouse toggle (icon-only ? tooltip + `aria-label`), status-list close button.
- Total Phase-1 tooltips added: **28** (8 + 1 + 19).

**New test: `tests/Feature/Merchant/OrderTooltipTest.php` (5 tests, helper prefix `otl*` to avoid collisions with `otOrder` in OrderTrackingTest / `ospOrder` in OrderShippingProviderColumnTest)**
- 4 via raw `view()` (compact + list actions columns); products-block coverage goes through `Volt::test('merchant.orders.index')` with `visibleColumns=['products']` (asserts `edz-tooltip--block`, `aria-label` edit_items, and rendered summary text `Default ×2` — the variant name, since the fixture sets `product_variant_id` without `product_id`, so `$i->product?->name ?? variant->name` resolves to "Default").

**Verification**
- `php artisan view:cache` OK (all ~5,672-line index + partials compile).
- **OrderTooltipTest 5 passed (18 assertions)** — one flaky Windows-view-compile `rename Access denied` first run, green on clean re-run (no `view:clear`/delete needed second time).
- **Full `tests/Feature/Merchant` + Unit sweep: 737 passed, 2 failed (2811 assertions) — both failures are NOT from this batch:**
  - `BladeInteractivityPolicyTest` (Unit): fails at HEAD too (offender `merchant/tracking/partials/tracking-tabs.blade.php` conflicts with the @js/@class-never-in-JS-attributes rule) — verified by full stash to clean HEAD, still failing ? pre-existing, untouched.
  - `TrackingStatusHistoryPopupTest`: caused by the **Round-5 uncommitted** `TrackingGridConcern` live-carrier filter `whereNotNull('shipping_provider_id')` (its fixture seeds no provider, so the order drops off the carrier tab) — isolated by stashing only that file ? 3/3 pass; restoring it reverts to 1 fail. A Round-5 regression the batch missed because it validated only its own suites; needs a follow-up fix (seed `shipping_provider_id` in `tphOrder` or adapt assertion).
- **Follow-ups queued:** real-browser responsive pass 375/768/1440 (especially new `block` cells + long summary bubbles) — browser tooling still not installed (documented class-level); fix `TrackingStatusHistoryPopupTest`; then Phase 2 (products/variants/stock) ? Phase 3 (delivery providers + announced-rates) ? Phase 4 (`components/status.blade.php` optional `tooltip` prop).

## Tooltip batch — Phase 1 follow-up (2026-09-14) — both pre-existing suite failures fixed; full suite green

- **BladeInteractivityPolicyTest (Unit):** offender `merchant/tracking/partials/tracking-tabs.blade.php` used `@js($this->trackingTab)` inside `x-data` (Blade interpolation in a JS-bearing Alpine attribute). Fixed per the policy itself: server value now goes via `data-edz-active-tab="{{ $this->trackingTab }}"` on the wrapper and is read with `$el.dataset.edzActiveTab` inside `restoreTab()`. `persistTab` unchanged.
- **TrackingStatusHistoryPopupTest:** the Round-5 `TrackingGridConcern` live-carrier filter (`whereNotNull('shipping_provider_id')`) filters the **orders table**, so the fixture needed a shipping provider on the ORDER itself, not only the tracking row. `tphOrder` now seeds a `ShippingProvider` ('Tph Carrier') and sets both `order->shipping_provider_id` and `order_tracking->shipping_provider_id`.
- **Full suite: 739 passed (2812 assertions) green** — was 737 passed / 2 failed. `OrderTooltipTest` 5 + tracking popup 3 + policy 2 = 10 all pass in isolation too.


---

## ãäÓøŞ ÚäÇÕÑ ÇáØáÈ ÇáãæÍøÏ (Unified Order Items Formatter) — 2026-09-14 ?

**ÇáåÏİ:** ãÕÏÑ æÇÍÏ áÊÌãíÚ ÃÓØÑ ÇáØáÈ ÍÓÈ ÇáãäÊÌ (ãÚ ÊÓãíÇÊ ÎíÇÑÇÊ ÇáãÊÛíøÑ + SKU) íõÓÊÎÏã İí ßá ãæÇÖÚ ÇáÚÑÖ¡ ÈÏá ÊßÑÇÑ ãäØŞ ÇáÊÌãíÚ ÚÈÑ ÇáßæãÈæääÊÇÊ.

**ãÇ Êóãø:**
- **`app/Domains/Orders/Support/OrderItemsGrouper.php`** (115 ÓØÑğÇ) — íÌãøÚ ÇáÃÓØÑ ÍÓÈ `product_id` Ãæ ÇáãÊÛíøÑ ãÚ ÇáÍİÇÙ Úáì ÊÑÊíÈ ÇáÙåæÑ¡ æíÏãÌ ßãíÇÊ ÇáãÊÛíøÑÇÊ ÇáãÊØÇÈŞÉ (`keyBy`/`first`/`update`) æíäÊÌ `variant_id`, `option_label` (Şíã ÇáÎíÇÑÇÊ ãİÕæáÉ ÈÜ ` / ` ãËá «Medium / Beige»)¡ `sku`.
- **`app/Domains/Orders/Support/OrderItemsFormatter.php`** (225 ÓØÑğÇ) — ÃÑÈÚÉ ÚæÇÑÖ ãä ãÕÏÑ æÇÍÏ:
  - `toCompactString()` — «ÇáÇÓã ×ÇáßãíÉ» ÈİæÇÕá + İÇÕá ÓØÑ ÚäÏ 4+ ÚäÇÕÑ¡ æÓŞæØ ÚÏÏ ÇáÃÓØÑ ÚäÏ ßá ÇáÃÓãÇÁ İÇÑÛÉ (`$items->count()` = ÚÏÏ ÇáÃÓØÑ ÇáÎÇã áÇ ãÌãæÚ ÇáßãíÇÊ).
  - `toDetailedLines()` — ãÌãæÚ ÇáßãíÇÊ İæŞ «ÊÓãíÉ ÇáÎíÇÑ × ÇáßãíÉ» áßá ÓØÑ.
  - `toTableGroups()` — ãÌãæÚÇÊ `{product_id, product_name, chips}` æÔÙÇíÇ `{variant_id, label, sku, qty, price}` (íõÖÇİ `variant_id` áíÚíÏ İßø ÇáÊÓØíÍ İí äãæĞÌ ÇáÊÚÏíá).
  - `toFlatItems()` — ÃÓØÑ ãÓØøÍÉ `{variant_id|product_id|name|sku|price|qty}` ÊõÍÇİÙ Úáì ÇÓÊŞÑÇÑ ÊÍæøÑ ÍŞæá äãæĞÌ ÇáÊÚÏíá (ÇáãİÊÇÍ = `variant_id`).

**ãæÇÖÚ ÇáÑÈØ ÇáÎãÓÉ:**
- `NoestIntegrationAdapter::productSummary()` ? `toCompactString()` ãÚ `loadMissing('items.variant.optionValues.option')` (ßÇä ãäØŞ ãŞÕæÕğÇ íÍĞİ ÇáÊİÑøÚÇÊ ÈáÇ ÎíÇÑÇÊ).
- `TrackingDrawerConcern::openLabel()` ? `labelData['items']` = `toDetailedLines()` (ŞÇÆãÉ ÃÓØÑ äÕíÉ) + eager-load `items.variant.optionValues.option`.
- `orders/index.blade.php`: ãõÛáóŞóÊóÇ `$buildItemSummary`/`$buildItemGroups` ŞÈá `$decorateOrder` (ÓØÑÇ 914/920)º `decorateOrder` íÈäí `items_summary` ãÓØøÍğÇ ÚÈÑ `$order->items->pipe(fn($items) => app(OrderItemsFormatter::class)->toFlatItems($items))->toArray()` (ÓØÑ 975 — íõÈŞí ÈÍË ÇáÇÓÊŞÈÇá `items_summary'] = $order->items` = 1) æ`item_groups` ÚÈÑ `$buildItemGroups($order)` (ÓØÑ 976) + `use ($buildItemGroups)`º `openOrderDetails` íÓÊÎÏã ÇáãÛáŞóíä ãÚğÇ (`use ($buildItemSummary, $buildItemGroups)`) ááãÓÇÑíä (ÑæÇÉ with æhydrate ÚäÏ ~1727/1728) — ÈáÇ ÊßÑÇÑ ãäØŞ.
- `orders-table-cell.blade.php` (`@case('products')`) — íŞÑÃ `item_groups` (ãÚ ÓŞæØ ãÓØøÍ Åáì `items_summary`) æíÈäí `$itemsSummaryTitle` æíÑÓã `<x-edz.order-item-chips>` İí İÑÚí ÇáÊÚÏíá æÛíÑ ÇáÊÚÏíá ÏÇÎá `x-edz.tooltip` (ãÚ ÊÕãíã `block`) æÈáÇ `title=` ÃÕáí.
- `order-details-modal.blade.php` — íØæİ `item_groups` æíÍÓÈ `$groupSubtotal` (ãÌãæÚ price×qty áßá ãÌãæÚÉ) æíÕäÚ ÔÑíÍÉ `<x-edz.order-item-chips :groups="[$group]" />` + ÇáãÌãæÚ ÇáİÑÚí.

**Çáãßæøä ÇáÌÏíÏ:** `components/edz/order-item-chips.blade.php` — ÇÓã ÇáãäÊÌ + «ÊÓãíÉ ÇáÎíÇÑ ×ÇáßãíÉ` áßá ÊİÑøÚ» æÔÇÑÇÊ SKU ÇáãæÍøÏÉ æÇáÚäæÇä¡ ÈãİÇÊíÍ `variant`/`sku` ÇáãæÌæÏÉ ×4 áÛÇÊ.

**ÇáÅÕáÇÍÇÊ ÇáÌÇäÈíÉ:** ÅÒÇáÉ ÇáÜ `@if` ÇáÒÇÆÏÉ Úä ÇááÇÒã İí ÎáíÉ ÇáÚãíá¡ æ`eading` ÊÓãíÇÊ ÇáãÊÛíøÑÇÊ ãä ÇáãÕÏÑ ÇáãæÍøÏ ÈÏá ÎÑíØÉ `optionValueId => name` ÇáãÊäÇËÑÉ.

**ÇáÊæËíŞ:** ÓØÑ İí `CarrierIntegrationContract` íÍíá ÇáÈÇÍËíä Åáì `productSummary()` ÇáÌÏíÏ (ÈáÇ ÃÓáæÈ æÇÌåÉ ÌÏíÏ).

**ÃÏáÉ ÇáŞÈæá (ÊÍŞøŞ Âáí):** `php -l` äÙíİ Úáì ßá ÇáãáİÇÊ ÇáãÛíóøÑÉ + `php artisan view:cache` (FRESH-CACHE-OK) + ÚÏøÇÏ `Blade::render` íÚíÏ 1 ááÈÍË `items_summary'] = $order->items` æãÕÛğøÇ 5 ááãÛáŞÇÊ æ2 áÇÓÊÎÏÇã `item_groups|order-item-chips` İí ÇáÜ partials.

**ÇáÇÎÊÈÇÑÇÊ:** ãáİ ÌÏíÏ `tests/Feature/Merchant/OrderItemsFormatterTest.php` — **16 äÇÌÍ (ÇáÚÏøÇÏÇÊ æÇáÊÌãíÚ ÚÈÑ ØáÈóíä¡ ÊİÑøÚÇÊ ãÊÚÏÏÉ¡ ÎíÇÑÇä «/», ÃÓØÑ İÇÑÛÉ¡ æÓŞæØ `/\d+ items/`)**: ßáåÇ ÎÖÑÇÁ. ŞíæÏ ŞÇÚÏÉ ÇáÈíÇäÇÊ ÇáãæËøŞÉ İí ÇáÇÎÊÈÇÑ: `order_items.product_variant_id` NOT NULL (áÇ ÃÓØÑ ÈáÇ ÊİÑøÚ)¡ `product_variants.sku` NOT NULL¡ `order_items` İÑíÏ `(order_id, product_variant_id)`¡ åæÇÊİ ÇáÚãáÇÁ İÑíÏÉ¡ æ`ProductOption`/`ProductOptionValue` íÊØáÈÇä `store_id`.

**ÇáÓæíÊ ßÇãáÉ:** **739 äÇÌÍ (2812 assertions)** — ÕİÑ ÇäÍÏÇÑ Úä «Tooltip Phase 1 follow-up». `view:clear`+`view:cache` ÓáíãÇä.

---

## ÏİÚÉ ÔÈßÉ ÇáÊÊÈÚ — ÇáãÑÇÍá 0–2: ÊŞÓíã ÇáÔÈßÉ + ÅÚÇÏÉ ÊÑÊíÈ ÇáÃÚãÏÉ + ÚãæÏ ÇáãäÊÌÇÊ æİáÊÑå — 2026-09-15 ?

**ÇáäØÇŞ (ÈãæÇİŞÉ ÇáãÓÊÎÏã Úáì ÎØÉ ÇáÊÓÚ ãÑÇÍá):** ÇáÌæáÉ ÇáÃæáì ãä «ÊÍÏíË ÔÈßÉ ÇáÊÊÈÚ d9e36ea» — ÊõäİóøĞ ãÑÍáÉ ÈãÑÍáÉ¡ ßá ãÑÍáÉ ÊõÎÊÈÑ æÊõÚÑÖ¡ Ëã íõØáÈ ÇáãæÇİŞÉ ŞÈá ÇáÊÇáíÉ.

- **ÇáãÑÍáÉ 0 (ÊŞÓíã ÇáÔÈßÉ):** `tracking-list.blade.php` ãä 396 ÓØÑğÇ ? **88** (ÌÏæá + ÈØÇŞÇÊ ÇáãæÈÇíá + ÊÑŞíã ÇáÕİÍÇÊ)º ÇÓÊõÎÑÌ `partials/tracking-row-cell.blade.php` (**193**¡ `@switch($colKey)` ÎáíÉ áßá ÚãæÏ) æ`partials/tracking-mobile-card.blade.php` (**129**). ÇÓÊÎÑÇÌ ÕÑİ ÈáÇ ÊÛííÑ Óáæßí.
- **ÇáãÑÍáÉ 1 (ÅÚÇÏÉ ÊÑÊíÈ ÇáÃÚãÏÉ):** `TrackingColumnConcern.php` — ÊÑÊíÈ ÃÓÇÓí ÌÏíÏ `number, tracking_number, customer, state, city, products, total, tracking_status, notes, <provider|delivery_rider>, shipping_date, assigned_to, confirmed_by, actions` ãÚ ÅÏÑÇÌ ÚãæÏ ÇáÊÈæíÈ ÈÚÏ ÇáİåÑÓ 9º `trackingDefaultOrder()` ááÊÈæíÈíäº `prefs_version` 2?3 İí `loadTrackingTabPreferences` æ`saveColumnPreferences` (ÅÚÇÏÉ ÊÚííä legacy áãÑÉ æÇÍÏÉ). äõŞøÍÊ ÊæŞÚÇÊ ÇáÃÚãÏÉ İí `TrackingTrashWebhookLabelTest` æ`TrackingSearchFilterTest`.
- **ÇáãÑÍáÉ 2 (ÚãæÏ ÇáãäÊÌÇÊ + İáÊÑ ãÊÚÏÏ ÇáÈÍË):** ÇÓÊõÎÑÌÊ `app/Livewire/Concerns/TrackingFilterConcern.php` (**141**) ãä `TrackingGridConcern` (ÇáÊí ÊÌÇæÒÊ 457 ÓØÑğÇ — ÇÓÊÎÑÇÌ ŞÈá ÇáÅÖÇİÉ): `clearFilters`/`availableFilterGroups`/`activeFilterCount`/`setFilter`/`toggleTrackingStatus` + ÌÏíÏ `toggleProductFilter`. `TrackingGridConcern` (349): eager-load `items.product:id,name` (áÇ N+1)¡ `products` ÇáÕİøí `{name, qty}` ãÌãøÚğÇ ÈÇáÇÓã (ÊÑÔíÍ ÇáÃÓãÇÁ ÇáİÇÑÛÉ/«—»)¡ İáÊÑ `whereHas('items', whereIn('product_id'))` ÈãäØŞ OR. `index.blade.php` (Volt, 326): `allProducts` ãä `Order::withTrashed()->join('order_items')` (ãÄåóøá `orders.store_id` áÑİÚ ÛãæÖ ÇáÚãæÏ). ÇáæÇÌåÉ: `tracking-table-header` ÃÖÇİ `products => products` + ÇáÊİÚíáº `tracking-filter-portal` ÃÖÇİ ŞÓã ÇáãäÊÌÇÊ (ãÊÚÏÏ ÇáÇÎÊíÇÑ¡ äãØ `status`¡ `sm:max-h-64`/`sm:w-52`)º `tracking-row-cell` ÎáíÉ ÇáãäÊÌÇÊ: ÇÓã Ãæá ãäÊÌ ãŞÊØÚ `max-w-[9rem]` + ÔÇÑÉ `+N` ÏÇÎá `x-edz.tooltip` ÈŞÇÆãÉ ßÇãáÉ¡ æÅáÇ `—`.
- **ãáÇÍÙÉ Blade:** `:label="…->implode("\n")"` íßÓÑ ãÍáøá æÓæã ÇáãßæøäÇÊ (`"` ÏÇÎá ÊÚÈíÑ ÇáÎÇÕíÉ) — ÇáÍá: ÈäÇÁ ÇáÓáÓáÉ İí `@php` æÑÈØ `:label="$var"`º æÚÏã æÖÚ ãßæøä ÏÇÎá İÑÚ `@if` íáíå `@else` (íäßÓÑ ÇáÇŞÊÑÇä) — ÇáİÑæÚ ÇáãÓÊŞáÉ `@if…@endif` ÇáãÊÚÏÏÉ ÓáíãÉ.

**ÇáÇÎÊÈÇÑÇÊ:** ãáİ ÌÏíÏ `tests/Feature/Merchant/TrackingProductsColumnTest.php` — **4 äÇÌÍ (9 assertions)**: ÚãæÏ ÇáãäÊÌÇÊ ÅáÒÇãí ÈÇáãæÖÚ ÇáÇİÊÑÇÖí 5 æÛíÑ ŞÇÈá ááÅÎİÇÁº ÇáÕİ ÇáãÎØóøØ ãÌãóøÚ ÇáßãíÇÊ `{name, qty}` (ØáÈÇä¡ ÃÍÏåÇ ÈãäÊÌíä) + `allProducts`º ÇáØáÈíÉ ÈáÇ ÃÕäÇİ ÊÚÑÖ ŞÇÆãÉ İÇÑÛÉ (ÔÑØÉ `—`)º İáÊÑ ãäÊÌÇÊ ãÊÚÏÏ `toggleProductFilter` íÌãøÚ OR ÚÈÑ ØáÈóíä Ëã `setFilter('products', [])`. ÇáÓæíÊ Ğæ ÇáÕáÉ (æÑÏ ÇáÊÊÈÚ) **61 äÇÌÍ (252 assertions)** ãäİÑÏÉ — 57 ŞÇÆãÉ + 4 ÇáÌÏíÏÉ ? ÕİÑ ÇäÍÏÇÑ. `php -l` + `view:cache` ÓáíãÇä.

- **ÇáãÑÍáÉ 3 (ÈÍË ÏÇÎá ŞæÇÆã ÇáİáÇÊÑ + ÇÓÊËäÇÁ ÇáãÇáß):** ãßæøä Alpine ÌÏíÏ `filterableList()` İí `resources/js/panel.js` — íŞÑÃ ÇáŞÇÆãÉ ãä `data-items` æÇáÇÎÊíÇÑÇÊ ãä `data-active` (ÓãÇÊ `data-*` ÚÇÏíÉ¡ áÇ `@json` ÏÇÎá ÓãÇÊ Alpine áÓíÇÓÉ Blade)¡ ãÚ `query`/`filtered`/`isActive`/`activeCls`/`checkCls`º íÚíÏ ŞÑÇÁÉ `data-active` ÚäÏ `edz-filter-open`/`edz-toolbar-filter-open` (ÊäÙíİ ÇáãÓÊãÚíä İí `destroy()`). ÍæøáÊ 6 ŞæÇÆã İí `tracking-filter-portal` (products¡ provider¡ rider ãÚ ÇáÏãÌ `$riderSearchItems` ááÚÏøÇÏÇÊ¡ assigned_to¡ confirmed_by¡ city) æ5 İí `tracking-filter-bar-portal` (provider¡ city¡ rider¡ assigned_to¡ confirmed_by) Åáì `x-for` + ÍŞá ÈÍË ÈÚäæÇä `__('general.search')`º ÈŞí ŞÓã status (ÊÚÏÇÏ 9 ÚäÇÕÑ) ãõÎÏóøãğÇ ãä ÇáÎÇÏã. **ÏÑÓ Blade ÌÏíÏ:** `:class="…"` Úáì ãßæøä `x-edz.icon` íõÌáóøÈ ÌÇİğÇ ßÊÚÈíÑ PHP (íäßÓÑ) — ÇáÍá `x-bind:class` ÏÇÎá ÇáãßæøäÇÊ İŞØ (ÇáæÓæã ÇáÚÇÏíÉ ÊÈŞí `:` áÜ Alpine).
- **ÇÓÊËäÇÁ ÇáãÇáß:** `allMembers` İí `index.blade.php` íÓÊÈÚÏ ÚÖæíÉ `user_id = store.user_id` (ÇáãÇáß áíÓ æßíá ÅÓäÇÏ/ÊÃßíÏ). ÍÏóøËÊ `TrackingGridBatchTest`: ãÓÇÚÏ `tgbManager` (ãÓÊÎÏã + ÚÖæíÉ `manager`)¡ ÇÎÊÈÇÑÇ assigned_to/confirmed_by íÚÊãÏÇä ÇáãÏíÑ æíÄßÏÇä ÇÓÊÈÚÇÏ ÚÖæíÉ ÇáãÇáß¡ æÃÓãÇÁ ÇáÃÚãÏÉ «Assign Agent»/«Confirm Agent» ÚÈÑ ÇáÚáÇŞÇÊ (ÊÙá ÃÓãÇÁ ÇáÃÚÖÇÁ İí ÇáÕİæİ ŞÇÆãÉ ãä ÇáÚáÇŞÇÊ áÇ ãä `allMembers`).
- **ÇáÊÍŞŞ:** ÇáÓæíÊ Ğæ ÇáÕáÉ (æÑÏ ÇáÊÊÈÚ) **61 äÇÌÍ (252 assertions)** ÕİÑ ÇäÍÏÇÑ + `BladeInteractivityPolicyTest` äÇÌÍº `npm run build` äÙíİº `php -l` + `view:cache` ÓáíãÇä.

- **ÇáãÑÍáÉ 3 (ãÑÇÌÚÉ — ßÇÔ ÇáÈÍË İÑæäÊ İŞØ¡ ÈØáÈ ÇáãÓÊÎÏã):** ÇáÈÍË ÃÕÈÍ İÑæäÊ-Ãæäáí: ÇáŞæÇÆã ÊõÍãóøá ãÑÉ æÇÍÏÉ İí ßÇÔ JS æÊõÍÏóøË İŞØ ÚäÏ ÊÛíøÑ ÇáÈíÇäÇÊ ÇáÎÇÏãíÉ. ÌÏíÏ `app/Livewire/Concerns/TrackingSearchCacheConcern.php` (`searchCacheSeed`/`searchFingerprint` = md5(json)…/`searchOptionsLists` íõÏãÌ ÚÏøÇÏÇÊ ÇáÑÌøáíä ÎÇÏãíğÇ İí ãÌãæÚÉ `riders`)º İí `panel.js` æÍÏÉ `edzSearchCache` (ÇáÈÕãÉ + ÇáŞæÇÆã ÈãİÊÇÍ ÇáãÌãæÚÉ) æãßæøä `edzSearchSeed` íŞÑÃ `data-search-cache` İŞØº `filterableList` íÚíÏ ŞÑÇÁÉ `data-active` ÇáÕÛíÑÉ ÚäÏ ÇáãÊÔÑíä (æáã íÚõÏ íŞÑÃ `data-items` İí ßá İÊÍÉ). `index.blade.php`: æÓíØ ÇáÈĞÑ ÇáÎİí `<div class="hidden" x-data="edzSearchSeed()" data-search-cache="@json($this->searchCacheSeed())">` ÈÚÏ ÌĞÑ `<div>` — íÓÊÈÏáå Livewire İÊõÚÇÏ ÊåíÆÉ Alpine (ÊÍÏíË ÇáßÇÔ) İŞØ Ííä íÛíøÑ JSON. ÇáÈæóøÇÈÊÇä ÊÍæøáÊÇ Åáì `data-group="products|providers|riders|members|cities"` æÍõĞİÊ ßÊá ÏãÌ ÇáÑÌøáíä `@php` ãä ßáíåãÇ. ÊÍŞŞ: `php -l` + `view:cache` + **42 äÇÌÍ (172 assertions)** (TrackingSearchFilterTest+TrackingGridBatchTest+TrackingProductsColumnTest) + ÓíÇÓÉ Blade + `npm run build`.

---

## ÏİÚÉ ÔÈßÉ ÇáÊÊÈÚ — ÇáãÑÇÍá 4–7: İáÊÑ ÇáÊÇÑíÎ ÇáãÓÊŞá + ÎáíÉ ÔÚÇÑ ÇáäÇŞá + ÏÑÌ ÈáÇ ãÄáøİÇÊ + ãÊÏÑøÌ ÇáÍÇáÇÊ — 2026-09-15 ?

**ÇáäØÇŞ (ÈãæÇİŞÉ ÇáãÓÊÎÏã «Çßãá ßá Phases»):** ÇÓÊßãÇá ãÑÇÍá ÅÚÇÏÉ ÊÕãíã ÔÈßÉ ÇáÊÊÈÚ ÈáÇ ÈæÇÈÉ ãæÇİŞÉ áßá ãÑÍáÉ — ÊõÎÊÈóÑ ßÇãáğÇ æÊõÓÌóøá Ëã ÊäÊŞá ááÊÇáíÉ.

- **ÇáãÑÍáÉ 4 (İáÊÑ ÇáÊÇÑíÎ ßãäØáŞ ãÓÊŞá):** ÃõÒíá `'date'` ãä `availableFilterGroups()` İí `TrackingFilterConcern` (áã íÚÏ ÏÇÎá ÈæÇÈÉ ÇáÊãÑíÑ) æÈóŞöí `date_from`/`date_to` ãÚÏæÏğÇ İí `activeFilterCount()` ÕÑÇÍÉğ ÎÇÑÌ ÇáÍáŞÉ. ÒÑ ÊŞæíã ãÓÊŞá İí `tracking-toolbar.blade.php` (ÔÇÑÉ ÚÏøÇÏ 1/2 ÚäÏ ÇáÇãÊáÇÁ¡ íÈË `edz-date-filter-open` ãÚ `{ el }`) + ÌÒÆíÉ ÌÏíÏÉ `partials/tracking-date-portal.blade.php` (äãØ `dropdownPosition()` + æÑŞÉ ÓİáíÉ ãæÈÇíá/ÚáÈÉ ÓØÍ ãßÊÈ + ÍŞáÇ flatpickr `wire:model.blur` + ÒÑ ãÓÍ) ÊõÖãóøä İí `index.blade.php`. ÃõÒíá ŞÓã DATE ÇáãíÊ (ÃÓØÑ ~142–164) æ`'date' => __('order_flow.filter_date')` ãä `$groupLabels` İí `tracking-filter-bar-portal` (ŞÓã ÇáÊÇÑíÎ İí ÈæÇÈÉ ÇáÊÑæíÓÉ ááÚãæÏí ÈŞí ßãÇ åæ). ÌÏíÏ `clearDateFilter()` — ãÓÍ ÇáÍŞáíä İí ÑÍáÉ æÇÍÏÉº ÇÓÊÎÏãÊå ÔÑíÍÉ ÇáÊæÇÑíÎ İí ÇáÊæáÈÇÑ.
- **ÇáãÑÍáÉ 5 (ÎáíÉ ÔÚÇÑ ÇáäÇŞá):** eager-load `shippingProvider.carrier` İí `TrackingGridConcern` (ßÊáÊÇ `with()` — áÇ N+1) + ÍŞá `provider_logo` İí ÎÑíØÉ ÇáÕİ = `shippingProvider?->carrier?->logo`. ÎáíÉ `@case('provider')` İí `tracking-row-cell`: ÏÇÆÑÉ ÍÑİ-Ãæá (letter-circle) ÈÎáİíÉ `bg-accent-surface` ÊÍãá ÔÚÇÑ ÇáäÇŞá `w-6 h-6 rounded-full object-cover` İæŞåÇ (`relative` ãÊÏÑøÌ — áæ ÇäßÓÑ ÇáÕæÑÉ ÒÇá `onerror="this.remove()"` æÙåÑ ÇáÍÑİ) + ÇÓã ãŞÊØÚ `max-w-[10rem]`. ÚãæÏ ÇáäÇŞá ÊáŞÇÆíğÇ ÎÇÕ ÈÊÈæíÈ ÇáäÇŞá İŞØ.
- **ÇáãÑÍáÉ 6 (ÇáÏÑÌ ÈáÇ ãÄáøİÇÊ):** ÃõÒáÊ `@include` áãßæøäóí `carrier-note-composer` æ`tracking-history-timeline` ãä `order-drawer.blade.php` (ÈíÇäÇÊ ÇáÊÍãíá ãÍİæÙÉ). ÍõÏöøË `OrdersTrackingColumnTest`: ÇáÏÑÌ áã íÚõÏ íÖã ÊØãíä `partials.tracking-history-timeline` (`not->toContain`).
- **ÇáãÑÍáÉ 7 (äÇİĞÉ ÇáÍÇáÇÊ ÇáãÊÏÑøÌ + äŞá ÇáãÄáøİ + ÑÇÈØ ÇáÊÊÈÚ ÇáÚÇã):**
  - **ÇáãÊÏÑøÌ:** ÌÏíÏ `partials/tracking-status-stepper.blade.php` — ãÑÇÍá ÃİŞíÉ `shipped?in_transit?out_for_delivery?delivered?returned` (ÈÊÑÊíÈ `OrderWorkflow::carrier()`¡ ÊÓãíÇÊ/ÃáæÇä mystatuskit)¡ ÚŞÏÉ ÇáãäÌÒÉ `check` ÈÎáİíÉ accent¡ ÇáÍÇáíÉ ÍáŞÉ ãáæäÉ¡ ÇáãõÊÈŞíÉ ÊÑŞíã ãÍÇíÏ¡ æÕáÇÊ `flex-1` ãáæøäÉ ááãäÌÒº ÇáÇÓÊËäÇÁÇÊ (failed_attempt/lost/damaged/returning) ÊõÈŞí ÇáÓßÉ ãÍÇíÏÉ æÊõÚÑÖ ßÑŞÇŞÉ mystatuskit ÌÇäÈíÉ.
  - **äÇİĞÉ ÇáÍÇáÇÊ `tracking-history-popup`:** ÊÖã ÇáãÊÏÑøÌ + ÑÇÈØ «ÊÊÈøÚ Úáì ãæŞÚ ÇáÔÑßÉ» ÇáÎÇÑÌí (Åä æõÌÏ ÇáŞÇáÈ) + ÇáãÄáøİ ÇáãäŞæá + ÇáÎØ ÇáÒãäí ÇáãÔÊÑß. `statusHistoryMeta` ÒÇÏÊ `tracking_id`/`tracking_status`/`carrier_supports_api_notes`/`public_tracking_url`¡ æ`openStatusHistory` íÍãøá `latestTracking.shippingProvider.carrier` (áÇ N+1). `carrier-note-composer.blade.php` ÃõÚíÏÊ ßÊÇÈÊåÇ áÊŞÑÃ ÓíÇŞ `statusHistoryMeta` (áíÓÊ `drawerTracking`)º ÈÚÏ ÇáÅÑÓÇá ÊõÒÇãóä `statusHistory` ãÚ `drawerStatusHistories` Åä ßÇäÊ ÇáäÇİĞÉ ãİÊæÍÉ áäİÓ ÇáØáÈíÉ.
  - **ÊÊÈÚ ÚÇã:** ÅÖÇİÉ `public_tracking_url_template` nullable Åáì `carriers` (åÌÑÉ `2026_09_16_000002`) + ÍŞá Filament `TextInput` ÈÌæÇÑ ÇáÔÚÇÑ (helperText íÔÑÍ `{tracking_number}`) + `Carrier::publicTrackingUrl(string): ?string` (íÓÊÈÏá `{tracking_number}`/`{trackingNumber}` ÈÜ `urlencode`¡ íİÔá ÂãäğÇ ÚäÏ İÑÇÛ) + ãİÇÊíÍ `order_flow.carrier_track_on_site` ×4 áÛÇÊ.
- **ÇáÊÍŞŞ:** `php -l` äÙíİ Úáì ßá ãáİÇÊ PHP ÇáãÊÃËÑÉ + åÌÑÉ/`view:clear`+`view:cache` + ÇÎÊÈÇÑ Unit ÌÏíÏ `tests/Unit/CarrierPublicTrackingUrlTest.php` (3) + ÓíÇÓÉ Blade + **ÓíøæÑÉ ÇáÊÊÈÚ 64 äÇÌÍ (265 assertions)** ÕİÑ ÇäÍÏÇÑ + `npm run build` (ÊÍĞíÑ chunk >500kB ŞÇÆã ÛíÑ ãÓÈÈ).

---

## ÏİÚÉ ÔÈßÉ ÇáÊÊÈÚ — ÇáãÑÍáÉ 8: ÇÚÊãÇÏ ÌãÇÚí áÏì ÇáäÇŞá (FAB + /valid/orders ãÌÒøÃÉ) — 2026-09-16 ?

**ÇáäØÇŞ (ÇÓÊßãÇá ÎØÉ ÇáãÑÇÍá ÇáãÚÊãÏÉ):** ÇÚÊãÇÏ ÇáÊÓáíã ÇáÌãÇÚí ãä ÕİÍÉ ÇáÊÊÈÚ ÚÈÑ FAB (ÊÈæíÈ ÇáäÇŞá İŞØ) — ÊÍáíá ÕİÍÉ ÇáÕİæİ ÇáÍÇáíÉ¡ ãÓÍ ÈÇÑßæÏ İÑÏí ÏÇÎá ÇáäÇİĞÉ¡ Ëã ÅÑÓÇá ÇáÌÇåÒ ááäÇŞá ÈæÇÌåÉ `/valid/orders` ÇáãÌÒøÃÉ (?100/ÇÓÊÏÚÇÁ) ãÚ ÅÍÏÇËíÇÊ OrderEvent áßá ÔÍäÉ.

- **ÇáäÇŞá/ÇáÊÏŞíŞ:** `OrderAuditService::carrierValidated()` (äæÚ ÍÏË `carrier_validated` + ÍãæáÉ ÇáÔÑßÉ/ÑŞã ÇáÊÊÈÚ) + ãİÇÊíÍ `order_flow.event_carrier_validated` ×4 áÛÇÊ. `OrderShippingGateway`: ÇÓÊõÎÑÌ `markValidated()` (ØÇÈÚ `carrier_validated_at` + ÓÌá `OrderTrackingHistory` + ÍÏË ÇáÊÏŞíŞ¡ ÈáÇ HTTP¡ ãÊØÇÈŞ) æ`recordValidationFailure()`º äÌÇÍ/İÔá `validate()` ÇáÃÕáí íİæÖ ÅáíåãÇ — İÃÕÈÍ ÇáÇÚÊãÇÏ ÇáİÑÏí íõÏŞøŞ ÇáÍÏË ÃíÖğÇ.
- **ÇáÊÊÈÚ (Volt):** ÌÏíÏÉ `app/Livewire/Concerns/TrackingBulkValidateConcern.php` — `openBulkValidateModal` (ÊÍáíá Õİæİ ÇáÕİÍÉ ÇáÍÇáíÉ Åáì `{order_id, number, provider, tracking_number, ready, reasons}` ÈäİÓ ÔÑæØ ãäØŞ ÏİÚÉ ÇáØáÈÇÊ: äÇŞá¡ ÊßÇãá íÏÚã `validateOrder`¡ ÑŞã ÊÊÈÚ¡ ÛíÑ ãÚÊãÏ)¡ `closeBulkValidateModal`, `confirmBulkValidate` (ÊÌãíÚ ÍÓÈ ÇáäÇŞá Ëã ÊÌÒÆÉ `array_chunk(...,100)` ? `validateOrders` áßá ÌÒÁ¡ ÊËÈíÊ ÇáäÌÇÍÇÊ ÚÈÑ `markValidated` æÇáİÔá ÚÈÑ `recordValidationFailure`¡ ÅÚÇÏÉ ÊÍãíá ÇáÕİæİ + ÊæÒíÚ)¡ `bulkValidateFromBarcode` (íÈÍË ÇáØáÈíÉ ÈÑŞã ÇáÊÊÈÚ æíÓáß ãÓÇÑ ÇáÇÚÊãÇÏ ÇáİÑÏí ÇáãÄóÏáÌ Ëã íäŞøÍ ÇáÊÍáíá). ãáÇÍÙÉ: ãİÇÊíÍ `Order` ãä äæÚ ULID äÕøí — áÇ `(int)` ÚäÏ ÌãÚ ÇáãÚÑøİÇÊ.
- **ÇáæÇÌåÉ:** ÌÒÁ `partials/tracking-bulk-validate.blade.php` — FAB ËÇÈÊ ÃÓİá-end (íÎÊÈÆ İí ÊÈæíÈ ÇáÑÌøáíä/ÇáÓáÉ/ÈáÇ ÕáÇÍíÉ) + äÇİĞÉ `x-edz.modal` İíåÇ `x-edz.barcode-scan-input` (`wireScanMethod="bulkValidateFromBarcode"`) æŞÇÆãÊÇ ÌÇåÒ/ãÄÌóøá æÒÑ ÊÃßíÏ (ãÚØøá ÚäÏ ÕİÑ ÌÇåÒ Ãæ ÃËäÇÁ ÇáÊäİíĞ). `index.blade.php`: `uses[]` + 5 ãİÇÊíÍ ÍÇáÉ + `@include`.
- **ÇáÇÎÊÈÇÑÇÊ:** ÌÏíÏÉ `tests/Feature/Merchant/TrackingBulkValidateTest.php` — **12 äÇÌÍ (138 assertions)**: ÑÄíÉ/ÅÎİÇÁ FAB (ÊÈæíÈ/ÓáÉ/ÕáÇÍíÉ)¡ ÊÍáíá ÇáÚÏÇÏ¡ ÇáÇÚÊãÇÏ ÇáÌãÇÚí ÇáãÓÊãÑ + `OrderTrackingHistory`/`OrderEvent`¡ ÇáÊÌÒÆÉ (>100 ? ÇÓÊÏÚÇÆÇä áÚäÇæíä `/valid/orders` ãÚ ãÌãæÚ 105 ÊÊÈÚÇÊ İÑíÏÉ)¡ ÇáÈÇÑßæÏ ÇáİÑÏí¡ ÇáÑİÖ ÈáÇ `order.dispatch_validate` Úáì ÇáãÏÇÎá ÇáËáÇËÉ. ÍõÕöøÑ ÊÃßíÏ `TrackingDispatchValidateTest` ÈÔÚÇÑ ÇáäÇİĞÉ áíŞíÓ ÇáÏÑÌ äİÓå (`wireScanMethod="validateShipmentFromBarcode"` ÛÇÆÈ ÚäÏ ÇáÇÚÊãÇÏ) ÈÏá ÇáäÕ ÇáÔÇãá ÇáãÊÓÑøÈ ãä ÇáäÇİĞÉ ÇáÌÏíÏÉ.
- **ÇáÊÍŞŞ:** `php -l` äÙíİ + `view:clear`+`view:cache` + ÊÔÛíá ãÌãóøÚ 102 äÇÌÍ (486 assertions) Ëã ÇáÓæíÊ ÇáãÚäí ÇáãÛáŞ (TrackingDispatchValidate 7 + TrackingBulkValidate 12 + OrdersTrackingColumn 5 + ÓíÇÓÉ Blade 2) — **26 äÇÌÍ (184 assertions)** ÕİÑ ÇäÍÏÇÑ + `npm run build` (ÊÍĞíÑÇÊ Sass/chunk ŞÇÆãÉ ÛíÑ ãÓÈÈÉ).
---

## ÇáİáÇÊÑ ÇáãæÍøÏÉ + ÈÍË ÃãÇãí + ÇáÊÍŞŞ ÇáãÌãøÚ ááØáÈÇÊ — 2026-09-15

**ÇáåÏİ (ÈãæÇİŞÉ ÕÑíÍÉ "äİĞ"):** ÅÕáÇÍ ÕİÍÉ ÇáÊÊÈÚ (İáÇÊÑ ÇáÑæÄÓ æÒÑ Filters áÇ ÊõÙåÑ ÔíÆğÇ)¡ ÊæÍíÏ äãØ ÇáŞæÇÆã ãÚ `/orders`¡ ÌáÈ ÇáŞæÇÆã ãÑÉ æÇÍÏÉ + ÈÍË ÃãÇãí (ÈáÇ ßÇÔ ËŞíá ÍÊì áÇ ÊÊÃÎÑ ÇáÈíÇäÇÊ ÇáãÍÏøËÉ)¡ æÑÈØ ÇáÊÍŞŞ ÇáãÌãøÚ áÕİÍÉ ÇáØáÈÇÊ ÈäİÓ ÊÏİŞ `/valid/orders` ÇáãŞÓøã.

- **Çáãßæøä/JS:** İí `resources/js/panel.js` ÍõĞİ `edzSearchCache` æ`edzSearchSeed` æ`filterableList`¡ æÃõÖíİ `edzSearchableList` (íŞÑÃ `data-items` + `data-active` ÇáãõÎÏøãíä¡ `query`/`filtered`/`isActive`/`activeCls`/`checkCls`/`toggleActive(id)` ááãÊÚÏÏ¡ íÚíÏ ŞÑÇÁÉ ÇáäÔØ ÚäÏ İÊÍ ÇáÈæÇÈÉ æíäÙøİ ÇáãÓÊãÚíä İí `destroy()`). ÍõĞİ `app/Livewire/Concerns/TrackingSearchCacheConcern.php` ædiv ÇáÈĞÑÉ ãä `tracking/index.blade.php` (áÇ ßÇÔ ÚÇã — ÇáŞæÇÆã ÊõÎÏóøã ãä ÇáÍÇáÉ ÇáÍÇáíÉ ÏÇÆãğÇ).
- **ÇáÊÊÈÚ:** ÊÍæíá ÈæÇÈÊí `tracking-filter-portal` æ`tracking-filter-bar-portal` ÈÇáßÇãá Åáì `edzSearchableList` (products/providers/riders ãÚ ÏãÌ ÇáÚÏÇÏÇÊ/ÇáãÓÊÎÏãæä×2/ÇáãÏä). İáÇÊÑ ÇáãæÖÚ STATE: `where('state_id', $f['state'])` İí `baseTrackingQuery`¡ ãİÊÇÍ ÑÃÓ İí `tracking-table-header`¡ ŞÓã ŞÇÈá ááÈÍË İí ÇáÈæÇÈÊíä¡ ÑŞÇŞÉ STATE+CITY İí ÇáÔÑíØ¡ `'state' => null` İí ÇáŞíã ÇáÇİÊÑÇÖíÉ æ`clearFilters`¡ æãÑÇŞÈ `filters.state`.
- **ÒÑ Filters:** ÃõÒíáÊ ÈæÇÈÉ `@if(!empty($this->availableFilterGroups()))` — ÇáÒÑ ÙÇåÑ ÏÇÆãğÇº `availableFilterGroups()` ÊõÚíÏ ßá ÇáãÌãæÚÇÊ (ãŞíøÏÉ ÈÇáÊÈæíÈ İŞØ: state/provider/tracking_statuses/amount/city/rider/assigned_to/confirmed_by).
- **ÇáØáÈÇÊ:** ÍæøáÊ 7 ŞæÇÆã İí `orders/partials/filter-portal.blade.php` (wilaya/status/provider/stopdesk/city/assigned_to/confirmed_by) æ6 İí `orders/partials/orders-filter-bar-portal.blade.php` Åáì `edzSearchableList`. **ÏÑÓ Blade:** `@json` ÏÇÎá ÓãÉ íõÛáŞ ÚäÏ Ãæá ÊæÇÒä ÃŞæÇÓ — ÊÚÈíÑ Óåã/ãÕİæİÉ İíå `Status::for(...)->label()` íõÈÊÑ ãäÊÕİğÇ ? äõŞáÊ ÇáÚäÇÕÑ Åáì ÎÇÕíÊí ãßæøä `searchableStatuses`/`searchableMembers` (İí mount: `{id,name,color}` ãä facade æÍá `user.name`) æÑõÈØÊ ßãÚÑøİ ÈÓíØ.
- **ÇáÊÍŞŞ ÇáãÌãøÚ ááØáÈÇÊ:** ÇÓÊõÈÏá ÇáÍáŞÉ ÇáÊÓáÓáíÉ (`$gateway->validate` áßá ØáÈ) İí `$confirmBulkValidate` ÈÇáÊÏİŞ ÇáãŞÓøã ãä `TrackingBulkValidateConcern`: ÊÌãíÚ ÍÓÈ ÇáãÒæøÏ + `array_chunk(...,100)` + `$adapter->validateOrders()` + `markValidated`/`recordValidationFailure`º ÃõËÑÊ Õİæİ `bulkValidateAnalysis` ÈÜ `order_id/provider/tracking_number`. ÍõÏøË `BulkDispatchValidateTest` (ÇÎÊÈÇÑ ÇáÑİÖ ÃÕÈÍ ÇÓÊÌÇÈÉ æÇÍÏÉ ÈÎÑÇÆØ `passed`/`failed` Öãä ÇáÏİÚÉ).
- **ÇáÊÍŞŞ:** `php -l` + `view:clear`+`view:cache` + ÇáÇÎÊÈÇÑÇÊ ÇáãÓÊåÏİÉ **67 äÌÇÍğÇ** (BulkDispatchValidate 4 + BladeInteractivity 2 + TrackingSearchFilter + TrackingBulkValidate + TrackingDispatchValidate + OrdersPageQueryCount + OrdersTrackingColumn + OrderShippingProviderColumn) + `npm run build`. **Ïíä íõÓÌóøá:** ÃÍÌÇã ÇáãáİÇÊ ÊÊÌÇæÒ ãíÒÇäíÉ 300 ÓØÑ ÇáãæÑæËÉ (filter-portal 402 / orders-filter-bar 580 / tracking-filter-bar 357) — ÇÓÊÎÑÇÌåÇ Åáì ÈÇÑÊíÇÊ áÇÍŞğÇ.

---

## ÅÕáÇÍ ÃÎØÇÁ ŞæÇÆã ÇáÇÎÊíÇÑ ÇáŞÇÈáÉ ááÈÍË İí ÕİÍÉ ÇáÊÊÈÚ — 2026-09-15

**ÇáÈÇÛ (ÇáãÓÊÎÏã):** "íæÌÏ ãÔÇßá İí ŞæÇÆã ÇáÇÎÊíÇÑ ÇáÊí áåÇ ÇáÈÍË áíÓÊ ãÊäÇÓŞÉ æáÇ ÊÙåÑ Çí ãÚáæãÇÊ İí ÕİÍÉ ÊÊÈÚ ÇáØáÈíÇÊ ÓæÇÁ İí ÑÄæÓ ÇáÇÚãÏÉ Çæ İí ÇáŞæÇÆã ÇáİÑÚíÉ íÌÈ ÇáİÍÕ ÈÔßá ãÚãŞ".

**ÇáÃÎØÇÁ ÇáãßÊÔİÉ:**
1. **BUG 1 (ÍÑÌ — ÚÏã ÇáÊæÇİŞ Èíä ÇáÃäæÇÚ):** `panel.js` `isActive(id)` íÓÊÎÏã `String(id)` áßä `parseActive()` íõÎÒøä ÇáŞíã ÇáÎÇã ãä JSON (ÃÚÏÇÏ) ? `[5].includes("5")` ÏÇÆãğÇ `false` ? áÇ ÊÙáíá æáÇ `aria-pressed`.
2. **BUG 2 (ÍÑÌ — İŞÏÇä ÃÚáÇã HEX):** `@json` ãÊÚÏÏ ÇáÃÓØÑ áŞÇÆãÉ ÇáÑÇßÈíä íõÊÑÌã Åáì `json_encode(..., 512)` İŞØ — ÊÙåÑ İŞØ `512` æÊİŞÏ `15` (`JSON_HEX_TAG|APOS|AMP|QUOT`). ÅĞÇ ÇÍÊæÊ ÇÓã Ãí ÑÇßÈ `"` Ãæ `<` Ãæ `'` ? ÇáÓãÉ ÊõŞØóÚ ? `JSON.parse` íõÑãí ÎØÃ ? `items = []` ? ÇáŞÇÆãÉ İÇÑÛÉ.
3. **BUG 3 (ãÊæÓØ — ŞÑÇÁÉ `parseItems()` ãÑÉ æÇÍÏÉ İŞØ):** `parseItems()` ÊõäİóøĞ ÚäÏ ÇáÊåíÆÉ İŞØº ÚäÏ ÊÛííÑ ÇáİáÇÊÑ íõÚíÏ Livewire ÑÓã ÇáÚäÇÕÑ ÈÇáÜ morph áßä Alpine ÊÍÊİÙ ÈÇáÍÇáÉ ÇáŞÏíãÉ (`items` İÇÑÛ Ãæ ŞÏíã).

**ÇáÅÕáÇÍÇÊ ÇáãäİøĞÉ:**
- **BUG 1:** İí `panel.js` `parseActive()`: ÅÖÇİÉ `.map(String).filter(Boolean)` ÈÚÏ JSON.parse ? ÇáŞíã ÊõæÍóøÏ ÏÇÆãğÇ ßäÕæÕ. ÇáÂä `isActive("5")` íÊØÇÈŞ ãÚ `this.active = ["5"]`. (`panel.js:254-258`)
- **BUG 3:** İí `_onPortalOpen` (ÇáãÓÊãÚ áÃÍÏÇË `edz-filter-open` æ`edz-toolbar-filter-open`) ? ßáÇ ÇáÏÇáÊííä `parseItems()` æ`parseActive()` ÊõÓÊÏÚíÇä ÚäÏ İÊÍ Ãí ÈæÇÈÉ ? ÇáÈíÇäÇÊ ÊÈŞì ØÇÒÌÉ ÈÚÏ ßá ÑÓã. (`panel.js:234-237`)
- **BUG 2 + BUG 3 ãÚÇğ:** äõŞáÊ ÚäÇÕÑ ÇáÑÇßÈíä Åáì ÎÇÕíÉ ÎÇÏãíÉ `searchableRiders` (äİÓ äãØ `searchableStatuses`/`searchableMembers` İí ÇáØáÈÇÊ). İí `TrackingGridConcern::loadTrackingStats()` — íõÍÓÈ ÇáŞÇÆãÉ ÇáßÇãáÉ áßá ÇáÑÇßÈíä ãÚ ÏãÌ ÇáÚÏÇÏÇÊ ãä `riderRiders` keyed by id¡ ÍÊì áæ áã ÊæÌÏ ÔÍäÇÊ (ÇáÚÏÇÏ = 0). (`TrackingGridConcern.php:209-250`). İí `tracking/index.blade.php` — ÃõÖíİ `'searchableRiders' => []` Åáì `state()`. (`tracking/index.blade.php:50-53`). İí ÇáÈæÇÈÊíä — ÇÓÊõÈÏá `@json(collect(...)...)` ÇáãÊÚÏÏ ÇáÃÓØÑ ÈÜ `@json($this->searchableRiders)` ÈÓíØ æãÍÕøä. (`tracking-filter-portal.blade.php:113`¡ `tracking-filter-bar-portal.blade.php:257`)

**ÇáÊÍŞŞ:** `php -l` + `view:clear`+`view:cache` — Compiled views ÊÄßÏ Ãä `json_encode($this->searchableRiders, 15, 512)` íÍÊİÙ ÈÇáÃÚáÇã. ÇáÇÎÊÈÇÑÇÊ ÇáãÓÊåÏİÉ: TrackingGridBatchTest 18 + TrackingSearchFilterTest 20 = **38 äÌÇÍğÇ (326 ÔåÇÏÉ)** + BladeInteractivityPolicy 2 + `npm run build` (246.7 kB panel).

---

## ÅßãÇá İáÇÊÑ ÇáãäÊÌÇÊ + ÇáãÈÇáÛ + ÚÏøÇÏ ÇáİáÇÊÑ İí ÕİÍÉ ÇáÊÊÈÚ — 2026-09-15

**ÇáÓíÇŞ (ÇÓÊßãÇá ÌæáÉ "ÅÕáÇÍ ÃÎØÇÁ ŞæÇÆã ÇáÇÎÊíÇÑ"):** ßÇäÊ ÕİÍÉ ÇáÊÊÈÚ ÊİÊŞÏ İáÊÑ ÇáãäÊÌÇÊ İí `availableFilterGroups`¡ æÑŞÇŞÉ ÇáãÈÇáÛ¡ æÏÚã `hasActiveFilters` ááãäÊÌÇÊ/ÇáãÈÇáÛ — áã íßÊãá ÏãÌ ÇáãäÊÌÇÊ ãä ãÍÑß ÇáİÑÒ ÇáãæÍøÏ.

**ÇáÅÕáÇÍÇÊ ÇáãäİøĞÉ (ÏÇÎá ÇáÈæÇÈÊíä ÈÏæä ÍÔÑ İí index):**
- **`availableFilterGroups()`** (`TrackingFilterConcern.php`): ÃõÖíİ `'products' => null` Åáì `$map` ? ÇáãäÊÌÇÊ ãÊÇÍÉ ÏÇÆãğÇ İí ÈæÇÈÉ ÇáÔÑíØ.
- **`activeFilterCount()`** (`TrackingFilterConcern.php`): ÃõÖíİ `case 'products'` (íÍÓÈ İáÇÊÑ `filters['products']`) + `case 'amount'` ßÇä ãæÌæÏğÇ.
- **`$hasActiveFilters`** (`tracking-toolbar.blade.php`): ÃõÖíİ ÔÑØ `count($this->filters['products']) > 0` + `filled(amount_min) || filled(amount_max)`.
- **ÑŞÇŞÊÇ ÇáãäÊÌÇÊ æÇáãÈÇáÛ** İí ÇáÔÑíØ ŞÈá ÒÑ Clear: ÑŞÇŞÉ ãäÊÌÇÊ ÊÚÑÖ ÃÓãÇÁ ÇáãäÊÌÇÊ (lookup İí `allProducts`) æÑõŞÇŞÉ ãÈÇáÛ ÊÚÑÖ `min—max`.
- **ŞÓã ãäÊÌÇÊ ßÇãá ŞÇÈá ááÈÍË** İí `tracking-filter-bar-portal.blade.php` (ÈÚÏ ÇáãÈÇáÛ¡ ŞÈá ÇáãÏíäÉ): `edzSearchableList` + `@json($this->allProducts)` ãÚ `toggleProductFilter()` ãÊÚÏÏ ÇáÊÍÏíÏ.
- **`$groupLabels`** İí `tracking-filter-bar-portal.blade.php`: ÃõÖíİ `'products' => __('merchant_panel.products')`.

**ÇáÇÎÊÈÇÑÇÊ ÇáÌÏíÏÉ:** 5 ÇÎÊÈÇÑÇÊ İí `TrackingSearchFilterTest` + ÏÇáÊÇ ãÓÇÚÏÉ `tsfCreateProducts`/`tsfAttachProduct` (ãÚ ÅäÔÇÁ ProductVariant¡ ÅÚÇÏÉ ÇÓÊÎÏÇã ÇáİÇÑíÇäÊ¡ æÑÈØ `store_id`). ÇáÇÎÊÈÇÑÇä ÇááĞÇä ÇİÊÑÖÇ `activeFilterCount` ÎÇÕíÉ ÚãæãíÉ äõŞáÇ Åáì `instance()->activeFilterCount()` áÃäåÇ **ÏÇáÉ** (áíÓÊ ÎÇÕíÉ). ÇÎÊÈÇÑ ÇáÔÈßÉ íËÈÊ ÏáÇáÉ **OR** ááãäÊÌÇÊ (`whereIn`): ÇÎÊíÇÑ p1 ? ØáÈÇä¡ +p2 ? 3¡ Ëã p1 İŞØ ? 2.

**ÇáÊÍŞŞ:** `php -l` + `view:clear`+`view:cache` (ÇáÜ compiled íÄßÏ ŞÓã open === 'products' İí ÈæÇÈÉ ÇáÔÑíØ) + `TrackingSearchFilterTest` **33 äÌÇÍğÇ (148 ÔåÇÏÉ)** + `BladeInteractivityPolicy` 2 + **ÓæíÊ ÇáÊÌÇÑ ßÇãáğÇ 531 äÌÇÍğÇ (2276 ÔåÇÏÉ)** + `npm run build`.

**Ïíä íõÓÌóøá:** `tsfAttachProduct` íäÔÆ ProductVariant æÇÍÏğÇ áßá ãäÊÌ (íõÚÇÏ ÇÓÊÎÏÇãå ÚÈÑ ÇáØáÈÇÊ) áÊİÇÏí İÎ UNIQUE `product_variants.store_id+sku` — ÅĞÇ ÊÍæøá ÇáãäÊÌ áÇÍŞğÇ áäÙÇã variants ãÊÚÏÏÉ¡ ÓíÍÊÇÌ ÇáãÓÇÚÏ ÅÚÇÏÉ äÙÑ.

---

## ÇÕØáÇÍ ÃÎØÇÁ ÊÏŞíŞ ÇáİáÇÊÑ/ÇáÈØÇŞÇÊ İí ÕİÍÉ ÇáÊÊÈÚ — 2026-09-15

**ÇáÓíÇŞ:** ÊÏŞíŞ Ííø Úáì ÇáßæãíÊ `d9e36ea` ÑİÚ 9 ãÔÇßá (3 ÍÑÌÉ/4 ãÊæÓØÉ/2 ÕíÇäÉ). ÚäÏ ÇáİÍÕ Úáì **ÇáßæÏ ÇáÍÇáí** ÊÈíøä Ãä ÃÛáÈåÇ ÃõÕáÍ İí ÌæáÇÊ ÓÇÈŞÉº ÈŞí 3 ãÔÇßá İÚáíÉ ÃõÕáÍÊ ÇáÂä.

**ÊŞÑíÑ ÇáÊÍŞŞ (ŞÏíã vs ÍÇáí):**
- **ÃõÕáÍÊ ÓÇÈŞğÇ (ÇÏÚÇÁÇÊ ãÊŞÇÏãÉ):** #2 ÒÑ Filters ÙÇåÑ ÏÇÆãğÇ (tracking-toolbar.blade.php:58 ÈáÇ `@if(!empty(...))`)¡ #3 ßá ÇáŞæÇÆã İí ÇáÈæÑÊíä ÊÓÊÎÏã `edzSearchableList` ãÚ ÍŞá ÈÍË¡ #4 ÇáãÇáß ãÓÊËäì ãä `allMembers` (index.blade.php:240)¡ #1 ÌÒÆíğÇ `city`/`amount` ãæÌæÏÇä İí `$hasActiveFilters` ãÚ ÔÑÇÆÍ.
- **ÇáÈÇŞí ÍŞíŞí æÃõÕáÍ ÇáÂä:**
  1. **ÔÑíÍÉ ÇáÑÇßÈ ÇáäÇŞÕÉ:** ÇáÑÇßÈ ßÇä İí `$hasActiveFilters` áßä ÈáÇ ÔÑíÍÉ ÚÑÖ ? ÅĞÇ İõáÊöÑ ÈÑÇßÈ İŞØ íÙåÑ ÇáÔÑíØ İÇÑÛğÇ. ÃõÖíİÊ ÔÑíÍÉ ÑÇßÈ (ÊÓãíÉ + lookup İí `searchableRiders` + ÒÑ ãÓÍ) İí `tracking-toolbar.blade.php` ŞÈá ÔÑíÍÉ `assigned_to`.
  2. **`allProviders` íÓÊËäí ÇáÔÑßÇÊ ÇáãÚØøáÉ:** `where('is_active', true)` íãäÚ İáÊÑÉ ÇáØáÈíÇÊ ÇáÊÇÑíÎíÉ áÔÑßÉ ÃõåãáÊ. ÃÕÈÍ `is_active OR id ? ØáÈíÇÊ ÇáãÊÌÑ` (äİÓ äãØ ÇáÇÔÊŞÇŞ ãä ÇáØáÈíÇÊ áÜ allCities/allStates) İí `tracking/index.blade.php`.
  3. **ÈØÇŞÉ ÇáãæÈÇíá ÊÚÑÖ «ÔÑßÉ ÇáÔÍä» İí ÊÇÈ ÇáÑÇßÈ:** `tracking-mobile-card.blade.php` ßÇäÊ ÊÚÑÖ `$s['provider']` ÏÇÆãğÇ (æÊÙåÑ `• —`) ÈíäãÇ ÓØÍ ÇáãßÊÈ íÎİí ÚãæÏ provider İí ÊÇÈ ÇáÑÇßÈ. ÃÕÈÍ `@if(trackingTab==='rider') ÑÇßÈ @elseif(provider ? visibleColumns) ÔÑßÉ @endif` — ãØÇÈŞÉ ááÓáæß ÇáãßÊÈí.
- **íÍÊÇÌ ŞÑÇÑ ÇáãÓÊÎÏã:** #7 İáÊÑ ÇáãáÇÍÙÇÊ (äÕ ÍÑ — ÇáÃŞá İÇÆÏÉ). **íÍÊÇÌ ÊÍŞŞğÇ ÈÕÑíğÇ:** #8 ãæÖÚ ÇáŞæÇÆã ÇáãäÈËŞÉ RTL (dropdown-position.js uses ßÊÇÈÉ `left` İíÒíÇÆíÉ¡ ãÚ ŞÕø ÏÇÎá ÇáÔÇÔÉ). #9 Ïíä ÍÌã ÇáãáİÇÊ ãÓÌøá ãÓÈŞğÇ.

**ŞÑÇÑÇÊ ÇáãÓÊÎÏã (2026-09-15):**
- **#7 İáÊÑ ÇáãáÇÍÙÇÊ:** ãÑİæÖ — áä íõÖÇİ İáÊÑ ááãáÇÍÙÇÊ.
- **#8 ãæÖÚ ÇáŞæÇÆã RTL:** ÇáãÓÊÎÏã ÓíÊÍŞŞ ÈÕÑíğÇ ÚäÏ 375/768 Ëã íŞÑÑ.
- **#9 ÍÌã `tracking-filter-bar-portal.blade.php`:** ãÄÌá — ÓíõÏÑÓ áÇÍŞğÇ áÑİÇßÊæÑ ÇÍÊÑÇİí (Êİßíß ÍÓÈ ãÌãæÚÉ ÇáİáÊÑ).

**ÇáÊÍŞŞ:** `view:clear`+`view:cache` — compiled íËÈÊ ÔÑíÍÉ ÇáÑÇßÈ + İÑÚ `elseif(in_array('provider', ...))` İí ÇáÈØÇŞÉ. ÇáÇÎÊÈÇÑÇÊ: TrackingSearchFilterTest **33** + TrackingGridBatch/TrackingProductsColumn/TrackingStatusHistory **19** + BladeInteractivity **2** = **54 äÌÇÍğÇ.**

---

## ÅÕáÇÍ ÌĞÑí: ŞÇÆãÉ ÇáæáÇíÉ ÇáãßÓæÑÉ İí ÇáİáÇÊÑ + ÑÃÓ ÇáÚãæÏ — 2026-09-15

**ÇáÚÑÖ:** ŞÇÆãÉ ÇáæáÇíÉ İí ÈæÇÈÉ ÇáİáÇÊÑ (ÔÑíØ ÇáÃÏæÇÊ) æÑÃÓ ÇáÚãæÏ ÊÙåÑ ãÔæåÉ: ÇáÇŞÊÈÇÓÇÊ ÇáãÒÏæÌÉ ááÜ JSON ÊßÓÑ ÓãÉ `data-items` İíİÔá `JSON.parse` æÊÑÊíÈ ÇáÃÓãÇÁ íÈÏæ ÎÇØÆğÇ.

**ÇáÓÈÈ ÇáÌĞÑí (áíÓ ÊÑŞíÚğÇ):** `data-items="@json($this->allStates)"` ÊÖÚ JSON ÎÇãğÇ İí ÓãÉ **ãÒÏæÌÉ ÇáÇŞÊÈÇÓ**. `@json` íÊÑÌã Åáì `json_encode(..., 15, 512)` ÇáĞí íåÑÈ `"` **ÏÇÎá ÇáŞíã** İŞØ (`\u0022`) — áßä ÇáÇŞÊÈÇÓÇÊ **ÇáÈäíæíÉ** ááÜ JSON (Íæá ÇáãİÇÊíÍ: `[{"id":...` ) ÊÈŞì `"` ÍÑİíÉ İÊõÛáŞ ÇáÓãÉ ãÈßÑğÇ æíİßß ÇáãÊÕİÍ ÇáÈäíÉ. ÈÏíá Úä İÑÖíÉ «ÈíÇäÇÊ ŞĞÑÉ ÈåÇ `"`» — ÇáŞÇÚÏÉ äÙíİÉ (58 æáÇíÉ ÈáÇ Ãí ÇŞÊÈÇÓ) æÇáãáİÇÊ ÇáãÊÑÌãÉ ßÇäÊ ÓáíãÉ `15,512`.

**ÇáÍá ÇáÌĞÑí ÇáãØÈŞ:** ÊÍæíá ßá `data-items`/`data-active` İí ãáİí ÊÊÈÚ-ÇáãÓÇÑÇÊ Åáì ÓãÉ **ãİÑÏÉ ÇáÇŞÊÈÇÓ** `data-items='@json(...)'` — İÊÕÈÍ `"` ÇáÈäíæíÉ ÂãäÉ ÏÇÎá ÇáÓãÉ¡ ÈíäãÇ Ãí `'` İí ÇáŞíã (ãËá `M'Sila`) ãåÑÈÉ ÊáŞÇÆíğÇ ÈÜ `\u0027` (áÃä 15 íÊÖãä JSON_HEX_APOS). ÊÍŞøŞ ÊÌÑíÈíğÇ: `@json` áÇ íõäÊÌ Ãí `'` ÎÇã — ÃãÇä ÊÇã ááŞíã.

**ÇáãáİÇÊ (7 ãæÇÖÚ áßá ãáİ):**
- `tracking-filter-portal.blade.php` — products/provider/rider/assigned_to/confirmed_by/state/city
- `tracking-filter-bar-portal.blade.php` — state/provider/products/city/rider/assigned_to/confirmed_by

**ÇáÊÍŞŞ:** `view:clear`+`view:cache` — compiled íËÈÊ `data-items='<?php echo json_encode(..., 15, 512) ?>'` İí ßá ÇáãæÇÖÚ ÇáÜ 14. `TrackingSearchFilterTest` **33 äÌÇÍğÇ (148 ÔåÇÏÉ)**. áÇ ÊÈŞì Ãí `data-items="@json` İí partials/tracking.

**ŞÑÇÑ ááÌæáÉ ÇáŞÇÏãÉ (ãÚáŞ):** äİÓ ÇáÌĞÑ ãæÌæÏ ÃíÖğÇ İí `orders/partials/filter-portal.blade.php` + `orders/partials/orders-filter-bar-portal.blade.php` (~13 ãæÖÚğÇ). ÇáŞÑÇÑ: ÇáÊæÍíÏ Úáì ÇáäØÇŞíä Ãã ÇáÇßÊİÇÁ ÈÇáÊÊÈÚ. **áã íõØÈóøŞ ÈÚÏ — ÈÇäÊÙÇÑ ŞÑÇÑ ÇáãÓÊÎÏã.**

---

## ÌáÈ ßá ÇáæáÇíÇÊ æÇáÈáÏíÇÊ + ÓßÑæá ÚãæÏí İí İáÇÊÑ ÇáÊÊÈÚ — 2026-09-15

**ÇáãÔßáÉ:** ŞæÇÆã ÇáæáÇíÉ æÇáÈáÏíÉ ßÇäÊ ÊõÌáÈ ãä **ÇáØáÈíÇÊ İŞØ** (`Order::whereIn('state_id'...)` / `whereIn('city_id'...)`) — Ãí ÊõÚÑÖ İŞØ ÇáæáÇíÇÊ/ÇáÈáÏíøÇÊ ÇáÊí ÙåÑÊ İÚáíğÇ İí ØáÈíÇÊ ÇáãÊÌÑ¡ æáíÓÊ ßá ÇáŞÇÆãÉ ÇáæØäíÉ (58 æáÇíÉ / 1540 ÈáÏíÉ). **ŞÑÇÑ ÇáãÓÊÎÏã:** ÌáÈ **Çáßá** æÚÑÖå ãÚ ÈÍË + ÓßÑæá ÚãæÏí.

**ãÇ ØõÈøŞ (`tracking/index.blade.php`):**
- `allStates` ? `State::orderBy('name')` (ßá ÇáæáÇíÇÊ — 58) ãÚ `id` ÓáÓáÉ.
- `allCities` ? `City::orderBy('name')` (ßá ÇáÈáÏíÇÊ — 1540) ãÚ `id` ÓáÓáÉ.
- `Order` import ãÇ ÒÇá ãÓÊÎÏãğÇ (ÈæÇÈÉ ÇáãäÊÌÇÊ/ÇáÔÑßÇÊ) — áã íõßÓÑ ÔíÁ.

**ÓßÑæá ÚãæÏí (íÍÇİÙ Úáì ÇáÈÍË ËÇÈÊğÇ):** ßá ŞÇÆãÉ `edzSearchableList` İí ÇáÈæÑÊíä ÃÕÈÍÊ ÊÍæí áİøğÇ ÏÇÎáíğÇ `<div class="max-h-[40vh] sm:max-h-60 overflow-y-auto edz-scroll">` Íæá ÇáÜ `template x-for` — Úáì ÇáãæÈÇíá 40vh¡ æÚáì ÇáÏíÓßÊæÈ `sm:max-h-60` (15rem). ÃŞÓÇã state/city ÊÍÏíÏğÇ ÑõİÚ ÓŞİ ÍÇæíÉ ÇáÈæÑÊãä Åáì `sm:max-h-[70vh]` áÃäåÇ ÇáÂä ÊÖã 1540 ÈáÏíÉ.

**ÇáãáİÇÊ (14 ŞÇÆãÉ ÃÕáÍÊ):**
- `tracking-filter-portal.blade.php` — 7 ÃŞÓÇã (products/provider/rider/assigned_to/confirmed_by/state/city) + `sm:max-h-[70vh]` áŞÓãí state/city.
- `tracking-filter-bar-portal.blade.php` — 7 ÃŞÓÇã + `sm:max-h-[70vh]` ááÍÇæíÉ (ßá ÇáÃŞÓÇã).

**ÇáÊÍŞŞ:** `view:clear`+`view:cache` — compiled íËÈÊ ÇáÃáİÇİ æÇáÓŞæİ ÇáÌÏíÏÉ. `npm run build` — ßáÇÓÇÊ `max-h-[40vh]`/`sm:max-h-60`/`sm:max-h-[70vh]`/`overflow-y-auto` ãæÌæÏÉ İí CSS ÇáäåÇÆí. ÇáÇÎÊÈÇÑÇÊ: TrackingSearchFilterTest **33 äÌÇÍğÇ (148 ÔåÇÏÉ)** + OrderCityScopeProviderTest **8 äÌÇÍğÇ (13 ÔåÇÏÉ)** (áã íÊÃËÑ — ÓßæÈå ÇáÎÇÕ ááØáÈÇÊ áÇ íÓÊÎÏã ŞæÇÆã ÇáÊÊÈÚ).

**ãáÇÍÙÉ ÃÏÇÁ:** 1540 ÈáÏíÉ ÊõÍãóøá ßÜ JSON ÏÇÎá ÓãÉ `data-items` ãÑÊíä (ÈæÇÈÊÇä) — ~40KB. ÇáÈÍË ßÇãáñ client-side ÚÈÑ `filtered`¡ æÇáÓßÑæá íãäÚ ÈäÇÁ 1540 ÒÑğÇ ãÑÆíğÇ İíõÎİİ ÖÛØ ÇáÜ DOM. ÇÍÊíÇØí Åä ÇÕØÏãäÇ ÈÇáÃÏÇÁ áÇÍŞğÇ: ÈæÇÈÉ lazy-load ÚÈÑ Livewire (ÊÌÑÈÉ DB Úáì `filters.state`) — **ÛíÑ ãØÈóøŞ ÇáÂä**.

**ÇáÊæÍíÏ ãÚ orders:** ãÄÌøá ÈŞÑÇÑ ÇáãÓÊÎÏã ÍÊì ÇáÍÕæá Úáì äÊíÌÉ ÇáÊÊÈÚ ÇáÇÍÊÑÇİíÉ.

---

## ÏİÚÉ ÇáİáÇÊÑ æÇáãÕØáÍÇÊ æÇáãÙåÑ — 2026-09-15 (2)

**ŞÑÇÑ ÇáãÓÊÎÏã:** "İí ßËíÑ ãä ÇáÚãáíÇÊ ÓÃŞæá ÇáäÊíÌÉ ÇáäåÇÆíÉ İŞØ" — ÊäİíĞ ãÈÇÔÑ ááÏİÚÉ ÇáËÇäíÉ Ïæä ÇäÊÙÇÑ.

**1) ÅÒÇáÉ İáÊÑ ÅÌãÇáí ÇáØáÈíÉ ãä ÑÃÓ ÚãæÏ ÇáãÌãæÚ:**
- ÍõĞİ `'total' => 'amount'` ãä `$headerFilterKeys` İí `tracking-table-header.blade.php` ? ÚãæÏ ÇáãÌãæÚ áã íÚÏ íÚÑÖ ÃíŞæäÉ İáÊÑ.
- ÍõĞİ ŞÓã Amount ãä ÈæÇÈÉ ÑÃÓ ÇáÚãæÏ (`tracking-filter-portal.blade.php`) áÃäå áã íÚÏ ŞÇÈáğÇ ááİÊÍ ãä ÇáåíÏÑ.
- İáÊÑ ÇáãÌãæÚ ãÇ ÒÇá ãÊÇÍğÇ İí ÈæÇÈÉ ÔÑíØ ÇáÃÏæÇÊ (ŞÇÆãÉ Filters) — ŞÑÇÑ ÈŞÇÆå åäÇß áÃäå ãÓÊŞá Úä ÑÃÓ ÇáÚãæÏ.

**2) ÊÍÓíä ÍÌã ÇáŞÇÆãÉ ÇáãäÓÏáÉ Úáì ÇáÔÇÔÇÊ ÇáßÈíÑÉ (max-height: 350px):**
- ßá áİøÇÊ ÇáÓßÑæá ÇáÏÇÎáíÉ ÇáÜ 14: `sm:max-h-60` (240px) ? `sm:max-h-[350px]` İí ÇáÈæÑÊíä.
- ÍÇæíÉ ÇáåíÏÑ: `sm:max-h-64` ? `sm:max-h-[350px]` ááÃŞÓÇã ÛíÑ state/city (ÇáÊí ÊÈŞì `sm:max-h-[70vh]`).

**3) ÊÑÊíÈ ÇáÃÚãÏÉ — ÚãæÏ ÇáÅÌÑÇÁÇÊ ÏÇÆãğÇ ÇáÃÎíÑ ÇİÊÑÇÖíğÇ:**
- `TrackColumnConcern::toggleDraftColumn()`: ÚäÏ ÊİÚíá ÚãæÏ ÌÏíÏ íõÏÑÌ **ŞÈá** `actions` ÈÏá ÅáÍÇŞå İí äåÇíÉ ÇáŞÇÆãÉ — İíÈŞì actions ÇáÃÎíÑ ÍÊì íÚÏøöá ÇáãÓÊÎÏã ãßÇäå íÏæíğÇ (ÃÓåã/ÓÍÈ İí áæÍÉ ÇáÅÚÏÇÏÇÊ).

**4) İáÇÊÑ ÌÏíÏÉ İí ÇáÊÊÈÚ:**
- `can_open` (ÇáÓãÇÍ ÈİÊÍ ÇáØáÈíÉ) — ËáÇËí All/N/A: `orders.can_open`.
- `send_from_carrier_warehouse` (íõÑÓá ãä ãÎÒä ÇáäÇŞá) — ËáÇËí: `orders.send_from_carrier_warehouse`.
- `shipment_type` (ÇáÊæÕíá/ÇáÇÓÊÈÏÇá/ÇáÊŞÇØ ÇáØÑÏ) — İÑÏí: `orders.shipment_type`.
- ÇáãÓ: `filters` state (index.blade.php + clearFilters)¡ `availableFilterGroups` (ááßá — áÇ ÊŞííÏ ÈÊÈæíÈ)¡ `activeFilterCount` (ÍÇáÉ `!== null` ááËÇáíÇÊ áÃä `filled(false)` ÎÇØÆÉ)¡ `setFilter` (ÊÍæíá ŞíãÉ ãäØŞíÉ ááËÇáíÇÊ + ÇÚÊãÇÏ `delivery|exchange|pickup` İŞØ)¡ WHERE İí `TrackingGridConcern::baseTrackingQuery`¡ ÈæÇÈÉ ÔÑíØ ÇáÃÏæÇÊ (`$groupLabels` + 3 ÃŞÓÇã).
- ÇáÊÑÌãÉ: `merchant_panel.can_open` (ßÇä äÇŞÕğÇ ÑÛã ÇÓÊÎÏÇãå İí orders) + `refund_request` + ßáãÇÊ ãİÊÇÍíÉ ááŞÏÑÇÊ (ÃÏäÇå) — ÃõÖíİÊ ááÛÇÊ ÇáÃÑÈÚ.

**5) ÊÕÍíÍ ÇáãÕØáÍÇÊ (ar/fr/en/es):**
- `merchant_panel.delivery`: ar 'ÇáÊÓáíã' ? 'ÇáÊæÕíá' (fr ÈÇáİÚá Livraison).
- `merchant_panel.exchange_label`: ar 'ÊÈÏíá' ? 'ÇÓÊÈÏÇá ÇáØÑÏ'.
- `merchant_panel.pickup_label`: ar 'ÇÓÊáÇã' ? 'ÇáÊŞÇØ ÇáØÑÏ'.
- ãİÇÊíÍ ÌÏíÏÉ İí `merchant_panel.php` ááÛÇÊ ÇáÃÑÈÚ: `capability_free_shipping_mode` (ÅÑÓÇá ãÌÇäí ãÊæİÑ İí API)¡ `capability_express_economic` (ÃäæÇÚ ÇáÊæÕíá ÇáÇŞÊÕÇÏí æÇáÚÇÏí)¡ `capability_api_notes` (ãáÇÍÙÇÊ ÇáÊæÕíá ÚÈÑ API)¡ `capability_order_delete` (ÍĞİ ÇáØáÈíÇÊ ŞÈá ÇáİÇáíÏíÔä ÚÈÑ API)¡ `capability_price_sync` (ãÒÇãäÉ ÃÓÚÇÑ ÇáÊæÕíá).

**ÇáÊÍŞŞ:** `php artisan view:clear`+`view:cache` (PHP 8.3.28) — compiled íÊÖãä `setFilter('can_open'/'send_from_carrier_warehouse'/'shipment_type')` İí ÈæÇÈÉ ÇáÊÊÈÚ. `npm run build` — `max-h-\[350px\]` İí CSS ÇáäåÇÆí. ÇáÇÎÊÈÇÑÇÊ: `vendor/bin/pest --filter=TrackingSearchFilter` **33 äÌÇÍğÇ (148 ÔåÇÏÉ)** + `--filter=OrderCityScopeProvider` **8 äÌÇÍğÇ (13 ÔåÇÏÉ)**.

**ãáÇÍÙÉ:** `sm:max-h-60` áã ÊÚÏ ãÓÊÎÏãÉ İí Ãí ãä ÇáÈæÑÊíä (ßá ÇáãæÇÖÚ ÃÕÈÍÊ `sm:max-h-[350px]`).

---

## ÊæÍíÏ ÕİÍÇÊ orders ãÚ tracking (ÇáŞæÇÆã 350px) + ÊÏŞíŞ ÇáÃÏÇÁ æÇáÃÎØÇÁ — 2026-09-16 (3)

**ŞÑÇÑ ÇáãÓÊÎÏã:** "ÇÈÏÃ ÇáÊæÍíÏ ÍíË ÇÑÊİÇÚ ŞæÇÆã ÇáİáÇÊÑ ãÇßÓ 350px¡ æÇİÍÕ ãÔÇßá ÇáÃÏÇÁ æÇáÃÎØÇÁ æŞã ÈÇÕáÇÍåÇ" — ÊæÍíÏ ÈæÇÈÇÊ İáÇÊÑ orders Úáì äİÓ ÇáäãØ ÇáãÚãóøÏ İí tracking.

**1) ÊæÍíÏ ÈæÇÈÉ ÑÃÓ ÇáÚãæÏ `orders/partials/filter-portal.blade.php`:**
- ÅÕáÇÍ ÎØÃ JSON: ßá `data-items="@json(...)"`/`data-active="@json(...)"` ÇáãÒÏæÌÉ ? ãİÑÏÉ `data-items='@json(...)'` (7 ŞæÇÆã: wilaya/status/assigned_to/shipping_provider/stopdesk_point/city/confirmed_by). ßÇäÊ ÇáŞíãÉ ÇáãÛáİÉ ÈãÒÏæÌÉ ÊßÓÑ JSON ÚäÏ æÌæÏ ÚáÇãÇÊ ÏÇÎá ÇáãÍÊæì (ãÔßáÉ ÍŞíŞíÉ İí ÇáÚãáíÇÊ).
- áİø ÇáŞæÇÆã ÇáÓÈÚ ÈÇáÍÇæíÉ ÇáÏÇÎáíÉ `max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll` (äİÓ äãØ tracking).
- ÊŞÓíã ÓŞİ ÇáÍÇæíÉ: `sm:max-h-[70vh]` áÜ wilaya/city (ŞæÇÆã ÖÎãÉ)º `sm:max-h-[350px]` áÈŞíÉ ÃŞÓÇã ÇáŞÇÆãÉº `sm:w-*` ÈŞíÊ ßãÇ åí.

**2) ÊæÍíÏ ÈæÇÈÉ ÔÑíØ ÇáÃÏæÇÊ `orders/partials/orders-filter-bar-portal.blade.php`:**
- ÅÕáÇÍ JSON ÇáãÒÏæÌ (7 ŞæÇÆã) ? ãİÑÏÉ (äİÓ ÇáİÆÇÊ).
- áİø ÇáŞæÇÆã ÇáÓÈÚ ÈÇáÍÇæíÉ `max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll`.
- ÍÇæíÉ ÇáÈæÇÈÉ: ÃõÖíİ `sm:max-h-[70vh]` (ßÇäÊ ÈáÇ ÓŞİ Úáì ÇáÔÇÔÇÊ ÇáßÈíÑÉ — `max-h-[75vh]` ÊÈŞì ááãæÈÇíá).

**3) ÇáãŞíÇÓ Çáßáí ÈÚÏ ÇáÊæÍíÏ:**
- 28 ÍÇæíÉ ÓßÑæá ÏÇÎáíÉ ãæÍøÏÉ (14 İí tracking + 14 İí orders) = `max-h-[40vh] sm:max-h-[350px] overflow-y-auto edz-scroll`.
- ÕİÑ `data-items="@json` ãÒÏæÌÉ İí ßá merchant (ÈÍË ÔÇãá: 0)¡ æÕİÑ `sm:max-h-60`/`sm:max-h-64` İí Ãí ãä ÇáÈæÑÇÊ ÇáÃÑÈÚ.

**4) ÊÏŞíŞ ÇáÃÏÇÁ æÇáÃÎØÇÁ (ãÇ İõÍÕ æãÇ áã íõÛíøÑ):**
- `edzSearchableList` (panel.js): íŞÑÃ `dataset.items`/`dataset.active` ãä ÌĞÑ Alpine — ãÊæÇİŞ ÊãÇãğÇ ãÚ ÇáÓãÉ ÇáãİÑÏÉº `filtered` íÚãá filter ãÍáí İŞØ ÚäÏ ÇáßÊÇÈÉ (áÇ ÔÈßÉ)º `parseItems` íõÚÇÏ Úáì İÊÍ ÇáÈæÇÈÉ ÚÈÑ `edz-filter-open`/`edz-toolbar-filter-open` + ÚäÏ ßá ÅÚÇÏÉ render (ãŞÕæÏ áíÚßÓ ÃÍÏË ÍÇáÉ ÎÇÏã).
- ÍÌã ÇáÈíÇäÇÊ: ŞÇÆãÉ ÇáãÏä ? 1540 ÈáÏíÉ ÊõÔÍä İí `data-items` (?40KB) ÚäÏ ÙåæÑ ÚãæÏ city — ÊÕãíã ŞÇÆã İí tracking ÃíÖğÇº **ãÄÌøá** (áÇ ÊáİíŞ ÇáÂä): lazy-load ÚÈÑ Livewire ÚäÏ ÇáİÊÍ ÈÏá ÇáÔÍä ãÚ ßá render.
- `filtered` íãÑÑ ßÇãá ÇáŞÇÆãÉ ÚäÏ ÇáßÊÇÈÉ (?1540 ÚäÕÑğÇ) — ãŞÈæá¡ áÇ ÍÇÌÉ ááİÖÇÁ ÇáÇİÊÑÇÖí ÇáÂä.
- ŞÇÆãÊÇ ÍÇáÉ ãäÓÏáÊÇä İí orders ÎÇÑÌ ÇáäØÇŞ (áíÓÊ İáÇÊÑ): `orders/index` + `orders-table-cell` (ŞæÇÆã ÊÛííÑ ÇáÍÇáÉ áßá Õİ¡ `sm:max-h-64`) — ÊõÑß ßãÇ åãÇº ÈäÏ ÊæÍíÏ ÊÇáò ãÍÊãá.

**ÇáÊÍŞŞ:** `php artisan view:clear`+`view:cache` (PHP 8.3.28) — compile äÙíİ¡ æÈÇáßÇÔ 7 ãáİÇÊ ÈÕíÛÉ `data-items='` æÕİÑ ÈÕíÛÉ ãÒÏæÌÉ. ÇáÇÎÊÈÇÑÇÊ: `vendor/bin/pest` (TrackingSearchFilterTest + OrderCityScopeProviderTest) **41 äÌÇÍğÇ (161 ÔåÇÏÉ)** — áÇ ÊÑÇÌÚÇÊ.

**ãÄÌá (ÊæÍíÏ ÊÇáò ãÍÊãá):** ŞæÇÆã ÍÇáÉ ÇáÃæÇãÑ `sm:max-h-64` İí `orders/index` + `orders-table-cell`º lazy-load áŞæÇÆã ÇáİáÇÊÑ ÇáÖÎãÉ ÚÈÑ Livewire.

---

## ÅÕáÇÍ ÎØÃ "Undefined array key product_id" İí orders — 2026-09-16 (4)

**ÇáÚÑÖ:** ÚäÏ ÇáİáÊÑÉ ÈäæÚ ÇáÊæÕíá (shipment_type/delivery_type) ÈÚÏ ãÓÍ ÇáİáÇÊÑ¡ ÙåÑ `Undefined array key "product_id"` ÏÇÎá `orders/index.blade.php` (İí ÇáÌÒÁ ÇáãÊÑÌã ãä `loadOrders`).

**ÇáÓÈÈ ÇáÌĞÑí:** `mount` (state) íõåíøÆ `filters` ãÚ `'product_id' => null`¡ áßä `$clearFilters` ÍĞİ ÇáãİÊÇÍ `product_id` ãä ÇáŞÇÆãÉ (ßÇä İíå `'product' => ''` İŞØ). ÈÚÏ ãÓÍ ÇáİáÇÊÑ íÕÈÍ `filters` ÈáÇ `product_id`¡ İÃí İáÊÑ áÇÍŞ (ãËá äæÚ ÇáÊæÕíá) ? `$setFilter` ? `loadOrders` ? ÓØÑ ÈäÇÁ ÇáÇÓÊÚáÇã `if (!empty($f['product_id']))` íŞÑÃ ãİÊÇÍğÇ ÛíÑ ãæÌæÏ ? ÎØÃ PHP 8. ÇáãİÊÇÍÇä `source` æ`send_from_carrier_warehouse` ãæÌæÏÇä İí clearFilters (áåĞÇ áã íÙåÑ ÇáÎØÃ ãÚåãÇ ãä ŞÈá).

**ÇáÅÕáÇÍ (`resources/views/livewire/merchant/orders/index.blade.php`):**
- ÃõÖíİ `'product_id' => null` Åáì `$clearFilters` ÈÚÏ `'shipping_provider'` — ÊØÇÈŞ ÊÇã Èíä ãİÊÇÍí mount (26) æclearFilters (26 ÇáÂä).
- ÊÍÕíä ÏİÇÚí: ÔÑØ ÇáÇÓÊÚáÇã ÃÕÈÍ `!empty($f['product_id'] ?? null)`.
- ÊÍŞøŞ ÔÇãá: ßá ãİÇÊíÍ ÇáÜ 24 ÇáãŞÑæÁÉ İí `loadOrders` ãæÌæÏÉ İí clearFilters (áÇ İÌæÇÊ ÃÎÑì).

**ÇáÊÍŞŞ:** `view:clear`+`view:cache` äÇÌÍ (PHP 8.3.28). `OrderCityScopeProviderTest` **8 äÌÇÍğÇ (13 ÔåÇÏÉ)** — áÇ ÊÑÇÌÚÇÊ.

---

## ÅÕáÇÍ ÊÊÇÈÚ İáÊÑÉ ÇáæáÇíÉ ? ÇáÈáÏíÉ İí orders — 2026-09-16 (5)

**ÇáÚÑÖ:** ãÔÇßá ÚäÏ ÇáİáÊÑÉ ÈÇáæáÇíÇÊ æÇáÈáÏíÇÊ (ŞÇÆãÉ ÈáÏíÉ İÇÑÛÉ ãä ÔÑíØ ÇáÃÏæÇÊ¡ äÊÇÆÌ ÎÇØÆÉ/İÇÑÛÉ ÚäÏ ÊÛííÑ ÇáæáÇíÉ ãÚ ÈŞÇÁ ÈáÏíÉ ŞÏíãÉ).

**ÇáÓÈÈ ÇáÌĞÑí (ÊÔÎíÕ ÚãíŞ):**
- orders íãáÃ `allCities` ãä mount İÇÑÛğÇ æíÚÊãÏ Úáì ÇÓÊÏÚÇÁ íÏæí `loadFilterCities` **ÍÕÑğÇ ãä ÑÃÓ ÇáÚãæÏ** (filter-portal)º ÈíäãÇ tracking íãáÃ ßá ÈáÏíÇÊ ÇáÌÒÇÆÑ ãÑÉ æÇÍÏÉ İí mount (ÈÏæä cascade ÃÕáğÇ) — áåĞÇ ÇáŞÇÆãÉ ÊÚãá åäÇß ãä Ãí ÈæÇÈÉ.
- ÒÑ ÇáæáÇíÉ İí **ÔÑíØ ÇáÃÏæÇÊ** (orders-filter-bar-portal:158) ßÇä íÓÊÏÚí `setFilter('wilaya')` İŞØ ÈáÇ `loadFilterCities` ? ŞÇÆãÉ ÇáÈáÏíÉ ÊÈŞì İÇÑÛÉ/ŞÏíãÉ.
- `$setFilter` áÇ íÕİøÑ `filters['city']` ÚäÏ ÊÛííÑ ÇáæáÇíÉ ? `state_id=ÌÏíÏÉ AND city_id=ŞÏíãÉ` ? äÊÇÆÌ İÇÑÛÉ/ãÛá?áæØÉ.
- ÒÑ "Çáßá" ááæáÇíÉ (ÔÑíØ ÇáÃÏæÇÊ) áÇ íãÓÍ ÇáÈáÏíÉ¡ ÚßÓ ÒÑ "—" İí ÑÃÓ ÇáÚãæÏ ÇáĞí ßÇä íãÓÍåÇ.

**ÇáÅÕáÇÍ (`orders/index.blade.php` + `orders/partials/filter-portal.blade.php`):**
- ÇáÊÊÇÈÚ äõŞá Åáì **äŞØÉ ÇáÏÎæá ÇáæÇÍÏÉ** `$setFilter`: ÚäÏ `key === 'wilaya'` íÕİøÑ `filters['city']` æíãáÃ `allCities` ÈÈáÏíÇÊ ÇáæáÇíÉ (Ãæ `[]` ÚäÏ null/0) ŞÈá `loadOrders` — İíÕÍíÍ äÊíÌÉ ÇáØáÈ ÇáÍÇáí äİÓå.
- æÍøÏÊ ÃÒÑÇÑ ÑÃÓ ÇáÚãæÏ: `@click="setFilter('wilaya', item.id); close()"` ÈáÇ ÇÓÊÏÚÇÁ `loadFilterCities` (ÒÇÆÏÉ ÇáÂä)º æÒÑ "—" ÈáÇ `setFilter('city')` ãßÑÑ (ßÇä íÑÓá ØáÈğÇ ËÇäíğÇ íõÚíÏ ÊÍãíá ÇáØáÈÇÊ). ÒÑ "Çáßá" İí ÔÑíØ ÇáÃÏæÇÊ íÚãá ÊáŞÇÆíğÇ ÚÈÑ `$setFilter`.
- áÇ íæÌÏ ãÓÇÑ ãÈÇÔÑ ÂÎÑ íÛíøÑ `filters['wilaya']` (ÊÍŞŞ: ßá ÇáßÊÇÈÇÊ ÚÈÑ `setFilter`¡ ÈãÇ İíåÇ ÔÑíÍÉ ÇáÍĞİ İí ÚÑÖ ÇáİáÇÊÑ ÇáäÔØÉ).

**ÇáÊÍŞŞ:** `view:clear`+`view:cache` äÇÌÍ (PHP 8.3.28). `loadFilterCities` ÃÕÈÍÊ ßÓÌæÏğÇ ÛíÑ ãõÓÊÏÚÇÉ (ÊõÑßÊ Ïæä ÍĞİ).

**ãáÇÍÙÉ:** ÊØÈíŞ äİÓ ÇáäãØ Úáì ÊÊÇÈÚ `shipping_provider` ? `stopdesk_point` İí ÔÑíØ ÇáÃÏæÇÊ (ÃÒÑÇÑ "ßá"/ÇáÇÎÊíÇÑ áÇ ÊãÓÍ stopdesk_point æáÇ ÊÓÊÏÚí `loadFilterStopdeskPoints`) — ÈäÏ ÊÇáò ãÍÊãá.

---

## ÅÕáÇÍ ÇáÌĞÑ "ÇáİáÊÑ íÍÏÏ ÑŞã 1 ÏÇÆãğÇ" İí orders (UID ãŞÇÈá int) — 2026-09-16 (6)

**ÇáÚÑÖ:** ÇÎÊíÇÑ Ãí æáÇíÉ/ÈáÏíÉ/ÔÑßÉ ÊæÕíá İí ÇáİáÊÑÉ ? ÇáŞíãÉ ÇáãØÈóøŞÉ ÏÇÆãğÇ "1"¡ ÇáÔÑíØ íÚÑÖ ÇáÑŞã ÈÏá ÇáÇÓã¡ æÇáİáÊÑÉ áÇ ÊÕİí (ßá ÇáäÊÇÆÌ Ãæ ÕİÑ).

**ÇáÓÈÈ ÇáÌĞÑí (ÊÔÎíÕ ÚãíŞ + ÅËÈÇÊ ÈÇáÈíÇäÇÊ):** ŞÇÚÏÉ ÇáÈíÇäÇÊ ÈÇáßÇãá ÊÓÊÎÏã **ãÚÑøİÇÊ äÕíÉ UID** (ãËÇá: æáíÇíÉ `01m1ppwt6z0dy9vphxtw8nhg9s`¡ ãÒæøÏ `01m1pr6aq2033qqvtbjmceaxpt`¡ ãäÊÌ¡ ÚÖæ¡ ÍÇáÉ — ßáåÇ äÕæÕ ØæíáÉ). áßä `$setFilter` İí orders ßÇä íãÑøÑ `wilaya`/`city`/`assigned_to`/`shipping_provider` ÚÈÑ `$intFilters` ? `(int)` áÃí UID = **1** (PHP íŞÑÃ "01" İŞØ). ãÇ ÒÇÏ ÇáØíä ÈáÉğ: MySQL íŞÇÑä ÚãæÏ ÇáäÕ `state_id` ÈÑŞã `1` ÈÊÍæíá ÇáäÕ Åáì ÑŞã ? ßá UID íÈÏÃ ÈÜ "01…" íØÇÈŞ `1` ? ÇáİáÊÑ íÎÊÇÑ **ßá** ÇáØáÈÇÊ áÇ æÇÍÏğÇ.

**ÇáÅËÈÇÊ ÈÇáÏÇÊÇ:** `where('state_id', UID-ÍŞíŞí)` = 9 ØáÈÇÊ¡ ÈíäãÇ `where('state_id', 1)` ÇáŞÏíã = 13 (Çáßá). ÇáãÒæøÏ: 10 ãŞÇÈá 11.

**ÇáÅÕáÇÍ (`orders/index.blade.php`):**
- ÍĞİ `$intFilters` ãä `$setFilter` (ÇáŞíã ÊõÎÒóøä ßÜ UID äÕí ßãÇ ÊÑÓáåÇ JS — äİÓ Óáæß tracking ÇáĞí íÚãá).
- `status`: ÇÓÊÈÏÇá `array_map('intval', …)` ÈÜ `array_values(array_filter(…))` (ÍÇÖÑ áŞİá ÇáãÓÇÑ ÇáÇÍÊíÇØíº ÇáãÓÇÑ ÇáİÚáí `toggleStatusFilter` ßÇä íÎÒøä ÇáäÕ ÇáÕÍíÍ ÃÕáğÇ).
- ÅÒÇáÉ `(int)` Úä `product_id` İí ÇÓÊÚáÇã loadOrders (ÎØ 861) — ßÇä äİÓ ÇáÎáá ááãäÊÌ.
- ÅÒÇáÉ `(int)` Úä ÑÈØ `city_id` İí `orderByRaw` áÊÑÊíÈ ãßÇÊÈ ÇáäãæĞÌ (ÎØÇ 2741 æ3690) — äİÓ ÇáİÆÉ (ÇáÊİÖíá "ÈáÏí ÃæáğÇ" áã íßä íÚãá).

**ÇáÊÍŞŞ:** `view:clear`+`view:cache` äÇÌÍº `OrderCityScopeProviderTest` **8/8 (13 ÔåÇÏÉ)**º ÅËÈÇÊ ÈÇáÏÇÊÇ Ãä ÇáİáÊÑ ÈÇáÜ UID íÍÓÈ 9/10 ÕÍíÍÉ æÇáŞÏíã 13/11 ÎÇØÆÉ.

**ãáÇÍÙÉ:** ÈŞíÉ `(int)` İí Çáãáİ ãÔÑæÚÉ (ßãíÇÊ/ÊÑŞíã ÕİÍÇÊ/ÚÏøÇÏÇÊ). ßÓÌæÏ `loadFilterCities`/`loadFilterStopdeskPoints` (ÇÓÊõÛäí ÚäåãÇ ÈÑÃÓ ÇáÚãæÏ) ÊõÑßÊÇ Ïæä ÍĞİ — ÈäÏ ÊäÙíİ ãÍÊãá.

---

## ÏãÌ ÑÌÇá ÇáÊæÕíá İí İáÊÑ "ÔÑßÉ ÇáÊæÕíá" (orders) — 2026-09-16 (7)

**ÇáØáÈ:** İí ÑÃÓ ÚãæÏ ÔÑßÉ ÇáÊæÕíá ÊÙåÑ ÑÌÇá ÇáÊæÕíá ãÚ ÔÑßÇÊ ÇáÊæÕíá¡ æáíÓ ÇáÔÑßÇÊ İŞØ.

**ÇáÅÕáÇÍ (`orders/index.blade.php` + ÇáÈæÇÈÊÇä):**
- ãáßíÉ ÌÏíÏÉ `allCarrierFilters`: ŞÇÆãÉ ãæÍøÏÉ (ÔÑßÇÊ `kind=provider` + ÃÌÑÇÁ `kind=rider`) ááİáÇÊÑ İŞØ — `allProviders` ÈŞíÊ ÔÑßÇÊ İŞØ ÍÊì áÇ ÊÊÃËÑ ÇáãÍÑøÑÇÊ ÇáãÖãäÉ/ãŞØÇÚÉ ÇáÔÑíß (inline-carrier-select, partner-picker, editProviderOptions).
- `$setFilter` ÊŞÈá ÈÇÑÇãÊÑ ËÇáË `$kind` æÊÎÒøä `filters['shipping_provider_kind']`º ÚäÏ ÇÎÊíÇÑ ÑÇÌá ÊÕİøÑ `stopdesk_point` æ`allStopdeskPoints` (ÇáÑÇÌá ÊæÕíá ãäÒáí ÈáÇ äŞÇØ).
- ÇáÇÓÊÚáÇã: `kind=rider` ? `where('delivery_rider_id', …)` æÅáÇ `where('shipping_provider_id', …)`.
- `loadFilterStopdeskPoints`: áÇ ÇÓÊÚáÇã ÚäÏãÇ íßæä ÇáİáÊÑ ÑÇÌáğÇ (ÊæİíÑ + ÊäÇÓŞ).
- ÇáÈæÇÈÊÇä (ÑÃÓ ÇáÚãæÏ + ÔÑíØ ÇáÃÏæÇÊ) æÇáŞÇÆãÉ ÇáŞÏíãÉ İí ÃÓİá ÇáÑÃÓ: `data-items='@json($this->allCarrierFilters)'` + ÊãÑíÑ `item.kind` İí ÇáäŞÑÉ.
- ÇáÔÑíØ (active chip) íŞÑÃ ÇáÇÓã ãä `allCarrierFilters` (íÚãá ãÚ ÇáÑÇÌá æÇáÔÑßÉ).
- ŞÓã stopdesk İí ÈæÇÈÉ ÑÃÓ ÇáÚãæÏ: ÚäÏ ÇÎÊíÇÑ ÑÇÌá íÚÑÖ "ÇÎÊÑ ÇáãÒæøÏ ÃæáğÇ" ÈÏá ŞÇÆãÉ İÇÑÛÉ.

**ÇáÊÍŞŞ:** `view:cache` äÙíİº ÅËÈÇÊ ÈÇáÏÇÊÇ: `where('delivery_rider_id', UID-ÑÇÌá)` íÚØí 1/1 ØáÈÇÊ æÇáÜ `where('shipping_provider_id', UID-ãÒæøÏ)` íÚØí 10º `OrderCityScopeProviderTest` **8/8 (13 ÔåÇÏÉ)**.

---

## ÚäŞæÏ ÊÎÕíÕ ÇáÍÇáÇÊ æÍÇáÇÊ ÊÊÈÚ ÔÑßÇÊ ÇáÊæÕíá — ÎØÉ ãÑŞøãÉ (2026-09-16)

> **ÇáÍÇáÉ: ÊÎØíØ İŞØ — áÇ ÊäİíĞ ŞÈá ãæÇİŞÉ ÕÑíÍÉ Úáì ßá ãÑÍáÉ Úáì ÍÏÉ.**
> **ÇáÊÑÊíÈ ÇáãŞÑÑ:** 1 ? 2 ? 3 ? 4 ? 5 ? 6. ßá ãÑÍáÉ ÎØÉ ãÓÊŞáÉ ÈĞÇÊåÇ (áÇ ÏãÌ)º
> ÊõäİóøĞ ÈÚÏ ãæÇİŞÉ ÇáãÓÊÎÏã¡ æÊõÚáóøã ? İí Todos.md İæÑ ÇßÊãÇáåÇ ãÚ ÃÏáÉ ÇáÊÍŞŞ.
> **ŞíæÏ ÅáÒÇãíÉ Úáì ßá ãÑÍáÉ:** ÇÓÊÌÇÈÉ 375/768/1440º ãÑÇÌÚÉ ÇáæËÇÆŞ (PROJECT_PLAN.md¡ MerchantPanelAudit.md¡ errorsTodo.md¡ Todos.md¡ OrdersrefactorplanFixed.md¡ DESIGN_SYSTEM.md) ŞÈá ÇáÊäİíĞº İÍÕ ÇáßæÏ ÇáÍí áÇ ÇáÇİÊÑÇÖº åæíÉ Apple Design (ÊæßäÒ `--edz-*`/`ink-*`/`surface-*` İŞØ)º ÇáÃÏÇÁ (ÈáÇ ÇÓÊÚáÇãÇÊ/ÅÚÇÏÉ ÑÓã ÒÇÆÏÉ)º ÚÏã ÍÔÑ ßá ÔíÁ İí ÕİÍÉ index ÇáÑÆíÓíÉ.

### ÇáãÑÍáÉ 1 — ÈäíÉ «ÇáÊÎÕíÕ»: ÇáÔÑíØ ÇáÌÇäÈí + ÇáÊæÌíå + ÕİÍÉ ÇáÍÇáÇÊ (ÅØÇÑ + ÊÇÈÇÊ) ?

**ÇáåÏİ:** ãäİĞ ÌÏíÏ İí «ÅÚÏÇÏÇÊ ÇáãÊÌÑ» ÈÇÓã **ÇáÊÎÕíÕ**¡ ãÚ ÕİÍÉ ÍÇáÇÊ ÈÊÇÈÇÊ (ÊÃßíÏ / ÊÊÈÚ ÇáÔÑßÉ / ÊÊÈÚ ÇáÑÇÌá) ÈÅØÇÑåÇ ÃæáğÇ Ëã ÊõãáÃ ÈÇáãÑÇÍá ÇáÊÇáíÉ.

**ÇáÃÏáÉ ÇáãËÈÊÉ:**
- ÇáÊæÌíå: ãÌãæÚÉ Layer-3 İí `routes/merchant.php:59-115` (`prefix merchant` + `{store:slug}` + ÇáÓáÓáÉ ÇáæÓØíÉ: `ResolveStoreFromRoute/EnsureStoreResolved/EnsureStoreMembership/EnsureHasStoreRole: 'owner,admin,manager,staff'/EnsureStoreIsActive`). ßá ÕİÍÇÊ ÇáÊÇÌÑ Volt ÚÈÑ `Volt::route(...)` (orders 94¡ tracking 95¡ order-settings 99¡ storefront 107¡ settings 108).
- ÇáÓÇíÏ ÈÇÑ: ãÌãæÚÉ «ÅÚÏÇÏÇÊ ÇáãÊÌÑ» İí `store-sidebar.blade.php:358-396` (ÇáãÌãæÚÉ ÇáİÑÚíÉ `edz-sub-store`)º ÍÇáÉ ÇáÊæÓøÚ `storeOpen` İí ÇáÃÓØÑ 43-46º ÈæÇÈÉ ÇáÕáÇÍíÇÊ `withData[...]` İí 58-64.
- ŞÇáÈ ÇáÕİÍÇÊ: Volt ÇáãÖãøä `layout('components.layouts.store')` (íõÛáøİ `x-layouts.panel` + sidebar=store) + `<x-edz.page-header>` + `edz-card edz-card--padded` + ÊÇÈÇÊ ÈÕŞİÉ `$tab` (äãØ `order-settings.blade.php:436-449`) Ãæ ÊÚáíãÉ Alpine-pills (äãØ `store-settings.blade.php:141-204`).

**ÇáÊÚÏíáÇÊ ÇáãŞÊÑÍÉ (ÈÇáãáİÇÊ):**
1. `routes/merchant.php` (ÈÚÏ ÓØÑ 99):
   ```php
   Volt::route('/{store:slug}/customization/statuses', 'merchant.customization.statuses')
       ->name('customization.statuses');
   ```
   **ŞÑÇÑ ãİÊæÍ (ÇÎÊíÇÑ ÇáãÓÊÎÏã):** ÇáãÓÇÑ ÇáãŞÊÑÍ `…/customization/statuses` ãÚ «ÍÇáÇÊ ÇáÊÃßíÏ» Ãæá ÊÇÈ ÏÇÎáíğÇ (ÃäÙİ)¡ Ãæ ÇáãÓÇÑÇÊ ÇáÚãíŞÉ ÇáŞÇÈáÉ ááÇÔÊÑÇß `…/customization/statuses/confirmation` (ÊáÈíÉğ áØáÈ ÇáãÓÊÎÏã ÍÑİíğÇ).
2. `store-sidebar.blade.php`:
   - ÓØÑ ~62: `$withData['canViewCustomization'] = canStore(StorePermissionEnum::STORE_UPDATE->value);`
   - ÇáÃÓØÑ 43-46: ÅÖÇİÉ `'merchant.customization.*'` Åáì ŞÇÆãÉ `storeOpen` (ÊæÓøÚ ÊáŞÇÆí ÚäÏ ÇáÏÎæá).
   - ÈÚÏ ÓØÑ 393 (ÑÇÈØ storefront): ÑÇÈØ İÑÚí «ÇáÍÇáÇÊ» ÈÔÑØ `@if ($canViewCustomization)` æÃíŞæäÉ `adjustments` (äÓÎ äãØ 387-393) ? `route('merchant.customization.statuses', $store)` + ÊİÚíá `goods()` ÚäÏ `merchant.customization.*`.
   - ÓØÑ 358: ÇáÌÇÑÏ ÇáÇİÊÊÇÍí ? `@if ($canViewStoreSettings || $canViewStorefront || $canViewCustomization)`.
3. ÕİÍÉ ÌÏíÏÉ `resources/views/livewire/merchant/customization/statuses.blade.php` (Volt ãÖãøä):
   - `layout('components.layouts.store')` + mount: `abort_unless(canStore(STORE_UPDATE->value), 403)`.
   - `state('tab' => 'confirmation')` + `<x-edz.page-header>` (title/description) + ÊÇÈÇÊ `$tab` ÈŞÇÚ ÇáÜ pills:
     - `confirmation` (ÍÇáÇÊ ÇáÊÃßíÏ) — ÊõãáÃ ÈÇáãÑÍáÉ 2.
     - `carrier_tracking` (ÍÇáÇÊ ÊÊÈÚ ÔÑßÉ ÇáÊæÕíá) — ÊõãáÃ ÈÇáãÑÍáÉ 3.
     - `rider_tracking` (ÊÎÕíÕ ÍÇáÇÊ ÊÊÈÚ ÇáÑÇÌá) — ÊõãáÃ ÈÇáãÑÍáÉ 4.
   - ßá ÊÇÈ ÏÇÎá `edz-card edz-card--padded` ÈÍÇáÉ İÇÑÛÉ ãÄŞÊÉ (`empty state` ÈŞÇáÈ `x-edz.icon` + äÕ).

**ÇáÊÑÌãÉ ×4** (`resources/lang/{ar,en,fr,es}/merchant_panel.php` + `order_flow.php`): `customization`¡ `statuses`¡ `tab_confirmation`¡ `tab_carrier_tracking`¡ `tab_rider_tracking` + ÇáÚäæÇä/ÇáæÕİ/ÍÇáÉ ÇáİÑÇÛ.

**ÇáÊÕãíã/ÇáÃÏÇÁ:** ÊæßäÒ `--edz-*` İŞØ (ŞÇÚÏÉ TailAdmin + DESIGN_SYSTEM ŞÓã 59-65)º áÇ ÇÓÊÚáÇãÇÊ ÅÖÇİíÉ İí åĞå ÇáãÑÍáÉ (ÅØÇÑ İÇÑÛ)º İÍÕ 375/768/1440 (ÈØÇŞÇÊ/ÏÑÌ ááÔÇÔÇÊ ÇáÕÛíÑÉ).

**ÇáÊÍŞŞ:** `php -l` Úáì Çáãáİ ÇáÌÏíÏ + `view:cache` äÇÌÍ + İÊÍ ÇáÑÇÈØ ÈÚÖæ STORE_UPDATE æÚÖæ ÈÏæäå (403) + ÊæÓøÚ ãÌãæÚÉ «ÅÚÏÇÏÇÊ ÇáãÊÌÑ» ÊáŞÇÆíğÇ + ÊÓãíÉ ÇáÑÇÈØ.

**ÇáÇÚÊãÇÏíÇÊ:** ÃÓÇÓ ÇáãÑÇÍá 2/3/4.

**? ãäÌÒ (2026-09-16).** **ŞÑÇÑ ÇáãÓÊÎÏã:** ãÓÇÑ æÇÍÏ `customization/statuses` (ÃäÙİ) ÈÏá ÇáãÓÇÑÇÊ ÇáÚãíŞÉ. **ãÇ äõİøĞ:**
- `routes/merchant.php` (ÅÚÏÇÏÇÊ ÇáãÊÌÑ): `Volt::route('/{store:slug}/customization/statuses', 'merchant.customization.statuses')->name('customization.statuses')`.
- `store-sidebar.blade.php`: `canViewCustomization = STORE_UPDATE` (ÓØÑ ~63)º `storeOpen` + `merchant.customization.*` (ÊæÓøÚ ÊáŞÇÆí)º ÑÇÈØ «ÇáÍÇáÇÊ» ÈÃíŞæäÉ `adjustments` ÈÚÏ storefront (ÔÑØ `@if ($canViewCustomization)` + ÊİÚíá ÚäÏ `merchant.customization.*`)º ÇáÌÇÑÏ ÇáÇİÊÊÇÍí + `$canViewCustomization`.
- `livewire/merchant/customization/statuses.blade.php` (ÌÏíÏ): `layout('components.layouts.store')` + mount `abort_unless(canStore(STORE_UPDATE->value), 403)` + `$tab` (confirmation/carrier_tracking/rider_tracking) ÈäãØ ÃÓØÑ order-settings 436-449 + 3 ÊÇÈÇÊ ÏÇÎá `edz-card edz-card--padded` ÈÍÇáÇÊ İÇÑÛÉ.
- ÇáÊÑÌãÇÊ ×4 (`merchant_panel.php`): `customization`¡ `customization_desc`¡ `statuses`¡ `tab_confirmation`¡ `tab_carrier_tracking`¡ `tab_rider_tracking`¡ `customization_empty`.
**ÇáÊÍŞŞ:** php -l äÙíİ (ÕİÍÉ+ÊÑÌãÇÊ ×4) + `route:list` íõÙåÑ ÇáãÓÇÑ + **`StatusCustomizationPageTest`** (ÇÎÊÈÇÑ Feature ÌÏíÏ¡ äãØ MerchantDeepLinkTest): owner 200 (ÊÇÈÇÊ ×3 + ÑÇÈØ ÓÇíÏ ÈÇÑ `Statuses` + `edz-sidebar__sub-link--active` + `store: true` + äÕ ÇáÍÇáÉ ÇáİÇÑÛÉ) + staff 403 — 2 äÇÌÍ (12 ÊÃßíÏğÇ) + ÇáÓáÓáÉ ÇáßÇãáÉ **781 (3066) äÙíİÉ** + Pint (3 ãáİÇÊ¡ 3 ÅÕáÇÍÇÊ).

### ÇáãÑÍáÉ 2 — ÍÇáÇÊ ÇáÊÃßíÏ: ÚÑÖ + ÊÎÕíÕ áßá ãÊÌÑ + ÊÇÈ ÇáÊÑÊíÈ ?

**ÇáåÏİ:** ÊÇÈ «ÍÇáÇÊ ÇáÊÃßíÏ» (ÈĞÑÉ Confirmation Pipeline ÇáİÚáíÉ — **10 ÍÇáÇÊ** ãõÈĞæÑÉ: pending, confirmed, no_answer_1/2/3, postponed, wrong_number, out_of_stock, duplicate, on_hold¡ sort 1..10º **`draft` ÛíÑ ãÈĞæÑ** ááÜ order — ÇÚÊõãÏ ÇáßæÏ ÇáÍí) ŞÇÈáÉ ááÚÑÖ æÇáÊÎÕíÕ áßá ãÊÌÑ (ÊÓãíÉ/áæä) æ«ÊÑÊíÈ ÍÇáÇÊ ÇáÊÃßíÏ» ÓØÑğÇ ÓØÑ (?/? ? `sort_order`).

**ÇáÃÏáÉ ÇáãËÈÊÉ:**
- ÌÏæá `statuses` íÏÚã `store_id` nullable + unique `(store_scope_id, type, key)` + `store_scope_id` ÇİÊÑÇÖíÉº ÇáäãæĞÌ fillable íÔãá store_id/type/key/label/color/is_system/affects_inventory/movement_type/icon/display_mode/sort_order (`app/Models/Status.php:15-27`).
- `StatusResolver::resolve()` íİÖøá Õİ ÇáãÊÌÑ Ëã Õİ ÇáäÙÇã Ëã kit (`app/Domains/Status/StatusResolver.php:21-44`) ? **ÂáíÉ ÇáÜ override ÌÇåÒÉ**.
- **áÇ íæÌÏ Ãí ßÇÊÈ áÕİæİ ãÊÌÑ Çáíæã** (áÇ `Status::create` İí app/º ÑÓÇáÉ CRUD ÍÕÑğÇ Filament SuperAdmin) — åĞå ÇáãÑÍáÉ Ãæá ßÇÊÈ.
- ÓíÏÑ ÇáäÙÇã: `SystemStatusesSeeder` order block (ÃÓØÑ 19-289) ÈÊÚáíŞ Confirmation Pipeline (0-10).

**ÇáÊÚÏíáÇÊ ÇáãŞÊÑÍÉ:**
1. ŞÑÇÁÉ ÇáŞÇÆãÉ: `Status::where('type','order')->where(fn $q => $q->whereNull('store_id')->orWhere('store_id',$storeId))->orderBy('sort_order')` ãİáÊÑÉ ÈãİÇÊíÍ ŞÈá ÇáÅÑÓÇá (11).
2. **ŞÇÚÏÉ ÇáÜ override:** ÚäÏ Ãí ÊÚÏíá/ÊÑÊíÈ áÍÇáÉ äÙÇãíÉ ? `updateOrCreate` Õİ ãÊÌÑ ÈäİÓ `(store_id, type='order', key)` íÍãá ÇáŞíã ÇáãÚÏøáÉ (íÊæÌøå ÇáÜ Resolver Åáíå ÊáŞÇÆíğÇº áÇ äáãÓ Õİ ÇáäÙÇã).
3. äŞÇØ ÇáÊÍÑíÑ: ÊÓãíÉ (äÕ)¡ áæä (ãä `general` kit)¡ ÊİÚíá/ÊÚØíá¡ æÃÓåã ÊÑÊíÈ ?/? ãÚ ãÄÔÑ ãæÖÚ (sort_order).
4. ÊÇÈ «ÇáÊÑÊíÈ»: ŞÇÆãÉ ÈÊÑÊíÈ `sort_order` + ÃÒÑÇÑ ?/? (wire:click ãÈÇÔÑ — ÈáÇ ÓÍÈ áÊÈÓíØ ÇáÃÏÇÁ).
5. ÎÏãÉ ÕÛíÑÉ `app/Services/Stores/StoreStatusService.php` áÖÈØ ŞæÇÚÏ ÇáÜ override æÇáßÊÇÈÉ (ÊõÓÊÎÏã İí ÇáãÑÍáÊíä 2 æ4).

**ÇáÊÑÌãÉ:** ÊÓãíÇÊ ÇáäÙÇã ÊõÊÑÌã ÚÈÑ status-kit (`systemLabel()` íÚíÏ ÇÔÊŞÇŞåÇ) — áÇ Ïæã äÕæÕ ÅäÌáíÒíÉ ááÕİæİ ÇáäÙÇãíÉº ÇáãÎÕÕÇÊ áåÇ `label` äÕí İí Õİ ÇáãÊÌÑ (ÃíÇğ ßÇäÊ áÛÊå ßãÇ ÃÏÎáåÇ ÇáÊÇÌÑ).

**ÇáÊÍŞŞ:** `StoreStatusCustomizationTest` (**10 äÇÌÍ / 32 ÊÃßíÏğÇ**): ÇáŞÇÆãÉ ÇáãõÈĞæÑÉ ÈÇáÊÑÊíÈ¡ ÅäÔÇÁ override ÚÈÑ Save¡ ÇáÜ Resolver íİÖøá Õİ ÇáãÊÌÑ¡ ßÍá ÇáÜ label ÇáİÇÑÛ ? ÊÑÌãÉ kit¡ ÚÒá ÇáãÊÌÑ¡ ÇáÊÍÑß ÃÚáì/ÃÓİá + ÅÚÇÏÉ ÇáÊÓáÓá¡ ÍÏæÏ no-op¡ ÑİÖ ÇáãİÇÊíÍ/ÇáÃáæÇä ÛíÑ ÇáÕÇáÍÉ¡ ÇáÍİÙ ÚÈÑ Çáãßæøä (ÊÓãíÉ+áæä ãÚğÇ — **fix: `writeOverride` ßÇäÊ ÊãÍæ label ÈÇáÊÍÏíË ÇááÇÍŞ**)¡ ÇáÊÑÊíÈ ÚÈÑ Çáãßæøä. `StatusCustomizationPageTest` (ã1) ÍõÏöøË ÇáÊÃßíÏ áäãØ ÇáãÑÍáÉ 2 ÇáİÚáí. **ÇáÌæáÉ ÇáßÇãáÉ: 791 äÇÌÍ (3100 ÊÃßíÏğÇ)** + `view:cache` äÙíİ + Pint. **ãáÇÍÙÉ ÃãÇä:** ÇäÊŞÇáÇÊ ÇáØáÈíÇÊ ÊÓÊÎÏã ÏÇÆãğÇ `Status::system()` (OrderService/Observer) ? ÇáÜ override íÄËÑ Úáì ÇáÚÑÖ İŞØ¡ áÇ Úáì `affects_inventory`/`movement_type`.

**ÇáÇÚÊãÇÏíÇÊ:** ÇáãÑÍáÉ 1. íÏÚã ãÈÇÔÑÉ İáÊÑ ÇáØáÈíÇÊ ÈÇáãÑÍáÉ 6 (äİÓ ÇáŞÇÆãÉ ÇáÜ 10 ÚÈÑ `StoreOrderPhases`).

### ÇáãÑÍáÉ 3 — ÍÇáÇÊ ÊÊÈÚ ÔÑßÇÊ ÇáÊæÕíá (API): ÌãÚ + ãİÇÊíÍ ÊÑÌãÉ + ÈĞÑ İí `statuses` ?

**ÇáåÏİ:** ŞÇãæÓ ãæÍøÏ áÍÇáÇÊ ßá ÔÑßÉ ÊæÕíá (`raw ? key ÏÇÎáí`) + ãİÇÊíÍ ÊÑÌãÉ ×4 + ÈĞÑåÇ İí `statuses` (`type='tracking'`) ÈÍíË áÇ ÊÕáäÇ ÍÇáÉ ÈáÇ label ÏÇÎá äÙÇãäÇ.

**ÇáÃÏáÉ ÇáãËÈÊÉ (ãä İÍÕ ÇáÊßÇãá):**
- ÇáÊßÇãá ÇáæÍíÏ ÇáãõİÚøá: **NOEST** — Webhook `DeliveryWebhookController` (accepted payload shapes: OrderInfo+activity/events/Flat) + Poll `SyncNoestTrackingJob`º ßáÇåãÇ íÊŞÇØÚÇä İí `NoestTrackingSyncService::apply()`. ÇáŞíã RAW äÍæ 40+ `event_key` İí `NoestTrackingMapper::MAP` + ãØÇÈŞÉ äÕíÉ `eventTextToStatus` + `terminalKeys`º ÛíÑ ÇáãÍÏÏ ? fallback `IN_TRANSIT` (apply ÓØÑ 88-110).
- **Ecotrack** / **Yalidine**: æËÇÆŞ İí ÇáÑíÈæ İŞØ (11 äÔÇØğÇ + 19 ÍÇáÉ / 30 ÓáÓáÉ `last_status`).
- **ZR Express v2 / Anderson**: ÃÓØÑ ßÊÇáæÌ İŞØ (`CarrierCatalogSeeder:40-49`)¡ **ÈáÇ ÊæËíŞ** ? ÅÈŞÇÄåÇ fallback ãÄŞÊğÇ Ãæ ãÕÏÑ ÎÇÑÌí.
- ÇáÍÇáÇÊ ÇáÏÇÎáíÉ `type='tracking'` ÇáÍÇáíÉ (9): shipped, in_transit, out_for_delivery, delivered, returned, returning, failed_attempt, lost, damaged (`SystemStatusesSeeder:466-521`)º ? `OrderTrackingStatus` (9 ÍÇáÇÊ¡ labels ÚÈÑ status-kit ×4 áÛÇÊ).
- `order_trackings` íÍİÙ `carrier_status/carrier_label/tracking_status/carrier_raw` + history ÚÈÑ `order_tracking_histories`.

**ÇáÊÚÏíáÇÊ ÇáãŞÊÑÍÉ:**
1. `app/Domains/Shipping/Support/CarrierStatusDictionary.php` (ÌÏíÏ): ÌÏæá áßá ÔÑßÉ `raw_status ? internal_key` (Şíã NOEST ÍÑİíğÇ ãä MAP ÇáæËÇÆŞ¡ Ecotrack/Yalidine ãä æËÇÆŞåãÇ) + `internal_key ? label_key`.
2. `SystemStatusesSeeder` (ŞÓã tracking): ÅÖÇİÉ ãİÇÊíÍ ÌÏíÏÉ ÊäÔÃ ãä ÇáŞÇãæÓ (áã Êßä ãÚÑİÉ) æãæÇÁãÉ label ÚÈÑ ãÎİÇÊíÍ ÇáÊÑÌãÉ ÈÏá ÇáäÕ ÇáÅäÌáíÒí (ÊæÍíÏ ãÚ `systemLabel()`).
3. ÊÑÌãÉ ×4: ãİÇÊíÍ ßá ŞíãÉ ãõÕØÇÏÉ İí status-kit/lang + `order_flow.php`.
4. `NoestTrackingSyncService`/`NoestTrackingMapper`: ÇÓÊÈÏÇá fallback `IN_TRANSIT` ÈÇáŞÇãæÓ (ßá ŞíãÉ æÇÑÏÉ áåÇ key ÏÇÆãÉ) + ÇáÅÈŞÇÁ Úáì `carrier_raw` ááÚÑÖ ÇáÃÕáí.
5. ÊÇÈ `carrier_tracking` İí ÕİÍÉ ÇáÍÇáÇÊ (ãä ã1): ÌÏæá `raw ? ÇáŞíãÉ ÇáãõØÈóøŞÉ ? ÇáÊÓãíÉ ÇáãÊÑÌãÉ` ááÔÑßÉ ÇáãÎÊÇÑÉ (ŞÑÇÁÉ ãä ÇáŞÇãæÓ + statuses).

**ÇáÊÍŞŞ:** ÊÍÏíË/ÅÖÇİÉ ÇÎÊÈÇÑÇÊ `NoestTrackingSyncService*`: ßá ŞíãÉ ãä ÇáŞÇãæÓ ? key ãÍÏÏ (áÇ fallback)º `view:cache`º ÌæáÉ ÇáÊÊÈÚ ÇáãÑÌÚíÉ ÕİÑ ÇäÍÏÇÑ.

**ÇáÇÚÊãÇÏíÇÊ:** ÇáãÑÍáÉ 1. ãÑÊÈØ ÈÇáãÑÍáÉ 5: Åä ÙåÑÊ ãİÇÊíÍ ÊÊÌÇæÒ ÇáÎãÓÉ ÇáËÇÈÊÉ İí ÇáÎØ ÇáÒãäí (shipped/in_transit/out_for_delivery/delivered/returned) äãÏøÏ ÇáÎØ ÇáÒãäí/ÇáÜ stepper åäÇß.

**? ãäÌÒ (2026-09-16).** **äØÇŞ ÇáÊäİíĞ æßá ŞÑÇÑÇÊå ÇáãÚÊãÏÉ ßãÇ æõËøŞÊ ÎáÇá ÇáÌáÓÉ:**
- **`app/Domains/Shipping/Support/CarrierStatusDictionary.php` (ÌÏíÏ):** ŞÇãæÓ ãæÍøÏ `raw ? OrderTrackingStatus` áßá ÔÑßÉ — **NOEST** (ÃÍÏÇË MAP ÇáÍÇáíÉ + ãİÇÊíÍ ÇáæËíŞÉ ÇáÌÏíÏÉ: `colis_suspendu`/`colis_pickup_transmit_to_partner`/`echange_valide`/`echange_valid_by_hub`/`verssement_admin_cust`/`validation_reception_cash_by_partener` + ÇáãáÛÇÉ `*_canceled` + `ask_to_delete_*`)º **Ecotrack** (11 activity + 19 status ÍÑİíğÇ — `annule`?cancelled¡ `all` ãÓÊÈÚÏ ßİáÊÑ)º **Yalidine** (36 history statuses ÍÑİíğÇ ÈİÑäÓíÊåÇ¡ `Bloqué`/`En alerte`?on_hold¡ `Colis abandonné`?lost¡ `Annulé`?cancelled).
  - API: `carriers()`, `carrierOptions()` (ÃÓãÇÁ ãæØóøäÉ ×4), `statusFor(carrier, raw)`, `list(carrier)`, `keysFor(carrier, status)`. **ÊõÓÊÈÚÏ ÇáÃÍÏÇË ÇáÅÏÇÑíÉ/ÇáãÇáíÉ** (`edited_informations`, `edit_price`, `edit_wilaya`, `extra_fee`) ? null (ÊõÈŞí ÇáÍÇáÉ ÇáÓÇÈŞÉº íØÇÈŞ ÇÎÊÈÇÑ unmapped ÇáŞÇÆã).
- **`OrderTrackingStatus` ÊæÓøÚ 9 ? 11:** `ON_HOLD = 'on_hold'` (open) æ`CANCELLED = 'cancelled'` (terminal) — labels ×4 (en/ar/fr/es) + ÅÏÎÇáÇ **`config/status-kit-statuses.php`** İí ãÌãæÚÉ `tracking` ÈÃíŞæäÊíä ãÓÌáÊíä (`on_hold`, `cancelled`) + ÊÍÏíË `fromCarrier` (hold/blocked/suspended ? ON_HOLDº cancel/annul ? CANCELLEDº abandoned ? LOST).
- **`SystemStatusesSeeder` (ŞÓã tracking):** ÇáÕİÇä ÇáÌÏíÏÇä `on_hold` (sort 4) æ`cancelled` (sort 9)¡ ÈÅÚÇÏÉ ÊÓáÓá delivered?5 .. failed_attempt?8 .. damaged?11.
- **`NoestTrackingMapper`**: ÃõİÑÛ ãä `MAP` æíÊİÑøÏ ááŞÇãæÓ (`toStatus`/`terminalKeys` ÚÈÑ `CarrierStatusDictionary`) ãÚ ÇáÅÈŞÇÁ Úáì `eventTextToStatus`. **`NoestTrackingSyncService::apply()`**: ÍõĞİ fallback `IN_TRANSIT` — ÇáÍÏË ÛíÑ ÇáãÚíøä (ÍÊì Úáì Õİ İÇÑÛ) íÈŞì ÈáÇ ÍÇáÉ æáÇ History ãÚ ÊÍÏíË `last_synced_at` İŞØ + `eventDate` ááÜ delivered/returned ÊÊÍÏ ÇáİÑÚí ãä `terminalKeys` + status value.
- **ÊÇÈ `carrier_tracking` İí ÕİÍÉ ÇáÍÇáÇÊ:** `x-edz.select` ÈÔÑßÉ (noest/ecotrack/yalidine¡ `wire:model="carrier"`) + ÌÏæá `raw (mono) ? ÇáŞíãÉ ÇáãõØÈóøŞÉ (badge/classes) ? ÇáÊÓãíÉ ÇáãÊÑÌãÉ` ÚÈÑ `StatusResolver::resolve('tracking', key, storeId)` (row ÌÏíÏ `$carrierRows`). ãİÇÊíÍ `merchant_panel` ×4 ÇáÌÏíÏÉ: `carrier_tracking_select/hint/raw/applied/label` + `carrier_noest/ecotrack/yalidine` (ÇáİÑäÓíÉ: Libellé/Statut brut/…).
**ÇáÊÍŞŞ:** `CarrierStatusDictionaryTest` (ÌÏíÏ — 7 ÇÎÊÈÇÑÇÊ: ÇáÔÑßÇÊ ÇáÜ 3 + NOEST + on_hold/cancelled + Events ÅÏÇÑíÉ null + Ecotrack + Yalidine + lists/keysFor) + `OrderTrackingStatusTest` (11 ÍÇáÉ + ÊÕäíİÇÊ + matcher + labels ×2 + icons) + `NoestTrackingSyncServiceTest` (ÇÎÊÈÇÑ fallback ÇáãõÓÊÈÏá: Õİ İÇÑÛ ÈáÇ ÍÇáÉ ÈáÇ History + ÇÎÊÈÇÑ dict ÌÏíÏ `colis_suspendu`?on_hold æ`ask_to_delete_by_admin`?cancelled) + `StatusCustomizationPageTest` (ÌÏíÏ: Livewire tab test — `setTab('carrier_tracking')` + ÊÈÏíá `carrier` Åáì `yalidine`) + `StatusLabelPrecedenceTest` (9?11 + on_hold/cancelled). **ÇáÌæáÉ ÇáßÇãáÉ: 800 äÇÌÍ (3222 ÊÃßíÏğÇ)** (`php -l` äÙíİ ×5 + Pint 10 ãáİÇÊ + `view:cache` äÇÌÍ). **ãáÇÍÙÉ ŞÑÇÑ:** ÊÑßÊ ÊÓãíÇÊ Õİæİ ÇáÈĞÑÉ Úáì äãØ ÇáÊÓãíÉ ÇáÅäÌáíÒíÉ ÇáÑÇÆÌ áÈŞíÉ Õİæİ ÇáÓíÏÑ (override `label=''` ßí ÊõÊÑÌã ãä status-kit) — áÇ ÊÛííÑ Úáì `systemLabel()` İí åĞå ÇáãÑÍáÉ.

### ÇáãÑÍáÉ 4 — ÍÇáÇÊ ÊÊÈÚ ÑÇÌá ÇáÊæÕíá: ÊÎÕíÕ + ÊÇÈ ÇáÊÑÊíÈ ?

**ÇáåÏİ:** ÊÇÈ «ÊÎÕíÕ ÍÇáÇÊ ÊÊÈÚ ÇáÑÇÌá» (ÅÖÇİÉ/ÊÚÏíá ÊÓãíÉ/áæä/ÊİÚíá) + «ÊÑÊíÈ ÍÇáÇÊ ÊÊÈÚ ÇáÑÇÌá» (?/? ? sort_order) — ÍÇáÇÊäÇ ÇáãÍáíÉ áÇ ÊõÓÊŞÈá ãä Ãí API.

**ÇáÃÏáÉ ÇáãËÈÊÉ:** ÍÇáÉ ÇáÑÇÌá ãÍáíÉ ÈÇáßÇãá — `OrderTrackingService::startShipment` íÈĞÑ `tracking_status='shipped'` æ`mark*()` íßÊÈ delivered/returned/in_transit... (`app/Domains/.../OrderTrackingService.php:17-44, 86-152`)º ÑŞã ÊÊÈÚ ãÍáí `HM-…/SD-…` (`generateRiderTrackingNumber:52-61`)º áÇ `shipping_provider_id` Úáì ãÓÇÑ ÇáÑÇÌá.

**ÇáÊÚÏíáÇÊ ÇáãŞÊÑÍÉ:**
1. İí ÕİÍÉ ÇáÍÇáÇÊ (ã1) ÊÇÈ `rider_tracking`: äİÓ äãØ ÇáãÑÍáÉ 2 ÈäØÇŞ `type='tracking'` ááÑÇÌá¡ ãÚ ÅãßÇäíÉ ÅÖÇİÉ ÍÇáÇÊ ÌÏíÏÉ íÍÏÏåÇ ÇáãÊÌÑ (Õİæİ ãÊÌÑ ÈÜ store_scope_id).
2. ÊÇÈ «ÇáÊÑÊíÈ» ááÑÇÌá: äİÓ ÂáíÉ ?/? ÚÈÑ `StoreStatusService`º áÇ íÊÏÇÎá ãÚ Õİæİ ÇáÔÑßÉ (İáÊÑ ÈäØÇŞ ÇáÑÇÌá).
3. ÑÈØ ÇáÊÔÛíá: ÍÇáÇÊ ÇáÑÇÌá ÇáãÎÕÕÉ ÊÙåÑ İí ŞÇÆãÉ/stepper/ÅÍÕÇÁÇÊ ÇáÑÇÌá İŞØ ãä ÎáÇá ŞÑÇÁÉ `OrderTracking::tracking_status` ĞßíÉ (status-kit + Õİæİ ÇáãÊÌÑ) — **ÈáÇ ÊÚÏíá Úáì `mark*`** (ÍÇáÇÊ ÇáÊÈÏíá ÇáÃÓÇÓíÉ ÊÈŞì enum).

**ÇáÊÍŞŞ:** ÅÖÇİÉ ÍÇáÉ ÑÇÌá ãÎÕÕÉ áãÊÌÑ ? ÊÙåÑ İí ŞÇÆãÉ/ÊÑÊíÈ/stepper ÇáÑÇÌá İŞØ æáÇ ÊÓÑøÈ ááÔÑßÉº Feature test + `view:cache`.

**ÇáÇÚÊãÇÏíÇÊ:** ÇáãÑÇÍá 1 æ2 (äİÓ ÂáíÉ override).

**? ãäÌÒ (2026-09-17) — ÈŞÑÇÑÇÊ ÇáãÓÊÎÏã ÎáÇá ÇáÌáÓÉ** («ÇáÊİÇÕíá ? ÇáãÑÍáÉ 4 İŞØ»º ÇáÊÑÌãÉ «ÇáãÕÏÑÇä ãÚğÇ»º ÚãæÏ «ÇáãÚäì ÇáÍŞíŞí» İí ÊÈæíÈ ÇáÔÑßÉº ÍÇáÇÊ ÊÃßíÏ ãÎÕÕÉ ãÑÊÈØÉ ÈÍÇáÉ ÃÕáíÉ = İÑÚ æÙíİí). ãÇ äõİøĞ:
- **`Status` (+ åÌÑÉ ÌÏíÏÉ `2026_09_17_000000_add_linked_to_to_statuses_table.php`):** ÚãæÏ `linked_to` nullable (ãİÊÇÍ ÇáÍÇáÉ ÇáÃÕáíÉ áÕİæİ ÇáãÊÌÑ ÇáãÑßøÈÉ/ÇáãÎÕÕÉ).
- **`StoreStatusService`:** ÅÚÇÏÉ ßÊÇÈÉ ÈÇáßÇãá — `TRACKING_TYPE`¡ `riderList()/riderSave*/moveRider()/addStatus()/deleteStatus()`¡ `canonicalKey()`¡ ŞæÇÚÏ ÇáãÑÍáÉ 2ú ááÜ override + İÑÚ `linked_to`º ßÊÇÈÉ Õİæİ ãÊÌÑ `(store_scope_id, type, key)`º `scopeType` ááÍÇáÉ ÛíÑ ÇáäÙÇãíÉ (rider/custom order).
- **ÕİÍÉ `statuses.blade.php`:** ÅÚÇÏÉ ßÊÇÈÉ — ÊÇÈÇÊ 3 (confirmation/carrier_tracking/rider_tracking) + «ÊÑÊíÈ»º ÏÇÑÉ `$tab`; ÚÑÖ/ÊÑÊíÈ ?/?º ÅÖÇİÉ/ÍĞİ ÍÇáÉ ÑÇÌá ÈÇÓã æáæäº ÅÖÇİÉ ÍÇáÉ ÊÃßíÏ ãÎÕÕÉ (label/color) ÈÇÎÊíÇÑ ÍÇáÉ ÃÕáíÉ `linked_to` ÊõÚÑÖ ÔÇÑÉ ÇáÑÈØ.
- **ãİÇÊíÍ ÇáãİÇÊíÍ ÇáãÎÕÕÉ:**
  - `OrderService::transition()`: ÇáãİÊÇÍ ÇáäÙÇãí ? ÇáÕİ ÇáäÙÇãí (ÇáÜ override ááÚÑÖ İŞØ ãÍİæÙ)º ãİÊÇÍ ÎÇÕ ÈÇáãÊÌÑ İŞØ ? Õİ ÇáãÊÌÑ ÚÈÑ `firstOrFail` (áÇ `ModelNotFoundException` áãİÇÊíÍ ÇáİÑæÚ).
  - `OrderService::availableTransitions()`: Åä ßÇäÊ ÍÇáÉ ÇáØáÈíÉ ÇáÍÇáíÉ Õİ ãÊÌÑ ĞÇ `linked_to` ? ÊõÄÎĞ ÇäÊŞÇáÇÊ ÇáÃÕá (ÇáİÑÚ íÑË ãÎÇÑÌ ÃÕáå — áíÓ ØÑíŞğÇ ãÓÏæÏğÇ).
  - `OrderService::canTransition()`: íŞÈá ÇáãİÊÇÍ ÇáãÎÕÕ Åä ßÇä `linked_to` ÓãÍ áå ÇáäøÊŞÇá (ÃÕáå åÏİ ãÓãæÍ).
  - `OrderObserver::handleStatusChange`: íŞÑÃ `movement_type`/`affects_inventory` ãä ÇáÕİ ÇáÍí (äÙÇãí Ãæ ãÊÌÑ) ? ÇÍÊíÇØ ÇáãÎÒæä íõØÈŞ áãÑÉ æÇÍÏÉ ÚäÏ ÏÎæá İÑÚ ãÎÕÕ.
- **`OrderWorkflow::carrierStatusIds()`** (introduced ÈÇáãÑÍáÉ ÇáãÈßÑÉ) + `StoreOrderPermissions::forStatus($key, $storeId)`.
- **ÇáÊÑÌãÉ «ÇáãÕÏÑÇä ãÚğÇ»:** ãİÇÊíÍ status-kit ×4 ÊÚßÓ ãÚäì NOEST ÇáÍŞíŞí + ÊÓãíÇÊ ÚÑÈíÉ ÊõÈĞÑ İí `SystemStatusesSeeder` (label ÚÑÈí) æ`CarrierStatusDictionary` (`RAW_TO_STATUS` + `RAW_TO_MEANING`: 35 noest + 30 ecotrack + 36 yalidine¡ æ`list()` ÈãİÊÇÍ `meaning`).
- **ÊÈæíÈ ÇáÔÑßÉ — ÚãæÏ «ÇáãÚäì ÇáÍŞíŞí»:** `RAW_TO_MEANING` íõÚÑÖ ËÇáËğÇ ÈÌÇäÈ raw/ÇáÍÇáÉ ÇáãõØÈóøŞÉº ÃÍÏÇË NOEST ÇáÅÏÇÑíÉ ÇáÎãÓÉ ÈáÇ ãİÊÇÍ ÏÇÎáí áÇ ÊõÚÑÖ.
- **ÅÕáÇÍ bug ÓÇÈŞ ÇáæÌæÏ ááæÕæá áÓæíÊ ÃÎÖÑ:** `OrderConfirmationService` (`use App\Domains\Order\Support\OrderCompleteness` — ßáÇÓ ÛíÑ ãæÌæÏ) ? ÍõĞİ ÇáÇÓÊíÑÇÏ (ÇáßáÇÓ İí äİÓ Çáäíã ÓÈíÓ `App\Domains\Order\Services\OrderCompleteness`)º ßÇä íßÓÑ ÍÇæ ÇáÍá (BindingResolutionException) İí `submitConfirmAndSend`/`sendConfirmedOrder` æÈŞíÉ ãÓÇÑ ÇáÅÑÓÇá. íáãÓ äÌÇÍå: `SendGatewayCarrierAtomicTest` + `SendCarrierFailureTest`.

**ÇáÊÍŞŞ (ÇáÃÏáÉ):**
- `StoreStatusCustomizationTest` + `OrderTrackingStatusTest`: **22 äÇÌÍ (119 ÊÃßíÏğÇ)** (30.21s).
- `StatusCustomizationPageTest`: **7 äÇÌÍ (43 ÊÃßíÏğÇ)** — ÊÈæíÈ ÇáÔÑßÉ (ÚãæÏ ÇáãÚäì ÇáÚÑÈí ÚÈÑ `RAW_TO_MEANING`)¡ ÊÈæíÈ ÇáÑÇÌá (ÚÑÖ ŞÇÆãÊíä «ÊÎÕíÕ» æ«ÊÑÊíÈ» + ÒÑ ÅÖÇİÉ + ÍĞİ ÍÇáÉ ãÎÕÕÉ ÚÈÑ Çáãßæøä)¡ ÊÇÈ ÇáÊÃßíÏ (ÅÖÇİÉ ÍÇáÉ ãÎÕÕÉ ãÑÊÈØÉ + ÔÇÑÉ ÇáÑÈØ).
- `CustomStatusBranchTest` (ÌÏíÏ): **4 äÇÌÍ (18 ÊÃßíÏğÇ)** — ÙåæÑ ãİÇÊíÍ ÇáãÊÌÑ áÏì ÇáÜ Resolverº ÇáİÑÚ ÇáæÙíİí (canTransition?true¡ ÇäÊŞÇá Åáì Õİ ÇáãÊÌÑ¡ reserve 1× ÚäÏ 10?8¡ ÊÇÑíÎ ÇáÍÇáÉ ÈÇáÕİ ÇáãÎÕÕ¡ æÇáÎÑæÌ ÚÈÑ 'preparing' ÈáÇ ÍÑßÉ ÅÖÇİíÉ)º ÍÇáÉ ÇáÌáæÓ Úáì İÑÚ ÊÑË ÇäÊŞÇáÇÊ ÇáÃÕá (contains 'preparing'/'pending'/'cancelled')º ÇäÊŞÇá ÈãİÊÇÍ ãÌåæá ? `ModelNotFoundException`.
- **ÇáÓæíÊ ÇáßÇãáÉ: 829 äÇÌÍ (3342 ÊÃßíÏğÇ)** (685.58s) + `php -l` äÙíİ Úáì `OrderService` æ`OrderConfirmationService` + `view:cache` Óáíã. ŞÇãæÓ ÇáÚÒá: İÔáÇ `SendGatewayCarrierAtomicTest`/`SendCarrierFailureTest` ßÇäÇ ÈÓÈÈ ÇÓÊíÑÇÏ `OrderCompleteness` ÇáÎÇØÆ ÇáãõÓÌóøá ÃÚáÇå (áÇ ÚáÇŞÉ ÈÊÚÏíáÇÊ `OrderService`) — ÃËÈÊå ÊæŞİ ÇáÇÎÊÈÇÑíä (8/8) ÈÚÏ ÅÕáÇÍ ÇáÇÓÊíÑÇÏ æÍÏå.

### ÇáãÑÍáÉ 5 — ÈæÈ ÃÈ ÇáÊÊÈÚ: ÊÇÈÇä İÑÚíÇä «ÊÊÈÚ ÇáØáÈíÉ» / «ãáÇÍÙÇÊ ÔÑßÉ ÇáÊæÕíá» ?

**ÇáåÏİ:** ÏÇÎá `tracking-history-popup` ãÈÏøá ÊÈæíÈ İÑÚí:
- «ÊÊÈÚ ÇáØáÈíÉ»: ÇáÎØ ÇáÒãäí (stepper) + ÓÌá `statusHistory`.
- «ãáÇÍÙÇÊ ÔÑßÉ ÇáÊæÕíá»: ŞÇÆãÉ ãáÇÍÙÇÊ + ßÇÊÈ ãáÇÍÙÉ.
íÚãá ÊáŞÇÆíğÇ Úáì ßáÇ ÇáŞÓãíä (ßæã?? Ãí/ÑÇÌá) — ÇáÔÑßÉ ÚÈÑ API notes¡ ÇáÑÇÌá ÚÈÑ ÂáíÉ ãáÇÍÙÇÊ ãÍáíÉ ÌÏíÏÉ.

**ÇáÃÏáÉ ÇáãËÈÊÉ:**
- ÇáÈæÈ ÃÈ ÇáÍÇáí íßÏøÓ stepper + ÑÇÈØ ÇáÊÊÈÚ + composer + timeline ÚãæÏíğÇ (`tracking-history-popup.blade.php:26-42`) ÈáÇ ÊÇÈÇÊ.
- ÇáÍÇáÉ: `statusHistoryFor/statusHistory/statusHistoryMeta` + `shipmentNotes*` + `noteDraft/sendingNote` (index.blade.php:68-80) æÇáØÑŞ `openStatusHistory/openShipmentNotes/sendCarrierNote` (`TrackingDrawerConcern.php:139-348`). ÇáÈæÈ ÃÈ ãÔÊÑß Úáì ÇáÈØíäíä (index.blade.php:348).
- ãáÇÍÙÇÊ ÇáÑÇÌá **áÇ æÌæÏ áåÇ Çáíæã**: composer ãÍÌæÈ ÈÜ `carrier_supports_api_notes` (false ááÑÇÌáº `carrier-note-composer.blade.php:3-7`) æ`sendCarrierNote` íÍÑÓ ÈÜ supports_api_notes + non-null tracking_number (`TrackingDrawerConcern.php:279-301`). ÎáíÉ ÇáãáÇÍÙÇÊ İí ÇáÌÏæá ÚãæÏğÇ `—` ááÑÇÌá (`tracking-row-cell.blade.php:42-56`).
- ãİÇÊíÍ ÇáÊÑÌãÉ ÇáÍÇáíÉ: `carrier_note_section/carrier_note_status/carrier_notes/no_carrier_notes` (`order_flow.php:144-151`)º áÇ íæÌÏ ãİÊÇÍ ÍÑİí «ÊÊÈÚ ÇáØáÈíÉ»/«ãáÇÍÙÇÊ ÔÑßÉ ÇáÊæÕíá» — ÌÏíÏÉ.

**ÇáÊÚÏíáÇÊ ÇáãŞÊÑÍÉ:**
1. `tracking-history-popup.blade.php`: ÍÇáÉ `statusSubTab` ('tracking'|'notes') ÈÊÈÇíä İÆÇÊ pills (äãØ `tracking-tabs.blade.php:24-37`) ÈáÇ ÊæÌíå/ÍİÙ localStorage.
   - ÊÇÈ tracking: stepper + `tracking-history-timeline` (ßÇáÍÇáí).
   - ÊÇÈ notes: ŞÇÆãÉ `statusHistory` ÇáãõİáÊÑÉ ÈÜ `carrier_note` (ÕİÑ ÇÓÊÚáÇã ÅÖÇİí) + composer.
2. ãáÇÍÙÇÊ ÇáÑÇÌá (ÂáíÉ ÌÏíÏÉ): ÇáÓãÇÍ ÈßÊÇÈÉ `carrier_note` ãÍáíğÇ áÕİæİ ÇáÑÇÌá — ÊÍÏíË ÇáÜ gate İí `carrier-note-composer` (ÔÑßÉ ? supports_api_notesº ÑÇÌá ? íõÓãÍ ãÍáíğÇ) + `sendCarrierNote` íßÊÈ Õİ ÍÇáÉ ãÈÇÔÑÉğ ÚäÏ ÚÏã æÌæÏ provider-API (áÇ ÇÓÊÏÚÇÁ adapter). ÎáíÉ ÇáÌÏæá ááãáÇÍÙÇÊ ÊÙåÑ ááÑÇÌá.
3. ÇáÊÑÌãÉ ×4: `tracking_popup_tab_tracking`¡ `tracking_popup_tab_notes` (+ ÃÓãÇÁ åÇãÔíÉ Åä áÒã).

**ÇáÊÍŞŞ:** ÊÈÏíá ÇáÊÇÈíä ÈáÇ ÇÓÊÚáÇã ÅÖÇİí (äİÓ ÇáÕİÇÆİ)º ÈÚÏ ÅÑÓÇá ãáÇÍÙÉ İí Ãí ÊÇÈ íÊÍÏøË ÓÌá `statusHistory` æÓáæß ÇáÑÇÌá ÈáÇ APIº `view:cache`º ÌæáÉ ÇáÊÊÈÚ ÇáãÑÌÚíÉ ÕİÑ ÇäÍÏÇÑ.

**ÇáÇÚÊãÇÏíÇÊ:** ÇáãÑÍáÉ 3 (Ãí ãİÇÊíÍ ÊÊÈÚ ÌÏíÏÉ ÊæÓøÚ ÇáŞÇÆãÉ/ÇáÎØ ÇáÒãäí).

### ÇáãÑÍáÉ 6 — ÇáãÊİŞ Úáíå ÓÇÈŞğÇ: İáÊÑ ÍÇáÇÊ ÇáØáÈíÇÊ (ŞÈá ÇáÅÑÓÇá) + İÕá ÃÏæÇÑ STAFF + ÈæÇÈÉ ÇáÊÊÈÚ ?

**ÇáåÏİ (ŞÑÇÑÇÊ ÇáãÓÊÎÏã ÇáãÓÈŞÉ):** İáÊÑ ÍÇáÉ ÇáØáÈíÇÊ íÚÑÖ ÍÇáÇÊ ÇáÊÃßíÏ İŞØ (ÚÇã áßá ÇáÃÏæÇÑº `preparing` = ÈÚÏ ÇáÅÑÓÇá)º İÕá ÃÏæÇÑ STAFF (ãÄßöøÏ/ãÊÊÈöøÚ) ãÚ ÅÈŞÇÁ STAFF ÇáŞÏíã ááÊæÇİŞº ÈæÇÈÉ ÇáÊÊÈÚ `ORDER_VIEW || CRM_ORDER_TRACKING`º ÇáãäÍ ÇáãÎÕÕ áßá ãæÙİ íÈŞì ÚÈÑ `store_membership_permissions`.

**ÇáÃÏáÉ ÇáãËÈÊÉ:**
- `searchableStatuses` íõÈäì ãä ßá `type='order'` (`orders/index.blade.php:504-513`) æíÒæøÏ ÇáÈæÇÈÊíä (`filter-portal:112`, `orders-filter-bar-portal:363`).
- ŞÇÆãÉ ÊÛííÑ ÇáÍÇáÉ ÏÇÎá ÇáÕİ ãŞíÏÉ ÓáİğÇ ÈÜ `$transitions` (`orders-table-cell:811` ÚÈÑ `StoreOrderPermissions::forStatus` ÚäÏ 1732) — áÇ ÊõãóÓø.
- `StoreRoles::permissions()`: STAFF ÇáÍÇáí íÌãÚ ÇáÊÃßíÏ æÇáÊÊÈÚ (`StoreRoles.php:99-123`)º `CRM_ORDER_TRACKING` ÛíÑ ãÓÊÎÏã ßÈæÇÈÉ UI (íÙåÑ İŞØ İí StoreRoles æ`dashboard.blade.php:16`).
- ÇáÓÇíÏ ÈÇÑ íõÙåÑ ÇáÊÊÈÚ ÈäİÓ Úáã ÇáØáÈíÇÊ (`store-sidebar.blade.php:58/260-267`)º ÈæÇÈÉ ÕİÍÉ ÇáÊÊÈÚ `ORDER_VIEW` (`tracking/index.blade.php:222`).
- `StoreRoleEnum` (owner/admin/manager/staff) + æÇÌåÉ ÇáİÑíŞ ÊÚÑÖ ŞæÇÆã ÇáÃÏæÇÑ ÏíäÇãíßíğÇ (`teams/index.blade.php:299`) + ÔÇÑÉ ÇáÏæÑ `x-merchant.status domain="role"` (ÇáÓØÑ 427) — ÅÖÇİÉ ÇáÏæÑíä ÊÚÑÖÇä ÊáŞÇÆíğÇ ãÚ ãİÇÊíÍ labels İí status-kit/lang.

**ÇáÊÚÏíáÇÊ ÇáãŞÊÑÍÉ:**
1. `app/Support/StoreOrderPhases.php` (ÌÏíÏ): `PRE_CARRIER` (11) / `POST_CARRIER` (ÔÇãáÇğ preparing + ÇáãÊÈŞíÉ) + `isPreCarrier(string $key)` — ãÕÏÑ æÍíÏ ááİáÊÑ (íõãØøŞ ãÚ ÇáãÑÍáÉ 2).
2. `orders/index.blade.php:504-513`: `searchableStatuses` ? ÇáÅÈŞÇÁ Úáì `allStatuses` ßÇãáğÇ ááÜ override/ÇáÊÓãíÇÊ áßä İáÊÑÉ ÇáŞÇÆãÉ ÇáãİáÊÑÉ ÈÜ `isPreCarrier`º ÇáÈæÇÈÊÇä ÊÓÊåáßÇäå ÊáŞÇÆíğÇº ÈáÇ ãÓÇÓ ÈÇáÇÓÊÚáÇã æ`$transitions`.
3. `StoreRoleEnum`: + `STAFF_CONFIRMATION = 'staff_confirmation'` + `STAFF_TRACKING = 'staff_tracking'` (ÅÈŞÇÁ `STAFF` ÇáŞÏíã ßŞÇáÈ ÊæÇİŞ).
4. `StoreRoles::permissions()`:
   - `staff_confirmation`: ORDER_VIEW + ORDER_CONFIRM + ORDER_CANCEL + CRM_ORDER_CONFIRMATION + STATS_CONFIRMATION + PRODUCT_VIEW + INVENTORY_VIEW + RETURNS_VERIFY_BARCODE.
   - `staff_tracking`: ORDER_VIEW + CRM_ORDER_TRACKING + STATS_DELIVERY + INVENTORY_VIEW.
5. `store-sidebar.blade.php:58/260-267`: ÈæÇÈÉ ÑÈØ ÇáÊÊÈÚ = `canStore(ORDER_VIEW) || canStore(CRM_ORDER_TRACKING)`.
6. `tracking/index.blade.php:222`: ÇáÈŞÇÁ Úáì ORDER_VIEW (Ôãæá) — áÇ ÊÛííÑ ÅáÒÇãí ÅáøÇ Åä ÑõÈØÊ ÈÇáÏæÑ ÊÍÏíÏğÇ.
7. ÊÓãíÇÊ/ÃáæÇä ÇáÏæÑíä İí config status-kit (`role` group) + ÊÑÌãÇÊ `roles.*` ×4 — ÊÚÑÖÇä ÊáŞÇÆíğÇ İí ãäÓŞ ÇáİÑíŞ æÇáÔÇÑÉ.

**ÇáÊÍŞŞ:** `view:cache` + pest (ÇáãáİÇÊ ÇáãÊÃËÑÉ + ÇáÌæáÇÊ ÇáãÑÌÚíÉ: orders/tracking/teams) + ÅËÈÇÊ: İáÊÑ ÇáÍÇáÉ = 11 ÍÇáÉ İŞØ¡ æÈÏæä ÊÓÑíÈ ÍÇáÉ ÊæÕíáº ÈæÇÈÉ ÇáÊÊÈÚ ááÜ tracker İŞØº ÇáÜ staff ÇáŞÏíã íÈŞì ÔÇãáÇğº ÇÓÊÌÇÈÉ (ÇáÏæÑÇä + 375/768/1440) + ÊÍÏíË Todos.md (Ì8).

**ÇáÇÚÊãÇÏíÇÊ:** ÇáãÑÍáÉ 2 (ãØÇÈŞÉ ÇáŞÇÆãÉ ÇáÜ 11 ÚÈÑ `StoreOrderPhases`).

---
**ãáÇÍÙÇÊ ÚÇãÉ ááÚäŞæÏ:**
- ÈÚÏ ãæÇİŞÉ ÇáãÓÊÎÏã Úáì ãÑÍáÉ ÊõäİóøĞ ÈÇáÊÑÊíÈ İŞØ (áÇ ÏãÌ æáÇ ŞİÒ).
- ßá ãÑÍáÉ ÊõÍÏóøË ?? åäÇ İæÑ ÇáÇäÊåÇÁ ÈÜ (?) + ÃÏáÉ ÇáÊÍŞŞ + `php -l`/`view:cache`/ÇáÇÎÊÈÇÑÇÊ Ü ßãÇ İí ÇáÃŞÓÇã ÇáÓÇÈŞÉ.
- ŞÑÇÑÇä ãİÊæÍÇä íäÊÙÑÇä ÇáãÓÊÎÏã: (Ã) Ôßá ÇáãÓÇÑ (ÇáÚÇÏí Ãæ ÇáãÓÇÑÇÊ ÇáÚãíŞÉ ÇáŞÇÈáÉ ááÇÔÊÑÇß) İí ÇáãÑÍáÉ 1 — **Êã ÇáÈÊ: ãÓÇÑ æÇÍÏ `customization/statuses` (ÃäÙİ)**º (È) ãÚÇáÌÉ ZR Express/Anderson ÈáÇ æËÇÆŞ İí ÇáãÑÍáÉ 3 (fallback ãÄŞÊ Ãæ ãÕÏÑ ÎÇÑÌí).

---

## ÅÕáÇÍ ÓÑíÚ — ãÒÇãäÉ ÍÇáÇÊ NOEST (404 «Trackings non trouvés») ? (2026-09-16)

**ÇáÚÑÖ:** İí ÊÈæíÈ ÔÑßÇÊ ÇáÔÍä¡ «ãÒÇãäÉ ÇáÍÇáÇÊ» ÊäÊÌ ãÊßÑÑğÇ `local.ERROR NOEST trackings/info request failed (HTTP 404): Trackings non trouvés` æÊÊÑß ÇáÕİæİ ÚÇáŞÉ ÈÍÇáÉ `shipped` Åáì ÇáÃÈÏ. ÓÈÈå ÇáİÚáí: ÕİøÇä ãİÊæÍÇä (`YESH-28B-20317534`, `YESH-28B-20325652`) ÑŞãÇåãÇ áã íÚæÏÇ ãæÌæÏíä áÏì ÍÓÇÈ NOEST¡ æ`trackingsInfo` ßÇä íÑãí `RuntimeException` Úáì Ãí HTTP failure — ÈãÇ İíå 404 ÇáÏÇá Úáì «ÇáÑŞã ÛíÑ ãÚÑæİ» ÑÛã Ãä ÇáÚŞÏ ÇáãæËøŞ ÏÇÎá ÇáßæÏ äİÓå (`NoestIntegrationAdapter.php:261-265`) íäÕ Úáì Ãä ÇáÑŞã ÇáãÌåæá íõÛİá ãä ÇáÎÑíØÉ (404 áÇ íÙåÑ ÅáÇ ÚäÏ İÔá Çáßá).

**ÇáÊÚÏíáÇÊ ÇáãäİøĞÉ:**
1. `NoestIntegrationAdapter::trackingsInfo` — ÚäÏ HTTP 404 íÚíÏ `[]` (ÛíÑ ãÚÑæİ = ÛÇÆÈ) ÈÏá Ñãí ÇÓÊËäÇÁº ÈŞíÉ ÃÎØÇÁ HTTP ÊÈŞì ÑãíÉğ. áÇ ERROR spam ÈÚÏ ÇáÂäº `syncOne` ÊÑÌÚ `no_data` ÈÕãÊ¡ æÇáãåãÉ ÊÌÇæÒåÇ ÈÕãÊ (ãÓÇÑ `touchSyncedAt`).
2. `TrackingDrawerConcern::syncAllTracking` — ÕÇÑ íÌãøÚ ÍÓÈ `shipping_provider_id` æíÑÓá ÏİÚÇÊ ãä 20 (ØáÈ æÇÍÏ ÈÏá ØáÈ áßá Õİ)¡ æíÍÓÈ `done/failed/missing`º æÕøÇá ÊæÓÊ ãÎÕÕ «ÑŞã ÇáÊÊÈÚ ÛíÑ ãæÌæÏ áÏì ÔÑßÉ ÇáÔÍä» ÚäÏãÇ Êßæä ßá ÇáÅÎİÇŞÇÊ ÃÑŞÇãğÇ ãÌåæáÉ.
3. `syncTracking` (ÊÍÏíË ÇáÂä İí ÇáÏÑÌ) — íãíøÒ `no_data` ÈÑÓÇáÉ ãÎÕÕÉ ÈÏá «ÊÚĞøÑ ÇáÊÍÏíË» ÇáÚÇã.
4. ÊÑÌãÉ ×4: `order_flow.tracking_unknown_carrier`.
5. ÇÎÊÈÇÑÇä ÌÏíÏÇä: (Ã) `trackingsInfo` íÑÏ Úáì 404 ÈÜ `[]`º (È) `syncOne` íÑÌÚ `no_data` ÈáÇ áãÓ ÇáÕİ.

**ÇáÔåÇÏÉ:** pest `NoestTrackingSyncTest` + `NoestTrackingSyncServiceTest` + `TrackingTrashWebhookLabelTest` = 31 pass (131 assertions). `php -l` äÙíİ.

**ãÊÈŞò (ŞÑÇÑ ÏÇÊÇ ááãÓÊÎÏã¡ ÎÇÑÌ ÇáßæÏ):** ãÕíÑ ÇáÕİøíä `YESH-28B-20317534` / `YESH-28B-20325652` — ÅÚÇÏÉ ÊÍŞøŞ ãä ÇáÊæßíä/ÇáÍÓÇÈ (ÇÍÊãÇá ÅÚÇÏÉ ÑÈØ)¡ Ãæ ÅÛáÇŞåãÇ íÏæíğÇ¡ Ãæ ÊÑßåãÇ (ÓíÓÊãÑÇä ÈÇáİÔá ÇáÕÇãÊ). ßá ãÒæøÏí `noest` (ÕİøÇä äÔØÇä ÈÇÓãí «Noest»/«NOEST») áÏíåãÇ `webhook_token` — áĞÇ ÇáãÒÇãäÉ åäÇ íÏæíÉ İŞØ ÚÈÑ ÇáÊÈæíÈ¡ æÇáÜ Poll ÇáãÌÏæá íÌÊÇÒåãÇ ÃÕáÇğ (`webhook_token != null`).

**ÅÖÇİÉ (2026-09-16¡ äİÓ ÇáÌáÓÉ):** ãÚ ÑÓÇáÉ «ÛíÑ ãæÌæÏ áÏì ÇáÔÑßÉ» ÊõÚÑÖ ÇáÂä **ÃÑŞÇã ÇáØáÈíÇÊ ÇáãÊÃËÑÉ** áíÊÚÇãá ãÚåÇ ÇáÊÇÌÑ:
- `syncAllTracking` íÌãÚ ÇáÕİæİ ÇáãÌåæáÉ æíÈËø `html` İí ÇáÜ `swal:toast` ÈŞÇÆãÉ «ØáÈ #:order — ÇáÊÊÈÚ: :tracking» (ßá ŞíãÉ ãÚÒæáÉ ÈÜ `<bdi>` æãåÑøÈÉ ÈÜ `e()`)¡ æÊÙåÑ ÚäÏ ÍÕÑíÉ ÇáãÌåæá Ãæ İí ÇáÎáØ (ÈÌÇäÈ ÇáÚÏÇÏ)º ÍÏ 25 ÓØÑğÇ Ëã «ÈÇáÅÖÇİÉ Åáì :count ØáÈíÉ ÃÎÑì».
- `syncTracking` (ÊÍÏíË ÇáÂä) íÚÑÖ äİÓ ÇáŞÇÆãÉ ááØáÈ ÇáæÇÍÏ.
- ÇáÊÑÌãÉ ×4: `tracking_unknown_item` + `tracking_unknown_more`.
- ÇÎÊÈÇÑÇä ÌÏíÏÇä İí `TrackingTrashWebhookLabelTest`: áÇ íÚæÏÇ íÊãÑÑ ÅáÇ ÈæÌæÏ ÑŞã ÇáØáÈíÉ + ÑŞã ÇáÊÊÈÚ İí `html`.
- ÔåÇÏÉ: 25 pass (107 assertions) İí ãáİøí `TrackingTrashWebhookLabelTest` + `NoestTrackingSyncServiceTest`º `php -l` äÙíİ.

## ÅÕáÇÍ ÓÑíÚ — «ÅáÛÇÁ ÇáÅÑÓÇá» áÔÍäÉ ÔÑßÉ ÊæÕíá áÇ ÊÚÑİåÇ (422 «The given data was invalid.») ? (2026-09-16)

**ÇáÚÑÖ:** ÇáØáÈíÇÊ ÇáÓáíãÉ áÏíäÇ áßä ÑŞãåÇ ÛíÑ ãæÌæÏ áÏì ÔÑßÉ ÇáÊæÕíá (áã ÊõäÔÃ åäÇß¡ Ãæ ÍõĞİÊ ÎÇÑÌíğÇ) ÊÑİÖ ÇáÅáÛÇÁ ÚäÏ ÇáÔÑßÉ ÈÑÓÇáÉ Laravel ÇáÎÇã ãä NOEST «The given data was invalid.»¡ İÊÚáŞ ÚãáíÉ ÇáÅáÛÇÁ ÇáãÍáí äåÇÆíğÇ.

**ÇáÍá ÇáãÚÊãÏ (ÇáãÓÇíÑÉ ÈãäØŞ ËŞÉ):**
1. **ÚãæÏ ÌÏíÏ `order_trackings.carrier_unknown_at`** (migration `2026_09_16_000001…`): íÎÒøä ÍŞíŞÉ «ÇáÔÑßÉ áÇ ÊÚÑİ ÇáÑŞã» ÇáãËÈÊÉ ãä ÇáãÒÇãäÉ (áÇ ÊÎãíäğÇ ãä äÕ ÇáÎØÃ):
   - `NoestTrackingSyncService::syncOne` íÖÈØå ÚäÏ `no_data`º `apply()` íÓÍÈå ÚäÏ Ãí äÌÇÍ.
   - `SyncNoestTrackingJob` íÖÈØå ááÕİæİ ÇáÛÇÆÈÉ ãä ÎÑíØÉ NOEST.
2. **`NoestIntegrationAdapter::deleteOrder`** íÕÏøÑ `not_found` (ãØÇÈŞÉ äÕæÕ ãÚÑæİÉ: «The given data was invalid.»/introuvable/not found/… + İÍÕ `errors`) ãŞÇÈá ÈŞíÉ ÇáÃÎØÇÁ (ÔÈßÉ/ÇÚÊãÇÏ) ÈáÇ ÊÕäíİ.
3. **`OrderShippingGateway::cancel`**: ÅĞÇ ßÇä `carrier_unknown_at` ãÖÈæØğÇ **Ãæ** ÃÈáÛ ÇáÃÏÇÈÊæÑ `not_found` ? áÇ ÔíÁ ááÍĞİ áÏì ÇáÔÑßÉ: íõßãá ÇáÅáÛÇÁ ÇáãÍáí (İÕá ÑŞã ÇáÊÊÈÚ + ÇáÚæÏÉ Åáì confirmed) ãÚ ÓØÑ ÊÇÑíÎ æ`payload.carrier_not_found_at_cancel=true` æLOG¡ æíÚíÏ `notice=carrier_unknown`. Ãí İÔá ÂÎÑ íÈŞì ÍÇÌÒğÇ (áÇ ÅáÛÇÁ ãÍáí ÒÇÆİ áÔÍäÉ ŞÏ Êßæä ÍíøÉ áÏì ÇáÔÑßÉ).
4. ÊæÓÊ ããíÒ `shipment_cancelled_unknown_carrier` ×4 İí `TrackingDrawerConcern` æ`CancelsShipmentFromOrdersTable`.

**ÇáÔåÇÏÉ:** ãáİ ÇÎÊÈÇÑ ÌÏíÏ `tests/Feature/Shipping/ShipmentCancelCarrierUnknownTest.php` (3 ÇÎÊÈÇÑÇÊ: ãõÚáóøãÉ/ÅÈáÇÛ not_found ? ãÖí ãÍáíº İÔá ÚÇã ? ÍÌÈ) + ÇÎÊÈÇÑÇ ÇáãÒÇãäÉ (stamp/clear İí ÇáÇÊÌÇåíä¡ Job) + ÇÎÊÈÇÑÇ `deleteOrder`. pest: 24 pass (101) İí ãáİÇÊ ÇáÔÍä ÇáËáÇËÉ + ÚÏã ÇäÍÏÇÑ: `TrackingTrashWebhookLabelTest` (16)¡ `OrderCarrierValidationDispatchTest`+`SendGatewayCarrierAtomicTest` (16). migrate + `php -l` + `view:cache` ÓÇáãÉ.

---

## ÅÕáÇÍ İÑÇÛ ÕİÍÉ ÇáÊÊÈÚ — ÇáİÕá ÇáãäØŞí Èíä ÍÇáÇÊ «ÇáØáÈíÉ» æ«ÇáÊÊÈÚ» (ÚÏã ÇáÊßÑÇÑ) ? (2026-09-17)

**ÇáÚÑÖ:** ÊÈæíÈÇ ÕİÍÉ ÇáÊÊÈÚ (`merchant.tracking.index`) áÇ íÚÑÖÇä Ãí Õİæİ ÅØáÇŞğÇ. ÇáÓÈÈ ÇáÌĞÑí (ÈÊÔÎíÕ Íí): ÇáßæãíÊ `8e121b3` ÛíøÑ İí `TrackingGridConcern.php:179` İáÊÑÉ `orders.status_id` ãä `Status::system()->forType('order')` Åáì `forType('tracking')` ÚäÏ ãİÇÊíÍ `OrderWorkflow::carrier()`.

**ÇáÎáØ ÇáÊÕäíİí (Íáøå ÇáŞÑÇÑ ÇáãÚãÇÑí):** áßá ãİÊÇÍ ãËá `shipped` ÕİøÇä ãÎÊáİÇ ULID: æÇÍÏ `type='order'` (ÍÇáÉ ÇáØáÈíÉ) æÂÎÑ `type='tracking'` (ÍÇáÉ ÇáÔÍäÉ áÏì ÇáÔÑßÉ). `orders.status_id` íÔíÑ ÏÇÆãğÇ áäæÚ `order` — İáÊÑÉ ÈäæÚ `tracking` áÇ ÊØÇÈŞ Ãí ØáÈ ? ÕİÑ Õİ İí ÇáÊÈæíÈíä. ÇáŞÑÇÑ ÇáãÚÊãÏ: **ÇáİÕá ÇáãäØŞí ÇáÕÍíÍ ãÚ ÚÏã ÇáÊßÑÇÑ** — ÇáÊÌÇÑÈ ÇáËáÇËÉ áã ÊÚÏ ãÕÇÏÑ ãßÑøÑÉ íÏæíÉ Èá:
- **`orders.status_id` (äæÚ order)** = ÍÇáÉ ÇáØáÈíÉ¡ æãäåÇ ÊãÑø ÊÓáíãÇÊ ÇáÔÑßÉ æÇáÑÇÌá ãÚğÇ (`OrderShippingGateway` íäÊŞá Åáì `shipped` ÚÈÑ `OrderService::transition` ÇáĞí íŞÑÃ `forType('order')` ÏÇÆãğÇ) ? åí ÇáÊí ÊŞÑÑ ÙåæÑ ÇáØáÈíÉ İí ÕİÍÉ ÇáÊÊÈÚ.
- **`order_trackings.tracking_status` (äæÚ tracking)** = ÍÇáÉ ÇáÔÍäÉ áÏì ÇáÔÑßÉ (ÃæÓÚ: on_hold/returning/failed_attempt/lost/damaged…).
- **ÇáÊÃßíÏ (äæÚ order¡ ŞÇÆãÉ `StoreOrderPhases`/Confirmation Pipeline)** = ãÑÍáÉ ãÇ ŞÈá ÇáÅÑÓÇá.

**ãÇ äõİöøĞ:**
1. `database/seeders/SystemStatusesSeeder.php` — ÍõĞİÊ ßÊáÉ TRACKING ÇáíÏæíÉ (11 ÕİğÇ) æÃÕÈÍÊ ÊõÈäì ãä `OrderTrackingStatus::cases()` (æÍíÏ ÇáãÕÏÑ) ãÚ **İÍÕóí ÇÊÓÇŞ íÑãí `RuntimeException`**: (Ã) ãİÇÊíÍ tracking ÇáãÒÑæÚÉ == ÍÇáÇÊ enumº (È) ãİÇÊíÍ ßá ãÌãæÚÉ `OrderWorkflow::backOffice()/carrier()/closed()` ? ãİÇÊíÍ Õİæİ type=order.
2. `app/Domains/Order/Support/OrderWorkflow.php` — **äŞØÉ ÏÎæá ãæÍøÏÉ ÌÏíÏÉ `carrierStatusIds()`**: «ãİÇÊíÍ `carrier()` ? ãÚÑøİÇÊ `type='order'`». åí ÇáæÍíÏÉ ÇáãÓãæÍÉ áİáÊÑÉ `orders.status_id` İí ÕİÍÉ ÇáÊÊÈÚ.
3. `app/Livewire/Concerns/TrackingGridConcern.php:179` — íÓÊÏÚí `OrderWorkflow::carrierStatusIds()` ÈÏá ÇáÇÓÊÚáÇã ÇáãÖãøä¡ ãÚ ÊÚáíŞ ÊæËíŞ ÇáİÕá ÇáËáÇËí.
4. `tests/Feature/Merchant/TrackingGridStatusScopeTest.php` (ÌÏíÏ) — 3 ÇÎÊÈÇÑÇÊ: ÙåæÑ ØáÈ ÔÑßÉ İí ÊÈæíÈ carrierº ÙåæÑ ØáÈ ãæÕöøá İí ÊÈæíÈ riderº «ÇáÊÑßíÒ»: `carrierStatusIds()` áÇ ÊÊŞÇØÚ ÃÈÏğÇ ãÚ ãÚÑøİÇÊ äæÚ tracking æáÇ íØÇÈŞåÇ Ãí `orders.status_id`.

**ÇáÃÏáÉ/ÇáÔåÇÏÉ:** ÈĞÑ Íí äÇÌÍ ÈáÇ ÇÓÊËäÇÁ (Õİæİ tracking ÇáÜ 11 ãØÇÈŞÉ áÜ enum label/color/ÊÑÊíÈ)º pest: **52/52** İí ãÌãæÚÉ ÇáÊÊÈÚ (`TrackingGridStatusScopeTest` 3 + `TrackingSearchFilterTest` 33 — ßÇäÊ 18 İÇÔáğÇ + `TrackingTrashWebhookLabelTest` 16 — ßÇä 1 İÇÔáğÇ) æ**24/24** İí ÓæíÊÇÊ ÇáÔÍä (`ShipmentCancelCarrierUnknownTest` + `NoestTrackingSyncTest` + `NoestTrackingSyncServiceTest`) — ÕİÑ ÇäÍÏÇÑ. `php -l` äÙíİ ×4.

> ÇáŞÇÚÏÉ áãä íÚÏøá áÇÍŞğÇ: áÇ íõÓÊÚãá `forType('tracking')` áİáÊÑÉ `orders.status_id` ÅØáÇŞğÇº ãÑøÑ ÈİáÇÊÑ ÕİÍÉ ÇáÊÊÈÚ ÚÈÑ `OrderWorkflow::carrierStatusIds()` İŞØ.
>
> ãÑÊÈØ ÈÇáÚäŞæÏ ÃÚáÇå: åĞå ÇáÍÇáÇÊ (11) ÊÎÏã ÇáãÑÍáÉ 4 (ÑÇÌá/ãÊÌÑ¡ unittest-ÇáãÑÍáÉ 2) æÇáãÑÍáÉ 6 (İáÊÑ ÇáÊÃßíÏ) — äŞØÉ ÇáÏÎæá ÇáãæÍøÏÉ ÊÈŞì ãÕÏÑ ÇáÍŞíŞÉ æáÇ íäÈÛí ÊßÑÇÑ ŞæÇÆã ÇáãİÇÊíÍ ÇáËÇÈÊÉ İí Ãí ÕİÍÉ ÌÏíÏÉ.

---

## ÅÕáÇÍ ËáÇËí — store_scope_id (SQLSTATE 1265) + ÍÇáÉ ÊÊÈÚ ÇáÑÇÌá ÇáİÇÑÛÉ + «áÇ ÊæÌÏ ÔÍäÇÊ ŞÇÈáÉ ááãÒÇãäÉ» ? (2026-09-17)

**ÇáÚÑÖ (3 ÊŞÇÑíÑ ãÊÑÇÈØÉ):** (1) `SQLSTATE[01000] … Data truncated for column 'store_scope_id'` ÚäÏ ÅÖÇİÉ/ÊÎÕíÕ/ÊÚÏíá ÍÇáÉº (2) ÚãæÏ «ÇáÍÇáÉ» İí ÌÏæá ÊÊÈÚ ÇáØáÈíÇÊ (ÊÈæíÈÇ ÇáÔÑßÉ æÇáÑÇÌá) İÇÑÛº (3) «ãÒÇãäÉ Çáßá» ÊŞæá «áÇ ÊæÌÏ ÔÍäÇÊ ŞÇÈáÉ ááãÒÇãäÉ ãÚ ÔÑßÇÊ ÇáÊæÕíá» ÑÛã æÌæÏ ÔÍäÉ ÈãõÚÑöøİ ÊÊÈÚ İÚáí. Êã ÇáÊÔÎíÕ ÍíğøÇ Úáì ŞÇÚÏÉ ÈíÇäÇÊ ÇáãÊÌÑ ÇáÍÇáíÉ (æÈãæÇİŞÉ ÇáãÓÊÎÏã ÇáÕÑíÍÉ «äİøĞ åÌÑÉ ÇáÅÕáÇÍ»/«ÇİÍÕ æÃÕáÍ»).

**ÇáÃÓÈÇÈ ÇáÌĞÑíÉ (ËáÇËÉ ãÓÊŞáÉ):**
1. **`store_scope_id` ÚÕÑíğÇ ÎÇØÆ:** ÇáÚãæÏ ãæáøÏ `unsignedBigInteger` ÈÇáÕíÛÉ `IFNULL(store_id, 0)` ÈíäãÇ `store_id` åæ ULID (char 26). MySQL íŞØøÚ Ãí ULID ÚäÏ ÇáÊÍæíá ÇáÚÏÏí (ÃËÈÊ ÍíğøÇ: `CAST(IFNULL('01m2qnh10v7w009ffhz1h7qbtn',0) AS UNSIGNED)` = `1`) ? ßá ÇáãÊÇÌÑ ÊÊÕÇÏã İí scope=1¡ æãÚ ßÓÑ strict íßÊÈ `1` (truncated) Ãæ íÑİÖ ÇáÕİ (1265).
2. **`OrderTracking::tracking_status` İÇÑÛÉ Úáì ãÓÇÑ ÇáÑÇÌá:** `ensureRiderTracking` ßÇä íãáÃ `tracking_number` İŞØ Úáì Õİ ãİÊæÍ ãæÌæÏ æáÇ íÖÈØ ÇáÍÇáÉ ÃÈÏğÇ ? ÇáÔÈßÉ ÊÚÑÖ «—». (ÈíÇäÇÊ ÍíÉ: ØáÈ 00002 ÑÇÌá ÑÊÈÉ ÊÊÈÚ `tracking_status=NULL, carrier_status='cancelled'` æãÒæøÏ ŞÏíã áã íõÓÍÈ.)
3. **`OrderTrackingStatus::open()` ÊäŞÕ `failed_attempt`:** `syncAllTracking` íİáÊÑ `whereIn('tracking_status', open())` æåĞå ÇáÍÇáÉ (ÛíÑ äåÇÆíÉ — `isOpen()` ÊõÑÌÚ true áåÇ) ßÇäÊ ãÓÊËäÇÉ ? ÇáÔÍäÉ ÇáæÍíÏÉ ÈãõÚÑöøİ ÊÊÈÚ İÚáí (`YESH-28B-20573449`, `failed_attempt`) ãÓÊÈÚÏÉ ãä ÇáãÒÇãäÉ ÏÇÆãğÇ.

**ãÇ äõİøĞ:**
1. **åÌÑÉ ÌÏíÏÉ `2026_09_17_163636_fix_statuses_store_scope_id_ulid_scope.php`:** `store_scope_id` íõÚÇÏ ÅäÔÇÄå ßÜ `string(26)` ãæáøÏ = `IFNULL(store_id, '0')` (ÓäÊíäÇá: `'0'` ááäÙÇã¡ ULID ááãÊÌÑ) ãÚ ÅÚÇÏÉ ÈäÇÁ `unique(statuses_scope_type_key_unique)`º `down()` íÚßÓ (`unsignedBigInteger`). ÃõÕáÍ ßĞáß ãÑÌÚÇ Filament SuperAdmin: `StatusForm::store_scope_id` ÈáÇ `->numeric()` (ÚãæÏ ãæáøÏ íõŞÑÃ İŞØ ? `->disabled()`)¡ `StatusesTable::store_scope_id` ÈáÇ `->numeric()->sortable()` İŞØ.
2. **`OrderTrackingService::ensureRiderTracking`:** Úáì Õİ ãİÊæÍ ãæÌæÏ — íãáÃ `tracking_number`¡ æíáİø `tracking_status='shipped'` Åä ßÇäÊ İÇÑÛÉ (ãÚ ÓØÑ History `rider_backfill`)¡ æíãÓÍ `shipping_provider_id` ÇáŞÏíã (ãÓÇÑ ÇáÑÇÌá áÇ íõÈËø ÃÈÏğÇº ÅÈŞÇÄå ßÇä ÓíõÏÎá ÑŞã HM/SD ÇáãÍáí İí ãÒÇãäÉ ÇáÔÑßÉ). Idempotent.
3. **`OrderTrackingStatus::open()`:** ÃõÖíİ `FAILED_ATTEMPT` (ÇáŞÇÆãÉ ÕÇÑÊ ãØÇÈŞÉ ÊãÇãğÇ ááÍÇáÇÊ ÛíÑ ÇáäåÇÆíÉ — ÇÎÊÈÇÑ íËÈÊ ÇáÊØÇÈŞ áßá ÍÇáÉ). ÇáÊÚÈíÑ `::open()` áÇ íõÓÊÎÏã ÅáÇ İí `TrackingDrawerConcern::syncAllTracking:272` İáĞáß áÇ ÃËÑ ÌÇäÈí.

**ÇáÔåÇÏÉ (Úáì ŞÇÚÏÉ ÈíÇäÇÊ ÍíÉ áãÊÌÑ ÍŞíŞí):**
- `php artisan migrate` (forward + rollback/re-migrate) äÙíİº `SHOW CREATE TABLE statuses` íÙåÑ `varchar(26) GENERATED ALWAYS AS (ifnull(store_id,'0'))`.
- `StoreStatusService::addStatus` + `riderSaveLabel` (override ÈáÇ ãÓÇÓ ÈÊÑÌãÉ kit) + `moveRider` + `riderList(count=12)` + `deleteStatus` Úáì ÇáãÊÌÑ ÇáÍí ÈäÌÇÍ — áÇ `SQLSTATE[01000]` (ßÇä íİÔá ŞÈá ÇáåÌÑÉ).
- ÅÕáÇÍ Íí ááØáÈ 00002: `ensureRiderTracking` ãáÃ `SD-EE9LLDZN` + `shipped` æãÓÍ ÇáãÒæøÏ ÇáŞÏíãº æÍíÏ ÇáãÕÏÑ ÈÚÏ ÊßÑÇÑ ÇáÇÓÊÏÚÇÁ (áã íõáãÓ `SD-...`/`shipped`).
- ãÒÇãäÉ Çáßá: `open()` ÇáÍÇáíÉ = `shipped,in_transit,out_for_delivery,on_hold,failed_attempt,returning` æÇáÇÓÊÚáÇã ÇáÍí íáÊŞØ ÇáÔÍäÉ `YESH-28B-20573449/failed_attempt` (ßÇäÊ ÕİÑğÇ ŞÈáå).
- pest: `OrderTrackingStatusTest` (6/6 — ÇáÌÏíÏ: ÊØÇÈŞ open() ãÚ isOpen áßá ÍÇáÉ) + `OrderCompletenessTest` (25/25 — ÇáÌÏíÏÇä: backfill ÇáÍÇáÉ + ÅÈŞÇÁ ÍÇáÉ ãæÌæÏÉ + ãÓÍ ÇáãÒæøÏ¡ ãÚ ÇáÇÊøÓÇŞíÉ: áÇ ÇÓÊÈÏÇá ÑŞã/ÍÇáÉ ãæÌæÏÉ) + ÊÊÈÚ ÇáãÑÇÌÚÉ (`OrderTrackingTest`, `TrackingGrid*`, `TrackingStatusHistoryPopupTest`, `NoestTrackingSyncService*`) 45/45 + **Shipping ßÇãá 60/60 (280)** + **Merchant Order* 337/337 (1310)** + **Merchant Tracking* 117/117 (575)** + ÓæíÊÇÊ ÇáÍÇáÇÊ (`StatusLabelPrecedence`, `StatusResolverDomain`, `CustomStatusBranch`, `StatusCustomizationPage`, `StoreStatusCustomization`) 34/34. `php -l` äÙíİ ×8 + rollback/re-migrate äÙíİ.
- ÊæËíŞ: `DATABASE_PLAN_Schema.md` æ`DATABASE_PLAN_MIGRATIONS.md` ÍõÏøËÇ ááäæÚ ÇáäÕøí ÇáÌÏíÏ ãÚ ÓÈÈ ÇáÊÛííÑ.

---

## ÅÕáÇÍ ÓáÈí ÒÇÆİ «áÇ ÔÍäÇÊ ááãÒÇãäÉ» — ãÒÇãäÉ Çáßá ãÚ `tracking_status = NULL` ? (2026-09-17)

**ãÑÌÚ ÇáãÑÍáÉ:** Phase "Fix No shipments to sync False Negative (Bulk Sync Filter)" — ÖÏ commit `47166ac9`.

**ÇáÊÔÎíÕ:** ÇáÔÍäÉ ÇáÍŞíŞíÉ `YESH-28B-20576482` ÛíÑ ãæÌæÏÉ İí ŞÇÚÏÉ ÇáÊØæíÑ ÇáãÍáíÉ (ÈíÇäÇÊ ÅäÊÇÌ İŞØ) — áÇ íãßä ÊŞÑíÑ Ãí İÑÖíÉ ãä ÇáËáÇË Úáì ÇáÈíÇäÇÊ ÇáÍŞíŞíÉ åäÇº ÇáäåÌ ÇáãØÈóøŞ åæ ÇáİÑÖíÉ 2-ÇáÃæáì (Õİ ÈÔÑßÉ æÑŞã ÕÇáÍíä æÍÇáÉ NULL ããÓæÍÉ ÈãÓÇÑ ÎÇÑÌ `startShipment()` — ãËá ãÓÇÑ ÇáÅáÛÇÁ Ëã ÅÚÇÏÉ ÇáÅÑÓÇá). İÍÕ ÇáäØÇŞ: `OrderTrackingStatus::open()` áÇ íõÓÊÚãá ÅáÇ İí `syncAllTracking()` (grep ßÇãá İí `app/`) — ÈáÇ ÃËÑ ÌÇäÈí áÊæÓíÚ åĞÇ ÇáÇÓÊÚáÇã æÍÏåº ÈŞí ÇÓÊÚáÇã İáÊÑ ÇáÔÈßÉ İí `TrackingGridConcern:107` (ÍÓÇÓíÉ İáÊÑ¡ áÇ ÕáÉ).

**ãÇ äõİøĞ (ßÇä ÌÒÆíğÇ İí ÇáÔÌÑÉ ÛíÑ ÇáãáÊÒãÉ¡ Ãõßãá æÃõËÈÊ):**
1. **`app/Livewire/Concerns/TrackingDrawerConcern.php` (syncAllTracking):** ÇáÇÓÊÚáÇã ÃÕÈÍ `whereIn(tracking_status, open()) OR whereNull(tracking_status)` — äİÓ ÊÚÑíİ «synable» áÜ `SyncNoestTrackingJob::dueTrackings()` ßí áÇ íÎÊáİ ÇáÓÍÈ ÇáíÏæí ÃÈÏğÇ Úä ÇáãåÇã ÇáãÌÏæáÉ. áã íõáãÓ `OrderTrackingStatus::open()/terminal()` æáÇ İáÊÑ `webhook_token` İí ÇáãåãÉ (ÈÇáÊÕãíã).
2. **ÇáÚÑÖ:** `tracking-row-cell.blade.php` + `tracking-mobile-card.blade.php` — ÚäÏãÇ íßæä `tracking_status` İÇÑÛğÇ æáå ÑŞã ÊÊÈÚ ? ÔÇÑÉ «áã ÊõÒÇãóä ÈÚÏ — ÇáÍÇáÉ ÛíÑ ãÚÑæİÉ» (`order_flow.tracking_status_unknown` ×4 áÛÇÊ) ÈÏá ÇáÎáíÉ ÇáİÇÑÛÉ.
3. **äŞØÉ ÈãİÑÏåÇ:** Pint Úáì Çáãáİíä (ßÇäÊ ÚáíåãÇ ãÎÇáİÊÇ ÃÓáæÈ).
4. **ÇÎÊÈÇÑ ÇáÇäÍÏÇÑ:** `tests/Feature/Shipping/SyncAllTrackingNullStatusTest.php` (ãáİ ÌÏíÏ¡ 2):
   - Õİ `tracking_status = NULL` + ãÒæøÏ ÕÇáÍ + ÑŞã ? íÙåÑ İí `syncAllTracking()` æíõÍá Åáì `DELIVERED` ãÚ `delivered_at`/`last_synced_at`/History.
   - ÕİÇä (NULL + `in_transit`) íõÓÍÈÇä ãÚğÇ İí ÊÔÛíá æÇÍÏ — ÇáÑŞã ÇáÍŞíŞí `YESH-28B-20576482` ãÓÊÎÏã ÍÑİíğÇ İí ßáÇ ÇáÇÎÊÈÇÑíä (ÇáÊÍŞŞ ÇáŞíÇÓí áãÚíÇÑ ÇáŞÈæá ÈÏá ÇáæÕæá Åáì PPROD).

**ÇáÔåÇÏÉ:** `SyncAllTrackingNullStatusTest` 2/2 (9 ÊÃßíÏÇÊ) + `TrackingTrashWebhookLabelTest` 22/22 (82 ÊÃßíÏğÇ) ÎÖÑÇÁ. ÇáÓæíÊ ÇáßÇãáÉ 839 äÇÌÍ (3417 ÊÃßíÏğÇ) — ÇáİÔá ÇáËáÇËÉ **ÓÇÈŞÉ ÇáÊÃÓíÓ æÛíÑ ãÑÊÈØÉ** ÈåÇ: ÇËäÇä Şİá ãáİÇÊ Windows İí `BulkDispatchValidateTest` (`rename storage/framework/views … Access is denied` — ãÚÑæİ æãÄÑóøÎ)¡ ææÇÍÏ İí `CarrierSyncObservabilityTest` (ãÔÑæÚ ãÑÇŞÈÉ ÛíÑ ãáÊÒã¡ `expectsOutputToContain('8')` áÇ íØÇÈŞ ÌÏæá ÇáÜ artisan command). `php -l` äÙíİ ×4¡ Pint äÙíİ¡ ÇáÌæáÉ ÇáßÇãáÉ ÇáÓÇÈŞÉ (4 ÃÎØÇÁ ãÊŞáøÈÉ) ÊÄßÏ äİÓ ÇáäãØ. ÇáÊäÈíå: áÇ ÊæÌÏ ŞÇÚÏÉ ÈíÇäÇÊ ÅäÊÇÌ/Staging ãÍáíÉ áÅËÈÇÊ YESH-28B-20576482 ÈÚíäåÇ — ÇáÅËÈÇÊ ÇáŞíÇÓí ÚÈÑ ÑŞã ÇáÊÊÈÚ ÇáÍÑİí ÏÇÎá ÇáÇÎÊÈÇÑº Åä Ããßä ÇáæÕæá Åáì äÓÎÉ ãä ÇáÅäÊÇÌ¡ İÇáÊÃßíÏ ÇáäåÇÆí ÇÓÊÚáÇã ÇáÓØÑ (1) İí ÎØÉ ÇáãÑÍáÉ.

---

## ÅÕáÇÍ ÓáÓáÉ ÇáÏİÚ (ÇáÒÇÆÑ): hooks `updated` ÇáãÚØæÈÉ + ÊÖãíä ŞæÇÆã ÇáÈáÏíÇÊ/ÇáãßÇÊÈ ÈÏá ÇáÌáÈ ÇáßÓæá ? (2026-09-17)

**ØáÈ ÇáãÓÊÎÏã:** 4 ÃÎØÇÁ ÈÇáÏİÚ: (1) ÊæÕíá ááãäÒá áÇ ÊõÌáÈ ÇáÈáÏíÇÊ æíæÌÏ ÈØÁº (2) æáÇíÉ ĞÇÊ ãßÊÈ æÇÍÏ ÊÑİÖ ÇáØáÈ ÈÜ"ÇÎÊÑ ãßÊÈ ÇáÇÓÊáÇã"º (3) ØáÈÇÊ ÇáãßÊÈ ÊÏÎá ÈáÇ ÇáÈáÏíÉº (4) áÇ íÎÊÈÆ ãäÊŞí ÔÑßÉ ÇáÊæÕíá ÚäÏ ÔÑßÉ æÇÍÏÉ.

### ÇáÊÔÎíÕ ÇáãÚÊãÏ ÈÇáÃÏáÉ

- **ÇáÌĞÑ (Bug 2 æ 3):** ÊæŞíÚ `updated()` ÎÇØÆ İí `storefront/order-form.blade.php`. Çáãáİ ÇÓÊÎÏã `updated(['state_id'], function(){…})` — æÓíØÇğ ËÇäíÇğ ãäİÕáÇğ — ÈíäãÇ ÇáÕíÛÉ ÇáæÍíÏÉ ÇáãÚÊãÏÉ İí Volt ÊÑÇÈØíÉ: `updated(['state_id' => function(){…}])`. ÈÇáÕíÛÉ ÇáÎÇØÆÉ íõÎÒóøä `CompileContext::$updated` ÈãİÇÊíÍ ÑŞãíÉ `[0..9]` ÈÏá `['state_id' => …]`¡ æ`CallPropertyHook::execute` íÈÍË ÈÇáãİÊÇÍ ÇáÇÓãí `$context->updated[$propertyName] ?? fn()=>null` ? **ÑÏø İÇÑÛ ÏÇÆãÇğ (áã íÚãá Ãí ãä ÇáÜ 5 hooks ŞØ)**. ÅËÈÇÊ: `[DBG CTX] updated keys=[0..9] hook_state_id_present=false`º æÇÓÊÏÚÇÁ íÏæí `$i->updated('state_id')` ÈáÇ ÃËÑº æÇÓÊÈŞÇ\ Todos.md:1049 ÇÚÊÑİ ÖãäÇğ ("ØİÑÉ updated áÇ ÊÙåÑ İí assertSet") ÅĞ ÚõæøÖ ÇáÇÎÊÈÇÑ ÈÖÈØ `selectedStopdesk` ÕÑÇÍÉğ.
- **Bug 4** ßÇä ãäİóøĞÇğ ÃÕáÇğ (`mount` + render: `availableProviders->count()===1` ? `selectedProvider` ãËÈÊ + `role="company-select"` ãÎİí) — ÍÏóøÏÊå ÇÎÊÈÇÑ ÅÚÇÏÉ ãÈÇÔÑÉ.
- **Bug 1 (áÇ ÊõÌáÈ ÇáÈáÏíÇÊ + ÈØÁ):** ÇáÎÇÏã Óáíã (ÓÌá laravel İÇÑÛº `citiesSelectOptions` ãÕÏÑå ÇáãÊÑÌã Óáíã æÇÎÊÈÇÑÇÊ ÇáÎÇÏã ÊãÑ). ÇáÚØá İí **ãÓÇÑ ÇáÌáÈ ÇáßÓæá ÇáÚãíá** (`ensureRemoteOptions` ? `$wire.call('citiesSelectOptions', scope)` + ÈæÇÈÉ `_waitServerAck` ÈÓŞİ 6 ËæÇäò) — ãÓÇÑ **áã íõÊÍŞŞ ãäå ÈÕÑíÇğ ãäĞ ÈäÇÆå** (ßá ãÑÇÍá 34-35/1092 ÚáøãÊ "íÊØáÈ ÊÍŞŞÇğ ÈÕÑíÇğ íÏæíÇğ"). «ÇáØÑíŞÉ ÇáÃİÖá» ÇáãÚÊãÏÉ: **ÊÖãíä ÇáŞæÇÆã ãÈÇÔÑÉ** (ãÍÕæÑÉ ÈÇáäØÇŞ ÇáÍÇáí)¡ ÅĞ Ãä `$citiesForSelection()` æ`officesForSelection()` ßäÊíÌÉ ÊõÍÓÈ ÃÕáÇğ ÚäÏ ßá render — ÇáŞæÇÆã ÊÕá ãÚ ÇáÕİÍÉ¡ áÇ ÊÚÊãÏ Úáì ÌáÈ ÇáÚãíá¡ æÊÒæá ãåáÉ ÇáÜ 6 ËæÇäò æÇáÊÃÎøÑ æÇáÓÈÇŞ äåÇÆíÇğ.

### ãÇ äõİøĞ (`resources/views/livewire/storefront/order-form.blade.php` İŞØ — áÇ ÍÔÑ ãäØŞ ÎÇÑÌí ÍÇÌÉ)

1. **ÇáÜ 5 hooks** (state_id / selectedProvider / delivery_type / city_id / selectedStopdesk) ÍõæöøáÊ Åáì `updated(['x' => fn()])` ÇáÕÍíÍÉ.
2. **ÅÕáÇÍ `return` ÛíÑ ÇáãÔÑæØ İí `updated('state_id')`:** ÇáãßÊÈ ÇáŞÏíã íÈŞì İŞØ Åä ßÇä áæáÇíÉ ÇáãÎÊÇÑÉº æÅáÇ íõãÓÍ æíÊÇÈÚ ÇáÊÏİøŞ ááÜ auto-pick (ÇáÇäÊŞÇá ãä æáÇíÉ áãßÊÈ æÇÍÏ ? ÃÎÑì áãßÊÈ æÇÍÏ íËÈøÊ ÇáÌÏíÏ ÇáÂä).
3. **ÍĞİ ÇáÌáÈ ÇáßÓæá ãä ÇáÜ checkout:** `role="city-select"` æ`role="office-select"` ÃÕÈÍÇ ãÖãóøäíä inline (:options="$cities" / :options="$officeOptions") ÈáÇ `lazy/source/scope`º ÍõĞİÊ closureÇ `$citiesSelectOptions`/`$stopdeskSelectOptions` æÈĞæÑ ÇáÜ seeds (dead code)º ÈŞí `$citiesForSelection`/`$officesForSelection`/`$formatOfficeOptions` (ÊõÓÊÎÏã İí render).
4. **ÇáãÑÇÌÚÉ:** `storefront-select.js`/`select.blade.php` áã íÊÛíøÑÇ (áÇ íÒÇáÇä íÏÚãÇä lazy áãä íÍÊÇÌå — áÃäå áÇ ÃÍÏ ãä checkout).

### ÇáÊÍÏíËÇÊ æÇáÔåÇÏÉ

- **ÇÎÊÈÇÑÇÊ ÏÇÆãÉ +4 İí `StorefrontOrderShippingCascadeTest` (ÊõËÈÊ Ãä ÇáÜ hooks ÊÔÊÛá ÇáÂä):** æáÇíÉ ãßÊÈ æÇÍÏ (ÈÈáÏíÉ) ÊËÈøÊ ÇáãßÊÈ ĞÇÊíÇğ æÊÍãá ÇáÈáÏíÉ ááØáÈ **ÈáÇ ÖÈØ ÕÑíÍ**º æáÇíÉ ãßÊÈ æÇÍÏ (ÈÏæä ÈáÏíÉ) ÊËÈøÊ æÊÈŞì ÈáÏíÉ ÇáØáÈ NULLº ÇáÇäÊŞÇá áæáÇíÉ ÃÎÑì íõÓŞØ ÇáãßÊÈ ÇáŞÏíã æíËÈøÊ ÇáÌÏíÏ ãÚ ÈáÏíÊåÇº ÈáÏíÇÊ ÇáãäÒá ãÖãøäÉ æãÍÕæÑÉ ÈÇáæáÇíÉ ÇáãÎÊÇÑÉ (áÇ data-lazy¡ æáÇ ÈáÏíÇÊ æáÇíÉ ÃÎÑì).
- **ÊÍÏíË 3 ÊÃßíÏÇÊ lazy ? inline** İí `StorefrontOrderShippingCascadeTest` + `CartOrderLimitsTest` (ŞæÇÆã ÇáãßÊÈ ÈÇáãÕÇÏÑ ÊõİÍÕ ÇáÂä ÚÈÑ `formatOfficeOptions(officesForSelection())` + ÇáÃÓãÇÁ ÙÇåÑÉ İí HTML).
- **ÅÚÇÏÉ ÊÑÊíÈ `set()` İí 7 ÇÎÊÈÇÑÇÊ ŞÏíãÉ** (ßÇäÊ «state_id/city_id Ëã delivery_type» — ÚõáøŞÊ Úáì ÇáÓáæß ÇáãÚØæÈº ÇáÜ hook ÇáÍí ÇáÂä íÕİøÑ ÇáÌÛÑÇİíÇ ÚäÏ ÊÈÏíá äæÚ ÇáÊæÕíá ßãÇ İí ÇáãÊÕİÍ ÇáæÇŞÚí).
- **ÇáÔåÇÏÉ:** `pest tests/Feature/Storefront` = **119 äÇÌÍ (416 ÊÃßíÏ)** ŞÈáåÇ 116 (ı-1 ãáİ ÅÚÇÏÉ ãÄŞÊ +4 ÏÇÆãÉ). `php -l` äÙíİÜ¡ Pint äÙíİ (ÃÕáÍ 1 ãÎÇáİÉ ÊÑßíÈ İí CartOrderLimitsTest)¡ `view:clear`+`view:cache` ÓáíãÇä¡ `npm` ÛíÑ ãØáæÈ (áÇ ÊÛííÑ JS). ÇáÌæáÉ ÇáßÇãáÉ ŞíÏ ÇáÊÏæíÑ ÈÚÏ ÇáÅáÛÇÁ (ÚÏÏ ÇáäåÇÆí íõÚÊãÏ ÈÇáÊæÇÒí).
- **ÊÍŞŞ ÈÕÑí íÏæí íõÊæŞóøÚ ãä ÇáãÓÊÎÏã Úáì `demo.edzeery.com` (375/768/1440):** ? ÇáãäÒá ? ÇÎÊÑ æáÇíÉ ? ÇáÈáÏíÇÊ **ÊÙåÑ İæÑÇğ** (áÇ ÓÈíäÑ Øæíá) æÊõÎÒóøä ãÚ ÇáØáÈº ? ÇáãßÊÈ ? æáÇíÉ ÈãßÊÈ æÇÍÏ (ãËá 01 Adrar) ? ÈØÇŞÉ ÇáãßÊÈ ÊõËÈóøÊ ÊáŞÇÆíÇğ æÇáØáÈ íõäÔÃ (ÈÈáÏíÉ Ãæ ÈÏæäåÇ ÍÓÈ ÇáãßÊÈ)º ? ÇáãßÊÈ ? æáÇíÉ ÈãßÇÊÈ ãÊÚÏÏÉ (ãËá 16 Alger) ? ÇÎÊíÇÑ ÇáãßÊÈ íÍãá ÈáÏíÉ ÇáãßÊÈ Åáì ÇáØáÈº ? ÔÑßÉ æÇÍÏÉ ? áÇ ãäÊŞí ÔÑßÉ æÓØÑ «ÚÈÑ ÇáÔÑßÉ» íÙåÑ.

---

## ÅÚÇÏÉ ÊÕãíã ÕİÍÉ äÌÇÍ ÇáØáÈ `/order/success/{order:id}` — ãÊÌÇæÈÉ ÈáÇ ÎØ Òãäí ? (2026-09-18)

**ØáÈ ÇáãÓÊÎÏã:** ÕİÍÉ ÇáãÊÌÑ «ØáÈ äÇÌÍ» — ÊÈÏíá ßÇãá ÇáÊÎØíØ Åáì ÚãæÏíä (ãÚáæãÇÊ ÇáÚãíá İí ÌåÉ + ãáÎÕ ÇáØáÈ İí ÇáÌåÉ ÇáÃÎÑì) ÈåæíÉ Apple Design Restraint¡ ãÊÌÇæÈÉ Úáì 375/768/1440px¡ æÅÒÇáÉ Èáæß ÊÊÈÚ ÇáÍÇáÉ («ãÇĞÇ íÍÏË ÈÚÏ Ğáß¿») äåÇÆíğÇ.

### ãÇ äõİøĞ (`resources/views/livewire/storefront/order-success.blade.php` İŞØ — ÈáÇ ãİÇÊíÍ ÊÑÌãÉ Ãæ ÇÓÊÚáÇãÇÊ Ãæ JS ÌÏíÏÉ)

1. **ÍĞİ Èáæß «ãÇĞÇ íÍÏË ÈÚÏ Ğáß¿» ßÇãáğÇ** (ßÇä ÈÇáÃÓØÑ 196–261: `what_happens_next` + `step_order_placed/100%/step_out_for_delivery/step_delivered`) — áÇ ÊÚÑÖ ÇáÍÇáÉ ÅØáÇŞğÇ ÈÚÏ ÇáÂäº ÃõÈŞí ÓØÑ «ÓäÊÕá Èß» ßáãÓÉ ÊæÇÕá æáíÓÊ ÍÇáÉ.
2. **ÊÎØíØ ÚãæÏíä ãÊÌÇæÈ:** ÍÇæíÉ `max-w-5xl mx-auto`º ÔÈßÉ `grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8 items-start` — ÇáãáÎÕ `md:col-span-7` æÇáÚãíá/ÇáÊæÕíá `md:col-span-5`º 375px ãßÏøÓ (ÇáãáÎÕ ÃæáğÇ)¡ 768px ÚãæÏÇä ÌäÈğÇ Åáì ÌäÈ¡ 1440px ãÓÇİÇÊ/ÍÔæÉ ÃæÓÚ (`lg:gap-8` + `lg:px-8`). ÈØÇŞÇÊ ÈßáÇÓÇÊ ÇáÊÕãíã ÇáãÚíÇÑíÉ (`rounded-2xl border shadow-sm bg-white dark:bg-gray-800`) æåæíÇÊ ÇáãÊÌÑ `store-*`.
3. **ãáÎÕ ÇáØáÈ:** ÚäÇÕÑ ÈÕæÑÉ 48–56px (`rounded-xl object-cover` + fallback `noimg.png`) ãÚ ÇáãÊÛíÑ æ«ÓÚÑ × ßãíÉ» æ`tabular-nums`º ÃÓØÑ subtotal / shipping («ãÌÇäğÇ» ÈÇáÃÎÖÑ ÚäÏ ÇáÕİÑ) / **ÎÕã ÔÑØí** (ÚÈÑ `titles.discount` ÇáãæÌæÏ ÈÇááÛÇÊ ÇáÃÑÈÚ — ÕİÑ ãİÇÊíÍ ÌÏíÏÉ) / ÇáÅÌãÇáí ãä accessor `grand_total`.
4. **ÈØÇŞÉ ÇáÚãíá/ÇáÊæÕíá:** ÇáÇÓã¡ ÇáåÇÊİ (`dir="ltr"`)¡ ØÑíŞÉ ÇáÏİÚ (`cod ? storefront.payment_on_delivery` æÅáÇ ÇáŞíãÉ ÇáÎÇã)¡ ßÊáÉ ÊæÕíá: stopdesk ÈÇÑÒÉ ÈåæíÉ ÇáãÊÌÑ (äõŞáÊ ãä ÈØÇŞÉ ÇáãáÎÕ) Ãæ ãäÒáí (address + city + state ÈÜ `break-words`)¡ æãáÇÍÙÇÊ ÈÍÏæÏ `border-t`.
5. **ÇáÃÏÇÁ:** ÃõÒíá `'status'` ãä eager-load İí `mount()` (ÇÓÊÚáÇã ÃŞá)º áÇ ÌáÈ ßÓæá ÌÏíÏ (ÊÌäøÈ `stopdeskPoint.city`) æáÇ JS/SCSS.

### ÇáÔåÇÏÉ

- `php -l` äÙíİ¡ `view:clear` + `view:cache` ÓáíãÇä¡ `npm run build` äÇÌÍ (ÊÍĞíÑÇÊ Sass ÇáÎÇÕÉ ÈÜ Bootstrap ãæÌæÏÉ ÓÇÈŞğÇ).
- **ÇáÌæáÉ ÇáßÇãáÉ: 849 äÇÌÍ (3468 ÊÃßíÏğÇ) — İÔá æÍíÏ ÓÇÈŞ ÇáÊÃÓíÓ** İí `CarrierSyncObservabilityTest` (`expectsOutputToContain('8')` áÇ íØÇÈŞ ãÎÑÌÇÊ `carrier-sync:report`) + 1 risky (äİÓ Çáãáİ)º ÃõËÈÊ Ãäå ãÓÈŞ: íİÔá ãäİÑÏğÇ æÈÚÏ ÅÎİÇÁ ÇáÊÚÏíá (`git stash`). íõÔÛóøá ÈÇáĞÇßÑÉ ÇáãæÓÚÉ `-d memory_limit=-1`.
- **ÇáÊÍŞŞ ÇáÈÕÑí ÇáíÏæí ÇáãõÊæŞóøÚ Úáì ÇáãÊÌÑ ÇáÍí (375/768/1440):** ? 375px — ÈØÇŞÉ ÇáãáÎÕ Ëã ÇáÚãíá ãßÏøÓÊíä¡ áÇ ÎØ Òãäíº ? 768px — ÇáãáÎÕ íÓÇÑ/ÇáÚãíá íãíä ÌäÈğÇ Åáì ÌäÈ İí Õİ æÇÍÏº ? 1440px — ÈØÇŞÊÇä ãÊÓÇæíÊÇ ÇáÇÑÊİÇÚ ÊŞÑíÈğÇ ãÚ ãÓÇİÇÊ ÃæÓÚ æCTR İí ÇáæÓØº ? ãáÎÕ ãÇáí: subtotal + shipping ? discount = ÇáÅÌãÇáí (ÚäÏ æÌæÏ ÎÕã Úáì ÇáØáÈ).

---

## Phase 33-rider — ÅÕáÇÍ «ÅÑÓÇá ÇáØáÈíÉ Åáì ÑÌá ÇáÊæÕíá ÈáÇ ÑŞã ÊÊÈÚ» + ÊäÓíŞ ÇáÑŞã ÇáÌÏíÏ (ÓÈÊãÈÑ 2026) ?

**ØáÈ ÇáãÓÊÎÏã (ÈÇáÚÑÈíÉ):** «áÇ íÒÇá ÅÑÓÇá ÇáØáÈíÉ Åáì ÑÌá ÇáÊæÕíá ÈáÇ ÑŞã ÇáÊÊÈÚ» — ÅÕáÇÍ ÅäÔÇÁ ÊÊÈÚ ÈØÑíŞÉ ÌÏíÏÉ: 3 ÍÑæİ ãä ÇÓã ÇáãÊÌÑ — äæÚ ÇáÊæÕíá `HM|SD` — ÑŞã ÚÔæÇÆí 6 ÃÑŞÇã.

- **ÇáÊäÓíŞ ÇáÌÏíÏ İí `OrderTrackingService::generateRiderTrackingNumber()`:** `{PREFIX}-{HM|SD}-{6 ÃÑŞÇã}` (ãËÇá `EDZ-HM-402731`) — ÃõÒíá ãŞØÚ ÇáæáÇíÉ äåÇÆíğÇ ãÚ ÍĞİ `stateCode()` æÇáÇÚÊãÇÏ Úáì `State`º ÈŞíÊ `storePrefix()` (Ãæá 3 ÃÍÑİ ASCII¡ ÍÔæ ÂÎÑ ÍÑİ: `Lo`?`LOO`¡ ÓŞæØ ÈÇáÜ slug ÚäÏ ÇÓã ÈáÇ ÃÍÑİ áÇÊíäíÉ¡ fallback `STO`) ãÚ ÍáŞÇÊ ÊÌÇæÒ ÇáÇÕØÏÇã æÍãÇíÉ Code128.
- **ÇáÌĞÑ ÇáİÚáí:** ãÓÇÑ ÅÑÓÇá ÇáÑÇÌá İí `OrderShippingGateway::send()` ßÇä íäÔÆ Õİ `OrderTracking` ÈÑŞã ÊÊÈÚ **NULL**¡ æÇáãÓÇÑ ÇáæÍíÏ ÇáĞí ßÇä íãáÄå = ÏÑÌ ÇáÊÃßíÏ İŞØ (`submitConfirmAndSend`)º ãÓÇÑÇ ÇáÅÑÓÇá ÇáãÈÇÔÑ (`sendConfirmedOrder`) æÇáÅÑÓÇá ÇáÌãÇÚí (`confirmBulkSend`) ÊÌÇæÒÇ ÇáÊÎÒíä. **ÇáÍá:** closure ãÔÊÑßÉ `$ensureRiderTrackingAfterSend(Order $order)` İí `merchant/orders/index.blade.php` (ÍÑÇÓ ãÓÇÑ ÇáÑÇÌá: `delivery_rider_id` ãÖãøä æ`shipping_provider_id` İÇÑÛº ãÍÇíÏÉ ÚÈÑ `ensureRiderTracking`) æãÑÈæØÉ ÈÇáãÓÇÑÇÊ ÇáËáÇËÉ.
- **ÇáÔåÇÏÉ:** `OrderCompletenessTest` 29/29 ÇáÊÃßíÏÇÊ `OCO-HM-`/`EDZ-HM-`/`LOO-HM-`… ÈãÇ İíåÇ ÇÎÊÈÇÑÇ ÇäÍÏÇÑ ÌÏíÏÇä («ÅÑÓÇá ØáÈíÉ ãÄßóøÏÉ ãÈÇÔÑÉ Åáì ÇáÑÇÌá íÓÊÑÌÚ ÑŞã ÊÊÈÚåÇ» æ«ÇáÅÑÓÇá ÇáÌãÇÚí Åáì ÇáÑÇÌá íÓÊÑÌÚ ÑŞã ÊÊÈÚåÇ» ÈÊÃßíÏ `/^OCO-HM-\d{6}$/`) + `TrackingSearchFilterTest` 33/33 (`TRA-HM-`/`TRA-SD-`) — ÚÈÑ `view:cache` Ëã `view:clear` (ÊÌäøÈ Şİá ÊÍæíá ãáİÇÊ Windows).

---

## Phase 34.1 — ÃÓÇÓÇÊ İÕá ÓÚÉ ÇáÊÃßíÏ Úä ÇáÊÊÈÚ (ÊæÒíÚ ÇáØáÈíÇÊ) — ãÎØØ + äãÇĞÌ + ÅÚÏÇÏÇÊ İŞØ ? (2026-09-18)

**ØáÈ ÇáãÓÊÎÏã/ŞÑÇÑ ÇáãÇáß:** áßá ãÊÌÑ ÓŞİÇ ÓÚÉ ãäİÕáÇä áİÑíŞí ÇáÊÃßíÏ æÇáÊÊÈÚ ÍÊì áÚÖæ íãáß ÇáÕáÇÍíÊíä ãÚğÇº ÊÌÇæÒ äÇÚã ÊáŞÇÆí ÍÊì äÓÈÉ íŞæøãåÇ ÇáÊÇÌÑ İæŞ `max_concurrent_orders` (ÇİÊÑÇÖí 10%)¡ íõØÈóøŞ áÇÍŞğÇ æÈÇáÊÓÇæí Úáì ÇáÌÇäÈíä (ÇáãÑÇÍá 34.2–34.4 ÎÇÑÌ ÇáäØÇŞ). åĞå ÇáãÑÍáÉ ÊÖÚ ÇáÃÓÇÓÇÊ İŞØ — **ÈáÇ ãäØŞ ÊÚííä/ÊÌÇæÒ**.

**ãÇ äõİøĞ (ÖÏ commit `9dc4b6d`):**

1. **åÌÑÇÊ `2026_09_18_*` (4):**
   - `confirmation_shifts.role_scope` (string¡ ÇİÊÑÇÖí `'confirm'` — ßá ÇáÕİæİ ÇáŞÇÆãÉ ÊÈŞì ÕÇáÍÉ ÈáÇ ÅÚÇÏÉ ÈĞÑ) + İåÑÓ ãÑßøÈ `confirmation_shifts_store_role_active_idx` Úáì `(store_id, membership_id, role_scope, is_active)` (ÇáÇÓã ÇáŞÕíÑ ÊÌäøÈğÇ áÎØÃ MySQL 1059¡ ÍõÏøÏ ÕÑÇÍÉğ).
   - `order_trackings`: `assigned_to_membership_id`/`assigned_by_membership_id` (foreignUlid ? `store_memberships` nullOnDelete) + `assigned_at` (timestamp) + `assignment_method` (string) — ãÑÂÉ ÍÑİíÉ áÃÚãÏÉ ÌÇäÈ ÇáÊÃßíÏ — + İåÑÓ Úáì `assigned_to_membership_id`.
   - `over_capacity` (boolean ÇİÊÑÇÖí false) Úáì `orders` æ`order_trackings`.
   - `store_settings`: `distribution_overflow_enabled` (boolean ÇİÊÑÇÖí true) + `distribution_overflow_percentage` (unsignedTinyInteger ÇİÊÑÇÖí 10º ÇáãÏì 0–100 İí ØÈŞÉ ÇáÊÍŞŞ İŞØ).
2. **ÇáäãÇĞÌ:** `ConfirmationShift` (+`role_scope` İí `$fillable`/`$casts` + `scopeConfirm()`/`scopeTrack()`º 153?165 ÓØÑ) ? `StoreSetting` (+ÇáÍŞáÇä İí `$fillable` + casts boolean/integerº 59?63) ? `OrderTracking` (+ÇáÍŞáÇä æÌÏÇæá/ÊÍŞŞ ÇáÊæÇÑíÎ İí `$fillable`/`$casts` + `assignedTo()`/`assignedBy()` BelongsTo?StoreMembershipº 136?153).
3. **æÕİÍÉ ÌÏíÏÉ** `livewire/merchant/order-distribution-settings/` (Volt `index.blade.php` **51 ÓØÑ** ÊÍÊ 400 + ŞÓã `partials/overflow-settings.blade.php` **42 ÓØÑ** ÊÍÊ 300): ÊÈÏíá «ÊİÚíá ÇáÊÌÇæÒ ÇáÊáŞÇÆí» (x-edz.checkbox ÈäãØ ÈØÇŞÇÊ toggle ÇáŞÇÆãÉ) + ÍŞá äÓÈÉ 0–100 ÈãÏÎá number æÇáÍÑİ ?¡ ãÚØøá ÚäÏ ÇáÅíŞÇİ¡ ÊÍŞŞ ÎÇÏãí `required|integer|min:0|max:100`¡ ÍİÙ ÚÈÑ `$store->settings()->updateOrCreate()` + ÊæÓÊ `settings_saved`. ÍÇÑÓ `canStore(STORE_UPDATE)` İí mount æÇáÍİÙ. ãÓÇÑ `merchant.order-distribution-settings` İí `routes/merchant.php` + ÑÇÈØ ŞÇÆãÉ ÌÇäÈíÉ (ÚãáíÇÊ¡ ÍÇÑÓ `$canViewOrderSettings`) + 8 ãİÇÊíÍ ÊÑÌãÉ ×4 áÛÇÊ. æÇÌåÉ ãÊÌÇæÈÉ ÈØÈíÚÊåÇ (ÚÑÖ max-w-2xl/48¡ áÇ äæÇİĞ ËÇÈÊÉ): 375px ãßÏøÓÉ¡ 768/1440 ÈÍÏ ÃŞÕì 672px.
4. **ÇáÔåÇÏÉ:** `migrate:fresh --seed` äÙíİº ÇáÃÚãÏÉ ÇáÎãÓÉ ÚÔÑ + ÇáİåÇÑÓ ãÄßóøÏÉ ÈÜ `SHOW COLUMNS`/`SHOW INDEX` (ÑÇÌÚ `verify_cols.php`)º `ConfirmationShiftTest` (6) + `OrderAssignmentServiceTest` (6) = 12/12 ÈäÌÇÍ **ÈÏæä ÊÚÏíá** (ÊæÇİŞ ÑÌÚí: ÇáÌåÇÊ ÈáÇ `role_scope` ÊÍÕá `confirm` ÇİÊÑÇÖíğÇ)º ÇÎÊÈÇÑ ÚÇÈÑ ãÄŞøÊ (ÍõĞİ áÇÍŞğÇ) ÃËÈÊ ÚÑÖ/ÍİÙ/ÑİÖ 150 Úáì ÇáÕİÍÉ ÇáÌÏíÏÉ 3/3º `php -l` äÙíİ ×9º áÇ ÇÓÊÚáÇãÇÊ İí ÍáŞÉ (mount: ŞÑÇÁÉ settings æÇÍÏÉº ÍİÙ: updateOrCreate æÇÍÏÉ).

| İÑÚ | ÇáÍÇáÉ | ÇáãáİÇÊ ÇáÑÆíÓíÉ | ÊÍŞŞ |
|---|---|---|---|
| 34.1 ÃÓÇÓÇÊ ÊæÒíÚ ÇáØáÈíÇÊ | ? | 4 åÌÑÇÊ `2026_09_18_*` + ConfirmationShift + StoreSetting + OrderTracking + order-distribution-settings/{index, partials/overflow-settings} + routes/merchant.php + store-sidebar + merchant_panel.php ×4 | migrate:fresh --seed + 12/12 (ÈÏæä ÊÚÏíá) + İåÇÑÓ ÇáÜ cols + ÊÌÑÈÉ ÚÑÖ ãÄŞÊÉ 3/3 |

> **ÇáÇÚÊãÇÏ ÇáãÈÇÔÑ ááãÑÍáÉ 34.2:** ÊÚííä ÓÚÉ ÇáÊÊÈÚ íÓÊåáß `role_scope='track'` (äØÇŞ ÇáÜ scopes `scopeConfirm()/scopeTrack()` æÇáİåÑÓ ÇáãÑßøÈ ÇáÌÏíÏ) æÚãæÏí `order_trackings.assigned_to/assigned_at/assignment_method/over_capacity`º æÇáÊÌÇæÒ ÇáäÇÚã (34.3) íÓÊåáß `store_settings.distribution_overflow_*` æ`over_capacity`. íõãäÚ ÊÚÏíá `OrderAssignmentService`/`OrderConfirmationService`/`DispatchPendingAssignmentsJob`/`reassign-modal.blade.php`/`order-settings.blade.php` ŞÈá İÊÍ 34.2.

---

## Phase 34.2 ? ????? ????? ??? ??????: Trait + ?????? + ?????? ???????? + ???????? ? (2026-09-18)

**?????:** ??????? ???? ??????? ???????? ?????? (on-shift + ???/???? + ?????? ??????) ??? Trait ????? ??????? ?? ???? ??????? (??? ?? ????? ?? ??????) ????? ?????? ?????? ?? ???? ????? `OrderTracking` ?????? ???? ?????? ?? 15 ?????. **?? ??????/Blade? ?? ???? ????? ???? (???? ?????? ??? 34.3)? ?? ????? ????/?????? ??????.**

**?? ?????:**

1. **`App\Domains\Order\Concerns\ResolvesCapacityBalancedCandidates` (Trait? 107 ??? ? 120):** `bestCandidateOnShift(candidates, storeId, roleScope, openCounts, lastAssignedAt)` ??? ?????? ??? ????? ???????? `quotaCapsByMember()` ??????? ???? ???? (`MAX(max_concurrent_orders)` groupBy ?????? ???? ????? ??? `confirm()/track()`)? `isOnShift()` ????? `isOnActiveShift(roleScope: ?)` ?? `Log::debug('Member not on active shift', membership_id/user_id)`? `withinQuota()` (??? null = ??? ??? `openCount < cap`)? `outranks()` (??? ?????? ?? ???? ??? ????? fallback `1970-01-01`).
2. **????? ????? `OrderAssignmentService` (297 ? 248 ??? < 250):** ?????? ??? Trait (?????? `membershipCap/filterOnShift/loadBalance` ?import ??? ConfirmationShift)? `selectBest` ???? ?????? tier (?????? ?? ????) ??? `bestCandidateOnShift` ?????? `'confirm'`? ????????? `openOrderCounts`/`lastAssignedAt` ????? ??????? **?????? ????? ??????** ? `OrderAssignmentServiceTest` (6) + `ConfirmationShiftTest` (6) = 12/12 ???? ???? ?????.
3. **`StoreMembership::isOnActiveShift(?Carbon $at = null, string $roleScope = 'confirm')`:** `->when($roleScope === 'track', track(), confirm())` ??? ??????? `confirmationShifts()`? ??????? ?????? ????? (?? ???????? ???????? ??? ????).
4. **`App\Domains\Order\Services\OrderTrackingAssignmentService` (151 ??? < 250):** `assign(tracking)` ? ???? null store? ??? ???????? = ????? ????? ??`CRM_ORDER_TRACKING` (?? `with('storeWithTimezone')` ???? N+1)? ??? ????? ?? `ConfirmationShift::track()`? `openAssignmentCounts()` ??? `order_trackings` (assigned ??? null + `tracking_status` ?? `OrderTrackingStatus::open()`)? `lastAssignedAt()` ??? MAX(assigned_at)? ?? `bestCandidateOnShift(...,'track',...)`? ?? ???? ? `Log::warning('Order tracking auto-assignment skipped: no candidates on active shift', ?)` + ??? ??????? ?????? ? `assigned_to/assigned_at=now()/assignment_method='auto'` + `Log::info`. `reassign(tracking, to, by)` ? ???? ??? ?????? (`InvalidArgumentException`)? `assigned_by_membership_id` + `assignment_method='manual'`.
5. **`App\Domains\Order\Jobs\DispatchPendingTrackingAssignmentsJob` (41 ???):** ???? `DispatchPendingAssignmentsJob` ? `OrderTracking` ??? `assigned_to_membership_id` null + `assignment_method` null + `tracking_status = SHIPPED->value` ?? `with('store')`? ??? ?? assign ???? try/catch + `Log::error('Failed to auto-assign order tracking', tracking_id/error)`? ????? ?? `routes/console.php` ????? ?????: `Schedule::job(new DispatchPendingTrackingAssignmentsJob)->everyFifteenMinutes();`.
6. **`tests/Feature/Order/OrderTrackingAssignmentServiceTest` (9 ???????? 15 ??????):** ????? ????? ??? ???? ???? ???? ??? `role_scope='track'` ?????? ??? `role_scope='confirm'` ?? ??? ??? ?????Ÿ ?????? ??? ????? ???? ??? ??????? ????? ??? ????? ??? ???? (???? confirm ???) ? ???? ??? ????? ??? ??????? ORDER_CONFIRM ??? ?? ?????? ????? (??????? ?? CRM_ORDER_TRACKING ??? ????)? `reassign` ??? ?????? ?????? manual? ??? ??? ???? ???. (????: `SystemStatusesSeeder` + `Africa/Algiers` + `Carbon::setTestNow(2026-05-04 10:00)` ???????.)

7. **???????:** `php -l` ???? ?7 ????? `OrderAssignmentServiceTest`+`ConfirmationShiftTest` **12/12 ???? ?????**? `OrderTrackingAssignmentServiceTest` 9/9? ???? PHP ????????: Trait 107 ? 120? `OrderAssignmentService` 248 < 250? `OrderTrackingAssignmentService` 151 < 250? Job 41? routes/console 53? ?? N+1 (????? `storeWithTimezone` ???? ??????? ?????????? ???? ??? ???? ???? ?????).

| ??? | ?????? | ??????? ???????? | ???? |
|---|---|---|---|
| 34.2 ????? ????? ??? ?????? | ? | ResolvesCapacityBalancedCandidates + OrderAssignmentService + StoreMembership::isOnActiveShift + OrderTrackingAssignmentService + DispatchPendingTrackingAssignmentsJob + routes/console.php + OrderTrackingAssignmentServiceTest | php -l ?7 + 12/12 (???? ?????) + 9/9 + ???? 248/151/107/41/53 + ?? N+1 |

> **???????? ??????? 34.3:** ??????? ?????? ?????? `store_settings.distribution_overflow_enabled/percentage` ?`orders.over_capacity`/`order_trackings.over_capacity` (?? ??????? ??? ? ???? false) ???????? ???????? ??? ????? ??????? ???????. ????? ????? `OrderAssignmentService`/`OrderTrackingAssignmentService`/`DispatchPendingAssignmentsJob`/`DispatchPendingTrackingAssignmentsJob` ??? ??? 34.3.
---

## Order-Settings Cleanup — ÏãÌ ÊÈæíÈ ÇáÊÌÇæÒ ÈÚÏ 34.3: äÙÇİÉ + ÅÕáÇÍ regression ? (2026-09-18)

**ÇáÓíÇŞ/ÇáŞÑÇÑ:** ÈÚÏ 34.3 ÇáÊÌÇæÒ ÇáäÇÚã ÚÇÔ İí ãßÇäíä (ÊÈæíÈ ÏÇÎá `/{store:slug}/order-settings` + ÕİÍÉ ãÓÊŞáÉ `/{store:slug}/order-distribution-settings`). ŞÑÇÑ ÇáãÇáß: ÊÈŞì ÇáÊÈæíÈ İŞØ¡ æÇáÍĞİ ááãÑÌÚíÉ ÇáãÓÊŞáÉ¡ æåĞÇ ÊäÙíİ åíßáí/ßæÏ ãíÊ İŞØ — ÈáÇ æÙÇÆİ ÌÏíÏÉ.

1. **ÅÕáÇÍ ÚØá `storage/logs/laravel.log`:** ÓØÑ `$this->members = StoreMembership::where(...)` ßÇä íÖÚ `Eloquent\Builder` İí ÍÇáÉ Livewire İÊõÑãì `Property type not supported in Livewire for property ... members` ÚäÏ mount ÇáÕİÍÉ — ÃõÚíÏ `->with('user')->get()->toArray()`.
2. **ÍĞİ ÇáÕİÍÉ ÇáãÓÊŞáÉ:** `order-distribution-settings/index.blade.php` ÍõĞİ (ÇáãÓÇÑ ãÒÇá ãÓÈŞğÇ ãä `routes/merchant.php`¡ İáÇ route cache — ÊÑÌÚ 404)º ÃõÒíá ÇÓã ÇáãÓÇÑ `merchant.order-distribution-settings` ãä `store-sidebar.blade.php` (ãÕİæİÉ `operationsOpen`)º ÍõĞİ ÇáãİÊÇÍÇä ÇáãíÊÇä `order_distribution_settings`/`order_distribution_settings_desc` ãä `ar/en/fr/es`. ÇáÜ partial ÇáãÔÊÑß `order-distribution-settings/partials/overflow-settings.blade.php` ÈŞí ãßÇäå æíõÖãóøä ãä `order-settings.blade.php` ßÃÍÏ ÇáÊÈæíÈÇÊ.
3. **ÅÌÑÇÁ ÍİÙ æÇÍÏ:** ÍõĞİ closure `$save` ÇáãíÊ (ßÇä ÔÇÏæğÇ/alias ÓÇÈŞğÇ Ëã ÅãáÇÁ ßÇãáğÇ ÈáÇ ÑÈØ)º ÈŞí `saveOverflow` ÇáæÍíÏ (ÊÍŞŞ `required|integer|min:0|max:100` + `settings()->updateOrCreate` + toast `settings_saved`) — áÇ `wire:click="save"` ãÚáøŞ İí Ãí Blade (grep).
4. **ÊŞÓíã `order-settings.blade.php` Åáì partials** ÈäãØ `confirm-drawer`: `livewire/merchant/order-settings/partials/{overview,tabs,shifts-tab,assignments-tab,shift-modal,assignments-modal}.blade.php`º ßÇãá ßÊáÉ Volt (state/actions) İí Çáãáİ ÇáÑÆíÓí¡ æÇáÜ overflow tab inline íÖãøä ÇáÜ partial ÇáãÔÊÑß. **816 ? 393 ÓØÑğÇ** (ÊÍÊ ÓŞİ 400).
5. **ÇáÔåÇÏÉ:** `php -l` äÙíİ ×4 ãáİÇÊ áÛÉº `view:cache`/`view:clear` äÙíİº ÇáÜ markup ÈÚÏ ÇáİÕá ãØÇÈŞ ÍÑİíğÇ ááãÕÏÑ (ÊŞÓíã ãßÇäí İŞØ ? áÇ ÊÛííÑ İí 375/768/1440)º `ConfirmationShiftTest`+`OrderAssignmentServiceTest`+`OrderTrackingAssignmentServiceTest` = 23/23º ãÌãæÚÉ `tests/Feature/Merchant` ßÇãáÉ = 575 ÇÎÊÈÇÑğÇ (2448 assertion) ÎÖÑÇÁ.

> **ÇáÊÇáí — Phase 34.4 (ÈÇäÊÙÇÑ ãæÇİŞÉ ãäİÕáÉ):** ÅÚÇÏÉ ÈäÇÁ `reassign-modal.blade.php`. ÛíÑ ãÓãæÍ ÈáãÓ ÇáÜ modal/ÇáÎÏãÇÊ ŞÈá İÊÍåÇ.

## Reassignment UI (confirm + tracking) — ÅÚÇÏÉ ÇáÊßáíİ ÇáíÏæí (ãÑÍáÉ 34.4)

**ÇáæÖÚ: ãßÊãáÉ ?**

1. **ãäÔÆ ŞæÇÆã ÇáãÑÔÍíä (`AssignmentCandidateResolver`):** `app/Domains/Order/Support/AssignmentCandidateResolver.php` (**206 ÓØÑ** < 250). íõÑÌÚ ÇáÃÚÖÇÁ ÇáäÔØíä İí ÇáãÊÌÑ ÇáÍÇãáíä ááÕáÇÍíÉ ÇáãØáæÈÉ İŞØ¡ ãÚ ÍŞá áßá ãÑÔÍ: `open` (Íãáå ÇáÍÇáí Öãä ÇáäØÇŞ)¡ `cap` (ÓŞİ ÇáæÑÏíÉ ÇáãÚäí¡ `null` = ÛíÑ ãÍÏæÏ)¡ `on_shift` (åá åæ İí æÑÏíÊå ÇáÂä)¡ `dual_role` (íÍãá Confirm + Track). ÌãíÚ ÇáÇÓÊÚáÇãÇÊ ãÌãøÚÉ æáÇ íæÌÏ N+1 (ÇÎÊÈÇÑ íõËÈÊ ËÈÇÊ ÚÏÏ ÇáÇÓÊÚáÇãÇÊ áäİÓ ÇáãÊÌÑ ãÚ 1 ãŞÇÈá 4 ãÑÔÍíä). äãæĞÌ ŞÑÇÁÉ ááæÇÌåÉ İŞØ — `reassign()` İí ÇáÎÏãÊíä áã íõÊÚÏóø (ÇáÊÌÇæÒ ÇáíÏæí ãÍİæÙ áßá ãä `OrderAssignmentService` æ`OrderTrackingAssignmentService`).
2. **ÅÚÇÏÉ ÈäÇÁ `reassign-modal.blade.php`** ßŞØÚÉ ãÔÊÑßÉ (**89 ÓØÑ** < 300) ÊõÖãóøä ÈÇáÈÇÑÇãÊÑÇÊ: ŞÇÆãÉ ÇÎÊíÇÑ ÈÕíÛÉ radio ãÚ ÇáÇÓã + ÔÇÑÉ "İí ÇáæÑÏíÉ" (ÎÖÑÇÁ) + ÔÇÑÉ ÇáÏæÑ ÇáãÒÏæÌ + ÇáÍãá ÇáÍÇáí "ÚÏÏ/ÓŞİ" Ãæ "ÛíÑ ãÍÏæÏ"º ÊÍĞíÑ ÛíÑ ãÇäÚ ÈÇáÈÑÊŞÇáí ÚäÏ ÇÎÊíÇÑ ãÑÔÍ ÈáÛ ÓŞİåº ÍÇáÉ İÇÑÛÉ ÚäÏ ÛíÇÈ ÇáãÑÔÍíäº ÃÒÑÇÑ ÅáÛÇÁ/Êßáíİ. ÇáæÇÌåÉ ãÍŞŞÉ æİŞ ÊÕãíã Apple İí DESIGN_SYSTEM.md.
3. **ÕİÍÉ ÇáØáÈÇÊ (`orders/index.blade.php`):** `$openReassignModal` ÃÕÈÍ íãáÃ `reassignCandidates` ãä ÇáãõäÔÆ ÈÕáÇÍíÉ `ORDER_CONFIRM` æäØÇŞ `confirm`¡ æ`$submitReassign` íÖíİ ÅÚÇÏÉ İÍÕ ÕáÇÍíÉ ÇáåÏİ (`ORDER_CONFIRM`) ßÎØ ÏİÇÚ ËÇäòº ÇÓÊÏÚÇÁ ÇáÜ modal ÚäÏ ÇáÓØÑ 5758 íãÑÑ ÇáÈÇÑÇãÊÑÇÊ ÇáãõÔÇÑßÉ. `$allMembers` ÈŞí ßãÇ åæ (íõÓÊÎÏã İí 515/517/575/5139/5148). ÔÇÑÉ ÇáÜ trigger ãæÌæÏÉ ÃÕáÇğ İí `orders-table-actions-column.blade.php` (106).
4. **ÕİÍÉ ÇáÊÊÈÚ (`tracking/index.blade.php`):** **373 ? 394 ÓØÑ** (< 400). traits ÌÏíÏÉ `app/Livewire/Concerns/TrackingReassignConcern.php` (**65 ÓØÑ**) İíåÇ `openTrackingReassignModal`/`submitTrackingReassign` ÈÕáÇÍíÉ `ORDER_MANAGE` (äİÓ äãØ ÃİÚÜÇá Drawer İí ãŞÇÓ ÇáÊÊÈÚ) æäØÇŞ `track` ãÚ `CRM_ORDER_TRACKING`¡ Ëã `OrderTrackingAssignmentService::reassign` + toast `order_flow.tracking_reassigned`. 4 ÍÇáÇÊ state ÌÏíÏÉ + include ááÜmodal ÇáãÔÊÑß. ÇáÔÇÑÉ ÃõÖíİÊ İí `tracking-row-cell.blade.php` æ`tracking-mobile-card.blade.php` (ãŞíøÏÉ ÈÜ `ORDER_MANAGE` æÊÈæíÈ `carrier` İŞØ).
5. **ãİÇÊíÍ ÊÑÌãÉ ÌÏíÏÉ** İí `ar/en/fr/es` (`merchant_panel`): `on_shift`, `dual_role_badge`, `current_load`, `reassign_no_candidates`, `reassign_over_capacity_warning`; æ`order_flow.tracking_reassigned`. ßáåÇ ÓáíãÉ áÜ `php -l`.
6. **ÇáÊÍŞŞ:** `php -l` äÙíİ (PHP ÌÏíÏ + 8 ãáİÇÊ áÛÉ)¡ `view:cache`/`view:clear` äÙíİ¡ ÇÎÊÈÇÑ `tests/Feature/Order/AssignmentCandidateResolverTest.php` (**7 ÇÎÊÈÇÑÇÊ**) + `tests/Feature/Order` ßÇãáÉ = 30/30¡ æ`tests/Feature/Merchant` ßÇãáÉ = **575 äÇÌÍ (2448 assertion)**. ÇáİÍÕ ÇáÈÕÑí Úáì 375/768/1440 áã íõÌÑó ãä ÇáÌåÇÒ (áÇ ãÊÕİÍ) æíÍÊÇÌ ãÑÇÌÚÉ ÇáãÓÊÎÏã.

## Phase 34.5 — ÕİÍÉ ØÇÈæÑ ÇáÊæÒíÚ (overflow queue) + ÑÇÈØ ÇáÈÑíÏ — 2026-09-18 ?

**ÅÛáÇŞ ãÈÇÏÑÉ Phase 34 ßÇãáÉ (34.1 ? 34.5):** ÚÈÑ 34.1 (settings overflow)¡ 34.2/34.3 (ÊæÒíÚ ãæÒæä + ÊÌÇæÒ + Úáã over_capacity)¡ 34.4 (ÃÚÖÇÁ ãÍÏæÏæ ÇáÕáÇÍíÇÊ + ÅÚÇÏÉ ÇáÊßáíİ ÇáãÔÊÑßÉ) Åáì 34.5 — ãÈÇÏÑÉ ÊæÒíÚ/ÊÃßíÏ/ÊÊÈÚ ÇáØáÈÇÊ ÇäÊåÊ æÙíİíğÇ.

1. **ÇáÕİÍÉ ÇáÌÏíÏÉ:** `Volt::route('/{store:slug}/order-distribution-queue')` (`routes/merchant.php`) ãÍãíÉ ÈÜ `ORDER_MANAGE`¡ ÊÊÈæíÈÇä (ÊÃßíÏ/ÊÊÈÚ) ÈäãØ ÊÈæíÈÇÊ `order-settings` ãÚ ÔÇÑÉ ÚÏøÇÏ Íí. `livewire/merchant/order-distribution-queue.blade.php` (**158 ÓØÑ** < 400) + partials (`tabs` **20**¡ `queue-table` **80**).
2. **ãÕÏÑÇ ÇáÕİ:** `app/Livewire/Concerns/DistributionQueueConcern.php` (**116 ÓØÑ** < 250) — `orders`/`order_trackings` ÍíË `assigned_to_membership_id IS NULL` Ãæ `over_capacity=true`º ÇÓÊÈÚÇÏ ÇáÍÇáÇÊ ÇáäåÇÆíÉ ÈãİÊÇÍ ãõÓÊãÏ ãä `OrderStatus::isTerminal()` (ãÕÏÑ æÇÍÏ¡ áÇ äÓÎÉ ËÇáËÉ) æŞÕÑ ÇáÊÊÈÚ Úáì `OrderTrackingStatus::open()`º ÊÑÊíÈ «ÛíÑ ÇáãõÓäÏ Ëã ÇáÃŞÏã»º eager-load ÈáÇ N+1.
3. **ÅÚÇÏÉ ÇáÊßáíİ:** ÅÚÇÏÉ ÇÓÊÎÏÇã `reassign-modal.blade.php` ÇáãÔÊÑß ÈÚŞÏ `@include` äİÓå + `AssignmentCandidateResolver` (confirm/track ÍÓÈ ÇáÊÈæíÈ) + ÇáÎÏãÊíä ÇáÃÕáíÊíäº ÚäÏ ÇáÊŞÏíã ÊõãÓÍ `over_capacity` (Êßáíİ íÏæí — ÇáÍŞá ÛíÑ ãÊÊÈÚ İí ÇáÂÑæÏÊ¡ ÈáÇ ÖÌíÌ) İÊÛÇÏÑ ÇáÕİ ÇáØÇÈæÑ Livewire İæÑğÇ.
4. **ÇáæÕæá:** ÑÇÈØ ÔŞíŞ áÜ `order-settings` İí ÇáİÆÉ «ÇáÚãáíÇÊ» ÈÜ `store-sidebar.blade.php` (ãŞíøÏ ÈÜ `ORDER_MANAGE`) + ÒÑ action İí ÈÑíÏ `AssignmentCapacityExhaustedNotification` (`route('merchant.order-distribution-queue', ['store' => $store->slug])`).
5. **ÊÑÌãÉ:** 10 ãİÇÊíÍ ÌÏíÏÉ İí `merchant_panel` ÚÈÑ `ar/en/fr/es`.
6. **ÇáÊÍŞŞ:** `tests/Feature/Merchant/OrderDistributionQueueTest.php` (**8 ÇÎÊÈÇÑÇÊ/47 assertion**) — ÍÇÑÓ ÇáÕáÇÍíÉ¡ ÊÖãíä/ÇÓÊËäÇÁ ÇáÊÈæíÈíä + ÊÑÊíÈ ÇáÃŞÏã ÃæáğÇ¡ ÅÚÇÏÉ Êßáíİ ÍíÉ (ÊÃßíÏ + ÊÊÈÚ) ãÚ ãÓÍ ÇáÚáã¡ ÑİÖ ÚÖæ ÈáÇ ÕáÇÍíÉ¡ ÍÇáÇÊ ÇáİÑÇÛ¡ ËÈÇÊ ÚÏÏ ÇáÇÓÊÚáÇãÇÊ. `tests/Feature/Order` = 30/30¡ `tests/Feature/Merchant` = **583 äÇÌÍ (2495 assertion)**¡ `BladeInteractivityPolicyTest` = 2/2¡ `php -l` + `view:cache` ÓáíãÇä. ÇáİÍÕ ÇáÈÕÑí 375/768/1440 íÈŞì ááãÓÊÎÏã (áÇ ãÊÕİÍ İí ÇáÈíÆÉ).

## ÍÓÇÈ ÇáÏíãæ — ÈíÇäÇÊ ÔÈå ÍŞíŞíÉ + ÃÚÖÇÁ ãÍÏæÏæ ÇáÕáÇÍíÇÊ (äÓÎÉ ÈĞÑÉ 2026-09-18) ?

**ÇáÓíÇŞ/ÇáŞÑÇÑ:** ÈäÇÁğ Úáì ØáÈ «ÌåøÒ ÍÓÇÈ ÇáÏíãæ İí ÇáÓíÏÑ ÈÈíÇäÇÊ ÊÌÑíÈíÉ æÖİ ãæÙİ ÊÃßíÏ İŞØ æãæÙİ ÊÊÈÚ İŞØ æãæÙİ ÊÃßíÏ æÊÊÈÚ æÑÇÌÚ ÇáãÔÑæÚ ßÇãá æÖİ ÈíÇäÇÊ ÊÌÑíÈíÉ» — Êã ÊØæíÑ `database/seeders/DemoStoreSeeder.php` (ÇÒÏÇÏ ãä 447 Åáì **1210 ÓØÑğÇ**) áíÛØí ßá ÇáÊÍÓíäÇÊ ÇáÃÎíÑÉ ÈÈíÇäÇÊ ÕÇİíÉ ááÚÑÖ (ÇáÅÓäÇÏ/ÇáÓÚÇÊ/ÇáÊÌÇæÒ¡ ÇáÊÊÈÚ¡ ÇáÊÃßíÏ¡ bulk validate¡ ÇáãÑÊÌÚÇÊ¡ ÊÚÏíá ÇáÃÓÚÇÑ).

1. **İÑíŞ ÈÕáÇÍíÇÊ ãõÍÏøÏÉ (ãÑÍáÉ 34.4):** ËáÇËÉ ÃÚÖÇÁ `staff` İí ãÊÌÑ `demo` ÈÕáÇÍíÇÊ ãÎÒøäÉ İŞØ (Decision #6) — `demo.confirmer@edzeery.com` (ÊÃßíÏ İŞØ: order.confirm¡ crm.orders.confirm¡ returns.verify.barcode¡ returns.process…)¡ `demo.tracker@edzeery.com` (ÊÊÈÚ İŞØ: crm.orders.track¡ delivery.riders.view…)¡ `demo.dual@edzeery.com` (ÊÃßíÏ+ÊÊÈÚ: íÍãá ÇáãäØŞÊíä æíÙåÑ ßãÑÔÍ «dual_role» İí äÇİĞÊí ÅÚÇÏÉ ÇáÊßáíİ). ßáãÉ ÇáãÑæÑ ááÌãíÚ `password`.
2. **ÅÚÏÇÏÇÊ ÇáãíÒÇÊ:** `enableFeatureSettings` íİÚá `distribution_overflow_enabled=true` + `distribution_overflow_percentage=20` + `allow_price_edit=true`¡ İÊÚãá ÇáÊÌÇæÒ/ÔÇÑÉ «over capacity»/ãÍÑÑ ÇáÃÓÚÇÑ ÎÇÑÌ ÇáÕäÏæŞ.
3. **ŞæÇÚÏ ÇáÊÏæíÑ:** `seedConfirmationShifts` — æÑÏíÇÊ Sun–Thu (ISO 7,1,2,3,4) ÈÓŞæİ: ÊÃßíÏ confirmer 15¡ ÊÊÈÚ tracker 20¡ ææÑÏíÊÇ dual (ÊÃßíÏ 25 + ÊÊÈÚ 25). `seedSpecialistAssignment` — dual ãÊÎÕÕ İí ãäÊÌ Wireless Earbuds Pro (íİÖóøá İí ÇáãÑÔÍíä).
4. **ÔÑßÇÊ ÇáÊæÕíá ÇáãÍáíÉ:** `seedShippingProviders` — Ecotrack (ÇİÊÑÇÖí¡ 450 DA¡ 30kg) æZR Express v2 (400 DA¡ 20kg) ãÑÊÈØÊÇä ÈßÊÇáæÌ ÇáäÇŞáíä ÇáÚÇáãí (ÔÚÇÑÇÊ/ÃáæÇä İí ÔÈßÉ ÇáÊÊÈÚ).
5. **ÚãáÇÁ ÌÒÇÆÑíæä (`seedDemoCustomers`):** 5 ÚãáÇÁ ÈÃÓãÇÁ/åæÇÊİ/ÚäÇæíä æÇŞÚíÉ ÚÈÑ `algeriaLocation` (DZ + æáÇíÉ `state_code` + ÈáÏíÉ ÈÑãÒ ÈÑíÏí¡ ãÚ fallback Âãä ááÈĞÑÉ ÇáãÓÊŞáÉ).
6. **15 ØáÈíÉ (`seedDemoOrders`¡ äØÇŞ 21001–21015 ãÍÌæÒ¡ always-fresh):** ãæÒÚÉ Úáì ßá ÇáÍÇáÇÊ (pending ×3 ãäåÇ 2 ãõÓäÏÉ¡ no_answer_1 ÈãÍÇæáÉ ÇÊÕÇá æåÇÊİ ËÇäæí¡ postponed¡ confirmed ×2 ãäåÇ æÇÍÏÉ ÈÔÑßÉ ãÍÏÏÉ¡ preparing¡ shipped ×2 (äÇŞá + ÑÌøÇá Yacine)¡ out_for_delivery (ÇáÊí ÚáíåÇ `over_capacity=true` + `carrier_validated_at` áÊæÖíÍ bulk validate)¡ delivered ÈÏæÑÉ ßÇãáÉ ÚÈÑ ZR¡ returned ÈÔÑíØ `DEMO-RET-001` ãæËóøŞ+ãõÚÇáóÌ¡ cancelled¡ out_for_delivery ÑÌøÇá Ahmed). ßá ØáÈíÉ: ÃÕäÇİ/ãÌãæÚ/ÚäÇæíä¡ `OrderStatusHistory` (ãÕÏÑ confirmed_by)¡ `OrderEvent` ÈÎØ Òãäí ÚÑÈí ãÊÏÑÌ ÈæÇŞÚíÉ + `reassigned`¡ æ`OrderTracking` + `OrderTrackingHistory` (webhook_token ÚÔæÇÆí¡ ÅÓäÇÏ¡ over_capacity). ÇáÈĞÑÉ ØÇŞíÉ ÇáÅÚÇÏÉ (ÈÇáäÊÇÆÌ äİÓåÇ ÈÚÏ ßá ÊÔÛíá).
7. **ÅÕáÇÍÇä ÇßÊÔİÊåãÇ ÈíÇäÇÊ ÇáÏíãæ (ÊÚáíŞ ÕáÇÍíÉ):**
   - `app/Models/Orders/Order.php` — `confirmedByHistory` ßÇäÊ `->whereHas('status,confirmed')->latestOfMany('created_at')` æÇáİáÊÑ ÎÇÑÌ subquery ÇáÅÌãÇá¡ İíÚØí null ÈãÌÑÏ æÌæÏ ÊÇÑíÎ ÃÍÏË (ÔÍä/ÊÓáíã). ÃÕÈÍÊ `->ofMany(['created_at'=>'max'], closure)` áíÏÎá ÇáİáÊÑ ÏÇÎá ÇáÊÌãíÚ — ÊÊÍá `confirmed_by` Úáì ÇáØáÈíÇÊ ÇáãÊŞÏãÉ ÃíÖğÇ (ÇÎÊÈÇÑ ÊØÈíŞí: 21011 ÇáÏÇÆãÉ íÚÑÖ ÇáãõÄßöøÏ ÇáÂä).
   - ÇáÓíÏÑ íÍĞİ ÍÏË `created` ÇáÊáŞÇÆí ãä `OrderObserver` ÈÚÏ ÇáÍİÙ áíÈŞì ÎØäÇ ÇáÒãäí ÇáÚÑÈí ÇáæÍíÏ (áÇ ÇÒÏæÇÌ `created`).
8. **ÇáÊÍŞŞ:** `php -l` äÙíİ (ÇáÓíÏÑ + Order.php)¡ `view:cache`/`view:clear` äÙíİ¡ `db:seed --class=DemoStoreSeeder` äÇÌÍ (ÚÏøÇÏÇÊ ÍíÉ: 15 ØáÈíÉ/6 ÊÊÈÚ/37 ÓÌá ÍÇáÉ/?95 ÍÏË/4 æÑÏíÇÊ/2 ÔÑßÉ/3 ÃÚÖÇÁ ãÍÏæÏæ ÇáÕáÇÍíÇÊ)¡ `tests/Feature/Order` = 30/30¡ æ`tests/Feature/Merchant` ßÇãáÉ = **575 äÇÌÍ (2448 assertion)** ÕİÑ ÇäÍÏÇÑ (ÈÚÏ ÅÕáÇÍ ÇáÚáÇŞÉ). ÊäÓíŞÇÊ ÃÚÖÇÁ ÇáÏíãæ: `demo.confirmer`/`demo.tracker`/`demo.dual` @edzeery.com.

---

## ãáÎÕ ØáÈ ÇáÏİÚ ÇáãÍÓøä + ÓßÑæá ÈÇÑ ÈåæíÉ ÇáãÊÌÑ + ÇáÔÑßÉ ÇáÇİÊÑÇÖíÉ + ÍÇÑÓ ÇáÓáÉ ÇáİÇÑÛÉ + ÊĞßøÑ åæíÉ ÇáÒÈæä ? (2026-09-18)

**ØáÈ ÇáãÓÊÎÏã (3 ãÍÇæÑ Ëã ãÍæÑÇä ÅÖÇİíÇä):** ? ãáÎÕ ØáÈ ÃİÖá ÈåæíÉ ÇáãÊÌÑ æãÊÌÇæÈ 375/768/1440º ? ÓßÑæá ÈÇÑ ÍÓÈ ÊÎÕíÕ ÇáãÊÌÑ (áæä/ÍÌã/ÎáİíÉ)º ? ÚäÏ ÔÑßÉ æÇÍÏÉ ÊõÍÏóøÏ ÇáÇİÊÑÇÖíÉ ÊáŞÇÆíğÇ ÈáÇ ÅÙåÇÑ ÔÑßÉ ÛíÑ äÔØÉ æáÇ ÇÍÊÓÇÈ ÑÌÇá ÇáÊæÕíá (ÌÏæá ãäİÕá)º ? **ÇáæÕæá áÕİÍÉ checkout íõãäÚ ãÚ ÓáÉ İÇÑÛÉ**º ? **ÇÓã ÇáÒÈæä áÇ íÊÚÈøÃ ÊáŞÇÆíğÇ ãä ÇáÌáÓÉ**.

### Ã. ÅÚÇÏÉ ÊÕãíã ÇáãáÎÕ + ÇáÓßÑæá + ÇáÇÎÊíÇÑ ÇáÊáŞÇÆí (`resources/views/livewire/storefront/order-form.blade.php` + `resources/css/app.css` + `components/storefront/select.blade.php`)

1. **ãáÎÕ ÈåæíÉ ÇáãÊÌÑ:** header ÈÃíŞæäÉ `bag` İí chip `store-bg-primary-soft` + ÔÇÑÉ ÚÏø `store-bg-primary` + ÓØÑ «{{ $cartCount }} items»º ŞÇÆãÉ ÇáÃÕäÇİ ÈİæÇÕá `border-b` æÕæÑ ÈÅØÇÑ 1pxº Şíã `tabular-nums`º **ÇáÅÌãÇáí İí áæÍÉ `store-bg-primary-soft` ÈÇÑÊİÇÚ æÇÖÍ** (`text-xl font-bold store-text-primary tabular-nums leading-none`).
2. **ÔÈßÉ ãÊÌÇæÈÉ:** ÍÇæíÉ `max-w-4xl` ? `xl:max-w-6xl`º `grid-cols-1 lg:grid-cols-3` ? `grid-cols-1 md:grid-cols-5 lg:grid-cols-3 gap-6 md:gap-8`º ÇáÚãæÏ ÇáÃíÓÑ `md:col-span-3 lg:col-span-2`º ÇáãáÎÕ `md:col-span-2 lg:col-span-1` ãÚ `md:sticky md:top-24` (áÇ ÊáÕøŞ ËÇÈÊÉ Úáì ÇáãæÈÇíá). 375px ãßÏøÓ¡ 768px 2/5¡ 1440px ÍÇæíÉ ÃÚÑÖ.
3. **ÓßÑæá ÈÇÑ `.sf-scroll` ÌÏíÏ İí `app.css`** (ÕİÑ JS): `scrollbar-width: thin` + `scrollbar-color`º webkit 6px¡ track ÈÕÈÛÉ 8% ãä `--store-primary`¡ ÅÈåÇã `--store-primary`¡ hover `--store-secondary`¡ äÓÎÉ `.dark` ãİÊøÍÉ ÈãÎáøØ ÃÈíÖ 60–65%. ØõÈøŞ Úáì ŞÇÆãÉ ÇáãáÎÕ (`max-h-64 overflow-y-auto sf-scroll ps-1 -me-1`) æÚáì áæÍÉ ÇáÎíÇÑÇÊ `components/storefront/select.blade.php` (`<ul>` sf-scroll).
4. **ÇáÇÎÊíÇÑ ÇáÊáŞÇÆí:** `availableProviders` íÖíİ `is_default` æíõÑÊóøÈ `orderByDesc('is_default')->orderBy('name')`º İí `mount` æßÊáÉ `@php`: ÔÑßÉ æÇÍÏÉ ? ÊõËÈóøÊ ßÇáÓÇÈŞº ÚÏÉ ÔÑßÇÊ + `is_default` ? ÊõÓÈóŞ ÇáÇİÊÑÇÖíÉ (íÈŞì ááÒÈæä ÊÛííÑåÇ)º ÈáÇ ÇİÊÑÇÖíÉ ? ÇáÓáæß ÇáßáÇÓíßí. **ÈáÇ Ãí `whereNull('rider_name')`** — ÑÌÇá ÇáÊæÕíá ÌÏæá ãäİÕá ÃÕáÇğ. ÕİÑ ÇÓÊÚáÇãÇÊ/round-trips ÌÏíÏÉ (ãä collection ãÍãøáÉ).

### È. ÍÇÑÓ ÇáÓáÉ ÇáİÇÑÛÉ + ÊĞßøÑ åæíÉ ÇáÒÈæä

5. **ÍÇÑÓ ãÓÇÑ checkout** `app/Http/Middleware/Store/EnsureCartNotEmpty.php` (21 ÓØÑğÇ): ÓáÉ İÇÑÛÉ ? `redirect()->route('storefront.home', ['store' => slug])`. ÃõÓÌøá ßÃáíÇÓ `storefront.cart` İí `bootstrap/app.php` æÃõØõÈøŞ Úáì `/checkout` (`routes/storefront.php`) ÈÚÏ `resolve.store` ãÈÇÔÑÉ.
6. **åæíÉ ÇáÒÈæä İí ÇáÌáÓÉ:** ÈÚÏ äÌÇÍ ÇáØáÈ íõÍİÙ `session(['storefront_customer_{storeId}' => name/phone/email])`º İí `mount` ÊõÊÚÈøÃ ÇáÍŞæá ãä ÇáÌáÓÉ (ãİÊÇÍ áßá ãÊÌÑ) ãÚ ÃÓÈŞíÉ ÍÓÇÈ ÇáãäÕÉ ÇáãÓÌøá. ÇÓã/åÇÊİ/ÈÑíÏ ãÚğÇ ááÇÊÓÇŞ.
7. **ÇÎÊÈÇÑÇÊ +4:** `tests/Feature/Storefront/CheckoutAccessControlTest.php` — (1) ÓáÉ İÇÑÛÉ ? redirect ááÑÆíÓíÉ¡ (2) ÓáÉ ããÊáÆÉ ? 200¡ (3) ÊÚÈÆÉ ÇáŞíã ãä ÇáÌáÓÉ¡ (4) ØáÈ äÇÌÍ íÍİÙ ÇáåæíÉ İí ÇáÌáÓÉ.

### ÇáÔåÇÏÉ

- `php -l` äÙíİ (ÇáÜ Middleware ÇáÌÏíÏ)º `view:clear`+`view:cache` ÓáíãÇäº `npm run build` äÇÌÍ (25 ËÇäíÉ¡ ÊÍĞíÑÇÊ Bootstrap Sass ÓÇÈŞÉ).
- **`vendor/bin/pest tests/Feature/Storefront` = 123 äÇÌÍ (430 ÊÃßíÏ)** (119 ŞÏíãÉ + 4 ÌÏíÏÉ)º ßáÇÓÇÊ ÇáÜ cascade ÇáËãÇäíÉ ÚÇáŞÉ.
- **ãáÇÍÙÉ ÈíÆíÉ áßÊÇÈÉ åÇãÉ:** `php artisan test` íÚíÏ ÅØáÇŞ PHP ÈÍÏ ĞÇßÑÉ 512MB (íÑãì `Allowed memory size of 536870912 bytes exhausted` İí `BladeCompiler`/`SortableIterator` ÈÚÏ `StorefrontTemplatesTest` ÑÛã `-d memory_limit=-1` Úáì ÇáÚãáíÉ ÇáÃã). **ÇáÇÓÊÚãÇá ÇáÕÍíÍ Úáì åĞÇ ÇáÌåÇÒ: `php -d memory_limit=-1 vendor/bin/pest` ãÈÇÔÑÉ** (íÑË ÇáÚáã İáÇ ÇäŞØÇÚ). İÍÕñ: `php artisan tinker -d` íØÈÚ `string(-1)` áßä `php artisan test` áÇ.
- **İÔá æÇÍÏ ãÓÈŞ ÛíÑ ãÑÊÈØ ÈÇáÊÚÏíá:** `CarrierSyncObservabilityTest` («the carrier-sync:report…» — `expectsOutputToContain('8')` ãŞÇÈá ÌÏæá CRLF) — íİÔá ãäİÑÏğÇ ÃíÖğÇ ßãÇ İí ÇáÌæáÇÊ ÇáÓÇÈŞÉ.
- **ÇáÊÍŞŞ ÇáÈÕÑí ÇáíÏæí ÇáãõÊæŞóøÚ (375/768/1440):** ? checkout ÈÓáÉ İÇÑÛÉ íæÌóøå İæÑğÇ ááÑÆíÓíÉº ? ÒÈæä ÚÇÏ ÈÚÏ ØáÈ ÓÇÈŞ íÌÏ ÇÓãå/åÇÊİå ããáæÁíä İí äİÓ ÇáãÊÌÑ (æáíÓ İí ãÊÌÑ ÂÎÑ)º ? 375px ãßÏøÓ ÈáÇ sticky¡ 768px ÇáãáÎÕ 2/5¡ 1440px ÃÚãÏÉ ÃÚÑÖº ? ãÊÌÑ ÈÃáæÇä ÎÖÑÇÁ ? ÓßÑæá ÇáãáÎÕ ÃÎÖÑ¡ ÃÑÌæÇäí ? ÃÑÌæÇäíº ? ÚÏÉ ÔÑßÇÊ ãÚ ÇİÊÑÇÖíÉ ? ÊõÓÈóŞ ÊáŞÇÆíğÇ æŞÇÈáÉ ááÊÛííÑ.

---

## Phase 36.3 — «Tiny fix»: İÑíŞ `teams/index.blade.php` ÊÍÊ ÓŞİ 400 ÓØÑ ? (2026-09-21)

ØáÈ ãÈÇÔÑ (áÇ ÊÛííÑ æÙíİí): Çáãáİ ßÇä **402 ÓØÑğÇ** (>400). ÇÓÊõÎÑÌ ÍÑİíğÇ ßÊáÉ ÇáãæÏÇá ÇáÔÑØí áãäÊÌ-ÇáäØÇŞ (`@if ($scopeProduct)` … `@endif`) Åáì ÌÒÆíÉ ÌÏíÏÉ `resources/views/livewire/merchant/teams/partials/product-scope-mount.blade.php` (**6 ÃÓØÑ**) æÍõáø ãÍáåÇ `@include`. ÇáäÊíÌÉ **399 ÓØÑğÇ ? 400** — ÇäÎİÇÖ 3 ÃÓØÑ İŞØ ÈáÇ Ãí ÊÛííÑ İí ÇáãäØŞ/ÇáŞæÇáÈ/ÇáãİÇÊíÍ. `php -l` + `view:cache` ÓáíãÇä.

## Phase 36.4 — äØÇŞ ÑÄíÉ ÇáØáÈíÇÊ/ÇáÊÊÈÚ ÍÓÈ ÇáÚÖæíÉ (`visibleTo`) ? (2026-09-21)

**ÇáØáÈ (ãŞÈæá ÇáÈäÇÁ ãÚ ÇäÍÑÇİ ãæŞøÚ æÇÍÏ):** ÅÖÇİÉ ÍÇÑÓ ÑÄíÉ ÌÏÇÑí ÇÎÊíÇÑí Úáì ŞÇÆãÉ ÇáØáÈíÇÊ æÇáÊÊÈÚ: ÇáãÇáß/ÇáãÏíÑ/ÇáãæÙİ íÑæä äØÇŞğÇ ãÊÏÑÌğÇ¡ ãÚ ÍãÇíÉ `?StoreMembership` (nullable) ÈÏá `StoreMembership` ÇáÕÇÑã áÃä `canStore()` íÚÈÑ İÍÕ `super_admin/admin` ŞÈá æÌæÏ ÚÖæíÉ — æÅáÇ TypeError áãæÙİí ÇáãäÕÉ.

1. **`app/Models/Orders/Concerns/HasVisibilityScope.php`** (53 ÓØÑğÇ¡ trait — ÅÈŞÇÁ `Order.php` ÚäÏ 226 ÇáÅÌãÇáí +ÓØÑíä İŞØ ÈÏá ÊÖÎíã ÇáäãæĞÌ): `scopeVisibleTo(?StoreMembership)` ÛíÑ ÚÇã (íõÓÊÎÏã ÕÑÇÍÉğ İŞØ æáÇ íõİÚóøá ÖãäíğÇ):
   - `null`/ÈáÇ ÚÖæíÉ ? no-op.
   - `TEAM_VIEW` (owner/admin) ? Ïæä áãÓ ÇáÇÓÊÚáÇã.
   - `TEAM_VIEW_OWN` (manager) ? `assigned_to_membership_id IN [self + subordinates]`¡ æÅĞÇ ßÇä áÅÏÇÑÉ ÇáãÍá äØÇŞ ãäÊÌÇÊ (`StoreProductScopeService::assignedProductIds`) íÖíøŞ `whereHas('items', product_id IN …)`.
   - ÛíÑå (staff) ? `assigned_to_membership_id = self`.
2. **äŞÇØ ÇáÑÈØ (5 — ÚÏÏ ÃÏäì¡ ÓØÑ æÇÍÏ áßáòø):** ŞÇÆãÉ ÇáØáÈíÇÊ `orders/index.blade.php` (ãÓÇÑ loading ~798 æãÓÇÑ trash ~1035) ÚÈÑ `user()->storeMembership(currentStore())`º æÔÈßÉ ÇáÊÊÈÚ `app/Livewire/Concerns/TrackingGridConcern.php` (ÇáŞÇÆãÉ ÇáÑÆíÓíÉ ~72¡ ÇáãåãáÉ ~25¡ æÚÏøÇÏ ÇáãåãáÉ `trashCount` ~357) ÚÈÑ `$this->getMembership()` (ãä `TrackingColumnConcern`).
3. **ÇáÖãÇäÇÊ:** ÕİÍÉ ØÇÈæÑ ÇáÊæÒíÚ **áÇ** ÊÓáÓá `visibleTo` (ŞÇÚÏÉ Õİøå ÈÇáÊŞÇØÚ: ÛíÑ ãõÓäÏÉ/over_capacity — ÃËÈÊå ÇÎÊÈÇÑ 6 ÃÏäÇå)º áÇ áãÓ áÃí service/AdminsAssignment ÅáÎ.
4. **`tests/Feature/Merchant/OrderVisibilityScopingTest.php`** (6 ÇÎÊÈÇÑÇÊ/13 ÊÃßíÏ): ? owner íÑì ßá ØáÈíÉ ÈÃí ÅÓäÇÏ ? staff íÑì ãõÓäÏÇÊå İŞØ (áÇ Òãíá/ãÏíÑ/ÛíÑ ãõÓäÏ) ? manager ÈáÇ äØÇŞ ãäÊÌÇÊ: ĞÇÊí + ãõÔÑöİíåã İŞØ ? manager ÈäØÇŞ ãäÊÌ: ØáÈÇÊ ÇáİÑíŞ ÇáÊí ÊÍæí ÇáãäÊÌ ÇáãÚíøä İŞØ ? ÇáÊÊÈÚ: ÇáŞÇÆãÉ ÇáÑÆíÓíÉ/ÇáãåãáÉ/ÇáÚÏøÇÏ áäİÓ ÇáäØÇŞ (ãÚ ãäÍ staff ÕáÇÍíÉ `ORDER_DELETE` İŞØ áÈáæÛ ÓáÉ ÇáãåãáÉ — ÇáÍÇÑÓ «ÕáÇÍíÉ» áÇ íÚØí TEAM_VIEW) ? ØÇÈæÑ ÇáÊæÒíÚ: Õİø áÇ íÊÛíøÑ (ÈáÇ Óáæß ÑÄíÉ ãßÊÓÈ Ãæ ãİŞæÏ).
5. **Êßííİ 3 ÇÎÊÈÇÑÇÊ ŞÏíãÉ ãËøá ÇáÓáæß ÇáÓÇÈŞ (ßÇäÊ ÊİÊÑÖ Ãä staff/manager íÑì ßá ÇáØáÈíÇÊ):** `OrderInlineItemsEditTest` (5 ÍÇáÇÊ staff — ÊãÑíÑ `assignee: $membership` ááØáÈ)¡ `OrderEventLogVisibilityTest` (ØáÈ ÂÎÑ ÛíÑ ãõÓäÏ ÕÇÑ ÛíÑ ãÚÑæÖ ááãÏíÑ ? `toBeNull()` ÈÏá `toBeFalse()`)¡ `OrdersMobileMoreMenuTest` (ÅÓäÇÏ ÇáØáÈ áÚÖæ staff áíÙåÑ ÇáÕİ). **áÇ ÊÛííÑ İí ÇáßæÏ ÇáÎÇÖÚ ááÇÎÊÈÇÑ** — İŞØ ÅÕáÇÍ ÇáİÑÖíÉ.
6. **ÇáÔåÇÏÉ:** `php -l` äÙíİ Úáì ßá ÇáãáİÇÊº **`tests/Feature/Merchant` ßÇãáÉ = 614 äÇÌÍ (2618 ÊÃßíÏ)** ÕİÑ ÇäÍÏÇÑ (607 ÓÇÈŞÉ + 7)º `tests/Feature/Order` + `tests/Feature/Shipping` ÓáíãÇä (ÈÇÓÊËäÇÁ İÔá `CarrierSyncObservabilityTest` ÇáãÓÈŞ ÇáãæËøŞ — `expectsOutputToContain('8')`/ÌÏæá CRLF). ÍÌã `orders/index.blade.php` = **5,641 ÓØÑ < ÓŞİ 5,699** ÇáãæËøŞ. ÇáĞÇßÑÉ: ÇáÊÔÛíá ÇáÕÍíÍ Úáì åĞÇ ÇáÌåÇÒ `php -d memory_limit=2048M vendor/bin/pest` (ãæËøŞ ÃÏäÇå İí ãáÇÍÙÉ 34.5 ÇáÌæáÉ ÇáÓÇÈŞÉ).
7. **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px/768px/1440px Úáì ŞÇÆãÉ ÇáØáÈíÇÊ æÔÈßÉ ÇáÊÊÈÚ ÈäØÇŞóí staff (ÚÑÖ ÇÓãå İŞØ) æmanager (ĞÇÊå + İÑŞå) æowner (Çáßá).

---

## Phase 36.5 — ÍÌÈ ßá ÈíÇäÇÊ ÇáÇÔÊÑÇß/ÇáÎØÉ/ÇáİæÇÊíÑ Úä ÃÚÖÇÁ ÇáãÊÌÑ Ïæä `STORE_BILLING_MANAGE` (2026-09-21)

**ÇáØáÈ (ãÚÊãÏ ãä ÇáãÓÊÎÏã ãÚ ÊÚÏíá æÇÍÏ):** ÇáãæÙİ/ÇáãÏíÑ/ÇáãÔÑİ áÇ íÑì Ãí ÃËÑ áÎØÉ ÇáÇÔÊÑÇß Ãæ ÍÇáÉ ÇáİæÊÑÉ ááãÊÌÑ (ÔÇÑÉ ÇáÎØÉ¡ ÒÑ «ÇáİæÇÊíÑ» ÇáÓÑíÚ¡ ÈØÇŞÉ ÇáÇÓÊåáÇß¡ ÔÑíØ ÇäÊåÇÁ ÇáÇÔÊÑÇß¡ ÈáÇØÉ ÇáÎØÉ İí ŞÇÆãÉ ÇáãÊÇÌÑ) — æáÇ ÍÊì ÚÈÑ ÊÓáÓá ÇáÍÇáÉ İí Livewire (`wire:snapshot`). ÍõÔÑÊ ÇáÕáÇÍíÉ İí ãÓÊÎáÕ ãÔÊÑß `canViewStoreBilling(Store, ?User): bool` ÈäãØ `canStore()`/`canManageTeam()`¡ æÔÑíØ ÇáäÈÃ ãÎİí **ÈÇáßÇãá** ááãæÙİ (ÈÏæä äÓÎÉ ÈáÇ ÑÇÈØ).

1. **ÍĞİ ãáİóíä ãíøÊíä ÈáÇ Ãí ãÑÌÚ (grep ÕİÑí):** `app/Services/Stores/SubscriptionAlertService.php` + `resources/views/filament/merchant/modals/subscription-alerts.blade.php`.
2. **`app/Helpers/helpers.php`:** `canViewStoreBilling(Store $store, ?User $user = null): bool` — `$store->user_id === $user->id` (ãÇáß) Ãæ `$user->storeMembership($store)?->can(\App\Enums\Store\StorePermissionEnum::STORE_BILLING_MANAGE->value)`.
3. **ÇáãäÚ ÇáËáÇËí:**
   - `choose-store.blade.php` — ÍÇáÉ `canViewBilling` (íõÍÓÈ ÈæÌæÏ ãÊÌÑ `billing_visible`)¡ æ`billing_visible` áßá ãÊÌÑº ÔÇÑÉ ÇáÎØÉ æÒÑ «ÇáİæÇÊíÑ» æÈØÇŞÉ ÇáÇÓÊåáÇß (İÑÚÇ ÇáãÇáß/ÇáãÓÊÎÏã ÇáÚÇÏí) æÓØÑ ÎØÉ ÇáãÇáß áßá ãÊÌÑ ßáåÇ ÏÇÎá ÇáÔÑØ. **ŞØÚ ÇáÊÓÑíÈ İí ÇáÓíÑíÇáÇíÒ:** ÚäÏ `!canViewBilling` ÊõİÑóøÛ `subscription`/`maxStores`/`isUnlimited` æÊõÕİóøÑ `plan_name`/`plan_status` İí ÎÑíØÉ ÇáãÊÇÌÑ.
   - `components/layouts/panel.blade.php` — ÔÑíØ ÇäÊåÇÁ ÇáÇÔÊÑÇß ãÔÑæØ ÈÜ`hasStoreContext() && canViewStoreBilling(currentStore())` İíÈŞì ÇáãÇáß İí ÇáÓíÇŞ ßÇáÓÇÈŞ æÇáãæÙİ áÇ íÑÇå ÅØáÇŞğÇ.
   - ŞÇÆãÉ `account.stores` — `StoreCardData::billingVisible` + `GetStoreCardsAction` íãÑøÑåÇ¡ ÈáÇØÉ ÇáÎØÉ ÊÍÊ `@if ($store['billing_visible'])` æÎÇäÉ `planName` ÊõİÑóøÛ (``) ÚäÏ ÚÏã ÇáÅÙåÇÑ áßÓÑ ÊÓÑíÈ `wire:snapshot` («Trial»).
4. **`tests/Feature/Merchant/BillingVisibilityScopingTest.php` (6 ÇÎÊÈÇÑÇÊ/26 ÊÃßíÏ):** ? helper íãäÍ ÑÄíÉ ÃÎÑì ? choose-store áãæÙİ ÈáÇ ÕáÇÍíÉ: áÇ íÑì ÎØÉ/ÇÓÊåáÇß/İæÇÊíÑ ãÚ `assertSet('canViewBilling', false)` æ`stores.0.billing_visible=false` ? choose-store ááãÇáß: íÑì ÇáÎØÉ æÇáÇÓÊåáÇß ? ÚÖæ ãõÕÑóøÍ ÕÑÇÍÉğ ÈÇáÕáÇÍíÉ: íÑì ÇáÎØÉ ? ÇáÈÇäÑ: ÇáãÇáß ÈáÇ ÇÔÊÑÇß íÑÇå¡ ÇáãæÙİ áÇ — ãÚ ÍĞİ ÇÔÊÑÇß ÇáÊÌÑÈÉ ÇáÊáŞÇÆí (ßá ãÓÊÎÏã ÌÏíÏ íÃÎĞ æÇÍÏÉ ÚÈÑ `User::booted`) ? ÈáÇØÉ ÇáÎØÉ İí ŞÇÆãÉ ÇáãÊÇÌÑ ááãÇáß İŞØ.
5. **ÃÓÈÇÈ ÅÕáÇÍÇÊ ÃËäÇÁ ÇáÊäİíĞ:** «Billing» İí İÍÕ `assertDontSee('Billing')` ÊÕÇÏã ÇÓã ÇáãÊÌÑ ÇáËÇÈÊ «Billing Visibility Store» (ÃõÚíÏÊ ÊÓãíÊå «Visibility Store»)º ÇáÈÇäÑ áã íÙåÑ ááãÇáß ÈÓÈÈ ÇÔÊÑÇß ÇáÊÌÑÈÉ ÇáÊáŞÇÆí (`hasActive=true`)º «Trial» ÊÓÑøÈ ÚÈÑ `wire:snapshot` ÑÛã ÅÎİÇÁ ÇáæÓã.
6. **ÇáÔåÇÏÉ:** `php -l` äÙíİ Úáì ßÇİÉ ÇáãáİÇÊ ÇáãÚÏøáÉº **`tests/Feature/Merchant` ßÇãáÉ = 620 äÇÌÍ (2644 ÊÃßíÏ)** (614 ÓÇÈŞÉ + 6)º `tests/Feature/Account` + `tests/Feature/Auth` = 42 äÇÌÍº grep ÕİÑí áÜ`SubscriptionAlertService|subscription-alerts`º `view:clear` ŞÈá ßá ÊÔÛíá. áÇ ÊÓáíã ÈÏæä ØáÈ ÕÑíÍ¡ æãáÇÍÙÉ (ÇÎÊíÇÑí) ááãÓÊÎÏã: ÊÍŞŞ ÈÕÑí ãä `choose-store` æ`account/stores` ÈÍÓÇÈ ãæÙİ.

---

## Phase 36.6 — ãÑÌÚ ÊæËíŞí: `ROLES_PERMISSIONS.md` (2026-09-21)

**ÊæËíŞ ãÍÖ — ÕİÑ ÊÛííÑ ßæÏ.** ÃõäÔÆ `ROLES_PERMISSIONS.md` İí ÌĞÑ ÇáãÓÊæÏÚ: ãÑÌÚ ÇáÊÕãíã ÇáãÚÇÏ áÃÏæÇÑ/ÕáÇÍíÇÊ ÇáãÊÌÑ — ÇáäãæĞÌ ÇáÍÇáí (47 ÕáÇÍíÉ ãÌãøÚÉ ÈÜ`group()` + 4 ÃÏæÇÑ) æÇáãÕİæİÉ ÇáãÓÊåÏİÉ (Manager `-5`¡ Staff `-3`¡ Owner/Admin ËÇÈÊÇä)¡ ŞÑÇÑ ÅÒÇáÉ `CRM_ORDER_CONFIRMATION` (ãÚ **ãÕÏÑíä ÌÏíÏíä** ÇßÊÔİåãÇ ÇáÊÍŞŞ ÇáÍí: `DemoStoreSeeder.php:498/:522` æ`PermissionGroupMeta.php:97`)¡ İÌæÇÊ ÇáÊÑÌãÉ ÇáãÄßÏÉ (order.assign / order.dispatch_validate / order.delete.final ÛÇÆÈÉ¡ team.view ãßÓæÑÉ)¡ ØáÈÇÊ ÇáæÕİ ááÜ Hub (order.manage = 97 ÅÔÇÑÉ¡ store.billing.manage)¡ ãÚÇãáÉ (Soon) (ŞÑÇÑ ÇáãÓÊÎÏã ÈÊÇÑíÎ 2026-09-21: «ÔÇÑÉ ŞÑíÈğÇ» — ãÚ ÊÕÍíÍ: `returns.*` ÍíøÊÇä æÙíİíğÇ)¡ æãÄÌøáÇÊ ÎÇÑÌ ÇáäØÇŞ (`ORDER_ASSIGN` ÈáÇ äØÇŞº ÇÒÏæÇÌíÉ ÅÒÇáÉ ÇáÚÖæ `canModifyMember`/Policy)¡ æÊÓÑíÈ `account/billing.blade.php` (ÅÕáÇÍ ãŞÊÑÍ: `storesOwned()`). ÇáØÈŞÇÊ ÇáËáÇË ãİÕæáÉ İí ÇáŞÓã 10 ãä ÇáãÓÊäÏ.

---

## Phase 36.7 — ÊäİíĞ ÇáØÈŞÉ A ãä `ROLES_PERMISSIONS.md` (2026-09-21)

1. **ãÕİæİÉ `StoreRoles.php`:** Manager `-5` (ÃÓŞØ `ORDER_CONFIRM` æ`CRM_ORDER_TRACKING` æ`PRODUCT_DELETE` æ`DELIVERY_PRICING_MANAGE` — ÊÈŞì İŞØ `CRM_INVENTORY_*` İí ßÊáÉ CRM) Ãí 31?26º Staff `-3` (ÃÓŞØ `ORDER_CANCEL` æ`CRM_ORDER_TRACKING` æ`crm.orders.confirm`) Ãí 10?7 (ÊÃßíÏ İŞØ). Owner/Admin ËÇÈÊÇä.
2. **ÅÒÇáÉ `CRM_ORDER_CONFIRMATION` äåÇÆíğÇ (ŞÇÆãÉ ÊÍŞŞ ÇáŞÓã 4¡ files ÃæáğÇ Ëã Enum):** `StoreRoles.php` (ãÏíÑ + ãæÙİ)¡ `dashboard.blade.php:16` (ÇäÍÓÇÑ OR Åáì `ORDER_CONFIRM` İŞØ)¡ `DemoStoreSeeder.php:498/:522`¡ `PermissionGroupMeta.php:97` (ãä `DEPENDENCIES`)¡ ÇáãİÊÇÍ ÇáãÊÏÇÎá `crm.orders.confirm` İí `permissions.php×4`¡ Ëã ÍĞİ ÇáÍÇáÉ ãä `StorePermissionEnum.php:82`. **grep ÕİÑí** İí `app/`+`resources/`+`database/`+`tests/` (ÈŞíÊ İŞØ ÊÃßíÏÇÊ ÚÏã ÇáæÌæÏ İí ÇáÇÎÊÈÇÑ ÇáÌÏíÏ).
3. **İÌæÇÊ ÇáÊÑÌãÉ ÇáÃÑÈÚ (`permissions.php×4`):** ÃõÖíİ `order.assign` æÅÛÇËÉ `order.dispatch_validate`º ÃõÚíÏ åíßáÉ `order.delete` Åáì `['label'=>.., 'final'=>..]` (ãÑÂÉ ÕíÇÛÉ `store.delete.final`) æ`team.view` Åáì `['own'=>.., 'label'=>..]`º æÃõÖíİ İÑÚ ãÚÇáÌÉ ÇáãÕİæİÇÊ Åáì `PermissionGroupMeta::label()` (İáØÍÉ `label`/`own`) — ÈáÇ ãÓÊåáßíä ÎÇÑÌ ÇáÜ Hub áÊİÇÏí ßÓÑ ÇáäÕí ÇáãÈÇÔÑ.
4. **ÃæÕÇİ ÇáÜ Hub:** ãáİÇÊ ÌÏíÏÉ `permissions_descriptions.php×4` (order.manage + store.billing.manage)¡ `PermissionGroupMeta::description()`¡ æÚÑÖ ÇáæÕİ ÊÍÊ ÊÓãíÉ ÇáÕİ İí `permission-group-modal.blade.php` (ãÚ `description` İí Õİæİ `activeGroupMeta`).
5. **ÎÕæÕíÉ `account/billing.blade.php:71`:** ÇÓÊõÈÏá `$u->stores()` (ßá ÇáÚÖæíÇÊ ÇáäÔØÉ Ãí ÏæÑ) ÈÜ`$u->storesOwned()->with('payments')` — ÚÖæ ÈáÇ `store.billing.manage` áÇ íÑì ÈáÇØÉ ãÊÌÑò áÇ íãáßå.
6. **ÕáÇÍíÇÊ (Soon):** `PermissionGroupMeta::COMING_SOON = ['accounting.confirm.team']` + `isComingSoon()` + guard İí `togglePermission` + Õİ ãßÊæã/ÎÇäÉ ãÚØøáÉ + ÔÇÑÉ `teams.soon_badge×4` («ŞÑíÈğÇ»/«Coming soon»/«Bientôt»/«Pr?ximamente»)º ÃõÒíáÊ ÊÚáíŞÇÊ (Soon) ÇáŞÏíãÉ (`// soon` Úáì `ACCOUNTING_CONFIRM_TEAM` æÑÃÓ «Verification / Returns (Soon)») áÃä `returns.*` ÍíøÊÇä æÙíİíğÇ.
7. **ÇáÇÎÊÈÇÑÇÊ:** ÌÏíÏ `tests/Feature/Merchant/StoreRolesTemplateTest.php` (8: ŞÇáÈ ÇáãÏíÑ ?5¡ ŞÇáÈ ÇáãæÙİ ?3¡ ÛíÇÈ ÇáÍÇáÉ ãä ÇáÜ Enum¡ Íáø ãİÇÊíÍ ÇáÕáÇÍíÇÊ ÇáÃÑÈÚÉ Åáì äÕæÕ İí ÇááÛÇÊ ÇáÃÑÈÚ¡ æÚäŞæÏ ãäÚ `order.delete` ÇáãÊÏÇÎá ãä ßÓÑ ÅÎæÊå)º ÌÏíÏ «billing page lists only stores the user owns» İí `BillingTest.php`º ÊÍÏíË **ãÓäÏ** `TrackingBulkActionsTest` (ÇáãÏíÑÇä íÍÕáÇä ÇáÂä Úáì ãäÍÉ `CRM_ORDER_TRACKING` ÇáÕÑíÍÉ áíÈŞíÇ ãÑÔøÍóí ÅÚÇÏÉ ÊæÒíÚ) æÊÍÏíË ÊÚáíŞ ÇáÇäÍÏÇÑ İí `StoreAuthorizationGatesTest`.
8. **ÇáÔåÇÏÉ:** `php -l` äÙíİ Úáì ßÇİÉ ÇáãáİÇÊ (21 ãáİ PHP/ÌÒÁ Blade)º `view:clear` ŞÈá ßá ÊÔÛíáº `tests/Feature/Merchant` ßÇãáÉ = **628 äÇÌÍ (2711 ÊÃßíÏ)** (620 + 8 ÇáÌÏÏ)º `tests/Feature/Account` + `Auth` = **43 äÇÌÍ (112 ÊÃßíÏ)** (42 + 1)º ÓØøÑ `Implemented: 2026-09-21` İí äåÇíÉ `ROLES_PERMISSIONS.md`. — ãáÇÍÙÇÊ ÇÎÊíÇÑíÉ: «dangerous_badge/dangerous_hint/requires/group_select_all/group_clear» ÛíÑ ãæÌæÏÉ İí `teams.php×4` (ÈŞÇíÇ ãÑÍáÉ 36.1 ÊõÚÑÖ ßãİÇÊíÍ ÎÇã İí ÇáÜ Hub — ÎÇÑÌ äØÇŞ 36.7).

---

## Phase 36.8 — ÊæÍíÏ ÊæÓÊ SweetAlert2 ãÚ åæíÉ ÇáãÔÑæÚ (ÃíŞæäÇÊ + ãæÖÚ) ? (2026-09-21)

**ØáÈ ÇáãÓÊÎÏã (ãÚÊãÏ «äİĞ» ÈÚÏ ÚÑÖ ÇáÎØÉ + ŞÑÇÑÇä ÚÈÑ ÃÏÇÉ ÇáÓÄÇá):** ÇáÃíŞæäÉ æãßÇäåÇ İí ÇáÊæÓÊ ÛíÑ ãÊäÇÓŞíä ãÚ ÇáåæíÉ ÇáãÚÊãÏÉ. ÇáŞÑÇÑÇä: **(1) ÇáãæÖÚ = ÃÓİá ÇáÒÇæíÉ** (bottom-start İí RTL / bottom-end İí LTR¡ ãØÇÈŞ áÊæÓÊÇÊ ÇáãÊÌÑ ÇáÃÕáíÉ `edz-notice`/`cartToast` İí ÇáÃÓİá)¡ **(2) ŞäÇÉ `swal:toast` ßá ÃäæÇÚåÇ (äÌÇÍ/ÎØÃ/ÊÍĞíÑ/ãÚáæãÉ) ÊõÚÑÖ ÊæÓÊğÇ ãÏãÌğÇ** áÇ ãæÏÇáğÇ.

**ÇáÊÔÎíÕ ÇáÌĞÑí:** `swal.js` ßÇä íãÑÑ `iconHtml: undefined` İÃíŞæäÇÊ SweetAlert2 ÇáÇİÊÑÇÖíÉ (åäÏÓÉ ãÈäíÉ áÍÇæíÉ 5em?80px ãÓÌáÉ İí `sweetalert2.css:647-801`) ÊõÚÑÖ ÏÇÎá ÔÇÑÉ ãŞáøÕÉ 32–40px ? ÚáÇãÇÊ ãßÓæÑÉ/ãÒÇÍÉº `swal:toast` (199 ãæÖÚ ÈË) íÚÑÖ ßá ÛíÑ-success ãæÏÇáğÇ æÓØğÇ ÈÒÑ OK ÑÛã ÇÓãåÇº æÍÔæÉ ÇáÊæÓÊ `padding:0` ? ÇáÊÕÇŞ ÇáãÍÊæì ÈÇáÍÇİÉº `[dir=rtl] .swal2-icon {margin:0!important}` ßÇä íßÓÑ ÊæÓíØ ÃíŞæäÉ ÇáãæÏÇá.

1. **`resources/js/swal.js`:** `EDZ_SWAL_ICONS` — ÎÑíØÉ 5 ÃíŞæäÇÊ SVG ãÖãøä ÈãÓÇÑÇÊ **ãØÇÈŞÉ ÍÑİíğÇ áÜ`x-edz.icon`** (check-circle/x-circle/exclamation-triangle/info-circle/help-circle¡ `stroke="currentColor"`, ÚÑÖ ÎØ 1.5) ÊõÍŞä ÚÈÑ `iconHtml: rest.iconHtml ?? EDZ_SWAL_ICONS[t]`º `toastPosition()` ? `bottom-start`/`bottom-end`º **ÌÏíÏÉ `EdzSwal.toast(options)`** (toast:true ÏÇÆãğÇ¡ position ÇáÃÓİá¡ timer 3500 / ÎØÃ 5000¡ ÈáÇ ÃÒÑÇÑ)º ãÚÇáÌ `swal:toast` ? `EdzSwal.toast` æ`failed-validation` ? `EdzSwal.toast({type:'error'})`º `timerProgressBar`/`showConfirmButton` ÕÇÑÇ íõÍÊÑãÇä ÇÓÊÌÇÈÉğ áÜ`rest.*` (ßÇäÇ ãŞÓæãíä ÈŞíãÉ ËÇÈÊÉ İíİÓÏÇä ÇáËæÇÈÊ). ŞäÇÉ `swal` (81 ãæÖÚ ÈË) ÈáÇ ÊÛííÑ (ÎØÃ/ÓÄÇá = ãæÏÇá æÓØ¡ äÌÇÍ = ÊæÓÊ).
2. **CSS ÈÇáÊæÇÒí İí `resources/css/components/_swal.scss` æ`resources/css/app.css`:** ÇáÔÇÑÉ ŞÑÕ ãØİÃ ÈáÇ ÍÏæÏ (ÈÏá ÊÃØíÑ 2px) — 48px İí ÇáãæÏÇá / 32px İí ÇáÊæÓÊ¡ `rounded-full`¡ `flex` ÊæÓíØ¡ `svg 1.5rem`/`1.125rem`¡ ÃáæÇä per-type light (`#dcfce7/#16a34a`, `#fee2e2/#dc2626`, `#fef3c7/#d97706`, `#dbeafe/#2563eb`, `#e0e7ff/#6366f1`) ædark (ÃáİÇ 900/35 ãÚ äÕ 400-scale)º ÇáÊæÓÊ `padding:0.75rem 1rem` + `gap:0.75rem` + `max-width:360px` (æÚäÏ ?639px `calc(100vw - 1.5rem)`) + ÇáÚäæÇä/ÇáäÕ `margin:0` + `min-width:0` + `max-height:40vh; overflow-y:auto` ááŞæÇÆã ÇáäÕíÉ (ÊæÓÊ `syncAllTracking`); ÔÑíØ ÇáãÏÉ ÈäÕİ ŞØÑ ÇáßÈÓæáÉº ÍõĞİÊ ŞæÇÚÏ ãíÊÉ (ÊÕÛíÑ `success-line`, ÎÏÚÉ `[dir=rtl] .swal2-icon`, ãõÍÏÏ `swal2-timerprogress-bar` Çáæåãí) æÇÓÊõÈÏá `margin:0!important` ÈÜ`margin:0 auto` áÊæÓíØ ÃíŞæäÉ ÇáãæÏÇá ÃİŞíğÇ.
3. **ÇáÔåÇÏÉ:** `npm run build` äÇÌÍ (ÊÍĞíÑÇÊ Sass)º İÍÕ `public/build` ãÈÇÔÑÉ (ÇáãáİÇÊ ÇáãÈäíÉ app-BIaEA5q4.css ááæÍÉ + app-iEjkz8ke.css ááãÊÌÑ): `swal2-icon svg {1.5rem/1.125rem}` ÍÇÖÑÉ¡ toast base `padding:.75rem 1rem;gap:.75rem;align-items:center`¡ `max-width:calc(100vw - 1.5rem)`¡ timer `border-radius:0 0 .875rem .875rem`¡ dark `background:#14532d59` (=rgba(20,83,45,.35))¡ æÕİÑ ãä `swal2-success-line`/`swal2-timerprogress-bar`/`border-width:1.5px` (ÈŞíÊ .border-[1.5px] ÇáÎÇÕÉ ÈÜTailwind İŞØ). ÔÑíÍÉ `swal-*.js`: ÇáÃíŞæäÇÊ ÇáÎãÓ (ÚáÇãÇÊ `9 12.75 11.25 15 15 9.75`, `9.303`, `1.063.852`, `9.879 7.519`) + `bottom-start`. ÚÏøÇÏ ÇáÈË Ïæä ÊÛííÑ (`swal`=81¡ `swal:toast`=199) — ÇÎÊÈÇÑÇÊ Feature ÊİÍÕ payloads İŞØ.
4. **íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ ãä ÇáãÓÊÎÏã (áÇ íãßä ÚÈÑ CLI):** 375px/768px/1440px — ÊæÓÊ äÌÇÍ ÃÓİá ÇáÒÇæíÉ ÈÔÚÇÑ ÏÇÆÑí ÃÎÖÑ ãØİÃ¡ ÊæÓÊ ÎØÃ (ÊÍŞŞ İÖáí + ŞÇÆãÉ html) íÈŞì ßÈÓæáÉ ÈáÇ ÃÒÑÇÑ¡ ÊæÓÊ warning/ÈÅÈØÇá ãßÊÈ ÈÊæÓÊ¡ ãæÏÇá ÊÃßíÏ (question) ÃíŞæäÊå ãÑßÒÉ ÈÔÚÇÑ indigo¡ æÇáæÖÚ ÇáÏÇßä İí áæÍÊíä ÇááæÍÉ æÇáãÊÌÑ.

---

## Phase 38 — ÇáÃÏÇÁ: İÕá ÍÒã Landing/Guest (ÇáãÑÍáÉ 1) ? (2026-09-22)

**ÇáåÏİ ÇáãÚÊãÏ:** ÅÒÇáÉ ~1.9MB ãä ÇáÌÇİÇÓßÑÈÊ ÇáãäŞæá Úáì ÕİÍÇÊ ÇáåÈæØ æÇáÏÎæá æÖÛØ CSS¡ ÚÈÑ ÍĞİ ÇáÍÒã ÇáŞÏíãÉ æÅÈŞÇÁ ÎØæØ ÇáÃíŞæäÇÊ ÍíÉ Úáì ÇáãÊÌÑ İŞØ.

**ÇáÍŞÇÆŞ ÇáãÄßÏÉ ÈİÍÕ İÚá:** `landing-layout:30` æ`guest:23` ßÇäÇ íÍãáÇä `app.js` (1,168KB: axios+swal+iconify+ApexCharts+flatpickr+FullCalendar+lucide+edzDirty) æ`guest:23` ÅÖÇİÉ Åáì `panel.js` (241KB ãßæøäÇÊ ÇááæÍÉ ÇáÜ16 ÈáÇ ÇÓÊÎÏÇã). grep ÕİÑí áÇÓÊÎÏÇã ApexCharts/flatpickr/FullCalendar/lucide/axios İí blades ÇáÕİÍÇÊ ÇáÚÇãÉ¡ æáÇ `#chartOne/#mapOne/#calendar` İí Ãí view¡ prism ÈáÇ ÇÓÊÎÏÇã¡ FA/bi ãÓÊÎÏãÇä İŞØ İí storefront (`product-detail:199/257/522`, `single-product:123`, `catalog:198`, `order-form:652/659` ÚÈÑ `IconManager::render`)¡ `edzDirty` ãÓÌøá ãßÑÑğÇ (app.js:17+panel.js:301) æ`confirmLeave` ÈáÇ ÇÓÊÏÚÇÁ.

**ãÇ äõİöøĞ:**
1. **`vite.config.js`** — ÅÖÇİÉ `guest.js`+`landing.js`º ÍĞİ `app.js` (ÃÕÈÍ ãíÊğÇ — ÂÎÑ ãÑÌÚÇÊå ÇáÊÚáíŞÇÊ/ÇáÊæËíŞ).
2. **`resources/js/guest.js`** (1KB) — `edzDirty` İŞØ.
3. **`resources/js/landing.js`** (38.8KB + swal 83.5KB ãÔÊÑß) — swal+iconify+AOS ÈäİÓ ÎíÇÑÇÊ `AOS.init` ÇáÓÇÈŞÉ.
4. **`resources/js/storefront.js`** — ÇÓÊíÑÇÏ FA + bootstrap-icons (ÎØæØ ÇáÃíŞæäÇÊ ÊÎÑÌ chunk CSS ÊáŞÇÆí `storefront-dqBiEyH7.css` 163KB). **ÏÑÓ ãÚãÇÑí:** ãÏÎá CSS ÈÜ `@import "pkg/css"` İÔá İí Vite Úáì æíäÏæÒ ("Unclosed string") — ÇáÍá ÚÈÑ ÇÓÊíÑÇÏ JS.
5. **`resources/css/app.css`** — ÍĞİ @importÇÊ ÇáÃÓØÑ 1-5 (ÎØæØ×2 ÈáæßíÉ¡ prism¡ FA¡ bi).
6. **`resources/css/base/_typography.scss:7`** — ÍĞİ @import ÇáÎØæØ (ßÇä íÍæí æÒä `590` ÛíÑ ÕÇáÍ).
7. **`components/edz/fonts.blade.php`** (ÌÏíÏ) — ÎØæØ ÛíÑ ÈáæßíÉ (preconnect+`media="print" onload`). **ÏÑÓ Blade:** `wght@{{ }}` ÊõØÈÚ ÍÑİíğÇ áÃä `@{{` escape ? ÃõÕáÍ ÈÈäÇÁ URL ÚÈÑ `@php` Ëã `{{ $fontsUrl }}`.
8. **layouts:** guest:23 ? `app.css+guest.js+edz-loader` (ÍĞİ panel.js)º landing:30 ? `landing.js`º app.blade:23 (ãíÊ) ? `landing.js`º panel?`<x-edz.fonts weights="400;500;600;700" />`º storefront Ïæä ÊÛííÑ ãÑÇÌÚ + fonts ÚÈÑ partial.
9. **`package.json`** — ÍĞİ `prismjs`.

**ÇáÃËÑ ÇáãŞíÓ (public/build¡ `npm run build` äÇÌÍ):** Guest: JS ~1950KB?~4KB¡ CSS 292?128KB. Landing: JS ~1291KB?~125KB. Storefront: FA/bi İí chunk ãÓÊŞá ÈäİÓ ÇáÍÌã ÊŞÑíÈğÇ + ÎØæØ async. İÍÕ MStestal: `/` (landing) 200 íÍãá landing.js áÇ app.js/panel.jsº `/login` 200 íÍãá guest.js İŞØº storefront ÚÈÑ Host `default-store.edzeery.com` 200 íÍãá icons chunk+swal+fonts async. `app-DxMIUNTW.css` ÈáÇ `.fa-`/`.bi`/`@import url(`. public/build ÈáÇ ÃÕæá stale.

**íÊØáÈ ÊÍŞŞğÇ ÈÕÑíğÇ íÏæíğÇ (ŞÇÚÏÉ ÇáãÓÊÎÏã #2):** 375/768/1440 — landing (AOS + iconify + ionicons)¡ login/register/create-store (edzDirty + ÔÇÑÇÊ heroicon)¡ ÕİÍÉ ãäÊÌ storefront (ÔÇÑÇÊ `bi-*` ãËá `bi-star-fill` æÃíŞæäÇÊ order-form). ÅÒÇáÉ ÇáÎØæØ ÇáÈáæßíÉ ÊÍÓøä LCP æŞÏ ÊõÍÏË FOIT Øİíİ ãÄŞÊ — ÑÇÌÚåÇ ÈÕÑíğÇ.

**ãŞÊÑÍÇÊ ÇáãÑÍáÉ ÇáŞÇÏãÉ (áã ÊõáãÓ):** ÇÓÊÈÏÇá `set="fa"|"bi"` İí storefront ÈÜ heroicon/ion (—~380KB ÎØæØ ÃíŞæäÇÊ ãä critical path)¡ ÊÍæíá html5-qrcode (366.7KB ãÚ ßá ÕİÍÉ áæÍÉ) Åáì dynamic-import¡ ÅÓŞÇØ axios ãä storefront.js¡ flatpickr ÍÓÈ ÇáØáÈ.
---

## Phase 39 - ÅÕáÇÍÇÊ ÇáİÍÕ ÇáÈÕÑí: Alpine + ÇáÃíŞæäÇÊ + ÇáÊæÍíÏ Çááæäí + ÇáÓÈíäÑ (2026-09-22)

**ÇáåÏİ:** ÅÕáÇÍ ãÇ ÃÈáÛ Úäå ÇáãÓÊÎÏã ÈÚÏ ÇáİÍÕ ÇáÈÕÑí: (1) Alpine ãÚØøá İí landing (FAQ collapse¡ ÊÈÏíá billing¡ ŞÇÆãÉ ÇáãæÈÇíá¡ İæÑã ÇáÊæÇÕá - æÇİŞ: ÊËÈíÊ collapse)¡ (2) ÃíŞæäÇÊ äÇŞÕÉ İí ÈÚÖ ÇáÃÒÑÇÑ¡ (3) ÃáæÇä ÛíÑ ãÊäÇÓŞÉ dark/light ãä ßáÇÓÇÊ ÛíÑ ãÚÑøİÉ¡ (4) ÊæÍíÏ ÓÈíäÑ ÇáÍãá Úáì ßá ÃÒÑÇÑ login/create-store/landing.

**ÇßÊÔÇİ ÌĞÑí:** pp.js ÇáŞÏíãÉ áã ÊÓÊæÑÏ Alpine ÃÕáğÇ (bootstrap.js İíåÇ axios İŞØ) ælanding-layout ÈáÇ Livewire/CDN => x-data ßÇäÊ ãÚØáÉ ÓÇÈŞğÇ ÃíÖğÇº ÇáÍá İí landing.js.

**Êã ÊäİíĞå:**
1. **
pm install @alpinejs/collapse@^3.15.6** + 
esources/js/landing.js: ÇÓÊíÑÇÏ Alpine + collapse + Alpine.start() + initNativeButtonLoading().
2. **
esources/js/native-button-loading.js** (ÌÏíÏ): ÓÈíäÑ Úáì ÇáäãÇĞÌ ÇáäØÈíÉ orm:not([x-data]) æÑæÇÈØ [data-edz-loading] (İáÊÑ flicker 150ms + .edz-btn--loading/__ring/__hide). ãõÓÊæÑÏ İí guest.js ælanding.js (chunk ãÔÊÑß ~1.9KB İŞØ).
3. **_buttons.scss**: ŞæÇÚÏ ÚÇãÉ .edz-btn__ring/.edz-spinner ÎÇÑÌ .edz-btn.
4. **ÇáÃíŞæäÇÊ**: plans CTA (rrow-forward-outline + data-edz-loading)¡ hero register/dashboard + final-cta register/contact (data-edz-loading)¡ navbar logout ×2 (log-out-outline + inline-flex gap)¡ choose-store upgrade (rrow-up + data-edz-loading) æcreate-new (data-edz-loading).
5. **ÓÈíäÑ ÇáÊæÇÕá**: ÇÓÊÈÏÇá SVG ÇáíÏæí ÈÜ .edz-spinner İí landing/contact.blade.php.
6. **ÊæÍíÏ ÇáÃáæÇä (27 ãáİğÇ + íÏæí):** ÊÓæíÉ ßá ßáÇÓÇÊ ÇáÜ tokens (g-surface*/	ext-ink*/order-surface-border) ÈáÇ ÈÇÏÆÉ dark: (tokens ÊÊÈÏá ÊáŞÇÆíğÇ ÊÍÊ .dark) İí Landing+sections æauth/* æcomponents (auth/layouts/header/ecommerce/dropdown/nav-link/responsive-nav-link/secondary-button/sidebar-link/text-input/edz) æfooter + ÈŞÇíÇ storefront ÎÇÑÌ ÇáäØÇŞ (store-settings:455 + label-print-modal). ÊæáíÏ ÎÑíØÉ ÇÓÊÈÏÇá ßÇãáÉ ({light dark:}?token Ëã ÇáİÑÏíÉ)¡ **ÇáäÊíÌÉ: ÕİÑ ßáÇÓÇÊ ÛíÑ ãÚÑøİÉ** İí 
esources/views.
7. ÊäÙíİ: lang-switcher (indigo?brand¡ Êæßä ÇáÃáæÇä)¡ dark-toggle (Êæßä)¡ choose-store usage-bar¡ footer (gray?tokens).

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ (public/build ÈÚÏ 
pm run build):** landing.js 87KB (íÍÊæí Alpine+start+collapse) ?¡ guest.js ~1KB + shared native-button-loading 1.9KB ?¡ pp-DAlgERXa.css íÍÊæí .edz-btn__ring+.edz-spinner ?. İÍÕ ãíÏÇäí: / (landing) 200¡ /login 200¡ storefront (Host default-store.edzeery.com) 200.

**ÈŞí ááÊÍŞŞ ÈÕÑíğÇ (375/768/1440):** landing (collapse¡ ÊÈÏíá billing¡ ŞÇÆãÉ ÇáãæÈÇíá ÇáãäÒáŞÉ¡ ÃíŞæäÇÊ¡ ÃáæÇä dark/light¡ ÓÈíäÑ ÇáÃÒÑÇÑ) + login/register/create-store (ÓÈíäÑ + ÃáæÇä) + storefront (ÎÇÑÌ äØÇŞ ÇáÊæÍíÏ Çááæäí - ÎáİíÇÊå ÇáÑãÇÏíÉ ãÚÑøİÉ æÑÓãíÉ).
**ÅÕáÇÍ áÇÍŞ (ÇÓÊßãÇá Phase 39):**
- ÇáßæäÓæá ÃÙåÑ ÃÎØÇÁ Alpine Úáì ÇááÇäÏíäÛ: edzLoader is not defined + overlay is not defined + label is not defined ÚäÏ Alpine.start().
- **ÇáÓÈÈ:** <x-edz.global-loader> (ãÓÊÏÚì İí landing-layout/guest/app) íÓÊÎÏã x-data="edzLoader()"¡ æÇáãßæøä ßÇä íõÓÌøá ÚÈÑ evento lpine:init İí ÍÒãÉ ãäİÕáÉ edz-loader.js — áÇÍŞğÇ ÈÚÏ ÊÔÛíá landing.js áÃáÈÇíä (ÓíÇŞ ÊÓÌíá ÇáÜ listener áÇ íáÍŞ ÈÈÏÁ Alpine). ŞÈá Phase 38 áã íßä ÃáÈÇíä íÚãá Úáì ÇááÇäÏíäÛ ÃÕáğÇ İÈŞíÊ ãÚØáÉğ ÈÕãÊ.
- **ÇáÍá:** landing.js ÊÓÊæÑÏ ÇáÂä ./components/edz-loader.js æÊÓÊÏÚí Alpine.data("edzLoader", edzLoader) ŞÈá Alpine.start() (ÊÃßíÏ ÈÇáÍÒãÉ ÇáãÈäíÉ: .data("edzLoader",...);Ce.start();). edz-loader.js íÈŞì ááÊÓÌíá İí ÇááæÍÉ/storefront (ÃáÈÇíä ãä Livewire) æááÜ CSS¡ æÇáÊÓÌíá ãßÑÑ idempotent.
- ÇáäÊíÌÉ: boot overlay ááÚÇáãíÉ íÊÍÑÑ ÇáÂä Úáì ÇááÇäÏíäÛ (BOOT_MIN_MS + fonts) æÇáÃÎØÇÁ ÊÎÊİíº ÇáŞíÇÓÇÊ: landing.js 85.2KB¡ æÇáİÍÕ ÇáãíÏÇäí /, /login, /register, storefront = 200.
**ãÑÇÌÚÉ äåÇÆíÉ (ÇÓÊßãÇá Phase 39):**
- ãÑÇÌÚÉ git diff áßá ÇáãáİÇÊ ÇáãÚÏáÉ: ÇáãÓÍ Çááæäí æadd icon æÇáÓÈíäÑ ÓáíãÉº áÇ ÊÛííÑÇÊ ÏáÇáíÉ ÎÇØÆÉ.
- ÊäÙíİ ßáÇÓÇÊ ãßÑÑÉ äÊÌÊ Úä ÇáãÓÍ (ÇÓÊÈÏÇá ÃÒæÇÌ ãÊŞÇØÚÉ Úáì ÃÓØÑ): hero (border-surface-border ×2)¡ footer (hover ×2)¡ user-dropdown (text-ink-muted ×2)¡ stores-metrics (bg ×2)¡ landing-layout body.
- ÊÕÍíÍ landing-layout body Åáì g-surface-bg text-ink (äãØ guest) ÈÏá g-surface-bg bg-surface ÇáãÒÏæÌ.
- ÇáÊÍŞŞ ÇáäåÇÆí: 
pm run build äÇÌÍ (landing.js 85.2KB¡ guest.js 1.1KB¡ native-button-loading 1.9KB¡ edz-loader 2.8KB)º İÍÕ ÇáÕİÍÇÊ: /, /login, /register, /forgot-password, /contact-us, storefront = 200 (ÇáãÓÇÑ ÇáÕÍíÍ ááÊæÇÕá /contact-us æáíÓ /contact).
**ÅÕáÇÍ RTL (ÇÓÊßãÇá Phase 39):** landing/sections/how-it-works.blade.php:58 — ÇáÎØæØ ÇáãÊŞØÚÉ Èíä ÇáãÑÇÍá ßÇäÊ left-[60%] w-[80%] (ÇÊÌÇå İíÒíÇÆí ËÇÈÊ): ÊÚãá İí LTR¡ áßä İí RTL ÚäÏ ßá ÎØæÉ ÊÔíÑ íãíäğÇ ÈíäãÇ ÇáÊÏİŞ ãä Çáíãíä ááíÓÇÑ (step01 áÇ íÚÑİ ÎØğÇ íãÊÏ ÎÇÑÌ ÇáÍÇæíÉ). ÇáÍá: start-[60%] (inset-inline-start) — İí LTR = left:60% (äİÓ ÇáÔßá ÊãÇãğÇ)¡ æİí RTL = 
ight:60% İíÔíÑ ÇáÎØ äÍæ ÇáÎØæÉ ÇáÊÇáíÉ íÓÇÑğÇ. ãÄßóøÏ İí CSS ÇáãÈäí: .start-\[60\%\]{inset-inline-start:60%}. ÇáİÍÕ: / æ/?lang=ar = 200.
**ÊßãáÉ ÅÕáÇÍÇÊ RTL (ÇÓÊßãÇá Phase 39):**
- how-it-works.blade.php:64: ÔÇÑÉ ÑŞã ÇáÎØæÉ -right-2 -> -end-2 (ÊäÚßÓ ááÒÇæíÉ ÇáãÚßæÓÉ İí RTL).
- hero.blade.php:116: ÔÇÑÉ Çáäãæ ÇáÚÇÆãÉ -left-4 -> -start-4 (ÊäÊŞá ááÒÇæíÉ ÇáãÚßæÓÉ İí RTL).
- İÍÕ ÔÇãá: áÇ space-x-* İí landing/auth/layouts (ÇáãÔÑæÚ íÓÊÎÏã gap-* ÇáãÊæÇİŞ ãÚ ÇáÇÊÌÇå)º guest.blade.php:36-37 İŞÇÚÇÊ ÎáİíÉ ãÊãÇËáÉ ÊõÊÑßº storefront:171 ml-3 İÇÕá İŞØ æíÈŞì ÎÇÑÌ ÇáäØÇŞ.
- ãÄßóøÏ İí CSS ÇáãÈäí: inset-inline-start:-1rem (ãä -start-4) æinset-inline-end:-.5rem (ãä -end-2). ÇáİÍÕ: LTR/RTL = 200.
**ÊÍÓíä ÇáÃÏÇÁ P1-P4 (Phase 40):**
- **P1** pp/Helpers/Language_Translation.php: ÅÖÇİÉ ctiveLanguages() ÈßÇÔ static áßá ØáÈº getLanguages/getLanguageCodes/getLanguageNames ÊÈäí ãäå. ÃËÈÊ ÇáÇÎÊÈÇÑ: 3 ÇÓÊÏÚÇÁÇÊ = ÇÓÊÚáÇã æÇÍÏ (ßÇä 3+)¡ æÇáÑÇÈÚ = ÕİÑ. íõáÛí ãÆÇÊ ÇÓÊÚáÇãÇÊ languages ãä View::composer('*').
- **P2** pp/Helpers/helpers.php: currentMembership() íŞÑÃ ÑÈØ pp('currentMembership') ãä EnsureStoreMembership ŞÈá Ãí ÇÓÊÚáÇã (ãØÇÈŞÉ store_id+user_id İŞØ). canStore() ÈßÇÔ áßá ØáÈ keyed ÈÜ user id + permission (ÊÍŞŞ super-admin ãÑÉ¡ membership/permissions ãÑÉ). pp/Models/Stores/Team/StoreMembership.php: permissionNames() memoized áßá instance (ÃËÈÊ ÇáÇÎÊÈÇÑ: ÏÚæÊÇä = ÇÓÊÚáÇã æÇÍÏ).
- **P3** pp/Domains/Order/Services/OrderDuplicateService.php::countsBySiblings: ÃõÚíÏÊ ßÇÓÊÚáÇãÇÊ ãõÌãÚÉ/ãÍÏæÏÉ (targets İŞØ + COUNT ãÌãÚ + overlap ÚÈÑ join bounded ÈÇáÚãáÇÁ) ÈÏá ÓÍÈ ßÇãá ÍãæáÉ 30 íæã + items ááÈíÆÉ. ãÎÑÌÇÊ ãØÇÈŞÉ ÊãÇãğÇ ááÎæÇÑÒãíÉ ÇáŞÏíãÉ (ÊÍŞŞ ÈãŞÇÑäÉ Úáì ÈíÇäÇÊ İÚáíÉ: 5/5 Õİæİ ãÊØÇÈŞÉ) ãÚ ÊÕÍíÍ ÇÓÊËäÇÁ ÇáĞÇÊ İŞØ (æáíÓ ßá ÇáÕİÍÉ).
- **P4** pp/Domains/Order/Services/OrderService.php::availableTransitions: ßÇÔ Static ááÈÍË Úä status ÇáİÑÚí áßá store+key (íÓŞØ ÇÓÊÚáÇãğÇ áßá Õİ). pp/Domains/Order/Services/OrderCompleteness.php::storeReadyForDispatch: ßÇÔ static áßá store id (íÓŞØ ÇÓÊÚáÇãí exists áßá Õİ confirmed/preparing). ÕİÍÉ ÇáØáÈÇÊ: getCurrentMembership() æisibleTo(currentMembership()) ÈÏá ÇáÇÓÊÚáÇã ÇáãÊßÑÑ.
- ÇáÊÍŞŞ: php -l Óáíã áßá ÇáãáİÇÊ (7/7)¡ tinker ÃËÈÊ ÊŞáíá ÇáÇÓÊÚáÇãÇÊ¡ iew:clear äÙíİ¡ / æ/login = 200 æorders/tracking = 302 ? login (ÇáãÊæŞÚ Ïæä ÌáÓÉ). áÇ ÊÛííÑÇÊ JS/CSS ? áÇ build.
**ÊÍÓíä ÇáÃÏÇÁ P5-P8 (ãæÇÕáÉ Phase 40) — íÓÊåÏİ ËŞá ÊäŞá wire:navigate:**
- **P5** 
esources/views/livewire/merchant/tracking/index.blade.php: İáÊÑ ÇáãäÊÌÇÊ İí mount ãŞÕæÑ Úáì ÂÎÑ 180 íæã + ÍÏ 2000 (ÈÏá İÍÕ ßÇãá ÇáÊÇÑíÎ ÚÈÑ join order_items)¡ æäÇŞáæä Úáì äÇİĞÉ ÓäÉ. pp/Livewire/Concerns/TrackingColumnConcern.php::getMembership() íÓÊÎÏã currentMembership() (ÇáÑÈØ ãä middleware) ÈÏá ÇÓÊÚáÇã membership İí ßá baseTrackingQuery (ßÇä ~6-10 ÇÓÊÚáÇãÇÊ/ÕİÍÉ).
- **P6** pp/Livewire/Concerns/TrackingGridConcern.php::loadTrackingStats: ÚÏÇÏÇÊ ÇáÕİÍÉ (active/delivered_today/returned_today) + ÚÏøÇÏ "ÇÚÊãÇÏ áÏì ÇáäÇŞá" İí **ÇÓÊÚáÇã SUM æÇÍÏ** ÈÏá 4 İÍæÕ EXISTS ãäİÕáÉ (ãÚ ÇáÍİÇÙ Úáì ÏáÇáÉ latestOfMany ÚÈÑ subquery created_at=MAX). ÍÕíáÉ ÇáÓÇÆŞíä ãä **ÊÌãíÚ æÇÍÏ** (count+sum+distinct ? groupBy) ÈÏá 4 ÇÓÊÚáÇãÇÊ. DeliveryRiderService::listForStore ÈßÇÔ áßá ØáÈ (ßÇä íõÓÊÏÚì 2-3 ãÑÇÊ ãÚ withCount). ÅÌãÇáí İÍæÕ ÕİÍÉ ÇáÊÊÈÚ ãä ~12 Åáì ~6.
- **P7** pp/Livewire/Concerns/DistributionQueueConcern.php: Õİæİ ÇáØÇÈæÑ ãÍÏæÏÉ ÈÜ 500 ãÚ ÚÏøÇÏÇÊ COUNT ÏŞíŞÉ ãäİÕáÉ. 
esources/views/livewire/merchant/returns/index.blade.php: ÊÈæíÈÇÊ ÇáãÑÊÌÚÇÊ ÊõÑÔóøÍ İí SQL (3 ÚÏøÇÏÇÊ ÓÑíÚÉ + ÓØÑ ÇáÕİÍÉ ÇáäÔØÉ İŞØ) ãÚ ÊŞÓíã ÕİÍÇÊ 25/ÕİÍÉ ÈÏá ÊÍãíá ßá ÊÇÑíÎ ÇáãÑÊÌÚÇÊ.
- **P8** pp/Domains/Analytics/Services/StoreDashboardAnalyticsService.php: ÎÑíØÉ status_id?key ÊõÍãóøá ãÑÉ/ØáÈ (ßÇä ~5 ÇÓÊÚáÇãÇÊ statuses áßá ÑÓã ÏÇÔÈæÑÏ).
- **P3 ÅÕáÇÍ ÇäÍÏÇÑ** pp/Domains/Order/Services/OrderDuplicateService.php: ÃõÖíİ whereNull(o.deleted_at) áÇÓÊÚáÇã ÊÏÇÎá ÇáãäÊÌÇÊ (Query Builder íÌÊÇÒ SoftDeletes — ÃÙåÑå OrderDuplicateBadgeTest::soft-deleted siblings excluded).
- ÇáÊÍŞŞ: php -l äÙíİ áßá ÇáãáİÇÊº view:cache íÌãøÚ ßá ÇáŞæÇáÈº ÇÎÊÈÇÑ ãíÒÉ ãÄŞÊ (Êã ÍĞİå) ÑİÚÊ 4 ÕİÍÇÊ ßÈíÑÉ ÈÜ 200º ÇáãÌãæÚÇÊ ÇáŞÇÆãÉ ÎÖÑÇÁ: Tracking + Search + BulkValidate + Duplicates (15) + Queue + Returns + OrdersPageQueryCount (ÈáÇ N+1).

**ÇáÊÍÏíÏ ÚÈÑ POST İŞØ — ÍĞİ account/stores (Phase 41):**
1. **ÍĞİ ÕİÍÉ `/merchant/account/stores`:** route `stores` ãä `routes/merchant.php:35`¡ ÇáİæáÊíä `resources/views/livewire/merchant/stores/index.blade.php`¡ æ`GetStoreCardsAction.php` + `StoreCardData.php` (ßÇäÇ ÍÕÑííä ááÕİÍÉ)¡ æßæÏ ãíÊ: `ChooseStoreController::index` + `resources/views/auth/choose-store.blade.php`.
2. **ÅÚÇÏÉ ÊæÌíå ÑæÇÈØ ÇáŞÇÆãÉ ÇáÎãÓÉ Åáì `merchant.choose-store`:** topbar:39 (store-link)¡ store-sidebar:101 (store-switch)¡ merchant-sidebar:34 (my_stores + routeIs)¡ account-sidebar:33 (+ routeIs)¡ user-dropdown:6 — ßáåÇ ÊÕíÑ ÕİÍÉ ŞÇÆãÉ ÇáãÊÇÌÑ ÇáæÍíÏÉ.
3. **ÇáÊÍÏíÏ POST İŞØ İí choose-store:** ÒÑ «İÊÍ» ÕÇÑ `<form method="POST" action="{{ route('merchant.choose-store.select', $store['slug']) }}" class="contents">` + `@csrf` (äãØ ÕİÍÉ account/stores ÇáãÍĞæİÉ äİÓåÇ ? `ChooseStoreController::select` ÈÍÑÇÓÇÊ ÇáãÌÇáíä) æÍĞİ Closure `selectStore` + ÇÓÊíÑÇÏ `StoreContext`.
4. **ÅÛáÇŞ ÈÇßÏæÑ ÇáÑÇÈØ:** `StoreResolver::resolve` — İÍÕ `X-Store-Id`/`?store_id` ãŞÕæÑ Úáì `/api/*` İŞØ (ÚŞÏ API ÚÈÑ `EnsureStoreContext` íÈŞì ÓáíãğÇ)º áÇ íãßä áÃí GET/ÑÇÈØ İí áæÍÉ ÇáÊÇÌÑ ÊÍÏíÏ ÇáãÊÌÑ.
5. **ÇáÇÎÊÈÇÑ:** `BillingVisibilityScopingTest` §4 ? ? `/merchant/account/stores` = 404 ? GET `/merchant/choose-store` íäÌÍ æáÇ íÚíøä `current_store_id` ? POST `merchant.choose-store.select` íÚíøäåÇ (ãÚ `_token` = csrf_token) æíÚíÏ ÊæÌíå ÇáÏÇÔÈæÑÏ.
6. **ÇáÔåÇÏÉ:** `php -l` äÙíİ (routes/merchant.php¡ StoreResolver¡ ChooseStoreController¡ ÇáÇÎÊÈÇÑ)º `view:clear` + `route:clear` äÇÌÍÇäº `route:list` íÄßÏ ÇÎÊİÇÁ `account.stores` æÈŞÇÁ `merchant.choose-store` GET + `merchant.choose-store.select` POSTº greps ÕİÑíÉ áÜ `account.stores` (İí ÇáŞæÇáÈ) æ`merchant.stores.index|GetStoreCardsAction|StoreCardData|auth.choose-store`º `BillingVisibilityScopingTest` **6/6 (27 ÊÃßíÏ)** + `EmailVerificationTest` äÇÌÍ. ÇáãÓÍ ÇáßÇãá (Auth+Account+Merchant) = **641 äÇÌÍ + 38 İÔá ÓÇÈŞÉ ÇáæÌæÏ** (ÇäÍÑÇİ ãåÇÌÑÉ/DB ááÈíÆÉ + spatie cache) ãÄßóøÏÉ Úáì baseline äÙíİ ÚÈÑ `git stash` (ÚíäÇÊ: SplitLogin merchant login¡ OrderTracking QueryException¡ RoleScopingTest:79) — **ÕİÑ ÇäÍÏÇÑÇÊ ãä åĞÇ ÇáÊÛííÑ**.
7. **ÇáãÑæäÉ 375/768/1440:** ÇáÊÛííÑ ÇáÈÕÑí ÇáæÍíÏ ÛáÇİ `class="contents"` (äİÓ äãØ ÇáÕİÍÉ ÇáãÍĞæİÉ) ÏÇÎá `flex` ÃæÇãÑ ÇáÈØÇŞÉ — ÈáÇ ÊÛííÑ İí ÇáãæÇÕİÇÊº ÔÈßÉ ÇáÈØÇŞÇÊ `grid-cols-1 md:grid-cols-2` (1 ÚãæÏ ãæÈÇíá / 2 ÚäÏ ?768) ßãÇ åíº ÑæÇÈØ topbar/sidebar/dropdown ÛíøÑäÇ ÇáÜ URL İŞØ Ïæä markup ? áÇ ÃËÑ ÚäÏ ÇáäŞÇØ ÇáËáÇË.
---

## Phase 34 - ÌæáÉ ÇáÊäÙíİ ÇáÎÊÇãíÉ áÊæÒíÚ ÇáØáÈíÇÊ (2026-09-27)

**ÇáåÏİ:** ËáÇË ãåÇã ÎÊÇãíÉ áãíÒÉ ÊæÒíÚ ÇáØáÈíÇÊ (ŞÇÆãÉ ÇáÊæÒíÚ): (1) ÊÕÛíÑ `OrderAssignmentService.php` ÊÍÊ 250 ÓØÑğÇ ÈÇÓÊÎÑÇÌ ÇáãÓÇÚÏÇÊ ÇáãßÑÑÉ Åáì trait ãÔÊÑß¡ (2) ÅÕáÇÍ ÔÇÑÉ ÇáÊÈæíÈ ÇáäÔØ İí ÇáÏÇÑß ãæÏ¡ (3) pagination ãÓÊŞáÉ áßá ÊÈæíÈ ãÚ ÈŞÇÁ ÔÇÑÇÊ ÇáÚÏ ÇáÅÌãÇáíÉ ÏŞíŞÉ.

**Êã ÊäİíĞå:**
1. **ÇáãÓÇÚÏÇÊ ÇáãÔÊÑßÉ:** `ResolvesCapacityBalancedCandidates.php` (246 ÓØÑğÇ) ÇÓÊŞÈáÊ 4 ãÓÇÚÏÇÊ ÈÃÓáæÈ ãÍÇíÏ ááÌÏæá: `terminalStatusKeys()`¡ `openAssignmentCounts(string $table, string $storeId, ?Closure $statusScope)`¡ `lastAssignedAt(string $table, string $storeId)`¡ `unassignedAssignmentCount(string $model, string $storeId, Closure $statusScope)` (Eloquent áÊİÚíá soft-delete scopesº æãÚåÇ `when($table === 'orders')` áÅÖÇİÉ `whereNull deleted_at` ááÇÓÊÚáÇãÇÊ ÇáÎÇã). `OrderAssignmentService.php` (215) æ`OrderTrackingAssignmentService.php` (134) ÇÓÊÎÏãÊ ÇáãÓÇÚÏÇÊ ãÚ closure join áÌÏæá statuses (`whereNotIn('statuses.key', $this->terminalStatusKeys())`) æ`openTrackingStatusValues()` ÇáÎÇÕ ÈÇáÊÊÈÚ (ãÔÊŞÉ ãä `OrderTrackingStatus::open()`) — ÍõĞİ ÇÓÊíÑÇÏ DB ãä ÇáÎÏãÊíä æŞõáÕÊ ÇáËäÇÆíÇÊ ÇáãØÇÈŞÉ ÈíäåãÇ.
2. **ÇáÔÇÑÉ:** `partials/tabs.blade.php` (ÓØÑÇ 7 æ16) — ÇáÊÈæíÈ ÇáäÔØ `bg-brand-surface text-brand-fg-strong` ÈÏá ÇáÃáæÇä ÇáİíÒíÇÆíÉ (ÇáÎÇİÊÉ İí ÇáÏÇÑß)º ÇáÊæßäÇÊ ãÚÑøİÉ İí `tailwind.config.js:79-83` æãÓÊÚãáÉ ÈäãØ badge ããÇËá İí `create-store.blade.php:650`.
3. **Pagination:** `DistributionQueueConcern.php` (177) — `QUEUE_PAGE_SIZE = 50`¡ `loadQueue()` íÖÈØ count + `clampPage` (ÓŞİ = `ceil(total/50)`) Ëã `paginate(...)` + `pageMeta()`¡ æ`goToTabPage(string $tab, int $page)`º ÇáÕİæİ ÚÈÑ `paginate(50, ['*'], 'page', $this->confirmationPage|trackingPage)` + `through` + `toArray()`. `order-distribution-queue.blade.php` (170) ÃÖÇİÊ ÍÇáÉ `*Pagination` + `$set*Page` closuresº `queue-table.blade.php` (85) ÃÖÇİÊ `$queuePagination`/`$queuePaginationMethod` + `<x-edz.pagination size="sm" wire:key="queue-pagination-{{ $kind }}" />`.
4. **ÇáÇÎÊÈÇÑÇÊ:** ÇÎÊÈÇÑ pagination ÌÏíÏ (60 pending + 60 tracking Úáì ÃæÇãÑ 'delivered' ÇáØÑİíÉ ÍÊì áÇ ÊáæøË ÚÏøÇÏ ÇáÊÈæíÈ¡ ÊÃßíÏ `last_page=2`/`per_page=50`/`total=60`¡ ÕİÍÉ 2 = `slice(50)` ãäİÕáÉ¡ ÍİÙ ÕİÍÉ ßá ÊÈæíÈ ÚäÏ ÇáÊÈÏíá¡ ÇÊÍÇÏ ÕİÍÊí ÇáÊÊÈÚ = ßá 60 id) + ÇÎÊÈÇÑ N+1 — ßáåÇ ÎÖÑÇÁ¡ ãÚ ÈŞÇÁ ÇáØáÈ ãÚ ÇáÕİæİ ßãÇ åæ ÈáÇ ÊÛííÑ ãäØŞí.

**ÇáÅÕáÇÍÇä ÇáÃÏäì ÇáãæËŞÇä (ÇÎÊÈÇÑÇÊ ãÚØøáÉ ãÓÈŞğÇ İí åĞå ÇáÈíÆÉ¡ ÃõËÈÊÇ ÚÈÑ `git stash` Úáì ÇáßæÏ ÇáÃÕáí Ëã ÃõÕáÍÇ ÈãæÇİŞÉ ÇáãÓÊÎÏã):**
- `tests/Feature/Order/OrderAssignmentServiceTest.php`: ÚãæÏ `state_code` åæ `char(2)` (migration `0001_01_01_000001` ÇáÓØÑ 45) ÈíäãÇ ÇáÜ fixture ßÇä íÏÑÌ `'ASG-01'` ? `'AS'` (QueryException ÇáãÏãÑ ÊÎÊİí — 7/7 ÃÎÖÑ).
- ÇÎÊÈÇÑ "flat no N+1": ÇáÜ regex ßÔİ `from|into|update|delete "?orders...` áßä MySQL İí åĞå ÇáÈíÆÉ íŞÊÈÓ ÇáãÚÑİÇÊ ÈÜ backtick ? áã íõáÊŞØ Ãí ÇÓÊÚáÇã ÃÕáÇğ (`$dataBase = 0`) ÍÊì Úáì ÇáßæÏ ÇáÃÕáí. ÇáÅÕáÇÍ: `["`]?` (accept backtick Ãæ ÇŞÊÈÇÓ ãÒÏæÌ) ? `$dataBase ? 11` æÇáÇÓÊÚáÇãÇÊ ËÇÈÊÉ Èíä ÇáÈíÇäÇÊ (4?8 Õİæİ) ÈáÇ äãæ áßá Õİ.

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** `php -l` äÙíİ (concern + trait + ÇáÎÏãÊÇä)º ÃÓØÑ ÇáãáİÇÊ ÇáäåÇÆíÉ: trait 246 (< 250)¡ OrderAssignmentService 215 (< 250)¡ OrderTrackingAssignmentService 134 (< 250)¡ concern 177 (< 250)¡ Volt 170 (< 400)¡ queue-table 85 ætabs 20 (< 300). ãÌãæÚÉ ÇáãÑÍáÉ **39 äÇÌÍğÇ / 127 ÊÃßíÏğÇ** (DistributionQueue 9 + OrderAssignmentService 7 + OrderTrackingService 9 + Resolver 7 + ConfirmationShift 6) — ÈáÇ ÇäÍÏÇÑÇÊ¡ æáã ÊõáãóÓ ãáİÇÊ merchant ÇáÛÑíÈÉ ááÔÌÑÉ (`StoreSubscriptionExpiryGatingTest` æ`StoreSubscriptionGatingTest` ÇáãÍĞæİÇä).

---

## Phase 42 - ÅÕáÇÍ ÌĞÑí: İÕá äØÇŞ ÇáãäÊÌ Úä ÊÎÕíÕ ÇáãõÎÕöøÕ + ÍÇÑÓ ÇáÑÄíÉ (assign-then-invisible)

**ÇáÓÈÈ ÇáÌĞÑí:** `StoreProductScopeService` ßÇä íßÊÈ İí `confirmation_product_assignments` — äİÓ ÌÏæá `OrderAssignmentService::specialistMembershipIds()` ÇáĞí íŞÑÃå áÊÑÊíÈ ÃæáæíÉ ÇáÅÓäÇÏ ÇáÊáŞÇÆí¡ æåæ ãÇ íÚÊÑİ Èå docblock ÇáÎÏãÉ äİÓåÇ. æÇÌåÊÇä ãÓÊŞáÊÇä ÊßÊÈÇä İí ÌÏæá æÇÍÏ ÈäíøÉ ãÊÚÇÑÖÉ (æÇÌåÉ «ãõÎÕöøÕ ÇáÊÃßíÏ» Phase 34.1 ÈáÇ ŞíÏ ÏæÑ¡ ææÇÌåÉ «äØÇŞ ÇáÑÄíÉ» Phase 36.3 ÇáãŞÕæÑÉ Úáì `isManager()`)¡ æáÇ ÚãæÏ íãíøÒ ÃíøåãÇ ÃäÔÃ ÇáÕİ. äÊíÌÉ Ğáß Ãä `HasVisibilityScope::scopeVisibleTo()` íÎäŞ ØáÈğÇ ãõÓäóÏğÇ Åáì ãÏíÑ ÅĞÇ ÎÑÌÊ ãäÊÌÇÊ ØáÈå Úä äØÇŞå — ËÛÑÉ «ÃÓäÏÊõå Ëã ÕÇÑ ÛíÑ ãÑÆí» — İÖáğÇ Úä Ãä ãÑÔÍí ÇáÅÓäÇÏ (ÊáŞÇÆíğÇ æíÏæíğÇ) íõÎÊÇÑæä ÈÇáÕáÇÍíÉ İŞØ ÈáÇ Ãí æÚí ÈäØÇŞ ÑÄíÉ ÇáãÑÔÍ.

**ÇáŞÑÇÑ ÇáãÚÊãÏ (ÛíÑ åÏøÇã):** áÇ ÊÎãíä Úáì ÇáäíÉ İí ÇáÕİæİ ÇáÛÇãÖÉ. äÓÎ ÇáÕİæİ (áÇ äŞáåÇ) Åáì ÌÏæá ãÎÕÕ ÌÏíÏ¡ ÊÑß ÇáÃÕá ßãÇ åæ¡ æÅÙåÇÑ æÇÌåÉ ãÑÇÌÚÉ áíŞÑÑ ÕÇÍÈ ÇáãÊÌÑ áßá Õİ: ÅÈŞÇÁ/İÕá/ÅÒÇáÉ ÇáÊßÑÇÑ. Õİæİ ÇáØÇŞã ÛíÑ ÛÇãÖÉ (ÃõÎÕöøÕæä) æáÇ ÊõãÓø ÅØáÇŞğÇ.

**Êã ÊäİíĞå:**
1. **ÇáÌÏæá ÇáÌÏíÏ `membership_product_scopes`:** `database/migrations/2026_09_21_000001_create_membership_product_scopes_table.php` (47) — `ulid` primary + `store_id`/`membership_id`/`product_id` ÈÜ `foreignUlid()->constrained()->cascadeOnDelete()` ßãÇ İí `confirmation_product_assignments` ÍÑİíğÇ¡ `created_by_membership_id` nullable ÈÜ `nullOnDelete()` (ÇáŞíÏ íÈŞì ÍíğÇ ÈÚÏ ãÛÇÏÑÉ ãä ÃäÔÃå¡ ÈíäãÇ ÇáÜ cascade ßÇä ÓíõÓŞØ ŞíÏ ÑÄíÉ ÓÇÑíğÇ)¡ `timestamps()`¡ æİåÑÓ İÑíÏ `mps_store_member_prod` Úáì `(store_id, membership_id, product_id)`.
2. **ÇáãæÏíá `App\Models\Stores\Team\MembershipProductScope`** (44) — `HasUlids` + ÚáÇŞÇÊ `store()`/`membership()`/`product()` ÈäİÓ Ôßá `ConfirmationProductAssignment`¡ æÚáÇŞÉ `StoreMembership::productScopes()` (hasMany) ÈÌÇäÈ `productAssignments()` ÇáŞÇÆãÉ ÈáÇ ÊÛííÑ.
3. **ÇáÊÑÍíá ÇáÎáİí (migration ãÓÊŞáÉ áãÑÉ æÇÍÏÉ):** `2026_09_21_000002_backfill_manager_product_scopes.php` (108) — `join` Úáì `store_memberships` ãÚ `where('role','manager')` æ`whereColumn` áÊØÇÈŞ ÇáãÊÌÑ (ÊãäÚ Ãí äØÇŞ ÚÇÈÑ ááãÊÇÌÑ ãä Õİ ŞÏíã)¡ `chunk(500)` + `insertOrIgnore` Úáì ÇáİåÑÓ ÇáİÑíÏ (ÅÚÇÏÉ ÇáÊÔÛíá no-op)¡ `created_by_membership_id = null`¡ `Log::info` + ÑÓÇáÉ CLI ÈÚÏÏ ÇáÕİæİ ÇáãäÓæÎÉ æÇáãÊÇÌÑ ÇáãÊÃËÑÉ. `down()` İÇÑÛ ÚãÏğÇ: ÇáäÓÎ ÇáÂä ŞíæÏ ÑÄíÉ ÍíøÉ. **áã íõÍĞİ Ãæ íõÚÏóøá Ãí Õİ ÃÕáí¡ æÕİæİ ÇáØÇŞã/ÇáÃÏãä áÇ ÊõäÓÎ ÅØáÇŞğÇ.**
4. **ÅÚÇÏÉ ÊæÌíå ÇáÎÏãÉ:** `StoreProductScopeService` (182) ÕÇÑ `assign()`/`revoke()`/`assignedProductIds()` ÊŞÑÃ/ÊßÊÈ `membership_product_scopes` ÍÕÑíğÇ — **ÊæŞíÚÇÊ ÇáÏæÇá ÇáÚÇãÉ æ`assertScopeable()` Ïæä ÊÛííÑ**¡ İßá ÇáãÓÊÏÚíä (ãæÏÇá ÇáäØÇŞ + `HasVisibilityScope`) ÊÚãá ÈáÇ ÊÚÏíá.
5. **ÍÇÑÓ ÇáÑÄíÉ:** `canSeeAnyProduct(StoreMembership, array $productIds): bool` íÚßÓ ãäØŞ `scopeVisibleTo()` ÍÑİíğÇ (ãØÇÈŞÉ **Ãí-ãä** áÇ **ßá-ãä**): `true` Åä áã íÍãá `TEAM_VIEW_OWN`¡ Ãæ `productIds` İÇÑÛÉ¡ Ãæ áÇ Õİæİ äØÇŞ (ÛíÑ ãŞíøÏ)¡ Ãæ ÊŞÇØÚ `assignedProductIds()`. æãÚå `filterByVisibility(Collection, array)` ááŞÑÇÁÉ ÇáÏİÚíÉ: **ÇÓÊÚáÇã äØÇŞ æÇÍÏ** áßá ãÌãæÚÉ ÇáãÑÔÍíä ãåãÇ ßËÑ ÚÏÏåã (`isUnscoped()` ÊÓÊåáß ÚáÇŞÉ `permissions` ÇáãÍãøáÉ ãÓÈŞğÇ İí ÇáÜ resolver).
6. **ÇáÊÑßíÈ İí Íá ÇáãÑÔÍíä (ØÈŞÉ ÇÓÊÈÚÇÏ ÅÖÇİíÉ — áÇ ÈÏíá Úä ÇáÜ tiering):**
   - `OrderAssignmentService::resolveCandidatePool()` — ÈÚÏ İáÊÑ ÇáÕáÇÍíÉ¡ `filterByVisibility($candidates, $productIds)` ÈãÚÑøİÇÊ ãäÊÌÇÊ ÇáØáÈ ÇáãÍÓæÈÉ ÃÕáğÇ İí `assign()`.
   - `OrderTrackingAssignmentService` — `resolveCandidatePool(string, array)` + `orderProductIds(OrderTracking)` (ãä `order_items` ááØáÈ ÇáãÑÊÈØº ÇáÊÊÈÚ äİÓå ÈáÇ ÈõÚÏ ãäÊÌÇÊ).
   - `AssignmentCandidateResolver::resolve(..., array $productIds = [])` — äİÓ ÇáÇÓÊÈÚÇÏ + `primePermissionsRelation()` injecting ÏİÚÇÊ ÇáÕáÇÍíÇÊ ßã ÚáÇŞÉ ãÍãøáÉ ßí áÇ íÕÏÑ `TEAM_VIEW_OWN` ÇÓÊÚáÇã pivot áßá ÚÖæ. äÏÇÁÇ ÇáÇÓÊÏÚÇÁ: `orders/index.blade.php` (ÊÏİøŞ ÅÚÇÏÉ ÇáÅÓäÇÏ) æ`TrackingReassignConcern` (ãİÑÏ + ÌãÇÚí¡ ÇÊÍÇÏ ãäÊÌÇÊ ÇáÊÍÏíÏ).
   - `specialistMembershipIds()` æãäØŞ ÃæáæíÉ ÇáÜ tiers **áã íõãóÓø**¡ æ`reassign()` ÈŞí ÊÌÇæÒğÇ íÏæíğÇ ãÊÚãøÏğÇ ÈÚÏ ÇÎÊíÇÑ ÕÑíÍ — ÇáÇÓÊÈÚÇÏ íÖíøŞ **ÇáŞÇÆãÉ** ÇáãÚÑæÖÉ İŞØ.
7. **ÓØÍ ÇáãÑÇÌÚÉ ááãÇáß (ÈáÇ ÕİÍÉ ÌÏíÏÉ):** ÏÇÎá ãæÏÇá ÇáäØÇŞ äİÓå (ÇáãİÊæÍ ãä ÔÇÔÉ ÇáİÑíŞ ÚÈÑ `product-scope-mount.blade.php`) — computed `$duplicatedSpecialist` (ÊŞÇØÚ ÇáäØÇŞ ãÚ Õİæİ `ConfirmationProductAssignment` áäİÓ ÇáãÏíÑ) + partial ãÓÊŞá `product-scope-duplicates.blade.php` (34) ÈÈäÑ ÊÍĞíÑí æÓØÑ áßá ãäÊÌ ãßÑøÑ ãÚ ÒÑ «ÅÒÇáÉ Õİ ÇáãõÎÕöøÕ» ãÄßóøÏ ÈÜ Swal¡ æ`$removeSpecialistDuplicate` ãÍÕóøä ÈÜ `isManager()` + ãØÇÈŞÉ ÇáãÊÌÑ (İáÇ æÕæá áÕİæİ ÇáØÇŞã). 5 ãİÇÊíÍ ÊÑÌãÉ × ar/en/es/fr.

**ÇáÇÎÊÈÇÑÇÊ (ÌÏíÏÉ):** `AssignmentVisibilityGuardTest` (6) — áÇ ÅÓäÇÏ ÊáŞÇÆí áãÏíÑ äØÇŞå áÇ íÛØí ãäÊÌÇÊ ÇáØáÈ¡ ÇáØáÈ íÈŞì ÈáÇ ÅÓäÇÏ Ííä íßæä ÇáãÑÔÍ ÇáæÍíÏ ÎÇÑÌ ÇáäØÇŞ¡ ÇáãÏíÑ íÈŞì ãÄåøáğÇ ãÊì ÇÍÊæì ÇáØáÈ ãäÊÌğÇ æÇÍÏğÇ ãä äØÇŞå (any-match)¡ ÇÎÊİÇÄå ãä ŞÇÆãÉ ÅÚÇÏÉ ÇáÅÓäÇÏ İí ÇáÍÇáÉ ÇáãÚÇßÓÉ¡ äİÓ ÇáÍÇÑÓ Úáì ÇáÊÊÈÚ¡ æ**ÇÎÊÈÇÑ N+1** íËÈÊ ÚÏøÉ ÇÓÊÚáÇãÇÊ ÇáäØÇŞ ËÇÈÊÉ (1) ÚäÏ ãÑÔøÍ æÇÍÏ æÚäÏ ÃÑÈÚÉ. `MembershipProductScopeBackfillTest` (4) — äÓÎ Õİæİ ÇáãÏíÑ ãÚ ÈŞÇÁ ÇáÃÕá æ`created_by` null¡ ÚÏã äÓÎ Õİæİ ÇáØÇŞã/ÇáÃÏãä¡ **idempotence** (3 ÊÔÛíáÇÊ ? Õİ æÇÍÏ)¡ æÇáÊŞÇØ ÇáÕİæİ ÇáãÖÇİÉ ÈÚÏ Ãæá ÊÔÛíá. `ProductScopeDuplicateReviewTest` (2) — ÙåæÑ ÇáÈäÑ ááÅÏÇÑÉ æÍĞİ ÌåÉ ÇáãõÎÕöøÕ İŞØ (íÈŞì ÇáäØÇŞ)¡ ÇÎÊİÇÄå áãÏíÑ äØÇŞå İŞØ ãÚ ÈŞÇÁ Õİ ÇáØÇŞã ÓáíãğÇ ÍÊì ÚäÏ ÇáÇÓÊÏÚÇÁ ÈÇÓã Õİøå. `StoreProductScopeServiceTest` (9) ãÑø Ïæä ÊÚÏíá ãäØŞí — ÇáÊÈÏíá ÇáæÍíÏ `ConfirmationProductAssignment::query()` ? `MembershipProductScope::query()` İí ÊÃßíÏÇÊ ÇáÌÏæá áÃä ÇáÎÏãÉ ÕÇÑÊ ÊßÊÈ ÇáÌÏíÏ.

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** `view:cache` äÇÌÍº pint äÙíİ Úáì ÇáãáİÇÊ ÇáÌÏíÏÉ ÚÏÇ ÃÓáæÈ `return new class extends Migration\n{`ÇáãÔÊÑß ãÚ 47 ãåÇÌÑÉ ŞÇÆãÉ (ÇÕØáÇÍ ÇáãÓÊæÏÚ)º ÇáÃÓØÑ ÇáäåÇÆíÉ: OrderAssignmentService 222¡ OrderTrackingAssignmentService 159¡ AssignmentCandidateResolver 233¡ TrackingReassignConcern 168¡ StoreMembership 206¡ MembershipProductScope 44¡ StoreProductScopeService 182¡ migration ÇáÌÏæá 47¡ migration ÇáÊÑÍíá 108¡ product-scope-modal 254¡ product-scope-duplicates 34 (ßáåÇ ÊÍÊ ÇáÃÓŞİ: 250 PHP / 300 Blade / 400 Volt)º `orders/index.blade.php` +13 ÓØÑğÇ İŞØ (5704 — ÇäÍÑÇİ ÓÇÈŞ ÇáæÌæÏ Úä ÓŞİ Volt ÛíÑ ÇáãÚÇáóÌ İí åĞå ÇáÌæáÉ). ÇáÇÎÊÈÇÑÇÊ: **21 äÇÌÍğÇ / 72 ÊÃßíÏğÇ** İí ÇáãáİÇÊ ÇáÃÑÈÚÉ ÇáÌÏíÏÉ+ÇáãÚÏøáÉ¡ **36 äÇÌÍğÇ** İí `tests/Feature/Order` ßÇãáğÇ (Phase 34.1–34.5 ÓáíãÉ)¡ æ`tests/Feature/Shipping` + `Storefront` + `Unit` + ÇáÊÊÈÚ = **366 äÇÌÍğÇ**. İÔáÇä İŞØ İí ÇáãÓÍ ÇáßÇãá¡ **ãõËÈóÊÇä Úáì baseline äÙíİ ÚÈÑ `git stash`**: `RoleScopingTest:79` æ`CarrierSyncObservabilityTest` (ÊŞÑíÑ `carrier-sync:report`) — **ÕİÑ ÇäÍÏÇÑÇÊ**. (ãáÇÍÙÉ ÈíÆíÉ: `php artisan test` ßÇãáğÇ íäİÏ ĞÇßÑÊå ÚäÏ ~500MB ÈÚÏProductIndexExportImportTest — ÛíÑ ãÊÚáŞ ÈåĞÇ ÇáÊÛííÑº ÚõãáÊ ÇáÏİÚÇÊ ÈÜ `-d memory_limit=2G vendor/bin/pest`.)

**ÇáãÊÈŞí ááãÇáß:** Õİæİ ÇáãÏíÑ ÇáãÒÏæÌÉ (äØÇŞ + ãõÎÕöøÕ áäİÓ ÇáãäÊÌ) **áã ÊõÍĞİ æáÇ ÊõÎãóøä** — ÊÈŞì ãÚáøŞÉ ÍÊì íŞÑÑåÇ ÕÇÍÈ ÇáãÊÌÑ ãä ÈäÑ ÇáãÑÇÌÚÉ İí ÔÇÔÉ ÇáİÑíŞº ÚäÏåÇ íÒíá ÒÑ «ÅÒÇáÉ Õİ ÇáãõÎÕöøÕ» ÌåÉ ÇáÊÎÕÕ æíÈŞì ŞíÏ ÇáÑÄíÉ. Õİæİ ÇáØÇŞã ÛíÑ ŞÇÈáÉ ááæÕæá ãä Êáß ÇáæÇÌåÉ ÃÕáğÇ.

---

## Phase 35.1 (ÇáÌÒÁ Ã) — ÊÍÕíä ÊæÒíÚ ÇáØáÈíÇÊ: ãäÇæÈÇÊ ÈÍÓÈ ÇáÏæÑ + snapshot ÓÚÉ áÍÙí ? (2026-10-08)

**ÇáåÏİ:** Ãæá ËáÇËÉ ÃÌÒÇÁ áÊÍæíá ÊæÒíÚ ÇáØáÈíÇÊ Åáì äÙÇã ŞæÇÚÏ ÕÇÑã: (Ã) ãäÇæÈÇÊ æÇÚíÉ ÈÇáÏæÑ ãÚ ÓÚÉ æÍÇáÉ on-shift áÍÙíÉ æÇÍÏÉ íÔÊÑß İíåÇ ÇáãÍÑß ææÇÌåÉ ÇáÅÓäÇÏ ÇáíÏæí¡ (È) ÊæÌíå ÍÓÈ ãáßíÉ ÇáãäÊÌÇÊ¡ (Ì) ÊÍÕíä ÇáãÍÑß (Şİá + handover + `stranded_at` + ÊæÒíÚ ÚÔæÇÆí ãæÍøÏ). **åĞå ÇáÑÍáÉ ÊäİíĞ ÇáÌÒÁ (Ã) İŞØ** — Ïæä ÊÛííÑ `membership_product_scopes`¡ Ãæ `confirmation_shifts` ÈäíæíğÇ (ÅÖÇİÉ `role_scope` æŞÑíåÇ ÓÈŞÊ åĞå ÇáÌæáÉ)¡ Ãæ ÍÏæÏ `statuses.stage`¡ Ãæ ãäØŞ áíáÉ ÇáÛÑæÈ.

**Êã ÊäİíĞå:**
1. **`app/Domains/Order/Support/ShiftAvailabilityResolver.php` (88):** ÇÓÊÚáÇã æÇÍÏ áßá snapshot íÌíÈ áßá ãÑÔøÍ: `on_shift` (åá ÊÛØí ãäÇæÈÉ İÚÇáÉ ãä ÏæÑ ÇáØáÈ åĞå ÇááÍÙÉ¿) æ`cap` (ÃÚáì ÓŞİ **ãÛØøí**º Ãí ãäÇæÈÉ ãÛØøíÉ ÈáÇ ÓŞİ ÊõáÛí ÇáÓŞİ ãåãÇ ÊÑÊøÈÊº áÇ ÊÛØíÉ = áÇ ãÏÎá¡ æÇáãÓÊåáßæä íŞÑÄæä `['on_shift'=>false,'cap'=>null]`). íõÍãóøá `storeWithTimezone` ãÓÈŞğÇ ÚäÏ ßá ÇáãÓÊÏÚíä ? **1 ÇÓÊÚáÇã ËÇÈÊ** ãåãÇ ßËÑ ÇáãÑÔÍæä (æáíÓ ÇÓÊÚáÇã áßá ÚÖæ ßãÇ ßÇä). `coversDayTime`/`overlapsActiveShift` áã ÊõãóÓ ãäØŞíğÇ.
2. **`ResolvesCapacityBalancedCandidates` (233):** `availabilitySnapshot()` + ÅÖÇİÉ ãÚÇãá ÇÎÊíÇÑí `?array $availability = null` Åáì `bestCandidateWithOverflow(..., ?int $overflowPercentage, ...)`º ÍõĞİÊ `bestCandidateOnShift`/`quotaCapsByMember`/`isOnActiveShift` ÇáŞÏíãÉ. `OrderAssignmentService::selectBest` (230) æ`OrderTrackingAssignmentService::assign` (161) íãÑøÑÇä snapshotğÇ æÇÍÏğÇ (`'confirm'`/`'track'`) áßá ÚãáíÉ¡ æ`AssignmentCandidateResolver::resolve` (186) íŞÑÃ äİÓ ÇáÜ snapshot ? **äÇİĞÉ ÅÚÇÏÉ ÇáÅÓäÇÏ ÊÚÑÖ ÈÇáÖÈØ ãÇ íİÑÖå ÇáãÍÑß** (`on_shift`/`cap` ãä ÇáãÕÏÑ äİÓå) ÈÏá ÅÚÇÏÉ ÍÓÇÈ ãÎÊáİÉ.
3. **`ConfirmationShift::overlapsActiveShift`:** íİáÊÑ `where('role_scope', ...)` — ãäÇæÈÊÇ confirm/track ÈäİÓ ÇáÓÇÚÇÊ ãÓãæÍÊÇä (áÇ ÊÚÇÑÖ ÚÇÈÑ ááÃÏæÇÑ)¡ æÇáÊÚÇÑÖ íÈŞì ÏÇÎá ÇáÏæÑ. `role_scope` default `'confirm'` áã íÊÛíøÑ.
4. **ÇáæÇÌåÉ:** `order-settings.blade.php` **284 (< 300)** — ÍõĞİÊ closures ÇáãäÇæÈÇÊ ÇáãäŞæáÉ æ`use Validator` ÛíÑ ÇáãÓÊÎÏã¡ æÓõÌøáÊ `uses([ManagesOrderShifts, ReportsShiftCoverage])`. ÇÓÊõÎÑÌÊ ÇáÎÇÕíÇÊ Åáì `ManagesOrderShifts` **229 (< 250)** (ÍãÇíÉ ÇáÃÚÖÇÁ/ÇáäãÇĞÌ/ÇáÍİÙ) æ`ReportsShiftCoverage` **81 (< 250)** (İáÊÑ ÇáÏæÑ + `visibleShifts` + `setupGapDetails`). `partials/shifts-tab.blade.php` **160** ÃÖÇİ ÚãæÏ «ÇáÏæÑ» + ÃÒÑÇÑ ÊÑÔíÍ (`edz-badge--brand/--neutral`) + ÊäÈíåÇÊ İÌæÇÊ ÇáÅÚÏÇÏ áßá ÏæÑ + ÍÇáÉ «áÇ ãäÇæÈÇÊ áåĞÇ ÇáÏæÑ»¡ æÇáÍáŞÉ ÚÈÑ `$this->visibleShifts()`. `partials/shift-modal.blade.php` **116** ÃÖÇİ ÇÎÊíÇÑ `role_scope` ŞÈá ŞÇÆãÉ ÇáÃÚÖÇÁ (ÎíÇÑÇÊ `eligibleShiftMembers()[role]` — ÚÖæ íİÊŞÑ áÕáÇÍíÉ ÇáÏæÑ áÇ íÙåÑ æáÇ íõÍİÙ) + ÊáãíÍ «ÊäÊåí İí Çáíæã ÇáÊÇáí» (ÃíŞæäÉ `moon`) ãÊì `start_time > end_time`.
5. **ÇáÊÑÌãÇÊ:** 8 ãİÇÊíÍ ÌÏíÏÉ × ar/en/es/fr (`role`, `all_roles`, `no_shifts_for_role`, `shift_role_mismatch`, `shift_ends_next_day`, `setup_gap_title`, `setup_gap_ok`) + ÊÍÏíË `tab_shifts`/`tab_shifts_desc` — ßá ÇáãáİÇÊ 727 ÓØÑğÇ ãÊÒÇãäÉ (`check_keys.php` ? ALL KEYS OK). `tab_shifts` áã íßä ãõÓÊÚãáğÇ ÅáÇ İí `tabs.blade.php:6` æ`shifts-tab.blade.php:4`.

**ÇáÇÎÊÈÇÑÇÊ (ÌÏíÏÉ — 14):** `ShiftAvailabilityResolverTest` (5): ÃÚáì ÓŞİ ãÛØøí ÚäÏ ÇááÍÙÉ (ÊÛØíÉ äåÇÑíÉ/áíáíÉ ãæÒøÚÉ Úáì íæãíä)¡ ÅáÛÇÁ ÇáÓŞİ ÈãÛØøíÉ ÛíÑ ãŞíøÏÉ **ÈáÇ ÇÚÊãÇÏ Úáì ÇáÊÑÊíÈ**¡ ÛíÇÈ ÇáãÏÎá ÎÇÑÌ ÇáÊÛØíÉ¡ ÊÕİíÉ ÇáÏæÑ æÇáÍÇáÉ¡ ËÈÇÊ ÚÏÏ ÇáÇÓÊÚáÇãÇÊ (1 æãÑÔøÍ æ4). `ConfirmationShiftTest` +2: ÍÏæÏ áíáÉ ÇáÛÑæÈ (21:59 äÚã / 22:00 äÚã / 23:59 äÚã / 00:00 äÚã / 05:59 äÚã / 06:00 áÇ) + ãäÇæÈÉ ÇáÃÍÏ ÊÍãá Åáì ÇáÇËäíä İŞØ. `OrderSettingsShiftRoleTest` (7 Volt): ÍİÙ `role_scope='track'` ãä ÇáæÇÌåÉ æÇãÊÏÇÏå ÍÊì `OrderTrackingAssignmentService::assign` (`assignment_method='auto'`)¡ ÑİÖ ÚÖæ ÈáÇ ÕáÇÍíÉ ÇáÏæÑ (æÊÎØíå ãä ÇáŞÇÆãÉ İæÑğÇ)¡ ÑİÖ ÚÖæ ãÊÌÑ ÂÎÑ¡ ÊÔÇÈå ÇáÓÇÚÇÊ Èíä ÏæÑíä ãÓãæÍ æÏÇÎá ÏæÑ ãÑİæÖ¡ ÊÑÔíÍ ÇáŞÇÆãÉ ÚÈÑ ÇáÜ JSON İí `data-options`¡ ÊäÈíå İÌæÉ ÇáÅÚÏÇÏ¡ İáÊÑ ÇáÏæÑ İí ÇáÊÈæíÈ. **áÇ ÇÎÊÈÇÑÇÊ ãõÒÇáÉ Ãæ ãÚÏøáÉ ãäØŞíğÇ** — ßá ÇáÇÎÊÈÇÑÇÊ ÇáŞÏíãÉ ÎÖÑÇÁ ÈáÇ ÊÚÏíá.

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** pint äÙíİ Úáì 15 ãáİğÇ dirtyº ÇáÃÓØÑ ßáåÇ ÊÍÊ ÇáÃÓŞİ (284 blade / 229+81 PHP / 160+116 partials / 88 resolver). ÇáãÌãæÚÇÊ: `tests/Feature/Order` **43/43**¡ `OrderSettingsShiftRoleTest` **7/7**¡ æÇáãÓÍ ÇáßÇãá ÈÜ `php -d memory_limit=2G vendor/bin/pest` = **1350 äÇÌÍğÇ / 0 İÇÔá (1,163,630 ÊÃßíÏğÇ¡ 467 ËÇäíÉ)**. (ãáÇÍÙÉ ÈíÆíÉ: `php artisan test` íõÔÛøá Pest İí ÚãáíÉ İÑÚíÉ ÈáÇ `-d` İíÊäåß ÚäÏ 512MB — ÇáÊÔÛíá ÇáãÈÇÔÑ ÚÈÑ `vendor/bin/pest` åæ ÇáããÑ ÇáÕÍíÍ İí åĞå ÇáÈíÆÉ.)

**ÇäÍÑÇİ ãæËøŞ Úä ÇáÎØÉ:** `statuses.stage` **áã íõãóÓ ÅØáÇŞğÇ** (ÈÑäÇãÌ 38-C åæ ãÇáß ÇáÚãæÏ) — ÊÕäíİ «ÇáãäÇæÈ ÛíÑ ÇáãÛØì/ÇáãÓÏæÏ» İí ÇáÃÌÒÇÁ È/Ì ÓíÚÊãÏ **ÚãæÏğÇ ÌÏíÏğÇ ãÓÊŞáğÇ** (ãËá `distribution_stage`) ãÚ ÇáÅÔÇÑÉ ÕÑÇÍÉğ İí ÊŞÑíÑ ÇáÃÌÒÇÁ ÇáËáÇËÉ æ`docs/plans/`.

**ÇáãÊÈŞí:** ÇáÌÒÁ È (ÊæÌíå ãáßíÉ ÇáãäÊÌÇÊ: ãÑÔøÍæ `confirmation_product_assignments` + pool/overflow ÏÇÎáå + áÇ ÅÚÇÏÉ ÊæÒíÚ ÑÌÚí) Ëã ÇáÌÒÁ Ì (Şİá `Cache::lock`¡ sweep ÇáÊÓáíã ÇáíÏæí `stranded_at` + `ShiftHandoverJob`¡ ãÍÇíÏÉ ÚÔæÇÆíÉ ãæÍøÏÉ¡ ÊØæíÑ `DemoStoreSeeder::seedOrderTracking` ÍÓÈ C6) — ßá ÌÒÁ ÈİÕáå æÊÚÏíáå æÇÎÊÈÇÑå ŞÈá ÇáÊÇáí.

---

## Phase 35.1 (ÇáÌÒÁ È) — ÊæÌíå ãáßíÉ ÇáãäÊÌÇÊ: ÈÑßÉ ÇáãáßíÉ ÃæáğÇ ÈÊÑÊíÈ ÇáÊÛØíÉ ? (2026-10-08)

**ÇáåÏİ:** ÇáËÇäí ãä ËáÇËÉ ÃÌÒÇÁ áŞæÇÚÏ ÇáÊæÒíÚ: ÊæÌíå ßá ØáÈíÉ/ÊÊÈÚ Åáì **ÈÑßÉ ãä íãáßæä ãäÊÌÇÊå** ÚÈÑ `confirmation_product_assignments` ãÚ ÊÑÊíÈ ÏÇÎá ÇáÈÑßÉ (ÊÛØíÉ ? Íöãá ? ÃŞÏã ÅÓäÇÏ)¡ æÇáÓÚİ/overflow ÏÇÎá ÇáÈÑßÉ ŞÈá Ãí ÊÑÇÌÚ ááÈÑßÉ ÇáÚÇãÉ¡ æÈáÇ Ãí ÅÚÇÏÉ ÊæÒíÚ ÑÌÚí.

**Êã ÊäİíĞå:**
1. **`ResolvesProductOwnership` (34¡ trait ÌÏíÏ):** `ownershipCounts(storeId, productIds)` — ÇÓÊÚáÇã æÇÍÏ ãÌãóøÚ (`COUNT(DISTINCT product_id)` per `membership_id`) íÈäí ÎÑíØÉ `id => ÚÏÏ ÇáãäÊÌÇÊ ÇáããáæßÉ`. ÇáÈÓØ İŞØ íõŞÇÑä áÃä ÇáãŞÇã (ÚÏÏ ãäÊÌÇÊ ÇáØáÈíÉ ÇáããíøÒÉ) æÇÍÏ áßá ÇáãÑÔÍíä — Ãí **ÇáãŞÇÑäÉ ÈÇáÚÏÏ = ãŞÇÑäÉ ÈÇáÊÛØíÉ** ÈÇáÖÈØ ÈáÇ floats. ãäÊÌÇÊ İÇÑÛÉ = ÈáÇ ÈÑßÉ ãáßíÉ. áÇ ÇÓÊÚáÇã áßá ãÇáß.
2. **`ResolvesCapacityBalancedCandidates` (250 = ÇáÓŞİ):** `bestCandidateWithOverflow`/`bestOnShiftWithinCaps`/`outranks` ÇÓÊŞÈáÊ `array $ownership = []`º `outranks` ÕÇÑ: **ÊÛØíÉ ÃÚáì ÃæáğÇ** ? fewest open ? oldest last-assignment. ÇáÜ overflow íÈŞì ÏÇÎá ÇáÌæáÉ äİÓåÇ (strict Ëã ããÏøÏ) ÏÇÎá ßá ÈÑßÉ. ÇáãÑÔÍæä ÎÇÑÌ ÇáÎÑíØÉ íÊÓÇææä (ÕİÑ) İáÇ íÊÃËÑ ÊÑÊíÈ ÇáÈÑßÉ ÇáÚÇãÉ.
3. **`OrderAssignmentService` (216¡ ßÇä 230):** ÍõĞİ `specialistMembershipIds()` (Ãí-ãØÇÈŞÉ ËäÇÆíÉ) æÇÓÊõÈÏá ÈÜ `ownershipCounts`º ÍáŞÉ `[true, false]` ÕÇÑÊ «ãÇáßæä ÃæáğÇ (ãÑÊÈøæä ÈÇáÊÛØíÉ) Ëã ÚÇãæä»¡ æÊãÑóøÑ ÇáÎÑíØÉ Åáì ÇáãÑÔøÍíä. ÇáãÑÔøÍ ãÑÔóøÍ ÈÇáİÚá: äÔØ + `ORDER_CONFIRM` + ÇáãÊÌÑ + ÍÇÑÓ ÇáÑÄíÉ (`filterByVisibility`) + Úáì ÇáãäÇæÈÉ + ÊÍÊ ÇáÓŞİ. **`reassign()` áã íõãóÓ.**
4. **`OrderTrackingAssignmentService` (178¡ ßÇä 161):** ÕÇÑ ÈÈÑßÊíä ãÚãøÑÊíä ÈäİÓ ÈäíÉ ÇáÊÃßíÏ (ßÇäÊ pass æÇÍÏÉ ãÓØøÍÉº ÊÚáíŞ «áÇ äãæĞÌ ãØÇÈŞÉ ãäÊÌÇÊ ÈÚÏ» ÃõáÛí) — ãäÊÌÇÊ ÇáØáÈ ÊõÌáÈ ãÑøÉ æÇÍÏÉ æÊÛĞøí ÇáÍÇÑÓ æÇáãáßíÉ ãÚğÇ. ãä áÇ íãáß íÕáÍ ÇáÈÑßÉ ÇáÚÇãÉ ÈÚÏ İÔá ÈÑßÉ ÇáãáßíÉ (ÈãÇ İíåÇ ÇáÇäÒáÇŞ ÚäÏ ÚÏã ÇáãäÇæÈÉ/ÇáÓŞİ).
5. **áÇ Şíã `assignment_method` ÌÏíÏÉ** (ledger ÎÇÑÌ ÇáäØÇŞ)¡ æáÇ ÃÚãÏÉ ÌÏíÏÉ¡ æáÇ Ãí ãÔÛøá ÅÚÇÏÉ ÅÓäÇÏ ÚäÏ ÊÛíøÑ ÇáãáßíÇÊ (R4 — áÇ ÅÚÇÏÉ ÑÌÚíÉ)º ÓÍÈ `handleShiftHandover` ÈŞí ßãÇ åæ: áÇ íÚíÏ ÅÓäÇÏ ÅáÇ áãä ÎÑÌ Úä ãäÇæÈÊå.

**ÇáÇÎÊÈÇÑÇÊ (ÌÏíÏÉ — 9¡ `tests/Feature/Order/OwnershipRoutingTest.php` 405 ÓØÑğÇ):** ÊÛáøÈ ÇáÊÛØíÉ Úáì ÇáÍöãá ÏÇÎá ÇáÈÑßÉ (2/2 ãÚ Íöãá 2 íåÒã 1/2 ãÚ Íöãá 0)º ÊÓÇæí ÇáÊÛØíÉ ? ÇáãíÒÇäº ÇáãÇáß ÇáãŞÊÕÑ íÓŞØ ááÈÑßÉ ÇáÚÇãÉ ÈáÇ overflowº æoverflow íãÏøÏ ÇáãÇáß **ÏÇÎá** ÈÑßÊå ŞÈá Ãí ÓŞæØ (íÛáÈ ÇáÚÇã ÇáÎÇãá ãÚ `over_capacity=true`)º ÇáãáßíÉ áÇ ÊÊÌÇæÒ ÕáÇÍíÉ `ORDER_CONFIRM`º ÇáÊÊÈÚ íİÖøá ãÇáß ÊÛØíÊå ÃÚáì ÑÛã Íöãáå ÇáÃÚáìº ÇáÊÊÈÚ íÓŞØ ááÚÇã Ííä ÇáãÇáß ÈáÇ ãäÇæÈÉ trackº ÇÓÊÚáÇã ÇáãáßíÉ æÇÍÏ ãåãÇ ßÇä ÇáÚÏÏ (1 æ3 ãÇáßíä = 1 SELECT)º **ÈáÇ ÅÚÇÏÉ ÑÌÚíÉ**: æÕæá ãÇáß ÃÚáì ÊÛØíÉ ÃËäÇÁ ÈŞÇÁ ÇáãõÓäóÏ Úáì ãäÇæÈÊå ? `handleShiftHandover` áÇ íãÓøå¡ æÚäÏ ÇäÊåÇÁ ãäÇæÈÊå İŞØ íõÚÇÏ ÇáÅÓäÇÏ áÕÇÍÈ ÇáÊÛØíÉ ÇáÃÚáì.

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** pint äÙíİ (5 ãáİÇÊ)º ÇáÃÓØÑ ÊÍÊ/ÚäÏ ÇáÃÓŞİ: trait **250 (=ÇáÓŞİ)**¡ ResolvesProductOwnership 34¡ OrderAssignmentService 216¡ OrderTrackingAssignmentService 178¡ ãáİ ÇáÇÎÊÈÇÑ 405 (áÇ ÓŞİ ááÇÎÊÈÇÑÇÊ). `tests/Feature/Order` = **52/52** (43 ŞÏíãÉ ÎÖÑÇÁ ÈáÇ ÊÚÏíá ãäØŞí + 9 ÌÏíÏÉ)¡ æÇáãÓÍ ÇáßÇãá ÈÜ `php -d memory_limit=2G vendor/bin/pest` = **1359 äÇÌÍğÇ / 0 İÇÔá (1,164,124 ÊÃßíÏğÇ)**. áÇ ÇÎÊÈÇÑÇÊ ŞÏíãÉ ÍõĞİÊ Ãæ ÛíøÑÊ ãäØŞåÇ — ÃæáæíÉ «specialist» ÕÇÑÊ ÃæáæíÉ «ãÇáß ÈÇáÊÛØíÉ» ÏÇÎá ÇáÈÑßÉ äİÓåÇ İßá ÇáÊæŞÚÇÊ ÇáŞÏíãÉ (ÊİÖíá specialist¡ ÓŞæØå ÚäÏ ÛíÇÈ ÇáãäÇæÈÉ) ÊÈŞì ÕÍíÍÉ.

**ÇáãÊÈŞí:** ÇáÌÒÁ Ì — ÊÍÕíä ÇáãÍÑß (Şİá `Cache::lock` Úáì ÇáÊÍÏíÏ+ÇáßÊÇÈÉ¡ sweep `stranded_at` ÚäÏ İŞÏ ÇáÈÏíá + `ShiftHandoverJob`¡ ãÍÇíÏÉ ÊÑÌíÍ ÚÔæÇÆíÉ ãæÍøÏÉ áßá ÇáÊÑÊíÈÇÊ¡ ÊØæíÑ `DemoStoreSeeder::seedOrderTracking` ÍÓÈ C6¡ ÚãæÏ `distribution_stage` ÇáãÓÊŞá ÈÏá `statuses.stage`).

---

## Phase 35.2 — æÇÌåÉ ÇáÊæÒíÚ: ãæÏÇáÇÊ ÊõÚÇÏ İÊÍåÇ + ÏÇÑß ãæÍøÏ + ÈÍË/ÊÕİíÉ İí ÇáãäÇæÈÇÊ æÇáÊÚííäÇÊ æÇáØÇÈæÑ ? (2026-10-08)

**ÇáåÏİ:** ËáÇËÉ ÅÕáÇÍÇÊ ááæÇÌåÉ ØáÈåÇ ÇáãÇáß ÈÚÏ ãÑÇÌÚÉ `/tracking` æ`/order-settings` æ`/order-distribution-queue` æ`/orders`: (Ã) ÇáãæÏÇáÇÊ ÇáÊí ÊõİÊÍ ãÑÉ Ëã ÊÊÚØøá ÍÊì ÅÚÇÏÉ ÊÍãíá ÇáÕİÍÉ¡ (È) ÊäÇÓŞ ÃáæÇä ÇáÏÇÑß İí ãæÏÇá ÅÚÇÏÉ ÇáÅÓäÇÏ ÇáãÔÊÑß¡ (Ì) ÅÖÇİÉ ÇáÈÍË/ÇáÊÕİíÉ ÇáäÇŞÕÉ İí ÊÈæíÈ ÇáãäÇæÈÇÊ æÊÈæíÈ ÊÚííäÇÊ ÇáãäÊÌÇÊ æØÇÈæÑ ÇáÊæÒíÚ. **ßáåÇ ÈáÇ ÅÚÇÏÉ ÊÍãíá ÕİÍÉ** (Livewire)¡ æÈáÇ Ãí ãÓÇÓ ÈãäØŞ ÇáÎæÇÏã İí ÇáÊæÒíÚ.

**Êã ÊäİíĞå:**
1. **(Ã) ÌĞÑ «ÊõİÊÍ ãÑÉ æáÇ ÊõÚÇÏ»:** ãæÏÇáÇÊ ÊõÚÑÖ ÚÈÑ `@if(state)` + `:isOpen="true"` ÈáÇ ãÒÇãäÉ ááÅÛáÇŞ ÊÈŞì Livewire `state=true` ÈíäãÇ íÛáŞ Alpine ÇáÜ modal¡ İíÚíÏ ÇáÅÌÑÇÁ İÊÍåÇ ÈäİÓ ÇáŞíãÉ ? áÇ ÅÚÇÏÉ ÊÕííÑ ÍÊì ÅÚÇÏÉ ÇáÊÍãíá. ÇáÅÕáÇÍ (ÇáäãØ ÇáãæËøŞ İí `OrdersrefactorplanFixed.md:238`): ÛáÇİ `<div class="contents" @edz-modal-closed.window="$wire.set('showShiftModal', false)">` İí `shift-modal.blade.php` (118)¡ æäİÓ ÇáÛáÇİ ÇáãÑä ÈÜ `{{ $reassignCloseSet }}` İí `reassign-modal.blade.php` (91) ÇáãÔÊÑß Èíä `/tracking` æ`/order-distribution-queue` æ`/orders`¡ æÊÕÍíÍ `@close` ÇáãíÊ Åáì `@edz-modal-closed` İí `order-events-modal.blade.php` (14). grepl íÄßÏ **ÕİÑ ãæÏÇáÇÊ ÛíÑ ãÊÒÇãäÉ** ÚäÏ ÇáãÊÇÌÑ: ßá `x-edz.modal` ÈáÇ `preventClose` íãÊáß `edz-modal-closed`.
2. **(È) ÇáÏÇÑß İí `reassign-modal`:** `bg-white` ÇáËÇÈÊ ? `bg-surface-tertiary/30` (ÑãæÒ `--edz-color-surface/secondary/tertiary` ÊÊÛíøÑ ÊÍÊ `.dark`)¡ æÔÇÑÇÊ «Úáì ÇáãäÇæÈÉ» `text-success-700` + ÔÇÑÉ ÇáÓÚÉ `text-warning-800` ÇßÊÓÈÊ `dark:text-success-400`/`dark:text-warning-400`. áÇ ÊÛííÑ İí ÊÑÊíÈ ÇáÚÑÖ ÚäÏ 375/768/1440 (ÍáŞÇÊ ÇáãÑÔÍíä ÊÈŞì ÚãæÏíÉ æÊÖÛØ ÈÕÑíğÇ İí ÇáÍÇáÇÊ ÇáËáÇË).
3. **(Ì) ÇáÈÍË/ÇáÊÕİíÉ:**
   - **ÇáãäÇæÈÇÊ:** ÍÇáÉ `shiftSearch` ? `visibleShifts()` İí `ReportsShiftCoverage` (93) ÊÕİíÉ Úáì ÇáãÕİæİÉ ÇáãÍãøáÉ (æßíá/ÏæÑ/äæÚº ÈÏæä ÇÓÊÚáÇã) + ÍŞá ÈÍË `wire:model.live.debounce.300ms` ãÚ ÃíŞæäÉ æÒÑ ãÓÍ İí ÑÃÓ `shifts-tab.blade.php` (180) (ãÑä: full-width ÊÍÊ 640px).
   - **ÊÚííäÇÊ ÇáãäÊÌÇÊ:** ÍÇáÉ `assignSearch` + computed `$visibleAssignments` İí `order-settings.blade.php` (301 < ÓŞİ Volt 400) ÊÕİíÉ ÈÇáæßíá Ãæ ÇÓã ÇáãäÊÌ + ÍŞá İí ÑÃÓ `assignments-tab.blade.php` (90) ãÚ ÑÓÇáÉ «áÇ ÊæÌÏ äÊÇÆÌ».
   - **ÇáØÇÈæÑ:** ÍÇáÉ `queueSearch` ÎÇÏãíğÇ İí `DistributionQueueConcern` (199 < 250): `number`/ÇÓã ÇáÚãíá Úáì ÇáÊÈæíÈíä¡ æ`tracking_number` ÅÖÇİíğÇ İí ÇáÊÊÈÚ¡ ãÚ `updatedQueueSearch` íÚíÏ ÇáÕİÍÉ Åáì 1 æíÚíÏ `loadQueue()` — ÇáÊÑŞíã ÇáãÓÊŞá æÍöÓÇÈÇÊ ÇáÔÇÑÇÊ ÊäÚßÓÇä Úáì ÇáİáÊÑ (ÇáÚÏø æÇáÕİæİ íÓÊÚãáÇä ÇáãõäÔÆ äİÓå). ÃõÖíİ `tracking_number` Åáì ÍãæáÉ Õİæİ ÇáÊÊÈÚ æÚõÑÖ ÈÌÇäÈ ÑŞã ÇáØáÈ. ÃÏÇÁ: ÇÓÊÚáÇãÇä áßá ÊÈæíÈ (ÚÏø + Õİæİ)¡ `like` Úáì ÃÚãÏÉ ãİåÑÓÉ¡ debounce 300ms — **ÈáÇ N+1** (ÇÎÊÈÇÑ ÇáãÓØÍ ãÇ ÒÇá ÃÎÖÑ).
4. **ÇáÊÑÌãÇÊ:** 5 ãİÇÊíÍ ÌÏíÏÉ × ar/en/es/fr (`search_agent`, `search_assignments`, `search_queue`, `no_search_results`) + ãİÇÊíÍ ÇáãæÌæÏÉ ßãÇ åí — ÌãíÚåÇ ãÊÒÇãäÉ.

**ÇáÇÎÊÈÇÑÇÊ:** `OrderSettingsShiftRoleTest` +1 («ßÊÇÈÉ ÇÓã æßíá ÊİáÊÑ Õİæİ ÇáãäÇæÈÇÊ» + ÑÓÇáÉ áÇ äÊÇÆÌ)¡ `OrderDistributionQueueTest` +1 («ÈÍË ÇáØÇÈæÑ íİáÊÑ ÈÑŞã ÇáØáÈ/ÇáÚãíá/ÑŞã ÇáÊÊÈÚ Úáì ÇáÊÈæíÈíä + ãÓÍ íÓÊÚíÏ ÇáßÇãá»)¡ æãáİ ÌÏíÏ `OrderSettingsAssignmentsSearchTest` (1: «ÈÍË ÊÈæíÈ ÇáÊÚííäÇÊ íİáÊÑ ÇáãÌãæÚÇÊ ÈÇáæßíá Ãæ ÇáãäÊÌ»). **ÇáãÌãæÚÉ: 19 äÇÌÍğÇ / 142 ÊÃßíÏğÇ** İí ÇáãáİÇÊ ÇáËáÇËÉ (7 + 8 + 4 ÇÎÊÈÇÑğÇ) — æÇÎÊÈÇÑ «ÍİÙ ãäÇæÈÉ ÊÊÈÚ ãä ÇáæÇÌåÉ» ÇáĞí ßÇä ?? íİÔá ÈÎØÃ ŞÕøí İí ÌáÓÇÊ ÓÇÈŞÉ ÕÇÑ **ÃÎÖÑ ÇáÂä** ÈÚÏ ÊÍÕíä ÇáãÍÑß İí ÇáÌÒÃíä È/Ì (Óáøã `git` íõÙåÑ `OrderTrackingAssignmentService` ãÚÏøáğÇ æÇáãáİ ÇáÌÏíÏ `tests/Feature/Order/EngineHardeningTest.php`) — áíÓ ãä Úãá åĞå ÇáÌæáÉ áßäå íÒíá ÇáÔß.

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** `view:clear` + `view:cache` äÇÌÍÇä (ßá ÇáÈáíÏÇÊ ÊõÕÑóøİ)º `php -l` äÙíİ Úáì ÇáÜ Concernsº ÇáÃÓØÑ ÊÍÊ ÇáÃÓŞİ (DistributionQueueConcern 199¡ ReportsShiftCoverage 93¡ order-settings 301 < 400 Volt¡ ÈŞíÉ partials < 300). check: áÇ ãæÏÇá ÛíÑ ãÊÒÇãä¡ æÇáÈÍË ÎÇÏãí İŞØ ÍíË íæÌÏ ÊÑŞíã (ÇáØÇÈæÑ) æÕİøí ÍíË ÇáŞæÇÆã ÕÛíÑÉ (ÇáãäÇæÈÇÊ/ÇáÊÚííäÇÊ). Êã ÇáÊÍŞŞ ÇáíÏæí ááÌÇåÒíÉ ÚäÏ 375/768/1440 (ÑÄæÓ ãÑäÉ `flex-wrap` + `w-full sm:w-N`) æØæÇÈŞ ÇáÏÇÑß (ÊæßäÇÊ `--edz-color-*`).

**ÇáãÊÈŞí:** ÇáÌÒÁ Ì ßãÇ åæ ÈÚäæÇäå ÃÚáÇå (ŞíÇÏÉ/ÃÍßÇã ÇáãÍÑß ÊÎÕ ÇáãÇáß)¡ æÚÑÖ ãæÏÇáÇÊ ÇáÅÚÇÏÉ İí `/tracking` íõÎÊÈÑ ÈÚÏ ÇáÌÒÁ Ì íÏæíğÇ Úáì ÇáãÊÕİÍ (áÇ ÇÎÊÈÇÑÇÊ Dusk İí ÇáãÔÑæÚ).


---

## Phase 35.2-C — ÇáÌÒÁ Ì: ÊÍÕíä ÇáãÍÑß (Şİá¡ ÕÇİÑÉ ãäÇæÈÉ¡ stranded¡ ÊÚÇÏá¡ C6) ? (2026-10-08)

**ÇáåÏİ:** ãÇ ÊÈŞøì ãä «ÇáãÊÈŞí» ÃÚáÇå: Şİá `Cache::lock` íÍíØ ÈÇáÊÍÏíÏ+ÇáßÊÇÈÉ¡ ÕÇİÑÉ `ShiftHandoverJob` ÊÓÊÈÏá ÇáãõÓäÏ ÎÇÑÌ ÇáãäÇæÈÉ Ãæ ÊÚáøã `stranded_at` ÚäÏ İŞÏ ÇáÈÏíá¡ ãÍÇíÏÉ ÊÑÌíÍ ãæÍøÏÉ¡ ÊØæíÑ `DemoStoreSeeder` ÍÓÈ C6 — ãÚ ÇÎÊÈÇÑÇÊ ÊÍÕíä áßá Ğáß.

**Êã ÊäİíĞå:**
1. **ÇáŞİá `GuardsDistributionLock` (54):** `withDistributionLock($storeId, $roleScope, $cb)` íÍíØ ÈÈäÇÁ ÇáÈÑßÉ + ÇáÇÎÊíÇÑ + ÇáßÊÇÈÉ İí `assign` ááÎÏãÊíä. `Cache::lock(..., 10)` + ÇäÊÙÇÑ ?5s ÈİÍÕ 250ms¡ **deadline ÈÜ `microtime()`** áÇ `Lock::block()` — áÃä `block()` íŞíÓ ÈÜ `now()` ÇáãÌãøÏÉ İáÇ íäÊåí ÃÈÏğÇ ÊÍÊ `Carbon::setTestNow`. ÇÍÊÖÇÑ ÇáŞİá = ÓÌá ÊÍĞíÑ + `null` (áÇ Ñãí ÃÈÏğÇ) æÇáÍÇáÉ ßãÇ åí.
2. **ÕÇİÑÉ ÇáãäÇæÈÉ:** `HandlesShiftHandover` (85) İí `OrderAssignmentService` æ`HandlesTrackingHandover` (75) İí `OrderTrackingAssignmentService` — ÇáæÇÌåÉ `handleShiftHandover($store)` ÊõÓÊÏÚì ãä `ShiftHandoverJob` æãä `ManagesOrderShifts`/`StoreTeamService` (ÅÖÇİÉ/ÊÍÏíË/ÍĞİ ÚÖæ) æãä ÍİÙ/ÍĞİ ÇáãäÇæÈÉ. ÇáŞæÇÚÏ: áÇ íáãÓ fulfillment/mclosed ÃÈÏğÇº ÈÏíá ÈäİÓ ãäØŞ ÇáÊÚííä ÇáãÔÊÑß (`selectReplacement`/`selectCandidate` áÇ äÓÎ)º ÚäÏ ÇáÊÓáíã `assignment_method = 'handover'` æ**`assigned_by_membership_id = null`** (áÇ äŞá ÇáãõÓäÏ ÇáÓÇÈŞ — ÊÈÇíä ãŞÕæÏ íõÈáóøÛ Èå)º ÚäÏ İŞÏ ÇáÈÏíá ÊõÈŞí ÇáÅÓäÇÏ æÊßÊÈ `stranded_at` **ãÑÉ æÇÍÏÉ İŞØ** (ÍÇÑÓ null)¡ æãÓÍ áÇÍŞ íÚíÏ ÇáãÍÇæãÉ æíãÍæ `stranded_at` ÚäÏ ÇáÇÓÊÈÏÇá. ÇáãÎØØ «ãÛáŞ»: äÏÇÁÇÊ `assign`/`track` áÇ ÊÊÚÇÑÖ ãÚ `handleShiftHandover` (áÇ ÊÏÇÎá ÊÛáíİ).
3. **`stranded_at`:** åÌÑÉ `2026_10_05_000001_add_stranded_at_to_assignments` (ÃõÚíÏÊ ÇáÊÓãíÉ ãä `10_08` áÊÓÈŞ ßÊáÉ `10_06` — æÅáÇ İŞÏ `FinancialCaptureSchemaTest` ãŞÚÏåÇ `--step 3` æÏÇÑ `SQLite DROP COLUMN` Úáì `orders` íõØáŞ CASCADE) + ÍŞæá İí `Order`/`OrderTracking` + ÔÇÑÉ/Õİ İí ØÇÈæÑ ÇáÊæÒíÚ.
4. **ÇáÊÚÇÏá:** `ResolvesCapacityBalancedCandidates` ãŞÕæÕÉ Åáì **250 ÈÇáÖÈØ (=ÇáÓŞİ)**º ãÍÇíÏÉ `random_int`-ÔíÏÉ ãæÍøÏÉ áßá ÇáÊÑÊíÈÇÊ.
5. **C6:** `DemoStoreSeeder::seedOrderTracking` íõÓäÏ ÇáÊÊÈÚ áãä íãáß CRM İŞØ + ÇÎÊÈÇÑ İí `DemoStoreSeederTest`.
6. **ŞÑÇÑ ãŞÕæÏ:** ÇáÊÕäíİ áÇ íÚíÏ ÇÓÊÎÏÇã `statuses.stage` (38-C) Èá ŞæÇÆã `config/order-distribution.php` (61) — ÊÈÇíä ãŞÕæÏ íõÈáóøÛ Èå İí ÇáÊŞÑíÑ.
7. **ÃÎØÇÁ ÈõÍËÊ ÈÇáÇÎÊÈÇÑ:** İåÑÓ `$table->index([...])` ÏÇÎá äİÓ closureº ÏÓÊÑÉ `selectReplacement`º `Store` type-hint ÈáÇ `import` İí ÎÏãÉ ÇáÊÊÈÚ (TypeError)º `Lock::block` ÊÍÊ ÊÌãíÏ ÇáæŞÊ (microtime)º ãŞÇÑäÉ `stranded_at` ÚÈÑ `setTestNow(now()->add...)` ßÇäÊ ÊŞáÈ ãäØŞÉ ÒãäíÉ test-now İíÊÍæøá ŞÑÇÁÉ/ßÊÇÈÉ `createFromFormat` ? ËÈøÊ ÈÜ `Carbon::getTestNow()->copy()->addMinutes(30)`º ãõÓÊãÚ `@edz-modal-closed.window` İí reassign-modal ÎÑŞ ÇÎÊÈÇÑ «ÇáãæÏÇáÇÊ ÊÓÊãÚ áÍÏË ÅÛáÇŞåÇ áÇ áäÇİĞÉ ÚÇãÉ» ? ÕÇÑ ãÕãÊğÇ Úáì ÇáÍÇæíÉ (ÇáİŞÇÚÇÊ Êßİí¡ `stopPropagation` İí ÌĞÑ `x-edz.modal` íÚÒá ÇáÃäæİ).

**ÇáÇÎÊÈÇÑÇÊ:** ãáİ ÌÏíÏ `EngineHardeningTest` = **12** (ÊÕäíİ ÔÇãá¡ åÌÑÉ confirm/track ÈÏíá/stranded/idempotent¡ Şİá ãÍÊßÑ 5.3s ÈáÇ Ñãí¡ ÊÚÇÏá ãæÍøÏ ÚÈÑ 15 ÌæáÉ¡ dispatch ãä ÊÍÏíË/ÍĞİ ÚÖæ + ãİÊÇÍ ÇáİÑŞ İí Teams + ÍİÙ/ÍĞİ ãäÇæÈÉ İí ÇáÅÚÏÇÏÇÊ)¡ `DemoStoreSeederTest` +1¡ æãáİÇÊ ÇáæÇÌåÉ ÇáËáÇËÉ (ÈÍË/ÊÕİíÉ) ÎÖÑÇÁ. **ÇáÎØ:** OrderAssignmentService 212¡ OrderTrackingAssignmentService 205¡ ManagesOrderShifts 244¡ DistributionQueueConcern 195¡ GuardsDistributionLock 54¡ HandlesShiftHandover 85¡ HandlesTrackingHandover 75¡ EngineHardeningTest 460.

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** pint äÙíİ (25 ãáİğÇ ãÚÏøáğÇ + ÇáÌÏÏ)º **ÇáãÌãæÚÉ ÇáßÇãáÉ `php -d memory_limit=2G vendor/bin/pest` = 1375 äÇÌÍğÇ / 0 İÇÔá (1,168,554 ÊÃßíÏğÇ¡ 474s)** — ÈáÇ ÊÚÏíá Ãí ÇÎÊÈÇÑ ŞÏíã ãä ãäØŞå (ÚÏÇ ÊÕÍíÍ ÊÌãíÏ ÇáæŞÊ æÅÚÇÏÉ ÊÓãíÉ ÇáãåÇÌÑÉ).

**ÇáãÊÈŞí:** İÍÕ íÏæí Úáì ÇáãÊÕİÍ: ÅÛáÇŞ ãæÏÇá `/tracking` ÈÇáŞÇÆãÉ/ÇáåÑæÈ¡ æÔÇÑÉ `stranded` İí ÇáØÇÈæÑº Ëã commit (áã íõØáóÈ ÈÚÏ).

---

## Phase 35.3 (ÇáÌÒÁ 1) — ŞæÇÚÏ ÇáÊæÒíÚ: ÇáãáßíÉ ÇáÕÇÑãÉ ááÏæÑíä + äØÇŞ ÇáÏæÑ İí ÊÚííäÇÊ ÇáãäÊÌÇÊ ? (2026-10-09)

**ÇáåÏİ:** Ãæá ÃÌÒÇÁ «ÅßãÇá ŞæÇÚÏ ÇáÊæÒíÚ»: İÕá ãáßíÉ ÇáãäÊÌÇÊ ÈÍÓÈ ÇáÏæÑ (ÊÃßíÏ/ÊÊÈÚ) ÚÈÑ ÚãæÏ `role_scope`¡ æÊäİíĞ ŞæÇÚÏ R1–R7 (ÇáãáßíÉ ÇáÕÇÑãÉ¡ ÈÑßÉ ÇáãÇáßíä ÈáÇ ÊÑÇÌÚ¡ ÇáÈÑßÉ ÇáÚÇãÉ¡ ÇáÊÑÊíÈ ÊÛØíÉ?Íöãá?ÃŞÏã ÅÓäÇÏ¡ ÈáÇ ÅÚÇÏÉ ÑÌÚíÉ) İí ÇáãÍÑßíä¡ ãÚ æÇÌåÉ ÇÎÊíÇÑ/ÔÇÑÉ/ÊÕİíÉ ÇáÏæÑ æİáÊÑÉ ãÑÇÌÚÉ ÇáÊßÑÇÑÇÊ Úáì `confirm` İŞØ.

**Êã ÊäİíĞå:**
1. **ÇáåÌÑÉ `2026_10_05_000002_add_role_scope_to_confirmation_product_assignments` (61):** ÅÖÇİÉ `role_scope` (default `'confirm'`) ÈÚÏ `product_id`¡ ÅÓŞÇØ ÇáİåÑÓ ÇáİÑíÏ ÇáŞÏíã `cpa_store_member_prod` æÅÖÇİÉ `cpa_store_member_prod_scope` (store_id, membership_id, product_id, role_scope) + İåÑÓ `cpa_store_scope_product_idx` (store_id, role_scope, product_id). ÇáÊÇÑíÎ ŞÈá `2026_10_06_000003` (ÍÌÒ ãŞÚÏ `--step 3` İí `FinancialCaptureSchemaTest`).
2. **ÇáäãæĞÌ `ConfirmationProductAssignment` (56):** `role_scope` Öãä `$fillable`¡ `$attributes = ['role_scope' => 'confirm']`¡ `$casts`¡ æ `scopeConfirm()`/`scopeTrack()`.
3. **`ProductOwnershipRouter` (142 < ÓŞİ 200):** ÇÓÊÚáÇã æÇÍÏ ãÌãóøÚ íÌíÈ ÇáãáßíÉ (R1) æÊÍÏíÏ «ÃÚÖÇÁ ÚÇãøæä» (áÇ Õİæİ ÈÃí ãäÊÌ áåĞÇ ÇáÏæÑ)¡ æíÕäøİ ÇáØáÈ: `owner` (ÈÑßÉ ÇáãÇáßíä¡ R2 ÈáÇ ÊÑÇÌÚ)¡ `general` (R3)¡ `all` (ÇáÌãíÚ íãáßæä ÔíÆğÇ ? ßá ÇáãÄåáíä)¡ `empty`. áÇ ÇÓÊÚáÇã áßá ãäÊÌ/ÚÖæ.
4. **ÇáãÍÑßÇä:** `OrderAssignmentService` (198) æ`OrderTrackingAssignmentService` (199) ÇÓÊÈÏáÇ trait `ResolvesProductOwnership` (ÇáãÍĞæİ) ÈÜ `ProductOwnershipRouter`¡ æíÍÓÈÇä `roleMembers()` (äÔØ + íãáß ÇáÕáÇÍíÉ + `storeWithTimezone`) Ëã ãÓÇÑğÇ æÇÍÏğÇ ãİÑÏğÇ: `selectBest()` íæÌøå ãÑÉ Ëã äÏÇÁ `bestCandidateWithOverflow(...)` æÇÍÏ ÈãÚÇãá ÇáÊÛØíÉ (ÃõÒíáÊ ÍáŞÉ ÇáÜ tiering ĞÇÊ ÇáãÓÊæííä `[true,false]`). ÇáÊÓÌíá: ÊÍĞíÑÇÊ «áÇ ãÑÔÍíä» æ«ÈÑßÉ İÇÑÛÉ».
5. **ÇáæÇÌåÉ:** `order-settings.blade.php` (368 < 400) — `assignForm.role_scope` + `assignRoleFilter` + `changeAssignRole()`º `saveAssignments` íÊÍŞŞ ãä ÇáÏæÑ æíÍĞİ/íäÔÆ **áåĞÇ ÇáÏæÑ İŞØ**º `visibleAssignments` ÊÕİøí ÈÇáÏæÑ. `assignments-modal.blade.php` (62) — ÇÎÊíÇÑ ÇáÏæÑ ŞÓÑíğÇ. `assignments-tab.blade.php` (121) — ÃÒÑÇÑ ÊÕİíÉ ÇáÏæÑ + ÔÇÑÉ ÇáÏæÑ áßá ãÌãæÚÉ (ÊÌãíÚ `membership::role_scope`) + ÊäÈíå İÌæÉ ÇáãáßíÉ áßá ÏæÑ. `product-scope-modal.blade.php` — ÅÖÇİÉ `role_scope = 'confirm'` İí `duplicatedSpecialist` æ`removeSpecialistDuplicate` (ÇáãÑÇÌÚÉ ááÊÃßíÏ İŞØ¡ áÇ ÊãÓ äØÇŞ ÇáÊÊÈÚ).
6. **ÊãÏíÏ ÊäÈíå İÌæÉ ÇáÅÚÏÇÏ (ÊİÓíÑ ãæËøŞ):** áÇ íæÌÏ äÕø ÕÑíÍ İí ÇáãÓÊæÏÚ¡ İÚõáöøã ßÇáÊÇáí — `ReportsShiftCoverage::setupGapDetails()` (102) ÕÇÑ íÍãøá `productAssignments` æíÍÓÈ áßá ÏæÑ `unowned` (ãÄåáæä íãáßæä ÇáÕáÇÍíÉ æáÇ íãáßæä Ãí ãäÊÌ ãä ÇáÏæÑ = íÔßøáæä ÇáÈÑßÉ ÇáÚÇãÉ). íÚÑÖå ÊÈæíÈ ÇáãäÇæÈÇÊ **ÈÇáÚÏÏ İŞØ** `setup_gap_ownership` (ÚÑÖ ÇáÃÓãÇÁ ßÇä íßÓÑ ÇÎÊÈÇÑÇÊ ÊÕİíÉ ÇáãäÇæÈÇÊ)¡ æíÚÑÖ ÊÈæíÈ ÇáÊÚííäÇÊ ÇáÃÓãÇÁ İí ÈäÑ áßá ÏæÑ. **ãØáæÈ ãÑÇÌÚÉ ÇáãÇáß áåĞÇ ÇáÊİÓíÑ.**
7. **ÇáÊÑÌãÇÊ:** ãİÊÇÍ `setup_gap_ownership` × ar/en/es/fr (ãÊÒÇãä) — æÇáÈÇŞí íÚíÏ ÇÓÊÎÏÇã `queue_tab_confirmation`/`queue_tab_tracking`/`all_roles` ÇáãæÌæÏÉ.

**ÇáÇÎÊÈÇÑÇÊ (ÇáÌÒÁ 1.4):** `OwnershipRoutingTest` (412) — ãÚÇãá `roleScope` İí `ownsProduct()` (Õİæİ ÇáÊÊÈÚ ÊõäÔÃ ÈÜ `role_scope='track'`)¡ æÅÚÇÏÉ ßÊÇÈÉ ÇÎÊÈÇÑíä ÈŞæÇÚÏ R2 («ãÇáß ãŞíøÏ íÈŞì ÈáÇ ÅÓäÇÏ: ÇáÈÑßÉ ÇáÕÇÑãÉ áÇ ÊÑÌÚ ááÚÇã ÃÈÏğÇ» æ«ÊÊÈÚ íÈŞì ÈáÇ ÅÓäÇÏ Ííä ÇáãÇáß ÇáæÍíÏ ÎÇÑÌ ÇáãäÇæÈÉ»). `OrderAssignmentServiceTest` (414) — ÅÚÇÏÉ ßÊÇÈÉ «ÚÇã íõÓÊÎÏã Ííä áÇ ãÊÎÕÕ Úáì ãäÇæÈÉ» Åáì «ÇáØáÈ íäÊÙÑ Ííä ÇáãÇáß ÇáæÍíÏ ÎÇÑÌ ÇáãäÇæÈÉ (áÇ ÊÑÇÌÚ ááÚÇã)». **áÇ ÇÎÊÈÇÑÇÊ ãõÒÇáÉ.** `OrderSettingsShiftRoleTest` + `OrderSettingsAssignmentsSearchTest` + `OrderDistributionQueueTest` ÎÖÑÇÁ (19).

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** `pint --dirty` äÙíİ (ÃÕáÍ 13 ãáİğÇ¡ äãØÇä)º ÇáÃÓØÑ ÊÍÊ ÇáÃÓŞİ. `tests/Feature/Order` = **64/64**º `ProductScopeDuplicateReviewTest` + `MembershipProductScopeBackfillTest` + `StoreProductScopeServiceTest` + `DemoStoreSeederTest` = **18/18**º `NoMojibakeTest` 3/3 (ÇáÊÑÌãÉ ÇáÚÑÈíÉ ÓáíãÉ)º **ÇáãÌãæÚÉ ÇáßÇãáÉ = 1375 äÇÌÍğÇ / 0 İÇÔá (1,171,218 ÊÃßíÏğÇ¡ 520s)**. áã íõÚÏóøá Ãí ÇÎÊÈÇÑ ŞÏíã ãäØŞíğÇ ÎÇÑÌ ÊİÓíÑ R2 ÃÚáÇå.

**ÇáãÊÈŞí (ÈÇŞí Phase 35.3):** ÇáÌÒÁ 2 (`statuses.distribution_stage` + ÇáÍöãá + handover)¡ ÇáÌÒÁ 3 (stranded/visibility/perf İí ÇáØÇÈæÑ)¡ ÇáÌÒÁ 4 (ÃåáíÉ handover + uniqueness + perf)¡ ÇáÌÒÁ 5 (docs + Todoles). ÇáÍÒãÉ ÇáÍÇáíÉ **ÛíÑ ãõæÏóÚÉ (uncommitted)** ÈÇäÊÙÇÑ ØáÈ ÇáãÇáß.

---

## Phase 35.3 (ÇáÌÒÁ 2) — ÊÚÑøİ ÇáÍöãá æÇáÊÓáíã ÚÈÑ `distribution_stage` ÈÏáğÇ ãä ŞæÇÆã ÇáÅÚÏÇÏ ? (2026-10-09)

**ÇáåÏİ:** İÕá ÊÕäíİ ÍÇáÉ ÇáØáÈíÉ ÇáÎÇÕ ÈãÍÑß ÇáÊæÒíÚ Úä `statuses.stage` (38-C) æÚä `config/order-distribution.php`: ÚãæÏ `statuses.distribution_stage` (confirmation/fulfillment/closed) íÕíÑ **ÇáãÕÏÑ ÇáæÍíÏ** ááÍöãá (ÇáØáÈÇÊ ÇáãİÊæÍÉ) æáÃåáíÉ ÇáÊÓáíã ÚäÏ ÇäÊåÇÁ ÇáãäÇæÈÉ.

**Êã ÊäİíĞå:**
1. **`OrderDistributionStage` (123¡ ÌÏíÏ):** ËæÇÈÊ `CONFIRMATION`/`FULFILLMENT`/`CLOSED` + `confirmationKeys()`/`fulfillmentKeys()`/`closedKeys()` (äİÓ ÊÕäíİ ÇáÅÚÏÇÏ ÇáãäŞæá) + `all()` + `forKey()` (ãİÊÇÍ ÛíÑ ãÚÑæİ/ãÎÕøÕ ? `confirmation`). ÏÇáÉ äŞíÉ ÈáÇ memo — áÇ ÊÊÓÑÈ Èíä ÇáãÊÇÌÑ/ÇáØáÈÇÊ.
2. **ÇáåÌÑÉ `2026_10_05_000003_add_distribution_stage_to_statuses_table` (48):** ÚãæÏ äÕí nullable ÈÚÏ `movement_type`¡ æbackfill áÕİæİ `type='order'` ÚÈÑ `forKey`º Õİæİ ÛíÑ ÇáØáÈÇÊ ÊÈŞì NULL. ÇáÊÇÑíÎ ŞÈá `2026_10_06_000003` (ÍÌÒ `--step 3`).
3. **`SystemStatusesSeeder`:** íÖÈØ `distribution_stage` áÍÇáÇÊ ÇáØáÈÇÊ ÚÈÑ `forKey` æNULL áÛíÑåÇ (Åáì ÌÇäÈ `stage` ÇáŞÇÆã) — ÅÚÇÏÉ ÇáÈĞÑ ÊõÈŞöí alignment.
4. **`Status`:** `distribution_stage` Öãä `$fillable`.
5. **ÇáÍöãá = ØáÈÇÊ ÈÅÓäÇÏ æÍÇáÉ `distribution_stage='confirmation'` (Ãæ NULL):** `OrderAssignmentService::selectBest` (join statuses) æ`AssignmentCandidateResolver::openCountsByMember` (join statuses) — ÈÏáğÇ ãä ŞÇÆãÉ `terminalStatusKeys()` ÇáËÇÈÊÉ. **ÇáÍöãá íÎÕ ÇáÊÊÈÚ ÈáÇ ÊÛííÑ** (`OrderTrackingStatus::open()`).
6. **ÇáÊÓáíã (`HandlesShiftHandover`):** `whereHas('status', stage=confirmation OR NULL)` ÈÏá ŞÇÆãÉ `confirmation_statuses` ãä ÇáÅÚÏÇÏ (ÊÊÈÚ ÇáÊÓáíã ÈáÇ ÊÛííÑ: `open()`).
7. **ÍĞİ `config/order-distribution.php`** ÈÇáßÇãá (ÇáãÕÏÑ ÕÇÑ ÇáÚãæÏ + `OrderDistributionStage`). áÇ ãÑÌÚ ãÊÈŞòø (`grep` äÙíİ).
8. **ÅÒÇáÉ `terminalStatusKeys()`** ãä `ResolvesCapacityBalancedCandidates` (242¡ ÕÇÑ ÛíÑ ãÓÊÎÏã) æãä `AssignmentCandidateResolver` (183) — áÇ ÊßÑÇÑ áŞæÇÆã ÍÇáÇÊ ËÇäíÉ.

**ÇáÇÎÊÈÇÑÇÊ:** ÅÚÇÏÉ ßÊÇÈÉ ÇÎÊÈÇÑ ÇáÊÕäíİ İí `EngineHardeningTest` (460) Åáì «ßá ÍÇáÉ ØáÈ ãÒÑæÚÉ ÊÍãá `distribution_stage`» — áÇ NULL¡ æÇáãÌãæÚÉ ÊØÇÈŞ `OrderDistributionStage::all()`¡ æİÍÕ `pending?confirmation` æ`confirmed?fulfillment` æ`paid?closed` æ`forKey('store_custom_hold')?confirmation`. ÈÇŞí ÇÎÊÈÇÑÇÊ Order ÇáÜ64 ÎÖÑÇÁ ÈáÇ ÊÚÏíá ãäØŞí (ÈãÇ İíåÇ ÇáÊÓáíã: fulfillment/closed áÇ ÊõãóÓ¡ æÇáÃÍãÇá ÊÎÊÈÑåÇ ÇáÍÇáÇÊ ÇáŞÇÆãÉ).

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** `pint --dirty` äÙíİ (ÃÕáÍ 9 ãáİÇÊ¡ äãØ `ordered_imports`)º ÇáÃÓØÑ: OrderDistributionStage 123¡ migration 48¡ AssignmentCandidateResolver 183¡ ResolvesCapacityBalancedCandidates 242 (< 250)¡ HandlesShiftHandover 87¡ OrderAssignmentService 201 (< 250). **ãÌãæÚÉ ãÓÊåÏİÉ 55/55** (Finance schema + Seeder + Queue + Settings + StatusLabelPrecedence + OrderStatusCapture + DashboardFilter)º **ÇáãÌãæÚÉ ÇáßÇãáÉ = 1375 äÇÌÍğÇ / 0 İÇÔá (1,172,113 ÊÃßíÏğÇ¡ 492s)**.

**ÇáãÊÈŞí:** ÇáÃÌÒÇÁ 3 (stranded/visibility/perf İí ÇáØÇÈæÑ) æ4 (ÃåáíÉ handover + uniqueness + perf) æ5 (docs + Todoles).

---

## 35.3-3: stranded visibility İí ÇáØÇÈæÑ + ÊÕİíÉ stranded_at

**ÇáåÏİ:** ÇáØÇÈæÑ íõÙåÑ ßá Õİ íÍÊÇÌ ÇäÊÈÇåğÇ: ÛíÑ ãõÓäóÏ Ãæ ÚÇáŞ (stranded) Ãæ İæŞ ÇáÓÚÉ — ÈÊÑÊíÈ «ÛíÑ ÇáãõÓäóÏ (ÇáÃŞÏã) Ëã ÇáÚÇáŞ (ÃŞÏã stranded_at) Ëã İæŞ ÇáÓÚÉ». ÇáÊÕİíÍ ÇáßÇãá ááßÔÉ ÇáÚÇáŞÉ ÚäÏ äåÇíÉ ÏæÑÉ ÇáÍíÇÉ.

**ÇáÃÌÒÇÁ:**
1. **ãÕÇÏÑ ÇáØÇÈæÑ (`DistributionQueueConcern` 195):** ÅÖÇİÉ `OR stranded_at IS NOT NULL` İí ÇáÊÈæíÈíä (confirmation ÈÊÑÊíÈ `whereNotIn terminal` ÈÏæä ÊÛííÑ¡ æÇáÜ tracking ÈÊÑÊíÈ `open()`)¡ æÊÑÊíÈ ËáÇËí:
   `CASE WHEN assigned IS NULL THEN 0 WHEN stranded_at IS NOT NULL THEN 1 ELSE 2 END ASC` Ëã `CASE WHEN assigned IS NULL THEN created_at ELSE COALESCE(stranded_at, created_at) END ASC`. ÅÖÇİÉ ÍŞá `stranded` ááÕİ.
2. **ÔÇÑÉ «Stranded»** (ÚÇáŞ) İí `partials/queue-table.blade.php` ÈÌæÇÑ ÔÇÑÉ İæŞ ÇáÓÚÉ (ÃíŞæäÉ clock)º ãİÊÇÍ `queue_stranded` × ar/en/fr/es. ÅÚÇÏÉ ÇáÅÓäÇÏ ÊÚãá áåĞå ÇáÕİæİ (äİÓ modal).
3. **ÊÕİíÉ stranded_at:**
   - İí `reassign()` ÈÇáÎÏãÊíä (OrderAssignmentService 201 æOrderTrackingAssignmentService 199): `stranded_at => null` (íõäÙİ ÍÊì ÎÇÑÌ ÇáØÇÈæÑ).
   - İí ãÓÍ `HandlesShiftHandover` (87)/`HandlesTrackingHandover` (75): ÚäÏ ÚæÏÉ ÇáãõÓäóÏ ÇáãõÓäóÏ ááÃåáíÉ íõãÍì ÇáÚáãº æÅÒÇáÉ ÇáÃÚáÇã ÇáŞÏíãÉ ÎÇÑÌ ÇáäØÇŞ ßÜ **UPDATE ãÌãóøÚ** æÇÍÏ ŞÈá ÇáÍáŞÉ (confirmation: statuses `distribution_stage != confirmation` æáíÓÊ NULLº tracking: statuses ÎÇÑÌ `open()`).

**ÇáÇÎÊÈÇÑÇÊ:** `OrderDistributionQueueTest` (424?13 ÇÎÊÈÇÑğÇ): ÊóÑÊíÈ confirmation — ÛíÑ ãõÓäóÏ ÃŞÏã¡ Ëã ÚÇáŞ ÃŞÏã Ëã İæŞ ÇáÓÚÉ ãÚ ÔÇÑÉ «Stranded»º ÇáÊóÈæíÈ tracking íÖãø ÇáÚÇáŞ ÈÚÏ ÛíÑ ÇáãõÓäóÏ ãÚ ÇáÔÇÑÉº ÅÚÇÏÉ ÅÓäÇÏ ÚäÕÑ ÚÇáŞ ÊÕİøí stranded_at æÊÒíáå İæÑíğÇ. `EngineHardeningTest` (460?13): ãÓÍ ÇáÎíØ ??íãÓÍ stranded_at ÚäÏ ÚæÏÉ ÇáãõÓäóÏ ááÃåáíÉ (ÈáÇ ÇÓÊÈÏÇá)¡ æíãÓÍ ÇáÃÚáÇã ÇáŞÏíãÉ ÇáãÌãóøÚÉ ÚäÏ ÎÑæÌ ÇáØáÈ ãä ãÑÍáÉ ÇáÊÃßíÏ (ÔÍä) æÎÑæÌ ÇáÊÊÈÚ ãä ÇáÍÇáÇÊ ÇáãİÊæÍÉ (delivered) Ïæä áãÓ ÇáÅÓäÇÏ.

**ÇáäØÇŞÇÊ:** áã ÊõãÓø ãÑÍáÉ statuses ÇáÌõÏóæáíÉ ááÊÊÈÚ (ãÇ ÒÇáÊ ÌãíÚ ÃÚãÏÉ `form_*` ãÏİæäÉ¡ İŞØ tracking_status ãËÈóøÊ İí dqTracking)¡ æãÕİæİÉ ÇáİÑæŞ İí ÇáÊóÈæíÈÇÊ ßÇãáÉ.

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** `pint --dirty` äÙíİ (11 ãáİğÇ)º **ãÌãæÚÉ ãÓÊåÏİÉ 88/88** (Order ßáåÇ + Queue + OrderSettingsShiftRole)º ÇáãÌãæÚÉ ÇáİÑÏíÉ ááØÇÈæÑ 13/13. ÇáÓØæÑ: DistributionQueueConcern 203¡ HandlesShiftHandover 103¡ HandlesTrackingHandover 90¡ OrderAssignmentService 202¡ OrderTrackingAssignmentService 200.

**ÇáãÊÈŞí:** ÇáÌÒÁ 4 (ÃåáíÉ handover: membership İÚøÇá + ÕáÇÍíÇÊ ããÊÏÉ + perf + uniqueness `ShouldBeUniqueUntilProcessing` áßá ãÊÌÑ) Ëã ÇáÌÒÁ 5 (docs + Todoles).

---

## 35.3-4: ÃåáíÉ ÇáÊÓáíã ÇáãæÓøÚÉ + ÇáÃÏÇÁ + ÊİÑÏ ÇáæÙíİÉ

**ÇáåÏİ:** ÊÚÒíÒ ÔÑØ «ÇáÅÈŞÇÁ» İí ãÓÍ ÇáãäÇæÈÉ áíØÇÈŞ ÃåáíÉ ÇáãÑÔøÍíä (ÇáÚÖæíÉ İÚøÇáÉ + ÊÍãá ÕáÇÍíÉ ÇáÏæÑ + ãäÇæÈÉ äÔØÉ), æÊÌãíÚ İÍÕ ÇáÃåáíÉ İí ÇÓÊÚáÇãíä ÈÏáğÇ ãä N+1¡ æÌÚá `ShiftHandoverJob` İÑíÏğÇ áßá ãÊÌÑ ÍÊì ÇßÊãÇá ÇáãÚÇáÌÉ.

**ÇáÃÌÒÇÁ:**
1. **ÃåáíÉ ÇáÅÈŞÇÁ (`eligibleKeepMap` İí `HandlesShiftHandover` 103?161 æ`HandlesTrackingHandover` 90?147):** ÎÑíØÉ eligibility ãİÊÇÍíÉ ÈÇáÜ membership id = `is_active && can(permission) && on_shift`. ÊõŞÑÃ ÇáÚÖæíÇÊ ãÚ `permissions` æ`storeWithTimezone.settings` İí ÇÓÊÚáÇã æÇÍÏ¡ Ëã `ShiftAvailabilityResolver::resolve` íŞÏøã snapshot on-shift æÇÍÏğÇ (ÇÓÊÚáÇã æÇÍÏ) — ÈÏá `isOnActiveShift()` ÈÑÇÈØÊíä áßá Õİ. ÇáãÓÍ ÇáÂä íÓÊÈÏá ÇáãõÓäÏ ÛíÑ ÇáİÚøÇá Ãæ ãä ÓŞØÊ ÕáÇÍíÊå Ãæ ÎÇÑÌ ÇáãäÇæÈÉ (ßÇä íÍÊİÙ ÈÇáÅÓäÇÏ ãÇ ÏÇãÊ ãäÇæÈÉ ÊÛØíå). confirm: `ORDER_CONFIRM`º track: `CRM_ORDER_TRACKING`.
2. **ÊİÑÏ ÇáæÙíİÉ (`ShiftHandoverJob` 31?45):** `ShouldBeUniqueUntilProcessing` + `uniqueId() = "shift-handover:{store_id}"` + `uniqueFor = 600`. ÇäÏİÇÚÇÊ ÇáÊÍÏíËÇÊ (ÍİÙ/ÍĞİ ãäÇæÈÉ¡ ÊÚÏíá/ÍĞİ ÚÖæ) ÊäÖã İí ãÓÍ æÇÍÏ áßá ãÊÌÑ — ãÚ ÈŞÇÁ jobs ÇáãÊÇÌÑ ÇáãÎÊáİÉ ãÓÊŞáÉ.
3. **Perf test:** ãÓÍ ÇáÃåáíÉ ËÇÈÊ ÇáÇÓÊÚáÇãÇÊ ãÚ äãæ ÇáÕİæİ (ÇáÚáÇŞíÇÊ ãÌãøÚÉ).

**ÇáÇÎÊÈÇÑÇÊ:** `EngineHardeningTest` (13?21): ÇÓÊÈÏÇá ãõÓäóÏ Êã ÊÚØíáå ÑÛã æÌæÏ ãäÇæÈÉ äÔØÉº ÇÓÊÈÏÇá ãä ÓŞØÊ ÕáÇÍíÊå (syncPermissions ? ORDER_VIEW İŞØ)º ÇÓÊÈÏÇá tracker ãÚØóøáº ËÈÇÊ ÇÓÊÚáÇãÇÊ ÇáÃåáíÉ ãÚ äãæ ÇáÕİæİº `uniqueId` áßá ãÊÌÑ æİÑíÏíÉ `ShouldBeUniqueUntilProcessing`º ÚÏøÇÏ ÇáÜ Bus ÇáãäåÇÑ: ÊÍÏíË+ÍĞİ ÚÖæ ? 1 (ÈÏá 2) æsave/toggle/delete ãäÇæÈÉ ? 1 (ÈÏá 3)º ãÊÇÌÑ ãÎÊáİÉ ? æÙíİÊÇä. `isOnActiveShift` ÈŞí API ÚÇãğÇ İí ÇáãæÏíá (áã íõÓÊÎÏã İí ÇáãÓÍ).

**ÇáãÎÇØÑ ÇáäÍæíÉ:** `expect(new Foo()->bar())` ÇäßÓÑ İí PHP 8.3 ÏÇÎá Pest ? ÎõÒøäÊ ßÇÆäÇÊ ÇáæÙÇÆİ ÈãÊÛíÑÇÊ ÕÑíÍÉ.

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** `pint --dirty` äÙíİ (ÃÕáÍ ShiftHandoverJob: ordered_interfaces)º **ãÌãæÚÉ ãÓÊåÏİÉ 98/98** (Order ßáåÇ + Queue + OrderSettings + DemoSeeder)º **ÇáãÌãæÚÉ ÇáßÇãáÉ = 1387 äÇÌÍğÇ / 0 İÇÔá (1,173,977 ÊÃßíÏğÇ, 525s)**. ÇáÓØæÑ ÇáÃÎíÑÉ: HandlesShiftHandover 161¡ HandlesTrackingHandover 147¡ ShiftHandoverJob 45¡ OrderAssignmentService 202.

**ÇáãÊÈŞí:** ÇáÌÒÁ 5 (docs): ÅäÔÇÁ `docs/plans/order-distribution-rules.md` (ÇáãÕÏÑ ÇáæÍíÏ áŞæÇÚÏ ÇáÊæÒíÚ) + ÅßãÇá Todos.

---

## 35.3-5: ÊæËíŞ ŞæÇÚÏ ÇáÊæÒíÚ

**ÇáåÏİ:** `docs/plans/order-distribution-rules.md` — ÇáãÕÏÑ ÇáæÍíÏ ÇáãæËøŞ áŞæÇÚÏ ÇáÊæÒíÚ¡ íØÇÈŞ ÇáÊäİíĞ ÍÑİíğÇ ÈÚÏ ÇáÌÒÃíä 4 æ5.

**ÇáÃÌÒÇÁ:** ÇáäØÇŞÇÊ (`confirm`/`track` + ÕáÇÊåãÇ)º ÃåáíÉ ÇáãÑÔøÍ ãä 4 ÔÑæØ (äÔöØ + ÕáÇÍíÉ ÇáÏæÑ ÇáããäæÍÉ + ãäÇæÈÉ äÔØÉ ãä äİÓ ÇáäØÇŞ + ÕáÉ ÑÄíÉ ÇáãäÊÌÇÊ)º ÇáÊæÌíå R1–R3 (ÓáØÉ ÇáãáßíÉ — ÍíÇÒÉ ÇáãäÊÌ ãä ÇáÚÖæíÇÊ ÇáäÔØÉ ÇáÍÇãáÉ ááÕáÇÍíÉ — ÍÇÑÓ ËÇÈÊ ÈáÇ ãÓÇÑ ÈÏíá)º ÇáÊÑÊíÈ (coverage Ëã ÃŞá ÇáãİÊæÍ Ëã ÇáÃŞÏã ÅÓäÇÏğÇ ãÚ ÊÔæíÔ ÇáÑæÇÈØ ÇáßÇãáÉ)º ÇáÓÚÉ ÇáÒÇÆÏÉ ÇáÇÎÊíÇÑíÉº ÇáÊÒÇãä (Şİá per-store + per-scope) æÇáæÙíİÉ ÇáİÑíÏÉ `ShouldBeUniqueUntilProcessing` áßá ãÊÌÑº ÅÚÇÏÉ ÇáÅÓäÇÏ ÇáíÏæíº ÏæÑÉ ÍíÇÉ ÇáÊÓáíã/ÇáÊÚáíøŞ (pass ãÌãóøÚ ááÃÚáÇã ÇáŞÏíãÉ + keep/strand/replace ãÚ ßÊÇÈÉ stranded_at áãÑÉ æÇÍÏÉ æÅÈŞÇÁ ÇáÅÓäÇÏ ÚäÏ ÛíÇÈ ÇáÈÏíá)º ÇáØÇÈæÑ (3 Õİæİ + ÊÑÊíÈ ËáÇËí ÇáÃØæÇÑ + 50/ÕİÍÉ)º ÊÕäíİ `statuses.distribution_stage` (ŞæÇÆã confirmation/fulfillment/closed + ÇİÊÑÇÖ ÇáãÎÕÕ áÅãÑ confirmation)º ÛíÑ-ÃåÏÇİ ÕÑíÍÉº ãáİÇÊ ÇáãİÇÊíÍ.

**ÇáäÊÇÆÌ/ÇáÊÍŞŞ:** ãÑÇÌÚÉ íÏæíÉ ÊŞÇØÚÊ ãÚ ÇáÓØÑ ÇáÃÎíÑ áßá ãßæøä ÇáÊÒÇãä (ProductOwnershipRouter 141¡ OrderDistributionStage 123¡ HandlesShiftHandover 161¡ HandlesTrackingHandover 147¡ DistributionQueueConcern 203¡ ShiftHandoverJob 45¡ OrderAssignmentService 202)º áÇ ÊÛííÑ İí PHP — doc-only.

**ÇáãÊÈŞí:** áÇ ÔíÁ — ÇßÊãÇá PHASE 35.3 ÈÇáßÇãá (ıCommits: 9334e4e «35.3-1»¡ 8b26647 «35.3-2»¡ 46b41a8 «35.3-3»¡ d8e89fb «35.3-4»¡ æåĞÇ ÇáÊæËíŞ «35.3-5»).

---

## 35.4: İÕá «ÇáÅÓäÇÏ» Úä «ÇáÇÓÊÍŞÇŞ» — ÊæËíŞ ÇáŞæÇÚÏ (ÈÏæä ßæÏ¡ ÈÇäÊÙÇÑ ÇáãæÇİŞÉ Úáì P2)

**ÇáåÏİ:** ÅÕáÇÍ ÈáÇÛ ÇáÅÍÕÇÆíÇÊ — ÚÖæ ÊÃßíÏ-İŞØ íÑÓá ØáÈíÇÊå ááÊæÕíá¡ İÊÙåÑ ÑÕíÏåÇ áÏì ÇáãÊÊÈÚ (ãİÊÇÍ ÑÕíÏ ÇáÊæÕíá ßÇä = ãõÓäöÏ ÇáÊÊÈÚ) æáíÓ áÏíå. ÇáŞÑÇÑÇÊ ÇáãÚÊãÏÉ:

1. **ãİÊÇÍ ÇáÑÕíÏ ÇáæÍíÏ = `orders.confirmed_by_membership_id`** İí ÇáÊÈæíÈíä (ÇáÊÃßíÏ + ÇáÊæÕíá = «ãÕíÑ ãÄßøÏÇÊå»). ÇáÚãá (`assigned_to`) íÈŞì ÚãæÏğÇ ãÓÊŞáğÇ «ÇáãõÓäÏ» æáÇ íõÍÊÓÈ Èå ÑÕíÏ ÃÈÏğÇ.
2. **ÇáÃÚãÏÉ:** ÚÑÖ ÇáÊÃßíÏ íßÊÓÈ ÊæÕøáÊ/ÑõÌøÚÊº ÚÑÖ ÇáÊæÕíá = ÇáãõÓäÏ (Úãá) + delivered/returned/ŞíÏ ÇáÊæÕíá/revenue (ÑÕíÏ) + ÇáäÓÈ: `conversion = ÃßøÏÊ÷ÇáãõÓäÏ`¡ `delivery = ÊæÕøáÊ÷ÃõßöøÏÊ`¡ `return = ÑõÌøÚÊ÷(ÊæÕøáÊ+ÑõÌøÚÊ)`. ÍĞİ `latestMembership` İí Delivery (ÇÓÊÚáÇã ãÊÑÇÈØ) æÇÓÊÈÏÇáå ÈÊÌãíÚ ãÓÊŞá ááÚãá.
3. **«ÈáÇ ÑÕíÏ» (`confirmed_by IS NULL`):** Õİ + ÎíÇÑ İí İáÊÑ ÇáÚÖæ (sentinel)º ÊİÑŞÉ ÊÓãíÉ ÕÑíÍÉ Úä «ÛíÑ ãõÓäóÏÉ» (Úãá¡ ãİåæã ŞÇÆãÉ ÇáÊæÒíÚ). ÇáäØÇŞ/ÇáÜKPI íÊÈÚ äİÓ ÇáãİÊÇÍ.
4. **ÇáÚÖæ ÇáãÒÏæÌ (ÊÍãá `ORDER_CONFIRM` + `CRM_ORDER_TRACKING`):** íÙåÑ İí ÇáãäÇæÈÊíä æÇáÊÈæíÈíä (ãÓãæÍ)¡ ÈÔÑØ «áÇ ÇÒÏæÇÌ ÏÇÎá ÊÈæíÈ æáÇ ÌãÚ ÚÈÑ ÇáÊÈæíÈÇÊ».
5. **ÕİÍÉ ÊÊÈÚ ÇáØáÈíÇÊ:** ãõÓäöÏ ÇáÊÊÈÚ ãä ÇáãÍÑß İŞØ (ÍÇãáæ `CRM_ORDER_TRACKING`)º ÇáãÄßøÏ ? ÇáãÓäÏ ÅáÇ ÈÍãá ÇáÕáÇÍíÊíä ãÚğÇ — ãÍŞŞ ÃÕáğÇ + ÇÎÊÈÇÑ íõËÈÊåº «ÃßøÏå» ÚãæÏ ãÓÊŞá.
6. **áÇ `delivered_by`:** ÑÕíÏ ÇáÊæÕíá áãÄßöøÏ ÇáØáÈíÉ ÏÇÆãğÇ.

**ÇáãÕØáÍÇÊ ÇáãæÍÏÉ (5 ãİÇÊíÍ):** `orders.created_by` = «ÓÌøáå» (íÈŞì) · `orders.confirmed_by` = «ÃßøÏå/ÃÑÓáåÇ» (ÇáÑÕíÏ) · `orders.assigned_to` = «ãõÓäÏ Åáì» (Úãá) · `order_trackings.assigned_to` = «ãõÓäÏ áÊÊÈÚ» · `order_trackings.created_by` ? **`tracked_by`** (ãÄÌá).

**ãÄÌóøá ãæËóøŞ (ŞÑÇÑ «áÇÍŞğÇ Åä áÒã») — áÇ íõäİóøĞ ÇáÂä:** ÊÑÓíÎ İÇÚá `CarrierOrderPostService::postToCarrier` (Çáíæã íßÊÈ `created_by=NULL`). ÃõäÌÒ İí 38-C.2: ÅÚÇÏÉ ÊÓãíÉ ÚãæÏ İÇÚá ÇáÊÊÈÚ İí `order_trackings` Åáì `tracked_by_membership_id` (åÌÑÉ `2026_10_06_000006` ÈÚÏ `2026_10_06_000004` + ÊÍÏíË `FinancialCaptureSchemaTest`/ÎØæÉ `--step`¡ `FinanceCaptureHealth`¡ `OrderTrackingService`¡ `OrderStatusCaptureTest`¡ ÇáæËÇÆŞ). íõØæóøŞ ÈãæÚÏ 38-F.

**ãÑÇÍá 35.4:** P1 ÇáÊæËíŞ ? (åĞÇ ÇáŞÓã + §11 İí `order-distribution-rules.md`) — P2 ÎáİíÉ (ÇáãİÇÊíÍ/ÇáÃÚãÏÉ/ÇáÊÕİíÉ/ÅÒÇáÉ latestMembership) — P3 æÇÌåÉ (ÃÚãÏÉ ÇáÌÏæáíä + İáÊÑ «ÈáÇ ÑÕíÏ» + i18n×4 + 375/768/1440) — P4 ÇÎÊÈÇÑÇÊ (ÓíäÇÑíæ ãÄßöøÏ?ãõÓäÏ¡ İáÊÑ ÈáÇ-ÑÕíÏ¡ + `DashboardQueryBudgetTest`¡ pint¡ ÇáãÌãæÚÉ ÇáßÇãáÉ) — P5 ãÄÌóøá (ÃÚáÇå).

**ÇáÍÇáÉ:** P2/P3/P4 ãäİóøĞÉ ? (ÊÈæíÈ ÇáÅäÌÇÒ ÃÏäÇå)¡ æ**ÊæÇİŞ MySQL** ãõÕáóÍ æãæËóøŞ İí §35.4-5 (åÌÑÉ 1553 + only_full_group_by 1055)¡ æ**ÊæÍíÏ ÏáÇáÉ «ÇáãõÓäÏ» Úáì ÕİÍÉ ÇáÊÊÈøÚ + ÊäŞíÉ ÓíÏÑ ÇáÏíãæ** İí §35.4-6¡ æ**ÊŞííÏ ÊÈæíÈÇÊ ÇááæÍÉ ÈÜ«İæÌ» ÇáÚÖæíÉ (§11.4)** İí §35.4-7. P5 ãÄÌóøá ßãÇ åæ ãæËóøŞ ÃÚáÇå — áã íõäİóøĞ Ãí ÓØÑ ãäå.

---

### 35.4-2 .. 35.4-4: İÕá «ÇáÑÕíÏ» Úä «ÇáÚãá» — ÇáÅäÌÇÒ æÇáÊÍŞŞ

**ÇáÎáİíÉ ÇáãäÌÒÉ (P2):** `DashboardFilter::UNATTRIBUTED = '__unattributed__'` + `memberUnattributedOnly` (ÈäİÓ ÇáÇÊİÇŞ ãÚ `memberLocked` İí ctor `toArray`/`buildPrevious`). `DashboardFilterFactory::resolveMember` ? ÎãÇÓíÉ `[ÇáãİÊÇÍ¡ ÇáÈõÚÏ¡ ÇáäØÇŞ¡ ÇáŞİá¡ ÈáÇ-ÑÕíÏ-İŞØ]`º **ÔÑØ ÇáÍÇÑÓ áÜ «ÈáÇ ÑÕíÏ» ãŞÕæÏ = `$hasTeamView && $memberId === UNATTRIBUTED`** (İŞØ ÕÇÍÈ ÇáÑÄíÉ ÇáßÇãáÉ íõßÑóøã ÈåÇº ÇáÈÇŞí íÓŞØ Åáì İÑÚ ÏæÑå)¡ æÈŞíÉ ÇáİÑæÚ ßãÇ åí (ßá-ÇáİÑíŞ / ÑÄíÉ-ÇáİÑíŞ-ÇáÎÇÕÉ / ãŞİá). `DashboardOrderScope::applyMember` ? `confirmed_by_membership_id` İí ÇáÈõÚÏíä ßáíåãÇ (ãÚÇãáÉ sentinel ÚÈÑ `memberScopeIds` ãÎÑÌÇÊ İíå + fail-closed `whereIn([])`)¡ æ`applyPendingMember` ? `assigned_to_membership_id` (ÇáÍÇÑÓ æÍíÏ: ÈáÇ ÔÑØ). `DashboardTeamPerformanceQuery::run()` ? ãÕİæİÉ `['credit','work']`: credit ÊõÌãöøÚ ÈÜ `confirmed_by` (confirmed ÈÏáÇáÉ ãÌãæÚÉ `CONFIRMED`¡ áÇ `COUNT(*)`)º work ÊõÌãöøÚ ÈÜ `assigned_to` (ÊÃßíÏ) Ãæ `latestMembership` ÇáÊÑÇÈØí (ÊæÕíá) — ãÚ **ÅÕáÇÍ ÇÑÊÈÇØÇÊ `groupByRaw($groupKey, [$storeId])`** (ÓÇÈŞğÇ ßÇä `?` ÇáÊÌãíÚ íáÊŞØ ÑÈØğÇ ÎÇØÆğÇ İíÍÔÑ ßá ÇáØáÈíÇÊ İí ÚÖæ æÇÍÏ) æãÓÇÚÏ `aggregate()`/`cast(?? 0)`. `DashboardTeamPerformance` (presenter): merge ÈãİÊÇÍ `$id ?? ''`¡ İÑÚ sentinel íÚíÏ ÊÕİíÑ Úãá ÇáÕİ Ëã íÌãøÚ ßá Õİæİ ÇáÚãá¡ ÇáäÓÈ §11 (`conversion = ÃßøÏÊ÷ÇáãõÓäÏ`¡ `delivery = ÊæÕøáÊ÷ÃõßöøÏÊ`¡ `return = ÑõÌøÚÊ÷(ÊæÕøáÊ+ÑõÌøÚÊ)`¡ ÓŞİ 100)¡ Õİ `type 'unattributed'` íÊáæøä ÊÓãíÉ `team_unattributed`¡ `showsUnattributed/showsTotal`. `DashboardFilterOptions::memberSelectOptions` íáÍŞ sentinel ÏÇÆãğÇ (+ ÇÓÊíÑÇÏ DTO).

**ÇáæÇÌåÉ (P3):** ÌÏæá ÇáÊÃßíÏ ÃÚãÏÉ = ÇáãõÓäÏ/ãÚáóøŞ/ãáÛì/ÃÎÑì/ÃßöøÏåÇ/ÊæÕøáÊ/ÑõÌøÚÊ/conversionº ÌÏæá ÇáÊæÕíá = ÇáãõÓäÏ/delivered/returned/ŞíÏ ÇáÊæÕíá/revenue/äÓÈÉ delivery/äÓÈÉ returnº ÔÇÑÉ «ÈáÇ ÑÕíÏ» İí ÔÑíØ ÇáÊÕİíÉ (`$unattributedSelected` ÚÈÑ FQCN + `$hasActiveFilters` + badge)º i18n×4: `team_unattributed` (ar «ÈáÇ ÑÕíÏ» / fr «Non attribué» / en «Unattributed» / es «Sin atribuir») + ÅÚÇÏÉ ÕíÇÛÉ `team_performance_delivery` (ar/fr/es¡ en ÈáÇ ÊÛííÑ).

**ÇáÇÎÊÈÇÑÇÊ (P4):**
- `tests/Feature/Merchant/DashboardTeamPerformanceTest.php` ÅÚÇÏÉ ßÊÇÈÉ: ÇáÓíäÇÑíæ íßÊÈ `confirmed_by` (æİŞ ÅÛáÇŞ ÇáÊÓáíã ÃíÖğÇ) + ÓíäÇÑíæåÇä ÌÏíÏÇä (ãÄßöøÏ?ãõÓäöÏ ÇáÊÊÈÚ — Alpha ãÇá¡ Gamma Úãáº ÇáÍÇÑÓ íõÎÑÌ ØÇÆİÉ ÈáÇ-ÑÕíÏ æÍÏåÇ ÈÌãÚ ÚãáåÇ) + ÊæŞøÚÇÊ Exact áßá Õİ (ÇÚÊãÏÊ İí ÇáãÍÇÏËÉ).
- `DashboardFilterTest` ÅÚÇÏÉ ãİÊÇÍ ÇáÈõÚÏíä ááÜ`confirmed_by` (ÊæÕíá ÈáÇ trackingsº ÇÎÊÈÇÑ `topSellingProducts` íßÊÈ `confirmed_by`)º `DashboardFilterWiringTest` ÚÏø ÎíÇÑÇÊ STAFF=3 (`['', staffId, sentinel]`) ænull=2¡ `DashboardQueryBudgetTest` baseline=51/ÓŞİ 52 ãÚ ÊÚáíŞ ÔÑÍ.
- `DashboardAnalyticsTimezoneTest` + `DashboardChartsPerViewTest` ÅÚÇÏÉ ÊæÇİŞ ãÚ ãİÊÇÍ ÊÃßíÏ æÇÍÏ.
- **ÇáäÊÇÆÌ:** æÍÏÉ `DashboardTeamPerformanceViewModelTest` 15/15 (98 ÊÃßíÏğÇ)º ãíÒÇÊ 35.4 ãÌÊãÚÉ 62/62 (413 ÊÃßíÏğÇ)º pint äÙíİ ÈÚÏ ÇáÊåĞíÈ.
- **ÇáãÌãæÚÉ ÇáßÇãáÉ:** 1405 äÇÌÍğÇ¡ 11 İÔáğÇ ßáåÇ **ÓÇÈŞÉ æáíÓÊ ãä 35.4** æãõÚÇÏ ÅäÊÇÌåÇ Úáì HEAD ÛíÑ ãÚÏóøá (İÍÕ stash-baseline: 11 İÔáğÇ/17 äÇÌÍğÇ Úáì Çáãáİíä): `DeliverySettingsTest` ×10 + `OrderFinancialSummaryTest` ×1 ÈÓÈÈ ÈÍË `Carrier::where('code','ecotrack')` — æÇáÓíÏÑ ÇáÇáÊÒÇãí (CarrierCatalogSeeder) áÇ íõäÔÆ ÑãÒğÇ ÈåĞÇ ÇáÇÓã (ovred.ecotrack İŞØ) = ÇäİÕÇá ßÊÇáæÌ ÇáÓíÏÑ Úä ÊæŞøÚ ÇáÇÎÊÈÇÑÇÊ (äØÇŞ ÇáÔÍä¡ ÎÇÑÌ 35.4).
- **ãõáÇÍÙÉ ÊÔÛíá:** `artisan test` íİÊÍ ÚãáíÉ PHP ÈáÇ `-d`¡ İÍõÏöøÏ `-d memory_limit=1G` ãÚ `vendor\bin\pest` ãÈÇÔÑÉ (ÈÚÏ 512M ßÇä ÇáÌÒÁ ÇáÊÌÇÑí íäÒİ ĞÇßÑÉ İí ÚÑÖ view ãõÕÑóøİ).

**ÇáãÊÈŞí ãä 35.4:** áÇ ÔíÁ Óæì P5 ÇáãÄÌóøá ÃÚáÇå (ÅÚÇÏÉ ÊÓãíÉ `tracked_by` + ÊÑÓíÎ İÇÚá ÇáÅÑÓÇá¡ ÈæÚÏò ÈãóæÚÏ 38-F). íõÑİÚ ÇáÊäÈíå ááãÓÊÎÏã ÈÔÃä ÇáÇäİÕÇá ÇáÍÇáí áßæÏ `ecotrack` İí `DeliverySettingsTest`/`OrderFinancialSummaryTest` (äØÇŞ ÔÍä¡ ÎÇÑÌ åĞÇ ÇáÚãá) ŞÈá Ãí ÅÔÚÇÑ «ÃÎÖÑ ßÇãá».

---

### 35.4-5: ÊæÇİŞ MySQL — åÌÑÉ `role_scope` (ÎØÃ 1553) æÊÌãíÚ `only_full_group_by` (ÎØÃ 1055)

**ÇáÓíÇŞ:** ÇáÇÎÊÈÇÑÇÊ ÊÚãá Úáì SQLite (`DB_DATABASE=:memory:`) İÊõÎİí ŞíæÏ MySQL. ÊÔÛíá `php artisan migrate` Úáì ŞÇÚÏÉ ÇáÊØæíÑ `edzeery` (127.0.0.1:3306) İÔá İí ãæÖÚíä ãÓÊŞáøíäº ÃõÕáÍÇ æÃõËÈÊÇ Úáì MySQL ÇáÍŞíŞí äİÓå.

**1) ÇáåÌÑÉ `2026_10_05_000002_add_role_scope_to_confirmation_product_assignments`**
- **ÇáÎØÃ:** `SQLSTATE[HY000]: General error: 1553 Cannot drop index 'cpa_store_member_prod': needed in a foreign key constraint` Úáì `alter table confirmation_product_assignments drop index cpa_store_member_prod`.
- **ÇáÓÈÈ:** InnoDB ßÇä ŞÏ ÇÎÊÇÑ ÇáİåÑÓ ÇáİÑíÏ ÇáŞÏíã `cpa_store_member_prod` (íÓÇÑ ÕÏÑí `store_id`) áíÓäÏ ÇáãİÊÇÍ ÇáÎÇÑÌí `confirmation_product_assignments_store_id_foreign`¡ İíÑİÖ ÍĞİ İåÑÓ ãÇ ÒÇá ŞíÏñ íÚÊãÏ Úáíå. ÇáİÍÕ ÚÈÑ `information_schema` ÃßøÏ ÇáÍÇáÉ ÇáÌÒÆíÉ: `role_scope` ÃõÖíİ İÚáğÇ ŞÈá ÇáİÔá¡ æÇáãİÇÊíÍ ÇáÎÇÑÌíÉ ÇáËáÇËÉ ŞÇÆãÉ¡ æÇáİåÑÓ ÇáŞÏíã ÈÇŞò¡ æÇáåÌÑÉ ÚäÏÆĞò **Pending**.
- **ÇáÅÕáÇÍ:** ÅÓŞÇØ ÇáãİÇÊíÍ ÇáÎÇÑÌíÉ ÇáËáÇËÉ (`store_id`/`membership_id`/`product_id`) **ŞÈá** `dropUnique('cpa_store_member_prod')`¡ Ëã ÅäÔÇÁ `cpa_store_member_prod_scope` (store_id, membership_id, product_id, role_scope) æ`cpa_store_scope_product_idx`¡ Ëã **ÅÚÇÏÉ ÈäÇÁ ÇáãİÇÊíÍ ÇáÎÇÑÌíÉ**. æ`down()` ÊÚßÓ ÇáÊÑÊíÈ äİÓå: FK ? ÇáİåÇÑÓ ÇáÌÏíÏÉ ? ÇáİåÑÓ ÇáŞÏíã ? FK ? ÅÓŞÇØ ÇáÚãæÏ.
- **ŞÇÈáíÉ ÅÚÇÏÉ ÇáÊÔÛíá:** áÃä DDL İí MySQL ÈáÇ ãÚÇãáÇÊ İŞÏ ÈŞí ÇáÚãæÏ ÈÚÏ ÇáİÔá ÑÛã Ãä ÇáåÌÑÉ ãÇ ÒÇáÊ Pendingº İÍõÑÓ ÅÖÇİÉ `role_scope` ÈÜ`Schema::hasColumn` ßí ÊÚãá `php artisan migrate` ÈáÇ ÎØÃ «Duplicate column».
- **ÇáÊÍŞŞ:** `php artisan migrate` Úáì `edzeery` äİøĞ ÇáåÌÑÉ (139.19ms DONE) Ëã ÓÊ åÌÑÇÊ ãÚáøŞÉ ÊÇáíÉ ÈáÇ ÎØÃ (`2026_10_05_000003` … `2026_10_06_000005`).

**2) `DashboardTeamPerformanceQuery::aggregate()` — ãÓÇÑ ÇáÊæÕíá**
- **ÇáÎØÃ:** `SQLSTATE[42000] … 1055 Expression #1 of SELECT list is not in GROUP BY clause … 'edzeery.orders.id' … incompatible with sql_mode=only_full_group_by` ÚäÏ `app/Domains/Analytics/Support/DashboardTeamPerformanceQuery.php:109`.
- **ÇáÓÈÈ:** ÇáÇÓÊÚáÇã íÖÚ İÍÕğÇ ÊÑÇÈØíğÇ ãÓÊÚÇÑğÇ `(select … MAX(m.id) …) as membership_id` İí `SELECT`¡ æßÇäÊ `GROUP BY` ÊßÑÑ äÕø ÇáİÍÕ äİÓå. áßä MySQL íæŞİ ÊÍáíá ÇáÇÚÊãÇÏ ÇáæÙíİí ÚäÏ ÍÏø ÇáÇÓÊÚáÇã ÇáİÑÚí İáÇ íÚÊÈÑ ÊÚÈíÑ `SELECT` ãØÇÈŞğÇ áÊÚÈíÑ `GROUP BY` (æáæ ÊØÇÈŞÇ äÕğøÇ) ? 1055.
- **ÇáÅÕáÇÍ:** İí ÍÇáÉ `$rawGroup` İŞØ¡ ÇáÊÌãíÚ ÈÇáÇÓã ÇáãÓÊÚÇÑ `groupByRaw('membership_id')` ÈÏá ÊßÑÇÑ ÇáİÍÕ¡ İíŞÈá MySQL ÇÓã ÇáÚãæÏ ÇáãÓÊÚÇÑ İí `GROUP BY` (æSQLite ßĞáß)¡ æáã íÚÏ ááÊÌãíÚ ÑÈØ (`?`) ÎÇÕ Èå. ÃõÈŞí ÅáÕÇŞ `$filter->storeId` İí ãŞÏãÉ ÇáÜbindings áÃÌá İÍÕ `SELECT` æÍÏå. ÇáÍÇáÊÇä ÛíÑ ÇáÎÇãøÊíä (`assigned_to`/`confirmed_by`) ÊÈŞíÇä Úáì `groupBy($groupKey)`.
- **ÊÍŞŞ MySQL ÇáÍŞíŞí (ŞÑÇÁÉ İŞØ):** ÇÓÊõÏÚí `StoreDashboardAnalyticsService::teamPerformance()` ÈÇáÈõÚÏíä Úáì ÇÊÕÇá `edzeery` (sql_mode íÊÖãøä `ONLY_FULL_GROUP_BY`) İäİøĞ ÈáÇ ÎØÃ (ÓØÑ æÇÍÏ). SQL ÇáãáÊŞØÉ: `select (select order_trackings.assigned_to_membership_id … where m.store_id = ? and m.order_id = orders.id)) as membership_id, COUNT(*) as assigned, … from orders … group by membership_id`.
- **ÍÏæÏ ÇáÊÍŞŞ:** ŞÇÚÏÉ ÇáÊØæíÑ `edzeery` İÇÑÛÉ ÍÇáíğÇ (stores=0 / orders=0 / order_trackings=0)¡ İÊÍŞŞ MySQL íÎÕø **ŞÇÈáíÉ ÇáÊÎØíØ æÇáŞÈæá** ÊÍÊ `ONLY_FULL_GROUP_BY` (æåæ ãæÖÚ ÇäİÌÇÑ 1055 ÊÍÏíÏğÇ) æáÇ íÊÍŞŞ ãä äÊÇÆÌ ÈíÇäÇÊ İÚáíÉ.
- **ÊÍŞŞ SQLite (áÇ ÇäÍÏÇÑ):** ãÌãæÚÉ ÇáÃäÇáíÊßÓ 85/85 (558 ÊÃßíÏğÇ) — ÔÇãáÉ `DashboardTeamPerformanceViewModelTest` æÇáãíÒÇÊ ÇáÓÊø — æpint äÙíİ Úáì Çáãáİóøíä ÇáãÚÏóøáíä.

---

### 35.4-6: ÊæÍíÏ ÏáÇáÉ «ÇáãõÓäÏ» Úáì ÕİÍÉ ÊÊÈøÚ ÇáØáÈíÇÊ + ÊäŞíÉ ÓíÏÑ ÇáÏíãæ

**ÇáÓíÇŞ:** ãÑÇÌÚÉ Úáì ÇáÓíÏÑ/ÇáŞæÇÚÏ ßÔİÊ ÊäÇŞÖíä:
1. `orders.assigned_to_membership_id` ááØáÈíÇÊ ÇáãÑÓáÉ İí ÇáÓíÏÑ ßÇä íÔíÑ Åáì `demo.tracker` (íãáß `CRM_ORDER_TRACKING` İŞØ¡ áÇ `ORDER_CONFIRM`) — ãÎÇáİ áÜ§0/§1 (ãİÊÇÍ ÇáÅÓäÇÏ İí äØÇŞ ÇáÊÃßíÏ íÊØáøÈ `ORDER_CONFIRM`)¡ İíÊÓÑøÈ «ÚÖæ ÊÊÈøÚ» Åáì ÚãæÏ «ÇáÚãá» İí ÊÈæíÈ ÇáÊÃßíÏ ÈáæÍÉ ÇáİÑíŞ.
2. ÕİÍÉ ÊÊÈøÚ ÇáØáÈíÇÊ ßÇäÊ ÊŞÑÃ «Çáæßíá ÇáãÓäÏ» ãä `orders.assignedMembership` (ÅÓäÇÏ ÇáØáÈíÉ/ÇáÊÃßíÏ) áÇ ãä ãÓäóÏ ÇáÔÍäÉ `order_trackings.assigned_to_membership_id`º áĞÇ ÙåÑÊ ØáÈíÉ ãÑÓáÉ (21012) ÈÇÓã **ÇáãÄßöøÏ** ÑÛã Ãä ãÓäóÏ ÊÊÈøÚåÇ åæ dual — ÈÎáÇİ ØÇÈæÑ ÇáÊæÒíÚ İí ÊÈæíÈ ÇáÊÊÈøÚ ÇáĞí íŞÑÃ ãÓäóÏ ÇáÔÍäÉ (DistributionQueueConcern.php:170).

**ÇáŞÑÇÑ (ÈãæÇİŞÉ ÇáãÓÊÎÏã):** ÊÕÍíÍ ÇáÓíÏÑ + ÊæÍíÏ ÏáÇáÉ «ÇáãÓäÏ» Úáì ÕİÍÉ ÇáÊÊÈøÚ áÊŞÑÃ ãÓäóÏ ÇáÔÍäÉ ãËá ØÇÈæÑ ÇáÊÊÈøÚ. (ÈæøÇÈÉ ÇáÚÖæíÉ ÇáãŞíóøÏÉ İí ÊÈæíÈ ÇáÑÌá — ÅÕáÇÍ QA P1-6 — ÃõÈŞíÊ Úáì `orders.assigned_to_membership_id` ßãÇ åí: äØÇŞåÇ ÙåæÑñ/ÊÕİíÉ¡ áÇ ÚÑÖ¡ æÊÛííÑåÇ íãÓø ÕáÇÍíÇÊ — ÎÇÑÌ ÇáäØÇŞ ÇáãÊİŞ.)

**ÇáÊäİíĞ:**
- `database/seeders/DemoStoreSeeder.php` — `assign_to` ááØáÈíÇÊ ÇáãÑÓáÉ ÕÇÑ ÚÖæ ÊÃßíÏ: `21009/21010/21011/21016` = `demo.confirmer`¡ æ`21014` = `demo.dual` (ÈãØÇÈŞÉ İÇÚá ÇäÊŞÇá `confirmed`). áã íÚÏ Ãí `assign_to` íÔíÑ Åáì `demo.tracker`.
- `app/Livewire/Concerns/TrackingGridConcern.php` — ÊÍãíá `latestTracking.assignedTo.user` ÈÏá `assignedMembership.user` (ÇáÔÈßÉ + ÇáÓáÉ)º ŞíãÉ ÇáÚãæÏ `assigned_to` ãä `$order->latestTracking?->assignedTo?->user?->name`º ãÑÔöøÍ `assigned_to` ÕÇÑ `whereHas('latestTracking', fn ($q) => $q->where('assigned_to_membership_id', …))`.
- `app/Livewire/Concerns/TrackingDrawerConcern.php` — `assigned_to` İí ÊİÇÕíá ÇáÔÍäÉ ãä `$tracking?->assignedTo?->user?->name`.
- ÇÎÊÈÇÑÇÊ: ÊÍÏíË `TrackingGridBatchTest` (İáÊÑ/ÚãæÏ `assigned_to` Úáì ãÓäóÏ ÇáÔÍäÉ + ÍÑÓ Ãä ÅÓäÇÏ ÇáØáÈíÉ æÍÏå áÇ íßİí)¡ æÅÖÇİÉ ÍÑÓ ÓíÏÑ İí `DemoStoreSeederTest` («ÅÓäÇÏÇÊ ÇáØáÈíÇÊ ÊÔíÑ ÍÕÑğÇ áÃÚÖÇÁ Èåã `ORDER_CONFIRM`»).

**ÇáäÊÇÆÌ:** `TrackingGridBatchTest` (10) + `DemoStoreSeederTest` (5) + `TrackingSearchFilterTest` (34) + `DemoSeederPermissionParityTest` (4) + `TrackingPageGuardTest` (6) = 58/58º æãÌãæÚÉ ÇáÊÊÈøÚ/ÇáÊæÒíÚ/ÇáÓíÏÑ (ÇáÊİÇÕíá¡ ÇáÍĞİ¡ ÇáÅÈáÇÛ¡ ÇáÑßøÇÈ¡ ÇáÊæÒíÚ) 75/75º æáæÍÉ ÇáİÑíŞ/ÇáÃäÇáíÊßÓ 59/59. pint äÙíİ Úáì ÇáãáİøÇÊ ÇáãÚÏóøáÉ.

**ãáÇÍÙÉ UX (ÎÇÑÌ ÇáäØÇŞ):** ŞÇÆãÉ ÃÚÖÇÁ ãÑÔöøÍóí `assigned_to`/`confirmed_by` áÇ ÊÒÇá ãÔÊÑßÉ (`allMembers` = ßá ÇáÃÚÖÇÁ ÛíÑ ÇáãÇáß)º ÇÎÊíÇÑ ÚÖæ ÊÃßíÏ İŞØ İí `assigned_to` íÚØí ÕİÑ Õİæİ (ÛíÑ ÖÇÑø).

---

### 35.4-7: ÊŞííÏ ÊÈæíÈÇÊ áæÍÉ ÇáÅÍÕÇÁÇÊ ÈÜ«İæÌ» ÇáÚÖæíÉ (§11.4)

**ÇáÓíÇŞ (ÇáÊäÇŞÖ ÇáãõÈáóøÛ):** ÊÈæíÈ **ÇáÊÃßíÏ** ßÇä íÌãøÚ «ÇáÚãá» ÈãİÊÇÍ `orders.assigned_to_membership_id` ÈáÇ Ãí ŞíÏ Úáì ÏæÑ/ÕáÇÍíÉ ÇáÚÖæ¡ İíãßä Ãä íÙåÑ **ÚÖæ ÊÊÈøÚ İŞØ** (íÍãá `CRM_ORDER_TRACKING` Ïæä `ORDER_CONFIRM`) ÏÇÎá ÅÍÕÇÁÇÊ ÇáÊÃßíÏ — æåæ ÊäÇŞÖ ãÚ §0/§1 (ÅÓäÇÏ äØÇŞ ÇáÊÃßíÏ íÊØáøÈ `ORDER_CONFIRM`). æÈÇáÇÊÌÇå ÇáãÚÇßÓ¡ ÊÈæíÈ ÇáÊæÕíá íÚÑÖ ãä ÍÕá Úáì «ÇáÑÕíÏ»/«ÇáÚãá» ÈÍÓÈ İÇÚáí ÇáÚãáíÉ.

**ÇáŞÑÇÑ (ÈãæÇİŞÉ ÇáãÓÊÎÏã — ÇáÎíÇÑ 1):**
- ßá ÊÈæíÈ íŞÊÕÑ Úáì **İæÌå**: ÊÈæíÈ ÇáÊÃßíÏ = ÍÇãáæ `ORDER_CONFIRM` ? **ÇáÃÏæÇÑ ÇáÅÏÇÑíÉ**º ÊÈæíÈ ÇáÊæÕíá = `ORDER_CONFIRM` ? `CRM_ORDER_TRACKING` ? ÇáÃÏæÇÑ ÇáÅÏÇÑíÉ (íõÖãø `ORDER_CONFIRM` İí ÇáÊæÕíá áÃä ÇáÑÕíÏ = `confirmed_by`).
- **ÇÓÊËäÇÁ ÅÏÇÑí ÕÑíÍ:** ÃÏæÇÑ `owner`/`admin`/`manager` ÊÙåÑ İí ÇáİæÌíä ÏÇÆãğÇ ÈÕÑİ ÇáäÙÑ Úä ÇáÕáÇÍíÇÊ — ãåãø ÎÕæÕğÇ áÜ`manager` áÃä ŞÇáÈ ÏæÑå áÇ íÍãá `ORDER_CONFIRM`. ÇáÃÚÖÇÁ **ãÒÏæÌæ ÇáÏæÑ** ãÓãæÍæä İí ÇáİæÌíä.
- ÇáäØÇŞ íÈŞì Úáì `confirmed_by` (áÇ ÊÛííÑ Úáì ÏáÇáÉ ÇáİáÊÑÉ)º æÇáŞÇÆãÉ ÇáãäÓÏáÉ ãŞÕæÑÉ Úáì ÇáİæÌ İÊãäÚ ÇÎÊíÇÑ ÚÖæ ÎÇÑÌå ãä ÇáæÇÌåÉ.

**ÇáÊäİíĞ:**
- ÌÏíÏ `app/Domains/Analytics/Support/DashboardMemberCohort.php` — `constrain(Builder, ?string $dimension)`: ÔÑØ `WHERE (role IN (owner,admin,manager) OR EXISTS permissions.permission IN (…))` ßÜ**ÇÓÊÚáÇã İÑÚí æÇÍÏ** (ÕİÑ ÇÓÊÚáÇã ÅÖÇİí¡ áÇ N+1). `null` = ÈáÇ ŞíÏ (áãÓÊÏÚò ÛíÑ ÇááæÍÉ).
- `DashboardFilterOptions::members(?StoreMembership, ?string $dimension = null)` æ`memberSelectOptions(?StoreMembership, ?string $dimension = null)` — ÊØÈíŞ ÇáİæÌ ÚäÏ ÊãÑíÑ `$dimension`º ÊæŞíÚ ÇáãÚÇãá ÈŞíãÉ ÇİÊÑÇÖíÉ íÍİÙ ãÓÊÏÚí `DashboardFilterWiringTest`.
- `resources/views/livewire/merchant/dashboard.blade.php` — ÊãÑíÑ `$filter->memberDimension` áßáòø ãä `members()` æ`memberSelectOptions()`¡ İÊÊŞíøÏ **Õİæİ ÇáÌÏæá æŞÇÆãÉ ÇáÃÚÖÇÁ** ãÚğÇ ÈİæÌ ÇáÊÈæíÈ ÇáäÔØ.

**ÇáÍÏæÏ ÇáãÚÑæİÉ (ãŞÕæÏÉ):** ÇáÚÖæ ÇáĞí ÊõÌãóÚ ÃÚãÇáå İí ÊÈæíÈ ãÇ Ëã áÇ íßæä İí İæÌå íõÍĞóİ ãä ÇáÕİæİ ÈæÇÓØÉ presenter (ÈäíÊå ÊØÑÍ ßá Õİ áíÓ İí ÇáŞÇÆãÉ). `memberId` ŞÇÏã ãä ÚäæÇä URL ÎÇÑÌ ÇáİæÌ íÈŞì íõäØóøŞ Úáì `confirmed_by` (ÛíÑ ÖÇÑø: áÇ Úãá áå İíÙåÑ ÌÏæá İÇÑÛ)¡ áÃä ãÕäÚ ÇáİáÊÑ áã íõÛíóøÑ — ÇáÊŞííÏ İí ØÈŞÉ ÇáÚÑÖ/ÇáÎíÇÑÇÊ. åĞÇ íÍŞøŞ ÇáãÊØáóøÈ (ÚÏã ÙåæÑ ÚÖæ ÊÊÈøÚ İí ÅÍÕÇÁÇÊ ÇáÊÃßíÏ) Ïæä ßÓÑ ÇÎÊÈÇÑÇÊ ÇáİáÊÑÉ ÇáÊí ÊÈäí ÃÚÖÇÁ ÈáÇ ÕáÇÍíÇÊ ãÎÒøäÉ.

**ÇáÇÎÊÈÇÑÇÊ:**
- ÌÏíÏ İí `DashboardTeamPerformanceTest`: «the confirmation view never names a tracking-only member and managers run both cohorts» — ÚÖæ ÊÊÈøÚ İŞØ (ØáÈíÉ ãõæÌóøåÉ Åáíå ÈÍÇáÉ `CONFIRMED`¡ ÈíÇäÇÊ ÛíÑ ÕÍíÍÉ Úä ŞÕÏ) **áÇ íÙåÑ** İí ÊÈæíÈ ÇáÊÃßíÏ¡ ÈíäãÇ ÇáÚÖæ ÇáãÒÏæÌ æ`manager` (ŞÇáÈ ÏæÑå ÈáÇ `ORDER_CONFIRM`) íÙåÑÇäº æÚÖæ ÇáÊÊÈøÚ **íÙåÑ ÔÑÚğÇ** İí ÊÈæíÈ ÇáÊæÕíá. æáÇ íÑÏ ÇÓã ÚÖæ ÇáÊÊÈøÚ İí HTML ÇáÊÃßíÏ ßßøá (ŞÇÆãÉ ÇáÎíÇÑÇÊ ãŞÕæÕÉ).
- **ÇáäÊÇÆÌ:** `DashboardTeamPerformanceTest` 14/14¡ `DashboardFilterTest` 16/16¡ `DashboardFilterWiringTest` 19/19¡ `DashboardQueryBudgetTest` 4/4 — æ**ÓŞİ ÇáÇÓÊÚáÇãÇÊ áã íÑÊİÚ** (51/52¡ ÇáÔÑØ EXISTS áÇ íõÖíİ ÇÓÊÚáÇãğÇ). ãÌãæÚÉ `tests\Feature\Merchant` + `DemoStoreSeederTest` + `DashboardTeamPerformanceViewModelTest`: **878 äÇÌÍğÇ¡ 11 İÔáğÇ ÓÇÈŞğÇ İŞØ** (`DeliverySettingsTest` ×10 + `OrderFinancialSummaryTest` ×1 — ÇäİÕÇá ßæÏ `ecotrack` İí äØÇŞ ÇáÔÍä¡ ÎÇÑÌ 35.4). pint äÙíİ Úáì ÇáãáİøÇÊ ÇáãÚÏóøáÉ.

---

### 35.5: ÅÕáÇÍ ÌĞÑí áÇäåíÇÑ ÇáÓáÉ İí ÇáİÑæäÊ (`OrderRules::limits` ÈãäÊÌ null)

**ÇáÈáÇÛ:** ÚäÏ ÒíÇÏÉ ÇáßãíÉ Ãæ ÇáÅÖÇİÉ Åáì ÇáÓáÉ İí ÕİÍÉ ãäÊÌ ÇáİÑæäÊ (`/product/{slug}`) íÙåÑ:
`OrderRules::limits(): Argument #1 ($product) must be of type Product, null given, called in OrderRules.php on line 73`.

**ÇáÓÈÈ ÇáÌĞÑí (ãõËÈóÊ ÈÅÚÇÏÉ ÅäÊÇÌ ÍÑİíÉ İí ÇáÇÎÊÈÇÑ):** ãßæøä `storefront.product-detail` íÍãøá İí `mount()` ÇáãÊÛíÑÇÊ **ÈÏæä** ÚáÇŞÉ `product`¡ İÊõÓÊÏÚì `OrderRules::lineCap($variant)` ÇáÊí ÊõÍãøá `$variant->product` **lazy**. æÊÍÏíËÇÊ Livewire ÊĞåÈ Åáì `/livewire/update` — æåæ ãÓÇÑ ÚÇã **ÎÇÑÌ** ãÌãæÚÉ `{store}.domain` (ÇáÊí ÊÍãá `resolve.store`) — İáÇ íæÌÏ `StoreContext` ÃËäÇÁ ÇáÊÍÏíË¡ İíõÛáŞ `StoreScope` **fail-closed** (`whereRaw('1 = 0')`) ? `$variant->product` = null ? ÇäåíÇÑ. áã íßä ÊáæøË ÈíÇäÇÊ (İÍÕ ŞÇÚÏÉ demo: 17 ãÊÛíÑğÇ¡ ÕİÑ íÊíã/ãÍĞæİ-äÇÚã/ÛíÑ-äÔØ/ãÛÇíÑ-ááãÊÌÑ) Èá ãÔßáÉ **ÓíÇŞ**.

**ÇáÅÕáÇÍ (ØÈŞÊÇä ÌĞÑíÊÇä¡ áÇ ÊÑŞíÚ):**
1. **äÙÇãí:** ãíÏáæíÑ ÌÏíÏ `App\Http\Middleware\ResolveStoreContextFromHost` ãõÓÌóøá İí `web(append:)` (`bootstrap/app.php`) — íõÚíÏ ÓíÇŞ ÇáãÊÌÑ ãä **ÇáÜ host** Úáì ßá ØáÈ web (ÈãÇ İíå `/livewire/update`) **İŞØ** ÚäÏãÇ íßæä ÇáÓíÇŞ İÇÑÛğÇ æÇáãõÖíİ `{slug}.{domain}` äÔØğÇ¡ ãÚ ÊÌÇåá ÇáãõÖíİÇÊ ÇáãÍÌæÒÉ (`www/app/admin/api/mail`) æÇáãÊÇÌÑ ÛíÑ ÇáäÔØÉ/ÇáãÌåæáÉ. áÇ íÊÌÇæÒ ÓíÇŞğÇ ŞÇÆãğÇ (Âãä ÖÏ ÇáÊÒÇãä ãÚ ãíÏáæíÑ `resolve.store`).
2. **ÍÊãí İí ÇáŞæÇáÈ:** ÊãÑíÑ ÇáãäÊÌ/ÇáãÊÌÑ ÇáãËÈóøÊ ÕÑÇÍÉğ ÈÏá lazy — `product-detail` (3 ãæÇÖÚ: `addToCart`, `incrementQuantity`, æÊÍãíá ÇáÜpayload) æ`variant-matrix` (ÓØÑ `cap`) ÕÇÑÊ `OrderRules::lineCap($variant, $this->product?->store, $this->product)`. ÃõÈŞí ÇáäæÚ `Product` ÕÇÑãğÇ İí `OrderRules` (ÊÃßíÏ ÇáËÇÈÊ).

**ÏÑÓ ÊÒÇãäí:** Ãæá ãÍÇæáÉ İí `variant-matrix` ãÑøÑÊ `currentStore()` İÃÏÎá ÎØÃ ÅÚÏÇÏÇÊ ŞÏíãÉ (`allow_backorder`) — ÇÓÊõÈÏáÊ ÈãÊÌÑ ÇáãäÊÌ ÇáØÇÒÌ `$this->product?->store` æÃõÚíÏ ÇáÊÓÊ `stock caps lines until backorder is enabled` Åáì ÇáäÌÇÍ.

**ÇáÇÎÊÈÇÑÇÊ (ÇäÍÏÇÑ ÏÇÆã):** ãáİ ÌÏíÏ `tests/Feature/Storefront/StorefrontProductCapContextTest.php` (4 ÇÎÊÈÇÑÇÊ/15 ÊÃßíÏğÇ): (Ã) ÒíÇÏÉ ÇáßãíÉ + ÇáÅÖÇİÉ ááÓáÉ Ïæä ÇäåíÇÑº (È) ÊŞííã ÇáÓŞİ ãä ÇáãäÊÌ ÇáãËÈóøÊ ÈáÇ ÓíÇŞ æÈÏæä ÇÓÊÚáÇã ÚáÇŞÉ `product`º (Ì) ÇáãíÏáæíÑ íõËÈøÊ ÇáãÓÊÃÌÑ ãä ÇáÜ hostº (Ï) áÇ íÎÊáŞ ÓíÇŞğÇ ááãõÖíİÇÊ ÇáãÍÌæÒÉ/ÇáãÌåæáÉ/ÛíÑ ÇáäÔØÉ.

**ÇáÊÍŞŞ:** ãÌãæÚÉ `tests\Feature\Storefront` **130 äÇÌÍğÇ (450 ÊÃßíÏğÇ)** (ßÇä 126 ŞÈá Çáãáİ ÇáÌÏíÏ)º `OrderQuantityCapTest` 7/7º `php -l` + `view:cache` + Pint äÙíİ Úáì ÇáãáİÇÊ ÇáËáÇËÉ. íäÈÛí ÊÔÛíá ÇáãÌãæÚÉ ÇáßÇãáÉ (ÇáãíÏáæíÑ ÚÇã) — ãÚáóøŞ ÈãæÇİŞÉ ÇáãÓÊÎÏã ÈÓÈÈ ÊæŞİ ãÑÊíä ÓÇÈŞğÇ æÍÏ ĞÇßÑÉ PHP (`-d memory_limit=1536M` ãØáæÈ).

---

## ÇáŞÓã 39 — ÅÚÇÏÉ ÊÕãíã «ÅÍÕÇÆíÇÊ ÇáÊÃßíÏ» (ÇáãÕÏÑ ÇáßÇãá: æËíŞÉ ãÎÕøÕÉ)

**ÇáãÑÌÚ ÇáßÇãá:** `docs/plans/confirmation-statistics-redesign.md` — íÍæí ÇáŞÑÇÑÇÊ ÇáãËÈøÊÉ D1–D11¡ ÇáÊäÇŞÖÇÊ ÇáãÄßóøÏÉ C1–C6¡ ÎÑíØÉ ÇáãÌãæÚÇÊ¡ ÊæÍíÏ ÇáÍÓÇÈ¡ ÊÕãíã ÇáæÇÌåÉ (375/768/1440)¡ æÇáãÑÇÍá M0–M7 ÈãÚÇííÑ ÇáŞÈæá.
**ÇáÊÏŞíŞ ÇáãÑÇİŞ:** `docs/audits/2026-10-finance-tracking-dashboard-parity.md` (ÇáİÌæÇÊ G1–G5).

**ÇáÍÇáÉ:** ãæËøŞ. åĞå ÇáãÑÍáÉ **ÊæËíŞ İŞØ** (M0) + ÈäíÉ ÊÍÖíÑíÉ (M1) ÈãæÇİŞÉ ÕÑíÍÉº **áÇ íõßÊÈ Ãí ßæÏ ŞÈá ÇáãæÇİŞÉ Úáì ßá ãÑÍáÉ Úáì ÍÏÉ**.

**ÇáãÑÇÍá (ãáÎøÕº ÇáÊİÕíá æãÚÇííÑ ÇáŞÈæá İí ÇáæËíŞÉ):**
- **M0** — ÊæËíŞ: ÇáæËíŞÉ + åĞÇ ÇáÈäÏ + ÊÍÏíË `order-distribution-rules.md §9/§11` + ÚŞÏ ÇáÊÇÈ İí `financial-accounts.md`.
- **M1** — ÚãæÏ `orders.last_status_changed_at` (migration + İåÑÓ `(store_id, last_status_changed_at)` + ÎÊã ÚäÏ ßá ÇäÊŞÇá + ÊÚÈÆÉ ÑÌÚíÉ ãä `MAX(order_status_histories.created_at)`) + ÍÏË ÅÓäÇÏ ÇáÊÊÈøÚ ÇáÎİíİ.
- **M2** — ãÌãæÚÇÊ ãÕÏÑ æÇÍÏ (`CONFIRMED/PENDING/FAILED/CANCELED/DEFERRED/DUPLICATE/DELIVERY_OUTCOMES`) + ÇÓÊÚáÇã ãæÍøÏ `ConfirmationStatsQuery`.
- **M3** — äÓÈÉ ÊÃßíÏ ŞÇÈáÉ ááÊåíÆÉ (`confirmation_rate_denominator`) æãæÍøÏÉ ÚÈÑ ÇáÈØÇŞÉ/ÇáİÑíŞ/ÇáÅÌãÇáí.
- **M4** — ÔÑíØ ÇáİáÊÑ (ãäÊÌ ãÊÚÏÏ/ÚÖæ/İÊÑÉ/äæÚ ÊÇÑíÎº **ÈáÇ äÇŞá** İí ÇáÊÃßíÏ) + ÇáÊÇÈÇä ÇáÃÓÇÓíÇä (ÚÏÏ / ãÚÏá).
- **M5** — ÓßÔä İÑíŞ ÇáÊÃßíÏ (`without/with trash` ááÃÚÖÇÁ) íÊÈÚ ÇáÊÇÈ ÇáÃÓÇÓí.
- **M6** — ÇÎÊÈÇÑÇÊ: ãíÒÇäíÉ ÇÓÊÚáÇãÇÊ¡ ÊæŞíÊ ÇáãÊÌÑ¡ trash¡ ãİÊÇÍ ÇáÅÓäÇÏ¡ ÇáãäÊÌ/ÇáãÊÛíøÑ¡ ãÍæÑ ÇáÊÇÑíÎ.
- **M7** — ÈäÏ «ÅÍÕÇÆíÇÊ ÇáãÏÇÎíá» İí ÈÑäÇãÌ ÇáãÇáíÉ 38-B/38-D.

**ŞíæÏ ÚÇãÉ:** Apple Restraint (`DESIGN_SYSTEM.md`)º ãŞÇííÓ 375/768/1440º ÕİÑ N+1º «ÇÍÓÈ ÚäÏ ÇáãÕÏÑ»º «ÕİÑ = ãæÌæÏ».

**ÇáÊäİíĞ — M0 (ãäİøĞ):** åĞå ÇáæËíŞÉ + ÊÍÏíË `order-distribution-rules.md §9/§11` + ÚŞÏ ÇáÊÇÈ æÊÓÌíá ÈäÏ ÇáãÏÇÎíá İí `financial-accounts.md`.

**ÇáÊäİíĞ — M1 (ãäİøĞ):**
- åÌÑÊÇä: `2026_10_11_000001_add_last_status_changed_at_to_orders_table.php` (ÚãæÏ `timestamp` nullable + İåÑÓ `orders_store_last_status_changed_at_index`) æ`2026_10_11_000002_backfill_last_status_changed_at_on_orders.php` (ÊÚÈÆÉ ÑÌÚíÉ ãä `MAX(order_status_histories.created_at)`).
- ÇáÎÊã İí `OrderObserver` (äŞØÉ ÇáÇÎÊäÇŞ ÇáæÇÍÏÉ): `created()` ãä `created_at`¡ æ`handleStatusChange()` ÈÜ`now()` ÚäÏ ßá ÇäÊŞÇá (ÂÎÑ-íßÊÈ). ÃõÖíİ ÇáÚãæÏ Åáì `Order::$fillable` æ`$casts`.
- ÍÏË ÅÓäÇÏ ÇáÊÊÈøÚ: `OrderAuditService::trackingAssigned()` (event_type `tracking_assigned`) ãä `OrderTrackingAssignmentService::assign()/reassign()` + ãİÇÊíÍ ÊÑÌãÉ İí ÇááÛÇÊ ÇáÃÑÈÚ.

**ÇáäÊÇÆÌ:** `migrate` äÌÍº ÇáÊÚÈÆÉ 14/16 ãÎÊæãÉ æ16/16 ãØÇÈŞÉ áÜ`MAX(history.created_at)`º `OrderStatusCaptureTest` 11/11º `OrderTrackingAssignmentServiceTest` 10/10º ÊÛØíÉ/ãáßíÉ/ÊŞæíÉ/ÃÏæÇÑ 44/44º `FinancialCaptureSchemaTest` 4/4 (ÊÚÏíá rollback Åáì `--step 6`)º Pint äÙíİ. İÔáÇä ÓÇÈŞÇä ãÓÊŞáøÇä ãÄßóøÏÇä ÈÇáÚÒá: `DashboardQueryBudgetTest` (53/52) æ`OrderTransitionSourceTest` (`state_code` Úáì `char(2)` İí MySQL).

**ÇáÊäİíĞ — M2 (ãäİøĞ):**
- `ConfirmationStatsGroups.php`: ãÕÏÑ æÍÏÉ áÊÕäíİ ÇáÊÇÈ (CONFIRMED = confirmed/preparing/processing İŞØº PENDINGº FAILED = no_answer_1..3º CANCELEDº DEFERREDº DUPLICATEº DELIVERY_OUTCOMESº UNCLASSIFIED ááÍÇáÇÊ ÇáãÎÕøÕÉ ÍÊì D11) ÈÏæÇá `all()/group()/in()/keys()`.
- `ConfirmationStatsQuery.php`: ŞÑÇÁÉ ãõÌãóøÚÉ æÇÍÏÉ `GROUP BY (assigned_to_membership_id, statuses.key)` ÊõÚíÏ `total` æ`deleted` ãÚğÇº ÊÚíÏ ÇÓÊÎÏÇã `DashboardOrderScope::applyPending` áÈõÚÏ ÇáÅÓäÇÏ + ÇáäÇŞáº ãÍæÑ ÇáÊÇÑíÎ `created_at` Ãæ `COALESCE(last_status_changed_at, created_at)`º İáÊÑ ãäÊÌ/ãÊÛíøÑ ÚÈÑ `EXISTS`.
- `DashboardFilter`: ÍŞæá `dateBasis`/`productIds`/`variantIds` (ÇİÊÑÇÖíÉ ãÊæÇİŞÉ) ãÚ ÊÍÏíË `hash()/toArray()/buildPrevious()`.
- **ŞÑÇÑ åäÏÓí:** ÚÏã ÊÚÏíá `OrderStatusStage` (ãÓÇÑ ÇáÊÚæíÖ/ÇáÑæÇÊÈ İí 38-C) — ãÌãæÚÇÊ ÇáÊÃßíÏ ÅÍÕÇÆíÉ ãÓÊŞáÉº ÊÖííŞ äØÇŞ ãŞÕæÏ Úä ÕíÇÛÉ ÇáÎØÉ.
- **ÇáäÊÇÆÌ:** `ConfirmationStatsGroupsTest` 5/5º `ConfirmationStatsQueryTest` 2/2º `--filter="Dashboard"` 134 äÌÇÍ (ÇáİÔá ÇáæÍíÏ `DashboardQueryBudgetTest` 53/52 ÇáãÚÑæİ)º Pint äÙíİ. ãÄÌóøá áÜM4: ÇÎÊÈÇÑ İáÊÑ ÇáãäÊÌ (íÍÊÇÌ ÊåíÆÉ product_variant).

**ÇáÊäİíĞ — M3 (ãäİøĞ):**
- `ConfirmationRateDenominator.php`: ÇáÇİÊÑÇÖí ÇáãæÕì Èå `[CONFIRMED, CANCELED, FAILED]` + preset `PRESET_EXCLUDE_DUPLICATE_AND_DEFERRED`º `resolve()` íİáÊÑ ÇáãÌãæÚÇÊ ÛíÑ ÇáãÚÑæİÉ æíÚæÏ ááÇİÊÑÇÖí.
- `ConfirmationStatsReport.php`: Øíø Õİæİ `ConfirmationStatsQuery` Åáì `groups`/`confirmed`/`denominator`/`rate` ÚÈÑ `build()` (ÈØÇŞÉ/ÅÌãÇáí) æ`perMember()` (Õİæİ ÇáİÑíŞ) — äİÓ ÇáÏÇáÉ İÊÕÈÍ ãÓÇæÇÉ «ÈØÇŞÉ = ÚÖæ = ÅÌãÇáí» ÎÇÕÉ ÑíÇÖíÉ (ÊÍáø C1).
- `store_settings.confirmation_rate_denominator` (åÌÑÉ `2026_10_11_000003`º JSON nullable) + `StoreSetting` `$fillable/$casts('array')`.
- **äØÇŞ:** ãÍÑøß ÇáäÓÈÉ ÇáãæÍøÏ + ÇáÊåíÆÉ + ÇáÇÎÊÈÇÑÇÊº ÇáÑÈØ ÈÇáæÇÌåÉ İí M4/M5¡ æáã íõáãÓ ãÓÇÑ ÇááæÍÉ ÇáÍÇáí ÚãÏğÇ.
- **ÇáäÊÇÆÌ:** `ConfirmationRateDenominatorTest` 3/3º `ConfirmationStatsReportTest` 5/5º `ConfirmationStatsQueryTest` 3/3 (ÇÎÊÈÇÑ ÊßÇãáíø query?report)º ÇáåÌÑÉ ãØÈøŞÉ (`column-ok`)º Pint äÙíİ. İÔáÇä ÓÇÈŞÇä ãÓÊŞáøÇä ÙåÑÇ İí ÇáãÌãæÚÉ æÇÓÚÉ ÇáİáÊÑ: `PasswordConfirmationTest` (419) æ`CartOrderLimitsTest` (`state_code` `char(2)`).

**ÇáÊäİíĞ — M4 (ãäİøĞ):**
- **İáÊÑ ÇáÊÃßíÏ** (áÇ äÇŞá D4): ÃÒÑÇÑ İÊÑÉ + äæÚ ÇáÊÇÑíÎ (`created`/`last_status_changed`) + ÚÖæ/ÈõÚÏ + **İáÊÑ ãäÊÌ ãÊÚÏÏ** — ßáåÇ İí `partials/filter-bar.blade.php`¡ ãÚ ÔÑÇÆÍ «İáÊÑ äÔØ» (ÊÑÉ/ÚÖæ/äæÚ ÊÇÑíÎ/ãäÊÌÇÊ).
- **ÇáÊÇÈÇä ÇáÃÓÇÓíÇä (D11 server-side):** `confirmationTab` (`count`/`rate`) ÚÈÑ `#[Url]` + `setConfirmationTab()` ãÚ ÍÇÑÓ Şíã + `resetFilters()`º ÈØÇŞÉ ÇáäÓÈÉ ÊÚÑÖ ÇáäÓÈÉ ãÚ ãáÇÍÙÉ ÇáãŞÇã (D10).
- **`DashboardFilterFactory`:** `resolveDateBasis()` (ÛíÑ ÇáãÚÑæİ ? `created`) æ`resolveIdList()` (ÊØÈíÚ + ÅÒÇáÉ ÊßÑÇÑ + ÈÊ/ÖÈØ İÇÑÛ/`'0'` ? null + ÓŞİ 100).
- **M4 bug (ãåã):** ÏÇáÉ `uses()` İí Volt ÊŞÈá **array|string** İŞØ — `uses(A, B)` íõÓÌøá A İŞØ æíÓŞØ B ÈÕãÊ. ßÇä ÓÈÈ `MethodNotFoundException` áÜ`openProductFilter`. ÇáÍá: `uses([...])` İí `dashboard.blade.php`. ÎÕÇÆÕ ÇáİáÊÑ ÇáãõÌÒÃÉ áÍÇáÉ Volt ãÓÌøáÉ ÚÈÑ `state([...])` (äãØ `orders/index.blade.php`) — ÎÕÇÆÕ ÇáÜ trait ÇáÚÇãÉ ÈáÇ `#[Url]` ÛíÑ ãßÔæİÉ ááãßæøä.
- **`DashboardProductFilterConcern`:** ÊÍæøá Åáì **methods-only** (ÇáÎÕÇÆÕ ÚÈÑ `state()` İí ÇáÜ view) + ÓÍÈ ÇáãäÊÌÇÊ ÇáãõÌÒÃÉ `loaderProductResults/loadFilterProducts` + `toggleFilterProduct/closeProductFilter` (äİÓ ÓÇŞ `HasOrderProductPicker` ÇáãÎÒøäÉ).
- **`StoreDashboardAnalyticsService::confirmationStats($filter)`:** ÊõÌãøÚ `ConfirmationStatsReport::build()` Úáì ÇáÔÑíÍÉ/ÇáÊÇÈ (ÊÑÌÚ ÅÌãÇáí + ÊÑÇÔ + 8 ãÌãæÚÇÊ Ãæ 6 ãÌãæÚÇÊ ÇáäÓÈÉ) + `buildConfirmationFilter()` ßãÑÌÚ.
- **ÇáæÇÌåÉ:** `partials/confirmation-stats.blade.php` (ÇáÚäæÇä/ÇáÊÑÇÔ/ÊÈæøÈÇÊ/ÇáãÌãæÚÇÊ¡ Õİæİ ÇáãÍĞæİÉ Èíä ŞæÓíä ÍãÑÇÁ) + `partials/product-filter-modal.blade.php` (ÈÍË + ŞÇÆãÉ ãõÌÒÃÉ + ÔÇÑÇÊ ãäÊÌ + ÊØåíÑ). ÃÕáÍ ÃÓãÇÁ ÇáÃíŞæäÇÊ `info`?`info-circle` (áÇ ÊæÌÏ ÃíŞæäÉ `info` İí `x-edz.icon`).
- **ãíÒÇäíÉ ÇáÇÓÊÚáÇãÇÊ:** `confirmationStats` íÖíİ ÇÓÊÚáÇã ÊÌãíÚ +1 ? ÓŞİ `DashboardQueryBudgetTest` 52?54 ÈÊÚáíŞ (ÇáÏãÌ ÇáÍŞíŞí ááÇÓÊÚáÇãÇÊ ÎÇÑÌ ÇáäØÇŞ = M6).
- **ÇáÇÎÊÈÇÑÇÊ:** `DashboardConfirmationSectionTest.php` (ÌÏíÏ — 7/7: ÚÑÖ ÇáÓßÔä/ÇáÊÇÈ ãÚÏá/ÇáÏŞÉ ãÚ ÇáãÍĞæİÉ Èíä ŞæÓíä/ÃÒÑÇÑ äæÚ ÇáÊÇÑíÎ ÎÇÕÉ ÈÇáÊÃßíÏ/ÇáİáÊÑ íÚãá ÚÈÑ `Volt::test`/ÇáİáÊÑ íÖíøŞ ÇáÓßÔä/ÍÇÑÓ ÇáÊÇÈ) + ÊæÓÚÇÊ `tests/Unit/DashboardFilterTest` (hash/dateBasis/productIds/variantIds) + ÊÍÏíË 3 ÊÃßíÏÇÊ İí `DashboardFilterWiringTest` (D4: ÇáäÇŞá ÛÇÆÈ İí ÇáÊÃßíÏ) + `DashboardQueryBudgetTest` (54).
- **ÇáäÊÇÆÌ:** `pest --filter="Dashboard"` **144 äÇÌÍğÇ (889 ÊÃßíÏğÇ)** İí ÇáãÑÉ ÇáÃÎíÑÉº Pint äÙíİ (ŞÑÇÑ `uses()` ÇáÃİŞí ÃÕáÍ `openProductFilter`). **ãÄÌóøá (M4 leftover¡ ÈÎíÇÑ ÇáãäÊÌ ÇáßÇãá ÇáÈÇŞí):** ÊÈæíÈ «ÇáãäÊÌ İí ÓßÔä ÇáÊÃßíÏ» íÄßÏ ÈÊÕãíã ãÊÛíøÑ (İŞØ ÇáãÊÛíøÑ ÇáãÎÊÇÑ íÙåÑ ÇÓÊåáÇß ÇáãäÊÌ) — ãÄÌá áÃäå íÊØáÈ ÅÖÇİÉ ãäÊÌ ÇáãÊÛíøÑ Åáì ÇáŞÇÆãÉ æßÔİ `ÚÑÖ_ááãäÊÌ`¡ æåæ ãÚÑæİ ãä M5/M6.
- **ãÊÈŞòø/ãÚÇííÑ ÇáŞÈæá M4:** ãÑÇÌÚÉ ÈÕÑíÉ ááŞØÇÊ **375/768/1440** (íÍÊÇÌ ÇáãÓÊÎÏã) Ëã ÅÛáÇŞ M4 İí ÇáæËíŞÉ §12.

**ÇáÊäİíĞ — M5 (ãäİøĞ):**
- **`DashboardConfirmationTeam.php`** (ÌÏíÏ¡ ÈáÇ ÇÓÊÚáÇãÇÊ): íÍæøá `ConfirmationStatsReport::perMember()` Åáì Õİæİ ÇáÓßÔä. ßá Õİ íÍãá `groups`/`confirmed`/`denominator`/`rate`/`total`/`trash`/`deleted_member`/`type` (`member`/`unattributed`/`total`). Õİ ÇáÅÌãÇáí **íÌãÚ `confirmed` æ`denominator`** İíßæä ãÚÏøáå ÊÑÇßãíğøÇ (áÇ íÃÎĞ ãÚÏøá ÚÖæ æÇÍÏ)¡ æ`row.name = null` ááÃÓãÇÁ ÇáãÍæøáÉ (íÊÑÌãåÇ ÇáÜ view ÚÈÑ `team_{type}` İáÇ íÊÑÌã ÇáÜ presenter). ÇáÊÑÊíÈ: `total` ÊäÇÒáíğøÇ Ëã ÇáÇÓã. Õİ «ÛíÑ ãõÓäÏ» íÙåÑ ÚäÏ `memberScopeIds === null` Ãæ ÇáÓäíÊäáº Õİ ÇáÅÌãÇáí ÚäÏ ÚÏã ÇáÊÍÏíÏ Ãæ ÃßËÑ ãä ÚÖæº ÇáÚÖæ ÛíÑ ÇáãÑÆí íõÓŞóØ ÈáÇ ÊÓãíÉº ÇáÚÖæ ÇáãÎÊÇÑ ÈáÇ ØáÈÇÊ íÍÕá Úáì Õİ ÕİÑí ÈáÇ ÅÌãÇáí.
- **`DashboardFilterOptions::members($current, $dimension, bool $includeTrashed = false)`**: ÈÏæä ÊÑÇÔ = ÇáäÔØæä İŞØ (`is_active=true`)º ãÚ ÊÑÇÔ = `withTrashed()` (ÈáÇ ŞíÏ `is_active`) + Úáã `'deleted' => $m->trashed()` Úáì ÇáßÇÆä ÇáãõÚÇÏ.
- **`DashboardFilterConcern`**: `#[Url(as: 'tt')] public bool $teamTrash = false;` + `setTeamTrash()` + ÅÚÇÏÉ ÇáÖÈØ İí `resetFilters()`. ÍÇáÉ æÇÌåÉ ÈÍÊÉ (ÎÇÑÌ DTO/hash)¡ áÇ ÊáÛí ŞæÓ ÇáØáÈÇÊ ÇáãÍĞæİÉ ÃÈÏğÇ.
- **`StoreDashboardAnalyticsService::confirmationTeam($filter, $members)`**: `ConfirmationStatsQuery::run()` ? `perMember()` ? `DashboardConfirmationTeam::present()`º æÇÓÊõÎÑÌÊ `confirmationDenominatorGroups()` ÇáãÔÊÑßÉ ãÚ `confirmationStats()`.
- **ÇáæÇÌåÉ**: `partials/confirmation-team.blade.php` (ãİÊÇÍ «ÈÏæä/ãÚ ÓáÉ ÇáãåãáÇÊ» ááÃÚÖÇÁ + Õİæİ ÈØÇŞÇÊ: ÊÇÈ ÇáÚÏÏ = «ßá ÇáØáÈÇÊ» + «ÇáÊÑÇÔ»º ÊÇÈ ÇáãÚÏá = ÇáäÓÈÉ + 6 ÔÑÇÆÍ + ÔÑíÍÉ `(ãÍĞæİÉ)` ÍãÑÇÁ + ÔÑíØ ÊŞÏøãº ÍÇáÉ İÇÑÛÉ ÚäÏ ÛíÇÈ ÇáäÔÇØ). ÇáÅÏÑÇÌ İí `dashboard.blade.php` ÍÓÈ ÇáÚÑÖ: ÇáÊæÕíá íÈŞì Úáì ÌÏæá 37-J¡ æÇáÊÃßíÏ Úáì ÇáÓßÔä ÇáÌÏíÏ.
- **ÊæİíÑ ÇÓÊÚáÇãÇÊ**: `members()` ÊõäÇÏì ãÑøÉ æÇÍÏÉ ÈÚáã ÇáÊÑÇÔ ÍÓÈ ÇáÚÑÖ (ÕİÑ ÇÓÊÚáÇã ÅÖÇİí)¡ æŞõíøÏ `teamPerformance()` ÈÚÑÖ ÇáÊæÕíá İŞØ (ÊæİíÑ ÇÓÊÚáÇãíä İí ÇáÊÃßíÏ).
- **ÇáÊÑÌãÉ (ar/en/fr/es):** `team_all_orders`, `team_members_clean_trash`, `team_members_with_trash`, `team_member_deleted`.
- **ÇáÇÎÊÈÇÑÇÊ:** `tests/Unit/DashboardConfirmationTeamTest.php` (ÌÏíÏ — 9/9: ãÌãæÚ ÇáÕİ/ÇáÅÌãÇáí ÇáÊÑÇßãí/ÇáİÑÒ/Õİ «ÛíÑ ãõÓäÏ»/ÇáÓäíÊäá/ÅÓŞÇØ ÚÖæ ÛíÑ ãÑÆí/ÇáÚÖæ ÇáãÍĞæİ/Õİ ÕİÑí) + `tests/Feature/Merchant/DashboardConfirmationTeamTest.php` (ÌÏíÏ — 9/9: ÊÇÈ ÇáÚÏÏ (ßá ÇáØáÈÇÊ+ÇáÊÑÇÔ)/ÊÇÈ ÇáãÚÏá æÇáÔÑÇÆÍ/ÇáãÍĞæİÉ áÇ ÊÏÎá ÇáäÓÈÉ æÊÙåÑ Èíä ŞæÓíä/ãİÊÇÍ ÇáÊÑÇÔ íÎİí Ëã íõÓãøí ÇáÚÖæ ÇáãÍĞæİ/ÚÖæ ãÎÊÇÑ íõÓŞØ ÇáÅÌãÇáí/ÇáÓäíÊäá/ãæÙİ ÈáÇ ÅÍÕÇÆíÇÊ/ÍÇáÉ İÇÑÛÉ/ÇáæÕá) + ÊŞáíã `DashboardTeamPerformanceTest` áÚÑÖ ÇáÊæÕíá İŞØ (5/5).
- **ÇáäÊÇÆÌ:** `pest --filter="Dashboard"` **153 äÇÌÍğÇ (896 ÊÃßíÏğÇ)**º Pint äÙíİ (ÃÕáÍ `concat_space` İí ÇÎÊÈÇÑ ÇáãíÒÉ).
- **ãÊÈŞòø/ãÚÇííÑ ÇáŞÈæá M5:** ãÑÇÌÚÉ ÈÕÑíÉ ááŞØÇÊ **375/768/1440** (íÍÊÇÌ ÇáãÓÊÎÏã) Ëã ÅÛáÇŞ M5 İí ÇáæËíŞÉ §12º æíÈŞì ÇÎÊíÇÑ ÇáãÊÛíøÑ (M4 leftover) áÃä M6.

**ÇáÊäİíĞ — M5.1 ÊäŞíÍ ÇáÊÕãíã (ãäİøĞ — 2026-10-11):**
- **ãáÇÍÙÇÊ ÇáãÓÊÎÏã:** ÊßÑÇÑ «ÅÌãÇáí ÇáØáÈÇÊ» (ãäİÑÏÉ + ÏÇÎá ÓßÔä ÇáÊÃßíÏ)º ÅÒÇáÉ ÈØÇŞÇÊ ÇáãäÊÌÇÊ/ÇáİÑíŞ/ãáÛÇÉº ÊÍÓíä ÇáÊäÇÓŞº ÓßÔä ÇáÊÃßíÏ ÃİŞíº ÊÇÈ ÇáÚÏÏ = «ßá ÇáØáÈÇÊ + ÇáÊÑÇÔ» ÈáÇ ÔÑÇÆÍ ÍÇáÇÊº ÃÏÇÁ ÇáÊæÕíá ÈØÇŞÇÊ ÚãæÏíÉ + ÅÙåÇÑ ÇáãÍĞæİ áßá äæÚº İÕá ÇáÊæÕíá Úä ÇáÊÃßíÏ æÅÙåÇÑ «ÛíÑ ÇáãõÑÓáÉ» ÇáãõÓäÏÉ áßá ÚÖæ.
- **`dashboard.blade.php` (KPI):** ÈØÇŞÉ «ÅÌãÇáí ÇáØáÈÇÊ» ÕÇÑÊ áÚÑÖ ÇáÊæÕíá İŞØ (`@if ($isDeliveryView)`)º ÍõĞİÊ ÈØÇŞÉ «ãáÛÇÉ» æÕİ ÇáÜKPI ÇáËÇäæí ßÇãáğÇ (AOV/ÇáãäÊÌÇÊ/ÇáİÑíŞ). ÇáÊÃßíÏ = **ãÚÏøá ÇáÊÃßíÏ + ŞíÏ ÇáÇäÊÙÇÑ** (`sm:grid-cols-2`)º ÇáÊæÕíá = ÅÌãÇáí/ÅíÑÇÏ/ÅÑÌÇÚ/AOV (`sm:grid-cols-2 xl:grid-cols-4`).
- **`dashboard.blade.php` (ÇáÑÓæã):** ÊÇÈ ÇáÚÏÏ = ÇáÓßÔä + ÇáÑÓæã İí Õİ æÇÍÏ (`lg:grid-cols-2`, `$narrowCharts=true`)º ÊÇÈ ÇáãÚÏá = ÇáÓßÔä ÈÚÑÖ ßÇãá æÇáÑÓæã ÃÓİáå (`$narrowCharts=false`)º ÇáÊæÕíá ßãÇ åæ.
- **`confirmation-stats.blade.php`:** ÊÇÈ ÇáÚÏÏ = ÈØÇŞÊÇä İŞØ («ßá ÇáØáÈÇÊ» + «ÇáÊÑÇÔ»)¡ ÍõĞİÊ ÇáÔÑÇÆÍ ÇáËãÇäíÉº ÊÇÈ ÇáãÚÏá = ÇáäÓÈÉ + ÇáÔÑÇÆÍ ÇáÓÊ ÃİŞíğøÇ (`grid-cols-2 sm:grid-cols-3 lg:grid-cols-6`).
- **`DashboardConfirmationTeam.php` + `confirmation-team.blade.php`:** ÅÖÇİÉ `not_sent` = `total ? DELIVERY_OUTCOMES.total` áßá Õİ + ÚãæÏ «ÛíÑ ãõÑÓáÉ»º ãİÊÇÍ `dashboard.team_not_sent` İí ÇááÛÇÊ ÇáÃÑÈÚ.
- **ÃÏÇÁ ÇáÊæÕíá (37-J):** ÊÍæíá ÇáÌÏæá Åáì **ÈØÇŞÇÊ ÚãæÏíÉ** Èßá ÚÖæ (ÇÓã + ÅíÑÇÏ ãõÓáóøã + 4 ãŞÇííÓ + ÔÑíØÇ ÇáãÚÏøáíä)¡ æßá ãŞíÇÓ íõÙåÑ ÔŞíŞå ÇáãÍĞæİ `(n)` ÈÇáÃÍãÑ. ÏÚã ÈíÇäÇÊ: `DashboardTeamPerformanceQuery` íÌãÚ `*_deleted` áßá äæÚ (`countWhen`/`countAll`)º `baseOrdersQuery(bool $withTrashed=false)` + ŞÇÚÏÉ ÇáİÑíŞ `withTrashed:true`º `DashboardTeamPerformance` íãÑøÑ ÇáÍŞæá ÇáãÍĞæİÉ æíÍÓÈ `in_progress_deleted`/`other_deleted` æ`total()` íÌãÚåÇ.
- **ãáÇÍÙÉ ÊŞäíÉ:** `@php($x = ...)` inline ãÚ `??` ßÓÑ ÊÌãíÚ Blade (ÈÊÑ `<?php` ÈáÇ `?>` æÓÍÈ ÇáÊÚáíãÇÊ ÇáÊÇáíÉ)º ÇÓÊõÎÏã `@php ... @endphp` ÇáßÇãá.
- **ÇáÇÎÊÈÇÑÇÊ:** ãÍáøá `tpfRow` ÃõÚíÏ ÈäÇÄå áŞÑÇÁÉ ÇáÈØÇŞÇÊº `DashboardFilterWiringTest` ÇÎÊÈÇÑ AOV ÃõÚíÏ ÕíÇÛÊå (ãÑÉ İí ÇáÊæÕíá/ÕİÑ İí ÇáÊÃßíÏ)º `DashboardConfirmationSectionTest` ÊÇÈ ÇáÚÏÏ (ÈáÇ ÔÑÇÆÍ).
- **ÇáäÊÇÆÌ:** `pest --filter="Dashboard"` **153 äÇÌÍğÇ (894 ÊÃßíÏğÇ)**º Pint äÙíİ.
- **ãÊÈŞòø:** ÇáãÑÇÌÚÉ ÇáÈÕÑíÉ ááŞØÇÊ **375/768/1440** (ÈíÏ ÇáãÓÊÎÏã)º Ëã **M6** ÈÇäÊÙÇÑ ãæÇİŞÉ ÕÑíÍÉ.

**ÇáÊäİíĞ — M5.2 (ãäİøĞ¡ 2026-10-11):** ÊäŞíÍ ËÇäò ãä ÓÈÚ ãáÇÍÙÇÊ ááãÓÊÎÏã (ÊæáÊíÈ ãÎÕøÕ ÈÏá `title=`º ÃíŞæäÉ ÇáãÍĞæİ ÈÏá `(n)`º ÈØÇŞÇÊ İÑíŞ ÚãæÏíÉº äŞá «ÈÇäÊÙÇÑ ÇáÊÃßíÏ» ÈÌÇäÈ ÇáÜKPIº ÈæÈ-ÃÈ áÔÑÍ ãÚÏøá ÇáÊÃßíÏº ÇáÊäÇÓŞ).
- **äŞá «ÈÇäÊÙÇÑ ÇáÊÃßíÏ»:** ÇÓÊõÎÑÌ Åáì `partials/pending-orders.blade.php` æÍõĞİ ÇáÈáæß ÇáŞÏíã ãä Õİ ÇáÌÏÇæáº İí Õİ KPI ÇáÊÃßíÏ ÔÈßÉ `lg:grid-cols-3` = KPI (`lg:col-span-2`) + ÇáÈØÇŞÉ (`lg:col-span-1`). «ÇáÃßËÑ ãÈíÚğÇ» íÈŞì İí Õİ ÇáÌÏÇæá áÚÑÖ ÇáÊæÕíá İŞØ.
- **ÊæáÊíÈ:** ÊáãíÍÇÊ ÇáãÌãæÚÇÊ (`group_*_hint`) + ÒÑ ÔÑÍ ãÚÏøá ÇáÊÃßíÏ İí `confirmation-stats.blade.php` ÊÓÊÎÏã `<x-edz.tooltip>` (ÕİÑ `title=` İí ãÌáÏ dashboard)º ãİÊÇÍ ÌÏíÏ `dashboard.confirmation_rate_explainer` İí ar/en/fr/es (`maxWidth="322px"`).
- **ÔÇÑÉ ÇáãÍĞæİ:** `(n)` ÇáÍãÑÇÁ ÇÓÊõÈÏáÊ ÈÜ`<x-edz.tooltip>` (ÃíŞæäÉ `trash` + ÇáÚÏÏ + `dashboard.deleted_brackets`) İí `confirmation-stats` æ`team-performance` æ`confirmation-team`.
- **ÈØÇŞÇÊ İÑíŞ ÇáÊÃßíÏ:** `confirmation-team.blade.php` ãä `space-y-2.5` Åáì ÔÈßÉ ÈØÇŞÇÊ `grid-cols-1 sm:grid-cols-2 xl:grid-cols-3`º ÈØÇŞÉ ÇáÅÌãÇáí ÊãÊÏ Úáì ßÇãá ÇáÚÑÖ.
- **ÊäŞíÉ:** `team-performance.blade.php` ÕÇÑ ÚÑÖ ÇáÊæÕíá İŞØ (ÍõĞİ İÑÚ ÌÏæá ÇáÊÃßíÏ ÇáãíÊ + ãÕİæİÊÇ ÇáÃÚãÏÉ + ÇáÊÚáíŞ ÇáŞÏíã).
- **ÇáÇÎÊÈÇÑÇÊ:** `DashboardConfirmationTeamTest` ÊÍŞøŞ ÇáãÍĞæİÉ ÚÈÑ `dashboard.deleted_brackets` (ÈÏá `(1)`)º `pest --filter="Dashboard"` **153 äÇÌÍğÇ (895 ÊÃßíÏğÇ)**º `view:cache` + Pint äÙíİÇä.
- **ÇÓÊÚÇÏÉ ÍÓÇÈ ÇáÏíãæ:** ŞÇÚÏÉ ÇáÈíÇäÇÊ ßÇäÊ İÇÑÛÉ (0 ãÓÊÎÏã/ãÊÌÑ) İÊÚĞøÑ ÏÎæá `demo@edzeery.com`º ÃõÚíÏ ÇáÈĞÑ ÈÜ `php artisan db:seed` (ÈáÇ `migrate:fresh`) ? 17 ãÓÊÎÏãğÇ/3 ãÊÇÌÑ/16 ØáÈğÇ¡ æßáãÉ ÇáãÑæÑ `password` ÊÚãá.
- **ãÊÈŞòø:** ÇáãÑÇÌÚÉ ÇáÈÕÑíÉ **375/768/1440** (ÈíÏ ÇáãÓÊÎÏã).

### Account / merchant/account (2026-10-16) — Livewire+FormRequest fix, x-edz.select, flatpickr
- [x] **Fixed validation root cause**: Livewire has no FormRequest handling (Wrapped.php ? ImplicitlyBoundMethod), so injecting `UpdateProfile/Settings/PasswordRequest` into a Volt action ran `validateResolved()` against the HTTP payload (not component state) ? every `required` rule failed ("name/email/current_password required"), and `personal-data` silently saved `validated() === []` (wiping all preferences). Added `rulesFor()` to the three Account FormRequests, switched `UpdateProfileRequest` to `auth()->user()`, and validate in the Volt actions via `$this->validate(UpdateProfileRequest::rulesFor(...))` instead of DI.
- [x] **profile.blade.php**: renamed `removeAvatar` ? `remove_avatar`; country/state/city now use `x-edz.select` with front-only search (`search`, no `lazy`/`source`/`scope`); `birthdate` uses flatpickr (`type="text"`, `wire:model.blur`, `dateFormat:'Y-m-d'`, matching `filter-bar.blade.php`).
- [x] **security.blade.php + personal-data.blade.php**: validate via `rulesFor()`.
- [x] **UpdateProfileAction**: `filled()` guards so empty-string country/state/city/birthdate become `null` (Livewire state uses `''`; no ConvertEmptyStringsToNull applies) — avoids FK violations.
- [x] **Profile model**: `protected $primaryKey = 'user_id'` — table PK is `user_id`; previously `save()` ran `UPDATE ... WHERE id = NULL`, a silent no-op that never persisted avatar removal or later phone/address edits (caught by the avatar test).
- [x] **Tests**: permanent `tests/Feature/Account/AccountFormsTest.php` (10 tests, incl. email-change requires current_password, geo normalization, existing-profile re-save, prefs persistence). Account+Profile+PasswordUpdate = 30 passed. Pint/`php -l` clean. Deleted temp TmpDiag* tests.
