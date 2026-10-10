# 02 — Auth & Session (مصادقة/جلسات/2FA)

> الهدف: دخول آمن بلا تخمين، جلسات تُبطل عند تغيير كلمة المرور، و2FA على اللوحة.
> المصدر: `docs/audits/2026-10-readiness-audit.md` A10/A11/A33/A1.
> البوابات: `00-quality-gates.md` §6.

---

## A-01 🟠 P1 — تسجيل الدخول (تاجر + أدمن) بلا تقييد عنيف

الملفات والمراجع:
- `routes/auth.php:25` (login) و`:30` (admin login) — أضف middleware `throttle:5,1`.
- `routes/auth.php:20` (register) و`:35-36` (forgot/reset) — `throttle`.
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php:7` — استرجاع `LoginRequest` المهمَل: غيّر التوقيع `store(Request …)` إلى `store(LoginRequest …)` وانزع `Auth::attempt` الداخلي المعتمد على التحقق اليدوي.
- `app/Http/Controllers/Auth/AdminSessionController.php:27-38` — إما استخدم عمل LoginRequest منفصل أو أضف «أو الساعة/الدالة» صريحًا (اختيارك: لا تكمل وتؤجل).

**قبول:** تفتح Flask أداة لـ 6 محاولات في 60 ثانية → 429 أو رسالة `throttle`. إعادة ضغط 0 في الدقيقة التالية يعمل.

---

## A-02 🟠 P1 — إبطال الجلسات الأخرى عند تغيير كلمة المرور

**الملف:** `routes/web.php:9` + `bootstrap/app.php`
**الإصلاح:** أضف middleware `Illuminate\Session\Middleware\AuthenticateSession` إلى مجموعة المتاجر (أو إلى `bootstrap/app.php:31-33` append في مجموعة web) — يكون `AuthenticateSession` فقط للمتاجر/اللوحة. إن أردنا جزئية: أضفه كـ middleware داخل `bootstrap/app.php` لـ `web` مجموعة بالكامل ثم تخصص المجموعات الحساسة بالأولوية.
**تحقق:** `PasswordController`/`SecurityController` (`app/Http/Controllers/Account/SecurityController.php:18`) — بعد تحديث كلمة مرور، الجلسات اللاحقة تُلغى.
**Regressions:** `tests/Feature/Auth/Password*` — تأكيد أن إعادة `login` تتبع بعد تحديث.

---

## A-03 🟠 P1 — دور «الموقع» للموظفين الانتهاء: اثنان من لوحة دخول لا يملكه (أدوار mismatch)

**الملف:** `app/Models/User.php:208-217` (مجموعة أدوار `isPlatformStaff`) مقابل `SuperAdminPanelProvider.php:63` (Guard).
**الإصلاح:** اجعل القائمتين متماثلتين عبر ضع في وظيفة واحدة (`User::staffRoles()` أو const). أزل `dd()` المعلّق `User.php:163-164`.
**قبول:** لا ملفّات زائدة؛ add test يثبت أن `support_agent` لا يدخّل اللوحة.

---

## A-04 🟡 P2 — 2FA/TOTP على اللوحة ولوحة المنصة

**النطاق (تحقّق أولًا، لا تنفذ):**
- أدخل `pragmarx/google2fa-laravel` أو `laravel/fortify`? — قبل حسم اختر خيار وانسق مع `00-quality-gates`. لا نورد بالمكتبة أي إصلاح دون موافقة.
- الهدف المدروس: على كل دخول تاجر/أدمن `Web` + لوحة `SuperAdmin`. حفظ نشط.

---

## A-05 🟡 P2 — سجلّ دخول (أمان تنظيفي)

**الملف:** تقريبًا في `app/Support` أو عبر `activity()`
**الإصلاح:** سجّل `Login` / `Logout` / `FailedLogin` عبر `activity()->event('...')` على مدعول `\App\Models\User` في `AuthenticatedSessionController`/`AdminSessionController`.
**ML:** `event` لتفادي الأنماط المهجّنة.

---

## نطاق إضافي: تقوية استعادة كلمة المرور (Password Reset Hardening)

> **قرار المستخدم (2026-10-10):** إبقاء تدفق «رابط البريد» الحالي وتقويته — **لا OTP** (بريد/SMS) و**لا تعديل schema** (أُسجِّل هنا؛ لا تنفيذ كود قبل موافقة لاحقة).
> المصدر: فحص هذه الجلسة + `docs/audits/2026-10-readiness-audit.md` A1/A10/A11/A13.
> واقع الحالي: التوكن مُهشّر at-rest (bcrypt) في `password_reset_tokens`، صالح 60د، يُستهلك مرة واحدة، وthrottle إعادة إنشاء 60ث لكل مستخدم (`config/auth.php:96-97`) — نبقي هذه المكاسب.

### PR-01 🟠 P1 — Rate limiting لكل نقاط الاستعادة/الدخول
- **الملفات:** `routes/auth.php:35-42` (forgot/reset)، `:25,30` (login/admin)، `:20` (register).
- **الإصلاح:** limiters مسمّاة في `AppServiceProvider` (مفتاح = IP + بريد مُهشّر `Str::lower(email)`) ثم `->middleware('throttle:...')`: forgot/reset حد أوضح (مثال 5/دقيقة + 3/ساعة لكل بريد)، login/admin 5/دقيقة.
- **قبول:** المحاولة السادسة → 429/رسالة throttle؛ الدقيقة التالية تعمل.
- **↔ يوافق A-01 و S-06.**

### PR-02 🔴 P1 — منع User Enumeration (ثغرة مؤكدة الآن)
- **الملف:** `app/Http/Controllers/Auth/PasswordResetLinkController.php:39-42`.
- **الإصلاح:** رد عام واحد دائمًا (`passwords.sent`) بصرف النظر عن `INVALID_USER`/`RESET_THROTTLED`؛ تسجيل الحالة داخليًا فقط بلا فرق زمني ملحوظ.
- **قبول:** بريد غير موجود → 200 + status عام بلا `error`؛ مطابق تمامًا لبريد موجود (اختبار Feature).

### PR-03 🟠 P1 — تقليل تسرّب التوكن (URL/logs)
- **الملفات:** `resources/views/auth/reset-password.blade.php:10`، توجيه `.env.example:51`.
- **الإصلاح:** ترويسة/ميتا `Referrer-Policy: no-referrer` على صفحة الاستعادة؛ عدم تسجيل الرابط الكامل؛ توثيق أن الإنتاج يستخدم mailer حقيقيًا لا `log`.
- **قبول:** استجابة reset تحمل `Referrer-Policy: no-referrer`؛ لا توكن في `laravel.log`.

### PR-04 🟠 P1 — إبطال الجلسات الأخرى بعد الاستعادة (= A-02)
- **الملفات:** `bootstrap/app.php:31-33`، `app/Http/Controllers/Auth/NewPasswordController.php:44-51`.
- **الإصلاح:** أضف `Illuminate\Session\Middleware\AuthenticateSession` لمجموعة `web`؛ فتُبطَل الجلسات الأخرى تلقائيًا عند تغيير `password_hash`.
- **قبول:** جلسة تاجر ثانية تُخرَج بعد الاستعادة من جلسة أولى.

### PR-05 🟠 P2 — Secure logging/تدقيق بلا بيانات حساسة (= A-05/A10 جزئيًا)
- **الإصلاح:** أحداث `PasswordResetRequested`/`Succeeded`/`Failed` + `Login`/`FailedLogin` عبر `activity()` {بريد مُقنّع، IP} — ممنوع التوكن/كلمة المرور في السجل.
- **قبول:** الأحداث تُسجَّل؛ لا بيانات حساسة في اللوج.

### PR-06 🟡 P2 — توثيق + اختبار هشير التوكن (بدل OTP)
- **القرار:** لا مكتبة ولا جدول OTP.
- **الإصلاح:** اختبار يثبت: (١) `password_reset_tokens.token` ≠ التوكن المرسل (bcrypt)، (٢) استهلاك single-use، (٣) الانتهاء بـ TTL.
- **قبول:** اختبار واحد يغطي الثلاثة.

### PR-07 🟡 P2 — منع Race Condition
- **الملف:** `NewPasswordController::store` + broker.
- **الإصلاح:** `DB::transaction` + `lockForUpdate` على المستخدم/سجل التوكن، أو `Cache::lock("password-reset:{$email}")` لتسلسل الطلبات.
- **قبول:** اختبار متزامن متسلسل → نجاح واحد فقط.

### PR-08 🟡 P2 — سياسة كلمة مرور أقوى
- **الملفات:** `NewPasswordController.php:36`, `PasswordController.php:20`, `RegisteredUserController.php:37`.
- **الإصلاح:** `Password::defaults(fn () => Password::min(12)->mixedCase()->numbers()->symbols())` (+ `uncompromised()` مُشروطًا بالبيئة/الشبكة).
- **قبول:** كلمة ضعيفة تُرفض؛ الاختبارات تُحدَّث.

### PR-09 🟡 P2 — إشعار أمني بعد التغيير
- **الإصلاح:** بريد «تم تغيير كلمة مرورك» بعد نجاح الاستعادة/التحديث (Notification جديد).
- **قبول:** البريد يُرسَل (تأكيد عبر `Notification::fake`).

### PR-10 🟡 P2 — الهوية/الاستجابة/RTL/a11y (يوافق 07)
- **الملفات:** `resources/views/auth/forgot-password.blade.php`, `reset-password.blade.php` (+ `x-auth.card`).
- **الإصلاح:** `<ion-icon>` → `<x-edz.icon>` (قرار `STATUS.md:34`)؛ فحص 360/768/1024/1440؛ RTL/dark عبر التوكنات؛ `autocomplete`/`aria-label`/زر إظهار كلمة المرور.
- **قبول:** لا ion-icons في الصفحتين؛ لا hex خام؛ لقطات 4 أحجام في `STATUS.md`.

### PR-11 🔴 P1 — اختبارات regression + تحديث STATUS
- **الملف:** توسيع `tests/Feature/Auth/PasswordResetTest.php` + ملف throttle/enumeration جديد.
- **قبول:** كل بنود PR-01..PR-10 مغطاة؛ الصيغة خضراء.

---

## مرجعية

| المهمة | الوثيقة |
|---|---|
| A-01 | `00-quality-gates` §8 + `app/Http/Requests/Auth/LoginRequest.php` |
| A-02 | `00-quality-gates` §5 |
| A-03 | `docs/audits/...` A33 |
| A-04 | موافقة إضافية قبل أي إضافة مكتبة |
| PR-01..PR-09 | `docs/audits/...` A1/A10/A11/A13 + `00-quality-gates` §5/§8 |
| PR-10 | `07-design-system-apple` + `DESIGN_SYSTEM.md` + `00-quality-gates` §1/§2/§4/§5 |

## قبول المرحلة

- [ ] login/admin login/register/forgot/reset throttled.
- [ ] تغيير كلمة المرور يبطل جلسات أخرى.
- [ ] سجل Logs.SESSION موجود.
- [ ] الصيغة خضراء.
- [ ] (PR) لا user enumeration في forgot-password.
- [ ] (PR) لا تسرّب توكن للـ Referer/logs.
- [ ] (PR) التوكن مُهشّر/أحادي الاستخدام/ينتهي بـTTL (اختبار).
- [ ] (PR) صفحات الاستعادة بهوية Apple + استجابة 4 أحجام + RTL/dark + a11y.