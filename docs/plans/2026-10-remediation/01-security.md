# 01 — Security (أمان: P0 ثم P1)

> الهدف: إغلاق أسوأ ثغرات النظام (RCE، سرقة حساب، رفع، جلسات، XSS) بأولوية P0 ثم P1.
> مرجع الحقائق: `docs/audits/2026-10-readiness-audit.md` (جدول الأرقام T1..T9, A7, A1).
> بوابات: `00-quality-gates.md` §1/§6/§8.

---

## S-01 🔴 P0 — إيقاف RCE في `HasInlineEdit::saveEdit`

**الملف:** `app/Livewire/Concerns/HasInlineEdit.php`
**السبب:** `saveEdit(array $config)` يستقبل من العميل:
- `rules` (يمكن أن تُمرَّر كائنه لإلغاء التحقق) — `:77-81`
- `subject` كـ callable → `system(base64...)` مشفّر في `editingId` — `:151-152`
- `apply` كـ callable → `file_put_contents` — `:134-136`
- `permission` يُلغى عند `null` → `:100-101`، والمكتبة تُصدر دوال trait عامة تصل عبر `/livewire/update`.
- لا `#[Locked]` على `editingId/editingValue`.

**الإصلاح المقترح (سطرًا بسطر):**
1. شغّل `startEdit`/`saveEdit` عبر سجلّ **ثابت في الكود** (registry) للـ `field => [permission, rules, subject resolver, apply]` — ممنوع جلب هذا من `editingValue`/`$config` المُراجَع من العميل.
2. `saveEdit(string $field): void` فقط يُستدعى؛ تُحل `$config` من الـ registry وليس من المعامل.
3. على كل حل: تحقق ملكية السجل الحالي للمتجر (`$row->store_id === currentStoreId()`).
4. أضف `\Livewire\Attributes\Locked` على `editingId`/`editingValue`/`editingField` لمنع تعديلها عبر الطلب.
5. `permission` إلزامي (افتراض fail-closed) — أزل المسار `permission === null => true`.
6. any callable من `$config` لخدمة إصلاح — لا تمر حرفيًا.
7. عدّل المستخدمين في السطر `:15-30` docblock (نوع `$config`).
   - «نقل registry»: ينشئ في `app/Livewire/Concerns/InlineEditDefinitions.php` أو `app/Support/InlineEdit/…`.

> **نفّذ 2026-10-09 ✓ (بضميمة أعمق، بلا registry منفصل):** إغلاق RCE تمّ من جهتين دون نقل كل configs الـ Volt:
> - `startEdit`/`cancelEdit`/`saveEdit` → `protected` — Livewire يسمح wire-callable **للطرق العامة فقط** (`BaseUtils::getPublicMethodsDefinedBySubClass`), فالعميل لا يستطيع اليوم استدعاء `saveEdit(array $config)` بموضوع/تطبيق callable (system/file_put_contents). الـ Volt closures (`$saveOrderWilaya`...) والمُستدعي الداخلي (`InlineEditComponent`) يستمران في استدعائها لأنها ضمن scope المكوّن.
> - `#[Locked]` على `editingId`/`editingField`/`editingError`/`editingSnapshot` — يرمي `CannotUpdateLockedPropertyException` عند محاولة ضبطها عبر wire؛ `editingValue` **بقي غير Locked عمدًا** لأنه مربوط بـ `wire:model="editingValue"` في 21 موضعًا (textarea/select) ولا يمر إلى callable بل إلى validation + apply المحدد خادميًا في كل closure.
> - `permission` أصبح **fail-closed**: أُزيل `permission === null => true`؛ كل 11 موقع saveEdit في orders + test component تُمرر permission صراحةً لذا لا انحدار.
> - اختبارات الـ hijack القديمة (order.edit.*) عُدّلت كي تتحقق أن `set('editingId'/'editingField')` يرمي Locked الآن بدل الوصول إلى الصلاحيات، مع إبقاء المسار الشرعي (start ثم save) يعمل بدون انحدار.
> - اختبار جديد: `InlineEditInfrastructureTest` — استدعاء `saveEdit([...system/config...])` عبر `->call()` يرمي `MethodNotFoundException` ولا يُسجَّل audit ولا تغيير.

**Regressions:** مكونات ترِد في `resources/views/livewire/**/*.blade.php` بمقدار `saveEdit([` — شغّل grep: `grep -rn "saveEdit(" resources/views/livewire`. كل موقعة يجب أن تصبح call إلى registry.

**اختبار قبول:**
- اختبار يحاول `system(...)` عبر `wire:update` → تُرفض (403/فشل).
- كل حقول التعديل الحالية تعمل.
- **الحجم:** الملف الحالي 197 سطرًا (≤250 ✓)؛ registry قد يرفع الملف — عيّن أنه لا يتضخم فوق السقف.

---

## S-02 🔴 P0 — إيقاف إعادة تعيين كلمة مرور موجود عبر `StoreTeamService`

**الملف:** `app/Services/Stores/StoreTeamService.php`
**السبب:** `addMember` → `User::firstOrCreate` ثم `if (!empty($data['password']) && ! wasRecentlyCreated)` **يكتب كلمة مرور الجديدة فوق حساب موجود** — سرقة حساب لأي إيميل (أتخذ حسبة P0). وكذلك `updateMember` عند `:110-112` يعيد تعيين كلمة مرور دون تحقق.

**الإصلاح المقترح:**
1. عند `!wasRecentlyCreated` (حساب موجود): **لا تُكتب `password`** أبدًا من `$data`. بدلًا: اعرض أيضًا في `$data` أمرًا صريحًا — مثلًا «change_password` للجايزة، ويتطلب توثيق الحساب (رمز/مصادقة) قبل `Hash::make`.
2. حث `updateMember` على نفس النهج: إن وُلدت `password`، تشترط إعادة تقديم كلمة المرور الحالية أولًا (أو رمز تأكيد).
3. أبقِ/لا تُغيّر اسم `guard_name = 'merchant'` في `:75` (لا علاقة أمنية).
4. سطر `:47-49` (wasRecentlyCreated) صحيح لكن لا يُغيّر روتين `firstOrCreate`.

> **نفّذ 2026-10-09 ✓**
> - `addMember`: أُزيلت كتابة `password` فوق الحساب الموجود نهائيًا؛ مسار «حساب موجود + كلمة مرور» يرمي `teams.cannot_set_password_on_existing_user` (فشل مغلق). الحساب الجديد فقط يستقبل كلمة المرور.
> - `updateMember`: تغيير كلمة مرور عضو يشترط `current_password` يُطابق كلمة مرور الفاعل الحالية (`Hash::check`) وإلا يرمي `teams.current_password_required` — يغلق إعادة التعيين الصامت من جلسة قديمة.
> - إعادة تسمية `$data` → `$payload` في `StoreTeamService` (قاعدة `00-quality-gates` §6).
> - بريد الاعتمادات يُرسل فقط للحساب الجديد فعليًا (`newCredentialsProvided`)، فالحساب الموجود لا يتسرب بريدًا يحمل كلمة مرور.
> - واجهة الأعضاء (`teams/index.blade.php` + `member-form`): أُضيف حقل `current_password` في وضع التعديل مع `required_with:password` وقاعدة تحقق على المعامل قبل الخدمة.
> - اختبارات جديدة في `StoreTeamCredentialsEmailTest`: (١) رفض منح كلمة مرور فوق حساب موجود مع الاسترجاع الكامل (لا عضوية)، (٢) عدم إرسال بريد اعتمادات لحساب موجود، (٣) رفض تغيير كلمة مرور دون إعادة مصادقة + قبوله عند المرور الصحيح.
> - `pint --dirty` (13 ملفات) + `tests/Feature/Merchant` كاملة خضراء (866 اختبار — 3 إضافيين لـS-02).

**Regressions:** `tests/Feature/Merchant/StoreTeamCredentialsEmailTest.php` — سيتأثر. عدّل الاختبار كي يحاكي المسار المسموح (مستخدم جديد) ومسار السرقة المحظور (مستخدم موجود بلا تأكيد → يُرفض).

---

## S-03 🔴 P0 — إغلاق ثغرة «جلسة قابلة للتسمم» بين المتاجر (عزل جلسة)

**الملفات:**
- `app/Http/Middleware/Merchant/Store/ResolveStoreFromRoute.php:27-33`
- `app/Http/Middleware/Merchant/Store/EnsureStoreMembership.php:19-27`
- `app/Http/Middleware/ResolveStoreFromSubdomain.php` (session write على أي متجر)
- `app/Support/StoreResolver.php:20-26` (يصدّق الجلسة بلا فحص عضو)

**الإصلاح:**
1. `ResolveStoreFromRoute` لا يكتب `current_store_id` قبل `EnsureStoreMembership`؛ أو كتابة الشرعية في نفس طلب تحقق العضوية.
2. `EnsureStoreMembership` عند 403: `session()->forget('current_store_id')` لتعطيل أثر السم.
3. `StoreResolver::resolve()` فرع الجلسة: تحقق `auth()->user()->stores()->where('id',$id)->exists()` قبل `app(StoreContext)->set`.
4. لا تُكتب `current_store_id` في `ResolveStoreFromSubdomain` إلا بعد تحقق العضوية (وما السلامة للغويين؟ يبقى المستهتر لا يعبر).
5. بعدها اختبار عبر `session(['current_store_id'=>other])` ثم محاولة توليد Vue→Livewire: متوقع 403/404.

> **نفّذ 2026-10-09 ✓**
> - `ResolveStoreFromRoute`: أُزيلت كتابة `session(['current_store_id' => $store->id])` نهائيًا (طرقت قبل تحقق العضوية)؛ الآن يضبط `StoreContext` فقط ويترك استمرار الجلسة لـ `EnsureStoreMembership`.
> - `EnsureStoreMembership`: عند غياب المتجر أو غياب عضوية نشطة → `session()->forget('current_store_id')` + 403 (السم يُسقط من الجلسة). عند النجاح → `session(['current_store_id' => $store->id])` + `app()->instance('currentMembership', $membership)` (المسار الوحيد الشرعي للكتابة).
> - `StoreResolver::resolve()` فرع الجلسة: لا يثق بـ `current_store_id` إلا إذا وُجدت عضوية نشطة `storeMemberships()->where('store_id',$id)->where('is_active',true)->exists()`؛ وإلا يفطر الجلسة (forget) ويعيد null. (مطابق للبند 3: `stores()` relation في User هي نفسها `belongsToMany` عبر `store_memberships` مع `is_active=true`).
> - `ResolveStoreFromSubdomain`: لا يكتب `current_store_id` إلا لمستخدم مصادق يملك عضوية نشطة على متجر النطاق — متجر المتفرج لا يسمم جلسة التاجر.
> - كُتب آخر الكتابات المشروعة (مدقَّق): `ChooseStoreController` (abort_unless مالك/عضو 403)، `LoginRedirectService` (اختيار من العضويات النشطة)، `create-store` (ينشئ متجره + عضوية في نفس المعاملة).
> - اختبارات جديدة `tests/Feature/Security/StoreSessionIsolationTest` (7): (1) جلسة مسمومة بلا عضوية → `StoreResolver` null + forget؛ (2) عضوية نشطة → يُحَل المتجر؛ (3) `merchant.dashboard` لمتجر أجنبي → 403 ومسح الجلسة؛ (4) متجره الشرعي → 200 والجلسة تبقى قابلة للحل لاحقًا؛ (5) `ResolveStoreFromSubdomain` لا يكتب لمتجر لا يملك العضوية؛ (6) يكتب للعضو النشط؛ (7) العضوية غير النشطة تُرفض في المسارين.
> - `pint --dirty` (18 ملفات) + `tests/Feature/Merchant` كاملة خضراء (866 اختبار / 1,173,050 تأكيدًا) + اختبارات الجلسة 7 خضراء.

---

## S-04 🟠 P1 — تحقق ملكية المتجر على كل معامل Livewire نموذجي (IDOR)

**الملفات (الأمثلة):**
- `resources/views/livewire/merchant/brands/index.blade.php:72-76,142-146` — `Brand $brand` بلا store check قبل update/delete
- `resources/views/livewire/merchant/categories/index.blade.php` — `Category $category`
- `resources/views/livewire/merchant/options/…` — `ProductOption`
- `resources/views/livewire/merchant/variants/index.blade.php` — `$variant->update` بلا store check
- `resources/views/livewire/merchant/returns/index.blade.php:140,168`
- `app/Domains/Order/Services/ReturnVerificationService.php:46-64,70-109`
- `resources/views/livewire/merchant/orders/index.blade.php:1261,2101-2110,3435-3457,4331-4430`

**القاعدة:** أي دالة `(Model $x)` من Livewire/Volt تُمرَّر النموذج مباشرة يجب أن تبدأ بـ:
```php
abort_unless($x->store_id === currentStoreId(), 403);
```
(أو `->where('store_id', currentStoreId())->findOrFail()` كبديل حتى عند اقتران نموذج).

**أولا الملفات من القائمة أعلاه بأولوية ملكية، ثم كامل `resources/views/livewire/**/*.blade.php` لمراجعة كاملة (ستطرق في رمز مرّور).** استخدم grep:
```
rg "function .*\(\$?(brand|category|option|variant|order|tracking|return|product)" resources/views/livewire
```

---

## S-05 🟠 P1 — رفع الملفات: قواعد mime/type على كل `->store('…','public')`

**الملفات:**
- `resources/views/livewire/merchant/products/form.blade.php:62` — `'images' => []` → أضف `['image','mimes:jpg,jpeg,png,webp','max:2048']` لكل صورة.
- `resources/views/livewire/account/billing.blade.php:154-162` + إضافة قيد `manualProofFile` في قواعد التحقق.
- `app/Domains/Billing/Actions/SubmitManualPaymentAction.php:23` — قيد `$proofFile` قبل `store('billing/proofs','public')`.
- `resources/views/livewire/merchant/store-settings.blade.php:74` — favicon (موجود) — تأكد دمج كل المسارات.
- Livewire التحميل المؤقت: لا `config/livewire.php` منشور — إن أردت خلافة استخدام `#[Validate(['file','image',...])]` في كل calling.

**حماية جانبية:** أضف deny يدوي لـ `storage/app/public/.htaccess` (PHP/HTML/SVG) أو سياسة serve-only عبر Apache/Laravel عند `public/storage`.

**Regressions:** `tests/Feature/Merchant/StoreSettingsTest` وجود؟ — أعد إنشاء/إضافة test رفع ملف `.php` يجب أن يفشل.

---

## S-06 🟠 P1 — دخول بلا حماية (تخمين كلمات مرور)

**الملفات:**
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php:7,28-39` — `use App\Http\Requests\Auth\LoginRequest;` غير مستخدم: استبدل `store(Request $request)` بـ`store(LoginRequest $request)` (يحمل limit 5/دقيقة).
- `app/Http/Controllers/Auth/AdminSessionController.php:27-38` — أضف `throttle:` إلى المسار أو تحقق الـ LoginRequest المنفصلة.
- `routes/auth.php:25,30,20,35-36` — أضف middleware `throttle:5,1` على login/admin-login/register/forgot.

**Regressions:** أعد تشغيل `tests/Feature/Auth/*`.

---

## S-07 🟠 P1 — SSRF عبر `api_base` شركات الشحن + وكيل البطاقة

**الملفات:**
- `resources/views/livewire/merchant/delivery/providers.blade.php:269-299` (حفظ credentials `api_base`)
- `app/Domains/Shipping/Adapters/NoestIntegrationAdapter.php` — `baseUrl()` ~`:618-621`
- `app/Domains/Shipping/Adapters/NoestDeliveryRatesAdapter.php:104-109`
- `app/Http/Controllers/Merchant/DeliveryLabelController.php:45-57` — يمرّر upstream Content-Type كما هو (سم XSS: لا يُصنَّف كصورة/PDF بشكل آمن).

**الإصلاح:**
1. قاعدة تحقق `api_base`: `['required','url']` + allowlist host (https فقط، يمنع RFC1918/127.0.0.1/[::1]/169.254.*).
2. عند `DeliveryLabelController::show`: فرض نوع `image/*`/`application/pdf` في `Content-Type` المرسل (لا نسبة upstream كما هو).
3. نماذج التحقق التعريفية عند `providers.blade.php:269-299` يحدد المنطق.

---

## S-08 🟡 P2 — XSS مخزّن في وصف المنتج (معالجة لاحقة غير مقصودة)

**الملف:** `resources/views/livewire/merchant/products/show.blade.php:112`
**الإصلاح:** استبدل `{!! $product->description !!}` بـ `{!! nl2br(e($product->description)) !!}` (يطابق storefront).

---

## S-09 🟡 P2 — `lang.js` مسار غير منظم + تضخيم ملف ترجمة

**الملف:** `routes/lang.php:7-28`
**الإصلاح:** فرض allowlist `['ar','en','fr','es']` قبل أي `resource_path` (يطابق `LanguageController.php:13`).

---

## S-10 🟡 P2 — ترقية التبعيات (مرحلة لاحقة مع `05-ops`)

- `composer update` الحزم ذات التحذيرات عالية (laravel/framework, guzzlehttp/guzzle, symfony/http-kernel, tiptap, dompdf) ثم `composer audit --locked`.
- npm: `npm update`/upgrade axios, swiper, @vue/server-renderer, @tailwindcss/typography → `npm audit`.
- **لا تُنفَّذ في نفس اللقطة مع إصلاحات الكود** (فصل تغييرات التبعيات عن تغييرات المعنى).

---

## جدول مرجعية

| المهمة | الوثيقة المرجعية |
|---|---|
| S-01 | `docs/livewire-conventions.md` §6 + `00-quality-gates` §1/§6 |
| S-02 | `00-quality-gates` §6 + Logics في `tests/Feature/Merchant/StoreTeamCredentialsEmailTest.php` |
| S-03 | `00-quality-gates` §5 (عزل سياق) + `docs/livewire-conventions.md` §16 |
| S-04 | `docs/audits/2026-10-readiness-audit.md` T3/T8/T9 |
| S-05 | `00-quality-gates` §7 + `DESIGN_SYSTEM` (مصادر الرفع لا تخالف) |
| S-06 | `docs/audits/...` A1 |
| S-07 | `docs/audits/...` A2 + `docs/plans/order-distribution-rules.md` |

---

## قبول هذه المرحلة

- [x] RCE army-test; حسابات قديمة تبقى.
- [x] لا `HasInlineEdit` callable من العميل يصل فعلًا.
- [x] لا إعادة تعيين كلمة مرور حسابات موجودة (addMember) ولا تغيير دون إعادة مصادقة (updateMember).
- [ ] كل معامل Livewire نموذجي يفحص `store_id` (S-04 قادم).
- [x] كامل الصيغة: `php -d memory_limit=-1 vendor/bin/pest` خضراء + `STATUS.md` محدّثة.