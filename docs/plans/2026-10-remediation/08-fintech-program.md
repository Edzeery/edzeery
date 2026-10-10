# 08 — Fintech Program (برنامج الحسابات المالية 38-B/38-C/38-D)

> الهدف: إكمال الفجوات المؤكدة في برنامج المحاسبة (38-C منجزة، الباقي مسارات) مع الحفاظ على العقد «لا بناء جداول دفع/رواتب مؤقتة في Edzeery».
> المصدر: `docs/plans/financial-accounts.md`، `docs/plans/38B-scope-proposal.md`، `docs/audits/2026-10-readiness-audit.md` F1/F2/F3/F4/F5.

---

## F-01 🟠 P1 — `finance_capture_started_at` للمتاجر الجديدة/المملوءة

**الملفات:**
- `database/migrations/2026_10_06_000005_add_finance_capture_started_at_to_store_settings_table.php` — يملأ الحالي فقط.
- `app/Observers/StoreObserver.php` — إنشاء store لا يضبطه.
- `resources/views/livewire/merchant/create-store.blade.php` (~`:116-125`) — `updateOrCreate` بدون `finance_capture_started_at`.

**الإصلاح:**
1. **migration لاحق** يملي متاجر بحقل NULL: `UPDATE store_settings SET finance_capture_started_at = created_at WHERE finance_capture_started_at IS NULL;`
2. عند إنشاء store: `store_settings` المُنشأة تُعطى `finance_capture_started_at = now()` (في الـ Observer أو في `createStore` الفعل).
3. حقل في `finance:capture-health` يُبلّغ "capture since unbounded" كحالة يجب تحويلها إلى `-1` (يُشال من exit النهائي).
4. دالة جديدة «بدأ الكشف» لا تسمح NULL.

**قبول:** على dev DB جديد (متجر أنشئ بعد الـ migration) يراعي عقب `capture-health` أن all stores لها start; audit يشير لغير ذلك.

---

## F-02 🟠 P1 — ملكية الديون تعادل تخصيص العلاقة

**الملف:** `docs/plans/financial-accounts.md:31-38` (جدول الملكية)

**القرار المقترح (يُعرض على المستخدم):** الديون التجارية (merchant counterparty debts) **تملكها Edzeery**؛ finance-manager يستلم فقط بقايا/تجميعات. يُحدَّث الجدول.

**الإصلاح:**
- تحديث الجدول في `financial-accounts.md` بإضافة صف `Debts (merchant-facing)` → Owner = **Edzeery**, Notes.
- لا كود الآن — قرار التوثيق فقط.

---

## F-03 🟡 P2 — stage للحالات المخصصة

**الملف:** `app/Services/Stores/StoreStatusService.php:168` (`addStatus` → `stage = 'other'`) + `finance:capture-health` (اختبار 7)

**القرار**: ليس صناعة دالة الآن — وثّق في `financial-accounts.md` أن `stage='other'` ستُسقط من default accrual. إن Kاجتماع واجبه اضبط `create-status` ليتطلب stage mapping إجباري.

---

## F-04 🟡 P2 — اجتياز ببوابة صحية في كل إنتاج بيانات

من تدقيق: `finance:capture-health` خرج FAILURE على demo (8 متأخرة بلا confirmed_at؛ 2 بلا delivered_at؛ 1 returned؛ 31 history NULL source; 7 trackings بلا creator; 2 other stage).

**الإصلاح (لم يبدأ — مرحلة النشر):**
1. لا بدء pet-program في 38-B تجاه متاجر تكون health غير نظيفة (أو تمرير name_interop للقائمة).
2. data repair مؤجلة → تُسجَّل كقسم في `STATUS` وليس في migration.
3. أي حقل جديد يُستخدم في الحسابات يجب أن يدخل `capture-health` في نفس اللقطة.

---

## F-05 🟡 P2 — مراجعة جشعة legacy `order_status_histories` (38-A الفجوات)

- إكمال `source`/`from_status` لقاعدة القيم (معظمها NULL قبل 38-C). استبدل completed migration «الحامل» بالرصيد؟ — **هذا قرار ينتظر مؤلف برنامج مالی**؛ الوثيقة تشير أن 38-D ترحيل legacy يتضمنه. سجل هنا قاعدة «كل ترحيل سيوّلد جرد خامل محدود».

---

## مرجعية

| المهمة | الوثيقة |
|---|---|
| F-01 | `financial-accounts.md` §38-C + migrations 000005 |
| F-02 | `financial-accounts.md` §ownership |
| F-03 | `financial-accounts.md` §38-B accruals |
| F-04 | `docs/audits/...` (finance output) |
| F-05 | `38B-scope-proposal.md` |

## قبول المرحلة

- [ ] متجر جديد → `capture since` محدد.
- [ ] جدول ملكية محدّث (debts).
- [ ] قرار موثق لـ stage custom; لا حقل مؤقت جديد.
- [ ] `finance:capture-health` يعود 0 على demo **بعد إصلاح بيانات موضح** (في مرحلة النشر لا الآن).