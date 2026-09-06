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
>
> **آخر تحديث (2026-09-01):** Phases 9, 10, 11, 19, 20, 21, 22, 23 مكتملة ومنفَّذة فعليًا (تحقّق: 56 اختبار Order ناجحة، `view:cache` و`npm run build` سليمة). تفاصيل التنفيذ في أقسام كل Phase أدناه.
>
> **ملاحظة معمارية حاسمة (أُضيفت 2026-08-29، تؤثر في الأداء مباشرة، وليست مسألة تنظيم كود فقط):**
> كلمة "sub-component" في Phases 9/10/11 تعني **`@include` Blade partial ضمن نفس نسخة الـ Volt component ونفس `wire:id`** —
> **وليس** مكوّن Livewire منفصل (`<livewire:merchant.orders.xxx />`). الدليل: النمط المعتمد فعليًا في المشروع
> (`resources/views/livewire/merchant/storefront-settings.blade.php` → `@include('livewire.merchant.storefront-settings.partials.section-editor', [...])`)
> يستخدم `@include` مع مشاركة كاملة للحالة (`$wire.*` ودوال الأب مباشرة)، بينما `<livewire:...>` الحقيقي مستخدم فقط
> لودجت مستقل تمامًا (`variant-matrix`) لا يشارك حالة أبيه إطلاقًا.
>
> **لماذا هذا قرار أداء وليس شكليًا:** لو استُخدم `<livewire:...>` حقيقي هنا، فكل مكوّن فرعي (bulk bar, filter portal, form modal)
> يصبح له `wire:id` مستقل → كل تفاعل بداخله (فتح فلتر، تبديل تحديد) يُنشئ حمولة snapshot/checksum إضافية خاصة به،
> وأي تواصل مع حالة الأب (تحديد الصفوف، تحديث الجدول) يتطلب تمرير أحداث (`dispatch`/`listen`) وربما جولة شبكة إضافية،
> بالضبط عكس هدف هذا الريفاكتور (تقليل حمولة الشبكة وتبسيط دورة التحديث). أما `@include` فيبقي كل شيء
> ضمن حمولة Livewire واحدة لنفس المكوّن — نفس عدد الطلبات، بدون تسلسل/فك تسلسل إضافي، وبدون أي event plumbing.
> **الخلاصة: نقل الحالة والدوال بالكامل (كما هو مخطَّط أصلاً) آمن ومطلوب، لكن فقط عبر `@include`.**

---

## جدول حالة المراحل (بعد الدمج)

| Phase | العنوان | الحالة | ملاحظة الدمج |
|---|---|---|---|
| 1–8 | تتبّع الشحن + إصلاحات N+1/dead state/mystatuskit | ✅ DONE | — |
| 9 | استخراج مودال الإنشاء/التعديل | ✅ DONE | + M7 (submitEdit عبر OrderService) + وحدة `<x-edz.spinner>` — تنفيذ `@include` وليس مكوّن Livewire منفصل |
| 10 | استخراج شريط الإجراءات الجماعية (Bulk Actions) | ✅ DONE | + M10 (إعاقة الأزرار أثناء التنفيذ) — النقل عبر `@include` مع بقاء الحالة في الأب |
| 11 | استخراج بوابة الفلاتر (Filter Portal) | ✅ DONE | + M8 (استبدال `positionFilter` بـ `Alpine.data('dropdownPosition')` + تعميد `edz-filter-open` من أزرار الفلاتر) — البوابة تبقى في `index.blade.php` عبر `@include` وليس مكوّنًا منفصلًا |
| 12–18 | tracking UI + bulkSendToCarrier + مودال الإنشاء الجديد | ✅ DONE | — |
| R1–R7 | وحدة التحقق من المرتجعات | ✅ DONE | — |
| — | M2 (errorTofix): تخزين مؤقت لـ `filtered_amount` | ✅ محلول ضمنيًا | تمت إزالة `filtered_amount` بالكامل في Phase 6 — لا حاجة لتخزين مؤقت لشيء غير موجود |
| **19** | **أداء صفوف الجدول + إزالة كود JS المحقون** | ✅ DONE | `orderRowActions.js` (`Alpine.data`) + تقليص حمولة `orders` شرطيًا حسب الأعمدة المفعّلة |
| **20** | **تجاوب الشاشات + تنظيف رموز الوضع الليلي (Apple HIG)** | ✅ DONE | عرض بطاقات `lg:hidden` للجوال + جدول `hidden lg:block`؛ تنظيف `dark:` اقتصر على المواضع التي لا تُغيّر المظهر (الحالات الدلالية المتبادلة تلقائيًا أُبقيَت) |
| **21** | **تسجيل الأعمدة الممتد + إضافات السكيما** | ✅ DONE | سجل 23 عمودًا (13 افتراضيًا + 10 جديدًا) + migration `meta`/`send_from_carrier_warehouse` + لوحة Customize Columns مجمّعة (Apple-style) + عرض الأعمدة الجديدة read-only + ترجمات ar/en/es/fr |
| **22** | **إعدادات الجدول: مودال + حفظ صريح + ستايلات** | ✅ DONE | مودال `x-edz.modal` (تبويبا أعمدة/ستايل) + حفظ صريح عبر مسودات + قفل الأعمدة الأساسية الـ 13 + ستايل ثانٍ يلوّن الصفوف حسب الحالة (5 كلاسات SCSS) + migration `table_style` |
| **23** | **تفاصيل الطلب: مودال متجاوب + إزالة التكرار + ترجمة الحالات** | ✅ DONE | استبدال التوسيع المضمّن بمودال `x-edz.modal` متجاوب + إخفاء حقول الأعمدة المفعّلة + ترجمة الحالات بكل المواضع (badge/select/مينيو/فلاتر) عبر mystatuskit بالأيقونات |
| **24** | **إزالة التجاوز اليدوي لتكلفة الشحن + «توصيل مجاني» بدل 0 دج** | ✅ DONE | التراجع عن `order_shipping_costs` بالكامل + `manual_override`؛ `orders.shipping_cost` مصدر وحيد؛ عرض «مجاني» موحّد لأي `<=0` (Phase 24 أدناه) |

### بنود errorTofix.md المتبقية خارج نطاق هذا الملف (لم تُطوَ ضمن أي Phase أعلاه، تبقى مرجعًا منفصلاً)
- L1: حذف حالة `canceled` المكرّرة (تحتاج ترحيل بيانات) — مؤجّل
- L2: أعمدة `product_name`/`variant_name` كصورة ثابتة على `OrderItem` — تغيير سكيمة، مؤجّل
- ~~L5: `ShippingCostCalculator` fallback يتطلّب إعدادًا صريحًا~~ — **مغلقة في Phase 24**: عدم وجود أسعار مخصّصة يُعرض الآن «توصيل مجاني» بدل `0 دج` (قرار المستخدم: أي `shipping_cost <= 0` → مجاني)
- FE1–FE6 (تحسينات مستقبلية): حماية COD، إشعارات واتساب، تصدير، تحديث جماعي متعدد الحالات، حالات إضافية، إدارة حالات مخصّصة — **لا تُنفَّذ الآن**

---

## Phase 9 — استخراج مودال الإنشاء/التعديل (+ M7)

**الهدف:** فصل نموذج إنشاء/تعديل الطلب (اختيار المنتجات، الملخّص، الخصم) إلى مكوّن Blade منفصل، وتوحيد `submitCreate`/`submitEdit` عبر `OrderService` بدل التحديث المباشر على الموديل.

**الملفات:**
- جديد: `resources/views/livewire/merchant/orders/partials/order-form-modal.blade.php` (**`@include` Blade partial — ليس Livewire component منفصلاً**، راجع الملاحظة المعمارية أعلى الملف)
- تعديل: `index.blade.php` (إزالة الكتلة المنقولة، استبدالها بـ `@include('livewire.merchant.orders.partials.order-form-modal', [...])`)
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
- جديد: `resources/views/livewire/merchant/orders/partials/bulk-actions-bar.blade.php` (**`@include`، نفس نسخة الـ Volt component ونفس `wire:id` الخاص بـ `index.blade.php`**)
- تعديل: `index.blade.php`

**ما ينتقل:** الـ **Blade markup فقط** لشريط الإجراءات الجماعية. أما `selectedOrders`, `selectAll`, `showBulkBar`
والدوال `toggleSelectAll`, `toggleSelectOrder`, `clearSelection`, `bulkAssignAgent`, `bulkSendToCarrier`, `bulkDelete`
فتبقى **معرَّفة في `index.blade.php` كما هي بالضبط** (لأن جدول الطلبات في الأب يحتاجها مباشرة: تظليل الصف عبر
`in_array($orderId, $selectedOrders)`، و`wire:model="selectAll"` على رأس الجدول). لا يوجد أي تقسيم للحالة —
الشريحة الجزئية تستدعي `$wire.selectedOrders`/`toggleSelectAll()` مباشرة تمامًا كما يفعل الجدول الأب، لأنهما
نفس نسخة المكوّن حرفيًا.

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
- جديد: `resources/views/livewire/merchant/orders/partials/filter-portal.blade.php` (**`@include`، نفس نسخة الـ Volt component**)
- جديد: `resources/js/components/dropdown-position.js` (Alpine.data مسجَّل في `panel.js`، يحل محل كل نسخة من `positionFilter`/`getBoundingClientRect` اليدوية)
- تعديل: `index.blade.php`, `resources/js/panel.js`

**ما ينتقل:** `filters` (16 مفتاحًا)، `setFilter`, `clearFilters`, `toggleStatusFilter`, `loadFilterCities`، وكل قوائم `x-data="{ open:false }"` الفردية للفلاتر (Source, Delivery Type, إلخ — الأسطر 1234–1466 تقريبًا).

**إصلاح M8:** استبدل الدالة المضمّنة `positionFilter(e) { ... getBoundingClientRect ... }` (المعرَّفة في `x-data` الجذري بالسطر 1144) بمكوّن Alpine مسمّى (مثلاً `Alpine.data('dropdownPosition', ...)`) يُستخدم عبر `x-data="dropdownPosition()"` على كل قائمة منسدلة، بدل إعادة كتابة نفس منطق الموضع كسلسلة JS متكررة.

**تصحيح أداء (بعد اعتماد `@include`):** لا حاجة لأي حدث `filters-changed`/`dispatch`/`listen` بين "أب وابن" — بما أن الشريحة الجزئية جزء من نفس نسخة المكوّن، تستدعي `setFilter()` مباشرة والتي تستدعي `$this->loadOrders()` داخليًا كما هو معمول به الآن (نفس النمط الحالي في السطر ~104-123). إضافة طبقة أحداث هنا كانت ستضيف جولة معالجة زائدة بلا فائدة فعلية — إزالتها من الخطة الأصلية هي تحسين أداء مباشر، وليس فقط تبسيطًا معماريًا.

**Scope guard:** الشريحة الجزئية عرض ومنطق فلترة فقط — لا تُنشئ حالة أو `wire:id` منفصلاً بأي شكل.

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

---

## Phase 21 — تسجيل الأعمدة الممتد + إضافات السكيما (جديد)

**الهدف:** إضافة نظام تسجيل مركزي للأعمدة (Column Registry)، توسيع أعمدة الجدول بـ 13 عمودًا جديدًا قابلًا للتفعيل (تُقرأ فقط بالمرحلة الحالية)، مع إضافتي سكيما (`meta` json، `send_from_carrier_warehouse` bool) وترجمات كاملة. الفلاتر والتعديل المضمّن وتغييرات بطاقات الموبايل **خارج النطاق** هنا.

**الحالة: ✅ DONE (2026-09-01)** — تحقّق: 56 اختبار Order ناجحة، `view:cache` + `php -l` سليمة، `npm run build` ناجح، محاذاة thead/tbody مؤكَّدة برمجيًا، غيت نظيف عدا الملفات المقصودة.

**الملفات:**
- جديد: `database/migrations/2026_09_01_000001_add_meta_and_carrier_warehouse_to_orders_table.php` (`meta` json nullable بعد `notes`؛ `send_from_carrier_warehouse` bool default false بعد `shipment_type`؛ down يدمج الاثنين)
- تعديل: `app/Models/Orders/Order.php` (fillable + casts `meta=>array`/`send_from_carrier_warehouse=>boolean` + `confirmedByHistory(): HasOne` عبر `whereHas(status,key=confirmed)->latestOfMany('created_at')`)
- تعديل: `resources/views/livewire/merchant/orders/index.blade.php` (سجل الأعمدة، `loadColumnPreferences` مع تحقق، `toggleColumn`/`resetColumns`، eager-loads شرطية، لوحة مجمّعة، عرض 10 أعمدة جديدة read-only)
- تعديل: `resources/lang/{ar,en,es,fr}/merchant_panel.php` (11 مفتاح أعمدة + 4 تسميات مجموعات + `reset_columns`)

**المفاهيم المعتمدة:**
1. **سجل 23 عمودًا** (وليس 26 — العدّ الوارد في العنوان الأصلي خطأ مطبعي؛ تعداد الخطة نفسه 23: identity 6 + geography 7 + products_financial 4 + workflow 6). الـ 13 الموجودة سابقًا `default=>true` كلها (قرار المستخدم: "مع 13 افتراضية كما في الخطة حرفيًا" — يغيّر العرض الافتراضي من 9 إلى 13 عمودًا)، والـ 10 الجديدة `default=>false`.
2. **التحقق من المفاتيح القديمة:** `loadColumnPreferences` يستخدم `array_intersect($stored, $validKeys)` مع fallback للافتراضيات عند الإفراغ — أي مفتاح قديم/متقادم يُتجاهل تلقائيًا.
3. **eager-loads شرطية:** `confirmedByHistory` + `confirmedByHistory.status` + `confirmedByHistory.changedBy.user` تُضاف فقط عند تفعيل `confirmed_by`؛ و`stopdeskPoint` + `stopdeskPoint.city` فقط عند تفعيل `stopdesk_point`. `latestTracking.shippingProvider` محمّل دائمًا مسبقًا — لا تغيير في عدد الاستعلامات عند عدم تفعيل الأعمدة الجديدة.
4. **عرض `shipping_provider`:** مسطّح عبر `$order['tracking']['shipping_provider']` (سلسلة) — ليس متداخلًا كما افترضت الخطة.
5. **لوحة Customize Columns:** مجمّعة (identity/geography/products_financial/workflow)، `w-64 max-h-96 overflow-y-auto edz-scroll`، زر "استعادة الافتراضي" (`resetColumns`)، بدون تغيير نمط خانات الاختيار.
6. **التسميات:** `assigned_agent` تستخدم مفتاح ترجمة جديد (`merchant_panel.assigned_agent` — عربي: "يديره") وليس `agent` القديم.

**ملاحظات محفوظة للتوثيق:**
- لا توجد أي `dark:` جديدة عن النمط القائم سوى على زر إعادة التعيين (`dark:text-accent-400 dark:hover:text-accent-300 dark:hover:bg-ink-700`) — مطابقة لنمط `store-settings.blade.php` وضرورية لاتساق الوضع الليلي للوحة المُفعّلة داكنًا أصلًا.
- Zero raw hex في الإضافات؛ الحقول الجديدة تستخدم توكنز `surface-*`/`ink-*`/`accent-*` و`<x-edz.badge>`.
- لم يجرِ اختبار المسار الإيجابي لـ `confirmedByHistory` محليًا (لا توجد سجلّات status history في DB المحلية) — تحقّق null/first فقط.
- خطوط وأرقام أسطر مرجع الخطة (commit 47fb62a) قد لا تطابق الشجرة الحية بسبب التعديلات غير الملتَزمة — الاعتماد دائمًا على الملف الحي.

**Acceptance criteria (مكتملة):**
- [x] migration up/down سليمة
- [x] اختبارات الطلبات تمر
- [x] كل الأعمدة قابلة للتبديل عبر اللوحة مع تحقق من المفاتيح القديمة
- [x] لا `dark:` على العناصر المضيئة سابقًا / لا hex خام جديد
- [x] محاذاة thead/tbody مؤكدة (head/body متطابقان برمجيًا)
- [x] عدد الاستعلامات ثابت عند عدم تفعيل الأعمدة الجديدة

---

## Phase 22 — إعدادات الجدول: مودال + حفظ صريح + ستايلات (جديد)

**الهدف:** تحويل Customize Columns من dropdown بحفظ تلقائي إلى **مودال احترافي (Popup Modal)** مع **حفظ صريح** عبر زر Save، إظهار التحكم في **الأعمدة الثانوية فقط** (الأساسية الـ 13 دائمًا ظاهرة ومقفلة)، وإضافة **تابات لاختيار ستايل الجدول**: ستايل 1 (القياسي الحالي) وستايل 2 (تلوين خلفية الصف ونصّه حسب الحالة). تم التنفيذ بموافقة المستخدم الصريحة.

**الحالة: ✅ DONE (2026-09-01)** — تحقّق: migration نُفّذت (`migrate`)، `php -l` سليم، `view:cache` و`npm run build` ناجحان، كلاسات SCSS الجديدة موجودة في الملف المبني، 56 اختبار Order ناجحة، غيت نظيف عدا الملفات المقصودة.

**الملفات:**
- جديد: `database/migrations/2026_09_01_000002_add_table_style_to_user_column_preferences_table.php` (إضافة `table_style` string default `'default'` بعد `visible_columns`؛ down تحذف العمود)
- جديد: `resources/css/components/_status_rows.scss` (5 كلاسات `.edz-table-row--{success|warning|danger|info|gray}` فاتح/داكن + حالة `edz-row-selected` بـ accent)
- تعديل: `app/Domains/Order/Models/UserColumnPreference.php` (إضافة `table_style` إلى fillable)
- تعديل: `resources/css/components/_index.scss` (تسجيل `status_rows`)
- تعديل: `resources/views/livewire/merchant/orders/index.blade.php`
- تعديل: `resources/lang/{ar,en,es,fr}/merchant_panel.php` (12 مفتاحًا: tabs، أساسية/إضافية، دائمًا ظاهرة، حفظ، اسماء الستايلات + تلميحات، تم الحفظ)

**المفاهيم المعتمدة:**
1. **فصل مسودة/اعتماد:** خصائص `draftColumns`/`draftStyle` تُملأ عند فتح المودال من الحالة الفعلية (`tableStyle` + `visibleColumns`). تغيير checkboxes/الستايل يعدّل المسودة فقط. **Save** يصدّرها ويدفعها بـ`saveColumnPreferences()` + `loadOrders()` + toast؛ **Cancel/إغلاق/backdrop/ESC** يستدعي `discardTableSettings()` ولا يكتب شيئًا.
2. **الأساسية مقفلة دومًا:** التمييز عبر `default => true` (الـ 13). `loadColumnPreferences` يفرض `array_merge($primaryKeys, secondaries مفلتّرة)`؛ `saveColumnPreferences` يخزّن **الثانوية فقط** بعد `array_intersect`/`array_diff` ضد الأساسية — حتى لو عُبث بالـ payload لا يمكن إخفاء الأعمدة الأساسية.
3. **ستايل 2 بلا JS:** `$order['status']['color']` يعطي الدرجة (success/warning/danger/info/gray) → كلاس واحد على `<tr>` (والكارت على الموبايل) → SCSS يطبّق خلفية `{tone}-100`@.5 (light) / `{tone}-900`@.35 (dark) ولون نص `{tone}-700`/`{tone}-300` بكافّة `td`. عند الستايل الملوّن تُحذف كلاسات hover/select بتوكندات Tailwind لتجنّب التعارض وتُدار بالكامل في SCSS (بما فيها `edz-row-selected` بلون accent).
4. **المودال:** `x-edz.modal size=lg` (bottom-sheet تلقائيًا على الموبايل)، تبويبان بمنظّم segmented (columns/style بدوال `<x-edz.icon>`)، قائمة الأعمدة الأساسية بحرف قفل `lock-closed`، grid `2-col` للثواني، وبطاقتا ستايل بمعاينة حيّة (جدول مصغّر 3 صفوف ملوّنة). إغلاق عبر X/backdrop/ESC يُراقَب بـ`@edz-modal-closed.window` → `discardTableSettings`.
5. **سلوك الموبايل:** بطاقات `lg:hidden` تطبّق نفس كلاس `edz-table-row--{tone}` (تجانس بصري عبر المقاسات) — الكلاسات عنصر-مستقل (بدون بادئة `tr`).

**ملاحظات محفوظة للتوثيق:**
- لا `dark:` جديدة فوق المساحات المضيئة سابقًا؛ النمط الملوّن يعتمد على `.dark` ancestor من نفس الـ SCSS (ظل تناسق الوضع الليلي).
- عدم إعادة كتابة DB عند كل تبديل (حفظ واحد فقط) — تحسين أداء مقصود مقارنة بـ `toggleColumn` القديم.
- سلوك `body overflow` بعد الحفظ (إلغاء التركيب عبر `@if`) مطابق للسلوك المعتمد في مودالات reassign/create الحالية — لم يُغيَّر تناسقًا مع الكودبيس.
- ملفات `storage/framework/views/*.php` المتتبَّعة في git قد تعرض نسخًا قديمة من الصفحات — تُعاد ترجمتها تلقائيًا عند التشغيل.

**Acceptance criteria (مكتملة):**
- [x] migration up/down سليمة والعمود `table_style` موجود بعد `migrate`
- [x] حفظ صريح فقط (المسودة لا تُكتب قبل Save؛ إلغاء/إغلاق يتجاهلها)
- [x] الأعمدة الأساسية الـ 13 دائمًا ظاهرة ولا يمكن إخفاؤها من المودال
- [x] التبديل بين الستايلين يعمل فور الحفظ ويبقى محفوظًا لكل مستخدم
- [x] كلاسات `edz-table-row--*` موجودة في CSS المبني (app-*.css) وتعمل فاتح/داكن
- [x] اختبارات الطلبات تمر و`npm run build` سليم

---

## Phase 23 — تفاصيل الطلب: مودال متجاوب + إزالة التكرار + ترجمة الحالات (جديد)

**الهدف:** تحويل تفاصيل الطلب من التوسيع المضمّن (inline expand داخل صف/بطاقة الجوال) إلى **مودال منبثق متجاوب**، **إخفاء البيانات المكررة** (أي حقل له عمود مُفعّل في الجدول لا يُعرض في التفاصيل)، و**ترجمة كاملة بالحالات** عبر mystatuskit بالأيقونات. تم التنفيذ بموافقة المستخدم الصريحة (نفّذ كما هو مقترح / كل المواضع / زر تفاصيل = أيقونة معلومات داخل دائرة `info-circle`).

**الحالة: ✅ DONE (2026-09-01)** — تحقّق: `view:cache` سليم، 56 اختبار Order ناجحة، كل مفاتيح الترجمة والأيقونات المطابقة مؤكَّدة الوجود.

**الملفات:**
- تعديل: `resources/views/livewire/merchant/orders/index.blade.php` (استبدال `expandedOrderId`/`toggleDetail` وزر chevron بـ `detailsOrderId` + `openOrderDetails`/`closeOrderDetails` + زر `info-circle`؛ حذف صف التوسيع desktop وبطاقة الجوال الموسعة؛ إضافة مودال التفاصيل؛ ترجمة الحالة بكل مواضعها)
- تعديل: `resources/lang/{ar,en,es,fr}/merchant_panel.php` (3 مفاتيح جديدة: `order_details`, `details_shipping`, `details_contact`) + إصلاح ثغرة es الناقصة (`products`, `payment_method`, `status`)

**المفاهيم المعتمدة:**
1. **تفاصيل مودال `x-edz.modal size=md`:** يُعرض فقط عند `$this->detailsOrderId` مع بحث `firstWhere('id', ...)` في بيانات الصفحة الحالية (لا طلب إضافي). `@edz-modal-closed.window="$wire.closeOrderDetails()"` وX/backdrop/ESC تغلق وتُصفّر الحالة.
2. **قاعدة إزالة التكرار:** كل حقل له عمود مُفعّل يُستبعد عبر `!in_array('X', $this->visibleColumns)`. بدون عمود (مثل `payment_method`, `assignment_method`, `created_by`, `confirmed_by`, `tracking_number`, `shipped_at`, `delivered_at`) يُعرض دائمًا. الأقسام تُخفى بالكامل إن كانت كل حقولها مكرّرة.
3. **بعيد عن الجدول:** 6 حقول تبقي **دائمًا** في التفاصيل حتى لو لم يتوافق/يُخفى: `number`, `customer` (name/phone), `state`, `created_at` (في الرأس) + حقول بلا أعمدة (قائمة أعلاه).
4. **الأقسام (أيقونات edz):** Items (`bag` + إجمالي)، Shipping & Payment (`credit-card` — delivery/shipment/payment_method/weight/stopdesk/send_from_carrier_warehouse)، Contact & Location (`map-pin` — phone_secondary/city/address/meta)، Assignment (`users` — agent/method/created_by/confirmed_by/attempts/last_contact/notes)، Tracking (`truck` — carrier/tracking_number/shipped_at/delivered_at).
5. **ترجمة الحالات بالأيقونات (كل المواضع):** خلية الحالة desktop وأزرار/شرائح الفلاتر وبطاقة الجوال وعرض المودال تستخدم `Status::for('order', key)` → `icon()` + `label()` + `color()`. خيارات منيو الانتقال: ترجمة عبر mystatuskit + نقطة لون عبر `Status::for('general', color)->hex()`. أدوات العرض (badge/x-status-select/x-status-icon/dot) جاهزة عند الحاجة.
6. **استخدام التنسيقات المأخوذة من خلايا الجدول:** `delivery_type` → `stop_desk_label`/`home_delivery_label`، `stopdesk_point` → name (+city)، `send_from_carrier_warehouse` → `<x-edz.badge>` check/x، الوزن `… kg`، address محدود 60، meta مجمّع `k: v`.

**Scope guard:** لا `dark:` على عناصر مضيئة سابقًا، لا hex خام (نقطة اللون فقط عبر `hex()` في المينيو، مطابق للنمط القائم)، توكنز `surface-*/ink-*/accent-*`، خصائص logical RTL، فتحة المودال بنفس ملف المكوّن (لا مكوّن Livewire منفصل).

**Acceptance criteria (مكتملة):**
- [x] فتح/إغلاق المودال يعمل (زر `info-circle` desktop والجوال، إغلاق عبر X/backdrop/ESC)
- [x] أي حقل له عمود مُفعّل لا يظهر في التفاصيل حتى بتكرار الأعمدة الافتراضية الـ 13
- [x] كل مواضع الحالة مترجمة بالأيقونات (خلية/badge/مينيو/فلاتر/مودال)
- [x] مفاتيح الترجمة الثلاثة + إصلاح es موجودة في اللغات الأربع
- [x] اختبارات الطلبات تمر و`view:cache` سليم

---

## Phase 24 — إزالة التجاوز اليدوي لتكلفة الشحن + «توصيل مجاني» بدل 0 دج (جديد)

**الحالة: ✅ DONE (2026-09-05)** — تحقّق: `php -l` سليم على كل ملف مُغيَّر، `view:cache` ناجح، اختبارات طلبات التاجر 23 ناجحة (109 assertions)، والـ suite كاملة **285 ناجحة (1056 assertions)**، لا مراجع متبقية لأي رمز مُزال (فحص app/resources/routes/config/database/tests).

**الهدف:** التراجع عن جدول `order_shipping_costs` وخط تجاوز `shipping_cost` اليدوي (بموافقة المستخدم الصريحة — «`orders.shipping_cost` مصدر الحقيقة الوحيد، تكلفة الشحن والمجموع الكلي غير قابلَين للتخصيص»)، وإغلاق **L5**: عند غياب أسعار مخصّصة (لا للشركة ولا عبر قائمة الأسعار) يُعرض **«توصيل مجاني»** بدل مبلغ `0` — بقاعدة موحّدة: أي `shipping_cost <= 0` يُعرض مجاني.

### إزالات
| الملف | التغيير |
|---|---|
| `database/migrations/2026_09_05_000003_create_order_shipping_costs_table.php` | حُذف (لم يُنشر في MySQL — ظل `Pending`) |
| `app/Models/Orders/OrderShippingCost.php` | حُذف |
| `app/Models/Orders/Order.php` | حُذف `shippingCosts()` + `latestShippingCost()` |
| `app/Domains/Order/Services/OrderService.php` | حُذف `use OrderShippingCost` + أسلوب `snapshotShippingCost()`؛ إنشاء الطلب يكتب `orders.shipping_cost` مباشرة من نتيجة الحاسبة |
| `resources/views/livewire/merchant/orders/index.blade.php` | حُذف `$startOrderShippingCostEdit` + `$saveOrderShippingCost` + الإدخال الرقمي المضمّن؛ `$recalculateOrderShipping` يكتب مباشرة |
| `resources/lang/{ar,en,fr,es}/merchant_panel.php` | حُذف `manual_override` |
| `tests/Feature/Merchant/OrderInlineEditTest.php` | حُذف اختبار «override the shipping cost inline» (الميزة أُزيلت) — أُبقي اختبار recalc المدينة (يتوقّع `shipping_cost === 400.0` بعد `saveOrderCity`، يثبت مسار الحاسبة) |
| `tests/Feature/scratch_validator_test.php` | حُذف (بقايا مؤقتة) |

### عرض «توصيل مجاني» (بلا تغيير سكيما — أداء مثالي)
- عمود `shipping_cost` في الجدول (desktop) يعرض الآن شارة `<x-edz.badge tone="neutral" sm>` بأيقونة `truck` ونص `merchant_panel.shipping_free` عند `<= 0`، والـ `currency()` عند القيمة المدفوعة — **بلا زر تعديل (read-only)**.
- بطاقة الموبايل (`lg:hidden`) تطبّق نفس المنطق عند إظهار تكلفة الشحن.
- ترجمة `shipping_free` أُضيفت ×4 لغات: `Free delivery / توصيل مجاني / Livraison gratuite / Envío gratuito`.
- خصائص أداء: صفر استعلامات إضافية (يقرأ العمود الرقمي المحمّل في `$arr`)، صفر ترحيل/ميغريشن، صفر إضافة لحجم الحزمة (شارة موجودة مسبقًا).
- توثيق سلوك موحّد (قرار المستخدم): `cost=0` من أي مصدر (لا أسعار، `free_above`، أو stopdesk غير محسوب) يُعرض مجاني.

**Acceptance criteria (مكتملة):**
- [x] لا مراجع متبقية لـ `snapshotShippingCost`/`shippingCosts`/`latestShippingCost`/`OrderShippingCost`/`manual_override`/`saveOrderShippingCost`/`startOrderShippingCostEdit` في app/resources/routes/config/database/tests
- [x] `php -l` و`view:cache` سليمان
- [x] اختبارات طلبات التاجر 23 ناجحة (109 assertions) — منها recalc المدينة بـ `400.0`
- [x] الـ suite كاملة 285 ناجحة (1056 assertions) — لا ارتداد
- [x] تحقّق النقاط: خلية `shipping_cost` لا توسّع الجدول على `lg+` (overflow-x قائم)، والشارة تلفّ سليمًا على بطاقة الجوال

---

## Phase 25–29 — عمليات الطلبيات: التأكيد الاحترافي/التتبع/السجل/التكرار/الجماعي (جديد)

**الحالة: ✅ DONE (2026-09-05)** — الخطة الكاملة: `OrdersOperationsPlan.md` (P25–P29، موافقة «نفّذ كل شيء»). التحقّق: `php -l` سليم، `view:cache` ناجح، `route:list` يؤكد `merchant.tracking.index`، والـ suite كاملة **290 ناجحة (1064 assertions)** — منها **5 اختبارات جديدة** (`tests/Feature/Merchant/OrderDuplicateDetectionTest.php`) + اختبارات التتبع الناتجة (8 لدومين التتبع، تستمر ناجحة) + صفر انحدار.

### P25 — البنية (متوفّرة فعليًا، بلا تصريح لمكوّن منفصل — التزام قاعدة `@include`)
- `app/Domains/Order/Support/OrderWorkflow.php` — مجموعات `backOffice`/`carrier`/`closed` + `isCarrier(key)`؛ CARRIER تعني «يظهر في صفحة التتبع فقط».
- `app/Domains/Order/Services/OrderAuditService.php` — `log(Order, eventType, message, actorMembershipId, meta)` + المساعِدات `created`/`statusChanged`/`fieldChanges`/`contactAttempt`/`sentToCarrier`/`tracking`/`reassigned`/`note` (يُكتب `order_events`).
- `app/Domains/Order/Services/OrderTrackingService.php` (إعادة كتابة) — سجل `order_tracking_histories` على كل تغيّر تتبع (startShipment/markDelivered/markReturned/markReturning/markLost/markDamaged/markFailedAttempt/markInTransit/markOutForDelivery) مع idempotency عبر `currentOpenTracking()`، يقبل actor membership.
- `app/Observers/OrderObserver.php` — تدقيق إنشاء + تعديل (diff على TRACKED_FIELDS) + حالة (from→to، يفكّر في reason عبر `setTransitionMeta` الذي خُزّن `from_key` جديدًا) + `syncTracking` مطلوب (علامة `$status->key` shipped/delivered/returned) — إنشاء/تعديل/تفعيل تمامًا داخل `orders` فقط.
- ميغريشنات `2026_09_05_100001/100002/100003` — `order_events`، `order_tracking_histories`، فهرسا `(store_id, customer_id, created_at)` و`order_items(product_variant_id)`.

### P26 — درج التأكيد الاحترافي (صفحة الطلبيات)
- `resources/views/livewire/merchant/orders/index.blade.php` — مودال التأكيد (ملخص الطلبية + شريك التوصيل `confirmProviderId` + مفتاح «تم الاتصال» → `confirmation_attempts`/`last_contact_at`) بمسلكين: `submitConfirmOnly` (تأكيد فقط) و`submitConfirmAndSend` (تأكيد + إرسال للشركة عبر `OrderShippingGateway::send()`).
- `app/Domains/Shipping/Services/OrderShippingGateway.php` — `send()`: `confirm` ثم حلقة `preparing → shipped` داخل معاملة DB + إرسالًا للـ carrier عبر `CarrierOrderPostService` (try/catch + `sentToCarrier` audit) + إنشاء tracking عبر observer تلقائيًا.
- **التصفية الافتراضية:** `loadOrders` تستبعد حالات `OrderWorkflow::carrier()` من «الطلبيات» عندما `filters.status` فارغًا وليست في سلة المحذوفة — الطلبية «تختفي» بمجرد الإرسال للشركة (تظهر في «تتبع الطلبيات»).

### P27 — صفحة «تتبع الطلبيات» (`merchant/{store}/tracking`)
- `routes/merchant.php` + `resources/views/livewire/merchant/tracking/index.blade.php` (سطر Volt — لا مكوّن فرعي، حارس `ORDER_VIEW`).
- فلاتر (provider/tracking_status/date_from/date_to) + إحصائيات (نشطة/سلّمت اليوم/رجع اليوم) + جدول desktop (`hidden md:block`) + بطاقات موبايل (`md:hidden`).
- درج الطلبية: بطاقة الشركة (رقم التتبع + نسخ)، ملخص الشحن، إجراءات سريعة (in_transit/out_for_delivery/failed_attempt/returning/delivered/returned/lost/damaged — على حارس `ORDER_MANAGE`)، سجل حالات التتبع + سجل الطلبية (خط زمني). التسميات عبر `Status::for('tracking', …)->label()` (بدون ملف `status_tracking.*`).

### P28 — كشف الطلبيات المتكررة
- `app/Domains/Order/Services/OrderDuplicateService.php` — `findSimilar(Order|array $candidate, int $limit=5)`: تطابق `customer.phone` + تداخل منتجات (`product_variant_id`/`product_id`)، نافذة 30 يومًا، `exclude_id` لاستبعاد الطلبية الجاري تعديلها، عزل المتجر.
- **نموذج الإنشاء/التعديل:** `formDuplicateWarnings` يُحتى في `updated('form.customer_phone')`/`updated('form.items')` (قرار §4.2: على أفعال منفصلة — اختيار العميل/إضافة عنصر) + لوحة تحذير بعد المنتجات (حالة/رقم/تاريخ + رابط يفتح تفاصيل الطلبية عبر `openOrderDetails` — الذي صار يحمّل الطلبية بنفسه حتى لو لم تكن في الصفحة الحالية).
- **درج التأكيد:** لوحة «تحقّق» مُماثلة + إجراء سريع `markOrderDuplicate` (transition `duplicate` عبر `OrderService`).
- Limit 5 (مطابقة §4.1 «أحدث 5»؛ §4.3 ذكر 6 كنظر نظري).

### P29 — تعديل حالة جماعي
- `submitBulkStatus` — قائمة الحالات المستثناة دائمًا `['cancelled', 'canceled', 'confirmed']`؛ لكل طلبية `canTransition` تُطبق والتخطّي يُبلَّغ (swal)، زرار «تغيير الحالة» في شريط التحديد الجماعي.
- ترجمات `order_flow.php` ×4 لغات (event_*/confirm_*/duplicate_*/tracking_*/bulk_*).

### Acceptance criteria (مكتملة)
- [x] strip: الصفحة «الافتراضية» للطلبيات لا تعرض CARRIER (ظهر الاختبار المرتبط فعليًا قبل هذا العنقود؛ فحص `loadOrders` يأكد)
- [x] التتبع: `merchant.{store}.tracking` مسار فعلي + القائمة الجانبية تظهر عليه
- [x] تطبيق التتبع عبر `OrderTrackingService` يسجّل سجل حالة لكل تغيير + تدقيق على الطلبية
- [x] `findSimilar` يقبل `Order|array` (اختبار مباشر)
- [x] تحذير النموذج لا يرمي على كل keystroke (hooks على onChange لأفعال منفصلة فقط)
- [x] الأداء: صفر N+1 (eager-loading)، `limit 5`، لا مكوّن Livewire فرعي
- [x] 290 ناجحة (1064 assertions) — صفر انحدار؛ `php -l` + `view:cache` سليمان

---

## Phase 29.1 — إتاحة سجل أحداث الطلبية (Event Log Visibility) (جولة Phase 29 الجديدة)

**الحالة: ✅ DONE (2026-09-05)** — فتح جولة «Phase 29» (29.1–29.7) على مكوّن الطلبيات، تُنفَّذ فرعًا تلو الفرع بموافقة صريحة من المستخدم. الخطة الكاملة: `OrdersOperationsPlan.md`.

- **الفجوة (root cause):** الخط الزمني `order_events` كان يظهر لكل من يفتح مودال تفاصيل الطلبية أو درج التتبع دون تمييز دور — حتى STAFF يرى سجل الاتصالات/الإرسال/الإسناد للمنظومة بأكملها.
- **الحل:** `StoreOrderPermissions::canViewOrderEventLog(Order, StoreMembership): bool` — OWNER/ADMIN دائمًا، MANAGER فقط لطلب معيّن لعضويته (`assigned_to_membership_id === $membership->id`)، STAFF أبدًا (ولا تُحمَّل الأحداث أصلاً عند المنع). حارس موحّد على كلٍّ من:
  - مودال التفاصيل `resources/views/livewire/merchant/orders/index.blade.php` (`openOrderDetails` + `canViewOrderDetailsEvents` + الشرط في التيمبلت).
  - درج التتبع `resources/views/livewire/merchant/tracking/index.blade.php` (`openDrawer` + `canViewDrawerEvents`) — نفس السجل، لا ثغرة ملتافة.
- **سهولة القراءة:** تجميع الأحداث باليوم (اليوم/أمس/تاريخ مترجم عبر `translatedFormat`) + وقت `H:i` + اسم الفاعل + شارة دوره عبر mystatuskit (`x-role-badge`).
- **الترجمات:** `event_day_today`/`event_day_yesterday` ×4 لغات في `resources/lang/{en,ar,fr,es}/order_flow.php`؛ تأكيد مسبق أن مفاتيح `event_type_*` كلها موجودة (8/8 في كل لغة).
- **الاختبارات:** `tests/Feature/Merchant/OrderEventLogVisibilityTest.php` — **8 ناجحة (20 assertions)**: الدور الشامل (owner/admin دائمًا، manager للمعيَّن فقط، staff أبدًا حتى لو مُعيَّن) + تكامل مودال التفاصيل (owner يرى/لا يرى staff + `canView*`) + درج التتبع (owner يرى/لا يرى staff).
- **التحقق:** السويت الكامل **297 ناجح (1080 assertions)**؛ الإخفاق الوحيد كان قفل ملفات Blade على Windows (`CartOrderLimitsTest`) — أُعيد منفردًا **11/11**، أي صفر انحدار فعلي. `view:cache` + `php -l` سليمان.
- **خارج النطاق (لم يُلمس):** call-center، HR/payroll، ERP، landing-builder، `mystatuskit`، قيم `StorePermissionEnum`.

---

## Phase 29.2 — الإرسال المباشر لطلبية مُؤكَّدة (Direct Send to Carrier) (جولة Phase 29)

**الحالة: ✅ DONE (2026-09-05)** — الفرع الثاني من جولة Phase 29، بموافقة صريحة من المستخدم.

- **الفجوة (root cause):** طلبية في `confirmed`/`preparing` لا يمكن إرسالها لشركة التوصيل إلا بإعادة فتح درج التأكيد — لا يوجد إجراء مباشر من الصف/البطاقة.
- **الحل:** إجراء `$sendConfirmedOrder(string $orderId)` في `resources/views/livewire/merchant/orders/index.blade.php`:
  - حارس `abort_unless(canStore(ORDER_MANAGE), 403)` — **استُبدل بتوست `messages.unauthorized` في إغلاق 29.7** (الوعد «توست بدل 403» نُفِّذ؛ نَقل 29.2→29.7).
  - يُقبل `confirmed`/`preparing` فقط — غير ذلك → `swal:toast` بـ`send_requires_confirmation` ويعود فورًا (**لا auto-confirm مطلقًا**؛ `OrderShippingGateway::send` يُستدعى بـ`confirmFirst: false`).
  - فحص جهوزية قبل أي تحوّل: اسم العميل، هاتفه، الولاية، البلدية، العنوان-أو-نقطة الاستلام، ≥1 صنف، وشركة توصيل-أو-موصّل. الناقص يُدرج واحدًا واحدًا (عبر `merchant_panel.*` المترجمة) في توست `send_missing_fields:fields` — وتبقى الحالة دون تغيير.
  - عند اكتمال المعطيات: `OrderShippingGateway::send(order: $order, providerId: $order->shipping_provider_id ?: null, changedBy: $membership)` ثم `loadOrders()` ثم توست نجاح (`merchant.orders_sent`).
  - زرا إرسال (أيقونة truck) في صف الجدول + بطاقات الهاتف يظهران فقط عندما يكون `$order['status']['key'] ∈ {confirmed, preparing}` وبإذن `ORDER_MANAGE` — ليس في سلة المهملات. درج التأكيد (P26) لم يُعدَّل إطلاقًا.
- **الترجمات:** `send_requires_confirmation`/`send_missing_fields` ×4 في `resources/lang/{en,ar,fr,es}/order_flow.php` بعد `confirmed_only`.
- **إصلاح خطأ كامن اكتُشف أثناء التنفيذ:** `app/Domains/Order/Services/OrderTrackingService.php` كان يعلّق معاملات الفاعل `?int $actorMembershipId` بينما `store_memberships.id` نوعه ULID (نصي) → أي انتقال إلى `shipped` بعضوية فاعلة كان يرمي `TypeError` داخل `OrderObserver::syncTracking()` (يُعطّل حتى درج «تأكيد وإرسال» الحالي). أصبح النوع `int|string|null` — تعديل محايد سلوكيًا بلا أي تغيير منطقي.
- **الاختبارات:** `tests/Feature/Merchant/DirectSendConfirmedOrderTest.php` — **7 ناجحة (16 assertions)**: STAFF بلا صلاحية → توست `messages.unauthorized` بلا فصل ولا تغيير حالة (كان 403؛ نُقِّح في إغلاق 29.7)؛ pending → رفض بتوست `send_requires_confirmation` مع بقائها pending (لا تأكيد تلقائي)؛ confirmed → shipped مع تأكيد حدث `sent_to_carrier`؛ preparing → shipped؛ عنوان ناقص → توست يذكر `merchant_panel.address` والحالة ثابتة؛ غياب (العميل+المنتجات+الشريك) → كل الحقول الناقصة تُدرج؛ نقطة استلام بدل العنوان → إرسال ناجح.
- **التحقق:** السويت الكامل **305 ناجح (1100 assertions)** — صفر انحدار (CartOrderLimitsTest نجح ضمن السويت كاملًا هذه المرة). `view:cache` + `php -l` سليمان.
- **خارج النطاق (لم يُلمس):** درج التأكيد، call-center، HR/payroll، ERP، landing-builder، `mystatuskit`، قيم `StorePermissionEnum`.

---

## Phase 29.3 — الإرسال الجماعي للطلبيات مجمَّعًا حسب شركة كل طلبية (Bulk Send Grouped per Carrier) (جولة Phase 29)

**الحالة: ✅ DONE (2026-09-05) — أُعيد العمل عليه بملاحظات المستخدم ثم اكتمل** — الفرع الثالث من جولة Phase 29، بموافقة صريحة من المستخدم.

- **الفجوة (root cause):** الإرسال الجماعي القديم (`bulkSendToCarrier(?string $providerId)`) كان يدفع *كل* الطلبيات المحددة إلى شركة واحدة يختارها المستخدم من قائمة — بينما لكل طلبية شركة (أو موصّل) خاص بها مُسجَّل، فكان الإرسال «لكل الاحتياط» يعلّقها على شركة مختلفة. كما لم يكن يعرض ملخص تأكيد ولا فحص جهوزية. **وفي مراجعة المستخدم:** المودال لم يميّز الجاهز عن غير الجاهز (مجموعة «بلا شركة» تظهر كأنها ستُرسل ثم تُتخطى بصمت)، والمجموعات كانت بأسماء عامة لا اسم الموصّل الحقيقي، وتوست النتائج كان بعدًّا غامضًا بلا أرقام ولا أسباب.
- **الحل النهائي** في `resources/views/livewire/merchant/orders/index.blade.php`:
  - `$resolveBulkOrderState(Order)` — مصدر الحقيقة الوحيد: `carrierKey = shipping_provider_id ?: delivery_rider_id ?: 'unassigned'`؛ `carrierName` = اسم الشركة **أو اسم الموصّل الحقيقي** (`deliveryRider->name`) أو «بلا شركة»؛ `ready`؛ و`reasons[]` صريحة (تحتاج تأكيدًا/تجهيزًا مع `status_label`، أو كل الحقول الناقصة، أو بلا شركة/موصّل).
  - `$openBulkSendModal()` — جلب واحد بـ `with(['status','customer','shippingProvider','deliveryRider','items'])` (لا N+1؛ `items()->exists()` → `items->isEmpty()`)، يملأ `bulkSendAnalysis`/`bulkSendReadyCount`/`bulkSendSkipCount`؛ ملخص `bulkSendSummary` من **الجاهزة فقط**.
  - مودال التأكيد قسمان في `x-edz.modal` (bottom-sheet تلقائيًا ≤639px حسب `_modal.scss`): «ستُرسل» (ملخص الناقلين) + `<x-edz.alert type="warning">` يسرد كل طلبية غير جاهزة (رقم + سبب، قائمة قابلة للتمرير `max-h-40`). بلا جاهزة → زر معطَّل `bulk_send_confirm_none`؛ وإلا «إرسال :count الجاهزة فقط» `bulk_send_confirm_some`. صف الأزرار `flex-col sm:flex-row` (الأساسي أسفل في الموبايل).
  - `$confirmBulkSend()` — يعيد الجلب طازجًا بنفس eager-loads ويعيد الحساب بالمولّد نفسه، يرسل الجاهزة فقط كلٌّ لناقلها الفعلي (`OrderShippingGateway::send(... providerId: $order->shipping_provider_id ?: null)`؛ لا auto-confirm أبدًا)، توست واحد صريح: سطور الشركات + رأس `bulk_send_skipped :count` + سطر لكل طلبية `رقم (السبب)` — حدث واحد لأن `assertDispatched` بغلاف يفحص أول حدث مطابق فقط.
  - `$sendConfirmedOrder` (29.2) أعيد إلى استخدام `$collectMissingFields` نفسه (مصدر فحص واحد، إزالة النسخ).
  - زر «إرسال» في `partials/bulk-actions-bar.blade.php` (زر واحد يفتح المودال مع حارس `ORDER_MANAGE`).
- **الترجمات:** مفاتيح 16 ×4 لغات في `order_flow.php` (9 القائمة + 7 جديدة): `bulk_send_ready_title`/`bulk_send_skipped_title`/`bulk_send_reason_status`/`bulk_send_reason_missing`/`bulk_send_reason_no_carrier`/`bulk_send_confirm_some`/`bulk_send_confirm_none`.
- **الاختبارات:** `tests/Feature/Merchant/BulkSendCarrierGroupingTest.php` — **9 ناجحة (48 assertions)**:
  1. STAFF بلا صلاحية → توست `messages.unauthorized` بلا فتح المودال (كان 403؛ نُقِّح في إغلاق 29.7)؛ 2. تحذير بدون تحديد دون فتح المودال؛ 3. تصنيف المودال: مجموعات جاهزة بأسماء الناقلين الفعلية (شركتان + «Bsc Rider» باسمه) + غير الجاهز مُعلَم بسبب `bulk_send_reason_no_carrier` + `assertSee` للأرقام والأسباب؛ 4. إرسال كل طلبية لناقلها مع ملخص «Alpha Carrier (2) • Beta Carrier (1)» + حادثتَا `sent_to_carrier`؛ 5. تخطِّي pending دون تأكيد تلقائي **مع ذِكر رقمها**؛ 6. تخطِّي الناقص **مع رقمه واسم الحقل**؛ 7. إرسال طلبية موصّل فقط **باسم الموصّل**؛ 8. تخطِّي بلا شركة مع السبب في المودال والتوست؛ 9. «لا شيء جاهز» → تحذير صريح بالأرقام دون أي إرسال.
- **التحقق:** السويت الكامل **314 ناجح (1148 assertions)** — صفر انحدار. `view:cache` + `php -l` سليمان.
- **خارج النطاق (لم يُلمس):** درج التأكيد (P26)، call-center، HR/payroll، ERP، landing-builder، `mystatuskit`، قيم `StorePermissionEnum`.

## Phase 29.4 — عمود حالة التتبع + توحيد الخط الزمني للأحداث (Tracking Status Column + Shared Timeline Partial) (جولة Phase 29)

**الحالة: ✅ DONE (2026-09-05) — بموافقة المستخدم؛ بذرة محلية ملغاة بقراره الصريح** — الفرع الرابع من جولة Phase 29، واجهة خالصة بلا أي تغيير في السلوك/المنطق.

- **الفجوة (root cause):** جدول الطلبيات لا يعرض حالة التتبع للطلبيات التي لها شحنة (بينما `latestTracking.tracking_status` متاح بالترابط الحالي)، وقسم «سجل الطلبية» (الخط الزمني لأحداث `order_events`) كان منسوخًا حرفيًا في موضعين: درج تفاصيل الطلب في `orders/index.blade.php` ودرج التتبع في `tracking/index.blade.php` — أي إصلاح مستقبلي (مثل 29.1/29.6) يتطلب تعديلين مكررين.
- **الحل:**
  - **العمود:** إدخال سجل `$orderColumns()`: `['key' => 'tracking_status', 'label_key' => 'tracking_status', 'group' => 'workflow', 'default' => false]` (ثانوي اختياري — لا يظهر افتراضيًا؛ يُفعَّل من إعدادات الجدول). `tracking_status` أُضيف إلى بانيّ `$arr['tracking']` في `loadOrders` وfallback `openOrderDetails`. الخلية (desktop، بعد عمود الحالة) تصفّ شارة mystatuskit بنطاق `tracking` (`Status::for('tracking', $key)->color()/icon()/label()` — نفس النمط المعتمد في صفحة التتبع P27) عند وجود حالة، أو `—` بخلافها. بطاقة الموبايل تعرض الشارة بجانب شارة الحالة عند تفعيل العمود فقط.
  - **الجزئية:** `resources/views/livewire/merchant/orders/partials/order-events-timeline.blade.php` — تبني ترقيم الأيام وأحدث حدث داخليًا، وتستقبل `events` + `icon` عبر `@include`. الاستدعاءان: `orders/index.blade.php` (`canViewOrderDetailsEvents` + `detailsEvents`, `icon: clock`) و`tracking/index.blade.php` (`canViewDrawerEvents` + `drawerEvents`, `icon: list-bullet`). الحرّاسات بقيت في مواضع الاستدعاء — لا أثر على صلاحيات 29.1. قسم «محفوظات التتبع» في درج التتبع خارج النطاق (خاص بـ P27).
  - **الترجمات:** `merchant_panel.tracking_status` ×4 لغات (en/ar/fr/es) — لرأس الجدول ومنتقي الأعمدة. لا مفاتيح `order_flow` جديدة.
  - **البذر المحلي:** `order_tracking_histories` **لم يُنفَّذ** — قرار المستخدم الصريح بتخطيه (البيانات تُنشأ طبيعيًا عبر `OrderTrackingService` عند الإرسال/التحديث، والفراغ مُعالَج بواجهات `tracking_history_empty`/`order_timeline_empty`).
- **الاختبارات:** `tests/Feature/Merchant/OrdersTrackingColumnTest.php` — **3 ناجحة**: 1) إظهار شارة التتبع عند تفعيل العمود (`assertSee` + رأس العمود + `visibleColumns`)؛ 2) غياب الشارة افتراضيًا (عمود معطَّل)؛ 3) `—` عند غياب الشحنة مع العمود مفعّلًا. **ملاحظة تقنية:** يستخدم الاختبار حالة تتبع `damaged` — خاصة بنطاق `tracking` فقط، لأن `in_transit` موجودة أيضًا في نطاق `order` (تُعرض في فلتر الحالة/قوائم الحالات الأخرى فتلوّث فحص `assertSee`/`assertDontSee`). التوحيد تحت التحقق عبر `OrderEventLogVisibilityTest` (8) القائمة التي تغطي الدرجين بعد إعادة البناء.
- **التحقق:** السويت الكامل **317 ناجح (1158 assertions)** — صفر انحدار. `view:cache` + `php -l` سليمان.
- **متابعة (بناءً على ملاحظة المستخدم):** زرا «إرسال للناقل» في الجدول وبطاقة الموبايل كانا يفحصان `in_array($order['status']['key'] ?? null, ['confirmed','preparing'], true)` — شكل قراءة يوحي خطأً بوجود عمود حالة في جدول `orders`، بينما الحالة في جدول إحالة عبر `status_id`. الحل المعتمد: `loadOrders` يُضيف `$arr['status_key'] = $order->status?->key` (بانيّ الصف)، والحرّاسان أصبحا `in_array($order['status_key'] ?? null, ...)`. نمط النموذج/الوحدة `$order->status?->key` في `$resolveBulkOrderState`/`$sendConfirmedOrder` كما هو. تحقّق: السويت كاملة **317 ناجح (1158 assertions)** + 27 ناجح (94) في الاستهداف.
- **خارج النطاق (لم يُلمس):** منطق `loadOrders`/الفلاتر/الصلاحيات، صفحة التتبع وأقسام محفوظاتها، درج التأكيد (P26)، call-center، HR/payroll، ERP، landing-builder، `mystatuskit`، قيم `StorePermissionEnum`.

## Phase 29.5 — شارة التكرار + الفحص البطيء للطلبيات المكررة (Duplicate Badge + Lazy Scan Popup) (جولة Phase 29)

**الحالة: ✅ DONE (2026-09-05) — بموافقة المستخدم (أكمل التنفيذ)** — الفرع الخامس من جولة Phase 29، واجهة معلومات بحتة بلا تغيير في السلوك/المنطق.

- **الفجوة (root cause):** قائمة الطلبيات لا تُعلم بوجود طلبيات مشابهة يشتبه أنها مكررة إلا داخل درج تأكيد الطلب (P26) — والتاجر لا يفتحه لطلبياته العادية، بينما الكشف ممكن: رقم هاتف العميل الوحيد ضمن المتجر (قيود `store+phone`) أو تداخل أصناف (variant/product) كلاهما متاعبان في نافذة 30 يومًا نفسها.
- **الحل (طبقتان):**
  - **العلامة غير المعطِّلة (عند كل تحميل صفحة، استعلام تجميعي واحد):** `loadOrders` يجمع `COUNT(*)` حسب `customer_id` لطلبيات المتجر في نافذة `WINDOW_DAYS` مع `havingRaw('COUNT(*) >= 2')` — يتنفس على فهرس `orders_duplicate_scan (store_id, customer_id, created_at)` (تتمة جدولة P27) — وشروطها مطابقة لشرط `findSimilar` (نافذة + نطاق + soft-deletes) عدا «استثناء الذات» الذي لا معنى له في العلامة. البانيّ يضع `$arr['duplicate_count'] = $duplicateCounts[$order->customer_id] ?? 0`. الشارة `edz-badge edz-badge--warning edz-badge--sm` «×N» تظهر بعد رقم الطلبية — desktop (خلية `number`) وموبايل (رأس البطاقة `justify-between`) — مرشَّحة بـ `!$this->showTrash` + `status_key !== 'duplicate'` + `count >= 2`. صفر مفاتيح ترجمة جديدة (أدواة القصد تعيد استخدام `order_flow.duplicate_warnings_title`).
  - **الفحص البطيء (عند النقر فقط):** `$openDuplicateScan(string $orderId)` يسترجع الطلبية مصرَّفـًا لعلاقاته، يميز `findSimilar($order)` **الآن فقط** (لا مساحة حساب لصفوف غير المكبوسة)، ويحفظ `duplicateScanNumber` + `duplicateScanResults` ويفتح `showDuplicateScanModal`. العرض `<x-edz.modal>` بنمط لوحة التحذير في درج التأكيد (سطر لكل نتيجة: رقم • `diffForHumans` + `×total_overlap_qty`، وزر انتقال `openOrderDetails` إلى درج التفاصيل؛ فراغ → `order_flow.no_duplicates`). الإغلاق `$closeDuplicateScanModal` (= false + تفريغ) عبر `@edz-modal-closed.window`. لا `wire:lazy`/`#[Lazy]` في الكود — «البطيء» يعني عند الطلب بنمط المودالات القائمة (نفس منهج المودالات الفارسية الحالية).
  - **الصلاحيات:** النافذة معلومات لكل أعضاء الصفحة (أرقام طلبيات متجرٍّ نفسه — مرئية بالقائمة أصلًا)؛ وضع العلامة `markOrderDuplicate` (ORDER_MANAGE) لم يُلمس.
- **الأدلة:** `tests/Feature/Merchant/OrderDuplicateBadgeTest.php` — **5 ناجحة**: ① علامة على الصفّين لزوج رقمٍ مكرر (أيضًا `duplicate_count`=2)، ② لا علامة لطلبية وحيدة، ③ النقر الواحد يُعيد نتيجتين للصفّين الآخرين ويستبعد الذات ويعرض رقمًا ظاهرًا + `showDuplicateScanModal` true، ④ طلبيّات حالة `duplicate` لا علامة مهما بلغ العدد، ⑤ زوج بعمر 35–40 يومًا لا علامة. ملاحظة تقنية مُسجّلة: `created_at` خارج `$fillable` — تزويده في `create()` يُسقَط ويُركَّب `now()` تلقائيًا؛ التعديل بعد الإنشاء بصفّة الحالة (`created_at = …; save()`). السويت الكامل **322 ناجح (1169 assertions)** — صفر انحدار. `view:cache` + `php -l` سليمان.
- **خارج النطاق (لم يُلمس):** `OrderDuplicateService` ومنطق مطابقته، درج التأكيد (P26) ومفاهيمه، صفحة التتبع/المحفوظات، إعدادات `orderColumns` (العلامة ليست عمودًا)، call-center، HR/payroll، ERP، landing-builder، `mystatuskit`، قيم `StorePermissionEnum`. بقية جولة Phase 29 (التروس → توست، تكافؤ بطاقة الموبايل) لم تُلمس بعد؛ سُلِّمت لاحقًا معًا كفرع **29.7**.

## متابعة 29.5a — توحيد تعريف الكشف بين الشارة والنافذة (الرقم + المنتج) + رفع N+1 + حالة فراغ ذكية (جولة Phase 29)

**الحالة: ✅ DONE (2026-09-05) — بموافقة المستخدم بعد تقرير العطل** — إصلاح علة أوضحت نفسها في بيانات حية، لا تخمين.

- **العلة (root cause، ببيانات حية):** الشارة كانت تعدّ طلبيات العميل في نافذة 30 يومًا **بالرقم فقط** (تجميع `customer_id`)، بينما `findSimilar` (نافذة الفحص) تشترط الرقم **والمنتج** — فعندما اشتبه الطرفان (رقم متطابق، منتجات مختلفة) أظهرت الشارة «×N» ونافذة الفحص فارغة. الشاهد الحيّ: 00005/00006 «Demo Merchant» بالهاتف 0669633075، كلاهما `pending`، منتجان مختلفان (variant `01m1ppwymk0j16km3j7dz1fxwh`/product `01m1ppwymg6h4tmpq8364ync8e` مقابل variant `01m1ppwyn0dpax6meb5675470s`/product `01m1ppwymy1gzedek0179d1d0m`) — أيّ `findSimilar` يعيد 0. التعريف المتّفق عليه: **أخو الرقم+المنتج فقط هو «مكرر»**؛ المنتج مختلف ⇒ حالة محايدة تُشرح لا خضراء مضلِّلة.
- **الحل:**
  - `OrderDuplicateService::countsBySiblings(array $orderIds, string $storeId): array` — يبني **استعلامين فقط** (طلبيات نافذة المتجر مرة + أصنافها مرة، فهرس `orders_duplicate_scan`) ويُعين لكل طلبية `same_phone`/`same_product` (الأخوة بنفس `customer_id` == نفس الرقم؛ الذات مستثناة؛ بلا ترشيح حالة — مطابق لِـ `findSimilar`).
  - `loadOrders` يستبدل استعلامه التجميعي بالاستدعاء الجديد: `$arr['duplicate_count'] = same_product` (الشارة) و`$arr['duplicate_phone_count'] = same_phone`. الشارةُ شرطها `>= 1` وسقف عرضها `×9+` عند تجاوز 9.
  - `findSimilar` رُفع عنه N+1: `->with(['status','items'])` — استعلامان بدل N+1.
  - النافذة: حالة فراغ ذكية — إن وُجد رقم مطابق بلا منتج → رسالة محايدة بمفتاح جديد واحد `duplicate_phone_only_orders` ×4 لغات (ar «توجد :count طلبية بنفس الرقم لكن بمنتجات مختلفة»، en «:count order(s) with the same phone but different products»، fr «:count commande(s) avec le même numéro mais des produits différents»، es «:count pedido(s) con el mismo teléfono pero diferentes productos»)؛ الأخضر `no_duplicates` يبقى لحالة عدم وجود أي اشتباه.
- **الأدلة:** `tests/Feature/Merchant/OrderDuplicateBadgeTest.php` أُعيد بناؤه — **6 ناجحة**: ① رقم+منتج ⇒ شارة على الصفّين (`duplicate_warnings_title`) والنافذة تسرد الأخت وتستبعد الذات، ② رقم بمنتجات مختلفة ⇒ صفر شارة (`assertDontSee` للعنوان) + النافذة تعرض `duplicate_phone_only_orders` 👈 يغيّر العلة الحية، ③ طلبية وحيدة بلا شارة، ④ حالة duplicate بلا شارة، ⑤ خارج النافذة (35–40 يومًا) بلا شارة، ⑥ ثلاث طلبيّات ⇒ نتيجتان وأختاه وظهور والدته متوافقان. **ملاحظة تقنية:** فحوص «×N» للمحتوى كانت تتلوّث بعمود الأصناف القائم (`الاسم ×qty`) — فالفحص بعنوان الشارة الفريد `duplicate_warnings_title`. مساعدات `dbbProduct`/`dbbAttachItem` جديدتان (variant قابل لإعادة الاستخدام بين الطلبيّات). تحقّق بيانات حية عبر tinker: لكل أواردر المتجر → 00005/00006 `same_phone=1 same_product=0` (الباقي 0/0). السويت الكامل **323 ناجح (1177 assertions)** — صفر انحدار. `view:cache` + `php -l` سليمان.

## Phase 29.6 — كشف مكرر تلقائي بجانب اسم الزبون: تصنيف ثلاثي (مكررة / احتمال تكرار / سبق الطلب) (جولة Phase 29)

**الحالة: ✅ DONE (2026-09-05) — بقرارات المستخدم الصريحة** — الفرع السادس من جولة Phase 29. اضطرابات التصميم كما طُلب: وشم واحد كحد أقصى لكل صف.

- **تشخيص طلب المستخدم (تحليل مفصل قُدّم ثم اعتمد):** المشكلة توزّع على ثلاث فجوات: ① الكشف لم يكن ناشطًا بصريًا (وهشام بجانب رقم الطلبية — لا يقرأه التاجر كتحذير أصل)، ② تعريف واحد يمزج قضيتين مختلفتين (مكرر فعلي مقابل عميل معايد وضع طلبية لأنه ظنّ شحنته القديمة تأخرت — «لا تهم الفترة»)، ③ لا يوجد مسار «وضع مباشر». قرارا المستخدم عبر أسئلة القرار: «كشف فوري + تأكيد بضغطة» (لا كتابة حالة تلقائية إلا برغبة التاجر؛ بصلاحية ORDER_MANAGE كما هي) و«سبق الطلب = طلبية سابقة وصلت للإرسال» (أي عمر).
- **الحل:**
  - **البيانات (3 استعلامات إجمالًا للصفحة كلها على فهارس قائمة، بلا N+1):** `countsBySiblings` (نافذة 30 يومًا: `same_phone`/`same_product`، من 29.5أ) + **جديد** `OrderDuplicateService::countsPriorCarrierOrders(array $customerIds)` — عدّ طلبيات كل عميل (أي عمر) بلغت الناقل: `shipping_provider_id`/`delivery_rider_id` مُسند أو حالة ضمن `OrderWorkflow::carrier()` (shipped/in_transit/out_for_delivery/delivered/returned). `loadOrders` يحسب `repeat_count` (العد ناقص الطلبية نفسها إن بلغت الناقل — لا تعلِّم ذاتها) و`dup_level` بأولوية واحدة لا أكثر للصف: duplicate > probable > repeat.
  - **الموضع المتفق:** الوشم **بجانب اسم الزبون** (عمود الزبون desktop `flex items-center gap-1.5` بالاسم القابل للقص + وشم `shrink-0`؛ بطاقة الموبايل بجانب الاسم) و**حُذف نهائيًا من بجانب رقم الطلبية**. النغمات: `danger`/«مكررة ×N» (سقف 9+)، `warning`/«احتمال تكرار ×N»، `neutral`/«سبق الطلب» (بلا عدد — معلومة لا اتهام). رسالة خلفية reuse: `duplicate_warnings_title`.
  - **النافذة تصنّف الحالات الشائعة:** شريحة تصنيف في رأسها (نفس النغمات والمفاتيح) + عند `مكررة` قائمة نتائج `findSimilar` (كما هي) + سطر دائم «وضع طلبية سابقة أُرسلت إلى شركة توصيل/موصّل :count» (`dup_repeat_carrier_sent`) متى وُجد تاريخ — حتى في الحالة القوية (لغز «تأخر الشحنة»)؛ وعند `احتمال تكرار` رسالة `duplicate_phone_only_orders` القائمة؛ وعند لا شيء الأخضر `no_duplicates`. `closeDuplicateScanModal` يصفّر `duplicateScanLevel`/`duplicateScanRepeatCount`.
  - **الترجمات:** 4 مفاتيح جديدة ×4 لغات (`dup_badge_duplicate`/`dup_badge_probable`/`dup_badge_repeat`/`dup_repeat_carrier_sent`).
- **الأدلة:** `tests/Feature/Merchant/OrderDuplicateBadgeTest.php` أُعيد بناؤه — **10 ناجحة (64 assertions مع فحصَي الكشف القديمين والتتبع)**:
  1. رقم+منتج ضمن النافذة ⇒ وشم `danger` بجانب الاسم + النافذة تصنّف `duplicate` وتعرض الأخت وتستبعد الذات؛ 2. رقم بمنتجات مختلفة ⇒ وشم `warning` + النافذة `probable` ورسالة المنتجات المختلفة؛ 3. أخ أقدم (60 يومًا) **أُرسل الناقل** («shipped» — المفتاح الصحيح لا «sent») ⇒ وشم `neutral` «سبق الطلب» أيّ عمر + النافذة `repeat` برقم 1؛ 4. أولوية: مكرر قوي + أخ أرسله الناقل ⇒ وشم `danger` لا `neutral`، والنافذة تعرض القائمة **وسطر التاريخ معًا**؛ 5. طلبية `shipped` وحيدة بلا أخ ⇒ لا وشم (استثناء الذات) + `none` أخضر؛ 6. زوج خارج النافذة بحالة عادية ⇒ لا وشم؛ 7. حالة `duplicate` ⇒ لا وشم؛ 8. حذف ناعم ⇒ مستثنى؛ 9. 12 طلبية ⇒ سقف «×9+»؛ 10. تحقق الصفّين بأرقام ظاهرة. **ملاحظة تقنية:** فحوص التسميات المترجمة («Duplicate») تتلوّث بصدفة مع تسميات نطاق الحالات — فالفحص بنغمات `edz-badge` الفريدة في الصفحة + `assertSet` على مفاتيح الحالة. السويت الكامل **326 ناجح (1204 assertions)** — صفر انحدار. `view:cache` + `php -l` سليمان (`git checkout -- storage/framework/views/`).
- **خارج النطاق (لم يُلمس):** كتابة حالة تلقائية (مرفوض من المستخدم)، درج التأكيد (P26)، `markOrderDuplicate`/الصلاحيات، صفحة التتبع، الإعدادات الأخرى. عمود الوشم ليس عمودًا قابلًا للتفعيل. بقية الجولة (29.7 تكافؤ بطاقة الموبايل/التروس → توست عند انتهاء النطاق المتفق) لم تُلمس.

## Phase 29.7 — «التروس → توست» + تكافؤ بطاقة الموبايل (جولة Phase 29)

**الحالة: ✅ DONE (2026-09-05) — الفرعان معًا بقرار المستخدم «الاثنان معًا»** — إغلاق الفرعين المتبقيين اللذين كانت الأسماء فقط في الوثائق (بدون مواصفات): _توحيد توست الإجراءات_ و_تكافؤ البطاقة_.

- **لماذا هذا النطاق:** كان هدفا الفرعين الأصليين «توست بدل الحوارات المزعجة في إجراءات صف الطلبيات» و«كل إجراء/معلومة في صف الجدول متاح من بطاقة الموبايل». الفحص الميداني رصد: ② إجراءات كثيرة تبث نافذيات `swal` (transitionOrder، reassign، delete، restore/trash، guard shipped، قيود المخزون، create/update) بينما الأغلبية تبث `swal:toast` (خليط)، ③ بطاقة الموبايل بلا قائمة انتقالات وبلا تحرير هاتف/ولاية (متاحان في الجدول فقط).
- **الحل:**
  - **التوست الموحّد:** كل إجراءات `orders/index.blade.php` على قناة `swal:toast` (payload `['icon'=>…,'title'=>…]`) — تحويلات: bulkAssignAgent، bulkDelete، restoreOrder/restoreAll/forceDeleteAll، transitionOrder (نجاح/خطأ)، submitReassign، deleteOrder، guardOrderEditable + submitEdit (shipped)، addFormItem/addFormItemByBarcode (out_of_stock/max_qty)، الـ qty في create/edit (insufficient_stock)، submitCreate/submitEdit (نجاح). مع ترجمة كل الحرفيّات: `Select an agent`→`merchant_panel.select_agent` (موجود)، `Unauthorized`→`messages.unauthorized` (⇒ بثلاثة مواضع toast)، `Order created/updated/deleted/reassigned`→مفاتيح جديدة `merchant.order_*` ×4، `Cannot edit shipped/closed orders`→`merchant_panel.cannot_edit_shipped` ×4، واستبدال `$e->getMessage()` في confirm+send/sendConfirmedOrder بـ`order_flow.send_failed` ×4 (مبقين `Log::warning` للتفاصيل). مفاتيح جديدة ×6 ×4 لغات.
  - **تكافؤ البطاقة:** قائمة الانتقالات في شارة حالة بطاقة الموبايل (مطابقة للجدول: `x-data="orderRowActions($el)"` المشترك — `openStatusMenu()`/`open`/`top`/`left`/`$refs.trigger`، `@click.away`، `fixed z-[200]`، spinning each `transitionOrder` بمعرّف الطلبية) + تحرير الهاتف inline (محرر `phone-inline-card-{id}` — `wire:key` مفرَّق عن `phone-inline-{id}` في الجدول لأن الخلوّي يُصيّر كليهما في طلب واحد، منعًا لتعارض Livewire) + تحرير الولاية inline (`wilaya-inline-card-{id}`، `w-full min-w-[180px]` داخل صف الرقائق). كلاهما تحت `ORDER_MANAGE`.
- **الأدلة:** `tests/Feature/Merchant/OrderActionToastUnificationTest.php` — **7** (bulk assign، bulk delete، single delete، restore، transition نجاح + transition خطأ بدون تغيير حالة، reassign) و`tests/Feature/Merchant/OrderMobileCardParityTest.php` — **4** (مشغّل `openStatusMenu()` مرتين = جدول+بطاقة، محرّر الهاتف يظهر/يُلغى في البطاقة، محرّر الولاية يظهر/يُلغى، وصول الهاتف بالصلاحية). ترحيل 6 تأكيدات قديمة `assertDispatched('swal', type: …)` في OrderInlineEditTest/OrderOfficeSelectionTest/OrderQuantityCapTest إلى `swal:toast`. **ملاحظة تقنية:** `dispatch('swal:toast', ['icon'=>…])` يُبث كمعامل مرصوص واحد ⇒ الفحص بـ closure: `fn($n,$p) => ($p[0]['icon'] ?? null) === 'error'`. السويت الكامل **337 ناجح (1224 assertions)** — صفر انحدار. `php -l` للملفات الـ12 + `view:cache` سليمان (`git checkout -- storage/framework/views/`).
- **إغلاق توست-403 (طلب «أكمل كل ما يتعلق بـ 29» — قرار مستخدم «تحويل تدفق الإرسال فقط»):** الحارس من الفقرة 29.2 المذكور أعلاه — `abort_unless(canStore(ORDER_MANAGE), 403)` — تحوّل إلى توست `messages.unauthorized` في **إجراءات تدفق الإرسال الأربعة**: `sendConfirmedOrder` (1228)، `submitConfirmAndSend` (1177)، `openBulkSendModal` (652)، `confirmBulkSend` (760). بقي نمط «if (! canStore()) → toast + return». تُركت بقية حواريز الصفحة على 403 الصريح (القراءة/الإسناد/الحذف/التحويل/الجماعي/التعديل) بإبقائها حاجزًا أمنيًا صلبًا متعمَّدًا. الاختباران المتأثران نُقِّحا من `assertStatus(403)` إلى `assertDispatched('swal:toast', … messages.unauthorized)`: `DirectSendConfirmedOrderTest` (approved_item ①) و`BulkSendCarrierGroupingTest` (①). المستهدفان **16/16 ناجحة (64 assertions)** + السويت الكامل **337 ناجح (1224 assertions)** — صفر انحدار.
- **خارج النطاق (لم يُلمس):** نافذيات `swal` في صفحات أخرى (returns/tracking/delivery/riders/storefront-settings) — لا تزال على النمط القديم (خارجة عن نطاق صفحة الطلبيات)، درج التأكيد (P26)، مصدر `markOrderDuplicate`، حواريز 403 خارج تدفق الإرسال، جولات ما بعد Phase 29.

---

## تذييل 2026-09-06 — **جولة البرومت ذي الترقيم المحلّي** (تداخل التسميات موثّق؛ يُقرأ هذا التذييل فوق كل ما قبله)

> تداخل: البرومت الحالي رقم «29.4–29.7» بأربعة بنود **مختلفة عن** أقسام هذا الملف (التي أكملت سابقًا ما سمّته 29.4/29.5/29.6/29.7 = عمود التتبع/شارة التكرار/التصنيف الثلاثي/التروس→توست). بنود البرومت الأربعة نُفِّذت **كاملةً فوق** المحتوى السابق دون كسر أي اختبار:

- **البرومت 29.4 — سجل التتبع نافذة (page tracking) + سجل أحداث الطلبية قائمة/نافذة (orders):** بعد فحص النطاق تبين أن سجل حالة التتبع يخص صفحة التتبع (الطلب بعد الإرسال = صفحة التتبع)، فأُبقيَت شارات التتبع في صفحة الطلبيات معلوماتية، ووُضع في صفحة الطلبيات قائمة سجل أحداث الطلبية. **التجزئة المنجزة:** `orders/partials/order-events-menu.blade.php` (قائمة ≤4 أحداث + «عرض المزيد» → `order-events-modal.blade.php`) + `tracking/partials/tracking-history-popup.blade.php` (يعيد استخدام `tracking-history-timeline`) — بلا حشر في index. مكوّن Alpine `orderEventsMenu(el)` يقرأ `el.dataset.orderId`/`canView` (سليم مع `BladeInteractivityPolicyTest` — لا `@js` داخل خصائص Alpine). الحارس `can_view_events` لكل صف (OWNER/ADMIN دومًا، MANAGER للمعيَّن، STAFF أبدًا) + `messages.permission_denied` عند الرفض؛ قسم الأحداث **حُررت من درج التفاصيل**. **19 ناجح (63 assertions)** عبر `TrackingStatusHistoryPopupTest` + نقحة `OrderEventLogVisibilityTest` + البنيوي في `OrdersTrackingColumnTest`.
- **البرومت 29.5 — فحص عدد الاستعلامات:** `OrdersPageQueryCountTest` — عدّ **استعلامات البيانات فقط** (orders/order_items/customers/order_trackings/shipping_providers/product_variants/products) لصفحة 6 مقابل 16 طلبية ⇒ فرق ≤2. **1 ناجح (3 assertions)**.
- **البرومت 29.6 — توحيد الحرّاس توست بدل 403 (الكل):** تحويل كل `abort_unless(...403)` في إجراءات orders index (دفق الإرسال + delete/assign/confirm/manage/transition) إلى `if (! canStore(...)) → توست `messages.permission_denied` + return`؛ بقي 403 في `mount` فقط (مستوى صفحة). حارس `HasInlineEdit::saveEdit` (البنية المشتركة brand/product/order) تحوّل هو الآخر. **لا بقايا `messages.unauthorized` في livewire/merchant.**
- **البرومت 29.7 — قائمة «المزيد» المتنقلة:** أيقونة `general.more` (ellipsis) في بطاقة الموبايل → لوحة `orderMoreMenu` (fixed top/left، `@click.away`) صفوف confirm/send/edit/reassign/delete عند `ORDER_*` المناسب **بأهداف 44px** (`min-h-[44px]`) تُغلق بعد الفعل (`confirmDelete(); close()`). `OrdersMobileMoreMenuTest` **2** (مالك: menu+44px وجميع الصفوف؛ STAFF: menu بلا أي صف).
- **التحقّق النهائي:** السويت كاملة **348 ناجح (1268 assertions)** — صفر انحدار. `npm run build` ناجح، `view:cache` ناجح + `git checkout -- storage/framework/views/`.
