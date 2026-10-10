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

## مرجعية

| المهمة | الوثيقة |
|---|---|
| A-01 | `00-quality-gates` §8 + `app/Http/Requests/Auth/LoginRequest.php` |
| A-02 | `00-quality-gates` §5 |
| A-03 | `docs/audits/...` A33 |
| A-04 | موافقة إضافية قبل أي إضافة مكتبة |

## قبول المرحلة

- [ ] login/admin login/register/forgot throttled.
- [ ] تغيير كلمة المرور يبطل جلسات أخرى.
- [ ] سجل Logs.SESSION موجود.
- [ ] الصيغة خضراء.