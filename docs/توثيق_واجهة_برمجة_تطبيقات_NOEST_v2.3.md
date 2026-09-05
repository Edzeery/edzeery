# توثيق واجهة برمجة التطبيقات العامة لشركة NOEST

**الإصدار:** 2.3
**آخر تحديث:** مايو 2026

---

## 🚀 معلومات عامة

| الخاصية | القيمة |
|---|---|
| الرابط الأساسي (Base URL) | `https://app.noest-dz.com` |
| صيغة البيانات | JSON |
| طريقة المصادقة | رمز الحامل (Bearer Token) — `api_token` |

### المصادقة (Authentication)

تتطلب جميع نقاط النهاية (Endpoints) المصادقة عبر:

- **الترويسة (Header):** `Authorization: Bearer {api_token}`
- **المعامل (Parameter):** `user_guid` (المعرّف الفريد للشريك المزوَّد من طرف NOEST)

يتم تزويدكم ببيانات `api_token` و`user_guid` من طرف NOEST عند إنشاء حسابكم.

---

## 📦 إدارة الطلبات

### 1. إنشاء طلبية (Create an Order)

إنشاء طلبية واحدة.

**نقطة النهاية:**
```
POST /api/public/create/order
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `user_guid` | نص | نعم | المعرّف الفريد للشريك |
| `reference` | نص | لا | مرجع الطلبية (5 أحرف كحد أدنى) |
| `client` | نص | نعم | الاسم الكامل للزبون (255 حرفًا كحد أقصى) |
| `phone` | نص | نعم | رقم الهاتف (9 إلى 10 أرقام) |
| `phone_2` | نص | لا | رقم هاتف ثانوي (9 إلى 10 أرقام) |
| `adresse` | نص | نعم | عنوان التوصيل (255 حرفًا كحد أقصى) |
| `wilaya_id` | عدد صحيح | مشروط | رقم الولاية (1 إلى 58) — إلزامي إذا لم يُقدَّم `zip_code` |
| `commune` | نص | مشروط | اسم البلدية — إلزامي إذا لم يُقدَّم `zip_code` أو `stop_desk` |
| `montant` | رقمي | نعم | مبلغ الطلبية |
| `remarque` | نص | لا | ملاحظات (255 حرفًا كحد أقصى) |
| `produit` | نص | نعم | اسم/مرجع المنتج، وتُفصل المنتجات المتعددة بفاصلة |
| `type_id` | عدد صحيح | نعم | نوع التوصيل: 1=توصيل، 2=تبديل، 3=استلام من الزبون |
| `poids` | رقمي | لا | وزن الطرد (حسب الحد المسموح به للشريك) |
| `stop_desk` | عدد صحيح | نعم | التوصيل إلى نقطة استلام: 0=المنزل، 1=مكتب (Stop Desk) |
| `station_expedition` | عدد صحيح | لا | رمز محطة الشحن (يجب تفعيل هذه الميزة على الحساب) |
| `station_code` | نص | مشروط | رمز المحطة — إلزامي إذا كانت `stop_desk=1` |
| `stock` | عدد صحيح | لا | طلبية مرتبطة بمخزون: 0=لا، 1=نعم |
| `quantite` | نص | مشروط | الكميات مفصولة بفاصلة — إلزامي إذا كانت `stock=1` |
| `shop_name` | نص | لا | اسم المتجر (255 حرفًا كحد أقصى) |
| `zip_code` | نص | لا | الرمز البريدي (يحلّ محل `wilaya_id` و`commune`) |
| `remboursement` | عدد صحيح | لا | نوع الاسترجاع المالي: 0=لا، 1=نعم (مبلغ سالب = استرداد، مبلغ موجب = تحصيل) |

**مثال طلب:**
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

**استجابة النجاح:**
```json
{
 "success": true,
 "tracking": "ECS1234567890",
 "reference": "REF001",
 "regional_hub_name": "W",
 "wilaya_rank": "16B"
}
```

**رسائل الأخطاء الشائعة:**

| الخطأ | الوصف |
|---|---|
| `account_suspended` | حساب الشريك موقوف |
| `commune inexistante ou non activée` | البلدية المحددة غير موجودة في قاعدة البيانات |
| `zip_code invalide` | رمز بريدي غير صالح |
| `Le code de wilaya est different de code de station` | عدم تطابق بين رمز الولاية ورمز المحطة |
| `Aucune commune liee a la station choisie` | لا توجد بلدية مرتبطة بالمحطة المختارة |
| `Il faut choisir un code de station valide` | رمز محطة غير صالح |
| `Module stopdesk désactivé pour cette wilaya` | خدمة الاستلام من المكتب (Stop Desk) غير متاحة لهذه الولاية |
| `montant doit être inferieur à X` | المبلغ يتجاوز الحد المسموح به |

---

### 2. إنشاء طلبيات متعددة (Bulk Orders)

إنشاء عدة طلبيات في طلب واحد.

**نقطة النهاية:**
```
POST /api/public/create/orders
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `user_guid` | نص | نعم | المعرّف الفريد للشريك |
| `orders` | مصفوفة | نعم | مصفوفة من الطلبيات (1 كحد أدنى، 100 كحد أقصى) |

**بنية الطلبية:**
انظر نفس المعاملات الخاصة بإنشاء طلبية واحدة.

**مثال طلب:**
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

**استجابة النجاح:**
```json
{
 "success": true,
 "passed": {
 "0": { "success": true, "tracking": "TRK123456789" },
 "1": { "success": true, "tracking": "TRK987654321" }
 },
 "failed": {}
}
```

**استجابة تحتوي على أخطاء:**
```json
{
 "success": false,
 "passed": {
 "0": { "success": true, "tracking": "TRK123456789" }
 },
 "failed": {
 "1": {
 "reference": "Order reference (if sent)",
 "phone": ["The phone field is required."],
 "commune": ["The selected commune is invalid."]
 }
 }
}
```

#### جدول الأخطاء الممكنة

يمكن تصنيف الأخطاء إلى فئتين: أخطاء التحقق من صحة البيانات (Validation) وأخطاء منطق العمل المخصصة (Business Errors).

**أخطاء التحقق من صحة البيانات:**

| الحقل | رسالة الخطأ | الوصف |
|---|---|---|
| `user_guid` | The user_guid field is required. | المعرّف مفقود |
| `user_guid` | The selected user_guid is invalid. | المعرّف غير موجود |
| `client` | The client field is required. | اسم الزبون مفقود |
| `client` | The client field may not be greater than 255 characters. | الاسم طويل جدًا |
| `phone` | The phone field is required. | رقم الهاتف مفقود |
| `phone` | The phone field must be between 9 and 10 digits. | صيغة هاتف غير صالحة |
| `phone_2` | The phone_2 field must be between 9 and 10 digits. | صيغة الهاتف الثانوي غير صالحة |
| `adresse` | The adresse field is required. | العنوان مفقود |
| `adresse` | The adresse field may not be greater than 255 characters. | العنوان طويل جدًا |
| `wilaya_id` | The wilaya_id field is required. | الولاية مفقودة (إذا لم يُقدَّم zip_code) |
| `wilaya_id` | The wilaya_id field must be an integer. | نوع بيانات غير صالح |
| `wilaya_id` | The wilaya_id field must be between 1 and 58. | رقم الولاية خارج النطاق المسموح |
| `commune` | The commune field is required. | البلدية مفقودة (إذا لم يُقدَّم zip_code وstop_desk) |
| `commune` | The selected commune is invalid. | البلدية غير موجودة |
| `montant` | The montant field is required. | المبلغ مفقود |
| `montant` | The montant field must be numeric. | صيغة مبلغ غير صالحة |
| `remarque` | The remarque field may not be greater than 255 characters. | الملاحظات طويلة جدًا |
| `shop_name` | The shop_name field may not be greater than 255 characters. | اسم المتجر طويل جدًا |
| `produit` | The produit field is required. | المنتج مفقود |
| `type_id` | The type_id field is required. | نوع الطلبية مفقود |
| `type_id` | The type_id field must be between 1 and 3. | نوع غير صالح (يجب أن يكون 1 أو 2 أو 3) |
| `poids` | The poids field must be numeric. | صيغة وزن غير صالحة |
| `poids` | The poids field must be at least 0. | وزن سالب |
| `poids` | The poids field may not be greater than X. | الوزن يتجاوز الحد المسموح |
| `stop_desk` | The stop_desk field is required. | حقل Stop Desk مفقود |
| `stop_desk` | The stop_desk field must be between 0 and 1. | قيمة غير صالحة (يجب أن تكون 0 أو 1) |
| `stock` | The stock field must be between 0 and 1. | قيمة غير صالحة (يجب أن تكون 0 أو 1) |
| `quantite` | The quantite field is required. | الكميات مفقودة (إذا كانت stock=1) |
| `zip_code` | The selected zip_code is invalid. | الرمز البريدي غير موجود |
| `paypart_ref` | The paypart_ref field may not be greater than 255 characters. | مرجع الدفع طويل جدًا |
| `station_code` | The station_code field is required. | رمز المحطة مفقود (إذا كانت stop_desk=1) |
| `reference` | The reference field must be at least 5 characters. | المرجع قصير جدًا |
| `remboursement` | The remboursement field must be between 0 and 1. | قيمة غير صالحة (يجب أن تكون 0 أو 1) |

**أخطاء منطق العمل المخصصة:**

| رمز الخطأ | الرسالة | رمز HTTP | الوصف |
|---|---|---|---|
| `account_suspended` | Compte suspendu | 422 | حساب الشريك موقوف |
| `duplicate_order` | Colis déja existant | 200 | توجد طلبية بهذا المرجع مسبقًا |
| `inactive_commune` | commune inexistante ou non activée | 422 | البلدية المحددة غير موجودة أو غير مفعّلة |
| `zip_code` | zip_code invalide. | 200 | الرمز البريدي المقدَّم غير صالح |
| `max_amount_exceeded` | montant doit être inferieur à X | 422 | المبلغ يتجاوز الحد المسموح للشريك |
| `stopdesk_disabled` | Module stopdesk désactivé pour cette wilaya | 422 | خدمة Stop Desk غير متاحة لهذه الولاية |
| `station_expedition` | La station d'expedition n'est pas valide | 200 | رمز محطة الشحن غير صالح |
| `station_code` | Le code de wilaya est different de code de station | 200 | عدم تطابق بين رمز الولاية ورمز المحطة |
| `station_code` | Aucune commune liee a la station choisie. | 200 | لا توجد بلدية مرتبطة بهذه المحطة |
| `station_code` | Il faut choisir un code de station valide. | 200 | رمز المحطة المقدَّم غير صالح |
| `disabled_module` | Module de stockage désactivé | 422 | خدمة إدارة المخزون معطّلة لهذا الشريك |
| `wrong_quantities` | Le nombre des quantités saisi n'est pas identique au nombre des produits | 422 | عدد الكميات لا يساوي عدد المنتجات |
| `invalid_product` | Le produit avec la réference X n'existe pas ou désactivé | 422 | المنتج المحدَّد غير موجود أو معطَّل |
| `out_of_stock` | Stock indisponible | 422 | المخزون غير كافٍ للكمية المطلوبة |
| `already_validated` | Commande déjà validée | 422 | الطلبية تم التحقق منها مسبقًا |

---

### 3. التحقق من طلبية (Validate an Order)

التحقق من صحة طلبية تم إنشاؤها. بعد التحقق، تصبح الطلبية مرئية لقسم اللوجستيك ولا يمكن تعديلها أو حذفها.

**نقطة النهاية:**
```
POST /api/public/valid/order
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `user_guid` | نص | نعم | المعرّف الفريد للشريك |
| `tracking` | نص | نعم | رمز تتبّع الطلبية |

**مثال طلب:**
```json
{
 "user_guid": "abc123-def456-ghi789",
 "tracking": "TRK123456789"
}
```

**استجابة النجاح:**
```json
{ "success": true }
```

**رسائل الأخطاء الشائعة:**

| الخطأ | الوصف |
|---|---|
| `Commande introuvable` | الطلبية غير موجودة أو لا تعود لهذا الشريك |
| `Commande déjà validée` | تم التحقق من الطلبية مسبقًا |
| `Stock insuffisant` | المخزون غير كافٍ للتحقق من الطلبية (إذا كانت stock=1) |

---

### 4. التحقق من طلبيات متعددة (Validate Bulk Orders)

التحقق من صحة عدة طلبيات في طلب واحد.

**نقطة النهاية:**
```
POST /api/public/valid/orders
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `user_guid` | نص | نعم | المعرّف الفريد للشريك |
| `trackings` | مصفوفة | نعم | مصفوفة من رموز التتبّع (100 كحد أقصى) |

**مثال طلب** (الصيغة البسيطة: مصفوفة نصوص):
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

**استجابة النجاح:**
```json
{
 "success": true,
 "passed": {
 "TRK123456789": true,
 "TRK987654321": true,
 "TRK555666777": true
 },
 "failed": {}
}
```

**استجابة تحتوي على أخطاء:**
```json
{
 "success": false,
 "passed": {
 "TRK123456789": true
 },
 "failed": {
 "TRK987654321": {
 "tracking": ["The selected tracking is invalid."]
 },
 "TRK555666777": "Insufficient stock for this order"
 }
}
```

---

### 5. تعديل طلبية (Update an Order)

إنشاء طلب تعديل لطلبية موجودة.

**⚠️ ملاحظات مهمة:**
- الحقل `tracking` إلزامي.
- التعديلات المسموح بها تعتمد على الحالة الراهنة للطلبية.
- **لا يمكن تغيير الولاية عبر هذه النقطة.**

**نقطة النهاية:**
```
POST /api/public/update/order
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `tracking` | نص | نعم | رمز تتبّع الطلبية |
| `tel` | نص | لا | رقم هاتف جديد (10 أرقام) |
| `adresse` | نص | لا | عنوان جديد (يُستخدم مع `commune`) |
| `wilaya` | عدد صحيح | لا | يجب أن يطابق ولاية الطلبية الحالية (وإلا يظهر خطأ) |
| `commune` | نص | لا | بلدية جديدة (إلزامية مع `adresse` معًا) |
| `montant` | رقمي | لا | مبلغ جديد |
| `type` | عدد صحيح | لا | نوع شحن جديد (1 إلى 3) |
| `stop_desk` | عدد صحيح | لا | وضعية توصيل جديدة (0=المنزل، 1=مكتب) |
| `code_station` | نص | لا | إلزامي عند `stop_desk=1` عند التحويل إلى نظام المكتب |

**قواعد منطق العمل:**
- إذا كانت الطلبية في حالة "قيد التوصيل" (En livraison)، فلا يمكن تعديل سوى `type` و`montant`.
- بالنسبة للتوصيل المنزلي، يجب تقديم `commune` و`adresse` معًا.
- بالنسبة لنظام المكتب (`stop_desk=1`)، فإن `code_station` إلزامي ويجب أن ينتمي لنفس ولاية الطلبية.
- إذا لم يُقدَّم أي حقل قابل للتعديل بشكل صحيح، يُرفض الطلب.

**مثال طلب:**
```json
{
 "tracking": "TRK123456789",
 "tel": "0551234567",
 "stop_desk": 1,
 "code_station": "16B",
 "montant": 4000
}
```

**استجابة النجاح:**
```json
{
 "success": true,
 "message": "La demande a été envoyée avec succès !"
}
```

**رسائل الأخطاء الشائعة:**

| الخطأ | الوصف |
|---|---|
| `Commande non trouvée dans l'étape de modification` | الطلبية غير موجودة، أو تم شحنها بالفعل، أو لا تعود لهذا الشريك |
| `Vous ne pouvez pas modifier la wilaya de cette commande` | الولاية المقدَّمة تختلف عن ولاية الطلبية |
| `Le code station est obligatoire pour une livraison stop desk` | `stop_desk=1` بدون `code_station` |
| `Station non trouvée dans la wilaya de cette commande` | المحطة غير صالحة لولاية الطلبية |
| `Il faut saisir l'adresse de cette commande` | تم تقديم `commune` بدون `adresse` |
| `Il faut saisir la commune de cette commande` | تم تقديم `adresse` بدون `commune` |
| `Aucune demande de modification !` | لم يتم رصد أي تعديل صالح |

---

### 5.1. تعديل طلبية قبل الشحن (Update an Order Before Expedition)

يقوم بتعديل طلبية موجودة لم تُشحن بعد بشكل مباشر. وبخلاف النقطة رقم 5، تُطبَّق التعديلات فورًا دون إنشاء طلب تعديل منفصل.

**⚠️ ملاحظات مهمة:**
- الحقل `tracking` إلزامي ويجب أن يعود للشريك المصادَق عليه.
- يجب ألا تكون الطلبية قد شُحنت بعد.
- فقط الحقول المُقدَّمة سيتم تحديثها؛ الحقول المحذوفة تبقى دون تغيير.

**نقطة النهاية:**
```
POST /api/public/update/order/before/expedition
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `tracking` | نص | نعم | رمز تتبّع الطلبية |
| `reference` | نص | لا | مرجع الطلبية |
| `client` | نص | لا | الاسم الكامل للزبون |
| `tel` | نص | لا | رقم الهاتف الأساسي (10 أرقام) |
| `tel2` | نص | لا | رقم الهاتف الثانوي (10 أرقام) |
| `adresse` | نص | لا | عنوان التوصيل |
| `wilaya` | رقمي | لا | رقم الولاية |
| `commune` | نص | لا | اسم البلدية |
| `montant` | رقمي | لا | مبلغ الطلبية |
| `remarque` | نص | لا | ملاحظات (255 حرفًا كحد أقصى) |
| `product` | نص | لا | اسم/مرجع المنتج |
| `type` | عدد صحيح | لا | نوع الشحن (1=توصيل، 2=تبديل، 3=استلام من الزبون) |
| `poids` | نص | لا | وزن الطرد |
| `stop_desk` | عدد صحيح | لا | وضعية التوصيل (0=المنزل، 1=مكتب) |

**مثال طلب:**
```json
{
 "tracking": "TRK123456789",
 "client": "Ahmed Ahmed",
 "tel": "0550000000",
 "montant": 4500,
 "adresse": "Rue des Martyrs, Bab Ezzouar",
 "commune": "Bab Ezzouar"
}
```

**استجابة النجاح:**
```json
{ "success": true }
```

**استجابة خطأ:**
```json
{
 "success": false,
 "message": "Commande non trouvée dans l'étape de modification"
}
```

**رسائل الأخطاء الشائعة:**

| الخطأ | الوصف |
|---|---|
| `Commande non trouvée dans l'étape de modification` | الطلبية غير موجودة، أو تم شحنها بالفعل، أو لا تعود لهذا الشريك |

---

### 6. حذف طلبية (Delete an Order)

حذف طلبية لم يتم التحقق منها بعد.

**⚠️ ملاحظة مهمة:** لا يمكن حذف سوى الطلبيات غير المُتحقَّق منها.

**نقطة النهاية:**
```
POST /api/public/delete/order
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `user_guid` | نص | نعم | المعرّف الفريد للشريك |
| `tracking` | نص | نعم | رمز تتبّع الطلبية |

**مثال طلب:**
```json
{
 "user_guid": "abc123-def456-ghi789",
 "tracking": "TRK123456789"
}
```

**استجابة النجاح:**
```json
{ "success": true }
```

**استجابة الخطأ:**
```json
{ "success": false }
```

---

### 7. إضافة ملاحظة (Add a Remark)

إضافة تحديث أو ملاحظة إلى طلبية.

**نقطة النهاية:**
```
POST /api/public/add/maj
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `tracking` | نص | نعم | رمز تتبّع الطلبية |
| `content` | نص | نعم | محتوى الملاحظة (255 حرفًا كحد أقصى) |

**مثال طلب:**
```json
{
 "tracking": "TRK123456789",
 "content": "Customer prefers afternoon delivery"
}
```

**استجابة النجاح:**
```json
{
 "success": true,
 "message": "Mise a jour avec success"
}
```

**رسائل الأخطاء الشائعة:**

| الخطأ | الوصف |
|---|---|
| `Commande inexistante` | الطلبية غير موجودة أو لا تعود لهذا الشريك |

---

### 8. طلب محاولة توصيل جديدة (Request a New Delivery Attempt)

طلب محاولة توصيل جديدة لطلبية.

**نقطة النهاية:**
```
POST /api/public/ask/new-tentative
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `tracking` | نص | نعم | رمز تتبّع الطلبية |

**مثال طلب:**
```json
{ "tracking": "TRK123456789" }
```

**استجابة النجاح:**
```json
{ "success": true }
```

---

### 9. طلب إرجاع (Request a Return)

طلب إرجاع طلبية إلى الشريك.

**نقطة النهاية:**
```
POST /api/public/ask/return
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `tracking` | نص | نعم | رمز تتبّع الطلبية |

**مثال طلب:**
```json
{ "tracking": "TRK123456789" }
```

**استجابة النجاح:**
```json
{ "success": true }
```

---

### 10. تحميل وصل التوصيل (Download Delivery Slip)

تحميل ملصق الطلبية (وصل التوصيل) بصيغة PDF.

**نقطة النهاية:**
```
GET /api/public/get/order/label
```

**الترويسات:**
```
Authorization: Bearer {token}
```

**المعاملات (Query String):**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `tracking` | نص | نعم | رمز تتبّع الطلبية |

**مثال طلب:**
```
GET /api/public/get/order/label?tracking=TRK123456789
```

**الاستجابة:** يُرجع ملف PDF قابل للتحميل يحتوي على ملصق التوصيل.

---

## 📊 تتبّع الطلبيات

### 11. الحصول على معلومات طلبيات متعددة (Get Information for Multiple Orders)

استرجاع المعلومات التفصيلية والسجل التاريخي لعدة طلبيات.

**نقطة النهاية:**
```
POST /api/public/get/trackings/info
```

**الترويسات:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `trackings` | مصفوفة | نعم | مصفوفة من رموز التتبّع |

**مثال طلب:**
```json
{
 "trackings": [
 "TRK123456789",
 "TRK987654321"
 ]
}
```

**استجابة النجاح:**
```json
{
 "TRK123456789": {
 "OrderInfo": {
 "tracking": "TRK123456789",
 "reference": "REF0001",
 "client": "Ahmed Benali",
 "phone": "0550505050",
 "phone_2": null,
 "adresse": "Bab Ezzouar, Alger",
 "wilaya_id": 16,
 "commune": "Bab Ezzouar",
 "montant": "3500.00",
 "remarque": "",
 "produit": "Smartphone Samsung",
 "driver_name": "Driver 001",
 "driver_tel": "0550000000",
 "type_id": 1,
 "stop_desk": 0,
 "created_at": "2024-01-15T12:45:04.000000Z"
 },
 "recipientName": "Ahmed Benali",
 "shippedBy": "Electronics Store",
 "originCity": 16,
 "destLocationCity": 16,
 "activity": [
 {
 "event": "Uploaded to system",
 "causer": "PARTNER",
 "badge-class": "badge-success",
 "by": "Electronics Store",
 "name": "",
 "driver": "",
 "fdr": "",
 "date": "2024-01-15 12:45:04"
 },
 {
 "event": "Validated",
 "causer": "PARTNER",
 "badge-class": "badge-success",
 "by": "Electronics Store",
 "name": "",
 "driver": "",
 "fdr": "",
 "date": "2024-01-15 13:20:15"
 }
 ],
 "deliveryAttempts": []
 },
 "TRK987654321": {
 "OrderInfo": {
 "tracking": "TRK987654321",
 "reference": "REF0002",
 "client": "Fatima Zahra",
 "phone": "0660606060",
 "phone_2": null,
 "adresse": "Cité 300 logements",
 "wilaya_id": 16,
 "commune": "Dar El Beida",
 "montant": "2000.00",
 "remarque": "",
 "produit": "Bluetooth Earphones",
 "driver_name": "Driver 002",
 "driver_tel": "0551111111",
 "type_id": 1,
 "stop_desk": 0,
 "created_at": "2024-01-15T14:30:22.000000Z"
 },
 "recipientName": "Fatima Zahra",
 "shippedBy": "Electronics Store",
 "originCity": 16,
 "destLocationCity": 16,
 "activity": [
 {
 "event": "Return dispatched to partner",
 "event_key": "return_dispatched_to_partenaire",
 "causer": "NOEST",
 "badge-class": "badge-primary",
 "by": "",
 "name": "",
 "driver": "",
 "fdr": "",
 "date": "2024-01-20 10:18:12"
 }
 ],
 "deliveryAttempts": []
 }
}
```

**استجابة الخطأ:**
```json
{ "message": "Trackings not found" }
```

**ملاحظة:** يتم البحث عن الطلبيات في كل من الطلبيات النشطة والمؤرشفة.

---

## 📋 قائمة الأحداث

القائمة الكاملة للأحداث الممكنة في سجل تاريخ الطلبية:

| رمز الحدث (Event Key) | الحدث | الوصف |
|---|---|---|
| `upload` | Uploaded to system | تم إنشاء الطلبية |
| `customer_validation` | Validated | تم التحقق من الطلبية من طرف الشريك |
| `validation_collect_colis` | Package Picked Up | تم استلام الطرد من الشريك |
| `validation_reception_admin` | Reception validated | تم التحقق من الاستلام من طرف الإدارة |
| `validation_reception` | Picked up by driver | تم استلام الطرد من طرف السائق |
| `fdr_activated` | Out for delivery | تفعيل ورقة الطريق (التوزيع) |
| `sent_to_redispatch` | Sent for redispatch | جارٍ إعادة التوزيع |
| `nouvel_tentative_asked_by_customer` | New attempt requested by seller | تم طلب محاولة جديدة |
| `return_asked_by_customer` | Return requested by partner | تم طلب الإرجاع |
| `return_asked_by_hub` | Return in transit | الإرجاع قيد التنفيذ |
| `retour_dispatched_to_partenaires` | Return dispatched to partner | تم شحن الإرجاع إلى الشريك |
| `return_dispatched_to_partenaire` | Return dispatched to partner | تم شحن الإرجاع إلى الشريك |
| `colis_retour_transmit_to_partner` | Return package transmitted to partner | تم تسليم الإرجاع |
| `colis_pickup_transmit_to_partner` | Pick-UP transmitted to partner | تم تسليم عملية الاستلام |
| `annulation_dispatch_retour` | Return transmission to partner cancelled | تم إلغاء عملية التسليم |
| `cancel_return_dispatched_to_partenaire` | Return transmission cancelled | تم إلغاء عملية التسليم |
| `livraison_echoue_recu` | Return received by partner | تم استلام الإرجاع |
| `return_validated_by_partener` | Return validated by partner | تم تأكيد الإرجاع |
| `return_redispatched_to_livraison` | Return put back for delivery | محاولة توصيل جديدة |
| `return_dispatched_to_warehouse` | Return dispatched to warehouse | تم الإرسال إلى المستودع |
| `pickedup` | Pick-Up collected | تم إتمام عملية الاستلام |
| `valid_return_pickup` | Pick-Up validated | تم تأكيد عملية الاستلام |
| `pickup_picked_recu` | Pick-Up received by partner | تم استلام عملية الاستلام |
| `colis_suspendu` | Suspended | تم تعليق الطلبية |
| `livre` | Delivered | تم تسليم الطلبية |
| `livred` | Delivered | تم تسليم الطلبية |
| `verssement_admin_cust` | Amount transmitted to partner | تم تحويل الدفعة |
| `verssement_admin_cust_canceled` | Payment cancelled | تم إلغاء الدفعة |
| `verssement_hub_cust_canceled` | Payment cancelled | تم إلغاء الدفعة |
| `validation_reception_cash_by_partener` | Amount received by partner | تم استلام الدفعة |
| `echange_valide` | Exchange validated | تم تأكيد التبديل |
| `echange_valid_by_hub` | Exchange validated by hub | تم تأكيد التبديل |
| `ask_to_delete_by_admin` | Deletion requested | طلب حذف من طرف الإدارة |
| `ask_to_delete_by_hub` | Deletion requested | طلب حذف من طرف المركز |
| `edited_informations` | Information modified | تعديل المعلومات |
| `edit_price` | Price modified | تعديل المبلغ |
| `edit_wilaya` | Wilaya change | تعديل الولاية |
| `extra_fee` | Package surcharge | رسوم إضافية |
| `mise_a_jour` | Delivery attempt | محاولة توصيل |

---

## 🏢 البيانات المرجعية (Reference Data)

### 12. قائمة المكاتب (المحطات / نقاط الاستلام)

استرجاع قائمة كل نقاط التوصيل المتاحة (Stop Desk).

**نقطة النهاية:**
```
GET /api/public/desks
```

**الترويسات:**
```
Authorization: Bearer {token}
```

**مثال طلب:**
```
GET /api/public/desks
```

**استجابة النجاح:**
```json
{
 "01A": {
 "code": "1A",
 "name": "Adrar",
 "address": "Cité les palmier en face l'hopital",
 "map": "",
 "phones": {
 "0": "0550602181",
 "1": "0561623531",
 "2": "",
 "3": ""
 },
 "email": "adrar@noest-dz.com"
 },
 "02A": {
 "code": "2A",
 "name": "Chlef",
 "address": "Rue Lac des Forêts (À côté du CNRC)",
 "map": "",
 "phones": {
 "0": "0770582116",
 "1": "0561686360",
 "2": "",
 "3": ""
 },
 "email": "chlef@noest-dz.com"
 }
}
```

---

### 13. قائمة أسعار التوصيل (List of Delivery Fees)

استرجاع شبكة الأسعار الخاصة بالشريك.

**نقطة النهاية:**
```
GET /api/public/fees
```

**الترويسات:**
```
Authorization: Bearer {token}
```

**مثال طلب:**
```
GET /api/public/fees
```

**استجابة النجاح:**
```json
{
 "tarifs": {
 "return": {
 "16": {
 "tarif_id": 400,
 "wilaya_id": 16,
 "tarif": "300",
 "tarif_stopdesk": "300"
 },
 "9": {
 "tarif_id": 400,
 "wilaya_id": 9,
 "tarif": "300",
 "tarif_stopdesk": "300"
 }
 },
 "delivery": {
 "16": {
 "tarif_id": 399,
 "wilaya_id": 16,
 "tarif": "700",
 "tarif_stopdesk": "300"
 },
 "9": {
 "tarif_id": 399,
 "wilaya_id": 9,
 "tarif": "800",
 "tarif_stopdesk": "350"
 }
 }
 }
}
```

**البنية:**
- `return`: رسوم الإرجاع حسب الولاية
- `delivery`: رسوم التوصيل حسب الولاية
- `tarif`: سعر التوصيل المنزلي (بالدينار الجزائري)
- `tarif_stopdesk`: سعر نقطة الاستلام (بالدينار الجزائري)

---

### 14. قائمة البلديات (List of Communes)

استرجاع قائمة البلديات القابلة للتوصيل، مع إمكانية التصفية حسب الولاية.

**نقطة النهاية:**
```
GET /api/public/get/communes/{wilaya_id}
```

**الترويسات:**
```
Authorization: Bearer {token}
```

**المعاملات:**

| المعامل | النوع | إلزامي | الوصف |
|---|---|---|---|
| `wilaya_id` | عدد صحيح | لا | رقم الولاية (1 إلى 58) للتصفية |

**مثال طلب:**

جميع البلديات:
```
GET /api/public/get/communes
```

بلديات ولاية محددة:
```
GET /api/public/get/communes/5
```

**استجابة النجاح:**
```json
[
 { "nom": "Batna", "wilaya_id": 5, "code_postal": "05001", "is_active": 1 },
 { "nom": "Ghassira", "wilaya_id": 5, "code_postal": "05002", "is_active": 1 },
 { "nom": "Maafa", "wilaya_id": 5, "code_postal": "05003", "is_active": 1 },
 { "nom": "Merouana", "wilaya_id": 5, "code_postal": "05004", "is_active": 1 },
 { "nom": "Seriana", "wilaya_id": 5, "code_postal": "05005", "is_active": 1 }
]
```

---

### 15. قائمة الولايات (List of Wilayas)

استرجاع قائمة كل الولايات الجزائرية.

**نقطة النهاية:**
```
GET /api/public/get/wilayas
```

**الترويسات:**
```
Authorization: Bearer {token}
```

**مثال طلب:**
```
GET /api/public/get/wilayas
```

**استجابة النجاح:**
```json
[
 { "code": 1, "nom": "Adrar", "is_active": 1 },
 { "code": 2, "nom": "Chlef", "is_active": 1 },
 { "code": 3, "nom": "Laghouat", "is_active": 1 },
 { "code": 4, "nom": "Oum El Bouaghi", "is_active": 1 },
 { "code": 5, "nom": "Batna", "is_active": 1 }
]
```

---

## 📝 ملاحظات مهمة

### الحدود والقيود

- **الإنشاء الجماعي (Bulk creation):** 100 طلبية كحد أقصى في الطلب الواحد
- **التحقق الجماعي (Bulk validation):** 100 رمز تتبّع كحد أقصى في الطلب الواحد
- **حد معدل الطلبات (Rate limiting):** 60 طلبًا في الدقيقة افتراضيًا
- **المصادقة:** رمز الحامل (Bearer Token) إلزامي لجميع نقاط النهاية
- **المهلة الزمنية (Timeout):** 30 ثانية لكل طلب

### معالجة الأخطاء

- إذا فشلت طلبية واحدة على الأقل في عملية جماعية، تكون قيمة `success` تساوي `false`
- في عمليات الإنشاء، تُطابق المفاتيح في `passed` و`failed` فهارس المصفوفة (indices)
- في عمليات التحقق، تُطابق المفاتيح في `passed` و`failed` رموز التتبّع
- أخطاء التحقق من صحة البيانات تُرجع رمز حالة HTTP 422 مع التفاصيل

### سير العمل الموصى به

1. إنشاء طلبية عبر `POST /api/public/create/order` أو `/create/orders`
2. التحقق من المعلومات عند الحاجة
3. التعديل عبر `POST /api/public/update/order` (إن لم يتم التحقق منها بعد)
4. التحقق عبر `POST /api/public/valid/order` أو `/valid/orders`
5. تحميل الملصق عبر `GET /api/public/get/order/label`
6. التتبّع عبر `POST /api/public/get/trackings/info`

### نظام المكتب (Stop Desk)

- إذا كانت `stop_desk=1`، يصبح حقل `station_code` إلزاميًا
- يجب أن يطابق رمز المحطة ولاية الوجهة
- استخدم `/api/public/desks` للحصول على قائمة الرموز المتاحة

### محطة الشحن (Expedition Station)

- يتيح حقل `station_expedition` إمكانية إرسال محطة شحن محددة للطلبية
- يجب تفعيل هذه الميزة صراحةً لحساب الشريك، وإلا سيتم رفض القيمة

### الرمز البريدي (zip_code)

- إذا تم تقديم `zip_code`، فإنه يحل تلقائيًا محل `wilaya_id` و`commune`
- يجب أن يكون الرمز البريدي موجودًا في قاعدة البيانات
- استخدم `/api/public/get/communes` للحصول على الرموز البريدية الصالحة

### المخزون (Stock)

- إذا كانت `stock=1`، يصبح حقل `quantite` إلزاميًا
- يجب فصل مراجع المنتجات بفواصل في حقل `produit`
- يجب فصل الكميات المقابلة بفواصل في حقل `quantite`
- مثال: `produit="PROD001,PROD002"` و`quantite="2,3"`

### أنواع الطلبيات

- **النوع 1 (توصيل):** توصيل عادي مع تحصيل المبلغ
- **النوع 2 (تبديل):** تبديل منتج مع الزبون
- **النوع 3 (استلام من الزبون):** استلام طرد من الزبون (يُفرض المبلغ = 0)

### الاسترداد / التحصيل (Refund/Collection)

- إذا كانت `remboursement=1` و`montant` أقل من صفر: طلب استرداد للزبون
- إذا كانت `remboursement=1` و`montant` أكبر من صفر: طلب تحصيل من الزبون
- يجب تفعيل هذه الميزة للحساب

---

## 🆘 الدعم الفني

لأي استفسارات أو مشاكل تقنية، يُرجى التواصل مع فريق دعم NOEST:

- **البريد الإلكتروني:** api@noest-dz.com
- **الإصدار:** 2.3 — آخر تحديث: مايو 2026
