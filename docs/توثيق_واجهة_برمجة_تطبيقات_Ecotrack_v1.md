# توثيق واجهة برمجة تطبيقات ECOTRACK — v1

> **المصدر:** استُخرج بالكامل من ملف Postman Documenter الذي رفعه المستخدم (`ECOTRACK-API-Full-Documentation.html`).
> **النطاق:** Public Delivery & Order Management API — **20 Endpoints**
> **Base URL:** `{{url}}/api/v1/`

---

## 1. نظرة عامة

الـ ECOTRACK Public API يسمح بإدارة عمليات التوصيل برمجيًا — الربط مع CRM أو منصة تجارة إلكترونية أو أي تطبيق آخر، بدون المرور عبر واجهة الويب.

### المصادقة (Authentication)
كل الطلبات تتطلب إرسال `api_token` كـ **query parameter**.

### حد الطلبات (Rate Limit)
- الحد الأقصى: **50 طلب / دقيقة**
- تجاوز الحد يُرجع: **`429 Too Many Requests`**
- ⚠️ **ملاحظة معمارية مهمة لمشروعنا:** هذا مختلف عن الـ headers اللي أرسلتها سابقًا (`X-RateLimit-Limit-Day`, `X-RateLimit-Limit-Hour`...) — تلك الـ headers تُرجع فقط من endpoint مخصص (`GET /api/v1/` — انظر القسم 2). يجب التحقق الفعلي عبر grep + اختبار حي من الرد الحقيقي لكل endpoint قبل بناء الـ rate-limit middleware، لأن التوثيق هنا يذكر حد `50/دقيقة` بشكل عام بينما الـ headers المرسلة سابقًا تتضمن حدود يومية/ساعية أيضًا — قد تكون هذه الـ headers مرسلة على **كل** الطلبات وليس فقط على endpoint الـ Rate Limit. **يجب تأكيد هذا فعليًا بطلب تجريبي حقيقي (Postman) قبل كتابة الكود.**

---

## 2. 🔐 Authorisation

### `GET /api/v1/validate/token`
**التحقق من صلاحية الـ API token قبل إجراء أي طلبات أخرى.**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `api_token` | string | Required | Your API token from your ECOTRACK account |

**Possible Responses:**
```json
{ "success": false, "message": "INVALID_TOKEN" }
{ "success": false, "message": "TOKEN_NOT_ALLOWED" }
{ "success": true,  "message": "VALID_TOKEN" }
```

---

## 3. ⏱ Rate Limit

### `GET /api/v1/`
**إرجاع معلومات حد الطلبات الحالية للحساب.** تجاوز 50 طلب/دقيقة يُرجع `429 Too Many Requests`.

**Example Request:**
```
GET {{url}}/api/v1/
```

*(هذا على الأرجح الـ endpoint الذي يُرجع headers مثل `X-RateLimit-Limit`, `X-RateLimit-Limit-Day`, `X-RateLimit-Limit-Hour` وما يقابلها من `Remaining`/`Reset` — يجب التأكد بطلب فعلي.)*

---

## 4. 📦 Orders

### `POST /api/v1/create/order`
**إضافة طلبية واحدة.** يدعم كل أنواع العمليات: Delivery, Exchange, Pickup, Collection.

| Parameter | Type | Required | Description |
|---|---|---|---|
| `reference` | string (max 255) | Optional | مرجعك الداخلي للطلبية |
| `nom_client` | string (max 255) | **Required** | الاسم الكامل للمستلم |
| `telephone` | numeric (9–10 digits) | **Required** | رقم الهاتف الأساسي |
| `telephone_2` | numeric (9–10 digits) | Optional | رقم هاتف ثانوي |
| `adresse` | string (max 255) | **Required** | عنوان التوصيل |
| `code_postal` | numeric | Optional | الرمز البريدي |
| `commune` | string (max 255) | **Required** | اسم البلدية |
| `code_wilaya` | integer (1–58) | **Required** | رمز الولاية |
| `montant` | numeric | **Required** | المبلغ المطلوب تحصيله (شامل رسوم التوصيل) |
| `type` | integer (1–4) | **Required** | `1`=Delivery, `2`=Exchange, `3`=Pickup, `4`=Collection |
| `stop_desk` | integer (0 or 1) | Optional | `0`=توصيل منزلي, `1`=مكتب/نقطة استلام |
| `produit` | string (max 255) | Optional | اسم/أسماء المنتج. لطلبات المخزون: فصل المراجع بفاصلة (`prod001,prod052`) |
| `stock` | integer (0 or 1) | Optional | `1`=طلبية من المخزون, `0`=لا |
| `quantite` | string | مطلوب إذا `stock=1` | الكميات لكل منتج، مفصولة بفاصلة |
| `produit_a_recuperer` | string (max 255) | Optional | المنتج المراد استرجاعه (لطلبات التبديل/Exchange) |
| `boutique` | string (max 255) | Optional | اسم المتجر (عند إدارة عدة متاجر) |
| `remarque` | string (max 255) | Optional | ملاحظات/تعليمات التوصيل |
| `weight` | numeric | Optional | وزن الطرد (كغ) |
| `fragile` | integer (0 or 1) | Optional | `1`=طرد قابل للكسر |
| `gps_link` | string (URL) | Optional | رابط موقع GPS للعميل |

**Example Request:**
```
POST {{url}}/api/v1/create/order
?nom_client=Ahmed Benali
&telephone=0550123456
&adresse=17 Rue des Frères Bouadou
&commune=Birtouta
&code_wilaya=16
&montant=2500
&type=1
&stop_desk=0
&produit=Chaussures Nike
&weight=1.5
&fragile=0
&remarque=Appeler avant livraison
```

**Success Response:**
```json
{
  "success": true,
  "tracking": "ECO-123456789"
}
```

---

### `POST /api/v1/create/orders` (Bulk)
**إضافة حتى 100 طلبية في طلب واحد.** جسم الطلب JSON object بمفتاح `orders` يحتوي على كائنات مرقّمة.

> ⚠️ **الحد الأقصى: 100 طلبية لكل طلب.**

**Example Request Body (JSON):**
```json
{
  "orders": {
    "0": {
      "reference": "DEMO852",
      "nom_client": "Client 1",
      "telephone": "0500000000",
      "adresse": "17 Rue Med",
      "commune": "Oum Touyour",
      "code_wilaya": "5",
      "montant": "5000",
      "type": "1",
      "stop_desk": 0,
      "stock": 1,
      "produit": "tesrty",
      "quantite": "1",
      "weight": "2",
      "gps_link": "https://maps.app.goo.gl/VnX8UtFq4PVY2c7d7"
    },
    "1": {
      "reference": "DEMO853",
      "nom_client": "Client 2",
      "telephone": "0500000002",
      "adresse": "17 Rue Med",
      "commune": "Oum Touyour",
      "code_wilaya": "5",
      "montant": "5000",
      "type": "1",
      "stop_desk": 0,
      "stock": 1,
      "produit": "tesrty",
      "quantite": "1",
      "weight": "2"
    }
  }
}
```

> 🔗 **ملاحظة معمارية:** هذا مطابق لنمط NOEST من ناحية الحد (100 طلب لكل نداء) — يجب أن تُبنى طبقة الـ chunking المشتركة (المذكورة في خطة Phase 32 لـ NOEST) بحيث تخدم الاثنين عبر نفس الواجهة `validateOrders()` / `createOrders()` في `CarrierIntegrationContract`، لا تُكرر منطق التقسيم لكل ناقل.

---

### `POST /api/v1/update/order`
**تعديل طلبية موجودة.** ممكن فقط **قبل** أن تُشحن/تُصادَق الطلبية. كل المعاملات اختيارية ما عدا `tracking`.

| Parameter | Type | Required | Description |
|---|---|---|---|
| `tracking` | string | **Required** | رقم تتبع الطلبية الفريد |
| `reference` | string (max 255) | Optional | مرجع الطلبية |
| `client` | string (max 255) | Optional | الاسم الكامل للمستلم |
| `tel` | numeric (9–10 digits) | Optional | رقم الهاتف الأساسي |
| `tel2` | numeric (9–10 digits) | Optional | رقم هاتف ثانوي |
| `adresse` | string (max 255) | Optional | عنوان التوصيل |
| `code_postal` | numeric | Optional | الرمز البريدي |
| `commune` | string (max 255) | Optional | اسم البلدية |
| `wilaya` | integer (1–58) | Optional | رمز الولاية |
| `montant` | numeric | Optional | المبلغ المطلوب تحصيله |
| `remarque` | string (max 255) | Optional | ملاحظات/تعليمات |
| `product` | string (max 255) | Optional | اسم/أسماء المنتج |
| `boutique` | string (max 255) | Optional | اسم المتجر |
| `type` | integer (1–4) | Optional | `1`=Delivery, `2`=Exchange, `3`=Pickup, `4`=Collection |
| `stop_desk` | integer (0 or 1) | Optional | `0`=منزلي, `1`=مكتب |
| `fragile` | integer (0 or 1) | Optional | `1`=قابل للكسر |
| `gps_link` | string (URL) | Optional | رابط GPS |

> ⚠️ **ملاحظة:** أسماء الحقول هنا مختلفة عن `create/order` (`client` بدل `nom_client`, `tel` بدل `telephone`, `wilaya` بدل `code_wilaya`, `product` بدل `produit`). **يجب معالجة هذا الفرق صراحة في الـ Adapter/Mapper** حتى لا يحدث خلط بين الحقول عند البناء.

**Example Request:**
```
POST {{url}}/api/v1/update/order
?tracking=ECO-123456789
&montant=3000
&adresse=12 Rue Didouche Mourad
&remarque=Livrer le matin
```

---

### `DELETE /api/v1/delete/order`
**حذف طلبية طالما لم تُشحن/تُصادَق بعد.**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `tracking` | string | **Required** | رقم تتبع الطلبية الفريد |

**Example Request:**
```
DELETE {{url}}/api/v1/delete/order?tracking=ECO-123456789
```

---

### `POST /api/v1/valid/order`
**التحقق من الطلبية وشحنها.** بعد الشحن، لا يمكن تعديل الطلبية أو حذفها.

| Parameter | Type | Required | Description |
|---|---|---|---|
| `tracking` | string | **Required** | رقم تتبع الطلبية الفريد |
| `ask_collection` | integer (0 or 1) | Optional | `1`=طلب استلام الطرد, `0`=بدون استلام |

**Example Request:**
```
POST {{url}}/api/v1/valid/order?tracking=ECO-123456789&ask_collection=1
```

> 🔗 هذا هو المكافئ المباشر لـ `validateOrder()` المخطط لها في Phase 32 لـ NOEST (`/api/public/valid/order`). **نفس التوقيع تقريبًا** — فرصة جيدة لتوحيد اسم الطريقة في `CarrierIntegrationContract` بين الناقلين.

---

### `POST /api/v1/valid/returns`
**تأكيد استلام الطرود المرتجعة.**

**Example Request:**
```
POST {{url}}/api/v1/valid/returns
```

> ⚠️ لا توجد query parameters موثقة لهذا الـ endpoint في المصدر — يجب التأكد عبر اختبار حي هل يتطلب `tracking` أو `trackings[]` في جسم الطلب قبل استخدامه في workflow الـ rotor/returns.

---

### `GET /api/v1/get/order/label`
**تحميل ملصق الشحن القابل للطباعة للطلبية.**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `tracking` | string | **Required** | رقم تتبع الطلبية الفريد |

**Example Request:**
```
GET {{url}}/api/v1/get/order/label?tracking=ECO-123456789
```

---

## 5. 🔍 Order Tracking

### `POST /api/v1/add/maj`
**إضافة ملاحظة/تحديث على طرد بعد الشحن،** لإخطار شركة التوصيل بأي تغييرات.

| Parameter | Type | Required | Description |
|---|---|---|---|
| `tracking` | string | **Required** | رقم تتبع الطلبية الفريد |
| `content` | string (max 255) | **Required** | محتوى الملاحظة/التحديث |

**Example Request:**
```
POST {{url}}/api/v1/add/maj
?tracking=ECO-123456789
&content=Client changed address to 5 Rue Larbi Ben Mhidi
```

> 🔗 مكافئ مباشر لـ `addNote()` المستعملة مع NOEST عبر `/add/maj` (نفس المسار بالضبط!) — يجب التأكد إن كانت هذه الطريقة قابلة لإعادة الاستخدام بنفس التوقيع بين الناقلين، أو إن كان هذا مجرد تطابق تسمية عرضي.

---

### `GET /api/v1/get/maj`
**استرجاع كل تحديثات التتبع المطبّقة على طرد** — سواء من طرف السائق أو المرسل.

| Parameter | Type | Required | Description |
|---|---|---|---|
| `tracking` | string | **Required** | رقم تتبع الطلبية الفريد |

**Example Request:**
```
GET {{url}}/api/v1/get/maj?tracking=ECO-123456789
```

---

### `POST /api/v1/ask/for/order/return`
**طلب إرجاع طرد أثناء التوصيل.** ملاحظة: قد تتجاهل شركة التوصيل هذا الطلب.

| Parameter | Type | Required | Description |
|---|---|---|---|
| `tracking` | string | **Required** | رقم تتبع الطلبية الفريد |

**Example Request:**
```
POST {{url}}/api/v1/ask/for/order/return?tracking=ECO-123456789
```

---

### `GET /api/v1/get/tracking/info`
**سجل العمليات الكامل لطلبية واحدة.**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `tracking` | string | **Required** | رقم تتبع الطلبية الفريد |

**قيم حقل `activity` الممكنة في الرد:**

| القيمة | المعنى |
|---|---|
| `order_information_received_by_carrier` | تسجيل الطلبية والمصادقة عليها من طرف البائع |
| `picked` | استلام الطلبية من مزوّد التوصيل |
| `accepted_by_carrier` | استلام الطلبية من مركز الفرز (Hub/Station) |
| `dispatched_to_driver` | إرسال الطلبية لسائق التوصيل |
| `attempt_delivery` | محاولة توصيل |
| `return_asked` | بدء الإرجاع من مركز الفرز |
| `return_in_transit` | الإرجاع قيد النقل |
| `Return_received` | استلام البائع للإرجاع |
| `livred` | تم التوصيل |
| `encaissed` | تحصيل الدفع |
| `payed` | تحويل الدفعة للبائع |

**Example Request:**
```
GET {{url}}/api/v1/get/tracking/info?tracking=ECO-123456789
```

> 🔗 **مهم جدًا لصفحة تتبع الطلبيات:** هذه القيم الـ 11 هي أساس بناء عمود/جدول تتبع الحالة — يجب تخطيط mapping كامل بينها وبين حالات `OrderTrackingHistory` الداخلية + رموز `mystatuskit` قبل أي تنفيذ (سؤال قرار جديد يُضاف لقائمة القرارات المرقمة).

---

### `GET /api/v1/get/trackings/info`
**سجل العمليات الكامل لعدة طلبيات دفعة واحدة.**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `trackings[]` | array | **Required** | مصفوفة من أرقام التتبع |

**Example Request:**
```
GET {{url}}/api/v1/get/trackings/info?trackings[]=[ECO-111,ECO-222,ECO-333]
```

---

### `GET /api/v1/get/orders`
**استرجاع الطلبيات الجارية مع حالاتها الحالية.** النتائج مقسّمة على صفحات (40 طلبية/صفحة). افتراضيًا يُرجع طلبيات آخر 90 يوم. الطلبيات المؤرشفة مستثناة.

| Parameter | Type | Required | Description |
|---|---|---|---|
| `page` | integer | Optional | رقم الصفحة |
| `start_date` | date (Y-m-d) | Optional | فلترة حسب تاريخ إنشاء البداية |
| `end_date` | date (Y-m-d) | Optional | فلترة حسب تاريخ إنشاء النهاية |
| `tracking` | string | Optional | استرجاع معلومات طلبية واحدة محددة |

**Example Request:**
```
GET {{url}}/api/v1/get/orders?page=1&start_date=2024-01-01&end_date=2024-12-31
```

---

### `GET /api/v1/get/orders/status`
**فلترة الطلبيات حسب حالة أو أكثر.** الحد الأقصى: **100 رقم تتبع** لكل طلب.

| Parameter | Type | Required | Description |
|---|---|---|---|
| `api_token` | string | **Required** | الـ API token |
| `trackings` | string (comma-separated) | **Required** | أرقام التتبع مفصولة بفاصلة |
| `status` | string (comma-separated) | **Required** | قيم الحالة مفصولة بفاصلة (انظر القائمة أدناه) |

**القيم الممكنة لـ `status`:**
```
prete_a_expedier
en_ramassage
en_preparation_stock
vers_hub
en_hub
vers_wilaya
en_preparation
en_livraison
suspendu
livre_non_encaisse
encaisse_non_paye
paiements_prets
paye_et_archive
retour_chez_livreur
retour_transit_entrepot
retour_en_traitement
retour_recu
retour_archive
annule
all
```

**Example Request:**
```
GET {{url}}/api/v1/get/orders/status
?api_token=YOUR_TOKEN
&trackings=ECO-111,ECO-222,ECO-333
&status=en_livraison,suspendu
```

> 🔗 قائمة الحالات هنا (19 قيمة) **مختلفة تمامًا** عن قيم `activity` في `get/tracking/info` (11 قيمة). هاتان طبقتان منفصلتان من الحالة (status vs. activity/event) — يجب عدم خلطهما عند بناء mapping الـ `mystatuskit`، وتوثيق أيهما يُستخدم لعرض بادج الحالة الرئيسي في الجدول وأيهما يُستخدم لسجل الأحداث التفصيلي.

---

## 6. ⚙️ Configuration

### `GET /api/v1/get/wilayas`
**قائمة كل الولايات النشطة للتوصيل من طرف شركة التوصيل.**

```
GET {{url}}/api/v1/get/wilayas
```

---

### `GET /api/v1/get/communes`
**قائمة البلديات النشطة.** فلترة حسب `wilaya_id`.

| Parameter | Type | Required | Description |
|---|---|---|---|
| `wilaya_id` | integer (1–58) | Optional | فلترة البلديات حسب رمز الولاية |

```
GET {{url}}/api/v1/get/communes?wilaya_id=16
```

> 🔗 يجب دمج هذا مع منطق `communeScore()` الحالي (تنظيف guillemets/whitespace) — بيانات ECOTRACK قد تحتاج نفس المعالجة أو معالجة مختلفة، **لا تفترض تطابقها مع بيانات NOEST بدون تحقق فعلي.**

---

### `GET /api/v1/get/desks`
**قائمة كل مكاتب الـ Stop-Desk ونقاط الاستلام المتاحة.**

```
GET {{url}}/api/v1/get/desks
```

---

### `GET /api/v1/get/fees`
**أسعار التوصيل المطبّقة على الحساب لكل الولايات النشطة.** الأسعار مفصّلة لكل نوع خدمة ونمط توصيل:

- Delivery (Home / Stop Desk)
- Pickup (Home / Stop Desk)
- Exchange (Home / Stop Desk)
- Collection (Home / Stop Desk)
- Return (Home / Stop Desk)

```
GET {{url}}/api/v1/get/fees
```

> 🔗 هذا هو الـ endpoint المكافئ لـ "NOEST real price sync" (Phase 33.2). بنية الرد هنا أوسع (5 أنواع خدمة × نمطين = حتى 10 قيم سعر لكل ولاية) — يجب فحص الشكل الفعلي للرد قبل بناء جدول `carrier_wilaya_fees` أو ما يعادله، لأنه قد يحتاج أعمدة أكثر مما تدعمه بنية NOEST الحالية.

---

## 7. 🛒 Products

### `GET /api/v1/get/products/list`
**قائمة كل المنتجات المسجّلة في حساب ECOTRACK.**

```
GET {{url}}/api/v1/get/products/list
```

---

## 8. جدول ملخّص — كل الـ 20 Endpoint

| # | Method | Endpoint | الوصف |
|---|---|---|---|
| 1 | GET | `/api/v1/validate/token` | التحقق من صلاحية التوكن |
| 2 | GET | `/api/v1/` | معلومات حد الطلبات |
| 3 | POST | `/api/v1/create/order` | إنشاء طلبية واحدة |
| 4 | POST | `/api/v1/create/orders` | إنشاء طلبيات متعددة (حتى 100) |
| 5 | POST | `/api/v1/update/order` | تعديل طلبية |
| 6 | DELETE | `/api/v1/delete/order` | حذف طلبية |
| 7 | POST | `/api/v1/valid/order` | مصادقة وشحن طلبية |
| 8 | POST | `/api/v1/valid/returns` | تأكيد استلام المرتجعات |
| 9 | GET | `/api/v1/get/order/label` | تحميل ملصق الشحن |
| 10 | POST | `/api/v1/add/maj` | إضافة تحديث/ملاحظة على طرد |
| 11 | GET | `/api/v1/get/maj` | قائمة تحديثات طرد |
| 12 | POST | `/api/v1/ask/for/order/return` | طلب إرجاع طرد |
| 13 | GET | `/api/v1/get/tracking/info` | سجل عمليات طلبية واحدة |
| 14 | GET | `/api/v1/get/trackings/info` | سجل عمليات طلبيات متعددة |
| 15 | GET | `/api/v1/get/orders` | قائمة الطلبيات مع حالاتها |
| 16 | GET | `/api/v1/get/orders/status` | فلترة الطلبيات حسب الحالة |
| 17 | GET | `/api/v1/get/wilayas` | قائمة الولايات النشطة |
| 18 | GET | `/api/v1/get/communes` | قائمة البلديات النشطة |
| 19 | GET | `/api/v1/get/desks` | قائمة مكاتب Stop-Desk |
| 20 | GET | `/api/v1/get/fees` | أسعار التوصيل |
| — | GET | `/api/v1/get/products/list` | قائمة المنتجات *(غير معدود ضمن الـ 20 في التوثيق الأصلي، لكنه موثّق)* |

---

## 9. نقاط تحتاج تحقق فعلي قبل بناء الـ Adapter (لا تُفترض)

1. **شكل رد الأخطاء الموحّد** — التوثيق المستخرج لا يظهر نموذج خطأ عام (مثل نمط NOEST الذي يُرجع `HTTP 200` مع `success: false`). يجب اختبار عدة سيناريوهات فشل فعليًا (توكن خاطئ، بيانات ناقصة، تجاوز حد الطلبات) وتوثيق الشكل الحقيقي.
2. **الفرق بين `status` (19 قيمة) و`activity` (11 قيمة)** — يجب حسم أيهما يقود بادج `mystatuskit` الرئيسي في جدول التتبع.
3. **اختلاف أسماء الحقول بين `create/order` و`update/order`** (`nom_client`/`client`, `telephone`/`tel`, `code_wilaya`/`wilaya`, `produit`/`product`) — يجب تصميم Mapper صريح، لا Mapping ضمني.
4. **شكل رد `get/fees` الفعلي** — عدد وبنية الحقول لكل ولاية غير موضّح بالكامل في النص المستخرج؛ يلزم طلب تجريبي حقيقي.
5. **معاملات `valid/returns`** — غير موثقة بوضوح في المصدر (لا توجد جدول Query Parameters لهذا الـ endpoint).
6. **علاقة `X-RateLimit-*` headers المرسلة سابقًا بالمستخدم بـ endpoint رقم 2** — هل هي عامة على كل الطلبات أم خاصة بهذا الـ endpoint فقط؟

---

*هذا الملف جاهز ليُحفظ في `docs/توثيق_واجهة_برمجة_تطبيقات_Ecotrack_v1.md` كمرجع دائم للمشروع، بنفس مستوى التفصيل المعتمد لملف NOEST.*
