# Yalidine API Integration

## Overview

The **Yalidine API** provides a REST-based interface for integrating Yalidine delivery services into third-party applications and e-commerce platforms.

This documentation describes how **Edzeery** can communicate with the Yalidine API to:

- Authenticate API requests
- Retrieve wilayas
- Retrieve communes
- Retrieve delivery centers and stop desks
- Retrieve delivery fees
- Create parcels
- Retrieve parcels
- Update parcels
- Delete parcels
- Retrieve parcel tracking histories
- Calculate applicable delivery and overweight fees
- Handle pagination, filtering, ordering, and rate limits

The API uses predictable resource-oriented URLs, standard HTTP methods, JSON responses, HTTP status codes, and API-key authentication.

---

# 1. API Information

## Base URL

All API requests must use the following base URL:

```text
https://api.yalidine.app/v1/
```

### API Resources

| Resource | Endpoint | Method |
|---|---|---|
| Wilayas | `/wilayas` | GET |
| Communes | `/communes` | GET |
| Centers | `/centers` | GET |
| Fees | `/fees` | GET |
| Parcels | `/parcels` | GET, POST, PATCH, DELETE |
| Histories | `/histories` | GET |

---

# 2. Authentication

The Yalidine API uses an **API ID** and **API Token** to authenticate requests.

API credentials can be generated and managed from the Yalidine Developer Dashboard:

```text
https://www.yalidine.app/app/dev/index.php
```

## Security Requirements

API credentials must be treated as sensitive credentials.

Never:

- expose the API ID or API Token in frontend JavaScript;
- store credentials directly inside client-side code;
- commit credentials to a public Git repository;
- expose credentials in browser responses;
- share credentials with unauthorized users.

For Edzeery, Yalidine credentials should be stored in the server-side environment configuration.

Example:

```env
YALIDINE_API_ID=YOUR_API_ID
YALIDINE_API_TOKEN=YOUR_API_TOKEN
YALIDINE_BASE_URL=https://api.yalidine.app/v1
```

The credentials shown in the official examples should not be reused in production.

---

# 3. Authentication Headers

Every API request requires the following HTTP headers:

```http
X-API-ID: YOUR_API_ID
X-API-TOKEN: YOUR_API_TOKEN
```

For requests containing JSON data, use:

```http
Content-Type: application/json
```

### Example

```http
X-API-ID: 94986571734304520846
X-API-TOKEN: YOUR_API_TOKEN
```

---

# 4. Authentication Test

Before implementing the complete integration, it is recommended to test the credentials using the Wilayas endpoint.

## cURL

```bash
curl "https://api.yalidine.app/v1/wilayas/" \
  -H "X-API-ID: YOUR_API_ID" \
  -H "X-API-TOKEN: YOUR_API_TOKEN"
```

A successful response should return the list of wilayas.

This confirms that:

1. the API ID is valid;
2. the API Token is valid;
3. the API endpoint is reachable;
4. the authentication headers are correctly configured.

---

# 5. API Usage Policy

All API requests are logged by Yalidine.

Improper or abusive API usage may result in:

- API access being disabled;
- temporary restrictions;
- permanent loss of API access;
- account suspension.

Applications such as Edzeery should therefore implement caching, pagination, rate-limit monitoring, and controlled retry mechanisms.

---

# 6. Rate Limits

Yalidine applies rate limits to API requests.

The default limits are:

| Period | Default Limit |
|---|---:|
| Per second | 5 requests |
| Per minute | 50 requests |
| Per hour | 1,000 requests |
| Per day | 10,000 requests |

When a limit is exceeded, the API returns:

```http
429 Too Many Requests
```

The response includes a `Retry-After` header indicating how many seconds the client should wait before retrying.

---

## 6.1 Quota Headers

After every API request, Yalidine provides the remaining quota through HTTP response headers.

| Quota | Response Header | Reset |
|---|---|---|
| Per second | `x-second-quota-left` | 1 second after the first request |
| Per minute | `x-minute-quota-left` | 60 seconds after the first request |
| Per hour | `x-hour-quota-left` | 1 hour after the first request |
| Per day | `x-day-quota-left` | 24 hours after the first request |

Example:

```http
x-second-quota-left: 4
x-minute-quota-left: 47
x-hour-quota-left: 998
x-day-quota-left: 9997
```

Edzeery should monitor these headers and avoid unnecessary requests when the remaining quota is low.

Repeatedly exceeding the quota may cause increasingly long API restrictions.

---

# 7. Pagination

Most list endpoints support pagination using:

```text
page
page_size
```

## Parameters

| Parameter | Type | Description |
|---|---|---|
| `page` | integer | Page number |
| `page_size` | integer | Number of results per page |

The default `page_size` is generally `100`.

The maximum is:

```text
1000
```

The minimum is:

```text
1
```

### Example

```http
GET /v1/wilayas/?page=2&page_size=20
```

---

## 7.1 Paginated Response

A typical paginated response contains:

```json
{
    "has_more": true,
    "total_data": 58,
    "data": [
        {
            "id": 4,
            "name": "Oum El Bouaghi",
            "zone": 2,
            "is_deliverable": 1
        },
        {
            "id": 5,
            "name": "Batna",
            "zone": 2,
            "is_deliverable": 1
        }
    ],
    "links": {
        "self": "https://api.yalidine.app/v1/wilayas/?page_size=2&page=2",
        "before": "https://api.yalidine.app/v1/wilayas/?page_size=2&page=1",
        "next": "https://api.yalidine.app/v1/wilayas/?page_size=2&page=3"
    }
}
```

### Response Properties

| Property | Type | Description |
|---|---|---|
| `has_more` | boolean | Indicates whether more results are available |
| `total_data` | integer | Total number of matching records |
| `data` | array | Current page results |
| `links.self` | string | Current request URL |
| `links.before` | string | Previous page URL, when available |
| `links.next` | string | Next page URL, when available |

---

# 8. Wilayas

The Wilayas endpoint provides the list of Algerian wilayas supported by Yalidine.

## Endpoints

```http
GET /v1/wilayas
GET /v1/wilayas/:id
```

---

## 8.1 Retrieve All Wilayas

```bash
curl "https://api.yalidine.app/v1/wilayas/" \
  -H "X-API-ID: YOUR_API_ID" \
  -H "X-API-TOKEN: YOUR_API_TOKEN"
```

### Example Response

```json
{
    "has_more": false,
    "total_data": 58,
    "data": [
        {
            "id": 1,
            "name": "Adrar",
            "zone": 4,
            "is_deliverable": 1
        },
        {
            "id": 5,
            "name": "Batna",
            "zone": 2,
            "is_deliverable": 1
        }
    ],
    "links": {
        "self": "https://api.yalidine.app/v1/wilayas/"
    }
}
```

---

## 8.2 Retrieve a Specific Wilaya

```http
GET /v1/wilayas/15
```

---

## 8.3 Retrieve Multiple Wilayas

```http
GET /v1/wilayas/?id=15,16,5
```

Multiple IDs are separated by commas.

---

## 8.4 Filter Wilayas

Example:

```http
GET /v1/wilayas/?id=19
```

Multiple values:

```http
GET /v1/wilayas/?id=16,19,6
```

---

## 8.5 Select Specific Fields

Use the `fields` parameter.

```http
GET /v1/wilayas/?fields=id,name
```

Example response:

```json
{
    "data": [
        {
            "id": 1,
            "name": "Adrar"
        },
        {
            "id": 5,
            "name": "Batna"
        }
    ]
}
```

---

## 8.6 Wilaya Fields

| Field | Type | Description |
|---|---|---|
| `id` | integer | Wilaya identifier |
| `name` | string | Wilaya name |
| `zone` | integer | Delivery zone |
| `is_deliverable` | boolean | Whether the wilaya is deliverable |

---

## 8.7 Ordering

Supported ordering fields:

```text
id
name
```

Example:

```http
GET /v1/wilayas/?order_by=name
```

Descending:

```http
GET /v1/wilayas/?order_by=name&desc
```

Ascending:

```http
GET /v1/wilayas/?order_by=name&asc
```

---

# 9. Communes

The Communes endpoint provides commune-level delivery information.

This endpoint is particularly important for Edzeery because the destination commune is required when creating a parcel.

## Endpoints

```http
GET /v1/communes
GET /v1/communes/:id
```

---

## 9.1 Retrieve Communes

```bash
curl "https://api.yalidine.app/v1/communes/" \
  -H "X-API-ID: YOUR_API_ID" \
  -H "X-API-TOKEN: YOUR_API_TOKEN"
```

### Example Response

```json
{
    "has_more": true,
    "total_data": 1541,
    "data": [
        {
            "id": 101,
            "name": "Adrar",
            "wilaya_id": 1,
            "wilaya_name": "Adrar",
            "has_stop_desk": 0,
            "is_deliverable": 1,
            "delivery_time_parcel": 20,
            "delivery_time_payment": 10
        }
    ],
    "links": {
        "self": "https://api.yalidine.app/v1/communes/",
        "next": "https://api.yalidine.app/v1/communes/?page=2"
    }
}
```

---

## 9.2 Retrieve a Specific Commune

```http
GET /v1/communes/1630
```

---

## 9.3 Retrieve Multiple Communes

```http
GET /v1/communes/?id=1630,1601,1620
```

---

## 9.4 Filter by Stop Desk Availability

To retrieve communes that have a stop desk:

```http
GET /v1/communes/?has_stop_desk=true
```

Filter by both stop-desk availability and wilaya:

```http
GET /v1/communes/?has_stop_desk=true&wilaya_id=16
```

Multiple wilayas:

```http
GET /v1/communes/?wilaya_id=16,19,6
```

---

## 9.5 Commune Fields

| Field | Type | Description |
|---|---|---|
| `id` | integer | Commune identifier |
| `name` | string | Commune name |
| `wilaya_id` | integer | Wilaya identifier |
| `wilaya_name` | string | Wilaya name |
| `has_stop_desk` | boolean | Whether the commune has a stop desk |
| `is_deliverable` | boolean | Whether the commune is deliverable |
| `delivery_time_parcel` | integer | Average parcel delivery time in days |
| `delivery_time_payment` | integer | Average payment delivery time in days |

---

## 9.6 Select Specific Fields

```http
GET /v1/communes/?fields=name,is_deliverable
```

---

## 9.7 Ordering

Supported fields:

```text
id
wilaya_id
```

Example:

```http
GET /v1/communes/?order_by=wilaya_id
```

Descending:

```http
GET /v1/communes/?order_by=wilaya_id&desc
```

Ascending:

```http
GET /v1/communes/?order_by=wilaya_id&asc
```

---

# 10. Centers and Stop Desks

The Centers endpoint provides Yalidine centers and stop-desk locations.

## Endpoints

```http
GET /v1/centers
GET /v1/centers/:center_id
```

The `center_id` is especially important when creating a stop-desk parcel.

---

## 10.1 Retrieve Centers

```bash
curl "https://api.yalidine.app/v1/centers/" \
  -H "X-API-ID: YOUR_API_ID" \
  -H "X-API-TOKEN: YOUR_API_TOKEN"
```

### Example Response

```json
{
    "has_more": false,
    "total_data": 99,
    "data": [
        {
            "center_id": 10101,
            "name": "Centre de Adrar",
            "address": "Cité el moudjahidine",
            "gps": "27.872313093666232,-0.2959112704377818",
            "commune_id": 101,
            "commune_name": "Adrar",
            "wilaya_id": 1,
            "wilaya_name": "Adrar"
        }
    ],
    "links": {
        "self": "https://api.yalidine.app/v1/centers/"
    }
}
```

---

## 10.2 Retrieve a Specific Center

```http
GET /v1/centers/10101
```

---

## 10.3 Retrieve Multiple Centers

```http
GET /v1/centers/?center_id=10101,163001,190102
```

---

## 10.4 Filter Centers by Wilaya

```http
GET /v1/centers/?wilaya_id=19
```

Multiple wilayas:

```http
GET /v1/centers/?wilaya_id=16,19,6
```

---

## 10.5 Center Fields

| Field | Type | Description |
|---|---|---|
| `center_id` | integer | Center identifier |
| `name` | string | Center name |
| `address` | string | Center address |
| `gps` | string | GPS coordinates |
| `commune_id` | integer | Commune identifier |
| `commune_name` | string | Commune name |
| `wilaya_id` | integer | Wilaya identifier |
| `wilaya_name` | string | Wilaya name |

---

## 10.6 Select Specific Fields

```http
GET /v1/centers/?fields=center_id,name
```

---

## 10.7 Ordering

Supported fields:

```text
center_id
commune_id
wilaya_id
```

Example:

```http
GET /v1/centers/?order_by=commune_id
```

Descending:

```http
GET /v1/centers/?order_by=wilaya_id&desc
```

Ascending:

```http
GET /v1/centers/?order_by=wilaya_id&asc
```

---

# 11. Delivery Fees

The Fees endpoint retrieves delivery prices between a sender's wilaya and a destination wilaya.

## Endpoint

```http
GET /v1/fees/?from_wilaya_id=value1&to_wilaya_id=value2
```

Both parameters are required:

```text
from_wilaya_id
to_wilaya_id
```

---

## 11.1 Example

```http
GET /v1/fees/?from_wilaya_id=5&to_wilaya_id=1
```

### cURL

```bash
curl "https://api.yalidine.app/v1/fees/?from_wilaya_id=5&to_wilaya_id=1" \
  -H "X-API-ID: YOUR_API_ID" \
  -H "X-API-TOKEN: YOUR_API_TOKEN"
```

---

## 11.2 Example Response

```json
{
    "from_wilaya_name": "Batna",
    "to_wilaya_name": "Adrar",
    "zone": 4,
    "retour_fee": 250,
    "cod_percentage": 0.75,
    "insurance_percentage": 0.75,
    "oversize_fee": 100,
    "per_commune": {
        "101": {
            "commune_id": 101,
            "commune_name": "Adrar",
            "express_home": 1400,
            "express_desk": 1100,
            "economic_home": null,
            "economic_desk": null
        }
    }
}
```

---

## 11.3 Fee Response Fields

| Field | Type | Description |
|---|---|---|
| `from_wilaya_name` | string | Sender's wilaya |
| `to_wilaya_name` | string | Destination wilaya |
| `zone` | integer | Route zone |
| `retour_fee` | integer | Return fee |
| `cod_percentage` | float | COD fee percentage |
| `insurance_percentage` | float | Insurance percentage |
| `oversize_fee` | integer | Additional fee per kilogram above the threshold |

---

## 11.4 Commune Delivery Fees

Each commune may contain:

| Field | Type | Description |
|---|---|---|
| `commune_id` | integer | Commune identifier |
| `commune_name` | string | Commune name |
| `express_home` | integer | Express home delivery fee |
| `express_desk` | integer | Express stop-desk delivery fee |
| `economic_home` | integer/null | Economy home delivery fee, if available |
| `economic_desk` | integer/null | Economy stop-desk delivery fee, if available |

The listed delivery fee includes the commune tax but does not include the applicable weight/overweight fee.

---

# 12. Overweight and Billable Weight

Yalidine determines the billable weight using the higher value between:

1. Actual weight
2. Volumetric weight

## Volumetric Weight Formula

```text
Volumetric Weight =
Width × Height × Length × 0.0002
```

Dimensions are measured in centimeters.

Actual weight is measured in kilograms.

---

## 12.1 Billable Weight

```text
Billable Weight =
MAX(Actual Weight, Volumetric Weight)
```

---

## 12.2 Overweight Fee

The first 5 KG are free.

If the billable weight is less than or equal to 5 KG:

```text
Overweight Fee = 0 DA
```

If the billable weight exceeds 5 KG:

```text
Overweight Fee =
(Billable Weight - 5) × oversize_fee
```

---

## 12.3 Example

Assume:

```text
Width  = 20 cm
Height = 10 cm
Length = 30 cm
Weight = 6 KG
```

Volumetric weight:

```text
20 × 10 × 30 × 0.0002
= 1.2 KG
```

Actual weight:

```text
6 KG
```

Therefore:

```text
Billable Weight = 6 KG
```

If:

```text
oversize_fee = 100 DA
```

Then:

```text
Overweight Fee =
(6 - 5) × 100
= 100 DA
```

The final delivery cost is:

```text
Delivery Fee + Overweight Fee
```

---

# 13. Parcels

The Parcels resource is the core resource used to create, retrieve, update, and delete shipments.

## Endpoints

```http
GET /v1/parcels
GET /v1/parcels/:tracking
POST /v1/parcels
DELETE /v1/parcels/:tracking
PATCH /v1/parcels/:tracking
```

---

# 14. Retrieve Parcels

## Retrieve Parcels

```http
GET /v1/parcels
```

### cURL

```bash
curl "https://api.yalidine.app/v1/parcels/" \
  -H "X-API-ID: YOUR_API_ID" \
  -H "X-API-TOKEN: YOUR_API_TOKEN"
```

---

## 14.1 Example Response

```json
{
    "has_more": true,
    "total_data": 7457,
    "data": [
        {
            "tracking": "yal-123456",
            "order_id": "#eadoeead",
            "firstname": "M*****d",
            "familyname": "E* A****",
            "contact_phone": "0********9",
            "address": "C*** K****",
            "is_stopdesk": 1,
            "stopdesk_id": 163001,
            "stopdesk_name": "Centre de Bordj El Kiffan",
            "from_wilaya_id": 5,
            "from_wilaya_name": "Batna",
            "to_commune_id": 1630,
            "to_commune_name": "Bordj El Kiffan",
            "to_wilaya_id": 16,
            "to_wilaya_name": "Alger",
            "product_list": "Machine à café",
            "price": 2400,
            "do_insurance": true,
            "declared_value": 5000,
            "delivery_fee": 500,
            "freeshipping": 0,
            "import_id": 233,
            "date_creation": "2020-03-25 18:44:22",
            "date_expedition": null,
            "date_last_status": "2020-03-25 18:44:22",
            "last_status": "Centre",
            "taxe_percentage": 1.5,
            "taxe_from": 10000,
            "taxe_retour": 300,
            "parcel_type": "ecommerce",
            "parcel_sub_type": null,
            "has_receipt": null,
            "length": null,
            "width": null,
            "height": null,
            "weight": null,
            "has_recouvrement": 1,
            "return_center_code": "RC01",
            "current_center_id": 190201,
            "current_center_name": "Centre de Aïn Arnat",
            "current_wilaya_id": 19,
            "current_wilaya_name": "Sétif",
            "current_commune_id": 1902,
            "current_commune_name": "Aïn Arnat",
            "payment_status": "not-ready",
            "payment_id": null,
            "has_exchange": 0,
            "product_to_collect": null,
            "label": "https://yalidine.app/app/bordereau.php?tracking=yal-123456",
            "pin": "1572",
            "qr_text": "16,yal-123456,1630,Store Name,6548,16,0********9"
        }
    ],
    "links": {
        "self": "https://api.yalidine.app/v1/parcels/",
        "next": "https://api.yalidine.app/v1/parcels/?page=2"
    }
}
```

---

# 15. Personal Data Masking

For security and privacy reasons, personal data is masked in `GET` and `PATCH` responses.

Masked fields include:

```text
firstname
familyname
contact_phone
address
phone segment in qr_text
```

This masking does not apply to data submitted through `POST`.

Edzeery must not use masked response values to overwrite the original customer data stored in its own database.

---

# 16. Retrieve a Specific Parcel

Use the tracking number in the URL:

```http
GET /v1/parcels/yal-123456
```

---

# 17. Retrieve Multiple Parcels

Use the `tracking` query parameter:

```http
GET /v1/parcels/?tracking=yal-123456,yal-789123,yal-456789
```

---

# 18. Parcel Filters

Parcels can be filtered using one or more query parameters.

## Example: Free Shipping

```http
GET /v1/parcels/?freeshipping=true
```

## Example: Free Shipping + Algiers

```http
GET /v1/parcels/?freeshipping=true&to_wilaya_id=16
```

## Multiple Statuses

```http
GET /v1/parcels/?last_status=Expédié,Livré
```

Multiple values can generally be separated using commas.

Date filters are an exception.

---

# 19. Parcel Filter Parameters

| Parameter | Type | Description |
|---|---|---|
| `tracking` | string | Unique parcel tracking number |
| `order_id` | string | Merchant/customer order identifier |
| `import_id` | integer | Bulk parcel creation operation ID |
| `to_wilaya_id` | integer | Destination wilaya ID |
| `to_commune_name` | string | Destination commune name |
| `is_stopdesk` | boolean | Stop-desk or home delivery |
| `is_exchange` | boolean | Whether this is an exchange parcel |
| `has_exchange` | boolean | Whether an exchange should be requested |
| `freeshipping` | boolean | Whether delivery is paid by the sender |
| `date_creation` | string | Parcel creation date |
| `date_last_status` | string | Date of latest status |
| `payment_status` | string | Current payment status |
| `last_status` | string | Current delivery status |
| `fields` | string | Fields to return |
| `page` | integer | Page number |
| `page_size` | integer | Results per page |
| `order_by` | string | Ordering field |
| `desc` | flag | Descending order |
| `asc` | flag | Ascending order |

---

# 20. Date Filters

Date filters use:

```text
YYYY-MM-DD
```

## Single Date

```http
GET /v1/parcels/?date_creation=2020-06-01
```

This returns parcels created on the specified date.

## Date Range

```http
GET /v1/parcels/?date_creation=2020-06-01,2020-07-01
```

This returns results between the specified dates.

The same approach applies to:

```text
date_last_status
```

---

# 21. Payment Status

The `payment_status` field can contain:

```text
not-ready
ready
receivable
payed
```

Example:

```http
GET /v1/parcels/?payment_status=ready
```

---

# 22. Parcel Delivery Statuses

The `last_status` field can contain statuses such as:

```text
Pas encore expédié
A vérifier
En préparation
Pas encore ramassé
Prêt à expédier
En passation
Ramassé
Bloqué
Débloqué
Transfert
Expédié
Centre
En localisation
Vers Wilaya
En transit
Reçu à Wilaya
En attente du client
Prêt pour livreur
Sorti en livraison
En attente
Annulé
En alerte
Tentative échouée
Livré
Echèc livraison
Retour vers centre
Retourné au centre
Retour transfert
Retour groupé
Retour à retirer
Retour non retiré
Colis abandonné
Retour vers vendeur
Retourné au vendeur
Echange échoué
```

For the complete status timeline of a parcel, use the Histories endpoint.

---

# 23. Parcel Fields

| Field | Type | Description |
|---|---|---|
| `tracking` | string | Unique parcel identifier |
| `order_id` | string | Order identifier |
| `firstname` | string | Receiver first name |
| `familyname` | string | Receiver family name |
| `contact_phone` | string | Receiver phone number(s) |
| `address` | string | Receiver address |
| `is_stopdesk` | boolean | Stop-desk or home delivery |
| `stopdesk_id` | integer | Stop-desk center ID |
| `from_wilaya_id` | integer | Sender wilaya ID |
| `from_wilaya_name` | string | Sender wilaya |
| `to_commune_id` | integer | Destination commune ID |
| `to_commune_name` | string | Destination commune |
| `to_wilaya_id` | integer | Destination wilaya ID |
| `to_wilaya_name` | string | Destination wilaya |
| `product_list` | string | Shipment content description |
| `price` | integer | Amount to recover from receiver |
| `do_insurance` | boolean | Insurance enabled |
| `declared_value` | integer | Declared financial value |
| `delivery_fee` | integer | Delivery fee |
| `freeshipping` | boolean | Delivery fee paid by sender |
| `import_id` | integer | Import/bulk creation operation ID |
| `date_creation` | string | Creation timestamp |
| `date_expedition` | string | Expedition timestamp |
| `date_last_status` | string | Latest status timestamp |
| `last_status` | string | Current delivery status |
| `taxe_percentage` | float | COD fee percentage |
| `taxe_from` | integer | Price threshold for COD percentage |
| `taxe_retour` | integer | Parcel return fee |
| `parcel_type` | string | Parcel type |
| `parcel_sub_type` | string | Parcel subtype |
| `has_receipt` | boolean | Acknowledgment of receipt |
| `length` | integer | Length in centimeters |
| `width` | integer | Width in centimeters |
| `height` | integer | Height in centimeters |
| `weight` | integer | Parcel weight |
| `has_recouvrement` | boolean | Cash-on-delivery enabled |
| `return_center_code` | string | Seller return center code |
| `current_center_id` | integer | Current center ID |
| `current_center_name` | string | Current center name |
| `current_wilaya_id` | integer | Current wilaya ID |
| `current_wilaya_name` | string | Current wilaya |
| `current_commune_id` | integer | Current commune ID |
| `current_commune_name` | string | Current commune |
| `payment_status` | string | Current payment status |
| `payment_id` | string | Payment manifest ID |
| `has_exchange` | boolean | Exchange request enabled |
| `product_to_collect` | string | Product to collect for exchange |
| `label` | string | Current parcel label URL |
| `labels` | string | Labels URL for parcels created in the request |
| `qr_text` | string | QR code text |
| `pin` | string | Parcel label PIN |

---

# 24. Parcel Types

The `parcel_type` value can be:

```text
classic
ecommerce
multiseller
```

Possible `parcel_sub_type` values include:

```text
accuse
exchange
rcc
rccback
sm
```

---

# 25. Parcel Ordering

By default, parcel results are ordered by:

```text
date_creation DESC
```

Supported `order_by` fields:

```text
date_creation
date_last_status
tracking
order_id
import_id
to_wilaya_id
to_commune_id
last_status
```

Example:

```http
GET /v1/parcels/?order_by=date_last_status
```

Ascending:

```http
GET /v1/parcels/?order_by=date_last_status&asc
```

Descending:

```http
GET /v1/parcels/?order_by=date_last_status&desc
```

---

# 26. Select Parcel Fields

The `fields` parameter can reduce the response payload.

Example:

```http
GET /v1/parcels/?fields=to_wilaya_name,tracking
```

This is recommended when Edzeery only needs a small amount of information.

---

# 27. Create Parcels

Create parcels using:

```http
POST /v1/parcels
```

The request body must contain an array of one or more parcel objects.

This allows multiple parcels to be created in a single API request.

---

# 28. Create a Single Parcel

### Request

```http
POST /v1/parcels/
Content-Type: application/json
X-API-ID: YOUR_API_ID
X-API-TOKEN: YOUR_API_TOKEN
```

### Example JSON

```json
[
    {
        "order_id": "EDZEERY-10001",
        "from_wilaya_name": "Batna",
        "firstname": "Brahim",
        "familyname": "Mohamed",
        "contact_phone": "0550123456",
        "address": "Cité Kaidi",
        "to_commune_name": "Bordj El Kiffan",
        "to_wilaya_name": "Alger",
        "product_list": "Coffee Machine",
        "price": 3000,
        "do_insurance": true,
        "declared_value": 3500,
        "height": 10,
        "width": 20,
        "length": 30,
        "weight": 6,
        "freeshipping": true,
        "is_stopdesk": true,
        "stopdesk_id": 163001,
        "has_exchange": false,
        "product_to_collect": null
    }
]
```

---

# 29. Create Parcel with cURL

```bash
curl -X POST "https://api.yalidine.app/v1/parcels/" \
  -H "X-API-ID: YOUR_API_ID" \
  -H "X-API-TOKEN: YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '[
    {
      "order_id": "EDZEERY-10001",
      "from_wilaya_name": "Batna",
      "firstname": "Brahim",
      "familyname": "Mohamed",
      "contact_phone": "0550123456",
      "address": "Cité Kaidi",
      "to_commune_name": "Bordj El Kiffan",
      "to_wilaya_name": "Alger",
      "product_list": "Coffee Machine",
      "price": 3000,
      "do_insurance": true,
      "declared_value": 3500,
      "height": 10,
      "width": 20,
      "length": 30,
      "weight": 6,
      "freeshipping": true,
      "is_stopdesk": true,
      "stopdesk_id": 163001,
      "has_exchange": false,
      "product_to_collect": null
    }
  ]'
```

---

# 30. Create Multiple Parcels

Multiple parcels can be included in the same request.

```json
[
    {
        "order_id": "EDZEERY-10001",
        "from_wilaya_name": "Batna",
        "firstname": "Brahim",
        "familyname": "Mohamed",
        "contact_phone": "0550123456",
        "address": "Cité Kaidi",
        "to_commune_name": "Bordj El Kiffan",
        "to_wilaya_name": "Alger",
        "product_list": "Coffee Machine",
        "price": 3000,
        "do_insurance": true,
        "declared_value": 3500,
        "height": 10,
        "width": 20,
        "length": 30,
        "weight": 6,
        "freeshipping": true,
        "is_stopdesk": true,
        "stopdesk_id": 163001,
        "has_exchange": false,
        "product_to_collect": null
    },
    {
        "order_id": "EDZEERY-10002",
        "from_wilaya_name": "Batna",
        "firstname": "Rufida",
        "familyname": "Ben Mehidi",
        "contact_phone": "0550123456",
        "address": "Hay El Yasmin",
        "to_commune_name": "Ouled Fayet",
        "to_wilaya_name": "Alger",
        "product_list": "Cooking Books",
        "price": 2400,
        "do_insurance": false,
        "declared_value": 3500,
        "height": 10,
        "width": 20,
        "length": 30,
        "weight": 6,
        "freeshipping": false,
        "is_stopdesk": false,
        "has_exchange": false,
        "product_to_collect": null
    }
]
```

---

# 31. Create Parcel Parameters

| Parameter | Required | Type | Description |
|---|---|---|---|
| `order_id` | Yes | string | Unique order identifier within the request |
| `opening_decision` | No | string | `accept` or `reject` |
| `from_wilaya_name` | Yes | string | Sender's wilaya |
| `firstname` | Yes | string | Receiver first name |
| `familyname` | Yes | string | Receiver family name |
| `contact_phone` | Yes | string | Receiver phone number(s) |
| `address` | Yes | string | Receiver address |
| `to_commune_name` | Yes | string | Destination commune |
| `to_wilaya_name` | Yes | string | Destination wilaya |
| `product_list` | Yes | string | Parcel content description |
| `price` | Yes | integer | Amount to recover from receiver |
| `do_insurance` | Yes | boolean | Whether insurance is enabled |
| `declared_value` | Yes | integer | Declared parcel value |
| `length` | Yes | integer | Length in cm |
| `width` | Yes | integer | Width in cm |
| `height` | Yes | integer | Height in cm |
| `weight` | Yes | integer | Parcel weight |
| `freeshipping` | Yes | boolean | Whether sender pays delivery |
| `is_stopdesk` | Yes | boolean | Stop-desk or home delivery |
| `stopdesk_id` | Conditional | integer | Required when `is_stopdesk=true` |
| `has_exchange` | Yes | boolean | Whether an exchange is requested |
| `product_to_collect` | Conditional | string | Required when `has_exchange=true` |

---

# 32. Important Stop-Desk Rule

When:

```json
"is_stopdesk": true
```

the request **must include**:

```json
"stopdesk_id": 163001
```

The `stopdesk_id` corresponds to the Yalidine center/stop-desk where the parcel should be delivered.

The available center IDs can be retrieved through:

```http
GET /v1/centers/
```

---

# 33. Phone Number Requirements

The `contact_phone` value must:

- start with `0`;
- contain 9 digits for mobile numbers;
- contain 8 digits for landline numbers.

Examples:

```text
0550123456
023456789
```

Multiple phone numbers can be separated by commas:

```text
0550123456,0660123456
```

---

# 34. Price and Declared Value

The parcel `price` must be between:

```text
0 and 150000
```

The `declared_value` must also be between:

```text
0 and 150000
```

`price` represents the amount Edzeery/Yalidine should recover from the receiver.

`declared_value` represents the financial estimation of the parcel's contents.

---

# 35. Insurance

The `do_insurance` parameter determines whether insurance is enabled.

Example:

```json
"do_insurance": true
```

According to the supplied Yalidine documentation, enabling insurance applies a 1.5% fee based on the declared value and provides 100% refund coverage.

---

# 36. Free Shipping

The `freeshipping` parameter determines who pays the delivery fee.

```json
"freeshipping": true
```

means:

```text
Sender pays the delivery fee.
```

Whereas:

```json
"freeshipping": false
```

means:

```text
Receiver pays the delivery fee.
```

---

# 37. Exchange Parcels

To request an exchange:

```json
"has_exchange": true
```

You must also specify:

```json
"product_to_collect": "Original product"
```

Example:

```json
{
    "has_exchange": true,
    "product_to_collect": "Blue jacket, size M"
}
```

If `has_exchange` is `true` and `product_to_collect` is missing, the API returns an error.

---

# 38. Create Parcel Response

The API returns the relationship between the submitted `order_id` and the generated Yalidine tracking number.

### Example

```json
{
    "EDZEERY-10001": {
        "success": true,
        "order_id": "EDZEERY-10001",
        "tracking": "yal-12345A",
        "import_id": 234,
        "label": "https://yalidine.app/app/bordereau.php?tracking=yal-12345A",
        "labels": "https://yalidine.app/app/bordereau.php?import_id=352",
        "message": ""
    }
}
```

---

# 39. Partial Success

When multiple parcels are submitted in one request, each parcel is processed independently.

A valid parcel can be created even if another parcel in the same request fails.

Example:

```json
{
    "EDZEERY-10001": {
        "success": true,
        "order_id": "EDZEERY-10001",
        "tracking": "yal-12345A",
        "import_id": 234,
        "label": "https://yalidine.app/app/bordereau.php?tracking=yal-12345A",
        "labels": "https://yalidine.app/app/bordereau.php?import_id=352",
        "message": ""
    },
    "EDZEERY-10002": {
        "success": false,
        "order_id": "EDZEERY-10002",
        "tracking": null,
        "import_id": null,
        "label": null,
        "labels": null,
        "message": "The do_insurance parameter must be of type boolean"
    }
}
```

Edzeery must therefore evaluate the `success` property for **each submitted parcel**, rather than treating the entire request as successful or failed.

---

# 40. Order ID Requirements

`order_id` is required.

It identifies which Edzeery order corresponds to which Yalidine tracking number.

Within the same API request, duplicated `order_id` values cannot be used.

Recommended Edzeery format:

```text
EDZEERY-{ORDER_ID}
```

Example:

```text
EDZEERY-10001
EDZEERY-10002
EDZEERY-10003
```

The exact internal format can be adapted to Edzeery's order model.

---

# 41. Update a Parcel

A parcel can be updated using:

```http
PATCH /v1/parcels/:tracking
```

Example:

```http
PATCH /v1/parcels/yal-123456
```

A parcel can only be edited while its latest status is:

```text
En préparation
```

---

# 42. Update Request

Only the fields that need to be changed have to be included.

Fields that are not provided remain unchanged.

### Example

```json
{
    "firstname": "Mustapha",
    "freeshipping": true
}
```

### cURL

```bash
curl -X PATCH "https://api.yalidine.app/v1/parcels/yal-123456" \
  -H "X-API-ID: YOUR_API_ID" \
  -H "X-API-TOKEN: YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "Mustapha",
    "freeshipping": true
  }'
```

---

# 43. Update Parameters

| Parameter | Required | Type | Description |
|---|---|---|---|
| `order_id` | No | string | Parcel order ID |
| `opening_decision` | No | string | Opening authorization |
| `firstname` | No | string | Receiver first name |
| `familyname` | No | string | Receiver family name |
| `contact_phone` | No | string | Receiver phone number(s) |
| `address` | No | string | Receiver address |
| `from_wilaya_name` | No | string | Sender wilaya |
| `to_commune_name` | Conditional | string | Required when changing destination commune |
| `to_wilaya_name` | No | string | Destination wilaya |
| `product_list` | No | string | Shipment contents |
| `price` | No | integer | Amount to recover |
| `do_insurance` | No | boolean | Insurance |
| `declared_value` | No | integer | Declared value |
| `length` | No | integer | Length in cm |
| `width` | No | integer | Width in cm |
| `height` | No | integer | Height in cm |
| `weight` | No | integer | Weight |
| `freeshipping` | No | boolean | Sender/receiver delivery payment |
| `is_stopdesk` | No | boolean | Stop-desk/home delivery |
| `stopdesk_id` | Conditional | integer | Required when `is_stopdesk=true` |
| `has_exchange` | No | boolean | Exchange request |
| `product_to_collect` | Conditional | string | Required when `has_exchange=true` |

---

# 44. Important PATCH Security Rule

Personal information returned by `PATCH` is masked.

For example:

```json
{
    "firstname": "M******a",
    "familyname": "M*****d",
    "contact_phone": "0********9",
    "address": "C*** K****"
}
```

These masked values must **not** be used to overwrite the customer's original information in Edzeery.

---

# 45. Update Response

A successful update returns the updated parcel object.

Example:

```json
{
    "tracking": "yal-123456",
    "order_id": "EDZEERY-10001",
    "firstname": "M******a",
    "familyname": "M*****d",
    "contact_phone": "0********9",
    "from_wilaya_name": "Adrar",
    "address": "C*** K****",
    "to_commune_name": "Bordj El Kiffan",
    "to_wilaya_name": "Alger",
    "product_list": "The product list",
    "length": 10,
    "height": 1,
    "width": 20,
    "weight": 3,
    "price": 3000,
    "do_insurance": true,
    "declared_value": 10000,
    "freeshipping": true,
    "is_stopdesk": false,
    "stopdesk_id": null,
    "has_exchange": false,
    "product_to_collect": null,
    "label": "https://yalidine.app/app/bordereau.php?tracking=yal-123456"
}
```

---

# 46. Delete Parcels

A parcel can only be deleted while its latest status is:

```text
En préparation
```

There are two deletion methods.

---

## 46.1 Delete a Specific Parcel

```http
DELETE /v1/parcels/yal-123456
```

### cURL

```bash
curl -X DELETE "https://api.yalidine.app/v1/parcels/yal-123456" \
  -H "X-API-ID: YOUR_API_ID" \
  -H "X-API-TOKEN: YOUR_API_TOKEN"
```

---

## 46.2 Delete Multiple Parcels

```http
DELETE /v1/parcels/?tracking=yal-123456,yal-789102
```

Tracking numbers are separated by commas.

---

# 47. Delete Response

The API returns the deletion result for each tracking number.

Example:

```json
[
    {
        "tracking": "yal-12345A",
        "deleted": true
    },
    {
        "tracking": "yal-789102",
        "deleted": false
    }
]
```

`deleted: false` can mean that:

- the parcel cannot be deleted;
- the tracking number is incorrect;
- the parcel does not exist;
- the parcel was already deleted.

---

# 48. Histories

The Histories resource provides the complete status history of parcels.

It can be used by Edzeery to build:

- parcel tracking timelines;
- delivery progress;
- status synchronization;
- delivery analytics;
- customer tracking pages.

## Endpoints

```http
GET /v1/histories
GET /v1/histories/:tracking
```

---

# 49. Retrieve Histories

```bash
curl "https://api.yalidine.app/v1/histories/" \
  -H "X-API-ID: YOUR_API_ID" \
  -H "X-API-TOKEN: YOUR_API_TOKEN"
```

### Example Response

```json
{
    "has_more": true,
    "total_data": 65465,
    "data": [
        {
            "date_status": "2022-12-17 01:48:09",
            "tracking": "yal-337AAS",
            "status": "Sorti en livraison",
            "reason": "",
            "center_id": 120201,
            "center_name": "Centre de Bir el Ater",
            "wilaya_id": 12,
            "wilaya_name": "Tébessa",
            "commune_id": 1202,
            "commune_name": "Bir el Ater"
        }
    ],
    "links": {
        "self": "https://api.yalidine.app/v1/histories/",
        "next": "https://api.yalidine.app/v1/histories/?page=2"
    }
}
```

---

# 50. Retrieve History for One Parcel

```http
GET /v1/histories/yal-123456
```

This returns the status history associated with the specified tracking number.

---

# 51. Retrieve Histories for Multiple Parcels

```http
GET /v1/histories/?tracking=yal-123456,yal-789123,yal-456789
```

---

# 52. Filter Histories by Status

Example:

```http
GET /v1/histories/?status=Livré
```

---

# 53. Filter by Tracking and Status

```http
GET /v1/histories/?status=Livré&tracking=yal-123456
```

Multiple tracking numbers:

```http
GET /v1/histories/?status=Livré&tracking=yal-123456,yal-789123
```

---

# 54. History Filters

| Parameter | Type | Description |
|---|---|---|
| `tracking` | string | Parcel tracking number |
| `status` | string | Parcel status |
| `date_status` | string | Status date |
| `reason` | string | Failure/hold reason |
| `fields` | string | Fields to return |
| `page` | integer | Page number |
| `page_size` | integer | Results per page |
| `order_by` | string | Ordering field |
| `desc` | flag | Descending order |
| `asc` | flag | Ascending order |

---

# 55. History Date Filters

Single date:

```http
GET /v1/histories/?date_status=2020-06-01
```

Date range:

```http
GET /v1/histories/?date_status=2020-06-01,2020-07-01
```

---

# 56. History Status Values

The history API supports statuses including:

```text
Pas encore expédié
A vérifier
En préparation
Pas encore ramassé
Prêt à expédier
En passation
Ramassé
Bloqué
Débloqué
Transfert
Expédié
Centre
En localisation
Vers Wilaya
En transit
Reçu à Wilaya
En attente du client
Prêt pour livreur
Sorti en livraison
En attente
Annulé
En alerte
Alerte résolue
Tentative échouée
Livré
Echèc livraison
Retour vers centre
Retourné au centre
Retour transfert
Retour groupé
Retour à retirer
Retour non retiré
Colis abandonné
Retour vers vendeur
Retourné au vendeur
Echange échoué
```

---

# 57. History Failure and Hold Reasons

For failed delivery attempts, possible reasons include:

```text
Téléphone injoignable
Client ne répond pas
Faux numéro
Client absent (reporté)
Client absent (échoué)
Annulé par le client
Commande double
Le client n'a pas commandé
Produit erroné
Produit manquant
Produit cassé ou défectueux
Client incapable de payer
Wilaya erronée
Commune erronée
Client no-show
Adresse non livrable
```

For parcel holds:

```text
Document manquant
Produit interdit
Produit dangereux
Fausse déclaration
```

---

# 58. History Fields

| Field | Type | Description |
|---|---|---|
| `date_status` | string | Status timestamp |
| `tracking` | string | Parcel tracking |
| `status` | string | Parcel status |
| `reason` | string | Failure or hold reason |
| `center_id` | integer | Center where status occurred |
| `center_name` | string | Center name |
| `wilaya_id` | integer | Wilaya where status occurred |
| `wilaya_name` | string | Wilaya name |
| `commune_id` | integer | Commune where status occurred |
| `commune_name` | string | Commune name |

---

# 59. Select History Fields

Example:

```http
GET /v1/histories/?fields=tracking,status
```

This is useful when Edzeery only needs tracking and status information.

---

# 60. History Ordering

Default ordering:

```text
date_status DESC
```

Supported fields:

```text
date_status
tracking
status
reason
```

Example:

```http
GET /v1/histories/?order_by=tracking
```

Ascending:

```http
GET /v1/histories/?order_by=tracking&asc
```

Descending:

```http
GET /v1/histories/?order_by=tracking&desc
```

---

# 61. Recommended Edzeery Integration Architecture

For Edzeery, Yalidine should be integrated through a dedicated server-side delivery service.

Recommended architecture:

```text
Edzeery
   │
   ├── Order
   │
   ├── Delivery Order
   │
   └── Yalidine Integration Service
            │
            ├── Authentication
            ├── Wilayas
            ├── Communes
            ├── Centers
            ├── Fees
            ├── Parcels
            └── Histories
                     │
                     ▼
              Yalidine API
```

The frontend should communicate with Edzeery rather than directly with Yalidine.

---

# 62. Recommended Edzeery Delivery Flow

A typical Edzeery → Yalidine flow should be:

```text
1. Customer places an order
        ↓
2. Merchant confirms the order
        ↓
3. Edzeery validates customer information
        ↓
4. Edzeery resolves Wilaya
        ↓
5. Edzeery resolves Commune
        ↓
6. If Stop Desk:
      Resolve Stop Desk / Center
        ↓
7. Retrieve delivery fee if required
        ↓
8. Build Yalidine parcel payload
        ↓
9. POST /v1/parcels
        ↓
10. Receive Yalidine tracking number
        ↓
11. Store tracking in Edzeery
        ↓
12. Store label URL
        ↓
13. Synchronize parcel status
        ↓
14. GET /v1/histories/:tracking
        ↓
15. Update Edzeery order status
```

---

# 63. Recommended Data Mapping

A typical Edzeery order can be mapped to Yalidine as follows:

| Edzeery | Yalidine |
|---|---|
| Order ID | `order_id` |
| Sender Wilaya | `from_wilaya_name` |
| Customer First Name | `firstname` |
| Customer Last Name | `familyname` |
| Customer Phone | `contact_phone` |
| Customer Address | `address` |
| Destination Commune | `to_commune_name` |
| Destination Wilaya | `to_wilaya_name` |
| Product Name | `product_list` |
| Order Total / COD Amount | `price` |
| Insurance Enabled | `do_insurance` |
| Declared Value | `declared_value` |
| Package Length | `length` |
| Package Width | `width` |
| Package Height | `height` |
| Package Weight | `weight` |
| Free Shipping | `freeshipping` |
| Delivery Method | `is_stopdesk` |
| Stop Desk | `stopdesk_id` |
| Exchange Requested | `has_exchange` |
| Exchange Product | `product_to_collect` |
| Yalidine Tracking | `tracking` |
| Yalidine Label | `label` |
| Yalidine Status | `last_status` |

---

# 64. Recommended Local Caching

Wilayas, communes, centers, and other relatively stable reference data should not be requested from Yalidine on every customer interaction.

Edzeery should preferably synchronize and cache:

```text
Wilayas
Communes
Centers
Stop Desks
```

This reduces API usage and protects the application from unnecessary rate-limit consumption.

Example:

```text
Edzeery Database
      │
      ├── wilayas
      ├── communes
      └── delivery_centers
```

Then periodically synchronize these records with Yalidine.

---

# 65. Delivery Fee Calculation in Edzeery

The recommended process is:

```text
1. Identify sender Wilaya
2. Identify destination Wilaya
3. Request /fees
4. Identify destination Commune
5. Select delivery method
6. Select express/economic fee
7. Calculate billable weight
8. Calculate overweight fee
9. Add overweight fee to delivery fee
10. Apply the appropriate business logic for the order
```

### Example

```text
Sender Wilaya:
Batna

Destination Wilaya:
Adrar

Destination Commune:
Adrar

Delivery Method:
Express Home

Base Delivery Fee:
1400 DA

Billable Weight:
7 KG

Oversize Fee:
100 DA/KG

Free Threshold:
5 KG

Overweight:
7 - 5 = 2 KG

Overweight Fee:
2 × 100 = 200 DA

Total Delivery Cost:
1400 + 200 = 1600 DA
```

The exact final amount charged to the customer should follow Edzeery's own shipping-pricing configuration and the current Yalidine fee response.

---

# 66. Error Handling

Edzeery should distinguish between:

### HTTP-level errors

Examples:

```text
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
429 Too Many Requests
500 Internal Server Error
```

### Application-level parcel errors

A parcel creation request can return:

```json
{
    "success": false,
    "message": "Validation error"
}
```

Therefore, Edzeery must inspect both:

1. HTTP status code;
2. JSON response content;
3. per-parcel `success` value for bulk creation.

---

# 67. Handling HTTP 429

When the API returns:

```http
429 Too Many Requests
```

Edzeery should read:

```http
Retry-After
```

Example:

```http
Retry-After: 10
```

The application should wait for the specified period before retrying.

Do not immediately retry repeatedly because this can cause additional API restrictions.

---

# 68. Retry Strategy

For transient API failures, Edzeery should use controlled retries.

Recommended logic:

```text
Request
   ↓
Success?
 ├── YES → Process response
 │
 └── NO
      ↓
HTTP 429?
 ├── YES → Read Retry-After → Wait → Retry
 │
 └── NO
      ↓
Transient server error?
 ├── YES → Controlled retry
 │
 └── NO → Record error and stop
```

Retries should never be used to bypass API restrictions.

---

# 69. Security Recommendations for Edzeery

Yalidine credentials should be stored server-side.

Recommended Laravel configuration:

```env
YALIDINE_API_ID=
YALIDINE_API_TOKEN=
YALIDINE_BASE_URL=https://api.yalidine.app/v1
```

Never expose:

```env
YALIDINE_API_TOKEN
```

to:

```text
JavaScript
Browser
Livewire frontend state
Public API responses
Logs
Client-side HTML
```

---

# 70. Laravel Configuration Example

Example configuration:

```php
// config/services.php

return [

    'yalidine' => [
        'base_url' => env(
            'YALIDINE_BASE_URL',
            'https://api.yalidine.app/v1'
        ),

        'api_id' => env('YALIDINE_API_ID'),

        'api_token' => env('YALIDINE_API_TOKEN'),
    ],

];
```

---

# 71. Laravel HTTP Client Example

Example service request:

```php
use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'X-API-ID' => config('services.yalidine.api_id'),
    'X-API-TOKEN' => config('services.yalidine.api_token'),
])->get(
    config('services.yalidine.base_url') . '/wilayas/'
);

$data = $response->json();
```

---

# 72. Laravel Parcel Creation Example

```php
use Illuminate\Support\Facades\Http;

$payload = [
    [
        'order_id' => 'EDZEERY-10001',
        'from_wilaya_name' => 'Batna',
        'firstname' => 'Brahim',
        'familyname' => 'Mohamed',
        'contact_phone' => '0550123456',
        'address' => 'Cité Kaidi',
        'to_commune_name' => 'Bordj El Kiffan',
        'to_wilaya_name' => 'Alger',
        'product_list' => 'Coffee Machine',
        'price' => 3000,
        'do_insurance' => true,
        'declared_value' => 3500,
        'height' => 10,
        'width' => 20,
        'length' => 30,
        'weight' => 6,
        'freeshipping' => true,
        'is_stopdesk' => true,
        'stopdesk_id' => 163001,
        'has_exchange' => false,
        'product_to_collect' => null,
    ],
];

$response = Http::withHeaders([
    'X-API-ID' => config('services.yalidine.api_id'),
    'X-API-TOKEN' => config('services.yalidine.api_token'),
])->post(
    config('services.yalidine.base_url') . '/parcels/',
    $payload
);

$result = $response->json();
```

---

# 73. Recommended Edzeery Database Data

After successful parcel creation, Edzeery should store at minimum:

```text
delivery_provider
delivery_tracking
delivery_status
delivery_import_id
delivery_label_url
delivery_created_at
delivery_last_synced_at
```

Example:

```text
delivery_provider = yalidine
delivery_tracking = yal-12345A
delivery_status = En préparation
delivery_import_id = 234
delivery_label_url = ...
```

This allows Edzeery to maintain the relationship:

```text
Edzeery Order
      │
      └── Yalidine Parcel
              │
              ├── Tracking
              ├── Label
              ├── Current Status
              └── History
```

---

# 74. Tracking Synchronization

Edzeery can retrieve the current parcel:

```http
GET /v1/parcels/yal-123456
```

For the complete status timeline:

```http
GET /v1/histories/yal-123456
```

Recommended synchronization flow:

```text
Scheduled Sync
      ↓
Get Yalidine Tracking Numbers
      ↓
Retrieve Parcel / History
      ↓
Compare Current Status
      ↓
Update Edzeery Order
      ↓
Record Last Synchronization
```

---

# 75. Recommended Synchronization Rules

Edzeery should:

- avoid requesting the same parcel repeatedly within a short period;
- use stored tracking numbers;
- synchronize only relevant active shipments;
- stop frequent synchronization for completed/returned orders where appropriate;
- respect Yalidine rate limits;
- record synchronization failures;
- retry temporary failures using controlled delays.

---

# 76. Example End-to-End Workflow

## Step 1 — Get Wilayas

```http
GET /v1/wilayas/
```

Find:

```text
Batna
Alger
```

---

## Step 2 — Get Destination Communes

```http
GET /v1/communes/?wilaya_id=16
```

Find:

```text
Bordj El Kiffan
```

---

## Step 3 — Check Stop Desk

```http
GET /v1/communes/?has_stop_desk=true&wilaya_id=16
```

---

## Step 4 — Get Centers

```http
GET /v1/centers/?wilaya_id=16
```

Find the required `center_id`.

---

## Step 5 — Retrieve Fees

```http
GET /v1/fees/?from_wilaya_id=5&to_wilaya_id=16
```

Select:

```text
express_home
```

or:

```text
express_desk
```

depending on the delivery method.

---

## Step 6 — Create Parcel

```http
POST /v1/parcels/
```

Store:

```text
order_id
tracking
label
import_id
```

---

## Step 7 — Track Parcel

```http
GET /v1/parcels/yal-123456
```

---

## Step 8 — Retrieve Full History

```http
GET /v1/histories/yal-123456
```

---

## Step 9 — Update Edzeery

Map the Yalidine status to the corresponding Edzeery delivery/order status.

---

# 77. API Endpoint Reference

| Resource | Method | Endpoint | Purpose |
|---|---|---|---|
| Wilayas | GET | `/wilayas/` | List wilayas |
| Wilaya | GET | `/wilayas/:id` | Retrieve one wilaya |
| Communes | GET | `/communes/` | List communes |
| Commune | GET | `/communes/:id` | Retrieve one commune |
| Centers | GET | `/centers/` | List centers |
| Center | GET | `/centers/:center_id` | Retrieve one center |
| Fees | GET | `/fees/` | Retrieve delivery fees |
| Parcels | GET | `/parcels/` | List parcels |
| Parcel | GET | `/parcels/:tracking` | Retrieve one parcel |
| Parcels | POST | `/parcels/` | Create one or multiple parcels |
| Parcel | PATCH | `/parcels/:tracking` | Update parcel |
| Parcel | DELETE | `/parcels/:tracking` | Delete parcel |
| Parcels | DELETE | `/parcels/?tracking=...` | Delete multiple parcels |
| Histories | GET | `/histories/` | List status histories |
| History | GET | `/histories/:tracking` | Retrieve parcel history |

---

# 78. Implementation Checklist for Edzeery

Before enabling Yalidine for merchants, verify:

### Authentication

- [ ] API ID is configured.
- [ ] API Token is configured.
- [ ] Credentials are stored server-side.
- [ ] Credentials are never exposed to frontend code.
- [ ] Authentication test succeeds.

### Reference Data

- [ ] Wilayas synchronization works.
- [ ] Communes synchronization works.
- [ ] Stop-desk availability is recognized.
- [ ] Centers are synchronized.
- [ ] Center IDs are stored correctly.

### Delivery Fees

- [ ] Sender wilaya is identified.
- [ ] Destination wilaya is identified.
- [ ] Destination commune is identified.
- [ ] Home delivery fee is supported.
- [ ] Stop-desk delivery fee is supported.
- [ ] Weight calculation is implemented.
- [ ] Overweight fee is implemented.

### Parcel Creation

- [ ] `order_id` is unique.
- [ ] Customer name is valid.
- [ ] Phone number is validated.
- [ ] Address is available.
- [ ] Destination commune is valid.
- [ ] Destination wilaya is valid.
- [ ] Product description is available.
- [ ] Price is valid.
- [ ] Declared value is valid.
- [ ] Dimensions are valid.
- [ ] Weight is valid.
- [ ] `freeshipping` is correctly mapped.
- [ ] `is_stopdesk` is correctly mapped.
- [ ] `stopdesk_id` is included when required.
- [ ] Exchange parameters are validated.

### Response Handling

- [ ] `success` is checked for every parcel.
- [ ] Tracking number is stored.
- [ ] Import ID is stored.
- [ ] Label URL is stored.
- [ ] API error messages are stored/logged safely.
- [ ] Partial bulk failures are supported.

### Tracking

- [ ] Parcel status synchronization is implemented.
- [ ] History synchronization is implemented.
- [ ] Failed delivery reasons are handled.
- [ ] Return statuses are handled.
- [ ] Delivered status is handled.
- [ ] Exchange statuses are handled.

### Rate Limits

- [ ] Quota headers are monitored.
- [ ] HTTP 429 is handled.
- [ ] `Retry-After` is respected.
- [ ] Excessive polling is avoided.
- [ ] Reference data is cached.

---

# 79. Important Integration Rules

The following rules should be treated as critical when implementing Yalidine in Edzeery:

1. **Never expose Yalidine credentials to the frontend.**

2. **Always include `X-API-ID` and `X-API-TOKEN`.**

3. **Respect the Yalidine rate limits.**

4. **Use pagination for large datasets.**

5. **Cache reference data such as wilayas, communes, and centers whenever practical.**

6. **When creating a stop-desk parcel, `stopdesk_id` is mandatory.**

7. **When `has_exchange=true`, `product_to_collect` is mandatory.**

8. **When updating a parcel, only parcels in `En préparation` can be edited.**

9. **When deleting a parcel, only parcels in `En préparation` can be deleted.**

10. **Never overwrite Edzeery customer data with masked `GET` or `PATCH` response data.**

11. **For bulk parcel creation, process each parcel result independently.**

12. **Use the `tracking` value as the primary Yalidine shipment reference after successful creation.**

13. **Use the Histories endpoint when a complete status timeline is required.**

14. **Handle HTTP 429 using the `Retry-After` header.**

15. **Do not repeatedly retry failed requests without respecting API limits.**

---

# 80. Support

For Yalidine API development support, contact:

```text
developer@yalidine.com
```

Developer Dashboard:

```text
https://www.yalidine.app/app/dev/index.php
```

---

# 81. Summary

The Yalidine API provides the core resources required for a complete delivery integration:

```text
Wilayas
   ↓
Communes
   ↓
Centers / Stop Desks
   ↓
Fees
   ↓
Create Parcel
   ↓
Tracking
   ↓
Histories
```

For Edzeery, the recommended implementation is to encapsulate all Yalidine communication inside a dedicated server-side integration layer.

The frontend should communicate with Edzeery, while Edzeery communicates securely with Yalidine.

This architecture provides:

- secure credential management;
- reusable delivery services;
- centralized error handling;
- rate-limit protection;
- cached reference data;
- reliable tracking synchronization;
- easier support for future delivery providers.

---

## Yalidine API Base URL

```text
https://api.yalidine.app/v1/
```

## Developer Support

```text
developer@yalidine.com
```

## Main Resources

```text
GET    /wilayas/
GET    /communes/
GET    /centers/
GET    /fees/

GET    /parcels/
GET    /parcels/:tracking
POST   /parcels/
PATCH  /parcels/:tracking
DELETE /parcels/:tracking

GET    /histories/
GET    /histories/:tracking
```