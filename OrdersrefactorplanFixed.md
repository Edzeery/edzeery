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
