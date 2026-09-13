# توثيق تكامل شركة التوصيل NOEST
## NOEST Public API — الإصدار 2.3

**تاريخ الوثيقة:** 17 يوليو 2026  
**آخر تحديث موثق:** مايو 2026

---

## 1. نظرة عامة

يتيح **NOEST Public API** للتطبيقات والمنصات المتكاملة مع شركة **NOEST** إدارة عمليات الشحن والتوصيل آليًا، بدءًا من إنشاء الطلبات وإدارتها، مرورًا بالتحقق والتعديل والحذف، وصولًا إلى التتبع وإدارة محاولات التسليم والمرتجعات.

### معلومات الاتصال الأساسية

| العنصر | القيمة |
|---|---|
| Base URL | `https://app.noest-dz.com` |
| تنسيق البيانات | JSON |
| نظام المصادقة | Bearer Token |
| `api_token` | رمز المصادقة المقدم من NOEST |
| `user_guid` | معرف الشريك المقدم من NOEST |

يتم تزويد الشريك بقيمتي `api_token` و`user_guid` عند إنشاء حسابه لدى NOEST.

---

# 2. المصادقة Authentication

جميع نقاط API تتطلب المصادقة باستخدام **Bearer Token**.

يجب إرسال رأس الطلب التالي:

```http
Authorization: Bearer {api_token}
Content-Type: application/json
```

كما يتطلب معظم عمليات API إرسال:

```text
user_guid
```

وهو **Partner GUID** الخاص بحساب الشريك لدى NOEST.

---

# 3. إدارة الطلبات Order Management

## 3.1 إنشاء طلب واحد

### Endpoint

```http
POST /api/public/create/order
```

### الغرض

إنشاء طلب توصيل جديد لدى NOEST.

### الحقول

| الحقل | النوع | مطلوب | الوصف |
|---|---|---:|---|
| `user_guid` | string | نعم | معرف الشريك المقدم من NOEST |
| `reference` | string | لا | مرجع الطلب، بحد أدنى 5 أحرف |
| `client` | string | نعم | الاسم الكامل للعميل، بحد أقصى 255 حرفًا |
| `phone` | string | نعم | رقم الهاتف، من 9 إلى 10 أرقام |
| `phone_2` | string | لا | رقم هاتف إضافي |
| `adresse` | string | نعم | عنوان التوصيل، بحد أقصى 255 حرفًا |
| `wilaya_id` | integer | مشروط | معرف الولاية من 1 إلى 58 |
| `commune` | string | مشروط | اسم البلدية |
| `montant` | numeric | نعم | مبلغ الطلب |
| `remarque` | string | لا | ملاحظات، بحد أقصى 255 حرفًا |
| `produit` | string | نعم | اسم أو مرجع المنتج، ويمكن إرسال عدة منتجات مفصولة بفواصل |
| `type_id` | integer | نعم | نوع العملية |
| `poids` | numeric | لا | وزن الطرد |
| `stop_desk` | integer | نعم | نوع التوصيل: 0 للمنزل، 1 إلى نقطة Stop Desk |
| `station_expedition` | integer | لا | رمز محطة الإرسال، ويتطلب تفعيل الميزة للحساب |
| `station_code` | string | مشروط | رمز نقطة Stop Desk، مطلوب عند `stop_desk=1` |
| `stock` | integer | لا | إدارة المخزون: 0 بدون مخزون، 1 مع المخزون |
| `quantite` | string | مشروط | الكميات مفصولة بفواصل، مطلوبة عند `stock=1` |
| `shop_name` | string | لا | اسم المتجر |
| `zip_code` | string | لا | الرمز البريدي، ويحل محل `wilaya_id` و`commune` |
| `remboursement` | integer | لا | الاسترداد/التحصيل: 0 تعطيل، 1 تفعيل |

### أنواع الطلبات

| `type_id` | النوع |
|---:|---|
| `1` | توصيل عادي |
| `2` | استبدال |
| `3` | استلام الطرد من العميل |



### مثال طلب

```json
{
    "user_guid": "abc123-def456-ghi789",
    "reference": "REF12345",
    "client": "Ahmed Ahmed",
    "phone": "0550505050",
    "phone_2": "0660606060",
    "adresse": "Rue des Martyrs, Bab Ezzouar",
    "wilaya_id": 16,
    "commune": "Bab Ezzouar",
    "montant": 3500,
    "produit": "Smartphone Samsung Galaxy",
    "type_id": 1,
    "poids": 0.5,
    "stop_desk": 0,
    "remarque": "Call before delivery"
}
```

### الاستجابة الناجحة

```json
{
    "success": true,
    "tracking": "ECS1234567890",
    "reference": "REF001",
    "regional_hub_name": "W",
    "wilaya_rank": "16B"
}
```

عند نجاح الإنشاء يتم إرجاع رقم التتبع `tracking` الذي يستخدم لاحقًا في عمليات التحقق والتعديل والتتبع وغيرها.

---

# 4. إنشاء عدة طلبات دفعة واحدة

يسمح API بإنشاء عدة طلبات ضمن طلب HTTP واحد.

### Endpoint

```http
POST /api/public/create/orders
```

### الحدود

- الحد الأدنى: طلب واحد.
- الحد الأقصى: **100 طلب في الطلب الواحد**.

يستخدم كل عنصر داخل `orders` نفس بنية طلب الإنشاء الفردي.

### مثال

```json
{
    "user_guid": "abc123-def456-ghi789",
    "orders": [
        {
            "reference": "REF001",
            "client": "Ahmed Ahmed",
            "phone": "0550000000",
            "adresse": "Rue des Martyrs, Bab Ezzouar",
            "wilaya_id": 16,
            "commune": "Bab Ezzouar",
            "montant": 3500,
            "produit": "Smartphone Samsung",
            "type_id": 1,
            "stop_desk": 0,
            "poids": 0.5
        },
        {
            "reference": "REF002",
            "client": "Fatima Fatima",
            "phone": "0770000000",
            "adresse": "Cité 300 logements",
            "zip_code": "16000",
            "montant": 2000,
            "produit": "Bluetooth Earphones",
            "type_id": 1,
            "stop_desk": 0
        }
    ]
}
```

### استجابة النجاح

تعرض الاستجابة الطلبات التي تم إنشاؤها بنجاح داخل `passed`، بينما تعرض الطلبات الفاشلة داخل `failed`.

```json
{
    "success": true,
    "passed": {
        "0": {
            "success": true,
            "tracking": "TRK123456789"
        },
        "1": {
            "success": true,
            "tracking": "TRK987654321"
        }
    },
    "failed": {}
}
```

في حالة فشل أحد الطلبات، يمكن أن تصبح `success=false` مع بقاء الطلبات الناجحة ضمن `passed` وتفاصيل الأخطاء ضمن `failed`.

---

# 5. التحقق من الطلب Validate Order

بعد إنشاء الطلب، يمكن التحقق منه وإرساله إلى مرحلة المعالجة اللوجستية.

> بعد التحقق من الطلب، يصبح ظاهرًا لعمليات اللوجستيك ولا يمكن تعديله أو حذفه.

### Endpoint

```http
POST /api/public/valid/order
```

### البيانات المطلوبة

```json
{
    "user_guid": "abc123-def456-ghi789",
    "tracking": "TRK123456789"
}
```

### الاستجابة

```json
{
    "success": true
}
```

### أبرز الأخطاء

- الطلب غير موجود أو لا ينتمي إلى الشريك.
- الطلب تم التحقق منه مسبقًا.
- المخزون غير كافٍ عند تفعيل إدارة المخزون.

---

# 6. التحقق من عدة طلبات

### Endpoint

```http
POST /api/public/valid/orders
```

### الحد الأقصى

**100 رقم تتبع في الطلب الواحد.**

### مثال

```json
{
    "user_guid": "abc123-def456-ghi789",
    "trackings": [
        "TRK123456789",
        "TRK987654321",
        "TRK555666777"
    ]
}
```

الاستجابة تحتوي على:

- `passed`: الطلبات التي تم التحقق منها.
- `failed`: الطلبات التي تعذر التحقق منها مع سبب الفشل.

---

# 7. تعديل الطلب

يتم استخدام هذا الـ endpoint لإنشاء **طلب تعديل** على طلب موجود.

### Endpoint

```http
POST /api/public/update/order
```

### ملاحظة مهمة

الحقل `tracking` إلزامي، ولا يمكن تغيير الولاية من خلال هذا الـ endpoint.

### الحقول

| الحقل | الوصف |
|---|---|
| `tracking` | رقم تتبع الطلب — مطلوب |
| `tel` | رقم هاتف جديد |
| `adresse` | عنوان جديد |
| `wilaya` | يجب أن تطابق ولاية الطلب الحالية |
| `commune` | البلدية الجديدة |
| `montant` | المبلغ الجديد |
| `type` | نوع الشحنة |
| `stop_desk` | 0 توصيل منزلي، 1 Stop Desk |
| `code_station` | رمز المحطة عند استخدام Stop Desk |

### قواعد التعديل

- إذا كان الطلب في حالة **En livraison**، فلا يمكن تعديل سوى `type` و`montant`.
- عند التوصيل المنزلي يجب إرسال `commune` و`adresse` معًا.
- عند التحويل إلى Stop Desk يجب إرسال `code_station`.
- يجب أن تنتمي محطة Stop Desk إلى نفس الولاية الخاصة بالطلب.
- يتم رفض الطلب إذا لم يتم إرسال أي حقل قابل للتعديل.

---

# 8. تعديل الطلب قبل الإرسال

هذا الـ endpoint يقوم بتعديل الطلب مباشرة، بشرط ألا يكون قد تم شحنه بعد.

### Endpoint

```http
POST /api/public/update/order/before/expedition
```

### الحقول القابلة للتعديل

- `tracking`
- `reference`
- `client`
- `tel`
- `tel2`
- `adresse`
- `wilaya`
- `commune`
- `montant`
- `remarque`
- `product`
- `type`
- `poids`
- `stop_desk`

الـ `tracking` إلزامي، ويجب أن يكون الطلب تابعًا للشريك وألا يكون قد تم شحنه بعد.

---

# 9. حذف الطلب

يمكن حذف الطلب فقط إذا كان **غير متحقق منه (Unvalidated)**.

### Endpoint

```http
POST /api/public/delete/order
```

### البيانات

```json
{
    "user_guid": "abc123-def456-ghi789",
    "tracking": "TRK123456789"
}
```

### الاستجابة

```json
{
    "success": true
}
```



---

# 10. إضافة ملاحظة إلى الطلب

يسمح بإضافة تحديث أو ملاحظة إلى سجل الطلب.

### Endpoint

```http
POST /api/public/add/maj
```

### البيانات

```json
{
    "tracking": "TRK123456789",
    "content": "Customer prefers afternoon delivery"
}
```

الحد الأقصى للملاحظة هو **255 حرفًا**.

---

# 11. طلب محاولة توصيل جديدة

لإرسال طلب بمحاولة توصيل جديدة:

### Endpoint

```http
POST /api/public/ask/new-tentative
```

### البيانات

```json
{
    "tracking": "TRK123456789"
}
```



---

# 12. طلب إرجاع الشحنة

لإنشاء طلب إرجاع للشريك:

### Endpoint

```http
POST /api/public/ask/return
```

### البيانات

```json
{
    "tracking": "TRK123456789"
}
```



---

# 13. تحميل وصل التوصيل

يمكن تحميل ملصق/وصل التوصيل بصيغة PDF.

### Endpoint

```http
GET /api/public/get/order/label
```

### مثال

```http
GET /api/public/get/order/label?tracking=TRK123456789
```

### النتيجة

يتم إرجاع ملف PDF قابل للتنزيل يحتوي على **Delivery Label** الخاص بالطلب.

---

# 14. تتبع الطلبات

يمكن الحصول على المعلومات التفصيلية وسجل الأحداث لعدة طلبات في طلب واحد.

### Endpoint

```http
POST /api/public/get/trackings/info
```

### البيانات

```json
{
    "trackings": [
        "TRK123456789",
        "TRK987654321"
    ]
}
```

تتضمن الاستجابة معلومات مثل:

- رقم التتبع.
- مرجع الطلب.
- اسم العميل.
- أرقام الهاتف.
- العنوان.
- الولاية والبلدية.
- المبلغ.
- المنتج.
- اسم السائق.
- رقم هاتف السائق.
- نوع الطلب.
- نوع التوصيل.
- تاريخ الإنشاء.
- سجل الأحداث.
- محاولات التوصيل.

---

# 15. سجل أحداث الطلب

يوفر NOEST مجموعة من الأحداث التي تصف دورة حياة الطلب، ومن أبرزها:

| الحدث | الوصف |
|---|---|
| `upload` | إنشاء/رفع الطلب إلى النظام |
| `customer_validation` | التحقق من الطلب |
| `validation_collect_colis` | استلام الطرد |
| `validation_reception_admin` | تأكيد الاستلام من الإدارة |
| `validation_reception` | استلام الطرد من طرف السائق |
| `fdr_activated` | خروج الطلب للتوصيل |
| `sent_to_redispatch` | إعادة توجيه الطلب |
| `nouvel_tentative_asked_by_customer` | طلب محاولة توصيل جديدة |
| `return_asked_by_customer` | طلب الإرجاع |
| `return_asked_by_hub` | الإرجاع قيد التنفيذ |
| `return_dispatched_to_partenaire` | إرسال المرتجع إلى الشريك |
| `colis_retour_transmit_to_partner` | تسليم المرتجع للشريك |
| `pickedup` | إتمام عملية الاستلام |
| `colis_suspendu` | تعليق الطلب |
| `livre` / `livred` | تم التسليم |
| `verssement_admin_cust` | تحويل المبلغ إلى الشريك |
| `echange_valide` | تأكيد عملية الاستبدال |
| `edited_informations` | تعديل معلومات الطلب |
| `edit_price` | تعديل السعر |
| `edit_wilaya` | تعديل الولاية |
| `extra_fee` | رسوم إضافية |
| `mise_a_jour` | محاولة توصيل |



---

# 16. البيانات المرجعية Reference Data

## 16.1 قائمة نقاط Stop Desk

### Endpoint

```http
GET /api/public/desks
```

يعيد قائمة نقاط التوصيل المتاحة، بما في ذلك:

- رمز النقطة.
- الاسم.
- العنوان.
- الخريطة إن توفرت.
- أرقام الهاتف.
- البريد الإلكتروني.

---

## 16.2 قائمة أسعار التوصيل

### Endpoint

```http
GET /api/public/fees
```

يعيد جدول الأسعار المخصص للشريك.

يحتوي على:

```text
delivery
return
tarif
tarif_stopdesk
```

حيث:

- `tarif`: سعر التوصيل المنزلي بالدينار الجزائري.
- `tarif_stopdesk`: سعر التوصيل إلى نقطة Stop Desk بالدينار الجزائري.
- `return`: أسعار الإرجاع حسب الولاية.
- `delivery`: أسعار التوصيل حسب الولاية.

---

## 16.3 قائمة البلديات

### Endpoint

```http
GET /api/public/get/communes/{wilaya_id}
```

يمكن استخدامه لجلب:

- جميع البلديات.
- بلديات ولاية محددة.
- الرمز البريدي.
- حالة تفعيل البلدية.

### أمثلة

جميع البلديات:

```http
GET /api/public/get/communes
```

بلديات ولاية محددة:

```http
GET /api/public/get/communes/5
```



---

## 16.4 قائمة الولايات

### Endpoint

```http
GET /api/public/get/wilayas
```

يعيد قائمة الولايات الجزائرية، مع:

- رمز الولاية.
- اسم الولاية.
- حالة التفعيل.

---

# 17. الأخطاء Validation & Business Errors

تنقسم أخطاء NOEST إلى فئتين رئيسيتين:

### أخطاء التحقق Validation Errors

من أمثلتها:

- `user_guid` مفقود أو غير صالح.
- اسم العميل مفقود أو يتجاوز 255 حرفًا.
- رقم الهاتف مفقود أو بتنسيق غير صالح.
- العنوان مفقود أو طويل جدًا.
- الولاية غير صالحة.
- البلدية غير موجودة.
- المبلغ مفقود أو غير رقمي.
- المنتج مفقود.
- نوع الطلب غير صالح.
- الوزن غير صالح.
- `stop_desk` بقيمة غير `0` أو `1`.
- `station_code` مفقود عند استخدام Stop Desk.
- `reference` أقل من 5 أحرف.
- `remboursement` بقيمة غير `0` أو `1`.

### أخطاء الأعمال Business Errors

من أبرزها:

| الخطأ | الوصف |
|---|---|
| `account_suspended` | حساب الشريك موقوف |
| `duplicate_order` | الطلب موجود مسبقًا |
| `inactive_commune` | البلدية غير موجودة أو غير مفعلة |
| `zip_code` | الرمز البريدي غير صالح |
| `max_amount_exceeded` | المبلغ يتجاوز الحد المسموح |
| `stopdesk_disabled` | Stop Desk غير متاح في الولاية |
| `station_expedition` | محطة الإرسال غير صالحة |
| `station_code` | رمز المحطة لا يتوافق مع الولاية |
| `disabled_module` | وحدة إدارة المخزون غير مفعلة |
| `wrong_quantities` | عدد الكميات لا يطابق عدد المنتجات |
| `invalid_product` | المنتج غير موجود أو غير مفعل |
| `out_of_stock` | المخزون غير كافٍ |
| `already_validated` | الطلب تم التحقق منه مسبقًا |



---

# 18. القيود التقنية

| العنصر | الحد / القيمة |
|---|---|
| إنشاء الطلبات بالجملة | 100 طلب كحد أقصى |
| التحقق بالجملة | 100 رقم تتبع كحد أقصى |
| معدل الطلبات | 60 طلبًا في الدقيقة افتراضيًا |
| المصادقة | Bearer Token إلزامي |
| Timeout | 30 ثانية لكل طلب |

في عمليات الـ Bulk، إذا فشل طلب واحد على الأقل فإن قيمة `success` تصبح `false`، مع الاحتفاظ بالطلبات الناجحة داخل `passed` والفاشلة داخل `failed`.

---

# 19. دورة العمل الموصى بها

تسلسل التكامل الأساسي مع NOEST:

```text
1. إنشاء الطلب
   ↓
2. التحقق من بيانات الطلب
   ↓
3. تعديل الطلب عند الحاجة
   ↓
4. التحقق من الطلب Validate
   ↓
5. تحميل وصل التوصيل
   ↓
6. تتبع الطلب
```

### Endpoints المستخدمة

```text
POST /api/public/create/order
POST /api/public/create/orders

POST /api/public/update/order

POST /api/public/valid/order
POST /api/public/valid/orders

GET  /api/public/get/order/label

POST /api/public/get/trackings/info
```



---

# 20. قواعد Stop Desk

عند استخدام:

```text
stop_desk = 1
```

يصبح:

```text
station_code
```

إلزاميًا.

ويجب أن يتوافق رمز المحطة مع **ولاية الوجهة**.

يمكن الحصول على قائمة المحطات ورموزها من:

```http
GET /api/public/desks
```



---

# 21. رمز محطة الإرسال Expedition Station

يسمح الحقل:

```text
station_expedition
```

بتحديد محطة إرسال معينة للطلب.

لكن هذه الخاصية يجب أن تكون **مفعلة مسبقًا على حساب الشريك**، وإلا سيتم رفض القيمة المرسلة.

---

# 22. الرمز البريدي ZIP Code

عند إرسال:

```text
zip_code
```

فإنه يحل تلقائيًا محل:

```text
wilaya_id
commune
```

ويجب أن يكون الرمز البريدي موجودًا في قاعدة بيانات NOEST.

يمكن استخدام قائمة البلديات للحصول على الرموز البريدية الصحيحة:

```http
GET /api/public/get/communes
```



---

# 23. إدارة المخزون

عند:

```text
stock = 1
```

يصبح الحقل:

```text
quantite
```

إلزاميًا.

يجب فصل المنتجات بفواصل داخل:

```text
produit
```

وفصل الكميات المقابلة بفواصل داخل:

```text
quantite
```

### مثال

```text
produit="PROD001,PROD002"
quantite="2,3"
```

أي:

```text
PROD001 → الكمية 2
PROD002 → الكمية 3
```

ويجب أن يتطابق عدد الكميات مع عدد المنتجات.

---

# 24. أنواع العمليات

### النوع 1 — Delivery

توصيل عادي مع تحصيل المبلغ من العميل.

### النوع 2 — Exchange

عملية استبدال المنتج مع العميل.

### النوع 3 — Pick-up

استلام طرد من العميل، ويكون مبلغ الطلب **مجبراً على القيمة 0**.

---

# 25. الاسترداد والتحصيل

الحقل:

```text
remboursement
```

يحدد تفعيل وظيفة الاسترداد/التحصيل.

عند:

```text
remboursement = 1
```

فإن:

```text
montant < 0
```

يعني طلب **استرداد للعميل**.

بينما:

```text
montant > 0
```

يعني طلب **تحصيل مبلغ من العميل**.

> يجب أن تكون هذه الخاصية مفعلة على حساب الشريك لدى NOEST حتى يمكن استخدامها.

---

# 26. الدعم الفني

في حال وجود استفسارات أو مشاكل تقنية متعلقة بالـ API، يمكن التواصل مع دعم NOEST عبر:

```text
api@noest-dz.com
```

**API Version:** `2.3`  
**آخر تحديث موثق:** مايو 2026.

---

## ملخص الـ Endpoints

| العملية | Method | Endpoint |
|---|---|---|
| إنشاء طلب | POST | `/api/public/create/order` |
| إنشاء طلبات متعددة | POST | `/api/public/create/orders` |
| التحقق من طلب | POST | `/api/public/valid/order` |
| التحقق من طلبات متعددة | POST | `/api/public/valid/orders` |
| تعديل طلب | POST | `/api/public/update/order` |
| تعديل قبل الإرسال | POST | `/api/public/update/order/before/expedition` |
| حذف طلب | POST | `/api/public/delete/order` |
| إضافة ملاحظة | POST | `/api/public/add/maj` |
| طلب محاولة جديدة | POST | `/api/public/ask/new-tentative` |
| طلب إرجاع | POST | `/api/public/ask/return` |
| تحميل وصل التوصيل | GET | `/api/public/get/order/label` |
| معلومات التتبع | POST | `/api/public/get/trackings/info` |
| قائمة Stop Desk | GET | `/api/public/desks` |
| أسعار التوصيل | GET | `/api/public/fees` |
| قائمة البلديات | GET | `/api/public/get/communes` |
| بلديات ولاية محددة | GET | `/api/public/get/communes/{wilaya_id}` |
| قائمة الولايات | GET | `/api/public/get/wilayas` |

هذه الوثيقة تمثل واجهة التكامل العامة الموثقة في ملف **NOEST Public API v2.3** المرفق.