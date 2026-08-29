# Orders System — Master Refactor & Fix Plan
> **يوحّد ويحلّ محل:** `TodoRefactor.md` (2026-08-26) + `errorTofix.md` (2026-08-25/26).
> الأولوية للأحدث عند التعارض: `TodoRefactor.md` هو المرجع الأساسي لأنه الأحدث محتوى وهيكلة (Phase Status Table)،
> ودُمجت فيه كل البنود المفتوحة (غير المنفَّذة) من `errorTofix.md` كملاحظات إضافية ضمن المراحل المناسبة.
> `Todos.md` (2026-08-17) و`errorsTodo.md` (2026-08-20) **لم يُدمجا** لأنهما يغطيان نطاقًا مختلفًا تمامًا
> (بنية الألوان/Filament على مستوى المشروع، وتدقيق الواجهة الأمامية للمتجر/الصفحة الرئيسية على التوالي) —
> لا تعارض بينهما وبين هذا الملف، لكنهما يبقيان مرجعين مستقلين قائمين بذاتهما.
>
> **الحالة عند الدمج (2026-08-28):** Phases 1–8, 12–18 و R1–R7 منفَّذة فعليًا (✅ تم التحقق بفحص مباشر للكود).
> Phases 9–11 لم تبدأ بعد (تحقّق: لا توجد أي ملفات فرعية تحت `resources/views/livewire/merchant/orders/` عدا `index.blade.php` نفسه، 2796 سطرًا).

---

## جدول حالة المراحل (بعد الدمج)

| Phase | العنوان | الحالة | ملاحظة الدمج |
|---|---|---|---|
| 1–8 | تتبّع الشحن + إصلاحات N+1/dead state/mystatuskit | ✅ DONE | — |
| 9 | استخراج مودال الإنشاء/التعديل | ⬜ TODO | + M7 من errorTofix (submitEdit لا يمر عبر OrderService) + توحيد الـ spinner |
| 10 | استخراج شريط الإجراءات الجماعية (Bulk Actions) | ⬜ TODO | + M10 من errorTofix (loading skeleton) |
| 11 | استخراج بوابة الفلاتر (Filter Portal) | ⬜ TODO | + M8 من errorTofix (positionFilter اليدوي) |
| 12–18 | tracking UI + bulkSendToCarrier + مودال الإنشاء الجديد | ✅ DONE | — |
| R1–R7 | وحدة التحقق من المرتجعات | ✅ DONE | — |
| — | M2 (errorTofix): تخزين مؤقت لـ `filtered_amount` | ✅ محلول ضمنيًا | تمت إزالة `filtered_amount` بالكامل في Phase 6 — لا حاجة لتخزين مؤقت لشيء غير موجود |
| **19** | **أداء صفوف الجدول + إزالة كود JS المحقون** | ⬜ TODO جديد | من التدقيق الحالي (2026-08-28) |
| **20** | **تجاوب الشاشات + تنظيف رموز الوضع الليلي (Apple HIG)** | ⬜ TODO جديد | من التدقيق الحالي (2026-08-28) |

### بنود errorTofix.md المتبقية خارج نطاق هذا الملف (لم تُطوَ ضمن أي Phase أعلاه، تبقى مرجعًا منفصلاً)
- L1: حذف حالة `canceled` المكرّرة (تحتاج ترحيل بيانات) — مؤجّل
- L2: أعمدة `product_name`/`variant_name` كصورة ثابتة على `OrderItem` — تغيير سكيمة، مؤجّل
- L5: `ShippingCostCalculator` fallback يتطلّب إعدادًا صريحًا — منخفض الخطورة، مؤجّل
- FE1–FE6 (تحسينات مستقبلية): حماية COD، إشعارات واتساب، تصدير، تحديث جماعي متعدد الحالات، حالات إضافية، إدارة حالات مخصّصة — **لا تُنفَّذ الآن**

---

## Phase 9 — استخراج مودال الإنشاء/التعديل (+ M7)

**الهدف:** فصل نموذج إنشاء/تعديل الطلب (اختيار المنتجات، الملخّص، الخصم) إلى مكوّن Blade منفصل، وتوحيد `submitCreate`/`submitEdit` عبر `OrderService` بدل التحديث المباشر على الموديل.

**الملفات:**
- جديد: `resources/views/livewire/merchant/orders/order-form-modal.blade.php`
- تعديل: `index.blade.php` (إزالة الكتلة المنقولة، استدعاء المكوّن)
- تعديل: `app/Domains/Order/Services/OrderService.php` (إضافة `updateOrder(Order $order, array $data): Order` إن لم توجد)

**ما ينتقل:**
- `form`, `formProductResults`, `formProductView`, `formSelectedProduct`, `formSelectedItems`, `showCreateModal`, `showEditModal`, `editingOrderId`, `editProviders`, `editDesks`
- `openCreateModal`, `openEditModal`, `submitCreate`, `submitEdit`, `addFormItem`, `removeFormItem`, `syncFormSelectedItems`, وكل ما يخص اختيار المنتج/المتغيّر
- Blade template للمودال بالكامل (سطر ~1974 فصاعدًا: `orderProductPicker`)

**إصلاح M7 (errorTofix.md):** استبدل `$order->update([...])` في `submitEdit` (السطر 997 حاليًا) باستدعاء `app(OrderService::class)->updateOrder($order, $payload)` — نفس منطق التحقق والحظر (شُحن/مغلق) يبقى في المكوّن، لكن التحديث الفعلي يمر عبر الخدمة لضمان اتساق مستقبلي مع أي منطق مصاحب (إشعارات، مخزون).

**إصلاح تكرار الـ spinner:** أنشئ `resources/views/components/edz/spinner.blade.php`:
```blade
@props(['size' => 'w-4 h-4'])
<svg x-cloak wire:loading {{ $attributes->merge(['class' => "edz-spinner {$size}"]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" /></svg>
```
واستبدل كل نسخة من الـ 23 نسخة المكررة (`grep -n "M12 2v4M12 18v4M4.93 4.93" index.blade.php`) بـ `<x-edz.spinner wire:target="..." />` مع الحفاظ على `wire:target` الصحيح لكل زر.

**Scope guard:** لا تغيّر منطق التحقق (Validator rules) ولا قواعد حظر التعديل بعد الشحن. لا تلمس `submitCreate`'s customer creation logic. لا تُدخل `dark:` جديدة — استخدم `surface-*`/`ink-*` فقط.

**Acceptance criteria:**
- [ ] المودالان (إنشاء/تعديل) يعملان بنفس السلوك الحالي بالضبط
- [ ] `submitEdit` يمر عبر `OrderService::updateOrder()`
- [ ] لا يوجد أي spinner SVG مكرر حرفيًا داخل `order-form-modal.blade.php` — الكل عبر `<x-edz.spinner>`
- [ ] اختبارات الطلبات الحالية (Feature tests) تمر دون كسر

---

## Phase 10 — استخراج شريط الإجراءات الجماعية (+ M10)

**الهدف:** فصل شريط التحديد الجماعي إلى مكوّن، وإضافة حالة تحميل (skeleton/disabled) أثناء العمليات الجماعية.

**الملفات:**
- جديد: `resources/views/livewire/merchant/orders/bulk-actions-bar.blade.php`
- تعديل: `index.blade.php`

**ما ينتقل:** `selectedOrders`, `selectAll`, `showBulkBar`, `toggleSelectAll`, `toggleSelectOrder`, `clearSelection`, `bulkAssignAgent`, `bulkSendToCarrier`, `bulkDelete` (السطور 54–470 تقريبًا حسب الفحص الحالي).

**إصلاح M10:** أضف `wire:loading.attr="disabled"` + `wire:target="bulkAssignAgent,bulkSendToCarrier,bulkDelete"` على شريط الإجراءات كاملاً، مع مؤشر تحميل مرئي (`<x-edz.spinner>` من Phase 9) بدل ترك الأزرار قابلة للنقر المتكرر أثناء التنفيذ.

**Scope guard:** لا تغيّر منطق `bulkSendToCarrier` (معالجة فردية per-order موثّقة ومقصودة — batching جماعي عبر queue هو بند مستقبلي منفصل، غير مطلوب هنا).

**Acceptance criteria:**
- [ ] التحديد الجماعي، تعيين الوكيل، الإرسال للناقل، الحذف الجماعي — كلها تعمل كالسابق
- [ ] الأزرار تُعطَّل مرئيًا أثناء التنفيذ (لا نقر مزدوج ممكن)
- [ ] بعد أي إجراء جماعي: القائمة تُحدَّث والتحديد يُفرَّغ

---

## Phase 11 — استخراج بوابة الفلاتر (+ M8)

**الهدف:** فصل شريط الفلاتر إلى مكوّن، واستبدال حساب الموضع اليدوي (`getBoundingClientRect` المكرر) بمكوّن Alpine واحد قابل لإعادة الاستخدام.

**الملفات:**
- جديد: `resources/views/livewire/merchant/orders/filter-portal.blade.php`
- جديد: `resources/js/components/dropdown-position.js` (Alpine.data مسجَّل في `panel.js`، يحل محل كل نسخة من `positionFilter`/`getBoundingClientRect` اليدوية)
- تعديل: `index.blade.php`, `resources/js/panel.js`

**ما ينتقل:** `filters` (16 مفتاحًا)، `setFilter`, `clearFilters`, `toggleStatusFilter`, `loadFilterCities`، وكل قوائم `x-data="{ open:false }"` الفردية للفلاتر (Source, Delivery Type, إلخ — الأسطر 1234–1466 تقريبًا).

**إصلاح M8:** استبدل الدالة المضمّنة `positionFilter(e) { ... getBoundingClientRect ... }` (المعرَّفة في `x-data` الجذري بالسطر 1144) بمكوّن Alpine مسمّى (مثلاً `Alpine.data('dropdownPosition', ...)`) يُستخدم عبر `x-data="dropdownPosition()"` على كل قائمة منسدلة، بدل إعادة كتابة نفس منطق الموضع كسلسلة JS متكررة.

**Scope guard:** المكوّن عرض وحالة فقط — الاستعلام الفعلي يبقى في `index.blade.php` عبر حدث `filters-changed` تستمع له.

**Acceptance criteria:**
- [ ] كل الفلاتر (wilaya, status, agent, amount, date, product, source, delivery, provider) تعمل
- [ ] القوائم المنسدلة تتموضع صحيحًا في كل من RTL/LTR وعند القرب من حافة الشاشة
- [ ] لا يوجد `getBoundingClientRect` مكرر يدويًا أكثر من مرة واحدة في كامل شجرة orders/*

---

## Phase 19 — أداء صفوف الجدول + إزالة كود JS المحقون (جديد)

**الهدف:** إزالة تكرار منطق Alpine داخل حلقة `@foreach` الخاصة بصفوف الجدول (القائمة المنسدلة لتغيير الحالة + تأكيد الحذف)، وتقليل حمولة `orders` كخاصية Livewire عامة.

**الملفات:**
- جديد: `resources/js/components/order-row-actions.js` (مسجَّل عبر `Alpine.data('orderRowActions', ...)` في `panel.js`، بنفس نمط `orderProductPicker` الموجود مسبقًا)
- تعديل: `index.blade.php` (صف الجدول، السطور ~1695–1811 حاليًا)
- تعديل: قسم `loadOrders()` في نفس الملف

**التفاصيل:**
1. استبدل `x-data="{ open:false, top:0, left:0 }"` + منطق `getBoundingClientRect` المكرر لكل صف بـ `x-data="orderRowActions()"` تستقبل `orderId` كـ prop، وتُعرِّف `openStatusMenu(event)` و`confirmDelete(orderId, orderNumber)` كدوال معرَّفة مرة واحدة في ملف JS.
2. استبدل IIFE `x-on:click.prevent="(async () => {...})()"` الخاص بالحذف (السطر 1801) باستدعاء دالة مسمّاة من نفس المكوّن (`@click="confirmDelete('{{ $orderId }}', '{{ $order['number'] }}')"`)
3. طبّق `<x-edz.spinner>` (من Phase 9) على كل الـ spinners المتبقية داخل صف الجدول.
4. **تقليل حمولة `orders`:** أزل من `$arr` في `loadOrders()` أي مفتاح غير مستخدم فعليًا في العرض الحالي للأعمدة (تحقّق بمطابقة كل مفتاح في `$arr` مع الأعمدة المفعّلة فعليًا في `visibleColumns` + تفاصيل التوسعة)، ووثّق أي حقل أُبقي عمدًا للتوسعة المستقبلية.

**Scope guard:** لا تغيّر منطق `availableTransitions()` ولا استعلامات `loadOrders()` الأساسية (eager-loading سليم بالفعل، تم التحقق). لا تُدخل N+1 جديدة.

**Acceptance criteria:**
- [ ] القائمة المنسدلة لتغيير الحالة تعمل بنفس السلوك (تموضع صحيح، إغلاق عند النقر خارجها)
- [ ] تأكيد الحذف عبر `EdzSwal.confirmDelete` يعمل كالسابق
- [ ] عدد نسخ Alpine المُنشأة لكل صف يبقى كتلة واحدة معرَّفة مرة عبر `Alpine.data`، بدل تكرار الكود الخام
- [ ] فحص Livewire payload (DevTools Network) يُظهر تقليصًا ملموسًا في حجم الاستجابة عند تفاعلات لا تمسّ `orders`

---

## Phase 20 — تجاوب الشاشات + تنظيف رموز الوضع الليلي (Apple HIG) (جديد)

**الهدف:** معالجة غياب أي نقاط توقف (`md:`, `lg:`, `xl:`) في الملف بالكامل (0 استخدام حاليًا)، وإزالة الاستخدام الزائد لبادئة `dark:` (52 موضعًا) مع توكنات `surface-*`/`ink-*` التي تتبدّل تلقائيًا عبر `.dark` في `_tokens.scss`.

**الملفات:** `index.blade.php` فقط في هذه المرحلة (النطاق محصور بملف الطلبات).

**التفاصيل:**
1. **تنظيف `dark:`:** لكل `dark:bg-ink-XXX`, `dark:border-ink-XXX`, `dark:hover:bg-ink-XXX` مرتبط بتوكن دلالي (`surface`, `ink`, `border`) — احذف البادئة `dark:` بالكامل، أبقِ فقط الكلاس الأساسي (لأن المتغيّر يتبدّل تلقائيًا). لا تلمس أي `dark:` مرتبطة بألوان Tailwind الخام غير الدلالية (`gray-*` مباشرة) — هذه استُثنيت صراحة في `errorsTodo.md` (Session 4: "verified valid TW3 utilities") ولها نمط مختلف مقصود (M2 في نفس الملف: "Dual dark mode system → SKIPPED architectural").
2. **عرض بطاقات للجوال (Apple Adaptive Layout):** أضف عرضًا بديلاً (`lg:hidden`) لصفوف الجدول كبطاقات مكدّسة على الشاشات الصغيرة (رقم الطلب، الزبون، الحالة، المبلغ كحقول رئيسية + إجراءات كأزرار كبيرة قابلة للمس)، والجدول الكامل الحالي يُعرض فقط من `lg:` فصاعدًا (`hidden lg:block`).
3. راجع `frontend-design` skill قبل التنفيذ لضمان الالتزام بمعايير التصميم المعتمدة في المشروع.

**Scope guard:** لا تُنشئ أي مكوّن Blade جديد لعرض البطاقات إن كان `<x-edz.card>` أو مكافئ موجودًا بالفعل — تحقّق أولاً عبر `grep -rn "x-edz.card" resources/views/components/`. لا تغيّر منطق الأعمدة القابلة للتفعيل/الإخفاء — يبقى سلوكها كما هو على الشاشات الكبيرة فقط.

**Acceptance criteria:**
- [ ] الصفحة قابلة للاستخدام كاملاً على عرض 375px (iPhone SE) دون تمرير أفقي مفروض
- [ ] لا يوجد أي `dark:` زائد على توكن دلالي متبدّل تلقائيًا (تحقّق: `grep -c "dark:" index.blade.php` ينخفض من 52 إلى ما يقارب الصفر باستثناء الحالات المستثناة صراحة)
- [ ] لا فرق بصري في الوضعين الفاتح/الداكن عن الحالة قبل التعديل
