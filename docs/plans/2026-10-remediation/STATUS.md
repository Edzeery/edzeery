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

## 2026-10-16 (تنفيذ PR-01..PR-11 — تقوية استعادة كلمة المرور)

- [x] **PR-01 🟠 محدّدات المعدّل (rate limiters) على مسارات المصادقة** · `AppServiceProvider::registerRateLimiters()` يضيف `auth-login` (5/دقيقة لكل بريد+IP، 20/دقيقة لكل IP)، `auth-register` (5/دقيقة/IP)، `auth-password-email` (3/دقيقة بريد+IP، 5/ساعة بريد، 15/ساعة IP)، `auth-password-reset` (5/دقيقة بريد+IP، 15/دقيقة IP) + ربط `throttle:` في `routes/auth.php` (login/admin-login/register/forgot/reset). التحقق: اختبارات المعدّل الثلاثة في `PasswordResetTest`.
- [x] **PR-02 🔴 P0 — إغلاق تعداد الحسابات في `PasswordResetLinkController`** · الرد واحد دائمًا (`passwords.sent`) سواء وُجد الحساب أم لا وسواء كان الـthrottle فعّالًا؛ النتيجة الحقيقية تُسجَّل داخليًا فقط. التحقق: `forgot password response does not reveal whether an account exists`.
- [x] **PR-03 🟠 حماية التوكن من التسريب عبر المُحيل (Referer)** · صفحة `reset-password` تُعاد برأس `Referrer-Policy: no-referrer` (يربط التوكن في المسار). التحقق: `reset password page suppresses the referrer to protect the token`.
- [x] **PR-04 🟠 إبطال الجلسات الأُخرى عند تغيير كلمة المرور** · `AuthenticateSession` مضاف إلى مجموعة `web` في `bootstrap/app.php`. التحقق: `changing the password invalidates sessions established with the old hash`.
- [x] **PR-05 🟠 سجل تدقيق أمني آمن** · `App\Services\Auth\AuthSecurityLogger` يسجّل أحداث الدخول/إعادة التعيين ببريد مُهشّر (sha256 + `app.key`) بلا كلمة مرور/توكن، ومحمي بـtry/catch. التحقق: `password reset activity is audited without storing raw identifiers`.
- [x] **PR-06 🟠 رسائل إعادة التعيين لا تكشف وجود الحساب** · invalid-user وinvalid-token كلاهما `passwords.token` العامة. التحقق: `reset submission does not reveal whether an account exists` + `reset with an invalid token surfaces the generic token error`.
- [x] **PR-07 🟠 منع إعادة استخدام التوكن تحت التزامن (race)** · `NewPasswordController::store` يلفّ `Password::reset` في `DB::transaction` + `Cache::lock('password-reset:{email}')` مع التقاط `LockTimeoutException` → `passwords.throttled`. **إصلاح خطأ:** كان يُلتقط `Illuminate\Cache\LockTimeoutException` غير الموجود → صُحّح إلى `Illuminate\Contracts\Cache\LockTimeoutException`. التحقق: `a reset token can only be used once`.
- [x] **PR-08 🟠 سياسة كلمة مرور موحّدة** · `AppServiceProvider::configurePasswordDefaults()`: `Password::min(12)->mixedCase()->numbers()->symbols()` (+ `uncompromised()` اختياري عبر `config('auth.password_uncompromised')`، افتراضي معطّل). تغيّر في `config/auth.php`. التحقق: `reset rejects a password that does not meet the policy` + تحديث كلمات اختبارات التسجيل/التحديث.
- [x] **PR-09 🟡 إشعار «تم تغيير كلمة المرور»** · `App\Notifications\PasswordChangedNotification` يُرسَل من `NewPasswordController` و`PasswordController`؛ مفاتيح `password_changed_subject/body` في `resources/lang/{en,ar,fr,es}/notifications.php`. التحقق: `a password change notification is sent after a successful reset`.
- [x] **PR-10 🟢 تحديث واجهة صفحات المصادقة (Apple/RTL/استجابة)** · استبدال `<ion-icon>` بـ`<x-edz.icon>` في `forgot-password`, `reset-password`, `input-error`, `auth-session-status`؛ مكوّن جديد `x-password-input` (زر إظهار/إخفاء كلمة المرور عبر Alpine + a11y `aria-pressed`) وأيقونة `eye-slash` جديدة؛ `inputmode=email`. التحقق: عرض الصفحات في اختبارات المصادقة (200).
- [x] **PR-11 🟢 اختبارات + توثيق** · توسيع `tests/Feature/Auth/PasswordResetTest.php` إلى 16 اختبارًا (enumeration، referrer، single-use، policy، throttle x3، جلسة، تدقيق) + تصحيح كلمات `RegistrationTest`/`PasswordUpdateTest`. التحقق: `vendor/bin/pest tests/Feature/Auth` — **43 ناجح**.
- ⚠️ **تنبيه تشغيلي:** كانت هناك **route cache قديمة** (`bootstrap/cache/routes-v7.php`) تُخفي `throttle:` — نُفّذ `php artisan route:clear` + `config:clear`. أي بيئة فيها `bootstrap/cache/*` يجب إعادة بناء/مسح الكاش بعد هذه التغييرات (`php artisan optimize:clear`).

## سجل التخطيط/الفحص (قراءة فقط — تمت في هذه الجلسة)

| النطاق | الحالة | الملاحظة |
|---|---|---|
| 01-security | 🟢 قيد التنفيذ | S-01، S-02، S-03 مكتملان؛ التالي S-04 |
| 02-auth-session | 🟢 قيد التنفيذ | PR-01..PR-11 مكتملة (تقوية استعادة كلمة المرور)؛ المتبقي A-03..A-05 |
| 03-data-integrity-money | 📋 مخطط | M-01..M-05 |
| 04-tenant-isolation | 🟢 قيد التنفيذ | T-03 مكتمل؛ أساس T-02 مكتمل، الدفعات (1)–(4) جارية |
| 05-ops-deps-infra | 📋 مخطط | O-01..O-09 |
| 06-performance | 📋 مخطط | P-01..P-07 |
| 07-design-system-apple | 📋 مخطط | DS-01..DS-20 + قرارات مفتوحة |
| 08-fintech-program | 📋 مخطط | F-01..F-05 |
| 09-code-health | 📋 مخطط | H-01..H-06 |
| 10-scalability-resilience | 📋 مخطط (يبدأ بعد المهام الجارية) | SR-00..SR-28 — بوابة `SR-00` إلزامية قبل البدء |

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

- [x] **A-Account -- P1 -- /merchant/account: Livewire+FormRequest validation fix + x-edz.select + flatpickr + Profile PK fix (2026-10-16)**:
  - `UpdateProfileRequest::rulesFor()` + `auth()->user()` (was `$this->user()`, null on a bare instance); `UpdateSettingsRequest::rulesFor()`; `UpdatePasswordRequest::rulesFor()`. Root cause: Livewire has no FormRequest handling (Wrapped.php -> ImplicitlyBoundMethod), so injecting the request into a Volt action ran `validateResolved()` against the HTTP payload, not component state => every `required` rule failed ("name/email/current_password required"). The settings form silently saved `validated() === []`, wiping all preferences.
  - `resources/views/livewire/account/profile.blade.php`: renamed `removeAvatar` -> `remove_avatar`; dropped `UpdateProfileRequest $request` DI in favour of `$this->validate(UpdateProfileRequest::rulesFor(...))`; country/state/city are now `x-edz.select` with front-only search (`search`, no `lazy`/`source`/`scope`); `birthdate` is flatpickr (`type="text"`, `wire:model.blur`, `dateFormat:'Y-m-d'`) matching the filter-bar convention.
  - `resources/views/livewire/account/security.blade.php` and `personal-data.blade.php`: validate via `rulesFor()` instead of injecting the FormRequest.
  - `app/Domains/Account/Actions/Profile/UpdateProfileAction.php`: `filled()` guards so empty-string country/state/city/birthdate become null (Livewire state uses `''`, and no ConvertEmptyStringsToNull middleware applies to component state; avoids FK violations).
  - `app/Domains/Account/Models/Profile.php`: `protected $primaryKey = 'user_id'` (table PK is user_id; previously `save()` ran `UPDATE ... WHERE id = NULL`, a silent no-op that never persisted avatar removal or later phone/address edits).
  - Verification: new `tests/Feature/Account/AccountFormsTest.php` (10 tests); Account + Profile + PasswordUpdate = 30 passed; Pint on 23+9 touched files; `php -l` clean. Deleted temp TmpDiag* tests.
