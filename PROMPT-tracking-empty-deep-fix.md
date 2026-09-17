# برومت — فحص عميق منظم لانحدار صفحة التتبع والتخطيط لإصلاحها

> اقرأ هذا النص كاملًا قبل بدء أي عمل. هذا البرومت موجَّه لذكاء اصطناعي في جلسة مستقلة.
> مهمتك: **فحص عميق منظم للمشروع كاملًا**، ثم **إنتاج خطة إصلاح منظمة في ملف `.md` منفصل**.

---

## 1) المهمة والأهداف

المنتجات: `app/`, `resources/`, `database/`, `tests/`, وملف `Todos.md` (لعمل حدث بالفعل مؤخرًا).

الوضع الحالي (الانحدار الحرج):

> صفحة تتبع الطلبيات (`merchant.tracking.index`) **لا تعرض أي طلبيات** — لا في تبويب شركات التوصيل (`carrier`) ولا في تبويب راجل التوصيل (`rider`) — بعد تعديلاتٍ جرت مؤخرًا (آخرها كوميت `8e121b3`).

أهدافك بالترتيب:

1. إعادة إنتاج الانحدار وتوثيق دليلٍ قاطع عليه.
2. فحص متعمق لسلسلة الاستعلام أمام قاعدة البيانات الحقيقية (وليس فقط بقراءة الكود).
3. التأكد من السبب الجذري (فرضية مرفقة أدناه — يجب التحقق منها بدلًا من افتراضها).
4. رصد انحدارات/ملاحظات ثانوية قد تُفشل الاختبارات أو الانعكاسات.
5. إنتاج خطة إصلاح منظمة في ملف `.md` **منفصل** (الاسم المقترح أدناه).

**قيود إلزامية:**

- هذه الجلسة **فحص وتخطيط فقط**: لا تعدّل ملفات المصدر ولا migrations ولا الاختبارات.
- لا تُنهِ الجلسة قبل كتابة ملف الخطة.
- استخدم موارد المشروع الفعلية؛ لا تفترض وجود مكتبات غير مثبتة.

---

## 2) فحص أولي إلزامي (اقرأ قبل أي استنتاج)

- `Todos.md` — سجّل الإصلاحات الثلاثة الأخيرة (مزامنة NOEST 404، أرقام الطلبيات في رسالة المجهول، إلغاء إرسال شحنة carrier-unknown) وخطة التخصيص الست.
- `git log --oneline -15` و `git show 8e121b3 --stat` — لفهم آخر تغيير.
- `storage/logs/laravel.log` — ابحث عن `local.ERROR` خلال مسار صفحة التتبع تحديدًا.

---

## 3) الفرضية المدعومة بالدليل (تحقَّق منها، لا تعتمد عليها)

التهمة المباشرة: تغيير سطرٍ واحد في `app/Livewire/Concerns/TrackingGridConcern.php` داخل `baseTrackingQuery()`:

```diff
- $trackingStatusIds = \App\Models\Status::system()->forType('order')
+ $trackingStatusIds = \App\Models\Status::system()->forType('tracking')
            ->whereIn('key', \App\Domains\Order\Support\OrderWorkflow::carrier())
            ->pluck('id')->all();
```

**إثبات نُفّذ بالفعل (يجب إعادة توليده بنفسك):**

- الطلبيات في DB تحمل حالات **نوع `order`** (`Status::type = 'order'`؛ أيدٍ مثل `shipped → 01m2qngwyr4...`).
- الفلتر الآن يجمع **نوع `tracking`** (أيدٍ مختلفة: `shipped → 01m2qngwzwjf...`).
- `orders.status_id` لا يطابق أبدًا معرّفات نوع `tracking` → `0` صف للتبويب carrier.
- تبويب rider يطالب `delivery_rider_id NOT NULL` **مع نفس مرشّح الحالات** → 0 صف أيضًا.
- البيانات التوضيحية: متجر `Edzeery Demo Store` (id `01m2qnh10v7w009ffhz1h7qbtn`) فيه طلبيتان:
  - `00001` → provider `NOEST`، الحالة `shipped` (order-type) **كانت ستظهر قبل الانحدار** (السطر القديم `forType('order')` يجعلها تطابق).
  - `00002` → provider `NOEST`، الحالة `pending` — ملاحظة ثانوية (ليست ضمن `OrderWorkflow::carrier()`) ويجب معالجتها في الخطة.
- `git log -S "forType('tracking')"` يُظهر أنها أُدخلت في `8e121b3` (قبلها `forType('order')` موجود منذ `17f72eb`).

**ملاحظة دلالية:** حالات `tracking` تُستعمل لصفوف `order_trackings.tracking_status` (قيم keys)، بينما `orders.status_id` يحمل حالات `order` — تأكّد من هذا التمييز في الكود قبل اعتماده في الخطة.

---

## 4) خطوات إعادة الإنتاج الإلزامية

1. **بيئة التشغيل (Windows / Laragon):** PHP من `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe`، واختر سكربت مستقل (لا `--execute` مع `$` في PowerShell) يستدعي:
   - `vendor/autoload.php` ثم `bootstrap/app.php` ثم `->make(Console\Kernel::class)->bootstrap()`، لبدء سياق Laravel.
2. **استعلام التشخيص (أعد إنتاجه):**
   - `Status::system()->forType('order')->whereIn('key', OrderWorkflow::carrier())->pluck('id')` → قارنه مع `forType('tracking')`.
   - لكل متجر: عدد `orders` مع `shipping_provider_id NOT NULL`, مع `delivery_rider_id NOT NULL`, ثم تطبيق `whereIn('status_id', $ids)` للنسختين.
3. **تحقق طبقي:** نفّذ `baseTrackingQuery()` عبر مكوّن فولت (اختبار Livewire/Volt) ليسجّل `filteredTotal` — تحقق أنه `0` بالنسخة الحالية و`>= 1` بعد استعادة `forType('order')`.
4. **اختبارات الانحدار:** شغّل مجموعة الاختبارات ذات الصلة للبناء على خط الأساس:
   - `vendor/bin/pest` للملفين:
     - `tests/Feature/Merchant/TrackingTrashWebhookLabelTest.php`
     - `tests/Feature/Shipping/ShipmentCancelCarrierUnknownTest.php`
     - `tests/Feature/Shipping/NoestTrackingSyncTest.php` و `NoestTrackingSyncServiceTest.php`
   سجّل النتائج قبل/بعد كل تجربة في جدولٍ بالخطة.
5. **فحص الجانب الأمامي:** تحقق من أن المشكلة ليست تجميعًا (compiled views) — راجع `config/view.php`, ومجلد `storage/framework/views` (يُلاحظ أن الريبو يتتبع بعضها) إن لزم.

---

## 5) نقاط تُفحص وتُدرج في الخطة (لا تفترض فيها ما لم تتحقق)

- **تصنيف المجموعات الثلاث (خلط يجب حسمه وتوثيقه في الخطة):** حالات تأكيد/معالجة الطلبية (type=order, مفاتيح `OrderWorkflow::backOffice()`), حالات التوصيل (type=order, مفاتيح `OrderWorkflow::carrier()`), وحالات التتبع (type=tracking). الصورة المرجعية في `database/seeders/SystemStatusesSeeder.php`. وثّق مكان تخزين كل مجموعة (`orders.status_id` مقابل `order_trackings.tracking_status`) وعلاقتها بالصفحات (الطلبيات/التتبع) وطوّر جدولًا بها، وأتمِ مسحًا كاملًا لـ `forType(` في `app/` تثبت به ألا خلط آخر غير السطر المُصلَح.
- هل يُقصد بـ`forType('order')` استهداف حالات الطلبات؟ راجع `app/Domains/Order/Support/OrderWorkflow.php` وطريقة وضع `status_id` للطلبات (مثلاً عند إرسال الشحنة لشركة التوصيل).
- هل يجب أن يُصفّى تبويب rider بحالات carrier أيضًا؟ (سلوك قائم ومُعلَّق في الخطة)
- ملاحظة الثانوية: الطلب `00002` بحالة `pending` رغم وجود `shipping_provider_id` — هل يُفترض أن يظهر في التتبع؟ ضع توصية واضحة.
- تأثير الإصلاح على: عدّادات `loadTrackingStats()`، سلة المحذوفات (`showTrash`) — كلها تشتق من نفس `baseTrackingQuery()`, أي أن الإصلاح سيُصلحها جميعًا دفعةً واحدة — أثبت ذلك.
- لا حاجة لتغيير schema/migrations.

---

## 6) مخرجات مطلوبة (إلزامية)

أنشئ ملف: **`FIXPLAN-tracking-empty-grid.md`** في جذر المشروع:

يتضمّن (بالعربية، منسّق بلغة Markdown واضحة):

1. **الخلاصة التنفيذية** (السبب الجذري في جملة أو اثنتين).
2. **سجل الأدلة** (أوامر/نتائج/تحليل البيانات — كما أعدت إنتاجه فعليًا).
3. **السبب الجذري** (بإسهاب مع ملف:سطر).
4. **خطة الإصلاح** (منظمة بمراحل، كل مرحلة تذكر الملف وسطر التغيير والنص المقترح، مع أولوية).
5. **اختبارات الإثبات** (نص/وصف اختبار يجب كتابته أو تشغيله — بما فيها اختبار يضمن ألا تعود `forType('order')` إلى `tracking`).
6. **معايير القبول** (checklist — ماذا يعني أن المشكلة أُصلحت فعليًا).
7. **مناطق الانحدار المحتملة** (ماذا قد ينكسر، وكيف يُكتشف مبكرًا).

لا تكتب أسماء ملفات تخمينية إلا بعد التأكد من وجودها عبر `glob`/`grep`. لا تعدّل أي ملف مصدر.

---

**تذكير أخير:** أنت جلسة فحص وتخطيط فقط — أَنجِز الخطة في الملف المطلوب ثم سلّمها دون أي تعديل على ملفات المشروع.