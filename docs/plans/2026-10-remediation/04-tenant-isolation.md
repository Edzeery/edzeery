# 04 — Tenant Isolation (عزل المتاجر)

> الهدف: سياق المتجر يُحسم من مصادر موثوقة فقط (دخول الويب بعد تحقق العضوية / API عبر X-Store-Id فحص عضوية)، وكل نموذج `store_id` داخل نطاق واحد.
> المصدر: `docs/audits/2026-10-readiness-audit.md` T3/T4/T5/T6/T8/T9/A16.
> البوابات: `00-quality-gates` §5.

---

## T-01 🔴 P0 — إغلاق «جلسة قابلة للتسمم» (نفّذ سابقًا في 01-S-03 — كرر مرجعًا)

الملفات:
- `app/Http/Middleware/Merchant/Store/ResolveStoreFromRoute.php:27-33`
- `app/Http/Middleware/Merchant/Store/EnsureStoreMembership.php:19-27`
- `app/Support/StoreResolver.php:20-26`
- `app/Http/Middleware/ResolveStoreFromSubdomain.php` (session write على أي subdomain)

يمنع:
1. كتابة الجلسة قبل تحقق العضوية.
2. `StoreResolver` فرع الجلسة يفحص `stores()->exists()`.
3. `EnsureStoreMembership` يُمحى `current_store_id` عند رفض.
4. subdomain-storefront session لا يُكتب إلا للعضو.

**accept:** محاكاة هجوم: `session(['current_store_id'=>storeB])` قبل أي يحتاج livewire للمتجر A → فشل.

---

## T-02 🟠 P1 — `StoreScope` على كل نموذج `store_id` متبقي

**نموذج الحاضر:** `app/Scopes/StoreScope.php` مسجّل على 3 نماذج فقط (Product, Debt, DebtPayment).

**القائمة المستهدفة (من فحص التوثيق):** Brand, Category, ProductVariant, ProductOption, Order, OrderItem, OrderTracking, Invoice, Payment, InventoryMovement, Status, ShippingProvider, StopdeskPoint, DeliveryRider, Customer, Returns…

**التوصية:** بدل تعديل كل سكوب file inline (مخاطرة)، اعتماد pattern موحّد:
```php
// في نموذج النطاق: booted() { static::addGlobalScope(fn ($b) => $b->where('store_id', currentStoreId())); }
```
**حدود وثائقة إلزامية:**
- النماذج التي تحتاج عبر all stores (التقارير/الإدارة) تعرض scope بها أو تستخدم `withoutGlobalScope`.
- `store_id` nullable (نطاق تجاري، templates) تحتاج `where(fn $q => $q->where('store_id', $id)->orWhereNull('store_id'))`.

**تنبيه:** لا تُضف scope على النماذج التي يكتبها النظام لقطعة النظام (مثل jobs بمستودع `<store_id>` صريح) دون review — أو يتحول كل `findOrFail` الموجود إلى أمان. أدرج قائمة كاملة قبل إضافة.

---

## T-03 🟠 P1 — API: سياق host-subdomain بلا عضوية

**الملفات:** `app/Support/StoreResolver.php:47-65`، `routes/api.php:18-19`، `app/Http/Middleware/Api/EnsureStoreContext.php:31-38`

**الإصلاح:**
1. في `resolveFromSubdomain`: تأكد أن المتصل `auth()->user()?->stores()->where('id',$store->id)->exists()` عندما يطرح المتجر (delete فرع `api/*`).
2. `EnsureStoreContext`: أضف تحققًا موحدًا — أي طلب API بلا `X-Store-Id` على `api/*` → 422/401 (بدل الاعتماد على host).
3. `StoreResolver::resolve()` فرع api: الحفاظ على `X-Store-Id` كمسار أساسي؛ no fallback إلى subdomain.

---

## T-04 🟠 P1 — syncRoles/syncPermissions عام يتجاوز المتاجر

**الملف:** `app/Services/Stores/StoreTeamService.php:127,132,161-162` (tri ازالة) + `app/Helpers/helpers.php:81,169` (fallback عام بـ `hasRole`/`can` merchant).

**الإصلاح:**
1. توقف إعادة تعيين `$user->syncPermissions/syncRoles` في تدفقات المتجر؛ بدلًا: فقط `$membership->syncPermissions()` كل ما يلزم.
2. `hasStoreRole`/`canStore` fallback: عندما العضوية بلا فيها custom permissions (مثل `:169`) احذف الاستدعاء على الغضب للمؤسّسات؛ إما رفض (الافتراضي) أو اختراع سلوك معتمد بإذن من المسؤول.
3. أعد اختبارًا: متجران، عضوية A تعيين custom، ثم تعديل في B → لا تؤثر على A.

---

## T-05 🟠 P1 — النموذج «المرجَع عبر ID» في returns/orders (انظر S-04)

- `resources/views/livewire/merchant/returns/index.blade.php:140,168`
- `app/Domains/Order/Services/ReturnVerificationService.php:46-64,70-109`
- `resources/views/livewire/merchant/orders/index.blade.php:4331-4430` (rules exists:+whereIn غير مجهزة بstore)

**الإصلاح:** كل `findOrFail` في هذه المسارات يضيف `->where('store_id', currentStoreId())`؛ rules `exists:...` تضيف `,store_id,currentStoreId()`.

---

## T-06 🟡 P2 — أمان مستوى المفاتيح الفرعية: `DB::table('statuses')` العمومي

**الملف:** `app/Domains/.../OrderStatusIdMap.php:27` — يستخدم `DB::table('statuses')` العام بينما `Status` له `store_id`.

**الإصلاح:** أي بحث عن حالة تم تغيير إلى scope المتجر أو custom status قد نعم بتوليد من الخريطة بالتجار context.

---

## قبول المرحلة

- [ ] اختبارات stra้ght عبر المتاجر: شدّ test مثل `tests/Feature/Tenant/*` موجود — نضيف 5 هجمات من التدقيق (فقرة «the 5 cross-tenant attacks attempted in the head» في `docs/audits/2026-10-readiness-audit.md`).
- [ ] كل cliqueارب يعطي 403/404 لا بيانات.